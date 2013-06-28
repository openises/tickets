<?php
/*
10/14/08 moved js includes here fm function_major
1/11/09  handle callboard frame
1/19/09 dollar function added
1/21/09 added show butts - re button menu
1/24/09 auto-refresh iff situation display and setting value
1/28/09 poll time added to top frame
3/16/09 added updates and auto-refresh if any mobile units
3/18/09 'aprs_poll' to 'auto_poll'
4/10/09 frames check for call board
7/16/09	protocol handling added
4/11/10 poll_id dropped
6/18/10 timeout test for yg
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 Added reference to stylesheet.php for revisable day night colors plus other bug fixes and revisions to show/hide buttons.
4/1/11 Set font size of Incident and Assignment lists based on screen size to ensure readability and consistent tabular layout.
5/27/2013 added HAS message handling
6/3/2013 added auto-reload operation, removed predecessor, corrected references to window.opener
*/
error_reporting(E_ALL);			// 9/13/08
set_time_limit(0); 				// 6/18/10

@session_start();
require_once('./incs/functions.inc.php');		//7/28/10
require_once('./incs/full_scr.inc.php');	//
$api_key = get_variable('gmaps_api_key');		// empty($_GET)

//dump($_GET);
if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
//	snap(__LINE__, basename(__FILE__));
	do_login(basename(__FILE__));
	$do_mu_init = (array_key_exists('log_in', $_GET))? "window.opener.parent.frames['upper'].mu_init();" : "";	// start multi-user function, 3/15/11	
	}
if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	}

// $remotes = get_current();								// returns array - 3/16/09 - removed 6/3/2013
// snap(basename(__FILE__), __LINE__);
if ($_SESSION['internet']) {				// 8/22/10
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
	}
