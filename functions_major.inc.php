<?php
// 6/9/08  added  'Closed Calls' button
//	{ -- dummy

function list_tickets($sort_by_field='',$sort_value='') {	// list tickets ===================================================
	global $my_session;
//	SELECT ticket.*, notify.id AS nid FROM ticket LEFT JOIN notify ON ticket.id=notify.ticket_id		WORKS

	$get_status = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['status'])))) ) ? "" : $_GET['status'] ;
	$get_sortby = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['sortby'])))) ) ? "" : $_GET['sortby'] ;
	$get_offset = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['offset'])))) ) ? "" : $_GET['offset'] ;

	$closed = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_CLOSED']))? "Closed" : "";
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

?>
<TABLE BORDER=0>
	<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'>Current <?php print $closed; ?> Call Tickets</FONT></TD></TR>
	<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>
	<TR><TD VALIGN='TOP' width='400px' ><DIV ID='side_bar'></DIV></TD>			
		<TD></TD>			
		<TD CLASS='td_label'>
			<DIV ID='map' STYLE='WIDTH: <?php print get_variable('map_width');?>PX; HEIGHT: <?php print get_variable('map_height');?>PX'></DIV>	

		<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />
		Units: <A HREF="#" onClick = "hideGroup(0)">	<IMG SRC = './markers/sm_yellow.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Incident Priority:&nbsp;&nbsp;&nbsp;&nbsp;
		Typical: <A HREF="#" onClick = "hideGroup(1)">	<IMG SRC = './markers/sm_blue.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		High: <A HREF="#" onClick = "hideGroup(2)">		<IMG SRC = './markers/sm_green.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		Highest: <A HREF="#" onClick = "hideGroup(3)">	<IMG SRC = './markers/sm_red.png' BORDER=0></A>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN ID="allIcons" STYLE="visibility: hidden">Show all: <A HREF="#" onClick = "showAll()"><IMG SRC = './markers/sm_white.png' BORDER=0></A></CENTER><BR /></TD>
	</TR>

	<TR><TD COLSPAN='99'> </TD></TR>
	<TR><TD CLASS='td_label' COLSPAN=3 ALIGN='center'>
		&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:shoreas@Gmail.com?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		</TD></TR></TABLE>
	<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
	<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
	<INPUT TYPE='hidden' NAME='view' VALUE='true'>
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

<SCRIPT>

if (GBrowserIsCompatible()) {

//	document.getElementById("map").style.backgroundImage = "url(./markers/loading.jpg)";
	document.getElementById("map").style.backgroundImage = "url('http://maps.google.com/staticmap?center=<?php echo get_variable('def_lat');?>,<?php echo get_variable('def_lng');?>&zoom=<?php echo get_variable('def_zoom');?>&size=<?php echo get_variable('map_width');?>x<?php echo get_variable('map_height');?>&key=<?php echo get_variable('gmaps_api_key');?> ')";

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

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (line_no) + ". "+ sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
//		alert (151);
//		alert (v_id);
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
//		alert (157);
//		alert (id);
		GEvent.trigger(gmarkers[id], "click");
		}

	function do_sidebar (instr, id) {								// constructs sidebar row
		side_bar_html += "<TR CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='td_label'>" + (id) + ". "+ instr +"</TD></TR>\n";
		}		// end function do_sidebar ()

	function createMarker(point, tabs, color, id) {					// Creates marker and sets up click event infowindow
//		alert (color);
		points = true;
		var icon = new GIcon(baseIcon);
		icon.image = icons[color] + ((id % 100)) + ".png";			// e.g.,marker9.png, 100 icons limit
//		alert(color + " " + icon.image);
		var marker = new GMarker(point, icon);	
		marker.id = color;				// for hide/unhide
		
		GEvent.addListener(marker, "click", function() {			// here for both side bar and icon click
//			alert(178);
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();			
			marker.openInfoWindowTabsHtml(infoTabs[id]);
//			alert(183);
			var dMapDiv = document.getElementById("detailmap");
			var detailmap = new GMap2(dMapDiv);
			detailmap.addControl(new GSmallMapControl());
			detailmap.setCenter(point, 12);  						// larger # = closer
			detailmap.addOverlay(marker);
			});

		gmarkers[id] = marker;							// marker to array for side_bar click function
		infoTabs[id] = tabs;							// tabs to array
		
		bounds.extend(point);										// extend the bounding box
		
		return marker;
		}				// end function create Marker()
	function doGrid() {
		map.closeInfoWindow();
		map.addOverlay(new LatLonGraticule());
		}	
		
	var icons=[];						// note globals
	icons[0] = 											   "./markers/YellowIcons/marker";	// Yellow units
	icons[<?php print $GLOBALS['SEVERITY_NORMAL']; ?>+1] = "./markers/BlueIcons/marker";	
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM']; ?>+1] = "./markers/GreenIcons/marker";
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =   "./markers/RedIcons/marker";		
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+2] =   "./markers/WhiteIcons/marker";

	var map;
	var center;
	var zoom;
	var points = false;

