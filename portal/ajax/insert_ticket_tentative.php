<?php
require_once('../../incs/functions.inc.php');
@session_start();
$by = $_SESSION['user_id'];
$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
$regions = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]'";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
	$regions[] = $row['group'];
	}
	
$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Tentative', `tentative_date` = '" .$now . "' WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$query = "SELECT *, UNIX_TIMESTAMP(`request_date`) AS `request_date` FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . strip_tags($_GET['id']) . " LIMIT 1";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$description = (($row['description'] == "") && ($row['comments'] == "")) ? "New Ticket from Portal - Tentatively Accepted " . $now : $row['description'] . $row['comments'];
$ret_arr = array();
$query = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`in_types_id`,
				`org`,
				`contact`,
				`street`, 
				`city`, 
				`state`, 
				`phone`, 
				`facility`,
				`rec_facility`,
				`lat`,
				`lng`,
				`booked_date`,
				`problemstart`, 
				`scope`, 
				`description`, 
				`status`, 
				`owner`, 
				`severity`, 
				`updated`, 
				`_by` 
			) VALUES (
				0, 
				0,
				" . quote_smart(trim($row['the_name'])) . ", 
				" . quote_smart(trim($row['street'])) . ", 
				" . quote_smart(trim($row['city'])) . ", 
				" . quote_smart(trim($row['state'])) . ", 
				" . quote_smart(trim($row['phone'])) . ", 
				" . quote_smart(trim($row['orig_facility'])) . ", 				
				" . quote_smart(trim($row['rec_facility'])) . ", 
				" . $row['lat'] . ", 
				" . $row['lng'] . ", 
				" . quote_smart(trim($row['request_date'])) . ", 
 				" . quote_smart(trim($now)) . ", 
				" . quote_smart(trim($row['scope'])) . ", 
				" . quote_smart(trim($description)) . ", 
				2, 
				" . quote_smart(trim($by)) . ",  
				0, 
 				" . quote_smart(trim($now)) . ", 
				" . quote_smart(trim($by)) . ")";
		
$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
if($result) {
	$last_id = mysql_insert_id();
	} else {
	$last_id = 0;
	}

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `ticket_id` = " . $last_id . " WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
	
foreach ($regions as $grp_val) {
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
				($grp_val, 1, '$now', 2, $last_id, 'Allocated to Group' , $by)";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
	}
	
do_log($GLOBALS['LOG_INCIDENT_OPEN'], $last_id);
if($last_id != 0) {
	$ret_arr[0] = $last_id;
	} else {
	$ret_arr[0] = 0;
	}

print json_encode($ret_arr);