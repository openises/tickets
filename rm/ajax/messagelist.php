<?php
/*
9/10/13 - new file - gets message list for mobile screen
*/
@session_start();
require_once('../../incs/functions.inc.php');
$filter = "";

$ret_arr = array();
$i = 0;

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

$the_user = ($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$ticket_id = ((isset($_GET['ticket_id'])) && ($_GET['ticket_id'] != 0)) ? clean_string($_GET['ticket_id']) : NULL;
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;
$filter = (isset($_GET['filter'])) ? clean_string($_GET['filter']) : "";
$sort = (isset($_GET['sort'])) ? clean_string($_GET['sort']) : NULL;
$way = (isset($_GET['way'])) ? clean_string($_GET['way']) : NULL;
$where = "";

if($responder_id) {
	$where .= "WHERE (`resp_id` = '" . $responder_id . "')";
	$where .= ($ticket_id) ? " AND `ticket_id` = " . $ticket_id . " ":"";
	} else {
	$where .= ($ticket_id) ? " WHERE `ticket_id` = " . $ticket_id : "";
	}
	
$order = (isset($sort)) ? "ORDER BY `read_status`, " . $sort : "ORDER BY `date`" ;
$order2 = (isset($way)) ? $way : "DESC";
$actr=0;

$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder`";
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$responderlist = array();
$responderlist[0] = "NA";	
$caption = "Messages: ";	
while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
	$responderlist[$act_row['id']] = $act_row['handle'];
	}	
	
$query = "SELECT *, `date` AS `date`, `_on` AS `_on`,
		`m`.`id` AS `message_id`,
		`m`.`fromname` AS `fromname`,		
		`m`.`message` AS `message`,
		`m`.`ticket_id` AS `ticket_id`,
		`m`.`resp_id` AS `resp_id`,		
		`m`.`message_id` AS `msg_id`,
		`m`.`msg_type` AS `msg_type`,	
		`m`.`recipients` AS `recipients`,	
		`m`.`readby` AS `readby`,		
		`m`.`subject` AS `subject`	
		FROM `$GLOBALS[mysql_prefix]messages` `m` 
		{$where} {$order} {$order2}";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$bgcolor = "#EEEEEE";
$num=mysql_num_rows($result);
if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$print = "<TABLE style='width: 100%;'><TR style='width: 100%;'><TD style='width: 100%;'>No Messages</TD></TR></TABLE>";	
	} else {
	$print = "<TABLE style='width: 100%;'>";	
	$print .= "<TR style='width: 100%; font-weight: bold; color: #FFFFFF; background-color: #707070;'><TD style='width: 10%;'>TYPE</TD><TD style='width: 30%;'>FROM</TD><TD style='width: 40%;'>SUBJECT</TD><TD style='width: 20%;'>DATE</TD></TR>";
	while ($msg_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$the_readers = array();
		$the_readers = explode("," , $msg_row['readby']);
		if(($the_readers[0] != "") && (in_array($responder_id, $the_readers, true))) {
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
			$resp_names .= $responderlist[$val] . $thesep;
			$n++;
			}
		$resp_name = (isset($responderlist[$the_responder])) ? $responderlist[$the_responder] : "INCOMING";	
		$the_message = ($msg_row['message'] != "") ? strip_tags($msg_row['message']) : "";
		if($msg_row['recipients'] == NULL) {
			$respstring = $resp_names;		
			} else {
			$responders = explode (" ", trim($msg_row['recipients']));	// space-separated list to array
			$sep = $respstring = "";
			for ($k=0 ;$k < count($responders);$k++) {				// build string of responder names
				if (in_array($responders[$k], $responderlist)) {
					$respstring .= $sep . $responders[$k];
					$sep = "<BR />";
					} else {
					$respstring .= $responders[$k];
					}
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
		$read_class = ($the_class == 1) ? "font-weight: bold;" : "";

		$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
		$print .= "<TR style='width: 100%; cursor: pointer; background-color: " . $bgcolor . "; " . $read_class . "' onClick='get_message(" . $the_message_id . ");'>";		
		$print .= "<TD style='width: 10%; " . $color . ";'>" . $type_flag . "</TD>";		
		$print .= "<TD style='width: 30%;'>" . $fromname . "</TD>";
		$print .= "<TD style='width: 40%;'>" . stripslashes_deep(shorten($msg_row['subject'], 18)) . "</TD>";
		$print .= "<TD style='width: 20%;'>" . format_date_2(strtotime($msg_row['date'])) . "</TD>";		
		$print .= "</TR>";
		$bgcolor = ($bgcolor == "#EEEEEE") ? "#FEFEFE" : "#EEEEEE";				
		} // end while	
		$print .= "</TABLE>";
	}				// end else
print $print;
exit();
?>