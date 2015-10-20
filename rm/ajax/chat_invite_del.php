<?php
/*
9/10/13	New file, deletes chat invite on acceptance.
*/

error_reporting(E_ALL);	
@session_start();
require_once('../../incs/functions.inc.php');

if((empty($_GET)) || (!isset($_GET['responder_id'])) || ($_GET['responder_id'] == 0) || ($_GET['responder_id'] =="")) {
	exit();
	}

$theID = $_GET['responder_id'];

$query = "DELETE from `$GLOBALS[mysql_prefix]chat_invites` WHERE `to` = '" . $theID . "'";
$result	= mysql_query($query);	// 
if(mysql_affected_rows() > 0) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 99;
	}
	
print json_encode($ret_arr);
exit();
?>