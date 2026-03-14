<?php
/*
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
@session_start();

$training = array();
$capabilities = array();
$equipment = array();
$vehicles = array();
$clothing = array();
$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;
$from = $_SERVER['REMOTE_ADDR'];
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}waste_basket_m` WHERE `id` = ?";
$result = db_query($query, [['type' => 'i', 'value' => sanitize_int($_GET['id'])]]);
$row = $result->fetch_assoc();
$old_filename = (isset($row['field5'])) ? $row['field5'] : "";
$old_id = $row['old_id'];
$training = explode(",", $row['training']);
$capabilities = explode(",", $row['capabilities']);
$equipment = explode(",", $row['equipment']);
$vehicles = explode(",", $row['vehicles']);
$clothing = explode(",", $row['clothing']);
$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}member`
		(`field1`,
		`field2`,
		`field3`,
		`field4`,
		`field5`,
		`field6`,
		`field7`,
		`field8`,
		`field9`,
		`field10`,
		`field11`,
		`field12`,
		`field13`,
		`field14`,
		`field15`,
		`field16`,
		`field17`,
		`field18`,
		`field19`,
		`field20`,
		`field21`,
		`field22`,
		`field23`,
		`field24`,
		`field25`,
		`field26`,
		`field27`,
		`field28`,
		`field29`,
		`field30`,
		`field31`,
		`field32`,
		`field33`,
		`field34`,
		`field35`,
		`field36`,
		`field37`,
		`field38`,
		`field39`,
		`field40`,
		`field41`,
		`field42`,
		`field43`,
		`field44`,
		`field45`,
		`field46`,
		`field47`,
		`field48`,
		`field49`,
		`field50`,
		`field51`,
		`field52`,
		`field53`,
		`field54`,
		`field55`,
		`_by`,
		`_on`,
		`_from` )
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$params = [];
for ($i = 1; $i <= 55; $i++) {
	$params[] = ['type' => 's', 'value' => trim($row['field' . $i])];
}
$params[] = ['type' => 'i', 'value' => intval($who)];
$params[] = ['type' => 's', 'value' => trim($now)];
$params[] = ['type' => 's', 'value' => trim($from)];

$result = db_query($query, $params);
$new_id = db_insert_id();

if($old_filename != "") {
	$filename = "./pictures/" . $new_id . "/id.jpg";
	} else {
	$filename = "";
	}

$query = "UPDATE `{$GLOBALS['mysql_prefix']}member` SET
	`field5`= ?
	WHERE `id`= ?";
$result = db_query($query, [
	['type' => 's', 'value' => trim($filename)],
	['type' => 'i', 'value' => $new_id]
]);

if(count($training != 0)) {
	foreach($training AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `completed`, `refresh_due`, `_on` )
				VALUES (?, ?, ?, ?, ?, ?)";
			$result = db_query($query, [
				['type' => 'i', 'value' => $new_id],
				['type' => 'i', 'value' => 1],
				['type' => 'i', 'value' => intval(trim($val))],
				['type' => 's', 'value' => trim($now)],
				['type' => 's', 'value' => trim($now)],
				['type' => 's', 'value' => trim($now)]
			]);
			}
		}
	}
if(count($capabilities != 0)) {
	foreach($capabilities AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (?, ?, ?, ?)";
			$result = db_query($query, [
				['type' => 'i', 'value' => $new_id],
				['type' => 'i', 'value' => 2],
				['type' => 'i', 'value' => intval(trim($val))],
				['type' => 's', 'value' => trim($now)]
			]);
			}
		}
	}
if(count($equipment != 0)) {
	foreach($equipment AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (?, ?, ?, ?)";
			$result = db_query($query, [
				['type' => 'i', 'value' => $new_id],
				['type' => 'i', 'value' => 3],
				['type' => 'i', 'value' => intval(trim($val))],
				['type' => 's', 'value' => trim($now)]
			]);
			}
		}
	}
if(count($vehicles != 0)) {
	foreach($vehicles AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (?, ?, ?, ?)";
			$result = db_query($query, [
				['type' => 'i', 'value' => $new_id],
				['type' => 'i', 'value' => 4],
				['type' => 'i', 'value' => intval(trim($val))],
				['type' => 's', 'value' => trim($now)]
			]);
			}
		}
	}
if(count($clothing != 0)) {
	foreach($clothing AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (?, ?, ?, ?)";
			$result = db_query($query, [
				['type' => 'i', 'value' => $new_id],
				['type' => 'i', 'value' => 5],
				['type' => 'i', 'value' => intval(trim($val))],
				['type' => 's', 'value' => trim($now)]
			]);
			}
		}
	}

$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}waste_basket_m` WHERE `id` = ?";
$result = db_query($query, [['type' => 'i', 'value' => sanitize_int($_GET['id'])]]);

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}waste_basket_f` WHERE `member_id` = ?";
$result = db_query($query, [['type' => 'i', 'value' => intval($old_id)]]);
while ($row = $result->fetch_assoc()) {
	$oldname = explode("/",$row['name']);
	$filename = "./files/" . $new_id . "/" . $oldname[3];
	$query2 = "INSERT INTO `{$GLOBALS['mysql_prefix']}files`
			(`member_id`,
			`name`,
			`shortname`,
			`description`,
			`_on`)
		VALUES (?, ?, ?, ?, ?)";
	$result2 = db_query($query2, [
		['type' => 'i', 'value' => $new_id],
		['type' => 's', 'value' => trim($filename)],
		['type' => 's', 'value' => trim($row['shortname'])],
		['type' => 's', 'value' => trim($row['description'])],
		['type' => 's', 'value' => trim($now)]
	]);
	}

$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}waste_basket_f` WHERE `member_id` = ?";
$result = db_query($query, [['type' => 'i', 'value' => intval($old_id)]]);

$files_directory = "../files/" . $new_id;
$files_wastebasket = "../file_waste/" . $old_id;
$pictures_directory = "../pictures/" . $new_id;
$pictures_wastebasket = "../pictures_waste/" . $old_id;

if(file_exists($files_wastebasket)) {
	rename ($files_wastebasket, $files_directory);
	}

if(file_exists($pictures_wastebasket)) {
	rename ($pictures_wastebasket, $pictures_directory);
	}

if($result) {
	$ret_code = 100;
	} else {
	$ret_code = 99;
	}
print json_encode($ret_code);