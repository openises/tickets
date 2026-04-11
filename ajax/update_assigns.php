<?php
/*
1/15/09 initial release
1/20/09 added $frm_tick, $frm_unit
1/21/09 added frm_id as info param
2/1/09 corrections to logging
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/28/10 frm_u2fenr and frm_u2farr added
9/1/10 set unit 'updated' time
*/
error_reporting(E_ALL);

require_once '../../incs/functions.inc.php';        //7/28/10

$vals_ary = explode("%", sanitize_string($_POST['frm_vals']));        // example: "frm_id=17&frm_vals=frm_dispatched%frm_responding%frm_clear"
$ret_arr = array();
$frm_id = sanitize_int($_POST['frm_id']);
$frm_tick = sanitize_int($_POST['frm_tick']);
$frm_unit = sanitize_int($_POST['frm_unit']);

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$set_parts = [];
$params = [$now]; // for as_of

if (in_array("frm_dispatched",$vals_ary))    {
    $set_parts[] = "`dispatched` = ?";
    $params[] = $now;
    do_log($GLOBALS['LOG_CALL_DISP'],     $frm_tick, $frm_unit, $frm_id);
    $disp_status = 1;
    }
if (in_array("frm_responding",$vals_ary))    {
    $set_parts[] = "`responding` = ?";
    $params[] = $now;
    do_log($GLOBALS['LOG_CALL_RESP'],     $frm_tick, $frm_unit, $frm_id);
    $disp_status = 2;
    }
if (in_array("frm_on_scene",$vals_ary))        {
    $set_parts[] = "`on_scene` = ?";
    $params[] = $now;
    do_log($GLOBALS['LOG_CALL_ONSCN'],     $frm_tick, $frm_unit, $frm_id);
    $disp_status = 3;
    }
if (in_array("frm_u2fenr",$vals_ary))        {
    $set_parts[] = "`u2fenr` = ?";
    $params[] = $now;
    do_log($GLOBALS['LOG_CALL_U2FENR'],     $frm_tick, $frm_unit, $frm_id);
    $disp_status = 4;
    }
if (in_array("frm_u2farr",$vals_ary))        {
    $set_parts[] = "`u2farr` = ?";
    $params[] = $now;
    do_log($GLOBALS['LOG_CALL_U2FARR'],     $frm_tick, $frm_unit, $frm_id);
    $disp_status = 5;
    }
if (in_array("frm_clear",$vals_ary))        {
    $set_parts[] = "`clear` = ?";
    $params[] = $now;
    do_log($GLOBALS['LOG_CALL_CLR'],     $frm_tick, $frm_unit, $frm_id);
    $disp_status = 6;
    }

$date_part = implode(", ", $set_parts);

$query = "UPDATE `{$GLOBALS['mysql_prefix']}assigns` SET `as_of`= ?" . ($date_part ? ", " . $date_part : "");
$query .=  " WHERE `id` = ? LIMIT 1";
$params[] = $frm_id;

$result    = db_query($query, $params);

set_u_updated ($frm_id);                                 // set unit 'updated' time - 9/1/10
$use_status_update = get_variable('use_disp_autostat');
// 8/22/13 - Update unit status to reflect dispatch status
if($use_status_update == "1") {
    auto_disp_status($disp_status, $frm_unit);
    }
$ret_arr[0] = "'" . $now . "'";
print json_encode($ret_arr);
exit();
?>
