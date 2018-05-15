<?php
/*
11/28/12 Update requests table status field
*/
error_reporting(E_ALL);
//	file as_up_un_status.php

@session_start();
require_once('../incs/functions.inc.php');

extract($_GET);
$now = time() - (get_variable('delta_mins')*60);
print mysql_format_date($now) . "<BR />";
@session_start();

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status`= ";
$query .= quote_smart($status) ;
if($status == 'Tentative') {
	$query .= ", `tentative_date` = " . quote_smart(mysql_format_date($now));
	} elseif($status == 'Accepted') {
	$query .= ", `accepted_date` = " . quote_smart(mysql_format_date($now));
	} elseif($status == 'Resourced') {
	$query .= ", `resourced_date` = " . quote_smart(mysql_format_date($now));
	} elseif($status == 'Complete') {
	$query .= ", `completed_date` = " . quote_smart(mysql_format_date($now));	
	} elseif($status == 'Declined') {
	$query .= ", `declined_date` = " . quote_smart(mysql_format_date($now));
	} elseif($status == 'Closed') {
	$query .= ", `closed` = " . quote_smart(mysql_format_date($now));	
	}
$query .= ", `_on` = " . quote_smart(mysql_format_date($now));
$query .= ", `_by` = " . $_SESSION['user_id'];
$query .= " WHERE `id` = ";
$query .= quote_smart($the_id);
$query .=" LIMIT 1";

$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
//do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_responder_id, $frm_status_id);
	
set_sess_exp();				// update session time
print date("H:i", $now) ;
exit();
?>
