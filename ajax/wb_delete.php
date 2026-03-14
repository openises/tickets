<?php
/*
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
@session_start();
if(isset($_GET['id'])) {
	$addon = " WHERE `id` = ?";
	$addon_params = [['type' => 'i', 'value' => sanitize_int($_GET['id'])]];
	} else {
	$addon = "";
	$addon_params = [];
	}

function delete_directory($dirname) {
	if (is_dir($dirname)) {
		$dir_handle = opendir($dirname);
		} else {
		return false;
		}
	if (!$dir_handle)
		return false;
	while($file = readdir($dir_handle)) {
		if ($file != "." && $file != "..") {
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delete_directory($dirname.'/'.$file);
		}
	}
	closedir($dir_handle);
	rmdir($dirname);
	return true;
	}


$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}waste_basket_m`" . $addon;
$result = db_query($query, $addon_params);
while($row = $result->fetch_assoc()) {
	$member_id = $row['old_id'];
	$query2 = "DELETE FROM `{$GLOBALS['mysql_prefix']}waste_basket_f` WHERE `member_id` = ?";
	$result2 = db_query($query2, [['type' => 'i', 'value' => intval($member_id)]]);
	$wastebasket = "../file_waste/" . $member_id;
	$pic_waste = "../pictures_waste/" . $member_id;
	delete_directory($wastebasket);
	delete_directory($pic_waste);
	}

$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}waste_basket_m`" . $addon;
$result = db_query($query, $addon_params);

if($result) {
	$ret_code = 100;
	} else {
	$ret_code = 99;
	}
print json_encode($ret_code);