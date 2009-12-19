<?php
error_reporting(E_ALL);
/*
5/23/08 per AD7PE - line 432
8/25/08 handling sgl quotes in unit names
8/25/08 TITLE to td's
9/23/08 small map control
10/7/08	added auto-mail feature
10/13/08 added onClick() directions
10/13/08 accommodate no location data
10/14/08 added graticule
10/16/08 changed ticket_id to frm_ticket_id - tbd
10/16/08 added traffic functions
10/17/08 allow map click for directions if error
10/25/08 pointer housekeeping when can't route
10/26/08 always accept click
11/8/08 commas as separator
1/21/09 added show butts - re button menu
1/29/09 icon letter to number
2/15/09 added do_mail_win() for mail text editing
2/25/09 handle empty lat/lng
3/30/09 drop htmlentities for utf-8 handling
4/27/09 addslashes vs htmlentities, for easy
5/22/09	Multi handling, 
6/3/09	checkbox relocated
6/14/09	guest handling corrected
7/7/09	float check corrected, div transparent
7/13/09	fetch_assoc, direcs array
7/24/09	pick up in_types for protocol display
8/2/09	floating div location revised
8/7/09	disallow multiple assigns unless 'multi' is set
8/10/09	`tick_descr` added to query to resolve 'description' ambiguity
8/17/09	 street view added, select only cleared units
10/6/09 Added multi point routes for receiving facility and mail route to unit capability, added links button
10/28/09 Mail Direcs button hidden on load, shown on select after timer
10/28/09 Add Loading Directions message in floating menu.
10/29/09 Added ticket scope to hidden form filed for passing to do_direcs_mail script
11/12/09 corrections for 'direcs' handling, array indexing
11/15/09 revised logic re identifying units with position data
11/23/09 'quick' operation restored
11/27/09 relocated incident information to underneath map, added address to floating div
*/

$from_top = 20;				// buttons alignment, user-reviseable as needed
$from_left = 400;
$show_tick_left = FALSE;	// controls left-side vs. right-side appearance of incident details - 11/27/09

require_once('./incs/functions.inc.php');
$my_session = do_login(basename(__FILE__));		// returns session array
//snap(__LINE__, basename(__FILE__));

if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
	
