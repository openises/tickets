<?php
/*

*/
function do_datestring($date) {
    $theRet = date("jS M Y", $date);
    return $theRet;
    }

function get_fieldlabel($field) {
    $p = $GLOBALS['mysql_prefix'];
    $row = db_fetch_one("SELECT * FROM `{$p}defined_fields` WHERE `field_id` = ?", [$field]);
    if (isset($row['label'])) {
        $ret_val = $row['label'];
        } else {
        $ret_val = $field;
        }
    return $ret_val;
    }

function get_control($table, $set_id, $fieldname, $label, $readonly) {
    $p = $GLOBALS['mysql_prefix'];
    $readonly_string = $readonly ? "DISABLED='disabled'" : "";
    $output = "                            <LABEL for=\"" . e($fieldname) . "\">" . get_text(get_field_label('defined_fields', 3)) . ":</LABEL>";
    $output .= "                                <SELECT NAME='" . e($fieldname) . "' " . $readonly_string . ">";
    $table_escaped = db_escape($table);
    $rows = db_fetch_all("SELECT * FROM `{$p}{$table_escaped}`");
    foreach ($rows as $row) {
        $sel = ($set_id == $row['id']) ? " SELECTED" : "";
        $output .= "                                    <OPTION VALUE='" . e($row['id']) . "'" . $sel . ">" . e($row['name']) . "</OPTION>";
        }
        $output .= "                                </SELECT><BR />";
    return $output;
    }

function get_control_add($table, $fieldname, $label) {
    $p = $GLOBALS['mysql_prefix'];
    $output = "<LABEL for=\"" . e($fieldname) . "\">" . get_text(get_field_label('defined_fields', 3)) . ":</LABEL>";
    $output .= "<SELECT NAME='" . e($fieldname) . "'>";
    $output .= "<OPTION  VALUE=0 SELECTED>Select Team</OPTION>";
    $table_escaped = db_escape($table);
    $rows = db_fetch_all("SELECT * FROM `{$p}{$table_escaped}`");
    foreach ($rows as $row) {
        $output .= "                                    <OPTION 100%;' VALUE='" . e($row['id']) . "'>" . e($row['name']) . "</OPTION>";
        }
        $output .= "                                </SELECT><BR />";
    return $output;
    }

function get_field_numbers($table) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $result = db_query("SELECT * FROM `{$p}{$table_escaped}` LIMIT 1");
    $num_fields = $result->field_count;
    return $num_fields;
    }

function get_status_name($id) {
    $p = $GLOBALS['mysql_prefix'];
    $id = sanitize_int($id, 0);
    $row = db_fetch_one("SELECT * FROM `{$p}member_status` WHERE `id` = ?", [$id], 'i');
    $return_value = is_null($row['status_val']) ? "Not Found" : $row['status_val'];
    return $return_value;
    }

function get_field_label($table, $fieldid) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `field_id` = ? LIMIT 1", [$fieldid]);
    return $row ? $row['label'] : '';
    }

function get_fieldset($table, $fieldid) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `field_id` = ? LIMIT 1", [$fieldid]);
    return $row ? $row['fieldset'] : '';
    }

function get_fieldset_control($table, $fieldid) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `field_id` = ? LIMIT 1", [$fieldid]);
    if (!$row) { return ''; }
    $curr_fieldset = $row['fieldset'];
    $noedit = $row['_noedit'];
    if($noedit ==0) {
        $output = "                            <LABEL for='frm_fieldset'>" . get_text('Fieldset') . ":</LABEL>";
        $output .= "                                <SELECT NAME='frm_fieldset'>";
        $rows = db_fetch_all("SELECT * FROM `{$p}fieldsets`");
        foreach ($rows as $row) {
            $sel = ($row['id'] == $curr_fieldset) ? " SELECTED" : "";
            $output .= "                                    <OPTION STYLE='font-size: 100%;' VALUE='" . e($row['id']) . "'" . $sel . ">" . e($row['name']) . "</OPTION>";
            }
            $output .= "                                </SELECT><BR />";
        } else {
        $output = "<INPUT TYPE='hidden' NAME='frm_fieldset' VALUE='" . e($curr_fieldset) . "' />";
        }
    return $output;
    }

