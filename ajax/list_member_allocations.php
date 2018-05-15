<?php
require_once('../incs/functions.inc.php');
$key = $_GET['key'];
$theType = $_GET['type'];

$print = "";
function check_training_allocation($id, $type, $skill_id) {
	$ret = array();
	$query	= "SELECT *, `completed` AS `completed`, `refresh_due` AS `refresh_due` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = '" . $id . "' AND `skill_type` = '" . $type . "' AND `skill_id` = '" . $skill_id . "'";
//	print $query;
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = mysql_fetch_array($result,MYSQL_ASSOC);	
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
$result_mem	= mysql_query($query_mem) or do_error($query_mem, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
while($row = mysql_fetch_array($result_mem,MYSQL_ASSOC)) {
	$members[$row['id']] = $row['field2'] . " " . $row['field1'];
	}
if(count($members) > 0) {	
	foreach ($members AS $key1 => $val1) {
		$sel = (check_training_allocation($key1, $theType, $key)) ? "CHECKED disabled='disabled'" : "";							
		$print .= "<DIV style='width: 150px; float: left; text-align: left;'><INPUT type='checkbox' name='frm_tra[" . $key1 . "]' value='" . $val1 . "' " . $sel . ">" . shorten($val1, 20) . "</DIV>";
		}
	}

print $print;