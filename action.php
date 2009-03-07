<?php
/*
8/16/08	lots of changes; lock icon for date entry control, date validation, 'fetch_assoc' vs 'fetch_array', 'delete' process, 'LIMIT 1' added
8/24/08 removed LIMIT from INSERT sql
10/7/08	set  WRAP="virtual"
10/19/08 set end tags
10/22/08 added priorities as notify selection criteria
1/21/09 added show butts - re button menu
1/27/09 options list style revision - per variable unit types
2/12/09 changed order per AF request
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php'); 
do_login(basename(__FILE__));

if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$get_action = (empty($_GET['action']))? "form" : $_GET['action'];		// 10/21/08
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Action Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="8/24/08">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT src="./js/multiSelect.js"></SCRIPT>
<?php
if ($get_action == 'add') {		
	$api_key = get_variable('gmaps_api_key');		// empty($_GET)
?>
	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
	<SCRIPT src="./js/graticule.js" type="text/javascript"></SCRIPT>
<?php
	}					// end if ($get_action == 'add')
?>
<SCRIPT>
	function ck_frames() {		//  onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()

	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}				

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

		
	function do_asof(theForm, theBool) {							// 8/10/08
//		alert(56);
//		alert(theForm.name);
		theForm.frm_year_asof.disabled = theBool;
		theForm.frm_month_asof.disabled = theBool;
		theForm.frm_day_asof.disabled = theBool;
		theForm.frm_hour_asof.disabled = theBool;
		theForm.frm_minute_asof.disabled = theBool;
		}

	function do_unlock(theForm) {									// 8/10/08
		document.getElementById("lock").style.visibility = "hidden";		
		do_asof(theForm, false)
		}
		
	function do_lock(theForm) {										// 8/10/08
		do_asof(theForm, true)
		document.getElementById("lock").style.visibility = "visible";
		}

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function chknum(str) {
		var nums = str.trim().replace(/\D/g, "" );							// strip all non-digits
		return (nums == str.trim());
		}

	function chkval(val, lo, hi) { 
		return  (chknum(val) && !((val> hi) || (val < lo)));}

	function datechk_r(theForm) {		// as-of vs now
		var start = new Date();
		start.setFullYear(theForm.frm_year_asof.value, theForm.frm_month_asof.value-1, theForm.frm_day_asof.value);
		start.setHours(theForm.frm_hour_asof.value, theForm.frm_minute_asof.value, 0,0);
	
		var end = new Date();
		return (start.valueOf() <= end.valueOf());	
		}
	
	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_description.value == "")		{errmsg+= "\tDescription is required\n";}
		do_unlock(theForm) ;
		if (!chkval(theForm.frm_year_asof.value, <?php print date('Y')-1 . ", " . date('Y'); ?>)) 	{errmsg+= "\tAs-of date error - Year\n";}
		if (!chkval(theForm.frm_month_asof.value, 1,12)) 		{errmsg+= "\tAs-of date error - Month\n";}
		if (!chkval(theForm.frm_day_asof.value, 1,31)) 			{errmsg+= "\tAs-of date error - Day\n";}
		if (!chkval(theForm.frm_hour_asof.value, 0,23)) 		{errmsg+= "\tAs-of time error - Hours\n";}
		if (!chkval(theForm.frm_minute_asof.value, 0,59)) 		{errmsg+= "\tAs-of time error - Minutes\n";}
		if (!datechk_r(theForm))								{errmsg+= "\tAs-of date/time error - future?\n" ;}
		
		if (errmsg!="") {
			do_lock(theForm);
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function validate(theForm)
	</SCRIPT>
	</HEAD>
<?php 
	print (($get_action == "add")||($get_action == "update"))? "<BODY onload = 'do_notify(); ck_frames();' onunload='GUnload();'>\n": "<BODY onLoad = 'ck_frames();'>\n";

	$do_yr_asof = false;		// js year housekeeping

	$optstyles = array ();		// see css

	$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`";				// 1/27/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$optstyles[$row['name']] = $row['name'];	
		}
	unset($result);

	if ($get_action == 'add') {
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$_GET[ticket_id]'"))
			print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
		elseif ($_POST['frm_description'] == '')
			print '<FONT CLASS="warn">Please enter Description.</FONT><BR />';
		else {
			$responder = $sep = "";
			if (array_key_exists('frm_responder', ($_POST))) {
				for ($i=0; $i< count ($_POST['frm_responder']); $i++) {
					$responder .= $sep . $_POST['frm_responder'][$i];		// space separator for multiple responders
					$sep = " ";
					}
				}
			$_POST['frm_description'] = strip_html($_POST['frm_description']); //fix formatting, custom tags etc.

			$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST['frm_meridiem_asof'] : "" ;

			$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
			
     		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]action` (`description`,`ticket_id`,`date`,`user`,`action_type`, `updated`, `responder`) VALUES('$_POST[frm_description]','$_GET[ticket_id]','$now'," . $my_session['user_id'] . ",$GLOBALS[ACTION_COMMENT] ,'$frm_asof','$responder' )";		// 8/24/08
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename(__FILE__), __LINE__);

			$ticket_id = mysql_insert_id();								// just inserted id
			do_log($GLOBALS['LOG_ACTION_ADD'], mysql_insert_id(), $_GET['ticket_id']);
			$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE `id`='" . $_GET['ticket_id'] . "' LIMIT 1";
			$result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);

			add_header($_GET['ticket_id']);
			print '<br /><FONT CLASS="header">Action record has been added.</FONT><BR /><BR />';

			show_ticket($_GET['ticket_id']);
//________________________________________________________________
			print "</BODY>";				// 10/19/08
			
			$addrs = notify_user($_GET['ticket_id'],$GLOBALS['NOTIFY_ACTION_CHG']);		// returns array or FALSE

			if ($addrs) {
?>			
<SCRIPT>

	function do_notify() {
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "TICKET Update - ACTION: ";
		var theId = '<?php print $_POST['frm_ticket_id'];?>';
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId);		// ($to_str, $text, $ticket_id)   10/15/08
		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function
		}

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
//				alert('HTTP error ' + req.status);
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}
	
</SCRIPT>
<?php

			}		// end if($addrs) 
		else {
?>		
<SCRIPT>
	function do_notify() {
		return;
		}			// end function do notify()
</SCRIPT>
<?php		
			}
			
		print "</HTML>";				// 10/19/08
		}		// end else ...
// ____________________________________________________
		exit();

		}		// 	end if($get_action == 'add')

	else if ($get_action == 'delete') {
		if (array_key_exists('confirm', ($_GET))) {
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") or do_error('','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
			print '<FONT CLASS="header">Action deleted</FONT><BR /><BR />';
			add_header($_GET['ticket_id']);
			show_ticket($_GET['ticket_id']);
			}
		else {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));

			print "<FONT CLASS='header'>Really delete action record '" . shorten($row['description'], 24) . "' ? </FONT><BR /><BR />";
			print "<FORM NAME='delfrm' METHOD='post' ACTION='action.php?action=delete&id=$_GET[id]&ticket_id=" . $_GET['ticket_id'] . "&confirm=1'>";
			print "<INPUT TYPE='Submit' VALUE='Yes'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			print "<INPUT TYPE='Button' VALUE='Cancel' onClick='history.back();'></FORM>";
			}

		}				// end if ($get_action == 'delete') 
		
	else if ($get_action == 'update') {		//update action and show ticket
		$responder = $sep = "";
		if (array_key_exists('frm_responder', ($_POST))) {			
			for ($i=0; $i< count ($_POST['frm_responder']); $i++) {
				$responder .= $sep . $_POST['frm_responder'][$i];		// space separator for multiple responders
				$sep = " ";
				}
			}
		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;
				
		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
		$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]action` SET `description`='$_POST[frm_description]', `responder` = '$responder', `updated` = '$frm_asof' WHERE `id`='$_GET[id]' LIMIT 1") or do_error('action.php::update action','mysql_query',mysql_error(),basename( __FILE__), __LINE__);
		$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` =	'$frm_asof' WHERE id='$_GET[ticket_id]' LIMIT 1") 	or do_error('action.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") 			or do_error('action.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		print '<BR /><BR /><FONT CLASS="header">Action updated</FONT><BR /><BR />';
		add_header($_GET['ticket_id']);
		show_ticket($row['ticket_id']);
		}				// end if ($get_action == 'update') 
		
	else if ($get_action == 'edit') {		//get and show action to update
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		$responders = explode(" ", $row['responder']);				// to array
//		dump ($row);
		$do_yr_asof = true;
?>
		<FONT CLASS="header">Edit Action</FONT><BR /><BR />
		<FORM METHOD="post" NAME='ed_frm' ACTION="action.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update"><TABLE BORDER="0">
		<TR CLASS='even' VALIGN='top'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description" WRAP="virtual"><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS='odd'VALIGN='top'><TD><B>Responder:</B></TD>
<?php
//						generate dropdown menu of responders -- if(in_array($rowtemp[id], $row[responder]))

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name` ASC";		// 2/12/09
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		$height = (mysql_affected_rows() + 1) * 16;
		$selected = (in_array("0", $responders))? "SELECTED" : "";	// NA is special case
		print "<TD><SELECT NAME='frm_responder[]' style='width: 150px; height: " . $height ."px;' multiple><OPTION VALUE='0' $selected>NA</OPTION>\n";
    	while ($rowtemp = stripslashes_deep(mysql_fetch_assoc($result))) {
    		$selected = (in_array($rowtemp['id'], $responders))? " SELECTED" : "";
			print "\t<OPTION VALUE='" . $rowtemp['id'] . "'" . $selected . ">" . $rowtemp['name'] . "</OPTION>\n";
			}
		unset ($rowtemp);
		print "\t</SELECT></TD></TR>\n";
?>
		<TR CLASS='even'>
		<TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD>
		<INPUT SIZE=4 NAME="frm_year_asof" VALUE="" MAXLENGTH=4>
		<INPUT SIZE=2 NAME="frm_month_asof" VALUE="" MAXLENGTH=2>
		<INPUT SIZE=2 NAME="frm_day_asof" VALUE="" MAXLENGTH=2>
		<INPUT SIZE=2 NAME="frm_hour_asof" VALUE="" MAXLENGTH=2>:
		<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="" MAXLENGTH=2>
		&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.ed_frm);'>
		</TD></TR>

		<TR CLASS='odd'><TD></TD><TD ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel"	onClick="history.back()" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="Reset" 	onClick="this.form.reset();init();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="Submit"	onClick="return validate(this.form)">
			</TD></TR>
		</TABLE></FORM><BR />
<?php
		}		// end if ($get_action == 'edit')
		
//	else {											// do form
	else if ($get_action == 'form') {
//		print __LINE__; 
//		dump($get_action);
		$do_yr_asof = true;
//		add_header($_GET['ticket_id']);
//		print __LINE__ . "<BR>";
?>
		<BR /><BR /><FONT CLASS="header">Add Action</FONT><BR /><BR />
		<FORM METHOD="post" NAME="add_frm" onSubmit='return validate(this.form);' ACTION="action.php?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
		<TABLE BORDER="0">
		<TR CLASS='even'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD>
			<TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description"></TEXTAREA>
			</TD></TR>
<?php
//						generate dropdown menu of responders
		$query = "SELECT `id`,`name`,`type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name` ASC";	// 2/12/09
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
		$height = (mysql_affected_rows() + 1) * 16;
		print "<TR CLASS='odd' ><TD CLASS='td_label'>Units:</TD>";
		print "<TD><SELECT NAME='frm_responder[]' style='width: 250px; height: " . $height ."px;' multiple><OPTION VALUE='0'>NA</OPTION>\n";
		
    	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			print "\t<OPTION CLASS='" . $optstyles[$row['type']] . "' VALUE='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";
			}
		print "\t</SELECT>\n</TD></TR>";
?>
		<TR CLASS='even'>
		<TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD>
		<INPUT SIZE=4 NAME="frm_year_asof" VALUE="" MAXLENGTH=4>
		<INPUT SIZE=2 NAME="frm_month_asof" VALUE="" MAXLENGTH=2>
		<INPUT SIZE=2 NAME="frm_day_asof" VALUE="" MAXLENGTH=2>
		<INPUT SIZE=2 NAME="frm_hour_asof" VALUE="" MAXLENGTH=2>:
		<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="" MAXLENGTH=2>
		<INPUT TYPE="hidden" NAME = "frm_ticket_id" VALUE = "<?php print $_GET['ticket_id'];?>">
		&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.add_frm);'>
		</TD></TR>
		<TR CLASS='odd'><TD></TD><TD>
		<INPUT TYPE="button" VALUE="Cancel"	onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" VALUE="Reset"	onClick="this.form.reset();init();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" VALUE="Submit"	onClick="return validate(this.form)"></TD></TR>

		</TABLE><BR />
		</FORM>
<?php
		}				// end if ($get_action == 'form')

//				 common to all
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
</FORM>	
</BODY>
<?php
	if ($do_yr_asof) { 		// for ADD and EDIT only
?>
<SCRIPT LANGUAGE="Javascript">
init();

function init () {
	do_unlock(document.forms[0])
	var now = new Date();
	if (now.getYear()>2000) {
		document.forms[0].frm_year_asof.value= now.getYear() - 2000;
		}
	else {
		if (now.getYear()>100) {
			document.forms[0].frm_year_asof.value=now.getYear() - 100;
			}
		else {
			document.forms[0].frm_year_asof.value=now.getYear();
			}
		}
	document.forms[0].frm_year_asof.value=parseInt(document.forms[0].frm_year_asof.value)+ 2000;
	document.forms[0].frm_month_asof.value=now.getMonth()+1;
	document.forms[0].frm_day_asof.value=now.getDate();
	document.forms[0].frm_hour_asof.value=now.getHours();
	document.forms[0].frm_minute_asof.value=now.getMinutes() ;
	if (document.forms[0].frm_hour_asof.value<10) 	{ document.forms[0].frm_hour_asof.value = "0" + document.forms[0].frm_hour_asof.value; }
	if (document.forms[0].frm_minute_asof.value<10) 	{ document.forms[0].frm_minute_asof.value = "0" + document.forms[0].frm_minute_asof.value; }
	do_lock(document.forms[0]);
	}
</SCRIPT>
<?php
		}		// end 	if ($do_yr_asof)
	
?>
</HTML>
<?php
//function new_notify_user($ticket_id,$action) {								// 10/20/08
//	if (get_variable('allow_notify') != '1') return FALSE;					//should we notify?
//	
//	$addrs = array();													// 
//	
//	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE (`ticket_id`='$ticket_id' OR `ticket_id`=0)  AND `on_action` = '1'";	// all notifies for given ticket - or any ticket 10/22/08
//	print __LINE__;
//	dump ($query);
//	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
//		dump ($row['email_address']);
////		if (($action == $GLOBALS['NOTIFY_ACTION'] AND $row['on_action']) OR ($action == $GLOBALS['NOTIFY_TICKET'] AND $row['on_ticket'])){
//			if (is_email($row['email_address'])) {
//				print __LINE__;
//				array_push($addrs, $row['email_address']); 
//				}		// save for emailing
////			}
//		}
//	dump($addrs);
//	return (empty($addrs))? FALSE: $addrs;
//	}
//
?>