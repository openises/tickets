<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once('incs/functions.inc.php');		//7/28/10
do_login(basename(__FILE__));
$gmaps = $_SESSION['internet'];
require_once($_SESSION['fmp']);		// 8/26/10
// $istest = TRUE;
if($istest) {print "_GET"; dump($_GET);}
if($istest) {print "_POST"; dump($_POST);}

$zoom_tight = FALSE;		// default is FALSE (no tight zoom); replace with a decimal zoom value to over-ride the standard default zoom setting - 3/27/10

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
12/16/09 quick-based click by-pass
1/3/10 corrections for quote handling
1/7/10 added elapsed time since written
3/13/10 present constituents 'miscellaneous'
3/25/10 facility code rearr and onChange added, logging revised
3/27/10 - zoom_tight added
5/6/10 accommodate embedded quotes in protocols values
6/26/10 handle 911 information field
7/5/10 Revised reverse geocoding function
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
7/28/10 Added default icon for tickets entered in no maps operation
8/13/10 map.setUIToDefault();	
8/26/10 FMP correction, etc.
11/23/10 'state' size made locale-dependent
12/1/10 get_text disposition added
12/7/10 '_by' included in update
12/18/10 added signals handling
1/1/11 Titles array added, scheduled incidents revised
1/19/10 revised to accommodate maps/no-maps
1/21/11 corrections to 'action' target two places
2/1/11 added table 'hints' as hints source
3/15/11 added reference to configurable stylesheet for reviseable colors.
2/11/11 condition signals on non-empty table
4/1/11 added 'by user' information
4/1/11 Added extra update query to update any existing assigns records for the ticket if they are not closed.
5/4/11 get_new_colors() 				// 
6/10/11 Added changes required to support regional capability (Ticket region assignment) plus tidied screen for no maps.
*/
	$addrs = FALSE;										// notifies address array doesn't exist

	function edit_ticket($id) {							/* post changes */
		global $addrs, $NOTIFY_TICKET;

		$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
		$post_frm_meridiem_booked_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_booked_date'])))) ) ? "" : $_POST['frm_meridiem_booked_date'] ;	//10/1/09
		$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;

		$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
		$post_frm_affected 	 		= strip_html($post_frm_affected);
		$_POST['frm_scope']			= strip_html($_POST['frm_scope']);

/*		if (get_variable('reporting')) {		// if any change do automatic action reporting
		
//			if ($_POST[frm_affected] != $_POST[frm_affected_default]) report_action($GLOBALS[ACTION_AFFECTED],$_POST[frm_affected],0,$id);
			if ($_POST[frm_severity] != $_POST[frm_severity_default]) report_action($GLOBALS[ACTION_SEVERITY],get_severity($_POST[frm_severity_default]),get_severity($_POST[frm_severity]),$id);
			if ($_POST[frm_scope] != $_POST[frm_scope_default]) report_action($GLOBALS[ACTION_SCOPE],$_POST[frm_scope_default],0,$id);
			} 
*/
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

		$curr_groups = $_POST['frm_exist_groups']; 	//	6/10/11
		$groups = "," . implode(',', $_POST['frm_group']) . ","; 	//	6/10/11
//		dump($_POST); 	//	6/10/11

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

		if($_POST['frm_status'] != 1) {
			$frm_problemend = "NULL";
			}
	
		// perform db update
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$by = $_SESSION['user_id'];			// 12/7/10
		if(empty($post_frm_owner)) {$post_frm_owner=0;}
								// 8/23/08, 9/20/08, 9/22/09 (Facility), 10/1/09 (receiving facility), 6/26/10 (911), 6/10/11
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
			`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
			`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
			`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
			`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
			`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
			`facility`= " . 	quote_smart(trim($_POST['frm_facility_id'])) . ",
			`rec_facility`= " . quote_smart(trim($_POST['frm_rec_facility_id'])) . ",
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
			`nine_one_one`= " . quote_smart(trim($_POST['frm_nine_one_one'])) .",
			`booked_date`= 		{$frm_booked_date},
			`_by` = 			{$by}, 
			`updated`='{$now}'
			WHERE ID='$id'";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		
		$list = $_POST['frm_exist_groups']; 	//	6/10/11
		$ex_grps = explode(',', $list); 	//	6/10/11 
		
		if($curr_groups != $groups) { 	//	6/10/11
			foreach($_POST['frm_group'] as $posted_grp) { 	//	6/10/11
				if(!(in_array($posted_grp, $ex_grps))) {
					$tick_stat = $_POST['frm_status'];
					$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
							($posted_grp, 1, '$now', $tick_stat, $id, 'Allocated to Group' , $by)";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
					}
				}
			foreach($ex_grps as $existing_grp) { 	//	6/10/11
				print $existing_grp;
				if(in_array($existing_grp, get_allocates(4, $id))) {
					if(!(in_array($existing_grp, $_POST['frm_group']))) {
						$query  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type` = 1 AND `group` = '$existing_grp' AND `resource_id` = {$id}";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				}
			}
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = '$id' AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')"; 
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$num_assigns = mysql_num_rows($result);

		if($num_assigns !=0) {	//	4/4/11 - added to update any existing assigns record with any ticket changes.
		$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
			`as_of`='{$now}',
			`status_id`= " . 	quote_smart(trim($_POST['frm_status'])) . ",
			`user_id`= " . 		quote_smart(trim($post_frm_owner)) . ",
			`facility_id`= " . 	quote_smart(trim($_POST['frm_facility_id'])) . ",
			`rec_facility_id`= " . quote_smart(trim($_POST['frm_rec_facility_id'])) . "
			WHERE ticket_id='$id'";		
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		}
	do_log($GLOBALS['LOG_INCIDENT_CHANGE'], $id, 0);	// report change - 3/25/10

	if($_POST['frm_status'] == $GLOBALS['STATUS_CLOSED']) {		// log incident complete - repeats possible
		do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $id, 0);		
		}
		
	switch ($_POST['frm_fac_chng']) {				// log facility changes - 3/25/10
		case "0":					// no change
			break;
		case "1":
			do_log($GLOBALS['LOG_FACILITY_INCIDENT_CHANGE'], $id, 0);	//10/1/09
			break;
		case "2":
			do_log($GLOBALS['LOG_CALL_REC_FAC_CHANGE'], $id);	//10/7/09			
			break;
		case "3":
			do_log($GLOBALS['LOG_FACILITY_INCIDENT_CHANGE'], $id, 0);	//10/1/09
			do_log($GLOBALS['LOG_CALL_REC_FAC_CHANGE'], $id);	//10/7/09	
			break;
		default:																	// 8/10/09
//			dump($_POST['frm_fac_chng']);
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		}			// end switch ()

	print '<FONT CLASS="header">Ticket <I>' . $_POST['frm_scope'] . '</I> has been updated</FONT><BR /><BR />';		/* show updated ticket */
