<?php
error_reporting(E_ALL);	
/*
12/23/09 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/

@session_start();
require_once($_SESSION['fip']);		//7/28/10
										// housekeep old invites
$query = "DELETE from `$GLOBALS[mysql_prefix]chat_invites` WHERE `_on` < DATE_SUB(NOW(),INTERVAL 1 DAY )";
$result	= mysql_query($query);	// 

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
										// note: 'to' is a comma sep'd string of id's, with 0 = all
$query  = "INSERT INTO `$GLOBALS[mysql_prefix]chat_invites` (`to`, `_by`, `_from`, _on) VALUES ('{$_GET['frm_to']}', {$_GET['frm_user']}, '{$_SERVER['REMOTE_ADDR']}', '{$now}');";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
print "";
?>