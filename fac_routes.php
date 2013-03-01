<?php
/*
8/20/09 created fac_routes.php from routes.php - routing units to facilities
10/06/09 added links button
10/28/09 Mail Direcs button hidden on load, shown on select after timer
10/28/09 Add Loading Directions message in floating menu.
10/29/09 Added ticket scope to hidden form filed for passing to do_direcs_mail script
11/13/09 correction for apostrophe handling
7/16/10 detailmap.setCenter correction
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 map.setUIToDefault();
11/23/10 - mi vs km per locale
3/15/11	Added reference to revisable stylesheet for configurable colors.
5/4/11 get_new_colors() added
5/28/11 intrusion detection added
6/10/11 Added Regions / Groups
*/

$from_top = 20;				// buttons alignment, user-reviseable as needed
$from_left = 400;

error_reporting(E_ALL);

@session_start();
if ((isset($_REQUEST['ticket_id'])) && (!(strval(intval($_REQUEST['ticket_id']))===$_REQUEST['ticket_id']))) {	shut_down();}	// 5/28/11
require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));
if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
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
	
$api_key = get_variable('gmaps_api_key');
$conversion = get_dist_factor();				// KM vs mi - 11/23/10
$_GET = stripslashes_deep($_GET);
$eol = "< br />\n";

$u_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);
	}

$icons = $GLOBALS['icons'];	
$sm_icons = $GLOBALS['sm_icons'];
$fac_icons = $GLOBALS['fac_icons'];

