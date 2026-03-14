<?php
/*
4/25/16 initial release Sets local maps to 1 once all map tiles are downloaded.
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
$ret_arr = array();

function update_setting ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}settings` WHERE `name`= ? LIMIT 1";
	$result = db_query($query, [['type' => 's', 'value' => $which]]);
	if ($result->num_rows !=0) {
		$query2 = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`= ? WHERE `name` = ?";
		$result2 = db_query($query2, [
			['type' => 's', 'value' => $what],
			['type' => 's', 'value' => $which]
		]);
		$success = ($result2) ? 1 : 0;
		}
	unset ($result);
	unset ($result2);
	return $success;
	}				// end function update_setting ()

$theResult = update_setting ('local_maps','1');

$ret_arr[0] = $theResult;

print json_encode($ret_arr);
exit();
?>