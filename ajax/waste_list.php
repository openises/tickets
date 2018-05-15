<?php
/*
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php'); 
@session_start();

$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]waste_basket_m`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) != 0) {
	$i=0;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$ret_arr[$i][] = $row['id'];
		$ret_arr[$i][] = $row['field1'];
		$ret_arr[$i][] = $row['field2'];
		$ret_arr[$i][] = $row['field4'];
		$i++;
		}
	} else {
	$ret_arr = "";
	}
print json_encode($ret_arr);