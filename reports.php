<?php
/*
6/6/08 revised to accommodate deleted incident and unit records, these identified by a # at its index value
8/7/08 added ACTION & PATIENT delete types
8/9/08 calculate graphics width
8/15/08	 mysql_fetch_array to mysql_fetch_assoc - performance
8/15/08-1	handle dropped tickets
*/
$asof = "8/9/08";
require_once('./incs/functions.inc.php'); 
require_once('./incs/istest.inc.php'); 
$my_session = do_login(basename(__FILE__));
//dump($my_session);
$img_width  = round(.8*$my_session['scr_width']/3);		//8/9/08
//dump($img_width);
if($istest) {
	if (!empty($_POST)) {dump ($_POST);}
	if (!empty($_GET)) {dump ($_GET);}
	}

//$ionload =  ((isset($_POST) && isset($_POST['frm_group']) && $_POST['frm_group']=='i'))? " inc_onload();": "";

extract($_GET);
extract($_POST);
$evenodd = array ("even", "odd");	// CLASS names for alternating tbl row colors
if (empty($_POST)) {				// default to today
	$frm_date = date('m,d,Y');
	$frm_func = "dr";				// single day report
	$group = "u";
	}
else {
	$group = $_POST['frm_group'];
	}
	
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Reports Module</TITLE>
	<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" 				CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<style type="text/css">
