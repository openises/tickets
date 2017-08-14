<?php
/*
4/25/16 initial release Sets local maps to 1 once all map tiles are downloaded.
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
extract($_GET);
$ret_arr = array();

function update_setting ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '" . $which . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)!=0) {
		$query2 = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '" . $which . "'";
		$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$success = ($result2) ? 1 : 0;
		}
	unset ($result);
	unset ($result2);
	return $success;
	}				// end function update_setting ()
	
$boundsString = $bl_lat . "," . $bl_lon . "," . $tr_lat . "," . $tr_lon;

$theResult = update_setting ('bounds', $boundsString);

$ret_arr[0] = $theResult;
print json_encode($ret_arr);
exit();
?>