<?php

$kml_olays = array();
$dir = "./kml_files";
$dh  = opendir($dir);
$i = 1;
$temp = explode ("/", $_SERVER['REQUEST_URI']);
$temp[count($temp)-1] = "kml_files";				// 
$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
while (false !== ($filename = readdir($dh))) {
	if (!is_dir($filename)) {
	    echo "\tvar kml_" . $i . " = new GGeoXml(\"" . $server_str . $filename . "\");\n";
	    $kml_olays[] = "map.addOverlay(kml_". $i . ");";
	    $i++;
	    }
	}
//	dump ($kml_olays);
?>

	var side_bar_html = "<TABLE border=0 CLASS='sidebar'>";
	side_bar_html += "<tr class='even'><td colspan=99 align='center'>Click for information</td></tr>";
	side_bar_html += "<tr class='odd'><td></td><td align='center'><B>Incident</B></td><td align='center'><B>Type</B></td><td>P</td><td>A</td><td align='center'>As of</td></tr>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);	
	
	var bounds = new GLatLngBounds();						// create  bounding box
//	map.addControl(new GOverviewMapControl());
	map.addMapType(G_PHYSICAL_MAP);
	map.enableScrollWheelZoom(); 	

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);
	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
//		alert (center);
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])		
		});	
				 
<?php
//	dump ($my_session);
	$get_status = (!empty ($get_status))? $get_status : $GLOBALS['STATUS_OPEN'];			 						 // default to show all open tickets
//	$order_by =  ((!empty ($_GET)) && ($_GET['sortby'] == ''))? $my_session['sortorder']: $order_by = $_GET['sortby']; // use default sort order?
	$order_by =  (!empty ($get_sortby))? $get_sortby: $my_session['sortorder']; // use default sort order?
//	dump ($my_session['sortorder']);
																			//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($my_session['ticket_per_page'] && (check_for_rows("SELECT id FROM $GLOBALS[mysql_prefix]ticket") > $my_session['ticket_per_page']))	{
		if ($_GET['offset']) {
			$limit = "LIMIT $_GET[offset],$my_session[ticket_per_page]";
			}
		else {
			$limit = "LIMIT 0,$my_session[ticket_per_page]";
			}
		}
	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$my_session[user_id]" : "";
	$where = ($get_status==2)? " WHERE `status`='2' OR (`status`='1'  AND `problemend` > (NOW() - INTERVAL 24 HOUR)) ": " WHERE `status`='1' ";

	if ($sort_by_field && $sort_value) {					//sort by field?
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, in_types.type AS `type`, in_types.id AS `t_id` FROM $GLOBALS[mysql_prefix]ticket LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON $GLOBALS[mysql_prefix]ticket.in_types_id=in_types.id  WHERE $sort_by_field='$sort_value' $restrict_ticket ORDER BY $order_by";
		}
	else {
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, $GLOBALS[mysql_prefix]in_types.type AS `type`, $GLOBALS[mysql_prefix]in_types.id AS `t_id` FROM $GLOBALS[mysql_prefix]ticket LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON $GLOBALS[mysql_prefix]ticket.in_types_id=$GLOBALS[mysql_prefix]in_types.id $where $restrict_ticket ORDER BY $order_by $limit";
		}
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							// major while ... starts here
							
	while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
