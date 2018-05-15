<?php
//require_once('../incs/functions.inc.php');
//@session_start();
//session_write_close();

$wiz_settings = array();
$showfields = array();
$wizfields = array();
$addformlabels = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]wizard_settings` ORDER BY `screen` ASC, `display_order` ASC";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$wiz_settings[$row['screen']][$row['display_order']] = $row['fieldname'];
	$wizard[] = $row;
	$wizfields[] = $row['fieldname'];
	$addformlabels[$row['fieldname']] = $row['label'];
	$def_text[$row['fieldname']] = $row['default_text'];
	}

//dump($showfields);
//dump($wizard);
//dump($wizfields);

$screencount = count($wiz_settings);

$fieldtable = array();
$fieldtable[0] = 'in_types';
$fieldtable[1] = 'organisations';
$fieldtable[2] = 'user';
$fieldtable[8] = 'facility';
$fieldtable[9] = 'facility';

$mandatory = array();
$mandatory[0] = 'in_types_id';
$mandatory[1] = 'contact';
$mandatory[2] = 'street';
$mandatory[3] = 'city';
$mandatory[4] = 'state';
$mandatory[7] = 'scope';
$mandatory[8] = 'description';
$mandatory[9] = 'problemstart';
$mandatory[10] = 'severity';

$showfields[0] = 'in_types_id';
$showfields[1] = 'org';
$showfields[2] = 'portal_user';
$showfields[3] = 'contact';
$showfields[4] = 'address';
$showfields[5] = 'address_about';
$showfields[6] = 'phone';
$showfields[7] = 'to_address';
$showfields[8] = 'facility';
$showfields[9] = 'rec_facility';
$showfields[10] = 'lat';
$showfields[11] = 'lng';
$showfields[12] = 'date';
$showfields[13] = 'problemstart';
$showfields[14] = 'problemend';
$showfields[15] = 'scope';
$showfields[16] = 'description';
$showfields[17] = 'comments';
$showfields[18] = 'nine_one_one';
$showfields[19] = 'status';
$showfields[20] = 'severity';
$showfields[21] = 'booked_date';

$addformfields = array();
$addformfields['in_types_id'] = "frm_in_types";
$addformfields['contact'] = "frm_contact";
$addformfields['address'] = "frm_street";
$addformfields['street'] = "frm_street";
$addformfields['city'] = "frm_city";
$addformfields['state'] = "frm_state";
$addformfields['to_address'] = "frm_to_address";
$addformfields['address_about'] = "frm_address_about";
$addformfields['scope'] = "frm_scope";
$addformfields['description'] = "frm_description";
$addformfields['problemstart'] = "frm_problemstart";
$addformfields['severity'] = "frm_severity";
$addformfields['booked_date'] = "frm_bookeddate";
$addformfields['org'] = "frm_org";
$addformfields['portal_user'] = "frm_portal_user";
$addformfields['phone'] = "frm_phone";
$addformfields['facility'] = "frm_facility_id";
$addformfields['rec_facility'] = "frm_rec_facility_id";
$addformfields['lat'] = "frm_lat";
$addformfields['lng'] = "frm_lng";
$addformfields['comments'] = "frm_comments";
$addformfields['nine_one_one'] = "frm_nine_one_one";
$addformfields['status'] = "frm_status";

$temp1 = array();
$temp2 = array();
$temp3 = array();
$screen1 = array();
$screen2 = array();
$screen3 = array();
$tabindex = 1;
$focusfield = "";

function get_wizard_field_control($theField, $field_name) {
	global $showfields, $fieldtable, $addformfields, $addformlabels, $wizard, $wizfields, $titles, $tabindex, $focusfield, $def_text;
	$thefield = array_search($field_name, $wizfields);
	$fieldtype = $wizard[$thefield]['fieldtype'];
	$label = $wizard[$thefield]['label'];
	$fieldname = $wizard[$thefield]['fieldname'];
	$theControl = "";
	if($tabindex == 1) {$focusfield = "wiz_" . $fieldname;}
 	switch($fieldname) {
		case "in_types_id":
			$theControl = get_intypes_control();
			break;
		case "contact":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "address":
			$theControl = get_address_control();
			break;
		case "to_address":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "address_about":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "scope":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "description":
			$theControl = get_textarea($label, $addformfields[$field_name], $thefield, $def_text[$field_name]);
			break;
		case "problemstart":
			$theControl = generate_date_select('problemstart',0,"Incident Start",FALSE);
			break;
		case "booked_date":
			$theControl = generate_date_select('booked_date',0,"Scheduled Date",FALSE);
			$theControl .= "<INPUT TYPE='hidden' NAME='wiz_frm_do_scheduled' VALUE=0>";
			break;
		case "org":
			$table = "organisations";
			$theControl = get_select_fromtable($table, $addformfields[$field_name], "wiz_frm_org", $label, "name", "organisation", FALSE);
			break;
		case "portal_user":
			$table = "user";
			$theControl = get_select_fromtable($table, $addformfields[$field_name], "wiz_frm_portal_user", $label, "user", "portal", FALSE);
			break;
		case "phone":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "facility":
			$table = "facilities";
			$theControl = get_select_fromtable($table, $addformfields[$field_name], "wiz_frm_facility_id", $label, "name", "facility", FALSE);
			break;
		case "rec_facility":
			$table = "facilities";
			$theControl = get_select_fromtable($table, $addformfields[$field_name], "wiz_frm_rec_facility_id", $label, "name", "rec_facility", FALSE);
			break;
		case "lat":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "lng":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "comments":
			$theControl = get_textarea($label, $addformfields[$field_name], $thefield, $def_text[$field_name]);
			break;
		case "nine_one_one":
			$theControl = get_input($label, $addformfields[$field_name], $thefield, "40", "64", $def_text[$field_name]);
			break;
		case "status":
			$table = $fieldtable[$thefield];
			$values = array();
			$values[$GLOBALS['STATUS_OPEN']] = "OPEN";
			$values[$GLOBALS['STATUS_SCHEDULED']] = "SCHEDULED";			
			$theControl = get_select_fromvalues($values, $addformfields[$field_name], $label);
			break;
		default:
			//	Do nothing
		}
	return $theControl;
	}
	
function generate_date_select($date_suffix, $default_date=0, $theLabel, $disabled=FALSE) {
	global $tabindex;
	$label = "dateselect_" . $date_suffix;
	$output = "";
	$output .= "<LABEL for='" . $label . "' onmouseout='UnTip();' onmouseover='Tip(\"" . $date_suffix . "\");'>" . $theLabel . ":";
	if($date_suffix == "booked_date") {
		$output .= "<input id='wiz_bookingselect' type='radio' style='float: right;' name='wiz_book_but' onClick =\"do_booking(this.form);\" />";
		$disabled = TRUE;
		}
	$output .= "</LABEL>";
	if($date_suffix == "booked_date") {
		$output .= "<SPAN style = 'visibility: hidden;' ID = 'wiz_booking1'>";
		}
	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");
	$deltam = intval(get_variable('delta_mins'));
	$local = (time() - (intval(get_variable('delta_mins'))*60));
	$default_date = ($default_date == 0) ? $local : $default_date;

	if ($default_date)	{
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		$minute		= date('i',$default_date);
		$meridiem		= date('a',$default_date);
		if (get_variable('military_time')==1) {
			$hour = date('H',$default_date);
			} else {
			$hour = date('h',$default_date);;
			}
		} else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		$minute		= date('i', $local);
		$meridiem	= date('a', $local);
		if (get_variable('military_time')==1) {
			$hour = date('H', $local);
			} else {
			$hour = date('h', $local);
			}
		}
	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
			$output .= "<SELECT id='wiz_dateselect_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_year_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_year_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				$output .= "<OPTION VALUE='$i'";
				$output .= ($year == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
			$output .= "</SELECT>";
			$tabindex++;
			$output .= "&nbsp;<SELECT id='wiz_frm_month_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_month_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_month_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = 1; $i < 13; $i++){
				$output .= "<OPTION VALUE='$i'";
				$output .= ($month == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
			
			$output .= "</SELECT>&nbsp;";
			$tabindex++;
			$output .= "<SELECT id='wiz_frm_day_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_day_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_day_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = 1; $i < 32; $i++){
				$output .= "<OPTION VALUE='" . $i . "'";
				$output .= ($day == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
			$output .= "</SELECT>&nbsp;&nbsp;";
			$tabindex++;
			break;

		case "1":
			$output .= "<SELECT id='wiz_dateselect_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_day_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_day_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = 1; $i < 32; $i++){
				$output .= "<OPTION VALUE='" . $i . "'";
				$output .= ($day == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
	
			$output .= "</SELECT>";
			$tabindex++;
			$output .= "&nbsp;<SELECT id='wiz_frm_month_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_month_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_month_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = 1; $i < 13; $i++){
				$output .= "<OPTION VALUE='" . $i . "'";
				$output .= ($month == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}

			$output .= "</SELECT>";
			$tabindex++;
			$output .= "&nbsp;<SELECT id='wiz_frm_year_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_year_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_year_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				$output .= "<OPTION VALUE='" . $i . "'";
				$output .= ($year == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
			$output .= "</SELECT>&nbsp;&nbsp;";
			$tabindex++;
			break;

		case "2":
			$output .= "<SELECT id='wiz_dateselect_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_day_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_day_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = 1; $i < 32; $i++){
				$output .= "<OPTION VALUE=\"$i\"";
				$output .= ($day == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
	
			$output .= "</SELECT>";
			$tabindex++;
			$output .= "&nbsp;<SELECT id='wiz_frm_month_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_month_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_month_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = 1; $i < 13; $i++){
				$output .= "<OPTION VALUE='$i'";
				$output .= ($month == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}

			$output .= "</SELECT>";
			$tabindex++;
			$output .= "&nbsp;<SELECT id='wiz_frm_month_" . $date_suffix . "' tabindex=" . $tabindex . " name='wiz_frm_year_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_year_" . $date_suffix . "\", this.selectedIndex);'>\n";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				$output .= "<OPTION VALUE='" . $i . "'";
				$output .= ($year == $i) ? " SELECTED>" . $i . "</OPTION>" : ">" . $i . "</OPTION>\n";
				}
			$output .= "</SELECT>&nbsp;&nbsp;\n";
			$tabindex++;
			break;

		default:
		    $output .= "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}

	
	$output .= "<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' tabindex=" . $tabindex . " NAME='wiz_frm_hour_" . $date_suffix . "' VALUE='" . $hour . "' " . $dis_str . " onChange='set_controlvalue(\"frm_hour_" . $date_suffix . "\", this.selectedIndex);'>:";
	$tabindex++;
	$output .= "<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' tabindex=" . $tabindex . " NAME='wiz_frm_minute_" . $date_suffix . "' VALUE='" . $minute . "' " . $dis_str . " onChange='set_controlvalue(\"frm_minute_" . $date_suffix . "\", this.selectedIndex);'>";
	$tabindex++;
	$show_ampm = (!get_variable('military_time')==1);
	if ($show_ampm){	//put am/pm optionlist if not military time
		$output .= "<SELECT tabindex=" . $tabindex . " NAME='wiz_frm_meridiem_" . $date_suffix . "' " . $dis_str . " onChange='set_datselectvalue(\"frm_meridiem_" . $date_suffix . "\", this.selectedIndex);'><OPTION value='am'";
		if ($meridiem == 'am') {
			$output .= ' selected';
			$output .= ">am</OPTION>\n<OPTION value='pm'";
			}
		if ($meridiem == 'pm') {
			$output .= ' selected';
			$output .= ">pm</OPTION>\n</SELECT>\n";
			}
		$tabindex++;
		}
	if($date_suffix == "booked_date") {
		$output .= "</SPAN>";
		}
	return $output;
	}		// end function generate_date_select(
	
function get_intypes_control() {
	global $titles, $nature, $tabindex;
	$theControl = "<LABEL for='wiz_in_types_id' onmouseout='UnTip()' onmouseover='Tip(\"" . $titles['_nature'] . "\");'>" . $nature . ":<font color='red' size='-1'>*</font></LABEL>";
	$theControl .= "<SELECT id='wiz_in_types_id' NAME='sel_in_types_id' tabindex=" . $tabindex . " onChange='do_set_severity (this.selectedIndex); do_inc_protocol(this.options[selectedIndex].value.trim());'>\n";
	$theControl .= "<OPTION VALUE=0 SELECTED>TBD</OPTION>\n";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
	$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$the_grp = strval(rand());
	$i = 0;
	while ($temp_row = stripslashes_deep(mysql_fetch_array($temp_result))) {
		if ($the_grp != $temp_row['group']) {
			$theControl .= ($i == 0)? "": "</OPTGROUP>\n";
			$the_grp = $temp_row['group'];
			$theControl .= "<OPTGROUP LABEL='" . $temp_row['group'] . "'>\n";
			}
		$color = $temp_row['color'];
		$bgcolor = "white";
		$theControl .= "<OPTION VALUE='" . $temp_row['id'] . "' CLASS='" . $temp_row['group'] . "' style='color: " . $color . "; background-color: " . $bgcolor . ";' title='" . addslashes($temp_row['description']) . "'>" . addslashes($temp_row['type']) . "</OPTION>\n";
		$i++;
		}		// end while()
	$theControl .= "</OPTGROUP>\n";
	$theControl .= "</SELECT>\n";
	$theControl .= "<BR />";
	$theControl .= "<LABEL for='wiz_frm_severity' onmouseout='UnTip()' onmouseover=\"Tip('" . $titles['_prio'] . "');\">" . get_text('Priority') . ":<font color='red' size='-1'>*</font></LABEL>";
	$tabindex ++;
	$theControl .= "<SELECT id='wiz_frm_severity' NAME='wiz_frm_severity' tabindex=" . $tabindex . " onChange='do_set_priority(this.selectedIndex);'>";
	$theControl .= "<OPTION VALUE='0' SELECTED>" . get_severity($GLOBALS['SEVERITY_NORMAL']) . "</OPTION>";
	$theControl .= "<OPTION VALUE='1'>" . get_severity($GLOBALS['SEVERITY_MEDIUM']) . "</OPTION>";
	$theControl .= "<OPTION VALUE='2'>" . get_severity($GLOBALS['SEVERITY_HIGH']) . "</OPTION>";
	$theControl .= "</SELECT>";
	$theControl .= "<BR />";
	$theControl .= "<LABEL for='proto_cell' onmouseout='UnTip();' onmouseover=\"Tip('" . $titles["_proto"] . "');\">" . get_text("Protocol") . " :</LABEL>";
	$theControl .= "<SPAN ID='proto_cell'></SPAN>";
	$theControl .= "<BR />";
	$tabindex ++;
	return $theControl;
	}
	
function get_select_fromtable($table, $selectname, $selectid, $label, $valname, $extra, $mand=NULL) {
	if($extra == "portal") {$where = "WHERE `level` = 7";} else {$where = "";}
	global $titles, $nature, $tabindex;
	$mandtext = ($mand) ? "<font color='red' size='-1'>*</font>" : "";
	$theControl = "<LABEL for='" . $selectid . "' onmouseout='UnTip()' onmouseover='Tip(\"" . get_text($label) . "\");'>" . get_text($label) . ": " . $mandtext . "</LABEL>\n";
	$theControl .= "<SELECT id='" . $selectid . "' NAME='" . $selectname . "' tabindex=" . $tabindex . " onChange='do_set_severity (this.selectedIndex); do_inc_protocol(this.options[selectedIndex].value.trim());'>\n";
	$theControl .= "<OPTION VALUE=0 SELECTED>Select One</OPTION>\n";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $table . "` " . $where . " ORDER BY `id`";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$theControl .= "<OPTION VALUE='" . $row['id'] . "' >" . addslashes($row[$valname]) . "</OPTION>\n";
		}		// end while()
	$theControl .= "</SELECT>\n";
	$tabindex++;
	return $theControl;
	}

