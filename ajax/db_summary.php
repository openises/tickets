<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
// $the_session = $_GET['session'];
// if(!(secure_page($the_session))) {
	// exit();
	// } else {
	
$type_arr = array();
$st_arr = array();
$ret_arr = array();
$today = time();
$temp = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
$datetoday = strtotime($temp);
$daytoday = $temp;
$plusonemonth = date('Y-m-d H:i:s', strtotime("+1 month", $datetoday));
$theClass = 'even';

function output_report($table, $field, $title, $noneflag) {
	global $datetoday, $plusonemonth, $today, $daytoday, $theClass;
	$thestring = "";
	$query = "SELECT *, `" . $field . "` FROM `$GLOBALS[mysql_prefix]" . $table . "`
						WHERE (`" . $field . "` BETWEEN '" . $daytoday . "' AND '" . $plusonemonth . "') OR (`" . $field . "` < '" . $daytoday . "')";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			if($table == "member") {
				$toview = "onClick='go_there(\"member.php?view=true&id=" . $row['id'] . "\");'";
				} else {
				$toview = "onClick='linkFromSumm(\"vehicles\", " . $row['id'] . ");'";
				}

			$thestring .= "<TR class='" . $theClass . "' style='height: 1.3em; width: 100%;'>";
			if($table == "member") {
				$thestring .= "<TD class='text' style='text-align: left; font-size: 0.8em; vertical-align: middle;'>" . $row['field2'] . " " . $row['field1'] . "</TD>";
				} else {
				$thestring .= "<TD class='text' style='text-align: left; font-size: 0.8em; vertical-align: middle;'>" . $row['regno'] . " " . $row['make'] . " " . $row['model'] . "</TD>";			
				}
			$thestring .= "<TD class='text' style='text-align: left; font-size: 0.8em; vertical-align: middle;'>" . $title . "</TD>";
			$numDays = abs($today - strtotime($row[$field]))/60/60/24;
			if($numDays >= 10) {
				$theFlag = "TITLE='10 days or more overdue' style='text-align: left; font-weight: bold; background-color: red; color: #000000; font-size: 0.8em; vertical-align: middle;'";
				} else {
				$theFlag = "TITLE='Less than 10 days overdue' style='text-align: left; font-size: 0.8em; vertical-align: middle;'";
				}
			$thestring .= "<TD class='text' " . $theFlag . ">Due: &nbsp;" . date('d/m/Y', strtotime($row[$field])) . "</TD>";
			$thestring .= "<TD class='text' style='text-align: left; vertical-align: middle;'>&nbsp;</TD>";
			$thestring .= "<TD class='text'>&nbsp;</TD>";
			$thestring .= "<TD class='text' style='text-align: left; vertical-align: middle;'><SPAN id = \"view_but_" . $field . "_" . $row['id'] . "\" class='plain text_medium' style='width: 80px; display: block; cursor: pointer;' onMouseOver='do_hover_medium(this.id);' onMouseOut='do_plain_medium(this.id);' " . $toview . ">View</SPAN></TD>";
			$thestring .= "</TR>";
			$theClass = ($theClass == 'even') ? 'odd' : 'even';
			}
		} else {
		$thestring .= "<TR class='" . $theClass . "' style='width: 100%;'><TD class='text' colspan=99><B>No " . $noneflag . " due within the next month</B></TD></TR>";
		$theClass = ($theClass == 'even') ? 'odd' : 'even';
		}
	return $thestring;
	}

function get_vehicle_driver($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `skill_id` = '{$id}'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$member = get_member_name($row['member_id']);
		return $member;
		} else {
		return "UNK";
		}
	}

function get_stname($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status` WHERE `id` = '{$id}'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['status_val'];
	}
	
function get_typename($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types` WHERE `id` = '{$id}'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['name'];
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	
	$type_arr[] = get_typename($row['id']);
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	
	$st_arr[] = get_stname($row['id']);
	}	

$num_by_type = array();												// 1/28/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$num_by_type[$row['id']]=  $row['name'];
	}
unset($result);

$query = "SELECT `field7`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]member` GROUP BY `field7`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
$total = 0;
$out_str = "";
while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$total += $row['the_count'];
	$plural = ($row['the_count']!= 1)? "s": "";
	$out_str .= (array_key_exists($row['field7'], $num_by_type)) ? $row['the_count'] ." " . $num_by_type[$row['field7']] . $plural . ", " : "";
	}
$show_str = $out_str . "<B>" . $total . " total</B>";
unset($result);	

$types_str = "";
$total_types = count($type_arr);
foreach($type_arr as $val) {
	$types_str .= $val . ", ";
	}
$types_str .= "<B>" . $total_types . " total</B>";
	
$status_str = "";
$total_status = count($st_arr);
foreach($st_arr as $val) {
	$status_str .= $val . ", ";
	}
$status_str .= "<B>" . $total_status . " total</B>";
	
$thestring = "<TABLE cellpadding='5' width='100%'>";	//	Due Dates alerts table