//		dump ($row['id']);
//		dump ($row[0]);
		$the_id = $row[0];

		if ($row['description'] == '') $row['description'] = '[no description]';
		if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
			if (strlen($row['description']) > get_variable('abbreviate_description')) {
				$row['description'] = substr($row['description'],0,get_variable('abbreviate_description')).'...';
				}
			}
		if (get_variable('abbreviate_affected')) {
			if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
				$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
				}
			}
		switch($row['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 							$severityclass=''; break;
			}
		
		$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
		
		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>$street</TD></TR>";
		$utm = get_variable('UTM');
		if ($utm==1) {
			$coords =  $row['lat'] . "," . $row['lng'];
			$tab_1 .= "<TR CLASS='even'><TD>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>";
			}
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>";
		$tab_1 .= 	"&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Show details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
		if (!(is_guest() && get_variable('guest_add_ticket')==0)) {			
			$tab_1 .= 	"<A HREF='patient.php?ticket_id=" . $the_id."'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tab_1 .= 	"<A HREF='action.php?ticket_id=" . $the_id . "'><U>Add Action</U></A>&nbsp;&nbsp;";
			}
		$tab_1 .= 	"</TD></TR><TABLE>";
		

		$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_2 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 120) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
		$tab_2 .= "<TR CLASS='odd'><TD>Comments:</TD><TD>" . shorten($row['comments'], 120) . "</TD></TR>";
		$tab_2 .= "<TR><TD>&nbsp;</TD></TR>";
		$tab_2 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'>";
		$tab_2 .= 	"&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Show details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
		if (!(is_guest() && get_variable('guest_add_ticket')==0)) {			
			$tab_2 .= 	"<A HREF='patient.php?ticket_id=" . $the_id . "'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tab_2 .= 	"<A HREF='action.php?ticket_id=" . $the_id . "'><U>Add Action</U></A>&nbsp;&nbsp;";
			}
		$tab_2 .= 	"</TD></TR><TABLE>";
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `ticket_id` = " . $the_id;
		$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$A = mysql_affected_rows();
		
		$query= "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE `ticket_id` = " . $the_id;
		$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$P = mysql_affected_rows ();

		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
			
		$sidebar_line = "<TD CLASS='$severityclass'><NOBR>$strike" . shorten($row['scope'], 20) . " $strikend</NOBR></TD>";
		$sidebar_line .= "<TD CLASS='$severityclass'><NOBR>$strike" . shorten($row['type'], 20) . " $strikend</NOBR></TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . $P . " </TD><TD CLASS='td_data'> " . $A . " </NOBR></TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . format_sb_date($row['updated']) . "</NOBR></TD>";
?>
		var myinfoTabs = [
			new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
			new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $tab_2);?>"),
			new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
			];

		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket	
		bounds.extend(point);																// point into BB
		i++;																				// step the index
	
		var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, i);	// (point,tabs, color, id) 
		do_sidebar ("<?php print $sidebar_line;?>", i)		
		map.addOverlay(marker);
<?php

		}				// end tickets while ($row = ...) start responders while ($row = ...)
?>
		side_bar_html += (i>0)? "": "<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'><BR /><B>No <?php print $closed; ?> tickets!</B><BR /><BR /></TD></TR>";
// ==============================================================================================================
		points = false;			
		i++

<?php
	$types = array();	$types[$GLOBALS['TYPE_EMS']] = "Medical";	$types[$GLOBALS['TYPE_FIRE']] = "Fire";
						$types[$GLOBALS['TYPE_COPS']] = "Police";	$types[$GLOBALS['TYPE_MUTU']] = "Mutual"; $types[$GLOBALS['TYPE_OTHR']] = "Other";
						$types['0'] = "error";

	$status_vals = array();				// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);
	
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder ORDER BY `name`";	//
//	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder WHERE `id` = 9999 ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	print (mysql_affected_rows()==0)? "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center' COLSPAN=99><B>No units!</B></TD></TR>\"\n" : "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center'><B>Unit</B></TD><TD ALIGN='center' COLSPAN=2><B>Status</B></TD><TD>M</TD><TD></TD></TR>\"\n" ;
	
	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");			// major while ... for RESPONDER data starts here
							
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$toedit = (is_guest())? "" : "<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
		$mobile = ($row['mobile']==1);
//		dump ($mobile);
		if (!$mobile) {
			$mode = ($row['lat']==0)? 4 :  0;				// valid?
?>
		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// mobile position

<?php
			}
		else {			// is mobile, do infowin
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if (mysql_affected_rows()>0) {		// got track stuff. do tab 2 and 3
				$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));
				$mode = ($rowtr['speed'] == 0)? 1: 2 ;
				if ($rowtr['speed'] >= 50) { $mode = 3;}
