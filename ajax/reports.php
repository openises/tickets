<?php
@session_start();
session_write_close();
require_once('../incs/functions.inc.php');		//7/28/10
require_once('../incs/log_codes.inc.php'); 				// 3/25/10
extract($_GET);
$theWidth = "100%";
$doprint = (array_key_exists('do_print', $_GET) && $_GET['do_print'] == 1) ? true : false;
$dohtml = (array_key_exists('dohtml', $_GET) && $_GET['dohtml'] == true) ? true : false;
$doDownload = $dohtml;
$theStartDate = (array_key_exists('startdate', $_GET)) ? explode(",", $startdate) : "";
$theEndDate = (array_key_exists('enddate', $_GET)) ? explode(",", $enddate) : "";
$date = (array_key_exists('date', $_GET)) ? $date : "";
$htmlheader = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<head>
				<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
				<meta name=ProgId content=Word.Document>
				<meta name=Generator content="Microsoft Word 9">
				<meta name=Originator content="Microsoft Word 9">
				<title>My Title Here</title>
				<style>
				@page Section1 {size:595.45pt 841.7pt; margin:1.0in 1.25in 1.0in 1.25in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
				div.Section1 {page:Section1;}
				@page Section2 {size:841.7pt 595.45pt;mso-page-orientation:landscape;margin:1.25in 1.0in 1.25in 1.0in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
				div.Section2 {page:Section2;}
				</style>
				</head>
				<body>
				<div class=Section2>';
$htmlfooter = "</DIV></BODY></HTML>";

	function get_responder_regions($id) {
		$query = "SELECT * 
		FROM `$GLOBALS[mysql_prefix]allocates` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]region` `r` ON `a`.`group` = `r`.`id` 
		WHERE `a`.`resource_id` = " . $id . " AND `a`.`type` = 2";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$region = array();
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$region[] = $row['group_name'];
			}
		$return = implode(", ", $region);
		return $return;
		}
		
	function get_assignscount($id) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`	WHERE `ticket_id` = " . $id;
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$count = 0;
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$count++;
			}
		return $count;
		}

	function get_routeDistance($start, $end) {
		$start = str_replace (" ", "+", $start);
		$end = str_replace (" ", "+", $end);
		$api = get_variable('gmaps_api_key');
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $start . "&destination=" . $end . "&key=" . $api;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		curl_close($ch);
		$json = json_decode($result);
		if($json) {$routes = $json->routes;} else {$routes=false;}
		if($routes) {$legs = $routes[0]->legs;} else {$legs = false;}
		if($legs) {$distance = $legs[0]->distance;} else {$distance=false;}
		if($distance) {
			$distance = intval($distance->text);
			$distance = $distance / 1.609344;
			}
		return $distance;
		}

	function date_range($dr_date_in, $dr_func_in) {			// returns array of MySQL-formatted dates
		global $theStartDate, $theEndDate;
		$temp = explode(",", $dr_date_in);					// into m, d, y
		$range = array();				// mktime ($hour, $minute, $second, $month, $day, $year)$temp[0] $temp[1] $temp[2]
		switch ($dr_func_in) {
			case "dr":
				$range[0] = mysql_format_date(mktime(0,0,0,$temp[0],$temp[1],$temp[2]));		// m, d, y -- date ('D, M j',
				$range[1] = mysql_format_date(mktime(0,0,0,$temp[0],$temp[1]+1,$temp[2]));
				$range[2] = date ('D, M j',mktime(0,0,0,$temp[0],$temp[1],$temp[2]));
				$range[3] = date ('D, M j',mktime(0,0,0,$temp[0],$temp[1]+1,$temp[2]));
				return $range;
				break;

			case "cm" :		// current month
				$range[0] = mysql_format_date(mktime(0,0,0,$temp[0],1,$temp[2]));			// m, d, y
				$range[1] = mysql_format_date(mktime(23,59,59,$temp[0],$temp[1],$temp[2]));	// from day 1 of this month m
				$range[2] = date ('D, M j', mktime(0,0,0,$temp[0],1,$temp[2]));				// m, d, y
				$range[3] = date ('D, M j', mktime(23,59,59,$temp[0],$temp[1],$temp[2]));	// from day 1 of this month m
				return $range;
				break;

			case "cw" :		// current week
				for ($i=0;$i<7;$i++) {												// find last Monday
					$monday = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
					if (date('w', $monday) == 1){
						break;
						}
					}
				$range[0] = mysql_format_date(mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday)));	// midnight sun/mon
				$range[1] = mysql_format_date(mktime(23,59,59,date('m'),date('d'),date('Y')));								// today
				$range[2] = date ('D, M j', mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday)));		// midnight sun/mon
				$range[3] = date ('D, M j', mktime(23,59,59,date('m'),date('d'),date('Y')));								// today
				return $range;
				break;

			case "lw" :		// last week
				for ($i=0;$i<7;$i++) {												// find last Monday
					$monday = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
					if (date('w', $monday) == 1){
						break;
						}
					}
				$prior_monday = $monday - (7*24*60*60);	// back seven days
				$range[0] = mysql_format_date(mktime(0,0,0,date('m', $prior_monday), date('d', $prior_monday), date('Y', $prior_monday)));	// midnight sun/mon
				$range[1] = mysql_format_date(mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday)));					// midnight sun/mon
				$range[2] = date ('D, M j', mktime(0,0,0,date('m', $prior_monday), date('d', $prior_monday), date('Y', $prior_monday)));	// midnight sun/mon
				$range[3] = date ('D, M j', mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday))-1);						// midnight sun/mon
				return $range;
				break;

			case "lm" :		// last month
				$prior1st = mktime(0, 0, 0, date("m")-1, 1, date("Y"));
				$this1st = mktime(0, 0, 0, date("m"), 1, date("Y"));

				$range[0] = mysql_format_date(mktime(0,0,0,date('m', $prior1st), date('d', $prior1st), date('Y', $prior1st)));	// midnight on prior 1st
				$range[1] = mysql_format_date(mktime(0,0,0,date('m', $this1st), date('d', $this1st), date('Y', $this1st)));		// midnight on this month's 1st
				$range[2] = date ('D, M j', mktime(0,0,0,date('m', $prior1st), date('d', $prior1st), date('Y', $prior1st)));	// midnight on prior 1st
				$range[3] = date ('D, M j', mktime(0,0,0,date('m', $this1st), date('d', $this1st), date('Y', $this1st))-1);		// midnight on this month's 1st
				return $range;
				break;

			case "cy" :		// current year
				$range[0] = mysql_format_date(mktime(0,0,0,1,1,date("Y")));							// from Jan 1 of this year
				$range[1] = mysql_format_date(mktime(23,59,59, date('m'),date('d'),date("Y")));		// to today
				$range[2] = date ('D, M j', mktime(0,0,0,1,1,date("Y")));
				$range[3] = date ('D, M j', mktime(23,59,59,date('m'),date('d'),date("Y")));
				return $range;
				break;

			case "ly" :		// last year
				$range[0] = mysql_format_date(mktime(0,0,0,1,1,date("Y")-1));				// from Jan 1 of last year
				$range[1] = mysql_format_date(mktime(23,59,59,12,31,date("Y")-1));			// to Dec 31 of that year
				$range[2] = date ('D, M j', mktime(0,0,0,1,1,date("Y")-1));					//
				$range[3] = date ('D, M j', mktime(23,59,59,12,31,date("Y")-1));			//
				return $range;
				break;

			case "s" :		// Specific Dates
				$range[0] = mysql_format_date(mktime(0,0,0,$theStartDate[0],$theStartDate[1],$theStartDate[2]));
				$range[1] = mysql_format_date(mktime(23,59,59,$theEndDate[0],$theEndDate[1],$theEndDate[2]));
				$range[2] = date ('D, M j', mktime(0,0,0,$theStartDate[0],$theStartDate[1],$theStartDate[2]));
				$range[3] = date ('D, M j', mktime(23,59,59,$theEndDate[0],$theEndDate[1],$theEndDate[2]));			//			
				return $range;
				break;
				
			default:
			    echo " error - error - error " . $dr_func_in;
			}		// end switch ()
		}				// end function date range()

	function date_part($in_date) {						// return date part of date/time string
		$temp = explode (" ", $in_date);
		return $temp[0];
		}		// end function date_part()

	function time_part($in_date) {						// "2007-12-02 21:07:30"
		$temp = explode (" ", $in_date);
		return substr($temp[1], 0, 5);
		}		// end function time_part()


