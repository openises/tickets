<?php
require_once('../incs/functions.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$ret_arr = array();
$i = 0;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$ret_arr[0] = $num;

print json_encode($ret_arr);
exit();
?>