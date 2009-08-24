<?php
/*
5/23/08 added function do_kml() - generates JS for kml files - 
5/31/08 added function do_log() default values
6/4/08  added $GLOBALS['LOG_INCIDENT_DELETE']	
6/9/08  added $GLOBALS['LEVEL_SUPER']
6/16/08 added reference $GLOBALS['LEVEL_SUPER']
6/26/08 added DELETE abandoned SESSION records
6/26/08 added log entries to  show_log()
6/28/08 added $my_session refresh at login
7/16/08 limited USER_AGENT string lgth to  100
7/18/07 dispatch disallowed for guest-level
8/6/08  fix to show_actions() when persons empty
8/7/08  added log actions for ACTION, PATIENT
8/15/08 mysql_fetch_array to mysql_fetch_assoc - performance
8/22/08 added function usng()
8/26/08 added speed check to distance check
9/7/08  added coords display per CG format
9/12/08 added USNG PHP functions
9/14/08 empty check to lat/lng functions
10/4/08 corrections to initial array setup to detect zero speed
10/6/08 added function mail_it ()
10/8/08 added window.focus()
10/8/08 added function is_email
10/8/08 'User' revised to 'Operator'
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
11/21/08 added user agent string to session id hash - for testing
1/11/09 suppress mail error report, return TBD incident type
1/20/09 added callboard log entries
1/21/09 show/hide top frame buttons
1/23/09 added isFloat function, aprs position checks, error snaps, aprs conditionals
1/26/09 mysql2timestamp() made public
1/28/09 relocated function quote_smart() fm istest.php, global types removed
1/30/09 handle MD5 passwds
2/3/09  removed delta fm date/time evaluation
2/4/09  added db functions - unused at this writing
2/13/09 disallow 'member' logins
2/15/09 added function format_date_time()
2/16/09 added text parameter to caption string
2/18/09 function mail_it() broken into msg() and send() functions
2/19/09 added get_mysession ()
3/3/09  MEMBER text addition, disallow MEMBER login
3/5/09  renamed table _test to z_snapper
3/7/09  removed function do_mail()
3/8/09  test user/pword
3/12/09 unset() added
3/16/09 added function get_current()
3/18/09 'aprs_poll' to 'auto_poll', dist chk rev'd for testing
3/19/09 tracks_hh update added, single track record only
3/22/09 fixed 'action' entries, instam/aprs hskpg
3/25/09 added$GLOBALS['TOLERANCE']  for remote time validity determination, function my_is_float(), my_is_int()
3/26/09 dropped use of last position
5/4/09  revised My_is_float for 0 handling
7/7/09  upgrade do_send to handle smtp, LOG_CALL_RESET added, force 'waiting' message after logout
7/7/09  force non-zero str match, script META's addad
7/8/09  $GLOBALS['LEVEL_UNIT'] added
7/8/09  extract smtp name
7/8/09  $GLOBALS['TRACK_APRS'], etc, added
7/25/09 instam corrections, apply 1-minute poll limit, removed fm APRS
7/29/09 added functions do_grack, do_locatea and do_glat to get data from these datasources. Modified function get_current to include them.
8/2/09  explode() -> split()
8/3/09  explode() -> split() for gtrack and locateA functions
8/7/09	Revised function generate_date_dropdown to change display based on locale setting
8/9/09	revise glat() to handle non-Curl configurations
8/10/09	removed 'mobile = 1' from tracking select criteria, removed locale case "2"
8/20/09	added close_incident link
{									// 3/25/09

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
$GLOBALS['NOTIFY_ACTION'] 			= 'Added Action/Patient';
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
$GLOBALS['LEVEL_MEMBER'] 			= 4;		// 12/15/08	
$GLOBALS['LEVEL_UNIT'] 				= 5;		// 7/8/09

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

$GLOBALS['LOG_CALL_DISP']			=30;		// 1/20/09
$GLOBALS['LOG_CALL_RESP']			=31;
$GLOBALS['LOG_CALL_ONSCN']			=32;
$GLOBALS['LOG_CALL_CLR']			=33;
$GLOBALS['LOG_CALL_RESET']			=34;		// 7/7/09

$GLOBALS['icons'] 		= array("black.png", "blue.png", "green.png", "red.png", "white.png", "yellow.png", "gray.png", "lt_blue.png", "orange.png");
$GLOBALS['sm_icons']	= array("sm_black.png", "sm_blue.png", "sm_green.png", "sm_red.png", "sm_white.png", "sm_yellow.png", "sm_gray.png", "sm_lt_blue.png", "sm_orange.png");

$GLOBALS['SESSION_TIME_LIMIT']		= ($istest)? 3600 : 3600;		// minutes of inactivity 10/19/08
$GLOBALS['TOLERANCE']				= 180*60;		// seconds of deviation from UTC before remotes sources considered 	erroneous - 3/25/09

$GLOBALS['TRACK_APRS']			=1;     	// 7/8/09
$GLOBALS['TRACK_INSTAM']			=2;       
$GLOBALS['TRACK_GTRACK']			=3;   
$GLOBALS['TRACK_LOCATEA']			=4;      
$GLOBALS['TRACK_GLAT']			=5;     

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
	$types[0]									=" - error - ";
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
	
	$types[$GLOBALS['LOG_CALL_DISP']]			="Unit dispatched";				// 1/29/09
	$types[$GLOBALS['LOG_CALL_RESP']]			="Unit responding";
	$types[$GLOBALS['LOG_CALL_ONSCN']]			="Unit on-scene";	
	$types[$GLOBALS['LOG_CALL_CLR']]			="Unit clear";		
	$types[$GLOBALS['LOG_CALL_RESET']]			="Times reset";				// 7/7/09
	
	
	$query = "
		SELECT *, UNIX_TIMESTAMP(`when`) AS `when`, t.scope AS `tickname`, `r`.`name` AS `unitname`, `s`.`status_val` AS `theinfo`, `u`.`user` AS `thename` FROM `$GLOBALS[mysql_prefix]log`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]log.ticket_id = t.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON ($GLOBALS[mysql_prefix]log.responder_id = r.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s ON ($GLOBALS[mysql_prefix]log.info = s.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ($GLOBALS[mysql_prefix]log.who = u.id)
		WHERE `$GLOBALS[mysql_prefix]log`.`ticket_id` = $theid
		";
//`	dump($query);		
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$i = 0;
	$print = "<TABLE ALIGN='left' CELLSPACING = 1 WIDTH='100%'>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
//		dump($row);
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
	
function good_date($date) {		// 
	return (is_string ($date) && ((strlen($date)==10)));
	}

function format_sb_date($date){							/* format sidebar date */ 
	if (is_string ($date) && strlen($date)==10) {	
		return date("M-d H:i",$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)

function good_date_time($date) {						//  2/15/09
//	if(! (is_string ($date) && (strlen($date)==19) && (!($date=="0000-00-00 00:00:00")))) snap(__FUNCTION__ . __LINE__, $date);
	return (is_string ($date) && (strlen($date)==19) && (!($date=="0000-00-00 00:00:00")));
	}

function format_date_time($date){		// mySql format to settings spec - 2/15/09
	return (good_date_time($date))? date(get_variable("date_format"),mysql2timestamp($date))  : "TBD";
	}				// end function format_date()
	
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
	
function do_logout($return=FALSE){						/* logout - destroy session data */
	global $my_session;
	if (!empty($my_session)) {				// logged in?
		do_log($GLOBALS['LOG_SIGN_OUT'],0,0,$my_session['user_id']);				// log the logout	
		}
	$sess_key = get_sess_key();
	$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' OR `last_in` < '" .(time()-$the_time_limit) . "'";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ($return) return;
	do_login('main.php', TRUE);
	}

function do_error($err_function,$err,$custom_err='',$file='',$line=''){/* raise an error event */
	print "<FONT CLASS=\"warn\">An error occured in function '<B>$err_function</B>': '<B>$err</B>'<BR />";
	if ($file OR $line) print "Error occured in '$file' at line '$line'<BR />";
	if ($custom_err != '') print "Additional info: '<B>$custom_err</B>'<BR />";
	print '<BR />Check your MySQL connection and if the problem persist, contact the <A HREF="help.php?q=credits">author</A>.<BR />';
	die('<B>Execution stopped.</B></FONT>');
	}

function add_header($ticket_id, $no_close = FALSE)		{/* add header with links */
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
	print "<A HREF='#' onClick = \"var mailWindow = window.open('mail.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=300, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\">E-mail </A> |"; // 10/8/08
	if (!is_guest()) {				// 7/18/07
		print "<A HREF='routes.php?ticket_id=$ticket_id'> Dispatch Unit</A> | ";		// new 9/22
		print "<A HREF='#' onClick = \"var mailWindow = window.open('add_note.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"> Add note </A>"; // 10/8/08
		if (!($no_close)) {
			print "  | <A HREF='#' onClick = \"var mailWindow = window.open('close_in.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"> Close incident </A> ";  // 8/20/09
			}
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
	$locale = get_variable('locale');				// Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
	switch($locale) { 
		case "0":
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
			break;
	
		case "1":
			print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "<SELECT name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				print "<OPTION VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
			break;
																						// 8/10/09
//		case "2":
//			print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' $dis_str>";
//			for($i = 1; $i < 32; $i++){
//				print "<OPTION VALUE=\"$i\"";
//				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
//				}
//	
//			print "</SELECT>";
//			print "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
//			for($i = 1; $i < 13; $i++){
//				print "<OPTION VALUE='$i'";
//				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
//				}
//	
//			print "<SELECT name='frm_year_$date_suffix' $dis_str>";
//			for($i = date("Y")-1; $i < date("Y")+1; $i++){
//				print "<OPTION VALUE='$i'";
//				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
//				}
//			print "</SELECT>\n&nbsp;&nbsp;";
//		
//			print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
//			break;

		default:
		    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		}

	
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
		case $GLOBALS['LEVEL_GUEST'] 			: return "Guest"; break;
		case $GLOBALS['LEVEL_MEMBER'] 			: return "Member"; break;			// 3/3/09
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

function toUTM($coordsIn, $from = "") {							// UTM converter - assume comma separator
	$temp = explode(",", $coordsIn);
//	dump($coordsIn);
//	dump($temp[0]);
//	dump($temp[1]);
//	if (!count($temp)==2) {
//		print __LINE__; 
//		dump ($coordsIn);
//		}
//	dump( __LINE__ . $from);
	$coords = new LatLng(trim($temp[0]), trim($temp[1]));	
	$utm = $coords->toUTMRef();
	$temp = $utm->toString();
	$temp1 = explode (" ", $temp);					// parse by space
	$temp2 = explode (".", $temp1[1]);				// parse by period
	$temp3 = explode (".", $temp1[2]);
	return $temp1[0] . " " . $temp2[0] . " " . $temp3[0];
	}				// end function toUTM ()
	
function get_type($id) {				// returns incident type given its id
	if ($id == 0) {return "TBD";}		// 1/11/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` WHERE `id`= $id LIMIT 1";
	$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_type = stripslashes_deep(mysql_fetch_assoc($result_type));
//	unset ($result_type);
	return (isset($row_type['type']))? $row_type['type']: "?";		// 8/12/09
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


function mysql2timestamp($m) {				// 1/26/09
	return mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
	}

function aprs_date_ok ($indate) {	// checks for date/time within 48 hours
		return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60); 
		}

function do_aprs() {				// populates the APRS tracks table 
									// major surgery by Randy Hammock, August 07
									// Note:	This function assumes the structure/format of APRS data as of Aug 30,2007.
									//			Contact developer with solid information regarding any change in that format.
									// rev 8/17/08 to toss data further than 500 mi fm defult center - to prevent data pollution
									//
	global $istest;
	$dist_chk = ($istest)? 2500000.0 : 250000.0 ;		// 3/18/09
//	$delay = 1;			// minimum time in minutes between APRS queries

//	$when = get_variable('_aprs_time');

//	if(time() < $when) { 
//		return;
//		} 
		
//	else {
//		snap(__FUNCTION__ . __LINE__, "");
//		$next = time() + $delay*60;
//		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$next' WHERE `name`='_aprs_time'";
//		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$pkt_ids = array();				// 6/17/08
		$speeds = array();				// 10/2/08
		$sources = array();
																		// 10/4/08
//		$query = "SELECT `callsign`, `mobile` FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile`= 1 AND `aprs`= 1 AND `callsign` <> ''";  // 1/23/09
		$query = "SELECT `callsign`, `mobile` FROM `$GLOBALS[mysql_prefix]responder` WHERE `aprs`= 1 AND `callsign` <> ''";  // 1/23/09, 8/10/09
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

//	snap(__FUNCTION__ . __LINE__, "");

		$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `packet_date`< (NOW() - INTERVAL 7 DAY)"; // remove ALL expired track records 
		$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		unset($resultd);
		
//		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `aprs`= 1 AND `callsign` <> ''";  // work each call sign
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `aprs`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

		if (mysql_affected_rows() > 0) {			// 1/23/09
//			snap(__FUNCTION__ . __LINE__, "");
	
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
//					snap(__FUNCTION__ . __LINE__, count($data) );
	
					$data[1] = str_replace("\",\"", '|', $data[1]); 			// Convert to pipe delimited
					$data[1] = str_replace("\"", '', $data[1]);	  				// Strip remaining quotes
					$fields = explode ("|",  $data[1]);				 			// Break out the fields

					$fields = mysql_real_escape_string_deep($fields);			// 

					if ((count($fields) == 14) && (aprs_date_ok ($fields[13])))  {	// APRS data sanity check
//						snap(__FUNCTION__ . __LINE__, $fields[13] );
		
						$packet_id = trim($fields[1]) . trim($fields[13]); 		// source, date - unique
//						snap(__FUNCTION__ . __LINE__ , $packet_id );
						$temp = (isset($pkt_ids[$packet_id]))? "true" : "false";
//						snap(__FUNCTION__ . __LINE__ , $temp );

						if(!(isset($pkt_ids[$packet_id]))) {					// 6/17/08 - avoid duplicate reports						
							$dist = pow($lat-$fields[2],2) + pow($lng-$fields[3],2);		// 8/17/08
	//						print $dist . " " .  __LINE__ . "<br />";

//							snap(__FUNCTION__ . __LINE__, $dist );
							if ($dist < $dist_chk) {							// 3/18/09, 8/26/08	- 10/2/08  planar distance from center < 500 mi?
							
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
//								snap(__FUNCTION__ . __LINE__, "" );
																						// don't store if duplicate packet_id or invalid floats
								if ((!array_key_exists($packet_id, $pkt_ids)) && 		// 1/23/09
									(intval($fields[5])>0) || 
									(isFloat($fields[2])) &&
									(isFloat($fields[3])) && 
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
//									dump($query);
									}
									
								else {													// 1/23/09
									$a = (array_key_exists($packet_id, $pkt_ids));
									$b = (intval($fields[5])>0);
									$c = (intval($speeds[$fields[1]])>0);
									}
									
								$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
								$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
									`lat`= " . 	quote_smart(trim($fields[2])) . ",
									`lng`= " . 	quote_smart(trim($fields[3])) . ",
									`updated`=	'$now'
									WHERE `callsign`= " . quote_smart(trim($fields[1])) . " LIMIT 1";				// 10/2/08, 8/26/08  -- needs USNG computation
								
//								snap(__FUNCTION__ . __LINE__, $query );

								$result_tr = mysql_query($query);
								unset($result_tr);
								$lat = $fields[2];										// 8/26/08
								$lng = $fields[3];
								}	
	//						else {
	//							print __LINE__ . "</BR />";
	//							}
							}			// end if(!(isset(...)
						
						}				// end count($fields) == 14) && ...		
					}		// end for ($i...)		
			
				}		// end while ($row =...)
			}		// 1/23/09

//		}		// end else time
	}		// end function do_aprs() 



function do_log($code, $ticket_id=0, $responder_id=0, $info="") {		// generic log table writer - 5/31/08
	global $my_session;
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
	unset($result);		// 3/12/09
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

function sig_check ($in_array) {						// returns user record or FALSE, given $_POST -- 11/22/08
	$temp = $GLOBALS['LEVEL_MEMBER'];					// 2/13/09
	$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `user` = '${in_array['frm_user']}' AND `level` <> " . $temp . " LIMIT 1";
//	snap(_FUNCTION__, $query);
	$result_u = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_sess = get_sess_key();
	if (mysql_affected_rows() == 1) {																		// 'user' is defined

		$row_user = mysql_fetch_array($result_u);	
		$query  = "SELECT `sessid`, `salt` FROM `$GLOBALS[mysql_prefix]logins` WHERE `sessid` = '$the_sess'  LIMIT 1";		// 12/12/08
		$result_l = mysql_query($query) or mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
		$row_logins = mysql_fetch_array($result_l);

		if (md5($row_user['passwd'] . $row_logins['salt']) == $in_array['frm_sign']) { 				// taTAH!
			$query  = "DELETE FROM `$GLOBALS[mysql_prefix]logins` WHERE `sessid` = '$the_sess'";			// multiples possible - 12/12/08
			$result_l = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			return $row_user;
			}
		else {
			return FALSE;
			}
		}
	else {																												// userid fails
		$query  = "DELETE FROM `$GLOBALS[mysql_prefix]logins` WHERE `sessid` = '$the_sess'";				// multiples possible 12/12/08
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		return FALSE;									// userID or signature match failure
		}
	}		// end function sig _check ()
			
function do_login($requested_page, $outinfo = FALSE, $hh = FALSE) {			// do login/session code - returns array - 2/12/09, 3/8/09
	global $my_session, $istest;

	$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds
	$sess_key = get_sess_key();
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$temp = mysql_affected_rows();
//	snap(__FUNCTION__ . __LINE__, $temp);

	if($temp==1) {												// logged in OK - normal path, update time last_in
		return upd_lastin();													// session array
		}
															// not logged in; now either get form data or db check form entries 		
	else { if(array_key_exists('frm_passwd', $_POST)) {		// first, db check
			$temp = $GLOBALS['LEVEL_MEMBER'];					// 2/13/09
																														// 1/30/09 - 3/3/09
			$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `user`=" . quote_smart($_POST['frm_user']). " 	 AND (`passwd`=PASSWORD(" . quote_smart($_POST['frm_passwd']) . ") OR `passwd`=MD5(" . quote_smart(strtolower($_POST['frm_passwd'])) . " ))  AND `level` <>  $temp  LIMIT 1";
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()==1) {
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				if ($row['sortorder'] == NULL) $row['sortorder'] = "date";
				$dir = ($row['sort_desc']) ? " DESC " : "";
//				$now = time() - (get_variable('delta_mins')*60);
				$key = get_sess_key();				// 6/28/08
				
//		  		sess_id  user_name  user_id  level  ticket_per_page  sortorder  scr_width  scr_height  browser  last_in 10
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]session` WHERE `user_id` = " . $row['id'];						// 6/26/08
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
// 7/16/08 - 2/3/09
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
									quote_smart(time()));														// 2/3/09
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
	
//				@mail  ($to, $subject, $message);				// 1/11/09
							
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
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
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
		

		function do_hh_onload () {				// 2/24/09
			document.login_form.scr_width.value=getBrowserWidth();
			document.login_form.scr_height.value=getBrowserHeight();
			document.login_form.frm_user.focus();
			}		// end function do_onload () 


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
			parent.upper.hide_butts();				// 1/21/09
			}		// end function do_onload () 

<?php
		if (get_variable('call_board')==2) {		// 7/7/09
			print "\tparent.calls.location.href = 'assigns.php';\n";				// reload to show 'waiting' message 6/19/09
			}
?>
		window.setTimeout("document.forms[0].frm_user.focus()", 1000);
		</SCRIPT>
		</HEAD>
<?php
		print ($hh)? "\n\t<BODY onLoad = 'do_hh_onload()'>\n" : "\n\t<BODY onLoad = 'do_onload()'>\n";		// 2/24/09
?>	
		
		<BODY onLoad = "do_onload()">
		<CENTER><BR />
<?php
		if(get_variable('_version') != '') print "<SPAN style='FONT-WEIGHT: bold; FONT-SIZE: 15px; COLOR: #000000;'>" . get_variable('login_banner')."</SPAN><BR /><BR />";
?>
		</FONT>
		
		<FORM METHOD="post" ACTION="<?php print $requested_page;?>" NAME="login_form"  onSubmit="return true;">
		<TABLE BORDER=0>
		
<?php

		if(array_key_exists('frm_passwd', $_POST)) { print "<TR CLASS='odd'><TH COLSPAN='99'><FONT CLASS='warn'>Login failed. Pls enter correct values and try again.</FONT><BR /><BR /></TH></TR>";}
		$temp =  isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : "";

		$t_user = ($istest)? "admin": "";		// 3/8/09
		$t_pword = $t_user;
?>
		<TR CLASS='even'><TD ROWSPAN=6 VALIGN='middle' ALIGN='left' bgcolor=#EFEFEF><BR /><BR />&nbsp;&nbsp;<IMG BORDER=0 SRC='open_source_button.png'><BR /><BR />
		&nbsp;&nbsp;<a href="http://www.openaprs.net/"><img src="openaprs.png"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD><TD CLASS="td_label">User:</TD><TD><INPUT TYPE="text" NAME="frm_user" MAXLENGTH="255" SIZE="30" onChange = "document.login_form.frm_user.value = document.login_form.frm_user.value.trim();" VALUE="<?php print $t_user; ?>"></TD></TR>
		<TR CLASS='odd'><TD CLASS="td_label">Password: &nbsp;&nbsp;</TD><TD><INPUT TYPE="password" NAME="frm_passwd" MAXLENGTH="255" SIZE="30" onChange = "document.login_form.frm_passwd.value = document.login_form.frm_passwd.value.trim();"  VALUE="<?php print $t_pword; ?>"></TD></TR>
		<TR CLASS='even'><TD></TD><TD><INPUT TYPE="submit" VALUE="Log In"></TD></TR>
		<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><BR />&nbsp;&nbsp;&nbsp;&nbsp;Visitors may login as <B>guest</B> with password <B>guest</B>.&nbsp;&nbsp;&nbsp;&nbsp;</TD></TR>
		<TR CLASS='odd'><TD COLSPAN=2>&nbsp;</TD></TR>
		<TR CLASS='even'><TD COLSPAN=2>&nbsp;</TD></TR>
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
	$ips = split (".", "$IPaddress");
	return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
	}

function get_sess_key() {
	return sha1(ip_address_to_number($_SERVER['REMOTE_ADDR']) . trim ($_SERVER["HTTP_USER_AGENT"]));		// 11/21/08
	}

function upd_lastin() {						// updates session last-in time, returns session array - 2/3/09
	$sess_key = get_sess_key();
//	$now = time() - (get_variable('delta_mins')*60);
//	$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `last_in` = '" . $now . "' WHERE `sess_id`='{$sess_key}' LIMIT 1";
	$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `last_in` = '" . time() . "' WHERE `sess_id`='{$sess_key}' LIMIT 1";		// note no 'delta'
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
	$exts = explode(".", $filename) ;	// 8/2/09
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
	
function mail_it ($to_str, $text, $ticket_id, $text_sel=1, $txt_only = FALSE) {				// 10/6/08, 10/15/08,  2/18/09, 3/7/09
	global $istest;
/*
Subject		A
Inciden		B  Title
Priorit		C  Priorit
Nature		D  Nature
Written		E  Written
Updated		F  As of
Reporte		G  By
Phone: 		H  Phone: 
Status:		I  Status:
Address		J  Location
Descrip		K  Descrip
Disposi		L  Disposi
Start/end	M
Map: " 		N  Map: " 
Actions		O
Patients	P
Host		Q
*/

	switch ($text_sel) {		// 7/7/09
		case 1:
		   	$match_str = strtoupper(get_variable("msg_text_1"));				// note case
		   	break;
		case 2:
		   	$match_str = strtoupper(get_variable("msg_text_2"));
		   	break;
		case 3:
		   	$match_str = strtoupper(get_variable("msg_text_3"));
		   	break;
		}
	if (empty($match_str)) {$match_str = implode ("", range("A", "Q"));}		// empty get all

//	snap(__FUNCTION__ . __LINE__, $match_str);
	require_once("cell_addrs.inc.php");			// 10/22/08
	$cell_addrs = array( "gmail.com", "vtext.com", "messaging.sprintpcs.com", "txt.att.net", "vmobl.com", "myboostmobile.com");		// 10/5/08
	if ($istest) {array_push($cell_addrs, "gmail.com");};
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
	
	$eol = "\n";

	$message="";
	$_end = (good_date_time($t_row['problemend']))?  "  End:" . $t_row['problemend'] : "" ;		// 
	
	for ($i = 0;$i< strlen($match_str); $i++) {
		if(!($match_str[$i]==" ")) {
			switch ($match_str[$i]) {
				case "A":
				    break;
				case "B":
					$message .= "Incident: " . $t_row['scope'] . $eol;
				    break;
				case "C":
					$message .= "Priority: " . get_severity($t_row['severity']) . $eol;
				    break;
				case "D":
					$message .= "Nature: " . get_type($t_row['in_types_id']) . $eol;
				    break;
				case "J":
					$str = "";
					$str .= (empty($t_row['street']))? 	""  : $t_row['street'] . " " ;
					$str .= (empty($t_row['city']))? 	""  : $t_row['city'] . " " ;
					$str .= (empty($t_row['state']))? 	""  : $t_row['state'];
					$message .= empty($str) ? "" : "Addr: " . $str . $eol;
				    break;
				case "K":
					$message .= (empty($t_row['description']))?  "": "Descr: ". wordwrap($t_row['description']).$eol;
				    break;
				case "G":
					$message .= "Reported by: " . $t_row['contact'] . $eol;
				    break;
				case "H":
					$message .= (empty($t_row['phone']))?  "": "Phone: " . format_phone ($t_row['phone']) . $eol;
					break;
				case "E":
					$message .= (empty($t_row['date']))? "":  "Written: " . format_date_time($t_row['date']) . $eol;
				    break;
				case "F":
					$message .= "Updated: " . format_date_time($t_row['updated']) . $eol;
				    break;
				case "I":
					$message .= "Status: ".get_status($t_row['status']).$eol;
				    break;
				case "L":
					$message .= (empty($t_row['comments']))? "": "Disp: ".wordwrap($t_row['comments']).$eol;
				    break;
				case "M":
					$message .= "Run Start: " . format_date_time($t_row['problemstart']). $_end .$eol;
				    break;
				case "N":
					$usng = LLtoUSNG($t_row['lat'], $t_row['lng']);
					$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $usng . "\n";
				    break;
			
				case "P":															
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {
						$message .= "\nPatient:\n";
						while($pat_row = stripslashes_deep(mysql_fetch_array($result))){
							$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
							}
						}
					unset ($result);
				    break;
			
				case "O":
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$ticket_id'";		// 10/16/08
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_affected_rows()>0) {
						$message .= "\nActions:\n";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						while($act_row = stripslashes_deep(mysql_fetch_array($result))) {
							$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
							}
						}	
					unset ($result);
				    break;
			
				case "Q":
					$message .= "Tickets host: ".get_variable('host').$eol;
				    break;
				default:
				    $message = "Match string error:" . $match_str[$i]. " " . $match_str . $eol ;

				}		// end switch ()
			}		// end if(!($match_...))
		}		// end for ($i...)

	$message = str_replace("\n.", "\n..", $message);					// see manual re mail win platform peculiarities
	
	$subject = (strpos ($match_str, "A" ))? $subject = $text . $t_row['scope'] . " (#" .$t_row['id'] . ")": "";

	if ($txt_only) {
		return $subject . "\n" . $message;		// 2/16/09
		}
	else {
		do_send ($to_str,  $subject, $message);
		}
	}				// end function mail_it ()
// ________________________________________________________

function smtp ($my_to, $my_subject, $my_message, $my_params, $my_from) {				// 7/7/09
	require_once 'lib/swift_required.php';

// $params = "outgoing.verizon.net/587/ashore3/********/ashore3@verizon.net";
//				   0				1	   2	  3			4	

	$conn_ary = explode ("/",  $my_params);
//	dump($conn_ary) ;
	$transport = Swift_SmtpTransport::newInstance($conn_ary[0] , $conn_ary[1])
	  ->setUsername($conn_ary[2])
	  ->setPassword($conn_ary[3])
	  ;
	
	$mailer = Swift_Mailer::newInstance($transport);		// instantiate using  created Transport	
	$temp_ar = explode("@", $my_to);						// extract name portion - 7/8/09
	$the_from = (isset($conn_ary[4]))? $conn_ary[4]: $my_from;
	$the_from_ar = explode("@", $my_from);					// to extract user portion
															// Create a message
	$message = Swift_Message::newInstance($my_subject)
	  ->setFrom(array($the_from => $the_from_ar[0]))
	  ->setTo(array($my_to , $my_to => trim($temp_ar[0])))
	  ->setBody($my_message)
	  ;
	//    ->setTo(array('receiver@domain.org', 'other@domain.org' => 'Names'))
	$result = $mailer->send($message);						//Send the message
	
	}		// end function smtp


function do_send ($to_str, $subject_str, $text_str ) {						// 7/7/09
	global $istest;
	$sleep = 4;																// seconds delay between text messages

	$to_array = explode ("|",$to_str );										// pipe-delimited string  - 10/17/08
	require_once("cell_addrs.inc.php");										// 10/22/08
	$cell_addrs = array("vtext.com", "messaging.sprintpcs.com", "txt.att.net", "vmobl.com", "myboostmobile.com");		// 10/5/08
	if ($istest) {array_push($cell_addrs, "gmail.com");};

	$host = get_variable('host');
	$temp = get_variable('email_reply_to');	
	$reply_to = (empty($temp))? "": "'Reply-To: '". $temp ."\r\n" ;
	
	$temp = get_variable('email_from');												// 6/24/09
	if (empty($temp)) {
		$from_str = "Tickets_CAD" .'@' .$host ;
		}
	else {	
		$temp_ar = split ("@", $temp);
		if (count($temp_ar)==2) {
			$from_str = $temp;		// OK
			}
		else {
			$from_str = $temp_ar[0] . "@" . $host ;
			}
		}
		
//	$from = (empty($temp))?  "Tickets_CAD" : $temp;
	
	$headers = 'From:' .$from_str  . "\r\n" .
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
	$smtp = trim(get_variable('smtp_acct'));									// 7/7/09
	if (strlen($tostr)>0) {	
		if (strlen($smtp)==0) {
			@mail($tostr, $subject_str, $text_str, $headers);
			}
		else {
			smtp ($tostr, $subject_str, $text_str, $smtp, $from_str);						// ($my_to, $my_subject, $my_message, $my_params)
			}
		$caption = "Email sent";
		}
	if (strlen($tocellstr)>0) {
		$lgth = 140;
		$ix = 0;
		$i = 1;
		while (substr($text_str, $ix , $lgth )) {								// chunk to $lgth-length strings
			$subject_ex = $subject_str . "/part " . $i . "/";					// 10/21/08
			if (strlen($smtp)==0) {			
				mail($tocellstr, $subject_ex, substr ($text_str, $ix , $lgth ), $headers);
				}
			else {
				smtp ($tocellstr, $subject_ex, substr ($text_str, $ix , $lgth ), $smtp, $from_str);	// ($my_to, $my_subject, $my_message, $my_params, $my_from)
				}
			if($i>1) {sleep ($sleep);}								// 10/17/08
			$ix+=$lgth;
			$i++;
			}
		$caption .= " - Cell mail sent";
		}
	return $caption;
	}					// end function do send ()

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
	return (empty($addrs))? FALSE: $addrs;
	}


function snap($source, $stuff) {																// 10/18/08 , 3/5/09 - debug tool
	$table_name = "_snap_data";
	if (mysql_table_exists($table_name)) {
		$query	= "DELETE FROM `$table_name` WHERE `when`< (NOW() - INTERVAL 1 DAY)"; 		// first remove old
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	
//		$query = "INSERT INTO `$GLOBALS[mysql_prefix]_test` (`source`,`stuff`) VALUES('$source', '$stuff')";
		$query = sprintf("INSERT INTO `$table_name` (`source`,`stuff`)  
			VALUES(%s,%s)",
				quote_smart_deep(trim($source)),
				quote_smart_deep(trim($stuff)));
	
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		unset($result);
		}
	}		// end function snap()
	
function isFloat($n){														// 1/23/09
    return ( $n == strval(floatval($n)) )? true : false;
	}
	
function quote_smart($value) {												// 1/28/09
	if (get_magic_quotes_gpc()) {		// Stripslashes
		$value = stripslashes($value);
		}
	if (!is_numeric($value)) {			// Quote if not a number or a numeric string
		$value = "'" . mysql_real_escape_string($value) . "'";
		}
	return $value;
	}

function quote_smart_deep($value) {		// recursive array-capable version of the above 
    $value = is_array($value) ? array_map('quote_smart_deep', $value) : quote_smart($value);
    return $value;
	}

function db_insert($table, $fieldset){				// 2/4/09
	return 'INSERT INTO ' . $table . '(' . implode(',', array_keys($fieldset)) . ') VALUES (' . implode(',', array_values($fieldset)) . ')';
	}
function db_delete($table, $where = ''){
	return 'DELETE FROM ' . $table . ($where ? ' WHERE ' . $where : '');
	}
function db_update($table, $fieldset, $where = ''){
	$set = array();
	foreach($fieldset as $field=>$value) $set[] = $field . '=' . $value;
	return 'UPDATE ' . $table . ' SET ' . implode(',', $set) . ($where ? ' WHERE ' . $where : '');
	}
function get_mysession() {							// returns session array 2/19/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '". get_sess_key() . "'  LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	return mysql_fetch_assoc($result);
	}

function do_instam($key_val) {				// 3/17/09
//	snap(basename(__FILE__) . __LINE__, $key_val);
	// http://www.instamapper.com/api?action=getPositions&key=4899336036773934943
	// housekeep 

//	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `instam`= 1 AND `callsign` <> ''";  // work each call/license
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `instam`= 1 AND `callsign` <> ''";  				// work each call/license, 8/10/09
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
//	snap(basename(__FILE__) . __LINE__, $query);
	
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$query	= "SELECT `id`,`utc_stamp` FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` = '{$row['callsign']}' ORDER BY `utc_stamp` DESC LIMIT 1";		// work each call/license
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		$row_tr = (mysql_affected_rows()>0)? mysql_fetch_assoc($result): FALSE;
		
//		$from_utc = ($row_tr)?  "&from_ts=" . $row_tr['utc_stamp']: "";		// 3/26/09
		$from_utc = "";											// reconsider for tracking
		
		$url = "http://www.instamapper.com/api?action=getPositions&key={$key_val}{$from_utc}";
//		snap(basename(__FILE__) . __LINE__, $url);
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec ($ch);
			curl_close ($ch);
//			print __LINE__ ;
			}
		else {				// not CURL
//			print __LINE__ ;
			if ($fp = @fopen($url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error 1";		// @fopen fails
				}
			}
				
	/*
	InstaMapper API v1.00
	1263013328977,bold,1236239763,34.07413,-118.34940,25.0,0.0,335
	1088203381874,CABOLD,1236255869,34.07701,-118.35262,27.0,0.4,72
	*/
	
	$ary_data = explode ("\n", $data);
	if (count($ary_data) > 1) {
		for ($i=1; $i<count($ary_data)-2; $i++) {
//			snap(basename(__FILE__) . __LINE__, $ary_data[$i]);
		
			$str_pos = explode (",", $ary_data[$i]);
			if (count($str_pos)==8) {
//				snap(basename(__FILE__) . __LINE__, count($str_pos));

				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
					`lat`=		" . quote_smart(trim($str_pos[3])) . ",
					`lng`=		" . quote_smart(trim($str_pos[4])) . ",
					`updated` = " .	quote_smart(mysql_format_date(trim($str_pos[2]))) . "
					WHERE `instam` = 1 and `callsign` = " . quote_smart(trim($str_pos[0]));		// 7/25/09

				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
				
																									// 3/19/09
				$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source`= " . quote_smart(trim($str_pos[1]));		// remove prior track this device  3/20/09
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
											// 
				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh`(`source`,`utc_stamp`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
									VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
										quote_smart($str_pos[1]),
										quote_smart($str_pos[2]),
										quote_smart($str_pos[3]),
										quote_smart($str_pos[4]),
										quote_smart($str_pos[7]),
										quote_smart($str_pos[6]),
										quote_smart($str_pos[5]),
										quote_smart(mysql_format_date($str_pos[2])),
										quote_smart($str_pos[6])) ;
		//		dump($query);
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);					
				unset($result);
					
				}		// end if (count())


			}		// end for ()
		}		// end if (count())
	
		}		// end while
	}		// end function do_instam()

function do_gtrack() {			//7/29/09
	$gtrack_url = get_variable('gtrack_url');
//	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `gtrack`= 1 AND `callsign` <> ''";  // work each call/license
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `gtrack`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
	$tracking_id = ($row['callsign']);
	$exist_lat = ($row['lat']);
	$exist_lng = ($row['lng']);
	$exist_updated = ($row['updated']);
	$update_error = strtotime('now - 1 hour');

		$request_url = $gtrack_url . "/data.php?userid=$tracking_id";		//gtrack_url set by entry in settings table
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($request_url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error 1";		// @fopen fails
				}
			}

		$xml = new SimpleXMLElement($data);

		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];
		$alt = $xml->marker['alt'];
		$date = $xml->marker['local_date'];
		if ($date != "") {
			list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day 8/3/09
			$date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
			$time = $xml->marker['local_time'];
			$time = date("H:i:s", strtotime($time));	// format as mySQL time
			$updated = $date . " " . $time;	// create updated datetime
		}
		$mph = $xml->marker['mph'];
		$kph = $xml->marker['kph'];
		$heading = $xml->marker['heading'];

		if (!empty($lat) && !empty($lng)) {		//check not NULL
	
			if ($exist_lat<>$lat && $exist_lng<>$lng) {	// check for change in position

				if(($exist_updated == $updated) && ($update_error > $updated)) {
				} else {

				$query	= "DELETE FROM $GLOBALS[mysql_prefix]tracks WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
				$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				unset($resultd);
	
				$query = "UPDATE $GLOBALS[mysql_prefix]responder SET lat = '$lat', lng ='$lng', updated	= '$updated' WHERE callsign = '$user_id'";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query = "DELETE FROM $GLOBALS[mysql_prefix]tracks_hh WHERE source = '$user_id'";	// remove prior track this device
				$result = mysql_query($query);
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks_hh (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}	//end if
			}	//end if
		}	//end if
	}	// end while
}	// end function do_gtrack()

