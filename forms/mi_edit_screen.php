<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;

function get_markup($id) {
	$ret_arr = array();
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `id` = " . $id;
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr['id'] = $row['id'];
		$ret_arr['name'] = $row['line_name'];
		$ret_arr['type'] = $row['line_type'];
		$ret_arr['status'] = $row['line_status'];
		$ret_arr['ident'] = $row['line_ident'];
		$ret_arr['cat'] = get_categoryName($row['line_cat_id']);
		$ret_arr['data'] = $row['line_data'];
		$ret_arr['color'] = $row['line_color'];
		$ret_arr['opacity'] = $row['line_opacity'];
		$ret_arr['width'] = $row['line_width'];
		$ret_arr['fill_color'] = $row['fill_color'];
		$ret_arr['fill_opacity'] = $row['fill_opacity'];
		$ret_arr['filled'] = $row['filled'];
		$ret_arr['updated'] = format_date_2($row['_on']);
		} else {
		$ret_arr['id'] = 0;
		}
	return $ret_arr;
	}
	
function get_categoryName($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['category'];
	}

?>
<SCRIPT>
window.onresize=function(){set_size();}

window.onload = function(){set_size();}

var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;

var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var baseHxIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -40]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});
			
var colors = new Array ('odd', 'even');

function set_size() {
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
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .48;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95;
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('incs_table').style.width = mapWidth + "px";
	$('incs_heading').style.width = mapWidth + "px";
	}

function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}

function validate(theForm) {						// Responder form contents validation	8/11/09
	if (theForm.frm_remove) {
		if (theForm.frm_remove.checked) {
			var str = "Please confirm removing '" + theForm.frm_name.value + "'";
			if(confirm(str)) 	{
				theForm.submit();					// 8/11/09
				return true;}
			else 				{return false;}
			}
		}
	var errmsg="";
							// 2/24/09, 3/24/10
	if (theForm.frm_name.value.trim()=="")													{errmsg+="Major Incident NAME is required.\n";}
	if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)					{errmsg+="Major Incident TYPE selection is required.\n";}			// 1/1/09

	if (theForm.frm_descr.value.trim()=="")													{errmsg+="Major Incident DESCRIPTION is required with Tracking.\n";}

	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {																	// good to go!
		theForm.submit();													// 7/21/09
		}
	}				// end function validate(theForm)
	
function do_end(theForm) {
	elem = $("enddate1");
	if(elem.style.visibility == "visible") {
		elem.style.visibility = "hidden";
		theForm.frm_year_inc_endtime.disabled = true;
		theForm.frm_month_inc_endtime.disabled = true;
		theForm.frm_day_inc_endtime.disabled = true;
		theForm.frm_hour_inc_endtime.disabled = true;
		theForm.frm_minute_inc_endtime.disabled = true;		
		} else {
		elem.style.visibility = "visible";
		theForm.frm_year_inc_endtime.disabled = false;
		theForm.frm_month_inc_endtime.disabled = false;
		theForm.frm_day_inc_endtime.disabled = false;
		theForm.frm_hour_inc_endtime.disabled = false;
		theForm.frm_minute_inc_endtime.disabled = false;		
		}
	}

</SCRIPT>
</HEAD>
<?php

