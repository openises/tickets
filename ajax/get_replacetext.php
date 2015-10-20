<?php
/*
get_replacetext.php, gets replacement text data for standard messages
2/4/13	New File
*/

require_once('../incs/functions.inc.php');
require_once('../incs/messaging.inc.php');

@session_start();
session_write_close();
$the_result = "";
if (empty($_SESSION)) {
	header("Location: ../index.php");
	}
do_login(basename(__FILE__));
$ticket = ((isset($_GET['tick'])) && ($_GET['tick'] != 0)) ? strip_tags($_GET['tick']) : 0;
$text_to_replace = ((isset($_GET['text'])) && ($_GET['text'] != "")) ? urldecode($_GET['text']) : "";
$user = $_SESSION['user_id'];
$user_name = get_owner($user);
$timestamp = (time() - (intval(get_variable('delta_mins'))*60));
$time = date("H:i", $timestamp);
$date = date("d-m-Y", $timestamp);
$start_tag = "|";
$end_tag = "|";
	
function get_user_name($id){							//	get User Name from id , 1/8/14
	$result	= mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id`= '$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "unk?" : $row['name_f'] . " " . $row['name_l'];
	}

function get_owner_unit($id){								/* get owner unit name from id */
	$result	= mysql_query("SELECT responder_id FROM `$GLOBALS[mysql_prefix]user` WHERE `id`='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? 0 : $row['responder_id'];
	}

function get_owner_unit_handle($id){								/* get owner unit name from id */
	$result	= mysql_query("SELECT handle FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "" : $row['handle'];
	}	
	

function replace_content_inside_delimiters($start, $end, $new, $source) {
	$thetxt = preg_replace('#('.preg_quote($start).')(.*)('.preg_quote($end).')#si', '$1'.$new.'$3', $source);
	$tags = array($start,$end);	
	$thetxt = str_replace($tags, "", $thetxt);
	return $thetxt;
	}
	
function get_coords($lat, $lng) {
	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
			$ret = "USNG: " . LLtoUSNG($lat, $lng);
			break;
		case "1":
			$ret = "OSGB: " . LLtoOSGB($lat, $lng) ;
			break;			
		default:
			$ret = "UTM: " . toUTM($lat, $lng);
			break;
		}
	return $ret;
	}
	
function tkt_summary($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `t`.`scope` AS `scope`,
					`t`.`id` AS `t_id`,
					`t`.`contact` AS `contact`,
					`t`.`street` AS `street`,
					`t`.`city` AS `city`,
					`t`.`phone` AS `phone`,
					`t`.`description` AS `synopsis`,
					`t`.`lat` AS `lat`,
					`t`.`lng` AS `lng`,
					`i`.`id` AS `type_id`,
					`i`.`type` AS `in_type`,
					`f`.`id` AS `f_id`, 
					`f`.`name` AS `fac_name`, 
					`f`.`street` AS `fac_street`,
					`f`.`city` AS `fac_city`,
					`f`.`description` as `fac_desc`,
					`f`.`lat` AS `fac_lat`,
					`f`.`lng` AS `fac_lng`,
					`f2`.`id` AS `f2_id`,					
					`f2`.`name` AS `recfac_name`, 
					`f2`.`street` AS `recfac_street`,
					`f2`.`city` AS `recfac_city`,
					`f2`.`description` AS `recfac_desc`,	
					`f2`.`lat` AS `recfac_lat`,	
					`f2`.`lng` AS `recfac_lng`							
					FROM `$GLOBALS[mysql_prefix]ticket` `t` 
					LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON ( `t`.`facility` = `f`.`id`)	
					LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f2` ON ( `t`.`rec_facility` = `f2`.`id`)	
					LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i` ON ( `t`.`in_types_id` = `i`.`id`)					
					WHERE `t`.`id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));	
		$coords = get_coords($row['lat'], $row['lng']);
		$fac_add = $row['fac_street'] . ", " . $row['fac_city'];
		$fac_desc = $row['fac_desc'];
		$fac_coords = get_coords($row['fac_lat'], $row['fac_lng']);
		$recfac_add = $row['recfac_street'] . ", " . $row['recfac_city'];
		$recfac_desc = $row['recfac_desc'];		
		$recfac_coords = get_coords($row['recfac_lat'], $row['recfac_lng']);
		$the_text = get_text('Controller') . ": " . get_user_name($_SESSION['user_id']) . "\r\n";
		$the_text .= $row['scope'] . "\n";
		$the_text .= $row['contact'] . ", " . $row['phone'] . "\n";
		$the_text .= $row['street'] . ", " . $row['city'] . "\n";
		$the_text .= $row['synopsis'] . "\n";
		$the_text .= $row['in_type'] . "\n";
		$the_text .= "LAT: " . $row['lat'] . " - LNG: " . $row['lng'] . " " . $coords . "\n";
		$the_text .= ($row['facility'] != 0) ? $row['fac_name'] . "\n": "";
		$the_text .= ($row['facility'] != 0) ? $fac_desc . "\n": "";
		$the_text .= ($row['facility'] != 0) ? $fac_add . "\n": "";
		$the_text .= ($row['facility'] != 0) ? "LAT: " . $row['fac_lat'] . " - LNG: " . $row['fac_lng'] . " " . $fac_coords . "\n": "";
		$the_text .= ($row['rec_facility'] != 0) ? $row['recfac_name'] . "\n": "";
		$the_text .= ($row['rec_facility'] != 0) ? $recfac_desc . "\n": "";
		$the_text .= ($row['rec_facility'] != 0) ? $recfac_add . "\n": "";
		$the_text .= ($row['rec_facility'] != 0) ? "LAT: " . $row['recfac_lat'] . " - LNG: " . $row['recfac_lng'] . " " . $recfac_coords . "\n": "";		
		}
	return $the_text;
	}

