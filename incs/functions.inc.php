<?php
error_reporting(E_ALL ^ E_STRICT);

$theTimezone = "America/New_York";
date_default_timezone_set($theTimezone);
$https = (array_key_exists('HTTPS', $_SERVER)) ? 1 : 0;
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
3/25/09 added $GLOBALS['TOLERANCE']  for remote time validity determination, function my_is_float(), my_is_int()
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
9/29/09 Added additional $Globals for new log events and Status Special
10/20/09 Added function remove_nls to strip new lines from database entries for use in JS tooltips.
11/7/09 E_DEPRECATED, is_email() redo for deprecated
11/20/09 revised show_log () for shortened field display and title
11/21/09 $_SESSION destroy added to logout
11/27/09 added no-edit option to function add_header()
12/13/09 force GLat badge hyphen
12/26/09 send 'logged in' flag
1/6/10 revised get_sess_key() to use userid in hash
1/7/10 added function my_date_diff()
1/8/10 NULL to user sid on logout
1/23/10 browser detect added
2/1/10 disallow guest email
2/6/10 moved get_status_sel() from FMP
2/7/10 correction for empty values - source TBD
2/8/10 added units and facilities color-coding and legend
2/18/10 'reply-to' correction
2/19/10 Set/Get_Cookie() added
3/8/10 added session vbls to show/hide facilities and  unavailable units
3/13/10 added function is_phone ()
3/21/10 added function get_unit_status_legend()
3/25/10 added function get_un_div_height (), log_codes.inc
3/30/10 relocated 'dispatch' link
4/4/10 session_start added 2 places
4/27/10 added show/hide unavailable units - per AF mail
4/29/10 session_destroy() to force CB frame reload on timeout, reload top frame
4/30/10 added addr string with ticket descr
5/2/10  added get_start(), get_end(), misc date functions
5/4/10 $_SESSION['internet'] added
5/13/10 re-do my_date_diff()
6/17/10 applied intval() to delta_mins
6/24/10 round instam speed
6/25/10 'member' login supported as guest
6/26/10 911 contact information added
7/2/10 functions is_member(), may_email() added, allow upper case email addr elements
7/5/10 smtp revised to accomodate security protocol- per Kurt Jack
7/6/10 function show_assigns() per AH
7/10/10 added function get_cb_height ()
7/12/10 added level 'unit'
7/15/10 'NULL' corrections
7/21/10 remove dead 'reserved' tickets
7/26/10 unit login to term page
7/27/10 handle undefined session key
7/28/10 deletion error suppress
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/5/10 auto-detect new install - moved to index.php
8/10/10 logout user sql corrections applied, try/catch applied to cb/frame
8/13/10 glat hyphen drop
8/25/10 session housekeeping corrected, expires format changed to integer, logout() relocated to LIP
8/27/10 UK date format per AH, operator ticket edit test added
8/29/10 added get_disp_status()
9/22/10 has_admin()added
9/29/10 mysql2timestamp typecast and drop ldg zeros, added do_diff(), require_once => require
10/2/10 added function short_ts() - timestamp trimmer
10/5/10 added function set_u_updated ()
10/19/10 u2fenr reference correction
11/14/10 fix occasional 'Undefined index: user_id'
11/16/10 added check for locale for UK/OZ phone number format.
11/24/10 added function get_dist_factor()
11/26/10 functions get_speed(),  get_remote() added
11/29/10 locale == 2 handling added
11/26/10 added function get_remote()
11/30/10 added function get_hints()
12/03/10 added require status_cats.inc.php.
12/4/10 added GLOBALS['CLOUD_SQL_STR']
3/15/11 added function replace quotes to replace double quotes with single in html strings to fix js complaint
3/15/11 revised text color on facility types yellow background to black from white.
3/15/11 Add function get_css to get css colors from table for revisable screen colors and day/night setting.
3/19/11 added function get_unit()
4/23/11 added JSON optional get_remote() param
5/22/11 added notify severity filter
5/25/11 log intrusion detection, shut_down() added
6/10/11 added functions for regional operation
7/6/11 OpenGTS, $GLOBALS['TRACK_NAMES' added
10/18/11 Added functions for receiving facility control on mobile page.
10/26/11 Added function is_admin - checks for administrator but not super.
3/11/12 added LOG_UNIT_TO_QUARTERS
3/22/12 added ICS 213 log entry
4/12/12 moved regions view control functions from individual files into FIP
6/18/12 added cases "S" and "T", and revised match string error notification
6/20/12 corrections to set_u_updated() re responder schema/sql
10/20/12 fixes to show_log()and get_disps() re handle, ordering
10/23/12 Additions to support message store and additional $GLOBALS for resource type in multi region allocations.
11/2/2012 corrects smtp address validation
11/13/2012 handle "U" as units list request
11/14/2012 realigned mail_it formal paramters to accommodate optional smsg_to_str
11/30/2012 significant re-do, dropping unixtimestamp in favor of strtotime.  Also see FMP
12/14/2012 corrections to case "S", if/else for cell messages, date string handling in function mail_it
3/4/2013 corrections to function format_date_2()
3/27/2013 AS revisions - $GLOBALS['NM_LAT_VAL'], function get_maptype_str () - used with GMaps V3
4/10/13 revised calling of KML files for GMaps V3
5/11/2013 revised do_error() logging
5/11/2013 fix to remove '_on' from set_u_updated () sql
5/20/2013 - rewrote get_elapsed_time with its calls, added function now_ts()
5/23/2013 - replaced nl2br with replace_newline
5/31/2013 message selector string housekeeping added
6/10/2013 fix to set_u_updated () re _from
7/3/2013 function mail_it () subject line corrected
7/10/13 Revisions to function show_actions( to correct failure to show patients if no actions.
8/9/13 Added globals colors for Warn Locations
8/28/13 Added Mail list notifies to function notify user
9/6/13 Added tracking type - mobile tracker for mobile screen
9/10/13 Added function show_unit_log() and function list_files(...)
9/10/13 Added Xastir APR tracking
4/7/2014 ICS message code revised
5/8/14 Revised call to format_sb_date_2 in function show_log to correct incorrect display.
*/
error_reporting(E_ALL);

//	{						-- dummy
//
if( !extension_loaded('mysql') ){
	require_once('mysql2i.class.php');
	}
require_once('istest.inc.php');
require_once('mysql.inc.php');
require_once("phpcoord.php");				// UTM converter
require_once("usng.inc.php");				// USNG converter 9/12/08
require_once("browser.inc.php");			// added 1/23/10
require_once("messaging.inc.php");			// added 10/23/12
require_once("member.inc.php");			// added 10/23/12

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09
error_reporting (E_ALL  ^ E_DEPRECATED);

define ('NOT_STR', '*not*');
define ('NA_STR', '*na*');
define ('ADM_STR', 'Admin');
define ('SUPR_STR', 'Super');				// added 6/16/08

/* constants - do NOT change */
$GLOBALS['STATUS_RESERVED'] 		= 0;		// 10/24/08
$GLOBALS['STATUS_CLOSED'] 			= 1;
$GLOBALS['STATUS_OPEN']   			= 2;
$GLOBALS['STATUS_SCHEDULED']   		= 3;

$GLOBALS['NOTIFY_ACTION'] 			= "Added Action/Patient";
$GLOBALS['NOTIFY_TICKET'] 			= 'Ticket Update';
$GLOBALS['ACTION_DESCRIPTION']		= 1;
$GLOBALS['ACTION_OPEN'] 			= 2;
$GLOBALS['ACTION_CLOSE'] 			= 3;
$GLOBALS['PATIENT_OPEN'] 			= 4;
$GLOBALS['PATIENT_CLOSE'] 			= 5;

$GLOBALS['NOTIFY_TICKET_CHG'] 		= 0;		// 10/22/08
$GLOBALS['NOTIFY_ACTION_CHG'] 		= 1;
$GLOBALS['NOTIFY_PERSON_CHG'] 		= 2;
$GLOBALS['NOTIFY_TICKET_OPEN'] 		= 3;
$GLOBALS['NOTIFY_TICKET_CLOSE'] 	= 4;

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
$GLOBALS['LEVEL_ADMINISTRATOR']		= 1;
$GLOBALS['LEVEL_USER'] 				= 2;
$GLOBALS['LEVEL_GUEST'] 			= 3;
$GLOBALS['LEVEL_MEMBER'] 			= 4;		// 12/15/08
$GLOBALS['LEVEL_UNIT'] 				= 5;		// 7/8/09
$GLOBALS['LEVEL_STATS'] 			= 6;		// 7/6/11
$GLOBALS['LEVEL_SERVICE_USER'] 		= 7;		// 10/23/12
$GLOBALS['LEVEL_FACILITY'] 			= 8;		// 04/08/12
$GLOBALS['LEVEL_MANAGER'] 			= 8;		// 04/08/12

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
$GLOBALS['LOG_UNIT_TO_QUARTERS']	=23;		// 3/11/12
$GLOBALS['LOG_UNIT_COMMENT']   		=24;		// 3/18/15

$GLOBALS['LOG_MEMBER_STATUS']		=120;
$GLOBALS['LOG_MEMBER_COMPLETE']		=121;
$GLOBALS['LOG_MEMBER_CHANGE']		=122;
$GLOBALS['LOG_MEMBER_ADD']			=123;
$GLOBALS['LOG_MEMBER_TYPE']			=124;

$GLOBALS['LOG_CALL_EDIT']			=29;		// 6/17/11
$GLOBALS['LOG_CALL_DISP']			=30;		// 1/20/09
$GLOBALS['LOG_CALL_RESP']			=31;
$GLOBALS['LOG_CALL_ONSCN']			=32;
$GLOBALS['LOG_CALL_CLR']			=33;
$GLOBALS['LOG_CALL_RESET']			=34;		// 7/7/09

$GLOBALS['LOG_CALL_REC_FAC_SET']	=35;		// 9/29/09
$GLOBALS['LOG_CALL_REC_FAC_CHANGE']	=36;		// 9/29/09
$GLOBALS['LOG_CALL_REC_FAC_UNSET']	=37;		// 9/29/09
$GLOBALS['LOG_CALL_REC_FAC_CLEAR']	=38;		// 9/29/09

$GLOBALS['LOG_FACILITY_ADD']		=40;		// 9/22/09
$GLOBALS['LOG_FACILITY_CHANGE']		=41;		// 9/22/09
$GLOBALS['LOG_FACILITY_STATUS']		= 4040;

$GLOBALS['LOG_FACILITY_INCIDENT_OPEN']	=42;		// 9/29/09
$GLOBALS['LOG_FACILITY_INCIDENT_CLOSE']	=43;		// 9/29/09
$GLOBALS['LOG_FACILITY_INCIDENT_CHANGE']=44;		// 9/29/09

$GLOBALS['LOG_CALL_U2FENR']			=45;		// 9/29/09
$GLOBALS['LOG_CALL_U2FARR']			=46;		// 9/29/09

$GLOBALS['LOG_FACILITY_DISP']		=47;		// 9/22/09
$GLOBALS['LOG_FACILITY_RESP']		=48;		// 9/22/09
$GLOBALS['LOG_FACILITY_ONSCN']		=49;		// 9/22/09
$GLOBALS['LOG_FACILITY_CLR']		=50;		// 9/22/09
$GLOBALS['LOG_FACILITY_RESET']		=51;		// 9/22/09

$GLOBALS['LOG_ICS_MESSAGE_SEND']	=60;		// 4/7/2014

$GLOBALS['LOG_ERROR']				=90;		// 1/10/11
$GLOBALS['LOG_INTRUSION']			=91;		// 5/25/11
$GLOBALS['LOG_ERRONEOUS']			=0;			// 1/10/11

$GLOBALS['LOG_SMSGATEWAY_CONNECT']	=1000;		// 10/23/12
$GLOBALS['LOG_SMSGATEWAY_SEND']		=1001;		// 10/23/12
$GLOBALS['LOG_SMSGATEWAY_RECEIVE']	=1002;		// 10/23/12

$GLOBALS['LOG_EMAIL_CONNECT']		=1010;		// 10/23/12
$GLOBALS['LOG_EMAIL_SEND']			=1011;		// 10/23/12
$GLOBALS['LOG_EMAIL_RECEIVE']		=1012;		// 10/23/12

$GLOBALS['LOG_NEW_REQUEST']			=2010;		// 26/7/13
$GLOBALS['LOG_EDIT_REQUEST']		=2011;		// 26/7/13
$GLOBALS['LOG_CANCEL_REQUEST']		=3012;		// 26/7/13
$GLOBALS['LOG_ACCEPT_REQUEST']		=3013;		// 26/7/13
$GLOBALS['LOG_TENTATIVE_REQUEST']	=3014;		// 26/7/13
$GLOBALS['LOG_DECLINE_REQUEST']		=3015;		// 26/7/13

$GLOBALS['LOG_WARNLOCATION_ADD']	=4010;		// 8/9/13
$GLOBALS['LOG_WARNLOCATION_CHANGE']	=4013;		// 8/9/13
$GLOBALS['LOG_WARNLOCATION_DELETE']	=4014;		// 8/9/13

$GLOBALS['LOG_BROADCAST_MESSAGE'] 	=5000;		//	11/30/15
$GLOBALS['LOG_BROADCAST_ALERT'] 	=5001;		//	11/30/15
$GLOBALS['LOG_BROADCAST_ERROR'] 	=5099;		//	11/30/15
$GLOBALS['LOG_SYSTEM_MESSAGE'] 		=5999;		//	07/06/16

$GLOBALS['SOCKET_MESSAGETYPE_STANDARD'] 	= 1;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_ERROR'] 		= 99;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_STARTSTOP'] 	= 199;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_INCUPDATE'] 	= 21;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_RESPUPDATE'] 	= 22;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_RESPSTATUS'] 	= 23;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_POSUPDATE'] 	= 24;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_FACPUPDATE'] 	= 25;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_CHATID'] 		= 26;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_DISPUPDATE'] 	= 27;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_REQUPDATE'] 	= 28;		//	12/16/15
$GLOBALS['SOCKET_MESSAGETYPE_OSWUPDATE'] 	= 29;		//	12/16/15

$GLOBALS['LOG_SPURIOUS']			=127;		// 10/24/13 Added to catch failed logs

$GLOBALS['icons'] = array("black.png", "blue.png", "green.png", "red.png", "white.png", "yellow.png", "gray.png", "lt_blue.png", "orange.png");
$GLOBALS['sm_icons']	= array("sm_black.png", "sm_blue.png", "sm_green.png", "sm_red.png", "sm_white.png", "sm_yellow.png", "sm_gray.png", "sm_lt_blue.png", "sm_orange.png");
$GLOBALS['fac_icons'] = array("square_red.png", "square_black.png", "square_white.png", "square_yellow.png", "square_blue.png", "square_green.png", "shield_red.png", "shield_grey.png", "shield_green.png", "shield_blue.png", "shield_orange.png");
$GLOBALS['sm_fac_icons'] = array("sm_square_red.png", "sm_square_black.png", "sm_square_white.png", "sm_square_yellow.png", "sm_square_blue.png", "sm_square_green.png", "sm_shield_red.png", "sm_shield_grey.png", "sm_shield_green.png", "sm_shield_blue.png", "sm_shield_orange.png");


$GLOBALS['SESSION_TIME_LIMIT']		= 60*480;		// minutes of inactivity before logout is forced - 1/18/10
$GLOBALS['TOLERANCE']				= 180*60;		// seconds of deviation from UTC before remotes sources considered not current - 3/25/09

$GLOBALS['TRACK_NONE']			=0;     	// 12/3/10
$GLOBALS['TRACK_APRS']			=1;     	// 7/8/09
$GLOBALS['TRACK_INSTAM']		=2;
$GLOBALS['TRACK_GTRACK']		=3;
$GLOBALS['TRACK_LOCATEA']		=4;
$GLOBALS['TRACK_GLAT']			=5;
$GLOBALS['TRACK_OGTS']			=6;     	// 7/6/11
$GLOBALS['TRACK_T_TRACKER']		=7;  	 	//	5/11/11
$GLOBALS['TRACK_MOBILE']		=8;  	 	//	9/6/13
$GLOBALS['TRACK_XASTIR']		=9;  	 	//	1/30/14
$GLOBALS['TRACK_FOLLOWMEE']		=10;
$GLOBALS['TRACK_TRACCAR']		=11;
$GLOBALS['TRACK_JAVAPRSSRVR']	=12;

$GLOBALS['TRACK_2L']		= array("", "AP", "IN", "GT", "LO", "GL", "OG", "TT", "MT", "XA", "FM", "TR", "JA" ); 	// 7/6/11, 9/6/13, 1/30/14
$GLOBALS['TRACK_NAMES']		= array("", "APRS", "Instamapper", "GTrack", "LocateA", "Latitude", "OpenGTS", "Internal", "Mobile Tracker", "Xastir", "FollowMee", "Traccar", "Javaprssrvr" ); 	// 7/6/11, 9/16/13, 1/30/14

$GLOBALS['UNIT_TYPES_BG']	= array("#000000", "#5A59FF", "#63DB63", "#FF3C4A", "#FFFFFF", "#F7F363", "#C6C3C6", "#00FFFF");	// keyed to unit_types - 2/8/10
$GLOBALS['UNIT_TYPES_TEXT']	= array("#FFFFFF", "#FFFFFF", "#000000", "#000000", "#000000", "#000000", "#000000", "#000000");	// 2/8/10

$GLOBALS['FACY_TYPES_BG']	= array("#E72429", "#000000", "#E7E3E7", "#E7E321", "#5269BD", "#52BE52", "#C60000", "#7B7D7B", "#005D00", "#1000EF");	// keyed to fac_types - 2/8/10
$GLOBALS['FACY_TYPES_TEXT']	= array("#000000", "#FFFFFF", "#000000", "#000000", "#FFFFFF", "#000000", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF");	// 2/8/10, 02/05/11 - revised text color on yellow background to black.

$GLOBALS['CLOUD_SQL_STR'] = "`passwd` = '55606758fdb765ed015f0612112a6ca7'";		// 12/4/10

$GLOBALS['TYPE_TICKET'] 		= 1;	//	10/23/12
$GLOBALS['TYPE_UNIT'] 			= 2;	//	10/23/12
$GLOBALS['TYPE_FACILITY']		= 3;	//	10/23/12
$GLOBALS['TYPE_USER']			= 4;	//	10/23/12

$GLOBALS['MSGTYPE_OG_EMAIL']	= 1;	//	10/23/12
$GLOBALS['MSGTYPE_IC_EMAIL']	= 2;	//	10/23/12
$GLOBALS['MSGTYPE_OG_SMS']		= 3;	//	10/23/12
$GLOBALS['MSGTYPE_IC_SMS']		= 4;	//	10/23/12
$GLOBALS['MSGTYPE_IC_SMS_DR']	= 5;	//	10/23/12
$GLOBALS['MSGTYPE_IC_SMS_DF']	= 6;	//	10/23/12

$GLOBALS['NM_LAT_VAL'] 		= 0.999999;												// 3/27/2013

$GLOBALS['LOC_TYPES_NAMES']	= array('Violence','Frequent','Health','Environmental','General');
$GLOBALS['LOC_TYPES'] = array(0,1,2,3,4);		//	11/10/14
$GLOBALS['LOC_TYPES_BG']	= array('#FF0000','#000000','#FFFFFF','#FFFF00','#0000FF ');		//	11/10/14
$GLOBALS['LOC_TYPES_TEXT']	= array('#FFFFFF','#FFFFFF','#000000','#000000','#FFFFFF');		//	11/10/14
$GLOBALS['wl_icons'] = array("square_red.png", "square_black.png", "square_white.png", "square_yellow.png", "square_blue.png");
$GLOBALS['wl_sm_icons']	= array("sm_square_red.png", "sm_square_black.png", "sm_square_white.png", "sm_square_yellow.png", "sm_square_blue.png");

$evenodd = array ("even", "odd", "heading");	// class names for alternating table row css colors

/* connect to mysql database */

mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_passwd']);
/* if (!$connect) {
	die ("Connection attempt to MySQL failed - correction required in order to continue.");
	} */

mysql_select_db($GLOBALS['mysql_db']);
/* if (!$db_selected) {
	print "Connection attempt to database failed. Please run <a href=\"install.php\">install.php</a> with valid  database configuration information.";
	} */

/* if (!mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_passwd'])) {
	die ("Connection attempt to MySQL failed - correction required in order to continue.");
	}

if (!mysql_select_db($GLOBALS['mysql_db'])) {
	print "Connection attempt to database failed. Please run <a href=\"install.php\">install.php</a> with valid  database configuration information.";
	exit();
	} */

/* check for mysql tables, if non-existent, point to install.php */
$failed = 0;
if (!mysql_table_exists("$GLOBALS[mysql_prefix]user")) 		{ print "MySQL table '$GLOBALS[mysql_prefix]user' is missing<BR />"; $failed = 1; 	}
if ($failed) {
	print "One or more database tables is missing.  Please run <a href=\"install.php\">install.php</a> with valid database configuration information.";
	exit();
	}

$expiry = expires();		// note global

$timezone = (get_variable('timezone') != "") ? get_variable('timezone') : "America/New_York";
date_default_timezone_set($timezone);
$internet = intval(get_variable("internet"));

require_once ('login.inc.php');
require_once('status_cats.inc.php');

$useMdb = get_variable('use_mdb');
$useMdbContact = (get_mdb_variable('use_mdb_contact')) ? get_mdb_variable('use_mdb_contact'): 0;
$useMdbStatus = (get_mdb_variable('use_mdb_status')) ? get_mdb_variable('use_mdb_status') : 0;
$validStatuses = array();
$validFacStatuses = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status`";
$result = mysql_query($query);
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$validStatuses[$row['id']] = $row['status_val'];
		}
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status`";
$result = mysql_query($query);
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$validFacStatuses[$row['id']] = $row['status_val'];
		}
	}

function remove_nls($instr) {                // 10/20/09
	$nls = array("\r\n", "\n", "\r");        // note order
	return str_replace($nls, " ", $instr);
	}        // end function

