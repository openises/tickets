<?php
error_reporting(E_ALL);
session_start();
session_write_close();	
require_once('../incs/functions.inc.php');
$incident = get_text('Incident');
$evenodd = array ("even", "odd", "heading");	// class names for alternating table row css colors

function get_updated($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `$GLOBALS[mysql_prefix]ticket`.`id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return strtotime($row['updated']);
	}

function log_details($theid, $show_cfs=FALSE) {								// 11/20/09, 10/20/12, 5/8/14
	global $evenodd, $incident ;	// class names for alternating table row colors
	require('../incs/log_codes.inc.php'); 									// 9/29/10
	$query = "
		SELECT `$GLOBALS[mysql_prefix]log`.`id` AS `log_id`,
		`$GLOBALS[mysql_prefix]log`.`who` AS `who`,
		`$GLOBALS[mysql_prefix]log`.`code` AS `code`,		
		`$GLOBALS[mysql_prefix]log`.`when` AS `when`,
		`$GLOBALS[mysql_prefix]log`.`ticket_id` AS `ticket_id`,		
		`$GLOBALS[mysql_prefix]log`.`responder_id` AS `responder_id`,
		`$GLOBALS[mysql_prefix]log`.`info` AS `info`,
		`$GLOBALS[mysql_prefix]log`.`from` AS `from`,
		`t`.`scope` AS `tickname`,
		`r`.`handle` AS `unitname`,
		`s`.`status_val` AS `theinfo`,
		`u`.`user` AS `thename` 
		FROM `$GLOBALS[mysql_prefix]log`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t 		ON (`$GLOBALS[mysql_prefix]log`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` r 	ON (`$GLOBALS[mysql_prefix]log`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s 	ON (`$GLOBALS[mysql_prefix]log`.`code` = `s`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` u 		ON (`$GLOBALS[mysql_prefix]log`.`who` = `u`.`id`)
		WHERE `$GLOBALS[mysql_prefix]log`.`ticket_id` = " . $theid . " ORDER BY `when` ASC";								// 10/2/12
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$i = 0;
	$print = "<TABLE ALIGN='left' CELLSPACING = 1 WIDTH='100%'>";

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$code = $row['code'];
		if ($i==0) {				// 11/20/09
			$print .= "<TR CLASS='heading'><TD CLASS='heading' TITLE = \"{$row['tickname']}\" COLSPAN=99 ALIGN='center'><U>Log: <I>". shorten($row['tickname'], 32) . "</I></U></TD></TR>";
			$cfs_head = ($show_cfs)? "<TD ALIGN='center'>CFS</TD>" : ""  ;
			$print .= "<TR CLASS='odd'><TD ALIGN='left'>Code</TD>" . $cfs_head . "<TD ALIGN='left'>Unit</TD><TD ALIGN='left'>Status</TD><TD ALIGN='left'>When</TD><TD ALIGN='left'>By</TD><TD ALIGN='left'>From</TD></TR>";
			}
	
		$print .= "<TR CLASS='" . $evenodd[$i%2] . "'>" .				// 11/20/09
			"<TD TITLE =\"{$types[$row['code']]}\">". shorten($types[$row['code']], 20) . "</TD>"; // 
		if ($show_cfs) {
			$print .= "<TD TITLE =\"{$row['tickname']}\">". shorten($row['tickname'], 16) . "</TD>";	// 2009-11-07 22:37:41 - substr($row['when'], 11, 5)
			}
		$print .= "<TD TITLE =\"{$row['unitname']}\">". 	shorten($row['unitname'], 16) . "</TD>";
		if($code == 20) {
			$print .= "<TD TITLE =\"{$row['theinfo']}\">". 	shorten(get_un_status_name($row['info']), 16) . "</TD>";
			} else {
			$print .= "<TD>&nbsp;</TD>";
			}
		$print .= "<TD TITLE =\"" . format_date_2(strtotime($row['when'])) . "\">". format_sb_date_2($row['when']) . "</TD>";
		$print .= "<TD TITLE =\"{$row['thename']}\">". 	shorten($row['thename'], 8) . "</TD>";
		$print .= "<TD TITLE =\"{$row['from']}\">". 		substr($row['from'], -4) . "</TD>";
			"</TR>";
			$i++;
		}
	$print .= "</TABLE>";
	return $print;
	}		// end function get_log ()

function ticket_details($id, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$id})</I>" : "";			// 1/25/09, 2/18/12
	$istest = FALSE;
	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
										// 1/7/10
	$query = "SELECT *,
		`problemstart` AS `my_start`,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		`date` AS `date`,
		`booked_date` AS `booked_date`,		
		`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]ticket`.`owner` AS `owner`,
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`street` AS `rec_fac_street`,
		`rf`.`city` AS `rec_fac_city`,
		`rf`.`state` AS `rec_fac_state`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09


	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";	// 8/18/09
		exit();
		}

	$theRow = stripslashes_deep(mysql_fetch_array($result));
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("About Address") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['address_about']) . "</TD></TR>\n";	//	9/10/13
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("To Address") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['to_address']) . "</TD></TR>\n";	//	9/10/13
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("City") . ":</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['tick_descr']))) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['nine_one_one'])) . "</TD></TR>\n";	//	6/26/10
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>	<TD ALIGN='left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>			<TD ALIGN='left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$elapsed = get_elapsed_time ($theRow);			
	$elapsed_str = get_elapsed_time ($theRow);			
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Status") . ":</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$owner_str = ($theRow['owner'] == 0) ? "&nbsp;&nbsp;by unk(" . $theRow['owner'] . ")" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$by_str = ($theRow['call_taker'] == 0) ? "&nbsp;&nbsp;by unk(" . $theRow['call_taker'] . ")" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$print .= "<TR CLASS='even'><TD ALIGN='left'>" . get_text("Written") . ":</TD>		<TD ALIGN='left'>" . format_date_2($theRow['date']) . $owner_str;
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2($theRow['updated']) . $by_str . "</TD></TR>\n";
	$print .=  empty($theRow['booked_date']) ? "" : "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date_2($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09
	$print .= "<TR CLASS='even' ><TD ALIGN='left' COLSPAN='2'>&nbsp;	<TD ALIGN='left'></TR>\n";			// separator
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD ALIGN='left'>{$incident} at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$rec_fac_details = empty($theRow['rec_fac_name'])? "" : $theRow['rec_fac_name'] . "<BR />" . $theRow['rec_fac_street'] . "<BR />" . $theRow['rec_fac_city'] . "<BR />" . $theRow['rec_fac_state'];
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $rec_fac_details) . "</TD></TR>\n";	// 10/6/09
	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['comments']))) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Run Start") . ":</TD> <TD ALIGN='left'>" . format_date_2($theRow['problemstart']);
	$end_str = (good_date_time($theRow['problemend']))? format_date_2(strtotime($theRow['problemend'])) : "";
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;{$end_str}&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD ALIGN='left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>" . get_text("Position") . "</U>: </TD>
		<TD ALIGN='left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08

	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= log_details ($theRow[0]);				// log
	$print .="</TD></TR>";
	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
											// 3/30/2013
	$print .= "<TR><TD COLSPAN=99>";
	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09 - new_show_assigns($id_in)
	$print .= "</TD></TR><TR><TD COLSPAN=99>";
	$print .= show_actions($theRow[0], "date", TRUE, TRUE, 2);
	$print .= "</TD></TR>";	
	$print .= "</TABLE>\n";	
	return $print;
	}		// end function do ticket()
	
$the_ticket_id = intval($_GET['ticket_id']);
$time_now = mysql_format_date(now());
$ret_arr = array();
if (intval($the_ticket_id) > 0) {
	$ret_arr[0] = ticket_details($the_ticket_id, "100%", NULL, NULL);
	$ret_arr[1] = get_updated($the_ticket_id);
	}

print json_encode($ret_arr);
exit();
?>