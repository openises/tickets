<?php
error_reporting(E_ALL);
require_once '../incs/functions.inc.php';
$resp = sanitize_string($_REQUEST['id']);
$coords = array();
$points = "";
$query = "SELECT `r`.`id`,`r`.`lat`,`r`.`lng`, `r`.`ring_fence` FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `name` = ?";
$result = db_query($query, [$resp]) or do_error($query, 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
while ($row = stripslashes_deep($result->fetch_assoc())) {
    extract($row);
    $coords[] = $lat;
    $coords[] = $lng;
}
print $coords[0] . "," . $coords[1];
?>