function tkt_shortSummary($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `t`.`scope` AS `scope`,
					`t`.`id` AS `t_id`,
					`t`.`contact` AS `contact`,
					`t`.`street` AS `street`,
					`t`.`city` AS `city`,
					`t`.`phone` AS `phone`
					FROM `$GLOBALS[mysql_prefix]ticket` `t` 
					WHERE `t`.`id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$thestreet = ($row['street'] != "") ? $row['street'] . ", " : "";
		$the_text = get_text('Controller') . ": " . get_user_name($_SESSION['user_id']) . "\r\n";
		$the_text .= get_text('Scope') . ": " . $row['scope'] . "\n";
		$the_text .= get_text('Patient') . ": " . $row['contact'] . ", " . $row['phone'] . "\n";
		$the_text .= get_text('Address') . ": " . $thestreet . $row['city'] . "\n";
		}
	return $the_text;
	}
	
function tkt_description($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `t`.`scope` AS `scope`,
					`t`.`id` AS `t_id`,
					`t`.`description` AS `description`
					FROM `$GLOBALS[mysql_prefix]ticket` `t` 
					WHERE `t`.`id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_text = get_text('Controller') . ": " . get_user_name($_SESSION['user_id']) . "\r\n";
		$the_text .= get_text('Synopsis') . ": " . $row['description'] . "\n";
		}
	return $the_text;
	}

function get_replacement_text($val) {
	$return = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]replacetext` WHERE `in_text` = '" . $val . "'";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$return[] = $row['out_text'];
		$return[] = $row['add_ticket'];
		$return[] = $row['add_user'];
		$return[] = $row['add_user_unit'];
		$return[] = $row['add_time'];	
		$return[] = $row['add_date'];
		$return[] = $row['app_summ'];
		$return[] = $row['app_shortsumm'];
		$return[] = $row['app_desc'];
		return $return;
		} else {
		return false;
		}
	}
	
	
$ret_arr = array();
$foundtext = GetBetween($text_to_replace,$start_tag,$end_tag);
$rep_val = get_replacement_text($foundtext);

if($rep_val) {
	$tags = array($start_tag,$end_tag);
	$ret_text = ((isset($rep_val[0])) && ($rep_val[0] != "")) ? $rep_val[0] : "Nothing Found";
	$the_replaced_text = $rep_val[0];
	$the_replaced_text .= ($rep_val[1] == "Yes") ? " " . $ticket : "";
	$the_replaced_text .= ($rep_val[2] == "Yes") ? " " . $user_name : "";
	$the_replaced_text .= ($rep_val[3] == "Yes") ? " " . get_owner_unit_handle(get_owner_unit($user)) : "";
	$the_replaced_text .= ($rep_val[4] == "Yes") ? " " . $time : "";	
	$the_replaced_text .= ($rep_val[5] == "Yes") ? " " . $date : "";
	$thesummary = ($rep_val[6] == "Yes") ? "TKT Summary\n" . tkt_summary($ticket) . "\n" : "";	
	$theshortsummary = ($rep_val[7] == "Yes") ? "TKT Summary\n" . tkt_shortSummary($ticket) . "\n" : "";	
	$thedescsumm = ($rep_val[8] == "Yes") ? "Job Requirements\n" . tkt_description($ticket) . "\n" : "";	
	$the_output = replace_content_inside_delimiters($start_tag, $end_tag, $the_replaced_text, $text_to_replace) . "\n";
	$ret_arr[0] = $the_output . $thesummary . $theshortsummary . $thedescsumm;
	} else {
	$ret_arr[0] = "";
	}

print json_encode($ret_arr);
exit();
?>

