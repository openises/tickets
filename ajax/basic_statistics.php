<?php
#
# statistics.php - Management Statistics from Tickets.
#
/*
6/14/11	First version
*/
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = $user_id ORDER BY `id` ASC;";	//	6/10/11
$result = mysql_query($query);	//	6/10/11
$al_names = "";	
$a_gp_bounds = array();	
$al_groups = $_SESSION['user_groups'];

if(count($al_groups) > 0) {
	$x=0;	
	$where2 = "AND (";
	foreach($al_groups as $grp) {
		$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	} else {
	$where2 = "";
	}

//-----------------Functions-----------------
function datediff($value1,$value2) {   
	$diff = $value1-$value2;  
	return $diff;
	}

function parsedate($diff){	
	$seconds = 0;   
	$hours   = 0;   
	$minutes = 0;   

	if($diff % 86400 <= 0){$days = $diff / 86400;}  // 86,400 seconds in a day   
	if($diff % 86400 > 0)   
	{   
		$rest = ($diff % 86400);   
		$days = ($diff - $rest) / 86400;   
		if($rest % 3600 > 0)   
		{   
			$rest1 = ($rest % 3600);   
			$hours = ($rest - $rest1) / 3600;   
			if($rest1 % 60 > 0)   
			{   
				$rest2 = ($rest1 % 60);   
			$minutes = ($rest1 - $rest2) / 60;   
			$seconds = $rest2;   
			}   
			else{$minutes = $rest1 / 60;}   
		}   
		else{$hours = $rest / 3600;}   
	}   

	if($days > 0){$days = floor($days).' Days: ';}   
	else{$days = false;}   
	if($hours > 0){$hours = $hours.' Hours: ';}   
	else{$hours = false;}   
	if($minutes > 0){$minutes = $minutes.' Minutes: ';}   
	else{$minutes = false;}   
	$seconds = $seconds.' Seconds'; // always be at least one second   

	return $days.''.$hours.''.$minutes.''.$seconds;   
}	

//-----------------end of Functions-----------------

// Queries
$multi=array();
$time_running = array();
$duration = array();
$start_dates = array();
$disp_list = array();
$f_disp_list = array();
$min_disp = array();
$os_list = array();
$tick_id = array();
$resp_ids_na = array();
$n=0;
$x=0;
$xx=0;
$y=0;
$z=0;
$r=0;
$fr=0;

// QUERIES

// Number of Tickets
$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id` FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
		WHERE (`status` = 2 or `status` = 3) $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_tick = mysql_num_rows($result);

//	Number of Tickets not assigned
$query = "SELECT *,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemstart`) AS problemstart,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemend`) AS problemend,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`booked_date`) AS booked_date,	
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`date`) AS date, 
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.updated) AS updated,
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
		`$GLOBALS[mysql_prefix]in_types`.type AS `type`, 
		`$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
		`$GLOBALS[mysql_prefix]ticket`.`status` AS `status`,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `as_id`,	
		`$GLOBALS[mysql_prefix]assigns`.`dispatched` AS `as_dispatched`,			
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`  
			AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))) 
			AS `units_assigned`,
		(SELECT  COUNT(*) as numfound2 FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AND ((`$GLOBALS[mysql_prefix]assigns`.`dispatched` IS NOT NULL) OR (DATE_FORMAT(`dispatched`,'%y') != '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`responding` IS NULL) OR (DATE_FORMAT(`responding`,'%y') = '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`on_scene` IS NULL) OR (DATE_FORMAT(`on_scene`,'%y') = '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))) 
			AS `units_assigned_nr`,	
		(SELECT  COUNT(*) as numfound3 FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AND ((`$GLOBALS[mysql_prefix]assigns`.`dispatched` IS NOT NULL) OR (DATE_FORMAT(`dispatched`,'%y') != '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`responding` IS NOT NULL) OR (DATE_FORMAT(`responding`,'%y') != '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`on_scene` IS NULL) OR (DATE_FORMAT(`on_scene`,'%y') = '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))) 
			AS `units_assigned_no`,	
		(SELECT  COUNT(*) as numfound4 FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AND ((`$GLOBALS[mysql_prefix]assigns`.`dispatched` IS NOT NULL) OR (DATE_FORMAT(`dispatched`,'%y') != '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`responding` IS NOT NULL) OR (DATE_FORMAT(`responding`,'%y') != '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`on_scene` IS NOT NULL) OR (DATE_FORMAT(`on_scene`,'%y') != '00')) AND ((`$GLOBALS[mysql_prefix]assigns`.`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))) 
			AS `units_assigned_os`				
		FROM `$GLOBALS[mysql_prefix]ticket`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]assigns`.`ticket_id`		
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` 
			ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			WHERE (`status` = 2 or `status` = 3) $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY tick_id";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	$tick_ids[] = $row['tick_id'];
	$startdate = $row['problemstart'];
	$time_running[$row['tick_id']] = datediff(time(), $startdate);
	$start_dates[$row['tick_id']] = date('r', $row['problemstart']);
	$tick_id = $row['tick_id'];
	if($row['units_assigned'] == 0) {
		$n++;
		}	
	}
	
