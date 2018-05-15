<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$sess_id = $_SESSION['id'];
$ret_arr = array();
if(!array_key_exists('q', $_GET) || $_GET['q'] != $_SESSION['id']) {
	$ret_arr[0] = "Error calling form";
	print json_encode($ret_arr);
	exit();
	}

$query = "SELECT `id`, `scope` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $_GET['ticket_id'] . " LIMIT 1";	
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = mysql_fetch_array($result);
$title = substr(stripslashes($row['scope']), 0, 60);
$title = (isset($row)) ? substr(stripslashes($row['scope']), 0, 60): $_POST['frm_title'];
$smsg_provider = return_provider_name(get_msg_variable('smsg_provider'));
$use_messaging = get_variable('use_messaging');
$using_smsg = (($use_messaging) || ($use_messaging)) ? true : false;	
$text = mail_it($_GET['addrs'], $_GET['smsgaddrs'], "", $_GET['ticket_id'], 1, TRUE) ;
$temp = explode("\n", $text);
$the_other = ((isset($_GET['other'])) && ($_GET['other'] != "")) ? $_GET['other'] : "";

$output = "<FORM NAME=dispatchmail_frm' METHOD='post' ACTION='./ajax/form_post.php?q=". $sess_id . "&function=dispatchmail'>";
$output .= "<TABLE ALIGN='center' BORDER='0' STYLE='position: relative; top: 70px;'>";
$output .= "<TR CLASS='even'>";
$output .= "<TH CLASS='heading' COLSPAN=2>Revise message to suit</TH>";
$output .= "</TR>";
$output .= "<TR CLASS='even'>";
$output .= "<TD COLSPAN=2>";
$output .= "<TEXTAREA CLASS='text' NAME='frm_text' COLS=80 ROWS=" . count($temp)*1.1 . " wrap='soft'>" . $text . "</TEXTAREA>";
$output .= "</TD>";
$output .= "</TR>";
$output .= "<TR VALIGN = 'TOP' CLASS='even'>";
$output .= "<TD CLASS='td_label text text_left'>Standard Message:</TD>";
$output .= "<TD CLASS='td_data text text_left'>";
$output .= "<SELECT NAME='signals' onChange = 'set_message(this.options[this.selectedIndex].value);'>";
$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
$output .= get_standard_messages_sel();
$output .= "</SELECT>";
$output .= "</TD>";
$output .= "</TR>";	
$output .= "<TR CLASS='even'>";
$output .= "<TD CLASS='td_label text text_left'>Email Addresses: </TD>";
$output .= "<TD CLASS='td_data text text_left'>";
$output .= "<INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE='" . $_GET['addrs'] . "'>";
$output .= "</TD>";
$output .= "</TR>";
if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {	//	10/23/12
	$smsgaddrs = ((isset($_GET['smsgaddrs'])) && ($_GET['smsgaddrs'] != "")) ? $_GET['smsgaddrs'] : "";
	$output .= "<TR CLASS='even'>";
	$output .= "<TD CLASS='td_label text'>" . $smsg_provider . " Addresses: ";
	$output .= "</TD>";
	$output .= "<TD CLASS='td_data text text_left'>";
	$output .= "<INPUT TYPE='text' NAME='frm_smsgaddrs' size='60' VALUE='" . $smsgaddrs . "' />";
	$output .= "</TD>";
	$output .= "</TR>";	
	$output .= "<TR CLASS='even'>";
	$output .= "<TD CLASS='td_label text'>";
	$output .= "Use " . $smsg_provider . "?: ";
	$output .= "</TD>";
	$output .= "<TD CLASS='td_data text text_left'>";
	if(($_GET['addrs'] == "" && $smsgaddrs != "") || ($smsgaddrs != "" && get_msg_variable('default_sms') == "1")) { print "checked"; }
		$checked = "checked";
		$output .= "<INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE='1' " . $checked . " />";
		$output .= "</TD>";
		$output .= "</TR>";		
		$output .= "<INPUT TYPE='hidden' NAME = 'frm_theothers' VALUE='" . $the_other . "'/>";
	} else {
	$output .= "<INPUT TYPE='hidden' NAME = 'frm_smsgaddrs' VALUE=''/>";
	$output .= "<INPUT TYPE='hidden' NAME = 'frm_use_smsg' VALUE = '0'>";
	$output .= "<INPUT TYPE='hidden' NAME = 'frm_theothers' VALUE='" . $the_other . "'/>";
	}
$output .= "<TR CLASS='odd'>";
$output .= "<TD COLSPAN=99 ALIGN = 'center'><BR /><BR />";
$output .= "<SPAN ID='send_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_val(document.mail_frm);'><SPAN STYLE='float: left;'>OK - mail this</SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>";
$output .= "<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_frm.reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
//$output .= "<SPAN ID='nosend_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"if(confirm('Confirm_do_not_send?')) {" .$finished_str . "};\"><SPAN STYLE='float: left;'>Do Not Send</SPAN><IMG STYLE='float: right;' SRC='./images/nosend_small.png' BORDER=0></SPAN>";
$output .= "</TD>";
$output .= "</TR>";
$output .= "</TABLE>";
$output .= "<INPUT TYPE='hidden' NAME = 'ticket_id' VALUE='" . $_GET['ticket_id'] . "'/>";
$output .= "<INPUT TYPE='hidden' NAME = 'frm_title' VALUE='" . $row['scope'] . "'/>";
$output .= "</FORM>";
$ret_arr[] = $output;
print json_encode($ret_arr);