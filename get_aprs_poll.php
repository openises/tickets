<?php
require_once('functions.inc.php');				//	server-side ajax function 
$aprs_poll = @get_variable('aprs_poll');		//  possibly not set
$aprs_poll = (is_null ($aprs_poll))? 0 : $aprs_poll ;
print $aprs_poll;
//print "55";
?>
