<?php
/*
4/26/09 initial release
5/25/09 reset defined here to avoid need for current functions.inc.php
10/6/09 added unused entries for unit to facility fields
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/1/10 set unit 'updated' time
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10
$now = "'" . mysql_format_date(time() - (get_variable('delta_mins')*60)) . "'";

/*
USERS: you may replace NULL with $now (EXACTLY THAT!) in the following sql query to meet local needs
*/

$GLOBALS['LOG_CALL_RESET']			= 34;		// 5/25/09

//$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
//	`dispatched` = NULL,
//	`responding` = NULL,
//	`on_scene` = NULL,
//	`u2fenr` = NULL,
//	`u2farr` = NULL,
//	`clear` = NULL,
//	`as_of` = $now
//	WHERE `id` = {$_POST['frm_id']} LIMIT 1;";


$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` =  {$_POST['frm_id']} LIMIT 1";
$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

$row = mysql_fetch_assoc($result);													// collect for log


do_log($GLOBALS['LOG_CALL_RESET'], $row['ticket_id'], $row['responder_id'], $row['id']);
					
set_u_updated ($_POST['frm_id']) ;					// 9/1/10

$query = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = {$_POST['frm_id']} LIMIT 1;";
$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
//snap(__LINE__, $query );

unset($result);
?>
