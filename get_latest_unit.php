<?php
/*
4/9/10 initial release 
6/11/10 disabled unit_flag_2 setting
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.

*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
$me = $_SESSION['user_id'];
							// position updates?
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE  `callsign` > '' AND (`aprs` = 1 OR  `instam` = 1 OR  `locatea` = 1 OR  `gtrack` = 1 OR  `glat` = 1 ) ORDER BY `updated` DESC LIMIT 1";
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

if (!($row )) {				// latest unit status updates written by others 
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `user_id` != {$me} ORDER BY `updated` DESC LIMIT 1";		// get most recent
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$row =  (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
	}

if ($row) {
	$_SESSION['unit_flag_1'] = $row['id'];
//	$_SESSION['unit_flag_2'] = $me;
	}
print ($row)? $row['id'] : "0";

?>