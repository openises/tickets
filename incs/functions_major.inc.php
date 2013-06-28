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

function list_tickets($sort_by_field='',$sort_value='', $my_offset=0) {	// list tickets ===================================================
	global $istest, $iw_width, $units_side_bar_height, $do_blink, $nature, $disposition, $patient, $incident, $incidents, $gt_status, $curr_cats, $cat_sess_stat, $hidden, $shown, $un_stat_cats;	// 12/3/10
	$time = microtime(true); // Gets microseconds

	@session_start();		// 
	$captions = array(get_text("Current situation"), "{$incidents} closed today", "{$incidents} closed yesterday+", "{$incidents} closed this week", "{$incidents} closed last week", "{$incidents} closed last week+", "{$incidents} closed this month", "{$incidents} closed last month", "{$incidents} closed this year", "{$incidents} closed last year", "Scheduled");
	$by_severity = array(0, 0, 0);				// counters // 5/2/10
	
	if (!(array_key_exists('func', $_GET))) {		//	3/15/11
		$func = 0;
	} else {
		extract ($_GET);
		}
//	snap(__LINE__, $func);
	if ((array_key_exists('func', $_GET)) && ($_GET['func'] == 10)) {		//	3/15/11
		$func = 10;
		}
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	//	6/10/11
	$result = mysql_query($query);	//	6/10/11
	$al_groups = array();
	$al_names = "";	
	$a_gp_bounds = array();	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	6/10/11
		$al_groups[] = $row['group'];
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	//	6/10/11
		$result2 = mysql_query($query2);	// 4/18/11
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	//	//	6/10/11	
				$al_names .= $row2['group_name'] . ", ";
			}
		}
	if(is_super()) {	//	6/10/11
		$al_names .= "Superadmin Level";
	}	

	if (isset($_SESSION['list_type'])) {$func = $_SESSION['list_type'];}		// 12/02/10	 persistance for the tickets list

	$cwi = get_variable('closed_interval');			// closed window interval in hours

	$get_sortby = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['sortby'])))) ) ? "" : $_GET['sortby'] ;
	$get_offset = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['offset'])))) ) ? "" : $_GET['offset'] ;

	if (!isset($_GET['status'])) {
		$open = "Open";
	} else {
	$open = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_OPEN']))? "Open" : "";
	$open = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_SCHEDULED']))? "Scheduled" : "";	//	11/29/10
	}
	
	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		} else {
		$curr_viewed = $al_groups;
		}

	$curr_names="";	//	6/10/11
	$z=0;	//	6/10/11
	foreach($curr_viewed as $grp_id) {	//	6/10/11
		$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
		$curr_names .= get_groupname($grp_id);
		$curr_names .= $counter;
		$z++;
		}
		
	$heading = $captions[($func)] . " - " . get_variable('map_caption');
	$regs_string = "<FONT SIZE='-1'>Allocated " . get_text("Regions") . ":&nbsp;&nbsp;" . $al_names . "&nbsp;&nbsp;|&nbsp;&nbsp;Currently Viewing " . get_text("Regions") . ":&nbsp;&nbsp;" . $curr_names . "</FONT>";	//	6/10/11
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	@session_start(); 

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 5/12/10
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	unset($result);		
	$required = 48 + (mysql_affected_rows()*22);		// derived by trial and error - emphasis the latter = 7/18/10
	$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// see main for $units_side_bar_height value
	$buttons_width = (integer) get_variable('map_width') - 50; 
	$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
	$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
	$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
	$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
	$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
	$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
	$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
	$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
	$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
	$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
	$regions_inuse = get_regions_inuse($user_level);
	$group = get_regions_inuse_numbers($user_level);
	$test_size = 1200;			// scale map down if monitor width LT test value - 6/28/11
	$map_factor = ($_SESSION['scr_width']< $test_size ) ? ($_SESSION['scr_width'] / $test_size): 1.0;		// a float
	$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
	$ctrls_width = $col_width * .75;
	print get_buttons_inner();	//	4/12/12
	print get_buttons_inner2();	//	4/12/12
?>
<DIV style='z-index: 1;'>
<TABLE BORDER=0>
	<TR CLASS='header' style = "height:32px;">	<!-- 4/5/12 -->
		<TD COLSPAN='99' ALIGN='center' ID = "hdr_td_str"  CLASS='header' STYLE='background-color: inherit;'>
		<?php print $heading; ?>
		<SPAN ID='sev_counts' CLASS='sev_counts'></SPAN>
	</TD></TR>
	<TR CLASS='header'><TD COLSPAN='99' ALIGN='center'>
		<SPAN ID='region_flags' style='background: #00FFFF; font-weight: bold;'></SPAN>
	</TD></TR>
	<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;
	</TD></TR>
	<TR><TD align = 'left' VALIGN='TOP'  >
		<TABLE>
			<TR class = 'heading'><TH width = <?php print $col_width;?> ALIGN='center' COLSPAN='99'>Incidents <SPAN ID='sched_flag'></SPAN>
				<SPAN id='collapse_incs' onClick="hideDiv('incs_list_sh', 'collapse_incs', 'expand_incs')" style = 'display: "";'><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
			<SPAN id='expand_incs' onClick="showDiv('incs_list_sh', 'collapse_incs', 'expand_incs')" style = 'display: none;'><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
			</TH></TR>
			<TR><TD>	
				<DIV ID='incs_list_sh'>
					<DIV ID = 'side_bar'></DIV>
				</DIV>
			</TD></TR>
		</TABLE>
		<TABLE>
			<TR class = 'heading'><TH width = <?php print $col_width;?> ALIGN='center' COLSPAN='99'><?php print get_text("Units");?> 
				<SPAN id='collapse_resp' onClick="hideDiv('resp_list_sh', 'collapse_resp', 'expand_resp')" style = "display: <?php print $resp_col_butt;?>;"><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
				<SPAN id='expand_resp' onClick="showDiv('resp_list_sh', 'collapse_resp', 'expand_resp')" style = "display: <?php print $resp_exp_butt;?>;"><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
			</TH></TR>
			<TR><TD>		
				<DIV ID='resp_list_sh' style='display: <?php print $show_resp;?>'>
					<DIV ID = 'side_bar_r' style='min-height: 100px; max-height: <?php print $the_height;?>px; overflow-y: scroll; overflow-x: hidden;'></DIV>
					<DIV ID = 'side_bar_rl'></DIV>
					<DIV STYLE = "height:12px;">&nbsp;</DIV>
					<DIV ID = 'units_legend'></DIV>
				</DIV>
			</TD></TR>
		</TABLE>
		<TABLE>
			<TR class = 'heading'><TH width = <?php print $col_width;?> ALIGN='center' COLSPAN='99'>Facilities 
				<SPAN id='collapse_facs' onClick="hideDiv('facs_list_sh', 'collapse_facs', 'expand_facs')" style = "display: <?php print $facs_col_butt;?>;"><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
				<SPAN id='expand_facs' onClick="showDiv('facs_list_sh', 'collapse_facs', 'expand_facs')" style = "display: <?php print $facs_exp_butt;?>;"><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
			</TH></TR>
			<TR><TD>			
				<DIV ID='facs_list_sh' style='display: <?php print $show_facs;?>'>
					<DIV ID = 'side_bar_f' style="min-height: 100px; max-height: <?php print $the_height;?>px; overflow-y: scroll; overflow-x: hidden;"></DIV>
					<DIV STYLE = "height:12px">&nbsp;</DIV>
					<DIV ID = 'facs_legend'></DIV>
				</DIV>
			</TD></TR>
		</TABLE>	
	</TD>
	<TD></TD>
	<TD CLASS='td_label'>
	<TABLE>		<!-- 341 -->
		<TR><TD ALIGN='center' padding="0">
			<DIV ID='map_canvas' STYLE='WIDTH: <?php print round ($map_factor * get_variable('map_width'));?>PX; HEIGHT: <?php print round ($map_factor * get_variable('map_height'));?>PX; z-index: 999;'></DIV>
		</TD></TR>
		<TR><TD ALIGN='center' style='padding: 0'>
			<BR /><CENTER><A HREF='#' onClick='toglGrid()'><u>Grid</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doTraffic()'><U>Traffic</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doWeather()'><U>Weather</U></A></CENTER>
		</TD></TR>
		<TR><TD>&nbsp;</TD></TR>				<!-- 3/15/11 -->
		<TR><TD>
			<TABLE ALIGN='center' WIDTH='<?php print $ctrls_width;?>'>
				<TR class='heading'><TH ALIGN='center' COLSPAN=99>Show / Hide
					<SPAN id='collapse_buttons' onClick="hideDiv('buttons_sh', 'collapse_buttons', 'expand_buttons')" style = "display: <?php print $col_butt;?>;"><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
					<SPAN id='expand_buttons' onClick="showDiv('buttons_sh', 'collapse_buttons', 'expand_buttons')" style = "display: <?php print $exp_butt;?>;"><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
				</TH></TR>
				<TR class='even'><TD>
					<CENTER>
						<TABLE ID='buttons_sh' style='display: <?php print $show_controls;?>;'>
							<TR CLASS='odd'><TD>
								<TABLE>
									<TR class='heading_2'><TH ALIGN='center' WIDTH='<?php print $ctrls_width;?>'>Incidents</TH></TR>
									<TR><TD>				<!-- 3/15/11 -->
										<DIV class='pri_button' onClick="set_pri_chkbox('normal'); hideGroup(1, 'Incident');"><IMG SRC = './our_icons/sm_blue.png' STYLE = 'vertical-align: middle'BORDER=0>&nbsp;&nbsp;Normal: <input type=checkbox id='normal'  onClick="set_pri_chkbox('normal')"/>&nbsp;&nbsp;</DIV>
										<DIV class='pri_button' onClick="set_pri_chkbox('medium'); hideGroup(2, 'Incident');"><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;Medium: <input type=checkbox id='medium'  onClick="set_pri_chkbox('medium')"/>&nbsp;&nbsp;</DIV>
										<DIV class='pri_button' onClick="set_pri_chkbox('high'); hideGroup(3, 'Incident');"><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;High: <input type=checkbox id='high'  onClick="set_pri_chkbox('high')"/>&nbsp;&nbsp;</DIV>
										<DIV class='pri_button' ID = 'pri_all' class='pri_button' STYLE = 'display: none; width: 70px;' onClick="set_pri_chkbox('all'); hideGroup(4, 'Incident');"><IMG SRC = './our_icons/sm_blue.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;All <input type=checkbox id='all'  STYLE = 'display:none;' onClick="set_pri_chkbox('all')"/>&nbsp;&nbsp;</DIV>
										<DIV class='pri_button' ID = 'pri_none' class='pri_button' STYLE = 'width: 60px;' onClick="set_pri_chkbox('none'); hideGroup(5, 'Incident');"><IMG SRC = './our_icons/sm_white.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;None <input type=checkbox id='none' STYLE = 'display:none;' onClick="set_pri_chkbox('none')"/>&nbsp;&nbsp;</DIV>
									</TD></TR>
								</TABLE>
							</TD></TR>
							<TR CLASS='odd'><TD>
								<DIV ID = 'boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV>
							</TD></TR>		<!-- 12/03/10, 3/15/11 -->
							<TR CLASS='odd'><TD>
								<DIV ID = 'fac_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV>
							</TD></TR>		<!-- 12/03/10, 3/15/11 -->
							<TR CLASS='odd'><TD>
								<DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV>
							</TD></TR>	
						</TABLE>
					</CENTER>
				</TD></TR>
			</TABLE>
		</TD></TR>
		<TR><TD CLASS='td_label' COLSPAN=99 ALIGN='CENTER'>
			&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:<?php echo get_contact_addr ();?>?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		</TD></TR>
	</TABLE>
	</TD></TR>
</TABLE>
</DIV>
<FORM NAME='unit_form' METHOD='get' ACTION="<?php print $_SESSION['unitsfile'];?>">				<!-- 7/28/10 -->
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='edit' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='tick_form' METHOD='get' ACTION='<?php print $_SESSION['editfile'];?>'>				<!-- 11/27/09 7/28/10 -->
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='sort_form' METHOD='post' ACTION='main.php'>				<!-- 6/11/10 -->
<INPUT TYPE='hidden' NAME='order' VALUE=''>
</FORM>

<FORM NAME='fac_sort_form' METHOD='post' ACTION='main.php'>				<!-- 3/15/11 -->
<INPUT TYPE='hidden' NAME='forder' VALUE=''>
</FORM>	

<FORM NAME='facy_form_ed' METHOD='get' ACTION='<?php print $_SESSION['facilitiesfile'];?>'>		<!-- 8/3/10 -->
<INPUT TYPE='hidden' NAME='id' VALUE=''>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
</FORM>
<SCRIPT>
	var boundary = new Array();	
	var bound_names = new Array();
	
	function add_hash(in_str) { // prepend # if absent
		return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
		}

	function do_landb() {				// JS function - 8/1/11
		var points = new Array();
<?php
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND `use_with_bm` = 1";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$empty = FALSE;
			extract ($row);
			$name = $row['line_name'];
			switch ($row['line_type']) {
				case "p":				// poly
					$points = explode (";", $line_data);

					$sep = "";
					echo "\n\t var points = [\n";
					for ($i = 0; $i<count($points); $i++) {
						$coords = explode (",", $points[$i]);
						echo	"{$sep}\n\t\tnew google.maps.LatLng({$coords[0]}, {$coords[1]})";
						$sep = ",";					
						}			// end for ($i = 0 ... )
					echo "];\n";

			 	if ((intval($filled) == 1) && (count($points) > 2)) {
?>
//					446
					  polyline = new google.maps.Polygon({
					    paths: 			 points,
					    strokeColor: 	 add_hash("<?php echo $line_color;?>"),
					    strokeOpacity: 	 <?php echo $line_opacity;?>,
					    strokeWeight: 	 <?php echo $line_width;?>,
					    fillColor: 		 add_hash("<?php echo $fill_color;?>"),
					    fillOpacity: 	 <?php echo $fill_opacity;?>
						});

<?php			} else {
?>
//					457
//				    var polyline = new google.maps.Polyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>);
					  polyline = new google.maps.Polygon({
					    paths: 			points,
					    strokeColor: 	add_hash("<?php echo $line_color;?>"),
					    strokeOpacity: 	<?php echo $line_opacity;?>,
					    strokeWeight: 	<?php echo $line_width;?>,
					    fillColor: 		add_hash("<?php echo $fill_color;?>"),
					    fillOpacity: 	<?php echo $fill_opacity;?>
						});
<?php			} ?>				        
					polyline.setMap(map);		
<?php				
					break;
			
				case "c":		// circle
					$temp = explode (";", $line_data);
					$radius = $temp[1];
					$coords = explode (",", $temp[0]);
					$lat = $coords[0];
					$lng = $coords[1];
					$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
					
					echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_width}, {$line_opacity}, add_hash('{$fill_color}'), {$fill_opacity}, '{$name}'); // 513\n";
					break;
				case "t":		// text banner

					$temp = explode (";", $line_data);
					$banner = $temp[1];
					$coords = explode (",", $temp[0]);
					echo "\n var point = new google.maps.LatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
					$the_banner = htmlentities($banner, ENT_QUOTES);
					$the_width = intval( trim($line_width), 10);		// font size
					echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width}, add_hash('{$line_color}'));\n";
					break;
				}	// end switch
		}			// end while ()
		unset($query, $result);
?>
		}		// end function do_landb()
/*
	try {
		do_landb();				// 7/3/11 - show lines
		}
	catch (e) {	}
*/


