<?php
#
# statistics.php - Management Statistics from Tickets.
#
/*
6/14/11	First version
*/
error_reporting(0);
require_once('../incs/functions.inc.php');
$type = (isset($type)) ? clean_string($type) : "";

function get_stat_type_type($value) {
	$stat_type = "Not Used";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` WHERE `st_id` = {$value}";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
		$stat_type = $row['stat_type'];
		}
	return $stat_type;
	}

print json_encode(get_stat_type_type($type));
exit();
?>
