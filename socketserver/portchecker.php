<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
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
