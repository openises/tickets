<?php
/*
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
$memberid = $_GET['member_id'];
$responder_id = (array_key_exists('responder_id', $_GET)) ? $_GET['responder_id'] : 0;
$searchstring = (array_key_exists('searchstring', $_GET)) ? $_GET['searchstring'] : "";
$func = (array_key_exists('func', $_GET)) ? intval($_GET['func']) : 0;

dump($_GET);

function get_capabilities($member_id) {
	$output = "";

	$query = "SELECT
		`tp`.`package_name` AS `training_package_name`,
		`a`.`completed` AS `completed`,
		`a`.`refresh_due` AS `refresh_due`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` `tp` ON ( `a`.`skill_id` = `tp`.`id` ) 	
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 1 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['training_package_name'];
			$completed = $row['completed'];
			$refresh_due = $row['refresh_due'];
			$output .= $value . " - Completed: " . $completed . ", Due: " . $refresh_due . "<BR />";
			}
		}
	
	$query = "SELECT
		`ct`.`name` AS `capability_name`
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ct` ON ( `a`.`skill_id` = `ct`.`id` ) 
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 2 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['capability_name'];
			$output .= $value . "<BR />";
			}
		}
		
	$query = "SELECT
		`et`.`equipment_name` AS `equipment_name`,	
		`et`.`serial` AS `equipment_serial`	
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]equipment_types` `et` ON ( `a`.`skill_id` = `et`.`id` ) 		
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 3 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['equipment_name']. " - " . $row['equipment_serial'];
			$output .= $value . "<BR />";
			}
		}
		
	$query = "SELECT
		`cl`.`clothing_item` AS `clothing_item`,
		`cl`.`size` AS `size`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]clothing_types` `cl` ON ( `a`.`skill_id` = `cl`.`id` ) 
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 5 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['clothing_item']. " - " . $row['size'];
			$output .= $value . "<BR />";
			}	
		}
	return $output;
	}
	
function search_capabilities($member_id, $searchstring) {
	$theArray =  array();
	$key = 1;
		
	$query = "SELECT
		`tp`.`package_name` AS `training_package_name`,
		`a`.`completed` AS `completed`,
		`a`.`refresh_due` AS `refresh_due`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` `tp` ON ( `a`.`skill_id` = `tp`.`id` ) 	
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 1 AND `refresh_due` > NOW() ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['training_package_name'] . " | " . $row['refresh_due'];
			$theArray[$key] = $value;
			$key++;
			}
		}
	
	$query = "SELECT
		`ct`.`name` AS `capability_name`
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ct` ON ( `a`.`skill_id` = `ct`.`id` ) 
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 2 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['capability_name'];
			$theArray[$key] = $value;
			$key++;
			}
		}
		
	$query = "SELECT
		`et`.`equipment_name` AS `equipment_name`,	
		`et`.`serial` AS `equipment_serial`	
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]equipment_types` `et` ON ( `a`.`skill_id` = `et`.`id` ) 		
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 3 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['equipment_name']. " - " . $row['equipment_serial'];
			$theArray[$key] = $value;
			$key++;
			}
		}
		
	$query = "SELECT
		`cl`.`clothing_item` AS `clothing_item`,
		`cl`.`size` AS `size`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]clothing_types` `cl` ON ( `a`.`skill_id` = `cl`.`id` ) 
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 5 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['clothing_item']. " - " . $row['size'];
			$theArray[$key] = $value;
			$key++;
			}	
		}
	$counter = 0;
	foreach ($theArray AS $k => $v) {
		if(stripos($v, $searchstring) !== FALSE){
			$counter++;
			}
		}
	$theReturn = ($counter > 0) ? TRUE : FALSE;
	return $theReturn;
	}
	
function search_resp_capabilities($responder_id, $searchstring) {
	$theArray =  array();
	$key = 1;
	
	$query = "SELECT `capab` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $responder_id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['capab'];
			$theArray[$key] = $value;
			$key++;
			}
		}
		
	$counter = 0;
	foreach ($theArray AS $k => $v) {
		if(stripos($v, $searchstring) !== FALSE){
			$counter++;
			}
		}
	$theReturn = ($counter > 0) ? TRUE : FALSE;
	return $theReturn;
	}

switch($func) {
	case 0:
	$return = get_capabilities($memberid);
	break;
	
	case 1:
	$return = search_capabilities($memberid, $searchstring);
	$return .= ($responder_id != 0) ? search_resp_capabilities($responder_id, $searchstring) : "";
	break;
	}
$ret_arr = array();
$ret_arr[0] = $func;
$ret_arr[1] = $return;
print json_encode($ret_arr);