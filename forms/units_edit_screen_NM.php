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
print do_calls();		// call signs to JS array for validation

function can_do_dispatch($the_row) {
	if (intval($the_row['multi'])==1) return TRUE;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = {$the_row['id']}";	// all dispatches this unit
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_temp = stripslashes_deep(mysql_fetch_array($result_temp))) {		// check any open runs this unit
		if (!(is_date($row_temp['clear']))) { 			// if  clear is empty, then NOT dispatch-able
			unset ($result_temp, $row_temp); 
			return FALSE;
			}
		}		// end while ($row_temp ...)
	unset ($result_temp, $row_temp); 
	return TRUE;					// none found, can dispatch
	}		// end function can do_dispatch()
?>
<SCRIPT>
window.onresize=function(){set_size();}

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
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
			
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
	if (theForm.frm_remove) {
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
		}
	}				// end function validate(theForm)

function any_track(theForm) {
	return (theForm.frm_track_disp.selectedIndex > 0);
	}

var track_captions = ["", "Callsign", "Device key", "Userid ", "Userid ", "Badge", "Device", "Userid", "Automatic"];	//	9/6/13
function do_tracking(theForm, theVal) {							// 7/10/09, 7/24/09 added specific code to switch off unselected,
	theForm.frm_aprs.value=theForm.frm_instam.value=theForm.frm_locatea.value=theForm.frm_gtrack.value=theForm.frm_glat.value=theForm.frm_ogts.value=theForm.frm_t_tracker.value=theForm.frm_mob_tracker.value=theForm.frm_xastir_tracker.value=0;		//	 9/6/13
	switch(parseInt(theVal)) {
		case <?php print $GLOBALS['TRACK_NONE'];?>:		 break;
		case <?php print $GLOBALS['TRACK_APRS'];?>:		 theForm.frm_aprs.value=1;	 break;
		case <?php print $GLOBALS['TRACK_INSTAM'];?>:	 theForm.frm_instam.value=1;	 break;
		case <?php print $GLOBALS['TRACK_LOCATEA'];?>:	 theForm.frm_locatea.value=1; break;
		case <?php print $GLOBALS['TRACK_GTRACK'];?>:	 theForm.frm_gtrack.value=1;  break;
		case <?php print $GLOBALS['TRACK_GLAT'];?>:		 theForm.frm_glat.value=1;	 break;
		case <?php print $GLOBALS['TRACK_T_TRACKER'];?>:	theForm.frm_t_tracker.value=1;	break;
		case <?php print $GLOBALS['TRACK_OGTS'];?>:		 theForm.frm_ogts.value=1;	 break;
		case <?php print $GLOBALS['TRACK_MOBILE'];?>:	 theForm.frm_mob_tracker.value=1;	 break;	//	9/6/13
		case <?php print $GLOBALS['TRACK_XASTIR'];?>:	 theForm.frm_xastir_tracker.value=1;	 break;	//	1/30/14
		default:  alert("error <?php print __LINE__;?>");
		}		// end switch()
	}				// end function do tracking()		

</SCRIPT>
<?php

