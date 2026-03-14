<?php
error_reporting(E_ALL);
/*
9/10/13 New file, supports mobile chat
*/
@session_start();
require_once('../../incs/functions.inc.php');

// housekeep old invites

$query = "DELETE from `{$GLOBALS['mysql_prefix']}chat_invites` WHERE `_on` < DATE_SUB(NOW(),INTERVAL 1 DAY )";
$result	= db_query($query);

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$frm_to = sanitize_string($_GET['frm_to']);
$frm_user = sanitize_int($_GET['frm_user']);
									// note: 'to' is a comma sep'd string of id's, with 0 = all
$query  = "INSERT INTO `{$GLOBALS['mysql_prefix']}chat_invites` (`to`, `_by`, `_from`, _on) VALUES (?, ?, ?, ?)";
$result	= db_query($query, [$frm_to, $frm_user, $_SERVER['REMOTE_ADDR'], $now]);
print "";
exit();
?>