$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `id`= " . $id;
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$lat = get_variable('def_lat');
$lng = get_variable('def_lng');
$boundary = $row['boundary'];
$existing_incs = array();
$query_x = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `mi_id` = " . $id . " ORDER BY `id`;";
$result_x = mysql_query($query_x) or do_error($query_x, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$cbcount = mysql_num_rows($result_x);				// count of incomplete assigns
$dis_rmv = ($cbcount==0)? "": " DISABLED";		// allow/disallow removal
$cbtext = ($cbcount==0)? "": "&nbsp;&nbsp;<FONT size=-2>(NA - incidents currently managed: " .$cbcount . " )</FONT>";
while ($row_x = stripslashes_deep(mysql_fetch_assoc($result_x))) {
	$existing_incs[] = $row_x['ticket_id'];
	}
	
$gold_command = ($row['gold_loc'] != 0) ? get_building_details($row['gold_loc']) : null;
$silver_command = ($row['silver_loc'] != 0) ? get_building_details($row['silver_loc']) : null;
$bronze_command = ($row['bronze_loc'] != 0) ? get_building_details($row['bronze_loc']) : null;

if($gold_command) {$gold_name = $gold_command[0]; $gold_address = $gold_command[1]; $gold_city = $gold_command[2]; $gold_state = $gold_command[3]; $gold_lat = floatval($gold_command[4]); $gold_lng = floatval($gold_command[5]); }
if($silver_command) {$silver_name = $silver_command[0]; $silver_address = $silver_command[1]; $silver_city = $silver_command[2]; $silver_state = $silver_command[3]; $silver_lat = floatval($silver_command[4]); $silver_lng = floatval($silver_command[5]); }
if($bronze_command) {$bronze_name = $bronze_command[0]; $bronze_address = $bronze_command[1]; $bronze_city = $bronze_command[2]; $bronze_state = $bronze_command[3]; $bronze_lat = floatval($bronze_command[4]); $bronze_lng = floatval($bronze_command[5]); }

if(!$gold_command) {
	$gold_name = $row['gold_street'] . " " . $row['gold_city'] . " " . $row['gold_state'];
	$gold_address = $row['gold_street'];
	$gold_city = $row['gold_city'];
	$gold_state = $row['gold_state'];
	$gold_lat = floatval($row['gold_lat']);
	$gold_lng = floatval($row['gold_lng']);
	}
	
if(!$silver_command) {
	$silver_name = $row['silver_street'] . " " . $row['silver_city'] . " " . $row['silver_state'];
	$silver_address = $row['silver_street'];
	$silver_city = $row['silver_city'];
	$silver_state = $row['silver_state'];
	$silver_lat = floatval($row['silver_lat']);
	$silver_lng = floatval($row['silver_lng']);
	}

if(!$bronze_command) {
	$bronze_name = $row['bronze_street'] . " " . $row['bronze_city'] . " " . $row['bronze_state'];
	$bronze_address = $row['bronze_street'];
	$bronze_city = $row['bronze_city'];
	$bronze_state = $row['bronze_state'];
	$bronze_lat = floatval($row['bronze_lat']);
	$bronze_lng = floatval($row['bronze_lng']);
	}
?>
<BODY>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
<A NAME='top'></A>
<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
<DIV id='outer' style='position: absolute; left: 0px;'>
	<DIV id='leftcol' style='position: absolute; left: 10px;'>
		<A NAME='top'>		<!-- 11/11/09 -->
		<FORM METHOD="POST" NAME= "mi_edit_Form" ENCTYPE="multipart/form-data" ACTION="maj_inc.php?goedit=true"> <!-- 7/9/09 -->
		<TABLE ID='editform' style='border: 1px outset #707070;'>
			<TR>
				<TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Major Incident '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
					<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT>
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>	
			<TR CLASS = "even">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Major Incident Name - enter, well, the name!">Major Incident Name</A>:<font color='red' size='-1'>*</font>
				</TD>			
				<TD COLSPAN=3><INPUT MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Major Incident Start Time / Date">Start Date/Time</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
				<TD COLSPAN=3 ><?php print generate_date_dropdown('inc_startime', strtotime($row['inc_startime']), FALSE);?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Major Incident Name">End Date/Time</A>:&nbsp;<input type="checkbox" name="end_but" onClick ="do_end(this.form);" />&nbsp;</TD>
				<TD COLSPAN=3 >
<?php
				if(is_date($row['inc_endtime'])) {
?>
					<SPAN style = "visibility:visible" ID = "enddate1"><?php print generate_date_dropdown('inc_endtime', strtotime($row['inc_endtime']), FALSE);?></SPAN></TD>
<?php
				} else {
?>
					<SPAN style = "visibility:hidden" ID = "enddate1"><?php print generate_date_dropdown('inc_endtime', 0, TRUE);?></SPAN></TD>
<?php
				}
?>
				</TD>
			</TR>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Type of Major Incident"><?php print get_text("MI Type");?></A>:</TD>
				<TD>
					<SELECT NAME="frm_type">	<!--  11/17/10 -->
						<OPTION VALUE=0>Select</OPTION>
<?php
						$query_types = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_types` ORDER BY `id` ASC";		// 12/18/10
						$result_types = mysql_query($query_types) or do_error($query_types, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_types = stripslashes_deep(mysql_fetch_assoc($result_types))) {
							$sel = ($row['type'] == $row_types['id']) ? "SELECTED" : "";
							print "\t<OPTION VALUE='{$row_types['id']}' {$sel}>{$row_types['name']}</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
				</TD>
			</TR>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Boundary for this Major Incident"><?php print get_text("Boundary");?></A>:</TD>
				<TD>
					<SELECT NAME="frm_boundary">	<!--  11/17/10 -->
						<OPTION VALUE=0>Select</OPTION>
<?php
						$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` ORDER BY `id` ASC";		// 12/18/10
						$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
							$sel = ($row['boundary'] == $row_bound['id']) ? "SELECTED" : "";
							print "\t<OPTION VALUE='{$row_bound['id']}' {$sel}>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Gold Command");?>"><?php print get_text("Gold Command");?></A>:</TD>
				<TD>
					<SPAN style='width: 100%; display: block;'>
						<SELECT NAME="frm_gold" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'gold_command_data'); showtheDiv('gold_location_data');">	<!--  11/17/10 -->
							<OPTION VALUE=0>Select Gold Command</OPTION>