//================================= 7/18/10
	var spe=500;
	var NameOfYourTags="mi";
	var swi=1;
	var na=document.getElementsByName(NameOfYourTags);
	var sho;
	
	doBlink();
	
	function doBlink() {
		if (swi == 1) {
			sho="visible";
			swi=0;
			}
		else {
			sho="hidden";
			swi=1;
			}
	
		for(i=0;i<na.length;i++) {
			na[i].style.visibility=sho;
			}
		setTimeout("doBlink()", spe);
		}
		
	Array.prototype.contains = function (element) {
		for (var i = 0; i < this.length; i++) {
		if (this[i] == element) {
		return true;
		}
		}
		return false;
		}	
	
	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=1' +',resizable=1')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)
	

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	function to_session(the_name, the_value) {									// generic session variable writer - 3/8/10, 4/4/10
		function local_handleResult(req) {			// the called-back function
			}			// end function local handleResult

		var params = "f_n=" + the_name;				// 1/20/09
		params += "&f_v=" + the_value;				// 4/4/10
		sendRequest ('do_session_get.php',local_handleResult, params);			// does the work via POST
		}


	function to_server(the_unit, the_status) {									// write unit status data via ajax xfer
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
	
		var url = "as_up_un_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<?php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			parent.frames['upper'].show_msg ('<?php print get_text("Units");?> status update applied!')
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server()

	function to_server_fac(the_unit, the_status) {		//	3/15/11							// 3/15/11
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
	
		var url = "as_up_fac_status.php?" + querystr;
		var payload = syncAjax(url); 
		if (payload.substring(0,1)=="-") {	
			alert ("<?php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			parent.frames['upper'].show_msg ('Facility status update applied!')
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server_fac()
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// e
			return AJAX.responseText;																				 
			} 
		else {
			alert ("<?php print __LINE__; ?>: failed");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

	var starting = false;
	
	function do_mail_win(the_id) {	

		if(starting) {return;}					// dbl-click catcher
		starting=true;
		var url = "do_unit_mail.php?name=" + escape(the_id);	//
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=320,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()

	function do_fac_mail_win(the_name, the_addrs) {			// 3/8/10
		if(starting) {return;}					// dbl-click catcher
		starting=true;
		var url = (isNull(the_name))? "do_fac_mail.php?" : "do_fac_mail.php?name=" + escape(the_name) + "&addrs=" + escape(the_addrs);	//
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=320,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()


	function do_close_tick(the_id) {	//	3/15/11
		if(starting) {return;}					// dbl-click catcher
		starting=true;
		var url = "close_in.php?ticket_id=" + escape(the_id);	//
		newwindow_close = window.open(url, "close_ticket", "titlebar, location=0, resizable=1, scrollbars, height=300, width=700, status=0, toolbar=0, menubar=0, left=100,top=100,screenX=100,screenY=100");
		if (isNull(newwindow_close)) {
			alert ("Close Ticket operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_close.focus();
		starting = false;
		}		// end function do mail_win()

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

	function sendRequest(url,callback,postData) {								// 2/14/09
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
//				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function get_chg_disp_tr() {								// 5/5/11
		var chg_disp_tr ="<TR><TD COLSPAN=99 ALIGN='center'>\n";
		chg_disp_tr +="\t\t<FORM NAME = 'frm_interval_sel' STYLE = 'display:inline' >\n";
		chg_disp_tr +="\t\t<SELECT NAME = 'frm_interval' onChange = 'do_listtype(this.value);'>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='0'><?php print get_text("Current situation"); ?></OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='1'><?php print $incidents;?> closed today</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='2'><?php print $incidents;?> closed yesterday+</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='3'><?php print $incidents;?> closed this week</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='4'><?php print $incidents;?> closed last week</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='5'><?php print $incidents;?> closed last week+</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='6'><?php print $incidents;?> closed this month</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='7'><?php print $incidents;?> closed last month</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='8'><?php print $incidents;?> closed this year</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='9'><?php print $incidents;?> closed last year</OPTION>\n";
		chg_disp_tr +="\t\t</SELECT>\n</FORM>\n";
				// 3/30/2013
 		chg_disp_tr +="\t\t<SPAN ID = 'btn_go' onClick='document.to_listtype.submit()' CLASS='conf_button' STYLE = 'margin-left: 10px; display:none; color: green;'><U>Next</U></SPAN>";
 		chg_disp_tr +="\t\t<SPAN ID = 'btn_can' onClick='hide_btns_closed(); hide_btns_scheduled(); ' CLASS='conf_button' STYLE = 'margin-left: 10px; display:none; color: red;'><U>Cancel</U></SPAN>";
 		chg_disp_tr +="</TD></TR>\n";

		return chg_disp_tr;
		} 					// end function get chg_disp_tr()

<?php
	$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));				// 8/3/10
	print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var tr_id_fixed_part = "tr_id_";		// 3/2/10

//	if (GBrowserIsCompatible()) {		725

	$("map_canvas").style.backgroundImage = "url(./markers/loading.jpg)";
//	$("map").style.backgroundImage = "url('http://maps.google.com/staticmap?center=<?php echo get_variable('def_lng');?>,<?php echo get_variable('def_lat');?>&zoom=<?php echo get_variable('def_zoom');?>&size=<?php echo round ($map_factor * get_variable('map_width'));?>x<?php echo round ($map_factor * get_variable('map_height'));?>&key=<?php echo get_variable('gmaps_api_key');?> ')";

	var colors = new Array ('odd', 'even');

	function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity, name) {		// 8/19/09, 2/26/2013
		var circle = new google.maps.Circle({
				center: new google.maps.LatLng(lat,lng),
				map: map,
				fillColor: fillColor,
				fillOpacity: fillOpacity,
				strokeColor: strokeColor,
				strokeOpacity: strokeOpacity,
				strokeWeight: strokeWidth
			});
		circle.setRadius(radius*5000); 
		}
		
	function drawBanner(point, html, text, font_size, color, name) {        // Create the banner - 6/5/2013
		var invisibleIcon = new google.maps.MarkerImage("./markers/markerTransparent.png");
		map.setCenter(point, 8);
		var the_color = (typeof color == 'undefined')? "000000" : color ;	// default to black
		var label = new ELabel({
			latlng: point, 
			label: html, 
			classname: "label", 
			offset: new google.maps.Size(-8, 4), 
			opacity: 100,
			theSize: font_size + "px",		
			theColor:add_hash(the_color),
			overlap: true,
			clicktarget: false
			});	
		label.setMap(map);		
		var marker = new google.maps.Marker(point,invisibleIcon);	        // Create an invisible google.maps.Marker
		marker.setMap(map);				
		}				// end function draw Banner()
		
	function URLEncode(plaintext ) {					// 3/15/11 The Javascript escape and unescape functions do,
														// NOT correspond with what browsers actually do...
		var SAFECHARS = "0123456789" +					// Numeric
						"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
						"abcdefghijklmnopqrstuvwxyz" +	// guess
						"-_.!*'()";					// RFC2396 Mark characters
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

//	Tickets show / hide by Priority functions

	function set_initial_pri_disp() {
		$('normal').checked = true;
		$('medium').checked = true;
		$('high').checked = true;
		$('all').checked = true;
		$('none').checked = false;
	}

	function hideGroup(color, category) {			// 8/7/09 Revised function to correct incorrect display, revised 12/03/10 completely revised
		var priority = color;
		var priority_name="";
		if(priority == 1) {
			priority_name="normal";
		}
		if(priority == 2) {
			priority_name="medium";
		}
		if(priority == 3) {
			priority_name="high";
		}
		if(priority == 4) {
			priority_name="all";
		}

		if(priority == 5) {
			priority_name="none";
		}

		if(priority == 1) {
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if ((gmarkers[i].id == priority) && (gmarkers[i].category == category)) {
//						gmarkers[i].show();
						gmarkers[i].setMap(map);		
						}
					if ((gmarkers[i].id != priority) && (gmarkers[i].category == category)) {
//						gmarkers[i].hide();
						gmarkers[i].setMap(null);		
						}

					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = true;
			$('medium').checked = false;
			$('high').checked = false;
			$('all').checked = false;
			$('none').checked = false;
			$('pri_all').style.display = '';
			$('pri_none').style.display = '';
			}	//	end if priority == 1
		if(priority == 2) {
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if ((gmarkers[i].id == priority) && (gmarkers[i].category == category)) {
//						gmarkers[i].show();
						gmarkers[i].setMap(map);		
						}
					if ((gmarkers[i].id != priority) && (gmarkers[i].category == category)) {
//						gmarkers[i].hide();
						gmarkers[i].setMap(null);		
						}

					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = false;
			$('medium').checked = true;
			$('high').checked = false;
			$('all').checked = false;
			$('none').checked = false;
			$('pri_all').style.display = '';
			$('pri_none').style.display = '';
			}	//	end if priority == 2
		if(priority == 3) {
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if ((gmarkers[i].id == priority) && (gmarkers[i].category == category)) {
//						gmarkers[i].show();
						gmarkers[i].setMap(map);		
						}
					if ((gmarkers[i].id != priority) && (gmarkers[i].category == category)) {
//						gmarkers[i].hide();
						gmarkers[i].setMap(null);		
						}

					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = false;
			$('medium').checked = false;
			$('high').checked = true;
			$('all').checked = false;
			$('none').checked = false;
			$('pri_all').style.display = '';
			$('pri_none').style.display = '';
			}	//	end if priority == 3
		if(priority == 4) {		//	show All
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if (gmarkers[i].category == category) {
//						gmarkers[i].show();
						gmarkers[i].setMap(map);		
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = true;
			$('medium').checked = true;
			$('high').checked = true;
			$('all').checked = true;
			$('none').checked = false;
			$('pri_all').style.display = 'none';
			$('pri_none').style.display = '';
			}	//	end if priority == 4
		if(priority == 5) {		// hide all
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if (gmarkers[i].category == category) {
//						gmarkers[i].hide();
						gmarkers[i].setMap(null);		
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = false;
			$('medium').checked = false;
			$('high').checked = false;
			$('all').checked = false;
			$('none').checked = true;
			$('pri_all').style.display = '';
			$('pri_none').style.display = 'none';
			}	//	end if priority == 5
		}			// end function hideGroup(color, category)

	function set_pri_chkbox(control) {
		var pri_control = control;
		if($(pri_control).checked == true) {
			$(pri_control).checked = false;
			} else {
			$(pri_control).checked = true;
			}
		}

//	End of Tickets show / hide by Priority functions		

// 	Units show / hide functions				
		
	function set_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
//		alert(943);
//		alert($('ALL').style.display );
		var curr_cats = <?php echo json_encode($curr_cats); ?>;
		var cat_sess_stat = <?php echo json_encode($cat_sess_stat); ?>;
		var hidden = <?php print json_encode($hidden); ?>;
		var shown = <?php print json_encode($shown); ?>;
		var number_of_units = <?php print get_no_units(); ?>;
		if(hidden!=0) {
			$('ALL').style.display = '';
			$('ALL_BUTTON').style.display = '';
			$('ALL').checked = false;	
		} else {			
			$('ALL').style.display = 'none';
			$('ALL_BUTTON').style.display = 'none';
			$('ALL').checked = false;
		}
		if((shown!=0) && (number_of_units > 0)) {
			$('NONE').style.display = '';
			$('NONE_BUTTON').style.display = '';
			$('NONE').checked = false;	
		} else {
			$('NONE').style.display = 'none';
			$('NONE_BUTTON').style.display = 'none';
			$('NONE').checked = false;
		}
		for (var i = 0; i < curr_cats.length; i++) {
			var catname = curr_cats[i];
			if(cat_sess_stat[i]=="s") {
				for (var j = 0; j < gmarkers.length; j++) {
					if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == catname)) {
//						gmarkers[j].show();
						gmarkers[j].setMap(map);		
						var catid = catname + j;
						if($(catid)) {
							$(catid).style.display = "";
							}
						}
					}
				$(catname).checked = true;
			} else {
				for (var j = 0; j < gmarkers.length; j++) {
					if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == catname)) {
//						gmarkers[j].hide();
						gmarkers[j].setMap(null);		
						var catid = catname + j;
						if($(catid)) {
							$(catid).style.display = "none";
							}
						}
					}
				$(catname).checked = false;
				}				
			}
		}
		

	function do_view_cats() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('go_can').style.display = 'inline';
		$('can_button').style.display = 'inline';
		$('go_button').style.display = 'inline';
		}

	function cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('go_can').style.display = 'none';
		$('can_button').style.display = 'none';
		$('go_button').style.display = 'none';
		$('ALL').checked = false;
		$('NONE').checked = false;
		}

	function set_chkbox(control) {
		var units_control = control;
		if($(units_control).checked == true) {
			$(units_control).checked = false;
			} else {
			$(units_control).checked = true;
			}
		do_view_cats();
		}

	function do_go_button() {							// 12/03/10	Show Hide categories
		var curr_cats = <?php echo json_encode($curr_cats); ?>;
		if ($('ALL').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(category).checked = true;				
				for (var j = 0; j < gmarkers.length; j++) {
					var catid = category + j;
					if($(catid)) {
						$(catid).style.display = "";
					}
					if((gmarkers[j]) && (gmarkers[j].category!="Incident")) {				
//					gmarkers[j].show();
					gmarkers[j].setMap(map);		
					}
					}
				}
				$('ALL').checked = false;
				$('ALL').style.display = 'none';
				$('ALL_BUTTON').style.display = 'none';				
				$('NONE').style.display = '';
				$('NONE_BUTTON').style.display = '';				
				$('go_button').style.display = 'none';
				$('can_button').style.display = 'none';				

		} else if ($('NONE').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);	
				$(category).checked = false;				
				for (var j = 0; j < gmarkers.length; j++) {
					var catid = category + j;
					if($(catid)) {
						$(catid).style.display = "none";
					}
					if((gmarkers[j]) && (gmarkers[j].category!="Incident")) {
//						gmarkers[j].hide();
						gmarkers[j].setMap(null);		
					}
					}
				}
				$('NONE').checked = false;
				$('ALL').style.display = '';
				$('ALL_BUTTON').style.display = '';				
				$('NONE').style.display = 'none';
				$('NONE_BUTTON').style.display = 'none';					
				$('go_button').style.display = 'none';
				$('can_button').style.display = 'none';
		} else {
			var x = 0;
			var y = 0;
			for (var i = 0; i < curr_cats.length; i++) {

				var category = curr_cats[i];
				if ($(category).checked == true) {
					x++;
					var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
					var url = "persist2.php";	//	3/15/11
					sendRequest (url, gb_handleResult, params);
					$(category).checked = true;			
					for (var j = 0; j < gmarkers.length; j++) {
						var catid = category + j;
						if($(catid)) {
							$(catid).style.display = "";
							}
						if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == category)) {			
//							gmarkers[j].show();
							gmarkers[j].setMap(map);		
							}
						}
					}
				}
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];				
				if ($(category).checked == false) {
					y++;
					var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
					var url = "persist2.php";	//	3/15/11
					sendRequest (url, gb_handleResult, params);
					$(category).checked = false;
					var y=0;
					for (var j = 0; j < gmarkers.length; j++) {
						var catid = category + j;
						if($(catid)) {
							$(catid).style.display = "none";
							}
						if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == category)) {			
//							gmarkers[j].hide();
							gmarkers[j].setMap(null);		
							}
						}
					}	
				}
			}



		$('go_button').style.display = 'none';
		$('can_button').style.display = 'none';

		if((x > 0) && (x < curr_cats.length)) {
			$('ALL').style.display = '';
			$('ALL_BUTTON').style.display = '';
			$('NONE').style.display = '';
			$('NONE_BUTTON').style.display = '';
		}
		if(x == 0) {
			$('ALL').style.display = '';
			$('ALL_BUTTON').style.display = '';
			$('NONE').style.display = 'none';
			$('NONE_BUTTON').style.display = 'none';
		}
		if(x == curr_cats.length) {
			$('ALL').style.display = 'none';
			$('ALL_BUTTON').style.display = 'none';
			$('NONE').style.display = '';
			$('NONE_BUTTON').style.display = '';
		}


	}	// end function do_go_button()

	function gb_handleResult(req) {							// 12/03/10	The persist callback function
		}

// Facilities show / hide functions		

	function set_fac_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show, revised 3/15/11.
		var fac_curr_cats = <?php echo json_encode(get_fac_category_butts()); ?>;
		var fac_cat_sess_stat = <?php echo json_encode(get_fac_session_status()); ?>;
		var fac_hidden = <?php print find_fac_hidden(); ?>;
		var fac_shown = <?php print find_fac_showing(); ?>;
		if(fac_hidden!=0) {
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';
			$('fac_ALL').checked = false;	
		} else {			
			$('fac_ALL').style.display = 'none';
			$('fac_ALL_BUTTON').style.display = 'none';
			$('fac_ALL').checked = false;
		}
		if(fac_shown!=0) {
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';
			$('fac_NONE').checked = false;
		} else {
			$('fac_NONE').style.display = 'none';
			$('fac_NONE_BUTTON').style.display = 'none';
			$('fac_NONE').checked = false;
		}
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_catname = fac_curr_cats[i];
			if(fac_cat_sess_stat[i]=="s") {
				for (var j = 0; j < fmarkers.length; j++) {
					if (fmarkers[j].category == fac_catname) {
//						fmarkers[j].show();
						fmarkers[j].setMap(map);		
						var fac_catid = fac_catname + j;
						if($(fac_catid)) {
							$(fac_catid).style.display = "";
							}
						}
					}
				$(fac_catname).checked = true;
			} else {
				for (var j = 0; j < fmarkers.length; j++) {
					if (fmarkers[j].category == fac_catname) {
//						fmarkers[j].hide();
						fmarkers[j].setMap(null);		
						var fac_catid = fac_catname + j;
						if($(fac_catid)) {
							$(fac_catid).style.display = "none";
							}
						}
					}
				$(fac_catname).checked = false;
				}				
			}
		}

	function do_view_fac_cats() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('fac_go_can').style.display = 'inline';
		$('fac_can_button').style.display = 'inline';
		$('fac_go_button').style.display = 'inline';
		}

	function fac_cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('fac_go_can').style.display = 'none';
		$('fac_can_button').style.display = 'none';
		$('fac_go_button').style.display = 'none';
		$('fac_ALL').checked = false;
		$('fac_NONE').checked = false;
		}

	function set_fac_chkbox(control) {
		var fac_control = control;
		if($(fac_control).checked == true) {
			$(fac_control).checked = false;
			} else {
			$(fac_control).checked = true;
			}
		do_view_fac_cats();
		}

	function do_go_facilities_button() {							// 12/03/10	Show Hide categories
		var fac_curr_cats = <?php echo json_encode(get_fac_category_butts()); ?>;
		if ($('fac_ALL').checked == true) {
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var fac_category = fac_curr_cats[i];
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(fac_category).checked = true;				
				for (var j = 0; j < fmarkers.length; j++) {
					var fac_catid = fac_category + j;
					if($(fac_catid)) {
						$(fac_catid).style.display = "";
					}
					if(fmarkers[j].category != "Incident") {				
//					fmarkers[j].show();
					fmarkers[j].setMap(map);		
					}
					}
				}
				$('fac_ALL').checked = false;
				$('fac_ALL').style.display = 'none';
				$('fac_ALL_BUTTON').style.display = 'none';				
				$('fac_NONE').style.display = '';
				$('fac_NONE_BUTTON').style.display = '';				
				$('fac_go_button').style.display = 'none';
				$('fac_can_button').style.display = 'none';

		} else if ($('fac_NONE').checked == true) {
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var fac_category = fac_curr_cats[i];
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);	
				$(fac_category).checked = false;				
				for (var j = 0; j < fmarkers.length; j++) {
					var fac_catid = fac_category + j;
					if($(fac_catid)) {
						$(fac_catid).style.display = "none";
					}
					if(fmarkers[j].category != "Incident") {
//						fmarkers[j].hide();
						fmarkers[j].setMap(null);		
					}
					}
				}
				$('fac_NONE').checked = false;
				$('fac_ALL').style.display = '';
				$('fac_ALL_BUTTON').style.display = '';				
				$('fac_NONE').style.display = 'none';
				$('fac_NONE_BUTTON').style.display = 'none';					
				$('fac_go_button').style.display = 'none';
				$('fac_can_button').style.display = 'none';
		} else {
			var x = 0;
			var y = 0;
			for (var i = 0; i < fac_curr_cats.length; i++) {

				var fac_category = fac_curr_cats[i];
				if ($(fac_category).checked == true) {
					if($('fac_table').style.display == 'none') {
						$('fac_table').style.display = 'inline-block';
						$('side_bar_f').style.display = 'inline-block';
						}
					x++;
					var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
					var url = "persist2.php";	//	3/15/11
					sendRequest (url, gb_handleResult, params);
					$(fac_category).checked = true;			
					for (var j = 0; j < fmarkers.length; j++) {
						var fac_catid = fac_category + j;
						if($(fac_catid)) {
							$(fac_catid).style.display = "";
							}
						if(fmarkers[j].category == fac_category) {			
//							fmarkers[j].show();
							fmarkers[j].setMap(map);		
							}
						}
					}
				}
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var fac_category = fac_curr_cats[i];				
				if ($(fac_category).checked == false) {
					y++;
					var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
					var url = "persist2.php";	//	3/15/11
					sendRequest (url, gb_handleResult, params);
					$(fac_category).checked = false;
					var y=0;
					for (var j = 0; j < fmarkers.length; j++) {
						var fac_catid = fac_category + j;

						if($(fac_catid)) {
							$(fac_catid).style.display = "none";
							}
						if(fmarkers[j].category == fac_category) {			
//							fmarkers[j].hide();
							fmarkers[j].setMap(null);		
							}
						}
					}	
				}
			}

		var hidden = <?php print $hidden; ?>;
		var shown = <?php print $shown; ?>;
		$('fac_go_button').style.display = 'none';
		$('fac_can_button').style.display = 'none';

		if((x > 0) && (x < fac_curr_cats.length)) {
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';
		}
		if(x == 0) {
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';
			$('fac_NONE').style.display = 'none';
			$('fac_NONE_BUTTON').style.display = 'none';
		}
		if(x == fac_curr_cats.length) {
			$('fac_ALL').style.display = 'none';
			$('fac_ALL_BUTTON').style.display = 'none';
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';
		}


	}	// end function do_go_button()

	function gfb_handleResult(req) {							// 12/03/10	The persist callback function
		}

