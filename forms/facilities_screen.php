<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$isGuest = (is_guest()) ? 1 : 0;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);										

$usng = get_text('USNG');
$osgb = get_text('OSGB');
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$sess_id = $_SESSION['id'];
$curr_cats = get_category_butts();	//	get current categories.
$fac_curr_cats = get_fac_category_butts();
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$api_key = get_variable('gmaps_api_key');
$key_str = (strlen($api_key) == 39) ? "key={$api_key}&" : false;
$gmaps_ok = ($key_str) ? 1 : 0;
$showmaps = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) ? 1 : 0;
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}


$mapzooms = array();
$dir = '_osm/tiles';
$mapdir = scandir($dir);
foreach($mapdir as $val) {
	if($val <> "." && $val <> "..") {
		if(is_dir('_osm/tiles/' . $val)) {
			$mapzooms[] = intval($val);
			}
		}
	}
if(count($mapzooms) > 0 && get_variable('local_maps') == "1") {$localZoomMin = min($mapzooms); $localZoomMax = max($mapzooms);} else {$localZoomMin = 0; $localZoomMax = 20;}
$setZoom = (get_variable('local_maps') == "1") ? $localZoomMin : get_variable('def_zoom');	
?>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var facFin = false;
var facstatSel = false;
var mapCenter;
var mapZoom;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var listwidth;
var leftlistwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var f_interval = null;
var latest_facility = 0;
var do_fac_update = true;
var facilities_updated = new Array();
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});
var fac_icons=[];
fac_icons[0] = 1;
fac_icons[1] = 2;
fac_icons[2] = 3;
fac_icons[3] = 4;	
fac_icons[4] = 5;
fac_icons[5] = 6;
fac_icons[6] = 7;
fac_icons[7] = 8;
			
var colors = new Array ('odd', 'even');

function loadData() {
	get_mi_totals();
	load_facilitylist(window.fac_field2, 'ASC');
	load_warnlocations();
	}

