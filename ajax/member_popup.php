<?php
require_once('../incs/functions.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$id = $_GET['id'];
$ret_arr = array();
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['color'], $row['background']);		// name, index, aprs - 1/5/09, 1/21/09
	}
unset($result);

function is_ok_coord($inval) {				// // 3/14/12
	return ((abs(floatval($inval) != 0.0)) && (floatval($inval) != $GLOBALS['NM_LAT_VAL']));
	}

$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	}

unset($result_st);
$query = "SELECT `m`.`_on` AS `updated`,
	`t`.`id` AS `type_id`, 
	`s`.`id` AS `status_id`, 
	`m`.`id` AS `member_id`, 
	`m`.`field6` AS `middle_name`, 
	`m`.`field1` AS `surname`, 
	`m`.`field2` AS `firstname`, 
	`m`.`field9` AS `street`, 
	`m`.`field10` AS `city`, 	
	`m`.`field11` AS `postcode`, 
	`m`.`field21` AS `member_status_id`, 
	`m`.`field7` AS `membertype`, 
	`m`.`field12` AS `lat`, 
	`m`.`field13` AS `lng`, 
	`m`.`field25` AS `contact`,
	`m`.`field20` AS `description`, 
	`m`.`field17` AS `joindate`, 
	`m`.`field16` AS `duedate`, 
	`m`.`field18` AS `dob`, 	
	`m`.`field19` AS `crb`, 
	`m`.`field4` AS `teamno`,
	`s`.`description` AS `stat_descr`, 
	`s`.`status_val` AS `status_name`, 
	`t`.`name` AS `type_name`, 	
	`t`.`background` AS `type_background`, 
	`t`.`color` AS `type_text`, 
	`s`.`background` AS `status_background`, 
	`s`.`color` AS `status_text`, 
	`m`.`field14` AS `medical` 
	FROM `$GLOBALS[mysql_prefix]member` `m` 
	LEFT JOIN `$GLOBALS[mysql_prefix]member_types` `t` ON ( `m`.`field7` = t.id )	
	LEFT JOIN `$GLOBALS[mysql_prefix]member_status` `s` ON ( `m`.`field21` = s.id ) 	
	LEFT JOIN `$GLOBALS[mysql_prefix]team` `te` ON ( `m`.`field3` = te.id ) 
	LEFT JOIN `$GLOBALS[mysql_prefix]user` `us` ON ( `m`.`id` = us.member ) 	
	WHERE `m`.`id` = " . $id;

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$units_ct = mysql_affected_rows();			// 1/4/10

$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = $ogts = $mob_tracker = FALSE;		//7/23/09

$utc = gmdate ("U");				// 3/25/09

$row = stripslashes_deep(mysql_fetch_assoc($result));
$latitude = ($row['lat']) ? $row['lat'] : get_variable('def_lat');		// 7/18/10		
$longitude = ($row['lng']) ? $row['lng'] : get_variable('def_lng');		// 7/18/10

$got_point = FALSE;
$tempname = $row['firstname'] . " " . $row['surname'];
$name = htmlentities($tempname,ENT_QUOTES);

// MAIL						
if (is_email($row['contact'])) {		// 2/1/10
	$mail_link = $row['contact'];
	} else {
	$mail_link = "";
	}

// STATUS
$status_name = $status_vals[$row['member_status_id']];
$status_id = $row['member_status_id'];

// as of - 7/2/2013
$updated = format_sb_date(strtotime($row['updated']));