.hovermenu ul{font:bold 13px arial;padding-left:0;margin-left:0;height:20px;}
.hovermenu ul li{ list-style:none; display:inline;}
.hovermenu ul li { padding:2px 0.5em; float:left; color:black; background-color:##DEE3E7; border:2px solid #EFEFEF; width:81px;text-align: center}
.hovermenu ul li:hover{ background-color:#DEE3E7; border-style:outset;text-decoration: underline }
.hovermenu2 ul{font:bold 13px arial;padding-left:0;margin-left:0;height:20px;}
.hovermenu2 ul li{ list-style:none; display:inline;}
.hovermenu2 ul li { padding:2px 0.5em; float:left; color:black; background-color:#DEE3E7; border:2px solid #EFEFEF; width:179px;text-align: center}
.hovermenu2 ul li:hover{ background-color:#DEE3E7; border-style:outset;text-decoration: underline }
</style>

<SCRIPT>
<?php 
	print "//  {$asof}  \n";
?>	
	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	var which='<?php print $group;?>';					// global - which report default
	
	function viewT(id) {			// view ticket
		return;
//		document.T_nav_form.id.value=id;
//		document.T_nav_form.action='main.php';
//		document.T_nav_form.submit();
		}

	function viewU(id) {			// view unit
		return;
//		document.U_nav_form.id.value=id;
//		document.U_nav_form.submit();
		}
	function toUDRnav(date_in) {					// daily report
		document.udr_form.frm_date.value=date_in;	// set date params
		document.udr_form.frm_group.value=which;
		document.udr_form.submit();	
		}

	function do_ugr(instr) {						// select for generic
		document.ugr_form.frm_func.value=instr;
		document.ugr_form.frm_group.value=which;
		document.ugr_form.submit();
		}		// end do_ugr()

	function ck_frames() {		// ck_frames()
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()
		
	</SCRIPT>

	</HEAD>
<BODY onLoad = "ck_frames()">
<?php

	function date_range($dr_date_in, $dr_func_in) {			// returns array of MySQL-formatted dates
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

			
			default:
			    echo " error - error - error " . $dr_func_in;
			}		// end switch ()
		}				// end function date_range()
		
	function date_part($in_date) {						// return date part of date/time string
		$temp = explode (" ", $in_date);
		return $temp[0];
		}		// end function date_part()
	
	function time_part($in_date) {						// "2007-12-02 21:07:30" 
		$temp = explode (" ", $in_date);
		return substr($temp[1], 0, 5);
		}		// end function time_part()

// =================================================== UNIT LOG =========================================		
	
	function do_unitreport($date_in, $func_in) {				// $frm_date, $mode as params
		global $evenodd;
		$from_to = date_range($date_in,$func_in);	// get date range as array

		$incidents = $severity = $unit_names = $status_vals = $users = $unit_status_ids = array();
		
		$query = "SELECT `id`, `scope`, `severity` FROM `$GLOBALS[mysql_prefix]ticket`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$incidents[0]="";

		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$incidents[$temp_row['id']]=$temp_row['scope'];
			$severity[$temp_row['id']]=$temp_row['severity'];
			}
			
//		dump($severity);

		$query = "SELECT `id`, `name`, `un_status_id` FROM `$GLOBALS[mysql_prefix]responder`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$unit_names[0]="TBD";
		while ($temp_row = mysql_fetch_assoc($temp_result)) {
			$unit_names[$temp_row['id']]=$temp_row['name'];
			$unit_status_ids[$temp_row['id']]=$temp_row['un_status_id'];
			}
		
		$query = "SELECT `id`, `status_val` FROM `$GLOBALS[mysql_prefix]un_status`";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$status_vals[0]="TBD";
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
		$titles['dr'] = "Units - Daily Report - ";
		$titles['cm'] = "Units - Current Month-to-date - ";
		$titles['lm'] = "Units - Last Month - ";
		$titles['cw'] = "Units - Current Week-to-date - ";
		$titles['lw'] = "Units - Last Week - ";
		$titles['cy'] = "Units - Current Year-to-date - ";
		$titles['ly'] = "Units - Last Year - ";
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		print "\n<TABLE ALIGN='left' BORDER = 0 WIDTH='800px'>\n<TR CLASS='even' style='height: 24px'>\n";
		print "<TH COLSPAN=99 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";

		$i = 1;	

//		collect status values in use
		$query = "SELECT DISTINCT `info` FROM `$GLOBALS[mysql_prefix]log` WHERE `code` = " . $GLOBALS['LOG_UNIT_STATUS'] . " ORDER BY `info` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i++;

		$caption =  "<TR CLASS = 'odd'><TD></TD><TD ALIGN='center'><U>Unit</U></TD>";
		$curr_unit = "";
		$statuses = array();
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// build header row
			if (!empty($row['info'])){
				$statuses[$row['info']] = "";										// define the entry
				$query = "SELECT `status_val` FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $row['info'] . " LIMIT 1" ;// status type
				$result_val= mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				$row_val = stripslashes_deep(mysql_fetch_assoc($result_val));
				$caption .= "\t<TD ALIGN='CENTER'>&nbsp;&nbsp;" . shorten($row_val['status_val'], 12) . "&nbsp;&nbsp;</TD>\n";
				}
			}
		$caption .=  "<TD ALIGN='center'><U>Incident</U></TD></TR>\n";
		$blank = $statuses;

		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
		
//		$query = "SELECT *, UNIX_TIMESTAMP(`when`) AS `when_num`, `responder_id` AS `unit`, `info` AS `status`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]log`" .  $where . " AND `code` = " . $GLOBALS['LOG_UNIT_STATUS'] . " ORDER BY `when` ASC,`unit` ASC, `incident` ASC, `status` ASC" ;
		$query = "SELECT *, UNIX_TIMESTAMP(`when`) AS `when_num`, `responder_id` AS `unit`, `info` AS `status`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]log`" .  $where . " AND `code` = " . $GLOBALS['LOG_UNIT_STATUS'] . " ORDER BY `unit` ASC, `incident` ASC, `status` ASC, `when` ASC" ;
//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$i = 0;
		if (mysql_affected_rows()>0) {				// main loop - top
			print $caption;
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				if (empty($curr_unit)) {
					$curr_unit = $row['unit'];
					$curr_inc = $row['incident'];
					$curr_date_test = date ('z', $row['when_num']);			// day of year as test value
					$do_date=$row['when_num'];
					}								// populate break item
				if (($row['unit'] == $curr_unit) && ($row['incident'] == $curr_inc ) && (date ('z', $row['when_num']) == $curr_date_test )) {	// same unit and incident, date?
					$statuses[$row['status']] = time_part($row['when']);		// yes, populate the row
					$theIncident_id = $row['incident'];
					}
				else {														// no, flush, initialize and populate
					print "<TR CLASS='" . $evenodd[$i%2] . "'>";
					if (!empty($do_date)) {
						print "<TD>" . date ('D, M j', $do_date) . "</TD>";
						$do_date = "";
						}
					else {
						print "<TD></TD>";
						}
					if(((date ('z', $row['when_num'])) != $curr_date_test)) {		// date change?	
						$do_date=$row['when_num'];
						$curr_date_test = date ('z', $row['when_num']);
						}
					$theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;
//					dump($theUnitName);

					print (array_key_exists($curr_unit, $unit_names))? "<TD onClick = 'viewU(" .$curr_unit . ")'><B>" . $theUnitName . "</B></TD>":	"<TD>[#" . $curr_unit . "]</TD>";

//					print "<TD onClick = 'viewU(" .$curr_unit . ")'><B>" . $theUnitName . "</B></TD>";		// flush
					foreach($statuses as $key => $val) {
						print "<TD ALIGN='center'> $val </TD>";
						}
					if ($row['incident']>0) {				// 6/6/08
						$theIncidentName = (array_key_exists($row['incident'], $incidents))? $incidents[$row['incident']]: "#" . $row['incident'] ;
						$theSeverity = (array_key_exists($row['incident'], $severity))? $severity[$row['incident']]: 0;
//						print "<TD CLASS='" . $priorities[$theSeverity] . "' onClick = 'viewT(" . $row['incident'] . ")'><B>" . shorten($theIncidentName, 20) . "</B></TD>";	// incident 
						print (array_key_exists($row['incident'], $incidents))?	"<TD CLASS='" . $priorities[$theSeverity] . "' onClick = 'viewT(" . $row['incident'] . ")'><B>" . shorten($theIncidentName, 20) . "</B></TD>":	"<TD>#" . $row['incident']. " ??</TD>";


						}
					else {
						print "<TD></TD>";
						}
					print "</TR>\n";
					$statuses = $blank;															// initalize
					$statuses[$row['status']] = date('H:i', $row['when_num']);					// MySQL format		
					$curr_unit = $row['unit'];
					$curr_inc = $row['incident'];
					$i++;
					$theIncident_id = $row['incident'];
			
					}
				}		// end while($row...)		 main loop - bottom

			print "\n<TR CLASS='" . $evenodd[$i%2] . "'>";
			if (!empty($do_date)) {
				print "<TD>" . date ('D, M j', $do_date) . "</TD>";
//				$do_date = "";
				}
			else {
				print "<TD></TD>";
				}
			$theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16):  "#" . $curr_unit ;
			print "<TD onClick = 'viewU(" .$curr_unit . ")'><B>" . $theUnitName . "</B></TD>";		// flush tail-end Charlie
			
			foreach($statuses as $key => $val) {
				print "<TD ALIGN='center'> $val </TD>";
				}
			if ($theIncident_id>0) {
//				dump($theIncident_id);
//				dump(array_key_exists($theIncident_id, $severity)); 	// false
				$theIncidentName = (array_key_exists($theIncident_id, $incidents))? $incidents[$theIncident_id]: "#" . $theIncident_id ;
				$theSeverity = (array_key_exists($theIncident_id, $severity))? $severity[$theIncident_id]: 0;
				
//				print "<TD CLASS='" . $priorities[$severity[$theIncident_id]] . "' onClick = 'viewT(" . $theIncident_id . ")'><B>" . shorten($incidents[$theIncident_id],20) . "</B></TD>";
				print "<TD CLASS='" . $priorities[$theSeverity] . "' onClick = 'viewT(" . $theIncident_id . ")'><B>" . shorten($theIncidentName,20) . "</B></TD>";
				}
			else {
				print "<TD></TD>";
				}				
			print "</TR>\n";
			print "<TR><TD COLSPAN=99 ALIGN='center'><HR COLOR='red' size = 1 width='50%'></TD></TR>";
			}		// end if (mysql_affected_rows()>0)
		else {
			print "\n<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'><br /><I>No Unit data for this period</I><BR /></TD></TR>\n";
			}
		print "<TR><TD ALIGN='center' COLSPAN=99>";
		$m = date("m"); $d = date("d"); $y = date("Y");

		print "</TD></TR>";
		$i++;
		print "</TABLE>\n";
		}		// end function do_unitreport()

