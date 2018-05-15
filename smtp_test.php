<?php
/*
7/5/10 accomodate security parameter, per KJ email
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);

require './lib/phpmailer/PHPMailerAutoload.php';
require './lib/phpmailer/class.phpmailer.php';
require './lib/phpmailer/class.smtp.php';

/* enter known good values here - as a convemience */

$temp = get_variable('smtp_acct');
//if (!(empty($temp))) {
	$temp_ar = explode("/",$temp); 
	$server = 		isset($temp_ar[0])? $temp_ar[0]:""; 
	$port =  		isset($temp_ar[1])? $temp_ar[1]:""; 
	$security =  	isset($temp_ar[2])? $temp_ar[2]:""; // 7/5/10
	$user_acct =  	isset($temp_ar[3])? $temp_ar[3]:""; 
	$pass =  		isset($temp_ar[4])? $temp_ar[4]:"";
	$email_acct = 	isset($temp_ar[5])? $temp_ar[5]:"";
//	}

$chkd_none = ($security == "") ? "CHECKED" : "";
$chkd_tls = ($security == "tls" || $security == "TLS") ? "CHECKED" : "";
$chkd_ssl = ($security == "ssl" || $security == "SSL") ? "CHECKED" : "";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>SMTP Mail Transfer Test

</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;
var colwidth;
var colheight;

function validate_email(field) {
	apos=field.indexOf("@");
	dotpos=field.lastIndexOf(".");
	return (!(apos<1||dotpos-apos<2));
	}				// end function validate_email()
String.prototype.trim = function () {				// 10/19/08
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};
 
	
function validate() {
	errormsg="";
	if (document.test.server.value.trim()=="")				{errormsg += "\tServer name is required\n";}
	if (isNaN(parseInt(document.test.port.value)))			{errormsg += "\tNumeric port no. is required\n";}
	if (!(validate_email(document.test.from_addr.value)))	{errormsg += "\t\'From address\' is invalid\n";}
	if (!(validate_email(document.test.to_addr.value)))		{errormsg += "\t\'To address\' is invalid\n";}
	if (errormsg=="") {document.test.submit(); }
	else {
		alert ("Please correct the following errors\n"+ errormsg);
		return false;
		}
	}
</SCRIPT>
</HEAD>


<?php

