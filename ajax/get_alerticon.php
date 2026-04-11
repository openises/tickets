<?php
/*
1/3/14 - new file, lists road condition alerts for plotting on situation screen map
*/
require_once '../incs/functions.inc.php';

$ret_arr = array();
$id = sanitize_int($_GET['id']);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` WHERE `id` = ?";
$result = db_query($query, [$id]) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
$row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
$ret_arr[0] = $row['icon'];

print json_encode($ret_arr);
exit();
?>