// =================================================== DISPATCH LOG =========================================

	function do_dispreport($date_in, $func_in) {
		global $nature, $disposition, $patient, $incident, $incidents;
		global $evenodd, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$ret_arr = array();

		function the_time($in_val) {
			return date("j H:i", (int)$in_val);
			}

		function do_cells ($in_1, $in_2) {
			global $row, $out_row_1, $out_row_2;
			if (is_date($row['in_1'])) {
				$out_val1 = format_date_2($row['in_2']);
				$out_val2 = my_date_diff($row['problemstart_i'], $row['in_2']);
				}
			else {$out_val1 = $out_val2 = "";}
			$out_row_1 .= "<TD CLASS='td_data text text_normal text_center'>{$out_val1}</TD>";
			$out_row_2 .= "<TD CLASS='td_data text text_normal text_center'>{$out_val2}</TD>";
			}

		function do_cell ($in_1, $in_2) {
			return (is_date($in_2))? format_date_2($in_1): "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}

		$from_to = date_range($date_in,$func_in);	// get date range as array

		$titles = array ();
		$titles['dr'] = "Dispatch Log Daily Report - ";
		$titles['cm'] = "Dispatch Log Current Month-to-date - ";
		$titles['lm'] = "Dispatch Log Last Month - ";
		$titles['cw'] = "Dispatch Log Current Week-to-date - ";
		$titles['lw'] = "Dispatch Log Last Week - ";
		$titles['cy'] = "Dispatch Log Current Year-to-date - ";
		$titles['ly'] = "Dispatch Log Last Year - ";
		$titles['s'] = "Dispatch Log Last Year -  - Specific Dates - ";
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$where = " WHERE  `a`.`dispatched` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
					OR  `a`.`responding` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
					OR  `a`.`on_scene` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
					OR  `a`.`u2fenr` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
					OR  `a`.`u2farr` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
					OR  `a`.`clear` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'";

		$which_inc = ($_GET['tick_sel']==0)? 	"" : " AND `ticket_id` = " . 	quote_smart($_GET['tick_sel']);				// 2/7/09
		$which_unit = ($_GET['resp_sel']==0)? 	"" : " AND `responder_id` = " . quote_smart($_GET['resp_sel']);

		$query = "SELECT *,
			`dispatched` AS dispatched_i,
			`responding` AS responding_i,
			`on_scene` AS on_scene_i,
			`u2fenr` AS u2fenr_i,
			`u2farr` AS u2farr_i,
			`clear` AS clear_i,
			`a`.`comments` AS `disp_comments`,
			`r`.`handle`,
			`t`.`problemstart` AS `problemstart_i`,
			`r`.`handle`
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`t`.`id` = `a`.`ticket_id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`r`.`id` = `a`.`responder_id`)
			{$where} {$which_inc} {$which_unit}
			ORDER BY `t`.`severity` DESC, `a`.`id` ASC" ;

//		dump($query);

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if (mysql_affected_rows()>0) {										// main loop - top
			$temp = explode("/", get_variable('disp_stat'));				// 1/7/2013
			if (count($temp)< 6) {$temp = 	explode("/", "D/R/O/FE/FA/Clear");}
			$header= "<thead><TR CLASS = '{$evenodd[1]} {highest}'>
				<TH class='plain_listheader text text_left'>" . get_text("Unit") . "&nbsp;</TH>
				<TH class='plain_listheader text text_left'>" . get_text("Incident") . "&nbsp;</TH>
				<TH class='plain_listheader text text_left'>Start</TH>
				<TH class='plain_listheader text text_left'>{$temp[0]}&nbsp;</TH>
				<TH class='plain_listheader text text_left'>{$temp[1]}&nbsp;</TH>
				<TH class='plain_listheader text text_left'>{$temp[2]}&nbsp;</TH>
				<TH class='plain_listheader text text_left'>{$temp[3]}&nbsp;</TH>
				<TH class='plain_listheader text text_left'>{$temp[4]}&nbsp;</TH>
				<TH class='plain_listheader text text_left'>{$temp[5]}&nbsp;</TH>
				<TH class='plain_listheader text text_left'>Comments&nbsp;</TH>
				</TR></thead><tbody>\n";
			$table .= $header;
			$i = 0;
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// main loop - top
				switch($row['severity'])		{		//style row by severity
				 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='high'; break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='highest'; break;
					default: 							$severityclass='typical'; break;
					}

				$row_tr = "<TR CLASS = '{$evenodd[$i%2]} {$severityclass}'>";
				$row_tr .= "<TD CLASS='plain_list text text_left'>&nbsp;{$row['handle']}</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_left'>&nbsp;{$row['scope']}</TD>\n";		//
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['problemstart_i'], $row['problemstart']) . "</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['dispatched_i'], $row['dispatched']) . "</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['responding_i'], $row['responding']) . "</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['on_scene_i'], $row['on_scene']) . "</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['u2fenr_i'], $row['u2fenr']) . "</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['u2farr_i'], $row['u2farr']) . "</TD>\n";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . do_cell ($row['clear_i'], $row['clear']) . "</TD>\n";
				$disp_comments = ($row['disp_comments'] != "" && $row['disp_comments'] != "New") ? $row['disp_comments'] : "&nbsp;&nbsp;&nbsp;&nbsp;";
				$row_tr .= "<TD CLASS='plain_list text text_center'>&nbsp;" . $disp_comments . "</TD>\n";
				$row_tr .= "</TR>\n";
				$table .= $row_tr;
				$i++;
				}
			} else {		// end if (mysql_affected_rows()>0)
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .=  "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Dispatch Log</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			}
		$table .= "</tbody></TABLE><BR/><BR/>";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}		// end function do_dispreport()

// =================================================== FACILITY REPORT =========================================

	function do_facilityreport($date_in, $func_in) {				// $frm_date, $mode as params
		global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
		global $evenodd, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
	
		$ret_arr = array();

		$from_to = date_range($date_in,$func_in);	// get date range as array

		$incidents = $severity = $unit_names = $status_vals = $users = $unit_status_ids = array();
		$query = "SELECT `id`, `scope`, `severity` FROM `$GLOBALS[mysql_prefix]ticket`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$incidents[0]="";

		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$incidents[$temp_row['id']]=$temp_row['scope'];
			$severity[$temp_row['id']]=$temp_row['severity'];
			}

		$query = "SELECT `id`, `name`, `status_id` FROM `$GLOBALS[mysql_prefix]facilities`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$unit_names[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$unit_names[$temp_row['id']]=$temp_row['name'];
			$unit_status_ids[$temp_row['id']]=$temp_row['status_id'];
			}

		$query = "SELECT `id`, `status_val` FROM `$GLOBALS[mysql_prefix]fac_status`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$status_vals[0]="??";										// 2/2/09
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$status_vals[$temp_row['id']]=$temp_row['status_val'];
			}

		$query = "SELECT `id`, `user` FROM `$GLOBALS[mysql_prefix]user`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$users[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$users[$temp_row['id']]=$temp_row['user'];
			}
		$priorities = array("text_black","text_blue","text_red" );
		$titles = array ();
		$titles['dr'] = get_text("Facilities") . " - Daily Report - ";
		$titles['cm'] = get_text("Facilities") . " - Current Month-to-date - ";
		$titles['lm'] = get_text("Facilities") . " - Last Month - ";
		$titles['cw'] = get_text("Facilities") . " - Current Week-to-date - ";
		$titles['lw'] = get_text("Facilities") . " - Last Week - ";
		$titles['cy'] = get_text("Facilities") . " - Current Year-to-date - ";
		$titles['ly'] = get_text("Facilities") . " - Last Year - ";
		$titles['s'] = get_text("Facilities") . " - Specific Dates - ";
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$i = 1;

//		collect status values in use
		$query = "SELECT DISTINCT `info` FROM `$GLOBALS[mysql_prefix]log` WHERE `code` = " . $GLOBALS['LOG_FACILITY_STATUS'] . " ORDER BY `info` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i++;

		$table .= "<thead><TR style='width: 100%;'><TH class='plain_listheader text text_left'>" . get_text("Facility") . "</TH>";
		$curr_unit = "";
		$statuses = array();
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// build header row
			if (!empty($row['info'])){
				$statuses[$row['info']] = "";										// define the entry
				$query = "SELECT `status_val` FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id` = " . $row['info'] . " LIMIT 1" ;// status type
				$result_val= mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				$row_val = stripslashes_deep(mysql_fetch_assoc($result_val));
				$the_status = (empty($row_val))? "??": shorten($row_val['status_val'], 12); 		// 2/2/09
				$table .= "\t<TH class='plain_listheader text text_left'>&nbsp;&nbsp;" . shorten($the_status, 12) . "&nbsp;&nbsp;</TH>\n";
				}
			}
		$table .=  "<TH class='plain_listheader text text_left'>{$incident}</TH>";
		$table .=  "<TH class='plain_listheader text text_left'>Comment</TH></TR></thead><tbody>\n";	//	9/10/13
		$blank = $statuses;

		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
