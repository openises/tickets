<?php
$iw_width = 	"300px";		// map infowindow with

/*
6/9/08  added  'Closed Calls' button
7/27/08 handle deleted status values
8/02/08 provide link to dispatch function
8/3/08  add assign data to unit IW's
8/6/08  added function do_tracks
8/15/08 mysql_fetch_array to mysql_fetch_assoc - performance
8/22/08 added usng position
8/24/08 revised sort order to include severity
8/25/08 added responders TITLE display
8/25/08 revised map control type to small - for TB
9/8/08  lat/lng to CG format
9/12/08 added USNG PHP functions
9/14/08 added js trim()
10/9/08 added check for div defined - IE JS pblm
10/14/08 changed reference to usng.js
10/15/08 changed 'Comments' to 'Disposition'
10/15/08 corrections re LL2NGS
10/16/08 added traffic functions
10/17/08 added hide_Units()
10/21/08 added edit link in infowindow
10/21/08 added  rand into link, istest as global
11/1/08 added prefix
11/06/08 sql error
11/6/08 missing table close tags corrected, timer for mini-map
11/29/08 added streetview
12/24/08 added GOverviewMapControl()
1/6/09  revised unit types for variable types
1/9/09  use icons subdir
1/10/09 dollar function added
1/17/09 caption changed to 'situation'
1/21/09 - drop aprs field fm unit types
1/23/09 tracks correction
1/25/09 do/don't show serial no.
1/27/09 revised sort order
1/29/09 revised icons array index
2/2/09 order sorts 'status=completed' last, unit status fix for non-existent keys.
2/11/09 added streetview function, removed redundant dollar function
2/12/09, 2/14/09 added persistence to show/hide units
2/13/09 added to_str() for no. units > 25
2/21/09 dropped infowindow from map
2/24/09 handle no-position units
3/2/09 corrected table caption
3/3/09 underline units sans position
3/16/09	get current aprs, instam updates
3/23/09 null added as possible value
3/23/09 is_float() replaces settype(), the latter not detecting 0, fix quotes
3/25/09 added time validation for remote sources, my_is_float()
4/2/09 correction for sidebar letters, added default zoom handling, closed ticket display interval
5/4/09 my_is_float() repl is_float
7/9/09 popups, per AH, COLOR='blue' correction
7/16/09	protocol display
7/27/09	'id' ambiguity resolved
7/29/09 Added Gtrack, Locatea and Google Latitude tracking sources, revised mobile speed icon display
7/29/09 Modified code to get tracking data, updated time and speed to fix errors. variable for updated and speed is now set before query result is unset. 
8/1/09 Added Facilities display
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
8/3/09 Added code to get locale variable and change USNG/OSGB/UTM dependant on variable in tabs and sidebar.
8/3/09 Revised function popup_ticket to remove spurious listener.
8/7/09 Revised show/hide units and show hide incident markers
8/11/09 Revised code for incident popup to use function my_is_float to capture out units with no location
8/11/09 Added code to show responding units on incident details screen.
8/12/09 Revised MYSQL queries where there is an ambiguity between field names (description) in Ticket and In_types tables to correct ticket display
8/12/09	toUTM() parameters corrected
8/13/09	shorten() disposition, etc. 
8/19/09 drawCircle() added
9/29/09 Added Handling for Special Tickets
10/8/09 Index in list and on marker changed to part of name after / for both units and facilities
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs for both units and facilities
10/21/09 Added hide/show for unavailable units in Situation map.
10/21/09 Added check for any closed or special incidents on the database before showing the buttons in the situation screen.
10/27/09 Added check for special incidents being due and bring to current situation screen if due and mark with * in list.
10/27/09 Added Booked date to Info Window tab 1 for ticket.
10/28/09 Added receiving facility to Info Window tab 1 for ticket
10/30/09 Added dispatch times and miles to ticket print, fixed action/patient print
10/30/09 Removed period after index in sidebar
11/06/09 Changed "Special" Incidents to "Scheduled" Incidents.
11/10/09 fixes to facilities display by AS
11/11/09 top/bottom anchors added
11/20/09 sort order handle, name
12/17/09 added unit status update functions
12/19/09 disable for guest priv's
1/1/10 style applied to <select>, relocated 'Closed incidents' link
1/2/10 $on_click implemented to release tr onclick event in favor of td onclick
1/7/10 re-arranged incident display, added 'call-taker' alias
1/27/10 map caption to page top
2/1/10 color-coded unit sidebar added, unit display order by # incidents assigned
2/6/10 function get_status_sel() moved to FIP
2/8/10 added units color-coding legend, calculate height allowed/required for units sidebar div
2/19/10 added offset handling for large closed lists 
3/2/10 add unit sidebar row hiding
3/8/10 revised SQL and JS for unit unavailable row show/hide, add show/hide delay
3/12/10 revise popup height
4/4/10 rewrite to_session ()
4/8/10 identify $chgd_unit
4/11/10 added count of units assigned and blink if none
5/11/10 disallow user/operator unit edit
5/15/10 added 'closed' handling
5/17/10 significant re-do of the ticket sidebar click handling
6/11/10 user-selectable unit sort added
6/25/10 responder SQL corrections for assigns count
6/26/10 handle 911 contact data
7/18/10 use responder position data, sidebar height
7/27/10 unit() user limitations added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/2/10 Revised alignment of ticket, responder and facility text in sidebar. Revised main query to specifically name street and city fields from tickets table to avoid confolict with responder fields of same name.
8/5/10 corrections applied re facilities  sidebar click handling
8/12/10 check $_POST for sort order
8/13/10 map.setUIToDefault();
8/27/10 use can_edit() in IW
8/30/10 dispatch status display added
9/29/10 use mysql2timestamp() for conversion
11/26/10 get_speed() call replaces $the_bull generation
11/28/10 revised session persistance for show/hide facilities.
11/29/10 Added scheduled calls to view list.
11/29/10 locale == 2 handled
11/30/10 get_text("Patient"), disposition added
12/02/10 Revised show hide for unavailable and facilities to correct persistance issues
12/02/10 Added persistance for the tickets list
12/03/10 Completely revised show hide units. Added show hide by category and revised hide and show incidents to remove units from these functions.
12/8/10 knl_files handling revised to accommodate missing directory
12/9/10 on_scene_miles added
3/15/11 Various classes added to support user variable css color scheme, Revised Infowindow close event to recenter and rezoom based on zoomed position when inforwindow was opened.
3/19/11 unit, facilities index expanded to 6 char's
3/22/11 icon string set for low-order three chars
4/1/11 lift restriction re operators and non-owned incidents - pending incident-owner corrections
4/11/11 Added replace quotes function to all text fields in InfoWindows and sidebars to catch double quotes in entries.
4/18/11 Added replace_quotes() to all text fields to catch double quotes stored in fields.
4/18/11 Where clause updated in all major queries to support Group functionality
4/22/11 addslashes added to accommodate apostrophe's in unit and incident names
4/27/11 addslashes added to facilities handling
5/3/11 Revised text for sort radio buttons in Facilities sidebar.
5/5/11 added line count test for 'Change display' location.
5/11/11 Added internal Tickets Tracker
5/19/11 facilities email fix per AH email
6/10/11 Added Groups and Boundaries
6/28/11 do_kml corrections applied
8/1/11  do_landb revised and relocated
2/18/12 minor fix
4/5/12 revised for auto-refresh trigger notification
4/12/12 Revised Regions control buttons
6/20/12 applied get_text() to Units
6/21/12 boolean points => got_points
6/22/12 set limit to setCenter zoom
10/23/12 Added code for messaging
10/29/2012 Beds handling added to facilities
11/3/2012 facilities as-of date dropped unixtimestamp in favor of substring
11/30/2012 significant re-do, dropping unixtimestamp in favor of strtotime.  See FIP
1/30/2013 - 0.999999 -> $GLOBALS['NM_LAT_VAL'] 
3/3/2013 function do_ticket_wm added 
3/30/2013 several revisions to accommodate version 20C per AH
5/21/2013 revised get_elapsed_time calls to pass entire row
5/24/2013 hid up/down arrows if in print mode
5/26/2013 added 'hide_booked' variable handling
5/30/13 Implement catch for when there are no allocated regions for current user. 
5/31/2013 tracking speed display corrections made
6/1/2013 revised 'contact us' addr to user setting value
7/2/2013 revised to take responder as-of timestamp per server time
7/30/13 Revised to solve issue with showing and hiding individual Facility categories
9/10/13 Added "Address About" and "To Address" fields, fixed on click event in maps mode for ticket entered in no maps mode.
12/23/13 Revised sidebar numbering and unit marker creation to separate units markers from ticket markers in preparation for dynamic unit markers
1/3/14 Added Road Condition Alert markers to map
1/30/14 Added specific Unit dummy marker to fix problem with not IW when clicking unit with dummy position
*/

