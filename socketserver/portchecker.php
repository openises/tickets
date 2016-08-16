<?php
$site = "localhost";

for ($port = 1337; $port <= 1338; $port++) {
	$fp = fsockopen($site,$port,$errno,$errstr,10);
	if(!$fp) {
		echo "Port " . $port . " is not available<BR />";		
		} else {
		echo "Port " . $port . " is available<BR />";
		}
	fclose($fp);
	}
?>
