<?php
/*
*/
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
$tick_id = sanitize_int($_GET['ticket_id']);
$ret_arr = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `id` = ?";
$result = db_query($query, [$tick_id]);
if (db()->affected_rows!=0) {
	while ($row = stripslashes_deep($result->fetch_assoc())) {
		$the_fac = $row['rec_facility'];
		}
	} else {
	$the_fac = 0;
	}

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}facilities`";
$result = db_query($query);
if (db()->affected_rows!=0) {
	$outstr = "<SELECT name='frm_recfac' ONCHANGE = 'update_recfac(tick_id, this.value);'>";
	while ($row = stripslashes_deep($result->fetch_assoc())) {
		$sel = ($row['id']==$the_fac)? " SELECTED": "";
		$outstr .= "<OPTION VALUE=" . intval($row['id']) . $sel .">" . e(shorten($row['handle'], 12)) . "</OPTION>";
		}
	$outstr .= "</SELECT>";
	} else {
	$outstr = "";
	}
$ret_arr[0] = $outstr;
print json_encode($ret_arr);
exit();
?>
