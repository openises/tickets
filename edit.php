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
$in_win = (array_key_exists("mode", $_GET)) ? 1 : 0;
$from_mi = (array_key_exists("mi", $_GET)) ? 1 : 0;
$gmaps = $_SESSION['internet'];
$noMaps = (!$in_win && !$gmaps) ? 1 : 0;
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
			print "<DIV id='button_bar' class='but_container'>";
			add_header($id, FALSE, TRUE);
			print "</DIV";
			print "<BR />";
			print '<FONT CLASS="header">Ticket <I>' . $theScope . '</I> has not changed</FONT><BR /><BR />';
			require_once('./forms/ticket_view_screen.php');
			exit();
			} else {
			$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
			$post_frm_affected 	 		= strip_html($post_frm_affected);
			$_POST['frm_scope']			= strip_html($_POST['frm_scope']);
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
				
// If Major Incident Set


			if(array_key_exists('frm_maj_inc', $_POST) && $_POST['frm_maj_inc'] != 0) {
				// Delete existing entries to avoid dupes
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `ticket_id` = '$id'"; 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				// Insert new entry
				$maj_inc = intval($_POST['frm_maj_inc']);
				$query  = "INSERT INTO `$GLOBALS[mysql_prefix]mi_x` (
					`mi_id`, 
					`ticket_id`)
					VALUES (
					" . $maj_inc . ",
					" . $id . ")";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
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

//			print "<DIV id='button_bar' class='but_container'>";
//			add_header($id, FALSE, TRUE);
//			print "</DIV";
//			print '<BR /><BR /><BR /><BR /><BR /><FONT CLASS="header">Ticket <I>' . $_POST['frm_scope'] . '</I> has been updated</FONT><BR /><BR />';		/* show updated ticket */
			if($tick_stat == 1) {
				$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE				
				} else {
				$addrs = notify_user($id,$GLOBALS['NOTIFY_TICKET_CLOSE']);		// returns array or FALSE						
				}
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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">		<!-- 3/15/11 -->
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="./js/Control.Geocoder.css" />
<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT SRC="./js/suggest.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT>
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
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
			if($https) {
?>
				<script src="https://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php
				} else {
?>
				<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php				
				}
			}
		}
?>
<SCRIPT SRC="./js/usng.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT SRC='./js/jscoord.js' TYPE="application/x-javascript"></SCRIPT>			<!-- coordinate conversion 12/10/10 -->	
<SCRIPT SRC="./js/lat_lng.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/geotools2.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/osgb.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 11/8/11 -->	
<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<SCRIPT>
window.onresize=function(){set_size();}
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var layercontrol;
var mapWidth;
var mapHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
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
var fieldwidth;
var medfieldwidth;		
var smallfieldwidth;
var in_win = <?php print $in_win;?>;
var noMaps = <?php print $noMaps;?>;
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
	
var fields = ["proto_cell",
			"street",
			"scope",
			"toaddress",
			"address_about",
			"loc_warnings",
			"description",
			"comments",
			"phone",
			"contact",
			"updated",
			"sel_file",
			"filename",
			"sel_bldg",
			"911",
			"portal_user",
			"elapsed"];
var medfields = ["city",
				"sel_in_types",
				"sel_severity",
				"sel_signals",
				"sel_signals2",
				"sel_maj_inc",
				"sel_facility",
				"sel_recfacility",
				"sel_status"];
