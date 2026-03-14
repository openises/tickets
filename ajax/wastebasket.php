<?php
require_once('../incs/functions.inc.php');
@session_start();

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}waste_basket_m`";
$result = db_query($query);
$num_rows = $result->num_rows;

print json_encode($num_rows);