//		$which_unit = ($_POST['frm_resp_sel']==0)? "" : " AND `responder_id` = " .$_POST['frm_resp_sel'];
																																			// 3/23/09
		$query = "SELECT *,
			`when` AS `when_num`,
			`facility` AS `facility`,
			`info` AS `status`,
			`ticket_id` AS `incident`
			FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` r ON (`$GLOBALS[mysql_prefix]log`.facility = r.id) ".
			$where . " AND `code` = " . $GLOBALS['LOG_FACILITY_STATUS'] . " ORDER BY `name` ASC, `incident` ASC, `when` ASC" ;	//	9/10/13
//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i = 0;

		if (mysql_affected_rows()>0) {				// main loop - top
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$do_date=$row['when'];
				$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
				$curr_unit = $row["facility"];
				$theUnitName = (array_key_exists($row["facility"], $unit_names))? shorten($unit_names[$row["facility"]], 16): "#" . $row["facility"] ;
				
				$table .= (array_key_exists($curr_unit, $unit_names))? "<TD CLASS='plain_list text text_left' onClick = 'viewU(" .$curr_unit . ")'>" . $theUnitName . "</TD>":	"<TD>[#" . $curr_unit . "]</TD>";
				if (!empty($do_date)) {
					$table .= "<TD CLASS='plain_list text text_left'>" . date ('D, M j', strtotime($do_date)) . "</TD>";
					$do_date = "";
					} else {
					$table .= "<TD CLASS='plain_list text text_left'></TD>";
					}
				$theUnitName = (array_key_exists($row["facility"], $unit_names))? shorten($unit_names[$row["facility"]], 16): "#" . $row["facility"] ;
				foreach($statuses as $key => $val) {
					if($row['status'] == $key) {
						$val = date("H:i:s", strtotime($row['when_num']));
					}
					$table .= "<TD CLASS='plain_list text text_left'>$val</TD>";
					}
				if ($row['incident']>0) {				// 6/6/08
					$theIncidentName = (array_key_exists($row['incident'], $incidents))? $incidents[$row['incident']]: "#" . $row['incident'] ;
					$theSeverity = (array_key_exists($row['incident'], $severity))? $severity[$row['incident']]: 0;
					$table .= (array_key_exists($row['incident'], $incidents))?	"<TD CLASS='" . $priorities[$theSeverity] . " plain_list text text_left' onClick = 'viewT(" . $row['incident'] . ")'><B>" . $theIncidentName . "</B></TD>":	"<TD>#" . $row['incident']. " ??</TD>";
					} else {
					$table .= "<TD CLASS='plain_list text text_left'></TD>";
					}
				$table .= "<TD CLASS='plain_list text text_left'></TD>";
				$i++;
				$table .= "</TR>\n";
				}		// end while($row...)		 main loop - bottom
			} else {		// end if (mysql_affected_rows()>0)
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .=  "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>" . get_text("Facility") . " Report</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			}
		$table .= "<TR><TD CLASS='plain_list text text_center' COLSPAN=99>";
		$m = date("m"); $d = date("d"); $y = date("Y");

		$table .= "</TD></TR>";
		$i++;
		$table .= "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}		// end function do_facilityreport()

// =================================================== UNIT LOG =========================================

	function do_unitreport($date_in, $func_in) {				// $frm_date, $mode as params
		global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
		global $evenodd, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$ret_arr = array();

		$from_to = date_range($date_in,$func_in);	// get date range as array

		$incidents = $severity = $unit_names = $status_vals = $users = $unit_status_ids = array();
		$query = "SELECT `id`, `scope`, `severity` FROM `$GLOBALS[mysql_prefix]ticket`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$incidents[0]="";

		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$incidents[$temp_row['id']]=$temp_row['scope'];
			$severity[$temp_row['id']]=$temp_row['severity'];
			}

		$query = "SELECT `id`, `name`, `un_status_id` FROM `$GLOBALS[mysql_prefix]responder`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$unit_names[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$unit_names[$temp_row['id']]=$temp_row['name'];
			$unit_status_ids[$temp_row['id']]=$temp_row['un_status_id'];
			}

		$query = "SELECT `id`, `user` FROM `$GLOBALS[mysql_prefix]user`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$users[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$users[$temp_row['id']]=$temp_row['user'];
			}
		$priorities = array("text_black","text_blue","text_red" );
		$titles = array ();
		$titles['dr'] = get_text("Units") . " - Daily Report - ";
		$titles['cm'] = get_text("Units") . " - Current Month-to-date - ";
		$titles['lm'] = get_text("Units") . " - Last Month - ";
		$titles['cw'] = get_text("Units") . " - Current Week-to-date - ";
		$titles['lw'] = get_text("Units") . " - Last Week - ";
		$titles['cy'] = get_text("Units") . " - Current Year-to-date - ";
		$titles['ly'] = get_text("Units") . " - Last Year - ";
		$titles['s'] = get_text("Units") . " - Specific Dates - ";		
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$i = 1;

//		collect status values in use
		$query = "SELECT DISTINCT `info` FROM `$GLOBALS[mysql_prefix]log` WHERE `code` = " . $GLOBALS['LOG_UNIT_STATUS'] . " ORDER BY `info` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i++;

		$table .=  "<thead><TR style='width: 100%;'>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Unit") . "</TH>";
		$table .=  "<TH CLASS='plain_listheader text text_left'>Date</TH>";
		$curr_unit = "";
		$count = 0;
		$statuses = array();
		$statusvals = array();
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// build header row
			if (!empty($row['info'])) {
				$query = "SELECT `status_val` FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . intval($row['info']) . " LIMIT 1" ;// status type
				$result_val= mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				if(mysql_num_rows($result_val) == 0) {
					$statuses[0] = "";
					$table .= "\t<TH CLASS='plain_listheader text text_center' TITLE='Status No Longer in use'>??</TH>\n";
					$statusvals[0] = "?";
					} else {
					$statuses[$row['info']] = "";
					$row_val = stripslashes_deep(mysql_fetch_assoc($result_val));
					$the_status = shorten($row_val['status_val'], 9);
					$table .= "\t<TH CLASS='plain_listheader text text_center' TITLE='" . $row_val['status_val'] . "'>" . $the_status . "</TH>\n";
					$statusvals[$row['info']] = $the_status;
					}
				}
			}		
		$status_count = count($statuses);
		$table .=  "<TH CLASS='plain_listheader text text_center'>{$incident}</TH>";
		$table .=  "<TH CLASS='plain_listheader text text_left'>Comment</TH></TR></thead><tbody>\n";	//	9/10/13
		$blank = $statuses;
		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
		$which_unit = ((!isset($_POST['frm_resp_sel']) || ($_POST['frm_resp_sel']==0)))? "" : " AND `responder_id` = " .$_POST['frm_resp_sel'];
																																		// 3/23/09
		$query = "SELECT *,
			`when` AS `when_num`,
			`responder_id` AS `unit`,
			`info` AS `status`,
			`ticket_id` AS `incident`
			FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON (`$GLOBALS[mysql_prefix]log`.responder_id = r.id) ".
			$where . $which_unit. " AND ((`code` = " . $GLOBALS['LOG_UNIT_STATUS'] . ") OR (`code` = " . $GLOBALS['LOG_UNIT_COMMENT'] . ") OR (`code` = " . $GLOBALS['LOG_COMMENT'] . ")) ORDER BY `name` ASC, `incident` ASC, `status` ASC, `when` ASC" ;	//	9/10/13
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i = 0;
		if (mysql_affected_rows()>0) {				// main loop - top
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				if (empty($curr_unit)) {
					$curr_unit = $row['unit'];
					$curr_inc = $row['incident'];
					$curr_date_test = date ('z', strtotime($row['when_num']));			// day of year as test value
					$do_date=$row['when_num'];
					}								// populate break item
				if (($row['unit'] == $curr_unit) && ($row['incident'] == $curr_inc ) && (date ('z', strtotime($row['when_num'])) == $curr_date_test )) {	// same unit and incident, date?
					if(array_key_exists($row['status'], $statuses)) {
						$statuses[$row['status']] = time_part($row['when']);		// yes, populate the row
						}
					$theIncident_id = $row['incident'];
					$i++;
					} else {						// no, flush, initialize and populate
					if($row['code'] == $GLOBALS['LOG_UNIT_STATUS']) {	//	9/10/13
						if(array_key_exists($row['status'], $statuses)) {
							$statuses[$row['status']] = time_part($row['when']);		// yes, populate the row
							}
						$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
						$theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;
						$table .= (array_key_exists($curr_unit, $unit_names))? "<TD CLASS='plain_list text text_normal text_left' onClick = 'viewU(" .$curr_unit . ")'>" . $theUnitName . "</TD>":	"<TD CLASS='plain_list text text_normal text_left'>[#" . $curr_unit . "]</TD>";	//	Unit column
						if (!empty($do_date)) {		//	The Date column
							$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', strtotime($do_date)) . "</TD>";
							$do_date = "";
							} else {
							$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
							}
						if(((date ('z', strtotime($row['when_num']))) != $curr_date_test)) {		// date change?
							$do_date=$row['when_num'];
							$curr_date_test = date ('z', strtotime($row['when_num']));
							}
						foreach($statuses as $val) {
							if($val != "") {
								$table .= "<TD CLASS='plain_list text text_normal text_center'>" . $val . "</TD>";		//	Each Status change time
								} else {
								$table .= "<TD CLASS='plain_list text text_normal text_center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";		//	Each Status change time
								}
							}
						if ($row['incident'] > 0) {				// 6/6/08 The Incident Column
							$theIncidentName = (array_key_exists($row['incident'], $incidents))? $incidents[$row['incident']]: "#" . $row['incident'] ;
							$theSeverity = (array_key_exists($row['incident'], $severity))? $severity[$row['incident']]: 0;
							$table .= (array_key_exists($row['incident'], $incidents))?	"<TD CLASS='" . $priorities[$theSeverity] . " plain_list text text_normal text_left' onClick = 'viewT(" . $row['incident'] . ")'>" . $theIncidentName . "</TD>":	"<TD CLASS='plain_list text text_normal text_left'>#" . $row['incident']. " ??</TD>";
							} else {
							$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
							}
						$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";		//	Info Column
						$curr_unit = $row['unit'];
						$curr_inc = $row['incident'];
						$i++;
						$theIncident_id = $row['incident'];
						} elseif($row['code'] == $GLOBALS['LOG_UNIT_COMMENT'] || $row['code'] == $GLOBALS['LOG_COMMENT']) {
						$statuses = $blank;
						$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
						$theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;
						$table .= (array_key_exists($curr_unit, $unit_names))? "<TD CLASS='plain_list text text_normal text_left' onClick = 'viewU(" .$curr_unit . ")'>" . $theUnitName . "</TD>":	"<TD CLASS='plain_list text text_normal text_left'>[#" . $curr_unit . "]</TD>";	//	Unit column
						if (!empty($do_date)) {		//	The Date column
							$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', strtotime($do_date)) . "</TD>";
							$do_date = "";
							} else {
							$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";
							}
						foreach($statuses as $val) {	//	Status columns - all blank
							$table .= "<TD CLASS='plain_list text text_normal text_center text_blue'>------</TD>";
							} 
						$table .= "<TD CLASS='plain_list text text_normal text_center text_blue'>------</TD>";		//	The Incident Column
						$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['info'] . "</TD>";	//	The Info Column
						$statuses = $blank;															// initalize
						$i++;
						} else {	//	9/10/13 Other Codes
						$statuses = $blank;
						$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
						$theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;
						$table .= (array_key_exists($curr_unit, $unit_names))? "<TD CLASS='plain_list text text_normal text_left' onClick = 'viewU(" .$curr_unit . ")'>" . $theUnitName . "</TD>":	"<TD CLASS='plain_list text text_normal text_left'>[#" . $curr_unit . "]</TD>";	//	Unit column
						if (!empty($do_date)) {		//	The Date column
							$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', strtotime($do_date)) . "</TD>";
							$do_date = "";
							} else {
							$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";
							}
						foreach($statuses as $val) {	//	Status columns - all blank
							$table .= "<TD CLASS='plain_list text text_normal text_center text_blue'>------</TD>";
							} 
						$table .= "<TD CLASS='plain_list text text_normal text_center text_blue'>------</TD>";		//	The Incident Column
						$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['info'] . "</TD>";	//	The Info Column
						$statuses = $blank;															// initalize
						$i++;
						}
					}
				$table .= "</TR>";
				}		// end while($row...)		 main loop - bottom
			$table .= "\n<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
			$theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16):  "#" . $curr_unit ;
			$table .= "<TD CLASS='plain_list text text_normal text_left' onClick = 'viewU(" .$curr_unit . ")'><B>" . $theUnitName . "</B></TD>";		// flush tail-end Charlie, Unit Column
			$work_date = (!empty($do_date))? date ('D, M j', strtotime($do_date)) : "" ; // 1/7/2013
			$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $work_date . "</TD>";	//	Date column
			foreach($statuses as $val) {
				if($val != "") {
					$table .= "<TD CLASS='plain_list text text_normal text_center'>" . $val . "</TD>";		//	Each Status change time
					} else {
					$table .= "<TD CLASS='plain_list text text_normal text_center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";		//	Blank
					}
				}
			if ($theIncident_id>0) {	//	Incident column
				$theIncidentName = (array_key_exists($theIncident_id, $incidents))? $incidents[$theIncident_id]: "#" . $theIncident_id ;
				$theSeverity = (array_key_exists($theIncident_id, $severity))? $severity[$theIncident_id]: 0;
				$table .= "<TD CLASS='" . $priorities[$theSeverity] . " plain_list text text_normal text_left' onClick = 'viewT(" . $theIncident_id . ")'>" . $theIncidentName . "</TD>";
				} else {
				$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";
				}
			$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD></TR>";	//	Info Column
			} else {	// end if (mysql_affected_rows()>0)
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .=  "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>" . get_text("Unit") . " Report</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			}
		$statuses = $blank;															// initalize
		$i++;
		$table .= "<TR style='width: 100%;'><TD CLASS='plain_list text text_normal text_left' COLSPAN=99>";
		$m = date("m"); $d = date("d"); $y = date("Y");

		$table .= "</TD></TR>";
		$i++;
		$table .= "<TR style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}		// end function do_unitreport()
		
// =================================================== UNIT COMMS LOG =========================================

	function do_unitcommsreport($date_in, $func_in) {				// $frm_date, $mode as params
		global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
		global $evenodd, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$ret_arr = array();

		$from_to = date_range($date_in,$func_in);	// get date range as array

		$unit_names = $users = $responders = array();
		$query = "SELECT `id`, `name`, `un_status_id` FROM `$GLOBALS[mysql_prefix]responder`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$unit_names[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$unit_names[$temp_row['id']]=$temp_row['name'];
			$unit_status_ids[$temp_row['id']]=$temp_row['un_status_id'];
			}

		$query = "SELECT `id`, `user` FROM `$GLOBALS[mysql_prefix]user`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$users[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$users[$temp_row['id']]=$temp_row['user'];
			}

		$priorities = array("text_black","text_blue","text_red" );
		$titles = array ();
		$titles['dr'] = get_text("Comms") . " - Daily Report - ";
		$titles['cm'] = get_text("Comms") . " - Current Month-to-date - ";
		$titles['lm'] = get_text("Comms") . " - Last Month - ";
		$titles['cw'] = get_text("Comms") . " - Current Week-to-date - ";
		$titles['lw'] = get_text("Comms") . " - Last Week - ";
		$titles['cy'] = get_text("Comms") . " - Current Year-to-date - ";
		$titles['ly'] = get_text("Comms") . " - Last Year - ";
		$titles['s'] = get_text("Comms") . " - Specific Dates - ";
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$i = 1;

//		collect status values in use
		$query = "SELECT DISTINCT `info` FROM `$GLOBALS[mysql_prefix]log` WHERE `code` = " . $GLOBALS['LOG_UNIT_STATUS'] . " ORDER BY `info` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i++;

		$table .=  "<thead><TR style='width: 100%;'>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("User") . "</TH>";
		$curr_unit = "";
		$table .=  "<TH CLASS='plain_listheader text text_left'>" . get_text("Unit") . "</TH>";
		$table .=  "<TH CLASS='plain_listheader text text_left'>" . get_text("Date") . "</TH>";
		$table .=  "<TH CLASS='plain_listheader text text_left'>" . get_text("Time") . "</TH>";
		$table .=  "<TH CLASS='plain_listheader text text_left'>" . get_text("Type") . "</TH>";
		$table .=  "<TH CLASS='plain_listheader text text_left'>" . get_text("Message") . "</TH></TR></thead><tbody>";
		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
		$which_unit = ((!isset($_POST['frm_resp_sel']) || ($_POST['frm_resp_sel']==0)))? "" : " AND `responder_id` = " .$_POST['frm_resp_sel'];
																																			// 3/23/09
		$query = "SELECT *,
			`when` AS `when_num`,
			`responder_id` AS `unit`,
			`info` AS `status`
			FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON (`$GLOBALS[mysql_prefix]log`.responder_id = r.id) ".
			$where . $which_unit. " AND ((`code` = " . $GLOBALS['LOG_COMMENT'] . ") OR (`code` = " . $GLOBALS['LOG_UNIT_COMMENT'] . ") OR (`code` = " . $GLOBALS['LOG_BROADCAST_MESSAGE'] . ") OR (`code` = " . $GLOBALS['LOG_BROADCAST_ALERT'] . ")) ORDER BY `name` ASC, `when` ASC" ;	//	9/10/13
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i = 0;
		if (mysql_num_rows($result)>0) {				// main loop - top
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				if (empty($curr_unit)) {
					$who = $row['unit'];
					$curr_date_test = date ('z', strtotime($row['when_num']));
					$curr_time_test = date ("H:i:s", strtotime($row['when_num']));
					}								// populate break item
				if (($row['unit'] == $curr_unit) && ($row['unit'] > 0) && (date ('z', strtotime($row['when_num'])) == $curr_date_test ) && (date ('H:i:s', strtotime($row['when_num'])) == $curr_time_test )) {	// same unit and date and time?
					$table .= "<TR CLASS='" . $evenodd[$i%2] . "' STYLE='width: 100%;'>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	Blank Unit field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	Blank Unit field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	blank date field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	blank time field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $types[$row['code']] . "</TD>";	//	populate new time
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['info'] . "</TD>";	//	The Info Column
					$i++;
					} elseif(($row['unit'] == $curr_unit) && ($row['unit'] > 0) && (date ('z', strtotime($row['when_num'])) == $curr_date_test ) && (date ('H:i:s', strtotime($row['when_num'])) != $curr_time_test )) {	// same unit and date, different time?
					$table .= "<TR CLASS='" . $evenodd[$i%2] . "' STYLE='width: 100%;'>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	Blank Unit field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	Blank Unit field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>&nbsp;</TD>";	//	blank date field
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date("H:i:s", strtotime($row['when_num'])) . "</TD>";	//	populate new time
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $types[$row['code']] . "</TD>";	//	populate new time
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['info'] . "</TD>";	//	The Info Column
					$curr_unit = $row['unit'];
					$curr_time_test = date ("H:i:s", strtotime($row['when_num']));
					$i++;
					} else {	//	different everything
					$table .= "<TR CLASS='" . $evenodd[$i%2] . "' STYLE='width: 100%;'>";
					$user = (array_key_exists($row['who'], $users)) ? $users[$row['who']] : "UNK";
					$responder = (array_key_exists($row['unit'], $unit_names)) ? $unit_names[$row['unit']] : "UNK";
					$theWho = ($row['unit'] != 0) ? $responder : "N/A";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $user . "</TD>";	//	The Unit
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $theWho . "</TD>";	//	The Unit
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', strtotime($row['when_num'])) . "</TD>";	//	populate new date
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date("H:i:s", strtotime($row['when_num'])) . "</TD>";	//	populate new time
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $types[$row['code']] . "</TD>";	//	populate new time
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['info'] . "</TD>";	//	The Info Column
					$curr_unit = $row['unit'];
					$curr_date_test = date ('z', strtotime($row['when_num']));
					$curr_time_test = date ("H:i:s", strtotime($row['when_num']));
					$i++;						
					}
				$table .= "</TR>";
				}		// end while($row...)		 main loop - bottom
			} else {	//	end if mysql_num_rows()
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .= "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Communications Report</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			}
		$table .= "<TR><TD ALIGN='center' COLSPAN=99>";
		$m = date("m"); $d = date("d"); $y = date("Y");
		$table .= "</TD></TR>";
		$i++;
		$table .= "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}		// end function do_unitcommsreport()
		
// =============================================== STATION LOG  ===========================================

	function do_sta_report($date_in, $func_in) {				// $frm_date, $mode as params
		global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
		global $evenodd, $istest, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$ret_arr = array();

		$from_to = date_range($date_in,$func_in);	// get date range as array
//		dump ($from_to);

		$types[$GLOBALS['LOG_ERRONEOUS']]			= "Bad Log entry";			//	3/15/11
		$types[$GLOBALS['LOG_ERRONEOUS']]			= "Bad Log entry";			//	3/15/11
		$types[$GLOBALS['LOG_BROADCAST_MESSAGE']] 	= "Broadcast Message";		//	11/30/15
		$types[$GLOBALS['LOG_BROADCAST_ALERT']] 	= "Responder Alert";		//	11/30/15
		$types[$GLOBALS['LOG_BROADCAST_ERROR']] 	= "Broadcast Error";		//	11/30/15 
		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
		$query = "
			SELECT `when`, `l`.`id` AS `logid`,`l`.`info` AS `loginfo`,  t.scope AS `tickname`, `r`.`name` AS `unitname`, `s`.`status_val` AS `theinfo`, `u`.`user` AS `thename`,
			 `l`.`code`,  `l`.`ticket_id`,  `u`.`user`, `l`.`from`, `r`.`handle`
			FROM `$GLOBALS[mysql_prefix]log` `l`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`l`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`l`.`responder_id` = `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`l`.info = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`l`.`who` = `u`.`id`)
	 		$where ORDER BY `when` ASC
			";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

		$titles = array ();
		$titles['dr'] = "Station Daily Report - ";
		$titles['cm'] = "Station Report - Current Month-to-date - ";
		$titles['lm'] = "Station Report - Last Month - ";
		$titles['cy'] = "Station Report - Current Year-to-date - ";
		$titles['ly'] = "Station Report - Last Year - ";
		$titles['cw'] = "Station Report - Current Week-to-date - ";
		$titles['lw'] = "Station Report - Last Week - ";
		$titles['s'] = "Station Report - Specific Dates - ";
		$i = 0;
		$curr_date="";
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		if (mysql_affected_rows()>0) {
				$table .= "<thead><TR style='width: 100%;'>";
				$table .= "<TH CLASS='plain_listheader text text_left'>Date</TH>";		// 4/4/10
				$table .= "<TH CLASS='plain_listheader text text_left'>Time</TH>";
				$table .= "<TH CLASS='plain_listheader text text_left'>Code</TH>";
				$table .= "<TH CLASS='plain_listheader text text_left'>Call</TH>";
				$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Unit") . "</TH>";
				$table .= "<TH CLASS='plain_listheader text text_left'>Info</TH>";
				$table .= "<TH CLASS='plain_listheader text text_left'>User</TH>";
				$table .= "<TH CLASS='plain_listheader text text_left'>From</TH>";
				if ($istest) {$table .= "<TH CLASS='plain_listheader text text_left'>ID</TH>";}
				$table .= "</TR></thead><tbody>\n";

			$of_interest = array($GLOBALS['LOG_ERROR'], $GLOBALS['LOG_INTRUSION'], $GLOBALS['LOG_ICS_MESSAGE_SEND'], $GLOBALS['LOG_BROADCAST_MESSAGE'], $GLOBALS['LOG_BROADCAST_ALERT'], $GLOBALS['LOG_BROADCAST_ERROR']);
			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			// main loop - top
				if (($row['code']<20) || in_array( $row['code'], $of_interest) ) {		// 4/7/2014
					$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";

					if(!(date("z", mysql2timestamp($row['when'])) == $curr_date))  {								// date change?
						$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', mysql2timestamp($row['when'])) ."</TD>";
						$curr_date = date("z", mysql2timestamp($row['when']));
						} else {
						$table .= "<TD CLASS='plain_list text text_normal text_left'></TD>";
						}
//					$the_ticket = (empty($row['tickname']))? "[#" . $row['ticket_id']. "]" : $row['tickname'] ;

					if (empty($row['tickname'])) {
						$the_ticket = ($row['ticket_id']>0 )? "[#" . $row['ticket_id']. "]" :"";
						} else {
						$the_ticket =$row['tickname'] ;
						}
					$handle = "<DIV style='min-width: 30px;'>" . $row['handle'] . "</DIV>";
					$info = "<DIV style='max-width: 200px; min-width: 30px;'>" . $row['loginfo'] . "</DIV>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date('H:i', mysql2timestamp($row['when'])) . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $types[$row['code']] . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $the_ticket . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_center'>" . $handle . "</TD>";			// 5/29/12
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $info . "</TD>";			// 1/21/09
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['user'] . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['from'] . "</TD>";
					if ($istest) {$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['logid'] . "</TD>";}
					$table .= "</TR>\n";
					$i++;
					}
				}		// end while($row = ...)
			} else {		// end if (mysql_affected_rows() ...
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .= "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Station Report</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			}
		$table .= "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}		// end function do_sta_report()

// ================================================== INCIDENT SUMMARY =========================================
	function datediff($value1,$value2) {   
		$diff = $value1-$value2;  
		return $diff;
		}
	
	function parsedate($diff){	
		$seconds = 0;   
		$hours   = 0;   
		$minutes = 0;   

		if($diff % 86400 <= 0){$days = $diff / 86400;}  // 86,400 seconds in a day   
		if($diff % 86400 > 0) {   
			$rest = ($diff % 86400);   
			$days = ($diff - $rest) / 86400;   
			if($rest % 3600 > 0) {   
				$rest1 = ($rest % 3600);   
				$hours = ($rest - $rest1) / 3600;   
				if($rest1 % 60 > 0) {   
					$rest2 = ($rest1 % 60);   
					$minutes = ($rest1 - $rest2) / 60;   
					$seconds = $rest2;   
					} else{
					$minutes = $rest1 / 60;
					}   
				} else{
				$hours = $rest / 3600;
				}   
			}   

		
		if(floor($days) > 0){$days = floor($days).' D ';} else { $days = 0 . ' D '; };
		
		
		$hours = sprintf('%02d', floor($hours)).':';
		$minutes = sprintf('%02d',$minutes).':';
		$seconds = sprintf('%02d',$seconds); // always be at least one second   

		return $days.''.$hours.''.$minutes.''.$seconds;   
		}

	function do_inc_report($date_in, $func_in) {				// Incidents summary report - $frm_date, $mode as params
		global $evenodd, $img_width, $types, $doDownload ;
		global $nature, $disposition, $patient, $incident, $incidents;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$ret_arr = array();

		$from_to = date_range($date_in,$func_in);	// get date range as array
		$priorities = array("text_black","text_blue","text_red" );

		$types = array();
		$types[$GLOBALS['LOG_INCIDENT_OPEN']]		="{$incident} open";
		$types[$GLOBALS['LOG_INCIDENT_CLOSE']]		="{$incident} close";
		$types[$GLOBALS['LOG_INCIDENT_CHANGE']]		="{$incident} change";

		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
		$which_inc = ($_GET['tick_sel'] ==0)? "" : " AND `ticket_id` = " . $_Get['tick_sel'];				// 2/7/09

		$query = "
			SELECT *,
			`when` AS `when`,
			t.id AS `tick_id`,t.scope AS `tick_name`,
			t.severity AS `tick_severity`,
			`u`.`user` AS `user_name`
			FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON (`$GLOBALS[mysql_prefix]log`.ticket_id = t.id)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON (`$GLOBALS[mysql_prefix]log`.who = u.id)
			". $where . $which_inc . " AND `code` >= '" . $GLOBALS['LOG_INCIDENT_OPEN'] ."'  AND `code` <= '" . $GLOBALS['LOG_INCIDENT_CLOSE'] . "'
	 		ORDER BY `when` ASC
			";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

		$titles = array ();
		$titles['dr'] = "Incidents Daily Report - ";
		$titles['cm'] = "Incidents Report - Current Month-to-date - ";
		$titles['lm'] = "Incidents Report - Last Month - ";
		$titles['cy'] = "Incidents Report - Current Year-to-date - ";
		$titles['ly'] = "Incidents Report - Last Year - ";
		$titles['cw'] = "Incidents Report - Current Week-to-date - ";
		$titles['lw'] = "Incidents Report - Last Week - ";
		$titles['s'] = "Incidents Report - Specific Dates - ";
		$i = 0;
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$curr_date="";
		$secondTable = "";
		$num_incs = mysql_num_rows($result);
		if ($num_incs>0) {
			$table .= "<thead><TR style='width: 100%;'>";
			$table .= "<TH CLASS='plain_listheader text text_left'>Date</TH>";
			$table .= "<TH CLASS='plain_listheader text text_left'>Time</TH>";
			$table .= "<TH CLASS='plain_listheader text text_left'>Code</TH>";
			$table .= "<TH CLASS='plain_listheader text text_left'>{$incident}</TH>";
			$table .= "<TH CLASS='plain_listheader text text_left'>User</TH>";
			$table .= "<TH CLASS='plain_listheader text text_left'>From</TH>";
			$table .= "</TR></thead><tbody>\n";
			$inc_types = array();

			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			// 8/15/08 main loop - top
				if ($row['code']<20) {
					if (array_key_exists($row['in_types_id'], $inc_types)) {
						$inc_types[$row['in_types_id']]++;
						} else {
						$inc_types[$row['in_types_id']] = 1;
						}
					$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
					if(!(date("z", strtotime($row['when'])) == $curr_date))  {								// date change?
						$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', strtotime($row['when'])) ."</TD>";
						$curr_date = date("z", strtotime($row['when']));
						} else {
						$table .= "<TD CLASS='plain_list text text_normal text_left'></TD>";
						}
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date('H:i',strtotime($row['when'])) . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $types[$row['code']] . "</TD>";
					if ($row['ticket_id']>0) {
						$the_ticket = (empty($row['tick_name']))? "[#" . $row['ticket_id'] . "]" : $row['tick_name'];	// 8/15/08 -1
						$severity_class = empty($row['tick_severity'])? $priorities[0]: $priorities[$row['tick_severity']];			// accommodate null
						$table .= "<TD TITLE = '" .	$row['ticket_id'] . "' CLASS='" . $severity_class . " plain_list text text_normal text_left' onClick = 'viewT(" . $row['ticket_id'] . ")'>" . $the_ticket . "</TD>";
						}
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['user_name'] . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['from'] . "</TD>";
					$table .= "</TR>\n";
					$i++;
					}
				}		// end while($row = ...)

			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id IN (" . $query . ")";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			//
//				dump ($row['id']);
				}																// end while($row ...
//			graphics date range in db format and calculated img width - when` < '2013-06-01 23:59:59&p3=391'  AND `code` = '10'
			$s_urlstr =  "sever_graph.php?p1=" . 		urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1]) . "&p3={$img_width}";	//8/9/08
			$t_urlstr =  "inc_types_graph.php?p1=" . 	urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1]) . "&p3={$img_width}";
			$c_urlstr =  "city_graph.php?p1=" . 		urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1]) . "&p3={$img_width}";

			
			$table .= "<tbody></TABLE>";
			if(!$doDownload) {
				$secondTable .= "<TABLE style='width: 90%;'>";
				$secondTable .= "<TR><TD COLSPAN=3 ALIGN='center'><BR /><HR SIZE=1 COLOR='blue' WIDTH='50%'><BR /></TD></TR>";
				$secondTable .= "<TR VALIGN='bottom'><TD ALIGN='center'>";
				$secondTable .= "<img src='" . $s_urlstr . "' border=0 ID = 'sev_img'>";
				$secondTable .= "</TD>";
				$secondTable .= "<TD ALIGN='center'>";
				$secondTable .= "<img src='" . $t_urlstr . "' border=0 ID = 'typ_img'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$secondTable .= "</TD>";
				$secondTable .= "<TD ALIGN='center'>";
				$secondTable .= "<img src='" . $c_urlstr . "' border=0 ID = 'cit_img'>";
				$secondTable .= "</TD>";
				$secondTable .= "</TR>";
				$secondTable .= "</TABLE>";
				} else {
				$secondTable = "";
				}
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .= "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Incident Summary</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			}
		$thirdTable = "<CENTER><TABLE style='width: 70%;'>";
		$thirdTable .= "<TR CLASS='odd'>";
		$thirdTable .= "<th CLASS='td_data text text_normal text_left' colspan=\"2\">Response Time Averages</th>";
		$thirdTable .= "</tr>";
		$thirdTable .= "<TR CLASS='odd'>";
		$thirdTable .= "<th CLASS='td_data text text_normal text_left'>";
		$thirdTable .= "Details";
		$thirdTable .= "</th>";
		$thirdTable .= "<th CLASS='td_data text text_normal text_left'>";
		$thirdTable .= "Value";
		$thirdTable .= "</th>";
		$thirdTable .= "</tr>";
		
		// Number of tickets
		$query2 = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id` FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
		WHERE (problemstart between '$from_to[0]' and '$from_to[1]') AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
		$result = mysql_query($query2) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_tick = mysql_num_rows($result);
		$thirdTable .= "<tr><td class='even td_data text text_left text_bold'>Number of Calls</td><td CLASS='td_data text text_normal text_left'>$num_tick</td></tr>";

		// Dispatch Time
		// 	Average Time Call to Responding
		$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id` 
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		WHERE (problemstart between '$from_to[0]' and '$from_to[1]')  AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = mysql_fetch_assoc($result)) {
			$tick_id = $row['tick_id'];
			$query_01 = "SELECT *,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`responding`) as `responding`				
				FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `ticket_id` = $tick_id AND (`responding` IS NOT NULL OR DATE_FORMAT(`responding`,'%y') != '00') order by responding";
			$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row_01 = mysql_fetch_assoc($result_01)) {
				$disptime = strtotime($row['problemstart']);
				$resptime = $row_01['responding'];		
				$resp_list[$row_01['id']] = datediff($resptime, $disptime);
				break;
			}
		}

		if((isset($resp_list)) && (count($resp_list) > 0)) {
			$tot_call_resp = array_sum($resp_list);
			$avg_calltoresp = $tot_call_resp/count($resp_list);
			$avg_time_call2resp = $avg_calltoresp;
			} else {
			$avg_time_call2resp = 0;
			}
		$thirdTable .= "<tr><td class='even td_data text text_bold text_left'>Average Time from Call Received to Response</td><td CLASS='td_data text text_normal text_left'>" . parsedate($avg_time_call2resp) . "</td></tr>";

		$resp_list = array();

		// Dispatch Time
		// 	Average Time Dispatched to Responding
		$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id` 
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
			ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		WHERE (problemstart between '$from_to[0]' and '$from_to[1]')  AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = mysql_fetch_assoc($result)) {
			$tick_id = $row['tick_id'];
			$query_01 = "SELECT *,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`dispatched`) as `dispatched`,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`responding`) as `responding`				
				FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `ticket_id` = $tick_id AND (`responding` IS NOT NULL OR DATE_FORMAT(`responding`,'%y') != '00')";
			$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row_01 = mysql_fetch_assoc($result_01)) {
				$disptime = $row_01['dispatched'];
				$resptime = $row_01['responding'];		
				$resp_list[$row_01['id']] = datediff($resptime, $disptime);
				}
			}

		if((isset($resp_list)) && (count($resp_list) > 0)) {
			$tot_disp_resp = array_sum($resp_list);
			$avg_disptoresp = $tot_disp_resp/count($resp_list);
	
			$avg_time_disp2resp = $avg_disptoresp;
			} else {
			$avg_time_disp2resp = 0;
			}
		$thirdTable .= "<tr><td CLASS='even td_data text text_bold text_left'>Average Time from Dispatch to Response</td><td CLASS='td_data text text_normal text_left'>" . parsedate($avg_time_disp2resp) . "</td></tr>";
		
				//	Average time Dispatched to On Scene	
		$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
				FROM `$GLOBALS[mysql_prefix]ticket` 
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
					ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
				WHERE (problemstart between '$from_to[0]' and '$from_to[1]') AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_tick3 = mysql_num_rows($result);
		while ($row = mysql_fetch_assoc($result)) {
			$tick_id = $row['tick_id'];
			$query_01 = "SELECT *,
						UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`dispatched`) as `dispatched`,
						UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`responding`) as `responding`,				
						UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]assigns`.`on_scene`) as `on_scene`				
						FROM `$GLOBALS[mysql_prefix]assigns` 
						WHERE `ticket_id` = $tick_id AND (`on_scene` IS NOT NULL OR DATE_FORMAT(`on_scene`,'%y') != '00')";
			$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row_01 = mysql_fetch_assoc($result_01)) {
				$disptime = $row_01['dispatched'];
				$ostime = $row_01['on_scene'];			
				$os_list[$row_01['id']] = datediff($ostime, $disptime);		
				}
			}
				
		if((isset($os_list)) && (count($os_list) > 0)) {
			$tot_disp_os = array_sum($os_list);
			$avg_disptoos = $tot_disp_os/count($os_list);
			$avg_time_disp2os = $avg_disptoos;
			} else {
			$avg_time_disp2os = 0;
			}
		$thirdTable .= "<tr><td CLASS='even td_data text text_bold text_left'>Average Time from Dispatch to On-Scene (Response Time)</td><td CLASS='td_data text text_normal text_left'>" . parsedate($avg_time_disp2os) . "</td></tr>";

			// Total On-Scene Time
			//	Number of Responders dispatched and responding not on scene
		$query = "SELECT `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
				`$GLOBALS[mysql_prefix]assigns`.`id` AS `ass_id`,
				`$GLOBALS[mysql_prefix]assigns`.`responder_id` AS `resp_id`,		
				`$GLOBALS[mysql_prefix]assigns`.`dispatched` AS `dispatched`,	
				`$GLOBALS[mysql_prefix]assigns`.`responding` AS `responding`,
				`$GLOBALS[mysql_prefix]assigns`.`on_scene` AS `on_scene`,			
				`$GLOBALS[mysql_prefix]assigns`.`clear` AS `clear`
				FROM `$GLOBALS[mysql_prefix]ticket`
				LEFT JOIN `$GLOBALS[mysql_prefix]assigns` 
					ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]assigns`.`ticket_id`	
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
					ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
					WHERE (problemstart between '$from_to[0]' and '$from_to[1]') AND `$GLOBALS[mysql_prefix]assigns`.`on_scene` is not null GROUP BY `resp_id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$unit_cnt = 0;
		$tot_onscene_time = 0;
		$row_cnt = mysql_num_rows($result);
		while ($row = mysql_fetch_assoc($result)) {
			$onscene_time = strtotime($row['on_scene']);
			$clear_time = strtotime($row['clear']);
			$tot_onscene_time += datediff($clear_time, $onscene_time);
			$unit_cnt++;
		}
		if($row_cnt != 0) {
			$avg_onscene_time = $tot_onscene_time/$unit_cnt;
			} else {
			$avg_onscene_time = 0;
			}

		$thirdTable .= "<tr><td CLASS='even td_data text text_bold text_left'>Average Total On-Scene Time</td><td CLASS='td_data text text_normal text_left'>" . parsedate($avg_onscene_time) . "</td></tr>";
			
			
			//	Number of Tickets closed
		$query = "SELECT *,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemstart`) AS problemstart,
				UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`problemend`) AS problemend,
				`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
				`$GLOBALS[mysql_prefix]ticket`.`status` AS `status`
				FROM `$GLOBALS[mysql_prefix]ticket`
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
					ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
					WHERE (`status` = 1) and (problemstart between '$from_to[0]' and '$from_to[1]') AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 GROUP BY `tick_id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = mysql_fetch_assoc($result)) {
			$tick_ids[] = $row['tick_id'];
			$pbstartdate = $row['problemstart'];
			$pbenddate = $row['problemend'];	
			$time_toclose[$row['tick_id']] = datediff($pbenddate, $pbstartdate);	
			$tick_id = $row['tick_id'];
			}
			
			if((isset($time_toclose)) && (count($time_toclose) > 0)) {
			$tot_time = array_sum($time_toclose);
			$avg_time_toclose = $tot_time/count($time_toclose);
			$avg_tick_toclose = $avg_time_toclose;
			} else {
			$avg_tick_toclose = 0;
			}	
		$thirdTable .= "<tr><td CLASS='even td_data text text_bold text_left'>Average Total Call Time</td><td CLASS='td_data text text_normal text_left'>" . parsedate($avg_tick_toclose) . "</td></tr>";
		$thirdTable .= "</TABLE></CENTER>";
		if($num_incs == 0) {$secondTable = $thirdTable = "";}
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[2] = $secondTable;
		$ret_arr[3] = $thirdTable;
		$ret_arr[6] = $title;
		return $ret_arr;
		}		// end function do_inc_report()
