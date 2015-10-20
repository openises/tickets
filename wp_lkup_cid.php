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
*/

@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10

$phone = (empty($_POST))? "4108498721": $_POST['phone'];

	function cid_lookup($phone )  {
		$aptStr = " Apt:";															
		function do_the_row($inRow) {		// for ticket or constituents data
			global $apartment, $misc;
			$outStr = $inRow['contact']	. ";";		// phone
			$outStr .= $inRow['phone']	. ";";			// phone
			$outStr .= $inRow['street'] . (stripos($inRow['street'], " Apt:"))? "" : $apartment;		// street and apartment - 3/13/10
			
			$outStr .= $inRow['street']	. $apartment . ";";			// street and apartment - 3/13/10
			$outStr .= $inRow['city']	. ";";			// city 
			$outStr .= $inRow['state']	. ";";			// state 	
			$outStr .= ";";								// frm_zip - unused 
			$outStr .=$inRow['lat']		. ";"; 
			$outStr .=$inRow['lng']		. ";"; 
			$outStr .=$misc			. ";"; 			// possibly empty - 3/13/10
			return 	$outStr;						// end function do_the_row()
			}
	
																// collect constituent data this phone no.
	
	$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$phone}'
		OR `phone_2`= '{$phone}' OR `phone_3`= '{$phone}' OR `phone_4`= '{$phone}'	LIMIT 1";
	
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$cons_row = (mysql_num_rows($result)==1)	? stripslashes_deep(mysql_fetch_array($result)): NULL;
	$apartment = 	(is_null($cons_row))		? "" : $aptStr . $cons_row['apartment']; 						// note brackets
	$misc = 		(is_null($cons_row))		? "" : $cons_row['miscellaneous'];
	
	$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `phone`= '{$phone}' ORDER BY `updated` DESC";			// 9/29/09
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$ret = mysql_num_rows($result) . ";";						// hits - common to each return
	
	if (mysql_num_rows($result)> 0) {							// build return string from newest incident data
		$row = stripslashes_deep(mysql_fetch_array($result));
		$ret .= do_the_row($row);
		}
	
	 elseif (!(is_null($cons_row))) {						// 3/13/10
		$ret .= do_the_row($cons_row);						// otherwise use constituents data
		}
	
	else {													// no priors or constituents - do WP
			$wp_key = get_variable("wp_key");				// 1/26/09
			$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=" . urlencode($phone) . ";api_key=". $wp_key;
			if(isset($phone)) {								// wp phone lookup
				$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=" . urlencode($phone) . ";api_key=". $wp_key;
				}
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
					print "-error 1";		// @fopen fails
					}
				}
					
		//						target: "Arnold Shore;(410) 849-8721;1684 Anne Ct;  Annapolis; MD;21401;lattitude;longitude; miscellaneous"
			if  (!(((strpos ($data, "Invalid")>0)) || ((strpos ($data, "Missing")>0)))){
		
				$aryk[0] = "<wp:firstname>";
				$aryk[1] = "<wp:lastname>";
				$aryk[2] = "<wp:fullphone>";
				$aryk[3] = "<wp:fullstreet>";
				$aryk[4] = "<wp:city>";
				$aryk[5] = "<wp:state>";
				$aryk[6] = "<wp:zip>";
				$aryk[7] = "<wp:latitude>";
				$aryk[8] = "<wp:longitude>";
	//			dump($aryk);
				$aryv = array(9);				// values
			//	First Last;(123) 456-7890;1234 Name Ct,  Where, NY 12345"
				$arys[0] = " ";		// firstname
				$arys[1] = ";";		// lastname
				$arys[2] = ";";		// fullphone
				$arys[3] = ";";		// fullstreet
				$arys[4] = ";";		// city
				$arys[5] = ";";		// state
				$arys[6] = ";";		// zip
				$arys[7] = ";";		// latitude
				$arys[8] = ";";		// longitude
				
				$pos = 0;					//
				for ($i=0; $i< count($aryk); $i++) {
					$pos = strpos ( $data, $aryk[$i], $pos);
					if ($pos === false) {								// bad
						$arys="";
						break;
						}
					$lhe = $pos+strlen($aryk[$i]);
					$rhe = strpos ( $data, "<", $lhe);
					$aryv[$i] = substr ( $data, $lhe , $rhe-$lhe );		// substr ( string, start , length )
					}		// end for ($i...)
	//			dump($aryv);
		
				if (!(empty($arys))) {									// 11/11/09
					for ($i=0; $i< count($aryk); $i++) {				// append return string to match count
						$ret .= $aryv[$i].$arys[$i];					// value + separator
						}			// end for ()
					unset($result);
					}
				}
		}					// end no priors
	
	//dump($ret);
	return $ret;
	}			// end function cid_lookup() 


$lookup_str =  cid_lookup($phone);
$query = "INSERT INTO `$GLOBALS[mysql_prefix]caller_id` (`call_str`, `lookup_vals`, `status`)  VALUES ( " . quote_smart(trim($phone)) . ", " .  quote_smart(trim($lookup_str)) . ", 0);";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
dump  (explode(";", $lookup_str)) ;
?>