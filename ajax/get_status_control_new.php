<?php
require_once('../incs/functions.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */
$id = $_GET['responder_id'];
$grp_arr = array();
$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;											// 2/1/10, 3/15/10, 6/10/11
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$status_id = $row['un_status_id'];
$icon_str = $row['icon_str'];

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$tmp_arr[$row['group']] = $row['group'];
	}
	
$tmp_arr = array_unique($tmp_arr);

foreach($tmp_arr as $key => $val) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `group` = '" . $val . "' ORDER BY `sort`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$grp_arr[$key][$row['id']]['name'] = $row['status_val'];
		$grp_arr[$key][$row['id']]['hide'] = $row['hide'];
		$grp_arr[$key][$row['id']]['bg_color'] = $row['bg_color'];
		$grp_arr[$key][$row['id']]['text_color'] = $row['text_color'];	
		}
	}
	
$ret_arr[0] = $status_id;
$ret_arr[1] = $grp_arr;
$ret_arr[2] = $icon_str;
print json_encode($ret_arr);
exit();
?>