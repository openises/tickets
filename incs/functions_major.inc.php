<?php
/*
6/9/08	added  'Closed Calls' button
7/27/08	handle deleted status values
8/02/08	provide link to dispatch function
8/3/08	add assign data to unit IW's
8/6/08	added function do_tracks
8/15/08	mysql_fetch_array to mysql_fetch_assoc - performance
8/22/08 added usng position
8/24/08 revised sort order to include severity
8/25/08 added responders TITLE display
8/25/08 revised map control type to small - for TB
9/8/08	lat/lng to CG format
9/12/08 added USNG PHP functions
9/14/08 added js trim()
10/9/08	added check for div defined - IE JS pblm
10/14/08 changed reference to usng.js
10/15/08 changed 'Comments' to 'Disposition'
10/15/08 corrections re LL2NGS
10/16/08 added traffic functions
10/17/08 added hide_Units()
10/21/08 added edit link in infowindow
10/21/08 added  rand into link, istest as global
11/1/08 added prefix
11/06/08 sql error
11/6/08 missing table close tags corrected, timer for mini-map
*/
//	{ -- dummy

function list_tickets($sort_by_field='',$sort_value='') {	// list tickets ===================================================
	global $my_session, $istest;
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

		<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR />
			<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doTraffic()'><U>Traffic</U></A><BR /><BR />
		<SPAN ID="allIcons" STYLE="display: inline-block">		
		<A HREF="#" onClick = "show_Units()">Units: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<SPAN ID="incidents" STYLE="display: inline-block">
		Incident Priority:&nbsp;&nbsp;&nbsp;&nbsp;
		<A HREF="#" onClick = "hideGroup(1)">Typical: 	<IMG SRC = './markers/sm_blue.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		<A HREF="#" onClick = "hideGroup(2)">	High: 	<IMG SRC = './markers/sm_green.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		<A HREF="#" onClick = "hideGroup(3)">Highest: 	<IMG SRC = './markers/sm_red.png' BORDER=0></A></SPAN>
		</SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN ID="show_all_icon" STYLE="display: none"><A HREF="#" onClick = "show_All()">Show all: <IMG SRC = './markers/sm_white.png' BORDER=0></A></SPAN>
		</NOBR></CENTER><BR /></TD>

		</CENTER><BR /></TD>
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
		elem = document.getElementById("show_all_icon");
		elem.style.display = "inline-block";
		}			// end function

	function show_All() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {			
				gmarkers[i].show();
				}
			} 	// end for ()
		elem = document.getElementById("show_all_icon");
		elem.style.display = "none";
		elem = document.getElementById("allIcons");
		elem.style.display = "inline-block";
		elem = document.getElementById("incidents");
		elem.style.display = "inline-block";		
		}			// end function	

	function show_Units() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].id == 0) {
					gmarkers[i].show();			
					}
				else {
					gmarkers[i].hide();			
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		elem = document.getElementById("incidents");
		elem.style.display = "none";
		elem = document.getElementById("show_all_icon");
		elem.style.display = "inline-block";
		}

	function hide_Units () {								// 10/17/08
		for (var i = 0; i < gmarkers.length; i++) {	
//			alert(gmarkers[i].id);
			if ((gmarkers[i]) && (gmarkers[i].id==0))	{ gmarkers[i].hide();}		// hide units icon
			else 										{ gmarkers[i].show();}
			}				// end for()
		elem = document.getElementById("show_all_icon");
		elem.style.display = "inline-block";			
		}				// end function hide_units ()

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (line_no) + ". "+ sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
//		alert (v_id);
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
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
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();			
			marker.openInfoWindowTabsHtml(infoTabs[id]);
			
			setTimeout(function() {										// wait for rendering complete - 11/6/08
				if (document.getElementById("detailmap")) {				// 10/9/08
					var dMapDiv = document.getElementById("detailmap");
					var detailmap = new GMap2(dMapDiv);
					detailmap.addControl(new GSmallMapControl());
					detailmap.setCenter(point, 12);  						// larger # = closer
					detailmap.addOverlay(marker);
					}
				else {
//					alert(62);
//					alert(document.getElementById("detailmap"));
					}
				},3000);				// end setTimeout(...)				
				
			});
		gmarkers[id] = marker;							// marker to array for side_bar click function
		infoTabs[id] = tabs;							// tabs to array
		
		bounds.extend(point);										// extend the bounding box
		
		return marker;
		}				// end function create Marker()

	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			}
		grid = !grid;
		}			// end function doGrid
		
    var trafficInfo = new GTrafficOverlay();
    var toggleState = true;

	function doTraffic() {				// 10/16/08
		if (toggleState) {
	        map.removeOverlay(trafficInfo);
	     	} 
		else {
	        map.addOverlay(trafficInfo);
	    	}
        toggleState = !toggleState;			// swap
	    }				// end function doTraffic()


	var icons=[];						// note globals
	icons[0] = 											   "./markers/YellowIcons/marker";	// Yellow units
	icons[<?php print $GLOBALS['SEVERITY_NORMAL']+1; ?>] = "./markers/BlueIcons/marker";	
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM']+1; ?>] = "./markers/GreenIcons/marker";
	icons[<?php print $GLOBALS['SEVERITY_HIGH']+1; ?>] =   "./markers/RedIcons/marker";		
	icons[<?php print $GLOBALS['SEVERITY_HIGH']+2; ?>] =   "./markers/WhiteIcons/marker";

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

