<?php
//	mail server-side  script
require_once('functions.inc.php');
$istest=FALSE;
if (!empty($_POST)) {
	$err = "";
	if(!isset($addresses)) 	{$err.= "-Addresses ERROR ";}
	if(!isset($subject)) 	{$err.=  "-Subject ERROR ";	}
	if(!isset($msgtext)) 	{$err.=  "-Msg text ERROR ";}
	if(!$err == "") 		{die($err);}
	
	$headers = 'From: crismail@KolAmiAnnapolis.org' . "\r\n" .
	$headers .= 'Cc: crismail@KolAmiAnnapolis.org' . "\r\n";
	'Reply-To: 3ashore@Comcast.net' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	
	mail($addresses, "TicketsGram: " . $subject, $msgtext, $headers);
	}
else {
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
	</HEAD>
	<BODY>
	<FORM METHOD="post" ACTION="<?php print basename( __FILE__); ?>" NAME="MAIL" onSubmit="return validate(document.mail)">
	<TABLE>
	<TR><TD COLSPAN=99 ALIGN='center'><B>Enter e-mail Addresses<BR /><BR /></TD></TR>
	<TR CLASS='even'><TD>From:</TD>	<TD><INPUT TYPE='text' NAME='from' SIZE=36 VALUE=""></TD></TR>
	<TR CLASS='odd'><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F1').style.display = ""></TD></TR>
<TR CLASS='even' ID = 'F1'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F2').style.display = ""></TD></TR>
	<TR CLASS='odd' ID = 'F2'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F3').style.display = ""></TD></TR>
	<TR CLASS='even' ID = 'F3'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F4').style.display = ""></TD></TR>
	<TR CLASS='odd' ID = 'F4'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F5').style.display = ""></TD></TR>
	<TR CLASS='even' ID = 'F5'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F6').style.display = ""></TD></TR>
	<TR CLASS='odd' ID = 'F6'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F7').style.display = ""></TD></TR>
	<TR CLASS='even' ID = 'F7'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F8').style.display = ""></TD></TR>
	<TR CLASS='odd' ID = 'F8'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F9').style.display = ""></TD></TR>
	<TR CLASS='even' ID = 'F9'STYLE="display: none"><TD>To:</TD>	<TD><INPUT TYPE='text' NAME='to' SIZE=36 VALUE=""><INPUT TYPE='button' VALUE= '+' onClick = document.getElementById('F0').style.display = ""></TD></TR>
	<TR CLASS='odd'><TD COLSPAN="2" ALIGN="center"><BR /><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset" onclick= "reset_end();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Send"></TD></TR>
	</FORM>
	</TABLE>
	<SCRIPT>
	var str = "<INPUT TYPE='text' NAME='to' SIZE=36 VALUE=''></ BR>";
	</SCRIPT>
	<INPUT TYPE='button' onClick = "document.write str;" value="click">
	</BODY></HTML>
<?php
	}
?>