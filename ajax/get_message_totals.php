<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
require_once('../incs/messaging.inc.php');

set_time_limit(0);

//Outgoing Email Messages
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = 1 AND `read_status` = 0";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_new_msgs = mysql_num_rows($result);
if($num_new_msgs != 0) {
	$ogemails = $num_new_msgs;
	} else {
	$ogemails = 0;
	}

//Outgoing SMS Messages
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = 3 AND `read_status` = 0";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_new_msgs = mysql_num_rows($result);
if($num_new_msgs != 0) {
	$ogsms = $num_new_msgs;
	} else {
	$ogsms = 0;
	}
	
//Incoming Email Messages
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = 2 AND `read_status` = 0";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_new_msgs = mysql_num_rows($result);
if($num_new_msgs != 0) {
	$icemails = $num_new_msgs;
	} else {
	$icemails = 0;
	}
	
//Incoming SMS Messages
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = 4 AND `read_status` = 0";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_new_msgs = mysql_num_rows($result);
if($num_new_msgs != 0) {
	$icsms = $num_new_msgs;
	} else {
	$icsms = 0;
	}
	
//Incoming SMS Messages
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages_bin` WHERE `read_status` = 0";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_new_msgs = mysql_num_rows($result);
if($num_new_msgs != 0) {
	$wastemsg = $num_new_msgs;
	} else {
	$wastemsg = 0;
	}	

$num_sent = $ogemails + ogsms;
$num_incoming = $icemails + icsms;
$num_waste = $wastemsg;

$ret_arr[0] = $num_sent;
$ret_arr[1] = $num_incoming;
$ret_arr[2] = $num_waste;
print json_encode($ret_arr);
exit();
?>

		