//	notify_user($id, $GLOBALS['NOTIFY_TICKET']);
	add_header($id);
	show_ticket($id);
	$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE

	unset ($_SESSION['active_ticket']);								// 5/4/11

	}				// end function edit ticket() 

$api_key = get_variable('gmaps_api_key');
$nature = get_text("Nature");				// 12/1/10	{$nature} 
$disposition = get_text("Disposition");		// 	{$disposition} 
$patient = get_text("Patient");				// 	{$patient} 
$incident = get_text("Incident");			// 	{$incident} 
$incidents = get_text("Incidents");			// 	{$incidents} 	

$titles = array();				// 2/1/11

$query = "SELECT `tag`, `hint` FROM `$GLOBALS[mysql_prefix]hints`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$titles[trim($row['tag'])] = trim($row['hint']);
	}
$disallow = ((is_guest()) || ((is_user()) && (intval(get_variable('oper_can_edit')) != 1))) ;
$dis =  ($disallow)? "DISABLED ": "";				// 4/1/11 - 

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
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">		<!-- 3/15/11 -->
<?php
	if ($gmaps) {		// 1/1/11
?>
	<SCRIPT type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
	<SCRIPT SRC="./js/graticule.js" type="text/javascript"></SCRIPT>
	
<?php
		}
	?>
	<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>		<!-- 8/23/08 -->
	<SCRIPT SRC='./js/jscoord.js'></SCRIPT>		<!-- coordinate conversion 12/11/10 -->	
	<SCRIPT  SRC="./js/lat_lng.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
	<SCRIPT  SRC="./js/geotools2.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
	<SCRIPT  SRC="./js/osgb.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->		

<SCRIPT>

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function get_new_colors() {				// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
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
		alert(279);
		if (document.edit.frm_ngs.value.trim().length>6) {do_usng_conv();}
		}

	function do_usng_conv(){			// usng to LL array			- 12/4/08
		tolatlng = new Array();
		USNGtoLL(document.edit.frm_ngs.value, tolatlng);
		var point = new GLatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
		map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
		var marker = new GMarker(point);
		document.edit.frm_lat.value = point.lat(); document.edit.frm_lng.value = point.lng(); 	
		do_lat (point.lat());
		do_lng (point.lng());
		do_grids(document.edit);
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
														{errmsg+= "\tClosed ticket requires <?php print $disposition;?> data\n";}
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
//			top.upper.calls_start();											 // 1/17/09
			theForm.submit();
//			return true;
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
	
	function ReadOnlyCheckBox() {
		alert("You can't change this value");
		return false;
	}	
</SCRIPT>
</HEAD>

<?php
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if(!(empty($_POST))  && $quick) {
?>
	<BODY onLoad = "do_notify(); parent.frames['upper'].show_msg ('Edit applied!'); document.go_Form.submit();">
		<FORM NAME='go_Form' METHOD = 'post' ACTION="main.php">
		</FORM>	
		</BODY></HTML>
	
<?php
		}
$do_unload = ($gmaps)? " onUnload=\"GUnload();\"" : "";		
?>		
<STYLE>
		.box {
			background-color: transparent;
			border: none;
			color: #000000;
			padding: 0px;
			position: absolute;
			}
		.bar {
			background-color: #DEE3E7;
			color: transparent;
			cursor: move;
			font-weight: bold;
			padding: 2px 1em 2px 1em;
			}
		.content {
			padding: 1em;
			}
</STYLE>
<BODY onLoad = "do_notify(); ck_frames()" <?php print $do_unload; ?>>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<?php
	require_once('./incs/links.inc.php');

	if (array_key_exists('id', ($_GET))) {				// 5/4/11
		$_SESSION['active_ticket'] = $_GET['id'];
		$id = $_GET['id'];
		}
	elseif (array_key_exists('id', ($_SESSION))){
		$id = $_SESSION['active_ticket'];	
		}
	else {
		echo "error at "	 . __LINE__;
		}								// end if/else

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
			print "<FONT CLASS='header'>Confirm ticket deletion</FONT><BR /><BR /><FORM METHOD='post' NAME = 'del_form' ACTION='" . basename(__FILE__) . "?id=$id&delete=1&go=1'><INPUT TYPE='checkbox' NAME='frm_confirm' VALUE='1'>Delete ticket #$id &nbsp;<INPUT TYPE='Submit' VALUE='Confirm'></FORM>";
			}
		}
	else {				// not ($_GET['delete'])
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {		/* sanity check */
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT><BR />";
			} 

		else {				// OK, do form - 7/7/09, 4/1/11
 
 			$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,
 				UNIX_TIMESTAMP(problemend) AS problemend, 
 				UNIX_TIMESTAMP(booked_date) AS booked_date, 
 				UNIX_TIMESTAMP(date) AS date,
 				UNIX_TIMESTAMP(updated) AS updated, 
 				`t`.`description` AS `tick_descr`,
 				`u`.`user` AS `tick_user`
 				FROM `$GLOBALS[mysql_prefix]ticket` `t`
 				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`t`.`in_types_id` = `ty`.`id`)
 				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`t`.`_by` = `u`.`id`)
 				WHERE `t`.`id`='$id' LIMIT 1";
