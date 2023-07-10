<?php

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
if (empty($_SESSION)) {
	header("Location: index.php");
	}

require_once('../incs/functions.inc.php');
$ret_arr = array();
$good_internet = ($_SESSION['good_internet']) ? $_SESSION['good_internet'] : 0;
$gmaps = $_SESSION['internet'];
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$output = "";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$states[$row['name']] = $row['code'];
	}

if(empty($_GET)) {
	if (mysql_table_exists("$GLOBALS[mysql_prefix]caller_id")) {				// 6/9/11
		$cid_calls = $cid_name = $cid_phone = $cid_street = $cid_city = $cid_state = $cid_lat = $cid_lng = "";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]caller_id` WHERE `status` = 0 LIMIT 1;";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_num_rows($result)> 0) {							// build return string from newest incident data
			$row = stripslashes_deep(mysql_fetch_array($result));
			$query = "UPDATE `$GLOBALS[mysql_prefix]caller_id` SET `status` = 1 WHERE `id` = " . quote_smart($row['id']);
			$result = mysql_query($query);
			$lookup_vals = explode (";", $row['lookup_vals']);
			$cid_calls = $lookup_vals[0];
			$cid_name = $lookup_vals[1];
			$cid_phone = $lookup_vals[2];
			$cid_street = $lookup_vals[3];
			$cid_city = $lookup_vals[4];
			$cid_state = $lookup_vals[5];
			$cid_lat = $lookup_vals[7];
			$cid_lng = $lookup_vals[8];
			$cid_id = $row['id'];			// id of caller id record
			}
		}				// end if(empty())
	}

$current_facilities = array();												// 9/22/09
$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `id`";		// types in use
$result_f = mysql_query($query_f) or do_error($query_f, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_f = stripslashes_deep(mysql_fetch_assoc($result_f))) {
	$current_facilities [$row_f['id']] = array ($row_f['name'], $row_f['lat'], $row_f['lng']);
	}
$facilities = mysql_affected_rows();		// 3/24/10

$protocols = array();
$query_in = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
$result_in = mysql_query($query_in);
while ($row_in = stripslashes_deep(mysql_fetch_assoc($result_in))) {
	if($row_in['protocol'] != "") {$protocols[$row_in['id']] = addslashes($row_in['protocol']);}
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_mi = mysql_num_rows($result);

function get_res_row() {				// writes empty ticket if none exists - returns a row - 11/5/10
	$by = $_SESSION['user_id'];			// 5/27/10

	$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`
		WHERE `status`= '{$GLOBALS['STATUS_RESERVED']}'
		AND  `_by` = '{$by}' LIMIT 1";

	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result) == 1) {							// any ?
		$row = stripslashes_deep(mysql_fetch_array($result));	// yes, return it
		}
	else {				// insert empty STATUS_RESERVED row
		$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`id` , `in_types_id` , `contact` , `street` , `address_about` , `city` , `state` , `phone` , `to_address` , `lat` , `lng` , `date` ,
				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` ,
				`severity` , `updated`, `booked_date`, `_by`
			) VALUES (
				NULL , 0, 0, NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL ,
				NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL, NULL, $by
			)";	//	9/10/13

		$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		}

	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));			// get the reserved row
	return $row;													// and return it - 11/5/10
	}						// end function get_res_row()


$res_row = get_res_row();

$ticket_id = $res_row['id'];
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");


$titles = array();				// 2/1/11

$query = "SELECT `tag`, `hint` FROM `$GLOBALS[mysql_prefix]hints`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while ($row =stripslashes_deep(mysql_fetch_assoc($result))) {
	$titles[trim($row['tag'])] = trim($row['hint']);
		}

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}

if(!isset($curr_viewed)) {
	if(empty($al_groups)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
		$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
		} else {
		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
		}
	} else {
	if(empty($curr_viewed)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
		$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
		} else {
		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($curr_viewed as $grp) {	//	6/10/11
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
		}
	}

	// Pulldown menu for use of Incident set at Facility 9/22/09, 3/18/10 - 2/12/11
$query_fc = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
	$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$pulldown = '<option value=0 selected>Incident at Facility</option>\n';	// 3/18/10
	while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
		$pulldown .= "<option value=\"{$row_fc['fac_id']}\">" . shorten($row_fc['name'], 30) . "</option>\n";
		}

	// Pulldown menu for use of receiving Facility 10/6/09, 3/18/10
$query_rfc = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
	$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$pulldown2 = '<option value = 0 selected>Receiving facility</option>\n'; 	// 3/18/10
	while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
		$pulldown2 .= "<option value=\"{$row_rfc['fac_id']}\">" . shorten($row_rfc['name'], 30) . "</option>\n";
		}

	// Pulldown menu for portal user association 9/10/13
$query_pu = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = " . $GLOBALS['LEVEL_SERVICE_USER'] . " ORDER BY `name_l` ASC";
$result_pu = mysql_query($query_pu) or do_error($query_pu, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_affected_rows() > 0) {
	$has_portal = 1;
	$portal_user_control = "<SELECT ID='portal_user' NAME='frm_portal_user'>\n";
	$portal_user_control .= "<OPTION VALUE = 0 SELECTED>Select User</OPTION>\n";
	while ($row_pu = mysql_fetch_array($result_pu, MYSQL_ASSOC)) {
		$theName = $row_pu['name_f'] . " " . $row_pu['name_l'] . " (" . $row_pu['user'] . ")";
		$portal_user_control .= "<OPTION VALUE=" . $row_pu['id'] . ">" . $theName . "</OPTION>\n";
		}
	$portal_user_control .= "</SELECT>\n";
	} else {
	$has_portal = 0;
	}

if (!(mysql_table_exists("$GLOBALS[mysql_prefix]places"))) {
	$city_name_array_str="";
	} else {		// 2/21/11 - build array of city names for JS usage
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` =  'city' ORDER BY `id`";		// get all city names
	$place_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_places = mysql_num_rows($result);	//	3/17/11
	$city_name_array_str = $sep = "";
	while ($place_row = stripslashes_deep(mysql_fetch_assoc($place_result))) {
		$city_name_array_str .= "{$sep}'{$place_row['name']}'";
		$sep =",";
		}
	}
						// apply cid data if available
