<?php
/*
5/23/08 per AD7PE
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
3/10/09 show id's as letters, directions on/off
3/11/09 revise email edit window per 'quick' operation
3/11/09 scroll wheel operation added
3/16/09 added assignments to list display, get current re APRS, INSTAM
3/26/09 fix for null/empty
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
$my_session = do_login(basename(__FILE__));							// returns session array
//snap(basename(__FILE__), __LINE__);
$remotes = get_current();											// refresh remote positions, returns array - 3/16/09

$temp = my_is_int($my_session['f3'])? $my_session['f3'] : 0;			//  3/26/09

$do_dirs_ary = array ("false", "true");
$do_direcs = $do_dirs_ary[$temp];

if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
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
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";
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
	<META HTTP-EQUIV="Script-date" CONTENT="8/25/08">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
    <style type="text/css">
      body 					{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
      table.directions th 	{background-color:#EEEEEE;}
      img 					{color: #000000;}
    </style>
<SCRIPT>

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	var do_direcs = <?php print $do_direcs;?>		// 3/10/09

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

	function to_str(instr) {			// 0-based conversion - 2/13/09
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);													// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
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
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
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
		}										// onto floor!

	function do_switch_direcs (in_val) {
		document.rad_butts.frm_do_direcs[0].checked = in_val;		// On
		document.rad_butts.frm_do_direcs[1].checked = !in_val;		// Off
//		$("directions").style.visibility = (in_val)? "hidden" : "visible";
		$("directions").style.visibility = (in_val)? "visible" : "hidden";
		do_direcs = in_val;
		persist(in_val);	// to session
		return;
		}

	function persist(in_val_2){
		var the_val= (in_val_2)? 1 : 0 ;
		var left = 	new Array("f_n=", 	"&v_n=", "&sess_id=");
		var right = new Array("f3", 	the_val, "<?php print get_sess_key(); ?>");
		params="";
		for (var i=0; i<left.length;i++) {
			params += left[i] + right[i];
			}

		var url = "persist.php";
		sendRequest (url, handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function persist()


</SCRIPT>
<?php

if (!empty($_POST)) {				// 77-200
	extract($_POST);
	$addrs = array();													// 10/7/08
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$assigns = explode ("|", $_POST['frm_id_str']);		// pipe sep'd id's in frm_id_str
	for ($i=0;$i<count($assigns); $i++) {
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`)
						VALUES (%s,%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($frm_ticket_id),
							quote_smart($assigns[$i]),
							quote_smart($frm_comments),
							quote_smart($frm_by_id),
							quote_smart($now));
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
		}
//	print __LINE__;
//	dump($addrs);				// array of addresses
?>
<SCRIPT>
	var starting = false;						// 2/15/09

	function do_mail_win(addrs, ticket_id) {
		if(starting) {return;}					// dbl-click catcher
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

</SCRIPT>
</HEAD>
<?php
	$addr_str = urlencode( implode("|", array_unique($addrs)));
	if (empty($addr_str)) {
		$next = (get_variable('quick'))? " onLoad = 'document.cont_form.submit();'" : "";			//3/11/09

		print "\n<BODY $next>\n";
		}
	else {
		$next = (get_variable('quick'))? "; document.cont_form.submit();" : "";
		print "\n<BODY onLoad = \"do_mail_win('" . $addr_str . "', '" . $_POST['frm_ticket_id'] . "')$next \">\n";
		}
?>
	<CENTER><BR><BR><BR><BR><H3>Call Assignments made to:<BR /><?php print substr((str_replace ( "\n", ", ", $_POST['frm_name_str'])) , 0, -2);?><BR><BR> <!-- 11/8/08 -->
<?php print (get_variable("call_board") == 1)? "See Call Board": "";?>
	</H3>
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

//	function extr_num (instr) {						// extracts the ho number
//		outstr="";
//		var OKchars = '0123456789,.';
//		for (i=0;i< instr.length; i++) {
//			if (OKchars.indexOf(instr.charAt(i)) ==-1) {
//				break;
//				}
//			else {
//				outstr+=instr.charAt(i);
//				}
//			}				// end for ()
//		return outstr;
//		}				// end function extr_num ()

	function min(inArray) {				// returns index of least float value in inArray
		var minsofar = dummy_max;
		var j=false;
//		for (var i=0; i< inArray.length; i++){
		for (var i=1; i< inArray.length; i++){
//			alert("352 " + direcs[i]);			//
			if ((direcs[i]) && (!(parseFloat(inArray[i]==dummy_max))) && (parseFloat(inArray[i]) < parseFloat(minsofar))) {		//
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
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()

<?php
	$addrs = FALSE;													// notifies address array doesn't exist
	if (array_key_exists ( "email", $_GET)) {						// 10/23/08
		$addrs = notify_user(0,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
		}				// end if (array_key_exists())
//			ticket
	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated  FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`=" . $_GET['ticket_id'] . " LIMIT 1";			// 10/16/08 Incident location
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	unset ($result);

//	switch($row_ticket['severity'])		{							//color by ticket severity
//	 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
//		case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
//		default: 							$severityclass=''; break;
//		}
//	dump($severityclass);

	print "var thelat = " . $row_ticket['lat'] . ";\nvar thelng = " . $row_ticket['lng'] . ";\n";		// set js-accessible location data
?>
</SCRIPT>
<BODY onLoad = "do_notify(); ck_frames(); do_switch_direcs (do_direcs)" onunload="GUnload()"> <!-- 3/10/09 -->

	<TABLE ID='outer' BORDER = 0 ID= 'main' STYLE='display:block' >
	<TR><TD VALIGN='top'><DIV ID='side_bar' STYLE='width: 400px'></DIV>
		<BR>
			<DIV ID='the_ticket' style='width: 500px;'><?php print do_ticket($row_ticket, 500, FALSE, FALSE); ?></DIV>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR /><SPAN ID = 'grid_sp'  onClick='doGrid()'><U>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID = 'traffic_sp' onClick='doTraffic()'><U>Traffic</U></SPAN>
			<BR />
			<BR />
<?php
		print get_icon_legend ();
?>
			<BR /><BR />
			<DIV ID='do_radio'>
				<FORM NAME='rad_butts'>
				Directions: On &raquo; <INPUT TYPE='radio' NAME='frm_do_direcs' onClick = 'do_switch_direcs(true);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Off &raquo <INPUT TYPE='radio' NAME='frm_do_direcs' CHECKED onClick = 'do_switch_direcs(false);'>
				</FORM>

			</DIV>
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"></DIV>
		</TD></TR></TABLE><!-- end outer -->
	<DIV ID='bottom' STYLE='display:none'>
	<CENTER>
	<H3>Dispatching ... please wait ...</H3><BR /><BR /><BR />
<!-- 	<IMG SRC="./markers/spinner.gif" BORDER=0> -->
	</DIV>


	<FORM NAME='can_Form' ACTION="main.php">
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
	</FORM>
	<FORM NAME='routes_Form' METHOD='post' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db'>
	<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>'>
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $my_session['user_id'];?>">
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1">
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New">
	</FORM>
	<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>'>	<!-- 10/25/08 -->
	</FORM>

	</BODY>

<?php
//	dump($addrs);
			if ($addrs) {				// 10/21/08
?>
<SCRIPT>
	function do_notify() {
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "ATTENTION - New Ticket: ";
		var theId = '<?php print $_GET['ticket_id'];?>';
//			 mail_it ($to_str, $text, $ticket_id, $text_sel=1;, $txt_only = FALSE)

//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId);		// ($to_str, $text, $ticket_id)   10/15/08
		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()

	function handleResult(req) {				// the 'called-back' function  - ignore returned data
		}

	function sendRequest(url,callback,postData) {
//		alert(455);
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
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
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
	global $row_ticket, $my_session, $eol;

	switch($row_ticket['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
		default: 							$severityclass=''; break;
		}

?>
<SCRIPT>
	var color=0;
	var last_from;
	var last_to;
	var current_id;			// 10/25/08

	if (GBrowserIsCompatible()) {
		var colors = new Array ('odd', 'even');
	    function setDirections(fromAddress, toAddress, locale) {
//	    	alert(544);
	    	if(!do_direcs) {return;}

	    	last_from = fromAddress;
	    	last_to = toAddress;

		   	G_START_ICON.image = "./icons/sm_white.png";
		   	G_START_ICON.iconSize = new GSize(12,20);
		   	G_END_ICON.image = "./icons/sm_white.png";
		   	G_END_ICON.iconSize = new GSize(12,20);

	    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale, preserveViewport : true  });
//			GEvent.addListener(Direcs, "addoverlay", GEvent.callback(Direcs, cb()));
	    	}		// end function set Directions()

	    function cb() {
	    							// onto floor
	    	}

		function createMarker(point,sidebar,tabs, color, id) {		// Creates marker and sets up click event infowindow
//			var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09
			var letter = to_str(id);										// 2/13/09

			var icon = new GIcon(listIcon);
			if(!(isNull(point))) {										// 2/25/09
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
				}				// if(!(isNull(point)))
			else {
				marker = null;
				}

			side_bar_html += "<TR CLASS='" + colors[(id%2)] +"' VALIGN='bottom' onClick = myclick(" + id + ");><TD>";
			side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;'></TD>";
			var letter = to_str(id); 		// 3/10/09
			var the_class = (direcs[(id+1)])?  "td_label": "nodir";
			side_bar_html += "<TD CLASS='" +the_class + "'>" + letter + "</TD>" + sidebar +"</TD></TR>\n";
//			alert("602 " + side_bar_html);
			return marker;
			}				// end function create Marker()

		function myclick(id) {								// Responds to sidebar click
//			alert("608 " + id);
			which = id;
			document.getElementById(current_id).style.visibility = "hidden";		// hide last check
			current_id= "R"+id;
			document.getElementById(current_id).style.visibility = "visible";		// show newest
			if (!(direcs[id])) {return;}					// 3/19/09
			if (!(lats[id])) {
				if (do_direcs) {													// 3/10/09
					alert("600 Cannot route -  no position data currently available\n\nClick map point for directions.");
					}
				}
			else {
				var thelat = <?php print $row_ticket['lat'];?>; var thelng = <?php print $row_ticket['lng'];?>;		// coords of click point
				if (do_direcs) {
					setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, "en_US");						// get directions
					}
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

		function doTraffic() {				// 10/16/08
			if (toggleState) {
		        map.removeOverlay(trafficInfo);
		     	}
			else {
		        map.addOverlay(trafficInfo);
		    	}
	        toggleState = !toggleState;			// swap
		    }				// end function do Traffic()


		function handleErrors(){		//G_GEO_UNKNOWN_DIRECTIONS
			if (gdir.getStatus().code == G_GEO_UNKNOWN_DIRECTIONS ) {
				alert("501: directions unavailable\n\nClick map point for directions.");
				}
			else if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
				alert("643: No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
				alert("645: A map request could not be processed, reason unknown.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
				alert("647: Technical error.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_KEY)
				alert("649: The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
				alert("651: A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
			else alert("652: An unknown error occurred.");
			}		// end function handleErrors()

		function onGDirectionsLoad(){
//			var temp = gdir.getSummaryHtml();
			}		// function onGDirectionsLoad()

		function guest () {
			alert ("Demonstration only.  Guests may not commit dispatch!");
			}

		function validate(){		// frm_id_str
			msgstr="";
			for (var i =0;i<unit_sets.length;i++) {
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
				var quick = <?php print get_variable("quick")? "true;\n" : "false;\n";?>
				if ((quick) || (confirm ("Please confirm unit dispatch\n\n" + msgstr))) {
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
				}		// end else ...

			}		// end function validate()

		function ifexists(myarray,myid) {
			var str_key = " " + myid;		// force associative
			return ((typeof myarray[str_key])!="undefined");		// exists if not undefined
			}		// end function ifexists()

		var icons=[];						// note globals
<?php
//		unit_types
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

		var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
		side_bar_html += "<TR class='even'>	<TD CLASS='<?php print $severityclass; ?>' colspan=99 ALIGN='center'><B>Units to Incident: <I><?php print shorten($row_ticket['scope'], 20); ?></I></B></TD></TR>";
		side_bar_html += "<TR class='odd'>	<TD COLSPAN=99 ALIGN='center'>Click line, icon or map for route</TD></TR>";
		side_bar_html += "<TR class='even'>	<TD COLSPAN=2></TD><TD ALIGN='center'>Unit</TD><TD ALIGN='center'>SLD</TD><TD>Dispatched</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD><TD>Assign</TD></TR>";
		var gmarkers = [];
		var infoTabs = [];
		var lats = [];
		var lngs = [];
		var distances = [];
		var which;			// marker last selected
		var i = 0;			// sidebar/icon index

		map = new GMap2(document.getElementById("map_canvas"));		// create the map
		map.addControl(new GSmallMapControl());						// 9/23/08
		map.addControl(new GMapTypeControl());

<?php print (get_variable('terrain') == 1)? "\tmap.addMapType(G_PHYSICAL_MAP);\n": "\n"; ?>
		map.enableScrollWheelZoom(); 	// 3/11/09

		gdir = new GDirections(map, document.getElementById("directions"));

		GEvent.addListener(gdir, "load", onGDirectionsLoad);
		GEvent.addListener(gdir, "error", handleErrors);
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>

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
		var point = new GLatLng(<?php print $row_ticket['lat'];?>, <?php print $row_ticket['lng'];?>);	// 675
		bounds.extend(point);										// Incident into BB

		if (do_direcs) {											// 3/10/09
			GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
				setDirections(last_from, last_to, "en_US") ;
				});
			var accept_click = false;					// 10/15/08
			GEvent.addListener(map, "click", function(marker, point) {		// point.lat()
				var the_start = point.lat().toString() + "," + point.lng().toString();
				var the_end = thelat.toString() + "," + thelng.toString();
				setDirections(the_start, the_end, "en_US");
				});				// end GEvent.addListener()
			}

		unit_names = 	new Array();				// names
		unit_sets = 	new Array();				// settings
		unit_ids = 		new Array();				// id's
		unit_assigns = 	new Array();				// unit id's assigned this incident
		direcs =	 	new Array();				// do/no-not do directions this unit	3/18/09

		var nr_units = 	0;
		var email= false;
	    var km2mi = 0.6214;				//
	    var dummy_max = 9999.9;

<?php
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
//			responders	to current ticket	--  builds possibly empty js array
		$query = "SELECT `ticket_id`, `responder_id` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $_GET['ticket_id'];
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		while ($assigns_row = stripslashes_deep(mysql_fetch_array($result))) {
			print "\t\tunit_assigns[' '+ " . $assigns_row['responder_id']. "]= true;\n";	// note string forced
			}
		print "\n";
?>
//	for (x in unit_assigns){
//		alert ("806 " + unit_assigns[x] + " " + x);
//		}

<?php
		$where = (empty($unit_id))? "" : " WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit_id ";		// revised 5/23/08 per AD7PE
		$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]responder`.`id` AS `unit_id`, `s`.`status_val` AS `unitstatus`, `contact_via` FROM $GLOBALS[mysql_prefix]responder
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]responder`.`un_status_id` = `s`.`id`)
			$where
			ORDER BY `name` ASC, `unit_id` ASC";
//		dump($query);

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if(mysql_affected_rows()>0) {
													// major while ... for RESPONDER data starts here
			$i = $k = 1;			// sidebar/icon index
			while ($unit_row = stripslashes_deep(mysql_fetch_assoc($result))) {
//				dump($unit_row);
				if(is_email($unit_row['contact_via'])) {
					print "\t\t\temail= true\n";
					}
				$has_coords = (is_numeric($unit_row['lat']));				// 2/25/09
//				dump($has_coords);
?>
				nr_units++;
				var i = <?php print $i;?>;						// top of loop

				unit_names[i] = '<?php print htmlentities ($unit_row['name'], ENT_QUOTES);?>';	// unit name 8/25/08
				unit_sets[i] = false;								// pre-set checkbox settings
				unit_ids[i] = <?php print $unit_row['unit_id'];?>;
				direcs[i] = <?php print ($unit_row['direcs']==1)? "true" : "false" ;?>;
				distances[i]=dummy_max;
<?php
				if ($has_coords) {
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
				var dist_mi = "na&nbsp;&nbsp;";
				var no_dir = true;
<?php

// 		assignments 3/16/09
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
					WHERE `responder_id` = '{$unit_row['unit_id']}' AND `clear` IS NULL ";
//				dump($query);

				$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//				dump (mysql_affected_rows());
				$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
				unset($result_as);

				switch($row_assign['severity'])		{		//color tickets by severity
				 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
					default: 							$severityclass=''; break;
					}

				$tick_ct = (mysql_affected_rows()>1)? "(" .mysql_affected_rows() . ") ": "";
				$ass_td =  (mysql_affected_rows()>0)? "<TD CLASS='$severityclass'  STYLE = 'white-space:nowrap' TITLE = '" .$row_assign['scope'] . "' >" .$tick_ct . shorten($row_assign['scope'], 16) . "</TD>": "<TD>na</TD>";

				$tickets_td = ($row_assign)? $ass_td : "<TD CLASS='td_data'>na</TD>";
// --------------- end new

				if (intval($unit_row['aprs'])==1) {
					$thespeed = "";
//						tracks
					$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
						WHERE `source`= '$unit_row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";
//					dump ($query);
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

						lats[i] = <?php print $track_row['latitude'];?>; // 774 now compute distance - in km
						lngs[i] = <?php print $track_row['longitude'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_ticket['lat'];?>), parseFloat(<?php print $row_ticket['lng'];?>));
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to miles
<?php
						$thespeed = ($track_row['speed'] == 0)? "<FONT COLOR='red'><B>&bull;</B></FONT>"  : "<FONT COLOR='green'><B>&bull;</B></FONT>" ;
						if ($track_row['speed'] >= 50) { $thespeed = "<FONT COLOR='WHITE'><B>&bull;</B></FONT>";}
?>
						var point = new GLatLng(<?php print $track_row['latitude'];?>, <?php print $track_row['longitude'];?>);	// 783 - mobile position
						bounds.extend(point);															// point into BB
<?php
						}			// end if (mysql_affected_rows()>0;) for track data
					else {				// no track data
						$k--;			// not a clickable unit for dispatch

						print "\t\tvar myinfoTabs ='';\n";

						}				// end  no track data
?>
					sidebar_line =  "<TD CLASS='td_data' TITLE = '<?php print htmlentities ($unit_row['name'], ENT_QUOTES);?>'>&nbsp;<?php print shorten($unit_row['name'], 16);?></TD>";
					sidebar_line += "<TD CLASS='td_data' ALIGN='right'>" + dist_mi + "&nbsp;</TD>"; // 8/25/08
					sidebar_line += "<?php print $tickets_td; ?>";
					sidebar_line += "<TD CLASS='td_data' TITLE = '<?php print htmlentities ($unit_row['unitstatus'], ENT_QUOTES);?>'><?php print shorten($unit_row['unitstatus'], 6);?></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print format_sb_date($unit_row['updated']);?></TD>";
					if (no_dir) {				// 10/13/08
						var is_checked = "";
						var is_disabled = "";
						}
					else {

						var is_checked = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " CHECKED ": "";
						var my_is_checked = (is_checked)? "true": "false";
//						alert ("907 " + my_is_checked);
						var is_disabled = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " DISABLED ": "";
						}
					sidebar_line += "<TD CLASS='td_data' ALIGN='center'><INPUT TYPE='checkbox' " + is_checked + is_disabled + " NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[i]=this.checked;'></TD>";

					var marker = createMarker(point, sidebar_line, myinfoTabs,<?php print $unit_row['type'];?>, (i-1));	// (point,sidebar,tabs, color, id)
					if (!(isNull(marker))) {
						map.addOverlay(marker);
						}
<?php
					}		// if aprs

					else {				// position with location info.
						if ($has_coords) {					//  2/25/09
//-------------
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
//----------------------
<?php
						}
					$speed = ($unit_row['instam']==1) ? "I": "";
?>
					sidebar_line =  "<TD CLASS='td_data'  TITLE = '<?php print htmlentities ($unit_row['name'], ENT_QUOTES);?>'>&nbsp;<?php print shorten($unit_row['name'], 16);?></TD>";
					sidebar_line += "<TD CLASS='td_data' ALIGN='right'>"+ dist_mi+"&nbsp;</TD>";	// 8/25/08
					sidebar_line += "<?php print $tickets_td; ?>";

					sidebar_line += "<TD CLASS='td_data' TITLE = '<?php print htmlentities ($unit_row['unitstatus'], ENT_QUOTES);?>'><?php print shorten($unit_row['unitstatus'],6);?></TD>";
					sidebar_line += "<TD CLASS='td_data' ALIGN='center'><?php print $speed; ?></TD>";
					sidebar_line += "<TD CLASS='td_data' ><NOBR><?php print format_sb_date($unit_row['updated']);?></NOBR></TD>";
					var is_checked = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " CHECKED ": "";
					var my_is_checked = (is_checked)? "true": "false";
					var is_disabled = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " DISABLED ": "";
					sidebar_line += "<TD CLASS='td_data' ALIGN='center'><INPUT TYPE='checkbox' " + is_checked  + is_disabled + " NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[<?php print $i; ?>]=this.checked;'></TD>";
<?php
					if ($has_coords) {		//  2/25/09
?>
						var point = new GLatLng(<?php print $unit_row['lat'];?>, <?php print $unit_row['lng'];?>);	//  840 for each responder 832
						bounds.extend(point);																// point into BB

						var marker = createMarker(point, sidebar_line, myinfoTabs, <?php print $unit_row['type'];?>, (i-1));	// (point,sidebar,tabs, color, id)
						if (!(isNull(marker))) {
							map.addOverlay(marker);
							}
<?php
						}				// end if ($has_coords)
					else {
?>
						var marker = createMarker(null, sidebar_line, null, <?php print $unit_row['type'];?>, (i-1));	// (point,sidebar,tabs, color, id)
<?php
						}		// end else

						}				// end if/else (mysql_affected_rows()>0;) - no track data
				$i++;
				$k++;
				}				// end major while ($unit_row = ...)  for each responder
			}				// end if(mysql_affected_rows()>0)

//					responders complete
?>
 		var point = new GLatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>);	// 855

		var baseIcon = new GIcon();
		var inc_icon = new GIcon(baseIcon, "./markers/sm_black.png", null);		// 10/26/08
		var thisMarker = new GMarker(point, inc_icon);
//		map.addOverlay(thisMarker);

		if (nr_units==0) {
			side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><B>No Units!</B></TD></TR>";;
			map.setCenter(new GLatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);		// -1 for further out
			map.setCenter(center,zoom);
			side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>";
			side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
			side_bar_html+= "<TR><TD>&nbsp;</TD></TR>";
			}

<?php
			$thefunc = (is_guest())? "guest()" : "validate()";		// reject guest attempts
?>
			side_bar_html+= "<TR><TD COLSPAN=99 ALIGN='center'><INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'>";
			if (nr_units>0) {
				side_bar_html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' value='DISPATCH SELECTED UNITS' onClick = '<?php print $thefunc;?>' />";
				}
			side_bar_html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='RESET' VALUE='Reset' onClick = 'doReset()'>";
			side_bar_html+= "</TD></TR>";

		side_bar_html +="</TABLE>\n";
		document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div

		var thelat = <?php print $row_ticket['lat'];?>; var thelng = <?php print $row_ticket['lng'];?>;
		var start = min(distances);					// min straight-line distance to Incident
		if (start>0) {
			--start;
			var current_id= "R"+start;			//
			document.getElementById(current_id).style.visibility = "visible";		// show link check image at the selected sidebar el ement
			if ((do_direcs) && (lats[start])) {
				setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, "en_US");
				}
			}
		}		// end if (GBrowserIsCompatible())

	else {
		alert("Sorry,  browser compatibility problem. Contact your tech support group.");
		}

//	for (x in unit_assigns){
//		alert (unit_assigns[x] + " " + x);
//		}
	</SCRIPT>

<?php
	}				// end function do_list() ===========================================================


/*
46	unit_types to build $u_types array
57	responder to build legend
207 INSERT INTO `$GLOBALS[mysql_prefix]assigns
218	DELETE FROM `$GLOBALS[mysql_prefix]assigns`
221	UPDATE `$GLOBALS[mysql_prefix]responder
!empty($_POST))

375 ticket
520 !empty($_POST))

522 function do_list()
709	unit_types for icons
797	assigns for unit_assigns array
811	responder
820 while responder loop top
857 tracks
*/
?>