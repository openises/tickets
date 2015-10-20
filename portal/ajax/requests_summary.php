<?php
@session_start();
require_once('../../incs/functions.inc.php');
require_once('../incs/portal.inc.php');

if(empty($_GET)) {
	exit();
	}
	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `request_date` >= (NOW() - INTERVAL 1 WEEK) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_week = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `request_date` >= (NOW() - INTERVAL 1 MONTH) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_month = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `request_date` >= (NOW() - INTERVAL 1 YEAR) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_year = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`request_date` IS NOT NULL OR `request_date` <> '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `accepted_date` >= (NOW() - INTERVAL 1 WEEK) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$accepted_week = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `accepted_date` >= (NOW() - INTERVAL 1 MONTH) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$accepted_month = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `accepted_date` >= (NOW() - INTERVAL 1 YEAR) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$accepted_year = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`accepted_date` IS NOT NULL OR `accepted_date` <> '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$accepted = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`declined_date` >= (NOW() - INTERVAL 1 WEEK) AND `accepted_date` != '' AND `tentative_date` != '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$declined_week = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`declined_date` >= (NOW() - INTERVAL 1 MONTH) AND `accepted_date` != '' AND `tentative_date` != '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$declined_month = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`declined_date` >= (NOW() - INTERVAL 1 YEAR) AND `accepted_date` != '' AND `tentative_date` != '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$declined_year = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE ((`declined_date` IS NOT NULL OR `declined_date` <> '') AND `accepted_date` != '' AND `tentative_date` != '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$declined = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `closed` >= (NOW() - INTERVAL 1 WEEK) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$closed_week = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `closed` >= (NOW() - INTERVAL 1 MONTH) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$closed_month = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `closed` >= (NOW() - INTERVAL 1 YEAR) AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$closed_year = mysql_num_rows($result);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`closed` IS NOT NULL OR `closed` <> '') AND `requester` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$closed = mysql_num_rows($result);

$ret_arr = array();

$ret_arr[0] = $requests_week;
$ret_arr[1] = $requests_month;
$ret_arr[2] = $requests_year;
$ret_arr[3] = $requests;
$ret_arr[4] = $accepted_week;
$ret_arr[5] = $accepted_month;
$ret_arr[6] = $accepted_year;
$ret_arr[7] = $accepted;
$ret_arr[8] = $declined_week;
$ret_arr[9] = $declined_month;
$ret_arr[10] = $declined_year;
$ret_arr[11] = $declined;
$ret_arr[12] = $closed_week;
$ret_arr[13] = $closed_month;
$ret_arr[14] = $closed_year;
$ret_arr[15] = $closed;

print json_encode($ret_arr);
exit();
?>