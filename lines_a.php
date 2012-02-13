<?php
/*
6/29/10 - initial issue
7/10/10 revised to call get_cb_height ()
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
echo (string) get_cb_height ();
?>
	