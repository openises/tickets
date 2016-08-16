<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require_once('../../incs/functions.inc.php');
$ret_arr = array();
function get_requester_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $the_id . " LIMIT 1";
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
	return $the_ret;
	}

function get_facname($id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[0] = ($row['name'] != "") ? $row['name'] : "NA";
		$street = ($row['street'] != "") ? $row['street'] : "";
		$the_ret[1] = ($street != "") ? $street . ", " . $row['city'] . ", " . $row['state']: "";
		$the_ret[2] = "Phone: " . $row['contact_phone'];
		} else {
		$the_ret[0] = "";
		$the_ret[1] = "";
		$the_ret[2] = "";
		}
	return $the_ret;
	}

if($_GET['frm_patient'] == "") {
	$ret_arr[0] = 999;
	} else {
	$theDetails = get_requester_details($_SESSION['user_id']);
	$userEmail = $theDetails[0];
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
	$appEmail = ($_GET['frm_app_email'] != "") ? $_GET['frm_app_email'] : NULL;
	$the_email = (($appEmail != NULL) && (is_email($appEmail))) ? $appEmail : $theDetails[0];
	$where = $_SERVER['REMOTE_ADDR'];
	$scope = $_GET['frm_scope'];
	$comments = $_GET['frm_comments'];
	$street = $_GET['frm_street'];
	$city = $_GET['frm_city'];
	$postcode = $_GET['frm_postcode'];
	$state = $_GET['frm_state'];	
	$lat = ($_GET['frm_lat'] != "") ? $_GET['frm_lat'] : '0';
	$lng = ($_GET['frm_lng'] != "") ? $_GET['frm_lng'] : '0';	
	$description = $_GET['frm_description'];
	$request_date = $_GET['frm_request_date'];
	$request_date = mysql_format_date(strtotime($request_date));
	$userName = $_GET['frm_username'];
	$comments = $_GET['frm_comments'];
	$phone = $_GET['frm_phone'];
	$toAddress = urldecode($_GET['frm_toaddress']);
	$pickup = $_GET['frm_pickup'];
	$arrival = $_GET['frm_arrival'];
	$patient = $_GET['frm_patient'];
	$origFac = ($_GET['frm_orig_fac'] != "") ? $_GET['frm_orig_fac'] : '0';
	$recFac = ($_GET['frm_rec_fac'] != "") ? $_GET['frm_rec_fac'] : '0';	
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
				`org`,
				`contact`, 
				`email`,
				`street`, 
				`city`, 
				`postcode`,
				`state`, 
				`the_name`, 
				`phone`, 
				`to_address`,
				`pickup`,
				`arrival`,
				`orig_facility`,
				`rec_facility`, 
				`scope`, 
				`description`, 
				`comments`, 
				`lat`,
				`lng`,
				`request_date`, 
				`status`, 
				`accepted_date`,
				`declined_date`, 
				`resourced_date`, 
				`completed_date`, 
				`closed`, 
				`requester`, 
				`_by`, 
				`_on`, 
				`_from` 
				) VALUES (
				" . 0 . ",
				'" . addslashes($userName) . "',
				'" . addslashes($appEmail) . "',
				'" . addslashes($street) . "',	
				'" . addslashes($city) . "',	
				'" . addslashes($postcode) . "',	
				'" . addslashes($state) . "',	
				'" . addslashes($patient) . "',
				'" . addslashes($phone) . "',
				'" . addslashes($toAddress) . "',
				'" . addslashes($pickup) . "',
				'" . addslashes($arrival) . "',				
				" . $origFac . ",					
				" . $recFac . ",	
				'" . addslashes($scope) . "',
				'" . addslashes($description) . "',					
				'" . addslashes($comments) . "',		
				'" . $lat . "',		
				'" . $lng . "',				
				'" . $request_date . "',
				'Open',
				NULL,
				NULL,
				NULL,
				NULL,
				NULL,
				" . $_SESSION['user_id'] . ",
				" . $_SESSION['user_id'] . ",				
				'" . $now . "',
				'" . $where . "')";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if($result) {
		do_log($GLOBALS['LOG_NEW_REQUEST'], $_SESSION['user_id']);
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
		$the_summary = "New Request from " . $userName . "\r\n";
		$the_summary .= get_text('Scope') . ": " . $_GET['frm_scope'] . "\r\n\r\n";
		$the_summary .= get_text('Patient') . " name: " . $_GET['frm_patient'] . "\r\n";
		$the_summary .= get_text('Street') . ": " . $street . ", ";	
		$the_summary .= get_text('City') . ": " . $city . ", ";	
		$the_summary .= get_text('Postcode') . ": " . $city . ", ";	
		$the_summary .= get_text('State') . ": " . $state . "\r\n";	
		$the_summary .= get_text('Contact Phone') . ": " . $phone . "\r\n";
		$the_summary .= get_text('To Address') . ": " . $toAddress . "\r\n";
		$the_summary .= get_text('Pickup Time') . ": " . $pickup . "\r\n";
		$the_summary .= get_text('Arrival Time') . ": " . $arrival . "\r\n";
		$orig_Fac = ($_GET['frm_orig_fac'] != "0") ? get_facname($_GET['frm_orig_fac']) : "";
		$rec_Fac =  ($_GET['frm_rec_fac'] != "0") ? get_facname($_GET['frm_rec_fac']) : "";
		$the_summary .= ((is_array($orig_Fac)) && ($orig_Fac[0] != "")) ? "Originating Facility " . $orig_Fac[0] . "\nAddress: " . $orig_Fac[1] . "\nPhone " . $orig_Fac[2] . "\r\n" : "";
		$the_summary .= ((is_array($rec_Fac)) && ($rec_Fac[0] != "")) ? "Receiving Facility " . $rec_Fac[0] . "\nAddress: " . $rec_Fac[1] . "\nPhone " . $rec_Fac[2] . "\r\n" : "";
		$the_summary .= get_text('Description') . "\r\n" . $description . "\r\n";	
		$the_summary .= get_text('Comments') . "\r\n" . $_GET['frm_comments'] . "\r\n";	
		$the_summary .= get_text('Request Date') . ": " . format_date_2(strtotime($request_date)) . "\r\n";		
		$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?
			$to_str1 = implode("|", $addrs);
			$smsg_to_str1 = "";
			$subject_str1 = "New " . get_text('Service User') . " Request";
			$text_str1 = "A new request has been loaded by " . $userName . " Dated " . $now . ". \r\nPlease log on to Tickets and check\n\n"; 
			$text_str1 .= "Request Summary\r\n" . $the_summary;
//			do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);
			}				// end if/else ($addrs)	
		if ($the_email != "") {				// any addresses?
			$to_str2 = $the_email;
			$smsg_to_str2 = "";
			$subject_str2 = "Your request " . $scope . " has been registered";
			$text_str2 = "Your Request " . $scope . " has been registered\r\n"; 
			$text_str2 .= "Request Summary\n\n" . $the_summary;
//			do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
			}				// end if/else ($the_email)	
		if ($userEmail != "") {				// any addresses?
			$to_str3 = $userEmail;
			$smsg_to_str3 = "";
			$subject_str3 = "Your request " . $scope . " has been registered";
			$text_str3 = "Your Request " . $scope . " has been registered\r\n"; 
			$text_str3 .= "Request Summary\n\n" . $the_summary;
//			do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
			}				// end if/else ($userEmail)	
		$ret_arr[0] = 100;
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
		$ret_arr[0] = 999;
		}
	}
print json_encode($ret_arr);
exit();
?>