<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
//if($_GET['q'] != $_SESSION['id']) {//
//	exit();
//	}
$resp_id = $_GET['unit'];
$assigns = array();
@session_start();		// 
$query = "SELECT *, 
	`a`.`id` AS `assigns_id`,
	`r`.`id` AS `resp_id`,
	`r`.`name` AS `responder_name`, 
	`r`.`handle` AS `responder_handle`,
	`t`.`id` AS `tick_id`,
	`t`.`scope` AS `tick_scope`
	FROM `$GLOBALS[mysql_prefix]assigns` `a`
	LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON `r`.id=`a`.`responder_id`
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `t`.id=`a`.`ticket_id`
	WHERE `r`.`id` = " . $resp_id . " AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')
	ORDER BY `assigns_id` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_assigns = mysql_num_rows($result);
if($num_assigns == 0) {
	$assignsStr = "";
	} else if($num_assigns == 1) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$assignsStr = $row['tick_scope'];
	} else {
	$assignsStr = $num_assigns;
	}

$assigns[$resp_id] = $assignsStr;

print json_encode($assigns);
exit();
?>