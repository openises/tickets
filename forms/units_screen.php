<?php

error_reporting(E_ALL);				// 9/13/08

$not_sit = (array_key_exists('id', ($_GET)))?  $_GET['id'] : NULL;
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
window.onresize=function(){set_size()};
var respFin = false;
var statSel = false;
var mapCenter;
var mapZoom;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var listwidth;
var leftlistwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var r_interval = null;
var c_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = [];
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
var unit_icons=[];
unit_icons[0] = 0;
unit_icons[4] = 4;
			
var colors = new Array ('odd', 'even');

function loadData() {
	get_mi_totals();
	load_responderlist2(window.resp_field2, 'ASC');
	load_warnlocations();
	}

function set_size() {
	window.respFin = false;
	window.statSel = false;
	window.resp_last_display = 0;
	window.do_resp_update = true;
	responders_updated = [];
	$('the_rlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
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
	mapWidth = viewportwidth * .30;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	leftcolwidth = viewportwidth * .55;
	rightcolwidth = viewportwidth * .30;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .99;
	leftlistwidth = leftcolwidth * .99;
	rightlistwidth = rightcolwidth * .99;
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('legend').style.width = mapWidth + "px";
	$('responderlist').style.maxHeight = listHeight + "px";
	$('responderlist').style.width = leftlistwidth + "px";
	$('the_rlist').style.maxHeight = listHeight + "px";
	$('the_rlist').style.width = leftlistwidth + "px";
	$('respondersheading').style.width = leftlistwidth + "px";
	loadData();
	}
	
function pageLoaded() {
	if(respFin) {
		load_exclusions();
		load_ringfences();
		load_catchments();
		load_basemarkup();
		load_groupbounds();	
		do_conditions();
		load_regions();
		load_poly_controls();
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

?>		
</HEAD>
<?php
	if((!(is_guest())) && ($_SESSION['good_internet'])) {
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}

	$gunload = "clearInterval(r_interval);";				// 3/23/12
?>
<BODY style="overflow-y: scroll;" onLoad = "loadData(); ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>
<DIV id='screenname' style='display: none;'>responders</DIV>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
<DIV id = "outer" style='position: absolute; left: 0px;'>
	<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
		<A id='maj_incs' class='plainmi text_bold text_biggest' style='display: none;' onMouseover='do_hover_mi(this.id);' onMouseout='do_plain_mi(this.id);' HREF="maj_inc.php"></A>
		<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Units Screen</SPAN>&nbsp;&nbsp;&nbsp;
		<SPAN ID='theRegions' CLASS='heading' STYLE='background-color: #707070; cursor: hand;'>Viewing Regions (mouse over to view)</SPAN>
		<DIV id='timer_div' class='text_medium' style='color: #707070; float: right;'></DIV>
	</DIV>
	<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
		<DIV id="respondersheading" class='heading' style='border: 1px outset #707070;'>
			<DIV CLASS='heading text' style='text-align: center;'>Responders</DIV>
			<SPAN class='text_medium text_center text_italic' style='color: #FFFFFF; width: 100%; display: block;' id='caption'>click on item to view / edit, Click headers to sort</SPAN>
		</DIV>
		<DIV class="scrollableContainer2" id='responderlist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea2" id='the_rlist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<DIV style='z-index: 1; position: relative; text-align: center;'>
			<DIV style='width: 100%;'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></DIV>
			<BR />
			<DIV style='width: 100%; font-size: 12px;'><?php print get_units_legend();?></DIV>
		<BR />
		</DIV>				
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
		<DIV style='position: fixed; top: 50px; z-index: 9999;'>
			<SPAN id='show_asgn' class='plain_centerbuttons text' style='color: #CFCFCF; cursor: default; width: 80px; display: block; float: none;' onmouseover='do_hover_centerbuttons(this.id);' onmouseout='do_plain_centerbuttons(this.id);' onClick='do_assignment_flags();'>Show Assigned<BR /><IMG id='show_asgn_img' SRC='./images/assigned.png' style='opacity: 0.3;' /></SPAN>
			<SPAN id='tracks_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='do_tracks();'><?php print get_text("Unit");?> Tracks<BR /><IMG id='show_asgn_img' SRC='./images/tracks.png' /></SPAN>
<?php
			if (!(is_guest())) {
				if ((!(is_user())) && (!(is_unit())) || (get_variable('oper_can_edit') == "1")) {
?>
					<SPAN id='add_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='document.add_Form.submit();'>Add a <?php print get_text("Unit");?><BR /><IMG id='show_asgn_img' SRC='./images/plus.png' /></SPAN>
<?php
					}
?>
				<SPAN id='mail_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='do_mail_win();'>Contact <?php print get_text("Units");?><BR /><IMG id='show_asgn_img' SRC='./images/mail.png' /></SPAN>
<?php
				}
?>
		</DIV>
	</DIV>
	<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
		<DIV id='map_canvas' style='border: 1px outset #707070; z-index: 2;'></DIV>
		<CENTER><SPAN CLASS='text_blue text text_bold' style='width: 100%; text-align: center;'><?php print get_variable('map_caption');?></SPAN></CENTER><BR />
		<DIV id='legend' style='text-align: center;'>
			<SPAN CLASS="legend" STYLE="font-size: 14px; text-align: center; vertical-align: middle;"><B><?php print get_text("Units");?> Legend:</B></SPAN>
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
	var controlsHTML = "<TABLE id='controlstable' ALIGN='center'>";
	controlsHTML += "<SPAN class='heading' style='width: 100%; text-align: center; display: inline-block;'>Map Controls</SPAN></BR>";
	controlsHTML +=	"<TR class='even'><TD><CENTER><TABLE ID='buttons_sh' style='display: <?php print $show_controls;?>;'><TR CLASS='odd'><TD>";
	controlsHTML +=	"<DIV ID = 'boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
	controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'fac_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
	controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR></TABLE></CENTER></TD></TR></TABLE>";
//	setup map-----------------------------------//
var map;
var minimap;
var sortby = '`date`';
var sort = "DESC";
var columns = "<?php print get_msg_variable('columns');?>";
var the_columns = new Array(<?php print get_msg_variable('columns');?>);
var thescreen = 'ticket';
var thelevel = '<?php print $the_level;?>';
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	Responder Markers array
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
mapWidth = viewportwidth * .30;
mapHeight = viewportheight * .55;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
leftcolwidth = viewportwidth * .55;
rightcolwidth = viewportwidth * .30;
colheight = outerheight * .95;
listHeight = viewportheight * .8;
listwidth = colwidth * .99;
leftlistwidth = leftcolwidth * .99;
rightlistwidth = rightcolwidth * .99;
inner_listwidth = listwidth *.9;
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('legend').style.width = mapWidth + "px";
$('responderlist').style.maxHeight = listHeight + "px";
$('responderlist').style.width = leftlistwidth + "px";
$('the_rlist').style.maxHeight = listHeight + "px";
$('the_rlist').style.width = leftlistwidth + "px";
$('respondersheading').style.width = leftlistwidth + "px";
// end of set widths

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
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;	// map is empty of points
$('controls').innerHTML = controlsHTML;
<?php
do_kml();
?>
</SCRIPT>
<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<?php 		$the_val = (can_edit())? "edit" : "view"; ?>
<INPUT TYPE='hidden' NAME='<?php print $the_val;?>' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='add_Form' METHOD='get' ACTION='units.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='add' VALUE='true'>
</FORM>

<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__);?>?func=responder"></FORM>
<FORM NAME='tracks_Form' METHOD="get" ACTION = "tracks.php"></FORM>

<FORM NAME='resp_form' METHOD='get' ACTION='units.php?func=responder&edit=true'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
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
