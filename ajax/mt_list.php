<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
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
	
function get_ticket($id) {
	$ret_arr = array();
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `id` = " . $id;
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr['id'] = $row['id'];
		$ret_arr['scope'] = $row['scope'];
		$ret_arr['lat'] = $row['lat'];
		$ret_arr['lng'] = $row['lng'];
		} else {
		$ret_arr[0] = 0;
		}
	return $ret_arr;
	}
	
function get_place_details($id) {
	$ret_arr = array();
	if($id == 0) {
		$ret_arr[0] = 0;
		$ret_arr[1] = 0;
		$ret_arr[2] = 0;
		$ret_arr[3] = 0;
		} else {
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}places` WHERE `id` = " . $id;
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		if(mysql_num_rows($result) > 0) {
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$ret_arr[0] = $row['id'];
			$ret_arr[1] = $row['name'];
			$ret_arr[2] = $row['lat'];
			$ret_arr[3] = $row['lon'];
			} else {
			$ret_arr[0] = 0;
			$ret_arr[1] = 0;
			$ret_arr[2] = 0;
			$ret_arr[3] = 0;
			}
		}
	return $ret_arr;
	}

function mt_list($sortby="mi_id", $sortdir="ASC") {
	global $istest, $internet, $mi_row;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	@session_start();		// 
	$query = "SELECT *, mi._on AS `mi_updated`,
	`mi`.`id` AS `majinc_id`,
	`mt`.`id` AS `type_id`,
	`mi`.`name` AS `mi_name`,
	`mt`.`name` AS `type_name`, 
	`mi`.`boundary` AS `boundary`,
	`mi`.`mi_status` AS `mi_status`,
	`mi`.`description` AS `mi_description`
	FROM `$GLOBALS[mysql_prefix]major_incidents` `mi` 
	LEFT JOIN `$GLOBALS[mysql_prefix]mi_types` `mt` ON ( `mi`.`type` = `mt`.`id` )
	GROUP BY `majinc_id` ORDER BY `inc_endtime`, `majinc_id` DESC";
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
			$updated = format_sb_date_2($row['mi_updated']);
			$starttime = format_sb_date_2($row['inc_startime']);
			$endtime = (is_date($row['inc_endtime'])) ? format_sb_date_2($row['inc_endtime']) : "N/A";
			$mi_name = replace_quotes(shorten($row['mi_name'], 50));
			$mi_description = replace_quotes(shorten($row['mi_description'], 30));
			$locale = get_variable('locale');	// 08/03/09	
			$status = $row['mi_status'];
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
			$query_tick = "SELECT 
				`mx`.`id` AS `mx_id`,
				`mx`.`ticket_id` AS `mt_id`,
				`mx`.`mi_id` AS `mi_id`,
				`t`.`id` AS `tick_id`, 
				`t`.`scope` AS `tick_scope`, 
				`t`.`lat` AS `lat`, 
				`t`.`lng` AS `lng`,
				`t`.`severity` AS `severity`,
				`t`.`in_types_id` AS `inc_type`
				FROM `$GLOBALS[mysql_prefix]mi_x` `mx` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON ( `mx`.`ticket_id` = `t`.`id` )
				WHERE `mx`.`mi_id` = " . $row['majinc_id'] . " ORDER BY `tick_id` ASC";
			$result_tick = mysql_query($query_tick) or do_error($query_tick, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$mi_num_tick = mysql_num_rows($result_tick);
			if($mi_num_tick > 0) {
				$z = 0;
				while ($row_tick = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
					if($row_tick['tick_id'] != "") {
						$mi_row[$i][11][$z][0] = $row_tick['tick_id'];
						$mi_row[$i][11][$z][1] = $row_tick['tick_scope'];
						$mi_row[$i][11][$z][2] = $row_tick['lat'];
						$mi_row[$i][11][$z][3] = $row_tick['lng'];
						$mi_row[$i][11][$z][4] = $row_tick['inc_type'];
						$mi_row[$i][11][$z][5] = $row_tick['severity'];
						$query_resp = "SELECT *, 
							`r`.`id` AS `resp_id`,
							`r`.`lat` AS `resp_lat`,
							`r`.`lng` AS `resp_lng`,
							`r`.`handle` AS `resp_handle`
							FROM `$GLOBALS[mysql_prefix]assigns` `a` 
							LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `a`.`responder_id` = `r`.`id` )
							WHERE `a`.`ticket_id` = " . intval($row_tick['tick_id']) . " ORDER BY `resp_id` ASC";
						$result_resp = mysql_query($query_resp) or do_error($query_resp, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$mi_num_resp = mysql_num_rows($result_resp);
						$y = 0;
						if($mi_num_resp > 0) {
							while ($row_resp = stripslashes_deep(mysql_fetch_assoc($result_resp))) {
								$mi_row[$i][11][$z][6][$y][0] = $row_resp['resp_id'];
								$mi_row[$i][11][$z][6][$y][1] = $row_resp['resp_handle'];
								$mi_row[$i][11][$z][6][$y][2] = $row_resp['resp_lat'];
								$mi_row[$i][11][$z][6][$y][3] = $row_resp['resp_lng'];
								$y++;
								}
							}
						$z++;
						}
					}
				}
			$mi_row[$i][12] = $row['bg_color'];
			$mi_row[$i][13] = $row['color'];
			$gold_loc = get_place_details($row['gold_loc']);
			$silver_loc = get_place_details($row['silver_loc']);
			$bronze_loc = get_place_details($row['bronze_loc']);		
			$mi_row[$i][14][0] = $gold_loc[0];
			$mi_row[$i][14][1] = $gold_loc[1];
			$mi_row[$i][14][2] = $gold_loc[2];
			$mi_row[$i][14][3] = $gold_loc[3];	
			$mi_row[$i][14][4] = $row['gold_street'];
			$mi_row[$i][14][5] = $row['gold_city'];
			$mi_row[$i][14][6] = $row['gold_state'];
			$mi_row[$i][14][7] = $row['gold_lat'];
			$mi_row[$i][14][8] = $row['gold_lng'];
			$mi_row[$i][15][0] = $silver_loc[0];
			$mi_row[$i][15][1] = $silver_loc[1];
			$mi_row[$i][15][2] = $silver_loc[2];
			$mi_row[$i][15][3] = $silver_loc[3];	
			$mi_row[$i][15][4] = $row['silver_street'];
			$mi_row[$i][15][5] = $row['silver_city'];
			$mi_row[$i][15][6] = $row['silver_state'];
			$mi_row[$i][15][7] = $row['silver_lat'];
			$mi_row[$i][15][8] = $row['silver_lng'];
			$mi_row[$i][16][0] = $bronze_loc[0];
			$mi_row[$i][16][1] = $bronze_loc[1];
			$mi_row[$i][16][2] = $bronze_loc[2];
			$mi_row[$i][16][3] = $bronze_loc[3];
			$mi_row[$i][16][4] = $row['bronze_street'];
			$mi_row[$i][16][5] = $row['bronze_city'];
			$mi_row[$i][16][6] = $row['bronze_state'];
			$mi_row[$i][16][7] = $row['bronze_lat'];
			$mi_row[$i][16][8] = $row['bronze_lng'];
			$mi_row[$i][17] = $starttime;
			$mi_row[$i][18] = $endtime;
			$mi_row[$i][19] = $status;
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $mi_row;
	}
$output_arr = mt_list($sortby, $sortdir);
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