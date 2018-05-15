<?php
$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>	
<script src="./js/misc_function.js" type="text/javascript"></script>
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
			
function pop_veh(veh_id) {
	sendRequest ('./ajax/view_vehicle_details.php?session=<?php print MD5(session_id());?>&veh_id=' + veh_id ,pop_cb, "");			
		function pop_cb(req) {
			var the_det_arr=JSON.decode(req.responseText);
				$('f1').innerHTML = the_det_arr[0];
				$('f2').innerHTML = the_det_arr[1];
				$('f3').innerHTML = the_det_arr[2];
				$('f4').innerHTML = the_det_arr[3];
				$('f5').innerHTML = the_det_arr[4];
				$('f6').innerHTML = the_det_arr[5];
				$('f7').innerHTML = the_det_arr[6];
				$('f8').innerHTML = the_det_arr[7];
				$('f9').innerHTML = the_det_arr[8];
				$('f10').innerHTML = the_det_arr[9];
				$('f11').innerHTML = the_det_arr[10];
				$('f12').innerHTML = the_det_arr[11];
				$('f13').innerHTML = the_det_arr[12];
				$('f14').innerHTML = the_det_arr[13];					
		}				// end function pop_cb()
	}				// end function pop_veh()

function set_freq_disp(val) {
	if(val=="Permanent") {
		$('daily').style.display = 'none';
		$('weekly').style.display = 'none';
		}
	if(val=="Daily") {
		$('daily').style.display = 'block';
		$('weekly').style.display = 'none';
		}	
	if(val=="Weekly") {
		$('daily').style.display = 'none';
		$('weekly').style.display = 'block';
		}
	}
	
function validate_vehicle_form(theForm) {
	var errmsg="";
	if (theForm.frm_skill.value.trim()==0) { errmsg+="You need to select a vehicle.\n";}
	if ((theForm.frm_selector.value.trim()=="Daily")
		&& (theForm.frm_hour_frm_daily_start.value.trim() == "00") 
		&& (theForm.frm_minute_frm_daily_start.value.trim() == "00") 				
		&& (theForm.frm_hour_frm_daily_end.value.trim() == "00")
		&& (theForm.frm_minute_frm_daily_end.value.trim() == "00")				
		) {
		errmsg+="For the Daily setting you need to input a star and end time.\n";
		}			
	if ((theForm.frm_selector.value.trim()=="Weekly")
		&& (theForm.frm_weekly_days_monday == 0)
		&& (theForm.frm_weekly_days_tuesday == 0)
		&& (theForm.frm_weekly_days_wednesday == 0)
		&& (theForm.frm_weekly_days_thursday == 0)
		&& (theForm.frm_weekly_days_friday == 0)
		&& (theForm.frm_weekly_days_saturday == 0)
		&& (theForm.frm_weekly_days_sunday == 0)				
		) {
		errmsg+="For the Weekly setting you need to chose some days.\n";
		}
	if (theForm.frm_selector.value.trim()=="Permanent") {
		}
	if ((theForm.frm_selector.value.trim()=="Daily")
		&& (theForm.frm_hour_frm_daily_start.value.trim() != "00") 
		&& (theForm.frm_minute_frm_daily_start.value.trim() != "00") 				
		&& (theForm.frm_hour_frm_daily_end.value.trim() != "00")
		&& (theForm.frm_minute_frm_daily_end.value.trim() != "00")				
		) {
		theForm.frm_start.value = theForm.frm_hour_frm_daily_start.value.trim() + ":" + theForm.frm_minute_frm_daily_start.value.trim();
		theForm.frm_end.value = theForm.frm_hour_frm_daily_end.value.trim() + ":" + theForm.frm_minute_frm_daily_end.value.trim();
		theForm.frm_hour_frm_daily_start.disabled = true;
		theForm.frm_minute_frm_daily_start.disabled = true;
		theForm.frm_hour_frm_daily_end.disabled = true;
		theForm.frm_minute_frm_daily_end.disabled = true;
		theForm.frm_weekly_days_monday.disabled=true;
		theForm.frm_weekly_days_tuesday.disabled=true;
		theForm.frm_weekly_days_wednesday.disabled=true;
		theForm.frm_weekly_days_thursday.disabled=true;
		theForm.frm_weekly_days_friday.disabled=true;
		theForm.frm_weekly_days_saturday.disabled=true;
		theForm.frm_weekly_days_sunday.disabled=true;			
		}
	if (theForm.frm_selector.value.trim()=="Weekly") {
		var theDays = ""
		if(theForm.frm_weekly_days_monday.checked) { theDays += "Monday,"; } else { theDays += ","; }
		if(theForm.frm_weekly_days_tuesday.checked) { theDays += "Tuesday,"; } else { theDays += ","; }
		if(theForm.frm_weekly_days_wednesday.checked) { theDays += "Wednesday,"; } else { theDays += ","; }
		if(theForm.frm_weekly_days_thursday.checked) { theDays += "Thursday,"; } else { theDays += ","; }
		if(theForm.frm_weekly_days_friday.checked) { theDays += "Friday,"; } else { theDays += ","; }
		if(theForm.frm_weekly_days_saturday.checked) { theDays += "Saturday,"; } else { theDays += ","; }
		if(theForm.frm_weekly_days_sunday.checked) { theDays += "Sunday,"; } else { theDays += ","; }				
		theForm.frm_days.value = theDays;
		theForm.frm_weekly_days_monday.disabled=true;
		theForm.frm_weekly_days_tuesday.disabled=true;
		theForm.frm_weekly_days_wednesday.disabled=true;
		theForm.frm_weekly_days_thursday.disabled=true;
		theForm.frm_weekly_days_friday.disabled=true;
		theForm.frm_weekly_days_saturday.disabled=true;
		theForm.frm_weekly_days_sunday.disabled=true;				
		theForm.frm_hour_frm_daily_start.disabled = true;
		theForm.frm_minute_frm_daily_start.disabled = true;
		theForm.frm_hour_frm_daily_end.disabled = true;
		theForm.frm_minute_frm_daily_end.disabled = true;
		}

	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		theForm.submit();
		}
	}				// end function validate_vehicle_form()					
