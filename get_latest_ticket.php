<?php
/*
4/9/10 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts. 
*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
$me = $_SESSION['user_id'];
//$me =1;
				// most recent ticket other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `_by` <> {$me} AND `status` = {$GLOBALS['STATUS_OPEN']} ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

print ($row)? $row['id'] : "0";
?>

