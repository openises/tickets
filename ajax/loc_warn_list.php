<?php
/*
9/10/13 - New file, gets stored warn locations for display in new and edit ticket pages
11/18/13 - Fixed inccorrect <DENTER> !!
*/
//	error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if(empty($_GET)) {
	exit();
	}

$in_lat = $_GET['lat'];
$in_lng = $_GET['lng'];
$proximity = intval(get_variable('warn_proximity'))/10;
$unit = get_variable('warn_proximity_units');

function distance($lat1, $lon1, $lat2, $lon2, $unit) { 
	if(($lat1 == 0 ) || ($lon1 == 0)) { 
		return 0; 
		}
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
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

$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` ORDER BY `id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) > 0) {
	$i=0;
	while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$ret_arr[$i][0] = $row['id'];
		$ret_arr[$i][1] = $row['title'];
		$ret_arr[$i][2] = $row['street'];
		$ret_arr[$i][3] = $row['city'];
		$ret_arr[$i][4] = $row['state'];
		$ret_arr[$i][5] = $row['lat'];
		$ret_arr[$i][6] = $row['lng'];
		$ret_arr[$i][7] = $row['description'];
		$the_dist = distance($in_lat, $in_lng, $row['lat'], $row['lng'], $unit);
		$ret_arr[$i][8] = round($the_dist,1);
		$ret_arr[$i][9] = get_owner($row['_by']);
		$ret_arr[$i][10] = format_date_2(strtotime($row['_on']));
		$i++;
		}
	}

$out_arr = array();

$z = 0;	
foreach($ret_arr as $val) {
	if($val[8] < $proximity) {
		$out_arr[$z][0] = $val[0];
		$out_arr[$z][1] = $val[1];
		$out_arr[$z][2] = $val[2];
		$out_arr[$z][3] = $val[3];
		$out_arr[$z][4] = $val[4];
		$out_arr[$z][5] = $val[7];
		$out_arr[$z][6] = $val[8];
		$out_arr[$z][7] = $val[9];
		$out_arr[$z][8] = $val[10];
		$z++;
		}
	}
if(empty($out_arr)) { $out_arr[0] = "No Warnings Found"; $count = 0;} else { $count = count($out_arr); }

$print = "<CENTER><TABLE width='100%'>";
if($out_arr[0] == "No Warnings Found") {
	$print .= "<TR><TD>No Warnings Found</TD></TR>";
	} else {
	$print .= "<TR class='heading' style: width: 100%; color: #FFFFFF;'><TD class='heading' COLSPAN=99 style='text-align: center; background-color: red;'>Location Warnings</TD></TR>";	
	$print .= "<TR class='heading' style: width: 100%; color: #000000; background-color: #EFEFEF; font-weight: bold;'><TD style='background-color: #EFEFEF;'>Title</TD><TD style='background-color: #EFEFEF;'>Street</TD><TD style='background-color: #EFEFEF;'>City</TD><TD style='background-color: #EFEFEF;'>State</TD><TD style='background-color: #EFEFEF;'>Distance</TD><TD style='background-color: #EFEFEF;'>Date</TD></TR>";
	$bgcol = "#CECECE";
	foreach($out_arr as $output) {
		$print .= "<TR style='background-color: " . $bgcol . ";' onClick='wl_win(" . $output[0] . ");'>";
		$print .= "<TD style='color: #000000;'>" . $output[1] . "</TD>";
		$print .= "<TD style='color: #000000;'>" . $output[2] . "</TD>";
		$print .= "<TD style='color: #000000;'>" . $output[3] . "</TD>";
		$print .= "<TD style='color: #000000;'>" . $output[4] . "</TD>";
		$print .= "<TD style='color: #000000;'>" . $output[6] . "</TD>";
		$print .= "<TD style='color: #000000;'>" . $output[8] . "</TD>";
		$print .= "</TR>";
		if($bgcol == "#CECECE") { $bgcol = "#DEDEDE"; } else { $bgcol = "#CECECE"; }
		}
	}
$print .= "</TABLE></CENTER>";	//	11/18/13

$ret = array();
$ret[0] = $count;
$ret[1] = $print;

print json_encode($ret);
exit();
?>