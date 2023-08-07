<?php
/*

*/
function do_datestring($date) {
	$theRet = date("jS M Y", $date);
	return $theRet;
	}

function get_fieldlabel($field) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]defined_fields` WHERE `field_id` = '$field'";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	if (isset($row['label'])) {
		$ret_val = $row['label'];
		} else {
		$ret_val = $field;
		}
	return $ret_val;
	}
	
function get_control($table, $set_id, $fieldname, $label, $readonly) {
	$readonly_string = $readonly ? "DISABLED='disabled'" : "";
	$output = "							<LABEL for=\"" . $fieldname . "\">" . get_text(get_field_label('defined_fields', 3)) . ":</LABEL>";
	$output .= "								<SELECT NAME='" . $fieldname . "' " . $readonly_string . ">";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$sel = ($set_id == $row['id']) ? " SELECTED" : "";
		$output .= "									<OPTION VALUE='" . $row['id'] . "'" . $sel . ">" . $row['name'] . "</OPTION>";
		}
		$output .= "								</SELECT><BR />";
	return $output;
	}
	
function get_control_add($table, $fieldname, $label) {
	$output = "<LABEL for=\"" . $fieldname . "\">" . get_text(get_field_label('defined_fields', 3)) . ":</LABEL>";
	$output .= "<SELECT NAME='" . $fieldname . "'>";
	$output .= "<OPTION  VALUE=0 SELECTED>Select Team</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$output .= "									<OPTION 100%;' VALUE='" . $row['id'] . "'>" . $row['name'] . "</OPTION>";
		}
		$output .= "								</SELECT><BR />";
	return $output;
	}

function get_field_numbers($table) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]$table`");
	$fields = mysql_num_fields($result);
	$num_fields = mysql_num_fields($result);
	return $num_fields;
	}
	
function get_status_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	$return_value = is_null($row['status_val']) ? "Not Found" : $row['status_val'];
    return $return_value;
	}
	
function get_field_label($table, $fieldid) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table` WHERE `field_id` = '" . $fieldid . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	return $row['label'];
	}
	
function get_fieldset($table, $fieldid) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table` WHERE `field_id` = '$fieldid' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	return $row['fieldset'];
	}
	
function get_fieldset_control($table, $fieldid) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table` WHERE `field_id` = '$fieldid' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	$curr_fieldset = $row['fieldset'];
	$noedit = $row['_noedit'];
	if($noedit ==0) {
		$output = "							<LABEL for='frm_fieldset'>" . get_text('Fieldset') . ":</LABEL>";
		$output .= "								<SELECT NAME='frm_fieldset'>";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fieldsets`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$sel = ($row['id'] == $curr_fieldset) ? " SELECTED" : "";
			$output .= "									<OPTION STYLE='font-size: 100%;' VALUE='" . $row['id'] . "'" . $sel . ">" . $row['name'] . "</OPTION>";
			}
			$output .= "								</SELECT><BR />";
		} else {
		$output = "<INPUT TYPE='hidden' NAME='frm_fieldset' VALUE='" . $curr_fieldset . "' />";
		}
	return $output;
	}
	
function get_fieldset_label($table, $id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table` WHERE `id` = '$id' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	return $row['label'];
	}
	
function get_fieldset_name($table, $id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table` WHERE `id` = '$id' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	return $row['name'];
	}

function get_field_inuse($table, $field, $fieldid) {
	$field_inuse = false;
	$field_type = get_field_type($table, $fieldid);
	$where = ($field_type == "ENUM" || $field_type == "DATE" || $field_type == "DATETIME") ? " WHERE (`{$field}` != 0 AND `{$field}` != 2)" : " WHERE `{$field}` > '' LIMIT 1";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table`" .$where;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$rows = mysql_num_rows($result);
	if($rows > 0) {
		$field_inuse = true;
		}
	return $field_inuse;
	}
	
function get_editable($table, $id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]$table` WHERE  `field_id` = $id";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	$editable = ($row['_noedit'] == 0) ? true : false;
	return $editable;
	}
	
