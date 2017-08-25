<?php
require_once('../incs/functions.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
	
$id = $_GET['facility_id'];

$ret_arr = array();
$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp]['status'] = $row_st['status_val'];
	$status_vals[$temp]['bg_color'] = $row_st['bg_color'];
	$status_vals[$temp]['text_color'] = $row_st['text_color'];
	}

unset($result_st);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id;											// 2/1/10, 3/15/10, 6/10/11
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 7/7/10
	$status = (valid_fac_status($row['status_id'])) ? get_status_sel($row['id'], $row['status_id'], "f") : "Status Error";		// status
	if(get_variable('facility_auto_status') == "0") {
		$ret_arr[] = $status;
		} else {
		$theStatus = $status_vals[$row['status_id']]['status'];
		$bgcolor = $status_vals[$row['status_id']]['bg_color'];
		$textcolor = $status_vals[$row['status_id']]['text_color'];
		$ret_arr[] = "<SPAN style='width: 100%; display: inline-block; background-color: " . $bgcolor . "; color: " . $textcolor . ";'>" . $theStatus . "</SPAN>";	
		}
	}				// end  ==========  while() for RESPONDER ==========

print json_encode($ret_arr);
exit();
?>