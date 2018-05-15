<?php
/*
*/
error_reporting(E_ALL);
set_time_limit(0);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');

error_reporting(E_ALL);
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
?>
<!DOCTYPE HTML>
<HTML>
	<HEAD><TITLE>Tickets - Callboard</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<STYLE>
	table.fixedheadscrolling { cellspacing: 0; border-collapse: collapse; }
	table.fixedheadscrolling td {overflow: hidden; }
	div.scrollableContainer {position: relative; top: 30px; border: 1px solid #999;}
	div.scrollingArea {max-height: 500px; overflow: auto; overflow-x: hidden;}
	table.scrollable thead tr.firstline {position: absolute; left: -1px; top: 0px; }
	table.scrollable thead tr.secondline {position: absolute; left: -1px; top: 20px; }
	table.fixedheadscrolling th {text-align: left; border-left: 1px solid #999;}	
	</STYLE>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
window.onresize=function(){set_size();}

var colors = new Array ('odd', 'even');
var viewportwidth, viewportheight, cbWidth;
function pad(width, string, padding) { 
	return (width <= string.length) ? string : pad(width, string + padding, padding)
	}
	
function secondsToTime(secs) {
	var numdays = Math.floor(secs / 86400);
	var numhours = Math.floor((secs % 86400) / 3600);
	var numminutes = Math.floor(((secs % 86400) % 3600) / 60);
	var numseconds = ((secs % 86400) % 3600) % 60;
	var outputText =  numdays + "D " + numhours + ":" + numminutes + ":" + Math.round(numseconds);
	return outputText;
	}

function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	set_fontsizes(viewportwidth, "fullscreen");
	get_callboard();
	cbWidth = viewportwidth * .98;
	if($('board')) {$('board').style.width = cbWidth + "px";}
	if($('the_board')) {$('the_board').style.width = cbWidth + "px";}
	if($('cbtable')) {$('cbtable').style.width = cbWidth + "px";}
	}
</SCRIPT>
</HEAD>
<?php
include("./forms/callboard.php");
?>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
set_fontsizes(viewportwidth, "fullscreen");
get_callboard();
	cbWidth = viewportwidth * .98;
if($('board')) {$('board').style.width = cbWidth + "px";}
if($('the_board')) {$('the_board').style.width = cbWidth + "px";}
if($('cbtable')) {$('cbtable').style.width = cbWidth + "px";}
</SCRIPT>
</HTML>