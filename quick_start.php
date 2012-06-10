<?php
/*
5/11/12 Initial release - Basic setup script to build a default set of values and responders into Tickets
*/
require_once('./incs/functions.inc.php');

if((empty($_POST)) && (empty($_GET))) {	//	checks to make sure script is not run directly.
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'index.php';
	header("Location: http://$host$uri/$extra");
	exit();
	}
	
// The Help Text
$help1 = "<B>Tickets Quick Start</B><BR />This script helps a new Tickets user to populate some system settings required to get a new Tickets system working and also populate a number of responders with some default settinsg.<BR />";
$help1 .= "The settings populated here can be later changed by the user and the Responders can be edited to personalize them with actual names etc.<BR />";
$help1 .= "This first page allows you to select the country you are operating in - the purpose of this setting is to center the map on the center of the country and set the default time and date display.";

$help2 = "This page allows you to set the state when you have selected United States as the country. This can be changed later.";

$help3 = "<B>Titles</B><BR />On this page you can set the Name of your organisation so that it appears in the Tickets Titlebar.<BR />";
$help3 .= "You can leave this blank and a default Title string will be set - this can be changed later and is not operationally significant.<BR />";

$help4 = "<B>Incident Types</B><BR />Incidents can be set with a specific type allowing you to categorise the nature of the incident. This is also used for Priority coloring.<BR />";
$help4 .= "A couple of examples are give and you could either edit these, add more or just submit the examples and they will be built on your Tickets system.. These can be changed later or more added as required.";

$help5 = "<B>Responders</B><BR />On this page you can chose to build a number of Responders onto the system with basic settings and generic names such as<BR />";
$help5 .= "Responder_1, Responder_2 etc. The Name Prefix will be used for each Responder built plus an index from 1 to the number that you chose to build.";
$help5 .= "The Responders that you build here can be edited later, added to or deleted depending on operational need.";

$help6 = "<B>Responder Types</B><BR />Responders are categorized by type which helps to deploy the correct resources based on need. A couple of default types are built,<BR />";
$help6 .= "You can chose to edit these, add more or just submit those already provided. These can all be changed later and more added if required"; 

$help7 = "<B>Responder Types</B><BR />Responders are allocated operational status when using Tickets such as available, unavailable etc. You can use may operationally significant status values.<BR />";
$help7 .= "Your own operation will determine what these are and how many you will need. A couple of examples are given, you can edit these, add more or submit those provided. They can be changed, added or deleted later.";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

<HEAD><TITLE>Tickets - Preliminary Setup Tool</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC="./js/jscolor/jscolor.js"></SCRIPT>
<STYLE type="text/css">
.hover 	{ margin-left: 4px; font-size: 14px; color: #000000; border: 2px inset #FFFFFF; float: none; background: #DEE3E7; font-weight: bold; cursor: pointer;}
.plain 	{ margin-left: 4px; font-size: 14px; color: #000000; border: 2px outset #FFFFFF; float: none; background: #EFEFEF; font-weight: bold; cursor: pointer;}	
INPUT {width: 100%;}
TEXTAREA {width: 100%;}
SELECT {width: 100%;}
</STYLE>
<SCRIPT>
function validate(theForm) {
	var errmsg="";
	if (theForm.frm_db_host.value == "")			{errmsg+= "\tMySQL HOST name is required\n";}
	if (theForm.frm_db_dbname.value == "")			{errmsg+= "\tMySQL DATABASE name is required\n";}
	if (theForm.frm_api_key.value.length != 86)		{errmsg+= "\tGMaps API key is required - 86 chars\n";}
	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		return true;
		}
	}				// end function validate(theForm)

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}		
	
function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}
	
function do_plain (the_id) {				// 8/21/10
	CngClass(the_id, 'plain');
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

function det_time() {
	var d = new Date();
	var servDateArray ="<?php print date("Y/n/d/H/i/s", time())?>".split('/');	
	var s =new Date(Number(servDateArray[0]),Number(servDateArray[1])-1,Number(servDateArray[2]),Number(servDateArray[3]),Number(servDateArray[4]),Number(servDateArray[5]));	
	var d_time = Math.floor((d.getTime()/86400000)*24*60);
	var s_time = Math.floor((s.getTime()/86400000)*24*60);
	var z = s_time - d_time;
	if(typeof document.forms['theForm'] != 'undefined') {
		document.theForm.delta.value = z;	
		}
	}
	
var ct = 1;
var dt = 1;
var et = 1;

function new_line() {
	ct++;
	var div1 = document.createElement('div');
	div1.id = "severity" + ct;
	var the_text = "<DIV style='width: 90%;'>";
	the_text +=	"<TABLE style='width: 90%;'>";
	the_text +=	"<TR style='width: 98%;'>";		
	the_text +=	"<TD style='width: 20%;'><INPUT NAME='frm_name[]' TYPE='text' SIZE='20' MAXLENGTH='20' VALUE=''></TD>";
	the_text +=	"<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_desc[]' STYLE='overflow-y: scroll;'></TEXTAREA></TD>";		
	the_text +=	"<TD style='width: 20%;'><SELECT NAME='frm_sev[]'><OPTION VALUE=0 SELECTED>Normal</OPTION><OPTION VALUE=1>Medium<O/PTION><OPTION VALUE=2>High</OPTION></SELECT></TD>";	
	the_text +=	"<TD style='width: 20%;'><INPUT NAME='frm_grp[]' TYPE='text' SIZE='24' VALUE=''></TD>";
	the_text += '<TD style="width: 10%;"><SPAN id="a_line' + ct + '" class="plain" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="delIt(\'severity' + ct + '\')">Delete</SPAN></TD>';
	the_text +=	"</TR>";
	the_text +=	"</TABLE>";
	the_text +=	"</DIV>";
	div1.innerHTML = the_text;
	document.getElementById('formline').appendChild(div1);
	}

function delIt(eleId) {	// function to delete the newly added set of elements
	d = document;
	var ele = d.getElementById(eleId);
	var parentEle = d.getElementById('formline');
	parentEle.removeChild(ele);
	}
	
function new_line2() {
	dt++;
	var div2 = document.createElement('div');
	div2.id = "rtype" + dt;
	var the_text = "<DIV style='width: 90%;'>";
	the_text +=	"<TABLE style='width: 90%;'>";
	the_text +=	"<TR style='width: 98%;'>";
	the_text +=	"<TD style='width: 20%;'><INPUT NAME='frm_rtype_name[]' TYPE='text' SIZE='16' MAXLENGTH='16' VALUE=''></TD>";
	the_text +=	"<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_rtype_desc[]' STYLE='overflow-y: scroll;'></TEXTAREA></TD>";	
	the_text +=	"<TD style='width: 20%;'><SELECT NAME='frm_rtype_icon[]' onChange='do_icon_view(this.value, \"resp_icon_" + dt + "\");'>";
	the_text +=	"<OPTION VALUE=99 SELECTED>Select one</OPTION>";						
	the_text +=	"<OPTION VALUE=0>Black</OPTION>";
	the_text +=	"<OPTION VALUE=1>Blue</OPTION>";
	the_text +=	"<OPTION VALUE=2>Green</OPTION>";
	the_text +=	"<OPTION VALUE=3>Red</OPTION>";
	the_text +=	"<OPTION VALUE=4>White</OPTION>";
	the_text +=	"<OPTION VALUE=5>Yellow</OPTION>";
	the_text +=	"<OPTION VALUE=6>Grey</OPTION>";
	the_text +=	"<OPTION VALUE=7>Lt-Blue</OPTION>";	
	the_text +=	"<OPTION VALUE=8>Orange</OPTION>";
	the_text +=	"</SELECT>";		
	the_text +=	"</TD>";						
	the_text +=	"<TD style='width: 10%; text-align: right; background-color: #FFFFFF; border: 1px inset #DEDEDE;'><DIV id='resp_icon_" + dt + "' style='width: 100%; text-align: right;'></DIV></TD>";
	the_text += '<TD style="width: 20%;"><SPAN id="b_line' + dt + '" class="plain" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="delIt2(\'rtype' + dt + '\')">Delete</SPAN></TD>';
	the_text +=	"</TR>";	
	the_text +=	"</TABLE>";
	the_text +=	"</DIV>";
	div2.innerHTML = the_text;
	document.getElementById('formline2').appendChild(div2);
	}

function delIt2(eleId) {	// function to delete the newly added set of elements
	d = document;
	var ele = d.getElementById(eleId);
	var parentEle = d.getElementById('formline2');
	parentEle.removeChild(ele);
	}
	
function new_line3() {
	et++;
	var div3 = document.createElement('div');
	div3.id = "rstat" + et;
	var the_text = "<DIV style='width: 90%;'>";
	the_text +=	"<TABLE style='width: 90%;'>";
	the_text +=	"<TR style='width: 98%;'>";
	the_text +=	"<TD style='width: 20%;'><INPUT NAME='frm_rstat_name[]' TYPE='text' SIZE='12' MAXLENGTH='20' VALUE=''></TD>";
	the_text +=	"<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_rstat_desc[]' STYLE='overflow-y: scroll;'></TEXTAREA></TD>";		
	the_text +=	"<TD style='width: 20%;'><INPUT NAME='frm_rstat_group[]' TYPE='text' SIZE='12' VALUE=''></TD>";					
	the_text +=	"<TD style='width: 10%;'><INPUT CLASS='color' NAME='frm_rstat_bgcol[]' TYPE='text' SIZE='6' VALUE='FFFFFF'></TD>";
	the_text +=	"<TD style='width: 10%;'><INPUT CLASS='color' NAME='frm_rstat_col[]' TYPE='text' SIZE='6' VALUE='000000'></TD>";						
	the_text += '<TD style="width: 10%;"><SPAN id="c_line' + et + '" class="plain" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="delIt3(\'rstat' + et + '\')">Delete</SPAN></TD>';
	the_text +=	"</TR>";	
	the_text +=	"</TABLE>";
	the_text +=	"</DIV>";
	div3.innerHTML = the_text;
	document.getElementById('formline3').appendChild(div3);
	jscolor.init()	
	}

function delIt3(eleId) {	// function to delete the newly added set of elements
	d = document;
	var ele = d.getElementById(eleId);
	var parentEle = d.getElementById('formline3');
	parentEle.removeChild(ele);
	}

function page_changer(divid) {
	var theID = divid;
	var the_help = "";
	if(divid == "state_sel") {
		if (document.forms['theForm'].country_select.value == '0,0,Select one,,0') {
			alert ("No Country has been Selected - please correct");
			return false;
			} else {
			if(document.forms['theForm'].country_select.value != '39.8282,-98.5795,United States,USA,0') {
				theID = "titles";
				} else {
				theID = "state_sel";	
				}
			}
		}
	if(divid == "titles") {
		if (document.forms['theForm'].state_sel.value == '0,0,Select one,,0') {
			alert ("No State has been Selected - please correct");
			return false;
			}
		}
	switch(theID) {
		case "country_sel":
			the_help = "<?php print $help1;?>"
			break
		case "state_sel":
			the_help = "<?php print $help2;?>"
			break
		case "titles":
			the_help = "<?php print $help3;?>"
			break
		case "inc_types":
			the_help = "<?php print $help4;?>"
			break
		case "responders":
			the_help = "<?php print $help5;?>"
			break
		case "resp_types":
			the_help = "<?php print $help6;?>"
			break	
		case "resp_stats":
			the_help = "<?php print $help7;?>"
			break	
		}	
	if($('help_inner')) {
		$('help_inner').innerHTML = the_help
		}
	if($('country_sel')) {$('country_sel').style.display = 'none';}
	if($('state_sel')) {$('state_sel').style.display = 'none';}
	if($('titles')) {$('titles').style.display = 'none';}		
	if($('inc_types')) {$('inc_types').style.display = 'none';}
	if($('responders')) {$('responders').style.display = 'none';}	
	if($('resp_types')) {$('resp_types').style.display = 'none';}	
	if($('resp_stats')) {$('resp_stats').style.display = 'none';}	
	if($(theID)) {$(theID).style.display = 'block';}
	}
	
function get_cntr(the_arr) {
	var myArray = the_arr.split(','); 
	var lat = myArray[0];
	var lng = myArray[1];
	var country = myArray[2];
	var state = myArray[3];	
	var locale = myArray[4];
	$('latitude').innerHTML = lat;
	$('latitude').style.background = "#FFFFFF";
	$('longitude').innerHTML = lng;
	$('longitude').style.background = "#FFFFFF";	
	$('country').innerHTML = country;
	$('country').style.background = "#FFFFFF";	
	document.theForm.lat.value = lat;	
	document.theForm.lng.value = lng;
	document.theForm.locale.value = locale;
	document.theForm.country.value = country;		
	document.theForm.state.value = state;
	document.theForm.country_select.disabled = true;
	}
	
var sm_icons = new Array();
var icons = new Array();
var type_names = new Array();
var icons_dir = "./our_icons/";	

function gen_img_str(the_index) {						// returns image string for nth icon
	var the_sm_image = icons_dir + sm_icons[the_index];
	var the_title = icons[the_index].substr (0, icons[the_index].length-4).toUpperCase();	// extract color name
	return "<IMG SRC='" + the_sm_image + "'>";
	}

function do_icon_view(val, thediv) {
	$(thediv).innerHTML = gen_img_str(val);
	}

</script>
</HEAD>
<?php 
function count_responders() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query);	
	$count_responders = mysql_num_rows($result);
	return $count_responders;
	}
	
