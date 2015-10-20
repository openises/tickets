<?php
require_once('./incs/functions.inc.php');

extract($_GET);
$ret_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . strip_tags($id) . " LIMIT 1";
$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
$row = mysql_fetch_assoc($result);
$ret_arr[0] = $row['un_status_id'];
print json_encode($ret_arr);
?>