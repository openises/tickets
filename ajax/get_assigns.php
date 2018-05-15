<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */
$id = mysql_real_escape_string($_GET['id']);
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$ret_arr = array();

function adj_time($time_stamp) {
	$temp = mysql2timestamp($time_stamp);					// MySQL to integer form
	return date ("H:i", $temp);
	}

function assignment_list($id) {
	global $istest, $iw_width, $disposition, $patient, $incident, $num_rows, $internet, $by_severity, $sev_color;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	$assigns = array();
	@session_start();		// 
	$query = "SELECT *, 
		`a`.`id` AS `assigns_id`,
		`r`.`name` AS `responder_name`, 
		`r`.`handle` AS `responder_handle` 
		FROM `$GLOBALS[mysql_prefix]assigns` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON `r`.id=`a`.`responder_id`
		WHERE `a`.`ticket_id` = {$id} AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'
		ORDER BY `assigns_id` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_assigns = mysql_num_rows($result);
	$assigns[0][0] = $num_assigns;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$display_val = "block";
		$assignID = $row['assigns_id'];
		$ticketID = $id;
		$responderID = $row['responder_id'];
		$time_disp = $row['dispatched'];
		$time_resp = $row['responding'];
		$time_onsc = $row['on_scene'];
		$time_fenr = $row['u2fenr'];
		$time_farr = $row['u2farr'];
		$time_clear = $row['clear'];
		if (is_date($time_disp)) { 
			$disp_button ="<INPUT ID='disp_btn' TYPE= 'button' aria-label='Dispatched " .  adj_time($time_disp) . "' tabindex=2 CLASS='btn_chkd text_big' VALUE='Disp @ " . adj_time($time_disp) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else {
			$disp_button = "<INPUT ID='disp_btn' TYPE= 'button' aria-label='Not Dispatched yet' tabindex=2 CLASS='btn_not_chkd text_big' VALUE='Dispatched' onClick = 'set_assign(\'d\', " . $assignID . ", " . $ticketID . ", " . $responderID . ");' STYLE = 'display:" . $display_val . ";' />";
			} 
		if (is_date($time_resp)) { 
			$resp_button ="<INPUT ID='resp_btn' TYPE= 'button' aria-label='Responding " .  adj_time($time_resp) . "' tabindex=3 CLASS='btn_chkd text_big' VALUE='Resp @ " . adj_time($time_resp) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$resp_button ="<INPUT ID='resp_btn' TYPE= 'button' aria-label='Not Responding yet' tabindex=3 CLASS='btn_not_chkd text_big' VALUE='Responding' onClick = \"set_assign('r', " . $assignID . ", " . $ticketID . ", " . $responderID . ");\" STYLE = 'display:" . $display_val . ";' />";
			} 
		if (is_date($time_onsc)) { 
			$on_scene_button ="<INPUT ID='onsc_btn' TYPE= 'button' aria-label='On-scene " .  adj_time($time_onsc) . "' tabindex=4 CLASS='btn_chkd text_big' VALUE='On-scene @ " . adj_time($time_onsc) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$on_scene_button ="<INPUT ID='onsc_btn' TYPE= 'button' aria-label='Not on-scene yet' tabindex=4 CLASS='btn_not_chkd text_big' VALUE='On-scene' onClick = \"set_assign('s', " . $assignID . ", " . $ticketID . ", " . $responderID . ");\" STYLE = 'display:" . $display_val . ";' />";
			} 
		if (is_date($time_fenr)) { 
			$fenr_button ="<INPUT ID='f_enr_btn' TYPE= 'button' aria-label='En-route to Facility " .  adj_time($time_fenr) . "' tabindex=5 CLASS='btn_chkd text_big' VALUE=\"Fac'y enr @ " . adj_time($time_fenr) . "\" onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$fenr_button ="<INPUT ID='f_enr_btn' TYPE= 'button' aria-label='Not yet en-route to Facility' tabindex=5 CLASS='btn_not_chkd text_big' VALUE=\"Fac'y enroute\" onClick = \"set_assign('e', " . $assignID . ", " . $ticketID . ", " . $responderID . ");\" STYLE = 'display:" . $display_val . ";' />";
			}
		if (is_date($time_farr)) { 		// 5/19/11
			$farr_button ="<INPUT ID='f_arr_btn' TYPE= 'button' aria-label='Arrived at Facility " .  adj_time($time_farr) . "' tabindex=6 CLASS='btn_chkd text_big' VALUE=\"Fac'y arr @ " . adj_time($time_farr) . "\" onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$farr_button ="<INPUT ID='f_arr_btn' TYPE= 'button' aria-label='Not yet arrived at Facility' tabindex=6 CLASS='btn_not_chkd text_big' VALUE=\"Fac'y arrive\" onClick = \"set_assign('a', " . $assignID . ", " . $ticketID . ", " . $responderID . ");\" STYLE = 'display:" . $display_val . ";' />";
			}
		if (is_date($time_clear)) { 
			$clear_button ="<INPUT ID='clear_btn' TYPE= 'button' aria-label='Cleared from call " .  adj_time($time_clear) . "' tabindex=7 CLASS='btn_chkd text_big' VALUE='Clear @ " . adj_time($time_clear) . "' onClick = 'toss();' STYLE = 'display:" . $display_val . ";' />";
			} else { 
			$clear_button ="<INPUT ID='clear_btn' TYPE= 'button' aria-label='Not yet cleared from call' tabindex=7 CLASS='btn_not_chkd text_big' VALUE='Clear' onClick = \"set_assign('c', " . $assignID . ", " . $ticketID . ", " . $responderID . ");\" STYLE = 'display:" . $display_val . ";' />";	
			}		// end if (is_date($time_clear))	
		
		

		$assigns[$assignID][0] = $responderID;
		$assigns[$assignID][1] = $row['responder_name'];
		$assigns[$assignID][2] = $row['responder_handle'];
		$assigns[$assignID][3] = $disp_button;
		$assigns[$assignID][4] = $resp_button;
		$assigns[$assignID][5] = $clear_button;
		$assigns[$assignID][6] = $on_scene_button;
		$assigns[$assignID][7] = $fenr_button;
		$assigns[$assignID][8] = $farr_button;
		$assigns[$assignID][9] = $row['start_miles'];
		$assigns[$assignID][10] = $row['on_scene_miles'];
		$assigns[$assignID][11] = $row['end_miles'];
		$assigns[$assignID][12] = $row['miles'];
		$assigns[$assignID][13] = htmlentities($row['comments'], ENT_QUOTES);	
		$assigns[$assignID][14] = adj_time($time_disp);
		$assigns[$assignID][15] = adj_time($time_resp);
		$assigns[$assignID][16] = adj_time($time_onsc);
		$assigns[$assignID][17] = adj_time($time_fenr);
		$assigns[$assignID][18] = adj_time($time_farr);
		$assigns[$assignID][19] = adj_time($time_clear);
		}
	return $assigns;
	}
$ret_arr = assignment_list($id);
print json_encode($ret_arr);
exit();
?>