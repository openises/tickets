<?php
error_reporting(E_ALL);				// 9/13/08
$side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
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
window.onresize=function(){set_size()};

window.onload = function(){set_size();};

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
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = listwidth + "px";
	$('rightcol').style.width = listwidth + "px";
	$('incs_heading').style.width = colwidth + "px";
	$('incs_table').style.width = colwidth + "px";
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

</SCRIPT>
</HEAD>
<BODY>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
<A NAME='top'></A>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id='outer' style='position: absolute; left: 0px;'>
		<DIV id='leftcol' style='position: absolute; left: 10px;'>
			<A NAME='top'>	
			<FORM NAME= "mi_add_Form" ENCTYPE="multipart/form-data" METHOD="POST" ACTION="maj_inc.php?func=mi&goadd=true">
			<TABLE ID='addform' style='width: 100%;'>
				<TR>
					<TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>Add a <?php print get_text("Major Incident");?></FONT></FONT><BR /><BR />
					<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR />
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>	
			<TR CLASS = "even">
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Major Incident Name">Name</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
				<TD COLSPAN=3 ><INPUT MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="" /></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Major Incident Name">Start Date/Time</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
				<TD COLSPAN=3 ><?php print generate_date_dropdown('inc_startime', 0, FALSE);?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Major Incident Name">End Date/Time</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
				<TD COLSPAN=3 ><?php print generate_date_dropdown('inc_endtime', 0, FALSE);?></TD>
			</TR>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Type of Major Incident"><?php print get_text("MI Type");?></A>:</TD>
				<TD>
					<SELECT NAME="frm_type">	<!--  11/17/10 -->
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
			<TR CLASS='even' VALIGN="top">
				<TD CLASS='td_label'>
					<A CLASS="td_label" HREF="#"  TITLE="Sets Boundary for this major incident"><?php print get_text("Boundary");?></A>:
				</TD>
				<TD COLSPAN='3'>
					<SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">
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
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>	
			<TR CLASS='even' VALIGN="top">
				<TD CLASS='td_label'>
					<A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Gold Command");?>"><?php print get_text("Gold Command");?></A>:
				</TD>
				<TD COLSPAN='3'>
					<SPAN style='width: 100%; display: block;'>
						<SELECT NAME="frm_gold" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'gold_command_data');">
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
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'></TD>
							</TR>
						</TABLE>					
					</DIV>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
							<TD class='td_data'><?php print get_building("frm_gold_loc");?></TD>
						</TR>
					</TABLE>
				</TD>
			</TR>	
			<TR CLASS='odd' VALIGN="top">
				<TD CLASS='td_label'>
					<A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Silver Command");?>"><?php print get_text("Silver Command");?></A>:
				</TD>
				<TD COLSPAN='3'>
					<SPAN style='width: 100%; display: block;'>
						<SELECT NAME="frm_silver" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'silver_command_data');">
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
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'></TD>
							</TR>
						</TABLE>						
					</DIV>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
							<TD class='td_data'><?php print get_building("frm_silver_loc");?></TD>
						</TR>
					</TABLE>		
				</TD>
			</TR>	
			<TR CLASS='even' VALIGN="top">
				<TD CLASS='td_label'>
					<A CLASS="td_label" HREF="#"  TITLE="<?php print get_text("Bronze Command");?>"><?php print get_text("Bronze Command");?></A>:
				</TD>
				<TD COLSPAN='3'>
					<SPAN style='width: 100%; display: block;'>
						<SELECT NAME="frm_bronze" onChange = "this.value=JSfnTrim(this.value); set_command_info(this.value, 'bronze_command_data');">
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
						<TABLE>
							<TR>
								<TD class='td_label'>Email 1</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Email 2</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 1</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Phone 2</TD>
								<TD class='td_data'></TD>
							</TR>
							<TR>
								<TD class='td_label'>Location</TD>
								<TD class='td_data'><?php print get_building();?></TD>
							</TR>
						</TABLE>					
					</DIV>
					<TABLE>
						<TR>
							<TD class='td_label'>Location</TD>
							<TD class='td_data'><?php print get_building("frm_bronze_loc");?></TD>
						</TR>
					</TABLE>		
				</TD>
			</TR>	
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>			
			<TR CLASS = "even">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Description - details about Major Incidents"><?php print get_text("Description");?></A>:&nbsp;<font color='red' size='-1'>*</font>
				</TD>	
				<TD COLSPAN=3 >
					<TEXTAREA NAME="frm_descr" COLS=56 ROWS=10></TEXTAREA>
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Actions and Notes"><?php print get_text("Disposition");?></A>:&nbsp;
				</TD>	
				<TD COLSPAN=3 >
					<TEXTAREA NAME="frm_notes" COLS=56 ROWS=10></TEXTAREA>
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
			<TR>
				<TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD>
			</TR>
			<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
				<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
					<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
					<SPAN id='reset_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.reset_Form.submit();'>Reset</SPAN>
					<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate(document.mi_add_Form);'>Submit</SPAN>
				</TD>
			</TR>
			</TABLE> <!-- end inner left -->
		</DIV>
		<DIV id='rightcol' style='position: fixed; top: 50px;right: 170px;'>
			<DIV id='incs_heading' class='heading' style='text-align: center;'>Incidents to be managed as part of the Major Incident</DIV>
			<DIV id= 'incs_table' style = 'max-height: 400px; border: 1px outset #707070; overflow-y: scroll;'>
				<TABLE>
					<TR CLASS = "even">
						<TD>
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
		</FORM>
		<FORM NAME='can_Form' METHOD="post" ACTION = "maj_inc.php"></FORM>
		<FORM NAME='reset_Form' METHOD='get' ACTION='maj_inc.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='mi'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>

		<!-- 2829 -->
		<A NAME="bottom" /> <!-- 5/3/10 -->
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
		</BODY>
		</HTML>