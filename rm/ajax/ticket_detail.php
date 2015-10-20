<?php
/*
9/10/13 - new file, Shows Ticket detail for selected Ticket for mobile screen
*/

@session_start();
require_once('../../incs/functions.inc.php');
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
	
$the_status_vals = array();
$the_status_vals[0] = "Reserved";
$the_status_vals[1] = "Closed";
$the_status_vals[2] = "Open";
$the_status_vals[3] = "Scheduled";

function the_ticket($id) {
	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$query = "SELECT *,
	`problemstart` AS `problemstart`,
	`problemend` AS `problemend`,
	`date` AS `date`,
	`booked_date` AS `booked_date`,		
	`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
	`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
	`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
	`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
	`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,	
	`$GLOBALS[mysql_prefix]ticket`.`contact` AS `the_contact`,	
	`$GLOBALS[mysql_prefix]ticket`.`phone` AS `the_phone`,		
	`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
	`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
	`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
	`$GLOBALS[mysql_prefix]ticket`.`rec_facility` AS `rec_facility`,	
	`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
	`rf`.`name` AS `rec_fac_name`,
	`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
	`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
	`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
	FROM `$GLOBALS[mysql_prefix]ticket` 
	LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
	LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
	LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
	WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result) == 0) { 	
		return false;
		} else {
		$row = stripslashes_deep(mysql_fetch_array($result));
		return $row;
		}
	}	

function get_assigns_id($user_id, $ticket) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = '" . $user_id . "' AND `ticket_id` = '" . $ticket . "'"; 
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$assigns_arr = array();
	$assigns_arr[0] = $row['id'];
	$assigns_arr[1] = (($row['dispatched'] != "") && ($row['dispatched'] != NULL)) ? format_date_2(strtotime($row['dispatched'])): "";
	$assigns_arr[2] = (($row['responding'] != "") && ($row['responding'] != NULL)) ? format_date_2(strtotime($row['responding'])): "";
	$assigns_arr[3] = (($row['on_scene'] != "") && ($row['on_scene'] != NULL)) ? format_date_2(strtotime($row['on_scene'])): "";
	$assigns_arr[4] = (($row['u2fenr'] != "") && ($row['u2fenr'] != NULL)) ? format_date_2(strtotime($row['u2fenr'])): "";
	$assigns_arr[5] = (($row['u2farr'] != "") && ($row['u2farr'] != NULL)) ? format_date_2(strtotime($row['u2farr'])): "";
	$assigns_arr[6] = (($row['clear'] != "") && ($row['clear'] != NULL)) ? format_date_2(strtotime($row['clear'])): "";
	$assigns_arr[7] = (($row['start_miles'] != "") && ($row['start_miles'] != NULL)) ? $row['start_miles']: "";
	$assigns_arr[8] = (($row['end_miles'] != "") && ($row['end_miles'] != NULL)) ? $row['end_miles']: "";
	$assigns_arr[9] = (($row['on_scene_miles'] != "") && ($row['on_scene_miles'] != NULL)) ? $row['on_scene_miles']: "";	
	return $assigns_arr;
	}
	
function get_recfac_address($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = '" . $id . "' LIMIT 1"; 
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result) != 0) { 				// 8/6/08	
		$row = stripslashes_deep(mysql_fetch_assoc($result));	
		$ret = $row['lat'] . " " . $row['lng'];
		} else {
		$ret = "";
		}
	return $ret;
	}
	
$ret_arr = array();

if($_GET['user_id'] != 0) {
	$the_user = $_GET['user_id'];
	} else{
	exit;
	}

$ticket_id = (isset($_GET['ticket_id'])) ? $_GET['ticket_id'] : NULL;
$row = the_ticket($ticket_id);

