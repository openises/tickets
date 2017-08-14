<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */
$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_array($result))) {
	$ret_arr[$row['id']] = $row['text_color'];
	}

//dump($ret_arr);
print json_encode($ret_arr);
exit();
?>