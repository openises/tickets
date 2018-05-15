<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$sess_id = $_SESSION['id'];
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}

//dump($_GET);
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
	if(my_is_float($lat1) && my_is_float($lon1) && my_is_float($lat2) && my_is_float($lon2)) {
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344); 
			} else if ($unit == "N") {
			return ($miles * 0.8684);
			} else {
			return $miles;
			}
		} else {
		return 0;
		}
	}

function subval_sort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}
	
$tik_id = (array_key_exists('ticket_id', $_GET)) ? $_GET['ticket_id'] : 0;
$responder_id = (array_key_exists('responder_id', $_GET)) ? $_GET['responder_id'] : NULL;
$smsg_provider = return_provider_name(get_msg_variable('smsg_provider'));
$smsg_providers = array('SMS Responder','SMS Broadcast','MOTOTRBO Text Message','Txt Local');
$using_smsg = ((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) ? true : false;
$t_query = "SELECT `t`.`lat` AS `t_lat`, `t`.`lng` AS `t_lng`	FROM `$GLOBALS[mysql_prefix]ticket` `t`	WHERE `id` = {$tik_id} LIMIT 1";
$t_result = mysql_query($t_query) or do_error($t_query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
$t_row = stripslashes_deep(mysql_fetch_assoc($t_result), MYSQL_ASSOC);			
$assigned_resp = array();
$theTickets = array();
$func = (array_key_exists('func', $_GET)) ? $_GET['func'] : 0;

switch($func) {
	case 'doselect':		//	Show Incident Selection
		$return = "";
		$query = "SELECT DISTINCT `ticket_id` , `scope`, `severity`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			WHERE `t`.`status` = {$GLOBALS['STATUS_OPEN']} OR `t`.`status` = {$GLOBALS['STATUS_SCHEDULED']}	
			ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;				// 4/28/10
	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$no_tickets = mysql_affected_rows();
		$return .= "<CENTER>";
		$return .= "<H3>Mail to " . get_text("Units") . "</H3>";
		$return .= "<P>";
		$return .= "<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>";
		$return .= "<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>";
		$return .= "<DIV STYLE='width: 500px; text-align: left;'>";
		$bg_colors_arr = array ("transparent", "lime", "red");		// for severity
		$return .= "<LABEL for='frm_sel_inc' onmouseout='UnTip()' onmouseover=\"Tip('Select units assigned to specific Incident');\"><EM>". get_text("Units"). " assigned to ". get_text("Incident") . "</EM></LABEL>: 
				<SELECT ID='frm_sel_inc' NAME='frm_sel_inc' STYLE='display: inline-block;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; window.sel_inc=this.value;'>\n\t
				<OPTION VALUE=0 SELECTED>All incidents </OPTION>\n";
			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
				$bg_color = $bg_colors_arr[$row['severity']];
				if(!(empty($row['scope']))) {				// 6/28/09
					$return .= "\t<OPTION VALUE='{$row['incident']}' STYLE='background-color:{$bg_color}; color:black;' >{$row['scope']} </OPTION>\n";
					}
				}
		$return .= "</SELECT>";
		$return .= "</DIV>";
		$return .= "</FORM>";
		$return .= "</P>";
		$return .= "<BR />";
		$return .= "<SPAN ID='all_units_but' class='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(0, 'Contact Units', 'contact_all');\"><SPAN STYLE='float: left;'>Contact All " . get_text('Units') . "</SPAN><IMG STYLE='float: right;' SRC='./images/mail_small.png' BORDER=0></SPAN>";
		$return .= "<BR />";
		$return .= "<BR />";
		$return .= "<BR />";
		$return .= "<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(window.sel_inc, 'Contact Units', 'contact_sel');\"><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
		$return .= "<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"$('extra').style.display='none';\"><SPAN STYLE='float: left;'>" . get_text('Cancel') . "</SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN><BR /><BR />";
		$return .= "</CENTER>";
	break;
	
	case 'all_units':
		$default_msg = "Ticket ID *" . $tik_id . "*";	//	10/23/12
		if ((!array_key_exists ( 'selected_inc', $_GET)) || ($_GET['selected_inc']==0)) {
			$default_msg = "Ticket ID *" . $tik_id . "*";	//	10/23/12
			$query_ass = "SELECT *, 
				`r`.`id` AS `responder_id`
				FROM `$GLOBALS[mysql_prefix]assigns` `a`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	 `s` ON (`r`.`un_status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)
				WHERE (LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')) AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
				ORDER BY `r`.`id` ASC ";
			$result_ass = mysql_query($query_ass) or do_error($query_ass, 'mysql query failed', mysql_error(), __FILE__, __LINE__);				
			while($row_ass = stripslashes_deep(mysql_fetch_assoc($result_ass), MYSQL_ASSOC)){
				$assigned_resp[] = $row_ass['responder_id'];
				}
			$query = "SELECT *,	`r`.`id` AS `responder_id`,
				`r`.`lat` AS `r_lat`,
				`r`.`lng` AS `r_lng`				
				FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	`s` ON (`r`.`un_status_id` = `s`.`id`)
				WHERE LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')
				ORDER BY  `name` ASC ";
			}
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$lines = mysql_affected_rows() +8;
		$no_rows = mysql_num_rows($result);
		$return = "<FORM ID='contact_form' NAME='contact_form' METHOD='post' ACTION='./ajax/form_post.php?q=". $sess_id . "&function=contact'>";
		$return .= "<INPUT TYPE='hidden' NAME='frm_step' VALUE='3' />\n";
		$return .= "<INPUT TYPE='hidden' NAME='frm_add_str' VALUE='' />\n";
		$return .= "<TABLE ALIGN = 'center' border=0>";
		$return .= "<TR>";
		$return .= "<TD COLSPAN=99 ALIGN='center'>&nbsp;</TD>";
		$return .= "</TR>";
		if($no_rows>0) {
			$return .= "<TR>";
			$return .= "<TD COLSPAN=99 ALIGN='center'>";
			$return .= "<SPAN id='clr_spn' CLASS='plain text' style='width: 100px; display: none; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_clear();'><SPAN STYLE='float: left;'>" . get_text('Uncheck All') . "</SPAN><IMG STYLE='float: right;' SRC='./images/unselect_all_small.png' BORDER=0></SPAN>";
			$return .= "<SPAN id='chk_spn' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_check();'><SPAN STYLE='float: left;'>" . get_text('Check All') . "</SPAN><IMG STYLE='float: right;' SRC='./images/select_all_small.png' BORDER=0></SPAN>";
			$return .= "</TD>";
			$return .= "</TR>";
			$return .= "<TR>";
			$return .= "<TD COLSPAN=99>&nbsp;</TD>";
			$return .= "</TR>";
			$the_arr = array();
			$n=1;
			while($row = mysql_fetch_assoc($result)) {
				//	SMS Gateway IDs
				$smsg_arr = array();
				$temp_arr = array();
				$temp_smsg = get_smsgid($row['responder_id']);
				if(!is_array($temp_smsg)) {
					$temp_smsg = array();
					$temp_smsg[] = get_smsgid($row['responder_id']);
					}
				$smsg_length = count($temp_smsg);
				foreach($temp_smsg as $val) {
					array_push($temp_arr, $val);
					}
				$smsg_arr = array_unique($temp_arr);
				$smsg_addr = implode(",", $smsg_arr);
				if($smsg_length > 1) {
					$the_arr[$n]['smsg_id'] = "...Multiple...";
					} else {
					$the_arr[$n]['smsg_id'] = $smsg_addr;
					}
				//	Cellphone Numbers
				$cell_arr = array();
				$temp_arr = array();
				$temp_cell = get_mdb_cell($row['responder_id']);
				if(!is_array($temp_cell)) {
					$temp_cell = array();
					$temp_cell[] = get_mdb_cell($row['responder_id']);
					}				
				$cell_length = count($temp_cell);
				foreach($temp_cell as $val) {
					array_push($temp_arr, $val);
					}
				$cell_arr = array_unique($temp_arr);
				$theCells = implode(",", $cell_arr);
				if($cell_length > 1) {
					$the_arr[$n]['cellphone'] = "...Multiple...";
					} else {
					$the_arr[$n]['cellphone'] = $theCells;
					}
				//	Contact Names
				$name_arr = array();
				$temp_arr = array();
				$temp_names = get_mdb_names($row['responder_id']);
				if(!is_array($temp_names)) {
					$temp_names = array();
					$temp_names[] = get_mdb_names($row['responder_id']);
					}
				$names_length = count($temp_names);
				foreach($temp_names as $val) {
					array_push($temp_arr, $val);
					}
				$name_arr = array_unique($temp_arr);
				$theNames = implode(",", $name_arr);	
				if($names_length > 1) {
					$the_arr[$n]['name'] = "...Multiple...";
					} else {
					$the_arr[$n]['name'] = $theNames;
					}
				$the_arr[$n]['handle'] = $row['handle'];	
				$the_arr[$n]['responder_id'] = $row['responder_id'];
				//	Email addresses - contact via
				$em_arr = array();
				$temp_arr = array();
				$temp_addrs = get_contact_via($row['responder_id']);
				if(!is_array($temp_addrs)) {
					$temp_addrs = array();
					$temp_addrs[] = get_contact_via($row['responder_id']);
					}
				$addrs_length = count($temp_addrs);
				foreach($temp_addrs as $val) {
					if (is_email($val)) {
						array_push($temp_arr, $val);
						}
					}
				$em_arr = array_unique($temp_arr);
				$em_addr = implode("|", $em_arr);
				if($addrs_length > 1) {
					$the_arr[$n]['contact_via'] = "...Multiple...";
					} else {
					$the_arr[$n]['contact_via'] = $em_addr;
					}
				$the_arr[$n]['bg_color'] = $row['bg_color'];		
				$the_arr[$n]['text_color'] = $row['text_color'];
				$the_arr[$n]['distance'] = (isset($t_row['t_lat'])) ? distance($row['r_lat'], $row['r_lng'], $t_row['t_lat'], $t_row['t_lng'], "N") : 0;	//	populate array entry with distance from responder to ticket
				$the_arr[$n]['status'] = $row['status_val'];
				$the_arr[$n]['fullnames'] = $theNames;
				$the_arr[$n]['fullcontactvia'] = $em_addr;
				$the_arr[$n]['fullsmsgids'] = $smsg_addr;
				$the_arr[$n]['fullcells'] = $theCells;
				$n++;
				}	//	End While
			if((isset($_GET['the_ticket'])) && ($_GET['the_ticket'] != 0)) {
				$the_arr = subval_sort($the_arr,'distance'); 	//	sort array by distance ascending but only if the mail form is called from a Ticket
				}

			$i=1;
			$return .= "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_units_legend() . "</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_unit_status_legend() . "</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>\n";
			$return .= "<TR><TD>\n";
			$return .= "<TABLE ALIGN = 'center' BORDER=0><TR><TD><SPAN class='text text_center' style='width: 100%; display: block;'>Check checkbox to Select Unit (any assigned units already selected)</SPAN></TD></TR><TR><TD>\n";
			$return .= "<DIV class='container' style='display: block; width: 100%; min-height: 200PX; max-height: 400px; overflow-y: scroll; overflow-x: none;'>\n";
			foreach($the_arr as $val) {
				if(!empty($assigned_resp)) {
					$checked = in_array($val['responder_id'],$assigned_resp) ? "checked" : "";
					} else {
					$checked = "";
					}
				$smsg = $cell = "";
				$e_add = (($val['contact_via'] == NULL) || ($val['contact_via'] == "")) ? "<SPAN TITLE='No email address stored' class='cell text' style='color: LightGrey;'>(E) NONE</SPAN>" : "<SPAN TITLE='{$val['fullcontactvia']}' class='cell text'>(E) " . $val['contact_via'] . "</SPAN>\n" ;
				if($using_smsg && $smsg_provider == "SMS Responder") {
					$smsg = (($val['smsg_id'] == NULL) || ($val['smsg_id'] == "")) ? "<SPAN TITLE='SMS Gateway ID stored' class='cell text' style='color: LightGrey; display:'>(SMSG) NONE</SPAN>" : "<SPAN TITLE='{$val['fullsmsgids']}' class='cell text'>(SMSG) " . $val['smsg_id'] . "</SPAN>\n" ;
					}
				if($using_smsg && $smsg_provider == "Txt Local") {				
					$cell = (($val['cellphone'] == NULL) || ($val['cellphone'] == "")) ? "<SPAN TITLE='No Cellphone number stored' class='cell text' style='color: LightGrey;'>(CELL) NONE</SPAN>" : "<SPAN TITLE='{$val['fullcells']}' class='cell text'>(CELL) " . $val['cellphone'] . "</SPAN>\n" ;
					}
				$dist = (round($val['distance'],1) != 0) ? "Dist: " . round($val['distance'],2) : "";         
				$return .= "\t<SPAN class='row' STYLE='background-color:{$val['bg_color']}; color:{$val['text_color']};'>
					<SPAN class='cell text'><INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$val['contact_via']}:{$val['responder_id']}:{$val['smsg_id']}:' {$checked}></SPAN>
					<SPAN class='cell text'>" . $dist . "</SPAN>
					<SPAN class='cell text' TITLE='{$val['status']}'>{$val['handle']}</SPAN>
					<SPAN class='cell text' TITLE='{$val['fullnames']}'>{$val['name']}</SPAN>
					{$e_add} 
					{$smsg}
					{$cell}
					</SPAN>\n";
				$i++;
				}		// end foreach()
		$return .= "<BR />";
		$return .= "</DIV>";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "</TABLE>";
		$return .= "</TD>";
		$return .= "<TD style='vertical-align: top;'>";
		$return .= "<TABLE BORDER=0>";
		$return .= "<TR VALIGN='top' CLASS='even'><TD CLASS='td_label' ALIGN='right'>Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 55 VALUE='" . $default_msg . "'></TD></TR>";
		$return .= "<TR VALIGN='top' CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=45 ROWS=4 wrap='soft'></TEXTAREA><BR /><SPAN CLASS='warn'>" . get_text("messaging help") . "</SPAN></TD></TR>";

		$return .= "<TR VALIGN = 'TOP' CLASS='even'>";
		$return .= "<TD ALIGN='right' CLASS='td_label'>Signal: </TD><TD>";

		$return .= "<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
		$return .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			$return .= "\t<OPTION VALUE='" . $row['code'] . "'>" . $row['code'] . "|" . $row['text'] . "</OPTION>\n";
			}
		$return .= "</SELECT>";
		$return .= "<BR />";
		$return .= "<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>";
		$return .= "<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'></SPAN>";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "<TR VALIGN = 'TOP' CLASS='even'>";
		$return .= "<TD ALIGN='right' CLASS='td_label'>Standard Message: </TD><TD>";
		$return .= "<SELECT NAME='std_msgs' onChange = 'set_message(this.options[this.selectedIndex].value, document.contact_form);'>";
		$return .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
		$return .= get_standard_messages_sel();
		$return .= "</SELECT>";
		$return .= "<BR />";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "<TR VALIGN='top' CLASS='odd'>";
		$return .= "<TD ALIGN='center' COLSPAN=2><BR /><BR />";
		$return .= "<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_sendform(document.contact_form);'><SPAN STYLE='float: left;'>" . get_text('Next') ."</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
		$return .= "<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.contact_form.reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
		$return .= "</TD>";
		$return .= "</TR>";
		if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
			$return .= "<TR><TD>&nbsp;</TD></TR>	";			
			$return .= "<TR>";
			$return .= "<TD ALIGN='left' COLSPAN=2>";
			$return .= "<input type='radio' name='use_smsg' VALUE='0' checked> Use Email<br>";
			$return .= "<input type='radio' name='use_smsg' VALUE='1'> Use " . return_provider_name(get_msg_variable('smsg_provider')) . "?<br>";
			$return .= "</TD>";
			$return .= "</TR>";
			} else {
			$return .= "<INPUT TYPE='hidden' NAME='use_smsg' VALUE='0' />";
			}
		$return .= "<INPUT type='hidden' NAME='frm_resp_ids' VALUE='' />";
		$return .= "<INPUT type='hidden' NAME='frm_smsg_ids' VALUE='' />";
		$return .= "<INPUT type='hidden' NAME='frm_ticket_id' VALUE='" . $tik_id . "' />";					
		$return .= "</TABLE></TD></TR></TABLE></FORM>";
		} else {
		$return .= "<TR style-'width: 100%;'><TD COLSPAN=99>No assigned reponders</TD></TR></TABLE>";
		}
	break;

	case 'all_incidents':
		$default_msg = "Ticket ID *" . $tik_id . "*";	//	10/23/12
		$query = "SELECT *, 
			`r`.`id` AS `responder_id`,
			`r`.`lat` AS `r_lat`,
			`r`.`lng` AS `r_lng`,
			`t`.`id` AS `tick_id`,
			`t`.`scope` AS `ticket_scope`
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	 `s` ON (`r`.`un_status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)
			WHERE (LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')) AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
			ORDER BY `r`.`id` ASC ";
		$result = mysql_query($query);				
		$lines = mysql_num_rows($result) +8;
		$no_rows = mysql_num_rows($result);
		$return = "<FORM ID='contact_form' NAME='contact_form' METHOD='post' ACTION='./ajax/form_post.php?q=". $sess_id . "&function=contact'>";
		$return .= "<INPUT TYPE='hidden' NAME='frm_step' VALUE='3' />\n";
		$return .= "<INPUT TYPE='hidden' NAME='frm_add_str' VALUE='' />\n";
		$return .= "<TABLE ALIGN = 'center' border=0>";
		$return .= "<TR>";
		$return .= "<TD COLSPAN=99 ALIGN='center'>&nbsp;</TD>";
		$return .= "</TR>";
		if($no_rows>0) {
			$return .= "<TR>";
			$return .= "<TD COLSPAN=99 ALIGN='center'>";
			$return .= "<SPAN id='clr_spn' CLASS='plain text' style='width: 100px; display: none; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_clear();'><SPAN STYLE='float: left;'>" . get_text('Uncheck All') . "</SPAN><IMG STYLE='float: right;' SRC='./images/unselect_all_small.png' BORDER=0></SPAN>";
			$return .= "<SPAN id='chk_spn' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_check();'><SPAN STYLE='float: left;'>" . get_text('Check All') . "</SPAN><IMG STYLE='float: right;' SRC='./images/select_all_small.png' BORDER=0></SPAN>";
			$return .= "</TD>";
			$return .= "</TR>";
			$return .= "<TR>";
			$return .= "<TD COLSPAN=99>&nbsp;</TD>";
			$return .= "</TR>";
			$the_arr = array();
			$n=1;
			while($row = mysql_fetch_assoc($result)) {
				$assigned_resp[] = $row['responder_id'];
				$theTickets[$row['tick_id']] = $row['ticket_scope'];
				$smsg_arr = array();
				$temp_arr = array();
				$temp_smsg = get_smsgid($row['responder_id']);
				$smsg_length = count($temp_smsg);
				foreach($temp_smsg as $val) {
					array_push($temp_arr, $val);
					}
				$smsg_arr = array_unique($temp_arr);
				$smsg_addr = implode(",", $smsg_arr);
				if($smsg_length > 1) {
					$the_arr[$n]['smsg_id'] = "...Multiple...";
					} else {
					$the_arr[$n]['smsg_id'] = $smsg_addr;
					}
					
				$cell_arr = array();
				$temp_arr = array();
				$temp_cell = get_mdb_cell($row['responder_id']);
				$cell_length = count($temp_cell);
				foreach($temp_cell as $val) {
					array_push($temp_arr, $val);
					}
				$cell_arr = array_unique($temp_arr);
				$theCells = implode(",", $cell_arr);
				if($cell_length > 1) {
					$the_arr[$n]['cellphone'] = "...Multiple...";
					} else {
					$the_arr[$n]['cellphone'] = $theCells;
					}
				$name_arr = array();
				$temp_arr = array();
				$temp_names = get_mdb_names($row['responder_id']);
				$names_length = count($temp_names);
				foreach($temp_names as $val) {
					array_push($temp_arr, $val);
					}
				$name_arr = array_unique($temp_arr);
				$theNames = implode(",", $name_arr);	
				if($names_length > 1) {
					$the_arr[$n]['name'] = "...Multiple...";
					} else {
					$the_arr[$n]['name'] = $theNames;
					}
				$the_arr[$n]['handle'] = $row['handle'];	
				$the_arr[$n]['responder_id'] = $row['responder_id'];
				$em_arr = array();
				$temp_arr = array();
				$temp_addrs = get_contact_via($row['responder_id']);
				$addrs_length = count($temp_addrs);
				foreach($temp_addrs as $val) {
					if (is_email($val)) {
						array_push($temp_arr, $val);
						}
					}
				$em_arr = array_unique($temp_arr);
				$em_addr = implode("|", $em_arr);
				if($addrs_length > 1) {
					$the_arr[$n]['contact_via'] = "...Multiple...";
					} else {
					$the_arr[$n]['contact_via'] = $em_addr;
					}
				$the_arr[$n]['bg_color'] = $row['bg_color'];		
				$the_arr[$n]['text_color'] = $row['text_color'];
				$the_arr[$n]['distance'] = (isset($t_row['t_lat'])) ? distance($row['r_lat'], $row['r_lng'], $t_row['t_lat'], $t_row['t_lng'], "N") : 0;	//	populate array entry with distance from responder to ticket
				$the_arr[$n]['status'] = $row['status_val'];
				$the_arr[$n]['fullnames'] = $theNames;
				$the_arr[$n]['fullcontactvia'] = $em_addr;
				$the_arr[$n]['fullsmsgids'] = $smsg_addr;
				$the_arr[$n]['fullcells'] = $theCells;
				$n++;
				}	//	End While
			if((isset($_GET['the_ticket'])) && ($_GET['the_ticket'] != 0)) {
				$the_arr = subval_sort($the_arr,'distance'); 	//	sort array by distance ascending but only if the mail form is called from a Ticket
				}

			$i=1;
			$return .= "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_units_legend() . "</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_unit_status_legend() . "</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>\n";
			$return .= "<TR><TD>\n";
			$return .= "<TABLE ALIGN = 'center' BORDER=0><TR><TD><SPAN class='text text_center' style='width: 100%; display: block;'>Check checkbox to Select Unit</SPAN></TD></TR><TR><TD>\n";
			$return .= "<DIV class='container' style='display: block; width: 100%; min-height: 200PX; max-height: 400px; overflow-y: scroll; overflow-x: none;'>\n";
			foreach($the_arr as $val) {
				if(!empty($assigned_resp)) {
					$checked = in_array($val['responder_id'],$assigned_resp) ? "checked" : "";
					} else {
					$checked = "";
					}
				$smsg = $cell = "";
				$e_add = (($val['contact_via'] == NULL) || ($val['contact_via'] == "")) ? "<SPAN TITLE='No email address stored' class='cell text' style='color: LightGrey;'>(E) NONE</SPAN>" : "<SPAN TITLE='{$val['fullcontactvia']}' class='cell text'>(E) " . $val['contact_via'] . "</SPAN>\n" ;
				if($using_smsg && $smsg_provider == "SMS Responder") {
					$smsg = (($val['smsg_id'] == NULL) || ($val['smsg_id'] == "")) ? "<SPAN TITLE='SMS Gateway ID stored' class='cell text' style='color: LightGrey; display:'>(SMSG) NONE</SPAN>" : "<SPAN TITLE='{$val['fullsmsgids']}' class='cell text'>(SMSG) " . $val['smsg_id'] . "</SPAN>\n" ;
					}
				if($using_smsg && $smsg_provider == "Txt Local") {				
					$cell = (($val['cellphone'] == NULL) || ($val['cellphone'] == "")) ? "<SPAN TITLE='No Cellphone number stored' class='cell text' style='color: LightGrey;'>(CELL) NONE</SPAN>" : "<SPAN TITLE='{$val['fullcells']}' class='cell text'>(CELL) " . $val['cellphone'] . "</SPAN>\n" ;
					}
				$dist = (round($val['distance'],1) != 0) ? "Dist: " . round($val['distance'],2) : "";         
				$return .= "\t<SPAN class='row' STYLE='background-color:{$val['bg_color']}; color:{$val['text_color']};'>
					<SPAN class='cell text'><INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$val['contact_via']}:{$val['responder_id']}:{$val['smsg_id']}:' {$checked}></SPAN>
					<SPAN class='cell text'>" . $dist . "</SPAN>
					<SPAN class='cell text' TITLE='{$val['status']}'>{$val['handle']}</SPAN>
					<SPAN class='cell text' TITLE='{$val['fullnames']}'>{$val['name']}</SPAN>
					{$e_add} 
					{$smsg}
					{$cell}
					</SPAN>\n";
				$i++;
				}		// end foreach()
		$return .= "<BR />";
		$return .= "</DIV>";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "</TABLE>";
		$return .= "</TD>";
		$return .= "<TD style='vertical-align: top;'>";
		$return .= "<TABLE BORDER=0>";
		$return .= "<TR VALIGN='top' CLASS='even'><TD CLASS='td_label' ALIGN='right'>Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 55 VALUE='" . $default_msg . "'></TD></TR>";
		$return .= "<TR VALIGN='top' CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=45 ROWS=4 wrap='soft'></TEXTAREA><BR /><SPAN CLASS='warn'>" . get_text("messaging help") . "</SPAN></TD></TR>";

		$return .= "<TR VALIGN = 'TOP' CLASS='even'>";
		$return .= "<TD ALIGN='right' CLASS='td_label'>Signal: </TD><TD>";

		$return .= "<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
		$return .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			$return .= "\t<OPTION VALUE='" . $row['code'] . "'>" . $row['code'] . "|" . $row['text'] . "</OPTION>\n";
			}
		$return .= "</SELECT>";
		$return .= "<BR />";
		$return .= "<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>";
		$return .= "<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'></SPAN>";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "<TR VALIGN = 'TOP' CLASS='even'>";
		$return .= "<TD ALIGN='right' CLASS='td_label'>Standard Message: </TD><TD>";
		$return .= "<SELECT NAME='std_msgs' onChange = 'set_message(this.options[this.selectedIndex].value, document.contact_form);'>";
		$return .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
		$return .= get_standard_messages_sel();
		$return .= "</SELECT>";
		$return .= "<BR />";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "<TR VALIGN='top' CLASS='odd'>";
		$return .= "<TD ALIGN='center' COLSPAN=2><BR /><BR />";
		$return .= "<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_sendform(document.contact_form);'><SPAN STYLE='float: left;'>" . get_text('Next') ."</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
		$return .= "<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.contact_form.reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
		$return .= "</TD>";
		$return .= "</TR>";
		if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
			$return .= "<TR><TD>&nbsp;</TD></TR>	";			
			$return .= "<TR>";
			$return .= "<TD ALIGN='left' COLSPAN=2>";
			$return .= "<input type='radio' name='use_smsg' VALUE='0' checked> Use Email<br>";
			$return .= "<input type='radio' name='use_smsg' VALUE='1'> Use " . return_provider_name(get_msg_variable('smsg_provider')) . "?<br>";
			$return .= "</TD>";
			$return .= "</TR>";
			} else {
			$return .= "<INPUT TYPE='hidden' NAME='use_smsg' VALUE='0' />";
			}
		$return .= "<INPUT type='hidden' NAME='frm_resp_ids' VALUE='' />";
		$return .= "<INPUT type='hidden' NAME='frm_smsg_ids' VALUE='' />";
		$return .= "<INPUT type='hidden' NAME='frm_ticket_id' VALUE='" . $tik_id . "' />";					
		$return .= "</TABLE></TD></TR></TABLE></FORM>";
		} else {
		$return .= "<TR style-'width: 100%;'><TD COLSPAN=99>No assigned reponders</TD></TR></TABLE>";
		}
	break;
	
	case 'selected':
		$tik_id = $_GET['ticket_id'];
		$default_msg = "Ticket ID *" . $tik_id . "*";

		$query_t = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $tik_id;
		$result_t = mysql_query($query_t);		
		$row_t = stripslashes_deep(mysql_fetch_assoc($result_t), MYSQL_ASSOC);
		$t_lat = $row_t['lat'];
		$t_lng = $row_t['lng'];
		
		$query_ass = "SELECT *, 
			`r`.`id` AS `responder_id`
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	 `s` ON (`r`.`un_status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)
			WHERE `t`.`id` = " . $tik_id . " AND (LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')) AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
			ORDER BY `r`.`id` ASC ";
		$result_ass = mysql_query($query_ass) or do_error($query_ass, 'mysql query failed', mysql_error(), __FILE__, __LINE__);				
		while($row_ass = stripslashes_deep(mysql_fetch_assoc($result_ass), MYSQL_ASSOC)){
			$assigned_resp[] = $row_ass['responder_id'];
			}
		$query = "SELECT *,	`r`.`id` AS `responder_id`,
			`r`.`lat` AS `r_lat`,
			`r`.`lng` AS `r_lng`				
			FROM `$GLOBALS[mysql_prefix]responder` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	`s` ON (`r`.`un_status_id` = `s`.`id`)
			WHERE LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')
			ORDER BY `name` ASC ";
		$result = mysql_query($query);
	
		$lines = mysql_affected_rows() +8;
		$no_rows = mysql_num_rows($result);
		$return = "<FORM ID='contact_form' NAME='contact_form' METHOD='post' ACTION='./ajax/form_post.php?q=". $sess_id . "&function=contact'>";
		$return .= "<INPUT TYPE='hidden' NAME='frm_step' VALUE='3' />\n";
		$return .= "<INPUT TYPE='hidden' NAME='frm_add_str' VALUE='' />\n";
		$return .= "<TABLE ALIGN = 'center' border=0>";
		$return .= "<TR>";
		$return .= "<TD COLSPAN=99 ALIGN='center'>&nbsp;</TD>";
		$return .= "</TR>";
		if($no_rows>0) {
			$return .= "<TR>";
			$return .= "<TD COLSPAN=99 ALIGN='center'>";
			$return .= "<SPAN id='clr_spn' CLASS='plain text' style='width: 100px; display: none; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_clear();'><SPAN STYLE='float: left;'>" . get_text('Uncheck All') . "</SPAN><IMG STYLE='float: right;' SRC='./images/unselect_all_small.png' BORDER=0></SPAN>";
			$return .= "<SPAN id='chk_spn' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_check();'><SPAN STYLE='float: left;'>" . get_text('Check All') . "</SPAN><IMG STYLE='float: right;' SRC='./images/select_all_small.png' BORDER=0></SPAN>";
			$return .= "</TD>";
			$return .= "</TR>";
			$return .= "<TR>";
			$return .= "<TD COLSPAN=99>&nbsp;</TD>";
			$return .= "</TR>";
			$the_arr = array();
			$n=1;
			while($row = mysql_fetch_assoc($result)) {
				// SMS Gateway IDs
				$smsg_arr = array();
				$temp_arr = array();
				$temp_smsg = get_smsgid($row['responder_id']);
				if(!is_array($temp_smsg)) {
					$temp_smsg = array();
					$temp_smsg[] = get_smsgid($row['responder_id']);
					}
				$smsg_length = count($temp_smsg);
				foreach($temp_smsg as $val) {
					array_push($temp_arr, $val);
					}
				$smsg_arr = array_unique($temp_arr);
				$smsg_addr = implode(",", $smsg_arr);
				if($smsg_length > 1) {
					$the_arr[$n]['smsg_id'] = "...Multiple...";
					} else {
					$the_arr[$n]['smsg_id'] = $smsg_addr;
					}
				// Cellphone Numbers
				$cell_arr = array();
				$temp_arr = array();
				$temp_cell = get_mdb_cell($row['responder_id']);
				if(!is_array($temp_cell)) {
					$temp_cell = array();
					$temp_cell[] = get_mdb_cell($row['responder_id']);
					}				
				$cell_length = count($temp_cell);
				foreach($temp_cell as $val) {
					array_push($temp_arr, $val);
					}
				$cell_arr = array_unique($temp_arr);
				$theCells = implode(",", $cell_arr);
				if($cell_length > 1) {
					$the_arr[$n]['cellphone'] = "...Multiple...";
					} else {
					$the_arr[$n]['cellphone'] = $theCells;
					}
				//	Name(s)
				$name_arr = array();
				$temp_arr = array();
				$temp_names = get_mdb_names($row['responder_id']);
				if(!is_array($temp_names)) {
					$temp_names = array();
					$temp_names[] = get_mdb_names($row['responder_id']);
					}	
				$names_length = count($temp_names);
				foreach($temp_names as $val) {
					array_push($temp_arr, $val);
					}
				$name_arr = array_unique($temp_arr);
				$theNames = implode(",", $name_arr);	
				if($names_length > 1) {
					$the_arr[$n]['name'] = "...Multiple...";
					} else {
					$the_arr[$n]['name'] = $theNames;
					}
				$the_arr[$n]['handle'] = $row['handle'];	
				$the_arr[$n]['responder_id'] = $row['responder_id'];
				//	Email addresses (contact via)
				$em_arr = array();
				$temp_arr = array();
				$temp_addrs = get_contact_via($row['responder_id']);
				if(!is_array($temp_addrs)) {
					$temp_addrs = array();
					$temp_addrs[] = get_contact_via($row['responder_id']);
					}
				$addrs_length = count($temp_addrs);
				foreach($temp_addrs as $val) {
					if (is_email($val)) {
						array_push($temp_arr, $val);
						}
					}
				$em_arr = array_unique($temp_arr);
				$em_addr = implode("|", $em_arr);
				if($addrs_length > 1) {
					$the_arr[$n]['contact_via'] = "...Multiple...";
					} else {
					$the_arr[$n]['contact_via'] = $em_addr;
					}
				$the_arr[$n]['bg_color'] = $row['bg_color'];		
				$the_arr[$n]['text_color'] = $row['text_color'];
				$the_arr[$n]['distance'] = (isset($t_lat) && isset($t_lng)) ? distance($row['r_lat'], $row['r_lng'], $t_lat, $t_lng, "N") : 0;	//	populate array entry with distance from responder to ticket
				$the_arr[$n]['status'] = $row['status_val'];
				$the_arr[$n]['fullnames'] = $theNames;
				$the_arr[$n]['fullcontactvia'] = $em_addr;
				$the_arr[$n]['fullsmsgids'] = $smsg_addr;
				$the_arr[$n]['fullcells'] = $theCells;
				$n++;
				}	//	End While
			if((isset($_GET['the_ticket'])) && ($_GET['the_ticket'] != 0)) {
				$the_arr = subval_sort($the_arr,'distance'); 	//	sort array by distance ascending but only if the mail form is called from a Ticket
				}

			$i=1;
			$return .= "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_units_legend() . "</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_unit_status_legend() . "</TD></TR>\n";
			$return .= "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>\n";
			$return .= "<TR><TD>\n";
			$return .= "<TABLE ALIGN = 'center' BORDER=0><TR><TD><SPAN class='text text_center' style='width: 100%; display: block;'>Check checkbox to Select Unit</SPAN></TD></TR><TR><TD>\n";
			$return .= "<DIV class='container' style='display: block; width: 100%; min-height: 200PX; max-height: 400px; overflow-y: scroll; overflow-x: none;'>\n";
			foreach($the_arr as $val) {
				if(!empty($assigned_resp)) {
					$checked = in_array($val['responder_id'],$assigned_resp) ? "checked" : "";
					} else {
					$checked = "";
					}
				$smsg = $cell = "";
				$e_add = (($val['contact_via'] == NULL) || ($val['contact_via'] == "")) ? "<SPAN TITLE='No email address stored' class='cell text' style='color: LightGrey;'>(E) NONE</SPAN>" : "<SPAN TITLE='{$val['fullcontactvia']}' class='cell text'>(E) " . $val['contact_via'] . "</SPAN>\n" ;
				if($using_smsg && $smsg_provider == "SMS Responder") {
					$smsg = (($val['smsg_id'] == NULL) || ($val['smsg_id'] == "")) ? "<SPAN TITLE='SMS Gateway ID stored' class='cell text' style='color: LightGrey; display:'>(SMSG) NONE</SPAN>" : "<SPAN TITLE='{$val['fullsmsgids']}' class='cell text'>(SMSG) " . $val['smsg_id'] . "</SPAN>\n" ;
					}
				if($using_smsg && $smsg_provider == "Txt Local") {				
					$cell = (($val['cellphone'] == NULL) || ($val['cellphone'] == "")) ? "<SPAN TITLE='No Cellphone number stored' class='cell text' style='color: LightGrey;'>(CELL) NONE</SPAN>" : "<SPAN TITLE='{$val['fullcells']}' class='cell text'>(CELL) " . $val['cellphone'] . "</SPAN>\n" ;
					}
				$dist = (round($val['distance'],1) != 0) ? "Dist: " . round($val['distance'],2) : "";         
				$return .= "\t<SPAN class='row' STYLE='background-color:{$val['bg_color']}; color:{$val['text_color']};'>
					<SPAN class='cell text'><INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$val['contact_via']}:{$val['responder_id']}:{$val['smsg_id']}:' {$checked}></SPAN>
					<SPAN class='cell text'>" . $dist . "</SPAN>
					<SPAN class='cell text' TITLE='{$val['status']}'>{$val['handle']}</SPAN>
					<SPAN class='cell text' TITLE='{$val['fullnames']}'>{$val['name']}</SPAN>
					{$e_add} 
					{$smsg}
					{$cell}
					</SPAN>\n";
				$i++;
				}		// end foreach()
		$return .= "<BR />";
		$return .= "</DIV>";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "</TABLE>";
		$return .= "</TD>";
		$return .= "<TD style='vertical-align: top;'>";
		$return .= "<TABLE BORDER=0>";
		$return .= "<TR VALIGN='top' CLASS='even'><TD CLASS='td_label' ALIGN='right'>Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 55 VALUE='" . $default_msg . "'></TD></TR>";
		$return .= "<TR VALIGN='top' CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=45 ROWS=4 wrap='soft'></TEXTAREA><BR /><SPAN CLASS='warn'>" . get_text("messaging help") . "</SPAN></TD></TR>";

		$return .= "<TR VALIGN = 'TOP' CLASS='even'>";
		$return .= "<TD ALIGN='right' CLASS='td_label'>Signal: </TD><TD>";

		$return .= "<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
		$return .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			$return .= "\t<OPTION VALUE='" . $row['code'] . "'>" . $row['code'] . "|" . $row['text'] . "</OPTION>\n";
			}
		$return .= "</SELECT>";
		$return .= "<BR />";
		$return .= "<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>";
		$return .= "<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'></SPAN>";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "<TR VALIGN = 'TOP' CLASS='even'>";
		$return .= "<TD ALIGN='right' CLASS='td_label'>Standard Message: </TD><TD>";
		$return .= "<SELECT NAME='std_msgs' onChange = 'set_message(this.options[this.selectedIndex].value, document.contact_form);'>";
		$return .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
		$return .= get_standard_messages_sel();
		$return .= "</SELECT>";
		$return .= "<BR />";
		$return .= "</TD>";
		$return .= "</TR>";
		$return .= "<TR VALIGN='top' CLASS='odd'>";
		$return .= "<TD ALIGN='center' COLSPAN=2><BR /><BR />";
		$return .= "<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_sendform(document.contact_form);'><SPAN STYLE='float: left;'>" . get_text('Next') ."</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
		$return .= "<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.contact_form.reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
		$return .= "</TD>";
		$return .= "</TR>";
		if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
			$return .= "<TR><TD>&nbsp;</TD></TR>	";			
			$return .= "<TR>";
			$return .= "<TD ALIGN='left' COLSPAN=2>";
			$return .= "<input type='radio' name='use_smsg' VALUE='0' checked> Use Email<br>";
			$return .= "<input type='radio' name='use_smsg' VALUE='1'> Use " . return_provider_name(get_msg_variable('smsg_provider')) . "?<br>";
			$return .= "</TD>";
			$return .= "</TR>";
			} else {
			$return .= "<INPUT TYPE='hidden' NAME='use_smsg' VALUE='0' />";
			}
		$return .= "<INPUT type='hidden' NAME='frm_resp_ids' VALUE='' />";
		$return .= "<INPUT type='hidden' NAME='frm_smsg_ids' VALUE='' />";
		$return .= "<INPUT type='hidden' NAME='frm_ticket_id' VALUE='" . $tik_id . "' />";					
		$return .= "</TABLE></TD></TR></TABLE></FORM>";
		} else {
		$return .= "<TR style-'width: 100%;'><TD COLSPAN=99>No assigned reponders</TD></TR></TABLE>";
		}
	break;
	}
$ret_arr = array();
$ret_arr[] = $return;
print json_encode($ret_arr);