session_start(); 								// 11/21/09
if (!(isset ($_SESSION['allow_dirs']))) {	
	$_SESSION['allow_dirs'] = 'true';			// note js-style LC
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

$icons = $GLOBALS['icons'];				// 1/1/09
$sm_icons = $GLOBALS['sm_icons'];

function get_icon_legend (){			// returns legend string - 1/1/09
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle` ASC, `name` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_data = $u_types[$row['type']];
		$print .= "\t\t" .$type_data[0] . " &raquo; <IMG SRC = './icons/" . $sm_icons[$type_data[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">	
	<HEAD><TITLE>Tickets - Routes Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
    <style type="text/css">
		body 				{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 				{border-collapse: collapse; }
		table.directions th {background-color:#EEEEEE;}	  
		img 				{color: #000000;}
		span.even 			{background-color: #DEE3E7;}
		span.warn			{display:none; background-color: #FF0000; color: #FFFFFF; font-weight: bold; font-family: Verdana, Arial, sans serif; }

		span.mylink			{margin-right: 32PX; text-decoration:underline; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_1		{margin-right: 32PX; text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_2		{margin-right: 8PX;  text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
    	</style>

<SCRIPT>
	try {	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
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
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

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
	<INPUT TYPE='button' VALUE='Continue' onClick = "document.cont_form.submit()">
	</FORM></BODY></HTML>
<?php		
	}		// end if (!empty($_POST))
else {		// 201-439

?>
<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2.s&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT SRC="./js/usng.js"></SCRIPT>		<!-- 10/14/08 -->
<SCRIPT SRC="./js/graticule.js"></SCRIPT>
	

<SCRIPT>
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
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
		`r`.`id` AS `theunit_id` FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		AND `clear` IS NULL ";				// 8/17/09

//	dump($query);
	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if(!(empty($row['theunit_id']))) {
			$dispatches_act[$row['theunit_id']] = (empty($row['clear']))? $row['ticket_id']:"";	// blank = unit unassigned

			if ($row['multi']==1) {
				$dispatches_disp[$row['theunit_id']] = "&nbsp;&nbsp;* ";					// identify as multiple - 5/22/09
//				print __LINE__;
//				dump($dispatches_disp);
				}
			else {
				$dispatches_disp[$row['theunit_id']] = (empty($row['clear']))? $row['theticket']:"";	// blank = unit unassigned
//				print __LINE__;
//				dump($dispatches_disp);				
				}		// end if/else(...)
			}
		}		// end while (...)

//										8/10/09, 10/6/09
	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(booked_date) AS booked_date,
		UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated, `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`, `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`, `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`, `rf`.`name` AS `rec_fac_name`, `rf`.`lat` AS `rf_lat`, `rf`.`lng` AS `rf_lng`, `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`, `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`=" . $_GET['ticket_id'] . " LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

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
		
//		print "var thereclat = " . $rf_lat . ";\nvar thereclng = " . $rf_lng . ";\n";		// set js-accessible location data for receiving facility
//		dump($row_rec_fac);
		unset ($result_rfc);
		} else {
//		print "var thereclat;\nvar thereclng;\n";		// set js-accessible location data for receiving facility

	}
?>
</SCRIPT>
<BODY onLoad = "do_notify(); ck_frames()" onunload="GUnload()">
<A NAME='top'>
	<TABLE ID='outer' BORDER = 0 ID= 'main' STYLE='display:block'>
	<TR><TD VALIGN='top'><DIV ID='side_bar' STYLE='width: 400px'></DIV>
<?php
	$the_width = get_variable('map_width');

	if ($show_tick_left) { 				// 11/27/09
		print "\n<BR>\n<DIV ID='the_ticket' STYLE='width: " .  get_variable('map_width') . "'>\n";	
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
?>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR />
			<SPAN CLASS = "mylink" onClick ='doGrid()'>Grid</SPAN>
			<SPAN CLASS = "mylink" onClick ='doTraffic()'>Traffic</SPAN>
			<SPAN CLASS = "mylink" onClick = "sv_win('<?php print $row_ticket['lat'];?>','<?php print $row_ticket['lng'];?>' );">Street view</SPAN> <!-- 8/17/09 -->
			<SPAN CLASS = "warn" ID = "loading_2">Loading Directions, Please wait........</SPAN>
			<SPAN CLASS = "even" ID = "directions_ok_no">&nbsp;
			<SPAN CLASS = "other_1">Directions&nbsp;&raquo;</SPAN>
			<SPAN CLASS = "other_2">
<?php
		$checked_ok = ($_SESSION['allow_dirs'] =='true')? " CHECKED ": "";
		$checked_no = ($_SESSION['allow_dirs'] =='true')? "": " CHECKED ";
?>
				OK: <INPUT TYPE='radio' name='frm_dir' VALUE = true  <?php print $checked_ok; ?> onClick = "docheck(this.value);" />&nbsp;&nbsp;
				No: <INPUT TYPE='radio' name='frm_dir' VALUE = false <?php print $checked_no; ?> onClick = "docheck(this.value);" /></SPAN>
				&nbsp;</SPAN></SPAN>
			<BR />
			<BR />
<?php
		print get_icon_legend ();
?>
			<BR /><BR />
<?php
	if (!($show_tick_left)) {				// 11/27/09
		print "\n<DIV ID='the_ticket' STYLE='width: " .  get_variable('map_width') . "'>\n";	
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
?>
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"></DIV>
		</TD></TR></TABLE><!-- end outer -->
	<DIV ID='bottom' STYLE='display:none'>
	<CENTER>
	<H3>Dispatching ... please wait ...</H3><BR /><BR /><BR />
<!-- 	<IMG SRC="./markers/spinner.gif" BORDER=0> -->
	</DIV>
		

	<FORM NAME='can_Form' ACTION="main.php">
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>"/>	
	</FORM>	

	<FORM NAME='routes_Form' METHOD='post' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db'>
	<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>'>
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $my_session['user_id'];?>">
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1">
	<INPUT TYPE='hidden' NAME='frm_facility_id' 	VALUE= "<?php print $facility;?>"> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_rec_facility_id' VALUE= "<?php print $rec_fac;?>"> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New">
	<INPUT TYPE='hidden' NAME='frm_allow_dirs' VALUE = <?php print $_SESSION['allow_dirs']; ?> />	<!-- 11/21/09 -->
	</FORM>
	<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>'>	<!-- 10/25/08 -->
	</FORM>
	<!-- 8/2/09 -->
	<DIV STYLE="position:fixed; width:120px; height:auto; top:<?php print $from_top;?>px; left:<?php print $from_left;?>px; background-color: transparent;">	<!-- 5/17/09, 7/7/09 -->
		
<?php
			function get_addr(){				// returns incident address 11/27/09
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`={$_GET['ticket_id']} LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_array($result));
				return "{$row['street']} {$row['city']} {$row['state']}"; 
				}		// end function get_addr()

			$thefunc = (is_guest())? "guest()" : "validate()";		// disallow guest attempts
			$nr_units = 1;
			$addr = get_addr();

			print "<SPAN ID=\"mail_button\" STYLE=\"display: 'none'\">";	//10/6/09
			print "<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='_blank' onsubmit='return mail_direcs(this);'>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_direcs' VALUE=''>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_u_id' VALUE=''>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Incident'>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_scope' VALUE=''>"; // 10/29/09
			print "<INPUT TYPE='submit' value='Mail Direcs' ID = 'mail_dir_but' />";	//10/6/09
			print "</FORM>";	
			print "<INPUT TYPE='button' VALUE='Reset' onClick = 'doReset()' />";
			print "</SPAN>";			
			print "<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();' />";
			if ($nr_units>0) {			
				print "<BR /><INPUT TYPE='button' value='DISPATCH\nUNITS' onClick = '" . $thefunc . "' />\n";	// 6/14/09
				}
			print "<BR /><BR /><SPAN STYLE='display: 'inline-block'><NOBR><H2><I>&nbsp;&nbsp;{$addr}</I></H2></NOBR></SPAN>\n";
			print "<SPAN ID=\"loading\" STYLE=\"display: 'inline-block'\">";
			print "<TABLE BGCOLOR='red' WIDTH='80%'><TR><TD><FONT COLOR='white'><B>Loading Directions, Please wait........</B></FONT></TD></TR></TABLE>";		// 10/28/09
			print "</SPAN>";

