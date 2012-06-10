<?php
/*
*/
error_reporting(E_ALL);	

ob_start();							// 6/26/10
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
ob_start();							// 6/26/10

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10

@session_start();							// 
if ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) {
	require_once('./incs/functions.inc.php');
	require_once('./incs/functions_major.inc.php');
	}
else {
//	require_once('./incs/functions_nm.inc.php');
	require_once('./incs/functions_major_nm.inc.php');
	}
/*
*/
echo "===== HELLO =====";
?>
