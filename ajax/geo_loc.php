<?php
/*
*/
error_reporting(E_ALL);	
require_once('../incs/functions.inc.php');

$ret_arr = array();
$addr = urlencode($_GET['addr']);
$api_key = get_variable('gmaps_api_key');
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
if($https) {
	$the_url = "https://maps.googleapis.com/maps/api/geocode/json?" . $key_str . "&address={$addr}&sensor=false";
	} else {
	$the_url = "https://maps.googleapis.com/maps/api/geocode/json?" . $key_str . "&address={$addr}&sensor=false";		
	}
$temp = array();
$json = get_remote($the_url,TRUE);							// arrives decoded 4/9/11
$temp = objectToArray($json);
$ret_arr = array();
$ret_arr[0] = $temp["results"][0]["geometry"]["location"]["lat"];
$ret_arr[1] = $temp["results"][0]["geometry"]["location"]["lng"];
print json_encode($ret_arr);
?>
