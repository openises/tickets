<?php
/*
11/21/09 initial release
*/
//
// generic session value writer - note names, method
//
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
session_start(); 	
$_SESSION[$_GET['the_name']] = $_GET['the_value'];
?>