?>
	</DIV>
	
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
//	print __LINE__;
			}
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	print do_list($unit_id);
	print "</HTML> \n";

	}			// end if/else !empty($_POST)

function do_list($unit_id ="") {
	global $row_ticket, $my_session, $dispatches_disp, $dispatches_act, $from_top, $from_left, $eol;
	
	switch($row_ticket['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
		default: 							$severityclass=''; break;
		}

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(booked_date) AS booked_date,
		UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated, `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`=" . $_GET['ticket_id'] . " LIMIT 1";			// 7/24/09 10/16/08 Incident location 09/25/09 Pre Booking

//	print __LINE__;
//	dump($query);

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	$facility = $row_ticket['facility'];
	$rec_fac = $row_ticket['rec_facility'];
	$lat = $row_ticket['lat'];
	$lng = $row_ticket['lng'];
	
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

	if (GBrowserIsCompatible()) {
		var colors = new Array ('odd', 'even');
		
		var Direcs = null;			// global
		var Now;
		var mystart;
		var myend;
	    function setDirections(fromAddress, toAddress, recfacAddress, locale, unit_id) {	//10/6/09

			if (document.routes_Form.frm_allow_dirs.value==='false') {return false;}		// 11/21/09
			
			$("mail_button").style.display = "none";			//10/6/09
			$("loading").style.display = "inline-block";		// 10/28/09

			$("directions_ok_no").style.display = "none";
			$("loading_2").style.display = "inline-block";
			
		    last_from = fromAddress;
		    last_to = toAddress;
			rec_fac = recfacAddress;
			f_unit = unit_id;	//10/6/09
			G_START_ICON.image = "./icons/sm_white.png";
			G_START_ICON.iconSize = new GSize(12,20); 
			G_END_ICON.image = "./icons/sm_white.png";
			G_END_ICON.iconSize = new GSize(12,20);         	

			Now = new Date();      				// Grab the current date.
			mystart = Now.getTime(); 		// Initialize variable Start
	
			if (rec_fac != "") {	//10/6/09
			    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress + " to: " + recfacAddress, { "locale": locale, preserveViewport : true  });
					}
				else{
			    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale, preserveViewport : true  });
					}
				GEvent.addListener(Direcs, "addoverlay", GEvent.callback(Direcs, cb2())); 		// 11/21/09
		    	}		// end function set Directions()

