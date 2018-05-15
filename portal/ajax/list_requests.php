<?php
@session_start();
require_once('../../incs/functions.inc.php');
require_once('../incs/portal.inc.php');
include('../../incs/html2text.php');
$sortby = (!(array_key_exists('sort', $_GET))) ? "request_date" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
	
function subval_sort($a,$subkey, $dd) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	if($dd == 1) {	
		asort($b);
		} else {
		arsort($b);
		}
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}

function get_contact_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if($result && mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[] = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		$the_ret[] = ($row['email'] != "") ? $row['email'] : "Unknown";
		$the_ret[] = ($row['email_s'] != "") ? $row['email_s'] : "Unknown";		
		$the_ret[] = ($row['phone_p'] != "") ? $row['phone_p'] : "Unknown";			
		$the_ret[] = ($row['phone_s'] != "") ? $row['phone_s'] : "Unknown";		
		} else {
		$the_ret[] = "UNK";
		$the_ret[] = "UNK";
		$the_ret[] = "UNK";		
		$the_ret[] = "UNK";			
		$the_ret[] = "UNK";
		}
	return $the_ret;
	}
	
$where = ((!empty($_GET)) && (isset($_GET['id']))) ? "WHERE `requester` = " . strip_tags($_GET['id']): "";
$order = "ORDER BY `" . $sortby . "`";
$order2 = $sortdir;
$showall = ((isset($_GET['showall'])) && ($_GET['showall'] == 'yes')) ? true : false;
if($where == "") {
	$where .= ($showall == false) ? " WHERE `r`.`status` <> 'Closed' AND `r`.`status` <> 'Completed'" : "";
	} else {
	$where .= ($showall == false) ? " AND `r`.`status` <> 'Closed' AND `r`.`status` <> 'Closed' " : "";
	}