// =============================================== STATION LOG  ===========================================		
		
	function do_sta_report($date_in, $func_in) {				// $frm_date, $mode as params
		global $evenodd, $istest;
		$from_to = date_range($date_in,$func_in);	// get date range as array
//		dump ($from_to);
	
		$types = array();
		$types[$GLOBALS['LOG_SIGN_IN']]				="Login";				// 6/26/08
		$types[$GLOBALS['LOG_SIGN_OUT']]			="Logout";
		$types[$GLOBALS['LOG_COMMENT']]				="Comment";		// misc comment
		$types[$GLOBALS['LOG_INCIDENT_OPEN']]		="Incident open";
		$types[$GLOBALS['LOG_INCIDENT_CLOSE']]		="Incident close";
		$types[$GLOBALS['LOG_INCIDENT_CHANGE']]		="Incident change";
		$types[$GLOBALS['LOG_ACTION_ADD']]			="Action added";
		$types[$GLOBALS['LOG_PATIENT_ADD']]			="Patient added";
		$types[$GLOBALS['LOG_ACTION_DELETE']]		="Action deleted";			// 8/7/08
		$types[$GLOBALS['LOG_PATIENT_DELETE']]		="Patient deleted";
		$types[$GLOBALS['LOG_INCIDENT_DELETE']]		="Incident delete";			// 6/26/08
		$types[$GLOBALS['LOG_UNIT_STATUS']]			="Unit status change";
		$types[$GLOBALS['LOG_UNIT_COMPLETE']]		="Unit complete";
		$types[$GLOBALS['LOG_UNIT_CHANGE']]			="Unit change";				// 6/26/08
		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";

		$query = "
			SELECT *, UNIX_TIMESTAMP(`when`) AS `when`, `log`.`id` AS `logid`, t.scope AS `tickname`, `r`.`name` AS `unitname`, `s`.`status_val` AS `theinfo`, `u`.`user` AS `thename` FROM `$GLOBALS[mysql_prefix]log` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON (log.ticket_id = t.id)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON (log.responder_id = r.id)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s ON (log.info = s.id)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON (log.who = u.id)
	 		$where ORDER BY `when` ASC		
			";
//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		
		$titles = array ();
		$titles['dr'] = "Station Daily Report - ";
		$titles['cm'] = "Station Report - Current Month-to-date - ";
		$titles['lm'] = "Station Report - Last Month - ";
		$titles['cy'] = "Station Report - Current Year-to-date - ";
		$titles['ly'] = "Station Report - Last Year - ";
		$titles['cw'] = "Station Report - Current Week-to-date - ";
		$titles['lw'] = "Station Report - Last Week - ";

		$i = 0;
		$curr_date="";
		print "\n<TABLE ALIGN='left' WIDTH='800px' BORDER = 0><TR CLASS='even'>\n";
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		print "<TH COLSPAN=99 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";
		
//		print "<TR CLASS='even'><TH COLSPAN=99 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . " to " . $from_to[3] . "</TH></TR>\n";
		if (mysql_affected_rows()>0) {	
				print "<TR CLASS='odd'>";
				print "<TH>Date</TH>";
				print "<TH>Time</TH>";
				print "<TH>Code</TH>";
				print "<TH>Call</TH>";
				print "<TH>Unit</TH>";
				print "<TH>Info</TH>";
				print "<TH>User</TH>";
				print "<TH>From</TH>";
				if ($istest) {print "<TH>ID</TH>";}
				print "</TR>\n";
			
			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			// main loop - top
				if ($row['code']<20) {
					print "<TR CLASS='" . $evenodd[$i%2] . "'>";
					
					if(!(date("z", $row['when']) == $curr_date))  {								// date change?
						print "<TD>" . date ('D, M j', $row['when']) ."</TD>";
						$curr_date = date("z", $row['when']);
						}
					else {print "<TD></TD>";}
//					$the_ticket = (empty($row['tickname']))? "[#" . $row['ticket_id']. "]" : $row['tickname'] ;

					if (empty($row['tickname'])) {
						$the_ticket = ($row['ticket_id']>0 )? "[#" . $row['ticket_id']. "]" :"";
						}
					else {
						$the_ticket =$row['tickname'] ;
						}
//			$action = (empty($_POST['action'])) ? ( isset( $defaultString ) ? $defaultString : 'default' ) : $_POST['action'];
//			$the_ticket = (empty($row['tickname']))? (($row['ticket_id']>0 )? "[#" . $row['ticket_id']. "]" :"";) : $row['tickname'] ;

					print "<TD>" . date('H:i',$row['when']) . "</TD>";
					print "<TD>" . $types[$row['code']] . "</TD>";
//					print "<TD>" . $row['tickname'] . "</TD>";
					print "<TD>" . $the_ticket . "</TD>";
					print "<TD>" . $row['name'] . "</TD>";
					print "<TD>" . $row['info'] . "</TD>";
					print "<TD>" . $row['user'] . "</TD>";
					print "<TD>" . $row['from'] . "</TD>";
					if ($istest) {print "<TD>" . $row['logid'] . "</TD>";}				
					print "</TR>\n";
					$i++;
					}				
				}		// end while($row = ...)
			print "<TR><TD COLSPAN=99 ALIGN='center'><HR COLOR='red' size = 1 width='50%'></TD></TR>";
			}		// end if (mysql_affected_rows() ...
		else {
			print "<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'><br /><I>No data for this period</I><BR /></TD></TR>\n";
			}
		print "</TABLE>\n";
	
		}		// end function do_sta_report()

