<?php
/*
9/10/13 New file - Index for mobile page
4/24/14 Revised to load map with default position and then move on location found.
*/
require_once('../incs/functions.inc.php');
require_once('./incs/mobile_login.inc.php');
require_once('../incs/browser.inc.php');			// 6/12/10
$https = (array_key_exists('HTTPS', $_SERVER)) ? TRUE : FALSE;
@session_start();
$c_types = array();
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$logged_in = (!(empty($_SESSION))) ? 1 : 0;
$cycle = 5000;			// user reviseable delay between chat polls, in milliseconds
$list_length = 99;		// chat list length maximum
$browser = trim(checkBrowser(FALSE));						// 6/12/10

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}conditions`";
$result = db_query($query);
while($row = $result->fetch_array(MYSQLI_ASSOC)) {
	$c_types[$row['id']]['id'] = $row['id'];
	$c_types[$row['id']]['title'] = $row['title'];
	$c_types[$row['id']]['description'] = $row['description'];	
	$c_types[$row['id']]['icon'] = $row['icon'];
	}
if (isset($_GET['logout'])) {
	do_mobile_logout();
	}
if(((!empty($_GET)) && ($_GET['do_login'] == 1)) || (!empty($_POST)) || (intval(get_variable('responder_mobile_forcelogin')) == 1)) {
	do_mobile_login(basename(__FILE__));
	}
$al_names = "";
$the_user = 0;
$the_responder = 0;
$the_email = "";
$poll_cycle_time = 5000;
$chat_user = 0;

$status_vals = array();
$status_vals[''] = $status_vals['0']="TBD";

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}un_status` ORDER BY `id`";
$result_st = db_query($query);
while ($row_st = stripslashes_deep($result_st->fetch_array())) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	}
unset($result_st);

$users_arr = array();

$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user`";
$result_users = db_query($query);
while ($row_users = stripslashes_deep($result_users->fetch_assoc())) {
	$users_arr[$row_users['id']] = $row_users['responder_id'];
	}

$user_names = array();

$query2 = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user`";
$result_users2 = db_query($query2);
while ($row_users2 = stripslashes_deep($result_users2->fetch_assoc())) 	{
	$user_names[$row_users2['id']] = $row_users2['user'];
	}

function get_status_selector($unit_in, $status_val_in, $tbl_in) {
	switch ($tbl_in) {
		case ("u") :
			$tablename = "responder";
			$link_field = "un_status_id";
			$status_table = "un_status";
			$status_field = "status_val";
			break;
		case ("f") :
			$tablename = "facilities";
			$link_field = "status_id";
			$status_table = "fac_status";
			$status_field = "status_val";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";	
			}

	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}{$tablename}`, `{$GLOBALS['mysql_prefix']}{$status_table}` WHERE `{$GLOBALS['mysql_prefix']}{$tablename}`.`id` = $unit_in 
		AND `{$GLOBALS['mysql_prefix']}{$status_table}`.`id` = `{$GLOBALS['mysql_prefix']}{$tablename}`.`{$link_field}` LIMIT 1" ;	

	$result = db_query($query);
	if (db()->affected_rows==0) {				// 2/7/10
		$init_bg_color = "transparent";
		$init_txt_color = "black";
		}
	else {
		$row = stripslashes_deep($result->fetch_assoc());
		$init_bg_color = $row['bg_color'];
		$init_txt_color = $row['text_color'];
		}

	$guest = is_guest();
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}{$status_table}` ORDER BY `group` ASC, `sort` ASC, `{$status_field}` ASC";	
	$result_st = db_query($query);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = ($tbl_in == "u") ? "\t\t<SELECT CLASS='text' id='frm_status_id_u_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 100%;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; update_status({$unit_in}, this.value)' >" :
	"\t\t<SELECT CLASS='text' id='frm_status_id_f_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 100%;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update_fac({$unit_in}, this.value)' >";
	while ($row = stripslashes_deep($result_st->fetch_assoc())) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP CLASS='text' LABEL='$the_grp'>";
			}
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION CLASS='text' VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'  onMouseover = 'style.backgroundColor = this.backgroundColor;'>$row[$status_field] </OPTION>";		
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\t\t</SELECT>";
	return $outstr;
	}

function get_responder_details($id) {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `id`= ?";
	$result = db_query($query, [$id]);
	$row = stripslashes_deep($result->fetch_assoc());
	$ret = $row['contact_via'];
	return $ret;
	}

function get_responder_status($id) {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `id`= ?";
	$result = db_query($query, [$id]);
	$row = stripslashes_deep($result->fetch_assoc());
	$ret = $row['un_status_id'];
	return $ret;
	}

function get_responder_name($id) {
	if(($id == 0) && (isset($_SESSION['user_id']))) {
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user` WHERE `id` = ?";
		$result = db_query($query, [$_SESSION['user_id']]);
		$row = stripslashes_deep($result->fetch_assoc());
		return $row['name_f'] . " " . $row['name_l'];
		} elseif(($id == 0) && (!isset($_SESSION['user_id']))) {
		return "NA";
		} else {
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `id` = ?";
		$result = db_query($query, [$id]);
		if($result->num_rows != 0) {
			$row = stripslashes_deep($result->fetch_assoc());
			$ret = $row['name'];
			return $ret;
			} else {
			return "NA";
			}
		}
	}

function get_responder_handle($id) {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `id` = ?";
	$result = db_query($query, [$id]);
	if($result->num_rows != 0) {
		$row = stripslashes_deep($result->fetch_assoc());
		$ret = $row['handle'];
		return $ret;
		} else {
		return "NA";
		}
	}

if (isset($_SESSION['user_id'])) {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}user` WHERE `id`= ?";
	$result = db_query($query, [$_SESSION['user_id']]);
	$row = stripslashes_deep($result->fetch_assoc());
	$the_responder = (($row['responder_id'] != 0) && ($row['responder_id'] != NULL) && ($row['responder_id'] != "")) ? $row['responder_id']: 0;
	$chat_user = $_SESSION['user_id'];
	$the_user = ($the_responder != 0) ? $the_responder : $_SESSION['user_id'];
	$the_email = ($the_responder != 0) ? get_responder_details($the_user) : $row['email'];
	}

if($the_user != 0) {
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}allocates` WHERE `type`= 4 AND `resource_id` = ? ORDER BY `id` ASC";
	$result = db_query($query, [$the_user]);
	$al_names = "Showing " . get_text("Region") . "(s): ";
	while ($row = stripslashes_deep($result->fetch_assoc())) 	{
		$query2 = "SELECT * FROM `{$GLOBALS['mysql_prefix']}region` WHERE `id`= ?";
		$result2 = db_query($query2, [$row['group']]);
		while ($row2 = stripslashes_deep($result2->fetch_assoc())) 	{
				$al_names .= $row2['group_name'] . ", ";
			}
		}
	}

if($the_responder != 0) {
	$the_status_sel = get_status_selector($the_responder, get_responder_status($the_responder), "u");
	}
	
$logged_in_load = ($logged_in == 1) ? "get_conditions(); get_ticket_markers(" . $the_user . ");" : "";
$respondername = get_responder_handle($the_responder);

if(get_variable('map_on_rm') == "1" && $https) {
//if(get_variable('map_on_rm') == "1") {	
	include('./forms/mapindex.php');
	} else {
	include('./forms/nomapindex.php');
	}