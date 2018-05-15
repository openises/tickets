<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];
if(!(secure_page($the_session))) {
	exit();
	} else {
	$veh_id = $_GET['veh_id'];

	$ret_arr = array();

	$query = "SELECT *, `v`.`id` AS `id`, 
		`v`.`make` AS `make`, 	
		`v`.`model` AS `model`,
		`v`.`year` AS `year`,
		`v`.`color` AS `color`, 
		`v`.`regno` AS `regno`,
		`v`.`fueltype` AS `fueltype`,
		`v`.`seats` AS `seats`,
		`v`.`roofrack` AS `roofrack`,	
		`v`.`towbar` AS `towbar`,	
		`v`.`winch` AS `winch`,		
		`v`.`trailer` AS `trailer`,
		`v`.`notes` AS `notes`,		
		`t`.`name` AS `type_name`,
		`t`.`description` AS `type_description`,
		`m`.`field4` AS `vehicle_owner`		
		FROM `$GLOBALS[mysql_prefix]vehicles` `v` 
		LEFT JOIN `$GLOBALS[mysql_prefix]vehicle_types` `t` ON ( `v`.`type` = `t`.`id` )	
		LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `v`.`owner` = `m`.`id` ) 		
		WHERE `v`.`id` = {$veh_id};";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	
			$ret_arr[] = $row['vehicle_owner'];		
			$ret_arr[] = $row['make'];
			$ret_arr[] = $row['model'];
			$ret_arr[] = $row['year'];
			$ret_arr[] = $row['color'];
			$ret_arr[] = $row['regno'];
			$ret_arr[] = $row['type_name'];
			$ret_arr[] = $row['fueltype'];
			$ret_arr[] = $row['seats'];
			$ret_arr[] = $row['roofrack'];		
			$ret_arr[] = $row['towbar'];		
			$ret_arr[] = $row['winch'];		
			$ret_arr[] = $row['trailer'];
			$ret_arr[] = $row['notes'];
			}
	
	print json_encode($ret_arr);
}	