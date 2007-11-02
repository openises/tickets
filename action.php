<?php 
	error_reporting(E_ALL);

	require_once('functions.inc.php'); 
	do_login(basename(__FILE__));
	$api_key = get_variable('gmaps_api_key');
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Action Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT src="./incs/multiSelect.js"></SCRIPT>
<SCRIPT>				
	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}				
<?php
	print "var user = '";
	print $_SESSION['user_name'];
	print "'\n";
	print "\nvar level = '" . get_level_text ($_SESSION['level']) . "'\n";		// get_level_text ($_SESSION['level'])
?>	
	parent.frames["upper"].document.getElementById("whom").innerHTML  = user;
	parent.frames["upper"].document.getElementById("level").innerHTML  = level;

	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_description.value == "")		{errmsg+= "\tDescription is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		}				// end function validate(theForm)
	</SCRIPT>
	</HEAD>
<BODY>
<?php 
	$get_action = (empty($_GET['action']))? "" : $_GET['action'];
	
//	if ($_GET['action'] == 'add') {		/* update ticket */
	if ($get_action == 'add') {		/* update ticket */
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$_GET[ticket_id]'"))
			print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
		elseif ($_POST['frm_description'] == '')
			print '<FONT CLASS="warn">Description field is empty. Please try again.</FONT><BR />';
		else {
			$responder = $sep = "";
			for ($i=0; $i< count ($_POST['frm_responder']); $i++) {
				$responder .= $sep . $_POST['frm_responder'][$i];		// space separator for multiple responders
				$sep = " ";
				}
			$_POST['frm_description'] = strip_html($_POST['frm_description']); //fix formatting, custom tags etc.

			$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

			$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
			
     		$query 	= "INSERT INTO $GLOBALS[mysql_prefix]action (description,ticket_id,date,user,action_type, updated, responder) VALUES('$_POST[frm_description]','$_GET[ticket_id]','$now',$_SESSION[user_id],$GLOBALS[ACTION_COMMENT] ,'$frm_asof','$responder' )";
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), __FILE__, __LINE__);

			$query = "UPDATE $GLOBALS[mysql_prefix]ticket SET `updated` = '$frm_asof' WHERE `id`='$_GET[ticket_id]'";
			$result = mysql_query($query) or do_error($query,$query, mysql_error(), __FILE__, __LINE__);

			add_header($_GET['ticket_id']);
			print '<br /><FONT CLASS="header">Action record has been added.</FONT><BR /><BR />';

			show_ticket($_GET['ticket_id']);
			notify_user($_GET['ticket_id'],$NOTIFY_ACTION);
			exit();
			}
		}
	else if ($get_action == 'delete') {
		if (array_key_exists('confirm', ($_GET))) {
			$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]action WHERE id='$_GET[id]'") or do_error('action.php::del action','mysql_query',mysql_error(), __FILE__, __LINE__);
			print '<FONT CLASS="header">Action deleted</FONT><BR /><BR />';
			show_ticket($_GET['ticket_id']);
			}
		else {
			print "<FONT CLASS='header'>Really delete action record # '$_GET[id]'?</FONT><BR /><BR />";
			print "<FORM METHOD='post' ACTION='action.php?action=delete&id=$_GET[id]&ticket_id=$_GET[ticket_id]&confirm=1'>";
			print "<INPUT TYPE='Submit' VALUE='Yes'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			print "<INPUT TYPE='Button' VALUE='Cancel' onClick = 'document.can_Form.submit();'></FORM>";
			}

		}				// end if ($get_action == 'delete') 
		
	else if ($get_action == 'update') {		//update action and show ticket
			$responder = $sep = "";
			for ($i=0; $i< count ($_POST['frm_responder']); $i++) {
				$responder .= $sep . $_POST['frm_responder'][$i];		// space separator for multiple responders
				$sep = " ";
				}
		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;
				
		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
		$result = mysql_query("UPDATE $GLOBALS[mysql_prefix]action SET description='$_POST[frm_description]', responder = '$responder', `updated` = '$frm_asof' WHERE id='$_GET[id]'") or do_error('action.php::update action','mysql_query',mysql_error(), __FILE__, __LINE__);
		$result = mysql_query("UPDATE $GLOBALS[mysql_prefix]ticket SET `updated` =	'$frm_asof' WHERE id='$_GET[ticket_id]'") or do_error('action.php::update action','mysql_query',mysql_error(), __FILE__, __LINE__);
		$result = mysql_query("SELECT ticket_id FROM $GLOBALS[mysql_prefix]action WHERE `id`='$_GET[id]'") or do_error('action.php::update action','mysql_query',mysql_error(), __FILE__, __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		add_header($_GET['ticket_id']);
		print '<BR /><BR /><FONT CLASS="header">Action updated</FONT><BR /><BR />';
		show_ticket($row['ticket_id']);
		}				// end if ($get_action == 'update') 
		
	else if ($get_action == 'edit') {		//get and show action to update
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `id`='$_GET[id]'";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), __FILE__, __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		$responders = explode(" ", $row['responder']);				// to array
?>
		<FONT CLASS="header">Edit Action</FONT><BR /><BR />
		<FORM METHOD="post" NAME='action'  onSubmit='return validate(document.action)' ACTION="action.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update"><TABLE BORDER="0">
		<TR CLASS='even' VALIGN='top'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description"><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS='odd'VALIGN='top'><TD><B>Responder:</B></TD>
<?php
//						generate dropdown menu of responders -- if(in_array($rowtemp[id], $row[responder]))

		$query = "SELECT * FROM $GLOBALS[mysql_prefix]responder";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$height = (mysql_affected_rows() + 1) * 16;
		$selected = (in_array("0", $responders))? "SELECTED" : "";	// NA is special case
		print "<TD><SELECT NAME='frm_responder[]' style='width: 150px; height: " . $height ."px;' multiple><OPTION VALUE='0' $selected>NA</OPTION>\n";
		$optstyles = array (1 => "Meds", 2 => "Fire", 3 => "Poli", 4 => "Othr");		// see css
    	while ($rowtemp = stripslashes_deep(mysql_fetch_array($result))) {
    		$temp = $row[type];
    		$selected = (in_array($rowtemp[id], $responders))? " SELECTED" : "";
			print "<OPTION VALUE='$rowtemp[id]'$selected>$rowtemp[name]</OPTION>\n";
			}
		unset ($rowtemp);
		print "</SELECT></TD></TR>\n";
?>
		<TR CLASS='even'>
		<TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD>
		<INPUT SIZE=4 NAME="frm_year_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_month_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_day_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_hour_asof" VALUE="">:
		<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="">
		</TD></TR>

		<TR CLASS='odd'><TD></TD><TD ALIGN='center'><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back()" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Submit" VALUE="Submit"></TD></TR>
		</TABLE><BR />
<?php
		}		// end if ($_GET['action'] == 'edit')
		
	else {											// do form
//		add_header($_GET['ticket_id']);
//		print __LINE__ . "<BR>";
?>
		<BR /><BR /><FONT CLASS="header">Add Action</FONT><BR /><BR />
		<FORM METHOD="post" NAME="action" onSubmit='return validate(document.action);' ACTION="action.php?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
		<TABLE BORDER="0">
		<TR CLASS='even'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description"></TEXTAREA></TD></TR>
<?php
//						generate dropdown menu of responders
//		$query = "SELECT `id`,`name`,`type` FROM $GLOBALS[mysql_prefix]responder";
		$query = "SELECT `id`,`name`,`type` FROM $GLOBALS[mysql_prefix]responder";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$height = (mysql_affected_rows() + 1) * 16;
		print "<TR CLASS='odd' ><TD CLASS='td_label'>Units:</TD>";
		print "<TD><SELECT NAME='frm_responder[]' style='width: 150px; height: " . $height ."px;' multiple><OPTION VALUE='0'>NA</OPTION>\n";
		
		$optstyles = array (1 => "Meds", 2 => "Fire", 3 => "Poli", 4 => "Othr");		// see css
    	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
    		$temp = $row['type'];
			print "<OPTION CLASS='" . $optstyles[$temp] . "' VALUE='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";
			}
		print "</SELECT>\n</TD></TR>";
?>
		<TR CLASS='even'>
		<TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD>
		<INPUT SIZE=4 NAME="frm_year_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_month_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_day_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_hour_asof" VALUE="">:
		<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="">
		</TD></TR>
		<TR CLASS='odd'><TD></TD><TD><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Submit" VALUE="Submit"></TD></TR>
		</TABLE><BR />
		</FORM>
<?php
		}
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
</FORM>	
</BODY>
<SCRIPT LANGUAGE="Javascript">
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

</SCRIPT>
</HTML>