$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`= " . $id;
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= mysql_fetch_array($result);
$track_type = get_remote_type ($row) ;			// 7/6/11
$is_mobile = (($row['mobile']==1) && (!(empty($row['callsign']))));		// 1/27/09, 3/15/10

$lat = (($row['lat'] == "") || ($row['lat'] == 0)) ? get_variable('def_lat') : $row['lat'];
$lng = (($row['lng'] == "") || ($row['lng'] == 0)) ? get_variable('def_lng') : $row['lng'];

$type_checks = array ("", "", "", "", "");
$type_checks[$row['type']] = " checked";
$mob_checked = (($row['mobile']==1))? " CHECKED" : "" ;				// 1/24/09
$multi_checked = (($row['multi']==1))? " CHECKED" : "" ;				// 1/24/09
$direcs_checked = (($row['direcs']==1))? " CHECKED" : "" ;			// 3/11/09

?>
<SCRIPT>
function track_reset(the_Form) {		// reset to original as-loaded values
	the_Form.frm_aprs.value = <?php echo $row['aprs'];?>;
	the_Form.frm_instam.value = <?php echo $row['instam'];?>;
	the_Form.frm_locatea.value = <?php echo $row['locatea'];?>;
	the_Form.frm_gtrack.value = <?php echo $row['gtrack'];?>;
	the_Form.frm_glat.value = <?php echo $row['glat'];?>;
	the_Form.frm_ogts.value = <?php echo $row['ogts'];?>;
	the_Form.frm_t_tracker.value = <?php echo $row['t_tracker'];?>;
	the_Form.frm_mob_tracker.value = <?php echo $row['mob_tracker'];?>;				//	9/6/13
	the_Form.frm_xastir_tracker.value = <?php echo $row['xastir_tracker'];?>;				//	9/6/13
	}		// end function track reset()
	
var track_captions = ["", "Callsign&nbsp;&raquo;", "Device key&nbsp;&raquo;", "Userid&nbsp;&raquo;", "Userid&nbsp;&raquo;", "Badge&nbsp;&raquo;", "Device&nbsp;&raquo;", "Userid&nbsp;&raquo;","Automatic&nbsp;&raquo;","Callsign&nbsp;&raquo;"];	//	 9/6/13
</SCRIPT>
</HEAD>
<BODY onLoad='set_size();'>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id='outer' style='position: absolute; left: 0px;'>
		<DIV id='leftcol' style='position: absolute; left: 10px;'>
			<A NAME='top'>
			<FORM METHOD="POST" NAME="res_edit_Form" ENCTYPE="multipart/form-data" ACTION="units.php?goedit=true">
			<TABLE BORDER=0 ID='editform'>
				<TR>
					<TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
						<FONT SIZE=-1>(mouseover caption for help information)</FONT><DIV id = 'fence_flag'><BR /><BR /></DIV>
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>	
				<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Roster User">Roster User</A></TD>	<!-- 9/6/13 -->
					<TD COLSPAN=3 style='text-align: left; vertical-align: top;'><?php print get_roster($row['roster_user']);?><DIV id='user_details' style='width: 300px; vertical-align: top; display: none; font-size: 1.3em; word-wrap: normal;'><?php print get_user_details($row['roster_user']);?></DIV></TD></TR>	
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Unit Name - enter, well, the name!">Name</A>:<font color='red' size='-1'>*</font>
					</TD>			
					<TD COLSPAN=3><INPUT MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Handle - local rules, could be callsign or badge number, generally for radio comms use">Handle</A>: &nbsp;<FONT COLOR='red' size='-1'>*</FONT>&nbsp;
					</TD>
					<TD COLSPAN=3><INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" />
						<SPAN STYLE = 'margin-left:30px' CLASS="td_label"> Icon: </SPAN>&nbsp;<FONT COLOR='red' size='-1'>*</FONT>&nbsp;<INPUT TYPE = 'text' NAME = 'frm_icon_str' SIZE = 3 MAXLENGTH=3 VALUE='<?php print $row['icon_str'] ;?>'>
					</TD>
				</TR>
<?php
				if(get_num_groups()) {
					if((is_super())&& (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>			
						<TR CLASS='odd' VALIGN='top'>
							<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>:
								<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
							<TD>
<?php			
							$alloc_groups = implode(',', get_allocates(2, $id));
?>
							<?php print get_sub_group_butts(($_SESSION['user_id']), 2, $id);?>
							</TD>
						</TR>
<?php	
						} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>
						<TR CLASS='odd' VALIGN='top'>
							<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>:
								<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
							<TD>
<?php
							$alloc_groups = implode(',', get_allocates(2, $id));
?>
							<?php print get_sub_group_butts(($_SESSION['user_id']), 2, $id);?>
							</TD>
						</TR>
<?php
						} else {
?>
						<TR CLASS='odd' VALIGN='top'>
							<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Shows Regions that Responder is allocated to"><?php print get_text("Regions");?></A>:
							<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
							<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
							<TD>
<?php
							$alloc_groups = implode(',', get_allocates(3, $id));
?>
							<?php print get_sub_group_butts_readonly(($_SESSION['user_id']), 2, $id);?>
							</TD>
						</TR>
<?php						
						}			
					} else {
?>
					<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
					}
?>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>
				<TR CLASS = "even" VALIGN='middle'>
					<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Unit Type - Select from pulldown menu">Type</A>: <font color='red' size='-1'>*</font></TD>
					<TD ALIGN='left'><FONT SIZE='-2'>
						<SELECT NAME='frm_type'>
<?php
							foreach ($u_types as $key => $value) {								// 1/9/09
							$temp = $value; 												// 2-element array
							$sel = ($row['type']==$key)? " SELECTED": "";					// 9/11/09
							print "\t\t\t\t<OPTION VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
							}
?>
						</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label" HREF="#" TITLE="Check if Unit is mobile">Mobile</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" <?php print $mob_checked; ?> />&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label" HREF="#" TITLE="Check if Unit can be dispatched to multiple incidents - e.g., ACO">Multiple</A>  &raquo;<INPUT TYPE="checkbox" NAME="frm_multi_disp" <?php print $multi_checked; ?> />&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label" HREF="#" TITLE="Check if directions are to be shown on dispatch - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" <?php print $direcs_checked; ?> />
					</TD>
				</TR>
				<TR CLASS = "odd" VALIGN='top'>
					<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Tracking Type - select from the pulldown menu - you must also fill in the callsign or tracking id which is used by the tracking provider to identify the unit - each unit should have a unique id.">Tracking</A>:&nbsp;</TD>
					<TD ALIGN='left'>

						<SELECT NAME='frm_track_disp' onChange = "do_tracking(this.form, this.options[this.selectedIndex].value);">
<?php
							$selects = array("", "", "", "", "", "", "", "", "", "");	//	9/6/13
							$selects[$track_type] = "SELECTED";

							print "<OPTION VALUE={$GLOBALS['TRACK_NONE']} 		{$selects[$GLOBALS['TRACK_NONE']]} > 	None </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_APRS']} 		{$selects[$GLOBALS['TRACK_APRS']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_APRS']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_INSTAM']} 	{$selects[$GLOBALS['TRACK_INSTAM']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_INSTAM']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_GTRACK']} 	{$selects[$GLOBALS['TRACK_GTRACK']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_GTRACK']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_LOCATEA']}	{$selects[$GLOBALS['TRACK_LOCATEA']]} > {$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_LOCATEA']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_GLAT']} 		{$selects[$GLOBALS['TRACK_GLAT']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_GLAT']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_OGTS']} 		{$selects[$GLOBALS['TRACK_OGTS']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_OGTS']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_T_TRACKER']} 	{$selects[$GLOBALS['TRACK_T_TRACKER']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_T_TRACKER']]} </OPTION>";	
							print "<OPTION VALUE={$GLOBALS['TRACK_MOBILE']} 	{$selects[$GLOBALS['TRACK_MOBILE']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_MOBILE']]} </OPTION>";
							print "<OPTION VALUE={$GLOBALS['TRACK_XASTIR']} 	{$selects[$GLOBALS['TRACK_XASTIR']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_XASTIR']]} </OPTION>";
?>
						</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<SCRIPT>				
						var track_info = "APRS:   callsign\nInstamapper:   Device key\nLocateA:   Userid\nGtrack:   Userid\nLatitude:   Badge\nOpenGTS:   Device\nMobile Tracker:    Automatic\nXastir:    Callsign\n";	//	9/6/13
</SCRIPT>
						<INPUT TYPE = 'button' onClick = alert(track_info) value="?">&nbsp;&raquo;&nbsp;
				
						<INPUT SIZE="<?php print $key_field_size;?>" MAXLENGTH="<?php print $key_field_size;?>" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'];?>" />
					</TD>
				</TR>
<?php			
				if(is_administrator()) {
?>
					<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
						<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets boundary used to ring-fence the area this unit is allowed in"><?php print get_text("Ringfence");?></A>:</TD>
						<TD>
							<SELECT NAME="frm_ringfence" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
								<OPTION VALUE=0>Select</OPTION>
<?php
								$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_rf` = 1 ORDER BY `line_name` ASC";		// 12/18/10
								$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
									$sel = ($row['ring_fence'] == $row_bound['id']) ? "SELECTED" : "";
									print "\t<OPTION VALUE='{$row_bound['id']}' {$sel}>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
									}
