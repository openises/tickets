<?php
if(can_edit()) {
	$scr_width = $_SESSION['scr_width'];
	$scr_height = $_SESSION['scr_height'];
	$left_col_width = $scr_width * 0.45;
	$right_col_width = $scr_width * 0.45; 
	$map_width = $right_col_width * .8;
	$map_height = $right_col_width * .8;
?>
	<script src="./js/misc_function.js" type="text/javascript"></script>
	<SCRIPT>
	window.onresize=function(){set_size()};
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

	function geo_locate(myForm) {								// monitor for changes
		var the_address = myForm.frm_field9.value.trim() + "," + myForm.frm_field10.value.trim() + "," + myForm.frm_field11.value.trim() + "," + myForm.frm_field34.value.trim();
		sendRequest ('./ajax/geo_loc.php?addr='+ the_address ,get_data_cb, "");
		}

	function get_data_cb(req) {
		var the_id_arr=JSON.decode(req.responseText);
		var lat = the_id_arr[0];
		var lng = the_id_arr[1];
		document.mem_add_form.frm_field12.value = lat;
		document.mem_add_form.frm_field13.value = lng;
		addMarker(lat, lng, "New Member", './markers/sm_yellow.png');
		map.setView([lat, lng], 12);
		}	
		
</SCRIPT>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Add <?php print get_text('Member');?></SPAN>
		</DIV>
		<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
			<FORM enctype="multipart/form-data" METHOD="POST" NAME= "mem_add_form" ACTION="member.php?func=member&goadd=true">
				<FIELDSET>
					<LEGEND class='text_large text_bold'><?php print get_text(get_fieldset_label('fieldsets', 1));?></LEGEND>
					<DIV style='position: relative;'>	
					<BR />							
					<LABEL for="frm_field1"><?php print get_text(get_field_label('defined_fields', 1));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('1');?>" SIZE="<?php print get_fieldsize('1');?>" TYPE="text" NAME="frm_field1" VALUE="" />
					<BR />
					<LABEL for="frm_field2"><?php print get_text(get_field_label('defined_fields', 2));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('2');?>" SIZE="<?php print get_fieldsize('2');?>" TYPE="text" NAME="frm_field2" VALUE="" />
					<BR />
					<LABEL for="frm_field6"><?php print get_text(get_field_label('defined_fields', 6));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('6');?>" SIZE="<?php print get_fieldsize('6');?>" TYPE="text" NAME="frm_field6" VALUE="" />
					<BR />
<?php
					print get_control_add('team', 'frm_field3', 'Team');
?>
					<LABEL for="frm_field4"><?php print get_text(get_field_label('defined_fields', 4));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('4');?>" SIZE="<?php print get_fieldsize('4');?>" TYPE="text" NAME="frm_field4" VALUE="" />
					<BR />
					<LABEL class='text text_bold' for="frm_field8"><?php print get_text(get_field_label('defined_fields', 8));?>:</LABEL>
						<SELECT CLASS='text_medium' NAME='frm_field8'>
							<OPTION class='normalSelect text_medium' VALUE='Yes'>Yes</OPTION>
							<OPTION class='normalSelect text_medium' VALUE='No' SELECTED>No</OPTION>
						</SELECT>
					<BR />
					<LABEL for="frm_field5"><?php print get_text(get_field_label('defined_fields', 5));?> (jpg only):</LABEL>
					<INPUT TYPE="file" NAME="frm_field5" SIZE="<?php print get_fieldsize('5');?>" style='cursor: pointer;' onChange='checkFile(this, "picture");'></TD>
					<BR />							
					<LABEL for="frm_field7"><?php print get_text(get_field_label('defined_fields', 7));?>:</LABEL>
					<SELECT NAME='frm_field7'>
<?php
					foreach ($u_types as $key => $value) {
						$temp = $value;
						print "\t\t\t\t<OPTION class='normalSelect' VALUE='{$key}'>{$temp[0]}</OPTION>\n";
						}
?>
					</SELECT>
					<BR />
					<LABEL for="frm_field21"><?php print get_text(get_field_label('defined_fields', 21));?>:</LABEL>								
					<SELECT CLASS='text_medium' NAME='frm_field21'>
<?php
					foreach ($st_types as $key => $value) {
						$temp = $value;
						print "\t\t\t\t<OPTION class='normalSelect' VALUE='{$key}'>{$temp[0]}</OPTION>\n";
						}
?>
					</SELECT>
					<BR />
					<LABEL for="frm_field18"><?php print get_text(get_field_label('defined_fields', 18));?>:</LABEL>	
					<?php print generate_date_dropdown_olddates("frm_field18","",0, $disallow);?>
					<BR />
					<LABEL for="frm_field17"><?php print get_text(get_field_label('defined_fields', 17));?>:</LABEL>	
					<?php print generate_date_dropdown_middates("frm_field17","",0, $disallow);?>
					<BR />
					<LABEL for="frm_field16"><?php print get_text(get_field_label('defined_fields', 16));?>:</LABEL>	
					<?php print generate_date_dropdown_middates("frm_field16","",0, $disallow);?>
					<BR />
					<LABEL for="frm_field15"><?php print get_text(get_field_label('defined_fields', 15));?>:</LABEL>	
						<SELECT NAME='frm_field15'>
							<OPTION class='normalSelect' VALUE='Yes'>Yes</OPTION>
							<OPTION class='normalSelect' VALUE='No' SELECTED>No</OPTION>
						</SELECT>
					<BR />
					<LABEL class='text text_bold' for="frm_field19"><?php print get_text(get_field_label('defined_fields', 19));?> Complete:</LABEL>
						<SELECT NAME='frm_field19'>
							<OPTION class='normalSelect' VALUE='Yes'>Yes</OPTION>
							<OPTION class='normalSelect' VALUE='No' SELECTED>No</OPTION>
						</SELECT>
					<BR />
<?php
					for($i=35; $i < 67; $i++) {
						$fieldset = get_fieldset('defined_fields', $i);
						if($fieldset==1) {
							$fieldname = "field" . $i;
							print get_field_controls_add($i, false);
						}
					}
?>
					</DIV>
				</FIELDSET>
				<FIELDSET>
					<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 2));?></LEGEND>
					<BR />
					<LABEL for="frm_field9"><?php print get_text(get_field_label('defined_fields', 9));?>:&nbsp;&nbsp;<BUTTON type="button" onClick="geo_locate(document.mem_add_form);return false;"><img src="./markers/glasses.png" alt="Lookup location." /></BUTTON></LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('9');?>" SIZE="<?php print get_fieldsize('9');?>" TYPE="text" NAME="frm_field9" VALUE="" />
					<BR />
					<LABEL for="frm_field10"><?php print get_text(get_field_label('defined_fields', 10));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('10');?>" SIZE="<?php print get_fieldsize('10');?>" TYPE="text" NAME="frm_field10" VALUE="" />
					<BR />
					<LABEL for="frm_field11"><?php print get_text(get_field_label('defined_fields', 11));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('11');?>" SIZE="<?php print get_fieldsize('11');?>" TYPE="text" NAME="frm_field11" VALUE="" />
					<BR />
					<LABEL for="frm_field34"><?php print get_text(get_field_label('defined_fields', 34));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('34');?>" SIZE="<?php print get_fieldsize('34');?>" TYPE="text" NAME="frm_field34" VALUE="<?php print get_variable('def_state');?>" />
					<BR />							
					<DIV style='position: relative;'>
						<LABEL for="frm_field12"><?php print get_text(get_field_label('defined_fields', 12));?>:</LABEL>
						<INPUT MAXLENGTH="<?php print get_fieldsize('12');?>" SIZE="<?php print get_fieldsize('12');?>" TYPE="text" NAME="frm_field12" VALUE="" />
						<BR />
						<LABEL for="frm_field13"><?php print get_text(get_field_label('defined_fields', 13));?>:</LABEL>								
						<INPUT MAXLENGTH="<?php print get_fieldsize('13');?>" SIZE="<?php print get_fieldsize('13');?>" TYPE="text" NAME="frm_field13" VALUE="" />
					</DIV>
					<BR />