//	Number of Tickets closed
$query = "SELECT *,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemstart`) AS problemstart,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemend`) AS problemend,
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
		`$GLOBALS[mysql_prefix]ticket`.`status` AS `status`
		FROM `$GLOBALS[mysql_prefix]ticket`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			WHERE (`status` = 1) $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	$tick_ids[] = $row['tick_id'];
	$startdate = $row['problemstart'];
	$enddate = $row['problemend'];	
	$time_toclose[$row['tick_id']] = datediff($enddate, $startdate);	
	$tick_id = $row['tick_id'];
	}	

// Number of responders dispatched not responding	
$query = "SELECT `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`, 
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `ass_id`,
		`$GLOBALS[mysql_prefix]assigns`.`responder_id` AS `resp_id`,			
		`$GLOBALS[mysql_prefix]assigns`.`dispatched` AS `dispatched`,	
		`$GLOBALS[mysql_prefix]assigns`.`responding` AS `responding`,
		`$GLOBALS[mysql_prefix]assigns`.`on_scene` AS `on_scene`			
		FROM `$GLOBALS[mysql_prefix]ticket`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]assigns`.`ticket_id`	
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			WHERE ((`status` = 2) AND (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AND (`$GLOBALS[mysql_prefix]assigns`.`dispatched` IS NOT NULL OR DATE_FORMAT(`dispatched`,'%y') != '00') AND (`$GLOBALS[mysql_prefix]assigns`.`responding` IS NULL OR DATE_FORMAT(`responding`,'%y') = '00') AND (`$GLOBALS[mysql_prefix]assigns`.`on_scene` IS NULL OR DATE_FORMAT(`on_scene`,'%y') = '00') AND (`$GLOBALS[mysql_prefix]assigns`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') $where2 $where2 AND (`$GLOBALS[mysql_prefix]allocates`.`type` = 1)) GROUP BY `resp_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
$y = mysql_num_rows($result);
	
//	Number of Responders dispatched and responding not on scene
$query = "SELECT `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `ass_id`,
		`$GLOBALS[mysql_prefix]assigns`.`responder_id` AS `resp_id`,		
		`$GLOBALS[mysql_prefix]assigns`.`dispatched` AS `dispatched`,	
		`$GLOBALS[mysql_prefix]assigns`.`responding` AS `responding`,
		`$GLOBALS[mysql_prefix]assigns`.`on_scene` AS `on_scene`			
		FROM `$GLOBALS[mysql_prefix]ticket`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]assigns`.`ticket_id`	
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			WHERE ((`status` = 2) AND (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AND (`$GLOBALS[mysql_prefix]assigns`.`dispatched` IS NOT NULL OR DATE_FORMAT(`dispatched`,'%y') != '00') AND (`$GLOBALS[mysql_prefix]assigns`.`responding` IS NOT NULL OR DATE_FORMAT(`responding`,'%y') != '00') AND (`$GLOBALS[mysql_prefix]assigns`.`on_scene` IS NULL OR DATE_FORMAT(`on_scene`,'%y') = '00') AND (`$GLOBALS[mysql_prefix]assigns`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') $where2 $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1) GROUP BY `resp_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$z = mysql_num_rows($result);
	