?>
				var point = new GLatLng(<?php print $rowtr['latitude'];?>, <?php print $rowtr['longitude'];?>);	// mobile position
<?php
				}				// end got tracks 
			else {				// no track data, do sidebar only
				$mode = 4;			
				}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end mobile
//										common to all modes
		$the_bull = ($mode == 0)? "" : "<B><FONT COLOR=" . $bulls[$mode] .">&bull;</FONT></B>";
			
		$sidebar_line = "<TD><NOBR>" . shorten($row['name'], 20) . "</NOBR></TD>";
		$temp = $row['un_status_id'];
		$sidebar_line .= "<TD COLSPAN=2><NOBR>" . shorten($status_vals[$temp], 10 ) . "</NOBR></TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . $the_bull . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . format_sb_date($row['updated']) . "</NOBR></TD>";
?>

		var do_map = true;		// default
		
<?php
		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$row['un_status_id']] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>Details:&nbsp;&nbsp;&nbsp;&nbsp;" . $toedit . "<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "<TABLE>";

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
				$tab_2 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $rowtr['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD>		<TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD>	<TD>" . $rowtr['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD>		<TD>" . $rowtr['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD>		<TD>" . format_date($rowtr['packet_date']) . "</TD></TR>";
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
//				alert(<?php print $row['type'];?>);
//				var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i);	// (point,tabs, color, id)
				var marker = createMarker(point, myinfoTabs,0, i);	// (point,tabs, color, id)	// yellow for responders
				map.addOverlay(marker);
				}
			i++;				// zero-based
<?php

		}				// end major while ($row = ...) for each responder
		
	for ($i = 0; $i<count($kml_olays); $i++) {				// emit kml overlay calls
		echo "\t\t" . $kml_olays[$i] . "\n";
		}
?>
//    map.addOverlay(North_Central);

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
	if (mysql_affected_rows()>0) {		
		print "\n\tside_bar_html+= \"<TR CLASS='\" + colors[i%2] +\"'><TD COLSPAN=99 ALIGN='center'>&nbsp;&nbsp;<B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'>&bull;</FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'>&bull;</FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'>&bull;</FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'>&bull;</FONT>&nbsp;&nbsp;</TD></TR>\";\n";
		}
	if(empty($closed)) {									// 6/9/08  added button
		print "\n\tvar button = \"<INPUT TYPE='button' VALUE='Closed Calls' onClick = 'document.to_closed.submit()'>\"\n";
		print "\n\tside_bar_html+= \"<TR><TD COLSPAN=99 ALIGN='center'><BR>\" + button + \"</TD></TR>\";\n";
		}
?>		
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div

// =============================================================================================================
	}		// end if (GBrowserIsCompatible())
else {
	alert("Sorry, browser compatibility problem. Contact your tech support group.");
	}
</SCRIPT>

<?php
	}				// end function list_tickets() ===========================================================
	//	} { -- dummy

function show_ticket($id,$print='false', $search = FALSE) {								/* show specified ticket */

	global $my_session, $istest;

	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}
	

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}
	
	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$my_session[user_id]" : "";

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]ticket WHERE ID='$id' $restrict_ticket";

