<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions`;";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$caption = quote_smart($row['capt']);
			$repl = quote_smart($row['repl']);
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$dupeCaptions++;
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = " . $caption . ";";
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if($caption != $repl) {
					$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (" . $caption . ", " . $repl . ");";
					$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
					} else {
					$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES (" . $caption . ", " . $caption . ");";
					$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					}
				}
			}
		}
	}
	
function cleanup_states_translator() {
	global $dupeStates;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`;";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$name = quote_smart($row['name']);
			$code = quote_smart($row['code']);
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = " . $name . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$dupeStates++;
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]states_translator` WHERE `name` = " . $name . ";";
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]states_translator` ( `name`, `code`) VALUES (" . $name . ", " . $code . ");";
				$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
				}
			}
		}
	}

function cleanup_codes() {
	global $dupeCodes;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes`;";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$code = quote_smart($row['code']);
			$text = quote_smart($row['text']);
			$sort = $row['sort'];
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` WHERE `code` = " . $code . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$dupeCodes++;
				$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
				$by = $_SESSION['user_id'];
				$from = $_SERVER['REMOTE_ADDR'];
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]codes` WHERE `code` = " . $code . ";";	//	Delete all existing entries
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]codes` 
				( `code`, `text`, `sort`, `_by`, `_from`, `_on`) 
				VALUES (" . $code . ", " . $text . ", " . $sort . ", " . $by . ", '" . $from . "', '" . $now . "');";	//	 Add a single new entry
				$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
				}
			}
		}
	}
	
function cleanup_hints() {
	global $dupeHints;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]hints`;";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$tag = quote_smart($row['tag']);
			$hint = quote_smart($row['hint']);
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]hints` WHERE `tag` = " . $tag . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$dupeHints++;
				$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
				$by = $_SESSION['user_id'];
				$from = $_SERVER['REMOTE_ADDR'];
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]hints` WHERE `tag` = " . $tag . ";";	//	Delete all existing entries
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]hints` 
				( `tag`, `hint`, `_by`, `_from`, `_on`) 
				VALUES (" . $tag . ", " . $hint . ", " . $by . ", '" . $from . "', '" . $now . "');";	//	 Add a single new entry
				$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
				}
			}
		}
	}
	
function cleanup_insurance() {
	global $dupeInsurance;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance`;";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$ins_value = quote_smart($row['ins_value']);
			$sort_order = quote_smart($row['sort_order']);
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` WHERE `ins_value` = " . $ins_value . ";";
			$result2 = mysql_query($query2) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_rows2 = mysql_num_rows($result2);
			if($num_rows2 > 1) {
				$dupeInsurance++;
				$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
				$by = $_SESSION['user_id'];
				$from = $_SERVER['REMOTE_ADDR'];
				$query3 = "DELETE FROM `$GLOBALS[mysql_prefix]insurance` WHERE `ins_value` = " . $ins_value . ";";	//	Delete all existing entries
				$result3 = mysql_query($query3) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$query4 = "INSERT INTO `$GLOBALS[mysql_prefix]insurance` 
				( `ins_value`, `sort_order`, `_by`, `_from`, `_on`) 
				VALUES (" . $ins_value . ", " . $sort_order . ", " . $by . ", '" . $from . "', '" . $now . "');";	//	 Add a single new entry
				$result4 = mysql_query($query4) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 
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