// ================================================== INCIDENT LOG REPORT =========================================

	function do_inc_log_report($the_ticket_id) {			// 3/18/10
		global $types;
		global $theWidth, $doprint;		// 4/14/11
		global $nature, $disposition, $patient, $incident, $incidents;
		global $startdate, $enddate;
		
		$ret_arr = array();

		$tickets = $actions = $patients = $unit_names = $un_status = $unit_types = $users = $facilities = $fac_status = $fac_types = array();

		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]ticket`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$tickets[$row['id']] = substr($row['scope'], 0, 10) . "/" . substr($row['street'], 0, 10);
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]action`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$actions[$row['id']] = substr($row['description'], 0, 20);
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]patient`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$patients[$row['id']] = substr($row['description'], 0, 20);
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]responder`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$unit_names[$row['id']] = $row['name'];
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]un_status`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$un_status[$row['id']] = $row['status_val'];
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]unit_types`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$unit_types[$row['id']] = $row['name'];
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]user`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$users[$row['id']] = $row['user'];
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]facilities`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$facilities[$row['id']] = $row['name'];
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]fac_status`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$fac_status[$row['id']] = $row['status_val'];
			}
		$query = "SELECT *FROM `$GLOBALS[mysql_prefix]fac_types`";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$fac_types[$row['id']] = $row['name'];
			}
// ______________________________________________________________________________

		$query = "SELECT *,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`booked_date` AS `booked_date`,
			`date` AS `date`,
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS updated,
			 `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			 `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
			 `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
			 `$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
			 `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
			 `rf`.`name` AS `rec_fac_name`,
			 `rf`.`lat` AS `rf_lat`,
			 `rf`.`lng` AS `rf_lng`,
			 `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
			 `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`)
			WHERE `$GLOBALS[mysql_prefix]ticket`.`id`= '{$the_ticket_id}' LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);

		$theRow = stripslashes_deep(mysql_fetch_array($result));
		$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

		switch($theRow['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
			default: $severityclass='severity_normal'; break;
			}
		$title = $incident . $theRow['scope'] . $tickno;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $incident . $theRow['scope'] . $tickno . "</DIV>";
		$table = "<TABLE BORDER='0' WIDTH='100%'>\n";		//
		$table .= "<TR CLASS='odd' ><TD ALIGN='left'>Priority:</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
		$table .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
		$table .= "</TD></TR>\n";

		$table .= "<TR CLASS='even' ><TD ALIGN='left'>Protocol:</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
		$table .= "<TR CLASS='odd' ><TD ALIGN='left'>Address:</TD>		<TD ALIGN='left'>{$theRow['street']}";
		$table .= "&nbsp;&nbsp;{$theRow['city']}&nbsp;&nbsp;{$theRow['state']}</TD></TR>\n";
		$table .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>Description:</TD>	<TD ALIGN='left'>" .  nl2br($theRow['tick_descr']) . "</TD></TR>\n";	//	8/12/09
		$end_date = (intval($theRow['problemend'])> 1)? $theRow['problemend']:  (time() - (get_variable('delta_mins')*60));
		$elapsed = my_date_diff($theRow['problemstart'], $end_date);		// 5/13/10
		$table .= "<TR CLASS='odd'><TD ALIGN='left'>Status:</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;({$elapsed})</TD></TR>\n";
		$table .= "<TR CLASS='even'><TD ALIGN='left'>Reported by:</TD>	<TD ALIGN='left'>{$theRow['contact']}";
		$table .= "&nbsp;&nbsp;&nbsp;&nbsp;Phone:&nbsp;&nbsp;" . format_phone ($theRow['phone']) . "</TD></TR>\n";
		$by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
		$table .= "<TR CLASS='odd'><TD ALIGN='left'>Written:</TD>		<TD ALIGN='left'>" . format_date_2($theRow['date']) . $by_str;
		$table .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2($theRow['updated']) . "</TD></TR>\n";
		$table .= (empty($theRow['booked_date']))? "" : "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date_2($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09
		$table .= (!(is_int($theRow['facility'])))? 		"" : "<TR CLASS='odd' ><TD ALIGN='left'>{$incident} at Facility:</TD>		<TD ALIGN='left'>{$theRow['fac_name']}</TD></TR>\n";	// 8/1/09
		$table .= (!(is_int($theRow['rec_facility'])))? 	"" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>{$theRow['rec_fac_name']}</TD></TR>\n";	// 10/6/09
		$table .= (empty($theRow['comments']))? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . nl2br($theRow['comments']) . "</TD></TR>\n";
		$table .= "<TR CLASS='even' ><TD ALIGN='left'>Run Start:</TD><TD ALIGN='left'>" . format_date_2($theRow['problemstart']);
		$table .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date_2($theRow['problemend']) . "&nbsp;&nbsp;&nbsp;&nbsp;Elapsed:&nbsp;&nbsp;{$elapsed}</TD></TR>\n";
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
			$table .= "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
			}
		$table .= "<TR CLASS='odd'><TD ALIGN='left'>Position: </TD><TD ALIGN='left'>" .
				get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type .
				"</TD></TR>\n";																				// 9/13/08
		$table .= "<TR><TD>&nbsp;</TD></TR></TABLE>\n";

		$table .= show_actions ($the_ticket_id, "date" , FALSE, TRUE, 1); // ($the_id, $theSort="date", $links, $display)
		$query = "
			SELECT *, `u`.`user` AS `thename` ,
				`l`.`info` AS `log_info` ,
				`l`.`id` AS `log_id` ,
				`l`.`responder_id` AS `the_unit_id`,
				`l`.`when`  AS `when`
			FROM `$GLOBALS[mysql_prefix]log` `l`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ( `t`.`id` = `l`.`ticket_id` )
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ( `l`.`who` = `u`.`id` )
			LEFT JOIN `$GLOBALS[mysql_prefix]assigns` a ON ( `a`.`ticket_id` = `t`.`id` )
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `r`.`id` = `a`.`responder_id` )
			WHERE `code` >= '{$GLOBALS['LOG_INCIDENT_OPEN']}'
			AND `l`.`ticket_id` ={$the_ticket_id}
			ORDER BY `log_id` ASC";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$evenodd = array ("even", "odd");
		$i = 0;
		$table .= "<TABLE ALIGN='left' CELLSPACING = 1 border=0 WIDTH='100%'>";
		$do_hdr = TRUE;
		$day_part="";
		$last_id = "";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result)) ) 	{
			if ($row['log_id'] <> $last_id ) {
				$last_id = $row['log_id'] ;			// dupe preventer
				if ($do_hdr) {
					$table .= "<TR CLASS='odd'><TD>&nbsp;</TD></TR>";
					$table .= "<TR CLASS='even'><TH COLSPAN=99> {$incident} Log</TH></TR>";
					$table .= "<TR CLASS='odd'>
						<TD CLASS='td_label text text_left text_bold'>Date</TD>
						<TD CLASS='td_label text text_left text_bold'>Time</TD>
						<TD CLASS='td_label text text_left text_bold'>Log code</TD>
						<TD CLASS='td_label text text_left text_bold'>" . get_text("Unit") . "/Fac'y</TD>
						<TD CLASS='td_label text text_left text_bold'><bData</TD>
						<TD CLASS='td_label text text_left text_bold'>By</TD>
						<TD CLASS='td_label text text_left text_bold'>From</TD>
						</TR>";
					$do_hdr = FALSE;
					}
			$temp = explode (" ", format_date_2($row['when']));
			$show_day = ($temp[0] == $day_part)? "" : $temp[0] ;
			$day_part = $temp[0];
			$table .= "<TR CLASS = '{$evenodd[($i%2)]}'>
				<TD CLASS='td_data text text_left'>{$show_day}</TD>
				<TD CLASS='td_data text text_left'>&nbsp;{$temp[1]}&nbsp;</TD>
				<TD CLASS='td_data text text_left'>{$types[$row['code']]}</TD>";

				switch ($row['code']){

					case $GLOBALS['LOG_INCIDENT_OPEN'] :
					case $GLOBALS['LOG_INCIDENT_CLOSE'] :
					case $GLOBALS['LOG_INCIDENT_CHANGE'] :
					case $GLOBALS['LOG_INCIDENT_DELETE'] :
						$table .= "<TD></TD><TD></TD>";
						break;

					case $GLOBALS['LOG_ACTION_ADD'] :
					case $GLOBALS['LOG_ACTION_DELETE'] :
						$act_str = (array_key_exists($row['log_info'], $actions))? $actions[$row['log_info']] : "[{$row['log_info']}]";
						$table .= "<TD></TD><TD CLASS='td_data text text_left'>{$act_str}</TD>";
						break;

					case $GLOBALS['LOG_PATIENT_ADD'] :
					case $GLOBALS['LOG_PATIENT_DELETE'] :
						$pat_str = (array_key_exists($row['log_info'], $patients))? $patients[$row['log_info']] : "[{$row['log_info']}]";
						$table .= "<TD></TD><TD CLASS='td_data text text_left'>{$pat_str}</TD>";
						break;


					case $GLOBALS['LOG_UNIT_STATUS'] :
					case $GLOBALS['LOG_UNIT_COMPLETE'] :
					case $GLOBALS['LOG_UNIT_CHANGE'] :
						$the_unit = array_key_exists($row['the_unit_id'], $unit_names) ? $unit_names[$row['the_unit_id']] : "?? {$row['the_unit_id']}" ;
						$the_status = array_key_exists($row['log_info'], $un_status) ? $un_status[$row['log_info']] : "?? {$row['the_unit_id']}" ;
						$table .= "<TD CLASS='td_data text text_left'>{$the_unit}</TD><TD CLASS='td_data text text_left'>{$the_status}</TD>";
						break;

					case $GLOBALS['LOG_CALL_DISP'] :
					case $GLOBALS['LOG_CALL_RESP'] :
					case $GLOBALS['LOG_CALL_ONSCN'] :
					case $GLOBALS['LOG_CALL_CLR'] :
					case $GLOBALS['LOG_CALL_RESET'] :
						$the_unit = array_key_exists($row['the_unit_id'], $unit_names) ? $unit_names[$row['the_unit_id']] : "?? {$row['the_unit_id']}" ;
						$table .= "<TD CLASS='td_data text text_left'>{$the_unit}</TD><TD></TD>";
						break;

					case $GLOBALS['LOG_CALL_REC_FAC_SET'] :
					case $GLOBALS['LOG_CALL_REC_FAC_CHANGE'] :
					case $GLOBALS['LOG_CALL_REC_FAC_UNSET'] :
					case $GLOBALS['LOG_CALL_REC_FAC_CLEAR'] :
					case $GLOBALS['LOG_FACILITY_INCIDENT_OPEN'] :
					case $GLOBALS['LOG_FACILITY_INCIDENT_CLOSE'] :
					case $GLOBALS['LOG_FACILITY_INCIDENT_CHANGE'] :
					case $GLOBALS['LOG_FACILITY_DISP'] :
					case $GLOBALS['LOG_FACILITY_RESP'] :
					case $GLOBALS['LOG_FACILITY_ONSCN'] :
					case $GLOBALS['LOG_FACILITY_CLR'] :
					case $GLOBALS['LOG_FACILITY_RESET'] :
						$the_facy = array_key_exists($row['facility'], $facilities) ? $facilities[$row['facility']] : "?? {$row['facility']}" ;

						$table .= "<TD CLASS='td_data text text_left'>$the_facy</TD><TD></TD>";
						break;

					default:
					    $table .= "<TD CLASS='td_data text text_left'>ERROR {$row['code']} : {$row['log_id']} </TD><TD></TD>";
					}		// end switch()
				$table .= "
					<TD CLASS='td_data text text_left'>{$row['thename']}</TD>
					<TD CLASS='td_data text text_left'>{$row['from']}</TD>";
				$table .= "</TR>\n";
				$i++;
				}
			}				// end while()
		$table .= "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
		$table .= "</TABLE>";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		} 					// end function do_inc_log_report()

// ================================================== AFTER-ACTION REPORT =========================================

	function do_aa_report($date_in, $func_in) {				// after action report $frm_date, $mode as params - 9/27/10
		global $types, $incident, $disposition;
		global $theWidth, $doprint;
		global $startdate, $enddate;

		require_once('../incs/functions_major.inc.php');		// 7/28/10
		$table = "";
		$from_to = date_range($date_in,$func_in);			// get date range as array
		$where = " WHERE `problemstart` >= '{$from_to[0]}' AND `problemstart` < '{$from_to[1]}'";
		$to_str = ($func_in=="dr")? "": " to {$from_to[3]} " . substr($from_to[1] ,0 , 4) ;
		$title = "After Action Report - " . mysql_affected_rows() . " Incidents: " . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>After Action Report - " . mysql_affected_rows() . " Incidents: " . $from_to[2] . $to_str .  "</DIV>";
		$query = "SELECT *,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`booked_date` AS `booked_date`,
			`date` AS `date`,
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS updated,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
			`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
			`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
			`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
			`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
			`rf`.`lat` AS `rf_lat`,
			`rf`.`lng` AS `rf_lng`,
			`rf`.`name` AS `rec_fac_name`,
			`rf`.`street` AS `rec_fac_street`,
			`rf`.`city` AS `rec_fac_city`,
			`rf`.`state` AS `rec_fac_state`,
			`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`)
			{$where} ORDER BY `SEVERITY` ASC, `problemstart` ASC";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		if (mysql_affected_rows()==0) {
			$title = "After Action Report - 0 Incidents: " . $from_to[2] . $to_str;
			$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>After Action Report - " . mysql_affected_rows() . " Incidents: " . $from_to[2] . $to_str .  "</DIV>";
			$table .= "<SPAN style='text-align: center; width: 100%; display: inline-block;'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></SPAN>";
			} else {
			$numrows = mysql_num_rows($result);
			$title = "After Action Report - " . $numrows . " Incidents: " . $from_to[2] . $to_str;
			$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>After Action Report - " . mysql_affected_rows() . " Incidents: " . $from_to[2] . $to_str .  "</DIV>";
			$page_num = 1;
			while ($row_ticket = stripslashes_deep(mysql_fetch_array($result))){
				$table .= do_ticket_wm($row_ticket, "98%", FALSE, FALSE);	//	2/4/13
				$table .= "<BR />";
				$table .= "<p class='page'>Page " . $page_num . " of " . $numrows . "</p>";
				$page_num++;
				}			// end while ()
			}		// end if/else
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}			// end function

// ================================= INCIDENT MANAGEMENT REPORT ================================= 10/4/10
	function my_stripslashes_deep($value) {
		if (is_array($value))	{$value = array_map('my_stripslashes_deep', $value);}
		else 					{$value = stripslashes($value); }
		return str_replace ( "'", "&#39;", $value  );
		}

	$logs = array();

	function do_im_report($date_in, $func_in) {				// incident mgmt report $frm_date, $mode as params - 9/27/10
		global $types, $tick_array,$deltas, $counts, $severities, $units_str, $evenodd, $logs, $types ;
		global $types, $incident, $disposition;
		global $theWidth, $doprint;
		global $startdate, $enddate;

		$tick_array = array(0);
		$deltas = array(0, 0, 0, 0);		// normal, medium, high, total
		$counts = array(0, 0, 0, 0);		//
		$severities = array ("", "M", "H");	// severity symbols

		$from_to = date_range($date_in,$func_in);			// get date range as array
		$where = " WHERE `problemstart` >= '{$from_to[0]}' AND `problemstart` < '{$from_to[1]}'";
		
			function do_print($row_in) {
				global $today, $today_ref, $line_ctr, $units_str, $severities, $evenodd;
				global $theWidth;
				$theReturn = "";
//																			5/31/2013
					if (empty($today)) {
						$today_ref = date("z", strtotime($row_in['problemstart']));
						$today = substr( format_date_2($row_in['problemstart']), 0, 7);
						}
					else {
						if (!($today_ref == (date("z", strtotime($row_in['problemstart']))))) {				// date change?
							$today_ref = date("z", strtotime($row_in['problemstart']));
							$today = substr( format_date_2($row_in['problemstart']), 0, 7);
							}
						}

				$def_city = get_variable('def_city');
				$def_st = get_variable('def_st');

				$theReturn .= "<TR CLASS = '{$evenodd[$line_ctr%2]}' style='width: 100%;' onClick = 'open_tick_window(" . $row_in['tick_id'] . ");'>\n";		
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left'><DIV style='width: 50px;;'>{$today}</DIV></TD>\n";							//		Date -
				
				$scope = $row_in['tick_scope'];
				$scope_sh = shorten($row_in['tick_scope'], 30);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$scope}');\" onmouseout='UnTip();'>{$scope}</TD>\n";					//		Ticket Name

				$problemstart = format_date_2($row_in['problemstart']);
				$problemstart_sh = short_ts($problemstart);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$problemstart}');\" onmouseout='UnTip();'><DIV style='width: 50px;;'>{$problemstart_sh}</DIV></TD>\n";						//		start

				$problemend = format_date_2($row_in['problemend']);
				$problemend_sh = short_ts($problemend);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$problemend}');\" onmouseout='UnTip();'><DIV style='width: 50px;;'>{$problemend_sh}</DIV></TD>\n";						//		end

				$elapsed =(((intval( $row_in['problemstart'])>0) && (intval ($row_in['problemend'])>0)))? my_date_diff($row_in['problemstart'], $row_in['problemend']) : "na";
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left'>{$elapsed}</TD>\n";							//		Ending time

				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' ALIGN='center'><DIV style='width: 50px;;'>{$severities[$row_in['severity']]}</DIV></TD>\n";

				$type = $row_in['inc_type_name'];
				$type_sh = shorten($type, 30);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$type}');\" onmouseout='UnTip();'>{$type_sh}</TD>\n";					//		Call type

				$comment = $row_in['comments'];
				$short_comment = shorten ( $row_in['comments'] , 128);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$comment}');\" onMouseout='UnTip();'>{$short_comment}</TD>\n";			//		Comments/Disposition

				$facility = $row_in['facy_name'];
				$facility_sh = shorten($row_in['facy_name'], 20);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$facility}');\" onmouseout='UnTip();'><DIV style='min-width: 50px;'>{$facility_sh}</DIV></TD>\n";			//		Facility

				$city = ($row_in['tick_city']==$def_city)? 	"": ", {$row_in['tick_city']}" ;
				$st = ($row_in['tick_state']==$def_st)? 	"": ", {$row_in['tick_state']}";
				$addr = "{$row_in['tick_street']}{$city}{$st}";
				$addr_sh = shorten($row_in['tick_street'] . $city . $st, 30);

				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$addr}');\" onMouseout='UnTip();'>{$addr_sh}</TD>\n";					//		Street addr
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left'><DIV style='width: 250px;'>{$units_str}</DIV></TD>\n";						//		Units responding
				
				$sssign_comment = (array_key_exists('assign_comments', $row_in) && $row_in['assign_comments'] != "" && $row_in['assign_comments'] != "New") ? $row_in['assign_comments'] : "&nbsp;";
				$short_assigns_comment = shorten ( $sssign_comment , 128);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left'><DIV style='width: 100px;'>{$short_assigns_comment}</DIV></TD>\n";						//		Assignment comments
				$theReturn .= "</TR>\n\n";
				$line_ctr++;
				return $theReturn;
				}		// end function do print()

			function do_stats($in_row) {		//
				global $deltas, $counts;
				if ((intval( strtotime($in_row['problemstart']))>0) && (intval (strtotime($in_row['problemend']))>0)) {
					$deltas[$in_row['severity']]+= (strtotime($in_row['problemend']) - strtotime($in_row['problemstart']));
					$deltas[3] 					+= (strtotime($in_row['problemend']) - strtotime($in_row['problemstart']));
					}
				$counts[$in_row['severity']]++;
				$counts[3]++;
				}		// end function do stats()
																					// 12/7/10
			function do_print_log($ary_in) {		//     ["code"]=> string(1) "3" ["info"]=>  string(14) "test test test"  ["when"]=>   string(10) "1302117158"
				global $today, $today_ref, $line_ctr,$evenodd, $types ;
				global $theWidth;

				$theReturn = "<TR CLASS = '{$evenodd[$line_ctr%2]}' STYLE='width: 100%;>\n";
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left'>{$today}</TD>\n";							//		Date -

				$when = format_date_2($ary_in['when']);
				$when_sh = short_ts($when);
				$theReturn .= "<TD CLASS='plain_list text text_normal text_left' onMouseover=\"Tip('{$when}');\" onmouseout='UnTip();'>{$when_sh}</TD>\n";						//		start
				$theReturn .= "<TD CLASS='plain_list text text_normal text_center' style='background-color: white; color: black;' COLSPAN=11>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";							//		end	Ending time
				$theReturn .= "<I>Log entry:</I>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";					//		Call type
				$info = $ary_in['info'];
				$sh_info = shorten ( $ary_in['info'] , 128);
				$theReturn .= "Text: {$sh_info}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$theReturn .= "By: {$ary_in['user']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$theReturn .= "</TD>\n";
				$theReturn .= "</TR>\n\n";
				$line_ctr++;
				return $theReturn;
				}		// end function do print_log()

																			// populate global logs array
		$where_l = str_replace ("problemstart",  "when", $where);		// log version - 7/22/11
		$query = "SELECT `l`.`code`,
			`l`.`info` AS `info`,
			`l`.`when` AS `when`,
			`u`.`user`, `u`.`info` AS `user_info`
			FROM `$GLOBALS[mysql_prefix]log` `l`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON (`l`.who = u.id)
			{$where_l}
			AND (`code` = {$GLOBALS['LOG_COMMENT']})
			ORDER BY `when` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($logs, $row);
			}
		unset ($result);

		function check_logs($in_time) {						//  prints qualifying log entries
			global $logs;
			$theReturn = "";
			while ((!(empty($logs))) && ($logs[0]['when']<= $in_time ))	{
				$theReturn .= do_print_log ($logs[0]);
				array_shift ($logs);		// remove 1st entry
				}
			return $theReturn;
			}		// end function check_logs()

		$query = "SELECT *,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`a`.`id` AS `assign_id` ,
			`a`.`comments` AS `assign_comments`,
			`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
			`t`.`id` AS `tick_id`,
			`t`.`description` AS `tick_descr`,
			`t`.`status` AS `tick_status`,
			`t`.`street` AS `tick_street`,
			`t`.`city` AS `tick_city`,
			`t`.`state` AS `tick_state`,
			`r`.`id` AS `unit_id`,
			`r`.`name` AS `unit_name`,
			`r`.`type` AS `unit_type`,
			`f`.`name` AS `facy_name`,
			`i`.`type` AS `inc_type_name`,
			`a`.`as_of` AS `assign_as_of`
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`a`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types`	 `i` ON (`t`.`in_types_id` = `i`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`a`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`a`.`facility_id` = `f`.`id`)
			{$where}
			AND `t`.`status` <> '{$GLOBALS['STATUS_RESERVED']}'
			ORDER BY `problemstart` ASC";
			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$to_str = ($func_in=="dr")? "": " to {$from_to[3]} " . substr($from_to[1] ,0 , 4) ;
		$title = $incident . " Management Report - " . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $title . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$table .= "<thead><TR style='width: 100%;'>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Date</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Ticket</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Opened</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Closed</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Elapsed</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Severity</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Call type</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Comments/{$disposition}</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Facility</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>Address</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" .  get_text("Unit") . " responding</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" .  get_text("Assigns") . " " . get_text("Comments") . "</TH>";
		$table .= "</TR></thead><tbody>";
		$num_incs = mysql_num_rows ($result);
		if ($num_incs == 0) {												// empty?
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .= "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Incident Summary</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			} else {
			$units_str = "";
			$i=0;
			$today = $today_ref = "";
			$buffer = "";
			$sep = ", ";
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {					// major while ()
				array_push ($tick_array, $row['tick_id']);									// stack them up
				if (empty($buffer)) {											// first time
					$buffer = $row;
					$units_str = $row['unit_name'];
					} else {		// not first time
					if ($row['tick_id'] == $buffer['tick_id']) {
						$units_str .= $sep . $row['unit_name'] ;		// no change, collect unit names
						} else {
						$table .= check_logs($buffer['problemstart']) ;				// problemstart integer
						$table .= do_print($buffer);		// print from buffer
						do_stats($buffer);
						$buffer = $row;
						$units_str = $row['unit_name'];
						}
					}		// end if/else
				}		// end while(

			$table .= check_logs(time()) ;				// everything remaining
			$table .= do_print($buffer);					// print from buffer
			do_stats($buffer);
			}		// end else{}

		$tick_array2 = array_unique ($tick_array );		// delete dupes
		$tick_array3 = array_values ($tick_array2 );	// compress result
		$sep = $tick_str = "";
		for ($i=0; $i< count($tick_array3); $i++ ) {
			$tick_str .= $sep . $tick_array3[$i];
			$sep = ",";
			}

		if($num_incs != 0) {
			$query = "SELECT *,
				`problemstart` AS `problemstart`,
				`problemend` AS `problemend`,
				`u`.`user` AS `theuser`,
				NULL AS `unit_name`,
				`t`.`scope` AS `tick_scope`,
				`t`.`id` AS `tick_id`,
				`t`.`description` AS `tick_descr`,
				`t`.`status` AS `tick_status`,
				`t`.`street` AS `tick_street`,
				`t`.`city` AS `tick_city`,
				`t`.`state` AS `tick_state`,
				`f`.`name` AS `facy_name`,
				`i`.`type` AS `inc_type_name`
				FROM `$GLOBALS[mysql_prefix]ticket` `t`
				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i` ON (`t`.`in_types_id` = `i`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`t`.`_by` = `u`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`t`.`facility` = `f`.`id`)
				{$where}
				AND `t`.`id` NOT IN ({$tick_str})
				AND `t`.`status` <> '{$GLOBALS['STATUS_RESERVED']}'
				ORDER BY `problemstart` ASC";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$table .= "<TR style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>Not dispatched</B></TD></TR>";
			if (mysql_num_rows($result)==0) {
				$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>none</B></TD></TR>";
				} else {
				$units_str = "";
				while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// incidents not dispatched
					$table .= do_print($row);
					$table .= do_stats($row) ;
					}
				}
			}
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$secondTable = "";
		
		if ($counts[3]>0) {						// any stats?
			$secondTable .= "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
			$secondTable .= "<TR style='width: 100%; height: 50px;'><TD CLASS='plain_list text text_normal text_center' COLSPAN=99 ALIGN='center' style='vertical-align: middle;'><B>Mean incident close times by severity:&nbsp;&nbsp;&nbsp;";
			for ($i = 0; $i<3; $i++) {					// each severity level
				if ($counts[$i]>0) {
					$mean = round($deltas[$i] / $counts[$i]);
					$secondTable .= "<B>" . ucfirst(get_severity($i)) ."</B> ({$counts[$i]}): ". my_date_diff(0, $mean) . ",&nbsp;&nbsp;&nbsp;&nbsp;";
					}
				}

				$mean = round($deltas[3] / $counts[3]);		// overall
				$secondTable .= "<B>Overall</B>  ({$counts[3]}): ". my_date_diff(0, $mean);
			$secondTable .= "</B></TD></TR></TABLE>";
			}
		if($num_incs == 0) {$secondTable = "";}
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[2] = $secondTable;
		$ret_arr[6] = $title;
		return $ret_arr;
		return;
		}		// end function do_im_report()
		