$street = isset($cid_street) ?  $cid_street : $res_row['street'] ;

if (isset($cid_city)) {
	$city = $cid_city;
	} else {
	$city = (!(empty($res_row['city'])))?	$res_row['city']  : get_variable('def_city');		// 11/5/10, 3/17/11
	}
if (isset($cid_state)) {
	$st = $cid_state;
	} else {
	$st = (!(empty($res_row['state'])))?	$res_row['state'] : get_variable('def_st') ;
	}
$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10
$phone = (isset($cid_phone))? format_phone($cid_phone): get_variable('def_area_code');
$reported_by = (isset($cid_name))?	$cid_name: "TBD";

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	} else {
	$curr_viewed = $al_groups;
	}

$curr_names="";	//	6/10/11
$z=0;	//	6/10/11
foreach($curr_viewed as $grp_id) {	//	6/10/11
	$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
	$curr_names .= get_groupname($grp_id);
	$curr_names .= $counter;
	$z++;
	}

$heading = "Add Ticket - " . get_variable('map_caption');

$cid_lat = isset($cid_lat) ? $cid_lat : "";
$cid_lng = isset($cid_llng) ? $cid_lng : "";
$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
$onload_str .= (is_float($cid_lat))? " pt_to_map( add, {$cid_lat} ,{$cid_lng});": "";
$doloc = intval(get_variable('add_uselocation'));
$loc_startup = ($doloc == 1) ? "getLocation();" : "";

