<?php
/*
2/3/09 initial release - returns an existing reserved id from this remote or else a new one
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10
													// do we have one fm this user
	$query  = "SELECT `id`, `contact` FROM `$GLOBALS[mysql_prefix]ticket` 
		WHERE `status`= '" . $GLOBALS['STATUS_RESERVED'] . "' AND  `contact` = " . quote_smart($_SERVER['REMOTE_ADDR']) . " LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	if (mysql_affected_rows() > 0) {
		$row = stripslashes_deep(mysql_fetch_array($result));
		unset($result);
		print $row['id'];				// return it
		}

	else {
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` , `severity` , `updated` 
			) VALUES (
				NULL , '', " . quote_smart($_SERVER['REMOTE_ADDR']) . "	, NULL , NULL , NULL , NULL , NULL , NULL , NULL , 
				NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL
			)";
			
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		print mysql_insert_id();
		}

?>