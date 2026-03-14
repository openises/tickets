<?php
/*
restore_message.php - restores message from wastebasket to inbox.
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');

$id = (isset($_GET['id'])) ? sanitize_int($_GET['id']) : NULL;
$ret_arr = array();

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}messages_bin` WHERE `id` = ?";
$result = db_query($query, [['type' => 'i', 'value' => $id]]);
$row = $result->fetch_assoc();
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

$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}messages` (
		`msg_type`, `message_id`, `ticket_id`, `resp_id`, `recipients`, `from_address`, `fromname`, `subject`, `message`, `status`, `date`, `read_status`, `readby`, `delivered`, `delivery_status`, `_by`, `_from`, `_on`
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$params = [
	['type' => 's', 'value' => trim($msg_type)],
	['type' => 's', 'value' => trim($message_id)],
	['type' => 'i', 'value' => trim($ticket_id)],
	['type' => 'i', 'value' => trim($resp_id)],
	['type' => 's', 'value' => trim($recipients)],
	['type' => 's', 'value' => trim($from_address)],
	['type' => 's', 'value' => trim($fromname)],
	['type' => 's', 'value' => trim($subject)],
	['type' => 's', 'value' => trim($message)],
	['type' => 's', 'value' => trim($status)],
	['type' => 's', 'value' => trim($date)],
	['type' => 's', 'value' => trim($read_status)],
	['type' => 's', 'value' => trim($readby)],
	['type' => 's', 'value' => trim($delivered)],
	['type' => 's', 'value' => trim($delivery_status)],
	['type' => 'i', 'value' => trim($by)],
	['type' => 'i', 'value' => trim($from)],
	['type' => 's', 'value' => trim($on)]
];
$result = db_query($query, $params);

$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}messages_bin` WHERE `id` = ?";
$result = db_query($query, [['type' => 'i', 'value' => $id]]);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}
print json_encode($ret_arr);
exit();
?>
