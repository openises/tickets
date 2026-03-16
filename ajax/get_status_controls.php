<?php
require_once('../incs/functions.inc.php');
set_time_limit(90);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}

$ret_arr = array();
$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result_st = db_query($query) or do_error($query, 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep($result_st->fetch_array())) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	$status_hide[$temp] = $row_st['hide'];
	}

unset($result_st);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC";											// 2/1/10, 3/15/10, 6/10/11
$result = db_query($query) or do_error($query, 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
while ($row = stripslashes_deep($result->fetch_assoc())) {			// 7/7/10
	$status = (valid_status($row['un_status_id'])) ? get_status_sel($row['id'], $row['un_status_id'], "u") : "Status Error";		// status
	$ret_arr[$row['id']] = $status;
	}				// end  ==========  while() for RESPONDER ==========

//dump($ret_arr);
print json_encode($ret_arr);
exit();
?>