<?php
							$query_gold = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";		// 12/18/10
							$result_gold = mysql_query($query_gold) or do_error($query_gold, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_gold = stripslashes_deep(mysql_fetch_assoc($result_gold))) {
								$sel = ($row['gold'] == $row_gold['id']) ? "SELECTED" : "";
								print "\t<OPTION VALUE='" . $row_gold['id'] . "' " . $sel . ">" . $row_gold['user'] . " - " . $row_gold['name_f'] . " " . $row_gold['name_l'] . "</OPTION>\n";
								}
?>
						</SELECT>
					</SPAN>
<?php
					if($row['gold'] != 0) {
?>
					<DIV id='gold_command_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='gold_command_data' style='display: none;'>
<?php
					}
?>
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][7];
										}
?>
								</TD>
							</TR>
						</TABLE>
					</DIV>
<?php
					if($row['gold'] != 0) {
?>
					<DIV id='gold_location_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='gold_location_data' style='display: none;'>
<?php
					}
?>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
<?php
							if($row['gold_loc'] != 0) {
?>
								<TD class='td_data'><?php print get_building_edit("frm_gold_loc", $row['gold_loc']);?></TD>
<?php
								} else {
								$temp = get_building_only("frm_gold_loc");
								$gold_street = "";
								$gold_city = get_variable('def_city');
								$gold_state = get_variable('def_st');
								if($temp){
?>
									<TD class='td_data'><?php print $temp;?><BR />
<?php
									} else {
?>
									<TD class='td_data'>
<?php
									if($row['gold_street'] == "") {
										$gold_street = "";
										$gold_city = get_variable('def_city');
										$gold_state = get_variable('def_st');
										} else {
										$gold_street = $row['gold_street'];
										$gold_city = $row['gold_city'];
										$gold_state = $row['gold_state'];											
										}
									}
?>
								<BUTTON type='button' id='gold_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "gold");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;
								<DIV style='width: 100%; display: inline-block; vertical-align: top;'>
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_gold_street' VALUE='<?php print $gold_street;?>' /><BR />
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_gold_city' VALUE='<?php print $gold_city;?>' /></BR>
									<INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_gold_state' VALUE='<?php print $gold_state;?>' />							
									<INPUT TYPE='hidden' NAME='frm_gold_lat' VALUE='<?php print $row['gold_lat'];?>'>
									<INPUT TYPE='hidden' NAME='frm_gold_lng' VALUE='<?php print $row['gold_lng'];?>'>
								</DIV>
								</TD>						
<?php							
								}
?>
						</TR>
					</TABLE>
					</DIV>
				</TD>
			</TR>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Silver Command");?>"><?php print get_text("Silver Command");?></A>:</TD>
				<TD>
					<SPAN style='width: 100%; display: block;'>					
						<SELECT NAME="frm_silver" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'silver_command_data'); showtheDiv('silver_location_data');">	<!--  11/17/10 -->
							<OPTION VALUE=0>Select Silver Command</OPTION>
<?php
							$query_silver = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
							$result_silver = mysql_query($query_silver) or do_error($query_silver, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_silver = stripslashes_deep(mysql_fetch_assoc($result_silver))) {
								$sel = ($row['silver'] == $row_silver['id']) ? "SELECTED" : "";
								print "\t<OPTION VALUE='" . $row_silver['id'] . "' " . $sel . ">" . $row_silver['user'] . " - "  . $row_silver['name_f'] . " " . $row_silver['name_l'] . "</OPTION>\n";
								}
?>
						</SELECT>
					</SPAN>
<?php
					if($row['silver'] != 0) {
?>
					<DIV id='silver_command_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='silver_command_data' style='display: none;'>
<?php
					}