function do_track(callsign) {					// added 8/6/08
	if (parent.frames["upper"].logged_in()) {
//		if(starting) {return;}					// 6/6/08
//		starting=true;	
		map.closeInfoWindow();
		var width = <?php print get_variable('map_width');?>+360;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "track_u.php?source="+callsign;
		
		newwindow=window.open(url, callsign,  spec);
		if (isNull(newwindow)) {
			alert ("Track display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function

	var side_bar_html = "<TABLE border=0 CLASS='sidebar' WIDTH = <?php print max(320, intval($my_session['scr_width']* 0.4));?> >";
	side_bar_html += "<tr class='even'><td colspan=99 align='center'>Click for information</td></tr>";
	side_bar_html += "<tr class='odd'><td></td><td align='center'><B>Incident</B></td><td align='center'><B>Type</B></td><td>P</td><td>A</td><td align='center'>As of</td></tr>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GSmallMapControl());					// 8/25/08
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
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])		
		});	
				 
<?php
//	dump ($my_session);
	$get_status = (!empty ($get_status))? $get_status : $GLOBALS['STATUS_OPEN'];			 						 // default to show all open tickets
//	$order_by =  ((!empty ($_GET)) && ($_GET['sortby'] == ''))? $my_session['sortorder']: $order_by = $_GET['sortby']; // use default sort order?
	$order_by =  (!empty ($get_sortby))? $get_sortby: $my_session['sortorder']; // use default sort order?
//	dump ($order_by);
																			//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($my_session['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $my_session['ticket_per_page']))	{
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
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, in_types.type AS `type`, in_types.id AS `t_id` FROM `$GLOBALS[mysql_prefix]ticket` LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.`in_types_id`=in_types.id  WHERE $sort_by_field='$sort_value' $restrict_ticket ORDER BY $order_by";
		}
	else {
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]in_types`.type AS `type`, `$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id` FROM $GLOBALS[mysql_prefix]ticket LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` $where $restrict_ticket ORDER BY `severity` DESC, $order_by $limit";		// 8/24/08
		}
//	dump($query);
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							// major while ... starts here
							
	while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
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
		$todisp = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $the_id . "'><U>Dispatch</U></A>";	// 8/2/08
		
		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
		$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
		
		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . shorten($row['scope'], 48)  . "$strikend</B></TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>$street</TD></TR>";
		$utm = get_variable('UTM');
		if ($utm==1) {
			$coords =  $row['lat'] . "," . $row['lng'];
			$tab_1 .= "<TR CLASS='even'><TD>UTM grid:</TD><TD>" . toUTM($coords) . "</TD></TR>";
			}
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><FONT SIZE='-1'>";
		$tab_1 .= 	$todisp . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Details</U></A>";		// 08/8/02
		if (!(is_guest() && get_variable('guest_add_ticket')==0)) {
			$tab_1 .= 	"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='edit.php?id=" . $the_id . $rand . "'><U>Edit</U></A><BR /><BR />";					// 10/21/08
			$tab_1 .= 	"<A HREF='patient.php?ticket_id=" . $the_id . $rand ."'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tab_1 .= 	"<A HREF='action.php?ticket_id=" . $the_id . $rand ."'><U>Add Action</U></A>";
			}
		$tab_1 .= 	"</FONT></TD></TR></TABLE>";			// 11/6/08
		

		$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_2 .= "<TR CLASS='even'>	<TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 120) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
		$tab_2 .= "<TR CLASS='odd'>		<TD>Disposition:</TD><TD>" . shorten($row['comments'], 120) . "</TD></TR>";
		$tab_2 .= "<TR CLASS='even'>	<TD>USNG:</TD><TD>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";				// 8/23/08, 10/15/08
