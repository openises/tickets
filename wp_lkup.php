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
5/11/11
*/

require_once('incs/functions.inc.php');		//7/28/10

$phone = (empty($_POST))? "4108498721": $_POST['phone'];

	function do_the_row($inRow) {		// for ticket or constituents data
		global $apartment, $misc, $aptStr;
		$outStr = $inRow['contact']	. ";";		// phone
		$outStr .= $inRow['phone']	. ";";			// phone
		$outStr .= $inRow['street'] . (stripos($inRow['street'],$aptStr))? "" : $apartment;		// street and apartment - 3/13/10
		
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
$aptStr = " Apt:";															
//$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$phone}' LIMIT 1";			// 4/30/10
$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$phone}'
	OR `phone_2`= '{$phone}' OR `phone_3`= '{$phone}' OR `phone_4`= '{$phone}'	LIMIT 1";

$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$cons_row = (mysql_affected_rows()==1) ? stripslashes_deep(mysql_fetch_array($result)): NULL;
$apartment = 	(is_null($cons_row))? "" : $aptStr . $cons_row['apartment']; 						// note brackets
$misc = 		(is_null($cons_row))? "" : $cons_row['miscellaneous'];

$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `phone`= '{$phone}' ORDER BY `updated` DESC";			// 9/29/09
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$ret = mysql_affected_rows() . ";";						// common to each  return
//dump(mysql_affected_rows());
if (mysql_affected_rows()> 0) {							// build return string from newest incident data
	$row = stripslashes_deep(mysql_fetch_array($result));
	$ret .= do_the_row($row);
	}

 elseif (!(is_null($cons_row))) {						// 3/13/10
	$ret .= do_the_row($cons_row);						// otherwise use constituents data
	}

else {													// no priors or constituents - do WP
//	dump(__LINE__);
//	if ((get_variable("locale") ==0) && ( $_SESSION['internet'])) {				// 9/30/10 - USA only and if internet available - 7/6/10, 9/2/10
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
//		}					// end USA only
	}					// end no priors

//dump($ret);
print $ret;
		
/*
<wp:wp xmlns:wp="http://api.whitepages.com/schema/">
  <wp:result wp:type="success" wp:message=" " wp:code="Found Data"/>
  <wp:meta>
    <wp:linkexpiration>2008-09-10</wp:linkexpiration>
    <wp:recordrange wp:lastrecord="1" wp:firstrecord="1" wp:totalavailable="1"/>
    <wp:apiversion>1.0</wp:apiversion>
    <wp:searchlinks>
      <wp:link wp:linktext="Whitepages.com" wp:type="homepage">http://www.whitepages.com/16176/</wp:link>
      <wp:link wp:linktext="Link to this api call" wp:type="self">http://api.whitepages.com/reverse_phone/1.0/?phone=4108492143;api_key=729c1a751fd3d2428cfe2a7b43442c64</wp:link>
    </wp:searchlinks>
  </wp:meta>
  <wp:listings>
    <wp:listing>
      <wp:people>
        <wp:person wp:rank="primary">
          <wp:firstname>John</wp:firstname>
          <wp:lastname>Wright</wp:lastname>
        </wp:person>
      </wp:people>
      <wp:phonenumbers>
        <wp:phone wp:type="landline" wp:rank="primary">
          <wp:fullphone>(410) 849-2143</wp:fullphone>
          <wp:areacode>410</wp:areacode>
          <wp:exchange>849</wp:exchange>
          <wp:linenumber>2143</wp:linenumber>
        </wp:phone>
      </wp:phonenumbers>
      <wp:address wp:deliverable="true">
        <wp:fullstreet>1689 Anne Ct</wp:fullstreet>
        <wp:house>1689</wp:house>
        <wp:street>Anne Ct</wp:street>
        <wp:streettype>Ct</wp:streettype>
        <wp:city>Annapolis</wp:city>
        <wp:state>MD</wp:state>
        <wp:zip>21401</wp:zip>
        <wp:zip4>6512</wp:zip4>
        <wp:country>US</wp:country>
      </wp:address>
      <wp:geodata>
        <wp:geoprecision>0</wp:geoprecision>
        <wp:latitude>39.013297</wp:latitude>
        <wp:longitude>-76.544775</wp:longitude>
      </wp:geodata>
      <wp:listingmeta>
        <wp:lastvalidated>05/2008</wp:lastvalidated>
        <wp:type>home</wp:type>
        <wp:moreinfolinks>
          <wp:link wp:linktext="Find Neighbors" wp:type="findneighbors">http://www.whitepages.com/16176/track/10214/search/FindNeighbors?addr_research=1&amp;tmpl=search_research_neighbors&amp;search_id=20051310582502495749&amp;rarl=&amp;element_id=0</wp:link>
          <wp:link wp:linktext="View Map" wp:type="viewmap">http://www.whitepages.com/16176/track/10213/map_provider?full_address=1689+Anne+Ct&amp;city=Annapolis&amp;state=MD&amp;zip=21401&amp;country=US&amp;lat=39.013297&amp;long=-76.544775&amp;name=Wright&amp;firstname=John&amp;show_form=false</wp:link>
          <wp:link wp:linktext="Driving Directions" wp:type="drivingdirections">http://www.whitepages.com/16176/track/10216/map_provider?=&amp;show_form=true&amp;full_address=1689+Anne+Ct&amp;city=Annapolis&amp;state=MD&amp;zip=21401&amp;country=US&amp;lat=39.013297&amp;long=-76.544775&amp;name=Wright&amp;firstname=John</wp:link>
          <wp:link wp:linktext="View Listing Detail" wp:type="viewdetails">http://www.whitepages.com/16176/track/10215/search/Replay?search_id=20051310582502495749&amp;lower=1&amp;more_info=1</wp:link>
        </wp:moreinfolinks>
      </wp:listingmeta>
    </wp:listing>
  </wp:listings>
</wp:wp>
"

*/
?>