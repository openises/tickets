<?php
/*
*/
error_reporting(E_ALL);	
require_once('../../incs/functions.inc.php');
$tick_id = $_GET['ticket_id'];
$fac_id = $_GET['recfac'];
$ret_arr = array();

$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `rec_facility` = " . $fac_id . " WHERE `id` = " . $tick_id;	
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows()!=0) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 99;
	}
print json_encode($ret_arr);
exit();
?>
