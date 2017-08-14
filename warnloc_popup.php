<?php
/*
9/10/13 - New file - popup window to vie details of Location warnings
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);
$api_key = get_variable('gmaps_api_key');		// empty($_GET)
$in_win = array_key_exists ("mode", $_GET);		// in

if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
	do_login(basename(__FILE__));
	}
if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	}

$id =	(array_key_exists('id', ($_GET)))?	$_GET['id']  :	NULL;

$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]warnings` WHERE id='$id'");
$row = mysql_fetch_assoc($result);
$lat = $row['lat'];
$lng = $row['lng'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Location Warning Details</TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		#regions_control { font-family: verdana, arial, helvetica, sans-serif; font-size: 5px; background-color: #FEFEFE; font-weight: bold;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 2em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { left: -1px; top: 0; position: absolute; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
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
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT><!-- 10/23/12-->
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/OSOpenspace.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<SCRIPT>
	window.onresize=function(){set_size()};
	window.onload = function(){set_size();};
	</SCRIPT>
<?php
	require_once('./incs/all_forms_js_variables.inc.php');
?>
	<SCRIPT>
	var layercontrol;	
	
	function ck_frames() {		// onLoad = "ck_frames()"
		}		// end function ck_frames()

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
	
	var icon_file = "./markers/sm_red.png";
	
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
		mapWidth = viewportwidth * .99;
		mapHeight = viewportheight * .60;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		colwidth = outerwidth * .42;
		colheight = outerheight * .95;
		listHeight = viewportheight * .7;
		listwidth = colwidth * .95
		inner_listwidth = listwidth *.9;
		celwidth = listwidth * .20;
		res_celwidth = listwidth * .15;
		fac_celwidth = listwidth * .15;
		$('outer').style.width = outerwidth + "px";
		$('outer').style.height = outerheight + "px";
		$('map_canvas').style.width = mapWidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
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
</HEAD>
<BODY onLoad='ck_frames();'>
<TABLE ALIGN = 'center'><TR><TD>

<CENTER><BR /><BR clear=all/><BR /></CENTER>
<TABLE style='width: 680px;'>
	<TR>
		<TD style='width: 300px;'>
			<TABLE style='width: 300px; border: 1px solid #000000;'>
				<TR class='tab_row'>
					<TD class='wrap_label'>Title</TD><TD class='wrap_data'><?php print $row['title'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Street</TD><TD class='wrap_data'><?php print $row['street'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>City</TD><TD class='wrap_data'><?php print $row['city'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>State</TD><TD class='wrap_data'><?php print $row['state'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Latitude</TD><TD class='wrap_data'><?php print $lat;?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Longitude</TD><TD class='wrap_data'><?php print $lng;?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Description</TD><TD class='wrap_data'><?php print $row['description'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Reported By</TD><TD class='wrap_data'><?php print get_owner($row['_by']);?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Date Reported</TD><TD class='wrap_data'><?php print $row['_on'];?></TD>
				</TR>
			</TABLE>
		</TD>
		<TD style='width: 380px;'>
			<DIV id='map_canvas' style='z-index:1; width: 380px; height: 380px'></DIV>
		</TD>
	</TR>
</TABLE>
<BR /><BR /><BR />
<CENTER>
<SPAN ID='fin_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
</CENTER>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>'>
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE=''>
</FORM>
</TD></TR></TABLE>
<SCRIPT>
var map;
var minimap;
var latLng;
var in_local_bool = "0";
var mapWidth = 800;
var mapHeight = 450;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", 13, theLocale, 1);
map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);
var bounds = map.getBounds();
var zoom = map.getZoom();
</SCRIPT>
</BODY></HTML>