// 			snap(__LINE__, $query);

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

			$query_al = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 6/10/11
			$result_al = mysql_query($query_al);	// 6/10/11
			$al_groups = array();
			$al_names = "";	
			while ($row_al = stripslashes_deep(mysql_fetch_assoc($result_al))) 	{	// 6/10/11
				$al_groups[] = $row_al['group'];
				$query_al2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row_al[group]';";	// 6/10/11
				$result_al2 = mysql_query($query_al2);	// 6/10/11
				while ($row_al2 = stripslashes_deep(mysql_fetch_assoc($result_al2))) 	{	// 6/10/11		
						$al_names .= $row_al2['group_name'] . ", ";
					}
				}
			if(is_super()) {
				$al_names .= "&nbsp;&nbsp;Superadmin Level";
			}	
			
			if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11		
				$heading = "Edit Ticket - " . get_variable('map_caption') . "&nbsp;-----------&nbsp;Groups:&nbsp;&nbsp; " . $al_names;	//	6/10/11		
			} else {
				$heading = "Edit Ticket - " . get_variable('map_caption');	//	6/10/11		
			}			
				
				
				
			print "<TABLE BORDER='0' ID = 'outer' ALIGN='left' CLASS = 'BGCOLOR'>\n";
			if($gmaps) {	//	6/10/11		
				print "<TR CLASS='header'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'>" . $heading . "</FONT></TD></TR>";	//	6/10/11	
				print "<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN=99 ALIGN='center'>&nbsp;</TD></TR><TR><TD>";	//	6/10/11					
				print "<TR CLASS='odd'><TD ALIGN='left' COLSPAN='2'>";
				} else {
				print "<TR CLASS='header'><TD ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'>" . $heading . "</FONT></TD></TR>";	//	6/10/11	
				print "<TR CLASS='spacer'><TD CLASS='spacer' ALIGN='center'>&nbsp;</TD></TR><TR><TD>";	//	6/10/11						
				print "<TR CLASS='odd'><TD ALIGN='left'>";	
				}
			print add_header($id, TRUE);
			print "</TD></TR>\n";
			print "<TR CLASS='odd'><TD>&nbsp;</TD></TR>\n";	
			if($gmaps) {	//	6/10/11
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left'>";
			} else {
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left' style='width: 100%;' COLSPAN=99>";
			}
	
			print "<FORM NAME='edit' METHOD='post' onSubmit='return validate(document.edit)' ACTION='" . basename(__FILE__) . "?id=$id&action=update'>";
			if($gmaps) {	//	6/10/11
				print "<TABLE BORDER='0' ID='data'>\n";
				} else {
				print "<TABLE BORDER='0' ID='data' WIDTH='100%'>\n";
				}
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=3><FONT CLASS='$theClass'><B>Edit Run Ticket</FONT> (#{$id})</B></TD></TR>";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=3><FONT CLASS='header'><FONT SIZE='-2'>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>";

			print "<TR CLASS='even'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_loca']}');\">" . get_text("Location") . ": </TD><TD><INPUT SIZE='48' TYPE='text' NAME='frm_street' VALUE=\"{$row['street']}\" MAXLENGTH='48' {$dis}></TD></TR>\n";
			print "<TR CLASS='odd'>
					<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_city']}');\">" . get_text("City") . ":</TD>";
			print 		"<TD><button type='button' onClick='Javascript:loc_lkup(document.edit);'><img src='./markers/glasses.png' alt='Lookup location.' /></button>";
			print 		"</TD>
						<TD><INPUT SIZE='32' TYPE='text' 	NAME='frm_city' VALUE=\"{$row['city']}\" MAXLENGTH='32' onChange = 'this.value=capWords(this.value)' {$dis}>\n";
			$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10
			print 	"<SPAN STYLE='margin-left:24px'  onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_state']}');\">" . get_text("St") . "</SPAN>:&nbsp;&nbsp;<INPUT SIZE='{$st_size}' TYPE='text' NAME='frm_state' VALUE='" . $row['state'] . "' MAXLENGTH='{$st_size}' {$dis}></TD></TR>\n";

			print "<TR CLASS='even'>
				<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_phone']}');\">" . get_text("Phone") . ":</TD>";
			print 		"<TD><button type='button'  onClick='Javascript:phone_lkup(document.edit.frm_phone.value);'><img src='./markers/glasses.png' alt='Lookup phone no' /></button>";	// 1/19/09
			print 		"</TD><TD><INPUT SIZE='48' TYPE='text' NAME='frm_phone' VALUE='" . $row['phone'] . "' MAXLENGTH='16' {$dis}></TD></TR>\n";

			if (!(empty($row['phone']))) {					// 3/13/10
				$query  = "SELECT `miscellaneous` FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$row['phone']}' LIMIT 1";
				$result_cons = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if (mysql_affected_rows() > 0) {
					$row_cons = stripslashes_deep(mysql_fetch_array($result_cons));
					print "<TR CLASS='even'>
						<TD CLASS='td_label' COLSPAN=2 >Add'l:</TD>
						<TD CLASS='td_label'>{$row_cons['miscellaneous']}</TD></TR>\n";		// 3/13/10
					}
				unset($result_cons);
				}				
			print "<TR CLASS='odd'>
				<TD CLASS='td_label' COLSPAN=2 >";
			print "<SPAN CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_nature']}');\">{$nature}:</SPAN>\n";
			print "</TD><TD>";
	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<SELECT NAME='frm_in_types_id'  {$dis} onChange='do_inc_nature(this.options[selectedIndex].value.trim());'>";
