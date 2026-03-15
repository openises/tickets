<?php

error_reporting(E_ALL);
session_start();						// 
session_write_close();
require_once('./incs/functions.inc.php');
require_once('./incs/log_codes.inc.php');
require_once('./incs/functions_major.inc.php');
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
// Replaced extract — variables used by included reports_print_screen.php (Phase 2 cleanup)
$report = $_GET['report'] ?? '';
$func   = $_GET['func'] ?? '';
$what   = $_GET['what'] ?? '';
do_login(basename(__FILE__));

require_once('./forms/reports_print_screen.php');
print "<BR /><P ALIGN='left'>";
exit();
?>