//		function cb() {	//10/6/09
//			Now = new Date();      				// Grab the current date.
//			var myend = Now.getTime(); 		// Initialize variable Start
//			alert("708 " + (myend - mystart));
//	
//			setTimeout("cb2()",1);     		// (confirmed) I THINK you need quotes around the named function - here's 2 seconds of delay
//			setTimeout("cb2()",2000);		// I THINK you need quotes around the named function - here's 2 seconds of delay
//			}      // end function cb()

		function cb2() {                               // callback function 10/6/09
			var output_direcs = "";
			for ( var i = 0; i < gdir.getNumRoutes(); i++) {        // Traverse all routes - not really needed here, but ...
				var groute = gdir.getRoute(i);
				var distanceTravelled = 0;             // if you want to start summing these
 
				for ( var j = 0; j < groute.getNumSteps(); j++) {                // Traverse the steps this route
					var gstep = groute.getStep(j);
					var directions_text =  gstep.getDescriptionHtml();
					var directions_dist = gstep.getDistance().html;
					output_direcs = output_direcs + directions_text + " " + directions_dist + ". " + "\n";
					}
				}
//			alert("Raw Output" + output_direcs);	//10/6/09
			output_direcs = output_direcs.replace("<div class=\"google_note\">", "\n -");	//10/6/09
			output_direcs = output_direcs.replace("Destination", "\n***Destination");	//10/6/09
			output_direcs = output_direcs.replace("&nbsp:", " ");	//10/6/09
			document.email_form.frm_direcs.value = output_direcs;	//10/6/09
			document.email_form.frm_u_id.value = f_unit;	//10/6/09
			document.email_form.frm_scope.value = tick_name;	//10/29/09
//			alert(output_direcs);	//10/6/09
			have_direcs = 1;	//10/6/09
			$("mail_button").style.display = "inline-block";	//10/6/09
			$("loading").style.display = "none";		// 10/28/09	
			$("loading_2").style.display = "none";
			$("directions_ok_no").style.display = "inline-block";			
			}                // end function cb2()

		function mail_direcs(f) {	//10/6/09
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
			var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09
			marker = null;
			gmarkers[id] = null;										// marker to array for side_bar click function
	
			side_bar_html += "<TR ID = '_tr" + id  + "' CLASS='" + colors[(id+1)%2] +"' VALIGN='bottom' onClick = myclick(" + id + "," + unit_id +");><TD>";

			side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;'></TD>";
			var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09

//			var the_class = (direcs[id])?  "emph" : "td_label";
			var the_class = (lats[id])?  "emph" : "td_label";
			side_bar_html += "<TD CLASS='" + the_class + "'>" + letter + " "+ sidebar +"</TD></TR>\n";
			return null;
			}				// end function create Marker()


		function createMarker(point,sidebar,tabs, color, id, unit_id) {		// Creates marker and sets up click event infowindow
			do_sidebar(sidebar, color, id, unit_id)
			var icon = new GIcon(listIcon);
			var uid = unit_id;
			var letter = ""+ id;
										// start with 1 - 1/5/09 - 1/29/09
			var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;				// 1/5/09
	
			icon.image = icon_url;		// ./icons/gen_icon.php?blank=4&text=zz"
			var marker = new GMarker(point, icon);
			marker.id = color;				// for hide/unhide - unused
		
			GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);
				var dMapDiv = document.getElementById("detailmap");
				var detailmap = new GMap2(dMapDiv);
				detailmap.addControl(new GSmallMapControl());
				detailmap.setCenter(point, 13);  					// larger # = closer
				detailmap.addOverlay(marker);
				});
		
			gmarkers[id] = marker;							// marker to array for side_bar click function
			infoTabs[id] = tabs;							// tabs to array
			bounds.extend(point);							// extend the bounding box		
	