if (!$row) {
	$print = "<TABLE style='width: 100%;'><TR style='width: 100%;'><TD style='width: 100%;'>No Ticket Details</TD></TR></TABLE>";
	} else {
	switch ($row['severity']) {		
		case 0:
			$sev_string = "Normal";
			$bgcolor = "green";
			$txtcol = "white";
			break;			
		case 1:
			$sev_string = "Medium";
			$bgcolor = "blue";
			$txtcol = "white";			
			break;
		case 2:
			$sev_string = "High";
			$bgcolor = "red";
			$txtcol = "yellow";			
			break;			
		default:
			$sev_string = "";
			break;
		}						// end switch(($row['severity']))
	$rec_fac_address = ($row['rec_facility'] != 0) ? get_recfac_address($row['rec_facility']) : "";
	$print = "<TABLE style='width: 100%; border: 2px solid #707070;'>";	
	$print .= "<TR style='width: 100%; color: #FFFFFF; background-color: #707070;'><TD COLSPAN=2 style='text-align: center; font-weight: bold;'>TICKET DETAILS<BR />";
	$print .= "<SPAN id='close_tkt_detail_but' class='plain' style='float: right; z-index: 999999; text-align: center; width: 40px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='close_ticket_detail();'><IMG SRC = './images/close.png' BORDER=0 STYLE = 'vertical-align: middle'></span>";
	$print .= "<SPAN id='directions_but' class='plain' style='float: right; z-index: 999999; text-align: center;width: 40px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='setDirections(\"" . $row['lat'] . "," . $row['lng'] . "\", \"" . $rec_fac_address . "\");'><IMG SRC = './images/directions.png' BORDER=0 STYLE = 'vertical-align: middle'></span>";	
	$print .= "<SPAN id='ticket_msgs' class='plain' style='float: right; z-index: 999999; text-align: center; width: 40px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='tkt_messages(" . $ticket_id . ");'>Tkt Msgs</span>";
	$print .= "</TD></TR>";
	$rec_fac = $row['rec_facility'];
	$rec_fac_name = ($row['rec_fac_name'] != "") ? $row['rec_fac_name'] : "Not Set";
	$booked_date = (($row['booked_date'] != NULL) && ($row['booked_date'] != "0000-00-00 00:00:00")) ? format_date_2(strtotime($row['booked_date'])) : "";
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Title</TD>";		
	$print .= "<TD style='width: 70%; background-color: #CECECE; color: #000000;'>" . $row['scope'] . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Severity</TD>";		
	$print .= "<TD style='font-weight: bold; width: 70%; background-color: " . $bgcolor . "; color: " . $txtcol . ";'>" . $sev_string . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";		
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Status</TD>";		
	$print .= "<TD style='width: 70%; background-color: #CECECE; color: #000000;'>" . $the_status_vals[$row['status']] . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";		
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Contact</TD>";		
	$print .= "<TD style='width: 70%; background-color: #CECECE; color: #000000;'>" . $row['the_contact'] . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";	
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Phone</TD>";		
	$print .= "<TD style='width: 70%; background-color: #CECECE; color: #000000;'>" . $row['the_phone'] . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";		
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Address</TD>";		
	$print .= "<TD style='width: 70%; background-color: #CECECE; color: #000000;'>" . $row['tick_street'] . "<BR />" .  $row['tick_city'] . "<BR />" . $row['tick_state'] . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";		
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Receiving Facility</TD>";			
	$print .= "<TD style='width: 70%; background-color: #DEDEDE; color: #000000;'>" . $rec_fac_name . "</TD></TR>";	
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";			
	$print .= "<TR style='width: 100%;'>";	
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>911 Contacted</TD>";			
	$print .= "<TD style='width: 20%; background-color: #CECECE; color: #000000;'>" . $row['nine_one_one'] . "</TD></TR>";	
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";				
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Description</TD>";			
	$print .= "<TD style='width: 70%; background-color: #DEDEDE; color: #000000;'>" . $row['tick_descr'] . "</TD></TR>";
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";				
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Start Time</TD>";			
	$print .= "<TD style='width: 20%; background-color: #CECECE; color: #000000;'>" . format_date_2(strtotime($row['problemstart'])) . "</TD></TR>";	
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";			
	$print .= "<TR style='width: 100%;'>";	
	$print .= "<TR style='width: 100%;'>";
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Updated</TD>";			
	$print .= "<TD style='width: 20%; background-color: #CECECE; color: #000000;'>" . format_date_2(strtotime($row['updated'])) . "</TD></TR>";	
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";			
	$print .= "<TR style='width: 100%;'>";		
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Scheduled Time</TD>";			
	$print .= "<TD style='width: 20%; background-color: #CECECE; color: #000000;'>" . $booked_date . "</TD></TR>";	
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";			
	$print .= "<TR style='width: 100%;'>";	
	$print .= "<TD style='width: 30%; background-color: #000000; color: #FFFFFF; font-weight: bold;'>Comments</TD>";			
	$print .= "<TD style='width: 20%; background-color: #CECECE; color: #000000;'>" . $row['comments'] . "</TD></TR>";		
	$print .= "<TR class='spacer'><TD colspan=99 class='spacer'>&nbsp;</TD></TR>";			
	$print .= "</TABLE>";
	}	//	end else
$assigns_ret = get_assigns_id($the_user, $ticket_id);
$ret_arr[0] = $assigns_ret[0];
$ret_arr[1] = $assigns_ret[1];
$ret_arr[2] = $assigns_ret[2];
$ret_arr[3] = $assigns_ret[3];
$ret_arr[4] = $assigns_ret[4];
$ret_arr[5] = $assigns_ret[5];
$ret_arr[6] = $assigns_ret[6];
$ret_arr[7] = $assigns_ret[7];
$ret_arr[8] = $assigns_ret[8];
$ret_arr[9] = $assigns_ret[9];
$ret_arr[10] = $rec_fac;
$ret_arr[11] = $print;
$ret_arr[12] = stripslashes_deep(trim($row['comments']));
print json_encode($ret_arr);
exit();
?>