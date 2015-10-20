<?php

require_once './incs/functions.inc.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Twitter Timeline</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
</HEAD>
<BODY>
	<DIV id='outer' class='even' style='height: 100%;'>
		<SPAN class='heading' style='display: block; width: 100%; text-align: center;'>Twitter Timeline</SPAN>
		<SPAN class='header' style='display: block; width: 100%; font-size: .9em;'>Received Direct Messages</SPAN>
		<DIV style="height: 120px; overflow-y: scroll;"><?php echo show_rec_direc(20);?></DIV><BR />
		<SPAN class='header' style='display: block; width: 100%; font-size: .9em;'>Sent Direct Messages</SPAN>
		<DIV style="height: 120px; overflow-y: scroll;"><?php echo show_sent_direc(20);?></DIV><BR />
		<SPAN class='header' style='display: block; width: 100%; font-size: .9em;'>Timeline</SPAN>
		<DIV style="height: 220px; overflow-y: scroll;"><?php echo show_tweets();?></DIV><BR />
		<CENTER><SPAN id='close_but' class='plain' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN></CENTER>
	</DIV>
</BODY>
