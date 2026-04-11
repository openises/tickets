<?php
error_reporting(E_ALL);
require_once '../../incs/functions.inc.php';
@session_start();
$the_session = $_GET['session'];
$ret_arr = array();

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}adverts` WHERE `active` = 1 ORDER BY rand() LIMIT 1";
$result = db_query($query);
if($result->num_rows == 1) {
	$row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
	$ret_arr[0] = $row['name'];
	$ret_arr[1] = $row['url'];
	$ret_arr[2] = $row['picture'];
	}

print json_encode($ret_arr);
