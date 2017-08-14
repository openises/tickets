<?php
/*
11/28/12	New incs file for portal
*/
function get_status_selection($the_id, $status_val_in) {					// returns select list as click-able string - 2/6/10
	$tablename = "requests";
	$status_field = "status";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $the_id LIMIT 1" ;	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result)); 
	$guest = is_guest();
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	if($row['status'] == 'Tentative') {
		$the_values = array('Tentative','Accepted','Resourced','Complete','Declined','Closed');
		} elseif($row['status'] == 'Accepted') {
		$the_values = array('Accepted','Resourced','Complete','Declined','Closed');
		} elseif($row['status'] == 'Resourced') {	
		$the_values = array('Resourced','Complete','Declined','Closed');
		} elseif($row['status'] == 'Complete') {		
		$the_values = array('Complete','Declined','Closed');		
		} elseif($row['status'] == 'Declined') {
		$the_values = array('Declined','Closed');	
		} elseif($row['status'] == 'Closed') {
		$the_values = array('Closed');	
		} else {
		$the_values = array("{$row['status']}");	
		}
	if(count($the_values) > 1) {
		$outstr = "<SELECT id='frm_status_" . $the_id . "' name='frm_status_id' {$dis} style='font-size: .9em; width: 100%;' ONCHANGE = 'do_sel_update({$the_id}, this.value)' >";
		foreach($the_values AS $val) {
			$sel = ($row['status'] == $val)? " SELECTED": "";
			$outstr .= "<OPTION VALUE=" . $val . $sel .">$val</OPTION>";		
			}		// end foreach()
		$outstr .= "</SELECT>";
		} else {
		$outstr = $the_values[0];
		}
	return $outstr;
	}