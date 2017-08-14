<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
session_start();
session_write_close();
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));	// session_start()

$constants = get_defined_constants();
$class='even';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Configuration Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};

var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;
var innerwidth;
var innerheight;
var tablewidth;
var tableheight;

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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	innerwidth = outerwidth * .60;
	innerheight = outerheight * .90;
	tablewidth = innerwidth;
	tableheight = innerheight;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('inner').style.width = innerwidth + "px";
	$('inner').style.height = innerheight + "px";
	$('thetable').style.width = tablewidth + "px";
	$('thetable').style.height = tableheight + "px";
	set_fontsizes(viewportwidth, "fullscreen");
	}
	
</SCRIPT>
</HEAD>
<BODY onLoad = 'ck_frames();'>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV CLASS='odd text_center'>
			<SPAN CLASS='header text_green text_biggest'>PHP CONSTANTS</SPAN>
		</DIV>
		<DIV id = "inner" style='position: relative; left: 20%; top: 50px; float: left;'>
			<TABLE ID='thetable' class='fixedheadscrolling scrollable'>
				<TR STYLE='width: 98%;'>
					<TH class='plain_listheader text'>Constant</TH>
					<TH  class='plain_listheader text'>Value</TH>
				</TR>
<?php
				foreach($constants as $key => $val) {
					print "<TR CLASS='" . $class . "' STYLE='width: 100%;'><TD CLASS='plain_list text'>" . $key . "</TD><TD CLASS='plain_list text'>" . $val . "</TD></TR>";
					$class = ($class == 'even') ? "odd" : "even";
					}
?>
			</TABLE>				
		</DIV>
	</DIV>
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
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
innerwidth = outerwidth * .60;
innerheight = outerheight * .90;
tablewidth = innerwidth;
tableheight = innerheight;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('inner').style.width = innerwidth + "px";
$('inner').style.height = innerheight + "px";
$('thetable').style.width = tablewidth + "px";
$('thetable').style.height = tableheight + "px";
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</BODY>
</HTML>
