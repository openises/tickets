<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
extract($_GET);
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$sortby = (!(array_key_exists('sort', $_GET))) ? "tick_id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];
$func = (!(array_key_exists('func', $_GET))) ? 0 : $_GET['func'];
$sort_by_field = (!(array_key_exists('sortbyfield', $_GET))) ? "" : $_GET['sortbyfield'];
$sort_value = (!(array_key_exists('sort_value', $_GET))) ? "" : $_GET['sort_value'];
$my_offset = (!(array_key_exists('my_offset', $_GET))) ? 0 : $_GET['my_offset'];
$istest = FALSE;
$iw_width= "270px";					// map infowindow with
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$output_arr = array();
$num_rows = 0;
$by_severity = array(0, 0, 0);
$sev_color = array('blue','green','red');
$theFacility = get_user_facility($_SESSION['user_id']);
$gender = array();
$gender[1] = "M";
$gender[2] = "F";
$gender[3] = "T";
$gender[4] = "U";
if($theFacility == 0) {
	exit();
	}
	
$showall = (array_key_exists('showall', $_GET) && $_GET['showall'] == 'no') ? " AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')" : "";

function incident_list() {
	global $theFacility, $istest, $disposition, $patient, $incident, $num_rows, $internet, $by_severity, $sev_color, $gender, $showall;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	$pats_ary = array();
	@session_start();		// 
	// initiate arrays
	$ticket_row = array();

	//	User Groups
	
//	Count number of patients on Ticket

	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]patient` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$pats_ary[$row['ticket_id']] = $row['the_count'];
		}	
		
	// search rules

	$limit = "";
	
	$query = "SELECT *,problemstart AS tick_pstart,
		`problemend` AS `problemend`,
		`booked_date` AS `booked_date`,
		`a`.`id` AS `assign_id` ,
		`a`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
		`t`.`id` AS `tick_id`,
		`t`.`description` AS `tick_descr`,
		`t`.`problemstart` AS `problemstart`,		
		`t`.`status` AS `ticket_status`,
		`t`.`street` AS `ticket_street`,
		`t`.`city` AS `ticket_city`,
		`t`.`state` AS `ticket_state`,
		`t`.`facility` AS `facility`,
		`t`.`rec_facility` AS `rec_facility`,		
		`r`.`id` AS `unit_id`,
		`r`.`name` AS `unit_name` ,
		`r`.`type` AS `unit_type` ,
		`r`.`lat` AS `resp_lat` ,
		`r`.`lng` AS `resp_lng` ,
		`f`.`name` AS `fac_name`,
		`f`.`lat` AS `fac_lat` ,
		`f`.`lng` AS `fac_lng` ,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`lat` AS `rec_fac_lat` ,
		`rf`.`lng` AS `rec_fac_lng` ,
		`a`.`as_of` AS `assign_as_of`,
		`a`.`clear` AS `clear`,
		`in`.`type` AS `intype`, 
		`in`.`id` AS `intype_id`,
		`in`.`color` AS `color`,		
		`fn`.`origin` AS `origin`,
		`fn`.`destination` AS `fac_dest`,
		`fn`.`type` AS `fac_dealtype`,
		`fn`.`notes` AS `fac_notes`,
		`fn`.`patient` AS `fac_patient`,
		`fn`.`ETA` AS `ETA`,
		`fcc`.`category` AS `med_category`,
		`fcc`.`color` AS `med_color`,
		`fcc`.`bgcolor` AS `med_bgcolor`,
		`pa`.`fullname` AS `pat_name`,
		`pa`.`dob` AS `pat_dob`,
		`pa`.`gender` AS `pat_gender`,
		`pi`.`ins_value` AS `pat_insurance`
		FROM `$GLOBALS[mysql_prefix]assigns` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]patient_x` `px` ON (`a`.`id` = `px`.`assign_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]patient` `pa` ON (`px`.`patient_id` = `pa`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]insurance` `pi` ON (`pa`.`insurance_id` = `pi`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facnotes` `fn` ON (`a`.`ticket_id` = `fn`.`ticket_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`a`.`user_id` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	`r` ON (`a`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`t`.`facility` = `f`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`t`.`rec_facility` = `rf`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `in` ON (`t`.`in_types_id` = `in`.`id`) 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_case_cat` `fcc` ON (`fn`.`type` = `fcc`.`id`) 
		WHERE (`t`.`facility` = " . $theFacility . " OR `t`.`rec_facility` = " . $theFacility . ")" . $showall;

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$ticket_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 0;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$problemstart = strtotime($row['problemstart']);
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			$now = strtotime($now);
			$difference = round(abs($now - $problemstart) / 60,2);
			$by_severity[$row['severity']] ++;
			$sevs_arr = array();
			$sevs_arr[0] = "blue";
			$sevs_arr[1] = "green";
			$sevs_arr[2] = "red";
			$textcol_arr = array();
			$textcol_arr[0] = "white";
			$textcol_arr[1] = "black";
			$textcol_arr[2] = "black";			
			$intype = $row['intype'];
			$the_id = $row['tick_id'];
			$facOrigin = $row['origin'];
			$facDest = $row['fac_dest'];
			$facType = $row['fac_dealtype'];
			$facPatient = ($row['pat_name'] != "") ? $row['pat_name'] : $row['fac_patient'];
			$facPatient = ($facPatient != "") ? $facPatient : "Not Provided Yet";
			$facPatient = ((get_variable('facboard_hide_patient') == "0") && ($facPatient != "Not Provided Yet")) ? $facPatient : "<SPAN style='text-align: center; color: red; width: 100%; display: inline-block;'>***********</SPAN>";
			if($row['pat_name'] != "") {$facPatient .= "<BR />" . $row['pat_dob'] . "<BR />" . $gender[$row['pat_gender']] . "<BR />" . $row['pat_insurance'];}
			$facETA = ($row['ETA'] != "") ? $row['ETA'] . "<BR /><BR /><SPAN style='color: red; width: 80%; display: inline-block;'>Data from Notes</SPAN>": "";
			$facNotes = $row['fac_notes'];
			$medType = $row['med_category'];
			$medColor = $row['med_color'];
			$medBgcolor = $row['med_bgcolor'];
			$facLat = ($row['rec_facility'] != 0) ? $row['rec_fac_lat'] : $row['fac_lat'];
			$facLng = ($row['rec_facility'] != 0) ? $row['rec_fac_lng'] : $row['fac_lng'];			
			$facArr = "";
			$facClr = "";
			if($row['u2farr'] != "") {
				$temp1 = strtotime($row['u2farr']);
				$temp1_hour = date('H', $temp1);
				$temp1_mins = date('i', $temp1);
				$facArr = $temp1_hour . ":" . $temp1_mins;
				}
			if($row['clear'] != "") {				
				$temp2	= strtotime($row['clear']);
				$temp2_hour = date('H', $temp2);
				$temp2_mins = date('i', $temp2);
				$facClr = $temp2_hour . ":" . $temp2_mins;
				}
			$color = ($facType == 0) ? $textcol_arr[$row['severity']] : $medColor;
			$bgcolor = ($facType == 0) ? $sevs_arr[$row['severity']] : $medBgcolor;
			$unitName = "<BR />Unit: " . $row['unit_name'];
			$updated = format_sb_date_2($row['updated']);
			$the_scope = htmlentities(shorten($row['scope'], 30), ENT_QUOTES);
			$address_street=htmlentities(shorten($row['ticket_street'] . " " . $row['ticket_city'], 20), ENT_QUOTES);
			$num_patients = array_key_exists ($the_id , $pats_ary)? $pats_ary[$the_id]: 0;
			if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
			$locale = get_variable('locale');	// 08/03/09			
			
			$ticket_row[$i][0] = htmlentities($the_scope, ENT_QUOTES);
			$ticket_row[$i][1] = htmlentities($address_street, ENT_QUOTES);
			$ticket_row[$i][2] = $intype;
			$ticket_row[$i][3] = $num_patients;
			$ticket_row[$i][4] = $the_id;
			$ticket_row[$i][5] = $facOrigin . ", " . $unitName;
			$ticket_row[$i][6] = $facDest;
			$ticket_row[$i][7] = $medType;
			$ticket_row[$i][8] = $facPatient;
			$ticket_row[$i][9] = $facETA;			
			$ticket_row[$i][10] = $facNotes;
			$ticket_row[$i][11] = $color;
			$ticket_row[$i][12] = $bgcolor;	
			$ticket_row[$i][13] = $row['assign_id'];
			$ticket_row[$i][14] = $row['unit_id'];
			$ticket_row[$i][15] = $facArr;
			$ticket_row[$i][16] = $facClr;
			$ticket_row[$i][17] = $row['resp_lat'];
			$ticket_row[$i][18] = $row['resp_lng'];
			$ticket_row[$i][19] = $facLat;
			$ticket_row[$i][20] = $facLng;
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $ticket_row;
	}
$output_arr = incident_list();
//dump($output_arr);
print json_encode($output_arr);

exit();
?>