// end of facilities show / hide functions

// show hide polygons
	function do_view_bnd() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('bnd_go_can').style.display = 'inline';
		$('bnd_can_button').style.display = 'inline';
		$('bnd_go_button').style.display = 'inline';
		}

	function bnd_cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('bnd_go_can').style.display = 'none';
		$('bnd_can_button').style.display = 'none';
		$('bnd_go_button').style.display = 'none';
		$('BND_ALL').checked = false;
		$('BND_NONE').checked = false;
		}

	function set_bnd_chkbox(control) {
		var bnd_control = control;
		if($(bnd_control).checked == true) {
			$(bnd_control).checked = false;
			} else {
			$(bnd_control).checked = true;
			}
		do_view_bnd();
		}

	function do_go_bnd_button() {							// 12/03/10	Show Hide categories
		var bnd_curr = bound_names;
		if ($('BND_ALL').checked == true) {
			for (var i = 0; i < bnd_curr.length; i++) {
				var bnds = bnd_curr[i];
				var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gbb_handleResult, params);
				$(bnds).checked = true;				
				if(boundary[i]) {				
//					boundary[i].show();
					boundary[i].setMap(map);		
					}
				}
				$('BND_ALL').checked = false;
				$('BND_ALL').style.display = 'none';
				$('BND_ALL_BUTTON').style.display = 'none';				
				$('BND_NONE').style.display = '';
				$('BND_NONE_BUTTON').style.display = '';				
				$('bnd_go_button').style.display = 'none';
				$('bnd_can_button').style.display = 'none';

		} else if ($('BND_NONE').checked == true) {
			for (var i = 0; i < bnd_curr.length; i++) {
				var bnds = bnd_curr[i];
				var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gbb_handleResult, params);	
				$(bnds).checked = false;				
				if(boundary[i]) {				
//					boundary[i].hide();
					boundary[i].setMap(null);		
					}
				}
				$('BND_NONE').checked = false;
				$('BND_ALL').style.display = '';
				$('BND_ALL_BUTTON').style.display = '';				
				$('BND_NONE').style.display = 'none';
				$('BND_NONE_BUTTON').style.display = 'none';					
				$('bnd_go_button').style.display = 'none';
				$('bnd_can_button').style.display = 'none';
		} else {
			var x = 0;
			var y = 0;
			for (var i = 0; i < bnd_curr.length; i++) {
				var bnds = bnd_curr[i];
				if ($(bnds) && ($(bnds).checked == true)) {
					x++;
					var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
					var url = "persist2.php";	//	3/15/11
					sendRequest (url, gbb_handleResult, params);
					$(bnds).checked = true;		
					if((boundary[i]) && (bound_names.contains(bnds))) {			
//						boundary[i].show();
						boundary[i].setMap(map);		
						}
					}
				}
			for (var i = 0; i < bnd_curr.length; i++) {
				var bnds = bnd_curr[i];	
				if ($(bnds) && ($(bnds).checked == false)) {
					y++;
					var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
					var url = "persist2.php";	//	3/15/11
					sendRequest (url, gbb_handleResult, params);
					$(bnds).checked = false;
					if((boundary[i]) && (bound_names.contains(bnds))) {			
//						boundary[i].hide();
						boundary[i].setMap(null);		
						}
					}
				}	
			}

		var hidden = <?php print find_bnd_hidden(); ?>;
		var shown = <?php print find_bnd_showing(); ?>;
		$('bnd_go_button').style.display = 'none';
		$('bnd_can_button').style.display = 'none';

		if((x > 0) && (x < bnd_curr.length)) {
			$('BND_ALL').style.display = '';
			$('BND_ALL_BUTTON').style.display = '';
			$('BND_NONE').style.display = '';
			$('BND_NONE_BUTTON').style.display = '';
		}
		if(x == 0) {
			$('BND_ALL').style.display = '';
			$('BND_ALL_BUTTON').style.display = '';
			$('BND_NONE').style.display = 'none';
			$('BND_NONE_BUTTON').style.display = 'none';
		}
		if(x == bnd_curr.length) {
			$('BND_ALL').style.display = 'none';
			$('BND_ALL_BUTTON').style.display = 'none';
			$('BND_NONE').style.display = '';
			$('BND_NONE_BUTTON').style.display = '';
		}

	}	// end function do_go_bnd_button()

	function set_bnds() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show, revised 3/15/11.
		var bnd_curr = <?php echo json_encode(get_bnd_session()); ?>;
		var bnd_names_curr = <?php echo json_encode(get_bnd_session_names()); ?>;
		var fac_hidden = <?php print find_bnd_hidden(); ?>;
		var fac_shown = <?php print find_bnd_showing(); ?>;
		if(fac_hidden!=0) {
			if($('BND_ALL')) { $('BND_ALL').style.display = '';}
			if($('BND_ALL_BUTTON')) { $('BND_ALL_BUTTON').style.display = '';}
			if($('BND_ALL')) { $('BND_ALL').checked = false;}	
		} else {			
			if($('BND_ALL')) { $('BND_ALL').style.display = 'none';}
			if($('BND_ALL_BUTTON')) { $('BND_ALL_BUTTON').style.display = 'none';}
			if($('BND_ALL')) { $('BND_ALL').checked = false;}
		}
		if(fac_shown!=0) {
			if($('BND_NONE')) { $('BND_NONE').style.display = '';}
			if($('BND_NONE_BUTTON')) { $('BND_NONE_BUTTON').style.display = '';}
			if($('BND_NONE')) { $('BND_NONE').checked = false;}
		} else {
			if($('BND_NONE')) { $('BND_NONE').style.display = 'none';}
			if($('BND_NONE_BUTTON')) { $('BND_NONE_BUTTON').style.display = 'none';}
			if($('BND_NONE')) { $('BND_NONE').checked = false;}
		}
		for (var i = 0; i < bnd_curr.length; i++) {
			var bnds = bnd_curr[i];
			var bnd_nm = bnd_names_curr[i];
			if(bnds == "s") {
//				boundary[i].show();
				boundary[i].setMap(map);		
				$(bnd_nm).checked = true;
				} else {
//				boundary[i].hide();
				boundary[i].setMap(null);		
				$(bnd_nm).checked = false;
				}				
			}
		}
		
	function gbb_handleResult(req) {							// 12/03/10	The persist callback function
		}

// end of functions for showing and hiding boundaries
var show_cont;
var hide_cont;	
var divarea;	

	function hideDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}			
		var divarea = div_area 
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = 'none';
			$(hide_cont).style.display = 'none';
			$(show_cont).style.display = '';
			} 
		var params = "f_n=" +controlarea+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);			
		} 

	function showDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}				
		var divarea = div_area
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = '';
			$(hide_cont).style.display = '';
			$(show_cont).style.display = 'none';
			}
		var params = "f_n=" +controlarea+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);					
		} 

	function show_All() {						// 8/7/09 Revised function to correct incorrect display, 12/03/10, revised to remove units show and hide from this function
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].category == "Incident") {
//				gmarkers[i].show();
				gmarkers[i].setMap(map);		
				}
				}
			} 	// end for ()
		$("show_all_icon").style.display = "none";
		$("incidents").style.display = "inline-block";
		}			// end function

	var starting = false;
	
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
	
	function checkForm(form)	{	//	5/3/11
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
		
	function form_validate(theForm) {	//	5/3/11
//		alert("Validating");
		checkForm(theForm);
		}				// end function validate(theForm)	

	function do_mail_fac_win(id) {			// Facility email 9/22/09
		if(starting) {return;}					
		starting=true;	
		var url = "do_fac_mail.php?fac_id=" + id;	
		newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_in)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_in.focus();
		starting = false;
		}

	function do_sel_update (in_unit, in_val) {							// 12/17/09
		to_server(in_unit, in_val);
		}

	function do_sel_update_fac (in_unit, in_val) {							// 3/15/11
		to_server_fac(in_unit, in_val);
		}

	function do_sidebar_unit (instr, id, sym, myclass, tip_str, category) {		
							// sidebar_string, sidebar_index, row_class, icon_info, mouseover_str - 1/7/09
		var tr_id = category + id;
		if (isNull(tip_str)) {
			side_bar_html += "<TR ID = '" + tr_id + "' CLASS='" + colors[(id)%2] +"'><TD CLASS='" + myclass + "' onClick = myclick(" + id + "); ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 2/6/10 moved onclick to TD
			}
		else {
			side_bar_html += "<TR ID =  '" + tr_id + "' onMouseover=\"Tip('" + tip_str + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[(id)%2] +"'><TD CLASS='" + myclass + "' onClick = myclick(" + id + "); ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 1/3/10 added tip param		
			}
		}		// end function do sidebar_unit ()

<?php		// 5/17/10
		$use_quick = (((integer)$func == 0) || ((integer)$func == 10)) ? FALSE : TRUE ;	//	11/29/10
		if ($use_quick) 			{$js_func = "open_tick_window";}			//	11/29/10
		elseif (($quick) && (!is_guest())) 	{$js_func = "myclick_ed_tick";}
		else 								{$js_func = "myclick";}

