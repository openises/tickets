<?php
// 5/29/08 - added do_kml() call
	require_once('functions.inc.php'); 
	do_login(basename(__FILE__));

	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}	

	function edit_ticket($id) {							/* post changes */
		$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
		$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;

		$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
		$post_frm_affected 	 	= strip_html($post_frm_affected);
		$_POST['frm_scope']			= strip_html($_POST['frm_scope']);

		/*if (get_variable('reporting')) {		// if any change do automatic action reporting
		
//			if ($_POST[frm_affected] != $_POST[frm_affected_default]) report_action($GLOBALS[ACTION_AFFECTED],$_POST[frm_affected],0,$id);
			if ($_POST[frm_severity] != $_POST[frm_severity_default]) report_action($GLOBALS[ACTION_SEVERITY],get_severity($_POST[frm_severity_default]),get_severity($_POST[frm_severity]),$id);
			if ($_POST[frm_scope] != $_POST[frm_scope_default]) report_action($GLOBALS[ACTION_SCOPE],$_POST[frm_scope_default],0,$id);
			} */

		if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
			if ($post_frm_meridiem_problemstart == 'pm'){
				$post_frm_meridiem_problemstart	= ($post_frm_meridiem_problemstart + 12) % 24;
				}
//			if ($_POST['frm_meridiem_problemend'] == 'pm') 	$_POST['frm_hour_problemend'] 	= ($_POST['frm_hour_problemend'] + 12) % 24;
			}
		if(empty($post_frm_owner)) {$post_frm_owner=0;}
//		$frm_problemstart = $_POST['frm_year_problemstart']-$_POST['frm_month_problemstart']-$_POST['frm_day_problemstart'] $_POST['frm_hour_problemstart']:$_POST['frm_minute_problemstart']:00";
		$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";


		if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
			if ($post_frm_meridiem_problemstart == 'pm'){
				$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
				}
			if (isset($_POST['frm_meridiem_problemend'])) {
				if ($_POST['frm_meridiem_problemend'] == 'pm'){
					$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
					}
				}
			}
		$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart("$_POST[frm_year_problemend]-$_POST[frm_month_problemend]-$_POST[frm_day_problemend] $_POST[frm_hour_problemend]:$_POST[frm_minute_problemend]:00") : "NULL";
