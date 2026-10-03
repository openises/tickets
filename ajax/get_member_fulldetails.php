<?php
/*
*/
error_reporting(E_ALL);
require_once '../incs/functions.inc.php';
require_login_or_401();
$id = (array_key_exists('id', $_GET)) ? strip_tags($_GET['id']) : 0;

if($id == 0) {
    exit();
    }

$ret_arr = get_member_full_details($id);
print json_encode($ret_arr);
?>