?>
							</SELECT>
						</TD>
					</TR>
					<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
						<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets exclusion zone for this unit"><?php print get_text("Exclusion Zone");?></A>:</TD>
						<TD>
							<SELECT NAME="frm_excl_zone" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
								<OPTION VALUE=0>Select</OPTION>
<?php
								$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_ex` = 1 ORDER BY `line_name` ASC";		// 12/18/10
								$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
									$sel = ($row['excl_zone'] == $row_bound['id']) ? "SELECTED" : "";
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
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>		
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Unit Status - Select from pulldown menu">Status</A>:&nbsp;
					</TD>
					<TD ALIGN='left'>
						<SELECT NAME="frm_un_status_id" onChange = "this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; document.res_edit_Form.frm_log_it.value='1'; document.res_edit_Form.frm_status_update.value='1';">
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";
							$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

							$the_grp = strval(rand());			//  force initial optgroup value
							$i = 0;
							while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
							if ($the_grp != $row_st['group']) {
								print ($i == 0)? "": "</OPTGROUP>\n";
								$the_grp = $row_st['group'];
								print "\t\t<OPTGROUP LABEL='$the_grp'>\n";
								}
							$sel = ($row['un_status_id']== $row_st['id'])? " SELECTED" : "";
							print "\t\t<OPTION VALUE=" . $row_st['id'] . $sel ." STYLE='background-color:{$row_st['bg_color']}; color:{$row_st['text_color']};'  >" . $row_st['status_val']. "</OPTION>\n";	// 3/15/10
							$i++;
							}
?>
						</SELECT>
<?php
						unset($result_st);
						$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id`=$id AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') ";
						$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

						$cbcount = mysql_affected_rows();				// count of incomplete assigns
						$dis_rmv = ($cbcount==0)? "": " DISABLED";		// allow/disallow removal
						$cbtext = ($cbcount==0)? "": "&nbsp;&nbsp;<FONT size=-2>(NA - calls in progress: " .$cbcount . " )</FONT>";