function mysql_table_exists($name) {
	return boolVal ( mysql_num_rows(mysql_query("SHOW TABLES LIKE '{$name}'") )  > 0 );
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

function get_disp_closure_summary($tick_id) {
	$eol = PHP_EOL;
	$string = "";
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
		WHERE `ticket_id`='$tick_id' AND ((`clear` IS NOT NULL) AND (DATE_FORMAT(`clear`,'%y') != '00'))
		ORDER BY `id` ASC");		// 6/25/10
	if (mysql_affected_rows()>0) {
		while($row = mysql_fetch_assoc($result)) {
			$string .= "Unit: " . get_responder($row['responder_id']) . chr(0x0D).chr(0x0A);
			$string .= "D: " . format_sb_date_2($row['dispatched']) . chr(0x0D).chr(0x0A);
			$string .= "R: " . format_sb_date_2($row['responding']) . chr(0x0D).chr(0x0A);
			$string .= "O: " . format_sb_date_2($row['on_scene']) . chr(0x0D).chr(0x0A);
			if($row['u2fenr'] != NULL && $row['u2fenr'] != "0000-00-00 00:00:00") {
				$string .= "FENR: " . format_sb_date_2($row['u2fenr']) . chr(0x0D).chr(0x0A);
				}
			if($row['u2farr'] != NULL && $row['u2farr'] != "0000-00-00 00:00:00") {
				$string .= "FARR: " . format_sb_date_2($row['u2farr']) . chr(0x0D).chr(0x0A);
				}
			$string .= "C: " . format_sb_date_2($row['clear']) . chr(0x0D).chr(0x0A);
			}
		}
	return $string;
	}

function get_disps($tick_id, $resp_id) {				// 7/4/10, 10/20/12
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
		WHERE `ticket_id`='$tick_id' AND `responder_id` = '$resp_id'
		AND ((`dispatched` IS NOT NULL) 	AND (DATE_FORMAT(`dispatched`,'%y') != '00'))
		AND ((`responding` IS NULL) 		OR (DATE_FORMAT(`responding`,'%y') = '00'))
		AND ((`on_scene` IS NULL) 			OR (DATE_FORMAT(`on_scene`,'%y') = '00'))
		AND ((`clear` IS NULL) 				OR (DATE_FORMAT(`clear`,'%y') = '00'))
		ORDER BY `id` DESC LIMIT 1
		 ");		// 6/25/10
	if (mysql_affected_rows()>0) {
		$row = mysql_fetch_assoc($result);
		return "dispatched " . substr ($row['dispatched'] ,11 ,5 );
		}

	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
		WHERE `ticket_id`='$tick_id' AND `responder_id` = '$resp_id'
		AND ((`responding` IS NOT NULL) 	AND (DATE_FORMAT(`responding`,'%y') != '00'))
		AND ((`on_scene` IS NULL) 			OR (DATE_FORMAT(`on_scene`,'%y') = '00'))
		AND ((`clear` IS NULL) 				OR (DATE_FORMAT(`clear`,'%y') = '00'))
		ORDER BY `id` DESC LIMIT 1
		");		// 6/25/10
	if (mysql_affected_rows()>0) {
		$row = mysql_fetch_assoc($result);
		return "responding " . substr ($row['responding'] ,11 ,5 );
		}

	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
		WHERE `ticket_id`='$tick_id'  AND `responder_id` = '$resp_id'
		AND ((`on_scene` IS NOT NULL) 	AND (DATE_FORMAT(`dispatched`,'%y') != '00'))
		AND (`clear` IS NULL 				OR DATE_FORMAT(`clear`,'%y') = '00')
		ORDER BY `id` DESC LIMIT 1
		");
	if (mysql_affected_rows()>0) {
		$row = mysql_fetch_assoc($result);
		return "on_scene " . substr ($row['on_scene'] ,11 ,5 );
		}
		return "???? ";
	}

function show_assigns($which, $id_in){				// 10/20/12
	global $evenodd;
	$which_ar = array ("ticket_id", "responder_id");		//
	$as_query = "SELECT *,
		dispatched AS dispatched_i,
		responding AS responding_i,
		on_scene AS on_scene_i,
		u2fenr AS u2fenr_i,
		u2farr AS u2farr_i,
		clear AS clear_i,
		start_miles AS start_m,
		on_scene_miles AS os_miles,
		end_miles AS end_m,
		miles AS miles,
		`a`.`comments` AS `assigns_comments`,
		`r`.`handle`,
		`t`.`problemstart` AS `problemstart_i`
		FROM `$GLOBALS[mysql_prefix]assigns` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r`	ON (`r`.`id` = `a`.`responder_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`t`.`id` = `a`.`ticket_id`)
		WHERE `a`.`{$which_ar[$which]}` = {$id_in} ORDER BY `problemstart_i` DESC LIMIT 50";
	$as_result	= mysql_query($as_query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	$out_str = $the_handle = "";
	$i=0;		// line counter
	if (mysql_num_rows($as_result)){	//
		$tags_arr = explode("/", get_variable('disp_stat'));
		if (count($tags_arr)<6) {$tags_arr = explode("/", "Disp/Resp/OnS/FEnr/FArr/Clear");}		// protect against bad user setting

		$out_str = "\n<TABLE WIDTH='100%' ALIGN = 'center'><TR><TD COLSPAN=4 CLASS = 'heading text' ALIGN='center'><U>" . get_text("Dispatch") . " history</U></TD></TR>\n";
		while($row = stripslashes_deep(mysql_fetch_assoc($as_result))) {
			$start_miles = ($row['start_m'] != NULL) ? $row['start_m'] : "NA";
			$os_miles = ($row['os_miles'] != NULL) ? $row['os_miles'] : "NA";
			$end_miles = ($row['end_m'] != NULL) ? $row['end_m'] : "NA";
			$notes = $row['assigns_comments'];
			if($row['miles'] != NULL) {
				$tot_miles = $row['miles'];
				} elseif(($row['miles'] == NULL) && (($start_miles != "NA") && ($end_miles != "NA"))) {
				$tot_miles = intval($end_miles) - intval($start_miles);
				} else {
				$tot_miles = "NA";
				}
			$out_str .= "<TR><TD COLSPAN=4 CLASS = 'header text' ALIGN='center'>" . $row['scope'] . "</TD></TR>\n";
			$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>Start</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['problemstart_i'])) . "</TD></TR>\n"; $i++;
			if (is_date($row['dispatched'])) 	{
				$delta  = my_date_diff($row['problemstart_i'], $row['dispatched_i']);
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>{$tags_arr[0]}</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['dispatched_i'])) . "&nbsp;(" . $delta . ")</TD></TR>\n"; $i++;}
			if (is_date($row['responding'])) 	{
				$delta  = my_date_diff($row['problemstart_i'], $row['responding_i']);
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>{$tags_arr[1]}</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['responding_i'])) . "&nbsp;(" . $delta . ")</TD></TR>\n"; $i++;}
			if (is_date($row['on_scene'])) 		{
				$delta  = my_date_diff($row['problemstart_i'], $row['on_scene_i']);
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>{$tags_arr[2]}</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['on_scene_i'])) . "&nbsp;(" . $delta . ")</TD></TR>\n"; $i++;}
			if (is_date($row['u2fenr'])) 		{
				$delta  = my_date_diff($row['problemstart_i'], $row['u2fenr_i']);
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>{$tags_arr[3]}</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['u2fenr_i'])) . "&nbsp;(" . $delta . ")</TD></TR>\n"; $i++;}
			if (is_date($row['u2farr'])) 		{
				$delta  = my_date_diff($row['problemstart_i'], $row['u2farr_i']);
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>{$tags_arr[4]}</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['u2farr_i'])) . "&nbsp;(" . $delta . ")</TD></TR>\n"; $i++;}
			if (is_date($row['clear'])) 		{
				$delta  = my_date_diff($row['problemstart_i'], $row['clear_i']);
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>{$tags_arr[5]}</TD><TD CLASS='text text_normal text_left' COLSPAN=3>" . format_date_2(strtotime($row['clear_i'])) . "&nbsp;(" . $delta . ")</TD></TR>\n"; $i++;}
			if($notes != "" && $notes != "New") {
				$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_left' style='width: 25%;'>Notes:</TD><TD CLASS='td_data_wrap text text_normal text_left' COLSPAN=3>" . $notes . "</TD></TR>\n"; $i++;
				}
			$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_center' COLSPAN = '3'>Start Miles: {$start_miles}&nbsp;&nbsp;On Scene Miles: {$os_miles}&nbsp;&nbsp;End Miles: {$end_miles}</TD></TR>\n"; $i++;	//	1/28/13
			$out_str .= "<TR CLASS = '{$evenodd[$i%2]}'><TD CLASS='td_label text text_normal text_center' COLSPAN = '3'>TOTAL MILES: {$tot_miles}</TD></TR>\n"; $i++;	//	1/28/13
			}
		$out_str .= "</TABLE>\n";
		}
	return $out_str;
	}		// end function show_assigns()


function show_actions ($the_id, $theSort="date", $links, $display, $mode=0) {			/* list actions and patient data belonging to ticket */
	$print = "";
	$evenodd = array("even", "odd");
	if($display) {
		$evenodd = array ("plain", "plain");
		}
	$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$responderlist = array();
	$responderlist[0] = "NA";
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$responderlist[$act_row['id']] = $act_row['handle'];
		}
	$query = "SELECT *, `p`.`id` AS `pat_id`
		FROM `$GLOBALS[mysql_prefix]patient` `p`
 		LEFT JOIN `$GLOBALS[mysql_prefix]insurance` `i` ON (`i`.`id` = `p`.`insurance_id` )
 		WHERE `ticket_id`='{$the_id}' ORDER BY `date`";	//	7/10/13

	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = get_text("Patients");
	$pctr=0;
	$genders = array("", "M", "F", "T", "U");
	if(mysql_num_rows($result) > 0) {
		$print .= "<TABLE style='width: 100%;' ID='patients'>";	//	Patients Table
		$print .= "<TR CLASS='heading' style='width: 100%;'><TD CLASS='heading' COLSPAN=99 ALIGN='center'><U>{$caption}</U></TD></TR>";
		while ($pat_row = stripslashes_deep(mysql_fetch_assoc($result))){
			$the_gender = ($pat_row['gender'] != 0) ? $genders[$pat_row['gender']] : $genders[4];	//	7/12/13
			$tipstr = addslashes("Name: {$pat_row['name']}<br> Fullname: {$pat_row['fullname']}<br> DOB: {$pat_row['dob']}<br> Gender: {$the_gender}<br>  Insurance_id: {$pat_row['ins_value']}<br>    Facility_contact: {$pat_row['facility_contact']}<br>    Date: {$pat_row['date']}<br>Description:{$pat_row['description']}");
			$print .= "<TR CLASS='{$evenodd[$pctr%2]}' style='width: 100%; vertical-align: middle;' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\">";
			$print .= "<TD CLASS='text text_left' NOWRAP>{$pat_row['name']}</TD><TD CLASS='text text_left' NOWRAP> Z ". format_date_2($pat_row['updated']) . "</TD>";
			$print .= "<TD CLASS='text text_left text_bolder' NOWRAP> by ". get_owner($pat_row['user']);
			$print .= ($pat_row['action_type']!=$GLOBALS['ACTION_COMMENT'] ? "*" : "-")."</TD><TD CLASS='text text_left'>" . shorten($pat_row['description'], 24) . "</TD>";
			if ($links) {
				if($mode == 0) {
					$print .= "<TD CLASS='text'>&nbsp;[<A HREF='patient.php?ticket_id=$the_id&id={$pat_row['pat_id']}&action=edit'>edit</A> | <A HREF='patient.php?id=" . $pat_row['pat_id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</TD>";
					} elseif($mode ==1) {
					$print .= "<TD CLASS='text'>&nbsp;[<A HREF='patient_w.php?ticket_id=$the_id&id={$pat_row['pat_id']}&action=edit'>edit</A> | <A HREF='patient_w.php?id=" . $pat_row['pat_id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</TD>";
					} else {
					$print .= "<TD CLASS='text'>&nbsp;[<A HREF='#' onClick=\"open_pat_window(ticket_id=$the_id, {$pat_row['pat_id']}, 'edit');\">edit</A> | <A HREF='#' onClick=\"open_pat_window(ticket_id=$the_id, {$pat_row['pat_id']}, 'delete');\">delete</A>]</TD>";
					}
				} else {
				$print .= "<TD>&nbsp;</TD>";
				}
			$print .=  "<TD CLASS='text text_left'> Y ({$genders[$pat_row['gender']]}) - {$pat_row['fullname']} - Z{$pat_row['dob']}</TD><TD CLASS='text text_left'> A {$pat_row['ins_value']} - B{$pat_row['facility_contact']}</TD></TR>";
			$caption = "";				// once only
			$pctr++;
			}
		$print .= "</TABLE>";	//	End of Patients Table
		}
														/* list actions */
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id` = '$the_id' ORDER BY `date`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = get_text("Actions");
	$actr=0;
	if (mysql_num_rows($result) > 0) {
		$print .= "<TABLE style='width: 100%;' ID='actions'>";	//	Actions Table
		$print .= "<TR CLASS='heading' style='width: 100%;'><TD CLASS='heading' COLSPAN=99 ALIGN='center'><U>{$caption}</U></TD></TR>";
		while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
			$tipstr = addslashes(replace_newline($act_row['description']));
			$print .= "<TR CLASS='{$evenodd[$actr%2]}' style='width: 100%;' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\">";
			$responders = explode (" ", trim($act_row['responder']));	// space-separated list to array
			$sep = $respstring = "";
			for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
				if (array_key_exists($responders[$i], $responderlist)) {
					$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
					$sep = "<BR />";
					} else {
					$respstring .= "&nbsp;";
					}
				}
			$print .= "<TD CLASS='text text_left' NOWRAP>" . $respstring . "</TD><TD CLASS='text text_left' NOWRAP> ". format_date_2($act_row['updated']) ." </TD>";	//	3/15/11
			$print .= "<TD CLASS='text text_left' NOWRAP> by <B>".get_owner($act_row['user'])." </B> ";	//	3/15/11
			$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'])? '*' : '-';
			$print .= "</TD><TD CLASS='text text_left'>" . replace_newline($act_row['description']) . "</TD>";	//	3/15/11
			if ($links) {
				if($mode == 0) {
					$print .= "<TD CLASS='text'><NOBR>&nbsp;[<A HREF='action.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A> | <A HREF='action.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</NOBR></TD>";
					} elseif($mode ==1) {
					$print .= "<TD CLASS='text'>&nbsp;[<A HREF='action_w.php?ticket_id=$the_id&id={$act_row['id']}&action=edit'>edit</A> | <A HREF='action.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</TD>\n";
					} else {
					$print .= "<TD CLASS='text'>&nbsp;[<A HREF='#' onClick=\"open_act_window(ticket_id=$the_id, {$act_row['id']}, 'edit');\">edit</A> | <A HREF='#' onClick=\"open_act_window(ticket_id=$the_id, {$act_row['id']}, 'delete');\">delete</A>]</TD>\n";
					}
				}
			$caption = "";
			$actr++;
			}				// end while (...)
		$print .= "</TABLE>";	//	End of Actions Table
		}
	return $print;
	}			// end function show_actions

function list_messages($the_id, $theSort="date", $links, $display) {
	$print = "";
	if(get_variable('use_messaging') != 0) {
		$evenodd = array ("even", "odd");		// class names for display table row colors
		$actr=1;
		$print = "<TABLE WIDTH='100%'>";
		$print .= "<TR><TD CLASS='heading text text_center text_bold' COLSPAN=99><U>Messages</U></TD></TR>";
		$print .= "<TR CLASS='odd' STYLE='width: 98%;'><TD CLASS='td_label text text_left text_bold'>Type</TD><TD CLASS='td_label text text_left text_bold'>To</TD><TD CLASS='td_label text text_left text_bold'>From</TD><TD CLASS='td_label text text_left text_bold'>Subject</TD><TD CLASS='td_label text text_left text_bold'>Message</TD><TD CLASS='td_label text text_left text_bold' WIDTH='10%'>Date</TD></TR>";
		$actr++;
		$query_messages = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `ticket_id`= " . $the_id . " ORDER BY '" . $theSort . "' ASC;";
		$result_messages = mysql_query($query_messages) or do_error($query_messages, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if(mysql_num_rows($result_messages) == 0) {
			$print .= "<TR CLASS='{$evenodd[$actr%2]}'><TD ALIGN='center' COLSPAN='99'>No Messages</TD></TR>";
			} else {
			while ($row_messages = mysql_fetch_assoc($result_messages))	{
				if ($row_messages['msg_type'] == 1) {
					$type_flag = "Outoging Email";
					$type = 1;
					$color = "background-color: blue; color: white;";
					} elseif ($row_messages['msg_type'] ==2) {
					$type_flag = "Incoming Email";
					$type = 2;
					$color = "background-color: white; color: blue;";
					} elseif ($row_messages['msg_type'] ==3) {
					$color = "background-color: orange; color: white;";
					$type_flag = "Outgoing SMS";
					$type = 3;
					} elseif (($row_messages['msg_type'] ==4) || ($row_messages['msg_type'] ==5) || ($row_messages['msg_type'] ==6)) {
					$color = "background-color: white; color: orange;";
					$type_flag = "Incoming SMS";
					$type = 4;
					} else {
					$color = "";
					$type_flag = "?";
					$type = 99;
					}
				$print .= "<TR CLASS='{$evenodd[$actr%2]}'><TD CLASS='td_data_wrap text text_normal text_left'>" . $type_flag . "</TD>";
				$print .= "<TD CLASS='td_data_wrap text text_normal text_left'>" . stripslashes_deep(shorten($row_messages['recipients'], 18)) . "</TD>";
				$print .= "<TD CLASS='td_data_wrap text text_normal text_left'>" . $row_messages['fromname'] . "</TD>";
				$print .= "<TD CLASS='td_data_wrap text text_normal text_left'>" . stripslashes_deep(shorten($row_messages['subject'], 18)) . "</TD>";
				$print .= "<TD CLASS='td_data_wrap text text_normal text_left'>" . stripslashes_deep(shorten($row_messages['message'], 100)) . "</TD>";
				$print .= "<TD CLASS='td_data text text_normal text_left'>" . format_date_2(strtotime($row_messages['date'])) . "</TD></TR>";
				$actr++;
				}
			}
		$print .= "</TABLE>";
		}
	return $print;
	}	//	End of function Show Messages

function show_actions_orig ($the_id, $theSort="date", $links, $display) {			/* list actions and patient data belonging to ticket */
	if ($display) {
		$evenodd = array ("even", "odd");		// class names for display table row colors
		}
	else {
		$evenodd = array ("plain", "plain");	// print
		}
	$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$responderlist = array();
	$responderlist[0] = "NA";
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$responderlist[$act_row['id']] = $act_row['handle'];
		}
	$print = "<TABLE BORDER='0' ID='patients' width=" . max(320, intval($_SESSION['scr_width']* 0.4)) . ">";
																	/* list patients */
	$query = "SELECT *,
		`date` AS `date`,
		`updated` AS `updated`,
		`p`.`id` AS `patient_id`
		FROM `$GLOBALS[mysql_prefix]patient` `p`
 		LEFT JOIN `$GLOBALS[mysql_prefix]insurance` `i` ON (`i`.`id` = `p`.`insurance_id` )
 		WHERE `ticket_id`='{$the_id}' ORDER BY `date`";

	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = get_text("Patient") . ": &nbsp;&nbsp;";
	$actr=0;
//	$genders = array("M", "F", "T", "U");
	$genders = array("", "M", "F", "T", "U");
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$the_gender = $genders[$act_row['gender']];
		$the_patient_id = $act_row['patient_id'];

		$tipstr = addslashes("Name: {$act_row['name']}<br> Fullname: {$act_row['fullname']}<br> DOB: {$act_row['dob']}<br> Gender: {$the_gender}<br>Insurance_id: {$act_row['ins_value']}<br>Facility_contact: {$act_row['facility_contact']}<br>    Date: {$act_row['date']}<br>    Description: {$act_row['description']}");

		$print .= "<TR CLASS='{$evenodd[$actr%2]}' WIDTH='100%'  onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\">
			<TD VALIGN='top' NOWRAP CLASS='td_label'>" . $caption . "</TD>";
		$print .= "<TD NOWRAP>" . $act_row['name'] . "</TD><TD NOWRAP>". format_date_2($act_row['updated']) . "</TD>";
		$print .= "<TD NOWRAP> by <B>".get_owner($act_row['user'])."</B>";

		$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'] ? "*" : "-")."</TD>
			<TD>" . shorten($act_row['description'], 24) . "</TD>";

		if ($links) {
			$print .= "<TD>&nbsp;[<A HREF='patient.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
				<A HREF='patient.php?id=$the_patient_id&ticket_id=$the_id&action=delete'>delete</A>]</TD></TR>\n";
				}
		$caption = "";				// once only
		$actr++;
		}
																	/* list actions */
	$query = "SELECT *,
		`date` AS `date`,
		`updated` AS `updated`
		FROM `$GLOBALS[mysql_prefix]action`
		WHERE `ticket_id`='$the_id'
		ORDER BY `date`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_affected_rows() + $actr)==0) { 				// 8/6/08
		return "";
		}
	else {
		$caption = "Actions: &nbsp;&nbsp;";
		$pctr=0;
		while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$tipstr = addslashes($act_row['description']);
			$print .= "<TR CLASS='{$evenodd[$pctr%2]}' WIDTH='100%' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\" >
				<TD VALIGN='top' NOWRAP CLASS='td_label'>$caption</TD>";
			$responders = explode (" ", trim($act_row['responder']));	// space-separated list to array
			$sep = $respstring = "";
			for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
				if (array_key_exists($responders[$i], $responderlist)) {
					$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
					$sep = "<BR />";
					}
				}

			$print .= "<TD CLASS='normal_text' NOWRAP>" . $respstring . "</TD><TD CLASS='normal_text' NOWRAP>". format_date_2($act_row['updated']) ."</TD>";	//	3/15/11
			$print .= "<TD CLASS='normal_text' NOWRAP>by <B>".get_owner($act_row['user'])."</B> ";	//	3/15/11
			$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'])? '*' : '-';
			$print .= "</TD><TD CLASS='normal_text' WIDTH='100%'>" . replace_newline($act_row['description']) . "</TD>";	//	3/15/11
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
	}			// end function show_actions_orig

// } { -- dummy

function show_messages ($the_id, $theSort="date", $links, $display) {			/* list messages belonging to ticket 10/23/12 */
	global $evenodd;
	$actr=0;
	$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$responderlist = array();
	$responderlist[0] = "NA";
	$caption = "Messages: ";
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$responderlist[$act_row['id']] = $act_row['handle'];
		}

	$print = "<TABLE BORDER='0' ID='messages' width='100%'>";
	$print .= "<TR><TH class='heading' COLSPAN=99 STYLE='text-align: center;'>" . $caption . "</TH></TR>";
	$query = "SELECT *,
		`date` AS `date`,
		`_on` AS `_on`,
		`m`.`id` AS `message_id`,
		`m`.`message` AS `message`
		FROM `$GLOBALS[mysql_prefix]messages` `m`
 		WHERE `ticket_id`='{$the_id}' ORDER BY `date`";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result) == 0) {
		print "No Messages";
//		return "";
		} else {
		$msgtr=0;
		while ($msg_row = stripslashes_deep(mysql_fetch_assoc($result))){
			$the_message_id = $msg_row['message_id'];
			$the_responder = $msg_row['resp_id'];
			$resp_name = (isset($responderlist[$the_responder])) ? $responderlist[$the_responder] : "";

	//		$tipstr = addslashes("Name: {$act_row['name']}<br> Fullname: {$act_row['fullname']}<br> DOB: {$act_row['dob']}<br> Gender: {$the_gender}<br>Insurance_id: {$act_row['ins_value']}<br>Facility_contact: {$act_row['facility_contact']}<br>    Date: {$act_row['date']}<br>    Description: {$act_row['description']}");
			$tipstr = addslashes("A Message");

			$print .= "<TR CLASS='{$evenodd[$msgtr%2]}' WIDTH='100%'  onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\">";

			if($msg_row['recipients'] == NULL) {
				$respstring = $resp_name;
				} else {
				$responders = explode (" ", trim($msg_row['recipients']));	// space-separated list to array
				$sep = $respstring = "";
				for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
					if (array_key_exists($responders[$i], $responderlist)) {
						$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
						$sep = "<BR />";
						}
					}
				}
			$print .= "<TD CLASS='normal_text' NOWRAP>" . $respstring . "</TD><TD CLASS='normal_text' NOWRAP>" . format_date_2($msg_row['_on']) ."</TD>";
			$print .= "<TD NOWRAP>by <B>".get_owner($msg_row['_by'])."</B></TD>";

			if ($msg_row['msg_type'] == 1) {
				$type_flag = "OE";
				} elseif ($msg_row['msg_type'] ==2) {
				$type_flag = "IE";
				} elseif ($msg_row['msg_type'] ==3) {
				$type_flag = "OS";
				} elseif ($msg_row['msg_type'] ==4) {
				$type_flag = "IS";
				} else {
				$type_flag = "?";
				}

			$print .= "<TD>" . $type_flag . "</TD>";
			$print .= "<TD CLASS='normal_text' WIDTH='100%'>" . shorten($msg_row['message'], 24) . "</TD>";

			if ($links) {
				$print .= "<TD>[<A HREF='message.php?message_id=" . $msg_row['message_id'] . "&action=view'>view</A>|
					<A HREF='message.php?message_id=" . $msg_row['message_id'] . "&action=delete'>delete</A>]</TD>\n";
					}
			$print .= "</TR>";
			$caption = "";				// once only
			$msgtr++;
			}

			$print .= "</TABLE>\n";
			$print .= "<BR /><BR />";
			return $print;
			}				// end else
	}			// end function show_messages

// } { -- dummy

function get_un_status_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if($result && mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['status_val'];
		} else {
		return "unk";
		}
	}

function get_un_status_cols($id) {
	$stat_cols = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if($result && mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$stat_cols[0] = $row['bg_color'];
		$stat_cols[1] = $row['text_color'];
		} else {
		$stat_cols[0] = "#FFFFFF";
		$stat_cols[1] = "#000000";
		}
	return $stat_cols;
	}

function get_fac_status_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if($result && mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['status_val'];
		} else {
		return "unk";
		}
	}

function get_fac_status_cols($id) {
	$stat_cols = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$stat_cols[0] = $row['bg_color'];
		$stat_cols[1] = $row['text_color'];
		} else {
		$stat_cols[0] = "#FFFFFF";
		$stat_cols[1] = "#000000";
		}
	return $stat_cols;
	}

function show_log($theid, $show_cfs=FALSE) {								// 11/20/09, 10/20/12, 5/8/14
	global $evenodd ;	// class names for alternating table row colors
	require('log_codes.inc.php'); 									// 9/29/10
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
	$print = "<TABLE style='width: 100%;' ID='theLog'>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$code = $row['code'];
		if ($i==0) {				// 11/20/09
			$print .= "<TR CLASS='heading' STYLE='width: 98%;'><TD CLASS='heading text text_bold' TITLE = \"{$row['tickname']}\" COLSPAN=99 ALIGN='center'><U>Log: <I>". shorten($row['tickname'], 32) . "</I></U></TD></TR>";
			$cfs_head = ($show_cfs)? "<TD CLASS='td_label text text_center text_bold'>CFS</TD>" : ""  ;
			$print .= "<TR CLASS='odd' STYLE='width: 98%;'><TD CLASS='td_label text text_left text_bold' ALIGN='left'>Code</TD>" . $cfs_head . "<TD CLASS='td_label text text_left text_bold'>Unit</TD><TD CLASS='td_label text text_left text_bold'>Status</TD><TD CLASS='td_label text text_left text_bold'>When</TD><TD CLASS='td_label text text_left text_bold'>By</TD><TD CLASS='td_label text text_left text_bold'>From</TD></TR>";
			}
		if($code ==3) {$theTitle = $row['info'];} else {$theTitle = $types[$row['code']];}
		$print .= "<TR CLASS='" . $evenodd[$i%2] . "' onClick = 'view_log_entry({$row['log_id']});'>" .
			"<TD CLASS='td_data text text_left text_normal' TITLE =\"{$theTitle}\">". shorten($types[$row['code']], 20) . "</TD>";
		if ($show_cfs) {
			$print .= "<TD CLASS='td_data text text_left text_normal' TITLE =\"{$row['tickname']}\">". shorten($row['tickname'], 16) . "</TD>";	// 2009-11-07 22:37:41 - substr($row['when'], 11, 5)
			}
		$print .= "<TD CLASS='td_data text text_left text_normal' TITLE =\"{$row['unitname']}\">". 	shorten($row['unitname'], 16) . "</TD>";
		if($code == 20) {
			$print .= "<TD CLASS='td_data text text_left text_normal' TITLE =\"{$row['theinfo']}\">". 	shorten(get_un_status_name($row['info']), 16) . "</TD>";
			} else {
			$print .= "<TD CLASS='td_data text text_left text_normal'>&nbsp;</TD>";
			}
		$print .= "<TD CLASS='td_data text text_left text_normal' TITLE =\"" . format_date_2(strtotime($row['when'])) . "\">". format_date_2(strtotime($row['when'])) . "</TD>";
		$print .= "<TD CLASS='td_data text text_left text_normal' TITLE =\"{$row['thename']}\">". 	$row['thename'] . "</TD>";
		$print .= "<TD CLASS='td_data text text_left text_normal' TITLE =\"{$row['from']}\">". $row['from'] . "</TD>";
			"</TR>";
			$i++;
		}
	$print .= "</TABLE>";
	return $print;
	}		// end function get_log ()
//	} -- dummy

function show_unit_log ($theid, $show_cfs=FALSE) {								// 9/10/13
	global $evenodd ;	// class names for alternating table row colors
	require('./incs/log_codes.inc.php');

	$query = "
		SELECT *,
		`when` AS `when`,
		`l`.`id` AS `log_id`,
		`t`.`scope` AS `tickname`,
		`r`.`handle` AS `unitname`,
		`l`.`info` AS `comment`,
		`s`.`status_val` AS `theinfo`,
		`u`.`user` AS `thename`
		FROM `$GLOBALS[mysql_prefix]log` l
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t 		ON (l.ticket_id = t.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` r 	ON (l.responder_id = r.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s 	ON (l.info = s.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` u 		ON (l.who = u.id)
		WHERE `l`.`responder_id` = {$theid}
		ORDER BY `when` DESC LIMIT 100";								// 10/2/12
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$i = 0;
	$print = "<TABLE ALIGN='left' CELLSPACING = 1 WIDTH='100%'>";

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		if ($i==0) {				// 11/20/09
			$print .= "<TR CLASS='heading'><TD CLASS='heading' TITLE = \"{$row['tickname']}\" COLSPAN=99 ALIGN='center'><U>Log: <I>". shorten($row['tickname'], 32) . "</I></U></TD></TR>";
			$cfs_head = ($show_cfs)? "<TD ALIGN='center'>CFS</TD>" : ""  ;
			$print .= "<TR CLASS='odd'><TD ALIGN='left'>Code</TD>" . $cfs_head . "<TD ALIGN='left'>Unit</TD><TD ALIGN='left'>Status</TD><TD ALIGN='left'>Comment</TD><TD ALIGN='left'>When</TD><TD ALIGN='left'>By</TD></TR>";
			}
		$print .= "<TR CLASS='" . $evenodd[$i%2] . "' onClick = 'view_log_entry({$row['log_id']});'>" .				// 11/20/09
			"<TD TITLE =\"{$types[$row['code']]}\">". shorten($types[$row['code']], 20) . "</TD>"; //
		if ($show_cfs) {
			$print .= "<TD TITLE =\"{$row['tickname']}\">". shorten($row['tickname'], 16) . "</TD>";	// 2009-11-07 22:37:41 - substr($row['when'], 11, 5)
			}
		$theComment = (!is_numeric($row['comment'])) ? $row['comment'] : "";
		$print .=
			"<TD TITLE =\"{$row['unitname']}\">". 	shorten($row['unitname'], 16) . "</TD>".
			"<TD TITLE =\"{$row['theinfo']}\">". 	shorten($row['theinfo'], 16) . "</TD>".
			"<TD TITLE =\"{$row['comment']}\">". 	shorten($theComment, 24) . "</TD>".
			"<TD TITLE =\"" . format_date_2(strtotime($row['when'])) . "\">". format_date_2(strtotime($row['when'])) . "</TD>".
			"<TD TITLE =\"{$row['thename']}\">". 	shorten($row['thename'], 8) . "</TD>".
			"</TR>";
			$i++;
		}
	$print .= "</TABLE>";
	return $print;
	}		// end function show_unit_log ()
//	} -- dummy

function set_ticket_status($status,$id){				/* alter ticket status */
	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET status='$status' WHERE ID='$id'LIMIT 1";
	$result = mysql_query($query) or do_error("set_ticket_status(s:$status, id:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function get_allocates($type, $resource) {	//	6/10/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= '$type' AND `resource_id` = '$resource' ORDER BY `group`;";		//	6/10/11
	$result = mysql_query($query);	// 4/13/11
	$al_groups = array();
	if(mysql_num_rows($result) == 0) {
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region`;";		//	6/10/11
		$result2 = mysql_query($query2);	// 4/13/11
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result))) 	{		//	6/10/11
			$al_groups[] = $row2['id'];
			}
		} else {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{		//	6/10/11
			$al_groups[] = $row['group'];
			}
		}
	return $al_groups;
	}