function do_setting ($which, $what) {				// 7/7/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";		// 5/25/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='" . $what . "' WHERE `name`='" . $which . "'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	unset ($result);
	return TRUE;
	}				// end function do_setting ()

if((count_responders() != 0) && (!isset($_GET['func']))) {
?>
	<BODY onLoad="det_time(); page_changer('country_sel');">
	<DIV style='position: absolute; left: 20%; top: 10%; background-color: #AEAEAE; color: #707070; width: 60%; padding-bottom: 50px;'>
	<DIV style='position: relative; font-size: 24px; background-color: #707070; color: #FFFFFF; font-weight: bold; width: 100%; text-align: center;'>Tickets Preliminary Setup Tool</DIV><BR /><BR /><BR />
	<DIV style='position: relative; top: 20px; left: 2.5%; width: 95%; padding-bottom: 50px; padding-top: 10px; border: 2px outset #DEDEDE; background-color: #CECECE;'>
		<DIV style='position: relative; font-size: 20px; width: 100%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>Preliminary Check</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV style=' padding: 40px; position: relative; font-size: 16px; width: 60%; font-weight: bold; text-align: center; background-color: red; color: #000000; border: 1px outset #707070;'>
		You already have responders built on this Tickets installation.<BR />
		Are you sure you want to run the Tickets Preliminary Setup Tool 
		</DIV>
		<BR />
		<BR />		
		<BR />
		<BR />			
		<SPAN id="stage3" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="location.href='quick_start.php?func=ok';">Yes do it.</SPAN>
		</CENTER>		
		<BR />
		<BR />			
	</DIV>
	</DIV>
<?php
}

