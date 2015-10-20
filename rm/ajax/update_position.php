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
	$id = $_GET['responder'];
	$tracking_id = "999_$id";
	$lat = $_GET['lat'];
	$lng = $_GET['lng'];
	$alt = ($_GET['altitude'] && $_GET['altitude'] != "") ? intval($_GET['altitude']) : 0;
	$heading = ($_GET['heading'] && $_GET['heading'] != "") ? intval($_GET['heading']) : 0;
	$speed = ($_GET['speed'] && $_GET['speed'] != "") ? intval($_GET['speed']) : 0;	

	$query3	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
	$result3 = mysql_query($query3) or do_error($query3, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$query4 = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `lat` = '$lat', `lng` ='$lng', `updated` = '$now' WHERE `id` = " . $id;
	$result4 = mysql_query($query4) or do_error($query4, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$query5 = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` = '$tracking_id'";		// remove prior track this device
	$result5 = mysql_query($query5);

	$query6 = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (source, latitude, longitude, speed, course, altitude, updated) VALUES ('$tracking_id', '$lat', '$lng', $speed, $heading, $alt, '$now')";		// 6/24/10
	$result6 = mysql_query($query6) or do_error($query6, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$query7 = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES ('$tracking_id', '$lat', '$lng', $speed, $heading, $alt, '$now', '$now')";
	$result7 = mysql_query($query7) or do_error($query7, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$response_code[] = ($result7) ? 700 : 799;		
	}
print json_encode($response_code);
exit();
?>