<?php
error_reporting(E_ALL);
require_once($the_inc);
?>
<SCRIPT>
window.onresize=function(){set_size()};
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var listwidth;
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
			"filename"];
var medfields = ["type",
				"boundary",
				"gold",
				"silver",
				"bronze",
				"level4",
				"level5",
				"level6",
				"status"];
var smallfields = ["show_lat", "show_lng"];

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
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	leftcolwidth = viewportwidth * .42;
	rightcolwidth = viewportwidth * .42;
	listHeight = viewportheight * .7;
	listwidth = leftcolwidth;
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .2;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('rightcol').style.width = rightcolwidth + "px";
	$('incs_heading').style.width = colwidth + "px";
	$('incs_table').style.width = colwidth + "px";
	for (var i = 0; i < fields.length; i++) {
		$(fields[i]).style.width = fieldwidth + "px";
		} 
	for (var i = 0; i < medfields.length; i++) {
		$(medfields[i]).style.width = medfieldwidth + "px";
		}
	set_fontsizes(viewportwidth, "fullscreen");
	}

var sortby = '`date`';	//	10/23/12
var sort = "DESC";	//	10/23/12
var columns = "<?php print get_msg_variable('columns');?>";	//	10/23/12
var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	10/23/12
var thescreen = 'ticket';	//	10/23/12
var thelevel = '<?php print $the_level;?>';
var rmarkers = new Array();			//	Responder Markers array
var cmarkers = new Array();			//	conditions markers array

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

function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}
	
function collect(){				// constructs a string of id's for deletion
	var str = sep = "";
	for (i=0; i< document.del_Form.elements.length; i++) {
		if (document.del_Form.elements[i].type == 'checkbox' && (document.del_Form.elements[i].checked==true)) {
			str += (sep + document.del_Form.elements[i].name.substring(1));		// drop T
			sep = ",";
			}
		}
	document.del_Form.idstr.value=str;
	}

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
<BODY>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<A NAME='top'></A>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<FORM NAME= "mi_add_Form" ENCTYPE="multipart/form-data" METHOD="POST" ACTION="maj_inc.php?func=mi&goadd=true">
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<TABLE ID='addform' style='width: 100%;'>
			<TR CLASS='even'>
				<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
			</TR>
			<TR CLASS='even'>
				<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
					<SPAN CLASS='text_green text_biggest'>Add a <?php print get_text("Major Incident");?></SPAN>
					<BR />
					<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
					<BR />
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>	
			<TR CLASS = "even">
				<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Major Incident Name">Name</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
				<TD COLSPAN=3 >
					<INPUT ID='name' MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="" />
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Major Incident Name">Start Date/Time</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;
				</TD>
				<TD CLASS='td_data text' COLSPAN=3 >
					<?php print generate_date_dropdown('inc_startime', 0, FALSE);?>
				</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Major Incident Name">End Date/Time</A>:&nbsp;<input type="checkbox" name="end_but" onClick ="do_end(this.form);" />&nbsp;
				</TD>
				<TD CLASS='td_data text' COLSPAN=3 >
					<SPAN style = "visibility:hidden" ID = "enddate1"><?php print generate_date_dropdown('inc_endtime', 0, FALSE);?></SPAN>
				</TD>
			</TR>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Type of Major Incident"><?php print get_text("MI Type");?></A>:</TD>
				<TD CLASS='td_data text'>
					<SELECT ID='type' NAME="frm_type">	<!--  11/17/10 -->
						<OPTION VALUE=0>Select</OPTION>
<?php
						$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_types` ORDER BY `id` ASC";		// 12/18/10
						$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_types = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
							print "\t<OPTION VALUE='{$row_types['id']}'>{$row_types['name']}</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
				</TD>
			</TR>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_data text" CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Type of Major Incident"><?php print get_text("Status");?></A>:
				</TD>
				<TD>
					<SELECT id='status' NAME="frm_status">
						<OPTION VALUE="Open" SELECTED>Open</OPTION>
						<OPTION VALUE="Closed">Closed</OPTION>
					</SELECT>
				</TD>
			</TR>
			<TR CLASS='odd' VALIGN="top">
				<TD CLASS='td_label text'>
					<A CLASS="td_label text" HREF="#"  TITLE="Sets Boundary for this major incident"><?php print get_text("Boundary");?></A>:
				</TD>
				<TD CLASS='td_data text' COLSPAN='3'>
					<SELECT ID='boundary' NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` ORDER BY `line_name` ASC";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
							print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";
							}