?>
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][7];
										}
?>
								</TD>
							</TR>
						</TABLE>
					</DIV>
<?php
					if($row['silver'] != 0) {
?>
					<DIV id='silver_location_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='silver_location_data' style='display: none;'>
<?php
					}
?>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
<?php
							if($row['silver_loc'] != 0) {
?>
								<TD class='td_data'><?php print get_building_edit("frm_silver_loc", $row['silver_loc']);?></TD>
<?php
								} else {
								$temp = get_building_only("frm_silver_loc");
								$silver_street = "";
								$silver_city = get_variable('def_city');
								$silver_state = get_variable('def_st');
								if($temp){
?>
									<TD class='td_data'><?php print $temp;?><BR />
<?php
									} else {
?>
									<TD class='td_data'>
<?php
									if($row['silver_street'] == "") {
										$silver_street = "";
										$silver_city = get_variable('def_city');
										$silver_state = get_variable('def_st');
										} else {
										$silver_street = $row['silver_street'];
										$silver_city = $row['silver_city'];
										$silver_state = $row['silver_state'];											
										}
									}
?>
								<BUTTON type='button' id='silver_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "silver");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;
								<DIV style='width: 100%; display: inline-block; vertical-align: top;'>
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_silver_street' VALUE='<?php print $silver_street;?>' /><BR />
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_silver_city' VALUE='<?php print $silver_city;?>' /></BR>
									<INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_silver_state' VALUE='<?php print $silver_state;?>' />							
									<INPUT TYPE='hidden' NAME='frm_silver_lat' VALUE='<?php print $row['silver_lat'];?>'>
									<INPUT TYPE='hidden' NAME='frm_silver_lng' VALUE='<?php print $row['silver_lng'];?>'>
								</DIV>
								</TD>						
<?php							
								}
?>
						</TR>
					</TABLE>
					</DIV>
				</TD>
			</TR>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Bronze Command");?>"><?php print get_text("Bronze Command");?></A>:</TD>
				<TD>
					<SPAN style='width: 100%; display: block;'>		
						<SELECT NAME="frm_bronze" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'bronze_command_data'); showtheDiv('bronze_location_data');">	<!--  11/17/10 -->
							<OPTION VALUE=0>Select Bronze Command</OPTION>
<?php
							$query_bronze = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";		// 12/18/10
							$result_bronze = mysql_query($query_bronze) or do_error($query_bronze, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_bronze = stripslashes_deep(mysql_fetch_assoc($result_bronze))) {
								$sel = ($row['bronze'] == $row_bronze['id']) ? "SELECTED" : "";
								print "\t<OPTION VALUE='" . $row_bronze['id'] . "' " . $sel . ">" . $row_bronze['user'] . " - "  . $row_bronze['name_f'] . " " . $row_bronze['name_l'] . "</OPTION>\n";
								}
?>
						</SELECT>
					</SPAN>
<?php
					if($row['bronze'] != 0) {
?>
					<DIV id='bronze_command_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='bronze_command_data' style='display: none;'>
<?php
					}
?>
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][7];
										}
?>
								</TD>
							</TR>
						</TABLE>
					</DIV>
<?php
					if($row['bronze'] != 0) {
?>
					<DIV id='bronze_location_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='bronze_location_data' style='display: none;'>
<?php
					}
?>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
<?php
							if($row['bronze_loc'] != 0) {
?>
								<TD class='td_data'><?php print get_building_edit("frm_bronze_loc", $row['bronze_loc']);?></TD>
<?php
								} else {
								$temp = get_building_only("frm_bronze_loc");
								$bronze_street = "";
								$bronze_city = get_variable('def_city');
								$bronze_state = get_variable('def_st');
								if($temp){
?>
									<TD class='td_data'><?php print $temp;?><BR />
<?php
									} else {
?>
									<TD class='td_data'>
<?php
									if($row['bronze_street'] == "") {
										$bronze_street = "";
										$bronze_city = get_variable('def_city');
										$bronze_state = get_variable('def_st');
										} else {
										$bronze_street = $row['bronze_street'];
										$bronze_city = $row['bronze_city'];
										$bronze_state = $row['bronze_state'];											
										}
									}
?>
								<BUTTON type='button' id='bronze_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "bronze");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;
								<DIV style='width: 100%; display: inline-block; vertical-align: top;'>
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_bronze_street' VALUE='<?php print $bronze_street;?>' /><BR />
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_bronze_city' VALUE='<?php print $bronze_city;?>' /></BR>
									<INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_bronze_state' VALUE='<?php print $bronze_state;?>' />							
									<INPUT TYPE='hidden' NAME='frm_bronze_lat' VALUE='<?php print $row['bronze_lat'];?>'>
									<INPUT TYPE='hidden' NAME='frm_bronze_lng' VALUE='<?php print $row['bronze_lng'];?>'>
								</DIV>
								</TD>						
<?php							
								}
