<?php
error_reporting(E_ALL);
@session_start();
require_once('./incs/functions.inc.php');
require_once($_SESSION['fmp']);


$sidebar_width = 400;		// pixels
//$from_left = $sidebar_width + get_variable('map_width') + 72;
//$from_left =  get_left_margin ($sidebar_width);
if ((array_key_exists('frm_mode', $_GET)) && ($_GET['frm_mode']==1)) {
	$inWin = true;
	$from_left = round (0.3 * $_SESSION['scr_width']);
	$from_top = round (0.3 * $_SESSION['scr_height']);
	} else {
	$inWin = false;
	$from_left = round (0.4 * $_SESSION['scr_width']);
	$from_top = round (0.3 * $_SESSION['scr_height']);
	}

$show_tick_left = FALSE;	// controls left-side vs. right-side appearance of incident details - 11/27/09
$unit_ht_max = 	0.3;		// unit sidebar height maximum as a portion of screen height, a decimal fraction; default 0.3 ( = 30%)

$units_side_bar_height = 0.6;		// height of units sidebar as decimal fraction - default is 0.9 (90%)

/*
7/16/10 Initial Release for no internet operation - created from routes.php
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
11/18/10 Added filter by capabilities and fixed individual unit dispatch.
3/15/11 changed default.css to stylesheet.php
5/28/11 sql inject prevention added
*/

do_login(basename(__FILE__));		// 
if ((isset($_REQUEST['ticket_id'])) && (!(strval(intval($_REQUEST['ticket_id']))===$_REQUEST['ticket_id']))) {	shut_down();}	// 5/28/11
//snap(__LINE__, basename(__FILE__));

//$istest = TRUE;
if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_REQUEST);
	}
	
if (!(isset ($_SESSION['allow_dirs']))) {	
	$_SESSION['allow_dirs'] = 'true';			// note js-style LC
	}

function get_ticket_id () {				// 5/4/11
	if (array_key_exists('ticket_id', ($_REQUEST))) {
		$_SESSION['active_ticket'] = $_REQUEST['ticket_id'];
		return (integer) $_REQUEST['ticket_id'];
		}
	elseif (array_key_exists('active_ticket', $_SESSION)) {
		return (integer) $_SESSION['active_ticket'];	
		}
	else {
		echo "error at "	 . __LINE__;
		}								// end if/else
	}				// end function	

function get_left_margin ($sb_width) {
//	return min(($_SESSION['scr_width'] - 150), ($sb_width + get_variable('map_width') + 72));
	return min(($_SESSION['scr_width'] - 150), ($sb_width + 72));
	}

