<?php
/*
9/30/15 initial release
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
$ret_arr = array();

if(isset($_GET['the_error'])) {
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$text = quote_smart(base64_decode($_GET['the_error']));
	$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ajax_log` (`info` , `_when`) VALUES (" . $text . ", '" . $now . "')";
	$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_affected_rows() == 1) {
		$ret_arr[0] = 1;
		} else {
		$ret_arr[0] = 0;
		}
	} else {
	$ret_arr[0] = 99;
	}

print json_encode($ret_arr);
exit();
?>