$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
/*
{$nature}
<?php print $nature;?>

{$disposition} 
<?php print $disposition;?>

{$patient} 
<?php print $patient;?>

{$incident}
<?php print $incident;?>

{$incidents}
<?php print $incidents;?>

global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
*/


//	{ -- dummy

$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();

function do_updated ($instr) {		// 11/3/2012
	return substr($instr, 8, 8);
	}

//	} { -- dummy

function show_ticket($id,$print='false', $search = FALSE) {								/* show specified ticket */
	global $iw_width, $istest, $zoom_tight, $nature, $disposition, $patient, $incident, $incidents, $col_width;	// 12/3/10, 8/4/11
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$id})</I>" : "";			// 1/25/09, 2/18/12
	$istest = FALSE;
	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
										// 1/7/10
	$query = "SELECT *,
		`problemstart` AS `my_start`,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		`date` AS `date`,
		`booked_date` AS `booked_date`,		
		`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`street` AS `rec_fac_street`,
		`rf`.`city` AS `rec_fac_city`,
		`rf`.`state` AS `rec_fac_state`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09


	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";	// 8/18/09
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_array($result));

    $locale = get_variable('locale');    // 10/29/09
    switch($locale) {
        case "0":
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
        break;

        case "1":
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);    // 8/23/08, 10/15/08, 8/3/09
        break;
   
        case "2":
        $coords =  $row['lat'] . "," . $row['lng'];                                    // 8/12/09
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);    // 8/23/08, 10/15/08, 8/3/09
        break;

        default:
        print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
        }


	if ($print == 'true') {				// 1/7/10

		print "<TABLE BORDER='0'ID='left' width='1000px'>\n";		//
		print "<TR CLASS='print_TD'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>" . $row['scope'] . "</B>" . $tickno . "</TD></TR>\n";
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> 
					<TD ALIGN='left'>" . get_severity($row['severity']);
		print 		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($row['in_types_id']);
		print "</TD></TR>\n";
	
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left'>{$row['protocol']}</TD></TR>\n";		// 7/16/09
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>	
				<TD ALIGN='left'>" .  $row['tick_street'];
		print "<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 300px; font-size: 1.5em; font-weight: bold; border: 2px outset #707070;'></DIV>";	
		print "</TD></TR>\n";		
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("About Address") . ":</TD>	
				<TD ALIGN='left'>" .  $row['address_about'] . "</TD></TR>\n";	//	9/10/13
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("To Address") . ":</TD>	
				<TD ALIGN='left'>" .  $row['to_address'] . "</TD></TR>\n";	//	9/10/13
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("City") . ":</TD>		
				<TD ALIGN='left'>" .  $row['tick_city'];
		print 		"&nbsp;&nbsp;" .  $row['tick_state'] . "</TD></TR>\n";
		print "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>
				<TD ALIGN='left'>" .  nl2br($row['tick_descr']) . "</TD></TR>\n";	//	8/12/09

		print "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>
				<TD ALIGN='left'>" .  nl2br($row['nine_one_one']) . "</TD></TR>\n";	//	8/12/09

		$elapsed = get_elapsed_time ($row);
		print "<TR CLASS='print_TD'><TD ALIGN='left'>" . get_text("Status") . ":</TD>	
				<TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$elapsed}</TD></TR>\n";
		print "<TR CLASS='print_TD'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>
				<TD ALIGN='left'>" . $row['contact'] . "</TD></TR>\n";
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>		
				<TD ALIGN='left'>" . format_phone ($row['phone']) . "</TD></TR>\n";
		$by_str = ($row['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($row['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
		print "<TR CLASS='print_TD'><TD ALIGN='left'>" . get_text("Written") . ":</TD>	
				<TD ALIGN='left'>" . format_date_2(strtotime($row['date'])) . $by_str;
		print 		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2(strtotime($row['updated'])) . "</TD></TR>\n";
		print empty($row['booked_date']) ? "" : "<TR CLASS='print_TD'><TD ALIGN='left'>Scheduled date:</TD>	
				<TD ALIGN='left'>" . format_date_2(strtotime($row['booked_date'])) . "</TD></TR>\n";	// 10/6/09	
		print "<TR CLASS='print_TD' ><TD ALIGN='left' COLSPAN='2'>&nbsp;
				<TD ALIGN='left'></TR>\n";			// separator
		print empty($row['fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>{$incident} at Facility:</TD>	
				<TD ALIGN='left'>" .  $row['fac_name'] . "</TD></TR>\n";	// 8/1/09, 3/27/10
		$rec_fac_details = empty($row['rec_fac_name'])? "" : $row['rec_fac_name'] . "<BR />" . $row['rec_fac_street'] . "<BR />" . $row['rec_fac_city'] . "<BR />" . $row['rec_fac_state'];
		print empty($row['rec_fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>Receiving Facility:</TD>	
				<TD ALIGN='left'>" .  $rec_fac_details . "</TD></TR>\n";	// 10/6/09	
		print empty($row['comments'])? "" : "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>
				<TD ALIGN='left'>" .  replace_quotes(nl2br($row['comments'])) . "</TD></TR>\n";	
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Run Start") . ":3758</TD>				
				<TD ALIGN='left'>" . format_date_2(strtotime($row['problemstart']));
//		$elapsed_str = (!(empty($closed)))? $elapsed : "" ;				
		$elapsed_str = get_elapsed_time ($row);			
		print	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date_2(strtotime($row['problemend'])) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	
		$locale = get_variable('locale');	// 08/03/09
		switch($locale) { 
			case "0":
				$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
				break;
	
			case "1":
				$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);	// 8/23/08, 10/15/08, 8/3/09
				break;
		
			case "2":
				$coords =  $row['lat'] . "," . $row['lng'];									// 8/12/09
				$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
				break;
	
			default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
	
		print "<TR CLASS='print_TD'><TD ALIGN='left' >" . get_text("Position") . ": </TD>		
				<TD ALIGN='left'>" . get_lat($row['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($row['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08
						// 3/30/2013
 		print "<TR CLASS='print_TD'><TD colspan=2 ALIGN='left'>";
 		print show_log ($row[0]);				// log
 		print "</TD></TR>";
 		print "<TR CLASS='print_TD' STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $row['lat'] . "</SPAN><SPAN ID='oldlng'>" . $row['lng'] . "</SPAN></TD></TR>";
 		print "<TR><TD COLSPAN=99>";
 		print show_assigns(0, $row[0]);				// 'id' ambiguity - 7/27/09 - new_show_assigns($id_in)
 		print "</TD></TR>";		
 		print "<TR CLASS='print_TD'><TD colspan=99 ALIGN='left'>";
 		print show_actions($row['tick_id'], "date", FALSE, TRUE);		// lists actions and patient data, print - 10/30/09

		print "</TD></TR>";
		print "</TABLE>\n";
		print "<BR /><BR /><BR />";
// =============== 10/30/09 

		function my_to_date($in_date) {			// date_time format to user's spec
//			$temp = mktime(substr($in_date,11,2),substr($in_date,14,2),substr($in_date,17,2),substr($in_date,5,2),substr($in_date,8,2),substr($in_date,0,4));
			$temp = mysql2timestamp($d1);		// 9/29/10
			return (good_date_time($in_date)) ?  date(get_variable("date_format"), $temp): "";		// 
			}
/*
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `facility_id` IS NOT NULL LIMIT 1";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$facilities = mysql_affected_rows()>0;		// set boolean in order to avoid waste space

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `start_miles` IS NOT NULL  LIMIT 1";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$miles = mysql_affected_rows()>0;		// set boolean in order to avoid waste space
		unset($result_temp);

		$query = "SELECT *,
		`as_of` AS `as_of`,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
		`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`,
		`t`.`scope` AS `theticket`,
		`t`.`description` AS `thetickdescr`,
		`t`.`status` AS `thestatus`,
		`t`.`_by` AS `call_taker`,
		`r`.`id` AS `theunitid`,
		`r`.`name` AS `theunit` ,
		`f`.`name` AS `thefacility`,
		`g`.`name` AS `the_rec_facility`,
		`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`$GLOBALS[mysql_prefix]assigns`.`facility_id` = `f`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `g` ON (`$GLOBALS[mysql_prefix]assigns`.`rec_facility_id` = `g`.`id`)
		WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = $id
		ORDER BY `theunit` ASC ";																// 5/25/09, 1/16/08

		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_affected_rows()>0) {
			print "<P><TABLE  CLASS='print_TD' BORDER = 1 CELLPADDING = 2 STYLE = 'border-collapse: collapse;'>\n";
			print "<TR><TH>" . get_text("Units") . "</TH><TH>D</TH><TH>R</TH><TH>E</TH>";
			print ($facilities)? "<TH>FE</TH><TH>FA</TH>": "";
			print "<TH>C</TH>";
			print ($miles)? "<TH>M/S</TH><TH>M/OS/E</TH>": "";
			print "</TR>";
			
			while ( $asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result))){
				print "<TR>";			
				print "<TD>" . shorten($asgn_row['theunit'], 24) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['dispatched']) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['responding']) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['on_scene']) . "</TD>";
				print ($facilities)? "<TD>" . my_to_date($asgn_row['u2fenr']) . "</TD>": "";
				print ($facilities)? "<TD>" . my_to_date($asgn_row['u2farr']) . "</TD>": "";
				print "<TD>" . my_to_date($asgn_row['clear']) . "</TD>";
				print ($miles)? "<TD>" . my_to_date($asgn_row['start_miles']) . "</TD>": "";
				print ($miles)? "<TD>" . my_to_date($asgn_row['on_scene_miles']) . "</TD>": "";	// 12/9/10
				print ($miles)? "<TD>" . my_to_date($asgn_row['end_miles']) . "</TD>": "";
				print "</TR>\n";				
				}		// end while () $asgn_row = ...
			print "</TABLE>\n";
			}				// end if (mysql_affected_rows()>0 