var smallfields = ["the_lat", "the_lng"];
			
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
	if(in_win == 1) {
		set_fontsizes(viewportwidth, "popup");
		} else {
		set_fontsizes(viewportwidth, "fullscreen");
		}
	if(window.isinwin) {set_fontsizes(viewportwidth, "popup");} else {set_fontsizes(viewportwidth, "fullscreen");}
	if(window.isinwin) {mapWidth = viewportwidth * .95;} else {mapWidth = viewportwidth * .40;}
	mapHeight = viewportheight * .55;
	if(window.isinwin) {outerwidth = viewportwidth * .95;} else {outerwidth = viewportwidth * .99;}
	outerheight = viewportheight * .95;
	if(window.isinwin) {colwidth = outerwidth;} else {colwidth = outerwidth * .40;}
	colheight = outerheight * .95;
	leftcolwidth = viewportwidth * .45;
	winleftcolwidth = viewportwidth * .90;
	rightcolwidth = viewportwidth * .35;
	fieldwidth = leftcolwidth * .45;
	medfieldwidth = colwidth * .20;	
	smallfieldwidth = colwidth * .15;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('leftcol_inwin')) {$('leftcol_inwin').style.width = winleftcolwidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
	if($('rightcol')) {$('rightcol').style.width = colwidth + "px";}
	if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
	if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
	if($('map_caption')) {$('map_caption').style.width = mapWidth + "px";}
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}
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
		var errmsg="";
		if ((theForm.frm_street.value == "") && (theForm.frm_city.value == "") && (theForm.frm_state.value == ""))	{errmsg+= "\tAddress is required\n";}
		if ((document.edit.frm_status.value == <?php print $GLOBALS['STATUS_CLOSED'];?>) && (document.edit.frm_year_problemend.disabled))
														{errmsg+= "\tClosed ticket requires run end date\n";}
		if ((document.edit.frm_status.value == <?php print $GLOBALS['STATUS_CLOSED'];?>) && (document.edit.frm_comments==""))
														{errmsg+= "\tClosed ticket requires <?php print $disposition;?> data\n";}
		if (theForm.frm_contact.value == "")			{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")				{errmsg+= "\tIncident name is required\n";}		// 10/21/08

		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			} else {
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
		}
		
	function st_unlk_res(theForm) {										// 8/10/08	
		theForm.frm_year_problemstart.disabled = true;
		theForm.frm_month_problemstart.disabled = true;
		theForm.frm_day_problemstart.disabled = true;
		theForm.frm_hour_problemstart.disabled = true;
		theForm.frm_minute_problemstart.disabled = true;
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
		
	function load_markup() {
		load_exclusions();
		load_ringfences();
		load_catchments();
		load_basemarkup();
		load_groupbounds();	
		load_regions();
		}
</SCRIPT>
<?php				// 7/3/2013
	if ((intval(get_variable('broadcast') == 1)) &&  ($_SESSION['good_internet'])) { 	
		require_once('./incs/socket2me.inc.php');		// 5/22/2013
		}
?>
</HEAD>

<?php
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if(!(empty($_POST)) && $quick) {
?>
	<BODY onLoad = "do_notify(); parent.frames['upper'].show_msg ('Edit applied!'); document.go_Form.submit();"> 	<!-- 600 -->
		<FORM NAME='go_Form' METHOD = 'post' ACTION="main.php">
		</FORM>	
		</BODY></HTML>
	
<?php
		}
	if($in_win == 0) {
		$mode = 0;
		require_once('./incs/links.inc.php');
		} else {
		$mode = 1;
		}
	@session_start();
	if (array_key_exists('id', ($_GET))) {				// 5/4/11
		$_SESSION['active_ticket'] = $_GET['id'];
		session_write_close();
		$id = $_GET['id'];
		} elseif (array_key_exists('id', ($_SESSION))) {
		$id = $_SESSION['active_ticket'];	
		} else {
		echo "error at "	 . __LINE__;
		}								// end if/else

	if ((isset($_GET['action'])) && ($_GET['action'] == 'update')) {		/* update ticket */
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id' LIMIT 1")) {
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT>";
			} else {
			$the_addrs = edit_ticket($id);	// post updated data	11/18/13
			$theStatus = $_POST['frm_status'];
			$theNotice = "Ticket <I>'" . $_POST['frm_scope'] . "'</I> has been updated";
			if ($addrs) {
				$theTo = implode("|", array_unique($addrs));
				$theText = "TICKET-Update: " . $_POST['frm_scope'];
				mail_it($theTo, "", $theText, $id, $theStatus);
				}				// end if ($addrs)
			if($_SESSION['internet']) {
				require_once('./forms/ticket_view_screen.php');
				} else {
				require_once('./forms/ticket_view_screen_NM.php');
				}
			}
		exit();
		} else if (isset($_GET['delete'])) {							//delete ticket
		if ($_POST['frm_confirm']) {
			/* remove ticket and ticket actions */
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id'") or do_error('edit.php::remove_ticket(ticket)', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE ticket_id='$id'") or do_error('edit.php::remove_ticket(action)', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<FONT CLASS=\"header\">Ticket '$id' has been removed.</FONT><BR /><BR />";
			list_tickets();
			} else {		//confirm deletion
			print "<FONT CLASS='header'>Confirm ticket deletion</FONT><BR /><BR /><FORM METHOD='post' NAME = 'del_form' ACTION='" . basename(__FILE__) . "?id=$id&delete=1&go=1'><INPUT TYPE='checkbox' NAME='frm_confirm' VALUE='1'>Delete ticket #$id &nbsp;<INPUT TYPE='Submit' VALUE='Confirm'></FORM>";
			}
		} else {				// not ($_GET['delete'])
		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {		/* sanity check */
			print "<FONT CLASS=\"warn\">Invalid Ticket ID: '$id'</FONT><BR />";
			} else {				// OK, do form - 7/7/09, 4/1/11
			$tick_id = $_GET['id'];
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00'";
			$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_mi = mysql_num_rows($result);

			$query_mi = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `ticket_id` = " . $tick_id . " LIMIT 1";
			$result_mi = mysql_query($query_mi);
			if(mysql_num_rows($result_mi) > 0) {
				$row_mi = stripslashes_deep(mysql_fetch_assoc($result_mi));
				$exist_mi = $row_mi['mi_id'];
				}
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
			if($in_win == 1) {
?>
				<BODY onLoad = "load_markup(); find_warnings(<?php print $row['lat'];?>, <?php print $row['lng'];?>);">
<?php
				} else {
?>
				<BODY onLoad = "ck_frames(); load_markup(); find_warnings(<?php print $row['lat'];?>, <?php print $row['lng'];?>);">
<?php
				}
?>
			<div id = "bldg_info" class = "even" style = "display: none; position:fixed; left:500px; top:70px; z-index: 998; width:300px; height:auto;"></div> <!-- 4/1/2014  -->
			
			<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
			<SCRIPT SRC="./js/misc_function.js"></SCRIPT>				
<?php				
			if (good_date($row['problemend'])) {
?>
				<SCRIPT>
				good_end = true;
				</SCRIPT>
<?php			
				}

			$priorities = array("severity_normal","severity_medium","severity_high" );
			$theClass = $priorities[$row['severity']];
			$exist_rec_fac = $row['rec_facility'];
			$st_size = (get_variable("locale") ==0)?  2: 4;
			$lat = $row['lat']; $lng = $row['lng'];	
			if(($lat==$GLOBALS['NM_LAT_VAL']) && ($lng==$GLOBALS['NM_LAT_VAL'])) {
				$lat=get_variable('def_lat');
				$lng=get_variable('def_lng');
				}

			$heading = "Edit Ticket - " . get_variable('map_caption');	//	6/10/11		
			
			echo "\n<SCRIPT>\n";
			$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
			$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_num_rows($result_bldg) > 0) {
				$i = 0;
				$sel_str = "<select id='sel_bldg' name='bldg' onChange = 'do_bldg(this.options[this.selectedIndex].value); '>\n";
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
?>
			<SPAN style='width: 100%; display: inline-block' CLASS='spacer'>&nbsp;</SPAN><BR />
			<DIV id = "outer" style='position: absolute; left: 0px; top: 70px; width: 90%;'>
<?php
				if($in_win == 0) {
?>
					<DIV id='button_bar' class='but_container' style='text-align: left; position: fixed; top: 10px;'>
					<?php print add_header($tick_id, TRUE);?>
					</DIV>
					<DIV id = 'leftcol' style='position: relative; left: 30px; float: left;'>
<?php
					} else {
?>
					<DIV id='button_bar' class='but_container' style='text-align: left; position: fixed; top: 60px;'>
					<?php print add_header($tick_id, TRUE);?>
					</DIV>
					<DIV id = 'leftcol_inwin' style='position: relative; left: 10px; top: 50px; float: left;'>
<?php
					}
?>
					<DIV CLASS='header' style = "height:40px; width: 100%; float: none; text-align: center;">
						<SPAN CLASS='text_biggest <?php print $theClass;?>'><B>Edit Run Ticket (#<?php print $tick_id;?>)</B></SPAN>
						<BR />
						<SPAN CLASS='text_blue'>(mouseover caption for help information)</SPAN>
						<BR />
					</DIV>
					<FORM NAME='edit' METHOD='post' ENCTYPE='multipart/form-data' onSubmit='return validate(document.edit)' ACTION='edit.php?id=<?php print $tick_id;?>&action=update&mode=<?php print $in_win;?>'>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Incident Basics</LEGEND>
						<DIV style='position: relative;'>
						<LABEL for="sel_in_types" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_nature'];?>');"><?php print $nature;?>: </LABEL>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
						$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
?>
						<SELECT id='sel_in_types' NAME='frm_in_types_id' <?php print $dis;?> onChange='do_inc_nature(this.options[selectedIndex].value.trim());'>
<?php
							if ($row['in_types_id']==0) {
?>
								<OPTION VALUE=0 SELECTED>TBD</OPTION>
<?php										
								}
							$the_grp = strval(rand());
							$i = 0;
							$proto = "";
							while ($row2 = stripslashes_deep(mysql_fetch_array($result))) {
								$color = $row2['color'];
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
								print "<OPTION style='color: " . $color . ";' VALUE=" . $row2['id'] . $sel . ">" . $row2['type'] . "</OPTION>";
								if (!(empty($row2['protocol']))) {				// 7/7/09 - note string key
									$temp = preg_replace("/[\n\r]/"," ",$row2['protocol']); 
									$temp = addslashes($temp);
									print "\n<SCRIPT>protocols[{$row2['id']}] = '" . $temp . "';</SCRIPT>\n";		// 5/6/10
									}
								$i++;
								}
							unset ($result);
?>
							</OPTGROUP>
						</SELECT>
						<BR />	
						<LABEL for="sel_severity" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_prio'];?>');"><?php print get_text("Priority");?>: </LABEL>
						<SELECT ID='sel_severity' NAME='frm_severity' <?php print $dis;?>>
<?php
							$nsel = ($row['severity']==$GLOBALS['SEVERITY_NORMAL'])? "SELECTED" : "" ;
							$msel = ($row['severity']==$GLOBALS['SEVERITY_MEDIUM'])? "SELECTED" : "" ;
							$hsel = ($row['severity']==$GLOBALS['SEVERITY_HIGH'])? "SELECTED" : "" ;
?>
							<OPTION VALUE='<?php print $GLOBALS['SEVERITY_NORMAL'];?>' <?php print $nsel;?>><?php print get_text("Normal");?></OPTION>
							<OPTION VALUE='<?php print $GLOBALS['SEVERITY_MEDIUM'];?>' <?php print $msel;?>><?php print get_text("Medium");?></OPTION>
							<OPTION VALUE='<?php print $GLOBALS['SEVERITY_HIGH'];?>' <?php print $hsel;?>><?php print get_text("High");?></OPTION>
						</SELECT>
						<BR />
						<LABEL for="proto_cell" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_proto'];?>');"><?php print get_text("Protocol");?>: </LABEL>
						<DIV ID='proto_cell' style='display: inline-block; min-height: 20px;'><?php print $row['protocol'];?></DIV>
