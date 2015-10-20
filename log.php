<?php

//	CONSTANT settings specific to this script
// end file-specific constants

/*
1/18/09 initial version
2/30/09 delete functions added
3/12/10 session started
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
4/19/11 obtain log codes via a 'require'
4/5/11 get_new_colors() added
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

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
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
.box { background-color: transparent; border: 0px solid #000000; color: #000000; padding: 0px; position: absolute; z-index:1000; }
.bar { background-color: #DEE3E7; color: #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; }
.content { padding: 1em; }
</STYLE>
<SCRIPT SRC="./js/misc_function.js" type="text/javascript"></SCRIPT>

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

function get_new_colors() {								// 4/5/11
	window.location.href = '<?php print basename(__FILE__);?>';
	}


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
		require_once('./incs/log_codes.inc.php');				// returns $types array - 4/19/11
		
		$query = "
			SELECT *,  `u`.`user` AS `thename`, `$GLOBALS[mysql_prefix]log`.`info` AS `theinfo` FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ($GLOBALS[mysql_prefix]log.who = u.id)
			ORDER BY `$GLOBALS[mysql_prefix]log`.`when` ASC;";
//		dump ($query);
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$i = 1;
		$print = "<A NAME='page_top'></A>\n<TABLE ALIGN='left' BORDER = 0 CELLSPACING = 1  >";
		$do_hdr = TRUE; 
		$day_part="";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			if ($do_hdr) {
				$print .= "<TR CLASS='even'><TH COLSPAN=99> Station Log</TH></TR>\n";
				$print .= "<TR CLASS='odd'><TD ROWSPAN=10000 ALIGN='right'><BR /><BR /><BR /><BR />
					<INPUT TYPE='button' VALUE='Finished' onClick = 'self.close()' STYLE = 'width:80px; margin-top:10px;margin-right:10px;' />&nbsp;&nbsp;<BR />
					<INPUT TYPE='button' VALUE='Log entry' STYLE = 'width:80px; margin-top:10px;margin-right:10px;' onClick = 'document.dummy_form.submit();' />&nbsp;&nbsp;</TD>
					<TH ALIGN='center'>When</TH><TH ALIGN='center'>Code</TH><TH ALIGN='center'>By</TH><TH ALIGN='center'>Info</TH><TH ALIGN='center'>From</TH></TR>\n";
				$do_hdr = FALSE;
				}
			switch ($row['code']):
				case $GLOBALS['LOG_SIGN_IN']:
				case $GLOBALS['LOG_SIGN_OUT']:
				case $GLOBALS['LOG_COMMENT']:
				$i++;
					$print .= "<TR CLASS='{$evenodd[$i%2]}' VALIGN='bottom'>";
					$temp = preg_split('/ /',  $row['when']);				// date and time
					if ($temp[0]==$day_part) {
						$the_date = $temp[1];
						}
					else {
						$the_date = "<U>{$temp[0]}</U> {$temp[1]}";
						$day_part = $temp[0];
						}					
					$print .= 
						"<TD ALIGN='right'>&nbsp;". $the_date . "&nbsp;</TD>".
						"<TD>". $types[$row['code']] . "</TD>".
						"<TD>". $row['thename'] . "</TD>".
						"<TD>". $row['theinfo'] . "</TD>".
						"<TD>&nbsp;". $row['from'] . "</TD>".
						"</TR>\n";
					    break;
					endswitch;
			
			}
		$print .= "<TR><TD COLSPAN=99 ALIGN='center'><BR /><B>End of Station Log Report</B><BR /><BR /><A HREF='#page_top'><U>to top</U></A></TD></TR>\n";
		$print .= "</TABLE><BR /><BR /><CENTER></CENTER>";
		return $print;
		}		// end function my_show_log ()

	switch ($_POST['func']) {
		case "add":
			do_log($GLOBALS['LOG_COMMENT'], $ticket_id=0, $responder_id=0, strip_tags(trim($_POST['frm_comment'])));
			
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
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	// 
				print "<BR /> <BR /> " . mysql_affected_rows() . " Log entries deleted<BR /> <BR /> <BR /> ";
			break;
	
		default:
		    echo "ERROR - ERROR";		
	}

?>

<FORM NAME="dummy_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>"></FORM>
</BODY>
</HTML>
<?php } ?>

