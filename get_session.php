<?php
/*
returns JSON-encoded session array
1/22/10 - initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
print json_encode($_SESSION);

?>