<?php

						if (mysql_num_rows($result_bldg) > 0) {			// 4/7/2014
?>
							<LABEL for="sel_bldg" onmouseout="UnTip()" onmouseover="Tip('Buildings stored - select one if required');"><?php print get_text('Buildings');?>: <font color='red' size='-1'>*</font></LABEL>
							<?php echo $sel_str;?>
<?php
							}		// end if()
?>
						<LABEL for="street" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_loca'];?>');"><?php print get_text('Location');?>: <font color='red' size='-1'>*</font></LABEL>
						<INPUT id='street' NAME='frm_street' tabindex=1 SIZE='64' TYPE='text' VALUE="<?php print $row['street'];?>" MAXLENGTH='512'>
						<LABEL for="address_about" onmouseout="UnTip()" onmouseover="Tip('Extra information about address');"><?php print get_text('About Address');?>: <font color='red' size='-1'>*</font></LABEL>
						<INPUT id='address_about' NAME='frm_address_about' tabindex=1 SIZE='64' TYPE='text' VALUE="<?php print $row['address_about'];?>" MAXLENGTH='512'>
						<LABEL for="city" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_city"];?>');"><?php print get_text('City');?>: <font color='red' size='-1'>*</font>
<?php
						if($gmaps || $good_internet) {
?>								
							<button type='button' style='float: right;' onClick='Javascript:loc_lkup(document.edit);'><img src='./markers/glasses.png' alt='Lookup location.' /></button>
<?php
							}				// end if($gmaps)
?>
						</LABEL>
						<INPUT SIZE='32' TYPE='text' id='city' NAME='frm_city' VALUE="<?php print $row['city'];?>" MAXLENGTH='32' onChange = 'this.value=capWords(this.value)' <?php print $dis;?>>
