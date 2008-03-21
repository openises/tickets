<?php 
require_once('functions.inc.php'); 
do_login(basename(__FILE__));

if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
	
$get_action = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['action'])))) ) ? "" : $_GET['action'] ;
	
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Patient Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<?php
if ($get_action == 'add') {		
	$api_key = get_variable('gmaps_api_key');		// empty($_GET)
?>
<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT src="graticule.js" type="text/javascript"></SCRIPT>
<?php
	}	
?>

<SCRIPT>
function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	}		// end function ck_frames()
	
	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}				

	if (parent.frames["upper"]) {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}

	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_name.value == "")			{errmsg+= "\tNAME is required\n";}
		if (theForm.frm_description.value == "")	{errmsg+= "\tDESCRIPTION is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		}				// end function validate(theForm)
	</SCRIPT>
	</HEAD>
<?php 
	print ($get_action == "add")? "<BODY onload = 'ck_frames();' onunload='GUnload();'>\n": "<BODY onLoad = 'ck_frames();'>\n";
	if ($get_action == 'add') {		/* update ticket */
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$_GET[ticket_id]'"))
			print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
		elseif ($_POST['frm_description'] == '')
			print '<FONT CLASS="warn">Description field is empty. Please try again.</FONT><BR />';
		else {
			$_POST['frm_description'] = strip_html($_POST['frm_description']); 				//fix formatting, custom tags etc.

			$post_frm_meridiem_asof = empty($_POST['frm_meridiem_asof'])? "" : $_POST['frm_meridiem_asof'] ;
			$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$post_frm_meridiem_asof";

     		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]patient` (`description`,`ticket_id`,`date`,`user`,`action_type`, `name`, `updated`) VALUES('$_POST[frm_description]','$_GET[ticket_id]','$now',$my_session[user_id],$GLOBALS[ACTION_COMMENT], '$_POST[frm_name]', '$frm_asof')";
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			do_log($GLOBALS['LOG_PATIENT_ADD'], mysql_insert_id(), $_GET['ticket_id']);

			$result = mysql_query("UPDATE $GLOBALS[mysql_prefix]ticket SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'") or do_error($query,mysql_error(), basename( __FILE__), __LINE__);

			print '<br><br><FONT CLASS="header">Patient record has been added</FONT><BR /><BR />';
			add_header($_GET['ticket_id']);
			show_ticket($_GET['ticket_id']);
			notify_user($_GET['ticket_id'],$NOTIFY_ACTION);
			exit();
			}
		}
	else if ($get_action == 'delete') {
		if (array_key_exists('confirm', ($_GET))) {
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]'";
			$result = mysql_query($query) or do_error('patient.php::del patient record',$query,mysql_error(), basename( __FILE__), __LINE__);
			print '<FONT CLASS="header">Patient record deleted</FONT><BR /><BR />';
			show_ticket($_GET['ticket_id']);
			}
		else {
			print "<FONT CLASS='header'>Really delete Patient record # '$_GET[id]'?</FONT><BR /><BR />";
			print "<FORM METHOD='post' ACTION='patient.php?action=delete&id=$_GET[id]&ticket_id=$_GET[ticket_id]&confirm=1'><INPUT TYPE='Submit' VALUE='Yes'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			print "<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'></FORM>";
			}
		}
	else if ($get_action == 'update') {		//update patient record and show ticket

		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
		$query = "UPDATE `$GLOBALS[mysql_prefix]patient` SET `description`='$_POST[frm_description]' , `name`='$_POST[frm_name]', `updated` = '$frm_asof' WHERE id='$_GET[id]'";
		$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$query = "UPDATE $GLOBALS[mysql_prefix]ticket SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'";
		$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$result = mysql_query("SELECT ticket_id FROM $GLOBALS[mysql_prefix]patient WHERE id='$_GET[id]'") or do_error('patient.php::update patient record','mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		add_header($_GET['ticket_id']);
		
		print '<br><br><FONT CLASS="header">Patient record updated</FONT><BR /><BR />';
		show_ticket($row['ticket_id']);
		}
	else if ($get_action == 'edit') {		//get and show action to update
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE id='$_GET[id]'";
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_array($result));
//		dump($row);
//		dump(stripslashes($row['description']));
?>
		<FONT CLASS="header">Edit Patient Record</FONT><BR /><BR />
		<FORM METHOD='post' NAME='patientEd' onSubmit='return validate(document.patientEd);' ACTION="patient.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update"><TABLE BORDER="0">
		<TR CLASS='even' ><TD><B>Name: <font color='red' size='-1'>*</font></B></TD><TD><INPUT TYPE="text" NAME="frm_name" value="<?php print $row['name'];?>" size="32"></TD></TR>
		<TR CLASS='odd'  VALIGN='top'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description"><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS='even'>
		<TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD>
		<INPUT SIZE=4 NAME="frm_year_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_month_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_day_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_hour_asof" VALUE="">:
		<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="">
		</TD></TR>

		<TR CLASS='odd' ><TD></TD><TD ALIGN='center'><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Submit" VALUE="Submit"></TD></TR>
		</TABLE><BR />
		<?php
		}
	else {
?>
		<BR /><BR /><FONT CLASS="header">Add Patient Record</FONT><BR /><BR />
		<FORM METHOD="post" NAME='patientAdd' onSubmit='return validate(document.patientAdd);'  ACTION="patient.php?ticket_id=<?php print $_GET['ticket_id'];?>&action=add"><TABLE BORDER="0">
		<TR CLASS='even' ><TD><B>Name:</B> <font color='red' size='-1'>*</font></TD><TD><INPUT TYPE="text" NAME="frm_name" value="" size="32"></TD></TR>
		<TR CLASS='odd' ><TD><B>Description:</B></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description"></TEXTAREA></TD></TR>
		<TR CLASS='even'>
		<TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD>
		<INPUT SIZE=4 NAME="frm_year_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_month_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_day_asof" VALUE="">
		<INPUT SIZE=2 NAME="frm_hour_asof" VALUE="">:
		<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="">
		</TD></TR>
		<TR CLASS='odd'><TD></TD><TD><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Submit" VALUE="Submit"></TD></TR>
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