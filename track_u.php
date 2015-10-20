<?php
/*
original, converted from tracks.php
10/4/08	added auto-refresh
10/4/08	corrected to include all point into bounding box
10/4/08	added direction icons
3/18/09 'aprs_poll' to 'auto_poll'
4/8/09 correction to icon names, 'small text' added
7/29/09	Changed titlebar to show Name and Handle
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
7/16/10 detailmap.setCenter correction
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 map.setUIToDefault();
8/19/10 alternative source of lookup argument
3/15/11 changed stylesheet.php to stylesheet.php
*/

@session_start();
session_write_close();
if (!array_key_exists ("user_id", $_SESSION)) {exit();}		//3/6/2015 - if logged out then kill this window

require_once('./incs/functions.inc.php');
//do_login(basename(__FILE__));		// in a window
$interval = intval(get_variable('auto_poll'));
$refresh = ($interval>0)? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";	//10/4/08

if (array_key_exists('unit_id', $_GET)) {	// 8/19/10
	$query = "SELECT  * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = {$_GET['unit_id']} LIMIT 1;";	//	8/19/10
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_array($result)) ;
	$source = $row['callsign'];
	}
else {
	extract($_GET);
	}

$query_callsign	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `callsign`='{$source}'";				// 7/29/09
$result_callsign = mysql_query($query_callsign) or do_error($query_callsign, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		// 7/29/09
$row_callsign	= mysql_fetch_assoc($result_callsign);				// 7/29/09
$handle = ($row_callsign['handle']);
$name = ($row_callsign['name']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - <?php print $name; ?> : <?php print $handle; ?> Tracks</TITLE>

<?php print $refresh; ?>	<!-- 10/4/08 -->
	
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></SCRIPT>
	<SCRIPT src="./js/usng.js"></SCRIPT>
	<SCRIPT src="./js/proj4js.js"></SCRIPT>
	<SCRIPT src="./js/proj4-compressed.js"></SCRIPT>
	<SCRIPT src="./js/leaflet/leaflet.js"></SCRIPT>
	<SCRIPT src="./js/proj4leaflet.js"></SCRIPT>
	<SCRIPT src="./js/leaflet/KML.js"></SCRIPT>
	<script src="./js/leaflet/gpx.js"></script>
	<SCRIPT src="./js/leaflet-openweathermap.js"></SCRIPT>
	<SCRIPT src="./js/esri-leaflet.js"></SCRIPT>
	<SCRIPT src="./js/OSOpenspace.js"></SCRIPT>
	<SCRIPT src="./js/Control.Geocoder.js"></SCRIPT>
	<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
	<script src="./js/Google.js"></script>
	<SCRIPT type="text/javascript" src="./js/osm_map_functions.js.php"></SCRIPT>
	<SCRIPT type="text/javascript" src="./js/L.Graticule.js"></SCRIPT>
	<SCRIPT SRC="./js/jscolor/jscolor.js"  type="text/javascript"></SCRIPT>
	<script type="text/javascript" src="./js/leaflet-providers.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};
window.onload=function(){set_size()};

var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var c_interval;
var boundary = [];
var bound_names = [];

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
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	load_exclusions();
	load_ringfences();
	load_catchments();
	load_basemarkup();
	load_groupbounds();	
	do_conditions();
	load_regions();
	}

var map;
var polyline = null;
var starticon = L.icon({
	iconUrl: './markers/start.png',
	iconSize: [16, 16],
	iconAnchor: [8, 8],
	popupAnchor: [9, 2]
	});

var endicon = L.icon({
	iconUrl: './markers/end.png',
	iconSize: [16, 16],
	iconAnchor: [8, 8],
	popupAnchor: [9, 2]
	});
	
var direcsicon = L.Icon.extend({options: {
	iconSize: [15, 15],	iconAnchor: [4, 4], popupAnchor: [9, 2]
	}
	});

var direcs=new Array("north.png","north_east.png","east.png","south_east.png","south.png","south_west.png","west.png","north_west.png", "north.png");	// 4/8/09
var colors = new Array ('odd', 'even');
var gmarkers = [];
var infoTabs = [];
var which;
var i = 0;			// sidebar/icon index, track point index
var points = false;								// none

function create_track_Marker(point, html, node_type, heading, id) {
	switch (node_type){
		case 1:				// start node
			var marker = L.marker(point, {icon: starticon}).bindPopup(html).addTo(map);
			break;
		case 0:				// end node
			var marker = L.marker(point, {icon: endicon}).bindPopup(html).addTo(map);				
			break;
		default : 			// in between nodes
			var iconurl = "./markers/" + direcs[heading];
			icon = new direcsicon({iconUrl: iconurl});	
			var marker = L.marker(point, {icon: icon}).bindPopup(html).addTo(map);
			}
	gmarkers[id] = marker;									// marker to array for side_bar click function
	infoTabs[id] = html;									// tabs to array
	return marker;
	}
	
function do_sidebar (sidebar, id) {
	side_bar_html += "<TR CLASS='" + colors[(id)%2] +"'>";
	side_bar_html += "<TD CLASS='td_label' COLSPAN=99>" + sidebar +"</TD></TR>\n";
	}

function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	gmarkers[id].openPopup()
	}

