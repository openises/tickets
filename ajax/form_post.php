<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$func = $_GET['function'];
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
switch($func) {
	case "contact":
	$smsg_ids = ((isset($_POST['use_smsg'])) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";
	$address_str = $_POST['frm_add_str'];
	$resp_ids = ((isset($_POST['frm_resp_ods'])) && ($_POST['frm_resp_ids'] != "") && ($_POST['frm_resp_ids'] != 0)) ? $_POST['frm_resp_ids'] : 0;
	$count = 0;
	$tik_id = ((isset($_POST['frm_ticket_id'])) && ($_POST['frm_ticket_id'] != 0)) ? $_POST['frm_ticket_id'] : 0;
	$count = do_send ($address_str, $smsg_ids, $_POST['frm_subj'], $_POST['frm_text'], $tik_id, $_POST['frm_resp_ids']);
	print "Messages sent: {$count}";
	break;
	
	case "note":
	$field_name = array('description', 'comments');
	$frm_ticket_id=(int)$_POST['frm_ticket_id'];	//	4/4/14
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$frm_ticket_id} LIMIT 1";	//	4/4/14
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$now = (time() - (get_variable('delta_mins')*60)); 
	$format = get_variable('date_format');
	$the_date = date($format, $now);
	$the_in_str = ($_POST['frm_add_to']=="0")? $row['description'] : $row['comments'] ;
	$the_text = "{$the_in_str} [{$_SESSION['user']}:{$the_date}]" . strip_tags(trim($_POST['frm_text'])) . "\n";		// 1/7/2013
	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `{$field_name[$_POST['frm_add_to']]}`= " . quote_smart($the_text) . " WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if($result) {print "Note added to Ticket " . $frm_ticket_id . "<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;

	case "addaction":
	$responder = $sep = "";
	foreach ($_POST as $VarName=>$VarValue) {
		$temp = explode("_", $VarName);
		if (substr($VarName, 0, 7)=="frm_cb_") {
			$responder .= $sep . $VarValue;
			$sep = " ";
			}
		}
	$_POST['frm_description'] = strip_html($_POST['frm_description']);

	$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST['frm_meridiem_asof'] : "" ;

	$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
																// 4/22/11
	$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE
		`description` = '" . addslashes($_POST['frm_description']) . "' AND
		`ticket_id` = '{$_POST['frm_ticket_id']}' AND
		`user` = '{$_SESSION['user_id']}' AND
		`action_type` = '{$GLOBALS['ACTION_COMMENT']}' AND
		`updated` = '{$frm_asof}' AND
		`responder` = '{$responder}' ";
		
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename(__FILE__), __LINE__);
	if (mysql_affected_rows()==0) {		// not a duplicate - 8/15/10
		
		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]action` 
			(`description`,`ticket_id`,`date`,`user`,`action_type`, `updated`, `responder`) VALUES
			('" . addslashes($_POST['frm_description']) . "', '{$_POST['frm_ticket_id']}', '{$now}', {$_SESSION['user_id']}, {$GLOBALS['ACTION_COMMENT']}, '{$frm_asof}', '{$responder}')";		// 8/24/08
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename(__FILE__), __LINE__);
		if($result) {print "Action added to Ticket " . $_POST['frm_ticket_id'] . "<BR />";} else {print "Something went wrong, please try again<BR />";}
		$ticket_id = mysql_insert_id();								// just inserted action id
		do_log($GLOBALS['LOG_ACTION_ADD'], $_POST['frm_ticket_id'], 0,  mysql_insert_id());		// 3/18/10
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE `id`='" . $_POST['frm_ticket_id'] . "' LIMIT 1";
		$result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		}
		
	$id = $_POST['frm_ticket_id'];
	$addrs = notify_user($_POST['frm_ticket_id'],$GLOBALS['NOTIFY_ACTION_CHG']);
	if ($addrs) {
		$theTo = implode("|", array_unique($addrs));
		$theText = "TICKET - ACTION: ";
		mail_it ($theTo, "", $theText, $id, 1 );
		}				// end if/else ($addrs)
	break;
	
	case "editaction":
	$responder = $sep = "";
	foreach ($_POST as $VarName=>$VarValue) {			// 3/20/10
		$temp = explode("_", $VarName);
		if (substr($VarName, 0, 7)=="frm_cb_") {
			$responder .= $sep . $VarValue;		// space separator for multiple responders
			$sep = " ";
			}
		}
	$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;
	$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
	$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]action` SET `description`='$_POST[frm_description]', `responder` = '$responder', `updated` = '$frm_asof' WHERE `id`='$_GET[id]' LIMIT 1") or do_error('action.php::update action','mysql_query',mysql_error(),basename( __FILE__), __LINE__);
	$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` =	'$frm_asof' WHERE id='$_GET[ticket_id]' LIMIT 1") 	or do_error('action.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
	$result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") 			or do_error('action.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_array($result));
	$id = $_GET['ticket_id'];
	print '<SPAN CLASS="header text" style="width: 100%; display: block; text-align: center;">Action record has been updated.</SPAN><BR /><BR /><BR />';
	print "<DIV STYLE='width: 100%; display: block; text-align: center;'>";
	print "<A ID='main_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' HREF='main.php'>" . get_text('Main') . "</A>";
	print "<A ID='inc_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' HREF='main.php?id={$id}'>" . get_text('Incident') . "</A><BR />";
	print "</DIV>";
	$addrs = notify_user($_GET['ticket_id'],$GLOBALS['NOTIFY_ACTION_CHG']);		// returns array or FALSE
	if ($addrs) {
		$theTo = implode("|", array_unique($addrs));
		$theText = "TICKET - ACTION UPDATED: ";
		$theCount = mail_it ($theTo, "", $theText, $id, 1 );
		if($theCount > 0) {print $theCount . " Notifications sent<BR />";}
		}
	break;
	
	case "addpatient":
	$_POST['frm_description'] = strip_html($_POST['frm_description']);

	$post_frm_meridiem_asof = empty($_POST['frm_meridiem_asof'])? "" : $_POST['frm_meridiem_asof'] ;
	$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$post_frm_meridiem_asof";
													//  8/15/10	
	$query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]patient` WHERE 
		`description` =	'" . addslashes($_POST['frm_description']) . "' AND
		`ticket_id` =	'{$_REQUEST['ticket_id']}' AND
		`user` =		'{$_SESSION['user_id']}' AND
		`action_type` =	'{$GLOBALS['ACTION_COMMENT']}' AND 
		`name` = 		'" . addslashes($_POST['frm_name']) . "' AND 
		`updated` =		'{$frm_asof}' LIMIT 1";
		
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {		// not a duplicate - 8/15/10	

		if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
			$ins_data = "
				`fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
				`dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
				`gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
				`insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",
				`facility_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",						
				`facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",";
			}
		else { $ins_data = "";}
			
		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]patient` SET 
			{$ins_data}
			`description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
			`ticket_id`= " .  	quote_smart(addslashes(trim($_REQUEST['ticket_id']))) .	",
			`date`= " .  		quote_smart(addslashes(trim($now))) . ",
			`user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
			`action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
			`name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ", 
			`updated` = " .  	quote_smart(addslashes(trim($frm_asof)));

		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		do_log($GLOBALS['LOG_PATIENT_ADD'], $_REQUEST['ticket_id'], 0, mysql_insert_id());		// 3/18/10
		$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'  LIMIT 1") or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
		}

	$id = $_REQUEST['ticket_id'];			
	$addrs = notify_user($_REQUEST['ticket_id'],$GLOBALS['NOTIFY_PERSON_CHG']);		// returns array or FALSE
	if ($addrs) {
		$theTo = implode("|", array_unique($addrs));
		$subject = "TICKET - PATIENT ADDED: ";
		$theMessage= mail_it ($theTo, "", $subject, $id, 1, TRUE );
		$theCount = do_send ($theTo, "", $subject, $theMessage, $id, 0, NULL, NULL);
		if($theCount > 0) {print $theCount . " Notifications sent<BR />";}
		}				// end if ($addrs)
	if($result) {print "Patient added to Ticket " . $id . "<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;

	case "editpatient":
	$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

	$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
	$now = mysql_format_date(now());
	if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
		$ins_data = "
			`fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
			`dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
			`gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
			`insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",";

		} else { 
		$ins_data = "";
		}
	$query 	= "UPDATE `$GLOBALS[mysql_prefix]patient` SET 
		{$ins_data}
		`description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
		`ticket_id`= " .  	quote_smart(addslashes(trim($_REQUEST['ticket_id']))) .	",
		`date`= " .  		quote_smart(addslashes(trim($frm_asof))) . ",
		`user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
		`action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
		`name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ",
		`facility_id` =" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",
		`facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",			
		`updated` = " .  	quote_smart(addslashes(trim($now))) . "
		WHERE id= " . 		quote_smart($_GET['id']) . " LIMIT 1";

	$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);

	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_REQUEST[ticket_id]'";
	$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);

	$result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]'") or do_error('patient.php::update patient record','mysql_query',mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));

	if($_POST['assigns'] != "0") {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id` = " . $_GET['id'];
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if(mysql_num_rows($result) > 0) {
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id`= " . $_GET['id'];
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			}			
		
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]patient_x` (
				`patient_id`, `assign_id`, `_by`, `_on`, `_from`
				) VALUES (" .
				quote_smart(trim($_GET['id'])) . "," .
				quote_smart(trim($_POST['assigns'])) . "," .
				quote_smart(trim($_SESSION['user_id'])) . "," .
				quote_smart(trim($now)) . "," .
				quote_smart(trim($_SERVER['REMOTE_ADDR'])) . ");";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id` = " . $_GET['id'];
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if(mysql_num_rows($result) > 0) {
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id`= " . $_GET['id'];
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			}					
		}
	
	$id = $_REQUEST['ticket_id'];
	$addrs = notify_user($_REQUEST['ticket_id'],$GLOBALS['NOTIFY_ACTION_CHG']);
	if ($addrs) {
		$theTo = implode("|", array_unique($addrs));
		$subject = "TICKET - ACTION: ";
		$theMessage= mail_it ($theTo, "", $subject, $id, 1, TRUE );
		$theCount = do_send ($theTo, "", $subject, $theMessage, $id, 0, NULL, NULL);
		if($theCount > 0) {print $theCount . " Notifications sent<BR />";}
		}				// end if ($addrs)
	if($result) {print "Patient record changed for Ticket " . $id . "<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;
	
	case "dispatch":
	extract($_REQUEST);
 	$the_ticket_id = (integer) $_REQUEST["frm_ticket_id"];
	$addrs = array();
	$smsgaddrs = array();
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
	$assigns = explode ("|", $_REQUEST['frm_id_str']);
	$ok = 0;
	for ($i=0;$i<count($assigns); $i++) {
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`, `facility_id`, `rec_facility_id`)
						VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($frm_ticket_id),
							quote_smart($assigns[$i]),
							quote_smart($frm_comments),
							quote_smart($frm_by_id),
							quote_smart($now),
							quote_smart($frm_facility_id),
							quote_smart($frm_rec_facility_id));
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

