<?php 
require_once('functions.inc.php');
$version = get_variable('_version');
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Top Frame</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<meta name="description" content="A free, Open Source Computer-Aided-Dispatch (CAD) application, especially suited to Volunteer Groups">	
	<meta name="keywords" content="'Computer-aided dispatch', Volunteers, CAD, Search and Rescue, Emergency Medicine, Open Source, PHP, MySQL, Mash-ups, Google Maps">
<style type="text/css">
BODY { BACKGROUND-COLOR: #EEEEEE; FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
A { FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.hovermenu ul{font: bold 13px arial;padding-left: 0;margin-left: 0;height: 20px;}
.hovermenu ul li{list-style: none;display: inline;}
.hovermenu ul li a{padding: 2px 0.5em;text-decoration: none;float: left;color: black;background-color: #EFEFEF;border: 2px solid #EFEFEF;}
.hovermenu ul li a:hover{background-color: #DEE3E7;border-style: outset;}
/* Apply mousedown effect only to NON IE browsers
html>body .hovermenu ul li a:active{ border-style: inset;} */

</style>
<SCRIPT>
var which = "";	// id of last-invoked li
function go_there (where, id) {
	if (!which=="") {
		}		// end if(which)
	document.go.action = where;
	which = id;
	document.go.submit();
	}				// end function go_there () 

function syncAjax(strURL) {							// synchronous ajax function
	if (window.XMLHttpRequest) {						 
		AJAX=new XMLHttpRequest();						 
		} 
	else {																 
		AJAX=new ActiveXObject("Microsoft.XMLHTTP");
		}
	if (AJAX) {
		AJAX.open("GET", strURL, false);														 
		AJAX.send(null);							// form name
		return AJAX.responseText;																				 
		} 
	else {
		alert ("57: failed")
		return false;
		}																						 
	}		// end function sync Ajax(strURL)

function AsyncAjax(strURL) {						// asynch ajax() function
	var xmlHttpReq = false;
	var self = this;
	if (window.XMLHttpRequest) {					// Mozilla/Safari
		self.xmlHttpReq = new XMLHttpRequest();
		}
	else if (window.ActiveXObject) {				// IE
		self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
		}
	self.xmlHttpReq.open('POST', strURL, true);
	self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	self.xmlHttpReq.onreadystatechange = function() {
		if (self.xmlHttpReq.readyState == 4) {
//			document.getElementById("whatever").innerHTML = self.xmlHttpReq.responseText;		// display
			var temp = self.xmlHttpReq.responseText
			return temp;		// for test purpose only
			}
		}
	self.xmlHttpReq.send("");
	}

// ex useage:	<input value="Go" type="button" onclick='JavaScript:AsyncAjax("whatever.php")'>
// cross-frame function call:  parent.frames["upper"].start_poll();
//	alert (syncAjax(strURL))
var aprs_poll;
var aprs = new Boolean(false);									// boolean
var temp;
function get_aprs_time() {		// 
	var temp = syncAjax("get_aprs_poll.php");
	return temp;
	}

function get_aprs() {		// 
	temp = AsyncAjax ("get_php_aprs.php");						// runs do_aprs() server-side to update the db
	}

function start_poll() {											// start the process
	if (aprs) {window.clearInterval(aprs);}

	var aprs_poll = get_aprs_time();
	if ((!isNaN(aprs_poll)) && (aprs_poll>0)) {					// 0 -> no poll
		get_aprs();												// kick off
		aprs = window.setInterval("get_aprs()", aprs_poll*60*1000);	// aprs => Boolean(true);	
		document.getElementById("aprs").innerHTML = aprs_poll + " min.";
		}
	else {
		document.getElementById("aprs").innerHTML = "none";
		}
	}				// end function start_poll()

function stop_poll() {
	if (aprs) {window.clearInterval(aprs);}
	aprs =  Boolean(false);	
	document.getElementById("aprs").innerHTML = "none";
	}
	
function toggle_aprs() {
	if (aprs) 	{stop_poll();}
	else 		{start_poll();}
	}
	
</SCRIPT>
</HEAD>
<BODY onLoad = "start_poll();" onunload="stop_poll();">
<!-- <BODY> -->
<table border=0 cellpadding=0><tr valign='top'>
<td><img src="t.gif" border=0></td>
<td><FONT SIZE="3">ickets <?php print $version ." on <B>".get_variable('host')."</B></FONT>"; ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Logged in as: 
<A HREF="#"><span ID="whom">not</span></A>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Permissions:
<A HREF="#"><SPAN ID="level">na</SPAN></A>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; APRS Poll:
<A HREF="#"><SPAN ID="aprs" onClick="toggle_aprs()">none</SPAN></A>

<nobr><span class="hovermenu">
<ul>
<li id = "main1"><A HREF="main.php" target="main" 	onClick = "go_there ( 'main.php', 'main1');">Show Tickets</A></li>
<li id = "main2"><A HREF="main.php?status=<?php print $GLOBALS['STATUS_CLOSED'];?>" target="main">Show Closed</A></li>
<li id = "add"><A HREF='add.php' target='main' 		onClick = "go_there ( 'add.php', 'add');">Add Ticket</A></li>
<li id = "resp"><A HREF="config.php?func=responder" target="main">Units</A></li>
<li id = "search"><A HREF="search.php" target="main">Search</A></li>
<li id = "traffic"><A HREF="traffic.php" target="main">Traffic</A></li>
<li id = "config"><A HREF="config.php" target="main">Configuration</A></li>
<li id = "help"><A HREF="help.php" target="main">Help</A></li>
<?php
	$caption = get_variable('link_capt');
	if (!empty($caption)) {
		print "<li id = 'iframe1'><A HREF=\"iframe1.php\" target=\"main\">" . get_variable('link_capt') . "</A></li>\n";
	}
?>

<li id = "logout"><A HREF="main.php?logout=true" target="main" onClick = "stop_poll()">Logout</A></li>
</ul>
</SPAN>
</NOBR>
</TD></TR></TABLE>
<FORM NAME="go" action="#" TARGET = "main"></FORM>
</BODY></HTML>
