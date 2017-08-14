<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);

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
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();

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
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('viewform').style.width = colwidth + "px";
	$('incs_table').style.width = mapWidth + "px";
	$('incs_heading').style.width = mapWidth + "px";
	set_fontsizes(viewportwidth, "fullscreen");
	map.invalidateSize();
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

</SCRIPT>
</HEAD>
<?php

$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `id`= " . $id;
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$lat = get_variable('def_lat');
$lng = get_variable('def_lng');
$existing_incs = array();
$query_x = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `mi_id` = " . $id . " ORDER BY `id`;";
$result_x = mysql_query($query_x) or do_error($query_x, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if($result) {
	while ($row_x = stripslashes_deep(mysql_fetch_assoc($result_x))) {
		$existing_incs[] = $row_x['ticket_id'];
		}
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
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px; z-index: 999;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'></A>
			<TABLE ID='viewform'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
						<SPAN CLASS='text_green text_biggest'>&nbsp;View Major Incident '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></SPAN>
						<BR />
						<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>	
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Name">Major Incident Name</A>:
					</TD>			
					<TD CLASS='td_data text'><?php print $row['name'] ;?></TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Start Time / Date">Start Date/Time</A>:&nbsp;
					</TD>
					<TD CLASS="td_data text"><?php print format_date_2(strtotime($row['inc_startime']));?></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Major Incident Status">Status</A>:&nbsp;
					</TD>
					<TD CLASS="td_data text">
<?php 
						if(is_date($row['inc_endtime'])) {
							print format_date_2(strtotime($row['inc_endtime']));
							} else {
							print "N/A";
							}
?>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Major Incident End Time / Date">End Date/Time</A>:&nbsp;</TD>
					<TD CLASS="td_data text">
						<?php print $row['mi_status'];?>
					</TD>
				</TR>
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Boundary for this Major Incident"><?php print get_text("Boundary");?></A>:</TD>
					<TD CLASS="td_data text">
<?php
						if($row['boundary'] > 0) {
							$boundary = get_markup($row['boundary']);
							print $boundary['name'];
							} else {
							print "";
							}
?>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="MI Description - additional details about Major Incident">Description</A>:
					</TD>	
					<TD CLASS="td_data_wrap text"COLSPAN=3><?php print $row['description'];?></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Incident / Closure Notes - actions and other information noted during Incident and when closing">Incident Notes</A>:
					</TD>	
					<TD CLASS="td_data_wrap text"COLSPAN=3><?php print $row['incident_notes'];?></TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>
<?php
				if($row['gold'] != 0) {
?>
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Gold Command");?>"><?php print get_text("Gold Command");?></A>:</TD>
					<TD CLASS="td_data text"><SPAN class='heading' style='width: 100%; display: block; text-align: center;'><?php print get_owner($row['gold']);?></SPAN>
						<TABLE style='width: 100%; background-color: gold;'>
							<TR>
								<TD class='td_label text text_blue'>Email 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Email 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['gold']])) {
										print $comm_arr[$row['gold']][7];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text_blue'>Location</TD>
<?php 							
								if($row['gold_loc'] != 0) {
?>
									<TD class='td_data text'><?php print get_loc_name($row['gold_loc']);?></TD>
<?php
									} else {
									$gold_loc_details = $row['gold_street'] . "<BR />" . $row['gold_city'] . " " . $row['gold_state'];
?>
									<TD class='td_data text'><?php print $gold_loc_details;?></TD>
<?php
									}
?>
							</TR>
						</TABLE>
					</TD>
				</TR>
<?php
				}
				if($row['silver'] != 0) {
?>
				<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Silver Command");?>"><?php print get_text("Silver Command");?></A>:</TD>
					<TD CLASS="td_data text"><SPAN class='heading' style='width: 100%; display: block; text-align: center;'><?php print get_owner($row['silver']);?></SPAN>
						<TABLE style='width: 100%; background-color: silver;'>
							<TR>
								<TD class='td_label text text_blue'>Email 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Email 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['silver']])) {
										print $comm_arr[$row['silver']][7];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Location</TD>
<?php 							
								if($row['silver_loc'] != 0) {
?>
									<TD class='td_data text'><?php print get_loc_name($row['silver_loc']);?></TD>
<?php
									} else {
									$silver_loc_details = $row['silver_street'] . "<BR />" . $row['silver_city'] . " " . $row['silver_state'];
?>
									<TD class='td_data text'><?php print $silver_loc_details;?></TD>
<?php
									}
?>
							</TR>
						</TABLE>
					</TD>
				</TR>
<?php
				}
				if($row['bronze'] != 0) {
?>
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Bronze Command");?>"><?php print get_text("Bronze Command");?></A>:</TD>
					<TD CLASS="td_data text"><SPAN class='heading' style='width: 100%; display: block; text-align: center;'><?php print get_owner($row['bronze']);?></SPAN>
						<TABLE style='width: 100%; background-color: #cd7f32;'>
							<TR>
								<TD class='td_label text text_blue'>Email 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Email 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['bronze']])) {
										print $comm_arr[$row['bronze']][7];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Location</TD>
<?php 							
								if($row['bronze_loc'] != 0) {
?>
									<TD class='td_data text'><?php print get_loc_name($row['bronze_loc']);?></TD>
<?php
									} else {
									$bronze_loc_details = $row['bronze_street'] . "<BR />" . $row['bronze_city'] . " " . $row['bronze_state'];
?>
									<TD class='td_data text'><?php print $bronze_loc_details;?></TD>
<?php
									}
?>
							</TR>
						</TABLE>				
					</TD>
				</TR>
<?php
				}
				if($row['level4'] != 0) {
?>
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 4 Command");?>"><?php print get_text("Level 4 Command");?></A>:</TD>
					<TD CLASS="td_data text"><SPAN class='heading' style='width: 100%; display: block; text-align: center;'><?php print get_owner($row['level4']);?></SPAN>
						<TABLE>
							<TR>
								<TD class='td_label text text_blue'>Email 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Email 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level4']])) {
										print $comm_arr[$row['level4']][7];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Location</TD>
<?php 							
								if($row['level4_loc'] != 0) {
?>
									<TD class='td_data text'><?php print get_loc_name($row['level4_loc']);?></TD>
<?php
									} else {
									$level4_loc_details = $row['level4_street'] . "<BR />" . $row['level4_city'] . " " . $row['level4_state'];
?>
									<TD class='td_data text'><?php print $level4_loc_details;?></TD>
<?php
									}
?>
							</TR>
						</TABLE>				
					</TD>
				</TR>
<?php
				}
				if($row['level5'] != 0) {
?>
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 5 Command");?>"><?php print get_text("Level 5 Command");?></A>:</TD>
					<TD CLASS="td_data text"><SPAN class='heading' style='width: 100%; display: block; text-align: center;'><?php print get_owner($row['level5']);?></SPAN>
						<TABLE>
							<TR>
								<TD class='td_label text text_blue'>Email 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Email 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level5']])) {
										print $comm_arr[$row['level5']][7];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Location</TD>
<?php 							
								if($row['level5_loc'] != 0) {
?>
									<TD class='td_data text'><?php print get_loc_name($row['level5_loc']);?></TD>
<?php
									} else {
									$level5_loc_details = $row['level5_street'] . "<BR />" . $row['level5_city'] . " " . $row['level5_state'];
?>
									<TD class='td_data text'><?php print $level5_loc_details;?></TD>
<?php
									}
?>
							</TR>
						</TABLE>				
					</TD>
				</TR>
<?php
				}
				if($row['level6'] != 0) {
?>
				<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 6 Command");?>"><?php print get_text("Level 6 Command");?></A>:</TD>
					<TD CLASS="td_data text"><SPAN class='heading' style='width: 100%; display: block; text-align: center;'><?php print get_owner($row['level6']);?></SPAN>
						<TABLE>
							<TR>
								<TD class='td_label text text_blue'>Email 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][4];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Email 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][5];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 1</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][6];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Phone 2</TD>
								<TD class='td_data text'>
