<?php
error_reporting(E_ALL);
/*
9/10/13 New file, read chat messages on mobile page
*/

@session_start();
require_once '../../incs/functions.inc.php';
$last_id = sanitize_int($_REQUEST['last_id']);
$max_ct = sanitize_int($_REQUEST['max_ct']);
//	snap(basename(__FILE__),$last_id );
$query = "DELETE from `{$GLOBALS['mysql_prefix']}chat_messages` WHERE `when` < DATE_SUB(NOW(),INTERVAL 1 DAY )";
$result = db_query($query);

$query_p1 = "SELECT * FROM (" ;
$query_p2 = "SELECT `{$GLOBALS['mysql_prefix']}chat_messages`.*, `{$GLOBALS['mysql_prefix']}user`.`user` AS `user_name`,
	`{$GLOBALS['mysql_prefix']}chat_messages`.`id` AS chat_messages_id
	FROM `{$GLOBALS['mysql_prefix']}chat_messages`
	LEFT JOIN `{$GLOBALS['mysql_prefix']}user` ON `{$GLOBALS['mysql_prefix']}chat_messages`.`user_id` = `{$GLOBALS['mysql_prefix']}user`.`id`
	WHERE `{$GLOBALS['mysql_prefix']}chat_messages`.`id` > ?
	ORDER BY `chat_messages_id` DESC LIMIT ?";
$query_p3 = ") AS r ORDER BY `chat_messages_id` ASC ";

$query = $query_p1 . $query_p2 . $query_p3;
$result = db_query($query, [$last_id, $max_ct]);
//	snap(__LINE__, $query );
$return = "";
while ($row = stripslashes_deep($result->fetch_array())){
	$return .= e($row['user_name']) . "\t" . substr($row['when'], 11,5) . "\t" . e($row['message']) . "\t" .$row['chat_messages_id'] . "\t". 0xFF;
	}
print $return;
exit();
?>
