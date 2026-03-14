<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$table = sanitize_string($_GET['table']);
// Whitelist allowed table names to prevent SQL injection via table name
$allowed_tables = array('ticket', 'assigns', 'responder', 'facility', 'patient');
if (!in_array($table, $allowed_tables, true)) {
	echo "Invalid table";
	exit();
}
$showfields = array();
$showfields[] = 'in_types_id';
$showfields[] = 'contact';
$showfields[] = 'street';
$showfields[] = 'city';
$showfields[] = 'state';
$showfields[] = 'to_address';
$showfields[] = 'address_about';
$showfields[] = 'scope';
$showfields[] = 'description';
$showfields[] = 'problemstart';
$showfields[] = 'severity';
$showfields[] = 'booked_date';

$mandatory = array();
$mandatory[] = 'in_types_id';
$mandatory[] = 'contact';
$mandatory[] = 'street';
$mandatory[] = 'city';
$mandatory[] = 'state';
$mandatory[] = 'scope';
$mandatory[] = 'description';
$mandatory[] = 'problemstart';
$mandatory[] = 'severity';

function get_table_field_types($table, $showfields, $mandatory) {
	$output = "<TABLE><TR><TH>Field</TH><TH>Required?</TH><TH>Screen</TH><TH>Order</TH></TR>";
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}{$table}`";
	$result = db_query($query);
	if ($result === false) {
		return "<p>Error loading fields</p>";
	}
	$fields = $result->field_count;
	$rows   = $result->num_rows;
	// Get field metadata
	$field_info = $result->fetch_fields();
	for ($i=0; $i < $fields; $i++) {
		$name  = $field_info[$i]->name;
		if(in_array($name, $showfields, true)) {
			$output .= "<TR><TD><INPUT type='checkbox' value='" . e($name) . "'>" . e($name) . "</TD>";
			if(in_array($name, $mandatory, true)) {
				$output .= "<TD>Mandatory</TD>";
				} else {
				$output .= "<TD>Optional</TD>";
				}
			$output .= "<TD><INPUT NAME='" . e($name) . "_screen' TYPE='text' size=5 /></TD>";
			$output .= "<TD><INPUT NAME='" . e($name) . "_order' TYPE='text' size=5 /></TD>";
			$output .= "</TR>";
			}
		}
	$output .= "</TABLE>";
	return $output;
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
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}wizard_settings`";
	$result = db_query($query);
	while ($row = stripslashes_deep($result->fetch_assoc())){
		$ret_arr[] = $row;;
		}
	return $ret_arr;
	}

function get_number_of_screens() {
	$query = "SELECT DISTINCT screen FROM `{$GLOBALS['mysql_prefix']}wizard_settings`";
	$result = db_query($query);
	$count = $result->num_rows;
	return $count;
	}

$count = get_number_of_screens();

$temp1 = array();
$temp2 = array();
$temp3 = array();
$screen1 = array();
$screen2 = array();
$screen3 = array();

print get_table_field_types($table, $showfields, $mandatory);
$table_settings = get_wizard_table();
foreach($table_settings as $key => $val) {
	if(intval($val['screen']) == 1) {
		$temp1[] = $val;
		}
	}
$screen1 = aasort($temp1,"display_order");
foreach($table_settings as $key => $val) {
	if(intval($val['screen']) == 2) {
		$temp2[] = $val;
		}
	}
$screen2 = aasort($temp2,"display_order");
foreach($table_settings as $key => $val) {
	if(intval($val['screen']) == 3) {
		$temp3[] = $val;
		}
	}
$screen3 = aasort($temp3,"display_order");
print "Screen 1 of " . intval($count) . "<BR />";
foreach($screen1 as $key=>$val) {
	foreach($val as $key2=>$val2) {
		print e($key2) . ", " . e($val2) . "<BR />";
		}
	}
print "<BR />Screen 2 of " . intval($count) . "<BR />";
foreach($screen2 as $key=>$val) {
	foreach($val as $key2=>$val2) {
		print e($key2) . ", " . e($val2) . "<BR />";
		}
	}
print "<BR />Screen 3 of " . intval($count) . "<BR />";
foreach($screen3 as $key=>$val) {
	foreach($val as $key2=>$val2) {
		print e($key2) . ", " . e($val2) . "<BR />";
		}
	}
?>