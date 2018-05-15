<?php
/*
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php'); 
@session_start();
if(isset($_GET['id'])) {
	$addon = " WHERE `id` =" . mysql_real_escape_string($_GET['id']); 
	} else {
	$addon = "";
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
	
	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]waste_basket_m`" . $addon;
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$member_id = $row['old_id'];
	$query2 = "DELETE FROM `$GLOBALS[mysql_prefix]waste_basket_f` WHERE `member_id` =" . mysql_real_escape_string($member_id);
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$wastebasket = "../file_waste/" . $member_id;
	$pic_waste = "../pictures_waste/" . $member_id;
	delete_directory($wastebasket);
	delete_directory($pic_waste);
	}

$query = "DELETE FROM `$GLOBALS[mysql_prefix]waste_basket_m`" . $addon;
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	
if($result) {
	$ret_code = 100;
	} else {
	$ret_code = 99;
	}
print json_encode($ret_code);