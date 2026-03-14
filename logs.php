<?php
/*
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
	$query = "
		SELECT * FROM `{$GLOBALS['mysql_prefix']}log`
		WHERE `code` = {$GLOBALS['LOG_ERROR']}
		AND `when` > DATE_SUB(NOW(),INTERVAL 10 DAY )
		ORDER BY `when` DESC";
	$result = db_query($query);
	echo "<table border = 1>";
	while ($row = stripslashes_deep($result->fetch_assoc())) 	{
		extract ($row);
		echo "<tr><td>" . e($when) . "</td><td>" . e($info) . "</td><td>" . e($who) . "</td><td>" . e($ticket_id) . "</td><td>" . e($responder_id) . "</td><td>" . e($who) . "</td><td>" . e($from) . "</td></tr>";
		}
	echo "</table>";
?>
