<?php 
/*
11/3/07 added frame jump prevention
3/25/08 added module identification
5/28/08	added guest can no longer toggle APRS
5/28/08	added version force update and log schema change. 
6/6/08	added double-click prevention
6/9/08  removed 'Closed Calls' button
6/16/08 added start_poll() to body onload();
6/26/08 added conditional to KML_files insert
8/6/08	version number change
9/8/08	added settings value to allow for varied lat/lng display formats
9/18/08 version no. change - revised tables.php
*/ 
require_once('./incs/functions.inc.php');

$version = get_variable('_version');

//$this_version = "2.8.? beta";								// 
//if (!($version == $this_version)) {		// current?
//	$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($this_version)." WHERE `name`='_version' LIMIT 1";	// 5/28/08
//	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//
//	$temp = explode ("/", $_SERVER['REQUEST_URI']);			// set redirect target
//	$temp[count($temp)-1] = "index.php";					// startup script
//	$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp);
//	header("Location:" .$server_str ); 						// Redirect browser 
//	}			// end (!($version ==...)

$sess_key = get_sess_key();
$the_time_limit = 2*60*60;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()==1)? stripslashes_deep(mysql_fetch_array($result)) : "";
//  		sess_id  user_name  user_id  level  ticket_per_page  sortorder  scr_width  scr_height  browser  last_in 10
$whom = (empty($row))? NOT_STR: $row['user_name'];
$level =(empty($row))? NA_STR: get_level_text($row['level']);	
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Top Frame</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="9/16/08">
	<META NAME="description" content="A free, Open Source Computer-Aided-Dispatch (CAD) application, especially suited to Volunteer Groups">	
	<META NAME="keywords" content="'Computer-aided-dispatch', '9-1-1', Volunteers, CAD, Search and Rescue, Emergency Medicine, Open Source, PHP, MySQL, Mash-ups, Google Maps">
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
var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php
var ADM_STR = '<?php echo ADM_STR;?>';			// Admin priv level, defined in functions.inc.php 
var SUPR_STR = '<?php echo SUPR_STR;?>';		// super priv level, defined in functions.inc.php 6/16/08
var starting = false;							// 6/6/08

function isNull(val) {								// checks var stuff = null;
	return val === null;
	}

function logged_in() {								// returns boolean
	var temp = document.getElementById("whom").innerHTML==NOT_STR;
	return !temp;
	}
	
function ck_frames() {		// onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	}		// end function ck_frames()

function shut_down(){
	if (aprs) {window.clearInterval(aprs);}			// 5/28/08
	if (!isNull(newwindow_cb)) {					// call board window?
		newwindow_cb.close();
		}
	if (!isNull(newwindow_c)) {						// chat window?
		newwindow_c.close();	
		}
	}			// end function shut_down()			// cards window allowed

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
		alert ("118: ajax failed")
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
			var temp = self.xmlHttpReq.responseText
			return temp;		// for test purpose only
			}
		}
	self.xmlHttpReq.send("");
	}

// ex useage:	<input value="Go" type="button" onclick='JavaScript:AsyncAjax("whatever.php")'>
// cross-frame function call:  parent.frames["upper"].s tart_poll();
var aprs_poll;
var aprs = new Boolean(false);									// 
var temp;
function get_aprs_time() {		// 
	var temp = syncAjax("get_aprs_poll.php");					// gets poll cycle period via server-side get_variable('aprs_poll');
	return temp;
	}

function get_aprs() {		//
//	alert(154);
	temp = AsyncAjax ("get_php_aprs.php");						// runs do_aprs() server-side to update the db
	}

function start_poll() {											// start the process
	var aprs_poll = get_aprs_time();							// cycle period
	if ((parseInt(aprs_poll)==0) || (aprs_poll==new Boolean (NaN)))	{
		window.clearInterval(aprs)
		document.getElementById("poll_id").innerHTML = "none";
		return;} 
	else {
		get_aprs();												// kick off
		aprs = window.setInterval("get_aprs()", aprs_poll*60*1000);	// aprs => Boolean(true);	
		document.getElementById("poll_id").innerHTML = aprs_poll + " min.";
		}
	}				// end function start poll()

function stop_poll() {
	if (aprs) {window.clearInterval(aprs);}
	aprs =  Boolean(false);	
	document.getElementById("poll_id").innerHTML = "none";
	}
	
