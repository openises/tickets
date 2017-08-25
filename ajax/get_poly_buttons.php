<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
@session_start();
session_write_close();
$user_id = $_SESSION['user_id'];

function get_usergroups() {
	global $user_id;
	$ret_arr = array();
	$al_groups = $_SESSION['user_groups'];
	
	if(array_key_exists('viewed_groups', $_SESSION)) {
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}
	if(count($al_groups) == 0) {	
		return false;
		} else {
		if(!isset($curr_viewed)) {
			$ret_arr = $al_groups;
			} else {
			$ret_arr = $curr_viewed;
			}
		}
	return $ret_arr;
	}
	
function get_incidents() {
	$ret_arr = array();
	$the_groups = get_usergroups();
	if($the_groups) {
		foreach($the_groups as $grp) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 1 AND `group` = " . $grp;
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
				$ret_arr[] = $row['resource_id'];
				}
			}
		} else {
		$ret_arr = false;
		}
	return $ret_arr;
	}
	
function get_responders() {
	$ret_arr = array();
	$the_groups = get_usergroups();
	if($the_groups) {
		foreach($the_groups as $grp) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `group` = " . $grp;
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/18/11
				$ret_arr[] = $row['resource_id'];
				}
			}
		} else {
		$ret_arr = false;
		}
	return $ret_arr;
	}
	
function get_userfacilities() {
	$ret_arr = array();
	$the_groups = get_usergroups();
	if($the_groups) {
		foreach($the_groups as $grp) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 3 AND `group` = " . $grp;
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/18/11
				$ret_arr[] = $row['resource_id'];
				}
			}
		} else {
		$ret_arr = false;
		}
	return $ret_arr;
	}
	
//	Base Map
function get_basemarkup() {
	$ret_arr = array();
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND `use_with_bm` = 1";
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$ret_arr[$row['id']]['id'] = $row['id'];
			$ret_arr[$row['id']]['name'] = $row['line_name'];
			$ret_arr[$row['id']]['status'] = $row['line_status'];
			$ret_arr[$row['id']]['ident'] = $row['line_ident'];
			$ret_arr[$row['id']]['cat'] = $row['line_cat_id'];
			$ret_arr[$row['id']]['data'] = $row['line_data'];
			$ret_arr[$row['id']]['color'] = $row['line_color'];
			$ret_arr[$row['id']]['opacity'] = $row['line_opacity'];
			$ret_arr[$row['id']]['width'] = $row['line_width'];
			$ret_arr[$row['id']]['fill_color'] = $row['fill_color'];
			$ret_arr[$row['id']]['fill_opacity'] = $row['fill_opacity'];
			$ret_arr[$row['id']]['filled'] = $row['filled'];
			$ret_arr[$row['id']]['theType'] = "basemarkup";
			}
		} else {
		$ret_arr = array();
		}
	return $ret_arr;
	}
	
//	Group Boundaries
	
function get_groupbounds() {
	$ret_arr =array();
	$gp_bounds = get_usergroups();
	if(count($gp_bounds) != 0) {
		foreach($gp_bounds as $value) {
			$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= " . $value . " AND `boundary` <> 0 LIMIT 1";
			$result_bound = mysql_query($query_bound)or do_error($query_bound, mysql_error(), basename(__FILE__), __LINE__);
			if(mysql_num_rows($result_bound) == 1) {
				$row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound));
				$theBound = $row_bound['boundary'];
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $theBound;
				$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
				if(mysql_num_rows($result) != 0) {
					$row = stripslashes_deep(mysql_fetch_assoc($result));
					$ret_arr[$value]['id'] = $row['id'];
					$ret_arr[$value]['name'] = $row['line_name'];
					$ret_arr[$value]['status'] = $row['line_status'];
					$ret_arr[$value]['ident'] = $row['line_ident'];
					$ret_arr[$value]['cat'] = $row['line_cat_id'];
					$ret_arr[$value]['data'] = $row['line_data'];
					$ret_arr[$value]['color'] = "#" . $row['line_color'];
					$ret_arr[$value]['opacity'] = $row['line_opacity'];
					$ret_arr[$value]['width'] = $row['line_width'];
					$ret_arr[$value]['fill_color'] = "#" . $row['fill_color'];
					$ret_arr[$value]['fill_opacity'] = $row['fill_opacity'];
					$ret_arr[$value]['filled'] = $row['filled'];
					$ret_arr[$value]['type'] = $row['line_type'];
					}
				}
			}
		} else {
		$ret_arr = array();
		}
	return $ret_arr;
	}
	
