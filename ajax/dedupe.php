<?php
require_once('../incs/functions.inc.php');

do_login(basename(__FILE__));
error_reporting(E_ALL);
set_time_limit(0);

$dupeCaptions = 0;
$dupeStates = 0;
$dupeCodes = 0;
$dupeHints = 0;
$dupeInsurance = 0;

function cleanup_captions() {
    global $dupeCaptions;
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions`";
    $result = db_query($query) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
    $num_rows = $result->num_rows;
    if($num_rows > 0) {
        while ($row = stripslashes_deep($result->fetch_assoc())) {
            $caption = $row['capt'];
            $repl = $row['repl'];
            $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = ?";
            $result2 = db_query($query2, [$caption]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
            $num_rows2 = $result2->num_rows;
            if($num_rows2 > 1) {
                $dupeCaptions++;
                $query3 = "DELETE FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = ?";
                $result3 = db_query($query3, [$caption]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                if($caption != $repl) {
                    $query4 = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (?, ?)";
                    $result4 = db_query($query4, [$caption, $repl]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                    } else {
                    $query4 = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (?, ?)";
                    $result4 = db_query($query4, [$caption, $caption]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                    }
                }
            }
        }
    }

function cleanup_states_translator() {
    global $dupeStates;
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
    $result = db_query($query) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
    $num_rows = $result->num_rows;
    if($num_rows > 0) {
        while ($row = stripslashes_deep($result->fetch_assoc())) {
            $name = $row['name'];
            $code = $row['code'];
            $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = ?";
            $result2 = db_query($query2, [$name]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
            $num_rows2 = $result2->num_rows;
            if($num_rows2 > 1) {
                $dupeStates++;
                $query3 = "DELETE FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = ?";
                $result3 = db_query($query3, [$name]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                $query4 = "INSERT INTO `$GLOBALS[mysql_prefix]states_translator` ( `name`, `code`) VALUES (?, ?)";
                $result4 = db_query($query4, [$name, $code]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                }
            }
        }
    }

function cleanup_codes() {
    global $dupeCodes;
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes`";
    $result = db_query($query) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
    $num_rows = $result->num_rows;
    if($num_rows > 0) {
        while ($row = stripslashes_deep($result->fetch_assoc())) {
            $code = $row['code'];
            $text = $row['text'];
            $sort = $row['sort'];
            $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` WHERE `code` = ?";
            $result2 = db_query($query2, [$code]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
            $num_rows2 = $result2->num_rows;
            if($num_rows2 > 1) {
                $dupeCodes++;
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
                $by = sanitize_int($_SESSION['user_id']);
                $from = $_SERVER['REMOTE_ADDR'];
                $query3 = "DELETE FROM `$GLOBALS[mysql_prefix]codes` WHERE `code` = ?";
                $result3 = db_query($query3, [$code]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                $query4 = "INSERT INTO `$GLOBALS[mysql_prefix]codes`
                ( `code`, `text`, `sort`, `_by`, `_from`, `_on`)
                VALUES (?, ?, ?, ?, ?, ?)";
                $result4 = db_query($query4, [$code, $text, $sort, $by, $from, $now]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                }
            }
        }
    }

function cleanup_hints() {
    global $dupeHints;
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]hints`";
    $result = db_query($query) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
    $num_rows = $result->num_rows;
    if($num_rows > 0) {
        while ($row = stripslashes_deep($result->fetch_assoc())) {
            $tag = $row['tag'];
            $hint = $row['hint'];
            $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]hints` WHERE `tag` = ?";
            $result2 = db_query($query2, [$tag]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
            $num_rows2 = $result2->num_rows;
            if($num_rows2 > 1) {
                $dupeHints++;
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
                $by = sanitize_int($_SESSION['user_id']);
                $from = $_SERVER['REMOTE_ADDR'];
                $query3 = "DELETE FROM `$GLOBALS[mysql_prefix]hints` WHERE `tag` = ?";
                $result3 = db_query($query3, [$tag]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                $query4 = "INSERT INTO `$GLOBALS[mysql_prefix]hints`
                ( `tag`, `hint`, `_by`, `_from`, `_on`)
                VALUES (?, ?, ?, ?, ?)";
                $result4 = db_query($query4, [$tag, $hint, $by, $from, $now]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                }
            }
        }
    }

function cleanup_insurance() {
    global $dupeInsurance;
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance`";
    $result = db_query($query) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
    $num_rows = $result->num_rows;
    if($num_rows > 0) {
        while ($row = stripslashes_deep($result->fetch_assoc())) {
            $ins_value = $row['ins_value'];
            $sort_order = $row['sort_order'];
            $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` WHERE `ins_value` = ?";
            $result2 = db_query($query2, [$ins_value]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
            $num_rows2 = $result2->num_rows;
            if($num_rows2 > 1) {
                $dupeInsurance++;
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
                $by = sanitize_int($_SESSION['user_id']);
                $from = $_SERVER['REMOTE_ADDR'];
                $query3 = "DELETE FROM `$GLOBALS[mysql_prefix]insurance` WHERE `ins_value` = ?";
                $result3 = db_query($query3, [$ins_value]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                $query4 = "INSERT INTO `$GLOBALS[mysql_prefix]insurance`
                ( `ins_value`, `sort_order`, `_by`, `_from`, `_on`)
                VALUES (?, ?, ?, ?, ?)";
                $result4 = db_query($query4, [$ins_value, $sort_order, $by, $from, $now]) or do_error("", 'mysql query failed', db()->error, basename( __FILE__), __LINE__);
                }
            }
        }
    }

cleanup_captions();
cleanup_states_translator();
cleanup_codes();
cleanup_hints();
cleanup_insurance();

$doneit = ($dupeCaptions == 0 && $dupeStates == 0 && $dupeCodes == 0 && $dupeHints == 0 && $dupeInsurance == 0) ? 0: 1;

$ret_arr[0] = $doneit;
$ret_arr[1] = $dupeCaptions;
$ret_arr[2] = $dupeStates;
$ret_arr[3] = $dupeCodes;
$ret_arr[4] = $dupeHints;
$ret_arr[5] = $dupeInsurance;

print json_encode($ret_arr);
exit();
?>