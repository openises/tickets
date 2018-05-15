<?php
$all_id = mysql_real_escape_string($_GET['all_id']);
$query_all = "SELECT `id`, `member_id`, `skill_type`, `skill_id`, `frequency`, `_on`, UNIX_TIMESTAMP(completed) AS `completed`, UNIX_TIMESTAMP(refresh_due) AS `refresh_due` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `id` = {$all_id}";
$result_all = mysql_query($query_all) or do_error($query_all, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_all	= mysql_fetch_array($result_all);

$id = $row_all['member_id'];

$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth, viewportheight, outerwidth, outerheight, colwidth, leftcolwidth, rightcolwidth;

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
	outerwidth = viewportwidth * .98;
	outerheight = viewportheight * .95;
	colwidth = viewportwidth * .45;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .35;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	set_fontsizes(viewportwidth, "fullscreen");
	}

function set_expires(val) {
	if(val=="Yes") {
		tpack_edit_Form.frm_day_refresh.disabled = false;
		tpack_edit_Form.frm_month_refresh.disabled = false;
		tpack_edit_Form.frm_year_refresh.disabled = false;
		} else if(val=="Permanent") {
		tpack_edit_Form.frm_day_refresh.disabled = true;
		tpack_edit_Form.frm_month_refresh.disabled = true;
		tpack_edit_Form.frm_year_refresh.disabled = true;
		} else {
		return false;
		}
	}
	
function pop_tra(tp_id) {								// get initial values from server -  4/7/10
	var url = "./ajax/view_training_package.php?session=<?php print MD5($sess_id);?>&tp_id=" + tp_id;
	sendRequest (url ,pop_cb, "");			
		function pop_cb(req) {
			var the_det_arr=JSON.decode(req.responseText);
				$('f1').innerHTML = the_det_arr[2];
				$('f2').innerHTML = the_det_arr[3];
				$('f3').innerHTML = the_det_arr[4];
				$('f4').innerHTML = the_det_arr[5];
				$('f5').innerHTML = the_det_arr[6];
				$('f6').innerHTML = '<a href="mailto:' + the_det_arr[7] + '">' + the_det_arr[7] + '</a>';
				$('f7').innerHTML = the_det_arr[8];						
				$('f8').innerHTML = the_det_arr[9];						
		}				// end function pop_cb()
	}	
function rem_record() {
	if (confirm("Are you sure you want to delete the training record")) { 
		document.tpack_edit_Form.frm_all_remove.value="yes";
		document.forms['tpack_edit_Form'].submit();
		}
	}
</SCRIPT>
</HEAD>
<BODY onload='pop_tra(<?php print $row_all['skill_id'];?>)'>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV CLASS='header text_large' style = "height:32px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><b>Edit <?php print get_text('Training');?> Attendance for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b></SPAN>
		</DIV>
		<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
			<FORM METHOD="POST" NAME= "tpack_edit_Form" ACTION="member.php?func=member&goedittpack=true&extra=edit">
				<FIELDSET>
				<LEGEND><?php print get_text('Training');?> Record</LEGEND>
					<BR />
					<LABEL for="frm_skill"><?php print get_text('Training');?> Completed:</LABEL>
						<SELECT NAME="frm_skill" style='height: 20px; font-size=12px;' onChange='pop_tra(this.options[this.selectedIndex].value);'>
							<OPTION class='normalSelect' VALUE=0 SELECTED>Select</OPTION>
<?php
							$query_tra = "SELECT * FROM `$GLOBALS[mysql_prefix]training_packages` WHERE `available` = 'Yes' ORDER BY `package_name` ASC";
							$result_tra = mysql_query($query_tra) or do_error($query_tra, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_tra = stripslashes_deep(mysql_fetch_assoc($result_tra))) {
								$sel = ($row_tra['id'] == $row_all['skill_id']) ? "SELECTED" : "";									
								print "\t<OPTION class='normalSelect' VALUE='{$row_tra['id']}' {$sel}>{$row_tra['package_name']}</OPTION>\n";
								}
?>
						</SELECT>
					<BR />
					<LABEL for='frm_completed'><?php print get_text('Training');?> Completed On</LABEL>
