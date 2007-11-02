<?php 
	require_once('functions.inc.php');
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
<FRAMESET ROWS="<?php print get_variable('framesize')+24;?>,*" BORDER="<?php print get_variable('frameborder')-1;?>">
	<FRAME SRC="top.php" NAME="upper" SCROLLING="no">
	<FRAME SRC="main.php" NAME="main">
	<NOFRAMES>
	<BODY>
		Tickets requires a frame capable browser.
	</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>
