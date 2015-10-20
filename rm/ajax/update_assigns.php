<?php
/*
9/10/13 - New file, updates dispatch status from mobile screen
*/
error_reporting(E_ALL);

@session_start();
require_once('../../incs/functions.inc.php');

$vals_ary = explode("%", $_POST['frm_vals']);
$ret_arr = array();
$frm_id = $_POST['frm_id'];
$frm_tick = $_POST['frm_tick'];
$frm_unit = $_POST['frm_unit'];

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$date_part="";
if (in_array("frm_dispatched",$vals_ary)) {
	$date_part .= "`dispatched` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_DISP'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 1;
	}
if (in_array("frm_responding",$vals_ary)) {
	$date_part .= "`responding` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_RESP'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 2;
	}
if (in_array("frm_on_scene",$vals_ary)) {
	$date_part .= "`on_scene` = ". quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_ONSCN'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 3;
	}
if (in_array("frm_u2fenr",$vals_ary)) {
	$date_part .= "`u2fenr` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_U2FENR'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 4;
	}
if (in_array("frm_u2farr",$vals_ary)) {
	$date_part .= "`u2farr` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_U2FARR'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 5;
	}
if (in_array("frm_clear",$vals_ary)) {
	$date_part .= "`clear` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_CLR'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 6;
	}

$date_part .= substr($date_part, 0, -2);

$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) .", " . $date_part ;
$query .=  " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);

set_u_updated ($_POST['frm_id']);
$use_status_update = "1";
if($use_status_update == "1") {
	print auto_disp_status($disp_status, $frm_unit);
	}
$ret_arr[0] = quote_smart($now);
print json_encode($ret_arr);
exit();
?>