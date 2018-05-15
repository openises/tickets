<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
//if($_GET['q'] != $_SESSION['id']) {
//	exit();
//	}

$assigns = array();
@session_start();
$query = "SELECT  
	`a`.`as_of` AS `as_of`,
	`a`.`id` AS `assigns_id`,
	`a`.`responder_id` AS `responder_id`,
	`a`.`status_id` AS `assigns_status`,
	`a`.`comments` AS `assigns_comments`,
	`a`.`start_miles` AS `assigns_startmiles`,
	`a`.`on_scene_miles` AS `assigns_osmiles`,
	`a`.`end_miles` AS `assigns_endmiles`,
	`a`.`miles` AS `assigns_miles`,
	`a`.`dispatched` AS `dispatched`,
	`a`.`responding` AS `responding`,
	`a`.`clear` AS `clear`,
	`a`.`on_scene` AS `on_scene`,
	`a`.`u2fenr` AS `u2fenr`,	
	`a`.`u2farr` AS `u2farr`,
	`a`.`user_id` AS `dispatched_by`,	
	`r`.`id` AS `resp_id`,
	`r`.`name` AS `responder_name`, 
	`r`.`handle` AS `responder_handle`,
	`r`.`un_status_id` AS `un_status_id`,
	`s`.`status_val` AS `status_val`,
	`t`.`id` AS `tick_id`,
	`t`.`comments` AS `tick_comments`,
	`t`.`street` AS `tick_street`,	
	`t`.`city` AS `tick_city`,	
	`t`.`state` AS `tick_state`,	
	`t`.`scope` AS `tick_scope`,
	`i`.`id` AS `type_id`,
	`i`.`type` AS `type_name`	
	FROM `$GLOBALS[mysql_prefix]assigns` `a`
	LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON `r`.`id` = `a`.`responder_id`
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `t`.`id` = `a`.`ticket_id`
	LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON `r`.`un_status_id` = `s`.`id`
	LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i` ON `t`.`in_types_id` = `i`.`id` 
	WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'
	ORDER BY `tick_id`, `assigns_id` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_assigns = mysql_num_rows($result);
$i = 0;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$assigns[$i][] = $row['assigns_id'];	//	assignment ID
	$assigns[$i][] = $row['resp_id'];		//	Responder / Unit ID
	$assigns[$i][] = $row['tick_id'];		//	Ticket ID
	$assigns[$i][] = $row['tick_scope'];	//	Ticket Name
	$assigns[$i][] = $row['tick_comments'];	//	Ticket Synopsis
	$assigns[$i][] = substr($row['tick_street'] . " " . $row['tick_city'] . " " . $row['tick_state'], 0, 25);	//	Ticket Location / Address
	$assigns[$i][] = $row['type_name'];		//	Ticket Type / Nature
	$assigns[$i][] = $row['responder_name'];	//	Ticket Synopsis
	$assigns[$i][] = get_contact_via($row['resp_id']);	//	Responder Contact email(s)
	$assigns[$i][] = (is_date($row['dispatched'])) ? format_sb_date_2($row['dispatched']) : "";
	$assigns[$i][] = (is_date($row['responding'])) ? format_sb_date_2($row['responding']) : "";
	$assigns[$i][] = (is_date($row['on_scene'])) ? format_sb_date_2($row['on_scene']) : "";
	$assigns[$i][] = (is_date($row['u2fenr'])) ? format_sb_date_2($row['u2fenr']) : "";
	$assigns[$i][] = (is_date($row['u2farr'])) ? format_sb_date_2($row['u2farr']) : "";
	$assigns[$i][] = (is_date($row['clear'])) ? format_sb_date_2($row['clear']) : "";
	$assigns[$i][] = (array_key_exists($row['un_status_id'], $validStatuses)) ? get_status_sel($row['resp_id'], $row['un_status_id'], "u") : "Status Error"; 	//	Responder Status
	$assigns[$i][] = format_sb_date_2($row['as_of']);
	$assigns[$i][] = $row['dispatched_by'];
	$assigns[$i][] = $row['assigns_comments'];;	
	$i++;
	}
print json_encode($assigns);
exit();
?>