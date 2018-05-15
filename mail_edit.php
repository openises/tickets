<?php
/*
2/18/09 initial release
2/28/09 added email addr validation
7/19/10 title handling corrected
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/30/10 finished addr correction
3/15/11 changed stylesheet.php to stylesheet.php
10/23/12 Added code for messaging (SMS Gateway)
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
require_once('./incs/messaging.inc.php');
$tick_id = ((isset($_GET['ticket_id'])) && ($_GET['ticket_id'] != "")) ? $_GET['ticket_id'] : 0;
if (empty($_POST)) {
	$query = "SELECT `id`, `scope` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$_GET['ticket_id']} LIMIT 1";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = mysql_fetch_array($result);
	$title = substr(stripslashes($row['scope']), 0, 60);
	unset($result);
	}
$title = (isset($row)) ? substr(stripslashes($row['scope']), 0, 60): $_POST['frm_title'];		// 7/19/10 
$smsg_provider = return_provider_name(get_msg_variable('smsg_provider'));
$using_smsg = ((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) ? true : false;	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Email re:  <?php print $title; ?></TITLE>
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};

function reSizeScr(lines) {
	window.resizeTo(640,((lines * 18)+260));		// derived via trial/error (more of the latter, mostly)
	}

function addrcheck(str) {
	var at="@"
	var dot="."
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
	if (str.indexOf(at)==-1)													{return false;}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr) 	{return false;}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr)	{return false;}
	if (str.indexOf(at,(lat+1))!=-1)											{return false;}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot)		{return false;}
	if (str.indexOf(dot,(lat+2))==-1)											{return false;}
	if (str.indexOf(" ")!=-1)													{return false;}
	return true					
	}
var temp;
var lines;

function do_val(theForm) {										// 2/28/09, 10/23/12
	if((theForm.frm_use_smsg) && (theForm.frm_use_smsg == 0)) {
		if(theForm.frm_addrs.value == "") {
			var sep = "";
			theForm.frm_addrs.value = theForm.frm_addrs.value + sep + theForm.frm_theothers.value;
			} else {
			var sep = ",";
			theForm.frm_addrs.value = theForm.frm_addrs.value + sep + theForm.frm_theothers.value;
			}
			theForm.frm_smsgaddrs.value = "";
		}
		
	if ((theForm.frm_addrs.value.trim() == "") && (theForm.frm_smsgaddrs.value.trim() == "")) {
		alert("Addressee required");
		return false;
		}

	if (theForm.frm_addrs.value.trim() != "") {
		temp = theForm.frm_addrs.value.trim().split("|");		// explode to array
		var emerr = false;
		for (i=0; i<temp.length; i++) {								// check each addr
			if (!(addrcheck(temp[i].trim()))) {
				emerr = true;
				}
			}

		if (emerr) {
			alert("Valid addressee email required");
			return false;
			}
		}
		
	if (theForm.frm_text.value.trim() == "") {
		alert("Message text required");
		return false;
		}
	theForm.submit();
	}
	
	function set_message(id) {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		var theMessages = <?php echo json_encode($std_messages);?>;
		var message = theMessages[parseInt(id)]['message'];
		var tick_id = <?php print $tick_id;?>;
		var url = './ajax/get_replacetext.php?tick=' + tick_id + '&version=' + randomnumber + '&text=' + encodeURIComponent(message);
		sendRequest (url,replacetext_cb, "");			
			function replacetext_cb(req) {
				var the_text=JSON.decode(req.responseText);
				if (the_text[0] == "") {
					var replacement_text = message;
					} else {
					var replacement_text = the_text[0];					
					}
				document.mail_form.frm_text.value += replacement_text;					
				}			// end function replacetext_cb()	
		}		// end function set_message(message)
</SCRIPT>
</HEAD>
<?php
	if (empty($_POST)) {
//		dump($_GET);
		$to_str = "ashore4@verizon.net";
//		$smsgaddrs = ((isset($_GET['smsgaddrs'])) && ($_GET['smsgaddrs'] != "")) ? $_GET['smsgaddrs'] : $smsgaddrs;
		$smsgaddrs = "";
		$text = mail_it ($_GET['addrs'], $smsgaddrs, $_GET['text'], $_GET['ticket_id'], 1, TRUE) ;		// returns msg text **ONLY** //	4/24/12
//		dump($text);
		$temp = explode("\n", $text);
		$finished_str = ((get_variable('call_board')==1))? "location.href = 'board.php'": "window.close();";	// 8/30/10
?>
<BODY onLoad = "reSizeScr(<?php print count($temp);?>)";><CENTER>
<?php
$use_messaging = get_variable('use_messaging');
$the_other = ((isset($_GET['other'])) && ($_GET['other'] != "")) ? $_GET['other'] : "";
$heading = "Dispatch message";

?>
<DIV id='button_bar' class='but_container'>
	<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?></SPAN>
	<SPAN ID='send_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_val(document.mail_frm);'><SPAN STYLE='float: left;'>OK - mail this</SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>
	<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_frm.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
	<SPAN ID='nosend_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="if(confirm('Confirm_do_not_send?')) {<?php print $finished_str;?>};"><SPAN STYLE='float: left;'>Do Not Send</SPAN><IMG STYLE='float: right;' SRC='./images/nosend_small.png' BORDER=0></SPAN>
</DIV>
<FORM NAME="mail_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
<TABLE ALIGN='center' BORDER="0" STYLE='position: relative; top: 70px;'>
	<TR CLASS='even'>
		<TH CLASS='heading' COLSPAN=2>Revise message to suit</TH>
	</TR>
	<TR CLASS='even'>
		<TD COLSPAN=2>
			<TEXTAREA CLASS='text' NAME="frm_text" COLS=80 ROWS=<?php print count($temp)*1.1; ?> wrap='soft'><?php print $text ;?></TEXTAREA>
		</TD>
	</TR>
	<TR VALIGN = 'TOP' CLASS='even'> <!-- 10/23/12 -->
		<TD CLASS="td_label text text_left">
			Standard Message: 
		</TD>
		<TD CLASS="td_data text text_left">

			<SELECT NAME='signals' onChange = 'set_message(this.options[this.selectedIndex].value);'>	<!--  11/17/10, 10/23/12 -->
			<OPTION VALUE=0 SELECTED>Select</OPTION> <!-- 10/23/12 -->
<?php
				print get_standard_messages_sel();
?>
			</SELECT>
		</TD>
	</TR>	
	<TR CLASS='even'>
		<TD CLASS="td_label text text_left">
			Email Addresses: 
		</TD>
		<TD CLASS="td_data text text_left">
			<INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE='<?php print $_GET['addrs'];?>'>
		</TD>
	</TR>
<?php
	if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {	//	10/23/12
		$smsgaddrs = ((isset($_GET['smsgaddrs'])) && ($_GET['smsgaddrs'] != "")) ? $_GET['smsgaddrs'] : "";

?>
		<TR CLASS='even'>
			<TD CLASS="td_label text">
				<?php get_provider_name(get_msg_variable('smsg_provider'));?> Addresses: 
			</TD>
			<TD CLASS="td_data text text_left">
				<INPUT TYPE='text' NAME='frm_smsgaddrs' size='60' VALUE='<?php print $smsgaddrs;?>' />
			</TD>
		</TR>	
		<TR CLASS='even'>
			<TD CLASS="td_label text">
				Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: 
			</TD>
			<TD CLASS="td_data text text_left">
				<INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE="1" <?php if(($_GET['addrs'] == "" && $smsgaddrs != "") || ($smsgaddrs != "" && get_msg_variable('default_sms') == "1")) { print "checked"; } ?> />
			</TD>
		</TR>			
		<INPUT TYPE="hidden" NAME = 'frm_theothers' VALUE="<?php print $the_other;?>"/> <!-- 10/23/12 -->
<?php
	} else {
?>
		<INPUT TYPE="hidden" NAME = 'frm_smsgaddrs' VALUE=""/> <!-- 10/23/12 -->
		<INPUT TYPE='hidden' NAME = 'frm_use_smsg' VALUE = "0"> <!-- 10/23/12 -->
		<INPUT TYPE="hidden" NAME = 'frm_theothers' VALUE="<?php print $the_other;?>"/>
<?php
	}
?>
	<TR CLASS='odd'>
		<TD COLSPAN=2 ALIGN = 'center'>
			<INPUT TYPE="hidden" NAME = 'ticket_id' VALUE="<?php print $_GET['ticket_id'];?>"/>
			<INPUT TYPE="hidden" NAME = 'frm_title' VALUE="<?php print $row['scope'];?>"/>
		</TD>
	</TR>
</TABLE>
</FORM>
<?php
	} else {		// end if (empty($_POST))
	$the_responders = array();
	$the_emails = explode('|',$_POST['frm_addrs']);
	$the_sms = ((isset($_POST['frm_smsgaddrs'])) && ($_POST['frm_smsgaddrs'] != "")) ? explode(',', $_POST['frm_smsgaddrs']) : "";
	$email_addresses = ($_POST['frm_addrs'] != "") ? $_POST['frm_addrs'] : "";
	$smsg_addresses = ((isset($_POST['frm_use_smsg'])) && ($_POST['frm_use_smsg'] == 1) && ($_POST['frm_smsgaddrs'] != "")) ? $_POST['frm_smsgaddrs'] : "";
	foreach($the_emails as $val) {
		$the_responders[] = get_resp_id2($val);
		}
	if(($_POST['frm_use_smsg']) && ($_POST['frm_use_smsg'] == 1)) {
		foreach($the_sms as $val2) {
			$the_responders[] = get_resp_id($val2);	
			}
		}
	$the_resp_ids = array_unique($the_responders);
	$resps = substr(implode(',', $the_resp_ids), 0 -2);
	$count = do_send ($email_addresses, $smsg_addresses, "Tickets CAD",  $_POST['frm_text'], $_POST['ticket_id'], $resps );		// - ($to_str, $to_smsr, $subject_str, $text_str, %ticket_id, $responder_id ) 
?>
	<!-- <BODY onLoad = "setTimeout('window.close()',6000);"><CENTER> -->
	<BODY><CENTER> 
	<BR /><BR /><H3>Sent dispatch notifications</H3><BR /><BR />
	<P><?php print $count;?> Message(s) sent</P>
	<SCRIPT>
	setTimeout(function(){ window.close(); }, 10000);
	</SCRIPT>
<?php
	}				// end else
?>
<SCRIPT LANGUAGE="Javascript">

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
</BODY>
</HTML>