function get_fieldset_label($table, $id) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `id` = ? LIMIT 1", [$id]);
    return $row ? $row['label'] : '';
    }

function get_fieldset_name($table, $id) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `id` = ? LIMIT 1", [$id]);
    return $row ? $row['name'] : '';
    }

function get_field_inuse($table, $field, $fieldid) {
    $p = $GLOBALS['mysql_prefix'];
    $field_inuse = false;
    $field_type = get_field_type($table, $fieldid);
    $field_escaped = db_escape($field);
    $table_escaped = db_escape($table);
    if ($field_type == "ENUM" || $field_type == "DATE" || $field_type == "DATETIME") {
        $where = " WHERE (`{$field_escaped}` != 0 AND `{$field_escaped}` != 2)";
    } else {
        $where = " WHERE `{$field_escaped}` > '' LIMIT 1";
    }
    $result = db_query("SELECT * FROM `{$p}{$table_escaped}`" . $where);
    $rows = $result->num_rows;
    if($rows > 0) {
        $field_inuse = true;
        }
    return $field_inuse;
    }

function get_editable($table, $id) {
    $p = $GLOBALS['mysql_prefix'];
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `field_id` = ?", [$id], 'i');
    $editable = ($row && $row['_noedit'] == 0) ? true : false;
    return $editable;
    }

function get_avail_field_types($form_field, $inuse) {
    if(!($inuse)) {
        $ret_val = "<SELECT NAME='" . e($form_field) . "'>";
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
    $ret_val = "<SELECT NAME='" . e($form_field) . "'>";
    $ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='VARCHAR' {$sel_varchar}>VARCHAR</OPTION>";
    $ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='INT' {$sel_int}>INT</OPTION>";
    $ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='DATETIME' {$sel_date}>DATETIME</OPTION>";
    $ret_val .= "<OPTION STYLE='font-size: 100%;' VALUE='ENUM' {$sel_enum}>ENUM</OPTION>";
    $ret_val .= "</SELECT>";
    return $ret_val;
    }

function get_enum_vals($table_name, $column_name) {    //    01/15/13
    $p = $GLOBALS['mysql_prefix'];
    $row = db_fetch_one(
        "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",
        [$p . $table_name, $column_name],
        'ss'
    );
    $enum_list = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (safe_strlen($row['COLUMN_TYPE'])-6))));
    return $enum_list;
    }

function get_enum_default($table_name, $column_name) {
    $p = $GLOBALS['mysql_prefix'];
    $row = db_fetch_one(
        "SELECT COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",
        [$p . $table_name, $column_name],
        'ss'
    );
    $enum_default = $row['COLUMN_DEFAULT'];
    return $enum_default;
    }