?>
					</SELECT>&nbsp;
				</TD>
			</TR>			
				<TR CLASS='even' VALIGN="top">
					<TD CLASS='td_label text top'>
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Gold Command");?>"><?php print get_text("Gold Command");?></A>:
					</TD>
					<TD CLASS="td_data text"  COLSPAN='3'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='gold' NAME="frm_gold" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'gold_command_data'); showtheDiv('gold_location_data');">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_gold = stripslashes_deep(mysql_fetch_assoc($result))) {
									print "\t<OPTION VALUE='" . $row_gold['id'] . "'>" . $row_gold['user'] . " - " . $row_gold['name_f'] . " " . $row_gold['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
						<DIV id='gold_command_data'>
						</DIV>
						<DIV id='gold_location_data' style='display: none;'>
							<TABLE>
								<TR>
									<TD class='td_label text top'>Location&nbsp;&nbsp;</TD>
									<TD class='td_data text'><?php print get_building("frm_gold_loc");?></TD>
								</TR>
							</TABLE>
						</DIV>
					</TD>
				</TR>	
				<TR CLASS='odd' VALIGN="top">
					<TD CLASS='td_label text top'>
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Silver Command");?>"><?php print get_text("Silver Command");?></A>:
					</TD>
					<TD CLASS="td_data text"  COLSPAN='3'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='silver' NAME="frm_silver" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'silver_command_data'); showtheDiv('silver_location_data');">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_silver = stripslashes_deep(mysql_fetch_assoc($result))) {
									print "\t<OPTION VALUE='" . $row_silver['id'] . "'>" . $row_silver['user'] . " - " . $row_silver['name_f'] . " " . $row_silver['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
						<DIV id='silver_command_data'>
						</DIV>
						<DIV id='silver_location_data' style='display: none;'>
							<TABLE>
								<TR>
									<TD class='td_label text top'>Location&nbsp;&nbsp;</TD>
									<TD class='td_data text'><?php print get_building("frm_silver_loc");?></TD>
								</TR>
							</TABLE>
						</DIV>
					</TD>
				</TR>	
				<TR CLASS='even' VALIGN="top">
					<TD CLASS='td_label text top'>
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Bronze Command");?>"><?php print get_text("Bronze Command");?></A>:
					</TD>
					<TD CLASS="td_data text"  COLSPAN='3'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='bronze' NAME="frm_bronze" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'bronze_command_data'); showtheDiv('bronze_location_data');">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_bronze = stripslashes_deep(mysql_fetch_assoc($result))) {
									print "\t<OPTION VALUE='" . $row_bronze['id'] . "'>" . $row_bronze['user'] . " - " . $row_bronze['name_f'] . " " . $row_bronze['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
						<DIV id='bronze_command_data'>
						</DIV>
						<DIV id='bronze_location_data' style='display: none;'>
							<TABLE>
								<TR>
									<TD class='td_label text top'>Location&nbsp;&nbsp;</TD>
									<TD class='td_data text'><?php print get_building("frm_bronze_loc");?></TD>
								</TR>
							</TABLE>
						</DIV>
					</TD>
				</TR>	
				<TR CLASS='odd' VALIGN="top">
					<TD CLASS='td_label text top'>
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 4 Command");?>"><?php print get_text("Level 4 Command");?></A>:
					</TD>
					<TD CLASS="td_data text"  COLSPAN='3'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='level4' NAME="frm_level4" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level4_command_data'); showtheDiv('level4_location_data');">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_level4 = stripslashes_deep(mysql_fetch_assoc($result))) {
									print "\t<OPTION VALUE='" . $row_level4['id'] . "'>" . $row_level4['user'] . " - " . $row_level4['name_f'] . " " . $row_level4['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
						<DIV id='level4_command_data'>
						</DIV>
						<DIV id='level4_location_data' style='display: none;'>
							<TABLE>
								<TR>
									<TD class='td_label text top'>Location&nbsp;&nbsp;</TD>
									<TD class='td_data text'><?php print get_building("frm_level4_loc");?></TD>
								</TR>
							</TABLE>
						</DIV>
					</TD>
				</TR>	
				<TR CLASS='even' VALIGN="top">
					<TD CLASS='td_label text top'>
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 5 Command");?>"><?php print get_text("Level 5 Command");?></A>:
					</TD>
					<TD CLASS="td_data text" COLSPAN='3'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='level5' NAME="frm_level5" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level5_command_data'); showtheDiv('level5_location_data');">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_level5 = stripslashes_deep(mysql_fetch_assoc($result))) {
									print "\t<OPTION VALUE='" . $row_level5['id'] . "'>" . $row_level5['user'] . " - " . $row_level5['name_f'] . " " . $row_level5['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
						<DIV id='level5_command_data'>
						</DIV>
						<DIV id='level5_location_data' style='display: none;'>
							<TABLE>
								<TR>
									<TD class='td_label text top'>Location&nbsp;&nbsp;</TD>
									<TD class='td_data text'><?php print get_building("frm_level5_loc");?></TD>
								</TR>
							</TABLE>
						</DIV>
					</TD>
				</TR>	
				<TR CLASS='odd' VALIGN="top">
					<TD CLASS='td_label text top'>
						<A CLASS="td_label text" HREF="#"  TITLE="<?php print get_text("Level 6 Command");?>"><?php print get_text("Level 6 Command");?></A>:
					</TD>
					<TD CLASS="td_data text" COLSPAN='3'>
						<SPAN style='width: 100%; display: block;'>
							<SELECT id='level6' NAME="frm_level6" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'level6_command_data'); showtheDiv('level6_location_data');">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_level6 = stripslashes_deep(mysql_fetch_assoc($result))) {
									print "\t<OPTION VALUE='" . $row_level6['id'] . "'>" . $row_level6['user'] . " - " . $row_level6['name_f'] . " " . $row_level6['name_l'] . "</OPTION>\n";
									}
?>
							</SELECT>
						</SPAN>
						<DIV id='level6_command_data'>
						</DIV>
						<DIV id='level6_location_data' style='display: none;'>
							<TABLE>
								<TR>
									<TD class='td_label text top'>Location&nbsp;&nbsp;</TD>
									<TD class='td_data text'><?php print get_building("frm_level6_loc");?></TD>
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
						<A CLASS="td_label text" HREF="#" TITLE="Description - details about Major Incidents"><?php print get_text("Description");?></A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD COLSPAN=3 >
						<TEXTAREA id='description' NAME="frm_descr" COLS=56 ROWS=5></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Actions and Notes"><?php print get_text("Disposition");?></A>:&nbsp;
					</TD>	
					<TD COLSPAN=3 >
						<TEXTAREA id='notes' NAME="frm_notes" COLS=56 ROWS=5></TEXTAREA>
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
				<TR>
					<TD CLASS="td_data text" COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD>
				</TR>
				<TR>
					<TD COLSPAN=4 ALIGN='center'>&nbsp;</TD>
				</TR>
			</TABLE> <!-- end inner left -->
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'>Cancel<BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.reset_Form.submit();'>Reset<BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.mi_add_Form);'>Submit<BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='incs_heading' class='heading text' style='text-align: center;'>Incidents to be managed as part of the Major Incident</DIV>
			<DIV id= 'incs_table' style = 'max-height: 400px; border: 1px outset #707070; overflow-y: scroll;'>
				<TABLE>
					<TR CLASS = "even">
						<TD CLASS='td_data text'>
							<DIV>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_OPEN']}' OR `$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' ORDER BY `id` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row	= stripslashes_deep(mysql_fetch_assoc($result))) {
									$the_id = $row['id'];
									print "<input type='checkbox' name='frm_inc[]' value='" . $row['id'] . "'><SPAN class='link' onClick='do_popup(" . $the_id . ");'>" . $row['scope'] . "</SPAN><BR />";
									}
?>					
							</DIV>
						</TD>
					</TR>
				</TABLE>
			</DIV>
		</DIV>
		<DIV id='map_canvas' style='display: none;'></DIV>
		</FORM>
	<FORM NAME='can_Form' METHOD="post" ACTION = "maj_inc.php"></FORM>
	<FORM NAME='reset_Form' METHOD='get' ACTION='maj_inc.php'>
	<INPUT TYPE='hidden' NAME='func' VALUE='mi'>
	<INPUT TYPE='hidden' NAME='add' VALUE='true'>
	</FORM>
	</DIV>

	<!-- 2829 -->
	<A NAME="bottom" /> <!-- 5/3/10 -->
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
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
leftcolwidth = viewportwidth * .42;
rightcolwidth = viewportwidth * .42;
listHeight = viewportheight * .7;
listwidth = leftcolwidth;
fieldwidth = colwidth * .6;
medfieldwidth = colwidth * .3;		
smallfieldwidth = colwidth * .2;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('rightcol').style.width = rightcolwidth + "px";
$('incs_heading').style.width = colwidth + "px";
$('incs_table').style.width = colwidth + "px";
for (var i = 0; i < fields.length; i++) {
	if(!($(fields[i]))) {alert(fields[i]);}
	$(fields[i]).style.width = fieldwidth + "px";
	} 
for (var i = 0; i < medfields.length; i++) {
	if(!($(medfields[i]))) {alert(medfields[i]);}
	$(medfields[i]).style.width = medfieldwidth + "px";
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
	init_map(1, <?php print $def_lat;?>, <?php print $def_lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	var bounds = map.getBounds();	
	var zoom = map.getZoom();
<?php
	}
?>
</SCRIPT>
</BODY>
</HTML>