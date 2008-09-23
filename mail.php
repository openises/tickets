<?php
/*
9/17/08 notify user if no contact addresses
*/
require_once('./incs/functions.inc.php');
if($istest) {
	dump ($_GET);
	dump ($_POST);
	}
extract ($_GET);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Mail Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<?php

function do_insert_settings($name,$value) {
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` (`name`,`value`) VALUES('$name','$value')"; 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

if (!empty ($_POST)) {
	extract ($_POST);
	if ((array_key_exists('frm_to', ($_POST))) && (count($frm_to)>0)) {
		$ticket_id = $_POST['frm_ticket_id'];
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
		$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));

//		dump ($t_row);		
		$eol = "\n";
	
		$to_sep = "";
		$tostr	 = "";
		for ($i = 0; $i< count($frm_to); $i++) {
			$tostr .= $to_sep . stripslashes($frm_to[$i]);
			$to_sep = ",";									// comma separate addr string
			}
		$temp = trim(stripslashes_deep($_POST['frm_text']));
		$message  = empty($temp)? "" : "Note: " . $temp . $eol;
		$message .= "Tickets host: ".get_variable('host').$eol;
		$message .= "Incident: " . $t_row['scope'] . " (#" .$t_row['id'] . ")" . $eol;
		$message .= "Priority: " . get_severity($t_row['severity']);
		$message .= "      Nature: " . get_type($t_row['in_types_id']) . $eol;
		$message .= "Written: " . $t_row['date'] . $eol;
		$message .= "Updated: " . $t_row['updated'] . $eol;
		$message .= "Reported by: " . $t_row['contact'] .", Phone: " . format_phone ($t_row['phone']) . $eol;
		$message .= "Phone: " . format_phone ($t_row['phone']) .  $eol;
		$message .= "Status: ".get_status($t_row['status']).$eol.$eol;
		$message .= "Address: " . $t_row['street'] . " "  . $t_row['city'] . " " . $t_row['state'] . $eol;
		$message .= "Description: ".wordwrap($t_row['description']).$eol;
		$message .= "Comments: ".wordwrap($t_row['comments']).$eol;
		$message .= "Run Start: " . $t_row['problemstart'] . " Incident End: " . $t_row['problemend'] .$eol;
		$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . "\n";
		$message = wordwrap($message, 70);
//	add patient record to message
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE ticket_id='$ticket_id'";
		$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_affected_rows()>0) {
			$message .= "\nPatient:\n";
			while($pat_row = stripslashes_deep(mysql_fetch_array($ticket_result))){
				$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
				}
			}
//	add actions to message
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$ticket_id'";
		if (mysql_affected_rows()>0) {
			$message .= "\nActions:\n";
			$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while($act_row = stripslashes_deep(mysql_fetch_array($ticket_result))) {
				$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
				}
			}
		$message .= "\nThis is an automatically generated message sent by Tickets CAD.";
		$message = str_replace("\n.", "\n..", $message);		// see manual re mail win platform peculiarities
	
		$subject = "Ticket: " . shorten($t_row['scope'], 36);
		$host = get_variable('host');
		$headers = 'From: Tickets_CAD@' .$host . "\r\n" .
		    'Reply-To: '. $frm_reply_to ."\r\n" .
		    'X-Mailer: PHP/' . phpversion();

//		dump ($message);		
	
		mail($tostr, $subject, $message, $headers);
		$caption = "Email sent";
		}
	else {
		$caption = "No addresees - Email not sent!";	
		}
?>	
	</HEAD>
	<BODY>
	<CENTER><BR /><BR /><BR /><BR /><BR /><h3><?php print $caption; ?></h3><BR /><BR />
	<FORM NAME='can_Form' METHOD="get" ACTION = "<?php print basename( __FILE__); ?>" >
	<INPUT TYPE="button" VALUE = "Close" onClick = "self.close()"></CENTER>
	</FORM>		
	</BODY></HTML>

<?php	
		}
	else {
		$ticket_id = $_GET['ticket_id'];
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
		$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
//		if (!$reply_to = get_variable("email_reply_to"))  {
//			$reply_to = "<INPUT TYPE='text' NAME='frm_reply_to' SIZE=36 VALUE=''>";
//			}
?>
	<SCRIPT src="./js/multiSelect.js"></SCRIPT>

	<SCRIPT>
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
	
	function OKaddr(theStr) {
		var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return filter.test(theStr);
		}

	var line = 1;
	var max=19;
	function do_more () {
		if (line==max) {return;}
		else {
			elem="F"+line;
			document.getElementById(elem).style.display = '';		// show it
			line++;
			}
		}		// end function do_more ()
		
	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_reply_to) {
			if ((theForm.frm_reply_to.value.trim().length == 0) || (!OKaddr(theForm.frm_reply_to.value))) {
								errmsg+= "\tValid Replies-to address is required\n";}
			}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			return true;
			}
		}				// end function val idate()


	</SCRIPT>
	</HEAD>
	<BODY>
	<FORM METHOD="post" ACTION="<?php print basename( __FILE__); ?>" NAME="mailit" onsubmit="return validate(document.mailit);" >
	<INPUT TYPE='hidden' NAME = 'frm_ticket_id' VALUE='<?php print $ticket_id; ?>'>
	<TABLE>
	<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><BR /><B>Enter e-mail Information<BR /><BR /></TD></TR>
	<TR CLASS='odd'><TD>Ticket:</TD><TD><B><?php print shorten($t_row['scope'], 48); ?></B></TD></TR>
	<TR CLASS='even'><TD><NOBR>Replies to:&nbsp;</NOBR></TD>				<TD><INPUT TYPE='text' NAME= 'frm_reply_to' SIZE = 32 VALUE = '<?php print get_variable("email_reply_to");?>'></TD></TR>
	<TR CLASS='odd'><TD>Add'l text:</TD>		<TD><TEXTAREA ROWS = 2 COLS=36 NAME='frm_text'></TEXTAREA></TD></TR>
<?php
//						generate dropdown menu of contacts
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` ORDER BY `name` ASC";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		if (mysql_affected_rows()>0) {				// 9/17/08
			$height = (mysql_affected_rows() + 1) * 16;
			print "<TR CLASS='even'><TD>To:</TD>";
			print "<TD><SELECT NAME='frm_to[]' style='width: 250px; height: " . $height ."px;' multiple >\n";
	    	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
				print "\t<OPTION VALUE='" . $row['email'] . "'>" . $row['name'] . "</OPTION>\n";
				}
			print "\t</SELECT>\n</TD></TR>";
			}				// end (mysql_affected_rows()>0)
		else {
			print "<TR CLASS='even'><TD COLSPAN=2 align='CENTER'><B>No addresses.  Populate 'Contacts' table via Configuration link.</TD></TR>";
			}
		
?>
	
	<TR CLASS='odd'><TD COLSPAN=2 ALIGN="center"><BR />
		<INPUT TYPE="reset" VALUE="Reset" >
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="submit" VALUE="Send">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" VALUE="Cancel"  onClick="self.close();" >
		</FORM>
		</TD></TR>
	</TABLE>
	</BODY></HTML>
<?php
	}
?>
