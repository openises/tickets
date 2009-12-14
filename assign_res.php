<?php
/*
4/26/09 initial release
5/25/09 reset defined here to avoid need for current functions.inc.php
10/6/09 Added untries for new fields in assigns table u2fenr and u2farr (unit to facility status
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php'); 
$now = "'" . mysql_format_date(time() - (get_variable('delta_mins')*60)) . "'";

/*
USERS: you may replace NULL with $now (EXACTLY THAT!) in the following sql query to meet local needs
*/

$GLOBALS['LOG_CALL_RESET']			= 34;		// 5/25/09

$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
	`dispatched` = NULL,
	`responding` = NULL,
	`on_scene` = NULL,
	`u2fenr` = NULL,
	`u2farr` = NULL,
	`clear` = NULL,
	`as_of` = $now
	WHERE `id` = {$_POST['frm_id']} LIMIT 1;";


$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` =  {$_POST['frm_id']} LIMIT 1";
$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

$row = mysql_fetch_assoc($result);

do_log($GLOBALS['LOG_CALL_RESET'], $row['ticket_id'], $row['responder_id'], $row['id']);

unset($result);
?>
