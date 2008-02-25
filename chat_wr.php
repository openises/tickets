<?php
	require_once('functions.inc.php'); 
	extract ($_GET);	
/*
	chat_messages : // id message  when chat_room_id user_id from
*/
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

	$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]chat_messages` (`when`, `message`, `chat_room_id`,  `user_id`,  `from`)
					VALUES (%s,%s,%s,%s,%s)",
						quote_smart($now),
						quote_smart($frm_message),
						quote_smart($frm_room),
						quote_smart($frm_user),
						quote_smart($frm_from));

	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	print mysql_insert_id();
?>