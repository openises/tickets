<?php
/*
*/
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');

function get_fieldid($theval) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]defined_fields` WHERE `label` = '$theval' LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result)); 
	$ret_val = "field" . $row['field_id'];
	return $ret_val;
	}
	
function get_training($id) {
	$query = "SELECT `package_name`, `description`, `available`, `cost` FROM `$GLOBALS[mysql_prefix]training_packages` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_event($id) {
	$query = "SELECT `event_name`, `description` FROM `$GLOBALS[mysql_prefix]events` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_capabilities($id) {
	$query = "SELECT `name`, `description` FROM `$GLOBALS[mysql_prefix]capability_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_equipment($id) {
	$query = "SELECT `equipment_name`, `spec`, `serial`, `condition` FROM `$GLOBALS[mysql_prefix]equipment_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}

function get_clothing($id) {
	$query = "SELECT `clothing_item`, `description`, `size` FROM `$GLOBALS[mysql_prefix]clothing_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_memberType($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row= mysql_fetch_assoc($result);
	return $row['name'];		
	}

function get_memberStatus($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row= mysql_fetch_assoc($result);
	return $row['status_val'];		
	}
	
function get_memberName($id) {
	$surname = "";
	$firstname = "";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row= mysql_fetch_assoc($result);
	foreach ($row as $col_name => $cell) {
		if($col_name == "_by" && $col_name != "_on" && $col_name != "_from" && $col_name != "id") {
			$col_name = substr($col_name, 5);
			$col_name = get_fieldlabel($col_name);
			if($col_name == "Surname") {$surname = $cell;}
			if($col_name == "First Name") {$firstname = $cell;}
			}
		}
	return $firstname . " " . $surname;		
	}
	
function get_vehicle($id) {
	$query = "SELECT
		`ve`.`regno` AS `vehicle_identifier`,
		`m`.`field4` AS `vehicle_owner`,		
		`ve`.`make` AS `vehicle_make`, 	
		`ve`.`model` AS `vehicle_model`, 
		`ve`.`seats` AS `vehicle_seats`,
		`ve`.`fueltype` AS `vehicle_fuel`	
		FROM `$GLOBALS[mysql_prefix]vehicles` `ve` 
		LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `ve`.`owner` = `m`.`id` ) 			
		WHERE `ve`.`id` = '$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
$report = (array_key_exists('report', $_GET)) ? $_GET['report'] : 1;
$member = (array_key_exists('member', $_GET)) ? $_GET['member'] : 0;
$team = (array_key_exists('team', $_GET)) ? $_GET['team'] : 0;
$output = array();
if($team != 0) {
	$scope = 3;
	} elseif($team == 0 && $member != 0) {
	$scope = 1;
	} elseif($team == 0 && $member == 0) {
	$scope = 2;
	} else {
	$scope = 2;
	}

switch($scope) {
	case 1:		//	scope equals 1 - individual member
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $member;
	break;
	
	case 2:		//	scope equals 1 - all members
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` ORDER by `field4`";
	break;
	
	case 3:		//	scope equals 1 - all members in specific team
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `field3` = " . $team . " ORDER by `id`";
	break;
	}
$result = mysql_query($query);

$class='even';
if($report != 8) {
	while ($row = mysql_fetch_assoc($result)) {
		if($report == 7) {
			$print = "<TABLE style='width: 100%; border: 1px solid #707070;'>";
			$member = $row['id'];
			$name = $row['field2'] . " " . $row['field1'];
			$teamid = $row['field4'];
			$print .= "<TR><TD class='reportheading text_center' COLSPAN=99>" . $teamid . "  --  " . $name . "</TD></TR>";			
			} else {
			$print = "<TABLE style='width: 100%; border: 1px solid #707070;'>";
			$member = $row['id'];
			$name = $row['field2'] . " " . $row['field1'];
			$teamid = $row['field4'];
			$print .= "<TR><TD class='reportheading text_center' COLSPAN=99>" . $teamid . "  --  " . $name . "</TD></TR>";
			$cols = array('Surname', 'First Name', 'Team', 'Street', 'City', 'Postcode', 'Subscriptions Paid', 'Membership Due date', 'Member Type', 'Member Status', 'Picture');
			foreach ($row as $col_name => $cell) {
				if($col_name != "_by" && $col_name != "_on" && $col_name != "_from" && $col_name != "id") {
					if($col_name != 'id') {
						$col_name = substr($col_name, 5);
						$col_name = get_fieldlabel($col_name);
						}
					if($col_name != "Not Used") {
						if(in_array($col_name, $cols)) {
							if($col_name == "Picture") {
								$class = ($class == "even") ? "odd" : "even";
								$cell = ($cell != "") ? $cell : "./images/no_image.jpg";
								$print .= "<TR class='" . $class . "'><TD class='td_label text td_border'>" . $col_name . "</TD><TD class='td_data_wrap text td_border'><img src='" . $cell . "' alt='Member Picture' height='200'></TD></TR>";
								} elseif($col_name == "Member Type") {
								$class = ($class == "even") ? "odd" : "even";
								$print .= "<TR class='" . $class . "'><TD class='td_label text td_border'>" . $col_name . "</TD><TD class='td_data_wrap text td_border'>" . get_memberType($cell) . "</TD></TR>";
								} elseif($col_name == "Member Status") {
								$class = ($class == "even") ? "odd" : "even";
								$print .= "<TR class='" . $class . "'><TD class='td_label text td_border'>" . $col_name . "</TD><TD class='td_data_wrap text td_border'>" . get_memberStatus($cell) . "</TD></TR>";
								} elseif($col_name == "Team") {
								$class = ($class == "even") ? "odd" : "even";
								$print .= "<TR class='" . $class . "'><TD class='td_label text td_border'>" . $col_name . "</TD><TD class='td_data_wrap text td_border'>" . get_Teamname($cell) . "</TD></TR>";
								} elseif($col_name == "Membership Due date") {
								$class = ($class == "even") ? "odd" : "even";
								$print .= "<TR class='" . $class . "'><TD class='td_label text td_border'>" . $col_name . "</TD><TD class='td_data_wrap text td_border'>" . format_dateonly($cell) . "</TD></TR>";
								} else {
								$class = ($class == "even") ? "odd" : "even";
								$print .= "<TR class='" . $class . "'><TD class='td_label text td_border'>" . $col_name . "</TD><TD class='td_data_wrap text td_border'>" . $cell . "</TD></TR>";
								}
							}
						}
					}
				}
			}
		print $print;
		$print = "";
		if($report == 1 || $report == 5) {
			$query1 = "SELECT `refresh_due` AS `refresh_due`, 
					`completed` AS `completed`,
					`skill_id` AS `skill_id`
					FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 1";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1) > 0) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Training</SPAN></TD></TR>";
					$print .= "<TR><TD class='heading2'>Training Package</TD><TD class='heading2'>Description</TD><TD class='heading2'>Available</TD><TD class='heading2'>Cost</TD><TD class='heading2'>Completed</TD><TD class='heading2'>Refresh Due</TD></TR>";	
					$class='even';
					while ($row1 = mysql_fetch_assoc($result1)) {
						$print .= "<TR class='" . $class . "'>";
						$completed = format_dateonly($row1['completed']);
						$refresh_due = format_dateonly($row1['refresh_due']);
						$skillid = $row1['skill_id'];
						$training_arr = get_training($skillid);
						$training_arr['completed'] = $completed;
						$training_arr['refresh due'] = $refresh_due;
						foreach ($training_arr as $col_name => $cell) {
							$print .= "<TD class='td_data_wrap text'>" . $cell . "</TD>";
							}
						$class = ($class == "even") ? "odd" : "even";
						$print .= "</TR>";
						}
					$print .= "</TABLE></TD></TR>";
					}
				}
			}
		if($report == 1 || $report == 5) {
			$query1 = "SELECT `_on` AS `updated`, 
					`skill_id` AS `skill_id` 
					FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 2";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1) > 0) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Other Capabilities</SPAN></TD></TR>";
					$print .= "<TR><TD class='heading2'>Capability</TD><TD class='heading2'>Description</TD><TD class='heading2'>Registered</TD></TR>";	
					$class='even';
					while ($row1 = mysql_fetch_assoc($result1)) {
						$print .= "<TR class='" . $class . "'>";
						$skillid = $row1['skill_id'];
						$registered = format_dateonly($row1['updated']);
						$capabilities_arr = get_capabilities($skillid);
						$capabilities_arr['registered'] = $registered;
						foreach ($capabilities_arr as $col_name => $cell) {
							$print .= "<TD class='td_data_wrap text'>" . $cell . "</TD>";
							}
						$class = ($class == "even") ? "odd" : "even";
						$print .= "</TR>";
						}
					$print .= "</TABLE></TD></TR>";
					}
				}
			}
		if($report == 2 || $report == 5) {
			$query1 = "SELECT `_on` AS `updated`, 
					`skill_id` AS `skill_id` 
					FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 3";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1) > 0) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Equipment</SPAN></TD></TR>";
					$print .= "<TR><TD class='heading2'>Item</TD><TD class='heading2'>Spec</TD><TD class='heading2'>Serial</TD><TD class='heading2'>Condition</TD><TD class='heading2'>Allocated</TD></TR>";	
					$class='even';
					while ($row1 = mysql_fetch_assoc($result1)) {
						$print .= "<TR class='" . $class . "'>";
						$skillid = $row1['skill_id'];
						$allocated = format_dateonly($row1['updated']);
						$equipment_arr = get_equipment($skillid);
						$equipment_arr['allocated'] = $allocated;
						foreach ($equipment_arr as $col_name => $cell) {
							$print .= "<TD class='td_data_wrap text'>" . $cell . "</TD>";
							}
						$class = ($class == "even") ? "odd" : "even";
						$print .= "</TR>";
						}
					$print .= "</TABLE></TD></TR>";
					}
				}
			}
		if($report == 3 || $report == 5) {
			$query1 = "SELECT `_on` AS `updated`, 
					`skill_id` AS `skill_id` 
					FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 4";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1) > 0) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Vehicles</SPAN></TD></TR>";
					$print .= "<TR><TD class='heading2'>Reg No</TD><TD class='heading2'>Owner</TD><TD class='heading2'>Make</TD><TD class='heading2'>Model</TD><TD class='heading2'>Seats</TD><TD class='heading2'>Fuel</TD></TR>";				
					$class='even';
					while ($row1 = mysql_fetch_assoc($result1)) {
						$skillid = $row1['skill_id'];
						$vehicles_arr = get_vehicle($skillid);
						if($vehicles_arr) {
							$print .= "<TR class='" . $class . "'>";
							foreach ($vehicles_arr as $col_name => $cell) {
								$print .= "<TD class='td_data_wrap text'>" . $cell . "</TD>";
								$class = ($class == "even") ? "odd" : "even";
								}
							$class = ($class == "even") ? "odd" : "even";
							$print .= "</TR>";
							} else {
							$print .= "<TR class='" . $class . "'><TD class='td_data' COLSPAN=99>No Vehicle</TD></TR>";
							}
						}
					$print .= "</TABLE></TD></TR>";
					} else {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Vehicles</SPAN></TD></TR>";
					$print .= "<TR><TD class='heading2'>Reg No</TD><TD class='heading2'>Owner</TD><TD class='heading2'>Make</TD><TD class='heading2'>Model</TD><TD class='heading2'>Seats</TD><TD class='heading2'>Fuel</TD></TR>";				
					$class='even';
					$print .= "<TR class='" . $class . "'><TD class='td_data' style='text-align: center;' COLSPAN=99>No Vehicle</TD></TR>";
					$print .= "</TABLE></TD></TR>";
					}
				}
			}
		if($report == 4 || $report == 5) { 
			$query1 = "SELECT `_on` AS `updated`, 
					`skill_id` AS `skill_id` 
					FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 5";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1) > 0) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Clothing</SPAN></TD></TR>";
					$print .= "<TR><TD class='heading2'>Item</TD><TD class='heading2'>Description</TD><TD class='heading2'>Size</TD><TD class='heading2'>Allocated</TD></TR>";				
					$class='even';		
					while ($row1 = mysql_fetch_assoc($result1)) {
						$print .= "<TR class='" . $class . "'>";
						$skillid = $row1['skill_id'];
						$allocated = format_dateonly($row1['updated']);
						$clothing_arr = get_clothing($skillid);
						$clothing_arr['allocated'] = $allocated;
						foreach ($clothing_arr as $col_name => $cell) {
							$print .= "<TD class='td_data_wrap text'>" . $cell . "</TD>";
							}
						$class = ($class == "even") ? "odd" : "even";
						$print .= "</TR>";
						}
					$print .= "</TABLE></TD></TR>";
					}
				}
			}
		if($report == 6) {
			$thestring = "";
			$temp = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
			$datetoday = strtotime($temp);
			$query1 = "SELECT *, `refresh_due` AS `refresh_due`,`$GLOBALS[mysql_prefix]allocations`.`id` AS `all_id`,`$GLOBALS[mysql_prefix]member`.`id` AS `member_id` FROM `$GLOBALS[mysql_prefix]allocations`
						LEFT JOIN `$GLOBALS[mysql_prefix]member` ON `$GLOBALS[mysql_prefix]allocations`.`member_id`=`$GLOBALS[mysql_prefix]member`.`id`
						LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` ON `$GLOBALS[mysql_prefix]allocations`.`skill_id`=`$GLOBALS[mysql_prefix]training_packages`.`id`					
						WHERE `member_id` = " . $member . " AND `skill_type` = '1' AND (`refresh_due` < " . $datetoday . "  + INTERVAL 1 MONTH))";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1)) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Training Due</SPAN></TD></TR>";
					$class='even';
					while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
						$print .= "<TR class='" . $class . "'>";
						$print .= "<TD class='td_data_wrap text'>" . $row1['package_name'] . "</TD>";
						$numDays = abs($today - strtotime($row1['refresh_due']))/60/60/24;
						if($numDays >= 10) {
							$theFlag = "style='background-color: red; color: #000000; font-weight: bold;'";
							} else {
							$theFlag = "";
							}
						$print .= "<TD class='td_data_wrap text' " . $theFlag . ">Due: &nbsp;" . format_dateonly($row1['refresh_due']) . "</TD>";
						$print .= "</TR>";
						}
						$print .= "</TABLE></TD></TR>";			
					} else {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Training Due</SPAN></TD></TR>";
					$class='even';
					$print .= "<TR class='" . $class . "'>";
					$print .= "<TD COLSPAN = 99 class='td_data_wrap text' style='text-align: center;'><B>No training is due for renewal within the next month</B></TD>";
					$print .= "</TR>";
					$print .= "</TABLE></TD></TR>";					
					}
				}
			}
		if($report == 7 || $report == 5) {
			$thestring = "";
			$temp = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
			$datetoday = strtotime($temp);
			$query1 = "SELECT *, `start` AS `start`, `end` AS `end`, `$GLOBALS[mysql_prefix]allocations`.`id` AS `all_id`,`$GLOBALS[mysql_prefix]member`.`id` AS `member_id` FROM `$GLOBALS[mysql_prefix]allocations`
						LEFT JOIN `$GLOBALS[mysql_prefix]member` ON `$GLOBALS[mysql_prefix]allocations`.`member_id`=`$GLOBALS[mysql_prefix]member`.`id`
						LEFT JOIN `$GLOBALS[mysql_prefix]events` ON `$GLOBALS[mysql_prefix]allocations`.`skill_id`=`$GLOBALS[mysql_prefix]events`.`id`					
						WHERE `member_id` = " . $member . " AND `skill_type` = '6' ORDER BY `start`";
			$result1 = mysql_query($query1);
			if(!$result1) {
				} else {
				if(mysql_num_rows($result1)) {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Events Attended</SPAN></TD></TR>";
					$print .= "<TR CLASS='heading2'>";
					$print .= "<TD CLASS='heading2'>Event</TD>";
					$print .= "<TD CLASS='heading2'>Description</TD>";
					$print .= "<TD CLASS='heading2'>Start Date</TD>";
					$print .= "<TD CLASS='heading2'>End Date</TD>";
					$print .= "</TR>";					
					$class='even';
					while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
						$print .= "<TR class='" . $class . "'>";
						$print .= "<TD class='td_data_wrap text'>" . $row1['event_name'] . "</TD>";
						$print .= "<TD class='td_data_wrap text'>" . $row1['description'] . "</TD>";
						$print .= "<TD class='td_data_wrap text'>" . do_datestring(strtotime($row1['start'])) . "</TD>";
						$print .= "<TD class='td_data_wrap text'>" . do_datestring(strtotime($row1['end'])) . "</TD>";
						$print .= "</TR>";
						}
						$print .= "</TABLE></TD></TR>";			
					} else {
					$print .= "<TR><TD COLSPAN=99><TABLE style='width:100%; border: 1px solid #707070;'>";
					$print .= "<TR><TD COLSPAN=99><SPAN class='heading1'>Events Attended</SPAN></TD></TR>";
					$class='even';
					$print .= "<TR class='" . $class . "'>";
					$print .= "<TD COLSPAN = 99 class='td_data_wrap text' style='text-align: center;'><B>Member has not attended any events</B></TD>";
					$print .= "</TR>";
					$print .= "</TABLE></TD></TR>";					
					}
				}
			}
		
		$print .= "<TR class='spacer'><TD COLSPAN=99 class='spacer'>&nbsp;</TD></TR>";
		$print .= "</TABLE>";
		$print .= "<div class='pagebreak'> </div>";
		print $print;
		}
	} else {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` ORDER by `field4`";
	$result = mysql_query($query);
	$cols = array('Team ID', 'First Name', 'Surname', 'Member Type', 'Mobile', 'Email', 'City', 'Picture');
	$print = "<TABLE style='width: 100%; border: 1px solid #707070;'>";
	$print .= "<TR><TD class='reportheading' COLSPAN=99>CONTACT LIST</TD></TR>";
	$print .= "<TR>";
	foreach($cols as $val) {
		$print .= "<TD style='font-weight: bold;'>" . $val . "</TD>";
		}
	$print .= "</TR>";
	$status_arr = array(6,7,8);
	while ($row = mysql_fetch_assoc($result)) {
		$member = $row['id'];
		$name = $row['field2'] . " " . $row['field1'];
		$print .= "<TR>";
		$temp = array();
		foreach ($row as $col_name => $cell) {
			$col_name = substr($col_name, 5);
			$col_name = get_fieldlabel($col_name);
			if(!in_array($row['field7'], $status_arr)) {
				if($col_name == "Team ID") {
					$temp[0] = "<TD>" . $cell . "</TD>";
					} elseif($col_name == "First Name") {
					$temp[1] = "<TD>" . $cell . "</TD>";
					} elseif($col_name == "Last Name" || $col_name == "Surname") {
					$temp[2] = "<TD>" . $cell . "</TD>";
					} elseif($col_name == "Member Type") {
					$temp[3] = "<TD>" . get_memberType($cell) . "</TD>";
					} elseif($col_name == "Cellphone") {
					$temp[4] = "<TD>" . $cell . "</TD>";
					} elseif($col_name == "Email Address" || $col_name == "Primary Email Address") {
					$temp[5] = "<TD>" . $cell . "</TD>";
					} elseif($col_name == "City") {
					$temp[6] = "<TD>" . $cell . "</TD>";
					} elseif($col_name == "Picture") {
					if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {$protocol = 'http://';} else {$protocol = 'https://';}
					$parent = dirname(dirname($_SERVER['REQUEST_URI']));
					$baseurl = $_SERVER['SERVER_NAME'] . $parent;
					$cell = ($cell != "") ? $protocol . $baseurl . "/" . substr($cell, 2) : $protocol . $baseurl . "/no_image.jpg";
					$temp[7] = "<TD class='td_data_wrap text'><img src='" . $cell . "' alt='Member Picture' height='100'></TD>";
					}
				}
			}
			ksort($temp);
			foreach($temp as $val) {
				$print .= $val;
				}
		$print .= "</TR>";
		}
	$print .= "<TABLE></TD></TR>";
	print $print;		
	}

?>
