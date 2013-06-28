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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
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
	<TD colspan = 2 align= "center"><br/>
	<input type="button" value="Submit" onclick = "validateForm (document.mail_form)">
	<input type="button" value="Reset" onclick = "document.mail_form.reset(); document.mail_form.frm_from.focus();"  STYLE = 'margin-left: 24px;'>
	<input type="button" value="Cancel" STYLE = 'margin-left: 24px;'>
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
	<input type="button" value="Another?" onclick = "document.mail_form.submit();">
	<input type="button" value="Finished ?" onclick = "this.window.close();" style = "margin-left:20px;">
	</p>
<?php
	}		// end if/else if (empty($_POST)) 
?>
</BODY>
</HTML>
