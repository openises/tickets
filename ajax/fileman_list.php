<?php
/*
9/10/13 - New file - popup window to vie details of Location warnings
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
error_reporting(E_ALL);

require_once('../incs/functions.inc.php');
do_login(basename(__FILE__));
$ret_arr = array();

$query = "SELECT *,
		`fx`.`id` AS `x_id`,
		`f`.`id` AS `f_id`
		FROM `$GLOBALS[mysql_prefix]files` `f`
		LEFT JOIN `$GLOBALS[mysql_prefix]files_x` `fx` ON `fx`.`file_id` = `f`.`id` 
		ORDER BY `f`.`id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) == 0) {
	$ret_arr[0]['id'] = 0;
	} else {
	$i = 0;
	while($row = mysql_fetch_assoc($result)) {
		$ret_arr[$i]['id'] = $row['f_id'];
		$ret_arr[$i]['filename'] = $row['orig_filename'];
		$ret_arr[$i]['ticket_id'] = $row['ticket_id'];
		$ret_arr[$i]['responder_id'] = $row['responder_id'];
		$ret_arr[$i]['facility_id'] = $row['facility_id'];
		$ret_arr[$i]['type'] = $row['type'];
		$ret_arr[$i]['filetype'] = $row['filetype'];
		$ret_arr[$i]['_by'] = $row['_by'];
		$ret_arr[$i]['_on'] = $row['_on'];
		$ret_arr[$i]['_from'] = $row['_from'];
		$ret_arr[$i]['x_id'] = $row['x_id'];
		$ret_arr[$i]['user_id'] = $row['user_id'];
		$ret_arr[$i]['target_filename'] = $row['filename'];		
		$i++;
		}
	}

print json_encode($ret_arr);
exit();
?>