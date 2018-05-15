<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];
if(!(secure_page($the_session))) {
	exit();
	} else {
	$tp_id = $_GET['tp_id'];

	$ret_arr = array();

	$query = "SELECT *
		FROM `$GLOBALS[mysql_prefix]training_packages` `tp` 
		WHERE `tp`.`id` = {$tp_id};";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	
			$ret_arr[] = $row['id'];
			$ret_arr[] = $row['package_name'];
			$ret_arr[] = $row['description'];
			$ret_arr[] = $row['available'];
			$ret_arr[] = $row['provider'];
			$ret_arr[] = $row['address'];		
			$ret_arr[] = $row['name'];
			$ret_arr[] = $row['email'];
			$ret_arr[] = $row['phone'];		
			$ret_arr[] = $row['cost'];		
			}
		
	print json_encode($ret_arr);
}	