?>
						</TR>
					</TABLE>
					</DIV>
				</TD>
			</TR>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Level 4 Command");?>"><?php print get_text("Level 4 Command");?></A>:</TD>
				<TD>
					<SPAN style='width: 100%; display: block;'>		
						<SELECT NAME="frm_level4" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level4_command_data'); showtheDiv('level4_location_data');">	<!--  11/17/10 -->
							<OPTION VALUE=0>Select Level 4 Command</OPTION>
<?php
							$query_row_level4 = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";		// 12/18/10
							$result_row_level4 = mysql_query($query_row_level4) or do_error($query_row_level4, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_level4 = stripslashes_deep(mysql_fetch_assoc($result_bronze))) {
								$sel = ($row['level4'] == $row_level4['id']) ? "SELECTED" : "";
								print "\t<OPTION VALUE='" . $row_level4['id'] . "' " . $sel . ">" . $row_level4['user'] . " - "  . $row_level4['name_f'] . " " . $row_level4['name_l'] . "</OPTION>\n";
								}
?>
						</SELECT>
					</SPAN>
<?php
					if($row['level4'] != 0) {
?>
					<DIV id='level4_command_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='level4_command_data' style='display: none;'>
<?php
					}
?>
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][7];
										}
?>
								</TD>
							</TR>
						</TABLE>
					</DIV>
<?php
					if($row['level4'] != 0) {
?>
					<DIV id='level4_location_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='level4_location_data' style='display: none;'>
<?php
					}
?>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
<?php
							if($row['level4_loc'] != 0) {
?>
								<TD class='td_data'><?php print get_building_edit("frm_level4_loc", $row['level4_loc']);?></TD>
<?php
								} else {
								$temp = get_building_only("frm_level4_loc");
								$level4_street = "";
								$level4_city = get_variable('def_city');
								$level4_state = get_variable('def_st');
								if($temp){
?>
									<TD class='td_data'><?php print $temp;?><BR />
<?php
									} else {
?>
									<TD class='td_data'>
<?php
									if($row['level4_street'] == "") {
										$level4_street = "";
										$level4_city = get_variable('def_city');
										$level4_state = get_variable('def_st');
										} else {
										$level4_street = $row['level4_street'];
										$level4_city = $row['level4_city'];
										$level4_state = $row['level4_state'];											
										}
									}
?>
								<BUTTON type='button' id='level4_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "level4");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;
								<DIV style='width: 100%; display: inline-block; vertical-align: top;'>
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level4_street' VALUE='<?php print $level4_street;?>' /><BR />
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level4_city' VALUE='<?php print $level4_city;?>' /></BR>
									<INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_level4_state' VALUE='<?php print $level4_state;?>' />							
									<INPUT TYPE='hidden' NAME='frm_level4_lat' VALUE='<?php print $row['level4_lat'];?>'>
									<INPUT TYPE='hidden' NAME='frm_level4_lng' VALUE='<?php print $row['level4_lng'];?>'>
								</DIV>
								</TD>						
<?php							
								}
?>
						</TR>
					</TABLE>
					</DIV>
				</TD>
			</TR>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Level 5 Command");?>"><?php print get_text("Level 5 Command");?></A>:</TD>
				<TD>
					<SPAN style='width: 100%; display: block;'>		
						<SELECT NAME="frm_level5" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level5_command_data'); showtheDiv('level5_location_data');">	<!--  11/17/10 -->
							<OPTION VALUE=0>Select Level 5 Command</OPTION>
<?php
							$query_level5 = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";		// 12/18/10
							$result_level5 = mysql_query($query_level5) or do_error($query_level5, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_level5 = stripslashes_deep(mysql_fetch_assoc($result_level5))) {
								$sel = ($row['level5'] == $row_level5['id']) ? "SELECTED" : "";
								print "\t<OPTION VALUE='" . $row_level5['id'] . "' " . $sel . ">" . $row_level5['user'] . " - "  . $row_level5['name_f'] . " " . $row_level5['name_l'] . "</OPTION>\n";
								}
