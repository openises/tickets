<?php
/* Release Notes - tracking script for windows and windows mobile devices using uTrack application
5/10/11	Initial Release. Takes incoming data from tracked unit and puts into database
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
require_once('incs/functions.inc.php');		//7/28/10

$speed_type = 1;	//	Speed Measure - 1 = mph, 2 = kph, 3 = knots
$measurements = 1;	//	Measuments - 1 = feet, 2 = meters
$debug = 0;	//	writes incoming stream to text file if debug = 1
if($debug == 1) {
	write_track_log();
	}
if(!((isset($_GET['user'])) || (isset($_GET['username'])))) {
	print "Invalid Data String<br />Goodbye";
	exit;
	}
	
//---------Core Functions-----------------//
	
function write_track_log() {
	if (!$fp = fopen('tracker_log.txt', 'a'))
		print '<LI> <FONT CLASS="warn">Cannot open Tracker Log for writing</FONT>';
	else {
		$iclog = "";
        $iclog = "<pre>"; 
        $iclog .= print_r($_GET, 1); 
        $iclog .= "</pre>"; 
		fwrite($fp, $iclog);
		}
	fclose($fp);
	}

function parse_date_time($date, $time) {	//	If the source is GPSGate, this takes the incoming Date and Time and put's it into a format suitable for MySQL.
	date_default_timezone_set("UTC");
	$year = substr($date, 0, 4);
	$month = substr($date, 4, 2);
	$day = substr($date, 6, 2);
	$hour = substr($time, 0, 2);
	$minute = substr($time, 2, 2);
	$second = substr($time, 4, 2);
	$date_time = date("c", mktime($hour, $minute, $second, $month, $day, $year));
	return $date_time;
	}
	
function raw2lat_dez($degree) { 
	$myLat_dezimal = 0;
	$degreeParts=explode(".",$degree);
	if (count($degreeParts)) {
		$degreePart=substr($degreeParts[0],0,2);
		$minute=substr($degreeParts[0],2,2);
		$second=$degreeParts[1];
        $iNumDigits=count($degreeParts[1]);
        $myLat_dezimal = ($second/pow(10,$iNumDigits) + $minute)/60 + $degreePart;
		}
	return $myLat_dezimal;
}

function raw2long_dez($degree) { 
	$degreeParts=explode(".",$degree);
	$degreePart=substr($degreeParts[0],0,3);
	$minute=substr($degreeParts[0],3,2);
	$second=$degreeParts[1];  
    $iNumDigits=count($degreeParts[1]);
    $myLat_dezimal = ($second/pow(10,$iNumDigits) + $minute)/60 + $degreePart;
	return $myLat_dezimal;
}	

//-------End of functions


if(isset($_GET['pw'])) {	// is the client GPSGate	
	print "Client is GPSGate<br />";
	$alt_m = isset($_GET['altitude']) ? $_GET['altitude'] : "0";	//	Altitude in Meters
	$vel_kt = isset($_GET['speed']) ? $_GET['speed'] : "0";	//	Speed in Knots
	$head = isset($_GET['heading']) ? $_GET['heading']: "0.0";	//	 Heading in degrees
	$alt_ft = 3.2808399 * floatval($alt_m);	//	Altitidue in Feet
	$vel_mph = 1.15077945 * floatval($vel_kt);	// velocity in MPH
	$vel_kph = 1.852 * floatval($vel_kt);	// velocity in KPH 
	$date = isset($_GET['date']) ? $_GET['date']: date("Ymd");
	$time = isset($_GET['time']) ? $_GET['time']: date("His.000");
	$myTime = parse_date_time($date, $time);
	$myLongitude = isset($_GET['longitude']) ? $_GET['longitude']: 0;
	$myLatitude = isset($_GET['latitude']) ? $_GET['latitude']: 0;
	$myUser = isset($_GET['username']) ? $_GET['username']: 0;
	$myDirection = $head;
	switch ($speed_type) {
		case 1:
		$mySpeed = $vel_mph;
		break;
		case 2:
		$mySpeed = $vel_kph;
		break;
		case 3:
		$mySpeed = $vel_kt;
		break;
		default;
		$mySpeed = "0";
		}
	switch ($measurements) {
		case 1:
		$myAltitude = $alt_ft;
		break;
		case 2:
		$myAltitude = $alt_m;
		break;
		default;
		$myAltitude = "0";
		}	
	
} else {	//	not GPSGate
	print "Client is BT747 or uTrack<br />";
	if (isset($_GET['longitude_raw'])) {
		$myLongitude_raw = $_GET['longitude_raw'];
		if ($myLongitude_raw != "") {
			$myLongitude = raw2long_dez($myLongitude_raw);
			echo $myLongitude;
			}
		}
		
	if (isset($_GET['longitude'])) {
		$myLongitude = $_GET['longitude'];
		if  ($myLongitude == "") {
			exit;
			}
		}

	if (isset($_GET['latitude_raw'])) {
		$myLatitude_raw = $_GET['latitude_raw'];
		if ($myLatitude_raw != "") {
			$myLatitude = raw2lat_dez($myLatitude_raw);
			}
		}

	if (isset($_GET['latitude'])) {
		$myLatitude = $_GET['latitude'];
		if ($myLatitude == "") {
			exit;
			}
		}

	if (isset($_GET['time'])) {
		$myTime = $_GET['time'];
		} else {
		$date = date("Ymd");
		$time = date("His.000");
		$myTime = parse_date_time($date, $time);
		}

	if (isset($_GET['speed'])) {
		$mySpeed = $_GET['speed'];
		} else {
		$mySpeed = "0";
		}

	if (isset($_GET['alt'])) {
		$myAltitude = $_GET['alt'];
		} else {
		$myAltitude = "0";
		}

	if (isset($_GET['dir'])) {
		$myDirection = $_GET['dir'];
		} else {
		$myDirection = "0.0";
		}

	if (isset($_GET['user'])) {
		$myUser = $_GET['user'];
		} else {
		$myUser = "unknown";
		}
	}	//	end if client is GPSGate
	
$query = "DELETE FROM `$GLOBALS[mysql_prefix]remote_devices` WHERE `user` = '$myUser'";		// remove prior location for this device
$result = mysql_query($query);

$query = "INSERT INTO `$GLOBALS[mysql_prefix]remote_devices` (`lat`, `lng`, `time`, `speed`, `altitude`, `direction`, `user`) VALUES ($myLatitude, $myLongitude, '$myTime', $mySpeed, $myAltitude, $myDirection, '$myUser')";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$response_code = ($result) ? 100 : 99;

$return_code="";	
$return_code .= (($response_code) && ($response_code == 100 )) ? "Data Received and Inserted into database<BR />" : "Data Not received<BR />";

if($return_code != "") {
	print $return_code;
	} else {
	print "Mysql Error";
	}
?>