elseif((!empty($_POST)) && (isset($_POST['country']))) {
	$now = mysql_format_date(time() - (intval($_POST['delta']*60)));	
	$from = $_SERVER['REMOTE_ADDR'];
	$user = 1;
	$output_text = "";
	$by = 1;	

	if(isset($_POST['frm_title'])) {
		do_setting ('title_string', $_POST['frm_title']);
		$output_text .= "Title String Set<BR />";
		}
		
	if((isset($_POST['lat'])) && (isset($_POST['lng']))) {
		do_setting ('def_lat', $_POST['lat']);
		do_setting ('def_lng', $_POST['lng']);		
		$output_text .= "Default Latitude and Longitude Set<BR />";
		}	

	if(isset($_POST['state'])) {
		do_setting ('def_st', $_POST['state']);
		$output_text .= "Default State Set<BR />";
		}

	if(isset($_POST['delta'])) {
		do_setting ('delta_mins', $_POST['delta']);
		$output_text .= "Default Server Time Difference Set<BR />";
		}	

	if((isset($_POST['locale'])) && ($_POST['locale'] != "undefined")) {
		do_setting ('locale', $_POST['locale']);
		$output_text .= "Default Date Format Set<BR />";
		}			

	if(isset($_POST['frm_name'])) {
		$i=0;
		foreach ($_POST['frm_name'] as $val) {
			$inc_type = substr($_POST['frm_name'][$i], 0, 20);
			$description = ($_POST['frm_desc'][$i] != "") ? substr($_POST['frm_desc'][$i], 0, 60) : "Not Completed";
			$severity = ($_POST['frm_sev'][$i] != NULL) ? $_POST['frm_sev'][$i] : 0;
			$grouping = ($_POST['frm_grp'][$i] != "") ? $_POST['frm_grp'][$i] : "Not Grouped";
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]in_types` (`type`,`description`,`set_severity`,`group`) VALUES('$inc_type','$description',$severity,'$grouping')";
			$result = mysql_query($query) or die("Incident types insertion failed, execution halted");	
			if($result) {
				$output_text .= "Incident Type " . $_POST['frm_name'] . " inserted<BR />";	
				}
			$i++;
			}
		}
		
	if(isset($_POST['frm_rtype_name'])) {
		$i=0;
		foreach ($_POST['frm_rtype_name'] as $val) {
			$resp_type = substr($_POST['frm_rtype_name'][$i], 0, 16);
			$description = ($_POST['frm_rtype_desc'][$i] != "") ? substr($_POST['frm_rtype_desc'][$i], 0, 48) : "Not Completed";
			$icon = ($_POST['frm_rtype_icon'][$i] != 99) ? $_POST['frm_rtype_icon'][$i] : 0 ;			
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]unit_types` (`name`,`description`,`icon`,`_on`,`_from`,`_by`) VALUES('$resp_type','$description','$icon','$now','$from',$user)";
			$result = mysql_query($query) or die("Unit types insertion failed, execution halted");		
			if($result) {
				$output_text .= "Responder Type " . $_POST['frm_rtype_name'] . " inserted<BR />";	
				}			
			$i++;
			}
		}	

	if(isset($_POST['frm_rstat_name'])) {
		$i=0;
		foreach ($_POST['frm_rstat_name'] as $val) {
			$resp_stat = substr($_POST['frm_rstat_name'][$i], 0, 20);
			$description = ($_POST['frm_rstat_desc'][$i] != "") ? substr($_POST['frm_rstat_desc'][$i], 0, 60) : "Not Completed";
			$can_dispatch = 0;
			$can_hide = "y";
			$grouping = ($_POST['frm_rstat_group'][$i] != "") ? $_POST['frm_rstat_group'][$i] : "Not Grouped";
			$bgcolor = ($_POST['frm_rstat_bgcol'][$i] != "") ?  "#" . $_POST['frm_rstat_bgcol'][$i] : "#FFFFFF";
			$textcol = ($_POST['frm_rstat_col'][$i] != "") ?  "#" . $_POST['frm_rstat_col'][$i] : "#000000";
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]un_status` (`status_val`,`description`,`dispatch`,`hide`,`group`,`bg_color`,`text_color`) VALUES('$resp_stat','$description','$can_dispatch','$can_hide','$grouping','$bgcolor','$textcol')";
			$result = mysql_query($query) or die("Unit Status Types insertion failed, execution halted");		
			if($result) {
				$output_text .= "Responder Status " . $_POST['frm_rstat_name'] . " inserted<BR />";	
				}	
			$i++;
			}
		}	

	if(isset($_POST['frm_num_responders'])) {
		$counter = $_POST['frm_num_responders'];
		$resp_prefix = (isset($_POST['frm_responder_prefix'])) ? $_POST['frm_responder_prefix'] : "Unit";
		$description = "Auto entered";
		$un_status = 1;
		$lat = "0.999999";
		$lng = "0.999999";
		for ( $i = 1; $i <= $counter; $i++) {
			$name = $resp_prefix . $i;
			$handle = substr($resp_prefix, 0, 3) . $i; 
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]responder` (
				`name`, `icon_str`, `handle`, `description`, `un_status_id`, `mobile`, `multi`, `aprs`, `instam`, `locatea`, `gtrack`, `glat`, `t_tracker`, `ogts`, `ring_fence`, `excl_zone`, `direcs`, `lat`, `lng`, `type`, `user_id`, `updated` )
				VALUES (" .
					quote_smart(trim($name)) . "," .
					quote_smart(trim($i)) . "," .
					quote_smart(trim($handle)) . "," .					
					quote_smart(trim($description)) . "," .
					quote_smart(trim($un_status)) . "," .
					0 . "," .
					0 . "," .
					0 . "," .
					0 . "," .
					0 . "," .
					0 . "," .
					0 . "," .
					0 . "," .	
					0 . "," .
					0 . "," .	
					0 . "," .					
					1 . "," .
					$lat . "," .
					$lng . "," .
					1 . "," .
					1 . "," .
					quote_smart(trim($now)) . ");";								// 8/23/08, 5/11/11

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if($result) {
				$new_id=mysql_insert_id();					
				$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` 
						(`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) 
						VALUES (
						1, 
						2,
						'$now', 
						1, 
						$new_id, 
						'Allocated to Group' , 
						$by)";
				$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$output_text .= "Responder " . $resp_prefix . $i . " inserted<BR />";	
				}	
			}
		}
?>
	<BODY onLoad="det_time(); page_changer('country_sel');">
	<DIV style='position: absolute; left: 20%; top: 10%; background-color: #AEAEAE; color: #707070; width: 60%; padding-bottom: 50px;'>
	<DIV style='position: relative; font-size: 24px; background-color: #707070; color: #FFFFFF; font-weight: bold; width: 100%; text-align: center;'>Tickets Preliminary Setup Tool</DIV><BR /><BR /><BR />
	<DIV style='position: relative; top: 20px; left: 2.5%; width: 95%; padding-bottom: 50px; padding-top: 10px; border: 2px outset #DEDEDE; background-color: #CECECE;'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>Setup Complete</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV style='width: 70%;'><?php print $output_text;?></DIV>
		<BR />
		<BR />
			<SPAN id="stage3" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="location.href='index.php';">Finish</SPAN>
		</CENTER>		
		<BR />
		<BR />			
	</DIV>
	</DIV>
	
