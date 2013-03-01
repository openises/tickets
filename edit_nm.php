<?php
$zoom_tight = FALSE;		// default is FALSE (no tight zoom); replace with a decimal zoom value to over-ride the standard default zoom setting - 3/27/10

error_reporting(E_ALL);

/*
7/16/10 Initial Release for no internet operation - created from edit.php
8/26/10 revised hard-coded self-address, and added 911 contact field
9/8/10 $mode handling added to accommodate mobile usage
11/23/10 'state' size made locale-dependent
12/03/10 get_text  added
12/7/10 '_by' included in update
12/18/10 signals added
01/10/11 Fixed display due to no use of stylesheet.php file.
3/15/11 Reference to revisable stylesheet for configurable day and night colors.
5/4/11 get_new_colors added

*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Edit Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
	<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>		<!-- 8/23/08 -->
<?php
	
@session_start();
require_once('incs/functions.inc.php');		//7/28/10
do_login(basename(__FILE__));
$mode = ((array_key_exists('mode',$_REQUEST)) && ($_REQUEST['mode'] ==1))? 1: 0;		// 9/8/10

require_once($_SESSION['fmp']);		// 8/26/10

if($istest) {print "_GET"; dump($_GET);}
if($istest) {print "_POST";dump($_POST);}
$this_file = basename(__FILE__);					// 8/26/10
$addrs = FALSE;										// notifies address array doesn't exist

$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$incident_name = get_text("Incident name");

/*
{$nature}			Nature
<?php print $nature;?>

{$disposition}		Disposition
<?php print $disposition;?>

{$patient}			Patient
<?php print $patient;?>

{$incident}			Incident
<?php print $incident;?>

{$incidents}		Incidents
<?php print $incidents;?>

$incident_name = get_text("Incident name")

global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
*/

