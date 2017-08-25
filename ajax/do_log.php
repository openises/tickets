<?php
/*
11/30/15	AJAX log handler	
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('../incs/functions.inc.php');

$code = (array_key_exists('code', $_GET)) ? $_GET['code'] : 90;
$ticket_id = (array_key_exists('ticket_id', $_GET)) ? $_GET['ticket_id'] : 0;
$responder_id = (array_key_exists('responder_id', $_GET)) ? $_GET['responder_id'] : 0;
$info = (array_key_exists('info', $_GET)) ? $_GET['info'] : "";
$facility_id = (array_key_exists('fac_id', $_GET)) ? $_GET['fac_id'] : 0;
$rec_facility_id = (array_key_exists('rec_fac_id', $_GET)) ? $_GET['rec_fac_id'] : 0;
$mileage = (array_key_exists('mileage', $_GET)) ? $_GET['mileage'] : 0;

//if($istest) {
//	dump ($_GET);
//	dump ($_POST);
//	}

do_log($code, $ticket_id, $responder_id, $info, $facility_id, $rec_facility_id, $mileage);		// call generic log table writer

print"";
?>