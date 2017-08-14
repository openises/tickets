<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
$resp = $_REQUEST['id'];
$coords = array();
$points = "";
$query = "SELECT `r`.`id`,`r`.`lat`,`r`.`lng`, `r`.`ring_fence` FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `name` = '{$resp}'";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	extract($row);
	$coords[] = $lat;
	$coords[] = $lng;
}
print $coords[0] . "," . $coords[1];
?>