function get_allocated_names($type, $resource) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= " . $type . " AND `resource_id` = " . $resource . " ORDER BY `group`;";
	$result = mysql_query($query);
	$temp_ary = array();
	if(mysql_num_rows($result) != 0) {
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id` = " . $row['group'];
			$result2 = mysql_query($query2);
			$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
			$temp_ary[] = $row2['group_name'];
			}
		$theReturn = "Allocated to regions " . implode(", ", $temp_ary);
		} else {
		$theReturn = "";
		}
	return $theReturn;
	}

function get_tickets_allocated($group) {	//	6/10/11
	$x=0;
	$cwi = get_variable('closed_interval');			// closed window interval in hours
	$time_back = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60) - ($cwi*3600));
	$where = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type`= 1 AND (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_OPEN']}' OR (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `$GLOBALS[mysql_prefix]ticket`.`booked_date` <= (NOW() + INTERVAL 2 DAY)) OR
				(`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_CLOSED']}'  AND `$GLOBALS[mysql_prefix]ticket`.`problemend` >= '{$time_back}')) AND (";
	foreach($group as $grp) {
		$where2 = (count($group) > ($x+1)) ? " OR " : ")";
		$where .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
		$where .= $where2;
		$x++;
		}
	$query = "SELECT *,`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates`
			ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
		LEFT JOIN `$GLOBALS[mysql_prefix]region`
			ON `$GLOBALS[mysql_prefix]allocates`.group=`$GLOBALS[mysql_prefix]region`.`id`
		$where GROUP BY tick_id ORDER BY `$GLOBALS[mysql_prefix]allocates`.`group`;";		//	6/10/11
	$result = mysql_query($query);	// 4/13/11
	$tickets = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{		//	6/10/11
		$tickets[] = $row['tick_id'];
		}
	return $tickets;
	}

function get_all_group_butts($curr_grps) {		//	6/10/11
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` ORDER BY `id` ASC";		//	6/10/11
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	while ($row_gp = stripslashes_deep(mysql_fetch_assoc($result1))) {
		if(in_array($row_gp['id'], $curr_grps)) {
			$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' VALUE='{$row_gp['id']}'></INPUT>{$row_gp['group_name']}&nbsp;&nbsp;</DIV>";
			} else {
			$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' name='frm_group[]' VALUE='{$row_gp['id']}'></INPUT>{$row_gp['group_name']}&nbsp;&nbsp;</DIV>";
			}
		}
		$al_buttons .= "</DIV>";
		return $al_buttons;
	}

function get_all_group_butts_chkd($curr_grps) {		//	6/10/11
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` ORDER BY `id` ASC";		//	6/10/11
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	while ($row_gp = stripslashes_deep(mysql_fetch_assoc($result1))) {
		if(in_array($row_gp['id'], $curr_grps)) {
			$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' VALUE='{$row_gp['id']}'></INPUT>{$row_gp['group_name']}&nbsp;&nbsp;</DIV>";
			} else {
			$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' name='frm_group[]' VALUE='{$row_gp['id']}' CHECKED DISABLED></INPUT>{$row_gp['group_name']}&nbsp;&nbsp;</DIV>";
			}
		}
		$al_buttons .= "</DIV>";
		return $al_buttons;
	}

function get_sub_group_butts($user_id, $resource, $resource_id) {		//	6/10/11
	$al_groups = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= '$resource' AND `resource_id` = '$resource_id';";		//	6/10/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$al_groups[] = $row['group'];
		}
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$user_id';";		//	6/10/11
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{		//	6/10/11
			if(in_array($row2['group'], $al_groups)) {
				$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV>";
				} else {
				$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' name='frm_group[]' VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV>";
				}
			}
	$al_buttons .= "</DIV>";
	return $al_buttons;
	}

function get_sub_group_butts_readonly($user_id, $resource, $resource_id) {		//	6/10/11

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= '$resource' AND `resource_id` = '$resource_id';";		//	6/10/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$al_groups[] = $row['group'];
		}
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$user_id';";		//	6/10/11
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{		//	6/10/11
			if(in_array($row2['group'], $al_groups)) {
				$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' OnClick='javascript:return ReadOnlyCheckBox()' onkeydown='javascript:return ReadOnlyCheckBox()' VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV>";
				} else {
				$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' name='frm_group[]' OnClick='javascript:return ReadOnlyCheckBox()' onkeydown='javascript:return ReadOnlyCheckBox()' VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV>";
				}
			}
	$al_buttons .= "</DIV>";
	return $al_buttons;
	}

function get_user_group_butts($user_id) {		//	6/10/11
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$user_id'";			//	6/10/11
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{		//	6/10/11
		$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV>";
	}
	$al_buttons .= "</DIV>";
	return $al_buttons;
	}

function get_user_group_butts_readonly($user_id) {		//	6/10/11
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$user_id'";			//	6/10/11
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{		//	6/10/11
		$al_buttons.="<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' OnClick='javascript:return ReadOnlyCheckBox()' onkeydown='javascript:return ReadOnlyCheckBox()' VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV>";
	}
	$al_buttons .= "</DIV>";
	return $al_buttons;
	}

function get_user_group_butts_no_regions($user_id) {		//	6/10/11
	$al_buttons="<DIV ID='groups_sh' style='width: 100%; text-align: left; display: none;'>";
	$al_buttons.="<DIV style='float: left; display: none;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' OnClick='javascript:return ReadOnlyCheckBox()' onkeydown='javascript:return ReadOnlyCheckBox()' VALUE='1'></INPUT></DIV>";
	$al_buttons .= "</DIV>";
	return $al_buttons;
	}

function get_groupname($groupid) {		//	6/10/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$groupid'";		//	6/10/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$groupname = $row['group_name'];
			}
		} else {
		$groupname = "N/A";
		}
	return $groupname;
	}

function get_num_groups() {		//	6/10/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region`";		//	6/10/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if($num_rows >= 2) {
		return true;
		} else {
		return false;
		}
	}

function get_first_group($resource, $resource_id) {		//	6/10/11
	$query = "SELECT `$GLOBALS[mysql_prefix]allocates`.`group`, `$GLOBALS[mysql_prefix]allocates`.`type`, `$GLOBALS[mysql_prefix]region`.`group_name`
			FROM `$GLOBALS[mysql_prefix]allocates`
			LEFT JOIN `$GLOBALS[mysql_prefix]region` ON `$GLOBALS[mysql_prefix]allocates`.`group`=`$GLOBALS[mysql_prefix]region`.`id`
			WHERE `type`= '$resource' AND `resource_id` = '$resource_id'
			ORDER BY `type` LIMIT 1";		// 4/12/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$group = $row['group_name'];
		}
	return $group;
	}

function get_regions_inuse($user) {		//	6/10/11
	if($user = 9999) {
		$where = "";
		} else {
		$where = "WHERE `type` = 4 AND `resource_id` = '$user'";
		}
	$group = array();
	$query = "SELECT DISTINCT `$GLOBALS[mysql_prefix]allocates`.`group`, `$GLOBALS[mysql_prefix]region`.`group_name`
				FROM `$GLOBALS[mysql_prefix]allocates`
				LEFT JOIN `$GLOBALS[mysql_prefix]region` ON `$GLOBALS[mysql_prefix]allocates`.`group`=`$GLOBALS[mysql_prefix]region`.`id`
				$where ORDER BY `$GLOBALS[mysql_prefix]region`.`group_name` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$group[] = $row['group_name'];
		}
	return $group;
	}

function get_regions_inuse_numbers($user) {		//	6/10/11
	if($user == 9999) {
		$where = "";
		} else {
		$where = "WHERE `type` = 4 AND `resource_id` = '$user'";
		}
	$group = array();
	$query = "SELECT DISTINCT `$GLOBALS[mysql_prefix]allocates`.`group`, `$GLOBALS[mysql_prefix]region`.`group_name`
				FROM `$GLOBALS[mysql_prefix]allocates`
				LEFT JOIN `$GLOBALS[mysql_prefix]region` ON `$GLOBALS[mysql_prefix]allocates`.`group`=`$GLOBALS[mysql_prefix]region`.`id`
				$where ORDER BY `$GLOBALS[mysql_prefix]region`.`group_name` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$group[] = $row['group'];
		}
	return $group;
	}

function test_allocates($resource, $al_group, $type) {	//	6/10/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '$resource' AND `group` = '$al_group' AND `type` = '$type'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$found = mysql_num_rows($result);
	if($found == 0) {
		return TRUE;
		} else {
		return FALSE;
		}
	}

function format_date($date){							/* format date to defined type 8/27/10 */
	if (good_date($date)) {
		if (get_variable('locale')==1) {
			return date("j/n/y H:i",$date);		// 08/27/10 - Revised to show UK format for locale = 1
		} else {
			return date(get_variable("date_format"),$date);	//return date(get_variable("date_format"),strtotime($date));
		}
	} else {return "TBD";}
	}				// end function format date($date)

function good_date($date) {		//
	return (is_string ($date) && ((strlen($date)==10)));
	}

//		return  (substr(inval, 5, 2) . substr(inval, 10, 6));

function format_sb_date($date){							/* format sidebar date Oct-30 07:46 */
	if (is_string ($date) && strlen($date)==10) {
		return date("M-d H:i",$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_sb_date($date)

/*		3/27/2013
function new_format_sb_date($date){
	if (is_string ($date) && strlen($date)==19) {return  (substr(inval, 5, 2) . substr(inval, 10, 6));}
	else 										{return "TBD";}
	}				// end new_format_sb_date();
*/

function new_format_sb_date($date){						// 1/19/2013
	if (is_string ($date) && strlen($date)==19) {return  substr($date, 8, 8);}	/* 2013-01-19 21:18:19 	 */
	else 										{return "TBD";}
	}				// end new_format_sb_date();

function good_date_time($date) {						//  2/15/09
	return (is_string ($date) && (strlen($date)==19) && (!($date=="0000-00-00 00:00:00")));
	}

function format_date_time($date){		// mySql format to settings spec - 2/15/09 - 11/30/2012
	return format_date_2 ($date);
	}				// end function format_date_time()

function get_status($status){							/* return status text from code */
	switch($status)	{
		case 1: 	return 'Closed';	break;
		case 2: 	return 'Open';		break;
		case 3: 	return 'Scheduled';	break;
		default: 	return 'Status error';
		}
	}

function get_owner($id){								/* get owner name from id */
	$result	= mysql_query("SELECT user FROM `$GLOBALS[mysql_prefix]user` WHERE `id`= '$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if($result) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return (mysql_affected_rows()==0 )? "unk?" : $row['user'];
		} else {
		return "unk";
		}
	}

function get_user_facility($id){								/* get owner facility from id */
	$result	= mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id`= '$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? 0 : intval($row['facility_id']);
	}

function get_reader($id){								/* Add in for Messaging 10/23/12 */
	$result	= mysql_query("SELECT user FROM `$GLOBALS[mysql_prefix]user` WHERE `id`='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "None" : $row['user'];
	}

function get_severity($severity){			/* return severity string from value */
	switch($severity) {
		case $GLOBALS['SEVERITY_NORMAL']: 	return get_text("Normal"); break;
		case $GLOBALS['SEVERITY_MEDIUM']: 	return get_text("Medium"); break;
		case $GLOBALS['SEVERITY_HIGH']: 	return get_text("High"); break;
		default: 							return "Severity error"; break;
		}
	}

function get_severity_field($severity){			/* return severity string from value */
	switch($severity) {
		case $GLOBALS['SEVERITY_NORMAL']: 	return get_text("Normal"); break;
		case $GLOBALS['SEVERITY_MEDIUM']: 	return get_text("Medium"); break;
		case $GLOBALS['SEVERITY_HIGH']: 	return get_text("High"); break;
		default: 							return "Severity error"; break;
		}
	}

function get_responder($id){			/* return responder-type string from value */
	$query = "SELECT `name` FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$id' LIMIT 1";
	$result	= mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['name'];
	}

function get_member($id){			/* return responder-type string from value */
	$query = "SELECT `field1`, `field2`, `field4` FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $id . " LIMIT 1";
	$result	= mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['field2'] . " " . $row['field1'] . " " . $row['field4'];
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

$msg_variables = array();
function get_msg_variable($which){								/* get variable from db msg_settings table, returns FALSE if absent  */
	global $msg_variables;
	if (empty($msg_variables)) {
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]msg_settings`") or do_error("get_msg_variable(n:$which)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$ms_name = $row['name']; $ms_value=$row['value'] ;
			$msg_variables[$ms_name] = $ms_value;
			}
		}
	return (array_key_exists($which, $msg_variables))? $msg_variables[$which] : FALSE ;
	}

$mdb_variables = array();
function get_mdb_variable($which){								/* get variable from db msg_settings table, returns FALSE if absent  */
	global $mdb_variables;
	if (empty($mdb_variables)) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mdb_settings`";
		$result = mysql_query($query);
		if(!$result) {return FALSE;}
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$mdb_name = $row['name']; $mdb_value=$row['value'] ;
			$mdb_variables[$mdb_name] = $mdb_value;
			}
		}
	return (array_key_exists($which, $mdb_variables))? $mdb_variables[$which] : FALSE ;
	}

$css = array();			//	3/15/11
function get_css($element, $day_night){								/* get hex color string from db css colors table, returns FALSE if absent 3/15/11 */
	global $css;
	if($day_night=="Day") {
		if (empty($css)) {
			$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]css_day`") or do_error("get_css(n:$name)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
				$name = $row['name']; $value=$row['value'] ;
				$css[$name] = $value;
				}
			}
	}
	if($day_night=="Night") {
		if (empty($css)) {
			$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]css_night`") or do_error("get_css(n:$name)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
				$name = $row['name']; $value=$row['value'] ;
				$css[$name] = $value;
				}
			}
	}
	return (array_key_exists($element, $css))? "#" . $css[$element] : FALSE ;
	}
/* raise an error event
function do_error($err_function,$err,$custom_err='',$file='',$line=''){
	print "<FONT CLASS=\"warn\">An error occured in function '<B>$err_function</B>': '<B>$err</B>'<BR />";
	if ($file OR $line) print "Error occured in '$file' at line '$line'<BR />";
	if ($custom_err != '') print "Additional info: '<B>$custom_err</B>'<BR />";
	print '<BR />Check your MySQL connection and if the problem persist, contact the <A HREF="help.php?q=credits">author</A>.<BR />';
	die('<B>Execution stopped.</B></FONT>');
	}
*/

function do_error($err_function, $err, $custom_err='', $file='', $line=''){ /* report an error event - revised 5/11/2013 */
	@session_start();											//
	$log_message = substr ( "application error: {[$file]@[$line] [$err_function]", 0, 2048) ;
	if (!(array_key_exists ( $log_message, $_SESSION ))) {		// limit to once per session
		$_SESSION[$log_message] = TRUE;
		do_log($GLOBALS['LOG_ERROR'], 0, 0, $log_message);		// visible in reports station log
		@error_log ($log_message);								// to server log
		}

	print "<FONT CLASS=\"warn\">An error occured in function '<B>$err_function</B>': '<B>$err</B>'<BR />";
	if ($file OR $line) print "Error occured in '$file' at line '$line'<BR />";
	if ($custom_err != '') print "Additional info: '<B>$custom_err</B>'<BR />";
	print '<BR />Check your MySQL connection and if the problem persist, contact the <A HREF="help.php?q=credits">author</A>.<BR />';
	die('<B>Execution stopped.</B></FONT>');
	}

function add_header($ticket_id, $no_edit = FALSE, $show_ed_button = FALSE) {		// 11/27/09, 3/30/10, 8/27/10
	$win_height =  get_variable('map_height') + 240;
	$win_width = get_variable('map_width') + 80;
	print "<SPAN STYLE='margin-left: 40px;'><NOBR><SPAN class='text_large text_bold text_white'>This Call: </SPAN>";
	print "<A id='pop_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='#' onClick = \"var popWindow = window.open('incident_popup.php?id=$ticket_id', 'PopWindow', 'resizable=1, scrollbars, height={$win_height}, width={$win_width}, left=50,top=50,screenX=50,screenY=50'); popWindow.focus();\"><SPAN STYLE='float: left;'>" . get_text("Popup") . "</SPAN><IMG STYLE='float: right;' SRC='./images/popup_small.png' BORDER=0></A>"; // 7/3/10
	if (can_edit()){
		if($show_ed_button) {
			print "<A id='ed_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='{$_SESSION['editfile']}?id=$ticket_id'><SPAN STYLE='float: left;'>" . get_text("Edit") . "</SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></A>";
			}
		if (!is_closed($ticket_id)) {
			print "<A id='act_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"do_add_action('" . $ticket_id . "');\" HREF='#'><SPAN STYLE='float: left;'>+ " . get_text("Action") . "</SPAN><IMG STYLE='float: right;' SRC='./images/action_small.png' BORDER=0></A>";
			print "<A id='pat_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"do_add_patient('" . $ticket_id . "');\" HREF='#'><SPAN STYLE='float: left;'>+ " . get_text("Patient") . "</SPAN><IMG STYLE='float: right;' SRC='./images/patient_small.png' BORDER=0></A>";
			}
		print "<A id='notify_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='config.php?func=notify&id=$ticket_id'><SPAN STYLE='float: left;'>" . get_text("Notify") . "</SPAN><IMG STYLE='float: right;' SRC='./images/message_small.png' BORDER=0></A>";
		}
	print "<A id='prt_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"do_print_ticket('" . $ticket_id . "');\" HREF='#'><SPAN STYLE='float: left;'>" . get_text("Print") . "</SPAN><IMG STYLE='float: right;' SRC='./images/print_small.png' BORDER=0></A>";
	if (!is_guest()) {				// 2/1/10
		print "<A id='email_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='#' onClick = \"var mailWindow = window.open('mail.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=300, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"><SPAN STYLE='float: left;'>" . get_text("E-mail") . "</SPAN><IMG STYLE='float: right;' SRC='./images/message_small.png' BORDER=0></A>"; // 2/1/10
		print "<A id='mail_" . $ticket_id . "' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' HREF='#' onClick = 'do_mail_all_win({$ticket_id});' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\"><SPAN STYLE='float: left;'>" . get_text("Contact Units") . "</SPAN><IMG STYLE='float: right;' SRC='./images/message_small.png' BORDER=0></A>";
		print "<A id='note_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='#' onClick = \"var mailWindow = window.open('add_note.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"><SPAN STYLE='float: left;'>+ " . get_text("Note") . "</SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></A>"; // 10/8/08
		if ((!(is_closed($ticket_id))) && (!is_unit()))  {		// 7/27/10
			print "<A id='closein_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='#' onClick = \"var mailWindow = window.open('close_in.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=300, width=700, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"><SPAN STYLE='float: left;'>" . get_text("Close inc") . "</SPAN><IMG STYLE='float: right;' SRC='./images/close_small.png' BORDER=0></A> ";  // 8/20/09
			}
		if (!is_unit()) {				// 7/27/10
			print "<A id='disp_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' HREF='{$_SESSION['routesfile']}?ticket_id=$ticket_id'><SPAN STYLE='float: left;'>" . get_text("Dispatch") . "</SPAN><IMG STYLE='float: right;' SRC='./images/dispatch_small.png' BORDER=0></A>";		// 3/30/10
			}
		}
	print "</FONT></NOBR></SPAN>";
	}				// function add_header()

function is_closed($id){/* is ticket closed? */
	return check_for_rows("SELECT id,status FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id' AND status='$GLOBALS[STATUS_CLOSED]'");
	}

function is_super(){				// added 6/9/08, 6/12/14
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_SUPER']));		// 5/11/10, 4/29/14
	}
function is_administrator(){		/* is user admin or super? */
	return ((array_key_exists('level', $_SESSION)) && (($_SESSION['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) || ($_SESSION['level'] == $GLOBALS['LEVEL_SUPER'])));		// 5/11/10, 4/29/14
	}
function is_admin(){		/* is user admin but not super? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']));		// 10/26/11, 4/29/14
	}
function is_guest(){				/* is user guest? */
	return ((array_key_exists('level', $_SESSION)) && (($_SESSION['level'] == $GLOBALS['LEVEL_GUEST']) || ($_SESSION['level'] == $GLOBALS['LEVEL_MEMBER'])));				// 6/25/10, 4/29/14
	}
function is_member(){				/* is user member? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_MEMBER']));				// 7/2/10, 4/29/14
	}
function is_user(){					/* is user operator/dispatcher? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_USER']));		// 5/11/10, 4/29/14
	}
function is_unit(){					/* is user unit? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']));						// 7/12/10, 4/29/14
	}
function is_facility(){					/* is user facility? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_FACILITY']));						// 5/26/16
	}
function is_statistics(){					/* is user statistics? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_STATISTICS']));						// 10/23/12, 4/29/14
	}
function is_service_user(){					/* is user service user? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_SERVICE_USER']));						// 10/23/12, 4/29/14
	}
function is_manager(){					/* is user service user? */
	return ((array_key_exists('level', $_SESSION)) && ($_SESSION['level'] == $GLOBALS['LEVEL_MANAGER']));						// 10/23/12, 4/29/14
	}
function see_buttons() {
	return ((array_key_exists('level', $_SESSION)) && (($_SESSION['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) || ($_SESSION['level'] == $GLOBALS['LEVEL_SUPER']) || ($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) || ($_SESSION['level'] == $GLOBALS['LEVEL_USER']) || ($_SESSION['level'] == $GLOBALS['LEVEL_MEMBER'])));		// 10/11/12, 4/29/14
	}
function may_email() {
	return (!(is_guest()) || (is_member() || is_unit())) ;						// members, units  allowed
	}
																	/* print date and time in dropdown menus */
function has_admin() {
	return ((is_super()) || (is_administrator())) ;								// 9/22/10
	}
function generate_date_dropdown($date_suffix,$default_date=0, $disabled=FALSE) {			// 'extra allows 'disabled'

	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));
	$default_date = ($default_date == 0) ? $local : $default_date;

	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		$minute		= date('i',$default_date);
		$meridiem		= date('a',$default_date);
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
			print "<SELECT class='text' id='dateselect_$date_suffix' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>\n&nbsp;<SELECT class='text' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='text' VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";

			print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
			break;

		case "1":
			print "<SELECT class='text' id='dateselect_$date_suffix' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='text' VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";

			print "\n<!-- default:$default_date,$day-$month-$year $hour:$minute -->\n";
			break;
		case "2":				// 11/29/10
			print "<SELECT class='text' id='dateselect_$date_suffix' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='text' VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";

			print "\n<!-- default:$default_date,$day-$month-$year $hour:$minute -->\n";
			break;
																						// 8/10/09
		default:
		    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}


	print "\n<INPUT class='text' TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_hour_$date_suffix' VALUE='$hour' $dis_str>:";
	print "\n<INPUT class='text' TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_minute_$date_suffix' VALUE='$minute' $dis_str>";
	$show_ampm = (!get_variable('military_time')==1);
	if ($show_ampm){	//put am/pm optionlist if not military time
		print "\n<SELECT class='text' NAME='frm_meridiem_$date_suffix' $dis_str><OPTION class='text' value='am'";
		if ($meridiem == 'am') print ' selected';
		print ">am</OPTION><OPTION class='text' value='pm'";
		if ($meridiem == 'pm') print ' selected';
		print ">pm</OPTION></SELECT>";
		}
	}		// end function generate_date_dropdown(

function return_date_dropdown($date_suffix,$default_date=0, $disabled=FALSE) {			// 'extra allows 'disabled'
	$output = "";
	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));
	$default_date = ($default_date == 0) ? $local : $default_date;

	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		$minute		= date('i',$default_date);
		$meridiem		= date('a',$default_date);
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
			$output .= "<SELECT id='dateselect_$date_suffix' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				$output .= "<OPTION VALUE='$i'";
				$year == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}

			$output .= "</SELECT>";
			$output .= "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				$output .= "<OPTION VALUE='$i'";
				$month == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}

			$output .= "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				$output .= "<OPTION VALUE=\"$i\"";
				$day == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}
			$output .= "</SELECT>\n&nbsp;&nbsp;";

			$output .= "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
			break;

		case "1":
			$output .= "<SELECT id='dateselect_$date_suffix' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				$output .= "<OPTION VALUE=\"$i\"";
				$day == $i ? $output .= " SELECTED>$i</OPTION>" :$output .= ">$i</OPTION>";
				}

			$output .= "</SELECT>";
			$output .= "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				$output .= "<OPTION VALUE='$i'";
				$month == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}

			$output .= "</SELECT>";
			$output .= "&nbsp;<SELECT name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				$output .= "<OPTION VALUE='$i'";
				$year == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}
			$output .= "</SELECT>\n&nbsp;&nbsp;";

			$output .= "\n<!-- default:$default_date,$day-$month-$year $hour:$minute -->\n";
			break;
		case "2":				// 11/29/10
			$output .= "<SELECT id='dateselect_$date_suffix' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				$output .= "<OPTION VALUE=\"$i\"";
				$day == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}

			$output .= "</SELECT>";
			$output .= "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				$output .= "<OPTION VALUE='$i'";
				$month == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}

			$output .= "</SELECT>";
			$output .= "&nbsp;<SELECT name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				$output .= "<OPTION VALUE='$i'";
				$year == $i ? $output .= " SELECTED>$i</OPTION>" : $output .= ">$i</OPTION>";
				}
			$output .= "</SELECT>\n&nbsp;&nbsp;";

			$output .= "\n<!-- default:$default_date,$day-$month-$year $hour:$minute -->\n";
			break;
																						// 8/10/09
		default:
		    $output .= "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}


	$output .= "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_hour_$date_suffix' VALUE='$hour' $dis_str>:";
	$output .= "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_minute_$date_suffix' VALUE='$minute' $dis_str>";
	$show_ampm = (!get_variable('military_time')==1);
	if ($show_ampm){	//put am/pm optionlist if not military time
		$output .= "\n<SELECT NAME='frm_meridiem_$date_suffix' $dis_str><OPTION value='am'";
		if ($meridiem == 'am') $output .= ' selected';
		$output .= ">am</OPTION><OPTION value='pm'";
		if ($meridiem == 'pm') $output .= ' selected';
		$output .= ">pm</OPTION></SELECT>";
		}
	return $output;
	}		// end function generate_date_dropdown(

function generate_dateonly_dropdown($date_suffix,$default_date=0, $disabled=FALSE) {			// 10/23/12

	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));

	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		}
	else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		}

	$locale = get_variable('locale');				// Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
	switch($locale) {
		case "0":
			print "<SELECT class='text' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-70; $i < date("Y")+1; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>\n&nbsp;<SELECT class='text' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='text' VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";

			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;

		case "1":
			print "<SELECT class='text' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='text' VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-70; $i < date("Y")+1; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";

			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;
		case "2":				// 11/29/10
			print "<SELECT class='text' name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='text' VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT class='text' name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-70; $i < date("Y")+1; $i++){
				print "<OPTION class='text' VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";

			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;
																						// 8/10/09
		default:
		    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
	}		// end function generate_dateonly_dropdown(

function report_action($action_type,$ticket_id,$value1='',$value2=''){/* insert reporting actions */
	if (!get_variable('reporting')) return;

	switch($action_type)	{
		case $GLOBALS[ACTION_OPEN]: 	$description = "Action Opened"; break;
		case $GLOBALS[ACTION_CLOSED]: 	$description = "Action Closed"; break;
		case $GLOBALS[PATIENT_OPEN]: 	$description = get_text("Patient") . " Item Opened"; break;
		case $GLOBALS[PATIENT_CLOSED]: 	$description = get_text("Patient") . " Item Closed"; break;
		default: 						$description = "[unknown report value: $action_type]";
		}
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]action` (date,ticket_id,action_type,description,user) VALUES('{$now}','{$ticket_id}','{$action_type}','{$description}','{$_SESSION['user_id']}')";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function dumpp($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump(debug_backtrace());
	var_dump($variable) ;
	echo "</PRE>\n";
	}