// ================================================== INCIDENT SUMMARY =========================================		
		
	function do_inc_report($date_in, $func_in) {				// Incidents summary report - $frm_date, $mode as params
		global $evenodd, $img_width;
		$from_to = date_range($date_in,$func_in);	// get date range as array
//		dump ($from_to);
		$priorities = array("text_black","text_blue","text_red" );
	
		$types = array();
		$types[$GLOBALS['LOG_INCIDENT_OPEN']]		="Incident open";
		$types[$GLOBALS['LOG_INCIDENT_CLOSE']]		="Incident close";
		$types[$GLOBALS['LOG_INCIDENT_CHANGE']]		="Incident change";

		$where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
		$query = "
			SELECT *, UNIX_TIMESTAMP(`when`) AS `when`, t.id AS `tick_id`,t.scope AS `tick_name`, t.severity AS `tick_severity`, `u`.`user` AS `user_name` FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON (log.ticket_id = t.id)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON (log.who = u.id)
			". $where . " AND `code` >= '" . $GLOBALS['LOG_INCIDENT_OPEN'] ."'  AND `code` <= '" . $GLOBALS['LOG_INCIDENT_CLOSE'] . "'
	 		ORDER BY `when` ASC		
			";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//		dump ($query);
		
		$titles = array ();
		$titles['dr'] = "<B>Incidents</B> Daily Report - ";
		$titles['cm'] = "<B>Incidents</B> Report - Current Month-to-date - ";
		$titles['lm'] = "<B>Incidents</B> Report - Last Month - ";
		$titles['cy'] = "<B>Incidents</B> Report - Current Year-to-date - ";
		$titles['ly'] = "<B>Incidents</B> Report - Last Year - ";
		$titles['cw'] = "<B>Incidents</B> Report - Current Week-to-date - ";
		$titles['lw'] = "<B>Incidents</B> Report - Last Week - ";
		
		$i = 0;
		print "\n<TABLE ALIGN='left' BORDER = 0 width=800>\n";	
		$to_str = ($func_in=="dr")? "": " to " . $from_to[3];
		print "<TR CLASS='even'><TH COLSPAN=6 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";
		$curr_date="";
		if (mysql_affected_rows()>0) {	

			print "<TR CLASS='odd'>";
			print "<TH>Date</TH>";
			print "<TH>Time</TH>";
			print "<TH>Code</TH>";
			print "<TH>Incident</TH>";
			print "<TH>User</TH>";
			print "<TH>From</TH>";
			print "</TR>\n";
			$inc_types = array();
			
			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			// 8/15/08 main loop - top
//				dump ($row);
				if ($row['code']<20) {
					if (array_key_exists($row['in_types_id'], $inc_types)) {
						$inc_types[$row['in_types_id']]++;
						}
					else {
						$inc_types[$row['in_types_id']] = 1;
						}
					print "<TR CLASS='" . $evenodd[$i%2] . "'>";
					if(!(date("z", $row['when']) == $curr_date))  {								// date change?
						print "<TD>" . date ('D, M j', $row['when']) ."</TD>";
						$curr_date = date("z", $row['when']);
						}
					else {print "<TD></TD>";}
					print "<TD>" . date('H:i',$row['when']) . "</TD>";
					print "<TD>" . $types[$row['code']] . "</TD>";
					if ($row['ticket_id']>0) {
						$the_ticket = (empty($row['tick_name']))? "[#" . $row['ticket_id'] . "]" : shorten($row['tick_name'],20);	// 8/15/08 -1
						$severity_class = empty($row['tick_severity'])? $priorities[0]: $priorities[$row['tick_severity']];			// accommodate null
						print "<TD TITLE = '" . 
						$row['ticket_id'] . "' CLASS='" . 
						$severity_class . "' onClick = 'viewT(" . 
//						$row['tick_severity'] . "' onClick = 'viewT(" . 
						$row['ticket_id'] . ")'>" . 
						$the_ticket . "</TD>";
						}
					print "<TD>" . $row['user_name'] . "</TD>";
					print "<TD>" . $row['from'] . "</TD>";
					print "</TR>\n";
					$i++;
					}				
				}		// end while($row = ...)
//			dump ($inc_types);
			
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id IN (" . $query . ")";
//		dump ($query2);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			//
//				dump ($row['id']);
				}																// end while($row ...		