function get_avail_field_types($form_field, $inuse) {
	if(!($inuse)) {
		$ret_val = "<SELECT NAME='" . $form_field . "'>";
		$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='VARCHAR'>VARCHAR</OPTION>";
		$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='INT'>INT</OPTION>";	
		$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='DATETIME'>DATETIME</OPTION>";	
		$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='ENUM'>ENUM</OPTION>";	
		$ret_val .= "</SELECT>";
		} else {
		$ret_val = "";
		}
	return $ret_val;
	}
	
function get_curr_field_types($form_field, $inuse, $type) {
	$sel_varchar = ($type == "STRING") ? "SELECTED" : "";
	$sel_int = ($type == "INT") ? "SELECTED" : "";
	$sel_date = ($type == "DATETIME") ? "SELECTED" : "";
	$sel_enum = ($type == "ENUM") ? "SELECTED" : "";	
	$ret_val = "<SELECT NAME='" . $form_field . "'>";
	$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='VARCHAR' {$sel_varchar}>VARCHAR</OPTION>";
	$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='INT' {$sel_int}>INT</OPTION>";	
	$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='DATETIME' {$sel_date}>DATETIME</OPTION>";	
	$ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='ENUM' {$sel_enum}>ENUM</OPTION>";	
	$ret_val .= "</SELECT>";
	return $ret_val;
	}

function get_enum_vals($table_name, $column_name) {	//	01/15/13
    $sql = "
        SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = '$GLOBALS[mysql_prefix]" . mysql_real_escape_string($table_name) . "' 
            AND COLUMN_NAME = '" . mysql_real_escape_string($column_name) . "'
    ";
    $result = mysql_query($sql) or die (mysql_error());
    $row = mysql_fetch_array($result);
    $enum_list = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
    return $enum_list;
	}
	
function get_enum_default($table_name, $column_name) {
    $sql = "
        SELECT COLUMN_DEFAULT 
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = '$GLOBALS[mysql_prefix]" . mysql_real_escape_string($table_name) . "' 
            AND COLUMN_NAME = '" . mysql_real_escape_string($column_name) . "'
    ";
    $result = mysql_query($sql) or die (mysql_error());
    $row = mysql_fetch_array($result);
    $enum_default = $row['COLUMN_DEFAULT'];
    return $enum_default;
	}	
	
