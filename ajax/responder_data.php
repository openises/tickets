<?php
/*
9/10/13 - new file, lists responder locations
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');

if(empty($_GET)) {
	exit;
	}
$ret_arr=array();	
$where = "WHERE `mobile` = 1";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` " . $where . " ORDER BY `id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows() == 0) { 
	$ret_arr[0][0] = 0;
	} else {
	$i = 0;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$ret_arr[$i][0] = $row['id'];
		$ret_arr[$i][1] = $row['un_status_id'];
		$ret_arr[$i][2] = $row['icon_str'];
		$ret_arr[$i][3] = $row['handle'];
		$ret_arr[$i][4] = $row['lat'];
		$ret_arr[$i][5] = $row['lng'];
		$ret_arr[$i][6] = $row['ring_fence'];
		$ret_arr[$i][7] = $row['excl_zone'];
		$ret_arr[$i][8] = $row['contact_via'];
		$ret_arr[$i][9] = $row['type'];
		$ret_arr[$i][10] = $row['updated'];
		$ret_arr[$i][11] = $row['status_updated'];
		$ret_arr[$i][12] = $row['user_id'];
		$i++;
		}
	}	//	end else

print json_encode($ret_arr);
exit();
?>