<?php 
									if(isset($comm_arr[$row['level6']])) {
										print $comm_arr[$row['level6']][7];
										}
?>
								</TD>
							</TR>
							<TR>
								<TD class='td_label text text_blue'>Location</TD>
<?php 							
								if($row['level6_loc'] != 0) {
?>
									<TD class='td_data text'><?php print get_loc_name($row['level6_loc']);?></TD>
<?php
									} else {
									$level6_loc_details = $row['level6_street'] . "<BR />" . $row['level6_city'] . " " . $row['level6_state'];
?>
									<TD class='td_data text'><?php print $level6_loc_details;?></TD>
<?php
									}
?>
							</TR>
						</TABLE>				
					</TD>
				</TR>
<?php
				}
?>				
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>		

				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>	
			</TABLE>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: relative; top: 50px; z-index: 1;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='ed_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.to_edit_Form.submit();'><?php print get_text("Edit");?><BR /><IMG id='edit_img' SRC='./images/edit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
			<CENTER><SPAN CLASS='text_blue text text_bold' style='width: 100%; text-align: center;'><?php print get_variable('map_caption');?></SPAN></CENTER><BR />
			<DIV id='incs_heading' class='heading' style='text-align: center;'>Incidents to be managed as part of the Major Incident (click to view)</DIV>
			<DIV id= 'incs_table' style = 'max-height: 400px; border: 1px outset #707070; overflow-y: scroll;'>
				<TABLE style='width: 100%;'>
					<TR class='plain_listheader' style='width: 100%;'>
						<TH class='plain_listheader' style='text-align: left;'>Scope</TH>
						<TH class='plain_listheader' style='text-align: left;'>Opened</TH>
						<TH class='plain_listheader' style='text-align: left;'>Units Assigned</TH>
						<TH class='plain_listheader' style='text-align: left;'>Elapsed</TH>
					</TR>
