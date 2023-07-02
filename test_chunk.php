<?php
error_reporting(E_ALL);
require_once('incs/functions.inc.php');

$the_key = "1567633552";
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile`= 1 AND `aprs`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
// print $query . "<BR />";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
if (1 > 0) {			//	
	$call_arr = array();
//	while ($row = @mysql_fetch_assoc($result)) {
//		$call_arr[] = $row['callsign'];
//		}
	$call_arr = array("GB17865","GB57223","GB67117","MO34527","MO45226","MO65226","GB77992","GB81662");
	$allcall_arr = array_chunk($call_arr, 20);
	
	foreach($allcall_arr as $temp_arr) {
		dump($temp_arr);
		
//		Each group of 20 callsigns	

		$call_str = implode(",", $temp_arr);			
		$the_url = "https://api.aprs.fi/api/get?name={$call_str}&what=loc&apikey={$the_key}&format=json";
		print $the_url . "<BR />";
		}
	}
?>