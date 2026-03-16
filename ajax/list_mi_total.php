<?php
require_once('../incs/functions.inc.php');
set_time_limit(90);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$ret_arr = array();
$i = 0;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00'";
$result = db_query($query);
$num=$result->num_rows;
$ret_arr[0] = $num;

print json_encode($ret_arr);
exit();
?>