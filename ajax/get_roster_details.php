<?php
/*
9/10/13 - New file, lists personnel for Roster user functionality
*/
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');

if(empty($_GET)) {
	exit();
	}

$the_id = strip_tags($_GET['id']);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]personnel` WHERE `id` = '" . $the_id . "' LIMIT 1"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));

$ret_arr[0] = $row['id'];
$ret_arr[1] = $row['person_identifier'];
$ret_arr[2] = $row['forenames'];
$ret_arr[3] = $row['surname'];
$ret_arr[4] = $row['address'];
$ret_arr[5] = $row['state'];
$ret_arr[6] = $row['homephone'];
$ret_arr[7] = $row['workphone'];
$ret_arr[8] = $row['cellphone'];
$ret_arr[9] = $row['email'];
$ret_arr[10] = $row['amateur_radio_callsign'];
$ret_arr[11] = $row['person_capabilities'];
$ret_arr[12] = $row['person_notes'];

print json_encode($ret_arr);
exit();
?>