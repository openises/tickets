<?php
/*
5/23/08 added function do_kml() - generates JS for kml files - 
5/31/08 added function do_log() default values
6/4/08	added $GLOBALS['LOG_INCIDENT_DELETE']	
6/9/08	added $GLOBALS['LEVEL_SUPER']
6/16/08 added reference $GLOBALS['LEVEL_SUPER']
6/26/08 added DELETE abandoned SESSION records
6/26/08 added log entries to  show_log()
6/28/08 added $my_session refresh at login
7/16/08 limited USER_AGENT string lgth to  100
7/18/07 dispatch disallowed for guest-level
8/6/08	fix to show_actions() when persons empty
8/7/08	added log actions for ACTION, PATIENT
8/15/08	mysql_fetch_array to mysql_fetch_assoc - performance
8/22/08	added function usng()
8/26/08	added speed check to distance check
9/7/08	added coords display per CG format
9/12/08 added USNG PHP functions
9/14/08 empty check to lat/lng functions
10/4/08	corrections to initial array setup to detect zero speed
10/6/08	added function mail_it ()
10/8/08 added window.focus()
10/8/08	added function is_email
10/8/08	'User' revised to 'Operator'
10/15/08 changed 'Comments' to 'Disposition'
10/15/08 relocated host id in mail msg
10/15/08 addr array to string
10/16/08 added tic's
10/17/08 addr string is now pipe-delim'd
10/17/08 sleep time added per settings value
10/18/08 added snap()
10/19/08 added istest-based timeout limit
10/21/08 added chunk no. to subject line
10/21/08 added new_notify_user() 
10/22/08 added priorities as selection criteria
10/22/08 set globals for notifies
10/22/08 added cell_addrs.inc.php as include
10/24/08 added status RESERVED
*/
error_reporting(E_ALL);

//	{						-- dummy
//	SELECT ticket.*, notify.id AS nid FROM ticket LEFT JOIN notify ON ticket.id=notify.ticket_id		These work
// 	SELECT * FROM ASSIGNS LEFT JOIN ticket ON ASSIGNS.ticket_id = assigns.id LEFT JOIN responder ON responder.id = assigns.responder_id
require_once('istest.inc.php');
require_once('mysql.inc.php');
require_once("phpcoord.php");				// UTM converter	
require_once("usng.inc.php");				// USNG converter 9/12/08
require_once("functions_major.inc.php");	// added 12/19/07

define ('NOT_STR', '*not*');
define ('NA_STR', '*na*');
define ('ADM_STR', 'Admin');
define ('SUPR_STR', 'Super');				// added 6/16/08

//$GLOBALS['mysql_prefix'] 			= $mysql_prefix;
/* constants - do NOT change */
$GLOBALS['STATUS_RESERVED'] 		= 0;		// 10/24/08
$GLOBALS['STATUS_CLOSED'] 			= 1;
$GLOBALS['STATUS_OPEN']   			= 2;
$GLOBALS['NOTIFY_ACTION'] 			= 'Added Action/Person';
$GLOBALS['NOTIFY_TICKET'] 			= 'Ticket Update';
$GLOBALS['ACTION_DESCRIPTION']		= 1;
$GLOBALS['ACTION_OPEN'] 			= 2;
$GLOBALS['ACTION_CLOSE'] 			= 3;
$GLOBALS['PATIENT_OPEN'] 			= 4;
$GLOBALS['PATIENT_CLOSE'] 			= 5;

$GLOBALS['NOTIFY_TICKET_CHG'] 		= 0;		// 10/22/08
$GLOBALS['NOTIFY_ACTION_CHG'] 		= 1;
$GLOBALS['NOTIFY_PERSON_CHG'] 		= 2;

//$GLOBALS['ACTION_OWNER'] 			= 4;
//$GLOBALS['ACTION_PROBLEMSTART'] 	= 5;
//$GLOBALS['ACTION_PROBLEMEND'] 	= 6;
//$GLOBALS['ACTION_AFFECTED'] 		= 7;
//$GLOBALS['ACTION_SCOPE'] 			= 8;
//$GLOBALS['ACTION_SEVERITY']		= 9;

$GLOBALS['ACTION_COMMENT']			= 10;
$GLOBALS['SEVERITY_NORMAL'] 		= 0;
$GLOBALS['SEVERITY_MEDIUM'] 		= 1;
$GLOBALS['SEVERITY_HIGH'] 			= 2;
$GLOBALS['LEVEL_SUPER'] 			= 0;		// 6/9/08
$GLOBALS['LEVEL_ADMINISTRATOR'] 	= 1;
$GLOBALS['LEVEL_USER'] 				= 2;
$GLOBALS['LEVEL_GUEST'] 			= 3;

$GLOBALS['TYPE_EMS']				= 1;		 // added 12/1/07
$GLOBALS['TYPE_FIRE'] 				= 2;
$GLOBALS['TYPE_COPS'] 				= 3;
$GLOBALS['TYPE_MUTU'] 				= 4; 		// Mutual Aid added 12/21/07
$GLOBALS['TYPE_OTHR'] 				= 5;

$GLOBALS['LOG_SIGN_IN']				= 1;
$GLOBALS['LOG_SIGN_OUT']			= 2;
$GLOBALS['LOG_COMMENT']				= 3;		// misc comment
$GLOBALS['LOG_INCIDENT_OPEN']		=10;
$GLOBALS['LOG_INCIDENT_CLOSE']		=11;
$GLOBALS['LOG_INCIDENT_CHANGE']		=12;
$GLOBALS['LOG_ACTION_ADD']			=13;
$GLOBALS['LOG_PATIENT_ADD']			=14;
$GLOBALS['LOG_INCIDENT_DELETE']		=15;		// added 6/4/08 
$GLOBALS['LOG_ACTION_DELETE']		=16;		// 8/7/08
$GLOBALS['LOG_PATIENT_DELETE']		=17;
$GLOBALS['LOG_UNIT_STATUS']			=20;
$GLOBALS['LOG_UNIT_COMPLETE']		=21;		// 	run complete
$GLOBALS['LOG_UNIT_CHANGE']			=22;

$GLOBALS['SESSION_TIME_LIMIT']		= ($istest)? 1200 : 120;		// minutes of inactivity 10/19/08

$evenodd = array ("even", "odd");	// class names for alternating table row css colors

/* connect to mysql database */

if (!mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_passwd'])) {
	die ("Connection attempt to MySQL failed - correction required in order to continue.");
	}

if (!mysql_select_db($GLOBALS['mysql_db'])) {
	print "Connection attempt to database failed. Please run <a href=\"install.php\">install.php</a> with valid  database configuration information.";
	exit();
	}

/* check for mysql tables, if non-existent, point to install.php */
$failed = 0;
/*		bypass 11/5/07 for performance
if (!mysql_table_exists("$GLOBALS[mysql_prefix]ticket")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]ticket' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]action")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]action' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]patient")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]patient' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]notify")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]notify' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]settings")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]settings' is missing<BR />"; $failed = 1; 	}
*/	
if (!mysql_table_exists("$GLOBALS[mysql_prefix]user")) 		{ print "MySQL table '$GLOBALS[mysql_prefix]user' is missing<BR />"; $failed = 1; 	}
if ($failed) {
	print "One or more database tables is missing.  Please run <a href=\"install.php\">install.php</a> with valid database configuration information.";
	exit();
	}

$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds
$sess_key = get_sess_key();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$my_session = (mysql_affected_rows()==1)? stripslashes_deep(mysql_fetch_assoc($result)): "";

function mysql_table_exists($table) {/* check if mysql table exists */
	$query = "SELECT COUNT(*) FROM `$table`";
//	dump($query);
	$result = mysql_query($query);
	$num_rows = @mysql_num_rows($result);
	if($num_rows)
		return TRUE;
	else
		return FALSE;
	}

function get_issue_date($id){
	$result = mysql_query("SELECT date FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'");
	$row = mysql_fetch_assoc($result);
	print $row[date];
	}

function check_for_rows($query) {		/* check sql query for returning rows, courtesy of Micah Snyder */
	if($sql = mysql_query($query)) {
		if(mysql_num_rows($sql) !== 0)
			return mysql_num_rows($sql);
		else
			return false;
		}
	else
		return false;
	}