</SCRIPT>		
</HEAD>
<BODY>	
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV CLASS='header text_large' style = "height:32px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><b>Add <?php print get_text('Vehicle');?> For "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b></SPAN>
		</DIV>
		<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
			<FORM METHOD="POST" NAME= "veh_add_Form" ACTION="member.php?func=member&goaddveh=true&extra=edit">
				<FIELDSET>
				<LEGEND>Add <?php print get_text('Vehicle');?></LEGEND>
					<BR />
					<LABEL for="frm_skill"><?php print get_text('Vehicle');?>:</LABEL>
					<SELECT NAME="frm_skill" onChange="pop_veh(this.options[this.selectedIndex].value);">
						<OPTION class='normalSelect' VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]vehicles` ORDER BY `regno` ASC";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
							$text = $row['make'] . " " . $row['model'] . " " . $row['regno'];
							print "\t<OPTION class='normalSelect' VALUE='{$row['id']}'>{$text}</OPTION>\n";
							}
?>
					</SELECT>
					<BR />
					<DIV ID='timeframes' style='width: 100%; text-align: left;'>
					<BR />
						<DIV ID='selector'>
						<BR />
						<LABEL for="frm_selector"><?php print get_text('Frequency');?>:</LABEL>	
						<SELECT NAME="frm_selector" onChange="set_freq_disp(this.options[this.selectedIndex].value);">
							<OPTION class='normalSelect' VALUE="Permanent" SELECTED>Permanent</OPTION>
							<OPTION class='normalSelect' VALUE="Daily">Daily</OPTION>
							<OPTION class='normalSelect' VALUE="Weekly">Weekly</OPTION>		
						</SELECT>
						</DIV>
						<DIV ID='daily' style='display: none;'>
						<LABEL for="frm_daily_start"><?php print get_text('Start');?>:</LABEL>	
						<?php print generate_time_dropdown("frm_daily_start","",0, $disallow);?>
						<BR />
						<LABEL for="frm_daily_end"><?php print get_text('End');?>:</LABEL>
						<?php print generate_time_dropdown("frm_daily_end","",0, $disallow);?>		
						<BR />
						</DIV>
						<DIV ID='weekly' style='display: none;'>
						<BR />
						<DIV style='width: 100%; text-align: center; font-size: 1.5em; font-weight: bold; background: #707070; color: #FFFFFF;'><LABEL for="frm_weekly_days"><?php print get_text('Days');?></LABEL></DIV>
						<BR />
						<DIV style='width: 60%; display: inline-block; position: relative; left: 20%; border: 2px outset #CECECE;'>
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Monday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Monday" NAME="frm_weekly_days_monday"></DIV><BR />
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Tuesday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Tuesday" NAME="frm_weekly_days_tuesday"></DIV><BR />
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Wednesday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Wednesday" NAME="frm_weekly_days_wednesday"></DIV><BR />
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Thursday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Thursday" NAME="frm_weekly_days_thursday"></DIV><BR />
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Friday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Friday" NAME="frm_weekly_days_friday"></DIV><BR />
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Saturday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Saturday" NAME="frm_weekly_days_saturday"></DIV><BR />	
							<DIV style='font-weight: bold; width: 50%; display: inline-block;'><?php print get_text('Sunday');?></DIV><DIV style='font-weight: bold; width: 50%; display: inline-block;'><INPUT TYPE="checkbox" VALUE="Sunday" NAME="frm_weekly_days_sunday"></DIV><BR />									
						</DIV>
						<BR />
						</DIV>
					</DIV>
				</FIELDSET>
				<INPUT TYPE='hidden' NAME = 'frm_start' VALUE=""/>
				<INPUT TYPE='hidden' NAME = 'frm_end' VALUE=""/>
				<INPUT TYPE='hidden' NAME = 'frm_days' VALUE=""/>							
				<INPUT TYPE="hidden" NAME = "frm_id" VALUE="<?php print $id;?>" />
				<INPUT TYPE="hidden" NAME = "id" VALUE="<?php print $id;?>" />	
				<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>	
				<INPUT TYPE="hidden" NAME = "frm_remove" VALUE=""/>						
			</FORM>	
			<DIV id='veh_details' style='width: 90%; border: 2px outset #CECECE; padding: 20px; text-align: left;'>
				<DIV style='width: 100%; text-align: center;' CLASS='tablehead'>SELECTED <?php print get_text('VEHICLE');?> DETAILS</DIV><BR /><BR />
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Owner');?>:</DIV><DIV style='width: 60%; display: inline-block;' ID='f1'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Make:</DIV><DIV style='width: 60%; display: inline-block;' ID='f2'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Model:</DIV><DIV style='width: 60%; display: inline-block;' ID='f3'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Year:</DIV><DIV style='width: 60%; display: inline-block;' ID='f4'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Colour:</DIV><DIV style='width: 60%; display: inline-block;' ID='f5'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> <?php print get_text('Registration');?>:</DIV><DIV style='width: 60%; display: inline-block;' ID='f6'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Type:</DIV><DIV style='width: 60%; display: inline-block;' ID='f7'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'>Fuel Type:</DIV><DIV style='width: 60%; display: inline-block;' ID='f8'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'>Pessenger Seats:</DIV><DIV style='width: 60%; display: inline-block;' ID='f9'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'>Roof Rack:</DIV><DIV style='width: 60%; display: inline-block;' ID='f10'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'>Tow Bar:</DIV><DIV style='width: 60%; display: inline-block;' ID='f11'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'>Winch:</DIV><DIV style='width: 60%; display: inline-block;' ID='f12'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'>Trailer:</DIV><DIV style='width: 60%; display: inline-block;' ID='f13'>TBA</DIV>
				<DIV class='td_label' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Notes:</DIV><DIV style='width: 60%; display: inline-block;' ID='f14'>TBA</DIV>
			</DIV>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 1;'>
				<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle;' src="./images/back_small.png"/></SPAN>
				<SPAN ID = 'sub_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="validate_skills(document.veh_add_Form);"><?php print get_text('Save');?> <IMG style='vertical-align: middle;' src="./images/save.png"/></SPAN>			
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
			<DIV class='tablehead text_large text_bold text_center'><?php print get_text('Vehicle');?> Allocation</DIV><BR /><BR />		
			<DIV style='padding: 10px; float: left;'>This is to register the <?php print get_text('vehicle');?> that is allocated to the member.
			<BR />
			<BR />					
			<SPAN style='display: inline-block; float: left;'>Available <?php print get_text('Vehicles');?> need to be added first to the system either from "Config" or by clicking</SPAN>
			<SPAN ID='to_vehicle' class = 'plain' style='display: inline; float: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_Post('vehicles');">Here</SPAN><BR />
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