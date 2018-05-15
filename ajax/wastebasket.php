<?php
require_once('../incs/functions.inc.php');
@session_start();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]waste_basket_m`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
$num_rows = mysql_num_rows($result);

print json_encode($num_rows);