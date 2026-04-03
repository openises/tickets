<?php
/*
10/18/11    New release - updating receiving Facility from Mobile screen.
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('incs/functions.inc.php');

$fac_id = sanitize_int($_POST['rec_fac']);
$unit_id = sanitize_int($_POST['unit']);
$tick_id = sanitize_int($_POST['tick_id']);
$assign_id = sanitize_int($_POST['frm_id']);

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query = "UPDATE `{$GLOBALS['mysql_prefix']}ticket` SET
    `rec_facility`= ?,
    `updated`= ?,
    `_by` = ?
    WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$fac_id, $now, $unit_id, $tick_id]);

$query = "UPDATE `{$GLOBALS['mysql_prefix']}assigns` SET
    `rec_facility_id`= ?,
    `as_of`= ?
     WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$fac_id, $now, $assign_id]);
?>