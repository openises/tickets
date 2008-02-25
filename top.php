<?php 
require_once('functions.inc.php');
$version = get_variable('_version');
$newvers = "2.5";
if ($version==$newvers) {
	$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value` = '" . $newvers . "' WHERE `settings`.`id` =1 LIMIT 1 ;";
	$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
	$version = $newvers;
	}

$sess_key = get_sess_key();
$the_time_limit = 2*60*60;

$query = "SELECT * FROM $GLOBALS[mysql_prefix]session WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()==1)? stripslashes_deep(mysql_fetch_array($result)) : "";
//  		sess_id  user_name  user_id  level  ticket_per_page  sortorder  scr_width  scr_height  browser  last_in 10
$whom = (empty($row))? "na": $row['user_name'];
$level =(empty($row))? "na": get_level_text($row['level']);	

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
BODY { BACKGROUND-COLOR: #EEEEEE; FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
A { FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
/* .hovermenu ul{font: bold 13px arial;padding-left: 0;margin-left: 0;height: 20px;}  */
.hovermenu ul li{list-style: none;display: inline;} 
.hovermenu ul li a{padding: 2px 0.5em;text-decoration: none;float: left;color: black;background-color: #EFEFEF;border: 2px solid #EFEFEF;}
.hovermenu ul li a:hover{background-color: #DEE3E7;border-style: outset;} 
*.selected {background-color: #DEE3E7;border-style: inset; }
*.unselected {background-color: #EFEFEF;border-style: none; }
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

//function do_callBoard() {
//	newwindow_cb=window.open("assigns.php", "callBoard",  "titlebar, resizable=1, scrollbars, height=240,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
//	while (!newwindow_cb) {
//		window.setTimeout("", 200);					// check every 200 ms.
//		}
//	newwindow_cb.focus();
//	}		// end function do_callBoard()
	
function do_callBoard() {
	newwindow_cb=window.open("assigns.php", "callBoard",  "titlebar, resizable=1, scrollbars, height=240,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
	var i;												// note: visible outside 'for' loop
	for(i=0;i<8;i++) {
		if (!newwindow_cb) {
			window.setTimeout("", 250);					// check every 250 ms.
			}
		else {break; }
		}		// end for(i=0;...)
	if (i==8) {
		alert ("Call Board functions requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
		return;
		}
	else {
		newwindow_cb.focus();
		}
	}		// end function do callBoard()
	
function do_chat() {
	newwindow_c=window.open("chat.php", "chatBoard",  "titlebar, resizable=1, scrollbars, height=480,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
	while (!newwindow_c) {
		window.setTimeout("", 200);
		}
	newwindow_c.focus();

	}
function do_ems_card(filename) {
	newwindow_em=window.open(filename, "emsCard",  "titlebar, resizable=1, scrollbars, height=480,width=720,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
	while (!newwindow_em) {
		window.setTimeout("", 200);
		}
	newwindow_em.focus();
	}
	
</SCRIPT>
</HEAD>
<!-- <BODY onLoad = "if(self.location.href==parent.location.href) {self.location.href = 'index.php';}; start_poll();" onunload=stop_poll();> -->
<BODY>
<table border=0 cellpadding=0>
<tr valign='top'>
	<td><img src="t.gif" border=0></td>
	<td><FONT SIZE="3">ickets <?php print $version ." on <B>".get_variable('host')."</B></FONT>"; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Logged in as: 
		<span ID="whom"><?php print $whom ; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
		Permissions: <SPAN ID="level"><?php print $level; ?></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		APRS Poll: <SPAN ID="aprs" onClick="toggle_aprs()">none</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Module: <SPAN ID="script"></SPAN>
<!--	<a href="#" onClick = "alert(parent.frames[0].name);">test</a> -->
<nobr><span class="hovermenu">
<ul>
<li id = "main1"><A HREF="main.php" target="main" 	onClick = "go_there ( 'main.php', 'main1');">Active Calls</A></li>
<li id = "main2"><A HREF="main.php?status=<?php print $GLOBALS['STATUS_CLOSED'];?>" target="main">Closed Calls</A></li>
<li id = "add"><A HREF='add.php' target='main' 		onClick = "go_there ( 'add.php', 'add');">New Call</A></li>
<li id = "resp"><A HREF="units.php" target="main">Units</A></li>
<li id = "search"><A HREF="search.php" target="main">Search</A></li>
<li id = "traffic"><A HREF="traffic.php" target="main">Traffic</A></li>
<li id = "reps"><A HREF="reports.php" target="main">Reports</A></li>
<li id = "config"><A HREF="config.php" target="main">Configuration</A></li>
<?php
$dir = "./cards";
$dh  = opendir($dir);
while (false !== ($filename = readdir($dh))) {
	if (!is_dir($filename)) {
	    $card_file = $filename;		// at least one file
	    break;
	    }
	}
if (!empty($filename)){		
	print"<li id = \"emscard\"><A HREF='#' onClick = \"do_ems_card('".$dir . "/" . $filename . "')\">EM Card</A></li>\n";
	}

	if (!intval(get_variable('chat_time')==0)) { print "<li id = 'chat'><A HREF='#' onClick = 'do_chat()'>Chat</A></li>\n";}
?>	
<li id = "help"><A HREF="help.php" target="main">Help</A></li>
<?php
	$caption = get_variable('link_capt');
	if (!empty($caption)) {
		print "<li id = 'iframe1'><A HREF=\"iframe1.php\" target=\"main\">" . get_variable('link_capt') . "</A></li>\n";
		}

	$call_board = get_variable('call_board');
	if (intval($call_board)==1) {
		print"<li id = 'callboard'><A HREF='#' onClick = 'do_callBoard()'>Call Board</A></li>\n";
		}
?>

<li id = "logout"><A HREF="main.php?logout=true" target="main" onClick = "stop_poll()">Logout</A></li>
</ul>
</SPAN>
</NOBR>
</TD></TR></TABLE>
<FORM NAME="go" action="#" TARGET = "main"></FORM>
</BODY></HTML>
<?php
/*
11/3 added frame jump prevention
*/
?>
