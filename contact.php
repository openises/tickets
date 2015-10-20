<?php 
error_reporting(E_ALL);		// 10/1/08

/*
12/24/14 safe contact form
*/
@session_start();
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10

$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]access_requests` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `phone` varchar(24) NOT NULL,
  `reason` longtext NOT NULL,
  `sec_code` varchar(24) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";			
$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

function genRandomString() {
	$length = 8;
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$strLength = strlen($characters)-1;
	$string = '';
	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, $strLength)];
		}
    $_SESSION["req_security_code"] = $string;
    return $string;
	}
$randString = genRandomString();
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Contact and request access form</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="8/24/08" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<STYLE type="text/css">	
	INPUT { FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
	SELECT { FONT-WEIGHT: normal; FONT-SIZE: 100%; COLOR: #000000; FONT-STYLE: normal; height: 20px; cursor: pointer; }
	OPTION { FONT-WEIGHT: normal; FONT-SIZE: 100%; COLOR: #000000; FONT-STYLE: normal; height: 20px; cursor: pointer; }
	FIELDSET { margin: 20px; padding: 10px; border: 3px inset #FFFFFF; border-radius: 20px 20px;}
	LABEL { width: 40%; display: inline-block; vertical-align: top; font-weight: bold; padding: 5px; text-align: left; }
	LEGEND { font-weight: bold; font-size: 14px; padding: 5px; background: #0000FF; border: 3px inset #FFFFFF; color: #FFFFFF; }
	TEXTAREA { clear: both;	font-size: 12px; }
	.legend { font-weight: bold; font-size: 14px; padding: 5px; background: #0000FF; border: 3px inset #FFFFFF; color: #FFFFFF; }
	</STYLE>
	<script src="./js/misc_function.js" type="text/javascript"></script>	
<SCRIPT>

function $() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
	
function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) top.location.href = document.location.href;
	}		// end function out_frames()

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};

function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
													// NOT correspond with what browsers actually do...
	var SAFECHARS = "0123456789" +					// Numeric
					"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// guess
					"abcdefghijklmnopqrstuvwxyz" +	// guess again
					"-_.!~*'()";					// RFC2396 Mark characters
	var HEX = "0123456789ABCDEF";

	var encoded = "";
	for (var i = 0; i < plaintext.length; i++ ) {
		var ch = plaintext.charAt(i);
		if (ch == " ") {
			encoded += "+";				// x-www-urlencoded, rather than %20
		} else if (SAFECHARS.indexOf(ch) != -1) {
			encoded += ch;
		} else {
			var charCode = ch.charCodeAt(0);
			if (charCode > 255) {
				alert( "Unicode Character '"
						+ ch
						+ "' cannot be encoded using standard URL encoding.\n" +
						  "(URL encoding only supports 8-bit characters.)\n" +
						  "A space (+) will be substituted." );
				encoded += "+";
			} else {
				encoded += "%";
				encoded += HEX.charAt((charCode >> 4) & 0xF);
				encoded += HEX.charAt(charCode & 0xF);
				}
			}
		} 			// end for(...)
	return encoded;
	};			// end function

function URLDecode(encoded ){   					// Replace + with ' '
   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
   var i = 0;
   while (i < encoded.length) {
	   var ch = encoded.charAt(i);
	   if (ch == "+") {
		   plaintext += " ";
		   i++;
	   } else if (ch == "%") {
			if (i < (encoded.length-2)
					&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
					&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
				plaintext += unescape( encoded.substr(i,3) );
				i += 3;
			} else {
				alert( '-- invalid escape combination near ...' + encoded.substr(i) );
				plaintext += "%[ERROR]";
				i++;
			}
		} else {
			plaintext += ch;
			i++;
			}
	} 				// end  while (...)
	return plaintext;
	};				// end function URLDecode()

function sendRequest(url,callback,postData) {
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
	if (postData)
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	req.onreadystatechange = function () {
		if (req.readyState != 4) return;
		if (req.status != 200 && req.status != 304) {
			return;
			}
		callback(req);
		}
	if (req.readyState == 4) return;
	req.send(postData);
	}

var XMLHttpFactories = [
	function () {return new XMLHttpRequest()	},
	function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
	function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
	function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
	];

function createXMLHTTPObject() {
	var xmlhttp = false;
	for (var i=0;i<XMLHttpFactories.length;i++) {
		try { xmlhttp = XMLHttpFactories[i](); }
		catch (e) { continue; }
		break;
		}
	return xmlhttp;
	}

function JSfnCheckInput(myform, mybutton, test) {		// reject empty form elements
	var security_image = "<?php print $_SESSION['req_security_code'];?>";
	var errmsg = "";
	if (myform.frm_sec.value.trim() != security_image) 							{errmsg+= "\tThe Security Code doesn't match, please try again\n";}
	if (myform.frm_requester.value.trim()=="") 									{errmsg+= "\tYour Name is required\n";}
	if (myform.frm_email.value.trim()!=myform.frm_email2.value.trim()) 			{errmsg+= "\tEmail Address Confirmation doesn't match\n";}	
	if (myform.frm_email.value.trim()=="") 										{errmsg+= "\tYour Email is required\n";}
	if (myform.frm_phone.value =="") 											{errmsg+= "\tYour Contact Phone Number is required\n";}
	if (myform.frm_reason.value =="" || myform.frm_reason.value.length < 10) 	{errmsg+= "\tA reason for the access request is required or the reason given is too short\n";}
	if (errmsg!="") {
		$(mybutton).disabled = false;
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else { // test? 
		myform.submit(); 
		}			// end if/else errormsg 
	}		// end function JSfnCheckInput		
</SCRIPT>
</HEAD>
<BODY onLoad="out_frames()">
<?php
if(!empty($_POST)) {
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) { 
		define("PROTOCOL", "https://");
		} else { 
		define("PROTOCOL", "http://"); 
		}
	define("WEBROOT_URL", PROTOCOL.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
	$name = base64_encode($_POST['frm_requester']);
	$email = base64_encode($_POST['frm_email']);
	$seccode = base64_encode($_POST['frm_sec']);
	$url = WEBROOT_URL . "/contact_response.php?nx=" . $name . "&ex=" . $email . "&sx=" . $seccode;
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$textStr = $_POST['frm_requester'] . "\r\n\r\n";
	$textStr .= "You have requested access to the Tickets CAD system.\r\n";
	$textStr .= "Please click the following link to verify your email address.\r\n\r\n";
	$textStr .= $url . "\r\n\r\n";
	$textStr .= "Thank you\r\n";
	$textStr .= "The Administrator\r\n";
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]access_requests` (name,email,phone,sec_code,reason,date) VALUES('{$_POST['frm_requester']}', '{$_POST['frm_email']}', '{$_POST['frm_phone']}', '{$_POST['frm_sec']}', '{$_POST['frm_reason']}', '{$now}')";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	// function do_send ($to_str, $smsg_to_str, $subject_str, $text_str, $ticket_id, $responder_ids=0, $messageid=NULL, $server=NULL) {
	do_send($_POST['frm_email'], "", "Tickets Access Request", $textStr, 0, 0, 0, NULL);
	$output = "Your request has been submitted. You will now receive an email to verify your email address<BR /><BR />";
	$output .= "Follow the instructions in the email<BR /><BR />";
	$output .= "Thanks<BR />The Administrator<BR /><BR />";
