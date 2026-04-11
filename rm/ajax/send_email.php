<?php
/*
9/10/13 - new file - sends email from mobile screen.
*/
error_reporting(E_ALL);
require_once '../../incs/functions.inc.php';
@session_start();
$the_session = $_GET['session'];
$ret_arr = array();
$from = '127.0.0.1';
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$ticket_id = sanitize_int($_GET['ticket_id']);
$resp_id = sanitize_int($_GET['resp_id']);
$from_address = sanitize_string($_GET['from_address']);
$fromname = sanitize_string($_GET['fromname']);
$subject = sanitize_string($_GET['subject']);
$message = sanitize_string($_GET['message']);
$msg_type = 2;
$message_id = "email";
$recipients = "Tickets";
$read_status = 0;
$delivery_status = 2;

$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}messages` (
	`msg_type`,
	`message_id`,
	`ticket_id`,
	`resp_id`,
	`recipients`,
	`from_address`,
	`fromname`,
	`subject`,
	`message`,
	`date`,
	`read_status`,
	`delivery_status`,
	`_by`,
	`_on`,
	`_from` )
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$result = db_query($query, [
	trim($msg_type),
	trim($message_id),
	$ticket_id,
	$resp_id,
	trim($recipients),
	trim($from_address),
	trim($fromname),
	trim($subject),
	trim($message),
	trim($now),
	$read_status,
	$delivery_status,
	trim($resp_id),
	trim($now),
	trim($from)
]);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}

print json_encode($ret_arr);
exit();
?>