function get_field_controls_edit($fieldid, $field_value, $memberid, $disallowed) {
	$disallow = ($disallowed == 1) ? true : false;
	$dis_str = ($disallowed) ? "DISABLED='disabled'" : "";	
	$query = "SELECT *, _on AS `updated`,
			field17 AS `joindate`, 
			field16 AS `duedate`, 
			field56 AS field56,
			field57 AS field57,
			field58 AS field58,
			field59 AS field59,
			field60 AS field60,
			field61 AS field61,
			field62 AS field62,
			field63 AS field63,
			field64 AS field64,
			field65 AS field65				
			FROM `$GLOBALS[mysql_prefix]member`
			WHERE  `id` = $memberid";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = mysql_fetch_array($result);
	$row['updated'] = strtotime($row['updated']);
	$row['joindate'] = strtotime($row['joindate']);
	$row['duedate'] = strtotime($row['duedate']);
	$row['field56'] = strtotime($row['field56']);
	$row['field57'] = strtotime($row['field57']);
	$row['field58'] = strtotime($row['field58']);
	$row['field59'] = strtotime($row['field59']);
	$row['field60'] = strtotime($row['field60']);
	$row['field61'] = strtotime($row['field61']);
	$row['field62'] = strtotime($row['field62']);
	$row['field63'] = strtotime($row['field63']);
	$row['field64'] = strtotime($row['field64']);
	$row['field65'] = strtotime($row['field65']);
	$fieldtype = get_field_type('member', $fieldid);
	$fieldname = "frm_field" . $fieldid;
	$short_fieldname = "field" . $fieldid;
	if(get_text(get_field_label('defined_fields', $fieldid)) != "Not Used") {
		if($fieldtype == "ENUM") {
			$output = "							<LABEL for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";	
			$fieldOptions = get_enum_vals('member', $short_fieldname);
			$theDefault = get_enum_default('member', $short_fieldname);
						$output .= "								<SELECT NAME='" . $fieldname . "' " . $dis_str . " style='color: #000000;'>";
			foreach($fieldOptions as $tmp) {
				$sel = ($tmp == $row[$short_fieldname]) ? "SELECTED" : "" ;
				$output .= "									<OPTION STYLE='font-size: 100%;' VALUE='" . $tmp . "' " . $sel . ">" . $tmp . "</OPTION>";
				}
			$output .= "								</SELECT><BR />";
			} elseif($fieldtype == "STRING" || $fieldtype == "VAR_STRING") {
			$fieldsize = get_fieldsize($fieldid);	
			if($fieldsize < 1025)	{
				$output = "							<LABEL for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
				$output .= "							<INPUT MAXLENGTH='" . $fieldsize . "' SIZE='40%' TYPE='text' NAME='" . $fieldname . "' VALUE='" . $field_value . "' " . $dis_str . " />";
				$output .= "							<BR />";
				} else {
				$output = "							<LABEL for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
				$output .= "							<TEXTAREA name='" . $fieldname . "' rows='' cols='' class='expand50-200 text_medium' " . $dis_str . "}>" . $field_value . "</TEXTAREA>";
				$output .= "							<BR />";	
				}
			} elseif($fieldtype == "DATETIME" || $fieldtype == "datetime") {

			print "<LABEL CLASS='text text_bold' for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL> ";
			print generate_date_dropdown_middates($fieldname,$row["$short_fieldname"],0, $disallowed);	
			print "<BR />";	
			$output = "";				
			} else {
			$output = "";
			}
		} else {
		$output = "<INPUT TYPE='hidden' NAME='" . $fieldname . "' VALUE=''>";
		}
	return $output;		
	}
	
function get_field_controls_add($fieldid, $disallowed) {
	$dis_str = ($disallowed) ? "DISABLED='disabled'" : "";	
	$fieldtype = get_field_type('member', $fieldid);
	$fieldname = "frm_field" . $fieldid;
	$short_fieldname = "field" . $fieldid;
	if(get_text(get_field_label('defined_fields', $fieldid)) != "Not Used") {
		if($fieldtype == "ENUM") {
			$output = "							<LABEL for='frm_field15'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";	
			$fieldOptions = get_enum_vals('member', $short_fieldname);
			$theDefault = get_enum_default('member', $short_fieldname);
			$output .= "								<SELECT NAME='" . $fieldname . "' " . $dis_str . " style='color: #000000;'>";
			$output .= "<OPTION VALUE='' SELECTED>Select</OPTION>";				
			foreach($fieldOptions as $tmp) {
				$sel = ($tmp == $theDefault) ? "SELECTED" : "" ;
				$output .= "									<OPTION VALUE='" . $tmp . "' " . $sel . ">" . $tmp . "</OPTION>";
				}
			$output .= "								</SELECT><BR />";
			} elseif($fieldtype == "STRING" || $fieldtype == "VAR_STRING") {
			$fieldsize = get_fieldsize($fieldid);	
			if($fieldsize < 1025)	{
				$output = "							<LABEL for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
				$output .= "							<INPUT MAXLENGTH='" . $fieldsize . "' SIZE='40%' TYPE='text' NAME='" . $fieldname . "' VALUE='' " . $dis_str . " />";
				$output .= "							<BR />";
				} else {
				$output = "							<LABEL for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
				$output .= "							<TEXTAREA name='" . $fieldname . "' rows='' cols='' class='expand50-200' " . $dis_str . "}></TEXTAREA>";
				$output .= "							<BR />";	
				}
			} elseif($fieldtype == "DATETIME") {
				print "<LABEL CLASS='text text_bold' for='" . $fieldname . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL> ";
				print generate_date_dropdown_middates($fieldname,"",0, $disallowed);	
				print "<BR />";	
				$output = "";					
			} else {
				$output = "";
			}
		} else {
		$output = "<INPUT TYPE='hidden' NAME='" . $fieldname . "' VALUE=''>";
		}
	return $output;		
	}	
	
	
