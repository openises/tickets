<?php
@session_start();
session_write_close();
require_once('../incs/functions.inc.php');
$_GET = stripslashes_deep($_GET);
extract($_GET);
$sortby_distance = TRUE;
$return_array = array();
$unit_array = array();
$status_array = array();
$sortby = (array_key_exists('sortby', $_GET)) ? $_GET['sortby'] : "distance";
$sortdir = (array_key_exists('dir', $_GET)) ? $_GET['dir'] : "ASC";
$capabilities = (array_key_exists('searchstring', $_GET)) ? $_GET['searchstring'] : "";

$u_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);
	}

$icons = $GLOBALS['icons'];
$sm_icons = $GLOBALS['sm_icons'];
$searchtype = "OR";

function search_capabilities($member_id, $searchstring) {
	if($searchstring == "") {return TRUE;}
	$theArray =  array();
	$key = 1;
	
	$query = "SELECT
		`tp`.`package_name` AS `training_package_name`,
		`a`.`completed` AS `completed`,
		`a`.`refresh_due` AS `refresh_due`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` `tp` ON ( `a`.`skill_id` = `tp`.`id` ) 	
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 1 AND `refresh_due` > NOW() ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['training_package_name'] . " | " . $row['refresh_due'];
			$theArray[$key] = $value;
			$key++;
			}
		}
	
	$query = "SELECT
		`ct`.`name` AS `capability_name`
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ct` ON ( `a`.`skill_id` = `ct`.`id` ) 
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 2 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['capability_name'];
			$theArray[$key] = $value;
			$key++;
			}
		}
		
	$query = "SELECT
		`et`.`equipment_name` AS `equipment_name`,	
		`et`.`serial` AS `equipment_serial`	
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]equipment_types` `et` ON ( `a`.`skill_id` = `et`.`id` ) 		
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 3 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['equipment_name']. " - " . $row['equipment_serial'];
			$theArray[$key] = $value;
			$key++;
			}
		}
		
	$query = "SELECT
		`cl`.`clothing_item` AS `clothing_item`,
		`cl`.`size` AS `size`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a` 
		LEFT JOIN `$GLOBALS[mysql_prefix]clothing_types` `cl` ON ( `a`.`skill_id` = `cl`.`id` ) 
		WHERE `a`.`member_id` = '$member_id' AND `a`.`skill_type` = 5 ORDER BY `a`.`member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['clothing_item']. " - " . $row['size'];
			$theArray[$key] = $value;
			$key++;
			}	
		}
	$counter = 0;
	foreach ($theArray AS $k => $v) {
		if(stripos($v, $searchstring) !== FALSE){
			$counter++;
			}
		}
	$theReturn = ($counter > 0) ? TRUE : FALSE;
	return $theReturn;
	}
	
function search_resp_capabilities($responder_id, $searchstring) {
	if($searchstring == "") {return TRUE;}
	$theArray =  array();
	$key = 1;
	
	$query = "SELECT `capab` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $responder_id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$value = $row['capab'];
			$theArray[$key] = $value;
			$key++;
			}
		}
		
	$counter = 0;
	foreach ($theArray AS $k => $v) {
		if(stripos($v, $searchstring) !== FALSE){
			$counter++;
			}
		}
	$theReturn = ($counter > 0) ? TRUE : FALSE;
	return $theReturn;
	}

function get_icon_legend (){
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle` ASC, `name` ASC";
	$result = mysql_query($query);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_data = $u_types[$row['type']];
		$print .= "\t\t" .$type_data[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$type_data[1]] . "' BORDER=0 />&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {
	if(my_is_float($lat1) && my_is_float($lon1) && my_is_float($lat2) && my_is_float($lon2)) {
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344); 
			} else if ($unit == "N") {
			return ($miles * 0.8684);
			} else {
			return $miles;
			}
		} else {
		return 0;
		}
	}
	
function subval_sort($a,$subkey, $dd) {
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

function get_assigns($id) {
	$dispatches_disp = array();										// unit id to ticket descr	- 5/23/09
	$dispatches_act = array();										// actuals
	$query = "SELECT *, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,  `t`.`scope` AS `theticket`,
		`r`.`id` AS `theunit_id` 
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";
	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if(!(empty($row['theunit_id']))) {
			$dispatches_act[$row['theunit_id']] = (empty($row['clear']))? $row['ticket_id']:"";

			if ($row['multi']==1) {
				$dispatches_disp[$row['theunit_id']] = "**";
				} else {
				$dispatches_disp[$row['theunit_id']] = (empty($row['clear']))? $row['theticket']:"";
				}
			}
		}
	}
	
function get_ticket($id) {
	$query = "SELECT *,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		UNIX_TIMESTAMP(booked_date) AS booked_date,		
		UNIX_TIMESTAMP(date) AS date,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`lat` AS `rf_lat`,
		`rf`.`lng` AS `rf_lng`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` 
		FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`={$id} LIMIT 1";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row;
	}
	
function get_unit_icon_id($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if($result) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['icon'];
		} else {
		return false;
		}
	}
	
