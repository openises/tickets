<?php
/*
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
	$query = "
		SELECT * FROM `$GLOBALS[mysql_prefix]log`
		WHERE `code` = {$GLOBALS['LOG_ERROR']}
		AND `when` > DATE_SUB(NOW(),INTERVAL 10 DAY )
		ORDER BY `when` DESC";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	echo "<table border = 1>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		extract ($row);
		echo "<tr><td>{$when}</td><td>{$info}</td><td>{$who}</td><td>{$ticket_id}</td><td>{$responder_id}</td><td>{$who}</td><td>{$from}</td></tr>";
		}
	echo "</table>";
?>