function do_locatea() {				//7/29/09
	
//	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `locatea`= 1 AND `callsign` <> ''";  // work each call/license
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `locatea`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
	$tracking_id = ($row['callsign']);
	$exist_lat = ($row['lat']);
	$exist_lng = ($row['lng']);
	$exist_updated = ($row['updated']);
	$update_error = strtotime('now - 4 hours');

		$request_url = "http://www.locatea.net/data.php?userid=$tracking_id";
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($request_url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error 1";		// @fopen fails
				}
			}

		$xml = new SimpleXMLElement($data);

		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];
		$alt = $xml->marker['alt'];
		$date = $xml->marker['local_date'];
		if ($date != "") {
			list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day	8/3/09
			$date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
			$time = $xml->marker['local_time'];
			$time = date("H:i:s", strtotime($time));	// format as mySQL time
			$updated = $date . " " . $time;	// create updated datetime
		}
		$mph = $xml->marker['mph'];
		$kph = $xml->marker['kph'];
		$heading = $xml->marker['heading'];

		if (!empty($lat) && !empty($lng)) {		//check not NULL
	
			if ($exist_lat<>$lat && $exist_lng<>$lng) {	// check for change in position

				if(($exist_updated == $updated) && ($update_error > $updated)) {
				} else {
	
				$query	= "DELETE FROM $GLOBALS[mysql_prefix]tracks WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
				$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				unset($resultd);
	
				$query = "UPDATE $GLOBALS[mysql_prefix]responder SET lat = '$lat', lng ='$lng', updated	= '$updated' WHERE callsign = '$user_id'";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query = "DELETE FROM $GLOBALS[mysql_prefix]tracks_hh WHERE source = '$user_id'";		// remove prior track this device
				$result = mysql_query($query);
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks_hh (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}	//end if
			}	//end if
		}	//end if
	}	// end while
}	// end function do_locatea()