function get_unit_icon_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if($result) {
		$row = stripslashes_deep(mysql_fetch_associ($result));
		return $row['name'];
		} else {
		return "Unk";
		}
	}

function get_assigned_td($unit_id, $on_click = "") {		// returns td string - 3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
		WHERE `responder_id` = '{$unit_id}' AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";	//	5/4/11
	
	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ( mysql_num_rows($result_as) == 0) {
		unset($result_as); return "<SPAN>---------</SPAN>";
		} else {		
		$row_assign = stripslashes_deep(mysql_fetch_assoc($result_as)) ;
		unset($result_as);
		$tip = str_replace ( "'", "`",    ("{$row_assign['contact']}/{$row_assign['street']}/{$row_assign['city']}/{$row_assign['phone']}/{$row_assign['scope']}   "));
	
		switch($row_assign['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 							$severityclass='severity_normal'; break;
			}
	
		switch (mysql_affected_rows()) {		// 8/30/10
			case 0:
				$the_disp_stat="";
				break;			
			case 1:
				$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
				break;
			default:							// multiples
			    $the_disp_stat = "<SPAN CLASS='disp_stat'>&nbsp;" . mysql_affected_rows() . "&nbsp;</SPAN>&nbsp;";
			    break;
			}						// end switch()
		$ass_td = "<SPAN onMouseover=\"Tip('" . $tip . "')\" onmouseout=\"UnTip();\" CLASS='" . $severityclass . "'  STYLE = 'white-space:nowrap;'>" . $the_disp_stat . " " . shorten($row_assign['scope'], 24) . "</SPAN>";
		return $ass_td;
		}		// end else
	}		// end function get_assigned_td()	

function get_cd_str($in_row, $ticket_id) {
	global $unit_id;
	$return = "";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE  `ticket_id` = " . $ticket_id . " AND (`responder_id`= " . $in_row['unit_id'] . ") AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) LIMIT 1;";
	$result = mysql_query($query);
	if(mysql_num_rows($result)==1) {
		$return = " CHECKED DISABLED ";
		}
	if (($unit_id != "") && ((mysql_affected_rows()!=1) || ((mysql_affected_rows()==1) && (intval($in_row['multi'])==1)))) { // 12/18/10 - Checkbox checked here individual unit seleted.
		$return = " CHECKED ";
		}
	if (intval($in_row['dispatch'])==2) {
		$return = " DISABLED ";
		}
	if (intval($in_row['multi'])==1) {
		$return = "";
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id`= " . $in_row['unit_id'] . " AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) LIMIT 1;";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result)==1) {
		$return = " DISABLED ";
		} else {
		$return = "";
		}
	return $return;
	}
	
function is_assigned_to($tick_id, $resp_id) {
	$query = "SELECT `ticket_id`, `responder_id` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $tick_id . " AND `responder_id` = " . $resp_id . " LIMIT 1";
	$result = mysql_query($query);
	if($result) {
		return TRUE;
		} else {
		return FALSE;
		}
	}

function have_position($theLat, $theLng) {
	if($theLat != $GLOBALS['NM_LAT_VAL'] && $theLng != $GLOBALS['NM_LAT_VAL']) {
		return TRUE;
		} else {
		return FALSE;
		}
	}
	
// Main list

switch (intval(trim(get_variable('locale')))) {
	case 0:
		$nm_to_what = 1.1515;
		$capt = "mi";
		break;
	case 1:
		$nm_to_what = 1.1515;
		$capt = "mi";
		break;
	case 2:
		$nm_to_what = 1.1515*1.609344;
		$capt = "km";
		break;
	default:
		$nm_to_what = 1.1515*1.609344;
		$capt = "km";
		break;
		}

$query = "SELECT *, UNIX_TIMESTAMP(problemstart) AS problemstart, UNIX_TIMESTAMP(problemend) AS problemend, `scope` AS `scope` 
	FROM `$GLOBALS[mysql_prefix]ticket` 
	WHERE `id`= " . $ticket_id . " LIMIT 1;";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_affected_rows()==1) {
	$row = stripslashes_deep(mysql_fetch_array($result));
	$latitude = $row['lat'];
	$longitude = $row['lng'];
	$problemstart = $row['problemstart'];
	$problemend = $row['problemend'];
	$scope = $row['scope'];
	unset($result);
	}

$where = (empty($unit_id))? "" : " AND `r`.`id` = " . $unit_id;
$al_groups = $_SESSION['user_groups'];

$x=0;	
$where3 = "AND (";	
foreach($al_groups as $grp) {
	$where4 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
	$where3 .= "`a`.`group` = '{$grp}'";
	$where3 .= $where4;
	$x++;
	}

$where3 .= " AND `a`.`type` = 2";			

$the_ticket = get_ticket($ticket_id);
$latitude = $the_ticket['lat'];
$longitude = $the_ticket['lng'];

$order = (($sortby_distance)&& (have_position($latitude, $longitude)))? "ORDER BY `distance` ASC ": "ORDER BY `dispatch` ASC, `calls_assigned` ASC, `handle` ASC, `unit_name` ASC, `unit_id` ASC";
				
$query = "(SELECT *, `r`.`updated` AS `unit_updated`, `r`.`handle` AS `unit_handle`,
	`r`.`name` AS `unit_name`, `t`.`name` AS `type_name`, `r`.`type` AS `type`, 
	`r`.`icon_str` AS `icon_str`, `t`.`icon` AS `icon`,
	`r`.`id` AS `unit_id`, `r`.`capab` AS `capab`, `r`.`status_about` AS `status_about`,
	`s`.`bg_color` AS `status_bg`, `s`.`text_color` AS `status_text`,
	`s`.`status_val` AS `unitstatus`, `contact_via`, 
	(((acos(sin(({$latitude}*pi()/180)) * sin((`r`.`lat`*pi()/180))+cos(({$latitude}*pi()/180)) * cos((`r`.`lat`*pi()/180)) * cos((({$longitude} - `r`.`lng`)*pi()/180))))*180/pi())*60*{$nm_to_what}) AS `distance`,
	(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`  
		AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )
		AS `calls_assigned`			
	
	FROM `$GLOBALS[mysql_prefix]responder` `r`
	LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`r`.`un_status_id` = `s`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON (`r`.`type` = `t`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`r`.`id` = `a`.`resource_id`)					
	 WHERE  `dispatch` = 0 $where $where3 GROUP BY unit_id )