function get_fieldsize($field) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]defined_fields` WHERE `field_id` = '$field'";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result)); 
	if (isset($row['size'])) {
		$ret_val = $row['size'];
		} else {
		$ret_val = '48';
		}
	return $ret_val;
	}
	
function get_veh_datetime_fields() {
	$veh_arr = array();
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]vehicles`";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$numfields = mysql_num_fields( $result );
	for ($z=0; $z < $numfields; $z++) {
		$fieldnames[$z] = mysql_field_name($result , $z);
		$fieldtypes[$z] = get_field_type('vehicles', $z);
		}
	$x = 0;
	for($y=0; $y < $numfields; $y++) {
		$fieldtype = $fieldtypes[$y];
		if($fieldnames[$y][0] != "_" && ($fieldtype == "DATE" || $fieldtype == "DATETIME" || $fieldtype == "date" || $fieldtype == "datetime")) {
			$veh_arr[$x]['label'] = $fieldnames[$y];
			$veh_arr[$x]['fieldid'] = $y + 1;
			$veh_arr[$x]['fieldtype'] = $fieldtypes[$y];
			$veh_arr[$x]['textlabel'] = $fieldnames[$y];
			$veh_arr[$x]['title'] = $fieldnames[$y];
			$veh_arr[$x]['flag'] = $fieldnames[$y];
			$x++;
			}
		}
	return $veh_arr;
	}
	