<?php
						if(count($existing_incs) != 0) {
							$class = "even";
							foreach($existing_incs AS $val) {
								$query_inc = "SELECT *, 
									(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
									WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`) AS `units_assigned` 	
									FROM `$GLOBALS[mysql_prefix]ticket` WHERE `$GLOBALS[mysql_prefix]ticket`.`id` = " . $val;
								$result_inc = mysql_query($query_inc) or do_error($query_inc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								$class = "even";
								$row_inc = stripslashes_deep(mysql_fetch_assoc($result_inc));
								$the_id = $row_inc['id'];
								$elapsed = get_elapsed_time($row_inc);
								print "<TR class='" . $class . "'  style='width: 100%;' onClick='do_popup(" . $the_id . ");'>";
								print "<TD class='plain_list'>" . $row_inc['scope'] . "</TD>";
								print "<TD class='plain_list'>" . format_date_2($row_inc['problemstart']) . "</TD>";
								print "<TD class='plain_list' style='text-align: left;'>" . $row_inc['units_assigned'] . "</TD>";
								print "<TD class='plain_list'>" . $elapsed . "</TD>";										
								print "</TR>";
								$class = ($class == "even") ? "odd" : "even";
								}
							} else {
							print "<TR class='plain_list' style='width: 100%;'><TD COLSPAN = 99 style='text-align: center;'>No Incidents set</TD></TR>";
							}										
?>
				</TABLE>
			</DIV>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, $row['id']);

?>
<FORM NAME='can_Form' METHOD="post" ACTION = "maj_inc.php"></FORM>
<FORM NAME="to_edit_Form" METHOD="post" ACTION = "maj_inc.php?edit=true&id=<?php print $id; ?>"></FORM>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
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
mapWidth = viewportwidth * .40;
mapHeight = viewportheight * .55;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('viewform').style.width = colwidth + "px";
$('incs_table').style.width = mapWidth + "px";
$('incs_heading').style.width = mapWidth + "px";
set_fontsizes(viewportwidth, "fullscreen");
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
init_map(1, <?php print $lat;?>, <?php print $lng;?>, "", 13, theLocale, useOSMAP, "tr");
map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);
var bounds = map.getBounds();	
var zoom = map.getZoom();

<?php
do_kml();
?>
</SCRIPT>
<?php
if((is_float($gold_lat) && $gold_lat != "" && $gold_lat != NULL) && ($gold_lng != "" && $gold_lng != NULL)) {
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
	b_lmarker.addTo(map);
</SCRIPT>
<?php
	}
	
if(count($existing_incs) != 0) {
	foreach($existing_incs AS $val) {
		$query_tick = "SELECT *	FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $val . " ORDER BY `id` ASC";
		$result_tick = mysql_query($query_tick) or do_error($query_tick, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$mi_num_tick = mysql_num_rows($result_tick);
		while ($row_tick = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
			$tickLat = floatval($row_tick['lat']);
			$tickLng = floatval($row_tick['lng']);
?>
<SCRIPT>
			var marker = createTicMarker(<?php print $tickLat;?>, <?php print $tickLng;?>, "Ticket: <?php print $row_tick['scope'];?><BR />Major Incident: <?php print $row['name'];?>", <?php print $row_tick['severity'];?>, <?php print $row_tick['id'];?>, <?php print $row['id'];?>, "<?php print $row_tick['scope'];?>");
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
				$respLat = floatval($row_resp['resp_lat']);
				$respLng = floatval($row_resp['resp_lng']);
?>
<SCRIPT>
				var rmarker = createRespMarker(<?php print $respLat;?>, <?php print $respLng;?>, <?php print $row_resp['resp_id'];?>, <?php print $row['id'];?>, "<?php print $row_resp['resp_handle'];?>")
				rmarker.addTo(map);
</SCRIPT>
<?php
				}
			}
		}
	}
if($row['boundary'] > 0) {
	$theBound = get_markup($row['boundary']);
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

