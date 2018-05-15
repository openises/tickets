<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */
extract ($_GET);
$internet = ((array_key_exists('internet', $_SESSION)) && ($_SESSION['internet'] == true)) ? true: false;
$sortby = (!(array_key_exists('sort', $_GET))) ? "member_id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];
$istest = FALSE;
$output_arr = array();
$num_rows = 0;
$by_severity = array(0, 0, 0);
$sev_color = array('blue','green','red');

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

function member_list() {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	@session_start();		// 
	
	// initiate arrays
	$member_row = array();

	// search rules

	$query = "SELECT `m`.`_on` AS `updated`,
		`t`.`id` AS `type_id`, 
		`s`.`id` AS `status_id`, 
		`m`.`id` AS `member_id`, 
		`m`.`field6` AS `middle_name`, 
		`m`.`field1` AS `surname`, 
		`m`.`field2` AS `firstname`, 
		`m`.`field9` AS `street`, 
		`m`.`field10` AS `city`, 	
		`m`.`field11` AS `postcode`, 
		`m`.`field21` AS `member_status_id`, 
		`m`.`field7` AS `membertype`, 
		`m`.`field12` AS `lat`, 
		`m`.`field13` AS `lng`, 
		`m`.`field25` AS `contact`, 	
		`m`.`field17` AS `joindate`, 
		`m`.`field16` AS `duedate`, 
		`m`.`field18` AS `dob`, 	
		`m`.`field19` AS `crb`, 
		`m`.`field4` AS `teamno`,
		`s`.`description` AS `stat_descr`, 
		`s`.`status_val` AS `status_name`, 
		`t`.`name` AS `type_name`, 	
		`t`.`background` AS `type_background`, 
		`t`.`color` AS `type_text`, 
		`s`.`background` AS `status_background`, 
		`s`.`color` AS `status_text`, 
		`m`.`field14` AS `medical` 
		FROM `$GLOBALS[mysql_prefix]member` `m` 
		LEFT JOIN `$GLOBALS[mysql_prefix]member_types` `t` ON ( `m`.`field7` = t.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]member_status` `s` ON ( `m`.`field21` = s.id ) 	
		LEFT JOIN `$GLOBALS[mysql_prefix]team` `te` ON ( `m`.`field3` = te.id ) 
		LEFT JOIN `$GLOBALS[mysql_prefix]user` `us` ON ( `m`.`id` = us.member ) 	
		GROUP BY `member_id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$member_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			if((can_edit()) || (is_manager($row['member_id'])) || (is_curr_member($row['member_id']))) {
				$statusmenu = get_member_status_sel($row['member_id'], $row['member_status_id'], "m");
				} else {
				$statusmenu = "<SPAN style='background: " . $row['status_background'] . "; color: " . $row['status_text'] . ";'>" . $row['status_name'] . "</SPAN>";
				}
			if((can_edit()) || (is_manager($row['member_id']))) {
				$typemenu = get_type_sel($row['member_id'], $row['membertype'], "m");
				} else {
				$typemenu = "<SPAN style='background: " . $row['type_background'] . "; color: " . $row['type_text'] . ";'>" . $row['type_name'] . "</SPAN>";
				}	
			$member_row[$i][0] = $row['member_id'];
			$member_row[$i][1] = htmlentities($row['firstname'], ENT_QUOTES);
			$member_row[$i][2] = htmlentities($row['middle_name'], ENT_QUOTES);
			$member_row[$i][3] = htmlentities($row['surname'], ENT_QUOTES);
			$member_row[$i][4] = htmlentities($row['teamno'], ENT_QUOTES);
			$member_row[$i][5] = htmlentities($row['street'], ENT_QUOTES);
			$member_row[$i][6] = htmlentities($row['city'], ENT_QUOTES);
			$member_row[$i][7] = htmlentities($row['postcode'], ENT_QUOTES);
			$member_row[$i][8] = $row['lat'];
			$member_row[$i][9] = $row['lng'];
			$member_row[$i][10] = htmlentities($row['contact'], ENT_QUOTES);
			$member_row[$i][11] = htmlentities($row['membertype'], ENT_QUOTES);;
			$member_row[$i][12] = htmlentities($row['type_name'], ENT_QUOTES);
			$member_row[$i][13] = $typemenu;
			$member_row[$i][14] = htmlentities($row['status_name'], ENT_QUOTES);
			$member_row[$i][15] = $statusmenu;
			$joindate = ((strtotime($row['joindate']) == NULL) || (date("Y", strtotime($row['joindate'])) == '1970') || (date("Y", strtotime($row['joindate'])) == '0000')) ? "TBA" : date("d-m-Y", strtotime($row['joindate']));
			$duedate = ((strtotime($row['duedate']) == NULL) || (date("Y", strtotime($row['duedate'])) == '1970') || (date("Y", strtotime($row['duedate'])) == '0000')) ? "TBA" : date("d-m-Y", strtotime($row['duedate']));
			$updated = ((strtotime($row['updated']) == NULL) || (date("Y", strtotime($row['updated'])) == '1970') || (date("Y", strtotime($row['updated'])) == '0000')) ? "TBA" : date("d-m-Y", strtotime($row['updated']));
			$member_row[$i][16] = $joindate;
			$member_row[$i][17] = $duedate;
			$member_row[$i][18] = $updated;
			$member_row[$i][19] = htmlentities($row['firstname'], ENT_QUOTES) . " " . htmlentities($row['surname'], ENT_QUOTES);
			$member_row[$i][20] = htmlentities(substr($row['teamno'], 0, 3), ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		return $member_row;
		}
	}
$output_arr = member_list();
if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}

switch($sortby) {
	case 'id':
		$sortval = 0;
		break;
	case 'firstname':
		$sortval = 1;
		break;
	case 'middle_name':
		$sortval = 2;
		break;
	case 'surname':
		$sortval = 3;
		break;
	case 'teamno':
		$sortval = 4;
		break;
	case 'street':
		$sortval = 5;
		break;
	case 'city':
		$sortval = 6;
		break;
	case 'postcode':
		$sortval = 7;
		break;
	case 'contact':
		$sortval = 10;
		break;
	case 'membertype':
		$sortval = 11;
		break;
	case 'type_name':
		$sortval = 12;
		break;
	case 'status_name':
		$sortval = 14;
		break;
	case 'joindate':
		$sortval = 16;
		break;
	case 'duedate':
		$sortval = 17;
		break;
	case 'updated':
		$sortval = 18;
		break;
	default:
		$sortval = 0;
	}


if($num_rows > 0) {
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
		$the_output[0][22] = $by_severity[0];		
		$the_output[0][23] = $by_severity[1];		
		$the_output[0][24] = $by_severity[2];
		print json_encode($the_output);
		}
	} else {
	$output_arr[0][0] = 0;
	$output_arr[0][22] = 0;		
	$output_arr[0][23] = 0;		
	$output_arr[0][24] = 0;
	print json_encode($output_arr);
	}
exit();
?>