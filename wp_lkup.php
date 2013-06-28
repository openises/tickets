<?php
/*
8/7/08	initial release - replaces a Google search
1/18/09 POST replace GET
1/26/09 added functions.inc, get_variable for wp key 
10/1/09	revised return string to include match count as initial entry
3/13/10 constituents table handling added
4/30/10 accommodate add'l phone fields
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/6/10  Added test for internet available
9/2/10 corrected test for internet available
9/30/10 fix per JB email
6/9/2013 revised for general code cleanup, WP json data
*/

require_once('incs/functions.inc.php');		//7/28/10

$phone = (empty($_POST))? "1234560000": $_POST['phone'];
$vals = array("", "", "{$phone}", "", "", "", "", "", "", "", "");		// output values
	function do_the_row($inRow) {		// for ticket or constituents data
		global $vals;
		$vals[1] = $inRow['contact'];
		$apt_str = array_key_exists  ( "apartment", $inRow )? " # {$inRow['apartment']}" : "";
		$vals[3] = $inRow['street']	. $apt_str ;
		$vals[4] = $inRow['city'];
		$vals[5] = $inRow['state'];
		$vals[7] = $inRow['lat'];
		$vals[8] = $inRow['lng'];
		$misc_str = array_key_exists  ( "miscellaneous", $inRow ) ? $inRow['miscellaneous'] : "";
		$vals[9] = $misc_str;
//		return 	$outStr;						// end function do_the_row()
		}
															// collect constituent data this phone no.
$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE 
	`phone`= '{$phone}'	OR `phone_2`= '{$phone}' OR `phone_3`= '{$phone}' OR `phone_4`= '{$phone}'	
	LIMIT 1";

$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$cons_row = (mysql_affected_rows()==1) ? stripslashes_deep(mysql_fetch_array($result)): NULL;

$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `phone` = '{$phone}' ORDER BY `updated` DESC";			// 9/29/09
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

//dump(mysql_affected_rows());
if (mysql_affected_rows()> 0) {							// build return string from newest incident data
	$vals[0] = mysql_affected_rows();
	$vals[10] = 1;										// identify data source as ticket
	$row = stripslashes_deep(mysql_fetch_array($result));
	do_the_row($row);
	}

 elseif (!(is_null($cons_row))) {						// 3/13/10
	$vals[10] = 2;
	do_the_row($cons_row);						// otherwise use constituents data
	}

else {													// no priors or constituents - do WP
		$wp_key = trim(get_variable("wp_key"));				// 1/26/09
		
		$url = "http://api.whitepages.com/reverse_phone/1.0/?phone={$phone};api_key={$wp_key};outputtype=JSON";	// 4507191994
			
		$data = "";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec ($ch);
			curl_close ($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				@session_start();
				$err_str = "WhitePages connection fails @ " .  __LINE__;		// 6/8/2013
				if (!(array_key_exists ( $err_str, $_SESSION ))) {		// limit to once per session
					do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_str);
					$_SESSION[$err_str] = TRUE;		
					}
				echo "0;";
				return;
			}
		}				// end if/else CURL
				
	$jsonresp = json_decode ($data, true); 	
	
	if ( ! (array_key_exists ( "errors", $jsonresp ) ) ) {
		$vals[10] = "3";		// id WP as data source
		$vals[1] = array_key_exists (  "displayname", $jsonresp["listings"][0] ) ?
					$jsonresp["listings"][0]["displayname"] : "" ;
		$vals[3] = array_key_exists (  "fullstreet", $jsonresp["listings"][0]["address"] ) ?
					$jsonresp["listings"][0]["address"] ["fullstreet"] : "" ;
		$vals[4] = array_key_exists (  "city", $jsonresp["listings"][0]["address"] ) ?
					$jsonresp["listings"][0]["address"] ["city"] : "" ;
		$vals[5] = array_key_exists (  "state", $jsonresp["listings"][0]["address"] ) ?
					$jsonresp["listings"][0]["address"] ["state"] : "" ;
		$vals[6] = array_key_exists (  "zip", $jsonresp["listings"][0]["address"] ) ?
					$jsonresp["listings"][0]["address"] ["zip"] : "" ;
		$vals[7] = array_key_exists (  "latitude", $jsonresp["listings"][0]["geodata"] ) ?
					$jsonresp["listings"][0]["geodata"] ["latitude"] : "" ;
		$vals[8] = array_key_exists ( "longitude", $jsonresp["listings"][0]["geodata"] ) ?
					$jsonresp["listings"][0]["geodata"] ["longitude"] : "" ;			

		$val_str = implode(";", $vals);				
		}				// end no errors
	else {			
		echo "0;";				// report failure
		return;
		};	
	}					// end WP

$val_str = implode(";", $vals);			// success
print $val_str;		
return;
?>