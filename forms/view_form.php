<?php
require_once('./incs/functions.inc.php');
@session_start();
$sess_id = session_id();
do_login(basename(__FILE__));
$scr_width = $_SESSION['scr_width'];
$scr_height = $_SESSION['scr_height'];
$left_col_width = $scr_width * 0.45;
$right_col_width = $scr_width * 0.45; 
$map_width = $right_col_width * .8;
$map_height = $right_col_width * .8;

$field_34 = ($row['field34'] == "") ? get_variable('def_state') : $row['field34'] ;

?>
<SCRIPT>
window.onresize=function(){set_size()};
var theForm = "view";
var map, minimap, latLng, marker, viewportwidth, viewportheight, outerwidth, outerheight, mapWidth, mapHeight, colwidth, leftcolwidth, rightcolwidth;

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
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .80;
	colwidth = viewportwidth * .45;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .35;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	if($('other_details')) {$('other_details').style.width = rightcolwidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
	set_fontsizes(viewportwidth, "fullscreen");
	}
</SCRIPT>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
			<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
				<DIV CLASS='header text_large' style = "height:32px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><b>Viewing <?php print get_text('Member');?> Data for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b></SPAN>
				</DIV>
				<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
					<FORM enctype="multipart/form-data" METHOD="POST" NAME= "mem_edit_form" ACTION="member.php?func=member&goedit=true&id=<?php print $id;?>&extra=view">
						<FIELDSET>
							<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 1));?></LEGEND>
							<DIV style='position: relative;'>	
							<BR />							
							<LABEL for="frm_field1"><?php print get_text(get_field_label('defined_fields', 1));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('1');?>" SIZE="<?php print get_fieldsize('1');?>" TYPE="text" NAME="frm_field1" VALUE="<?php print $row['field1'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field2"><?php print get_text(get_field_label('defined_fields', 2));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('2');?>" SIZE="<?php print get_fieldsize('2');?>" TYPE="text" NAME="frm_field2" VALUE="<?php print $row['field2'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field6"><?php print get_text(get_field_label('defined_fields', 6));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('6');?>" SIZE="<?php print get_fieldsize('6');?>" TYPE="text" NAME="frm_field6" VALUE="<?php print $row['field6'];?>" DISABLED />
							<BR />
<?php
							print get_control('team', $row['field3'], 'frm_field3', 'Team', true);
?>
							<LABEL for="frm_field4"><?php print get_text(get_field_label('defined_fields', 4));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('4');?>" SIZE="<?php print get_fieldsize('4');?>" TYPE="text" NAME="frm_field4" VALUE="<?php print $row['field4'];?>" DISABLED />
							<BR />

							</DIV>
							<DIV style='position: relative;'>
								<LABEL for="frm_field8"><?php print get_text(get_field_label('defined_fields', 8));?>:</LABEL>
<?php
									$sel_yes = ($row['field8'] == "Yes") ? "SELECTED" : "";
									$sel_no = ($row['field8'] == "No") ? "SELECTED" : "";
?>	
									<SELECT NAME='frm_field8' DISABLED>
										<OPTION VALUE='Yes' <?php print $sel_yes;?>>Yes</OPTION>
										<OPTION VALUE='No' <?php print $sel_no;?>>No</OPTION>
									</SELECT>
								<BR />
<?php
								if((!isset($row['field5'])) || ($row['field5'] == "")) {	//	ID Picture
?>
									<DIV style='position: absolute; top: 0px; right: 0px;'><IMG ALIGN="top" src="./images/no_image.jpg" alt="ID Picture" width="100px" /></DIV>
<?php
								} else {
?>
									<DIV style='position: absolute; top: 0px; right: 0px;'><IMG ALIGN="top" src="<?php print $row['field5'];?>" alt="ID Picture" width="100px" /></DIV>
<?php
								}
?>
								<LABEL for="frm_field7"><?php print get_text(get_field_label('defined_fields', 7));?>:</LABEL>
								<SELECT NAME='frm_field7' DISABLED>
<?php
									foreach ($u_types as $key => $value) {
										$temp = $value;
										$sel = ($row['field7']==$key)? " SELECTED": "";
										print "\t\t\t\t<OPTION STYLE='font-size: 100%;' VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
										}
?>
								</SELECT>
								<BR />
								<LABEL for="frm_field21"><?php print get_text(get_field_label('defined_fields', 21));?>:</LABEL>								
								<SELECT NAME='frm_field21' DISABLED>
<?php
									foreach ($st_types as $key => $value) {
										$temp = $value;
										$sel = ($row['field21']==$key)? " SELECTED": "";
										print "\t\t\t\t<OPTION STYLE='font-size: 100%;' VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
										}
?>
								</SELECT>
								<BR />
								<LABEL for="frm_field18"><?php print get_text(get_field_label('defined_fields', 18));?>:</LABEL>	
								<?php print generate_date_dropdown_olddates("frm_field18",$row['dob'],0, $disallow, true);?>
								<BR />
								<LABEL for="frm_field17"><?php print get_text(get_field_label('defined_fields', 17));?>:</LABEL>	
								<?php print generate_date_dropdown_middates("frm_field17",$row['joindate'],0, $disallow, true);?>
								<BR />
								<LABEL for="frm_field16"><?php print get_text(get_field_label('defined_fields', 16));?>:</LABEL>	
								<?php print generate_date_dropdown_middates("frm_field16",$row['duedate'],0, $disallow, true);?>
								<BR />
								<LABEL for="frm_field15"><?php print get_text(get_field_label('defined_fields', 15));?>:</LABEL>	
<?php
								$sel_yes = ($row['field15'] == "Yes") ? "SELECTED" : "";
								$sel_no = ($row['field15'] == "No") ? "SELECTED" : "";
?>							
								<SELECT NAME='frm_field15' DISABLED>
									<OPTION STYLE='font-size: 100%;' VALUE='Yes' <?php print $sel_yes;?>>Yes</OPTION>
									<OPTION STYLE='font-size: 100%;' VALUE='No' <?php print $sel_no;?>>No</OPTION>
								</SELECT>
								<BR />
								<LABEL for="frm_field19"><?php print get_text(get_field_label('defined_fields', 19));?> Complete:</LABEL>
<?php
									$sel_yes = ($row['field19'] == "Yes") ? "SELECTED" : "";
									$sel_no = ($row['field19'] == "No") ? "SELECTED" : "";
?>
									<SELECT NAME='frm_field19' DISABLED>
										<OPTION STYLE='font-size: 100%;' VALUE='Yes' <?php print $sel_yes;?>>Yes</OPTION>
										<OPTION STYLE='font-size: 100%;' VALUE='No' <?php print $sel_no;?>>No</OPTION>
									</SELECT>
								<BR />
<?php
								for($i=35; $i < 67; $i++) {
									$fieldset = get_fieldset('defined_fields', $i);
									if($fieldset==1) {
										$fieldname = "field" . $i;
										$value = $row[$fieldname];
										print get_field_controls_edit($i, $value, $id, true);
									}
								}
?>
							</DIV>
						</FIELDSET>
						<FIELDSET>
							<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 2));?></LEGEND>
							<BR />
							<LABEL for="frm_field9"><?php print get_text(get_field_label('defined_fields', 9));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('9');?>" SIZE="<?php print get_fieldsize('9');?>" TYPE="text" NAME="frm_field9" VALUE="<?php print $row['field9'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field10"><?php print get_text(get_field_label('defined_fields', 10));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('10');?>" SIZE="<?php print get_fieldsize('10');?>" TYPE="text" NAME="frm_field10" VALUE="<?php print $row['field10'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field11"><?php print get_text(get_field_label('defined_fields', 11));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('11');?>" SIZE="<?php print get_fieldsize('11');?>" TYPE="text" NAME="frm_field11" VALUE="<?php print $row['field11'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field34"><?php print get_text(get_field_label('defined_fields', 34));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('34');?>" SIZE="<?php print get_fieldsize('34');?>" TYPE="text" NAME="frm_field34" VALUE="<?php print $field_34;?>" DISABLED />
							<BR />							
							<DIV style='position: relative;'>
								<LABEL for="frm_field12"><?php print get_text(get_field_label('defined_fields', 12));?>:</LABEL>
								<INPUT MAXLENGTH="<?php print get_fieldsize('12');?>" SIZE="<?php print get_fieldsize('12');?>" TYPE="text" NAME="frm_field12" VALUE="<?php print $row['field12'];?>" DISABLED />
								<BR />
								<LABEL for="frm_field13"><?php print get_text(get_field_label('defined_fields', 13));?>:</LABEL>								
								<INPUT MAXLENGTH="<?php print get_fieldsize('13');?>" SIZE="<?php print get_fieldsize('13');?>" TYPE="text" NAME="frm_field13" VALUE="<?php print $row['field13'];?>" DISABLED />
								<SPAN ID = 'show_map' class = 'plain text' style='position: absolute; right: 30%; top: 10px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" STYLE="display: inline-block; font-size: 14px;" onClick = "$('veh_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_wrapper').style.display='block';">Show Map</SPAN>
							</DIV>
							<BR />
<?php
							for($i=35; $i < 67; $i++) {
								$fieldset = get_fieldset('defined_fields', $i);
								if($fieldset==2) {
									$fieldname = "field" . $i;
									$value = $row[$fieldname];
									print get_field_controls_edit($i, $value, $id, true);
								}
							}
?>
						</FIELDSET>
						<FIELDSET>
							<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 3));?></LEGEND>
							<BR />
							<LABEL for="frm_field22"><?php print get_text(get_field_label('defined_fields', 22));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('22');?>" SIZE="<?php print get_fieldsize('22');?>" TYPE="text" NAME="frm_field22" VALUE="<?php print $row['field22'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field23"><?php print get_text(get_field_label('defined_fields', 23));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('23');?>" SIZE="<?php print get_fieldsize('23');?>" TYPE="text" NAME="frm_field23" VALUE="<?php print $row['field23'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field24"><?php print get_text(get_field_label('defined_fields', 24));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('24');?>" SIZE="<?php print get_fieldsize('24');?>" TYPE="text" NAME="frm_field24" VALUE="<?php print $row['field24'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field25"><?php print get_text(get_field_label('defined_fields', 25));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('25');?>" SIZE="<?php print get_fieldsize('25');?>" TYPE="text" NAME="frm_field25" VALUE="<?php print $row['field25'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field26"><?php print get_text(get_field_label('defined_fields', 26));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('26');?>" SIZE="<?php print get_fieldsize('26');?>" TYPE="text" NAME="frm_field26" VALUE="<?php print $row['field26'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field27"><?php print get_text(get_field_label('defined_fields', 27));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('27');?>" SIZE="<?php print get_fieldsize('27 ');?>" TYPE="text" NAME="frm_field27" VALUE="<?php print $row['field27'];?>" DISABLED />
							<BR />
<?php
							for($i=35; $i < 67; $i++) {
								$fieldset = get_fieldset('defined_fields', $i);
								if($fieldset==3) {
									$fieldname = "field" . $i;
									$value = $row[$fieldname];
									print get_field_controls_edit($i, $value, $id, true);
								}
							}
?>
						</FIELDSET>
						<FIELDSET>
							<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 4));?></LEGEND>
							<BR />
							<LABEL for="frm_field28"><?php print get_text(get_field_label('defined_fields', 28));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('28');?>" SIZE="<?php print get_fieldsize('28');?>" TYPE="text" NAME="frm_field28" VALUE="<?php print $row['field28'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field29"><?php print get_text(get_field_label('defined_fields', 29));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('29');?>" SIZE="<?php print get_fieldsize('29');?>" TYPE="text" NAME="frm_field29" VALUE="<?php print $row['field29'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field30"><?php print get_text(get_field_label('defined_fields', 30));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('30');?>" SIZE="<?php print get_fieldsize('30');?>" TYPE="text" NAME="frm_field30" VALUE="<?php print $row['field30'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field31"><?php print get_text(get_field_label('defined_fields', 31));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('31');?>" SIZE="<?php print get_fieldsize('31');?>" TYPE="text" NAME="frm_field31" VALUE="<?php print $row['field31'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field32"><?php print get_text(get_field_label('defined_fields', 32));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('32');?>" SIZE="<?php print get_fieldsize('32');?>" TYPE="text" NAME="frm_field32" VALUE="<?php print $row['field32'];?>" DISABLED />
							<BR />
							<LABEL for="frm_field33"><?php print get_text(get_field_label('defined_fields', 33));?>:</LABEL>
							<INPUT MAXLENGTH="<?php print get_fieldsize('33');?>" SIZE="<?php print get_fieldsize('33');?>" TYPE="text" NAME="frm_field33" VALUE="<?php print $row['field33'];?>" DISABLED />
							<BR />
<?php
							for($i=35; $i < 67; $i++) {
								$fieldset = get_fieldset('defined_fields', $i);
								if($fieldset==4) {
									$fieldname = "field" . $i;
									$value = $row[$fieldname];
									print get_field_controls_edit($i, $value, $id, true);
								}
							}
?>
						</FIELDSET>						
						<FIELDSET>
							<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 5));?></LEGEND>
							<BR />							
							<LABEL for="frm_field20"><?php print get_text(get_field_label('defined_fields', 20));?>:</LABEL>
							<TEXTAREA NAME='frm_field20' COLS='48' ROWS='2' class="expand50-200" DISABLED><?php print $row['field20'];?></TEXTAREA>
							<BR />
							<LABEL for="frm_field14"><?php print get_text(get_field_label('defined_fields', 14));?>:</LABEL>
							<TEXTAREA name="frm_field14" rows="2" cols="48" class="expand50-200" DISABLED><?php print $row['field14'];?></TEXTAREA>
							<BR />
<?php
							for($i=35; $i < 67; $i++) {
								$fieldset = get_fieldset('defined_fields', $i);
								if($fieldset==5) {
									$fieldname = "field" . $i;
									$value = $row[$fieldname];
									print get_field_controls_edit($i, $value, $id, true);
									}
								}
?>							
						</FIELDSET>
				</DIV>
				<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
					<DIV style='position: fixed; top: 50px; z-index: 1;'>
					
<?php
						if((can_edit()) || (is_team_manager($id)) || (is_curr_member($id))) {
?>
							<SPAN ID = 'ed_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['toedit_Form'].submit();">Edit <?php print get_text('Member');?><BR /><IMG src="./images/edit_small.png"/></SPAN>				
<?php
							}
?>					
						<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?><BR /><IMG src="./images/back_small.png"/></SPAN>
					</DIV>
				</DIV>
				<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
					<DIV id='buttons' style='position: fixed; top: 30px; z-index: 1;'>
						<SPAN ID = 'veh_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_vehiclelist(<?php print $id;?>); $('event_details').style.display='none'; $('veh_details').style.display='block'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='none';"><?php print get_text('Vehicle');?></SPAN>
						<SPAN ID = 'tra_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_traininglist(<?php print $id;?>); $('event_details').style.display='none'; $('veh_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='block'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='none';"><?php print get_text('Training');?></SPAN>
						<SPAN ID = 'eve_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_eventlist(<?php print $id;?>); $('event_details').style.display='block'; $('veh_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='none';"><?php print get_text('Events');?></SPAN>
						<SPAN ID = 'equ_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_equipmentlist(<?php print $id;?>); $('event_details').style.display='none'; $('veh_details').style.display='none'; $('equip_details').style.display='block'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='none';"><?php print get_text('Equipment');?></SPAN>
						<SPAN ID = 'cap_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_capabilitieslist(<?php print $id;?>); $('event_details').style.display='none'; $('veh_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='block'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='none';"><?php print get_text('Capabilities');?></SPAN>
						<SPAN ID = 'cloth_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_clothinglist(<?php print $id;?>); $('event_details').style.display='none'; $('veh_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='block'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='none';"><?php print get_text('Clothing');?></SPAN>
						<SPAN ID = 'file_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "load_member_filelist(<?php print $id;?>); $('event_details').style.display='none'; $('veh_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='block'; $('other_details').style.display='none';"><?php print get_text('Files');?></SPAN>
						<SPAN ID = 'other_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "$('veh_details').style.display='none'; $('event_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='none'; $('file_details').style.display='none'; $('other_details').style.display='block';">Other</SPAN>
						<SPAN ID = 'map_but' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "$('veh_details').style.display='none'; $('event_details').style.display='none'; $('equip_details').style.display='none'; $('training_details').style.display='none'; $('capability_details').style.display='none'; $('cloth_details').style.display='none'; $('map_canvas').style.display='block'; $('file_details').style.display='none'; $('other_details').style.display='none';">Map</SPAN>
					</DIV>
					<BR /><BR />
					<DIV ID='equip_details' style='display: none; position: fixed; top: 70px;'>
						<DIV id='equipheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('Equipment');?> List
								<SPAN id='reload_equipment' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh Equipment List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_equipmentlist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='equipmentlist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_equipmentlist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='training_details' style="display: none; position: fixed; top: 70px;">
						<DIV id='traheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('Training');?> List
								<SPAN id='reload_training' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh Training List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_traininglist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='traininglist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_traininglist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='event_details' style="display: none; position: fixed; top: 70px;">
						<DIV id='eveheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('Events');?> List
								<SPAN id='reload_events' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh Events List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_eventlist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='eventlist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_eventlist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='capability_details' style="display: none; position: fixed; top: 70px;">
						<DIV id='capabheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('Capabilities');?> List
								<SPAN id='reload_capabilities' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh Capabilities List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_capabilitieslist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='capabilitieslist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_capabilitieslist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='veh_details' style="display: none; position: fixed; top: 70px;">
						<DIV id='vehsheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('Vehicle');?> List
								<SPAN id='reload_vehicles' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh Vehicle List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_vehiclelist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='vehiclelist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_vehlist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='cloth_details' style="display: none; position: fixed; top: 70px;">
						<DIV id='clothheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('Clothing');?> List
								<SPAN id='reload_clothing' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh Clothing List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_clothinglist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='clothinglist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_clothinglist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='file_details' style="display: none; position: fixed; top: 70px;">
						<DIV id='fileheading' class = 'header text_large text_white text_bold' style='width: 100%; border: 1px outset #707070; height: 30px;'>
							<DIV style='text-align: center; background-color: #707070; height: 30px;'><?php print get_text('File');?> List
								<SPAN id='reload_files' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Tip("Click to refresh File List");' onmouseout='do_plain(this.id); UnTip();' onClick="load_member_filelist(<?php print $id;?>);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
							</DIV>
						</DIV>				
						<DIV class="scrollableContainer" id='filelist' style='width: 100%; border: 1px outset #707070;'>
							<DIV class="scrollingArea" id='the_filelist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
						</DIV>
					</DIV>
					<DIV ID='other_details' style="position: fixed; top: 70px; padding: 20px; z-index: 2; display: none;">
						<FIELDSET>
							<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 6));?></LEGEND>
							<BR />
