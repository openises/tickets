<?php
/*
get_replacetext.php, gets replacement text data for standard messages
2/4/13	New File
*/
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
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

$proximity = 1000;
$unit = get_variable('warn_proximity_units');

$infotype = array();
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]replacetext_order`";
$result = mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$key = intval($row['displayorder']);
	$infotype[$key] = $row['info_name'];
	}

ksort($infotype);

function distance($lat1, $lon1, $lat2, $lon2, $unit) { 
	if(($lat1 == 0 ) || ($lon1 == 0)) { 
		return 0; 
		}
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);
	if ($unit == "K") {
		return ($miles * 1.609344); 
		} else if ($unit == "N") {
		return ($miles * 0.8684);
		} else {
		return $miles;
		}
	}

function subval_sort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}

function get_warnlocs($id) {
	if($id == 0) {return;}
	global $proximity, $unit;
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$in_lat = $row['lat'];
	$in_lng = $row['lng'];
	$ret_arr = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` ORDER BY `id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$i=0;
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$ret_arr[$i][0] = $row['id'];
			$ret_arr[$i][1] = $row['title'];
			$ret_arr[$i][2] = $row['street'];
			$ret_arr[$i][3] = $row['city'];
			$ret_arr[$i][4] = $row['state'];
			$ret_arr[$i][5] = $row['lat'];
			$ret_arr[$i][6] = $row['lng'];
			$ret_arr[$i][7] = $row['description'];
			$the_dist = distance($in_lat, $in_lng, $row['lat'], $row['lng'], $unit);
			$ret_arr[$i][8] = round($the_dist,1);
			$ret_arr[$i][9] = get_owner($row['_by']);
			$ret_arr[$i][10] = format_date_2(strtotime($row['_on']));
			$i++;
			}
		}

	$out_arr = array();
	$z = 0;	
	foreach($ret_arr as $val) {
		if($val[8] < $proximity) {
			$out_arr[$z][0] = $val[0];
			$out_arr[$z][1] = $val[1];
			$out_arr[$z][2] = $val[2];
			$out_arr[$z][3] = $val[3];
			$out_arr[$z][4] = $val[4];
			$out_arr[$z][5] = $val[7];
			$out_arr[$z][6] = $val[8];
			$out_arr[$z][7] = $val[9];
			$out_arr[$z][8] = $val[10];
			$z++;
			}
		}
	$warningsText = (count($out_arr) > 0) ? "Near Location Warnings\n" : "";
	foreach($out_arr as $val) {
		$warningsText .= $val[1] . " - " . $val[8] . "\n";
		$warningsText .= $val[2] . ", " . $val[3] . " " . $val[4] . "\n";
		$warningsText .= $val[7] . "\n";
		}	
	return $warningsText;
	}

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
	
function splitMessage($message, $delimiter) {
	$ret_arr = array();
	$ret_arr = explode($delimiter, $message);
	return $ret_arr;
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
					`t`.`facility` AS `facility`,
					`t`.`rec_facility` AS `rec_facility`,
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
					`t`.`description` AS `synopsis`,
					`t`.`street` AS `street`,
					`t`.`city` AS `city`,
					`t`.`phone` AS `phone`
					FROM `$GLOBALS[mysql_prefix]ticket` `t` 
					WHERE `t`.`id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$thestreet = ($row['street'] != "") ? $row['street'] . ", " : "";
		$the_text .= get_text('Address') . ": " . $thestreet . $row['city'] . "\n";
		$the_text .= $row['synopsis'] . "\n";
		$the_text .= get_text('Controller') . ": " . get_user_name($_SESSION['user_id']) . "\n";
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
	
function tkt_street($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `street` AS `street` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_text .= $row['street'];
		}
	return $the_text;
	}
	
function tkt_loc($id) {
	$theRet = array();
	if($id != 0) {
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$theRet[0] = $row['lat'];
		$theRet[1] = $row['lng'];		
		}
	return $theRet;
	}
	
function tkt_city($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `city` AS `city` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_text .= $row['city'];
		}
	return $the_text;
	}
	
function tkt_phone($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `phone` AS `phone` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_text .= $row['phone'];
		}
	return $the_text;
	}
	
function tkt_toaddress($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `to_address` AS `to_address` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_text .= $row['to_address'];
		}
	return $the_text;
	}
	
function tkt_dispnotes($id) {
	$the_text = "";
	if($id != 0) {
		$query	= "SELECT `comments` AS `comments` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_text .= $row['comments'];
		}
	return $the_text;
	}
	
function tkt_nature($id) {
	$the_text = "Incident Nature: \n";	
	if($id != 0) {
		$query	= "SELECT `t`.`in_types_id` AS `in_types_id`,
					`t`.`id` AS `t_id`,
					`i`.`id` AS `type_id`,
					`i`.`type` AS `in_type`
					FROM `$GLOBALS[mysql_prefix]ticket` `t` 
					LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i` ON ( `t`.`in_types_id` = `i`.`id`)					
					WHERE `t`.`id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));	
		$the_text .= $row['in_type'];
		}
	return $the_text;
	}
	