<?php
								if($gmaps) {
?>		
									<BUTTON type='button' onClick='Javascript:do_nearby(this.form); return false;'>Nearby?</BUTTON>
<?php
									}				// end if($gmaps)
?>
						<BR /><LABEL for="state" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_state"];?>');"><?php print get_text('St');?>: <font color='red' size='-1'>*</font></LABEL>
						<INPUT ID='state' SIZE='<?php print $st_size;?>' TYPE='text' NAME='frm_state' VALUE='<?php print $row['state'];?>' MAXLENGTH='<?php print $st_size;?>' <?php print $dis;?>><BR />
						<LABEL for="toaddress" onmouseout="UnTip()" onmouseover="Tip('To Address, Not plotted on map, for information only');"><?php print get_text('To Address');?>: </LABEL>
						<INPUT ID='toaddress' NAME='frm_to_address' tabindex=1 SIZE='64' TYPE='text' VALUE="<?php print $row['to_address'];?>" MAXLENGTH='512' /><BR />
						<DIV id='loc_warnings' CLASS='text' style='z-index: 1000; display: none;'></DIV>
						<LABEL for="the_lat" onmouseout="UnTip()" onmouseover="Tip('Position - Lat and Lng for Incident position. Click to show all position data.');" onClick='do_coords(document.edit.frm_lat.value ,document.edit.frm_lng.value);'><U>Position</U>: </LABEL>
						<INPUT SIZE='13' TYPE='text' id='the_lat' NAME='show_lat' VALUE='<?php print get_lat($row['lat']);?>' DISABLED>
						<INPUT SIZE='13' TYPE='text' id='the_lng' NAME='show_lng' VALUE='<?php print get_lng($row['lng']);?>' DISABLED>&nbsp;&nbsp;
<?php
								$locale = get_variable('locale');	// 08/03/09
								$gridStr = "";
								switch($locale) { 
									case "0":
										$usng = LLtoUSNG($row['lat'], $row['lng']);
										$gridStr .= "<B><SPAN ID = 'USNG' onClick = 'do_usng()'><U><A HREF='#' TITLE='US National Grid Co-ordinates.'>USNG</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='{$usng}'></TD></TR>";		// 9/13/08, 5/2/09
										break;
								
									case "1":
										$osgb = LLtoOSGB($row['lat'], $row['lng']) ;
										$gridStr .= "<B><SPAN ID = 'OSGB' ><U><A HREF='#' TITLE='United Kingdom Ordnance Survey Grid Reference.'>OSGB</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='{$osgb}' DISABLED ></TD></TR>";		// 9/13/08, 5/2/09
										break;			

									default:																	// 8/10/09
										$utm_str = toUTM("{$row['lat']}, {$row['lng']}");
										$gridStr .= "<B><SPAN ID = 'UTM'><U><A HREF='#' TITLE='Universal Transverse Mercator coordinate.'>UTM</A></U>:&nbsp;</SPAN></B><INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='{$utm_str}' DISABLED ></TD></TR>";		// 9/13/08, 5/2/09
										break;

									}
								print $gridStr;
?>
						<CENTER>
						<SPAN style='text-align: center;'><IMG SRC="glasses.png" BORDER="0"/>: Lookup </SPAN><BR />
						</CENTER>
						</DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>General</LEGEND>
						<DIV style='position: relative;'>
						<LABEL for="scope" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_name'];?>');"><?php print get_text("Incident name");?>: </LABEL>
						<INPUT TYPE='text' id='scope' NAME='frm_scope' SIZE='48' VALUE='<?php print $row['scope'];?>' MAXLENGTH='48' <?php print $dis;?> />						
						<LABEL for="description" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_synop'];?>');"><?php print get_text("Synopsis");?>: </LABEL>
						<TEXTAREA id='description' NAME='frm_description' COLS='45' ROWS='8' <?php print $dis;?>><?php print $row['tick_descr'];?></TEXTAREA>						
						<LABEL for="sel_signals" onmouseout="UnTip()" onmouseover="Tip('Signal');">Signal &raquo; </LABEL>
<?php 
						$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
						$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);

						if (mysql_num_rows($result_sigs)>0) {				// 2/11/11
?>
<SCRIPT>
							function set_signal(theCode) {
								randomnumber=Math.floor(Math.random()*99999999);
								var theurl ="./ajax/get_signal.php?version=" + randomnumber + "&code=" + theCode;
								sendRequest(theurl, signalCB, "");
								function signalCB(req) {
									var theRet = JSON.decode(req.responseText);
									var theText = theRet[0];
									var lh_sep = (document.edit.frm_description.value.trim().length>0)? "\r\n" : "";
									document.edit.frm_description.value+= lh_sep + theText + '\r\n';
									document.edit.frm_description.focus();
									}
								}		// end function set_signal()

							function set_signal2(theCode) {
								randomnumber=Math.floor(Math.random()*99999999);
								var theurl ="./ajax/get_signal.php?version=" + randomnumber + "&code=" + theCode;
								sendRequest(theurl, signalCB, "");
								function signalCB(req) {
									var theRet = JSON.decode(req.responseText);
									var theText = theRet[0];
									var lh_sep = (document.edit.frm_comments.value.trim().length>0)? "\r\n" : "";
									document.edit.frm_comments.value+= lh_sep + theText + '\r\n';
									document.edit.frm_comments.focus();
									}
								}		// end function set_signal()
</SCRIPT>
						<SELECT id='sel_signals' NAME='signals' <?php print $dis; ?> onChange = 'set_signal(this.options[this.selectedIndex].value); this.options[0].selected=true;'>	<!--  11/17/10 -->
							<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
							$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
								print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . $row_sig['text'] . "</OPTION>\n";		// pipe separator
								}
?>
						</SELECT>
