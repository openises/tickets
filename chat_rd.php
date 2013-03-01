<?php
error_reporting(E_ALL);
/*
12/26/09 added user id restriction, ordering logic, DELETE query
5/29/10 reviseD to accommodate asynch ajax post call
chat_messages : // id message  when chat_room_id user_id from
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/

@session_start();
require_once($_SESSION['fip']);		//7/28/10
	extract ($_REQUEST);						// 5/29/10
//	snap(basename(__FILE__),$last_id );
	$query = "DELETE from `$GLOBALS[mysql_prefix]chat_messages` WHERE `when` < DATE_SUB(NOW(),INTERVAL 1 DAY )";
	$result = mysql_query($query) or do_error('ERROR',$query,mysql_error(), basename( __FILE__), __LINE__);	

	$query_p1 = "SELECT * FROM (" ;
	$query_p2 = "SELECT `$GLOBALS[mysql_prefix]chat_messages`.*, `$GLOBALS[mysql_prefix]user`.`user` AS `user_name`, 
		`$GLOBALS[mysql_prefix]chat_messages`.`id` AS chat_messages_id 
		FROM `$GLOBALS[mysql_prefix]chat_messages` 
		LEFT JOIN `$GLOBALS[mysql_prefix]user` ON `$GLOBALS[mysql_prefix]chat_messages`.`user_id` = `$GLOBALS[mysql_prefix]user`.`id` 
		WHERE `$GLOBALS[mysql_prefix]chat_messages`.`id` > $last_id 
		ORDER BY `chat_messages_id` DESC LIMIT {$max_ct}";
	$query_p3 = ") AS r ORDER BY `chat_messages_id` ASC ";

	$query = $query_p1 . $query_p2 . $query_p3;
	$result = mysql_query($query) or do_error('ERROR',$query,mysql_error(), basename( __FILE__), __LINE__);
//	snap(__LINE__, $query );
	$return = "";
	while ($row = stripslashes_deep(mysql_fetch_array($result))){
		$return .= $row['user_name'] . "\t" . substr($row['when'], 11,5) . "\t" .$row['message'] . "\t" .$row['chat_messages_id'] . "\t". 0xFF;
		}
	print $return;
?>
 