//	} {		-- dummy

function show_assigns($which, $id) {				// 08/8/5
	global $evenodd;

	$which_ar = array ("ticket_id", "responder_id");
//	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.*, UNIX_TIMESTAMP(as_of) AS as_of, `assigns`.`responder_id`, `ticket`.`scope` AS `ticket`, responder.name AS `u_name`, `user`.`user` AS `by_name`
	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.*, UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket`, `$GLOBALS[mysql_prefix]responder`.`name` AS `u_name`, `$GLOBALS[mysql_prefix]user`.`user` AS `by_name`
	FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` 	ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`
	LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON `$GLOBALS[mysql_prefix]assigns`.`responder_id`=`$GLOBALS[mysql_prefix]responder`.`id`	
	LEFT JOIN `$GLOBALS[mysql_prefix]user` 		ON `$GLOBALS[mysql_prefix]assigns`.`user_id`=`$GLOBALS[mysql_prefix]user`.`id`	
	WHERE `$GLOBALS[mysql_prefix]assigns`.`" . $which_ar[$which] . "` = $id";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$i = 0;	
	$print = "";
	if (mysql_affected_rows()>0) {
		$print .= "\n<BR /><TABLE ALIGN='center' BORDER=0 width='100%'>";
		$print .= "\n<TR><TD ALIGN='center' COLSPAN=99><B>Active/Recent Dispatches</B> (" . mysql_affected_rows() . ")</TD>";
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$i++;
			$strike = $strikend = "";
			if (is_date($row['clear'])) {		
				$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
				}
			
			$print .="\n\t<TR CLASS= '" . $evenodd[($i+1)%2] . "'>";
//			$print .= "<TD>" . $strike . $row['id']	. 	$strikend . "</TD>";
			if ($which == 1) {															// showing incidents?
//				$print .= "<TD>" . $strike . shorten($row['ticket'], 20)	. 	$strikend . "</TD>";
				$print .= "<TD TITLE='" . $row['ticket']. "'>" . $strike . shorten($row['ticket'], 20)	. 	$strikend . "</TD>";
				}
			else {
//				$print .= "<TD>" . $strike . shorten($row['u_name']). 	$strikend . "</TD>";
				$print .= "<TD TITLE='" . $row['u_name']. "'>" . $strike . shorten($row['u_name'], 20)	. 	$strikend . "</TD>";
				}
			$print .= "<TD>" . $strike . format_date($row['as_of'])	. $strikend . "</TD>";
			$print .= "<TD>" . $strike . $row['by_name'] 	. 	$strikend . "</TD>";
			$print .= "</TR>";
			}				// end while($row...)
//		dump ($print);
		$print .= "</TABLE>\n";			
		}				// end if (mysql_ ...)
	return $print;
	
	}			// end function get_assigns()


function show_actions ($the_id, $theSort="date", $links, $display) {			/* list actions and patient data belonging to ticket */
	global $my_session;
	if ($display) {
		$evenodd = array ("even", "odd");		// class names for display table row colors
		}
	else {
		$evenodd = array ("plain", "plain");	// print
		}
	$query = "SELECT `id`, `name` FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$responderlist = array();
	$responderlist[0] = "NA";	
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$responderlist[$act_row['id']] = $act_row['name'];
		}
	$print = "<TABLE BORDER='0' ID='patients' width=" . max(320, intval($my_session['scr_width']* 0.4)) . ">";
																	/* list patients */
	$query = "SELECT *,UNIX_TIMESTAMP(date) AS `date`,UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]patient` WHERE `ticket_id`='$the_id' ORDER BY `date`";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = "Patient: &nbsp;&nbsp;";
	$actr=0;
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$print .= "<TR CLASS='" . $evenodd[$actr%2] . "' WIDTH='100%'><TD VALIGN='top' NOWRAP CLASS='td_label'>" . $caption . "</TD>";
		$print .= "<TD NOWRAP>" . $act_row['name'] . "</TD><TD NOWRAP>". format_date($act_row['updated']) . "</TD>";
		$print .= "<TD NOWRAP> by <B>".get_owner($act_row['user'])."</B>";
		
		$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'] ? "*" : "-")."</TD><TD>" . nl2br($act_row['description']) . "</TD>";
		if ($links) {
			$print .= "<TD>&nbsp;[<A HREF='patient.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
				<A HREF='patient.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</TD></TR>\n";	
				}
		$caption = "";				// once only
		$actr++;
		}
																	/* list actions */
	$query = "SELECT *,UNIX_TIMESTAMP(date) AS `date`,UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$the_id' ORDER BY `date`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_affected_rows() + $actr)==0) { 				// 8/6/08
		return "";
		}				
	else {
		$caption = "Actions: &nbsp;&nbsp;";
		$pctr=0;
		while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
			$print .= "<TR CLASS='" . $evenodd[$pctr%2] . "' WIDTH='100%'><TD VALIGN='top' NOWRAP CLASS='td_label'>$caption</TD>";
			$responders = explode (" ", trim($act_row['responder']));	// space-separated list to array
			$sep = $respstring = "";
			for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
				if (array_key_exists($responders[$i], $responderlist)) {
					$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
					$sep = "<BR />";
					}
				}
			
			$print .= "<TD NOWRAP>" . $respstring . "</TD><TD NOWRAP>".format_date($act_row['updated']) ."</TD>";
			$print .= "<TD NOWRAP>by <B>".get_owner($act_row['user'])."</B> ";
			$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'])? '*' : '-';
			$print .= "</TD><TD WIDTH='100%'>" . nl2br($act_row['description']) . "</TD>";
			if ($links) {
				$print .= "<TD><NOBR>&nbsp;[<A HREF='action.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
					<A HREF='action.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</NOBR></TD></TR>\n";	
				}
			$caption = "";
			$pctr++;
			}				// end if/else (...)
		$print .= "</TABLE>\n";
		return $print;
		}				// end else
	}			// end function show_actions

// } { -- dummy

function show_log ($theid, $show_cfs=FALSE) {
	global $evenodd ;	// class names for alternating table row colors
	
	$types = array();
	$types[$GLOBALS['LOG_SIGN_IN']]				="Login";
	$types[$GLOBALS['LOG_SIGN_OUT']]			="Logout";
	$types[$GLOBALS['LOG_COMMENT']]				="Comment";		// misc comment
	$types[$GLOBALS['LOG_INCIDENT_OPEN']]		="Incident open";
	$types[$GLOBALS['LOG_INCIDENT_CLOSE']]		="Incident close";
	$types[$GLOBALS['LOG_INCIDENT_CHANGE']]		="Incident change";
	$types[$GLOBALS['LOG_ACTION_ADD']]			="Action added";
	$types[$GLOBALS['LOG_PATIENT_ADD']]			="Patient added";
	$types[$GLOBALS['LOG_ACTION_DELETE']]		="Action delete";
	$types[$GLOBALS['LOG_PATIENT_DELETE']]		="Patient delete";
	$types[$GLOBALS['LOG_INCIDENT_DELETE']]		="Incident delete";			// 6/26/08
	$types[$GLOBALS['LOG_UNIT_STATUS']]			="Unit status change";
	$types[$GLOBALS['LOG_UNIT_COMPLETE']]		="Unit complete";
	$types[$GLOBALS['LOG_UNIT_CHANGE']]			="Unit change";				// 6/26/08
	
	$query = "
		SELECT *, UNIX_TIMESTAMP(`when`) AS `when`, t.scope AS `tickname`, `r`.`name` AS `unitname`, `s`.`status_val` AS `theinfo`, `u`.`user` AS `thename` FROM `$GLOBALS[mysql_prefix]log`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]log.ticket_id = t.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON ($GLOBALS[mysql_prefix]log.responder_id = r.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s ON ($GLOBALS[mysql_prefix]log.info = s.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ($GLOBALS[mysql_prefix]log.who = u.id)
		WHERE `$GLOBALS[mysql_prefix]log`.`ticket_id` = $theid
		";
		
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$i = 0;
	$print = "<TABLE ALIGN='left' CELLSPACING = 1 WIDTH='100%'>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		if ($i==0) {
			$print .= "<TR CLASS='even'><TD COLSPAN=99 ALIGN='center'><B> Log: <I>". shorten($row['tickname'], 32) . "</I></B></TD></TR>";
			$cfs_head = ($show_cfs)? "<TD ALIGN='center'>CFS</TD>" : ""  ;
			$print .= "<TR CLASS='odd'><TD ALIGN='center'>Code</TD>" . $cfs_head . "<TD ALIGN='center'>Unit</TD><TD ALIGN='center'>Status</TD><TD ALIGN='center'>When</TD><TD ALIGN='center'>By</TD><TD ALIGN='center'>From</TD></TR>";
			}
	
		$print .= "<TR CLASS='" . $evenodd[$i%2] . "'>" .
			"<TD>". $types[$row['code']] . "</TD>";
		if ($show_cfs) {
			$print .= "<TD>". shorten($row['tickname'], 32) . "</TD>";
			}
		$print .= 
			"<TD>". shorten($row['unitname'], 32) . "</TD>".
			"<TD>". $row['theinfo'] . "</TD>".
			"<TD>". format_date($row['when']) . "</TD>".
			"<TD>". $row['thename'] . "</TD>".
			"<TD>". $row['from'] . "</TD>".
			"</TR>";
			$i++;
		}
	$print .= "</TABLE>";
	return $print;
	}		// end function get_log ()
