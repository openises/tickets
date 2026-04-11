<?php
/*
9/10/13 New file - writes ne chat message - for mobile page
*/
@session_start();
require_once '../../incs/functions.inc.php';
$frm_message = sanitize_string($_GET['frm_message']);
$frm_room = sanitize_int($_GET['frm_room']);
$frm_user = sanitize_int($_GET['frm_user']);
$frm_from = sanitize_string($_GET['frm_from']);

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query  = "INSERT INTO `{$GLOBALS['mysql_prefix']}chat_messages` (`when`, `message`, `chat_room_id`, `user_id`, `from`)
				VALUES (?, ?, ?, ?, ?)";
$result	= db_query($query, [$now, $frm_message, $frm_room, $frm_user, $frm_from]);
print db()->insert_id;
exit();
?>