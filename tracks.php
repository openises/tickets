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
*/
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
$api_key = get_variable('gmaps_api_key');

extract($_GET);

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}
//dump($u_types);	
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
		$print .= "\t\t" .$temp[0] . " &raquo; <IMG SRC = './icons/" . $sm_icons[$temp[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()
	
function list_responders($addon = '', $start) {
global $u_types, $my_session;
?>
<SCRIPT>

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	var color=0;
	var colors = new Array ('odd', 'even');
	var starting = false;

	function $() {								// 1/23/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}
	function do_aprs_window() {				// 6/25/08
//				echo '<a href="mycgi?foo=', urlencode($userinput), '">';
	
//		var url = "http://www.openaprs.net?center=" + "<?php print urlencode(get_variable('def_lat') . ',' . get_variable('def_lng'));?>";
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
			map.closeInfoWindow();
			var width = <?php print get_variable('map_width');?>+360;
			var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
			var url = "track_u.php?source="+callsign;
			newwindow=window.open(url, callsign,  spec);
			if (isNull(newwindow)) {
				alert ("Track display requires popups to be enabled. Please adjust your browser options.");
				return;
				}
//			starting = false;
			newwindow.focus();
			}
		}				// end function

	function hideGroup(color) {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].id == color) {
					gmarkers[i].show();
					}
				else {
					gmarkers[i].hide();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "visible";
		}			// end function

	function showAll() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "hidden";

		}			// end function

	function create_track_Marker(point,html, mytype, ender, heading) {
		switch (mytype){
			case 1:
//				alert(99);
				var marker = new GMarker(point, starticon);	
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
					});
				break;
			case ender:
//				alert(106);
				var marker = new GMarker(point, endicon);	
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
					});
				break;
			default : 
				var infoicon = new GIcon();
				infoicon.image = "./markers/" + direcs[heading];
				
				infoicon.iconSize = new GSize(15, 15);
				infoicon.iconAnchor = new GPoint(4, 4);
			
				var marker = new GMarker(point, infoicon);	
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
					});
				}
		return marker;
		}
																// 1/24/09
	function createMarker(point,tabs, color, id) {				// Creates marker and sets up click event infowindow 
		points = true;											// at least one
		var letter = String.fromCharCode("A".charCodeAt(0) + id);		// start with A - 1/5/09

		var icon = new GIcon(listIcon);
		var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;				// 1/5/09
		icon.image = icon_url;		// ./icons/gen_icon.php?blank=4&text=zz"

		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide - unused

		GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();
			marker.openInfoWindowTabsHtml(infoTabs[id]);
			
//			var dMapDiv = document.getElementById("detailmap");
//			var detailmap = new GMap2(dMapDiv);
//			detailmap.addControl(new GSmallMapControl());
//			detailmap.setCenter(point, 13);  					// larger # = closer
//			detailmap.addOverlay(marker);

			setTimeout(function() {										// wait for rendering complete - 12/17/08
			if ($("detailmap")) {				
				var dMapDiv = $("detailmap");
				var detailmap = new GMap2(dMapDiv);
				detailmap.addControl(new GSmallMapControl());
				detailmap.setCenter(point, 17);  						// larger # = closer
				detailmap.addOverlay(marker);
				}
			else {
//				alert(62);
//				alert($("detailmap"));
				}
			},4000);				// end setTimeout(...)


		});			// end 	GEvent.addListener()




		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