<?php
					if(($row_all['completed']) == NULL) {
						generate_date_dropdown_middates('completed',0, 0,FALSE);
						} else {
						generate_date_dropdown_middates("completed",$row_all['completed'],0, $disallow);
						}
?>
					<LABEL for='frm_refresh'><?php print get_text('Training');?> Refresh Due</LABEL>
<?php
					if($row_all['frequency'] == "Permanent") {
						generate_date_dropdown_middates('refresh', 0, 0,TRUE);
						} elseif(($row_all['refresh_due'])) {
						generate_date_dropdown_middates('refresh', 0, 0,FALSE);
						} else {
						generate_date_dropdown_middates("refresh",$row_all['refresh_due'],0, $disallow);							
						}
?>
					<BR />
					<LABEL for="frm_expires"><?php print get_text('Training');?> <?php print get_text('expires');?>:</LABEL>
					<SELECT NAME="frm_expires" style='height: 20px; font-size=12px;' onChange="set_expires(this.options[this.selectedIndex].value);">
<?php
					$sel_yes = ($row_all['frequency'] == "Yes") ? "SELECTED" : "";
					$sel_never = ($row_all['frequency'] == "Permanent") ? "SELECTED" : "";
?>
						<OPTION class='normalSelect' VALUE="Yes" <?php print $sel_yes;?>>Yes</OPTION>
						<OPTION class='normalSelect' VALUE="Permanent" <?php print $sel_never;?>>Never</OPTION>
					</SELECT>
				</FIELDSET>
				<INPUT TYPE='hidden' NAME='frm_id' VALUE='<?php print $id;?>'>
				<INPUT TYPE='hidden' NAME='id' VALUE='<?php print $id;?>'>
				<INPUT TYPE='hidden' NAME='frm_all_id' VALUE='<?php print $all_id;?>'>	
				<INPUT TYPE="hidden" NAME = "frm_all_remove" VALUE=""/>
				<INPUT TYPE="hidden" NAME = "frm_fullname" VALUE="<?php print $row['field6'];?>"/>						
			</FORM>	
			<DIV id='tra_details' style='width: 90%; border: 2px outset #CECECE; padding: 20px; text-align: left;'>
				<DIV style='width: 100%; text-align: center;' CLASS='tablehead'>SELECTED <?php print get_text('TRAINING');?> PACKAGE DETAILS</DIV><BR /><BR />
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Description:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f1'>TBA</DIV><BR />
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Availablilty:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f2'>TBA</DIV><BR />						
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Provider:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f3'>TBA</DIV><BR />						
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Address:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f4'>TBA</DIV><BR />						
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Contact Name:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f5'>TBA</DIV><BR />
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Email:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f6'>TBA</DIV><BR />	
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Phone:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f7'>TBA</DIV><BR />							
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Cost <?php print get_text('$');?>:</DIV><DIV style='width: 30%; display: inline-block; vertical-align: text-top;' ID='f8'>TBA</DIV><BR />
			</DIV>					
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 1;'>
				<SPAN ID = 'rem_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="rem_record();">Remove <?php print get_text('Training');?> Record <IMG style='vertical-align: middle; float: right;' src="./images/delete.png"/></SPAN>
				<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle; float: right;' src="./images/back_small.png"/></SPAN>
				<SPAN ID = 'sub_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="validate_skills(document.tpack_edit_Form);"><?php print get_text('Save');?> <IMG style='vertical-align: middle; float: right;' src="./images/save.png"/></SPAN>			
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
			<DIV class='tablehead' style='width: 100%; float: left; z-index: 999'><b><?php print get_text('Training');?> Completed</b></DIV><BR /><BR />					
			<DIV style='padding: 10px; float: left;'><?php print get_text('Training');?> is for registration of the <?php print get_text('training');?> that members have completed, provided by the Organisation.
			<BR />
			Examples could be First Aid, Health and Safety or other job specific training.
			<BR />
			<BR />					
			</DIV>
		</DIV>
	</DIV>	
<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member&edit=true&id=<?php print $id;?>"></FORM>			
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
outerwidth = viewportwidth * .98;
outerheight = viewportheight * .95;
colwidth = viewportwidth * .45;
leftcolwidth = viewportwidth * .45;
rightcolwidth = viewportwidth * .35;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</HTML>						