<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
session_start();						// 
session_write_close();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

/*
10/14/08 moved js includes here fm function_major
1/11/09  handle callboard frame
1/19/09 dollar function added
1/21/09 added show butts - re button menu
1/24/09 auto-refresh iff situation display and setting value
1/28/09 poll time added to top frame
3/16/09 added updates and auto-refresh if any mobile units
3/18/09 'aprs_poll' to 'auto_poll'
4/10/09 frames check for call board
7/16/09	protocol handling added
11/11/09 'top' and 'bottom' anchors added - 
12/26/09 handle 'log_in' $_GET variable
1/3/10 wz tooltips added for usage in FMP
1/8/10 added do_init logic - called ONLY from index.php
1/23/10 refresh meta removed
3/27/10 $zoom_tight added
4/10/10 hide 'board' button if setting = 0
4/11/10 do_blink added, poll_id dropped
6/24/10 compression added
7/18/10 redundant $() removed
7/20/10 cb frame resize/refresh added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 links incl relocated
8/25/10 hide top buttons if ..., $_POST logout test
8/29/10 dispatch status style added
11/29/10 added to_listtype form when adding scheduled list type select dropdown
3/15/11	Added reference to stylesheet.php for revisable day night colors
3/19/11 added top term button value
4/22/11 gunload correction
5/16/11 Added code to support Ticker Module
6/10/11	added groups and boundaries
6/28/11 auto refresh added
7/3/11 lazy logout button moved out of try/catch
3/5/12 handle empty GMaps API key
3/23/12 auto-refresh changes
4/12/12 Revised regions control buttons
6/1/12 Revised loading of main page modules so tha they only load on the main screen, not the Ticket Detail screen.
6/14/12 Moved position of ck_frames() in onLoad string.
10/23/12 Added code for Messaging
3/26/2013 revised per RC Charlie
5/26/2013 made auto_refresh conditional on setting value
9/10/13 Changed logic to show full screen button, now shows if internet is available but maps are switched off by user choice.
10/23/13 Revisions for user selectable maps
10/31/13 Revisions for user selectable maps
1/3/14 Added Live moving Responder markers, added Road Condition Alert Markers
*/
if (isset($_GET['logout'])) {
	do_logout();
	exit();
	}
else {		// 
	if(isset($_GET['noaf'])) {	//	1/30/14
		do_login(basename(__FILE__), FALSE, FALSE, TRUE);
		} else {
		do_login(basename(__FILE__));
		}
	$do_mu_init = (array_key_exists('log_in', $_GET))? "parent.frames['upper'].mu_init();" : "";	// start multi-user function
	}

if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	print "SESSION<BR/>\n";	//	3/15/11
	if (!empty($_SESSION)) {
		dump ($_SESSION);
		}
	}
	
$protocol = ($https) ? "https" : "http";

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'mobile.php';
	header("Location: " . $protocol . "://$host$uri/$extra");
	exit();
	}
	
if($_SESSION['level'] == $GLOBALS['LEVEL_SERVICE_USER']) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'portal.php';
	header("Location: " . $protocol . "://$host$uri/$extra");
	exit;
	}

$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;
$alt_sit = (intval(get_variable('alternate_sit')) == 1) ? true : false;
	if ($get_print) {
		if((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) {
			require_once('./forms/ticket_view_screen.php');
			print "<BR /><P ALIGN='left'>";
			} else {
			require_once('./forms/ticket_view_screen_NM.php');
			print "<BR /><P ALIGN='left'>";
			}
		} else if ($get_id) {
		if((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) {
			require_once('./forms/ticket_view_screen.php');
			} else {
			add_header($get_id, FALSE, TRUE);
			require_once('./forms/ticket_view_screen_NM.php');
			print "<BR /><P ALIGN='left'>";
			}

		} else if ($get_sort_by_field && $get_sort_value) {
		if((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) {
			require_once('./forms/sit_screen.php');
			} else {
			require_once('./forms/sit_screen_NM.php');
			}
		} else {
		if((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) {
			if($alt_sit) {
				require_once('full_sit_scr.php');
				} else {
				require_once('./forms/sit_screen.php');
				}
			} else {
			require_once('./forms/sit_screen_NM.php');
			}

		}
exit();
?>