//		bounds.extend(point);									// extend the bounding box - removed 5/26/08
		return marker;
		}				// end function create Marker()
		
	function do_sidebar (sidebar, id, call) {
		var letter = String.fromCharCode("A".charCodeAt(0) + id);								// start with A - 1/5/09
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/5/09
		}

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		var letter = String.fromCharCode("A".charCodeAt(0) + line_no);							// start with A - 1/5/09
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/23/09
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		alert("No track data");
		}

	function myclick(id, call) {					// Responds to sidebar click, then triggers listener above -  note [id]
		GEvent.trigger(gmarkers[id], "click");
		}

	function doGrid() {
		map.addOverlay(new LatLonGraticule());
		}

	function do_lat (lat) {
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_lng.value=lng.toFixed(6);
		document.forms[0].frm_lng.disabled=true;
		}
						// 2/24/09
	var direcs=new Array("north.png","north_east.png","east.png","south_east.png","south.png","south_west.png","west.png","north_west.png", "north.png");	// 10/4/08

	var icons=[];						// note globals

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

	var starticon = new GIcon();
	starticon.image = "./markers/start.png";	
	starticon.iconSize = new GSize(16, 16);
	starticon.iconAnchor = new GPoint(8, 8);

	var endicon = new GIcon();
	endicon.image = "./markers/end.png";
	endicon.iconSize = new GSize(16, 16);
	endicon.iconAnchor = new GPoint(8, 8);

	var map;
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
	side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Mobile Units</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for information</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Description</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>#</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = k = 0;			// sidebar/icon index, track point index
	var points = false;								// none

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>

	var bounds = new GLatLngBounds();						// create  bounding box
//	map.addControl(new GOverviewMapControl());
	map.enableScrollWheelZoom(); 	

	var listIcon = new GIcon();
	listIcon.image = "./markers/yellow.png";	
	listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new GSize(20, 34);
	listIcon.shadowSize = new GSize(37, 34);
	listIcon.iconAnchor = new GPoint(8, 28);
	listIcon.infoWindowAnchor = new GPoint(9, 2);
	listIcon.infoShadowAnchor = new GPoint(18, 25);

	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

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
//			array_push ($calls, trim($row['source']));
			$calls[trim($row['source'])] = TRUE;
			$calls_nr[$row['source']] = 1;
			}
		$calls_time[$row['source']] = $row['packet_date'];		// save latest - note query order
		}

