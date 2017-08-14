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
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
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
var fields = ["name",
			"description",
			"notes",
			"file",
			"filename",
];
var medfields = ["type",
				"boundary",
				"gold",
				"silver",
				"bronze",
				"level4",
				"level5",
				"level6",
				"gold_street",
				"gold_city",
				"silver_street",
				"silver_city",			
				"bronze_street",
				"bronze_city",			
				"level4_street",
				"level4_city",			
				"level5_street",
				"level5_city",
				"level6_street",
				"level6_city"];
var smallfields = ["gold_state",
				"silver_state",
				"bronze_state",
				"level4_state",
				"level5_state",
				"level6_state",
				"status"];

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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .43;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .2;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = outerheight + "px";
	$('editform').style.width = listwidth + "px";
	$('rightcol').style.width = colwidth + "px";
	$('incs_heading').style.width = colwidth + "px";
	$('incs_table').style.width = colwidth + "px";
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}
	set_fontsizes(viewportwidth, "fullscreen");
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
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
	<A NAME='top'></A>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<FORM METHOD="POST" NAME= "mi_edit_Form" ENCTYPE="multipart/form-data" ACTION="maj_inc.php?goedit=true">
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<TABLE ID='editform' style='border: 1px outset #707070;'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
						<SPAN CLASS='text_green text_biggest'>&nbsp;Edit Major Incident '<?php print $row['name'];?>' data</SPAN>
						<BR />
						<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>	
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Name - enter, well, the name!">Major Incident Name</A>:<font color='red' size='-1'>*</font>
					</TD>			
					<TD CLASS="td_data text" COLSPAN=3>
						<INPUT id='name' CLASS='td_data text' MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Start Time / Date">Start Date/Time</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;
					</TD>
					<TD CLASS="td_data text" COLSPAN=3 >
						<?php print generate_date_dropdown('inc_startime', strtotime($row['inc_startime']), FALSE);?>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Name">End Date/Time</A>:&nbsp;<input type="checkbox" name="end_but" onClick ="do_end(this.form);" />&nbsp;
					</TD>
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
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Type of Major Incident"><?php print get_text("MI Type");?></A>:
					</TD>
					<TD CLASS='td_data text'>
						<SELECT id='type' CLASS='text' NAME="frm_type">	<!--  11/17/10 -->
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
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Status"><?php print get_text("Status");?></A>:
					</TD>
					<TD CLASS='td_data text'>
<?php
				$sel1 = ($row['mi_status'] == "Open") ? "SELECTED" : "";
				$sel2 = ($row['mi_status'] == "Closed") ? "SELECTED" : "";