*/
		
// ==============
			print "\n";
		return;
		}		// end if ($print == 'true')
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">
	<TR VALIGN="top"><TD CLASS="print_TD, even" ALIGN="left" style='<?php print get_variable('map_width');?>px;'>
	<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 100%; font-size: 1.5em; font-weight: bold; border: 2px outset #707070;'></DIV>	

<?php

	print do_ticket($row, $col_width, $search) ;				// 2/25/09
	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */
	$column_arr = explode(',', get_msg_variable('columns'));		
	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map_canvas' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		<BR />
		<SPAN ID='grid_id' onClick='toglGrid()'><U>Grid</U></SPAN>
		<SPAN ID='do_sv' onClick = 'sv_win(document.sv_form)' STYLE = 'margin-left: 20px' ><u>Street view</U></SPAN>";
	print ($zoom_tight)? "<SPAN  onClick= 'zoom_in({$row['lat']}, {$row['lng']}, {$zoom_tight});' STYLE = 'margin-left:20px'><U>Zoom</U></SPAN>\n" : "";	// 3/27/10	
		
	print "</TD></TR>";	// 11/29/08

	print "<FORM NAME='sv_form' METHOD='post' ACTION=''><INPUT TYPE='hidden' NAME='frm_lat' VALUE=" .$row['lat'] . ">";		// 2/11/09
	print "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=" .$row['lng'] . "></FORM>";

	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n
		<TR><TD ALIGN='center'><BR /><FONT SIZE='-1'>Click map point for distance information.</FONT></TD></TR>\n";
	print "</TABLE>\n";
	if((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
		print "<DIV style='width:" . get_variable('map_width') . "px; background-color: #CECECE;'>
				<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'><BR />
					<SPAN id='all_read_but' class='plain' style='float: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='read_status(\"read\", 0, \"ticket\", " . $row['tick_id'] . ", 0);'>Mark All Read</SPAN>	
					<SPAN id='all_unread_but' class='plain' style='float: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='read_status(\"unread\", 0, \"ticket\", " . $row['tick_id'] . ", 0);'>Mark All Unread</SPAN>	
					<SPAN id='waste_but' class='plain' style='float: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='get_wastebin();'>Wastebasket</SPAN>	
					<SPAN id='inbox_but' class='plain' style='float: none; display: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='get_inbox();'>Inbox</SPAN><BR /><BR />			

				</DIV>";
		print "	<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'>
					<SPAN style='vertical-align: middle; text-align: center; font-size: 22px; color: #FFFFFF;'>Messages for Ticket " . $row['tick_id'] . "</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN ID='the_box' style='font-size: 14px; color: blue; background-color: #FFFFFF;'>Showing Inbox</SPAN><BR />
					<SPAN style='font-size: 10px;'>Click Column Heading to sort</SPAN><BR />
				</DIV>";

		print	"<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'>
					<FORM NAME='the_filter'>			
						<SPAN style='vertical-align: middle; text-align: center;'><B>FILTER: &nbsp;&nbsp;</B><INPUT TYPE='text' NAME='frm_filter' size='60' MAXLENGTH='128' VALUE=''>
						<SPAN id = 'filter_box' class='plain' style='float: none; vertical-align: middle;' onMouseover = 'do_hover(this);' onMouseout='do_plain(this);' onClick='do_filter(the_ticket,\"\")'>&nbsp;&nbsp;&#9654;&nbsp;&nbsp;GO</SPAN>
						<SPAN id = 'the_clear' class='plain' style='float: none; display: none; vertical-align: middle;' onMouseover = 'do_hover(this);' onMouseout='do_plain(this);' onClick='clear_filter(the_ticket,\"\")'>&nbsp;&nbsp;X&nbsp;&nbsp;Clear</SPAN>
						</SPAN><BR /><BR />
					</FORM>
				</DIV>";
		print "	<TABLE cellspacing='0' cellpadding='0' style='width: 98%; background-color: #CECECE;'>
					<TR style='background-color: #CECECE; color: #FFFFFF; width: 100%;'>";
						$print = "";
	//					$print .= (in_array('1', $column_arr)) ? "<TD id='ticket' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`ticket_id`',filter)\">Tkt</TD>" : "";					
						$print .= (in_array('2', $column_arr)) ? "<TD id='type' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`msg_type`',filter)\">Typ</TD>" : "";				
						$print .= (in_array('3', $column_arr)) ? "<TD id='from' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`fromname`',filter)\">From</TD>" : "";				
						$print .= (in_array('4', $column_arr)) ? "<TD id='recipients' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`recipients`',filter)\">To</TD>" : "";
						$print .= (in_array('5', $column_arr)) ? "<TD id='subject' class='cols_h' NOWRAP style='width: 20%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`subject`',filter)\">Subject</TD>" : "";					
						$print .= (in_array('6', $column_arr)) ? "<TD id='message' class='msg_col_h' NOWRAP style='width: 40%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`message`',filter)\">Message</TD>" : "";
						$print .= (in_array('7', $column_arr)) ? "<TD id='date' class='cols_h' style='width: 10%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`date`',filter)\">Date</TD>" : "";
						$print .= (in_array('8', $column_arr)) ? "<TD id='owner' class='cols_h' NOWRAP style='width: 7%;' onClick=\"sort_switcher('ticket', the_selected_ticket,'','`_by`',filter)\">Owner</TD>" : "";
						$print .= "<TD class='cols_h' NOWRAP style='width: 4%;'>Del</TD>";
						print $print;
		print "		</TR>						
				</TABLE>";
		print "<DIV ID = 'message_list' style='position: relative; background-color: #CECECE; overflow-y: scroll; overflow-x: hidden; height: 500px; border: 2px outset #FEFEFE; width: 100%;'></DIV>";
		print "</DIV>";	
	}
	print "</TD></TR>";
	print "</TABLE>\n";	
	$lat = $row['lat']; $lng = $row['lng'];
?>
	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule_V3.js" type="text/javascript"></SCRIPT> 
	<SCRIPT>
	var grid;
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

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

	function find_warnings(tick_lat, tick_lng) {	//	9/10/13
		randomnumber=Math.floor(Math.random()*99999999);
		var theurl ="./ajax/loc_warn_list.php?version=" + randomnumber + "&lat=" + tick_lat + "&lng=" + tick_lng;
		sendRequest(theurl, loc_w_cb, "");
		function loc_w_cb(req) {
			var the_warnings=JSON.decode(req.responseText);
			var the_count = the_warnings[0]
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

	function file_window(id) {										// 9/10/13
		var url = "file_upload.php?ticket_id="+ id;
		var nfWindow = window.open(url, 'NewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { nfWindow.focus(); }, 1);
		}

	var grid_bool = false;		
	function toglGrid() {						// toggle
		grid_bool = !grid_bool;
		if (grid_bool)	{ grid = new Graticule(map); }
		else 			{ grid.setMap(null); }
		}		// end function toglGrid()
	
	function zoom_in (in_lat, in_lng, in_zoom) {				// 3/27/10
//		map.setCenter(new google.maps.LatLng(in_lat, in_lng), in_zoom );
//		var marker = new google.maps.Marker(map.getCenter());				// marker to map center
//		var myIcon = new GIcon() // 4035;
//		myIcon.image = "./markers/sm_red.png";
//		???.setMap(map);		
		}				// end function zoom in ()		 		


	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
	var thisMarker = false;

<?php
if ((($lat == $GLOBALS['NM_LAT_VAL']) && ($lng == $GLOBALS['NM_LAT_VAL'])) || (($lat == "") || ($lat == NULL)) || (($lng == "") || ($lng == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
	$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
	$icon_file = "./our_icons/question1.png";
	}
else {
	$icon_file = "./markers/crosshair.png";
	}
?>

//													no callback, read-only		
		map = gmaps_v3_init(null, 'map_canvas', 
			<?php echo $lat;?>, 
			<?php echo $lng;?>, 
			<?php echo get_variable('def_zoom');?>, 
			'<?php echo $icon_file;?>',  
			<?php echo get_variable('maptype');?>, 
			true);		
		find_warnings(<?php print $lat;?>, <?php print $lng;?>);	//	9/10/13
	
// ====================================Add Responding Units to Map 8/1/09================================================

	var icons=[];	
	icons[1] = "./our_icons/white.png";		// normal
	icons[2] = "./our_icons/black.png";		// green

/*
	var baseIcon = new GIcon();				// 4172
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var unit_icon = new GIcon(baseIcon);				// 4179
*/	
	unit_icon = icons[1];
	var bounds = new google.maps.LatLngBounds();		// Initialize 
	
function createMarker(unit_point, number) {		// unit marker
	bounds.extend(unit_point);	
	var unit_marker = new google.maps.Marker({position: unit_point, map: map, icon: unit_icon});			
	
	var html = number;	// Show this markers index in the info window when it is clicked

	google.maps.event.addListener(unit_marker, "click", function() {
		unit_marker.openInfoWindowHtml(html);
		});
	return unit_marker;
	}	
	
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
	$responder_id=($row['responder_id']);
	if ($row['clear'] == NULL) {

		$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
		$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row_unit = mysql_fetch_array($result_unit)){
		$unit_id=($row_unit['id']);
		$mobile=($row_unit['mobile']);
		if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {

		if ($mobile == 1) {
//			echo "var unit_icon = new GIcon(baseIcon);		// 4206\n";
			echo "var unit_icon_url = \"./our_icons/gen_icon.php?blank=0&text=RU\";\n";
			echo "unit_icon.image = unit_icon_url;\n";
			echo "var unit_point = new google.maps.LatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
			echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
			echo "unit_marker.setMap(map);\n";
			echo "\n";
		} else {
//			echo "var unit_icon = new GIcon(baseIcon);		// 4214\n";
			echo "var unit_icon_url = \"./our_icons/gen_icon.php?blank=4&text=RU\";\n";
			echo "unit_icon.image = unit_icon_url;\n";
			echo "var unit_point = new google.maps.LatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
			echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
			echo "unit_marker.setMap(map);\n";
			echo "\n";
		}	// end inner if
		}	// end middle if
		}	// end outer if
		}	// end inner while
	}	//	end outer while

// =====================================End of functions to show responding units========================================================================
// ====================================Add Facilities to Map 8/1/09================================================
?>

	var icons=[];	
	var g=0;

	var fmarkers = [];
/*
	var baseIcon = new GIcon();		// 4236
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);				// 4243
	fac_icon.image = icons[1];
*/
function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new google.maps.Marker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	google.maps.event.addListener(fac_marker, "click", function() {
		fac_marker.openInfoWindowHtml(fac_html);
		});
	return fac_marker;
}

<?php
	$query_fac = "SELECT *,`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]facilities`.id AS fac_id, 
		`$GLOBALS[mysql_prefix]facilities`.description AS facility_description, 
		`$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, 
		`$GLOBALS[mysql_prefix]facilities`.name AS facility_name 
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	while($row_fac = mysql_fetch_array($result_fac)){

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){

		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index = substr($fac_temp[count($fac_temp) -1] , -6, strlen($fac_temp[count($fac_temp) -1]));	// 3/19/11
		
		print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
	
		$fac_id=($row_fac['id']);
		$fac_type=($row_fac['icon']);
	
		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];
	
		if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {
	
			$fac_tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date_2(strtotime($row_fac['updated'])) . "</TD></TR>";
			$fac_tab_1 .= "</TABLE>";
	
			$fac_tab_2 = "<DIV style='max-height: 200px; overflow: auto;'><TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
			$fac_tab_2 .= "</TABLE></DIV>";
			
?>
/*
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
*/
			var myfacinfoTabs = "<?php print $fac_tab_1;?>";
<?php	
//				echo "var fac_icon = new GIcon(baseIcon);		// 4317\n";
				echo "var fac_type = $fac_type;\n";
?>
		var origin = ((fac_sym.length)>3)? (fac_sym.length)-3: 0;								// pick low-order three chars 3/22/11
		var iconStr = fac_sym.substring(origin);
<?php				
				echo "var fac_icon_url = \"./our_icons/gen_fac_icon.php?blank=$fac_type&text=\" + (iconStr) + \"\";\n";
//				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new google.maps.LatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon_url);\n";
				echo "fac_marker.setMap(map);\n";
				echo "\n";
			}	// end if my_is_float
	
?>
			g++;
<?php
		}	// end while

}
// ============================== End of functions to show facilities =======================================

//	$street = empty($row['ticket_street'])? "" : $row['ticket_street'] . "<BR/>" . $row['ticket_city'] . " " . $row['ticket_state'] ;  2/21/09

//	$tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
//	$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
//	$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date_2($row['updated']) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $street . " </TD></TR>";
//	$tab_1 .= "</TABLE>";		// 11/6/08

	do_kml();			// kml functions

?>
//	map.openInfoWindowHtml(point, "<?php // print $tab_1;?>");

	google.maps.event.addListener(map, "click", function(marker, point) {
		if (point) {
/*			var baseIcon = new GIcon(); // 4356
			baseIcon.iconSize=new GSize(32,32);
			baseIcon.iconAnchor=new GPoint(16,16);
			var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);		// 10/13/08   4359
*/
			map.clearOverlays();
			var thisMarker = new google.maps.Marker(point, cross);
			thisMarker.setMap(map);		
			$("newlat").innerHTML = point.lat().toFixed(6);
			$("newlng").innerHTML = point.lng().toFixed(6);

			var nlat = $("newlat").innerHTML ;
			var nlng = $("newlng").innerHTML ;
			var olat = $("oldlat").innerHTML ;
			var olng = $("oldlng").innerHTML ;

			var km=distCosineLaw(parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng));
			var dist = ((km * km2feet).toFixed(0)).toString();
			var dist1 = dist/5280;
			var dist2 = (dist>5280)? ((dist/5280).toFixed(2) + " mi") : dist + " ft" ;

			$("range").innerHTML	= dist2;
			$("brng").innerHTML	= (brng (parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng)).toFixed(0)) + ' degr';
			$("newusng").innerHTML= LLtoUSNG(nlat, nlng, 5);
			$("pointl1").style.display = "block";
			$("pointl2").style.display = "block";

			var point = new google.maps.LatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1196
			var polyline = new google.maps.Polyline([
			    new google.maps.LatLng(nlat, nlng),
			    new google.maps.LatLng(olat, olng)
				], "#FF0000", 2);
			polyline.setMap(map);		
			}
		} )

	function lat2ddm(inlat) {				// 9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}

	function lng2ddm(inlng) {
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}

	function do_coords(inlat, inlng) {  //9/14/08
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

	</SCRIPT>
	<SPAN id='s_fl' class='plain' style='position: fixed; top: 10px; right: 0px; height: 20px; width: 100px; font-size: 1.2em; float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="$('file_list').style.display= 'block'; $('s_fl').style.display='none'; $('h_fl').style.display='inline-block';">Files</SPAN>
	<DIV id='file_list' style='position: fixed; right: 10px; top: 10px; width: 400px; height: 600px; border: 2px outset #707070; text-align: center; display: none;'>
		<SPAN id='h_fl' class='plain' style='float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="$('file_list').style.display= 'none'; $('h_fl').style.display='none'; $('s_fl').style.display='inline-block';">Hide</SPAN>
		<DIV class='heading' style='text-align: center;'>FILE LIST</DIV><BR />
		<SPAN id='nf_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='file_window(<?php print $id;?>);'>Add file</SPAN><BR /><BR />
		<DIV style='width: 100%; height: 100%; overflow-y: auto; text-align: left;'><?php print list_files($id, 0, 0, 0, 0);?></DIV>
	</DIV>
<?php

	}				// end function show_ticket() =======================================================
//	} {		-- dummy

function do_ticket($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
//	dump(__LINE__);
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("About Address") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['address_about']) . "</TD></TR>\n";	//	9/10/13
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("To Address") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['to_address']) . "</TD></TR>\n";	//	9/10/13
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("City") . ":</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['tick_descr']))) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['nine_one_one'])) . "</TD></TR>\n";	//	6/26/10
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>	<TD ALIGN='left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>			<TD ALIGN='left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$elapsed = get_elapsed_time ($theRow);			
	$elapsed_str = get_elapsed_time ($theRow);			
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Status") . ":</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$print .= "<TR CLASS='even'><TD ALIGN='left'>" . get_text("Written") . ":</TD>		<TD ALIGN='left'>" . format_date_2($theRow['date']) . $by_str;
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2($theRow['updated']) . "</TD></TR>\n";
	$print .=  empty($theRow['booked_date']) ? "" : "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date_2($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09
	$print .= "<TR CLASS='even' ><TD ALIGN='left' COLSPAN='2'>&nbsp;	<TD ALIGN='left'></TR>\n";			// separator
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD ALIGN='left'>{$incident} at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$rec_fac_details = empty($theRow['rec_fac_name'])? "" : $theRow['rec_fac_name'] . "<BR />" . $theRow['rec_fac_street'] . "<BR />" . $theRow['rec_fac_city'] . "<BR />" . $theRow['rec_fac_state'];
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $rec_fac_details) . "</TD></TR>\n";	// 10/6/09
	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['comments']))) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Run Start") . ":</TD> <TD ALIGN='left'>" . format_date_2($theRow['problemstart']);
	$end_str = (good_date_time($theRow['problemend']))? format_date_2(strtotime($theRow['problemend'])) : "";
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;{$end_str}&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD ALIGN='left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>" . get_text("Position") . "</U>: </TD>
		<TD ALIGN='left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08

	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow[0]);				// log
	$print .="</TD></TR>";
	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
											// 3/30/2013
	$print .= "<TR><TD COLSPAN=99>";
	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09 - new_show_assigns($id_in)
	$print .= "</TD></TR><TR><TD COLSPAN=99>";
	$print .= show_actions($theRow[0], "date", FALSE, FALSE);
	$print .= "</TD></TR>";	
	$print .= "</TABLE>\n";	
	return $print;
	}		// end function do ticket()

function do_ticket_only($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
//	dump(__LINE__);
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("City") . ":</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['tick_descr']))) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['nine_one_one'])) . "</TD></TR>\n";	//	6/26/10
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>	<TD ALIGN='left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>			<TD ALIGN='left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$elapsed = get_elapsed_time ($theRow);			
	$elapsed_str = get_elapsed_time ($theRow);			
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Status") . ":</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$print .= "<TR CLASS='even'><TD ALIGN='left'>" . get_text("Written") . ":</TD>		<TD ALIGN='left'>" . format_date_2($theRow['date']) . $by_str;
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2($theRow['updated']) . "</TD></TR>\n";
	$print .=  empty($theRow['booked_date']) ? "" : "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date_2($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09
	$print .= "<TR CLASS='even' ><TD ALIGN='left' COLSPAN='2'>&nbsp;	<TD ALIGN='left'></TR>\n";			// separator
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD ALIGN='left'>{$incident} at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$rec_fac_details = empty($theRow['rec_fac_name'])? "" : $theRow['rec_fac_name'] . "<BR />" . $theRow['rec_fac_street'] . "<BR />" . $theRow['rec_fac_city'] . "<BR />" . $theRow['rec_fac_state'];
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $rec_fac_details) . "</TD></TR>\n";	// 10/6/09
	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['comments']))) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Run Start") . ":</TD> <TD ALIGN='left'>" . format_date_2($theRow['problemstart']);
	$end_str = (good_date_time($theRow['problemend']))? format_date_2(strtotime($theRow['problemend'])) : "";
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;{$end_str}&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD ALIGN='left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>" . get_text("Position") . "</U>: </TD>
		<TD ALIGN='left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08
	$print .= "</TABLE>\n";	
	return $print;
	}		// end function do ticket_only()
	
//	} -- dummy

function do_ticket_extras($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
//	dump(__LINE__);
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow[0]);				// log
	$print .="</TD></TR>";
	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
											// 3/30/2013
	$print .= "<TR><TD COLSPAN=99>";
	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09 - new_show_assigns($id_in)
	$print .= "</TD></TR><TR><TD COLSPAN=99>";
	$print .= show_actions($theRow[0], "date", FALSE, FALSE);
	$print .= "</TD></TR>";	
	$print .= "</TABLE>\n";	
	return $print;
	}		// end function do ticket_extras()
	
function do_ticket_messages($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
//	dump(__LINE__);
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR><TD COLSPAN=99>";
	$print .= list_messages($theRow[0], "date", FALSE, TRUE);
	$print .= "</TD></TR>";	
	$print .= "</TABLE>\n";	
	return $print;
	}		// end function do ticket_extras()

function popup_ticket($id,$print='false', $search = FALSE) {								/* 7/9/09 - show specified ticket */
	global $istest, $iw_width, $eols, $elapsed, $disposition, $status_vals, $f_types, $fac_status_vals, $assigns, $tickets;

	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

?>
	</DIV>
	<DIV ID='map_canvas' style='border: 1px outset #707070; z-index: 1;'></DIV>
</DIV>
<?php
		$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
											// 1/7/10
		$query = "SELECT *,
			`problemstart` AS `my_start`,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`date` AS `date`,
			`booked_date` AS `booked_date`,		
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
			`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
			`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
			`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
			`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
			`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
			`rf`.`name` AS `rec_fac_name`,
			`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
			`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
			FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
			WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09


		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));

		$lat = $row['lat']; $lng = $row['lng'];
?>
<SCRIPT>
var map;
var minimap;
var latLng;
var in_local_bool = "0";
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
mapWidth = viewportwidth * .95;
mapHeight = viewportheight * .60;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
init_map(1, <?php print $lat;?>, <?php print $lng;?>, "", 12, theLocale, 1);
map.setView([<?php print $lat;?>, <?php print $lng;?>], 12);
var bounds = map.getBounds();
var zoom = map.getZoom();
</SCRIPT>
<?php

		$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;

		$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$id})</I>" : "";			// 1/25/09, 2/18/12
		$un_stat_cats = get_all_categories();
		$istest = FALSE;
		if($istest) {
			print "GET<br />\n";
			dump($_GET);
			print "POST<br />\n";
			dump($_POST);
			}

		if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
			print "Invalid Ticket ID: '$id'<BR />";
			return;
			}

		$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
											// 1/7/10
		$query = "SELECT *,
			`problemstart` AS `my_start`,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`date` AS `date`,
			`booked_date` AS `booked_date`,		
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
			`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
			`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
			`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
			`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
			`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
			`rf`.`name` AS `rec_fac_name`,
			`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
			`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,
			`ty`.`type` AS `type`, 			
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
			FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
			WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09


		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		$tip =  htmlentities ("{$row['contact']}/{$row['tick_street']}/{$row['tick_city']}/{$row['tick_state']}/{$row['phone']}/{$row['scope']}", ENT_QUOTES);		// tooltip string - 10/28/2012
		$sched_flag = (($row['status'] == $GLOBALS['STATUS_SCHEDULED']) && ($func != 10)) ? "*" : "";		
		$type = shorten($row['type'], 18);
		$severity = $row['severity'];
		$status = $row['status'];
		$the_id = $row['tick_id'];		// 11/27/09
		$radius = $row['radius'];
		$updated = format_sb_date_2($row['updated']);
		$the_scope = htmlentities(shorten($row['scope'], 30), ENT_QUOTES);
		$address_street=htmlentities(shorten($row['tick_street'] . " " . $row['tick_city'], 20), ENT_QUOTES);
		$locale = get_variable('locale');	// 08/03/09		
		if ($status== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
		if (my_is_float($row['lat'])) {		// 6/21/10
			$temp_array[0] = $row['lat'];
			$temp_array[1] = $row['lng'];
			$temp_array[2] = htmlentities(shorten($row['scope'], 48), ENT_QUOTES);
			$temp_array[3] = htmlentities(shorten(str_replace($eols, " ", $row['tick_descr']), 256), ENT_QUOTES);
			$street = empty($row['ticket_street'])? "" : replace_quotes($row['ticket_street']) . "<BR/>" . replace_quotes($row['ticket_city']) . " " . replace_quotes($row['ticket_state']) ;
			$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
			$theTabs = "<div class='infowin'><BR />";
			$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
			$theTabs .= '<div class="tabArea">';
			$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
			$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab2\', 2, null, null);">Details</span>';
			$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row['lat'] . ',' . $row['lng'] . ');">Location</span>';
			$theTabs .= '</div>';
			$theTabs .= '<div class="contentwrapper">';
		
			$tab_1 = "<TABLE width='280px' style='height: 260px;'><TR><TD><TABLE width='98%'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . htmlentities(shorten($row['scope'], 48), ENT_QUOTES)  . "$strikend</B></TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>As of:</TD><TD ALIGN='left'>" . format_date_2(($row['updated'])) . "</TD></TR>";
			if (is_date($row['booked_date'])){
				$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left' >Booked Date:</TD><TD ALIGN='left'>" . format_date_2($row['booked_date']) . "</TD></TR>";	//10/27/09, 3/15/11
				}
			$tab_1 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Reported by:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['contact'], 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Phone:</TD><TD ALIGN='left'>" . format_phone($row['phone']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Addr:</TD><TD ALIGN='left'>$address_street</TD></TR>";
	
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Status:</TD><TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
			$tab_1 .= (empty($row['fac_name']))? "" : "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Receiving Facility:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['fac_name'], 30))  . "</TD></TR>";	//3/27/10, 3/15/11
			$utm = get_variable('UTM');
			if ($utm==1) {
				$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
				$tab_1 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>UTM grid:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";
				}
			$tab_1 .= "</TABLE></TD></TR>";
			$tab_1 .= 	"</FONT></TD></TR></TABLE>";			// 11/6/08	
			$tab_2 = "<TABLE width='280px' style='height: 280px;' ><TR><TD><TABLE width='98%'>";
			$tab_2 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Description:</TD><TD ALIGN='left'>" . replace_quotes(shorten(str_replace($eols, " ", $row['tick_descr']), 48)) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>911 contact:</TD><TD ALIGN='left'>" . shorten($row['nine_one_one'], 48) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>{$disposition}:</TD><TD ALIGN='left'>" . shorten(replace_quotes($row['comments']), 48) . "</TD></TR></TABLE></TD></TR>";		// 8/13/09, 3/15/11
			$tab_2 .= "<TR><TD COLSPAN=2 ALIGN='left'><DIV style='max-height: 200px; overflow-y: scroll;'>" . show_assigns(0, $the_id) . "</DIV></TD></TR>";

			$tab_2 .= "</TABLE>";			// 11/6/08			
			
			$tab_3 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
			$tab_3 .= "<TABLE width='98%'>";

			switch($locale) { 
				case "0":
				$tab_3 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "1":
				$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "2":
				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
				$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				default:
				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
				}
			$tab_3 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lat</TD><TD class='td_data' style='font-size: 80%;'>" . $row['lat'] . "</TD></TR>";
			$tab_3 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lng</TD><TD class='td_data' style='font-size: 80%;'>" . $row['lng'] . "</TD></TR>";
			$tab_3 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
			$tab_3 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
			$tab_3 .= "</TABLE></TD</TR></TABLE>";
			}
			
		$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
		$theTabs .= "<div class='content' id='content2' style = 'display: none;'>" . $tab_2 . "</div>";
		$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_3 . "</div>";
		$theTabs .= "</div>";
		$theTabs .= "</div>";
		$theTabs .= "</div>";
		$lat = $row['lat']; $lng = $row['lng'];

		if ((($lat == $GLOBALS['NM_LAT_VAL']) && ($lng == $GLOBALS['NM_LAT_VAL'])) || (($lat == "") || ($lat == NULL)) || (($lng == "") || ($lng == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
			$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
			$icon_file = "./our_icons/question1.png";
			}
		else {
			$icon_file = "./markers/crosshair.png";
			}
			if ((my_is_float($lat)) && (my_is_float($lng))) {
?>
<SCRIPT>
				var marker = createMarker(<?php print $lat;?>, <?php print $lng;?>, <?php print quote_smart($theTabs);?>, <?php print $row['severity'];?>, "<?php print $row['type'];?>", 0, 0, "Incident", 0, "<?php print $tip?>");
				marker.addTo(map);
				map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);		
</SCRIPT>
<?php
				}	// end if my_is_float
			
// ====================================Add Facilities to Map 8/1/09================================================
		$query_fac = "SELECT *,`$GLOBALS[mysql_prefix]facilities`.`updated` AS `updated`, 
			`$GLOBALS[mysql_prefix]facilities`.`id` 						AS `fac_id`, 
			`$GLOBALS[mysql_prefix]fac_types`.`id` 							AS `type_id`,
			`$GLOBALS[mysql_prefix]facilities`.`description` 				AS `facility_description`,
			`$GLOBALS[mysql_prefix]facilities`.`boundary` 					AS `boundary`,		
			`$GLOBALS[mysql_prefix]fac_types`.`name` 						AS `fac_type_name`, 
			`$GLOBALS[mysql_prefix]fac_types`.`icon` 						AS `icon`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` 						AS `facility_name`, 
			`$GLOBALS[mysql_prefix]fac_status`.`status_val` 				AS `fac_status_val`, 
			`$GLOBALS[mysql_prefix]facilities`.`status_id` 					AS `fac_status_id`
			FROM `$GLOBALS[mysql_prefix]facilities`
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 	ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = 			`$GLOBALS[mysql_prefix]allocates`.`resource_id` )	
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` 	ON (`$GLOBALS[mysql_prefix]facilities`.`type` = 		`$GLOBALS[mysql_prefix]fac_types`.`id` )
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` 	ON (`$GLOBALS[mysql_prefix]facilities`.`status_id` = 	`$GLOBALS[mysql_prefix]fac_status`.`id` )
			ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";	

		$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

		while($row_fac = mysql_fetch_array($result_fac)){

			$fac_name = $row_fac['facility_name'];			//	10/8/09
			$fac_temp = explode("/", $fac_name );
			$fac_index = $row_fac['icon_str'];	
			
			$fac_id=($row_fac['fac_id']);
			$fac_type=($row_fac['icon']);

			$f_disp_name = $row_fac['facility_name'];		//	10/8/09
			$f_disp_temp = explode("/", $f_disp_name );
			$facility_display_name = $f_disp_temp[0];
			$faclat = $row_fac['lat'];
			$faclng = $row_fac['lng'];
			// BEDS
				$beds_info = "<TD ALIGN='right'>{$row_fac['beds_a']}/{$row_fac['beds_o']}</TD>";
			// STATUS
				$status = get_status_sel($row_fac['fac_id'], $row_fac['fac_status_id'], "f");
				$status_id = $row_fac['fac_status_id'];
				$temp = $row_fac['status_id'] ;
				$the_status = (array_key_exists($temp, $fac_status_vals))? $fac_status_vals[$temp] : "??";
			// AS-OF - 11/3/2012
				$updated = format_sb_date_2 ( $row_fac['updated'] );
				
			if (my_is_float($row_fac['lat'])) {										// position data of any type?
				$temptype = ($f_types[$row_fac['type_id']]) ? $f_types[$row_fac['type_id']] : 0;
				$the_type = ($temptype != 0) ? $temptype[0] : "Not Set";
				$line_ctr = 0;
				$temp_array[0] = $row_fac['lat'];
				$temp_array[1] = $row_fac['lng'];
				$temp_array[2] = htmlentities(shorten($facility_display_name, 48), ENT_QUOTES);
				$temp_array[3] = htmlentities(shorten(str_replace($eols, " ", $facility_display_name), 48), ENT_QUOTES);
				$theTabs = "<div class='infowin'><BR />";
				$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
				$theTabs .= '<div class="tabArea">';
				$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
				$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row_fac['lat'] . ',' . $row_fac['lng'] . ');">Location</span>';
				$theTabs .= '</div>';
				$theTabs .= '<div class="contentwrapper">';		

				$tab_1 = "<TABLE width='280px' style='height: 280px;'><TR><TD><TABLE width='98%'>";	
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . htmlentities(shorten($facility_display_name, 48), ENT_QUOTES) . "</B> - " . $the_type . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . htmlentities(shorten(str_replace($eols, " ", $row_fac['facility_description']), 32), ENT_QUOTES) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . $the_status . " </TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date(strtotime($row_fac['updated'])) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). " Via: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
				if(!(isempty(trim($row_fac['security_contact']))))	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right' STYLE= 'width:50%'>Security contact:&nbsp;</TD><TD ALIGN='left' STYLE= 'width:50%'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['security_email']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['security_phone']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['access_rules']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>" . get_text("Access rules") . ":&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";}
				if(!(isempty(trim($row_fac['security_reqs']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";}
				if(!(isempty(trim($row_fac['opening_hours']))))  	{
					$opening_arr_serial = base64_decode($row_fac['opening_hours']);
					$opening_arr = unserialize($opening_arr_serial);
					$outputstring = "";
					$the_day = "";
					$z = 0;
					foreach($opening_arr as $val) {
						switch($z) {
							case 0:
							$dayname = "Monday";
							break;
							case 1:
							$dayname = "Tuesday";
							break;
							case 2:
							$dayname = "Wednesday";
							break;
							case 3:
							$dayname = "Thursday";
							break;
							case 4:
							$dayname = "Friday";
							break;
							case 5:
							$dayname = "Saturday";
							break;
							case 6:
							$dayname = "Sunday";
							break;
							}
						$openstring = ($dayname == get_day()) ? "Open" : "Closed";
						if($dayname == get_day()) {
							$the_day .= $dayname;
							$outputstring .= " Opens: " . $val[1] . " Closes: " . $val[2];
							}
						$z++;
						}
					$openingTimes = "Opening Times Today (" . $the_day . ")  ---  " . $outputstring;
					$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Opening today (" . $the_day . ")&nbsp;</TD><TD ALIGN='left'>" . $outputstring . "</TD></TR>";
					}
				if(!(isempty(trim($row_fac['pager_p']))))  			{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['pager_s']))))  			{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";}
				$tab_1 .= "</TABLE></TD></TR>";
				$tab_1 .= "<TR><TD COLSPAN=2 ALIGN='center'><TABLE>";
				$tab_1 .= "</TABLE></TD></TR></TABLE>";
				$tab_2 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
				$tab_2 .= "<TABLE width='98%'>";

				switch($locale) { 
					case "0":
					$tab_2 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row_fac['lat'], $row_fac['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "1":
					$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row_fac['lat'], $row_fac['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "2":
					$coords =  $row_fac['lat'] . "," . $row_fac['lng'];							// 8/12/09
					$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					default:
					print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
					}
				$tab_2 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lat</TD><TD class='td_data' style='font-size: 80%;'>" . $row_fac['lat'] . "</TD></TR>";
				$tab_2 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lng</TD><TD class='td_data' style='font-size: 80%;'>" . $row_fac['lng'] . "</TD></TR>";
				$tab_2 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
				$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
				$tab_2 .= "</TABLE></TD</TR></TABLE>";
					
				$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
				$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_2 . "</div>";
				$theTabs .= "</div>";
				$theTabs .= "</div>";
				$theTabs .= "</div>";
				$line_ctr++;
				}		// end if/else			
			

			if ((my_is_float($faclat)) && (my_is_float($faclng))) {
?>
<SCRIPT>
				var marker = createFacilityMarker(<?php print $faclat;?>, <?php print $faclng;?>, <?php print quote_smart($theTabs);?>, <?php print $fac_type;?>, 0, <?php print $fac_id;?>, '<?php print $fac_index;?>', 0, 0, '<?php print $facility_display_name;?>');
				marker.addTo(map);
</SCRIPT>
<?php
				}	// end if my_is_float
			}	// end while
// ================================End of Facilities========================================
// ====================================Add Responding Units to Map================================================

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id' AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row = mysql_fetch_array($result)){
			$responder_id=($row['responder_id']);
			$query_unit = "SELECT *, r.updated AS `r_updated`,
				`r`.`status_updated` AS `status_updated`,
				`r`.`status_about` AS `status_about`,
				`t`.`id` AS `type_id`,
				`r`.`id` AS `unit_id`,
				`r`.`name` AS `name`,
				`t`.`name` AS `un_type_name`,
				`s`.`description` AS `stat_descr`,
				`r`.`description` AS `unit_descr`, 
				`r`.`ring_fence` AS `ring_fence`,	
				`r`.`excl_zone` AS `excl_zone`,		
				(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns`
				WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND  (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )) AS `nr_assigned` 
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )			
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
				WHERE `r`.`id`='$responder_id';";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id = $row_unit['unit_id'];
				$mobile = $row_unit['mobile'];
				$handle = $row_unit['handle'];
				$index = $row_unit['icon_str'];
				$resp_cat = $un_stat_cats[$row_unit['unit_id']];
				$temp = $row_unit['un_status_id'] ;
				$the_time = $row_unit['updated'];
				$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
				if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {
					$theTabs = "<div class='infowin'><BR />";
					$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
					$theTabs .= '<div class="tabArea">';
					$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
					$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row_unit['lat'] . ',' . $row_unit['lng'] . ');">Location</span>';
					$theTabs .= '</div>';
					$theTabs .= '<div class="contentwrapper">';
					
					$tab_1 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD><TABLE>";			
					$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_unit['name'], 48)) . "</B></TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . addslashes(shorten(str_replace($eols, " ", $row_unit['description']), 32)) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . addslashes($row_unit['contact_name']). " Via: " . addslashes($row_unit['contact_via']) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($the_time) . "</TD></TR>";		// 4/11/10
					if (array_key_exists($unit_id, $assigns)) {
						$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$unit_id] . "'>" . addslashes(shorten($assigns[$unit_id], 20)) . "</A></TD></TR>";
						}
					$tab_1 .= "</TABLE></TD></TR></TABLE>";
				
					$tab_2 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD>";
					$tab_2 .= "<TABLE width='100%'>";
					$locale = get_variable('locale');	// 08/03/09
					switch($locale) { 
						case "0":
						$tab_2 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row_unit['lat'], $row_unit['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						case "1":
						$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row_unit['lat'], $row_unit['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						case "2":
						$coords =  $row_unit['lat'] . "," . $row_unit['lng'];							// 8/12/09
						$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						default:
						print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
						}
					$tab_2 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lat</TD><TD class='td_data' style='font-size: 80%;'>" . $row_unit['lat'] . "</TD></TR>";
					$tab_2 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lng</TD><TD class='td_data' style='font-size: 80%;'>" . $row_unit['lng'] . "</TD></TR>";
					$tab_2 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
					$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
					$tab_2 .= "</TABLE></TD</TR></TABLE>";
						
					$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
					$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_2 . "</div>";
					$theTabs .= "</div>";
					$theTabs .= "</div>";
					$theTabs .= "</div>";
?>				
<SCRIPT>
					var isMobile = <?php print $mobile;?>;
					var theCol = (isMobile == 1) ? 0 : 1;
					var marker = createUnitMarker(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>, <?php print quote_smart($theTabs);?>, theCol, 0, <?php print $unit_id;?>, '<?php print $index;?>', '<?php print $resp_cat;?>', 0, '<?php print $handle;?>', <?php print $row_unit['icon'];?>);
					marker.addTo(map);
</SCRIPT>						
<?php
					}	// end if mys_is_float
				}	// end while row unit
			}	//	end while row
// =====================================End of functions to show responding units========================================================================
	}				// end function popup_ticket() =======================================================
							// 3/30/2013
function do_ticket_wm($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<DIV style='border: 1px solid #707070;'><TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("City") . ":</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['tick_descr']))) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['nine_one_one'])) . "</TD></TR>\n";	//	6/26/10
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>	<TD ALIGN='left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>			<TD ALIGN='left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$elapsed = get_elapsed_time ($theRow);			
	$elapsed_str = get_elapsed_time ($theRow);			
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Status") . ":</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$print .= "<TR CLASS='even'><TD ALIGN='left'>" . get_text("Written") . ":</TD>		<TD ALIGN='left'>" . format_date_2(strtotime($theRow['date'])) . $by_str;
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2(strtotime($theRow['updated'])) . "</TD></TR>\n";
	$print .=  empty($theRow['booked_date']) ? "" : "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date_2(strtotime($theRow['booked_date'])) . "</TD></TR>\n";	// 10/6/09
	$print .= "<TR CLASS='even' ><TD ALIGN='left' COLSPAN='2'>&nbsp;	<TD ALIGN='left'></TR>\n";			// separator
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD ALIGN='left'>{$incident} at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$rec_fac_details = empty($theRow['rec_fac_name'])? "" : $theRow['rec_fac_name'] . "<BR />" . $theRow['rec_fac_street'] . "<BR />" . $theRow['rec_fac_city'] . "<BR />" . $theRow['rec_fac_state'];
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $rec_fac_details) . "</TD></TR>\n";	// 10/6/09
	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . replace_quotes(highlight($search, nl2br($theRow['comments']))) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Run Start") . ":</TD> <TD ALIGN='left'>" . format_date_2(strtotime($theRow['problemstart']));
	$end_str = (good_date_time($theRow['problemend']))? format_date_2(strtotime($theRow['problemend'])) : "";
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;{$end_str}&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD ALIGN='left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>" . get_text("Position") . "</U>: </TD>
		<TD ALIGN='left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08

	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow[0]);				// log
	$print .="</TD></TR>";
	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";

	$print .= "<TR><TD COLSPAN=99>";
	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09 - new_show_assigns($id_in)
	$print .= "</TD></TR><TR><TD COLSPAN=99>";
	$print .= show_actions($theRow[0], "date", FALSE, TRUE);
	$print .= "</TD></TR><TR><TD COLSPAN=99>";	
	$print .= list_messages($theRow[0], "date", FALSE, TRUE);
	$print .= "</TD></TR>";
	$print .= "</TABLE>\n<BR /><BR /><BR /><BR /></DIV>";	
	return $print;
	}		// end function do ticket_wm()
	