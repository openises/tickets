<?php
	require_once('./incs/functions.inc.php'); 
	extract ($_GET);	
/*
	chat_messages : // id message  when chat_room_id user_id from
*/
					
	$query = "SELECT `$GLOBALS[mysql_prefix]chat_messages`.*, UNIX_TIMESTAMP(`when`) AS `when`, `$GLOBALS[mysql_prefix]user`.`user` AS `user_name`, 
		`$GLOBALS[mysql_prefix]chat_messages`.`id` AS chat_messages_id 
		FROM `$GLOBALS[mysql_prefix]chat_messages` 
		LEFT JOIN `$GLOBALS[mysql_prefix]user` ON `$GLOBALS[mysql_prefix]chat_messages`.`user_id` = `$GLOBALS[mysql_prefix]user`.`id` 
		WHERE `$GLOBALS[mysql_prefix]chat_messages`.`id` > $last_id 
		ORDER BY `chat_messages_id`";


	$result = mysql_query($query) or do_error('ERROR',$query,mysql_error(), basename( __FILE__), __LINE__);
	$return = "";
	while ($row = stripslashes_deep(mysql_fetch_array($result))){
		$return .= $row['user_name'] . "\t" .date("G:i", $row['when']) . "\t" .$row['message'] . "\t" .$row['chat_messages_id'] . "\t". 0xFF;
		}
	print $return;

?>