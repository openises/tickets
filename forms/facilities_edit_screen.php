<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
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
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
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
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});
			
var colors = new Array ('odd', 'even');
var fields = ["name",
			"about",
			"location",
			"description",
			"beds_info",
			"capability",
			"contact_name",
			"contact_email",
			"contact_phone",
			"sec_contact",
			"sec_email",
			"sec_phone",
			"access_rules",
			"sec_reqs",
			"pager_prim",
			"pager_sec",
			"notify_email",
			"filename"];
var medfields = ["city",
				"handle",
				"grid",
				"type",
				"boundary",
				"status",
				"file"];
var smallfields = ["beds_o",
				"beds_a",
				"show_lat",
				"show_lng"];

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
	mapHeight = mapWidth;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95;
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('editform').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('map_legend').style.width = mapWidth + "px";
	$('icon_legend').style.width = mapWidth + "px";
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}
	load_exclusions();
	load_ringfences();
	load_basemarkup();
	load_groupbounds();
	set_fontsizes(viewportwidth, "fullscreen");
	map.invalidateSize();
	}

function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}

function validate(theForm) {
/* 		if (theForm.frm_remove) {
		if (theForm.frm_remove.checked) {
			var str = "Please confirm removing '" + theForm.frm_name.value + "'";
			if(confirm(str)) 	{
				theForm.submit();
				return true;}
			else 				{return false;}
			}
		}
	theForm.frm_mobile.value = (theForm.frm_mob_disp.checked)? 1:0;
	theForm.frm_multi.value =  (theForm.frm_multi_disp.checked)? 1:0;

	theForm.frm_direcs.value = (theForm.frm_direcs_disp.checked)? 1:0;
	var errmsg="";
	if (theForm.frm_name.value.trim()=="")													{errmsg+="Unit NAME is required.\n";}
	if (theForm.frm_handle.value.trim()=="")												{errmsg+="Unit HANDLE is required.\n";}
	if (theForm.frm_icon_str.value.trim()=="")												{errmsg+="Unit ICON is required.\n";}

	if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)					{errmsg+="Unit TYPE selection is required.\n";}
	if (any_track(theForm)){	//	9/6/13
		if (theForm.frm_callsign.value.trim()==""){
			if(theForm.frm_track_disp.selectedIndex == 8) {
				} else {
				errmsg+="License information is required with Tracking.\n";
				}
			}
		}
	else {
		if (!(theForm.frm_callsign.value.trim()==""))										{errmsg+="License information used ONLY with Tracking.\n";}
		}


	if (theForm.frm_un_status_id.options[theForm.frm_un_status_id.selectedIndex].value==0)	{errmsg+="Unit STATUS selection is required.\n";}
	
	if (theForm.frm_descr.value.trim()=="")													{errmsg+="Unit DESCRIPTION is required with Tracking.\n";}
	if ((!(theForm.frm_mob_disp.checked)) && (theForm.frm_lat.value.trim().length == 0)) 	{errmsg+="Map location is required for non-mobile units.\n";}
	
	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		theForm.submit();
		} */
	theForm.submit();
	}				// end function validate(theForm)

function any_track(theForm) {
	return (theForm.frm_track_disp.selectedIndex > 0);
	}		

function check_days(id) {
	if((id == "monday") && ($('monday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "monday") && (!($(id).checked))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[0][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "tuesday") && ($('tuesday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "tuesday") && (!($(id).tuesday))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[1][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "wednesday") && ($('wednesday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "wednesday") && (!($(id).checked))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[2][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "thursday") && ($('thursday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "thursday") && (!($(id).checked))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[3][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "friday") && ($('friday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "friday") && (!($(id).checked))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[4][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "saturday") && ($('saturday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "saturday") && (!($(id).checked))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[5][2]'].style.backgroundColor = "#CECECE";		
		} else if((id == "sunday") && ($('sunday').checked)) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][0]'].checked = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][1]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][2]'].readOnly  = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "sunday") && (!($(id).checked))) {
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][0]'].checked = false;
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][1]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][2]'].readOnly  = true;
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_edit_Form'].elements['frm_opening_hours[6][2]'].style.backgroundColor = "#CECECE";		
		} else {
		}
	}
