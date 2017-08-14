<?php
/*
5/23/08 added do_kml() - generate KML JS - 
5/25/08 revised removed deleting non-located units
5/26/08 revised to avoid adding fixed unit location to bounds computation
5/26/08 revised to refer to units.php vice config.php
6/15/08 revised to show mobile units only
6/16/08 UTC time format conversion corrected
6/17/08 added tracks array information
6/25/08 added APRS window handling
8/27/08 mysql_fetch_assoc replaces fetch_array
10/4/08	added auto-refresh
1/21/09 added show butts - re button menu
1/24/09 revised per generated icons
2/24/09 corrected png names
3/18/09 'aprs_poll' to 'auto_poll'
1/23/10 refresh meat removed
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 map.setUIToDefault();
3/15/11 changed stylesheet.php to stylesheet.php
6/21/2013 corrected the APRS-only sql

*/

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

$api_key = get_variable('gmaps_api_key');

extract($_GET);

function is_a_float($n){									// 3/25/09
    return ( $n == strval(floatval($n)) )? true : false;
	}

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}
unset($result);

$icons = $GLOBALS['icons'];				// 1/1/09
$sm_icons = $GLOBALS['sm_icons'];

function get_icon_legend (){			// returns legend string - 1/1/09
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $u_types[$row['type']];
		$print .= "\t\t" .$temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$temp[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()
	
function list_responders($addon = '', $start) {

	global $u_types;
?>
	<SCRIPT>


	var color=0;
	var colors = new Array ('odd', 'even');
	var starting = false;
<?php

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$icons = $GLOBALS['icons'];

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// map type to blank icon id
		$blank = $icons[$row['icon']];
		print "\ticons[" . $row['id'] . "] = " . $row['icon'] . ";\n";	// 
		}
	unset($result);
?>
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders' style='width: 100%;'>";
	side_bar_html += "<TR class='heading'><TD CLASS='heading text' colspan=99 ALIGN='center'>Mobile Units</TD></TR>";
	side_bar_html += "<TR class='heading'><TD CLASS='text_medium' style='color: #FFFFFF;' colspan=99 ALIGN='center'>Click line or icon for information</TD></TR>";
	side_bar_html += "<TR class='even'><TD class='header'></TD><TD class='header' ALIGN='center'>Name</TD><TD class='header' ALIGN='center'>Description</TD><TD class='header' ALIGN='center'>Status</TD><TD class='header'>M</TD><TD class='header' ALIGN='center'>#</TD><TD class='header' ALIGN='center'>As of</TD></TR>";
	var which;
	var i = k = 0;			// sidebar/icon index, track point index
<?php

	$calls = array();
	$calls_nr = array();
	$calls_time = array();
	
	$query = "SELECT * , UNIX_TIMESTAMP(packet_date) AS `packet_date` FROM `$GLOBALS[mysql_prefix]tracks` ORDER BY `packet_date` ASC";		// 6/17/08
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

	while ($row = mysql_fetch_assoc($result)) {
		if (isset($calls[$row['source']])) {		// array_key_exists ( mixed key, array search )
			$calls_nr[$row['source']]++;
			}
		else {
			$calls[trim($row['source'])] = TRUE;
			$calls_nr[$row['source']] = 1;
			}
		$calls_time[$row['source']] = $row['packet_date'];		// save latest - note query order
		}

	$query = "SELECT `id`, `status_val` FROM `$GLOBALS[mysql_prefix]un_status`";		// build unit status values array
	$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$status_vals[0]="TBD";
	while ($temp_row = mysql_fetch_assoc($temp_result)) {					// build array of values
		$status_vals[$temp_row['id']]=$temp_row['status_val'];
		}	

	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `callsign` <> '' ORDER BY `name`";	// 1/24/09 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 

		// major while ... for mobile RESPONDER data starts here

	$aprs = FALSE;													// legend show/not boolean
	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$toedit = (is_guest())? "" : "<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
		$totrack  = (empty($row['callsign']))? "" : "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><U>Tracks</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;

		$temp = $row['un_status_id'] ;		// 2/24/09
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
		
		if (!$row['mobile']==1) {							// for fixed units
			$mode = ((is_a_float($row['lat'])) && (!($row['lat']==0)))? 0 :  4;				//  toss invalid lat's - 4/8/09
?>
			var point = new L.LatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// mobile position

<?php
			} else {			// is mobile, do infowin, etc.
			$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` , UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" .$row['callsign'] . "' ORDER BY `updated`";	//	6/16/08 
//			dump ($query);
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()> 0 ) {
?>
				var j=1;				// point counter this unit
				var ender = <?php print mysql_affected_rows(); ?> ;
				theTrack = L.polyline(theLatLng, {color: 'black'}).addTo(map);
				var thePoints = new Array();				
<?php
				$theLast = "";
				while ($row_tr = stripslashes_deep(mysql_fetch_assoc($result_tr))) {
					if($theLast != "") {
?>
						theLastLat = <?php print $theLast['latitude'];?>;
						theLastLng = <?php print $theLast['longitude'];?>;
						theLat = <?php print $row_tr['latitude'];?>;
						theLng = <?php print $row_tr['longitude'];?>;
						var point = L.LatLng(theLat, theLng);		
						bounds.extend(point);
						map.fitBounds(bounds);			
						var html = "<b><?php print $row_tr['source'];?></b><br /><br /><?php print format_date($row['updated']);?>";
						var heading = Math.round(<?php print intval($row_tr['course']);?>/45);		// 10/4/08
			
						var marker = create_track_Marker(point, html, j, ender, heading);
						marker.setMap(map);
						if(!theTrack) {
							theTrack = L.polyline(point, {color: 'black'}).addTo(map);
							} else {
							theTrack.addLatLng(theLatLng);	
							}
						points++;
						j++;k++;
<?php
						}	//	end if(!empty($last))
						$theLast = $row_tr;
					}		// end while ($row_tr...)

					$mode = ($theLast['speed'] == 0)? 1: 2 ;
					if ($theLast['speed'] >= 50) { $mode = 3;}
					} else {				// no track data, do sidebar only
					$mode = 4;			
					}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end mobile
//										common to all modes
		$the_bull = ($mode == 0)? "" : "<FONT COLOR=" . $bulls[$mode] ."><B>&bull;</B></FONT>";
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
			
		$sidebar_line = "<TD>" . shorten($row['name'], 30) . "</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 16) . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . shorten($the_status, 16) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";
		$the_count = (isset($calls[$row['callsign']]))? $calls_nr[$row['callsign']]: "";					// track records
		if (isset($calls[$row['callsign']])) {
			$the_time = $calls_time[$row['callsign']];
			$the_class = "aprs";
			$aprs = TRUE;				// show legend
			}
		else {
			$the_time = $row['updated'];
			$the_class = "td_data";
			}
			
		$sidebar_line .= "<TD CLASS='td_data' ALIGN='right'> " . $the_count . "</TD>";
		$sidebar_line .= "<TD CLASS='$the_class'>" . format_sb_date($the_time) . "</TD>";
		$temptype = $u_types[$row['type']];
		$the_type = $temptype[0];																			// 1/1/09
		$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>Details:" . $totrack . "&nbsp;&nbsp;&nbsp;&nbsp;". $toedit . "<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "</TABLE>";

		switch ($mode) {
			case 0:				// not mobile
?>			
				do_sidebar ("<?php print str_replace($eols, " ", $sidebar_line); ?>", i, <?php print $row_tr['source'] ;?>);
<?php
			    break;
			case 1:				// stopped
			case 2:				// moving
			case 3:				// fast
?>			
				do_sidebar ("<?php print str_replace($eols, " ", $sidebar_line); ?>", i);
<?php			
				$tab_1 .= "<BR /><TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_1 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $theLast['source'] . "</B></TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $theLast['course'] . ", Speed:  " . $theLast['speed'] . ", Alt: " . $theLast['altitude'] . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $theLast['closest_city'] . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $theLast['status'] . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($theLast['packet_date']) . "(UTC)</TD></TR>";	//	6/16/08 
				$tab_1 .= "</TABLE>";
				$tab2 = "";
			    break;
			case 4:				// mobile - no track
?>
				do_sidebar_nm ("<?php print str_replace($eols, " ", $sidebar_line); ?>", i, <?php print $row['id'];?>);	// special sidebar link - adds id for view
				var do_map = false;
<?php			
			    break;
			default:
			    echo "mode error: $mode";
			    break;
			}		// end switch
?>
			if (do_map) {
//				alert(point);
				var marker = createMarker(point, "<?php print $tab_1;?>", <?php print $row['type'];?>, i);	// (point,tabs, color, id)
				marker.addTo(map);
				}
			i++;				// zero-based
<?php
		}				// end major while ($row = ...) for each responder
		$aprs_legend = ($aprs)? "<TD CLASS='aprs' ALIGN='center'>APRS time</TD>": "<TD></TD>";

?>
	if (!points) {		// any?
		var initZoom = <?php print get_variable('def_zoom');?>;
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
		}
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD CLASS='text' COLSPAN=7 style='text-align: center;'>No Unit Tracks</TD></TR>";
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD><?php print $aprs_legend;?></TR>";
<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
	$aprs_but = "";		
?>
	side_bar_html += "<?php print $aprs_but;?>";

	document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
	
<?php
	do_kml() 		// generate KML JS - added 5/23/08
?>
	</SCRIPT>
<?php
	}				// end function list_responders() ===========================================================
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";	

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Tracks Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
        .text-labels {font-size: 2em; font-weight: 700;}
		.leaflet-control-layers-expanded { padding: 10px 10px 10px 10px; color: #333; background-color: #F1F1F1; border: 3px outset #707070;}
		.leaflet-control-layers-expanded .leaflet-control-layers-list {height: auto; display: block; position: relative; margin-bottom: 20px;}
		.centerbuttons {width: 80px; font-size: 1.2em;}
	</STYLE>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/messaging.js"></SCRIPT>
<?php 
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
?>
	<script type="application/x-javascript" src="./js/proj4js.js"></script>
	<script type="application/x-javascript" src="./js/proj4-compressed.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/leaflet.js"></script>
	<script type="application/x-javascript" src="./js/proj4leaflet.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/KML.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/gpx.js"></script>  
	<script type="application/x-javascript" src="./js/osopenspace.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-openweathermap.js"></script>
	<script type="application/x-javascript" src="./js/esri-leaflet.js"></script>
	<script type="application/x-javascript" src="./js/Control.Geocoder.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
?>
			<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
			<script src="./js/Google.js"></script>
<?php
			}
		}
?>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
	<SCRIPT>
	var user = "<?php print $_SESSION['user'];?>";
	var level = "<?php print $_SESSION['level'];?>";
	window.onresize=function(){set_size()};
</SCRIPT>
<?php
	require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
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
		set_fontsizes(viewportwidth);
		mapWidth = viewportwidth * .30;
		mapHeight = viewportheight * .55;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		colwidth = outerwidth * .42;
		leftcolwidth = viewportwidth * .42;
		rightcolwidth = viewportwidth * .42;
		colheight = outerheight * .95;
		listHeight = viewportheight * .8;
		listwidth = colwidth * .99;
		leftlistwidth = leftcolwidth * .99;
		$('outer').style.width = outerwidth + "px";
		$('outer').style.height = outerheight + "px";
		$('leftcol').style.width = leftcolwidth + "px";
		$('side_bar').style.width = leftcolwidth + "px";
		$('leftcol').style.height = colheight + "px";	
		$('rightcol').style.width = rightcolwidth + "px";
		$('rightcol').style.height = colheight + "px";	
		$('map_canvas').style.width = mapWidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
		}

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	var do_map = true;		// default	

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}
	function do_aprs_window() {				// 6/25/08
		var url = "http://www.openaprs.net?center=" + "<?php print get_variable('def_lat') . ',' . get_variable('def_lng');?>";
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=640,status=0,toolbar=0,menubar=0,location=0, left=50,top=250,screenX=50,screenY=250";
		newwindow=window.open(url, 'openaprs',  spec);
		if (isNull(newwindow)) {
			alert ("APRS display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}				// end function

	function do_track(callsign) {
		if (parent.frames["upper"].logged_in()) {
			try  {open_iw.close()} catch (e) {;}
			var width = <?php print get_variable('map_width');?>+360;
			var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
			var url = "track_u.php?source="+callsign;
			newwindow=window.open(url, callsign,  spec);
			if (isNull(newwindow)) {
				alert ("Track display requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow.focus();
			}
		}				// end function

	function hideGroup(color) {
		for (var i = 0; i < rmarkers.length; i++) {
			if (rmarkers[i]) {
				if (rmarkers[i].id == color) {
					rmarkers[i].show();
					}
				else {
					rmarkers[i].hide();
					}
				}		// end if (rmarkers[i])
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "visible";
		}			// end function

	function showAll() {
		for (var i = 0; i < rmarkers.length; i++) {
			if (rmarkers[i]) {
				rmarkers[i].show();
				}
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "hidden";

		}			// end function
		
	function createMarker(point,tabs, color, id) {
		if((isFloat(lat)) && (isFloat(lon))) {
			var letter = String.fromCharCode("A".charCodeAt(0) + id);
			var iconurl = "./our_icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;
			var icon = new baseIcon({iconUrl: iconurl});	
			var marker = L.marker(point, {icon: icon});
			marker.on('popupclose', function(e) {
				map.setView(mapCenter, mapZoom);
				});
			marker.on('click', function(e) {
				map.panTo(rmarkers[id].getLatLng());
				rmarkers[id].bindPopup(tabs).openPopup();
				});	
			marker.id = color;
			marker.category = category;
			marker.region = region;		
			marker.stat = stat;
			rmarkers[theid] = marker;
			var point = new L.LatLng(lat, lon);
			rmarkers[theid].latlng = point;
			var in_local_bool = <?php print get_variable('local_maps');?>;
			if(in_local_bool == "1" && (theBounds instanceof Array)) {
				var southWest = L.latLng(theBounds[3], theBounds[0]);
				var northEast = L.latLng(theBounds[1], theBounds[2]);
				var maxBounds = L.latLngBounds(southWest, northEast);
				if(maxBounds.contains(point)) {
					bounds.extend(point);
					}
				} else {
				bounds.extend(point);				
				}
			return marker;
			} else {
			return false;
			}
		}

	function create_track_Marker(point, html, mytype, ender, heading) {	//	5/1/13
		switch (mytype){
			case 1:
				var marker = L.marker(point, {icon: starticon});
				marker.on('popupclose', function(e) {
					map.setView(mapCenter, mapZoom);
					});
				marker.on('click', function(e) {
					map.panTo(rmarkers[id].getLatLng());
					marker.bindPopup(html).openPopup();
					});	
				break;
			case ender:
				var marker = L.marker(point, {icon: endicon});
				var marker = new google.maps.Marker({position: point, map: map, icon: endicon});
				marker.on('popupclose', function(e) {
					map.setView(mapCenter, mapZoom);
					});
				marker.on('click', function(e) {
					map.panTo(rmarkers[id].getLatLng());
					marker.bindPopup(html).openPopup();
					});			
				break;
			default : 
				var infoicon = "./markers/" + direcs[heading];
				var marker = L.marker(point, {icon: infoicon});
				marker.on('popupclose', function(e) {
					map.setView(mapCenter, mapZoom);
					});
				marker.on('click', function(e) {
					map.panTo(rmarkers[id].getLatLng());
					marker.bindPopup(html).openPopup();
					});			
				}
		return marker;
		}
																// 1/24/09
	function do_sidebar (sidebar, id, call) {
		var letter = String.fromCharCode("A".charCodeAt(0) + id);								// start with A - 1/5/09
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/5/09
		}

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		var letter = String.fromCharCode("A".charCodeAt(0) + line_no);							// start with A - 1/5/09
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/23/09
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		alert("No track data");
		}

	function myclick(id, call) {					// Responds to sidebar click, then triggers listener above -  note [id]
		google.maps.event.trigger(rmarkers[id], 'click');		
		}
	</SCRIPT>
	</HEAD>
	<BODY>
		<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
			<DIV id='button_bar' class='but_container'>
				<SPAN id='print_but' class='plain' style='float: left; vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'>Print</SPAN>
				<SPAN id='close_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
			</DIV>
			<DIV id = "leftcol" style='position: relative; top: 70px; left: 10px; float: left;'>
				<DIV ID='side_bar'></DIV>
			</DIV>
			<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>
				&nbsp;
			</DIV>
			<DIV id='rightcol' style='position: relative; top: 70px; left: 20px; float: left;'>
				<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
				<DIV id='legend' style='text-align: center;'>
					<SPAN CLASS="legend" STYLE="font-size: 14px; text-align: center; vertical-align: middle;"><B><?php print get_text("Units");?> Legend:</B></SPAN>
					<DIV CLASS="legend" ALIGN='center' VALIGN='middle' style='padding: 20px; text-align: center; vertical-align: middle;'>
<?php 
						print get_icon_legend ();
?>
					</DIV>
				</DIV>
			</DIV>
	
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var points;								// none
var direcs=new Array("north.png","north_east.png","east.png","south_east.png","south.png","south_west.png","west.png","north_west.png", "north.png");	// 10/4/08
var icons=[];						// note globals
var marker;
var rmarkers = [];
var infowindow;
var center = new L.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
var theLat;
var theLng;
var theLastLat;
var theLastLng;

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

var starticon = L.icon({
	iconUrl: './markers/start.png',
	iconSize:     [16, 16], // size of the icon
	iconAnchor:   [0, 0], // point of the icon which will correspond to marker's location
	popupAnchor:  [8, 8] // point from which the popup should open relative to the iconAnchor
	});
	
var endicon = L.icon({
	iconUrl: './markers/start.png',
	iconSize:     [16, 16], // size of the icon
	iconAnchor:   [0, 0], // point of the icon which will correspond to marker's location
	popupAnchor:  [8, 8] // point from which the popup should open relative to the iconAnchor
	});

var map;				// the map object - note GLOBAL
var points;

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
set_fontsizes(viewportwidth);
mapWidth = viewportwidth * .30;
mapHeight = viewportheight * .55;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
leftcolwidth = viewportwidth * .42;
rightcolwidth = viewportwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .8;
listwidth = colwidth * .99;
leftlistwidth = leftcolwidth * .99;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('side_bar').style.width = leftcolwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
var bounds = map.getBounds();
var zoom = map.getZoom();
</SCRIPT>
<?php
print list_responders("", 0);
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "units.php?func=responder"></FORM>
			
<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='view' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='to_add_form' METHOD='get' ACTION='units.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='add' VALUE='true'>
</FORM>


</BODY>
</HTML>