$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";
$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_num_rows($result_bldg) > 0) {
	$i = 0;
	$sel_str = "<select tabindex=1 id='sel_bldg' name='bldg' onChange = 'do_bldg(this.options[this.selectedIndex].value); '>\n";
	$sel_str .= "\t<option value = '' selected>Select building</option>\n";
	echo "\n\t var bldg_arr = new Array();\n";
	while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
//			4/27/2014
		$sel_str .= "\t<option value = {$i} >{$row_bldg['name']}</option>\n";
		echo "\t var bldg={ bldg_name:\"{$row_bldg['name']}\", bldg_street:\"{$row_bldg['street']}\", bldg_city:\"{$row_bldg['city']}\",
			bldg_state:\"{$row_bldg['state']}\", bldg_lat:\"{$row_bldg['lat']}\", bldg_lon:\"{$row_bldg['lon']}\", bldg_info:\"{$row_bldg['information']}\"};\n";
		echo "\t bldg_arr.push(bldg);\n";
		$i++;
		}

	$sel_str .= "\t</SELECT>\n";
	}		// end if (mysql... )

$addr_sugg_str = (intval (get_variable('addr_source')) == 0 )? "" : " onkeyup=\"get_addr_list(this.value);\"  autocomplete=\"off\"";

$output .= "<div id = 'bldg_info' class = 'even' style = 'display: none; position:fixed; left:500px; top:30px; z-index: 998; width:300px; height:auto;'></div>";
$output .= "<DIV id ='outer' style='position: absolute; top: 40px; width: 95%;'>";
$heading = "<DIV>";
$heading .= "<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>New Incident</SPAN>";
$heading .= "<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.log_form.reset(); init();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
$heading .= "<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.log_form.submit();'><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
$heading .= "</DIV>";
$output .= "<FORM NAME='add' METHOD='post' ENCTYPE='multipart/form-data' ACTION='" . basename(__FILE__) . "?add=true' onSubmit='return validate(document.add);'>";
$output .= "<FIELDSET>";
$output .= "<LEGEND class='text_large text_bold'>Start Here</LEGEND>";
$output .= "<DIV style='position: relative;'>";
$output .= "<LABEL for='sel_in_types_id' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_nature'] . "');\">" . $nature . ": <font color='red' size='-1'>*</font></LABEL>";
$output .= "<SELECT id='sel_in_types_id' NAME='frm_in_types_id' tabindex=60 onChange='do_set_severity (this.selectedIndex); do_inc_protocol(this.options[selectedIndex].value.trim());'>";
$output .= "<OPTION VALUE=0 SELECTED>TBD</OPTION>";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$the_grp = strval(rand());			//  force initial optgroup value
$i = 0;
while ($temp_row = stripslashes_deep(mysql_fetch_array($temp_result))) {
	if ($the_grp != $temp_row['group']) {
		$output .= ($i == 0)? "": "</OPTGROUP>\n";
		$the_grp = $temp_row['group'];
		$output .= "<OPTGROUP LABEL='{$temp_row['group']}'>\n";
		}
	$color = $temp_row['color'];
	$bgcolor = "white";
	$output .= "\t<OPTION VALUE=' {$temp_row['id']}' CLASS='{$temp_row['group']}' style='color: {$color}; background-color: {$bgcolor};' title='" . addslashes($temp_row['description']) . "'> " . addslashes($temp_row['type']) . " </OPTION>\n";
	$i++;
	}		// end while()
