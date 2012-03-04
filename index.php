<?php 
error_reporting(E_ALL);		// 10/1/08
if(!(file_exists("./incs/mysql.inc.php"))) {
	print "This appears to be a new Tickets installation; file 'mysql.inc.inc' absent. Please run <a href=\"install.php\">install.php</a> with valid database configuration information.";
	exit();
	}

require_once('./incs/functions.inc.php');	

$version = "2.20 D beta - 3/2/12";	

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
3/2/12 Version number change
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

function table_exists($name) {			//	3/15/11
	$tablename = "$GLOBALS[mysql_prefix]" . "$name";
	$query 	= "SELECT COUNT(*) FROM $tablename";
      	$result 	= mysql_query($query);
	$num_rows 	= @mysql_num_rows($result);
	return $num_rows;
	}

function do_insert_day_colors($name,$value) {			//	3/15/11
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]css_day` (name,value) VALUES('$name','$value')";
	$result = mysql_query($query) or die("DO_INSERT_DAY_COLORS($name,$value) failed, execution halted");
	}

function do_insert_night_colors($name,$value) {			//	3/15/11
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]css_night` (name,value) VALUES('$name','$value')";
	$result = mysql_query($query) or die("DO_INSERT_NIGHT_COLORS($name,$value) failed, execution halted");
	}

if (table_exists("css_day") == 0) {			//	3/15/11
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


if (table_exists("css_night") == 0) {			//	3/15/11
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

function do_caption ($temp) { 				// adds a 'captions' table entry - 12/4/10
	$caption = quote_smart($temp);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = $caption LIMIT 1;";	// 11/30/10
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
	if (mysql_affected_rows()==0) {	
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES ( $caption, $caption);";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
		}
	return;
	}
	
function do_setting ($which, $what) {				// 7/7/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";		// 5/25/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` ( `id` , `name` , `value` ) VALUES (NULL , '$which', '$what');";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	unset ($result);
	return TRUE;
	}				// end function do_setting ()

function update_setting ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '$which'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	unset ($result);
	return TRUE;
	}				// end function update_setting ()
	
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}

$old_version = get_variable('_version');

