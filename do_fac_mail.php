<?php
/*
8/17/09	initial release - mail to facility
7/16/10 added check for no addressees
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		//

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');
$evenodd = array ("even", "odd");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print LessExtension(basename(__FILE__));?> </TITLE>
<META NAME="Description" CONTENT="Email to units">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
BODY {FONT-SIZE: 1vw;}
INPUT {FONT-SIZE: 1vw;}
SELECT {FONT-SIZE: 1vw;}
OPTION {FONT-SIZE: 1vw;}
TABLE {FONT-SIZE: 1vw;}
TEXTAREA {FONT-SIZE: 1vw;}
.td_label {FONT-SIZE: 1vw;}
.plain {FONT-SIZE: 1vw;}
.hover {FONT-SIZE: 1vw;}
</STYLE>
<?php
//dump($_POST);

if (empty($_POST)) {

?>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
 
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}
	
	function reSizeScr(lines){
		var the_width = 1200;
		var the_height = ((lines * 21)+400);				// values derived via trial/error (more of the latter, mostly)
		window.resizeTo(the_width,the_height);	
		}

	function validate() {
		var addr_err = true;
			for (i=0; i< document.mail_form.length; i++) {
			 	if ((document.mail_form.elements[i].name.substring(0, 2) == 'cb') && (document.mail_form.elements[i].checked)) {
			 		addr_err = false;
		 			}
		 		}
	
		var errmsg="";
		if (addr_err) 									  {errmsg+="One or more addresses required\n";}
		if (document.mail_form.frm_subj.value.trim()=="") {errmsg+="Message subject is required\n";}
		if (document.mail_form.frm_text.value.trim()=="") {errmsg+="Message text is required\n";}
		if (!(errmsg=="")){
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			document.mail_form.submit();	
			}
		}				// end function validate()

	</SCRIPT>
	</HEAD>
	
<?php

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ";		// (array_key_exists('first', $search_array)) 
	$query .= (array_key_exists('fac_id', $_GET))? " WHERE `id` = " . quote_smart(trim($_GET['fac_id'])) . " LIMIT 1": " WHERE `contact_email` != '' OR `security_email` != ''";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//	dump($query);
?>
	<BODY onLoad = "reSizeScr(<?php print mysql_affected_rows();?>)"><CENTER>		<!-- 1/12/09 -->

	<CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail Facilities </H3>
<?php
	$i = 0;
	if (mysql_affected_rows()>0) {
		print "<FORM NAME='mail_form' METHOD='post' ACTION='" . basename(__FILE__) . "'>\n";
		print "<TABLE BORDER = 0 ALIGN='center'>\n";
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			if (is_email($row['contact_email'])) {
				print "<TR CLASS = '{$evenodd[($i%2)]}'><TD><INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$row['contact_email']}' CHECKED></TD>
					<TD>{$row['name']}</TD><TD>{$row['contact_name']}</TD><TD>{$row['contact_email']}</TD><TD></TD></TR>\n";
				$i++;
				}
			if (is_email($row['security_email'])) {
				print "<TR CLASS = '{$evenodd[($i%2)]}'><TD><INPUT TYPE='checkbox' NAME='cb" .$i. "' VALUE='" . $row['security_email'] . "' CHECKED></TD>
					<TD>{$row['name']}</TD><TD>{$row['security_contact']}</TD><TD>{$row['security_email']}</TD><TD></TD></TR>\n";
				$i++;
				}	// end if (is_email)
			}		// end while()
		}				// end if (mysql_affected_rows()>0) 

	if ($i > 0 ) {							// 7/16/10
				
?>
		<TR>
			<TD COLSPAN=5>&nbsp;</TD>
		</TR>	
		<TR CLASS='even'>
			<TD CLASS="td_label" ALIGN='right'>Subject: </TD>
			<TD COLSPAN=4>
				<INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60>
			</TD>
		</TR>
		<TR CLASS='odd'>
			<TD CLASS="td_label" ALIGN='right'>Message:</TD>
			<TD COLSPAN=4>
				<TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA>
			</TD>
		</TR>
		<TR CLASS='even'>
			<TD></TD>
			<TD ALIGN='center' COLSPAN=4>
				<SPAN id='send_but' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate();"><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.mail_form.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
			</TD>
		</TR>
		</TABLE>
		</FORM>
<?php
		}		// end if ($i > 0 )
	else {
?>
		<BR /><H3>No facility addresses available</H3><BR /><BR />
			<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
<?php
	
		}		// end if/else end if ($i > 0 )
		
		}		// end if (empty($_POST)) {

	else {
		$addr_str = $sep = "";
		foreach ($_POST as $VarName=>$VarValue) {
			if (substr($VarName, 0, 2) == 'cb') {
				$addr_str .= $sep . $VarValue;
				$sep = "|";
				}				// end if
			}		// end foreach
		do_send ($addr_str, "", $_POST['frm_subj'], $_POST['frm_text'], 0, 0);	// ($to_str, $subject_str, $text_str ) - | separator
?>
	<BODY>
	<CENTER><BR /><BR /><BR /><H3>Mail sent</H3>
	<BR /><BR />
	<SPAN id='closebut' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
<?php

	}		// end else
?>
</BODY>
</HTML>