$the_time = strtotime($row['updated']);
$toedit = (!(can_edit()))?				 								"" : "<A id='edit_" . $row['member_id'] . "' CLASS='plain text' style='float: none; color: #000000;' HREF='member.php?func=responder&edit=true&id=" . $row['member_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">Edit</A>";
$temp = $row['member_status_id'] ;
$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
$theTabs = "";
$lat = $row['lat'];
$lng = $row['lng'];
$locale = get_variable('locale');	// 08/03/09	
// tab 1
$temptype = (array_key_exists($row['type_id'], $u_types)) ? $u_types[$row['type_id']] : "Unset";
$temp_array[0] = $lat;
$temp_array[1] = $lng;
$temp_array[2] = addslashes(shorten($name, 48));
$temp_array[3] = addslashes(shorten(str_replace($eols, " ", $row['description']), 256));
$the_type = $temptype[0];																			// 1/1/09
$toosmap = ((!($internet)) || ($locale != 1))? "" : "<A id='osmap_but' class='plain text' style='float: none; color: #000000;' HREF='#' onClick = 'do_osmap({$temp_array[0]}, {$temp_array[1]}, {$row['member_id']}, &quot;" . $temp_array[2] . "&quot;, &quot;" . $temp_array[3] . "&quot;, \"member\");' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">OS Map</A>";

$theTabs .= "<div class='infowin'><BR />";
$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
$theTabs .= '<div class="tabArea">';
$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab2\', 2, ' . $lat . ',' . $lng . ');">Location</span>';
$theTabs .= '</div>';
$theTabs .= '<div class="contentwrapper">';

$tab_1 = "<TABLE width='280px' style='height: auto;'><TR><TD style='text-align: center;'><TABLE width='98%'>";
if ((!(is_ok_coord($row['lat']))) || (!(is_ok_coord($row['lng'])))) {
	$tab_1 .= "<TR CLASS='odd' style='background-color: red; color: #FFFFFF;'><TD COLSPAN=2 ALIGN='center' style='color: #FFFFFF;'><B>Bad Position Data - Pls Check</B></TD></TR>";
	}
$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' COLSPAN=2 ALIGN='center'><B>" . $name . "</B> - " . $the_type . "</TD></TR>";
$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text'>Description:</TD><TD CLASS='td_data_wrap text'>" . htmlentities(str_replace($eols, " ", $row['description']), ENT_QUOTES) . "</TD></TR>";
$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text'>Status:</TD><TD CLASS='td_data text'>" . $the_status . " </TD></TR>";
$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text'>Contact:</TD><TD CLASS='td_data text'>" . addslashes($row['contact']) . "</TD></TR>";
$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text'>As of:</TD><TD CLASS='td_data text'>" . format_date(strtotime($the_time)) . "</TD></TR>";		// 4/11/10
$tab_1 .= "</TABLE></TD></TR><TR><TD COLSPAN=99>&nbsp;</TD></TR>";
$tab_1 .= "<TR><TD COLSPAN=2 ALIGN='center'><TABLE style='width: 100%; background-color: #707070;'>";
$tab_1 .= "<TR style='height: 25px; vertical-align: middle;'><TD COLSPAN=2 style='vertical-align: middle; text-align: center;'>" . $toedit . "<A id='view_" . $row['member_id'] . "' CLASS='plain text' style='float: none; color: #000000;' HREF='member.php?func=responder&view=true&id=" . $row['member_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">View</A>";
if($toosmap != "") {
	$tab_1 .= $toosmap;
	}
$tab_1 .= "</TD></TR></TABLE></TD></TR>";
$tab_1 .= "</TABLE>";


// tab 2
$tab_2 = "<TABLE width='100%' style='height: auto;'><TR><TD>";
$tab_2 .= "<TABLE width='98%'>";
$tab_2 .= "<TR><TD class='td_label'>Lat</TD><TD class='td_data'>" . $lat . "</TD></TR>";
$tab_2 .= "<TR><TD class='td_label'>Lng</TD><TD class='td_data'>" . $lng . "</TD></TR>";
$tab_2 .= "</TABLE></TD></TR><TR><TD><TABLE width='100%'>";			// 11/6/08
$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'></DIV></CENTER></TD></TR>";
$tab_2 .= "</TABLE></TD</TR></TABLE>";
	
$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
$theTabs .= "<div class='content' id='content2' style = 'display: none;'>" . $tab_2 . "</div>";
$theTabs .= "</div>";
$theTabs .= "</div>";
$theTabs .= "</div>";
$ret_arr[0] = $theTabs;	

print json_encode($ret_arr);
exit();
?>