function tkt_severity($id) {
	$the_text = "Incident Severity: \n";
	$severities = array();
	$severities[] = "Normal";
	$severities[] = "Medium";
	$severities[] = "High";
	if($id != 0) {
		$query	= "SELECT *	FROM `$GLOBALS[mysql_prefix]ticket` `t` WHERE `t`.`id`='$id' LIMIT 1";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));	
		$the_text .= $severities[$row['severity']];
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
		$return[] = $row['app_phone'];
		$return[] = $row['app_street'];
		$return[] = $row['app_city'];
		$return[] = $row['app_toaddress'];
		$return[] = $row['app_dispnotes'];
		$return[] = $row['app_nature'];
		$return[] = $row['app_priority'];
		$return[] = $row['app_warnloc'];		
		return $return;
		} else {
		return false;
		}
	}
	
function getfinalmessage($rep_val) {
	global $start_tag, $end_tag, $ticket, $text_to_replace, $infotype;
	$tags = array($start_tag,$end_tag);
	$ret_text = ((isset($rep_val[0])) && ($rep_val[0] != "")) ? $rep_val[0] : "Nothing Found";
	$add_tkt_order = array_keys($infotype, 'add_ticket');
	$add_user_order = array_keys($infotype, 'add_user');
	$add_user_unit_order = array_keys($infotype, 'add_user_unit');
	$add_time_order = array_keys($infotype, 'add_time');
	$add_date_order = array_keys($infotype, 'add_date');
	$app_summ_order = array_keys($infotype, 'app_summ');
	$app_shortsumm_order = array_keys($infotype, 'app_shortsumm');
	$app_desc_order = array_keys($infotype, 'app_desc');
	$app_phone_order = array_keys($infotype, 'app_phone');
	$app_street_order = array_keys($infotype, 'app_street');
	$app_city_order = array_keys($infotype, 'app_city');
	$app_toaddress_order = array_keys($infotype, 'app_toaddress');
	$app_dispnotes_order = array_keys($infotype, 'app_dispnotes');
	$app_nature[0] = 14;
	$app_priority[0] = 15;
	$app_warnings[0] = 16;
	$output[$add_tkt_order[0]] = ($rep_val[1] == "Yes") ? "" . $ticket : "";
	$output[$add_user_order[0]] = ($rep_val[2] == "Yes") ? "" . $user_name : "";
	$output[$add_user_unit_order[0]] = ($rep_val[3] == "Yes") ? "" . get_owner_unit_handle(get_owner_unit($user)) : "";
	$output[$add_time_order[0]] = ($rep_val[4] == "Yes") ? "" . $time : "";
	$output[$add_date_order[0]] = ($rep_val[5] == "Yes") ? "" . $date : "";
	$output[$app_summ_order[0]] = ($rep_val[6] == "Yes") ? tkt_summary($ticket) . "\n" : "";
	$output[$app_shortsumm_order[0]] = ($rep_val[7] == "Yes") ? tkt_shortSummary($ticket) . "\n" : "";
	$output[$app_desc_order[0]] = ($rep_val[8] == "Yes") ? tkt_description($ticket) . "\n" : "";
	$output[$app_phone_order[0]] = ($rep_val[9] == "Yes") ? tkt_phone($ticket) . "\n" : "";
	$output[$app_street_order[0]] = ($rep_val[10] == "Yes") ? tkt_street($ticket) . "\n" : "";
	$output[$app_city_order[0]] = ($rep_val[11] == "Yes") ? tkt_city($ticket) . "\n" : "";
	$output[$app_toaddress_order[0]] = ($rep_val[12] == "Yes") ? tkt_toaddress($ticket) . "\n" : "";
	$output[$app_dispnotes_order[0]] = ($rep_val[13] == "Yes") ? tkt_dispnotes($ticket) . "\n" : "";
	$output[$app_nature[0]] = ($rep_val[14] == "Yes") ? tkt_nature($ticket) . "\n" : "";
	$output[$app_priority[0]] = ($rep_val[15] == "Yes") ? tkt_severity($ticket) . "\n" : "";
	$output[$app_warnings[0]] = ($rep_val[16] == "Yes") ? get_warnlocs($ticket) . "\n" : "";
	$theText = "";
	foreach($output as $val) {
		$theText .= $val;
		}
	return $theText;
	}
	
$ret_arr = array();
$msg_arr = splitMessage($text_to_replace, $start_tag);
$msgtxt = "";
$temp = array();
foreach($msg_arr as $val) {
	if($val != "") {
		if(isTag($val)) {
			$temp = get_replacement_text($val);
			$msgtxt .= getfinalmessage($temp);
			} else {
			$msgtxt .= $val;
			}
		}
	}
$ret_arr[0] = $msgtxt; 
		
print json_encode($ret_arr);
exit();
?>

