<?php
/*
update_responder_status.php - used by fac_routes.php to change location of responder to a facility and add facility located at to responder table field "at_facility"
09/03/15 - new file
*/
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$istest = FALSE;

$fac_id = $_GET['fac_id'];
$resp_id = $_GET['resp_id'];
$new_status = $_GET['status'];
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $resp_id . " LIMIT 1";
$result = mysql_query($query);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$existing_status = $row['un_status_id'];
$response = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $fac_id . " LIMIT 1";
$result = mysql_query($query);
if(mysql_num_rows($result) >= 1) {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$lat = $row['lat'];
		$lng = $row['lng'];
		}
	}
	
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		
if($new_status == $existing_status) {
	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
		`lat`= " . 			$lat . ",
		`lng`= " . 			$lng . ",
		`at_facility`= " . 	quote_smart(trim($fac_id)) . ",
		`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
		`updated`= " . 		quote_smart(trim($now)) . "
		WHERE `id`= " . 	quote_smart(trim($resp_id)) . ";";
	} else {
	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
		`lat`= " . 			$lat . ",
		`lng`= " . 			$lng . ",
		`un_status_id`= " . $new_status . ",
		`at_facility`= " . 	quote_smart(trim($fac_id)) . ",
		`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
		`updated`= " . 		quote_smart(trim($now)) . ",
		`status_updated`= " . 		quote_smart(trim($now)) . "
		WHERE `id`= " . 	quote_smart(trim($resp_id)) . ";";
	}
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_affected_rows() == 1) {
	$response[0] = 1;
	} else {
	$response[0] = 0;
	}
	
print json_encode($response);
exit();
?>