function get_select_fromvalues($values, $selectname, $selectid) {
	global $titles, $nature, $tabindex;
	$theControl = "<LABEL for='" . $selectid . "' onmouseout='UnTip()' onmouseover='Tip(\"" . $titles['_nature'] . "\");'>" . $nature . ": <font color='red' size='-1'>*</font></LABEL>\n";
	$theControl .= "<SELECT id='" . $selectid . "' NAME='" . $selectname . "' tabindex=" . $tabindex . " onChange='do_set_severity (this.selectedIndex); do_inc_protocol(this.options[selectedIndex].value.trim());'>\n";
	foreach($values as $key=>$val) {
		$sel = ($val == "OPEN") ? " SELECTED" : "";
		$theControl .= "<OPTION VALUE=" . $key . " TITLE='Incident " . $val . "'" . $sel . ">" . $val . "</OPTION>\n";
		}		// end while()
	$theControl .= "</SELECT>\n";
	$tabindex++;
	return $theControl;
	}	

function get_input($inputname, $inputid, $fieldid, $size, $maxlength, $other) {
	global $tabindex;
	$thiscontrol = "wiz_" . $inputid;
	$theControl = "<LABEL for='" . $inputid . "' onmouseout='UnTip();' onmouseover='Tip(\"" . $inputname . "\");'>" . get_text($inputname) . ":</LABEL>";
	$theControl .= "<INPUT id='" . $inputid . "' NAME='" . $thiscontrol . "' tabindex=" . $tabindex . " SIZE='" . $size . "' TYPE='text' VALUE='" . $other . "' MAXLENGTH='" . $maxlength . "' " . $other . " onChange='set_controlvalue(\"" . $thiscontrol . "\",\"" . $inputid . "\");'/>";
	$tabindex++;
	return $theControl;
	}

