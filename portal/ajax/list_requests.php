<?php
@session_start();
require_once('../../incs/functions.inc.php');
require_once('../incs/portal.inc.php');
include('../../incs/html2text.php');

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
	
function get_contact_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[] = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		$the_ret[] = ($row['email'] != "") ? $row['email'] : "Unknown";
		$the_ret[] = ($row['email_s'] != "") ? $row['email_s'] : "Unknown";		
		$the_ret[] = ($row['phone_p'] != "") ? $row['phone_p'] : "Unknown";			
		$the_ret[] = ($row['phone_s'] != "") ? $row['phone_s'] : "Unknown";		
		}
	return $the_ret;
	}
	
//$where = ((!empty($_GET)) && (isset($_GET['id']))) ? "WHERE `requester` = " . strip_tags($_GET['id']): "WHERE `status` = 'Open' ";
$where = ((!empty($_GET)) && (isset($_GET['id']))) ? "WHERE `requester` = " . strip_tags($_GET['id']): " ";
$order = "ORDER BY `request_date`";
$order2 = "ASC";


$query = "SELECT *, 
		`r`.`id` AS `request_id`,
		`a`.`id` AS `assigns_id`,
		`a`.`start_miles` AS `start_miles`,
		`a`.`end_miles` AS `end_miles`,
		`request_date` AS `request_date`,
		`tentative_date` AS `tentative_date`,		
		`accepted_date` AS `accepted_date`,
		`declined_date` AS `declined_date`,		
		`resourced_date` AS `resourced_date`,
		`completed_date` AS `completed_date`,	
		`closed` AS `closed`,
		`_on` AS `_on`,
		`a`.`dispatched` AS `dispatched`,
		`a`.`clear` AS `clear`		
		FROM `$GLOBALS[mysql_prefix]requests` `r`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id` 			
		{$where} GROUP BY `r`.`id` {$order} {$order2}";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$i=0;
if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$ret_arr[$i][0] = "No Current Requests";
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$miles = $row['end_miles'] - $row['start_miles'];
		$request_id = $row['request_id'];
		$requester = get_owner($row['requester']);
		$name = $row['the_name'];
		$phone = $row['phone'];		
		$contact = $row['contact'];
		$the_details = get_contact_details($row['requester']);
		$contact_email_p = $the_details[1];
		$contact_email_s = $the_details[2];			
		$contact_phone_p = $the_details[3];
		$contact_phone_s = $the_details[4];		
		$street = $row['street'];
		$city = $row['city'];
		$state = $row['state'];	
		$orig_facility = $row['orig_facility'];				
		$rec_facility = $row['rec_facility'];		
		$scope = $row['scope'];	
		$description = $row['description'];	
		$comments = $row['comments'];
		$lat = (($row['lat'] != "") && ($row['lat'] != NULL) && ($row['lat'] != 0.999999)) ? $row['lat'] : 0.999999;
		$lng = (($row['lng'] != "") && ($row['lng'] != NULL) && ($row['lng'] != 0.999999)) ? $row['lng'] : 0.999999;
		$status = ((!is_service_user()) && ($row['status'] != 'Open') && ($row['status'] != 'Tentative')) ? get_status_selection($request_id, $row['status']) : $row['status'];		
		$request_date = $row['request_date'];	
		$tentative_date = $row['tentative_date'];			
		$accepted_date = $row['accepted_date'];	
		$declined_date = $row['declined_date'];	
		$resourced_date = (($row['dispatched'] != "") || ($row['dispatched'] != NULL)) ? $row['dispatched'] : $row['resourced_date'];
		if(($row['dispatched'] != "") && ($row['dispatched'] != NULL) && ($row['resourced_date'] == NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `resourced_date` = '" . mysql_format_date($row['dispatched']) . " WHERE `id` = " . $request_id;
			}
		$completed_date = (($row['clear'] != "") || ($row['clear'] != NULL)) ? $row['clear'] : $row['completed_date'];
		if(($row['clear'] != "") && ($row['clear'] != NULL) && ($row['completed_date'] == NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `completed_date` = '" . mysql_format_date($row['clear']) . " WHERE `id` = " . $request_id;
			}		
		$closed = $row['closed'];			
		$updated_by = get_owner($row['_by']);
		$updated = $row['_on'];		
		
		if ($row['status'] == 'Open') {
			$color = "background-color: #FFFF00; color: #000000;";
			} elseif ($row['status'] == 'Tentative') {
			$color = "background-color: #CC9900; color: #000000;";				
			} elseif ($row['status'] == 'Accepted') {
			$color = "background-color: #33CCFF; color: #000000;";			
			} elseif ($row['status'] == 'Resourced') {
			$color = "background-color: #00FF00; color: #000000;";			
			} elseif ($row['status'] == 'Completed') {
			$color = "background-color: #FFFFFF; color: #00FF00;";		
			} elseif ($row['status'] == 'Declined') {
			$color = "background-color: #FF0000; color: #FFFF00;";	
			} elseif ($row['status'] == 'Closed') {
			$color = "background-color: #000000; color: #FFFFFF;";					
			} else {
			$color = "";				
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
		$i++;
		} // end while	
	}				// end else

//dump($ret_arr);

print json_encode($ret_arr);
?>