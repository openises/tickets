<?php
/*
list_sent_messages.php - gets sent messages from messages table for display in message window and ticket view and unit view
10/23/12 - new file
*/
@session_start();
require_once('../incs/functions.inc.php');
include('../incs/html2text.php');
$filter = "";

$cols_width = array(30,30,120,120,150,250,90,90);
$columns_arr = explode(',', get_msg_variable('columns'));

$the_win_width = 60;
$the_screen = ((isset($_GET['screen'])) && ($_GET['screen'] == 'msg_win')) ? 1 : 0;
$ret_arr = array();
$i = 0;

if((in_array('1', $columns_arr)) && ((isset($_GET['screen'])) && ($_GET['screen'] == 'msg_win'))) { $the_win_width = $the_win_width + $cols_width[0];}
if(in_array('2', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[1];}
if(in_array('3', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[2];}
if(in_array('4', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[3];}
if(in_array('5', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[4];}
if(in_array('6', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[5];}
if(in_array('7', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[6];}
if(in_array('8', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[7];}

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
	
$ticket_id = (isset($_GET['ticket_id'])) ? clean_string($_GET['ticket_id']) : NULL;
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;
$filter = (isset($_GET['filter'])) ? clean_string($_GET['filter']) : "";
$sort = (isset($_GET['sort'])) ? clean_string($_GET['sort']) : NULL;
$way = (isset($_GET['way'])) ? clean_string($_GET['way']) : NULL;
$columns = (isset($_GET['columns'])) ? explode("," ,clean_string($_GET['columns'])) : explode(",", get_msg_variable('columns')) ;	

$where = "WHERE (`m`.`msg_type` = '1' OR `m`.`msg_type` = '3')";

if(isset($ticket_id)) { $where .= " AND (`ticket_id` = '" . $ticket_id . "')"; }
if(isset($responder_id)) { $where .= " AND (`resp_id` = '" . $responder_id . "')"; }
	
if((isset($filter)) && ($filter != "")) { $where .= " AND ((`m`.`fromname` REGEXP '" . $filter . "') OR (`m`.`message` REGEXP '" . $filter . "') OR (`m`.`recipients` REGEXP '" . $filter . "') OR  (`m`.`subject` REGEXP '" . $filter . "'))"; }

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

$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
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

		$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
		$ret_arr[$i][0] = $the_message_id;		
		$ret_arr[$i][1] = $msg_row['ticket_id'];
		$ret_arr[$i][2] = $type_flag;
		$ret_arr[$i][3] = $fromname;
		$ret_arr[$i][4] = $respstring;
		$ret_arr[$i][5] = stripslashes_deep(shorten($msg_row['subject'], 18));
		$ret_arr[$i][6] = stripslashes_deep(shorten($the_message, 2000));
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
?>