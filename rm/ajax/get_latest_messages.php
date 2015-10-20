<?php
/*
9/10/13 - New file, checks for new unread messages to light messages button on mobile page
*/
require_once('../../incs/functions.inc.php');
require_once('../../incs/messaging.inc.php');
set_time_limit(0);
if(empty($_GET)) {
	exit();
	}

@session_start();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `read_status` = 0 AND `resp_id` = '" . clean_string($_GET['responder_id']) . "'";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_new_msgs = mysql_num_rows($result);
if($num_new_msgs != 0) {
	$the_return = array (1);
	} else {
	$the_return = array (0);
	}
print json_encode($the_return);

exit();
?>
		
