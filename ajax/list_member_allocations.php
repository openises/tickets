<?php
require_once('../incs/functions.inc.php');
$key = sanitize_int($_GET['key']);
$theType = sanitize_int($_GET['type']);

$print = "";
function check_training_allocation($id, $type, $skill_id) {
	$ret = array();
	$query	= "SELECT *, `completed` AS `completed`, `refresh_due` AS `refresh_due` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = ? AND `skill_type` = ? AND `skill_id` = ?";
	$result	= db_query($query, [$id, $type, $skill_id]);
	if($result->num_rows != 0) {
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$ret[0] = strtotime($row['completed']);
		$ret[1] = strtotime($row['refresh_due']);
		} else {
		$ret = false;
		}
	return $ret;
	}

$members = array();
$member_list = "";

$query_mem	= "SELECT * FROM `$GLOBALS[mysql_prefix]member`";
$result_mem	= db_query($query_mem);
while($row = $result_mem->fetch_array(MYSQLI_ASSOC)) {
	$members[$row['id']] = $row['field2'] . " " . $row['field1'];
	}
if(count($members) > 0) {
	foreach ($members AS $key1 => $val1) {
		$sel = (check_training_allocation($key1, $theType, $key)) ? "CHECKED disabled='disabled'" : "";
		$print .= "<DIV style='width: 150px; float: left; text-align: left;'><INPUT type='checkbox' name='frm_tra[" . intval($key1) . "]' value='" . e($val1) . "' " . $sel . ">" . shorten(e($val1), 20) . "</DIV>";
		}
	}

print $print;