//	dump($calls);
//	dump($calls_nr);
//	dump($calls_time);
	

	$query = "SELECT `id`, `status_val` FROM `$GLOBALS[mysql_prefix]un_status`";		// build unit status values array
	$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$status_vals[0]="TBD";
	while ($temp_row = mysql_fetch_assoc($temp_result)) {					// build array of values
		$status_vals[$temp_row['id']]=$temp_row['status_val'];
		}	

	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `aprs` = 1 AND `callsign` <> '' ORDER BY `name`";	// 1/24/09 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//	dump(mysql_affected_rows());

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 

		// major while ... for mobile RESPONDER data starts here

	$aprs = FALSE;													// legend show/not boolean
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$toedit = (is_guest())? "" : "<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
		$totrack  = (empty($row['callsign']))? "" : "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><U>Tracks</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;

		$temp = $row['un_status_id'] ;		// 2/24/09
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
		
		if (!$row['mobile']==1) {							// for fixed units
			$mode = ($row['lat']==0)? 4 :  0;				//  toss invalid lat's
?>
		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// mobile position

<?php
			}
		else {			// is mobile, do infowin, etc.
			$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` , UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" .$row['callsign'] . "' ORDER BY `updated`";	//	6/16/08 
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()> 0 ) {
?>
				var j=1;				// point counter this unit
				var ender = <?php print mysql_affected_rows(); ?> ;
<?php
				$last = "";
				while ($row_tr = stripslashes_deep(mysql_fetch_assoc($result_tr))) {
?>
					bounds.extend(new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>));	// all points to bounding box
					var point = new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>);
					var html = "<b><?php print $row_tr['source'];?></b><br /><br /><?php print format_date($row['updated']);?>";
					var heading = Math.round(<?php print intval($row_tr['course']);?>/45);		// 10/4/08
		
					var marker = create_track_Marker(point, html, j, ender, heading);
					map.addOverlay(marker);	

<?php
					if (!empty($last)) {
?>			
						var polyline = new GPolyline([
						    new GLatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>),		// prior point
						    new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>)	// current point
							], "#FF0000", 2);
						map.addOverlay(polyline);
						points++;
						j++;k++;
<?php
						}		// end if (!empty($last))
					$last = $row_tr;										// either way 
					}		// end while ($row_tr...)
	
					$mode = ($last['speed'] == 0)? 1: 2 ;
					if ($last['speed'] >= 50) { $mode = 3;}
?>
					var point = new GLatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>);	// mobile position
<?php
					}				// end (mysql_affected_rows()> 0 )
				else {				// no track data, do sidebar only
					$mode = 4;			
					}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end mobile
//										common to all modes
		$the_bull = ($mode == 0)? "" : "<FONT COLOR=" . $bulls[$mode] ."><B>&bull;</B></FONT>";
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
			
		$sidebar_line = "<TD>" . shorten($row['name'], 30) . "</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 16) . "</TD>";
//		$sidebar_line .= "<TD CLASS='td_data'> " . shorten($status_vals[$row['un_status_id']], 16) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . shorten($the_status, 16) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";
		$the_count = (isset($calls[$row['callsign']]))? $calls_nr[$row['callsign']]: "";					// track records
//		$the_time = (isset($calls[$row['callsign']]))? $calls_time[$row['callsign']]: $row['updated'];		// latest report time
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
?>

		var do_map = true;		// default
		
<?php
		$temptype = $u_types[$row['type']];
		$the_type = $temptype[0];																			// 1/1/09

		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
//		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
//		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$row['un_status_id']] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>Details:" . $totrack . "&nbsp;&nbsp;&nbsp;&nbsp;". $toedit . "<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "</TABLE>";
//		dump($row['callsign']);
		switch ($mode) {
			case 0:				// not mobile
?>			
				do_sidebar ("<?php print str_replace($eols, " ", $sidebar_line); ?>", i, <?php print $row_tr['source'] ;?>);
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
			    break;
			case 1:				// stopped
			case 2:				// moving
			case 3:				// fast
?>			
				do_sidebar ("<?php print str_replace($eols, " ", $sidebar_line); ?>", i);
<?php			
				$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $last['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $last['course'] . ", Speed:  " . $last['speed'] . ", Alt: " . $last['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $last['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $last['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($last['packet_date']) . "(UTC)</TD></TR>";	//	6/16/08 
				$tab_2 .= "</TABLE>";
?>

				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("<?php print $last['source']; ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php			
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
				var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i);	// (point,tabs, color, id)
				map.addOverlay(marker);
				}
			i++;				// zero-based
<?php

		}				// end major while ($row = ...) for each responder
		$aprs_legend = ($aprs)? "<TD CLASS='aprs' ALIGN='center'>APRS time</TD>": "<TD></TD>";

?>
	if (!points) {		// any?
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds)-1;
		map.setCenter(center,zoom);
		}
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=6>&nbsp;</TD></TR>";
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD><?php print $aprs_legend;?></TR>";
<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
//	$temp = get_variable('aprs_poll');
//	$aprs_but = (intval($temp>0))? "<TR><TD COLSPAN=99 ALIGN='center'><INPUT TYPE='button' value= 'APRS'  onClick ='do_aprs_window();'></TD></TR>": "";
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

$interval = intval(get_variable('aprs_poll'));
$refresh = ($interval>0)? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";	//10/4/08

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Tracks Module</TITLE>
	<?php print $refresh; ?>	<!-- 10/4/08 -->

<?php
	$temp = get_variable('aprs_poll');
	if (intval($temp)>0) {
		print "\t<META HTTP-EQUIV='refresh' CONTENT='" . 60*$temp . "'>\n";
		}
?>	
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>

<?php
	print "<SCRIPT>\n";
	print "var user = '";
	print $my_session['user_name'];
	print "'\n";
	print "\nvar level = '" . get_level_text ($my_session['level']) . "'\n";
?>	
	parent.frames["upper"].document.getElementById("whom").innerHTML  = user;
	parent.frames["upper"].document.getElementById("level").innerHTML  = level;
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print basename( __FILE__);?>";

	function ck_frames() {		// ck_frames()
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
	</SCRIPT>

</SCRIPT>
	</HEAD>
	<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<TABLE ID='outer'><TR CLASS='even'><TD ALIGN='center' colspan=2><B><FONT SIZE='+1'>Mobile Unit Tracks</FONT></B></TD></TR><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
<?php
		print get_icon_legend ();				// 1/24/09
?>
			</TD></TR></TABLE><!-- end outer -->
			
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
							<!-- END RESPONDER LIST and ADD -->
<?php
		print list_responders("", 0);
		print "\n</BODY></HTML> \n";

?>