function get_icon_legend (){
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_data = $u_types[$row['type']];
		$print .= "\t\t" .$type_data[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$type_data[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()

function do_fac($theFac, $theWidth, $search=FALSE, $dist=TRUE) {

	$print = "<TABLE BORDER='0'ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD CLASS='td_data' COLSPAN=2 ALIGN='center'><B>Facility: <I>" . highlight($search,$theFac['fac_name']) . "</B></TD></TR>\n";

	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Description:</TD>	<TD>" . highlight($search, nl2br($theFac['fac_descr'])) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Capability:</TD>	<TD>" . highlight($search, nl2br($theFac['capab'])) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Status:</TD>	<TD>" . $theFac['status_val'] . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Opening Hours:</TD>	<TD>" . $theFac['opening_hours'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Access Rules:</TD>	<TD>" . $theFac['access_rules'] . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Sec Reqs:</TD>	<TD>" . $theFac['security_reqs'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Cont name:</TD>	<TD>" . $theFac['contact_name'] . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Cont email:</TD>	<TD>" . $theFac['contact_email'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Cont phone:</TD>	<TD>" . $theFac['contact_phone'] . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Sec contact:</TD>	<TD>" . $theFac['security_contact'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Sec email:</TD>	<TD>" . $theFac['security_email'] . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Sec phone:</TD>	<TD>" . $theFac['security_phone'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Prim pager:</TD>	<TD>" . $theFac['pager_p'] . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Sec pager:</TD>	<TD>" . $theFac['pager_s'] . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD>Updated:</TD>		<TD>" . format_date($theFac['updated']) . "</TD></TR>\n";
//	$print .= "<TR CLASS='even'><TD>Status:</TD>		<TD>" . $theFac['status_id'] . "</TD></TR>\n";

	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theFac['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theFac['lng'] . "</SPAN></TD></TR>";
	$print .= "</TABLE>\n";

	return $print;
	}		// end function do_fac(

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">	
	<HEAD><TITLE>Tickets - Routes Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">		<!-- 3/15/11 -->
    <style type="text/css">
	.box { background-color: transparent; border: none; color: #000000; padding: 0px; position: absolute; }
	.bar { background-color: transparent; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em; }
	
	.box2 { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:10000; width: 180px; }
	.bar2 { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:10000; text-align: center;}
	.content { padding: 1em; text-align: center; }	
      body 					{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
      table.directions th 	{background-color:#EEEEEE;}	  
      img 					{color: #000000;}
    </style>
<SCRIPT>
	try {	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	
	function isNull(arg) {
		return arg===null;
		}

	function $() {
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
//]]></script>	
<?php

if (!empty($_POST)) {
	extract($_POST);
	$addrs = array();
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
?>	
<SCRIPT>
	function checkArray(form, arrayName)	{	//	5/3/11
		var retval = new Array();
		for(var i=0; i < form.elements.length; i++) {
			var el = form.elements[i];
			if(el.type == "checkbox" && el.name == arrayName && el.checked) {
				retval.push(el.value);
			}
		}
	return retval;
	}	
		
	function checkForm(form)	{	//	6/10/11
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

	function fvg_handleResult(req) {	// 6/10/11	The persist callback function for viewed groups.
		document.region_form.submit();
		}
		
	function form_validate(theForm) {	//	6/10/11
	//		alert("Validating");
		checkForm(theForm);
		}				// end function validate(theForm)

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
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

	var starting = false;

	function do_mail_win(addrs, fac_id) {	
		if(starting) {return;}					// dbl-click catcher
		starting=true;	
		var url = "mail_edit.php?fac_id=" + fac_id + "&addrs=" + addrs + "&text=";	// no text
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()



<?php 
	if(get_variable('call_board')==2) {
		print "\n\tparent.top.calls.location.reload(true);\n";
		}
?>	

</SCRIPT>
</HEAD>
<?php
	$addr_str = urlencode( implode("|", array_unique($addrs)));
	if (empty($addr_str)) {
		print "\n<BODY>\n";
		require_once('./incs/links.inc.php');
		}
	else {
		print "\n<BODY onLoad = \"do_mail_win('" . $addr_str . "', '" . $_POST['frm_fac_id'] . "')\">\n";
		require_once('./incs/links.inc.php');
		}
?>
	<CENTER><BR><BR><BR><BR><H3>Call Assignments made to:<BR /><?php print substr((str_replace ( "\n", ", ", $_POST['frm_name_str'])) , 0, -2);?><BR><BR> <!-- 11/8/08 -->
	See call Board</H3>
	<FORM NAME='cont_form' METHOD = 'get' ACTION = "main.php">
	<INPUT TYPE='button' VALUE='Continue' onClick = "document.cont_form.submit()">
	</FORM></BODY></HTML>
<?php		
	}		// end if (!empty($_POST))
else {		// 201-439
?>
<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT SRC="./js/usng.js"></SCRIPT>		<!-- 10/14/08 -->
<SCRIPT SRC="./js/graticule.js"></SCRIPT>
	

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
		for (var i=0; i< inArray.length; i++){
			if (parseFloat(inArray[i]) < parseFloat(minsofar)) {
				j=i;
				minsofar=inArray[i];
				}
			}
		return j;
		}		// end function min()

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()

<?php
	$addrs = FALSE;									// notifies address array doesn't exist
	if (array_key_exists ( "email", $_GET)) {	
		$addrs = notify_user(0,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
		}				// end if (array_key_exists())

	$dispatches = array();

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if(!(empty($row['theunit_id']))) {
			if ($row['multi']==1) {
				$dispatches[$row['theunit_id']] = "&nbsp;&nbsp;* ";
				}
			else {
				$dispatches[$row['theunit_id']] = (empty($row['clear']))? $row['thefac']:"";
				}		// end if/else(...)
			}
		}		// end while (...)

	$query = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.`description` AS `fac_descr`, `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name` FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `ty` ON (`$GLOBALS[mysql_prefix]facilities`.`type` = `ty`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `st` ON (`$GLOBALS[mysql_prefix]facilities`.`status_id` = `st`.`id`)		
		WHERE `$GLOBALS[mysql_prefix]facilities`.`id`=" . $_GET['fac_id'] . " LIMIT 1";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_fac = stripslashes_deep(mysql_fetch_array($result));
	unset ($result);

	print "var thelat = " . $row_fac['lat'] . ";\nvar thelng = " . $row_fac['lng'] . ";\n";		// set js-accessible location data
?>
</SCRIPT>
<BODY onLoad = "do_notify(); ck_frames()" onUnload="GUnload()">
<?php
require_once('./incs/links.inc.php');
?>
	<TABLE ID='outer' BORDER = 0 ID= 'main' STYLE='display:block' >
	<TR><TD VALIGN='top'><DIV ID='side_bar' STYLE='width: 400px'></DIV>
		<BR>
			<DIV ID='the_fac' style='width: 500px;'><?php print do_fac($row_fac, 500, FALSE, FALSE); ?></DIV>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR /><A HREF='#' onClick='doGrid()'><U>Grid</U>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doTraffic()'><U>Traffic</U>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF='#' onClick = "sv_win('<?php print $row_fac['lat'];?>','<?php print $row_fac['lng'];?>' );"><U>Street view</U></A>			
			<BR />
			<BR />
<?php
		print get_icon_legend ();
?>
			<BR />
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"></DIV>
		</TD></TR></TABLE><!-- end outer -->
	<DIV ID='bottom' STYLE='display:none'>
	<CENTER>
	<H3>Dispatching ... please wait ...</H3><BR /><BR /><BR />
<!-- 	<IMG SRC="./markers/spinner.gif" BORDER=0> -->
	</DIV>
		

	<FORM NAME='can_Form' ACTION="main.php">
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['fac_id'];?>">
	</FORM>	
	<FORM NAME='routes_Form' METHOD='post' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db'>
	<INPUT TYPE='hidden' NAME='frm_fac_id' 	VALUE='<?php print $_GET['fac_id']; ?>'>
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $_SESSION['user_id'];?>">
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1">
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New">
	</FORM>
	<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='fac_id' 	VALUE='<?php print $_GET['fac_id']; ?>'>
	</FORM>
	<DIV STYLE="position:fixed; width:120px; height:auto; top:<?php print $from_top;?>px; left:<?php print $from_left;?>px; background-color: transparent;">	<!-- 5/17/09, 7/7/09 -->
		
<?php
			$thefunc = (is_guest())? "guest()" : "validate()";		// disallow guest attempts
	$nr_units = 1;

	print "<SPAN ID=\"mail_button\" STYLE=\"display: 'none'\">";
	print "<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='_blank' onsubmit='return mail_direcs(this);'>";
	print "<INPUT TYPE='hidden' NAME='frm_direcs' VALUE=''>";
	print "<INPUT TYPE='hidden' NAME='frm_u_id' VALUE=''>";
	print "<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Facility'>";
	print "<INPUT TYPE='hidden' NAME='frm_scope' VALUE=''>"; // 10/29/09
	print "<INPUT TYPE='submit' value='Mail Direcs' ID = 'mail_dir_but' />";
	print "</FORM>";
	print "<INPUT TYPE='button' VALUE='Reset' onClick = 'doReset()' />";
	print "</SPAN>";
	print "<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();' />";
	print "<SPAN ID=\"loading\" STYLE=\"display: 'inline-block'\">";
	print "<TABLE BGCOLOR='red' WIDTH='80%'><TR><TD><FONT COLOR='white'><B>Loading Directions, Please wait........</B></FONT></TD></TR></TABLE>";		// 10/28/09
	print "</SPAN>";
?>
	</DIV>
<?php
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
		$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
		$group = get_regions_inuse_numbers($user_level);	//	6/10/11		
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 4/13/11
		$result = mysql_query($query);	// 4/13/11
		$al_groups = array();
		$al_names = "";	
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/13/11
			$al_groups[] = $row['group'];
			if(!(is_super())) {
				$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 4/13/11
				$result2 = mysql_query($query2);	// 4/13/11
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 4/13/11		
					$al_names .= $row2['group_name'] . ", ";
					}
				} else {
					$al_names = "ALL. Superadmin Level";
				}
			}

?>				
		<A NAME="page_bottom" /> <!-- 5/13/10 -->	
		<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
		<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print get_ticket_id (); ?>' />	<!-- 10/25/08 -->
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
		var theText= "ATTENTION - New Facility Routing: ";
		var theId = '<?php print $_GET['fac_id'];?>';
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_fac_id=" + escape(theId);		// ($to_str, $text, $fac_id)
		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_fac_id=" + theId ;		// ($to_str, $text, $fac_id)
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $fac_id)
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function  - ignore returned data
		}

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
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
			}
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	print do_list($unit_id);
	print "</HTML> \n";

	}			// end if/else !empty($_POST)

function do_list($unit_id ="") {
	global $row_fac, $dispatches, $from_top, $from_left, $eol, $conversion;
	
?>
<SCRIPT>
	var color=0;
	var last_from;
	var last_to;
	var current_id;
	var output_direcs = "";
	var have_direcs = 0;
	var fac_name = "<?php print $row_fac['fac_name'];?>";	//10/29/09 - 11/13/09
	
	if (GBrowserIsCompatible()) {
		var colors = new Array ('odd', 'even');
	    function setDirections(fromAddress, toAddress, locale, unit_id) {
		$("mail_button").style.display = "none";
		$("loading").style.display = "inline-block";		// 10/28/09
	    	last_from = fromAddress;
	    	last_to = toAddress;
		f_unit = unit_id;
		   	G_START_ICON.image = "./our_icons/sm_white.png";
		   	G_START_ICON.iconSize = new GSize(12,20); 
		   	G_END_ICON.image = "./our_icons/sm_white.png";
		   	G_END_ICON.iconSize = new GSize(12,20);         	

	    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale, preserveViewport : true  });
			GEvent.addListener(Direcs, "addoverlay", GEvent.callback(Direcs, cb())); 
	    	}		// end function set Directions()

		function cb() {
			setTimeout(cb2,3000);     // I THINK you need quotes around the named function - here's 2 seconds of delay
		}      // end function cb()


		function cb2() {                                        // callback function 09/11/09
			var output_direcs = "";
			for ( var i = 0; i < gdir.getNumRoutes(); i++) {        // Traverse all routes - not really needed here, but ...
				var groute = gdir.getRoute(i);
				var distanceTravelled = 0;             // if you want to start summing these
 
				for ( var j = 0; j < groute.getNumSteps(); j++) {                // Traverse the steps this route
					var gstep = groute.getStep(j);
					var directions_text =  gstep.getDescriptionHtml();
					var directions_dist = gstep.getDistance().html;
//					alert ("1387 " + gstep.getDescriptionHtml());
//					alert ("1388 " + gstep.getDistance().html);
					output_direcs = output_direcs + directions_text + " " + directions_dist + ". " + "\n";

				}
			}
			output_direcs = output_direcs.replace("<div class=\"google_note\">", "\n - ");
			output_direcs = output_direcs.replace("&nbsp:", " ");
			document.email_form.frm_direcs.value = output_direcs;
			document.email_form.frm_u_id.value = f_unit;
			document.email_form.frm_scope.value = fac_name;	//10/29/09
			$("mail_button").style.display = "inline-block";	//10/6/09
			$("loading").style.display = "none";		// 10/28/09			
		}                // end function cb2()

		function mail_direcs(f) {
			f.target = 'Mail Form'
			newwindow_mail=window.open('',f.target,'titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300');
			if (isNull(newwindow_mail)) {
				alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
				return;
				}
			newwindow_mail.focus();
			f.submit();
			return false;
			}

	
		function do_sidebar(sidebar, color, id, unit_id) {						// No map
			var letter = ""+ id;	
			marker = null;
			gmarkers[id] = null;										// marker to array for side_bar click function
	
			side_bar_html += "<TR CLASS='" + colors[(id+1)%2] +"' VALIGN='bottom' onClick = myclick(" + id + "," + unit_id +");><TD>";
			side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;'></TD>";
			var letter = ""+ id;	
			var the_class = (lats[id])?  "emph" : "td_label";

			side_bar_html += "<TD CLASS='" + the_class + "'>" + letter + ". "+ sidebar +"</TD></TR>\n";
			return null;
			}				// end function create Marker()


		function createMarker(point,sidebar,tabs, color, id, unit_id) {		// Creates marker and sets up click event infowindow
			do_sidebar(sidebar, color, id, unit_id)
			var icon = new GIcon(listIcon);
			var uid = unit_id;
			var letter = ""+ id;	
			var icon_url = "./our_icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;
	
			icon.image = icon_url;		// ./our_icons/gen_icon.php?blank=4&text=zz"
			var marker = new GMarker(point, icon);
			marker.id = color;				// for hide/unhide - unused
		
			GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);
				var dMapDiv = document.getElementById("detailmap");
				var detailmap = new GMap2(dMapDiv);
//				detailmap.addControl(new GSmallMapControl());
				map.setUIToDefault();										// 8/13/10

				detailmap.setCenter(point, 17);  					// larger # = closer - 7/16/10
				detailmap.addOverlay(marker);
				});
		
			gmarkers[id] = marker;							// marker to array for side_bar click function
			infoTabs[id] = tabs;							// tabs to array
			bounds.extend(point);							// extend the bounding box		
			return marker;
			}				// end function create Marker()
	
		function myclick(id, unit_id) {								// responds to side bar click - 11/13/09
//			alert("550 " + direcs[id]);
			which = id;
			document.getElementById(current_id).style.visibility = "hidden";		// hide last check
			current_id= "R"+id;
			document.getElementById(current_id).style.visibility = "visible";		// show newest
			if (!(lats[id])) {
				alert("611 Cannot route -  no position data currently available\n\nClick map point for directions.");
				$('directions').innerHTML = "";							// 11/13/09	
				$('mail_dir_but').style.visibility = "hidden";			// 11/13/09	
				}
			else {
				$('mail_dir_but').style.visibility = "visible";			// 11/13/09	
				var thelat = <?php print $row_fac['lat'];?>; var thelng = <?php print $row_fac['lng'];?>;		// coords of click point
				setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, "en_US", unit_id);							// get directions
				}
			}					// end function my click(id)
	
		var the_grid;
		var grid = false;
		function doGrid() {
			if (grid) {
				map.removeOverlay(the_grid);
				}
			else {
				the_grid = new LatLonGraticule();
				map.addOverlay(the_grid);
				}
			grid = !grid;
			}			// end function doGrid
			
	    var trafficInfo = new GTrafficOverlay();
	    var toggleState = true;
	
		function doTraffic() {
			if (toggleState) {
		        map.removeOverlay(trafficInfo);
		     	} 
			else {
		        map.addOverlay(trafficInfo);
		    	}
	        toggleState = !toggleState;			// swap
		    }				// end function doTraffic()
	
		var starting = false;

		function sv_win(theLat, theLng) {
			if(starting) {return;}						// dbl-click proof
			starting = true;					
//			alert(622);
			var url = "street_view.php?thelat=" + theLat + "&thelng=" + theLng;
			newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
			if (!(newwindow_sl)) {
				alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
				return;
				}
			newwindow_sl.focus();
			starting = false;
			}		// end function sv win()

		
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
			for (var i =1;i<unit_sets.length;i++) {
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
				if (confirm ("Please confirm Unit dispatch as follows\n\n" + msgstr)) {
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$icons = $GLOBALS['fac_icons'];
	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// map type to blank icon id
		$blank = $icons[$row['icon']];
		print "\ticons[" . $row['id'] . "] = " . $row['icon'] . ";\n";	// 
		}
	unset($result);
?>
		var map;
		var center;
		var zoom;
		
	    var gdir;				// directions
	    var geocoder = null;
	    var addressMarker;
		$("mail_button").style.display = "none";		// 10/28/09
		$("loading").style.display = "none";		// 10/28/09
		
		var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
		side_bar_html += "<TR class='even'>	<TD  COLSPAN=99 ALIGN='center'><B>Routes to Facility: <I><?php print shorten($row_fac['fac_name'], 20); ?></I></B></TD></TR>\n";
		side_bar_html += "<TR class='odd'>	<TD COLSPAN=99 ALIGN='center'>Click line, icon or map for route</TD></TR>\n";
		side_bar_html += "<TR class='even'>	<TD COLSPAN=3></TD><TD ALIGN='center'>Unit</TD><TD ALIGN='center'>SLD</TD><TD ALIGN='center'>Facility</TD><TD ALIGN='center'>Status</TD><TD ALIGN='center'>As of</TD></TR>\n";

		var gmarkers = [];
		var infoTabs = [];
		var lats = [];
		var lngs = [];
		var distances = [];
		var unit_names = [];			// names
		var unit_contacts = [];		// contact emails
		var unit_sets = [];			// settings
		var unit_ids = [];			// id's
		var unit_assigns =  [];		// unit id's assigned this incident
		var direcs =  [];			// if true, do directions
		var which;			// marker last selected
		var i = 0;			// sidebar/icon index
	
		map = new GMap2(document.getElementById("map_canvas"));		// create the map
//		map.addControl(new GSmallMapControl());
		map.setUIToDefault();										// 8/13/10

		map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	

		gdir = new GDirections(map, document.getElementById("directions"));
		
		GEvent.addListener(gdir, "load", onGDirectionsLoad);
		GEvent.addListener(gdir, "error", handleErrors);
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>
	
		var bounds = new GLatLngBounds();						// create empty bounding box
	
		var listIcon = new GIcon();
		listIcon.image = "./markers/yellow.png";	// yellow.png - 16 X 28
//		listIcon.shadow = "./markers/sm_shadow.png";
		listIcon.iconSize = new GSize(20, 34);
//		listIcon.shadowSize = new GSize(37, 34);
		listIcon.iconAnchor = new GPoint(8, 28);
		listIcon.infoWindowAnchor = new GPoint(9, 2);
//		listIcon.infoShadowAnchor = new GPoint(18, 25);
	
		var newIcon = new GIcon();
		newIcon.image = "./markers/white.png";	// yellow.png - 20 X 34
//		newIcon.shadow = "./markers/shadow.png";
		newIcon.iconSize = new GSize(20, 34);
//		newIcon.shadowSize = new GSize(37, 34);
		newIcon.iconAnchor = new GPoint(8, 28);
		newIcon.infoWindowAnchor = new GPoint(9, 2);
//		newIcon.infoShadowAnchor = new GPoint(18, 25);
																	// set Incident position
		var point = new GLatLng(<?php print $row_fac['lat'];?>, <?php print $row_fac['lng'];?>);
		bounds.extend(point);

	
		GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
			setDirections(last_from, last_to, "en_US") ;
			});
		var accept_click = false;
		GEvent.addListener(map, "click", function(marker, point) {		// point.lat()
			var the_start = point.lat().toString() + "," + point.lng().toString();
			var the_end = thelat.toString() + "," + thelng.toString();			
			setDirections(the_start, the_end, "en_US");			
			});				// end GEvent.addListener()

		var nr_units = 	0;
		var email= false;
	  	var km2mi = <?php print $conversion ;?>;				// 
		
<?php
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
												// build js array of responders to this ticket - possibly none
		$where = (empty($unit_id))? "" : " WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit_id ";		// revised 5/23/08 per AD7PE 
		$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]responder`.`id` AS `unit_id`, `s`.`status_val` AS `unitstatus`, `contact_via` FROM $GLOBALS[mysql_prefix]responder
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]responder`.`un_status_id` = `s`.`id`)
			$where
			ORDER BY `name` ASC, `unit_id` ASC";	

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if(mysql_affected_rows()>0) {
													// major while ... for RESPONDER data starts here
			$i = $k = 1;				// sidebar/icon index
			while ($unit_row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$has_coords = ((my_is_float($unit_row['lat'])) && (my_is_float($unit_row['lng'])));

				if(is_email($unit_row['contact_via'])) {
					print "\t\temail= true\n";				
					}
?>
				nr_units++;

				var i = <?php print $i;?>;						// top of loop
				
				unit_names[i] = "<?php print addslashes($unit_row['name']);?>";
				unit_sets[i] = false;								// pre-set checkbox settings				
				unit_ids[i] = <?php print $unit_row['unit_id'];?>;
				distances[i]=9999.9;
 				direcs[i] = <?php print (intval($unit_row['direcs'])==1)? "true": "false";?>;
<?php
				if ($has_coords) {
//					snap (__LINE__, $unit_row['unit_id']);
					$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
					$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . shorten($unit_row['name'], 48) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $unit_row['description']), 32) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Status:</TD><TD>" . $unit_row['unitstatus'] . " </TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Contact:</TD><TD>" . $unit_row['contact_name']. " Via: " . $unit_row['contact_via'] . "</TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($unit_row['updated']) . "</TD></TR>";
					$tab_1 .= "</TABLE>";
					}
?>
//				new_element = document.createElement("input");								// please don't ask!
//				new_element.setAttribute("type", 	"checkbox");
//				new_element.setAttribute("name", 	"unit_<?php print $unit_row['unit_id'];?>");
//				new_element.setAttribute("id", 		"element_id");
//				new_element.setAttribute("style", 	"visibility:hidden");
//				document.forms['routes_Form'].appendChild(new_element);
				var dist_mi = "na";
				var multi = <?php print (intval($unit_row['multi'])==1)? "true;\n" : "false;\n";?>	
<?php
				$dispatched_to = (array_key_exists($unit_row['unit_id'], $dispatches))?  $dispatches[$unit_row['unit_id']]: "";
				if ($has_coords ) {
?>		
					lats[i] = <?php print $unit_row['lat'];?>; 		// 774 now compute distance - in km
					lngs[i] = <?php print $unit_row['lng'];?>;
					distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_fac['lat'];?>), parseFloat(<?php print $row_fac['lng'];?>));
					var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to miles
<?php					
					}
				else {
?>
					distances[i] = 9999.9;
					var dist_mi = "na";
<?php
					}

				if (!(empty($unit_row['callsign']))) {
					$thespeed = "";
					$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
						WHERE `source`= '$unit_row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";

					$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {		// got a track?
					
						$track_row = stripslashes_deep(mysql_fetch_array($result_tr));			// most recent track report
			
						$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
						$tab_2 .= "<TR><TH CLASS='even' COLSPAN=2>" . $track_row['source'] . "</TH></TR>";
						$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $track_row['course'] . ", Speed:  " . $track_row['speed'] . ", Alt: " . $track_row['altitude'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $track_row['closest_city'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $track_row['status'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($track_row['packet_date']) . "</TD></TR>";
						$tab_2 .= "</TABLE>";
?>
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['name'], 8));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("<?php print $track_row['source']; ?>", "<?php print $tab_2;?>"),
							new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
							];
<?php
						$thespeed = ($track_row['speed'] == 0)?"<FONT COLOR='red'><B>&bull;</B></FONT>"  : "<FONT COLOR='green'><B>&bull;</B></FONT>" ;
						if ($track_row['speed'] >= 50) { $thespeed = "<FONT COLOR='WHITE'><B>&bull;</B></FONT>";}
?>
						var point = new GLatLng(<?php print $track_row['latitude'];?>, <?php print $track_row['longitude'];?>);
						bounds.extend(point);								// point into BB
<?php
						}			// end if (mysql_affected_rows()>0;) for track data
					else {				// no track data
					
						$k--;			// not a clickable unit for dispatch
?>
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['name'], 12));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
							];
<?php						
						}				// end  no track data
					}		// if has callsign
			
				else {				// no callsign
					if ($has_coords) {
?>
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['name'], 12));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
							];
						
						lats[i] = <?php print $unit_row['lat'];?>; // 819 now compute distance - in km
						lngs[i] = <?php print $unit_row['lng'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_fac['lat'];?>), parseFloat(<?php print $row_fac['lng'];?>));	// note: km
					    var km2mi = <?php print $conversion ;?>;				// 
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to feet
<?php
						}		// end if ($has_coords)

					$thespeed = "";
					}									// END IF/ELSE (callsign)

				print (((!(intval($unit_row['multi'])==1))) && isset($dispatches[$unit_row['unit_id']]))? "\n\tvar is_checked =  ' CHECKED ';\n\tvar is_disabled =  ' DISABLED ';\n": "\n\tvar is_checked =  '';\n\tvar is_disabled =  '';\n";
