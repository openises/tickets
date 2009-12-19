<?php
$istest = FALSE;
$istest=false;
require_once('../functions.inc.php');		// some irv_functions
require_once("../nusoap/lib/nusoap.php");
$server = "www.ontok.com";   				// subscribers are emailed a private server with faster response times
$key = "";  								// free users do not need key (limit 10 addresses), subscribers w/key (limit 200  addresses)
define ("tab", "\t");

// ex:	$q = array("4954 Heather Glenn Dr Houston, TX", "55 Grove Street Somerville MA 02144");
//		$temp = str_replace("+", " ", $_GET['addr']);			// i.e., urldecode	urldecode()
		$temp = urldecode($_GET['addr']);			
		$q = array($temp);										// Array is required
		$soapclient = new soapclient("http://$server/geocode/soap");
		$mapData = $soapclient->call("geocode",  array('key'=> $key, 'q'=> $q), "", "");
		$lat = $mapData[0]["lat"];
		$lon = $mapData[0]["long"];
		if ((is_float($lat)) && (!floatval(lat) == 0.0)) {
			$ret = "-Lookup failed";
			}
		else {
			$ret = tab . $lat . tab .  $lon . tab ;
			}
		print $ret;
	
//dump ($ret);
//print eol . "<h3>End</h3>";
?>