$output .= "\n</OPTGROUP>\n";
$output .= "</SELECT>";
$output .= "<BR />";
$output .= "<LABEL for='severity' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_prio'] . "');\">" . get_text('Priority') . ": <font color='red' size='-1'>*</font></LABEL>";
$output .= "<SELECT id='severity' NAME='frm_severity' tabindex=70>";
$output .= "<OPTION VALUE='0' SELECTED>" . get_severity($GLOBALS['SEVERITY_NORMAL']) . "</OPTION>";
$output .= "<OPTION VALUE='1'>" . get_severity($GLOBALS['SEVERITY_MEDIUM']) . "</OPTION>";
$output .= "<OPTION VALUE='2'>" . get_severity($GLOBALS['SEVERITY_HIGH']) . "</OPTION>";
$output .= "</SELECT>";
$output .= "<BR />";
$output .= "<LABEL onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_proto'] . "');\">" . get_text('Protocol') . ":</LABEL>";
$output .= "<SPAN ID='proto_cell'></SPAN>";
$output .= "<BR />";
$output .= "<LABEL for='street' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_loca'] . "');\">" . get_text('Location') . ":</LABEL>";
$output .= "<INPUT id='street' NAME='frm_street' tabindex=20 SIZE='64' TYPE='text' VALUE='" . $street . "' MAXLENGTH='96' " . $addr_sugg_str . " />";
$output .= "<DIV ID='addr_list' style = 'display:inline;'></DIV>";
$output .= "<BR />";
$output .= "<LABEL for='about' onmouseout='UnTip();' onmouseover=\"Tip('About Address - for instance, round the back, building number etc.');\">" . get_text('Address About') . ":</LABEL>";
$output .= "<INPUT id='about' NAME='frm_address_about' tabindex=30 SIZE='64' TYPE='text' VALUE='' MAXLENGTH='512' />";
$output .= "<BR />";
$output .= "<LABEL for='my_txt' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_city'] . "');\">" . get_text('City') . ":</LABEL>";
if($gmaps || $good_internet) {
	$output .= "<BUTTON type='button' onClick='Javascript:loc_lkup(document.add);return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;";
	}
$output .= "<INPUT ID='my_txt' onFocus = \"createAutoComplete(); $('city_reset').visibility='visible';\" NAME='frm_city' autocomplete='off' tabindex=30 SIZE='32' TYPE='text' VALUE='" . $city . "' MAXLENGTH='32' onChange = \"$('city_reset').visibility='visible'; this.value=capWords(this.value);\">";
$output .= "<span id='suggest' onmousedown=\"$('suggest').style.display='none'; $('city_reset').style.visibility='visible';\" style='visibility: hidden; border: #000000 1px solid; width: 150px; right:400px;\" /></span>";
$output .= "<IMG ID = 'city_reset' SRC='./markers/reset.png' STYLE = 'margin-left: 20px; visibility: hidden;' onClick = \"this.style.visibility='hidden'; document.add.frm_city.value=''; document.add.frm_city.focus(); obj_sugg = null;\" />";
if ($gmaps) {		// 12/1/2012
	$output .= "<BUTTON type='button' onClick=\"Javascript:do_nearby(this.form);return false;\">Nearby?</BUTTON>";
	}
$output .= "<LABEL for='state' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_state'] . "');\">" . get_text('St') . "<font color='red' size='-1'>*</font></LABEL>";
$output .= "<INPUT ID='state' NAME='frm_state' tabindex=40 SIZE='" . $st_size . "' TYPE='text' VALUE='" . $st . "' MAXLENGTH='" . $st_size . "' />";
$output .= "<BR />";
$output .= "<LABEL for='toaddress' onmouseout='UnTip();' onmouseover=\"Tip('To address - Not plotted on map, for information only');\">" . get_text("To Address") . ":</LABEL>";
$output .= "<INPUT id='toaddress' NAME='frm_to_address' tabindex=50 SIZE='72' TYPE='text' VALUE=''  MAXLENGTH='1024'>";
$output .= "<BR />";
$output .= "<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 300px; font-size: 1.5em; font-weight: bold; border: 1px outset #707070;'></DIV>";
$output .= "<BR />";
if ($gmaps || $doloc|| $good_internet) {
	$output .= "<LABEL for='lock_p' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles["_coords"] . "');\">" . $incident . " Lat/Lng: <font color='red' size='-1'>*</font>";
	$output .= "<img id='lock_p' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle; margin-left: 20px;' onClick = 'do_unlock_pos(document.add);'>";
	$output .= "</LABEL>";				
	$output .= "<INPUT ID='show_lat' SIZE='11' TYPE='text' NAME='show_lat' VALUE='' />";
	$output .= "<INPUT ID='show_lng' SIZE='11' TYPE='text' NAME='show_lng' VALUE='' />";
	$output .= "<BR />";
	$locale = get_variable('locale');
	$grid_types = array("USNG", "OSGB", "UTM");
	$output .= "<LABEL for='griddisp' onmouseout='UnTip();' onmouseover=\"Tip('Grid Reference');\" onClick = 'do_grid_to_ll();'>" . $grid_types[$locale] . ":</LABEL>";
	$output .= "<INPUT ID='griddisp' SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='' DISABLED />";
	$output .= "<BR />";
	} else {
	$output .= "<INPUT TYPE='hidden' NAME='show_lat' VALUE='' />";
	$output .= "<INPUT TYPE='hidden' NAME='show_lng' VALUE='' />";
	}		// end else