<?php
							}						// end if (mysql_num_rows()>0)
						if(get_num_groups()) {
							if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>
								<BR />
								<LABEL onmouseout='UnTip()' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?>:
								<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center; float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';"><B>+</B></SPAN>
								<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';"><B>-</B></SPAN>
								</LABEL>
								<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>
									<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
									<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>
<?php
									$alloc_groups = implode(',', get_allocates(1, $tick_id));
									print get_sub_group_butts(($_SESSION['user_id']), 1, $tick_id) ;
//									$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
//									print get_user_group_butts(($_SESSION['user_id']));
?>
								</DIV>
								<BR />
<?php
								} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>
								<BR />
								<LABEL onmouseout='UnTip()' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?>:
								<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';"><B>+</B></SPAN>
								<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';"><B>-</B></SPAN>
								</LABEL>
								<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>
									<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
									<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>
<?php
									$alloc_groups = implode(',', get_allocates(1, $tick_id));
									print get_sub_group_butts(($_SESSION['user_id']), 1, $tick_id);		
//									$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
//									print get_user_group_butts(($_SESSION['user_id']));
?>
								</DIV>
								<BR />
<?php
								} else {
?>
								<BR />
								<LABEL onmouseout='UnTip()' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?>:
								<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';"><B>+</B></SPAN>
								<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';"><B>-</B></SPAN>
								</LABEL>
								<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>
									<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
									<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>
<?php
									$alloc_groups = implode(',', get_allocates(1, $tick_id));
									print get_sub_group_butts_readonly(($_SESSION['user_id']), 1, $tick_id);	
//									$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
//									print get_user_group_butts_readonly($_SESSION['user_id']);
?>
								</DIV>
								<BR />
<?php
								}
							} else {
?>
							<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">
<?php
							}
						if ($num_mi > 0) {
?>
							<LABEL for="sel_maj_inc" onmouseout="UnTip()" onmouseover="Tip('Major Incidents - sel one if appropriate');"><?php print get_text('Major Incident');?>: <font color='red' size='-1'>*</font></LABEL>
							<SELECT id="sel_maj_inc" NAME='frm_maj_inc'>
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query_mi = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `mi_status` = 'Open' OR `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00' ORDER BY `id` ASC";
								$result_mi = mysql_query($query_mi) or do_error($query_mi, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_mi = stripslashes_deep(mysql_fetch_assoc($result_mi))) {
									$sel = ($row_mi['id'] == $exist_mi) ? "SELECTED" : "";
									print "\t<OPTION VALUE='{$row_mi['id']}' {$sel}>{$row_mi['name']}</OPTION>\n";
									}
?>
							</SELECT>
<?php
							}		// end if()

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
								$where2 .= " AND `a`.`type` = 3";
								}
							}
//	Incident located at facility
						$facSel = "";
						if (!($row['facility'] == NULL)) {					
							$query_fc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
							LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = `a`.`resource_id` )		
							$where2 GROUP BY `fac_id` ORDER BY `name` ASC";		
							$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							$facSel .= "<SELECT id='sel_facility' NAME='frm_facility_id' " . $dis . " onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
							$facSel .= "<OPTION VALUE=0>Not using facility</OPTION>";
							$facSel .= "<SCRIPT>fac_lat[" . 0 . "] = " . get_variable('def_lat') . ";</SCRIPT>";
							$facSel .= "<SCRIPT>fac_lng[" . 0 . "] = " . get_variable('def_lng') . ";</SCRIPT>";
							while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
								$sel = ($row['facility'] == $row_fc['fac_id']) ? " SELECTED " : "";
								$facSel .= "<OPTION VALUE=" . $row_fc['fac_id'] . $sel . ">" . shorten($row_fc['name'], 20) . "</OPTION>";
								$facSel .= "\n<SCRIPT>fac_lat[" . $row_fc['fac_id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
								$facSel .= "\n<SCRIPT>fac_lng[" . $row_fc['fac_id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
								}
							$facSel .= "</SELECT>";
							unset ($result_fc);
							} else { 				// end if (!($row['facility'] == NULL))
							$query_fc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
							LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = `a`.`resource_id` )		
							$where2 GROUP BY `fac_id` ORDER BY `name` ASC";	
							$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							$facSel .= "<SELECT NAME='frm_facility_id' " . $dis . " onChange='document.edit.frm_fac_chng.value = 1; do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
							$facSel .= '<OPTION>Incident at Facility?</OPTION>';
							while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
								$facSel .= "<option value=" . $row_fc['fac_id'] . ">" . shorten($row_fc['name'], 20) . "</option>\n";
								$facSel .= "\n<SCRIPT>fac_lat[" . $row_fc['fac_id'] . "] = " . $row_fc['lat'] . " ;</SCRIPT>\n";
								$facSel .= "\n<SCRIPT>fac_lng[" . $row_fc['fac_id'] . "] = " . $row_fc['lng'] . " ;</SCRIPT>\n";
								}
							$facSel .= "</SELECT>";
							unset ($result_fc);
							}			
					
//	receiving facility
						$recfacSel = "";	
						if (!($row['rec_facility'] == NULL)) {
							$query_rfc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
							LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = a.resource_id )		
							$where2 GROUP BY `fac_id` ORDER BY `name` ASC";		
							$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							$recfacSel .= "<SELECT id='sel_recfacility' NAME='frm_rec_facility_id' " . $dis . " onChange = 'document.edit.frm_fac_chng.value = parseInt(document.edit.frm_fac_chng.value)+ 2;'>";
							$recfacSel .= "<OPTION VALUE=0>No receiving facility</OPTION>";
							while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
								$sel2 = ($row['rec_facility'] == $row_rfc['fac_id']) ? " SELECTED " : "";
								$recfacSel .= "<OPTION VALUE=" . $row_rfc['fac_id'] . $sel2 . ">" . shorten($row_rfc['name'], 20) . "</OPTION>";
								}
							$recfacSel .= "</SELECT>\n";
							unset ($result_rfc);
							} else { 				// end if (!($row['rec_facility'] == NULL))
							$query_rfc = "SELECT *, `f`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities` `f`
							LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = a.resource_id )		
							$where2 GROUP BY `fac_id` ORDER BY `name` ASC";	
							$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							$$recfacSel .= '<option>Receiving Facility?</option>';
							while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
								$recfacSel .= "<option value=" . $row_rfc['fac_id'] . ">" . shorten($row_rfc['name'], 20) . "</option>\n";
								}
							unset ($result_rfc);
							$recfacSel .= "</SELECT>\n";
							}
