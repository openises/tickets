<?php
$failed = "failed";
if(empty($_GET)) {
	print $failed;
	exit();
	}
require_once('../incs/functions.inc.php');

do_login(basename(__FILE__));
error_reporting(E_ALL);
set_time_limit(90);
$filestore = substr(getcwd(), 0, -5) . "/files/";
if(empty($_GET)) {
	exit;
	}

$fileid = sanitize_int($_GET['id']);
$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `id` = ?";
$result = db_query($query, [$fileid]) or do_error($query, $query, db()->error, basename( __FILE__), __LINE__);
if(db_affected_rows() > 0) {
	$row = stripslashes_deep($result->fetch_assoc());
	$filename = $row['filename'];
	} else {
	$ret_arr[0] = 999;
	}

if(isset($filename)) {
	$query = "DELETE FROM `$GLOBALS[mysql_prefix]files` WHERE `id` = ?";
	$result = db_query($query, [$fileid]) or do_error($query, 'db_query() failed', db()->error, __FILE__, __LINE__);
	if(db_affected_rows() > 0) {
		$num = db_affected_rows();
		if($num > 0) {
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]files_x` WHERE `file_id` = ?";
			$result = db_query($query, [$fileid]) or do_error($query, 'db_query() failed', db()->error, __FILE__, __LINE__);
			if(db_affected_rows() > 0) {
				$num2 = db_affected_rows();
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