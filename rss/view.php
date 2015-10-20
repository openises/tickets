<?php
/*
*/
//error_reporting(E_ALL);
require_once('../incs/functions.inc.php'); 
if(!array_key_exists('id', $_GET)) {
	exit();
	}
$theID = mysql_real_escape_string($_GET['id']);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) { 
	define("PROTOCOL", "https://");
	} else { 
	define("PROTOCOL", "http://"); 
	}
define("WEBHOST_URL", PROTOCOL.$_SERVER['HTTP_HOST']);
define("THISDIR_URL", PROTOCOL.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
$url = WEBHOST_URL;
$thisurl = THISDIR_URL;
$logo = $thisurl . "/logo.png";
$image = $thisurl . "/t.png";
$parent = dirname($thisurl);
$icons_dir = $parent . "/rm/roadinfo_icons/";

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
$query = "SELECT 
	`r`.`id` AS `feed_id`,
	`r`.`_on` AS `as_of`,
	`c`.`_on` AS `c_on`,	
	`r`.`_from` AS `r_from`, 
	`c`.`_from` AS `c_from`, 
	`r`.`address` AS `address`,
	`r`.`lat` AS `lat`,	
	`r`.`lng` AS `lng`,	
	`r`.`_by` AS `updated_by`, 
	`c`.`_by` AS `c_by`,
	`r`.`username` AS `username`,	
	`r`.`id` AS `the_id`, 
	`c`.`id` AS `type_id`, 
	`r`.`title` AS `the_title`, 
	`c`.`title` AS `type`,
	`c`.`icon` AS `type_icon`,
	`r`.`description` AS `notes`, 
	`c`.`description` AS `the_description` 
	FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
	LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON ( `r`.`conditions` = c.id )		
	WHERE `r`.`id` = " . $theID;
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = mysql_fetch_array($result,MYSQL_ASSOC);
extract($row);
$submitted = strtotime($as_of);
$iconurl = $icons_dir . $type_icon;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - View Road Conditions</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="../js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="../js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<SCRIPT TYPE="text/javascript" SRC="../js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="text/javascript" SRC="../js/domready.js"></script>
	<script src="../js/proj4js.js"></script>
	<script src="../js/proj4-compressed.js"></script>
	<script src="../js/leaflet/leaflet.js"></script>
	<script src="../js/proj4leaflet.js"></script>
	<script src="../js/leaflet/KML.js"></script>  
	<script src="../js/leaflet-openweathermap.js"></script>
	<script src="../js/esri-leaflet.js"></script>
	<script src="../js/OSOpenspace.js"></script>
	<script src="../js/Control.Geocoder.js"></script>
	<script type="text/javascript" src="../js/osm_map_functions.js.php"></script>
	<script type="text/javascript" src="../js/L.Graticule.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};

window.onload = function(){set_size();};
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array
var mapWidth;
var mapHeight;
var listHeight;
var listwidth;
var viewportwidth;
var viewportheight;
var colwidth;
var colheight;
var outerwidth;
var outerheight;
var colors = new Array ('odd', 'even');
var bounds;
var zoom;
var baseSqIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41]
	}
	});

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
	mapWidth = viewportwidth * .35;
	mapHeight = mapWidth;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	lefttblwidth = colwidth * .8;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('theHeading').style.width = outerwidth + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('leftTable').style.width = lefttblwidth + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	}
</SCRIPT>
</HEAD>
<BODY onLoad = "set_size();">
<DIV id='theHeading' class='heading' style='position: fixed; top: 0px; left: 0px; width: 100%; font-size: 2em; text-align: center;'>REPORTED ROAD CONDITION ALERT ON <?php print $url;?></DIV><BR />
<DIV id='outer' style='position: absolute; left: 0%; z-index: 1;'>
	<DIV id='leftcol' style='position: absolute; left: 0%; top: 10%; z-index: 3; margin-left: 10%;'>
		<TABLE id='leftTable' style='border: 4px solid #707070;'>
			<TR class='even'>
				<TD CLASS="td_label" style='vertical-align: top; padding: 10px; border-right: 1px solid #000000;'><?php print get_text('Address');?></TD>
				<TD CLASS='td_data_wrap' style='vertical-align: top; padding: 10px;'><?php print $address;?></TD>
			</TR>
			<TR class='odd'>
				<TD CLASS="td_label" style='vertical-align: top; padding: 10px; border-right: 1px solid #000000;'><?php print get_text('Title');?></TD>
				<TD CLASS='td_data_wrap' style='vertical-align: top; padding: 10px;'><?php print $the_title;?></TD>
			</TR>
			<TR class='even'>
				<TD CLASS="td_label" style='vertical-align: top; padding: 10px; border-right: 1px solid #000000;'><?php print get_text('Type');?></TD>
				<TD CLASS='td_data_wrap' style='vertical-align: top; padding: 10px;'><?php print $type;?></TD>
			</TR>
			<TR class='odd'>
				<TD CLASS="td_label" style='vertical-align: top; padding: 10px; border-right: 1px solid #000000;'><?php print get_text('Description');?></TD>
				<TD CLASS='td_data_wrap' style='vertical-align: top; padding: 10px;'><B><?php print $the_description;?></B><BR /><?php print $notes;?></TD>
			</TR>
			<TR class='even'>
				<TD CLASS="td_label" style='vertical-align: top; padding: 10px; border-right: 1px solid #000000;'><?php print get_text('Submitted');?></TD>
				<TD CLASS='td_data_wrap' style='vertical-align: top; padding: 10px;'><?php print date("F j, Y @ g:i a", $submitted);?></TD>
			</TR>
		</TABLE>
	</DIV>
	<DIV id='rightcol' style='position: absolute; right: 0%; top: 10%; z-index: 3; margin-right: 10%;'>
		<DIV ID='map_canvas' style='border: 4px outset #707070; z-index: 2;'></DIV>
	</DIV>
<SCRIPT>
var map;
var minimap;
var latLng;
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = mapWidth;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var theLat = "<?php print $lat;?>";
var theLng = "<?php print $lng;?>";
init_map(1, theLat, theLng, "", 13, theLocale, useOSMAP, "none");
L.control.scale().addTo(map);
L.control.zoom().addTo(map);
var bounds = map.getBounds();
var zoom = map.getZoom();
function createMarkerCond(lat, lon, iconurl) {
	if((isFloat(lat)) && (isFloat(lon))) {
		icon = new baseSqIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon});
		return marker;
		} else {
		return false;
		}
	}
	
var theMarker = createMarkerCond(theLat, theLng, "<?php print $iconurl;?>");
theMarker.addTo(map);
bounds.extend([theLat, theLng]);
map.fitBounds(bounds); 
map.setView([theLat, theLng], 13);
</SCRIPT>
</DIV>
</BODY>
</HTML>
<?php
exit();