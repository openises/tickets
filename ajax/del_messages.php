<?php
/*
del_messages.php - puts all current messages into the wastebasket - the del_all function.
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages`";
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$msg_type = $row['msg_type'];
	$message_id = $row['message_id'];
	$ticket_id = $row['ticket_id'];
	$resp_id = $row['resp_id'];
	$recipients = $row['recipients'];
	$from_address = $row['from_address'];
	$fromname = $row['fromname'];
	$subject = $row['subject'];
	$message = $row['message'];
	$status = $row['status'];
	$date = $row['date'];
	$read_status = $row['read_status'];
	$readby = $row['readby'];
	$delivered = $row['delivered'];
	$delivery_status = $row['delivery_status'];
	$by = $row['_by'];
	$from = $row['_from'];
	$on = $row['_on'];

	$query = "INSERT INTO `$GLOBALS[mysql_prefix]messages_bin` (
			`msg_type`, `message_id`, `ticket_id`, `resp_id`, `recipients`, `from_address`, `fromname`, `subject`, `message`, `status`, `date`, `read_status`, `readby`, `delivered`, `delivery_status`, `_by`, `_from`, `_on`
			) VALUES (" . 
			quote_smart(trim($msg_type)) . "," . 
			quote_smart(trim($message_id)) . "," . 
			quote_smart(trim($ticket_id)) . "," . 
			quote_smart(trim($resp_id)) . "," . 
			quote_smart(trim($recipients)) . "," . 
			quote_smart(trim($from_address)) . "," . 
			quote_smart(trim($fromname)) . "," . 
			quote_smart(trim($subject)) . "," . 
			quote_smart(trim($message)) . "," .
			quote_smart(trim($status)) . "," . 
			quote_smart(trim($date)) . "," . 
			quote_smart(trim($read_status)) . "," . 
			quote_smart(trim($readby)) . "," . 
			quote_smart(trim($delivered)) . "," . 
			quote_smart(trim($delivery_status)) . "," . 
			quote_smart(trim($by)) . "," . 
			quote_smart(trim($from)) . "," . 
			quote_smart(trim($on)) . ");";			
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	}
$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]messages`";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}
print json_encode($ret_arr);
exit();
?>