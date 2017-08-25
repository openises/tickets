<?php
$failed = "failed";
if(empty($_GET)) {
	print $failed;
	exit();
	}
require_once('../incs/functions.inc.php');

do_login(basename(__FILE__));
error_reporting(E_ALL);	
set_time_limit(0);
$filestore = substr(getcwd(), 0, -5) . "/files/";
if(empty($_GET)) {
	exit;
	}
	
$fileid = $_GET['id'];
$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `id` = " . $fileid;
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
if(mysql_affected_rows() > 0) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$filename = $row['filename'];
	} else {
	$ret_arr[0] = 999;
	}

if(isset($filename)) {
	$query = "DELETE FROM `$GLOBALS[mysql_prefix]files` WHERE `id` = " . $fileid;
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	
	if(mysql_affected_rows() > 0) {
		$num = mysql_affected_rows();
		if($num > 0) {
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]files_x` WHERE `file_id` = " . $fileid;
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	
			if(mysql_affected_rows() > 0) {
				$num2 = mysql_affected_rows();		
				} else {
				$num2 = 0;
				}
			}
		} else {
		$num = 0;
		}
		

	if(($num > 0) || ($num2 > 0)) {
		$file = $filestore . $filename;
		if(unlink($file)) {
			$ret_arr[0] = 100;
			} else {
			$ret_arr[0] = 999;
			}
		} else {
		$ret_arr[0] = 999;
		}
	}
	
print json_encode($ret_arr);
exit();
?>