$query = "SELECT *, 
		`r`.`id` AS `request_id`,
		`r`.`ticket_id` AS `r_tick_id`,
		`t`.`id` AS `tick_id`,
		`r`.`status` AS `req_status`,
		`t`.`status` AS `tick_status`,
		`r`.`phone` AS `req_phone`,
		`t`.`phone` AS `tick_phone`,
		`r`.`street` AS `req_street`,
		`r`.`city` AS `req_city`,
		`r`.`postcode` AS `req_postcode`,
		`r`.`state` AS `req_state`,
		`r`.`to_address` AS `req_to_address`,
		`r`.`pickup` AS `req_pickup`,
		`r`.`arrival` AS `req_arrival`,
		`r`.`description` AS `req_description`,
		`r`.`scope` AS `req_scope`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,
		`t`.`to_address` AS `tick_to_address`,
		`t`.`description` AS `tick_description`,
		`t`.`comments` AS `tick_comments`,
		`t`.`scope` AS `tick_scope`,
		`a`.`id` AS `assigns_id`,
		`a`.`start_miles` AS `start_miles`,
		`a`.`end_miles` AS `end_miles`,
		`r`.`rec_facility` AS `recFacility`,
		`r`.`orig_facility` AS `origFacility`,
		`r`.`contact` AS `req_contact`,
		`r`.`lat` AS `r_lat`,
		`r`.`lng` AS `r_lng`,
		`t`.`lat` AS `t_lat`,
		`t`.`lng` AS `t_lng`,		
		`request_date` AS `request_date`,
		`tentative_date` AS `tentative_date`,		
		`accepted_date` AS `accepted_date`,
		`declined_date` AS `declined_date`,		
		`resourced_date` AS `resourced_date`,
		`completed_date` AS `completed_date`,	
		`closed` AS `closed`,
		`_on` AS `_on`,
		`r`.`_by` AS `r_by`,
		`t`.`_by` AS `t_by`,
		`a`.`dispatched` AS `dispatched`,
		`a`.`clear` AS `clear` 
		FROM `$GLOBALS[mysql_prefix]requests` `r`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id` 	
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `r`.`ticket_id`=`t`.`id` 			
		{$where} GROUP BY `r`.`id` ORDER BY `request_date` ASC";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$i=0;
if ($result && mysql_num_rows($result) == 0) { 				// 8/6/08
	$ret_arr[$i][0] = "No Current Requests";
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$end_miles = ($row['end_miles'] != "") ? $row['end_miles'] : 0;
		$start_miles = ($row['start_miles'] != "") ? $row['start_miles'] : 0;
		$miles = $end_miles - $start_miles;
		$request_id = $row['request_id'];
		$requester = get_owner($row['requester']);
		$name = $row['the_name'];
		$phone = $row['req_phone'];		
		$contact = $row['req_contact'];
		$the_details = get_contact_details($row['requester']);
		$contact_email_p = $the_details[1];
		$contact_email_s = $the_details[2];			
		$contact_phone_p = $the_details[3];
		$contact_phone_s = $the_details[4];		
		$street = $row['req_street'];
		$city = $row['req_city'];
		$postcode = $row['req_postcode'];
		$state = $row['req_state'];	
		$toAddress = shorten($row['req_to_address'], 15);
		$pickup = $row['req_pickup'];
		$arrival = $row['req_arrival'];
		$orig_facility = $row['origFacility'];				
		$rec_facility = $row['recFacility'];		
		$scope = $row['req_scope'];	
		$description = $row['req_description'];
		$ticket_id = ($row['r_tick_id'] == NULL || $row['r_tick_id'] == 0) ? 0 : $row['r_tick_id'];
		$comments = "";
		$lat = (($row['r_lat'] != "") && ($row['r_lat'] != NULL) && ($row['r_lat'] != 0.999999)) ? $row['r_lat'] : 0.999999;
		$lng = (($row['r_lng'] != "") && ($row['r_lng'] != NULL) && ($row['r_lng'] != 0.999999)) ? $row['r_lng'] : 0.999999;
		$status = (!is_service_user()) ? get_status_selection($request_id, $row['req_status']) : $row['req_status'];	
		$request_date = format_dateonly(strtotime($row['request_date']));		//	12/3/13
		$tentative_date = $row['tentative_date'];
	
		if(($tentative_date != "") && ($row['accepted_date'] == "") && ($row['resourced_date'] == "") && ($row['completed_date'] == "") && ($row['closed'] == "") && ($row['req_status'] != "Tentative")) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Tentative' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}			
		$accepted_date = $row['accepted_date'];	
		if(($accepted_date != "") && ($row['resourced_date'] == "") && ($row['completed_date'] == "") && ($row['closed'] == "") && ($row['req_status'] != "Accepted")) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Accepted' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}		
		$declined_date = $row['declined_date'];	
		if(($declined_date != "") && ($row['tentative_date'] == "") && ($row['accepted_date'] == "") && ($row['resourced_date'] == "") && ($row['completed_date'] == "") && ($row['closed'] == "") && ($row['req_status'] != "Declined")) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Declined' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}	
		$resourced_date = (($row['dispatched'] != "") || ($row['dispatched'] != NULL)) ? $row['dispatched'] : $row['resourced_date'];
		if(($row['dispatched'] != "") && ($row['dispatched'] != NULL) && ($row['resourced_date'] == NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Resourced', `resourced_date` = '" . $row['dispatched'] . "' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}
		$completed_date = (($row['clear'] != "") || ($row['clear'] != NULL)) ? $row['clear'] : $row['completed_date'];
		if(($row['clear'] != "") && ($row['clear'] != NULL) && ($row['completed_date'] == NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Complete', `completed_date` = '" . $row['clear'] . "' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}		
		$closed = $row['closed'];
		if(($row['tick_status'] == 1) && ($row['closed'] == NULL) && ($row['problemend'] != NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Closed', `closed` = '" . $row['problemend'] . "' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}				
		$updated_by = get_owner($row['r_by']);
		$updated = format_date_2(strtotime($row['_on']));		
		if ($row['req_status'] == 'Open') {
			$color = "background-color: #FFFF00; color: #000000;";
			} elseif ($row['req_status'] == 'Tentative') {
			$color = "background-color: #CC9900; color: #000000;";				
			} elseif ($row['req_status'] == 'Accepted') {
			$color = "background-color: #33CCFF; color: #000000;";			
			} elseif ($row['req_status'] == 'Resourced') {
			$color = "background-color: #00FF00; color: #000000;";			
			} elseif ($row['req_status'] == 'Completed') {
			$color = "background-color: #FFFFFF; color: #00FF00;";		
			} elseif ($row['req_status'] == 'Declined') {
			$color = "background-color: #FF9900; color: #FFFFFF;";	
			} elseif ($row['req_status'] == 'Closed') {
			$color = "background-color: #FFFFFF; color: #707070;";					
			} else {
			$color = "";				
			}

		if ($row['cancelled'] != NULL && $row['cancelled'] != "" && $row['cancelled'] != "0000-00-00 00:00:00") {
			$color = "background-color: red; color: yellow;";	
			$status = "Cancelled";
			}
			
		$ret_arr[$i][0] = $request_id;		
		$ret_arr[$i][1] = $requester;
		$ret_arr[$i][2] = $name;
		$ret_arr[$i][3] = $phone;
		$ret_arr[$i][4] = $contact;
		$ret_arr[$i][5] = $contact_phone_p;
		$ret_arr[$i][6] = $contact_phone_s;
		$ret_arr[$i][7] = $contact_email_p;
		$ret_arr[$i][8] = $contact_email_s;
		$ret_arr[$i][9] = $street;
		$ret_arr[$i][10] = $city;
		$ret_arr[$i][11] = $state;
		$ret_arr[$i][12] = $rec_facility;		
		$ret_arr[$i][13] = $scope;	
		$ret_arr[$i][14] = $description;	
		$ret_arr[$i][15] = $comments;	
		$ret_arr[$i][16] = $status;	
		$ret_arr[$i][17] = $color;			
		$ret_arr[$i][18] = $request_date;	
		$ret_arr[$i][19] = $tentative_date;		
		$ret_arr[$i][20] = $accepted_date;		
		$ret_arr[$i][21] = $declined_date;		
		$ret_arr[$i][22] = $resourced_date;		
		$ret_arr[$i][23] = $completed_date;				
		$ret_arr[$i][24] = $closed;			
		$ret_arr[$i][25] = $updated;				
		$ret_arr[$i][26] = $updated_by;	
		$ret_arr[$i][27] = $miles;
		$ret_arr[$i][28] = $orig_facility;		
		$ret_arr[$i][29] = $lat;		
		$ret_arr[$i][30] = $lng;
		$ret_arr[$i][31] = $toAddress;
		$ret_arr[$i][32] = $postcode;
		$ret_arr[$i][33] = $pickup;
		$ret_arr[$i][34] = $arrival;
		$ret_arr[$i][35] = $ticket_id;
		$i++;
		} // end while	
	}				// end else
		
if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}

switch($sortby) {
	case 'id':
		$sortval = 0;
		break;
	case 'patient':
		$sortval = 2;
		break;
	case 'phone':
		$sortval = 3;
		break;
	case 'contact':
		$sortval = 4;
		break;
	case 'scope':
		$sortval = 13;
		break;
	case 'toaddress':
		$sortval = 31;
		break;
	case 'postcode':
		$sortval = 32;
		break;
	case 'requestdate':
		$sortval = 18;
		break;
	case 'pickup':
		$sortval = 33;
		break;
	case 'arrival':
		$sortval = 34;
		break;
	case 'status':
		$sortval = 16;
		break;
	case 'updated':
		$sortval = 25;
		break;
	case 'by':
		$sortval = 26;
		break;
	default:
		$sortval = 0;
	}

if($num > 0 ) {
	$the_arr = subval_sort($ret_arr, $sortval, $dd);
	$the_output = array();
	$z=0;
	foreach($the_arr as $val) {
		$the_output[$z] = $val;
		$z++;
		}
	} else {
	$the_output = $ret_arr;
	}

print json_encode($the_output);
exit();
?>