function get_field_controls_edit($fieldid, $field_value, $memberid, $disallowed) {
    $p = $GLOBALS['mysql_prefix'];
    $disallow = ($disallowed == 1) ? true : false;
    $dis_str = ($disallowed) ? "DISABLED='disabled'" : "";
    $memberid = sanitize_int($memberid, 0);
    $row = db_fetch_one("SELECT *, _on AS `updated`,
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
            FROM `{$p}member`
            WHERE  `id` = ?", [$memberid], 'i');
    $row['updated'] = !empty($row['updated']) ? safe_strtotime($row['updated']) : false;        // 3/14/26 - null-safe
    $row['joindate'] = !empty($row['joindate']) ? safe_strtotime($row['joindate']) : false;
    $row['duedate'] = !empty($row['duedate']) ? safe_strtotime($row['duedate']) : false;
    $row['field56'] = !empty($row['field56']) ? safe_strtotime($row['field56']) : false;    // 3/14/26 - null-safe
    $row['field57'] = !empty($row['field57']) ? safe_strtotime($row['field57']) : false;
    $row['field58'] = !empty($row['field58']) ? safe_strtotime($row['field58']) : false;
    $row['field59'] = !empty($row['field59']) ? safe_strtotime($row['field59']) : false;
    $row['field60'] = !empty($row['field60']) ? safe_strtotime($row['field60']) : false;
    $row['field61'] = !empty($row['field61']) ? safe_strtotime($row['field61']) : false;
    $row['field62'] = !empty($row['field62']) ? safe_strtotime($row['field62']) : false;
    $row['field63'] = !empty($row['field63']) ? safe_strtotime($row['field63']) : false;
    $row['field64'] = !empty($row['field64']) ? safe_strtotime($row['field64']) : false;
    $row['field65'] = !empty($row['field65']) ? safe_strtotime($row['field65']) : false;
    $fieldtype = get_field_type('member', $fieldid);
    $fieldname = "frm_field" . $fieldid;
    $short_fieldname = "field" . $fieldid;
    if(get_text(get_field_label('defined_fields', $fieldid)) != "Not Used") {
        if($fieldtype == "ENUM") {
            $output = "                            <LABEL for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
            $fieldOptions = get_enum_vals('member', $short_fieldname);
            $theDefault = get_enum_default('member', $short_fieldname);
                        $output .= "                                <SELECT NAME='" . e($fieldname) . "' " . $dis_str . " style='color: #000000;'>";
            foreach($fieldOptions as $tmp) {
                $sel = ($tmp == $row[$short_fieldname]) ? "SELECTED" : "" ;
                $output .= "                                    <OPTION STYLE='font-size: 100%;' VALUE='" . e($tmp) . "' " . $sel . ">" . e($tmp) . "</OPTION>";
                }
            $output .= "                                </SELECT><BR />";
            } elseif($fieldtype == "STRING" || $fieldtype == "VAR_STRING") {
            $fieldsize = get_fieldsize($fieldid);
            if($fieldsize < 1025)    {
                $output = "                            <LABEL for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
                $output .= "                            <INPUT MAXLENGTH='" . e($fieldsize) . "' SIZE='40%' TYPE='text' NAME='" . e($fieldname) . "' VALUE='" . e($field_value) . "' " . $dis_str . " />";
                $output .= "                            <BR />";
                } else {
                $output = "                            <LABEL for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
                $output .= "                            <TEXTAREA name='" . e($fieldname) . "' rows='' cols='' class='expand50-200 text_medium' " . $dis_str . "}>" . e($field_value) . "</TEXTAREA>";
                $output .= "                            <BR />";
                }
            } elseif($fieldtype == "DATETIME" || $fieldtype == "datetime") {

            print "<LABEL CLASS='text text_bold' for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL> ";
            print generate_date_dropdown_middates($fieldname,$row["$short_fieldname"],0, $disallowed);
            print "<BR />";
            $output = "";
            } else {
            $output = "";
            }
        } else {
        $output = "<INPUT TYPE='hidden' NAME='" . e($fieldname) . "' VALUE=''>";
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
            $output = "                            <LABEL for='frm_field15'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
            $fieldOptions = get_enum_vals('member', $short_fieldname);
            $theDefault = get_enum_default('member', $short_fieldname);
            $output .= "                                <SELECT NAME='" . e($fieldname) . "' " . $dis_str . " style='color: #000000;'>";
            $output .= "<OPTION VALUE='' SELECTED>Select</OPTION>";
            foreach($fieldOptions as $tmp) {
                $sel = ($tmp == $theDefault) ? "SELECTED" : "" ;
                $output .= "                                    <OPTION VALUE='" . e($tmp) . "' " . $sel . ">" . e($tmp) . "</OPTION>";
                }
            $output .= "                                </SELECT><BR />";
            } elseif($fieldtype == "STRING" || $fieldtype == "VAR_STRING") {
            $fieldsize = get_fieldsize($fieldid);
            if($fieldsize < 1025)    {
                $output = "                            <LABEL for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
                $output .= "                            <INPUT MAXLENGTH='" . e($fieldsize) . "' SIZE='40%' TYPE='text' NAME='" . e($fieldname) . "' VALUE='' " . $dis_str . " />";
                $output .= "                            <BR />";
                } else {
                $output = "                            <LABEL for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL>";
                $output .= "                            <TEXTAREA name='" . e($fieldname) . "' rows='' cols='' class='expand50-200' " . $dis_str . "}></TEXTAREA>";
                $output .= "                            <BR />";
                }
            } elseif($fieldtype == "DATETIME") {
                print "<LABEL CLASS='text text_bold' for='" . e($fieldname) . "'>" . get_text(get_field_label('defined_fields', $fieldid)) . ":</LABEL> ";
                print generate_date_dropdown_middates($fieldname,"",0, $disallowed);
                print "<BR />";
                $output = "";
            } else {
                $output = "";
            }
        } else {
        $output = "<INPUT TYPE='hidden' NAME='" . e($fieldname) . "' VALUE=''>";
        }
    return $output;
    }


