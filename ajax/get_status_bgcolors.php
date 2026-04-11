<?php
require_once '../incs/functions.inc.php';
set_time_limit(90);
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
    exit();
    } */
$ret_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result = db_query($query) or do_error($query, 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
while ($row = stripslashes_deep($result->fetch_array())) {
    $ret_arr[$row['id']] = $row['bg_color'];
    }

//dump($ret_arr);
print json_encode($ret_arr);
exit();
?>