//	dump ($query );

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">No such ticket or user access to ticket is denied</FONT>";
		exit();
		}
	
	$row = stripslashes_deep(mysql_fetch_array($result));

	$query = "SELECT *  FROM $GLOBALS[mysql_prefix]in_types WHERE `id`= $id";
	$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_type = stripslashes_deep(mysql_fetch_array($result_type));

	if ($print == 'true') {
//		print do_ticket($row, "800px", $search = FALSE) ;
	
		print "<TABLE BORDER='0' CLASS='print_TD' width='800px'>";		
		print "<TR><TD CLASS='print_TD'><B>Incident</B>:</TD>	<TD CLASS='print_TD'>" . $row['scope'].	"&nbsp;&nbsp;<I>(#" . $row['id'] . ")</I></TD></TR>\n"; 
		print "<TR><TD CLASS='print_TD'><B>Priority:</B></TD>	<TD CLASS='print_TD'>" . get_severity($row['severity']);
		print  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>Nature:</B>&nbsp;&nbsp;" . get_type($row['in_types_id']) . "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Written</B>:</TD>	<TD CLASS='print_TD'>" . format_date($row['date']) . "</TD></TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Updated</B>:</TD>	<TD CLASS='print_TD'>" . format_date($row['updated']) . "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Reported by</B>:</TD><TD CLASS='print_TD'>" . $row['contact'].	"</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Phone</B>:</TD>		<TD CLASS='print_TD'>" . format_phone($row['phone']) ."</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Status:</B></TD>		<TD CLASS='print_TD'>" . get_status($row['status'])."</TD></TR>\n";
		print "<TR><TD CLASS='print_TD' COLSPAN='2'></TD></TR>\n";

		print "<TR><TD CLASS='print_TD'><B>Address</B>:</TD>	<TD CLASS='print_TD'>" . $row['street']. "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>City</B>:</TD>		<TD CLASS='print_TD'>" . $row['city']. "&nbsp;&nbsp;&nbsp;&nbsp;<B>St</B>: " . $row['state'] . "</TD></TR>\n";
		print "<TR VALIGN='top'><TD CLASS='print_TD'>Description:</TD>	<TD>" .  nl2br($row['description']) . "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Comments:</B></TD>	<TD CLASS='print_TD'>" . nl2br ($row['comments']). "</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Owner:</B></TD>		<TD CLASS='print_TD'>" . get_owner($row['owner']). "</TD></TR>\n"; 
		print "<TR><TD CLASS='print_TD'><B>Issued:</B></TD>		<TD CLASS='print_TD'>" . format_date($row['date']). "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Run Start:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemstart']). "</TD></TR>";
		print "<TR><TD CLASS='print_TD'><B>Run End:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemend']).	"</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Affected:</B></TD>	<TD CLASS='print_TD'>" . $row['affected']. "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Map</B>:</TD>		<TD CLASS='print_TD'><B>Lat</B>: " . $row['lat']. "&nbsp;&nbsp;&nbsp;&nbsp; <B>Lon</B>: " . $row['lng'] . "</TD></TR>\n"; 

		print show_actions($row['id'], "date", FALSE, FALSE);		// lists actions and patient data, print
		
		print "</BODY></HTML>";
		return;
		}		// end if ($print == 'true')
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left" WIDTH="<?php print $my_session['scr_width']-32; ?>">
	<TR VALIGN="top"><TD CLASS="print_TD" ALIGN="left">
<?php

	print do_ticket($row, "500px", $search = FALSE) ;
	
	print "<TD ALIGN='left'>";
//	print "<TABLE ID='theMap'><TR CLASS='odd' ><TD  ALIGN='center'><DIV ID='map' STYLE='WIDTH:" . ($my_session['scr_width']-32)/2 . "px; HEIGHT: 450PX'></DIV><BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A></TD></TR></TABLE>\n";
	print "<TABLE ID='theMap'><TR CLASS='odd' ><TD  ALIGN='center'><DIV ID='map' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV><BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A></TD></TR></TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];	

	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "</TD></TR>\n";
//	print "<TR><TD ALIGN='left'>";
//	print show_log ($id);				// log as a table
//	print "</TD></TR></TABLE>\n";
	print "</TABLE>\n";
	
	
?>
	<SCRIPT SRC="graticule.js" type="text/javascript"></SCRIPT>
	<SCRIPT>

	function doGrid() {
		map.addOverlay(new LatLonGraticule());
		}
	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW
		
		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();
	
		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;
	var thisMarker = false;

	var map;
	var icons=[];						// note globals
	icons[<?php print $GLOBALS['SEVERITY_NORMAL']; ?>] = "./markers/BlueIcons/blank.png";	
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM']; ?>] = "./markers/GreenIcons/blank.png";
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =   "./markers/RedIcons/blank.png";
	
	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";
	
	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
//	map.addControl(new GOverviewMapControl());
	map.addMapType(G_PHYSICAL_MAP);
	
	map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>),14);
	var icon = new GIcon(baseIcon);
	icon.image = icons[<?php print $row['severity'];?>];		
	var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom(); 	

<?php
	$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;

	$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
	$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
	$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $street . " </TD></TR>";
	$tab_1 .= "<TABLE>";

	do_kml();			// kml functions

