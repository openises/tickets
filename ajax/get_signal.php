<?php
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
$ret_arr = array();
if(!array_key_exists("code", $_GET)) {
	$ret_arr[0] = "Error";
	} else {
	$theID = $_GET['code'];
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` WHERE `code` = '" . $theID . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);
	if (mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr[0] = $row['text'];
		} else {
		$ret_arr[0] = "Not Found";
		}
	}
		
print json_encode($ret_arr);
?>
