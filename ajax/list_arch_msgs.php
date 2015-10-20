<?php
/*
list_arch_messages.php - gets archive messages from stored csv files for display in message window and ticket view and unit view
10/23/12 - new file
*/
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
	
function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
		}
	array_multisort($sort_col, $dir, $arr);
	}
	
function search($array, $key, $value) {
    $results = array();
	foreach($array AS $subarray) {
		if(isset($subarray[$key])) {
			$thestring = $subarray[$key];
			$pos = strpos($thestring, $value);
			if($pos === false) {
				} else {
				$results[] = $subarray;
				}
			}
		}
    return $results;
	}

$sortdir = ((isset($_GET['way'])) && ($_GET['way'] == "DESC")) ? SORT_DESC : SORT_ASC;
$thesort = $_GET['sort'];
$filename = "../message_archives/" . $_GET['filename'];	
$ticket_id = (isset($_GET['ticket_id'])) ? clean_string($_GET['ticket_id']) : NULL;
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;
$filter = (isset($_GET['filter'])) ? clean_string($_GET['filter']) : "";
$sort = (isset($_GET['sort'])) ? clean_string($_GET['sort']) : NULL;
$columns = (isset($_GET['columns'])) ? explode("," ,clean_string($_GET['columns'])) : explode(",", get_msg_variable('columns')) ;
$actr=0;

$the_user =  1;
$errmsg = "";
$i = 1;
$row = 0;
$the_arr = array();
$col_names = array();
$titles = "";	
if (($handle = fopen($filename, "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);
		if($row==0) {
			for($f=0; $f < $num; $f++) {
				$col_names[$f] = $data[$f];
				}
			$g = $f + 1;
			$col_names[$g] = "row_num";
			} else {		
			for ($c=0; $c < $num; $c++) {
				$thedata = trim( preg_replace( '/\s+/', ' ', $data[$c] ) );
				$the_arr[$row-1]["$col_names[$c]"] = $thedata;
			}
			$the_arr[$row-1]['row_num'] = $row-1;
		}
		$row++;		
	}
	fclose($handle);
	}
if($filter != "") {
	$the_result = search($the_arr, 'message', $filter);
	array_sort_by_column($the_result, $thesort, $sortdir);	
	} else {
	$the_result = $the_arr;
	array_sort_by_column($the_result, $thesort, $sortdir);	
	}
	
foreach($the_result AS $msg_row) {
	$the_readers = array();
	$the_readers = explode("," , $msg_row['readby']);
	if(($the_readers[0] == "") || (!in_array($the_user, $the_readers, true))) {
		$the_class = 0;
		} else {
		$the_class = 1;
		}
	$date = substr($msg_row['date'], 0, 10);
	$datepart = explode("-", $date);
	if((strlen($datepart[0]) == 4) && (get_variable('locale') == 1)) {
		$yearpart = substr($datepart[0], 2);
		if(substr($datepart[2], 0, 1) == 0) {
			$daypart = substr($datepart[2], 1);
			} else {
			$daypart = $datepart[2];
			}
		if(substr($datepart[1], 0, 1) == 0) {
			$monthpart = substr($datepart[1], 1);
			} else {
			$monthpart = $datepart[1];
			}
		$timepart = explode(" ", $msg_row['date']);
		$thetime = explode(":", $timepart[1]);
		$thehour =  $thetime[0];
		$themin = $thetime[1];
		$formatted_date =  $daypart . "/" . $monthpart . "/" . $yearpart . " " . $thehour . ":" . $themin;
		} elseif((strlen($datepart[0]) == 4) && (get_variable('locale') == 0)) {
		$yearpart = substr($datepart[0], 2);
		if(substr($datepart[2], 0, 1) == "0") {
			$daypart = substr($datepart[2], 1);
			} else {
			$daypart = $datepart[2];
			}
		if(substr($datepart[1], 0, 1) == "0") {
			$monthpart = substr($datepart[1], 1);
			} else {
			$monthpart = $datepart[1];
			}
		$timepart = explode(" ", $msg_row['date']);
		$thetime = explode(":", $timepart[1]);
		$thehour =  $thetime[0];
		$themin = $thetime[1];
		$formatted_date =  $monthpart . "/" . $daypart . "/" . $yearpart . " " . $thehour . ":" . $themin;
		} else {
		$yearpart = substr($datepart[0], 2);
		if(substr($datepart[2], 0, 1) == "0") {
			$daypart = substr($datepart[2], 1);
			} else {
			$daypart = $datepart[2];
			}
		if(substr($datepart[1], 0, 1) == "0") {
			$monthpart = substr($datepart[1], 1);
			} else {
			$monthpart = $datepart[1];
			}
		$timepart = explode(" ", $msg_row['date']);
		$thetime = explode(":", $timepart[1]);
		$thehour =  $thetime[0];
		$themin = $thetime[1];
		$formatted_date =  $daypart . "/" . $monthpart . "/" . $yearpart . " " . $thehour . ":" . $themin;
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
		$resp_names .= get_respondername($val) . $thesep;
		$n++;
		}
	$resp_name = get_respondername($the_responder);	
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

	$fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
	$ret_arr[$i][0] = $the_message_id;		
	$ret_arr[$i][1] = $msg_row['ticket_id'];
	$ret_arr[$i][2] = $type_flag;
	$ret_arr[$i][3] = $fromname;
	$ret_arr[$i][4] = $respstring;
	$ret_arr[$i][5] = stripslashes_deep(shorten($msg_row['subject'], 18));
	$ret_arr[$i][6] = htmlentities(shorten($the_message, 2000));
	$ret_arr[$i][7] = $formatted_date;
	$ret_arr[$i][8] = get_owner($msg_row['_by']);	
	$ret_arr[$i][9] = $the_class;
	$ret_arr[$i][10] = $msg_row['id'];		
	$ret_arr[$i][11] = $readers_string;	
	$ret_arr[$i][12] = $msg_row['row_num'];
	$i++;
	} // end while

print json_encode($ret_arr);
exit();
?>