<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
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
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
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
3/28/12 Corrected to errors with Region display.
12/10/2012 - fix to ajax calls re message format
11/22/2012 Added Nearby Functionality
1/17/2013 GMaps V3 conversions made 
5/22/2013 added broadcast call
6/2/2013 reverse_geo operation added.
7/3/2013 - socket2me conditioned on internet and broadcast settings, reverse geo field size limits corrected
9/10/13 - Added "Address About" and "To Address" fields and File storage
11/18/13 - Fix for notifies on edit.
*/
	$addrs = FALSE;										// notifies address array doesn't exist
	
	function edit_ticket($id) {							/* post changes */
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $id;
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$theScope = $row['scope'];
		unset($result);
		global $addrs, $NOTIFY_TICKET;	//	8/28/13

		$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
		$post_frm_meridiem_booked_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_booked_date'])))) ) ? "" : $_POST['frm_meridiem_booked_date'] ;	//10/1/09
		$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;

		if(empty($_POST['frm_scope'])) {	//	catch for refresh of tickets view screen.
			add_header($id, FALSE, TRUE);
			print '<FONT CLASS="header">Ticket <I>' . $theScope . '</I> has not changed</FONT><BR /><BR />';
			require_once('./forms/ticket_view_screen.php');
			exit();
			} else {
			$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
			$post_frm_affected 	 		= strip_html($post_frm_affected);
			$_POST['frm_scope']			= strip_html($_POST['frm_scope']);

/*			if (get_variable('reporting')) {		// if any change do automatic action reporting
		
//				if ($_POST[frm_affected] != $_POST[frm_affected_default]) report_action($GLOBALS[ACTION_AFFECTED],$_POST[frm_affected],0,$id);
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

//				if ($_POST['frm_meridiem_problemend'] == 'pm') 	$_POST['frm_hour_problemend'] 	= ($_POST['frm_hour_problemend'] + 12) % 24;
				}

			if(empty($post_frm_owner)) {$post_frm_owner=0;}
			$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";

			$curr_groups = $_POST['frm_exist_groups']; 	//	6/10/11
			$groups = isset($_POST['frm_group']) ? ", " . implode(',', $_POST['frm_group']) . "," : $_POST['frm_exist_groups'];	//	3/28/12 - fixes error when accessed from view ticket screen..	
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
			$portal_user = 		empty($_POST['frm_portal_user'])?	NULL:  trim($_POST['frm_portal_user']);				// 9/10/13
			if($_POST['frm_status'] != 1) {
				$frm_problemend = "NULL";
				}
		
			// perform db update
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			$datetime = format_date_2(strtotime($now));
			$by = $_SESSION['user_id'];			// 12/7/10
			if(empty($post_frm_owner)) {$post_frm_owner=0;}
			if(!empty($_POST['frm_comments'])) {
				if($_POST['frm_notes'] == "") {
					$disp = $datetime . ": " . $_POST['frm_comments'];					
					} else {
					$disp = $_POST['frm_notes'] . "<BR />" . $datetime . ": " . $_POST['frm_comments'];
					}
				} else {
				$disp = $_POST['frm_notes'];
				}
									// 8/23/08, 9/20/08, 9/22/09 (Facility), 10/1/09 (receiving facility), 6/26/10 (911), 6/10/11
			$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
				`portal_user`= " .  quote_smart(trim($_POST['frm_portal_user'])) .",
				`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
				`address_about`= " . 		quote_smart(trim($_POST['frm_address_about'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
				`to_address`= " . 		quote_smart(trim($_POST['frm_to_address'])) . ",
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
				`comments`= " . 	quote_smart(trim($disp)) .",
				`nine_one_one`= " . quote_smart(trim($_POST['frm_nine_one_one'])) .",
				`booked_date`= 		{$frm_booked_date},
				`_by` = 			{$by}, 
				`updated`='{$now}'
				WHERE ID='$id'";

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			if(quote_smart(trim($_POST['frm_status'])) == "1" || quote_smart(trim($_POST['frm_status'])) == "0") {
				$al_status = 0;
				} else {
				$al_status = 1;				
				}
//	If portal user is set, insert an associated request if one does not already exist for this Ticket	9/10/13		

			$where = $_SERVER['REMOTE_ADDR'];		//	9/10/13	
			if(($portal_user != NULL) && ($portal_user != 0)) {		//	9/10/13	
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `ticket_id` = " . $id;		//	9/10/13	
				$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);			//	9/10/13	
				if(mysql_affected_rows() == 0) {		//	9/10/13	
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
					`org`,
					`contact`, 
					`street`, 
					`city`, 
					`state`, 
					`the_name`, 
					`phone`, 
					`to_address`,
					`orig_facility`,
					`rec_facility`, 
					`scope`, 
					`description`, 
					`comments`, 
					`lat`,
					`lng`,
					`request_date`, 
					`status`, 
					`accepted_date`,
					`declined_date`, 
					`resourced_date`, 
					`completed_date`, 
					`closed`, 
					`requester`, 
					`ticket_id`,
					`_by`, 
					`_on`, 
					`_from` 
					) VALUES (
					" . 0 . ",
					'" . get_owner($_POST['frm_portal_user']) . "',
					" . quote_smart(trim($_POST['frm_street'])) . ",	
					" . quote_smart(trim($_POST['frm_city'])) . ",	
					" . quote_smart(trim($_POST['frm_state'])) . ",	
					" . quote_smart(trim($_POST['frm_contact'])) . ",
					" . quote_smart(trim($_POST['frm_phone'])) . ",
					" . quote_smart(trim($_POST['frm_to_address'])) . ",	
					" . quote_smart(trim($_POST['frm_facility_id'])) . ",					
					" . quote_smart(trim($_POST['frm_rec_facility_id'])) . ",	
					" . quote_smart(trim($_POST['frm_scope'])) . ",	
					" . quote_smart(trim($_POST['frm_description'])) . ",					
					" . quote_smart(trim($_POST['frm_comments'])) . ",		
					" . quote_smart(trim($_POST['frm_lat'])) . ",		
					" . quote_smart(trim($_POST['frm_lng'])) . ",				
					" . quote_smart(trim($frm_problemstart)) . ",
					'Accepted',
					'" . $now . "',
					NULL,
					NULL,
					NULL,
					NULL,
					" . $portal_user . ",
						" . $id . ",	
					" . $_SESSION['user_id'] . ",				
					'" . $now . "',
					'" . $where . "')";
					$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);		//	9/10/13	
				}
			}
		
//	end of insert request associated with Ticket

//	9/10/13 File Upload support
			$print = "";
			if ((isset($_FILES['frm_file'])) && ($_FILES['frm_file']['name'] != "")){
				$nogoodFile = false;	
				$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py"); 
				foreach ($blacklist as $file) { 
					if(preg_match("/$file\$/i", $_FILES['frm_file']['name'])) { 
						$nogoodFile = true;
						}
					}
				if(!$nogoodFile) {
					$exists = false;
					$existing_file = "";
					$upload_directory = "./files/";
					if (!(file_exists($upload_directory))) {				
						mkdir ($upload_directory, 0770);
						}
					chmod($upload_directory, 0770);	
					$filename = rand(1,999999);
					$realfilename = $_FILES["frm_file"]["name"];
					$file = $upload_directory . $filename;
				
//	Does the file already exist in the files table		

					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `orig_filename` = '" . $realfilename . "'";
					$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);	
					if(mysql_affected_rows() == 0) {	//	file doesn't exist already
						if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
							if (strlen(filesize($file)) < 20000000) {
								$print .= "";
								} else {
								$print .= "Attached file is too large!";
								}
							} else {
							$print .= "Error uploading file";
							}
						} else {
						$row = stripslashes_deep(mysql_fetch_assoc($result));			
						$exists = true;
						$existing_file = $row['filename'];	//	get existing file name
						}
						
					$from = $_SERVER['REMOTE_ADDR'];	
					$filename = ($existing_file == "") ? $filename : $existing_file;	//	if existing file, use this file and write new db entry with it.
					$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]files` (
							`title` , `filename` , `orig_filename`, `ticket_id` , `responder_id` , `facility_id`, `type`, `filetype`, `_by`, `_on`, `_from`
						) VALUES (
							'" . $_POST['frm_file_title'] . "', '" . $filename . "', '" . $realfilename . "', " . $id . ", 0,
							0, 0, '" . $_FILES['frm_file']['type'] . "', $by, '" . $now . "', '" . $from . "'
						)";
					$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
					if($result_insert) {	//	is the database insert successful
						$dbUpdated = true;
						} else {	//	problem with the database insert
						$dbUpdated = false;				
						}
					}
				} else {	// Problem with the file upload
				$fileUploaded = false;
				}	

//	End of file upload				


			$list = $_POST['frm_exist_groups']; 	//	6/10/11
			$ex_grps = explode(',', $list); 	//	6/10/11
			
			if($al_status == 0) {
				$query  = "UPDATE `$GLOBALS[mysql_prefix]allocates` SET `al_status` = 0, `al_as_of` = '{$now}' WHERE `type` = 1 AND `resource_id` = '$id'";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				}
			
			$tick_stat = $al_status;	//	set allocates status to 1 if open or scheduled, 0 if ticket closed or reserved
			
			if($curr_groups != $groups) { 	//	6/10/11
				foreach($_POST['frm_group'] as $posted_grp) { 	//	6/10/11
					if(!(in_array($posted_grp, $ex_grps))) {
						$tick_stat = $al_status;
						$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
								($posted_grp, 1, '$now', $tick_stat, $id, 'Allocated to Group' , $by)";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				foreach($ex_grps as $existing_grp) { 	//	6/10/11
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
//					dump($_POST['frm_fac_chng']);
					print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
				}			// end switch ()

			add_header($id, FALSE, TRUE);
			print '<FONT CLASS="header">Ticket <I>' . $_POST['frm_scope'] . '</I> has been updated</FONT><BR /><BR />';		/* show updated ticket */
			$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
			unset ($_SESSION['active_ticket']);								// 5/4/11
			return($addrs);	//	11/18/13
			}
		}				// end function edit ticket() 

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
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE TYPE="text/css">
	#suggest{background:#fff; width:150px;	}
	#suggest div{ background:#ddd; color:#000; padding-left:4px; cursor:hand; text-align:left;position:relative;	}
	#suggest div.over{ color:#000; background:#fff;	}
	.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
	table.cruises td {overflow: hidden; }
	div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
	div.scrollableContainer2 { position: relative; padding-top: 2em; }
	div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
	div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
	table.scrollable thead tr { left: -1px; top: 0; position: absolute; }
	table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
	.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
	.box {background-color: transparent; border: none; color: #000000; padding: 0px; position: absolute;}
	.bar {background-color: #DEE3E7; color: transparent; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;}
	.content {padding: 1em;}
	</STYLE>	
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT SRC="./js/suggest.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script src="./js/leaflet/KML.js"></script>
<script src="./js/leaflet/gpx.js"></script>  
<script src="./js/leaflet-openweathermap.js"></script>
<script src="./js/esri-leaflet.js"></script>
<script src="./js/osopenspace.js"></script>
<script src="./js/Control.Geocoder.js"></script>
<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script src="./js/Google.js"></script>
<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC='./js/jscoord.js' TYPE="text/javascript"></SCRIPT>			<!-- coordinate conversion 12/10/10 -->	
<SCRIPT SRC="./js/lat_lng.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/geotools2.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/osgb.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->	
<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
<script type="text/javascript" src="./js/L.Graticule.js"></script>
<script type="text/javascript" src="./js/leaflet-providers.js"></script>
<SCRIPT>
window.onresize=function(){set_size();}

window.onload = function(){set_size();}

var layercontrol;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
var inc_sortby = "tick_id";		//	options tick_id, scope, ticket_street, type, updated
var inc_sortdir = "ASC";		// Initial sort direction ascending
var inc_sortbyfield = "";
var inc_sortvalue = "";
var inc_period = 0;
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
			
/* Initial period selection - current tickets, 
	options available 0 (current tickets), 
	1 - Closed today
	2 - Closed Yesterday+
	3 - Closed this week
	4 - Closed last week
	5 - Closed last week+
	6 - Closed this month
	7 - Closed last month
	8 - Closed this year
	9 - Closed last year
*/
var colors = new Array ('odd', 'even');

function set_size() {
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
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95;
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	load_regions();
	load_files();
	get_mainmessages();
	update_regions_text();
	}
	
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
	
	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08

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
			theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits
								
<?php				/* 5/22/2013 */
		if ((intval(get_variable('broadcast') == 1)) &&  ($_SESSION['good_internet'])) { 		// 7/2/2013
?>
			var theMessage = "Updated  <?php print get_text('Incident');?> (" + theForm.frm_scope.value + ") by <?php echo $_SESSION['user'];?>";
			broadcast(theMessage ) ;
<?php
	}			// end if (broadcast)
?>				
			theForm.submit();
			}
		}				// end function validate(theForm)

	function do_fac_to_loc(text, index){													// 9/22/09
		var curr_lat = fac_lat[index];
		var curr_lng = fac_lng[index];
		do_lat(curr_lat);
		do_lng(curr_lng);
		pt_to_map (document.edit, curr_lat, curr_lng)
		find_warnings(curr_lat, curr_lng);	//	9/10/13
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
	
	function file_window(id) {										// 9/10/13
		var url = "file_upload.php?ticket_id="+ id;
		var nfWindow = window.open(url, 'NewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { nfWindow.focus(); }, 1);
		}
		
	function get_files() {										// 9/10/13
		$('the_file_list').innerHTML = "Please Wait, loading files";
		randomnumber=Math.floor(Math.random()*99999999);
		var url ="./ajax/file_list.php?ticket_id=<?php print $_GET['id'];?>&version=" + randomnumber;
		theRequest (url, filelist_cb, "");	//	11/14/13
		function filelist_cb(req) {
			var theFiles=req.responseText;
			$('the_file_list').innerHTML = theFiles;		
			}
		}
		
	function theRequest(url,callback,postData) {	// 9/10/13, 11/14/13
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

	function createXMLHTTPObject() {	//	11/18/13
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try { xmlhttp = XMLHttpFactories[i](); }
			catch (e) { continue; }
			break;
			}
		return xmlhttp;
		}
		
	function find_warnings(tick_lat, tick_lng) {	//	9/10/13
		randomnumber=Math.floor(Math.random()*99999999);
		var theurl ="./ajax/loc_warn_list.php?version=" + randomnumber + "&lat=" + tick_lat + "&lng=" + tick_lng;
		theRequest(theurl, loc_w, "");	//	11/14/13
		function loc_w(req) {
			var the_warnings=JSON.decode(req.responseText);
			var the_count = the_warnings[0];
			if(the_count != 0) {
				$('loc_warnings').innerHTML = the_warnings[1];
				$('loc_warnings').style.display = 'block';
				}
			}			
		}
		
	var start_wl = false;
	function wl_win(the_Id) {				// 2/11/09
		if(start_wl) {return;}				// dbl-click proof
		start_wl = true;					
		var url = "warnloc_popup.php?id=" + the_Id;
		newwindow_wl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=600,width=750,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_wl)) {
			alert ("Locations warning operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_wl.focus();
		start_wl = false;
		}		// end function sv win()
</SCRIPT>
<?php				// 7/3/2013
	if ((intval(get_variable('broadcast') == 1)) &&  ($_SESSION['good_internet'])) { 	
		require_once('./incs/socket2me.inc.php');		// 5/22/2013
		}
?>
</HEAD>

<?php
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if(!(empty($_POST))  && $quick) {
?>
	<BODY onLoad = "do_notify(); parent.frames['upper'].show_msg ('Edit applied!'); document.go_Form.submit();"> 	<!-- 600 -->
		<FORM NAME='go_Form' METHOD = 'post' ACTION="main.php">
		</FORM>	
		</BODY></HTML>
	
<?php
		}
	require_once('./incs/links.inc.php');
	@session_start();
	if (array_key_exists('id', ($_GET))) {				// 5/4/11
		$_SESSION['active_ticket'] = $_GET['id'];
		session_write_close();
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
			} else {
			$the_addrs = edit_ticket($id);	// post updated data	11/18/13

			if ($addrs) {
				$theTo = implode("|", array_unique($addrs));
				$theText = "TICKET-Update: " . $_POST['frm_scope'];
				mail_it ($theTo, "", $theText, $id, 1 );
				}				// end if ($addrs)
			if($_SESSION['internet']) {
				require_once('./forms/ticket_view_screen.php');
				} else {
				require_once('./forms/ticket_view_screen_NM.php');
				}
			}
		exit();
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
			$tick_id = $_GET['id'];
 			$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,
 				UNIX_TIMESTAMP(problemend) AS problemend, 
 				UNIX_TIMESTAMP(booked_date) AS booked_date, 
 				UNIX_TIMESTAMP(date) AS date,
 				UNIX_TIMESTAMP(updated) AS updated, 
 				`t`.`description` AS `tick_descr`,
				`t`.`status` AS `in_status`,
 				`u`.`user` AS `tick_user`
 				FROM `$GLOBALS[mysql_prefix]ticket` `t`
 				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`t`.`in_types_id` = `ty`.`id`)
 				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`t`.`_by` = `u`.`id`)
 				WHERE `t`.`id`='$tick_id' LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			
			$row = stripslashes_deep(mysql_fetch_array($result));
