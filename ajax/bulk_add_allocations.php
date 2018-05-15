<?php
require_once('../incs/functions.inc.php');

function get_membername($id) {
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = '" . $id . "'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	$ret = $row['field2'] . " " . $row['field1'];
	return $ret;
	}	

function add_allocation($id, $type, $skill_id, $completed, $refresh, $freq) {
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = '" . $id . "' AND `skill_type` = '" . $type . "' AND `skill_id` = '" . $skill_id . "'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_num_rows($result) == 0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocations` (`member_id`, `skill_type`, `skill_id`, `completed`, `refresh_due`, `frequency`, `_on` )
			VALUES (" .
				quote_smart(trim($id)) . "," .
				quote_smart(trim($type)) . "," .
				quote_smart(trim($skill_id)) . "," .	
				quote_smart(trim($completed)) . "," .				
				quote_smart(trim($refresh)) . "," .
				quote_smart(trim($freq)) . "," .
				quote_smart(trim($now)) . ");";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		if($result){
			return true;
			} else {
			return false;
			}
		} else {
		return false;
		}
	}
	
function check_allocation($id, $type, $skill_id) {
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = '" . $id . "' AND `skill_type` = '" . $type . "' AND `skill_id` = '" . $skill_id . "'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_num_rows($result) != 0) {
		$ret = true;
		} else {
		$ret = false;
		}
	return $ret;
	}
	
function check_training_allocation($id, $type, $skill_id) {
	$ret = array();
	$query	= "SELECT *, `completed` AS `completed`, `refresh_due` AS `refresh_due` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = '" . $id . "' AND `skill_type` = '" . $type . "' AND `skill_id` = '" . $skill_id . "'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = mysql_fetch_array($result,MYSQL_ASSOC);	
		$ret[0] = strtotime($row['completed']);
		$ret[1] = strtotime($row['refresh_due']);
		} else {
		$ret = false;
		}
	return $ret;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets Personnel Database - Member Details</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<meta http-equiv=”X-UA-Compatible” content=”IE=EmulateIE7" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<STYLE type="text/css">
.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
			  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
.hover_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
			  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }				  
.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
			  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #EFEFEF;font-weight: bolder; cursor: pointer; }	
.plain_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
			  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder;}					  
.hover_lo 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
			  padding: 1px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
.plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;}
.data 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;
			  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: yellow;font-weight: bolder;}		
.message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
#outer { border-radius: 20px 20px;}
#leftcol { border-radius: 20px 20px;}	
#map { border: 2px outset #707070; border-radius: 20px 20px;}	
.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
</STYLE>
<LINK REL=StyleSheet HREF="../default.css?version=<?php print time();?>" TYPE="text/css">
<script src="../js/misc_function.js" type="text/javascript"></script>	
<SCRIPT>
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}
	
function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}
	
function do_hover_centered (the_id) {
	CngClass(the_id, 'hover_centered');
	return true;
	}
	
function do_lo_hover (the_id) {
	CngClass(the_id, 'lo_hover');
	return true;
	}
	
function do_plain (the_id) {				// 8/21/10
	CngClass(the_id, 'plain');
	return true;
	}
	
function do_plain_centered (the_id) {				// 8/21/10
	CngClass(the_id, 'plain_centered');
	return true;
	}
	
function do_lo_plain (the_id) {
	CngClass(the_id, 'lo_plain');
	return true;
	}		

function $() {															// 12/20/08
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
		}
	return elements;
	}
	
function validate_skills(theForm) {						// Responder form contents validation	8/11/09
	var errmsg="";
	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		theForm.submit();
		}
	}				// end function va lidate(theForm)			
</SCRIPT>
</HEAD>
<BODY>
	<DIV id='topbar' style='position: fixed; top: 0px; right: 0px; font-size: 14px; z-index: 999; width: 100%; background: #DEDEDE; border: 2px outset #CECECE;'>
		<DIV class='tablehead' style='width: 100%; float: left; z-index: 999'><b>Bulk add Capabilities, Training etc</b></DIV>			
		<DIV id='buttonbar' style='float: right;'>
			<SPAN class = 'plain' style='border: 0px; background: #DEDEDE;'>FORM CONTROLS&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
			<SPAN ID = 'can_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle;' src="../img/back.png"/></SPAN>
			<SPAN ID = 'sub_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="validate_skills(document.bulk_edit_Form);"><?php print get_text('Save');?> <IMG style='vertical-align: middle;' src="../img/save.png"/></SPAN>			
		</DIV>
	</DIV>
	<BR />
	<BR />
<?php

if((isset($_POST)) && (!empty($_POST))) {
	if(isset($_POST['frm_tra'])) {	
		foreach($_POST['frm_tra'] AS $key => $member) {
			foreach($member AS $key2 => $train){
				$frm_completed = $_POST['frm_year_frm_tra_comp'][$key][$key2] . "-" . $_POST['frm_month_frm_tra_comp'][$key][$key2] . "-" . $_POST['frm_day_frm_tra_comp'][$key][$key2];
				$frm_refresh =  $_POST['frm_year_frm_tra_refresh'][$key][$key2] . "-" . $_POST['frm_month_frm_tra_refresh'][$key][$key2] . "-" . $_POST['frm_day_frm_tra_refresh'][$key][$key2];	
				add_allocation($key, 1, $key2, $frm_completed, $frm_refresh, NULL);
				}
			}
		}		
	if(isset($_POST['frm_cap'])) {
		foreach($_POST['frm_cap'] AS $key => $member) {
			foreach($member AS $key2 => $capab){
				add_allocation($key, 2, $key2 , NULL, NULL, NULL);

				}
			}	
		}
	if(isset($_POST['frm_equ'])) {		
		foreach($_POST['frm_equ'] AS $key => $member) {
			foreach($member AS $key2 => $equip){
				add_allocation($key, 3, $key2 , NULL, NULL, NULL);			
				}
			}
		}
	if(isset($_POST['frm_veh'])) {
		foreach($_POST['frm_veh'] AS $key => $member) {
			foreach($member AS $key2 => $vehic){
				add_allocation($key, 4, $key2 , NULL, NULL, "Permanent");					
				}
			}		
		}		
	if(isset($_POST['frm_clo'])) {		
		foreach($_POST['frm_clo'] AS $key => $member) {
			foreach($member AS $key2 => $cloth){
				add_allocation($key, 5, $key2 , NULL, NULL, NULL);			
				}
			}
		}
	unset($_POST);
?>
	<DIV ID='finished' style='width: 100%; text-align: center; z-index: 998; position: relative; top: 100px;'>All skills allocations added</DIV>
<?php
	} else {
		
	$members = array();
	$capabilities = array();
	$equipment = array();
	$clothing = array();
	$training = array();
	$vehicles = array();
	$member_list = "";

	$query_mem	= "SELECT * FROM `$GLOBALS[mysql_prefix]member`";
	$result_mem	= mysql_query($query_mem) or do_error($query_mem, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result_mem,MYSQL_ASSOC)) {
		$members[$row['id']] = $row['field2'] . " " . $row['field1'];
		}

	$query_cap	= "SELECT * FROM `$GLOBALS[mysql_prefix]capability_types`";
	$result_cap	= mysql_query($query_cap) or do_error($query_cap, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result_cap,MYSQL_ASSOC)) {
		$capabilities[$row['id']] = $row['name'];
		}

	$query_tra	= "SELECT * FROM `$GLOBALS[mysql_prefix]training_packages`";
	$result_tra	= mysql_query($query_tra) or do_error($query_tra, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result_tra,MYSQL_ASSOC)) {
		$training[$row['id']] = $row['package_name'];
		}
		
	$query_equ	= "SELECT * FROM `$GLOBALS[mysql_prefix]equipment_types`";
	$result_equ	= mysql_query($query_equ) or do_error($query_equ, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result_equ,MYSQL_ASSOC)) {
		$equipment[$row['id']] = $row['equipment_name'];
		}
		
	$query_clo	= "SELECT * FROM `$GLOBALS[mysql_prefix]clothing_types`";
	$result_clo	= mysql_query($query_clo) or do_error($query_clo, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result_clo,MYSQL_ASSOC)) {
		$clothing[$row['id']] = $row['clothing_item'] . "," . $row['size'];;
		}

	$query_veh	= "SELECT * FROM `$GLOBALS[mysql_prefix]vehicles`";
	$result_veh	= mysql_query($query_veh) or do_error($query_veh, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	while($row = mysql_fetch_array($result_veh,MYSQL_ASSOC)) {
		$vehicles[$row['id']] = $row['regno'];
		}
?>
		<DIV ID='outer' style='width: 100%; text-align: center; z-index: 998; position: relative; top: 50px;'>
			<DIV id='leftcol' style='position: absolute; left: 20px; top: 20px; width: 80%; text-align: center; border: 2px outset #FFFFFF; padding: 20px; z-index: 2; background: #FEF7D6;'>
				<FORM METHOD="POST" NAME= "bulk_edit_Form" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>">
					<FIELDSET>
					<LEGEND><?php print get_text('Members');?></LEGEND>
					<TABLE width='100%' border='1'>
<?php						
					foreach($members AS $key => $val) {
						print "<TR style='vertical-align: text-top;'><TD style='background-color: blue; color: #FFFFFF;'>";
						print "<B>" . $val . "</B>";
						print "</TD><TD><B>Training</B><BR />";
						print "<TABLE><TR>";
						print "<TD ALIGN='left' style='font-weight: bold; font-style: italic;'>Training Pkg</TD><TD ALIGN='left' style='font-weight: bold; font-style: italic;'>Completed</TD><TD ALIGN='left' style='font-weight: bold; font-style: italic;'>Refresh Due</TD></TR>";
						foreach ($training AS $key1 => $val1) {
							$sel = (check_training_allocation($key, 1, $key1)) ? "CHECKED" : "";							
							print "<TR>";
							print "<TD width='33%' ALIGN='left'><INPUT type='checkbox' name='frm_tra[" . $key . "][" . $key1 . "]' value='" . $val1 . "' " . $sel . ">" . shorten($val1, 20) . "</TD>";
							if(check_training_allocation($key, 1, $key1)) {
								$thedates = check_training_allocation($key, 1, $key1);
								$fieldname = "frm_tra_comp[" . $key . "][" . $key1 . "]";
								$completed = $thedates[0];
								print "<TD width='33%' ALIGN='left'>";
								print generate_date_dropdown($fieldname,$completed,0,0);
								print "</TD>";
								$fieldname2 = "frm_tra_refresh[" . $key . "][" . $key1 . "]";		
								$refresh =  $thedates[1];									
								print "<TD width='33%' ALIGN='left'>";
								print generate_date_dropdown($fieldname2,$refresh,0,0);
								print "</TD>";	
								print "</TR>";
								} else {
								$fieldname = "frm_tra_comp[" . $key . "][" . $key1 . "]";
								print "<TD width='33%' ALIGN='left'>";
								print generate_date_dropdown($fieldname,0,0,0);
								print "</TD>";
								$fieldname2 = "frm_tra_refresh[" . $key . "][" . $key1 . "]";		
								print "<TD width='33%' ALIGN='left'>";
								print generate_date_dropdown($fieldname2,0,0,0);
								print "</TD>";	
								print "</TR>";									
								}
							}
						print "</TABLE>";
						print "</TD><TD ALIGN='left'><B>Capabilities</B><BR />";
						foreach ($capabilities AS $key2 => $val2) {
							$sel = (check_allocation($key, 2, $key2)) ? "CHECKED" : "";
							print "<INPUT type='checkbox' name='frm_cap[" . $key . "][" . $key2 . "]' value='" . $val2 . "' " . $sel . ">" .$val2 . "<BR />";
							}									
						print "</TD><TD ALIGN='left'><B>Equipment</B><BR />";
						foreach ($equipment AS $key3 => $val3) {
							$sel = (check_allocation($key, 3, $key3)) ? "CHECKED" : "";							
							print "<INPUT type='checkbox' name='frm_equ[" . $key . "][" . $key3 . "]' value='" . $val3 . "' " . $sel . ">" .$val3 . "<BR />";
							}	
						print "</TD><TD ALIGN='left'><B>Clothing</B><BR />";
						foreach ($clothing AS $key4 => $val4) {
							$sel = (check_allocation($key, 5, $key4)) ? "CHECKED" : "";							
							print "<INPUT type='checkbox' name='frm_clo[" . $key . "][" . $key4 . "]' value='" . $val4 . "' " . $sel . ">" .$val4 . "<BR />";
							}	
						print "</TD ALIGN='left'><TD><B>Vehicles</B><BR />";
						foreach ($vehicles AS $key5 => $val5) {
							$sel = (check_allocation($key, 4, $key5)) ? "CHECKED" : "";							
							print "<INPUT type='checkbox' name='frm_veh[" . $key . "][" . $key5 . "]' value='" . $val5 . "' " . $sel . ">" .$val5 . "<BR />";
							}		
						print "</TD>";
						print "</TR>";
						}
						print "</TABLE>";
?>
					</FIELDSET>
				</FORM>						
			</DIV>
		</DIV>	
		<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>
<?php
	}
?>
	</BODY>
</HTML>	