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
6/4/2013 broadcast() added

*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Edit Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT SRC="./js/usng.js" TYPE="application/x-javascript"></SCRIPT>		<!-- 8/23/08 -->
<?php
	
@session_start();
session_write_close();
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
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$disallow = ((is_guest()) || ((is_user()) && (intval(get_variable('oper_can_edit')) != 1))) ;
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

	if (!($mode==1)) {											// 9/8/10
		add_header($id);
		}
	$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
	@session_start();
	unset ($_SESSION['active_ticket']);								// 5/4/11
	session_write_close();
	return($addrs);	//	11/18/13
	}				// end function edit ticket() 

?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Edit Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
	<SCRIPT SRC="./js/usng.js" TYPE="application/x-javascript"></SCRIPT>		<!-- 8/23/08 -->
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
<?php		/* 6/4/2013  */
		if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 		// 7/2/2013
?>
			var theMessage = "Updated  <?php print get_text('Incident');?> (" + theForm.frm_scope.value + ") by <?php echo $_SESSION['user'];?>";
			broadcast(theMessage ) ;
<?php
	}			// end if (broadcast)
?>				

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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css" /> <!-- 3/15/11 -->
	<BODY onLoad = "parent.frames['upper'].show_msg ('Edit applied!'); document.go_Form.submit();">
		<FORM NAME='go_Form' METHOD = 'post' ACTION="main.php">
		</FORM>	
		</BODY></HTML>
<?php
		}
		
?>		
<BODY onLoad = "ck_frames()" >
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<?php
require_once('./incs/links.inc.php');
 
	$id = $_GET['id'];
	if ((isset($_GET['action'])) && ($_GET['action'] == 'update')) {		/* update ticket */
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id' LIMIT 1")) {
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT>";
			} else {
			$the_addrs = edit_ticket($id);	// post updated data	11/18/13
			if ($addrs) {
				$theTo = implode("|", array_unique($addrs));
				$theText = "TICKET-Update: " . $_POST['frm_scope'];
				mail_it ($theTo, "", $theText, $id, 1 );
				}				// end if ($addrs)

			if($_SESSION['internet'] || ($mode == 1)) {
				if($mode == 0) {
					require_once('./forms/ticket_view_screen_NM.php');
					} else {
					$now = time() - (intval(get_variable('delta_mins')*60));		// 6/20/10
					$ticket_name = $_POST['frm_scope'];
					print "<BR /><BR /><BR /><CENTER><FONT CLASS='header'>Ticket: " . $ticket_name . " Updated </FONT></CENTER><BR /><BR />";
?>
					<CENTER><SPAN id='cont_but' class='plain' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><?php print get_text('Finish');?></SPAN></CENTER>
<?php
					}
				}
			}
		exit();
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
		} else {				// not ($_GET['delete'])
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {		/* sanity check */
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT><BR />";
			} else {				// OK, do form - 7/7/09
 			$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend, UNIX_TIMESTAMP(booked_date) AS booked_date, UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, 
 				`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` FROM `$GLOBALS[mysql_prefix]ticket` 
 				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)				
 				WHERE `$GLOBALS[mysql_prefix]ticket`.`id`='$id' LIMIT 1";

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
			if($mode == 1) {
?>
				<DIV id='button_bar' class='but_container'>
					<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>Edit <?php print get_text('Incident');?></SPAN>
					<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
					<SPAN id='reset_but' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="st_unlk_res(document.edit); reset_end(document.edit);"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN id='sub_but_but' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.edit.submit();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				</DIV>
				<DIV ID='leftcol' STYLE="position: relative; top: 70px; width: 90%;">
<?php
				} else {
?>
				<DIV ID='leftcol' STYLE='position: relative; top: 0px;'>
<?php
				}
?>
				<TABLE BORDER='0' ID='outer' ALIGN='left' CLASS = 'BGCOLOR' STYLE = 'margin-left:5px; width: 90%;'>
<?php
					if($mode != 1) {
?>
						<TR CLASS='odd'><TD ALIGN='left'><?php print add_header($id, TRUE);?></TD></TR>
<?php
						}
?>
					<TR CLASS='even' valign='top'>
						<TD ALIGN='left'>
							<FORM NAME='edit' METHOD='post' onSubmit='return validate(document.edit)' ACTION='<?php print $this_file;?>?id=<?php print $id;?>&action=update'>
							<TABLE STYLE='width: 95%;' ID='data'>
								<TR CLASS='odd'>
									<TD COLSPAN='3'>&nbsp;</TD>
								</TR>
								<TR CLASS='odd'>
									<TD ALIGN='center' COLSPAN='3'>
										<SPAN CLASS='text_green text_biggest'><FONT CLASS='$theClass'><B>Edit Run Ticket</FONT> (#<?php print $id;?>)</SPAN>
										<BR />
										<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
										<BR />
									</TD>
								</TR>
								<TR CLASS='spacer'>
									<TD CLASS='spacer' COLSPAN='3' ALIGN='center'></TD>
								</TR>
								<TR CLASS='even'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE=" Incident Name - Use an easily identifiable name." ><?php print $incident_name;?></A>:
									</TD>
									<TD CLASS='td_data text'>
										<INPUT TYPE='text' NAME='frm_scope' SIZE='48' VALUE="<?php print $row['scope'];?>" MAXLENGTH='48' />
									</TD>
								</TR>
								<TR CLASS='odd'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Incident Priority - Normal, Medium or High. Affects order and coloring of Incidents on Situation display"><?php print get_text("Priority");?></A>:
									</TD>
									<TD CLASS='<?php print $theClass;?> td_data text'>
										<SELECT NAME='frm_severity'>
<?php
										$nsel = ($row['severity']==$GLOBALS['SEVERITY_NORMAL'])? "SELECTED" : "" ;
										$msel = ($row['severity']==$GLOBALS['SEVERITY_MEDIUM'])? "SELECTED" : "" ;
										$hsel = ($row['severity']==$GLOBALS['SEVERITY_HIGH'])? "SELECTED" : "" ;
?>				
											<OPTION VALUE='<?php print $GLOBALS['SEVERITY_NORMAL'];?>' <?php print $nsel;?>>normal</OPTION>
											<OPTION VALUE='<?php print $GLOBALS['SEVERITY_MEDIUM'];?>' <?php print $msel;?>>medium</OPTION>
											<OPTION VALUE='<?php print $GLOBALS['SEVERITY_HIGH'];?>' <?php print $hsel;?>>high</OPTION>
										</SELECT>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<SPAN CLASS='td_label text'>
											<A HREF="#" TITLE="Incident Nature or Type - Available types are set in in_types table in the configuration"><?php print $nature;?></A>:
										</SPAN>
										<SELECT NAME='frm_in_types_id' onChange='do_inc_nature(this.options[selectedIndex].value.trim());'>
<?php
											$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
											$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
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
													} else {
													$sel = "";
													}			
												print "<OPTION VALUE=" . $row2['id'] . $sel . ">" . $row2['type'] . "</OPTION>";
												if (!(empty($row2['protocol']))) {				// 7/7/09 - note string key
													print "\n<SCRIPT>protocols[{$row2['id']}] = \"{$row2['protocol']}\";</SCRIPT>\n";
													}
												$i++;
												}
											unset ($result);
