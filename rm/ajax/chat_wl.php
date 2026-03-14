<?php
/*
9/10/13 New file - shows who is logged in and availbale to chat - for mobile page.
*/
@session_start();
require_once('../../incs/functions.inc.php');

$ret_arr = array();
$the_users = "";

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$user_id = sanitize_int($_SESSION['user_id']);
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user`  WHERE `id` <> ? AND `expires` > ? ORDER BY `user`";
$result = db_query($query, [$user_id, $now]);
if ($result->num_rows==0) {
	$the_users = "no others";
	} else {
	$counter = $result->num_rows;
	$i=1;
	while ($row = stripslashes_deep($result->fetch_array())) {
		$the_sep = ($i < $counter) ? ", " : "";
		$the_users .=  e($row['user']) . $the_sep;
		$i++;
		}
	}

$ret_arr[0] = $the_users;
print json_encode($ret_arr);
exit();
?>