<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$iw_width= "300px";					// map infowindow with
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$ret_arr = array();
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$sortby = (!(array_key_exists('sort', $_GET))) ? "tick_id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$u_sb_indx = 0;	//	12/23/13
$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}
unset($result);

function subval_sort($a, $subkey, $dd) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	if($dd == 1) {	
		asort($b);
		} else {
		arsort($b);
		}
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}

function can_do_dispatch($the_row) {
	if (intval($the_row['multi'])==1) return TRUE;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = {$the_row['unit_id']}";	// all dispatches this unit
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_temp = stripslashes_deep(mysql_fetch_array($result_temp))) {		// check any open runs this unit
		if (!(is_date($row_temp['clear']))) { 			// if  clear is empty, then NOT dispatch-able
			unset ($result_temp, $row_temp); 
			return FALSE;
			}
		}		// end while ($row_temp ...)
	unset ($result_temp, $row_temp); 
	return TRUE;					// none found, can dispatch
	}		// end function can do_dispatch()
	
function unit_cat($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_types` WHERE `id` = " . $id;	// all dispatches this unit
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_array($result));
	return $row['name'];
	}
	

function is_ok_coord($inval) {				// // 3/14/12
	return ((abs(floatval($inval) != 0.0)) && (floatval($inval) != $GLOBALS['NM_LAT_VAL']));
	}

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

//	$order_str = $order_values[$_SESSION['unit_flag_2']];		// 6/11/10
$order_str = $order_values[1];		// 6/11/10

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
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
	
$query1 = "SELECT *, r.updated AS `r_updated`,
	`r`.`status_updated` AS `status_updated`,
	`r`.`id` AS `unit_id`,
	`r`.`name` AS `name`,
	`r`.`description` AS `unit_descr`, 
	`r`.`ring_fence` AS `ring_fence`,	
	`r`.`excl_zone` AS `excl_zone`	
	FROM `$GLOBALS[mysql_prefix]responder` `r` 
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )			
	{$where2} ORDER BY `unit_id` DESC LIMIT 1";

$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row1 = stripslashes_deep(mysql_fetch_assoc($result1));
$latest_id = (mysql_num_rows($result1) >0) ? $row1['unit_id'] : 0;

$query = "SELECT *, r.updated AS `r_updated`,
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
	{$where2}  GROUP BY unit_id ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC ";											// 2/1/10, 3/15/10, 6/10/11

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$units_ct = mysql_affected_rows();			// 1/4/10
if ($units_ct==0){
//	print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TH></TH><TH ALIGN='center' COLSPAN=99><I><B>No units!</I></B></TH></TR>\"\n";
	}
else {
	$checked = array ("", "", "", "");
	$checked[$_SESSION['unit_flag_2']] = " CHECKED";
	}

$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = $mob_tracker = FALSE;		//7/23/09

$utc = gmdate ("U");				// 3/25/09

// ===========================  begin major while() for RESPONDER ==========

$chgd_unit = $_SESSION['unit_flag_1'];					// possibly 0 - 4/8/10
$_SESSION['unit_flag_1'] = 0;							// one-time only - 4/11/10
$i = 1;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 7/7/10
	$resp_gps = get_allocates(2, $row['unit_id']);	//	6/10/11
	$the_color = ($row['mobile']=="1")? 4 : 0;		// icon color black, white		-- 4/18/09
	$grp_names = "Groups Assigned: ";	//	6/10/11
	$y=0;	//	6/10/11
	foreach($resp_gps as $value) {	//	6/10/11
		$counter = (count($resp_gps) > ($y+1)) ? ", " : "";
		$grp_names .= get_groupname($value);
		$grp_names .= $counter;
		$y++;
		}

	$tip =  addslashes($grp_names . " / " . htmlentities($row['name'],ENT_QUOTES));		// tooltip string - 1/3/10
		
	$latitude = ($row['lat']) ? $row['lat'] : get_variable('def_lat');		// 7/18/10		
	$longitude = ($row['lng']) ? $row['lng'] : get_variable('def_lng');		// 7/18/10

	$got_point = FALSE;

	$name = $row['name'];			//	10/8/09
	$index = $row['icon_str'];	// 4/27/11
	$track_type = get_remote_type($row) ;				// 7/8/11		
										// 2/13/09
	$callsign = ($track_type == 8) ? "999_" . $row['unit_id']: $row['callsign'];
	$hide_unit = ($row['hide']=="y")? "1" : "0" ;		// 3/8/10
	$fac_type_name = $row['un_type_name'];
	
	$type = $row['icon'];

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
			$got_point = TRUE;
			}
		unset($result_tr);
		}

	$the_bull = "";											// define the bullet
	$update_error = strtotime('now - 6 hours');				// set the time for silent setting
// NAME
	$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 2/1/10
	$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
	$name = htmlentities($row['name'],ENT_QUOTES);
	$handle = htmlentities($row['handle'],ENT_QUOTES);

// MAIL						
	if ((!is_guest()) && is_email($row['contact_via'])) {		// 2/1/10
		$mail_link = $row['contact_via'];
		} else {
		$mail_link = "";
		}

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
			$ass_td = " ";
			$flaginfo = "";
			break;			
		case 1:
			$row_assign = stripslashes_deep(mysql_fetch_assoc($result_as));
			$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
			$tip = htmlentities ("{$row_assign['contact']}/{$row_assign['street']}/{$row_assign['city']}/{$row_assign['phone']}/{$row_assign['scope']}", ENT_QUOTES );
			$addrs = $row_assign['street'] . " " . $row_assign['city'] . " " . $row_assign['state'];
			$flaginfo = $row_assign['scope'] . "<BR />";
//			$flaginfo .= "Address: " . $addrs . "<BR />";
//			$flaginfo .= ($row['contact_via'] != "") ? "Contact: " . $row['contact_via'] . "<BR />" : "";
//			$flaginfo .= ($row['smsg_id'] != "") ? "SMSG ID: " . $row['smsg_id'] . "<BR />" : "";
			switch($row_assign['severity'])		{		//color tickets by severity
				case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 							$severityclass='severity_normal'; break;
				}		// end switch()
			$ass_td = "<SPAN CLASS='" . $severityclass . "'>" . shorten($row_assign['scope'], 20) . "</SPAN>";
			break;
		default:
			$ass_td = $units_assigned;
			$flaginfo = "";
			break;
		}

// STATUS
	$status = get_status_sel($row['unit_id'], $row['un_status_id'], "u");		// status
	$status_name = $status_vals[$row['un_status_id']];
	$statusTemp = htmlentities($row['status_about'],ENT_QUOTES);
	$status_about = shorten($statusTemp, 25);

//  MOBILITY
	if 	($row_track){
		if ($row_track['speed']>=50) {
			$the_bull = $GLOBALS['TRACK_2L'][$track_type];
			$bull_color = 'blue';
			}
		if ($row_track['speed']<50) {
			$the_bull = $GLOBALS['TRACK_2L'][$track_type];
			$bull_color = 'green';
			}
		if ($row_track['speed']==0) {
			$the_bull = $GLOBALS['TRACK_2L'][$track_type];
			$bull_color = 'red';
			}
		} else {
		$the_bull = $GLOBALS['TRACK_2L'][$track_type];
		$bull_color = '#000000';
		}

	$cstip = htmlentities($row['callsign'], ENT_QUOTES); 
	$tip_str = $cstip; 

	// as of - 7/2/2013
	$the_class = "";
	$the_flag = $name . "_flag";

	$strike_ary = ( abs ( ( now() - strtotime ($row['updated'] ) ) ) <  $GLOBALS['TOLERANCE'] ) ? 
		array ( "", "") : 
		array ( "<strike>", "<strike>") ;
	$strike = $strike_ary[0];
	$updated = format_sb_date_2 ( $row['updated'] );

	$resp_cat = $un_stat_cats[$row['unit_id']];
	$the_time = $row['r_updated'];
	$the_callsign = ($track_type == 8) ? "999_" . $row['unit_id']: $row['callsign'];	//	9/6/13
	$temp = $row['un_status_id'] ;
	$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
	$theTabs = "";
	if(my_is_float($row['lat'])) {
		$lat = $row['lat'];
		$lng = $row['lng'];
		} else {
		$lat = get_variable('def_lat');
		$lng = get_variable('def_lng');
		}
	
// tab 1
		if (my_is_float($lat)) {										// position data? 4/29/09
		
			$temptype = $u_types[$row['type_id']];
			$temp_array[0] = $lat;
			$temp_array[1] = $lng;
			$temp_array[2] = addslashes(shorten($name, 48));
			$temp_array[3] = addslashes(shorten(str_replace($eols, " ", $row['unit_descr']), 256));
			$the_type = $temptype[0];																			// 1/1/09
			$toosmap = (!($internet))?				 								"" : "<A id='osmap_but' class='plain' style='float: none; color: #000000;' HREF='#' onClick = 'do_osmap({$temp_array[0]}, {$temp_array[1]}, {$row['unit_id']}, &quot;" . $temp_array[2] . "&quot;, &quot;" . $temp_array[3] . "&quot;, \"responder\");' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">OS Map</A>";

			$theTabs .= "<div class='infowin'><BR />";
			$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
			$theTabs .= '<div class="tabArea">';
			$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
			if($row_track) {
				$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab2\', 2, null, null);">Details</span>';
				}
			$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $lat . ',' . $lng . ');">Location</span>';
			$theTabs .= '</div>';
			$theTabs .= '<div class="contentwrapper">';
			
			$tab_1 = "<TABLE width='280px' style='height: 280px;'><TR><TD><TABLE width='98%'>";			
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row['name'], 48)) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . addslashes(shorten(str_replace($eols, " ", $row['description']), 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . addslashes($row['contact_name']). " Via: " . addslashes($row['contact_via']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date_2(strtotime($the_time)) . "</TD></TR>";		// 4/11/10
			if (array_key_exists($row['unit_id'], $assigns)) {
				$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['unit_id']] . "'>" . addslashes(shorten($assigns[$row['unit_id']], 20)) . "</A></TD></TR>";
				}
			$tab_1 .= "</TABLE></TD></TR></TABLE>";

// tab 2
		if ($row_track) {		// do all three tabs
			$tab_2 = "<TABLE width='280px' style='height: 280px;' ><TR><TD><TABLE width='98%'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_track['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_track['course'] . ", Speed:  " . $row_track['speed'] . ", Alt: " . $row_track['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $row_track['closest_city'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $row_track['status'] . "</TD></TR>";
			if (array_key_exists ('packet_date',$row_track ) ) {				// 7/2/2013
				$strike_ary = ( abs ( ( now() - strtotime ($row_track['packet_date'] ) ) ) <  $GLOBALS['TOLERANCE'] ) ? 
				array ( "", "") : 
				array ( "<strike>", "<strike>") ;		
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> {$strike_ary[0]}" . format_date($row_track['packet_date']) . "{$strike_ary[1]} </TD></TR></TABLE></TD></TR></TABLE>";
				}
			}	// end if ($row_track)
			
		$tab_3 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
		$tab_3 .= "<TABLE width='98%'>";
		$locale = get_variable('locale');	// 08/03/09
		switch($locale) { 
			case "0":
			$tab_3 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($lat, $lng) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
			break;
		
			case "1":
			$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($lat, $lng) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
			break;
		
			case "2":
			$coords =  $lat . "," . $lng;							// 8/12/09
			$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
			break;
		
			default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
			}
		$tab_3 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lat</TD><TD class='td_data' style='font-size: 80%;'>" . $lat . "</TD></TR>";
		$tab_3 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lng</TD><TD class='td_data' style='font-size: 80%;'>" . $lng . "</TD></TR>";
		$tab_3 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
		$tab_3 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
		$tab_3 .= "</TABLE></TD</TR></TABLE>";
			
		$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
		if($row_track) {
			$theTabs .= "<div class='content' id='content2' style = 'display: none;'>" . $tab_2 . "</div>";
			}
		$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_3 . "</div>";
		$theTabs .= "</div>";
		$theTabs .= "</div>";
		$theTabs .= "</div>";
		}

	$ret_arr[$i][0] = $name;
	$ret_arr[$i][1] = $handle;
	$ret_arr[$i][2] = $index;
	$ret_arr[$i][3] = $lat;
	$ret_arr[$i][4] = $lng;
	$ret_arr[$i][5] = $callsign;
	$ret_arr[$i][6] = $hide_unit;
	$ret_arr[$i][7] = $the_bg_color;
	$ret_arr[$i][8] = $the_text_color;
	$ret_arr[$i][9] = $tip;
	$ret_arr[$i][10] = $track_type;
	$ret_arr[$i][11] = $mail_link;
	$ret_arr[$i][12] = $ass_td;
	$ret_arr[$i][13] = $the_bull;
	$ret_arr[$i][14] = $bull_color;
	$ret_arr[$i][15] = htmlspecialchars($status);
	$ret_arr[$i][16] = shorten($updated, 10);
	$ret_arr[$i][17] = $row['unit_id'];
	$ret_arr[$i][18] = $the_color;	
	$ret_arr[$i][19] = $theTabs;	
	$ret_arr[$i][20] = $resp_cat;
	$ret_arr[$i][23] = $status_name;
	$ret_arr[$i][24] = intval($row['nr_assigned']);
	$ret_arr[$i][25] = $type;
	$ret_arr[$i][26] = $status_about;
	$ret_arr[$i][27] = $the_flag;
	$ret_arr[$i][28] = $row['excl_zone'];
	$ret_arr[$i][29] = $row['ring_fence'];
	$ret_arr[$i][30] = $flaginfo;
	$ret_arr[$i][31] = $units_assigned;
	$i++;
	}				// end  ==========  while() for RESPONDER ==========

$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
$ctrls_width = $col_width * .75;

$categories = array();
$categories = $curr_cats;
$cats_buttons = "<TABLE style='width: 200px;'><TR class='heading_2'><TH ALIGN='center'>" . get_text("Units") . "</TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label' ><form action='#'>";

if($units_ct > 0) {
	foreach($categories as $key => $value) {
		$cats_buttons .= "<DIV class='cat_button' onClick='set_fac_buttons(\"category\"); set_chkbox(\"" . $value . "\")'>" . $value . ": <input type=checkbox id='" . $value . "' onClick='set_buttons(\"category\"); set_chkbox(\"" . $value . "\")'/>&nbsp;&nbsp;&nbsp;</DIV>";
		}
		$all="RESP_ALL";
		$none="RESP_NONE";
		$cats_buttons .= "<DIV ID = 'RESP_ALL_BUTTON' class='cat_button' onClick='set_fac_buttons(\"all\"); set_chkbox(\"" . $all . "\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='" . $all . "' onClick='set_buttons(\"all\"); set_chkbox(\"" . $all . "\")'/></FONT></DIV>";
		$cats_buttons .= "<DIV ID = 'RESP_NONE_BUTTON' class='cat_button'  onClick='set_fac_buttons(\"none\"); set_chkbox(\"" . $none . "\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='" . $none . "' onClick='set_buttons(\"none\"); set_chkbox(\"" . $none . "\")'/></FONT></DIV>";
		$cats_buttons .= "<DIV ID = 'go_can' style='float:right; padding:2px;'><SPAN ID = 'go_button' onClick='do_go_button()' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: green;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);'>Next</SPAN>";
		$cats_buttons .= "<SPAN ID = 'can_button'  onClick='cancel_buttons()' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: red;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);''>Cancel</SPAN></DIV>";
		$cats_buttons .= "</form></TD></TR></TABLE>";
	} else {
	foreach($categories as $key => $value) {
		$cats_buttons .= "<DIV class='cat_button' STYLE='display: none;' onClick='set_fac_buttons(\"category\"); set_chkbox(\"" . $value . "\")'>" . $value . ": <input type=checkbox id='" . $value . "' onClick='set_buttons(\"category\"); set_chkbox(\"" . $value . "\")'/>&nbsp;&nbsp;&nbsp;</DIV>";
		}
		$all="RESP_ALL";
		$none="RESP_NONE";
		$cats_buttons .= "<DIV class='cat_button' style='color: red;'>None Defined ! </DIV>";
		$cats_buttons .= "<DIV ID = 'RESP_ALL_BUTTON' class='cat_button' STYLE='display: none;' onClick='set_buttons(\"all\"); set_chkbox(\"" . $all . "\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='" . $all . "' onClick='set_buttons(\"all\"); set_chkbox(\"" . $all . "\")'/></FONT></DIV>";
		$cats_buttons .= "<DIV ID = 'RESP_NONE_BUTTON' class='cat_button' STYLE='display: none;' onClick='set_buttons(\"none\"); set_chkbox(\"" . $none . "\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='" . $none . "' onClick='set_buttons(\"none\"); set_chkbox(\"" . $none . "\")'/></FONT></DIV>";
		$cats_buttons .= "</form></TD></TR></TABLE></DIV>";
	}


if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}

switch($sortby) {
	case 'icon':
		$sortval = 2;
		break;
	case 'handle':
		$sortval = 1;
		break;
	case 'mail':
		$sortval = 11;
		break;
	case 'incidents':
		$sortval = 24;
		break;
	case 'status':
		$sortval = 23;
		break;
	case 'sa':
		$sortval = 26;
		break;
	case 'm':
		$sortval = 13;
		break;
	case 'asof':
		$sortval = 16;
		break;
	default:
		$sortval = 2;
	}

if($units_ct > 0 ) {
	
	if((isset($ret_arr[0])) && ($ret_arr[0][22] == 0)) {
		$the_output = $ret_arr;
		} else {
		$the_arr = subval_sort($ret_arr, $sortval, $dd);
		$the_output = array();
		$z=1;
		foreach($the_arr as $val) {
			$the_output[$z] = $val;
			$z++;
			}
		}
	} else {
	$the_output[0][0] = 0;
	}
$the_output[0][21] = $cats_buttons;
$the_output[0][22] = $units_ct;	
$the_output[0][23] = $latest_id;

//dump($the_output);
print json_encode($the_output);
exit();
?>