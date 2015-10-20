<?php
/*
9/10/13 - New file, updates ticket assignment mileage from mobile screen
*/
error_reporting(E_ALL);

@session_start();
require_once('../../incs/functions.inc.php');
$ret_arr = array();
$type = $_GET['type'];
$value = $_GET['value'];
$assigns_id = $_GET['assigns_id'];

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$date_part="";
$date_part .= "`" . $type . "` = " . $value;

do_log($GLOBALS['LOG_CALL_DISP'], 	$frm_tick, $frm_unit, $frm_id);

$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) .", " . $date_part ;
$query .=  " WHERE `id` = " . $assigns_id . " LIMIT 1";
$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 999;
	}
print json_encode($ret_arr);
exit();
?>