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

$theresult = do_tweet($the_message);
if($theresult == 1) {
	$ret_arr[0] = 1;
	} else {
	$ret_arr[0] = 0;
	}

print json_encode($ret_arr);