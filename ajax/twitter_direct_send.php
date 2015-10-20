<?php
/*
list messages.php - gets messages from messages table for display in message window and ticket view and unit view
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$ret_arr = array();
$the_message = strip_tags($_GET['message']);
$the_userid = strip_tags($_GET['userid']);
$the_screenname = strip_tags($_GET['screenname']);
$theresult = do_tweet_direct($the_message, $the_userid, $the_screenname);
if($theresult == 1) {
	$ret_arr[0] = 1;
	} else {
	$ret_arr[0] = $theresult;
	}

print json_encode($ret_arr);