function get_fieldsize($field) {
    $p = $GLOBALS['mysql_prefix'];
    $row = db_fetch_one("SELECT * FROM `{$p}defined_fields` WHERE `field_id` = ?", [$field]);
    if (isset($row['size'])) {
        $ret_val = $row['size'];
        } else {
        $ret_val = '48';
        }
    return $ret_val;
    }

function get_veh_datetime_fields() {
    $p = $GLOBALS['mysql_prefix'];
    $veh_arr = array();
    $the_ret = array();
    $result = db_query("SELECT * FROM `{$p}vehicles`");
    $numfields = $result->field_count;
    $fieldnames = array();
    $fieldtypes = array();
    $finfo = $result->fetch_fields();
    for ($z=0; $z < $numfields; $z++) {
        $fieldnames[$z] = $finfo[$z]->name;
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
    $p = $GLOBALS['mysql_prefix'];
    $mem_arr = array();
    $the_ret = array();
    $i = 0;
    $rows = db_fetch_all("SELECT * FROM `{$p}defined_fields`");
    foreach($rows as $row) {
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
    if (is_object($d)) {                        // obtain properties of argument object
        $d = get_object_vars($d);                //   using get_object_vars function
        }
    if (is_array($d)) {                            // Returns array converted to object
        return array_map(__FUNCTION__, $d);        //  Using __FUNCTION__ (Magic constant) for recursive call
        }
    else { return $d; }                            // Return array -- ??
    }        // end function object ToArray()

function generate_time_dropdown($date_suffix, $default_time, $disabled, $readonly) {            // 'extra allows 'disabled'
    if(empty($default_time)) {
        $default_time = 0;
    }
    if(empty($disabled)) {
        $disabled = false;
    }
    $dis_str = ($disabled==true) ? " DISABLED='disabled'" : "" ;
    $dis_str = ($readonly==true) ? " DISABLED='disabled'" : "" ;
    $td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");                            // hours west of GMT
    $deltam = intval(get_variable('delta_mins'));                                                    // align server clock minutes
    $local = (time() - (intval(get_variable('delta_mins'))*60));

    if ($default_time)    {    //default to current date/time if no values are given
        $hour          = date('G',$default_time);
        $minute     = date('i',$default_time);
        }
    else {
        // $hour         = date('G', $local);
        // $minute     = date('i', $local);
        $hour          = 0;
        $minute     = 0;
        }

    print "<SELECT CLASS='text_medium' name='frm_hour_" . e($date_suffix) . "' " . $dis_str . " />";
    for($i = 0; $i < 24; $i++){
        if($i < 10) { $j = "0" . $i; } else { $j = $i; }
        print "<OPTION class='normalSelect text_medium' VALUE='$j'";
        $hour == $j ? print " SELECTED>$j</OPTION>" : print ">$j</OPTION>";
        }

    print "</SELECT>";
    print "&nbsp;<SELECT CLASS='text_medium' name='frm_minute_" . e($date_suffix) . "' " . $dis_str . " />";
    for($i = 0; $i < 59; $i++){
        if($i < 10) { $j = "0" . $i; } else { $j = $i; }
        print "<OPTION class='normalSelect text_medium' VALUE='$j'";
        $minute == $j ? print " SELECTED>$j</OPTION>" : print ">$j</OPTION>";
        }
    print "</SELECT>\n&nbsp;&nbsp;";

    print "\n<!-- default:$default_time,$hour-$minute -->\n";
}        // end function generate_time_dropdown(
                                                                    /* print date and time in dropdown menus */
function generate_date_dropdown_olddates($date_suffix, $default_date, $disabled, $readonly) {            // 'extra allows 'disabled'
    if(empty($default_time)) {
        $default_time = 0;
    }
    if(empty($disabled)) {
        $disabled = false;
    }
    $dis_str = ($disabled==true) ? " DISABLED='disabled'" : "" ;
    $dis_str = ($readonly==true) ? " DISABLED='disabled'" : "" ;
    $td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");                            // hours west of GMT
    $deltam = intval(get_variable('delta_mins'));                                                    // align server clock minutes
    $local = (time() - (intval(get_variable('delta_mins'))*60));

    if ($default_date)    {    //default to current date/time if no values are given
        $year          = date('Y',$default_date);
        $month         = date('m',$default_date);
        $day           = date('d',$default_date);
        }
    else {
        $year         = date('Y', $local);
        $month         = date('m', $local);
        $day         = date('d', $local);
        }

    $locale = get_variable('locale');                // Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
    switch($locale) {
        case "0":
            print "<SELECT name='frm_year_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = date("Y")-100; $i < date("Y")+10; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_month_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 13; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>\n&nbsp;<SELECT name='frm_day_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 32; $i++){
                print "<OPTION class='normalSelect' VALUE=\"$i\"";
                $day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }
            print "</SELECT>\n&nbsp;&nbsp;";

            print "\n<!-- default:$default_date,$year-$month-$day -->\n";
            break;

        case "1":
            print "<SELECT name='frm_day_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 32; $i++){
                print "<OPTION class='normalSelect' VALUE=\"$i\"";
                $day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_month_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 13; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_year_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = date("Y")-100; $i < date("Y")+10; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }
            print "</SELECT>\n&nbsp;&nbsp;";

            print "\n<!-- default:$default_date,$year-$month-$day -->\n";
            break;
        case "2":                // 11/29/10
            print "<SELECT name='frm_day_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 32; $i++){
                print "<OPTION class='normalSelect' VALUE=\"$i\"";
                $day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_month_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 13; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_year_" . e($date_suffix) . "' " . $dis_str . " />";
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
    }        // end function generate_date_dropdown_olddates(

function generate_date_dropdown_middates($date_suffix, $default_date, $disabled, $readonly) {            // 'extra allows 'disabled'
    if(empty($default_time)) {
        $default_time = 0;
    }
    if(empty($disabled)) {
        $disabled = false;
    }
    $dis_str = ($disabled==true) ? " DISABLED='disabled'" : "" ;
    $dis_str = ($readonly==true) ? " DISABLED='disabled'" : "" ;
    $td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");                            // hours west of GMT
    $deltam = intval(get_variable('delta_mins'));                                                    // align server clock minutes
    $local = (time() - (intval(get_variable('delta_mins'))*60));

    if ($default_date)    {    //default to current date/time if no values are given
        $year          = date('Y',$default_date);
        $month         = date('m',$default_date);
        $day           = date('d',$default_date);
        }
    else {
        $year         = date('Y', $local);
        $month         = date('m', $local);
        $day         = date('d', $local);
        }

    $locale = get_variable('locale');                // Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
    switch($locale) {
        case "0":
            print "<SELECT name='frm_year_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = date("Y")-10; $i < date("Y")+10; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_month_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 13; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>\n&nbsp;<SELECT name='frm_day_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 32; $i++){
                print "<OPTION class='normalSelect' VALUE=\"$i\"";
                $day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }
            print "</SELECT>\n&nbsp;&nbsp;";

            print "\n<!-- default:$default_date,$year-$month-$day -->\n";
            break;

        case "1":
            print "<SELECT name='frm_day_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 32; $i++){
                print "<OPTION class='normalSelect' VALUE=\"$i\"";
                $day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_month_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 13; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_year_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = date("Y")-10; $i < date("Y")+10; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $year == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }
            print "</SELECT>\n&nbsp;&nbsp;";

            print "\n<!-- default:$default_date,$year-$month-$day -->\n";
            break;
        case "2":                // 11/29/10
            print "<SELECT name='frm_day_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 32; $i++){
                print "<OPTION class='normalSelect' VALUE=\"$i\"";
                $day == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_month_" . e($date_suffix) . "' " . $dis_str . " />";
            for($i = 1; $i < 13; $i++){
                print "<OPTION class='normalSelect' VALUE='$i'";
                $month == $i ? print " SELECTED='selected'>$i</OPTION>" : print ">$i</OPTION>";
                }

            print "</SELECT>";
            print "&nbsp;<SELECT name='frm_year_" . e($date_suffix) . "' " . $dis_str . " />";
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
    }        // end function generate_date_dropdown_middates(

