<?php
/*
9/10/13 - New file, submits a new road condition alert from mobile screen
*/
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];


function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		}
	return $the_ret;
	}	
$ret_arr = array();
$from = $_SERVER['REMOTE_ADDR'];		
$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;
$whom = (array_key_exists('user_id', $_SESSION))? get_user_name($_SESSION['user_id']): "Public";
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
extract($_GET);

$query_cond = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` WHERE `id` = " . $type . " LIMIT 1";
$result_cond = mysql_query($query_cond) or do_error($query_cond, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row_cond = stripslashes_deep(mysql_fetch_assoc($result_cond));	
$the_description = $row_cond['description'];

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo` WHERE `lat` = '" . $lat . "' AND `lng` = '" . $lng . "'";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
if(mysql_num_rows($result) > 0) {
	$query_del = "DELETE FROM $GLOBALS[mysql_prefix]roadinfo WHERE `lat` = '" . $lat . "' AND `lng` = '" . $lng . "'";
	$result_del = mysql_query($query_del) or do_error($query_del, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	}

$query = "INSERT INTO `$GLOBALS[mysql_prefix]roadinfo` (
	`title`, 
	`description`,
	`address`,
	`conditions`,
	`lat`,		
	`lng`,	
	`username`,	
	`_by`,		
	`_on`,	
	`_from` )
	VALUES (" .
		quote_smart(trim($title)) . "," .
		quote_smart(trim($the_description)) . "," .
		quote_smart(trim($address)) . "," .		
		quote_smart(trim($type)) . "," .	
		quote_smart(trim($lat)) . "," .	
		quote_smart(trim($lng)) . "," .				
		quote_smart(trim($whom)) . "," .		
		quote_smart(trim($who)) . "," .	
		quote_smart(trim($now)) . "," .	
		quote_smart(trim($from)) . ");";

$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}

print json_encode($ret_arr);
exit();
?>
