<?php
error_reporting(E_ALL);
if(!(file_exists("./incs/mysql.inc.php"))) {
	if(file_exists('./install.php')) {
		header('Location: install.php');
	} else {
		print "Tickets is not configured (missing ./incs/mysql.inc.php). Restore install.php temporarily to run installation.";
	}
	exit();
	}

require_once('./incs/functions.inc.php');
require_once('./incs/version.inc.php');

$app_version = $tickets_current_version;
$installed_version = get_variable('_version');
$temp = explode(" ", $installed_version);
$disp_version = (isset($temp[0]) && $temp[0] != '') ? $temp[0] : 'unknown';

/*
10/1/08 added error reporting
1/11/09 added call frame, 'auto_route' setting
1/17/09 "ALTER TABLE `assigns` CHANGE `in-quarters` `on_scene` DATETIME NULL DEFAULT NULL"
2/1/09 version  no.
2/2/09 un_status schemae changes, version no.
2/24 comment re terrain setting
3/25/09 schema update
4/1/09 new settings added
7/7/09 function do_setting added, smtp_acct, email_from, 'multi' to responders
7/7/09 added protocol to in_types, utc_stamp
7/14/09 auto-size CB frame
7/29/09 added gtrack url setting, LocateA, Gtrack, Glat and Handle fields to responder table
8/2/09 added maptype setting which controls google map type default display
8/3/09 added locale setting which controls USNG, OSGB and UTM display plus date format
8/5/09 added user defined function key settings (3 keys).
8/19/09	added circle attrib's to in_types
11/1/09 Added setting for reverse geocoding on or off when setting location of incident.
11/11/09 Version no. to  11B
11/23/09 Version no. to  11C
1/3/10 added 'by' field to ticket table, for multi-user operation
1/8/10 added fields to table 'user' to support multi-user operation
1/23/10 session housekeeping
2/4/10 added unit status and fac_status value coloring
3/3/10 removed session destroy()
3/12/10 table `constituents` added
3/21/10 pie chart settings added
3/24/10 tables `in_types`, `un_status` revised
4/5/10 tag closure, version no.
4/7/10 unit_status_chg setting added, 'mu_init.php' renamed to 'get_latest.php'
4/11/10 added table 'pin_control' for asterisk integration
4/30/10 added three add'l phone fields to consx table
5/4/10 added responder_id (for use with level = 'unit') to user table
5/11/10 added miscellaneous to table consx
5/19/10 version update test added
6/20/10 schema changes per KJ email
6/25/10 user dob to text type
6/26/10 added set_severity to table in_types
6/27/10 corrected 911 field for prefix
7/6/10 address elements to responder, facilities schema, by AH
7/21/10 setting 'unit_status_chg' removed
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/5/10 internet setting added
8/8/10 install required if mysql.inc.php absent
8/13/10 gettext table inserted
8/17/10 gettext table renamed to 'captions'
8/21/10 - capts.inc.php added
8/25/10 utf8 collation set on table capts
8/27/10 setting added
8/30/10 captions data now handled as array
10/8/10 version number date change
10/31/10 facilities type description field size increased
11/5/10 ticket street field size increased
11/17/10 codes table added
11/23/10 'state' schema expanded
11/27/10 added codes, tweet tables, '_cloud' setting
11/30/10 hints table added, 'Patient' caption added
12/4/10 function do_caption() added
12/8/10 version number
12/9/10 assigns schema field addition
12/16/10 added group or dispatch setting
3/1/11 Added table of places for suggestions.
3/15/11 added css color tables
3/22/11 prepare for release - places table installs empty now.
3/30/11 Revisions to creaton of capts table and default value insertion.
3/30/11 corrections to _inc_num handling - AS
4/5/11 Added group table and group fields for future multi-region use.
4/19/11 revised log schema for larger info field.
4/22/11 field icon_str added to responder and facilities schema
6/6/11 Added table remote_devices for internal tickets tracker and added t_tracker field to responder table.
6/10/11 Dropped old groups tables and added new tables for group functionality
7/5/11 ogts field added to responder schema, settings
8/2/11 tables mmarkup and cats schema added
9/26/11 Changed Value field in settings table to varcahr (128) to fix issues with fiueld length for SMTP mail settings
4/17/12 Version number change
5/1/12 table captions field size increses from 38 to 64
5/11/12 Added extra indexes to Assigns and log table.
5/11/12 Added code for invocation of quick start choice on first login.
6/21/12 Version number change
10/23/12 New code for Messaging and Portal
04/02/13 version no. change only
6/6/2013 revisions to allocates schema re indexing, field size
6/14/2013 Added line to empty the $_SESSION array on first load of Index
5/7/13 Added new "status_updated" field to responder table
8/1/13 Added Mobile redirect for mobile devices
9/10/13 Added Warnings, mailgroups, personnel and various settings to support those features and mobile
10/31/13 Added fields to in_types.
2/24/14 Added Setting to restrict units to only see their own mobile screen
1/7/15 Changes for version 3.00
6/6/2023 Corrections for "Edit unit with no map". Corrections supplied by Andy Harvey and Arnie Shore
8/6/2023 Bugfixes for reset unit status. lot of deprecated language fixed for PHP 8.x, other misc bug fixes, fixed board.php errors
*/

//snap(basename(__FILE__) . " " . __LINE__  , count($_SESSION));

$cb_per_line = 22;				// 6/5/09
$cb_fixed_part = 60;
$cb_min = 96;
$cb_max = 300;



function tickets_version_to_compare($version_value) {
	$version_value = strtolower(trim((string)$version_value));
	$version_value = preg_replace('/[^0-9.]/', '', $version_value);
	if($version_value == '') { return '0.0.0'; }
	return $version_value;
}