//			dump($row['in_types_id']);
			if ($row['in_types_id']==0) {print "\n\t<OPTION VALUE=0 SELECTED>TBD</OPTION>\n";}
			
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
					print "\n<SCRIPT>protocols[{$row2['id']}] = '{$row2['protocol']}';</SCRIPT>\n";		// 5/6/10
					}
				$i++;
				}
			unset ($result);
			print "</OPTGROUP></SELECT>";
			print "<SPAN STYLE='margin-left: 30px;' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_prio']}');\">" . get_text("Priority") . ": <SELECT NAME='frm_severity' {$dis}>";		// 2/21/09
				$nsel = ($row['severity']==$GLOBALS['SEVERITY_NORMAL'])? "SELECTED" : "" ;
				$msel = ($row['severity']==$GLOBALS['SEVERITY_MEDIUM'])? "SELECTED" : "" ;
				$hsel = ($row['severity']==$GLOBALS['SEVERITY_HIGH'])? "SELECTED" : "" ;
				
				print "<OPTION VALUE='" . $GLOBALS['SEVERITY_NORMAL'] . "' $nsel>normal</OPTION>";
				print "<OPTION VALUE='" . $GLOBALS['SEVERITY_MEDIUM'] . "' $msel>medium</OPTION>";
				print "<OPTION VALUE='" . $GLOBALS['SEVERITY_HIGH'] . "' $hsel>high</OPTION>";
				print "</SELECT>";
				print "</SPAN></TD></TR>";

			print "<TR CLASS = 'odd'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_proto']}');\">" . get_text("Protocol") . ":</TD>";
			print 	"<TD ID='proto_cell'>{$row['protocol']}</TD></TR>\n";

			if(get_num_groups() > 1) {			
			if((is_super()) && (get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
					print "<TR CLASS='even' VALIGN='top'>";
					print "<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Group') . "</A>: </TD>";
					print "<TD><SPAN id='expand_gps' onClick=\"$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>";
					print "<SPAN id='collapse_gps' onClick=\"$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>";
					print "<TD>";
					$alloc_groups = implode(',', get_allocates(1, $id));	//	6/10/11
					print get_sub_group_butts(($_SESSION['user_id']), 1, $id) ;	//	6/10/11		
					print "</DIV></TD></TR>";		// 6/10/11
					
				} elseif((is_admin()) && (get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11	
					print "<TR CLASS='even' VALIGN='top'>";
					print "<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Group') . "</A>: </TD>";
					print "<TD><SPAN id='expand_gps' onClick=\"$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>";
					print "<SPAN id='collapse_gps' onClick=\"$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>";
					print "<TD>";
					$alloc_groups = implode(',', get_allocates(1, $id));	//	6/10/11
					print get_sub_group_butts(($_SESSION['user_id']), 1, $id) ;	//	6/10/11			
					print "</DIV></TD></TR>";		// 6/10/11	
					
				} else {
					print "<TR CLASS='even' VALIGN='top'>";
					print "<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Group') . "</A>: </TD>";
					print "<TD><SPAN id='expand_gps' onClick=\"$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>";
					print "<SPAN id='collapse_gps' onClick=\"$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>";
					print "<TD>";
					$alloc_groups = implode(',', get_allocates(1, $id));	//	6/10/11
					print get_sub_group_butts_readonly(($_SESSION['user_id']), 1, $id) ;	//	6/10/11			
					print "</DIV></TD></TR>";		// 6/10/11	
				}
			} else {
			print "<INPUT TYPE='hidden' NAME='frm_group[]' VALUE='1'>";
			}
			
			print "<TR CLASS='odd' VALIGN='top'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_synop']}');\">" . get_text("Synopsis") . ":</TD>";
			print 	"<TD CLASS='td_label'><TEXTAREA NAME='frm_description' COLS='45' ROWS='2' {$dis} >" . $row['tick_descr'] . "</TEXTAREA></TD></TR>\n";		// 8/8/09
														// 2/11/11
			$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
			$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);

			if (mysql_num_rows($result_sigs)>0) {				// 2/11/11
?>
<SCRIPT>
	function set_signal(inval) {
		var lh_sep = (document.edit.frm_description.value.trim().length>0)? " " : "";
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.edit.frm_description.value+= lh_sep + temp_ary[1] + ' ';		
		document.edit.frm_description.focus();		
		}		// end function set_signal()

	function set_signal2(inval) {
		var lh_sep = (document.edit.frm_comments.value.trim().length>0)? " " : "";
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.edit.frm_comments.value+= lh_sep + temp_ary[1] + ' ';		
		document.edit.frm_comments.focus();		
		}		// end function set_signal()
</SCRIPT>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
			<TD COLSPAN=2 ></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals' <?php print $dis; ?> onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
<?php
				}						// end if (mysql_num_rows()>0)
			print "<TR CLASS='even'>
				<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_911']}');\">" . get_text("911 Contacted") . ":&nbsp;</TD>
				<TD><INPUT SIZE='56' TYPE='text' NAME='frm_nine_one_one' VALUE='{$row['nine_one_one']}' MAXLENGTH='96' {$dis}/></TD></TR>";

			print "<TR CLASS='odd'>
				<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_caller']}');\">" . get_text("Reported by") . ":</TD>
				<TD><INPUT SIZE='48' TYPE='text' NAME='frm_contact' VALUE=\"{$row['contact']}\" MAXLENGTH='48' {$dis}/></TD></TR>\n";

			print "<TR CLASS='even'>
				<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_name']}');\">" . get_text("Incident name") . ":</TD>
				<TD><INPUT TYPE='text' NAME='frm_scope' SIZE='48' VALUE='{$row['scope']}' MAXLENGTH='48' {$dis} /></TD></TR>\n"; 

			print "<TR CLASS='odd'><TD COLSPAN='2'>&nbsp;</TD></TR>";

			$selO = ($row['status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
			$selC = ($row['status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
			$selP = ($row['status']==$GLOBALS['STATUS_SCHEDULED'])? "SELECTED" :"" ;

			$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (get_variable('delta_mins')*60));
			$elapsed = my_date_diff($row['problemstart'], $end_date);		// 5/13/10

			print "<TR CLASS='even'>
				<TD CLASS='td_label' COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_status']}');\">" . get_text("Status") . ":</TD>
				<TD><SELECT NAME='frm_status' {$dis}><OPTION VALUE='" . $GLOBALS['STATUS_OPEN'] . "' $selO>Open</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_CLOSED'] . "'$selC>Closed</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_SCHEDULED'] . "'$selP>Scheduled</OPTION></SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$elapsed} </TD></TR>";
//			print "<TR CLASS='odd'><TD CLASS='td_label'>Affected:</TD><TD><INPUT TYPE='text' SIZE='48' NAME='frm_affected' VALUE='" . $row['affected'] . "' MAXLENGTH='48' {$dis}></TD></TR>\n";

//	facility handling  - 3/25/10

			$query_al = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
			$result_al = mysql_query($query_al);	// 4/13/11
			$al_groups = array();
			while ($row_al = stripslashes_deep(mysql_fetch_assoc($result_al))) 	{	//	6/10/11
				$al_groups[] = $row_al['group'];
				}
				
			if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
				$curr_viewed= explode(",",$_SESSION['viewed_groups']);
				}

			if(!isset($curr_viewed)) {	
				$x=0;	//	6/10/11
				$where2 = "WHERE (";	//	6/10/11
				foreach($al_groups as $grp) {	//	6/10/11
					$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
					$where2 .= "`a`.`group` = '{$grp}'";
					$where2 .= $where3;
					$x++;
					}
			} else {
				$x=0;	//	6/10/11
				$where2 = "WHERE (";	//	6/10/11
				foreach($curr_viewed as $grp) {	//	6/10/11
					$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
					$where2 .= "`a`.`group` = '{$grp}'";
					$where2 .= $where3;
					$x++;
					}
			}
			$where2 .= " AND `a`.`type` = 3";	//	6/10/11		

			if (!($row['facility'] == NULL)) {				// 9/22/09
	
				print "<TR CLASS='odd'>
					<TD CLASS='td_label' COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_facy']}');\">Facility: &nbsp;&nbsp;</TD>";		// 2/21/09
				$query_fc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = `a`.`resource_id` )		
				$where2 GROUP BY `fac_id` ORDER BY `name` ASC";		
				$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				print "<TD><SELECT NAME='frm_facility_id'  {$dis} onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
				print "<OPTION VALUE=0>Not using facility</OPTION>";
	
				print "\n<SCRIPT>fac_lat[" . 0 . "] = " . get_variable('def_lat') . " ;</SCRIPT>\n";
				print "\n<SCRIPT>fac_lng[" . 0 . "] = " . get_variable('def_lng') . " ;</SCRIPT>\n";
	
				while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
					$sel = ($row['facility'] == $row_fc['fac_id']) ? " SELECTED " : "";
					print "<OPTION VALUE=" . $row_fc['fac_id'] . $sel . ">" . shorten($row_fc['name'], 20) . "</OPTION>";
					print "\n<SCRIPT>fac_lat[" . $row_fc['fac_id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
					print "\n<SCRIPT>fac_lng[" . $row_fc['fac_id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
					}
				print "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;\n";
				unset ($result_fc);
				} 				// end if (!($row['facility'] == NULL))
			else {	
				$query_fc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = `a`.`resource_id` )		
				$where2 GROUP BY `fac_id` ORDER BY `name` ASC";	
				$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$pulldown = '<option>Incident at Facility?</option>';
				while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
					$pulldown .= "<option value=" . $row_fc['fac_id'] . ">" . shorten($row_fc['name'], 20) . "</option>\n";
					print "\n<SCRIPT>fac_lat[" . $row_fc['fac_id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
					print "\n<SCRIPT>fac_lng[" . $row_fc['fac_id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
					}
				unset ($result_fc);
				print "<TR CLASS='odd'>
					<TD CLASS='td_label' COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_facy']}');\">Facility?:</TD>";
				print "<TD><SELECT NAME='frm_facility_id'  {$dis} onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>$pulldown</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;\n";
				}
