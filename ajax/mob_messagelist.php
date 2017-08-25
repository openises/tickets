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
		$type_flag = "Outgoing Email";
		} elseif ($id ==2) {
		$type_flag = "Incoming Email";
		} elseif ($id ==3) {
		$type_flag = "Outgoing SMS";
		} elseif (($id ==4) || ($id ==5) || ($id ==6)) {
		$type_flag = "Incoming SMS";	
		} else {
		$type_flag = "UNK";
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
	
function get_ticketinfo($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $id;			
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$theRow = stripslashes_deep(mysql_fetch_assoc($result));
	$scope = $theRow['scope'];
	return $scope;
	}
	
function get_unitinfo($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;			
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$theRow = stripslashes_deep(mysql_fetch_assoc($result));
	$the_unit_name = (empty($theRow['name']))? "NA": $theRow['name'];
	return $the_unit_name;
	}

$ticket_id = (array_key_exists('ticket_id', $_GET)) ? $_GET['ticket_id'] : 0;
$userid = (array_key_exists('user_id', $_SESSION)) ? $_SESSION['user_id'] : 0;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$userid} LIMIT 1";			
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$user_row = stripslashes_deep(mysql_fetch_assoc($result));
$unit_id =  intval($user_row['responder_id']);
$the_unit_name = ($user_row['responder_id'] == 0)? "NA": get_unitinfo($unit_id);

$where = ($userid != 0 && $ticket_id != 0) ? "WHERE `ticket_id` = " . $ticket_id . " AND (FIND_IN_SET(" . $unit_id . ", `resp_id`) > 0)" : "";

$order = "ORDER BY `date` DESC";
$the_user = $_SESSION['user_id'];	

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
		{$where} {$order}";

$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$i = 0;
if (mysql_num_rows($result) == 0) {
	$ret_arr[0][12]= 0;
	$ret_arr[0][9] = stripslashes_deep(get_ticketinfo($ticket_id));
	$temp = explode("/", $the_unit_name);
	$unitName = $temp[0];
	$ret_arr[0][10] = stripslashes_deep($unitName);
	} else {
	$ret_arr[0][9] = stripslashes_deep(get_ticketinfo($ticket_id));
	$temp = explode("/", $the_unit_name);
	$unitName = $temp[0];
	$ret_arr[0][10] = stripslashes_deep($unitName);
	while ($msg_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
		$ret_arr[$i][0] = $msg_row['id'];	
		$ret_arr[$i][1] = $msg_row['ticket_id'];
		$ret_arr[$i][2] = get_messagetype($msg_row['msg_type']);
		$ret_arr[$i][3] = $fromname;
		$ret_arr[$i][4] = stripslashes_deep(shorten($msg_row['subject'], 18));
		$ret_arr[$i][5] = stripslashes_deep(shorten($msg_row['message'], 70));
		$ret_arr[$i][6] = format_date_2(strtotime($msg_row['date']));
		$ret_arr[$i][7] = get_owner($msg_row['_by']);
		$ret_arr[$i][8] = get_messagecolor($msg_row['msg_type']);
		$i++;
		} // end while
	$ret_arr[0][12] = $i;
	}
print json_encode($ret_arr);
exit();
?>