//				 		graphics date range in db format and calculated img width
$s_urlstr =  "sever_graph.php?p1=" . 		urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1] . "&p3=" . $img_width);	//8/9/08
$t_urlstr =  "inc_types_graph.php?p1=" . 	urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1] . "&p3=" . $img_width);
$c_urlstr =  "city_graph.php?p1=" . 		urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1] . "&p3=" . $img_width);

?>
</TABLE>
<BR CLEAR='left' />
<TABLE>
<TR><TD COLSPAN=3 ALIGN='center'><br><HR SIZE=1 COLOR='blue' WIDTH='75%'></TD></TR>
<TR VALIGN='bottom'><TD ALIGN='center'>
	<img src="<?php print $s_urlstr;?>" border=0 ID = "sev_img">
	</TD>

	<TD ALIGN='center'>	
	<img src="<?php print $t_urlstr;?>" border=0 ID = "typ_img">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</TD>
	
	<TD ALIGN='center'>
	<img src="<?php print $c_urlstr;?>" border=0 ID = "cit_img">
	</TD>
	</TR>
<?php
			}
		else {
			print "\n<TR CLASS='odd'><TH COLSPAN='99' ALIGN='center'><br /><I>No data for this period!</I><BR /><BR /></TH></TR>\n";
			}
		print "</TABLE>\n";
			
		}		// end function do_inc_report()

