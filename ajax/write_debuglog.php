<?php
/*
4/22/16 initial release
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
$ret_arr = array();
$theError = $_GET['debugtxt'];
$filename = "../debuglog.txt";

if (file_exists($filename)) {
	if (!$fp = fopen($filename, 'a')) {
		$ret_arr[0] = 0;
		} else {
		fwrite($fp, "Debug Written " . date('r') . "\r\n");
		fwrite($fp, "{$theError}\r\n\n");
		fclose($fp);
		$ret_arr[0] = 1;
		}		
	} else {
	if(!$fp = fopen($filename, 'w')) {
		$ret_arr[0] = 99;
		} else {
		fwrite($fp, "Created " . date('r') . "\n");
		fclose($fp);
		if (!$fp = fopen($filename, 'a')) {
			$ret_arr[0] = 0;
			} else {
			fwrite($fp, "Debug Written " . date('r') . "\r\n");
			fwrite($fp, "{$theError}\r\n\n");
			fclose($fp);
			$ret_arr[0] = 1;
			}		
		}
	}

print json_encode($ret_arr);
exit();
?>