$api_key = get_variable('gmaps_api_key');
$_GET = stripslashes_deep($_GET);
$eol = "< br />\n";

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">	
	<HEAD><TITLE>Tickets - Routes Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"/>
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
    <STYLE TYPE="text/css">
		body 				{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 				{border-collapse: collapse; }
		table.directions th {background-color:#EEEEEE;}	  
		img 				{color: #000000;}
		span.even 			{background-color: #DEE3E7;}
		span.warn			{display:none; background-color: #FF0000; color: #FFFFFF; font-weight: bold; font-family: Verdana, Arial, sans serif; }

		span.mylink			{margin-right: 32PX; text-decoration:underline; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_1		{margin-right: 32PX; text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_2		{margin-right: 8PX;  text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		.box {background-color: transparent;  border: none;  color: #000000;  padding: 0px;  position: absolute;  }
		.bar {background-color: #DEE3E7;  color: transparent;  cursor: move;  font-weight: bold;  padding: 2px 1em 2px 1em;  }
		.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}	
		.content {
			padding: 1em;
			}

		.box2 { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:10000; width: 180px; }
		.bar2 { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:10000; text-align: center;}
		.content { padding: 1em; text-align: center; }		
    	</STYLE>

<SCRIPT>
	try {	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	
//	var url = "do_session.php?the_name=the_value";

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
//			alert ("332 " + AJAX.responseText);
			return AJAX.responseText;																				 
			} 
		else {
			alert ("837: failed");
			alert("failed at line <?php print __LINE__;?>");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

	function get_new_colors() {								// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function docheck(in_val){				// JS boolean  - true/false
		document.routes_Form.frm_allow_dirs.value = in_val;	
		url = "do_session.php?the_name=allow_dirs&the_value=" + in_val.trim();
		syncAjax(url);			// note asynch call
		}
		
	function isNull(arg) {
		return arg===null;
		}

	function $() {									// 2/11/09
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

	String.prototype.trim = function () {									// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
</SCRIPT>	
<script type="text/javascript">//<![CDATA[
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
	dragObj.elStartLeft	= parseInt(dragObj.elNode.style.left, 10);
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
	
function hideDiv(div_area, hide_cont, show_cont) {	//	3/15/11
	if (div_area == "buttons_sh") {
		var controlarea = "hide_controls";
		}
	if (div_area == "resp_list_sh") {
		var controlarea = "resp_list";
		}
	if (div_area == "facs_list_sh") {
		var controlarea = "facs_list";
		}
	if (div_area == "incs_list_sh") {
		var controlarea = "incs_list";
		}
	if (div_area == "region_boxes") {
		var controlarea = "region_boxes";
		}			
	var divarea = div_area 
	var hide_cont = hide_cont 
	var show_cont = show_cont 
	if($(divarea)) {
		$(divarea).style.display = 'none';
		$(hide_cont).style.display = 'none';
		$(show_cont).style.display = '';
		} 
	var params = "f_n=" +controlarea+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
	var url = "persist2.php";
	sendRequest (url, gb_handleResult, params);			
	} 

function showDiv(div_area, hide_cont, show_cont) {	//	3/15/11
	if (div_area == "buttons_sh") {
		var controlarea = "hide_controls";
		}
	if (div_area == "resp_list_sh") {
		var controlarea = "resp_list";
		}
	if (div_area == "facs_list_sh") {
		var controlarea = "facs_list";
		}
	if (div_area == "incs_list_sh") {
		var controlarea = "incs_list";
		}
	if (div_area == "region_boxes") {
		var controlarea = "region_boxes";
		}				
	var divarea = div_area
	var hide_cont = hide_cont 
	var show_cont = show_cont 
	if($(divarea)) {
		$(divarea).style.display = '';
		$(hide_cont).style.display = '';
		$(show_cont).style.display = 'none';
		}
	var params = "f_n=" +controlarea+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
	var url = "persist2.php";
	sendRequest (url, gb_handleResult, params);					
	}
//]]></script>
<?php
if (!empty($_POST)) {				// 77-200
	extract($_POST);
	$addrs = array();													// 10/7/08
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
	$assigns = explode ("|", $_POST['frm_id_str']);		// pipe sep'd id's in frm_id_str
	for ($i=0;$i<count($assigns); $i++) {		//10/6/09 added facility and receiving facility
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`, `facility_id`, `rec_facility_id`)
						VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($frm_ticket_id),
							quote_smart($assigns[$i]),
							quote_smart($frm_comments),
							quote_smart($frm_by_id),
							quote_smart($now),
							quote_smart($frm_facility_id),
							quote_smart($frm_rec_facility_id));
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//										remove placeholder inserted by 'add'		
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . quote_smart($frm_ticket_id) . " AND `responder_id` = 0 LIMIT 1";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
							// apply status update to unit status
		$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($frm_status_id) . " WHERE `id` = " . quote_smart($assigns[$i])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

		$query = "SELECT `id`, `contact_via` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . quote_smart($assigns[$i])  ." LIMIT 1";		// 10/7/08
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row_addr = stripslashes_deep(mysql_fetch_assoc($result));
		if (is_email($row_addr['contact_via'])) {array_push($addrs, $row_addr['contact_via']); }		// to array for emailing to unit

		do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $assigns[$i], $frm_status_id);
		if ($frm_facility_id != 0) {
			do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
			}
		if ($frm_rec_facility_id != 0) {
			do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
			}
		}
//	print __LINE__;
//	dump($addrs);				// array of addresses
?>	
<SCRIPT>
	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
//				alert('HTTP error ' + req.status);
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}
	
	
	function handleResult(req) {				// the 'called-back' function
												// onto floor!
		}

	var starting = false;						// 2/15/09

	function do_mail_win(addrs, ticket_id) {	
		if(starting) {return;}					// dbl-click catcher
//		alert("174 " +addrs);
		starting=true;	
		var url = "mail_edit.php?ticket_id=" + ticket_id + "&addrs=" + addrs + "&text=";	// no text
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()

<?php 
	if(intval(get_variable('call_board'))==2) {
		print "\n\tparent.top.calls.location.reload(true);\n";
		}
?>	

</SCRIPT>
</HEAD>
<?php
	$addr_str = urlencode( implode("|", array_unique($addrs)));
	if (empty($addr_str)) {
		$next = (intval(get_variable('quick'))==1)? " onLoad = 'document.cont_form.submit();'" : "";			//3/11/09
		print "\n<BODY $next>\n";
		}
	else {
		$next = (intval(get_variable('quick'))==1)? "; document.cont_form.submit();" : "";
		print "\n<BODY onLoad = \"do_mail_win('" . $addr_str . "', '" . $_POST['frm_ticket_id'] . "')$next \">\n";
		}
?>
	<CENTER><BR><BR><BR><BR><H3>Call Assignments made to:<BR /><?php print substr((str_replace ( "\n", ", ", $_POST['frm_name_str'])) , 0, -2);?><BR><BR> <!-- 11/8/08 -->
<?php print (intval(get_variable("call_board")) == 1)? "See Call Board": "";?>	
	</H3>
	<FORM NAME='cont_form' METHOD = 'get' ACTION = "main.php">
<?php
	if ((array_key_exists('frm_mode', $_GET)) && ($_GET['frm_mode']==1)) {
?>	
		<INPUT TYPE='button' VALUE='Finished' onClick = "window.close()">
<?php
		} else {
?>
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.cont_form.submit()">
<?php	
		}
?>		
	</FORM></BODY></HTML>
<?php		
	}		// end if (!empty($_POST))
else {		// 201-439

?>

<SCRIPT>
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW
		
		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();
	
		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;

	function min(inArray) {				// returns index of least float value in inArray
		var minsofar =  40076.0;		// initialize to earth circumference (km)
		var j=-1;
		for (var i=1; i< inArray.length; i++){											// 11/12/09
			if ((lats[i]) &&  (parseFloat(inArray[i]) < parseFloat(minsofar))) { 		// 11/12/09
				j=i;
				minsofar=inArray[i];
				}
			}
		return (j>0) ? j: false;
		}		// end function min()

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()
	
<?php
	$addrs = FALSE;												// notifies address array doesn't exist
	if (array_key_exists ( "email", $_GET)) {						// 10/23/08
		$addrs = notify_user(0,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
		}				// end if (array_key_exists())

	$dispatches_disp = array();										// unit id to ticket descr	- 5/23/09
	$dispatches_act = array();										// actuals
	
	$query = "SELECT *, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,  `t`.`scope` AS `theticket`,
		`r`.`id` AS `theunit_id`
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";				// 6/25/10
//	dump($query);
	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if(!(empty($row['theunit_id']))) {
			$dispatches_act[$row['theunit_id']] = (empty($row['clear']))? $row['ticket_id']:"";	// blank = unit unassigned

			if ($row['multi']==1) {
				$dispatches_disp[$row['theunit_id']] = "&nbsp;&nbsp;* ";					// identify as multiple - 5/22/09
				}
			else {
				$dispatches_disp[$row['theunit_id']] = (empty($row['clear']))? $row['theticket']:"";	// blank = unit unassigned
				}		// end if/else(...)
			}
		}		// end while (...)

//										8/10/09, 10/6/09, 1/7/10
	$query = "SELECT *,
		UNIX_TIMESTAMP(problemstart) AS problemstart,
		UNIX_TIMESTAMP(problemend) AS problemend,
		UNIX_TIMESTAMP(booked_date) AS booked_date,		
		UNIX_TIMESTAMP(date) AS date,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,
		 `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		 `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
		 `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		 `$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		 
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`lat` AS `rf_lat`,
		`rf`.`lng` AS `rf_lng`,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`street` AS `rec_fac_street`,
		`rf`.`city` AS `rec_fac_city`,
		`rf`.`state` AS `rec_fac_state`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
		 `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` 
		 FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`=" . quote_smart($_GET['ticket_id']) . " LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	$facility = $row_ticket['facility'];
	$rec_fac = $row_ticket['rec_facility'];
	$lat = $row_ticket['lat'];
	$lng = $row_ticket['lng'];
	
	print "var thelat = " . $lat . ";\nvar thelng = " . $lng . ";\n";		// set js-accessible location data
//	unset ($result);

	if ($rec_fac > 0) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=" . $rec_fac . "";			// 10/6/09
		$result_rfc = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_rec_fac = stripslashes_deep(mysql_fetch_array($result_rfc));
		$rf_lat = $row_rec_fac['lat'];
		$rf_lng = $row_rec_fac['lng'];
		$rf_name = $row_rec_fac['name'];		
		
		unset ($result_rfc);
		} else {
//		print "var thereclat;\nvar thereclng;\n";		// set js-accessible location data for receiving facility

	}

	@session_start();		// 
	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 5/12/10
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	unset($result);		
	$required = 96 + (mysql_affected_rows()*22);		// 7/9/10
	$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// set the max

$the_width = 480;
?>
function filterSubmit() {		//	11/18/10
	document.filter_Form.submit();
	}

function filterReset() {		//	11/18/10
	document.filter_Form.capabilities.value="";
	document.filter_Form.submit();
	}
function checkArray(form, arrayName)	{	//	6/10/11
	var retval = new Array();
	for(var i=0; i < form.elements.length; i++) {
		var el = form.elements[i];
		if(el.type == "checkbox" && el.name == arrayName && el.checked) {
			retval.push(el.value);
		}
	}
return retval;
}	
	
function checkForm(form)	{	//	5/4/11
	var errmsg="";
	var itemsChecked = checkArray(form, "frm_group[]");
	if(itemsChecked.length > 0) {
		var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
		var url = "persist3.php";	//	3/15/11	
		sendRequest (url, fvg_handleResult, params);				
//			form.submit();
	} else {
		errmsg+= "\tYou cannot Hide all the regions\n";
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
		}
	}
}

function fvg_handleResult(req) {	// 5/4/11	The persist callback function for viewed groups.
	document.region_form.submit();
	}
	
function form_validate(theForm) {	//	5/4/11
//		alert("Validating");
	checkForm(theForm);
	}				// end function validate(theForm)

function sendRequest(url,callback,postData) {	//	5/4/11
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
	if (postData)
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	req.onreadystatechange = function () {
		if (req.readyState != 4) return;
		if (req.status != 200 && req.status != 304) {
			return;
			}
		callback(req);
		}
	if (req.readyState == 4) return;
	req.send(postData);
	}

var XMLHttpFactories = [
	function () {return new XMLHttpRequest()	},
	function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
	function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
	function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
	];

function createXMLHTTPObject() {	//	5/4/11
	var xmlhttp = false;
	for (var i=0;i<XMLHttpFactories.length;i++) {
		try {
			xmlhttp = XMLHttpFactories[i]();
			}
		catch (e) {
			continue;
			}
		break;
		}
	return xmlhttp;
	}	
</SCRIPT>
<?php
$ck_frames_str = (((array_key_exists ( "frm_mode", $_GET)) && ($_GET['frm_mode']) ==1))? "": "ck_frames();" ;		// 10/9/10
$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
$capabilities = (array_key_exists('capabilities', $_GET))? stripslashes(trim(str_replace('/', '|', $_GET['capabilities']))) : "" ;	// 11/18/10
$disabled = ($capabilities=="")? "disabled" : "" ;	// 11/18/10
?>

<BODY onLoad = "do_notify(); <?php print $ck_frames_str;?>" >
<A NAME='top'>

<TABLE ID = 'outer' BORDER=0>
<TR><TD COLSPAN=2></TD></TR>
<TR>
	<TD><DIV ID='side_bar' style="height: <?php print $the_height;?>px; overflow-y: auto; overflow-x: auto;"></DIV>
<?php
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	if($unit_id=="") { 	// 11/18/10
		?>
		<DIV ID='theform' style='position: relative; top: 10px; background-color: transparent; border-color: #000000;'><!-- 11/18/10 -->	
		<TABLE ALIGN='center' BORDER='0'>
		<TR class='heading'><TH class='heading'>FILTER BY CAPABILITIES</TH></TR>	<!-- 3/15/11 -->
		<FORM NAME='filter_Form' METHOD="GET" ACTION="routes_nm.php">
		<TR class='odd'><TD ALIGN='center'>Filter Type: <b>OR </b><INPUT TYPE='radio' NAME='searchtype' VALUE='OR' checked><b>AND </b><INPUT TYPE='radio' NAME='searchtype' VALUE='AND'></TD></TR>	<!-- 3/15/11 -->
		<TR class='even'><TD><INPUT SIZE='48' TYPE='text' NAME='capabilities' VALUE='<?php print $capabilities;?>' MAXLENGTH='64'></TD></TR>	<!-- 3/15/11 -->
		<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>' />
		<INPUT TYPE='hidden' NAME='unit_id' 	VALUE='<?php print $unit_id; ?>' />
		<TR class='odd'><TD align="center"><input type="button" OnClick="filterSubmit();" VALUE="Filter"/>&nbsp;&nbsp;<input type="button" OnClick="filterReset();" VALUE="Reset Filter" <?php print $disabled;?>/></TD></TR>	<!-- 3/15/11 -->	
		</FORM></TABLE></DIV></TD>
	<?php }
	?>
	<TD><DIV ID='the_ticket' STYLE='width: " .  get_variable('map_width') . "'>
<?php	print do_ticket($row_ticket, $the_width, FALSE, FALSE); ?>	
		</DIV></TD>
	</TD>
	</TR>
	</TABLE> <!-- end ID = 'outer' -->


	<FORM NAME='can_Form' ACTION="main.php">
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>"/>	
	</FORM>	

<?php
	$theAction = ($inWin) ? "routes_nm.php?frm_mode=1" : "routes_nm.php";
?>
	<FORM NAME='routes_Form' METHOD='post' ACTION="<?php print $theAction;?>">
	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db' />
	<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>' />
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $_SESSION['user_id'];?>" />
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "" />
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "" />
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1" />
	<INPUT TYPE='hidden' NAME='frm_facility_id' 	VALUE= "<?php print $facility;?>" /> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_rec_facility_id' VALUE= "<?php print $rec_fac;?>" /> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New" />
	<INPUT TYPE='hidden' NAME='frm_allow_dirs' 	VALUE = <?php print $_SESSION['allow_dirs']; ?> />	<!-- 11/21/09 -->
	</FORM>
	<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>' />	<!-- 10/25/08 -->
	</FORM>
	<!-- 8/2/09 -->
	<DIV STYLE="position:fixed; width:120px; height:auto; top:<?php print $from_top;?>px; left:<?php print $from_left;?>px; background-color: transparent;">	<!-- 5/17/09, 7/7/09 -->
		
<?php
			function get_addr(){				// returns incident address 11/27/09
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`= " . $_GET['ticket_id'] . " LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_array($result));
				return "{$row['street']}<br />{$row['city']}<br /> {$row['state']}"; 
				}		// end function get_addr()

			$thefunc = (is_guest())? "guest()" : "validate()";		// disallow guest attempts
			$nr_units = 1;
			$addr = get_addr();
?>
		<div id='boxB' class='box' style='left:<?php print $from_left;?>px;top:<?php print $from_top;?>px; position:fixed; background-color : rgba(0, 0, 0, 0.2);' > <!-- 9/23/10 -->
		<div class="bar" style="width:12em; color: #000000;"
			 onmousedown="dragStart(event, 'boxB')">Drag me</div><!-- drag bar -->
		<div style = "margin-top:10px;">
		<IMG SRC="markers/down.png" BORDER=0  onclick = "location.href = '#page_bottom';" STYLE = 'margin-left:2px;' />		
		<IMG SRC="markers/up.png" BORDER=0  onclick = "location.href = '#page_top';" STYLE = 'margin-left:40px;'/><br />
		</div>
			 <div style = 'height:10px;'/>&nbsp;</div>

<?php

			print "<SPAN ID=\"mail_button\" STYLE=\"display: 'none'\">";	//10/6/09
			print "<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='_blank' onsubmit='return mail_direcs(this);'>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_direcs' VALUE=''>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_u_id' VALUE=''>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Incident'>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_scope' VALUE=''>"; // 10/29/09
			print "</FORM>";	
			print "<INPUT TYPE='button' VALUE='Reset' onClick = 'doReset()' />";
			print "</SPAN>";	
			if ((array_key_exists('frm_mode', $_GET)) && ($_GET['frm_mode']==1)) {			
				print "<INPUT TYPE='button' VALUE='Cancel'  onClick='window.close();' />";
				} else {
				print "<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();' />";
				}
			if ($nr_units>0) {			
				print "<BR /><INPUT TYPE='button' value='DISPATCH\nUNITS' onClick = '" . $thefunc . "' />\n";	// 6/14/09
				}
			print "<BR /><BR /><SPAN STYLE='display: inline-block;' class='normal_text'><NOBR><H3>to:<BR /><I>{$addr}</I></H3></NOBR></SPAN>\n";
			print "<SPAN ID=\"loading\" STYLE=\"display: 'inline-block'\">";
			print "</SPAN>";

?>
	</DIV>
<?php

		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
		$regions_inuse = get_regions_inuse($user_level);	//	5/4/11
		$group = get_regions_inuse_numbers($user_level);	//	5/4/11		
		
		$al_groups = $_SESSION['user_groups'];
?>				
		<A NAME="page_bottom" /> <!-- 5/13/10 -->	
		<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
		<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>' />	<!-- 10/25/08 -->
		</FORM>
	</BODY>

<?php
//	dump($addrs);
			if ($addrs) {				// 10/21/08
?>			
<SCRIPT>
	function do_notify() {
//		alert(352);
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "ATTENTION - New Ticket: ";
		var theId = '<?php print $_GET['ticket_id'];?>';
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId);		// ($to_str, $text, $ticket_id)   10/15/08
		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function  - ignore returned data
		}

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
//				alert('HTTP error ' + req.status);
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}
	
</SCRIPT>
<?php

			}		// end if($addrs) 
		else {
?>		
<SCRIPT>
	function do_notify() {
//		alert(414);
		return;
		}			// end function do notify()
</SCRIPT>
<?php		
//	print __LINE__;
			}
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	$capabilities = (array_key_exists('capabilities', $_GET))? stripslashes(trim(str_replace('/', '|', $_GET['capabilities']))) : "" ;	// 11/18/10
	$searchtype = (array_key_exists('searchtype', $_GET))? $_GET['searchtype'] : "OR" ;	// 11/18/10
	$disabled = ($capabilities=="")? "disabled" : "" ;	// 11/18/10
	print do_list($unit_id, $capabilities, $searchtype);
	print "</HTML> \n";

	}			// end if/else !empty($_POST)

function do_list($unit_id ="", $capabilities ="", $searchtype) {
	global $row_ticket, $dispatches_disp, $dispatches_act, $from_top, $from_left, $eol, $sidebar_width;
	
	switch($row_ticket['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
		default: 							$severityclass=''; break;
		}

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(booked_date) AS booked_date,
		UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated, `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`= " . quote_smart($_GET['ticket_id']) . " LIMIT 1";			// 7/24/09 10/16/08 Incident location 09/25/09 Pre Booking

//	print __LINE__;
//	dump($query);

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	$facility = $row_ticket['facility'];
	$rec_fac = $row_ticket['rec_facility'];
	$lat = $row_ticket['lat'];
	$lng = $row_ticket['lng'];
	$problemstart = $row_ticket['problemstart'];
	$problemend = $row_ticket['problemend'];
//	dump(mysql_format_date($row_ticket['problemstart']));
	
//	print "var thelat = " . $lat . ";\nvar thelng = " . $lng . ";\n";		// set js-accessible location data
	unset ($result);

	if ($rec_fac > 0) {
		$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`= $rec_fac ";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing
		$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_rec_fac = stripslashes_deep(mysql_fetch_array($result_rfc));
		$rf_lat = $row_rec_fac['lat'];
		$rf_lng = $row_rec_fac['lng'];
		$rf_name = $row_rec_fac['name'];		
		
//		print "var thereclat = " . $rf_lat . ";\nvar thereclng = " . $rf_lng . ";\n";		// set js-accessible location data for receiving facility
	} else {
//		print "var thereclat;\nvar thereclng;\n";		// set js-accessible location data for receiving facility
	}

?>
<SCRIPT>
	var color=0;
	var last_from;
	var last_to;
	var rec_fac;
	var current_id;			// 10/25/08
	var output_direcs = "";	//10/6/09
	var have_direcs = 0;	//10/6/09
	var tick_name = '<?php print $row_ticket['scope'];?>';	//10/29/09

		var colors = new Array ('odd', 'even');
		
		var Direcs = null;			// global
		var Now;
		var mystart;
		var myend;

		function do_sidebar(sidebar, color, id, unit_id) {						// No map
			var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09
			marker = null;
			gmarkers[id] = null;										// marker to array for side bar click function
	
			side_bar_html += "<TR ID = '_tr" + id  + "' CLASS='" + colors[(id)%2] +"' VALIGN='bottom' onClick = myclick(" + id + "," + unit_id +");><TD>";

			side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;'></TD>";
			var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09

//			var the_class = (direcs[id])?  "emph" : "td_label";
			var the_class = (lats[id])?  "emph" : "td_label";
			side_bar_html += "<TD CLASS='" + the_class + "'>" + letter + " "+ sidebar +"</TD></TR>\n";
			return null;
			}				// end function create Marker()


		function myclick(id, unit_id) {								// responds to side bar click
//			alert (821);
			var norecfac = "";
			if (document.getElementById(current_id)) {
				document.getElementById(current_id).style.visibility = "hidden";			// hide last check if defined
				}
			current_id= "R"+id;
			document.getElementById(current_id).style.visibility = "visible";			// show newest

			}					// end function my click(id)

		function handleErrors(){		//G_GEO_UNKNOWN_DIRECTIONS 
			if (gdir.getStatus().code == G_GEO_UNKNOWN_DIRECTIONS ) {
				alert("501: directions unavailable\n\nClick map point for directions.");
				}
			else if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
				alert("440: No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
				alert("442: A map request could not be processed, reason unknown.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
				alert("444: Technical error.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_KEY)
				alert("448: The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
				alert("450: A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
			else alert("451: An unknown error occurred.");
			}		// end function handleErrors()

		function onGDirectionsLoad(){ 
//			var temp = gdir.getSummaryHtml();
			}		// function onGDirectionsLoad()

		function guest () {
			alert ("Demonstration only.  Guests may not commit dispatch!");
			}
			
		function validate(){		// frm_id_str
			msgstr="";
			for (var i =1;i<unit_sets.length;i++) {				// 3/30
				if (unit_sets[i]) {
					msgstr+=unit_names[i]+"\n";
					document.routes_Form.frm_id_str.value += unit_ids[i] + "|";
					}
				}
			if (msgstr.length==0) {
				var more = (nr_units>1)? "s": ""
				alert ("Please select unit" + more + ", or cancel");
				return false;
				}
			else {
				var quick = <?php print (intval(get_variable("quick")==1))? "true;\n" : "false;\n";?>
			
//				if (confirm ("Please confirm Unit dispatch as follows\n\n" + msgstr)) {
				if ((quick) || (confirm ("Please confirm unit dispatch\n\n" + msgstr))) {		// 11/23/09

					document.routes_Form.frm_id_str.value = document.routes_Form.frm_id_str.value.substring(0, document.routes_Form.frm_id_str.value.length - 1);	// drop trailing separator
					document.routes_Form.frm_name_str.value = msgstr;	// for re-use
					document.routes_Form.submit();
					document.getElementById("outer").style.display = "none";
					document.getElementById("bottom").style.display = "block";					
					}
				else {
					document.routes_Form.frm_id_str.value="";	
					return false;
					}
				}

			}		// end function validate()
	
		function exists(myarray,myid) {
			var str_key = " " + myid;		// force associative
			return ((typeof myarray[str_key])!="undefined");		// exists if not undefined
			}		// end function exists()
			
		var icons=[];						// note globals
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$icons = $GLOBALS['icons'];
	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// map type to blank icon id
		$blank = $icons[$row['icon']];
		print "\ticons[" . $row['id'] . "] = " . $row['icon'] . ";\n";	// 
		}
	unset($result);
?>
		var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders' STYLE = 'WIDTH: <?php print $sidebar_width;?>px;'>";

		var gmarkers = [];
		var lats = [];
		var lngs = [];
		var distances = [];
		var unit_names = [];		// names 
		var unit_sets = [];			// settings
		var unit_ids = [];			// id's
		var unit_assigns =  [];		// unit id's assigned this incident
		var direcs =  [];			// if true, do directions - 7/13/09

		var which;			// marker last selected
		var i = 0;			// sidebar/icon index
	
		var nr_units = 	0;
		var email= false;
		
<?php

			function get_cd_str($in_row) {			// unit row in, 
				global $unit_id;	// 11/18/10
	//																			// first, already on this run?		
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE  `ticket_id` = " . quote_smart($_GET['ticket_id']) . "
					 AND (`responder_id`={$in_row['unit_id']}) 
					 AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) LIMIT 1;";	// 6/25/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if(mysql_affected_rows()==1) 			{return " CHECKED DISABLED ";}	
	
				if (($unit_id != "") && ((mysql_affected_rows()!=1) || ((mysql_affected_rows()==1) && (intval($in_row['multi'])==1))))		{print "checked";return " CHECKED ";}				// 12/18/10 - Checkbox checked here individual unit seleted.
				if (intval($in_row['dispatch'])==2) 	{return " DISABLED ";}				// 2nd, disallowed  - 5/30/10
				if (intval($in_row['multi'])==1) 		{return "";}						// 3rd, allowed
																				// 3rd, on another run?
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
					WHERE `responder_id`={$in_row['unit_id']} 
					AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
					LIMIT 1;";		// 6/25/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if(mysql_affected_rows()==1) 			{return " DISABLED ";}		// 3/30/10
				else							 		{return "";}
				}			// function get cd_str($in_row)
			

		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
												// build js array of responders to this ticket - possibly none
		$query = "SELECT `ticket_id`, `responder_id` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . quote_smart($_GET['ticket_id']);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
		
		while ($assigns_row = stripslashes_deep(mysql_fetch_array($result))) {
			print "\t\tunit_assigns[' '+ " . $assigns_row['responder_id']. "]= true;\n";	// note string forced
			}
		print "\n";

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`=" . quote_smart($_GET['ticket_id']) . " LIMIT 1;";	// 4/5/10
		$result_pos = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if(mysql_affected_rows()==1) {
			$row_position = stripslashes_deep(mysql_fetch_array($result_pos));
			$latitude = $row_position['lat'];
			$longitude = $row_position['lng'];
			unset($result_pos);
			}
		else {
//			dump ($query);
			}

		$where = (empty($unit_id))? "" : " AND `r`.`id` = $unit_id ";		// revised 5/23/08 per AD7PE 
		
		if(empty($unit_id)) {	// 11/18/10
			$where2 = (empty($capabilities))? "" : " AND (";	// 11/18/10
			$searchitems = (empty($capabilities))? "" : explode(" ", $capabilities);
			if($searchitems) {
				for($j = 0; $j < count($searchitems); $j++){
					if  ($j+1 != count($searchitems)) {
						$where2 .= "`$GLOBALS[mysql_prefix]responder`.`capab` LIKE '%{$searchitems[$j]}%' $searchtype ";
					} else {
						$where2 .= "`$GLOBALS[mysql_prefix]responder`.`capab` LIKE '%{$searchitems[$j]}%')";
					}
				}
			}
		} else {
			$where2="";
		}
// ============================= Regions Stuff							
			
			$al_groups = $_SESSION['user_groups'];

			$x=0;	
			$where3 = "WHERE (";	
			foreach($al_groups as $grp) {
				$where4 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where3 .= "`a`.`group` = '{$grp}'";
				$where3 .= $where4;
				$x++;
				}
			
			$where3 .= " AND (`a`.`type` = 2)";					
			
// ================================ end of regions stuff				

		// 4/5/10
		$query = "SELECT *, UNIX_TIMESTAMP(`updated`) AS `updated`,
			`r`.`id` AS `unit_id`, 
			`s`.`status_val` AS `unitstatus`, `contact_via`, 
			(POW(ABS({$latitude} - `r`.`lat`), 2.0) +  POW(ABS({$longitude} - `r`.`lng`), 2.0)) AS `distance`			
			FROM `$GLOBALS[mysql_prefix]responder` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`r`.`un_status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`r`.`id` = `a`.`resource_id`)					
			$where3 $where $where2 GROUP BY unit_id
			ORDER BY `distance` ASC, `handle` ASC, `name` ASC, `unit_id` ASC";		// 12/09/09

//		print $query;

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		if(mysql_affected_rows()>0) {
			$end_date = (intval($problemend)> 1)? $problemend:  (time() - (get_variable('delta_mins')*60));
//			dump(mysql_format_date($problemstart));
//			dump(mysql_format_date($end_date));
		
			$elapsed = my_date_diff($problemstart, $end_date);		// 5/13/10

?>
		side_bar_html += "<TR class='even'>	<TD CLASS='<?php print $severityclass; ?>' COLSPAN=99 ALIGN='center'><B>To Incident: <I><?php print shorten($row_ticket['scope'], 20) . "</I>&nbsp;&nbsp;(" .  $elapsed; ?>)</B></TD></TR>\n";
		side_bar_html += "<TR class='odd' STYLE = 'white-space:nowrap;'><TD COLSPAN=3></TD><TD ALIGN='center'>Unit</TD><TD ALIGN='center'>Call</TD><TD ALIGN='center'>Status</TD><TD ALIGN='center'>As of</TD></TR>\n";

<?php
													// major while ... for RESPONDER data starts here
			$i = $k = 1;				// sidebar/icon index
			while ($unit_row = stripslashes_deep(mysql_fetch_assoc($result))) {				// 7/13/09
				$has_coords = ((my_is_float($unit_row['lat'])) && (my_is_float($unit_row['lng'])));				// 2/25/09, 7/7/09
				$has_rem_source = ((intval ($unit_row['aprs'])==1)||(intval ($unit_row['instam'])==1)||(intval ($unit_row['locatea'])==1)||(intval ($unit_row['gtrack'])==1)||(intval ($unit_row['glat'])==1));		// 11/15/09

				if(is_email($unit_row['contact_via'])) {
					print "\t\temail= true\n";				
					}
?>
				nr_units++;

				var i = <?php print $i;?>;						// top of loop
				
				unit_names[i] = "<?php print addslashes($unit_row['name']);?>";	// unit name 8/25/08, 4/27/09
				unit_sets[i] = false;								// pre-set checkbox settings				
				unit_preselected = "<?php print $unit_id;?>";
				if (unit_preselected != "") {
					unit_sets[i] = true;								// pre-set checkbox settings	
					} else {
					unit_sets[i] = false;
					}

				
				unit_ids[i] = <?php print $unit_row['unit_id'];?>;
				new_element = document.createElement("input");								// please don't ask!
				new_element.setAttribute("type", 	"checkbox");
				new_element.setAttribute("name", 	"unit_<?php print $unit_row['unit_id'];?>");
				new_element.setAttribute("id", 		"element_id");
				new_element.setAttribute("style", 	"visibility:hidden");
				document.forms['routes_Form'].appendChild(new_element);
				var dist_mi = "na";
				var multi = <?php print (intval($unit_row['multi'])==1)? "true;\n" : "false;\n";?>	// 5/22/09
<?php
				$dispatched_to = (array_key_exists($unit_row['unit_id'], $dispatches_disp))?  $dispatches_disp[$unit_row['unit_id']]: "";

?>					
				sidebar_line = "<TD ALIGN='center'><INPUT TYPE='checkbox' <?php print get_cd_str($unit_row); ?> NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[<?php print $i; ?>]=this.checked;'></TD>";

				sidebar_line += "<TD TITLE = \"<?php print addslashes($unit_row['name']);?>\">";
				sidebar_line += "<NOBR><?php print shorten($unit_row['name'], 20);?></NOBR></TD>";
				sidebar_line += "<TD><NOBR><?php print shorten(addslashes($dispatched_to), 20); ?></NOBR></TD>";
				sidebar_line += "<TD TITLE = \"<?php print $unit_row['unitstatus'];?>\" CLASS='td_data'><?php print shorten($unit_row['unitstatus'], 12);?></TD>";
				sidebar_line += "<TD CLASS='td_data'><?php print substr(format_sb_date($unit_row['updated']), 4);?></TD>";
<?php
					print "\n\t\t\t\tdo_sidebar(sidebar_line, color, i);\n";
				$i++;
				$k++;
				}				// end major while ($unit_row = ...)  for each responder
			print "\t\t var start = 1;\n";	// already sorted - 3/24/10		
			}				// end if(mysql_affected_rows()>0)
		else {
			print "\t\t var start = 0;\n";	// already sorted - 3/24/10
			}			
			
//					responders complete
?>

		if (nr_units==0) {
			side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><BR /><H3>No Units!</H3></TD></TR>";	
			}
		else {
			side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>\n";
			side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>\n";
			side_bar_html+= "<TR><TD>&nbsp;</TD></TR>\n";
			}
				
		side_bar_html +="</TABLE>\n";
		document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side bar div

		if (start>0) {

			var current_id= "R"+start;			//
			document.getElementById(current_id).style.visibility = "visible";		// show link check image at the selected sidebar el ement
				location.href = "#top";				// 11/12/09
				}
	</SCRIPT>
	
<?php
	}				// end function do_list() ===========================================================
	
?>