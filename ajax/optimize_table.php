<?php
/*

*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');		//7/28/10
require_once('../incs/tables.inc.php');		//7/28/10
$ret_arr = array();
$tablename = $_GET['tablename'];

$ret_arr[0] = remove_dupes($tablename);
$ret_arr[1] = $tablename;
print json_encode($ret_arr);
?>