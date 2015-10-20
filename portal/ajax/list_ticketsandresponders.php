<?php
/*
9/10/13 New File - provides ticket and responder markers and infowindows to the portal
*/

@session_start();
require_once('../../incs/functions.inc.php');
	
$ticket_ids = array();
$request_ids = array();
$where = (isset($_GET['id'])) ? "WHERE `requester` = " . strip_tags($_GET['id']) : "";
$showall = ((isset($_GET['showall'])) && ($_GET['showall'] == 'yes')) ? true : false;
if($where == "") {
	$where .= ($showall == false) ? " WHERE `status` <> 'Closed' " : "";
	} else {
	$where .= ($showall == false) ? " AND `status` <> 'Closed' " : "";
	}
$the_ret = array();	

function get_request_details($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `ticket_id` = '" . $id . "' LIMIT 1";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$theReturn[0] = $row['id'];
		if ($row['status'] == 'Open') {
			$status = 1;
			} elseif ($row['status'] == 'Tentative') {
			$status = 2;				
			} elseif ($row['status'] == 'Accepted') {
			$status = 3;			
			} elseif ($row['status'] == 'Resourced') {
			$status = 4;			
			} elseif ($row['status'] == 'Completed') {
			$status = 5;		
			} elseif ($row['status'] == 'Declined') {
			$status = 6;	
			} elseif ($row['status'] == 'Closed') {
			$status = 7;					
			} else {
			$status = 9;				
			}
		if ($row['cancelled'] != NULL && $row['cancelled'] != "" && $row['cancelled'] != "0000-00-00 00:00:00") {
			$status = 8;
			}
		$theReturn[1] = $status;
		} else {
		$theReturn[0] = 0;
		}
	return $theReturn;
	}
	
function get_request_details2($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = '" . $id . "' LIMIT 1";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$theReturn[0] = $row['id'];
		if ($row['status'] == 'Open') {
			$status = 1;
			} elseif ($row['status'] == 'Tentative') {
			$status = 2;				
			} elseif ($row['status'] == 'Accepted') {
			$status = 3;			
			} elseif ($row['status'] == 'Resourced') {
			$status = 4;			
			} elseif ($row['status'] == 'Completed') {
			$status = 5;		
			} elseif ($row['status'] == 'Declined') {
			$status = 6;	
			} elseif ($row['status'] == 'Closed') {
			$status = 7;					
			} else {
			$status = 9;				
			}
		if ($row['cancelled'] != NULL && $row['cancelled'] != "" && $row['cancelled'] != "0000-00-00 00:00:00") {
			$status = 8;
			}
		$theReturn[1] = $status;
		} else {
		$theReturn[0] = 0;
		}
	return $theReturn;
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` " . $where;
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) == 0) {
	$the_ret[0] = -1;
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		if($row['ticket_id'] != 0) {
			$ticket_ids[] = $row['ticket_id'];
			} else {
			$request_ids[] = $row['id'];
			}
		}
	}
$x = 0;
foreach($ticket_ids as $val) {
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`	WHERE `id` = " . $val;
	$result1 = mysql_query($query1);
	if(mysql_num_rows($result1) == 0) {
		$the_ret[1][0]['id'] = 0;
		} else {
		while($row1 = stripslashes_deep(mysql_fetch_assoc($result1))){
			$details = get_request_details($val);
			$the_ret[1][$x]['id'] = $val;		
			$the_ret[1][$x]['lat'] = $row1['lat'];		
			$the_ret[1][$x]['lng'] = $row1['lng'];
			$the_ret[1][$x]['scope'] = $row1['scope'];	
			$the_ret[1][$x]['description'] = nl2br($row1['description']);
			$the_ret[1][$x]['request'] = $details[0];
			$the_ret[1][$x]['status'] = $details[1];
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` `a` WHERE `a`.`ticket_id` = " . $row1['id'];	
			$result2 = mysql_query($query2) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while($row2 = stripslashes_deep(mysql_fetch_assoc($result2))){
				$resp_id = $row2['responder_id'];
				$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `r`.`id` = " . $resp_id;	
				$result3 = mysql_query($query3) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				while($row3 = stripslashes_deep(mysql_fetch_assoc($result3))){	
					$the_id = $row3['id'];
					$the_ret[1][$x]['responders'][$the_id]['id'] = $row3['id'];	
					$the_ret[1][$x]['responders'][$the_id]['lat'] = $row3['lat'];	
					$the_ret[1][$x]['responders'][$the_id]['lng'] = $row3['lng'];
					$the_ret[1][$x]['responders'][$the_id]['handle'] = $row3['icon_str'];		
					$the_ret[1][$x]['responders'][$the_id]['jobtitle'] = $row1['scope'];	
					}
				}
			$x++;
			}
		}
	}

$y = 0;	
foreach($request_ids as $val2) {
	$query4 = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . $val2;
	$result4 = mysql_query($query4);
	if(mysql_num_rows($result4) == 0) {
		$the_ret[0][0]['id'] = 0;
		} else {
		while($row4 = stripslashes_deep(mysql_fetch_assoc($result4))){
			$details = get_request_details2($val2);
			$the_ret[0][$y]['id'] = $val2;		
			$the_ret[0][$y]['lat'] = $row4['lat'];		
			$the_ret[0][$y]['lng'] = $row4['lng'];
			$the_ret[0][$y]['scope'] = $row4['scope'];	
			$the_ret[0][$y]['description'] = nl2br($row4['description']);
			$the_ret[0][$y]['canedit'] = 1;	
			$the_ret[0][$y]['status'] = $details[1]; 
			$y++;
			}
		}
	}

//dump($the_ret);
print json_encode($the_ret);
exit();
?>