//	Training due for renewal
$query = "SELECT *, `refresh_due` AS refresh_due,`$GLOBALS[mysql_prefix]allocations`.`id` AS `all_id`,`$GLOBALS[mysql_prefix]member`.`id` AS `member_id` FROM `$GLOBALS[mysql_prefix]allocations`
					LEFT JOIN `$GLOBALS[mysql_prefix]member` ON `$GLOBALS[mysql_prefix]allocations`.`member_id`=`$GLOBALS[mysql_prefix]member`.`id`
					LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` ON `$GLOBALS[mysql_prefix]allocations`.`skill_id`=`$GLOBALS[mysql_prefix]training_packages`.`id`					
					WHERE `skill_type` = '1' AND ((`refresh_due` BETWEEN '" . $datetoday . "' AND '" . $plusonemonth . "') OR (`refresh_due` < '" . $datetoday . "'))";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) > 0) {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$toedit = "onClick='go_there(\"member.php?e_training=true&mem_id=" . $row['member_id'] . "&all_id=" . $row['all_id'] . "\");'";
		$toview = "onClick='do_view(" . $row['all_id'] . ", \"./forms/view_training.php\", \"Training\");'";
		$thestring .= "<TR class='" . $theClass . "' style='height: 1.3em; width: 100%;'>";
		$thestring .= "<TD class='text_medium' style='text-align: left; vertical-align: middle;'>" . $row['field2'] . " " . $row['field1'] . "</TD>";
		$thestring .= "<TD class='text_medium' style='text-align: left; vertical-align: middle;'>" . $row['package_name'] . "</TD>";
		$numDays = abs($today - strtotime($row['refresh_due']))/60/60/24;
		if($numDays >= 10) {
			$theFlag = "TITLE='10 days or more overdue' style='text-align: left; font-weight: bold; background-color: red; color: #000000; vertical-align: middle;'";
			} else {
			$theFlag = "TITLE='Less than 10 days overdue' style='text-align: left; vertical-align: middle;'";
			}
		$thestring .= "<TD class='text_medium' " . $theFlag . ">Due: &nbsp;" . date('d/m/Y', strtotime($row['refresh_due'])) . "</TD>";
		$thestring .= "<TD class='text' style='text-align: left; vertical-align: middle;'>&nbsp;</TD>";
		$thestring .= "<TD class='text_medium' style='text-align: left; vertical-align: middle;'><SPAN id = \"edit_but_training_" . $row['all_id'] . "\" class='plain text_medium' style='width: 80px; display: block; cursor: pointer;' onMouseOver='do_hover_medium(this.id);' onMouseOut='do_plain_medium(this.id);' " . $toedit . ">Edit</SPAN></TD>";
		$thestring .= "<TD class='text_medium' style='text-align: left; vertical-align: middle;'><SPAN id = \"view_but_training_" . $row['all_id'] . "\" class='plain text_medium' style='width: 80px; display: block; cursor: pointer;' onMouseOver='do_hover_medium(this.id);' onMouseOut='do_plain_medium(this.id);' " . $toview . ">View</SPAN></TD>";
		$thestring .= "</TR>";
		$theClass = ($theClass == 'even') ? 'odd' : 'even';
		}
	} else {
	$thestring .= "<TR class='" . $theClass . "' style='width: 100%;'><TD class='text' colspan=99><B>No Training is due for renewal within the next month</B></TD></TR>";
	$theClass = ($theClass == 'even') ? 'odd' : 'even';
	}
	
$temp1 = get_mem_datetime_fields();
$temp2 = get_veh_datetime_fields();
$count_mem = count($temp1);
$count_veh = count($temp2);
$theSetting = explode(",", get_mdb_variable("date_tracking"));
$count_setting = count($theSetting);
$temp3 = array_merge($temp1, $temp2);
$num_tracks = count($temp3);

// Member table date fields tracking
for ($i=0; $i < $count_mem; $i++) {
	if($theSetting[$i] == 1) {
		if(array_key_exists($i, $temp1) && $temp1[$i]['label']) {
			$field = $temp3[$i]['fieldid'];
			$table = 'member';
			$title = $temp3[$i]['title'];
			$flag = $temp3[$i]['flag'];
			$thestring .= output_report($table, $field, $title, $flag);
			}
		}
	}
	
// Vehicle table date fields tracking
for ($i=$count_veh; $i < $count_setting; $i++) {
	if($theSetting[$i] == 1) {
		if(array_key_exists($i, $temp2) && $temp2[$i]['label']) {
			$field = $temp3[$i]['label'];
			$table = 'vehicles';
			$title = $temp3[$i]['title'];
			$flag = $temp3[$i]['flag'];
			$thestring .= output_report($table, $field, $title, $flag);
			}
		}
	}
$thestring .= "</TABLE>";	

$ret_arr[] = $show_str;
$ret_arr[] = $types_str;
$ret_arr[] = $status_str;
$ret_arr[] = $thestring;
print json_encode($ret_arr);
//}