?>
						<LABEL for="sel_facility" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_facy'];?>');"><?php print get_text("Facility");?>: </LABEL>
						<?php print $facSel;?>
						<LABEL for="sel_recfacility" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_facy'];?>');"><?php print get_text("Receiving Facility");?>: </LABEL>						
						<?php print $recfacSel;?>
						</DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Contacts</LEGEND>
						<DIV style='position: relative;'>
						<LABEL for="phone" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_phone"];?>');"><?php print get_text('Phone');?>: <font color='red' size='-1'>*</font>
						<button type='button' style='float: right;' onClick='Javascript:phone_lkup(document.edit.frm_phone.value);'><img src='./markers/glasses.png' alt='Lookup phone no' /></button></LABEL>
						<INPUT SIZE='48' TYPE='text' ID='phone' NAME='frm_phone' VALUE='<?php print $row['phone'];?>' MAXLENGTH='16' <?php print $dis;?> />
<?php
						if (!(empty($row['phone']))) {					// 3/13/10
							$query  = "SELECT `miscellaneous` FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$row['phone']}' LIMIT 1";
							$result_cons = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							if (mysql_affected_rows() > 0) {
								$row_cons = stripslashes_deep(mysql_fetch_array($result_cons));
?>
								<DIV>Add'l:</DIV><DIV><?php print $row_cons['miscellaneous'];?></DIV><BR />
<?php
								}
							unset($result_cons);
							}	
?>
						<LABEL for="contact" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_caller'];?>');"><?php print get_text("Reported by");?>: </LABEL>
						<INPUT SIZE='48' TYPE='text' id='contact' NAME='frm_contact' VALUE="<?php print $row['contact'];?>" MAXLENGTH='48' <?php print $dis;?>/>
<?php
						$portal_user_control = "<SELECT id='portal_user' NAME='frm_portal_user'>";
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
?>
						<LABEL for="portal_user" onmouseout="UnTip()" onmouseover="Tip('Associated Portal User - this user can see this ticket as a request');"><?php print get_text("Portal User");?>: </LABEL>
						<?php print $portal_user_control;?>
<?php
							} else {
?>
							<INPUT TYPE='hidden' NAME='frm_portal_user' VALUE=0>
<?php
							}
?>
						</DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Call History</LEGEND>
						<DIV style='position: relative;'>
						<LABEL for="911" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_911'];?>');"><?php print get_text("911 Contacted");?>: </LABEL>
						<INPUT SIZE='56' TYPE='text' ID='911' NAME='frm_nine_one_one' VALUE='<?php print $row['nine_one_one']?>' MAXLENGTH='96' <?php print $dis;?> />
						<LABEL for="comments" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_disp'];?>');"><?php print $disposition;?>: </LABEL>
						<TEXTAREA id='comments' NAME='frm_comments' COLS='45' ROWS='8' <?php print $dis;?>></TEXTAREA>
<?php
						$sigControl = "";
						$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
						$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);
						if (mysql_num_rows($result_sigs)>0) {
							$sigControl .= "<SELECT ID='set_signals2' NAME='signals' {$dis} onChange = 'set_signal2(this.options[this.selectedIndex].value); this.options[0].selected=true;'>";
							$sigControl .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
							while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result_sigs))) {
								$sigControl .= "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . $row_sig['text']	. "</OPTION>\n";
								}
							$sigControl .= "</SELECT>";
?>
							<LABEL for="sel_signals2" onmouseout="UnTip()" onmouseover="Tip('Signal');">Signal &raquo; </LABEL>
							<?php print $sigControl;?>
<?php
							}
?>
						</DIV>				
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Time and Date</LEGEND>
						<DIV style='position: relative;'>
						<LABEL for="lock" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_start'];?>');"><?php print get_text("Run Start");?>: 
						<img id='lock' style='float: right;' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'st_unlk(document.edit);' /></LABEL>
						<?php print generate_date_dropdown("problemstart",$row['problemstart'],0, $disallow);?>
<?php
						if (good_date($row['problemend'])) {
?>
							<LABEL for="dateselect_problemend" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_end'];?>');"><?php print get_text("Run End");?>: </LABEL>
							<?php print generate_date_dropdown("problemend",$row['problemend'], $disallow);?>
<?php
							} else {
?>
							<LABEL for="runend1" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_end'];?>');"><?php print get_text("Run End");?>: 
							<input type='radio' style='float: right;' id='runend_unlock' name='re_but' <?php print $dis;?> onClick ='do_end(this.form);' /></LABEL>
							<SPAN style = 'visibility:hidden' ID = 'runend1'><?php print generate_date_dropdown('problemend',0, $disallow);?></SPAN>
<?php
							}
						if (good_date($row['booked_date'])) {
?>
							<LABEL for="dateselect_booked_date" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_booked'];?>');"><?php print get_text("Scheduled Date");?>: </LABEL>
							<?php print generate_date_dropdown("booked_date",$row['booked_date'], $disallow);?> 
<?php
							} else {
?>
							<LABEL for="booked1" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_booked'];?>');"><?php print get_text("Scheduled Date");?>: 
							<input id='bookingselect' style='float: right;' type='radio' name='boo_but' onClick = 'do_booking(this.form);' <?php print $dis;?> /></LABEL>
							<SPAN style = 'visibility:hidden' ID = 'booked1'><?php print generate_date_dropdown('booked_date',0, $disallow);?></SPAN>
