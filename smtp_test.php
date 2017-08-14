<?php
/*
7/5/10 accomodate security parameter, per KJ email
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
//dump($_POST);

/* enter known good values here - as a convemience */

$temp = get_variable('smtp_acct');
//if (!(empty($temp))) {
	$temp_ar = explode("/",$temp); 
	$server = 		isset($temp_ar[0])? $temp_ar[0]:""; 
	$port =  		isset($temp_ar[1])? $temp_ar[1]:""; 
	$security =  	isset($temp_ar[2])? $temp_ar[2]:""; // 7/5/10
	$user_acct =  	isset($temp_ar[3])? $temp_ar[3]:""; 
	$pass =  		isset($temp_ar[4])? $temp_ar[4]:"";
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
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
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
<BODY>

<?php
if (empty($_POST)) {
	$temp = trim(get_variable('smtp_acct'));
	$caption = (empty($temp))? "" : "(your current setting is '{$temp}')";
?>
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
			<TD CLASS='td_label text text_left'>User account: </TD>
			<TD CLASS='td_data text'>
				<INPUT TYPE = 'text' NAME='user_acct' VALUE='<?php print $user_acct;?>' SIZE= 24><I> ex:ashore3
			</TD>
		</TR>
		<TR CLASS="even">
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
				<INPUT TYPE = 'text' NAME='from_user' VALUE='' SIZE= 24><I> anything</I>
			</TD>
		</TR>
		<TR CLASS="odd">
			<TD CLASS='td_label text text_left'>From address: </TD>
			<TD CLASS='td_data text'>
				<INPUT TYPE = 'text' NAME='from_addr' VALUE='' SIZE= 24><I> a <B>valid</B> email address (your ISP's restriction may apply)</I>
			</TD>
		</TR>
		<TR CLASS="even">
			<TD CLASS='td_label text text_left'>To address: </TD>
			<TD CLASS='td_data text'>
				<INPUT TYPE = 'text' NAME='to_addr' VALUE='' SIZE= 24><I> a <B>valid</B> email address</I>
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
				<TEXTAREA NAME='msg' cols=60 rows=10></TEXTAREA>
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
<?php
	}				// end if (empty($_POST))

else {
	$errors = FALSE;
	@set_time_limit(10);		// certain errors take longer
	
	function myErrorHandler($errno, $errstr, $errfile, $errline) {
		global $errors, $istest;
		if($errno == 2048) { return;}
		if ($istest) {		
			dump(__LINE__);
			dump($errno);
			dump($errstr);
			}
		$errors = TRUE;
		return FALSE;
		}
		
	require_once 'lib/swift_required.php';
	error_reporting(E_ALL);
	$old_error_handler = set_error_handler("myErrorHandler");
	$server = trim($_POST['server']);
	$fp = gethostbyname($server);
	if ($fp) {
		
		//Create the Transport the call setUsername() and setPassword()
 		$transport = Swift_SmtpTransport::newInstance(trim($_POST['server']) , trim($_POST['port']) , trim($_POST['security']))
		  ->setUsername(trim($_POST['user_acct']))
		  ->setPassword(trim($_POST['pass']))
		  ;
		
		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);
		
		
		//Create a message
		$temp_ar = explode("@", trim($_POST['to_addr']));
		
		$message = Swift_Message::newInstance(trim($_POST['subj']))
		  ->setFrom(array($_POST['from_addr'] => trim($_POST['from_user'])))
		  ->setTo(array(trim($_POST['to_addr']) => trim($temp_ar[0])))
		  ->setBody(trim($_POST['msg']))
		  ;
		
		//    ->setTo(array('receiver@domain.org', 'other@domain.org' => 'Names'))
		//Send the message
	//	$result = $mailer->send($message, $failures);
		$failures="";
		$caption = "Sent";
		if (!($mailer->send($message, $failures))) {
			$errs = "";
			dump ($failures);
			foreach ($failures as $value){
			    $errs .= $value . " ";
			    }
			$caption = "Mail to '$errs' failed";
			}
		else {
			if ($errors) {
				$caption = "Mail delivery failed";
				}
			}
		}
	else {
		if ($istest) {
			dump($server);
			dump($fp);
			}
		$caption = "Failed - server name error";
		}

?>
<CENTER><BR />
<BR />
<H2> <?php echo $caption; ?></H2>
<BR />
<BR />
<FORM NAME='mail' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
<SPAN ID='another_but' class='plain text' style='float: none; width: 80px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='this.form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Again");?></SPAN><IMG STYLE='float: right;' SRC='./images/plus_small.png' BORDER=0></SPAN>
<SPAN ID='close_but' class='plain text' style='float: none; width: 80px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
</FORM>
</CENTER>
<?php
	}				// end else {}
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