?>
			<BODY onLoad = "ck_frames(); find_warnings(<?php print $row['lat'];?>, <?php print $row['lng'];?>);">	<!-- 628, 11/18/13 -->

			<div id = "bldg_info" class = "even" style = "display: none; position:fixed; left:500px; top:70px; z-index: 998; width:300px; height:auto;"></div> <!-- 4/1/2014  -->
			
			<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
			<SCRIPT SRC="./js/misc_function.js"></SCRIPT>				
<?php				
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

			$heading = "Edit Ticket - " . get_variable('map_caption');	//	6/10/11		
			
			echo "\n<SCRIPT>\n";
			$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
			$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_num_rows($result_bldg) > 0) {
				$i = 0;
				$sel_str = "<select name='bldg' onChange = 'do_bldg(this.options[this.selectedIndex].value); '>\n";
				$sel_str .= "\t<option value = '' selected>Select building</option>\n";
				echo "\n\t var bldg_arr = new Array();\n";
				while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
					extract ($row_bldg);
					$sel_str .= "\t<option value = {$i} >{$name}</option>\n";		
					echo "\t var bldg={ bldg_name:\"{$name}\", bldg_street:\"{$street}\", bldg_city:\"{$city}\", bldg_state:\"{$state}\", bldg_lat:\"{$lat}\", bldg_lon:\"{$lon}\", bldg_info:\"{$information}\"};\n";
					echo "\t bldg_arr.push(bldg);\n";		// object onto array
					$i++;
					}		// end while ()
			
				$sel_str .= "\t</SELECT>\n";
				}		// end if (mysql... )
			echo "\n</SCRIPT>\n";

			print "<TABLE BORDER='0' ID = 'outer' ALIGN='left'>\n";
			if($gmaps) {	//	6/10/11		
				print "<TR CLASS='header'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'>" . $heading . "</FONT></TD></TR>";	//	6/10/11	
				print "<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN=99 ALIGN='center'>&nbsp;</TD></TR>";	//	6/10/11					
				print "<TR CLASS='odd'><TD ALIGN='left' COLSPAN=99>";
				} else {
				print "<TR CLASS='header'><TD ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'>" . $heading . "</FONT></TD></TR>";	//	6/10/11	
				print "<TR CLASS='spacer'><TD CLASS='spacer' ALIGN='center'>&nbsp;</TD></TR>";	//	6/10/11						
				print "<TR CLASS='odd'><TD COLSPAN=99 ALIGN='left'>";	
				}
			print add_header($tick_id, TRUE);
			print "</TD></TR>\n";
			print "<TR CLASS='odd'><TD COLSPAN=99>&nbsp;</TD></TR>\n";	
			if($gmaps) {	//	6/10/11
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left'>";
			} else {
			print "<TR CLASS='even' valign='top'><TD CLASS='print_TD' ALIGN='left' style='width: 100%;' COLSPAN=99>";
			}
	
			print "<FORM NAME='edit' METHOD='post' ENCTYPE='multipart/form-data' onSubmit='return validate(document.edit)' ACTION='" . basename(__FILE__) . "?id=$tick_id&action=update'>";
			if($gmaps) {	//	6/10/11
				print "<TABLE BORDER='0' ID='data'>\n";
				} else {
				print "<TABLE BORDER='0' ID='data' WIDTH='100%'>\n";
				}
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=3><FONT CLASS='$theClass'><B>Edit Run Ticket</FONT> (#{$tick_id})</B></TD></TR>";
			print "<TR CLASS='odd'><TD ALIGN='center' COLSPAN=3><FONT CLASS='header'><FONT SIZE='-2'>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>";

			if (mysql_num_rows($result_bldg) > 0) {			// 4/7/2014
?>
		<TR CLASS='odd'>
			<TD CLASS="td_label" ><?php print get_text("Building"); ?></A>:</TD>
			<TD></TD>
			<TD><?php echo $sel_str;?></TD>
			</TR>
<?php
				}		// end if()

			print "<TR CLASS='even'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_loca']}');\">" . get_text("Location") . ": </TD><TD><INPUT SIZE='48' TYPE='text' NAME='frm_street' VALUE=\"{$row['street']}\" MAXLENGTH='48' {$dis}></TD></TR>\n";
			print "<TR CLASS='odd'><TD CLASS='td_label' onmouseout='UnTip()' onmouseover='Tip(\"About Address, for instance round the back or building number\");'>" . get_text('Address About') . "</A>:</TD>
					<TD></TD>
					<TD><INPUT NAME='frm_address_about' tabindex=1 SIZE='72' TYPE='text' VALUE=\"{$row['address_about']}\" MAXLENGTH='512'></TD>
					</TR>";	//	9/10/13
			print "<TR CLASS='odd'>
					<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_city']}');\">" . get_text("City") . ":</TD>";
			print 		"<TD>";

			if($gmaps) {	//	6/4/2013			
				print "<button type='button' onClick='Javascript:loc_lkup(document.edit);'><img src='./markers/glasses.png' alt='Lookup location.' /></button>";
			 	}				// end if($gmaps)
			print 		"</TD>";
			print 		"<TD><INPUT SIZE='32' TYPE='text' 	NAME='frm_city' VALUE=\"{$row['city']}\" MAXLENGTH='32' onChange = 'this.value=capWords(this.value)' {$dis}>\n";
			$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10, 3/27/2013
			
			print 	"<SPAN STYLE='margin-left:24px'  onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_state']}');\">" . get_text("St") . "</SPAN>:&nbsp;&nbsp;<INPUT SIZE='{$st_size}' TYPE='text' NAME='frm_state' VALUE='" . $row['state'] . "' MAXLENGTH='{$st_size}' {$dis}>";

			if ($gmaps) {						// 6/4/2013
				print "<BUTTON type='button' onClick='Javascript:do_nearby(this.form); return false;'>Nearby?</BUTTON>";
				}		// end if ($gmaps)
			print 		"<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 300px; font-size: 1.5em; font-weight: bold; border: 2px outset #707070;'></DIV>";		
			print 		"</TD></TR>\n";

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
			print "<TR CLASS='odd'><TD CLASS='td_label' onmouseout='UnTip()' onmouseover='Tip(\"To Address, Not plotted on map, for information only\");'>" . get_text('To Address') . "</A>:</TD>
					<TD></TD>
					<TD><INPUT NAME='frm_to_address' tabindex=1 SIZE='72' TYPE='text' VALUE=\"{$row['to_address']}\" MAXLENGTH='512'></TD>
					</TR>";	//	9/10/13		
			print "<TR CLASS='even'>
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
					$temp = preg_replace("/[\n\r]/"," ",$temp_row['protocol']); 
					$temp = addslashes($temp);
					print "\n<SCRIPT>protocols[{$row2['id']}] = '" . $temp . "';</SCRIPT>\n";		// 5/6/10
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

			if(get_num_groups()) {	//	3/28/12 - fixes incorrect display of Regions.		
			if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
					print "<TR CLASS='even' VALIGN='top'>";
					print "<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('Sets regions that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Regions') . "</A>: </TD>";
					print "<TD><SPAN id='expand_gps' onClick=\"$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>";
					print "<SPAN id='collapse_gps' onClick=\"$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>";
					print "<TD>";
					$alloc_groups = implode(',', get_allocates(1, $tick_id));	//	6/10/11
					print get_sub_group_butts(($_SESSION['user_id']), 1, $tick_id) ;	//	6/10/11		
					print "</DIV></TD></TR>";		// 6/10/11
					
				} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11	
					print "<TR CLASS='even' VALIGN='top'>";
					print "<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('Sets regions that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Regions') . "</A>: </TD>";
					print "<TD><SPAN id='expand_gps' onClick=\"$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>";
					print "<SPAN id='collapse_gps' onClick=\"$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>";
					print "<TD>";
					$alloc_groups = implode(',', get_allocates(1, $tick_id));	//	6/10/11
					print get_sub_group_butts(($_SESSION['user_id']), 1, $tick_id) ;	//	6/10/11			
					print "</DIV></TD></TR>";		// 6/10/11	
					
				} else {
					print "<TR CLASS='even' VALIGN='top'>";
					print "<TD CLASS='td_label' onmouseout='UnTip()' onmouseover=\"Tip('Sets regions that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Regions') . "</A>: </TD>";
					print "<TD><SPAN id='expand_gps' onClick=\"$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>";
					print "<SPAN id='collapse_gps' onClick=\"$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>";
					print "<TD>";
					$alloc_groups = implode(',', get_allocates(1, $tick_id));	//	6/10/11
					print get_sub_group_butts_readonly(($_SESSION['user_id']), 1, $tick_id) ;	//	6/10/11			
					print "</DIV></TD></TR>";		// 6/10/11	
				}
			} else {
			print "<INPUT TYPE='hidden' NAME='frm_group[]' VALUE='1'>";
			}
			
			print "<TR CLASS='odd' VALIGN='top'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_synop']}');\">" . get_text("Synopsis") . ":</TD>";
			print 	"<TD CLASS='td_label'><TEXTAREA NAME='frm_description' COLS='45' ROWS='8' {$dis} >" . $row['tick_descr'] . "</TEXTAREA></TD></TR>\n";		// 8/8/09
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

			$portal_user_control = "<SELECT NAME='frm_portal_user'>";
			$query_pu = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = " . $GLOBALS['LEVEL_SERVICE_USER'] . " ORDER BY `name_l` ASC";
			$result_pu = mysql_query($query_pu) or do_error($query_pu, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			if(mysql_affected_rows() > 0) {
				$has_portal = 1;
				$portal_user_control .= "<OPTION VALUE = 0 SELECTED>Select User</OPTION>\n";
					while ($row_pu = mysql_fetch_array($result_pu, MYSQL_ASSOC)) {
						$sel = $row_pu['id'] == $row['portal_user'] ? "SELECTED": "";
						$theName = $row_pu['name_f'] . " " . $row_pu['name_l'] . " (" . $row_pu['user'] . ")";
						$portal_user_control .= "<OPTION VALUE=" . $row_pu['id'] . " " . $sel . ">" . $theName . "</OPTION>\n";
						}
				$portal_user_control .= "</SELECT>\n";	
				} else {
				$has_portal = 0;
				}
			
			if($has_portal == 1) {	
				print "<TR CLASS='odd'>
					<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('Associated Portal User - this user can see this ticket as a request');\">" . get_text("Portal User") . ":</TD>
					<TD>";

				print $portal_user_control;
				print "</TD></TR>\n";
				} else {
				print "<INPUT TYPE='hidden' NAME='frm_portal_user' VALUE=0>";
				}

			print "<TR CLASS='even'>
				<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_name']}');\">" . get_text("Incident name") . ":</TD>
				<TD><INPUT TYPE='text' NAME='frm_scope' SIZE='48' VALUE='{$row['scope']}' MAXLENGTH='48' {$dis} /></TD></TR>\n"; 

			print "<TR CLASS='odd'><TD COLSPAN='2'>&nbsp;</TD></TR>";
			$selO = ($row['in_status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
			$selC = ($row['in_status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
			$selP = ($row['in_status']==$GLOBALS['STATUS_SCHEDULED'])? "SELECTED" :"" ;

			$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (get_variable('delta_mins')*60));
			$elapsed = my_date_diff($row['problemstart'], $end_date);		// 5/13/10

			print "<TR CLASS='even'>
				<TD CLASS='td_label' COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_status']}');\">" . get_text("Status") . ":</TD>
				<TD><SELECT NAME='frm_status' {$dis}><OPTION VALUE='" . $GLOBALS['STATUS_OPEN'] . "' $selO>Open</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_CLOSED'] . "'$selC>Closed</OPTION><OPTION VALUE='" . $GLOBALS['STATUS_SCHEDULED'] . "'$selP>Scheduled</OPTION></SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$elapsed} </TD></TR>";
//			print "<TR CLASS='odd'><TD CLASS='td_label'>Affected:</TD><TD><INPUT TYPE='text' SIZE='48' NAME='frm_affected' VALUE='" . $row['affected'] . "' MAXLENGTH='48' {$dis}></TD></TR>\n";

//	facility handling  - 3/25/10

			$al_groups = $_SESSION['user_groups'];
				
			if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
				$curr_viewed= explode(",",$_SESSION['viewed_groups']);
				}

			if(!isset($curr_viewed)) {	
				if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
					$where2 = "WHERE `a`.`type` = 3";
					} else {			
					$x=0;	//	6/10/11
					$where2 = "WHERE (";	//	6/10/11
					foreach($al_groups as $grp) {	//	6/10/11
						$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
						$where2 .= "`a`.`group` = '{$grp}'";
						$where2 .= $where3;
						$x++;
						}
					$where2 .= " AND `a`.`type` = 3";	//	6/10/11		
					}
				} else {
				if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
					$where2 = "WHERE `a`.`type` = 3";
					} else {				
					$x=0;	//	6/10/11
					$where2 = "WHERE (";	//	6/10/11
					foreach($curr_viewed as $grp) {	//	6/10/11
						$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
						$where2 .= "`a`.`group` = '{$grp}'";
						$where2 .= $where3;
						$x++;
						}
					$where2 .= " AND `a`.`type` = 3";	//	6/10/11		
					}
				}

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
			print 	"<TD style='background-color: #DEDEDE; color: #000000; border: 1px inset #707070;'>" . $row['comments'] . "</TD></TR>\n";
			print "<TR CLASS='even' VALIGN='top'>
				<TD CLASS='td_label' COLSPAN=2>&nbsp;</TD>";				// 10/21/08, 8/8/09
			print 	"<TD><TEXTAREA style='width: 98%;' NAME='frm_comments' {$dis} ></TEXTAREA></TD></TR>\n";			
			$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
			$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);

			if (mysql_num_rows($result_sigs)>0) {				// 2/11/11			
?>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 12/18/10 -->
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

			print "<TR CLASS='even'>
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
			print "<TR CLASS='odd'>
				<TD CLASS='td_label'  COLSPAN=2 onmouseout='UnTip()' onmouseover=\"Tip('{$titles['_asof']}');\">Updated:</TD><TD>" . format_date($row['updated']) . "{$by_str}</TD></TR>\n";		// 10/21/08
			print "<TR class='spacer'><TD COLSPAN='3' class='spacer'>&nbsp;</TD></TR>";
			print "<TR class='heading'><TD COLSPAN='3' class='heading' style='text-align: center;'>File Upload</TD></TR>";
			print "<TR class='odd'><TD class='td_label' COLSPAN='2' style='text-align: left;'>Choose a file to upload:</TD>
				<TD class='td_data' style='text-align: left;'><INPUT NAME='frm_file' TYPE='file' /></TD></TR>";
			print "<TR class='odd'><TD class='td_label' COLSPAN='2' style='text-align: left;'>File Name</TD>
				<TD class='td_data' style='text-align: left;'><INPUT NAME='frm_file_title' TYPE='text' SIZE='48' MAXLENGTH='128' VALUE=''></TD></TR>";
			print "<TR class='even'><TD COLSPAN='3'>&nbsp;</TD></TR>";
			print "<TR class='spacer'><TD COLSPAN='3' class='spacer'>&nbsp;</TD></TR>";
			$lat = $row['lat']; $lng = $row['lng'];	
			if(($lat==$GLOBALS['NM_LAT_VAL']) && ($lng==$GLOBALS['NM_LAT_VAL'])) {	// check ticket entered in "no maps" Mode 7/28/10
				$lat=get_variable('def_lat');
				$lng=get_variable('def_lng');
				}	// End of check on ticket entered in "no maps" Mode 7/28/10
?>	
			
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $row['lat'];?>">				<!-- // 8/9/08 -->
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $row['lng'];?>">
			<INPUT TYPE="hidden" NAME="frm_status_default" VALUE="<?php print $row['in_status'];?>">
			<INPUT TYPE="hidden" NAME="frm_affected_default" VALUE="<?php print $row['affected'];?>">
			<INPUT TYPE="hidden" NAME="frm_scope_default" VALUE="<?php print $row['scope'];?>">
			<INPUT TYPE="hidden" NAME="frm_owner_default" VALUE="<?php print $row['owner'];?>">
			<INPUT TYPE="hidden" NAME="frm_severity_default" VALUE="<?php print $row['severity'];?>">
			<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>">
			<INPUT TYPE="hidden" NAME="frm_exist_rec_fac" VALUE="<?php print $exist_rec_fac;?>">
			<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">			
			<INPUT TYPE="hidden" NAME="frm_fac_chng" VALUE="0">		<!-- 3/25/10 -->
			<INPUT TYPE="hidden" NAME="frm_notes" VALUE="<?php print $row['comments'];?>">
<?php
			print "<TR CLASS='even'>
				<TD COLSPAN='10' ALIGN='center'><BR /><B><U><A HREF='#' TITLE='List of all actions and patients atached to this Incident'>Actions and Patients</A></U></B><BR /></TD></TR>";	//8/7/09
			print "<TR CLASS='odd'><TD COLSPAN='10' ALIGN='center'>";										//8/7/09
			print show_actions($row[0], "date", !$disallow, TRUE);											//8/7/09
			print "<BR /><BR /></TD></TR>";																//8/7/09
			print "</TABLE>";		// end data 8/7/09
															//8/7/09
			print "</TD>";
			if($gmaps) {
				print "<TD style='background-color: transparent;'>";		
				print "<TABLE ID='mymap' border = 0><TR class='odd'><TD><DIV ID='map_canvas' STYLE='WIDTH: " . get_variable('map_width') . "PX; HEIGHT:" . get_variable('map_height') . "PX;  z-index: 1;'></DIV>";
				print ($zoom_tight)? "<SPAN  onClick= 'zoom_in({$lat}, {$lng}, {$zoom_tight});' STYLE = 'margin-left:20px'><U>Zoom</U></SPAN>\n" : "";	// 3/27/10
					
				print "</TD></TR></TABLE>\n";
				print "</TD>";
				}
			print "</TR>";
			print "</FORM>";
			print "</TABLE>";		// bottom of outer
			
			$from_left = 450;
			$from_top = 220;
			$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
			print add_sidebar(FALSE, TRUE, TRUE, FALSE, $allow_filedelete, $tick_id, 0, 0, 0);
?>			
			<FORM NAME='can_Form' ACTION="main.php">
			<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['id'];?>">
			</FORM>	
					<DIV id='boxB' class='box' STYLE='left:<?php print $from_left;?>px;top:<?php print $from_top;?>px; position:fixed; z-index: 2;' > <!-- 9/23/10 -->
					<DIV class="bar" STYLE="width:12em; color:red; background-color : transparent; z-index: 2;"
						 onmousedown="dragStart(event, 'boxB')">&nbsp;&nbsp;&nbsp;&nbsp;<I>Drag me</I></DIV><!-- drag bar - 2/5/11 -->
						 <DIV STYLE = 'height:10px;'/>
							<INPUT id='hist_but' TYPE='button' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' VALUE='<?php print get_text("Cancel");?>' onClick='history.back();'  STYLE = 'margin-left:20px;' /><BR />
<?php
if (!$disallow) {
?>
							<INPUT id='reset_but' TYPE='button' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' VALUE='<?php print get_text("Reset");?>' onClick= 'st_unlk_res(document.edit); reset_end(document.edit); document.edit.reset(); resetmap(<?php print $lat;?>, <?php print $lng;?>);'  STYLE = 'margin-left:20px; margin-top:10px' /><BR />
							<INPUT id='sub_but' TYPE='button' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' VALUE='<?php print get_text("Next");?>'  onClick='validate(document.edit);' STYLE = 'margin-left:20px; margin-top:10px' />
<?php
		}
?>		
					</DIV>
<div id = "bldg_info" class = "even" style = "display: none; position:fixed; left:500px; top:70px; z-index: 998; width:300px; height:auto;"></div> <!-- 4/1/2014  -->
					
	<SCRIPT>
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

	function do_ngs() {											// LL to USNG
		var loc = <?php print get_variable('locale');?>;
		if(loc == 0) { document.forms[0].frm_ngs.disabled=false; document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5); document.forms[0].frm_ngs.disabled=true;}
		if(loc == 1) { document.forms[0].frm_osgb.disabled=false; document.forms[0].frm_osgb.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value); document.forms[0].frm_osgb.disabled=true; }
		if(loc == 2) { document.forms[0].frm_utm.disabled=false; document.forms[0].frm_utm.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value); document.forms[0].frm_utm.disabled=true; }			
		}

	function do_grids(theForm) {								//12/13/10
		if (theForm.frm_ngs) {do_usng(theForm) ;}
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

	function resetmap(lat, lng) {						// restore original marker and center
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", 13, theLocale, useOSMAP, "tr");
		do_lat (lat);
		do_lng (lng);
		do_ngs(document.edit);								// 8/23/08
		}

// *********************************************************************
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
// *********************************************************************

	function contains(array, item) {
		for (var i = 0, I = array.length; i < I; ++i) {
			if (array[i] == item) return true;
			}
		return false;
		}
		
	function do_bldg(in_val) {							// called with zero-based array index - 3/29/2014				
		if (myMarker) {myMarker.setMap(null);}			// clear existing/default icon 
	
		var obj_bldg = bldg_arr[in_val];						// nth object
		document.edit.frm_street.value = obj_bldg.bldg_street;
		document.edit.frm_city.value = obj_bldg.bldg_city;
		document.edit.frm_state.value = obj_bldg.bldg_state;
		document.edit.frm_address_about.value = obj_bldg.bldg_name;
		if (document.edit.frm_lat) {
			document.edit.frm_lat.value = document.edit.show_lat.value = obj_bldg.bldg_lat.toString();		// parseFloat() - .toFixed(2); 
			document.edit.frm_lng.value = document.edit.show_lng.value = obj_bldg.bldg_lon.toString();
			}
		if (obj_bldg.bldg_info.length > 0 ) {
			var close_str = "<span onclick = \"$('bldg_info').style = 'display:none';\"><b><center><u>X</u></center></b></span>";
			$('bldg_info').innerHTML = obj_bldg.bldg_info + close_str;		// 
			$('bldg_info').style.display = "inline";	
			}
		loc_lkup(document.edit) ;			// to map
		}		// end function do_bldg()

	function loc_lkup(my_form) {		   						// 7/5/10
		if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var myAddress = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();
		control.options.geocoder.geocode(myAddress, function(results) {
			var r = results[0]['center'];
			var theLat = parseFloat(r.lat.toFixed(6));
			var theLng = parseFloat(r.lng.toFixed(6));
			pt_to_map (my_form, theLat, theLng);
			if((theLat) && (theLng)) {		//	8/14/13
				find_warnings(theLat, theLng)					
				}	
			});
		}				// end function loc_lkup()

	function theRequest(url,callback,postData) {
		var req = docreateXMLHTTPObject();
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

	var doXMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];

	function docreateXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try { xmlhttp = doXMLHttpFactories[i](); }
			catch (e) { continue; }
			break;
			}
		return xmlhttp;
		}