function edit_ticket($id) {							/* post changes */
		global $addrs, $NOTIFY_TICKET, $mode;			// 9/8/10

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
	
		// perform db update
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$by = $_SESSION['user_id'];			// 12/7/10
		if(empty($post_frm_owner)) {$post_frm_owner=0;}
																					// 8/23/08, 9/20/08, 9/22/09 (Facility), 10/1/09 (receiving facility)
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
			`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
			`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
			`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
			`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
			`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
			`facility`= " . 		quote_smart(trim($_POST['frm_facility_id'])) . ",
			`rec_facility`= " . 	quote_smart(trim($_POST['frm_rec_facility_id'])) . ",
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
			`booked_date`= ".	$frm_booked_date . ",
			`_by` = 			{$by}, 
			`updated`='$now'
			WHERE ID='$id'";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		
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
			dump($_POST['frm_fac_chng']);
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		}			// end switch ()

	print '<FONT CLASS="header" STYLE = "margin-left:40px">Update complete</FONT><BR /><BR />';		/* show updated ticket */
//	notify_user($id, $GLOBALS['NOTIFY_TICKET']);
	if (!($mode==1)) {											// 9/8/10
		add_header($id);
		}
	show_ticket($id);
	$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE

	}				// end function edit ticket() 

	$api_key = get_variable('gmaps_api_key');
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Edit Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
	<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>		<!-- 8/23/08 -->
<SCRIPT>

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function get_new_colors() {		// 5/4/11
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

	function ck_frames() {		// onLoad = "ck_frames()"
<?php if ($mode==1) { print "\t\t return;\n"; } else { ?>	
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
<?php } ?>		
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

<?php
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if(!(empty($_POST))  && $quick) {
		
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Add Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css" /> <!-- 3/15/11 -->
	<BODY onLoad = "do_notify(); parent.frames['upper'].show_msg ('Edit applied!'); document.go_Form.submit();">
		<FORM NAME='go_Form' METHOD = 'post' ACTION="main.php">
		</FORM>	
		</BODY></HTML>
<?php
		}
		
?>		
<BODY onLoad = "do_notify(); ck_frames()" >
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
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id'") or do_error("{$this_file}::remove_ticket(ticket)", 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$id'") or do_error("{$this_file}::remove_ticket(action)", 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<FONT CLASS=\"header\">Ticket '$id' has been removed.</FONT><BR /><BR />";
			list_tickets();
			}
		else {		//confirm deletion
			print "<FONT CLASS='header'>Confirm ticket deletion</FONT><BR /><BR /><FORM METHOD='post' NAME = 'del_form' ACTION='{$this_file}?id=$id&delete=1&go=1'><INPUT TYPE='checkbox' NAME='frm_confirm' VALUE='1'>Delete ticket #$id &nbsp;<INPUT TYPE='Submit' VALUE='Confirm'></FORM>";	// 8/26/10
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

			print "<TABLE BORDER='0' ID = 'outer' ALIGN='left' CLASS = 'BGCOLOR' STYLE = 'margin-left:20px'>\n";
			if(!($mode==1)) {					// 9/8/10
				print "<TR CLASS='odd'><TD ALIGN='left>" . add_header($id, TRUE) . "</TD></TR>\n";	// 11/27/09
				}
			print "<TR CLASS='odd'><TD>&nbsp;</TD></TR>\n";	
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left'>";
	
			print "<FORM NAME='edit' METHOD='post' onSubmit='return validate(document.edit)' ACTION='{$this_file}?id=$id&action=update'>";	// 8/26/10
			print "<TABLE BORDER='0' ID='data'>\n";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=2><FONT CLASS='$theClass'><B>Edit Run Ticket</FONT> (#" . $id . ")</B></TD></TR>";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=2><FONT CLASS='header'><FONT SIZE='-2'>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>";
			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\" Incident Name - Use an easily identifiable name.\" >{$incident_name}</A>:</TD><TD><INPUT TYPE='text' NAME='frm_scope' SIZE='48' VALUE=\"{$row['scope']}\" MAXLENGTH='48'></TD></TR>\n"; 
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Priority - Normal, Medium or High. Affects order and coloring of Incidents on Situation display\">" . get_text("Priority") . "</A>:</TD><TD CLASS='$theClass'><SELECT NAME='frm_severity'>";		// 2/21/09
			$nsel = ($row['severity']==$GLOBALS['SEVERITY_NORMAL'])? "SELECTED" : "" ;
			$msel = ($row['severity']==$GLOBALS['SEVERITY_MEDIUM'])? "SELECTED" : "" ;
			$hsel = ($row['severity']==$GLOBALS['SEVERITY_HIGH'])? "SELECTED" : "" ;
			
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_NORMAL'] . "' $nsel>normal</OPTION>";
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_MEDIUM'] . "' $msel>medium</OPTION>";
			print "<OPTION VALUE='" . $GLOBALS['SEVERITY_HIGH'] . "' $hsel>high</OPTION>";
			print "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Nature or Type - Available types are set in in_types table in the configuration\">{$nature}</A>:</SPAN>\n";

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
			print "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Protocol - this will show automatically if a protocol is set for the Incident Type in the configuration\">" . get_text("Protocol") . "</A>:</SPAN></TD></TR>\n";

			print "<TR CLASS='even'><TD CLASS='td_label'></TD><TD ID='proto_cell'>{$row['protocol']}</TD></TR>\n";
			print "<TR CLASS='even'><TD COLSPAN='2'>&nbsp;</TD></TR>";
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Location - type in location in fields, click location on map or use *Located at Facility* menu below \">" . get_text("Location") . "</A>: </TD><TD><INPUT SIZE='48' TYPE='text'NAME='frm_street' VALUE=\"{$row['street']}\" MAXLENGTH='48'></TD></TR>\n";
			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"City - defaults to default city set in configuration. Type in City if required\">" . get_text("City") . "</A>:&nbsp;&nbsp;&nbsp;&nbsp;";
			print 		"</TD><TD><INPUT SIZE='32' TYPE='text' 	NAME='frm_city' VALUE=\"{$row['city']}\" MAXLENGTH='32' onChange = 'this.value=capWords(this.value)'>\n";
			$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10

			print 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF=\"#\" TITLE=\"State - US State or non-US Country code e.g. UK for United Kingdom\">St</A>:&nbsp;&nbsp;<INPUT SIZE='{$st_size}' TYPE='text' NAME='frm_state' VALUE='" . $row['state'] . "' MAXLENGTH='{$st_size}'></TD></TR>\n";

			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Caller reporting the incident\">" . get_text("Reported by") . "</A>:</TD><TD><INPUT SIZE='48' TYPE='text' 	NAME='frm_contact' VALUE=\"{$row['contact']}\" MAXLENGTH='48'></TD></TR>\n";
			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Phone number\">" . get_text("Phone") . "</A>:&nbsp;&nbsp;&nbsp;&nbsp;";
			print 		"</TD><TD><INPUT SIZE='48' TYPE='text' NAME='frm_phone' VALUE='" . $row['phone'] . "' MAXLENGTH='16'></TD></TR>\n";

			if (!(empty($row['phone']))) {					// 3/13/10
				$query  = "SELECT `miscellaneous` FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$row['phone']}' LIMIT 1";
				$result_cons = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if (mysql_affected_rows() > 0) {
					$row_cons = stripslashes_deep(mysql_fetch_array($result_cons));
					print "<TR CLASS='even'><TD CLASS='td_label'>Add'l:</TD><TD CLASS='td_label'>{$row_cons['miscellaneous']}</TD></TR>\n";		// 3/13/10
					}
				unset($result_cons);
				}				

			print "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD></TR>\n";

			$selO = ($row['status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
			$selC = ($row['status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
			$selP = ($row['status']==$GLOBALS['STATUS_SCHEDULED'])? "SELECTED" :"" ;

			$end_date = (!(empty($row['problemend'])))? $row['problemend']:  (time() - (get_variable('delta_mins')*60));		// 1/7/10
			$elapsed = my_date_diff($end_date, $row['problemstart']);

			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident Status - Open or Closed or set to Scheduled for future booked calls\">" . get_text("Status") . "</A>:</TD><TD>
				<SELECT NAME='frm_status'><OPTION VALUE='{$GLOBALS['STATUS_OPEN']}' $selO>Open</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_CLOSED'] . "'$selC>Closed</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_SCHEDULED'] . "'$selP>Scheduled</OPTION></SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$elapsed} </TD></TR>";
//			print "<TR CLASS='even'><TD CLASS='td_label'>Affected:</TD><TD><INPUT TYPE='text' SIZE='48' NAME='frm_affected' VALUE='" . $row['affected'] . "' MAXLENGTH='48'></TD></TR>\n";

//	facility handling  - 3/25/10

			if (!($row['facility'] == NULL)) {				// 9/22/09
	
				print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received\">Facility</A>: &nbsp;&nbsp;</TD>";		// 2/21/09
				$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
				$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				print "<TD><SELECT NAME='frm_facility_id' onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
				print "<OPTION VALUE=0>Not using facility</OPTION>";
	
				print "\n<SCRIPT>fac_lat[" . 0 . "] = " . get_variable('def_lat') . " ;</SCRIPT>\n";
				print "\n<SCRIPT>fac_lng[" . 0 . "] = " . get_variable('def_lng') . " ;</SCRIPT>\n";
	
				while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
					$sel = ($row['facility'] == $row_fc['id']) ? " SELECTED " : "";
					print "<OPTION VALUE=" . $row_fc['id'] . $sel . ">" . $row_fc['name'] . "</OPTION>";
					print "\n<SCRIPT>fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
					print "\n<SCRIPT>fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
					}
				print "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;\n";
				unset ($result_fc);
				} 				// end if (!($row['facility'] == NULL))
			else {	
				$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
				$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$pulldown = '<option>Incident at Facility?</option>';
				while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
					$pulldown .= "<option value=\"{$row_fc['id']}\">{$row_fc['name']}</option>\n";
					print "\n<SCRIPT>fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
					print "\n<SCRIPT>fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
					}
				unset ($result_fc);
				print "<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received\">Facility?</A>:</TD>";
				print "<TD><SELECT NAME='frm_facility_id' onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>$pulldown</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;\n";
				}
//	receiving facility - 3/25/10

			if (!($row['rec_facility'] == NULL)) {				// 10/1/09
				$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
				$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				print "<SELECT NAME='frm_rec_facility_id' onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>";
				print "<OPTION VALUE=0>No receiving facility</OPTION>";

				while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
					$sel2 = ($row['rec_facility'] == $row_rfc['id']) ? " SELECTED " : "";
					print "<OPTION VALUE=" . $row_rfc['id'] . $sel2 . ">" . $row_rfc['name'] . "</OPTION>";
					}
				print "</SELECT></TD></TR>\n";
				unset ($result_rfc);
				} 
			else {
				$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";			//10/1/09
				$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$pulldown2 = '<option>Receiving Facility?</option>';
					while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
						$pulldown2 .= "<option value=\"{$row_rfc['id']}\">{$row_rfc['name']}</option>\n";
					}
				unset ($result_rfc);
				print "<SELECT NAME='frm_rec_facility_id' onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>$pulldown2</SELECT></TD></TR>\n";	//10/1/09
				}

			print "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Synopsis - Details about the Incident, ensure as much detail as possible is completed\">" . get_text("Synopsis") . "</A>:</TD>";
			print 	"<TD CLASS='td_label'><TEXTAREA NAME='frm_description' COLS='45' ROWS='2' >" . $row['tick_descr'] . "</TEXTAREA></TD></TR>\n";		// 8/8/09

?>
<SCRIPT>
	function set_signal(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.edit.frm_description.value+=" " + temp_ary[1] + ' ';		
		document.edit.frm_description.focus();		
		}		// end function set_signal()

	function set_signal2(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.edit.frm_comments.value+=" " + temp_ary[1] + ' ';		
		document.edit.frm_comments.focus();		
		}		// end function set_signal()
</SCRIPT>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
			<TD></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>

<?php

			print "<TR CLASS='even'><TD CLASS='td_label'><A HREF='#' TITLE='911 contact information'>" . get_text("911 Contacted") . "</A>:&nbsp;</TD>
				<TD><INPUT SIZE='56' TYPE='text' NAME='frm_nine_one_one' VALUE='{$row['nine_one_one']}' MAXLENGTH='96' ></TD></TR>";

			if (good_date($row['booked_date'])) {	//10/1/09
				print "\n<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields.\">Scheduled Date</A>:</TD><TD>";
				generate_date_dropdown("booked_date",$row['booked_date']);
				print "</TD></TR>\n";
				}
			else {	//10/1/09
				print "\n<TR CLASS='odd' valign='middle'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields.\">" . get_text("Scheduled Date") . "</A>: &nbsp;&nbsp;<input type='radio' name='boo_but' onClick = 'do_booking(this.form);' /></TD><TD>";
				print "<SPAN style = 'visibility:hidden' ID = 'booked1'>";
				generate_date_dropdown('booked_date',0, TRUE);
				print "</TD></TR>\n";
				print "</SPAN></TD></TR>\n";
				}

				print "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD></TR>\n";			
			print "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"{$disposition} - additional comments about incident\">{$disposition}</A>:</TD>";				// 10/21/08, 8/8/09
			print 	"<TD><TEXTAREA NAME='frm_comments' COLS='45' ROWS='2' >" . $row['comments'] . "</TEXTAREA></TD></TR>\n";

?>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 12/18/10 -->
			<TD></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
<?php
			print "\n<TR CLASS='even'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Run-start, Incident start time. Edit by clicking padlock icon to enable date & time fields\">" . get_text("Run Start") . "</A>:</TD><TD>";
			print  generate_date_dropdown("problemstart",$row['problemstart'],0, TRUE);
			print "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'st_unlk(document.edit);'></TD></TR>\n";
			if (good_date($row['problemend'])) {
				print "\n<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields\">Run End</A>:</TD><TD>";
				generate_date_dropdown("problemend",$row['problemend']);
				print "</TD></TR>\n";
				}
			else {
				print "\n<TR CLASS='odd' valign='middle'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields\">" . get_text("Run End") . "</A>: &nbsp;&nbsp;<input type='radio' name='re_but' onClick ='do_end(this.form);' /></TD><TD>";
				print "<SPAN style = 'visibility:hidden' ID = 'runend1'>";
				generate_date_dropdown('problemend',0, TRUE);
				print "</TD></TR>\n";
				print "</SPAN></TD></TR>\n";
				}

			print "<TR CLASS='even'><TD CLASS='td_label' onClick = 'javascript: do_coords(document.edit.frm_lat.value ,document.edit.frm_lng.value  )'><U><A HREF=\"#\" TITLE=\"Position - Lat and Lng for Incident position. Click to show all position data.\">Position</A>:</U>:</TD><TD>";
			print "</TD></TR>\n";
			print "<TR CLASS='odd'><TD CLASS='td_label'><A HREF=\"#\" TITLE=\"Incident last updated - date & time.\">Updated</A>:</TD><TD>" . format_date($row['updated']) . "</TD></TR>\n";		// 10/21/08
			$lat = $row['lat']; $lng = $row['lng'];	
			print "<TR CLASS='even'><TD COLSPAN='2' ALIGN='center'><BR />";
			print ($mode==1)? 					// 9/8/10
				"<INPUT TYPE='button' VALUE='Cancel' onClick='window.close();'  STYLE = 'margin-left:20px' />":
				"<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'  STYLE = 'margin-left:20px' />";
			print "<INPUT TYPE='reset' VALUE='Reset' onclick= 'st_unlk_res(this.form); reset_end(this.form); resetmap($lat, $lng);'  STYLE = 'margin-left:20px' />";
			print "<INPUT TYPE='submit' VALUE='Submit' STYLE = 'margin-left:20px' />";
			print "</TD></TR>";
?>	
			
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $row['lat'];?>" />			<!-- // 8/9/08 -->
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $row['lng'];?>" />
			<INPUT TYPE="hidden" NAME="frm_status_default" VALUE="<?php print $row['status'];?>" />
			<INPUT TYPE="hidden" NAME="frm_affected_default" VALUE="<?php print $row['affected'];?>" />
			<INPUT TYPE="hidden" NAME="frm_scope_default" VALUE="<?php print $row['scope'];?>" />
			<INPUT TYPE="hidden" NAME="frm_owner_default" VALUE="<?php print $row['owner'];?>" />
			<INPUT TYPE="hidden" NAME="frm_severity_default" VALUE="<?php print $row['severity'];?>" />
			<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>" />
			<INPUT TYPE="hidden" NAME="frm_fac_chng" VALUE="0" />		<!-- 3/25/10 -->
			<INPUT TYPE="hidden" NAME="mode" VALUE="<?php print $mode;?>" />	<!-- 9/8/10 -->
<?php
			print "<TR CLASS='even'><TD COLSPAN='10' ALIGN='center'><BR /><B><U><A HREF=\"#\" TITLE=\"List of all actions and patients atached to this Incident\">Actions and Patients</A></U></B><BR /></TD></TR>";	//8/7/09
			print "<TR CLASS='odd'><TD COLSPAN='10' ALIGN='center'>";										//8/7/09
			print show_actions($row[0], "date", TRUE, TRUE, $mode);											// 9/8/10
			print "</TD></TR>";																//8/7/09
			print "</TABLE>";		// end data 8/7/09
			print "</TD></TR>";																//8/7/09
			print "<TR><TD CLASS='print_TD' COLSPAN='2'>";
			print "</FORM>";
			print "</TD></TR></TABLE>";		// bottom of outer
?>
	<SCRIPT type="text/javascript">
	
<?php
		$in_strike = 	($row['status']== $GLOBALS['STATUS_CLOSED'])? "<strike>": "";							// 11/7/08
		$in_strikend = 	($row['status']== $GLOBALS['STATUS_CLOSED'])? "</strike>": "";
		$street = empty($row['street'])? "" : "<BR/>" . $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
?>

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