?>
	map.openInfoWindowHtml(point, "<?php print $tab_1;?>");		
	
	GEvent.addListener(map, "click", function(marker, point) {
		if (point) {
			map.clearOverlays();
			var thisMarker = new GMarker(point);
			map.addOverlay(thisMarker);
			document.getElementById("newlat").innerHTML = point.lat().toFixed(6);
			document.getElementById("newlng").innerHTML = point.lng().toFixed(6);
			
			var nlat = document.getElementById("newlat").innerHTML ;
			var nlng = document.getElementById("newlng").innerHTML ;
			var olat = document.getElementById("oldlat").innerHTML ;
			var olng = document.getElementById("oldlng").innerHTML ;
		
			var km=distCosineLaw(parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng));
			var dist = ((km * km2feet).toFixed(0)).toString();
			var dist1 = dist/5280;
			var dist2 = (dist>5280)? ((dist/5280).toFixed(2) + " mi") : dist + " ft" ;
			
			document.getElementById("range").innerHTML  = dist2;
			document.getElementById("brng").innerHTML = (brng (parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng)).toFixed(0)) + ' degr';

			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
			map.addOverlay(new GMarker(point, icon));
			var polyline = new GPolyline([
			    new GLatLng(nlat, nlng),
			    new GLatLng(olat, olng)
				], "#FF0000", 2);
			map.addOverlay(polyline);			
			}
			
		} )
	
	</SCRIPT>
<?php
	}				// end function show_ticket() =======================================================
//	} {		-- dummy
	
