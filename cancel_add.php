<?php
/*
6/9/11 initial release
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
//snap (basename(__FILE__), __LINE__);
extract ($_POST);
if (strval(intval($ticket_id)) == $_POST['ticket_id']) {				// sanity check

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id` = '{$ticket_id}';";		// possibly none
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient` WHERE `ticket_id` = '{$ticket_id}';";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = '{$ticket_id}' AND `status` = '{$GLOBALS['STATUS_RESERVED']}' LIMIT 1;";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	}
else {
	snap (basename(__FILE__), implode (";", $_POST));
	}

//snap (__LINE__, $query);
?>

