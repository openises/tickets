<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$table = $_GET['table'];
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $table . "`";
	$result = mysql_query($query);
	$fields = mysql_num_fields($result);
	$rows   = mysql_num_rows($result);
	$table  = mysql_field_table($result, 0);
	for ($i=0; $i < $fields; $i++) {
		$type  = mysql_field_type($result, $i);
		$name  = mysql_field_name($result, $i);
		$len   = mysql_field_len($result, $i);
		$flags = mysql_field_flags($result, $i);
		if(in_array($name, $showfields, true)) {
			$output .= "<TR><TD><INPUT type='checkbox' value='" . $name . "'>" . $name . "</TD>";
			if(in_array($name, $mandatory, true)) {
				$output .= "<TD>Mandatory</TD>";
				} else {
				$output .= "<TD>Optional</TD>";
				}
			$output .= "<TD><INPUT NAME='" . $name . "_screen' TYPE='text' size=5 /></TD>";
			$output .= "<TD><INPUT NAME='" . $name . "_order' TYPE='text' size=5 /></TD>";
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]wizard_settings`";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$ret_arr[] = $row;;
		}
	return $ret_arr;
	}
	
function get_number_of_screens() {
	$query = "SELECT DISTINCT screen FROM `$GLOBALS[mysql_prefix]wizard_settings`"; 
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
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
print "Screen 1 of " . $count . "<BR />";
foreach($screen1 as $key=>$val) {
	foreach($val as $key2=>$val2) {
		print $key2 . ", " . $val2 . "<BR />";
		}
	}
print "<BR />Screen 2 of " . $count . "<BR />";
foreach($screen2 as $key=>$val) {
	foreach($val as $key2=>$val2) {
		print $key2 . ", " . $val2 . "<BR />";
		}
	}
print "<BR />Screen 3 of " . $count . "<BR />";
foreach($screen3 as $key=>$val) {
	foreach($val as $key2=>$val2) {
		print $key2 . ", " . $val2 . "<BR />";
		}
	}
?>