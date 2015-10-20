<?php
/*
9/10/13 - New file, updates read status for message from mobile screen
*/

require_once('../../incs/functions.inc.php');
require_once('../../incs/messaging.inc.php');
set_time_limit(0);
if(empty($_GET)) {
	exit();
	}

@session_start();
extract($_GET);

$the_users = array();	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_users[] = $row['id'];
	}	

$count_users = count($the_users);

$the_return = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` `m` WHERE `id` = '" . clean_string($uid) . "' LIMIT 1";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$readby = $row['readby'];
$message = $row['message'];
$recipients = $row['recipients'];
$fromAddress = $row['from_address'];
$theothers = "";
$tick_id = $row['ticket_id'];
$responder_id = $row['resp_id'];
$the_sep = "";
$the_readers = array();
$the_readers = explode("," , $row['readby']);
$the_readnames = array();
foreach($the_readers as $val) {
	$the_readnames[] = get_reader_name($val);
	}
$the_names = implode(",", $the_readnames);
$the_user = clean_string($responder_id);
$count_readers = count($the_readers);
$the_readstatus = ($count_users == $count_readers) ? 2 : 1;
if(($the_readers[0] != "") && (in_array($the_user, $the_readers, true))) {
	//	Do Nothing - user has already read this message
	$the_return[0] = 0;
	} else {
	if(($readby == "") || ($readby == NULL)) {
		$the_sep = "";
		} else {
		$the_sep = ",";
		}
	$the_readstatus = ($count_users == $count_readers) ? 2 : 1;
	$the_readby_str = $readby . $the_sep . $the_user;
	$query2 = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `readby`='$the_readby_str', `read_status` = " . $the_readstatus . " WHERE `id`='$uid'";
	$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_affected_rows() > 0) {
		$the_return[0] = 1;
		} else {
		$the_return[0] = 99;
		}		
	}

print json_encode($the_return);	
exit();
?>