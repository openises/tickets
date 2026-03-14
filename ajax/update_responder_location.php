<?php
/*
update_responder_status.php - used by fac_routes.php to change location of responder to a facility and add facility located at to responder table field "at_facility"
09/03/15 - new file
*/
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$istest = FALSE;

$fac_id = sanitize_int($_GET['fac_id']);
$resp_id = sanitize_int($_GET['resp_id']);
$new_status = sanitize_int($_GET['status']);
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$resp_id]);
$row = $result->fetch_assoc();
$existing_status = $row['un_status_id'];
$response = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}facilities` WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$fac_id]);
if($result->num_rows >= 1) {
	while ($row = $result->fetch_assoc()) {
		$lat = $row['lat'];
		$lng = $row['lng'];
		}
	}

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
if($new_status == $existing_status) {
	$query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
		`lat`= ?,
		`lng`= ?,
		`at_facility`= ?,
		`user_id`= ?,
		`updated`= ?
		WHERE `id`= ?";
	$params = [
		$lat,
		$lng,
		$fac_id,
		$_SESSION['user_id'],
		$now,
		$resp_id
	];
	} else {
	$query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
		`lat`= ?,
		`lng`= ?,
		`un_status_id`= ?,
		`at_facility`= ?,
		`user_id`= ?,
		`updated`= ?,
		`status_updated`= ?
		WHERE `id`= ?";
	$params = [
		$lat,
		$lng,
		$new_status,
		$fac_id,
		$_SESSION['user_id'],
		$now,
		$now,
		$resp_id
	];
	}
$result = db_query($query, $params);
if(db_affected_rows() == 1) {
	$response[0] = 1;
	} else {
	$response[0] = 0;
	}

print json_encode($response);
exit();
?>