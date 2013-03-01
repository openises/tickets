<?php
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');

set_time_limit(0);
$orgcode = get_msg_variable('smsg_orgcode');
$apipin = get_msg_variable('smsg_apipin');
$mode = "TESTXML";

//$host = '{pop.mail.yahoo.co.uk:110/pop3/notls}INBOX'; 
$url = get_msg_variable('email_server');
$port = get_msg_variable('email_port');
$protocol = get_msg_variable('email_protocol');
$addon = get_msg_variable('email_addon');
$folder = get_msg_variable('email_folder');
$host = '{' . $url . ':' . $port . '/' . $protocol . '/' . $addon . '}' . $folder;
$user = get_msg_variable('email_userid'); 
$password = get_msg_variable('email_password'); 
print $host . "<BR />";


$mbox = imap_open($host, $user, $password);

@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
do_login(basename(__FILE__));

print get_emails($mbox);