//	receiving facility - 3/25/10

			if (!($row['rec_facility'] == NULL)) {				// 10/1/09
				$query_rfc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = a.resource_id )		
				$where2 GROUP BY `fac_id` ORDER BY `name` ASC";		
				$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				print "<SELECT NAME='frm_rec_facility_id' {$dis} onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>";
				print "<OPTION VALUE=0>No receiving facility</OPTION>";

				while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
					$sel2 = ($row['rec_facility'] == $row_rfc['fac_id']) ? " SELECTED " : "";
					print "<OPTION VALUE=" . $row_rfc['fac_id'] . $sel2 . ">" . shorten($row_rfc['name'], 20) . "</OPTION>";		// 2/14/11
					}
				print "</SELECT></TD></TR>\n";
				unset ($result_rfc);
				} 
			else {
				$query_rfc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = a.resource_id )		
				$where2 GROUP BY `fac_id` ORDER BY `name` ASC";	
				$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$pulldown2 = '<option>Receiving Facility?</option>';
					while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
						$pulldown2 .= "<option value=" . $row_rfc['fac_id'] . ">" . shorten($row_rfc['name'], 20) . "</option>\n";
					}
				unset ($result_rfc);
				print "<SELECT NAME='frm_rec_facility_id' {$dis} onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>$pulldown2</SELECT></TD></TR>\n";	//10/1/09
				}

			if (good_date($row['booked_date'])) {	//10/1/09
				print "\n<TR CLASS='odd'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_booked']}');\">Scheduled Date:</TD><TD>";
				generate_date_dropdown("booked_date",$row['booked_date'], $disallow);	// ($date_suffix,$default_date=0, $disabled=FALSE) 
				print "</TD></TR>\n";
				}
			else {	//10/1/09
				print "\n<TR CLASS='even' valign='middle'>
					<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_booked']}');\">" . get_text("Scheduled Date") . ": </TD>
					<TD ALIGN='center'><input type='radio' name='boo_but' onClick = 'do_booking(this.form);' {$dis} /></TD><TD>";
				print "<SPAN style = 'visibility:hidden' ID = 'booked1'>";
				generate_date_dropdown('booked_date',0, $disallow);
				print "</TD></TR>\n";
				print "</SPAN></TD></TR>\n";
				}

			print "<TR CLASS='odd'><TD COLSPAN=3 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD></TR>\n";			

			print "\n<TR CLASS='even'>
				<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_start']}');\">" . get_text("Run Start") . ":</TD><TD ALIGN='center'><img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'st_unlk(document.edit);'></TD><TD>";
			print  generate_date_dropdown("problemstart",$row['problemstart'],0, $disallow);
			print "&nbsp;&nbsp;&nbsp;&nbsp;</TD></TR>\n";
			if (good_date($row['problemend'])) {

				print "\n<TR CLASS='odd'>
					<TD CLASS='td_label' COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_end']}');\">" . get_text("Run End") . ":</TD><TD>";
				generate_date_dropdown("problemend",$row['problemend'], $disallow);
				print "</TD></TR>\n";
				}
			else {
				print "\n<TR CLASS='odd' valign='middle'>
					<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_end']}');\">Run End: </TD>
					<TD ALIGN='center'><input type='radio' name='re_but'  {$dis} onClick ='do_end(this.form);' /></TD>";
				print "<TD><SPAN style = 'visibility:hidden' ID = 'runend1'>";
				generate_date_dropdown('problemend',0, $disallow);