?>
						</SELECT>
					</SPAN>
<?php
					if($row['level5'] != 0) {
?>
					<DIV id='level5_command_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='level5_command_data' style='display: none;'>
<?php
					}
?>
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][7];
										}
?>
								</TD>
							</TR>
						</TABLE>
					</DIV>
<?php
					if($row['level5'] != 0) {
?>
					<DIV id='level5_location_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='level5_location_data' style='display: none;'>
<?php
					}
?>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
<?php
							if($row['level5_loc'] != 0) {
?>
								<TD class='td_data'><?php print get_building_edit("frm_level5_loc", $row['level5_loc']);?></TD>
<?php
								} else {
								$temp = get_building_only("frm_level5_loc");
								$level5_street = "";
								$level5_city = get_variable('def_city');
								$level5_state = get_variable('def_st');
								if($temp){
?>
									<TD class='td_data'><?php print $temp;?><BR />
<?php
									} else {
?>
									<TD class='td_data'>
<?php
									if($row['level5_street'] == "") {
										$level5_street = "";
										$level5_city = get_variable('def_city');
										$level5_state = get_variable('def_st');
										} else {
										$level5_street = $row['level5_street'];
										$level5_city = $row['level5_city'];
										$level5_state = $row['level5_state'];											
										}
									}
?>
								<BUTTON type='button' id='level5_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "level5");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;
								<DIV style='width: 100%; display: inline-block; vertical-align: top;'>
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level5_street' VALUE='<?php print $level5_street;?>' /><BR />
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level5_city' VALUE='<?php print $level5_city;?>' /></BR>
									<INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_level5_state' VALUE='<?php print $level5_state;?>' />							
									<INPUT TYPE='hidden' NAME='frm_level5_lat' VALUE='<?php print $row['level5_lat'];?>'>
									<INPUT TYPE='hidden' NAME='frm_level5_lng' VALUE='<?php print $row['level5_lng'];?>'>
								</DIV>
								</TD>						
<?php							
								}
?>
						</TR>
					</TABLE>
					</DIV>
				</TD>
			</TR>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Level 6 Command");?>"><?php print get_text("Level 6 Command");?></A>:</TD>
				<TD>
					<SPAN style='width: 100%; display: block;'>		
						<SELECT NAME="frm_level6" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level6_command_data'); showtheDiv('level6_location_data');">	<!--  11/17/10 -->
							<OPTION VALUE=0>Select Level 6 Command</OPTION>
<?php
							$query_level6 = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";		// 12/18/10
							$result_level6 = mysql_query($query_level6) or do_error($query_level6, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_level6 = stripslashes_deep(mysql_fetch_assoc($result_level6))) {
								$sel = ($row['level6'] == $row_level6['id']) ? "SELECTED" : "";
								print "\t<OPTION VALUE='" . $row_level6['id'] . "' " . $sel . ">" . $row_level6['user'] . " - "  . $row_level6['name_f'] . " " . $row_level6['name_l'] . "</OPTION>\n";
								}
?>
						</SELECT>
					</SPAN>
<?php
					if($row['level6'] != 0) {
?>
					<DIV id='level6_command_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='level6_command_data' style='display: none;'>
<?php
					}
?>
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][7];
										}
?>
								</TD>
							</TR>
						</TABLE>
					</DIV>
<?php
					if($row['level6'] != 0) {
?>
					<DIV id='level6_location_data' style='display: block;'>
<?php
					} else {
?>
					<DIV id='level6_location_data' style='display: none;'>
<?php
					}
?>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
<?php
							if($row['level6_loc'] != 0) {
?>
								<TD class='td_data'><?php print get_building_edit("frm_level6_loc", $row['level6_loc']);?></TD>
<?php
								} else {
								$temp = get_building_only("frm_level6_loc");
								$level6_street = "";
								$level6_city = get_variable('def_city');
								$level6_state = get_variable('def_state');
								if($temp){
?>
									<TD class='td_data'><?php print $temp;?><BR />
<?php
									} else {
?>
									<TD class='td_data'>
<?php
									if($row['level6_street'] == "") {
										$level6_street = "";
										$level6_city = get_variable('def_city');
										$level6_state = get_variable('def_state');
										} else {
										$level6_street = $row['level6_street'];
										$level6_city = $row['level6_city'];
										$level6_state = $row['level6_state'];											
										}
									}
?>
								<BUTTON type='button' id='level6_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "level6");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>&nbsp;&nbsp;
								<DIV style='width: 100%; display: inline-block; vertical-align: top;'>
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level6_street' VALUE='<?php print $level6_street;?>' /><BR />
									<INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level6_city' VALUE='<?php print $level6_city;?>' /></BR>
									<INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_level6_state' VALUE='<?php print $level6_state;?>' />							
									<INPUT TYPE='hidden' NAME='frm_level6_lat' VALUE='<?php print $row['level6_lat'];?>'>
									<INPUT TYPE='hidden' NAME='frm_level6_lng' VALUE='<?php print $row['level6_lng'];?>'>
								</DIV>
								</TD>						
<?php							
								}