$output .= "<CENTER>";
$output .= "<SPAN style='text-align: center;'><IMG SRC='glasses.png' BORDER='0'/>: Lookup </SPAN><BR />";
$output .= "</CENTER>";
$output .= "</DIV>";
$output .= "</FIELDSET>";
$output .= "<FIELDSET>";
$output .= "<LEGEND class='text_large text_bold'>General</LEGEND>";
$output .= "<DIV style='position: relative;'>";
if (empty($inc_name)) {
	switch (get_variable('serial_no_ap')) {
		case 0:								/*  no serial no. */
			$prepend = $append = "";
			break;
		case 1:								/*  prepend  */
			$prepend = $ticket_id . "/";
			$append = "";
			break;
		case 2:								/*  append  */
			$prepend = "";
			$append = "/" . $ticket_id;
			break;
		default:							/* error????  */
			$prepend = $append = " error ";
		}				// end switch()
	}				// end if (empty($inc_name))
			
$output .= "<LABEL for='scope' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_name'] . "');\">" . get_text('Incident name') . ": <font color='red' size='-1'>*</font></LABEL>";
if (!(empty($inc_name))) {
	$output .= "<INPUT id='scope' NAME='frm_scope' tabindex=120 SIZE='56' TYPE='text' VALUE='" . $inc_name . "' MAXLENGTH='61' />";
	} else {
	$output .= "<INPUT id='scope' NAME='frm_scope' tabindex=130 SIZE='56' TYPE='text' VALUE='TBD' MAXLENGTH='61' onFocus =\"Javascript: if (this.value.trim()=='TBD') {this.value='';}\" onkeypress='user_inc_name = true;'/>" . $append . " />";
	}

$output .= "<BR />";
$output .= "<LABEL onmouseout='UnTip();' onmouseover=\"Tip('Additional Information');\">" . get_text('Add\'l') . ":</LABEL>";
$output .= "<SPAN ID='td_misc' CLASS='td_data text'></SPAN>";
$output .= "<BR />";
$output .= "<LABEL for='description' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_synop'] . "');\">" . get_text('Synopsis') . ": <font color='red' size='-1'>*</font></LABEL>";
$output .= "<TEXTAREA id='description' NAME='frm_description' tabindex=80 COLS='48' ROWS='2' WRAP='virtual'></TEXTAREA>";
$output .= "<BR />";

$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);
if (mysql_num_rows($result_sigs)>0) {
	$output .= "<LABEL for='signals' onmouseout='UnTip();' onmouseover=\"Tip('Signal');\">Signal &raquo;</LABEL>";
	$output .= "<SELECT ID='signals' NAME='signals' tabindex=90 onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
	$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
		$output .= "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
		}
	$output .= "</SELECT>";
	$output .= "<BR />";
	}
if (mysql_num_rows($result_bldg) > 0) {
	$output .= "<LABEL for='sel_bldg'>" . get_text('Building') . ":</LABEL>";
	$output .= $sel_str;
	$output .= "<BR />";
	}
