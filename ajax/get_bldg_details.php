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

$ret_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `id` = " . $id;		// types in use
$result = mysql_query($query);
if($result) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
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