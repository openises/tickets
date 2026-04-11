<?php
require_once('./incs/functions.inc.php');

$id = sanitize_int($_GET['id']);
$ret_arr = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$id]);
$row = $result ? $result->fetch_assoc() : null;
$ret_arr[0] = $row['un_status_id'];
print json_encode($ret_arr);
?>