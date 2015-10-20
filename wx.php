<?php
/*
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);	

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10

function between ($in_str, $start_str, $end_str, $begin = 0) {		// returns string between two given values
	$temp1 = stripos ( $in_str, $start_str , $begin);				// position of start_str
	$temp2 = stripos ($in_str, $end_str, $temp1);					// position of end_str
 	return (!$temp1 || !$temp2)? FALSE: substr ($in_str, ($temp1 + strlen($start_str)), $temp2 - $temp1 - strlen($start_str));
	}

$note_lhe = "<note>";
$note_rhe = "</note>";
$headline_lhe = "<headline>";
$headline_rhe = "</headline>";
$description_lhe = "<description>";
$description_rhe = "</description>";

$wx_data = file_get_contents('wxalert.xml');
$note = between ($wx_data, $note_lhe, $note_rhe);
dump($note);
$headline = between ($wx_data, $headline_lhe, $headline_rhe);
$description = between ($wx_data, $description_lhe, $description_rhe);
dump($description);

?>
 
