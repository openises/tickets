<?php
@session_start();
require_once('incs/functions.inc.php');		//7/28/10

// This function retrieves the api output using fsockopen().
// The advantage over fopen/fread is that a timeout can be set so that your webpage does not hang on the API if it takes too long
// The disadvantage is that the http headers have to be parsed and stripped off, which this code does for you.

	function loadapijson($domain, $path, $timeout) {
		
		$fp = fsockopen($domain, 80, $errno, $errstr, $timeout);
		if($fp) {		// make request	
			$out = "GET $path HTTP/1.1\r\n";
			$out .= "Host: $domain\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);			
	
			$resp = "";		// collect response data
			while (!feof($fp)) {
				$resp .= fgets($fp, 128);
				}
			fclose($fp);
	
			$status_regex = "/HTTP\/1\.\d\s(\d+)/";						// check status is 200
			if(preg_match($status_regex, $resp, $matches) && $matches[1] == 200) {
				$parts = explode("\r\n\r\n", $resp);					// load xml as object
				// return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $parts[1]); // Return the http body, with problem chars removed
				return $parts[1]; 										// Return the http body			
				}
			}				 // end if($fp) 
		return false;		
		}				// end 	function loadapijson()

header('Content-Type: text/html'); // For demo use only -- output to browser will be html

$apidomain = "api.whitepages.com";
$apikey = "729c1a751fd3d2428cfe2a7b43442c64"; // Put your API key here
$apisearch = "reverse_phone"; // The search type, from WP API docs
//$apiphone = "2125867000"; // The phone number to reverse-search -- New York Hilton -- many listings
//$apiphone = "4103533986"; // The phone number to reverse-search -- UPS Store -- one listing
//$apiphone = "7755888200"; // The phone number to reverse-search -- UPS Store -- one listing
$apiphone = "4102242850"; // The phone number to reverse-search -- UPS Store -- one listing
$apipath = "/$apisearch/1.0/?phone=$apiphone;api_key=$apikey;outputtype=JSON";
$timeout = 10; // This is the max timeout limit for the API to respond, in seconds. Tune as needed

$apiresponse = loadapijson ($apidomain, $apipath, $timeout);	//
if($apiresponse != false) {
	// xml doc loaded
	$jsonresp = json_decode ($apiresponse, true); // Output is placed in an array
	
	dump($jsonresp["errors"]);							// ! empty => errors
	
	dump($jsonresp['listings'][0]["geodata"]["longitude"]);
	dump($jsonresp['listings'][0]["geodata"]["latitude"]);
	dump($jsonresp['listings'][0]["business"]["businessname"]);
	@dump($jsonresp['listings'][0]["address"]["fullstreet"]);
	dump($jsonresp['listings'][0]["address"]["city"]);
	dump($jsonresp['listings'][0]["address"]["state"]);
	@dump($jsonresp['listings'][0]["phonenumbers"][0]["carrier_only"]);
	@dump($jsonresp['listings'][0]["people"][0]["firstname"]);
	@dump($jsonresp['listings'][0]["people"][0]["middlename"]);
	@dump($jsonresp['listings'][0]["people"][0]["lastname"]);
	
	dump($jsonresp['listings'][0]["phonenumbers"][0]["fullphone"]);
	dump(__LINE__);
	
	dump($jsonresp); // This displays the response on the web page for demo use only.
	echo "<H1>JSON parsed output: </H1>";
	echo "<BR><BR><H1>Raw JSON response: </H1><BR>";
	echo "$apiresponse<BR>"; // This displays the response on the web page for demo use only.
	echo "<BR><BR><H1>Parsed variables: </H1><BR>";
	
	// Parse out variables by referring to the associative array returned, subscripted by the names of the indented dimensions
	
	// There is only one row in these array entries.
	echo "Result Type: " . $jsonresp["result"]["type"] . " <BR>";
	echo "Linkexpires: " . $jsonresp["meta"]["linkexpiration"] . " <BR>";
	echo "Home URl: " . $jsonresp["meta"]["searchlinks"]["homepage"]["url"] . " <BR>";
	
	// But there may be multiple rows, hence need to iterate
	foreach ($jsonresp["listings"] as $listing) {
		dump(__LINE__);

		echo "Addr Type: " . $listing["listingmeta"]["type"] . "<BR>";
		echo "Longitude: " . $listing["geodata"]["longitude"] . "<BR>";
		echo "Latitude: " . $listing["geodata"]["latitude"] . "<BR>";
		echo "Phone Owner: " . $listing["displayname"] . "<BR>";
		echo "Address : " . $listing["address"]["house"] . " " . $listing["address"]["street"] . " <BR>";
		echo "City/State : " . $listing["address"]["city"] . ", " . $listing["address"]["state"] . $listing["address"]["zip"] . "<BR><BR>";
		}

	} else {			// failed. show friendly error message.
		echo "<H2>json error</H2><BR>";
		}

?>