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
 * 3/14/26 - Security review: Dead code below contained SQL injection vulnerability
 *           (direct $_GET concatenation). Feature appears disabled/incomplete.
 *           If reactivated, must use db_query() with prepared statements.
 *
 * $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}ticket` (`to`, `_by`, `_from`) VALUES (?, ?, ?)";
 * $result = db_query($query, [$_GET['frm_to'], $_GET['frm_user'], $_SERVER['REMOTE_ADDR']]);
 */
print "";
?>
