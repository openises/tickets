<?php
/*
*/
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
@session_start();

$ret_arr = array();

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}waste_basket_m`";
$result = db_query($query);
if($result->num_rows != 0) {
    $i=0;
    while ($row = $result->fetch_assoc()) {
        $ret_arr[$i][] = $row['id'];
        $ret_arr[$i][] = $row['field1'];
        $ret_arr[$i][] = $row['field2'];
        $ret_arr[$i][] = $row['field4'];
        $i++;
        }
    } else {
    $ret_arr = "";
    }
print json_encode($ret_arr);