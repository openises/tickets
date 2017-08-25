<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
set_time_limit(0);
@session_start();

if ((array_key_exists("chg_hide", $_POST)) && ($_POST['chg_hide']==1)) {			// change persistence value - 2/18/09
	$temp = $_POST['hide_cl'];
	$_SESSION['show_hide_cleared'] = $temp;		// show/hide closed assigns
	}
session_write_close();

$thresh_n = array(3, 20, 30, 40, 50, 60, 120);	// threshold times in minutes - normal incidents
$thresh_m = array(2, 5, 15, 15, 15, 15, 60);	// threshold times in minutes - medium-severity incidents
$thresh_h = array(1, 5, 30, 40, 50, 60, 30);	// threshold times in minutes - high-severity incidents

// ========================================================================================================
//
// Call board layout values - group percentages followed by individual columnn widths - 11/27/09
//
// ========================================================================================================


$TBL_INC_PERC = 50;		// incident group - four columns  -  50 percent as default
$TBL_UNIT_PERC = 35;	// unit group, includes checkboxes  -  35 percent as default
$TBL_CALL_PERC = 10;	// call group - three columns  -  10 percent as default
						// total shd be ~ 100
//						column width in characters - use zero to suppress display

$COLS_INCID = 18;		// incident name -  18 characters as default
$COLS_OPENED = 0;		// date/time opened -  0 characters as default
$COLS_DESCR = 32;		// incident description -  32 characters as default
$COLS_ADDR = 32;		// address -  32 characters as default

$COLS_UNIT = 15;			// unit name

$COLS_ASOF = 9;			// call as-of date/time -  9 characters as default
$COLS_USER = 3;			// last update by user xxx -  3 characters as default
$COLS_COMMENTS = 8;		// run comments -  8 characters as default

function cb_shorten($instring, $limit) {
	return (strlen($instring) > $limit)? substr($instring, 0, $limit): $instring;	// &#133
	}
	
