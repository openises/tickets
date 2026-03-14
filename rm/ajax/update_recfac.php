<?php
/*
*/
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
$tick_id = sanitize_int($_GET['ticket_id']);
$fac_id = sanitize_int($_GET['recfac']);
$ret_arr = array();

$query = "UPDATE `{$GLOBALS['mysql_prefix']}ticket` SET `rec_facility` = ? WHERE `id` = ?";
$result = db_query($query, [$fac_id, $tick_id]);
if (db()->affected_rows!=0) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 99;
	}
print json_encode($ret_arr);
exit();
?>