<?php
							for($i=35; $i < 67; $i++) {
								$fieldset = get_fieldset('defined_fields', $i);
								if($fieldset==6) {
									$fieldname = "field" . $i;
									$value = $row[$fieldname];
									print get_field_controls_edit($i, $value, $id, true);
									}
								}
?>
						</FIELDSET>
					</DIV>
					<DIV id = 'map_canvas' style = 'position: fixed; top: 70px; border: 1px outset #707070;'></DIV>	
					<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
					<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>	
					<INPUT TYPE="hidden" NAME = "frm_remove" VALUE=""/>	
					<INPUT TYPE="hidden" NAME = "frm_exist_id_pic" VALUE="<?php print $row['field5'];?>"/>					
					</FORM>
				</DIV>
<SCRIPT>
//	setup map-----------------------------------//
			var map;
			var minimap;
			var latLng;
			var marker;
			
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
			mapWidth = viewportwidth * .35;
			mapHeight = viewportheight * .80;
			colwidth = viewportwidth * .45;
			leftcolwidth = viewportwidth * .45;
			rightcolwidth = viewportwidth * .35;
			if($('outer')) {$('outer').style.width = outerwidth + "px";}
			if($('outer')) {$('outer').style.height = outerheight + "px";}
			if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
			if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
			if($('equip_details')) {$('equip_details').style.width = rightcolwidth + "px";}	
			if($('training_details')) {$('training_details').style.width = rightcolwidth + "px";}	
			if($('event_details')) {$('event_details').style.width = rightcolwidth + "px";}	
			if($('capability_details')) {$('capability_details').style.width = rightcolwidth + "px";}	
			if($('veh_details')) {$('veh_details').style.width = rightcolwidth + "px";}
			if($('cloth_details')) {$('cloth_details').style.width = rightcolwidth + "px";}
			if($('file_details')) {$('file_details').style.width = rightcolwidth + "px";}	
			if($('other_details')) {$('other_details').style.width = rightcolwidth + "px";}
			if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
			if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
			set_fontsizes(viewportwidth, "fullscreen");
			var theLocale = <?php print get_variable('locale');?>;
			var useOSMAP = <?php print get_variable('use_osmap');?>;
			var initZoom = <?php print get_variable('def_zoom');?>;
			init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
			var bounds = map.getBounds();	
			var zoom = map.getZoom();
			var infotext = "<?php print $row['field2'];?> <?php print $row['field1'];?>";
			marker.bindPopup(infotext);
			set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>							
				<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member"></FORM>	
				<FORM NAME='toedit_Form' METHOD="post" ACTION = "member.php?func=member&edit=true&id=<?php print $id;?>"></FORM>					
				<FORM NAME='go_Form' METHOD="post" ACTION = ""></FORM>
