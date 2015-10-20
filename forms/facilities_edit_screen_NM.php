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

window.onload = function(){set_size();}

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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .55;
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
		document.forms['res_add_Form'].elements['frm_opening_hours[0][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "monday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[0][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "tuesday") && ($('tuesday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[1][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "tuesday") && (!($(id).tuesday))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[1][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "wednesday") && ($('wednesday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[2][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "wednesday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[2][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "thursday") && ($('thursday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[3][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "thursday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[3][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "friday") && ($('friday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[4][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "friday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[4][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "saturday") && ($('saturday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[5][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "saturday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[5][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].style.backgroundColor = "#CECECE";		
		} else if((id == "sunday") && ($('sunday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[6][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "sunday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[6][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].style.backgroundColor = "#CECECE";		
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
<BODY onLoad='set_size();'>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id='outer' style='position: absolute; left: 0px;'>
		<DIV id='leftcol' style='position: absolute; left: 10px;'>
			<A NAME='top'>		<!-- 11/11/09 -->
			<FORM METHOD="POST" NAME= "res_edit_Form" ENCTYPE="multipart/form-data" ACTION="facilities.php?goedit=true"> <!-- 7/9/09 -->
			<TABLE BORDER=0 ID='editform'>
				<TR>
					<TD ALIGN='center' COLSPAN='2'>
						<FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Facility '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
						<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker">Name</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>			
					<TD COLSPAN=3>
						<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility">Handle</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			
					<TD COLSPAN=3><INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" />
						<SPAN STYLE = "margin-left:40px;" CLASS="td_label"  TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<font color='red' size='-1'>*</font>
						<INPUT TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="<?php print $row['icon_str'];?>" />			
					</TD>
				</TR>
<?php
				if(get_num_groups()) {
					if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>			
						<TR CLASS='even' VALIGN='top'>
							<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
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
						<TR CLASS='even' VALIGN='top'>;
							<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
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
							<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Regions');?></A>:
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
						<TD CLASS="td_label">
							<A CLASS="td_label" HREF="#"  TITLE="Sets Facility Boundary"><?php print get_text("Boundary");?></A>:
						</TD>
						<TD>
							<SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">
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
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility Type - Select from pulldown menu">Type</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>
					<TD ALIGN='left'>
						<FONT SIZE='-2'>
						<SELECT NAME='frm_type'>
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
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility Status - Select from pulldown menu">Status</A>:&nbsp;
					</TD>
					<TD ALIGN='left'>
						<SELECT NAME="frm_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
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
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="About Facility status - information about particular status values for this facility">About Status</A>
					</TD>
					<TD>
						<INPUT SIZE="61" TYPE="text" NAME="frm_status_about" VALUE="<?php print $row['status_about'] ;?>" MAXLENGTH="512">
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:
					</TD>
					<TD>
						<INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61">
					</TD>
				</TR>
				<TR CLASS='odd'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button>
					</TD>
					<TD>
						<INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;
						<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>">
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?></A>&nbsp;</TD>
					<TD COLSPAN=3 >		<!-- 	6/4/2013 -->
						<SPAN  CLASS = "td_label" STYLE = "margin-left:20px;">Available: </SPAN><INPUT SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_a" VALUE="<?php print $row['beds_a'];?>" />			
						<SPAN  CLASS = "td_label" STYLE = "margin-left:20px;">Occupied: </SPAN><INPUT SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_o" VALUE="<?php print $row['beds_o'];?>" />			
					</TD>
				</TR>
				<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;</TD>
					<TD COLSPAN=3 >
						<TEXTAREA NAME="frm_beds_info" COLS=60 ROWS=2><?php print $row['beds_info'];?></TEXTAREA>			
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_capab" COLS=60 ROWS=2><?php print $row['capab'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility main contact name">Contact name</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="<?php print $row['contact_email'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility contact phone number - main contact phone number">Contact phone</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="<?php print $row['contact_phone'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility security contact">Security contact</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="<?php print $row['security_contact'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility security contact email">Security email</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="<?php print $row['security_email'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility security contact phone number">Security phone</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="<?php print $row['security_phone'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;
					</TD>
<?php
					$opening_arr_serial = base64_decode($row['opening_hours']);
					$opening_arr = unserialize($opening_arr_serial);
					$checked = array();
					$thestart = array();
					$theend = array();
					$y = 0;
					foreach($opening_arr as $val) {
						if($val[0] == "on") {
							$checked[$y] = "CHECKED";
							} else {
							$checked[$y] = "";
							}
						$thestart[$y] = $val[1];
						$theend[$y] = $val[2];
						$y++;
						}
?>
					<TD COLSPAN=3>
						<TABLE style='width: 100%;'>
							<TR>
								<TH style='text-align: left;'><A CLASS="td_label" HREF="#" TITLE="Day of the Week"><?php print get_text("Day"); ?></A></TH>
								<TH style='text-align: left;'><A CLASS="td_label" HREF="#" TITLE="Opening Time"><?php print get_text("Opening"); ?></A></TH>
								<TH style='text-align: left;'><A CLASS="td_label" HREF="#" TITLE="Opening Time"><?php print get_text("Closing"); ?></A></TH>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "monday" TYPE = "CHECKBOX" NAME="frm_opening_hours[0][0]" <?php print $checked[0];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Monday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[0][1]" VALUE="<?php print $thestart[0];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[0][2]" VALUE="<?php print $theend[0];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "tuesday" TYPE = "CHECKBOX" NAME="frm_opening_hours[1][0]" <?php print $checked[1];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Tuesday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[1][1]" VALUE="<?php print $thestart[1];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[1][2]" VALUE="<?php print $theend[1];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "wednesday" TYPE = "CHECKBOX" NAME="frm_opening_hours[2][0]" <?php print $checked[2];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Wednesday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[2][1]" VALUE="<?php print $thestart[2];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[2][2]" VALUE="<?php print $theend[2];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "thursday" TYPE = "CHECKBOX" NAME="frm_opening_hours[3][0]" <?php print $checked[3];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Thursday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[3][1]" VALUE="<?php print $thestart[3];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[3][2]" VALUE="<?php print $theend[3];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "friday" TYPE = "CHECKBOX" NAME="frm_opening_hours[4][0]" <?php print $checked[4];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Friday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[4][1]" VALUE="<?php print $thestart[4];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[4][2]" VALUE="<?php print $theend[4];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "saturday" TYPE = "CHECKBOX" NAME="frm_opening_hours[5][0]" <?php print $checked[5];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Saturday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[5][1]" VALUE="<?php print $thestart[5];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[5][2]" VALUE="<?php print $theend[5];?>" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID = "sunday" TYPE = "CHECKBOX" NAME="frm_opening_hours[6][0]" <?php print $checked[6];?> onClick = 'check_days(this.id);'><SPAN CLASS='td_label'>Sunday</SPAN></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[6][1]" VALUE="<?php print $thestart[6];?>" /></TD>
								<TD style='text-align: left;'><INPUT SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[6][2]" VALUE="<?php print $theend[6];?>" /></TD>
							</TR>
						</TABLE>
					</TD>			
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_access_rules" COLS=60 ROWS=5><?php print $row['access_rules'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility security requirements - e.g. phone security first, visitors must be security cleared etc.">Security reqs</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_security_reqs" COLS=60 ROWS=5><?php print $row['security_reqs'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility contact primary pager number">Pager Primary</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="<?php print $row['pager_p'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Facility contact secondary pager number">Pager Secondary</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="<?php print $row['pager_s'] ;?>" />
					</TD>
				</TR>

<?php
				$map_capt = "<BR /><BR /><CENTER><B><FONT CLASS = 'normal_text'>Click Map to revise facility location</FONT></B>";
				$lock_butt = (!$is_mobile)? "<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>" : "" ;
				$usng_link = (!$is_mobile)? "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>{$usng}:</SPAN>": "{$usng}:";
				$osgb_link = (!$is_mobile)? "<SPAN ID = 'osgb_link'>{$osgb}:</SPAN>": "{$osgb}:";		
?>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<SPAN onClick = 'javascript: do_coords(document.res_edit_Form.frm_lat.value ,document.res_edit_Form.frm_lng.value  )' ><A CLASS="td_label" HREF="#" TITLE="Latitude and Longitude - set from map click">
						Lat/Lng</A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;<?php print $lock_butt;?>
					</TD>
					<TD COLSPAN=3>
						<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
						<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;

<?php

						$usng_val = LLtoUSNG($row['lat'], $row['lng']);
						$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
						$utm_val = toUTM("{$row['lat']}, {$row['lng']}");

						$locale = get_variable('locale');
						switch($locale) { 
							case "0":
?>
								&nbsp;USNG:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $usng_val;?>' SIZE=19 disabled />
<?php 	
								break;

							case "1":
?> 
								&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $osgb_val;?>' SIZE=19 disabled />
<?php 
								break;

								default:
?> 
								&nbsp;UTM:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $utm_val;?>' SIZE=19 disabled />
<?php 		
							}
?>
					</TD>
				</TR>
				<TR CLASS = 'even'>
					<TD>
<?php
						$mg_select = "<SELECT NAME='frm_notify_mailgroup'>";	//	8/28/13
						$mg_select .= "<OPTION VALUE=0>Select Mail List</OPTION>";	//	8/28/13
						$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";	//	8/28/13
						$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	//	8/28/13
						while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {	//	8/28/13
							$sel = ($row['notify_mailgroup'] == $row_mg['id']) ? "SELECTED" : "";
							$mg_select .= "\t<OPTION {$sel} VALUE='{$row_mg['id']}'>{$row_mg['name']} </OPTION>\n";
							}
						$mg_select .= "</SELECT>";
?>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Notify Facility with selected mail list"><?php print get_text("Notify Mail List"); ?></A>:&nbsp;
					</TD>
					<TD COLSPAN=3 ><?php print $mg_select;?></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Notify Facility with this email address"><?php print get_text("Notify Email Address"); ?></A>:&nbsp;
					</TD>
					<TD COLSPAN=3 >
						<INPUT SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_notify_email" VALUE="<?php print $row['notify_email'] ;?>" />
					</TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<TR CLASS="even" VALIGN='baseline'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Delete Facility from system">Remove Facility</A>:&nbsp;
					</TD>
					<TD>
						<INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
					</TD>
				</TR>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
						<SPAN id='reset_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='map_reset();'>Reset</SPAN>
						<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate(document.res_edit_Form);'>Submit</SPAN>
					</TD>
				</TR>
				<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
				<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
				<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
				<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
				<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">			
			</TABLE>
			</FORM>
		</DIV>
		<DIV id='rightcol' style='position: absolute; right: 170px; z-index: 1;'>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, $id, 0);
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php"></FORM>
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>	
		</BODY>
		</HTML>
<?php
		exit();