//	} -- dummy
function set_ticket_status($status,$id){				/* alter ticket status */
	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET status='$status' WHERE ID='$id'LIMIT 1";
	$result = mysql_query($query) or do_error("set_ticket_status(s:$status, id:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function format_date($date){							/* format date to defined type */ 
	if (good_date($date)) {	
		return date(get_variable("date_format"),$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)
	
function good_date($date) {
	return (is_string ($date) && ((strlen($date)==10)));
	}

function format_sb_date($date){							/* format sidebar date */ 
	if (is_string ($date) && strlen($date)==10) {	
		return date("M-d H:i",$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)

function get_status($status){							/* return status text from code */
	switch($status)	{
		case 1: return 'Closed';
			break;
		case 2: return 'Open';
			break;
		default: return 'Status error';
		}
	}

function get_owner($id){								/* get owner name from id */
//	dump ($id);
	$result	= mysql_query("SELECT user FROM `$GLOBALS[mysql_prefix]user` WHERE `id`='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "unk?" : $row['user'];
//	return $row['user'];
	}

function get_severity($severity){			/* return severity string from value */
	switch($severity) {
		case $GLOBALS['SEVERITY_NORMAL']: 	return "normal"; break;
		case $GLOBALS['SEVERITY_MEDIUM']: 	return "medium"; break;
		case $GLOBALS['SEVERITY_HIGH']: 	return "high"; break;
		default: 							return "Severity error"; break;
		}
	}

function get_responder($id){			/* return responder-type string from value */
	$result	= mysql_query("SELECT `name` FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$id' LIMIT 1") or do_error("get_responder(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$temprow	= stripslashes_deep(mysql_fetch_assoc($result));
	return $temprow['name'];
	}

function strip_html($html_string) {						/* strip HTML tags/special characters and fix custom ones to prevent bad HTML, CrossSiteScripting etc */
	$html_string =strip_tags(htmlspecialchars($html_string));	//strip all "real" html and convert special characters first
	
	if (!get_variable('allow_custom_tags')){
		//$html_string = str_replace('\[|\]', '', $html_string);
		//$html_string = str_replace('[b]', '', $html_string);
		//$html_string = str_replace('[/b]', '', $html_string);
		//$html_string = str_replace('[i]', '', $html_string);
		//$html_string = str_replace('[/i]', '', $html_string);
		return $html_string;
		}
	
	$html_string = str_replace('[b]', '<b>', $html_string);	//fix bolds
	$html_string = str_replace('[/b]', '</b>', $html_string);
	
	$html_string = str_replace('[i]', '<i>',$html_string);	//fix italics
	$html_string = str_replace('[/i]', '</i>', $html_string);
	
	return $html_string;
	}

function do_mail($ticket_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_assoc($ticket_result));
	$message  = "PHP Ticket on ".get_variable('host')."\n";
	$message .= "This message has been sent to you because you are subscribed to be notified of updates to this ticket.\n\n";
	$message .= "Notify Action: $action\n";
	$message .= "Ticket ID: " . $t_row['id'] . "\n";
	$message .= "Ticket Name: " . $t_row['scope'] . "\n";
//	$message .= "Ticket Owner: ".get_owner($t_row['owner'])."\n";
	$message .= "Ticket Status: ".get_status($t_row['status'])."\n";
//	$message .= "Ticket Affected: $t_row['affected']\n";
	$message .= "Ticket Run Start: " . $t_row['problemstart'] . "\n";
	$message .= "Ticket Run End: " . $t_row['problemend'] . "\n";
	$message .= "Ticket Description: ".wordwrap($t_row['description'])."\n";
	$message .= "Ticket Disposition: ".wordwrap($t_row['comments'])."\n";
	
	//add patient record to message
	if(check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id' ORDER BY DATE")){
		$message .= "\nPatient:\n";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
		$ticket_result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);

		while($t_row = stripslashes_deep(mysql_fetch_assoc($ticket_result)))
			$message .= $t_row['name'] . ", " . $t_row['updated']  . "- ". wordwrap($t_row['description'])."\n";
			}
	//add actions to message
	if(check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$ticket_id' ORDER BY DATE")){
		$message .= "\nActions:\n";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$ticket_id'";
		$ticket_result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		while($t_row = stripslashes_deep(mysql_fetch_assoc($ticket_result)))
			$message .= $t_row['updated'] . " - ".wordwrap($t_row['description'])."\n";
			}
	
	$message .= "\nThis is an automated message, please do not reply.";
	mail($row['email_address'],'Ticket Notification', $message);
	}		// end function do_mail()

//function ZZnotify_user($ticket_id,$action){	/* notify user check, $action is the action that triggered the notify, edit, close etc */
//	if (get_variable('allow_notify') != '1') return;	//should we notify?
//	
//	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE ticket_id='$ticket_id'";	// all notifies for given ticket
//	$result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
//	while($row = stripslashes_deep(mysql_fetch_assoc($result))){		//is it the right action?
//		if (($action == $GLOBALS['NOTIFY_ACTION'] AND $row['on_action']) OR ($action == $GLOBALS['NOTIFY_TICKET'] AND $row['on_ticket'])){
//
//			if (strlen($row['email_address'])){			// notify by email?
//				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$ticket_id'";
//				$ticket_result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$ticket_id'") or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]ticket)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//				$t_row = stripslashes_deep(mysql_fetch_assoc($ticket_result));
//				$message  = "PHP Ticket on ".get_variable('host')."\n";
//				$message .= "This message has been sent to you because you are subscribed to be notified of updates to this ticket.\n\n";
//				$message .= "Notify Action: $action\n";
//				$message .= "Ticket ID: " . $t_row['id'] . "\n";
//				$message .= "Ticket Name: " . $t_row['scope'] . "\n";
////				$message .= "Ticket Owner: ".get_owner($t_row['owner'])."\n";
//				$message .= "Ticket Status: ".get_status($t_row['status'])."\n";
////				$message .= "Ticket Affected: $t_row['affected']\n";
//				$message .= "Ticket Run Start: " . $t_row['problemstart'] . "\n";
//				$message .= "Ticket Run End: " . $t_row['problemend'] . "\n";
//				$message .= "Ticket Description: ".wordwrap($t_row['description'])."\n";
//				$message .= "Ticket Disposition: ".wordwrap($t_row['comments'])."\n";
//			
//				// add patient record to message
//				if(check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id' ORDER BY DATE")){
//					$message .= "\nPatient:\n";
//					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
//					$ticket_result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM `$GLOBALS[mysql_prefix]action`)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//					while($t_row = stripslashes_deep(mysql_fetch_assoc($ticket_result)))
//						$message .= $t_row['name'] . ", " . $t_row['updated']  . "- ". wordwrap($t_row['description'])."\n";
//						}
//				// add actions to message
//				if(check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$ticket_id' ORDER BY DATE")){
//					$message .= "\nActions:\n";
//					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$ticket_id'";
//					$ticket_result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM `$GLOBALS[mysql_prefix]action`)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//					while($t_row = stripslashes_deep(mysql_fetch_assoc($ticket_result)))
//						$message .= $t_row['updated'] . " - ".wordwrap($t_row['description'])."\n";
//						}
//			
//				$message .= "\nThis is an automated message, please do not reply.";
////				mail($row['email_address'],'Ticket Notification', $message);
//				}
//	
//			// notify by running program
//			if (strlen($row['execute_path'])){	/* not done yet */
//				}
//			}
//		else {			/* no matching action */
//			return;
//			}
//		}
//	}

$variables = array();
function get_variable($which){								/* get variable from db settings table, returns FALSE if absent  */
	global $variables;
	if (empty($variables)) {
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]settings`") or do_error("get_variable(n:$name)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$name = $row['name']; $value=$row['value'] ;
			$variables[$name] = $value;
			}
		}
	return (array_key_exists($which, $variables))? $variables[$which] : FALSE ;
//	return $variables[$which];
	}
	
function do_logout(){						/* logout - destroy session data */
	global $my_session;
	if (!empty($my_session)) {				// logged in?
		do_log($GLOBALS['LOG_SIGN_OUT'],0,0,$my_session['user_id']);				// log the logout	
		}
	$sess_key = get_sess_key();
	$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' OR `last_in` < '" .(time()-$the_time_limit) . "'";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	do_login('main.php', TRUE);
	}

function do_error($err_function,$err,$custom_err='',$file='',$line=''){/* raise an error event */
	print "<FONT CLASS=\"warn\">An error occured in function '<B>$err_function</B>': '<B>$err</B>'<BR />";
	if ($file OR $line) print "Error occured in '$file' at line '$line'<BR />";
	if ($custom_err != '') print "Additional info: '<B>$custom_err</B>'<BR />";
	print '<BR />Check your MySQL connection and if the problem persist, contact the <A HREF="help.php?q=credits">author</A>.<BR />';
	die('<B>Execution stopped.</B></FONT>');
	}

function add_header($ticket_id)		{/* add header with links */
	print "<BR /><NOBR><FONT SIZE='2'>This Call: ";	
	if (is_administrator() || is_super()){
		print "<A HREF='edit.php?id=$ticket_id'>Edit </A> | ";
//		print "<A HREF='edit.php?id=$ticket_id&delete=1'>Delete </A> | ";
		if (!is_closed($ticket_id)) {
			print "<A HREF='action.php?ticket_id=$ticket_id'>Add Action</A> | ";
			print "<A HREF='patient.php?ticket_id=$ticket_id'>Add Patient</A> | ";
			}
		print "<A HREF='config.php?func=notify&id=$ticket_id'>Notify</A> | ";
		}
	print "<A HREF='main.php?print=true&id=$ticket_id'>Print </A> | ";
	print "<A HREF='#' onClick = \"var mailWindow = window.open('mail.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=300, width=400, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\">E-mail </A>"; // 10/8/08
	if (!is_guest()) {				// 7/18/07
		print " | <A HREF='routes.php?ticket_id=$ticket_id'>Dispatch Unit</A>";		// new 9/22
		}
	print "</FONT></NOBR><BR />";
	}

function is_closed($id){/* is ticket closed? */
	return check_for_rows("SELECT id,status FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id' AND status='$GLOBALS[STATUS_CLOSED]'");
	}

function is_super(){				// added 6/9/08
	global $my_session;
	if ($my_session['level'] == $GLOBALS['LEVEL_SUPER']) return 1;
	}
function is_administrator(){/* is user admin or super? */
	global $my_session;
	if (($my_session['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) || ($my_session['level'] == $GLOBALS['LEVEL_SUPER'])) return 1;
	}

function is_guest(){/* is user guest? */
	global $my_session;
	return ($my_session['level'] == $GLOBALS['LEVEL_GUEST']);
	}

function is_user(){/* is user admin? */
	global $my_session;
	if ($my_session['level'] == $GLOBALS['LEVEL_USER']) return 1;
	}
																	/* print date and time in dropdown menus */ 
function generate_date_dropdown($date_suffix,$default_date=0, $disabled=FALSE) {			// 'extra allows 'disabled'
//	return;
//	dump ($date_suffix);
	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = get_variable('delta_mins');													// align server clock minutes
	$local = mktime(date("G"), date("i")-$deltam, date("s"), date("m"), date("d"), date("Y"));
	
	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		$minute		= date('i',$default_date);
		$meridiem	= date('a',$default_date);
		if (get_variable('military_time')==1) 	$hour = date('H',$default_date);
		else 									$hour = date('h',$default_date);;
		}
	else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		$minute		= date('i', $local);
		$meridiem	= date('a', $local);
		if (get_variable('military_time')==1) 	$hour = date('H', $local);
		else 									$hour = date('h', $local);
		}
	print "<SELECT name='frm_year_$date_suffix' $dis_str>";
	for($i = date("Y")-1; $i < date("Y")+1; $i++){
		print "<OPTION VALUE='$i'";
		$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
		}
			
	print "</SELECT>";
	print "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
	for($i = 1; $i < 13; $i++){
		print "<OPTION VALUE='$i'";
		$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
		}
		
	print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' $dis_str>";
	for($i = 1; $i < 32; $i++){
		print "<OPTION VALUE=\"$i\"";
		$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
		}
	print "</SELECT>\n&nbsp;&nbsp;";
	
	print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
	
	print "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_hour_$date_suffix' VALUE='$hour' $dis_str>:";
	print "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_minute_$date_suffix' VALUE='$minute' $dis_str>";
	$show_ampm = (!get_variable('military_time')==1);
	if ($show_ampm){	//put am/pm optionlist if not military time
		print "\n<SELECT NAME='frm_meridiem_$date_suffix' $dis_str><OPTION value='am'";
		if ($meridiem == 'am') print ' selected';
		print ">am</OPTION><OPTION value='pm'";
		if ($meridiem == 'pm') print ' selected';
		print ">pm</OPTION></SELECT>";
		}
	}		// end function generate_date_dropdown(

function report_action($action_type,$ticket_id,$value1='',$value2=''){/* insert reporting actions */
	global $my_session;
//	exit(); //not used in 0.7
	if (!get_variable('reporting')) return;
	
	switch($action_type)	{
//		case $GLOBALS[ACTION_AFFECTED]: $description = "Changed affected field: $value1"; break;
//		case $GLOBALS[ACTION_SCOPE]: 	$description = "Changed scope field: $value1"; break;
//		case $GLOBALS[ACTION_SEVERITY]: $description = "Changed severity from $value1 to $value2"; break;
		case $GLOBALS[ACTION_OPEN]: 	$description = "Ticket Opened"; break;
		case $GLOBALS[ACTION_CLOSED]: 	$description = "Ticket Closed"; break;
		case $GLOBALS[PATIENT_OPEN]: 	$description = "Patient Item Opened"; break;
		case $GLOBALS[PATIENT_CLOSED]: 	$description = "Patient Item Closed"; break;
		default: 						$description = "[unknown report value: $action_type]";
		}
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]action` (date,ticket_id,action_type,description,user) VALUES('$now','$ticket_id','$action_type','$description','$my_session[user_id]')";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

function shorten($instring, $limit) {
	return (strlen($instring) > $limit)? substr($instring, 0, $limit-4) . "..." : $instring ;	// &#133
	}

function format_phone ($instr) {
	$temp = trim($instr);
	return  (!empty($temp))? "(" . substr ($instr, 0,3) . ") " . substr ($instr,3, 3) . "-" . substr ($instr,6, 4): "";
	}
	
function highlight($term, $string) {		// highlights search term
	$replace = "<SPAN CLASS='found'>" .$term . "</SPAN>";
	if (function_exists('str_ireplace')) {
		return str_ireplace ($term,  $replace, $string); 
		}
	else {
		return str_replace ($term,  $replace, $string); 
		}
	}

function stripslashes_deep($value) {
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);
    return $value;
	}
function trim_deep($value) {	
    $value = is_array($value) ?
                array_map('trim_deep', $value) :
                trim($value);
    return $value;
	}
function mysql_real_escape_string_deep($value) {
    $value = is_array($value) ?
                array_map('mysql_real_escape_string_deep', $value) :
                mysql_real_escape_string($value);
    return $value;
	}
function nl2brr($text) {
    return preg_replace("/\r\n|\n|\r/", "<BR />", $text);
	}

function get_level_text ($level) {
	switch ($level) {
		case $GLOBALS['LEVEL_SUPER'] 			: return "Super"; break;
		case $GLOBALS['LEVEL_ADMINISTRATOR'] 	: return "Admin"; break;
		case $GLOBALS['LEVEL_USER'] 			: return "Operator"; break;
		case $GLOBALS['LEVEL_GUEST'] 			: return "Guest"; break;;
		default 								: return "level error"; break;
		}
	}		//end function
	
function got_gmaps() {								// valid GMaps API key ?
	return (strlen(get_variable('gmaps_api_key'))==86);
	}

function mysql_format_date($indate="") {			// returns MySQL-format date given argument timestamp or default now
	if (empty($indate)) {$indate = time();}
	return date("Y-m-d H:i:s", $indate);
	}

function is_date($DateEntry) {						// returns true for valid non-zero date
	$Date_Array = explode('-',$DateEntry);			// "2007-00-00 00:00:00"
	if (count($Date_Array)!=3) 									return FALSE;
	if((strlen($Date_Array[0])!=4)|| ($Date_Array[0]=="0000")) 	return FALSE;
	else {return TRUE;}	
	}		// end function Is_Date()

function toUTM($coordsIn) {							// UTM converter - assume comma separator
	$temp = explode(",", $coordsIn);
	if (!count($temp)==2) {
//		print __LINE__; 
//		dump ($coordsIn);
		}
	$coords = new LatLng(trim($temp[0]), trim($temp[1]));	
	$utm = $coords->toUTMRef();
	$temp = $utm->toString();
	$temp1 = explode (" ", $temp);					// parse by space
	$temp2 = explode (".", $temp1[1]);				// parse by period
	$temp3 = explode (".", $temp1[2]);
	return $temp1[0] . " " . $temp2[0] . " " . $temp3[0];
	}				// end function toUTM ()
	
function get_type($id) {				// returns incident type given its id
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` WHERE `id`= $id LIMIT 1";
	$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_type = stripslashes_deep(mysql_fetch_assoc($result_type));
	unset ($result_type);
	return $row_type['type'];
	}

function output_csv($data, $filename = false){
	$csv = array();
	foreach($data as $row){
		$csv[] = implode(', ', $row);
		}
	$csv = sprintf('%s', implode("\n", $csv));

	if ( !$filename ){
		return $csv;
		}

	// Dumping output straight out to browser.

//	header('Content-Type: application/csv');
//	header('Content-Disposition: attachment; filename=' . $filename);
//	echo $csv;
//	exit;
	}


function do_aprs() {			// populates the APRS tracks table 
								// major surgery by Randy Hammock, August 07
								// Note:	This function assumes the structure/format of APRS data as of Aug 30,2007.
								//			Contact developer with solid information regarding any change in that format.
								// rev 8/17/08 to toss data further than 500 mi fm defult center - to prevent data pollution
								//
	$delay = 1;			// minimum time in minutes between APRS queries

	function mysql2timestamp($m) {
		return mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
		}
	function date_OK ($indate) {	// checks for date/time within 48 hours
		return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60); 
		}

	$when = get_variable('_aprs_time');

	if(time() < $when) { 
		return;
		} 
	else {
//		print __LINE__ . "</BR />";

		$pkt_ids = array();				// 6/17/08
		$speeds = array();				// 10/2/08
		$sources = array();
//		$query = "SELECT `packet_id` FROM `$GLOBALS[mysql_prefix]tracks`";
//		$query = "SELECT `packet_id`, `source`, `speed`, MAX(`packet_date`) AS `packet_date` FROM `$GLOBALS[mysql_prefix]tracks` GROUP BY `source`";		// 10/2/08
//		$query = "SELECT `source`, `speed`, MAX( `packet_id` ) as `packet_id` FROM `$GLOBALS[mysql_prefix]tracks` GROUP BY source";
//
//		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//		while ($row = mysql_fetch_assoc($result)) {
//			$pkt_ids[trim($row['packet_id'])] = TRUE;					// index is packet_id
//			$sources[trim($row['source'])] = TRUE;						// index is callsign
//			$speeds[trim($row['source'])] = $row['speed'];				// index is callsign 10/2/08
//			}
																		// 10/4/08
		$query = "SELECT `callsign`, `mobile` FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile`= 1";
		$result1 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		while ($row1 = mysql_fetch_assoc($result1)) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source`= '{$row1['callsign']}' ORDER BY `packet_date` DESC LIMIT 1";	// possibly none
			$result2 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			while ($row2 = mysql_fetch_assoc($result2)) {
				$pkt_ids[trim($row2['packet_id'])] = TRUE;					// index is packet_id
				$sources[trim($row2['source'])] = TRUE;						// index is callsign
				$speeds[trim($row2['source'])] = $row2['speed'];			// index is callsign 10/2/08
				}
			}

//		dump($pkt_ids);
//		dump($speeds);
		$next = time() + $delay*60;
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$next' WHERE `name`='_aprs_time'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `packet_date`< (NOW() - INTERVAL 7 DAY)"; // remove ALL expired track records 
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1";  // work each call sign
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//		$result	= mysql_query($query);				// skip error inserts

		while ($row = @mysql_fetch_assoc($result)) {	
			$lat= (empty($row['lat'])) ? $row['lat']: get_variable('def_lat');
			$lng= (empty($row['lng'])) ? $row['lng']: get_variable('def_lng');

			$url = "http://db.aprsworld.net/datamart/csv.php?call=". $row['callsign'];	
			$raw="";		
			if ($fp = @fopen($url, r)) {		
				while (!feof($fp)) $raw .= fgets($fp, 128);		
					fclose($fp);					
					}
			$raw = str_replace("\r",'',$raw);								// Strip Carriage Returns
			$data = explode ("\n",  $raw , 50 );							// Break each line
//			dump($data);
			if (count($data) > 1) {

				$data[1] = str_replace("\",\"", '|', $data[1]); 			// Convert to pipe delimited
				$data[1] = str_replace("\"", '', $data[1]);	  				// Strip remaining quotes
				$fields = explode ("|",  $data[1]);				 			// Break out the fields
				$fields = mysql_real_escape_string_deep($fields);
				if ((count($fields) == 14) && (date_OK ($fields[13])))  {	// APRS data sanity check
//					print __LINE__ . "<br />";

					$packet_id = trim($fields[1]) . trim($fields[13]); 		// source, date - unique
					if(!(isset($pkt_ids[$packet_id]))) {				// 6/17/08 - avoid duplicate reports						
						$dist = pow($lat-$fields[2],2) + pow($lng-$fields[3],2);		// 8/17/08
//						print $dist . " " .  __LINE__ . "<br />";
						if ($dist < 250000.0) {				// 8/26/08	- 10/2/08  planar distance from center < 500 mi?
//							print __LINE__ . "<br />";

							$query  = "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '$fields[1]' AND  `packet_date`< (NOW() - INTERVAL 7 DAY)"; // remove expired track records this source
							$temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

							if(!array_key_exists($fields[1], $sources)) {	// 		new, populate 10/2/08
//								$pkt_ids[$packet_id] = TRUE;
								$speeds[$fields[1]] = 999;					//
								}
//							if (intval($fields[5])==0) {
//								$a = (array_key_exists($packet_id, $pkt_ids));
//								$b = (intval($fields[5])>0);
//								$c = (intval($speeds[$fields[1]])>0);
//								dump($a);
//								dump($b);
//								dump($c);
//								dump($fields[1]);
//								print __LINE__ . "<br />";
//								}
							$error = FALSE;
																					// don't store if duplicate packet_id
							if ((!array_key_exists($packet_id, $pkt_ids)) && 		// 
								(intval($fields[5])>0) || 
								(intval($speeds[$fields[1]])>0)) {					// 10/2/08

								$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`packet_id`,
																		`source`,`latitude`,`longitude`,`course`,
																		`speed`,`altitude`,`symbol_table`,`symbol_code`,
																		`status`,`closest_city`,`mapserver_url_street`,
																		`mapserver_url_regional`,`packet_date`,`updated`)
													VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
																		NOW() + INTERVAL 1 MINUTE)",
														quote_smart($packet_id),
														quote_smart($fields[1]),
														quote_smart(floatval($fields[2])),
														quote_smart(floatval($fields[3])),
														quote_smart(intval($fields[4])),
														quote_smart(intval($fields[5])),
														quote_smart(intval($fields[6])),
														quote_smart($fields[7]),
														quote_smart($fields[8]),
														quote_smart($fields[9]),
														quote_smart($fields[10]),
														quote_smart($fields[11]),
														quote_smart($fields[12]),
														quote_smart($fields[13]));
			
								$result_tr = mysql_query($query) or $error = TRUE ;
								}
								
//							else {
//								$a = (array_key_exists($packet_id, $pkt_ids));
//								$b = (intval($fields[5])>0);
//								$c = (intval($speeds[$fields[1]])>0);
//								dump($a);
//								dump($b);
//								dump($c);
//								dump ($packet_id . " : " . $fields[5]);
//								}
								
							$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
							$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
								`lat`= " . 	quote_smart(trim($fields[2])) . ",
								`lng`= " . 	quote_smart(trim($fields[3])) . ",
								`updated`=	'$now'
								WHERE `callsign`= " . quote_smart(trim($fields[1])) . " LIMIT 1";				// 10/2/08, 8/26/08  -- needs USNG computation
							
							$result_tr = mysql_query($query);
							$lat = $fields[2];										// 8/26/08
							$lng = $fields[3];
							}	
//						else {
//							print __LINE__ . "</BR />";
//							}
						}			// end if(!(isset(...)
					
					}				// end if (count()== 15)		
				}		// end for ($i...)		
		
			}		// end while ($row =...)

		}		// end else time
	}		// end function do_aprs() 



function do_log($code, $ticket_id=0, $responder_id=0, $info="") {		// generic log table writer - 5/31/08
	global $my_session;
//	dump ($my_session);
	$who = (!empty($my_session))? $my_session['user_id']: 0;
	$from = $_SERVER['REMOTE_ADDR'];
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$query = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]log` (`who`,`from`,`when`,`code`,`ticket_id`,`responder_id`,`info`)  
		VALUES(%s,%s,%s,%s,%s,%s,%s)",
				quote_smart(trim($who)),
				quote_smart(trim($from)),
				quote_smart(trim($now)),
				quote_smart(trim($code)),
				quote_smart(trim($ticket_id)),
				quote_smart(trim($responder_id)),
				quote_smart(trim($info)));

	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//	<input type="hidden" name="frm_do_log" value="">
//	dump ($query);
	}

/*
9/29 quotes line 355 
11/02 corrections to list and show ticket to handle newlines in Description and Comments fields.
11/03 added function do_onload () frame jump prevention
11/06 revised function get_variable to return FALSE if argument is absent
11/9 added map under image
11/30 added function do_log()
12/15 revised log schema for consistency across codes
*/

// =====================================================================================
function do_login($requested_page, $outinfo = FALSE) {			/* do login/session code - returns array*/
	global $my_session;
//	function get_new_key() {						// returns newly-developed session key
//		return sha1(ip_address_to_number($_SERVER['REMOTE_ADDR']));
//		}

	$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds
	$sess_key = get_sess_key();
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	if(mysql_affected_rows()==1) {												// logged in OK - normal path, update time last_in
		return upd_lastin();													// session array
		}
															// not logged in; now either get form data or db check form entries 		
	else { if(array_key_exists('frm_passwd', $_POST)) {		// first, db check
			$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `user`=" . quote_smart($_POST['frm_user']). " AND `passwd`=PASSWORD(" . quote_smart($_POST['frm_passwd']) . ") LIMIT 1";
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()==1) {
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				if ($row['sortorder'] == NULL) $row['sortorder'] = "date";
				$dir = ($row['sort_desc']) ? " DESC " : "";
				$now = time() - (get_variable('delta_mins')*60);
				$key = get_sess_key();				// 6/28/08
				
//		  		sess_id  user_name  user_id  level  ticket_per_page  sortorder  scr_width  scr_height  browser  last_in 10
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]session` WHERE `user_id` = " . $row['id'];						// 6/26/08
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
// 7/16/08
				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]session` ( `sess_id`, `user_name`, `user_id`, `level`,  `ticket_per_page`, `sortorder`, `scr_width`, `scr_height`,  `browser`,  `last_in`)
								VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
									quote_smart($key),
									quote_smart($_POST['frm_user']),
									quote_smart($row['id']),
									quote_smart($row['level']),
									quote_smart($row['ticket_per_page']),
									quote_smart($row['sortorder'] .$dir),
									quote_smart($_POST['scr_width']),
									quote_smart($_POST['scr_height']),
									quote_smart(substr($_SERVER['HTTP_USER_AGENT'], 0, 100)),
									quote_smart($now));
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				$the_id = mysql_insert_id();																	// 6/28/08
				
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `id` = $the_id LIMIT 1";			// re-establish $my_session
				$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$my_session = stripslashes_deep(mysql_fetch_assoc($result));
				
				do_log($GLOBALS['LOG_SIGN_IN'],0,0,$row['id']);	// log it													

				$to = "";
				$subject = "Tickets Login";
				$message = "From: " . gethostbyaddr($_SERVER['REMOTE_ADDR']) ."\nBrowser:" . $_SERVER['HTTP_USER_AGENT'];
				$message .= "\nBy: " . $_POST['frm_user'];
				$message .= "\nScreen: " . $_POST['scr_width'] . " x " .$_POST['scr_height'];
				$message .= "\nReferrer: " . $_POST['frm_referer'];
	
//				mail  ($to, $subject, $message);
							
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', FALSE);
				header('Pragma: no-cache');
				

				$host  = $_SERVER['HTTP_HOST'];
				$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
				$extra = 'main.php';
				header("Location: http://$host$uri/$extra");
				exit;				
				
				return $my_session;				// to calling page
				}			// end if (mysql_affected_rows()==1)
			}			// end if((!empty($_POST))&&(check_for_rows(...)

//			if no form data or values fail

?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<HTML xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - Login Module</TITLE>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
		<META HTTP-EQUIV="Expires" CONTENT="0">
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
		<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
		<SCRIPT>
		String.prototype.trim = function () {
			return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
			};
			
		function getBrowserWidth(){
			var val="";
		    if (window.innerWidth){
		        var val= window.innerWidth;}
		    else if (document.documentElement && document.documentElement.clientWidth != 0){
		        var val= document.documentElement.clientWidth;    }
		    else if (window.screen.width && window.screen.width != 0){
		        var val= window.screen.width;    }
		    else if (document.body){var val= document.body.clientWidth;}
		        return(isNaN(val))? 1024: val;
			}
		function getBrowserHeight(){
			var val="";
		    if (window.innerHeight){
		        var val= window.innerHeight;}
		    else if (document.documentElement && document.documentElement.clientHeight != 0){
		        var val= document.documentElement.clientHeight;    }
		    else if (window.screen.height && window.screen.height != 0){
		        var val= window.screen.height;    }
		    else if (document.body){var val= document.body.clientHeight;}
		        return(isNaN(val))? 740: val;
			}

		
//		if (parent.frames["upper"]) {		// ????
//			parent.frames["upper"].document.getElementById("script").innerHTML  = "login";
//			}
		
		function do_onload () {
			if (this.window.name!="main") {self.close();}			// in a popup
			if(self.location.href==parent.location.href) {			// prevent frame jump
				self.location.href = 'index.php';
				};
			try {		// should always be true
				parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php echo NOT_STR;?>" ;
				parent.frames["upper"].document.getElementById("level").innerHTML  = "<?php echo NA_STR;?>" ;
				parent.frames["upper"].document.getElementById("script").innerHTML  = "login";
				}
			catch(e) {
				}
			document.login_form.scr_width.value=getBrowserWidth();
			document.login_form.scr_height.value=getBrowserHeight();
//			document.login_form.frm_user.focus();
			}		// end function do_onload () 

		window.setTimeout("document.forms[0].frm_user.focus()", 1000);
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "do_onload()">
		<CENTER><BR />
<?php
		if(get_variable('_version') != '') print "<SPAN style='FONT-WEIGHT: bold; FONT-SIZE: 15px; COLOR: #000000;'>" . get_variable('login_banner')."</SPAN><BR /><BR />";
?>
		</FONT><FORM METHOD="post" ACTION="<?php print $requested_page;?>" NAME="login_form">
		<TABLE BORDER=0>
		
<?php

		if(array_key_exists('frm_passwd', $_POST)) { print "<TR CLASS='odd'><TH COLSPAN='99'><FONT CLASS='warn'>Login failed.Pls enter correct values and try again.</FONT><BR /><BR /></TH></TR>";}
		$temp =  isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : "";;

?>
		<TR CLASS='even'><TD ROWSPAN=6 VALIGN='middle' ALIGN='left' bgcolor=#EFEFEF><BR /><BR />&nbsp;&nbsp;<IMG BORDER=0 SRC='open_source_button.png'><BR /><BR />
		&nbsp;&nbsp;<a href="http://www.openaprs.net/"><img src="openaprs.png"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD><TD CLASS="td_label">User:</TD><TD><INPUT TYPE="text" NAME="frm_user" MAXLENGTH="255" SIZE="30" onChange = "document.login_form.frm_user.value = document.login_form.frm_user.value.trim();"></TD></TR>
		<TR CLASS='odd'><TD CLASS="td_label">Password: &nbsp;&nbsp;</TD><TD><INPUT TYPE="password" NAME="frm_passwd" MAXLENGTH="255" SIZE="30" onChange = "document.login_form.frm_passwd.value = document.login_form.frm_passwd.value.trim();"></TD></TR>
		<TR CLASS='even'><TD></TD><TD><INPUT TYPE="submit" VALUE="Log In"></TD></TR>
		<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><BR />&nbsp;&nbsp;&nbsp;&nbsp;Visitors may login as <B>guest</B> with password <B>guest</B>.&nbsp;&nbsp;&nbsp;&nbsp;</TD></TR>
		<TR CLASS='odd'><TD COLSPAN=2>&nbsp;</TD></TR>
<!--	<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>
		&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:shoreas@Gmail.com?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		</TD></TR>
		<TR CLASS='even'><TD COLSPAN=3 ALIGN='center'><BR><A HREF='tickets_CAD.ppt'>Download '<U>About Tickets</U>' PowerPoint</A> <FONT COLOR='red'>&nbsp;&nbsp;<B>New!</B></FONT><BR><BR></TD></TR>
		<TR><TD COLSPAN=3 ALIGN='center'><BR><BR>Download <A HREF="tickets_2_7_d_beta.zip"><U>Version 2.7.d beta</U></A>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='http://demo-userguide.wikidot.com/start' TARGET = '_blank'> <U>Tickets Documentation</U> - draft</A></TD></TR>
 -->	
 		<TR CLASS='even'><TD COLSPAN=3><BR /><BR /></TD></TR>
 		</TABLE>
		<INPUT TYPE='hidden' NAME = 'scr_width' VALUE=''>
		<INPUT TYPE='hidden' NAME = 'scr_height' VALUE=''>
		<INPUT TYPE='hidden' NAME = 'frm_referer' VALUE="<?php print $temp; ?>">
		</FORM></CENTER>
		</HTML>
<?php
			exit();		// no return value
			}
	}		// end function do_login()

function ip_address_to_number($IPaddress) {
	$ips = split ("\.", "$IPaddress");
	return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
	}

function get_sess_key() {
	return sha1(ip_address_to_number($_SERVER['REMOTE_ADDR']));
	}

function upd_lastin() {						// updates session last-in time, returns session array
	$sess_key = get_sess_key();
	$now = time() - (get_variable('delta_mins')*60);
	$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `last_in` = '" . $now . "' WHERE `sess_id`='{$sess_key}' LIMIT 1";
	$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	return stripslashes_deep(mysql_fetch_assoc ($result));
	}

function totime($string){			// given a MySQL-format date/time, returns the unix equivalent
	return mktime(substr($string, 11 , 2),  substr($string, 14 , 2), substr($string, 17 , 2),  substr($string, 5 , 2),  substr($string, 8 , 2),  substr($string, 0 , 4));
	}

function LessExtension($strName) {
	$ext = strrchr($strName, '.');	
	return ($ext)? substr($strName, 0, -strlen($ext)):$strName  ;
	}		// end function LessExtension()


function xml2php($xml) {
	$fils = 0;
	$tab = false;
	$array = array();
	foreach($xml->children() as $key => $value) 	{   
		$child = xml2php($value);
		foreach($node->attributes() as $ak=>$av) {		// To deal with the attributes
			$child[$ak] = (string)$av;
			}
		if($tab==false && in_array($key,array_keys($array))) {		// Let's see if the new child is not in the array
			$tmp = $array[$key];									// If this element is already in the array
			$array[$key] = NULL;									//   we will create an indexed array
			$array[$key][] = $tmp;
			$array[$key][] = $child;
			$tab = true;
			}
		elseif($tab == true) {
			$array[$key][] = $child;			//Add an element in an existing array
			}
		else {			//Add a simple element
			$array[$key] = $child;
			}
		$fils++;	   
	  	}
	if($fils==0) {
		return (string)$xml;
		}
	return $array;
	}

function get_stuff($in_file) {				// return file contents as string
	return file_get_contents($in_file);;
	}				// end function get_stuff()
	
function get_ext($filename) {				// return extension in lower-case
	$exts = split("[/\\.]", $filename) ;
	return strtolower($exts[count($exts)-1]);
	}

function ezDate($d) {
	$temp = strtotime(str_replace("-","/",$d));
	$ts = time() - $temp;
	if (($ts < 0) || ($ts > 315360000)) {return FALSE;}							// sanity check
	
	if($ts>31536000) $val = round($ts/31536000,0).' year';
	else if($ts>2419200) $val = round($ts/2419200,0).' month';
	else if($ts>604800) $val = round($ts/604800,0).' week';
	else if($ts>86400) $val = round($ts/86400,0).' day';
	else if($ts>3600) $val = round($ts/3600,0).' hour';
	else if($ts>60) $val = round($ts/60,0).' minute';
  	else $val = $ts.' second';
	if(!($val==1)) $val .= 's';
	$val .= " ago";
	return $val;
	} 
	
function do_kml() {									// emits JS for kml-type files in noted directory - added 5/23/08
	$dir = "./kml_files";							// required as directory
	if (is_dir($dir)){										
		$dh  = opendir($dir);
		$temp = explode ("/", $_SERVER['REQUEST_URI']);
		$temp[count($temp)-1] = substr($dir, 2);				// home subdir
		$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
		while (false !== ($filename = readdir($dh))) {
			switch (get_ext($filename)) {						// drop all other types, incl directories
				case "kml":
				case "kmz":
				case "xml":
					$url = $server_str . $filename;
					echo "\tmap.addOverlay(new GGeoXml(\"" . $url . "\"));\n";
				}		// end switch ()
			}		// end while ()
		}		// end is_dir()
	}		// end function do_kml()
		


function lat2dms($inlat) {				// 9/9/08 both to degr, min, sec
	$nors = ($inlat<0.0)? "S.":"N.";
	$d = floor(abs($inlat));	// degrees
	$mu = (abs($inlat)-$d)*60;	// min's unrounded
	$m = floor($mu);			// min's
	$su = ($mu - $m)*60;		// sec's unrounded
	$s = (round($su, 1));		// seconds
	return $d . '&deg; ' . abs($m) . "&#39; " . abs($s) . "&#34;" . $nors;
	}

function lng2dms($inlng) {				// 9/9/08 both to degr, min, sec
	$wore = ($inlng<0.0)? "W.":"E.";
	$d = floor(abs($inlng));	// degrees
	$mu = (abs($inlng)-$d)*60;	// min's unrounded
	$m = floor($mu);			// min's
	$su = ($mu - $m)*60;		// sec's unrounded
	$s = (round($su, 1));		// seconds
	return $d . '&deg; ' . abs($m) . "&#39; " . abs($s) . "&#34;" . $wore;
	}


function lat2ddm($inlat) {				// to degr, dec mins 9/7/08
	$nors = ($inlat<0.0)? "S.":"N.";
	$deg = floor(abs($inlat));
	return $deg . '&deg; ' . round(abs($inlat-$deg)*60, 1) . "' " . $nors;
	}
function lng2ddm($inlng) {				// to degr, dec mins 9/7/08
	$wore = ($inlng<0.0)? "W.":"E.";
	$deg = floor(abs($inlng));
	return $deg . '&deg; ' . round((abs($inlng)-$deg)*60, 1) . "' " . $wore;
	}

function get_lat($in_lat) {					// 9/7/08
	if (empty($in_lat)) {return"";}			// 9/14/08
	$format = get_variable('lat_lng');

	switch ($format) {
		case 0:						// decimal
		    return $in_lat;
		    break;
		case 1:
//			return ll2dms($in_lat);	// dms
			return lat2dms($in_lat);	// dms
			break;
		case 2:						// cg format
		    return lat2ddm($in_lat);
		    break;
		}
	}				// end function get_lat()
	
function get_lng($in_lng) {					// 9/7/08
	if (empty($in_lng)) {return"";}			// 9/14/08
	$format = get_variable('lat_lng');

	switch ($format) {
		case 0:						// decimal
		    return $in_lng;
		    break;
		case 1:	
//			return ll2dms($in_lng);		// dms
			return lng2dms($in_lng);	// dms
			break;
		case 2:						// cg format
		    return lng2ddm($in_lng);
		    break;
		}
	}				// end function get_lng()
	

function mail_it ($to_str, $text, $ticket_id) {				// 10/6/08, 10/15/08		returns caption -- 
	$to_array = explode ("|",$to_str );						// pipe-delimited string  - 10/17/08
	require_once("cell_addrs.inc.php");			// 10/22/08
//	$cell_addrs = array( "gmail.com", "vtext.com", "messaging.sprintpcs.com", "txt.att.net", "vmobl.com", "myboostmobile.com");		// 10/5/08
	if ($istest) {array_push($cell_addrs, "gmail.com");};
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
	
	$eol = "\n";
	$message = "This message generated by Tickets CAD.\n";
	
	$temp = trim(stripslashes_deep($text));
//	$message .= empty($temp)? "" : "Note: " . $temp . $eol;
	$message .= "Incident: " . $t_row['scope'] . " (#" .$t_row['id'] . ")" . $eol;
	$message .= "Priority: " . get_severity($t_row['severity']);
	$message .= "      Nature: " . get_type($t_row['in_types_id']) . $eol;
	$message .= "Written: " . $t_row['date'] . $eol;
	$message .= "Updated: " . $t_row['updated'] . $eol;
	$message .= "Reported by: " . $t_row['contact'] .", Phone: " . format_phone ($t_row['phone']) . $eol;
	$message .= "Phone: " . format_phone ($t_row['phone']) .  $eol;
	$message .= "Status: ".get_status($t_row['status']).$eol.$eol;
	$message .= "Address: " . $t_row['street'] . " "  . $t_row['city'] . " " . $t_row['state'] . $eol;
	$message .= "Description: ".wordwrap($t_row['description']).$eol;
	$message .= "Disposition: ".wordwrap($t_row['comments']).$eol;
	$message .= "Run Start: " . $t_row['problemstart'] . " Incident End: " . $t_row['problemend'] .$eol;
	$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . "\n";
	$message = wordwrap($message, 70);
//		add patient record to message
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()>0) {
		$message .= "\nPatient:\n";
		while($pat_row = stripslashes_deep(mysql_fetch_array($ticket_result))){
			$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
			}
		}
//		add actions to message
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$ticket_id'";		// 10/16/08
	if (mysql_affected_rows()>0) {
		$message .= "\nActions:\n";
		$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while($act_row = stripslashes_deep(mysql_fetch_array($ticket_result))) {
			$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
			}
		}
	$message .= "Tickets host: ".get_variable('host').$eol;				// 10/15/08
	$message = str_replace("\n.", "\n..", $message);		// see manual re mail win platform peculiarities
	
	$subject = "Ticket: " . shorten($t_row['scope'], 36);
	$host = get_variable('host');
	$temp = get_variable('email_reply_to');
	$reply_to = (empty($temp))? "": "'Reply-To: '". $temp ."\r\n" ;
	
	$headers = 'From: Tickets_CAD@' .$host . "\r\n" .
	    $reply_to .
	    'X-Mailer: PHP/' . phpversion();
	
	$to_sep = $cell_sep = "";
	$tostr = $tocellstr = "";
	for ($i = 0; $i< count($to_array); $i++) {
		$temp =  explode ( "@", $to_array[$i]);
		if (in_array(trim(strtolower($temp[1])), $cell_addrs))  {				// cell addr?
			$tocellstr .= $cell_sep . stripslashes($to_array[$i]);				// yes
			$cell_sep = ",";
			}
		else {																	// no
			$tostr .= $to_sep . stripslashes($to_array[$i]);
			$to_sep = ",";														// comma separated addr string
			}
		}				// end for ($i = ...)
		
	$caption="";
	if (strlen($tostr)>0) {	
		mail($tostr, $subject, $message, $headers);
		$caption = "Email sent";
		}
	if (strlen($tocellstr)>0) {
//		dump($tocellstr);
		$lgth = 140;
		$ix = 0;
		$i = 1;
		while (substr($message, $ix , $lgth )) {								// chunk to $lgth-length strings
			$subject_ex = $subject . "/part " . $i . "/";				// 10/21/08
//			snap(__LINE__,$subject_ex);
			mail($tocellstr, $subject_ex, substr ($message, $ix , $lgth ), $headers);
			sleep ($sleep);														// 10/17/08
			$ix+=$lgth;
			$i++;
			}
		$caption .= " - Cell mail sent";
		}
	return $caption;
	
	}			// end function mail_it ()