?>					
				sidebar_line = "<TD ALIGN='center'><INPUT TYPE='hidden' NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[<?php print $i; ?>]=this.checked;'></TD>";

				sidebar_line += "<TD TITLE = \"<?php print addslashes($unit_row['name']);?>\">";
				sidebar_line += "<NOBR><?php print shorten($unit_row['name'], 20);?></NOBR></TD>";

				sidebar_line += "<TD>"+ dist_mi+"</TD>"; // 8/25/08, 4/27/09
				sidebar_line += "<TD><NOBR><?php print shorten(addslashes($dispatched_to), 20); ?></NOBR></TD>";
				sidebar_line += "<TD TITLE = \"<?php print $unit_row['unitstatus'];?>\" CLASS='td_data'><?php print shorten($unit_row['unitstatus'], 12);?></TD>";
//				sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD>";
				sidebar_line += "<TD CLASS='td_data'><?php print substr(format_sb_date($unit_row['updated']), 4);?></TD>";
<?php
				if (($has_coords)) {		//  2/25/09
?>		
					var point = new GLatLng(<?php print $unit_row['lat'];?>, <?php print $unit_row['lng'];?>);	//  840 for each responder 832
					var unit_id = <?php print $unit_row['unit_id'];?>;
					bounds.extend(point);																// point into BB
					var marker = createMarker(point, sidebar_line, myinfoTabs,<?php print $unit_row['type'];?>, i, unit_id);	// (point,sidebar,tabs, color, id, unit_id)
					if (!(isNull(marker))) {
						map.addOverlay(marker);
						}
<?php
					}				// end if ($has_coords) 
				else {
					print "\n\tdo_sidebar(sidebar_line, color, i);\n";
					}		// end if/else ($has_coords)
				$i++;
				$k++;
				}				// end major while ($unit_row = ...)  for each responder
				
			}				// end if(mysql_affected_rows()>0)