?>
		<DIV id='outer'>
			<DIV ID='titlebar'>
				<TABLE ALIGN='left'>
					<TR VALIGN='top'>
						<TD ROWSPAN=4><IMG SRC="<?php print get_variable('logo');?>" BORDER=0 /></TD>
						<TD>
<?php

							$temp = get_variable('_version');
							$version_ary = explode ( "-", $temp, 2);
							if(get_variable('title_string')=="") {
								$title_string = "<FONT SIZE='3'>ickets " . trim($version_ary[0]) . " on <B>" . get_variable('host') . "</B></FONT>";
								} else {
								$title_string = "<FONT SIZE='3'><B>ickets - " .get_variable('title_string') . "</B></FONT>";
								}
							print $title_string;
?>
						</TD>
					</TR>
				</TABLE>
			</DIV><BR />
			<DIV id='title' class='header' style='position: absolute; top: 80px; left: 0px; width: 100%; text-align: center;'>Access Request</DIV><BR /><BR /><BR />
			<DIV id='contact_form' STYLE='position: relative; top: 20px;'><BR /><BR /><BR />
				<DIV id='theForm'  style='position: relative; left: 25%; top: 20px; width: 50%; text-align: center; font-size: 12px; margin: 10px; border: 3px outset #646464;'><BR /><BR />
				<?php print $output;?>
				</DIV>
			</DIV>
		</DIV>
	</BODY>
