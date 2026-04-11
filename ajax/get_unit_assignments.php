<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
//if($_GET['q'] != $_SESSION['id']) {//
//    exit();
//    }
$resp_id = sanitize_int($_GET['unit']);
$assigns = array();
@session_start();        //
$query = "SELECT *,
    `a`.`id` AS `assigns_id`,
    `r`.`id` AS `resp_id`,
    `r`.`name` AS `responder_name`,
    `r`.`handle` AS `responder_handle`,
    `t`.`id` AS `tick_id`,
    `t`.`scope` AS `tick_scope`
    FROM `{$GLOBALS['mysql_prefix']}assigns` `a`
    LEFT JOIN `{$GLOBALS['mysql_prefix']}responder` `r` ON `r`.id=`a`.`responder_id`
    LEFT JOIN `{$GLOBALS['mysql_prefix']}ticket` `t` ON `t`.id=`a`.`ticket_id`
    WHERE `r`.`id` = ? AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')
    ORDER BY `assigns_id` ASC";
$result = db_query($query, [$resp_id]) or do_error($query, 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
$num_assigns = $result->num_rows;
if($num_assigns == 0) {
    $assignsStr = "";
    } else if($num_assigns == 1) {
    $row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
    $assignsStr = $row['tick_scope'];
    } else {
    $assignsStr = $num_assigns;
    }

$assigns[$resp_id] = $assignsStr;

print json_encode($assigns);
exit();
?>