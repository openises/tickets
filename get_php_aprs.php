<?php
	
@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
do_aprs();
?>
