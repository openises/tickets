<?php
/*
2/24/09 initial release - hh_login.php
*/
//	do_hh_login.php
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_logout(TRUE);

$requested_page = "track_me_bb.php";
do_login($requested_page, FALSE, TRUE) ;	
?>