function get_otherbounds($id) {
	$ret_arr =array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr['id'] = $row['id'];
		$ret_arr['name'] = $row['line_name'];
		$ret_arr['status'] = $row['line_status'];
		$ret_arr['ident'] = $row['line_ident'];
		$ret_arr['cat'] = $row['line_cat_id'];
		$ret_arr['data'] = $row['line_data'];
		$ret_arr['color'] = $row['line_color'];
		$ret_arr['opacity'] = $row['line_opacity'];
		$ret_arr['width'] = $row['line_width'];
		$ret_arr['fill_color'] = $row['fill_color'];
		$ret_arr['fill_opacity'] = $row['fill_opacity'];
		$ret_arr['filled'] = $row['filled'];
		} else {
		$ret_arr = array();
		}
	return $ret_arr;
	}

function get_exclusion_zones() {
	$ret_arr = array();
	$units =  array();
	$user_units = get_responders();
	if(!$user_units) {
		return false;
		}
	foreach($user_units as $val) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $val . " LIMIT 1";
		$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
		if(mysql_num_rows($result) > 0) {		
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			if(intval($row['excl_zone']) > 0) {
				$units[$row['id']] = intval($row['excl_zone']);
				}
			}
		}
	foreach($units as $val) {
		$ret_arr[key($units)] = get_otherbounds($val);
		$ret_arr[key($units)]['theType'] = "exclusion";
		}
	return $ret_arr;
	}
	
function get_ring_fences() {
	$ret_arr = array();
	$units =  array();
	$user_units = get_responders();
	if(!$user_units) {
		return false;
		}
	foreach($user_units as $val) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $val . " LIMIT 1";
		$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
		if(mysql_num_rows($result) > 0) {
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			if(intval($row['ring_fence']) > 0) {
				$units[$row['id']] = intval($row['ring_fence']);
				}
			}
		}
	foreach($units as $val) {
		$ret_arr[key($units)] = get_otherbounds($val);
		$ret_arr[key($units)]['theType'] = "ringfence";
		}
	return $ret_arr;
	}
	
function get_facility_catchments() {
	$ret_arr = array();
	$facilities =  array();
	$user_facilities = get_userfacilities();
	if(!$user_facilities) {
		return false;
		}
	foreach($user_facilities as $val) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $val . " LIMIT 1";
		$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
		if(mysql_num_rows($result) > 0) {		
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			if(intval($row['boundary']) > 0) {
				$facilities[$row['id']] = intval($row['boundary']);
				}
			}				
		}
	foreach($facilities as $val) {
		$ret_arr[key($facilities)] = get_otherbounds($val);
		$ret_arr[key($facilities)]['theType'] = "catchment";
		}

	return $ret_arr;		
	}

$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
$ctrls_width = $col_width * .75;
$ret_arr = array();
$basemarkup = get_basemarkup();
$groupbounds = get_groupbounds();
$exclusions = get_exclusion_zones();
$ringfences = get_ring_fences();
$catchments = get_facility_catchments();
$output_arr = array();

if($basemarkup) {
	foreach($basemarkup as $var) {
		if(array_key_exists('id', $var)) {
			if(!isset($output_arr[0][$var['id']])) {
				$output_arr[0][$var['id']] = $var;
				}
			}
		}
	}

