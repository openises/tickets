<?php

error_reporting(E_ALL);
session_start();						// 
session_write_close();
require_once('./incs/functions.inc.php');
require_once('./incs/log_codes.inc.php');
require_once('./incs/functions_major.inc.php');
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
extract($_GET);
/*

*/
do_login(basename(__FILE__));

require_once('./forms/reports_print_screen.php');
print "<BR /><P ALIGN='left'>";
exit();
?>