?>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="About unit status - information about particular status values for this unit">About Status</A>
					</TD>
					<TD>
						<INPUT SIZE="61" TYPE="text" NAME="frm_status_about" VALUE="<?php print $row['status_about'] ;?>"  MAXLENGTH="512">
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>	
				<TR CLASS='odd'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:
					</TD>
					<TD>
						<INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61">
					</TD>
				</TR> <!-- 7/5/10 -->
				<TR CLASS='even'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required">City</A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button>
					</TD>
					<TD>
						<INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;
						<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>">
					</TD>
				</TR>
<?php
				$query_fac	= "SELECT `f`.`id` AS `fac_id`, `lat`, `lng`, `type`, `f`.`name` AS `fac_name`, `handle` FROM `$GLOBALS[mysql_prefix]facilities` `f`
					LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
					ORDER BY `handle`";
				$result_fac	= mysql_query($query_fac) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				if (mysql_num_rows($result_fac) > 0) {
?>
					<TR CLASS = "even" VALIGN='middle'>
						<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Unit is located at the selected facility as a home base">Locate at Facility:&nbsp;</A></TD>
						<TD ALIGN='left'><FONT SIZE='-2'>
							<SELECT NAME='frm_facility_sel'>
<?php
							if($row['at_facility'] != 0) {
?>
								<OPTION VALUE=0>Select</OPTION>
<?php
								} else {
?>
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								}
								while ($row_fac = stripslashes_deep(mysql_fetch_assoc($result_fac))) {
									$temp = explode("/", $row_fac['fac_name']);
									$sel = ($row['at_facility'] == $row_fac['fac_id']) ? "SELECTED" : "";
									echo "\t\t<OPTION VALUE = {$row_fac['fac_id']} CLASS = '' {$sel}>{$temp[0]}</OPTION>\n";
									}
?>
							</SELECT>
						</TD>
					</TR>		
<?php		
					}			// end if ()