<?php
							}
						$selO = ($row['in_status']==$GLOBALS['STATUS_OPEN'])?   "SELECTED" :"";
						$selC = ($row['in_status']==$GLOBALS['STATUS_CLOSED'])? "SELECTED" :"" ;
						$selP = ($row['in_status']==$GLOBALS['STATUS_SCHEDULED'])? "SELECTED" :"" ;

						$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (get_variable('delta_mins')*60));
						$elapsed = my_date_diff(strftime('%Y-%m-%d %H:%M:%S',$row['problemstart']), strftime('%Y-%m-%d %H:%M:%S',$end_date));
?>
						<LABEL for="sel_status" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_status'];?>');"><?php print get_text("Status");?>: </LABEL>
						<SELECT ID='sel_status' NAME='frm_status' <?php print $dis;?>>
							<OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' <?php print $selO;?>>Open</OPTION>
							<OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>' <?php print $selC;?>>Closed</OPTION>
							<OPTION VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' <?php print $selP;?>>Scheduled</OPTION>
						</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR />
						<LABEL for="elapsed" onmouseout="UnTip()" onmouseover="Tip('Incident Elapsed Time');"><?php print get_text("Elapsed Time");?>: </LABEL>
						<INPUT SIZE='56' TYPE='text' ID='elapsed' NAME='frm_elapsed' VALUE='<?php print $elapsed?>' MAXLENGTH='96' DISABLED />
						<LABEL for="updated" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_asof'];?>');"><?php print get_text("Updated");?>: </LABEL>
						<INPUT SIZE='56' TYPE='text' ID='updated' NAME='frm_elapsed' VALUE="<?php print format_date($row['updated'])?>&nbsp;&nbsp;&nbsp;by <?php print $row['tick_user']?>" MAXLENGTH='96' DISABLED />
						</DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Attached Files</LEGEND>
						<DIV style='position: relative;'>						
						<LABEL for="sel_file" onmouseout="UnTip()" onmouseover="Tip('Select a file to upload to assoc');">Choose a file to upload: </LABEL>
						<INPUT id='sel_file' NAME='frm_file' TYPE='file' /></TD>
						<LABEL for="filename" onmouseout="UnTip()" onmouseover="Tip('Give the file a name');">File Name: </LABEL>
						<INPUT ID='filename' NAME='frm_file_title' TYPE='text' SIZE='48' MAXLENGTH='128' VALUE='' /><BR /><BR />
						</DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Actions and Patients</LEGEND>
						<DIV id='theactions' style='width: 100%; height: 150px; overflow-y: scroll;'><?php print show_actions($row[0], "date", !$disallow, FALSE, 0);?></DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Incident Log</LEGEND>						
						<DIV id='thelog' style='width: 100%; height: 150px; overflow-y: scroll;'><?php print show_log($row[0]);?></DIV>
					</FIELDSET>
					<FIELDSET>
						<LEGEND class='text_large text_bold'>Messages</LEGEND>
						<DIV id='themessages' style='width: 100%; height: 150px; overflow-y: scroll;'><?php print list_messages($row[0], "date", FALSE, TRUE);?></DIV>
					</FIELDSET>
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
					</FORM>
<?php
			if($in_win == 1) {
				if($gmaps) {
?>
					<CENTER><DIV id='map_canvas' style='position: relative; top: 10px; border: 1px outset #707070;'></DIV></CENTER><BR />
					<SPAN id='map_caption' class='text bold text_center' style='position: relative; top: 10px; display: inline-block;'><?php print get_variable('map_caption');?></SPAN><BR />
					<DIV id='button_bar' class='but_container'>
						<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>Edit <?php print get_text('Incident');?> <?php print $row['scope'];?></SPAN>
						<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
						<SPAN id='reset_but' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_reset(document.edit);"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN id='sub_but_but' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.edit);"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
					</DIV>
					</DIV>
					</DIV>
<?php
					} else {
?>
					<DIV id='map_canvas' style='border: 1px outset #707070; display: none;'></DIV>
					<DIV id='button_bar' class='but_container'>
						<SPAN id='can_but' roll='button' aria-label='Cancel' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_cancel(document.edit); window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
						<SPAN id='reset_but' roll='button' aria-label='Reset' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_reset(document.edit);"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN id='sub_but_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.edit);"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
					</DIV>
					</DIV>
					</DIV>
<?php							
					}
				} else {
?>
				</DIV>
				<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
					<DIV style='position: fixed; top: 120px; z-index: 4500;'>
						<SPAN id='hist_but' roll='button' tabindex=1 aria-label='History' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_hist_win();"><?php print get_text("History"); ?><BR /><IMG id='can_img' SRC='./images/list.png' /></SPAN>
						<SPAN id='can_but' roll='button' tabindex=2  aria-label='Cancel' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="document.can_Form.submit();"><?php print get_text("Cancel"); ?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
						<SPAN id='reset_but' roll='button' tabindex=3  aria-label='Reset' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_reset(document.edit);"><?php print get_text("Reset"); ?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
						<SPAN id='sub_but_but' roll='button' tabindex=4  aria-label='Submit' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="validate(document.edit);"><?php print get_text("Submit"); ?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
					</DIV>
				</DIV>
<?php
				if ($gmaps || $good_internet) {
?>
					<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
<?php
					if($gmaps) {
?>
						<DIV id='map_canvas' style='border: 1px outset #707070; position: fixed; top: 120px;'></DIV><BR />
						<SPAN id='map_caption' class='text bold text_center' style='display: inline-block;'><?php print get_variable('map_caption');?></SPAN><BR />
<?php
						} else {
?>
						<DIV id='map_canvas' style='border: 1px outset #707070; display: none;'></DIV>
						<SPAN id='map_caption' class='text bold text_center' style='display: none;'><?php print get_variable('map_caption');?></SPAN><BR />
<?php
						}
?>
					</DIV>
					</DIV>
					</DIV>
<?php
					}
				}
			if($in_win == 0) {
				$from_left = 450;
				$from_top = 220;
				$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
				print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, $tick_id, 0, 0, 0);
				}