function dump($variable) {
	echo "\n<PRE>\n";				// pretty it a bit - 2/23/2013
	var_dump($variable) ;
	echo "</PRE>\n";
	}

function shorten($instring, $limit) {
	return (strlen($instring) > $limit)? substr($instring, 0, $limit-4) . ".." : $instring ;	// &#133
	}

function format_phone ($instr) { // 11/16/10 added check for locale for UK phone number format.
	$locale = get_variable('locale');
	$temp = trim($instr);
	switch($locale) {
	case "0":
		return  (!empty($temp))? "(" . substr ($instr, 0,3) . ") " . substr ($instr,3, 3) . "-" . substr ($instr,6, 4): "";
		break;
	case "1":
		return  (!empty($temp))? substr ($instr, 0,5) . " " . substr ($instr,5, 6): "";
		break;
	case "2":				// 11/29/10
		return  (!empty($temp))? substr ($instr, 0,5) . " " . substr ($instr,5, 6): "";
		break;
	default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}			// end switch()
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

function replace_quotes($instring) {		//	3/15/11
    	$search = array(chr(34));
    	$value = str_replace($search, " ", $instring);
    	return $value;
       }

function stripslashes_deep($value) {
    	$value = is_array($value) ? array_map('stripslashes_deep', $value) :	stripslashes($value);
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
		case $GLOBALS['LEVEL_UNIT'] 			: return "Unit"; break;				// 7/12/10
		case $GLOBALS['LEVEL_FACILITY'] 		: return "Facility"; break;			// 4/8/16
		case $GLOBALS['LEVEL_STATS'] 			: return "Statistics"; break;		// 6/10/11
		case $GLOBALS['LEVEL_SERVICE_USER'] 	: return "Service User"; break;		// 10/23/12
		case $GLOBALS['LEVEL_MANAGER'] 			: return "Manager"; break;
		default 								: return "level error"; break;
		}
	}		//end function

function got_gmaps() {								// valid GMaps API key ?
	return (strlen(get_variable('gmaps_api_key'))==86);
	}

function mysql_format_date($indate="") {			// returns MySQL-format date given argument timestamp or default now
	if (empty($indate)) {$indate = time();}
	return @date("Y-m-d H:i:s", $indate);
	}

function is_date($DateEntry) {						// returns true for valid non-zero date
	$Date_Array = explode('-',$DateEntry);			// "2007-00-00 00:00:00"
	if (count($Date_Array)!=3) 									return FALSE;
	if((strlen($Date_Array[0])!=4)|| ($Date_Array[0]=="0000")) 	return FALSE;
	else {return TRUE;}
	}		// end function Is_Date()

function toUTM($coordsIn, $from = "") {							// UTM converter - assume comma separator
	$temp = explode(",", $coordsIn);
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


function mysql2timestamp($m) {				// 9/29/10
//	return mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
	return mktime(	(int) ltrim(substr((string)$m,11,2), "0"),
					(int) ltrim(substr((string)$m,14,2), "0"),
					(int) ltrim(substr((string)$m,17,2), "0"),
					(int) ltrim(substr((string)$m,5,2), "0"),
					(int) ltrim(substr((string)$m,8,2), "0"),
					(int) ltrim(substr((string)$m,0,4), "0")
					);
	}

require_once('remotes.inc.php');	// 8/21/10

function do_log($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09
	@session_start();							// 4/4/10
	$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;		// 11/14/10
	$info = substr($info, 0, 2047);
	$from = $_SERVER['REMOTE_ADDR'];
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$query = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]log` (`who`,`from`,`when`,`code`,`ticket_id`,`responder_id`,`info`, `facility`, `rec_facility`, `mileage`)
		VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
				quote_smart(trim($who)),
				quote_smart(trim($from)),
				quote_smart(trim($now)),
				quote_smart(trim($code)),
				quote_smart(trim($ticket_id)),
				quote_smart(trim($responder_id)),
				quote_smart(trim($info)),
				quote_smart(trim($facility_id)),
				quote_smart(trim($rec_facility_id)),
				quote_smart(trim($mileage)));
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

	function set_sess_exp() {						// updates session-expires time in user record
		@session_start();							// 4/4/10
		global $expiry;
		$the_date = mysql_format_date($expiry) ;

		$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `expires` = '{$the_date}' WHERE `id`='{$_SESSION['user_id']}' LIMIT 1";		// note no 'delta'
		$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
		}

	function expired() {			// returns TRUE/FALSE state of login time_out
		if(empty($_SESSION)) {return TRUE;}		// $_SESSION = array(); ??

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` ='{$_SESSION['user_id']}' LIMIT 1";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_affected_rows()==1) {
			$row = stripslashes_deep(mysql_fetch_array($result));
			$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
			if ($row['expires'] > $now) {
				return FALSE;			// NOT expired
				}
			else {
				return TRUE;		// expired
				}
			}		// end mysql_affected_rows() ==1
		else {
			dump (__LINE__ . " ?????????");		// ERROR ??????????????
			return TRUE;		// expired
			}
		}			// end expired()

function get_sess_key($line="") {
	if(!(isset($_SESSION['id']))) return FALSE;
	return $_SESSION['id'];
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

function get_field_index($table, $name) {
	$table_arr = array();
	$i = 0;
	$result = mysql_query("DESCRIBE `$GLOBALS[mysql_prefix]$table`");
	while($row = mysql_fetch_array($result)) {
		if($row[0] == $name) {
			return $i;
			}
		$i++;
		}
	}

function get_field_type($table, $field) {
	$enum = "enum";
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]$table`");
	$field_type  = mysql_field_type($result, $field);
	$flags = mysql_field_flags($result, $field);
	if($field_type == "blob") {
		$field_type = "STRING";
		} elseif($field_type == "real") {
		$field_type = "REAL";
		} elseif($field_type == "int") {
		$field_type = "INT";
		} elseif($field_type == "datetime") {
		$field_type = "DATETIME";
		} elseif($field_type == "DATETIME") {
		$field_type = "DATETIME";
		} elseif($field_type == "DATE") {
		$field_type = "DATE";
		} elseif($field_type == "date") {
		$field_type = "DATE";
		} elseif($field_type == "string" || $field_type == "STRING") {
		if($flags == "not_null enum" || $flags == "enum") {
			$field_type = "ENUM";
			} else {
			$field_type = "STRING";
			}
		}
	return $field_type;
	}

function get_field_name($table, $field) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]$table`");
	$field_name  = mysql_field_name($result, $field);
	return $field_name;
	}

function get_field_size($table, $field) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]$table`");
	$field_size  = mysql_field_len($result, $field);
	return $field_size;
	}

function get_display_field_size($table, $field) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]$table`");
	$row = mysql_fetch_array($result);
	$field_size  = $row['size'];
	return $field_size;
	}

