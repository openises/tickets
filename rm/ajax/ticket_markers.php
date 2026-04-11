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
	$the_user = sanitize_int($_GET['user_id']);
	} else {
	exit;
	}
$ret_arr = array();	
$responder_id = (isset($_GET['responder_id'])) ? sanitize_int($_GET['responder_id']) : NULL;

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}assigns` WHERE `responder_id` = ? AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))";
$bgcolor = "#EEEEEE";
$result = db_query($query, [$the_user]);
$num=$result->num_rows;
if ($result->num_rows == 0) { 				// 8/6/08
	$ret_arr[0] = "No Assignments Currently";
	} else {
	$i = 0;
	while ($row = stripslashes_deep($result->fetch_assoc())){
		$query2 = "SELECT * FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `id` = ? AND `status` = ?";
		$result2 = db_query($query2, [$row['ticket_id'], $GLOBALS['STATUS_OPEN']]);
		$row2 = $result2 ? stripslashes_deep($result2->fetch_assoc()) : null;
		$ret_arr[$i][0] = $row2['id'];
		$ret_arr[$i][1] = $row2['scope'];
		$ret_arr[$i][2] = $row2['lat'];
		$ret_arr[$i][3] = $row2['lng'];			
		$ret_arr[$i][4] = stripslashes_deep(shorten($row2['description'], 30));
		$ret_arr[$i][5] = format_date_2(safe_strtotime($row2['problemstart']));		
		$i++;
		}				// end while
	}	//	end else
print json_encode($ret_arr);
exit();
?>