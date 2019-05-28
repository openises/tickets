<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
@session_start();
session_write_close();
//if($_GET['q'] != $_SESSION['id']) {
//	exit();
//	}
$ret_arr = array();
$id = $_GET['id'];
$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$use_twitter = (get_variable('twitter_consumerkey') != "" && get_variable('twitter_consumersecret') != "" && get_variable('twitter_accesstoken') != "" && get_variable('twitter_accesstokensecret') != "") ? true : false;
$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";
$locale = get_variable('locale');	// 08/03/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	}
	
function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}
	
function fac_cat($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_array($result));
	return $row['name'];
	}
	
function get_day() {
	$timestamp = (time() - (intval(get_variable('delta_mins'))*60));
//	if(strftime("%w",$timestamp)==0) {$timestamp = $timestamp + 86400;}
	return strftime("%A",$timestamp);
	}
	
function get_currenttime() {
	$timestamp = (time() - (intval(get_variable('delta_mins'))*60));
//	if(strftime("%w",$timestamp)==0) {$timestamp = $timestamp + 86400;}
	return strftime("%R",$timestamp);
	}
	
function isTimeBetween($lower, $higher) {
	$current_time = get_currenttime();
	$timecurrent = strtotime($current_time);
	$timelower = strtotime($lower);
	$timehigher = strtotime($higher);

	if($timecurrent >= $timelower && $timecurrent <= $timehigher) {
		return true;
		} else {
		return false;
		}
	}
	
$f_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);	

$query_fac = "SELECT *,`$GLOBALS[mysql_prefix]facilities`.`updated` AS `updated`, 
	`$GLOBALS[mysql_prefix]facilities`.`id` 						AS `fac_id`, 
	`$GLOBALS[mysql_prefix]fac_types`.`id` 							AS `type_id`,
	`$GLOBALS[mysql_prefix]facilities`.`description` 				AS `facility_description`,
	`$GLOBALS[mysql_prefix]facilities`.`boundary` 					AS `boundary`,		
	`$GLOBALS[mysql_prefix]fac_types`.`name` 						AS `fac_type_name`, 
	`$GLOBALS[mysql_prefix]fac_types`.`icon` 						AS `icon`, 
	`$GLOBALS[mysql_prefix]facilities`.`name` 						AS `facility_name`, 
	`$GLOBALS[mysql_prefix]fac_status`.`status_val` 				AS `fac_status_val`, 
	`$GLOBALS[mysql_prefix]facilities`.`status_id` 					AS `fac_status_id`
	FROM `$GLOBALS[mysql_prefix]facilities`
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 	ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = 			`$GLOBALS[mysql_prefix]allocates`.`resource_id` )	
	LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` 	ON (`$GLOBALS[mysql_prefix]facilities`.`type` = 		`$GLOBALS[mysql_prefix]fac_types`.`id` )
	LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` 	ON (`$GLOBALS[mysql_prefix]facilities`.`status_id` = 	`$GLOBALS[mysql_prefix]fac_status`.`id` )
	WHERE `$GLOBALS[mysql_prefix]facilities`.`id` = " . $id;											// 3/15/11, 6/10/11

$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
$facs_ct = mysql_affected_rows();			// 1/4/10

$row_fac = mysql_fetch_assoc($result_fac);
$name = htmlentities($row_fac['facility_name'],ENT_QUOTES);
$handle = htmlentities($row_fac['handle'],ENT_QUOTES);
$address = $row_fac['street'] . ", " . $row_fac['city'] . ", " . $row_fac['state'];
$description = $row_fac['facility_description'];

$fac_id=$row_fac['fac_id'];
$fac_type=$row_fac['icon'];
$fac_type_name = $row_fac['fac_type_name'];
$fac_region = get_first_group(3, $fac_id);		

$fac_index = $row_fac['icon_str'];	

$latitude = $row_fac['lat'];
$longitude = $row_fac['lng'];

$facility_display_name = $f_disp_name = $row_fac['facility_name'];	
$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row_fac['icon']];		// 2/8/10
$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row_fac['icon']];		// 2/8/10			
// BEDS
$beds_info = "<TD ALIGN='right'>{$row_fac['beds_a']}/{$row_fac['beds_o']}</TD>";
// STATUS
$status = get_status_sel($row_fac['fac_id'], $row_fac['fac_status_id'], "f");		// status, 3/15/11
$status_id = $row_fac['fac_status_id'];
$temp = $row_fac['status_id'] ;
$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
// AS-OF - 11/3/2012
$updated = format_sb_date_2 ( $row_fac['updated'] );

