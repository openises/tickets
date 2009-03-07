<?php

function list_responders($addon = '', $do_tracks) {
	global $u_types, $my_session;

	$assigns = array();					// 08/8/3
	$tickets = array();					// ticket id's
	
	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);
	$calls = array();									// 6/17/08
	$calls_nr = array();
	$calls_time = array();
	
	$query = "SELECT * , UNIX_TIMESTAMP(packet_date) AS `packet_date` FROM `$GLOBALS[mysql_prefix]tracks` ORDER BY `packet_date` ASC";		// 6/17/08
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while ($row = mysql_fetch_array($result)) {
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

	$query = "SELECT `id`FROM `$GLOBALS[mysql_prefix]responder`";		// 12/17/08
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$units = mysql_affected_rows()>0 ?  mysql_affected_rows(): "<I>none</I>";
	unset($result);

?>

<SCRIPT >

	var color=0;
	var colors = new Array ('odd', 'even');

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
		elem = $("allIcons");
		elem.style.visibility = "visible";
		}			// end function

	function showAll() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		elem = $("allIcons");
		elem.style.visibility = "hidden";

		}			// end function
//																		// Creates marker and sets up click event infowindow
	function createMarker(point,tabs, color, id) {						// (point, myinfoTabs,<?php print $row['type'];?>, i)
		points = true;													// at least one
		var letter = String.fromCharCode("A".charCodeAt(0) + id);		// start with A - 1/5/09
		
		var icon = new GIcon(listIcon);
		var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;				// 1/5/09

		icon.image = icon_url;		// ./icons/gen_icon.php?blank=4&text=zz"

		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide - unused

		GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
			if (marker) {			
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);
	
				setTimeout(function() {										// wait for rendering complete - 12/17/08
					if ($("detailmap")) {				
						var dMapDiv = $("detailmap");
						var detailmap = new GMap2(dMapDiv);
						detailmap.addControl(new GSmallMapControl());
						detailmap.setCenter(point, 17);  						// larger # = closer
						detailmap.addOverlay(marker);
						}
					else {
	//					alert(62);
	//					alert($("detailmap"));
						}
					},4000);				// end setTimeout(...)
					
				}		// end if (marker)
			});			// end GEvent.addListener()

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		bounds.extend(point);									// extend the bounding box
		return marker;
		}				// end function create Marker()
		
	function do_sidebar (sidebar, id) {
		var letter = String.fromCharCode("A".charCodeAt(0) + id);								// start with A - 1/5/09

		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/5/09
		}

	function do_sidebar_nm (sidebar, line_no, id) {							// no map - view responder // view_Form
		var letter = String.fromCharCode("A".charCodeAt(0) + line_no);							// start with A - 1/5/09
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/23/09
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		GEvent.trigger(gmarkers[id], "click");
		}

	function do_lat (lat) {
		document.forms[0].frm_lat.value=lat.toFixed(6);
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);
		}
	
	function do_ngs() {						// LL to USNG into form
		document.forms[0].frm_ngs.disabled=false;
		document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
		document.forms[0].frm_ngs.disabled=true;
		}

	var icons=new Array;							// maps type to icon blank

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


	var map;
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
	side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Units (<?php print $units; ?>)</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for details - or to dispatch</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Description</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = 0;									// sidebar/icon index
	var points = false;								// none

	map = new GMap2($("map"));						// create the map
	map.addControl(new GSmallMapControl());					// 10/6/08
	map.addControl(new GMapTypeControl());
//	map.addMapType(G_PHYSICAL_MAP);							// 10/6/08
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	


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

	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