//			side_bar_html += "<TR CLASS='" + colors[(id+1)%2] +"' VALIGN='bottom' onClick = myclick(" + id + ");><TD>";
//			side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;'></TD>";
//			var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09
//
//			side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";
			return marker;
			}				// end function create Marker()
	
		function myclick(id, unit_id) {								// responds to side bar click
//			alert (821);
			var norecfac = "";
			if (document.getElementById(current_id)) {
				document.getElementById(current_id).style.visibility = "hidden";			// hide last check if defined
				}
			current_id= "R"+id;
			document.getElementById(current_id).style.visibility = "visible";			// show newest
			if (lats[id]) {																// position data?
				$('mail_dir_but').style.visibility = "visible";			// 11/12/09	
				var thelat = <?php print $lat;?>; var thelng = <?php print $lng;?>;		// coords of click point
<?php
				if ($row_ticket['rec_facility'] > 0) {
?>				
					var thereclat = <?php print $rf_lat;?>; var thereclng = <?php print $rf_lng;?>;									//adds in receiving facility
					if (direcs[id]) {
						setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, thereclat + " " + thereclng, "en_US", unit_id);	// get directions
						}
<?php
					} 
				else {
?>				
					if (direcs[id]) {
						setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, norecfac, "en_US", unit_id);					// get directions
						}
<?php
					}
?>
				}
			else {
				$('directions').innerHTML = "";							// no position data, no directions
				$('mail_dir_but').style.visibility = "hidden";			// 11/12/09	 -

				}

			$("directions").innerHTML= "";								// prior directions no longer apply - 11/21/09
			if (gdir) {	gdir.clear();}
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
	
		function doTraffic() {				// 10/16/08
			if (toggleState) {
		        map.removeOverlay(trafficInfo);
		     	} 
			else {
		        map.addOverlay(trafficInfo);
		    	}
	        toggleState = !toggleState;			// swap
		    }				// end function doTraffic()
	
		var starting = false;

		function sv_win(theLat, theLng) {				// 8/17/09
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
		var map;
		var center;
		var zoom;
		
	    var gdir;				// directions
	    var geocoder = null;
	    var addressMarker;
		$("mail_button").style.display = "none";		// 10/28/09
		$("loading").style.display = "none";		// 10/28/09		
		
		var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
		side_bar_html += "<TR class='even'>	<TD CLASS='<?php print $severityclass; ?>' COLSPAN=99 ALIGN='center'><B>Routes to Incident: <I><?php print shorten($row_ticket['scope'], 20); ?></I></B></TD></TR>\n";
		side_bar_html += "<TR class='odd'>	<TD COLSPAN=99 ALIGN='center'>Click line, icon or map for route</TD></TR>\n";
		side_bar_html += "<TR class='even'>	<TD COLSPAN=3></TD><TD ALIGN='center'>Unit</TD><TD ALIGN='center'>SLD</TD><TD ALIGN='center'>Call</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>\n";

		var gmarkers = [];
		var infoTabs = [];
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
	
		map = new GMap2(document.getElementById("map_canvas"));		// create the map
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>
		map.addControl(new GSmallMapControl());						// 9/23/08
		map.addControl(new GMapTypeControl());
<?php if (intval(get_variable('terrain')) == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	

		gdir = new GDirections(map, document.getElementById("directions"));
		
		GEvent.addListener(gdir, "load", onGDirectionsLoad);
		GEvent.addListener(gdir, "error", handleErrors);
	
		var bounds = new GLatLngBounds();						// create empty bounding box
	
		var listIcon = new GIcon();
		listIcon.image = "./markers/yellow.png";	// yellow.png - 16 X 28
		listIcon.shadow = "./markers/sm_shadow.png";
		listIcon.iconSize = new GSize(20, 34);
		listIcon.shadowSize = new GSize(37, 34);
		listIcon.iconAnchor = new GPoint(8, 28);
		listIcon.infoWindowAnchor = new GPoint(9, 2);
		listIcon.infoShadowAnchor = new GPoint(18, 25);
	
		var newIcon = new GIcon();
		newIcon.image = "./markers/white.png";	// yellow.png - 20 X 34
		newIcon.shadow = "./markers/shadow.png";
		newIcon.iconSize = new GSize(20, 34);
		newIcon.shadowSize = new GSize(37, 34);
		newIcon.iconAnchor = new GPoint(8, 28);
		newIcon.infoWindowAnchor = new GPoint(9, 2);
		newIcon.infoShadowAnchor = new GPoint(18, 25);
																	// set Incident position
		var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 675
		bounds.extend(point);										// Incident into BB
	
		GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
			setDirections(last_from, last_to, "en_US") ;
			});
		var accept_click = false;					// 10/15/08
		GEvent.addListener(map, "click", function(marker, point) {		// point.lat()
			var the_start = point.lat().toString() + "," + point.lng().toString();
			var the_end = thelat.toString() + "," + thelng.toString();	

			setDirections(the_start, the_end, "en_US");
			});				// end GEvent.addListener()

		var nr_units = 	0;
		var email= false;
	    var km2mi = 0.6214;				// 
		