<?php
					for($i=35; $i < 67; $i++) {
						$fieldset = get_fieldset('defined_fields', $i);
						if($fieldset==2) {
							$fieldname = "field" . $i;
							print get_field_controls_add($i, false);
						}
					}
?>
				</FIELDSET>
				<FIELDSET>
					<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 3));?></LEGEND>
					<BR />
					<LABEL for="frm_field22"><?php print get_text(get_field_label('defined_fields', 22));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('22');?>" SIZE="<?php print get_fieldsize('22');?>" TYPE="text" NAME="frm_field22" VALUE="" />
					<BR />
					<LABEL for="frm_field23"><?php print get_text(get_field_label('defined_fields', 23));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('23');?>" SIZE="<?php print get_fieldsize('33');?>" TYPE="text" NAME="frm_field23" VALUE="" />
					<BR />
					<LABEL for="frm_field24"><?php print get_text(get_field_label('defined_fields', 24));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('24');?>" SIZE="<?php print get_fieldsize('24');?>" TYPE="text" NAME="frm_field24" VALUE="" />
					<BR />
					<LABEL for="frm_field25"><?php print get_text(get_field_label('defined_fields', 25));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('25');?>" SIZE="<?php print get_fieldsize('25');?>" TYPE="text" NAME="frm_field25" VALUE="" />
					<BR />
					<LABEL for="frm_field26"><?php print get_text(get_field_label('defined_fields', 26));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('26');?>" SIZE="<?php print get_fieldsize('26');?>" TYPE="text" NAME="frm_field26" VALUE="" />
					<BR />
					<LABEL for="frm_field27"><?php print get_text(get_field_label('defined_fields', 27));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('27');?>" SIZE="<?php print get_fieldsize('27');?>" TYPE="text" NAME="frm_field27" VALUE="" />
					<BR />