function get_member_status_sel($member_in, $status_val_in, $tbl_in) {
    $p = $GLOBALS['mysql_prefix'];
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

    $member_in = sanitize_int($member_in, 0);
    $status_val_in = sanitize_int($status_val_in, 0);

    $row = db_fetch_one("SELECT * FROM `{$p}{$tablename}`, `{$p}{$status_table}` WHERE `{$p}{$tablename}`.`id` = ?
        AND `{$p}{$status_table}`.`id` = `{$p}{$tablename}`.`{$link_field}` LIMIT 1", [$member_in], 'i');

    $bgc = '#FFFFFF';
    $fgcol = '#000000';
    if($status_val_in != 0) {
        $row_fmt = db_fetch_one("SELECT * FROM `{$p}{$status_table}` WHERE `id` = ?", [$status_val_in], 'i');
        $bgc = ($row_fmt['background'] != "") ? "#" . e($row_fmt['background']) : '#FFFFFF';
        $fgcol = ($row_fmt['color'] != "") ? "#" . e($row_fmt['color']) : '#000000';
        }

    $status_field_escaped = db_escape($status_field);
    $rows = db_fetch_all("SELECT * FROM `{$p}{$status_table}` ORDER BY `{$status_field_escaped}` ASC");
    $dis = "";
    $i = 0;
    $outstr = "<SELECT CLASS='text' STYLE=\"width: 90%; color: {$fgcol}; background-color: {$bgc};\" name='frm_status_id' onChange = 'do_sel_update(" . intval($member_in) . ", this.value)' >";
    foreach ($rows as $row) {
        $sel = ($row['id']==$status_val_in)? " SELECTED": "";
        $outstr .= "<OPTION style=\"color: #" . e($row['color']) . "; background-color: #" . e($row['background']) . ";\" VALUE=" . e($row['id']) . $sel ." >" . e($row[$status_field]) . " </OPTION>";
        $i++;
        }
    $outstr .= "</SELECT>";
    return $outstr;
    }

function get_member_status($id) {
    $p = $GLOBALS['mysql_prefix'];
    if($id == 0) {return 0;}
    $id = sanitize_int($id, 0);
    $row = db_fetch_one("SELECT * FROM `{$p}member` WHERE `id` = ? LIMIT 1", [$id], 'i');
    if($row) {
        return $row['field21'];
        } else {
        return 0;
        }
    }

function get_type_sel($member_in, $type_val_in, $tbl_in) {
    $p = $GLOBALS['mysql_prefix'];
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

    $member_in = sanitize_int($member_in, 0);
    $type_val_in = sanitize_int($type_val_in, 0);

    $bgc = '#FFFFFF';
    $fgcol = '#000000';
    if($type_val_in != 0) {
        $row_fmt = db_fetch_one("SELECT * FROM `{$p}{$type_table}` WHERE `id` = ?", [$type_val_in], 'i');
        $bgc = (substr($row_fmt['background'], 0, 1) == "#") ? e($row_fmt['background']) : "#" . e($row_fmt['background']);
        $fgcol = (substr($row_fmt['color'], 0, 1) == "#") ? e($row_fmt['color']) : "#" . e($row_fmt['color']);
        }

    $result = db_query("SELECT * FROM `{$p}{$tablename}`, `{$p}{$type_table}` WHERE `{$p}{$tablename}`.`id` = ?
        AND `{$p}{$type_table}`.`id` = `{$p}{$tablename}`.`{$link_field}` LIMIT 1", [$member_in], 'i');
    $type_field_escaped = db_escape($type_field);
    $rows = db_fetch_all("SELECT * FROM `{$p}{$type_table}` ORDER BY `{$type_field_escaped}` ASC");
    $dis = "";
    $i = 0;
    $outstr = "<SELECT CLASS='text' STYLE=\"width: 90%; color: {$fgcol}; background: {$bgc};\" name='frm_type_id' onChange = 'do_sel_update_type(" . intval($member_in) . ", this.value)' >";
    foreach ($rows as $row) {
        $sel = ($row['id']==$type_val_in)? " SELECTED": "";
        $outstr .= "<OPTION style=\"color: #" . e($row['color']) . "; background: #" . e($row['background']) . ";\" VALUE=" . e($row['id']) . $sel ." >" . e($row[$type_field]) . " </OPTION>";
        $i++;
        }
    $outstr .= "</SELECT>";
    return $outstr;
    }

function is_team_manager($id) {
    $p = $GLOBALS['mysql_prefix'];
    $user = sanitize_int($_SESSION['user_id'], 0);
    $id = sanitize_int($id, 0);
    $row = db_fetch_one("SELECT * FROM `{$p}team` WHERE `manager` = ?", [$user], 'i');
    if($row) {
        $teamid = $row['id'];
        $row1 = db_fetch_one("SELECT * FROM `{$p}member` WHERE `id` = ?", [$id], 'i');
        $team = $row1['field3'];
        } else {
        $teamid = null;
        $team = null;
        }
    if(($teamid != null) && ($teamid == $team)) {
        return true;
        } else {
        return false;
        }
    }

function is_curr_member($id) {
    $p = $GLOBALS['mysql_prefix'];
    $user = $_SESSION['user_id'];
    $member = (isset($_SESSION['member'])) ? $_SESSION['member'] : null;
    $id = sanitize_int($id, 0);
    $row = db_fetch_one("SELECT * FROM `{$p}member` WHERE `id` = ?", [$id], 'i');
    if($row) {
        $userid = $row['id'];
        if(($member != null) && ($member == $userid)) {
            return true;
            } else {
            return false;
            }
        } else {
        return false;
        }
    }

function secure_page($val) {
    $sess_id = MD5($_SESSION['id']);
    if($sess_id == $val) {
        return true;
        } else {
        return false;
        }
    }

function get_its_name($id, $namefield, $table){                                /* entry Name Field */
    $p = $GLOBALS['mysql_prefix'];
    $id = sanitize_int($id, 0);
    $namefield_escaped = db_escape($namefield);
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT `{$namefield_escaped}` AS `thename` FROM `{$p}{$table_escaped}` WHERE `id` = ? LIMIT 1", [$id], 'i');
    return (!$row) ? "Not Specified?" : $row['thename'];
    }

function get_member_name($id, $reverse = null) {
    $p = $GLOBALS['mysql_prefix'];
    $id = sanitize_int($id, 0);
    $row = db_fetch_one("SELECT `field1` AS `surname`, `field2` AS `forename`, `field6` AS `middlename` FROM `{$p}member` WHERE `id` = ? LIMIT 1", [$id], 'i');
    if(!$row) {
        return "Not Specified?";
    }
    if(!$reverse) {
        return $row['forename'] . " " . $row['middlename'] . " " . $row['surname'];
        } else {
        return $row['surname'] . " - " . $row['forename'];
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
    $p = $GLOBALS['mysql_prefix'];
    $id = sanitize_int($id, 0);
    $table_escaped = db_escape($table);
    $row = db_fetch_one("SELECT * FROM `{$p}{$table_escaped}` WHERE `id` = ? LIMIT 1", [$id], 'i');
    $output = "<TABLE style='width: " . e($width) . "; border: 1px outset #707070;'>";
    foreach($row as $key => $val) {
        if($key != 'id') {
            $output .= "<TR><TD class='td_label text'>" . e($key) . "</TD><TD class='td_data_wrap text'>" . e($val) . "</TD></TR>";
            $output .= "<TR><TD class='td_label text' COLSPAN=99>&nbsp;</TD></TR>";
            }
        }
    $output .= "</TABLE>";
    return $output;
    }

function get_eventtype($id) {
    $p = $GLOBALS['mysql_prefix'];
    $id = sanitize_int($id, 0);
    $row = db_fetch_one("SELECT * FROM `{$p}event_types` WHERE `id` = ? LIMIT 1", [$id], 'i');
    if($row) {
        $output = ($row['name'] != "") ? $row['name'] : "UNK";
        } else {
        $output = "ERROR";
        }
    return $output;
    }

function mdb_notify_user() {                                // 10/20/08
    $p = $GLOBALS['mysql_prefix'];
    if(get_variable("notify_users") == "0") {return false;}
    $addrs = array();                                                            //
    $rows = db_fetch_all("SELECT * FROM `{$p}user` WHERE `level` = 0");
    foreach($rows as $row) {
        if (is_email($row['email'])) {
            array_push($addrs, $row['email']); // save for emailing
            }
        }
    return (empty($addrs))? false: $addrs;
    }

function get_mdb_fields_select($selectname, $selected=null) {
    $p = $GLOBALS['mysql_prefix'];
    $result = db_query("DESCRIBE `{$p}member`");
    if($result->num_rows > 0) {
        $output = "<SELECT name='" . e($selectname) . "' style='width: 100%;'>";
        $output .= "<OPTION style='color:#FFFF00; background-color:#CC0000;' selected>Select MDB Field</OPTION>";
        $rows = db_fetch_all("DESCRIBE `{$p}member`");
        foreach ($rows as $row) {
            if($row['Field'] != "id") {
                if(substr($row['Field'], 0, 5) == "field") {
                    $field = $row['Field'];
                    $fieldid = substr($row['Field'], 5);
                    $sel = ($row['Field'] == $selected) ? "selected" : "";
                    $fieldlabel = get_field_label('defined_fields', $fieldid);
                    $output .= "<OPTION VALUE='" . e($field) . "' {$sel}>" . e($fieldlabel) . "</OPTION>";
                    } else {
                    $field = $row['Field'];
                    $sel = ($row['Field'] == $selected) ? "selected" : "";
                    $output .= "<OPTION VALUE='" . e($field) . "' {$sel}>" . e($field) . "</OPTION>";
                    }
                }
            }
        $output .= "</SELECT>";
        } else {
        $output = "ERROR";
        }
    return $output;
    }