function is_email($email){ 	//  validate email, code courtesy of Jerrett Taylor - 10/8/08
	if(!eregi( "^" .
            "[a-z0-9]+([_\\.-][a-z0-9]+)*" .    //user
            "@" .
            "([a-z0-9]+([\.-][a-z0-9]+)*)+" .   //domain
            "\\.[a-z]{2,}" .                    //sld, tld
            "$", $email, $regs)
   			) {
		return FALSE;
		}
	else {
		return TRUE;
		}
	}		// end function

function notify_user($ticket_id,$action_id) {								// 10/20/08
	if (get_variable('allow_notify') != '1') return FALSE;						//should we notify?
	
	$fields = array();
	$fields[$GLOBALS['NOTIFY_TICKET_CHG']] = "on_ticket";
	$fields[$GLOBALS['NOTIFY_ACTION_CHG']] = "on_action";
	$fields[$GLOBALS['NOTIFY_PERSON_CHG']] = "on_patient";
	
	$addrs = array();															// 
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE (`ticket_id`='$ticket_id' OR `ticket_id`=0)  AND `" .$fields[$action_id] ."` = '1'";	// all notifies for given ticket - or any ticket 10/22/08
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
		if (is_email($row['email_address'])) {
			array_push($addrs, $row['email_address']); // save for emailing
			}		
		}
//	dump($query);
	return (empty($addrs))? FALSE: $addrs;
	}


function snap($source, $stuff) {																// 10/18/08 - debug tool
	if (mysql_table_exists("$GLOBALS[mysql_prefix]_test")) {
		$query	= "DELETE FROM `$GLOBALS[mysql_prefix]_test` WHERE `when`< (NOW() - INTERVAL 1 DAY)"; 		// first remove old
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]_test` (`source`,`stuff`) VALUES('$source', '$stuff')";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	}		// end function
?>