function do_glat() {			//7/29/09

	function get_remote($url) {				// 8/9/09
		
			$data="";
			if (function_exists("curl_init")) {
				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$data = curl_exec($ch);
				curl_close($ch);
				return ($data)?  json_decode($data): FALSE;			// FALSE if fails
				}
			else {				// no CURL
				if ($fp = @fopen($url, "r")) {
					while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
					fclose($fp);
					}		
				else {
//					print "-error 1";		// @fopen fails
					return FALSE;		// @fopen fails
					}
				}
	
		return json_decode($data);
	
		}	// end function get_remote()


//	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `glat`= 1 AND `callsign` <> ''";  // work each call/license
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `glat`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$user = ($row['callsign']);
		$exist_lat = ($row['lat']);
		$exist_lng = ($row['lng']);
		$exist_updated = ($row['updated']);
		$update_error = strtotime('now - 1 hour');
	
		$ret_val = array("", "", "", "");
		$the_url = "http://www.google.com/latitude/apps/badge/api?user={$user}&type=json";
/*
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $the_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($data);
*/
		$json = get_remote($the_url);
	
	//	dump($json);
		error_reporting(0);
		foreach ($json as $key => $value) {				// foreach 1
		    $temp = $value;
			foreach ($temp as $key1 => $value1) {			// foreach 2
			    $temp = $value1;
				foreach ($temp as $key2 => $value2) {			// foreach 3
					$temp = $value2;
					foreach ($temp as $key3 => $value3) {			// foreach 4
						switch (strtolower($key3)) {
							case "id":
								$ret_val[0] = $value3;
							    break;
							case "timestamp":
								$ret_val[1] = $value3;
							    break;
							case "coordinates":
								$ret_val[2] = $value3[0];
								$ret_val[3] = $value3[1];
							    break;
							}		// end switch()
						}		// end for each()
			    	}		// end for each()
				}		// end for each()
			}		// end foreach 1
		error_reporting(E_ALL);
	
		if ((empty($ret_val[0])) || ((empty($ret_val[1])))  || (!(my_is_float($ret_val[2] ))) || (!(my_is_float($ret_val[3])))) {
			break;
			}
		else {							// valid glat data
			$lat = $ret_val[3];
			$lng = $ret_val[2];
			$glat_id = $ret_val[0];
			$timestamp = $ret_val[1];
			$updated = date('Y-m-d H:i:s', $timestamp);
		
			if (!empty($lat) && !empty($lng)) {		//check not NULL
			
				if ($exist_lat<>$lat && $exist_lng<>$lng) {	// check for change in position
		
					if(($exist_updated == $updated) && ($update_error > $updated)) {
						} 
					else {		
						$query	= "DELETE FROM $GLOBALS[mysql_prefix]tracks WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
						$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						unset($resultd);
				
						$query = "UPDATE $GLOBALS[mysql_prefix]responder SET lat = '$lat', lng ='$lng', updated	= '$updated' WHERE callsign = '$glat_id'";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$query = "DELETE FROM $GLOBALS[mysql_prefix]tracks_hh WHERE source = '$glat_id'";		// remove prior track this device  
						$result = mysql_query($query);
				
						$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks_hh (source, latitude, longitude, updated) VALUES ('$glat_id', '$lat', '$lng', '$updated')";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				
						$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks (source, latitude, longitude,packet_date, updated) VALUES ('$glat_id', '$lat', '$lng', '$updated', '$updated')";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						}			//end if/else
					}			//end if
				}			//end if
			}			// end if/else()
		}			// end while()

	}		// end function do_glat();

