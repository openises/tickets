<?php 
/*

*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$sess_id = $_SESSION['id'];
$ret_arr = array();
if(!array_key_exists('q', $_GET) || $_GET['q'] != $_SESSION['id']) {
	$ret_arr[0] = "Error calling form";
	print json_encode($ret_arr);
	exit();
	}
if ((isset($_REQUEST['ticket_id'])) && 	(strlen(trim($_REQUEST['ticket_id']))>6)) {	shut_down();}
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
$get_action = (array_key_exists ( "action", $_REQUEST ))? $_REQUEST['action'] : "new" ;
	switch ($get_action) {
		case "add":		// db insert
			$w=720; $h=520;
			break;
		case "delete":
			$w=400; $h=240;
			break;
		case "update":
			$w=400; $h=240;
			break;
		case "list":
			$w=550; $h=300;
			break;
		case "new":	
		case "edit":
			$w=660; $h=520;
			break;

		}	

$patient = 			get_text("Patient");
$fullname =	 		get_text("Full name");
$dateofbirth =	 	get_text("Date of birth");
$gender =	 		get_text("Gender");
$insurance =	 	get_text("Insurance");
$facilitycontact = 	get_text("Facility contact");

$output = "";

$responder_details = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$responder_details[$row['id']] = $row['handle'];
	}
	
if ($get_action == 'edit') {		//get and show action to update
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id` = " . $_GET['id'];
	$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$assigned_to = $row['assign_id'];
		} else {
		$assigned_to = 0;				
		}

	$user_level = is_super() ? 9999 : $_SESSION['user_id']; 		
	$regions_inuse = get_regions_inuse($user_level);	//	5/4/11
	$group = get_regions_inuse_numbers($user_level);	//	5/4/11		
	$al_groups = $_SESSION['user_groups'];
	if(array_key_exists('viewed_groups', $_SESSION)) {	//	5/4/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		} else {
		$curr_viewed = $al_groups;
		}
	if(!isset($curr_viewed)) {	
		if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11					
			}
		} else {
		if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {				
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {	//	6/10/11
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11						
			}
		}	
	$query = "SELECT *, UNIX_TIMESTAMP(date) AS `date` FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]' LIMIT 1";	// 8/11/08
	$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	if ( can_edit()) {										// 8/27/10
		$hdr_str = "Edit";
		$dis = "";
		} else {
		$hdr_str = "Showing";
		$dis = "DISABLED";
		}
	$heading = $hdr_str . " " . $patient . "Record";
	$output .= "<DIV STYLE='position: relative; top: 10px; left: 10px; padding: 10px; width: 95%;'>";
	$output .= "<FORM ID='patientAdd' NAME='patientEd' METHOD='post' ACTION='./ajax/form_post.php?id=" . $_GET['id'] . "&ticket_id=" . $_GET['ticket_id'] . "&q=". $sess_id . "&function=editpatient'>";	
	$output .= "<TABLE BORDER='0' STYLE='margin-left: 20px; position: relative; top: 70px;'>";
	$output .= "<TR CLASS='even' >";
	$output .= "<TD CLASS='td_label text'>" . get_text('Patient ID') . ": <font color='red' size='-1'>*</font></TD>";
	$output .= "<TD CLASS='td_data text'><INPUT TYPE='text' NAME='frm_name' value='" . $row['name'] . "' size='32' " . $dis . "></TD>";
	$output .= "</TR>";
	$checks = array("", "", "", "", "");		// gender checks
	$checks[intval($row['gender'])] = "CHECKED";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
	$result = mysql_query($query);
	if(@mysql_num_rows($result) > 0) {
		$ins_sel_str = "<SELECT CLASS='sit' name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
		
		while ($row_ins = stripslashes_deep(mysql_fetch_assoc($result))) {
			$sel = (intval($row['insurance_id']) == intval($row_ins['id']))? "SELECTED": "";
			$ins_sel_str .= "\t\t\t<OPTION VALUE={$row_ins['id']} {$sel}>{$row_ins['ins_value']}</OPTION>\n";		
			}		// end while()
		$ins_sel_str .= "</SELECT>\n";

		$output .= "<TR CLASS='odd' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>" . $fullname . ": &nbsp;&nbsp;</TD>";
		$output .= "<TD CLASS='td_data text'>";
		$output .= "<INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='" . $row['fullname'] . "' SIZE = '64' " . $dis . " />";
		$output .= "</TD>";
		$output .= "</TR>";
		$output .= "<TR CLASS='even' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>" . $dateofbirth . ": &nbsp;&nbsp;</TD>";
		$output .= "<TD CLASS='td_data text'>";
		$output .= "<INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='" . $row['dob'] . "' SIZE = '24' />";
		$output .= "</TD>";
		$output .= "</TR>";
		$output .= "<TR CLASS='odd' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>";
		$output .= $gender . ": ";  
		if(get_variable('locale') != 1) {
			$output .= "<font color='red' size='-1'>*</font>";
			}
		$output .= "</B>&nbsp;&nbsp;";
		$output .= "</TD>";
		$output .= "<TD CLASS='td_data text'>";
		$output .= "&nbsp;&nbsp;";
		$output .= "M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;'" . $checks[1] . " " . $dis . " />";
		$output .= "&nbsp;&raquo;F&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;'" . $checks[2] . " " . $dis . " />";
		$output .= "&nbsp;&raquo;T&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;'" . $checks[3] . " " . $dis . " />";
		$output .= "&nbsp;&raquo;U&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;'" . $checks[4] . " " . $dis . " />";		
		$output .= "</TD>";
		$output .= "</TR>";
		if(get_variable('locale') != 1) {
			$output .= "<TR CLASS='even' VALIGN='bottom'>";
			$output .= "<TD CLASS='td_label text'>" . $insurance . ": <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>";
			$output .= "<TD CLASS='td_data text'>" . $ins_sel_str . "</TD>";
			$output .= "</TR>";
			}
		$query_fac = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )		
			$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";		
		$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$pulldown = '<option value = 0 selected>Select</option>\n'; 	// 3/18/10
			while ($row_fac = mysql_fetch_array($result_fac, MYSQL_ASSOC)) {
				$sel = ($row_fac['fac_id'] == $row['facility_id']) ? "SELECTED" : "";
				$pulldown .= "<option value=\"{$row_fac['fac_id']}\" {$sel}>" . $row_fac['name'] . "</option>\n";
				}	
		$output .= "<TR CLASS='odd'>";
		$output .= "<TD CLASS='td_label text'>Facility:</TD>";
		$output .= "<TD COLSPAN='2' class='td_data text'>";
		$output .= "<SELECT NAME='frm_facility_id'  tabindex=11 onChange='this.options[selectedIndex].value.trim();'>" . $pulldown . "</SELECT>";
		$output .= "</TD>";
		$output .= "</TR>";
		$output .= "<TR CLASS='odd' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>" . $facilitycontact . ": &nbsp;&nbsp;</TD>";
		$output .= "<TD CLASS='td_data text'><INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='" . $row['facility_contact'] . "' SIZE = '64' " . $dis . "/></TD>";
		$output .= "</TR>";
		}		// end 	if($num_rows>0) 
	$output .= "<TR CLASS='even'  VALIGN='top'><TD class='td_label text'>Description: </TD><TD><TEXTAREA ROWS='8' COLS='64' NAME='frm_description' WRAP='virtual' " . $dis . ">" . $row['description'] . "</TEXTAREA></TD></TR>";
	$output .= "<TR VALIGN = 'TOP' CLASS='even'>";
	$output .= "<TD  CLASS='td_label text'>Signal: </TD>";
	$output .= "<TD CLASS='td_data text'>";
	$output .= "<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;' " . $dis . ">";
	$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
		$output .= "\t<OPTION VALUE='" . $row_sig['code'] . "'>" . $row_sig['code'] . "|" . $row_sig['text'] . "</OPTION>\n";
		}
	$output .= "</SELECT>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR VALIGN = 'TOP' CLASS='odd'>";
	$output .= "<TD CLASS='td_label text'>Add to Assign: </TD>";
	$output .= "<TD CLASS='td_data text'>";
	$output .= "<SELECT NAME='assigns'>";
	$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $row['ticket_id'] . " AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_ass = stripslashes_deep(mysql_fetch_assoc($result))) {
		$sel = ($row_ass['id'] == $assigned_to) ? "SELECTED" : "";
		$output .= "\t<OPTION VALUE='" . $row_ass['id'] . "' " . $sel . ">" . $responder_details[$row_ass['responder_id']] . "&nbsp;|&nbsp;" . $row_ass['as_of'] . "</OPTION>\n";
		}
	$output .= "</SELECT>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR CLASS='odd'>";
	$output .= "<TD CLASS='td_label text'>As of:</TD>";
	$output .= "<TD CLASS='td_data text'>" . return_date_dropdown("asof",$row['date'], FALSE);
	$output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
	$output .= "<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientEd);' />";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR CLASS='even'>";
	$output .= "<TD COLSPAN=99 style='text-align: center;'><CENTER>";	
	$output .= "<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_lock(document.patientEd); document.patientEd.reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
	$output .= "<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='sendajax(document.patientEd);'><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = " . $row['gender'] . " />";
	$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = " . $row['insurance_id'] . " />";
	$output .= "<INPUT TYPE = 'hidden' NAME = 'id' VALUE = " . $_GET['id'] . " />";	
	$output .= "</FORM>";
	$output .= "</TABLE>";
	$output .= "</DIV>";
	} else {				// $get_action - NOTA - default
	$user_level = is_super() ? 9999 : $_SESSION['user_id']; 		
	$regions_inuse = get_regions_inuse($user_level);	//	5/4/11
	$group = get_regions_inuse_numbers($user_level);	//	5/4/11		

	$al_groups = $_SESSION['user_groups'];
		
	if(array_key_exists('viewed_groups', $_SESSION)) {	//	5/4/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		} else {
		$curr_viewed = $al_groups;
		}

	$curr_names="";	//	5/4/11
	$z=0;	//	5/4/11
	foreach($curr_viewed as $grp_id) {	//	5/4/11
		$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
		$curr_names .= get_groupname($grp_id);
		$curr_names .= $counter;
		$z++;
		}	

	$regs_string = "<FONT SIZE='-1'>Showing " . get_text("Regions") . ":&nbsp;&nbsp;" . $curr_names . "</FONT>";	//	5/4/11	
	
	if(!isset($curr_viewed)) {	
		if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11					
			}
		} else {
		if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {				
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {	//	6/10/11
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11						
			}
		}
	
	$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )		
		$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";		
	$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$pulldown = '<option value = 0 selected>Select</option>\n'; 	// 3/18/10
		while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
			$pulldown .= "<option value=\"{$row_fc['id']}\">" . shorten($row_fc['name'], 20) . "</option>\n";
			}
//	list existing patients
	
	$query 	= "SELECT *, `p`.`id` AS `pat_id`     		
	FROM  `$GLOBALS[mysql_prefix]patient` `p`
	LEFT JOIN `$GLOBALS[mysql_prefix]insurance` `i` 
	ON (`p`.`insurance_id` = `i`.`id`)
	WHERE `ticket_id` = {$_GET['ticket_id']}
	ORDER BY `name` ASC, `fullname` ASC";

	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			
	if (mysql_num_rows($result) != 0) {
		$i = 0;
		$output .= "<SPAN CLASS = 'text' style='text-align: center;'>" . $patient . " records - click line to edit</SPAN>";
		$output .= "<DIV style='height: 100px; overflow-y: scroll;'>";
		$output .= "<TABLE style='width: 100%;'>";
		while($row =stripslashes_deep( mysql_fetch_array($result))){
			$output .= "<TR CLASS='" . $evenodd[($i+1)%2] . "' VALIGN='baseline' onClick = 'patientedit(" . $_GET['ticket_id'] . ", " . $row['pat_id'] . ");'>";
			$output .= "<TD CLASS='td_data text'>" . $row['name'] . "</TD>";
			$output .= "<TD CLASS='td_data text'>" . shorten($row['fullname'], 24) . "</TD>";
			$output .= "<TD CLASS='td_data text'>" . $row['ins_value'] . "</TD>";
			$output .= "<TD CLASS='td_data text'>" . shorten($row['description'], 24) . "</TD>";
			$output .= "</TR>";
			$i++;
			}

		$output .= "<TR CLASS='even'>";
		$output .= "<TD COLSPAN=2>&nbsp;</TD>";
		$output .= "</TR>";
		$output .= "</TABLE>";
		$output .= "</DIV>";
		}
	$heading = "Add " . $patient . " Record";
	$output .= "<DIV STYLE='padding: 10px; width: 95%;'>";
	$output .= "<FORM ID='patientAdd' NAME='patientAdd' METHOD='post' ACTION='./ajax/form_post.php?ticket_id=" . $_GET['ticket_id'] . "&q=". $sess_id . "&function=addpatient'>";	
	$output .= "<TABLE BORDER='0' STYLE='margin-left: 20px; position: relative; top: 70px;'>";
	$output .= "<TR CLASS='even'>";
	$output .= "<TD CLASS='td_label text text'>" . get_text('Patient ID') . ": <font color='red' size='-1'>*</font></TD>";
	$output .= "<TD CLASS='td_data text'><INPUT TYPE='text' NAME='frm_name' value='' size='32'></TD>";
	$output .= "</TR>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
	$result = mysql_query($query);
	if(@mysql_num_rows($result) > 0) {
		$ins_sel_str = "<SELECT name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
		$ins_sel_str .= "\t\t\t<OPTION VALUE=0 SELECTED >Select</OPTION>\n";		// 7/27/11		
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$ins_sel_str .= "\t\t\t<OPTION VALUE={$row['id']}>{$row['ins_value']}</OPTION>\n";		
			}		// end while()
		$ins_sel_str .= "</SELECT>";
		$output .= "<TR CLASS='odd' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>" . $fullname . ": &nbsp;&nbsp;</TD>";
		$output .= "<TD CLASS='td_data text'><INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='' SIZE = '64' /></TD>";
		$output .= "</TR>";
		$output .= "<TR CLASS='even' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>" . $dateofbirth . ": &nbsp;&nbsp;</TD>";
		$output .= "<TD CLASS='td_data text'><INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='' SIZE = '24' /></TD>";
		$output .= "</TR>";
		$output .= "<TR CLASS='odd' VALIGN='bottom'>";
		$output .= "<TD CLASS='td_label text'>" . $gender . ": ";
		if(get_variable('locale') != 1) {
			$output .= "<font color='red' size='-1'>*</font>";
			}
		$output .= "</B>&nbsp;&nbsp;";
		$output .= "</TD>";
		$output .= "<TD CLASS='td_data text'>";		
		$output .= "&nbsp;&nbsp;";
		$output .= "M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' />";
		$output .= "&nbsp;&nbsp;F&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' />";
		$output .= "&nbsp;&nbsp;T&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' />";
		$output .= "&nbsp;&nbsp;U&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' />";
		$output .= "</TD>";
		$output .= "</TR>";
		if(get_variable('locale') != 1) {
			$output .= "<TR CLASS='even' VALIGN='bottom'>";
			$output .= "<TD CLASS='td_label text'>" . $insurance . ": <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>";
			$output .= "<TD CLASS='td_data text'>" . $ins_sel_str . "</TD>";
			$output .= "</TR>";
			}
		$output .= "<TR CLASS='odd'>";
		$output .= "<TD CLASS='td_label text'>Facility:</TD>";
		$output .= "<TD COLSPAN='2' class='td_data text'>";
		$output .= "<SELECT NAME='frm_facility_id'  tabindex=11 onChange='this.options[selectedIndex].value.trim();'>" . $pulldown . "</SELECT>";
		$output .= "</TD>";
		$output .= "</TR>";
		$output .= "<TR CLASS='even'>";
		$output .= "<TD CLASS='td_label text'>" . $facilitycontact . ":&nbsp;&nbsp;</TD>";
		$output .= "<TD class='td_data text'>";
		$output .= "<INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='' SIZE = '64' />";
		$output .= "</TD>";
		$output .= "</TR>";
		}		// end 	if($num_rows>0) 
	$output .= "<TR CLASS='even' >";
	$output .= "<TD CLASS='td_label text'>Description:</TD>";
	$output .= "<TD CLASS='td_data text'>";
	$output .= "<TEXTAREA ROWS='6' COLS='62' NAME='frm_description' WRAP='virtual'></TEXTAREA>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR VALIGN = 'TOP' CLASS='even'>";
	$output .= "<TD ALIGN='right' CLASS=td_label text></TD>";
	$output .= "<TD CLASS='td_data text'>";
	$output .= "<SPAN CLASS='td_label text'>Signal: </SPAN>";
	$output .= "<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
	$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
		$output .= "\t<OPTION VALUE='" . $row_sig['code'] . "'>" . $row_sig['code'] . "|" . $row_sig['text'] . "</OPTION>\n";
		}
	$output .= "</SELECT>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR CLASS='odd' VALIGN='bottom'>";
	$output .= "<TD CLASS='td_label text text'>As of: &nbsp;&nbsp;</TD>";
	$output .= "<TD CLASS='td_data text'>" . return_date_dropdown('asof',0,FALSE) . "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientAdd);'></TD>";
	$output .= "</TR>";
	$output .= "<TR CLASS='even'>";
	$output .= "<TD COLSPAN=2>&nbsp;</TD>";
	$output .= "</TR>";
	$output .= "<TR CLASS='even'>";
	$output .= "<TD COLSPAN=99 style='text-align: center;'><CENTER>";
	$output .= "<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_asof(document.patientAdd, false); reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0 /></SPAN>";
	$output .= "<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='sendajax(document.patientAdd);'><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0 /></SPAN>";
	$output .= "</CENTER></TD>";
	$output .= "</TR>";
	$output .= "</TABLE>";
	$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = 0 />";
	$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = 0 />";
	$output .= "</FORM>";
	$output .= "</DIV>";
	}
$output .= "<FORM NAME='next_Form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
$output .= "<INPUT TYPE='hidden' NAME='action' VALUE='list' />";
$output .= "<INPUT TYPE='hidden' NAME='ticket_id' VALUE='" . $_GET['ticket_id'] . "' />";
$output .= "</FORM>";
$output .= "<FORM NAME='can_Form' ACTION='main.php'>";
$output .= "<INPUT TYPE='hidden' NAME = 'id' VALUE = '" . $_GET['ticket_id'] . "'>";
$output .= "</FORM>";
//print $output . "<BR />";
$ret_arr[0] = $output;
$ret_arr[1] = $heading;
print json_encode($ret_arr);