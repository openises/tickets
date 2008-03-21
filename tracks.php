<?php 
require_once('functions.inc.php');
do_login(basename(__FILE__));
$api_key = get_variable('gmaps_api_key');

//	require_once('config.inc.php');
//	require_once('responders.php');
//	foreach ($_POST as $VarName=>$VarValue) {echo "POST:$VarName => $VarValue, <BR />";};
//	foreach ($_GET as $VarName=>$VarValue) 	{echo "GET:$VarName => $VarValue, <BR />";};
//	echo "<BR/>";

extract($_GET);

function list_responders($addon = '', $start) {
global $my_session;
?>
<SCRIPT>


	if (parent.frames["upper"]) {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	var color=0;
	var colors = new Array ('odd', 'even');

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

	function create_track_Marker(point,html, mytype, ender) {
		switch (mytype){
			case 1:
				var marker = new GMarker(point, starticon);	
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
					});
				break;
			case ender:
				var marker = new GMarker(point, endicon);	
					GEvent.addListener(marker, "click", function() {
						marker.openInfoWindowHtml(html);
						});
				break;
			default : 
				var marker = new GMarker(point, infoicon);	
					GEvent.addListener(marker, "click", function() {
						marker.openInfoWindowHtml(html);
						});
				}
		return marker;
		}
	function createMarker(point,tabs, color, id) {				// Creates marker and sets up click event infowindow
		points = true;											// at least one
		var icon = new GIcon(listIcon);
		icon.image = icons[color] + ((id % 100)+1) + ".png";	// e.g.,marker9.png, 100 icons limit
		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide - unused

		GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();
			marker.openInfoWindowTabsHtml(infoTabs[id]);
			var dMapDiv = document.getElementById("detailmap");
			var detailmap = new GMap2(dMapDiv);
			detailmap.addControl(new GSmallMapControl());
			detailmap.setCenter(point, 13);  					// larger # = closer
			detailmap.addOverlay(marker);
			});

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		bounds.extend(point);									// extend the bounding box
		return marker;
		}				// end function create Marker()
		
	function do_sidebar (sidebar, id) {
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (id+1) + ". "+ sidebar +"</TD></TR>\n";
		}

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (line_no+1) + ". "+ sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
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

	var icons=[];						// note globals
	icons[1] = "./markers/YellowIcons/marker";		//e.g.,marker9.png
	icons[2] = "./markers/RedIcons/marker";
	icons[3] = "./markers/BlueIcons/marker";
	icons[4] = "./markers/GreenIcons/marker";		//	BlueIcons/GreenIcons/YellowIcons/RedIcons

	var infoicon = new GIcon();
	infoicon.image = "./markers/dot.png";
	infoicon.iconSize = new GSize(8, 8);
	infoicon.iconAnchor = new GPoint(4, 4);

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
	side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Units</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for information</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Description</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = k = 0;			// sidebar/icon index, track point index
	var points = false;								// none

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>

	var bounds = new GLatLngBounds();						// create  bounding box
	map.addControl(new GOverviewMapControl());
	map.enableScrollWheelZoom(); 	

	var listIcon = new GIcon();
	listIcon.image = "./markers/yellow.png";	// yellow.png - 16 X 28
	listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new GSize(20, 34);
	listIcon.shadowSize = new GSize(37, 34);
	listIcon.iconAnchor = new GPoint(8, 28);
	listIcon.infoWindowAnchor = new GPoint(9, 2);
	listIcon.infoShadowAnchor = new GPoint(18, 25);

	var newIcon = new GIcon();
	newIcon.image = "./markers/white.png";	// yellow.png - 20 X 34
	newIcon.shadow = "./markers/shadow.png";
	newIcon.iconSize = new GSize(20, 34);
	newIcon.shadowSize = new GSize(37, 34);
	newIcon.iconAnchor = new GPoint(8, 28);
	newIcon.infoWindowAnchor = new GPoint(9, 2);
	newIcon.infoShadowAnchor = new GPoint(18, 25);

	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

	GEvent.addListener(map, "click", function(marker, point) {
//		if (marker) {
//			document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=false;
//			document.forms[0].frm_lat.value=document.forms[0].frm_lng.value="";
//			document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=true;
//			}
//		if (point) {				// new - ADD
//			myZoom = map.getZoom();
//			map.clearOverlays();
//			do_lat (point.lat())							// display
//			do_lng (point.lng())
//			map.setCenter(point, myZoom);		// panTo(center)
//			map.panTo(point);				// panTo(center)
//			if (document.forms[0].frm_zoom) {				// get zoom?
//				document.forms[0].frm_zoom.disabled = false;
//				document.forms[0].frm_zoom.value = myZoom;
//				document.forms[0].frm_zoom.disabled = true;
//				}
//			marker = new GMarker(point, {icon: newIcon, draggable:true});
//			map.addOverlay(marker);
//
//			}
		});				// end GEvent.addListener() "click"