<?php
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);
	
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$aprs = FALSE;
	$i=0;				// counter
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for RESPONDER ==========
		$todisp = (is_guest())? "": "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
		$toedit = (is_guest())? "" :"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 10/8/08
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U></SPAN>" ;

		$mobile = (($row['mobile']==1) && ($row['aprs']==1) && ($row['callsign'] != ''));				// 1/23/09

		if (!($mobile)) {													// 1/23/09
			$mode = 0;																			// for non-aprs units
			print "\t\tvar point = new GLatLng(" . $row['lat'] .", " .  $row['lng'] . ");\n";	// fixed position
			print "\t\tpoints=true;\n";
			}			// end fixed
		else {								// is mobile, any tracks?
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if (mysql_affected_rows()>0) {		// got track stuff. do tab 2 and 3
				$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));
				$mode = ($rowtr['speed'] == 0)? 1: 2 ;
				if ($rowtr['speed'] >= 50) { $mode = 3;}
				print "\t\tvar point = new GLatLng(" . $rowtr['latitude'] . ", " . $rowtr['longitude'] . ");\n";	// latest mobile position
				print "\t\tpoints=true;\n";			
				}				// end got tracks 
			else {				// aprs unit but no track data
				$mode = 4;			
				print "\t\tvar point = new GLatLng(" . $row['lat'] .", " .  $row['lng'] . ");\n";	// 1/23/09
				print "\t\tpoints=true;\n";
				}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end aprs
//										common to all modes

		$the_bull = ($mode == 0)? "" : "<FONT COLOR=" . $bulls[$mode] ."><B>&bull;</B></FONT>";
			
		$sidebar_line = "<TD TITLE = '" . htmlentities ($row['name'], ENT_QUOTES) . "'>" . shorten($row['name'], 30) . "</TD><TD TITLE = '" . htmlentities ($row['description'], ENT_QUOTES) . "' >" . shorten(str_replace($eols, " ", $row['description']), 16) . "</TD>"; 	// 8/24/08
		$temp = $row['un_status_id'];

		$sidebar_line .= "<TD CLASS='td_data' TITLE = '" . htmlentities ($status_vals[$temp], ENT_QUOTES) . "'> " . shorten($status_vals[$temp], 10) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";

		if (isset($calls[$row['callsign']])) {
			$the_time = $calls_time[$row['callsign']];
			$the_class = "emph";
			$aprs = TRUE;				// show footer legend
			}
		else {
			$the_time = $row['updated'];
			$the_class = "td_data";
			}
		
		$sidebar_line .= "<TD CLASS='$the_class'> " . format_sb_date($the_time) . "</TD>";				// 6/17/08

		print "\tvar do_map = true;\n";		// default
		$temptype = $u_types[$row['type']];
		$the_type = $temptype[0];																			// 1/1/09
		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$temp] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		if (array_key_exists($row['id'], $assigns)) { 
			$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
			}		
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $todisp . $totrack . $toedit . "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
		$tab_1 .= "</TABLE>";

		switch ($mode) {
			case 0:				// not mobile
?>			
				do_sidebar ("<?php print $sidebar_line; ?>", i);
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
				do_sidebar ("<?php print $sidebar_line; ?>", i);
<?php			
				$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $rowtr['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $rowtr['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $rowtr['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($rowtr['packet_date']) . "(UTC)</TD></TR>";
				$tab_2 .= "</TABLE>";
?>

				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("<?php print $rowtr['source']; ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php			
			    break;
			case 4:				// mobile - no track
?>
				do_sidebar_nm ("<?php print $sidebar_line; ?>", i, <?php print $row['id'];?>);	// special sidebar link - adds id for view
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
		$i++;				// zero-based
		}				// end  ==========  while() for RESPONDER ==========

	$aprs_legend = ($aprs)? "<TD CLASS='emph' ALIGN='center'>APRS time</TD>": "<TD></TD>";
?>
	if (!points) {		// any?
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		center = bounds.getCenter();
		zoom = (map.getBoundsZoomLevel(bounds)>10)? 10: map.getBoundsZoomLevel(bounds)-1 ;		// tightest zoom is 10 - 12/25/08
		map.setCenter(center,zoom);
		}
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=5>&nbsp;</TD><?php print $aprs_legend;?></TR>";
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
<?php

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div

<?php
	do_kml();
?>


</SCRIPT>
<?php
	}				// end function list_responders() ===========================================================
?>