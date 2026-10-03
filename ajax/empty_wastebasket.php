<?php
/*
empty_wastebasket.php - empties messages wastebasket table - uses truncate to reset id to 0.
10/23/12 - new file
*/
require_once '../incs/functions.inc.php';
require_login_or_401();

$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]messages_bin`";
$result = db_query($query) or do_error($query, 'db_query() failed', db()->error, __FILE__, __LINE__);
if($result) {
    $ret_arr[0] = 100;
    } else {
    $ret_arr[0] = 200;
    }
print json_encode($ret_arr);
exit();
?>