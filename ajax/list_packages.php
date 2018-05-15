<?php
require_once('../incs/functions.inc.php');
$key = $_GET['key'];
$ret_arr = array();
if($key == 1) {
	$table = 'training_packages';
	$name = "package_name";
	} elseif ($key == 2) {
	$table = 'capability_types';
	$name = "name";
	} elseif ($key == 3) {
	$table = 'equipment_types';
	$name = "equipment_name";
	} elseif ($key == 4) {
	$table = 'clothing_types';
	$name = "clothing_item";
	} else {
	}


$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $table . "`";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$ret_arr[$key][] = $row[$name];
	}
	
$print = "<SELECT NAME='pack_sel' style='width: 100%; height: 20px; font-size: 100%;' onChange='pop_members(this.options[this.selectedIndex].value);'><OPTION VALUE=0 SELECTED>Select One</OPTION>";
if(count($ret_arr) > 0) {	
	foreach($ret_arr[$key] AS $key1 => $val) {
		$theval = $key1 + 1;
		$print .= "<OPTION style='font-size: 100%;' VALUE=" . $theval . ">" . $val . "</OPTION>";
		}
	}
	
$print .= "</SELECT>";

$print .= "<BR />";


print $print;
if($key == 1 ) {
	print "<BR /><BR /><CENTER>";
	$fieldname = "frm_tra_comp";
	$fieldname2 = "frm_tra_refresh";	
	print generate_date_dropdown($fieldname,0,0,0);
	print generate_date_dropdown($fieldname2,0,0,0) . "<BR /><BR /><BR /></CENTER>";
	}