function get_un_stat_sel($s_id, $b_id) {					// returns select list as string
	global $guest;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`, `$GLOBALS[mysql_prefix]un_status`
		WHERE `$GLOBALS[mysql_prefix]un_status`.`id` = $s_id
		AND `$GLOBALS[mysql_prefix]un_status`.`id` = `$GLOBALS[mysql_prefix]responder`.`un_status_id` LIMIT 1" ;
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = (mysql_num_rows($result_st)>0)? stripslashes_deep(mysql_fetch_assoc($result_st)) : FALSE;
	$init_bg_color = ($row)? $row['bg_color'] : "transparent";
	$init_txt_color = ($row)? $row['text_color']: "black";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = "\n\t\t<SELECT name='frm_status_id'  onFocus = 'show_but($b_id)' $dis STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;'>\n";
	while ($row = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>\n";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		$sel = ($row['id']==$s_id)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'>" . $row['status_val'] . "</OPTION>\n";
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\n\t\t</SELECT>\n";
	return $outstr;
	unset($result_st);
	}
	
function my_to_date($in_date) {			// date_time format to user's spec
	$temp = mysql2timestamp($in_date);		// 9/29/10
	return (good_date_time($in_date)) ?  date(get_variable("date_format"), $temp): "";		//
	}

function my_to_date_sh($in_date) {			// short date_time string
	$temp = mysql2timestamp($in_date);		// 9/29/10
	return (good_date_time($in_date)) ?  date("H:i", $temp): "";		//
	}

function my_gregoriantojd ( $da, $mo, $yr) {		// 1/10/2013
	return strtotime ("{$da} {$mo} {$yr}");
	}

$jd_today = my_gregoriantojd (date ("M"), date ("j"), date ("Y"));			// julian today - see get_disp_cell() - 1/7/2013

function get_disp_cell($row_element, $form_element, $theClass ) {		// returns td cell with disp times or checkbox - 1/8/2013
	$can_update = (array_key_exists ('level', $_SESSION) )? ( is_administrator() || is_user()): FALSE;			// 1/8/2013
	global $jd_today;
	if (is_date($row_element)) {
		$ttip_str = " onmouseover=\"Tip(' " . my_to_date($row_element) . "')\" onmouseout=\"UnTip()\" ";
		$then = strtotime($row_element);
		$jd_then = my_gregoriantojd (date ("M", $then), date ("j", $then), date ("Y", $then));
		$this_class = ($jd_then == $jd_today )? $theClass: "my_plain";
		return "\n\t<TD CLASS='{$this_class}' {$ttip_str}>" . my_to_date_sh($row_element) . "</TD>\n";	// identify as not-today
		}
	else {
		$is_dis = ($can_update)? "" : "DISABLED";		// limit to admins, operators
		return "\n\t<TD CLASS='{$theClass}'><INPUT TYPE='checkbox' NAME='{$form_element}' {$is_dis} onClick = 'checkbox_clicked()' ></TD>\n";
		}
	}		// end function get_disp_cell()

$priorities = array("","severity_medium","severity_high" );
$status_vals_ar = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE 1";
$result_s = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_array($result_s))) {
	$sep = (empty($row['description']))? "": ":";
	$status_vals_ar[$row['id']] = $row['status_val'] . $sep . $row['description'] ;
	}
			
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";		// 2/12/09
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
$lines = mysql_num_rows($result);

if ( ((empty ($_POST)) && (!array_key_exists( "show_hide_cleared", $_SESSION)) ) || ( array_key_exists ( "hide_cl", $_POST) && ( $_POST["hide_cl"] == "h") ) || (array_key_exists ( "show_hide_cleared", $_SESSION) && ( $_SESSION["show_hide_cleared"] == "h")) ) {
	$butn_txt = "Show ";
	$butn_val = "s";
	$temp =  get_variable('closed_interval');
	$cwi = ( empty($temp) ) ? "24" : $temp;		// default to 24 hours if no user setting
	$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));
	$hide_sql = " OR `clear`>= '$time_back' ";
	}
else 	{										// otherwise show
	$temp =  get_variable('closed_interval');
	$cwi = ( empty($temp) ) ? "24" : $temp;		// default to 24 hours if no user setting
	$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));
	$hide_sql = " OR `clear`>= '$time_back' ";
	}

$order_by = (array_key_exists('sort', $_POST))? "`handle` ASC " : "`severity` DESC, `tick_scope` ASC, `unit_name` ASC ";

// ============================= Regions Stuff	sets which tickets the user can see.

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {	//	5/4/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	} else {
	$curr_viewed = $al_groups;
	}

if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
	if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where = "WHERE `a`.`type` = 1";
		} else {
		$x=0;
		$where = "WHERE ((";
		foreach($al_groups as $grp) {
			$where2 = (count($al_groups) > ($x+1)) ? " OR " : ")";
			$where .= "`a`.`group` = {$grp}";
			$where .= $where2;
			$x++;
			}
		$where .= " AND `a`.`type` = 1) ";
		}	//	end if count($al_groups ==0)
	} else {
	if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where = "WHERE `a`.`type` = 1";
		} else {
		$x=0;
		$where = "WHERE ((";		//	6/10/11
		foreach($curr_viewed as $grp) {
			$where2 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
			$where .= "`a`.`group` = {$grp}";
			$where .= $where2;
			$x++;
			}
		$where .= " AND `a`.`type` = 1) ";
		}	//	end if count($curr_viewed ==0)
	}	//	End if !isset $_SESSION['viewed_groups']
	
// ================================ end of regions stuff

$query = "SELECT *, `as_of` AS `as_of`,
	`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
	`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
	`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
	`t`.`description` AS `tick_descr`,
	`t`.`status` AS `tick_status`,
	`t`.`street` AS `tick_street`,
	`t`.`city` AS `tick_city`,
	`t`.`state` AS `tick_state`,
	`r`.`id` AS `unit_id`,
	`r`.`name` AS `unit_name` ,
	`r`.`type` AS `unit_type` ,
	`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
	FROM `$GLOBALS[mysql_prefix]assigns`
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `a`.`resource_id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
	{$where} AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') $hide_sql
	GROUP BY `$GLOBALS[mysql_prefix]assigns`.`id`
	ORDER BY {$order_by }";

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
$lines = mysql_num_rows($result);

$now = time() - (get_variable('delta_mins')*60);
$items = mysql_num_rows($result);
$tags_arr = explode("/", get_variable('disp_stat'));
$unit_ids = array();
$ret_arr = array();
if($lines == 0) {
	$ret_arr[0][0] = 0;
	} else {
	$ret_arr[0][0] = $lines;
	$i=1; 
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	//	============================= Regions stuff
		$query_un = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `resource_id` = '$row[unit_id]' ORDER BY `id` ASC;";
		$result_un = mysql_query($query_un);	// 6/10/11
		$un_groups = array();
		while ($row_un = stripslashes_deep(mysql_fetch_assoc($result_un))) 	{
			$un_groups[] = $row_un['group'];
			}

		if(count($al_groups) == 0) {
			$inviewed = 1;
			} else {
			$inviewed = 0;
			foreach($un_groups as $un_val) {
				if(in_array($un_val, $al_groups)) {
					$inviewed++;
					}
				}
			}
		$theClass = ($row['severity']=='')? "":$priorities[$row['severity']];

		if ($inviewed > 0) {
			$the_name = addslashes ($row['tick_scope']);															// 9/12/09
			$short_name = cb_shorten($row['tick_scope'], $COLS_INCID);
			$the_descr = addslashes ($row['tick_descr']);
			$short_desc = cb_shorten($row['tick_descr'], $COLS_DESCR);
			$address = (empty($row['tick_street']))? "" : $row['tick_street'] . ", ";		// 8/10/10
			$address = addslashes($address . $row['tick_city']. " ". $row['tick_state']);
			$short_addr = cb_shorten($address, $COLS_ADDR);
			$disp_string = "";
			$unit_name = "";
			$short_name = "";
			if (!($row['unit_id'] == 0)) {																	// 5/11/09
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`	WHERE `id`= '{$row['unit_type']}' LIMIT 1";
				$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				$row_type = (mysql_num_rows($result_type) > 0) ? stripslashes_deep(mysql_fetch_assoc($result_type)) : "";
				$the_bg_color = empty($row_type)?	"transparent" : $GLOBALS['UNIT_TYPES_BG'][$row_type['icon']];		// 3/15/10
				$the_text_color = empty($row_type)? "black" :		$GLOBALS['UNIT_TYPES_TEXT'][$row_type['icon']];		//
				unset ($row_type);
				$unit_name = empty($row['unit_id']) ? "[#{$row['unit_id']}]" : ($row['unit_name']) ;			// id only if absent
				$short_name = cb_shorten($row['handle'], $COLS_UNIT);
				$disp_string = get_disp_cell($row['dispatched'], 	"frm_dispatched", $theClass ) . "|";
				$disp_string .= get_disp_cell($row['responding'], 	"frm_responding", $theClass ) . "|";
				$disp_string .= get_disp_cell($row['on_scene'], 	"frm_on_scene", $theClass ) . "|";
				$disp_string .= get_disp_cell($row['u2fenr'], 	"frm_u2fenr", $theClass ) . "|";
				$disp_string .= get_disp_cell($row['u2farr'], 	"frm_u2farr", $theClass ) . "|";
				$disp_string .= get_disp_cell($row['clear'], 	"frm_clear", $theClass ) . "|";
				if (!in_array ($row['unit_id'], $unit_ids)) {				// status array not yet shown?
					$unit_st_val = (array_key_exists($row['un_status_id'], $status_vals_ar))? $status_vals_ar[$row["un_status_id"]]: "";
					if (empty($row['unit_id'])) {				// 3/15/10
						$status_sel = "na";
						} else {
						$status_sel = get_un_stat_sel($row['un_status_id'], $i);						// 4/4/10 status
						}
					array_push($unit_ids, $row['unit_id']);
					} else {
					$unit_status_val = "";
					$status_sel = "";
					}			
				}
			$d1 = $row['assign_as_of'];
			$d2 = mysql2timestamp($d1);		// 9/29/10
			$comment = addslashes (remove_nls($row['assign_comments']));
			$ret_arr[$i][0] = $the_name;
			$ret_arr[$i][1] = $short_name;
			$ret_arr[$i][2] = $the_descr;
			$ret_arr[$i][3] = $short_desc;
			$ret_arr[$i][4] = $address;
			$ret_arr[$i][5] = $short_addr;
			$ret_arr[$i][6] = $unit_name;
			$ret_arr[$i][7] = $short_name;
			$ret_arr[$i][8] = $disp_string;
			$ret_arr[$i][9] = $unit_st_val;
			$ret_arr[$i][10] = $status_sel;
			$ret_arr[$i][11] = "[#{$row['assign_id']}] " . date(get_variable("date_format"), $d2);
			$ret_arr[$i][12] = $comment;
			$ret_arr[$i][12] = $row['assign_id'];			
			$i++;
			}
		}
	dump($ret_arr);
	}