//		$tab_2 .= "<TR>					<TD>&nbsp;</TD></TR>";
		$tab_2 .= "<TR>					<TD COLSPAN=2>" . show_assigns(0, $the_id) . "</TD></TR>";
		$tab_2 .= "<TR CLASS='even'>	<TD COLSPAN=2 ALIGN='center'>";
		$tab_2 .= $todisp . "&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
		if (!(is_guest() && get_variable('guest_add_ticket')==0)) {			
			$tab_2 .= 	"<A HREF='patient.php?ticket_id=" . $the_id . "'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tab_2 .= 	"<A HREF='action.php?ticket_id=" . $the_id . "'><U>Add Action</U></A>&nbsp;&nbsp;";
			}
		$tab_2 .= 	"</TD></TR></TABLE>";		// 11/6/08
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `ticket_id` = " . $the_id;
		$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$A = mysql_affected_rows();
		
		$query= "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE `ticket_id` = " . $the_id;
		$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$P = mysql_affected_rows ();

			
		$sidebar_line = "<TD CLASS='$severityclass'  TITLE = '" . htmlentities ($row['scope'], ENT_QUOTES) . "'><NOBR>$strike" . shorten($row['scope'], 20) . " $strikend</NOBR></TD>";
		$sidebar_line .= "<TD CLASS='$severityclass'  TITLE = '" . htmlentities ($row['type'], ENT_QUOTES) . "'><NOBR>$strike" . shorten($row['type'], 20) . " $strikend</NOBR></TD>";
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
	$assigns = array();					// 08/8/3
	$tickets = array();					// ticket id's

//	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, $GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";				// 11/1/08

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";		// 11/06/08


	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_assoc($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);
	
	$types = array();	$types[$GLOBALS['TYPE_EMS']] = "Medical";	$types[$GLOBALS['TYPE_FIRE']] = "Fire";
						$types[$GLOBALS['TYPE_COPS']] = "Police";	$types[$GLOBALS['TYPE_MUTU']] = "Mutual"; $types[$GLOBALS['TYPE_OTHR']] = "Other";
						$types['0'] = "error";

	$status_vals = array();				// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);
	
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	print (mysql_affected_rows()==0)? "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center' COLSPAN=99><B>No units!</B></TD></TR>\"\n" : "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center'><B>Unit</B></TD><TD ALIGN='center' COLSPAN=2><B>Status</B></TD><TD>M</TD><TD></TD></TR>\"\n" ;
	
	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");			// major while ... for RESPONDER data starts here
							
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
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
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if (mysql_affected_rows()>0) {		// got track stuff. do tab 2 and 3
				$rowtr = stripslashes_deep(mysql_fetch_assoc($result_tr));
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
			
		$sidebar_line = "<TD TITLE = '" . htmlentities ($row['name'], ENT_QUOTES) . "'><NOBR>" . shorten($row['name'], 20) . "</NOBR></TD>";	// 8/25/08
		
		$temp = $row['un_status_id'];
//		$the_status = (array_key_exists($row['un_status_id'], $status_vals))? $status_vals[$temp]: $row['un_status_id'];		// 7/27/08 
		$sidebar_line .= "<TD COLSPAN=2><NOBR>" . shorten($status_vals[$temp], 10 ) . "</NOBR></TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . $the_bull . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . format_sb_date($row['updated']) . "</NOBR></TD>";

		print "\n\tvar do_map = true;\n";		// default

		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$row['un_status_id']] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
//		$temp = (array_key_exists($row['id'], $assigns))? $assigns[$row['id']] : "";
		if (array_key_exists($row['id'], $assigns)) { 
			$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
			$is_dispd = TRUE;
			}		

		$todisp = (isset($is_dispd) || is_guest())? "": "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
		$toedit = ((is_administrator() || is_super()))?  "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;":"" ;
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U></SPAN>" ;

		$tab_1 .=  "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $todisp . $totrack . $toedit . " <A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02

