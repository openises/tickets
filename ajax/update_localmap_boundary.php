<?php
/*
4/25/16 initial release Sets local maps to 1 once all map tiles are downloaded.
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
// Replaced extract — explicit variable assignments (Phase 2 cleanup)
$bl_lat = sanitize_string($_GET['bl_lat'] ?? '');
$bl_lon = sanitize_string($_GET['bl_lon'] ?? '');
$tr_lat = sanitize_string($_GET['tr_lat'] ?? '');
$tr_lon = sanitize_string($_GET['tr_lon'] ?? '');
$ret_arr = array();

function update_setting ($which, $what) {		//	3/15/11
	$which = sanitize_string($which);
	$what = sanitize_string($what);
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}settings` WHERE `name`= ? LIMIT 1";
	$result = db_query($query, [$which]);
	if ($result->num_rows !=0) {
		$query2 = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`= ? WHERE `name` = ?";
		$result2 = db_query($query2, [
			$what,
			$which
		]);
		$success = ($result2) ? 1 : 0;
		}
	unset ($result);
	unset ($result2);
	return $success;
	}				// end function update_setting ()

// Variables already sanitized above during explicit extraction
$boundsString = $bl_lat . "," . $bl_lon . "," . $tr_lat . "," . $tr_lon;

$theResult = update_setting ('bounds', $boundsString);

$ret_arr[0] = $theResult;
print json_encode($ret_arr);
exit();
?>