<?php

		function get_cd_str($in_row) {				// unit row in, 
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE  `ticket_id` = {$_GET['ticket_id']} 
				 AND (`responder_id`={$in_row['unit_id']}) AND `clear` IS NULL LIMIT 1;";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if(mysql_affected_rows()==1) 		{return " CHECKED DISABLED ";}

			if (intval($in_row['multi'])==1) 	{return "";}				// allowed
																			// on another run?
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id`={$in_row['unit_id']} AND `clear` IS NULL LIMIT 1;";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if(mysql_affected_rows()==1) 		{return " DISABLED ";}
			else							 	{return "";}

			}			// function get_cd_str($in_row)
			

		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
												// build js array of responders to this ticket - possibly none
		$query = "SELECT `ticket_id`, `responder_id` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $_GET['ticket_id'];
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
		
		while ($assigns_row = stripslashes_deep(mysql_fetch_array($result))) {
			print "\t\tunit_assigns[' '+ " . $assigns_row['responder_id']. "]= true;\n";	// note string forced
			}
		print "\n";

//			LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` 	ON (`$GLOBALS[mysql_prefix]responder`.`id` = `a`.`responder_id`)


		$where = (empty($unit_id))? "" : " WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit_id ";		// revised 5/23/08 per AD7PE 
		$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]responder`.`id` AS `unit_id`, `s`.`status_val` AS `unitstatus`, `contact_via` FROM $GLOBALS[mysql_prefix]responder
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]responder`.`un_status_id` = `s`.`id`)
			$where
			ORDER BY `name` ASC, `unit_id` ASC";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		if(mysql_affected_rows()>0) {
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
				unit_ids[i] = <?php print $unit_row['unit_id'];?>;
				distances[i]=9999.9;
 				direcs[i] = <?php print (intval($unit_row['direcs'])==1)? "true": "false";?>;			// do directions - 7/13/09
<?php
				if ($has_coords) {
//					snap (__LINE__, $unit_row['unit_id']);
					$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "px'>";
					$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . shorten($unit_row['name'], 48) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $unit_row['description']), 32) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Status:</TD><TD>" . $unit_row['unitstatus'] . " </TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Contact:</TD><TD>" . $unit_row['contact_name']. " Via: " . $unit_row['contact_via'] . "</TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($unit_row['updated']) . "</TD></TR>";
					$tab_1 .= "</TABLE>";
					}
?>
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
				if ($has_coords ) {
?>		
					lats[i] = <?php print $unit_row['lat'];?>; 		// 774 now compute distance - in km
					lngs[i] = <?php print $unit_row['lng'];?>;
					distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_ticket['lat'];?>), parseFloat(<?php print $row_ticket['lng'];?>));
					var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to miles
<?php					
					}
				else {
?>
					distances[i] = 9999.9;
					var dist_mi = "na";
<?php
					}

				if (($has_coords) && ($has_rem_source) && (!(empty($unit_row['callsign'])))) {				// 11/15/09
					$thespeed = "";
					$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
						WHERE `source`= '$unit_row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";

					$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {		// got a track?
					
						$track_row = stripslashes_deep(mysql_fetch_array($result_tr));			// most recent track report
			
						$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "px'>";
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
						var point = new GLatLng(<?php print $track_row['latitude'];?>, <?php print $track_row['longitude'];?>);	// 783 - mobile position
						bounds.extend(point);															// point into BB
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
										// 8/7/09
					}		// end if (has rem_source ... )
			
				else {				// no rem_source
					if ($has_coords) {					//  2/25/09
?>
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['name'], 12));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
							];
						
						lats[i] = <?php print $unit_row['lat'];?>; // 819 now compute distance - in km
						lngs[i] = <?php print $unit_row['lng'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_ticket['lat'];?>), parseFloat(<?php print $row_ticket['lng'];?>));	// note: km
					    var km2mi = 0.6214;				// 
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to feet
<?php
						}		// end if ($has_coords)

					$thespeed = "";
					}									// END IF/ELSE (rem_source)
		