$needs_install = ((!isset($installed_version)) || (trim((string)$installed_version) == ''));
if(!$needs_install) {
	$needs_install = version_compare(tickets_version_to_compare($installed_version), tickets_version_to_compare($app_version), '<');
}
if($needs_install) {
	if(file_exists('./install.php')) {
		header('Location: install.php');
	} else {
		print "Tickets requires installation/upgrade (DB version: " . htmlspecialchars((string)$disp_version, ENT_QUOTES, 'UTF-8') . ", app version: " . htmlspecialchars((string)$app_version, ENT_QUOTES, 'UTF-8') . "). Restore install.php temporarily to continue.";
	}
	exit();
}

//	cache buster and logout from statistics module.
$_SESSION = array();	//	6/14/13
$noforward_string = "";
// Mobile redirect
if((!isset($_POST) || (!array_key_exists('noautoforward', $_POST))) && ((!isset($_SESSION)) || ((array_key_exists('noautoforward', $_SESSION)) && ($_SESSION['noautoforward'] == FALSE)))) {	//	1/30/14
	if(get_variable('use_responder_mobile') == "1") {	//	8/1/13
		$text = $_SERVER['HTTP_USER_AGENT'];
		$var[0] = 'Mozilla/4.';
		$var[1] = 'Mozilla/3.0';
		$var[2] = 'AvantGo';
		$var[3] = 'ProxiNet';
		$var[4] = 'Danger hiptop 1.0';
		$var[5] = 'DoCoMo/';
		$var[6] = 'Google CHTML Proxy/';
		$var[7] = 'UP.Browser/';
		$var[8] = 'SEMC-Browser/';
		$var[9] = 'J-PHONE/';
		$var[10] = 'PDXGW/';
		$var[11] = 'ASTEL/';
		$var[12] = 'Mozilla/1.22';
		$var[13] = 'Handspring';
		$var[14] = 'Windows CE';
		$var[15] = 'PPC';
		$var[16] = 'Mozilla/2.0';
		$var[17] = 'Blazer/';
		$var[18] = 'Palm';
		$var[19] = 'WebPro/';
		$var[20] = 'EPOC32-WTL/';
		$var[21] = 'Tungsten';
		$var[22] = 'Netfront/';
		$var[23] = 'Mobile Content Viewer/';
		$var[24] = 'PDA';
		$var[25] = 'MMP/2.0';
		$var[26] = 'Embedix/';
		$var[27] = 'Qtopia/';
		$var[28] = 'Xiino/';
		$var[29] = 'BlackBerry';
		$var[30] = 'Gecko/20031007';
		$var[31] = 'MOT-';
		$var[32] = 'UP.Link/';
		$var[33] = 'Smartphone';
		$var[34] = 'portalmmm/';
		$var[35] = 'Nokia';
		$var[36] = 'Symbian';
		$var[37] = 'AppleWebKit/413';
		$var[38] = 'UPG1 UP/';
		$var[39] = 'RegKing';
		$var[40] = 'STNC-WTL/';
		$var[41] = 'J2ME';
		$var[42] = 'Opera Mini/';
		$var[43] = 'SEC-';
		$var[44] = 'ReqwirelessWeb/';
		$var[45] = 'AU-MIC/';
		$var[46] = 'Sharp';
		$var[47] = 'SIE-';
		$var[48] = 'SonyEricsson';
		$var[49] = 'Elaine/';
		$var[50] = 'SAMSUNG-';
		$var[51] = 'Panasonic';
		$var[52] = 'Siemens';
		$var[53] = 'Sony';
		$var[54] = 'Verizon';
		$var[55] = 'Cingular';
		$var[56] = 'Sprint';
		$var[57] = 'AT&T;';
		$var[58] = 'Nextel';
		$var[59] = 'Pocket PC';
		$var[60] = 'T-Mobile';
		$var[61] = 'Orange';
		$var[62] = 'Casio';
		$var[63] = 'HTC';
		$var[64] = 'Motorola';
		$var[65] = 'Samsung';
		$var[66] = 'NEC';
		$var[67] = 'Mobi';

		$result = count($var);

		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = 'rm/index.php';
		$url = "http://" . $host . $uri . "/" . $extra;

		for ($i=0;$i<$result;$i++) {
			$ausg = stristr($text, $var[$i]);
			if((strlen($ausg)>0) && (!stristr($text, 'MSIE'))) {
				echo '<meta http-equiv="refresh" content="', 0, ';URL=', $url, '">';
				exit;
				}
			}
		//	End of Mobile redirect
		}
	} else {
	$noforward_string = "&noaf=1";	//	1/30/14
	}

if(isset($_POST['logout'])) {
	$buster = strval(rand()) . "&logout=1";
	} else {
	$buster = strval(rand());
	}
if (get_variable('call_board') == 2) {
?>
	<FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, 0 ,*" BORDER="<?php print get_variable('frameborder');?>" BORDERCOLOR="#ff0000">
	<FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
	<FRAME SRC='board.php?stuff=<?php print $buster;?>' ID = 'what' NAME='calls' SCROLLING='AUTO' />	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?><?php print $noforward_string;?>" NAME="main" />	<!-- 1/30/14 -->
<?php
	} else  {
?>
	<FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, *" BORDER="<?php print get_variable('frameborder');?>">
	<FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?><?php print $noforward_string;?>" NAME="main" />	<!-- 1/30/14 -->
<?php
	}
?>
	<NOFRAMES>
	<BODY>
		Tickets requires a frames-capable browser.
	</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>
<?php

