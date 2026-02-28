<?php
/*
10/8/07 added domain detection for GMaps API key association
1/8/08 added settings email_reply_to' and call_board;
3/20/08 added settings map height and width;
6/7/08  added kml files setting
6/9/08  revised default 'admin' account level to 'super'
6/12/08 revised version number only
6/27/08 revised version number only
6/28/08 revised version number only
7/16/08 revised default military time
9/13/08 added error_reporting, lat/lng setting entry
9/13/08 added table `unit_types`
9/13/08 added white pages key
9/13/08 GSearch API key
9/15/08 added tables 'login'
9/16/08 corr's to undefined's
9/17/08 dropped field 'hash', rearranged field 'user' in table 'user'
9/17/08 added table 'photos'
9/18/08 added table `cities`
9/22/08 version # only
10/08/08 version # only
10/11/08 major schema update
10/17/08 added '__sleep' setting
10/19/08 added pager nos. to responder
10/22/08 expanded table notify schema
11/6/08 table user default corrections, sql_mode
1/17/09 changed `in-quarters` to `on_scene`, insert `auto_route` settings
1/17/09 version no. to '2.9 B beta'
1/18/08 added 'in service' unit status
1/25/09 team tables renamed
1/27/09 added default area code setting, unit_types schema, responder schema updates
2/3/09 version no.
2/8/09 table tracks_hh added
2/11/09 session table expanded
2/14/09 session flags to varchar
2/21/09 check file write-able
2/24/09 added 'terrain' setting
3/17/09 chgd aprs_poll to auto_poll
3/22/09 removed redundant def_area_code
4/10/09 responder schema update
1/23/10 - removed table session
8/5/10 version number base - to permit index.php to update schema, internet setting added
8/8/10    accomodate absent mysql.inc.php - as install trigger
10/29/10  'PASSWORD' => 'MD5' to accommodate old MySQL versions
12/18/10   write permissions test corrected
1/10/11 Added default setting for Group or dispatch
5/11/12 Added code for quick start.
1/9/2013 API key is no longer mandatory
4/2/2013 removed API key value.
*/

error_reporting(E_ALL);				// 2/3/09
if( !extension_loaded('mysql') ){
	require_once('./incs/mysql2i.class.php');
	}
require_once('./incs/version.inc.php');

$base_install_version = "2.20 A base beta";
$current_version = $tickets_current_version;
$version = $current_version;

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);
@ob_implicit_flush(true);

function dump($variable) {
	echo "\n<PRE>";					// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

function h($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}

$api_key = "AIzaSyBN2v_821i9ivnaWoNXb0MIV3Dz8RQ3xqc";			// 1/9/2013



if ((isset($_GET['installer_upgrade'])) && ($_GET['installer_upgrade'] == '1')) {
?>
<?php
error_reporting(E_ALL);
if(!(file_exists("./incs/mysql.inc.php"))) {
	print "This appears to be a new Tickets installation; file 'mysql.inc.inc' absent. Please run <a href=\"install.php\">install.php</a> with valid database configuration information.";
	exit();
	}

require_once('./incs/functions.inc.php');

$version = get_variable('_target_version');
if($version == '') { $version = get_variable('_version'); }
$temp = explode(" ", get_variable('_version'));
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

/*
if ($istest) {											// 12/13/09
	$query = "CREATE TABLE IF NOT EXISTS `{$snap_table}` (
		`id` int(4) NOT NULL AUTO_INCREMENT,
		`source` text,
		`stuff` text,
		`when` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

SET @@global.sql_mode= '';
sql-mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
*/
function count_responders() {	//	5/11/12 For quick start.
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query);
	$count_responders = mysql_num_rows($result);
	return $count_responders;
	}

function do_mi_fix() {
	$query = "DROP TABLE `$GLOBALS[mysql_prefix]mi_types`;";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	$query = "DROP TABLE `$GLOBALS[mysql_prefix]major_incidents`;";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	$query = "DROP TABLE `$GLOBALS[mysql_prefix]mi_x`;";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]major_incidents` (
		`id` int(11) NOT NULL auto_increment,
		`name` varchar(64) NOT NULL,
		`description` longtext NOT NULL,
		`type` int(4) NOT NULL,
		`gold` int(4) NOT NULL,
		`silver` int(4) NOT NULL,
		`bronze` int(4) NOT NULL,
		`boundary` int(4) NOT NULL,
		`inc_startime` datetime NOT NULL,
		`inc_endtime` datetime NOT NULL,
		`incident_notes` longtext,
		`_by` int(11) NOT NULL,
		`_on` datetime NOT NULL,
		`_from` varchar(16) NOT NULL,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mi_types` (
		`id` int(4) NOT NULL auto_increment,
		`name` varchar(64) NOT NULL,
		`bg_color` varchar(12) NOT NULL DEFAULT 'transparent',
		`color` varchar(12) NOT NULL DEFAULT '#000000',
		`_by` int(11) NOT NULL,
		`_on` datetime NOT NULL,
		`_from` varchar(16) NOT NULL,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mi_x` (
		`id` int(6) NOT NULL auto_increment,
		`mi_id` int(6) NOT NULL,
		`ticket_id` int(6) NOT NULL,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]mi_types` (
		`id`, `name`, `bg_color`, `color`, `_by`, `_on`, `_from`) VALUES (
		1, 'Environmental', 'transparent', '#000000', 1, '2015-02-27 11:50:34', '::1');";
	$result = mysql_query($query);
	if($result) { $thecounter++;}
	return true;
	}

function check_ai($name) {
	$tablename = "$GLOBALS[mysql_prefix]" . "$name";
	$query = "SHOW COLUMNS FROM $tablename";
	$result = mysql_query($query);
	if($result) {
		$num_rows = mysql_num_rows($result);
		}
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			if($row['Field'] == 'id') {
				if(($row['Extra'] == 'auto_increment') && ($row['Key'] == 'PRI')) {
					} else {
					do_mi_fix();
					}
				}
			}
		}
	}

function checkBase64Encoded($encodedString) {
	$length = strlen($encodedString);
	// Check every character.
	for ($i = 0; $i < $length; ++$i) {
		$c = $encodedString[$i];
		if (($c < '0' || $c > '9') && ($c < 'a' || $c > 'z') && ($c < 'A' || $c > 'Z') && ($c != '+') && ($c != '/') && ($c != '=')) {
			// Bad character found.
			return false;
			}
		}
	// Only good characters found.
	return true;
	}

function table_exists($name) {
	$tablename = "$GLOBALS[mysql_prefix]" . "$name";
	$query 	= "SELECT COUNT(*) FROM $tablename";
    $result = mysql_query($query);
	if($result) {
		$num_rows = mysql_num_rows($result);
		if($num_rows > 0) {
			return true;
			} else {
			return false;
			}
		} else {
		return false;
		}
	}

function remove_setting ($which) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==1) {
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]settings` WHERE `name` = '$which'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if($result) { print "Removed redundant setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function remove_setting ()

function check_field_exists($table, $field) {
	$the_table = $GLOBALS['mysql_prefix'] . $table;
	$query = "Show columns from " . $the_table . " like '" . $field . "'";

	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		return true;
		} else {
		return false;
		}
	}

function table_type($table) {
	$query = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '$GLOBALS[mysql_prefix]" . $table . "'";
	$result = mysql_query($query);
	if(mysql_num_rows($result)> 0) {		// 12/2/2021
		$row = stripslashes_deep(mysql_fetch_array($result));
		$ret = $row['ENGINE'];
		} else {
		$ret = "";
		}
	return $ret;
	}

function do_insert_day_colors($name,$value) {			//	3/15/11
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]css_day` (name,value) VALUES('$name','$value')";
	$result = mysql_query($query) or die("DO_INSERT_DAY_COLORS($name,$value) failed, execution halted");
	}

function do_insert_night_colors($name,$value) {			//	3/15/11
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]css_night` (name,value) VALUES('$name','$value')";
	$result = mysql_query($query) or die("DO_INSERT_NIGHT_COLORS($name,$value) failed, execution halted");
	}

if (!table_exists("css_day")) {			//	3/15/11
	$query = "CREATE TABLE `$GLOBALS[mysql_prefix]css_day` (
			`id` bigint(8) NOT NULL auto_increment,
			`name` tinytext,
			`value` tinytext,
				PRIMARY KEY  (`id`),
				UNIQUE KEY `ID` (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	do_insert_day_colors('page_background', 'EFEFEF');			//	3/15/11
	do_insert_day_colors('normal_text', '000000');			//	3/15/11
	do_insert_day_colors('header_text', '000000');			//	3/15/11
	do_insert_day_colors('header_background', 'EFEFEF');			//	3/15/11
	do_insert_day_colors('titlebar_text', '000000');			//	3/15/11
	do_insert_day_colors('links', '000099');			//	3/15/11
	do_insert_day_colors('other_text', '000000');			//	3/15/11
	do_insert_day_colors('legend', '000000');			//	3/15/11
	do_insert_day_colors('row_light', 'DEE3E7');			//	3/15/11
	do_insert_day_colors('row_light_text', '000000');			//	3/15/11
	do_insert_day_colors('row_dark', 'EFEFEF');			//	3/15/11
	do_insert_day_colors('row_dark_text', '000000');			//	3/15/11
	do_insert_day_colors('row_plain', 'FFFFFF');			//	3/15/11
	do_insert_day_colors('row_plain_text', '000000');			//	3/15/11
	do_insert_day_colors('row_heading_background', '707070');			//	3/15/11
	do_insert_day_colors('row_heading_text', 'FFFFFF');			//	3/15/11
	do_insert_day_colors('row_spacer', 'FFFFFF');			//	3/15/11
	do_insert_day_colors('form_input_background', 'FFFFFF');			//	3/15/11
	do_insert_day_colors('form_input_text', '000000');			//	3/15/11
	do_insert_day_colors('select_menu_background', 'FFFFFF');			//	3/15/11
	do_insert_day_colors('select_menu_text', '000000');			//	3/15/11
	do_insert_day_colors('label_text', '000000');			//	3/15/11
	} // end if !table_exists css_day


if (!table_exists("css_night")) {			//	3/15/11
	$query = "CREATE TABLE `$GLOBALS[mysql_prefix]css_night` (
			`id` bigint(8) NOT NULL auto_increment,
			`name` tinytext,
			`value` tinytext,
				PRIMARY KEY  (`id`),
				UNIQUE KEY `ID` (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	do_insert_night_colors('page_background', '121212');			//	3/15/11
	do_insert_night_colors('normal_text', 'DAEDE2');			//	3/15/11
	do_insert_night_colors('header_text', 'DAEDE2');			//	3/15/11
	do_insert_night_colors('header_background', '2B2B2B');			//	3/15/11
	do_insert_night_colors('titlebar_text', 'FFFFFF');			//	3/15/11
	do_insert_night_colors('links', '3F23F7');			//	3/15/11
	do_insert_night_colors('other_text', 'FFFFFF');			//	3/15/11
	do_insert_night_colors('legend', 'ECFC05');			//	3/15/11
	do_insert_night_colors('row_light', 'BEC3C7');			//	3/15/11
	do_insert_night_colors('row_light_text', '04043D');			//	3/15/11
	do_insert_night_colors('row_dark', '9E9E9E');			//	3/15/11
	do_insert_night_colors('row_dark_text', '000000');			//	3/15/11
	do_insert_night_colors('row_plain', 'A3A3A3');			//	3/15/11
	do_insert_night_colors('row_plain_text', '000000');			//	3/15/11
	do_insert_night_colors('row_heading_background', '262626');			//	3/15/11
	do_insert_night_colors('row_heading_text', 'F0F0F0');			//	3/15/11
	do_insert_night_colors('row_spacer', 'F2E3F2');			//	3/15/11
	do_insert_night_colors('form_input_background', 'B5B5B5');			//	3/15/11
	do_insert_night_colors('form_input_text', '212422');			//	3/15/11
	do_insert_night_colors('select_menu_background', 'B5B5B5');			//	3/15/11
	do_insert_night_colors('select_menu_text', '151716');			//	3/15/11
	do_insert_night_colors('label_text', '000000');			//	3/15/11
	} // end if !table_exists css_night

function cleanup_captions() {	//	detects and deletes dupe captions, leaves customised ones in place.
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions`;";	// 11/30/10
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$caption = quote_smart($row['capt']);
			$repl = quote_smart($row['repl']);
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . ";";
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if($caption != $repl) {
					$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (" . $caption . ", " . $repl . ");";
					$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					} else {
					$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (" . $caption . ", " . $caption . ");";
					$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					}
				}
			}
		}
	}

function cleanup_states_translator() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`;";	// 11/30/10
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$name = quote_smart($row['name']);
			$code = quote_smart($row['code']);
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = " . $name . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = " . $name . ";";
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]states_translator` ( `name`, `code`) VALUES (" . $name . ", " . $code . ");";
				$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}
			}
		}
	}

function do_caption ($temp, $repl="") { 				// adds a 'captions' table entry - 12/4/10, 01/14/15 added de-dupe function.
	if($repl == "") { $repl = $temp; }
	$caption = quote_smart($temp);
	$repl = quote_smart($repl);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . ";";	// 11/30/10
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if ($num_rows==0) {	//	if no current entries for that caption insert a new one
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (" . $caption . ", " . $repl . ");";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	if($num_rows > 1) {	//	if multiple entries exist (an error) delete all where the caption = the replacement value and only leave in custom one
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . " AND `repl` = " . $caption . ";";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . ";";	// 11/30/10
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if(mysql_num_rows($result)==0) {	//	if above operation deletes everything insert a new entry where caption = replacement value
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (" . $caption . ", " . $repl . ");";
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}
		}
	return;
	}

function do_setting ($which, $what) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)==0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` ( `id` , `name` , `value` ) VALUES (NULL , '$which', '$what');";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if($result) { print "Added Setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function do_setting ()

function update_setting ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '" . $which . "' AND `value` <> '" . $what . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '" . $which . "'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if($result) { print "Updated Setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function update_setting ()

function do_msg_setting ($which, $what) {				// 5/25/13
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]msg_settings` WHERE `name`= '" . $which . "' LIMIT 1";		// 5/25/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)==0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]msg_settings` ( `id` , `name` , `value` ) VALUES (NULL , '" . $which . "', '" . $what . "');";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if($result) { print "Added Message Setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function do_msg_setting ()

function update_msg_settings ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]msg_settings` WHERE `name`= '" . $which . "' AND `value` <> '" . $what . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]msg_settings` SET `value`= '$what' WHERE `name` = '" . $which . "'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if($result) { print "Updated Message Setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function update_msg_settings ()

function do_mdb_setting ($which, $what) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mdb_settings` WHERE `name`= '" . $which . "' LIMIT 1";
	$result = mysql_query($query);
	if (mysql_num_rows($result)==0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]mdb_settings` ( `id` , `name` , `value` ) VALUES (NULL , '" . $which . "', '" . $what . "');";
		$result = mysql_query($query);
		if($result) { print "Added MDB Setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function do_mdb_setting ()

function update_mdb_settings ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mdb_settings` WHERE `name`= '" . $which . "' AND `value` <> '" . $what . "' LIMIT 1";
	$result = mysql_query($query);
	if (mysql_num_rows($result)!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]mdb_settings` SET `value`= '$what' WHERE `name` = '" . $which . "'";
		$result = mysql_query($query);
		if($result) { print "Updated MDB Setting " . $which . "<BR />"; }
		}
	unset ($result);
	return TRUE;
	}				// end function update_mdb_settings ()

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}

