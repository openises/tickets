<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
//if($_GET['q'] != $_SESSION['id']) {
//	exit();
//	}

$iw_width= "300px";
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$ret_arr = array();
$curr_cats = get_category_butts();
$cat_sess_stat = get_session_status($curr_cats);
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$responderUnavail = intval(get_mdb_variable('tickets_status_unavailable'));
$responderAvail = intval(get_mdb_variable('tickets_status_available'));
$memberStatusAvailVal = intval(get_mdb_variable('member_status_available'));
$enforceMemberStatus = intval(get_mdb_variable('enforce_status'));
$removeStatusSelect = intval(get_mdb_variable('no_status_select'));
$delta = (get_variable('delta_mins') != "") ? get_variable('delta_mins') : 0;
$now = time() - ($delta*60);
$sortby = (!(array_key_exists('sort', $_GET))) ? 'icon' : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$def_lat = get_variable('def_lat');
$def_lng = get_variable('def_lng');
$locale = get_variable('locale');
$u_sb_indx = 0;
$u_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = {$the_row['unit_id']}";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_temp = stripslashes_deep(mysql_fetch_array($result_temp))) {
		if (!(is_date($row_temp['clear']))) {
			unset ($result_temp, $row_temp); 
			return FALSE;
			}
		}
	unset ($result_temp, $row_temp); 
	return TRUE;
	}
	
