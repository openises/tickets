<?php
require_once('../incs/functions.inc.php');

@session_start();
session_write_close();
$user_id = $_SESSION['user_id'];
$sortby = (!(array_key_exists('sort', $_GET))) ? "id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];

function get_usergroups() {
	global $user_id;
	$ret_arr = array();
	$al_groups = $_SESSION['user_groups'];
	
	if(array_key_exists('viewed_groups', $_SESSION)) {
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}
	if(count($al_groups) == 0) {	
		return false;
		} else {
		if(!isset($curr_viewed)) {
			$ret_arr = $al_groups;
			} else {
			$ret_arr = $curr_viewed;
			}
		}
	return $ret_arr;
	}
	
function get_categoryName($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['category'];
	}
	
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
	
function get_markup() {
	$ret_arr = array();
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup`";
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$i=0;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$ret_arr[$i]['id'] = $row['id'];
			$ret_arr[$i]['name'] = $row['line_name'];
			$ret_arr[$i]['type'] = $row['line_type'];
			$ret_arr[$i]['status'] = $row['line_status'];
			$ret_arr[$i]['ident'] = $row['line_ident'];
			$ret_arr[$i]['cat'] = get_categoryName($row['line_cat_id']);
			$ret_arr[$i]['data'] = $row['line_data'];
			$ret_arr[$i]['color'] = $row['line_color'];
			$ret_arr[$i]['opacity'] = $row['line_opacity'];
			$ret_arr[$i]['width'] = $row['line_width'];
			$ret_arr[$i]['fill_color'] = $row['fill_color'];
			$ret_arr[$i]['fill_opacity'] = $row['fill_opacity'];
			$ret_arr[$i]['filled'] = $row['filled'];
			$ret_arr[$i]['updated'] = format_date_2($row['_on']);
			$i++;
			}
		} else {
		$ret_arr[0] = 0;
		}
	return $ret_arr;
	}

if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}	
$ret_arr = get_markup();
$the_arr = subval_sort($ret_arr, $sortby, $dd);
//dump($ret_arr);
print json_encode($the_arr);
