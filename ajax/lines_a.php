<?php
/*
6/29/10 - initial issue

7/10/10 revised to call get_cb_height ()

*/
error_reporting(E_ALL);	

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
echo (string) get_cb_height ();
?>
	