// *****************************************************************************

	function do_nearby(the_form){		// 11/22/2012
		if (the_form.frm_lat.value.length == 0) {
			alert("Map <?php echo get_text("Location");?> is required for nearby <?php echo get_text("Incident");?> lookup.");
			return;
			}
		var the_url = "nearby.php?tick_lat="+the_form.frm_lat.value+"&tick_lng="+the_form.frm_lng.value;
		newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (newwindow == null) {
			alert ("Nearby operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}		// end do_nearby()
		
	</SCRIPT>


<?php
			}			// end  sanity check 
		}
if($gmaps) {
?>
	<SCRIPT>
	var latLng;
	var in_local_bool = "0";
	var mapWidth = <?php print get_variable('map_width');?>+20;
	var mapHeight = <?php print get_variable('map_height');?>+20;
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	var initZoom = <?php print get_variable('def_zoom');?>;
	init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	var bounds = map.getBounds();	
	var zoom = map.getZoom();

	function onMapClick(e) {
		if(marker) {map.removeLayer(marker); }
		var iconurl = "./our_icons/yellow.png";
		icon = new baseIcon({iconUrl: iconurl});	
		marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
		marker.addTo(map);
		newGetAddress(e.latlng, "ei");
		};

	map.on('click', onMapClick);
<?php
	do_kml();
?>
	</SCRIPT>
<?php
	}
?>
</BODY>
</HTML>