if(get_num_groups()) {
	if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
		$output .= "<BR />";
		$output .= "<LABEL onmouseout='UnTip()' onmouseover=\"Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Regions') . ":";
		$output .= "<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center; float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick=\"$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\"><B>+</B></SPAN>";
		$output .= "<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick=\"$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\"><B>-</B></SPAN>";
		$output .= "</LABEL>";
		$output .= "<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>";
		$output .= "<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>";
		$output .= "<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>";
		$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
		$output .= get_user_group_butts(($_SESSION['user_id']));
		$output .= "</DIV>";
		$output .= "<BR />";
		} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
		$output .= "<BR />";
		$output .= "<LABEL onmouseout='UnTip()' onmouseover=\"Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Regions') . ":";
		$output .= "<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick=\"$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\"><B>+</B></SPAN>";
		$output .= "<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick=\"$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\"><B>-</B></SPAN>";
		$output .= "</LABEL>";
		$output .= "<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>";
		$output .= "<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>";
		$output .= "<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>";
		$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
		$output .= get_user_group_butts(($_SESSION['user_id']));
		$output .= "</DIV>";
		$output .= "<BR />";
		} else {
		$output .= "<BR />";
		$output .= "<LABEL onmouseout='UnTip()' onmouseover=\"Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');\">" . get_text('Regions') . ":";
		$output .= "<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick=\"$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';\"><B>+</B></SPAN>";
		$output .= "<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick=\"$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';\"><B>-</B></SPAN>";
		$output .= "</LABEL>";
		$output .= "<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>";
		$output .= "<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>";
		$output .= "<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>";
		$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
		$output .= get_user_group_butts_readonly($_SESSION['user_id']);
		$output .= "</DIV>";
		$output .= "<BR />";
		}
	} else {
	$output .= "<INPUT TYPE='hidden' NAME='frm_group[]' VALUE='1'>";
	}