//	Number of Responders dispatched, responding and on scene
$query = "SELECT `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `ass_id`,
		`$GLOBALS[mysql_prefix]assigns`.`responder_id` AS `resp_id`,			
		`$GLOBALS[mysql_prefix]assigns`.`dispatched` AS `dispatched`,	
		`$GLOBALS[mysql_prefix]assigns`.`responding` AS `responding`,
		`$GLOBALS[mysql_prefix]assigns`.`on_scene` AS `on_scene`			
		FROM `$GLOBALS[mysql_prefix]ticket`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]assigns`.`ticket_id`	
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			WHERE ((`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AND (`$GLOBALS[mysql_prefix]assigns`.`dispatched` IS NOT NULL OR DATE_FORMAT(`dispatched`,'%y') != '00') AND (`$GLOBALS[mysql_prefix]assigns`.`responding` IS NOT NULL OR DATE_FORMAT(`responding`,'%y') != '00') AND (`$GLOBALS[mysql_prefix]assigns`.`on_scene` IS NOT NULL OR DATE_FORMAT(`on_scene`,'%y') != '00') AND (`$GLOBALS[mysql_prefix]assigns`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') $where2 $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1) GROUP BY `resp_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$r = mysql_num_rows($result);
	
// 	Average Time to Dispatch from ticket open
$query = "SELECT *, 
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemstart`) as `problemstart`,
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		WHERE (`status` = 2 or `status` = 3) $where2 $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	$tick_id = $row['tick_id'];
	$problemstart = $row['problemstart'];
	$query_01 = "SELECT *,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`dispatched`) as `dispatched`
				FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `ticket_id` = $tick_id AND (`dispatched` IS NOT NULL OR DATE_FORMAT(`dispatched`,'%y') != '00')";
	$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result_01) > 0) {
		while ($row_01 = mysql_fetch_assoc($result_01)) {
			$disptime = $row_01['dispatched'];
			$disp_list[$row['id']] = datediff($disptime,$problemstart);
			}
		$min_disp[] = min($disp_list);
		} else {
		$min_disp[] = 0;
		}
	}	
	
// 	Average Time to first dispatch from ticket open
$query = "SELECT *, 
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemstart`) as `problemstart`,
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		WHERE (`status` = 2 or `status` = 3) $where2 $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	$f_tick_id = $row['tick_id'];
	$f_problemstart = $row['problemstart'];
	$query_01 = "SELECT *,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`dispatched`) as `dispatched`
				FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `ticket_id` = $f_tick_id AND (`dispatched` IS NOT NULL OR DATE_FORMAT(`dispatched`,'%y') != '00')";
	$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result_01) > 0) {
		while ($row_01 = mysql_fetch_assoc($result_01)) {
			$f_disptime = $row_01['dispatched'];
			$f_disp_list[$row['id']] = datediff($f_disptime,$f_problemstart);
			}
		$min_disp[] = min($f_disp_list);
		} else {
		$min_disp[] = 0;
		}		
	}		
	
// 	Average Time Dispatched to Responding
$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id` 
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		WHERE (`status` = 2 or `status` = 3) $where2 $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	$tick_id = $row['tick_id'];
	$query_01 = "SELECT *,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`dispatched`) as `dispatched`,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`responding`) as `responding`				
				FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `ticket_id` = $tick_id AND (`responding` IS NOT NULL OR DATE_FORMAT(`responding`,'%y') != '00')";
	$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_01 = mysql_fetch_assoc($result_01)) {
		$disptime = $row_01['dispatched'];
		$resptime = $row_01['responding'];		
		$resp_list[$row_01['id']] = datediff($resptime, $disptime);
		}
	}
//	Average time Dispatched to On Scene	
$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		WHERE (`status` = 2 or `status` = 3) $where2 $where2 AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_tick3 = mysql_num_rows($result);
while ($row = mysql_fetch_assoc($result)) {
	$tick_id = $row['tick_id'];
	$query_01 = "SELECT *,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`dispatched`) as `dispatched`,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`responding`) as `responding`,				
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`on_scene`) as `on_scene`				
				FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `ticket_id` = $tick_id AND (`on_scene` IS NOT NULL OR DATE_FORMAT(`on_scene`,'%y') != '00')";
	$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_01 = mysql_fetch_assoc($result_01)) {
		$disptime = $row_01['dispatched'];
		$ostime = $row_01['on_scene'];			
		$os_list[$row_01['id']] = datediff($ostime, $disptime);		
		}
	}
// Number of responders not assigned and Available

if(count($al_groups) > 0) {
	$x=0;	
	$where2 = "WHERE (";
	foreach($al_groups as $grp) {
		$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	$where2 .= " AND ";
	} else {
	$where2 = "WHERE ";
	}

$query = "SELECT *,	`$GLOBALS[mysql_prefix]responder`.`id` AS `resp_id`,
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `$GLOBALS[mysql_prefix]responder`.`id`  
			AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
			AS `num_assignments`
		FROM `$GLOBALS[mysql_prefix]responder`			
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]responder`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`	
		 $where2 `$GLOBALS[mysql_prefix]allocates`.`type` = 2 GROUP BY `$GLOBALS[mysql_prefix]responder`.`id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	if($row['num_assignments'] == 0) {
		$resp_ids_na[] = $row['resp_id'];
		}
	}

	foreach($resp_ids_na as $value) {
		$query= "SELECT * FROM `$GLOBALS[mysql_prefix]responder`
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` ON `$GLOBALS[mysql_prefix]responder`.`un_status_id` = `$GLOBALS[mysql_prefix]un_status`.`id`
				WHERE `$GLOBALS[mysql_prefix]responder`.`id` = {$value} AND `$GLOBALS[mysql_prefix]un_status`.`hide` = 'n'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_rows = mysql_num_rows($result);
		if($num_rows ==1) {
			$fr++;
			}
		}

// Number of Responders assigned but Multi-assignment allowed

$query = "SELECT *,		
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `$GLOBALS[mysql_prefix]responder`.`id`  
			AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') AND `multi` = 1 ) 
			AS `num_assignments`
		FROM `$GLOBALS[mysql_prefix]responder`			
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]responder`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`				
		$where2 `$GLOBALS[mysql_prefix]allocates`.`type` = 2 GROUP BY `$GLOBALS[mysql_prefix]responder`.`id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = mysql_fetch_assoc($result)) {
	if($row['num_assignments'] == 1) {
		$xx++;
		}
	}