function do_ticket($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table

	global $my_session;

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass=''; break;
		}
	$print = "<TABLE BORDER='0'ID='left' width='" . $theWidth . "'>\n";		// 
	
	$print .= "<TR CLASS='even'><TD CLASS='td_data' COLSPAN=2 ALIGN='center'><B>Incident: <I>" . $theRow['scope'] . "</B>&nbsp;&nbsp;(#" . $theRow['id'] . ")</I></TD></TR>\n"; 
	$print .= "<TR CLASS='odd' ><TD>Priority:</TD> <TD CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nature:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD>Written:</TD>		<TD>" . format_date($theRow['date']) . "</TD></TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD>Updated:</TD>		<TD>" . format_date($theRow['updated']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD>Reported by:</TD>	<TD>" . $theRow['contact'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD>Phone:</TD>			<TD>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD>Status:</TD>		<TD>" . get_status($theRow['status']) . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD COLSPAN='2'>&nbsp;	<TD></TR>\n";			// separator
	$print .= "<TR CLASS='even' ><TD>Address:</TD>		<TD>" . highlight($search, $theRow['street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD>City:</TD>			<TD>" . highlight($search, $theRow['city']);
	$print .=	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;" . highlight($search, $theRow['state']) . "</TD></TR>\n";

	
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Description:</TD>	<TD>" . highlight($search, nl2br($theRow['description'])) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Comments:</TD>	<TD>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD>Run Start:</TD>					<TD>" . format_date($theRow['problemstart']);
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($theRow['problemend']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD>Map:</TD>						<TD>&nbsp;&nbsp;Lat:&nbsp;&nbsp;<SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;Lon:&nbsp;&nbsp; <SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>\n";
	$utm = get_variable('UTM');
	
	if ($utm==1) {
		$coords =  $theRow['lat'] . "," . $theRow['lng'];
		$print .= "<TR CLASS='even'  VALIGN='top'><TD>UTM:</TD>		<TD>" . toUTM($coords) . "</TD></TR>\n";
		}
	//				Northing 4508427,	Easting 380578, Zone 17T

	$print .= "<TR ID='point' CLASS='even' STYLE = 'display:none;'><TD>Point:</TD><TD>&nbsp;&nbsp;Lat:&nbsp;&nbsp; <SPAN ID='newlat'></SPAN>
		&nbsp;&nbsp;Lon:&nbsp;&nbsp; <SPAN ID='newlng'></SPAN></TD></TR>\n";
		
	if ($dist) {
		$print .= "<TR ID='point' CLASS='odd' STYLE = 'visibility:visible;'><TD>Point:</TD><TD ALIGN='center'>&nbsp;&nbsp;Range:&nbsp;&nbsp; <SPAN ID='range'>na</SPAN>
			&nbsp;&nbsp;Brng:&nbsp;&nbsp; <SPAN ID='brng'>na</SPAN></TD></TR>\n";
		}
	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow['id']);				// log
	$print .="</TD></TR>";

	if ($dist) {
		$print .= "<TR><TD COLSPAN=2 ALIGN='center'><BR />Click map point for distance information.</TD></TR>";
		}

	$print .= "</TABLE>\n";
	return $print;
	}		// end function do_ticket(
	
//	} {		-- dummy

function do_ticket_pr($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table
	global $my_session;
	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass=''; break;
		}
	$print = "<TABLE BORDER='0'ID='left' width='" . $theWidth . "'>\n";		// 
	
	$print .= "<TR><TD CLASS='td_data' COLSPAN=2 ALIGN='center'><B>Incident <I>" . $theRow['scope'] . "</B>&nbsp;&nbsp;(#" . $theRow['id'] . ")</I></TD></TR>\n"; 
	$print .= "<TR><TD CLASS='print_TD'>Priority:</TD>					<TD CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>Written:</TD>		<TD CLASS='print_TD'>" . format_date($theRow['date']) . "</TD></TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>Updated:</TD>		<TD CLASS='print_TD'>" . format_date($theRow['updated']) . "</TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>Reported by:</TD>	<TD CLASS='print_TD'>" . $theRow['contact'] . "</TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>Phone:</TD>			<TD CLASS='print_TD'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>Status:</TD>		<TD CLASS='print_TD'>" . get_status($theRow['status']) . "</TD></TR>\n";

	$print .= "<TR><TD COLSPAN='2'>&nbsp;	<TD CLASS='print_TD'></TR>\n";			// separator
	$print .= "<TR><TD CLASS='print_TD'>Address:</TD>		<TD CLASS='print_TD'>" . highlight($search, $theRow['street']) . "</TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>City:</TD>			<TD CLASS='print_TD'>" . highlight($search, $theRow['city']);
	$print .=	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;" . highlight($search, $theRow['state']) . "</TD></TR>\n";

	
	$print .= "<TR VALIGN='top'><TD CLASS='print_TD'>Description:</TD>	<TD CLASS='print_TD'>" . highlight($search, nl2br($theRow['description'])) . "</TD></TR>\n";
	$print .= "<TR VALIGN='top'><TD CLASS='print_TD'>Comments:</TD>	<TD CLASS='print_TD'>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";

	$print .= "<TR><TD CLASS='print_TD'>Run Start:</TD>					<TD CLASS='print_TD'>" . format_date($theRow['problemstart']);
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($theRow['problemend']) . "</TD></TR>\n";
	$print .= "<TR><TD CLASS='print_TD'>Map:</TD>						<TD CLASS='print_TD'>&nbsp;&nbsp;Lat:&nbsp;&nbsp;<SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;Lon:&nbsp;&nbsp; <SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>\n";
	$utm = get_variable('UTM');
	
	if ($utm==1) {
		$coords =  $theRow['lat'] . "," . $theRow['lng'];
		$print .= "<TR VALIGN='top'><TD CLASS='print_TD'>UTM:</TD>		<TD CLASS='print_TD'>" . toUTM($coords) . "<TD CLASS='print_TD'></TD></TR>\n";
		}
	//				Northing 4508427,	Easting 380578, Zone 17T
	$print .= "<TR ID='point'STYLE = 'display:none;'><TD CLASS='print_TD'>Point:</TD><TD CLASS='print_TD'>&nbsp;&nbsp;Lat:&nbsp;&nbsp; <SPAN ID='newlat'></SPAN>
		&nbsp;&nbsp;Lon:&nbsp;&nbsp; <SPAN ID='newlng'></SPAN></TD></TR>\n";

	if ($dist) {
		$print .= "<TR ID='point'STYLE = 'visibility:visible;'><TD CLASS='print_TD'>Point:</TD><TD ALIGN='center'>&nbsp;&nbsp;Range:&nbsp;&nbsp; <SPAN ID='range'>na</SPAN>
			&nbsp;&nbsp;Brng:&nbsp;&nbsp; <SPAN ID='brng'>na</SPAN></TD></TR>\n";
		$print .= "<TR><TD COLSPAN=2 ALIGN='center'><BR />Click map point for distance information.</TD></TR>";
		}

	$print .= "</TABLE>\n";
	return $print;
	}		// end function do_ticket_pr()
	
//	} -- dummy
	

?>