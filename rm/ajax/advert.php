<?php
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];
$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]adverts` WHERE `active` = 1 ORDER BY rand() LIMIT 1";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
if(mysql_num_rows($result) == 1) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$ret_arr[0] = $row['name'];
	$ret_arr[1] = $row['url'];
	$ret_arr[2] = $row['picture'];
	}

print json_encode($ret_arr);