function wizard_field_exists($field) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]wizard_settings` WHERE `fieldname` = '" . $field . "'");
	if(mysql_num_rows($result) > 0) {
		return true;
		} else {
		return false;
		}
	}

function get_wizard_field_select($current, $id = NULL) {
	$result = mysql_query("DESCRIBE `$GLOBALS[mysql_prefix]ticket`");
	$row = mysql_fetch_array($result);
	if($id) {
		$output = "<SELECT id='frm_fieldname_" . $id . "' NAME='frm_fieldname[" . $id . "]' STYLE='width: 90%;'>\n";
		$output .= "\t<OPTION VALUE='0'>Select One</OPTION>\n";
		while($row = mysql_fetch_array($result)) {
			$field = $row['Field'];
			if($field == 'street') {$field = "address";}
			if($field == "address") {
				$sel = ($field == $current) ? "SELECTED" : "";
				$output .= "\t<OPTION VALUE='" . $field . "' " . $sel . ">" . $field . "</OPTION>";
				} else {
				if($field != 'city' && $field != "state" && $field != "severity" && $field != "lat" && $field != "lng" && $field != "date" && $field != "affected" && $field != "status" && $field != "owner" && $field != "problemend" && $field != "updated" && $field != "_by") {
					$sel = ($row['Field'] == $current) ? "SELECTED" : "";
					$output .= "\t<OPTION VALUE='" . $field . "' " . $sel . ">" . $field . "</OPTION>";
					}
				}
			}
		$output .= "</SELECT>";
		} else {
		$options = array();
		$output = "<SELECT id='frm_fieldname_0' NAME='frm_fieldname[0]' STYLE='width: 90%;'>\n";
		$output .= "\t<OPTION VALUE='0'>Select One</OPTION>\n";
		while($row = mysql_fetch_array($result)) {
			$field = $row['Field'];
			if($field == 'street') {$field = "address";}
			if($field == "address") {
				if(!wizard_field_exists($field)) {
					$options[] = $field;
					$output .= "\t<OPTION VALUE='" . $field . "'>" . $field . "</OPTION>";
					}
				} else {
				if($field != 'city' && $field != "state" && $field != "severity" && $field != "lat" && $field != "lng" && $field != "date" && $field != "affected" && $field != "status" && $field != "owner" && $field != "problemend" && $field != "updated" && $field != "_by") {
					if(!wizard_field_exists($field)) {
						$options[] = $field;
						$output .= "\t<OPTION VALUE='" . $row['Field'] . "'>" . $row['Field'] . "</OPTION>";
						}
					}
				}
			}
		$numOptions = count($options);
		if($numOptions > 0) {
			$output .= "</SELECT>";
			} else {
			$output = "";
			}
		}
	return $output;
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

function isValidURL($url) {
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}

function do_kml() {									// emits JS for kml-type files in noted directory - added 5/23/08, 4/2/14
	if(get_variable('kml_files') == "0") {
		return;
		}
	$dir = "./kml_files";							// required as directory
	if (is_dir($dir)){
		$dh  = opendir($dir);
		$temp = explode ("/", $_SERVER['REQUEST_URI']);
		$temp[count($temp)-1] = substr($dir, 2);				// home subdir
		$server_str = "./kml_files/";
		$i=1;
		while (false !== ($filename = readdir($dh))) {
			$temp = explode(".", $filename);
			$thefileName = $temp[0];
			switch (get_ext($filename)) {						// drop all other types, incl directories
				case "kml":
				case "kmz":
				case "xml":
					$url = $server_str . $filename;
					echo "map.attributionControl.setPrefix('');\n";
					echo "var xml_" . $i ." = new L.KML('" . $url . "', {async: true});\n";
					echo "map.addLayer(xml_" . $i . ");\n";
					echo "layercontrol.addOverlay(xml_" . $i . ", '" . $thefileName . "');\n";
					$i++;
					break;
				case "gpx":
					$url = $server_str . $filename;
					echo "map.attributionControl.setPrefix('');\n";
					echo "var gpx_" . $i ." = new L.GPX('" . $url . "', {async: true});\n";
					echo "map.addLayer(gpx_" . $i . ");\n";
					echo "layercontrol.addOverlay(gpx_" . $i . ", '" . $thefileName . "');\n";
					$i++;
					break;
// ---------------------------------
				case "txt":
					$the_addr = "{$dir}/{$filename}";
					$lines = file($the_addr );
					foreach ($lines as $line_num => $line) {				// Loop through our array.
						if(isValidURL( trim($line))) {
							echo "map.attributionControl.setPrefix('');\n";
							echo "var xml_" . $i ." = new L.KML('" . $line . "', {async: true});\n";
							echo "map.addLayer(xml_" . $i . ");\n";
							echo "layercontrol.addOverlay(xml_" . $i . ", '" . $thefileName . "');\n";
							}
						$i++;
						}
						break;
// --------------------------------
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

/*
Subject		A
Incident	B  Title*
Priority	C  Priority*
Nature		D  Nature*
Written		E  Written
Updated		F  As of
Reporte		G  By*
Phone: 		H  Phone: *
Status:		I  Status:*
Address		J  Location
Descrip'n	K  Description*
Dispos'n	L  Disposition
Start/end	M
Map: " 		N  Map: " *
Actions		O
Patients	P
Host		Q
911 contact	R				// 6/26/10
Ticket link S				// 6/20/12
Facility 	T				// 6/20/12
Handle		U				// 3/25/13
Scheduled	V				// 3/25/13
*/

function mail_it ($to_str, $smsg_to_str, $text, $ticket_id, $text_sel=1, $txt_only = FALSE) {	// 10/6/08, 10/15/08,  2/18/09, 3/7/09, 10/23/12, 11/14/2012, 12/14/2012
	global $istest;
//	if (is_null($text_sel)) {$text_sel = 1;}			//

	switch ($text_sel) {		// 7/7/09
		case NULL:				// 11/15/2012
		case 1:
		   	$match_str = strtoupper(get_variable("msg_text_1"));				// note case
		   	break;
		case 2:
		   	$match_str = strtoupper(get_variable("msg_text_2"));
		   	break;
		case 3:
		   	$match_str = strtoupper(get_variable("msg_text_3"));
		   	break;
		case 4:
			$match_str = strtoupper(get_variable("msg_text_3")) . ",W";
			break;
		}
	$match_str = preg_replace("/[^a-zA-Z]+/", "", $match_str);					// drop ash/trash - 5/31/2013

	if (empty($match_str)) {$match_str = " " . implode ("", range("A", "W"));}		// empty get all - force non-zero hit
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $ticket_id . " LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
	$the_scope = strlen(trim($t_row['scope']))>0? trim($t_row['scope']) : "[#{$ticket_id}]" ;	// possibly empty
	$eol = PHP_EOL;
	$locale = get_variable('locale');

	$message="";
	$_end = (good_date_time($t_row['problemend']))?  "  End:" . $t_row['problemend'] : "" ;		//

	for ($i = 0;$i< strlen($match_str); $i++) {
		if(!($match_str[$i]==" ")) {
			switch ($match_str[$i]) {
				case "A":
				    break;
				case "B":
					$gt = get_text("Incident");
					$message .= "{$gt}: {$the_scope}{$eol}";
				    break;
				case "C":
					$gt = get_text("Priority");
					$message .= strtoupper(get_severity_field($t_row['severity'])) . $eol;
				    break;
				case "D":
					$gt = get_text("Nature");
					$message .= "{$gt}: " . get_type($t_row['in_types_id']) . $eol;
				    break;
				case "J":
					$gt = get_text("Addr");
					$str = "";
					$str .= (empty($t_row['street']))? 	""  : $t_row['street'] . " " ;
					$str .= (empty($t_row['city']))? 	""  : $t_row['city'] . " " ;
					$str .= (empty($t_row['state']))? 	""  : $t_row['state'];
					$message .= empty($str) ? "" : $str . $eol;
					$gt = get_text("About Address");
					$str2 = "";
					$str2 .= (empty($t_row['address_about']))? 	""  : $t_row['address_about'] ;
					$message .= empty($str2) ? "" : $str2 . $eol;
					$gt = get_text("To Address");
					$str3 = "";
					$str3 .= (empty($t_row['to_address']))? 	""  : $t_row['to_address'] . " " ;
					$message .= empty($str3) ? "" : " " . $str3 . $eol;
					if ( $GLOBALS['NM_LAT_VAL'] != $t_row['lat'] ) {						// 1/4/2014
						$message .= "http://maps.google.com/?q=loc:" . $t_row['lat'] . "," . $t_row['lng'] .  $eol;
						}
				    break;
				case "X":
					$gt = get_text("Addr");
					$str = "";
					$str .= (empty($t_row['street']))?      ""  : $t_row['street'] . " " ;
					$str .= (empty($t_row['city']))?        ""  : $t_row['city'] . " " ;
					$str .= (empty($t_row['state']))?       ""  : $t_row['state'];
					$message .= empty($str) ? "" : $str . $eol;
					$gt = get_text("About Address");
					$str2 = "";
					$str2 .= (empty($t_row['address_about']))?      ""  : $t_row['address_about'] ;
					$message .= empty($str2) ? "" : $str2 . $eol;
					$gt = get_text("To Address");
					$str3 = "";
					$str3 .= (empty($t_row['to_address']))?         ""  : $t_row['to_address'] . " " ;
					$message .= empty($str3) ? "" : " " . $str3 . $eol;
				    break;
				case "K":
					$gt = get_text("Description");
					$message .= (empty($t_row['description']))?  "": "{$gt}: ". wordwrap($t_row['description']).$eol;
				    break;
				case "G":
					$message .= "Call via: " . $t_row['contact'] . $eol;
				    break;
				case "H":
					$gt = get_text("Phone");
					$message .= (empty($t_row['phone']))?  "": "{$gt}: " . format_phone ($t_row['phone']) . $eol;
					break;
				case "E":
					$gt = get_text("Written");
					$message .= (empty($t_row['date']))? "":  "{$gt}: " . format_date_2($t_row['date']) . $eol;
				    break;
				case "F":
					$gt = get_text("Updated");
					$message .= "{$gt}: " . format_date_2($t_row['updated']) . $eol;
				    break;
				case "I":
					$gt = get_text("Status");
					$message .= "{$gt}: ".get_status($t_row['status']).$eol;
				    break;
				case "L":
					$gt = get_text("Disposition");
					$message .= (empty($t_row['comments']))? "": "{$gt}: ".wordwrap($t_row['comments']).$eol;
				    break;
				case "M":
					$gt = get_text("Run Start");
					$message .= get_text("{$gt}") . ": " . format_date_2($t_row['problemstart']). $_end .$eol;
				    break;
				case "N":
					$gt = get_text("Position");
					if($locale == 0) {
						$usng = LLtoUSNG($t_row['lat'], $t_row['lng']);
						$message .= "{$gt}: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $usng . "\n";
						}
					if($locale == 1) {
						$osgb = LLtoOSGB($t_row['lat'], $t_row['lng']);
						$message .= "{$gt}: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $osgb . "\n";
						}
					if($locale == 2) {
						$utm = LLtoUTM($t_row['lat'], $t_row['lng']);
						$message .= "{$gt}: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $utm . "\n";
						}
				    break;

				case "P":
					$gt = get_text("Patient");
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `ticket_id` = " . $ticket_id;
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {
						$message .= "\n{$gt}:\n";
						while($pat_row = stripslashes_deep(mysql_fetch_array($result))){
							$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
							}
						}
					unset ($result);
				    break;

				case "O":
					$gt = get_text("Actions");
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id` = " . $ticket_id;		// 10/16/08
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_affected_rows()>0) {
						$message .= "\n{$gt}:\n";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						while($act_row = stripslashes_deep(mysql_fetch_array($result))) {
							$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
							}
						}
					unset ($result);
				    break;

				case "Q":
					$gt = get_text("Tickets host");
					$message .= "{$gt}: ".get_variable('host').$eol;
				    break;

				case "R":							// 6/26/10
					$gt = get_text("911 Contacted");
					$message .= (empty($t_row['nine_one_one']))?  "": "{$gt}: " . wordwrap($t_row['nine_one_one']).$eol;	//	11/10/11
				    break;

				case "S":		// 6/20/12 - 12/14/2012
					$gt = get_text("Links");
					$protocol = explode("/", $_SERVER["SERVER_PROTOCOL"]);
					$uri = explode("/", $_SERVER["REQUEST_URI"]);
					unset ($uri[count($uri)-1]);
					$uri = join("/", $uri);
					//$message .= "{$gt}: {$temp_arr[0]}://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}/main.php?id={$ticket_id}";
					$message .= "{$gt}: {$protocol[0]}//{$_SERVER["SERVER_ADDR"]}:{$_SERVER["SERVER_PORT"]}{$uri}?id={$ticket_id}";
					break;
				case "T":							// 6/20/12
					$gt = get_text("Facility");
					if ((intval($t_row['rec_facility'])>0) || (intval($t_row['facility'])>0)) {
						$the_facility = (intval($t_row['rec_facility'])>0)? intval($t_row['rec_facility']) : intval($t_row['facility']);
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`={$the_facility} LIMIT 1";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
						if (mysql_num_rows ($result)>0) {
							$f_row = stripslashes_deep(mysql_fetch_array($result));
							$message .= "{$gt}: {$f_row['handle']}\n";
							$message .= "{$gt}: {$f_row['beds_info']}\n";
							}
						}
				    break;
				case "U":		// 11/13/2012
					$query_u = "SELECT  `handle` FROM `$GLOBALS[mysql_prefix]assigns` `a`
						LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`a`.`responder_id` = `r`.`id`)
						WHERE `a`.`ticket_id` = $ticket_id AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'
						ORDER BY `handle` ASC ";																// 5/25/09, 1/16/08
					$result_u = mysql_query($query_u) or do_error($query_u, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_num_rows($result_u)>0) {
						$gt = get_text("Units");
						$units_resp = "";
						while($u_row = stripslashes_deep(mysql_fetch_assoc($result_u))) {
							if($units_resp != "") $units_resp .= " ";
							$units_resp .= "[{$u_row['handle']}]";
							}
						$message .= $units_resp . $eol;		// 4/1/2013
						}
					unset ($result_u);
					break;
				case "V":
					if (is_date($t_row['booked_date'])) {
						$gt = get_text("Scheduled For");
						$message .= get_text("{$gt}") . ": " . format_date_2($t_row['booked_date']). $_end .$eol;
						}
				    break;
				case "W":
					$message .= get_disp_closure_summary($ticket_id) .$eol;
					break;
				default:
//				    $message = "Match string error:" . $match_str[$i]. " " . $match_str . $eol ;
					@session_start();
					$err_str = "mail error: '{$match_str[$i]}' @ " .  __LINE__;		// 6/18/12
					if (!(array_key_exists ( $err_str, $_SESSION ))) {		// limit to once per session
						do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_str);
						$_SESSION[$err_str] = TRUE;
						}
				}		// end switch ()
			}		// end if(!($match_...))
		}		// end for ($i...)

	$message = str_replace("\n.", "\n..", $message);					// see manual re mail win platform peculiarities

//	$subject = (strpos ($match_str, "A" ))? "": "Incident: {$the_scope}";	// 11/14/2012 - 11/14/2012 - don't duplicate
	$subject = ($text != "") ? $text : "{$the_scope}";						// 7/3/2013
	if ($txt_only) {
		return $message;		// 2/16/09
		} else {
		$smsg_to_str = ($smsg_to_str == NULL) ? "" : $smsg_to_str;
		do_send ($to_str, $smsg_to_str, $subject, $message, $ticket_id, 0, NULL, NULL);	//	10/23/12
		}
	}				// end function mail_it ()
// ________________________________________________________

function smtp ($my_to, $my_subject, $my_message, $my_params, $my_from) {
    require_once('smtp.inc.php');                                        // defer load until required - 8/2/10
    real_smtp ($my_to, $my_subject, $my_message, $my_params, $my_from);
    }                         // end function smtp

function do_send ($to_str, $smsg_to_str, $subject_str, $text_str, $ticket_id, $responder_ids=0, $messageid=NULL, $server=NULL) {					// 7/7/09 - 5/25/2013
//	print $to_str . "," . $smsg_to_str . "," . $subject_str . "," . $text_str . "," . $ticket_id . "," . $responder_ids . "<BR />";
	$the_resp_ids = "";
	if($responder_ids != 0) {
		$the_responder_ids = explode("|", $responder_ids);
		$the_responders = "";
		$sep = "";
		$the_resp_ids = implode(",", $the_responder_ids);
		foreach($the_responder_ids as $val) {
			if($val == 0) {
				$the_responders = "Not Set";
				} else {
				$the_responders = get_responder($val) . $sep;
				$sep = ",";
				}
			}
		$the_responders = substr($the_responders,0,-1);
	} else {
		$the_responders = "";
	}

	$count_cells = $count_ll = $count_smsg = $count_tweets = 0;; 				// counters
	$theaddresses = "";
	global $istest;
	require_once('smtp.inc.php');     									// defer load until required - 8/2/10
	require_once("messaging.inc.php");     									// defer load until required - 4/24/12
	$sleep = 4;															// seconds delay between text messages
	$now = time() - (intval(intval(get_variable('delta_mins')))*60);
	$my_smtp_ary = explode ("/",  trim(get_variable('smtp_acct')));
	if ($to_str != "" && (count($my_smtp_ary)>1) && (count($my_smtp_ary)<5)) {					// 4/19/11, 10/23/12, 11/2/12
		 do_log($GLOBALS['LOG_ERROR'], 0, 0, "Invalid smtp account information: " . trim(get_variable('smtp_acct')));
		 return;
		}
	$temp = explode("/", trim(get_variable('email_reply_to')));
	if ($to_str != "" && !(is_email(trim($temp[0])))) {								// accommodate possible /B
		do_log($GLOBALS['LOG_ERROR'], 0, 0, "Invalid email reply-to: " . trim(get_variable('email_reply_to')));
		return ;
		}
	if(!function_exists('stripLabels')) {
		function stripLabels($sText){
			$labels = array("Incident:", "Priority:", "Nature:", "Addr:", "Descr:", "Reported by:", "Phone:", "Written:", "Updated:", "Status:", "Disp:", "Run Start:", "Map:", "Patient:", "Actions:", "Tickets host:"); // 5/9/10
			for ($x = 0; $x < count($labels); $x++) {
				$sText = str_replace($labels[$x] , '', $sText);
				}
			return $sText;
			}
		}
	$to_array = array_values(array_unique(explode ("|", ($to_str))));		// input is pipe-delimited string  - 10/17/08
	$to_smsg_array = ($smsg_to_str != NULL) ? array_values(array_unique(explode (",", ($smsg_to_str)))) : NULL;		// input is comma string  - 4/24/12
	require_once("cell_addrs.inc.php");										// 10/22/08
	$ary_cell_addrs = $ary_ll_addrs = $ary_twitter_addrs = array();
	if($to_str != "") {
		if(count($to_array) > 0) {
			for ($i = 0; $i < count($to_array); $i++) {								// walk down the input address string/array
				$isTwitter = (substr($to_array[$i], 0, 1) == "@") ? TRUE : FALSE;
				$temp =  explode ( "@", $to_array[$i]);
				include('cell_addrs.inc.php');										// 10/22/08
				if ($isTwitter) {
					$screen_name = substr($to_array[$i], 1);
					array_push ($ary_twitter_addrs, $screen_name);						// yes
					} elseif(in_array(trim(strtolower($temp[1])), $cell_addrs))  {				// cell addr?
					array_push ($ary_cell_addrs, $to_array[$i]);						// yes
					} else {																	// no, land line addr
					array_push ($ary_ll_addrs, $to_array[$i]);
					}
				}				// end for ($i = ...)
			$caption="";

			$my_from_ary = explode("/", trim(get_variable('email_from')));				// note /B option
			$my_replyto_str = trim(get_variable('email_reply_to'));
			if (count($ary_ll_addrs)>0) {												// got landline addee's?
				$theaddresses = implode(",", $ary_ll_addrs);
				if($the_responders == "") { $the_responders = $theaddresses;}
						//								($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str)
				if (count($my_smtp_ary)>1) {
					$count_ll = do_smtp_mail ($my_smtp_ary, $ary_ll_addrs, $subject_str, $text_str, $my_from_ary, $my_replyto_str );
					store_email(1, $the_responders, "email", $subject_str, $text_str, $ticket_id, $the_resp_ids, date("Y/m/d H:i:s", $now), $my_replyto_str, 'Tickets');	// 7/9/12
					} else {
		//								($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str)
					$count_ll = do_native_mail ($my_smtp_ary, $ary_ll_addrs, $subject_str, $text_str, $my_from_ary, $my_replyto_str );
					store_email(1, $the_responders, "email", $subject_str, $text_str, $ticket_id, $the_resp_ids, date("Y/m/d H:i:s", $now), $my_replyto_str, 'Tickets'); // 7/9/12
					}
				}
			if (count($ary_cell_addrs)>0) {		// got cell addee's?
				$theaddressess = implode(",", $ary_cell_addrs);
				if($the_responders == "") { $the_responders = $theaddresses;}
				$lgth = 140;
				$ix = 0;
				$i = 1;
				$cell_text_str = stripLabels($text_str);								// strip labels 5/10/10
				while (substr($cell_text_str, $ix , $lgth )) {							// chunk to $lgth-length strings
					$subject_ex = $subject_str . "/part " . $i . "/";					// 10/21/08
		//										 ($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str)
					if (count($my_smtp_ary)>1) {
						$count_cells = do_smtp_mail ($my_smtp_ary, $ary_cell_addrs, $subject_ex, substr ($cell_text_str, $ix , $lgth ), $my_from_ary, $my_replyto_str);
						store_email(1, $the_responders, "email", $subject_str, $text_str, $ticket_id, $the_resp_ids, date("Y/m/d H:i:s", $now), $my_replyto_str, 'Tickets');	 // 7/9/12
						} else {
		//										  ($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str)
						$count_cells = do_native_mail ($my_smtp_ary, $ary_cell_addrs, $subject_ex, substr ($cell_text_str, $ix , $lgth ), $my_from_ary, $my_replyto_str);
						store_email(1, $the_responders, "email", $subject_str, $text_str, $ticket_id, $the_resp_ids, date("Y/m/d H:i:s", $now), $my_replyto_str, 'Tickets');	 // 7/9/12
						if($i>1) {sleep ($sleep);}								// 10/17/08
						}	//	end if/else	(count($my_smtp_ary)>1))		// 12/13/2012
					$ix+=$lgth;
					$i++;
					}				// end while (substr($cell_text_...))
				}		// end if (count($ary_cell_addrs)>0)
			if (count($ary_twitter_addrs)>0) {
				for ($t = 0; $t < count($ary_twitter_addrs); $t++) {
					$theRet = send_tweet_direct($text_str, NULL, $ary_twitter_addrs[$t]);
					if(!is_int($theRet)) {
						print $theRet . "<BR />";
						} else {
						$count_tweets = $count_tweets + $theRet;
						}
					}
				}
			}	//	end if(count($to_array) > 0)
		}	//	end if($to_str != "")
	if($smsg_to_str != "") {
		if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
			if (count($to_smsg_array)>0) {		// got sms gateway addresses?
				$addressess = "";
				$cell_text_str = stripLabels($text_str);								// strip labels 5/10/10
				$count_smsg = do_smsg_send(get_msg_variable('smsg_orgcode'),get_msg_variable('smsg_apipin'),$subject_str,$cell_text_str,"CALLSIGNS",$smsg_to_str,"standard_priority",get_msg_variable('smsg_replyto'),"SENDXML", $ticket_id, $messageid, $server);
				}	// end if (count($to_smsg_array)>0)
			}	// end if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3))
		}	//	end if($smsg_to_str != "")
	return (string) ($count_ll + $count_cells + $count_smsg + $count_tweets);
	}					// end function do send ()

function is_email($email){		   //  validate email, code courtesy of Jerrett Taylor - 10/8/08, 7/2/10
	if(!preg_match( "/^" .
	"[a-zA-Z0-9]+([_\\.-][a-zA-Z0-9]+)*" .		//user
	"@" .
	"([a-zA-Z0-9]+([\.-][a-zA-Z0-9]+)*)+" .   	//domain
	"\\.[a-zA-Z]{2,}" .							//sld, tld
	"$/", $email, $regs)) {
			return FALSE;
			}
		else {
			return TRUE;
			}
		}							  // end function is_email()

function is_twitter($address) {
	$isTwitter = (substr($address, 0, 1) == "@") ? TRUE : FALSE;
	return $isTwitter;
	}

function get_scope($id) {
	$query = "SELECT `scope` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $id;
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	if(!$result || mysql_num_rows($result) == 0) {
		return "";
		} else {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['scope'];
		}
	}

function notify_user($ticket_id, $action_id) {								// 10/20/08, 5/22/11. 8/28/13
	if (get_variable('allow_notify') != '1') return FALSE;						//should we notify?
	$actionText = "";
	$query = "SELECT `scope`, `severity`, `facility`, `rec_facility`, `in_types_id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $ticket_id;
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	if(!$result || mysql_num_rows($result) == 0) {return;}
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$scope = $row['scope'];
	$facility = $row['facility'];
	$rec_facility = $row['rec_facility'];
	$in_types_id = $row['in_types_id'];
	$fields = array();
	$fields[$GLOBALS['NOTIFY_TICKET_CHG']] = "on_ticket";
	$fields[$GLOBALS['NOTIFY_ACTION_CHG']] = "on_action";
	$fields[$GLOBALS['NOTIFY_PERSON_CHG']] = "on_patient";
	$fields[$GLOBALS['NOTIFY_TICKET_CLOSE']] = "on_ticket";
	$fields[$GLOBALS['NOTIFY_TICKET_OPEN']] = "on_ticket";
	$addrs = array();															//
	$facaddrs = array();
	$assignsaddrs = array();
	$assignssmsaddrs = array();
	$intypeaddrs = array();
	$severity_filter = (intval($row['severity']) == $GLOBALS['SEVERITY_NORMAL'])? "(`severities` = 1 )" : "(`severities`= 3) OR (`severities`= 1)";		// 5/22/11

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE (
		{$severity_filter} AND
		(`ticket_id`={$ticket_id} OR `ticket_id`=0)  AND
		`{$fields[$action_id]}` = '1')";			// all notifies for given ticket - or any ticket 10/22/08

	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
		if (is_email($row['email_address'])) {
			array_push($addrs, $row['email_address']); // save for emailing
			}
		if($row['mailgroup'] != 0) {	//	8/28/13	Checks for maillist notifies
			$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup_x` WHERE `mailgroup` = " . $row['mailgroup'];
			$result_mg	= mysql_query($query_mg) or do_error($query_mg,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			while($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
				if($row_mg['contacts'] != 0) {
					$query_c = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` WHERE `id` = " . $row_mg['contacts'] . " LIMIT 1";
					$result_c	= mysql_query($query_c) or do_error($query_c,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
					$row_c = stripslashes_deep(mysql_fetch_assoc($result_c));
					if (is_email($row_c['email'])) {
						array_push($addrs, $row_c['email']); // save for emailing
						}
					} elseif($row_mg['responder'] != 0) {
					$addrs_arr = get_contact_via($row_mg['responder']);
//					$query_r = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $row_mg['responder'] . " LIMIT 1";
//					$result_r	= mysql_query($query_r) or do_error($query_r,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//					$row_r = stripslashes_deep(mysql_fetch_assoc($result_r));
					foreach($addrs_arr as $val) {
						if (is_email($val)) {
							array_push($addrs, $val); // save for emailing
							}
						}
					}
				}
			}
		}
	if((get_variable('notify_facilities') == "1") && (($facility != 0) || ($rec_facility != 0))) {	//	8/28/13
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . strip_tags($facility) . " OR `id` = " . strip_tags($rec_facility);
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
			$continue = false;
			if($row['notify_when'] == 1) {
				$continue = true;
				} elseif($row['notify_when'] == 2 && $action_id == $GLOBALS['NOTIFY_TICKET_OPEN']) {
				$continue = true;
				} elseif($row['notify_when'] == 3 && $action_id == $GLOBALS['NOTIFY_TICKET_CLOSE']) {
				$continue = true;
				} else {
				$continue = false;
				}
			if($continue) {
				if($row['notify_email'] != "") {
					if (is_email($row['notify_email'])) {
						array_push($facaddrs, $row['notify_email']); // save for emailing
						}
					} elseif($row['notify_mailgroup'] != 0) {	//	8/28/13	Checks for maillist notifies
					$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup_x` WHERE `mailgroup` = " . $row['notify_mailgroup'];
					$result_mg	= mysql_query($query_mg) or do_error($query_mg,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
					while($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
						if($row_mg['contacts'] != 0) {
							$query_c = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` WHERE `id` = " . $row_mg['contacts'] . " LIMIT 1";
							$result_c	= mysql_query($query_c) or do_error($query_c,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
							$row_c = stripslashes_deep(mysql_fetch_assoc($result_c));
							if (is_email($row_c['email'])) {
								array_push($facaddrs, $row_c['email']); // save for emailing
								}
							} elseif($row_mg['responder'] != 0) {
							$addrs_arr = get_contact_via($row_mg['responder']);
							foreach($addrs_arr as $val) {
								if (is_email($val)) {
									array_push($facaddrs, $val); // save for emailing
									}
								}
							}
						}
					}
				}
			}
		if ($facaddrs) {
			$theTo = implode("|", array_unique($facaddrs));
			$theText = "You are being notified as your facility is involved in resolution of incident: " . $scope;
			mail_it ($theTo, "", $theText, $ticket_id, 1 );
			}				// end if ($addrs)
		}
	if(get_variable('notify_in_types') == "1") {	//	9/10/13
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` WHERE `id` = " . strip_tags($in_types_id);
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
			$theType = $row['type'];
			$theDescription = $row['description'];
			$continue = false;
			if($row['notify_when'] == 1) {
				$continue = true;
				} elseif($row['notify_when'] == 2 && $action_id == $GLOBALS['NOTIFY_TICKET_OPEN']) {
				$continue = true;
				} elseif($row['notify_when'] == 3 && $action_id == $GLOBALS['NOTIFY_TICKET_CLOSE']) {
				$continue = true;
				} else {
				$continue = false;
				}
			if($continue) {
				if($row['notify_email'] != "") {
					if (is_email($row['notify_email'])) {
						array_push($intypeaddrs, $row['notify_email']); // save for emailing
						}
					} elseif($row['notify_mailgroup'] != 0) {	//	8/28/13	Checks for maillist notifies
					$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup_x` WHERE `mailgroup` = " . $row['notify_mailgroup'];
					$result_mg	= mysql_query($query_mg) or do_error($query_mg,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
					while($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
						if($row_mg['contacts'] != 0) {
							$query_c = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` WHERE `id` = " . $row_mg['contacts'] . " LIMIT 1";
							$result_c	= mysql_query($query_c) or do_error($query_c,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
							$row_c = stripslashes_deep(mysql_fetch_assoc($result_c));
							if (is_email($row_c['email'])) {
								array_push($intypeaddrs, $row_c['email']); // save for emailing
								}
							} elseif($row_mg['responder'] != 0) {
							$addrs_arr = get_contact_via($row_mg['responder']);
							foreach($addrs_arr as $val) {
								if (is_email($val)) {
									array_push($intypeaddrs, $val); // save for emailing
									}
								}
							}
						}
					}
				}
			}
		if ($intypeaddrs) {
			$theTo = implode("|", array_unique($intypeaddrs));
			$theText = "You are being notified as incident " . $scope . " has an incident type of " . $theType . " - " . $theDescription;
			mail_it ($theTo, "", $theText, $ticket_id, 1 );
			}				// end if ($addrs)
		}
	$notify_assigns = get_variable('notify_assigns');
	$defaultSMS = get_msg_variable('default_sms');
	// notify assigns options - 0 is off, 1 notify assigns on close, 2  notify on close and inc change, 3 notify on close, inc change and action or patient change, 4 notify changes only not close
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . strip_tags($ticket_id);
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//	Assignments this Ticket
		$responderID = $row['responder_id'];
		$tick_id = $row['ticket_id'];
		$addrs_arr = get_contact_via($responderID);
		$smsgaddrs_arr = get_smsgid($responderID);
		$continue = false;
		if($action_id == "0" || $action_id == "1" || $action_id == "2" || $action_id == "4") {
			switch ($action_id) {
				case ("0") :
					$actionText = "changed";
					break;
				case ("1") :
					$actionText = "changed";
					break;
				case ("2") :
					$actionText = "changed";
					break;
				case ("3") :
					$actionText = "";
					break;
				case ("4") :
					$actionText = "closed\r\n";
					break;
				default:
					$actionText = "changed";
					}
			switch ($notify_assigns) {		// what types of incident changes to send notify to assigned units
				case ("0") :
					$continue = false;	//	off
					break;
				case ("1") :
					$continue = ($action_id == 4) ? true : false;	//	close only
					break;
				case ("2") :
					$continue = ($action_id == 0 || $action_id == 4) ? true : false;	//	Incident change and close
					break;
				case ("3") :
					$continue = ($action_id == 0 || $action_id == 1 || $action_id == 2 || $action_id == 3 || $action_id == 4) ? true : false;	//	all changes and close
					break;
				case ("4") :
					$continue = ($action_id == 0 || $action_id == 1 || $action_id == 2 || $action_id == 3) ? true : false;	//	changes only, not on close
					break;
				default:
					$continue = false;
					}
			if($continue) {
				foreach($smsgaddrs_arr as $val) {
					if($val != "") {
						array_push($assignssmsaddrs, $val); // save for SMS
						}
					}
				foreach($addrs_arr as $val2) {
					if (is_email($val2)) {
						array_push($assignsaddrs, $val2); // save for emailing
						}
					}
				}
			}
		}
	if($actionText != "") {
		if ($assignsaddrs) {
			$theTo = implode("|", array_unique($assignsaddrs));
			$theSMSTo = implode(",", array_unique($assignssmsaddrs));
			$theText = "Incident " . $scope . " has " . $actionText;
			mail_it ($theTo, $theSMSTo, $theText, $ticket_id, 4 );
			}
		}
	$temp = array_values(array_unique($addrs));		// 5/22/10
	return (empty($temp))? FALSE: $temp;
	}

function notify_newreq($svceuser_id) {								// 10/23/12
	if (get_variable('allow_notify') != '1') return FALSE;
	$addrs = array();															//
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = '0' OR `level` = '1'";	//	Get all users admin and super that have valid email address stored and save for emailing.
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if (is_email($row['email'])) {
			array_push($addrs, $row['email']);
			} else {
			if(is_email($row['email_s'])) {
				array_push($addrs, $row['email_s']);
				}
			}
		}
	$temp = array_values(array_unique($addrs));
	return (empty($temp))? FALSE: $temp;
	}

function snap($source, $stuff = "") {									// 10/18/08 , 3/5/09 - debug tool
	global $snap_table;				// defined in istest.inc.php
	if (mysql_table_exists($snap_table)) {
		$query	= "DELETE FROM `$snap_table` WHERE `when`< (NOW() - INTERVAL 1 DAY)"; 		// first remove old
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		if (is_array ( $source )) {$source = "array (" . count($source) . ")";}

		$query = sprintf("INSERT INTO `$snap_table` (`source`,`stuff`)
			VALUES(%s,%s)",
				quote_smart_deep(trim($source)),
				quote_smart_deep(trim($stuff)));

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		unset($result);
		}
	else {
//		dump(__LINE__);
		}
	}		// end function snap()


function isFloat($n){														// 1/23/09
    return ( $n == strval(floatval($n)) )? true : false;
	}

function quote_smart($value) {												// 1/28/09
//	if (@ get_magic_quotes_gpc()) {		// Stripslashes
//		$value = stripslashes($value);
//		}
	if (!is_int($value)) {			// Quote if not a number or a numeric string
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

function my_is_float($n){									// 5/4/09
    return ((($n == strval(floatval($n))) || ($n == floatval($n))) && (!($n==0)) )? true : false;		//	6/10/13
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
	}	//end function LLtoOSGB

function my_date_diff_u ($d1_in, $d2_in) {		// end, start datetime strings in, returns string - 5/13/10 - 11/29/2012
	$d1 = strtotime($d1_in);				// string to integer
	$d2 = strtotime($d2_in);
	if ($d1 < $d2){						// check higher timestamp and switch if neccessary
		$temp = $d2;
		$d2 = $d1;
		$d1 = $temp;
		}
	else {
		$temp = $d1; //temp can be used for day count if required
		}
	$d1 = date_parse(date("Y-m-d H:i:s", (integer)$d1));
	$d2 = date_parse(date("Y-m-d H:i:s", (integer)$d2));

	if ($d1['second'] >= $d2['second']){	//seconds
		$diff['second'] = $d1['second'] - $d2['second'];
		}
	else {
		$d1['minute']--;
		$diff['second'] = 60-$d2['second']+$d1['second'];
		}
	if ($d1['minute'] >= $d2['minute']){	//minutes
		$diff['minute'] = $d1['minute'] - $d2['minute'];
		}
	else {
		$d1['hour']--;
		$diff['minute'] = 60-$d2['minute']+$d1['minute'];
		}
	if ($d1['hour'] >= $d2['hour']){	//hours
		$diff['hour'] = $d1['hour'] - $d2['hour'];
		}
	else {
		$d1['day']--;
		$diff['hour'] = 24-$d2['hour']+$d1['hour'];
		}
	if ($d1['day'] >= $d2['day']){	//days
		$diff['day'] = $d1['day'] - $d2['day'];
		}
	else {
		$d1['month']--;
		$diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
		}
	if ($d1['month'] >= $d2['month']){	//months
		$diff['month'] = $d1['month'] - $d2['month'];
		}
	else {
		$d1['year']--;
		$diff['month'] = 12-$d2['month']+$d1['month'];
		}
	$diff['year'] = $d1['year'] - $d2['year'];	//years

	$out_str = "";
	$plural = ($diff['year'] == 1)? "": "s";								// needless elegance
	$out_str .= empty($diff['year'])? "" : "{$diff['year']} yr{$plural}, ";

	$plural = ($diff['month'] == 1)? "": "s";
	$out_str .= empty($diff['month'])? "" : "{$diff['month']} mo{$plural}, ";

	$plural = ($diff['day'] == 1)? "": "s";
	$out_str .= empty($diff['day'])? "" : "{$diff['day']} day{$plural}, ";

	$plural = ($diff['hour'] == 1)? "": "s";
	$out_str .= empty($diff['hour'])? "" : "{$diff['hour']} hr{$plural}, ";

	$plural = ($diff['minute'] == 1)? "": "s";
	$out_str .= empty($diff['minute'])? "" : "{$diff['minute']} min{$plural}";
	return  $out_str;
	}

function my_date_diff($d1_in, $d2_in) {		// end, start datetime strings in, returns string - 5/13/10 - 11/29/2012
	$d1 = strtotime($d1_in);				// string to integer
	$d2 = strtotime($d2_in);
	if ($d1 < $d2){						// check higher timestamp and switch if neccessary
		$temp = $d2;
		$d2 = $d1;
		$d1 = $temp;
		} else {
		$temp = $d1; //temp can be used for day count if required
		}
	$d1 = date_parse(date("Y-m-d H:i:s", (integer)$d1));
	$d2 = date_parse(date("Y-m-d H:i:s", (integer)$d2));

	if ($d1['second'] >= $d2['second']){	//seconds
		$diff['second'] = $d1['second'] - $d2['second'];
		}
	else {
		$d1['minute']--;
		$diff['second'] = 60-$d2['second']+$d1['second'];
		}
	if ($d1['minute'] >= $d2['minute']){	//minutes
		$diff['minute'] = $d1['minute'] - $d2['minute'];
		}
	else {
		$d1['hour']--;
		$diff['minute'] = 60-$d2['minute']+$d1['minute'];
		}
	if ($d1['hour'] >= $d2['hour']){	//hours
		$diff['hour'] = $d1['hour'] - $d2['hour'];
		}
	else {
		$d1['day']--;
		$diff['hour'] = 24-$d2['hour']+$d1['hour'];
		}
	if ($d1['day'] >= $d2['day']){	//days
		$diff['day'] = $d1['day'] - $d2['day'];
		}
	else {
		$d1['month']--;
		$diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
		}
	if ($d1['month'] >= $d2['month']){	//months
		$diff['month'] = $d1['month'] - $d2['month'];
		}
	else {
		$d1['year']--;
		$diff['month'] = 12-$d2['month']+$d1['month'];
		}
	$diff['year'] = $d1['year'] - $d2['year'];	//years

	$out_str = "";
	$plural = ($diff['year'] == 1)? "": "s";								// needless elegance
	$out_str .= empty($diff['year'])? "" : "{$diff['year']} yr{$plural}, ";

	$plural = ($diff['month'] == 1)? "": "s";
	$out_str .= empty($diff['month'])? "" : "{$diff['month']} mo{$plural}, ";

	$plural = ($diff['day'] == 1)? "": "s";
	$out_str .= empty($diff['day'])? "" : "{$diff['day']} day{$plural}, ";

	$plural = ($diff['hour'] == 1)? "": "s";
	$out_str .= empty($diff['hour'])? "" : "{$diff['hour']} hr{$plural}, ";

	$plural = ($diff['minute'] == 1)? "": "s";
	$out_str .= empty($diff['minute'])? "" : "{$diff['minute']} min{$plural}";
	return  $out_str;
	}

/* - 5/20/2013
function get_elapsed_time ($in_start, $in_end) {		// datetime strings - 11/30/2012
	if (!(good_date_time($in_end))) {					// possibly open
		$in_end = date("Y-m-d H:i:00", (time() - (intval(get_variable('delta_mins'))*60)));		// current local time to timestamp format
		return "(" . my_date_diff($in_start, $in_end) . ")";		// identify as 'now' time difference
		}
	else {
		return my_date_diff($in_start, $in_end);
		}
	}
*/

function get_elapsed_time ($in_row) {						// ex: 2012-03-29 14:37:10	- 5/20/2013
	$end_date = (good_date_time($in_row['problemend']))? $in_row['problemend'] :  now_ts();	// string
	$start_date = ($in_row['status'] == $GLOBALS['STATUS_SCHEDULED'] )? $in_row['booked_date'] : $in_row['problemstart'];
	if(is_numeric($start_date)) $start_date = date("Y-m-d H:i:s", $start_date);
	return my_date_diff_u ( $start_date , $end_date);
	}

function expires() {
	$deltamins = (get_variable('delta_mins') != "") ? intval(get_variable('delta_mins')) : 0;
	$now = time() - ($deltamins*60);
	$sessionTimeout = (intval(get_variable('session_timeout')) != 0) ? intval(get_variable('session_timeout')) : 60;
	return $now + (60*$sessionTimeout);
	}

function get_unit_icon($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$icon_str = $row['icon_str'];
		} else {
		$icon_str = "UNK";
		}
	return $icon_str;
	}

function get_facility_icon($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$icon_str = $row['icon_str'];
		} else {
		$icon_str = "UNK";
		}
	return $icon_str;
	}

function get_status_sel($unit_in, $status_val_in, $tbl_in) {					// returns select list as click-able string - 2/6/10
	$icon_str = ($tbl_in == "u") ? get_unit_icon($unit_in) : get_facility_icon($unit_in);
	switch ($tbl_in) {
		case ("u") :
			$tablename = "responder";
			$link_field = "un_status_id";
			$status_table = "un_status";
			$status_field = "status_val";
			break;
		case ("f") :
			$tablename = "facilities";
			$link_field = "status_id";
			$status_table = "fac_status";
			$status_field = "status_val";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";
			}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}`, `$GLOBALS[mysql_prefix]{$status_table}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $unit_in
		AND `$GLOBALS[mysql_prefix]{$status_table}`.`id` = `$GLOBALS[mysql_prefix]{$tablename}`.`{$link_field}` LIMIT 1" ;

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {				// 2/7/10
		$init_bg_color = "transparent";
		$init_txt_color = "black";
		}
	else {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$init_bg_color = $row['bg_color'];
		$init_txt_color = $row['text_color'];
		}

	$guest = is_guest();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` ORDER BY `group` ASC, `sort` ASC, `{$status_field}` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = ($tbl_in == "u") ? "\t\t<SELECT CLASS='sit text' id='frm_status_id_u_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update({$unit_in}, this.value, \"{$icon_str}\")' >" :
	"\t\t<SELECT CLASS='sit text' id='frm_status_id_f_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 90%;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update_fac({$unit_in}, this.value, \"{$icon_str}\")' >";	// 12/19/09, 1/1/10. 3/15/11
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP CLASS='text' LABEL='$the_grp'>";
			}
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION CLASS='text' VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};' onMouseover = 'style.backgroundColor = this.backgroundColor;'>$row[$status_field] </OPTION>";
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\t\t</SELECT>";
	return $outstr;
	}

function curr_regs() {	//	10/18/11	Gets currently allocated or viewed regions
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	10/18/11
	$result = mysql_query($query);
	$al_groups = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$al_groups[] = $row['group'];
		}

	if(array_key_exists('viewed_groups', $_SESSION)) {
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	if(!isset($curr_viewed)) {
		if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {
			$x=0;	//	6/10/11
			$where = "WHERE (";
			foreach($al_groups as $grp) {
				$where2 = (count($al_groups) > ($x+1)) ? " OR " : ")";
				$where .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where .= $where2;
				$x++;
				}
			$where .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	sets the region allocations searched for to type = 3 - Facilities.
			}
		} else {
		if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where = "WHERE `a`.`type` = 2";
			} else {
			$x=0;	//	6/10/11
			$where = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {
				$where2 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
				$where .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where .= $where2;
				$x++;
				}
			$where .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	sets the region allocations searched for to type = 3 - Facilities.
			}
		}
	return $where;
	}

