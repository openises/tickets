<?php
/*
list messages.php - gets messages from messages table for display in message window and ticket view and unit view
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');

if(empty($_GET)) {
	exit();
	}

$the_id = strip_tags($_GET['id']);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = '" . $the_id . "' LIMIT 1"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));

$ret_arr[0] = $row['id'];
$ret_arr[1] = $row['user'];
$ret_arr[2] = $row['name_f'];
$ret_arr[3] = $row['name_mi'];
$ret_arr[4] = $row['name_l'];
$ret_arr[5] = $row['addr_street'];
$ret_arr[6] = $row['addr_city'];
$ret_arr[7] = $row['addr_st'];
$ret_arr[8] = $row['phone_p'];
$ret_arr[9] = $row['phone_s'];
$ret_arr[10] = $row['phone_m'];
$ret_arr[11] = $row['email'];
$ret_arr[12] = $row['email_s'];

print json_encode($ret_arr);
?>