<?php 
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
*/
error_reporting(E_ALL);		// 10/1/08
require_once('./incs/functions.inc.php');

$cb_per_line = 20;				// 6/5/09
$cb_fixed_part = 60;
$cb_min = 96;
$cb_max = 240;

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

do_setting ('smtp_acct','');			// 7/7/09  
do_setting ('email_from','');			// 7/7/09
do_setting ('gtrack_url','');			// 7/7/09
do_setting ('maptype','1');				// 8/2/09
do_setting ('locale','0');				// 8/3/09
do_setting ('func_key1','http://openises.sourceforge.net/,Open ISES');				// 8/5/09
do_setting ('func_key2','');				// 8/5/09
do_setting ('func_key3','');				// 8/5/09
do_setting ('reverse_geo','0');				// 11/1/09

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

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `hide` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `description` ;";
$result = mysql_query($query);		// 10/21/09

$old_version = get_variable('_version');

$version = "2.11 B beta";			// 11/11/09

if (!($version == $old_version)) {		// current?
	$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($version)." WHERE `name`='_version' LIMIT 1";	// 5/28/08
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	unset ($result);
	}			// end (!($version ==...)


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD>
	<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<TITLE>Tickets <?php print get_variable('_version');?></TITLE>
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<link rel="shortcut icon" href="favicon.ico">
</HEAD>

<?php			// 7/14/09

$cb_rows = $cb_frame = "";				// initialize to empty
$temp = get_variable('call_board');		// 1/11/19
$cb_frame =  ($temp>1)? "<FRAME SRC='assigns.php' NAME='calls' SCROLLING='AUTO'>\n": "";		// optional callboard frame
if ($temp==2) {

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";	// 6/15/09
	$result = @mysql_query($query);
	$lines = mysql_affected_rows();
	unset($result);
	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;
	$height = ($height>$cb_max)? $cb_max: $height;		// 6/15/09
	$cb_rows = ", " . $height; 
	}
else {
	$height = $temp;
	$cb_rows = ($temp>2)? ", " . $height: "";
	}
?>
	<FRAMESET ID = 'the_frames' ROWS="<?php print get_variable('framesize')+25;?><?php print $cb_rows; ?>,*" BORDER="<?php print get_variable('frameborder');?>">
	<FRAME SRC="top.php" NAME="upper" SCROLLING="no">
	<?php print $cb_frame; ?>
	<FRAME SRC="main.php" NAME="main" >

	<NOFRAMES>
	<BODY>
		Tickets requires a frames-capable browser.
	</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>