// ================================= ORGANISATION BILLING REPORT =================================
		
	function do_org_report($date_in, $func_in) {
		global $nature, $disposition, $patient, $incident, $incidents, $organisation;
		global $evenodd, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$from_to = date_range($date_in,$func_in);	// get date range as array		
		
		$titles = array ();
		$titles['dr'] = get_text("Billing Report") . " - Daily Report - ";
		$titles['cm'] = get_text("Billing Report") . " - Current Month-to-date - ";
		$titles['lm'] = get_text("Billing Report") . " - Last Month - ";
		$titles['cw'] = get_text("Billing Report") . " - Current Week-to-date - ";
		$titles['lw'] = get_text("Billing Report") . " - Last Week - ";
		$titles['cy'] = get_text("Billing Report") . " - Current Year-to-date - ";
		$titles['ly'] = get_text("Billing Report") . " - Last Year - ";
		$titles['s'] = get_text("Billing Report") . " - Specific Dates - ";
		
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$table .=  "<thead><TR style='width: 100%;'>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Organisation") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Requester") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Date") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Incident") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Responder") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Description") . "</TH>";				
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("From Address") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("To Address") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Miles") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Comments") . "</TH></TR></thead><tbody>";
		$where = ($organisation != 0) ? "AND `u`.`org` = '" . $organisation . "'" : "";
		$where2 = " AND `request_date` >= '" . $from_to[0] . "' AND `request_date` < '" . $from_to[1] . "'";
		
		$query = "SELECT *, 
				`r`.`id` AS `request_id`,
				`r`.`ticket_id` AS `r_tick_id`,
				`t`.`id` AS `tick_id`,
				`r`.`status` AS `req_status`,
				`t`.`status` AS `tick_status`,
				`r`.`phone` AS `req_phone`,
				`t`.`phone` AS `tick_phone`,
				`r`.`street` AS `req_street`,
				`r`.`city` AS `req_city`,
				`r`.`postcode` AS `req_postcode`,
				`r`.`state` AS `req_state`,
				`r`.`to_address` AS `req_to_address`,
				`r`.`pickup` AS `req_pickup`,
				`r`.`arrival` AS `req_arrival`,
				`r`.`description` AS `req_description`,
				`r`.`scope` AS `req_scope`,
				`t`.`street` AS `tick_street`,
				`t`.`city` AS `tick_city`,
				`t`.`state` AS `tick_state`,
				`t`.`to_address` AS `tick_to_address`,
				`t`.`description` AS `tick_description`,
				`t`.`comments` AS `tick_comments`,
				`t`.`scope` AS `tick_scope`,
				`a`.`id` AS `assigns_id`,
				`a`.`start_miles` AS `start_miles`,
				`a`.`end_miles` AS `end_miles`,
				`r`.`rec_facility` AS `recFacility`,
				`r`.`orig_facility` AS `origFacility`,
				`r`.`contact` AS `req_contact`,
				`r`.`lat` AS `r_lat`,
				`r`.`lng` AS `r_lng`,
				`t`.`lat` AS `t_lat`,
				`t`.`lng` AS `t_lng`,		
				`r`.`request_date` AS `request_date`,
				`re`.`handle` AS `responder`,
				`tentative_date` AS `tentative_date`,		
				`accepted_date` AS `accepted_date`,
				`declined_date` AS `declined_date`,		
				`resourced_date` AS `resourced_date`,
				`completed_date` AS `completed_date`,	
				`closed` AS `closed`,
				`_on` AS `_on`,
				`r`.`_by` AS `r_by`,
				`t`.`_by` AS `t_by`,
				`u`.`user` AS `requester_name`,
				`u`.`org` AS `org`,
				`o`.`name` AS `organisation`,
				`a`.`dispatched` AS `dispatched`,
				`a`.`clear` AS `clear` 
				FROM `$GLOBALS[mysql_prefix]requests` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON `r`.`requester`=`u`.`id`
				LEFT JOIN `$GLOBALS[mysql_prefix]organisations` `o` ON `u`.`org`=`o`.`id` 
				LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `re` ON `a`.`responder_id`=`re`.`id` 	
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `r`.`ticket_id`=`t`.`id` 			
				WHERE `r`.`status` = 'Closed' " . $where . $where2 . " ORDER BY `organisation`, `requester_name`, `request_date` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$i = 1;
		$lastOrganisation = "";
		$lastRequester = "";
		if (mysql_num_rows($result)==0) {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .=  "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Organisation Billing Report</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";
			} else {
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// incidents not dispatched
				if($row['organisation'] == $lastOrganisation) {
					$organisation = "";
					} elseif($row['organisation'] != "" && $row['organisation'] != $lastOrganisation) {
					$lastOrganisation = $organisation = $row['organisation'];
					} else {
					$organisation = "None Set";
					}
				if($row['requester_name'] == $lastRequester) {
					$requester = "";
					} elseif($row['requester_name'] != "" && $row['requester_name'] != $lastRequester) {
					$lastRequester = $requester = $row['requester_name'];
					} else {
					$requester = "None Set";
					}
				$address = $row['req_street'] . ", " . $row['req_postcode'] . " " . $row['req_city'] . " " . $row['req_state'];
				if($row['miles'] == "") {
					if($row['end_miles'] != "" && $row['start_miles'] != "") {
						$miles = round($row['end_miles'] - $row['start_miles'], 0);
						} else {
						$miles = "N/A";
						}
					} else {
					$miles = round($row['miles'], 0);
					}
//				$miles = ($row['miles'] == "") ? round($row['end_miles'] - $row['start_miles'], 0): round($row['miles'], 0);
				$toaddress = ($row['req_to_address'] != "") ? $row['req_to_address'] : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$organisation = ($row['organisation'] != "") ? $row['organisation'] : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$scope = ($row['req_scope'] != "") ? $row['req_scope'] : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$responder = ($row['responder'] != "") ? $row['responder'] : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$description = ($row['req_description'] != "" && $row['req_description'] != 0) ? $row['req_description'] : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $organisation . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $requester . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . date ('D, M j', strtotime($row['request_date'])) . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $scope . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . $responder . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 20%;'>" . $description . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $address . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $toaddress . "</TD>";
				$start = $address;
				$end = $row['req_to_address'];
				$miles = ($miles == 0 || $miles == "") ? round(get_routeDistance($start, $end), 0) : round($miles, 0);
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 2%;'>" . $miles . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 10%;'>" . $row['tick_comments'] . "</TD></TR>";
				$i++;
				}
			}
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}			// end function
		
	function do_reg_report($date_in, $func_in) {
		global $nature, $disposition, $patient, $incident, $incidents, $organisation;
		global $evenodd, $types;
		global $theWidth, $doprint;
		global $startdate, $enddate;
		
		$from_to = date_range($date_in,$func_in);	// get date range as array		
		
		$titles = array ();
		$titles['dr'] = get_text("Regions Report") . " - Daily Report - ";
		$titles['cm'] = get_text("Regions Report") . " - Current Month-to-date - ";
		$titles['lm'] = get_text("Regions Report") . " - Last Month - ";
		$titles['cw'] = get_text("Regions Report") . " - Current Week-to-date - ";
		$titles['lw'] = get_text("Regions Report") . " - Last Week - ";
		$titles['cy'] = get_text("Regions Report") . " - Current Year-to-date - ";
		$titles['ly'] = get_text("Regions Report") . " - Last Year - ";
		$titles['s'] = get_text("Regions Report") . " - Specific Dates - ";
		
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		$title = $titles[$func_in] . $from_to[2] . $to_str;
		$heading = "<DIV CLASS='heading text_big text_bold text_center' style='width: 98%;'>" . $titles[$func_in] . $from_to[2] . $to_str . "</DIV>";
		if(!$doprint) {
			$table = "<TABLE id='reportstable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
			} else {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			}
		$table .=  "<thead><TR style='width: 100%;'>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Region") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Incident") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Assigns") . "</TH>";		
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Closed on") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Dispatched") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("On Scene") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Clear") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Responder") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Resp Regions") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Description") . "</TH>";				
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("From Address") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("To Address") . "</TH>";
		$table .= "<TH CLASS='plain_listheader text text_left'>" . get_text("Comments") . "</TH></TR></thead><tbody>";
		$where = " AND `problemend` >= '" . $from_to[0] . "' AND `problemend` < '" . $from_to[1] . "'";
		
		$query = "SELECT *, 
				`t`.`id` AS `tick_id`,
				`t`.`status` AS `tick_status`,
				`t`.`phone` AS `tick_phone`,
				`t`.`street` AS `tick_street`,
				`t`.`city` AS `tick_city`,
				`t`.`state` AS `tick_state`,
				`t`.`to_address` AS `tick_to_address`,
				`t`.`description` AS `tick_description`,
				`t`.`comments` AS `tick_comments`,
				`t`.`scope` AS `tick_scope`,
				`t`.`problemstart` AS `problemstart`,
				`t`.`problemend` AS `problemend`,
				`a`.`id` AS `assigns_id`,
				`a`.`start_miles` AS `start_miles`,
				`a`.`end_miles` AS `end_miles`,
				`r`.`lat` AS `r_lat`,
				`r`.`lng` AS `r_lng`,
				`t`.`lat` AS `t_lat`,
				`t`.`lng` AS `t_lng`,		
				`r`.`handle` AS `responder`,
				`r`.`id` AS `resp_id`,
				`t`.`updated` AS `tick_updated`,
				`t`.`_by` AS `t_by`,
				`a`.`dispatched` AS `dispatched`,
				`rg`.`group_name` AS `region_name`,
				`rg`.`id` AS `region`,
				`a`.`clear` AS `clear` 
				FROM `$GLOBALS[mysql_prefix]ticket` `t`
				LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `t`.`id` = `a`.`ticket_id` 
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON `a`.`responder_id` = `r`.`id` 
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `al` ON `t`.`id`=`al`.`resource_id` AND `al`.`type`=1
				LEFT JOIN `$GLOBALS[mysql_prefix]region` `rg` ON `al`.`group`=`rg`.`id`
				WHERE `t`.`status`='{$GLOBALS['STATUS_CLOSED']}' " . $where . " GROUP BY `assigns_id` ORDER BY `region`, `assigns_id`, `problemstart` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$i = 1;
		$lastRegion = "";
		$lastTicket = "";
		if (mysql_num_rows($result)==0) {
			$table = "<TABLE id='reportstable' STYLE='width: 100%;'>";
			$table .=  "<thead><TR style='width: 100%;'><TD CLASS='plain_listheader text text_center text_biggest' COLSPAN=99>Regions Report</TD></TR></thead><tbody>";
			$table .= "<TR CLASS='even' style='width: 100%;'><TD COLSPAN=99 ALIGN='center'><B>----------&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No data for this period&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----------</B></TD></TR>";

			} else {
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// incidents not dispatched
				$address = $row['tick_street'] . ", " . $row['tick_city'] . " " . $row['tick_state'];
				if($row['region_name'] == $lastRegion) {
					$region = "";
					} elseif($row['region_name'] != "" && $row['region_name'] != $lastRegion) {
					$lastRegion = $region = $row['region_name'];
					} else {
					$region = "None Set";
					}
				if($row['tick_scope'] == $lastTicket) {
					$ticketscope = "";
					} elseif($row['tick_scope'] != "" && $row['tick_scope'] != $lastTicket) {
					$lastTicket = $ticketscope = $row['tick_scope'];
					} else {
					$ticketscope = "None Set";
					}
				$responder = ($row['responder'] != "") ? $row['responder'] : "UNK";
				$table .= "<TR CLASS='" . $evenodd[$i%2] . "' style='width: 100%;'>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $region . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $ticketscope . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_center' style='width: 3%;'>" . get_assignscount($row['tick_id']) . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . date ('D, M j', strtotime($row['problemend'])) . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . date ('D, M j', strtotime($row['dispatched'])) . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . date ('D, M j', strtotime($row['on_scene'])) . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . date ('D, M j', strtotime($row['clear'])) . "</TD>";
				if($row['resp_id'] != "") {
					$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . $responder . "</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 5%;'>" . get_responder_regions($row['resp_id']) . "</TD>";
					} else {
					$table .= "<TD CLASS='plain_list text text_normal text_center' style='width: 5%;'>N/A</TD>";
					$table .= "<TD CLASS='plain_list text text_normal text_center' style='width: 5%;'>N/A</TD>";	
					}
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 20%;'>" . $row['tick_description'] . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $address . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left'>" . $row['tick_to_address'] . "</TD>";
				$table .= "<TD CLASS='plain_list text text_normal text_left' style='width: 10%;'>" . $row['tick_comments'] . "</TD></TR>";
				$i++;
				}
			}
		$table .= "</tbody>";
		$table .= "</TABLE>\n";
		$ret_arr[0] = $heading;
		$ret_arr[1] = $table;
		$ret_arr[6] = $title;
		return $ret_arr;
		}			// end function

