<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
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

	// set auto-refresh if any mobile units														
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 1.3em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 1.3em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { position: absolute; left: -1px; top: 0px; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
		.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}
		div.tabBox {}
		div.tabArea { font-size: 80%; font-weight: bold; padding: 0px 0px 3px 0px; }
		span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; -moz-border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
				padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
		span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; -moz-border-radius: .75em .75em 0em 0em;
				border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
		span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
		div.content { font-size: 80%; background-color: #F0F0F0; border: 2px outset #707070; -moz-border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
				position: relative;	z-index: 101; cursor: normal; height: 250px;}
		div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
	</STYLE>
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT><!-- 10/23/12-->
<?php 

if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}	
if ($_SESSION['internet']) {				// 8/22/10
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
?>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>  
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/osopenspace.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
	<script src="./js/Google.js"></script>
	<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
	<script type="text/javascript" src="./js/L.Graticule.js"></script>
	<script type="text/javascript" src="./js/leaflet-providers.js"></script>
<?php } ?>

<SCRIPT>
window.onresize=function(){set_size()};
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var incFin = true;
var respFin = true;
var facFin = false;
var logFin = true;
var mapCenter;
var mapZoom;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
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

function set_size() {
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
	load_fac_status_control();
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	listHeight = viewportheight * .25;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .5;
	listwidth = colwidth * .95
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('legend').style.width = mapWidth + "px";
	$('facilitylist').style.maxHeight = listHeight + "px";	
	$('facilitylist').style.width = listwidth + "px";
	$('the_flist').style.maxHeight = listHeight + "px";
	$('the_flist').style.width = listwidth + "px";
	$('facilitiesheading').style.width = listwidth + "px";
	load_regions();
	load_catchments();
	map.invalidateSize();
	}

function pageLoaded() {
	if(facFin) {
		mapCenter = map.getCenter();
		mapZoom = map.getZoom();
		} else {
		return;
		}
	}

function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
//		alert(suffix);
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
		<SCRIPT SRC='./js/usng.js' 			TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
<?php
	}
	if($_SESSION['good_internet']) {	//	10/31/13
		$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;	 	//	10/23/12
		if((module_active("Ticker")==1) && (!($sit_scr))) {	//	6/1/12, 10/23/12
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='text/javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='text/javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";	//	3/23/11 To support ticket module
			}
		}

?>		
<STYLE TYPE="text/css">
.box { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:1000; width: 180px; }
.bar { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
/* 3/26/2013
.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
*/
.bar_header { height: 30px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
.content { padding: 1em; }
</STYLE>
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
<BODY onLoad = "set_size(); ck_frames(); <?php print $ld_ticker;?> parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>facilities</DIV>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
<DIV id='outer' style='position: absolute; left: 0px;'>
	<DIV id='leftcol' style='position: absolute; left: 10px;'>
		<DIV id='facilitiesheading' class = 'heading'>
			<DIV style='text-align: center;'>Facilities
				<SPAN id='collapse_facs' onClick="hideDiv('facilitylist', 'collapse_facs', 'expand_facs')" style = 'display: "";'><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
				<SPAN id='expand_facs' onClick="showDiv('facilitylist', 'collapse_facs', 'expand_facs')" style = 'display: none;'><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
			</DIV>
		</DIV>
		<DIV class="scrollableContainer" id='facilitylist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea" id='the_flist'><CENTER><IMG src='./images/owmloading.gif'></DIV>				
		</DIV>
		<BR />
		<DIV style='z-index: 1; position: relative; text-align: center;'>
			<DIV style='width: 100%; font-size: 12px;'><?php print get_facilities_legend();?></DIV>
			<BR />
<?php
			if ((!(is_user())) && (!(is_unit()))) {
?>
				<SPAN id='add_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='document.add_Form.submit();'>Add a <?php print get_text("Facility");?></SPAN>
<?php
				}

			if(may_email()) {
?>
				<SPAN id='mail_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='do_fac_mail_win();'>Email <?php print get_text("Facilities");?></SPAN>
<?php
				}
?>
		</DIV>
	</DIV>
	<DIV id='rightcol' style='position: absolute; right: 170px;'>
		<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		<DIV id='legend' style='border: 1px outset #707070; text-align: center;'>
			<SPAN CLASS="legend" STYLE="font-size: 14px; text-align: center; vertical-align: middle;"><B><?php print get_text("Facilities");?> Legend:</B></SPAN>
			<DIV CLASS="legend" ALIGN='center' VALIGN='middle' style='padding: 20px; text-align: center; vertical-align: middle;'>
<?php 
				print get_icon_legend ();
				$from_right = 20;
				$from_top = 10;	
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
var boundary = [];			//	exclusion zones array
var bound_names = [];
var latLng;
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
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = <?php print get_variable('map_height');?>+20;;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 13);
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;	// map is empty of points
$('controls').innerHTML = controlsHTML;
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

<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php?func=responder"></FORM>

<br /><br />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>
<A NAME="bottom" /> <!-- 11/11/09 -->
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