if($groupbounds) {
	foreach($groupbounds as $var) {
		if(array_key_exists('id', $var)) {
			if(!isset($output_arr[1][$var['id']])) {
				$output_arr[1][$var['id']] = $var;
				}
			}
		}
	}
	
if($exclusions) {
	foreach($exclusions as $var) {
		if(array_key_exists('id', $var)) {
			if(!isset($output_arr[2][$var['id']])) {
				$output_arr[2][$var['id']] = $var;
				}
			}
		}
	}

if($ringfences) {
	foreach($ringfences as $var) {
		if(array_key_exists('id', $var)) {
			if(!isset($output_arr[3][$var['id']])) {
				$output_arr[3][$var['id']] = $var;
				}
			}
		}
	}

if($catchments) {
	foreach($catchments as $var) {
		if(array_key_exists('id', $var)) {
			if(!isset($output_arr[4][$var['id']])) {
				$output_arr[4][$var['id']] = $var;
				}
			}
		}
	}

$markupTypes = array("Base Map Markup", "Group Boundaries", "Exclusion Zones", "Ring Fences", "Facility Catchments");

$mmarkup = $output_arr;
$output = "";
if(count($mmarkup) == 0) {
	$output .= "<TABLE><TR class='heading_2'><TH width = '" . $ctrls_width . "px' ALIGN = 'center'>Map Markup</TH></TR>";
	$output .= "<TR class='odd'><TD COLSPAN=99 CLASS='td_label text' ><form action='#'>";
	$output .= "<DIV class='cat_button text' style='color: red;'>None Defined ! </DIV>";
	$output .= "</form></TD></TR></TABLE></DIV>";
	} else {
	$output .= "<form action='#'><TABLE><TR class='heading_2'><TH width = '" . $ctrls_width . "px' ALIGN = 'center'>Map Markup</TH></TR><TR class='spacer'><TD class='spacer'>&nbsp;</TD></TR>";
	foreach($mmarkup as $key1 => $val1) {
		if($markupTypes[$key1]) {
			$keytype = $markupTypes[$key1];
			$theMarkup = $mmarkup[$key1];
			if($keytype) {
				$output .= "<TR class='heading_3'><TH width = '" . $ctrls_width . "px' ALIGN = 'center'>" . $markupTypes[$key1] . "</TH></TR>";
				}
			foreach($theMarkup as $key => $value) {
				if(array_key_exists('name', $value)) {
					$output .= "<TR class='odd'><TD COLSPAN=99 CLASS='td_label text' >";		
					$output .= "<DIV class='cat_button text' onClick='set_bnd_chkbox(\"" . $value['name'] . "\")'>" . $value['name'] . ": <input type=checkbox id='" . $value['name'] . "' onClick='set_bnd_chkbox(\"" . $value['name'] . "\")' CHECKED/>&nbsp;&nbsp;&nbsp;</DIV></TD></TR>";
					}
				}
			}
		}
	$output .= "</TD></TR></TABLE></form>";
	$all="BND_ALL";
	$none="BND_NONE";
	$output .= "<DIV ID = 'BND_ALL_BUTTON' class='cat_button text' onClick='set_bnd_chkbox(\"" . $all . "\")' STYLE = 'display:none;'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='" . $all . "' onClick='set_bnd_chkbox(\"" . $all . "\")'/></FONT></DIV>";
	$output .= "<DIV ID = 'BND_NONE_BUTTON' class='cat_button text'  onClick='set_bnd_chkbox(\"" . $none . "\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='" . $none . "' onClick='set_bnd_chkbox(\"" . $none . "\")'/></FONT></DIV>";
	$output .= "<DIV ID = 'bnd_go_can' style='float:right; padding:2px;'><SPAN ID = 'bnd_go_button' onClick='do_go_bnd_button()' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: green;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);'>Next</SPAN>";
	$output .= "<SPAN ID = 'bnd_can_button'  onClick='bnd_cancel_button()' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: red;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);'>Cancel</SPAN></DIV>";
	}
	
$ret_arr[0] = $output;
print json_encode($ret_arr);
exit();
?>