$output .= "<LABEL for='sel_maj_inc'>" . get_text('Major Incident') . ":</LABEL>";
$output .= "<SELECT ID='sel_maj_inc' NAME='frm_maj_inc'>";
$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `mi_status` = 'Open' OR `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00' ORDER BY `id` ASC";
$result_mi = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while ($row_mi = stripslashes_deep(mysql_fetch_assoc($result_mi))) {
	$output .= "\t<OPTION VALUE='" . $row_mi['id'] . "'>" . $row_mi['name'] . "</OPTION>\n";
	}
$output .= "</SELECT>";
$output .= "<BR />";
if ($facilities > 0) {
	$output .= "<LABEL for='facy' onmouseout='UnTip();' onmouseover=\"Tip('Incident located at Facility');\">" . get_text('Incident at Facility') . "?:</LABEL>";
	$output .= "<SELECT id='facy' NAME='frm_facility_id' tabindex=140 onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>" . $pulldown . "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;";
	$output .= "<BR />";
	$output .= "<LABEL for='recfacy' onmouseout='UnTip();' onmouseover=\"Tip('Receiving Facility');\">" . get_text('Receiving Facility') . "?:</LABEL>";
	$output .= "<SELECT id='recfacy' NAME='frm_rec_facility_id' onFocus =\"Javascript: if (this.value.trim()=='TBD') {this.value='';}\">" . $pulldown2 . "</SELECT>";
	$output .= "<BR />";
	} else {		// end if ($facilities > 0)
	$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_facility_id' VALUE=''>";
	$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_rec_facility_id' VALUE=''>";
	$output .= "<BR />";
	}
$output .= "</DIV>";
$output .= "</FIELDSET>";
$output .= "<FIELDSET>";
$output .= "<LEGEND class='text_large text_bold'>Contacts</LEGEND>";
$output .= "<DIV style='position: relative;'>";
$output .= "<LABEL for='frm_phone' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_phone'] . "');\">" . get_text('Phone') . ":</LABEL>";
$output .= "<INPUT ID='frm_phone' NAME='frm_phone' tabindex=50 SIZE='16' TYPE='text' VALUE='" . $phone . "' MAXLENGTH='16' />";
if(get_variable('locale') == 0) {
	$output .= "<BUTTON type='button' onClick=\"Javascript:phone_lkup(document.add.frm_phone.value);\"><img src='./markers/glasses.png' alt='Lookup phone no.' /></button>&nbsp;&nbsp;";
	}
$output .= "<SPAN ID='repeats'></SPAN>";
$output .= "<BR />";
$output .= "<LABEL for='contact' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_caller'] . "');\">" . get_text('Reported by') . ":&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></LABEL>";
$output .= "<INPUT id='contact' NAME='frm_contact' tabindex=110 SIZE='56' TYPE='text' VALUE='" . $reported_by . "' MAXLENGTH='48' onFocus =\"Javascript: if (this.value.trim()=='TBD') {this.value='';}\">";
$output .= "<BR />";
if($has_portal == 1) {
	$output .= "<LABEL for='contact' onmouseout='UnTip();' onmouseover=\"Tip('Associate this ticket with a specific portal user so they can see it.');\">" . get_text('Portal User') . ":</LABEL>";
	$output .= $portal_user_control;				
	$output .= "<BR />";
	} else {
	$output .= "<INPUT TYPE='hidden' NAME='frm_portal_user' VALUE=0>";
	}
$output .= "</DIV>";
$output .= "</FIELDSET>";
$output .= "<FIELDSET>";
$output .= "<LEGEND class='text_large text_bold'>Call History</LEGEND>";
$output .= "<DIV style='position: relative;'>";
$output .= "<LABEL for='911' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_911'] . "');\">" . get_text('911 Contacted') . ":</LABEL>";
$output .= "<INPUT id='911' NAME='frm_nine_one_one' tabindex=100 SIZE='56' TYPE='text' VALUE='' MAXLENGTH='96' />&nbsp;";
$output .= "<SPAN id='now_but' CLASS='plain text' style='width: auto; display: inline; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"javascript:var now = new Date(); document.add.frm_nine_one_one.value=now.getDate()+'/' + (now.getMonth()+1) + '/' + now.getFullYear() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();\">Now</SPAN>";
$output .= "<BR />";
$output .= "<LABEL for='comments' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_disp'] . "');\">" . $disposition . ":</LABEL>";
$output .= "<TEXTAREA id='comments' NAME='frm_comments' COLS='45' ROWS='2' WRAP='virtual'></TEXTAREA>";
$output .= "<BR />";
if (mysql_num_rows($result_sigs)>0)	{
	$output .= "<LABEL for='signals2' onmouseout='UnTip();' onmouseover=\"Tip('Signal');\">" . get_text('Signal') . ":&raquo;</LABEL>";
	$output .= "<SELECT id='signals2' NAME='signals' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
	$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
	$result_sigs = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result_sigs))) {
		$output .= "\t<OPTION VALUE='" . $row_sig['code'] . "'>" . $row_sig['code'] . "|" . shorten($row_sig['text'], 32) . "</OPTION>\n";
		}
	$output .= "</SELECT>";
	$output .= "<BR />";
	}
$output .= "</DIV>";
$output .= "</FIELDSET>";
$output .= "<FIELDSET>";
$output .= "<LEGEND class='text_large text_bold'>Time and Date</LEGEND>";
$output .= "<DIV style='position: relative;'>";
$output .= "<LABEL for='dateselect_problemstart' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_start'] . "');\">" . get_text('Run Start') . ":</LABEL>";
$output .= return_date_dropdown('problemstart',0,TRUE);
$output .= "<BR />";
$output .= "<LABEL for='statusSelect' onmouseout='UnTip();' onmouseover=\"Tip('Incident Status - Open, Closed or Scheduled');\">" . get_text('Status') . ":</LABEL>";
$output .= "<SELECT ID='statusSelect' NAME='frm_status'>";
$output .= "<OPTION VALUE='" . $GLOBALS['STATUS_OPEN'] . "' selected>Open</OPTION>";
$output .= "<OPTION VALUE='" . $GLOBALS['STATUS_CLOSED'] . "'>Closed</OPTION>";
$output .= "<OPTION VALUE='" . $GLOBALS['STATUS_SCHEDULED'] . "'>Scheduled</OPTION>";
$output .= "</SELECT>";
$output .= "<BR />";
$output .= "<LABEL for='allowend' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_end'] . "');\">" . get_text('Run End') . ":";
$output .= "<input id='allowend' type='radio' style='float: right;' name='re_but' onClick ='do_end(this.form);' /></LABEL>";
$output .= "<SPAN style = 'visibility:hidden' ID = 'runend1'>" . return_date_dropdown('problemend',0, TRUE) . "</SPAN>";
$output .= "<BR />";
$output .= "<LABEL for='bookingselect' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles['_booked'] . "');\">" . get_text('Scheduled Date') . ":";
$output .= "<input id='bookingselect'type='radio' style='float: right;' name='book_but' onClick ='do_booking(this.form);' /></LABEL>";
$output .= "<SPAN style = 'visibility:hidden' ID = 'booking1'>" . return_date_dropdown('booked_date',0, TRUE) . "</SPAN>";
$output .= "<BR />";
$output .= "</DIV>";
$output .= "</FIELDSET>";
$output .= "<FIELDSET>";
$output .= "<LEGEND class='text_large text_bold'>Files</LEGEND>";
$output .= "<DIV style='position: relative;'>";
$output .= "<LABEL for='theFile' onmouseout='UnTip();' onmouseover=\"Tip('Chose a file to upload');\">" . get_text('Choose a file to upload') . ":</LABEL>";
$output .= "<INPUT NAME='theFile' TYPE='file' />";
$output .= "<BR />";
$output .= "<LABEL for='filename' onmouseout='UnTip();' onmouseover=\"Tip('Type a descriptive file name');\">" . get_text('Filename') . ":</LABEL>";
$output .= "<INPUT id='filename' NAME='frm_file_title' TYPE='text' SIZE='48' MAXLENGTH='128' VALUE='' />";
$output .= "<BR />";
$output .= "</DIV>";
$output .= "</FIELDSET>";
$output .= "</DIV>";
$output .= "<DIV>";
$output .= "</DIV>";
$output .= "<DIV ID='middle_col' style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;";
$output .= "<DIV style='position: fixed; top: 50px; z-index: 4500;'>";
$output .= "<SPAN id='hist_but' roll='button' tabindex=1 aria-label='History' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='do_hist_win();'>" . get_text('History') . "<BR /><IMG id='can_img' SRC='./images/list.png' /></SPAN>";
$output .= "<SPAN id='can_but' roll='button' tabindex=2  aria-label='Cancel' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='do_cancel(document.add); document.can_Form.submit();'>" . get_text('Cancel') . "<BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>";
$output .= "<SPAN id='reset_but' roll='button' tabindex=3  aria-label='Reset' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='do_reset(document.add);'>" . get_text('Reset') . "<BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>";
$output .= "<SPAN id='sub_but_but' roll='button' tabindex=4  aria-label='Submit' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.add);'>" . get_text('Submit') . "<BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>";
$output .= "<SPAN id='act_but' roll='button' tabindex=5  aria-label='Actions' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick=\"do_act_window('action_w.php?ticket_id=" . $ticket_id . "');\">" . get_text('Action') . "<BR /><IMG id='can_img' SRC='./images/action.png' /></SPAN>";
$output .= "<SPAN id='pat_but' roll='button' tabindex=6  aria-label='Patients' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick=\"do_pat_window('patient_w.php?ticket_id=" . $ticket_id . "');\">" . get_text('Patient') . "<BR /><IMG id='can_img' SRC='./images/patient.png' /></SPAN>";
$output .= "</DIV>";
$output .= "</DIV>";
/* if ($good_internet) {
	$output .= "<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>";
	$output .= "<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV><BR />";
	$output .= "<SPAN id='map_caption' class='text bold text_center' style='display: inline-block;'>" . get_variable('map_caption') . "</SPAN><BR />";
	$output .= "</DIV>";
	} */
$output .= "<INPUT TYPE='hidden' NAME='frm_lat' VALUE=''>";
$output .= "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=''>";
$output .= "<INPUT TYPE='hidden' NAME='ticket_id' VALUE='" . $ticket_id . "'>";
$output .= "<INPUT TYPE='hidden' NAME='frm_do_scheduled' VALUE=0>";
$output .= "</FORM>";
$output .= "<FORM NAME='can_Form' ACTION='main.php'>";
$output .= "</FORM>";
$ret_arr[0] = $output;
$ret_arr[1] = $heading;
print json_encode($ret_arr);