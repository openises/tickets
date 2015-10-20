<?php
/*
9/10/13 - New file, gets markers for mobile screen for assigned Tickets
*/

@session_start();
require_once('../../incs/functions.inc.php');
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

if($_GET['user_id'] != 0) {
	$the_user = $_GET['user_id'];
	} else {
	exit;
	}
$ret_arr = array();	
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = '" . $the_user . "' AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))"; 
$bgcolor = "#EEEEEE";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$ret_arr[0] = "No Assignments Currently";
	} else {
	$i = 0;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = '" . $row['ticket_id'] . "' AND `status` = " . $GLOBALS['STATUS_OPEN']; 		
		$result2 = mysql_query($query2) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
		$ret_arr[$i][0] .= $row2['id'];
		$ret_arr[$i][1] .= $row2['scope'];
		$ret_arr[$i][2] .= $row2['lat'];
		$ret_arr[$i][3] .= $row2['lng'];			
		$ret_arr[$i][4] .= stripslashes_deep(shorten($row2['description'], 30));
		$ret_arr[$i][5] .= format_date_2(strtotime($row2['problemstart']));		
		$i++;
		}				// end while
	}	//	end else
print json_encode($ret_arr);
exit();
?>