//				dump(get_cd_str($unit_row));
?>					
				sidebar_line = "<TD ALIGN='center'><INPUT TYPE='checkbox' <?php print get_cd_str($unit_row); ?> NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[<?php print $i; ?>]=this.checked;'></TD>";

				sidebar_line += "<TD TITLE = \"<?php print addslashes($unit_row['name']);?>\">";
				sidebar_line += "<NOBR><?php print shorten($unit_row['name'], 20);?></NOBR></TD>";

				sidebar_line += "<TD>"+ dist_mi+"</TD>"; // 8/25/08, 4/27/09
				sidebar_line += "<TD><NOBR><?php print shorten(addslashes($dispatched_to), 20); ?></NOBR></TD>";
				sidebar_line += "<TD TITLE = \"<?php print $unit_row['unitstatus'];?>\" CLASS='td_data'><?php print shorten($unit_row['unitstatus'], 12);?></TD>";
				sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD>";
				sidebar_line += "<TD CLASS='td_data'><?php print substr(format_sb_date($unit_row['updated']), 4);?></TD>";
<?php
				if (($has_coords)) {		//  2/25/09
?>		
					var point = new GLatLng(<?php print $unit_row['lat'];?>, <?php print $unit_row['lng'];?>);	//  840 for each responder 832
					var unit_id = <?php print $unit_row['unit_id'];?>;
					bounds.extend(point);																// point into BB
					var marker = createMarker(point, sidebar_line, myinfoTabs,<?php print $unit_row['type'];?>, i, unit_id);	// (point,sidebar,tabs, color, id)
					if (!(isNull(marker))) {
						map.addOverlay(marker);
						}
<?php
					}				// end if ($has_coords) 
				else {
					print "\n\t\t\t\tdo_sidebar(sidebar_line, color, i);\n";
					}		// end if/else ($has_coords)
				$i++;
				$k++;
				}				// end major while ($unit_row = ...)  for each responder
				
			}				// end if(mysql_affected_rows()>0)
//					responders complete
?>
 		var point = new GLatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>);	// incident

		var baseIcon = new GIcon();
		var inc_icon = new GIcon(baseIcon, "./markers/sm_black.png", null);		// 10/26/08
//		var thisMarker = new GMarker(point, inc_icon);
		var thisMarker = new GMarker(point);
		map.addOverlay(thisMarker);

		if (nr_units==0) {
			side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><B>No Units!</B></TD></TR>";;		
			map.setCenter(new GLatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);		// -1 for further out	
			map.setCenter(center,zoom);
			side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>\n";
			side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>\n";
			side_bar_html+= "<TR><TD>&nbsp;</TD></TR>\n";
			}
				
		side_bar_html +="</TABLE>\n";
		document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div

		var thelat = <?php print $lat;?>; var thelng = <?php print $lng;?>;
		var start = min(distances);		// min straight-line distance to Incident
		var norecfac = "";	//10/6/09

		if (start>0) {

			var current_id= "R"+start;			//
			document.getElementById(current_id).style.visibility = "visible";		// show link check image at the selected sidebar el ement
			$("mail_button").style.display = "none";	//10/6/09
			if (lats[start]) {
<?php
				if ($rec_fac > 0) {					
?>					
					var thereclat = <?php print $rf_lat;?>; var thereclng = <?php print $rf_lng;?>;	//adds in receiving facility
					if (direcs[start]) {
						setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, thereclat + " " + thereclng, "en_US", unit_id);	// get directions	10/6/09
						}
<?php
					} 
				else {
?>				
					if (direcs[start]) {
						setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, norecfac, "en_US", unit_id);	// get directions	10/6/09
						}
					location.href = "#top";				// 11/12/09

<?php
					}
?>
				location.href = "#top";				// 11/12/09
				}
			}

					
		}		// end if (GBrowserIsCompatible())

	else {
		alert("Sorry,  browser compatibility problem. Contact your tech support group.");
		}

//	alert(993);

//	for (i=1;i<9999; i++) {
//		var the_id = '_tr' + i;
//		if (!($(the_id))) {break;}
//		$(the_id).style.display = 'none';
//		}
//	alert("1288 " + (i-1));
		
	</SCRIPT>
	
<?php
	}				// end function do_list() ===========================================================
	
?>