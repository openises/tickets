<?php
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
$ret_arr = array();
if(!array_key_exists("code", $_GET)) {
    $ret_arr[0] = "Error";
    } else {
    $theID = sanitize_string($_GET['code']);
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` WHERE `code` = ? LIMIT 1";
    $result = db_query($query, [$theID]) or do_error($query, 'mysql query_sigs failed', db()->error,basename( __FILE__), __LINE__);
    if ($result->num_rows > 0) {
        $row = stripslashes_deep($result->fetch_assoc());
        $ret_arr[0] = $row['text'];
        } else {
        $ret_arr[0] = "Not Found";
        }
    }

print json_encode($ret_arr);
?>
