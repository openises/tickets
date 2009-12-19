<?php
/*
11/3/07 added frame jump prevention
5/29/08 - added do_kml() call
8/23/08	added usng functions
8/23/08	corrections to problem-end handling
9/9/08	additions to provide user lat/lng display format selection
9/13/08	revised to use LL2USNG function
9/14/08 added position display in three lat/lng formats
9/20/08 corrected still-active frm_ngs attempted write
10/8/08 synopsis made mandatory on closed tickets
10/15/08 changed 'Comments' to 'Disposition'
10/21/08 relocated Disposition for consistency with Add
10/21/08 'Synopsis' made non-mandatory
10/21/08 handle revised notifies
11/7/08 add strikethrough
1/17/09 added refresh callboard frame
1/19/09 added phone, geocode lookups
1/21/09 added show butts - re button menu
2/11/09 added streetview
2/11/09 added dollar function
2/21/09 color code by severity
5/2/09	USNG edit added, parsefloat
7/7/09	protocol handling added
7/16/09	protocol corrections
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
8/3/09 Added code to get locale variable and change USNG/OSGB/UTM dependant on variable in tabs and sidebar.
8/7/09 Revised Actions and Patients display to clean up display and also remove ID ambiguity.
8/8/09	resolved 'description' ambiguity, relocated 'disposition'
9/22/09 Added Incident to Facility capability
9/29/09	'frequest fliers' added
10/1/09 added special ticket type - for pre-booked tickets
10/2/09	added locale check for WP lookup
10/5/09 Added Mouseover help text to all field labels.
10/6/09 Added Mouseover help text to all field labels.
10/6/09 Added Receiving Facility, added links button
10/12/09 Incident at facility menu is hidden by default - click radio button to show.
10/13/09 Added reverse geocoding - map click now returns address and location to form.
11/01/09 Added use of reverse_geo setting to switch off reverse geocoding if not required - default is off.
11/06/09 Changed "Special" Incidents to "Scheduled" Incidents
11/06/09 Moved both Facility dropdown menus to the same area.
*/
	error_reporting(E_ALL);
	require_once('./incs/functions.inc.php'); 
	do_login(basename(__FILE__));

	if($istest) {dump($_GET);}
	if($istest) {dump($_POST);}
	$addrs = FALSE;										// notifies address array doesn't exist
	function edit_ticket($id) {							/* post changes */

//dump($_POST['frm_year_booked_date']);
		global $addrs, $NOTIFY_TICKET;

		$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
		$post_frm_meridiem_booked_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_booked_date'])))) ) ? "" : $_POST['frm_meridiem_booked_date'] ;	//10/1/09
		$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;

		$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
		$post_frm_affected 	 		= strip_html($post_frm_affected);
		$_POST['frm_scope']			= strip_html($_POST['frm_scope']);

		/*if (get_variable('reporting')) {		// if any change do automatic action reporting
		
//			if ($_POST[frm_affected] != $_POST[frm_affected_default]) report_action($GLOBALS[ACTION_AFFECTED],$_POST[frm_affected],0,$id);
			if ($_POST[frm_severity] != $_POST[frm_severity_default]) report_action($GLOBALS[ACTION_SEVERITY],get_severity($_POST[frm_severity_default]),get_severity($_POST[frm_severity]),$id);
			if ($_POST[frm_scope] != $_POST[frm_scope_default]) report_action($GLOBALS[ACTION_SCOPE],$_POST[frm_scope_default],0,$id);
			} */

		if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
			if ($post_frm_meridiem_problemstart == 'pm'){
				$post_frm_meridiem_problemstart	= ($post_frm_meridiem_problemstart + 12) % 24;
				}
			if (isset($_POST['frm_meridiem_booked_date'])) {	//10/1/09
				if ($_POST['frm_meridiem_booked_date'] == 'pm'){
					$_POST['frm_hour_booked_date'] = ($_POST['frm_hour_booked_date'] + 12) % 24;
					}
				}

//			if ($_POST['frm_meridiem_problemend'] == 'pm') 	$_POST['frm_hour_problemend'] 	= ($_POST['frm_hour_problemend'] + 12) % 24;
			}

		if(empty($post_frm_owner)) {$post_frm_owner=0;}
//		$frm_problemstart = $_POST['frm_year_problemstart']-$_POST['frm_month_problemstart']-$_POST['frm_day_problemstart'] $_POST['frm_hour_problemstart']:$_POST['frm_minute_problemstart']:00";
		$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";


		if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
			if ($post_frm_meridiem_problemstart == 'pm'){
				$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
				}
			if (isset($_POST['frm_meridiem_problemend'])) {
				if ($_POST['frm_meridiem_problemend'] == 'pm'){
					$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
					}
				}
			if (isset($_POST['frm_meridiem_booked_date'])) {	//10/1/09
				if ($_POST['frm_meridiem_booked_date'] == 'pm'){
					$_POST['frm_hour_booked_date'] = ($_POST['frm_hour_booked_date'] + 12) % 24;
					}
				}
			}
		$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart("$_POST[frm_year_problemend]-$_POST[frm_month_problemend]-$_POST[frm_day_problemend] $_POST[frm_hour_problemend]:$_POST[frm_minute_problemend]:00") : "NULL";
		$frm_booked_date  = (isset($_POST['frm_year_booked_date'])) ?  quote_smart("$_POST[frm_year_booked_date]-$_POST[frm_month_booked_date]-$_POST[frm_day_booked_date] $_POST[frm_hour_booked_date]:$_POST[frm_minute_booked_date]:00") : "NULL";	//10/1/09

