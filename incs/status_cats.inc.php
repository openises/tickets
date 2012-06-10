<?php
$hide_dispatched = 1;	// 0 is standard, disallows hiding of deployed units. 1 allows deployed units to be hidden.
$hide_status_groups = get_variable('group_or_dispatch');
/*
12/03/10 new file to support hide / show functions for facilities and units.
2/04/11 Revised SQL query in function get_category_butts() to IS NULL rather than <> NULL
2/12/11 Revised SQL in function get_category($unit) to correct error with show / hide when using setting group_or_dispatch = 0
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
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = $unit AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";	//2/12/11
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
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = $unit AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%Y') = '0000' )";	//2/12/11
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
	
function get_all_categories() {
	global $hide_status_groups, $hide_dispatched;
	$status_category=array();
	require_once('mysql.inc.php');
	if($hide_status_groups == 0) {
		$query = "SELECT `$GLOBALS[mysql_prefix]responder`.`un_status_id`, 
			`$GLOBALS[mysql_prefix]responder`.`id`, 		
			`$GLOBALS[mysql_prefix]un_status`.`status_val`, 
			`$GLOBALS[mysql_prefix]un_status`.`group`,
			`$GLOBALS[mysql_prefix]un_status`.`hide`
			FROM `$GLOBALS[mysql_prefix]responder`
			RIGHT JOIN `$GLOBALS[mysql_prefix]un_status` ON `$GLOBALS[mysql_prefix]responder`.`un_status_id`=`$GLOBALS[mysql_prefix]un_status`.`id`";	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$unit = $row['id'];
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = '{$unit}' AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%Y') = '0000' )";	//2/12/11
			$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$deployed = mysql_num_rows($result2);
			if($deployed == 0) {			
				$status_id = $row['un_status_id'];
				$status_hide = $row['hide'];
				if($status_hide == "y") {
					$status_category[$unit] = "Not Available";
					} else {
					$status_category[$unit] = "Available";
					}
				} else {
				$status_category[$unit] = "Dispatched";	
				}
			}
		} else {

		$query = "SELECT `$GLOBALS[mysql_prefix]responder`.`un_status_id`, 
			`$GLOBALS[mysql_prefix]responder`.`id`, 			
			`$GLOBALS[mysql_prefix]un_status`.`status_val`, 
			`$GLOBALS[mysql_prefix]un_status`.`group`,			
			`$GLOBALS[mysql_prefix]un_status`.`hide`
			FROM `$GLOBALS[mysql_prefix]responder`
			RIGHT JOIN `$GLOBALS[mysql_prefix]un_status` ON `$GLOBALS[mysql_prefix]responder`.`un_status_id`=`$GLOBALS[mysql_prefix]un_status`.`id`";	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$unit = $row['id'];
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = '{$unit}' AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%Y') = '0000' )";	//2/12/11
			$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$deployed = mysql_num_rows($result2);
			if($deployed == 0) {			
				if(($row['group']=="") || ($row['group']==NULL) || ($row['group']=="NULL")) {
					$status_category[$unit] = "?";
					} else {
					$status_category[$unit] = $row['group'];
					}				
				} else {
				$status_category[$unit] = "Dispatched";
				}	
			}
		}
		return $status_category;	
	}	// end function get_category($unit);
	
function get_no_units() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$num_units = mysql_num_rows($result);	//	11/29/10
	return $num_units;
	}
	
function get_session_status($curr_cats) {
	$category_stat = array();
	$cats_in_use = $curr_cats;
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

function find_hidden($curr_cats) {
	$stat_array = get_session_status($curr_cats);
	$counter=0;
	$string = "h";
	foreach($stat_array as $val) {$string == $val ? $counter++ : null;}
	return $counter;
	}

function find_showing($curr_cats) {
	$stat_array = get_session_status($curr_cats);
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
	
function get_bnd_butts() {
	$bnd_butts = array();
	$i=0;
	$query = "SELECT *, `$GLOBALS[mysql_prefix]mmarkup`.`type`, `$GLOBALS[mysql_prefix]mmarkup`.`line_name`
				FROM `$GLOBALS[mysql_prefix]mmarkup`";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$bnd_name = $row['line_name'];
		$bnd_butts[$i] = $bnd_name;
		$i++;
		}
	return $bnd_butts;
	} 	// end function get_bnd_butts()	
	
function get_bound_name($value) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id` = '{$value}'";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$bnd_name = $row['line_name'];
	return $bnd_name;
	}
	
function get_sess_boundaries() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	//	6/10/11
	$result = mysql_query($query);	//	6/10/11
	$a_all_boundaries = array();
	$all_boundaries = array();
	$al_groups = array();
	if(isset($_SESSION['viewed_groups'])) {
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}
		
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	6/10/11
		$al_groups[] = $row['group'];
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	//	6/10/11
		$result2 = mysql_query($query2);	// 4/18/11
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	//	//	6/10/11	
			if($row2['boundary'] != 0) {
				$a_all_boundaries[] = $row2['boundary'];
				}
		}
	}

	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		foreach(explode(",",$_SESSION['viewed_groups']) as $val_vg) {
			$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$val_vg';";
			$result3 = mysql_query($query3);	//	6/10/11		
			while ($row3 = stripslashes_deep(mysql_fetch_assoc($result3))) 	{
					if($row3['boundary'] != 0) {
						$all_boundaries[] = $row3['boundary'];
						}
				}
			}
		} else {
			$all_boundaries = $a_all_boundaries;
		}

	if(!isset($curr_viewed)) {	
		$x=0;	//	4/18/11
		$where2 = "WHERE (";	//	4/18/11
		foreach($al_groups as $grp) {	//	4/18/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	} else {
		$x=0;	//	4/18/11
		$where2 = "WHERE (";	//	4/18/11
		foreach($curr_viewed as $grp) {	//	4/18/11
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	}
	$where2 .= "AND `a`.`type` = 2";		
		
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `l`.`id` = `r`.`ring_fence`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
				{$where2} AND `use_with_u_rf`=1 GROUP BY `l`.`id`";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$all_boundaries[] = $row['ring_fence'];		
		}	//	End while		
		
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `l`.`id` = `r`.`excl_zone`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
				{$where2} AND `use_with_u_ex`=1 GROUP BY `l`.`id`";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$all_boundaries[] = $row['excl_zone'];		
		}	//	End while			
	return $all_boundaries;
	}

function get_bnd_session() {	
	$boundaries = array();
	$boundaries = get_sess_boundaries();
	$bnds_sess = array();
	$bn=0;
	if(!empty($boundaries)) {
		foreach($boundaries as $key => $value) {	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`='{$value}'";	
			$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$boundary_names[$bn] = $row['line_name'];
			$bn++;
			}	
		$i = 0;
		foreach($boundary_names as $key => $value) {
			$bnd_key = "show_hide_bnds_" . $value;
			if(isset($_SESSION[$bnd_key])) {
				$bnds_sess[$i] = ($_SESSION[$bnd_key]);
			} else {
				$bnds_sess[$i] = "s";
			}		
			$i++;
			}
			return $bnds_sess;
		} else {
		return 0;
		}
	}	//	end function get_bnd_session()
	
function get_bnd_session_names() {
	$bn=0;
	$tmp = array();
	$tmp = get_sess_boundaries();
	if(!empty($tmp)) {
		foreach($tmp as $key => $value) {	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`='{$value}'";	
			$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$boundary_names[$bn] = $row['line_name'];
			$bn++;
			}
		return $boundary_names;
		} else {
		return "";
		}
	}
	
function find_bnd_hidden() {
	$stat_array = get_bnd_session();
	if(!empty($stat_array)) {
		$counter=0;
		$string = "h";
		foreach($stat_array as $val) {$string == $val ? $counter++ : null;}
		return $counter;
		} else {
		return 0;
		}
	}

function find_bnd_showing() {
	$stat_array = get_bnd_session();
	if(!empty($stat_array)) {	
		$counter=0;
		$string = "s";
		foreach($stat_array as $val) {$string == $val ? $counter++ : null;}
		return $counter;
		} else {
		return 0;
		}
	}	

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