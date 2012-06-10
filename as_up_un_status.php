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
extract($_GET);
$now = time() - (get_variable('delta_mins')*60);
@session_start();							// 4/8/10 

$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= ";
$query .= quote_smart($frm_status_id) ;
$query .= ", `updated` = " . quote_smart(mysql_format_date($now));
$query .= ", `user_id` = " . $_SESSION['user_id'];
$query .= " WHERE `id` = ";
$query .= quote_smart($frm_responder_id);
$query .=" LIMIT 1";

$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

//	dump ($query);

do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_responder_id, $frm_status_id);
	
set_sess_exp();				// update session time
print date("H:i", $now) ;

//date("H:i", $row['as_of']) 
?>
