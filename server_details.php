<?php

error_reporting(0);
require_once('./incs/functions.inc.php');
dump($_SERVER);
echo $_SERVER['HTTP_HOST'] . "<BR />";
echo $_SERVER['SERVER_NAME'] . "<BR />";
echo gethostname() . "<BR />";
echo php_uname('n') . "<BR />";
$serverport = "1337";
$serverstring = "tcp://" . $_SERVER['HTTP_HOST'] . ":" . $serverport;
echo $serverstring . "<BR />";

$server = stream_socket_server("{$serverstring}", $errno, $errstr);

//	Check if server started.

if ($server === false) {
	echo 99;
	sleep(2);
	echo "\r\nServer Failed to start\r\n";
	} else {
	echo 1;
	sleep(2);
	echo "\r\nServer Started OK\r\n";
	}
?>