function get_recfac_sel($unit_in, $tickid, $assign_id) {					// 10/18/11 - Gets select menu for receiving facility control on mobile page
	$where = curr_regs();
	$query01 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`id` = " . $assign_id . " LIMIT 1";
	$result01 = mysql_query($query01) or do_error($query01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row01 = stripslashes_deep(mysql_fetch_assoc($result01))) {
		$curr_fac = $row01['rec_facility_id'];
		}

	$query02 = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id`
			FROM `$GLOBALS[mysql_prefix]facilities`
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
			$where GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
	$result02 = mysql_query($query02) or do_error($query02, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$guest = is_guest();
	$dis = ($guest)? " DISABLED": "";
	$i = 0;
	$outstr = "\t\t<SELECT CLASS='sit text' style='width: 90%;' name='frm_rec_fac' {$dis} ONCHANGE = 'set_rec_fac(this.value)' >";
	if($curr_fac == 0) {
		$outstr .= "\t\t\t<OPTION CLASS='text' VALUE=0 SELECTED>None Selected</OPTION>";
		} else {
		$outstr .= "\t\t\t<OPTION CLASS='text' VALUE=0>None Selected</OPTION>";
		}
	while ($row02 = stripslashes_deep(mysql_fetch_assoc($result02))) {
		$sel = ($row02['fac_id'] == $curr_fac)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION CLASS='text' VALUE=" . $row02['fac_id'] . $sel .">" . $row02['name'] . "</OPTION>";
		$i++;
		}		// end while()
	$outstr .= "\t\t</SELECT>";
	return $outstr;
	}

function get_units_legend() {		// returns string as centered span - 2/8/10
	$query = "SELECT DISTINCT `type`, `icon`,  `$GLOBALS[mysql_prefix]unit_types`.`name` AS `mytype` FROM `$GLOBALS[mysql_prefix]responder`
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` ON `$GLOBALS[mysql_prefix]unit_types`.`id` = `$GLOBALS[mysql_prefix]responder`.`type` ORDER BY `mytype`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$out_str = "<SPAN CLASS = 'even text' ALIGN = 'center'><SPAN CLASS = 'even text' ALIGN = 'center'> Units Types: </SPAN>&nbsp;";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_bg_color = array_key_exists($row['icon'], $GLOBALS['UNIT_TYPES_BG']) ?	$GLOBALS['UNIT_TYPES_BG'][$row['icon']] : "#FFFFFF";
		$the_text_color = array_key_exists($row['icon'], $GLOBALS['UNIT_TYPES_TEXT']) ?	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']] : "000000";
		$out_str .= "<SPAN CLASS='text' STYLE='padding: 2px; border: 1px outset #707070; background-color:{$the_bg_color}; opacity: .7; color:{$the_text_color}'> {$row['mytype']}</SPAN>&nbsp;";
		}
	return $out_str .= "</SPAN>";
	}										// end function get_units_legend()

function get_wl_legend() {		// returns string as centered span - 2/8/10
	$out_str = "<SPAN class = 'even text' ALIGN = 'center'><SPAN CLASS = 'even text' ALIGN='center'> Warn Location Types: </SPAN>&nbsp;";	//	3/15/11
	$warn_types = array();
	foreach($GLOBALS['LOC_TYPES'] as $val) {
		$warn_types[$val] = $GLOBALS['LOC_TYPES_NAMES'][$val];
		}
	foreach ($warn_types as $key => $value) {
		$the_bg_color = array_key_exists($key, $GLOBALS['LOC_TYPES_BG']) ? 	$GLOBALS['LOC_TYPES_BG'][$key]: "#FFFFFF";
		$the_text_color = array_key_exists($key, $GLOBALS['LOC_TYPES_TEXT']) ? $GLOBALS['LOC_TYPES_TEXT'][$key] : "#000000";
		$theName = array_key_exists($key, $GLOBALS['LOC_TYPES_NAMES']) ? $GLOBALS['LOC_TYPES_NAMES'][$key] : "Error";
		$out_str .= "<SPAN CLASS='text' STYLE='padding: 2px; border: 1px outset #707070; background-color:{$the_bg_color}; opacity: .7; color:{$the_text_color}'> {$theName}</SPAN>&nbsp;";
		}
	return $out_str .= "</SPAN>";
	}										// end function get_units_legend()

function get_facilities_legend() {		// returns string as centered row - 2/8/10
	$query = "SELECT DISTINCT `type`, `icon`,  `$GLOBALS[mysql_prefix]fac_types`.`name` AS `mytype` FROM `$GLOBALS[mysql_prefix]facilities`
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]fac_types`.`id` = `$GLOBALS[mysql_prefix]facilities`.`type` ORDER BY `mytype`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$out_str = "<SPAN class = 'even text' ALIGN = 'center'><SPAN CLASS = 'even text' ALIGN='center'> Facilitiy types: </SPAN>&nbsp;";	//	3/15/11
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$the_bg_color = array_key_exists($row['icon'], $GLOBALS['FACY_TYPES_BG']) ? $GLOBALS['FACY_TYPES_BG'][$row['icon']] : "#FFFFFF";
		$the_text_color = array_key_exists($row['icon'], $GLOBALS['FACY_TYPES_TEXT']) ? $GLOBALS['FACY_TYPES_TEXT'][$row['icon']] : "#000000";
		$out_str .= "<SPAN CLASS='text' STYLE='padding: 2px; border: 1px outset #707070; background-color:{$the_bg_color}; opacity: .7; color:{$the_text_color}'> {$row['mytype']} </SPAN>&nbsp;";
		}
	return $out_str .= "</SPAN>";
	}										// end function get_facilities_legend()

function is_phone ($instr) {		// 3/13/10
	if(get_variable("locale")==0){
		return ((strlen(trim($instr))==9) && (is_numeric($instr))) ;
		}
	else {
		return (is_numeric($instr));
		}
	}
function get_unit_status_legend() {		// returns string as div - 3/21/10
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `status_val`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$out_str = "<DIV style='width: 100%;'><DIV STYLE='width: 5%; display: inline-block; vertical-align: top;'> Status legend: </DIV>&nbsp;<DIV STYLE='width: 92%; display: inline-block;'>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$out_str .= "<SPAN class='text' STYLE='padding: 2px; border: 1px outset #707070; display: inline-block; background-color:{$row['bg_color']}; color:{$row['text_color']}; word-break: normal; padding: 3px; white-space: nowrap; padding: 2px;'>{$row['status_val']}</SPAN>&nbsp;";
		}
	return $out_str .= "</DIV></DIV>";
	}										// end function get_unit_status_legend()

function get_un_div_height ($in_max) {				//	compute pixels min 260, max .5 x screen height - 2/8/10
	$min = 80 ;
	$max = round($in_max * $_SESSION['scr_height']);
	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";
	$result_unit = mysql_query($query) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	unset ($result_unit);
	$required = 96 + (mysql_affected_rows()*22);		// 7/9/10

//	$required = mysql_affected_rows() * 23;		// pixels per line
	if ($required < $min)	{return $min;}
	else					{return ($required > $max)?   $max:  $required;}
	}		// end function un_div_height ()

function get_sess_vbl ($in_str) {				//
	$default = 'error';
	@session_start();
	return (array_key_exists ( $in_str, $_SESSION ))?  $_SESSION [$in_str]: $default;
	}		// end get_sess_vbl()

function now_ts() {		// returns date time as a timestamp - 5/19/2013
	return mysql_format_date(time() - intval(get_variable('delta_mins'))*60);
	}

function now() {		// returns date as integer
	return (time() - intval(get_variable('delta_mins'))*60);
	}
function monday() {		// returns date
	return strtotime("last Monday");
	}
function day() {		// returns number
	return date("d", now());
	}
function month() {		// returns number
	return date("n", now());
	}
function year() {		// returns number
	return date("Y", now());
	}

function get_start($local_func){						// 5/2/10
	switch ($local_func) {
		case 1 :		// Today
			return mysql_format_date(mktime( 0, 0, 0, month(), day(), year()));		// m, d, y -- date ('D, M j',
			break;

		case 2 :		// Yesterday+
			return mysql_format_date(mktime(0,0,0, month(), (day()-1), year()));		// m, d, y -- date ('D, M j',
			break;

		case 3 :		// This week
			return mysql_format_date(monday());						// m, d, y -- date ('D, M j',
			break;

		case 4 :		// Last week
			return mysql_format_date(monday() - 7*24*3600);			// m, d, y -- monday a week ago
			break;

		case 5 :		// Last week+
			return mysql_format_date(monday() - 7*24*3600);			// m, d, y -- monday a week ago
			break;

		case 6 :		// This month
			return mysql_format_date(mktime(0,0,0,  month(), 1, year()));				// m, d, y -- date ('D, M j',
			break;

		case 7 :		// Last month
			return mysql_format_date(mktime(0,0,0, (month()-1), 1, year()));			// m, d, y -- date ('D, M j',
			break;

		case 8 :		// This year
			return mysql_format_date(mktime(0,0,0, 1, 1, year()));						// m, d, y -- date ('D, M j',
			break;

		case 9 :		// Last year
			return mysql_format_date(mktime(0,0,0, 1, 1, (year()-1)));		// m, d, y -- date ('D, M j',
			break;

		default:
			echo __LINE__ . " error error error error error \n";
			}
		}		// end function get_start

function get_end($local_func){
	switch ($local_func) {
		case 1 :		// Today
		case 2 :		// Yesterday+
		case 3 :		// This week
		case 5 :		// Last week+
		case 6 :		// This month
		case 8 :		// This year
			return mysql_format_date(mktime( 23,59,59, month(), day(), year()));		// m, d, y -- date ('D, M j',

//			return mysql_format_date(now());		// m, d, y -- date ('D, M j',
			break;

		case 4 :		// Last week
			return mysql_format_date(monday()-1);			// m, d, y -- last monday
			break;

		case 7 :		// Last month
			return mysql_format_date(mktime(0,0,0, month(), 1,year()));		// m, d, y -- date ('D, M j',
			break;

		case 9 :		// Last year
			return mysql_format_date(mktime(23,59,59, 12,31, (year()-1)));		// m, d, y -- date ('D, M j',
			break;

		default:
			echo __LINE__ . " error error error error error \n";
			}
		}		// end function get_end

function get_cb_height () {		// returns pixel count for cb frame	height based on no. of lines - 7/10/10
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";		// 2/12/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$lines = mysql_num_rows($result);
	unset($result);

	$cb_per_line = 22;				// via trial and error
	$cb_fixed_part = 60;
	$cb_min = 96;
	$cb_max = 300;

	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;
	$height = ($height>$cb_max)? $cb_max: $height;

	return (integer) $height;
	}		// function get_cb_height ()


$text_array = array();
function get_text($which){		/* get replacement text from db captions table, returns FALSE if absent  */
	global $text_array;
	if (empty($text_array)) {	// populate it to avoid hammering db
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]captions`") or do_error("get_text({$which})::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$capt = $row['capt'];
			$repl=$row['repl'] ;
			$text_array[$capt] = $repl;
			}
		}
	return (array_key_exists($which, $text_array))? $text_array[$which] : $which ;
	}

$tips_array = array();
function get_tip($which){		/* get replacement text from db tips table, returns FALSE if absent  */
	global $tips_array;
	if (empty($tips_array)) {	// populate it to avoid hammering db
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]tips`") or do_error("get_tip({$which})::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$title = $row['title'];
			$tip = $row['tip'] ;
			$tips_array[$title] = $tip;
			}
		}
	return (array_key_exists($which, $tips_array))? $tips_array[$which] : $which ;
	}

function can_edit() {
	$retval = false;
	if(is_administrator() || is_super() || is_manager()) {
		$retval = true;
		} elseif(is_user() && get_variable('oper_can_edit') == 1) {
		$retval = true;
		} elseif(is_unit() && get_variable('unit_can_edit') == 1) {
		$retval = true;
		} else {
		$retval = false;
		}
	return $retval;
	} 	// end function can_edit()

function can_view() {
	$retval = false;
	if(is_administrator() || is_super() || is_manager() || is_user()) {
		$retval = true;
		} else {
		$retval = false;
		}
	return $retval;
	} 	// end function can_edit()


function do_diff($indx, $row){		// returns diff in seconds from problemstart- 9/29/10
	switch ($indx) {
		case 0:
			$temp = mysql2timestamp($row['dispatched']);
		    break;
		case 1:
			$temp = mysql2timestamp($row['responding']);
		    break;
		case 2:
			$temp = mysql2timestamp($row['on_scene']);
		    break;
		case 3:
			$temp = mysql2timestamp($row['u2fenr']);		// 10/19/10
		    break;
		case 4:
			$temp = mysql2timestamp($row['u2farr']);
		    break;
		case 5:
			$temp = mysql2timestamp($row['clear']);
		    break;
		case 6:
			$temp = mysql2timestamp($row['problemend']);
		    break;
		default:
			dump($indx);				// error  error  error  error  error
		}
	return $temp - mysql2timestamp($row['problemstart']);
	}

function elapsed ($in_time) {			// 4/26/11
	$mins = (integer) (round ((now() - mysql2timestamp($in_time)) / 60.0));
	return ($mins> 99)? 99: $mins;
	}				// end function elapsed

function get_disp_status ($row_in) {			// 4/26/11
	extract ($row_in);
	$tags_arr = explode("/", get_variable('disp_stat'));

	if (is_date($u2farr)) 		{ return "<SPAN CLASS='disp_stat'>&nbsp;{$tags_arr[4]}&nbsp;" . elapsed ($u2farr) . "</SPAN>";}
	if (is_date($u2fenr)) 		{ return "<SPAN CLASS='disp_stat'>&nbsp;{$tags_arr[3]}&nbsp;" . elapsed ($u2fenr) . "</SPAN>";}
	if (is_date($on_scene)) 	{ return "<SPAN CLASS='disp_stat'>&nbsp;{$tags_arr[2]}&nbsp;" . elapsed ($on_scene) . "</SPAN>";}
	if (is_date($responding))	{ return "<SPAN CLASS='disp_stat'>&nbsp;{$tags_arr[1]}&nbsp;" . elapsed ($responding) . "</SPAN>";}
	if (is_date($dispatched))	{ return "<SPAN CLASS='disp_stat'>&nbsp;{$tags_arr[0]}&nbsp;" . elapsed ($dispatched) . "</SPAN>";}
	}

function auto_disp_status($disp_status, $responder, $tick_id=0) {	//	8/22/13
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_disp_status` WHERE `id` = " . $disp_status . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) >= 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_val = intval($row['status_val']);
		$query2 = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id` = " . $the_val . ", `user_id` = '999999', `status_updated` = '" . $now . "', `updated`= '" . $now . "' WHERE `id`=" . $responder;
		$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		if($result2) {
			$the_ret = $the_val;
			do_log($GLOBALS['LOG_UNIT_STATUS'], $tick_id, $responder, $the_val);
			} else {
			$the_ret = 0;
			}
		} else {
		$the_ret = 0;
		}
	return $the_ret;
	}

// 5/11/2013 fix to remove '_on'  change ' _by' to 'user_id' from set_u_updated () sql  - 6/10/2013
function set_u_updated ($in_assign) {			// given a disaptch record id, updates unit data - 9/1/10
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` =  {$in_assign} LIMIT 1";
	$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
	$row_temp = mysql_fetch_assoc($result);					//
	$now = quote_smart(mysql_format_date(time() - (intval(get_variable('delta_mins'))*60)));														// 9/1/10
	$user = quote_smart(trim($_SESSION['user_id']));
	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
		`updated`= 			{$now},
		`user_id`=   		{$user}
		WHERE `id`=			{$row_temp['responder_id']}";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	unset($result);
	return TRUE;
	}		// end function set_u_updated (

function short_ts($in_str){		// ex:10/29/10 12:22 - 10/2/10
	return substr($in_str, -5);
	}

function get_dist_factor() {							// returns distance conversion factor - 11/24/10
	$factors = array("0.6214", "0.6214", "1.0");		// factors as strings
	return $factors[get_variable("locale")];			// US, UK, ROW
	}

function get_speed ($instr, $inspeed) {					// 11/26/10
	if (!(is_int($inspeed)))	{$the_class='unk';}
	elseif ($inspeed >= 50) 		{$the_class='fast'; }
	elseif ($inspeed == 0)  		{$the_class='stopped'; }
	else							{$the_class='moving'; }
	return "<SPAN CLASS='TD {$the_class}'>&nbsp;{$instr}&nbsp;</SPAN>";
	}

function get_remote($url, $json=TRUE) {				// 11/26/10	, 4/23/11
	if (function_exists("curl_init")) {
		$ch = curl_init();
		$timeout = 10;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno > 0) {
			print $curl_error . "<BR />";
			}
		} else {				// no CURL
		$data = "";
		if ($fp = @fopen($url, "r")) {
			while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
			fclose($fp);
			}
		}
	if($data) {
		if ($json) {				// 4/23/11
			$data = ($data) ? json_decode($data): FALSE;			// FALSE if fails
			} else {
			$data = ($data) ?  $data: FALSE;						// FALSE if fails
			}
		return $data;
		} else {
		return FALSE;
		}
	}	// end function get remote()


