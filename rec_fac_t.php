<?php
/*
10/18/11	New release - updating receiving Facility from Mobile screen.
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('incs/functions.inc.php');	

$fac_id = $_POST['rec_fac'];
$unit_id = $_POST['unit'];
$tick_id = $_POST['tick_id'];
$assign_id = $_POST['frm_id'];

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
	`rec_facility`= " . 	quote_smart($fac_id) . ",
	`updated`= " . quote_smart($now) . ",
	`_by` = " . quote_smart($unit_id) . "
	WHERE `id` = " . $tick_id . " LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
	`rec_facility_id`= " . 	quote_smart($fac_id) . ",
	`as_of`= " . quote_smart($now) . "
	 WHERE `id` = " . $assign_id . " LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
?>