function do_index($table, $theindex) {
	$query = "SHOW INDEX FROM `$GLOBALS[mysql_prefix]" . $table . "` WHERE KEY_NAME = '" . $theindex . "'";
	$result = mysql_query($query);
	if(!$result) {
		$query2 = "ALTER TABLE `$GLOBALS[mysql_prefix]" . $table . "` ADD INDEX ( `" . $theindex . "` )";
		$result2 = mysql_query($query2);
		if($result2) { print "Added Index " . $theindex . " to table " . $table . "<BR />"; }
		return true;
		} else {
		return false;
		}
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<HEAD>
	<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<TITLE>Tickets <?php print $disp_version;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
</HEAD>
<?php

$run_installer_upgrade = (isset($_GET['installer_upgrade']) && ($_GET['installer_upgrade'] == '1'));
if($run_installer_upgrade) {
$old_version = get_variable('_version');

if (!($version == $old_version)) {		// current? - 6/6/2013  ==================================================
										// not yet
	print "<DIV style='position: absolute; top: 5%; left: 0px; width: 100%; display: block;'>";
	print "<SPAN style='position: relative; top: 5%; left: 0px; font-size: 2em; font-weight: bold; width: 100%; text-align: center; display: block;'>Updating Tickets to Version " . $version . "</SPAN><BR /><BR />";
	print "<DIV id='update_status' style='position: relative; top: 15%; left: 20%; width: 60%; max-height: 500px; overflow-y: scroll; display: block; border: 2px outset #707070;'>";
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]hints` CHANGE `hint` `hint` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
	$result = mysql_query($query);

	do_setting ('smtp_acct','');			// 7/7/09
	do_setting ('email_from','');			// 7/7/09
	do_setting ('gtrack_url','');			// 7/7/09
	do_setting ('maptype','1');				// 8/2/09
	do_setting ('locale','0');				// 8/3/09
	do_setting ('func_key1','http://openises.sourceforge.net/,Open ISES');				// 8/5/09
	do_setting ('func_key2','');				// 8/5/09
	do_setting ('func_key3','');				// 8/5/09
	do_setting ('reverse_geo','0');				// 11/1/09
	do_setting ('logo','t.png');				// 11/1/09
	do_setting ('pie_charts','300/450/300');	// 3/21/10
	do_setting ('group_or_dispatch','0');			// 12/16/10
	do_setting ('title_string','');			// 12/16/10
	do_setting ('regions_control','0');			// 12/16/10
	do_setting('regions_control','0');				//	10/23/12
	do_setting('map_in_portal','1');				//	10/23/12
	do_setting('use_messaging','0');				//	10/23/12

	do_index("assigns", "ticket_id");
	do_index("assigns", "responder_id");
	do_index("assigns", "clear");
	do_index("log", "code");

	if (!table_exists("facilities")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]facilities` (
			`id` bigint(8) NOT NULL auto_increment,
			`name` text,
			`direcs` tinyint(2) NOT NULL default '1' COMMENT '0=>no directions, 1=> yes',
			`description` text NOT NULL,
			`capab` varchar(255) default NULL COMMENT 'Capability',
			`status_id` int(4) NOT NULL default '0',
			`other` varchar(96) default NULL,
			`handle` varchar(24) default NULL,
			`contact_name` varchar(64) default NULL,
			`contact_email` varchar(64) default NULL,
			`contact_phone` varchar(15) default NULL,
			`security_contact` varchar(64) default NULL,
			`security_email` varchar(64) default NULL,
			`security_phone` varchar(15) default NULL,
			`opening_hours` mediumtext,
			`access_rules` mediumtext,
			`security_reqs` mediumtext,
			`pager_p` varchar(64) default NULL,
			`pager_s` varchar(64) default NULL,
			`send_no` varchar(64) default NULL,
			`lat` double default NULL,
			`lng` double default NULL,
			`type` tinyint(1) default NULL,
			`updated` datetime default NULL,
			`user_id` int(4) default NULL,
			`callsign` varchar(24) default NULL,
			`_by` int(7) NOT NULL,
			`_from` varchar(16) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`),
			UNIQUE KEY `ID` (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Facilities Table<BR />"; }
		}

	if (!table_exists("fac_types")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fac_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(16) NOT NULL,
			`description` varchar(48) NOT NULL,
			`icon` int(3) NOT NULL default '0',
			`_by` int(7) NOT NULL,
			`_from` varchar(16) NOT NULL COMMENT 'ip',
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COMMENT='Allows for variable facility types' AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Facility Types Table<BR />"; }
		}

	if (!table_exists("fac_status")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fac_status` (
			`id` bigint(4) NOT NULL auto_increment,
			`status_val` varchar(20) NOT NULL,
			`description` varchar(60) NOT NULL,
			`group` varchar(20) default NULL,
			`sort` int(11) NOT NULL default '0',
			`_by` int(7) NOT NULL,
			`_from` varchar(16) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`),
			UNIQUE KEY `ID` (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Facility Status Table<BR />"; }
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `facility` INT( 4 ) NULL DEFAULT NULL AFTER `phone`;";
	$result = mysql_query($query);
	if($result) { print "Added Facility field to Ticket table<BR />"; }

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `rec_facility` INT( 4 ) NULL DEFAULT NULL AFTER `facility`;";
	$result = mysql_query($query);
	if($result) { print "Added Receiving Facility field to Ticket table<BR />"; }

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `booked_date` DATETIME NULL DEFAULT NULL AFTER `updated`;";
	$result = mysql_query($query);
	if($result) { print "Added Booked (scheduled) date field to Ticket table<BR />"; }

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `facility` INT(7) NULL DEFAULT NULL AFTER `info`;";
	$result = mysql_query($query);
	if($result) { print "Added Facility field to Log table<BR />"; }

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `rec_facility` INT(7) NULL DEFAULT NULL AFTER `facility`;";
	$result = mysql_query($query);
	if($result) { print "Added Reciving Facility field to Log table<BR />"; }

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `mileage` INT(8) NULL DEFAULT NULL AFTER `rec_facility`;";
	$result = mysql_query($query);
	if($result) { print "Added Mileage field to Log table<BR />"; }

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `facility_id` INT(8) NULL DEFAULT NULL AFTER `on_scene`;";
	$result = mysql_query($query);
	if($result) { print "Added Facility field to Assigns table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `rec_facility_id` INT(8) NULL DEFAULT NULL AFTER `facility_id`;";
	$result = mysql_query($query);
	if($result) { print "Added Receiving Facility field to Assigns table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` CHANGE `comments` TEXT;";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `u2fenr` DATETIME NULL DEFAULT NULL AFTER `rec_facility_id`;";
	$result = mysql_query($query);
	if($result) { print "Added u2fenr (unit en-route to facility) field to Assigns table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `u2farr` DATETIME NULL DEFAULT NULL AFTER `u2fenr`;";
	$result = mysql_query($query);
	if($result) { print "Added u2farr (unit arrived at facility) field to Assigns table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `start_miles` INT(8) NULL DEFAULT NULL AFTER `comments`;";
	$result = mysql_query($query);
	if($result) { print "Added start miles field to Assigns table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `end_miles` INT(8) NULL DEFAULT NULL AFTER `start_miles`;";
	$result = mysql_query($query);
	if($result) { print "Added end miles field to Assigns table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `multi` INT( 1 ) NOT NULL DEFAULT 0 COMMENT 'if 1, allow multiple call assigns' AFTER `direcs`;";
	$result = mysql_query($query);
	if($result) { print "Added 'multi' field to Responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `protocol` VARCHAR( 255 ) NULL AFTER `description` ;";
	$result = mysql_query($query);
	if($result) { print "Added protocol field to Incident Types table<BR />";}

	$query	= "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `utc_stamp` BIGINT( 12 ) NOT NULL DEFAULT 0 COMMENT 'Position timestamp in UTC' AFTER `altitude` ;";
	$result = mysql_query($query);
	if($result) { print "Added 'utc_stamp' field to tracks_hh table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `locatea` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses LocateA tracking - required to set callsign' AFTER `instam`;";
	$result = mysql_query($query);
	if($result) { print "Added 'locatea' field to Responder table for use with 'LocateA' tracking<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `gtrack` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Gtrack tracking - required to set callsign' AFTER `locatea`;";
	$result = mysql_query($query);
	if($result) { print "Added 'gtrack' field to Responder table for use with 'Gtrack' tracking<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `glat` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Google Latitude tracking - required to set callsign' AFTER `gtrack`;";
	$result = mysql_query($query);
	if($result) { print "Added 'glat' field to Responder table for use with 'Google Latitude' tracking<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `handle` VARCHAR( 24 ) NULL DEFAULT NULL COMMENT 'Unit Handle' AFTER `callsign`;";
	$result = mysql_query($query);
	if($result) { print "Added 'handle' field to Responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `radius` INT( 4 ) NOT NULL DEFAULT 0 COMMENT 'enclosing circle',
				ADD `color` VARCHAR( 8 ) NULL DEFAULT NULL ,
				ADD `opacity` INT( 3 ) NOT NULL DEFAULT '0';";
	$result = mysql_query($query);
	if($result) { print "Added 'radius', 'color', and 'opacity' fields to Incident Types table<BR />";}

	if (!table_exists("chat_invites")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]chat_invites` (
			`id` int(7) NOT NULL AUTO_INCREMENT,
			`to` varchar(64) NOT NULL COMMENT 'comma sep''d, 0 = all',
			`_by` int(7) NOT NULL,
			`_from` varchar(16) NOT NULL,
			`_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$result = mysql_query($query);
		if($result) { print "Added Chat Invites Table to support Chat function<BR />"; }
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `hide` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `description` ;";
	$result = mysql_query($query);
	if($result) { print "Added 'hide' field to Unit Status table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `_by` INT( 7 ) NOT  NULL DEFAULT '0' COMMENT 'Call taker id' ";
	$result = mysql_query($query);
	if($result) { print "Added 'By' field to Ticket table - stores who created the Ticket<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `expires` TIMESTAMP NULL  DEFAULT NULL COMMENT 'session start time';";
	$result = mysql_query($query);
	if($result) { print "Added 'expires' field to User table to auto log out user after inactivity<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `sid` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'php session id';";
	$result = mysql_query($query);
	if($result) { print "Added 'sid' field to User table - stores Session ID<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `login` TIMESTAMP NULL COMMENT 'last login';";
	$result = mysql_query($query);
	if($result) { print "Added 'login' field to User table - stores last login datetime<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `_from` VARCHAR( 24 ) NULL COMMENT 'IP addr';";
	$result = mysql_query($query);
	if($result) { print "Added 'from' field to User table - stores user logged in IP address<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user`  ADD `browser` VARCHAR( 40 ) NULL COMMENT 'used at last login';";
	$result = mysql_query($query);
	if($result) { print "Added 'browser' field to User table - stores last logged in browser type<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `bg_color` VARCHAR( 16 ) NOT NULL DEFAULT 'transparent' COMMENT 'background color',
		ADD `text_color` VARCHAR( 16 ) NOT NULL DEFAULT '#000000' COMMENT 'text color'";
	$result = mysql_query($query);
	if($result) { print "Added 'bg_color' and 'text_color' fields to Unit Status table to support different colors for statuses<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_status` ADD `bg_color` VARCHAR( 16 ) NOT NULL DEFAULT 'transparent' AFTER `sort` ,
		ADD `text_color` VARCHAR( 16 ) NOT NULL DEFAULT '#000000' AFTER `bg_color`";
	$result = mysql_query($query);
	if($result) { print "Added 'bg_color' and 'text_color' fields to Facility Status table to support different colors for statuses<BR />";}

	if (!table_exists("constituents")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]constituents` (
			`id` bigint(8) NOT NULL AUTO_INCREMENT,
			`contact` varchar(48) NOT NULL,
			`street` varchar(48) DEFAULT NULL,
			`apartment` varchar(48) DEFAULT NULL,
			`city` varchar(48) DEFAULT NULL,
			`state` char(2) DEFAULT NULL,
			`miscellaneous` varchar(80) DEFAULT NULL,
			`phone` varchar(16) NOT NULL,
			`email` varchar(48) DEFAULT NULL,
			`lat` double DEFAULT NULL,
			`lng` double DEFAULT NULL,
			`updated` varchar(16) DEFAULT NULL,
			`_by` int(7) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";
		$result = mysql_query($query);
		if($result) { print "Added Consituents Table to support frequent users functionality<BR />"; }
		}

	//			// 3/24/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types`
		CHANGE `type` `type` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
		CHANGE `description` `description` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
		CHANGE `sort` `sort` INT( 11 ) NULL DEFAULT NULL ,
		CHANGE `radius` `radius` INT( 4 ) NULL DEFAULT NULL ,
		CHANGE `opacity` `opacity` INT( 3 ) NULL DEFAULT NULL";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` CHANGE `description` `description` VARCHAR( 60 )
		CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
		CHANGE `sort` `sort` INT( 11 ) NOT NULL DEFAULT '0'";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	if (!table_exists("pin_ctrl")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]pin_ctrl` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `responder_id` int(7) NOT NULL DEFAULT '0' COMMENT 'link to responder record',
			  `pin` varchar(4) NOT NULL COMMENT 'login authentication ',
			  `_by` int(7) NOT NULL COMMENT 'user creating/updating this entry',
			  `_from` varchar(30) DEFAULT NULL COMMENT 'IP address',
			  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$result = mysql_query($query);
		if($result) { print "Added pin_ctrl Table<BR />"; }
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` CHANGE `id` `id` BIGINT( 7 ) NOT NULL AUTO_INCREMENT ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `contact` VARCHAR(48) NULL DEFAULT NULL AFTER `id` ";
	$result = mysql_query($query);
	if($result) { print "Added 'contact' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `street` VARCHAR(48) NULL DEFAULT NULL AFTER `contact` ";
	$result = mysql_query($query);
	if($result) { print "Added 'street' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `apartment` VARCHAR(48) NULL DEFAULT NULL AFTER `street` ";
	$result = mysql_query($query);
	if($result) { print "Added 'appartment' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `city` VARCHAR(48) NULL DEFAULT NULL AFTER `apartment` ";
	$result = mysql_query($query);
	if($result) { print "Added 'city' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `state` char(2) 	NULL DEFAULT NULL AFTER `city` ";
	$result = mysql_query($query);
	if($result) { print "Added 'state' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `miscellaneous` VARCHAR(80) NULL DEFAULT NULL AFTER `state` ";
	$result = mysql_query($query);
	if($result) { print "Added 'miscellaneous' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone` VARCHAR(16) NULL DEFAULT NULL AFTER `miscellaneous` ";
	$result = mysql_query($query);
	if($result) { print "Added 'phone' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_2` VARCHAR(16) NULL DEFAULT NULL AFTER `phone` ";
	$result = mysql_query($query);
	if($result) { print "Added 'phone_2' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_3` VARCHAR(16) NULL DEFAULT NULL AFTER `phone_2` ";
	$result = mysql_query($query);
	if($result) { print "Added 'phone_3' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_4` VARCHAR(16) NULL DEFAULT NULL AFTER `phone_3` ";
	$result = mysql_query($query);
	if($result) { print "Added 'phone_4' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `email` VARCHAR(48) NULL DEFAULT NULL AFTER `phone_4` ";
	$result = mysql_query($query);
	if($result) { print "Added 'email' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `lat` double NULL DEFAULT NULL AFTER `email` ";
	$result = mysql_query($query);
	if($result) { print "Added 'lat' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `lng` double NULL DEFAULT NULL AFTER `lat` ";
	$result = mysql_query($query);
	if($result) { print "Added 'lng' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `updated` VARCHAR(16) NULL DEFAULT NULL AFTER `lng` ";
	$result = mysql_query($query);
	if($result) { print "Added 'updated' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `_by` int(7) NULL DEFAULT NULL AFTER `updated` ";
	$result = mysql_query($query);
	if($result) { print "Added '_by' field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `responder_id` INT( 7 ) NOT NULL DEFAULT '0' COMMENT 'For level = unit' AFTER `level`";
	$result = mysql_query($query);
	if($result) { print "Added 'responder_id' field to user table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]chat_messages` CHANGE `message` `message` VARCHAR( 2048 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `dispatch` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0 - can dispatch, 1- no - inform, 2 - enforce' AFTER `description`";
	$result = mysql_query($query);
	if($result) { print "Added 'dispatch' field to un_status (Unit Status) table to allow or disallow dispatch of specific status values<BR />";}

	do_setting ('sound_wav','aooga.wav');
	do_setting ('sound_mp3','phonesring.mp3');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket`
		CHANGE `facility` `facility` INT( 4 ) NULL DEFAULT '0',
		CHANGE `rec_facility` `rec_facility` INT( 4 ) NULL DEFAULT '0'";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `_by` `_by` INT( 7 ) NULL DEFAULT NULL";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities`
		CHANGE `_by` `_by` INT( 7 ) NULL DEFAULT NULL ,
		CHANGE `_from` `_from` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
		CHANGE `_on` `_on` DATETIME NULL DEFAULT NULL" ;
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` CHANGE `dob` `dob` TEXT NULL DEFAULT NULL ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `set_severity` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'sets incident severity' AFTER `protocol`";
	$result = mysql_query($query);
	if($result) { print "Added 'set_severity' field to Incident Types to allow severity to be automatically varied by incident type<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `nine_one_one` VARCHAR( 96 ) NULL DEFAULT NULL COMMENT 'comments re 911' AFTER `comments` ";
	$result = mysql_query($query);
	if($result) { print "Added 'nine_one_one' field to Ticket table - allows comments re 911 call<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `street` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `name` ";
	$result = mysql_query($query);
	if($result) { print "Added 'street' field to Responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `city` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `street`;";
	$result = mysql_query($query);
	if($result) { print "Added 'city' field to Responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `state` CHAR( 2 ) NULL DEFAULT NULL AFTER `city`;";
	$result = mysql_query($query);
	if($result) { print "Added 'state' field to Responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `phone` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `state`;";
	$result = mysql_query($query);
	if($result) { print "Added 'phone' field to Responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `street` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `name`;";
	$result = mysql_query($query);
	if($result) { print "Added 'street' field to Facility table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `city` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `street`;";
	$result = mysql_query($query);
	if($result) { print "Added 'city' field to Facility table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `state` CHAR( 2 ) NULL DEFAULT NULL AFTER `city`;";
	$result = mysql_query($query);
	if($result) { print "Added 'state' field to Facility table<BR />";}

	remove_setting('unit_status_chg');

	$the_table = "$GLOBALS[mysql_prefix]captions";
	if (!(mysql_table_exists($the_table))) {
		$query = "CREATE TABLE IF NOT EXISTS `{$the_table}` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `capt` varchar(64) NOT NULL,
			  `repl` varchar(64) NOT NULL,
			  `_by` int(7) NOT NULL DEFAULT '0',
			  `_from` varchar(16) NOT NULL DEFAULT '''''',
			  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";
		} else {
		$query = "ALTER TABLE `{$the_table}` CHANGE `capt` `capt` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
			CHANGE `repl` `repl` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL";
		}		// end  (!(mysql_table_exists($the_table)))
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	require_once ("./incs/capts.inc.php");
	for ($i=0; $i< count($capts); $i++) {
		$temp = quote_smart($capts[$i]);
		$query = "SELECT `repl` FROM `{$the_table}` WHERE `repl` = {$temp} LIMIT 1;";		// 5/1/12
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_num_rows ($result) == 0) {
			$query = "INSERT INTO `{$the_table}` (`capt`, `repl`) VALUES ({$temp}, {$temp});";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}
		}
	unset ($result);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` CHANGE `info` `info` VARCHAR( 2049 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	do_setting ('internet','1');
	do_setting ('disp_stat','D/R/O/FE/FA/Clear');
	do_setting ('oper_can_edit','0');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_types` CHANGE `name` `name` VARCHAR(48) NOT NULL ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_types` CHANGE `description` `description` VARCHAR(96) NOT NULL ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `street` `street` VARCHAR( 96 )  NULL DEFAULT NULL";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `state` `state` CHAR( 4 )  NULL DEFAULT NULL";
	$result = mysql_query($query);

	$left_br = "{";
	$the_year = date("y");								// 2-digit year - restart numbers at yr rollover
	$the_inc_num = trim(get_variable('_inc_num'));		// possibly empty

	if (!(strlen($the_inc_num)>0)) {
		do_setting ('_inc_num',base64_encode(serialize(array("0", "", "", "", "0", $the_year))));		// insert if absent
		update_setting ('_inc_num', base64_encode(serialize(array("0", "", "", "", "0", $the_year))));	// it's there now, update it
		} else {									// exists, not-empty
		if(strpos($the_inc_num, $left_br)) {		// if unencoded - else ignore
			$instr = unserialize(get_variable('_inc_num'));
			$outstr = base64_encode(serialize($the_inc_num));
			update_setting ('_inc_num',$outstr);
			}
		}

	$the_table = "$GLOBALS[mysql_prefix]codes";				// 12/15/10
	if (!(mysql_table_exists($the_table))) {
		$query = "CREATE TABLE `{$the_table}` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `code` varchar(20) NOT NULL,
		  `text` varchar(64) NOT NULL,
		  `sort` int(3) NOT NULL DEFAULT '999',
		  `_by` int(7) NOT NULL DEFAULT 0,
		  `_from` varchar(16) NOT NULL DEFAULT '',
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";
		$result = mysql_query($query);
		if($result) { print "Created Codes table<BR />";}

		$query = "INSERT INTO `{$the_table}` (`code`, `text`, `sort`) VALUES
			('ex-1', 'Instructed to return to station ASAP', 999),
			('ex-2', 'Requested to contact Dispatch Central by voice', 999);";
		$result = mysql_query($query);
		if($result) { print "Inserted example data into codes table<BR />";}
		}

	$the_table = "$GLOBALS[mysql_prefix]hints";			// 11/30/10
	if (!(mysql_table_exists($the_table))) {

		$query = "CREATE TABLE IF NOT EXISTS `{$the_table}` (
			`id` int(7) NOT NULL AUTO_INCREMENT,
			`tag` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
			`hint` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
			`_by` int(7) NOT NULL DEFAULT '0',
			`_from` varchar(16) CHARACTER SET latin1 DEFAULT NULL,
			`_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Created Hints table<BR />";}

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]hints` (`tag`, `hint`) VALUES
			('_loca', 'Location - type in location in fields, click location on map or use *Located at Facility* menu below '),
			('_city', 'City - defaults to default city set in configuration. Enter City if required'),
			('_state', 'State - US State or non-US Country code - e.g. UK for United Kingdom'),
			('_phone', 'Phone number - for US only, you can use the lookup button to get the callers name and location using the White Pages'),
			('_nature', 'Incident  nature or Type - Available types are set in in_types table in the configuration'),
			('_prio', 'Incident priority - Normal, Medium or High. Affects order and coloring of incidents on Situation display'),
			('_proto', 'Incident Protocol - this will show automatically if a protocol is set for the Incident Enter the configuration'),
			('_synop', 'Synopsis - Details about the incident, ensure as much detail as possible is completed'),
			('_911', '911 contact information'),
			('_caller', 'Caller reporting the incident'),
			('_name', 'Incident Name - Partially completed and prepend or append incident ID depending on setting. Enter an easily identifiable name.'),
			('_booked', 'Scheduled Date. Must be set if incident Status is *Scheduled*. Sets date and time for a future booked Incident, mainly used for non immediate patient transport. Click on Radio button to show date field'),
			('_facy', 'Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the Incident will be received'),
			('_start', 'Run-start, Incident start time. Defaults to current date and time or edit by clicking padlock icon to enable date & time fields'),
			('_status', 'Incident  Status - Open or Closed or set to Scheduled for future booked calls'),
			('_end', 'Run-end - incident  end time. When incident is closed, click on radio button which will enable date & time fields'),
			('_disp', 'Disposition - additional comments about incident, particularly closing it'),
			('_coords', 'Incident Lat/Lng - set by clicking on the map for the location or by selecting location with the address fields.'),
			('_asof', 'Date/time of most recent incident data update');";
		$result = mysql_query($query);
		if($result) { print "Inserted data into codes table<BR />";}
		}				// end if (!(mysql_table_exists()))

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]captions`
		CHANGE `_by` `_by` INT( 7 ) NOT NULL DEFAULT '0',
		CHANGE `_from` `_from` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL; ";
	$result = mysql_query($query);

	$nature = get_text("Nature");
	$disposition = get_text("Disposition");
	$patient = get_text("Patient");
	$incident = get_text("Incident");
	$incidents = get_text("Incidents");

	do_caption("911 Contacted");
	do_caption("A");
	do_caption("About this version ...");
	do_caption("Add Action");
	do_caption("Add Facility");
	do_caption("Add note");
	do_caption("Add {$patient}");
	do_caption("Add Unit");
	do_caption("Add user");
	do_caption("Add/Edit Notifies");
	do_caption("Addr");
	do_caption("admin");
	do_caption("Alarm audio test");
	do_caption("All-Tickets Notify");
	do_caption("As of");
	do_caption("Board");
	do_caption("Cancel");
	do_caption("Capability");
	do_caption("Change display");
	do_caption("Chat");
	do_caption("City");
	do_caption("Clear");
	do_caption("Close incident");
	do_caption("Config");
	do_caption("Constituents");
	do_caption("Contact email");
	do_caption("Contact name");
	do_caption("Contact phone");
	do_caption("Contacts");
	do_caption("Current situation");
	do_caption("Delete Closed Tickets");
	do_caption("Description");
	do_caption("Dispatch Unit");
	do_caption("Dispatched");
	do_caption("{$disposition}");
	do_caption("Dump DB to screen");
	do_caption("E-mail");
	do_caption("Edit My Profile");
	do_caption("Edit Settings");
	do_caption("Email users");
	do_caption("Facs");
	do_caption("Facility arrive time");
	do_caption("Facility clear time");
	do_caption("Facility en-route time");
	do_caption("Facility Status");
	do_caption("Facility Types");
	do_caption("Facility");
	do_caption("Handle");
	do_caption("Help");
	do_caption("High");
	do_caption("ID");
	do_caption("{$incident} Lat/Lng");
	do_caption("{$incident} name");
	do_caption("{$incident} types");
	do_caption("{$incident}");
	do_caption("Lat/Lng");
	do_caption("Links");
	do_caption("Location");
	do_caption("Log In");
	do_caption("Log");
	do_caption("Logged in");
	do_caption("Logout");
	do_caption("Medium");
	do_caption("Mobile");
	do_caption("Module");
	do_caption("mouseover caption for help informati");
	do_caption("Name");
	do_caption("{$nature}");
	do_caption("New");
	do_caption("Next");
	do_caption("Normal");
	do_caption("Notify");
	do_caption("On-scene");
	do_caption("Opening hours");
	do_caption("Optimize Database");
	do_caption("P");
	do_caption("Password");
	do_caption("Perm's");
	do_caption("Phone");
	do_caption("Popup");
	do_caption("Position");
	do_caption("Primary pager");
	do_caption("Print");
	do_caption("Priority");
	do_caption("Protocol");
	do_caption("Region");
	do_caption("Reported by");
	do_caption("Reports");
	do_caption("Reset Database");
	do_caption("Responding");
	do_caption("Run End");
	do_caption("Run Start");
	do_caption("Scheduled Date");
	do_caption("Search");
	do_caption("Security contact");
	do_caption("Security email");
	do_caption("Security phone");
	do_caption("Security reqs");
	do_caption("Severities");
	do_caption("Situation");
	do_caption("SOP's");
	do_caption("Sort");
	do_caption("St");
	do_caption("Status");
	do_caption("Synopsis");
	do_caption("This Call");
	do_caption("Time");
	do_caption("Type");
	do_caption("U");
	do_caption("Unit status types");
	do_caption("Unit types");
	do_caption("Unit");
	do_caption("Units");
	do_caption("Updated");
	do_caption("User");
	do_caption("Written");
	do_caption("USNG");

	do_setting ('_cloud', 0);						// 11/27/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `on_scene_miles` INT( 8 ) NULL DEFAULT NULL AFTER `start_miles`;";		// 12/9/10
	$result = mysql_query($query);
	if($result) { print "Added on_scene_miles field to Assigns table<BR />";}

	if (!table_exists("places")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]places` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `name` varchar(64) DEFAULT NULL,
			  `lat` float DEFAULT '0',
			  `lon` float DEFAULT '0',
			  `zoom` int(2) DEFAULT '7',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$result = mysql_query($query);
		if($result) { print "Added Places Table<BR />"; }
		}

	$query = "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]group_test`";
	$result = mysql_query($query);
	if(mysql_affected_rows() != 0) { print "Dropped group_test table<BR />";}

	$query = "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]group`";
	$result = mysql_query($query);
	if(mysql_affected_rows() != 0) { print "Dropped group table<BR />";}

	do_setting ('aprs_fi_key','');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` CHANGE `info` `info` VARCHAR( 2048 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
	$result = mysql_query($query);

	if(!check_field_exists("responder", "icon_str")) {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `icon_str` CHAR( 3 ) NULL DEFAULT NULL COMMENT 'map icon value' AFTER `handle` ";
		$result = mysql_query($query);
		if($result) {print "Added icon_str field to responder table<BR />";}
		}

	if(!check_field_exists("facilities", "icon_str")) {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `icon_str` CHAR( 3 ) NULL DEFAULT NULL COMMENT 'map icon value' AFTER `handle` ";
		$result = mysql_query($query);
		if($result) {print "Added icon_str field to facilities table<BR />";}
		}

	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `handle`= SUBSTR(`description`,1,24) WHERE ((`handle` = '') OR (`handle` IS NULL));";
	$result = mysql_query($query);
	if(mysql_affected_rows() > 0) {print "Set handle field to a default setting if it is empty for all responders<BR />";}

	$query_update = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET `handle`= SUBSTR(`description`,1,24) WHERE ((`handle` = '') OR (`handle` IS NULL));";
	$result = mysql_query($query);
	if(mysql_affected_rows() > 0) {print "Set handle field to a default setting if it is empty for all facilities<BR />";}

	$counter = 0;
	$tables = array("$GLOBALS[mysql_prefix]responder", "$GLOBALS[mysql_prefix]facilities");		// 4/27/11
	for ($i=0; $i< count($tables); $i++) {
		$query = "SELECT * FROM `{$tables[$i]}`";
		$result = mysql_query($query);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 7/7/10
			if($row['icon_str'] == "" || is_null($row['icon_str'])) {
				$temp = explode("/", $row['name']);
				$icon_val = trim(substr($temp[count($temp) -1], -3, strlen($temp[count($temp) -1])));
				$counter ++;
				$icon_val = ($icon_val != "") ? $icon_val : "?";
				$query2 = "UPDATE `{$tables[$i]}` SET `icon_str` = '$icon_val' WHERE `id` = {$row['id']} LIMIT 1";
				$result2 = mysql_query($query2);
				}
			}		// end inner while()
		if($counter > 0) { print "Updated icon_str field to default value in " . $tables[$i] . " table<BR />";}
		}		// end outer while()

	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `handle` = REPLACE(`handle`, '\r', ' '),
		`handle` = REPLACE(`handle`, '\n', ' '),
		`handle` = REPLACE(`handle`, '  ', ' ')";
	$result = mysql_query($query);

	$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET `handle` = REPLACE(`handle`, '\r', ' '),
		`handle` = REPLACE(`handle`, '\n', ' '),
		`handle` = REPLACE(`handle`, '  ', ' ')";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` DROP `group` ";
	$result = mysql_query($query);
	if($result) { print "Dropped group field from facilities table if it existed<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` DROP `group` ";
	$result = mysql_query($query);
	if($result) { print "Dropped group field from ticket if it existed<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` DROP `group` ";
	$result = mysql_query($query);
	if($result) { print "Dropped group field from responder table if it existed<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` DROP `group` ";
	$result = mysql_query($query);
	if($result) { print "Dropped group field from user table if it existed<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` DROP `group` ";
	$result = mysql_query($query);
	if($result) { print "Dropped group field from assigns table if it existed<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `t_tracker` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses tickets tracker tracking - required to set callsign' AFTER `instam`;";
	$result = mysql_query($query);
	if($result) { print "Added field t_tracker to responder table<BR />";}

	if (!table_exists("remote_devices")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]remote_devices` (
			  `id` bigint(64) NOT NULL AUTO_INCREMENT,
			  `lat` double DEFAULT '0',
			  `lng` double DEFAULT '0',
			  `time` datetime NOT NULL,
			  `speed` int(4) NOT NULL DEFAULT '0',
			  `altitude` int(6) NOT NULL DEFAULT '0',
			  `direction` double NOT NULL DEFAULT '0',
			  `user` varchar(64) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added remote_devices table<BR />"; }
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` CHANGE `utc_stamp` `utc_stamp` BIGINT( 12 ) NULL DEFAULT NULL ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]patient` ADD `fullname` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `name` ,
			ADD `dob` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `fullname` ,
			ADD `gender` INT( 1 ) NOT NULL DEFAULT '0' AFTER `dob` ,
			ADD `insurance_id` INT (3) NOT NULL DEFAULT '0' COMMENT 'see table insurance' AFTER `gender` ,
			ADD `facility_contact` VARCHAR( 64 ) NOT NULL AFTER `insurance_id`,
			ADD `facility_id` INT( 3 ) NOT NULL DEFAULT '0' AFTER `facility_contact`";
	$result = mysql_query($query);
	if($result) { print "Added fullname, dob, gender, insurance_id, facility_contact, facility_id to patient table<BR />";}

	if (!table_exists("insurance")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]insurance` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `ins_value` varchar(64) NOT NULL,
			  `sort_order` int(3) NOT NULL DEFAULT '0',
			  `_by` int(7) NOT NULL,
			  `_from` varchar(16) DEFAULT NULL,
			  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$result = mysql_query($query);
		if($result) { print "Added insurance table<BR />"; }
		}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` WHERE `ins_value` = 'Example' LIMIT 1;";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]insurance` (`ins_value` ,`sort_order` ,`_by` ,`_from` ,`_on`)
			VALUES ( 'Example', '0', '0', NULL ,CURRENT_TIMESTAMP);";
		$result = mysql_query($query);
		if($result) { print "Inserted example entry into insurance table<BR />";}
		}

	do_caption("Full name");
	do_caption("Date of birth");
	do_caption("Gender");
	do_caption("Insurance");
	do_caption("Facility contact");
	do_caption("Facility id");
	do_caption("Catchment Area");
	do_caption("Ring Fence");
	do_caption("Exclusion Zone");

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` CHANGE `from` `from` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `ogts` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'value = 1 iff unit uses OpenGTS tracking' AFTER `instam`;";
	$result = mysql_query($query);
	if($result) { print "Added ogts field to responder table to support Open GTS<BR />";}

	do_setting ('ogts_info','');			// 7/5/11

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `closest_city` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `status`";
	$result = mysql_query($query);
	if($result) { print "Added closest_city field to tracks_hh table<BR />";}

	if (!table_exists("mmarkup")) {	//	6/10/11
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mmarkup` (
		  `id` bigint(4) NOT NULL AUTO_INCREMENT,
		  `line_name` varchar(32) NOT NULL,
		  `line_status` int(2) NOT NULL DEFAULT '0' COMMENT '0 => show, 1 => hide',
		  `line_type` varchar(1) DEFAULT NULL COMMENT 'poly, circle, banner, ellipse',
		  `line_ident` varchar(10) DEFAULT NULL,
		  `line_cat_id` int(3) NOT NULL DEFAULT '0',
		  `line_data` varchar(4096) NOT NULL,
		  `use_with_bm` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with base map',
		  `use_with_r` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with regions',
		  `use_with_f` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with facilities',
		  `use_with_u_ex` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units - exclusion zone',
		  `use_with_u_rf` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units - ringfence',
		  `line_color` varchar(8) DEFAULT NULL,
		  `line_opacity` float DEFAULT NULL,
		  `line_width` int(2) DEFAULT NULL,
		  `fill_color` varchar(8) DEFAULT NULL,
		  `fill_opacity` float DEFAULT NULL,
		  `filled` int(1) DEFAULT '0',
		  `_by` int(7) NOT NULL DEFAULT '0',
		  `_from` varchar(16) DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Lines and borders';";
		$result = mysql_query($query);
		if($result) { print "Added mmarkup (Map Markup) table<BR />";}

		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mmarkup_cats` (
		  `id` bigint(4) NOT NULL AUTO_INCREMENT,
		  `category` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
		  `_by` int(7) NOT NULL DEFAULT '0',
		  `_from` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Map markup categories' ;";
		$result = mysql_query($query);
		if($result) { print "Added mmarkup_cats (Map Markup categories table<BR />";}

		$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
		$query_insert = "INSERT INTO `$GLOBALS[mysql_prefix]mmarkup_cats` (`id`, `category`, `_by`, `_from`, `_on`) VALUES
		(1, 'Region Boundary', '1', 'install routine', '$now'),
		(2, 'Banners', '1', 'install routine', '$now'),
		(3, 'Facility Catchment', '1', 'install routine', '$now'),
		(4, 'Ring Fence', '1', 'install routine', '$now'),
		(5, 'Exclusion Zone', '1', 'install routine', '$now');";
		$result_insert = mysql_query($query_insert);
		if($result_insert) { print "Inserted standard entries in mmarkup_cats table<BR />";}
		}

	if (!table_exists("stats_type")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]stats_type` (
		`st_id` int(2) NOT NULL AUTO_INCREMENT,
		`name` varchar(64) NOT NULL,
		`stat_type` varchar(3) NOT NULL DEFAULT 'int',
		PRIMARY KEY (`st_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) {
			print "Added stats_type table<BR />";
			$query_insert = "INSERT INTO `$GLOBALS[mysql_prefix]stats_type` (`st_id`, `name`, `stat_type`) VALUES
			(1, 'Number of Open Tickets', 'int'),
			(2, 'Tickets not Assigned', 'int'),
			(3, 'Units Assgnd not Responding', 'int'),
			(4, 'Units Respg Not On Scene', 'int'),
			(5, 'Units On Scene', 'int'),
			(6, 'Average Time to Dispatch', 'avg'),
			(7, 'Average Dispatched to Responding', 'avg'),
			(8, 'Average Dispatched to On Scene', 'avg'),
			(9, 'Average Time Ticket Open', 'avg'),
			(10, 'Number of available Responders', 'int'),
			(11, 'Average time to close ticket', 'avg'),
			(12, 'Average time to first dispatch', 'avg');";
			$result_insert = mysql_query($query_insert);
			if($result_insert) { print "Inserted standard entries in stats_type table<BR />";}
			}
		}

	if (!table_exists("stats_settings")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]stats_settings` (
		`id` int(3) NOT NULL AUTO_INCREMENT,
		`user_id` int(3) NOT NULL,
		`refresh_rate` int(3) NOT NULL DEFAULT '10',
		`f1` int(3) NOT NULL DEFAULT '1',
		`f2` int(3) NOT NULL DEFAULT '2',
		`f3` int(3) NOT NULL DEFAULT '3',
		`f4` int(3) NOT NULL DEFAULT '4',
		`f5` int(3) NOT NULL DEFAULT '5',
		`f6` int(3) NOT NULL DEFAULT '6',
		`f7` int(3) NOT NULL DEFAULT '7',
		`f8` int(3) NOT NULL DEFAULT '8',
		`threshold_1` varchar(12) NOT NULL DEFAULT '0',
		`threshold_2` varchar(12) NOT NULL DEFAULT '0',
		`threshold_3` varchar(12) NOT NULL DEFAULT '0',
		`threshold_4` varchar(12) NOT NULL DEFAULT '0',
		`threshold_5` varchar(12) NOT NULL DEFAULT '0',
		`threshold_6` varchar(12) NOT NULL DEFAULT '0',
		`threshold_7` varchar(12) NOT NULL DEFAULT '0',
		`threshold_8` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_1` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_2` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_3` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_4` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_5` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_6` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_7` varchar(12) NOT NULL DEFAULT '0',
		`thresholdw_8` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_1` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_2` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_3` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_4` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_5` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_6` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_7` varchar(12) NOT NULL DEFAULT '0',
		`thresholdf_8` varchar(12) NOT NULL DEFAULT '0',
		`t_type1` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type2` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type3` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type4` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type5` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type6` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type7` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		`t_type8` enum('Less','Less or Equal','Equal','More or Equal','More') NOT NULL DEFAULT 'More',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='settings for statistics screen' AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added stats_settings table<BR />";}
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `ring_fence` INT( 3 ) NOT NULL DEFAULT '0' AFTER `t_tracker` ";
	$result = mysql_query($query);
	if($result) { print "Added ring_fence field to responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `excl_zone` INT( 3 ) NOT NULL DEFAULT '0' AFTER `ring_fence` ";
	$result = mysql_query($query);
	if($result) { print "Added excl_zone field to responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `boundary` INT( 3 ) NOT NULL DEFAULT '0' AFTER `icon_str` ";
	$result = mysql_query($query);
	if($result) { print "Added boundary field to facilities table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` CHANGE `state` `state` CHAR( 4 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` CHANGE `state` `state` CHAR( 4 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]settings` CHANGE `value` `value` VARCHAR( 512 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `smsg_id` VARCHAR( 16 ) DEFAULT NULL AFTER `contact_via` ;";
	$result = mysql_query($query);
	if($result) { print "Added smsg_id field length to responder table<BR />";}

	if (!table_exists("auto_status")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]auto_status` (
			`id` int(3) NOT NULL AUTO_INCREMENT,
			`text` varchar(24) NOT NULL,
			`status_val` int(3) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added auto_status table<BR />";}
		}

	if (!table_exists("known_sources")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]known_sources` (
			`id` int(4) NOT NULL AUTO_INCREMENT,
			`contact` varchar(64) NOT NULL,
			`email` varchar(64) NOT NULL,
			`allow` int(2) NOT NULL DEFAULT '0',
			`_by` int(7) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added known_sources table<BR />";}
		}


	if (!table_exists("messages")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]messages` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`msg_type` int(2) NOT NULL,
			`message_id` varchar(24) DEFAULT NULL,
			`ticket_id` int(8) DEFAULT NULL,
			`resp_id` varchar(128) DEFAULT NULL,
			`recipients` varchar(1024) DEFAULT NULL,
			`from_address` varchar(128) NOT NULL,
			`fromname` varchar(128) DEFAULT NULL,
			`subject` varchar(128) NOT NULL DEFAULT 'No Subject',
			`message` longtext,
			`status` varchar(24) DEFAULT NULL,
			`date` datetime NOT NULL,
			`read_status` int(11) NOT NULL DEFAULT '0',
			`readby` varchar(512) DEFAULT NULL,
			`delivered` varchar(512) DEFAULT NULL,
			`delivery_status` tinyint(2) NOT NULL DEFAULT '0',
			`_by` int(7) DEFAULT NULL,
			`_from` varchar(16) DEFAULT NULL,
			`_on` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added messages table<BR />";}
		}

	if (!table_exists("messages_bin")) {		//	10/23/12
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]messages_bin` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`msg_type` int(2) NOT NULL,
			`message_id` varchar(24) DEFAULT NULL,
			`ticket_id` int(8) DEFAULT NULL,
			`resp_id` varchar(128) DEFAULT NULL,
			`recipients` varchar(1024) DEFAULT NULL,
			`from_address` varchar(128) NOT NULL,
			`fromname` varchar(128) DEFAULT NULL,
			`subject` varchar(128) NOT NULL DEFAULT 'No Subject',
			`message` longtext,
			`status` varchar(24) DEFAULT NULL,
			`date` datetime NOT NULL,
			`read_status` int(11) NOT NULL DEFAULT '0',
			`readby` varchar(512) DEFAULT NULL,
			`delivered` varchar(512) DEFAULT NULL,
			`delivery_status` tinyint(2) NOT NULL DEFAULT '0',
			`_by` int(7) DEFAULT NULL,
			`_from` varchar(16) DEFAULT NULL,
			`_on` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added messages_bin table<BR />";}
		}

	if (!table_exists("msg_settings")) {		//	11/28/12, 7/10/13, 9/10/13
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]msg_settings` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` tinytext,
		`value` varchar(512) DEFAULT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added msg_settings table<BR />";}

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]msg_settings` (`id`, `name`, `value`) VALUES
			(1, 'email_server', ''),
			(2, 'email_port', ''),
			(3, 'email_protocol', 'POP3'),
			(4, 'email_addon', 'notls'),
			(5, 'email_folder', 'INBOX'),
			(6, 'email_userid', ''),
			(7, 'email_password', ''),
			(8, 'email_svr_simple', '0'),
			(9, 'smsg_provider', 'SMS Responder'),
			(10, 'smsg_server', 'http://gate1.sms-responder.com/external/smsrcheck.asp'),
			(11, 'smsg_server2', 'http://gate2.sms-responder.com/external/smsrcheck.asp'),
			(12, 'smsg_og_serv1', 'http://gate1.sms-responder.com/external/smsrsend.asp'),
			(13, 'smsg_og_serv2', 'http://gate2.sms-responder.com/external/smsrsend.asp'),
			(14, 'smsg_server_inuse', '1'),
			(15, 'smsg_force_sec', '0'),
			(16, 'smsg_orgcode', '0'),
			(17, 'smsg_apipin', '0'),
			(18, 'smsg_mode', 'SENDXML'),
			(19, 'smsg_replyto', ''),
			(20, 'smsg_replyto_2', ''),
			(21, 'columns', '1,2,3,4,5,6,7'),
			(22, 'use_autostat', '0'),
			(23, 'start_tag', '*'),
			(24, 'end_tag', '*');";
		$result = mysql_query($query);
		if($result) { print "Inserted settings in msg_settings table<BR />";}
		}

	if (!table_exists("requests")) {		//	11/28/12, 9/10/13
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]requests` (
			`id` bigint(8) NOT NULL AUTO_INCREMENT,
			`contact` varchar(48) NOT NULL DEFAULT '',
			`street` varchar(12000) DEFAULT NULL,
			`city` varchar(12000) DEFAULT NULL,
			`state` char(4) DEFAULT NULL,
			`the_name` varchar(64) DEFAULT NULL,
			`phone` varchar(16) DEFAULT NULL,
			`orig_facility` int(4) DEFAULT '0',
			`rec_facility` int(4) DEFAULT '0',
			`scope` text NOT NULL,
			`description` text NOT NULL,
			`comments` text,
			`lat` varchar(12000) DEFAULT NULL,
			`lng` varchar(12000) DEFAULT NULL,
			`request_date` datetime DEFAULT NULL,
			`status` enum('Open','Tentative','Accepted','Resourced','Complete','Declined','Closed') NOT NULL DEFAULT 'Open',
			`tentative_date` datetime DEFAULT NULL,
			`accepted_date` datetime DEFAULT NULL,
			`declined_date` datetime DEFAULT NULL,
			`resourced_date` datetime DEFAULT NULL,
			`completed_date` datetime DEFAULT NULL,
			`closed` datetime DEFAULT NULL,
			`requester` bigint(8) NOT NULL,
			`ticket_id` bigint(8) DEFAULT NULL,
			`_by` int(7) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `ID` (`id`),
			KEY `requester` (`requester`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added requests table<BR />";}
		}

	if (!table_exists("std_msgs")) {		//	10/23/12
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]std_msgs` (
			`id` int(4) NOT NULL AUTO_INCREMENT,
			`message` varchar(248) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added std_msgs table<BR />";}

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]std_msgs` (`id`, `message`) VALUES
			(1, 'Example Standard Message');";
		$result = mysql_query($query);
		if($result) { print "Added example standard message in std_msgs table<BR />";}
		}

	do_caption ("Beds") ;
	do_caption ("Available") ;
	do_caption ("Occupied") ;
	do_caption ("Beds information") ;

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities`
		ADD `beds_a` VARCHAR( 6 ) NULL DEFAULT NULL COMMENT 'Available' AFTER `description` ,
		ADD `beds_o` VARCHAR( 6 ) NULL DEFAULT NULL COMMENT 'Occupied' AFTER `beds_a` ,
		ADD `beds_info` VARCHAR( 2048 ) NULL DEFAULT NULL COMMENT 'Information' AFTER `beds_o` ";
	$result = mysql_query($query);
	if($result) { print "Added beds_a, beds_o and beds_info fields to facilities table (Beds Available, Beds Occupied and Beds Information<BR />";}

	if (!table_exists("replacetext")) {		//	10/23/12
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]replacetext` (
			`id` int(3) NOT NULL auto_increment,
			`in_text` varchar(128) NOT NULL,
			`out_text` varchar(128) NOT NULL,
			`add_ticket` enum('Yes','No') NOT NULL default 'No',
			`add_user` enum('Yes','No') NOT NULL default 'No',
			`add_user_unit` enum('Yes','No') NOT NULL default 'No',
			`add_time` enum('Yes','No') NOT NULL default 'No',
			`add_date` enum('Yes','No') NOT NULL default 'No',
			`app_summ` enum('Yes','No') NOT NULL default 'No',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added replacetext table to support replacement text in standard messages<BR />";}
		}

	if (!table_exists("organisations")) {		//	10/23/12
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]organisations` (
			`id` int(4) NOT NULL auto_increment,
			`name` varchar(128) NOT NULL,
			`street` varchar(256) NOT NULL,
			`city` varchar(64) NOT NULL,
			`state` varchar(4) NOT NULL,
			`tel` varchar(16) NOT NULL,
			`email` varchar(256) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";
		$result = mysql_query($query);
		if($result) { print "Added organisations table to support user organisations for billing out<BR />";}
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `org` INT( 3 ) NOT NULL DEFAULT '0' COMMENT 'Organisation' AFTER `in_types_id`;";
	$result = mysql_query($query);
	if($result) { print "Added org field to ticket table to support organisations - not used<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `org` INT( 3 ) NOT NULL DEFAULT '0' COMMENT 'Organisation' AFTER `id`;";
	$result = mysql_query($query);
	if($result) { print "Added org field to requests table to support organisations - not used<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `org` INT( 3 ) NOT NULL DEFAULT '0' COMMENT 'Organisation' AFTER `pers`;";
	$result = mysql_query($query);
	if($result) { print "Added org field to user table to support organisations<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `miles` INT( 8 ) NULL DEFAULT NULL AFTER `end_miles`;";
	$result = mysql_query($query);
	if($result) { print "Added miles field to assigns table - alternative to calculated miles from start and end - total miles<BR />";}

	do_caption("messaging help", "Messaging Help Goes Here");
	do_msg_setting ('email_del','1');			// 5/25/13

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]allocates` CHANGE `resource_id` `resource_id` INT( 8 ) NULL DEFAULT NULL";
	$result = mysql_query($query);

	do_index("allocates", "resource_id");
	do_index("allocates", "type");

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `status_updated` DATETIME NULL DEFAULT NULL AFTER `updated`";
	$result = mysql_query($query);
	if($result) { print "Added status_updated field to responder table<BR />";}

	do_setting ('broadcast','0');
	do_setting ('hide_booked','48');
	do_setting ('ics_top','0');
	do_setting ('auto_refresh','1/1/1');
	do_msg_setting ('no_whitelist','0');
	do_caption("HAS");

	if (!table_exists("conditions")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]conditions` (
			`id` int(2) NOT NULL auto_increment,
			`title` varchar(128) NOT NULL,
			`description` longtext,
			`icon` varchar(128) NOT NULL,
			`_by` int(6) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added conditions table<BR />";}
		}

	if(table_exists("roadinfo") && !check_field_exists('roadinfo', 'username')) {
		$query = "DROP TABLE `$GLOBALS[mysql_prefix]roadinfo`;";
		$result = mysql_query($query);
		if($result) { print "1754 Dropped old format roadinfo table<BR />";}

		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]roadinfo` (
			`id` int(6) NOT NULL auto_increment,
			`title` varchar(128) NOT NULL,
			`description` longtext NOT NULL,
			`address` varchar(512) default NULL,
			`conditions` int(2) NOT NULL,
			`lat` varchar(16) NOT NULL,
			`lng` varchar(16) NOT NULL,
			`username` varchar(24) NOT NULL,
			`_by` int(4) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Main table for roads information' AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added roadinfo table<BR />";}
		} elseif(!table_exists("roadinfo")){
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]roadinfo` (
			`id` int(6) NOT NULL auto_increment,
			`title` varchar(128) NOT NULL,
			`description` longtext NOT NULL,
			`address` varchar(512) default NULL,
			`conditions` int(2) NOT NULL,
			`lat` varchar(16) NOT NULL,
			`lng` varchar(16) NOT NULL,
			`username` varchar(24) NOT NULL,
			`_by` int(4) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Main table for roads information' AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "1788 Added roadinfo table<BR />";}
		}

	do_setting ('use_responder_mobile','1');
	do_setting ('responder_mobile_tracking','2');
	do_setting ('local_maps','0');
	do_setting ('cloudmade_api','');
	do_setting ('responder_mobile_forcelogin','1');
	do_setting ('use_disp_autostat','0');
	do_setting ('portal_contact_email','');
	do_setting ('portal_contact_phone','');
	do_setting ('notify_facilities','0');
	do_setting ('notify_in_types','0');
	do_setting ('warn_proximity','1');
	do_setting ('warn_proximity_units','M');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `roster_user` INT( 7 ) NOT NULL DEFAULT '0' AFTER `id`";
	$result = mysql_query($query);
	if($result) { print "Added roster_user field to responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `status_about` VARCHAR( 512 ) NULL AFTER `un_status_id`";
	$result = mysql_query($query);
	if($result) { print "Added status_about field to responder table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `status_about` VARCHAR( 512 ) NULL AFTER `status_id`";
	$result = mysql_query($query);
	if($result) { print "Added status_about field to facilities table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `address_about` VARCHAR( 512 ) NULL AFTER `street`";
	$result = mysql_query($query);
	if($result) { print "Added address_about field to ticket table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `to_address` VARCHAR( 1024 ) NULL AFTER `phone`";
	$result = mysql_query($query);
	if($result) { print "Added to_address field to ticket table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `to_address` VARCHAR( 1024 ) NULL AFTER `phone`";
	$result = mysql_query($query);
	if($result) { print "Added to_address field to requests table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `cancelled` DATETIME NULL AFTER `closed`";
	$result = mysql_query($query);
	if($result) { print "Added cancelled field to requests table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]notify` ADD `mailgroup` INT( 4 ) NOT NULL DEFAULT '0' AFTER `email_address`";
	$result = mysql_query($query);
	if($result) { print "Added mailgroup field to notify table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `notify_mailgroup` INT( 4 ) NOT NULL DEFAULT '0' AFTER `callsign`";
	$result = mysql_query($query);
	if($result) { print "Added notify_mailgroup field to facilities table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `notify_email` VARCHAR( 256 ) NULL DEFAULT NULL AFTER `notify_mailgroup`";
	$result = mysql_query($query);
	if($result) { print "Added notify_email field to facilities table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `mob_tracker` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Mobile screen tracking - callsign set automatically' AFTER `t_tracker`;";
	$result = mysql_query($query);
	if($result) { print "Added mob_tracker field to responder table<BR />";}

	if (!table_exists("warnings")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]warnings` (
			`id` int(7) NOT NULL auto_increment,
			`title` text NOT NULL,
			`street` varchar(96) NOT NULL,
			`city` varchar(32) NOT NULL,
			`state` char(4) NOT NULL,
			`lat` double NOT NULL,
			`lng` double NOT NULL,
			`description` text NOT NULL,
			`_by` int(7) default NULL,
			`_on` datetime default NULL,
			`_from` varchar(16) default NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added warnings table<BR />";}
		}

	if (!table_exists("auto_disp_status")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]auto_disp_status` (
			`id` int(3) NOT NULL auto_increment,
			`name` varchar(128) NOT NULL,
			`status_val` int(3) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added auto_disp_status table<BR />";}
		}

	if (!table_exists("mailgroup")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mailgroup` (
			`id` int(4) NOT NULL auto_increment,
			`name` varchar(128) NOT NULL,
			`notes` text,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added mailgroup table<BR />";}
		}

	if (!table_exists("mailgroup_x")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mailgroup_x` (
			`id` int(4) NOT NULL auto_increment,
			`mailgroup` int(4) NOT NULL,
			`contacts` int(4) default '0',
			`responder` int(4) default '0',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added mailgroup_x table (associates email addresses with mailgroup)<BR />";}
		}

	if (!table_exists("personnel")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]personnel` (
			`id` int(4) NOT NULL auto_increment COMMENT 'table id',
			`surname` varchar(48) default NULL,
			`forenames` varchar(48) default NULL,
			`address` varchar(128) default NULL,
			`state` varchar(24) default NULL,
			`latitude` double default NULL,
			`longitude` double default NULL,
			`map_grid` varchar(10) default NULL,
			`date_of_birth` date default NULL,
			`gender` varchar(48) default NULL,
			`person_identifier` varchar(48) default NULL COMMENT 'linking field to responders',
			`email` varchar(48) default NULL,
			`cellphone` varchar(48) default NULL,
			`homephone` varchar(48) default NULL,
			`workphone` varchar(48) default NULL,
			`next_of_kin_name` varchar(48) default NULL,
			`next_of_kin_address` varchar(128) default NULL,
			`next_of_kin_homephone` varchar(48) default NULL,
			`next_of_kin_workphone` varchar(48) default NULL,
			`next_of_kin_cellphone` varchar(48) default NULL,
			`amateur_radio_callsign` varchar(48) default NULL,
			`person_status` varchar(48) default NULL,
			`team_name` varchar(48) default NULL,
			`person_notes` longtext COMMENT 'combined field from various data inputs',
			`person_capabilities` longtext COMMENT 'combined field from various data inputs',
			`vehicle_identifier` varchar(48) default NULL,
			`vehicle_callsign` varchar(48) default NULL,
			`vehicle_owner` varchar(48) default NULL,
			`vehicle_make` varchar(48) default NULL,
			`vehicle_model` varchar(48) default NULL,
			`vehicle_year` varchar(48) default NULL,
			`vehicle_color` varchar(48) default NULL,
			`vehicle_seats` varchar(48) default NULL,
			`vehicle_notes` longtext COMMENT 'combined field from various data inputs',
			`vehicle_capabilities` longtext COMMENT 'combined field from various data inputs',
			`valid_training` longtext COMMENT 'combined field from various data inputs',
			`_on` datetime NOT NULL COMMENT 'when updated',
			`_from` varchar(16) NOT NULL COMMENT 'IP address',
			`_by` int(7) default NULL COMMENT 'User ID',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='data from membership database' AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added personnel table<BR />";}
		}

	if (!table_exists("files")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]files` (
			`id` mediumint(5) NOT NULL AUTO_INCREMENT,
			`title` varchar(128) NOT NULL,
			`filename` varchar(512) NOT NULL,
			`orig_filename` varchar(512) NOT NULL,
			`ticket_id` mediumint(6) NOT NULL DEFAULT '0',
			`responder_id` mediumint(6) NOT NULL DEFAULT '0',
			`facility_id` mediumint(6) NOT NULL,
			`type` int(2) DEFAULT '0',
			`filetype` varchar(128) NOT NULL,
			`_by` int(7) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added files table<BR />";}
		}

	if (!table_exists("files_x")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]files_x` (
			`id` mediumint(6) NOT NULL AUTO_INCREMENT,
			`file_id` mediumint(6) NOT NULL,
			`user_id` int(4) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added files_x table (associates files with user)<BR />";}
		}

	do_caption("To Address");
	do_caption("Address About", "About Address");
	do_caption("About Address");
	do_caption("Service User");
	do_caption("Originating Facility");
	do_caption("Receiving Facility");
	do_caption("Scope");
	do_caption("Title");
	do_caption("Comments");
	do_caption("Regions");
	do_caption("Destination");
	do_caption("Destination Address");
	do_caption("Start Address");
	do_caption("On Job");
	do_caption("Responder Handle");
	do_caption("Open");
	do_caption("Tentative");
	do_caption("Accepted");
	do_caption("Resourced");
	do_caption("Completed");
	do_caption("Closed");
	do_caption("Cancelled");
	do_caption("Current Requests");
	do_caption("Weather");
	do_caption("Contact Us");
	do_caption("Telephone");
	do_caption("Email");
	do_caption("Useful Documents");
	do_caption("Import");
	do_caption("New Request");
	do_caption("Export Requests to CSV");
	do_caption("Show Closed");
	do_caption("Hide Closed");
	do_caption("Portal");

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `portal_user` INT( 4 ) NULL DEFAULT NULL AFTER `org` ;";
	$result = mysql_query($query);
	if($result) { print "Added portal_user field to ticket table<BR />";}

	do_insert_day_colors('sev_background', 'EFEFEF');
	do_insert_night_colors('sev_background', 'EFEFEF');
	do_insert_day_colors('sev_text', '000000');
	do_insert_night_colors('sev_text', '000000');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `notify_mailgroup` INT( 4 ) NULL DEFAULT NULL AFTER `opacity`";
	$result = mysql_query($query);
	if($result) { print "Added notify_mailgroup field to in_types (incident types) table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `notify_email` VARCHAR( 256 ) NULL DEFAULT NULL AFTER `notify_mailgroup`";
	$result = mysql_query($query);
	if($result) { print "Added notify_email field to in_types (incident types) table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `postcode` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `city`";
	$result = mysql_query($query);
	if($result) { print "Added postcode field to requests table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `pickup` VARCHAR( 12 ) NULL DEFAULT NULL AFTER `to_address`";
	$result = mysql_query($query);
	if($result) { print "Added pickup field to requests table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `arrival` VARCHAR( 12 ) NULL DEFAULT NULL AFTER `pickup`";
	$result = mysql_query($query);
	if($result) { print "Added arrival field to requests table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_shortsumm` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_summ`";
	$result = mysql_query($query);
	if($result) { print "Added app_shortsum field to replacetext table (extra option in replacement text to append short summary)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_desc` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_shortsumm`";
	$result = mysql_query($query);
	if($result) { print "Added app_desc field to replacetext table (extra option in replacement text to append description)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `xastir_tracker` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'APRS tracking using XASTIR' AFTER `mob_tracker`;";
	$result = mysql_query($query);
	if($result) { print "Added xastir_rracker field to responder table to support tracking via XASTIR APRS tracking server<BR />";}

	update_msg_settings ('smsg_use_server','0');
	do_setting ('xastir_server','localhost');
	do_setting ('xastir_db','');
	do_setting ('xastir_dbuser','');
	do_setting ('xastir_dbpass','');
	do_setting ('restrict_units','0');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ADD `email` VARCHAR( 128 ) NULL DEFAULT NULL AFTER `contact`";
	$result = mysql_query($query);
	if($result) { print "Added email field to requests table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]messages` ADD `server_number` TINYINT( 2 ) NULL DEFAULT NULL AFTER `message_id`;";
	$result = mysql_query($query);
	if($result) { print "Added server_number field to messages table - sets which server message was sent from to ensure responses are retrieved from the correct server<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]patient` CHANGE `insurance_id` `insurance_id` INT( 3 ) NULL DEFAULT NULL ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]places`
			ADD `apply_to` 	ENUM( 'city', 'bldg' ) NOT NULL DEFAULT 'city' AFTER `name` ,
			ADD `street` 	VARCHAR( 96 ) NULL DEFAULT NULL AFTER `apply_to` ,
			ADD `city` 		VARCHAR( 32 ) NULL DEFAULT NULL AFTER `street` ,
			ADD `state` 	VARCHAR( 4 ) NULL DEFAULT NULL AFTER `city` ,
			ADD `information` VARCHAR( 1024 ) NULL DEFAULT NULL AFTER `state` ";
	$result = mysql_query($query);
	if($result) { print "Added apply_to, street, city, state and information to places table to support buildings functionality<BR />";}

	do_setting ('use_osmap','0');
	do_setting ('openspace_api','0');

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";	//	Check if Opening Hours has been converted to new format
	$result = mysql_query($query) ;
	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			$opening_hours = $row['opening_hours'];
			if((!checkBase64Encoded($opening_hours)) || ($opening_hours == "") || (is_null($opening_hours ))) {	//	if Opening Hours field is not valid base64encoded then set it to default value
				$query2 = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET `opening_hours`= 'YTo3OntpOjA7YTozOntpOjA7czoyOiJvbiI7aToxO3M6NToiMDA6MDAiO2k6MjtzOjU6IjIzOjU5Ijt9aToxO2E6Mzp7aTowO3M6Mjoib24iO2k6MTtzOjU6IjAwOjAwIjtpOjI7czo1OiIyMzo1OSI7fWk6MjthOjM6e2k6MDtzOjI6Im9uIjtpOjE7czo1OiIwMDowMCI7aToyO3M6NToiMjM6NTkiO31pOjM7YTozOntpOjA7czoyOiJvbiI7aToxO3M6NToiMDA6MDAiO2k6MjtzOjU6IjIzOjU5Ijt9aTo0O2E6Mzp7aTowO3M6Mjoib24iO2k6MTtzOjU6IjAwOjAwIjtpOjI7czo1OiIyMzo1OSI7fWk6NTthOjM6e2k6MDtzOjI6Im9uIjtpOjE7czo1OiIwMDowMCI7aToyO3M6NToiMjM6NTkiO31pOjY7YTozOntpOjA7czoyOiJvbiI7aToxO3M6NToiMDA6MDAiO2k6MjtzOjU6IjIzOjU5Ijt9fQ'";
				$result2 = mysql_query($query2) ;
				if($result2) { print "Revised way facility opening hours are stored from free text to encoded array to support auto facility status<BR />";}
				}
			}
		}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `category` = 'Basemap';";
	$result = mysql_query($query);
	$theRows = mysql_num_rows($result);
	if($theRows == 0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]mmarkup_cats` (`category`, `_by`, `_from`)	VALUES ('Basemap', '0', 'Install Routine');";
		$result = mysql_query($query);
		if($result) { print "Insert basemap category to mmarkup_cats table (Map Markup Categories)<BR />";}
		} else if($theRows > 1) {
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `category` = 'Basemap'";
		$result = mysql_query($query);
		if($result) { print "Deleted old format basemap category from mmarkup_cats table (Map Markup Categories)<BR />";}
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]mmarkup_cats` (`category`, `_by`, `_from`)	VALUES ('Basemap', '0', 'Install Routine');";
		$result = mysql_query($query);
		if($result) { print "Insert basemap category to mmarkup_cats table (Map Markup Categories)<BR />";}
		}

	if (!table_exists("access_requests")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]access_requests` (
			`id` int(6) NOT NULL auto_increment,
			`name` varchar(64) NOT NULL,
			`email` varchar(128) NOT NULL,
			`phone` varchar(24) NOT NULL,
			`reason` longtext NOT NULL,
			`sec_code` varchar(24) NOT NULL,
			`date` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added access_requests table to support requests for access to tickets system<BR />";}
		}

	if (!table_exists("tips")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]tips` (
			`id` int(7) NOT NULL auto_increment,
			`title` varchar(24) collate utf8_unicode_ci NOT NULL,
			`tip` text collate utf8_unicode_ci NOT NULL,
			`_by` int(7) NOT NULL default '0',
			`_from` varchar(16) character set latin1 default NULL,
			`_on` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
		if($result) { print "Added tips table to support configurable hover over tips to list entries<BR />";}
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` CHANGE `code` `code` SMALLINT( 7 ) NOT NULL DEFAULT '0'";
	$result = mysql_query($query);

	do_setting ('access_requests','0');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]warnings` ADD `loc_type` SMALLINT(4) NOT NULL DEFAULT 4 AFTER `lng`;";
	$result = mysql_query($query);
	if($result) { print "Added loc_type field to warnings table<BR />";}

	if (!table_exists("major_incidents")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]major_incidents` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(64) NOT NULL,
			`description` longtext NOT NULL,
			`type` int(4) NOT NULL,
			`gold` int(4) NOT NULL,
			`silver` int(4) NOT NULL,
			`bronze` int(4) NOT NULL,
			`boundary` int(4) NOT NULL,
			`inc_startime` datetime NOT NULL,
			`inc_endtime` datetime NOT NULL,
			`incident_notes` longtext,
			`_by` int(11) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Major Incidents table<BR />";}
		}

	if (!table_exists("mi_types")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mi_types` (
			`id` int(4) NOT NULL auto_increment,
			`name` varchar(64) NOT NULL,
			`bg_color` varchar(12) NOT NULL DEFAULT 'transparent',
			`color` varchar(12) NOT NULL DEFAULT '#000000',
			`_by` int(11) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
		$result = mysql_query($query);
		if($result) { print "Added mi_types (Major Incident types) table<BR />";}
		}

	if (!table_exists("mi_x")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mi_x` (
			`id` int(6) NOT NULL auto_increment,
			`mi_id` int(6) NOT NULL,
			`ticket_id` int(6) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added mi_x (assigns ticket to Major Incident) table<BR />";}
		}

	$query = "INSERT INTO `$GLOBALS[mysql_prefix]mi_types` (
		`id`, `name`, `bg_color`, `color`, `_by`, `_on`, `_from`) VALUES (
		1, 'Environmental', 'transparent', '#000000', 1, '2015-02-27 11:50:34', '::1');";
	$result = mysql_query($query);
	if($result) { print "Inserted mi_tyoes example entry<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]files` ADD `mi_id` SMALLINT(4) NOT NULL DEFAULT 0 AFTER `facility_id`;";
	$result = mysql_query($query);
	if($result) { print "Added mi_id to files table to associate file with major incident<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]files` CHANGE `ticket_id` `ticket_id` MEDIUMINT( 6 ) NOT NULL DEFAULT '0',
		CHANGE `responder_id` `responder_id` MEDIUMINT( 6 ) NOT NULL DEFAULT '0',
		CHANGE `facility_id` `facility_id` MEDIUMINT( 6 ) NOT NULL DEFAULT '0',
		CHANGE `mi_id` `mi_id` MEDIUMINT( 6 ) NOT NULL DEFAULT '0';";
	$result = mysql_query($query);

	do_setting ('os_watch','0/0/0');        // minutes priority/standard/routine - initially off, reasonable is 5/15/60

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `watch` INT(1) NOT NULL DEFAULT '0' COMMENT 'if 1, watch this unit' AFTER `dispatch`;";
	$result = mysql_query($query);
	if($result) { print "Added watch field to un_status (Unit Status) to support on-scene watch functionality<BR />";}

	if (!table_exists("states_translator")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]states_translator` (
			`id` int(4) NOT NULL auto_increment,
			`name` varchar(64) NOT NULL,
			`code` varchar(4) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
		$result = mysql_query($query);
		if($result) { print "Added States translator table - associates State name with abbreviation (where full name is used by geo-location provider<BR />";}

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]states_translator` (`id`, `name`, `code`) VALUES
			(1, 'Alabama', 'AL'),
			(2, 'Alaska', 'AK'),
			(3, 'Arizona', 'AZ'),
			(4, 'Arkansas', 'AR'),
			(5, 'California', 'CA'),
			(6, 'Colorado', 'CO'),
			(7, 'Connecticut', 'CT'),
			(8, 'Delaware', 'DE'),
			(9, 'Florida', 'FL'),
			(10, 'Georgia', 'GA'),
			(11, 'Hawaii', 'HI'),
			(12, 'Idaho', 'ID'),
			(13, 'Illinois', 'IL'),
			(14, 'Indiana', 'IN'),
			(15, 'Iowa', 'IA'),
			(16, 'Kansas', 'KS'),
			(17, 'Kentucky', 'KY'),
			(18, 'Louisiana', 'LA'),
			(19, 'Maine', 'ME'),
			(20, 'Maryland', 'MD'),
			(21, 'Massachusetts', 'MA'),
			(22, 'Michigan', 'MI'),
			(23, 'Minnesota', 'MN'),
			(24, 'Mississippi', 'MS'),
			(25, 'Missouri', 'MO'),
			(26, 'Montana', 'MT'),
			(27, 'Nebraska', 'NE'),
			(28, 'Nevada', 'NV'),
			(29, 'New Hampshire', 'NH'),
			(30, 'New Jersey', 'NJ'),
			(31, 'New Mexico', 'NM'),
			(32, 'New York', 'NY'),
			(33, 'North Carolina', 'NC'),
			(34, 'North Dakota', 'ND'),
			(35, 'Ohio', 'OH'),
			(36, 'Oklahoma', 'OK'),
			(37, 'Oregon', 'OR'),
			(38, 'Pennsylvania', 'PA'),
			(39, 'Rhode Island', 'RI'),
			(40, 'South Carolina', 'SC'),
			(41, 'South Dakota', 'SD'),
			(42, 'Tennessee', 'TN'),
			(43, 'Texas', 'TX'),
			(44, 'Utah', 'UT'),
			(45, 'Vermont', 'VT'),
			(46, 'Virginia', 'VA'),
			(47, 'Washington', 'WA'),
			(48, 'West Virginia', 'WV'),
			(49, 'Wisconsin', 'WI'),
			(50, 'Wyoming', 'WY'),
			(51, 'England', 'UK');";
		$result = mysql_query($query);
		if($result) { print "Inserted States Translator entries for US States<BR />";}

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]states_translator` (`id`, `name`, `code`) VALUES (52, 'District of Columbia', 'DC');";
		$result = mysql_query($query);
		if($result) { print "Inserted States Translator for District of Columbia<BR />";}
	}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `watch` INT(2) NOT NULL DEFAULT 0 AFTER `set_severity`;";
	$result = mysql_query($query);
	if($result) { print "Added watch field to in_types (Incident Types) table to support on-scene watch functionality<BR />";}

	do_setting ('add_uselocation','0');
	do_caption("Gold Command", "Incident Command");
	do_caption("Silver Command", "Region Command");
	do_caption("Bronze Command", "On-scene Command");

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents` ADD `gold_loc` INT(6) NOT NULL DEFAULT '0' AFTER `bronze`,
			ADD `silver_loc` INT(6) NOT NULL DEFAULT '0' AFTER `gold_loc`,
			ADD `bronze_loc` INT(6) NOT NULL DEFAULT '0' AFTER `silver_loc`;";
	$result = mysql_query($query);
	if($result) { print "Added gold_loc, silver_loc and bronze_loc to Major Incidents table<BR />";}

	do_setting ('geocoding_provider','0');
	do_setting ('bing_api_key','');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `community` VARCHAR(48) NULL DEFAULT NULL AFTER `apartment`;";
	$result = mysql_query($query);
	if($result) { print "Added community field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `post_code` VARCHAR(48) NULL DEFAULT NULL AFTER `city`;";
	$result = mysql_query($query);
	if($result) { print "Added post_code field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `reference` VARCHAR(48) NULL DEFAULT NULL AFTER `lng`;";
	$result = mysql_query($query);
	if($result) { print "Added reference field to constituents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents` ADD `gold_street` VARCHAR(64) NULL AFTER `gold_loc`,
			ADD `gold_city` VARCHAR(48) NULL AFTER `gold_street`,
			ADD `gold_state` VARCHAR(4) NULL AFTER `gold_city`,
			ADD `gold_lat` VARCHAR(16) NULL AFTER `gold_state`,
			ADD `gold_lng` VARCHAR(16) NULL AFTER `gold_lat`;";
	$result = mysql_query($query);
	if($result) { print "Added gold_street, gold_city, gold_state, gold_lat and gold_lng fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents` ADD `silver_street` VARCHAR(64) NULL AFTER `silver_loc`,
			ADD `silver_city` VARCHAR(48) NULL AFTER `silver_street`,
			ADD `silver_state` VARCHAR(4) NULL AFTER `silver_city`,
			ADD `silver_lat` VARCHAR(16) NULL AFTER `silver_state`,
			ADD `silver_lng` VARCHAR(16) NULL AFTER `silver_lat`;";
	$result = mysql_query($query);
	if($result) { print "Added silver_street, silver_city, silver_state, silver_lat and silver_lng fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents` ADD `bronze_street` VARCHAR(64) NULL AFTER `bronze_loc`,
			ADD `bronze_city` VARCHAR(48) NULL AFTER `bronze_street`,
			ADD `bronze_state` VARCHAR(4) NULL AFTER `bronze_city`,
			ADD `bronze_lat` VARCHAR(16) NULL AFTER `bronze_state`,
			ADD `bronze_lng` VARCHAR(16) NULL AFTER `bronze_lat`;";
	$result = mysql_query($query);
	if($result) { print "Added bronze_street, bronze_city, bronze_state, bronze_lat and bronze_lng fields to Major Incidents table<BR />";}

	do_setting ('addr_source','0');

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type` = 1 AND `al_status` <> 0;";
	$result = mysql_query($query);
	if($result) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$id = $row['resource_id'];
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $id . " AND (`status` = 0 OR `status` = 1)";
			$result2 = mysql_query($query2);

			if($result2) {
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {
					$query3  = "UPDATE `$GLOBALS[mysql_prefix]allocates` SET `al_status` = 0 WHERE `type` = 1 AND `resource_id` = " . $row2['id'];
					$result3 = mysql_query($query3);
//					print "Updated status for (closed or reserved) ticket " . $row2['id'] . " in allocates table<BR />";
					}
				}
			$query4 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $id . " AND `status` = 2";
			$result4 = mysql_query($query4);
			if($result4) {
				while ($row4 = stripslashes_deep(mysql_fetch_assoc($result4))) {
					$query5  = "UPDATE `$GLOBALS[mysql_prefix]allocates` SET `al_status` = 1 WHERE `type` = 1 AND `resource_id` = " . $row4['id'];
					$result5 = mysql_query($query5);
//					print "Updated status for (open or scheduled) ticket " . $row4['id'] . " in allocates table<BR />";
					}
				}
			}
		}

	do_setting ('default_map_layer','0');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` CHANGE `type` `type` SMALLINT( 5 ) NOT NULL DEFAULT '0'";
	$result = mysql_query($query);

	do_setting ('twitter_consumerkey','');        // needs to be setup on twitter account
	do_setting ('twitter_consumersecret','');        // needs to be setup on twitter account
	do_setting ('twitter_accesstoken','');        // needs to be setup on twitter account
	do_setting ('twitter_accesstokensecret','');	//	needs to be setup on twitter account
	do_setting('unit_can_edit','0');				//	to allow Units to edit Ticket or add actions and notes.

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents` ADD `level4_loc` INT(6) NOT NULL DEFAULT '0' AFTER `bronze_lng`,
			ADD `level5_loc` INT(6) NOT NULL DEFAULT '0' AFTER `level4_loc`,
			ADD `level6_loc` INT(6) NOT NULL DEFAULT '0' AFTER `level5_loc`;";
	$result = mysql_query($query);
	if($result) { print "Added level4_loc, level5_loc and level6_loc fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents`
			ADD `level4` INT(4) NOT NULL AFTER `bronze`,
			ADD `level5` INT(4) NOT NULL AFTER `level4`,
			ADD `level6` INT(4) NOT NULL AFTER `level5`;";
	$result = mysql_query($query);
	if($result) { print "Added level4, level5, level6, level4_street, level4_city, level4_state, level4_lat and level4_lng fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents`
			ADD `level4_street` VARCHAR(64) NULL AFTER `level4_loc`,
			ADD `level4_city` VARCHAR(48) NULL AFTER `level4_street`,
			ADD `level4_state` VARCHAR(4) NULL AFTER `level4_city`,
			ADD `level4_lat` VARCHAR(16) NULL AFTER `level4_state`,
			ADD `level4_lng` VARCHAR(16) NULL AFTER `level4_lat`;";
	$result = mysql_query($query);
	if($result) { print "Added level4_street, level4_city, level4_state, level4_lat and level4_lng fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents`
			ADD `level5_street` VARCHAR(64) NULL AFTER `level5_loc`,
			ADD `level5_city` VARCHAR(48) NULL AFTER `level5_street`,
			ADD `level5_state` VARCHAR(4) NULL AFTER `level5_city`,
			ADD `level5_lat` VARCHAR(16) NULL AFTER `level5_state`,
			ADD `level5_lng` VARCHAR(16) NULL AFTER `level5_lat`;";
	$result = mysql_query($query);
	if($result) { print "Added level5_street, level5_city, level5_state, level5_lat and level5_lng fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents`
			ADD `level6_street` VARCHAR(64) NULL AFTER `level6_loc`,
			ADD `level6_city` VARCHAR(48) NULL AFTER `level6_street`,
			ADD `level6_state` VARCHAR(4) NULL AFTER `level6_city`,
			ADD `level6_lat` VARCHAR(16) NULL AFTER `level6_state`,
			ADD `level6_lng` VARCHAR(16) NULL AFTER `level6_lat`;";
	$result = mysql_query($query);
	if($result) { print "Added level6_street, level6_city, level6_state, level6_lat and level6_lng fields to Major Incidents table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `at_facility` INT(6) NOT NULL DEFAULT '0' AFTER `type`;";
	$result = mysql_query($query);
	if($result) { print "Added at_facility field to responder table<BR />";}

	if (!table_exists("ajax_log")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]ajax_log` (
			`id` int(6) NOT NULL auto_increment,
			`info` text NOT NULL,
			`_when` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added ajax_log table<BR />";}
		}
	cleanup_captions();

	do_setting ('mob_show_cleared','1');	// sets default show on mobile screen to include cleared assignments where the Ticket is still open

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities`
			ADD `notify_when` INT(1) NOT NULL DEFAULT '1' COMMENT 'Sets when to notify facility, 1,2 or 3 for all, open or close' AFTER `notify_email`";
	$result = mysql_query($query);
	if($result) { print "Added notify_when field to facilities table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types`
			ADD `notify_when` INT(1) NOT NULL DEFAULT '1' COMMENT 'When Notifies are sent, 1,2 or 3 for all, open or close' AFTER `notify_email`";
	$result = mysql_query($query);
	if($result) { print "Added notify_when field to in_types (Incident Types) table<BR />";}

	do_setting ('socketserver_url','');	// sets URL for Websocket server_number - default empty is local server. Set to 127.0.0.1 by runtime code
	do_setting ('socketserver_port','');	// sets port for Websocket server_number - default empty is local server. Set to 1337 by runtime code

	do_msg_setting ('mototrbo_cai_id','12');
	do_msg_setting ('smsbroadcast_username','');
	do_msg_setting ('smsbroadcast_password','');
	do_msg_setting ('smsbroadcast_api_url','https://api.smsbroadcast.com.au/api-adv.php');
	do_msg_setting ('mototrbo_python_path','');
	do_msg_setting ('mototrbopy_path','');
	do_msg_setting ('smsbroadcast_maxsplit','2');

	$query_check = "select count(*) as cnt from information_schema.columns where table_schema = database() and column_name = 'followmee_tracker' and table_name = '$GLOBALS[mysql_prefix]responder'";
	$result_check = mysql_query($query_check);
	$row = mysql_fetch_assoc($result_check);
	if($row['cnt'] == 0) {
		$query = "ALTER TABLE $GLOBALS[mysql_prefix]responder ADD COLUMN `followmee_tracker` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'Tracking using FollowMee'";
		$result = mysql_query($query);
		if($result) { print "Added followmee_tracker field to responder table<BR />";}
		}

	do_setting ('custom_situation','1/1');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `facility_id` INT( 7 ) NOT NULL DEFAULT '0' COMMENT 'For level = facility' AFTER `responder_id` ";		// 04/07/16
	$result = mysql_query($query);
	if($result) { print "Added facility_id field to user table - supports 'Facility' user type<BR />";}

	if (!table_exists("facnotes")) {
		$query = "CREATE TABLE `$GLOBALS[mysql_prefix]facnotes` (
			`id` int(10) NOT NULL auto_increment,
			`ticket_id` int(10) NOT NULL,
			`origin` varchar(64) DEFAULT NULL,
			`destination` varchar(64) DEFAULT NULL,
			`type` varchar(64) NOT NULL,
			`notes` longtext,
			`_by` int(7) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added facnotes (Facility Notes) table<BR />";}
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facnotes` ADD `patient` VARCHAR( 64 ) NOT NULL AFTER `type`";
	$result = mysql_query($query);
	if($result) { print "Added patient field to facnotes table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facnotes` ADD `ETA` VARCHAR( 16 ) NOT NULL AFTER `patient`";
	$result = mysql_query($query);
	if($result) { print "Added ETA field to facnotes table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facnotes` CHANGE `type` `type` INT(7) NOT NULL";
	$result = mysql_query($query);

	if (!table_exists("fac_case_cat")) {
		$query = "CREATE TABLE `$GLOBALS[mysql_prefix]fac_case_cat` (
			`id` int(6) NOT NULL auto_increment,
			`category` varchar(64) NOT NULL,
			`description` longtext,
			`color` varchar(7) DEFAULT NULL,
			`bgcolor` varchar(7) DEFAULT NULL,
			`facility` int(7) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added fac_case_cat table (Supports Facility case types)<BR />";}
		}

	if (!table_exists("patient_x")) {
		$query = "CREATE TABLE `$GLOBALS[mysql_prefix]patient_x` (
			`id` int(7) NOT NULL auto_increment,
			`patient_id` int(7) NOT NULL,
			`assign_id` int(7) NOT NULL,
			`_by` int(7) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added patient_x table (Supports Multiple patients per assignment)<BR />";}
		}

	do_setting ('facboard_hide_patient','0');			// 04/20/16	Allows hiding of Patient Name from Facility Board
	do_setting ('debug','0');			// 04/22/16	For debug purposes
	do_setting ('log_days','3');			// 04/22/16	For debug purposes
	update_setting ('reverse_geo','1');
	do_setting ('bounds','0.0,0.0,0.0,0.0');

	cleanup_states_translator();

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `name` VARCHAR( 48 ) NOT NULL AFTER `id`";
	$result = mysql_query($query);
	if($result) { print "Added name field to std_msgs table (Supports name field for listing in select controls rather than abbreviated message)<BR />";}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]std_msgs`";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if($row['name'] == "") {
			$newname = trim(substr($row['message'], 0));
			$query2 = "UPDATE `$GLOBALS[mysql_prefix]std_msgs` SET `name` = '" . $newname . "' WHERE `id` = " . $row['id'] . " LIMIT 1";
			$result2 = mysql_query($query2);
			if($result2) { print "Added default entry for name field in std_msgs table by using abbreviated message<BR />";}
			}
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_phone` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_desc`";
	$result = mysql_query($query);
	if($result) { print "Added app_phone field to replacetext table (append Phone)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_street` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_phone`";
	$result = mysql_query($query);
	$result = mysql_query($query);
	if($result) { print "Added app_street field to replacetext table (append Street address)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_city` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_street`";
	$result = mysql_query($query);
	if($result) { print "Added app_city field to replacetext table (append City)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_toaddress` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_city`";
	$result = mysql_query($query);
	if($result) { print "Added app_toaddress field to replacetext table (append To Address)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_dispnotes` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_toaddress`";
	$result = mysql_query($query);
	if($result) { print "Added app_dispnotes field to replacetext table (append Dispatch Notes)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_nature` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_dispnotes``";
	$result = mysql_query($query);
	if($result) { print "Added app_nature field to replacetext table (append Incident Nature)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_priority` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_nature`";
	$result = mysql_query($query);
	if($result) { print "Added app_priority field to replacetext table (append Incident Priority)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]replacetext` ADD `app_warnloc` ENUM( 'Yes','No' ) NOT NULL DEFAULT 'No' AFTER `app_priority`";
	$result = mysql_query($query);
	if($result) { print "Added app_warnloc field to replacetext table (append Location Warnings)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `cellphone` VARCHAR(128) NULL DEFAULT NULL AFTER `smsg_id`";
	$result = mysql_query($query);
	if($result) { print "Added cellphone field to respoonder table (to support txtlocal as smsg provider)<BR />";}

	do_msg_setting ('txtlocal_icserver','http://api.txtlocal.com/get_messages/');
	do_msg_setting ('txtlocal_hash','');
	do_msg_setting ('txtlocal_username','');
	do_msg_setting ('txtlocal_ogserver','http://api.txtlocal.com/send/');
	do_msg_setting ('txtlocal_inserver','http://api.txtlocal.com/get_inboxes/');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `excl_from_reset` ENUM('n','y') NOT NULL DEFAULT 'n' AFTER `hide`";
	$result = mysql_query($query);
	if($result) { print "Added excl_from_reset field to un_status (Unit Status) table (Excludes status from Unit Status reset feature)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_status` ADD `status_available` INT(2) NOT NULL DEFAULT '0' AFTER `group`";
	$result = mysql_query($query);
	if($result) { print "Added status_available field to fac_status (Facility Status) table (Supports Automatic Facility status function)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_status` ADD `status_unavailable` INT(2) NOT NULL DEFAULT '0' AFTER `status_available`";
	$result = mysql_query($query);
	if($result) { print "Added status_unavailable field to fac_status (Facility Status) table (Supports Automatic Facility status function)<BR />";}

	do_setting ('facility_auto_status','0');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]major_incidents` ADD `mi_status` ENUM('Open','Closed') NOT NULL DEFAULT 'Open' AFTER `type`";
	$result = mysql_query($query);
	if($result) { print "Added mi_status field to major_incidents table<BR />";}

	if (!table_exists("replacetext_order")) {
		$query = "CREATE TABLE `$GLOBALS[mysql_prefix]replacetext_order` (
			`id` int(11) NOT NULL auto_increment,
			`displayorder` int(2) NOT NULL,
			`info_name` varchar(24) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		$result = mysql_query($query);
		if($result) { print "Added replacetext_order table (supports order of replacement text in messages)<BR />";}
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]replacetext_order` (`id`, `displayorder`, `info_name`) VALUES
			(1, 1, 'add_ticket'),
			(2, 2, 'add_user'),
			(3, 3, 'add_user_unit'),
			(4, 4, 'add_time'),
			(5, 5, 'add_date'),
			(6, 6, 'app_summ'),
			(7, 7, 'app_shortsumm'),
			(8, 8, 'app_desc'),
			(9, 9, 'app_phone'),
			(10, 10, 'app_street'),
			(11, 11, 'app_city'),
			(12, 12, 'app_toaddress'),
			(13, 13, 'app_dispnotes'),
			(14, 14, 'app_nature'),
			(15, 15, 'app_priority'),
			(16, 16, 'app_warnings');";
		$result = mysql_query($query);
		if($result) { print "Inserted default order data for replacetext_order table<BR />";}
		}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `groupby` VARCHAR(64) NULL DEFAULT 'Messages' AFTER `message`";
	$result = mysql_query($query);
	if($result) { print "Added groupby field to std_msgs table<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `email` INT(2) NOT NULL DEFAULT '1' AFTER `groupby`";
	$result = mysql_query($query);
	if($result) { print "Added email field to std_msgs table (supports different messages per message type)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `smsresponder` INT(2) NOT NULL DEFAULT '0' AFTER `email`";
	$result = mysql_query($query);
	if($result) { print "Added smsresponder field to std_msgs table (supports different messages per message type)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `txtlocal` INT(2) NOT NULL DEFAULT '0' AFTER `smsresponder`";
	$result = mysql_query($query);
	if($result) { print "Added txtlocal field to std_msgs table (supports different messages per message type)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `mototrbo` INT(2) NOT NULL DEFAULT '0' AFTER `txtlocal`";
	$result = mysql_query($query);
	if($result) { print "Added mototrbo field to std_msgs table (supports different messages per message type)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]std_msgs` ADD `smsbroadcast` INT(2) NOT NULL DEFAULT '0' AFTER `mototrbo`";
	$result = mysql_query($query);
	if($result) { print "Added smsbroadcast field to std_msgs table (supports different messages per message type)<BR />";}

	do_msg_setting ('default_sms','0');
	do_msg_setting ('append_timestamp','0');

	do_setting ('httpuser','');
	do_setting ('httppwd','');
	do_setting ('timezone', "America/New_York");
	do_setting ('followmee_username', '');
	do_setting ('followmee_key', '');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `traccar` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'APRS tracking using TRACCAR' AFTER `xastir_tracker`;";
	$result = mysql_query($query);
	if($result) { print "Added traccar field to responder table (supports TRACCAR APRS tracking)<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `javaprssrvr` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'APRS tracking using JAVAPRSSRVR' AFTER `traccar`;";
	$result = mysql_query($query);
	if($result) { print "Added javaprssrvr field to responder table (supports JAVAPRSSRVR APRS tracking)<BR />";}

	do_setting ('traccar_server','localhost');
	do_setting ('traccar_db','');
	do_setting ('traccar_dbuser','');
	do_setting ('traccar_dbpass','');

	do_setting ('javaprssrvr_server','localhost');
	do_setting ('javaprssrvr_db','');
	do_setting ('javaprssrvr_dbuser','');
	do_setting ('javaprssrvr_dbpass','');

	if (!table_exists("responder_rota")) {		//	Rota / Scheduling table for future use
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]responder_rota` (
			`id` int(8) NOT NULL auto_increment,
			`person_id` int(4) DEFAULT NULL,
			`resp_id` int(4) NOT NULL,
			`starttime` datetime DEFAULT NULL,
			`endtime` datetime DEFAULT NULL,
			`rota_status` int(2) DEFAULT NULL,
			`recurring` int(2) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added responder_rota table (to support automatic resource allocation by rota)<BR />";}
		}

	do_setting ('responder_list_sort','1,1');
	do_setting ('facility_list_sort','1,1');
	do_setting ('listheader_height','25');
	do_setting ('notify_assigns','1');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `member` INT(11) DEFAULT NULL COMMENT 'Supports Integrated Membership Database' AFTER `name_mi`;";
	$result = mysql_query($query);
	if($result) { print "Added member field to user table to support integrated Tickets MDB<BR />";}

	if (!table_exists("allocations")) {		//	MDB Skills allocations table
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]allocations` (
			`id` bigint(4) NOT NULL auto_increment,
			`member_id` int(2) NOT NULL DEFAULT '0',
			`skill_type` int(2) NOT NULL DEFAULT '0',
			`skill_id` int(2) NOT NULL DEFAULT '0',
			`completed` date DEFAULT NULL,
			`refresh_due` date DEFAULT NULL,
			`frequency` enum('Daily','Weekly','Permanent') DEFAULT NULL,
			`start` datetime DEFAULT NULL,
			`end` datetime DEFAULT NULL,
			`days` varchar(256) DEFAULT NULL,
			`_on` date NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added allocations table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("capability_types")) {		//	MDB Capability Types
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]capability_types` (
			`id` bigint(4) NOT NULL auto_increment,
			`name` varchar(48) DEFAULT NULL,
			`description` longtext,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added capability_types table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("clothing_types")) {		//	MDB Clothing types
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]clothing_types` (
			`id` bigint(8) NOT NULL auto_increment,
			`clothing_item` varchar(48) DEFAULT NULL,
			`description` longtext,
			`size` varchar(48) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added clothing_types table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("defined_fields")) {		//	MDB Defined fields - table that sets type and label of revisable fields
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]defined_fields` (
			`id` tinyint(2) NOT NULL auto_increment,
			`field_id` int(4) NOT NULL,
			`label` varchar(32) NOT NULL,
			`size` int(4) NOT NULL DEFAULT '48',
			`fieldset` int(2) DEFAULT NULL,
			`_noedit` int(2) NOT NULL DEFAULT '0',
			`_by` int(4) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(24) NOT NULL,
			`browser` varchar(40) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added defined_fields table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("equipment_types")) {		//	MDB Equipment Types
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]equipment_types` (
			`id` bigint(8) NOT NULL auto_increment,
			`equipment_name` varchar(48) DEFAULT NULL,
			`description` longtext,
			`spec` varchar(48) DEFAULT NULL,
			`serial` varchar(48) DEFAULT NULL,
			`condition` enum('New','Good','Serviceable','Unusable','For Destruction') NOT NULL DEFAULT 'Good',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added equipment_types table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("events")) {		//	MDB Events
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]events` (
			`id` int(11) NOT NULL auto_increment,
			`event_name` varchar(128) DEFAULT NULL,
			`description` longtext,
			`event_type` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added events table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("event_types")) {		//	MDB Event Types
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]event_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(64) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added event_types table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("fieldsets")) {		//	Customisable fieldsets
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fieldsets` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(48) NOT NULL,
			`label` varchar(48) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added fieldsets table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("mdb_files")) {		//	Table for MDB file store
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mdb_files` (
			`id` smallint(3) NOT NULL auto_increment,
			`member_id` smallint(3) NOT NULL,
			`name` varchar(64) NOT NULL,
			`shortname` varchar(32) NOT NULL,
			`description` varchar(24) NOT NULL,
			`filesize` varchar(12) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added mdb_files table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("member")) {		//	MDB Main Member Table
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]member` (
			`id` int(8) NOT NULL auto_increment,
			`field1` varchar(28) DEFAULT NULL,
			`field2` varchar(28) DEFAULT NULL,
			`field3` int(4) NOT NULL DEFAULT '0',
			`field4` varchar(16) DEFAULT NULL,
			`field5` varchar(64) DEFAULT NULL,
			`field6` varchar(48) DEFAULT NULL,
			`field7` bigint(4) NOT NULL,
			`field8` enum('Yes','No') NOT NULL DEFAULT 'Yes',
			`field9` varchar(28) DEFAULT NULL,
			`field10` varchar(28) DEFAULT NULL,
			`field11` varchar(12) DEFAULT NULL,
			`field12` double DEFAULT NULL,
			`field13` double DEFAULT NULL,
			`field14` longtext,
			`field15` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field16` datetime DEFAULT NULL,
			`field17` datetime DEFAULT NULL,
			`field18` datetime DEFAULT NULL,
			`field19` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field20` longtext,
			`field21` int(4) NOT NULL DEFAULT '0',
			`field22` varchar(1024) DEFAULT NULL,
			`field23` varchar(1024) DEFAULT NULL,
			`field24` varchar(1024) DEFAULT NULL,
			`field25` varchar(1024) DEFAULT NULL,
			`field26` varchar(1024) DEFAULT NULL,
			`field27` varchar(1024) DEFAULT NULL,
			`field28` varchar(1024) DEFAULT NULL,
			`field29` varchar(1024) DEFAULT NULL,
			`field30` varchar(1024) DEFAULT NULL,
			`field31` varchar(1024) DEFAULT NULL,
			`field32` varchar(1024) DEFAULT NULL,
			`field33` varchar(1024) DEFAULT NULL,
			`field34` varchar(1024) DEFAULT NULL,
			`field35` varchar(1024) DEFAULT NULL,
			`field36` varchar(1024) DEFAULT NULL,
			`field37` varchar(1024) DEFAULT NULL,
			`field38` varchar(1024) DEFAULT NULL,
			`field39` varchar(1024) DEFAULT NULL,
			`field40` varchar(1024) DEFAULT NULL,
			`field41` varchar(1024) DEFAULT NULL,
			`field42` varchar(1024) DEFAULT NULL,
			`field43` varchar(1024) DEFAULT NULL,
			`field44` varchar(1024) DEFAULT NULL,
			`field45` varchar(1024) DEFAULT NULL,
			`field46` enum('Yes','No') DEFAULT 'No',
			`field47` enum('Yes','No') DEFAULT 'No',
			`field48` enum('Yes','No') DEFAULT 'No',
			`field49` enum('Yes','No') DEFAULT 'No',
			`field50` enum('Yes','No') DEFAULT NULL,
			`field51` enum('Yes','No') DEFAULT 'No',
			`field52` enum('Yes','No') DEFAULT 'No',
			`field53` enum('Yes','No') DEFAULT 'No',
			`field54` enum('Yes','No') DEFAULT 'No',
			`field55` enum('Yes','No') DEFAULT 'No',
			`field56` datetime DEFAULT NULL,
			`field57` datetime DEFAULT NULL,
			`field58` datetime DEFAULT NULL,
			`field59` datetime DEFAULT NULL,
			`field60` datetime DEFAULT NULL,
			`field61` datetime DEFAULT NULL,
			`field62` datetime DEFAULT NULL,
			`field63` datetime DEFAULT NULL,
			`field64` datetime DEFAULT NULL,
			`field65` datetime DEFAULT NULL,
			`_by` int(7) NOT NULL DEFAULT '1',
			`_on` datetime NOT NULL,
			`_from` varchar(10) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("member_status")) {		//	MDB Member Status
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]member_status` (
			`id` bigint(4) NOT NULL auto_increment,
			`status_val` varchar(48) NOT NULL,
			`description` mediumtext NOT NULL,
			`color` varchar(8) NOT NULL DEFAULT '#000000',
			`background` varchar(8) NOT NULL DEFAULT '#FFFFFF',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member_status table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("member_types")) {		//	MDB Member Types
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]member_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(16) NOT NULL,
			`description` longtext NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL COMMENT 'ip',
			`_by` int(7) NOT NULL COMMENT 'by',
			`color` varchar(8) NOT NULL DEFAULT '#000000',
			`background` varchar(8) NOT NULL DEFAULT '#FFFFFF',
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member_types table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("showin_contactlist")) {		//	Show field in contact list
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]showin_contactlist` (
			`id` int(4) NOT NULL auto_increment,
			`fieldid` varchar(48) COLLATE latin1_general_ci NOT NULL,
			`show_contact` int(2) NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added showin_contactlist table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("team")) {		//	MDB Teams table
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]team` (
			`id` int(4) NOT NULL auto_increment,
			`name` varchar(48) NOT NULL,
			`description` varchar(512) NOT NULL,
			`manager` int(4) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added team table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("training_packages")) {		//	MDB Training packages
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]training_packages` (
			`id` bigint(8) NOT NULL auto_increment,
			`package_name` varchar(48) DEFAULT NULL,
			`description` longtext,
			`available` enum('Yes','No') NOT NULL DEFAULT 'No',
			`provider` varchar(48) DEFAULT NULL,
			`address` varchar(128) DEFAULT NULL,
			`name` varchar(48) DEFAULT NULL,
			`email` varchar(64) DEFAULT NULL,
			`phone` varchar(48) DEFAULT NULL,
			`cost` int(16) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added training_packages table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("vehicles")) {		//	MDB Training packages
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]vehicles` (
			`id` bigint(8) NOT NULL auto_increment,
			`owner` int(4) DEFAULT NULL,
			`make` varchar(16) NOT NULL,
			`model` varchar(28) NOT NULL,
			`year` varchar(4) DEFAULT NULL,
			`color` varchar(4) DEFAULT NULL,
			`regno` varchar(12) NOT NULL,
			`type` tinyint(2) NOT NULL DEFAULT '0',
			`seats` tinyint(2) NOT NULL,
			`fueltype` enum('Petrol','Diesel','LPG') NOT NULL,
			`roofrack` enum('Yes','No') NOT NULL DEFAULT 'No',
			`towbar` enum('None','Ball','Ball and Pin','NATO') NOT NULL DEFAULT 'None',
			`winch` enum('None','Electric','Hydraulic','PTO','Hand') NOT NULL DEFAULT 'None',
			`trailer` enum('Yes','No') NOT NULL DEFAULT 'No',
			`vin` varchar(48) DEFAULT NULL,
			`excise` date DEFAULT NULL,
			`test` date DEFAULT NULL,
			`insurance` date DEFAULT NULL,
			`notes` longtext NOT NULL,
			`_by` int(11) NOT NULL,
			`_from` varchar(24) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added vehicles table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("vehicle_types")) {		//	MDB Training packages
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]vehicle_types` (
			`id` bigint(4) NOT NULL auto_increment,
			`name` varchar(48) DEFAULT NULL,
			`description` longtext,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added vehicle_types table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("waste_basket_f")) {		//	MDB Training packages
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]waste_basket_f` (
			`id` smallint(3) NOT NULL auto_increment,
			`member_id` smallint(3) NOT NULL,
			`name` varchar(64) NOT NULL,
			`shortname` varchar(32) NOT NULL,
			`description` varchar(24) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added waste_basket_f table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	if (!table_exists("waste_basket_m")) {		//	MDB Training packages
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]waste_basket_m` (
			`id` int(8) NOT NULL auto_increment,
			`field1` varchar(28) DEFAULT NULL,
			`field2` varchar(28) DEFAULT NULL,
			`field3` int(4) NOT NULL DEFAULT '0',
			`field4` varchar(16) DEFAULT NULL,
			`field5` varchar(64) DEFAULT NULL,
			`field6` varchar(48) DEFAULT NULL,
			`field7` bigint(4) NOT NULL,
			`field8` enum('Yes','No') NOT NULL DEFAULT 'Yes',
			`field9` varchar(28) DEFAULT NULL,
			`field10` varchar(28) DEFAULT NULL,
			`field11` varchar(12) DEFAULT NULL,
			`field12` double DEFAULT NULL,
			`field13` double DEFAULT NULL,
			`field14` longtext,
			`field15` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field16` datetime DEFAULT NULL,
			`field17` datetime DEFAULT NULL,
			`field18` datetime DEFAULT NULL,
			`field19` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field20` longtext,
			`field21` int(4) NOT NULL DEFAULT '0',
			`field22` varchar(1024) DEFAULT NULL,
			`field23` varchar(1024) DEFAULT NULL,
			`field24` varchar(1024) DEFAULT NULL,
			`field25` varchar(1024) DEFAULT NULL,
			`field26` varchar(1024) DEFAULT NULL,
			`field27` varchar(1024) DEFAULT NULL,
			`field28` varchar(1024) DEFAULT NULL,
			`field29` varchar(1024) DEFAULT NULL,
			`field30` varchar(1024) DEFAULT NULL,
			`field31` varchar(1024) DEFAULT NULL,
			`field32` varchar(1024) DEFAULT NULL,
			`field33` varchar(1024) DEFAULT NULL,
			`field34` varchar(1024) DEFAULT NULL,
			`field35` varchar(1024) DEFAULT NULL,
			`field36` varchar(1024) DEFAULT NULL,
			`field37` varchar(1024) DEFAULT NULL,
			`field38` varchar(1024) DEFAULT NULL,
			`field39` varchar(1024) DEFAULT NULL,
			`field40` varchar(1024) DEFAULT NULL,
			`field41` varchar(1024) DEFAULT NULL,
			`field42` varchar(1024) DEFAULT NULL,
			`field43` varchar(1024) DEFAULT NULL,
			`field44` varchar(1024) DEFAULT NULL,
			`field45` varchar(1024) DEFAULT NULL,
			`field46` enum('Yes','No') DEFAULT NULL,
			`field47` enum('Yes','No') DEFAULT NULL,
			`field48` enum('Yes','No') DEFAULT NULL,
			`field49` enum('Yes','No') DEFAULT NULL,
			`field50` enum('Yes','No') DEFAULT NULL,
			`field51` enum('Yes','No') DEFAULT NULL,
			`field52` enum('Yes','No') DEFAULT NULL,
			`field53` enum('Yes','No') DEFAULT NULL,
			`field54` enum('Yes','No') DEFAULT NULL,
			`field55` enum('Yes','No') DEFAULT NULL,
			`field56` datetime DEFAULT NULL,
			`field57` datetime DEFAULT NULL,
			`field58` datetime DEFAULT NULL,
			`field59` datetime DEFAULT NULL,
			`field60` datetime DEFAULT NULL,
			`field61` datetime DEFAULT NULL,
			`field62` datetime DEFAULT NULL,
			`field63` datetime DEFAULT NULL,
			`field64` datetime DEFAULT NULL,
			`field65` datetime DEFAULT NULL,
			`_by` int(7) NOT NULL DEFAULT '1',
			`_on` datetime NOT NULL,
			`_from` varchar(10) NOT NULL,
			`training` varchar(128) DEFAULT NULL,
			`capabilities` varchar(128) DEFAULT NULL,
			`equipment` varchar(128) DEFAULT NULL,
			`vehicles` varchar(128) DEFAULT NULL,
			`clothing` varchar(128) DEFAULT NULL,
			`files` varchar(128) DEFAULT NULL,
			`old_id` int(8) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added waste_basket_m table (Incorporate Tickets MDB into Tickets CAD)<BR />";}
		}

	$query = "INSERT INTO `$GLOBALS[mysql_prefix]defined_fields` (`id`, `field_id`, `label`, `size`, `fieldset`, `_noedit`, `_by`, `_on`, `_from`, `browser`) VALUES
		(1, 1, 'Surname', 48, 1, 1, 1, '2012-01-05 00:00:00', '127.0.0.1', 'msie 8.0'),
		(2, 2, 'First Name', 48, 1, 1, 1, '2012-01-05 09:43:06', '127.0.0.1', 'msie 8.0'),
		(3, 3, 'Team', 4, 1, 1, 1, '2012-01-05 09:43:06', '127.0.0.1', 'msie 8.0'),
		(4, 4, 'Team ID', 48, 1, 1, 1, '2012-01-05 09:43:06', '127.0.0.1', 'msie 8.0'),
		(5, 5, 'Picture', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(6, 6, 'Middle Name', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(7, 7, 'Member Type', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(8, 8, 'Availability', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(9, 9, 'Street', 48, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(10, 10, 'City', 48, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(11, 11, 'Postcode', 10, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(12, 12, 'Latitude', 10, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(13, 13, 'Longitude', 10, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(14, 14, 'Medical', 1024, 5, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(15, 15, 'Subscriptions Paid', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(16, 16, 'Membership Due date', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(17, 17, 'Join Date', 48, 1, 1, 1, '2012-01-20 10:51:39', '127.0.0.1', 'msie 8.0'),
		(18, 18, 'Date of Birth', 48, 1, 1, 1, '2012-01-16 16:04:29', '127.0.0.1', 'msie 8.0'),
		(19, 19, 'Police Check', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(20, 20, 'Notes', 1024, 5, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(21, 21, 'Member Status', 4, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(22, 22, 'Home Phone', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(23, 23, 'Work Phone', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(24, 24, 'Cellphone', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(25, 25, 'Email Address', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(26, 26, 'Amateur Radio Callsign', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(27, 27, 'Skype ID', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(28, 28, 'Emergency Contact', 48, 4, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(29, 29, 'Emergency Contact Relationship', 48, 4, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(30, 30, 'Emergency Contact Home Phone', 48, 4, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(31, 31, 'Emergency Contact Work Phone', 48, 4, 0, 2, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(32, 32, 'Emergency Contact Cellphone', 48, 4, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(33, 33, 'Emergency Contact Email', 48, 4, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(34, 34, 'State', 10, 2, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(35, 35, 'CRB Reference', 28, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(36, 36, 'Driving Licence Number', 28, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(37, 37, 'Driving License Points', 16, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(38, 38, 'State', 10, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(39, 39, 'Not Used', 10, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(40, 40, 'Not Used', 10, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(41, 41, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(42, 42, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(43, 43, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(44, 44, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(45, 45, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(46, 46, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(47, 47, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(48, 48, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(49, 49, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(50, 50, 'Example ENUM', 48, 4, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(51, 51, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(52, 52, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(53, 53, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(54, 54, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(55, 55, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(56, 56, 'Driving Licence Expiry', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(57, 57, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(58, 58, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(59, 59, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(60, 60, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(61, 61, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(62, 62, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(63, 63, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(64, 64, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(65, 65, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
		(66, 66, 'Updated By', 48, 7, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(67, 67, 'Updated', 48, 7, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
		(68, 68, 'IP Address', 48, 7, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0');";
	$result = mysql_query($query);
	if($result) { print "Inserted default data into defined_fields table (Incorporate Tickets MDB into Tickets CAD)<BR />";}

	$query = "INSERT INTO `$GLOBALS[mysql_prefix]fieldsets` (`id`, `name`, `label`) VALUES
		(1, 'Personal', 'Personal Details'),
		(2, 'Location', 'Location Details'),
		(3, 'Contact', 'Contact Details'),
		(4, 'Emergency', 'Emergency Contacts'),
		(5, 'General', 'General Information'),
		(6, 'Other', 'Other Information'),
		(7, 'hidden', 'Hidden');";
	$result = mysql_query($query);
	if($result) { print "Inserted default data into fieldsets table (Incorporate Tickets MDB into Tickets CAD)<BR />";}

	do_setting ('use_mdb','1');

	if (!table_exists("wizard_settings")) {		//	For new incident wizard
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]wizard_settings` (
			`id` int(4) NOT NULL auto_increment,
			`screen` int(11) NOT NULL,
			`fieldname` varchar(512) DEFAULT NULL,
			`label` varchar(64) NOT NULL,
			`default_text` varchar(64) NOT NULL,
			`helptext` varchar(1024) NOT NULL,
			`display_order` int(2) NOT NULL,
			`fieldtype` varchar(64) NOT NULL,
			`size` int(4) NOT NULL,
			`maxlength` int(4) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added wizard_settings table (Supports New Incident Creation Wizard)<BR />";}
		}

	do_setting ('inc_statistics_red_thresholds','5,2,4,0,0,3');	//	Thresholds for situation and full screen situation statistics display
	do_setting ('inc_statistics_orange_thresholds','3,1,2,0,0,2');	//	Thresholds for situation and full screen situation statistics display

	if (!table_exists("allocations")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]allocations` (
			`id` bigint(4) NOT NULL auto_increment,
			`member_id` int(2) NOT NULL DEFAULT '0',
			`skill_type` int(2) NOT NULL DEFAULT '0',
			`skill_id` int(2) NOT NULL DEFAULT '0',
			`completed` date DEFAULT NULL,
			`refresh_due` date DEFAULT NULL,
			`frequency` enum('Daily','Weekly','Permanent') DEFAULT NULL,
			`start` datetime DEFAULT NULL,
			`end` datetime DEFAULT NULL,
			`days` varchar(256) DEFAULT NULL,
			`_on` date NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added allocations table for Tickets MDB<BR />";}
		}

	if (!table_exists("capability_types")) {		//	Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]capability_types` (
			`id` bigint(4) NOT NULL auto_increment,
			`name` varchar(48) DEFAULT NULL,
			`description` longtext,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added capability types table for Tickets MDB<BR />";}
		}

	if (!table_exists("clothing_types")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]clothing_types` (
			`id` bigint(8) NOT NULL auto_increment,
			`clothing_item` varchar(48) DEFAULT NULL,
			`description` longtext,
			`size` varchar(48) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added clothing types table for Tickets MDB<BR />";}
		}

	if (!table_exists("equipment_types")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]equipment_types` (
			`id` bigint(8) NOT NULL auto_increment,
			`equipment_name` varchar(48) DEFAULT NULL,
			`description` longtext,
			`spec` varchar(48) DEFAULT NULL,
			`serial` varchar(48) DEFAULT NULL,
			`condition` enum('New','Good','Serviceable','Unusable','For Destruction') NOT NULL DEFAULT 'Good',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added equipment types table for Tickets MDB<BR />";}
		}

	if (!table_exists("events")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]events` (
			`id` int(11) NOT NULL auto_increment,
			`event_name` varchar(128) DEFAULT NULL,
			`description` longtext,
			`event_type` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added events table for Tickets MDB<BR />";}
		}

	if (!table_exists("event_types")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]event_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(64) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added event types table for Tickets MDB<BR />";}
		}

	if (!table_exists("training_packages")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]training_packages` (
			`id` bigint(8) NOT NULL auto_increment,
			`package_name` varchar(48) DEFAULT NULL,
			`description` longtext,
			`available` enum('Yes','No') NOT NULL DEFAULT 'No',
			`provider` varchar(48) DEFAULT NULL,
			`address` varchar(128) DEFAULT NULL,
			`name` varchar(48) DEFAULT NULL,
			`email` varchar(64) DEFAULT NULL,
			`phone` varchar(48) DEFAULT NULL,
			`cost` int(16) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added training packages table for Tickets MDB<BR />";}
		}

	if (!table_exists("vehicles")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]vehicles` (
			`id` bigint(8) NOT NULL auto_increment,
			`owner` int(4) DEFAULT NULL,
			`make` varchar(16) NOT NULL,
			`model` varchar(28) NOT NULL,
			`year` varchar(4) DEFAULT NULL,
			`color` varchar(4) DEFAULT NULL,
			`regno` varchar(12) NOT NULL,
			`type` tinyint(2) NOT NULL DEFAULT '0',
			`seats` tinyint(2) NOT NULL,
			`fueltype` enum('Petrol','Diesel','LPG') NOT NULL,
			`roofrack` enum('Yes','No') NOT NULL DEFAULT 'No',
			`towbar` enum('None','Ball','Ball and Pin','NATO') NOT NULL DEFAULT 'None',
			`winch` enum('None','Electric','Hydraulic','PTO','Hand') NOT NULL DEFAULT 'None',
			`trailer` enum('Yes','No') NOT NULL DEFAULT 'No',
			`vin` varchar(48) DEFAULT NULL,
			`excise` date DEFAULT NULL,
			`test` date DEFAULT NULL,
			`insurance` date DEFAULT NULL,
			`notes` longtext NOT NULL,
			`_by` int(11) NOT NULL,
			`_from` varchar(24) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added vehicles table for Tickets MDB<BR />";}
		}

	if (!table_exists("vehicle_types")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]vehicle_types` (
			`id` bigint(4) NOT NULL auto_increment,
			`name` varchar(48) DEFAULT NULL,
			`description` longtext,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added vehicle types table for Tickets MDB<BR />";}
		}

	if (!table_exists("mdb_files")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mdb_files` (
			`id` smallint(3) NOT NULL auto_increment,
			`member_id` smallint(3) NOT NULL,
			`name` varchar(64) NOT NULL,
			`shortname` varchar(32) NOT NULL,
			`description` varchar(24) NOT NULL,
			`filesize` varchar(12) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added mdb_files table for Tickets MDB<BR />";}
		}

	if (!table_exists("mdb_settings")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]mdb_settings` (
			`id` bigint(8) NOT NULL auto_increment,
			`name` tinytext,
			`value` varchar(512) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added mdb settings table for Tickets MDB<BR />";}
		}

	if (!table_exists("member")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]member` (
			`id` int(8) NOT NULL auto_increment,
			`field1` varchar(28) DEFAULT NULL,
			`field2` varchar(28) DEFAULT NULL,
			`field3` int(4) NOT NULL DEFAULT '0',
			`field4` varchar(16) DEFAULT NULL,
			`field5` varchar(64) DEFAULT NULL,
			`field6` varchar(48) DEFAULT NULL,
			`field7` bigint(4) NOT NULL,
			`field8` enum('Yes','No') NOT NULL DEFAULT 'Yes',
			`field9` varchar(28) DEFAULT NULL,
			`field10` varchar(28) DEFAULT NULL,
			`field11` varchar(12) DEFAULT NULL,
			`field12` double DEFAULT NULL,
			`field13` double DEFAULT NULL,
			`field14` longtext,
			`field15` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field16` datetime DEFAULT NULL,
			`field17` datetime DEFAULT NULL,
			`field18` datetime DEFAULT NULL,
			`field19` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field20` longtext,
			`field21` int(4) NOT NULL DEFAULT '0',
			`field22` varchar(1024) DEFAULT NULL,
			`field23` varchar(1024) DEFAULT NULL,
			`field24` varchar(1024) DEFAULT NULL,
			`field25` varchar(1024) DEFAULT NULL,
			`field26` varchar(1024) DEFAULT NULL,
			`field27` varchar(1024) DEFAULT NULL,
			`field28` varchar(1024) DEFAULT NULL,
			`field29` varchar(1024) DEFAULT NULL,
			`field30` varchar(1024) DEFAULT NULL,
			`field31` varchar(1024) DEFAULT NULL,
			`field32` varchar(1024) DEFAULT NULL,
			`field33` varchar(1024) DEFAULT NULL,
			`field34` varchar(1024) DEFAULT NULL,
			`field35` varchar(1024) DEFAULT NULL,
			`field36` varchar(1024) DEFAULT NULL,
			`field37` varchar(1024) DEFAULT NULL,
			`field38` varchar(1024) DEFAULT NULL,
			`field39` varchar(1024) DEFAULT NULL,
			`field40` varchar(1024) DEFAULT NULL,
			`field41` varchar(1024) DEFAULT NULL,
			`field42` varchar(1024) DEFAULT NULL,
			`field43` varchar(1024) DEFAULT NULL,
			`field44` varchar(1024) DEFAULT NULL,
			`field45` varchar(1024) DEFAULT NULL,
			`field46` enum('Yes','No') DEFAULT 'No',
			`field47` enum('Yes','No') DEFAULT 'No',
			`field48` enum('Yes','No') DEFAULT 'No',
			`field49` enum('Yes','No') DEFAULT 'No',
			`field50` enum('Yes','No') DEFAULT NULL,
			`field51` enum('Yes','No') DEFAULT 'No',
			`field52` enum('Yes','No') DEFAULT 'No',
			`field53` enum('Yes','No') DEFAULT 'No',
			`field54` enum('Yes','No') DEFAULT 'No',
			`field55` enum('Yes','No') DEFAULT 'No',
			`field56` datetime DEFAULT NULL,
			`field57` datetime DEFAULT NULL,
			`field58` datetime DEFAULT NULL,
			`field59` datetime DEFAULT NULL,
			`field60` datetime DEFAULT NULL,
			`field61` datetime DEFAULT NULL,
			`field62` datetime DEFAULT NULL,
			`field63` datetime DEFAULT NULL,
			`field64` datetime DEFAULT NULL,
			`field65` datetime DEFAULT NULL,
			`_by` int(7) NOT NULL DEFAULT '1',
			`_on` datetime NOT NULL,
			`_from` varchar(10) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member table for Tickets MDB<BR />";}
		}

	if (!table_exists("member_status")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]member_status` (
			`id` bigint(4) NOT NULL auto_increment,
			`status_val` varchar(48) NOT NULL,
			`description` mediumtext NOT NULL,
			`color` varchar(8) NOT NULL DEFAULT '#000000',
			`background` varchar(8) NOT NULL DEFAULT '#FFFFFF',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member status table for Tickets MDB<BR />";}
		}

	if (!table_exists("member_types")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]member_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(16) NOT NULL,
			`description` longtext NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL COMMENT 'ip',
			`_by` int(7) NOT NULL COMMENT 'by',
			`color` varchar(8) NOT NULL DEFAULT '#000000',
			`background` varchar(8) NOT NULL DEFAULT '#FFFFFF',
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member types table for Tickets MDB<BR />";}
		}

	if (!table_exists("responder_x_member")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]responder_x_member` (
			`id` int(11) NOT NULL auto_increment,
			`responder_id` int(6) NOT NULL,
			`member_id` int(6) NOT NULL,
			`use_email` int(11) NOT NULL DEFAULT '0',
			`use_cellphone` int(11) NOT NULL DEFAULT '0',
			`use_homephone` int(11) NOT NULL DEFAULT '0',
			`use_workphone` int(11) NOT NULL DEFAULT '0',
			`use_smsg_id` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added responder X member table for Tickets MDB - associates member with unit/responder<BR />";}
		}

	if (!table_exists("showin_contactlist")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]showin_contactlist` (
			`id` int(4) NOT NULL auto_increment,
			`fieldid` varchar(48) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
			`show_contact` int(2) NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added showin_contactlist table for Tickets MDB - sets which fields are shown in the contact list report<BR />";}
		}

	if (!table_exists("team")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]team` (
			`id` int(4) NOT NULL auto_increment,
			`name` varchar(48) NOT NULL,
			`description` varchar(512) NOT NULL,
			`manager` int(4) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Team table for Tickets MDB<BR />";}
		}

	if (!table_exists("waste_basket_f")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]waste_basket_f` (
			`id` smallint(3) NOT NULL auto_increment,
			`member_id` smallint(3) NOT NULL,
			`name` varchar(64) NOT NULL,
			`shortname` varchar(32) NOT NULL,
			`description` varchar(24) NOT NULL,
			`_on` datetime NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added file waste basket table for Tickets MDB<BR />";}
		}

	if (!table_exists("waste_basket_m")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]waste_basket_m` (
			`id` int(8) NOT NULL auto_increment,
			`field1` varchar(28) DEFAULT NULL,
			`field2` varchar(28) DEFAULT NULL,
			`field3` int(4) NOT NULL DEFAULT '0',
			`field4` varchar(16) DEFAULT NULL,
			`field5` varchar(64) DEFAULT NULL,
			`field6` varchar(48) DEFAULT NULL,
			`field7` bigint(4) NOT NULL,
			`field8` enum('Yes','No') NOT NULL DEFAULT 'Yes',
			`field9` varchar(28) DEFAULT NULL,
			`field10` varchar(28) DEFAULT NULL,
			`field11` varchar(12) DEFAULT NULL,
			`field12` double DEFAULT NULL,
			`field13` double DEFAULT NULL,
			`field14` longtext,
			`field15` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field16` datetime DEFAULT NULL,
			`field17` datetime DEFAULT NULL,
			`field18` datetime DEFAULT NULL,
			`field19` enum('Yes','No') NOT NULL DEFAULT 'No',
			`field20` longtext,
			`field21` int(4) NOT NULL DEFAULT '0',
			`field22` varchar(1024) DEFAULT NULL,
			`field23` varchar(1024) DEFAULT NULL,
			`field24` varchar(1024) DEFAULT NULL,
			`field25` varchar(1024) DEFAULT NULL,
			`field26` varchar(1024) DEFAULT NULL,
			`field27` varchar(1024) DEFAULT NULL,
			`field28` varchar(1024) DEFAULT NULL,
			`field29` varchar(1024) DEFAULT NULL,
			`field30` varchar(1024) DEFAULT NULL,
			`field31` varchar(1024) DEFAULT NULL,
			`field32` varchar(1024) DEFAULT NULL,
			`field33` varchar(1024) DEFAULT NULL,
			`field34` varchar(1024) DEFAULT NULL,
			`field35` varchar(1024) DEFAULT NULL,
			`field36` varchar(1024) DEFAULT NULL,
			`field37` varchar(1024) DEFAULT NULL,
			`field38` varchar(1024) DEFAULT NULL,
			`field39` varchar(1024) DEFAULT NULL,
			`field40` varchar(1024) DEFAULT NULL,
			`field41` varchar(1024) DEFAULT NULL,
			`field42` varchar(1024) DEFAULT NULL,
			`field43` varchar(1024) DEFAULT NULL,
			`field44` varchar(1024) DEFAULT NULL,
			`field45` varchar(1024) DEFAULT NULL,
			`field46` enum('Yes','No') DEFAULT NULL,
			`field47` enum('Yes','No') DEFAULT NULL,
			`field48` enum('Yes','No') DEFAULT NULL,
			`field49` enum('Yes','No') DEFAULT NULL,
			`field50` enum('Yes','No') DEFAULT NULL,
			`field51` enum('Yes','No') DEFAULT NULL,
			`field52` enum('Yes','No') DEFAULT NULL,
			`field53` enum('Yes','No') DEFAULT NULL,
			`field54` enum('Yes','No') DEFAULT NULL,
			`field55` enum('Yes','No') DEFAULT NULL,
			`field56` datetime DEFAULT NULL,
			`field57` datetime DEFAULT NULL,
			`field58` datetime DEFAULT NULL,
			`field59` datetime DEFAULT NULL,
			`field60` datetime DEFAULT NULL,
			`field61` datetime DEFAULT NULL,
			`field62` datetime DEFAULT NULL,
			`field63` datetime DEFAULT NULL,
			`field64` datetime DEFAULT NULL,
			`field65` datetime DEFAULT NULL,
			`_by` int(7) NOT NULL DEFAULT '1',
			`_on` datetime NOT NULL,
			`_from` varchar(10) NOT NULL,
			`training` varchar(128) DEFAULT NULL,
			`capabilities` varchar(128) DEFAULT NULL,
			`equipment` varchar(128) DEFAULT NULL,
			`vehicles` varchar(128) DEFAULT NULL,
			`clothing` varchar(128) DEFAULT NULL,
			`files` varchar(128) DEFAULT NULL,
			`old_id` int(8) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added member waste basket table for Tickets MDB<BR />";}
		}

	if (!table_exists("defined_fields")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]defined_fields` (
			`id` tinyint(2) NOT NULL auto_increment,
			`field_id` int(4) NOT NULL,
			`label` varchar(32) NOT NULL,
			`size` int(4) NOT NULL DEFAULT '48',
			`fieldset` int(2) DEFAULT NULL,
			`_noedit` int(2) NOT NULL DEFAULT '0',
			`_by` int(4) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(24) NOT NULL,
			`browser` varchar(40) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added defined fields table for Tickets MDB<BR />";}
		}

	if (table_exists("defined_fields")) {		//	For Tickets MDB
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]defined_fields` (`id`, `field_id`, `label`, `size`, `fieldset`, `_noedit`, `_by`, `_on`, `_from`, `browser`) VALUES
			(1, 1, 'Surname', 48, 1, 1, 1, '2012-01-05 00:00:00', '127.0.0.1', 'msie 8.0'),
			(2, 2, 'First Name', 48, 1, 1, 1, '2012-01-05 09:43:06', '127.0.0.1', 'msie 8.0'),
			(3, 3, 'Team', 4, 1, 1, 1, '2012-01-05 09:43:06', '127.0.0.1', 'msie 8.0'),
			(4, 4, 'Team ID', 48, 1, 1, 1, '2012-01-05 09:43:06', '127.0.0.1', 'msie 8.0'),
			(5, 5, 'Picture', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(6, 6, 'Middle Name', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(7, 7, 'Member Type', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(8, 8, 'Availability', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(9, 9, 'Street', 48, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(10, 10, 'City', 48, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(11, 11, 'Postcode', 10, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(12, 12, 'Latitude', 10, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(13, 13, 'Longitude', 10, 2, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(14, 14, 'Medical', 1024, 5, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(15, 15, 'Subscriptions Paid', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(16, 16, 'Membership Due date', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(17, 17, 'Join Date', 48, 1, 1, 1, '2012-01-20 10:51:39', '127.0.0.1', 'msie 8.0'),
			(18, 18, 'Date of Birth', 48, 1, 1, 1, '2012-01-16 16:04:29', '127.0.0.1', 'msie 8.0'),
			(19, 19, 'Police Check', 48, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(20, 20, 'Notes', 1024, 5, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(21, 21, 'Member Status', 4, 1, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(22, 22, 'Home Phone', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(23, 23, 'Work Phone', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(24, 24, 'Cellphone', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(25, 25, 'Email Address', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(26, 26, 'Amateur Radio Callsign', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(27, 27, 'Skype ID', 48, 3, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(28, 28, 'Emergency Contact', 48, 4, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(29, 29, 'Emergency Contact Relationship', 48, 4, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(30, 30, 'Emergency Contact Home Phone', 48, 4, 1, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(31, 31, 'Emergency Contact Work Phone', 48, 4, 0, 2, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(32, 32, 'Emergency Contact Cellphone', 48, 4, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(33, 33, 'Emergency Contact Email', 48, 4, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(34, 34, 'State', 10, 2, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(35, 35, 'CRB Reference', 28, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(36, 36, 'Driving Licence Number', 28, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(37, 37, 'Driving License Points', 16, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(38, 38, 'State', 10, 1, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(39, 39, 'Not Used', 10, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(40, 40, 'Not Used', 10, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(41, 41, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(42, 42, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(43, 43, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(44, 44, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(45, 45, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(46, 46, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(47, 47, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(48, 48, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(49, 49, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(50, 50, 'Example ENUM', 48, 4, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(51, 51, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(52, 52, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(53, 53, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(54, 54, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(55, 55, 'Not Used', 48, 6, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(56, 56, 'Driving Licence Expiry', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(57, 57, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(58, 58, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(59, 59, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(60, 60, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(61, 61, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(62, 62, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(63, 63, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(64, 64, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(65, 65, 'Not Used', 48, 1, 0, 1, '2012-08-09 11:27:00', '127.0.0.1', 'msie 8.0'),
			(66, 66, 'Updated By', 48, 7, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(67, 67, 'Updated', 48, 7, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0'),
			(68, 68, 'IP Address', 48, 7, 0, 1, '2012-01-10 15:21:09', '127.0.0.1', 'msie 8.0');";
		$result = mysql_query($query);
		if($result) { print "Inserted default data into defined fields table for Tickets MDB<BR />";}
		}

	if (!table_exists("fieldsets")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fieldsets` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(48) NOT NULL,
			`label` varchar(48) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added fieldsets table for Tickets MDB<BR />";}
		}

	if (table_exists("fieldsets")) {		//	For Tickets MDB
		$query = "INSERT INTO `fieldsets` (`id`, `name`, `label`) VALUES
			(1, 'Personal', 'Personal Details'),
			(2, 'Location', 'Location Details'),
			(3, 'Contact', 'Contact Details'),
			(4, 'Emergency', 'Emergency Contacts'),
			(5, 'General', 'General Information'),
			(6, 'Other', 'Other Information'),
			(7, 'hidden', 'Hidden');";
		$result = mysql_query($query);
		if($result) { print "Inserted default data into fieldsets table for Tickets MDB<BR />";}
		}

	do_setting ('use_wizard','0');
	do_setting ('live_mdb','0');
	do_setting ('use_mdb','0');
	do_setting ('inc_statistics_red_thresholds','5,2,4,0,0,3');
	do_setting ('inc_statistics_orange_thresholds','3,1,2,0,0,2');
	do_setting ('alternate_sit','0');
	do_setting ('full_sit_v2','0');
	do_mdb_setting ('use_mdb_contact','0');
	do_mdb_setting ('use_mdb_status','0');
	do_mdb_setting ('date_tracking','0,0,0,0,0,0');
	do_mdb_setting ('mdb_contact_via_field','field25');
	do_mdb_setting ('mdb_cellphone_field','field24');
	do_mdb_setting ('mdb_smsg_id_field','field4');
	do_mdb_setting ('tickets_status_available','0');
	do_mdb_setting ('mdb_phone_field','field22');
	do_mdb_setting ('tickets_status_unavailable','0');

	do_setting('report_graphic','');
	do_setting('report_header','');
	do_setting('report_footer','');
	do_setting('report_contact','');
	do_setting('openweathermaps_api','');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `member_id` INT(7) NULL DEFAULT NULL AFTER `responder_id`;";
	$result = mysql_query($query);
	if($result) { print "Added Member ID field to Log table<BR />";}

	if (table_exists("wizard_settings")) {		//	For New Incident Wizard
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]wizard_settings`";	//	check if table contains mandatory fields, if not then truncate table and start again
		$result = mysql_query($query);
		if($result && (mysql_num_rows($result) > 0 && mysql_num_rows($result) < 7)) {		// If has some entries but not enough - there are 8 mandatory fields
			$query2 = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]wizard_settings`";
			$result2 = mysql_query($query2);
			}
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]wizard_settings` (`id`, `screen`, `fieldname`, `label`, `default_text`, `helptext`, `display_order`, `fieldtype`, `size`, `maxlength`) VALUES
			(1, 1, 'in_types_id', 'Incident Type', '', 'Select Incident Nature. Says what the incident is and also sets any specific protocol', 1, 'TEXT STRING', 4, 64),
			(2, 1, 'address', 'Address', '', 'Address, street, city and state. State needed for geocoding.', 3, 'TEXT STRING', 65535, 64),
			(3, 1, 'contact', 'Contact', '', 'Contact Name of requester', 4, 'TEXT STRING', 48, 64),
			(4, 1, 'phone', 'Caller Phone No.', ' Caller Phone Number', 'Reporting Caller Phone contact number', 4, 'TEXT STRING', 24, 64),
			(5, 2, 'scope', 'Incident Title', '', 'Incident Name', 2, 'TEXTAREA', 65535, 65535),
			(6, 2, 'description', 'Description', '', 'Description of job/request', 3, 'TEXTAREA', 65535, 65535),
			(7, 2, 'problemstart', 'Incident Start Time', '', 'Incident Start Time', 1, 'DATETIME', 19, 19);";
		$result = mysql_query($query);
		if($result) { print "Inserted Mandatory fields for New Incident Wizard<BR />";}
		}

	if (!table_exists("email_blacklist")) {		//	For Tickets MDB
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]email_blacklist` (
			`id` int(4) NOT NULL,
			`email` varchar(64) NOT NULL,
			`_by` int(7) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Email Blacklist Table for messaging<BR />";}
		}

	do_setting('allow_nogeo','0');
	do_setting('session_timeout','60');
	do_setting('login_userlist','0');

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder_x_member` ADD `use_email` INT(11) NOT NULL DEFAULT 0 AFTER `member_id`;";
	$result = mysql_query($query);
	if($result) { print "Added Use Email field to Responder X Member table for Tickets MDB<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder_x_member` ADD `use_cellphone` INT(11) NOT NULL DEFAULT 0 AFTER `use_email`;";
	$result = mysql_query($query);
	if($result) { print "Added Use Cellphone field to Responder X Member table for Tickets MDB<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder_x_member` ADD `use_homephone` INT(11) NOT NULL DEFAULT 0 AFTER `use_cellphone`;";
	$result = mysql_query($query);
	if($result) { print "Added Use Homephone field to Responder X Member table for Tickets MDB<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder_x_member` ADD `use_workphone` INT(11) NOT NULL DEFAULT 0 AFTER `use_homephone`;";
	$result = mysql_query($query);
	if($result) { print "Added Use Workphone field to Responder X Member table for Tickets MDB<BR />";}

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder_x_member` ADD `use_smsg_id` INT(11) NOT NULL DEFAULT 0 AFTER `use_workphone`;";
	$result = mysql_query($query);
	if($result) { print "Added Use SMSG ID field to Responder X Member table for Tickets MDB<BR />";}

	if (!table_exists("sound_settings")) {
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]sound_settings` (
			`id` int(2) NOT NULL,
			`name` varchar(48) NOT NULL,
			`filename` varchar(128) NOT NULL,
			`mp3_filename` varchar(128) NOT NULL,
			`ison` int(1) NOT NULL DEFAULT '0' ,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query);
		if($result) { print "Added Sound Settings Table<BR />";}
		}

	$query = "INSERT INTO `$GLOBALS[mysql_prefix]sound_settings` (`id`, `name`, `filename`, `mp3_filename`, `ison`) VALUES
		(1, 'Incident', 'aooga.wav', 'klaxon.mp3', 1),
		(2, 'Responder', 'aooga.wav', 'klaxon.mp3', 1),
		(3, 'Message', 'aooga.wav', 'klaxon.mp3', 1),
		(4, 'Chat', 'aooga.wav', 'klaxon.mp3', 1),
		(5, 'Broadcast', 'aooga.wav', 'klaxon.mp3', 1);";
	$result = mysql_query($query);
	if($result) { print "Inserted Sound Settings<BR />";}

	do_setting('map_on_rm','1');
	do_setting('sslcert_location','');
	do_setting('sslcert_passphrase','');
	do_mdb_setting ('member_status_available','0');
	do_mdb_setting ('enforce_status','0');
	do_mdb_setting ('no_status_select','0');

	if(table_type("ticket") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed ticket table to type InnoDB<BR />";}
		}

	if(table_type("responder") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed responder table to type InnoDB<BR />";}
		}

	if(table_type("facilities") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed facilities table to type InnoDB<BR />";}
		}

	if(table_type("settings") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]settings` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed settings table to type InnoDB<BR />";}
		}

	if(table_type("requests") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]requests` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed requests table to type InnoDB<BR />";}
		}

	if(table_type("member") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]member` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed member table to type InnoDB<BR />";}
		}

	if(table_type("assigns") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed assigns table to type InnoDB<BR />";}
		}

	if(table_type("allocates") != "InnoDB") {
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]allocates` ENGINE = InnoDB;;";
		$result = mysql_query($query);
		if($result) { print "Changed allocates table to type InnoDB<BR />";}
		}

	do_setting('tracks_length','12');

	$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($version)." WHERE `name`='_version' LIMIT 1";	// 5/28/08
	$result = mysql_query($query);
	if($result) {
		print "Updated Version number<BR /><BR /><BR />";
		unset ($result);
	}
	print "</DIV><BR /><BR /><BR />";
	print "<CENTER>";
	print "<DIV id='the_text' style='width: 100%; text-algn: center'></DIV>";
	print "<DIV id='buttons' style='width: 100%; text-algn: center'></DIV>";
	print "</CENTER>";
	print "</DIV>";
	}		// end (!($version ==...) ==================================================

$osmdir = getcwd() . "/_osm";
$tiledir = $osmdir . "/tiles";

if(!is_dir($osmdir)) {
	mkdir($osmdir, 0700, true);
	}

if(!is_dir($tiledir)) {
	mkdir($tiledir, 0700, true);
	}

//	check_ai("major_incidents");

	function update_disp_stat ($which, $what, $old) {		//	10/26/11
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' AND `value` = '$old' LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if ((mysql_affected_rows())!=0) {
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '$which'";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}
		unset ($result);
		return TRUE;
		}				// end function update_disp_stat ()

	update_disp_stat ('disp_stat','D/R/O/FE/FA/Clear','D/R/O/Clear');		// 10/26/11

	if (!table_exists("region")) {	//	6/10/11
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]region` (
			`id` bigint(8) NOT NULL AUTO_INCREMENT,
			`group_name` varchar(60) NOT NULL,
			`category` int(2) DEFAULT NULL,
			`description` varchar(60) DEFAULT NULL,
			`owner` int(2) NOT NULL DEFAULT '1',
			`def_area_code` varchar(4) DEFAULT NULL,
			`def_city` varchar(20) DEFAULT NULL,
			`def_lat` double DEFAULT NULL,
			`def_lng` double DEFAULT NULL,
			`def_st` varchar(20) DEFAULT NULL,
			`def_zoom` int(2) NOT NULL DEFAULT '10',
			`boundary` int(4) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]region` (`id`, `group_name`, `category`, `description`, `owner`, `def_area_code`, `def_city`, `def_lat`, `def_lng`, `def_st`, `def_zoom`, `boundary`) VALUES
				(0, 'General', 4, 'General - group 0', 1, '', '', NULL, NULL, '10', 10, 0);";
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11
	}

	if (!table_exists("region_type")) {	//	6/10/11
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]region_type` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(16) NOT NULL,
			`description` varchar(48) NOT NULL,
			`_on` datetime NOT NULL,
			`_from` varchar(16) NOT NULL,
			`_by` int(7) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]region_type` (`id`, `name`, `description`, `_on`, `_from`, `_by`) VALUES
			(1, 'EMS', 'Medical Services', '2011-06-17 14:21:39', '127.0.0.1', 1),
			(2, 'Security', 'Security Services', '2011-06-17 14:21:55', '127.0.0.1', 1),
			(3, 'Fire', 'Fire Services', '2011-06-17 14:22:10', '127.0.0.1', 1),
			(4, 'General', 'General Use', '2011-06-17 14:22:10', '127.0.0.1', 1);";
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11
	}

	if (!table_exists("allocates")) {	//	6/10/11
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]allocates` (
			`id` bigint(8) NOT NULL auto_increment,
			`group` int(4) NOT NULL default '1',
			`type` tinyint(1) NOT NULL default '1',
			`al_as_of` datetime default NULL,
			`al_status` int(4) default NULL,
			`resource_id` int(4) default NULL,
			`sys_comments` varchar(64) default NULL,
			`user_id` int(4) NOT NULL default  '0',
			PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
		$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`;";
		$result_insert = mysql_query($query_insert);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
			$id = $row['id'];
			$tick_stat = $row['status'];
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
					(1 , 1, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			}

		$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`;";
		$result_insert = mysql_query($query_insert);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
			$id = $row['id'];	// 4/13/11
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
					(1 , 2, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			}

		$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";
		$result_insert = mysql_query($query_insert);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
			$id = $row['id'];	// 4/13/11
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
					(1 , 3, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";	// 4/13/11
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			}

		$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]user`;";
		$result_insert = mysql_query($query_insert);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
			$id = $row['id'];
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
					(1 , 4, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			}
	}	//	End if "Allocates does not exist"


if((count_responders()== 0) && (get_variable('title_string') == "") && (isset($_GET['first_start']) && ($_GET['first_start'] == "yes"))) {	//	5/11/12 For quick start routine
?>
<SCRIPT>
	$('update_status').scrollTop = $('update_status').scrollHeight;
	var textinfo = "<B>Do you wish to use the Tickets Quick start routine?";
	$('the_text').innerHTML = textinfo;
	var thebuttons = "<SPAN id='quick_but' CLASS='plain text' style='display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.quick.submit();'>Yes Please</SPAN>";
	thebuttons += "<SPAN id='normal_but' CLASS='plain text' style='display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.normal.submit();'>No just start Tickets</SPAN>";
	$('buttons').innerHTML = thebuttons;
</SCRIPT>
	<FORM NAME='quick' METHOD='POST' ACTION='quick_start.php'>
	<INPUT TYPE='hidden' NAME='run_quick' VALUE='yes'></FORM>
	<FORM NAME='normal' METHOD='POST' ACTION='index.php'></FORM>
<?php
	} else {
?>
<SCRIPT>
	if($('update_status')) {
		$('update_status').scrollTop = $('update_status').scrollHeight;
		var thebuttons = "<SPAN id='normal_but' CLASS='plain text' style='display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.normal.submit();'>Continue</SPAN>";
		$('buttons').innerHTML = thebuttons;
		}
</SCRIPT>
	<FORM NAME='quick' METHOD='POST' ACTION='quick_start.php'>
	<INPUT TYPE='hidden' NAME='run_quick' VALUE='yes'></FORM>
	<FORM NAME='normal' METHOD='POST' ACTION='index.php'></FORM>
<?php
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

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `set_severity` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'sets incident severity' AFTER `protocol`";
	$result = mysql_query($query);
?>

<?php
	exit();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<style>
	body { max-width: 980px; margin: 20px auto; background: #f6f8fb; padding: 14px; border-radius: 8px; }
	fieldset { border: 1px solid #c9d2df; border-radius: 6px; background: #fff; }
	legend { padding: 0 8px; }
	input[type=text], input[type=password] { border: 1px solid #b3bfd1; border-radius: 4px; padding: 6px; }
	.install-status { background: #fff; border: 1px solid #c9d2df; border-radius: 6px; padding: 10px; margin-top: 8px; }
	.busy-indicator { display:none; margin-top:10px; font-weight:bold; color:#1f4f8a; }
	.spinner { display:inline-block; width:14px; height:14px; border:2px solid #9fb4d1; border-top-color:#1f4f8a; border-radius:50%; vertical-align:middle; margin-right:6px; animation: spin 0.8s linear infinite; }
	@keyframes spin { to { transform: rotate(360deg); } }
</style>
</HEAD><BODY>
<FONT CLASS="header">Installing <?php print $version; ?> </FONT><BR /><BR />
<SCRIPT>
	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_db_host.value == "")			{errmsg+= "\tMySQL HOST name is required\n";}
		if (theForm.frm_db_dbname.value == "")			{errmsg+= "\tMySQL DATABASE name is required\n";}
		if ((theForm.frm_option.value == "install") || (theForm.frm_option.value == "install-drop")) {
			if (theForm.frm_admin_user.value == "")			{errmsg+= "\tAdmin username is required\n";}
			if (theForm.frm_admin_pass.value.length < 8)			{errmsg+= "\tAdmin password must be at least 8 characters\n";}
		}
//		if (theForm.frm_api_key.value.length != 86)		{errmsg+= "\tGMaps API key is required - 86 chars\n";} -- 1/9/2013
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			var submitBtn = document.getElementById('do_it_btn');
			if(submitBtn){ submitBtn.disabled = true; submitBtn.value = 'Working...'; }
			var busy = document.getElementById('busy_indicator');
			if(busy){ busy.style.display = 'block'; }
			return true;
			}
		}				// end function validate(theForm)

</SCRIPT>
<?php

//	foreach ($_POST as $VarName=>$VarValue) {echo "POST:$VarName => $VarValue, <BR />";};
//	foreach ($_GET as $VarName=>$VarValue) 	{echo "GET:$VarName => $VarValue, <BR />";};
//	echo "<BR/>";

    function table_exists($name,$drop_tables) {            //check if mysql table exists, if it's a re-install
        $query = "SELECT COUNT(*) FROM $name";
        $result = mysql_query($query);
        if(!empty($result)){
            $num_rows = @mysql_num_rows($result);

            if($num_rows) {
                if($drop_tables) {
                    mysql_query("DROP TABLE $name");
                    print "<LI> Dropped table '<B>$name</B>'<BR />";
                }
                else {
                    print "<FONT CLASS=\"warn\">Table '$name' already exists, use Re-install option instead. Click back in your browser.</FONT></BODY></HTML>";
                    exit();
                }
            }
        }
    }

	function prefix ($tbl) {		/* returns concatenated string */
		global $db_prefix;
		return  $db_prefix . $tbl;
		}

	/* insert new values into settings table */
	function do_insert_settings($name,$value) {
		$tablename = prefix("settings");
		$query = "INSERT INTO `$tablename` (name,value) VALUES('$name','$value')";
		$result = mysql_query($query) or die("DO_INSERT_SETTINGS($name,$value) failed, execution halted");
		}
	//create tables
	function create_tables($db_prefix,$drop_tables=1) {
		//check if tables exists and if drop_tables is 1

		table_exists($db_prefix."action",$drop_tables);		// 10/11/08	 - 1/25/09
		table_exists($db_prefix."allocates",$drop_tables);		
		table_exists($db_prefix."assigns",$drop_tables);
		table_exists($db_prefix."captions",$drop_tables);		
		table_exists($db_prefix."certs",$drop_tables);
		table_exists($db_prefix."certs_x_user",$drop_tables);
		table_exists($db_prefix."chat_invites",$drop_tables);		
		table_exists($db_prefix."chat_messages",$drop_tables);
		table_exists($db_prefix."chat_rooms",$drop_tables);
		table_exists($db_prefix."cities",$drop_tables);
		table_exists($db_prefix."clones",$drop_tables);
		table_exists($db_prefix."codes",$drop_tables);		
		table_exists($db_prefix."constituents",$drop_tables);		
		table_exists($db_prefix."contacts",$drop_tables);
		table_exists($db_prefix."courses",$drop_tables);
		table_exists($db_prefix."courses_x_user",$drop_tables);
		table_exists($db_prefix."css_day",$drop_tables);
		table_exists($db_prefix."css_night",$drop_tables);		
		table_exists($db_prefix."documents",$drop_tables);
		table_exists($db_prefix."documents_log",$drop_tables);
		table_exists($db_prefix."facilities",$drop_tables);		
		table_exists($db_prefix."fac_status",$drop_tables);	
		table_exists($db_prefix."fac_types",$drop_tables);	
		table_exists($db_prefix."hints",$drop_tables);	
		table_exists($db_prefix."insurance",$drop_tables);		
		table_exists($db_prefix."in_types",$drop_tables);
		table_exists($db_prefix."log",$drop_tables);
		table_exists($db_prefix."logins",$drop_tables);
		table_exists($db_prefix."mmarkup",$drop_tables);	
		table_exists($db_prefix."mmarkup_cats",$drop_tables);	
		table_exists($db_prefix."modules",$drop_tables);		
		table_exists($db_prefix."notify",$drop_tables);
		table_exists($db_prefix."patient",$drop_tables);
		table_exists($db_prefix."photos",$drop_tables);
		table_exists($db_prefix."pin_ctrl",$drop_tables);	
		table_exists($db_prefix."places",$drop_tables);		
		table_exists($db_prefix."region",$drop_tables);		
		table_exists($db_prefix."region_type",$drop_tables);		
		table_exists($db_prefix."remote_devices",$drop_tables);		
		table_exists($db_prefix."responder",$drop_tables);
		table_exists($db_prefix."rss",$drop_tables);		
		table_exists($db_prefix."settings",$drop_tables);
		table_exists($db_prefix."skills",$drop_tables);
		table_exists($db_prefix."skills_x_user",$drop_tables);
		table_exists($db_prefix."stats_settings",$drop_tables);		
		table_exists($db_prefix."teams",$drop_tables);
		table_exists($db_prefix."teams_x_user",$drop_tables);
		table_exists($db_prefix."team_types",$drop_tables);
		table_exists($db_prefix."ticket",$drop_tables);
		table_exists($db_prefix."titles",$drop_tables);
		table_exists($db_prefix."tracks",$drop_tables);
		table_exists($db_prefix."tracks_hh",$drop_tables);
		table_exists($db_prefix."unit_types",$drop_tables);
		table_exists($db_prefix."un_status",$drop_tables);
		table_exists($db_prefix."user",$drop_tables);

		$tables = "";

// -- phpMyAdmin SQL Dump
// -- version 2.9.2
// -- http://www.phpmyadmin.net
// --
// -- Host: localhost
// -- Generation Time: Oct 11, 2008 at 12:35 PM
// -- Server version: 5.0.27
// -- PHP Version: 5.2.1
// --
// -- Database: `tickets_2_8_c`
// --


// -- --------------------------------------------------------

// --
// -- Table structure for table `action`
// --
		$table_name = prefix("action");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `ticket_id` int(8) NOT NULL default '0',
		 `date` datetime default NULL,
		 `description` text NOT NULL,
		 `user` int(8) default NULL,
		 `action_type` int(8) default NULL,
		 `responder` text,
		 `updated` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables = $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `assigns`
// --

		$table_name = prefix("assigns");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `as_of` datetime default NULL,
		 `status_id` int(4) default '1',
		 `ticket_id` int(4) default NULL,
		 `responder_id` int(4) default NULL,
		 `comments` varchar(64) default NULL,
		 `user_id` int(4) NOT NULL,
		 `dispatched` datetime default NULL,
		 `responding` datetime default NULL,
		 `clear` datetime default NULL,
		 `on_scene` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `certs`
// --

		$table_name = prefix("certs");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `certificate` varchar(48) NOT NULL COMMENT 'certificate description',
		 `source` varchar(48) NOT NULL COMMENT 'issuing agency',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `certs_x_user`
// --

		$table_name = prefix("certs_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `certificate_id` int(3) NOT NULL COMMENT 'certification description',
		 `user_id` int(4) NOT NULL COMMENT 'issued to - user id',
		 `date` date default NULL COMMENT 'date issued',
		 `comment` varchar(48) default NULL COMMENT 'comment re certification issued',
		 `by` int(7) NOT NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `chat_messages`
// --

		$table_name = prefix("chat_messages");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(10) unsigned NOT NULL auto_increment,
		 `message` varchar(255) NOT NULL default '0',
		 `when` datetime default NULL,
		 `chat_room_id` int(7) NOT NULL default '0',
		 `user_id` int(7) NOT NULL default '1',
		 `from` varchar(16) NOT NULL COMMENT 'ip addr',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `chat_rooms`
// --

		$table_name = prefix("chat_rooms");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(7) NOT NULL auto_increment,
		 `room` varchar(16) NOT NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `cities`
// --

		$table_name = prefix("cities");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(11) NOT NULL auto_increment,
		 `city_zip` int(5) unsigned zerofill NOT NULL,
		 `city_name` varchar(50) NOT NULL,
		 `city_state` char(2) NOT NULL,
		 `city_lat` double NOT NULL,
		 `city_lng` double NOT NULL,
		 `city_county` varchar(50) NOT NULL,
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `clones`
// --

		$table_name = prefix("clones");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(4) NOT NULL auto_increment,
		 `name` varchar(16) default NULL,
		 `prefix` varchar(8) default NULL,
		 `date` datetime default NULL COMMENT 'last used',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `contacts`
// --

		$table_name = prefix("contacts");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(7) NOT NULL auto_increment,
		 `name` varchar(48) NOT NULL,
		 `organization` varchar(48) default NULL,
		 `phone` varchar(24) default NULL,
		 `mobile` varchar(24) default NULL,
		 `email` varchar(48) NOT NULL,
		 `other` varchar(24) default NULL,
		 `as-of` datetime NOT NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `courses`
// --

		$table_name = prefix("courses");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `course` varchar(48) NOT NULL COMMENT 'certificate description',
		 `source` varchar(48) NOT NULL COMMENT 'sponsor agency',
		 `location` varchar(48) NOT NULL COMMENT 'location',
		 `duration` varchar(48) NOT NULL COMMENT 'no. days or hours',
		 `basis` varchar(48) NOT NULL COMMENT 'cost basis',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `courses_x_user`
// --

		$table_name = prefix("courses_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `courses_id` int(4) NOT NULL COMMENT 'certification description',
		 `user_id` int(4) NOT NULL COMMENT 'issued to - user id',
		 `date` date default NULL COMMENT 'date taken',
		 `comment` varchar(48) default NULL COMMENT 'comment re certification issued',
		 `by` int(7) NOT NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `documents`
// --

		$table_name = prefix("documents");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(10) unsigned NOT NULL auto_increment,
		 `name` varchar(64) NOT NULL,
		 `status` enum('locked','unlocked','na') NOT NULL default 'na',
		 `locked_by` int(7) NOT NULL COMMENT 'user index',
		 `locked_on` datetime default NULL,
		 `info` tinytext,
		 `keyword` varchar(64) default NULL,
		 `type` varchar(64) default NULL,
		 `size` int(10) unsigned NOT NULL,
		 `author` int(10) unsigned default NULL,
		 `source` int(10) unsigned default NULL,
		 `maintainer` int(10) unsigned default NULL,
		 `revision` varchar(64) default NULL COMMENT 'revision information',
		 `created` datetime default NULL,
		 `modified` datetime default NULL,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `documents_log`
// --

		$table_name = prefix("documents_log");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(10) unsigned NOT NULL auto_increment,
		 `user_id` int(10) unsigned NOT NULL,
		 `document_id` int(10) unsigned NOT NULL,
		 `revision` int(10) unsigned NOT NULL,
		 `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `in_types`
// --

		$table_name = prefix("in_types");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(4) NOT NULL auto_increment,
		 `type` varchar(20) NOT NULL,
		 `description` varchar(120) default NULL,
		 `group` varchar(20) default NULL,
		 `sort` int(11) NOT NULL default '0',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Incident types' AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

//		--
//		--  data for table `in_types`
//		--

			$query = "INSERT INTO `$table_name` (`id`, `type`, `description`, `group`, `sort`) VALUES
				(NULL, 'examp1', 'Example one', 'grp 1', '1'),
				(NULL, 'examp2', 'Example two', 'grp 2', '2');";
			mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);

// -- --------------------------------------------------------

// --
// -- Table structure for table `log`
// --

		$table_name = prefix("log");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `who` tinyint(7) default NULL,
		 `from` varchar(20) default NULL,
		 `when` datetime default NULL,
		 `code` tinyint(7) NOT NULL default '0',
		 `ticket_id` int(7) default NULL,
		 `responder_id` int(7) default NULL,
		 `info` varchar(2048) default NULL,
		 `facility` int(7) default NULL,
		 `rec_facility` int(7) default NULL,
		 `mileage` int(8) default NULL,
		 `member_id` int(7) default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Log of station actions' AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `logins`
// --

		$table_name = prefix("logins");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `ip` varchar(15) NOT NULL,
		 `salt` varchar(36) NOT NULL,
		 `intime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='login authentication' AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `modules`
// --

        $table_name = prefix("modules");
        $query = "CREATE TABLE `$table_name` (
        `id` bigint(8) NOT NULL auto_increment,
        `mod_name` varchar(26) NOT NULL,
        `mod_status` varchar(1) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Optional modules to load' AUTO_INCREMENT=1;";

        mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
        $tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `notify`				// 10/22/08
// --

		$table_name = prefix("notify");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `ticket_id` int(8) NOT NULL default '0',
		  `user` int(8) NOT NULL default '0',
		  `execute_path` tinytext,
		  `severities` int(1) NOT NULL default '0' COMMENT '0=NA, 1=all, 2=top 2, 3=top only',
		  `on_action` tinyint(1) default '0',
		  `on_ticket` tinyint(1) default '0',
		  `on_patient` tinyint(1) default '0',
		  `email_address` varchar(255) default NULL,
		  `pager` varchar(255) default NULL COMMENT 'pipe-sep''d ',
		  `pager_cb` varchar(96) default NULL COMMENT 'pager call-back no.',
		  `by` int(7) NOT NULL COMMENT 'user',
		  `from` varchar(16) NOT NULL COMMENT 'IP addr',
		  `on` datetime NOT NULL COMMENT 'updated',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `patient`
// --

		$table_name = prefix("patient");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `ticket_id` int(8) NOT NULL default '0',
		 `name` varchar(32) default NULL,
		 `fullname` varchar(64) NULL default NULL,
		 `dob` varchar(32) NULL default NULL,
		 `gender` int(1) NOT NULL default '0',
		 `insurance_id` int(3) NOT NULL default '0' COMMENT 'see table insurance',
		 `facility_contact` varchar(64) NULL,
		 `facility_id` int(3) NOT NULL default '0',		 
		 `date` datetime default NULL,
		 `description` text NOT NULL,
		 `user` int(8) default NULL,
		 `action_type` int(8) default NULL,
		 `updated` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `photos`
// --

		$table_name = prefix("photos");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `description` varchar(256) NOT NULL,
		 `ticket_id` int(7) NOT NULL COMMENT 'associated ticket id',
		 `taken_by` varchar(48) default NULL,
		 `taken_on` varchar(24) default NULL,
		 `by` int(7) NOT NULL COMMENT 'user id',
		 `on` datetime NOT NULL,
		 `from` varchar(16) NOT NULL COMMENT 'ip address',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `responder` 1/27/09, 4/10/09
// --

		$table_name = prefix("responder");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `name` text,
		  `mobile` tinyint(2) default '0',
		  `direcs` tinyint(2) NOT NULL default '1' COMMENT '0=>no directions, 1=> yes',
		  `aprs` tinyint(2) NOT NULL default '0',
		  `instam` tinyint(2) NOT NULL default '0' COMMENT 'instamapper',
		  `description` text NOT NULL,
		  `capab` varchar(255) default NULL COMMENT 'Capability',
		  `un_status_id` int(4) NOT NULL default '0',
		  `other` varchar(96) default NULL,
		  `callsign` varchar(24) default NULL,
		  `contact_name` varchar(64) default NULL,
		  `contact_via` varchar(64) default NULL,
		  `pager_p` varchar(64) default NULL,
		  `pager_s` varchar(64) default NULL,
		  `send_no` varchar(64) default NULL,
		  `lat` double default NULL,
		  `lng` double default NULL,
		  `type` tinyint(1) default NULL,
		  `updated` datetime default NULL,
		  `user_id` int(4) default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

// -- --------------------------------------------------------

// --
// -- Table structure for table `settings`
// --

		$table_name = prefix("settings");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `name` tinytext,
		 `value` tinytext,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `skills`
// --

		$table_name = prefix("skills");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `skill` varchar(48) NOT NULL,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `skills_x_user`
// --

		$table_name = prefix("skills_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `skills_id` int(3) NOT NULL COMMENT 'certification description',
		 `user_id` int(4) NOT NULL COMMENT 'skill held - user id',
		 `level` enum('b','m','h','x','na') NOT NULL default 'na' COMMENT ' beginner, moderate, high, expert, NA',
		 `comment` varchar(48) default NULL COMMENT 'comment re certification issued',
		 `by` int(7) NOT NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `team_types`
// --

		$table_name = prefix("team_types");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `type` varchar(48) NOT NULL COMMENT 'team type',
		 `comment` varchar(48) NOT NULL COMMENT 'issuing agency',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `teams`
// --

		$table_name = prefix("teams");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `team` varchar(48) NOT NULL COMMENT 'team name- major',
		 `sub-group` varchar(48) NOT NULL COMMENT 'team name- major',
		 `ttypes_id` int(7) NOT NULL COMMENT 'team name',
		 `mission` varchar(48) NOT NULL COMMENT 'issuing agency',
		 `leader` int(4) NOT NULL COMMENT 'team leader - user id',
		 `leader_dpty` int(4) NOT NULL COMMENT 'team leader deputy - user id',
		 `formed` date default NULL COMMENT 'date formed',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `teams_x_user`
// --

		$table_name = prefix("teams_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `teams_id` int(4) NOT NULL COMMENT 'certification description',
		 `member_id` int(7) NOT NULL COMMENT 'team member - user id',
		 `status` int(2) default NULL COMMENT 'team member status',
		 `date_a` date default NULL COMMENT 'date assigned',
		 `date_e` date default NULL COMMENT 'date assignment ended',
		 `comment` varchar(48) default NULL COMMENT 'comment re assignment',
		 `by` int(7) default NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `ticket`
// --

		$table_name = prefix("ticket");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `in_types_id` int(4) NOT NULL,
		 `contact` varchar(48) NOT NULL default '',
		 `street` varchar(48) default NULL,
		 `city` varchar(32) default NULL,
		 `state` char(2) default NULL,
		 `phone` varchar(16) default NULL,
		 `lat` double default NULL,
		 `lng` double default NULL,
		 `date` datetime default NULL,
		 `problemstart` datetime default NULL,
		 `problemend` datetime default NULL,
		 `scope` text NOT NULL,
		 `affected` text,
		 `description` text NOT NULL,
		 `comments` text,
		 `status` tinyint(1) NOT NULL default '0',
		 `owner` tinyint(4) NOT NULL default '0',
		 `severity` int(2) NOT NULL default '0',
		 `updated` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `titles`
// --
		$table_name = prefix("titles");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `title` varchar(24) NOT NULL,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------
// --
// -- Table structure for table `tracks`
// --

		$table_name = prefix("tracks");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(7) NOT NULL auto_increment,
		 `packet_id` varchar(48) default NULL,
		 `source` varchar(96) default NULL,
		 `latitude` double default NULL,
		 `longitude` double default NULL,
		 `speed` int(8) default NULL,
		 `course` int(8) default NULL,
		 `altitude` int(8) default NULL,
		 `symbol_table` varchar(96) default NULL,
		 `symbol_code` varchar(96) default NULL,
		 `status` varchar(96) default NULL,
		 `closest_city` varchar(200) default NULL,
		 `mapserver_url_street` varchar(200) default NULL,
		 `mapserver_url_regional` varchar(200) default NULL,
		 `packet_date` datetime default NULL,
		 `updated` datetime NOT NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `packet_id` (`packet_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------
// --
// -- Table structure for table `tracks_hh`		- 2/8/09
// --
		$table_name = prefix("tracks_hh");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(7) NOT NULL auto_increment,
		  `source` varchar(96) default NULL,
		  `latitude` double default NULL,
		  `longitude` double default NULL,
		  `speed` int(8) default NULL,
		  `course` int(8) default NULL,
		  `altitude` int(8) default NULL,
		  `status` varchar(96) default NULL,
		  `updated` datetime NOT NULL,
		  `from` varchar(16) NOT NULL COMMENT 'ip addr',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `un_status`				- 1/18/08
// --
		$table_name = prefix("un_status");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(4) NOT NULL auto_increment,
		 `status_val` varchar(20) NOT NULL,
		 `description` varchar(60) default NULL,
		 `group` varchar(20) default NULL,
		 `sort` int(11) NOT NULL default '0',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);

//		--
//		--  data for table `un_status`
//		--

		$tables .= $table_name . ", ";
			$query = "INSERT INTO `$table_name` ( `id` , `status_val` , `description` , `group` , `sort` ) VALUES
				(NULL, 'available', 'Available', 'av', 1),
				(NULL, 'unavailable', 'Unavailable', 'unav', 3),
				(NULL, 'in_service', 'In service', 'inserv', 0);
				";
			mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);


// -- --------------------------------------------------------

// --
// -- Table structure for table `unit_types`				// 1/27/09
// --

		$table_name = prefix("unit_types");
			$query = "CREATE TABLE `$table_name` (
			  `id` int(11) NOT NULL auto_increment,
			  `name` varchar(16) NOT NULL,
			  `description` varchar(48) NOT NULL,
			  `icon` int(3) NOT NULL default '0',
			  `_on` datetime NOT NULL,
			  `_from` varchar(16) NOT NULL COMMENT 'ip',
			  `_by` int(7) NOT NULL COMMENT 'by',
			  PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Allows for variable unit types' AUTO_INCREMENT=6 ;";

//		dump ($query);
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

//		--
//		--  data for table `unit_types`
//		--

			$query = "INSERT INTO `$table_name` (`id`, `name`, `description`, `icon`, `_on`, `_from`, `_by`) VALUES
				(1, 'example', 'An example unit type', 3, '2009-01-28 14:13:06', '127.0.0.1', 1);
				";

			mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);


// -- --------------------------------------------------------

// --
// -- Table structure for table `user`				 11/6/08
// --
		$DB = $db_prefix . $_POST['frm_db_dbname'];
		$table_name = prefix("user");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `user` text NOT NULL COMMENT 'userid',
		 `passwd` tinytext NOT NULL COMMENT 'MySQL hash',
		 `name_l` text default NULL  COMMENT 'last',
		 `name_f` text default NULL  COMMENT 'first',
		 `name_mi` text default NULL  COMMENT 'middle',
		 `dob` date default NULL,
		 `title_id` tinyint(2) default NULL COMMENT 'title',
		 `addr_street` text default NULL,
		 `addr_city` text default NULL,
		 `addr_st` text default NULL,
		 `disp` tinyint(1) default 1 NULL COMMENT 'dispatch access',
		 `files` tinyint(1) default 0 COMMENT 'docs data access',
		 `pers` tinyint(1) default 0 COMMENT 'personnel data access',
		 `teams` tinyint(1) default 0 COMMENT 'teams data access',
		 `status` enum('approved','pending','na') NOT NULL default 'approved',
		 `open_at` enum('d','f','p','t') NOT NULL default 'd' COMMENT 'after logon',
		 `ident` text default NULL COMMENT 'identification',
		 `info` text default NULL COMMENT 'account information',
		 `phone_p` text default NULL COMMENT 'phone primary',
		 `phone_s` text default NULL COMMENT 'phone secondary',
		 `phone_m` text default NULL COMMENT 'phone mobile',
		 `level` tinyint(1) NOT NULL  default 0 COMMENT 'privileges',
		 `email` text  default NULL COMMENT 'email addr - primary',
		 `email_s` text default NULL COMMENT 'email addr - secondary',
		 `ticket_per_page` tinyint(1) default NULL,
		 `sort_desc` tinyint(1) default 0,
		 `sortorder` tinytext default NULL,
		 `reporting` tinyint(1) default 1,
		 `callsign` varchar(12) default NULL COMMENT 'added 9/23/07',
		 `db_prefix` text  default NULL COMMENT 'db clone to use',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);$tables .= $table_name . ", ";

		print "<LI> Created tables " . substr($tables, 0, -2) . "<BR />";
		}


	function create_user($admin_user, $admin_pass) {	// create first super user and guest
		global $db_prefix;
		$tablename = prefix("user");
		$admin_user = trim((string)$admin_user);
		if($admin_user === '' || !preg_match('/^[A-Za-z0-9_.@-]{3,64}$/', $admin_user)) {
			die("<FONT CLASS=\"warn\">Admin username must be 3-64 chars and use letters, numbers, ., _, @ or -</FONT>");
			}
		if(strlen((string)$admin_pass) < 8) {
			die("<FONT CLASS=\"warn\">Admin password must be at least 8 characters.</FONT>");
			}
		$admin_hash = password_hash((string)$admin_pass, PASSWORD_BCRYPT);
		if($admin_hash === false) {
			die("<FONT CLASS=\"warn\">Unable to hash admin password safely.</FONT>");
			}
		$guest_hash = password_hash('guest', PASSWORD_BCRYPT);
		print "<P>";
		mysql_query("INSERT INTO `$tablename` (`user`,`passwd`,`info`,`level`,`ticket_per_page`,`sort_desc`,`sortorder`,`reporting`,`db_prefix`) VALUES('". mysql_real_escape_string($admin_user) ."','". mysql_real_escape_string($admin_hash) ."','Super-administrator',0,0,1,'date',0, '$db_prefix')") or die("INSERT INTO user failed, execution halted at line " . __LINE__);
		print "<LI> Created user '<B>" . h($admin_user) . "</B>'";
		mysql_query("INSERT INTO `$tablename` (`user`,`passwd`,`info`,`level`,`ticket_per_page`,`sort_desc`,`sortorder`,`reporting`,`db_prefix`) VALUES('guest','". mysql_real_escape_string($guest_hash) ."','Guest',3,0,1,'date',0,'$db_prefix')") or die("INSERT INTO user failed, execution halted at line " . __LINE__);
		print "<LI> Created user '<B>guest</B>'";
		print "</P>";
		}

	//insert settings
	function insert_settings() {
		global $version, $api_key, $base_install_version;

		do_insert_settings('_aprs_time','0');
		do_insert_settings('_sleep','5');				// 10/17/08 --
		do_insert_settings('_version',$base_install_version);
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','30');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','1');
		do_insert_settings('auto_poll','0');			// new 10/15/07, rev'd 3/17/09
		do_insert_settings('auto_route','1');					// 1/17/09
		do_insert_settings('call_board','1');			// new 1/10/08
		do_insert_settings('chat_time','4');			// new 1/16/08
		do_insert_settings('closed_interval','');
		do_insert_settings('date_format','n/j/y H:i');
		do_insert_settings('def_area_code','');
		do_insert_settings('def_city','');
		do_insert_settings('def_lat','39.1');			// approx center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_st','');
		do_insert_settings('def_zoom','3');
		do_insert_settings('def_zoom_fixed','0');
		do_insert_settings('delta_mins','0');
		do_insert_settings('email_reply_to','');		// new 1/10/08
		do_insert_settings('frameborder','1');
		do_insert_settings('framesize','50');
		do_insert_settings('gmaps_api_key',$_POST['frm_api_key']);		//
		do_insert_settings('group_or_dispatch','0');		//		1/10/11
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('host','www.yourdomain.com');
		do_insert_settings('instam_key','');			// 4/10/09
		do_insert_settings('kml_files','1');		// new 6/7/08
		do_insert_settings('lat_lng','0');			// 9/13/08
		do_insert_settings('link_capt','');
		do_insert_settings('link_url','');
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('map_height','512');
		do_insert_settings('map_width','512');
		do_insert_settings('military_time','1');				// 7/16/08
		do_insert_settings('msg_text_1','');
		do_insert_settings('msg_text_2','');
		do_insert_settings('msg_text_3','');
		do_insert_settings('quick','0');
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('serial_no_ap','1');					// 1/17/09
		do_insert_settings('situ_refr','');
		do_insert_settings('terrain','1');						// 2/24/09
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');
		do_insert_settings('validate_email','1');
		do_insert_settings('wp_key','729c1a751fd3d2428cfe2a7b43442c64');		// 9/13/08
		do_insert_settings ('internet','1');		// 8/5/10

		print "<LI> Inserted default settings";
		}

	//output mysql settings to mysql.inc.php
	function write_conf($host,$db,$user,$password,$prefix) {
		if (!$fp = fopen('./incs/mysql.inc.php', 'a'))
        	print '<LI> <FONT CLASS="warn">Cannot open mysql.inc.php for writing</FONT>';
		else {
			ftruncate($fp,0);
			fwrite($fp, "<?php\n");
			fwrite($fp, "	/* generated by '" . basename( __FILE__) . "' " . date('r') . " */\n");
			fwrite($fp, '	$mysql_host 	= '."'$host';\n");
			fwrite($fp, '	$mysql_db 		= '."'$db';\n");
			fwrite($fp, '	$mysql_user 	= '."'$user';\n");
			fwrite($fp, '	$mysql_passwd 	= '."'$password';\n");
			fwrite($fp, '	$mysql_prefix 	= '."'$prefix';\n");
			fwrite($fp, '?>');
			}

		fclose($fp);
		print '<LI> Wrote configuration to \'<B>./incs/mysql.inc.php</B>\'';
		}


	function read_existing_config() {
		$defaults = array('host'=>'','db'=>'','user'=>'','password'=>'','prefix'=>'');
		if(file_exists('./incs/mysql.inc.php')) {
			include('./incs/mysql.inc.php');
			$defaults['host'] = isset($mysql_host) ? $mysql_host : '';
			$defaults['db'] = isset($mysql_db) ? $mysql_db : '';
			$defaults['user'] = isset($mysql_user) ? $mysql_user : '';
			$defaults['password'] = isset($mysql_passwd) ? $mysql_passwd : '';
			$defaults['prefix'] = isset($mysql_prefix) ? $mysql_prefix : '';
		}
		return $defaults;
	}

	function fetch_installed_version($host, $db, $user, $password, $prefix) {
		if($host == '' || $db == '' || $user == '') { return ''; }
		if(!@mysql_connect($host, $user, $password)) { return ''; }
		if(!@mysql_select_db($db)) { return ''; }
		$table = $prefix . "settings";
		$result = @mysql_query("SHOW TABLES LIKE '" . mysql_real_escape_string($table) . "'");
		if(!$result || mysql_num_rows($result) == 0) { return ''; }
		$q = @mysql_query("SELECT `value` FROM `$table` WHERE `name`='_version' LIMIT 1");
		if(!$q || mysql_num_rows($q) == 0) { return ''; }
		$row = mysql_fetch_assoc($q);
		return isset($row['value']) ? $row['value'] : '';
	}

	function set_version_settings($prefix, $version) {
		$table = $prefix . "settings";
		@mysql_query("UPDATE `$table` SET `value`='" . mysql_real_escape_string($version) . "' WHERE `name`='_target_version' LIMIT 1");
		if(mysql_affected_rows() == 0) {
			@mysql_query("INSERT INTO `$table` (`name`,`value`) VALUES('_target_version','" . mysql_real_escape_string($version) . "')");
		}
		@mysql_query("UPDATE `$table` SET `value`='" . mysql_real_escape_string($version) . "' WHERE `name`='_version' LIMIT 1");
		if(mysql_affected_rows() == 0) {
			@mysql_query("INSERT INTO `$table` (`name`,`value`) VALUES('_version','" . mysql_real_escape_string($version) . "')");
		}
	}

	function installer_requires_admin($detected_version, $current_version) {
		return (($detected_version != '') && ($detected_version == $current_version));
	}

	function installer_has_admin_session() {
		if(session_id() == '') { @session_start(); }
		if(!array_key_exists('level', $_SESSION)) { return false; }
		$level = intval($_SESSION['level']);
		return (($level === 0) || ($level === 1));
	}

	//upgrade db from 0.65 to 0.7
	function upgrade_065_07($prefix) {
		print '<LI> Upgrading structure <B>0.65->0.7...</B><BR />';
		mysql_query("ALTER TABLE $prefix"."ticket ADD severity int(2) NOT NULL default '0'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #1 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD level tinyint(1) default NULL") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #2 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD ticket_per_page tinyint(1) default '0'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #3 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD sort_desc tinyint(1) default '0'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #4 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD sortorder tinytext") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #5 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD reporting tinyint(1) default '1'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #6 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."action ADD user int(8) default NULL") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #7 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."action ADD action_type int(8) default NULL") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #8 failed</FONT>");

		print '<LI> Replacing permissions and actions...</B>';
		mysql_query("UPDATE $prefix"."user SET level='1' WHERE admin='1'") or die("<FONT CLASS=\"warn\">Could not replace user permissions (admin)</FONT>");
		mysql_query("UPDATE $prefix"."user SET level='2' WHERE admin='0'") or die("<FONT CLASS=\"warn\">Could not replace user permissions (user)</FONT>");
		mysql_query("UPDATE $prefix"."action SET action_type='10', user='0'") or die("<FONT CLASS=\"warn\">Could not fix action data</FONT>");
		mysql_query("ALTER TABLE $prefix"."user DROP admin") or die("<FONT CLASS=\"warn\">Could not drop user field 'admin'</FONT>");

		print '<LI> Replacing settings...</B>';
		mysql_query("DELETE FROM $prefix"."settings") or die("<FONT CLASS=\"warn\">Could not <remove old settings</FONT>");
		insert_settings();
		}

$defaults = read_existing_config();
	$detected_version = fetch_installed_version($defaults['host'],$defaults['db'],$defaults['user'],$defaults['password'],$defaults['prefix']);
		$admin_required = installer_requires_admin($detected_version, $version);
		$admin_ok = installer_has_admin_session();

//	if($_GET['go']) {				/* connect to mysql database if option isn't writeconf' */
	if (array_key_exists('go', $_GET)) {		// 9/16/08

			if($admin_required && !$admin_ok) {
				print "<FONT CLASS=\"warn\">Admin login required before running reinstall/upgrade actions on an existing system at this version.</FONT><BR /><BR />";
				print "Please log in as an admin first, then re-open install.php.<BR />";
				exit();
			}

		$db_prefix=$_POST['frm_db_prefix'];

		if ($_POST['frm_option'] != 'writeconf') {
			$query = "@mysql_connect({$_POST['frm_db_host']}, {$_POST['frm_db_user']}, {$_POST['frm_db_password']})";
//			print __LINE__ . " " . $query . "<BR>";

			if (!@mysql_connect($_POST['frm_db_host'], $_POST['frm_db_user'], $_POST['frm_db_password'])) {
				$the_pw = (empty($_POST['frm_db_password']))? "<i>none entered</i>"  : str_repeat('*', 8);
				print "<B>Connection to MySQL failed using the following entered values:</B><BR /><BR />\n";
				print "MySQL Host:<B> " . h($_POST['frm_db_host']) . "</B><BR />\n";
				print "MySQL Username:<B> " . h($_POST['frm_db_user']) . "</B><BR />\n";
				print "MySQL Password:<B> " . $the_pw . "</B><BR /><BR />\n";
				print "MySQL Database Name:<B> " . h($_POST['frm_db_dbname']) . "</B><BR /><BR />\n";
				print "Please correct these entries and try again.<BR /><BR />";
?>
				<FORM NAME='db_error' METHOD='post' ACTION = 'install.php'>
				<INPUT TYPE='submit' VALUE='Try again'>
				</FORM>
				</BODY>
				</HTML>
<?php
				die();
				}		// end if (!$result)

//			mysql_connect($_POST['frm_db_host'], $_POST['frm_db_user'], $_POST['frm_db_password']) or die("<FONT CLASS=\"warn\">Couldn't connect to database on '$_POST[frm_db_host]', make sure it is running and user has permissions. Click back in your browser.</FONT>");
			mysql_select_db($_POST['frm_db_dbname']) or die("<FONT CLASS=\"warn\">Couldn't select database '$_POST[frm_db_dbname]', make sure it exists and user has permissions. Click back in your browser.</FONT>");

//			$query = "SET GLOBAL sql_mode='STRICT_ALL_TABLES'";					// 11/6/08
//			mysql_query($query) or die("<FONT CLASS=\"warn\">SQL error at line " . __LINE__ . " </FONT>");

			}

		//run the functions

		switch($_POST['frm_option']) {
			case 'install':{
				create_tables($_POST['frm_db_prefix']);
				create_user($_POST['frm_admin_user'], $_POST['frm_admin_pass']);
				insert_settings();
				set_version_settings($_POST['frm_db_prefix'], $version);
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> Install complete. Database/schema created once at version $version.</LI>";
				break;
				}
			case 'install-drop':{
				create_tables($_POST['frm_db_prefix'],1);
				create_user($_POST['frm_admin_user'], $_POST['frm_admin_pass']);
				insert_settings();
				set_version_settings($_POST['frm_db_prefix'], $version);
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> Re-install complete. Database/schema created once at version $version.</LI>";
				break;
				}
//			case 'upgrade-0.65':{
//				upgrade_065_07($_POST['frm_db_prefix']);
//				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
//				print "<LI> Upgrade <B>0.65->0.7</B> complete!";
//				break;
//				}
			case 'upgrade':{
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				set_version_settings($_POST['frm_db_prefix'], $version);
				print "<LI> Upgrade preparation complete. Running schema and settings updates now...</LI>";
				print "<DIV class='install-status'><IFRAME title='upgrade progress' src='install.php?installer_upgrade=1' style='width:100%;height:360px;border:1px solid #c9d2df;background:#fff;'></IFRAME></DIV>";
				break;
				}
			case 'writeconf':{
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> All done.";
				break;
				}
			default:
				print "<LI> <FONT CLASS=\"warn\">'$_POST[frm_option]' is not a valid option!</FONT>";
			}

		print '<BR /><BR /><FONT CLASS="warn">Your Tickets installation is now complete - the start page is \'index.php\' .</FONT>';
		print '<BR /><BR /><FONT CLASS="warn">It is strongly recommended that you move/delete/change rights on install.php after this</FONT>';
		print '<BR /><BR /><A HREF="index.php?first_start=yes"><< Start Tickets >></A>';	//	5/11/12 Changed link for quick start.		
		}
//	else if ($_GET['help']) {		//
	else if (array_key_exists('help', $_GET)) {		// 9/16/08
?>
		<BLOCKQUOTE>
		1.  Fill in the install form with your mysql server settings. The 'table prefix' option enables you to prefix the tables with
		an optional name if you're only using one database or need multiple instances. Thus a prefix of <B>my_</B> would name the
		tables <B>my_action</B>, <B>my_user</B> etc.<BR /><BR />

		2.  The Google Maps API key is obtained from them at http://www.google.com/apis/maps/signup.html and is free.  There, you'll be asked
		for the domain name to which the key applies, and that will be the Tickets server and directory address.  If you're planning multiple
		installations as many keys as you may need are available.  Please note:  That key is an 86-character string, which should be
		copy/pasted from them into the form.  Hint: email that key to yourself, along with the other form entries.<BR /><BR />

		3.  The <B>Upgrade Existing TicketsCAD</B> option preserves data and runs the full index.php schema/settings upgrade routine in-place.<BR /><BR />

		4.  The <B>Re-install</B> option <FONT CLASS="warn">drops all Tickets data</FONT> in the specified database and re-installs them;
		if the tables already exists this option is required. If the tables names are prefixed, you have to specify it in the form.<BR /><BR />

		5.  The <B>Write Configuration Only</B> option writes the specified mysql settings to the file <B>'mysql.inc.php'</B> in the <B>'incs'</B>
		subdirectory but doesn't alter the database	in any way.<BR /><BR />

		6.  The file <B>'mysql.inc.php'</B> in the <B>'incs'</B> subdirectory <B>must be write-able in any install option</B>.

		<BR /><BR /><A HREF="install.php"><< back to the install script</A></BLOCKQUOTE>
<?php
		}
	else {
		$filename = './incs';							// 12/18/10
		if (!is_writable($filename)) {					// 8/8/10 - 
		    die ("ERROR! Directory '{$filename}' is not writable. 'Write' permissions must be corrected for installation.");
			}
		$filename = './incs/mysql.inc.php';				// 2/21/09

		$dir = "./";
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if (is_dir($filename)) {
			    $files[] = $filename;
			    }
			}

		$dirsOK = TRUE;
		if (!in_array("incs", $files)) 		{$dirsOK=FALSE;}
		if (!in_array("markers", $files)) 	{$dirsOK=FALSE;}

		if (!$dirsOK) {
			print "<br><br><br><center><h3>At least one of the Tickets subdirectories is missing, and this needs to be corrected.<br /><br />You might check into how the Tickets zip file was unzipped or otherwise installed.<br><br><br><br><A HREF='mailto:ejosterberg+TicketsInstallHelp@gmail.com?subject=Tickets Install Problem'><u>Or click here to contact the developer.</u></A></h3></center>";
			}
		else {
?>
			Complete this form to install Tickets version <?php print $version;?>. Current installed version: <B><?php print h(($detected_version == '') ? 'unknown' : $detected_version);?></B>. Make sure to read through the <A HREF="install.php?help=1"><U>help</U></A> information.<BR /><BR />
<?php if($admin_required && !$admin_ok) { ?>
			<FONT CLASS="warn">This system already matches installer version <?php print h($version);?>. You must be logged in as admin/super to run install/reinstall/upgrade/config actions.</FONT><BR /><BR />
<?php } ?>
<?php
			$install_label = ($detected_version == $version && $detected_version != '') ? 'Re-install Database (safe, no existing data dropped)' : 'Install Database - new (safe, no existing data dropped)';
?>
<?php
			if (extension_loaded('gd') && function_exists('gd_info')) {
				$gd = "<FONT COLOR='green'>GD Installed</FONT>&nbsp;&nbsp;<span style='font-family: wingdings; font-size: 100%; color: green;'>&nbsp;&nbsp;&#9745</span>";
				} else {
				$gd = "<FONT COLOR='red'>GD not Installed</FONT>&nbsp;&nbsp;<span style='font-size: 100%; color: red;'>X</span>";
				}

			if (strnatcmp(phpversion(),'5.2.0') >= 0) { 
				$php = "<FONT COLOR='green'>PHP 5.2 or higher is installed, this is OK, you have at least the recommended version</FONT>&nbsp;&nbsp;<span style='font-family: wingdings; font-size: 100%; color: green;'>&nbsp;&nbsp;&#9745</span>"; 
				} else { 
				$php = "<FONT COLOR='red'>PHP is less than version 5.2. You need to upgrade PHP to at least version 5.2.0</FONT>&nbsp;&nbsp;<span style='font-size: 100%; color: red;'>X</span>"; 
				} 
			
			$docRoot = substr(getenv("DOCUMENT_ROOT"), -4);
			$curr = getcwd() . "/";
			$curr_dir = substr($curr , -4);
			if ($docRoot == $curr_dir) {
				$dir = "<FONT COLOR='red'>You need to Install the application in a directory off the web root not in the web root itself.</FONT>&nbsp;&nbsp;<span style='font-size: 100%; color: red;'>X</span>";
				} else {
				$dir = "<FONT COLOR='green'>The installation location is OK.</FONT>&nbsp;&nbsp;<span style='font-family: wingdings; font-size: 100%; color: green;'>&nbsp;&nbsp;&#9745</span>";
				}
				
			ob_start(); 
			phpinfo(INFO_MODULES); 
			$info = ob_get_contents(); 
			ob_end_clean(); 
			$info = stristr($info, 'Client API version '); 
			preg_match('/[1-9].[0-9].[1-9]/', $info, $match); 
			$md = $match[0];
			
			if (strnatcmp($md,'5.0.0') >= 0) {
				$mysql = "<FONT COLOR='green'>MYSQL 5.0 or better is installed</FONT>&nbsp;&nbsp;<span style='font-family: wingdings; font-size: 100%; color: green;'>&nbsp;&nbsp;&#9745</span>"; 
				} else {
				$mysql = "<FONT COLOR='red'>MYSQL version is lower than 5.0</FONT>&nbsp;&nbsp;<span style='font-size: 100%; color: red;'>X</span>"; 	
				}
			?>
			<FORM NAME = 'install_frm' METHOD="post" ACTION="install.php?go=1"  onSubmit='return validate(document.install_frm)' >
			<FIELDSET style="width: 900px;"><LEGEND style="font-weight: bold; color: #000; font-family: verdana; font-size: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;From your MySQL installation&nbsp;&nbsp;&nbsp;&nbsp;</LEGEND>
			<TABLE BORDER="0">
			<TR CLASS="even"><TD width="200px">MySQL Host: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_host" VALUE="<?php print h($defaults['host']);?>"></TD></TR>
			<TR CLASS="odd"><TD>MySQL Username: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_user" VALUE="<?php print h($defaults['user']);?>"></TD></TR>
			<TR CLASS="even"><TD>MySQL Password: </TD><TD><INPUT TYPE="password" SIZE="45" MAXLENGTH="255" NAME="frm_db_password"  VALUE="<?php print h($defaults['password']);?>"></TD></TR>
			</TABLE>
			</FIELDSET>
			<br />
			<FIELDSET style="width: 900px;"><LEGEND style="font-weight: bold; color: #000; font-family: verdana; font-size: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;Tickets Stuff&nbsp;&nbsp;&nbsp;&nbsp;</LEGEND>
			<TABLE BORDER="0">
			<TR CLASS="even"><TD width="200px">MySQL Database: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_dbname" VALUE="<?php print h($defaults['db']);?>"> your just-created MySQL database</TD></TR>
			<TR CLASS="odd"><TD>MySQL Table Prefix (optional): </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_prefix" VALUE="<?php print h($defaults['prefix']);?>"> your choice</TD></TR>
			<!-- 4/2/2013 -->
			<TR CLASS="even"><TD>Google API Key (optional):<BR /></TD><TD><INPUT TYPE="text" SIZE="70" MAXLENGTH="255" NAME="frm_api_key"  VALUE=""><BR>
				&nbsp;&nbsp;&nbsp;&nbsp;Note: You may obtain your site's API key at https://code.google.com/apis/console/
				</TD></TR>
			<TR CLASS="odd"><TD>First Admin Username: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="64" NAME="frm_admin_user" VALUE="admin"> letters/numbers . _ @ - only</TD></TR>
			<TR CLASS="even"><TD>First Admin Password: </TD><TD><INPUT TYPE="password" SIZE="45" MAXLENGTH="255" NAME="frm_admin_pass" VALUE=""> minimum 8 chars, stored with bcrypt</TD></TR>
			<TR CLASS="odd"><TD>Install Option: </TD><TD>
			<INPUT TYPE="radio" VALUE="install" NAME="frm_option" checked> <?php print $install_label;?><BR />
			<INPUT TYPE="radio" VALUE="upgrade" NAME="frm_option"> Upgrade Existing TicketsCAD (keeps existing data)<BR />
			<INPUT TYPE="radio" VALUE="install-drop" NAME="frm_option"> Re-install Database (drops Tickets tables)<BR />
			<INPUT TYPE="radio" VALUE="writeconf" NAME="frm_option"> Write Configuration File Only<BR /><BR>
			</TD></TR>
			<TR CLASS="even"><TD></TD><TD><INPUT TYPE="Reset" VALUE="Reset form">&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Submit" ID='do_it_btn' VALUE="Do it" <?php if($admin_required && !$admin_ok) print 'disabled';?>><DIV id='busy_indicator' class='busy-indicator'><SPAN class='spinner'></SPAN>Installer is running, please wait...</DIV></TD></TR>
			</TABLE>
			</FORM>
			<?php
			}
		}
?>
</BODY></HTML>
