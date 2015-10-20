<?php
require_once('../../incs/functions.inc.php');
@session_start();
$by = $_SESSION['user_id'];
$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
$regions = array();
$declined_reason = (isset($_GET['reason'])) ? $_GET['reason'] : "No reason given";
$description = "";
function get_requester_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		if($row['email'] == "") {
			if($row['email_s'] == "") {
				$the_ret[0] = "";
				} else {
				$the_ret[0] = $row['email_s'];
				}
			} else {
				$the_ret[0] = $row['email'];
			}
		} else {
		$the_ret[0] = "";
		}
		$the_ret[1] = $row['user'];
	return $the_ret;
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . strip_tags($_GET['id']) . " LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_num_rows($result) == 0) {
	exit();
	} else {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_user = $row['requester'];
	$the_scope = $row['scope'];
	$description .= $row['description'];
	}

$description .= "\r\n";
$description .= "Declined reason: " . addslashes($declined_reason);
$description .= "\r\n";
$theDetails = get_requester_details($the_user);
$the_email = $theDetails[0];
$the_requester = strip_tags($theDetails[1]);

$theFrom = trim(get_variable('email_reply_to'));

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Declined', `_by` = " . $by . ", `declined_date` = '" .$now . "', `description` = '" . $description . "' WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

if($result) {
	$ret_arr[0] = 100;
	do_log($GLOBALS['LOG_DECLINE_REQUEST'], $_SESSION['user_id']);	
		$to_str1 = "";
		$smsg_to_str1 = "";
		$subject_str1 = "";
		$text_str1 = "";	
		$to_str2 = "";
		$smsg_to_str2 = "";
		$subject_str2 = "";
		$text_str2 = "";		
		$to_str3 = "";
		$smsg_to_str3 = "";
		$subject_str3 = "";
		$text_str3 = "";	
	if ($the_email != "") {				// any addresses?
		$to_str1 = $the_email;
		$smsg_to_str1 = "";
		$subject_str1 = "Your request '" . $row['scope'] . "' has unfortunately been Declined.";
		$text_str1 = "Your Request '" . $row['scope'] . "' has unfortunately had to be declined.\r\n"; 
		$text_str1 .= "Declined reason: " . $declined_reason . ".\r\n"; 
		$text_str1 .= "Please contact the Tickets controllers to discuss this.\r\n";
		$text_str1 .= "The email address is " . $theFrom . "\r\n";
		$text_str1 .= "Thank you for your understanding\r\n";
//		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
		}				// end if/else ($addrs)	
	$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
	if ($addrs) {				// any addresses?
		$to_str2 = implode("|", $addrs);
		$smsg_to_str2 = "";
		$subject_str2 = "Service User request declined.";
		$text_str2 = "Service User Request '" . $row['scope'] . "' has been declined. \r\n"; 
		$text_str2 .= "Service User '" . $the_requester . "' has been informed and may contact to discuss. \r\n";
		$text_str2 .= "The Service User email address is " . $the_email . "\r\n";
//		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
		}				// end if/else ($addrs)	
	$ret_arr[1] = $to_str1;
	$ret_arr[2] = $smsg_to_str1;
	$ret_arr[3] = $subject_str1;
	$ret_arr[4] = $text_str1;	
	$ret_arr[5] = $to_str2;
	$ret_arr[6] = $smsg_to_str2;
	$ret_arr[7] = $subject_str2;
	$ret_arr[8] = $text_str2;
	$ret_arr[9] = $to_str3;
	$ret_arr[10] = $smsg_to_str3;
	$ret_arr[11] = $subject_str3;
	$ret_arr[12] = $text_str3;		
	} else {
	$ret_arr[0] = 200;
	}

print json_encode($ret_arr);
exit();
?>