?>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Phone number">Phone</A>:&nbsp;
					</TD>
					<TD COLSPAN=3>
						<INPUT SIZE="12" MAXLENGTH="48" TYPE="text" NAME="frm_phone" VALUE="<?php print $row['phone'] ;?>" />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Unit Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_descr" COLS=56 ROWS=2><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Unit Capability - training, equipment on board etc">Capability</A>:&nbsp; 
					</TD>										
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_capab" COLS=56 ROWS=2><?php print $row['capab'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Unit Contact name">Contact Name</A>:&nbsp;
					</TD>	
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Contact via - for email to unit this must be a valid email address or email to SMS address">Contact Via</A>:&nbsp;
					</TD>	
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_contact_via" VALUE="<?php print $row['contact_via'] ;?>" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="<?php get_provider_name(get_msg_variable('smsg_provider'));?> ID - This is for <?php get_provider_name(get_msg_variable('smsg_provider'));?> Integration and is the ID used by <?php get_provider_name(get_msg_variable('smsg_provider'));?> to send SMS messages"><?php get_provider_name(get_msg_variable('smsg_provider'));?> ID</A>:&nbsp;
					</TD>	
					<TD COLSPAN=3>
						<INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_smsg_id" VALUE="<?php print $row['smsg_id'] ;?>" />
					</TD>
				</TR>
<?php
				$map_capt = (!$is_mobile)? 	"<BR /><BR /><CENTER><B>Click Map to revise unit location</B>" : "";
				$lock_butt = (!$is_mobile)? "<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>" : "" ;
				$usng_link = (!$is_mobile)? "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>USNG:</SPAN>": "USNG:";
				$osgb_link = (!$is_mobile)? "<SPAN ID = 'osgb_link'>OSGB:</SPAN>": "OSGB:";		
				$utm_link = (!$is_mobile)? "<SPAN ID = 'utm_link'>UTM:</SPAN>": "UTM:";				
?>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<SPAN onClick = 'javascript: do_coords(document.res_edit_Form.frm_lat.value ,document.res_edit_Form.frm_lng.value  )' ><A CLASS="td_label" HREF="#" TITLE="Latitude and Longitude - set from map click">
						Lat/Lng</A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;<?php print $lock_butt;?>
					</TD>
					<TD COLSPAN=3>
						<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($row['lat']);?>" SIZE=11 disabled />&nbsp;
						<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($row['lng']);?>" SIZE=11 disabled />&nbsp;

<?php
						$locale = get_variable('locale');	// 08/03/09
						switch($locale) { 
							case "0":
								print $usng_link;
?>
								<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled />
<?php 	
								break;

							case "1":
								print $osgb_link;
?> 								
								<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoOSGB($row['lat'], $row['lng']) ;?>" SIZE=19 disabled />
<?php 
								break;

							case "2":
								print $utm_link;
								$utmcoords = $row['lat'] . "," . $row['lng'];
?> 								
								<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUTM($utmcoords);?>" SIZE=19 disabled />
<?php 
								break;		

							default:
							print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				

							}
?>
					</TD>
				</TR>
