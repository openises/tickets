<?php
/*
original, converted from tracks.php
10/4/08	added auto-refresh
10/4/08	corrected to include all point into bounding box
10/4/08	added direction icons
 */
require_once('./incs/functions.inc.php');
//do_login(basename(__FILE__));		// in a window
extract($_GET);

$api_key = get_variable('gmaps_api_key');

function list_tracks($addon = '', $start) {
global $source, $my_session, $evenodd;
?>
<SCRIPT>
	var direcs=new Array("north.png","northeast.png","east.png","southeast.png","south.png","southwest.png","west.png","northwest.png", "north.png");	// 10/4/08
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
//								(point, html, node_type, heading)
	function create_track_Marker(point, html, node_type, heading) {
//		alert(node_type);
		switch (node_type){
			case 1:				// start node
//				alert(51);
				var marker = new GMarker(point, starticon);	
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
					});
				break;
			case 0:				// end node
//				alert(57);
				var marker = new GMarker(point, endicon);	
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
					});
				break;
			default : 			// in between nodes
//				alert("65 " + heading);
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
	function createMarker(point,tabs, color, id) {				// Creates marker and sets up click event infowindow
//		alert(69);

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
//		bounds.extend(point);									// extend the bounding box - removed 5/26/08
		return marker;
		}				// end function create Marker()
		
	function do_sidebar (sidebar, id) {
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + sidebar +"</TD></TR>\n";
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

//	var icons=[];						// note globals
//	icons[1] = "./markers/YellowIcons/marker";		//e.g.,marker9.png
//	icons[2] = "./markers/RedIcons/marker";
//	icons[3] = "./markers/BlueIcons/marker";
//	icons[4] = "./markers/GreenIcons/marker";		//	BlueIcons/GreenIcons/YellowIcons/RedIcons


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
	side_bar_html +="<TR><TD ALIGN='center' COLSPAN=99>Mouseover for details</TD></TR>";

	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index, track point index
	var points = false;								// none

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>

	var bounds = new GLatLngBounds();						// create  bounding box
//	map.addControl(new GOverviewMapControl());
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
<?php

//	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 
	$toedit = "";
	$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` , `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" .$source . "' ORDER BY `packet_date`";	//	6/16/08 
	$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$sidebar_line = "<TABLE border=0>\n";
	if (mysql_affected_rows()> 1 ) {
?>
		var j=0;				// point counter this unit
		var ender = <?php print mysql_affected_rows(); ?> ;
<?php
		$last = $day = "";
		$i=0;
		
		while ($row_tr = stripslashes_deep(mysql_fetch_array($result_tr))) {
			if (substr($row_tr['packet_date'] ,  0,  10) != $day) {
				$day = substr ($row_tr['packet_date'] ,  0,  10);
				$sidebar_line .="<TR CLASS='" . $evenodd[$i%2] . "'><TD COLSPAN=99><U>" . $day . "</U></TD></TR>\n";
				$i++;
				}
			
			$sidebar_line .="<TR CLASS='" . $evenodd[$i%2] . "'>";
			$sidebar_line .= "<TD TITLE='" . $row_tr['packet_date'] . "'>" .  	substr ($row_tr['packet_date'] , 11, 5) ."</TD>\n";
			$sidebar_line .= "<TD TITLE='" . $row_tr['latitude']. ", ". $row_tr['longitude'] . "'>" . shorten($row_tr['latitude'], 8) ."</TD>\n";
			$sidebar_line .= "<TD>" . $row_tr['speed']."@" . $row_tr['course'] . "</TD>\n";
			$sidebar_line .= "<TD TITLE='" . $row_tr['closest_city'] . "'>" .  	shorten($row_tr['closest_city'], 16) ."</TD>\n";
			$sidebar_line .="</TR>\n";
?>
			j++;
			var point = new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>);
			var html = "<b><?php print $row_tr['source'];?></b><br /><br /><?php print format_date($row_tr['updated']);?>";
			var heading = Math.round(<?php print intval($row_tr['course']);?>/45);		// 10/4/08
//			alert("230 " + heading);

			if (j== ender) 	{node_type=0;}														// signifies last node 10/4/08
			else 			{node_type=j;};														// other than last
			var marker = create_track_Marker(point, html, node_type,  heading);
			map.addOverlay(marker);
			bounds.extend(new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>));	// 10/4/08  all points to bounding box
<?php
			if (!empty($last)) {
?>		
				var polyline = new GPolyline([
				    new GLatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>),		// prior point
				    new GLatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>)	// current point
					], "#FF0000", 2);
				map.addOverlay(polyline);
				points++;
<?php
				}		// end if (!empty($last))
			$last = $row_tr;										// either way 
			$i++;
			}		// end while ($row_tr...)
	
			$mode = ($last['speed'] == 0)? 1: 2 ;
			if ($last['speed'] >= 50) { $mode = 3;}
?>
			var point = new GLatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>);	// mobile position
<?php
			}				// end (mysql_affected_rows()> 1 )

		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
?>

		var do_map = true;		// default
		do_sidebar ("<?php print str_replace($eols, "", $sidebar_line); ?>", i);		// as single string
		var do_map = false;
		if (do_map) {
			var marker = createMarker(point, myinfoTabs,2, i);	// (point,tabs, color, id)
			map.addOverlay(marker);
			}
		i++;				// zero-based
<?php

//		}				// end major while ($row_tr = ...) for each track
?>
	if (!points) {		// any?
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds);
		map.setCenter(center,zoom);
		}
<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
	
<?php
	do_kml();		// generate KML JS - added 5/23/08
	print "\n</SCRIPT>\n";
	}				// end function list_tracks() ===========================================================

$interval = intval(get_variable('aprs_poll'));
$refresh = ($interval>0)? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";	//10/4/08

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - <?php print $source; ?> Tracks</TITLE>

<?php print $refresh; ?>	<!-- 10/4/08 -->
	
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>

<?php
	print "<SCRIPT>\n";
//	print "var user = '";
//	print $my_session['user_name'];
//	print "'\n";
//	print "\nvar level = '" . get_level_text ($my_session['level']) . "'\n";
?>	
//	parent.frames["upper"].document.getElementById("whom").innerHTML  = user;
//	parent.frames["upper"].document.getElementById("level").innerHTML  = level;
//	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print basename( __FILE__);?>";

	function ck_frames() {		// ck_frames()
//		if(self.location.href==parent.location.href) {
//			self.location.href = 'index.php';
//			}
		}		// end function ck_frames()
	</SCRIPT>

</SCRIPT>
	</HEAD>
	<BODY onLoad = "ck_frames()" onunload="GUnload()">
	<A NAME='top'>
		<TABLE ID='outer'><TR CLASS='even'><TD ALIGN='center' colspan=2><B><FONT SIZE='+1'>Mobile Unit <?php print $source;?> Tracks</FONT></B></TD></TR><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR><BR>
			<CENTER><SPAN onClick = 'self.close()'><B><U>Close</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="javascript:location.reload(true)"><B><U>Refresh</U>
			</TD></TR>
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
						<!-- END RESPONDER LIST and ADD -->
<?php
		print list_tracks("", 0);
		$alt_urlstr =  "./incs/alt_graph.php?p1=" . urlencode($source) ;		// 7/18/08  Call sign for altitude graph
?>

<BR /><HR ALIGN='center' SIZE=1 COLOR='blue' WIDTH='75%'><BR />
<CENTER><img src="<?php print $alt_urlstr;?>" border=0 />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br><br>
<CENTER><A HREF='#top'><U>to top</U></A>
<?php

		print "\n</BODY></HTML>\n";

?>