?>
											</OPTGROUP>
										</SELECT>
									</TD>
								</TR>
								<TR CLASS='odd'>
									<TD CLASS='td_label text'>
										<SPAN CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Incident Protocol - this will show automatically if a protocol is set for the Incident Type in the configuration"><?php print get_text("Protocol");?></A>:
										</SPAN>
									</TD>
									<TD ID='proto_cell' CLASS='td_data text'><?php print $row['protocol'];?></TD>
								</TR>
								<TR CLASS='even'>
									<TD COLSPAN='2'>&nbsp;</TD>
								</TR>
								<TR CLASS='odd'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Location - type in location in fields, click location on map or use *Located at Facility* menu below"><?php print get_text("Location");?></A>: 
									</TD>
									<TD CLASS='td_data text'>
										<INPUT SIZE='48' TYPE='text'NAME='frm_street' VALUE="<?php print $row['street'];?>" MAXLENGTH='48' />
									</TD>
								</TR>
								<TR CLASS='even'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City");?></A>:&nbsp;&nbsp;&nbsp;&nbsp;
									</TD>
									<TD CLASS='td_data text'>
										<INPUT SIZE='32' TYPE='text' NAME='frm_city' VALUE="<?php print $row['city'];?>" MAXLENGTH='32' onChange = 'this.value=capWords(this.value)' />
<?php
										$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10
