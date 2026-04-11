<?php
/*
9/10/13 - New file, updates dispatch status from mobile screen
*/
error_reporting(E_ALL);

@session_start();
require_once '../../incs/functions.inc.php';

$vals_ary = explode("%", $_POST['frm_vals']);
$ret_arr = array();
$frm_id = sanitize_int($_POST['frm_id']);
$frm_tick = sanitize_int($_POST['frm_tick']);
$frm_unit = sanitize_int($_POST['frm_unit']);

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$date_part="";
if (in_array("frm_dispatched",$vals_ary)) {
	$date_part .= "`dispatched` = ?, ";
	do_log($GLOBALS['LOG_CALL_DISP'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 1;
	}
if (in_array("frm_responding",$vals_ary)) {
	$date_part .= "`responding` = ?, ";
	do_log($GLOBALS['LOG_CALL_RESP'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 2;
	}
if (in_array("frm_on_scene",$vals_ary)) {
	$date_part .= "`on_scene` = ?, ";
	do_log($GLOBALS['LOG_CALL_ONSCN'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 3;
	}
if (in_array("frm_u2fenr",$vals_ary)) {
	$date_part .= "`u2fenr` = ?, ";
	do_log($GLOBALS['LOG_CALL_U2FENR'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 4;
	}
if (in_array("frm_u2farr",$vals_ary)) {
	$date_part .= "`u2farr` = ?, ";
	do_log($GLOBALS['LOG_CALL_U2FARR'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 5;
	}
if (in_array("frm_clear",$vals_ary)) {
	$date_part .= "`clear` = ?, ";
	do_log($GLOBALS['LOG_CALL_CLR'], 	$frm_tick, $frm_unit, $frm_id);
	$disp_status = 6;
	}

// Count how many date fields were set
$date_field_count = substr_count($date_part, '?');
$params = [$now]; // for as_of
for ($i = 0; $i < $date_field_count; $i++) {
	$params[] = $now;
}
$params[] = $frm_id;

// Remove trailing comma-space from date_part
$date_part = rtrim($date_part, ", ");

$query = "UPDATE `{$GLOBALS['mysql_prefix']}assigns` SET `as_of`= ?" . ($date_part ? ", " . $date_part : "");
$query .=  " WHERE `id` = ? LIMIT 1";
$result	= db_query($query, $params);

set_u_updated ($frm_id);
$use_status_update = "1";
if($use_status_update == "1") {
	print auto_disp_status($disp_status, $frm_unit);
	}
$ret_arr[0] = $now;
print json_encode($ret_arr);
exit();
?>