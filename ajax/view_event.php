<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];
if(!(secure_page($the_session))) {
	exit();
	} else {
	$ev_id = sanitize_int($_GET['ev_id']);

	$ret_arr = array();

	$query = "SELECT *
		FROM `{$GLOBALS['mysql_prefix']}events` `ev`
		WHERE `ev`.`id` = ?";

		$result = db_query($query, [$ev_id]);

		while ($row = $result->fetch_assoc()) {
			$ret_arr[] = $row['id'];
			$ret_arr[] = $row['event_name'];
			$ret_arr[] = $row['description'];
			}

	print json_encode($ret_arr);
}