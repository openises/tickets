<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
error_reporting(E_ALL);            // 9/13/08

$test=true;
$test2=true;

$val = ($test) ? "TEST1 true" : (($test2) ? "TEST2 true" : "false");
print $val;
?>

$blinkst = (!($do_blink)) ?  "" :(($row['units_assigned']==0)? "<blink>"  : "");
$blinkend = (!($do_blink)) ?  "" :(($row['units_assigned']==0)? "</blink>"  : "");
