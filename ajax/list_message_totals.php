<?php
/*
list messages totals.php - gets new message totals
21/1/14 - new file
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
@session_start();
session_write_close();
require_once('../incs/functions.inc.php');
include('../incs/html2text.php');
$ret_arr = array();
$i = 0;
$in_counter = 0;
$out_counter = 0;

$ticket_id = (isset($_GET['ticket_id'])) ? clean_string($_GET['ticket_id']) : NULL;
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;
	
$where = "WHERE (`m`.`msg_type` = '1' OR `m`.`msg_type` = '2' OR `m`.`msg_type` = '3' OR `m`.`msg_type` = '4' OR `m`.`msg_type` = '5' OR `m`.`msg_type` = '6')";

if(isset($ticket_id)) { $where .= " AND (`ticket_id` = '" . $ticket_id . "')"; }
if(isset($responder_id)) { $where .= " AND (`resp_id` = '" . $responder_id . "')"; }
	
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
		{$where}";

$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);

if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$ret_arr[0][0] = 0;
	$ret_arr[0][1] = 0;	
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$the_readers = array();
		$the_readers = explode("," , $row['readby']);
		if(($the_readers[0] == "") || (!in_array($the_user, $the_readers, true))) {
			$isread = 0;
			} else {
			$isread = 1;
			}
		if((($row['msg_type'] == 1) || ($row['msg_type'] == 3)) && ($isread == 0)) { $out_counter++; }
		if((($row['msg_type'] == 2) || ($row['msg_type'] == 4) || ($row['msg_type'] == 5)) && ($isread == 0)) { $in_counter++; }
		} // end while
	$ret_arr[0][0] = $in_counter;
	$ret_arr[0][1] = $out_counter;	
	}				// end else

print json_encode($ret_arr);
exit();
?>