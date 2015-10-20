<?php
/*
9/10/13 - New file, retrieves road conditions for mobile page (markers)
*/
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
@session_start();
$ret_arr = array();
$query = "SELECT *,
 	`r`.`id` AS `info_id`,
 	`c`.`id` AS `condition_id`,	
	`r`.`address` AS `address`,
	`r`.`lat` AS `lat`,	
	`r`.`lng` AS `lng`,	
	`r`.`username` AS `username`,	
	`r`.`id` AS `the_id`, 
	`c`.`id` AS `type_id`, 
	`r`.`title` AS `the_title`, 
	`c`.`title` AS `type`, 
	`c`.`icon` AS `icon`,
	`r`.`description` AS `notes`, 
	`c`.`description` AS `the_description` 
	FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
	LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON ( `r`.`conditions` = `c`.`id` )	
	WHERE `r`.`_on` >= (NOW() - INTERVAL 5 DAY)	
	ORDER BY `r`.`id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) > 0) {
	$i=0;
	while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$ret_arr[$i][0] = $row['info_id'];	
		$ret_arr[$i][1] = $row['the_title'];
		$ret_arr[$i][2] = $row['notes'];
		$ret_arr[$i][3] = $row['the_description'];
		$ret_arr[$i][4] = $row['type'];
		$ret_arr[$i][5] = $row['username'];
		$ret_arr[$i][6] = $row['lat'];
		$ret_arr[$i][7] = $row['lng'];		
		$ret_arr[$i][8] = $row['icon'];	
		$ret_arr[$i][9] = $row['address'];			
		$i++;
		}
	}
print json_encode($ret_arr);
exit();
?>


