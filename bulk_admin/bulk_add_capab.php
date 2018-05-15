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
	$query	= "SELECT *, UNIX_TIMESTAMP(completed) AS `completed`, UNIX_TIMESTAMP(refresh_due) AS `refresh_due` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = '" . $id . "' AND `skill_type` = '" . $type . "' AND `skill_id` = '" . $skill_id . "'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = mysql_fetch_array($result,MYSQL_ASSOC);	
		$ret[0] = $row['completed'];
		$ret[1] = $row['refresh_due'];
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
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="../js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="../js/Control.Geocoder.css" />
<link rel="stylesheet" href="../js/leaflet-openweathermap.css" />
<STYLE type="text/css">
TD {vertical-align: top;}
TR {vertical-align: top;}
</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="../js/jss.js"></SCRIPT>
<script type="text/javascript"src="../js/misc_function.js"></script>	
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
	<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='text_biggest text_white' style='text-align: center;'>Bulk add Capabilities, Training etc</SPAN>
			<SPAN id='can_but' class='plain text' style='float: right; vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.forms['can_Form'].submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='../images/close_door_small.png' BORDER=0></SPAN>
			<SPAN ID='sub_but' class='plain text' style='float: right; vertical-align: middle; display: inline-block; width: 100px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="validate_skills(document.bulk_edit_Form);"><SPAN STYLE='float: left;'><?php print get_text('Save');?></SPAN><IMG style='vertical-align: middle;' src="../images/save.png" BORDER=0 /></SPAN>			
		</DIV>
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
			<DIV ID='left_col' style='position: relative; left: 5%; top: 60px; width: 90%; padding: 5px; text-align: center;'>
				<B><i>Capabilities updated</i></B><BR /><BR /><BR /><BR />
				<SPAN ID='fin_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.can_Form.submit();"><SPAN STYLE='float: left;'>Finished - To Config</SPAN><IMG STYLE='float: right;' SRC='../images/config_small.png' BORDER=0></SPAN>
				<SPAN ID='cont_but' class='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.cont_Form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Continue");?></SPAN><IMG STYLE='float: right;' SRC='../images/submit_small.png' BORDER=0></SPAN>
			</DIV>
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
			<DIV ID='left_col' style='position: relative; left: 5%; top: 60px; width: 90%; padding: 5px;'>	
				<FORM METHOD="POST" NAME= "bulk_edit_Form" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>">
					<FIELDSET>
					<LEGEND><?php print get_text('Members');?></LEGEND>
					<TABLE width='100%' border='1'>
<?php						
					foreach($members AS $key => $val) {
						print "<TR style='vertical-align: text-top;'><TD style='width: 15%; vertical-align: text-top; background-color: blue; color: #FFFFFF;'>";
						print "<B>" . $val . "</B>";
						print "</TD><TD style='width: 35%; vertical-align: text-top;'><B>Training</B><BR />";
						print "<TABLE><TR style='vertical-align: text-top;'>";
						print "<TD ALIGN='left' style='vertical-align: text-top; font-weight: bold; font-style: italic;'>Training Pkg</TD><TD ALIGN='left' style='font-weight: bold; font-style: italic;'>Completed</TD><TD ALIGN='left' style='font-weight: bold; font-style: italic;'>Refresh Due</TD></TR>";
						foreach ($training AS $key1 => $val1) {
							$sel = (check_training_allocation($key, 1, $key1)) ? "CHECKED" : "";							
							print "<TR style='vertical-align: text-top;'>";
							print "<TD><INPUT class='text' type='checkbox' name='frm_tra[" . $key . "][" . $key1 . "]' value='" . $val1 . "' " . $sel . ">" . shorten($val1, 20) . "</TD>";
							if(check_training_allocation($key, 1, $key1)) {
								$thedates = check_training_allocation($key, 1, $key1);
								$fieldname = "frm_tra_comp[" . $key . "][" . $key1 . "]";
								$completed = $thedates[0];
								print "<TD class='text_small' ALIGN='left'>";
								print generate_dateonly_dropdown($fieldname,$completed,0,0);
								print "</TD>";
								$fieldname2 = "frm_tra_refresh[" . $key . "][" . $key1 . "]";		
								$refresh =  $thedates[1];									
								print "<TD class='text_small' ALIGN='left'>";
								print generate_dateonly_dropdown($fieldname2,$refresh,0,0);
								print "</TD>";	
								print "</TR>";
								} else {
								$fieldname = "frm_tra_comp[" . $key . "][" . $key1 . "]";
								print "<TD class='text_small' ALIGN='left'>";
								print generate_dateonly_dropdown($fieldname,0,0,0);
								print "</TD>";
								$fieldname2 = "frm_tra_refresh[" . $key . "][" . $key1 . "]";		
								print "<TD class='text_small' ALIGN='left'>";
								print generate_dateonly_dropdown($fieldname2,0,0,0);
								print "</TD>";	
								print "</TR>";									
								}
							}
						print "</TABLE>";
						print "</TD><TD style='width: 12%; vertical-align: text-top;'><B>Capabilities</B><BR />";
						foreach ($capabilities AS $key2 => $val2) {
							$sel = (check_allocation($key, 2, $key2)) ? "CHECKED" : "";
							print "<INPUT type='checkbox' name='frm_cap[" . $key . "][" . $key2 . "]' value='" . $val2 . "' " . $sel . ">" .$val2 . "<BR />";
							}									
						print "</TD><TD style='width: 12%; vertical-align: text-top;'><B>Equipment</B><BR />";
						foreach ($equipment AS $key3 => $val3) {
							$sel = (check_allocation($key, 3, $key3)) ? "CHECKED" : "";							
							print "<INPUT type='checkbox' name='frm_equ[" . $key . "][" . $key3 . "]' value='" . $val3 . "' " . $sel . ">" .$val3 . "<BR />";
							}	
						print "</TD><TD style='width: 12%; vertical-align: text-top;'><B>Clothing</B><BR />";
						foreach ($clothing AS $key4 => $val4) {
							$sel = (check_allocation($key, 5, $key4)) ? "CHECKED" : "";							
							print "<INPUT type='checkbox' name='frm_clo[" . $key . "][" . $key4 . "]' value='" . $val4 . "' " . $sel . ">" .$val4 . "<BR />";
							}	
						print "</TD><TD style='width: 12%; vertical-align: text-top;'><B>Vehicles</B><BR />";
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
<?php
	}
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>
		<FORM NAME='cont_Form' METHOD="post" ACTION = "bulk_add_capab.php"></FORM>
		</DIV>	
	</BODY>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
outerWidth = viewportwidth *.98;
outerHeigth = viewportheight *.70;
colWidth = outerWidth * .80;
colHeight = outerHeight *.65;
set_fontsizes(viewportwidth, "fullscreen");
if($('the_fieldlist')) {$('the_fieldlist').style.maxHeight = colHeight + "px";}
if($('outer')) {$('outer').style.width = outerWidth + "px";}
if($('outer')) {$('outer').style.height = outerHeight + "px";}
if($('maincol')) {$('maincol').style.width = colWidth + "px";}
if($('maincol')) {$('maincol').style.height = colHeight + "px";}
</SCRIPT>
</HTML>	