<?php
/*
9/10/13 - new file, lists road condition alerts (text) for mobile page - sorts by distance from current position.
*/
@session_start();
require_once('../../incs/functions.inc.php');
$filter = "";

$ret_arr = array();
$i = 0;

if(!isset($_GET)) {
	$print = "<TABLE style='width: 100%;'><TR style='width: 100%;'><TD style='width: 100%;'>No Alerts</TD></TR></TABLE>";
	print $print;
	exit();
	}

$curr_lat = $_GET['lat'];
$curr_lng = $_GET['lng'];
$unit = (isset($_GET['unit'])) ? $_GET['unit'] : "M";

function distance($lat1, $lon1, $lat2, $lon2, $unit) { 
	if(($lat1 == 0 ) || ($lon1 == 0)) { 
		return 0; 
		}
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344); 
		} else if ($unit == "N") {
		return ($miles * 0.8684);
		} else {
		return $miles;
		}
	}

function subval_sort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}

$the_arr = array();

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

$sort = (isset($_GET['sort'])) ? clean_string($_GET['sort']) : NULL;
$way = (isset($_GET['way'])) ? clean_string($_GET['way']) : NULL;

$order = (isset($sort)) ? "ORDER BY `" . $sort . "`": "ORDER BY `_on`" ;
$order2 = (isset($way)) ? $way : "DESC";
$actr=0;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo` `r` WHERE `r`.`_on` >= (NOW() - INTERVAL 5 DAY) {$order} {$order2}";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$bgcolor = "#EEEEEE";
$num=mysql_num_rows($result);
if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$print = "<TABLE style='width: 100%;'><TR style='width: 100%;'><TD style='width: 100%;'>No Alerts</TD></TR></TABLE>";
	} else {
	$print = "<TABLE style='width: 100%;'>";
	$print .= "<TR style='width: 100%; font-weight: bold; color: #FFFFFF; background-color: #707070;'><TD style='width: 30%;'>LOCATION</TD><TD style='width: 60%;'>SUBJECT</TD><TD style='width: 10%;'>DIST</TD></TR>";
	$z=0;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$the_arr[$z]['id'] = $row['id'];
		$the_arr[$z]['address'] = stripslashes_deep(shorten($row['address'], 30));
		$the_arr[$z]['description'] = stripslashes_deep(shorten($row['description'], 30));
		$the_arr[$z]['when'] = format_date_2(strtotime($row['_on']));
		$the_arr[$z]['lat'] = $row['lat'];
		$the_arr[$z]['lng'] = $row['lng'];		
		$the_arr[$z]['distance'] = distance($curr_lat, $curr_lng, $row['lat'], $row['lng'], $unit);
		$z++;
		} // end while
	$the_arr = subval_sort($the_arr,'distance');
		
	foreach($the_arr AS $val) {
		$print .= "<TR style='width: 100%; cursor: pointer; background-color: " . $bgcolor . ";' onClick='get_alert(" . $val['id'] . ");'>";
		$print .= "<TD style='width: 30%;'>" . $val['address'] . "</TD>";
		$print .= "<TD style='width: 60%;'>" . $val['description'] . "</TD>";
		$print .= "<TD style='width: 10%;'>" . round($val['distance'], 2) . "</TD>";		
		$print .= "</TR>";	
		$bgcolor = ($bgcolor == "#EEEEEE") ? "#FEFEFE" : "#EEEEEE";		
		}
	$print .= "</TABLE>";		
	}				// end else
print $print;
exit();
?>
?>