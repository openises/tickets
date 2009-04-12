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
*/
error_reporting(E_ALL);		// 10/1/08
require_once('./incs/functions.inc.php');

//	$old_version = get_variable('_version');
//
//	$version = "2.10 E beta";			// see usage below 1/30/09
//	
//	if (!($version == $old_version)) {		// current?
//		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($version)." WHERE `name`='_version' LIMIT 1";	// 5/28/08
//		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//		unset ($result);
//		}			// end (!($version ==...)
	

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
	<TITLE>Tickets <?php print get_variable('_version');?></TITLE>
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<link rel="shortcut icon" href="favicon.ico">
</HEAD>
<?php

$temp = get_variable('call_board');		// 1/11/19
$call1 = ($temp>1)? ", " . $temp : ""; 
$call2 = ($temp>1)? "<FRAME SRC='assigns.php' NAME='calls' SCROLLING='yes'>\n": "";		// optional callboard frame
?>
	<FRAMESET ROWS="<?php print get_variable('framesize')+25;?><?php print $call1; ?>,*" BORDER="<?php print get_variable('frameborder');?>">
	<FRAME SRC="top.php" NAME="upper" SCROLLING="no">
	<?php print $call2; ?>
	<FRAME SRC="main.php" NAME="main">
	<NOFRAMES>
	<BODY>
		Tickets requires a frame capable browser.
	</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>