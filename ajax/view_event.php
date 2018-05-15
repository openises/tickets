<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];
if(!(secure_page($the_session))) {
	exit();
	} else {
	$ev_id = $_GET['ev_id'];

	$ret_arr = array();

	$query = "SELECT *
		FROM `$GLOBALS[mysql_prefix]events` `ev` 
		WHERE `ev`.`id` = {$ev_id};";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	
			$ret_arr[] = $row['id'];
			$ret_arr[] = $row['event_name'];
			$ret_arr[] = $row['description'];
			}
		
	print json_encode($ret_arr);
}	