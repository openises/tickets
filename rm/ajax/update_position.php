<?php
/*
9/10/13 - New file, updates stored position data for mobile user - the tracking script
*/
error_reporting(E_ALL);

@session_start();
require_once('../../incs/functions.inc.php');		//7/28/10
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$date_part="";
$response_code = array();
if(($_GET['lat'] && $_GET['lat'] != "") && ($_GET['lng'] && $_GET['lng'] != "") && ($_GET['responder'] && $_GET['responder'] != "")) {
	$id = sanitize_int($_GET['responder']);
	$tracking_id = "999_$id";
	$lat = sanitize_string($_GET['lat']);
	$lng = sanitize_string($_GET['lng']);
	$alt = ($_GET['altitude'] && $_GET['altitude'] != "") ? intval($_GET['altitude']) : 0;
	$heading = ($_GET['heading'] && $_GET['heading'] != "") ? intval($_GET['heading']) : 0;
	$speed = ($_GET['speed'] && $_GET['speed'] != "") ? intval($_GET['speed']) : 0;

	$query3	= "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
	$result3 = db_query($query3);

	$query4 = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `lat` = ?, `lng` = ?, `updated` = ? WHERE `id` = ?";
	$result4 = db_query($query4, [$lat, $lng, $now, $id]);

	$query5 = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks_hh` WHERE `source` = ?";
	$result5 = db_query($query5, [$tracking_id]);

	$query6 = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh` (source, latitude, longitude, speed, course, altitude, updated) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$result6 = db_query($query6, [$tracking_id, $lat, $lng, $speed, $heading, $alt, $now]);

	$query7 = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	$result7 = db_query($query7, [$tracking_id, $lat, $lng, $speed, $heading, $alt, $now, $now]);
	$response_code[] = ($result7) ? 700 : 799;
	}
print json_encode($response_code);
exit();
?>