function get_mem_datetime_fields() {
	$mem_arr = array();
	$the_ret = array();
	$i = 0;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]defined_fields`";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$label = $row['label'];
		$fieldid = "field" . $row['field_id'];
		$fieldtype = get_field_type('member', $row['field_id']);
		if(($label != "Updated" && $label != "Not Used" && $label != "Join Date" && $label != "Date of Birth") && ($fieldtype == "DATETIME" || $fieldtype == "DATE")) {
			$mem_arr[$i]['label'] = $label;
			$mem_arr[$i]['fieldid'] = $fieldid;
			$mem_arr[$i]['fieldtype'] = $fieldtype;
			$mem_arr[$i]['textlabel'] = $label;
			$mem_arr[$i]['title'] = $label;
			$mem_arr[$i]['flag'] = $label;
			$i++;
			}
		}
	return $mem_arr;
	}

function objectToArray($d) {
	if (is_object($d)) {						// obtain properties of argument object	
		$d = get_object_vars($d);				//   using get_object_vars function
		}	
	if (is_array($d)) {							// Returns array converted to object
		return array_map(__FUNCTION__, $d);		//  Using __FUNCTION__ (Magic constant) for recursive call
		}
	else { return $d; }							// Return array -- ??		
	}		// end function object ToArray()

function generate_time_dropdown($date_suffix, $default_time, $disabled, $readonly) {			// 'extra allows 'disabled'
	if(empty($default_time)) {
		$default_time = 0;
	}
	if(empty($disabled)) {
		$disabled = FALSE;
	}
	$dis_str = ($disabled==true) ? " DISABLED='disabled'" : "" ;
	$dis_str = ($readonly==true) ? " DISABLED='disabled'" : "" ;		
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));

	if ($default_time)	{	//default to current date/time if no values are given
		$hour  		= date('G',$default_time);
		$minute 	= date('i',$default_time);
		}
	else {
		// $hour 		= date('G', $local);
		// $minute 	= date('i', $local);
		$hour  		= 0;
		$minute 	= 0;			
		}

	print "<SELECT CLASS='text_medium' name='frm_hour_$date_suffix' " . $dis_str . " />";
	for($i = 0; $i < 24; $i++){
		if($i < 10) { $j = "0" . $i; } else { $j = $i; }
		print "<OPTION class='normalSelect text_medium' VALUE='$j'";
		$hour == $j ? print " SELECTED>$j</OPTION>" : print ">$j</OPTION>";
		}
		
	print "</SELECT>";
	print "&nbsp;<SELECT CLASS='text_medium' name='frm_minute_$date_suffix' " . $dis_str . " />";
	for($i = 0; $i < 59; $i++){
		if($i < 10) { $j = "0" . $i; } else { $j = $i; }
		print "<OPTION class='normalSelect text_medium' VALUE='$j'";
		$minute == $j ? print " SELECTED>$j</OPTION>" : print ">$j</OPTION>";
		}
	print "</SELECT>\n&nbsp;&nbsp;";

	print "\n<!-- default:$default_time,$hour-$minute -->\n";
}		// end function generate_time_dropdown(
																	/* print date and time in dropdown menus */ 
function generate_date_dropdown_olddates($date_suffix, $default_date, $disabled, $readonly) {			// 'extra allows 'disabled'
	if(empty($default_time)) {
		$default_time = 0;
	}
	if(empty($disabled)) {
		$disabled = FALSE;
	}
	$dis_str = ($disabled==true) ? " DISABLED='disabled'" : "" ;
	$dis_str = ($readonly==true) ? " DISABLED='disabled'" : "" ;	
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));

	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		}
	else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		}

	$locale = get_variable('locale');				// Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
	switch($locale) { 
		case "0":
			print "<SELECT name='frm_year_$date_suffix' " . $dis_str . " />";
			for($i = date("Y")-100; $i < date("Y")+10; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
				
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			
			print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='normalSelect' VALUE=\"$i\"";
				$day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;
	
		case "1":
			print "<SELECT name='frm_day_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='normalSelect' VALUE=\"$i\"";
				$day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_year_$date_suffix' " . $dis_str . " />";
			for($i = date("Y")-100; $i < date("Y")+10; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;
		case "2":				// 11/29/10
			print "<SELECT name='frm_day_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='normalSelect' VALUE=\"$i\"";
				$day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_year_$date_suffix' " . $dis_str . " />";
			for($i = date("Y")-100; $i < date("Y")+10; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;

		default:
		    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		}
	}		// end function generate_date_dropdown_olddates(
	
function generate_date_dropdown_middates($date_suffix, $default_date, $disabled, $readonly) {			// 'extra allows 'disabled'
	if(empty($default_time)) {
		$default_time = 0;
	}
	if(empty($disabled)) {
		$disabled = FALSE;
	}
	$dis_str = ($disabled==true) ? " DISABLED='disabled'" : "" ;
	$dis_str = ($readonly==true) ? " DISABLED='disabled'" : "" ;	
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));

	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		}
	else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		}

	$locale = get_variable('locale');				// Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
	switch($locale) { 
		case "0":
			print "<SELECT name='frm_year_$date_suffix' " . $dis_str . " />";
			for($i = date("Y")-10; $i < date("Y")+10; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
				
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			
			print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='normalSelect' VALUE=\"$i\"";
				$day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;
	
		case "1":
			print "<SELECT name='frm_day_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='normalSelect' VALUE=\"$i\"";
				$day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_year_$date_suffix' " . $dis_str . " />";
			for($i = date("Y")-10; $i < date("Y")+10; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;
		case "2":				// 11/29/10
			print "<SELECT name='frm_day_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 32; $i++){
				print "<OPTION class='normalSelect' VALUE=\"$i\"";
				$day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' " . $dis_str . " />";
			for($i = 1; $i < 13; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_year_$date_suffix' " . $dis_str . " />";
			for($i = date("Y")-10; $i < date("Y")+10; $i++){
				print "<OPTION class='normalSelect' VALUE='$i'";
				$year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day -->\n";
			break;

		default:
		    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		}
	}		// end function generate_date_dropdown_middates(

function get_member_status_sel($member_in, $status_val_in, $tbl_in) {
	switch ($tbl_in) {
		case ("m") :
			$tablename = "member";
			$link_field = "field21";
			$status_table = "member_status";
			$status_field = "status_val";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";	
			}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}`, `$GLOBALS[mysql_prefix]{$status_table}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $member_in 
		AND `$GLOBALS[mysql_prefix]{$status_table}`.`id` = `$GLOBALS[mysql_prefix]{$tablename}`.`{$link_field}` LIMIT 1" ;	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
	$bgc = '#FFFFFF';
	$fgcol = '#000000';	
	if($status_val_in != 0) {
		$query_fmt = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` WHERE `id` = '{$status_val_in}'";	
		$result_fmt = mysql_query($query_fmt) or do_error($query_fmt, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_fmt = stripslashes_deep(mysql_fetch_assoc($result_fmt));
		$bgc = ($row_fmt['background'] != "") ? "#" . $row_fmt['background'] : '#FFFFFF';
		$fgcol = ($row_fmt['color'] != "") ? "#" . $row_fmt['color'] : '#000000';
		}


	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` ORDER BY `{$status_field}` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = "";
	$i = 0;
	$outstr = "<SELECT CLASS='text' STYLE=\"width: 90%; color: {$fgcol}; background-color: {$bgc};\" name='frm_status_id' onChange = 'do_sel_update({$member_in}, this.value)' >";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "<OPTION style=\"color: #{$row['color']}; background-color: #{$row['background']};\" VALUE=" . $row['id'] . $sel ." >$row[$status_field] </OPTION>";		
		$i++;
		}
	$outstr .= "</SELECT>";
	return $outstr;
	}
	
function get_member_status($id) {
	if($id == 0) {return 0;}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query);
	if($result) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['field21'];
		} else {
		return 0;
		}
	}
	
function get_type_sel($member_in, $type_val_in, $tbl_in) {
	switch ($tbl_in) {
		case ("m") :
			$tablename = "member";
			$link_field = "field7";
			$type_table = "member_types";
			$type_field = "name";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";	
			}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}`, `$GLOBALS[mysql_prefix]{$type_table}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $member_in 
		AND `$GLOBALS[mysql_prefix]{$type_table}`.`id` = `$GLOBALS[mysql_prefix]{$tablename}`.`{$link_field}` LIMIT 1" ;	
		
	$bgc = '#FFFFFF';
	$fgcol = '#000000';	
	if($type_val_in != 0) {
		$query_fmt = "SELECT * FROM `$GLOBALS[mysql_prefix]{$type_table}` WHERE `id` = '{$type_val_in}'";	
		$result_fmt = mysql_query($query_fmt) or do_error($query_fmt, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_fmt = stripslashes_deep(mysql_fetch_assoc($result_fmt));
		$bgc = (substr($row_fmt['background'], 0, 1) == "#") ? $row_fmt['background'] : "#" . $row_fmt['background'];
		$fgcol = (substr($row_fmt['color'], 0, 1) == "#") ? $row_fmt['color'] : "#" . $row_fmt['color'];
		}

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$type_table}` ORDER BY `{$type_field}` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = "";
	$i = 0;
	$outstr = "<SELECT CLASS='text' STYLE=\"width: 90%; color: {$fgcol}; background: {$bgc};\" name='frm_type_id' onChange = 'do_sel_update_type({$member_in}, this.value)' >";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		$sel = ($row['id']==$type_val_in)? " SELECTED": "";
		$outstr .= "<OPTION style=\"color: #{$row['color']}; background: #{$row['background']};\" VALUE=" . $row['id'] . $sel ." >$row[$type_field] </OPTION>";		
		$i++;
		}
	$outstr .= "</SELECT>";
	return $outstr;
	}
	
