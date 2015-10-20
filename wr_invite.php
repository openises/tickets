<?php
error_reporting(E_ALL);	
/*
12/23/09 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
//snap (basename(__FILE__), __LINE__);

/*
//snap (basename(__FILE__), $_GET['frm_to']);
//snap (basename(__FILE__), $_GET['frm_user']);
//snap (basename(__FILE__), $_GET['frm_from']);

$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (`to`, `_by`, `_from`) VALUES ('{$_GET['frm_to']}', $_GET['frm_user'], '{$_SERVER['REMOTE_ADDR'];}' );";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
*/
print "";
?>