//				print "</TD></TR>\n";
				print "</SPAN></TD></TR>\n";
				}

			print "<TR CLASS='even' VALIGN='top'>
				<TD CLASS='td_label' COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_disp']}');\">{$disposition}:</TD>";				// 10/21/08, 8/8/09
			print 	"<TD><TEXTAREA NAME='frm_comments' COLS='45' ROWS='2' {$dis} >" . $row['comments'] . "</TEXTAREA></TD></TR>\n";
			$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
			$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);

			if (mysql_num_rows($result_sigs)>0) {				// 2/11/11			
?>
		<TR VALIGN = 'TOP' CLASS='even'>		<!-- 12/18/10 -->
			<TD COLSPAN=2 ></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals' <?php print $dis; ?> onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result_sigs))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
<?php
				}						// end if (mysql_num_rows($result_sigs)>0)

			print "<TR CLASS='odd'>
				<TD CLASS='td_label' COLSPAN=2 onClick = 'javascript: do_coords(document.edit.frm_lat.value ,document.edit.frm_lng.value  )'><U><A HREF=\"#\" TITLE=\"Position - Lat and Lng for Incident position. Click to show all position data.\">Position</A></U>:</TD><TD>";
			print	 "<INPUT SIZE='13' TYPE='text' NAME='show_lat' VALUE='" . get_lat($row['lat']) . "' DISABLED>\n";
			print 	 "<INPUT SIZE='13' TYPE='text' NAME='show_lng' VALUE='" . get_lng($row['lng']) . "' DISABLED>&nbsp;&nbsp;";

			$locale = get_variable('locale');	// 08/03/09
			switch($locale) { 
				case "0":
					$usng = LLtoUSNG($row['lat'], $row['lng']);
					print "<B><SPAN ID = 'USNG' onClick = 'do_usng()'><U><A HREF='#' TITLE='US National Grid Co-ordinates.'>USNG</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='{$usng}'></TD></TR>";		// 9/13/08, 5/2/09
					break;
			
				case "1":
					$osgb = LLtoOSGB($row['lat'], $row['lng']) ;
					print "<B><SPAN ID = 'OSGB' ><U><A HREF='#' TITLE='United Kingdom Ordnance Survey Grid Reference.'>OSGB</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_osgb' VALUE='{$osgb}' DISABLED ></TD></TR>";		// 9/13/08, 5/2/09
					break;			

				default:																	// 8/10/09
					$utm_str = toUTM("{$row['lat']}, {$row['lng']}");
					print "<B><SPAN ID = 'UTM'><U><A HREF='#' TITLE='Universal Transverse Mercator coordinate.'>UTM</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_utm' VALUE='{$utm_str}' DISABLED ></TD></TR>";		// 9/13/08, 5/2/09
					break;

				}

			print "</TD></TR>\n";
			$by_str = "&nbsp;&nbsp;&nbsp;by '{$row['tick_user']}'";				// 4/1/11
			print "<TR CLASS='even'>
				<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_asof']}');\">Updated:</TD><TD>" . format_date($row['updated']) . "{$by_str}</TD></TR>\n";		// 10/21/08
			$lat = $row['lat']; $lng = $row['lng'];	
			if(($lat==0.999999) && ($lng==0.999999)) {	// check ticket entered in "no maps" Mode 7/28/10
				$lat=get_variable('def_lat');
				$lng=get_variable('def_lng');
				}	// End of check on ticket entered in "no maps" Mode 7/28/10