<?php
					for($i=35; $i < 67; $i++) {
						$fieldset = get_fieldset('defined_fields', $i);
						if($fieldset==3) {
							$fieldname = "field" . $i;
							print get_field_controls_add($i, false);
						}
					}
?>
				</FIELDSET>
				<FIELDSET>
					<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 4));?></LEGEND>
					<BR />
					<LABEL for="frm_field28"><?php print get_text(get_field_label('defined_fields', 28));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('28');?>" SIZE="<?php print get_fieldsize('28');?>" TYPE="text" NAME="frm_field28" VALUE="" />
					<BR />
					<LABEL for="frm_field29"><?php print get_text(get_field_label('defined_fields', 29));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('29');?>" SIZE="<?php print get_fieldsize('29');?>" TYPE="text" NAME="frm_field29" VALUE="" />
					<BR />
					<LABEL for="frm_field30"><?php print get_text(get_field_label('defined_fields', 30));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('30');?>" SIZE="<?php print get_fieldsize('30');?>" TYPE="text" NAME="frm_field30" VALUE="" />
					<BR />
					<LABEL for="frm_field31"><?php print get_text(get_field_label('defined_fields', 31));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('31');?>" SIZE="<?php print get_fieldsize('31');?>" TYPE="text" NAME="frm_field31" VALUE="" />
					<BR />
					<LABEL for="frm_field32"><?php print get_text(get_field_label('defined_fields', 32));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('32');?>" SIZE="<?php print get_fieldsize('32');?>" TYPE="text" NAME="frm_field32" VALUE="" />
					<BR />
					<LABEL for="frm_field33"><?php print get_text(get_field_label('defined_fields', 33));?>:</LABEL>
					<INPUT MAXLENGTH="<?php print get_fieldsize('33');?>" SIZE="<?php print get_fieldsize('33');?>" TYPE="text" NAME="frm_field33" VALUE="" />
					<BR />
<?php
					for($i=35; $i < 67; $i++) {
						$fieldset = get_fieldset('defined_fields', $i);
						if($fieldset==4) {
							$fieldname = "field" . $i;
							print get_field_controls_add($i, false);
						}
					}
?>
				</FIELDSET>						
				<FIELDSET>
					<LEGEND><?php print get_text(get_fieldset_label('fieldsets', 5));?></LEGEND>
					<BR />							
					<LABEL for="frm_field20"><?php print get_text(get_field_label('defined_fields', 20));?>:</LABEL>
					<TEXTAREA NAME='frm_field20' COLS='' ROWS='' class="expand50-200 text_medium"></TEXTAREA>
					<BR />
					<LABEL for="frm_field14"><?php print get_text(get_field_label('defined_fields', 14));?>:</LABEL>
					<TEXTAREA name="frm_field14" COLS='' ROWS='' class="expand50-200 text_medium"></TEXTAREA>
					<BR />
<?php
					for($i=35; $i < 67; $i++) {
						$fieldset = get_fieldset('defined_fields', $i);
						if($fieldset==5) {
							$fieldname = "field" . $i;
							print get_field_controls_add($i, false);
						}
					}
?>							
				</FIELDSET>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 1;'>
				<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?><BR /><IMG src="./images/back_small.png"/></SPAN>
				<SPAN ID = 'sub_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['mem_add_form'].submit();"><?php print get_text('Save');?><BR /><IMG src="./images/save.png"/></SPAN>	
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
			<DIV id='buttons' style='position: fixed; top: 30px; z-index: 1;'>
				<SPAN ID = 'other_det' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "$('map_canvas').style.display='none'; $('other_details').style.display='block';">Other Details</SPAN>
				<SPAN ID = 'map_but' class = 'plain text' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "$('map_canvas').style.display='block'; $('other_details').style.display='none';">Show Map</SPAN>
			</DIV>
			<BR /><BR />
			<DIV ID='other_details' style="position: fixed; display: none;">
				<FIELDSET>
					<LEGEND class='text_large text_bold'><?php print get_text(get_fieldset_label('fieldsets', 6));?></LEGEND>
					<BR />
<?php
					for($i=35; $i < 67; $i++) {
						$fieldset = get_fieldset('defined_fields', $i);
						if($fieldset==6) {
							$fieldname = "field" . $i;
							print get_field_controls_add($i, false);
						}
					}
?>
					</FIELDSET>
			</DIV>
			<DIV id = 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
			<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>	
			</FORM>						
		</DIV>
	</DIV>
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
mapWidth = viewportwidth * .35;
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
init_map(2, def_lat, def_lng, "", parseInt(initZoom), locale, useOSMAP, "tr");
var bounds = map.getBounds();	
var zoom = map.getZoom();
</SCRIPT>
	<FORM NAME='can_Form' METHOD="post" ACTION = "member.php"></FORM>			
	<FORM NAME='go_Form' METHOD="post" ACTION = ""></FORM>
<?php
	}	//	end if(can_edit())