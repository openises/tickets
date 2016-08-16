<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
set_time_limit(0);
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

function valid_status($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		return true;
		} else {
		return false;
		}
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
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
		WHERE `t`.`status` = '{$GLOBALS['STATUS_OPEN']}' AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";
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
	WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `unit_id` AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )) AS `nr_assigned` 
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

$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = $mob_tracker = $followmee = FALSE;		//7/23/09

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
	if ((!is_guest()) && (is_email($row['contact_via']) || $row["smsg_id"] !="" || is_twitter($row['contact_via']))) {		// 2/1/10
		$mail_link = $row['contact_via'] . $row['smsg_id'];
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
	$status = (valid_status($row['un_status_id'])) ? get_status_sel($row['unit_id'], $row['un_status_id'], "u") : "Status Error";		// status
	$status_name = (valid_status($row['un_status_id'])) ? $status_vals[$row['un_status_id']] : "Status Error" ;
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
	$updated = format_sb_date_2($row['updated']);

	$resp_cat = $un_stat_cats[$row['unit_id']];
	$the_time = $row['r_updated'];
	$tofac = (is_guest())? 													"" : "<A id='tofac_" . $row['unit_id'] . "' CLASS='plain' style='float: none; color: #000000;' HREF='{$_SESSION['unitsfile']}?func=responder&view=true&dispfac=true&id=" . $row['unit_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">To Facility</A>";
	$todisp = ((is_guest()) || (!(can_do_dispatch($row))))?					"" : "<A id='disp_" . $row['unit_id'] . "' CLASS='plain' style='float: none; color: #000000;' HREF='{$_SESSION['unitsfile']}?func=responder&view=true&disp=true&id=" . $row['unit_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">Dispatch</A>";
	$toedit = (!(can_edit()))?				 								"" : "<A id='edit_" . $row['unit_id'] . "' CLASS='plain' style='float: none; color: #000000;' HREF='{$_SESSION['unitsfile']}?func=responder&edit=true&id=" . $row['unit_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">Edit</A>";
	$the_callsign = ($track_type == 8) ? "999_" . $row['unit_id']: $row['callsign'];	//	9/6/13
	$totrack  = ((intval($row['mobile'])==0) || (($track_type != 8) && (empty($row['callsign'])))) ? "" : "&nbsp;&nbsp;<SPAN id='tracks_" . $row['unit_id'] . "' CLASS='plain' style='float: none; color: #000000;' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\" onClick = do_track('" .$the_callsign  . "');>Tracks</SPAN>" ;	//	9/6/13
	$to_home = (is_guest() || (!(is_ok_coord($row['lat'])))) ?			 	"" : "<SPAN id='home_" . $row['unit_id'] . "' CLASS='plain' style='float: none; color: #000000;' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\" onclick = 'go_home({$i});'>To quarters</SPAN>";
	$to_log = (is_guest()) ? "" : "<SPAN id='log_" . $row['unit_id'] . "' CLASS='plain' style='float: none; color: #000000;' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\" onclick = 'unit_log({$row['unit_id']});'>Log</SPAN>";	//	9/10/13
	$temp = $row['un_status_id'] ;
	$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
	$theTabs = "";
	$lat = $row['lat'];
	$lng = $row['lng'];
	$locale = get_variable('locale');	// 08/03/09	

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
	$ret_arr[$i][15] = $status_id;
	$ret_arr[$i][16] = $updated;
	$ret_arr[$i][17] = $row['unit_id'];
	$ret_arr[$i][18] = $the_color;	
	$ret_arr[$i][20] = $resp_cat;
	$ret_arr[$i][23] = $status_name;
	$ret_arr[$i][24] = intval($row['nr_assigned']);
	$ret_arr[$i][25] = $type;
	$ret_arr[$i][26] = htmlentities($status_about, ENT_QUOTES);
	$ret_arr[$i][27] = $the_flag;
	$ret_arr[$i][28] = $row['excl_zone'];
	$ret_arr[$i][29] = $row['ring_fence'];	
	$i++;
	}				// end  ==========  while() for RESPONDER ==========

$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
$ctrls_width = $col_width * .75;

$cats_buttons = "<TABLE style='width: 200px;'><TR class='heading_2'><TH ALIGN='center'>" . get_text("Units") . "</TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label' ><form action='#'>";

if($units_ct > 0) {
	foreach($categories as $key => $value) {
		$cats_buttons .= "<DIV class='cat_button' onClick='set_buttons(\"category\"); set_chkbox(\"" . $value . "\")'>" . $value . ": <input type=checkbox id='" . $value . "' onClick='set_buttons(\"category\"); set_chkbox(\"" . $value . "\")'/>&nbsp;&nbsp;&nbsp;</DIV>";
		}
		$all="RESP_ALL";
		$none="RESP_NONE";
		$cats_buttons .= "<DIV ID = 'RESP_ALL_BUTTON' class='cat_button' onClick='set_buttons(\"all\"); set_chkbox(\"" . $all . "\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='" . $all . "' onClick='set_buttons(\"all\"); set_chkbox(\"" . $all . "\")'/></FONT></DIV>";
		$cats_buttons .= "<DIV ID = 'RESP_NONE_BUTTON' class='cat_button'  onClick='set_buttons(\"none\"); set_chkbox(\"" . $none . "\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='" . $none . "' onClick='set_buttons(\"none\"); set_chkbox(\"" . $none . "\")'/></FONT></DIV>";
		$cats_buttons .= "<DIV ID = 'go_can' style='float:right; padding:2px;'><SPAN ID = 'go_button' onClick='do_go_button()' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: green;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);'>Next</SPAN>";
		$cats_buttons .= "<SPAN ID = 'can_button'  onClick='cancel_buttons()' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: red;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);''>Cancel</SPAN></DIV>";
		$cats_buttons .= "</form></TD></TR></TABLE>";
	} else {
	foreach($categories as $key => $value) {
		$cats_buttons .= "<DIV class='cat_button' STYLE='display: none;' onClick='set_buttons(\"category\"); set_chkbox(\"" . $value . "\")'>" . $value . ": <input type=checkbox id='" . $value . "' onClick='set_buttons(\"category\"); set_chkbox(\"" . $value . "\")'/>&nbsp;&nbsp;&nbsp;</DIV>";
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
