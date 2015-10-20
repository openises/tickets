<?php
/*
5/28/14 new release
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
$ret_arr = array();


$to_str = $_GET['to_str'];
$smsg_to_str = $_GET['smsg_to_str'];
$subject_str = $_GET['subject_str'];
$text_str = $_GET['text_str'];

if(($to_str == "") && ($smsg_to_str == "")) {
	$ret_arr[0] = "0";
	} elseif(($subject_str == "") || ($text_str == "")) {
	$ret_arr[0] = "0";
	} else {
	$sent_number = do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
	if($sent_number == "") {
		$sent_number = 0;
		}
	$ret_arr[0] = $sent_number;
	}
print json_encode($ret_arr);
exit();
?>