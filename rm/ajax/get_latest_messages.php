<?php
/*
9/10/13 - New file, checks for new unread messages to light messages button on mobile page
*/
require_once '../../incs/functions.inc.php';
require_once '../../incs/messaging.inc.php';
set_time_limit(90);
if(empty($_GET)) {
	exit();
	}

@session_start();

$responder_id = sanitize_string($_GET['responder_id']);
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}messages` WHERE `read_status` = 0 AND `resp_id` = ?";
$result = db_query($query, [$responder_id]);
$num_new_msgs = $result->num_rows;
if($num_new_msgs != 0) {
	$the_return = array (1);
	} else {
	$the_return = array (0);
	}
print json_encode($the_return);

exit();
?>
		
