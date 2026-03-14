<?php
/*
9/10/13 - New file, submits a new road condition alert from mobile screen
*/
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];


function get_user_name($the_id) {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user` `u` WHERE `id` = ? LIMIT 1";
	$result = db_query($query, [$the_id]);
	if($result->num_rows == 1) {
		$row = stripslashes_deep($result->fetch_assoc());
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		}
	return $the_ret;
	}
$ret_arr = array();
$from = $_SERVER['REMOTE_ADDR'];
$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;
$whom = (array_key_exists('user_id', $_SESSION))? get_user_name($_SESSION['user_id']): "Public";
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$title = sanitize_string($_GET['title']);
$type = sanitize_int($_GET['type']);
$address = sanitize_string($_GET['address']);
$lat = sanitize_string($_GET['lat']);
$lng = sanitize_string($_GET['lng']);

$query_cond = "SELECT * FROM `{$GLOBALS['mysql_prefix']}conditions` WHERE `id` = ? LIMIT 1";
$result_cond = db_query($query_cond, [$type]);
$row_cond = stripslashes_deep($result_cond->fetch_assoc());
$the_description = $row_cond['description'];

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}roadinfo` WHERE `lat` = ? AND `lng` = ?";
$result = db_query($query, [$lat, $lng]);
if($result->num_rows > 0) {
	$query_del = "DELETE FROM `{$GLOBALS['mysql_prefix']}roadinfo` WHERE `lat` = ? AND `lng` = ?";
	$result_del = db_query($query_del, [$lat, $lng]);
	}

$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}roadinfo` (
	`title`,
	`description`,
	`address`,
	`conditions`,
	`lat`,
	`lng`,
	`username`,
	`_by`,
	`_on`,
	`_from` )
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$result = db_query($query, [
	trim($title),
	trim($the_description),
	trim($address),
	trim($type),
	trim($lat),
	trim($lng),
	trim($whom),
	trim($who),
	trim($now),
	trim($from)
]);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}

print json_encode($ret_arr);
exit();
?>