UNION DISTINCT
	(SELECT *, `r`.`updated` AS `unit_updated`, `r`.`handle` AS `unit_handle`,
	`r`.`name` AS `unit_name`, `t`.`name` AS `type_name`, `r`.`type` AS `type`,
	`r`.`icon_str` AS `icon_str`, `t`.`icon` AS `icon`,
	`r`.`id` AS `unit_id`, `r`.`capab` AS `capab`, `r`.`status_about` AS `status_about`,
	`s`.`bg_color` AS `status_bg`, `s`.`text_color` AS `status_text`,
	`s`.`status_val` AS `unitstatus`, `contact_via`, 
	9999 AS `distance`,
	(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`  
		AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
		AS `calls_assigned`			
	
	FROM `$GLOBALS[mysql_prefix]responder` `r`
	LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`r`.`un_status_id` = `s`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON (`r`.`type` = `t`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`r`.`id` = `a`.`resource_id`)					
	 WHERE  `dispatch` > 0 $where $where3 GROUP BY unit_id )
	{$order}";		//	6/17/13				 
	 
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$i=0;

while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$unit_id = $row['unit_id'];
	$query2 = "SELECT `member_id` FROM `$GLOBALS[mysql_prefix]responder_x_member` WHERE `responder_id` = " . $unit_id;
	$result2 = mysql_query($query2);
	$assigned_members = array();
	while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {
		$assigned_members[] = $row2['member_id'];
		}
	$counter = 0;
	foreach($assigned_members as $val) {
		$isfound = search_capabilities($val, $capabilities);
		if($isfound) {
			$counter++;
			}
		}
	$isfound = search_resp_capabilities($unit_id, $capabilities);
	if($isfound) {$counter++;}
	if($counter > 0) {
		$unit_array[$i][0] = $unit_id;
		$nm_arr = array();
		$temp_arr = array();
		$temp_addrs = get_mdb_names($unit_id);
		foreach($temp_addrs as $val) {
			array_push($temp_arr, $val);
			}
		$nm_arr = array_unique($temp_arr);
		$contact_name = implode(",", $nm_arr);
		$unit_array[$i][1] = $contact_name;
		$unit_array[$i][2] = $row['unit_handle'];
		$unit_array[$i][3] = $row['status_bg'];
		$unit_array[$i][4] = $row['text_color'];
		$unit_array[$i][5] = $row['unitstatus'];
		$unit_array[$i][6] = $row['status_about'];	
		$unit_array[$i][7] = get_cd_str($row, $ticket_id);	
		$unit_array[$i][8] = $row['lat'];
		$unit_array[$i][9] = $row['lng'];
		$unit_array[$i][10] = round($row['distance'],2);
		$unit_array[$i][11] = $row['mobile'];
		$unit_array[$i][12] = format_sb_date_3(strtotime($row['unit_updated']));
		$unit_array[$i][13] = format_sb_date_3(strtotime($row['status_updated']));
		$unit_array[$i][14] = get_assigned_td($row['unit_id']);
		$i++;
		}
	}
	
if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}
	
switch($sortby) {
	case 'unit_id':
		$thesort = 0;
		break;
	case 'name':
		$thesort = 1;
		break;
	case 'handle':
		$thesort = 2;
		break;
	case 'unitstatus':
		$thesort = 5;
		break;
	case 'calls':
		$thesort = 7;
		break;
	case 'distance':
		$thesort = 10;
		break;
	case 'mobile':
		$thesort = 11;
		break;
	case 'updated':
		$thesort = 12;
		break;
	case 'status_updated':
		$thesort = 13;
		break;
	default:
		$thesort = 10;
	}		
$unit_array = subval_sort($unit_array, $thesort, $dd);
$return_array[0] = $the_ticket;
$return_array[1] = $unit_array;
$return_array[2] = get_icon_legend();
$return_array[3] = addslashes($scope);
print json_encode($return_array);