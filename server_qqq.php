<?php
/*
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
//dump($_SERVER);
dump($_SERVER['HTTP_HOST']);
dump($_SERVER['SERVER_PORT']);
dump(rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
/*
http://127.0.0.1:80/tickets_05_27_test/
http://127.0.0.1:80/tickets_05_27_test
*/
$extra = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}{$extra}/";
dump ($url);
?>