function get_hints($instr) {		// returns associative array - 11/30/10
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]hints` WHERE `form` = '{$instr}' ";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	$hints = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$hints[$row['ident']] = $row['title'];
		}
	return($hints);
	}						// end function

function get_regions_buttons($user_id) {		//	4/12/12
	global $evenodd;
	$regs_viewed = "";
	if(array_key_exists('viewed_groups', $_SESSION)) {
		$regs_viewed= explode(",",$_SESSION['viewed_groups']);
		}
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$user_id' ORDER BY `group`";			//	5/3/11
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

	$al_buttons="";
	$i = 1;
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{		//	4/12/12
		if(!empty($regs_viewed)) {
			if(in_array($row2['group'], $regs_viewed)) {
				$al_buttons.="<SPAN CLASS = '{$evenodd[$i%2]}'style='font-size: .7em;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' onClick=\"check_checkboxes(document.region_form, 'chk_spn', 'clr_spn');\" VALUE='{$row2['group']}'>" . get_groupname($row2['group']) . "</INPUT></SPAN><BR />";
			} else {
				$al_buttons.="<SPAN CLASS = '{$evenodd[$i%2]}' style='font-size: .7em;'><INPUT TYPE='checkbox' name='frm_group[]' onClick=\"check_checkboxes(document.region_form, 'chk_spn', 'clr_spn');\" VALUE='{$row2['group']}'>" . get_groupname($row2['group']) . "</INPUT></SPAN><BR />";
			}
			} else {
				$al_buttons.="<SPAN CLASS = '{$evenodd[$i%2]}' style='font-size: .7em;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' onClick=\"check_checkboxes(document.region_form, 'chk_spn', 'clr_spn');\" VALUE='{$row2['group']}'>" . get_groupname($row2['group']) . "</INPUT></SPAN><BR />";
			}
		$i++;
		}
	return $al_buttons;
	}

function get_regions_buttons2($user_id) {		//	4/12/12
	if(array_key_exists('viewed_groups', $_SESSION)) {
		$regs_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$user_id' ORDER BY `group`";			//	5/3/11
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

	$al_buttons="";
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	//	5/3/11
		if(!empty($regs_viewed)) {
			if(in_array($row2['group'], $regs_viewed)) {
				$al_buttons.="<DIV style='display: inline; float: left; word-wrap: normal; white-space: nowrap;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' onClick=\"check_checkboxes(document.region_form, 'chk_spn', 'clr_spn');\" VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV><BR />";
			} else {
				$al_buttons.="<DIV style='display: inline; float: left; word-wrap: normal; white-space: nowrap;'><INPUT TYPE='checkbox' name='frm_group[]' onClick=\"check_checkboxes(document.region_form, 'chk_spn', 'clr_spn');\" VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV><BR />";
			}
			} else {
				$al_buttons.="<DIV style='display: inline; float: left; word-wrap: normal; white-space: nowrap;'><INPUT TYPE='checkbox' CHECKED name='frm_group[]' onClick=\"check_checkboxes(document.region_form, 'chk_spn', 'clr_spn');\" VALUE='{$row2['group']}'></INPUT>" . get_groupname($row2['group']) . "&nbsp;&nbsp;</DIV><BR />";
			}
		}
	return $al_buttons;
	}

function clean_string($value) {	//	10/23/12
	// if(@ get_magic_quotes_gpc()) {
		// $value = stripslashes($value);
		// }
	return mysql_real_escape_string($value);
	}

function get_buttons_inner(){		//	4/12/12, 4/2/14
	if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11
?>
		<SCRIPT>
		side_bar_html= "";
		side_bar_html +="<form name='region_form' METHOD='post' action=\"<?php print $_SERVER['PHP_SELF'];?>\"><DIV><SPAN class='but_hdr'>Regions</SPAN>";
		side_bar_html +="<?php print get_regions_buttons($_SESSION['user_id']);?>";
		side_bar_html +="<SPAN id='reg_sub_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='form_validate(document.region_form);'>Update</SPAN>";
		side_bar_html +="<SPAN id='expand_regs' class='plain' style='z-index:1001; cursor: pointer; float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onclick=\"$('top_reg_box').style.display = 'none'; $('regions_outer').style.display = 'block';\">Undock</SPAN></DIV></form>";
		$("region_boxes").innerHTML = side_bar_html;
		</SCRIPT>
<?php
		}
	}

function get_buttons_inner2(){		//	4/12/12, 4/2/14
	if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11
?>
		<SCRIPT>
		side_bar_html= "";
		side_bar_html+="<form name='region_form2' METHOD='post' action=\"<?php print $_SERVER['PHP_SELF'];?>\"><DIV><SPAN class='but_hdr'>Regions</SPAN><BR /><BR />";
		side_bar_html += "<?php print get_regions_buttons2($_SESSION['user_id']);?><BR /><BR />";
		side_bar_html+="<BR /><BR /><SPAN id='reg_sub_but2' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='form_validate(document.region_form2);'>Update</SPAN></DIV></form>";
		$("region_boxes2").innerHTML = side_bar_html;
		</SCRIPT>
<?php
		}
	}

function get_remote_type ($inrow) { 							// returns type of remote - 12/3/10
	if ($inrow['aprs'] == 1) 				 		{ return $GLOBALS['TRACK_APRS']; }
	elseif ((int)$inrow['instam'] == 1) 	 		{ return $GLOBALS['TRACK_INSTAM']; }
	elseif ((int)$inrow['locatea'] == 1)	 		{ return $GLOBALS['TRACK_LOCATEA']; }
	elseif ((int)$inrow['gtrack'] == 1)		 		{ return $GLOBALS['TRACK_GTRACK']; }
	elseif ((int)$inrow['glat'] == 1)		 		{ return $GLOBALS['TRACK_GLAT']; }
	elseif ((int)$inrow['t_tracker'] == 1)	 		{ return $GLOBALS['TRACK_T_TRACKER']; }
	elseif ((int)$inrow['ogts'] == 1)		 		{ return $GLOBALS['TRACK_OGTS']; }			// 7/5/11
	elseif ((int)$inrow['mob_tracker'] == 1) 		{ return $GLOBALS['TRACK_MOBILE']; }		// 9/6/13
	elseif ((int)$inrow['xastir_tracker'] == 1) 	{ return $GLOBALS['TRACK_XASTIR']; }		// 1/30/14
	elseif ((int)$inrow['followmee_tracker'] == 1) 	{ return $GLOBALS['TRACK_FOLLOWMEE']; }		// 1/30/14
	elseif ((int)$inrow['traccar'] == 1) 			{ return $GLOBALS['TRACK_TRACCAR']; }		// 1/30/14
	elseif ((int)$inrow['javaprssrvr'] == 1) 		{ return $GLOBALS['TRACK_JAVAPRSSRVR']; }	// 6/30/17
	else 									 		{ return $GLOBALS['TRACK_NONE']; }			// 6/30/17
	}  				// end function

function is_cloud() {						// 12/4/10
	return (!(get_variable('_cloud')==0));
	}

function get_unit(){									//			returns unit index string - 3/19/11
	if  (!(array_key_exists('user_unit_id', $_SESSION)) && (!@intval($_SESSION['user_unit_id'])> 0)) {
		return FALSE;
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = {$_SESSION['user_unit_id']} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if ((mysql_num_rows($result))==0)  {
			unset($result);
			return FALSE;
			} else {
			$row = stripslashes_deep(mysql_fetch_array($result));
			$temp = explode("/", $row['name'] );
			$index = substr($temp[count($temp) -1], -6,strlen($temp[count($temp) -1]));
			unset($result);
			return $index;
			}
		}		// end if/else
	}		// end function get_unit()

function get_handle(){									//			returns unit index string - 3/19/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = {$_SESSION['user_unit_id']} LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_num_rows($result))==0)  {
		unset($result);
		return "Mobile";
		} else {
		$row = stripslashes_deep(mysql_fetch_array($result));
		$handle = ($row['handle'] != "") ? $row['handle'] : "Mobile";
		unset($result);
		return $handle;
		}		// end if/else
	}		// end function get_unit()

function get_respondername($id) {
	if(!$id) {return "N/A";}
	$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`=" . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_num_rows($result))==0)  {
		$ret_val = "NA";
		} else {
		$row = stripslashes_deep(mysql_fetch_array($result));
		$ret_val = $row['handle'];
		}
	return $ret_val;
	}

function like_ify($instr) {			// 3/6/2015	-- converts non-alphanumerics to underscores for use with mysql 'like'
	return  preg_replace("/[^a-zA-Z0-9]+/", "_", $instr);
	}

function get_facilityname($id) {
	$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=" . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_num_rows($result))==0)  {
		$ret_val = "NA";
		} else {
		$row = stripslashes_deep(mysql_fetch_array($result));
		$temp = explode("/", $row['name']);
		$ret_val = $temp[0];
		}
	return $ret_val;
	}

function get_facilityhandle($id) {
	$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=" . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_num_rows($result))==0)  {
		$ret_val = "NA";
		} else {
		$row = stripslashes_deep(mysql_fetch_array($result));
		$temp = explode("/", $row['handle']);
		$ret_val = $temp[0];
		}
	return $ret_val;
	}

function get_state_abb($name) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = '" . $name . "'";
	$result	= mysql_query($query);
	if(mysql_num_rows($result > 0)) {
		$row = stripslashes_deep(mysql_fetch_array($result));
		return $row['code'];
		} else {
		return $name;
		}
	}

function shut_down(){				// 5/25/11
	do_log($GLOBALS['LOG_INTRUSION'],0);
?>
<html>
 <body onload="setTimeout('parent.frames['upper'].do_logout();', 2000);" >
 <BR /><BR /><CENTER><H2>Intrusion attempt prevented!</H2></CENTER>
 </body>
</html>
<?php
	}		 // end function shut_down()

function win_shut_down() {				// for use in window vs. frame
	do_log($GLOBALS['LOG_INTRUSION'],0);
?>
<html>
 <body onload="setTimeout('window.close()', 2000);" >
 <BR /><BR /><CENTER><H2>Intrusion attempt prevented!</H2></CENTER>
 </body>
</html>
<?php
	}		 // end function win_shut_down()


/*			unused as of 3/22/11
function get_icon_str ($in_str) {		// return the rightmot three of the terminal element
	$my_array = explode("/", $in_str);
	return  substr($my_array[count($my_array) -1], -, strlen($my_array[count($my_array) -1]))
	}

function get_index_str ($in_str) {
	$my_array = explode("/", $in_str);								// if it's three elements return the center one
	$the_index = (count($my_array)==3)? 1: count($my_array)-1;		// otherwise the one
	return  substr($my_array[count($my_array) -1], -6, strlen($my_array[count($my_array) -1]))
	}

*/
	function format_sb_date_2($date_in){							// datetime: 2012-11-03 14:13:45 - 11/29/2012
		return substr($date_in, 8, 8);
		}

	function format_sb_date_3($date_in){
		return date("d H", intval($date_in));	//	Day and Hour
		}

	function format_date_2($date_in){								// datetime: 2012-11-03 14:13:45 - 11/29/2012
		$date_wk = (strlen(trim($date_in))== 19)? strtotime(trim($date_in)) : trim($date_in) ;			// force to integer
		if (get_variable('locale')==1)	{ return date("j/n/y H:i", intval($date_wk));}					// 08/27/10 - Revised to show UK format for locale = 1
		else 							{ return date(get_variable("date_format"), intval($date_wk)); }
		}

	if (!function_exists('format_dateonly')) {
		function format_dateonly($date_in){								// 12/3/13
			$date_wk = (strlen(trim($date_in))== 19)? strtotime(trim($date_in)) : trim($date_in) ;			// force to integer
			if (get_variable('locale')==0)	{ return date("n/j/y", intval($date_wk));}					//
			else 							{ return date("j/n/y", intval($date_wk));}
			}
		}

	function log_error($err_arg) {									// reports non-fatal error - 11/29/2012
		@session_start();											//
		if ( ! ( array_key_exists ( $err_arg, $_SESSION ) ) ) {		// limit to once per session to avoid log overload
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);			// logs argument error message
			$_SESSION[$err_arg] = TRUE;								//
			}
		}				// end function log_error()

	function get_maptype_str () {			// 3/27/2013
		switch(get_variable('maptype')) {
			case "1":			return "ROADMAP";			break;
			case "2":			return "SATELLITE";			break;
			case "3":			return "TERRAIN";			break;
			case "4":			return "HYBRID";			break;
			default:			return "HYBRID";
			}	// end switch
		}	// end function get maptype str

/**
 * Replace all linebreaks with one whitespace.
 *
 * @access public
 * @param string $string
 *   The text to be processed.
 * @return string
 *   The given text without any linebreaks.
 */
function replace_newline($string) {
	return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
	}

function get_contact_addr () {		// 6/1/2013 - returns user email addr if available
	$contact_addr =  is_email(get_variable('email_reply_to'))? get_variable('email_reply_to') :  FALSE;
	if (!($contact_addr)) {$contact_addr = 	is_email(get_variable('email_from'))? get_variable('email_from') :  FALSE; }
	if (!($contact_addr)) {$contact_addr =	"info@TicketsCAD.org"; }			// default to project home
	return trim($contact_addr);
	}

function list_files($ticket_id=0, $responder_id=0, $facility_id=0, $type=0, $portaluser=0) {	//	9/10/13, list stored files
	if($ticket_id != 0) {
		$where = " WHERE `ticket_id` = " . $ticket_id;
		} elseif($responder_id != 0) {
		$where = " WHERE `responder_id` = " . $responder_id;
		} elseif($facility_id != 0) {
		$where = " WHERE `facility_id` = " . $facility_id;
		} elseif($type != 0) {
		$where = " WHERE `type` = " . $type;
		} else {
		$where = "";
		}

	if($portaluser!=0) {
		$query = "SELECT *,
			`fx`.`id` AS fx_id,
			`f`.`id` AS file_id
			FROM `$GLOBALS[mysql_prefix]files_x` `fx`
			LEFT JOIN `$GLOBALS[mysql_prefix]files` `f`	ON (`f`.`id` = `fx`.`file_id`)
			WHERE `fx`.`user_id` = " . $portaluser . " ORDER BY `f`.`id` ASC";
		$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files`" . $where . " ORDER BY `id` ASC";
		$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	$bgcolor = "#EEEEEE";
	if (($result) && (mysql_num_rows($result) >=1)) {
		$print = "<TABLE style='width: 100%;'>";
		$print .= "<TR style='width: 100%; font-weight: bold; background-color: #707070;'><TD style='color: #FFFFFF;'>File Name</TD><TD style='color: #FFFFFF;'>Uploaded By</TD><TD style='color: #FFFFFF;'>Date</TD></TR>";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$print .= "<TR>";
			$filename = $row['filename'];
			$origfilename = $row['orig_filename'];
			$title = $row['title'];
			$print .= "<TD><A HREF='./ajax/download.php?filename=" . $filename . "&origname=" . $origfilename . "'>" . $row['title'] . "</A></TD>";
			$print .= "<TD>" . get_owner($row['_by']) . "</TD>";
			$print .= "<TD>" . format_date_2(strtotime($row['_on'])) . "</TD>";
			$print .= "</TR>";
			$bgcolor = ($bgcolor == "#EEEEEE") ? "#FEFEFE" : "#EEEEEE";
			}				// end while
			$print .= "</TABLE>";
		} else {
		$print = "<TABLE style='width: 100%;'>";
		$print .= "<TR class='spacer'><TD COLSPAN=99 class='spacer'>&nbsp;</TD></TR>";
		$print .="<TR style='width: 100%;'><TD style='width: 100%; text-align: center;'>No Files</TD></TR></TABLE>";
		}	//	end else

	return $print;
	}

function add_sidebar($regions = TRUE, $files = TRUE, $messages = TRUE, $controls = TRUE, $more=FALSE, $allowedit=FALSE, $ticket_id = 0, $responder_id = 0, $facility_id = 0, $mi_id = 0) {
	$theHeight = $_SESSION['scr_height'] / 2.5;
	$theHeight2 = $theHeight * .8;
	$theHeight3 = $theHeight * .58;
	$use_twitter = (get_variable('twitter_consumerkey') != "" && get_variable('twitter_consumersecret') != "" && get_variable('twitter_accesstoken') != "" && get_variable('twitter_accesstokensecret') != "") ? true : false;
	$print = "<DIV id='window_sidebar' style='position: fixed; top: 30px; right: 0px; width: auto; height: " . $theHeight . "px; font-size: 1.2em; z-index: 50000; background-color: #000000;'>";
	if((!(is_guest())) && $regions) {
		if(get_num_groups()) {
			$print .= "<DIV id='regions_control_outer' style='position: fixed; top: 30px; right: 0px; height: 400px; font-size: 1.2em; z-index: 9999;'>
				<SPAN id='s_rc' class='plain text' TITLE='Regions' style='position: fixed; top: 30px; right: 0px; width: 55px; display: inline-block; cursor: pointer; padding: 2px; background-color: #FEFEFE;'
				onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
				onClick=\"sidebar_buttonactions('s_rc');\">
				<IMG src='./buttons/regions.png' ALT='Regions'></SPAN>
				<SPAN id='h_rc' class='plain text' TITLE='Hide' style='z-index: 9999; width: 50px; display: none; cursor: pointer;'
				onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
				onClick=\"sidebar_buttonactions('h_rc');\">
				<IMG src='./images/close_large.png' ALT='Hide'></SPAN>
				<DIV class='even' ID = 'regions_control' style='padding: 3px; border: 1px outset #707070; height: 380px; width: 250px; display: none; overflow-y: auto; overflow-x: hidden; font-size: 0.8em; float: right;'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>
			</DIV>";
			}
		}
	if((!(is_guest())) && $files) {
		$print .= "<DIV id='file_list_outer' style='position: fixed; top: 30px; right: 0px; height: " . $theHeight . "px; font-size: 1.2em; z-index: 9999;'>
			<SPAN id='s_fl' class='plain text' TITLE='Files' style='position: fixed; top: 100px; right: 0px; width: 55px; display: inline-block; cursor: pointer; padding: 2px; background-color: #FEFEFE;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('s_fl'); load_files(". $ticket_id . ", " . $responder_id . ", " . $facility_id . ", " . $mi_id . ", " . $allowedit . ", 'name', 'ASC', 1)\">
			<IMG src='./buttons/files.png' ALT='Files'></SPAN>
			<SPAN id='h_fl' class='plain text' TITLE='Hide' style='z-index: 9999; width: 50px; display: none; cursor: pointer;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('h_fl');\">
			<IMG src='./images/close_large.png' ALT='Hide'></SPAN>
			<DIV class='even' ID = 'fileList' style='position: relative; height: " . $theHeight . "px; width: 500px; float: right; display: none; border: 1px outset #707070;'>
				<SPAN class='heading' style='width: 500px; text-align: center; display: inline-block;'>Files</SPAN></BR>
				<DIV style='margin: 10px;'>
					<DIV class=\"scrollableContainer2\" id='thefileslist' style='display: none;'>
						<DIV class=\"scrollingArea2\" style='height: " . $theHeight3 . "px;' id='file_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>
					</DIV>
				</DIV>
				<CENTER>
				<SPAN id='delSelected' class='plain text' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='delfiles(document.filesForm);' style='text-align: center; float: none;'>Delete Selected</SPAN>
				</CENTER>
			</DIV>
		</DIV>";
		}
	if((!(is_guest())) && $messages) {
		$print .= "<DIV id='msgs_list_outer' style='position: fixed; top: 30px; right: 0px; height: " . $theHeight . "px; font-size: 1.2em; z-index: 9999;'>
			<SPAN id='s_ms' class='plain text' TITLE='Messages' style='position: fixed; top: 170px; right: 0px; width: 55px; display: inline-block; cursor: pointer; padding: 2px; background-color: #FEFEFE;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('s_ms'); get_mainmessages(". $ticket_id . ", " . $responder_id . ", " . $facility_id . ", " . $mi_id . ", sortby, sort, 'inbox');\">
			<IMG src='./buttons/messages.png' ALT='Messages'></SPAN>
			<SPAN id='h_ms' class='plain text' TITLE='Hide' style='z-index: 9999; width: 50px; display: none; cursor: pointer;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('h_ms');\">
			<IMG src='./images/close_large.png' ALT='Hide'></SPAN>
			<DIV ID = 'message_list' class='even' style='position: relative; height: " . $theHeight2 . "px; width: 810px; float: right; display: none; border: 1px outset #707070;'>
				<SPAN class='heading' style='width: 810px; text-align: center; display: inline-block;'>Messages&nbsp;&nbsp;<SPAN id='foldername'>Inbox</SPAN></SPAN></BR>
				<DIV id='folderlist' class='odd' style='width: 90px; border: 1px outset #707070; display: inline-block; float: left; height: " . $theHeight3 . "px;'>
				<SPAN id='in_but' class='plain text' style='width: 70px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"inboxorsent(". $ticket_id . ", " . $responder_id . ", " . $facility_id . ", " . $mi_id . ", sortby, sort, 'inbox');\">INBOX</SPAN><BR />
				<SPAN id='sent_but' class='plain text' style='width: 70px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"inboxorsent(". $ticket_id . ", " . $responder_id . ", " . $facility_id . ", " . $mi_id . ", sortby, sort, 'sent');\">SENT ITEMS</SPAN><BR />
				</DIV>
				<DIV id='messages' style='width: 710px; border: 1px outset #707070; display: inline-block; float: right;'>
					<DIV class=\"scrollableContainer2\" id='messageslist' style='display: none; float: right;'>
						<DIV class=\"scrollingArea2\" style='height: " . $theHeight3 . "px;' id='the_msglist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>
					</DIV>
				</DIV>
			</DIV>
		</DIV>";
		}
	if($controls) {
		$print .= "<DIV id='controls_outer' style='position: fixed; top: 30px; right: 0px; height: " . $theHeight . "px; font-size: 1.2em; z-index: 9999;'>
			<SPAN id='s_ct' class='plain text' TITLE='Map Controls' style='position: fixed; top: 240px; right: 0px; width: 55px; display: inline-block; cursor: pointer; padding: 2px; background-color: #FEFEFE;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('s_ct');\">
			<IMG src='./buttons/controls.png' ALT='Map Controls'></SPAN>
			<SPAN id='h_ct' class='plain text' TITLE='Hide' style='z-index: 9999; width: 50px; display: none; cursor: pointer;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('h_ct');\">
			<IMG src='./images/close_large.png' ALT='Hide'></SPAN>
			<DIV class='even' id='controls' style='padding: 3px; border: 1px outset #707070; height: " . $theHeight . "px; width: 250px; display: none; font-size: 0.8em; float: right; overflow-y: scroll;'></DIV>
		</DIV>";
		}
	if((!(is_guest())) && $more && $use_twitter) {
		$print .= "<DIV id='more_outer' style='position: fixed; top: 30px; right: 0px; height: " . $theHeight . "px; font-size: 1.2em; z-index: 9999;'>
			<SPAN id='s_mo' class='plain text' TITLE='More Controls' style='position: fixed; top: 310px; right: 0px; width: 55px; display: inline-block; cursor: pointer; padding: 2px; background-color: #FEFEFE;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('s_mo');\">
			<IMG src='./buttons/more.png' ALT='More'></SPAN>
			<SPAN id='h_mo' class='plain text' TITLE='Hide' style='z-index: 9999; width: 50px; display: none; cursor: pointer;'
			onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'
			onClick=\"sidebar_buttonactions('h_mo');\">
			<IMG src='./images/close_large.png' ALT='Hide'></SPAN>
			<DIV class='even' id='more' style='padding: 3px; border: 1px outset #707070; height: " . $theHeight . "px; width: 500px; display: none; font-size: 0.8em; float: right; overflow-y: scroll;'>
			<SPAN class='heading' style='width: 100%; text-align: center; display: inline-block;'>More...</SPAN></BR>";
			if($use_twitter) {
				$print .= "<DIV id = 'tweetBox' style='width: 100%;'><BR /><SPAN class='header' style='width: 100%; font-size: .9em; text-align: center; display: block;'>Twitter</SPAN>
					<FORM NAME = 'tweetForm'>
					<SPAN style='width: 96%; font-size: .7em; display: block; background-color: #DEDEDE; margin: 2%;'>Input Message and (optional) User ID or Screen Name. If not using User ID or Screen Name, tweet will be a public status update. Using User ID or Screen Name it will be a Direct Message to that user.</SPAN><BR />
					<DIV style='height: 60px; width: 80%; float: left;'><DIV style='width: 30%; display: inline-block; font-size: .9em;'>User ID: </DIV><DIV style='width: 70%; display: inline-block; font-size: .9em;'><INPUT style='font-size: .9em;; 'TYPE = 'text' NAME = 'frm_userid' SIZE='24' MAXLENGTH='64' VALUE = '' style='display: inline; vertical-align: middle;'></DIV>
					<DIV style='width: 30%; display: inline-block; font-size: .9em;'>Screen Name: </DIV><DIV style='width: 70%; display: inline-block; font-size: .9em;'><INPUT style='font-size: .9em;' TYPE = 'text' NAME = 'frm_screenname' SIZE='24' MAXLENGTH='64' VALUE = '' style='display: inline; vertical-align: middle;'></DIV>
					<DIV style='width: 30%; display: inline-block; font-size: .9em;'>Message: </DIV><DIV style='width: 55%; display: inline-block; font-size: .9em;'><INPUT style='font-size: .9em;' TYPE = 'text' NAME = 'frm_message' SIZE='32' MAXLENGTH='140' VALUE = '' style='display: inline; vertical-align: middle;'></DIV></DIV>
					<DIV style='height: 60px; width: 20%; float: right;'><BR /><BR /><IMG id='sub_tweet' class='plain text' TITLE='Submit Tweet to Twitter account.' SRC='./buttons/tweetbutton.png' style='float: right; margin: 0px; padding: 0px; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= 'doTweet(document.tweetForm);'></DIV>
					<DIV style='width: 96%; height: 25px; display: inline-block;'><SPAN id='theFlag' style='margin-left: 20px; width: 100%; height: 25px; display: inline-block; float: left;'></SPAN></DIV></FORM>
					<DIV style='width: 100%; display: inline-block; text-align: center;'><SPAN id='tweets_but' class='plain text' style='float: none; text-align: center;' TITLE='Show Twitter Account Timeline - last 40 tweets' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick='twitter_window();'>Timeline</SPAN></DIV>
					<BR /></DIV>
					<hr>";
				}
		$print .= "</DIV></DIV>";
		}
	$print .= "</DIV>";
	return $print;
	}

function do_tweet($message) {
	require_once '../lib/twitter/twitter.class.php';

	$consumerKey = get_variable('twitter_consumerkey');
	$consumerSecret = get_variable('twitter_consumersecret');
	$accessToken = get_variable('twitter_accesstoken');
	$accessTokenSecret = get_variable('twitter_accesstokensecret');

	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

	try {
		$tweet = $twitter->send($message); // you can add $imagePath as second argument
		return 1;
		} catch (TwitterException $e) {
		$log_message = "Error Sending Tweet. Error Details - " . $e->getMessage();
		do_log($GLOBALS['LOG_ERROR'], 0, 0, $log_message);
		}
	}

function show_tweets() {
	require_once './lib/twitter/twitter.class.php';
	$consumerKey = get_variable('twitter_consumerkey');
	$consumerSecret = get_variable('twitter_consumersecret');
	$accessToken = get_variable('twitter_accesstoken');
	$accessTokenSecret = get_variable('twitter_accesstokensecret');
	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	$statuses = $twitter->load(Twitter::ME_AND_FRIENDS);
	$print = "";
	$print .= "<ul>";
	foreach($statuses as $status) {
		$print .= "<li><a href='http://twitter.com/" . $status->user->screen_name . "' target='_blank'><img src='" . htmlspecialchars($status->user->profile_image_url) . "'>";
		$print .= htmlspecialchars($status->user->name) . "</a>&nbsp;&nbsp;";
		$print .= Twitter::clickable($status);
		$print .= "<small> at " . date('j.n.Y H:i', strtotime($status->created_at)) . "</small>";
		$print .= "</li>";
		}
	$print .= "</ul>";
	return $print;
	}

function show_rec_direc($count = 20) {
	require_once './lib/twitter/twitter.class.php';
	$consumerKey = get_variable('twitter_consumerkey');
	$consumerSecret = get_variable('twitter_consumersecret');
	$accessToken = get_variable('twitter_accesstoken');
	$accessTokenSecret = get_variable('twitter_accesstokensecret');
	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	$messages = $twitter->rec_direct($count);
	$print = "";
	$print .= "<ul>";
	foreach($messages as $message) {
		$print .= "<li><a href='http://twitter.com/" . $message->recipient->screen_name . "' target='_blank'>";
		$print .= htmlspecialchars($message->recipient->name) . "</a>&nbsp;&nbsp;";
		$print .= Twitter::clickable($message);
		$print .= "<small> at " . date('j.n.Y H:i', strtotime($message->created_at)) . "</small>";
		$print .= "</li>";
		}
	$print .= "</ul>";
	return $print;
	}

function show_sent_direc($count = 20) {
	require_once './lib/twitter/twitter.class.php';
	$consumerKey = get_variable('twitter_consumerkey');
	$consumerSecret = get_variable('twitter_consumersecret');
	$accessToken = get_variable('twitter_accesstoken');
	$accessTokenSecret = get_variable('twitter_accesstokensecret');

	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	$messages = $twitter->sent_direct($count);
	$print = "";
	$print .= "<ul>";
	foreach($messages as $message) {
		$print .= "<li><a href='http://twitter.com/" . $message->user->screen_name . "' target='_blank'>";
		$print .= htmlspecialchars($message->user->name) . "</a>&nbsp;&nbsp;";
		$print .= Twitter::clickable($message);
		$print .= "<small> at " . date('j.n.Y H:i', strtotime($message->created_at)) . "</small>";
		$print .= "</li>";
		}
	$print .= "</ul>";
	return $print;
	}

function do_tweet_direct($message, $userid = NULL, $screenname = NULL) {
	require_once '../lib/twitter/twitter.class.php';

	$consumerKey = get_variable('twitter_consumerkey');
	$consumerSecret = get_variable('twitter_consumersecret');
	$accessToken = get_variable('twitter_accesstoken');
	$accessTokenSecret = get_variable('twitter_accesstokensecret');

	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	if(($userid == NULL || $userid == "") && ($screenname ==  NULL || $screenname == "")) {
		return false;
		} else {
		try {
			$tweet = $twitter->direct($message, $userid, $screenname);
			return 1;
			} catch (TwitterException $e) {
			$log_message = "Error Sending Tweet. Error Details - " . $e->getMessage();
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $log_message);
			return $e->getMessage();
			}
		}
	}

function send_tweet_direct($message, $userid = NULL, $screenname = NULL) {
	require_once './lib/twitter/twitter.class.php';

	$consumerKey = get_variable('twitter_consumerkey');
	$consumerSecret = get_variable('twitter_consumersecret');
	$accessToken = get_variable('twitter_accesstoken');
	$accessTokenSecret = get_variable('twitter_accesstokensecret');

	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	if(($userid == NULL || $userid == "") && ($screenname ==  NULL || $screenname == "")) {
		return false;
		} else {
		try {
			$tweet = $twitter->direct($message, $userid, $screenname);
			return 1;
			} catch (TwitterException $e) {
			$log_message = "Error Sending Tweet. Error Details - " . $e->getMessage();
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $log_message);
			return $e->getMessage();
			}
		}
	}

function is_dir_empty($dir) {
	if (!is_readable($dir)) return NULL;
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			return FALSE;
			}
		}
	return TRUE;
	}

function get_tile_bounds ($repository) {
	if(is_dir_empty($repository)) {return false;}
	if (!function_exists('tile2long')) {
		function tile2long( $x, $z) {
			$n = pow(2, $z);
			return $x / $n * 360.0 - 180.0;
			}
		}
	if (!function_exists('tile2lat')) {
		function tile2lat( $y, $z) {
			$n = pow(2, $z);
			return rad2deg(atan(sinh(pi() * (1 - 2 * $y / $n))));
			}
		}
	if (!function_exists('low_high_dir')) {
		function low_high_dir ($path, $low = TRUE) {
			$dh  = opendir($path);
			if ($low) {		// find min
				$return = 99999;					// starter - see below
				while (false !== ($filename = readdir($dh))  ) {
					if ( intval($filename) > 0 && intval ($filename) < intval ($return ) ) {
						$return = $filename ;		// retain extension if file
						}
					}		// end while ()
				} else {			//find max
				$return = 0;						// starter - see below
				while (false !== ($filename = readdir($dh))  ) {

					if ( intval($filename) > 0 && intval ($filename) > intval ($return ) ) {
						$return = $filename ;
						}
					}		// end while ()
				}		// end else
			return $return;
			}		// end function
		}
	//	1.  compute zoom
	$dir = $repository;
	$dh  = opendir($dir);
	$zoom = 99;						// starter - see below
	while (false !== ($filename = readdir($dh))  ) {
		if ( is_numeric ($filename ) && intval ($filename) < intval ($zoom ) ) { $zoom = intval ($filename) ; }
		}		// end while ()

	// 2. compute west and east longs

	$west = 99999;		// set extremes
	$east = 0;
	$path = "{$dir}/{$zoom}";
	$dh  = opendir($path);
	while (false !== ($filename = readdir($dh) ) ) {	// walk down the selected zoom directory
		if (is_numeric ($filename) ) {
			if ( intval($filename ) < intval ($west) ) {$west = $filename;}		// min
			if ( intval($filename ) > intval ($east) ) {$east = $filename;}		// max
			}		// end if (is_numeric () )
		}		// end while ()


	// 3. compute northwest tile - OK

	$path = "{$dir}/{$zoom}/{$west}";
	$northwest = low_high_dir ($path, $low = TRUE) ;

	// 4. compute southeast tile

	$path = "{$dir}/{$zoom}/{$east}";
	$southeast = low_high_dir ($path, $low = FALSE) ;

	$west_long = round (tile2long( $west, $zoom), 6) ;
	$north_lat = round (tile2lat( intval($northwest), $zoom), 6);
	$east_long = round (tile2long( $east + 1, $zoom), 6);					// note + 1
	$south_lat = round (tile2lat( intval($southeast) + 1, $zoom), 6);		// note + 1

	return array($west_long, $north_lat, $east_long, $south_lat );
	}		// end function

function  checkColExists($table, $col) {
	$query = "SHOW COLUMNS FROM `$GLOBALS[mysql_prefix]" . $table . "` LIKE '" . $col . "'";
	$result = mysql_query($query);
	if($result) {
		return true;
		} else {
		return false;
		}
	}

function get_standard_messages() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]std_msgs` ORDER BY `id` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$ret_arr[$row['id']]['id'] = $row['id'];
		$ret_arr[$row['id']]['name'] = $row['name'];
		$ret_arr[$row['id']]['message'] = $row['message'];
		}
	return $ret_arr;
	}