?>
 		var point = new GLatLng(<?php echo $row_fac['lat']; ?>, <?php echo $row_fac['lng']; ?>);	//
		var baseIcon = new GIcon();
		var inc_icon = new GIcon(baseIcon, "./markers/sm_black.png", null);
		var thisMarker = new GMarker(point);
		map.addOverlay(thisMarker);

		if (nr_units==0) {
			side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><B>No Units!</B></TD></TR>";;		
			map.setCenter(new GLatLng(<?php echo $row_fac['lat']; ?>, <?php echo $row_fac['lng']; ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);		// -1 for further out	
			map.setCenter(center,zoom);
			side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>\n";
			side_bar_html+= "<TR><TD>&nbsp;</TD></TR>\n";
			}
				
		side_bar_html +="</TABLE>\n";
		document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div

		var thelat = <?php print $row_fac['lat'];?>; var thelng = <?php print $row_fac['lng'];?>;
		var start = min(distances);		// min straight-line distance to Incident

		if (start>0) {
			var current_id= "R"+start;			//
//			document.getElementById(current_id).style.visibility = "visible";		// show link check image at the selected sidebar el ement
//			if (lats[start]) {
//				setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, "en_US");
//				}
			}
		}		// end if (GBrowserIsCompatible())

	else {
		alert("Sorry,  browser compatibility problem. Contact your tech support group.");
		}

	</SCRIPT>
	
<?php
	}				// end function do_list() ===========================================================
	
?>