// ==================================  end function do_inc_report() =====================================		
	
	$theDate = 	isset($frm_date)? $frm_date :  		date('m,d,Y');		// set defaults
	$theFunc= 	isset($frm_func)? $frm_func :  		"dr";				// daily
	$frm_group = isset($frm_group)? $frm_group: 	"u";				// unit reports

	switch ($frm_group) {
		case "u":
		    do_unitreport ($theDate, $theFunc) ;
		    break;
		case "s":
		    do_sta_report ($theDate, $theFunc);
		    break;
		case "i":
		    do_inc_report ($theDate, $theFunc);		// incidents summary
		    break;
		default:
		    echo "error error error " . __LINE__ . "<BR />";
		    break;
		}
	
	$i=1;
	$checked = array("u" => "", "s" => "", "i" => "");
	$temp = (empty($_POST))? "u":  $_POST['frm_group']; 		// set selector fm last, default is unit
	$checked [$temp] = " CHECKED ";								// copy fm last

?>	
	<BR CLEAR='left' /><BR />
	<TABLE ALIGN='left' CELLSPACING = 2 CELLPADDING = 2  BORDER=0 width='800px'>
	<TR CLASS='even'><TH COLSPAN=99>Other Reports</TH></TR>	
	<TR CLASS='odd'><TD COLSPAN=8 ALIGN='center'><B>
		Unit Log <INPUT TYPE='radio' <?php print $checked['u']; ?> NAME= 'frm_which' onClick ="Javascript: which='u';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Station Log <INPUT TYPE='radio' <?php print $checked['s']; ?> NAME= 'frm_which' onClick ="Javascript: which = 's';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Incident Summary <INPUT TYPE='radio' <?php print $checked['i']; ?> NAME= 'frm_which' onClick ="Javascript: which = 'i';"></B></TD></TR>
	<TR CLASS='even'><TD COLSPAN=99 ALIGN='center'><FONT SIZE="-1"><I>Mouseover for buttons</I></FONT><BR />
		
