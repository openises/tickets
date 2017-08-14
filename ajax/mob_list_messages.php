<?php
/*
mobile_list_messages.
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
@session_start();
session_write_close();
require_once('../incs/functions.inc.php');
include('../incs/html2text.php');
$ret_arr = array();
$userid = ((!empty($_SESSION)) && ((isset($_SESSION['user_id'])) && ($_SESSION['user_id'] != 0))) ? $_SESSION['user_id'] : 0;
$where = ($userid != 0) ? "WHERE (`_by` = '" . $userid . "')" : "";

if((isset($filter)) && ($filter != "")) { $where .= " AND ((`m`.`fromname` REGEXP '" . $filter . "') OR (`m`.`message` REGEXP '" . $filter . "') OR (`m`.`recipients` REGEXP '" . $filter . "') OR  (`m`.`subject` REGEXP '" . $filter . "'))"; }


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
		{$where} {$order} {$order2}";

$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$i = 0;
if (mysql_num_rows($result) == 0) {
	$ret_arr[$i][0] = "No Messages";
	} else {
	while ($msg_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
		$ret_arr[$i][0] = $msg_row['id'];	
		$ret_arr[$i][1] = $msg_row['ticket_id'];
		$ret_arr[$i][2] = $msg_row['msg_type'];
		$ret_arr[$i][3] = $fromname;
		$ret_arr[$i][5] = stripslashes_deep(shorten($msg_row['subject'], 18));
		$ret_arr[$i][6] = stripslashes_deep(shorten($the_message, 2000));
		$ret_arr[$i][7] = format_date_2(strtotime($msg_row['date']));
		$ret_arr[$i][8] = get_owner($msg_row['_by']);	
		$i++;
		} // end while	
	}				// end else
print json_encode($ret_arr);
?>