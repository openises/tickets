<?php
/*
*/
error_reporting(E_ALL);	
require_once('../../incs/functions.inc.php');

$ret_arr = array();
$addr = urlencode($_GET['addr']);

$the_url = "http://maps.googleapis.com/maps/api/geocode/json?address={$addr}&sensor=false";
$temp = array();
$json = get_remote($the_url);							// arrives decoded 4/9/11
$temp = objectToArray($json);

$ret_arr = array();
$ret_arr[0] = $temp["results"][0]["geometry"]["location"]["lat"];
$ret_arr[1] = $temp["results"][0]["geometry"]["location"]["lng"];
print json_encode($ret_arr);
exit();
?>
