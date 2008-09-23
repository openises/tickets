<?php
require_once('./incs/functions.inc.php');				//	server-side ajax function 
$aprs_poll = @get_variable('aprs_poll');				//  returns current settings value
$aprs_poll = (is_null ($aprs_poll))? 0 : $aprs_poll ;	//  possibly not set
print $aprs_poll;
//print "55";
?>