<?php
	} else {
?>
	<DIV id='outer'>
		<DIV ID='titlebar'>
			<TABLE ALIGN='left'>
				<TR VALIGN='top'>
					<TD ROWSPAN=4><IMG SRC="<?php print get_variable('logo');?>" BORDER=0 /></TD>
					<TD>
<?php
						$temp = get_variable('_version');
						$version_ary = explode ( "-", $temp, 2);
						if(get_variable('title_string')=="") {
							$title_string = "<FONT SIZE='3'>ickets " . trim($version_ary[0]) . " on <B>" . get_variable('host') . "</B></FONT>";
							} else {
							$title_string = "<FONT SIZE='3'><B>ickets - " .get_variable('title_string') . "</B></FONT>";
							}
						print $title_string;
?>
					</TD>
				</TR>
			</TABLE>
		</DIV><BR />
		<hr style='width: 100%;'><BR />
		<CENTER><p class='header'>Access Request Form</p></CENTER>
		<DIV id='contact_form' STYLE='position: relative; top: 10px;'>
			<DIV id='theForm' style='position: relative; left: 25%; width: 50%; text-align: center; font-size: 12px; margin: 10px;'>
				<FORM METHOD='POST' ACTION="contact.php" NAME="acc_req_form"><BR />
					<FIELDSET>
					<LABEL for="frm_requester">Your Name</LABEL>
					<INPUT TYPE="text" NAME="frm_requester" VALUE="" MAXLENGTH="64" SIZE="64"><BR />
					<LABEL for="frm_email">Your Email Address</LABEL>
					<INPUT TYPE="text" NAME="frm_email" VALUE="" MAXLENGTH="64" SIZE="64"><BR />
					<LABEL for="frm_email2">Repeat Your Email Address</LABEL>
					<INPUT TYPE="text" NAME="frm_email2" VALUE="" MAXLENGTH="64" SIZE="64"><BR />
					<LABEL for="frm_phone">Your Contact Phone</LABEL>
					<INPUT TYPE="text" NAME="frm_phone" VALUE="" MAXLENGTH="64" SIZE="64"><BR />
					<LABEL for="frm_reason">Your Reason for Access</LABEL>
					<TEXTAREA NAME="frm_reason" rows="4" cols="64" style='overflow: auto;'></TEXTAREA><BR /><BR />
					<LABEL for="frm_sec">Type security code in the text box --></LABEL>
					<INPUT TYPE="text" NAME="frm_sec" VALUE="" MAXLENGTH="12" SIZE="12">
					<IMG style='vertical-align: middle;' SRC='./ajax/create_image.php?string=<?php print $randString;?>'>
					<INPUT TYPE='hidden' NAME='the_submit' VALUE=1>
					</FIELDSET>
				</FORM> 
				<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='this.disabled=true; JSfnCheckInput(document.acc_req_form, this);'>Submit</SPAN>
				<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN><BR /><BR />
			</DIV>
		</DIV>
	</DIV>
<?php	}
?>
<FORM NAME='can_Form' ACTION="index.php">
</FORM>	
</BODY>
</HTML>