function get_address_control() {
	global $titles, $street, $city, $st, $st_size, $addr_sugg_str, $good_internet, $gmaps, $incident, $doloc, $tabindex;
	$theControl = "<LABEL for='wiz_street' onmouseout='UnTip()' onmouseover=\"Tip('" . $titles["_loca"] . "');\">" . get_text("Location") . ":</LABEL>";
	$theControl .= "<INPUT id='wiz_street' NAME='wiz_frm_street' tabindex=" . $tabindex . " SIZE='48' TYPE='text' VALUE='" . $street . "' MAXLENGTH='96' " . $addr_sugg_str . " onChange='set_controlvalue(\"wiz_frm_street\",\"frm_street\");' />";
	$theControl .= "<DIV ID='addr_list' style = 'display:inline;'></DIV>";
	$theControl .= "<BR />";
	$theControl .= "<LABEL for='wiz_my_txt' onmouseout='UnTip()' onmouseover=\"Tip('" . $titles["_city"] . "')\">" . get_text("City") . ":";
	if($gmaps || $good_internet) {
		$theControl .= "<BUTTON id='glasses' type='button' class='plain text' style='float: right; width: 80px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='loc_lkup(document.add);return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;";
		}
	$theControl .= "</LABEL>";
	$tabindex++;
	$theControl .= "<INPUT ID='wiz_my_txt' onFocus = \"createAutoComplete();$('city_reset').visibility='visible';\" NAME='wiz_frm_city' autocomplete='off' tabindex=" . $tabindex . " SIZE='48' TYPE='text' VALUE='" . $city . "' MAXLENGTH=64 onChange = \" $('city_reset').visibility='visible'; this.value=capWords(this.value); set_controlvalue('wiz_frm_city','frm_city');\">";
	$theControl .= "<IMG ID = 'city_reset' SRC='./markers/reset.png' STYLE = 'margin-left:20px; visibility:hidden;' onClick = \"this.style.visibility='hidden'; document.wiz_add.wiz_frm_city.value=''; document.add.frm_city.value=''; document.add.frm_city.focus(); obj_sugg = null;\" />";
	$tabindex++;
	if ($gmaps) {		// 12/1/2012
		$theControl .= "<BUTTON id='nearby' type='button' class='plain text' tabindex=-1 style='float: right; width: 80px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"Javascript:do_nearby(this.form);return false;\">Nearby?</BUTTON>";
		}
	$theControl .= "<span id='suggest' onmousedown=\"$('suggest').style.display='none'; $('city_reset').style.visibility='visible';\" style='visibility: hidden; border: #000000 1px solid; width:150px; position: absolute; right: 400px; background-color: #CECECE;'/></span>";
	$theControl .= "<LABEL for='wiz_state' onmouseout='UnTip()' onmouseover=\"Tip('" . $titles['_state'] . "');\">" . get_text("St") . "<font color='red' size='-1'>*</font></LABEL>";
	$theControl .= "<INPUT ID='wiz_state' NAME='wiz_frm_state' tabindex=" . $tabindex . " SIZE='" . $st_size . "' TYPE='text' VALUE='" . $st . "' MAXLENGTH='" . $st_size . "' onChange='set_controlvalue(\"wiz_frm_state\",\"frm_state\");' />";
	$theControl .= "<BR />";
	$tabindex++;
	if ($gmaps || $doloc|| $good_internet) {
		$theControl .= "<LABEL for='wiz_lock_p' onmouseout='UnTip()' onmouseover=\"Tip('" . $titles["_coords"] . "');\">" . $incident . " Lat/Lng: <font color='red' size='-1'>*</font>";
		$theControl .= "<img id='wiz_lock_p' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle; margin-left: 20px;' onClick = 'do_unlock_pos(document.add);'>";
		$theControl .= "</LABEL>";			
		$theControl .= "<INPUT ID='wiz_show_lat' SIZE='8' tabindex=" . $tabindex . " TYPE='text' NAME='wiz_show_lat' VALUE='' style='width: 50px;' />";
		$tabindex++;
		$theControl .= "<INPUT ID='wiz_show_lng' SIZE='8' tabindex=" . $tabindex . " TYPE='text' NAME='wiz_show_lng' VALUE='' style='width: 50px;' />";
		$tabindex++;
		$theControl .= "<BR />";
		$locale = get_variable('locale');
		$grid_types = array("USNG", "OSGB", "UTM");
		$theControl .= "<LABEL for='wiz_griddisp' onmouseout='UnTip()' onmouseover=\"Tip('Grid Reference');\" onClick = 'do_grid_to_ll();'>" . $grid_types[$locale] . ":</LABEL>";
		$theControl .= "<INPUT ID='wiz_griddisp' SIZE='12' TYPE='text' NAME='wiz_frm_ngs' VALUE='' DISABLED />";
		$theControl .= "<BR />";
		} else {		// end if ($gmaps)
		$tabindex++;
		$theControl .= "<INPUT TYPE='hidden' ID='wiz_show_lat' NAME='wiz_show_lat' VALUE='' />";
		$theControl .= "<INPUT TYPE='hidden' ID='wiz_show_lng' NAME='wiz_show_lng' VALUE='' />";
		}		// end else
	return $theControl;
	}
	
