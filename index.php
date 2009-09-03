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
9/1/09	corrections re php deprecated functions, only
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


$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `multi` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'if 1, allow multiple call assigns' AFTER `direcs`;";
$result = @mysql_query($query);			// 7/7/09

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `protocol` VARCHAR( 255 ) NULL AFTER `description` ;";
$result = @mysql_query($query);			// 7/7/09

$query	= "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `utc_stamp` BIGINT( 12 ) NOT NULL DEFAULT '0' COMMENT 'Position timestamp in UTC' AFTER `altitude` ;";
$result = @mysql_query($query);

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `locatea` TINYINT( 2 ) NOT NULL DEFAULT '0' COMMENT 'if 1 unit uses LocateA tracking - required to set callsign' AFTER `instam`;";
$result = @mysql_query($query);			// 7/29/09

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `gtrack` TINYINT( 2 ) NOT NULL DEFAULT '0' COMMENT 'if 1 unit uses Gtrack tracking - required to set callsign' AFTER `locatea`;";
$result = @mysql_query($query);			// 7/29/09

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `glat` TINYINT( 2 ) NOT NULL DEFAULT '0' COMMENT 'if 1 unit uses Google Latitude tracking - required to set callsign' AFTER `gtrack`;";
$result = @mysql_query($query);			// 7/29/09

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `handle` VARCHAR( 24 ) NULL DEFAULT 'NULL' COMMENT 'Unit Handle' AFTER `callsign`;";
$result = @mysql_query($query);			// 7/29/09

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `radius` INT( 4 ) NOT NULL DEFAULT '0' COMMENT 'enclosing circle',
			ADD `color` VARCHAR( 8 ) NULL DEFAULT NULL ,
			ADD `opacity` INT( 3 ) NOT NULL DEFAULT '0';";
$result = @mysql_query($query);			// 8/19/09

$old_version = get_variable('_version');

$version = "2.10 G beta";			// see usage below 7/21/09

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