?>	
	function open_tick_window (id) {				// 5/2/10
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}	
		
	function do_patient(id) {			// patient edit 6/23/11
		if(starting) {return;}					
		starting=true;	
		var url = "patient_w.php?action=list&ticket_id=" + id;	
		newwindow=window.open (url, 'Patient_Window', 'titlebar, resizable=1, scrollbars, height=300,width=550,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=50,screenY=150');
		if (isNull(newwindow)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		starting = false;
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
		google.maps.event.trigger(gmarkers[id], "click");
		location.href = "#top";
		}

	function do_sidebar (instr, id, sym, myclass, tip_str) {		// sidebar_string, sidebar_index, row_class, icon_info, mouseover_str - 1/7/09
		var tr_id = tr_id_fixed_part + id;		// e.g., 'tr_id_n'
		side_bar_html += "<TR onClick = 'myclick(" + id + ");' ID =  '" + tr_id + "' onMouseover=\"Tip('" + tip_str + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[id%2] +"'>";
		side_bar_html += "<TD CLASS='" + myclass + "' ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 1/3/10 added tip param		
		}		// end function do sidebar ()

	function do_sidebar_t_ed (instr, line_no, rcd_id, letter, tip_str) {		// ticket edit, tip str added 1/3/10
		side_bar_html += "<TR onMouseover=\"Tip('" + tip_str.replace("'", "") + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[(line_no)%2] +"'>";		
		side_bar_html += "<TD CLASS='td_data'>" + letter + "</TD>" + instr +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		}

	function do_sidebar_u_iw (instr, id, sym, myclass) {						// constructs unit incident sidebar row - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		side_bar_html += "<TR ID = '" + tr_id + "' CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='" + myclass + "'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 10/30/09 removed period
		}		// end function do sidebar ()

	function myclick_ed_tick(id) {				// Responds to sidebar click - edit ticket data
<?php
	$the_action = (is_guest()) ? "main.php" : $_SESSION['editfile'];				2/27/10
?>	
		document.tick_form.id.value=id;			// 11/27/09
		document.tick_form.action='<?php print $the_action; ?>';			// 11/27/09
		document.tick_form.submit();
		}

	function do_sidebar_u_ed (sidebar, line_no, on_click, letter, tip, category) {					// unit edit - letter = icon str
		var tr_id = category + line_no;
		side_bar_html += "<TR ID = '" + tr_id + "'  CLASS='" + colors[(line_no+1)%2] +"'>";
		side_bar_html += "<TD onClick = '" + on_click+ "' CLASS='td_data'>" + letter + "</TD>" + sidebar +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		}

	function myclick_nm(id) {				// Responds to sidebar click - view responder data
		document.unit_form.id.value=id;	// 11/27/09
		if (quick) {
			document.unit_form.edit.value="true";
			}
		else {
			document.unit_form.view.value="true";
			}
		document.unit_form.submit();
		}

	function do_sidebar_fac_ed (fac_instr, fac_id, fac_sym, myclass, fac_type) {					// constructs facilities sidebar row 9/22/09
		var fac_type_id = fac_type + fac_id;
		side_bar_html += "<TR ID = '" + fac_type_id + "' CLASS='" + colors[(fac_id+1)%2] +"'>";
		side_bar_html += "<TD width='5%' CLASS='" + myclass + "' onClick = fac_click_ed(" + fac_id + ");><B>" + (fac_sym) + "</B></TD>";
		side_bar_html += fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac_ed ()

	function do_sidebar_fac_iw (fac_instr, fac_id, fac_sym, myclass, fac_type) {					// constructs facilities sidebar row 9/22/09
		var fac_type_id = fac_type + fac_id;
		side_bar_html += "<TR ID = '" + fac_type_id + "' CLASS='" + colors[(fac_id+1)%2] +"' WIDTH = '100%';>"
		side_bar_html += "<TD width='5%' CLASS='" + myclass + "'><B>" + (fac_sym) + "</B></TD>";
		side_bar_html += fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac_iw ()

	function fac_click_iw(fac_id) {						// Responds to facilities sidebar click, triggers listener above 9/22/09
//		google.maps.trigger(fmarkers[fac_id], "click");
		google.maps.event.trigger(fmarkers[fac_id], "click");

		}

	function fac_click_ed(id) {							// Responds to facility sidebar click - edit data
		document.facy_form_ed.id.value=id;					// 11/27/09
		document.facy_form_ed.submit();
		}
		
	var curr_loc;	//	3/15/11
	var currzoom;	//	3/15/11
	var currbnds;	//	3/15/11
	var open_iw = false;
	
// Creates marker and sets up click event infowindow 10/21/09 added stat to hide unavailable units, 12/03/10 added category. 3/19/11 added tip param
	var icons=[];						// note globals
	icons[0] = 											 4;	// units white
	icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
	icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
	icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
	icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black

	function createMarker(point, tabs, color, stat, id, sym, category, region, tip) {		// 1804 - 3/19/11
		var group = category || 0;			// if absent from call
		var region = region || 0;
		var tip_val = tip || "";		// if absent from call
		got_points = true;				// 6/21/12
		
		var origin = ((sym.length)>3)? (sym.length)-3: 0;			// pick low-order three chars 3/22/11
		var iconStr = sym.substring(origin);						// icon string
		var image_file = "./our_icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + iconStr;
		var marker = new google.maps.Marker({position: point, map: map, icon: image_file});		
		marker.id = color;				// for hide/unhide
		marker.category = category;		// 12/03/10 for show / hide by status
		marker.region = region;			// 12/03/10 for show / hide by status		
		marker.stat = stat;				// 10/21/09

		google.maps.event.addListener(marker, "click", function() {		// 1811 - here for both side bar and icon click
			try  {open_iw.close()} catch (e) {;}
//			if (open_iw) {open_iw.close();} 							// another IW possibly open
			map.setCenter(point, 8);

			var infowindow = new google.maps.InfoWindow({ content: tabs, maxWidth: 400});	 
			infowindow.open(map, marker);
			open_iw = infowindow;
			which = id;
			});							// end add Listener( ... function())
		gmarkers[id] = marker;							// marker to array for side_bar click function
		gmarkers[id]['x'] = "y";							// ????
		infoTabs[id] = tabs;							// tabs to array
		bounds.extend(point);
		return marker;
		}				// end function create Marker()

	function test(location) { 	//	3/15/11
 		alert(location) 
		}

	function createdummyMarker(point, tabs, color, id, unit_id) {
		got_points = true;											// 6/18/12
		var image_file = "./our_icons/question1.png";
		var dummymarker = new google.maps.Marker({position: point, map: map, icon: image_file});		
		dummymarker.id = color;				// for hide/unhide - unused
		google.maps.event.addListener(dummymarker, "click", function() {		// here for both side bar and icon click
			if (dummymarker) {
				try {open_iw.close()} catch(err) {;}
				map.setZoom(8);
				map.setCenter(point);
				infowindow = new google.maps.InfoWindow({ content: tabs, maxWidth: 300});	 
				open_iw = infowindow;				
				infowindow.open(map, dummymarker);
				}		// end if (marker)
			});			// end google.maps.Event.add Listener()
		gmarkers[id] = dummymarker;									// marker to array for side bar click function
		infoTabs[id] = tabs;									// tabs to array
		if (!(map_is_fixed)) {				// 4/3/09
			bounds.extend(point);
			map.fitBounds(bounds);			
			}
		return dummymarker;
		}				// end function create dummy Marker()

	var grid_bool = false;		
	function toglGrid() {						// toggle
		grid_bool = !grid_bool;
		if (grid_bool)	{ grid = new Graticule(map); }
		else 			{ grid.setMap(null); }
		}		// end function toglGrid()
	

	var trafficInfo = new google.maps.TrafficLayer();
	trafficInfo.setMap(null);
	
    var toggleState = false;

	function doTraffic() {				// 10/16/08
		if (toggleState) {
			trafficInfo.setMap(null);
			}
		else {
			trafficInfo.setMap(map);		
			}
		toggleState = !toggleState;			// swap
		}				// end function doTraffic()

	var weatherLayer = new google.maps.weather.WeatherLayer({
	  temperatureUnits: google.maps.weather.TemperatureUnit.FAHRENHEIT
	});

	var cloudLayer = new google.maps.weather.CloudLayer();
	var toggleWeather = false	
	weatherLayer.setMap(null);
	cloudLayer.setMap(null);	
	
	function doWeather() {
		if (toggleWeather) {
			weatherLayer.setMap(null);
			cloudLayer.setMap(null);
			}
		else {
			weatherLayer.setMap(map);
			cloudLayer.setMap(map);				
			}
		toggleWeather = !toggleWeather;			// swap
		}				// end function doWeather()		
			
	var icons=[];						// note globals
	icons[0] = 											 4;	// units white
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black

	var center;
	var zoom = <?php echo get_variable('def_zoom'); ?>;
<?php

$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";
print (($dzf==1) || ($dzf==3))? "true;\n":"false;\n";

$kml_olays = array();
$dir = "./kml_files";
if ($dh = @opendir($dir)) {				// 12/8/10
	$i = 1;
	$temp = explode ("/", $_SERVER['REQUEST_URI']);
	$temp[count($temp)-1] = "kml_files";				//
	$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
	while (false !== ($filename = @readdir($dh))) {				// 12/8/10
		if (!is_dir($filename)) {
		    echo "\tvar kml_{$i} = new google.maps.KmlLayer(\"{$server_str}{$filename}\");\n";		// V3
			echo "\tkml_{$i}.setMap(map);\n";
		    $i++;
		    }
		}		// end if ($dh = @opendir($dir))
	}		

//	var ctaLayer = new google.maps.KmlLayer('http://gmaps-samples.googlecode.com/svn/trunk/ggeoxml/cta.kml');
//	ctaLayer.setMap(map);

?>

function do_add_note (id) {				// 8/12/09
	var url = "add_note.php?ticket_id="+ id;
	var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
	noteWindow.focus();
	}

function do_sort_sub(sort_by){				// 6/11/10
	document.sort_form.order.value = sort_by;
	document.sort_form.submit();
	}

function do_fac_sort_sub(sort_by){				// 3/15/11
	document.fac_sort_form.forder.value = sort_by;
	document.fac_sort_form.submit();
	}
	
function do_track(callsign) {		
	if (parent.frames["upper"].logged_in()) {
//		if(starting) {return;}					// 6/6/08
//		starting=true;
		try  {map.closeInfoWindow()} catch(err){;}
//		map.closeInfoWindow();
		var width = <?php print get_variable('map_width');?>+360;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "track_u.php?source="+callsign;

		newwindow=window.open(url, callsign,  spec);
		if (isNull(newwindow)) {
			alert ("Track display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function do track()

function do_popup(id) {					// added 7/9/09
//	alert(1980);
	if (parent.frames["upper"].logged_in()) {
		try  {map.closeInfoWindow()} catch(err){;}
//		map.closeInfoWindow();
		var mapWidth = <?php print get_variable('map_width');?>+32;
		var mapHeight = <?php print get_variable('map_height');?>+200;		// 3/12/10
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "incident_popup.php?id="+id;

		newwindow=window.open(url, id, spec);
		if (isNull(newwindow)) {
			alert ("Popup Incident display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function do popup()

function do_sched_jobs(choice) {		// 11/29/10 - added Scheduled tickets to menu. 12/02/10 Added persistance for the list view
	var params = "f_n=list_type&v_n=" + choice + "&sess_id=<?php print get_sess_key(__LINE__); ?>";					// 3/15/11	flag 1, value h
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, cs_handleResult, params);	// ($to_str, $text, $ticket_id)
	document.to_listtype.func.value=choice;
	}				// end function do_listtype()

function do_curr_jobs(choice) {		// 11/29/10 - added Scheduled tickets to menu. 12/02/10 Added persistance for the list view
	var params = "f_n=list_type&v_n=" + choice + "&sess_id=<?php print get_sess_key(__LINE__); ?>";					// 3/15/11 flag 1, value h
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, cs_handleResult, params);	// ($to_str, $text, $ticket_id)
	document.to_listtype.func.value=choice;
	}				// end function do_listtype()

function do_listtype(choice) {		// 11/29/10 - added Scheduled tickets to menu. 12/02/10 Added persistance for the list view
	var params = "f_n=list_type&v_n=" + choice + "&sess_id=<?php print get_sess_key(__LINE__); ?>";					// 3/15/11 flag 1, value h
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, l_handleResult, params);	// ($to_str, $text, $ticket_id)
	document.to_listtype.func.value=choice;
	show_btns_closed()
	}				// end function do_listtype()
	
function l_handleResult(req) {					// the 'called-back' persist function - nill content for the tickets list type persistance
	}

function cs_handleResult(req) {					// the 'called-back' function for show current or scheduled
	document.to_listtype.submit();	
	}

	var side_bar_html = "<TABLE border=0 CLASS='sidebar' WIDTH = <?php print $col_width;?> >";
	var sched_html = "";	//	3/15/11
<?php
	$hide_limit = get_variable('hide_booked');		// 5/26/2013
	$query_sched = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `booked_date` >= (NOW() + INTERVAL {$hide_limit} HOUR)";	//	11/29/10
	$result_sched = mysql_query($query_sched) or do_error($query_sched, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	11/29/10
	$num_sched = mysql_num_rows($result_sched);	//	11/29/10
	
	if (($num_sched != 0) && ($func != 10)) {	//	11/29/10
		$scheduled_link = ($num_sched >= 1) ? "Scheduled Jobs: " . $num_sched : "Scheduled Jobs: " . $num_sched;
	$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
?>
		sched_html +="\t\t<SPAN class='scheduled' TITLE='Click link to view a list of scheduled jobs that are not shown on the current situation screen. Scheduled jobs that are due in the next 2 days are shown on the current situation screen and have a * in front of the ID, the date is highlighted and is the scheduled date.' onClick='do_sched_jobs(10);'><u><?php print $scheduled_link;?></u></SPAN>\n";	//	3/15/11
<?php
	}	//	11/29/10	
	
	if (($num_sched != 0) && ($func == 10)) {	//	11/29/10
?>
		sched_html +="\t\t<SPAN class='scheduled' TITLE='Click link view current situation screen including scheduled jobs that are due in the next 2 days. Scheduled jobs are shown with a * in front of the ID, the date is highlighted and is the scheduled date.' onClick='do_curr_jobs(0);'>Click to view current situation</SPAN>\n";	//	3/15/11
<?php
	}	//	11/29/10		
	
?>
	var incs_array = new Array();
	var incs_groups = new Array();		
	var gmarkers = [];
	var fmarkers = [];
	var rmarkers = [];		//	6/27/12
	var rowIds = [];		// 3/8/10
	var infoTabs = [];
	var theTabs = [];		//	6/27/12
	var facinfoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index

	var myMarker;					// the marker object
	var lat_var;						// see init.js
	var lng_var;
	var zoom_var;

	var icon_file = "./markers/crosshair.png";

	function call_back (in_obj){				// callback function - from polyline()
		do_lat(in_obj.lat);			// set form values
		do_lng(in_obj.lng);
		do_ngs();	
		}
//				2058

	var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
	switch(<?php echo get_variable('maptype');?>) {
			case (2): the_type= google.maps.MapTypeId.SATELLITE; 	break;
			case (3): the_type= google.maps.MapTypeId.TERRAIN; 		break;
			case (4): the_type= google.maps.MapTypeId.HYBRID; 		break;
			default:  the_type= google.maps.MapTypeId.ROADMAP;
			}		// end switch

	var mapOptions = {
		zoom: <?php print get_variable('def_zoom');?>,
		center: myLatlng,
		panControl: true,
	    zoomControl: true,
	    scaleControl: true,
	    mapTypeId: the_type
		}	
//	 <?php echo __LINE__ ;?>
				
	var map = new google.maps.Map($('map_canvas'), mapOptions);				// 
	var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map
	var listIcon = new google.maps.MarkerImage("./markers/yellow.png");
	listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new google.maps.Size(20, 34);
	listIcon.shadowSize = new google.maps.Size(37, 34);
	listIcon.iconAnchor = new google.maps.Point(8, 28);
	listIcon.infoWindowAnchor = new google.maps.Point(9, 2);
	listIcon.infoShadowAnchor = new google.maps.Point(18, 25);

	google.maps.event.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
//		alert(2114);
		var zoomfactor = -2;	//	3/15/11
		var newzoom = currzoom + zoomfactor;
		base_zoom = map.getZoom();
		if (currzoom > (base_zoom - zoomfactor)) {	//	3/15/11
			map.setCenter(curr_loc, newzoom);
		} else {
			map.setCenter(curr_loc, currzoom);
		}
		gmarkers[which].setMap(map);		
		});

	var got_points = false;	// map is empty of points

	do_landb();				// 8/1/11 - show scribbles
<?php

//-----------------------BOUNDARIES STUFF--------------------6/10/11

?>
	var thepoint;
	var points = new Array();


	
	google.maps.event.addListener(map, "click", function(overlay,boundpoint) {
		for (var n = 0; n < boundary.length; n++) {
			if (boundary[n].Contains(boundpoint)) {
				map.openInfoWindowHtml(boundpoint,"This is " + bound_names[n]);
				}
			}
		});	

<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	//	6/10/11
	$result = mysql_query($query);	//	6/10/11
	$a_gp_bounds = array();
	$a_all_boundaries = array();
	$gp_bounds = array();
	$all_boundaries = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	6/10/11
		$al_groups[] = $row['group'];
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	//	6/10/11
		$result2 = mysql_query($query2);	// 4/18/11
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	//	//	6/10/11	
			if($row2['boundary'] != 0) {
				$a_gp_bounds[] = $row2['boundary'];	
				$a_all_boundaries[] = $row2['boundary'];
				}
		}
	}

	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		foreach(explode(",",$_SESSION['viewed_groups']) as $val_vg) {
			$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$val_vg';";
			$result3 = mysql_query($query3);	//	6/10/11		
			while ($row3 = stripslashes_deep(mysql_fetch_assoc($result3))) 	{
					if($row3['boundary'] != 0) {
						$gp_bounds[] = $row3['boundary'];
						$all_boundaries[] = $row3['boundary'];
						}
				}
			}
		} else {
			$gp_bounds = $a_gp_bounds;
			$all_boundaries = $a_all_boundaries;
		}

	foreach($gp_bounds as $value) {		//	6/10/11
?>
		var points = new Array();
<?php	
		if($value !=0) {
			$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`='{$value}' LIMIT 1";
			$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
			$row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn));
			extract ($row_bn);
			$bn_name = $row_bn['line_name'];
			$points = explode (";", $line_data);

			$sep = "";
			echo "\n\t var points = [\n";
			for ($i = 0; $i<count($points); $i++) {
				$coords = explode (",", $points[$i]);
				echo	"{$sep}\n\t\tnew google.maps.LatLng({$coords[0]}, {$coords[1]})";
				$sep = ",";					
				}			// end for ($i = 0 ... )
			echo "];\n";
			if ((intval($filled) == 1) && (count($points) > 2)) {
?>
				  polyline = new google.maps.Polygon({
				    paths: 			 points,
				    strokeColor: 	 add_hash("<?php echo $line_color;?>"),
				    strokeOpacity: 	 <?php echo $line_opacity;?>,
				    strokeWeight: 	 <?php echo $line_width;?>,
				    fillColor: 		 add_hash("<?php echo $fill_color;?>"),
				    fillOpacity: 	 <?php echo $fill_opacity;?>
					});

<?php		} else {
?>
				  polyline = new google.maps.Polygon({
				    paths: 			points,
				    strokeColor: 	add_hash("<?php echo $line_color;?>"),
				    strokeOpacity: 	<?php echo $line_opacity;?>,
				    strokeWeight: 	<?php echo $line_width;?>,
				    fillColor: 		add_hash("<?php echo $fill_color;?>"),
				    fillOpacity: 	<?php echo $fill_opacity;?>
					});
<?php		} ?>				        
			polyline.setMap(map);		
			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
<?php
		}
	}

//-------------------------END OF BOUNDARIES STUFF-------------------------

	$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
																				//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($_SESSION['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $_SESSION['ticket_per_page']))	{
		if ($_GET['offset']) {
			$limit = "LIMIT $_GET[offset],$_SESSION[ticket_per_page]";
			}
		else {
			$limit = "LIMIT 0,$_SESSION[ticket_per_page]";
			}
		}
	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$time_back = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60) - ($cwi*3600));

if(isset($_SESSION['viewed_groups'])) {		//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}

if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
	$where2 = " AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";
	} else {		
	if(!isset($curr_viewed)) {			//	6/10/11
		$x=0;	
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;	
		$where2 = "AND (";	
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		}
	$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";	
	}
	switch($func) {		
		case 0: 
			$where = "WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_OPEN']}' OR (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `$GLOBALS[mysql_prefix]ticket`.`booked_date` <= (NOW() + INTERVAL 2 DAY)) OR 
				(`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_CLOSED']}'  AND `$GLOBALS[mysql_prefix]ticket`.`problemend` >= '{$time_back}')){$where2}";	//	11/29/10, 4/18/11, 4/18/11
			break;
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
			$the_start = get_start($func);		// mysql timestamp format 
			$the_end = get_end($func);
			$where = " WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_CLOSED']}' AND `$GLOBALS[mysql_prefix]ticket`.`problemend` BETWEEN '{$the_start}' AND '{$the_end}') {$where2} ";		//	4/18/11, 4/18/11
			break;				
		case 10:
			$where = "WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `$GLOBALS[mysql_prefix]ticket`.`booked_date` >= (NOW() + INTERVAL 2 DAY)) {$where2}";	//	11/29/10, 4/18/11, 4/18/11
			break;			
		default: print "error - error - error - error " . __LINE__;
		}				// end switch($func)
	$sort_by_severity = ($func == 0)? "`severity` DESC, ": "";
	
	if ($sort_by_field && $sort_value) {					//sort by field?
		$query = "SELECT *,problemstart AS problemstart,problemend AS problemend,
			`date` AS `date`,updated AS updated, in_types.type AS `type`, in_types.id AS `t_id` 
			FROM `$GLOBALS[mysql_prefix]allocates`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]allocates`.`resource_id`=`$GLOBALS[mysql_prefix]ticket`.`id` 			
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.`in_types_id`=`$GLOBALS[mysql_prefix]in_types`.`in_types.id` 
			WHERE $sort_by_field='$sort_value' $restrict_ticket AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 ORDER BY $order_by";
		}
	else {					// 2/2/09, 8/12/09, updated 4/18/11 to support regional operation
		$query = "SELECT *,problemstart AS problemstart,
			`problemend` AS `problemend`,
			`booked_date` AS `booked_date`,	
			`date` AS `date`, 
			`$GLOBALS[mysql_prefix]ticket`.`street` AS ticket_street, 
			`$GLOBALS[mysql_prefix]ticket`.`state` AS ticket_city, 
			`$GLOBALS[mysql_prefix]ticket`.`city` AS ticket_state,
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
			`$GLOBALS[mysql_prefix]in_types`.`type` AS `type`, 
			`$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, 
			`$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`, 
			`$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
			(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`  
				AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
				AS `units_assigned`			
			FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
				ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` 
				ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 
				ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id`
			$where $restrict_ticket 
			GROUP BY tick_id ORDER BY `status` DESC, {$sort_by_severity} `$GLOBALS[mysql_prefix]ticket`.`id` ASC
			LIMIT 1000 OFFSET {$my_offset}";		// 2/2/09, 10/28/09, 2/21/10
//			snap (__LINE__,  $query);
//			dump($func);

		}
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$sb_indx = 0;				// note zero base!	

	$acts_ary = $pats_ary = array();				// 6/2/10
	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]action` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$acts_ary[$row['ticket_id']] = $row['the_count'];
		}
	
	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]patient` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$pats_ary[$row['ticket_id']] = $row['the_count'];
		}	
	$temp  = (string) ( round((microtime(true) - $time), 3));
	$line_limit = 25;											// 5/5/11
	if (mysql_num_rows($result)> $line_limit) {
?>
	side_bar_html += get_chg_disp_tr();							// get 'Change display' select list row
<?php	
		}
?>	
	side_bar_html += "<TR class='spacer'><TD CLASS='spacer' COLSPAN=99>&nbsp;</TD></TR>";	//	3/15/11
	side_bar_html += "<TR class='even'><TH colspan=99 align='center'>Click/Mouse-over for information</TH></TR>";	//	3/15/11
	side_bar_html += "<TR class='odd'><TD></TD><TD align='left' COLSPAN=2><B><?php print $incident;?></B></TD><TD align='left'><B><?php print $nature;?></B></TD><TD align='left'><B>&nbsp;Addr</B></TD><TD align='left'><B>P</B></TD><TD align='left'><B>A</B></TD><TD align='left'><B>U</B></TD><TD align='left'><B>&nbsp;&nbsp;As of</B></TD></TR>";

