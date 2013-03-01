<?php
@session_start();
require_once('../../incs/functions.inc.php');
	
$ticket_ids = array();
$where = (isset($_GET['id'])) ? "WHERE `requester` = " . strip_tags($_GET['id']) : "";
$the_ret = array();	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` " . $where;
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	if(($row['ticket_id'] != 0) && ($row['ticket_id'] != 0)) {
		$ticket_ids[] = $row['ticket_id'];
		}
	}

foreach($ticket_ids as $val) {
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` `t`			
			WHERE `t`.`id` = " . $val;
	$result1 = mysql_query($query1);	
	while($row1 = stripslashes_deep(mysql_fetch_assoc($result1))){
		$the_ret[$val]['lat'] = $row1['lat'];		
		$the_ret[$val]['lng'] = $row1['lng'];		
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` `a` WHERE `a`.`ticket_id` = " . $row1['id'];	
		$result2 = mysql_query($query2) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while($row2 = stripslashes_deep(mysql_fetch_assoc($result2))){
			$resp_id = $row2['responder_id'];
			$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `r`.`id` = " . $resp_id;	
			$result3 = mysql_query($query3) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while($row3 = stripslashes_deep(mysql_fetch_assoc($result3))){	
				$the_id = $row3['id'];
				$the_ret[$val]['responders'][$the_id]['lat'] = $row3['lat'];	
				$the_ret[$val]['responders'][$the_id]['lng'] = $row3['lng'];			
				}
			}
		}
	}
print json_encode($the_ret);	
?>