<?php
/*
*/
error_reporting(E_ALL);
set_time_limit(0);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
$sit_version = (intval(get_variable('full_sit_v2')) == 0) ? 1 : 2;

do_login(basename(__FILE__));

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

switch($sit_version) {
	case 1:
	include("./forms/full_sit_screen.php");
	break;
	
	case 2:
	include("./forms/full_sit_screen_alt.php");
	break;
	
	default:
	include("./forms/full_sit_screen.php");
	}