<?php		
// ===========================  begin major while() for incidents ==========
	$temp  = (string) ( round((microtime(true) - $time), 3));
											
	$use_quick = (((integer)$func == 0) || ((integer)$func == 10)) ? FALSE : TRUE ;	//	11/29/10
	if ($use_quick) 					{$js_func = "open_tick_window";}			//	11/29/10
	elseif (($quick) && (!is_guest())) 	{$js_func = "myclick_ed_tick";}
	else 								{$js_func = "myclick";}

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{		// 7/7/10

//		dump($row);
		$tick_gps = get_allocates(1, $row['tick_id']);	//	6/10/11
//		dump($tick_gps);
		$grp_names = "Groups Assigned: ";	//	6/10/11
		$y=0;	//	6/10/11
		foreach($tick_gps as $value) {	//	6/10/11
			$counter = (count($tick_gps) > ($y+1)) ? ", " : "";
			$grp_names .= get_groupname($value);
			$grp_names .= $counter;
			$y++;
			}
		$onclick_str = "onClick = '{$js_func}({$row['tick_id']});'";		// onClick = to_wherever(999);  -6/23/11

/*
		dump(__LINE__);
		dump($onclick_str);
		dump($pat_onclick_str);
*/
		$by_severity[$row['severity']] ++;															// 5/2/10

		if ($func > 0) {				// closed? - 5/16/10
			$onclick =  " open_tick_window({$row['tick_id']})";				
			}
		else {
			$onclick =  ($quick)? " myclick_ed_tick({$row['tick_id']}) ": "myclick({$sb_indx})";		// 1/2/10
			}

		if ((($do_blink)) && ($row['units_assigned']==0) && ($row['status']==$GLOBALS['STATUS_OPEN'])) {					// 4/11/10
			$blinkst = "<blink>";
			$blinkend ="</blink>";
			}
		else {$blinkst = $blinkend = "";
			}		
//		$tip =  str_replace ( "'", "`", $grp_names . " / " . $row['contact'] . "/" .$row['ticket_street'] . "/" .$row['ticket_city'] . "/" .$row['ticket_state'] . "/" .$row['phone'] . "/" . $row['scope']);		// tooltip string - 1/3/10
		$tip =  addslashes ( "{$grp_names}/{$row['contact']}/{$row['ticket_street']}/{$row['ticket_city']}/{$row['ticket_state']}/{$row['phone']}/{$row['scope']}");		// tooltip string - 10/28/2012
		$sp = (($row['status'] == $GLOBALS['STATUS_SCHEDULED']) && ($func != 10)) ? "*" : "";		
	
		print "\t\tvar scheduled = '$sp';\n";
?>
		var sym = (<?php print addslashes($sb_indx); ?>+1).toString();						// for sidebar
		var sym2= scheduled + (<?php print addslashes($sb_indx); ?>+1).toString();						// for icon

<?php
		$the_id = $row['tick_id'];		// 11/27/09
	
		if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
		if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
			if (strlen($row['tick_descr']) > get_variable('abbreviate_description')) {
				$row['tick_descr'] = substr($row['tick_descr'],0,get_variable('abbreviate_description')).'...';
				}
			}
		if (get_variable('abbreviate_affected')) {
			if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
				$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
				}
			}
		switch($row['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 				$severityclass='severity_normal'; break;
			}

		$A = array_key_exists ($the_id , $acts_ary)? $acts_ary[$the_id]: 0;		// 6/2/10
		$P = array_key_exists ($the_id , $pats_ary)? $pats_ary[$the_id]: 0;

		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
		
		$address_street=replace_quotes($row['ticket_street']) . " " . replace_quotes($row['ticket_city']);
		
		$sidebar_line = "<TD ALIGN='left' CLASS='$severityclass' {$onclick_str} COLSPAN=2><NOBR>$strike" . $sp . replace_quotes(shorten($row['scope'],20)) . " $strikend</NOBR></TD>";	//10/27/09, 8/2/10
		$sidebar_line .= "<TD ALIGN='left' CLASS='$severityclass' {$onclick_str}><NOBR>$strike" . shorten($row['type'], 20) . " $strikend</NOBR></TD>";	// 8/2/10
		$sidebar_line .= "<TD ALIGN='left' CLASS='$severityclass' {$onclick_str}><NOBR>$strike" . replace_quotes(shorten(($row['ticket_street'] . ' ' . $row['ticket_city']), 20)) . " $strikend</NOBR></TD>";	// 8/2/10
		if ($P==0) {
			$sidebar_line .= "<TD></TD>";		
			}
		else {
			$pat_onclick_str = "onClick = 'do_patient({$row['tick_id']});'";
			$sidebar_line .= "<TD CLASS='disp_stat' {$pat_onclick_str}><NOBR><B>{$P}</B></TD>";		
			}

		$sidebar_line .= "<TD CLASS='td_data'> " . $A . " </NOBR></TD>";
		$sidebar_line .= "<TD CLASS='td_data'>{$blinkst}{$row['units_assigned']}{$blinkend}</TD>";
		$disp_date = ($row['status'] == $GLOBALS['STATUS_SCHEDULED']) ? format_sb_date_2($row['booked_date']) : format_sb_date_2($row['updated']);	// 01/06/11
		$date_hlite = ($row['status'] == $GLOBALS['STATUS_SCHEDULED']) ? " class='scheduled'" : "";

		$sidebar_line .= "<TD $date_hlite><NOBR>{$disp_date}</NOBR></TD>";	// 01/06/11	
	
		if (my_is_float($row['lat'])) {		// 6/21/10
			$street = empty($row['ticket_street'])? "" : replace_quotes($row['ticket_street']) . "<BR/>" . replace_quotes($row['ticket_city']) . " " . replace_quotes($row['ticket_state']) ;
			$todisp = (is_guest()|| is_unit())? "": "&nbsp;<A HREF='{$_SESSION['routesfile']}?ticket_id={$the_id}'><U>Dispatch</U></A>";	// 7/27/10
		
			$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
		
			$tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . replace_quotes(shorten($row['scope'], 48))  . "$strikend</B></TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>As of:</TD><TD ALIGN='left'>" . format_date_2(($row['updated'])) . "</TD></TR>";
			if (is_date($row['booked_date'])){
				$tab_1 .= "<TR CLASS='odd'><TD>Booked Date:</TD><TD ALIGN='left'>" . format_date_2($row['booked_date']) . "</TD></TR>";	//10/27/09, 3/15/11
				}
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>Reported by:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['contact'], 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Phone:</TD><TD ALIGN='left'>" . format_phone($row['phone']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>Addr:</TD><TD ALIGN='left'>$address_street</TD></TR>";
	
			$elapsed = get_elapsed_time ($row);
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Status:</TD><TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
			$tab_1 .= (empty($row['fac_name']))? "" : "<TR CLASS='even'><TD ALIGN='left'>Receiving Facility:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['fac_name'], 30))  . "</TD></TR>";	//3/27/10, 3/15/11
			$utm = get_variable('UTM');
			if ($utm==1) {
				$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
				$tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>UTM grid:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";
				}
			$tab_1 .= "<TR CLASS='even'>	<TD ALIGN='left'>Description:</TD><TD ALIGN='left'>" . replace_quotes(shorten(str_replace($eols, " ", $row['tick_descr']), 48)) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
			$tab_1 .= "<TR CLASS='odd'>		<TD ALIGN='left'>{$disposition}:</TD><TD ALIGN='left'>" . shorten(replace_quotes($row['comments']), 48) . "</TD></TR>";		// 8/13/09, 3/15/11
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><FONT SIZE='-1'>";

//			$tab_1 .= "<TR><TD COLSPAN=2 ALIGN='left'>" . show_assigns(0, $the_id) . "</TD></TR>";
//
//			$tab_1 .= "<TR CLASS='even'>	<TD ALIGN='left'>911 contact:</TD><TD ALIGN='left'>" . shorten($row['nine_one_one'], 48) . "</TD></TR>";	// 6/26/10
//		
//			$locale = get_variable('locale');	// 08/03/09
//			switch($locale) { 
//				case "0":
//				$tab_1 .= "<TR CLASS='odd'>	<TD ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
//				break;
//			
//				case "1":
//				$tab_1 .= "<TR CLASS='odd'>	<TD ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
//				break;
//			
//				case "2":
//				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
//				$tab_1 .= "<TR CLASS='odd'>	<TD ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
//				break;
//			
//				default:
//				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
//				}
//		
//
//
			$tab_1 .= 	$todisp . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='main.php?id={$the_id}'><U>Details</U></A>";		// 08/8/02
			if (!(is_guest() )) {
				if (can_edit()) {							//8/27/10
					$tab_1 .= 	"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['editfile']}?id={$the_id}{$rand}'><U>Edit</U></A>";	
					}
				if ((!(is_closed($the_id))) && (!is_unit()))  {		// 3/3/11
					$tab_1 .= "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick = do_close_tick('{$the_id}');><U>" . get_text("Close incident") . " </U></A><BR /><BR /> ";  // 3/3/11
					}
				$tab_1 .= 	"&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_popup('{$the_id}');><FONT COLOR='blue'><B><U>Popup</B></U></FONT></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 7/7/09
				$tab_1 .= 	"<SPAN onClick = 'do_add_note ({$the_id});'><FONT COLOR='blue'><B><U>Add note</B></U></FONT></SPAN><BR /><BR />" ;	// 7/7/09
				if (can_edit()) {							//8/27/10
					$tab_1 .= 	"<A HREF='patient.php?ticket_id={$the_id}{$rand}'><U>Add {$patient}</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";	// 7/9/09
					$tab_1 .= 	"<A HREF='action.php?ticket_id={$the_id}{$rand}'><U>Add Action</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
					$tab_1 .=   "<A HREF='#' onClick = 'do_mail_all_win({$the_id});'><U>Contact Units</U></A>";					
					}
				}
			$tab_1 .= 	"</FONT></TD></TR></TABLE>";			// 11/6/08
		
			$tab_2 = "<DIV style='max-height: 200px; overflow: auto;'><TABLE CLASS='infowin'  width='{$iw_width}' >";	// 8/12/09
			$tab_2 .= "<TR CLASS='even'>	<TD ALIGN='left'>Description:</TD><TD ALIGN='left'>" . replace_quotes(shorten(str_replace($eols, " ", $row['tick_descr']), 48)) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
			$tab_2 .= "<TR CLASS='odd'>		<TD ALIGN='left'>{$disposition}:</TD><TD ALIGN='left'>" . shorten(replace_quotes($row['comments']), 48) . "</TD></TR>";		// 8/13/09, 3/15/11
			$tab_2 .= "<TR CLASS='even'>	<TD ALIGN='left'>911 contact:</TD><TD ALIGN='left'>" . shorten($row['nine_one_one'], 48) . "</TD></TR>";	// 6/26/10
		
			$locale = get_variable('locale');	// 08/03/09
			switch($locale) { 
				case "0":
				$tab_2 .= "<TR CLASS='odd'>	<TD ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "1":
				$tab_2 .= "<TR CLASS='odd'>	<TD ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "2":
				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
				$tab_2 .= "<TR CLASS='odd'>	<TD ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				default:
				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
				}
		
			$tab_2 .= "<TR><TD COLSPAN=2 ALIGN='left'>" . show_assigns(0, $the_id) . "</TD></TR>";
			$tab_2 .= 	"</TABLE></DIV>";		// 11/6/08. 3/15/11
?>
/*
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("More ..", "<?php print str_replace($eols, " ", $tab_2);?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
*/		
			var myinfoTabs = "<?php echo nl2brr($tab_1);?>";
<?php

			if ((($row['lat'] == $GLOBALS['NM_LAT_VAL']) && ($row['lng'] == $GLOBALS['NM_LAT_VAL'])) || (($row['lat'] == "") || ($row['lat'] == NULL)) || (($row['lng'] == "") || ($row['lng'] == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
?>			
				var point = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);	// for each ticket
				bounds.extend(point);
	
				var myinfoTabs = "<?php echo nl2brr($tab_1);?>";
				var dummymarker = createdummyMarker(point, myinfoTabs, <?php print $sb_indx; ?>);	// (point,tabs, id) - plots dummy icon in default position for tickets added in no maps operation 7/28/10
				dummymarker.setMap(map);		

				var the_class = ((map_is_fixed) && (!(bounds.contains(point))))? "emph" : "td_label";	
<?php
				} else {
?>
			var point = new google.maps.LatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket
			bounds.extend(point);
			var category = "Incident";
			var region = "<?php print get_first_group(1, $row['tick_id']);?>";
			var tip_str = "<?php print $tip;?>";  				// 3/19/11
			var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, 0, <?php print $sb_indx; ?>, sym2, category, region, tip_str);		// 3/19/11
			
										// (point,tabs, color, id, sym) - 1/6/09, 10/21/09 added 0 for stat display to avoid conflicts with unit marker hide by unavailable status
			marker.setMap(map);		
			var the_class = ((map_is_fixed) && (!(bounds.contains(point))))? "emph" : "td_label";
			incs_array[i] = <?php print $row['tick_id'];?>;
<?php
			$allocated_groups = get_allocates(1, $row['tick_id']);	//	4/18/11
			echo "var groups = [];\n";
			foreach($allocated_groups as $key => $value) {
				echo "groups[$key] = $value;\n";
			}
?>
			incs_groups[i] = groups;	//	4/18/11	
			i++;	//	4/18/11	
<?php
				}		// end of check for no maps markes
			}		// end if (my_is_float($row['lat']))
		$use_quick = (((integer)$func == 0) || ((integer)$func == 10)) ? FALSE : TRUE ;	//	11/29/10
			
		if (($quick) || ($use_quick)) {		// 5/18/10, 11/29/10
			print "\t\t	do_sidebar_t_ed (\"{$sidebar_line}\", ({$the_offset} + {$sb_indx}), {$row['tick_id']}, sym, \"{$tip}\");\n";
			}
		else {
			print "\t\t do_sidebar (\"{$sidebar_line}\", {$sb_indx}, ({$the_offset} + {$sb_indx}+1), the_class, \"{$tip}\");\n";
			}
		if (intval($row['radius']) > 0) {
			$color= (substr($row['color'], 0, 1)=="#")? $row['color']: "#000000";		// black default
?>	
//			drawCircle(				38.479874, 				-78.246704, 						50.0, 					"#000080",						 1, 		0.75,	 "#0000FF", 					.2);
			drawCircle(	<?php print $row['lat']?>, <?php print $row['lng']?>, <?php print $row['radius']?>, "<?php print $color?>", 1, 	0.75, "<?php print $color?>", .<?php print $row['opacity']?>);
<?php
			}				// end if (intval($row['radius']) 

			$sb_indx++;
			}				// end tickets while ($row = ...)
//		$temp  = (string) ( round((microtime(true) - $time), 3));
//		snap (__LINE__, $temp );

		$query_sched = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status`='{$GLOBALS['STATUS_SCHEDULED']}'";	//	11/29/10
		$result_sched = mysql_query($query_sched) or do_error($query_sched, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	11/29/10
		$num_sched = mysql_num_rows($result_sched);	//	11/29/10

		if (mysql_num_rows($result)<= $line_limit) {
?>	
	side_bar_html += get_chg_disp_tr();
<?php
			}				// end 	if (mysql_num_rows($result)<= $line_limit)		
?>		
						// 3/30/2013
//		side_bar_html +="\t\t<SPAN ID = 'btn_go' onClick='document.to_listtype.submit()' CLASS='conf_button' STYLE = 'margin-left: 10px; display:none'><U>Next</U></SPAN>";
//		side_bar_html +="\t\t<SPAN ID = 'btn_can'  onClick='hide_btns_closed(); hide_btns_scheduled(); ' CLASS='conf_button' STYLE = 'margin-left: 10px; display:none'><U>Cancel</U></SPAN>";
//		side_bar_html +="<br /><br /></TD></TR>\n";

<?php
		
		if ($sb_indx == 0) {
			$txt_str = ($func>0)? "closed tickets this period!": "current tickets!";
			print "\n\t\tside_bar_html += \"<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><I><B>No {$txt_str}</B></I></TD></TR>\";";
			print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD COLSPAN='99' ><BR /><BR /></TD></TR>\";";
			}
		$limit = 1000;
		$link_str = "";
		$query= "SELECT `id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = '{$GLOBALS['STATUS_CLOSED']}'";
		$result_cl = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_affected_rows() > $limit) {
			$sep = ", ";
			$rcds = mysql_affected_rows();
			for ($j=0; $j < (ceil($rcds / $limit)); $j++) {
				$sep = ($j==ceil($rcds / $limit)-1) ? "" : ", ";
				$temp = (string)($j * $limit);
				$link_str .= "<SPAN onClick = 'document.to_closed.frm_offset.value={$temp}; document.to_closed.submit();'><U>" . ($j+1) . "K</U></SPAN>{$sep}";
				}				
			}
		$sev_string = "" . get_text("Severities") . ": <SPAN CLASS='severity_normal'>" . get_text("Normal") . " ({$by_severity[$GLOBALS['SEVERITY_NORMAL']]})</SPAN>,&nbsp;&nbsp;<SPAN CLASS='severity_medium'>" . get_text("Medium") . " ({$by_severity[$GLOBALS['SEVERITY_MEDIUM']]})</SPAN>,&nbsp;&nbsp;<SPAN CLASS='severity_high'>" . get_text("High") . " ({$by_severity[$GLOBALS['SEVERITY_HIGH']]})</SPAN>";

		unset($acts_ary, $pats_ary, $result_temp, $result_cl);
//		snap(__LINE__, round((microtime(true) - $time), 3));

?>		
	side_bar_html +="<TR class='spacer'><TD class='spacer' COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>\n";	//	4/18/11
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML = side_bar_html;				// side_bar_html to incidents div 
	$("sched_flag").innerHTML = sched_html;				// side_bar_html to incidents div	
	$('sev_counts').innerHTML = "<?php print $sev_string; ?>";			// 5/2/10

//	for (var n = 0; n < incs_array.length; n++) {	//	4/18/11	
//		alert("Incident ID " + incs_array[n] + " Incident Groups " + incs_groups[n]);	//	4/18/11	
//			}	//	4/18/11	

// ==========================================      RESPONDER start    ================================================

	side_bar_html ="<TABLE border=0 CLASS='sidebar' WIDTH = <?php print $col_width;?> >\n";		// initialize units sidebar string
	side_bar_html += "<TR CLASS = 'spacer'><TD CLASS='spacer' COLSPAN=99>&nbsp;</TD></TR>";	//	3/15/11
	i++;
	var j=0;

<?php

	$u_types = array();												// 1/1/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
		}
	unset($result);

//	Categories for Unit status
	
	$categories = array();													// 12/03/10
	$categories = $curr_cats;											// 12/03/10
	$assigns = array();					// 8/3/08
	$tickets = array();					// ticket id's

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, 
		`$GLOBALS[mysql_prefix]assigns`.`responder_id`, 
		`$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` 
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";
	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		$status_hide[$temp] = $row_st['hide'];
		}

	unset($result_st);

	$assigns_ary = array();				// construct array of responder_id's on active calls
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$assigns_ary[$row['responder_id']] = TRUE;
		}
	$order_values = array(1 => "`nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC", 2 => "`type_descr` ASC, `handle` ASC",  3 => "`stat_descr` ASC, `handle` ASC" , 4 => "`handle` ASC");	// 6/24/10

	if ((array_key_exists ('order' , $_POST)) && (isset($_POST['order'])))	{$_SESSION['unit_flag_2'] =  $_POST['order'];}		// 8/12/10
	elseif (empty ($_SESSION['unit_flag_2'])) 	{$_SESSION['unit_flag_2'] = 1;}

	$order_str = $order_values[$_SESSION['unit_flag_2']];		// 6/11/10
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 4/18/11
	$result = mysql_query($query);	// 4/18/11
	$al_groups = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/18/11
		$al_groups[] = $row['group'];
		}	
	if(isset($_SESSION['viewed_groups'])) {
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}
	if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
		$where2 = "WHERE `a`.`type` = 2";
		} else {	
		if(!isset($curr_viewed)) {	
			$x=0;	//	4/18/11
			$where2 = "WHERE (";	//	4/18/11
			foreach($al_groups as $grp) {	//	4/18/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			} else {
			$x=0;	//	4/18/11
			$where2 = "WHERE (";	//	4/18/11
			foreach($curr_viewed as $grp) {	//	4/18/11
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			}
		$where2 .= "AND `a`.`type` = 2";
		}
	
//-----------------------UNIT RING FENCE STUFF--------------------6/10/11
?>
	var thepoint;
	var points = new Array();
		
<?php	
	$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `l`.`id` = `r`.`ring_fence`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
				{$where2} AND `use_with_u_rf`=1 GROUP BY `l`.`id`";
	$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
	while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
		extract ($row_bn);
		$bn_name = $row_bn['line_name'];
		$all_boundaries[] = $row_bn['ring_fence'];		
		$points = explode (";", $line_data);

		$sep = "";
		echo "\n\t var points = [\n";
		for ($i = 0; $i<count($points); $i++) {
			$coords = explode (",", $points[$i]);
			echo	"{$sep}\n\t\tnew google.maps.LatLng({$coords[0]}, {$coords[1]})";
			$sep = ",";					
			}			// end for ($i = 0 ... )
		echo "];\n";

		if ((intval($filled) == 1) && (count($points) > 2)) {
?>
			  polyline = new google.maps.Polygon({
			    paths: 			 points,
			    strokeColor: 	 add_hash("<?php echo $line_color;?>"),
			    strokeOpacity: 	 <?php echo $line_opacity;?>,
			    strokeWeight: 	 <?php echo $line_width;?>,
			    fillColor: 		 add_hash("<?php echo $fill_color;?>"),
			    fillOpacity: 	 <?php echo $fill_opacity;?>
				});

<?php	} else {
?>
			  polyline = new google.maps.Polygon({
			    paths: 			points,
			    strokeColor: 	add_hash("<?php echo $line_color;?>"),
			    strokeOpacity: 	<?php echo $line_opacity;?>,
			    strokeWeight: 	<?php echo $line_width;?>,
			    fillColor: 		add_hash("<?php echo $fill_color;?>"),
			    fillOpacity: 	<?php echo $fill_opacity;?>
				});
<?php	} ?>				        

			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
			polyline.setMap(map);		
<?php
		}	//	End while
//-------------------------END OF UNIT RING FENCE STUFF-------------------------		

//-----------------------UNIT EXCLUSION ZONE STUFF--------------------6/10/11
?>
	var thepoint;
	var points = new Array();
		
<?php	
	$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `l`.`id` = `r`.`excl_zone`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
				{$where2} AND `use_with_u_ex`=1 GROUP BY `l`.`id`";
	$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
	while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
		extract ($row_bn);
		$bn_name = $row_bn['line_name'];
		$all_boundaries[] = $row_bn['excl_zone'];		
		$points = explode (";", $line_data);

		$sep = "";
		echo "\n\t var points = [\n";
		for ($i = 0; $i<count($points); $i++) {
			$coords = explode (",", $points[$i]);
			echo	"{$sep}\n\t\tnew google.maps.LatLng({$coords[0]}, {$coords[1]})";
			$sep = ",";					
			}			// end for ($i = 0 ... )
		echo "];\n";
		if ((intval($filled) == 1) && (count($points) > 2)) {
?>
			  polyline = new google.maps.Polygon({
			    paths: 			 points,
			    strokeColor: 	 add_hash("<?php echo $line_color;?>"),
			    strokeOpacity: 	 <?php echo $line_opacity;?>,
			    strokeWeight: 	 <?php echo $line_width;?>,
			    fillColor: 		 add_hash("<?php echo $fill_color;?>"),
			    fillOpacity: 	 <?php echo $fill_opacity;?>
				});

<?php	} else {
?>
			  polyline = new google.maps.Polygon({
			    paths: 			points,
			    strokeColor: 	add_hash("<?php echo $line_color;?>"),
			    strokeOpacity: 	<?php echo $line_opacity;?>,
			    strokeWeight: 	<?php echo $line_width;?>,
			    fillColor: 		add_hash("<?php echo $fill_color;?>"),
			    fillOpacity: 	<?php echo $fill_opacity;?>
				});
<?php	} ?>				        

			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
			polyline.setMap(map);		
<?php
		}	//	End while
