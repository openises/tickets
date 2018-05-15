<?php

error_reporting(E_ALL);
session_start();						// 
session_write_close();
require_once('./incs/functions.inc.php');
/*

*/
do_login(basename(__FILE__));
require_once('./forms/mdb_reports_print_screen.php');
print "<BR /><P ALIGN='left'>";
exit();
?>
