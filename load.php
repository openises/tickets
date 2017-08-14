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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
BODY {FONT-SIZE: 2vw;}
.but_container{border: 1px solid black; text-align: center; position: fixed; top: 0px; left: 0px; width: 99%; z-index: 999; padding: 5px; background-color: rgb(0%,0%,0%); background-color: rgba(0%, 0%, 0%, 0.5);}
.plain {float: right; vertical-align: middle; display: inline-block; width: 100px; font-size: 12px;}
.hover {float: right; vertical-align: middle; display: inline-block; width: 100px; font-size: 12px;}
</STYLE>
<SCRIPT SRC="./js/misc_function.js" TYPE="application/x-javascript"></SCRIPT>
</HEAD>
<BODY>
	<DIV id='outer' class='even' style='height: 90%; padding: 10px;'>
		<DIV id='header' class='but_container'>
			<SPAN class='heading' style='float: none; display: inline-block; text-align: center; vertical-align: middle; font-size: 1.3em;'>Twitter Home</SPAN>
			<SPAN id='close_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
			<SPAN id='print_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'>Print</SPAN>
		</DIV>
		<DIV id='main' style='position: relative; top: 40px;'>
			<SPAN class='header' style='display: block; font-size: 1.2em;'>Received Direct Messages</SPAN>
			<DIV style="height: 110px; overflow-y: scroll;"><?php echo show_rec_direc(20);?></DIV><BR />
			<SPAN class='header' style='display: block; font-size: 1.2em;'>Sent Direct Messages</SPAN>
			<DIV style="height: 110px; overflow-y: scroll;"><?php echo show_sent_direc(20);?></DIV><BR />
			<SPAN class='header' style='display: block; font-size: 1.2em;'>Timeline</SPAN>
			<DIV style="height: 220px; overflow-y: scroll;"><?php echo show_tweets();?></DIV><BR />
		</DIV>
	</DIV>
</BODY>