?>	
			
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $row['lat'];?>">				<!-- // 8/9/08 -->
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $row['lng'];?>">
			<INPUT TYPE="hidden" NAME="frm_status_default" VALUE="<?php print $row['status'];?>">
			<INPUT TYPE="hidden" NAME="frm_affected_default" VALUE="<?php print $row['affected'];?>">
			<INPUT TYPE="hidden" NAME="frm_scope_default" VALUE="<?php print $row['scope'];?>">
			<INPUT TYPE="hidden" NAME="frm_owner_default" VALUE="<?php print $row['owner'];?>">
			<INPUT TYPE="hidden" NAME="frm_severity_default" VALUE="<?php print $row['severity'];?>">
			<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>">
			<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>">
			<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">			
			<INPUT TYPE="hidden" NAME="frm_fac_chng" VALUE="0">		<!-- 3/25/10 -->
<?php
			print "<TR CLASS='even'>
				<TD COLSPAN='10' ALIGN='center'><BR /><B><U><A HREF='#' TITLE='List of all actions and patients atached to this Incident'>Actions and Patients</A></U></B><BR /></TD></TR>";	//8/7/09
			print "<TR CLASS='odd'><TD COLSPAN='10' ALIGN='center'>";										//8/7/09
//			$temp = (!((is_user()) && (intval(get_variable('oper_can_edit')) != 1)));					// 4/1/11
			print show_actions($row[0], "date", !$disallow, TRUE);											//8/7/09
			print "<BR /><BR /></TD></TR>";																//8/7/09
			print "</TABLE>";		// end data 8/7/09
															//8/7/09
			if ($gmaps) {		// 1/1/11, 6/10/11
				print "</TD><TD>";		
				print "<TABLE ID='mymap' border = 0><TR><TD ALIGN='center'><DIV ID='map' STYLE='WIDTH: " . get_variable('map_width') . "PX; HEIGHT:" . get_variable('map_height') . "PX'></DIV>
					<BR /><SPAN ID='do_grid' onClick='toglGrid()'><U>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN ID='do_sv' onClick = 'sv_win(document.edit)'><U>Street view</U></SPAN>";
				print ($zoom_tight)? "<SPAN  onClick= 'zoom_in({$lat}, {$lng}, {$zoom_tight});' STYLE = 'margin-left:20px'><U>Zoom</U></SPAN>\n" : "";	// 3/27/10
					
				print "</TD></TR></TABLE ID='mymap'>\n";
				}
			
			print "</TD></TR>";
			print "<TR><TD CLASS='print_TD' COLSPAN='2'>";
			print "</FORM>";
			print "</TD></TR></TABLE>";		// bottom of outer
			
			$from_left = $gmaps ? 450: 650;	//	6/10/11
$from_top = 100;
?>			
			<FORM NAME='can_Form' ACTION="main.php">
			<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['id'];?>">
			</FORM>	
					<DIV id='boxB' class='box' STYLE='left:<?php print $from_left;?>px;top:<?php print $from_top;?>px; position:fixed;' > <!-- 9/23/10 -->
					<DIV class="bar" STYLE="width:12em; color:red; background-color : transparent;"
						 onmousedown="dragStart(event, 'boxB')">&nbsp;&nbsp;&nbsp;&nbsp;<I>Drag me</I></DIV><!-- drag bar - 2/5/11 -->
						 <DIV STYLE = 'height:10px;'/>&nbsp;</DIV>
							<INPUT TYPE='button' VALUE='<?php print get_text("Cancel");?>' onClick='history.back();'  STYLE = 'margin-left:20px;' /><BR />
<?php
if (!$disallow) {
?>
							<INPUT TYPE='button' VALUE='<?php print get_text("Reset");?>' onClick= 'st_unlk_res(document.edit); reset_end(document.edit); document.edit.reset(); resetmap(<?php print $lat;?>, <?php print $lng;?>);'  STYLE = 'margin-left:20px; margin-top:10px' /><BR />
							<INPUT TYPE='button' VALUE='<?php print get_text("Next");?>'  onClick='validate(document.edit);' STYLE = 'margin-left:20px; margin-top:10px' />
<?php
		}
?>		
					</DIV>				
<?php
	if ($gmaps) {				// 1/1/11

?>
	<SCRIPT>
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
	
	
		var map;												// <?php print __LINE__;?>

		var icons=[];						// note globals
		icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./our_icons/blue.png";		// normal
		icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./our_icons/green.png";	// green
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./our_icons/red.png";		// red	
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./our_icons/white.png";	// white - not in use

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
//		map.addControl(new GSmallMapControl());					// 1/19/09
		map.setUIToDefault();										// 8/13/10

		map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	
//		map.addControl(new GOverviewMapControl());		
		map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>), <?php echo ($zoom_tight)? $zoom_tight : get_variable('def_zoom');?>);	// 3/27/10
		map.enableScrollWheelZoom(); 	
		
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
//		baseIcon.infoWindowAnchor = new GPoint(9, 2);
//		baseIcon.infoShadowAnchor = new GPoint(18, 25);
<?php
		if(($row['lat']==0.999999) && ($row['lng']==0.999999)) {	// check of Tickets entered in "no maps" mode 7/28/10
?>
			var icon = new GIcon(baseIcon);
			var icon_url = "./our_icons/question1.png";				// 7/28/10
			icon.image = icon_url;		
<?php
		} else {
?>		

		var icon = new GIcon(baseIcon);
		icon.image = icons[<?php print $row['severity'];?>];		
<?php
		}	// end of check of Tickets entered in "no maps" mode 7/28/10
