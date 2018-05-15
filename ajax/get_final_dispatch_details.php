<?php
/*
*/
error_reporting(E_ALL);	
session_start();						// 
session_write_close();
require_once('../incs/functions.inc.php');
$id = (array_key_exists('id', $_GET)) ? strip_tags($_GET['id']) : 0;

if($id == 0) {
	exit();
	}
	
notify_user($id, 4);
//$ret_arr = get_disp_closure_summary($id);
//print json_encode($ret_arr);
?>