?>
						<SELECT id='status' NAME="frm_status">
							<OPTION VALUE="Open" <?php print $sel1;?>>Open</OPTION>
							<OPTION VALUE="Closed" <?php print $sel2;?>>Closed</OPTION>
						</SELECT>
					</TD>
				</TR>
				<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Sets Boundary for this Major Incident"><?php print get_text("Boundary");?></A>:
					</TD>
					<TD CLASS='td_data text'>
						<SELECT id='boundary' CLASS='text' NAME="frm_boundary">	<!--  11/17/10 -->
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
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Gold Command");?>"><?php print get_text("Gold Command");?></A>:
					</TD>
					<TD CLASS='td_data text'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='gold' CLASS='text' NAME="frm_gold" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'gold_command_data'); showtheDiv('gold_location_data');">	<!--  11/17/10 -->
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='gold_command_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text'>Email 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['gold']])) {
											print $comm_arr[$row['gold']][4];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Email 2</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['gold']])) {
											print $comm_arr[$row['gold']][5];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['gold']])) {
											print $comm_arr[$row['gold']][6];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 2</TD>
									<TD class='td_data text'>
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='gold_location_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text' style='vertical-align: top;'>Location</TD>
<?php
									if($row['gold_loc'] != 0) {
?>
										<TD class='td_data text'><?php print get_building_edit("frm_gold_loc", $row['gold_loc']);?></TD>
<?php
										} else {
										$temp = get_building_only("frm_gold_loc");
										$gold_street = "";
										$gold_city = get_variable('def_city');
										$gold_state = get_variable('def_st');
?>
										<TD class='td_data text'>
<?php
											if($temp){
												print $temp . "<BR />";
												} else {
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
											<DIV ID='gold_address_data' style='width: 100%; display: inline-block; vertical-align: top;'>
												<TABLE>
													<TR>
														<TD CLASS='td_label text'>Street&nbsp;&nbsp;
															<BUTTON type='button' id='gold_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "gold");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>
														</TD>
														<TD CLASS='td_data text'>
															<INPUT id='gold_street' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_gold_street' VALUE='<?php print $gold_street;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>City</TD>
														<TD CLASS='td_data text'>													
															<INPUT id='gold_city' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_gold_city' VALUE='<?php print $gold_city;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>State</TD>
														<TD CLASS='td_data text'>		
															<INPUT id='gold_state' MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_gold_state' VALUE='<?php print $gold_state;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>Lat / Lng</TD>
														<TD CLASS='td_data text'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_gold_lat' VALUE='<?php print $row['gold_lat'];?>'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_gold_lng' VALUE='<?php print $row['gold_lng'];?>'>
														</TD>
													</TR>
												</TABLE>
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
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Silver Command");?>"><?php print get_text("Silver Command");?></A>:</TD>
					<TD CLASS='td_data text'>
						<SPAN style='width: 100%; display: block;'>					
							<SELECT id='silver' CLASS='text' NAME="frm_silver" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'silver_command_data'); showtheDiv('silver_location_data');">	<!--  11/17/10 -->
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='silver_command_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text'>Email 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['silver']])) {
											print $comm_arr[$row['silver']][4];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Email 2</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['silver']])) {
											print $comm_arr[$row['silver']][5];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['silver']])) {
											print $comm_arr[$row['silver']][6];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 2</TD>
									<TD class='td_data text'>
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='silver_location_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text' style='vertical-align: top;'>Location</TD>
<?php
									if($row['silver_loc'] != 0) {
?>
										<TD class='td_data text'><?php print get_building_edit("frm_silver_loc", $row['silver_loc']);?></TD>
<?php
										} else {
										$temp = get_building_only("frm_silver_loc");
										$silver_street = "";
										$silver_city = get_variable('def_city');
										$silver_state = get_variable('def_st');
?>
										<TD class='td_data text'>
<?php
										if($temp){
											print $temp . "<BR />";
											} else {
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
											<DIV ID='silver_address_data' style='width: 100%; display: inline-block; vertical-align: top;'>
												<TABLE>
													<TR>
														<TD CLASS='td_label text'>Street&nbsp;&nbsp;
															<BUTTON type='button' id='silver_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "silver");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>
														</TD>
														<TD CLASS='td_data text'>
															<INPUT id='silver_street' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_silver_street' VALUE='<?php print $silver_street;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>City</TD>
														<TD CLASS='td_data text'>													
															<INPUT id='silver_city' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_silver_city' VALUE='<?php print $silver_city;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>State</TD>
														<TD CLASS='td_data text'>		
															<INPUT id='silver_state' MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_silver_state' VALUE='<?php print $silver_state;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>Lat / Lng</TD>
														<TD CLASS='td_data text'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_silver_lat' VALUE='<?php print $row['silver_lat'];?>'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_silver_lng' VALUE='<?php print $row['silver_lng'];?>'>
														</TD>
													</TR>
												</TABLE>
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
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Bronze Command");?>"><?php print get_text("Bronze Command");?></A>:</TD>
					<TD CLASS='td_data text'>
						<SPAN style='width: 100%; display: block;'>		
							<SELECT id='bronze' CLASS='text' NAME="frm_bronze" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'bronze_command_data'); showtheDiv('bronze_location_data');">	<!--  11/17/10 -->
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='bronze_command_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text'>Email 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['bronze']])) {
											print $comm_arr[$row['bronze']][4];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Email 2</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['bronze']])) {
											print $comm_arr[$row['bronze']][5];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['bronze']])) {
											print $comm_arr[$row['bronze']][6];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 2</TD>
									<TD class='td_data text'>
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='bronze_location_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text' style='vertical-align: top;'>Location</TD>
<?php
									if($row['bronze_loc'] != 0) {
?>
										<TD class='td_data text'><?php print get_building_edit("frm_bronze_loc", $row['bronze_loc']);?></TD>
<?php
										} else {
										$temp = get_building_only("frm_bronze_loc");
										$bronze_street = "";
										$bronze_city = get_variable('def_city');
										$bronze_state = get_variable('def_st');
?>
										<TD class='td_data text'>
<?php
										if($temp){
											print $temp . "<BR />";
											} else {
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
											<DIV ID='bronze_address_data' style='width: 100%; display: inline-block; vertical-align: top;'>
												<TABLE>
													<TR>
														<TD CLASS='td_label text'>Street&nbsp;&nbsp;
															<BUTTON type='button' id='bronze_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "bronze");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>
														</TD>
														<TD CLASS='td_data text'>
															<INPUT id='bronze_street' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_bronze_street' VALUE='<?php print $bronze_street;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>City</TD>
														<TD CLASS='td_data text'>													
															<INPUT id='bronze_city' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_bronze_city' VALUE='<?php print $bronze_city;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>State</TD>
														<TD CLASS='td_data text'>		
															<INPUT id='bronze_state' MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_bronze_state' VALUE='<?php print $bronze_state;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>Lat / Lng</TD>
														<TD CLASS='td_data text'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_bronze_lat' VALUE='<?php print $row['bronze_lat'];?>'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_bronze_lng' VALUE='<?php print $row['bronze_lng'];?>'>
														</TD>
													</TR>
												</TABLE>
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
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 4 Command");?>"><?php print get_text("Level 4 Command");?></A>:</TD>
					<TD CLASS='td_data text'>
						<SPAN style='width: 100%; display: block;'>	
							<SELECT id='level4' CLASS='text' NAME="frm_level4" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level4_command_data'); showtheDiv('level4_location_data');">	<!--  11/17/10 -->
								<OPTION VALUE=0>Select Level 4 Command</OPTION>
<?php
								$query_level4 = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";		// 12/18/10
								$result_level4 = mysql_query($query_level4) or do_error($query_level4, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_level4 = stripslashes_deep(mysql_fetch_assoc($result_level4))) {
									$sel = ($row['level4'] == $row_level4['id']) ? "SELECTED" : "";
									print "\t<OPTION VALUE='" . $row_level4['id'] . "' " . $sel . ">" . $row_level4['user'] . " - "  . $row_level4['name_f'] . " " . $row_level4['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
<?php
						if($row['level4'] != 0) {
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='level4_command_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text'>Email 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level4']])) {
											print $comm_arr[$row['level4']][4];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Email 2</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level4']])) {
											print $comm_arr[$row['level4']][5];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level4']])) {
											print $comm_arr[$row['level4']][6];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 2</TD>
									<TD class='td_data text'>
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='level4_location_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text' style='vertical-align: top;'>Location</TD>
<?php
									if($row['level4_loc'] != 0) {
?>
										<TD class='td_data text'><?php print get_building_edit("frm_level4_loc", $row['level4_loc']);?></TD>
<?php
										} else {
										$temp = get_building_only("frm_level4_loc");
										$level4_street = "";
										$level4_city = get_variable('def_city');
										$level4_state = get_variable('def_st');
?>
										<TD class='td_data text'>
<?php
										if($temp){
											print $temp . "<BR />";
											} else {
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
											<DIV ID='level4_address_data' style='width: 100%; display: inline-block; vertical-align: top;'>
												<TABLE>
													<TR>
														<TD CLASS='td_label text'>Street&nbsp;&nbsp;
															<BUTTON type='button' id='level4_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "level4");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>
														</TD>
														<TD CLASS='td_data text'>
															<INPUT id='level4_street' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level4_street' VALUE='<?php print $level4_street;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>City</TD>
														<TD CLASS='td_data text'>													
															<INPUT id='level4_city' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level4_city' VALUE='<?php print $level4_city;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>State</TD>
														<TD CLASS='td_data text'>		
															<INPUT id='level4_state' MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_level4_state' VALUE='<?php print $level4_state;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>Lat / Lng</TD>
														<TD CLASS='td_data text'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_level4_lat' VALUE='<?php print $row['level4_lat'];?>'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_level4_lng' VALUE='<?php print $row['level4_lng'];?>'>
														</TD>
													</TR>
												</TABLE>
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
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 5 Command");?>"><?php print get_text("Level 5 Command");?></A>:</TD>
					<TD CLASS='td_data text'>
						<SPAN style='width: 100%; display: block;'>		
							<SELECT id='level5' CLASS='text' NAME="frm_level5" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level5_command_data'); showtheDiv('level5_location_data');">	<!--  11/17/10 -->
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='level5_command_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text'>Email 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level5']])) {
											print $comm_arr[$row['level5']][4];
											}
?>
								</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Email 2</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level5']])) {
											print $comm_arr[$row['level5']][5];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level5']])) {
											print $comm_arr[$row['level5']][6];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 2</TD>
									<TD class='td_data text'>
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='level5_location_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text' style='vertical-align: top;'>Location</TD>
<?php
									if($row['level5_loc'] != 0) {
?>
										<TD class='td_data text'><?php print get_building_edit("frm_level5_loc", $row['level5_loc']);?></TD>
<?php
										} else {
										$temp = get_building_only("frm_level5_loc");
										$level5_street = "";
										$level5_city = get_variable('def_city');
										$level5_state = get_variable('def_st');
?>
										<TD class='td_data'>
<?php
										if($temp){
											print $temp . "<BR />";
											} else {
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
											<DIV ID='level5_address_data' style='width: 100%; display: inline-block; vertical-align: top;'>
												<TABLE>
													<TR>
														<TD CLASS='td_label text'>Street&nbsp;&nbsp;
															<BUTTON type='button' id='level5_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "level5");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>
														</TD>
														<TD CLASS='td_data text'>
															<INPUT id='level5_street' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level5_street' VALUE='<?php print $level5_street;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>City</TD>
														<TD CLASS='td_data text'>													
															<INPUT id='level5_city' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level5_city' VALUE='<?php print $level5_city;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>State</TD>
														<TD CLASS='td_data text'>		
															<INPUT id='level5_state' MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_level5_state' VALUE='<?php print $level5_state;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>Lat / Lng</TD>
														<TD CLASS='td_data text'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_level5_lat' VALUE='<?php print $row['level5_lat'];?>'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_level5_lng' VALUE='<?php print $row['level5_lng'];?>'>
														</TD>
													</TR>
												</TABLE>
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
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 6 Command");?>"><?php print get_text("Level 6 Command");?></A>:</TD>
					<TD CLASS='td_data text'>
						<SPAN style='width: 100%; display: block;'>		
							<SELECT id='level6' CLASS='text' NAME="frm_level6" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level6_command_data'); showtheDiv('level6_location_data');">
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
							$display = "block";
							} else {
							$display = "none";
							}
?>
						<DIV id='level6_command_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text'>Email 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level6']])) {
											print $comm_arr[$row['level6']][4];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Email 2</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level6']])) {
											print $comm_arr[$row['level6']][5];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 1</TD>
									<TD class='td_data text'>
<?php 
										if(isset($comm_arr[$row['level6']])) {
											print $comm_arr[$row['level6']][6];
											}
?>
									</TD>
								</TR>
								<TR>
									<TD class='td_label text'>Phone 2</TD>
									<TD class='td_data text'>
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
							$display = "block";
							} else {
							$display = "none";	
							}
?>
						<DIV id='level6_location_data' style='display: <?php print $display;?>;'>
							<TABLE>
								<TR>
									<TD class='td_label text' style='vertical-align: top;'>Location</TD>
<?php
									if($row['level6_loc'] != 0) {
?>
										<TD class='td_data text'><?php print get_building_edit("frm_level6_loc", $row['level6_loc']);?></TD>
<?php
										} else {
										$temp = get_building_only("frm_level6_loc");
										$level6_street = "";
										$level6_city = get_variable('def_city');
										$level6_state = get_variable('def_state');
?>
										<TD class='td_data'>
<?php
										if($temp){
											print $temp . "<BR />";
											} else {
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
											<DIV ID='level6_address_data' style='width: 100%; display: inline-block; vertical-align: top;'>
												<TABLE>
													<TR>
														<TD CLASS='td_label text'>Street&nbsp;&nbsp;
															<BUTTON type='button' id='level6_loc_button' onClick='mi_loc_lkup(document.mi_edit_Form, "level6");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON>
														</TD>
														<TD CLASS='td_data text'>
															<INPUT id='level6_street' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level6_street' VALUE='<?php print $level6_street;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>City</TD>
														<TD CLASS='td_data text'>													
															<INPUT id='level6_city' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='frm_level6_city' VALUE='<?php print $level6_city;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>State</TD>
														<TD CLASS='td_data text'>		
															<INPUT id='level6_state' MAXLENGTH='4' SIZE='4' TYPE='text' NAME='frm_level6_state' VALUE='<?php print $level6_state;?>' />
														</TD>
													</TR>
													<TR>
														<TD CLASS='td_label text'>Lat / Lng</TD>
														<TD CLASS='td_data text'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_level6_lat' VALUE='<?php print $row['level6_lat'];?>'>
															<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='frm_level6_lng' VALUE='<?php print $row['level6_lng'];?>'>
														</TD>
													</TR>
												</TABLE>
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
					<TD class='spacer' COLSPAN=99></TD>
				</TR>		
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Description - additional details about Major Incident">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD CLASS='td_data text' COLSPAN=3>
						<TEXTAREA id='description' NAME="frm_descr" COLS=56 ROWS=5><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Incident / Closure Notes - actions and other information noted during Incident and when closing"><?php print get_text("Disposition");?></A>:&nbsp;
					</TD>	
					<TD CLASS='td_data text' COLSPAN=3 >
						<TEXTAREA id='notes' NAME="frm_notes" COLS=56 ROWS=5><?php print $row['incident_notes'];?></TEXTAREA>
					</TD>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN='4' class='spacer'></TD>
				</TR>
				<TR class='heading'>
					<TD COLSPAN='4' class='heading text' style='text-align: center;'>File Upload</TD>
				</TR>
				<TR class='even'>
					<TD class='td_label text' style='text-align: left;'>Choose a file to upload:</TD>
					<TD COLSPAN='3' class='td_data text' style='text-align: left;'>
						<INPUT id='file' NAME="frm_file" TYPE="file" />
					</TD>
				</TR>
				<TR class='odd'>
					<TD class='td_label text' style='text-align: left;'>File Name</TD>
					<TD COLSPAN='3' class='td_data text' style='text-align: left;'>
						<INPUT id='filename' NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE="">
					</TD>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN='4' class='spacer'></TD>
				</TR>
				<TR CLASS="odd" VALIGN='baseline'>
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Delete Major Incident from system.">Remove Major Incident</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
					<?php print $cbtext; ?>
					</TD>
				</TR>
				<TR>
					<TD COLSPAN=99>&nbsp;</TD>
				</TR>
				<TR>
					<TD COLSPAN=99>&nbsp;</TD>
				</TR>
			</TABLE>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='track_reset(this.form); map_reset();'><?php print get_text("Reset");?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.mi_edit_Form);'><?php print get_text("Submit");?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV style='position: fixed; top: 0px; z-index: 9999;'>
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
			<DIV id='map_canvas' style='display: none;'></DIV>
			</DIV>
		</DIV>
	</FORM>

	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, $row['id']);
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "maj_inc.php"></FORM>
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
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .43;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = colwidth * .95
fieldwidth = colwidth * .6;
medfieldwidth = colwidth * .3;		
smallfieldwidth = colwidth * .2;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = outerheight + "px";
$('editform').style.width = listwidth + "px";
$('rightcol').style.width = colwidth + "px";
$('incs_heading').style.width = colwidth + "px";
$('incs_table').style.width = colwidth + "px";
for (var i = 0; i < fields.length; i++) {
	if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
	} 
for (var i = 0; i < medfields.length; i++) {
	if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
	}
for (var i = 0; i < smallfields.length; i++) {
	if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
	}
set_fontsizes(viewportwidth, "fullscreen");
<?php
if($good_internet) {
?>
	var latLng;
	var tmarkers = [];	//	Incident markers array
	var rmarkers = [];	//	Responder markers array
	var lmarkers = [];	//	Control locations markers array
	var boundary = [];			//	exclusion zones array
	var bound_names = [];
	var boundary = [];			//	exclusion zones array
	var bound_names = [];
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	var initZoom = <?php print get_variable('def_zoom');?>;
	init_map(1, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	var bounds = map.getBounds();	
	var zoom = map.getZoom();
<?php
	}
?>
</SCRIPT>
</BODY>
</HTML>