<?php		

	print "\n<span class='hovermenu' style='background-color:#DEE3E7'><ul>\n";
	print "<nobr><li onClick= \"do_ugr('lw')\">Last Week</li>";
	for ($j = -13; $j < 1; $j++)  {	
		$temp = mktime(0,0,0,date('m'), date('d')+$j, date('Y'));
		print "<LI onClick = \"toUDRnav('" . date ('m,d,Y', $temp) . "')\">";

		print date ("m/d", $temp);
		print "</LI>\n";
		if ($j== -7) {
			print "<BR /><BR /><nobr><li onClick= \"do_ugr('cw')\">This Week</li><nobr>";
			$i++;
			}
		}				// end for ($j...)
		print "</UL></nobr></SPAN>";
?>
	</TD></TR>
	<FORM NAME='udr_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'><!-- daily -->
	<TR CLASS='even' width='100%'><TD ALIGN='center' colspan=99>
	<span class="hovermenu2"><nobr>
	<ul>
	<li onClick= "do_ugr('lm')"><?php print date("M `y", mktime(0, 0, 0, date("m")-1, 15,   date("Y")));?></li>
	<li onClick= "do_ugr('cm')"><?php print date("M `y");?></li>
	<li onClick= "do_ugr('ly')"><?php print date("Y", mktime(0, 0, 0, 1, 1,  date("Y")-1));?></li>
	<li onClick= "do_ugr('cy')"><?php print date("Y", mktime(0, 0, 0, 1, 1,  date("Y")));?></li>
	</ul>
	</nobr>
	</span>
	</TD></TR>

	</TABLE>
	<INPUT TYPE='hidden' NAME='func' VALUE='dr'>
	<INPUT TYPE='hidden' NAME='frm_date' VALUE='<?php print date('m,d,Y'); ?>'>
	<INPUT TYPE='hidden' NAME='frm_group' VALUE='<?php print $group;?>'>
	</FORM>
	<FORM NAME='ugr_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'> <!-- generic, date-driven -->
	<INPUT TYPE='hidden' NAME='frm_func' VALUE='w'>
	<INPUT TYPE='hidden' NAME='frm_date' VALUE='<?php print date('m,d,Y'); ?>'>
	<INPUT TYPE='hidden' NAME='frm_group' VALUE='<?php print $group;?>'>
	</FORM>
	<FORM NAME='T_nav_form' METHOD='get' TARGET = 'main' ACTION = "main.php">
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>
	
	<FORM NAME='U_nav_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
	<INPUT TYPE='hidden' 	NAME='id' VALUE=''>
	<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'>
	<INPUT TYPE='hidden' 	NAME='view' VALUE='true'>
	</FORM>
	
	<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>
</BODY></HTML>

<?php  /*
$GLOBALS['LOG_SIGN_IN']				= 1;
$GLOBALS['LOG_SIGN_OUT']			= 2;
$GLOBALS['LOG_COMMENT']				= 3;		// misc comment
$GLOBALS['LOG_INCIDENT_OPEN']		=10;
$GLOBALS['LOG_INCIDENT_CLOSE']		=11;
$GLOBALS['LOG_INCIDENT_CHANGE']		=12;
$GLOBALS['LOG_ACTION_ADD']			=13;
$GLOBALS['LOG_PATIENT_ADD']			=14;
$GLOBALS['LOG_INCIDENT_DELETE']		=15;		// added 6/4/08 
$GLOBALS['LOG_UNIT_STATUS']			=20;
$GLOBALS['LOG_UNIT_COMPLETE']		=21;		// 	run complete
$GLOBALS['LOG_UNIT_CHANGE']			=22;
*/
?>