function toggle_aprs() {
	if (!((document.getElementById('level').innerHTML==ADM_STR) || (document.getElementById('level').innerHTML==SUPR_STR))){		// 5/28/08 allow admin or super only
		return;
		}
	else {
		if (aprs) 	{stop_poll();}
		else 		{start_poll();}
		}
	}				// end function toggle_aprs()

var newwindow_cb = null;

function do_callBoard() {
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		newwindow_cb=window.open("assigns.php", "callBoard",  "titlebar, location=0, resizable=1, scrollbars, height=240,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_cb)) {
			alert ("Call Board operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_cb.focus();
		starting = false;
		}
	}		// end function do callBoard()

var newwindow_c = null;
	
function do_chat() {
	if (logged_in()) {
		if(starting) {return;}					// 6/6/08
		starting=true;	
	
		newwindow_c=window.open("chat.php", "chatBoard",  "titlebar, resizable=1, scrollbars, height=480,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_c)) {
			alert ("Chat operation requires popups to be enabled. Please adjust your browser options - or else turn off the Chat option setting.");
			return;
			}
		newwindow_c.focus();
		starting = false;
		}
	}

var newwindow_em = null;

function do_emd_card(filename) {
	if(starting) {return;}					// 6/6/08
	starting=true;	

	newwindow_em=window.open(filename, "emdCard",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
	if (isNull(do_emd_card)) {
		alert ("EMD Card operation requires popups to be enabled. Please adjust your browser options.");
		return;
		}
	newwindow_em.focus();
	starting = false;
	}
	
</SCRIPT>
<NOSCRIPT>
	Tickets requires a JavaScript-capable browser.
</NOSCRIPT>	
</HEAD>
<!--<BODY onLoad = "ck_frames(); start_poll()" onunload="shut_down()"> -->
<BODY onLoad = "ck_frames();" onunload="shut_down()">

<table border=0 cellpadding=0>
<tr valign='top'>
	<td><img src="t.gif" border=0></td>
	<td><FONT SIZE="3">ickets <?php print $version ." on <B>".get_variable('host')."</B></FONT>"; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Logged in as: 
		<span ID="whom"><?php print $whom ; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
		Permissions: <SPAN ID="level"><?php print $level; ?></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
	$temp = get_variable('aprs_poll');
	$poll_val = ($temp==0)? "none" : $temp ;
?>
		<SPAN onClick = "toggle_aprs()">APRS Poll:</SPAN> <SPAN ID="poll_id"><?php print $poll_val;?></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Module: <SPAN ID="script"></SPAN>
<nobr><span class="hovermenu">
<ul>
<li id = "main1"><A HREF="main.php" target="main" 	onClick = "go_there ( 'main.php', 'main1');">Active Calls</A></li> <!-- 6/9/08  -->
<li id = "add"><A HREF='add.php' target='main' 		onClick = "go_there ( 'add.php', 'add');">New Call</A></li>
<li id = "resp"><A HREF="units.php" target="main">Units</A></li>
<li id = "search"><A HREF="search.php" target="main">Search</A></li>
<li id = "traffic"><A HREF="traffic.php" target="main">Traffic</A></li>
<li id = "reps"><A HREF="reports.php" target="main">Reports</A></li>
<li id = "config"><A HREF="config.php" target="main">Configuration</A></li>
<?php
$dir = "./emd_cards";

if (file_exists ($dir)) {
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if ((strlen($filename)>2) && (get_ext($filename)=="pdf"))  {
		    $card_file = $filename;						// at least one pdf, use first encountered
		    break;
		    }
		}
	if (!empty($card_file)){		
		print"<li id = \"emdcard\"><A HREF='#' onClick = \"do_emd_card('".$dir . "/" . $filename . "')\">EM Card</A></li>\n";
		}
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
	if (!(intval($call_board)==0)) {
		print"<li id = 'callboard'><A HREF='#' onClick = 'do_callBoard()'>Call Board</A></li>\n";
		}
?>

<li id = "logout"><A HREF="main.php?logout=true" target="main" onClick = "document.getElementById('whom').innerHTML=NOT_STR; stop_poll()">Logout</A></li>
</ul>
</SPAN>
</NOBR>
</TD></TR></TABLE>
<FORM NAME="go" action="#" TARGET = "main"></FORM>
</BODY></HTML>
