<?php
/*
4/8/10 update user_id field
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);
//	file as_up_un_status.php

@session_start();

require_once($_SESSION['fip']);		//7/28/10
//snap(basename(__FILE__), __LINE__);
// Replaced extract — explicit variable assignments (Phase 2 cleanup)
$frm_status_id    = sanitize_int($_GET['frm_status_id'] ?? 0);
$frm_responder_id = sanitize_int($_GET['frm_responder_id'] ?? 0);
$frm_ticket_id    = sanitize_int($_GET['frm_ticket_id'] ?? 0);
$now = time() - (get_variable('delta_mins')*60);
@session_start();							// 4/8/10
$query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `un_status_id`= ?, `updated` = ?, `user_id` = ? WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$frm_status_id, mysql_format_date($now), $_SESSION['user_id'], $frm_responder_id]);

//	dump ($query);

do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_responder_id, $frm_status_id);
	
set_sess_exp();				// update session time
session_write_close();
print date("H:i", $now) ;

//date("H:i", $row['as_of']) 
?>