//		dump ($frm_problemend);
//		dump ($frm_booked_date);
	
		// perform db update
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		if(empty($post_frm_owner)) {$post_frm_owner=0;}
																					// 8/23/08, 9/20/08, 9/22/09 (Facility), 10/1/09 (receiving facility)
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
		`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
		`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
		`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
		`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
		`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
		`facility`= " . 		quote_smart(trim($_POST['frm_facility_id'])) . ",
		`rec_facility`= " . 		quote_smart(trim($_POST['frm_rec_facility_id'])) . ",
		`lat`= " . 			quote_smart(trim($_POST['frm_lat'])) . ",
		`lng`= " . 			quote_smart(trim($_POST['frm_lng'])) . ",
		`scope`= " . 		quote_smart(trim($_POST['frm_scope'])) . ",
		`owner`= " . 		quote_smart(trim($post_frm_owner)) . ",
		`severity`= " . 	quote_smart(trim($_POST['frm_severity'])) . ",
		`in_types_id`= " . 	quote_smart(trim($_POST['frm_in_types_id'])) . ",
		`status`=" . 		quote_smart(trim($_POST['frm_status'])) . ",
		`problemstart`=".	quote_smart(trim($frm_problemstart)) . ",
		`problemend`=".		$frm_problemend . ",
		`description`= " .	quote_smart(trim($_POST['frm_description'])) .",
		`comments`= " . 	quote_smart(trim($_POST['frm_comments'])) .",
		`booked_date`= ".	$frm_booked_date . ",
		`updated`='$now'
		WHERE ID='$id'";

//dump($query);

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if (($_POST['frm_status']== $GLOBALS['STATUS_OPEN']) || ($_POST['frm_status']== $GLOBALS['STATUS_SCHEDULED'])) {		// log the change
			if ($_POST['frm_facility_id'] == 0) {
				do_log($GLOBALS['LOG_INCIDENT_CHANGE'], $id);
				} else {
				do_log($GLOBALS['LOG_INCIDENT_CHANGE'], $id);
				do_log($GLOBALS['LOG_FACILITY_INCIDENT_CHANGE'], $id);	//10/1/09
				}
			}
		else {
			if ($_POST['frm_facility_id'] == 0) {
				do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $id);
				} else {
				do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $id);
				do_log($GLOBALS['LOG_FACILITY_INCIDENT_CLOSE'], $id);	//10/1/09
				}
			}
		
		if ($_POST['frm_exist_rec_fac'] != $_POST['frm_rec_facility_id']) {
			do_log($GLOBALS['LOG_CALL_REC_FAC_CHANGE'], $id);	//10/7/09	
			}

		print '<FONT CLASS="header">Ticket <I>' . $_POST['frm_scope'] . '</I> has been updated</FONT><BR /><BR />';		/* show updated ticket */
//		notify_user($id, $GLOBALS['NOTIFY_TICKET']);

		add_header($id);
		show_ticket($id);
		$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE

		}				// end function edit ticket() 

	$api_key = get_variable('gmaps_api_key');
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Edit Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
	<SCRIPT SRC="./js/graticule.js" type="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>		<!-- 8/23/08 -->

<SCRIPT>

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function dump (obj) {
		var r = '';
		var dep = 10;
		var ind = '';
		 
		for (var i = 0; i < dep; i++) { ind += '\t'; }
		for (var i in obj) {
			var is_obj = (typeof(obj[i]) == 'object');
			 
			r += ind + '[' + i + '] : ';
			r += !is_obj ? obj[i] : '';
			r += '\n';
			r += is_obj ? arguments.callee(obj[i], dep) : '';
			}
		 
		alert("dump: " + r);
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

	String.prototype.trim = function () {									// 1/19/09
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08

	var starting = false;
	function sv_win(theForm) {				// 2/11/09
		if(starting) {return;}				// dbl-click proof
		starting = true;					

		var thelat = theForm.frm_lat.value;
		var thelng = theForm.frm_lng.value;
		var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
		newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_sl)) {
			alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_sl.focus();
		starting = false;
		}		// end function sv win()

	function do_usng() {														// 5/2/09
//		alert(177);
		if (document.edit.frm_ngs.value.trim().length>6) {do_usng_conv();}
		}

	function do_usng_conv(){			// usng to LL array			- 12/4/08
		tolatlng = new Array();
		USNGtoLL(document.edit.frm_ngs.value, tolatlng);
//		dump(tolatlng);
		var point = new GLatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
		map.setCenter(point, 13);
		var marker = new GMarker(point);
		document.edit.frm_lat.value = point.lat(); document.edit.frm_lng.value = point.lng(); 	
		do_lat (point.lat());
		do_lng (point.lng());
		do_ngs(document.edit);
		pt_to_map (document.edit, point.lat(), point.lng())

		}				// end function
		

	function do_coords(inlat, inlng) { 										 //9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				// lat to degr, dec min's  9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}
	
	function lng2ddm(inlng) {				// lng to degr, dec min's 
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}
	
	function do_lat_fmt(inlat) {				// 9/9/08
		switch(lat_lng_frmt) {
		case 0:
			return inlat;
		  	break;
		case 1:
			return ll2dms(inlat);
		  	break;
		case 2:
			return lat2ddm(inlat);
		 	break;
		default:
			alert ("invalid LL format selector");
			}	
		}

	function do_lng_fmt(inlng) {
		switch(lat_lng_frmt) {
		case 0:
			return inlng;
		  	break;
		case 1:
			return ll2dms(inlng);
		  	break;
		case 2:
			return lng2ddm(inlng);
		 	break;
		default:
			alert ("invalid LL format selector");
			}	
		}

	var map;
	var grid = false;										// toggle
	var thePoint;

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()

	function capWords(str){ 
		var words = str.split(" "); 
		for (var i=0 ; i < words.length ; i++){ 
			var testwd = words[i]; 
			var firLet = testwd.substr(0,1); 
			var rest = testwd.substr(1, testwd.length -1) 
			words[i] = firLet.toUpperCase() + rest 
	  	 	} 
		return( words.join(" ")); 
		} 

	function validate(theForm) {
//		alert (theForm);
		var errmsg="";
		if ((document.edit.frm_status.value == <?php print $GLOBALS['STATUS_CLOSED'];?>) && (document.edit.frm_year_problemend.disabled))
														{errmsg+= "\tClosed ticket requires run end date\n";}
		if ((document.edit.frm_status.value == <?php print $GLOBALS['STATUS_CLOSED'];?>) && (document.edit.frm_comments==""))
														{errmsg+= "\tClosed ticket requires Disposition data\n";}
		if (theForm.frm_contact.value == "")			{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")				{errmsg+= "\tIncident name is required\n";}		// 10/21/08
//		if (theForm.frm_description.value == "")		{errmsg+= "\tSynopsis is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			st_unlk(theForm);
//			theForm.frm_ngs.disabled=false;													// 9/13/08
			theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits
			top.upper.calls_start();											 // 1/17/09
			return true;
			}
		}				// end function validate(theForm)

	function do_fac_to_loc(text, index){													// 9/22/09
			var curr_lat = fac_lat[index];
			var curr_lng = fac_lng[index];
			do_lat(curr_lat);
			do_lng(curr_lng);
			pt_to_map (document.edit, curr_lat, curr_lng)
	}					// end function do_fac_to_loc

	function do_end(theForm) {				// make run-end date/time inputs available for posting
		elem = document.getElementById("runend1");
		elem.style.visibility = "visible";
		theForm.frm_year_problemend.disabled = false;
		theForm.frm_month_problemend.disabled = false;
		theForm.frm_day_problemend.disabled = false;
		theForm.frm_hour_problemend.disabled = false;
		theForm.frm_minute_problemend.disabled = false;
		
<?php
	if (!get_variable('military_time')){
		print "\tdocument.edit.frm_meridiem_problemend.disabled = false;\n";
		}
?>
		}
	var good_end = false;		// boolean defines run end 

	function do_booking(theForm) {				// 	10/1/09 make booking date/time inputs available for posting
		elem = document.getElementById("booked1");
		elem.style.visibility = "visible";
		theForm.frm_year_booked_date.disabled = false;
		theForm.frm_month_booked_date.disabled = false;
		theForm.frm_day_booked_date.disabled = false;
		theForm.frm_hour_booked_date.disabled = false;
		theForm.frm_minute_booked_date.disabled = false;
		
<?php
	if (!get_variable('military_time')){
		print "\tdocument.edit.frm_meridiem_booked_date.disabled = false;\n";
		}
?>
		}

	function reset_end(theForm) {		// on reset()
		if (!good_end) {
			elem = document.getElementById("runend1");
			elem.style.visibility = "hidden";
			theForm.frm_year_problemend.disabled = true;
			theForm.frm_month_problemend.disabled = true;
			theForm.frm_day_problemend.disabled = true;
			theForm.frm_hour_problemend.disabled = true;
			theForm.frm_minute_problemend.disabled = true;		
			}
	}

	function st_unlk(theForm) {										// problem start time enable 8/10/08
		theForm.frm_year_problemstart.disabled = false;
		theForm.frm_month_problemstart.disabled = false;
		theForm.frm_day_problemstart.disabled = false;
		theForm.frm_hour_problemstart.disabled = false;
		theForm.frm_minute_problemstart.disabled = false;
//		document.getElementById("lock").style.visibility = "hidden";	//8/23/08
		}
		
	function st_unlk_res(theForm) {										// 8/10/08
		theForm.frm_year_problemstart.disabled = true;
		theForm.frm_month_problemstart.disabled = true;
		theForm.frm_day_problemstart.disabled = true;
		theForm.frm_hour_problemstart.disabled = true;
		theForm.frm_minute_problemstart.disabled = true;
//		document.getElementById("lock").style.visibility = "visible";	// 8/23/08
		}

	function pb_unlk(theForm) {										// Booking time enable 8/10/08
		theForm.frm_year_booked_date.disabled = false;
		theForm.frm_month_booked_date.disabled = false;
		theForm.frm_day_booked_date.disabled = false;
		theForm.frm_hour_booked_date.disabled = false;
		theForm.frm_minute_booked_date.disabled = false;
		if (theForm.frm_meridiem_booked_date) {theForm.frm_meridiem_booked_date.disabled = false;}
		document.getElementById("pb_lock").style.visibility = "hidden";	//8/23/08
		}

	function do_inc_nature(indx) {										// 7/16/09
		if (protocols[indx]) {
			$('proto_cell').innerHTML = protocols[indx];
			}
		else {
			$('proto_cell').innerHTML = "";		
			}
			
		}			// end function
		
	var protocols = new Array();		// 7/7/09
	var fac_lat = [];
	var fac_lng = [];
</SCRIPT>
</HEAD>

<BODY onLoad = "do_notify(); ck_frames()" onunload="GUnload()">
<?php
require_once('./incs/links.inc.php');
 
	$id = $_GET['id'];

	if ((isset($_GET['action'])) && ($_GET['action'] == 'update')) {		/* update ticket */
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id' LIMIT 1")) {
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT>";
			}
		else {
			edit_ticket($id);									// post updated data
			}
		}

	else if (isset($_GET['delete'])) {							//delete ticket
		if ($_POST['frm_confirm']) {
			/* remove ticket and ticket actions */
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id'") or do_error('edit.php::remove_ticket(ticket)', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$id'") or do_error('edit.php::remove_ticket(action)', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<FONT CLASS=\"header\">Ticket '$id' has been removed.</FONT><BR /><BR />";
			list_tickets();
			}
		else {		//confirm deletion
			print "<FONT CLASS='header'>Confirm ticket deletion</FONT><BR /><BR /><FORM METHOD='post' NAME = 'del_form' ACTION='edit.php?id=$id&delete=1&go=1'><INPUT TYPE='checkbox' NAME='frm_confirm' VALUE='1'>Delete ticket #$id &nbsp;<INPUT TYPE='Submit' VALUE='Confirm'></FORM>";
			}
		}
	else {				// not ($_GET['delete'])
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {		/* sanity check */
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT><BR />";
			} 

		else {				// OK, do form - 7/7/09
//			$result = mysql_query("SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id' LIMIT 1") or do_error('', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
/*
			$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,
				UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM `$GLOBALS[mysql_prefix]ticket` 
				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)				
				WHERE `$GLOBALS[mysql_prefix]ticket`.`id`='$id' LIMIT 1";
*/
 
 			$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend, UNIX_TIMESTAMP(booked_date) AS booked_date, UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, 
 				`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` FROM `$GLOBALS[mysql_prefix]ticket` 
 				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)				
 				WHERE `$GLOBALS[mysql_prefix]ticket`.`id`='$id' LIMIT 1";

//			dump($query);
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	
			$row = stripslashes_deep(mysql_fetch_array($result));
			if (good_date($row['problemend'])) {
?>
				<SCRIPT>
				good_end = true;
				</SCRIPT>
<?php			
				}
			$priorities = array("","severity_medium","severity_high" );			// 2/21/09
			$theClass = $priorities[$row['severity']];
			$exist_rec_fac = $row['rec_facility'];

				
			print "<TABLE BORDER='0' ID = 'outer' ALIGN='left' CLASS = 'BGCOLOR'>\n";
			print "<TR CLASS='odd'><TD ALIGN='left COLSPAN=2>" . add_header($id, TRUE) . "</TD></TR>\n";	// 11/27/09
			print "<TR CLASS='odd'><TD>&nbsp;</TD></TR>\n";	
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left'>";
	
			print "<FORM NAME='edit' METHOD='post' onSubmit='return validate(document.edit)' ACTION='edit.php?id=$id&action=update'>";
			print "<TABLE BORDER='0' ID='data'>\n";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=2><FONT CLASS='$theClass'>Edit Run Ticket</FONT> (#" . $id . ")</TD></TR>";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=2><FONT CLASS='header'><FONT SIZE='-2'>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>";
			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\" Incident Name - Use an easily identifiable name.\" >Incident name</A>:</TD><TD><INPUT TYPE='text' NAME='frm_scope' SIZE='48' VALUE='" . $row['scope'] . "' MAXLENGTH='48'></TD></TR>\n"; 
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Priority - Normal, Medium or High. Affects order and coloring of Incidents on Situation display\">Priority</A>:</TD><TD CLASS='$theClass'><SELECT NAME='frm_severity'>";		// 2/21/09
			$nsel = ($row['severity']==$GLOBALS['SEVERITY_NORMAL'])? "SELECTED" : "" ;
			$msel = ($row['severity']==$GLOBALS['SEVERITY_MEDIUM'])? "SELECTED" : "" ;
			$hsel = ($row['severity']==$GLOBALS['SEVERITY_HIGH'])? "SELECTED" : "" ;
			
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_NORMAL'] . "' $nsel>normal</OPTION>";
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_MEDIUM'] . "' $msel>medium</OPTION>";
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_HIGH'] . "' $hsel>high</OPTION>";
			print "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Nature or Type - Available types are set in in_types table in the configuration\">Nature</A>:</SPAN>\n";

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<SELECT NAME='frm_in_types_id' onChange='do_inc_nature(this.options[selectedIndex].value.trim());'>";
			$the_grp = strval(rand());						// force initial optgroup value
			$i = 0;
			$proto = "";
			while ($row2 = stripslashes_deep(mysql_fetch_array($result))) {
				if ($the_grp != $row2['group']) {
					print ($i == 0)? "": "</OPTGROUP>\n";
					$the_grp = $row2['group'];
					print "<OPTGROUP LABEL='$the_grp'>\n";
					}
				if ($row['in_types_id'] == $row2['id']) {		// 7/16/09
					$sel = " SELECTED";
					$proto = addslashes($row2['protocol']);
					}
				else {
					$sel = "";
					}			
				print "<OPTION VALUE=" . $row2['id'] . $sel . ">" . $row2['type'] . "</OPTION>";
				if (!(empty($row2['protocol']))) {				// 7/7/09 - note string key
					print "\n<SCRIPT>protocols[{$row2['id']}] = \"{$row2['protocol']}\";</SCRIPT>\n";
					}
				$i++;
				}
			unset ($result);
			print "</OPTGROUP></SELECT>";
			print "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Protocol - this will show automatically if a protocol is set for the Incident Type in the configuration\">Protocol</A>:</SPAN></TD></TR>\n";

			print "<TR CLASS='even'><TD CLASS='td_label'></TD><TD ID='proto_cell'>{$row['protocol']}</TD></TR>\n";
			print "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD></TR>\n";

			if (good_date($row['booked_date'])) {	//10/1/09
//				dump(__LINE__);
//				dump($row['booked_date']);
				print "\n<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields.\">Scheduled Date</A>:</TD><TD>";
				generate_date_dropdown("booked_date",$row['booked_date']);
				print "</TD></TR>\n";
				}
			else {	//10/1/09
				print "\n<TR CLASS='even' valign='middle'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields.\">Scheduled Date</A>: &nbsp;&nbsp;<input type='radio' name='boo_but' onClick = 'do_booking(this.form);' /></TD><TD>";
				print "<SPAN style = 'visibility:hidden' ID = 'booked1'>";
				generate_date_dropdown('booked_date',0, TRUE);
				print "</TD></TR>\n";
				print "</SPAN></TD></TR>\n";
				}

				print "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD></TR>\n";			
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Caller reporting the incident\">Reported by</A>:</TD><TD><INPUT SIZE='48' TYPE='text' 	NAME='frm_contact' VALUE='" . $row['contact'] . "' MAXLENGTH='48'></TD></TR>\n";
			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Phone number - for US only, you can use the lookup button to get the callers name and location using the White Pages\">Phone</A>:&nbsp;&nbsp;&nbsp;&nbsp;";
			print 		"<button type=\"button\" onClick=\"Javascript:phone_lkup(document.edit.frm_phone.value);\"><img src=\"./markers/glasses.png\" alt=\"Lookup phone no.\" /></button>";	// 1/19/09
			print 		"</TD><TD><INPUT SIZE='48' TYPE='text' NAME='frm_phone' VALUE='" . $row['phone'] . "' MAXLENGTH='16'></TD></TR>\n";
			$selO = ($row['status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
			$selC = ($row['status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
			$selP = ($row['status']==$GLOBALS['STATUS_SCHEDULED'])? "SELECTED" :"" ;
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Status - Open or Closed or set to Scheduled for future booked calls\">Status</A>:</TD><TD>
				<SELECT NAME='frm_status'><OPTION VALUE='" . $GLOBALS['STATUS_OPEN'] . "' $selO>Open</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_CLOSED'] . "'$selC>Closed</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_SCHEDULED'] . "'$selP>Scheduled</OPTION></SELECT></TD></TR>";
			print "<TR CLASS='even'><TD COLSPAN='2'>&nbsp;</TD></TR>";
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Location - type in location in fields, click location on map or use *Located at Facility* menu below \">Location</A>: </TD><TD><INPUT SIZE='48' TYPE='text'NAME='frm_street' VALUE='" . $row['street'] . "' MAXLENGTH='48'></TD></TR>\n";
			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"City - defaults to default city set in configuration. Type in City if required\">City</A>:&nbsp;&nbsp;&nbsp;&nbsp;";
			print 		"<button type=\"button\" onClick=\"Javascript:loc_lkup(document.edit);\"><img src=\"./markers/glasses.png\" alt=\"Lookup location.\" /></button>";
			print 		"</TD><TD><INPUT SIZE='32' TYPE='text' 	NAME='frm_city' VALUE='" . $row['city'] . "' MAXLENGTH='32' onChange = 'this.value=capWords(this.value)'>\n";
			print 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF=\"#\" TITLE=\"State - US State or non-US Country code e.g. UK for United Kingdom\">St</A>:&nbsp;&nbsp;<INPUT SIZE='2' TYPE='text' NAME='frm_state' VALUE='" . $row['state'] . "' MAXLENGTH='2'></TD></TR>\n";
//			print "<TR CLASS='even'><TD CLASS='td_label'>Affected:</TD><TD><INPUT TYPE='text' SIZE='48' NAME='frm_affected' VALUE='" . $row['affected'] . "' MAXLENGTH='48'></TD></TR>\n";

			if (!($row['facility'] == NULL)) {				// 9/22/09

			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received\">Facility</A>: &nbsp;&nbsp;</TD>";		// 2/21/09
			$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
			$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			print "<TD><SELECT NAME='frm_facility_id' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
			print "<OPTION VALUE=0>Not using facility</OPTION>";

			$deflat = get_variable('def_lat');
			$deflng = get_variable('def_lng');
			print "\n<SCRIPT>fac_lat[" . 0 . "] = " . $deflat . " ;</SCRIPT>\n";
			print "\n<SCRIPT>fac_lng[" . 0 . "] = " . $deflng . " ;</SCRIPT>\n";

				while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
					if ($row['facility'] == $row_fc['id']) {		// 9/22/09
						$sel = " SELECTED";
						} else {
						$sel = "";
						}			
					print "<OPTION VALUE=" . $row_fc['id'] . $sel . ">" . $row_fc['name'] . "</OPTION>";
					print "\n<SCRIPT>fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
					print "\n<SCRIPT>fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
					}
				print "</SELECT></TD></TR>";
				unset ($result_fc);
				} else {

				$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
				$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$pulldown = '<option>Incident at Facility?</option>';
					while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
						$pulldown .= "<option value=\"{$row_fc['id']}\">{$row_fc['name']}</option>\n";
						print "\n<SCRIPT>fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
						print "\n<SCRIPT>fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
					}
			unset ($result_fc);
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received\">Facility?</A>:</TD>";
			print "<TD><SELECT NAME='frm_facility_id' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>$pulldown</SELECT></TD></TR>";
			}

			if (!($row['rec_facility'] == NULL)) {				// 10/1/09
				$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
				$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				print "<TR CLASS='odd'><TD CLASS='td_label'> </TD>";
				print "<TD><SELECT NAME='frm_rec_facility_id'>";
				print "<OPTION VALUE=0>No receiving facility</OPTION>";

					while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
						if ($row['rec_facility'] == $row_rfc['id']) {		// 10/1/09
							$sel2 = " SELECTED";
							} else {
							$sel2 = "";
							}			
						print "<OPTION VALUE=" . $row_rfc['id'] . $sel2 . ">" . $row_rfc['name'] . "</OPTION>";
						}
				print "</SELECT></TD></TR>";
				unset ($result_rfc);
				} else {

				$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";			//10/1/09
				$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$pulldown2 = '<option>Receiving Facility?</option>';
					while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
						$pulldown2 .= "<option value=\"{$row_rfc['id']}\">{$row_rfc['name']}</option>\n";
				}
			unset ($result_rfc);
			print "<TR CLASS='odd'><TD CLASS='td_label'> </TD>";
			print "<TD><SELECT NAME='frm_rec_facility_id'>$pulldown2</SELECT></TD></TR>";	//10/1/09
			}

			print "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Synopsis - Details about the Incident, ensure as much detail as possible is completed\">Synopsis</A>:</TD>";
			print 	"<TD CLASS='td_label'><TEXTAREA NAME='frm_description' COLS='45' ROWS='2' >" . $row['tick_descr'] . "</TEXTAREA></TD></TR>\n";		// 8/8/09

			print "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Disposition - additional comments about incident\">Disposition</A>:</TD>";				// 10/21/08, 8/8/09
			print 	"<TD><TEXTAREA NAME='frm_comments' COLS='45' ROWS='2' >" . $row['comments'] . "</TEXTAREA></TD></TR>\n";

			print "\n<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Run-start, Incident start time. Edit by clicking padlock icon to enable date & time fields\">Run Start</A>:</TD><TD>";
			print  generate_date_dropdown("problemstart",$row['problemstart'],0, TRUE);
			print "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'st_unlk(document.edit);'></TD></TR>\n";
			if (good_date($row['problemend'])) {
//				dump(__LINE__);
//				dump($row['problemend']);
				print "\n<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields\">Run End</A>:</TD><TD>";
				generate_date_dropdown("problemend",$row['problemend']);
				print "</TD></TR>\n";
				}
			else {
				print "\n<TR CLASS='odd' valign='middle'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields\">Run End</A>: &nbsp;&nbsp;<input type='radio' name='re_but' onClick ='do_end(this.form);' /></TD><TD>";
				print "<SPAN style = 'visibility:hidden' ID = 'runend1'>";
				generate_date_dropdown('problemend',0, TRUE);
				print "</TD></TR>\n";
				print "</SPAN></TD></TR>\n";
				}

			print "<TR CLASS='even'><TD CLASS='td_label' onClick = 'javascript: do_coords(document.edit.frm_lat.value ,document.edit.frm_lng.value  )'><U><A HREF=\"#\" TITLE=\"Position - Lat and Lng for Incident position. Click to show all position data.\">Position</A>:</U>:</TD><TD>";
			print "<INPUT SIZE='13' TYPE='text' NAME='show_lat' VALUE='" . get_lat($row['lat']) . "' DISABLED>\n";
			print "<INPUT SIZE='13' TYPE='text' NAME='show_lng' VALUE='" . get_lng($row['lng']) . "' DISABLED>&nbsp;&nbsp;";

			$locale = get_variable('locale');	// 08/03/09
			switch($locale) { 
				case "0":
					print "<B><SPAN ID = 'USNG' onClick = \"do_usng()\"><U><A HREF=\"#\" TITLE=\"US National Grid Co-ordinates.\">USNG</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='" . LLtoUSNG($row['lat'], $row['lng']) . "' ></TD></TR>";		// 9/13/08, 5/2/09
					break;
			
				case "1":
					print "<B><SPAN ID = 'USNG' onClick = \"do_usng()\"><U><A HREF=\"#\" TITLE=\"United Kingdom Ordnance Survey Grid Reference.\">OSGB</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='" . LLtoOSGB($row['lat'], $row['lng']) . "' DISABLED ></TD></TR>";		// 9/13/08, 5/2/09
					break;
			
//				case "2":
//					print "<B><SPAN ID = 'USNG' onClick = \"do_usng()\"><U><A HREF=\"#\" TITLE=\"United Kingdom Ordnance Survey Grid Reference.\">OSGB</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='" . LLtoUTM($row['lat'], $row['lng']) . "' DISABLED ></TD></TR>";		// 9/13/08, 5/2/09
//					break;

				default:																	// 8/10/09
				    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
				}

//			print "<B><SPAN ID = 'USNG' onClick = \"do_usng()\"><U>USNG</U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='" . LLtoUSNG($row['lat'], $row['lng']) . "' ></TD></TR>";		// 9/13/08, 5/2/09
			print "</TD></TR>\n";
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident last updated - date & time.\">Updated</A>:</TD><TD>" . format_date($row['updated']) . "</TD></TR>\n";		// 10/21/08
			$lat = $row['lat']; $lng = $row['lng'];	
			print "<TR CLASS='even'><TD COLSPAN='2' ALIGN='center'><BR /><INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='reset' VALUE='Reset' onclick= 'st_unlk_res(this.form); reset_end(this.form); resetmap($lat, $lng);' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Submit'></TD></TR>";
?>	
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $row['lat'];?>">				<!-- // 8/9/08 -->
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $row['lng'];?>">
			<INPUT TYPE="hidden" NAME="frm_status_default" VALUE="<?php print $row['status'];?>">
			<INPUT TYPE="hidden" NAME="frm_affected_default" VALUE="<?php print $row['affected'];?>">
			<INPUT TYPE="hidden" NAME="frm_scope_default" VALUE="<?php print $row['scope'];?>">
			<INPUT TYPE="hidden" NAME="frm_owner_default" VALUE="<?php print $row['owner'];?>">
			<INPUT TYPE="hidden" NAME="frm_severity_default" VALUE="<?php print $row['severity'];?>">
			<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>">
<?php
			print "<TR CLASS='even'><TD COLSPAN='10' ALIGN='center'><BR /><B><U><A HREF=\"#\" TITLE=\"List of all actions and patients atached to this Incident\">Actions and Patients</A></U></B><BR /></TD></TR>";	//8/7/09
			print "<TR CLASS='odd'><TD COLSPAN='10' ALIGN='center'>";										//8/7/09
			print show_actions($row[0], "date", TRUE, TRUE);											//8/7/09
			print "</TD></TR>";																//8/7/09
			print "</TABLE>";		// end data 8/7/09
			print "</TD><TD>";																//8/7/09
			print "<TABLE ID='mymap' border = 0><TR><TD ALIGN='center'><DIV ID='map' STYLE='WIDTH: " . get_variable('map_width') . "PX; HEIGHT:" . get_variable('map_height') . "PX'></DIV>
				<BR /><SPAN ID='do_grid' onClick='toglGrid()'><U>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID='do_sv' onClick = 'sv_win(document.edit)'><U>Street view</U></SPAN> <!-- 2/11/09 -->
				</TD></TR></TABLE ID='mymap'>\n";
			
			print "</TD></TR>";
			print "<TR><TD CLASS='print_TD' COLSPAN='2'>";
			print "</FORM>";
			print "</TD></TR></TABLE>";		// bottom of outer
?>
	<SCRIPT type="text/javascript">
		function toglGrid() {									// toggle
			grid = !grid;
			if (!grid) {										// check prior value
				map.clearOverlays();
				}
			else {
				map.addOverlay(new LatLonGraticule());
				}
			if (thePoint) {										// show it
				icon.image = icons[<?php print $row['severity'];?>];		
				map.addOverlay(new GMarker(thePoint, icon));
				}
			}		// end function toglGrid()
	
	
		var map;
		var icons=[];						// note globals
		icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./icons/blue.png";		// normal
		icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./icons/green.png";	// green
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./icons/red.png";		// red	
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./icons/white.png";	// white - not in use
	
		map = new GMap2(document.getElementById("map"));		// create the map
<?php
$maptype = get_variable('maptype');	// 08/02/09

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>
		map.addControl(new GSmallMapControl());					// 1/19/09
		map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	
//		map.addControl(new GOverviewMapControl());		
		map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>), 12);
		map.enableScrollWheelZoom(); 	
		
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
//		baseIcon.infoWindowAnchor = new GPoint(9, 2);
//		baseIcon.infoShadowAnchor = new GPoint(18, 25);

		var icon = new GIcon(baseIcon);
		icon.image = icons[<?php print $row['severity'];?>];		
		var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
		map.addOverlay(new GMarker(point, icon));
		thePoint = point;
		var geocoder = "";
		var address = "";
		var rev_coding_on = 0;	// 11/01/09
	

<?php
		$in_strike = 	($row['status']== $GLOBALS['STATUS_CLOSED'])? "<strike>": "";							// 11/7/08
		$in_strikend = 	($row['status']== $GLOBALS['STATUS_CLOSED'])? "</strike>": "";
		$street = empty($row['street'])? "" : "<BR/>" . $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
		do_kml();			// kml functions

?>
	GEvent.addListener(map, "click", function(marker, point) {
		if (marker) {
			map.removeOverlay(marker);
			document.edit.frm_lat.disabled=document.edit.frm_lat.disabled=false;
			document.edit.frm_lat.value=document.edit.frm_lng.value="";
			document.edit.frm_lat.disabled=document.edit.frm_lat.disabled=true;
			thePoint = false;
			}
		if (point) {
			map.clearOverlays();
			do_lat (point.lat())								// display
			do_lng (point.lng())
			do_ngs(document.edit);								// 8/23/08
			map.addOverlay(new GMarker(point, icon));			// GLatLng.
			map.setCenter(point, 12);
			getAddress(marker, point);					// 10/13/09
			thePoint = point;
			}

		if (grid) {map.addOverlay(new LatLonGraticule());}		// both cases
		});				// end GEvent.addListener()

	function do_lat (lat) {
		document.edit.frm_lat.value=parseFloat(lat).toFixed(6);			// 9/9/08, 5/2/09
		document.edit.show_lat.disabled=false;							// permit read/write
		document.edit.show_lat.value=do_lat_fmt(document.edit.frm_lat.value);
		document.edit.show_lat.disabled=true;
		}
	function do_lng (lng) {
		document.edit.frm_lng.value=parseFloat(lng).toFixed(6);			// 5/2/09
		document.edit.show_lng.disabled=false;
		document.edit.show_lng.value=do_lng_fmt(document.edit.frm_lng.value);
		document.edit.show_lng.disabled=true;
		}

	function do_ngs(theForm) {								// 8/23/08
		theForm.frm_ngs.disabled=false;						// 9/9/08
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);
//		theForm.frm_ngs.disabled=true;
		}

	function resetmap(lat, lng) {						// restore original marker and center
		map.clearOverlays();
		var point = new GLatLng(lat, lng);	
		icon.image = icons[<?php print $row['severity'];?>];		
		map.addOverlay(new GMarker(point, icon));
		map.setCenter(new GLatLng(lat, lng), 8);
		do_lat (lat);
		do_lng (lng);
		do_ngs(document.edit);								// 8/23/08
		if (grid) {map.addOverlay(new LatLonGraticule());}	// restore grid
		}

// *********************************************************************
	function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
														// NOT correspond with what browsers actually do...
		var SAFECHARS = "0123456789" +					// Numeric
						"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
						"abcdefghijklmnopqrstuvwxyz" +	// guess
						"-_.!~*'()";					// RFC2396 Mark characters
		var HEX = "0123456789ABCDEF";
	
		var encoded = "";
		for (var i = 0; i < plaintext.length; i++ ) {
			var ch = plaintext.charAt(i);
		    if (ch == " ") {
			    encoded += "+";				// x-www-urlencoded, rather than %20
			} else if (SAFECHARS.indexOf(ch) != -1) {
			    encoded += ch;
			} else {
			    var charCode = ch.charCodeAt(0);
				if (charCode > 255) {
				    alert( "Unicode Character '"
	                        + ch
	                        + "' cannot be encoded using standard URL encoding.\n" +
					          "(URL encoding only supports 8-bit characters.)\n" +
							  "A space (+) will be substituted." );
					encoded += "+";
				} else {
					encoded += "%";
					encoded += HEX.charAt((charCode >> 4) & 0xF);
					encoded += HEX.charAt(charCode & 0xF);
					}
				}
			} 			// end for(...)
		return encoded;
		};			// end function

	var the_form;
	function sendRequest(my_form, url,callback,postData) {		// ajax function set - 1/17/09
		the_form = my_form;
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

	function handleResult(req) {			// the called-back function 9/29/09 added frequent fliers
		if (req.responseText.substring(0,1)=="-") {
			alert("lookup failed");
			}
		else {
			var result=req.responseText.split(";");					// good return - now parse the puppy
// "Juan Wzzzzz;(123) 456-9876;1689 Abcd St;Abcdefghi;MD;16701;99.013297;-88.544775;"
//  0           1              2            3         4  5     6         7
			the_form.frm_contact.value=result[0].trim();
			the_form.frm_phone.value=result[1].trim();		// phone
			the_form.frm_street.value=result[2].trim();		// street
			the_form.frm_city.value=result[3].trim();		// city
			the_form.frm_state.value=result[4].trim();		// state 
//			the_form.frm_zip.value=result[5].trim();		// frm_zip - unused

			pt_to_map (the_form, result[6].trim(), result[7].trim());				// 1/19/09
			
			}		// end else ...			
		}		// end function handleResult()
	
	function phone_lkup(){	
		var goodno = document.edit.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
		if (goodno.length<10) {
			alert("10-digit phone no. required - any format");
			return;}
		var params = "phone=" + URLEncode(goodno)
		sendRequest (document.edit, 'wp_lkup.php',handleResult, params);		//1/17/09
		}
		
// *********************************************************************
		function pt_to_map (my_form, lat, lng) {				// 1/19/09
			my_form.frm_lat.value=lat;	
			my_form.frm_lng.value=lng;		
	
			my_form.show_lat.value=do_lat_fmt(my_form.frm_lat.value);
			my_form.show_lng.value=do_lng_fmt(my_form.frm_lng.value);
			
			my_form.frm_ngs.value=LLtoUSNG(my_form.frm_lat.value, my_form.frm_lng.value, 5);
			
			map.clearOverlays();
		
			map.setCenter(new GLatLng(my_form.frm_lat.value, my_form.frm_lng.value), <?php print get_variable('def_zoom');?>);
			var marker = new GMarker(map.getCenter());		// marker to map center
			var myIcon = new GIcon();
			myIcon.image = "./markers/sm_red.png";
			
			map.addOverlay(marker, myIcon);
			thePoint = new GLatLng(lat, lng);				// for grid toggle

			}				// end function pt_to_map ()
		

// *********************************************************************
	function loc_lkup(my_form) {		   // added 1/19/09 -- getLocations(address,  callback -- not currently used )
		if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var geocoder = new GClientGeocoder();
//				"1521 1st Ave, Seattle, WA"		
		var address = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();
		
		if (geocoder) {
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						alert(address + " not found");
						} 
					else {
						pt_to_map (my_form, point.lat(), point.lng())
						}
					}
				);
			}
		}				// end function addrlkup()

// *****************************************Reverse Geocoder 10/13/09
	var geocoder;
	var address;
	var rev_coding_on = '<?php print get_variable('reverse_geo');?>';	// 11/01/09	

	function getAddress(overlay, latlng) {
  		var geocoder = new GClientGeocoder();
		if (rev_coding_on == 1) {	// 11/01/09
			if (latlng != null) {
				address = latlng;   
				geocoder.getLocations(latlng, showAddress);  }
			}
		}	

	function showAddress(response) {
		map.clearOverlays();  
			if (!response || response.Status.code != 200) {
				alert("Status Code:" + response.Status.code);
			} else {
				place = response.Placemark[0];
 				locality = response.Placemark[0].AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality;   
				point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
				marker = new GMarker(point);
				map.addOverlay(marker);
				document.edit.frm_street.value = place.address;
				document.edit.frm_city.value = locality.LocalityName;
			}
		}

// *****************************************************************************


	</SCRIPT>


<?php
			}			// end  sanity check 
		}
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['id'];?>">
</FORM>	

</BODY>
<?php
			if ($addrs) {				// 10/21/08
?>			
<SCRIPT>
	function do_notify() {
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "TICKET-Update: ";
		var theId = '<?php print $_GET['id'];?>';
//			 mail_it ($to_str, $text, $ticket_id, $text_sel=1;, $txt_only = FALSE)
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId) + "&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08
		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function
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
	
</SCRIPT>
<?php

			}		// end if($addrs) 
		else {
?>		
<SCRIPT>
	function do_notify() {
		return;
		}			// end function do notify()
</SCRIPT>
<?php		
			}

?>
</HTML>
