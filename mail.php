<?php
/*
9/17/08 notify user if no contact addresses
10/6/08	extracted mail function to functions.inc.php as mail_it()
10/6/08	detect zero addressees
10/7/08	set WRAP="virtual"
10/7/08	ajax-ify for inter-message delay
10/17/08 changed addr string to pipe-delim'd
3/22/09 added mobile as email addr
7/19/10 line # print removed
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
if($istest) {
	print "GET";
	dump ($_GET);
	print "POST";
	dump ($_POST);
	}
extract ($_GET);
// outgoing.verizon.net/587//ashore4/pug2skim/ashore4@verizon.net
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Mail Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;
var listHeight;
var listwidth;
var colwidth;
var colheight;
</SCRIPT>
<?php

if (empty ($_POST)) {
		$ticket_id = $_GET['ticket_id'];
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
		$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
		$text = mail_it ("", "", "", $ticket_id, 2, TRUE) ;		// returns msg text **ONLY**
		$temp = explode("\n", $text);
		$nr_lines = intval(count($temp) + 2);
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
		
	function do_val(theForm) {
		var j = 0;
		var sep="";
		for (var i=0; i<theForm.elements.length;i++) {
			if ((theForm.elements[i].type == "checkbox") && (theForm.elements[i].checked)) {
				theForm.frm_to_str.value += (sep + theForm.elements[i].value);
				sep="|";
				j++;
				}
			}
		var errmsg="";
		if (j==0) 				{errmsg+= "\tAt least one address is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			} else {
			theForm.submit();
			}
		}				// end function val idate()

	</SCRIPT>
	</HEAD>
	<BODY>
	<DIV ID='outer' STYLE='position: absolute; top: 0px; left: 5%;'>
		<FORM METHOD="post" ACTION="<?php print basename( __FILE__); ?>" NAME="mail_Form" >
		<INPUT TYPE='hidden' NAME = 'frm_ticket_id' VALUE='<?php print $ticket_id; ?>'>
		<TABLE ID='mail_table' BORDER="0" STYLE='position: relative; top: 70px;'>
			<TR CLASS='even'>
				<TD COLSPAN=2 CLASS='heading text_large text_center'>Edit Message</TD>
			</TR>
			<TR CLASS='odd'>
				<TD CLASS='td_label text'>Ticket:</TD>
				<TD CLASS='td_data text'><?php print shorten($t_row['scope'], 48); ?></TD>
			</TR>
			<TR CLASS='even'>
				<TD CLASS='td_label text'>Message:</TD>
				<TD CLASS='td_data text'>
					<TEXTAREA ROWS = <?php print $nr_lines; ?> COLS=60 NAME='frm_text' WRAP="virtual"><?php print $text; ?></TEXTAREA>
				</TD>
			</TR>
<?php														//			generate dropdown menu of contacts
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` ORDER BY `name` ASC";
			$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (mysql_affected_rows()>0) {
				$got_addr = TRUE;
				$height = (mysql_affected_rows() + 1) * 16;
?>
				<TR CLASS='odd'>
					<TD CLASS='td_label text'>To:</TD>
					<TD CLASS='td_data text'>
						<SELECT NAME='frm_to[]' style='width: 100%; height: " . $height ."px;' multiple />
<?php
							while ($row = stripslashes_deep(mysql_fetch_array($result))) {
								if ((!((trim($row['email']))) == "") && (is_email(trim($row['email'])))) {
									print "\t<OPTION VALUE='" . $row['email'] . "'>" . $row['name'] . "/" .$row['organization'] . " <I>(email)</I></OPTION>\n";
									}
								if ((!((trim($row['mobile']))) == "") && (is_email(trim($row['mobile'])))) {
									print "\t<OPTION VALUE='" . $row['mobile'] . "'>" . $row['name'] . "/" .$row['organization'] . " <I>(mobile)</I></OPTION>\n";
									}
								if ((!((trim($row['other']))) == "") && (is_email(trim($row['other'])))) {
									print "\t<OPTION VALUE='" . $row['other'] . "'>" . $row['name'] . "/" .$row['organization'] . " <I>(other)</I></OPTION>\n";
									}
								}
?>
						</SELECT>
					</TD>
				</TR>
<?php
				} else {
?>
				<TR CLASS='even'>
					<TD COLSPAN=2 CLASS='td_data text text_center'><B>No addresses.<BR /> Populate 'Contacts' table via Configuration link.</TD>
				</TR>
<?php
				$got_addr = FALSE;
				}	
?>
			<TR CLASS='even'>
				<TD COLSPAN=2 CLASS='td_label text'>&nbsp;</TD>
			</TR>
		</TABLE>
		<INPUT TYPE = "hidden" NAME="frm_to_str" VALUE="">
		<INPUT TYPE = "hidden" NAME="frm_subj" VALUE= "<?php print shorten($t_row['scope'], 48); ?>">
		</FORM>
	</DIV>
	<DIV id='button_bar' class='but_container'>
		<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>Send Email</SPAN>
		<SPAN ID='can_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
<?php
		if ($got_addr) { 
?>
			<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_Form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
			<SPAN ID='send_but' CLASS='plain text' STYLE='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_val(document.mail_Form);'><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>
<?php
			} 
?>
	</DIV>
<?php
	} else {
	do_send ($_POST['frm_to_str'], "", $_POST['frm_subj'], $_POST['frm_text'], $_POST['frm_ticket_id'], 0) ;		// ($to, $subject, $text) ;
?>
	</HEAD>
	<BODY>
	<CENTER><BR /><BR /><h3>Sent!</h3><BR /><BR />
	<FORM NAME='can_Form' METHOD="get" ACTION = "<?php print basename( __FILE__); ?>" >
	<SPAN ID='fin_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
	</CENTER>
	</FORM>		

<?php
	}		// end else ...
	
?>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
outerwidth = viewportwidth * .95;
outerheight = viewportheight * .90;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('mail_table').style.width = colwidth + "px";
$('mail_table').style.height = colheight + "px";	
set_fontsizes(viewportwidth, "popup");
</SCRIPT>
</BODY>
</HTML>