if(is_guest() || is_unit()) {
	$toedit = $tomail = "";
	} else {
	$toedit = "<A id='edit_" . $row_fac['fac_id'] . "' CLASS='plain text' style='float: none; color: #000000;' HREF='{$_SESSION['facilitiesfile']}?func=responder&edit=true&id=" . $row_fac['fac_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">Edit</A>";
	if ((may_email()) && (is_email($row_fac['contact_email']))) {
		$tomail = "<SPAN id='mail_" . $row_fac['fac_id'] . "' CLASS='plain text' style='float: none; color: #000000;' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\" onClick = 'do_fac_mail_win(" . $row_fac['fac_id'] . ", \"" . $row_fac['contact_email'] . "\");'>Email</SPAN>";
		} elseif((may_email()) && (is_email($row_fac['security_email']))){
		$tomail = "<SPAN id='mail_" . $row_fac['fac_id'] . "' CLASS='plain text' style='float: none; color: #000000;' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\" onClick = 'do_fac_mail_win(" . $row_fac['fac_id'] . ", \"" . $row_fac['security_email'] . "\");'>Email</SPAN>";
		} else {
		$tomail = "";
		}
	}
	


$temptype = $f_types[$row_fac['type_id']];
$the_type = $temptype[0];
$line_ctr = 0;
$temp_array[0] = $row_fac['lat'];
$temp_array[1] = $row_fac['lng'];
$temp_array[2] = htmlentities(shorten($facility_display_name, 48), ENT_QUOTES);
$temp_array[3] = htmlentities(shorten(str_replace($eols, " ", $facility_display_name), 48), ENT_QUOTES);
$theTabs = "<div class='infowin'><BR />";
$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
$theTabs .= '<div class="tabArea">';
$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row_fac['lat'] . ',' . $row_fac['lng'] . ');">Location</span>';
$theTabs .= '</div>';
$theTabs .= '<div class="contentwrapper">';		

$tab_1 = "<TABLE width='280px' style='height: auto;'><TR><TD><TABLE width='98%'>";	
$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' COLSPAN=2 ALIGN='center'><B>" . htmlentities(shorten($facility_display_name, 48), ENT_QUOTES) . "</B> - " . $the_type . "</TD></TR>";
$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text' ALIGN='right'>Description:&nbsp;</TD><TD CLASS='td_data_wrap text' ALIGN='left'>" . htmlentities(shorten(str_replace($eols, " ", $row_fac['facility_description']), 32), ENT_QUOTES) . "</TD></TR>";
$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' ALIGN='right'>Status:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . $the_status . " </TD></TR>";
$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' ALIGN='right'>As of:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . format_date(strtotime($row_fac['updated'])) . "</TD></TR>";
$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text' ALIGN='right'>Contact:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . addslashes($row_fac['contact_name']). " Via: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
if(!(isempty(trim($row_fac['security_contact']))))	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text' ALIGN='right' STYLE= 'width:50%'>Security contact:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left' STYLE= 'width:50%'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";}
if(!(isempty(trim($row_fac['security_email']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' ALIGN='right'>Security email:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";}
if(!(isempty(trim($row_fac['security_phone']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text' ALIGN='right'>Security phone:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";}
if(!(isempty(trim($row_fac['access_rules']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' ALIGN='right'>" . get_text("Access rules") . ":&nbsp;</TD><TD CLASS='td_data_wrap text' ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";}
if(!(isempty(trim($row_fac['security_reqs']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text' ALIGN='right'>Security reqs:&nbsp;</TD><TD CLASS='td_data_wrap text' ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";}
if(!(isempty(trim($row_fac['opening_hours']))))  	{
	$opening_arr_serial = base64_decode($row_fac['opening_hours']);
	$opening_arr = unserialize($opening_arr_serial);
	$outputstring = "";
	$the_day = "";
	$z = 0;
	foreach($opening_arr as $val) {
		switch($z) {
			case 0:
			$dayname = "Monday";
			break;
			case 1:
			$dayname = "Tuesday";
			break;
			case 2:
			$dayname = "Wednesday";
			break;
			case 3:
			$dayname = "Thursday";
			break;
			case 4:
			$dayname = "Friday";
			break;
			case 5:
			$dayname = "Saturday";
			break;
			case 6:
			$dayname = "Sunday";
			break;
			}

		if($dayname == get_day()) {			
			$openstring = (array_key_exists(0, $val) && $val[0] == "on") ? "Open" : "Closed";
			if($openstring == "Open") {
				$outputstring .= "Opens: " . $val[1] . "<BR />Closes: " . $val[2];
				if(isTimeBetween($val[1], $val[2])) {
					$calculatedStatus = 1;
					} else {
					$calculatedStatus = 0;
					}
				$calculatedStatus = 1;
				} else {
				$outputstring .= "(" . $dayname . ")  ---  " . $openstring;
				$calculatedStatus = 0;
				}
			}
		$z++;
		}
	$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' ALIGN='right'>Opening Times<BR />(" . get_day() . "):</TD><TD CLASS='td_data text' ALIGN='left'>" . $outputstring . "</TD></TR>";
	}
if(!(isempty(trim($row_fac['pager_p']))))  			{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text' ALIGN='right'>Prim pager:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";}
if(!(isempty(trim($row_fac['pager_s']))))  			{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text' ALIGN='right'>Sec pager:&nbsp;</TD><TD CLASS='td_data text' ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";}
$tab_1 .= "</TABLE></TD></TR>";
$tab_1 .= "<TR><TD COLSPAN=99>&nbsp;</TD></TR>";
$tab_1 .= "<TR><TD COLSPAN=2 ALIGN='center'><TABLE>";
$tab_1 .= "<TR style='height: 20px;'><TD COLSPAN=2 ALIGN='center'>" . $toedit . $tomail;
$tab_1 .= "<A id='view_" . $row_fac['fac_id'] . "' CLASS='plain text' style='float: none; color: #000000;'  HREF='{$_SESSION['facilitiesfile']}?func=responder&view=true&id=" . $row_fac['fac_id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">View</A>";
if(($internet) && ($locale == 1)) {
	$tab_1 .= "<A id='osmap_but' class='plain text' style='float: none; color: #000000;' HREF='#' onClick = 'do_osmap({$temp_array[0]}, {$temp_array[1]}, {$row_fac['fac_id']}, &quot;" . $temp_array[2] . "&quot;, &quot;" . $temp_array[3] . "&quot;, \"facility\");' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">OS Map</A></TD></TR>";
		}
if ($use_twitter) {							//7/23/15
	$theInformation = $row_fac['facility_name'] . " at " . $address . ", " . $description . ". <small>Latitude: " . $row_fac['lat'] . ", Longitude: " . $row_fac['lng'] . "</small>";
	$tab_1 .= 	"<TR style='height: 25px;'>
		<TD style='text-align: center;'>
			<IMG id='twit_" . $fac_id . "' class='plain text' SRC='./buttons/tweetbutton.png' style='float: none; margin: 0px; padding: 0px; vertical-align: middle;' 
			onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= 'tweetInfo(\"" . $theInformation . "\");'>";
		}
$tab_1 .= "</TD></TR>";
$tab_1 .= "</TABLE></TD></TR></TABLE>";
	
if (my_is_float($row_fac['lat'])) {										// position data of any type?
	$tab_2 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
	$tab_2 .= "<TABLE width='98%'>";

	switch($locale) { 
		case "0":
		$tab_2 .= "<TR CLASS='odd'><TD class='td_label text' ALIGN='left'>USNG:</TD><TD CLASS='td_data text' ALIGN='left'>" . LLtoUSNG($row_fac['lat'], $row_fac['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "1":
		$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label text' ALIGN='left'>OSGB:</TD><TD CLASS='td_data text' ALIGN='left'>" . LLtoOSGB($row_fac['lat'], $row_fac['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $row_fac['lat'] . "," . $row_fac['lng'];							// 8/12/09
		$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label text' ALIGN='left'>UTM:</TD><TD CLASS='td_data text' ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
	$tab_2 .= "<TR><TD class='td_label text' style='font-size: 80%;'>Lat</TD><TD class='td_data text' style='font-size: 80%;'>" . $row_fac['lat'] . "</TD></TR>";
	$tab_2 .= "<TR><TD class='td_label text' style='font-size: 80%;'>Lng</TD><TD class='td_data text' style='font-size: 80%;'>" . $row_fac['lng'] . "</TD></TR>";
	$tab_2 .= "</TABLE></TD></TR><TR><TD><TABLE width='100%'>";			// 11/6/08
	$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
	$tab_2 .= "</TABLE></TD</TR></TABLE>";
		
	$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
	$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_2 . "</div>";
	$theTabs .= "</div>";
	$theTabs .= "</div>";
	$theTabs .= "</div>";
	$line_ctr++;
	}		// end if/else

$ret_arr[0] = $theTabs;	
	
print json_encode($ret_arr);
exit();
?>