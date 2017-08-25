<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$sortby = (!(array_key_exists('sort', $_GET))) ? "mi_id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];

$istest = FALSE;
$output_arr = array();
$mi_row = array();
$num_rows = 0;

function subval_sort($a,$subkey, $dd) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	if($dd == 1) {	
		asort($b);
		} else {
		arsort($b);
		}
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}
	
function get_categoryName($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['category'];
	}
	
function get_markup($id) {
	$ret_arr = array();
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `id` = " . $id;
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr['id'] = $row['id'];
		$ret_arr['name'] = $row['line_name'];
		$ret_arr['type'] = $row['line_type'];
		$ret_arr['status'] = $row['line_status'];
		$ret_arr['ident'] = $row['line_ident'];
		$ret_arr['cat'] = get_categoryName($row['line_cat_id']);
		$ret_arr['data'] = $row['line_data'];
		$ret_arr['color'] = $row['line_color'];
		$ret_arr['opacity'] = $row['line_opacity'];
		$ret_arr['width'] = $row['line_width'];
		$ret_arr['fill_color'] = $row['fill_color'];
		$ret_arr['fill_opacity'] = $row['fill_opacity'];
		$ret_arr['filled'] = $row['filled'];
		$ret_arr['updated'] = format_date_2($row['_on']);
		} else {
		$ret_arr[0] = 0;
		}

	return $ret_arr;
	}

function mi_list($sortby="mi_id", $sortdir="ASC") {
	global $istest, $internet;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	@session_start();		// 
	$query = "SELECT *, mi._on AS `mi_updated`,
	`mi`.`id` AS `majinc_id`,
	`mt`.`id` AS `type_id`,
	`mi`.`name` AS `mi_name`,
	`mt`.`name` AS `type_name`, 
	`mi`.`boundary` AS `boundary`,
	`mi`.`description` AS `mi_description`
	FROM `$GLOBALS[mysql_prefix]major_incidents` `mi` 
	LEFT JOIN `$GLOBALS[mysql_prefix]mi_types` `mt` ON ( `mi`.`type` = `mt`.`id` )
	GROUP BY `majinc_id` ORDER BY `majinc_id` DESC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$mi_row[0][0] = 0;
		} else {
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$tip =  addslashes ( "");		// tooltip string - 10/28/2012
			$type = shorten($row['type_name'], 50);
			$severity = $row['severity'];
			$updated = format_sb_date_2($row['mi_updated']);
			$mi_name = replace_quotes(shorten($row['mi_name'], 50));
			$mi_description = replace_quotes(shorten($row['mi_description'], 30));
			$locale = get_variable('locale');	// 08/03/09			
			$mi_row[$i][0] = $mi_name;
			$mi_row[$i][1] = $mi_description;			
			$mi_row[$i][2] = $type;
			$mi_row[$i][3] = $updated;
			$mi_row[$i][4] = $tip;
			$mi_row[$i][5] = $i;
			$mi_row[$i][6] = get_owner($row['gold']);
			$mi_row[$i][7] = get_owner($row['silver']);
			$mi_row[$i][8] = get_owner($row['bronze']);
			$mi_row[$i][9] = get_markup($row['boundary']);		
			$mi_row[$i][10] = $row['majinc_id'];			
			$i++;
			}				// end tickets while ($row = ...)
		return $mi_row;
		}
	}
$output_arr = mi_list($sortby, $sortdir);
if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}

switch($sortby) {
	case 'id':
		$sortval = 10;
		break;
	case 'name':
		$sortval = 0;
		break;
	case 'type':
		$sortval = 2;
		break;
	case 'description':
		$sortval = 1;
		break;
	case 'gold':
		$sortval = 6;
		break;
	case 'silver':
		$sortval = 7;
		break;
	case 'bronze':
		$sortval = 8;
		break;
	case 'updated':
		$sortval = 3;
		break;
	default:
		$sortval = 10;
	}
	
if((isset($output_arr[0][0])) && ($output_arr[0][0] == 0)) {
	print json_encode($output_arr);
	} else {
	$the_arr = subval_sort($output_arr, $sortval, $dd);
	$the_output = array();
	$z=1;
	foreach($the_arr as $val) {
		$the_output[$z] = $val;
		$z++;
		}
	print json_encode($the_output);
	}

exit();
?>