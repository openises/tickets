<?php
/*
*/
error_reporting(E_ALL);	
require_once('../incs/functions.inc.php');
$id = (array_key_exists('id', $_GET)) ? strip_tags($_GET['id']) : 0;

if($id == 0) {
	exit();
	}

$ret_arr[0] = get_contact_via($id);
print json_encode($ret_arr);
?>
