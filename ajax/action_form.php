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
$get_action = (empty($_GET['action']))? "form" : $_GET['action'];
$tick_id = (isset($_REQUEST['ticket_id'])) ? $_REQUEST['ticket_id'] : "";

$output = "";
$do_yr_asof = false;

$optstyles = array ();

$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$optstyles[$row['name']] = $row['name'];	
	}
unset($result);
if ($get_action == 'edit') {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_array($result));
	$responders = explode(" ", $row['responder']);
	$do_yr_asof = true;
	$heading = "Edit Action";
	$output .= "<FORM METHOD='post' NAME='ed_frm' ACTION./ajax/form_post.php?id=" . $_GET['id'] . "&ticket_id=" . $tick_id . "&q=". $sess_id . "&function=editaction'>";
	$output .= "<TABLE BORDER='0'>";
	$output .= "<TR CLASS='header'>";
	$output .= "<TD COLSPAN='99' ALIGN='center'>";
	$output .= "<FONT CLASS='header' STYLE='background-color: inherit;'>" . $heading . "</FONT>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR CLASS='even' VALIGN='top'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD>";
	$output .= "<TD colspan=3><TEXTAREA ROWS='4' COLS='60' NAME='frm_description' WRAP='virtual'>" . $row['description'] . "</TEXTAREA>";
	$output .= "</TD></TR>";
	$output .= "<TR CLASS='even'><TD COLSPAN=99>&nbsp;</TD></TR>";
	$output .= "<TR CLASS='odd' VALIGN='top'>";				
	$query = "SELECT *, 
		`updated` AS `updated`,
		`y`.`id` AS `type_id`,
		`r`.`id` AS `unit_id`,
		`r`.`name` AS `unit_name`,
		`s`.`description` AS `stat_descr`,
		`r`.`description` AS `unit_descr`, 
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
			AS `nr_assigned` 
		FROM `$GLOBALS[mysql_prefix]responder` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` ON ( `r`.`type` = y.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
		ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC";											// 2/1/10, 3/15/10
	$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	$max = 24;
	$height =  (mysql_affected_rows()>$max) ? ($max * 30 ) : (mysql_affected_rows() + 1) * 30;
	$output .= "<TR VALIGN='top'><TD COLSPAN=3 CLASS='td_label' style='text-align: center;'>" . get_units_legend(). "</TD></TR>";
	$checked = (in_array("0", $responders))? "CHECKED" : "";	// NA is special case - 8/8/10
	$output .= "<TD rowspan=3 CLASS='td_label_wrap' STYLE='vertical-align: top;'>Action for " . get_text('Responders') . "(s)</TD>";
	$output .= "<TD>";
	$output .= "<DIV  style='width:auto;height:" . $height . "PX; overflow-y: auto; overflow-x: auto;'>";
	$output .= "<INPUT TYPE = 'checkbox' VALUE=0 NAME = 'frm_cb_0' />NA<BR />";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 7/20/10
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		// 

		$checked = (in_array($row['unit_id'], $responders))? "CHECKED" : "";
		$ct_str = ($row['nr_assigned']==0) ? ""  : "&nbsp;({$row['nr_assigned']})" ;
		$the_name = "frm_cb_" . stripslashes ($row['unit_name']);
		$output .= "\t<INPUT TYPE = 'checkbox' VALUE='" . $row['unit_id'] . "' NAME = '" . $the_name . "' " . $checked . " />";
		$output .= "<SPAN STYLE='width:300px; display:inline; background-color:" . $the_bg_color . "; color:" . $the_text_color . ";'>" . stripslashes($row['unit_name']) . "&nbsp;</SPAN>" . $ct_str;
		$output .= "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;<SPAN STYLE = 'width:200px; background-color:" . $row['bg_color'] . "; color:" . $row['text_color'] . ";'>" . $row['stat_descr'] . "</SPAN><BR />\n";
		}
	unset ($row);
	$output .= "</DIV>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR>";
	$output .= "<TD COLSPAN=99 CLASS='td_label text'><SPAN>As of: &nbsp;&nbsp;<SPAN>";
	$output .= "<INPUT SIZE=4 NAME='frm_year_asof' VALUE='' MAXLENGTH=4>";
	$output .= "<INPUT SIZE=2 NAME='frm_month_asof' VALUE='' MAXLENGTH=2>";
	$output .= "<INPUT SIZE=2 NAME='frm_day_asof' VALUE='' MAXLENGTH=2>";
	$output .= "<INPUT SIZE=2 NAME='frm_hour_asof' VALUE='' MAXLENGTH=2>:";
	$output .= "<INPUT SIZE=2 NAME='frm_minute_asof' VALUE='' MAXLENGTH=2>";
	$output .= "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.ed_frm);'>";
	$output .= "</TD>";
	$output .= "</TR>";
	$output .= "<TR>";
	$output .= "<TD COLSPAN=99 CLASS='td_label text'>";	
	$output .= "<SPAN ID='can_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='history.back();'><SPAN STYLE='float: left;'>" . get_text('Cancel') . "</SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>";
	$output .= "<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.ed_frm.reset(); init();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "/SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
	$output .= "<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='return validate(document.ed_frm);'><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
	$output .= "</TD></TR></TABLE></FORM><BR />";

	} else if ($get_action == 'form') {		// end if ($get_action == 'edit')
	$do_yr_asof = true;
	$user_level = is_super() ? 9999 : $_SESSION['user_id']; 		
	$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
	$group = get_regions_inuse_numbers($user_level);	//	6/10/11		

	$al_groups = $_SESSION['user_groups'];
		
	if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		} else {
		$curr_viewed = $al_groups;
		}

	$curr_names="";	//	6/10/11
	$z=0;	//	6/10/11
	foreach($curr_viewed as $grp_id) {	//	6/10/11
		$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
		$curr_names .= get_groupname($grp_id);
		$curr_names .= $counter;
		$z++;
		}			

	$heading = "Add Action";
	$output .= "<DIV STYLE='position: relative; top: 10px; left: 10px; padding: 10px; width: 95%;'>";
	$output .= "<FORM ID='act_form' NAME='act_form' METHOD='post' ACTION='./ajax/form_post.php?q=". $sess_id . "&function=addaction'>";	
	$output .= "<LABEL for='frm_description'>Description: <font color='red' size='-1'>*</font></LABEL><BR />";
	$output .= "<TEXTAREA id='frm_description' NAME='frm_description' STYLE='width: 95%; height: 10%;'></TEXTAREA><BR /><BR />";
	$output .= "<LABEL FOR='signals'>Signal &raquo;</LABEL><BR />";
	$output .= "<SELECT ID='signals' NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
	$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
		$output .= "\t<OPTION VALUE='" . $row_sig['code'] . "'>" . $row_sig['code'] . "|" . $row_sig['text'] ."</OPTION>\n";
		}
	$output .= "</SELECT><BR /><BR />";
	if(!isset($curr_viewed)) {	
		if(empty($al_groups)) {
			$where = "WHERE `a`.`type` = 2";
			} else {
			$x=0;	//	6/10/11
			$where = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where2 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where .= "`a`.`group` = '" . $grp ."'";
				$where .= $where2;
				$x++;
				}
			$where .= "AND `a`.`type` = 2";		
			}
		} else {
		if(empty($curr_viewed)) {
			$where = "WHERE `a`.`type` = 2";
			} else {				
			$x=0;	//	6/10/11
			$where = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {	//	6/10/11
				$where2 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where .= "`a`.`group` = '" . $grp . "'";
				$where .= $where2;
				$x++;
				}
			$where .= "AND `a`.`type` = 2";				
			}
		}	
	$query = "SELECT *, 
		`updated` AS `updated`,
		`t`.`id` AS `type_id`, 
		`r`.`id` AS `unit_id`, 
		`r`.`name` AS `unit_name`,
		`s`.`description` AS `stat_descr`,  
		`r`.`description` AS `unit_descr`, 
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `unit_id`  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
			AS `nr_assigned` 
		FROM `$GLOBALS[mysql_prefix]responder` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )					
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
		$where GROUP BY unit_id ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC";
	$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
	$max = 24;

	$output .= "<SPAN style='display: block; width: 100%; text-align: center;'>" . get_units_legend(). "</SPAN><BR />";
	$output .= "<LABEL>Select Unit</LABEL><BR />";
	$output .= "<DIV style='width: auto; height: 35%; display: inline-block; overflow-y: auto; overflow-x: hidden;'>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_bg_color = $row['bg_color'];
		$type_text_color = $row['text_color'];
		$ct_str = ($row['nr_assigned']==0) ? ""  : "&nbsp;({$row['nr_assigned']})" ;
		$the_name = "frm_cb_" . stripslashes($row['unit_name']);
		$output .= "<SPAN class='row' STYLE='background-color: " . $type_bg_color . "; color: " . $type_text_color . "; width: 98%;'>";
		$output .= "<SPAN class='cell text'><INPUT TYPE = 'checkbox' VALUE='" . $row['unit_id'] . "' NAME = '" . $the_name . "' /></SPAN>";
		$output .= "<SPAN class='cell text'>" . stripslashes($row['unit_name']) . "</SPAN>";
		$output .= "<SPAN class='cell text'>" . $ct_str . "</SPAN>";
		$output .= "<SPAN class='cell text'> - " . $row['stat_descr'] . " - </SPAN>";
		$output .= "</SPAN>\n";
		}
	$output .= "</DIV><BR /><BR />";
	$output .= "<LABEL FOR='frm_year_asof'>As Of: &nbsp;&nbsp;</LABEL><BR />";
	$output .= return_date_dropdown('asof',0,FALSE);
	$output .= "<INPUT TYPE='hidden' NAME = 'frm_ticket_id' VALUE = '" . $tick_id . "' />";
	$output .= "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.act_form);'>";
	$output .= "&nbsp;&nbsp;";
	$output .= "<BR /><BR />";
	$output .= "<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.act_form.reset(); init();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
	$output .= "<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='sendajax(document.act_form);'><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
	$output .= "</FORM>";
	}				// end if ($get_action == 'form')

$output .= "<FORM NAME='can_Form' ACTION='main.php'>";
$output .= "<INPUT TYPE='hidden' NAME = 'id' VALUE = '" . $tick_id . "'>";
$output .= "</FORM></DIV>";

$ret_arr[] = $output;
print json_encode($ret_arr);
//print $output;