<?php

	$types = array();	$types[$GLOBALS['TYPE_EMS']] = "EMS";	$types[$GLOBALS['TYPE_FIRE']] = "Fire";
						$types[$GLOBALS['TYPE_COPS']] = "Police";	$types[$GLOBALS['TYPE_MUTU']] = "Mutual";	$types[$GLOBALS['TYPE_OTHR']] = "Other";

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile`=1 and `lat`=0";
	$result = mysql_query($query);
	
	$query = "SELECT `id`, `status_val` FROM `$GLOBALS[mysql_prefix]un_status`";		// build unit status array
	$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$status_vals[0]="TBD";
	while ($temp_row = mysql_fetch_array($temp_result)) {					// build array of values
		$status_vals[$temp_row['id']]=$temp_row['status_val'];
		}	

	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 

		// major while ... for RESPONDER data starts here
							
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$toedit = (is_guest())? "" : "<A HREF='config.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
		if (!$row['mobile']==1) {							// for fixed units
			$mode = ($row['lat']==0)? 4 :  0;				//  toss invalid lat's
?>
		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// mobile position

<?php
			}
		else {			// is mobile, do infowin, etc.
//			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
//				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` ";		
			$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` ,`packet_date`, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks WHERE `source` = '" .$row['callsign'] . "' ORDER BY `updated`";
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()> 1 ) {
	?>
				var j=1;				// point counter this unit
				var ender = <?php print mysql_affected_rows(); ?> ;
<?php
				$last = "";
				while ($row_tr = stripslashes_deep(mysql_fetch_array($result_tr))) {
					if (!empty($last)) {
?>			
						var polyline = new GPolyline([
						    new GLatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>),		// prior point
						    new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>)	// current point
							], "#FF0000", 2);
						map.addOverlay(polyline);
						bounds.extend(new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>));	// all points to bounding box
						var point = new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>);
						var html = "<b><?php print $row_tr['source'];?></b><br /><br /><?php print format_date($row['updated']);?>";
		
					    var marker = create_track_Marker(point, html, j, ender);
					    map.addOverlay(marker);
	
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
					}				// end (mysql_affected_rows()> 1 )
				else {				// no track data, do sidebar only
					$mode = 4;			
					}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end mobile
//										common to all modes
		$the_bull = ($mode == 0)? "" : "<FONT COLOR=" . $bulls[$mode] ."><B>&bull;</B></FONT>";
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
			
		$sidebar_line = "<TD>" . shorten($row['name'], 30) . "</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 16) . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . shorten($status_vals[$row['un_status_id']], 16) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . format_sb_date($row['updated']) . "</TD>";
?>

		var do_map = true;		// default
		
<?php
		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$row['un_status_id']] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>Details:&nbsp;&nbsp;&nbsp;&nbsp;" . $toedit . "<A HREF='config.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "</TABLE>";

		switch ($mode) {
			case 0:				// not mobile
?>			
				do_sidebar ("<?php print str_replace($eols, " ", $sidebar_line); ?>", i);
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
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($last['packet_date']) . "</TD></TR>";
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
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
</SCRIPT>
<?php
	}				// end function list_responders() ===========================================================
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
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
		}		// end function ck_frames()
	</SCRIPT>

</SCRIPT>
	</HEAD>
	<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<TABLE ID='outer'><TR CLASS='even'><TD ALIGN='center' colspan=2><B><FONT SIZE='+1'>Unit Tracks</FONT></B></TD></TR><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
				EMS: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Mutual:		<IMG SRC = './markers/sm_white.png' BORDER=0>&nbsp;&nbsp;
				Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
			</TD></TR></TABLE><!-- end outer -->
			
			<FORM NAME='view_form' METHOD='get' ACTION='config.php'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='view' VALUE='true'>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			</FORM>
			
			<FORM NAME='to_add_form' METHOD='get' ACTION='config.php'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='add' VALUE='true'>
			</FORM>
			
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php?func=responder"></FORM>
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print list_responders("", 0);
		print "\n</HTML> \n";

?>