<?php
error_reporting(E_ALL);			// 9/13/08

$test=true;
$test2=true;

$val = ($test) ? "TEST1 true" : (($test2) ? "TEST2 true" : "false");
print $val;
?>

$blinkst = (!($do_blink)) ?  "" :(($row['units_assigned']==0)? "<blink>"  : "");
$blinkend = (!($do_blink)) ?  "" :(($row['units_assigned']==0)? "</blink>"  : "");
