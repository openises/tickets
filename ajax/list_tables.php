<?php
/*

*/
error_reporting(E_ALL);
set_time_limit(0);
require_once('../incs/functions.inc.php');
$tables = array();

$query = "SHOW TABLES FROM `" . $mysql_db . "`";
$result = db_query($query);
$i=0;
while ($row = stripslashes_deep($result->fetch_assoc())) {
	$keyname = "Tables_in_" . $mysql_db;
	$table_name = $row[$keyname];
	$tables[$i] = $table_name;
	$i++;
	}

print json_encode($tables);
?>