if (!($version == $old_version)) {		// current? - 5/19/10 ==================================================
	
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
	//	do_setting ('unit_status_chg','0');			// 7/21/10
		do_setting ('group_or_dispatch','0');			// 12/16/10
		do_setting ('title_string','');			// 12/16/10		
		
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]facilities` (`id` bigint(8) NOT NULL auto_increment, `name` text, `direcs` tinyint(2) NOT NULL default '1' COMMENT '0=>no directions, 1=> yes', `description` text NOT NULL, `capab` varchar(255) default NULL COMMENT 'Capability', `status_id` int(4) NOT NULL default '0', `other` varchar(96) default NULL, `handle` varchar(24) default NULL, `contact_name` varchar(64) default NULL, `contact_email` varchar(64) default NULL, `contact_phone` varchar(15) default NULL, `security_contact` varchar(64) default NULL, `security_email` varchar(64) default NULL, `security_phone` varchar(15) default NULL, `opening_hours` mediumtext, `access_rules` mediumtext, `security_reqs` mediumtext, `pager_p` varchar(64) default NULL, `pager_s` varchar(64) default NULL, `send_no` varchar(64) default NULL, `lat` double default NULL, `lng` double default NULL, `type` tinyint(1) default NULL, `updated` datetime default NULL, `user_id` int(4) default NULL, `callsign` varchar(24) default NULL, `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL, `_on` datetime NOT NULL, PRIMARY KEY  (`id`), UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1 AUTO_INCREMENT=43;";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);			// 7/7/09
		
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fac_types` (`id` int(11) NOT NULL auto_increment, `name` varchar(16) NOT NULL, `description` varchar(48) NOT NULL, `icon` int(3) NOT NULL default '0', `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL COMMENT 'ip', `_on` datetime NOT NULL, PRIMARY KEY  (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COMMENT='Allows for variable facility types' AUTO_INCREMENT=19;";
		$result = mysql_query($query);		// 7/7/09
		
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fac_status` (`id` bigint(4) NOT NULL auto_increment, `status_val` varchar(20) NOT NULL, `description` varchar(60) NOT NULL, `group` varchar(20) default NULL, `sort` int(11) NOT NULL default '0', `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL, `_on` datetime NOT NULL,  PRIMARY KEY  (`id`), UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 AUTO_INCREMENT=4;";
		$result = mysql_query($query);			// 7/7/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `facility` INT( 4 ) NULL DEFAULT NULL AFTER `phone`;";
		$result = mysql_query($query);		// 8/1/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `rec_facility` INT( 4 ) NULL DEFAULT NULL AFTER `facility`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `booked_date` DATETIME NULL DEFAULT NULL AFTER `updated`;";
		$result = mysql_query($query);			// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `facility` INT(7) NULL DEFAULT NULL AFTER `info`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `rec_facility` INT(7) NULL DEFAULT NULL AFTER `facility`;";
		$result = mysql_query($query);			// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `mileage` INT(8) NULL DEFAULT NULL AFTER `rec_facility`;";
		$result = mysql_query($query);			// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `facility_id` INT(8) NULL DEFAULT NULL AFTER `on_scene`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `rec_facility_id` INT(8) NULL DEFAULT NULL AFTER `facility_id`;";
		$result = mysql_query($query);			// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` CHANGE `comments` TEXT;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `u2fenr` DATETIME NULL DEFAULT NULL AFTER `rec_facility_id`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `u2farr` DATETIME NULL DEFAULT NULL AFTER `u2fenr`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `start_miles` INT(8) NULL DEFAULT NULL AFTER `comments`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `end_miles` INT(8) NULL DEFAULT NULL AFTER `start_miles`;";
		$result = mysql_query($query);		// 10/6/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `multi` INT( 1 ) NOT NULL DEFAULT 0 COMMENT 'if 1, allow multiple call assigns' AFTER `direcs`;";
		$result = mysql_query($query);			// 7/7/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `protocol` VARCHAR( 255 ) NULL AFTER `description` ;";
		$result = mysql_query($query);			// 7/7/09
		
		$query	= "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `utc_stamp` BIGINT( 12 ) NOT NULL DEFAULT 0 COMMENT 'Position timestamp in UTC' AFTER `altitude` ;";
		$result = mysql_query($query);
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `locatea` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses LocateA tracking - required to set callsign' AFTER `instam`;";
		$result = mysql_query($query);		// 7/29/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `gtrack` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Gtrack tracking - required to set callsign' AFTER `locatea`;";
		$result = mysql_query($query);		// 7/29/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `glat` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Google Latitude tracking - required to set callsign' AFTER `gtrack`;";
		$result = mysql_query($query);			// 7/29/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `handle` VARCHAR( 24 ) NULL DEFAULT NULL COMMENT 'Unit Handle' AFTER `callsign`;";
		$result = mysql_query($query);		// 7/29/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `radius` INT( 4 ) NOT NULL DEFAULT 0 COMMENT 'enclosing circle',
					ADD `color` VARCHAR( 8 ) NULL DEFAULT NULL ,
					ADD `opacity` INT( 3 ) NOT NULL DEFAULT '0';";
		$result = mysql_query($query);			// 8/19/09
		
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]chat_invites` ( `id` int(7) NOT NULL AUTO_INCREMENT, `to` varchar(64) NOT NULL COMMENT 'comma sep''d, 0 = all', `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL, `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1;"; // 12/23/09
		$result = mysql_query($query);		// 10/21/09
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `hide` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `description` ;";
		$result = mysql_query($query);		// 10/21/09
					
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `_by` INT( 7 ) NOT  NULL DEFAULT '0' COMMENT 'Call taker id' ";
		$result = mysql_query($query);		//1/3/10
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `expires` TIMESTAMP NULL  DEFAULT NULL COMMENT 'session start time';";
		$result = mysql_query($query);		// 1/8/10
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `sid` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'php session id';";
		$result = mysql_query($query);		// 1/8/10
			
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `login` TIMESTAMP NULL COMMENT 'last login';";
		$result = mysql_query($query);		// 1/23/10
			
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `_from` VARCHAR( 24 ) NULL COMMENT 'IP addr';";
		$result = mysql_query($query);		// 1/23/10
			
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user`  ADD `browser` VARCHAR( 40 ) NULL COMMENT 'used at last login';";
		$result = mysql_query($query);		// 1/23/10
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `bg_color` VARCHAR( 16 ) NOT NULL DEFAULT 'transparent' COMMENT 'background color',
			ADD `text_color` VARCHAR( 16 ) NOT NULL DEFAULT '#000000' COMMENT 'text color'";
		$result = mysql_query($query);		// 2/4/10
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_status` ADD `bg_color` VARCHAR( 16 ) NOT NULL DEFAULT 'transparent' AFTER `sort` ,
			ADD `text_color` VARCHAR( 16 ) NOT NULL DEFAULT '#000000' AFTER `bg_color`";
		$result = mysql_query($query);		// 2/4/10
		
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
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10
		
		//			// 3/24/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` 
			CHANGE `type` `type` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			CHANGE `description` `description` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			CHANGE `sort` `sort` INT( 11 ) NULL DEFAULT NULL ,
			CHANGE `radius` `radius` INT( 4 ) NULL DEFAULT NULL ,
			CHANGE `opacity` `opacity` INT( 3 ) NULL DEFAULT NULL";	
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` CHANGE `description` `description` VARCHAR( 60 ) 
			CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			CHANGE `sort` `sort` INT( 11 ) NOT NULL DEFAULT '0'";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/24/10
		
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]pin_ctrl` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `responder_id` int(7) NOT NULL DEFAULT '0' COMMENT 'link to responder record',
			  `pin` varchar(4) NOT NULL COMMENT 'login authentication ',
			  `_by` int(7) NOT NULL COMMENT 'user creating/updating this entry',
			  `_from` varchar(30) DEFAULT NULL COMMENT 'IP address',
			  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$result = mysql_query($query);		// 4/11/10
												// 4/30/10
		/* query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_2` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `phone` ,
			ADD `phone_3` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `phone_2` ,
			ADD `phone_4` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `phone_3` ,
			ADD INDEX ( `phone_2` , `phone_3` , `phone_4` ) ";
		$result = mysql_query($query);
		*/
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` CHANGE `id` `id` BIGINT( 7 ) NOT NULL AUTO_INCREMENT ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `contact` 		VARCHAR(48) NULL DEFAULT NULL AFTER `id` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `street` 		VARCHAR(48) NULL DEFAULT NULL AFTER `contact` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `apartment` 		VARCHAR(48) NULL DEFAULT NULL AFTER `street` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `city` 			VARCHAR(48) NULL DEFAULT NULL AFTER `apartment` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `state` 			char(2) 	NULL DEFAULT NULL AFTER `city` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `miscellaneous` 	VARCHAR(80) NULL DEFAULT NULL AFTER `state` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone` 			VARCHAR(16) NULL DEFAULT NULL AFTER `miscellaneous` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_2` 		VARCHAR(16) NULL DEFAULT NULL AFTER `phone` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_3` 		VARCHAR(16) NULL DEFAULT NULL AFTER `phone_2` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_4` 		VARCHAR(16) NULL DEFAULT NULL AFTER `phone_3` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `email` 			VARCHAR(48) NULL DEFAULT NULL AFTER `phone_4` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `lat` 			double 		NULL DEFAULT NULL AFTER `email` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `lng` 			double 		NULL DEFAULT NULL AFTER `lat` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `updated` 		VARCHAR(16) NULL DEFAULT NULL AFTER `lng` ";
		$result = mysql_query($query);		// 5/11/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `_by` 			int(7) 		NULL DEFAULT NULL AFTER `updated` ";
		$result = mysql_query($query);		// 5/11/10
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `responder_id` INT( 7 ) NOT NULL DEFAULT '0' COMMENT 'For level = unit' AFTER `level` ";		// 5/4/10
		$result = mysql_query($query);			// 10/6/09
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]chat_messages` CHANGE `message` `message` VARCHAR( 2048 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ";
		$result = mysql_query($query);		// 5/29/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `dispatch` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0 - can dispatch, 1- no - inform, 2 - enforce' AFTER `description`";
		$result = mysql_query($query);		// 5/30/10
	
		do_setting ('sound_wav','aooga.wav');			// 6/12/10
		do_setting ('sound_mp3','phonesring.mp3');		// 6/12/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` 
			CHANGE `facility` `facility` INT( 4 ) NULL DEFAULT '0',
			CHANGE `rec_facility` `rec_facility` INT( 4 ) NULL DEFAULT '0'";
		$result = mysql_query($query);		// 6/20/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `_by` `_by` INT( 7 ) NULL DEFAULT NULL";
		$result = mysql_query($query);				// 6/20/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` 
			CHANGE `_by` `_by` INT( 7 ) NULL DEFAULT NULL ,
			CHANGE `_from` `_from` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
			CHANGE `_on` `_on` DATETIME NULL DEFAULT NULL" ;		// 6/20/10
		$result = mysql_query($query);		// 2/4/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` CHANGE `dob` `dob` TEXT NULL DEFAULT NULL ";		// 6/25/10
		$result = mysql_query($query);	
																// 6/26/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `set_severity` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'sets incident severity' AFTER `protocol`";
		$result = mysql_query($query);	
																// 6/27/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `nine_one_one` VARCHAR( 96 ) NULL DEFAULT NULL COMMENT 'comments re 911' AFTER `comments` ";
		$result = mysql_query($query);	
	
	// AH 7/6/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `street` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `name` ";
		$result = mysql_query($query);
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `city` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `street`;";
		$result = mysql_query($query);		// 7/5/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `state` CHAR( 2 ) NULL DEFAULT NULL AFTER `city`;";
		$result = mysql_query($query);		// 7/5/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `phone` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `state`;";
		$result = mysql_query($query);		// 7/5/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `street` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `name`;";
		$result = mysql_query($query);		// 7/5/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `city` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `street`;";
		$result = mysql_query($query);		// 7/5/10
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `state` CHAR( 2 ) NULL DEFAULT NULL AFTER `city`;";
		$result = mysql_query($query);		// 7/5/10
	
	// AH
	
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]settings` WHERE `settings`.`name` = 'unit_status_chg' LIMIT 1;";
		$result = mysql_query($query);		// 7/21/10
	
		$the_table = "$GLOBALS[mysql_prefix]captions";
	
		if (!(mysql_table_exists($the_table))) {					// 8/13/10, 8/25/10
							
			$query = "CREATE TABLE IF NOT EXISTS `{$the_table}` (
				  `id` int(7) NOT NULL AUTO_INCREMENT,
				  `capt` varchar(36) NOT NULL,
				  `repl` varchar(36) NOT NULL,
				  `_by` int(7) NOT NULL DEFAULT '0',
				  `_from` varchar(16) NOT NULL DEFAULT '''''',
				  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10	
	
			require_once ("./incs/capts.inc.php");		// get_text captions as an array 8/17/10
														//
			for ($i=0; $i< count($capts); $i++) {		// 8/29/10
				$temp = quote_smart($capts[$i]);
		
				$query = "INSERT INTO `{$the_table}` (`capt`, `repl`) VALUES ($temp, $temp);";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}
			
			unset ($result);
	
			}	// end if (!(mysql_table_exists($the_table))
									// 10/19/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` CHANGE `info` `info` VARCHAR( 256 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
	do_setting ('internet','1');						// 8/5/10 - just in case
	do_setting ('disp_stat','D/R/O/FE/FA/Clear');		// 8/29/10 - dispatch status tags
	do_setting ('oper_can_edit','0');					// 8/27/10  
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_types` CHANGE `name` `name` VARCHAR(48) NOT NULL ";
	$result = mysql_query($query);		// 10/31/10
		
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_types` CHANGE `description` `description` VARCHAR(96) NOT NULL ";
	$result = mysql_query($query);		// 10/31/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `street` `street` VARCHAR( 96 )  NULL DEFAULT NULL";
	$result = mysql_query($query);		// 11/5/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `state` `state` CHAR( 4 )  NULL DEFAULT NULL";	// 11/23/10
	$result = mysql_query($query);		// 11/5/10
					// 3/30/11 dummy "}"
	$left_br = "{";										
	$the_year = date("y");								// 2-digit year - restart numbers at yr rollover 
	$the_inc_num = trim(get_variable('_inc_num'));		// possibly empty
	
	if (!(strlen($the_inc_num)>0)) {
		do_setting ('_inc_num',base64_encode(serialize(array("0", "", "", "", "0", $the_year))));		// insert if absent
		update_setting ('_inc_num', base64_encode(serialize(array("0", "", "", "", "0", $the_year))));	// it's there now, update it
		}
	else {									// exists, not-empty
		if(strpos($the_inc_num, $left_br)) {		// if unencoded - else ignore
//			snap(__LINE__, $the_inc_num);
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
				
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10	
	
			$query = "INSERT INTO `{$the_table}` (`code`, `text`, `sort`) VALUES
				('ex-1', 'Instructed to return to station ASAP', 999),
				('ex-2', 'Requested to contact Dispatch Central by voice', 999);";
		
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10	
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
				
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		
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
		
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}				// end if (!(mysql_table_exists()))
	
																						// 11/30/10
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]captions` 
			CHANGE `_by` `_by` INT( 7 ) NOT NULL DEFAULT '0',
			CHANGE `_from` `_from` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL; ";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10
		
		$nature = get_text("Nature");			// 3/30/11
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
		$result = mysql_query($query);		// 8/1/09
		
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]places` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `name` varchar(64) DEFAULT NULL,
			  `lat` float DEFAULT '0',
			  `lon` float DEFAULT '0',
			  `zoom` int(2) DEFAULT '7',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";		//	03/01/11
			
		$result = mysql_query($query);		//	03/01/11
	
		$query = "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]group_test`";
		$result = mysql_query($query);		// 6/10/11	
		
		$query = "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]group`";
		$result = mysql_query($query);		// 6/10/11	

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `group` INT( 3 ) NOT NULL DEFAULT 0 AFTER `id` ;";
		$result = mysql_query($query);		// 6/10/11	

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `group` INT( 3 ) NOT NULL DEFAULT 0 AFTER `id` ;";
		$result = mysql_query($query);		// 6/10/11

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `group` INT( 3 ) NOT NULL DEFAULT 0 AFTER `id` ;";
		$result = mysql_query($query);		// 6/10/11

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `group` VARCHAR( 64 ) NOT NULL DEFAULT 0 AFTER `id` ;";
		$result = mysql_query($query);		// 6/10/11
	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `group` INT( 3 ) NOT NULL DEFAULT 0 AFTER `id` ;";
		$result = mysql_query($query);		// 6/10/11	
	
		do_setting ('aprs_fi_key','');			// 4/15/11
												// 4/19/11
												
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` CHANGE `info` `info` VARCHAR( 2048 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11	

											// 4/22/11	
		$query_alter = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `icon_str` CHAR( 3 ) NULL DEFAULT NULL COMMENT 'map icon value' AFTER `handle` ";
		$result = @mysql_query($query_alter);		
		$query_alter = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `icon_str` CHAR( 3 ) NULL DEFAULT NULL COMMENT 'map icon value' AFTER `handle` ";
		$result = @mysql_query($query_alter);			
	
		$query_update = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `handle`= SUBSTR(`description`,1,24) WHERE ((`handle` = '') OR (`handle` IS NULL));";
		$result = mysql_query($query_update) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	4/5/11	
	
		$query_update = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET `handle`= SUBSTR(`description`,1,24) WHERE ((`handle` = '') OR (`handle` IS NULL));";
		$result = mysql_query($query_update) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	4/5/11	
	
		$tables = array("$GLOBALS[mysql_prefix]responder", "$GLOBALS[mysql_prefix]facilities");		// 4/27/11	
		for ($i=0; $i< count($tables); $i++) {
			$query = "SELECT * FROM `{$tables[$i]}`";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 7/7/10
				if ( ($row['icon_str'] == "") || is_null($row['icon_str']) ) {
					$temp = explode("/", $row['name']);
					$icon_val = trim(substr($temp[count($temp) -1], -3, strlen($temp[count($temp) -1])));	
					
					$query2 = "UPDATE `{$tables[$i]}` SET `icon_str` = '$icon_val' WHERE `id` = {$row['id']} LIMIT 1";
					$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					}
				}		// end inner while()
			}		// end outer while()
	
		$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `handle` = REPLACE(`handle`, '\r', ' '),
			`handle` = REPLACE(`handle`, '\n', ' '),
			`handle` = REPLACE(`handle`, '  ', ' ')";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10

		$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET `handle` = REPLACE(`handle`, '\r', ' '),
			`handle` = REPLACE(`handle`, '\n', ' '),
			`handle` = REPLACE(`handle`, '  ', ' ')";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10

		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($version)." WHERE `name`='_version' LIMIT 1";	// 5/28/08
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

					// 6/22/11
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` DROP `group` ";		//	6/10/11
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` DROP `group` ";		//	6/10/11
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` DROP `group` ";		//	6/10/11
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` DROP `group` ";		//	6/10/11
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` DROP `group` ";		//	6/10/11
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `t_tracker` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses LocateA tracking - required to set callsign' AFTER `instam`;";
		$result = mysql_query($query);		// 6/10/11	

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
		$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11	

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` CHANGE `utc_stamp` `utc_stamp` BIGINT( 12 ) NULL DEFAULT NULL ";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		
					// 6/22/11
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]patient` ADD `fullname` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `name` ,
				ADD `dob` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `fullname` ,
				ADD `gender` INT( 1 ) NOT NULL DEFAULT '0' AFTER `dob` ,
				ADD `insurance_id` INT (3) NOT NULL DEFAULT '0' COMMENT 'see table insurance' AFTER `gender` ,
				ADD `facility_contact` VARCHAR( 64 ) NOT NULL AFTER `insurance_id`,
				ADD `facility_id` INT( 3 ) NOT NULL DEFAULT '0' AFTER `facility_contact`";
		$result = mysql_query($query);	
					// 6/22/11
		$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]insurance` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `ins_value` varchar(64) NOT NULL,
			  `sort_order` int(3) NOT NULL DEFAULT '0',
			  `_by` int(7) NOT NULL,
			  `_from` varchar(16) DEFAULT NULL,
			  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					// 6/22/11

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]insurance` (`ins_value` ,`sort_order` ,`_by` ,`_from` ,`_on`)
			VALUES ( 'Example', '0', '0', NULL ,CURRENT_TIMESTAMP);";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					
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
		$result = mysql_query($query);		// 7/29/09

		do_setting ('ogts_info','');			// 7/5/11

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `closest_city` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `status`";
		$result = mysql_query($query);
		
		if (table_exists("mmarkup") == 0) {	//	6/10/11
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
																					// 8/2/11
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

			$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));		
			$query_insert = "INSERT INTO `$GLOBALS[mysql_prefix]mmarkup_cats` (`id`, `category`, `_by`, `_from`, `_on`) VALUES
			(1, 'Region Boundary', '1', 'install routine', '$now'),
			(2, 'Banners', '1', 'install routine', '$now'),
			(3, 'Facility Catchment', '1', 'install routine', '$now'),
			(4, 'Ring Fence', '1', 'install routine', '$now'),
			(5, 'Exclusion Zone', '1', 'install routine', '$now');";
			$result_insert = mysql_query($query_insert) or do_error($query_insert , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11	
		}
		if (table_exists("stats_type") == 0) {	//	6/10/11		
			$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]stats_type` (
			`st_id` int(2) NOT NULL AUTO_INCREMENT,
			`name` varchar(64) NOT NULL,
			`stat_type` varchar(3) NOT NULL DEFAULT 'int',
			PRIMARY KEY (`st_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11			
			if($result) {
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
				$result_insert = mysql_query($query_insert) or do_error($query_insert , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11					
			}

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
			$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11	
		}
		
		$query_alter = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `ring_fence` INT( 3 ) NOT NULL DEFAULT '0' AFTER `t_tracker` ";
		$result = @mysql_query($query_alter);			

		$query_alter = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `excl_zone` INT( 3 ) NOT NULL DEFAULT '0' AFTER `ring_fence` ";
		$result = @mysql_query($query_alter);	
		
		$query_alter = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `boundary` INT( 3 ) NOT NULL DEFAULT '0' AFTER `icon_str` ";
		$result = @mysql_query($query_alter);	

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` CHANGE `state` `state` CHAR( 4 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
		$result = mysql_query($query);		// 7/30/11

		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` CHANGE `state` `state` CHAR( 4 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
		$result = mysql_query($query);		// 7/30/11
		
		$query = "ALTER TABLE `$GLOBALS[mysql_prefix]settings` CHANGE `value` `value` VARCHAR( 512 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;";
		$result = mysql_query($query);		// 9/26/11		
		}		// end (!($version ==...) ==================================================			

	function update_disp_stat ($which, $what, $old) {		//	10/26/11
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' AND `value` = '$old' LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if ((mysql_affected_rows())!=0) {
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '$which'";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}
		unset ($result);
		return TRUE;
		}				// end function update_setting ()
	
	update_disp_stat ('disp_stat','D/R/O/FE/FA/Clear','D/R/O/Clear');		// 10/26/11				
		
	if (table_exists("region") == 0) {	//	6/10/11
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

	if (table_exists("region_type") == 0) {	//	6/10/11
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

	if (table_exists("allocates") == 0) {	//	6/10/11
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
		
	$temp = explode(" ", get_variable('_version'));	
	$disp_version = $temp[0];
	if (table_exists("stats_type") == 1) {	//	6/10/11		
		$query_truncate = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]stats_type`;";		//	6/10/11
		$result_truncate = mysql_query($query_truncate);
		
		if($result_truncate) {
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
						(11, 'Average time to close ticket', 'avg');";
			$result_insert = mysql_query($query_insert) or do_error($query_insert , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11	
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
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<TITLE>Tickets <?php print $disp_version;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="shortcut icon" href="favicon.ico" />
</HEAD>

<?php			// 7/14/09
//	cache buster and logout from statistics module.
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
	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
<?php 
	}
else  {
?>
	<FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, *" BORDER="<?php print get_variable('frameborder');?>">
	<FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
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