<?php
				if (!(empty($row['lat']))) {
?>
					<TR CLASS='even' VALIGN='baseline'>
						<TD CLASS='td_label'>
							<A CLASS='td_label' HREF='#' TITLE='Clear from map'>Clear position</A>:&nbsp;
						</TD>
						<TD>
							<INPUT TYPE='checkbox' NAME='frm_clr_pos'/>
						</TD>
					</TR>
<?php
					} else {
?>
						
							<INPUT TYPE='hidden' NAME='frm_clr_pos' VALUE='' />
<?php
					}
?>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<TR CLASS="odd" VALIGN='baseline'>
					<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete unit from system - disallowed if unit is assigned to any calls.">Remove Unit</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
					<?php print $cbtext; ?>
					</TD>
				</TR>
<?php
$tofac = (is_guest())? "" : "<A id='tofac_" . $row['id'] . "' CLASS='plain' style='float: none; color: #000000; width: 100px; display: inline-block;' HREF='fac_routes_nm.php?stage=1&id=" . $row['id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">To Facility</A>";
$todisp = ((is_guest()) || (!(can_do_dispatch($row))))?					"" : "<A id='disp_" . $row['id'] . "' CLASS='plain' style='float: none; color: #000000; width: 100px; display: inline-block;' HREF='{$_SESSION['unitsfile']}?func=responder&view=true&disp=true&id=" . $row['id'] . "' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">Dispatch</A>";
?>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
						<SPAN id='reset_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='track_reset(this.form); map_reset();'>Reset</SPAN>
						<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate(document.res_edit_Form);'>Submit</SPAN>
						<?php print $todisp;?>
						<?php print $tofac;?>
					</TD>
				</TR>
			</TABLE>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
			<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
			<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
			<INPUT TYPE="hidden" NAME = "frm_mobile" VALUE=<?php print $row['mobile'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_multi" VALUE=<?php print $row['multi'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_aprs" VALUE=<?php print $row['aprs'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_instam" VALUE=<?php print $row['instam'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_locatea" VALUE=<?php print $row['locatea'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_gtrack" VALUE=<?php print $row['gtrack'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_glat" VALUE=<?php print $row['glat'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_t_tracker" VALUE=<?php print $row['t_tracker'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_ogts" VALUE=<?php print $row['ogts'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_mob_tracker" VALUE=<?php print $row['mob_tracker'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_xastir_tracker" VALUE=<?php print $row['xastir_tracker'] ;?> />
			<INPUT TYPE="hidden" NAME = "frm_direcs" VALUE=<?php print $row['direcs'] ;?> />
			<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">
			<INPUT TYPE="hidden" NAME = "frm_status_updated" VALUE="<?php print $row['status_updated'] ;?>" />	
			<INPUT TYPE="hidden" NAME = "frm_status_update" VALUE=0 />		
			</FORM>
		</DIV>
		<DIV id='rightcol' style='position: absolute; right: 170px; z-index: 1;'>
<SCRIPT>
			var controlsHTML = "<TABLE id='controlstable' ALIGN='center'>";
			controlsHTML += "<SPAN class='heading' style='width: 100%; text-align: center; display: inline-block;'>Map Controls</SPAN></BR>";
			controlsHTML +=	"<TR class='even'><TD><CENTER><TABLE ID='buttons_sh' style='display: <?php print $show_controls;?>;'>";
			controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR></TABLE></CENTER></TD></TR></TABLE>";
</SCRIPT>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, $row['id'], 0, 0);
print do_calls($id);					// generate JS calls array
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "units.php"></FORM>

<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
<SCRIPT>
var latLng;
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = <?php print get_variable('map_height');?>+20;
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
$('controls').innerHTML = controlsHTML;
</SCRIPT>
</BODY>
</HTML>
<?php
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10 Added for add on modules
	$handle=$row['handle'];
	get_modules('res_edit_Form');
	}
exit();
