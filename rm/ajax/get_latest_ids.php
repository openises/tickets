<?php
/*
9/10/13 - New file gets chat invites and new assignments to mobile user.
*/
error_reporting(E_ALL);
@session_start();
require_once('../../incs/functions.inc.php');

function error_out($err_arg) {
	do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);
	return;
	}				// end function error_out()

//	Get new Chat invites to me

$me = array_key_exists('user_id', $_SESSION)? sanitize_int($_SESSION['user_id']) :  1;
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}chat_invites` WHERE `_by` <> ? AND (`to` = 0   OR `to` = ?) ORDER BY `id` DESC LIMIT 1";
$result = db_query($query, [$me, $me]);
$chat_row = ($result->num_rows>0)? stripslashes_deep($result->fetch_assoc()): FALSE;

// 1/21/11 - get most recent dispatch to me

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user` WHERE `id` = ?";
$result = db_query($query, [$me]);
$resp_row = ($result->num_rows>0)? stripslashes_deep($result->fetch_assoc()): FALSE;
$the_resp_id = ($result->num_rows>0)? $resp_row['responder_id'] : 0;

$query = "SELECT *,`t`.`id` AS `ticket_id`
		FROM `{$GLOBALS['mysql_prefix']}assigns` `as`
		LEFT JOIN `{$GLOBALS['mysql_prefix']}ticket` `t` ON `as`.`ticket_id` = `t`.`id`
		WHERE `as`.`responder_id` = ?
		AND (`dispatched` IS NOT NULL OR DATE_FORMAT(`dispatched`,'%y') <> '00')
		AND (`responding` IS NULL OR DATE_FORMAT(`responding`,'%y') = '00')
		ORDER BY `as`.`as_of` DESC LIMIT 1";
$result = db_query($query, [$the_resp_id]);
$assign_row = ($result->num_rows>0)? stripslashes_deep($result->fetch_assoc()): FALSE;

$the_chat_id = ($chat_row)? $chat_row['id'] : "0";
$the_tick = ($assign_row)? $assign_row['ticket_id']: 0;
$the_hash = md5($the_chat_id . $the_tick);
$ret_arr = array ($the_chat_id, $the_tick, $the_hash);
print json_encode($ret_arr);
exit();
?>