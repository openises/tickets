<?php
/*
9/10/13 New file - Ajax use for showing who is logged in to chat.
*/
@session_start();
session_write_close();
require_once './incs/functions.inc.php';

$ret_arr = array();
$the_users = "";

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));        // 1/23/10
$safe_user_id = sanitize_int($_SESSION['user_id']);
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user`  WHERE `id` <> ? AND `expires` > ? ORDER BY `user`";    // 1/23/10
$result = db_query($query, [$safe_user_id, $now]);
if ($result->num_rows==0) {
    $the_users = "no others";
    } else {
    $counter = $result->num_rows;
    $i=1;
    while ($row = stripslashes_deep($result->fetch_array())) {
        $the_sep = ($i < $counter) ? ", " : "";
        $the_users .=  $row['user'] . $the_sep;
        $i++;
        }
    }

$ret_arr[0] = $the_users;
print json_encode($ret_arr);