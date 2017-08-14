<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$ret_array = array();

function fs_get_disp_status ($row_in) {			// 3/25/11
	$tags_arr = explode("/", get_variable('disp_stat'));
	if (is_date($row_in['u2farr'])) 	{ return $tags_arr[4];}
	if (is_date($row_in['u2fenr'])) 	{ return $tags_arr[3];}
	if (is_date($row_in['on_scene'])) 	{ return $tags_arr[2];}
	if (is_date($row_in['responding'])) { return $tags_arr[1];}
	if (is_date($row_in['dispatched'])) { return $tags_arr[0];}
	}

$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

$al_groups = $_SESSION['user_groups'];	
			
if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
	$where2 = " AND `a`.`type` = 2";
	} else {
	if(!isset($curr_viewed)) {		
		$x=0;	//	6/10/11
		$where2 = "AND (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;	//	6/10/11
		$where2 = "AND (";	//	6/10/11
		foreach($curr_viewed as $grp) {	//	6/10/11
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		}
	$where2 .= " AND `a`.`type` = 2";	
	}

$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of,
	`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
	`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
	`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
	`t`.`description` AS `tick_descr`,
	UNIX_TIMESTAMP(`t`.`problemstart`) AS `tick_pstart`,
	`t`.`problemstart` AS `problemstart`,		
	`t`.`status` AS `tick_status`,
	`t`.`street` AS `tick_street`,
	`t`.`city` AS `tick_city`,
	`t`.`state` AS `tick_state`,			
	`r`.`id` AS `unit_id`,
	`r`.`name` AS `unit_name` ,
	`r`.`type` AS `unit_type` ,
	`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`,
	`$GLOBALS[mysql_prefix]assigns`.`clear` AS `clear`		
	FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]responder`	`r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )		
		WHERE (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') {$where2}   
	GROUP BY `unit_id` ORDER BY `severity` DESC, `tick_pstart` ASC";		

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$curr_calls =  mysql_num_rows($result);

$guest = is_guest();
$user = is_user();
$now = time() - (get_variable('delta_mins')*60);
$items = mysql_affected_rows();
$tags_arr = explode("/", get_variable('disp_stat'));		// 8/29/10 
$priorities = array("","severity_medium","severity_high" );

if($curr_calls > 0) {
	$w=0;		
	$unit_ids = array();
	$i = 0;	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//	While for Assignments
	
//	============================= Regions stuff
		$query_un = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `resource_id` = '$row[unit_id]' ORDER BY `id` ASC;";	// 5/4/11
		$result_un = mysql_query($query_un);	// 5/4/11
		$un_groups = array();
		while ($row_un = stripslashes_deep(mysql_fetch_assoc($result_un))) 	{	// 5/4/11
			$un_groups[] = $row_un['group'];
			}

		$inviewed = 0;	//	5/4/11
		foreach($un_groups as $un_val) {
			if(in_array($un_val, $al_groups)) {
				$inviewed++;
				}
			}
			
//	============================= end of Regions stuff				

		$in_strike = 	((!(empty($row['tick_scope']))) && ($row['tick_status']== $GLOBALS['STATUS_CLOSED']))? "<STRIKE>": "";					// 11/7/08
		$in_strikend = 	((!(empty($row['tick_scope']))) && ($row['tick_status']== $GLOBALS['STATUS_CLOSED']))? "</STRIKE>": "";
		if ($inviewed > 0) {	//	Tests to see whether assigned unit is in one of the users groups 5/4/11	
			$the_descr = (empty($row['tick_descr'])) ? "&nbsp;" : addslashes(str_replace($eols, " ", $row['tick_descr']));
			$the_short_one = (empty($row['tick_descr'])) ? "&nbsp; " : shorten(addslashes(str_replace($eols, " ", $row['tick_descr'])), 25);
				
			$address = (empty($row['tick_street']))? "&nbsp;" : $row['tick_street'] . ", ";		// 8/10/10
			$address = addslashes($address . $row['tick_city']. "&nbsp;". $row['tick_state']);
			if (!(empty($row['tick_scope']))) {	
				$the_name = addslashes ($row['tick_scope']);															// 9/12/09
				$the_short_name = shorten($row['tick_scope'], 15);
				$short_addr = shorten($address, 15);
				$cell1 = $the_name;
				$cell2 = format_sb_date_2($row['problemstart']);
				$cell3 = $the_short_one;
				$cell4 = $address;
				} else {
				$cell1 = $row['ticket_id'];
				$cell2 = format_sb_date_2($row['problemstart']);
				$cell3 = $the_short_one;
				$cell4 = $address;
				}
	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`	WHERE `id`= '{$row['unit_type']}' LIMIT 1";
			$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$row_type = (mysql_affected_rows() > 0) ? stripslashes_deep(mysql_fetch_assoc($result_type)) : "";
			$the_bg_color = empty($row_type)?	"transparent" : $GLOBALS['UNIT_TYPES_BG'][$row_type['icon']];		// 3/15/10
			$the_text_color = empty($row_type)? "black" :		$GLOBALS['UNIT_TYPES_TEXT'][$row_type['icon']];		// 
			unset ($row_type);

			$unit_name = empty($row['unit_id']) ? "[#{$row['unit_id']}]" : addslashes($row['unit_name']) ;			// id only if absent
			$short_name = shorten($unit_name, 10);
			$the_disp_str =  fs_get_disp_status ($row);		// 3/25/11
			
			$cell5 = $unit_name;
			$cell6 = $the_disp_str;
			$cell7 = $row['unit_id'];
			
			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`,
				`t`.`id` AS `type_id`, 
				`r`.`id` AS `unit_id`, 
				`r`.`name` AS `name`,
				`s`.`description` AS `stat_descr`,  
				`r`.`name` AS `unit_name`
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 
				WHERE `r`.`id` = '{$row['unit_id']}' LIMIT 1";

			$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_unit = stripslashes_deep(mysql_fetch_assoc($result_unit));
			}
		$ret_arr[$i][0] = $cell1;
		$ret_arr[$i][1] = $cell2;
		$ret_arr[$i][2] = $cell3;
		$ret_arr[$i][3] = $cell4;
		$ret_arr[$i][4] = $cell5;
		$ret_arr[$i][5] = $cell6;
		$ret_arr[$i][6] = $cell7;				
		$i++;
		}
	} else {
		$ret_arr[0][0] = 0;
	}
print json_encode($ret_arr);
exit();