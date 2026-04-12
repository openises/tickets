<?php
/*
4/5/10 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once(isset($_SESSION['fip']) ? $_SESSION['fip'] : './incs/functions.inc.php');        //7/28/10
$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of FROM `{$GLOBALS['mysql_prefix']}assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";
$result = db_query($query);
print $result->num_rows;
?>
