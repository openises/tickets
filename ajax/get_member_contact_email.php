<?php
/*
*/
error_reporting(E_ALL);	
require_once('../incs/functions.inc.php');
$id = (array_key_exists('id', $_GET)) ? strip_tags($_GET['id']) : 0;

if($id == 0) {
	exit();
	}

//$ret_arr[0] = get_mdb_email($id);
//$ret_arr[1] = get_mdb_cellphone($id);
//$ret_arr[2] = get_mdb_homephone($id);
//$ret_arr[3] = get_mdb_workphone($id);
//$ret_arr[4] = get_mdb_smsgid($id);
//$ret_arr[5] = get_mdb_contactname($id);
$ret_arr[0] = get_contact_via($id);
print json_encode($ret_arr);
?>