function ck_frames() {		// ck_frames()
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		}
	}		// end function ck_frames()
</SCRIPT>

<?php
function list_tracks($addon = '', $start) {
	global $source, $evenodd;
?>
<SCRIPT>
	var side_bar_html = "<TABLE CLASS='sidebar' ID='tbl_responders' WIDTH='100%'>";
	side_bar_html +="<TR><TD ALIGN='center' COLSPAN=99>Mouseover for details</TD></TR>";
<?php

	$toedit = "";

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	
	$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,
		`status` , `packet_date`,
		UNIX_TIMESTAMP(updated) AS `updated`
		FROM `$GLOBALS[mysql_prefix]tracks`
		WHERE `source` LIKE '" . like_ify($source) . "'
		ORDER BY `packet_date` ASC ";	//	6/16/08	
	
	$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result_tr)> 1 ) {
?>
		var j=0;				// point counter this unit
		var ender = <?php print mysql_num_rows($result_tr); ?> ;
<?php
		$last = $day = "";
		$i=1;
		
		while ($row_tr = stripslashes_deep(mysql_fetch_array($result_tr))) {
			if (substr($row_tr['packet_date'] ,  0,  10) != $day) {
				$day = substr ($row_tr['packet_date'] ,  0,  10);
				$sidebar_line = "<TR CLASS='" . $evenodd[$i%2] . "'><TD COLSPAN=99><U>" . $day . "</U></TD></TR>\n";
				} else {
				$sidebar_line = "<TR CLASS='" . $evenodd[$i%2] . "' onClick='myclick(" . $i . ");'>";		// 4/8/09
				$sidebar_line .= "<TD CLASS = 'text_small' TITLE='" . $row_tr['packet_date'] . "'>&nbsp;" .  	substr ($row_tr['packet_date'] , 11, 5) ." </TD>\n";
				$sidebar_line .= "<TD CLASS = 'text_small' TITLE='" . $row_tr['latitude']. ", ". $row_tr['longitude'] . "'>&nbsp;" . shorten($row_tr['latitude'], 8) ."</TD>\n";
				$sidebar_line .= "<TD CLASS = 'text_small'>&nbsp;" . $row_tr['speed']."@" . $row_tr['course'] . "</TD>\n";
				$sidebar_line .= "<TD CLASS = 'text_small' TITLE='" . $row_tr['closest_city'] . "'>&nbsp;" .  	shorten($row_tr['closest_city'], 16) ."</TD>\n";
				$sidebar_line .= "</TR>\n";
				}
?>
				j++;
				do_sidebar ("<?php print str_replace($eols, "", $sidebar_line); ?>", j);		// as single string
				var point = new L.LatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>);
				var html = "<b><?php print $row_tr['source'];?></b><br /><br /><?php print format_date($row_tr['updated']);?>";
				var heading = Math.round(<?php print intval($row_tr['course']);?>/45);		// 10/4/08
				if(j == 1) {
					map.panTo(point);
					map.setView(point, 15);
					window.bounds = map.getBounds();
					}
				if (j== ender) 	{node_type=0;}														// signifies last node 10/4/08
				else 			{node_type=j;};														// other than last
				var marker = create_track_Marker(point, html, node_type, heading, j);
				marker.addTo(map);
				bounds.extend(point);	// 10/4/08  all points to bounding box
<?php
				if (!empty($last)) {
?>
					if(polyline == null){
						var polyline = new L.polyline([
							new L.LatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>),		// prior point
							new L.LatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>)	// current point
							], "#FF0000", 2);
						polyline.addTo(map);
						points++;
						} else {
						polyline.addLatLng(new L.LatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>))
						points++
						}
