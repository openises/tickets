<?php
require_once('../incs/functions.inc.php');
@session_start();
$sess_id = session_id();

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
var thetype;
var thepackage;
var randomnumber;
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
	
function validate_skills(theForm) {
	if (confirm("Are you sure you want to do this?")) { 
		theForm.submit();
		} else {
		}
	}				// end function va lidate_skills(theForm)		

function pop_types(type) {
	randomnumber=Math.floor(Math.random()*99999999);
	thetype=type;
	sendRequest ('../ajax/list_packages.php?key=' + type + '&session=<?php print MD5($sess_id);?>', poptypes_cb, "");			
		function poptypes_cb(req) {
			var the_ret_arr=req.responseText;
			$('s1').innerHTML = the_ret_arr;
			$('members').innerHTML = "";
		}
	}

function pop_members(thepackage) {
	randomnumber=Math.floor(Math.random()*99999999);
	var url = '../ajax/list_member_allocations.php?key=' + thepackage + '&type=' + thetype + '&session=<?php print MD5($sess_id);?>';
	sendRequest (url, poptypes_cb, "");			
		function poptypes_cb(req) {
			var the_ret_arr=req.responseText;
			$('members').innerHTML = the_ret_arr;
		}
	}		
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
			if(isset($_POST['cap_sel'])) {
				if($_POST['cap_sel'] == 1) {
					$frm_completed = $_POST['frm_year_frm_tra_comp'] . "-" . $_POST['frm_month_frm_tra_comp'] . "-" . $_POST['frm_day_frm_tra_comp'];
					$frm_refresh =  $_POST['frm_year_frm_tra_refresh'] . "-" . $_POST['frm_month_frm_tra_refresh'] . "-" . $_POST['frm_day_frm_tra_refresh'];	
					} else {
					$frm_completed = NULL;
					$frm_refresh = NULL;
					}
				if(isset($_POST['pack_sel'])){
					foreach($_POST['frm_tra'] AS $mem_id => $member) {
						add_allocation($mem_id, $_POST['cap_sel'], $_POST['pack_sel'], $frm_completed, $frm_refresh, NULL);
						}
					}
				} else {
				print "Error Processing changes";
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
		
			$training = array();
			$member_list = "";

			$query_tra	= "SELECT * FROM `$GLOBALS[mysql_prefix]training_packages`";
			$result_tra	= mysql_query($query_tra) or do_error($query_tra, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			while($row = mysql_fetch_array($result_tra,MYSQL_ASSOC)) {
				$training[$row['id']] = $row['package_name'];
				}
?>
			<DIV ID='left_col' style='position: relative; left: 5%; top: 60px; width: 90%; padding: 5px;'>	
				<FORM METHOD="POST" NAME= "bulk_edit_Form" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>">
					<FIELDSET>
					<LEGEND>Member Skills, Equipment, Clothing & Training Allocation</LEGEND>
					<TABLE style='width: 100%; padding: 10px;'>
						<TR style='vertical-align: top;'>
							<TH class='heading' style='width: 20%;'>Capability Type</TH>
							<TH class='heading' style='width: 30%;'>Capability</TH>
							<TH class='heading' style='width: 40%;'>Members</TH>	
						</TR>
						<TR VALIGN='top' style='vertical-align: top;'>	
							<TD class='td_data text' style='vertical-align: top; padding: 10px; border: 1px solid red;'>
								<SELECT name='cap_sel' style='width: 100%; height: 20px; font-size: 100%;' onChange='pop_types(this.options[this.selectedIndex].value);'>
									<OPTION style='font-size: 100%;' VALUE=0>Select One</OPTION>									
									<OPTION style='font-size: 100%;' VALUE=1>Training</OPTION>
									<OPTION style='font-size :100%;' VALUE=2>Capabilities</OPTION>
									<OPTION style='font-size :100%;' VALUE=3>Equipment</OPTION>
									<OPTION style='font-size :100%;' VALUE=4>Clothing</OPTION>
								</SELECT>
							<TD id='s1' class='td_data text' style='vertical-align: top; padding: 10px; border: 1px solid red;'></TD>
							<TD id='members' class='td_data text' style='vertical-align: top; padding: 10px; border: 1px solid red;'></TD>
						</TR>
					</TABLE>
					</FIELDSET>
				</FORM>						
			</DIV>

		<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>
		<FORM NAME='cont_Form' METHOD="post" ACTION = "bulk_add_allocation.php"></FORM>
<?php
			}
?>
	<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>
	<FORM NAME='cont_Form' METHOD="post" ACTION = "bulk_add_allocation.php"></FORM>
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