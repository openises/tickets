<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
@session_start();
session_write_close();
$iw_width= "300px";					// map infowindow with
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$ret_arr = array();

$u_sb_indx = 0;	//	12/23/13
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
	

function is_ok_coord($inval) {
	return ((abs(floatval($inval) != 0.0)) && (floatval($inval) != $GLOBALS['NM_LAT_VAL']));
	}
	
//	Categories for Unit status

$assigns = array();					// 8/3/08
$tickets = array();					// ticket id's

$eols = array ("\r\n", "\n", "\r");

$status_vals = array();
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

$query = "SELECT *, r.updated AS `r_updated`,
	`r`.`status_updated` AS `status_updated`,
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
	WHERE `r`.`id` = " . $_GET['id'] . "";

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$units_ct = mysql_affected_rows();

$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = $mob_tracker = FALSE;

$utc = gmdate ("U");

// ===========================  begin major while() for RESPONDER ==========

while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$latitude = ($row['lat']) ? $row['lat'] : 0.999999;
	$longitude = ($row['lng']) ? $row['lng'] : 0.999999;

	$name = $row['name'];
	$index = $row['icon_str'];
	$track_type = get_remote_type($row);
	$callsign = ($track_type == 8) ? "999_" . $row['unit_id']: $row['callsign'];
	$hide_unit = ($row['hide']=="y")? "1" : "0" ;
	$fac_type_name = $row['un_type_name'];
	
	$type = $row['type_id'];

	$row_track = FALSE;
	if ($track_type > 0 ) {
		$do_legend = TRUE;
		$query = "SELECT *,packet_date AS `packet_date`, updated AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
			WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
		$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_track = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
		$aprs_updated = $row_track['updated'];
		$aprs_speed = $row_track['speed'];
		if (($row_track) && (my_is_float($row_track['latitude']))) {
			$latitude = $row_track['latitude'];  $longitude = $row_track['longitude'];
			$got_point = TRUE;
			}
		unset($result_tr);
		}

// NAME
	$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];
	$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
	$name = htmlentities($row['name'],ENT_QUOTES);
	$handle = htmlentities($row['handle'],ENT_QUOTES);

// DISPATCHES 3/16/09

	$units_assigned = 0;
	if(array_key_exists ($row['unit_id'] , $assigns_ary)) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
			WHERE `responder_id` = '{$row['unit_id']}' AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";

		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$units_assigned = mysql_num_rows($result_as);
		}		// end if(array_key_exists ()

	switch ($units_assigned) {		
		case 0:
			$theString = "na";
			$ass_td = str_pad($theString, 160);
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
			$theString = shorten($row_assign['scope'], 20);
			$ass_td = str_pad($theString, 160);
			break;
		default:							// multiples
			$theString = $units_assigned;
			$ass_td = str_pad($theString, 160);
			break;
		}						// end switch(($units_assigned))

// STATUS
	$status = get_status_sel($row['unit_id'], $row['un_status_id'], "u");
	$status_name = $status_vals[$row['un_status_id']];

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

// as of
	$the_class = "";
	$the_flag = $name . "_flag";

	$strike_ary = ( abs ( ( now() - strtotime ($row['updated'] ) ) ) <  $GLOBALS['TOLERANCE'] ) ? 
		array ( "", "") : 
		array ( "<strike>", "<strike>") ;
	$strike = $strike_ary[0];
	$updated = format_sb_date_2 ( $row['updated'] );

	$the_time = $row['r_updated'];
	$tofac = (is_guest())? 													"" : "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&dispfac=true&id=" . $row['unit_id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 10/6/09
	$todisp = ((is_guest()) || (!(can_do_dispatch($row))))?					"" : "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&disp=true&id=" . $row['unit_id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;&nbsp;";	// 08/8/02, 9/19/09
	$toedit = (!(can_edit()))?				 								"" : "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&edit=true&id=" . $row['unit_id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 5/11/10
	$the_callsign = ($track_type == 8) ? "999_" . $row['unit_id']: $row['callsign'];	//	9/6/13
	$totrack  = ((intval($row['mobile'])==0) || (($track_type != 8) && (empty($row['callsign'])))) ? "" : "&nbsp;&nbsp;<SPAN CLASS = 'span_link' onClick = do_track('" .$the_callsign  . "');><U>Tracks</U></SPAN>" ;	//	9/6/13
	$to_home = (is_guest() || (!(is_ok_coord($row['lat'])))) ?			 	"" : "<SPAN CLASS = 'span_link' onclick = 'go_home({$row['unit_id']});'>To quarters</SPAN>";
	$to_log = (is_guest()) ? "" : "<SPAN CLASS = 'span_link' onclick = 'unit_log({$row['unit_id']});'><U>Log</U></SPAN>";	//	9/10/13
	$temp = $row['un_status_id'] ;
	$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";
	$theTabs = "";
// tab 1
		if (my_is_float($row['lat'])) {
			$temptype = $u_types[$row['type_id']];
			$the_type = $temptype[0];

			$theTabs .= "<div class='infowin'><BR />";
			$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
			$theTabs .= '<div class="tabArea">';
			$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
			if($row_track) {
				$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab2\', 2, null, null);">Details</span>';
				}
			$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row['lat'] . ',' . $row['lng'] . ');">Location</span>';
			$theTabs .= '</div>';
			$theTabs .= '<div class="contentwrapper">';
			
			$tab_1 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD><TABLE>";			
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row['name'], 48)) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . addslashes(shorten(str_replace($eols, " ", $row['description']), 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . addslashes($row['contact_name']). " Via: " . addslashes($row['contact_via']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date_2(strtotime($the_time)) . "</TD></TR>";		// 4/11/10
			if (array_key_exists($row['unit_id'], $assigns)) {
				$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['unit_id']] . "'>" . addslashes(shorten($assigns[$row['unit_id']], 20)) . "</A></TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $tofac . $todisp . $totrack . $toedit . $to_log ."&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&id=" . $row['unit_id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE></TD></TR></TABLE>";


// tab 2
		if ($row_track) {		// do all three tabs
			$tab_2 = "<TABLE width='{$iw_width}' style='height: 280px;' ><TR><TD><TABLE>";
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
			
		$tab_3 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD>";
		$tab_3 .= "<TABLE width='100%'>";
		$locale = get_variable('locale');	// 08/03/09
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
	$ret_arr[1] = $status;
	}				// end  ==========  while() for RESPONDER ==========

dump($ret_arr);
//print json_encode($the_output);
exit();
?>