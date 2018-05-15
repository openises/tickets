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
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]waste_basket_m` WHERE `id` =" . mysql_real_escape_string($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$old_filename = (isset($row['field5'])) ? $row['field5'] : "";
$old_id = $row['old_id'];
$training = explode(",", $row['training']);
$capabilities = explode(",", $row['capabilities']);
$equipment = explode(",", $row['equipment']);
$vehicles = explode(",", $row['vehicles']);
$clothing = explode(",", $row['clothing']);	
$query = "INSERT INTO `$GLOBALS[mysql_prefix]member` 
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
	VALUES (" . 
		quote_smart(trim($row['field1'])) . "," .
		quote_smart(trim($row['field2'])) . "," .
		quote_smart(trim($row['field3'])) . "," .
		quote_smart(trim($row['field4'])) . "," .	
		quote_smart(trim($row['field5'])) . "," .
		quote_smart(trim($row['field6'])) . "," .
		quote_smart(trim($row['field7'])) . "," .		
		quote_smart(trim($row['field8'])) . "," .		
		quote_smart(trim($row['field9'])) . "," .	
		quote_smart(trim($row['field10'])) . "," .				
		quote_smart(trim($row['field11'])) . "," .	
		quote_smart(trim($row['field12'])) . "," .					
		quote_smart(trim($row['field13'])) . "," .
		quote_smart(trim($row['field14'])) . "," .	
		quote_smart(trim($row['field15'])) . "," .					
		quote_smart(trim($row['field16'])) . "," .						
		quote_smart(trim($row['field17'])) . "," .	
		quote_smart(trim($row['field18'])) . "," .
		quote_smart(trim($row['field19'])) . "," .
		quote_smart(trim($row['field20'])) . "," .	
		quote_smart(trim($row['field21'])) . "," .
		quote_smart(trim($row['field22'])) . "," .
		quote_smart(trim($row['field23'])) . "," .		
		quote_smart(trim($row['field24'])) . "," .		
		quote_smart(trim($row['field25'])) . "," .	
		quote_smart(trim($row['field26'])) . "," .				
		quote_smart(trim($row['field27'])) . "," .	
		quote_smart(trim($row['field28'])) . "," .					
		quote_smart(trim($row['field29'])) . "," .
		quote_smart(trim($row['field30'])) . "," .	
		quote_smart(trim($row['field31'])) . "," .	
		quote_smart(trim($row['field32'])) . "," .	
		quote_smart(trim($row['field33'])) . "," .	
		quote_smart(trim($row['field34'])) . "," .	
		quote_smart(trim($row['field35'])) . "," .	
		quote_smart(trim($row['field36'])) . "," .	
		quote_smart(trim($row['field37'])) . "," .	
		quote_smart(trim($row['field38'])) . "," .	
		quote_smart(trim($row['field39'])) . "," .	
		quote_smart(trim($row['field40'])) . "," .	
		quote_smart(trim($row['field41'])) . "," .	
		quote_smart(trim($row['field42'])) . "," .	
		quote_smart(trim($row['field43'])) . "," .	
		quote_smart(trim($row['field44'])) . "," .	
		quote_smart(trim($row['field45'])) . "," .						
		quote_smart(trim($row['field46'])) . "," .	
		quote_smart(trim($row['field47'])) . "," .
		quote_smart(trim($row['field48'])) . "," .	
		quote_smart(trim($row['field49'])) . "," .	
		quote_smart(trim($row['field50'])) . "," .	
		quote_smart(trim($row['field51'])) . "," .	
		quote_smart(trim($row['field52'])) . "," .	
		quote_smart(trim($row['field53'])) . "," .	
		quote_smart(trim($row['field54'])) . "," .	
		quote_smart(trim($row['field55'])) . "," .	
		quote_smart(trim($who)) . "," .	
		quote_smart(trim($now)) . "," .					
		quote_smart(trim($from)) . ");";								// 8/23/08

$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	
$new_id = mysql_insert_id();

if($old_filename != "") {
	$filename = "./pictures/" . $new_id . "/id.jpg";
	} else {
	$filename = "";
	}
	
$query = "UPDATE `$GLOBALS[mysql_prefix]member` SET
	`field5`= " . 		quote_smart(trim($filename)) . "		
	WHERE `id`= " . 	quote_smart(trim(mysql_real_escape_string($new_id))) . ";";	
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	

if(count($training != 0)) {
	foreach($training AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocations` (
				`member_id`, `skill_type`, `skill_id`, `completed`, `refresh_due`, `_on` )
				VALUES (" .
					quote_smart(trim($new_id)) . "," .
					1 . "," .
					quote_smart(trim($val)) . "," .	
					quote_smart(trim($now)) . "," .		
					quote_smart(trim($now)) . "," .	
					quote_smart(trim($now)) . ");";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}
		}
	}
if(count($capabilities != 0)) {			
	foreach($capabilities AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (" .
					quote_smart(trim($new_id)) . "," .
					2 . "," .
					quote_smart(trim($val)) . "," .	
					quote_smart(trim($now)) . ");";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}
		}
	}
if(count($equipment != 0)) {			
	foreach($equipment AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (" .
					quote_smart(trim($new_id)) . "," .
					3 . "," .
					quote_smart(trim($val)) . "," .	
					quote_smart(trim($now)) . ");";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}	
		}
	}
if(count($vehicles != 0)) {
	foreach($vehicles AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (" .
					quote_smart(trim($new_id)) . "," .
					4 . "," .
					quote_smart(trim($val)) . "," .	
					quote_smart(trim($now)) . ");";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}	
		}
	}
if(count($clothing != 0)) {
	foreach($clothing AS $val) {
		if($val != 0) {
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (" .
					quote_smart(trim($new_id)) . "," .
					5 . "," .
					quote_smart(trim($val)) . "," .	
					quote_smart(trim($now)) . ");";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}	
		}
	}
		
$query = "DELETE FROM `$GLOBALS[mysql_prefix]waste_basket_m` WHERE `id` =" . mysql_real_escape_string($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]waste_basket_f` WHERE `member_id` = " . mysql_real_escape_string($old_id);
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$oldname = explode("/",$row['name']);
	$filename = "./files/" . $new_id . "/" . $oldname[3];
	$query2 = "INSERT INTO `$GLOBALS[mysql_prefix]files` 
			(`member_id`, 
			`name`, 
			`shortname`, 
			`description`, 
			`_on`)
		VALUES (" . 
			quote_smart(trim($new_id)) . "," .
			quote_smart(trim($filename)) . "," .
			quote_smart(trim($row['shortname'])) . "," .	
			quote_smart(trim($row['description'])) . "," .			
			quote_smart(trim($now)) . ");";		
	$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		
	}
	
$query = "DELETE FROM $GLOBALS[mysql_prefix]waste_basket_f WHERE `member_id`=" . mysql_real_escape_string($old_id);
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

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