//-------------------------END OF UNIT EXCLUSION ZONE STUFF-------------------------	

	$query = "SELECT *, `updated` AS `updated`, 
		`t`.`id` AS `type_id`, 
		`r`.`id` AS `unit_id`, 
		`r`.`name` AS `name`,
		`r`.`ring_fence` AS `ring_fence`,		
		`s`.`description` AS `stat_descr`,  
		`r`.`description` AS `unit_descr`, 
		`t`.`description` AS `type_descr`,
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id 	AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )) AS `nr_assigned` 
		FROM `$GLOBALS[mysql_prefix]responder` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )		
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
		{$where2}  GROUP BY unit_id ORDER BY {$order_str}";											// 2/1/10, 3/8/10, 4/18/11, 6/11/10

//	dump($query);
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$units_ct = mysql_affected_rows();			// 1/4/10
	if ($units_ct==0){
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TH></TH><TH ALIGN='center' COLSPAN=99><I><B>No units!</I></B></TH></TR>\"\n";
		}
	else {
		$checked = array ("", "", "", "");
		$checked[$_SESSION['unit_flag_2']] = " CHECKED";
?>
	
	side_bar_html += "<TR CLASS = 'even'><TD COLSPAN=99 ALIGN='center'>";
	side_bar_html += "<I><B>Sort</B>:&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "<?php print get_text("Units");?> &raquo; 	<input type = radio name = 'frm_order' value = 1 <?php print $checked[1];?> onClick = 'do_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Type &raquo; 	<input type = radio name = 'frm_order' value = 2 <?php print $checked[2];?> onClick = 'do_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Status &raquo; <input type = radio name = 'frm_order' value = 3 <?php print $checked[3];?> onClick = 'do_sort_sub(this.value);' />";
	side_bar_html += "</I></TD></TR>";
<?php	
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD></TD><TD><B>" . get_text("Units") . "</B> ({$units_ct}) </TD>	<TD onClick = 'do_mail_win(0); ' ALIGN = 'center'><IMG SRC='mail_red.png' /></TD><TD ALIGN='left' COLSPAN='2'><B>{$incident}</B></TD><TD>&nbsp; <B>Status</B></TD><TD><B>M</B></TD><TD><B>&nbsp;As of</B></TD></TR>\"\n" ;	// 12/17/10
//		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD></TD><TD><B>Unit</B> ({$units_ct}) </TD>	<TD onClick = 'do_mail_win(0); ' ALIGN = 'center'><IMG SRC='mail_red.png' /></TD><TD>&nbsp; <B>Status</B></TD><TD ALIGN='left' COLSPAN='2'><B>{$incident}</B></TD><TD><B>M</B></TD><TD><B>&nbsp;As of</B></TD></TR>\"\n" ;	// 12/17/10
//		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD></TD><TD><B>Unit</B> ({$units_ct}) </TD>	<TD onClick = 'do_mail_win(null, null); ' ALIGN = 'center'><IMG SRC='mail_red.png' /></TD><TD>&nbsp; <B>Status</B></TD><TD ALIGN='left' COLSPAN='2'><B>{$incident}</B></TD><TD><B>M</B></TD><TD><B>&nbsp;As of</B></TD></TR>\"\n" ;
		}

	$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = FALSE;		//7/23/09

	$utc = gmdate ("U");				// 3/25/09