function get_current() {		// 3/16/09, 7/25/09
	$delay = 1;			// minimum time in minutes between  queries - 7/25/09
	$when = get_variable('_aprs_time');				// misnomer acknowledged
	if(time() < $when) { 
		return;
		} 
	else {
		$next = time() + $delay*60;
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$next' WHERE `name`='_aprs_time'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}

	$aprs = $instam = $locatea = $gtrack = $glat = FALSE;	// 3/22/09
	
	$query = "SELECT `id`, `aprs`, `instam`, `locatea`, `gtrack`, `glat` FROM `$GLOBALS[mysql_prefix]responder`WHERE ((`aprs` = 1) OR (`instam` = 1) OR (`locatea` = 1) OR (`gtrack` = 1) OR (`glat` = 1))";	
	$result = mysql_query($query) or do_error($query, ' mysql error=', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ($row['aprs'] = 1) 	{ $aprs = TRUE;}
		if ($row['instam'] = 1) { $instam = TRUE;}
		if ($row['locatea'] = 1) { $locatea = TRUE;}		//7/29/09
		if ($row['gtrack'] = 1) { $gtrack = TRUE;}		//7/29/09
		if ($row['glat'] = 1) { $glat = TRUE;}			//7/29/09
		}		// end while ()
	unset($result);
	if ($aprs) 		{do_aprs();}
	if ($instam) {	
		$temp = get_variable("instam_key");
		$instam = ($temp=="")? FALSE: $temp;
		if ($instam )	{do_instam($temp);}
		}
//	dump($glat);
	if ($locatea) 	{do_locatea();}					//7/29/09
	if ($gtrack) 		{do_gtrack();}				//7/29/09
	if ($glat) 		{do_glat();}					//7/29/09
	return array("aprs" => $aprs, "instam" => $instam, "locatea" => $locatea, "gtrack" => $gtrack, "glat" => $glat);		//7/29/09
	
	}		// end function

function my_is_float($n){									// 5/4/09
    return ( $n == strval(floatval($n)) && (!($n==0)) )? true : false;
	}

function my_is_int($n){										// 3/25/09
    return ( $n == strval(intval($n)) )? true : false;
	}

function LLtoOSGB($lat, $lng) {

	$ll2w = new LatLng($lat, $lng);
	$ll2w->WGS84ToOSGB36();
	$os2w = $ll2w->toOSRef($lat, $lng);
	$osgrid = $os2w->toSixFigureString();

	return $osgrid;
}	//end function do_latlngtoosgb

?>
