<?php

//	CONSTANT settings specific to this script
// end file-specific constants

/*
1/18/09 initial version
2/30/09 delete functions added
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
//dump($_POST);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets Log Processing</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="Tickets Log Entry"">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT>
function validate_del() {
	if (document.del_form.frm_days_val.value==0) { 
		alert("check days value");
		return false;
		}
	else {
		return true;
		}
	}			// end function
</SCRIPT>
</HEAD>
<BODY>
<?php
if (empty($_POST)) {

	if (is_guest()) {
?>
<CENTER><BR /><BR /><BR /><BR /><BR /><H3>Guests not allowed Log access. </CENTER><BR /><BR />

<INPUT TYPE='button' value='Cancel' onClick = 'window.exit();'>
<?php } ?>


<FORM NAME="log_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>">
<TABLE>
<TR CLASS = 'even' ><TH COLSPAN=2>Station Log</TH></TR>
<TR CLASS = 'odd'><TD>Log entry:</TD><TD><TEXTAREA NAME="frm_comment" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD></TR>
<TR CLASS = 'even'><TD COLSPAN=2 ALIGN='center'>
<INPUT TYPE = 'button' VALUE='Submit' onClick="document.log_form.submit()" />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE = 'button' VALUE='Reset' onClick="document.log_form.reset()" />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE = 'button' VALUE='Review' onClick="document.log_form.func.value='view';document.log_form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (is_super()) { ?>
	<INPUT TYPE = 'button' VALUE='Deletion' onClick="document.log_form.func.value='del';document.log_form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;
<?php 	} ?>
</TD></TR>
<TR><TD COLSPAN=2 ALIGN='center'><BR /><INPUT TYPE = 'button' VALUE='Cancel' onClick="window.close()" /></TD></TR>
</TABLE>
<INPUT TYPE='hidden' NAME='func' VALUE='add'>
</FORM>
<?php 
	}

else {										// not empty

	function my_show_log () {				// returns  string
		global $evenodd ;					// class names for alternating table row colors
		$types = array();
		$types[$GLOBALS['LOG_SIGN_IN']]				="Login";
		$types[$GLOBALS['LOG_SIGN_OUT']]			="Logout";
		$types[$GLOBALS['LOG_COMMENT']]				="Comment";		// misc comment
		$types[$GLOBALS['LOG_INCIDENT_OPEN']]		="Incident open";
		$types[$GLOBALS['LOG_INCIDENT_CLOSE']]		="Incident close";
		$types[$GLOBALS['LOG_INCIDENT_CHANGE']]		="Incident change";
		$types[$GLOBALS['LOG_ACTION_ADD']]			="Action added";
		$types[$GLOBALS['LOG_PATIENT_ADD']]			="Patient added";
		$types[$GLOBALS['LOG_ACTION_DELETE']]		="Action delete";
		$types[$GLOBALS['LOG_PATIENT_DELETE']]		="Patient delete";
		$types[$GLOBALS['LOG_INCIDENT_DELETE']]		="Incident delete";			// 6/26/08
		$types[$GLOBALS['LOG_UNIT_STATUS']]			="Unit status change";
		$types[$GLOBALS['LOG_UNIT_COMPLETE']]		="Unit complete";
		$types[$GLOBALS['LOG_UNIT_CHANGE']]			="Unit change";				// 6/26/08
		
		$query = "
			SELECT *,  `u`.`user` AS `thename`, `$GLOBALS[mysql_prefix]log`.`info` AS `theinfo` FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ($GLOBALS[mysql_prefix]log.who = u.id)
			ORDER BY `$GLOBALS[mysql_prefix]log`.`when` ASC
			";
//		dump ($query);
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$i = 1;
		$print = "<TABLE ALIGN='left' CELLSPACING = 1 >";
		$do_hdr = TRUE; 
		$day_part="";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			if ($do_hdr) {
				$print .= "<TR CLASS='even'><TH COLSPAN=99> Station Log</TH></TR>";
				$print .= "<TR CLASS='odd'><TH ALIGN='center'>When</TH><TH ALIGN='center'>Code</TH><TH ALIGN='center'>By</TH><TH ALIGN='center'>Info</TH><TH ALIGN='center'>From</TH></TR>";
				$do_hdr = FALSE;
				}
			switch ($row['code']):
				case $GLOBALS['LOG_SIGN_IN']:
				case $GLOBALS['LOG_SIGN_OUT']:
				case $GLOBALS['LOG_COMMENT']:
				$i++;
					$print .= "<TR CLASS='{$evenodd[$i%2]}'>";
					$temp = split(" ", $row['when']);
					if ($temp[0]==$day_part) {
						$the_date = $temp[1];
						}
					else {
						$the_date = $row['when'];
						$day_part = $temp[0];
						}					
					$print .= 
						"<TD ALIGN='right'>&nbsp;". $the_date . "&nbsp;</TD>".
						"<TD>". $types[$row['code']] . "</TD>".
						"<TD>". $row['thename'] . "</TD>".
						"<TD>". $row['theinfo'] . "</TD>".
						"<TD>&nbsp;". $row['from'] . "</TD>".
						"</TR>";
					    break;
					endswitch;
			
			}
		$print .= "</TABLE>";
		return $print;
		}		// end function show_log ()

	switch ($_POST['func']) {
		case "add":
			do_log($GLOBALS['LOG_COMMENT'], $ticket_id=0, $responder_id=0, trim($_POST['frm_comment']));
			break;
		case "view":
			print my_show_log ();
			print "<BR CLEAR='left'><BR>";
			break;
		case "del":		// 2/30/09
?>
	<CENTER>
	<FORM NAME="del_form" METHOD="post" ACTION = "<?php print basename(__FILE__); ?>">
	<INPUT TYPE="hidden" NAME="func" VALUE="del_db" />

	Delete log entries older than:
		one day&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "1" onClick = "document.del_form.frm_days_val.value='this.value';" />&nbsp;&nbsp;&nbsp;&nbsp;
		one week&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "7"  onClick = "document.del_form.frm_days_val.value='this.value';" />&nbsp;&nbsp;&nbsp;&nbsp;
		two weeks&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "14" onClick = "document.del_form.frm_days_val.value='this.value';"  />&nbsp;&nbsp;&nbsp;&nbsp;
		one month&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "30" onClick = "document.del_form.frm_days_val.value='this.value';"  /><BR /><BR /><BR />
		<INPUT TYPE='button' VALUE='OK - do it' onClick = "if ((validate_del()) && (confirm('Confirm deletion - CANNOT BE UNDONE!'))) {document.del_form.submit();}" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE='button' VALUE='Cancel' onClick = "document.can_form.submit();" />
		<INPUT TYPE='hidden' NAME='frm_days_val' VALUE=0>
		</FORM>

	<FORM NAME="can_form" METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>

<?php
			break;
			case "del_db": 		// 2/30/09
				$the_date = mysql_format_date(time() - (get_variable('delta_mins')*60));
				$query = "DELETE from `$GLOBALS[mysql_prefix]log` WHERE `when` < ('{$the_date}' - INTERVAL {$_POST['frm_del']} DAY)";
//				dump($query);
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	// 
				print "<BR /> <BR /> " . mysql_affected_rows() . " Log entries deleted<BR /> <BR /> <BR /> ";
			break;
	
		default:
		    echo "ERROR - ERROR";		
	}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE='button' VALUE='Finished' onClick = 'self.close()' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE='button' VALUE='Log entry' onClick = 'document.dummy_form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<FORM NAME="dummy_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>"></FORM>
</BODY>
</HTML>
<?php } ?>