//										remove placeholder inserted by 'add'		
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . quote_smart($frm_ticket_id) . " AND `responder_id` = 0 LIMIT 1";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		
							//	Automatic Status Update by Dispatch Status
		$use_status_update = get_variable('use_disp_autostat');		//	9/10/13
		if($assigns[$i] != 0 && $assigns[$i] != "") {
			if($use_status_update == "1") {		//	9/10/13
				auto_disp_status(1, $assigns[$i]);
				}
			
								// apply status update to unit status

			$query = "SELECT `id`, `contact_via`, `smsg_id` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . quote_smart($assigns[$i])  ." LIMIT 1";		// 10/7/08
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$row_addr = stripslashes_deep(mysql_fetch_assoc($result));
			if (is_email($row_addr['contact_via'])) {array_push($addrs, $row_addr['contact_via']); }		// to array for emailing to unit
			if ($row_addr['smsg_id'] != "") {array_push($smsgaddrs, $row_addr['smsg_id']); }		// to array for sending message via SMS Gateway to unit	//	10/23/12
			do_log($GLOBALS['LOG_CALL_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);		// 3/13/12
			if ($frm_facility_id != 0) {
				do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
				}
			if ($frm_rec_facility_id != 0) {
				do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
				}
			}
		}
	$addr_str = urlencode( implode("|", array_unique($addrs)));
	$smsg_add_str = urlencode( implode(",", array_unique($smsgaddrs)));
	$ret_arr = array();
	$ret_arr[0] = "Assignments made for Ticket " . $frm_ticket_id . "<BR />";
	$ret_arr[1] = $addr_str;
	$ret_arr[2] = $smsg_add_str;
	$ret_arr[3] = $frm_ticket_id;
	print json_encode($ret_arr);
	break;
	
	case "dispatchmail":
	$the_responders = array();
	$the_emails = explode('|',$_POST['frm_addrs']);
	$the_sms = ((isset($_POST['frm_smsgaddrs'])) && ($_POST['frm_smsgaddrs'] != "")) ? explode(',', $_POST['frm_smsgaddrs']) : "";
	$email_addresses = ($_POST['frm_addrs'] != "") ? $_POST['frm_addrs'] : "";
	$smsg_addresses = ((isset($_POST['frm_use_smsg'])) && ($_POST['frm_use_smsg'] == 1) && ($_POST['frm_smsgaddrs'] != "")) ? $_POST['frm_smsgaddrs'] : "";
	foreach($the_emails as $val) {
		$the_responders[] = get_resp_id2($val);
		}
	if(($_POST['frm_use_smsg']) && ($_POST['frm_use_smsg'] == 1)) {
		foreach($the_sms as $val2) {
			$the_responders[] = get_resp_id($val2);	
			}
		}
	$the_resp_ids = array_unique($the_responders);
	$resps = substr(implode(',', $the_resp_ids), 0 -2);
	$count = do_send ($email_addresses, $smsg_addresses, "Tickets CAD",  $_POST['frm_text'], $_POST['ticket_id'], $resps );		// - ($to_str, $to_smsr, $subject_str, $text_str, %ticket_id, $responder_id ) 
	if($count > 0) {print $count . " Messages sent<BR />";}
	break;
	
	default: 	
	return 'error';
	}

	
	
exit();