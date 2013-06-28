<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require '../incs/functions.inc.php';
require './incs/exportcsv.inc.php';
$user = $_SESSION['user_id'];
 
exportMysqlToCsv($user);
 
?> 
