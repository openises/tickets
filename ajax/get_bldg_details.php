<?php
/*
list messages.php - gets messages from messages table for display in message window and ticket view and unit view
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');

if(empty($_GET)) {
	exit();
	}

$the_id = sanitize_int($_GET['id']);

$ret_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `id` = ?";		// types in use
$result = db_query($query, [$the_id]);
if($result) {
	$row = stripslashes_deep($result->fetch_assoc());
	$ret_arr[0] = $row['name'];
	$ret_arr[1] = $row['street'];
	$ret_arr[2] = $row['city'];
	$ret_arr[3] = $row['state'];
	$ret_arr[4] = $row['lat'];
	$ret_arr[5] = $row['lon'];
	} else {
	$ret_arr[0] = 0;
	}
print json_encode($ret_arr);
?>