?>		
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
			do_lat (point.lat());								// display
			do_lng (point.lng());

			do_grids(document.edit);							// 12/13/10
			map.addOverlay(new GMarker(point, icon));			// GLatLng.
			map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
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

	function do_grids(theForm) {								//12/13/10
//		if (theForm.frm_ngs.value) {do_usng(theForm) ;}
		if (theForm.frm_utm) {do_utm (theForm);}
		if (theForm.frm_osgb) {do_osgb (theForm);}
		}
	function do_usng(theForm) {								// 8/23/08, 12/5/10
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);	// US NG
		}

	function do_utm (theForm) {
		var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
		var utm_out = ll_in.toUTMRef().toString();
		temp_ary = utm_out.split(" ");
		theForm.frm_utm.value = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
		}

	function do_osgb (theForm) {
		var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
		var osgb_out = ll_in.toOSRef();
		theForm.frm_osgb.value = osgb_out.toSixFigureString();
		}

</SCRIPT>
<?php
		}		// end if ($gmaps) 
?>

<SCRIPT>
	function resetmap(lat, lng) {						// restore original marker and center
<?php
		if (!($gmaps)) {print "\n\t return;\n";}		// 1/1/11
?>
		map.clearOverlays();
		var point = new GLatLng(lat, lng);	
		icon.image = icons[<?php print $row['severity'];?>];		
		map.addOverlay(new GMarker(point, icon));
		map.setCenter(new GLatLng(lat, lng),<?php echo get_variable('def_zoom'); ?>);
		do_lat (lat);
		do_lng (lng);
		do_ngs(document.edit);								// 8/23/08
		if (grid) {map.addOverlay(new LatLonGraticule());}	// restore grid
		}

// *********************************************************************
	function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
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
			the_form.frm_contact.value=result[1].trim();
			the_form.frm_phone.value=result[2].trim();		// phone
			the_form.frm_street.value=result[3].trim();		// street
			the_form.frm_city.value=result[4].trim();		// city
			the_form.frm_state.value=result[5].trim();		// state 
//			the_form.frm_zip.value=result[6].trim();		// frm_zip - unused

			pt_to_map (the_form, result[7].trim(), result[8].trim());				// 1/19/09
			
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
			var locale = <?php print get_variable('locale');?>;
			
			my_form.frm_lat.value=lat;	
			my_form.frm_lng.value=lng;		
	
			my_form.show_lat.value=do_lat_fmt(my_form.frm_lat.value);
			my_form.show_lng.value=do_lng_fmt(my_form.frm_lng.value);
			if(locale == 0) {
				my_form.frm_ngs.value=LLtoUSNG(my_form.frm_lat.value, my_form.frm_lng.value, 5);
				}
			if(locale == 1) {
				my_form.frm_osgb.value=LLtoOSGB(my_form.frm_lat.value, my_form.frm_lng.value, 5);
				}
			if(locale == 2) {
				my_form.frm_utm.value=LLtoUTM(my_form.frm_lat.value, my_form.frm_lng.value, 5);
				}				
			
			map.clearOverlays();
		
			map.setCenter(new GLatLng(my_form.frm_lat.value, my_form.frm_lng.value), <?php print get_variable('def_zoom');?>);
			var marker = new GMarker(map.getCenter());		// marker to map center
			var myIcon = new GIcon();
			myIcon.image = "./markers/sm_red.png";
			
			map.addOverlay(marker, myIcon);
			thePoint = new GLatLng(lat, lng);				// for grid toggle

			}				// end function pt_to_map ()

// *********************************************************************

	function zoom_in (in_lat, in_lng, in_zoom) {				// 3/27/10
		map.setCenter(new GLatLng(in_lat, in_lng), in_zoom );
		var marker = new GMarker(map.getCenter());				// marker to map center
		var myIcon = new GIcon();
		myIcon.image = "./markers/sm_red.png";
		map.addOverlay(marker, myIcon);		 
		}				// end function zoom in ()		 		

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

// ****************************************************Reverse Geocoder 10/13/09, 7/5/10
	var geocoder;
	var address;
	var rev_coding_on = '<?php print get_variable('reverse_geo');?>';		// 7/5/10	
		
	function getAddress(overlay, latlng) {		//7/5/10
		var geocoder = new GClientGeocoder();
		if (rev_coding_on == 1) {	
			if (latlng != null) {
				geocoder.getLocations(latlng, function(response) {
				map.clearOverlays();  
					if(response.Status.code != 200) {
						alert("948: Status Code:" + response.Status.code);
					} else { 
						place = response.Placemark[0];
						locality = response.Placemark[0].AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality;   
						point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
						marker = new GMarker(point);
						map.addOverlay(marker);
						document.edit.frm_street.value = place.address;
						document.edit.frm_city.value = locality.LocalityName;
						}
					});
				}
			}
		}


// *****************************************************************************

	</SCRIPT>


<?php
			}			// end  sanity check 
		}

?>

</BODY>
<?php


			if ($addrs) {				// 10/21/08
?>			
<SCRIPT>
	function do_notify() {
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "TICKET-Update: ";
		var theId = '<?php print $_GET['id'];?>';

//			 		 ($to_str, $text, $ticket_id, $text_sel=1;, $txt_only = FALSE)
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