$num_multi = $xx;

//---Setup variables to be returned.
$number_tickets = $num_tick;	
$num_not_assigned = $n;
$responders_available = $fr + $num_multi;
$num_ass_not_responding = $y;
$num_dis_resp_not_os = $z;
$num_on_scene = $r;
//------------------------------------
if((isset($time_running)) && (count($time_running) > 0)) {
	$tot_dates = array_sum($time_running);
	$avg_time = $tot_dates/count($time_running);
	$avg_tick_open = $avg_time;
	} else {
	$avg_tick_open = 0;
	}
//------------------------------------
if((isset($time_toclose)) && (count($time_toclose) > 0)) {
	$tot_time = array_sum($time_toclose);
	$avg_time_toclose = $tot_time/count($time_toclose);
	$avg_tick_toclose = $avg_time_toclose;
	} else {
	$avg_tick_toclose = 0;
	}	
//------------------------------------
if((isset($disp_list)) && (count($disp_list) > 0)) {
	$tot_dispatch = array_sum($disp_list);
	$avg_disp_time = $tot_dispatch/count($disp_list);	
	$avg_to_disp = $avg_disp_time;
	} else {
	$avg_to_disp = 0;
	}
//------------------------------------	
if((isset($f_disp_list)) && (count($f_disp_list) > 0)) {
	$tot_first_dispatch = array_sum($min_disp);
	$avg_first_disp_time = $tot_first_dispatch/count($min_disp);
	$avg_to_first_disp = $avg_first_disp_time;
	} else {
	$avg_to_first_disp = 0;
	}	
//------------------------------------
if((isset($resp_list)) && (count($resp_list) > 0)) {
	$tot_disp_resp = array_sum($resp_list);
	$avg_disptoresp = $tot_disp_resp/count($resp_list);
	$avg_time_disp2resp = $avg_disptoresp;
	} else {
	$avg_time_disp2resp = 0;
	}
//------------------------------------
if((isset($os_list)) && (count($os_list) > 0)) {
	$tot_disp_os = array_sum($os_list);
	$avg_disptoos = $tot_disp_os/count($os_list);
	$avg_time_disp2os = $avg_disptoos;
	} else {
	$avg_time_disp2os = 0;
	}

$r1 = $num_tick;
$r2 = $num_not_assigned;
$r3 = $num_ass_not_responding;
$r4 = $num_dis_resp_not_os;
$r5 = $num_on_scene;
$r6 = $avg_to_disp;
$r7 = $avg_time_disp2resp;
$r8 = $avg_time_disp2os;
$r9 = $avg_tick_open;
$r10 = $responders_available;
$r11 = $avg_tick_toclose;
$r12 = $avg_to_first_disp;
//------------------------------------
$ret_arr = array ($r1,$r2,$r3,$r4,$r5,$r6,$r7,$r8,$r9,$r10,$r11,$r12);
print json_encode($ret_arr);				// 1/6/11
exit();
?>