</SCRIPT>
<?php
		$id = mysql_real_escape_string($_GET['id']);
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]facilities WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_assoc($result);
		$is_mobile = FALSE;

		$lat = $row['lat'];
		$lng = $row['lng'];
		$type = $row['type'];

		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$direcs_checked = (($row['direcs']==1))? " CHECKED" : "" ;
?>
</HEAD>
<BODY>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'>		<!-- 11/11/09 -->
			<FORM METHOD="POST" NAME= "res_edit_Form" ENCTYPE="multipart/form-data" ACTION="facilities.php?goedit=true"> <!-- 7/9/09 -->
			<TABLE BORDER=0 ID='editform'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
						<SPAN CLASS='text_green text_biggest'>&nbsp;Edit Facility '<?php print $row['name'];?>' data&nbsp;&nbsp;(#<?php print $id; ?>)</SPAN>
						<BR />
						<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>
					<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker">Name</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>			
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='name' MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility">Handle</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>			
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='handle' MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" />
						<SPAN STYLE = "margin-left:40px;" CLASS="td_label text"  TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<font color='red' size='-1'>*</font>
						<INPUT TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="<?php print $row['icon_str'];?>" />			
					</TD>
				</TR>
<?php
				if(get_num_groups()) {
					if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>			
						<TR CLASS='even' VALIGN='top'>
							<TD CLASS='td_label text'><A CLASS="td_label text" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
								<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
							</TD>
							<TD>
<?php			
								$alloc_groups = implode(',', get_allocates(3, $id));
								print get_sub_group_butts(($_SESSION['user_id']), 3, $id) ;
?>
							</TD>
						</TR>
<?php
						} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>
						<TR CLASS='even' VALIGN='top'>
							<TD CLASS='td_label text'><A CLASS="td_label text" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
								<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
							</TD>
							<TD>
<?php
								$alloc_groups = implode(',', get_allocates(3, $id));
								print get_sub_group_butts(($_SESSION['user_id']), 3, $id) ;
?>
							</TD>
						</TR>
<?php
						} else {
?>
						<TR CLASS='even' VALIGN='top'>
							<TD CLASS='td_label text'><A CLASS="td_label text" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Regions');?></A>:
								<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
							</TD>
							<TD>
<?php
								$alloc_groups = implode(',', get_allocates(3, $id));
								print get_sub_group_butts_readonly(($_SESSION['user_id']), 3, $id);
?>
							</TD>
						</TR>
<?php						
						}
					} else {
?>
					<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">
<?php
					}
		
				if(is_administrator()) {
?>
					<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
						<TD CLASS="td_label text">
							<A CLASS="td_label text" HREF="#"  TITLE="Sets Facility Boundary"><?php print get_text("Boundary");?></A>:
						</TD>
						<TD>&nbsp;</TD>
						<TD COLSPAN=2 CLASS='td_data text'>
							<SELECT id='boundary' NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">
								<OPTION VALUE=0>Select</OPTION>
<?php
								$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_f` = 1 ORDER BY `line_name` ASC";		// 12/18/10
								$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
									$sel = ($row['boundary'] == $row_bound['id']) ? "SELECTED" : "";
									print "\t<OPTION VALUE='{$row_bound['id']}' {$sel}>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
									}
?>
							</SELECT>
						</TD>
					</TR>
<?php
					}
?>					
				<TR class='spacer'>
					<TD class='spacer' COLSPAN='2'>&nbsp;</TD>
				</TR>
				<TR CLASS = "even" VALIGN='middle'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility Type - Select from menu">Type</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<SELECT id='type' NAME='frm_type'>
<?php
							foreach ($f_types as $key => $value) {
								$temp = $value; 												// 2-element array
								$sel = ($row['type']==$key)? " SELECTED": "";
								print "\t\t\t\t<OPTION VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
								}
?>
						</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label' HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;
						<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility Status - Select from pulldown menu">Status</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<SELECT id='status' NAME="frm_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";
							$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

							$the_grp = strval(rand());			//  force initial optgroup value
							$i = 0;
							while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
								if ($the_grp != $row_st['group']) {
									print ($i == 0)? "": "</OPTGROUP>\n";
									$the_grp = $row_st['group'];
									print "\t\t<OPTGROUP LABEL='$the_grp'>\n";
									}
								$sel = ($row['status_id']== $row_st['id'])? "SELECTED" : "";
								print "\t\t<OPTION VALUE={$row_st['id']} {$sel}>{$row_st['status_val']}</OPTION>\n";
								$i++;
								}
?>
						</SELECT>
<?php
						unset($result_st);

						$dis_rmv = " ENABLED";
?>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="About Facility status - information about particular status values for this facility">About Status</A>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='about' SIZE="61" TYPE="text" NAME="frm_status_about" VALUE="<?php print $row['status_about'] ;?>" MAXLENGTH="512">
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='location' SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61">
					</TD>
				</TR>
				<TR CLASS='odd'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:
					</TD>
					<TD><button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='city' SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label text" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;
						<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>">
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<TEXTAREA id='description' NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?></A>&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<SPAN  CLASS = "td_label text" STYLE = "margin-left:20px;">Available: </SPAN>
						<INPUT id='beds_a' SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_a" VALUE="<?php print $row['beds_a'];?>" />			
						<SPAN  CLASS = "td_label text" STYLE = "margin-left:20px;">Occupied: </SPAN>
						<INPUT id='beds_o' SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_o" VALUE="<?php print $row['beds_o'];?>" />			
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<TEXTAREA id='beds_info' NAME="frm_beds_info" COLS=60 ROWS=2><?php print $row['beds_info'];?></TEXTAREA>			
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<TEXTAREA id='capability' NAME="frm_capab" COLS=60 ROWS=2><?php print $row['capab'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility main contact name">Contact name</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='contact_name' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='contact_email' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="<?php print $row['contact_email'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility contact phone number - main contact phone number">Contact phone</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='contact_phone' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="<?php print $row['contact_phone'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility security contact">Security contact</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='sec_contact' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="<?php print $row['security_contact'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility security contact email">Security email</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='sec_email' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="<?php print $row['security_email'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility security contact phone number">Security phone</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='sec_phone' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="<?php print $row['security_phone'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;
					</TD>
<?php
					$opening_arr_serial = base64_decode($row['opening_hours']);
					$opening_arr = unserialize($opening_arr_serial);
					$checked = array();
					$thestart = array();
					$theend = array();
					$y = 0;
					foreach($opening_arr as $val) {
						if(array_key_exists(0, $val) && $val[0] == "on") {
							$checked[$y] = "CHECKED";
							} else {
							$checked[$y] = "";
							}
						$thestart[$y] = $val[1];
						$theend[$y] = $val[2];
						$y++;
						}
?>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<TABLE style='width: 100%;'>
							<TR>
								<TH style='text-align: left;'><A CLASS="td_label text" HREF="#" TITLE="Day of the Week"><?php print get_text("Day"); ?></A></TH>
								<TH style='text-align: left;'><A CLASS="td_label text" HREF="#" TITLE="Opening Time"><?php print get_text("Opening"); ?></A></TH>
								<TH style='text-align: left;'><A CLASS="td_label text" HREF="#" TITLE="Opening Time"><?php print get_text("Closing"); ?></A></TH>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "monday" TYPE = "CHECKBOX" NAME="frm_opening_hours[0][0]" <?php print $checked[0];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Monday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[0][1]" VALUE="<?php print $thestart[0];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[0][2]" VALUE="<?php print $theend[0];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "tuesday" TYPE = "CHECKBOX" NAME="frm_opening_hours[1][0]" <?php print $checked[1];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Tuesday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[1][1]" VALUE="<?php print $thestart[1];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[1][2]" VALUE="<?php print $theend[1];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "wednesday" TYPE = "CHECKBOX" NAME="frm_opening_hours[2][0]" <?php print $checked[2];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Wednesday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[2][1]" VALUE="<?php print $thestart[2];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[2][2]" VALUE="<?php print $theend[2];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "thursday" TYPE = "CHECKBOX" NAME="frm_opening_hours[3][0]" <?php print $checked[3];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Thursday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[3][1]" VALUE="<?php print $thestart[3];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[3][2]" VALUE="<?php print $theend[3];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "friday" TYPE = "CHECKBOX" NAME="frm_opening_hours[4][0]" <?php print $checked[4];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Friday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[4][1]" VALUE="<?php print $thestart[4];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[4][2]" VALUE="<?php print $theend[4];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "saturday" TYPE = "CHECKBOX" NAME="frm_opening_hours[5][0]" <?php print $checked[5];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Saturday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[5][1]" VALUE="<?php print $thestart[5];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[5][2]" VALUE="<?php print $theend[5];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "sunday" TYPE = "CHECKBOX" NAME="frm_opening_hours[6][0]" <?php print $checked[6];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Sunday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[6][1]" VALUE="<?php print $thestart[6];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[6][2]" VALUE="<?php print $theend[6];?>" /></TD>
							</TR>
						</TABLE>
					</TD>			
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<TEXTAREA id='access_rules' NAME="frm_access_rules" COLS=60 ROWS=5><?php print $row['access_rules'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility security requirements - e.g. phone security first, visitors must be security cleared etc.">Security reqs</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<TEXTAREA id='sec_reqs' NAME="frm_security_reqs" COLS=60 ROWS=5><?php print $row['security_reqs'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility contact primary pager number">Pager Primary</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='pager_prim' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="<?php print $row['pager_p'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Facility contact secondary pager number">Pager Secondary</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='pager_sec' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="<?php print $row['pager_s'] ;?>" />
					</TD>
				</TR>

<?php
				$map_capt = "<BR /><BR /><CENTER><B><FONT CLASS = 'normal_text'>Click Map to revise facility location</FONT></B>";
				$lock_butt = (!$is_mobile)? "<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>" : "" ;
				$usng_link = (!$is_mobile)? "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>{$usng}:</SPAN>": "{$usng}:";
				$osgb_link = (!$is_mobile)? "<SPAN ID = 'osgb_link'>{$osgb}:</SPAN>": "{$osgb}:";		
?>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<SPAN onClick = 'javascript: do_coords(document.res_edit_Form.frm_lat.value ,document.res_edit_Form.frm_lng.value  )' ><A CLASS="td_label text" HREF="#" TITLE="Latitude and Longitude - set from map click">
						Lat/Lng</A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;<?php print $lock_butt;?>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='show_lat' TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
						<INPUT id='show_lng' TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;
					</TD>
				</TR>
<?php

					$locale = get_variable('locale');	// 08/03/09
					switch($locale) {
						case "0":
							$label = $usng_link;
							$input = "<INPUT id='grid' TYPE='text' NAME='frm_ngs' VALUE='" . LLtoUSNG($row['lat'], $row['lng']) . "' SIZE=19 disabled />";
							break;
						
						case "1":
							$label = $osgb_link;
							$input = "<INPUT id='grid' TYPE='text' NAME='frm_ngs' VALUE='" . LLtoOSGB($row['lat'], $row['lng']) . "' SIZE=19 disabled />";
							break;

						case "2":
							$label = $utm_link;
							$input = "<INPUT id='grid' TYPE='text' NAME='frm_ngs' VALUE='" . toUTM($utmcoords) . "' SIZE=19 disabled />";
							break;
						
						default:
						print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
						}
?>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<?php print $label;?>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<?php print $input;?>
					</TD>
				</TR>
<?php
				$mg_select = "<SELECT id='mailgroup' NAME='frm_notify_mailgroup'>";	//	8/28/13
				$mg_select .= "<OPTION VALUE=0>Select Mail List</OPTION>";	//	8/28/13
				$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";	//	8/28/13
				$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	//	8/28/13
				while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {	//	8/28/13
					$sel = ($row['notify_mailgroup'] == $row_mg['id']) ? "SELECTED" : "";
					$mg_select .= "\t<OPTION {$sel} VALUE='{$row_mg['id']}'>{$row_mg['name']} </OPTION>\n";
					}
				$mg_select .= "</SELECT>";
?>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Notify Facility with selected mail list"><?php print get_text("Notify Mail List"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<?php print $mg_select;?>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Notify Facility with this email address"><?php print get_text("Notify Email Address"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT id='notify_email' SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_notify_email" VALUE="<?php print $row['notify_email'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Notify when?"><?php print get_text("Notify when"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
<?php
						$sel1 = ($row['notify_when'] == 1) ? "SELECTED" : "";
						$sel2 = ($row['notify_when'] == 2) ? "SELECTED" : "";
						$sel3 = ($row['notify_when'] == 3) ? "SELECTED" : "";
?>							
						<SELECT id='notify_when' NAME='frm_notify_when'>
							<OPTION VALUE=1 <?php print $sel1;?>>All</OPTION>
							<OPTION VALUE=2 <?php print $sel2;?>>Incident Open</OPTION>
							<OPTION VALUE=3 <?php print $sel3;?>>Incident Close</OPTION>
						</SELECT>					
					</TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<TR class='heading text'>
					<TD COLSPAN='4' class='heading text' style='text-align: center;'>File Upload</TD>
				</TR>
				<TR class='even'>
					<TD class='td_label text' style='text-align: left;'>Choose a file to upload:</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='file' NAME="frm_file" TYPE="file" />
					</TD>
				</TR>
				<TR class='odd'>
					<TD class='td_label text' style='text-align: left;'>File Name</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
					<INPUT id='filename' NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE=""></TD>
				</TR>
				<TR CLASS="even" VALIGN='baseline'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Delete Facility from system">Remove Facility</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
					</TD>
				</TR>
				<TR class='even'>
					<TD COLSPAN='4' ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
				<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
				<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
				<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
				<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">			
			</TABLE>
			</FORM>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='do_add_reset(document.res_edit_Form);'><?php print get_text("Reset");?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.res_edit_Form);'><?php print get_text("Submit");?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
			<DIV id= 'map_legend' style = "text-align: center;">
				<?php print $map_capt; ?>
			</DIV>
			<BR />
			<DIV ID='icon_legend' style='text-align: center;'>
				<SPAN><?php print get_text("Facilities Legend");?></SPAN>
				<BR /><BR />
				<SPAN style='text-align: center;'><?php print get_icon_legend();?></SPAN>
			</DIV>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, $id, 0);
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php"></FORM>
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
mapHeight = mapWidth;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = colwidth * .95;
inner_listwidth = listwidth *.9;
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
fieldwidth = colwidth * .6;
medfieldwidth = colwidth * .3;		
smallfieldwidth = colwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('editform').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('map_legend').style.width = mapWidth + "px";
$('icon_legend').style.width = mapWidth + "px";
for (var i = 0; i < fields.length; i++) {
	if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
	} 
for (var i = 0; i < medfields.length; i++) {
	if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
	}
for (var i = 0; i < smallfields.length; i++) {
	if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
	}
load_exclusions();
load_ringfences();
load_basemarkup();
load_groupbounds();
set_fontsizes(viewportwidth, "fullscreen");
var latLng;
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
var bounds = map.getBounds();	
var zoom = map.getZoom();
var doReverse = <?php print intval(get_variable('reverse_geo'));?>;
function onMapClick(e) {
	if(doReverse == 0) {return;}
	if(marker) {map.removeLayer(marker); }
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
    marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
    marker.addTo(map);
	newGetAddress(e.latlng, "e");
	};

map.on('click', onMapClick);
<?php
do_kml();
?>
</SCRIPT>		
		</BODY>
		</HTML>
<?php
		exit();
