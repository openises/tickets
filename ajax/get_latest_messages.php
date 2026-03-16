<?php
require_once('../incs/functions.inc.php');
require_once('../incs/messaging.inc.php');

set_time_limit(0);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `read_status` = 0";
$result = db_query($query) or do_error($query, 'db_query() failed', db()->error, basename( __FILE__), __LINE__);
$num_new_msgs = $result->num_rows;
if($num_new_msgs != 0) {
	$the_return = array (1);
	} else {
	$the_return = array (0);
	}
print json_encode($the_return);
exit();
?>

		