//		dump ($frm_problemend);
		
		// perform db update
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		if(empty($post_frm_owner)) {$post_frm_owner=0;}

		$query = "UPDATE $GLOBALS[mysql_prefix]ticket SET 
		`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
		`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
		`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
		`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
		`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
		`lat`= " . 			quote_smart(trim($_POST['frm_lat'])) . ",
		`lng`= " . 			quote_smart(trim($_POST['frm_lng'])) . ",
		`scope`= " . 		quote_smart(trim($_POST['frm_scope'])) . ",
		`affected`= " .		quote_smart(trim($post_frm_affected)) . ",
		`owner`= " . 		quote_smart(trim($post_frm_owner)) . ",
		`severity`= " . 	quote_smart(trim($_POST['frm_severity'])) . ",
		`in_types_id`= " . 	quote_smart(trim($_POST['frm_in_types_id'])) . ",
		`status`=" . 		quote_smart(trim($_POST['frm_status'])) . ",
		`problemstart`=".	quote_smart(trim($frm_problemstart)) . ",
		`problemend`=".		$frm_problemend . ",
		`description`= " .	quote_smart(trim($_POST['frm_description'])) .",
		`comments`= " . 	quote_smart(trim($_POST['frm_comments'])) .",
		`updated`='$now'
		WHERE ID='$id'";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if (($_POST['frm_status'])== $GLOBALS['STATUS_OPEN']) {		// log the change
			do_log($GLOBALS['LOG_INCIDENT_CHANGE'], $id);	
			}
		else {
			do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $id);
			}

		print '<FONT CLASS="header">Ticket <I>' . $_POST['frm_scope'] . '</I> has been updated</FONT><BR /><BR />';		/* show updated ticket */
		notify_user($id, $GLOBALS['NOTIFY_TICKET']);
		add_header($_GET['id']);
		show_ticket($id);
		}				// end function edit_ticket() 

	$api_key = get_variable('gmaps_api_key');
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Edit Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT SRC="graticule.js" type="text/javascript"></SCRIPT>
<SCRIPT>

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	var map;
	var grid = false;										// toggle
	var thePoint;

	function capWords(str){ 
		var words = str.split(" "); 
		for (var i=0 ; i < words.length ; i++){ 
			var testwd = words[i]; 
			var firLet = testwd.substr(0,1); 
			var rest = testwd.substr(1, testwd.length -1) 
			words[i] = firLet.toUpperCase() + rest 
	  	 	} 
		return( words.join(" ")); 
		} 

	function validate(theForm) {
//		alert (theForm);
		var errmsg="";
		if ((document.edit.frm_status.value == <?php print $GLOBALS['STATUS_CLOSED'];?>) && (document.edit.frm_year_problemend.disabled))
														{errmsg+= "\tClosed ticket requires run end date\n";}
		if (theForm.frm_contact.value == "")		{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")		{errmsg+= "\tIncident name is required\n";}
		if (theForm.frm_description.value == "")	{errmsg+= "\tSynopsis is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.frm_lat.disabled=false;
			theForm.frm_lng.disabled=false;
			theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits
			return true;
			}
		}				// end function validate(theForm)

	function do_end() {				// make run-end date/time inputs available for posting
		elem = document.getElementById("runend1");
		elem.style.visibility = "hidden";
		document.edit.frm_year_problemend.disabled = false;
		document.edit.frm_month_problemend.disabled = false;
		document.edit.frm_day_problemend.disabled = false;
		document.edit.frm_hour_problemend.disabled = false;
		document.edit.frm_minute_problemend.disabled = false;
		
<?php
	if (!get_variable('military_time')){
		print "\tdocument.add.frm_meridiem_problemend.disabled = false;\n";
		}
?>
		}
	var good_end = false;		// boolean defines run end 
	function reset_end() {		// on reset()
		if (!good_end) {
			elem = document.getElementById("runend1");
//			elem.style.visibility = "hidden";
			document.edit.frm_year_problemend.disabled = true;
			document.edit.frm_month_problemend.disabled = true;
			document.edit.frm_day_problemend.disabled = true;
			document.edit.frm_hour_problemend.disabled = true;
			document.edit.frm_minute_problemend.disabled = true;		
			}
	}

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()
	
</SCRIPT>
</HEAD>

<BODY onLoad = "ck_frames()" onunload="GUnload()">
<?php 
	$id = $_GET['id'];

	if ((isset($_GET['action'])) && ($_GET['action'] == 'update')) {		/* update ticket */
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id' LIMIT 1")) {
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT>";
			}
		else {
			edit_ticket($id);
			}
		}

	else if (isset($_GET['delete'])) {							//delete ticket
		if ($_POST['frm_confirm']) {
			/* remove ticket and ticket actions */
			$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]ticket WHERE ID='$id'") or do_error('edit.php::remove_ticket(ticket)', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$id'") or do_error('edit.php::remove_ticket(action)', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<FONT CLASS=\"header\">Ticket '$id' has been removed.</FONT><BR /><BR />";
			list_tickets();
			}
		else {		//confirm deletion
			print "<FONT CLASS='header'>Confirm ticket deletion</FONT><BR /><BR /><FORM METHOD='post' ACTION='edit.php?id=$id&delete=1&go=1'><INPUT TYPE='checkbox' NAME='frm_confirm' VALUE='1'>Delete ticket #$id &nbsp;<INPUT TYPE='Submit' VALUE='Confirm'></FORM>";
			}
		}
	else {				// not ($_GET['delete'])
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id'")) {		/* sanity check */
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT><BR />";
			} 

		else {				// OK, do form
			$result = mysql_query("SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]ticket WHERE ID='$id' LIMIT 1") or do_error('', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row = stripslashes_deep(mysql_fetch_array($result));
			if (good_date($row['problemend'])) {
?>
				<script>
				good_end = true;
				</SCRIPT>
<?php			
				}
			print "<TABLE BORDER='0' ID = 'outer' ALIGN='left' >\n";
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left'>";
	
			print "<FORM NAME='edit' METHOD='post' onSubmit='return validate(document.edit)' ACTION='edit.php?id=$id&action=update'>";
			print "<TABLE BORDER='0' ID='data'>\n";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=2><FONT CLASS='header'>Edit Run Ticket</FONT> (#" . $id . ")</TD></TR>";
			print "<TR CLASS='even'><TD CLASS='td_label'>Synopsis:</TD><TD><INPUT TYPE='text' NAME='frm_scope' SIZE='48' VALUE='" . $row['scope'] . "' MAXLENGTH='48'></TD></TR>\n"; 
			print "<TR CLASS='odd'><TD CLASS='td_label'>Priority:</TD><TD><SELECT NAME='frm_severity'>";
			$nsel = ($row['severity']==$GLOBALS['SEVERITY_NORMAL'])? "SELECTED" : "" ;
			$msel = ($row['severity']==$GLOBALS['SEVERITY_MEDIUM'])? "SELECTED" : "" ;
			$hsel = ($row['severity']==$GLOBALS['SEVERITY_HIGH'])? "SELECTED" : "" ;
			
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_NORMAL'] . "' $nsel>normal</OPTION>";
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_MEDIUM'] . "' $msel>medium</OPTION>";
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_HIGH'] . "' $hsel>high</OPTION>";
			print "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nature:\n";

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<SELECT NAME='frm_in_types_id'>";
			$the_grp = strval(rand());						// force initial optgroup value
			$i = 0;
			while ($row2 = stripslashes_deep(mysql_fetch_array($result))) {
				if ($the_grp != $row2['group']) {
					print ($i == 0)? "": "</OPTGROUP>\n";
					$the_grp = $row2['group'];
					print "<OPTGROUP LABEL='$the_grp'>\n";
					}
			
				$sel = ($row['in_types_id'] == $row2['id'])? " SELECTED" : "" ;
				print "<OPTION VALUE=" . $row2['id'] . $sel . ">" . $row2['type'] . "</OPTION>";
				$i++;
				}
			unset ($result);
			print "</OPTGROUP></SELECT>";
			print "</TD></TR>\n";
			
			print "<TR CLASS='even'><TD CLASS='td_label'>Reported by:</TD><TD><INPUT SIZE='48' TYPE='text' 	NAME='frm_contact' VALUE='" . $row['contact'] . "' MAXLENGTH='48'></TD></TR>\n";
			print "<TR CLASS='odd'><TD CLASS='td_label'>Phone:</TD><TD><INPUT SIZE='48' TYPE='text' NAME='frm_phone' VALUE='" . $row['phone'] . "' MAXLENGTH='16'></TD></TR>\n";
			$selO = ($row['status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
			$selC = ($row['status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
			print "<TR CLASS='even'><TD CLASS='td_label'>Status:</TD><TD>
				<SELECT NAME='frm_status'><OPTION VALUE='" . $GLOBALS['STATUS_OPEN'] . "' $selO>Open</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_CLOSED'] . "'$selC>Closed</OPTION></SELECT></TD></TR>";
			print "<TR CLASS='odd'><TD COLSPAN='2'>&nbsp;</TD></TR>";
			print "<TR CLASS='odd'><TD CLASS='td_label'>Location: </TD><TD><INPUT SIZE='48' TYPE='text'NAME='frm_street' VALUE='" . $row['street'] . "' MAXLENGTH='48'></TD></TR>\n";
			print "<TR CLASS='even'><TD CLASS='td_label'>City:</TD><TD><INPUT SIZE='32' TYPE='text' 	NAME='frm_city' VALUE='" . $row['city'] . "' MAXLENGTH='32' onChange = 'this.value=capWords(this.value)'>\n";
			print 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; St:&nbsp;&nbsp;<INPUT SIZE='2' TYPE='text' NAME='frm_state' VALUE='" . $row['state'] . "' MAXLENGTH='2'></TD></TR>\n";
//			print "<TR CLASS='even'><TD CLASS='td_label'>Affected:</TD><TD><INPUT TYPE='text' SIZE='48' NAME='frm_affected' VALUE='" . $row['affected'] . "' MAXLENGTH='48'></TD></TR>\n";
	
			print "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Synopsis:</TD>";
			print 	"<TD CLASS='td_label'><TEXTAREA NAME='frm_description' COLS='35' ROWS='4'>" . $row['description'] . "</TEXTAREA></TD></TR>\n";
			print "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Comments:</TD>";
			
			print 	"<TD><TEXTAREA NAME='frm_comments' COLS='35' ROWS='4'>" . $row['comments'] . "</TEXTAREA></TD></TR>\n";
			//lookup owners
/*			if (get_variable('restrict_user_add') && !(is_administrator()))
				print "<INPUT TYPE='hidden' NAME='frm_owner' VALUE='$my_session[user_id]'>";
			else {
				print '<TR CLASS='even'><TD CLASS="td_label">Owner:</TD><TD>';
				$result2 = mysql_query("SELECT id,user FROM $GLOBALS[mysql_prefix]user") or do_error('edit.php::lookup_owner', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				print '<SELECT NAME="frm_owner">';
				while ($row2 = stripslashes_deep(mysql_fetch_array($result2))) {
					$selected = (get_owner($row['owner']) == $row2['user'])? " SELECTED" : "" ;
					print "<OPTION VALUE='$row2[id]'$selected >$row2[user]</OPTION>";
					}
				print '</SELECT></TD></TR>';
				}
*/				
			print "\n<TR CLASS='even'><TD CLASS='td_label'>Run Start:</TD><TD>";
			print  generate_date_dropdown("problemstart",$row['problemstart']);
			print "</TD></TR>\n";
			if (good_date($row['problemend'])) {
				print "\n<TR CLASS='odd'><TD CLASS='td_label'>Run End:</TD><TD>";
				generate_date_dropdown("problemend",$row['problemend']);
				print "</TD></TR>\n";
				}
			else {
				print "\n<TR CLASS='odd' valign='middle'><TD CLASS='td_label'>Run End: &nbsp;&nbsp;<input type='radio' name='re_but' onClick ='do_end();' /></TD><TD>";
				print "<SPAN style = 'visibility:hidden' ID = 'runend1'>";
				generate_date_dropdown('problemend','' , TRUE);
				print "</SPAN></TD></TR>\n";
				}

			print "<TR CLASS='even'><TD CLASS='td_label'>Updated:</TD><TD>" . format_date($row['updated']) . "</TD></TR>\n";
			print "<TR CLASS='odd'><TD CLASS='td_label'>Map:</TD><TD>&nbsp;&nbsp;Lat:&nbsp;&nbsp;<INPUT SIZE='12' TYPE='text' NAME='frm_lat' VALUE='" . $row['lat'] . "' MAXLENGTH='12' disabled>\n";
			print "&nbsp;&nbsp;&nbsp;&nbsp;Lon:&nbsp;&nbsp;<INPUT SIZE='12' TYPE='text' NAME='frm_lng' VALUE='" . $row['lng'] . "' MAXLENGTH='12' disabled></TD></TR>\n";
			$lat = $row['lat']; $lng = $row['lng'];	
			print "<TR CLASS='even'><TD COLSPAN='2' ALIGN='center'><BR /><INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='reset' VALUE='Reset' onclick= 'reset_end(); resetmap($lat, $lng);' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Submit'></TD></TR>";
?>	
			<INPUT TYPE="hidden" NAME="frm_status_default" VALUE="<?php print $row['status'];?>">
			<INPUT TYPE="hidden" NAME="frm_affected_default" VALUE="<?php print $row['affected'];?>">
			<INPUT TYPE="hidden" NAME="frm_scope_default" VALUE="<?php print $row['scope'];?>">
			<INPUT TYPE="hidden" NAME="frm_owner_default" VALUE="<?php print $row['owner'];?>">
			<INPUT TYPE="hidden" NAME="frm_severity_default" VALUE="<?php print $row['severity'];?>">
<?php
			print "</TABLE>";		// end data
			print "</td><td>";
			print "<TABLE ID='mymap' border = 0><TR><TD ALIGN='center'><DIV ID='map' STYLE='WIDTH: " . get_variable('map_width') . "PX; HEIGHT:" . get_variable('map_height') . "PX'></DIV>
				<BR /><A HREF='#' onClick='toglGrid()'><u>Grid</U></A></TD></TR></TABLE ID='mymap'>\n";
			
			print "</TD></TR>";
			print "<TR><TD CLASS='print_TD' COLSPAN='2'>";

			print show_actions($row['id'], "date", TRUE, TRUE);		/* lists actions and patient data belonging to ticket with links */

			print "</FORM>";
			print "</TD></TR></TABLE>";		// bottom of outer
?>
	<SCRIPT type="text/javascript">
		function toglGrid() {									// toggle
			grid = !grid;
			if (!grid) {										// check prior value
				map.clearOverlays();
				}
			else {
				map.closeInfoWindow();
				map.addOverlay(new LatLonGraticule());
				}
			if (thePoint) {										// show it
				icon.image = icons[<?php print $row['severity'];?>];		
				map.addOverlay(new GMarker(thePoint, icon));
	//			map.addOverlay(new GMarker(thePoint));
				}
			}		// end function toglGrid()
	
	
		var map;
		var icons=[];						// note globals
		icons[<?php print $GLOBALS['SEVERITY_NORMAL']; ?>] = "./markers/BlueIcons/blank.png";	
		icons[<?php print $GLOBALS['SEVERITY_MEDIUM']; ?>] = "./markers/GreenIcons/blank.png";
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =   "./markers/RedIcons/blank.png";
	
		map = new GMap2(document.getElementById("map"));		// create the map
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		map.addControl(new GOverviewMapControl());		
		map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>), 12);
		map.enableScrollWheelZoom(); 	
		
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
		baseIcon.infoShadowAnchor = new GPoint(18, 25);

		var icon = new GIcon(baseIcon);
		icon.image = icons[<?php print $row['severity'];?>];		
		var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
		map.addOverlay(new GMarker(point, icon));
		thePoint = point;

<?php
		$street = empty($row['street'])? "" : "<BR/>" . $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 120)  . "</B></TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $street . "</TD></TR>";
		$tab_1 .= "<TABLE>";

		do_kml();			// kml functions

?>
		map.openInfoWindowHtml(point, "<?php print $tab_1;?>");		

	GEvent.addListener(map, "click", function(marker, point) {
		if (marker) {
			map.removeOverlay(marker);
			document.edit.frm_lat.disabled=document.edit.frm_lat.disabled=false;
			document.edit.frm_lat.value=document.edit.frm_lng.value="";
			document.edit.frm_lat.disabled=document.edit.frm_lat.disabled=true;
			thePoint = false;
			}
		if (point) {
			map.clearOverlays();
			do_lat (point.lat())								// display
			do_lng (point.lng())
			map.addOverlay(new GMarker(point, icon));			// GLatLng.
//			map.openInfoWindowHtml(point, tab1contents);		// fix
			map.setCenter(point, 12);
			thePoint = point;
			}

		if (grid) {map.addOverlay(new LatLonGraticule());}		// both cases
		});				// end GEvent.addListener()

	function do_lat (lat) {
		document.edit.frm_lat.disabled=false;
		document.edit.frm_lat.value=lat.toFixed(6)
		document.edit.frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.edit.frm_lng.disabled=false;
		document.edit.frm_lng.value=lng.toFixed(6);
		document.edit.frm_lng.disabled=true;
		}

	function resetmap(lat, lng) {						// restore original marker and center
		map.clearOverlays();
		var point = new GLatLng(lat, lng);	
//		map.addOverlay(new GMarker(point));
		icon.image = icons[<?php print $row['severity'];?>];		
		map.addOverlay(new GMarker(point, icon));
		map.setCenter(new GLatLng(lat, lng), 8);
		do_lat (lat);
		do_lng (lng)
		if (grid) {map.addOverlay(new LatLonGraticule());}	// restore grid
		}

	function do_end() {				// make run-end date/time inputs available for posting
		elem = document.getElementById("runend1");
		elem.style.visibility = "visible";
		document.edit.frm_year_problemend.disabled = false;
		document.edit.frm_month_problemend.disabled = false;
		document.edit.frm_day_problemend.disabled = false;
<?php
		if (!get_variable('military_time')){
			print "\tdocument.edit.frm_meridiem_problemend.disabled = false;\n";
			}
?>
		document.edit.frm_hour_problemend.disabled = false;	
		document.edit.frm_minute_problemend.disabled = false;
		}
		
	</SCRIPT>


<?php
			}			// end  sanity check 
		}
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['id'];?>">
</FORM>	

</BODY></HTML>
<?php
/*
11/3 added frame jump prevention
*/
?>