else {
	$err_arg = "Internet setting error:" . basename(__FILE__) . "@" . __LINE__;
	do_log ($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// logs supplied error message
	$key_str = "";
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<TITLE>Tickets - Full Screen Module</TITLE>
	<!-- 6/3/2013 removed refresh auto-poll -->
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">

	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>&libraries=geometry,weather&sensor=false"></SCRIPT>
	<SCRIPT  TYPE="text/javascript"SRC="./js/epoly.js"></SCRIPT>
	<!--
	<SCRIPT  TYPE="text/javascript"SRC="./js/epoly_v3.js"></SCRIPT>	
	-->
	<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT> 	<!-- 8/1/11 -->
	<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></script>	<!-- 1/29/2013 -->
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>	
	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule_V3.js" type="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/easyws.js"></SCRIPT>		<!-- 5/27/2013 -->	

	<SCRIPT>
	var grid;
	
	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}
	function $() {									// 1/19/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	function CngMenuClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}		
		
	function maxWindow() {
		window.moveTo(0,0); 		// reset origin
		window.resizeTo(window.screen.width,  window.screen.height);		// fill screen
		history.go(0);
		}		// end function maxWindow()

	function do_reload() {
		window.location.reload();				// do the deed!
		}		// end function do reload()


	var watch_val;										// interval var - for clearInterval() - 6/3/2013

	function start_watch() {							// get initial values from top
		window.opener.parent.frames['upper'].mu_init();				// start the polling
		$("div_ticket_id").innerHTML = window.opener.parent.frames["upper"].$("div_ticket_id").innerHTML;		// copy for monitoring
		$("div_assign_id").innerHTML = window.opener.parent.frames["upper"].$("div_assign_id").innerHTML;
		$("div_action_id").innerHTML = window.opener.parent.frames["upper"].$("div_action_id").innerHTML;	
		$("div_patient_id").innerHTML = window.opener.parent.frames["upper"].$("div_patient_id").innerHTML;
		
		watch_val = window.setInterval("do_watch()",5000);		// 4/7/10 - 5 seconds
		}				// end function start watch()

	function end_watch(){
		window.clearInterval(watch_val);
		do_reload();			// 6/3/2013
		}				// end function end_watch()

	function do_watch() {								// monitor for changes - 4/10/10, 6/10/11
		if (							// any change?
			($("div_ticket_id").innerHTML != window.opener.parent.frames["upper"].$("div_ticket_id").innerHTML) ||
			($("div_assign_id").innerHTML != window.opener.parent.frames["upper"].$("div_assign_id").innerHTML) ||
			($("div_action_id").innerHTML != window.opener.parent.frames["upper"].$("div_action_id").innerHTML) ||
			($("div_patient_id").innerHTML != window.opener.parent.frames["upper"].$("div_patient_id").innerHTML)			
			) 
				{		
				alert(148);
				end_watch();	  // a change
				do_reload();			
				}
		}			// end function do_watch()		





	//*****************************************************************************
	// Do not remove this notice.
	//
	// Copyright 2001 by Mike Hall.
	// See http://www.brainjar.com for terms of use.
	//*****************************************************************************
	// Determine browser and version.
	function Browser() {
		var ua, s, i;
		this.isIE		= false;
		this.isNS		= false;
		this.version = null;
		ua = navigator.userAgent;
		s = "MSIE";
		if ((i = ua.indexOf(s)) >= 0) {
			this.isIE = true;
			this.version = parseFloat(ua.substr(i + s.length));
			return;
			}
		s = "Netscape6/";
		if ((i = ua.indexOf(s)) >= 0) {
			this.isNS = true;
			this.version = parseFloat(ua.substr(i + s.length));
			return;
			}
		// Treat any other "Gecko" browser as NS 6.1.
		s = "Gecko";
		if ((i = ua.indexOf(s)) >= 0) {
			this.isNS = true;
			this.version = 6.1;
			return;
			}
		}
	var browser = new Browser();
	var dragObj = new Object();		// Global object to hold drag information.
	dragObj.zIndex = 0;
	function dragStart(event, id) {
		var el;
		var x, y;
		if (id)										// If an element id was given, find it. Otherwise use the element being
			dragObj.elNode = document.getElementById(id);	// clicked on.
		else {
			if (browser.isIE)
				dragObj.elNode = window.event.srcElement;
			if (browser.isNS)
				dragObj.elNode = event.target;
			if (dragObj.elNode.nodeType == 3)		// If this is a text node, use its parent element.
				dragObj.elNode = dragObj.elNode.parentNode;
			}
		if (browser.isIE) {			// Get cursor position with respect to the page.
			x = window.event.clientX + document.documentElement.scrollLeft
				+ document.body.scrollLeft;
			y = window.event.clientY + document.documentElement.scrollTop
				+ document.body.scrollTop;
			}
		if (browser.isNS) {
			x = event.clientX + window.scrollX;
			y = event.clientY + window.scrollY;
			}
		dragObj.cursorStartX = x;		// Save starting positions of cursor and element.
		dragObj.cursorStartY = y;
		dragObj.elStartLeft	= parseInt(dragObj.elNode.style.left, 30);
		dragObj.elStartTop	 = parseInt(dragObj.elNode.style.top,	10);
		if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
		if (isNaN(dragObj.elStartTop))	dragObj.elStartTop	= 0;
		dragObj.elNode.style.zIndex = ++dragObj.zIndex;		// Update element's z-index.
		if (browser.isIE) {									// Capture mousemove and mouseup events on the page.
			document.attachEvent("onmousemove", dragGo);
			document.attachEvent("onmouseup",	 dragStop);
			window.event.cancelBubble = true;
			window.event.returnValue = false;
			}
		if (browser.isNS) {
			document.addEventListener("mousemove", dragGo,	 true);
			document.addEventListener("mouseup",	 dragStop, true);
			event.preventDefault();
			}
		}
	function dragGo(event) {
		var x, y;
		if (browser.isIE) {	// Get cursor position with respect to the page.
			x = window.event.clientX + document.documentElement.scrollLeft
				+ document.body.scrollLeft;
			y = window.event.clientY + document.documentElement.scrollTop
				+ document.body.scrollTop;
			}
		if (browser.isNS) {
			x = event.clientX + window.scrollX;
			y = event.clientY + window.scrollY;
			}
		dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";	// Move drag element by the same amount the cursor has moved.
		dragObj.elNode.style.top	= (dragObj.elStartTop	+ y - dragObj.cursorStartY) + "px";
		if (browser.isIE) {
			window.event.cancelBubble = true;
			window.event.returnValue = false;
			}
		if (browser.isNS)
			event.preventDefault();
		}
	function dragStop(event) {
		if (browser.isIE) {	// Stop capturing mousemove and mouseup events.
			document.detachEvent("onmousemove", dragGo);
			document.detachEvent("onmouseup",	 dragStop);
			}
		if (browser.isNS) {
			document.removeEventListener("mousemove", dragGo,	 true);
			document.removeEventListener("mouseup",	 dragStop, true);
			}
		}		

</SCRIPT> 
<SCRIPT SRC='./js/usng.js' TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 --> 
<SCRIPT SRC='./js/graticule_V3.js' type='text/javascript'></SCRIPT>
<SCRIPT>	<!-- 4/1/11 sets font size depending on screen size -->
	var line_text_size = window.screen.width > 1200 ? "12" : "9";
	var line_text = "<STYLE TYPE='text/css'>.incs {font-size:" + line_text_size + "px;} .assigns {font-size:" + line_text_size + "px;} </STYLE>";
	var grid;
	document.write (line_text);
</SCRIPT>	
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<style type="text/css">
.box {
	background-color: #CECECE;
	border-style: solid;
	border-width:2px;
	color: #000000;
	padding: 0px;
	}
.bar {
	background-color: #DEE3E7;
	color: #000000;
	cursor: move;
	font-weight: bold;
	}
.content {
	padding: 1em;
	}
.map {width:99%;height:80%;} 
.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
.box { background-color: transparent; border: none; color: #000000; padding: 0px; position: absolute; z-index: 9999; width: 800px;}
.bar { background-color: transparent; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em; width: 1200px; }

.cfull 	{width: 95%; text-align: center;FONT-WEIGHT: normal; FONT-SIZE: 0.9em; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.c0 	{width: 40%; FONT-WEIGHT: normal; FONT-SIZE: 0.9em; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.c1 	{width: 16%; text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.cdate  {width: 16%; text-align: left;  	float: left; 	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.cspace {width: 5%; float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.unit_n {width: 16%; text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.unit_d {width: 2%; text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.unit_s {width: 13%; text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.in_1 	{width: 15%; text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.in_date  {width: 15%; text-align: left;  	float: left; 	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.in_space {width: 5%; 	text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.in_type 	{width: 15%; 	text-align: left; 	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.in_dur 	{width: 26%; text-align: left;  	float: left;	FONT-WEIGHT: normal; FONT-SIZE: 0.9em; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
</style>
</HEAD>
<?php
	$temp =  explode("/", get_variable('auto_refresh'));
	$do_start_watch = ( ( count($temp) == 3 ) && (intval ($temp[1]) == 1 ) ) ? "start_watch();" : "";	// set JS string
?>
<BODY onLoad = "set_initial_pri_disp(); set_categories(); set_fac_categories(); check_sidemenu(); <?php print $do_mu_init;?> <?php print $do_start_watch;?> ">	<!-- 3/15/11 -->
<SCRIPT SRC='./js/wz_tooltip.js' type='text/javascript'></SCRIPT>

	<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>	<!-- 6/3/2013 -->
	<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
	<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
	<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>

<TABLE><TR><TD>
<?php
//require_once('./incs/links.inc.php');
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;

	full_scr();
?>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
<INPUT TYPE='hidden' NAME='func' VALUE='0'>
</FORM>
</TD></TR></TABLE>
</BODY></HTML>