if (empty($_POST)) {
	$temp = trim(get_variable('smtp_acct'));
	$caption = (empty($temp))? "" : "(your current setting is '{$temp}')";
	$temp2 = explode("@", $email_acct);
	$name = $temp2[0];
?>
	<BODY>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>SMTP Mail Test
			<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
		</DIV>
		<DIV id='inner' STYLE="position: relative; top: 70px;">
			<FORM NAME = "test" METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
			<TABLE ALIGN = 'center' WIDTH='640px'>
				<TR CLASS="odd">
					<TD COLSPAN=2 CLASS='heading text text_center'>
						Enter values  from your email account
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>Server name: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='server' VALUE='<?php print $server;?>' SIZE= 24><I> ex: outgoing.verizon.net
					</TD>
				</TR>
				<TR CLASS="odd">
					<TD CLASS='td_label text text_left'>Port:  </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='port' VALUE='<?php print $port;?>' SIZE= 4><I> ex:587
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>Security: </TD>
					<TD CLASS='td_data text'>
						<SPAN STYLE = 'margin-left: 24px;'>None &raquo; <INPUT TYPE = 'radio' NAME = 'security' VALUE = '' <?php print $chkd_none;?>></SPAN>
						<SPAN STYLE = 'margin-left: 24px;'>SSL &raquo; <INPUT TYPE = 'radio' NAME = 'security' VALUE = 'ssl' <?php print $chkd_ssl;?>></SPAN>
						<SPAN STYLE = 'margin-left: 24px;'>TLS &raquo; <INPUT TYPE = 'radio' NAME = 'security' VALUE = 'tls' <?php print $chkd_tls;?>></SPAN>
						<SPAN STYLE = 'margin-left: 24px;'><I> ISP dependent (Gmail requires a secure transport)</SPAN>
					</TD>
				</TR>
				<TR CLASS="odd">
					<TD CLASS='td_label text text_left'>Debug: </TD>
					<TD CLASS='td_data text'>
						<SPAN STYLE = 'margin-left: 24px;'>Yes &raquo; <INPUT TYPE = 'radio' NAME = 'debug' VALUE = 'yes'></SPAN>
						<SPAN STYLE = 'margin-left: 24px;'>No &raquo; <INPUT TYPE = 'radio' NAME = 'debug' VALUE = 'no' CHECKED></SPAN>
						<SPAN STYLE = 'margin-left: 24px;'><I> Outputs SMTP debug messages</SPAN>
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>User account: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='user_acct' VALUE='<?php print $user_acct;?>' SIZE= 24><I> ex:ashore3
					</TD>
				</TR>
				<TR CLASS="odd">
					<TD CLASS='td_label text text_left'>Password:  </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='pass' VALUE='<?php print $pass;?>' SIZE= 12><I> ex:whatever
					</TD>
				</TR>
			</TABLE>
			<BR />
			<TABLE ALIGN = 'center' WIDTH='640px'>
				<TR CLASS="odd">
					<TD COLSPAN=2 CLASS='heading text text_center'>
						Enter your test message
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>From name: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='from_user' VALUE='<?php print $name;?>' SIZE= 24><I> anything</I>
					</TD>
				</TR>
				<TR CLASS="odd">
					<TD CLASS='td_label text text_left'>From address: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='from_addr' VALUE='<?php print $email_acct;?>' SIZE= 24><I> a <B>valid</B> email address (your ISP's restriction may apply)</I>
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>To address: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='to_addr' VALUE='' SIZE= 24><I> a <B>valid</B> email address</I>
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>CC: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='cc' VALUE='' SIZE= 24><I> a <B>valid</B> email address</I>
					</TD>
				</TR>
				<TR CLASS="even">
					<TD CLASS='td_label text text_left'>BCC: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='bcc' VALUE='' SIZE= 24><I> a <B>valid</B> email address</I>
					</TD>
				</TR>
				<TR CLASS="odd">
					<TD CLASS='td_label text text_left'>Subject:  </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE = 'text' NAME='subj' VALUE='' SIZE= 24>
					</TD>
				</TR>
				<TR CLASS="even" VALIGN='top'>
					<TD CLASS='td_label text text_left'>Message:  	</TD>
					<TD CLASS='td_data text'>
						<TEXTAREA NAME='msg' cols=60 rows=5></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS="odd">
					<TD COLSPAN=99 style='text-align: center;'>
						<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='this.form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN ID='send_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate();'><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>
					</TD>
				</TR>
			</TABLE>
			</FORM>
		</DIV>
	</BODY>
<?php
	} else {
?>
	<BODY>
		<DIV id='inner' STYLE="position: relative; top: 70px;">
			<DIV style='position: relative; left: 10%; max-height: 300px; width: 80%; overflow: auto;'>
<?php
				$server = trim($_POST['server']);
				$fp = gethostbyname($server);
				if ($fp) {
					$debug = ($_POST['debug'] == 'yes') ? 2 : 0;
					$security = $_POST['security'];
					$port = intval($_POST['port']);
					$useracct = $_POST['user_acct'];
					$pass = $_POST['pass'];
					$temp_ar = explode("@", trim($_POST['to_addr']));
					$toName = $temp_ar[0];
					$toAddress = $_POST['to_addr'];
					$temp_cc = explode("@", trim($_POST['cc']));
					$cc_name = $temp_cc[0];
					$cc = $_POST['cc'];
					$temp_bcc = explode("@", trim($_POST['bcc']));
					$bcc_name = $temp_bcc[0];
					$bcc = $_POST['bcc'];		
					$fromName = $_POST['from_user'];
					$fromAddress = $_POST['from_addr'];
					$subject = $_POST['subj'];
					$msg = $_POST['msg'];
					
					$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
					try {
						//Server settings
						$mail->SMTPDebug = $debug;									// Enable verbose debug output
						$mail->isSMTP();										// Set mailer to use SMTP
						$mail->Host = $server;									// Specify main and backup SMTP servers
						$mail->SMTPAuth = true;                               	// Enable SMTP authentication
						$mail->Username = $useracct;							// SMTP username
						$mail->Password = $pass;								// SMTP password
						$mail->SMTPSecure = $security;							// Enable TLS encryption, `ssl` also accepted
						$mail->Port = $port;                                    	// TCP port to connect to

						//Recipients
						$mail->setFrom($fromAddress, $fromName);
						$mail->addAddress($toAddress, $toName);     	// Add a recipient
						$mail->addReplyTo($fromAddress, $fromName);
						if($cc != "" && is_email($cc)) {
							$mail->addCC($cc);
							}
						if($bcc != "" && is_email($bcc)) {					
							$mail->addBCC($bcc);
							}

						//Attachments
	//				    $mail->addAttachment('/var/tmp/file.tar.gz');         	// Add attachments
	//				    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    	// Optional name

						//Content
						$mail->isHTML(true);                                  	// Set email format to HTML
						$mail->Subject = $subject;
						$mail->Body    = '<b>' . $msg . '</b>';
						$mail->AltBody = $msg;
						
						$mail->send();
						$caption = 'Message has been sent';
						} catch (Exception $e) {
						$caption = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
						}
					} else {
					$caption = 'Cannot connect to server!.';	
					}
?>
			</DIV>
			<CENTER>
			<BR />
			<H2> <?php echo $caption; ?></H2>
			<BR />
			<BR />
			<FORM NAME='again' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
			<CENTER>
				<SPAN ID='another_but' class='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.again.submit();'><SPAN STYLE='float: left;'><?php print get_text("Again");?></SPAN><IMG STYLE='float: right;' SRC='./images/plus_small.png' BORDER=0></SPAN>
				<SPAN ID='fin_but' class='plain text' style='float: none; width: 100px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finish");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
			</CENTER>
			</FORM>
			</CENTER>
		</DIV>
	</BODY>
<?php
	}				// end else {}
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
set_fontsizes(viewportwidth, "popup");
outerwidth = viewportwidth * .95;
outerheight = viewportheight * .45;
colwidth = outerwidth;
colheight = outerheight;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('inner')) {$('inner').style.width = colwidth + "px";}
if($('inner')) {$('inner').style.height = colheight + "px";}
</SCRIPT>
</HTML>