?>
			<FORM NAME='can_Form' ACTION="main.php">
			<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['id'];?>">
			</FORM>

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
		if(loc == 1) { document.forms[0].frm_ngs.disabled=false; document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value); document.forms[0].frm_ngs.disabled=true; }
		if(loc == 2) { document.forms[0].frm_ngs.disabled=false; document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value); document.forms[0].frm_ngs.disabled=true; }			
		}

	function do_grids(theForm) {								//12/13/10
		var loc = <?php print get_variable('locale');?>;
		if (loc == 0) {do_usng(theForm) ;}
		if (loc == 1) {do_utm (theForm);}
		if (loc == 2) {do_osgb (theForm);}
		}
		
	function do_usng(theForm) {								// 8/23/08, 12/5/10
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);	// US NG
		}

	function do_utm (theForm) {
		var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
		var utm_out = ll_in.toUTMRef().toString();
		temp_ary = utm_out.split(" ");
		theForm.frm_ngs.value = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
		}

	function do_osgb (theForm) {
		var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
		var osgb_out = ll_in.toOSRef();
		theForm.frm_ngs.value = osgb_out.toSixFigureString();
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
			pt_to_map (document.edit, result[7].trim(), result[8].trim());				// 1/19/09
			
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
		if ((document.edit.frm_city.value.trim()==""  || document.edit.frm_state.value.trim()=="")) {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var myAddress = document.edit.frm_street.value.trim() + ", " +document.edit.frm_city.value.trim() + " "  +document.edit.frm_state.value.trim();
		control.options.geocoder.geocode(myAddress, function(results) {
			var r = results[0]['center'];
			var theLat = parseFloat(r.lat.toFixed(6));
			var theLng = parseFloat(r.lng.toFixed(6));
			pt_to_map (document.edit, theLat, theLng);
			if((theLat) && (theLng)) {		//	8/14/13
				find_warnings(theLat, theLng)					
				}	
			});
		}				// end function loc_lkup()
		
	function pt_to_map (my_form, lat, lng) {
		if(!$('map_canvas')) {return; }
		if(marker) {map.removeLayer(marker);}
		if(myMarker) {map.removeLayer(myMarker);}
		var theLat = parseFloat(lat).toFixed(6);
		var theLng = parseFloat(lng).toFixed(6);
		document.edit.frm_lat.value=theLat;	
		document.edit.frm_lng.value=theLng;		
		document.edit.show_lat.value=do_lat_fmt(theLat);
		document.edit.show_lng.value=do_lng_fmt(theLng);	
		var loc = <?php print get_variable('locale');?>;
		if(loc == 0) { document.edit.frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
		if(loc == 1) { document.edit.frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
		if(loc == 2) { document.edit.frm_ngs.value=LLtoUTM(theLat, theLng, 5); }
		var iconurl = "./our_icons/yellow.png";
		icon = new baseIcon({iconUrl: iconurl});	
		marker = L.marker([theLat, theLng], {icon: icon});
		marker.addTo(map);
		map.setView([theLat, theLng], 16);
		}				// end function pt_to_map ()

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
if($gmaps || $good_internet) {
?>
	<SCRIPT>
	var latLng;
	var boundary = [];			//	exclusion zones array
	var bound_names = [];
	var cmarkers;

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
	if(in_win == 1) {
		set_fontsizes(viewportwidth, "popup");
		} else {
		set_fontsizes(viewportwidth, "fullscreen");
		}
	if(window.isinwin) {set_fontsizes(viewportwidth, "popup");} else {set_fontsizes(viewportwidth, "fullscreen");}
	if(window.isinwin) {mapWidth = viewportwidth * .95;} else {mapWidth = viewportwidth * .40;}
	mapHeight = viewportheight * .55;
	if(window.isinwin) {outerwidth = viewportwidth * .95;} else {outerwidth = viewportwidth * .99;}
	outerheight = viewportheight * .95;
	if(window.isinwin) {colwidth = outerwidth;} else {colwidth = outerwidth * .40;}
	colheight = outerheight * .95;
	leftcolwidth = viewportwidth * .45;
	winleftcolwidth = viewportwidth * .90;
	rightcolwidth = viewportwidth * .35;
	fieldwidth = leftcolwidth * .40;
	medfieldwidth = colwidth * .20;	
	smallfieldwidth = colwidth * .15;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('leftcol_inwin')) {$('leftcol_inwin').style.width = winleftcolwidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
	if($('rightcol')) {$('rightcol').style.width = colwidth + "px";}
	if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
	if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
	if($('map_caption')) {$('map_caption').style.width = mapWidth + "px";}
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	var initZoom = <?php print get_variable('def_zoom');?>;
	init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	var bounds = map.getBounds();	
	var zoom = map.getZoom();
	var doReverse = <?php print intval(get_variable('reverse_geo'));?>;
	function onMapClick(e) {
		if(doReverse == 0) {return;}
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
	} else {
?>
	<SCRIPT>
	var latLng;
	var boundary = [];			//	exclusion zones array
	var bound_names = [];
	var cmarkers;

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
	set_fontsizes(viewportwidth, "fullscreen");
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .45;
	colheight = outerheight * .95;
	if(in_win) {
		var leftcolwidth = viewportwidth * .99;
		var rightcolwidth = viewportwidth * .01;
		} else {
		var leftcolwidth = rightcolwidth = colwidth;
		}
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
	</SCRIPT>
<?php		
	}
?>
</BODY>
</HTML>
