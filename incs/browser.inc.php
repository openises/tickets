<?php
/*
1/23/10 - initial release
5/8/10 chrome added
*/
error_reporting (E_ALL);

function checkBrowser($input) {
	$browsers = "mozilla msie gecko firefox ";
	$browsers.= "konqueror safari netscape navigator ";
	$browsers.= "opera mosaic lynx amaya omniweb chrome";

	$browsers = explode(" ", $browsers);

	$userAgent = (array_key_exists('HTTP_USER_AGENT', $_SERVER) && !is_null($_SERVER['HTTP_USER_AGENT'])) ? strToLower($_SERVER['HTTP_USER_AGENT']) : "gecko";
	$l = strlen($userAgent);
	for ($i=0; $i<count($browsers); $i++) {
		$browser = $browsers[$i];
		$n = stristr($userAgent, $browser);
		if(strlen($n) > 0){
			$version = "";
			$navigator = $browser;
			$temp=strpos($userAgent, $navigator)+strlen($navigator)+1;
			$j = intval($temp);
			for($j=0; $j<=$l; $j++){
				if(is_numeric($j)) {
					$s = substr($userAgent, $j, 1);
					if(is_numeric($version.$s)) {
						$version .= $s;
						} else {
						break;
						}
					} else {
					break;
					}
				}
			}

		if (strpos($userAgent, 'linux')) {
			$platform = 'linux';
			} else if (strpos($userAgent, 'macintosh') || strpos($userAgent, 'mac platform x')) {
			$platform = 'mac';
			} else if (strpos($userAgent, 'windows') || strpos($userAgent, 'win32')) {
			$platform = 'windows';
			}

		if ($input==true) {
			return array(
			"browser"      => $navigator,
			"version"      => $version,
			"platform"     => $platform,
			"userAgent"    => $userAgent);
			} else {
			return "$navigator $version";
			}
		}		// end function
	}
?>