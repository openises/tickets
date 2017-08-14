<?php
/*
list messages.php - gets messages from messages table for display in message window and ticket view and unit view
10/23/12 - new file
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
require_once('../incs/functions.inc.php');
include('../incs/html2text.php');
$ret_arr = array();
$i = 0;
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

$ticket_id = (isset($_GET['ticket_id'])) ? clean_string($_GET['ticket_id']) : NULL;
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;
$facility_id = (isset($_GET['facility_id'])) ? clean_string($_GET['facility_id']) : NULL;
$mi_id = (isset($_GET['mi_id'])) ? clean_string($_GET['mi_id']) : NULL;
$sort = (isset($_GET['sort'])) ? clean_string($_GET['sort']) : NULL;
$way = (isset($_GET['dir'])) ? clean_string($_GET['dir']) : NULL;
$inorout = ((isset($_GET['inorout'])) && ($_GET['inorout'] == "sent")) ? true : false;

if($inorout) {
	$where = "WHERE (`m`.`msg_type` = '1' OR `m`.`msg_type` = '3')";
	} else {
	$where = "WHERE (`m`.`msg_type` = '2' OR `m`.`msg_type` = '4' OR `m`.`msg_type` = '5' OR `m`.`msg_type` = '6')";
	}

if(isset($mi_id)) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `mi_id` = " . $mi_id;
	$result = mysql_query($query);
	while($mi_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$incs_arr[] = $mi_row['ticket_id'];
		}
	} elseif(isset($ticket_id)) {
	$incs_arr[] = $ticket_id;
	} else {
	$incs_arr = null;
	}
	
if(isset($incs_arr)) {
	$output = "";
	if(count($incs_arr) > 1) {
		$output .= " AND (";
		$z = 1;
		foreach($incs_arr as $val) {
			if($z < count($incs_arr)) {
				$output .= "`ticket_id` = '" . $val . "' OR ";
				} else {
				$output .= "`ticket_id` = '" . $val . "'";
				}
			$z++;
			}
			$output .= ")";
		} else {
		$val = $incs_arr[0];
		$output .= " AND (`ticket_id` = '" . $val . "')";
		}
	$where .= $output;
	}
	

		
//if(isset($ticket_id)) { $where .= " AND (`ticket_id` = '" . $ticket_id . "')"; }
if(isset($responder_id)) { $where .= " AND (`resp_id` = '" . $responder_id . "')"; }
if(isset($facility_id)) { $where .= " AND (`resp_id` = '" . $facility_id . "')"; }	

$order = (isset($sort)) ? "ORDER BY `read_status` ," . $sort : "ORDER BY `date`" ;
$order2 = (isset($way)) ? $way : "";
$actr=0;

$the_user = $_SESSION['user_id'];	

$query = "SELECT *, `date` AS `date`, `_on` AS `_on`,
		`m`.`id` AS `message_id`,
		`m`.`fromname` AS `fromname`,		
		`m`.`message` AS `message`,
		`m`.`ticket_id` AS `ticket_id`,
		`m`.`message_id` AS `msg_id`,
		`m`.`msg_type` AS `msg_type`,	
		`m`.`recipients` AS `recipients`,	
		`m`.`readby` AS `readby`,		
		`m`.`subject` AS `subject`	
		FROM `$GLOBALS[mysql_prefix]messages` `m` 
		{$where} {$order} {$order2}";
$result = mysql_query($query);
$num=mysql_num_rows($result);
if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$ret_arr[$i][0] = "No Messages";
	} else {
	while ($msg_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$the_readers = array();
		$the_readers = explode("," , $msg_row['readby']);
		if(($the_readers[0] == "") || (!in_array($the_user, $the_readers, true))) {
			$the_class = 0;
			} else {
			$the_class = 1;
			}
			
		$the_message_id = $msg_row['message_id'];
		$the_responder = $msg_row['resp_id'];
		$the_resp_ids = explode(",", $the_responder);
		$resp_names = "";
		$n = 1;
		$thesep = ",";
		foreach($the_resp_ids as $val) {
			if($n == count($the_resp_ids)) {
				$thesep = "";
				}
			if($val != "") {
				$resp_names .= get_respondername($val) . $thesep;
				} else {
				$resp_names .= "Unk" . $thesep;
				}
			$n++;
			}
//		$resp_name = get_respondername($the_responder);	
		$the_message = ($msg_row['message'] != "") ? strip_tags($msg_row['message']) : "";
		if($msg_row['recipients'] == NULL) {
			$respstring = $resp_names;		
			} else {
			$responders = explode (" ", trim($msg_row['recipients']));	// space-separated list to array
			$sep = $respstring = "";
			for ($k=0 ;$k < count($responders);$k++) {				// build string of responder names
				$respstring .= $sep . $responders[$k];
				}
			}
			
		if ($msg_row['msg_type'] == 1) {
			$type_flag = "OE";
			$color = "background-color: blue; color: white;";
			} elseif ($msg_row['msg_type'] ==2) {
			$type_flag = "IE";
			$color = "background-color: white; color: blue;";			
			} elseif ($msg_row['msg_type'] ==3) {
			$color = "background-color: orange; color: white;";			
			$type_flag = "OS";
			} elseif (($msg_row['msg_type'] ==4) || ($msg_row['msg_type'] ==5) || ($msg_row['msg_type'] ==6)) {
			$color = "background-color: white; color: orange;";				
			$type_flag = "IS";	
			} else {
			$color = "";				
			$type_flag = "?";
			}
			
		$the_readby = array();
		foreach($the_readers AS $val) {
			$the_readby[] = get_reader($val);
			}
		$readers_string = "Message read by: " . implode(",", $the_readby);
		$del_status = ($msg_row['msg_type'] == 3) ? $msg_row['delivery_status'] : "3";
		$deliveredto = $msg_row['delivered'];

		$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
		$ret_arr[$i][0] = $the_message_id;		
		$ret_arr[$i][1] = $msg_row['ticket_id'];
		$ret_arr[$i][2] = $type_flag;
		$ret_arr[$i][3] = shorten($fromname, 18);
		$ret_arr[$i][4] = shorten($respstring, 10);
		$ret_arr[$i][5] = stripslashes_deep(shorten($msg_row['subject'], 18));
		$ret_arr[$i][6] = htmlentities(shorten($the_message, 80));
		$ret_arr[$i][7] = format_date_2(strtotime($msg_row['date']));
		$ret_arr[$i][8] = get_owner($msg_row['_by']);	
		$ret_arr[$i][9] = $the_class;
		$ret_arr[$i][10] = $msg_row['id'];		
		$ret_arr[$i][11] = $readers_string;	
		$ret_arr[$i][12] = $del_status;
		$ret_arr[$i][13] = $deliveredto;
		$i++;
		} // end while	
	}				// end else
print json_encode($ret_arr);
exit();
?>
