<?php
/*
8/20/09	initial release
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Close Incident</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
</HEAD>
<BODY onLoad = "if(document.frm_text) {document.frm_note.frm_text.focus() ;}"><CENTER>
<?php
if (empty($_POST)) { 

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . quote_smart($_GET['ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);
		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			do_is_closed();
			}
		else {
			do_is_start();
			}
		}		// end if (empty($_POST))
		
else {			// not empty then is finished
	do_is_finished();
	}				 // end else

function do_is_closed() {
	global $row;
?>		
	<H3>Call '<?php print $row['scope'];?>' is already closed</H3><BR /><BR />
	<INPUT TYPE = 'button' VALUE = 'Cancel' onClick = 'window.close()'>	
	</BODY>
	</HTML>
<?php		
	}				// end function do_is_closed()
	
function do_is_start() {
?>
	<H4>Enter Incident Close Information</H4>
	<FORM NAME='frm_note' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
	<TABLE ALIGN = 'center'>
	
	<TR CLASS='even'><TD CLASS='td_label'  ALIGN='right'>Run End:&nbsp;</TD><TD>
	<?php 
		print generate_date_dropdown('problemend',0, FALSE);
	?>	
			</TD></TR>
	<TR CLASS='odd'><TD ALIGN='right'CLASS='td_label' >Disposition:&nbsp;</TD>
		<TD><TEXTAREA NAME='frm_text' COLS=56 ROWS = 3></TEXTAREA>
			</TD></TR>
	<TR CLASS='even'><TD></TD><TD ALIGN = 'left'>
	<INPUT TYPE = 'button' VALUE = 'Cancel' onClick = 'window.close()' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE = 'button' VALUE = 'Reset' onClick = 'this.form.reset()' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE = 'button' VALUE = 'Next' onClick = 'this.form.submit()' />
	</TD></TR>
	</TABLE>
	<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='<?php print $_GET['ticket_id']; ?>' />
	</FORM>
	</BODY>
	</HTML>
<?php
	}		//end function do_is_start()

function do_is_finished(){
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
		$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  "{$_POST['frm_year_problemend']}-{$_POST['frm_month_problemend']}-{$_POST['frm_day_problemend']} {$_POST['frm_hour_problemend']}:{$_POST['frm_minute_problemend']}:00" : "NULL";

		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `problemend`= " . quote_smart($frm_problemend) . ", `comments`=" . quote_smart(trim($_POST['frm_text'])) . ", `status` = " . $GLOBALS['STATUS_CLOSED']  . " WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);
//		dump($query);
		do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $_POST['frm_ticket_id'])	;
?>
<H3>Call '<?php print $row['scope'];?>' closed</H3><BR /><BR />
<INPUT TYPE = 'button' VALUE = 'Finished' onClick = 'window.close()'>
</BODY>
</HTML>

<?php
		unset($result);

	} // end function do_is_finished()
		
?>
