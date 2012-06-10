<?php
error_reporting(E_ALL);	

/*
string(339)
"callback({"status":"OK","Licenses":{"page":"1","rowPerPage":"100","totalRows":"1","lastUpdate":"Sep
26, 2010","License":[{"licName":"Bednar, Kevin
M","frn":"0017345794","callsign":"WQIF216","categoryDesc":"Personal
Use","serviceDesc":"General Mobile Radio
(GMRS)","statusDesc":"Active","expiredDate":"01/23/2013","licenseID":"2978106"}]}})"

{"status":"OK","Licenses":{"page":"1","rowPerPage":"100","totalRows":"1","lastUpdate":"Sep
 26, 2010","License":[{"licName":"Bednar, Kevin
     M","frn":"0017345794","callsign":"WQIF216","categoryDesc":"Personal
     Use","serviceDesc":"General Mobile Radio
     (GMRS)","statusDesc":"Active","expiredDate":"01/23/2013","licenseID":"2978106"}]}}
     
*/
function tween($instr) {
	$lh_char = "{";
	$rh_char = "}";

	$lh_pos = strpos ($instr, $lh_char);
	$rh_pos = strrpos  ($instr, $rh_char);

	$stuff = substr ( $instr,  $lh_pos,  ($rh_pos-$lh_pos+1) );
	return $stuff;
	}
$in = '"callback({"status":"OK","Licenses":{"page":"1","rowPerPage":"100","totalRows":"1","lastUpdate":"Sep
26, 2010","License":[{"licName":"Bednar, Kevin
M","frn":"0017345794","callsign":"WQIF216","categoryDesc":"Personal
Use","serviceDesc":"General Mobile Radio
(GMRS)","statusDesc":"Active","expiredDate":"01/23/2013","licenseID":"2978106"}]}})"
';
print tween($in);
?>
     