<?php
					}		// end if (!empty($last))
				$last = $row_tr;										// either way 
				$i++;
			}		// end while ($row_tr...)
	
			$mode = ($last['speed'] == 0)? 1: 2 ;
			if ($last['speed'] >= 50) { $mode = 3;}
?>
			var point = new L.LatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>);	// mobile position
<?php
			}				// end (mysql_num_rows()> 1 )
?>
	if (!points) {		// any?
		map.setView(new L.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		map.fitBounds(bounds);
		}

<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
	</SCRIPT>
<?php
	}				// end function list_tracks() ===========================================================



$query_callsign	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `callsign`='{$source}'";				// 7/29/09
$result_callsign = mysql_query($query_callsign) or do_error($query_callsign, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		// 7/29/09
$row_callsign	= mysql_fetch_assoc($result_callsign);				// 7/29/09
$handle = ($row_callsign['handle']);				// 7/29/09
$name = ($row_callsign['name']);				// 7/29/09
?>
	</HEAD>
	<BODY>
	<A NAME='top'>
		<TABLE ID='outer'>
			<TR CLASS='even'>
				<TD ALIGN='center' colspan=2><B><FONT SIZE='+1'>Mobile Unit <?php print $handle;?> : <?php print $name;?> - Tracks</FONT></B></TD>
			</TR>
			<TR>
				<TD>
					<DIV ID='side_bar' style='width: 200px; max-height: <?php print get_variable('map_height');?>px; overflow-y: auto;'></DIV>
				</TD>
				<TD>
					<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
					<BR><BR>
					<DIV STYLE='text-align: center;'>
						<SPAN CLASS='plain' id='close_but' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'self.close()'>Close</SPAN>
						<SPAN CLASS='plain' id='refresh_but' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="javascript:location.reload(true);">Refresh</SPAN>
					</DIV>
					</TD>
			</TR>
		</TABLE><!-- end outer -->
			
		<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>
		
		<FORM NAME='to_add_form' METHOD='get' ACTION='units.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>
		
		<FORM NAME='can_Form' METHOD="post" ACTION = "units.php?func=responder"></FORM>
<?php

		$alt_urlstr =  "./incs/alt_graph.php?p1=" . urlencode($source) ;		// 7/18/08  Call sign for altitude graph
?>

<BR /><HR ALIGN='center' SIZE=1 COLOR='blue' WIDTH='80%'><BR />
<CENTER><img src="<?php print $alt_urlstr;?>" border=0 />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br><br>
<CENTER><A HREF='#top'><U>to top</U></A>
<SCRIPT>
//	setup map-----------------------------------//

var latLng;
var mapWidth = <?php print get_variable('map_width');?>;
var mapHeight = <?php print get_variable('map_height');?>;;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 20, theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 20);
var bounds = map.getBounds();	
map.setZoom(15);
</SCRIPT>
<?php
print list_tracks("", 0);
//do_kml();
?>
</BODY>
</HTML>