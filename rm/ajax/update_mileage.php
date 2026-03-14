<?php
/*
9/10/13 - New file, updates ticket assignment mileage from mobile screen
*/
error_reporting(E_ALL);

@session_start();
require_once('../../incs/functions.inc.php');
$ret_arr = array();
$type = sanitize_string($_GET['type']);
$value = sanitize_string($_GET['value']);
$assigns_id = sanitize_int($_GET['assigns_id']);

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

// Whitelist allowed column names for type
$allowed_types = ['start_miles', 'end_miles', 'on_scene_miles'];
if (!in_array($type, $allowed_types)) {
	$ret_arr[0] = 999;
	print json_encode($ret_arr);
	exit();
}

$query = "UPDATE `{$GLOBALS['mysql_prefix']}assigns` SET `as_of`= ?, `" . $type . "` = ? WHERE `id` = ? LIMIT 1";
$result	= db_query($query, [$now, $value, $assigns_id]);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 999;
	}
print json_encode($ret_arr);
exit();
?>