//		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'>Details:&nbsp;&nbsp;&nbsp;&nbsp;" . $toedit . "<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "</TABLE>";			// 11/6/08

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
	

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}
	
	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$my_session[user_id]" : "";

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id' $restrict_ticket";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">No such ticket or user access to ticket is denied</FONT>";
		exit();
		}
	
	$row = stripslashes_deep(mysql_fetch_assoc($result));

	$query = "SELECT *  FROM `$GLOBALS[mysql_prefix]in_types` WHERE `id`= $id";
	$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_type = stripslashes_deep(mysql_fetch_assoc($result_type));

	if ($print == 'true') {
	
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
		print "<TR><TD CLASS='print_TD'><B>Disposition:</B></TD>	<TD CLASS='print_TD'>" . nl2br ($row['comments']). "</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Owner:</B></TD>		<TD CLASS='print_TD'>" . get_owner($row['owner']). "</TD></TR>\n"; 
		print "<TR><TD CLASS='print_TD'><B>Issued:</B></TD>		<TD CLASS='print_TD'>" . format_date($row['date']). "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Run Start:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemstart']). "</TD></TR>";
		print "<TR><TD CLASS='print_TD'><B>Run End:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemend']).	"</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Affected:</B></TD>	<TD CLASS='print_TD'>" . $row['affected']. "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Position:</B></TD>	<TD CLASS='print_TD'>" . get_lat($row['lat']) . ", " .  get_lng($row['lng']) . "&nbsp;&nbsp;&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']) ."</TD></TR>\n"; 		// 9/13/08
		$utm = get_variable('UTM');
		if ($utm==1) {
			$coords =  $row['lat'] . "," . $row['lng'];
			print "<TR><TD CLASS='print_TD'><B>UTM grid:</B></TD> <TD CLASS='print_TD'>" . toUTM($coords) . "</TD></TR>\n"; 
			}

		print show_actions($row['id'], "date", FALSE, FALSE);		// lists actions and patient data, print
		
//		print "\n</BODY>\n<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>\n</HTML>";	10/14/08
		print "\n</BODY>\n</HTML>";
		return;
		}		// end if ($print == 'true')
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">
	<TR VALIGN="top"><TD CLASS="print_TD" ALIGN="left">
<?php
	print do_ticket($row, max(320, intval($my_session['scr_width']* 0.4)), $search = FALSE) ;		// 11/8/08
//	print "<TR><TD>&nbsp;</TD></TR>";
	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

//	print show_assigns(0, $row['id']);				// 08/8/5

	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		<BR /><A HREF='#' onClick='doGrid()'><U>Grid</U></A></A></TD></TR>";
	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n		
		<TR><TD ALIGN='center'><BR /><FONT SIZE='-1'>Click map point for distance information.</FONT></TD></TR>\n";
	print "</TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];	

//	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "</TD></TR>\n";
//	print "<TR><TD ALIGN='left'>";
//	print show_log ($id);				// log as a table
//	print "</TD></TR></TABLE>\n";
	print "</TABLE>\n";
	
	
?>
<!--	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT> 10/14/08 -->
	<SCRIPT>
	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			grid = false;
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			grid = true;
			}
		}

	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
	$tab_1 .= "</TABLE>";		// 11/6/08

	do_kml();			// kml functions

?>
	map.openInfoWindowHtml(point, "<?php print $tab_1;?>");		
	
	GEvent.addListener(map, "click", function(marker, point) {
		if (point) {
			var baseIcon = new GIcon();
			baseIcon.iconSize=new GSize(32,32);
			baseIcon.iconAnchor=new GPoint(16,16);
			var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);		// 10/13/08

			map.clearOverlays();
			var thisMarker = new GMarker(point, cross);
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
			
			document.getElementById("range").innerHTML	= dist2;
			document.getElementById("brng").innerHTML	= (brng (parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng)).toFixed(0)) + ' degr';
			document.getElementById("newusng").innerHTML= LLtoUSNG(nlat, nlng, 5);
			document.getElementById("pointl1").style.display = "block";
			document.getElementById("pointl2").style.display = "block";

			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
			map.addOverlay(new GMarker(point, icon));
			var polyline = new GPolyline([
			    new GLatLng(nlat, nlng),
			    new GLatLng(olat, olng)
				], "#FF0000", 2);
			map.addOverlay(polyline);			
			}
		} )

	function lat2ddm(inlat) {				// 9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}
	
	function lng2ddm(inlng) {
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}
	
	
	function do_coords(inlat, inlng) {  //9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

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
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Disposition:</TD>	<TD>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD>Run Start:</TD>					<TD>" . format_date($theRow['problemstart']);
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($theRow['problemend']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>Position</U>: </TD>
		<TD>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) .
			"&nbsp;&nbsp;&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']) . "</TD></TR>\n";		// 9/13/08
	$utm = get_variable('UTM');
	
	if ($utm==1) {
		$coords =  $theRow['lat'] . "," . $theRow['lng'];
		$print .= "<TR CLASS='even'  VALIGN='top'><TD>UTM grid:</TD>		<TD>" . toUTM($coords) . "</TD></TR>\n";
		}
		
	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow['id']);				// log
	$print .="</TD></TR>";

	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
	$print .= "</TABLE>\n";

	$print .= show_assigns(0, $theRow['id']);				// 08/8/5

	return $print;
	}		// end function do_ticket(
	
	
//	} -- dummy
	

?>