function is_team_manager($id) {
	$user = $_SESSION['user_id'];
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]team` WHERE `manager` = '{$user}'";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if($num_rows != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));	
		$teamid = $row['id'];
		$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = '{$id}'";	
		$result1 = mysql_query($query1) or do_error($query1, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		$row1 = stripslashes_deep(mysql_fetch_assoc($result1));
		$team = $row1['field3'];
		} else {
		$teamid = NULL;
		$team = NULL;
		}
	if(($teamid != NULL) && ($teamid == $team)) {
		return TRUE;
		} else {
		return FALSE;
		}
	}
	
function is_curr_member($id) {
	$user = $_SESSION['user_id'];
	$member = (isset($_SESSION['member'])) ? $_SESSION['member'] : NULL;	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = '{$id}'";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if($num_rows != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));	
		$userid = $row['id'];
		if(($member != NULL) && ($member == $userid)) {
			return TRUE;
			} else {
			return FALSE;
			}
		} else {
		return FALSE;
		}
	}

function secure_page($val) {
	$sess_id = MD5($_SESSION['id']);
	if($sess_id == $val) {
		return TRUE;
		} else {
		return FALSE;
		}
	}

function get_its_name($id, $namefield, $table){								/* entry Name Field */
	$result	= mysql_query("SELECT `" . $namefield . "` AS `thename` FROM `$GLOBALS[mysql_prefix]" . $table . "` WHERE `id`='$id' LIMIT 1") or do_error("get its namer(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "Not Specified?" : $row['thename'];
	}

function get_member_name($id, $reverse = NULL) {
	$result	= mysql_query("SELECT `field1` AS `surname`, `field2` AS `forename`, `field6` AS `middlename` FROM `$GLOBALS[mysql_prefix]member` WHERE `id`='$id' LIMIT 1") or do_error("get member name(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	if(!$reverse) {
		return (mysql_affected_rows()==0 )? "Not Specified?" : $row['forename'] . " " . $row['middlename'] . " " . $row['surname'];
		} else {
		return (mysql_affected_rows()==0 )? "Not Specified?" : $row['surname'] . " - " . $row['forename'];
		}
	}
	
function return_bytes ($size_str) {
    switch (substr ($size_str, -1)) {
        case 'M': case 'm': return (int)$size_str * 1048576;
        case 'K': case 'k': return (int)$size_str * 1024;
        case 'G': case 'g': return (int)$size_str * 1073741824;
        default: return $size_str;
		}
	}
	
function show_table($table, $id, $width) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $table . "` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$output = "<TABLE style='width: " . $width . "; border: 1px outset #707070;'>";
	foreach($row as $key => $val) {
		if($key != 'id') {
			$output .= "<TR><TD class='td_label text'>" . $key . "</TD><TD class='td_data_wrap text'>" . $val . "</TD></TR>";
			$output .= "<TR><TD class='td_label text' COLSPAN=99>&nbsp;</TD></TR>";			
			}
		}
	$output .= "</TABLE>";
	return $output;
	}
	
