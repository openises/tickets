<?php
/*
del_messages.php - puts all current messages into the wastebasket - the del_all function.
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages`";
$result = db_query($query) or do_error($query, $query, db()->error, basename( __FILE__), __LINE__);
while ($row = stripslashes_deep($result->fetch_assoc())) {
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
    ]) or do_error($query_ins, 'db_query() failed', db()->error, __FILE__, __LINE__);
    }
$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]messages`";
$result = db_query($query) or do_error($query, 'db_query() failed', db()->error, __FILE__, __LINE__);
if($result) {
    $ret_arr[0] = 100;
    } else {
    $ret_arr[0] = 200;
    }
print json_encode($ret_arr);
exit();
?>