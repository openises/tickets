<?php
@session_start();
require_once('../../incs/functions.inc.php');
require_once('../incs/portal.inc.php');

//	requests not yet accepted
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`accepted_date` = '' OR `accepted_date` IS NULL) AND `status` != 'Cancelled' AND (`closed` = '' OR `closed` IS NULL)";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_na = mysql_num_rows($result);

//	requests accepted and not resourced
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `accepted_date` <> '' AND (`resourced_date` = '' OR `resourced_date` IS NULL) AND (`closed` = '' OR `closed` IS NULL)";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_nr = mysql_num_rows($result);

// requests resourced and not complete
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `resourced_date` <> '' AND (`completed_date` = '' OR `completed_date` IS NULL) AND (`closed` = '' OR `closed` IS NULL)";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_rnc = mysql_num_rows($result);

// requests completed but not closed
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `completed_date` <> '' AND (`closed` = '' OR `closed` IS NULL)";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_ncl = mysql_num_rows($result);

// total requests not yet closed
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `closed` = '' OR `closed` IS NULL";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_tnc = mysql_num_rows($result);

// total open requests
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `status` = 'Open'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_open = mysql_num_rows($result);

// total accepted requests
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `status` = 'Accepted'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_acc = mysql_num_rows($result);

// total tentative requests
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `status` = 'Tentative'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_ten = mysql_num_rows($result);

// total cancelled requests
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `status` = 'Cancelled'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_can = mysql_num_rows($result);

// total declined requests
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE (`declined_date` <> '' OR `declined_date` IS NULL) AND `status` = 'Declined'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$requests_dec = mysql_num_rows($result);

$ret_arr = array();

$ret_arr[0] = $requests_na;
$ret_arr[1] = $requests_nr;
$ret_arr[2] = $requests_rnc;
$ret_arr[3] = $requests_ncl;
$ret_arr[4] = $requests_tnc;
$ret_arr[5] = $requests_open;
$ret_arr[6] = $requests_acc;
$ret_arr[7] = $requests_ten;
$ret_arr[8] = $requests_can;
$ret_arr[9] = $requests_dec;

print json_encode($ret_arr);
exit();
?>