?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:
										&nbsp;&nbsp;<INPUT SIZE='<?php print $st_size;?>' TYPE='text' NAME='frm_state' VALUE='<?php print $row['state'];?>' MAXLENGTH='<?php print $st_size;?>'>
									</TD>
								</TR>
								<TR CLASS='odd'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Caller reporting the incident"><?php print get_text("Reported by");?></A>:
									</TD>
									<TD CLASS='td_data text'>
										<INPUT SIZE='48' TYPE='text' NAME='frm_contact' VALUE="<?php print $row['contact'];?>" MAXLENGTH='48' />
									</TD>
								</TR>
								<TR CLASS='even'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Phone number"><?php print get_text("Phone");?></A>:&nbsp;&nbsp;&nbsp;&nbsp;
									</TD>
									<TD CLASS='td_data text'>
										<INPUT SIZE='48' TYPE='text' NAME='frm_phone' VALUE='<?php print $row['phone'];?>' MAXLENGTH='16' />
									</TD>
								</TR>
<?php
								if (!(empty($row['phone']))) {					// 3/13/10
									$query  = "SELECT `miscellaneous` FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$row['phone']}' LIMIT 1";
									$result_cons = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
									if (mysql_affected_rows() > 0) {
										$row_cons = stripslashes_deep(mysql_fetch_array($result_cons));
?>
										<TR CLASS='even'>
											<TD CLASS='td_label text'>Add'l:</TD>
											<TD CLASS='td_data text'><?php print $row_cons['miscellaneous'];?></TD>
										</TR>
<?php
										}
									unset($result_cons);
									}				
?>
								<TR CLASS='even'>
									<TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD>
								</TR>
<?php
								$selO = ($row['status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
								$selC = ($row['status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
								$selP = ($row['status']==$GLOBALS['STATUS_SCHEDULED'])? "SELECTED" :"" ;

								$end_date = (!(empty($row['problemend'])))? $row['problemend']:  (time() - (get_variable('delta_mins')*60));
								$elapsed = my_date_diff($end_date, $row['problemstart']);
?>
								<TR CLASS='odd'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Incident Status - Open or Closed or set to Scheduled for future booked calls"><?php print get_text("Status");?></A>:
									</TD>
									<TD CLASS='td_data text'>
										<SELECT NAME='frm_status'>
											<OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' <?php print $selO;?>>Open</OPTION>
											<OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>' <?php print $selC;?>>Closed</OPTION>
											<OPTION VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' <?php print $selP;?>>Scheduled</OPTION>
										</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $elapsed;?> 
									</TD>
								</TR>
<?php
//	facility handling  - 3/25/10

								if (!($row['facility'] == NULL)) {				// 9/22/09
?>		
									<TR CLASS='even'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received">Facility</A>: &nbsp;&nbsp;
										</TD>
										<TD CLASS='td_data text'>
											<SELECT NAME='frm_facility_id' onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>
												<OPTION VALUE=0>Not using facility</OPTION>
<?php
												$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
												$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
?>
		
<SCRIPT>
												fac_lat[0] = "<?php print get_variable('def_lat');?>";
												fac_lng[0] = "<?php print get_variable('def_lng');?>";
</SCRIPT>
<?php	
												while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
													$sel = ($row['facility'] == $row_fc['id']) ? " SELECTED " : "";
													print "<OPTION VALUE=" . $row_fc['id'] . $sel . ">" . $row_fc['name'] . "</OPTION>";
													print "\n<SCRIPT>fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
													print "\n<SCRIPT>fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
													}
												unset ($result_fc);
?>

											</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;
										</TD>
									</TR>
<?php
									} else {
?>
									<TR CLASS='even'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received">Facility?</A>:
										</TD>
										<TD CLASS='td_data text'>
											<SELECT NAME='frm_facility_id' onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>
												<OPTION>Incident at Facility?</OPTION>
<?php
												$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
												$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
												while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
													print "<OPTION value=\"{$row_fc['id']}\">{$row_fc['name']}</OPTION>\n";
													print "\n<SCRIPT>fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
													print "\n<SCRIPT>fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
													}
												unset ($result_fc);
?>
											</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;
										</TD>
									</TR>
<?php
									}
						
//	receiving facility - 3/25/10

								if (!($row['rec_facility'] == NULL)) {
?>		
									<TR CLASS='even'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received">Receiving Facility</A>: &nbsp;&nbsp;
										</TD>
										<TD CLASS='td_data text'>
											<SELECT NAME='frm_rec_facility_id' onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>
												<OPTION VALUE=0>No receiving facility</OPTION>
<?php
												$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
												$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
												while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
													$sel2 = ($row['rec_facility'] == $row_rfc['id']) ? " SELECTED " : "";
													print "<OPTION VALUE=" . $row_rfc['id'] . $sel2 . ">" . $row_rfc['name'] . "</OPTION>";
													}
												unset ($result_rfc);
?>
											</SELECT>
										</TD>
									</TR>
<?php
									} else {
?>
									<TR CLASS='even'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received">Receiving Facility</A>: &nbsp;&nbsp;
										</TD>
										<TD CLASS='td_data text'>
											<SELECT NAME='frm_rec_facility_id' onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>
												<OPTION>Receiving Facility?</OPTION>
<?php
												$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";			//10/1/09
												$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
												while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
													print "<option value=\"{$row_rfc['id']}\">{$row_rfc['name']}</option>\n";
													}
												unset ($result_rfc);
?>
											</SELECT>
										</TD>
									</TR>
<?php
									}
