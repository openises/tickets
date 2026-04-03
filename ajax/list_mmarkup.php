<?php
require_once('../incs/functions.inc.php');

@session_start();
session_write_close();
$user_id = $_SESSION['user_id'];
$sortby = (!(array_key_exists('sort', $_GET))) ? "id" : sanitize_string($_GET['sort']);
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : sanitize_string($_GET['dir']);

function get_usergroups() {
    global $user_id;
    $ret_arr = array();
    $al_groups = (array_key_exists('user_groups', $_SESSION) && is_array($_SESSION['user_groups'])) ? $_SESSION['user_groups'] : array();

    if(array_key_exists('viewed_groups', $_SESSION) && trim($_SESSION['viewed_groups']) !== '') {
        $curr_viewed= explode(",",$_SESSION['viewed_groups']);
        }
    if(count($al_groups) == 0) {
        return array();
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
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `id`= ? LIMIT 1";
    $result = db_query($query, [$id]);
    if($result->num_rows != 0) {
        $row = stripslashes_deep($result->fetch_assoc());
        $ret = $row['category'];
        } else {
        $ret = "unk";
        }
    return $ret;
    }

function subval_sort($a,$subkey, $dd) {
    foreach($a as $k=>$v) {
        $val = (is_array($v) && array_key_exists($subkey, $v) && !is_null($v[$subkey])) ? (string)$v[$subkey] : '';
        $b[$k] = strtolower($val);
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
    $result = db_query($query);
    if($result->num_rows != 0) {
        $i=0;
        while ($row = stripslashes_deep($result->fetch_assoc())){
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
print json_encode($the_arr);