<?php
} else {
?>
	<BODY onLoad="det_time(); page_changer('country_sel');">
	<DIV style='position: absolute; left: 20%; top: 10%; background-color: #AEAEAE; color: #707070; width: 60%; padding-bottom: 50px;'>
	<DIV style='position: relative; font-size: 24px; background-color: #707070; color: #FFFFFF; font-weight: bold; width: 100%; text-align: center;'>Tickets Preliminary Setup Tool</DIV><BR /><BR /><BR />
	<DIV style='position: relative; left: 10%; width: 80%; padding: 10px; background-color: #CECECE; color: blue; border: outset 2px #FFFFFF;'>
		<DIV style='width: 100%; border: 1px solid #DEDEDE; height: 30px;'>
			<DIV style='width: 12%; display: inline-block; color: #000000;'>Latitude</DIV><DIV id='latitude' style='width: 15%; display: inline-block;'>NOT SET</DIV>
			<DIV style='width: 12%; display: inline-block; color: #000000;'>Longitude</DIV><DIV id='longitude' style='width: 15%; display: inline-block;'>NOT SET</DIV>
			<DIV style='width: 12%; display: inline-block; color: #000000;'>Country</DIV><DIV id='country' style='width: 15%; display: inline-block;'>NOT SET</DIV>
			<DIV style='width: 10%; display: inline-block; color: #000000;'><SPAN id='help_but' class='plain' onMouseOver='do_hover(this);' onMouseOut='do_plain(this);' onClick="$('help').style.display='block'; $('help_but').style.display='none';" style='float: right;'>Help</SPAN></DIV>			
		</DIV>
	</DIV>
	<DIV id='help' style='position: relative; left: 10%; width: 80%; padding: 10px; background-color: yellow; color: blue; border: outset 2px #FFFFFF; display: none; min-height: 50px;'>
		<DIV style='width: 5%; display: inline-block; color: #000000; float: right;'><SPAN id='close_help' class='plain' onMouseOver='do_hover(this);' onMouseOut='do_plain(this);' onClick="$('help').style.display='none'; $('help_but').style.display='block';" style='float: right; font-size: 20px; color: red; vertical_align: text-top;'>X</SPAN></DIV>
		<DIV id='help_inner' style='width: 80%;'><?php print $help1;?></DIV>
	</DIV>
	<DIV style='position: relative; top: 20px; left: 2.5%; width: 95%; padding-bottom: 50px; padding-top: 10px; border: 2px outset #DEDEDE; background-color: #CECECE;'>
	<FORM NAME='theForm' METHOD='post' ACTION = '<?php print basename( __FILE__); ?>'>
	
<!-- First page display - Country -->

	<DIV id='country_sel'> 
		<DIV style='position: relative; font-size: 20px; width: 100%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>1 of 6 - Country</DIV><BR /><BR /><BR />
		<DIV style='position: relative; left: 20%; font-size: 16px; width: 60%; font-weight: bold; text-align: center; background-color: #DEDEDE; color: blue; border: 1px outset #707070; padding: 20px;'>
		Values entered during the initial setup procedure can be changed later through the Tickets configuration page.
		</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
			<TABLE style='width: 60%;'>	
				<TR style='width: 100%;'>
					<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 40%; font-size: 12px;'>Your Country</TD>
					<TD style='width: 10%;'>&nbsp;&nbsp;&nbsp;</TD>
					<TD style='width: 50%;'>
						<SELECT name = 'country_select' style='width: 100%;' ONCHANGE="get_cntr(this.options[this.selectedIndex].value); $('stage2').style.display='inline-block';">
							<OPTION value='0,0,Select one,,0'>Select one</OPTION>	
							<OPTION value='33.0000,66.0000,Afghanistan,AF,2'>Afghanistan</OPTION>
							<OPTION value='41.0000,20.0000,Albania,AL,2'>Albania</OPTION>
							<OPTION value='28.0000,3.0000,Algeria,DZ,2'>Algeria</OPTION>
							<OPTION value='-14.3333,-170.0000,American Samoa,AS,2'>American Samoa</OPTION> 
							<OPTION value='42.5000,1.5000,Andorra,AD,2'>Andorra</OPTION>
							<OPTION value='-12.5000,18.5000,Angola,AO,2'>Angola</OPTION>
							<OPTION value='18.2167,-63.0500,Anguilla,AI,2'>Anguilla</OPTION>
							<OPTION value='17.0500,-61.8000,Antigua and Barbuda,AG,2'>Antigua and Barbuda</OPTION> 
							<OPTION value='-34.0000,-64.0000,Argentina,AR,2'>Argentina</OPTION>
							<OPTION value='40.0000,45.0000,Armenia,AM,2'>Armenia</OPTION>
							<OPTION value='12.5000,-69.9667,Aruba,AW,2'>Aruba</OPTION>
							<OPTION value='-15.9500,-5.7000,Ascension,AC,2'>Ascension</OPTION>
							<OPTION value='-25.0000,135.0000,Australia,AU,2'>Australia</OPTION>
							<OPTION value='47.3333,13.3333,Austria,AT,2'>Austria</OPTION>
							<OPTION value='40.5000,47.5000,Azerbaijan,AZ,2'>Azerbaijan</OPTION>
							<OPTION value='24.0000,-76.0000,Bahamas,BS,2'>Bahamas</OPTION>
							<OPTION value='26.0000,50.5000,Bahrain,BH,2'>Bahrain</OPTION>
							<OPTION value='24.0000,90.0000,Bangladesh,BD,2'>Bangladesh</OPTION>
							<OPTION value='13.1667,-59.5333,Barbados,BB,2'>Barbados</OPTION>
							<OPTION value='53.0000,28.0000,Belarus,BY,2'>Belarus</OPTION>
							<OPTION value='50.8333,4.0000,Belgium,BE,2'>Belgium</OPTION>
							<OPTION value='17.2500,-88.7500,Belize,BZ,2'>Belize</OPTION>
							<OPTION value='9.5000,2.2500,Benin,BJ,2'>Benin</OPTION>
							<OPTION value='32.3333,-64.7500,Bermuda,BM,2'>Bermuda</OPTION>
							<OPTION value='27.5000,90.5000,Bhutan,BT,2'>Bhutan</OPTION>
							<OPTION value='-17.0000,-65.0000,Bolivia,BO,2'>Bolivia</OPTION>
							<OPTION value='12.2000,-68.2500,Bonaire,BQ,2'>Bonaire</OPTION>
							<OPTION value='44.2500,17.8333,Bosnia and Herzegovina,BA,2'>Bosnia and Herzegovina</OPTION> 
							<OPTION value='-22.0000,24.0000,Botswana,BW,2'>Botswana</OPTION>
							<OPTION value='-10.0000,-55.0000,Brazil,BR,2'>Brazil</OPTION>
							<OPTION value='18.5000,-64.5000,British Virgin Islands,VG,2'>British Virgin Islands</OPTION> 
							<OPTION value='4.5000,114.6667,Brunei,BN,2'>Brunei</OPTION>
							<OPTION value='43.0000,25.0000,Bulgaria,BG,2'>Bulgaria</OPTION>
							<OPTION value='13.0000,-2.0000,Burkina Faso,BF,2'>Burkina Faso</OPTION> 
							<OPTION value='-3.5000,30.0000,Burundi,BI,2'>Burundi</OPTION>
							<OPTION value='13.0000,105.0000,Cambodia,KH,2'>Cambodia</OPTION>
							<OPTION value='6.0000,12.0000,Cameroon,CM,2'>Cameroon</OPTION>
							<OPTION value='60.0000,-96.0000,Canada,CA,2'>Canada</OPTION>
							<OPTION value='16.0000,-24.0000,Cape Verde,CV,2'>Cape Verde</OPTION> 
							<OPTION value='19.5000,-80.6667,Cayman Islands,KY,2'>Cayman Islands</OPTION> 
							<OPTION value='7.0000,21.0000,Central African Republic,CF,2'>Central African Republic</OPTION> 
							<OPTION value='15.0000,19.0000,Chad,TD,2'>Chad</OPTION>
							<OPTION value='-30.0000,-71.0000,Chile,CL,2'>Chile</OPTION>
							<OPTION value='-10.5000,105.6667,Christmas Island,CX,2'>Christmas Island</OPTION> 
							<OPTION value='-12.0000,96.8333,Cocos (Keeling) Islands,CC,2'>Cocos (Keeling) Islands</OPTION> 
							<OPTION value='4.0000,-72.0000,Colombia,CO,2'>Colombia</OPTION>
							<OPTION value='-12.1667,44.2500,Comoros,KM,2'>Comoros</OPTION>
							<OPTION value='-16.0833,-161.5833,Cook Islands,CK,2'>Cook Islands</OPTION> 
							<OPTION value='10.0000,-84.0000,Costa Rica,CR,2'>Costa Rica</OPTION> 
							<OPTION value="8.0000,-5.0000,Cote d'Ivoire,CI,2">Cote d'Ivoire</OPTION> 
							<OPTION value='45.1667,15.5000,Croatia,HR,2'>Croatia</OPTION>
							<OPTION value='22.0000,-79.5000,Cuba,CU,2'>Cuba</OPTION>
							<OPTION value='12.1667,-69.0000,Curaçao,CW,2'>Curaçao</OPTION>
							<OPTION value='35.0000,33.0000,Cyprus,CY,2'>Cyprus</OPTION>
							<OPTION value='49.7500,15.0000,Czech Republic,CZ,2'>Czech Republic</OPTION> 
							<OPTION value='0.0000,25.0000,Democratic Republic of Congo,CD,2'>Democratic Republic of Congo</OPTION> 
							<OPTION value='56.0000,10.0000,Denmark,DK,2'>Denmark</OPTION>
							<OPTION value='11.5000,42.5000,Djibouti,DJ,2'>Djibouti</OPTION>
							<OPTION value='15.5000,-61.3333,Dominica,DM,2'>Dominica</OPTION>
							<OPTION value='19.0000,-70.6667,Dominican Republic,DO,2'>Dominican Republic</OPTION> 
							<OPTION value='-2.0000,-77.5000,Ecuador,EC,2'>Ecuador</OPTION>
							<OPTION value='27.0000,30.0000,Egypt,EG,2'>Egypt</OPTION>
							<OPTION value='13.8333,-88.9167,El Salvador,SV,2'>El Salvador</OPTION> 
							<OPTION value='2.0000,10.0000,Equatorial Guinea,GQ,2'>Equatorial Guinea</OPTION> 
							<OPTION value='15.0000,39.0000,Eritrea,ER,2'>Eritrea</OPTION>
							<OPTION value='59.0000,26.0000,Estonia,EE,2'>Estonia</OPTION>
							<OPTION value='8.0000,38.0000,Ethiopia,ET,2'>Ethiopia</OPTION>
							<OPTION value='-51.7500,-59.1667,Falkland Islands,FK,2'>Falkland Islands</OPTION> 
							<OPTION value='5.0000,152.0000,Federated States of Micronesia,FM,2'>Federated States of Micronesia</OPTION> 
							<OPTION value='-18.0000,178.0000,Fiji,FJ,2'>Fiji</OPTION>
							<OPTION value='64.0000,26.0000,Finland,FI,2'>Finland</OPTION>
							<OPTION value='46.0000,2.0000,France,FR,2'>France</OPTION>
							<OPTION value='4.0000,-53.0000,French Guiana,GF,2'>French Guiana</OPTION> 
							<OPTION value='-15.0000,-140.0000,French Polynesia,PF,2'>French Polynesia</OPTION> 
							<OPTION value='-1.0000,11.7500,Gabon,GA,2'>Gabon</OPTION>
							<OPTION value='13.5000,-15.5000,Gambia,GM,2'>Gambia</OPTION>
							<OPTION value='31.4251,34.3734,Gaza Strip,PS,2'>Gaza Strip</OPTION> 
							<OPTION value='42.0000,43.4999,Georgia,GE,2'>Georgia</OPTION>
							<OPTION value='51.5000,10.5000,Germany,DE,2'>Germany</OPTION>
							<OPTION value='8.0000,-2.0000,Ghana,GH,2'>Ghana</OPTION>
							<OPTION value='36.1333,-5.3500,Gibraltar,GI,2'>Gibraltar</OPTION>
							<OPTION value='39.0000,22.0000,Greece,GR,2'>Greece</OPTION>
							<OPTION value='72.0000,-40.0000,Greenland,GL,2'>Greenland</OPTION>
							<OPTION value='12.1167,-61.6667,Grenada,GD,2'>Grenada</OPTION>
							<OPTION value='16.2500,-61.5833,Guadeloupe,GP,2'>Guadeloupe</OPTION>
							<OPTION value='13.4444,144.7367,Guam,GU,2'>Guam</OPTION>
							<OPTION value='15.5000,-90.2500,Guatemala,GT,2'>Guatemala</OPTION>
							<OPTION value='11.0000,-10.0000,Guatemala,GN,2'>Guinea</OPTION>
							<OPTION value='12.0000,-15.0000,Guatemala,GW,2'>Guinea-Bissau</OPTION>
							<OPTION value='5.0000,-59.0000,Guatemala,GY,2'>Guyana</OPTION>
							<OPTION value='19.0000,-72.4167,Guatemala,HT,2'>Haiti</OPTION>
							<OPTION value='15.0000,-86.5000,Guatemala,HN,2'>Honduras</OPTION>
							<OPTION value='47.0000,20.0000,Guatemala,HU,2'>Hungary</OPTION>
							<OPTION value='65.0000,-18.0000,Guatemala,IS,2'>Iceland</OPTION>
							<OPTION value='20.0000,77.0000,Guatemala,IN,2'>India</OPTION>
							<OPTION value='-5.0000,120.0000,Guatemala,ID,2'>Indonesia</OPTION>
							<OPTION value='32.0000,53.0000,Guatemala,IR,2'>Iran</OPTION>
							<OPTION value='33.0000,44.0000,Guatemala,IQ,2'>Iraq</OPTION>
							<OPTION value='53.0000,-8.0000,Guatemala,IE,2'>Ireland</OPTION>
							<OPTION value='31.5000,34.7500,Guatemala,IL,2'>Israel</OPTION>
							<OPTION value='42.8333,12.8333,Guatemala,IT,2'>Italy</OPTION>
							<OPTION value='18.2500,-77.5000,Guatemala,JM,2'>Jamaica</OPTION>
							<OPTION value='36.0000,138.0000,Guatemala,JP,2'>Japan</OPTION>
							<OPTION value='31.0000,36.0000,Guatemala,JO,2'>Jordan</OPTION>
							<OPTION value='48.0000,68.0000,Guatemala,KZ,2'>Kazakhstan</OPTION>
							<OPTION value='1.0000,38.0000,Guatemala,KE,2'>Kenya</OPTION>
							<OPTION value='-5.0000,-170.0000,Guatemala,KI,2'>Kiribati</OPTION>
							<OPTION value='42.5833,21.0000,Guatemala,XK,2'>Kosovo</OPTION>
							<OPTION value='29.5000,47.7500,Guatemala,KW,2'>Kuwait</OPTION>
							<OPTION value='41.0000,75.0000,Guatemala,KG,2'>Kyrgyzstan</OPTION>
							<OPTION value='18.0000,105.0000,Guatemala,LA,2'>Laos</OPTION>
							<OPTION value='57.0000,25.0000,Guatemala,LV,2'>Latvia</OPTION>
							<OPTION value='33.8333,35.8333,Guatemala,LB,2'>Lebanon</OPTION>
							<OPTION value='-29.5000,28.2500,Guatemala,LS,2'>Lesotho</OPTION>
							<OPTION value='6.5000,-9.5000,Guatemala,LR,2'>Liberia</OPTION>
							<OPTION value='25.0000,17.0000,Libya,LY,2'>Libya</OPTION>
							<OPTION value='47.1667,9.5333,Liechtenstein,LI,2'>Liechtenstein</OPTION>
							<OPTION value='56.0000,24.0000,Lithuania,LT,2'>Lithuania</OPTION>
							<OPTION value='49.7500,6.1667,Luxembourg,LU,2'>Luxembourg</OPTION>
							<OPTION value='41.8333,22.0000,Macedonia,MK,2'>Macedonia</OPTION>
							<OPTION value='-20.0000,47.0000,Madagascar,MG,2'>Madagascar</OPTION>
							<OPTION value='-13.5000,34.0000,Malawi,MW,2'>Malawi</OPTION>
							<OPTION value='2.5000,112.5000,Malaysia,MY,2'>Malaysia</OPTION>
							<OPTION value='3.2000,73.0000,Maldives,MV,2'>Maldives</OPTION>
							<OPTION value='17.0000,-4.0000,Mali,ML,2'>Mali</OPTION>
							<OPTION value='35.9167,14.4333,Malta,MT,2'>Malta</OPTION>
							<OPTION value='10.0000,167.0000,Marshall Islands,MH,2'>Marshall Islands</OPTION> 
							<OPTION value='14.6667,-61.0000,Martinique,MQ,2'>Martinique</OPTION>
							<OPTION value='20.0000,-12.0000,Mauritania,MR,2'>Mauritania</OPTION>
							<OPTION value='-20.3000,57.5833,Mauritius,MU,2'>Mauritius</OPTION>
							<OPTION value='-12.8333,45.1667,Mayotte,YT,2'>Mayotte</OPTION>
							<OPTION value='23.0000,-102.0000,Mexico,MX,2'>Mexico</OPTION>
							<OPTION value='47.0000,29.0000,Moldova,MD,2'>Moldova</OPTION>
							<OPTION value='43.7333,7.4000,Monaco,MC,2'>Monaco</OPTION>
							<OPTION value='46.0000,105.0000,Mongolia,MN,2'>Mongolia</OPTION>
							<OPTION value='42.5000,19.3000,Montenegro,ME,2'>Montenegro</OPTION>
							<OPTION value='16.7500,-62.2000,Montserrat,MS,2'>Montserrat</OPTION>
							<OPTION value='32.0000,-5.0000,Morocco,MA,2'>Morocco</OPTION>
							<OPTION value='-18.2500,35.0000,Mozambique,MZ,2'>Mozambique</OPTION>
							<OPTION value='22.0000,98.0000,Myanmar (Burma),MM,2'>Myanmar (Burma)</OPTION>							
							<OPTION value='-22.0000,17.0000,Namibia,NA,2'>Namibia</OPTION>
							<OPTION value='-0.5333,166.9167,Nauru,NR,2'>Nauru</OPTION>
							<OPTION value='28.0000,84.0000,Nepal,NP,2'>Nepal</OPTION>
							<OPTION value='52.5000,5.7500,Netherlands,NL,2'>Netherlands</OPTION>
							<OPTION value='-21.5000,165.5000,New Caledonia,NC,2'>New Caledonia</OPTION> 
							<OPTION value='-42.0000,174.0000,New Zealand,NZ,2'>New Zealand</OPTION> 
							<OPTION value='13.0000,-85.0000,Nicaragua,NI,2'>Nicaragua</OPTION>
							<OPTION value='16.0000,8.0000,Niger,NE,2'>Niger</OPTION>
							<OPTION value='10.0000,8.0000,Nigeria,NG,2'>Nigeria</OPTION>
							<OPTION value='-19.0333,-169.8667,Niue,NU,2'>Niue</OPTION>
							<OPTION value='-29.0333,167.9500,Norfolk Island,NF,2'>Norfolk Island</OPTION> 
							<OPTION value='40.0000,127.0000,North Korea,KP,2'>North Korea</OPTION> 
							<OPTION value='16.0000,146.0000,Northern Mariana Islands,MP,2'>Northern Mariana Islands</OPTION> 
							<OPTION value='62.0000,10.0000,Norway,NO,2'>Norway</OPTION>
							<OPTION value='21.0000,57.0000,Oman,OM,2'>Oman</OPTION>
							<OPTION value='30.0000,70.0000,Pakistan,PK,2'>Pakistan</OPTION>
							<OPTION value='6.0000,134.0000,Palau,PW,2'>Palau</OPTION>
							<OPTION value='9.0000,-80.0000,Panama,PA,2'>Panama</OPTION>
							<OPTION value='-6.0000,147.0000,Papua New Guinea,PG,2'>Papua New Guinea</OPTION> 
							<OPTION value='-22.9933,-57.9964,Paraguay,PY,2'>Paraguay</OPTION>
							<OPTION value='35.0000,105.0000,Peoples Republic of China,CN,2'>Peoples Republic of China</OPTION>							
							<OPTION value='-10.0000,-76.0000,Peru,PE,2'>Peru</OPTION>
							<OPTION value='13.0000,122.0000,Philippines,PH,2'>Philippines</OPTION>
							<OPTION value='-25.0667,-130.1000,Pitcairn Islands,PN,2'>Pitcairn Islands</OPTION> 
							<OPTION value='52.0000,20.0000,Poland,PL,2'>Poland</OPTION>
							<OPTION value='39.5000,-8.0000,Portugal,PT,2'>Portugal</OPTION>
							<OPTION value='18.2483,-66.4999,Puerto Rico,PR,0'>Puerto Rico</OPTION> 
							<OPTION value='25.5000,51.2500,Qatar,QA,2'>Qatar</OPTION>
							<OPTION value='-1.0000,15.0000,Republic of the Congo,CG,2'>Republic of the Congo</OPTION> 
							<OPTION value='-21.1000,55.6000,Reunion,RE,2'>Reunion</OPTION>
							<OPTION value='46.0000,25.0000,Romania,RO,2'>Romania</OPTION>
							<OPTION value='60.0000,100.0000,Russia,RU,2'>Russia</OPTION>
							<OPTION value='-2.0000,30.0000,Rwanda,RW,2'>Rwanda</OPTION>
							<OPTION value='17.9000,-62.8333,Saint Barthelemy,BL,2'>Saint Barthelemy</OPTION> 
							<OPTION value='17.3333,-62.7500,Saint Kitts and Nevis,KN,2'>Saint Kitts and Nevis</OPTION> 
							<OPTION value='13.8833,-60.9667,Saint Lucia,LC,2'>Saint Lucia</OPTION> 
							<OPTION value='18.0417,-63.0667,Saint Maarten,MF,2'>Saint Maarten</OPTION> 							
							<OPTION value='18.0750,-63.0583,Saint Martin,MF,2'>Saint Martin</OPTION> 
							<OPTION value='46.8333,-56.3333,Saint Pierre and Miquelon,PM,2'>Saint Pierre and Miquelon</OPTION> 
							<OPTION value='13.0833,-61.2000,Saint Vincent and the Grenadines,VC,2'>Saint Vincent and the Grenadines</OPTION> 
							<OPTION value='-13.8031,-172.1783,Samoa,WS,2'>Samoa</OPTION>
							<OPTION value='43.9333,12.4167,San Marino,SM,2'>San Marino</OPTION> 
							<OPTION value='1.0000,7.0000,Sao Tome and Principe,ST,2'>Sao Tome and Principe</OPTION> 
							<OPTION value='25.0000,45.0000,Saudi Arabia,SA,2'>Saudi Arabia</OPTION>
							<OPTION value='14.0000,-14.0000,Senegal,SN,2'>Senegal</OPTION>
							<OPTION value='44.0000,21.0000,Serbia,CS,2'>Serbia</OPTION>
							<OPTION value='-4.5833,55.6667,Seychelles,SC,2'>Seychelles</OPTION>
							<OPTION value='8.5000,-11.5000,Sierra Leone,SL,2'>Sierra Leone</OPTION> 
							<OPTION value='1.3667,103.8000,Singapore,SG,2'>Singapore</OPTION>
							<OPTION value='18.0417,-63.0667,Sint Maarten,SX,2'>Sint Maarten</OPTION> 
							<OPTION value='48.6667,19.5000,Slovakia,SK,2'>Slovakia</OPTION>
							<OPTION value='46.2500,15.1667,Slovenia,SI,2'>Slovenia</OPTION>
							<OPTION value='-8.0000,159.0000,Solomon Islands,SB,2'>Solomon Islands</OPTION> 
							<OPTION value='6.0000,48.0000,Somalia,SO,2'>Somalia</OPTION>
							<OPTION value='-30.0000,26.0000,South Africa,ZA,2'>South Africa</OPTION> 
							<OPTION value='37.0000,127.5000,South Korea,KR,2'>South Korea</OPTION> 
							<OPTION value='8.0000,30.0000,South Sudan,SS,2'>South Sudan</OPTION> 
							<OPTION value='40.0000,-4.0000,Spain,ES,2'>Spain</OPTION>
							<OPTION value='7.0000,81.0000,Sri Lanka,LK,2'>Sri Lanka</OPTION> 
							<OPTION value='16.0000,30.0000,Sudan,SD,2'>Sudan</OPTION>
							<OPTION value='4.0000,-56.0000,Suriname,SR,2'>Suriname</OPTION>
							<OPTION value='-26.5000,31.5000,Swaziland,SZ,2'>Swaziland</OPTION>
							<OPTION value='62.0000,15.0000,Sweden,SE,2'>Sweden</OPTION>
							<OPTION value='47.0000,8.0000,Switzerland,CH,2'>Switzerland</OPTION>
							<OPTION value='35.0000,38.0000,Syria,SY,2'>Syria</OPTION>
							<OPTION value='24.0000,121.0000,Taiwan,TW,2'>Taiwan</OPTION>
							<OPTION value='39.0000,71.0000,Tajikistan,TJ,2'>Tajikistan</OPTION>
							<OPTION value='-6.0000,35.0000,Tanzania,TZ,2'>Tanzania</OPTION>
							<OPTION value='15.0000,100.0000,Thailand,TH,2'>Thailand</OPTION>
							<OPTION value='-8.8333,125.7500,Timor-Leste,TL,2'>Timor-Leste</OPTION>
							<OPTION value='8.0000,1.1667,Togo,TG,2'>Togo</OPTION>
							<OPTION value='-9.0000,-171.7500,Tokelau,TK,2'>Tokelau</OPTION>
							<OPTION value='-20.0000,-175.0000,Tonga,TO,2'>Tonga</OPTION>
							<OPTION value='11.0000,-61.0000,Trinidad and Tobago,TT,2'>Trinidad and Tobago</OPTION> 
							<OPTION value='34.0000,9.0000,Tunisia,TN,2'>Tunisia</OPTION>
							<OPTION value='39.0590,34.9115,Turkey,TR,2'>Turkey</OPTION>
							<OPTION value='40.0000,60.0000,Turkmenistan,TM,2'>Turkmenistan</OPTION>
							<OPTION value='21.7333,-71.5833,Turks and Caicos Islands,TC,2'>Turks and Caicos Islands</OPTION> 
							<OPTION value='-8.0000,178.0000,Tuvalu,TV,2'>Tuvalu</OPTION>
							<OPTION value='2.0000,33.0000,Uganda,UG,2'>Uganda</OPTION>
							<OPTION value='49.0000,32.0000,Ukraine,UA,2'>Ukraine</OPTION>
							<OPTION value='24.0000,54.0000,United Arab Emirates,AE,2'>United Arab Emirates</OPTION> 
							<OPTION value='51.78,-0.02,United Kingdom,UK,1'>United Kingdom</OPTION> 
							<OPTION value='39.8282,-98.5795,United States,USA,0'>United States</OPTION> 
							<OPTION value='-33.0000,-56.0000,Uruguay,UY,2'>Uruguay</OPTION>
							<OPTION value='18.3483,-64.9835,US Virgin Islands,VI,2'>US Virgin Islands</OPTION> 
							<OPTION value='41.7075,63.8491,Uzbekistan,UZ,2'>Uzbekistan</OPTION>
							<OPTION value='-16.0000,167.0000,Vanuatu,VU,2'>Vanuatu</OPTION>
							<OPTION value='41.9000,12.4500,Vatican City,VA,2'>Vatican City</OPTION> 
							<OPTION value='8.0000,-66.0000,Venezuela,VE,2'>Venezuela</OPTION>
							<OPTION value='16.1667,107.8333,Vietnam,VN,2'>Vietnam</OPTION>
							<OPTION value='-13.3000,-176.2000,Wallis and Futuna,WF,2'>Wallis and Futuna</OPTION> 
							<OPTION value='31.6667,35.2500,West Bank,PS,2'>West Bank</OPTION> 
							<OPTION value='25.0000,-13.5000,Western Sahara,EH,2'>Western Sahara</OPTION> 
							<OPTION value='15.5000,47.5000,Yemen,YE,2'>Yemen</OPTION>
							<OPTION value='-15.0000,30.0000,Zambia,ZM,2'>Zambia</OPTION>
							<OPTION value='-19.0000,29.0000,Zimbabwe,ZW,2'>Zimbabwe</OPTION>
						</SELECT>
					</TD>
				</TR>
			</TABLE>		
		</CENTER>
		<BR />
		<BR />		
		<BR />
		<CENTER><SPAN id="stage2" class="plain" style='padding: 10px; display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('state_sel');">Next</SPAN></CENTER>
		<BR />
		<BR />
	</DIV>

<!-- Second page display - US State -->

	<DIV id='state_sel'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>1 of 6 (part 2) - USA State</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
			<TABLE style='width: 60%;'>	
				<TR style='width: 100%;'>
					<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 40%; font-size: 12px;'>Your US State</TD>
					<TD style='width: 10%;'>&nbsp;&nbsp;&nbsp;</TD>
					<TD style='width: 50%;'>		
						<SELECT name = 'state_sel' style='width: 100%;' ONCHANGE="get_cntr(this.options[this.selectedIndex].value);this.disabled=true; $('stage2a').style.display='inline-block';">
							<OPTION value='0,0,Select one,,0'>Select one</OPTION>		
							<OPTION value='63.08,-149.06, United States, AK,0'>Alaska</OPTION>
							<OPTION value='32.61,-86.68, United States, AL,0'>Alabama</OPTION>
							<OPTION value='34.76,-92.13, United States, AR,0'>Arkansas</OPTION>
							<OPTION value='-14.31,-170.7, United States, AS,0'>American Samoa</OPTION> 
							<OPTION value='34.17,-111.93, United States, AK,0'>Arizona</OPTION>
							<OPTION value='37.27,-119.26, United States, CA,0'>California</OPTION>
							<OPTION value='39,-105.55, United States, CO,0'>Colorado</OPTION>
							<OPTION value='41.52,-72.76, United States, CT,0'>Connecticut</OPTION>
							<OPTION value='39.15,-75.42, United States, DE,0'>Delaware</OPTION>
							<OPTION value='28.06,-81.69, United States, FL,0'>Florida</OPTION>
							<OPTION value='32.68,-83.23, United States, GA,0'>Georgia</OPTION>
							<OPTION value='13.44,144.79, United States, GU,0'>Guam</OPTION>
							<OPTION value='19.59,-155.44, United States, HI,0'>Hawaii</OPTION>
							<OPTION value='41.94,-93.39, United States, IA,0'>Iowa</OPTION> 
							<OPTION value='45.49,-114.14, United States, ID,0'>Idaho</OPTION>
							<OPTION value='39.75,-89.51, United States, IL,0'>Illinois</OPTION>
							<OPTION value='39.77,-86.44, United States, IN,0'>Indiana</OPTION>
							<OPTION value='38.5,-98.32, United States, KS,0'>Kansas</OPTION>
							<OPTION value='37.82,-85.76, United States, KY,0'>Kentucky</OPTION>
							<OPTION value='30.98,-91.43, United States, LA,0'>Louisiana</OPTION>
							<OPTION value='42.06,-71.71, United States, MA,0'>Massachusetts</OPTION>
							<OPTION value='38.83,-76.74, United States, MD,0'>Maryland</OPTION>
							<OPTION value='45.26,-69.02, United States, ME,0'>Maine</OPTION>
							<OPTION value='43.75,-84.62, United States, MI,0'>Michigan</OPTION>
							<OPTION value='46.44,-93.36, United States, MN,0'>Minnesota</OPTION>
							<OPTION value='38.3,-92.44, United States, MO,0'>Missouri</OPTION>
							<OPTION value='15.19,145.76, United States, MP,0'>Marianas</OPTION>
							<OPTION value='32.59,-89.87, United States, MS,0'>Mississippi</OPTION>
							<OPTION value='46.68,-110.05, United States, MT,0'>Montana</OPTION>
							<OPTION value='35.22,-79.89, United States, NC,0'>North Carolina</OPTION> 
							<OPTION value='47.47,-100.3, United States, ND,0'>North Dakota</OPTION> 
							<OPTION value='41.5,-99.68, United States, NE,0'>Nebraska</OPTION>
							<OPTION value='44,-71.63, United States, NH,0'>New Hampshire</OPTION> 
							<OPTION value='40.14,-74.38, United States, NJ,0'>New Jersey</OPTION> 
							<OPTION value='34.17,-106.03, United States, NM,0'>New Mexico</OPTION> 
							<OPTION value='38.5,-117.02, United States, NV,0'>Nevada</OPTION>
							<OPTION value='42.76,-75.81, United States, NY,0'>New York</OPTION> 
							<OPTION value='40.19,-82.67, United States, OH,0'>Ohio</OPTION> 
							<OPTION value='35.31,-98.72, United States, OK,0'>Oklahoma</OPTION>
							<OPTION value='44.13,-120.51, United States, OR,0'>Oregon</OPTION>
							<OPTION value='40.99,-77.61, United States, PA,0'>Pennsylvania</OPTION>
							<OPTION value='18.2,-66.59, United States, PR,0'>Puerto Rico</OPTION> 
							<OPTION value='41.58,-71.51, United States, RI,0'>Rhode Island</OPTION> 
							<OPTION value='33.62,-80.95, United States, SC,0'>South Carolina</OPTION> 
							<OPTION value='44.22,-100.26, United States, SD,0'>South Dakota</OPTION> 
							<OPTION value='35.83,-85.98, United States, TN,0'>Tennessee</OPTION>
							<OPTION value='31.17,-100.08, United States, TX,0'>Texas</OPTION>
							<OPTION value='39.5,-111.54, United States, UT,0'>Utah</OPTION> 
							<OPTION value='38,-79.47, United States, VA,0'>Virginia</OPTION>
							<OPTION value='17.73,-64.73, United States, VI,0'>Virgin Islands</OPTION> 
							<OPTION value='43.87,-72.47, United States, VT,0'>Vermont</OPTION>
							<OPTION value='38.89,-77.02, United States, DC,0'>Washington, DC</OPTION> 
							<OPTION value='47.27,-120.84, United States, WA,0'>Washington</OPTION>
							<OPTION value='44.78,-89.85, United States, WI,0'>Wisconsin</OPTION>
							<OPTION value='38.92,-80.18, United States, WV,0'>West Virginia</OPTION> 
							<OPTION value='43,-107.55, United States, WY,0'>Wyoming</OPTION>
							<OPTION value='28.2,-177.37, United States, UM,0'>Midway</OPTION>
						</SELECT>
					</TD>
				</TR>
			</TABLE>		
		</CENTER>						
		<BR />
		<BR />	
		<BR />		
		<CENTER>
			<SPAN id="back2a" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('country_sel');">Back</SPAN>		
			<SPAN id="stage2a" class="plain" style='padding: 10px; display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('titles');">Next</SPAN>
		</CENTER>
		<BR />
		<BR />
	</DIV>

<!-- Third page display - Titles -->
	
	<DIV id='titles'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>2 of 6 - Titles</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV style='width: 70%;'>
			<TABLE style='width: 90%;'>	
				<TR style='width: 100%;'>
					<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px; width: 30%;'>Your Site</TD>
					<TD style='width: 50%;'><INPUT NAME='frm_title' TYPE='text' SIZE='5' VALUE='Example Site'></TD>
				</TR>
			</TABLE>
		</DIV>
		<C/ENTER>		
		<BR />
		<BR />
		<CENTER>
			<SPAN id="back3" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('country_sel');document.theForm.country_select.disabled = false;">Back</SPAN>	
			<SPAN id="stage3" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('inc_types');">Next</SPAN>
		</CENTER>		
		<BR />
		<BR />			
	</DIV>	

<!-- Fourth page display - incident types-->

	<DIV id='inc_types'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>3 of 6 - Incident Types</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV id="formline">
			<DIV style='width: 90%;'>
				<TABLE style='width: 90%;'>	
					<TR style='width: 100%;'>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Name</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 30%; font-size: 12px;'>Description</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Severity</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Group (Group Name)</TD>
						<TD style='width: 10%; font-size: 12px;'>&nbsp;</TD>						
					</TR>
					<TR style='width: 98%;'>
						<TD style='width: 20%;'><INPUT NAME='frm_name[]' TYPE='text' SIZE='20' MAXLENGTH='20' VALUE='MVC'></TD>
						<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_desc[]' STYLE='overflow-y: scroll;'>Motor Vehicle Accident - no injuries</TEXTAREA></TD>	
						<TD style='width: 20%;'>
							<SELECT NAME='frm_sev[]'>
								<OPTION VALUE=0>Normal</OPTION>
								<OPTION VALUE=1 SELECTED>Medium</OPTION>
								<OPTION VALUE=2>High</OPTION>
							</SELECT>
						</TD>
						<TD style='width: 20%;'><INPUT NAME='frm_grp[]' TYPE='text' SIZE='20' MAXLENGTH='20' VALUE='Traffic'></TD>
						<TD style='width: 10%;'>&nbsp;</TD>
					</TR>
					<TR style='width: 98%;'>
						<TD style='width: 20%;'><INPUT NAME='frm_name[]' TYPE='text' SIZE='20' MAXLENGTH='20' VALUE='Ambulance - BLS'></TD>
						<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_desc[]' STYLE='overflow-y: scroll;'>Medical Response Ambulance - Basic Life Support</TEXTAREA></TD>	
						<TD style='width: 20%;'>
							<SELECT NAME='frm_sev[]'>
								<OPTION VALUE=0>Normal</OPTION>
								<OPTION VALUE=1>Medium</OPTION>
								<OPTION VALUE=2 SELECTED>High</OPTION>
							</SELECT>
						</TD>
						<TD style='width: 20%;'><INPUT NAME='frm_grp[]' TYPE='text' SIZE='20' MAXLENGTH='20' VALUE='Medical'></TD>
						<TD style='width: 10%;'>&nbsp;</TD>
					</TR>					
				</TABLE>
			</DIV>	
		</DIV>
		</CENTER>
		<BR />
		<BR />
		<CENTER>
			<SPAN id='add_newline' class='plain' style='padding: 10px; background-color: yellow;' onMouseover='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='new_line();'>Add Line</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
			<SPAN id="back4" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('titles');">Back</SPAN>	
			<SPAN id="stage4" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('responders');">Next</SPAN>
		</CENTER>		
		<BR />
		<BR />		
	</DIV>

<!-- Fifth page display - Responders-->
	
	<DIV id='responders'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'> 4 of 6 - Responders</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV style='width: 90%;'>
			<TABLE style='width: 90%;'>	
				<TR style='width: 100%;'>
					<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>No. of Responders</TD>
					<TD style='width: 20%;'><INPUT NAME='frm_num_responders' TYPE='text' SIZE='5' VALUE='10'></TD><TD style='width: 20%;'>&nbsp;&nbsp;&nbsp;</TD>
					<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Name Prefix</TD>
					<TD style='width: 20%;'><INPUT NAME='frm_responder_prefix' TYPE='text' SIZE='12' MAXLENGTH='12' VALUE='Responder_'></TD>
				</TR>					
			</TABLE>
		</DIV>
		</CENTER>		
		<BR />
		<BR />
		<BR />
		<BR />		
		<CENTER>
			<SPAN id="back5" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('inc_types');">Back</SPAN>	
			<SPAN id="stage5" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('resp_types');">Next</SPAN>			
		</CENTER>		
		<BR />
		<BR />			
	</DIV>		
	
<!-- Sixth page display - Responder Types -->	

<SCRIPT>
<?php

	$icons = $GLOBALS['icons'];
	for ($i=0; $i<count($icons); $i++) {										// onto JS array
		print "\ticons.push(\"{$icons[$i]}\");\n";								// 11/20/10
		}
	
	$sm_icons = $GLOBALS['sm_icons'];
	for ($i=0; $i<count($sm_icons); $i++) {
		print "\tsm_icons.push(\"{$sm_icons[$i]}\");\n";								// 11/20/10
		}
?>
</SCRIPT>
	<DIV id='resp_types'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>5 of 6 - Responder Types</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV id="formline2" style='align: center;'>
			<DIV style='width: 90%;'>
				<TABLE style='width: 90%;'>	
					<TR style='width: 100%;'>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Name</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 30%; font-size: 12px;'>Description</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Icon</TD>
						<TD style='width: 10%; font-size: 12px;'>&nbsp;</TD>						
						<TD style='width: 20%; font-size: 12px;'>&nbsp;</TD>						
					</TR>
					<TR style='width: 98%;'>
						<TD style='width: 20%;'><INPUT NAME='frm_rtype_name[]' TYPE='text' SIZE='16' MAXLENGTH='16' VALUE='1st Responder'></TD>
						<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_rtype_desc[]' STYLE='overflow-y: scroll;'>Fast Response Paramedic</TEXTAREA></TD>						
						<TD style='width: 20%;'>
							<SELECT NAME='frm_rtype_icon[]' onChange='do_icon_view(this.value, "resp_icon_0");'>
								<OPTION VALUE=99>Select one</OPTION>						
								<OPTION VALUE=0>Black</OPTION>
								<OPTION VALUE=1>Blue</OPTION>
								<OPTION VALUE=2 SELECTED>Green</OPTION>
								<OPTION VALUE=3>Red</OPTION>
								<OPTION VALUE=4>White</OPTION>
								<OPTION VALUE=5>Yellow</OPTION>
								<OPTION VALUE=6>Grey</OPTION>
								<OPTION VALUE=7>Lt-Blue</OPTION>	
								<OPTION VALUE=8>Orange</OPTION>		
							</SELECT>
						</TD>						
						<TD style='width: 10%; background-color: #FFFFFF; border: 1px inset #DEDEDE;'><DIV id='resp_icon_0' style='width: 100%; text-align: right;'><IMG SRC='./our_icons/sm_green.png'></DIV></TD>
						<TD style='width: 20%; text-align: right;'>&nbsp;</TD>
					</TR>
					<TR style='width: 98%;'>
						<TD style='width: 20%;'><INPUT NAME='frm_rtype_name[]' TYPE='text' SIZE='16' MAXLENGTH='16' VALUE='Trans Ambulance'></TD>
						<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_rtype_desc[]' STYLE='overflow-y: scroll;'>Transport Ambulance - no emergency use</TEXTAREA></TD>						
						<TD style='width: 20%;'>
							<SELECT NAME='frm_rtype_icon[]' onChange='do_icon_view(this.value, "resp_icon_0");'>
								<OPTION VALUE=99>Select one</OPTION>						
								<OPTION VALUE=0>Black</OPTION>
								<OPTION VALUE=1>Blue</OPTION>
								<OPTION VALUE=2>Green</OPTION>
								<OPTION VALUE=3>Red</OPTION>
								<OPTION VALUE=4>White</OPTION>
								<OPTION VALUE=5 SELECTED>Yellow</OPTION>
								<OPTION VALUE=6>Grey</OPTION>
								<OPTION VALUE=7>Lt-Blue</OPTION>	
								<OPTION VALUE=8>Orange</OPTION>		
							</SELECT>
						</TD>						
						<TD style='width: 10%; background-color: #FFFFFF; border: 1px inset #DEDEDE;'><DIV id='resp_icon_0' style='width: 100%; text-align: right;'><IMG SRC='./our_icons/sm_yellow.png'></DIV></TD>
						<TD style='width: 20%; text-align: right;'>&nbsp;</TD>
					</TR>					
				</TABLE>
			</DIV>	
		</DIV>
		</CENTER>
		<BR />
		<BR />
		<CENTER>
			<SPAN id='add_newline2' class='plain' style='padding: 10px; background-color: yellow;' onMouseover='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='new_line2();'>Add Line</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;			
			<SPAN id="back6" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('responders');">Back</SPAN>	
			<SPAN id="stage6" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('resp_stats');">Next</SPAN>
		</CENTER>		
		<BR />
		<BR />			
	</DIV>

<!-- Seventh page display - Responder Status Definitions -->		
	
	<DIV id='resp_stats'>
		<DIV style='position: relative; font-size: 20px; width: 95%; font-weight: bold; text-align: center; background-color: #FFFFFF; color: #707070;'>6 of 6 - Responder Status Definitions</DIV><BR /><BR /><BR />
		<BR /><BR /><CENTER>
		<DIV id="formline3">
			<DIV style='width: 90%;'>
				<TABLE style='width: 90%;'>	
					<TR style='width: 100%;'>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Status</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 30%; font-size: 12px;'>Description</TD>
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 20%; font-size: 12px;'>Group (Group Name)</TD>						
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 10%; font-size: 12px;'>BG Color</TD>	
						<TD style='font-weight: bold; background-color: #707070; color: #FFFFFF; width: 10%; font-size: 12px;'>Text Color</TD>							
						<TD style='width: 10%; font-size: 12px;'>&nbsp;</TD>						
					</TR>
					<TR style='width: 98%;'>
						<TD style='width: 20%;'><INPUT NAME='frm_rstat_name[]' TYPE='text' SIZE='12' MAXLENGTH='20' VALUE='On Duty'></TD>
						<TD style='width: 30%;'><TEXTAREA ROWS='2' COLS='24' NAME='frm_rstat_desc[]' STYLE='overflow-y: scroll;'>Responder on Duty</TEXTAREA></TD>		
						<TD style='width: 20%;'><INPUT NAME='frm_rstat_group[]' TYPE='text' SIZE='12' MAXLENGTH='20' VALUE='Available'></TD>						
						<TD style='width: 10%;'><INPUT CLASS='color' NAME='frm_rstat_bgcol[]' TYPE='text' SIZE='6' VALUE='1FFF1F'></TD>
						<TD style='width: 10%;'><INPUT CLASS='color' NAME='frm_rstat_col[]' TYPE='text' SIZE='6' VALUE='FFFFFF'></TD>						
						<TD style='width: 10%;'>&nbsp;</TD>
					</TR>
				</TABLE>
			</DIV>	
		</DIV>
		</CENTER>
		<BR />
		<BR />
		<CENTER>
			<SPAN id='add_newline3' class='plain' style='padding: 10px; background-color: yellow;' onMouseover='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='new_line3();'>Add Line</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;		
			<SPAN id="back7" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="page_changer('resp_types');">Back</SPAN>		
			<SPAN id="stage7" class="plain" style='padding: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['theForm'].submit();">Finish</SPAN>
		</CENTER>		
		<BR />
		<BR />			
	</DIV>	

	<INPUT TYPE='hidden' NAME='resp_lat' VALUE='0.999999'>
	<INPUT TYPE='hidden' NAME='resp_lng' VALUE='0.999999'>	
	<INPUT TYPE='hidden' NAME='lat' VALUE=''>	
	<INPUT TYPE='hidden' NAME='lng' VALUE=''>	
	<INPUT TYPE='hidden' NAME='country' VALUE=''>	
	<INPUT TYPE='hidden' NAME='state' VALUE=''>	
	<INPUT TYPE='hidden' NAME='delta' VALUE=''>
	<INPUT TYPE='hidden' NAME='locale' VALUE=''>	
	</FORM>
	
	</DIV>
	</DIV>
	</BODY>
	</HTML>
<?php
}
