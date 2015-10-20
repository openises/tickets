<?php
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
4/11/10 poll_id dropped
6/18/10 timeout test for yg
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 Added reference to stylesheet.php for revisable day night colors plus other bug fixes and revisions to show/hide buttons.
4/1/11 Set font size of Incident and Assignment lists based on screen size to ensure readability and consistent tabular layout.
5/27/2013 added HAS message handling
6/3/2013 added auto-reload operation, removed predecessor, corrected references to window.opener
10/24/2014 Completely revised.
*/
error_reporting(E_ALL);
set_time_limit(0);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
require_once('./incs/full_scr.inc.php');
$api_key = get_variable('gmaps_api_key');

//dump($_GET);
if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
//	snap(__LINE__, basename(__FILE__));
	do_login(basename(__FILE__));
	$do_mu_init = (array_key_exists('log_in', $_GET))? "window.opener.parent.frames['upper'].mu_init();" : "";	// start multi-user function, 3/15/11	
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
	}

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

include("./forms/full_screen.php");