?>
								<TR CLASS='odd' VALIGN='top'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Synopsis - Details about the Incident, ensure as much detail as possible is completed"><?php print get_text("Synopsis");?></A>:
									</TD>
									<TD CLASS='td_data text'>
										<TEXTAREA NAME='frm_description' COLS='45' ROWS='2' ><?php print $row['tick_descr'];?></TEXTAREA>
									</TD>
								</TR>
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
								<TR VALIGN = 'TOP' CLASS='odd'>
									<TD CLASS='td_label text'>Signal &raquo;</TD>
									<TD CLASS='td_data text'> 
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
									</TD>
								</TR>
								<TR CLASS='even'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF='#' TITLE='911 contact information'><?php print get_text("911 Contacted");?></A>:&nbsp;
									</TD>
									<TD CLASS='td_data text'>
										<INPUT SIZE='56' TYPE='text' NAME='frm_nine_one_one' VALUE='{$row['nine_one_one']}' MAXLENGTH='96' />
									</TD>
								</TR>
<?php
								if (good_date($row['booked_date'])) {
?>
									<TR CLASS='odd'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields.">Scheduled Date</A>:
										</TD>
										<TD>
											<?php print generate_date_dropdown("booked_date",$row['booked_date']);?>
										</TD>
									</TR>
<?php
									} else {
?>
									<TR CLASS='odd' valign='middle'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields."><?php print get_text("Scheduled Date");?></A>: &nbsp;&nbsp;
											<input type='radio' name='boo_but' onClick = 'do_booking(this.form);' />
										</TD>
										<TD CLASS='td_data text'>
											<SPAN style = 'visibility:hidden' ID = 'booked1'>
												<?php print generate_date_dropdown('booked_date',0, TRUE);?>
											</SPAN>
										</TD>
									</TR>
<?php
									}
?>
								<TR CLASS='odd'>
									<TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' />
									</TD>
								</TR>			
								<TR CLASS='odd' VALIGN='top'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="<?php print $disposition;?> - additional comments about incident"><?php print $disposition;?></A>:
									</TD>
									<TD CLASS='td_data text'>
										<TEXTAREA NAME='frm_comments' COLS='45' ROWS='2' ><?php print $row['comments'];?></TEXTAREA>
									</TD>
								</TR>
								<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 12/18/10 -->
									<TD CLASS='td_label text'>Signal &raquo;
									</TD>
									<TD CLASS='td_data text'>
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
									</TD>
								</TR>
								<TR CLASS='even'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Run-start, Incident start time. Edit by clicking padlock icon to enable date & time fields"><?php print get_text("Run Start");?></A>:
									</TD>
									<TD CLASS='td_data text'>
										<?php print generate_date_dropdown("problemstart",$row['problemstart'],0, TRUE);?>
										&nbsp;&nbsp;&nbsp;&nbsp;
										<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'st_unlk(document.edit);'>
									</TD>
								</TR>
<?php
								if (good_date($row['problemend'])) {
?>
									<TR CLASS='odd'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields">Run End</A>:
										</TD>
										<TD CLASS='td_data text'>
											<?php print generate_date_dropdown("problemend",$row['problemend']);?>
										</TD>
									</TR>
<?php
									} else {
?>
									<TR CLASS='odd' valign='middle'>
										<TD CLASS='td_label text'>
											<A CLASS='td_label text' HREF="#" TITLE="Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields"><?php print get_text("Run End");?></A>: &nbsp;&nbsp;
											<input type='radio' name='re_but' onClick ='do_end(this.form);' />
										</TD>
										<TD CLASS='td_data text'>
											<SPAN style = 'visibility:hidden' ID = 'runend1'>
												<?php print generate_date_dropdown('problemend',0, TRUE);?>
											</SPAN>
										</TD>
									</TR>
<?php
									}
