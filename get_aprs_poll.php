<?php
/*
3/18/09 'aprs_poll' to 'auto_poll'
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/

@session_start();
require_once($_SESSION['fip']);		//7/28/10
$aprs_poll = @get_variable('auto_poll');				//  returns current settings value
$aprs_poll = (is_null ($aprs_poll))? 0 : $aprs_poll ;	//  possibly not set
print $aprs_poll;
//print "55";
?>
