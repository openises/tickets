<?php
/*
9/10/13 - new file - sends email from mobile screen.
*/
error_reporting(E_ALL);
require_once('../../incs/functions.inc.php');
@session_start();
$the_session = $_GET['session'];
$ret_arr = array();
$from = '127.0.0.1';		
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
extract($_GET);
$msg_type = 2;
$message_id = "email";
$recipients = "Tickets";
$read_status = 0;
$delivery_status = 2;
	
$query = "INSERT INTO `$GLOBALS[mysql_prefix]messages` (
	`msg_type`, 
	`message_id`,
	`ticket_id`,
	`resp_id`,
	`recipients`,		
	`from_address`,	
	`fromname`,
	`subject`,
	`message`,
	`date`,		
	`read_status`,	
	`delivery_status`,		
	`_by`,		
	`_on`,	
	`_from` )
	VALUES (" .
		quote_smart(trim($msg_type)) . "," .
		quote_smart(trim($message_id)) . "," .
		$ticket_id . "," .		
		$resp_id . "," .	
		quote_smart(trim($recipients)) . "," .	
		quote_smart(trim($from_address)) . "," .	
		quote_smart(trim($fromname)) . "," .	
		quote_smart(trim($subject)) . "," .	
		quote_smart(trim($message)) . "," .	
		quote_smart(trim($now)) . "," .				
		$read_status . "," .	
		$delivery_status . "," .			
		quote_smart(trim($resp_id)) . "," .	
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
