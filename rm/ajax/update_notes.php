<?php
/*
9/10/13 - New file, updates ticket notes for assignment from mobile screen
*/
error_reporting(E_ALL);

@session_start();
require_once('../../incs/functions.inc.php');
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

$ret_arr = array();
$value = sanitize_string($_GET['notes']);
$assigns_id = sanitize_int($_GET['assigns_id']);
$ticket_id = sanitize_int($_GET['ticket_id']);
$user_id = (isset($_GET['user_id'])) ? sanitize_int($_GET['user_id']) : 0;
$user_name = ($user_id != 0) ? get_responder($user_id) : "Unknown";
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
if($value != "") {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `id` = ? LIMIT 1";
	$result	= db_query($query, [$ticket_id]);
	$row = stripslashes_deep($result->fetch_assoc());
	$notes = (($row['comments'] != "New") && ($row['comments'] != "")) ? $row['comments'] . "\n\r" : "";
	$notes .= "Comment Added by " . $user_name . "\nDate" . $now . ": ";
	$notes .= $value . "\n---\n";
	$query = "UPDATE `{$GLOBALS['mysql_prefix']}ticket` SET `updated`= ?, `comments` = ? WHERE `id` = ? LIMIT 1";
	$result	= db_query($query, [$now, $notes, $ticket_id]);
	if(($result) && ($notes != "")) {
		$ret_arr[0] = 100;
		} else {
		$ret_arr[0] = 999;
		}
	$notes .= br2nl($value);
	} else {
	$ret_arr[0] = 999;
	}

print json_encode($ret_arr);
exit();
?>