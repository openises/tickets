<?php
/*
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets mail test</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" type="application/x-javascript"></SCRIPT>
<?php
if (empty($_POST)) {
?>

<SCRIPT>
function validateEmail(theEmail) {		// allows almost anything
    var re = /\S+@\S+\.\S+/;
    return re.test(theEmail);
	}

function validateForm (theForm) {
	var errstr = "";
	if ( ! ( validateEmail (theForm.frm_from.value ) ) )		{errstr += "From-addr error\n"}
	if ( ! ( validateEmail (theForm.frm_to.value ) ) )			{errstr += "To-addr error\n"}
	if ( ! ( validateEmail (theForm.frm_reply_to.value ) ) )	{errstr += "Reply-to-addr error\n"}
	if ( theForm.frm_subject.value.trim().length == 0 ) 		{errstr += "Message subject error\n"}
	if ( theForm.frm_message.value.trim().length == 0 ) 		{errstr += "Message text error\n"}

	if (errstr.length > 0) {alert ("Errors needing correction:\n\n" + errstr); return false;}

	else {mail_form.submit()}
	}		// end function validateForm ()
</SCRIPT>
</HEAD>
<BODY onload = "document.mail_form.frm_from.focus();">
<FORM NAME = "mail_form" METHOD = "post" ACTION = "<?php echo basename(__FILE__);?>">
<TABLE ALIGN="center" BORDER=0 CELLSPACING=4 CELLPADDING=4 STYLE = "margin-top:40px;">
<TR ALIGN="left" VALIGN="middle" CLASS = 'even'>
	<TD COLSPAN = 2 ALIGN = 'center'><br/><h3>Test Server 'Native mail'</h3></TD>
</TR>
<TR VALIGN="middle" CLASS = 'odd'>
	<TD ALIGN="right" CLASS="td_label" >E-mail from:</TD>
	<TD><INPUT TYPE = "text" NAME = "frm_from" SIZE = 48 MAXLENGTH = 48 VALUE = "" placeholder="test address here"></TD>
</TR>
<TR VALIGN="middle" CLASS = 'even'>
	<TD ALIGN="right" CLASS="td_label" >To:</TD>
	<TD><INPUT TYPE = "text" NAME = "frm_to" SIZE = 48 MAXLENGTH = 48 VALUE = "" placeholder="test address here"></TD>
</TR>
<TR VALIGN="middle" CLASS = 'odd'>
	<TD ALIGN="right" CLASS="td_label" >Reply-to:</TD>
	<TD><INPUT TYPE = "text" NAME = "frm_reply_to" SIZE = 48 MAXLENGTH = 48 VALUE = "" placeholder="test address here"></TD>
</TR>
<TR VALIGN="middle" CLASS = 'even'>
	<TD ALIGN="right" CLASS="td_label" > Subject:</TD>
	<TD><INPUT TYPE = "text" NAME = "frm_subject" SIZE = 48 MAXLENGTH = 48 VALUE = "Test Subject"></TD>
</TR>
<TR VALIGN="middle" CLASS = 'odd'>
	<TD ALIGN="right" CLASS="td_label" > Message: </TD>
	<TD><INPUT TYPE = "text" NAME = "frm_message" SIZE = 48 MAXLENGTH = 48 VALUE = "Test message text" ></TD>
</TR>
</FORM>

<TR VALIGN="middle" CLASS = 'even'>
	<TD colspan = 2 align= "center">
	<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validateForm (document.mail_form);'><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
	<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_form.reset(); document.mail_form.frm_from.focus();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
	<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
	</TD>
</TR>
</TABLE>

<?php
	}			// end if (empty($_POST)) {}
else {
?>
</HEAD>
<BODY>
<FORM NAME = "mail_form" METHOD = "post" ACTION = "<?php echo basename(__FILE__);?>"></FORM>

<?php
	$to      = "{$_POST['frm_to']}";
	$subject = "{$_POST['frm_subject']}";
	$message = "{$_POST['frm_message']}";
	$headers = "From: {$_POST['frm_from']}" . "\r\n" .
	    "Reply-To: {$_POST['frm_reply_to']}" . "\r\n" .
	    "X-Mailer: PHP/" . phpversion();

	if (@mail($to, $subject, $message, $headers)) {
		echo "<br/><br/><center><h3>Server reports success!</h3><br/><br/>";
		echo "<center><h4>(delivery can take minutes depending on ... )</h4><br/><br/>";
		}
	else {
		echo "<br/><br/><center><h3>Server reports failure!</h3><br/><br/>";
		}
?>
	<p align='center'>
	<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Another");?> ?</SPAN><IMG STYLE='float: right;' SRC='./images/plus_small.png' BORDER=0></SPAN>
	<SPAN ID='close_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
	</p>
<?php
	}		// end if/else if (empty($_POST)) 
?>
</BODY>
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
	
set_fontsizes(viewportwidth, "popup");
</SCRIPT>
</HTML>