// ===========================  begin major while() for RESPONDER ==========

	$chgd_unit = $_SESSION['unit_flag_1'];					// possibly 0 - 4/8/10
	$_SESSION['unit_flag_1'] = 0;							// one-time only - 4/11/10
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 7/7/10
//		dump($row);
		$resp_gps = get_allocates(2, $row['unit_id']);	//	6/10/11
		$grp_names = "Groups Assigned: ";	//	6/10/11
		$y=0;	//	6/10/11
		foreach($resp_gps as $value) {	//	6/10/11
			$counter = (count($resp_gps) > ($y+1)) ? ", " : "";
			$grp_names .= get_groupname($value);
			$grp_names .= $counter;
			$y++;
			}

		$tip =  addslashes($grp_names . " / " . htmlentities($row['name'],ENT_QUOTES));		// tooltip string - 1/3/10
			
		$latitude = $row['lat'];		// 7/18/10		
		$longitude = $row['lng'];		// 7/18/10

		$on_click =  ((!(my_is_float($row['lat']))) || ($quick))? " myclick_nm({$row['unit_id']}) ": "myclick({$sb_indx})";		// 1/2/10
		$got_point = FALSE;

		$name = $row['name'];			//	10/8/09
		$index = $row['icon_str'];	// 4/27/11
		
		print "\t\tvar sym = \"$index\";\n";				// for sidebar and icon 10/8/09	- 4/22/11
												// 2/13/09
		$todisp = ((is_guest()) || (is_unit()))? "": "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&disp=true&id=" . $row['unit_id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;";		// 08/8/02
		$toedit = ((is_guest()) || (is_user()) || (is_unit()) )? "" :"&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&edit=true&id=" . $row['unit_id'] . "'><U>Edit</U></A>&nbsp;&nbsp;" ;	// 7/27/10
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U>&nbsp;&nbsp;</SPAN>" ;
		$tofac = (is_guest())? "": "<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&dispfac=true&id=" . $row['unit_id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 08/8/02

		$hide_unit = ($row['hide']=="y")? "1" : "0" ;		// 3/8/10
		$track_type = get_remote_type($row) ;				// 7/8/11
//		print "Track Type = " . $track_type;
		$row_track = FALSE;
		if ($track_type > 0 ) {				// get most recent mobile track data
			$do_legend = TRUE;
			$query = "SELECT *,packet_date AS `packet_date`, updated AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_track = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$aprs_updated = $row_track['updated'];
			$aprs_speed = $row_track['speed'];
			if (($row_track) && (my_is_float($row_track['latitude']))) {
				$latitude = $row_track['latitude'];  $longitude = $row_track['longitude'];			// 7/7/10
				echo "\t\tvar point = new google.maps.LatLng(" . $row_track['latitude'] . ", " . $row_track['longitude'] ."); // 677\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}

		if (my_is_float($row['lat'])) {
			echo "\t\tvar point = new google.maps.LatLng(" . $row['lat'] . ", " . $row['lng'] .");	// 753\n";
			$got_point= TRUE;
			}

		$the_bull = "";											// define the bullet
		$update_error = strtotime('now - 6 hours');				// set the time for silent setting
// NAME
		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 2/1/10
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
		$arrow = ($chgd_unit == $row['unit_id'])? "<IMG SRC='rtarrow.gif' />" : "" ; 	// 4/8/10
		$sidebar_line = "<TD ALIGN='left' onClick = '{$on_click}' TITLE = '" . htmlentities($row['name'],ENT_QUOTES) . "' >{$arrow}<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>" . htmlentities($row['handle'],ENT_QUOTES)  . "</B></U></SPAN></TD>";

// MAIL						
		if ((!is_guest()) && is_email($row['contact_via'])) {		// 2/1/10
			$mail_link = "\t<TD CLASS='mylink' ALIGN='center'>"
				. "&nbsp;<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit {$row['handle']}'"
				. " onclick = 'do_mail_win(\\\"{$row['unit_id']}\\\");'> "
				. "&nbsp;</TD>";		// 4/26/09, 12/17/10
//				dump(__LINE__);
//				dump($mail_link);
				}
		else {
			$mail_link = "\t<TD ALIGN='center'>na</TD>";
			}
		$sidebar_line .= $mail_link;

// DISPATCHES 3/16/09

		$units_assigned = 0;
		if(array_key_exists ($row['unit_id'] , $assigns_ary)) {			// this unit assigned? - 6/4/10
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
				WHERE `responder_id` = '{$row['unit_id']}' AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";
	
			$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$units_assigned = mysql_num_rows($result_as);
			}		// end if(array_key_exists ()

		switch ($units_assigned) {		
			case 0:
				$ass_td = "<TD COLSPAN='2' onClick = '{$on_click}' > na </TD>";
				break;			
			case 1:
				$row_assign = stripslashes_deep(mysql_fetch_assoc($result_as));
				$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
				$tip = htmlentities ("{$row_assign['contact']}/{$row_assign['street']}/{$row_assign['city']}/{$row_assign['phone']}/{$row_assign['scope']}", ENT_QUOTES );
				switch($row_assign['severity'])		{		//color tickets by severity
				 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
					default: 							$severityclass='severity_normal'; break;
					}		// end switch()
				$ass_td = "<TD ALIGN='left' onMouseover=\\\"Tip('{$tip}')\\\" onmouseout=\\\"UnTip()\\\" onClick = '{$on_click}' COLSPAN=2 CLASS='$severityclass' >{$the_disp_stat}" . shorten($row_assign['scope'], 20) . "</TD>";
				break;
			default:							// multiples
			    $ass_td = "<TD COLSPAN=2 onClick = '{$on_click}' CLASS='disp_stat'>&nbsp;{$units_assigned}&nbsp;</TD>&nbsp;";
			    break;
			}						// end switch(($units_assigned))

		$sidebar_line .= $ass_td;

// STATUS
		$sidebar_line .= "<TD>" . get_status_sel($row['unit_id'], $row['un_status_id'], "u") . "</TD>";		// status

//  MOBILITY
	if 	($row_track){
		if ($row_track['speed']>=50) {$the_bull = "<FONT COLOR = 'blue'><B>{$GLOBALS['TRACK_2L'][$track_type]}</B></FONT>";} 
		if ($row_track['speed']<50) {$the_bull = "<FONT COLOR = 'green'><B>{$GLOBALS['TRACK_2L'][$track_type]}</B></FONT>";}
		if ($row_track['speed']==0) {$the_bull = "<FONT COLOR = 'red'><B>{$GLOBALS['TRACK_2L'][$track_type]}</B></FONT>";}
		} 
	else {
		$the_bull = "<FONT COLOR = 'black'><STRIKE><B>{$GLOBALS['TRACK_2L'][$track_type]}</B></STRIKE></FONT>";	// no data - 5/31/2013
		}

	$cstip = htmlentities($row['callsign'], ENT_QUOTES); 
	$tip_str = "onMouseover=\\\"Tip('{$cstip}')\\\" onmouseout=\\\"UnTip();\\\" "; 
	$sidebar_line .= "<TD {$tip_str} onClick = '{$on_click}'>{$the_bull}</TD>";

// as of
		$the_time = $row['updated'];
		$the_time_test = strtotime($row['updated']);
		$the_class = "";
		$strike = $strike_end = "";
		$the_flag = $name . "_flag";
		if (($track_type > 0) && ((abs($utc - $the_time_test)) > $GLOBALS['TOLERANCE'])) {			// attempt to identify  non-current values
			$strike = "<STRIKE>"; $strike_end = "</STRIKE>";
			}

		$sidebar_line .= "<TD CLASS='$the_class'> {$strike} <SPAN id = '" . $name . "'>" . format_sb_date_2($the_time) . "</SPAN>{$strike_end}&nbsp;&nbsp;<SPAN ID = '" . $the_flag . "'></SPAN></TD>";	// 6/17/08

		if (my_is_float($row['lat'])) {						// 5/4/09

// tab 1
			$temptype = $u_types[$row['type_id']];
			$the_type = $temptype[0];																	// 1/1/09

			$tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>{$row['handle']}</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Description:</TD><TD ALIGN='left'>" . shorten(str_replace($eols, " ", $row['unit_descr']), 32) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>{$gt_status}:</TD><TD ALIGN='left'> {$row['stat_descr']}</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Contact:</TD><TD ALIGN='left'>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>As of:</TD><TD ALIGN='left'>" . format_date_2($row['updated']) . "</TD></TR>";

			if (array_key_exists($row['unit_id'], $assigns_ary)) {
				$tab_1 .= "<TR CLASS='even'><TD ALIGN='left' CLASS='emph'>Dispatched to:</TD><TD ALIGN='left' CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['unit_id']] . "'>" . shorten($assigns[$row['unit_id']], 20) . "</A></TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $tofac . $todisp . $totrack . $toedit . "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&id=" . $row['unit_id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE>";

// tab 2
			if ($row_track) {		// three tabs if track data
				$tab_2 = "<DIV style='max-height: 200px; overflow: auto;'><TABLE CLASS='infowin'  width='{$iw_width}' >";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_track['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD ALIGN='left'>Course: </TD><TD ALIGN='left'>" . $row_track['course'] . ", Speed:  " . $row_track['speed'] . ", Alt: " . $row_track['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD ALIGN='left'>Closest city: </TD><TD ALIGN='left'>" . $row_track['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD ALIGN='left'>{$gt_status}: </TD><TD ALIGN='left'>" . $row_track['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD ALIGN='left'>As of: </TD><TD ALIGN='left'> $strike " . format_date_2(strtotime($row_track['packet_date'])) . " $strike_end (UTC)</TD></TR></TABLE></DIV";
?>
/*
			var myinfoTabs = [
				new GInfoWindowTab("<?php print $row['handle'];?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("<?php print $GLOBALS['TRACK_2L'][$track_type];?> <?php print addslashes(substr($row_track['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
*/
<?php
			}	// end if ($row_track)

		else {	// else two tabs
?>
/*
			var myinfoTabs = [
				new GInfoWindowTab("<?php print $row['handle'];?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
*/
<?php
			}		// end if(!($tabs_done))
		
		}		// end position data available
		
		$tip = isset($tip) ? $tip : "No Tip String";

	if ((!(my_is_float($row['lat']))) || ($quick)) {		// 11/27/09
		$resp_cat = $un_stat_cats[$row['unit_id']];
		print "\t\tdo_sidebar_u_ed (\"{$sidebar_line}\",  {$sb_indx}, '{$on_click}', sym, \"{$tip}\", \"{$resp_cat}\");\n";		// (sidebar, line_no, on_click, letter)
		}
	else {
?>
		var the_class = ((map_is_fixed) && (!(bounds.contains(point))))? "emph" : "td_label";
		do_sidebar_unit ("<?php print $sidebar_line; ?>",  <?php print $sb_indx; ?>, sym, the_class, "<?php print $tip;?>", "<?php print $un_stat_cats[$row['unit_id']];?>");		// (instr, id, sym, myclass, tip)  - 1/3/10
<?php
		}

	if (my_is_float($latitude)) {		// map position?
		$the_color = ($row['mobile']=="1")? 0 : 4;		// icon color black, white		-- 4/18/09
		$the_group = $un_stat_cats[$row['unit_id']];	//	3/15/11
		if ((($latitude == $GLOBALS['NM_LAT_VAL']) && ($longitude == $GLOBALS['NM_LAT_VAL'])) || (($latitude == "") || ($latitude == NULL)) || (($longitude == "") || ($longitude == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
			$dummylat = get_variable('def_lat');
			$dummylng = get_variable('def_lng');			
			echo "\t\tvar point = new google.maps.LatLng(" . $dummylat . ", " . $dummylng ."); // 677\n";
?>
			var myinfoTabs = "<?php echo nl2brr($tab_1);?>";		// V3
			var dummymarker = createdummyMarker(point, myinfoTabs, <?php print $sb_indx; ?>);	// 859  - 7/28/10. Plots dummy icon in default position for units added in no maps operation
			dummymarker.setMap(map);		
<?php
		} else {
?>
			var the_group = '<?php print $the_group;?>';	//	3086 - 3/15/11
			var tip_str = "";
			var region = "<?php print get_first_group(2, $row['unit_id']);?>";			

			var myinfoTabs = "<?php echo nl2brr($tab_1);?>";		// V3
			var marker = createMarker(point, myinfoTabs, <?php print $the_color;?>, <?php print $hide_unit;?>,  <?php print $sb_indx; ?>, sym, the_group, region, tip_str); // 7/28/10, 3/15/11
			marker.setMap(map);		
<?php
			} // end check for no maps added points
		}				// end if (my_is_float())
?>
		var rowId = <?php print $sb_indx; ?>;			// row index for row hide/show - 3/2/10
		rowIds.push(rowId);													// form is "tr_id_??" where ?? is the row no.
<?php
	$sb_indx++;				// zero-based
	}				// end  ==========  while() for RESPONDER ==========
//	$temp  = (string) ( round((microtime(true) - $time), 3));
	$source_legend = (isset($do_legend))? "<TD CLASS='emph' ALIGN='left'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09
	print "\n\tside_bar_html+= \"<TR CLASS='\" + colors[i%2] +\"'><TD COLSPAN='7' ALIGN='right'>{$source_legend}</TD></TR>\";\n";
?>
	var legends = "<TR class='even'><TD ALIGN='center' COLSPAN='99'><TABLE ALIGN='center' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.4));?> >";	//	3/15/11
	legends += "<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR><TR class='even'><TD ALIGN='center' COLSPAN='99'><B><?php print get_text("Units");?> Legend</B></TD></TR>";	//	3/15/11
	legends += "<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'>&nbsp;&nbsp;<B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'>&bull;</FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'>&bull;</FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'>&bull;</FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'>&bull;</FONT>&nbsp;&nbsp;</TD></TR>";	//	3/15/11
	legends += "<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><?php print get_units_legend();?></TD></TR></TABLE>";	//	3/15/11

	$("side_bar_r").innerHTML = side_bar_html;		//	12/03/10
	$("side_bar_rl").innerHTML = legends + "</TABLE>";		//	12/03/10
	side_bar_html= "";		//	12/03/10
	side_bar_html+="<TABLE><TR class='heading_2'><TH width = <?php print $ctrls_width;?> ALIGN='center'><?php print get_text("Units");?></TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label' ><form action='#'>";			//	12/03/10, 3/15/11
<?php
if($units_ct > 0) {	//	3/15/11
	foreach($categories as $key => $value) {		//	12/03/10
?>
		side_bar_html += "<DIV class='cat_button' onClick='set_chkbox(\"<?php print $value;?>\")'><?php print $value;?>: <input type=checkbox id='<?php print $value;?>' onClick='set_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!-- 12/03/10 -->
<?php
		}
		$all="ALL";		//	12/03/10
		$none="NONE";				//	12/03/10
?>
		side_bar_html += "<DIV ID = 'ALL_BUTTON' class='cat_button' onClick='set_chkbox(\"<?php print $all;?>\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='<?php print $all;?>' onClick='set_chkbox(\"<?php print $all;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'NONE_BUTTON' class='cat_button'  onClick='set_chkbox(\"<?php print $none;?>\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='<?php print $none;?>' onClick='set_chkbox(\"<?php print $none;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'go_can' style='float:right; padding:2px;'><SPAN ID = 'go_button' onClick='do_go_button()' class='conf_next_button' STYLE = 'display:none;'><U>Next</U></SPAN>";
		side_bar_html += "<SPAN ID = 'can_button'  onClick='cancel_buttons()' class='conf_can_button' STYLE = 'display:none;'><U>Cancel</U></SPAN></DIV>";
		side_bar_html+="</form></TD></TR></TABLE>";			<!-- 12/03/10 -->
<?php
} else {
	foreach($categories as $key => $value) {		//	12/03/10
?>
		side_bar_html += "<DIV class='cat_button' STYLE='display: none;' onClick='set_chkbox(\"<?php print $value;?>\")'><?php print $value;?>: <input type=checkbox id='<?php print $value;?>' onClick='set_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!-- 12/03/10 -->
<?php
		}
		$all="ALL";		//	12/03/10
		$none="NONE";				//	12/03/10
?>
		side_bar_html += "<DIV class='cat_button' style='color: red;'>None Defined ! </DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'ALL_BUTTON' class='cat_button' STYLE='display: none;' onClick='set_chkbox(\"<?php print $all;?>\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='<?php print $all;?>' onClick='set_chkbox(\"<?php print $all;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'NONE_BUTTON' class='cat_button' STYLE='display: none;' onClick='set_chkbox(\"<?php print $none;?>\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='<?php print $none;?>' onClick='set_chkbox(\"<?php print $none;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
		side_bar_html +="</form></TD></TR></TABLE></DIV>";			<!-- 12/03/10 -->
<?php
}
?>


	$("boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div			

<?php
//	snap(__LINE__, round((microtime(true) - $time), 3));

	$fac_categories = array();													// 12/03/10
	$fac_categories = get_fac_category_butts();											// 12/03/10
?>
// ==================================== Add Facilities to Map 8/1/09 ================================================
	side_bar_html ="<TABLE border=0 CLASS='sidebar' WIDTH = <?php print $col_width;?> >\n";		// initialize facilities sidebar string, 10/23/12
	side_bar_html += "<TR CLASS = 'spacer'><TD CLASS='spacer' COLSPAN=99>&nbsp;</TD></TR>";	//	3/15/11, 10/23/12
	var icons=[];	
	var g=0;

	var fmarkers = [];
/*
	var baseIcon = new GIcon();				// 3190
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);		// 3197
	fac_icon.image = icons[1];
*/
function createfacMarker(fac_point, fac_tabs, id, fac_icon, type, region) {		// 3213
//	alert("3207 " +fac_icon);
	var region = region || 0;
	var fac_marker = new google.maps.Marker({position: fac_point, map: map, icon: fac_icon});		
//	var fac_html = fac_tabs;	// Show this markers index in the info window when it is clicked
	fac_marker.category = type;
	fac_marker.region = region;	
	fmarkers[id] = fac_marker;

	google.maps.event.addListener(fac_marker, "click", function() {
//		alert(3217);
		if (open_iw) {open_iw.close();} 					// another IW possibly open
		var infowindow = new google.maps.InfoWindow({ content: fac_tabs, maxWidth: 400});	 
		infowindow.open(map, fac_marker);		
		open_iw = infowindow;
		
//		fac_marker.openInfoWindowHtml(fac_html);
		});
	return fac_marker;
	}

<?php

	$fac_order_values = array(1 => "`handle`,`fac_type_name` ASC", 2 => "`fac_type_name`,`handle` ASC",  3 => "`fac_status_val`,`fac_type_name` ASC");		// 3/15/11

	if (array_key_exists ('forder' , $_POST))	{$_SESSION['fac_flag_2'] =  $_POST['forder'];}		// 3/15/11
	elseif (empty ($_SESSION['fac_flag_2'])) 	{$_SESSION['fac_flag_2'] = 2;}		// 3/15/11

	$fac_order_str = $fac_order_values[$_SESSION['fac_flag_2']];		// 3/15/11		
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
	$result = mysql_query($query);	//	6/10/11
	$al_groups = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	6/10/11
		$al_groups[] = $row['group'];
		}	
	
	if(isset($_SESSION['viewed_groups'])) {
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}
		
	if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
		$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
		} else {	
		if(!isset($curr_viewed)) {	
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {	//	6/10/11
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			}
		$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
		}
	
//-----------------------FACILITY BOUNDARY / CATCHMENT FENCE STUFF--------------------6/10/11
?>
	var thepoint;
	var points = new Array();
	
<?php	
	$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON ( `l`.`id` = `f`.`boundary`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `f`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )	
				{$where2} AND `use_with_f`=1 GROUP BY `l`.`id`";
	
	$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
	while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
		extract ($row_bn);
		$bn_name = $row_bn['line_name'];
		$all_boundaries[] = $row_bn['boundary'];		
		$points = explode (";", $line_data);

		$sep = "";
		echo "\n\t var points = [\n";
		for ($i = 0; $i<count($points); $i++) {
			$coords = explode (",", $points[$i]);
			echo	"{$sep}\n\t\tnew google.maps.LatLng({$coords[0]}, {$coords[1]})";
			$sep = ",";					
			}			// end for ($i = 0 ... )
		echo "];\n";

		if ((intval($filled) == 1) && (count($points) > 2)) {
?>
			  polyline = new google.maps.Polygon({
			    paths: 			 points,
			    strokeColor: 	 add_hash("<?php echo $line_color;?>"),
			    strokeOpacity: 	 <?php echo $line_opacity;?>,
			    strokeWeight: 	 <?php echo $line_width;?>,
			    fillColor: 		 add_hash("<?php echo $fill_color;?>"),
			    fillOpacity: 	 <?php echo $fill_opacity;?>
				});

<?php	} else {
?>
			  polyline = new google.maps.Polygon({
			    paths: 			points,
			    strokeColor: 	add_hash("<?php echo $line_color;?>"),
			    strokeOpacity: 	<?php echo $line_opacity;?>,
			    strokeWeight: 	<?php echo $line_width;?>,
			    fillColor: 		add_hash("<?php echo $fill_color;?>"),
			    fillOpacity: 	<?php echo $fill_opacity;?>
				});
<?php	} ?>				        

			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
			polyline.setMap(map);	
			
<?php
			}	//	End while
//-------------------------END OF FACILITY BOUNDARY / CATCHMENT STUFF-------------------------			
	
	$query_fac = "SELECT *,`$GLOBALS[mysql_prefix]facilities`.`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]facilities`.`id` 						AS `fac_id`, 
		`$GLOBALS[mysql_prefix]facilities`.`description` 				AS `facility_description`,
		`$GLOBALS[mysql_prefix]facilities`.`boundary` 					AS `boundary`,		
		`$GLOBALS[mysql_prefix]fac_types`.`name` 						AS `fac_type_name`, 
		`$GLOBALS[mysql_prefix]facilities`.`name` 						AS `facility_name`, 
		`$GLOBALS[mysql_prefix]fac_status`.`status_val` 				AS `fac_status_val`, 
		`$GLOBALS[mysql_prefix]facilities`.`status_id` 					AS `fac_status_id`
		FROM `$GLOBALS[mysql_prefix]facilities`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 	ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = 			`$GLOBALS[mysql_prefix]allocates`.`resource_id` )	
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` 	ON (`$GLOBALS[mysql_prefix]facilities`.`type` = 		`$GLOBALS[mysql_prefix]fac_types`.`id` )
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` 	ON (`$GLOBALS[mysql_prefix]facilities`.`status_id` = 	`$GLOBALS[mysql_prefix]fac_status`.`id` )
		{$where2} 
		GROUP BY fac_id ORDER BY {$fac_order_str} ";											// 3/15/11, 6/10/11
//	snap(__LINE__, $query_fac);
	
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$temp = $col_width;
	$mail_str = (may_email())? "do_fac_mail_win();": "";		// 7/2/10
//	print "\n\t\tside_bar_html += \"<TR CLASS='even' colspan='99'><TABLE ID='fac_table' STYLE='display: inline-block; width: 100%'>\"\n";
	$facs_ct = mysql_affected_rows();			// 1/4/10
	if ($facs_ct==0){
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TH COLSPAN=99 ALIGN='center'><I><B>No Facilities!</I></B></TH></TR>\"\n";	//	3/15/11
		} else {
		$fs_checked = array ("", "", "", "");
		$fs_checked[$_SESSION['fac_flag_2']] = " CHECKED";
?>
		side_bar_html += "<TR CLASS = 'even'><TD COLSPAN=99 ALIGN='center'>";	//	3/15/11
		side_bar_html += "<I><B>Sort</B>:&nbsp;&nbsp;&nbsp;&nbsp;";
		side_bar_html += "Handle&raquo; 	<input type = radio name = 'frm_order' value = 1 <?php print $fs_checked[1];?> onClick = 'do_fac_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	//	3/15/11, 5/3/11
		side_bar_html += "Type &raquo; 	<input type = radio name = 'frm_order' value = 2 <?php print $fs_checked[2];?> onClick = 'do_fac_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	//	3/15/11, 5/3/11
		side_bar_html += "Status &raquo; <input type = radio name = 'frm_order' value = 3 <?php print $fs_checked[3];?> onClick = 'do_fac_sort_sub(this.value);' />";	//	3/15/11, 5/3/11
		side_bar_html += "</I></TD></TR>";	//	3/15/11
<?php
	
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD><B>Icon</B></TD><TD ALIGN='left'>&nbsp;&nbsp;&nbsp;&nbsp;<B>" . get_text ("Type") . "</B></TD><TD ALIGN='left'><B>" . get_text ("Facility") . "</B> ({$facs_ct}) </TD><TD ALIGN='left'><IMG SRC='mail_red.png' BORDER=0 onClick = '{$mail_str}'/></TD><TD COLSPAN=2 ALIGN='center'><B>" . get_text ("Beds") . "</B></TD><TD>&nbsp;<B>" . get_text ("Status") . "</B></TD><TD ALIGN='left'><B>&nbsp;" . get_text ("As of") . "</B></TD></TR>\"\n";	// 7/2/10, 3/15/11
		}

// ===========================  begin major while() for FACILITIES ==========
	
	$quick = (!(is_guest()) && (intval(get_variable('quick')==1)));				// 11/27/09		
	$sb_indx = 0;																// for fac's only 8/5/10

	while($row_fac = mysql_fetch_assoc($result_fac)){		// 7/7/10
//		snap(__LINE__, $row_fac['fac_id']);
	
		$fac_gps = get_allocates(3, $row_fac['fac_id']);	//	6/10/11
		$grp_names = "Groups Assigned: ";	//	6/10/11
		$y=0;	//	6/10/11
		foreach($fac_gps as $value) {	//	6/10/11
			$counter = (count($fac_gps) > ($y+1)) ? ", " : "";
			$grp_names .= get_groupname($value);
			$grp_names .= $counter;
			$y++;
			}
		$grp_names .= " / ";
			
		$fac_id=($row_fac['fac_id']);
		$fac_type=($row_fac['icon']);
//		dump (__LINE__);
//		dump ($fac_type);
		$fac_type_name = ($row_fac['fac_type_name']);
		$fac_region = get_first_group(3, $fac_id);		
	
		$fac_index = $row_fac['icon_str'];	
		$fac_name = $row_fac['handle'];		//		10/8/09

		$on_click= ($quick)? "fac_click_ed({$fac_id})" : $clickevent="fac_click_iw({$sb_indx})";	// 8/5/10
			
		print "\t\tvar fac_sym = '" . addslashes($fac_index) . "';\n";			//	 for sidebar and icon 10/8/09 - 4/27/11
		
		$toroute = (is_guest() || is_unit())? "": "&nbsp;<A HREF='{$_SESSION['routesfile']}?ticket_id=" . $fac_id . "'><U>Dispatch</U></A>";// 11/10/09, 7/27/10
	
		if(is_guest() || is_unit()) {		// 7/27/10
			$facedit = $toroute = $facmail = "";
			}
		else {
			$facedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&edit=true&id=" . $row_fac['fac_id'] . "'><U>Edit</U></A>" ;
			$facmail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_mail_fac_win('" .$row_fac['fac_id']  . "');><U><B>Email</B></U></SPAN>" ;
			$toroute = "&nbsp;<A HREF='{$_SESSION['facroutesfile']}?fac_id=" . $fac_id . "'><U>Route To Facility</U></A>";//	 8/2/08
			}
	
		if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {
	
//			$f_disp_name = $row_fac['handle'];	//		10/8/09
			$facility_display_name = $f_disp_name = $row_fac['handle'];	
			$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row_fac['icon']];		// 2/8/10
			$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row_fac['icon']];		// 2/8/10			
	
			$sidebar_fac_line = "<TD ALIGN='left'  onClick = '{$on_click};' >&nbsp;&nbsp;&nbsp;" . addslashes(shorten($row_fac['fac_type_name'],16)) ."</TD>";
			$sidebar_fac_line .= "<TD onClick = '{$on_click}' TITLE = '" . $grp_names . addslashes($facility_display_name) . "' ALIGN='left'><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};' >" . addslashes(shorten($facility_display_name, 24)) ."</SPAN></TD>";	//11/29/10, 3/15/11
// MAIL						
			if ((may_email()) && ((is_email($row_fac['contact_email'])) || (is_email($row_fac['security_email']))) ) {		// 7/2/10
													// 5/19/11
             $mail_link = "\t<TD CLASS='mylink' ALIGN='left'>"
                  . "<IMG SRC='mail.png' width='10' height='10' BORDER=0 TITLE = 'click to email facility {$f_disp_name[0]}'"
                  . " onclick = 'do_mail_win(\\\"{$f_disp_name[0]},{$row_fac['contact_email']}\\\");'> "
                  . "</TD>";                            // 4/26/09
					}
			else {
				$mail_link = "\t<TD ALIGN='left'>na</TD>";
				}
			$sidebar_fac_line .= "{$mail_link}";
// BEDS
			$sidebar_fac_line .= "<TD ALIGN='right' onClick = '{$on_click}'>{$row_fac['beds_a']}/{$row_fac['beds_o']}</TD>";
			$sidebar_fac_line .= "<TD ALIGN='left' onClick = '{$on_click}' TITLE = '{$row_fac['beds_info']}'><NOBR>" . shorten($row_fac['beds_info'], 10) . "</NOBR></TD>";
// STATUS
			$sidebar_fac_line .= "<TD>" . get_status_sel($row_fac['fac_id'], $row_fac['fac_status_id'], "f") . "</TD>";		// status, 3/15/11
//			$sidebar_fac_line .= "<TD ALIGN='left'  onClick = '{$on_click};' >" . addslashes($row_fac['status_val']) ."</TD>";
// AS-OF - 11/3/2012
			$sidebar_fac_line .= "<TD onClick = '{$on_click};' TITLE = '{$row_fac['updated']}'>&nbsp;" . format_sb_date_2($row_fac['updated']) . "</TD>";
	
			$fac_tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>Description:&nbsp;</TD><TD ALIGN='left'>" . replace_quotes(addslashes(str_replace($eols, " ", $row_fac['facility_description']))) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
// 10/29/2012
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>" . get_text("Beds") . ":&nbsp;</TD><TD ALIGN='left'>" . get_text("Available"). ":&nbsp;&nbsp;" . $row_fac['beds_a'] . "&nbsp;&nbsp;&nbsp;" . get_text("Occupied") . ":&nbsp;&nbsp;" . $row_fac['beds_o'] . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Beds information") . ":&nbsp;</TD><TD ALIGN='left'>" . $row_fac['beds_info'] . "</TD></TR>";

			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='left'>As of:&nbsp;</TD><TD ALIGN='left' TITLE = '{$row_fac['updated']}'> " . format_sb_date_2($row_fac['updated']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $facedit . $facmail . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&view=true&id=" . $row_fac['fac_id'] . "'><U>View</U></A></TD></TR>";
//			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toroute . $facedit ."&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&view=true&id=" . $row_fac['fac_id'] . "'><U>View</U></A></TD></TR>";
			$fac_tab_1 .= "</TABLE>";
	
			$fac_tab_2 = "<DIV style='max-height: 200px; overflow: auto;'><TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='left'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . replace_quotes(addslashes($row_fac['security_contact'])) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='left'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='left'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='left'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . replace_quotes(addslashes(str_replace($eols, " ", $row_fac['access_rules']))) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='left'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . replace_quotes(addslashes(str_replace($eols, " ", $row_fac['security_reqs']))) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='left'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . replace_quotes(addslashes(str_replace($eols, " ", $row_fac['opening_hours']))) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='left'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='left'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
			$fac_tab_2 .= "</TABLE></DIV>";
			
?>
/*			
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print replace_quotes(nl2brr(addslashes(shorten($row_fac['facility_name'], 10))));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
*/
			var myfacinfoTabs = "<?php echo $fac_tab_1; ?>";		// 3388
<?php
			if ((($row_fac['lat'] == "0.999999") && ($row_fac['lng'] == "0.999999")) || (($row_fac['lat'] == "") || ($row_fac['lat'] == NULL)) || (($row_fac['lng'] == "") || ($row_fac['lng'] == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
//				echo "var fac_icon = new GIcon(baseIcon);	// 3447\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_type_name = \"$fac_type_name\";\n";
				echo "var region = \"$fac_region\";\n";				
				echo "var fac_icon_url = \"./our_icons/question1.png\";\n";
//				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new google.maps.LatLng(" . get_variable('def_lat') . "," . get_variable('def_lng') . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon, fac_type_name, region);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
			} else {
//				echo "var fac_icon = new GIcon(baseIcon);		// 3458 \n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_type_name = \"$fac_type_name\";\n";
				echo "var region = \"$fac_region\";\n";					
?>
		var origin = ((fac_sym.length)>3)? (fac_sym.length)-3: 0;					// 3436 - low-order three chars 3/22/11
		var iconStr = fac_sym.substring(origin);
<?php
				echo "var fac_icon_url = \"./our_icons/gen_fac_icon.php?blank=$fac_type&text=\" + (iconStr) + \"\";\n";
//				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new google.maps.LatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
//				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon, fac_type_name, region);\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon_url, fac_type_name, region);\n";
//				echo "map.addOverlay(fac_marker);\n";
				echo "fac_marker.setMap(map);\n";
				echo "\n";
				}
				}//	 end if my_is_float
?>
			if(quick) {					//	 set up for facility edit - 11/27/09
				do_sidebar_fac_ed ("<?php print $sidebar_fac_line;?>", g, fac_sym, fac_icon_url, fac_type_name);	 // 3451	
				}
			else {				//	 set up for facility infowindow
				do_sidebar_fac_iw ("<?php print $sidebar_fac_line;?>", g, fac_sym, fac_icon_url, fac_type_name);	// 3454
				}
			g++;
<?php
	$sb_indx++;				// zero-based - 6/30/10
	}	// end while()
?>
	side_bar_html += "</TD></TR>\n";	//	11/29/10, 12/03/10
<?php

// ===================================== End of functions to show facilities========================================================================
?>
	if (!(map_is_fixed)){							// if fixed then map is already at center
		if (!got_points) {							// any? - 6/21/12
			map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			map.fitBounds(bounds);					// Now fit the map to the bounds
			var listener = google.maps.event.addListenerOnce (map, "idle", function() { 
				if (map.getZoom() > 16) map.setZoom(15); 
				});
			}			// end if/else (!got_points)
		}			// end if (!(map_is_fixed))
	
	side_bar_html +="</TABLE></TD></TR>\n";		//	3/15/11
	$("side_bar_f").innerHTML = side_bar_html;	//side_bar_html to facilities div
	
	side_bar_html ="<TABLE border='0' VALIGN='top' ALIGN='center' CLASS='sidebar' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.4));?> >";	//	11/29/10, 3/15/11
	side_bar_html +="<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>";	//	11/29/10, 3/15/11
	side_bar_html +="<TR class='even'><TD ALIGN='center' COLSPAN=99><B>Facilities Legend</B></TD></TR>";		// legend row, 11/29/10, 3/15/11
	side_bar_html +="<TR class='even'><TD ALIGN='center' COLSPAN=99><?php print get_facilities_legend();?></TD></TR></TABLE>\n";	//	3/15/11
	$("facs_legend").innerHTML = side_bar_html + "</TABLE>";	//side_bar_html to facilities legend div

	side_bar_html = "";
	side_bar_html+="<TABLE><TR class='heading_2'><TH width = <?php print $ctrls_width;?> ALIGN='center'>Facilities</TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label' ><form action='#'>";			//	12/03/10, 3/15/11
<?php
if(!empty($fac_categories)) {
	function get_fac_icon($fac_cat){			// returns legend string
		$icons = $GLOBALS['fac_icons'];
		$sm_fac_icons = $GLOBALS['sm_fac_icons'];
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` WHERE `name` = \"$fac_cat\"";		// types in use
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$print = "";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$fac_icon = $row['icon'];
			$print .= "<IMG SRC = './our_icons/" . $sm_fac_icons[$fac_icon] . "' STYLE = 'vertical-align: middle'>";
			}
		unset($result);
		return $print;
	}

		foreach($fac_categories as $key => $value) {		//	12/03/10
		$curr_icon = get_fac_icon($value);
?>
			side_bar_html += "<DIV class='cat_button' onClick='set_fac_chkbox(\"<?php print $value;?>\")'><?php print get_fac_icon($value);?>&nbsp;&nbsp;<?php print $value;?>: <input type=checkbox id='<?php print $value;?>'  onClick='set_fac_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!-- 12/03/10 -->
<?php
		}



	$all="fac_ALL";		//	12/03/10
	$none="fac_NONE";				//	12/03/10
?>
	side_bar_html += "<DIV ID = 'fac_ALL_BUTTON'  class='cat_button' onClick='set_fac_chkbox(\"<?php print $all;?>\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='<?php print $all;?>' onClick='set_fac_chkbox(\"<?php print $all;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
	side_bar_html += "<DIV ID = 'fac_NONE_BUTTON'  class='cat_button' onClick='set_fac_chkbox(\"<?php print $none;?>\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='<?php print $none;?>' onClick='set_fac_chkbox(\"<?php print $none;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
	side_bar_html += "<DIV ID = 'fac_go_can' style='float:right; padding:2px;'><SPAN ID = 'fac_go_button' onClick='do_go_facilities_button()' class='conf_next_button' STYLE = 'display:none;'><U>Next</U></SPAN>";
	side_bar_html += "<SPAN ID = 'fac_can_button'  onClick='fac_cancel_buttons()' class='conf_can_button' STYLE = 'display:none;'><U>Cancel</U></SPAN></DIV>";
	side_bar_html+="</DIV></form></TD></TR></TABLE>";			<!-- 12/03/10, 3/15/11 -->
	$("fac_boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div			

<?php
} else {
	function get_fac_icon($fac_cat){			// returns legend string
		$icons = $GLOBALS['fac_icons'];
		$sm_fac_icons = $GLOBALS['sm_fac_icons'];
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` WHERE `name` = \"$fac_cat\"";		// types in use
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$print = "";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$fac_icon = $row['icon'];
			$print .= "<IMG SRC = './our_icons/" . $sm_fac_icons[$fac_icon] . "' STYLE = 'vertical-align: middle'>";
			}
		unset($result);
		return $print;
	}

		foreach($fac_categories as $key => $value) {		//	12/03/10
		$curr_icon = get_fac_icon($value);
?>
			side_bar_html += "<DIV class='cat_button' STYLE='display: none;' onClick='set_fac_chkbox(\"<?php print $value;?>\")'><?php print get_fac_icon($value);?>&nbsp;&nbsp;<?php print $value;?>: <input type=checkbox id='<?php print $value;?>'  onClick='set_fac_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!-- 12/03/10 -->
<?php
		}



	$all="fac_ALL";		//	12/03/10
	$none="fac_NONE";				//	12/03/10
?>	
	side_bar_html= "";		//	12/03/10
	side_bar_html+="<TABLE><TR class='heading_2'><TH width = <?php print $ctrls_width;?> ALIGN='center' COLSPAN='99'>Facilities</TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label'><form action='#'>";			//	12/03/10, 3/15/11
	side_bar_html += "<DIV class='cat_button' style='color: red;'>None Defined ! </DIV>";			<!-- 12/03/10, 3/15/11 -->
<?php
	$all="fac_ALL";		//	12/03/10
	$none="fac_NONE";				//	12/03/10
?>
	side_bar_html += "<DIV ID = 'fac_ALL_BUTTON' class='cat_button' STYLE='display: none;'><input type=checkbox id='<?php print $all;?>'/></DIV>";			<!-- 12/03/10 -->
	side_bar_html += "<DIV ID = 'fac_NONE_BUTTON' class='cat_button' STYLE='display: none;'><input type=checkbox id='<?php print $none;?>'/></DIV>";			<!-- 12/03/10 -->
	side_bar_html += "</form></TD></TR></TABLE></DIV>";			<!-- 12/03/10 -->	
	$("fac_boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div			

<?php
}
//	------------------------------Buttons for boundaries show and hide
	$boundary_names = array();
	$bn = 0;
	// $query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup`";
	// $result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	// while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {;
		// $boundary_names[$bn] = $row['line_name'];
		// $bn++;
		// }
	foreach($all_boundaries as $key => $value) {	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`='{$value}'";	
		$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$boundary_names[$bn] = $row['line_name'];
			$bn++;
		}
	}
	?>
	side_bar_html= "";		//	6/10/11
	side_bar_html+="<TABLE><TR class='heading_2'><TH width = <?php print $ctrls_width;?> ALIGN='center'>Boundaries and Fences</TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label' ><form action='#'>";			//		6/10/11

<?php
$boundary_names = array_unique($boundary_names);
$counter = count($boundary_names);
if($counter == 0) {
?>
		side_bar_html += "<DIV class='cat_button' STYLE='display: none;'>No Boundaries</DIV>";			<!-- 6/10/11 -->
		side_bar_html += "<DIV class='cat_button' style='color: red;'>None Defined ! </DIV>";			<!-- 6/10/11 -->
		side_bar_html +="</form></TD></TR></TABLE></DIV>";			<!-- 6/10/11 -->
<?php
} else {

	foreach($boundary_names as $key => $value) {	// 6/10/11
?>
		side_bar_html += "<DIV class='cat_button' onClick='set_bnd_chkbox(\"<?php print $value;?>\")'><?php print $value;?>: <input type=checkbox id='<?php print $value;?>' onClick='set_bnd_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!--6/10/11 -->
<?php
		}
		$all="BND_ALL";		//	6/10/11
		$none="BND_NONE";				//	6/10/11
?>
		side_bar_html += "<DIV ID = 'BND_ALL_BUTTON' class='cat_button' onClick='set_bnd_chkbox(\"<?php print $all;?>\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='<?php print $all;?>' onClick='set_bnd_chkbox(\"<?php print $all;?>\")'/></FONT></DIV>";			<!-- 6/10/11 -->
		side_bar_html += "<DIV ID = 'BND_NONE_BUTTON' class='cat_button'  onClick='set_bnd_chkbox(\"<?php print $none;?>\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='<?php print $none;?>' onClick='set_bnd_chkbox(\"<?php print $none;?>\")'/></FONT></DIV>";			<!-- 6/10/11 -->
		side_bar_html += "<DIV ID = 'bnd_go_can' style='float:right; padding:2px;'><SPAN ID = 'bnd_go_button' onClick='do_go_bnd_button()' class='conf_next_button' STYLE = 'display:none;'><U>Next</U></SPAN>";
		side_bar_html += "<SPAN ID = 'bnd_can_button'  onClick='bnd_cancel_buttons()' class='conf_can_button' STYLE = 'display:none;'><U>Cancel</U></SPAN></DIV>";
		side_bar_html+="</form></TD></TR></TABLE>";			<!-- 6/10/11 -->
<?php
}
?>
	$("poly_boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div	
<?php
//	---------------------------end of Buttons for boundaries show and hide	
// code below revised 11/29/10 to remove scheduled / current buttons and repair faulty display of facilities

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 1 ";		// 10/21/09

		$result_ct = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_closed = mysql_num_rows($result_ct); 
		unset($result_ct);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 3 ";		// 10/21/09
		$result_scheduled = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_scheduled = mysql_num_rows($result_scheduled); 
		unset($result_scheduled);

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}

	do_kml();			// kml display - 6/28/11

?>
// ===============================================  3633  ==============================================
//	}		// end if (GBrowserIsCompatible())
//else {
//	alert("Sorry, browser compatibility problem. Contact your tech support group.");
//	}
	
function tester() {
	alert("2093 " + gmarkers.length);
	for (i=0; i<gmarkers.length; i++) {
		alert("3624 " + i + " " + gmarkers[i]['x']);
		}
	}
</SCRIPT>

<?php
//	snap(__LINE__, round((microtime(true) - $time), 3));

	echo "Elapsed: ".round((microtime(true) - $time), 3)."s";

	}				// end function list_tickets() ===========================================================


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

//	Regions stuff	6/10/11		
		
print get_buttons_inner();	// 4/12/12
print get_buttons_inner2();	//	4/12/12

//	End of Regions stuff
	
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
				<TD ALIGN='left'>" .  $row['tick_street'] . "</TD></TR>\n";
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
		print empty($row['rec_fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>Receiving Facility:</TD>	
				<TD ALIGN='left'>" .  $row['rec_fac_name'] . "</TD></TR>\n";	// 10/6/09	
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
			echo "var unit_icon = new GIcon(baseIcon);		// 4214\n";
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
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09
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
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09
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
	global $istest, $iw_width;


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

	$query = "SELECT *,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		`date` AS `date`,
		`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` 
		FROM `$GLOBALS[mysql_prefix]ticket` 
		WHERE ID='$id' $restrict_ticket";	// 8/12/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">No such ticket or user access to ticket is denied</FONT>";
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">	<!-- 4502 -->
<?php

	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map_canvas' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		</TD></TR>";	// 11/29/08

	print "<FORM NAME='sv_form' METHOD='post' ACTION=''><INPUT TYPE='hidden' NAME='frm_lat' VALUE=" .$row['lat'] . ">";		// 2/11/09
	print "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=" .$row['lng'] . "></FORM>";

	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n";
	print "</TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];
	print "</TABLE>\n";


?>
	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT>
	<SCRIPT>


	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var grid_bool = false;		
	function toglGrid() {						// toggle
		grid_bool = !grid_bool;
		if (grid_bool)	{ grid = new Graticule(map); }
		else 			{ grid.setMap(null); }
		}		// end function toglGrid()
	

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

	var icons=[];						// note globals	- 1/29/09
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./our_icons/blue.png";		// normal
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./our_icons/green.png";	// green
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./our_icons/red.png";		// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./our_icons/white.png";	// white - not in use

<?php
	$icon_file =  ((float)$lat==(float)$GLOBALS['NM_LAT_VAL'])? "./our_icons/question1.png" : "icons[{$row['severity']}]";
?>
	var point = new google.maps.LatLng(<?php print $lat;?>, <?php print $lng;?>);	// 4599
//			 4600 - no callback, read-only
		map = gmaps_v3_init(null, 'map_canvas', 
			<?php echo $lat;?>, 
			<?php echo $lng;?>, 
			<?php echo (get_variable('def_zoom')*2);?>, 
			<?php echo $icon_file;?>,  
			<?php echo get_variable('maptype');?>, 
			true);		

	var bounds = new google.maps.LatLngBounds();		// Initialize 
	bounds.extend(new google.maps.LatLng(<?php print $lat;?>, <?php print $lng;?>));			// include incident point

// ====================================Add Active Responding Units to Map =========================================================================
	var icons=[];						// note globals	- 1/29/09
	icons[1] = "./our_icons/white.png";		// normal
	icons[2] = "./our_icons/black.png";	// green
	unit_icon = icons[1];
function createMarker(unit_point, number) {		// unit marker
	bounds.extend(unit_point);	
	var unit_marker = new google.maps.Marker({position: unit_point, map: map, icon: unit_icon});			
	
	var html = number;	// Show this markers index in the info window when it is clicked

	google.maps.event.addListener(unit_marker, "click", function() {
		unit_marker.openInfoWindowHtml(html);
		});
	return unit_marker;
	}

// alert(4634);

<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
		$responder_id=($row['responder_id']);
		if ($row['clear'] == NULL) {
	
			$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id' ";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id=($row_unit['id']);
				$mobile=($row_unit['mobile']);
				if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {
			
					if ($mobile == 1) {
						echo "var unit_icon = \"./our_icons/gen_icon.php?blank=0&text={{$row_unit['icon_str']}}\";\n";				// black - 4/18/09
						echo "var unit_point = new google.maps.LatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
						echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
						echo "unit_marker.setMap(map);\n";
						echo "\n";
					} else {
						echo "var unit_icon = \"./our_icons/gen_icon.php?blank=4&text={$row_unit['icon_str']}\";\n";				// white - 4/18/09
						echo "var unit_point = new google.maps.LatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
						echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
						echo "unit_marker.setMap(map);\n";	
						echo "\n";
						}	// end if/else ($mobile)
					}	// end ((my_is_float()) - responding units
				}	// end outer if
			}	// end inner while
		}	//	end outer while

// =====================================End of functions to show responding units========================================================================
// ====================================Add Facilities to Map 8/1/09================================================
?>
//	alert(4675);
	var icons=[];	
	var g=0;

	var fmarkers = [];
/*
	var baseIcon = new GIcon();			// 4831
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);	// 4738
	fac_icon.image = icons[1];
*/

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	bounds.extend(fac_point);	
	var fac_marker = new google.maps.Marker({position: fac_point, map: map, icon: fac_icon});			

	var fac_html = fac_name;			// Show this markers index in the info window when it is clicked
	fmarkers[id] = fac_marker;
	google.maps.event.addListener(fac_marker, "click", function() {
		try  {open_iw.close()} catch (e) {;}					// another IW possibly open
		map.setCenter(point, 8);    
		infowindow = new google.maps.InfoWindow({ content: fac_html, maxWidth: 400});	
		});
	return fac_marker;
}

<?php

	$query_fac = "SELECT *,
		`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]facilities`.`id` 			AS `fac_id`, 
		`$GLOBALS[mysql_prefix]facilities`.`description` 	AS `facility_description`, 
		`$GLOBALS[mysql_prefix]fac_types`.`name` 			AS `fac_type_name`, 
		`$GLOBALS[mysql_prefix]facilities`.`name` 			AS `facility_name` 
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` 	ON `$GLOBALS[mysql_prefix]facilities`.`type` = 		`$GLOBALS[mysql_prefix]fac_types`.`id` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` 	ON `$GLOBALS[mysql_prefix]facilities`.`status_id` =	`$GLOBALS[mysql_prefix]fac_status`.`id` 
		ORDER BY `$GLOBALS[mysql_prefix]facilities`.`type` ASC ";
	
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	while($row_fac = mysql_fetch_array($result_fac)){

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac =stripslashes_deep( mysql_fetch_array($result_fac))){		// 

		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index = substr($fac_temp[count($fac_temp) -1], -6, strlen($fac_temp[count($fac_temp) -1]));	// 3/19/11
		
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
//			var fac_sym = (g+1).toString();
/*
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
*/
			var myfacinfoTabs = "<?php echo $fac_tab_1;?>";
<?php

			if ((($row_fac['lat'] == $GLOBALS['NM_LAT_VAL']) && ($row_fac['lng'] == $GLOBALS['NM_LAT_VAL'])) || (($row_fac['lat'] == "") || ($row_fac['lat'] == NULL)) || (($row_fac['lng'] == "") || ($row_fac['lng'] == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
//				snap(__LINE__, $row_fac['fac_id']);

?>
//			var fac_icon = new GIcon(baseIcon);		// 4819
			var fac_type = <?php echo $fac_type;?>;
			var fac_icon_url = "./our_icons/question1.png";
//			fac_icon.image = fac_icon_url;
<?php
				echo "var fac_point = new google.maps.LatLng(" . get_variable('def_lat') . "," . get_variable('def_lng') . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon_url);		// 4779\n";
				echo "fac_marker.setMap(map);\n";	
				echo "\n";
			} else {
//				snap(__LINE__, $row_fac['fac_id']);

?>
//		var fac_icon = new GIcon(baseIcon);	// 799
		var fac_type = <?php echo $fac_type;?>;
		var origin = ((fac_sym.length)>3)? (fac_sym.length)-3: 0;						// low-order three chars 3/22/11
		var iconStr = fac_sym.substring(origin);
		var fac_icon = "./our_icons/gen_fac_icon.php?blank=" + fac_type + "&text=" + iconStr ;
//		fac_icon.image = fac_icon_url;
		var fac_point = new google.maps.LatLng(<?php echo $row_fac['lat'];?>, <?php echo $row_fac['lng'] ?>);
		var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);		// 4808
		fac_marker.setMap(map);	
//		alert(4811)\n";
<?php
				}

		}	// end if my_is_float - facilities

?>
		g++;
<?php
	}	// end while

}
// ===================================== End of functions to show facilities========================================================================
	do_kml();			// kml functions
?>
	map.fitBounds(bounds);					// Now fit the map to the developed ounds


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
<?php
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
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09
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
	