<?php
/*
3/18/09 added direcs
3/22/09 remove terrain, add version settings update
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		// 10/1/08

@session_start();
require_once($_SESSION['fip']);		//7/28/10

$old_version = get_variable('_version'); 
$from_version = "2.10 D beta";
//$from_version = "2.10 D betaZ";
$this_version = "2.10 E beta";

if (!(trim($old_version)==trim($from_version) )) {
	die ("This script upgrades *only* version '{$from_version}' - but has detected version '{$old_version}' in database '{$mysql_db}'.\n<br />\n<br />Please correct.");
	}
				// 3/18/09
$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `direcs` TINYINT( 2 ) NOT NULL DEFAULT '1' COMMENT '0=>no directions, 1=> yes' AFTER `mobile` ;";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `instam` TINYINT( 2 ) NOT NULL DEFAULT '0' COMMENT 'instamapper' AFTER `aprs` ;";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `name` = 'auto_poll' WHERE `settings`.`name` ='aprs_poll' LIMIT 1 ;";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value` = '{$this_version}' WHERE `settings`.`name` ='_version' LIMIT 1 ;";	// 3/22/09
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

$query	= "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `utc_stamp` BIGINT( 12 ) NOT NULL DEFAULT '0' COMMENT 'Position timestamp in UTC' AFTER `altitude` ;";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

$query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` (`id`, `name`, `value`) VALUES 
		(NULL, 'quick', '1'),
		(NULL, 'msg_text_1', ''),
		(NULL, 'msg_text_2', ''),
		(NULL, 'msg_text_3', ''),
		(NULL, 'situ_refr', '120'),
		(NULL, 'instamapper', '0');";

$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META NAME="Author" CONTENT="A. Shore">
<META NAME="Description" CONTENT="Tickets Version Upgrade Script - <?php print $old_version; ?> to <?php print $this_version; ?>">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
</HEAD>
<BODY>
<BR /><BR /><BR /><CENTER>
<H3>Tickets Version Upgrade:  '<?php print $old_version; ?>' to '<?php print $this_version; ?>' with database '<?php print $mysql_db; ?>'  completed.
<BR /><BR />
<SPAN onClick = "location.href='index.php'"><U>Start</U></SPAN></H3>
</CENTER>

</BODY>
</HTML>
