<?php
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
ignore_user_abort(true);
set_time_limit (60*60*4);
print "sleeping";
flush();		// to buffer 
ob_flush();		// buffer flush

while (1){
//		$when = (time() + (5));
//		print time() . "<br />\n";
//snap(basename(__FILE__), __LINE__);
sleep (60);
	}
?>
