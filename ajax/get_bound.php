<?php
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
$bound = $_REQUEST['id'];
@session_start();
$coords = array();
$points = "";
$query = "SELECT `l`.`line_data` FROM `$GLOBALS[mysql_prefix]lines` `l` WHERE `id` = '{$bound}'";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	extract($row);
}
print $line_data;
?>
