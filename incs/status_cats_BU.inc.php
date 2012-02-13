<?php
$hide_dispatched = 1;	// 0 is standard, disallows hiding of deployed units. 1 allows deployed units to be hidden.
$hide_status_groups = get_variable('group_or_dispatch');
/*
12/03/10 new file to support hide / show functions for facilities and units.
2/04/11 Revised SQL query in function get_category_butts() to IS NULL rather than <> NULL
*/

function get_category_butts() {
	global $hide_status_groups, $hide_dispatched;
	$category_butts = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_disp = mysql_num_rows($result);	//
	if(($num_disp > 0) && ($hide_dispatched == 1)) { $category_butts[0] = "Dispatched"; $i=1; } else { $i=0; }

	if($hide_status_groups == 1) {
		$query = "SELECT DISTINCT `group` FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			if(($row['group']=="") || ($row['group']==NULL) || ($row['group']=="NULL")) {
				$category_name = "?";
			} else {
				$category_name = $row['group'];
			}				
			$category_butts[$i] = $category_name;
			$i++;
			}
		unset($result);
	} else {
		$category_butts[$i] = "Available";
		$i++;
		$category_butts[$i] = "Not Available";
		}	
	return $category_butts;

	} 	// end function get_category_butts()

function get_category($unit) {
	global $hide_status_groups, $hide_dispatched;
	$status_category="";
	require_once('mysql.inc.php');
	if($hide_status_groups == 0) {
		$query = "SELECT `$GLOBALS[mysql_prefix]responder`.`id`,`$GLOBALS[mysql_prefix]assigns`.`clear`
				FROM `$GLOBALS[mysql_prefix]responder`
				LEFT JOIN `$GLOBALS[mysql_prefix]assigns` ON `$GLOBALS[mysql_prefix]responder`.`id`=`$GLOBALS[mysql_prefix]assigns`.`responder_id`				
				WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$deployed = mysql_num_rows($result);
		unset($result);	
		if($deployed == 0) {
			$query = "SELECT `$GLOBALS[mysql_prefix]responder`.`un_status_id`, `$GLOBALS[mysql_prefix]un_status`.`status_val`, `$GLOBALS[mysql_prefix]un_status`.`hide`
				FROM `$GLOBALS[mysql_prefix]responder`
				RIGHT JOIN `$GLOBALS[mysql_prefix]un_status` ON `$GLOBALS[mysql_prefix]responder`.`un_status_id`=`$GLOBALS[mysql_prefix]un_status`.`id`
				WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_array($result))) {
				$status_id = $row['un_status_id'];
				$status_hide = $row['hide'];
			}
			unset($result);
				if($status_hide == "y") {
					$status_category = "Not Available";
				} else {
					$status_category = "Available";
				}
		} else {
			$status_category = "Dispatched";
		}
	} else {
		$query = "SELECT `$GLOBALS[mysql_prefix]responder`.`id`,`$GLOBALS[mysql_prefix]assigns`.`clear`
				FROM `$GLOBALS[mysql_prefix]responder`
				RIGHT JOIN `$GLOBALS[mysql_prefix]assigns` ON `$GLOBALS[mysql_prefix]responder`.`id`=`$GLOBALS[mysql_prefix]assigns`.`responder_id`				
				WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$deployed = mysql_num_rows($result);
		unset($result);		
		if($deployed == 0) {	
			$query = "SELECT `$GLOBALS[mysql_prefix]responder`.`un_status_id`, `$GLOBALS[mysql_prefix]un_status`.`status_val`, `$GLOBALS[mysql_prefix]un_status`.`group`
					FROM `$GLOBALS[mysql_prefix]responder`
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status` ON `$GLOBALS[mysql_prefix]responder`.`un_status_id`=`$GLOBALS[mysql_prefix]un_status`.`id`
					WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				while ($row = stripslashes_deep(mysql_fetch_array($result))) {
					if(($row['group']=="") || ($row['group']==NULL) || ($row['group']=="NULL")) {
						$category_name = "?";
					} else {
						$category_name = $row['group'];
					}				
					$status_category = $category_name;			
				}
			} else {
			$status_category = "Dispatched";
			}
		}
	return $status_category;	
	}	// end function get_category($unit);

function get_session_status() {
	$category_stat = array();
	$cats_in_use = get_category_butts();
	$i = 0;
	foreach($cats_in_use as $key => $value) {
		$cat_key = "show_hide_" . $value;
		if(isset($_SESSION[$cat_key])) {
			$category_stat[$i] = ($_SESSION[$cat_key]);
		} else {
			$category_stat[$i] = "s";
		}		
		$i++;
		}
	return $category_stat;
	}	//	end function get_session_status()

function find_hidden() {
	$stat_array = get_session_status();
	$counter=0;
	$string = "h";
	foreach($stat_array as $val) {$string == $val ? $counter++ : null;}
	return $counter;
	}

function find_showing() {
	$stat_array = get_session_status();
	$counter=0;
	$string = "s";
	foreach($stat_array as $val) {$string == $val ? $counter++ : null;}
	return $counter;
	}
	
function count_units() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$units_no = mysql_affected_rows();
	return $units_no;
	}

function get_fac_category_butts() {
	$fac_category_butts = array();
	$i=0;
	$query = "SELECT DISTINCT `$GLOBALS[mysql_prefix]facilities`.`type`, `$GLOBALS[mysql_prefix]fac_types`.`name`
				FROM `$GLOBALS[mysql_prefix]facilities`
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.`type`=`$GLOBALS[mysql_prefix]fac_types`.`id`	
				ORDER BY `$GLOBALS[mysql_prefix]fac_types`.`name` ASC";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$fac_category_name = $row['name'];
		$fac_category_butts[$i] = $fac_category_name;
		$i++;
		}
	return $fac_category_butts;
	} 	// end function get_fac_category_butts()

function get_fac_category($facility) {
	$fac_category="";
	require_once('mysql.inc.php');
	$query = "SELECT `$GLOBALS[mysql_prefix]facilities`.`type`, `$GLOBALS[mysql_prefix]fac_types`.`name`
			FROM `$GLOBALS[mysql_prefix]facilities`
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.`type`=`$GLOBALS[mysql_prefix]fac_types`.`id`
			WHERE `$GLOBALS[mysql_prefix]facilities`.`id` = $facility";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
			$facility_type = $row['name'];
			}				
	return $facility_type;	
	}	// end function get_fac_category($facility);

function get_fac_session_status() {
	$fac_category_stat = array();
	$fac_cats_in_use = get_fac_category_butts();
	$i = 0;
	foreach($fac_cats_in_use as $key => $value) {
		$fac_cat_key = "show_hide_fac_" . $value;
		if(isset($_SESSION[$fac_cat_key])) {
			$fac_category_stat[$i] = ($_SESSION[$fac_cat_key]);
		} else {
			$fac_category_stat[$i] = "h";
		}		
		$i++;
		}
	return $fac_category_stat;
	}	//	end function get_fac_session_status()

function find_fac_hidden() {
	$fac_stat_array = get_fac_session_status();
	$fac_counter=0;
	$fac_string = "h";
	foreach($fac_stat_array as $val) {$fac_string == $val ? $fac_counter++ : null;}
	return $fac_counter;
	}

function find_fac_showing() {
	$fac_stat_array = get_fac_session_status();
	$fac_counter=0;
	$fac_string = "s";
	foreach($fac_stat_array as $val) {$fac_string == $val ? $fac_counter++ : null;}
	return $fac_counter;
	}

function count_facilities() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$facilities_no = mysql_affected_rows();
	return $facilities_no;
	}

?>