function unit_cat($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_types` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_array($result));
	return $row['name'];
	}

function is_ok_coord($inval) {
	return ((abs(floatval($inval) != 0.0)) && (floatval($inval) != $GLOBALS['NM_LAT_VAL']));
	}

//	Categories for Unit status

$categories = array();
$categories = $curr_cats;
$assigns = array();
$tickets = array();

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
		WHERE (`t`.`status` = '{$GLOBALS['STATUS_OPEN']}' OR `t`.`status` = '{$GLOBALS['STATUS_SCHEDULED']}') AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$assigns_ary[$row['responder_id']] = TRUE;
	}
$numAssigns = count($assigns_ary);	
$order_values = array(1 => "`nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC", 2 => "`type_descr` ASC, `handle` ASC",  3 => "`stat_descr` ASC, `handle` ASC" , 4 => "`handle` ASC");

if ((array_key_exists ('order' , $_POST)) && (isset($_POST['order'])))	{$_SESSION['unit_flag_2'] =  $_POST['order'];}
elseif (empty ($_SESSION['unit_flag_2'])) 	{$_SESSION['unit_flag_2'] = 1;}

$order_str = $order_values[1];
$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
if(count($al_groups) == 0) {	
	$where2 = "WHERE `a`.`type` = 2";
	} else {	
	if(!isset($curr_viewed)) {	
		$x=0;
		$where2 = "WHERE (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;
		$where2 = "WHERE (";
		foreach($curr_viewed as $grp) {
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
	{$where2}  GROUP BY unit_id ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC ";

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$units_ct = mysql_affected_rows();			// 1/4/10
if ($units_ct != 0){
	$checked = array ("", "", "", "");
	$checked[$_SESSION['unit_flag_2']] = " CHECKED";
	}

$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = $mob_tracker = $followmee = FALSE;

$utc = gmdate ("U");

// ===========================  begin major while() for RESPONDER ==========

$chgd_unit = $_SESSION['unit_flag_1'];
$_SESSION['unit_flag_1'] = 0;
$i = 1;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$resp_gps = get_allocates(2, $row['unit_id']);
	$the_color = ($row['mobile']=="1")? 4 : 0;
	$grp_names = "Groups Assigned: ";
	$y=0;	//	6/10/11
	foreach($resp_gps as $value) {
		$counter = (count($resp_gps) > ($y+1)) ? ", " : "";
		$grp_names .= get_groupname($value);
		$grp_names .= $counter;
		$y++;
		}

	$tip =  addslashes($grp_names . " / " . htmlentities($row['name'],ENT_QUOTES));
	$latitude = ($row['lat']) ? $row['lat'] : $def_lat;	
	$longitude = ($row['lng']) ? $row['lng'] : $def_lng;
	$got_point = FALSE;

	$index = $row['icon_str'];	// 4/27/11
	$track_type = get_remote_type($row) ;
	$callsign = ($track_type == 8) ? "999_" . $row['unit_id']: $row['callsign'];
	$hide_unit = ($row['hide']=="y")? "1" : "0" ;
	$fac_type_name = $row['un_type_name'];
	
	$type = $row['icon'];

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

	$the_bull = "";
	$update_error = strtotime('now - 6 hours');
// NAME
	$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];
	$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
	$tempname = explode("/", $row['name']);
	$name = htmlentities(shorten($tempname[0], 14),ENT_QUOTES);
	$handle = htmlentities($row['handle'],ENT_QUOTES);

// MAIL						
	if ((!is_guest()) && (is_email($row['contact_via']) || $row["smsg_id"] !="" || is_twitter($row['contact_via']))) {
		$mail_link = $row['contact_via'] . $row['smsg_id'];
		} else {
		$mail_link = "";
		}

// STATUS
	if($useMdb == "1" && $useMdbStatus == "1" && get_member_count($row['unit_id']) == 1) {
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $row['unit_id'];
		$result2 = mysql_query($query2);
		if($result && mysql_num_rows($result2) == 1) {
			$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
			$memberID = $row2['member_id'];
			} else {
			$memberID = 0;
			}
		} else {
		$memberID = 0;
		}

	if($memberID != 0) {
		$query_updated = "SELECT `id`, `_on` FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $memberID;
		$result_updated = mysql_query($query_updated);
		$row_updated = stripslashes_deep(mysql_fetch_assoc($result_updated));
		$memUpdated = strtotime($row_updated['_on']);
		$responderStatusUpdated = strtotime($row['status_updated']);
		$member_status = (($useMdb == "1" && $useMdbStatus && "1") && (get_member_count($row['unit_id']) == 1) && ($memberID != 0)) ? get_member_status($memberID) : 0;
		if($member_status == $memberStatusAvailVal) {
			$doChange = false; 
			} else {
			$doChange = true;
			}
		} else {
		$memUpdated = 0;
		$responderStatusUpdated = 0;
		$doChange = false;
		}

	if($doChange && $responderUnavail != 0 && $memUpdated > $responderStatusUpdated) {
		$changeStatus = true;
		} else if($doChange && $responderUnavail != 0 && $enforceMemberStatus == 1) {
		$changeStatus = true;
		} else {
		$changeStatus = false;
		}		
	
	if(!$changeStatus) {
		$status = (array_key_exists($row['un_status_id'], $validStatuses)) ? get_status_sel($row['unit_id'], $row['un_status_id'], "u") : "Status Error";
		$status_name = (array_key_exists($row['un_status_id'], $validStatuses)) ? $status_vals[$row['un_status_id']] : "Status Error" ;
		$status_id = $row['un_status_id'];
		$statusTemp = ($row['status_about'] != "") ? addslashes($row['status_about']): "";
		$status_about = $statusTemp;
		$noSel = 0;
		} else {
		$query_upd = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= ";
		$query_upd .= quote_smart($responderUnavail) ;
		$query_upd .= ", `updated` = " . quote_smart(mysql_format_date($now));
		$query_upd .= ", `status_updated` = " . quote_smart(mysql_format_date($now));
		$query_upd .= ", `user_id` = " . $_SESSION['user_id'];
		$query_upd .= " WHERE `id` = ";
		$query_upd .= quote_smart($row['unit_id']);
		$query_upd .=" LIMIT 1";
		$result_upd = mysql_query($query_upd);
		$status = (array_key_exists($row['un_status_id'], $validStatuses)) ? get_status_sel($row['unit_id'], $responderUnavail, "u") : "Status Error";
		$status_name = (array_key_exists($row['un_status_id'], $validStatuses)) ? $status_vals[$row['un_status_id']] : "Status Error" ;
		$status_id = $responderUnavail;
		$status_about = "Status Changed as Assigned Member is not available";
		$noSel = ($removeStatusSelect == 1 && $enforceMemberStatus == 1) ? 1 : 0;
		}

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
	$lat = $row['lat'];
	$lng = $row['lng'];
	$theName = (is_array(get_mdb_names($row['unit_id']))) ? implode(" | ", get_mdb_names($row['unit_id'])) : get_mdb_names($row['unit_id']);
	$ret_arr[$i][0] = htmlentities($theName,ENT_QUOTES);
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
	$ret_arr[$i][12] = $name;
	$ret_arr[$i][13] = $the_bull;
	$ret_arr[$i][14] = $bull_color;
	$ret_arr[$i][15] = $status_id;
	$ret_arr[$i][16] = $updated;
	$ret_arr[$i][17] = $row['unit_id'];
	$ret_arr[$i][18] = $the_color;	
	$ret_arr[$i][20] = $resp_cat;
	$ret_arr[$i][23] = $status_name;
//	$ret_arr[$i][24] = $name;
	$ret_arr[$i][25] = $type;
	$ret_arr[$i][26] = htmlentities($status_about, ENT_QUOTES);
	$ret_arr[$i][27] = $the_flag;
	$ret_arr[$i][28] = $row['excl_zone'];
	$ret_arr[$i][29] = $row['ring_fence'];
	$ret_arr[$i][30] = $noSel;
	$i++;
	}				// end  ==========  while() for RESPONDER ==========

$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
$ctrls_width = $col_width * .75;

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
	case 'name':
		$sortval = 0;
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
$the_output[0][22] = $units_ct;	
$the_output[0][23] = $latest_id;

//dump($the_output);
print json_encode($the_output);
exit();
?>
