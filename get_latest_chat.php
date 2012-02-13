<?php
/*
4/9/10 initial release 
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
get_current();
@session_start(); 	
$me = $_SESSION['user_id'];
//$me = 1;

				// most recent chat invites other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]chat_invites` WHERE `_by` <> {$me}  AND (`to` = 0   OR `to` = {$me}) ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
//snap ($me, $query);
//dump($row);
print ($row)? $row['id'] : "0";
?>