<?php
/*
mobile_list_messages.
*/
@session_start();
session_write_close();
require_once('../incs/functions.inc.php');
include('../incs/html2text.php');
$ret_arr = array();
function get_messagetype($id) {
	if ($id == 1) {
		$type_flag = "OE";
		} elseif ($id ==2) {
		$type_flag = "IE";
		} elseif ($id ==3) {
		$type_flag = "OS";
		} elseif (($id ==4) || ($id ==5) || ($id ==6)) {
		$type_flag = "IS";	
		} else {
		$type_flag = "?";
		}
	return $type_flag;
	}
	
function get_messagecolor($id) {
	if ($id == 1) {
		$color = "background-color: blue; color: white;";
		} elseif ($id ==2) {
		$color = "background-color: white; color: blue;";			
		} elseif ($id ==3) {
		$color = "background-color: orange; color: white;";			
		} elseif (($id ==4) || ($id ==5) || ($id ==6)) {
		$color = "background-color: white; color: orange;";				
		} else {
		$color = "";				
		}
	return $color;
	}

$message_id = (array_key_exists('message_id', $_GET)) ? $_GET['message_id'] : 0;

$where = ($message_id != 0) ? "WHERE `id` = " . $message_id : "";

$query = "SELECT *, `date` AS `date`, `_on` AS `_on`,
		`m`.`id` AS `message_id`,
		`m`.`fromname` AS `fromname`,		
		`m`.`message` AS `message`,
		`m`.`ticket_id` AS `ticket_id`,
		`m`.`message_id` AS `msg_id`,
		`m`.`msg_type` AS `msg_type`,	
		`m`.`recipients` AS `recipients`,	
		`m`.`readby` AS `readby`,		
		`m`.`subject` AS `subject`	
		FROM `$GLOBALS[mysql_prefix]messages` `m` 
		{$where}";

$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$msg_row = stripslashes_deep(mysql_fetch_assoc($result));
$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
$ret_arr[0] = $msg_row['id'];	
$ret_arr[1] = $msg_row['ticket_id'];
$ret_arr[2] = get_messagetype($msg_row['msg_type']);
$ret_arr[3] = $fromname;
$ret_arr[4] = stripslashes_deep($msg_row['subject']);
$ret_arr[5] = stripslashes_deep($msg_row['message']);
$ret_arr[6] = format_date_2(strtotime($msg_row['date']));
$ret_arr[7] = get_owner($msg_row['_by']);
$ret_arr[8] = get_messagecolor($msg_row['msg_type']);

print json_encode($ret_arr);
exit();
?>