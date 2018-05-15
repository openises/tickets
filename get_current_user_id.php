<?php
/*
*/

error_reporting(E_ALL);
@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
$me = array_key_exists('user_id', $_SESSION)? $_SESSION['user_id'] :  0;
$ret_arr = array();
$ret_arr[0] = $me;
print json_encode($ret_arr);
exit();
?>