function set_size() {
	window.fac_last_display = 0;
	window.do_fac_update = true;
	facilities_updated = [];
	$('the_flist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	set_fontsizes(viewportwidth, "fullscreen");
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .40;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	leftlistwidth = leftcolwidth * .99;
	listwidth = leftlistwidth;
	rightlistwidth = rightcolwidth * .99;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('legend').style.width = mapWidth + "px";
	$('facilitylist').style.maxHeight = listHeight + "px";	
	$('facilitylist').style.width = leftlistwidth + "px";
	$('the_flist').style.maxHeight = listHeight + "px";
	$('the_flist').style.width = leftlistwidth + "px";
	$('facilitiesheading').style.width = leftlistwidth + "px";
	loadData();
	}

function pageLoaded() {
	if(facFin) {
		load_regions();
		load_catchments();
		mapCenter = map.getCenter();
		mapZoom = map.getZoom();
		map.invalidateSize();
		}
	set_fontsizes(viewportwidth, "fullscreen");
	}

function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
			}
		}
	if(tabid == "tab1") {
		if($('tab1')) {CngClass('tab1', 'tabinuse');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content1')) {$("content1").style.display = "block";}
		} else if(tabid == "tab2") {
		if($('tab2')) {CngClass('tab2', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content2')) {$("content2").style.display = "block";}
		} else {
		if($('tab3')) {CngClass('tab3', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "block";}
		init_minimap(3, lat,lng, "", 13, <?php print get_variable('locale');?>, 1);
		minimap.setView([lat,lng], 13);
		}
	}
</SCRIPT>
<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='application/x-javascript'></SCRIPT>		<!-- 10/14/08 -->
<?php
	}
	if($_SESSION['good_internet']) {	//	10/31/13
		$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;	 	//	10/23/12
		if((module_active("Ticker")==1) && (!($sit_scr))) {	//	6/1/12, 10/23/12
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='application/x-javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='application/x-javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";	//	3/23/11 To support ticket module
			}
		}

?>		
</HEAD>
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	
	if((!(is_guest())) && ($_SESSION['good_internet']) && (!($get_id))) {	//	4/6/11 Added for add on modules, 6/1/12 only on situation screen, not on ticket detail.
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}	
	
	$gunload = "clearInterval(f_interval);";				// 3/23/12
	$from_right = 20;	//	5/3/11
	$from_top = 10;		//	5/3/11
	$temp = intval(trim(get_variable('situ_refr')));		// 6/27/11
	$the_api_key = trim(get_variable('gmaps_api_key'));							// 3/5/12	
	$set_regions_control = ((!($get_id)) && ((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))) ? "set_regions_control();" : "";	//	6/1/12
	$get_messages = ($get_id) ? "get_mainmessages(" . $get_id . " ,'',sortby, sort, '', 'ticket');" : "";
?>
<BODY style="overflow-y: scroll;" onLoad = "ck_frames(); <?php print $ld_ticker;?> parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>
<DIV id='screenname' style='display: none;'>facilities</DIV>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
<DIV id='outer' style='position: relative; left: 0px;'>
	<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
		<A id='maj_incs' class='plainmi text_bold text_biggest' style='display: none;' onMouseover='do_hover_mi(this.id);' onMouseout='do_plain_mi(this.id);' HREF="maj_inc.php"></A>
		<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Facilities Screen</SPAN>&nbsp;&nbsp;&nbsp;
		<SPAN ID='theRegions' CLASS='heading' STYLE='background-color: #707070; cursor: hand;'>Viewing Regions (mouse over to view)</SPAN>
		<DIV id='timer_div' class='text_medium' style='color: #707070; float: right;'></DIV>
	</DIV>
	<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
		<DIV id='facilitiesheading' class = 'heading' style='border: 1px outset #707070;'>
			<DIV CLASS='heading text' style='text-align: center;'>Facilities</DIV>
			<SPAN class='text_medium text_center text_italic' style='color: #FFFFFF; width: 100%; display: block;' id='caption'>click on item to view / edit, Click headers to sort</SPAN>
		</DIV>
		<DIV class="scrollableContainer2" id='facilitylist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea2" id='the_flist' style='padding-top: 20px;'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<DIV style='z-index: 1; position: relative; text-align: center;'>
			<DIV style='width: 100%; font-size: 12px;'><?php print get_facilities_legend();?></DIV>
			<BR />
		</DIV>
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
		<DIV style='position: fixed; top: 50px; z-index: 9999;'>
<?php
			if (!(is_guest())) {
				if (can_edit()) {
?>
					<SPAN id='add_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='document.add_Form.submit();'>Add <?php print get_text("Facility");?><BR /><IMG id='show_asgn_img' SRC='./images/plus.png' /></SPAN>
<?php
					}
				if(may_email()) {
?>
					<SPAN id='mail_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='do_fac_mail_win();'>Contact <?php print get_text("Facilities");?><BR /><IMG id='show_asgn_img' SRC='./images/mail.png' /></SPAN>
<?php
					}
				}
?>
		</DIV>
	</DIV>
	<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
		<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		<CENTER><SPAN CLASS='text_blue text text_bold' style='width: 100%; text-align: center;'><?php print get_variable('map_caption');?></SPAN></CENTER><BR />
		<DIV id='legend' style='text-align: center;'>
			<SPAN CLASS="legend" STYLE="font-size: 14px; text-align: center; vertical-align: middle;"><B><?php print get_text("Facilities");?> Legend:</B></SPAN>
			<DIV CLASS="legend" ALIGN='center' VALIGN='middle' style='padding: 20px; text-align: center; vertical-align: middle;'>
<?php 
				print get_icon_legend ();
?>
			</DIV>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, TRUE, TRUE, $allow_filedelete, 0, 0, 0, 0);	//	09/05/14
?>
</DIV>
<SCRIPT>
var controlsHTML = "<TABLE id='controlstable' style='width: 200px; text-align: center;'>";
controlsHTML += "<SPAN class='heading' style='width: 100%; text-align: center; display: inline-block;'>Map Controls</SPAN></BR>";
controlsHTML +=	"<TR class='even'><TD><DIV ID = 'fac_boxes' style='width: 200px; height: 200px; text-align: center; vertical-align: middle;'></DIV></TD></TR></TABLE>";
//	setup map-----------------------------------//
var map;
var minimap;
var sortby = '`date`';
var sort = "DESC";
var columns = "<?php print get_msg_variable('columns');?>";
var the_columns = new Array(<?php print get_msg_variable('columns');?>);
var thescreen = 'ticket';
var thelevel = '<?php print $the_level;?>';
var fmarkers = [];			//	Responder Markers array
var wlmarkers = [];			//	Locations warning markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var latLng;

// set widths
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
set_fontsizes(viewportwidth, "fullscreen");
mapWidth = viewportwidth * .40;
mapHeight = viewportheight * .55;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
leftcolwidth = viewportwidth * .45;
rightcolwidth = viewportwidth * .40;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
leftlistwidth = leftcolwidth * .99;
listwidth = leftlistwidth;
rightlistwidth = rightcolwidth * .99;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('legend').style.width = mapWidth + "px";
$('facilitylist').style.maxHeight = listHeight + "px";	
$('facilitylist').style.width = leftlistwidth + "px";
$('the_flist').style.maxHeight = listHeight + "px";
$('the_flist').style.width = leftlistwidth + "px";
$('facilitiesheading').style.width = leftlistwidth + "px";
// end of set widths

var in_local_bool = "0";
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [0, 0],	shadowAnchor: [5, -5], popupAnchor: [6, -5]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [0, 0], popupAnchor: [6, -5]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [0, 0], popupAnchor: [6, -5]
}
});
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 13);
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;	// map is empty of points
$('controls').innerHTML = controlsHTML;
loadData();
<?php
do_kml();
?>
</SCRIPT>
<INPUT TYPE='hidden' NAME='func' VALUE='' />
</FORM>
<FORM NAME='add_Form' METHOD='get' ACTION='facilities.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='add' VALUE='true'>
</FORM>

<FORM NAME='fac_form' METHOD='get' ACTION='facilities.php?func=responder&edit=true'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='view' VALUE='false'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php?func=responder"></FORM>
</BODY>
<?php
if (array_key_exists('print', ($_GET))) {
?>
<script>
$("down").style.display = $("up").style.display = "none";
</script>
<?php
	}
?>
</HTML>