?>
						</TR>
					</TABLE>
					</DIV>
				</TD>
			</TR>			
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>		
			<TR CLASS = "even">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Major Incident Description - additional details about Major Incident">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
				</TD>	
				<TD COLSPAN=3>
					<TEXTAREA NAME="frm_descr" COLS=56 ROWS=10><?php print $row['description'];?></TEXTAREA>
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Actions and Notes"><?php print get_text("Disposition");?></A>:&nbsp;
				</TD>	
				<TD COLSPAN=3 >
					<TEXTAREA NAME="frm_notes" COLS=56 ROWS=10><?php print $row['incident_notes'];?></TEXTAREA>
				</TD>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
			</TR>
			<TR class='heading'>
				<TD COLSPAN='4' class='heading' style='text-align: center;'>File Upload</TD>
			</TR>
			<TR class='even'>
				<TD class='td_label' style='text-align: left;'>Choose a file to upload:</TD>
				<TD COLSPAN='3' class='td_data' style='text-align: left;'><INPUT NAME="frm_file" TYPE="file" /></TD>
			</TR>
			<TR class='odd'>
				<TD class='td_label' style='text-align: left;'>File Name</TD>
				<TD COLSPAN='3'  class='td_data' style='text-align: left;'><INPUT NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE=""></TD>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
			</TR>
			<TR CLASS="odd" VALIGN='baseline'>
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete Major Incident from system.">Remove Major Incident</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
				<?php print $cbtext; ?>
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>
			<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
				<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
					<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
					<SPAN id='reset_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='track_reset(this.form); map_reset();'>Reset</SPAN>
					<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate(document.mi_edit_Form);'>Submit</SPAN>
				</TD>
			</TR>
		</TABLE>
		<A NAME="bottom" />
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>

		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
	</DIV>
	<DIV id='rightcol' style='position: absolute; right: 2%; z-index: 1;'>
		<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		<BR /><BR />
		<DIV id='incs_heading' class='heading' style='text-align: center;'>Incidents to be managed as part of the Major Incident</DIV>
		<DIV id= 'incs_table' style = 'max-height: 400px; border: 1px outset #707070; overflow-y: scroll;'>
			<TABLE>
				<TR CLASS = "even">
					<TD>
						<DIV>
