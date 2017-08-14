<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$istest = FALSE;
$output_arr = array();
$num_rows = 0;


@session_start();		// 
// initiate arrays
$ticket_row = array();

//	User Groups

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {		//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
	
//	Set regions applicable for user

if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
	$where2 = " AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";
	} else {		
	if(!isset($curr_viewed)) {			//	6/10/11
		$x=0;	
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;	
		$where2 = "AND (";	
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		}
	$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";	
	}

$interval = get_variable('hide_booked');
	
$where = "WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `$GLOBALS[mysql_prefix]ticket`.`booked_date` >= (NOW() + INTERVAL " . $interval . " HOUR)) {$where2}";	//	11/29/10, 4/18/11, 4/18/11

$query = "SELECT *,problemstart AS problemstart,
	`problemend` AS `problemend`,
	`booked_date` AS `booked_date`,	
	`date` AS `date`, 
	`$GLOBALS[mysql_prefix]ticket`.`scope` AS scope, 
	`$GLOBALS[mysql_prefix]ticket`.`street` AS ticket_street, 
	`$GLOBALS[mysql_prefix]ticket`.`state` AS ticket_city, 
	`$GLOBALS[mysql_prefix]ticket`.`city` AS ticket_state,
	`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,
	`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
	`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, 
	`$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
	`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`
	FROM `$GLOBALS[mysql_prefix]ticket` 
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
		ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
	$where 
	GROUP BY tick_id";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_rows = mysql_num_rows($result);

if($num_rows == 0) {
	$ticket_row[0] = 0;
	} else {
	$ticket_row[0] = $num_rows;		
	}				// end tickets while ($row = ...)

print json_encode($ticket_row);
exit();
?>