<?php
/*
11/28/12 Update requests table status field
*/
error_reporting(E_ALL);
//	file as_up_un_status.php

@session_start();
require_once('../incs/functions.inc.php');

$status = sanitize_string($_GET['status']);
$the_id = sanitize_int($_GET['the_id']);
$now = time() - (get_variable('delta_mins')*60);
print mysql_format_date($now) . "<BR />";
@session_start();

$query = "UPDATE `{$GLOBALS['mysql_prefix']}requests` SET `status`= ?";
$params = [$status];

if($status == 'Tentative') {
	$query .= ", `tentative_date` = ?";
	$params[] = mysql_format_date($now);
	} elseif($status == 'Accepted') {
	$query .= ", `accepted_date` = ?";
	$params[] = mysql_format_date($now);
	} elseif($status == 'Resourced') {
	$query .= ", `resourced_date` = ?";
	$params[] = mysql_format_date($now);
	} elseif($status == 'Complete') {
	$query .= ", `completed_date` = ?";
	$params[] = mysql_format_date($now);
	} elseif($status == 'Declined') {
	$query .= ", `declined_date` = ?";
	$params[] = mysql_format_date($now);
	} elseif($status == 'Closed') {
	$query .= ", `closed` = ?";
	$params[] = mysql_format_date($now);
	}
$query .= ", `_on` = ?";
$params[] = mysql_format_date($now);
$query .= ", `_by` = ?";
$params[] = $_SESSION['user_id'];
$query .= " WHERE `id` = ? LIMIT 1";
$params[] = $the_id;

$result = db_query($query, $params);
//do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_responder_id, $frm_status_id);

set_sess_exp();				// update session time
print date("H:i", $now) ;
exit();
?>
