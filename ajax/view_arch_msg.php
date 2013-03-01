<?php 

@session_start();
require_once('../incs/functions.inc.php');
require_once('../incs/messaging.inc.php');
include('../incs/html2text.php');

$filename = "../message_archives/" . $_GET['filename'];	
$the_row = $_GET['rownum'];

$filerow = 0;
$numrows = 0;
$col_names = array();
$ret_arr = array();
$the_user = $_SESSION['user_id'];

$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder`";
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$responderlist = array();
$responderlist[0] = "NA";	
$caption = "Messages: ";	
while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
	$responderlist[$act_row['id']] = $act_row['handle'];
	}		

if (($handle = fopen($filename, "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$numrows++;		
		}
	fclose($handle);
	}	
	
if (($handle = fopen($filename, "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);
		if($filerow==0) {
			for($f=0; $f < $num; $f++) {
				$col_names[$f] = $data[$f];
				}	
			} elseif($filerow == $the_row) {		
				for ($c=0; $c < $num; $c++) {
					$thedata = trim( preg_replace( '/\s+/', ' ', $data[$c] ) );
					$msg_row["$col_names[$c]"] = $thedata;
					}
			} else {
			}
		$filerow++;		
		}
	fclose($handle);
	}
$the_readers = array();
$the_readers = explode("," , $msg_row['readby']);
if(($the_readers[0] == "") || (!in_array($the_user, $the_readers, true))) {
	$the_class = 0;
	} else {
	$the_class = 1;
	}
	
$the_message_id = $msg_row['message_id'];
//		$the_class = ($msg_row['read_status'] == 0) ? "0" : "1";		
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
$the_message = ($msg_row['message'] != "") ? stripslashes_deep(html2text($msg_row['message'])) : "";
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

if(($msg_row['msg_type'] == 4) || ($msg_row['msg_type'] == 5) || ($msg_row['msg_type'] == 6)) {
	$fromAddress = ($msg_row['from_address'] == "") ? $msg_row['recipients'] : $msg_row['from_address'];
	$theFrom = explode(",", $fromAddress);
	$theOthers = array();	
	foreach($theFrom AS $val) {
		$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `m` WHERE `smsg_id` = '" . $val . "'";
		$result1 = mysql_query($query1) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
			$theOthers[] = $row1['contact_via'];
			}
		}
	$theothers = implode("|", $theOthers);
	$recipients = implode("|", $theFrom);
	$recipients = "Tickets";
	}
	
if($msg_row['msg_type'] == 1) {
	$fromAddress = get_variable('email_reply_to');
	}
		
if($msg_row['msg_type'] == 3) {
	$theRecipients = explode(",", $msg_row['recipients']);
	$theOthers = array();	
	foreach($theRecipients AS $val) {
		$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `m` WHERE `smsg_id` = '" . $val . "'";
		$result1 = mysql_query($query1) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
			$theOthers[] = $row1['contact_via'];
			}
		}
	$theothers = implode("|", $theOthers);
	$fromAddress = "Tickets";	
	}
	
if($msg_row['recipients'] == "Tickets") {
	$recipients = "Tickets";
	}

$the_readby = array();
foreach($the_readers AS $val) {
	$the_readby[] = get_reader($val);
	}
$readers_string = implode(",", $the_readby);

$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
$ret_arr[0] = $the_message_id;		
$ret_arr[1] = $msg_row['ticket_id'];
$ret_arr[2] = $type_flag;
$ret_arr[3] = $fromname;
$ret_arr[4] = $respstring;
$ret_arr[5] = stripslashes_deep($msg_row['subject']);
$ret_arr[6] = $the_message;
$ret_arr[7] = format_date_2($msg_row['date']);
$ret_arr[8] = get_owner($msg_row['_by']);	
$ret_arr[9] = $msg_row['id'];		
$ret_arr[10] = $readers_string;	
$ret_arr[11] = $fromAddress;
$ret_arr[12] = $theothers;
$ret_arr[13] = $msg_row['resp_id'];
$ret_arr[14] = $numrows;
print json_encode($ret_arr);
?>