function get_standard_messages_sel() {
	$sms_provider = get_msg_variable('smsg_provider');
	$count = 0;
	switch($sms_provider) {
		case "0":
				$chosen = "";
				break;
		case "1":
				$chosen = "OR `smsresponder` = 1";
				break;
		case "2":
				$chosen = "OR `txtlocal` = 1";
				break;
		case "3":
				$chosen = "OR `mototrbo` = 1";
				break;
		case "4":
				$chosen = "OR `smsbroadcast` = 1";
				break;
		default:
				$chosen = "";
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]std_msgs` WHERE `email` = 1 {$chosen} ORDER BY `groupby`, `id` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$ret = "";
	$the_grp = "";
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		if ($the_grp != $row['groupby']) {
			if($the_grp != "") {$ret .= "</OPTGROUP>";}
			$the_grp = $row['groupby'];
			$ret .= "<OPTGROUP LABEL='{$row['groupby']}'>\n";
			}
		$ret .=  "\t<OPTION VALUE=' {$row['id']}'> {$row['name']} </OPTION>\n";
		}		// end while()
	$ret .= "\n</OPTGROUP>\n";
	return $ret;
	}

function multi_array_key_exists($key, $array) {
    if (array_key_exists($key, $array)) {
        return true;
		} else {
        foreach ($array as $nested) {
            if (is_array($nested) && multi_array_key_exists($key, $nested)) {
                return true;
				}
			}
		}
    return false;
	}

/* function valid_status($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		return true;
		} else {
		return false;
		}
	}

function valid_fac_status($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		return true;
		} else {
		return false;
		}
	} */

function get_roster($current=null) {	//	9/6/13
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]personnel` ORDER BY `person_identifier`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_ret = "<SELECT CLASS='text' NAME='frm_roster_id' onChange = 'get_roster_details(this.form, this.options[this.selectedIndex].value);' >";
	$the_ret .= "<OPTION VALUE='0' SELECTED>Select a Person</OPTION>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$sel = (($current) && ($current == $row['id'])) ? "SELECTED " : "";
		$the_ret .= "<OPTION VALUE=" .  $row['id'] . " " . $sel . ">" . $row['person_identifier'] . "</OPTION>";
		}
	$the_ret .= "</SELECT>";
	return $the_ret;
	}

function get_user_details($rosterID) {	//	9/6/13
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]personnel` WHERE `id` = '" . $rosterID . "' LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_affected_rows() != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret =  "Name: " . $row['forenames'] . " " . $row['surname'] . "<BR />";
		$the_ret .= "Street: " . $row['address'] . "<BR />";
		$the_ret .= "State: " . $row['state'] . "<BR />";
		$the_ret .= "Email: " . $row['email'] . "<BR />";
		$the_ret .= "Home phone: " . $row['homephone'] . "<BR />";
		$the_ret .= "Work Phone: " . $row['workphone'] . "<BR />";
		$the_ret .= "Cellphone: " . $row['cellphone'] . "<BR />";
		} else {
		$the_ret = "N/A";
		}
	return $the_ret;
	}

function get_teamname($id) {
	$result	= mysql_query("SELECT `name` FROM `$GLOBALS[mysql_prefix]team` WHERE `id` = " . $id . " LIMIT 1") or do_error("get team name(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "Error?" : $row['name'];
	}

function get_member_assigned($id, $responder) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `member_id` = " . $id . " AND `responder_id` = " . $responder . " LIMIT 1";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 1) {
		return 1;
		} else {
		return 0;
		}
	}

function get_member_assigned_other($id, $responder) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `member_id` = " . $id . " AND `responder_id` <> " . $responder . " LIMIT 1";
	$result = mysql_query($query);
	if($result) {
		return mysql_num_rows($result);
		} else {
		return 0;
		}
	}

function get_member_already_assigned($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `member_id` = " . $id;
	$result = mysql_query($query);
	if($result) {
		return mysql_num_rows($result);
		} else {
		return 0;
		}
	}

function get_member_assigned_addons($id, $responder) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `member_id` = " . $id . " AND `responder_id` = " . $responder . " LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row;
	}

function get_responder_members($id) {
	$output = "<DIV style='width: 100%; height: 150px; overflow-y: auto; overflow-z: hidden;'>";
	if($id == NULL) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member`";
		$result = mysql_query($query);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$theFlag = (get_member_already_assigned($row['id']) > 0) ? "background-color: red; color: white;'" : "";
			$output .= "<SPAN style='width: 95%; " . $theFlag . "'><SPAN style='width: 40%; display: inline-block;'><INPUT TYPE='checkbox' name='frm_memname[" . $row['id'] . "]' VALUE=1 />" . get_member_name($row['id'], TRUE) . "&nbsp;&nbsp;</SPAN>";
			$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Email Address from Member' name='frm_use_email[" . $row['id'] . "]' VALUE=1 />E</SPAN>";
			$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Cellphone Number from Member' name='frm_use_cell[" . $row['id'] . "]' VALUE=1 />C</SPAN>";
			$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Home Phobe Number from Member' name='frm_use_homephone[" . $row['id'] . "]' VALUE=1 />H</SPAN>";
			$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Work Phobe Number from Member' name='frm_use_workphone[" . $row['id'] . "]' VALUE=1 />W</SPAN>";
			$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use SMS Gateway ID from Member' name='frm_use_smsg[" . $row['id'] . "]' VALUE=1 />S</SPAN></SPAN><BR />";
			}
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` ORDER BY `field1` ASC, `field2` ASC";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$theFlag = (get_member_assigned_other($row['id'], $id) > 0) ? "background-color: red; color: white;'" : "";
			if(get_member_assigned($row['id'], $id) == 1) {
				$chkd_arr = get_member_assigned_addons($row['id'], $id);
				$chkd_email = ($chkd_arr['use_email'] == 1) ? "CHECKED" : "";
				$chkd_cell = ($chkd_arr['use_cellphone'] == 1) ? "CHECKED" : "";
				$chkd_homephone = ($chkd_arr['use_homephone'] == 1) ? "CHECKED" : "";
				$chkd_workphone = ($chkd_arr['use_workphone'] == 1) ? "CHECKED" : "";
				$chkd_smsgid = ($chkd_arr['use_smsg_id'] == 1) ? "CHECKED" : "";
				$output .= "<SPAN style='width: 95%; " . $theFlag . "'><SPAN style='width: 40%; display: inline-block;'><INPUT TYPE='checkbox' name='frm_memname[" . $row['id'] . "]' VALUE=1 CHECKED />" . get_member_name($row['id'], TRUE) . "&nbsp;&nbsp;</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Email Address from Member' name='frm_use_email[" . $row['id'] . "]' VALUE=1 " . $chkd_email . " />E</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Cellphone Number from Member' name='frm_use_cell[" . $row['id'] . "]' VALUE=1 " . $chkd_cell . " />C</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Home Phobe Number from Member' name='frm_use_homephone[" . $row['id'] . "]' VALUE=1 " . $chkd_homephone . " />H</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Work Phobe Number from Member' name='frm_use_workphone[" . $row['id'] . "]' VALUE=1 " . $chkd_workphone . " />W</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use SMS Gateway ID from Member' name='frm_use_smsg[" . $row['id'] . "]' VALUE=1 " . $chkd_smsgid . " />S</SPAN></SPAN><BR />";
				} else {
				$output .= "<SPAN style='width: 95%; " . $theFlag . "'><SPAN style='width: 40%; display: inline-block;'><INPUT TYPE='checkbox' name='frm_memname[" . $row['id'] . "]' VALUE=1 />" . get_member_name($row['id'], TRUE) . "&nbsp;&nbsp;</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Email Address from Member' name='frm_use_email[" . $row['id'] . "]' VALUE=1 />E</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Cellphone Number from Member' name='frm_use_cell[" . $row['id'] . "]' VALUE=1 />C</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Home Phobe Number from Member' name='frm_use_homephone[" . $row['id'] . "]' VALUE=1 />H</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use Work Phobe Number from Member' name='frm_use_workphone[" . $row['id'] . "]' VALUE=1 />W</SPAN>";
				$output .= "<SPAN style='width: 10%; display: inline-block;'><INPUT TYPE='checkbox' TITLE='Use SMS Gateway ID from Member' name='frm_use_smsg[" . $row['id'] . "]' VALUE=1 />S</SPAN></SPAN><BR />";
				}
			}
		}
	$output .= "</DIV>";
	return $output;
	}

function get_member_contact_details($id) {
	$ret_arr = array();
	$query = "SELECT `field1`, `field2`, `field24`, `field25`, `field26` FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	foreach($row as $key => $val) {
		$fieldid = substr($key, 5);
		$fieldname = get_fieldlabel($fieldid);
		$ret_arr[$fieldname] = $val;
		}
	return $ret_arr;
	}

function get_member_full_details($id) {
	$ret_arr = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	foreach($row as $key => $val) {
		if($key != "_on" && $key != "_by" && $key != "_from" && $key != "id") {
			$fieldid = substr($key, 5);
			$fieldname = get_fieldlabel($fieldid);
			if($fieldname == "Team") {
				$ret_arr[$fieldname] = get_teamname($val);
				} elseif($fieldname == "Member Status") {
				$ret_arr[$fieldname] = get_status_name($id);
				} elseif($fieldname == "Picture") {
				if($val != "") {
					$ret_arr[$fieldname] = "<IMG width='40px' SRC='" . $val . "' />";
					} else {
					$ret_arr[$fieldname] = "<IMG width='40px' SRC='./images/no_image.jpg' />";
					}
				} else {
				$ret_arr[$fieldname] = $val;
				}
			}
		}
	return $ret_arr;
	}

function get_mdb_email($id) {
	if(!get_mdb_variable('use_mdb_contact')) {return "";}
	$theReturn = false;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id . " AND `use_email` = 1 LIMIT 1";
	$result = mysql_query($query);
	if($result && mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$memberid = $row['member_id'];
		$field = get_mdb_variable('mdb_contact_via_field');
		$query2 = "SELECT " . $field . " FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
		$result2 = mysql_query($query2);
		if($result2 && mysql_num_rows($result2) > 0) {
			$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
			if($row2[$field] != "") {
				$theReturn = $row2[$field];
				}
			}
		}
	return $theReturn;
	}

function get_contact_via($id) {
	global $useMdb, $useMdbContact;
	if($useMdb == "1" && $useMdbContact == "1") {
		$ret_arr = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id . " AND `use_email` = 1";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0) {	//	member(s) assigned to responder - use member details
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$memberid = $row['member_id'];
				$field = get_mdb_variable('mdb_contact_via_field');
				$query2 = "SELECT " . $field . " FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
				$result2 = mysql_query($query2);
				if($result2 && mysql_num_rows($result2) > 0) {
					$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
					if($row2[$field] != "") {
						$ret_arr[] = $row2[$field];
						}
					}
				}
			} else {	//	No member assigned, use information from responder table
			$query = "SELECT `contact_via` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
			$result = mysql_query($query);
			if($result) {
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$ret_arr[] = $row['contact_via'];
				} else {
				$ret_arr = "";
				}
			}
		} else {
		$query = "SELECT `contact_via` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
		$result = mysql_query($query);
		if($result) {
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$temp = (strpos($row['contact_via'], "|")) ? explode(" | ", $row['contact_via']) : $row['contact_via'];
			if(is_array($temp)) {
				$ret_arr = $temp;
				} else {
				$ret_arr[] = $temp;
				}
			} else {
			$ret_arr = "";
			}
		}
	return $ret_arr;
	}

function get_smsgid($id) {
	global $useMdb, $useMdbContact;
	if($useMdb == "1" && $useMdbContact == "1") {
		$ret_arr = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id . " AND `use_smsg_id` = 1";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0) {	//	member(s) assigned to responder - use member details
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$memberid = $row['member_id'];
				$field = get_mdb_variable('mdb_smsg_id_field');
				$query2 = "SELECT " . $field . " FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
				$result2 = mysql_query($query2);
				if($result2 && mysql_num_rows($result2) > 0) {
					$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
					if($row2[$field] != "") {
						$ret_arr[] = $row2[$field];
						}
					}
				}
			} else {	//	No member assigned, use information from responder table
			$query = "SELECT `smsg_id` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
			$result = mysql_query($query);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$ret_arr[] = $row['smsg_id'];
			}
		} else {
		$query = "SELECT `smsg_id` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr[] = $row['smsg_id'];
		$temp = (strpos($row['smsg_id'], "|")) ? explode(" | ", $row['smsg_id']) : $row['smsg_id'];
		if(is_array($temp)) {
			$ret_arr = $temp;
			} else {
			$ret_arr[] = $temp;
			}
		}
	return $ret_arr;
	}

function get_member_count($id) {
	if(get_variable('use_mdb') == "0") {return 0;}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id;
	$result = mysql_query($query);
	if($result) {return mysql_num_rows($result);} else {return 0;}
	}

function get_mdb_names($id) {
	global $useMdb, $useMdbContact;
	if($useMdb == "1" && $useMdbContact == "1") {
		$ret_arr = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id;
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0) {	//	member(s) assigned to responder - use member details
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$memberid = $row['member_id'];
				$query2 = "SELECT `field1`, `field2` FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
				$result2 = mysql_query($query2);
				if($result2 && mysql_num_rows($result2) > 0) {
					$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
					if($row2['field1'] != "" && $row2['field2']) {
						$ret_arr[] = $row2['field2'] . " " . $row2['field1'];
						}
					}
				}
			} else {	//	No member assigned, use information from responder table
			$query = "SELECT `name` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
			$result = mysql_query($query);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$temp = explode('/', $row['name']);
			$ret_arr[] = $temp[0];
			}
		} else {
		$query = "SELECT `name` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$temp1 = explode(',', $row['name']);
		if(is_array($temp1)) {
			foreach($temp1 as $val) {
				$temp2 = explode("/", $val);
				$ret_arr[] = $temp2[0];
				}
			} else {
			$temp2 = explode("/", $temp1);
			$ret_arr[] = $temp2[0];
			}
		}
	return $ret_arr;
	}

function get_mdb_cell($id) {
	global $useMdb, $useMdbContact;
	if($useMdb == "1" && $useMdbContact == "1") {
		$ret_arr = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id . " AND `use_smsg_id` = 1";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0) {	//	member(s) assigned to responder - use member details
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$memberid = $row['member_id'];
				$field = get_mdb_variable('mdb_cellphone_field');
				$query2 = "SELECT " . $field . " FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
				$result2 = mysql_query($query2);
				if($result2 && mysql_num_rows($result2) > 0) {
					$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
					if($row2[$field] != "") {
						$ret_arr[] = $row2[$field];
						}
					}
				}
			} else {	//	No member assigned, use information from responder table
			$query = "SELECT `cellphone` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
			$result = mysql_query($query);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$temp = explode(',', $row['cellphone']);
			foreach($temp as $val) {
				$ret_arr[] = $val;
				}
			}
		} else {
		$query = "SELECT `cellphone` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$temp = explode(',', $row['cellphone']);
		foreach($temp as $val) {
			$ret_arr[] = $val;
			}
		}
	return $ret_arr;
	}

function get_mdb_phone($id) {
	if(!get_mdb_variable('use_mdb_contact')) {return "";}
	global $useMdb, $useMdbContact;
	if($useMdb == "1" && $useMdbContact == "1") {
		$ret_arr = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id . " AND `use_smsg_id` = 1";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0) {	//	member(s) assigned to responder - use member details
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$memberid = $row['member_id'];
				$field = get_mdb_variable('mdb_homephone_field');
				$query2 = "SELECT " . $field . " FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
				$result2 = mysql_query($query2);
				if($result2 && mysql_num_rows($result2) > 0) {
					$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
					if($row2[$field] != "") {
						$ret_arr[] = $row2[$field];
						}
					}
				}
			} else {	//	No member assigned, use information from responder table
			$query = "SELECT `phone` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
			$result = mysql_query($query);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$temp = explode(',', $row['phone']);
			foreach($temp as $val) {
				$ret_arr[] = $val;
				}
			}
		} else {
		$query = "SELECT `phone` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$temp = explode(',', $row['phone']);
		if(is_array($temp)) {
			$ret_arr = $temp;
			} else {
			$ret_arr[] = $temp;
			}
		}
	return $ret_arr;
	}

function get_members($id = NULL) {
	if(!get_mdb_variable('use_mdb_contact')) {return "";}
	global $useMdb, $useMdbContact;
	if($useMdb == "1" && $useMdbContact == "1" && $id != NULL) {
		$ret_arr = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $id . " AND `use_smsg_id` = 1";
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0) {	//	member(s) assigned to responder - use member details
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$memberid = $row['member_id'];
				$field = get_mdb_variable('mdb_smsg_id_field');
				$query2 = "SELECT " . $field . " FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberid;
				$result2 = mysql_query($query2);
				if($result2 && mysql_num_rows($result2) > 0) {
					$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
					if($row2[$field] != "") {
						$ret_arr[] = $row2[$field];
						}
					}
				}
			} else {	//	No member assigned, use information from responder table
			$query = "SELECT `smsg_id` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
			$result = mysql_query($query);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$ret_arr[] = $row['smsg_id'];
			}
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]members`";
		$result = mysql_query($query);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			print "<DIV style='float: left;'><INPUT TYPE='checkbox' CHECKED name='frm_member[" . $row['id'] . "]' VALUE='" . $row['id'] . "'></INPUT>" . get_member_name($row['id']) . "&nbsp;&nbsp;</DIV>";
			}
		}
	}

function get_roadcondition_types() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` ORDER BY `id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if($result && mysql_num_rows($result) > 0) {
		return mysql_num_rows($result);
		} else {
		return 0;
		}
	}

function get_tickets_status_select($selectname, $selected=NULL) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status`";
	$result = $result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$output = "<SELECT name='$selectname' style='width: 100%;'>";
		$output .= "<OPTION style='color:#FFFF00; background-color:#CC0000;' selected>Select Unit Status</OPTION>";
		while ($row = mysql_fetch_assoc($result)) {
			$name = $row['status_val'];
			$id = $row['id'];
			$sel = ($row['id'] == $selected) ? "selected" : "";
			$output .= "<OPTION VALUE='" . $id . "' {$sel}>" . $name . "</OPTION>";
			}
		$output .= "</SELECT>";
		} else {
		$output = "ERROR";
		}
	return $output;
	}

function get_mdb_status_select($selectname, $selected=NULL) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status`";
	$result = $result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$output = "<SELECT name='$selectname' style='width: 100%;'>";
		$output .= "<OPTION style='color:#FFFF00; background-color:#CC0000;' selected>Select Member Status</OPTION>";
		while ($row = mysql_fetch_assoc($result)) {
			$name = $row['status_val'];
			$id = $row['id'];
			$sel = ($row['id'] == $selected) ? "selected" : "";
			$output .= "<OPTION VALUE='" . $id . "' {$sel}>" . $name . "</OPTION>";
			}
		$output .= "</SELECT>";
		} else {
		$output = "ERROR";
		}
	return $output;
	}

if(checkColExists('std_msgs', 'name')) {$std_messages = get_standard_messages();}
?>