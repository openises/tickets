<?php
require_once('../../incs/functions.inc.php');
@session_start();
$by = $_SESSION['user_id'];
$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
$regions = array();

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Declined', `_by` = " . $by . ", `declined_date` = '" .$now . "' WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}

print json_encode($ret_arr);