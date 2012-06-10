<?php
/*
12/26/09 initial release 
5/5/10 drop json in favor of pipe delim'd string
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
get_current();
$me = $_SESSION['user_id'];

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]chat_invites` WHERE `_by` <> {$me}  AND (`to` = 0   OR `to` = {$me}) ORDER BY `id` DESC LIMIT 1";		// broadcasts
//snap( __LINE__, $query);

$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row_chat = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE  `callsign` > '' AND (`aprs` = 1 OR  `instam` = 1 OR  `locatea` = 1 OR  `gtrack` = 1 OR  `glat` = 1 ) ORDER BY `updated` DESC LIMIT 1";
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row_position = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_OPEN']} AND `_by` != {$me} ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row_ticket = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;


//	return pipe-delimited string - 5/5/10
$return = ($row_chat)? $row_chat['id'] 					: "0";	// 0
$return .= "|";
$return .= ($row_chat)? $row_chat['_by'] 				: "0";	// 1
//snap( __LINE__, $row_chat['_by'] );
//snap( __LINE__, $return);
$return .= "|";
$return .= ($row_position)? $row_position['id'] 		: "0";	// 2
// snap( __LINE__ , $return);
$return .= ($row_position)? $row_position['updated'] 	: "0";	// 3
// snap( __LINE__ , $return);
$return .= "|";
$return .= ($row_ticket)? $row_ticket['id'] 			: "0";	// 4
// snap( __LINE__ , $return);
$return .= "|";
$return .= ($row_ticket)? $row_ticket['owner'] 			: "0";	// 5
// snap( __LINE__ , $return);
$return .= "|";
// snap( __LINE__ , $return);

print $return;

/*
ex:	{"chat_id":0,"chat_by":0,"position_id":"95","position_updated":"2009-11-10 22:00:08","ticket_id":"361","ticket_owner":"0"}
*/
//$arr = array (
//		'chat_id'=>($row_chat)? $row_chat['id'] :(string) "0" ,
//		'chat_by'=>($row_chat)? $row_chat['_by'] : (string)"0"); 
//		'position_id'=>($row_position)? $row_position['id'] :(string) "0" ,
//		'position_updated'=>($row_position)? $row_position['updated'] : (string)"0" ,
//		'ticket_id'=> ($row_ticket)? $row_ticket['id'] :(string) "0",
//		'ticket_owner'=> ($row_ticket)? $row_ticket['owner'] :(string) "0"
//print json_encode($arr);		// returns JSON string

?>