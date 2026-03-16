<?php
/*
3/15/11 Created from as_up_un_status.php to allow change of facility status through situation screen 
*/
error_reporting(E_ALL);
//	file as_up_fac_status.php

@session_start();

require_once(isset($_SESSION['fip']) ? $_SESSION['fip'] : './incs/functions.inc.php');
//snap(basename(__FILE__), __LINE__);
$frm_status_id = sanitize_int($_GET['frm_status_id']);
$frm_responder_id = sanitize_int($_GET['frm_responder_id']);
$now = time() - (get_variable('delta_mins')*60);

$query = "UPDATE `{$GLOBALS['mysql_prefix']}facilities` SET `status_id`= ?, `updated` = ?, `user_id` = ? WHERE `id` = ? LIMIT 1";
$result = db_query($query, [$frm_status_id, mysql_format_date($now), $_SESSION['user_id'], $frm_responder_id]);

//	dump ($query);

do_log($GLOBALS['LOG_FACILITY_STATUS'], 0, 0, $frm_status_id, $frm_responder_id);
set_sess_exp();				// update session time
session_write_close();
print date("j H:i", $now) ;
//date("H:i", $row['as_of']) 
?>