// ================================= end incident management report =================================
	switch ($report) {
		case "m":								// 10/2/10 -->
		    $output = do_im_report ($date, $func) ;
//			print json_encode($output);
		    break;
		case "a":								// 9/27/10 -->
		    $output = do_aa_report ($date, $func) ;
//			print json_encode($output);
		    break;
		case "d":								// 1/27/09 -->
		    $output = do_dispreport ($date, $func);
//			print json_encode($output);
		    break;
		case "u":
		    $output = do_unitreport ($date, $func) ;
//			print json_encode($output);
		    break;
		case "c":
		    $output = do_unitcommsreport ($date, $func) ;
//			print json_encode($output);
		    break;
		case "f":
		    $output = do_facilityreport ($date, $func) ;
//			print json_encode($output);
		    break;
		case "s":
			$output = do_sta_report ($date, $func);
//			print json_encode($output);
		    break;
		case "i":
		    $output = do_inc_report ($date, $func);
//			print json_encode($output);
		    break;
		case "l":
		    $output = do_inc_log_report ($tick_sel);
//			print json_encode($output);
		    break;
		case "b":
		    $output = do_org_report ($date, $func);
//			print json_encode($output);
		    break;
		case "r":
		    $output = do_reg_report ($date, $func);
//			print json_encode($output);
		    break;
		default:
		    echo "error error error " . __LINE__ . "<BR />";
		    break;
		}

$header = ($dohtml) ? $htmlheader : "";
$footer = ($dohtml) ? $htmlfooter : "";
if($dohtml) {
	print $header;
	foreach($output as $val) {
		print $val;
		}
	print $footer;
	} else {
	print json_encode($output);
	}

exit();