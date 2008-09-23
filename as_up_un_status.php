<?php
error_reporting(E_ALL);
//	file as_up_un_status.php
require_once('./incs/functions.inc.php'); 
extract($_GET);
$now = time() - (get_variable('delta_mins')*60);

$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= ";
$query .= quote_smart($frm_status_id) ;
$query .= ", `updated` = " . quote_smart(mysql_format_date($now));
$query .= " WHERE `id` = ";
$query .= quote_smart($frm_responder_id);
$query .=" LIMIT 1";

$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

//	dump ($query);

do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_responder_id, $frm_status_id);
	
upd_lastin();				// update session time
print date("H:i", $now) ;

//date("H:i", $row['as_of']) 
?>
