<?php
/*
9/10/13 New file - shows who is logged in and availbale to chat - for mobile page.
*/
@session_start();
require_once('../../incs/functions.inc.php');

$ret_arr = array();
$the_users = "";

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`  WHERE `id` <> " . $_SESSION['user_id'] . " AND `expires` > '" . $now . "' ORDER BY `user`";	// 1/23/10 
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_num_rows($result)==0) {
	$the_users = "no others";
	} else {
	$counter = mysql_num_rows($result);
	$i=1;
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$the_sep = ($i < $counter) ? ", " : "";
		$the_users .=  $row['user'] . $the_sep;
		$i++;
		}
	}

$ret_arr[0] = $the_users;
print json_encode($ret_arr);
exit();
?>