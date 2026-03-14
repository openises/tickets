<?php
/*
del_messages.php - puts all current messages into the wastebasket - the del_all function.
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');
$messages = array_key_exists('messages', $_GET) ? sanitize_string($_GET['messages']) : "";
if($messages == "") {
	exit();
	}

$msgs_arr = explode("|", $messages);
$i=0;
$ret_arr = array();
foreach($msgs_arr as $id) {
	$id = sanitize_int($id);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = ?";
	$result = db_query($query, [$id]) or do_error($query, $query, db()->error, basename( __FILE__), __LINE__);
	$row = stripslashes_deep($result->fetch_assoc());
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

	$query_ins = "INSERT INTO `$GLOBALS[mysql_prefix]messages_bin` (
			`msg_type`, `message_id`, `ticket_id`, `resp_id`, `recipients`, `from_address`, `fromname`, `subject`, `message`, `status`, `date`, `read_status`, `readby`, `delivered`, `delivery_status`, `_by`, `_from`, `_on`
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$result_ins = db_query($query_ins, [
			trim($msg_type),
			trim($message_id),
			trim($ticket_id),
			trim($resp_id),
			trim($recipients),
			trim($from_address),
			trim($fromname),
			trim($subject),
			trim($message),
			trim($status),
			trim($date),
			trim($read_status),
			trim($readby),
			trim($delivered),
			trim($delivery_status),
			trim($by),
			trim($from),
			trim($on)
	]) or do_error($query_ins, 'mysql_query() failed', db()->error, __FILE__, __LINE__);
	$query_del = "DELETE FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = ?";
	$result_del = db_query($query_del, [$id]) or do_error($query_del, 'mysql_query() failed', db()->error, __FILE__, __LINE__);
	if($result_del) {
		$ret_arr[$i][0] = 100;
		$ret_arr[$i][1] = $id;
		} else {
		$ret_arr[][0] = 200;
		$ret_arr[$i][1] = $id;
		}
	$i++;
	}
print json_encode($ret_arr);
exit();
?>