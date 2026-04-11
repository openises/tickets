<?php
/*
9/30/15 initial release
*/
error_reporting(E_ALL);

require_once '../incs/functions.inc.php';
$ret_arr = array();

if(isset($_GET['the_error'])) {
    $now = mysql_format_date(time() - (get_variable('delta_mins')*60));
    $text = base64_decode(sanitize_string($_GET['the_error']));
    $query  = "INSERT INTO `$GLOBALS[mysql_prefix]ajax_log` (`info` , `_when`) VALUES (?, ?)";
    $result    = db_query($query, [$text, $now]) or do_error($query,'',db()->error, basename( __FILE__), __LINE__);
    if(db_affected_rows() == 1) {
        $ret_arr[0] = 1;
        } else {
        $ret_arr[0] = 0;
        }
    } else {
    $ret_arr[0] = 99;
    }

print json_encode($ret_arr);
exit();
?>