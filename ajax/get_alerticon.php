<?php
/*
1/3/14 - new file, lists road condition alerts for plotting on situation screen map
*/
require_once('../incs/functions.inc.php');

$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` WHERE `id` = " . $_GET['id'];
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$ret_arr[0] = $row['icon'];

print json_encode($ret_arr);
exit();
?>
