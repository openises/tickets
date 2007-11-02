<?php
require_once("../nusoap/lib/nusoap.php");
define("tab",  "\t");
if (!empty($_POST)) extract($_POST);
	    else if (!empty($HTTP_POST_VARS)) extract($HTTP_POST_VARS);
if (!empty($_GET)) extract($_GET);	    

function dump($variable) {
	echo "<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>";
	}
$server = "www.ontok.com";   				// subscribers are emailed a private server with faster response times
$key = "";  								// free users do not need key (limit 10 addresses), subscribers w/key (limit 200  addresses)
//	$phone = "4108498721";
	
// ex: 	http://www.whitepages.com/10583/search/ReversePhone?phone=410-849-8721
	$baseurl = "http://www.whitepages.com/10583/search/ReversePhone?phone=";
	$url = $baseurl . $phone;
	$indata="";
	if ($fp = @fopen($url, "r")) {
	    while (!feof($fp)) $indata .= fgets($fp, 128);
	    fclose($fp);
		}
	else { 
		print "-err 2";									// whitepages file copy fails
		}
	
	$thestr = '<div id="results_single_listing">';
	$thelhe = strpos ( $indata, $thestr , 0);				// strpos(haystack, needle, offset)
	if (!$thelhe){
		print "-err 3 " . $thestr;									// phone lookup fails
		}
	else {
		$temp = substr($indata, $thelhe, 500);				// substr (string, start, length )
		$temp1 = str_replace("<", tab ."<", $temp);			// tab separator will survive strip_tags()
		$temp2 = strip_tags($temp1);
		$temp3 = explode (tab, $temp2 ,7);					// 2-> 'Last, First', 4 ->'Street addr', 6 -> 'City, St zip+4'
		$temp4 = substr($temp3[6], 0, strpos($temp3[6], tab, 0));
		$temp5 = explode (comma, $temp4);					// into city, state zip
		unset ($indata);

// ex:	$q = array("4954 Heather Glenn Dr Houston, TX", "55 Grove Street Somerville MA 02144");
		$q = array($temp3[4] . " " . substr($temp3[6], 0, strpos($temp3[6], tab, 0)));		// up to tab
		$soapclient = new soapclient("http://$server/geocode/soap");
		$mapData = $soapclient->call("geocode",  array('key'=> $key, 'q'=> $q), "", "");
		if (!$thelhe){
			print "-err 4";								// geocode fails
			}
		else {
			$name = $temp3[2];
			$addr = $temp3[4];
			$city = $temp5[0];
			$state = substr($temp5[1], 0, 2);
			$zip = substr($temp5[1], 3, 5);
			$lat = $mapData[0]["lat"];
			$lon = $mapData[0]["long"];
			
			$ret = tab . $name . tab .  $addr . tab .  $city . tab .  $state . tab . $zip . tab .  $lat . tab .  $lon . tab .$phone . tab;
			print $ret;
//			1=> name,  2=>address,  3=>city,  4=>state,  5=>zip,  6=>lat,  7=> lon
			}				// geocode OK
		}				// phone lookup OK
	
//dump ($ret);
//print eol . "<h3>End</h3>";
?>