<?php
							$query_inc = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_OPEN']}' OR `$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' ORDER BY `id` ASC";
							$result_inc = mysql_query($query_inc) or do_error($query_inc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_inc	= stripslashes_deep(mysql_fetch_assoc($result_inc))) {
								$sel = (in_array($row_inc['id'], $existing_incs, TRUE)) ? "CHECKED": "";
								$the_id = $row_inc['id'];
								print "<input type='checkbox' name='frm_inc[]' value='" . $row_inc['id'] . "' " . $sel . "><SPAN class='link' onClick='do_popup(" . $the_id . ");'>" . $row_inc['scope'] . "</SPAN><BR />";
								}
?>					
						</DIV>
					</TD>
				</TR>
			</TABLE>
		</DIV>
	</DIV>
	</FORM>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, $row['id']);
?>
</DIV>

<FORM NAME='can_Form' METHOD="post" ACTION = "maj_inc.php"></FORM>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
<SCRIPT>
var latLng;
var tmarkers = [];	//	Incident markers array
var rmarkers = [];	//	Responder markers array
var lmarkers = [];	//	Control locations markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var mapWidth = <?php print get_variable('map_width');?>;
var mapHeight = <?php print get_variable('map_height');?>;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(1, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
var bounds = map.getBounds();	
var zoom = map.getZoom();
<?php
do_kml();
?>
</SCRIPT>
<?php
if((is_float($gold_lat) && $gold_lat != "" && $gold_lat != NULL) && (is_float($gold_lng) && $gold_lng != "" && $gold_lng != NULL)) {
?>
<SCRIPT>
	var goldmarker = createLocMarker(<?php print $gold_lat;?>, <?php print $gold_lng;?>, "<?php print $gold_name;?>", 0, 1, "G", "<?php print get_text('Gold Command');?>");
	goldmarker.addTo(map);
</SCRIPT>
<?php
	}
	
if((is_float($silver_lat) && $silver_lat != "" && $silver_lat != NULL) && (is_float($silver_lng) && $silver_lng != "" && $silver_lng != NULL)) {
?>
<SCRIPT>
	var silvermarker = createLocMarker(<?php print $silver_lat;?>, <?php print $silver_lng;?>, "<?php print $silver_name;?>", 1, 1, "S", "<?php print get_text('Silver Command');?>");
	silvermarker.addTo(map);
</SCRIPT>
<?php
	}

if((is_float($bronze_lat) && $bronze_lat != "" && $bronze_lat != NULL) && (is_float($bronze_lng) && $bronze_lng != "" && $bronze_lng != NULL)) {
?>
<SCRIPT>
	var bronzemarker = createLocMarker(<?php print $bronze_lat;?>, <?php print $bronze_lng;?>, "<?php print $bronze_name;?>", 2, 1, "B", "<?php print get_text('Bronze Command');?>");
	bronzemarker.addTo(map);
</SCRIPT>
<?php
	}
foreach($existing_incs AS $val) {
	$query_tick = "SELECT *	FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $val . " ORDER BY `id` ASC";
	$result_tick = mysql_query($query_tick) or do_error($query_tick, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$mi_num_tick = mysql_num_rows($result_tick);
	while ($row_tick = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
?>
<SCRIPT>
		var marker = createTicMarker(<?php print $row_tick['lat'];?>, <?php print $row_tick['lng'];?>, "Ticket: <?php print $row_tick['scope'];?><BR />Major Incident: <?php print $row['name'];?>", <?php print $row_tick['severity'];?>, <?php print $row_tick['id'];?>, <?php print $row['id'];?>, "<?php print $row_tick['scope'];?>");
		marker.addTo(map);
</SCRIPT>
<?php
		$query_resp = "SELECT *, 
			`r`.`id` AS `resp_id`,
			`r`.`lat` AS `resp_lat`,
			`r`.`lng` AS `resp_lng`,
			`r`.`handle` AS `resp_handle`
			FROM `$GLOBALS[mysql_prefix]assigns` `a` 
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `a`.`responder_id` = `r`.`id` )
			WHERE `a`.`ticket_id` = " . $val . " ORDER BY `resp_id` ASC";
		$result_resp = mysql_query($query_resp) or do_error($query_resp, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$mi_num_resp = mysql_num_rows($result_resp);
		while ($row_resp = stripslashes_deep(mysql_fetch_assoc($result_resp))) {
?>
<SCRIPT>
			var rmarker = createRespMarker(<?php print $row_resp['resp_lat'];?>, <?php print $row_resp['resp_lng'];?>, <?php print $row_resp['resp_id'];?>, <?php print $row['id'];?>, "<?php print $row_resp['resp_handle'];?>")
			rmarker.addTo(map);
</SCRIPT>
<?php
			}
		}
	}
if($row['boundary'] > 0) {
	$theBound = get_markup($boundary);
?>
<SCRIPT>
	var theID = <?php print $theBound['id'];?>;
	var theLinename = "<?php print $theBound['name'];?>";
	var theIdent = "<?php print $theBound['ident'];?>";
	var theCategory = "<?php print $theBound['cat'];?>";
	var theData = "<?php print $theBound['data'];?>";
	var theColor = "<?php print '#' . $theBound['color'];?>";
	var theOpacity = <?php print $theBound['opacity'];?>;
	var theWidth = <?php print $theBound['width'];?>;
	var theFilled = <?php print $theBound['filled'];?>;
	var theFillcolor = "<?php print '#' . $theBound['fill_color'];?>";
	var theFillopacity = <?php print $theBound['fill_opacity'];?>;
	var theType = "<?php print $theBound['type'];?>";
	if(theType == "p") {
		var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "basemarkup", theID);
		} else if(theType == "c") {
		var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "basemarkup", theID);
		} else if(theType == "t") {
		var banner = drawBanner(theLinename, theData, theWidth, theColor, "basemarkup", theID);
		}
<?php
	}
?>
</SCRIPT>
</BODY>
</HTML>

