<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);
session_start();
session_write_close();	
require_once('../incs/functions.inc.php');
function adj_time($time_stamp) {
	$temp = mysql2timestamp($time_stamp);					// MySQL to integer form
	return date ("H:i", $temp);
	}
	
define("UNIT", 0);
define("MINE", 1);
define("ALL", 2);
$interval = 48;				// booked date limit - hide if date is > n hours ahead of 'now'
$blink_duration = 5;		// blink for n (5, here) minutes after ticket was written
$button_height = 50;		// height in pixels
$button_width = 160;		// width in pixels
$button_spacing = 4;		// spacing in pixels
$map_size = .75;			// map size multiplier - as a percent of full size
$butts_width = 0;
$ret_arr = array();
$id_array = array();

$time_now = mysql_format_date(now());			// collect ticket id's into $id_array 

if (array_key_exists('frm_mode', $_GET)) {$mode =  $_GET['frm_mode'];
	} else {
	if (is_unit())  {
		$mode = UNIT;
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
		$mode = (intval ($user_row['responder_id'])>0)? MINE: ALL;		// $mode => 'all' if no unit associated this user - 10/3/10
		}
	}		// end if/else initialize $mode

if ((($mode==0) || ($mode==1))) {									// pull $the_unit, $the_unit_name, this user
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` 
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `u`.`responder_id` = `r`.`id` )
		WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$user_row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_unit = $user_row['responder_id'];
	$the_unit_name = (empty($user_row['name']))? "NA": $user_row['name'];	// 'NA' if no responder this user
	}
else {
	 $the_unit_name = "NA";
	}

$restrict = ((($mode==UNIT) ) || ($mode==MINE))? " (`responder_id` = {$the_unit}) AND ": "";		// 8/20/10, 9/3/10 

$mob_show_cleared = intval(get_variable('mob_show_cleared'));

$showWhich = ($mob_show_cleared == 1) ? 
			"((`t`.`status` = {$GLOBALS['STATUS_OPEN']}) OR (`t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} AND `t`.`booked_date` < (NOW() + INTERVAL {$interval} HOUR)))" : 
			"(((`t`.`status` = {$GLOBALS['STATUS_OPEN']}) OR ((`t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} AND `t`.`booked_date` < (NOW() + INTERVAL {$interval} HOUR))))	AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'))";
																					// 5/19/11 -  all open assigns
$query = "SELECT *,  `t`.`id` AS `tick_id`,
			`t`.`street` AS `tick_street`,
			`t`.`city` AS `tick_city`,
			`t`.`status` AS `tick_status`,
			`t`.`updated` AS `tick_updated`,
			`r`.`name` AS `unit_name`,
			`r`.`handle` AS `unit_handle`,				
			`a`.`id` AS `assign_id`,
			`i`.`type` AS `inc_type`
		FROM  `$GLOBALS[mysql_prefix]ticket` `t`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a`  		ON (`a`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`a`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `u`	ON (`r`.`type` = `u`.`id` )	
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i`		ON (`i`.`id` = `t`.`in_types_id` )	
		WHERE {$restrict} {$showWhich}
		ORDER BY `t`.`status` DESC, `t`.`severity` DESC, `t`.`problemstart` ASC";

// dump($query);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
if (mysql_affected_rows()==0) {
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

	$for_str = $the_unit_name;

	$caption = ($mode==MINE)? "All calls": $the_unit_name;
	$frm_mode = ($mode==MINE)? ALL: MINE;
	} else {
 
	$i = $selected_indx = 0;
	$assigns_stack = array();
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		array_push($assigns_stack, $in_row);									// stack it up		
		if (empty($_GET['assign_id']) && empty($_GET['ticket_id'])) {
			if (empty($_GET) && ($i==0))	{$selected_indx = $i;}
			}
		else {
			if 	((empty($assigns_stack[$i]['assign_id'])) && 
				($assigns_stack[$i]['tick_id'] == $_GET['ticket_id'])) 
					{$selected_indx = $i;}
 			elseif (
 				(!empty($_GET['assign_id'])) && 
 				($assigns_stack[$i]['assign_id'] == $_GET['assign_id'])) 
 					{$selected_indx = $i;}
			}
		$i++;
		}		// end while(...)
 
	$assign_id = 	$assigns_stack[$selected_indx]['assign_id'];				// if any
	$ticket_id =  	$assigns_stack[$selected_indx]['tick_id'];					// 2/20/12
	$unit_id =  	$assigns_stack[$selected_indx]['responder_id'];				// if any
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `updated` > ('{$time_now}' - INTERVAL 5 MINUTE);";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
	array_push($id_array, $in_row['ticket_id']);
	}
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `updated` > ('{$time_now}' - INTERVAL 5 MINUTE);";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
	array_push($id_array, $in_row['ticket_id']);
	}			

