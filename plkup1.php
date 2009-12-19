<?php
function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

//		$url = "http://www.google.com/search?q=". urlencode($_GET['qq']);			// google search - retired

//$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=7023954399;api_key=729c1a751fd3d2428cfe2a7b43442c64";
	$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=" . urlencode($_GET['qq']) . ";api_key=729c1a751fd3d2428cfe2a7b43442c64";
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
			
	if (((strpos ($data, "Invalid")>0)) || ((strpos ($data, "Missing")>0))) {
		print "-error 0";							// wp return bad
		}
	else {											// wp return good
//						target: "Arnold Shore;(410) 849-8721;1684 Anne Ct;  Annapolis; MD;21401;lattitude;longitude"
		$aryk[0] = "<wp:firstname>";
		$aryk[1] = "<wp:lastname>";
		$aryk[2] = "<wp:fullphone>";
		$aryk[3] = "<wp:fullstreet>";
		$aryk[4] = "<wp:city>";
		$aryk[5] = "<wp:state>";
		$aryk[6] = "<wp:zip>";
		$aryk[7] = "<wp:latitude>";
		$aryk[8] = "<wp:longitude>";
		$aryv = array(9);				// values
	//	Arnold Shore;(410) 849-8721;1684 Anne Ct,  Annapolis, MD 21401"
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
			$lhe = $pos+strlen($aryk[$i]);
			$rhe = strpos ( $data, "<", $lhe);
			$aryv[$i] = substr ( $data, $lhe , $rhe-$lhe );		// substr ( string, start , length )
			}
//		dump($aryv);
		$ret ="";												// construct return string
		for ($i=0; $i< count($aryk); $i++) {
			$ret .= $aryv[$i].$arys[$i];						// value + separator
			}			// end for ()
//		dump($ret);
		}					// end wp return good
		

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