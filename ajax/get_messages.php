<?php
/*
get_messages.php - AJAX file gets email and SMS Gateway messages in background - called from top.php
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');
require_once('../incs/messaging.inc.php');
require_once '../lib/xpm/POP3.php';
require_once '../lib/xpm/MIME.php';
error_reporting(E_ALL);				// 9/13/08
set_time_limit(0);
$orgcode = get_msg_variable('smsg_orgcode');
$apipin = get_msg_variable('smsg_apipin');
$mode = get_msg_variable('smsg_mode');
@session_start();
$the_result = "";
if (empty($_SESSION)) {
	header("Location: ../index.php");
	}
session_write_close();
$retrivemode = (!empty($_GET['mode'])) ? 1 : 0;
$response = 0;
$response2 = 0;
$ret_arr = array();
if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
	$the_ret = do_smsg_retrieve($orgcode,$apipin,$mode,$retrivemode);
	$response =($the_ret) ? $the_ret : "";
	}

if((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 3)) {
	$url = get_msg_variable('email_server');
	$port = intval(get_msg_variable('email_port'));
	$protocol = get_msg_variable('email_protocol');
	$addon = get_msg_variable('email_addon');
	$folder = get_msg_variable('email_folder');
	$user = get_msg_variable('email_userid'); 
	$password = get_msg_variable('email_password');
	$ssl = 'ssl';
	$response2 = get_emails("$url", "$user", "$password", $port, "$ssl", 100);
	}


$ret_arr[] = $response;
$ret_arr[] = $response2;
print json_encode($ret_arr);
exit();
?>	