?>
								<TR CLASS='even'>
									<TD CLASS='td_label text' onClick = 'javascript: do_coords(document.edit.frm_lat.value ,document.edit.frm_lng.value  )'>
										<U><A CLASS='td_label text' HREF="#" TITLE="Position - Lat and Lng for Incident position. Click to show all position data.">Position</A>:</U>:
									</TD>
									<TD>&nbsp;
									</TD>
								</TR>
								<TR CLASS='odd'>
									<TD CLASS='td_label text'>
										<A CLASS='td_label text' HREF="#" TITLE="Incident last updated - date & time.">Updated</A>:
									</TD>
									<TD CLASS='td_data text'><?php print format_date($row['updated']);?></TD>
								</TR>
<?php
								$lat = $row['lat']; $lng = $row['lng'];	
?>
								<TR CLASS='even'>
									<TD CLASS='td_data text text_center' COLSPAN='2'><BR />
<?php
										if($mode != 1) {
?>
											<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='history.back();'><?php print get_text('Cancel'); ?></SPAN>
											<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="st_unlk_res(this.form); reset_end(this.form); resetmap(<?php print $lat;?>, <?php print $lng;?>);"><?php print get_text("Reset"); ?></SPAN>
											<SPAN id='sub_but_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.edit.submit();;"><?php print get_text("Submit"); ?></SPAN>
<?php
											}
?>
									</TD>
								</TR>
								<TR CLASS='odd'>
									<TD COLSPAN=2 ALIGN='center'><?php print show_actions($row[0], "date", !$disallow, TRUE, 0);?></TD>
								</TR>
								<TR CLASS='odd'>
									<TD COLSPAN=2 ALIGN='center'><?php print show_log($row[0]);?></TD>
								</TR>
								<TR CLASS='odd'>
									<TD COLSPAN=2 ALIGN='center'>&nbsp;</TD>
								</TR>	
							</TABLE>
						<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $row['lat'];?>" />			<!-- // 8/9/08 -->
						<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $row['lng'];?>" />
						<INPUT TYPE="hidden" NAME="frm_status_default" VALUE="<?php print $row['status'];?>" />
						<INPUT TYPE="hidden" NAME="frm_affected_default" VALUE="<?php print $row['affected'];?>" />
						<INPUT TYPE="hidden" NAME="frm_scope_default" VALUE="<?php print $row['scope'];?>" />
						<INPUT TYPE="hidden" NAME="frm_owner_default" VALUE="<?php print $row['owner'];?>" />
						<INPUT TYPE="hidden" NAME="frm_severity_default" VALUE="<?php print $row['severity'];?>" />
						<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>" />
						<INPUT TYPE="hidden" NAME="frm_fac_chng" VALUE="0" />		<!-- 3/25/10 -->
						<INPUT TYPE="hidden" NAME="mode" VALUE="<?php print $mode;?>" />
						</FORM>
						</TD>
					</TR>
					<TR>
						<TD CLASS='print_TD' COLSPAN='2'></TD>
					</TR>

				</TABLE>
<?php
				$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
				print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, $id, 0, 0, 0);
?>
<SCRIPT type="application/x-javascript">
	
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
						} else {
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
					
				var inWin = <?php print $mode;?>;
					
				if (typeof window.innerWidth != 'undefined') {
					viewportwidth = window.innerWidth,
					viewportheight = window.innerHeight
					} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
					viewportwidth = document.documentElement.clientWidth,
					viewportheight = document.documentElement.clientHeight
					} else {
					viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
					viewportheight = document.getElementsByTagName('body')[0].clientHeight
					}
				if(inWin == 1) {
					set_fontsizes(viewportwidth, "popup");	
					} else {
					set_fontsizes(viewportwidth, "fullscreen");	
					}
				mapWidth = viewportwidth * .40;
				mapHeight = viewportheight * .55;
				outerwidth = viewportwidth * .99;
				outerheight = viewportheight * .95;
				colwidth = outerwidth * .45;
				colheight = outerheight * .95;
				if(inWin != 1) {
					leftcolwidth = viewportwidth * .70;
					rightcolwidth = viewportwidth * .10;
					} else {
					leftcolwidth = rightcolwidth = colwidth;
					}
				if($('outer')) {$('outer').style.width = outerwidth + "px";}
				if($('outer')) {$('outer').style.height = outerheight + "px";}
				if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
				if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
				if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
				if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
</SCRIPT>
				</DIV>
<?php
			}			// end  sanity check 
		}
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['id'];?>">
</FORM>	

</BODY>
</HTML>