function get_textarea($inputname, $inputid, $fieldid, $defaultText) {
	global $tabindex;
	$thiscontrol = "wiz_" . $inputid;
	$theControl = "<LABEL for='" . $inputid . "' onmouseout='UnTip();' onmouseover='Tip(\"" . $inputname . "\");'>" . get_text($inputname) . ":</LABEL>";
	$theControl .= "<TEXTAREA id='" . $inputid . "' NAME='" . $thiscontrol . "' tabindex=" . $tabindex . " WRAP='virtual' STYLE='width: 40%;' onChange='set_controlvalue(\"" . $thiscontrol . "\",\"" . $inputid . "\");'>" . $defaultText . "</TEXTAREA>";
	$tabindex++;
	return $theControl;
	}
	
function aasort ($array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
		}
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
		}
    $array=$ret;
	return $array;
	}

function get_wizard_table() {
	$ret_arr = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]wizard_settings`";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$ret_arr[] = $row;;
		}
	return $ret_arr;
	}
	
function get_screen($screen, $inputArray, $heading) {
	global $showfields, $screencount, $wizard, $wizfields;
	$thisScreen = intval($screen);
	$nextScreen = intval($screen)+1;
	$output = "<DIV id='stage" . $screen . "' class='modal-content' style='display: none;'>\n";
	$output .= "\t<DIV class='modal-header'>\n";
	$output .= "\t\t<SPAN class='text_biggest text_bold text_white' style='width: 100%; display: block;'>" . $heading . "</SPAN>\n";
	$output .= "\t</DIV>\n";
	$output .= "\t<DIV class='modal-body' style='text-align: left; width: 100%;'>\n";
	foreach($inputArray as $key=>$val) {	// Output the controls here
		$output .= "\t\t<DIV style='text-align: left; width: 97%; border: 2px outset #707070; padding: 1%;'>\n";
		$theField = array_search($val, $showfields);
		$output .= get_wizard_field_control($theField, $val);
		$output .= "<BR />\n";
		$fieldid = array_search($val, $wizfields);
		$helptext = $wizard[$fieldid]['helptext'];
		$output .= "<SPAN class='text text_red' style='display: block; width: 90%; padding: 5px;'>" . $helptext . "</SPAN>\n";
		$output .= "<BR />\n";
		$output .= "</DIV>\n";
		}
	$output .= "\t</DIV>\n";
	$output .= "\t<DIV class='modal-footer'><CENTER>\n";
	if($screen == 1) {
		$output .= "\t\t<SPAN id='canBtn' roll='button' aria-label='Cancel' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='modalCancel();'><SPAN STYLE='float: left;'>" . get_text('Cancel') . "</SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>\n";
		}
	if($screen < $screencount) {
		$output .= "\t\t<SPAN id='nextBtn' roll='button' aria-label='Next' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"doModal(" . $thisScreen . ", " . $nextScreen . ");\"><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/next.png' BORDER=0></SPAN>\n";
		} else {
		$output .= "\t\t<SPAN id='finBtn' roll='button' aria-label='Submit' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='modalEnd();'><SPAN STYLE='float: left;'>" . get_text('Submit') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>\n";
		}
	$output .= "\t</CENTER></DIV>\n";
	$output .= "</DIV>\n";
	return $output;
	}
	
?>
<SCRIPT>
var stageval1;
var stageval2;
var stageval3;
var stageval4;
var modal = $('myModal');
var screens = <?php print intval($screencount);?>

function modalStart() {
	modal = $('myModal');
	var thestage = $("stage1");
	modal.style.display = "block";
	modal.zIndex = 5000;
	thestage.style.display = "block"		
	}
	
function doModal(current, next) {
	var currentstage = "stage" + current;
	var nextstage = "stage" + next;
	$(currentstage).style.display = "none";
	$(nextstage).style.display = "block";
	}
	
function closeModal(stage) {
	}
	
function modalEnd() {
	modal = $('myModal');
	modal.style.display = "none";
	validate(document.add);
	}
	
function modalCancel() {
	var in_win = <?php print $in_win;?>;
	if(in_win == 1) {
		window.close();
		} else {
		modal = $('myModal');
		modal.style.display = "none";
		}
	}
	
// When the user clicks anywhere outside of the modal, close it
/* window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
		}
	} */
	
function get_bldg(in_val) {									// called with zero-based array index - 3/29/2014
	if(myMarker) {map.removeLayer(marker); }
	var obj_bldg = bldg_arr[in_val];						// nth object
	document.getElementById('wiz_street').value = obj_bldg.bldg_street;
	document.wiz_add.wiz_frm_city.value = obj_bldg.bldg_city;
	document.getElementById('wiz_st').value = obj_bldg.bldg_state;
	var theLat = parseFloat(obj_bldg.bldg_lat).toFixed(6);
	var theLng = parseFloat(obj_bldg.bldg_lon).toFixed(6);
	}		// end function do_bldg()
	

</SCRIPT>
<DIV id="myModal" class="modal">	<!-- Modal Box Outside wrapper -->
	<FORM NAME="wiz_add" METHOD="post" ENCTYPE="multipart/form-data">
<?php
	$numScreens = $screencount;
	for($i = 1; $i <= $numScreens; $i++) {
		print get_screen($i, $wiz_settings[$i], "Screen " . $i . " of " . $numScreens . " ");
		}
		
?>
	</FORM>
</DIV>
<SCRIPT>
function set_tabindex() {
	var focuselement = "<?php print $focusfield;?>";
	var theForm = document.add;
	var theInputs = theForm.getElementsByTagName("input");
	var theTextareas = theForm.getElementsByTagName("textarea");
	var theSelects = theForm.getElementsByTagName("select");
	var theButtons = theForm.getElementsByTagName("button");
	for(var	i = 0; i <= theInputs.length; i++) {
		if(theInputs[i]) {theInputs[i].tabIndex = -1;}
		}
	for(var	i = 0; i <= theTextareas.length; i++) {
		if(theTextareas[i]) {theTextareas[i].tabIndex = -1;}
		}
	for(var	i = 0; i <= theSelects.length; i++) {
		if(theSelects[i]) {theSelects[i].tabIndex = -1;}
		}
	for(var	i = 0; i <= theButtons.length; i++) {
		if(theButtons[i]) {theButtons[i].tabIndex = -1;}
		}
	setTimeout(function(){$(focuselement).focus(); }, 2000);
	}

set_tabindex();
</SCRIPT>