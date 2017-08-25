<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}

$ret_arr = array();
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}
unset($result);

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
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
		WHERE `t`.`status` = '{$GLOBALS['STATUS_OPEN']}' AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$assigns_ary[$row['responder_id']] = TRUE;
	}

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
	WHERE `r`.`id` = " . $id;

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$units_ct = mysql_affected_rows();			// 1/4/10

$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = $mob_tracker = FALSE;		//7/23/09

$utc = gmdate ("U");				// 3/25/09

$row = stripslashes_deep(mysql_fetch_assoc($result));
$latitude = ($row['lat']) ? $row['lat'] : get_variable('def_lat');		// 7/18/10		
$longitude = ($row['lng']) ? $row['lng'] : get_variable('def_lng');		// 7/18/10

$got_point = FALSE;

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
$tempname = explode("/", $row['name']);
$name = htmlentities($tempname[0],ENT_QUOTES);
$handle = htmlentities($row['handle'],ENT_QUOTES);

// MAIL						
if ((!is_guest()) && is_email($row['contact_via'])) {		// 2/1/10
	$mail_link = $row['contact_via'];
	} else {
	$mail_link = "";
	}

// DISPATCHES 3/16/09

$units_assigned = 0;
if(array_key_exists ($row['unit_id'] , $assigns_ary)) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
		WHERE `responder_id` = '{$row['unit_id']}' AND `t`.`status`='{$GLOBALS['STATUS_OPEN']}' AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";	//	03/26/15
	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$units_assigned = mysql_num_rows($result_as);
	}		// end if(array_key_exists ()

switch ($units_assigned) {		
	case 0:
		$ass_td = " ";
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
		$ass_td = shorten($row_assign['scope'], 20);
		break;
	default:							// multiples
		$ass_td = $units_assigned;
		break;
	}						// end switch(($units_assigned))

// STATUS
$status = get_status_sel($row['unit_id'], $row['un_status_id'], "u");		// status
$status_name = $status_vals[$row['un_status_id']];
$status_id = $row['un_status_id'];
$statusTemp = ($row['status_about'] != "") ? addslashes($row['status_about']): "";
$status_about = $statusTemp;

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
$locale = get_variable('locale');	// 08/03/09	
// tab 1
if (my_is_float($lat)) {										// position data? 4/29/09

	$temptype = $u_types[$row['type_id']];
	$temp_array[0] = $lat;
	$temp_array[1] = $lng;
	$temp_array[2] = addslashes(shorten($name, 48));
	$temp_array[3] = addslashes(shorten(str_replace($eols, " ", $row['unit_descr']), 256));
	$the_type = $temptype[0];																			// 1/1/09
	$toosmap = ((!($internet)) || ($locale != 1))? "" : "<A id='osmap_but' class='plain' style='float: none; color: #000000;' HREF='#' onClick = 'do_osmap({$temp_array[0]}, {$temp_array[1]}, {$row['unit_id']}, &quot;" . $temp_array[2] . "&quot;, &quot;" . $temp_array[3] . "&quot;, \"responder\");' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">OS Map</A>";

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
	
	$tab_1 = "<TABLE width='280px' style='height: auto;'><TR><TD><TABLE width='98%'>";			
	$tab_1 .= "<TR CLASS='even'><TD CLASS='td_data text text_center' COLSPAN=2><B>" . htmlentities(shorten($row['name'], 48),ENT_QUOTES) . "</B> - " . $the_type . "</TD></TR>";
	$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>Description:</TD><TD CLASS='td_data text text_left'>" . htmlentities(shorten(str_replace($eols, " ", $row['description']), 32), ENT_QUOTES) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text text_left'>Status:</TD><TD CLASS='td_data text text_left'>" . $the_status . " </TD></TR>";
	$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>Contact:</TD><TD CLASS='td_data text text_left'>" . addslashes($row['contact_name']). " Via: " . addslashes($row['contact_via']) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text text_left'>As of:</TD><TD CLASS='td_data text text_left'>" . format_date_2(strtotime($the_time)) . "</TD></TR>";		// 4/11/10
	if ($units_assigned > 0) {
		$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['unit_id']] . "'>" . $ass_td . "</A></TD></TR>";
		}
	$tab_1 .= "</TABLE></TD></TR>";
	$tab_1 .= "</TABLE>";


// tab 2
	if ($row_track) {		// do all three tabs
		$tab_2 = "<TABLE width='280px' style='height: 280px;' ><TR><TD><TABLE width='98%'>";
		$tab_2 .="<TR CLASS='even'><TD CLASS='td_data text text_center' COLSPAN=2><B>" . $row_track['source'] . "</B></TD></TR>";
		$tab_2 .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>Course: </TD><TD CLASS='td_data text text_left'>" . $row_track['course'] . ", Speed:  " . $row_track['speed'] . ", Alt: " . $row_track['altitude'] . "</TD></TR>";
		$tab_2 .= "<TR CLASS='even'><TD CLASS='td_label text text_left'>Closest city: </TD><TD CLASS='td_data text text_left'>" . $row_track['closest_city'] . "</TD></TR>";
		$tab_2 .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>Status: </TD><TD CLASS='td_data text text_left'>" . $row_track['status'] . "</TD></TR>";
		if (array_key_exists ('packet_date',$row_track ) ) {				// 7/2/2013
			$strike_ary = ( abs ( ( now() - strtotime ($row_track['packet_date'] ) ) ) <  $GLOBALS['TOLERANCE'] ) ? 
			array ( "", "") : 
			array ( "<strike>", "<strike>") ;		
			$tab_2 .= "<TR CLASS='even'><TD CLASS='td_label text text_left'>As of: </TD><TD CLASS='td_data text text_left'> {$strike_ary[0]}" . format_date($row_track['packet_date']) . "{$strike_ary[1]} </TD></TR></TABLE></TD></TR></TABLE>";
			}
		}	// end if ($row_track)
		
	$tab_3 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
	$tab_3 .= "<TABLE width='98%'>";

	switch($locale) { 
		case "0":
		$tab_3 .= "<TR CLASS='odd'><TD class='td_label text text_left' ALIGN='left'>USNG:</TD><TD CLASS='td_data text text_left'>" . LLtoUSNG($lat, $lng) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
		break;

		case "1":
		$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label text text_left' ALIGN='left'>OSGB:</TD><TD CLASS='td_data text text_left'>" . LLtoOSGB($lat, $lng) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
		break;

		case "2":
		$coords =  $lat . "," . $lng;							// 8/12/09
		$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label text text_left' ALIGN='left'>UTM:</TD><TD CLASS='td_data text text_left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
	$tab_3 .= "<TR><TD class='td_label text text_left'>Lat</TD><TD class='td_data text text_left'>" . $lat . "</TD></TR>";
	$tab_3 .= "<TR><TD class='td_label text text_left'>Lng</TD><TD class='td_data text text_left'>" . $lng . "</TD></TR>";
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
$ret_arr[0] = $theTabs;	

print json_encode($ret_arr);
exit();
?>