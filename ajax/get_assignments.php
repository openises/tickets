<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */

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
	WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'
	ORDER BY `assigns_id` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_assigns = mysql_num_rows($result);
$i = 0;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$assignsID = $row['assigns_id'];
	$respID = $row['resp_id'];
	$tickID = $row['tick_id'];
	$scope = $row['tick_scope'];
	$assigns[$i][0] = $assignsID;
	$assigns[$i][1] = $respID;
	$assigns[$i][2] = $tickID;
	$assigns[$i][3] = $scope;
	$i++;
	}

//dump($assigns);
print json_encode($assigns);
exit();
?>