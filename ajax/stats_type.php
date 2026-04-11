<?php
#
# statistics.php - Management Statistics from Tickets.
#
/*
6/14/11    First version
*/
error_reporting(0);
require_once '../incs/functions.inc.php';
$type = (isset($type)) ? sanitize_int($type) : "";

function get_stat_type_type($value) {
    $stat_type = "Not Used";
    $query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}stats_type` WHERE `st_id` = ?";
    $result = db_query($query, [intval($value)]);
    if($result->num_rows != 0) {
    $row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
        $stat_type = $row['stat_type'];
        }
    return $stat_type;
    }

print json_encode(get_stat_type_type($type));
exit();
?>