function get_eventtype($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]event_types` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if($result) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$output = ($row['name'] != "") ? $row['name'] : "UNK";
		} else {
		$output = "ERROR";
		}
	return $output;
	}
	
function mdb_notify_user() {								// 10/20/08
	if(get_variable("notify_users") == "0") {return FALSE;}
	$addrs = array();															// 
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = 0";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if (is_email($row['email'])) {
			array_push($addrs, $row['email']); // save for emailing
			}		
		}
	return (empty($addrs))? FALSE: $addrs;
	}
	
function get_mdb_fields_select($selectname, $selected=NULL) {
	$query = "DESCRIBE `$GLOBALS[mysql_prefix]member`";
	$result = $result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$output = "<SELECT name='$selectname' style='width: 100%;'>";
		$output .= "<OPTION style='color:#FFFF00; background-color:#CC0000;' selected>Select MDB Field</OPTION>";	
		while ($row = mysql_fetch_assoc($result)) {
			if($row['Field'] != "id") {
				if(substr($row['Field'], 0, 5) == "field") {
					$field = $row['Field'];
					$fieldid = substr($row['Field'], 5);
					$sel = ($row['Field'] == $selected) ? "selected" : "";
					$fieldlabel = get_field_label('defined_fields', $fieldid);
					$output .= "<OPTION VALUE='" . $field . "' {$sel}>" . $fieldlabel . "</OPTION>";
					} else {
					$sel = ($row['Field'] == $selected) ? "selected" : "";
					$output .= "<OPTION VALUE='" . $field . "' {$sel}>" . $field . "</OPTION>";
					}
				}
			}
		$output .= "</SELECT>";
		} else {
		$output = "ERROR";
		}
	return $output;
	}