$colors = array("even", "odd");
$the_ticket_id = (array_key_exists('ticket_id', $_GET))? $_GET['ticket_id'] : 0 ;

for ($i = 0; $i<count($assigns_stack); $i++) {
	if (($i==0) && ($the_ticket_id==0)) {$the_ticket_id = $assigns_stack[0]['ticket_id'];}
	$the_url = basename(__FILE__) . "?assign_id={$assigns_stack[$i]['assign_id']}&ticket_id={$assigns_stack[$i]['tick_id']}&frm_mode={$mode}\"";
	if (((now() -  mysql2timestamp($assigns_stack[$i]['tick_updated'])) < $blink_duration*60) ||
		(in_array( $assigns_stack[$i]['tick_id'], $id_array))) {
		$blinkst = "<blink>";
		$blinkend ="</blink>";
		} else {
		$blinkst = $blinkend = "";
		}		
			
	if ($i == $selected_indx) {
		$checked = "CHECKED";
		$the_ticket_id = $assigns_stack[$i]['tick_id'];
		} else {
		$checked = "";
		}
	switch($assigns_stack[$i]['severity'])		{					//set cell color by severity
		case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; 	break;
		case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; 	break;
		default: 							$severityclass='severity_normal'; 	break;
		}
	$the_icon = intval($assigns_stack[$i]['icon']);					// 6/19/11
	$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$the_icon];		// 8/29/10
	$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$the_icon];
	$unit_handle = addslashes($assigns_stack[$i]['handle']);
	$the_disp_stat = get_disp_status ($assigns_stack[$i]);			// 8/29/10
	$the_ticket = shorten("{$assigns_stack[$i]['scope']}", 24); 					
	$the_addr = shorten("{$assigns_stack[$i]['tick_street']}, {$assigns_stack[$i]['tick_city']}", 24); 					
	if($assigns_stack[$i]['tick_status'] == $GLOBALS['STATUS_SCHEDULED']) {
		$the_date = $assigns_stack[$i]['booked_date'];					
		$booked_symb = "<IMG SRC = 'markers/clock.png'/> &nbsp;";
		} else {
		$the_date =$assigns_stack[$i]['problemstart'];					
		$booked_symb = "";
		}
	$incType = $assigns_stack[$i]['inc_type'];
	
	$time_disp =  $assigns_stack[$selected_indx]['dispatched'];
	$time_resp =  $assigns_stack[$selected_indx]['responding'];
	$time_onsc =  $assigns_stack[$selected_indx]['on_scene'];
	$time_clear = $assigns_stack[$selected_indx]['clear'];
	$time_fenr =  $assigns_stack[$selected_indx]['u2fenr'];
	$time_farr =  $assigns_stack[$selected_indx]['u2farr'];
		
	$sb_width = max(320, intval($_SESSION['scr_width']* 0.4));				// 8/27/10
	$map_width = ($_SESSION['internet'])? get_variable('map_width'): 0;
	$position =  $sb_width + $map_width + $butts_width +10;
	$display_val = ($assigns_stack[$selected_indx]["assign_id"]>0)?  "block" : "none";
	}

	if (is_date($time_disp)) { 
		$ret_arr[0] ="<INPUT ID='disp_btn' TYPE= 'button' CLASS='btn_chkd text_big' VALUE='Disp @ " . adj_time($time_disp) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
		} else {
		$ret_arr[0] = "<INPUT ID='disp_btn' TYPE= 'button' CLASS='btn_not_chkd text_big' VALUE='Dispatched' onClick = 'set_assign(\'d\');' STYLE = 'display:" . $display_val . ";' />";
		} 
	if (is_date($time_resp)) { 
		$ret_arr[1] ="<INPUT ID='resp_btn' TYPE= 'button' CLASS='btn_chkd text_big' VALUE='Resp @ " . adj_time($time_resp) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
		} else { 
		$ret_arr[1] ="<INPUT ID='resp_btn' TYPE= 'button' CLASS='btn_not_chkd text_big' VALUE='Responding' onClick = \"set_assign('r');\" STYLE = 'display:" . $display_val . ";' />";
		} 
	if (is_date($time_onsc)) { 
		$ret_arr[2] ="<INPUT ID='onsc_btn' TYPE= 'button' CLASS='btn_chkd text_big' VALUE='On-scene @ " . adj_time($time_onsc) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
		} else { 
		$ret_arr[2] ="<INPUT ID='onsc_btn' TYPE= 'button' CLASS='btn_not_chkd text_big' VALUE='On-scene' onClick = \"set_assign('s');\" STYLE = 'display:" . $display_val . ";' />";
		} 
	if ($assigns_stack[$selected_indx]['rec_facility_id']>0) {
		if (is_date($time_fenr)) { 
			$ret_arr[3] ="<INPUT ID='f_enr_btn' TYPE= 'button' CLASS='btn_chkd text_big' VALUE=\"Fac'y enr @ " . adj_time($time_fenr) . "\" onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$ret_arr[3] ="<INPUT ID='f_enr_btn' TYPE= 'button' CLASS='btn_not_chkd text_big' VALUE=\"Fac'y enroute\" onClick = \"set_assign('e');\" STYLE = 'display:" . $display_val . ";' />";
			}
		if (is_date($time_farr)) { 		// 5/19/11
			$ret_arr[4] ="<INPUT ID='f_arr_btn' TYPE= 'button' CLASS='btn_chkd text_big' VALUE=\"Fac'y arr @ " . adj_time($time_farr) . "\" onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$ret_arr[4] ="<INPUT ID='f_arr_btn' TYPE= 'button' CLASS='btn_not_chkd text_big' VALUE=\"Fac'y arrive\" onClick = \"set_assign('a');\" STYLE = 'display:" . $display_val . ";' />";
			}
		} else {
		$ret_arr[3] = "";
		$ret_arr[4] = "";
		}
	if (is_date($time_clear)) { 
		$ret_arr[5] ="<INPUT ID='clear_btn' TYPE= 'button' CLASS='btn_chkd text_big' VALUE='Clear @ " . adj_time($time_clear) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
		} else { 
		$ret_arr[5] ="<INPUT ID='clear_btn' TYPE= 'button' CLASS='btn_not_chkd text_big' VALUE='Clear' onClick = \"set_assign('c');\" STYLE = 'display:" . $display_val . ";' />";	
		}		// end if (is_date($time_clear))

	if ((is_unit()) || ((has_admin())&&(intval($unit_id)>0))) {				// do/do-not allow status change - 2/7/12
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $unit_id . " LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$temp_row = mysql_fetch_assoc($result);    
		$ret_arr[6] ="<DIV CLASS='sel' style='width: 152px; display:" . $display_val . ";'>" . get_text("Status") . ":<BR />" . get_status_sel($unit_id, $temp_row['un_status_id'], "u", 10) . "</DIV>";
		$ret_arr[6] ="<DIV CLASS='sel' style='width: 152px; display:" . $display_val . ";'>" . get_text("Receiving Facility") . ":<BR />" . get_recfac_sel($unit_id, $ticket_id, $assign_id) . "</DIV>";	
		}
	if ($mode == ALL) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON (`$GLOBALS[mysql_prefix]responder`.`id` = `$GLOBALS[mysql_prefix]user`.`responder_id`)
			WHERE `$GLOBALS[mysql_prefix]user`.`id` = {$_SESSION['user_id']}
			LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
		if (intval($user_row['responder_id'])>0) {
			$ret_arr[7] = 1;
			}
		} else {
		if (can_edit()) {
			$ret_arr[7] = 2;
			} else {
			$ret_arr[7] = 0;
			}
		}
print json_encode($ret_arr);
//dump($ret_arr);
exit();
?>