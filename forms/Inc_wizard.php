<?php
?>
<SCRIPT>
var stageval1;
var stageval2;
var stageval3;
var stageval4;
var modal = $('myModal');

function modalStart() {
	modal = $('myModal');
	var thestage = $("stage1");
	modal.style.display = "block";
	thestage.style.display = "block"		
	}

function doModal(stage) {
	var thestage = $(stage);
	thestage.style.display = "block";
	if(stage == "stage2") {
		document.getElementById('sel_maj_inc').options[stageval1].selected = 'selected';
		document.getElementById('frm_phone').value = document.getElementById('phonedata').value;
		}
	if(stage == "stage3") {
		document.getElementById('contact').value = document.getElementById('wiz_contact').value;
		if(stageval1) {
			document.getElementById('sel_bldg').value = stageval1;
			document.getElementById('about').value = document.getElementById('wiz_about').value;
			document.getElementById('toaddress').value = document.getElementById('wiz_toaddress').value;
			do_bldg(stageval1);
			} else {
			document.getElementById('frm_street').value = document.getElementById('wiz_contact').value;
			document.getElementById('about').value = document.getElementById('wiz_about').value;
			document.wiz_add.frm_city.value = obj_bldg.bldg_city;
			document.getElementById('state').value = document.getElementById('wiz_st').value;
			document.getElementById('toaddress').value = document.getElementById('wiz_toaddress').value;
			}
		}
	if(stage == "stage4") {
		document.getElementById('sel_in_types_id').options[stageval1].selected = 'selected';
		}
	stageval1 = null;
	stageval2 = null;
	stageval3 = null;
	stageval4 = null;	
	}
	
function closeModal(stage) {
	var thestage = $(stage);
	thestage.style.display = "none";		
	}
	
function modalEnd() {
	modal = $('myModal');
	modal.style.display = "none";
	}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
		}
	}
	
function get_bldg(in_val) {									// called with zero-based array index - 3/29/2014
	if(myMarker) {map.removeLayer(marker); }
	var obj_bldg = bldg_arr[in_val];						// nth object
	document.getElementById('wiz_street').value = obj_bldg.bldg_street;
	document.wiz_add.wiz_frm_city.value = obj_bldg.bldg_city;
	document.getElementById('wiz_st').value = obj_bldg.bldg_state;
	var theLat = parseFloat(obj_bldg.bldg_lat).toFixed(6);
	var theLng = parseFloat(obj_bldg.bldg_lon).toFixed(6);
	}		// end function do_bldg()
</SCRIPT>
<DIV id="myModal" class="modal">	<!-- Modal Box Outside wrapper -->
	<FORM NAME="wiz_add" METHOD="post" ENCTYPE="multipart/form-data">
	<DIV id='stage1' class="modal-content" style='display: none;'>	<!-- First Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			<TABLE>
<?php
				if ($num_mi > 0) {
?>
					<TR>
						<TD CLASS="td_label text_biggest text_left" ><?php print get_text("Major Incident"); ?>:</TD>
						<TD>&nbsp;</TD>
						<TD class='td_data text text_biggest text_left'>
							<SELECT NAME='frm_maj_inc' class='text_biggest' onChange="window.stageval1 = this.selectedIndex;">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `mi_status` = 'Open' OR `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00' ORDER BY `id` ASC";
								$result_mi = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_mi = stripslashes_deep(mysql_fetch_assoc($result_mi))) {
									print "\t<OPTION VALUE='{$row_mi['id']}'>{$row_mi['name']}</OPTION>\n";
									}
?>
							</SELECT>
						</TD>
					</TR>
					<TR><TD>&nbsp;</TD></TR>
<?php
					}		// end if()
?>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_phone"];?>');"><?php print get_text("Phone");?>:</TD>
<?php
					if(get_variable('locale') == 0) {
?>				
						<TD ALIGN='center' ><BUTTON type="button" onClick="Javascript:phone_lkup(document.add.frm_phone.value);"><img src="./markers/glasses.png" alt="Lookup phone no." ></button>&nbsp;&nbsp;</TD>
<?php
						} else {
?>
						<TD ALIGN='center' >&nbsp;&nbsp;</TD>
<?php					
						}
?>
					<TD class='td_data text_biggest text_left'>
						<INPUT CLASS='text_biggest' ID='phonedata' NAME="phonedata" tabindex=50 SIZE="16" TYPE="text" VALUE="<?php print $phone;?>" MAXLENGTH="16" />&nbsp;
					</TD>
				</TR>
			</TABLE>
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage1"); doModal("stage2");'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	<DIV id='stage2' class="modal-content" style='display: none;'>	<!-- Second Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			<TABLE>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_caller"];?>');"><?php print get_text("Reported by");?></A>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD>
					<TD></TD>
					<TD class='td_data text_biggest text_left'><INPUT CLASS='text_biggest text_left' id='wiz_contact' NAME="frm_contact"  tabindex=110 SIZE="56" TYPE="text" VALUE="<?php print $reported_by; ?>" MAXLENGTH="48" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"></TD>
				</TR>
<?php
	
				$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
				$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if (mysql_num_rows($result_bldg) > 0) {
					$i = 0;
					$sel_str2 = "<SELECT CLASS='text_biggest' name='bldg' onChange = 'window.stageval1 = this.value; get_bldg(this.value)'>\n";
					$sel_str2 .= "\t<OPTION CLASS='text_biggest' value = '' SELECTED>Select building</OPTION>\n";
					while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
						$sel_str2 .= "\t<OPTION CLASS='text_biggest' VALUE = {$i} >{$row_bldg['name']}</OPTION>\n";
						$i++;
						}		// end while ()

					$sel_str2 .= "\t</SELECT>\n";
					}		// end if (mysql... )
				if (mysql_num_rows($result_bldg) > 0) {
?>
					<TR>
						<TD CLASS="td_label text_biggest text_left" ><?php print get_text("Building"); ?>:</TD>
						<TD>&nbsp;</TD>
						<TD class='td_data text_biggest text_left'><?php echo $sel_str2;?></TD>
					</TR>
					<TR><TD>&nbsp;</TD></TR>
<?php
					}		// end if()
?>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_loca"];?>');"><?php print get_text("Location"); ?>:</TD>
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<INPUT CLASS='text_biggest text_left' id='wiz_street' NAME="frm_street" SIZE="52" TYPE="text" VALUE="<?php print $street;?>" MAXLENGTH="96" <?php echo $addr_sugg_str ;?> />
						<DIV ID="addr_list" style = "display:inline;"></DIV>
					</TD> <!-- 5/23/2015  -->
				</TR>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('About Address - for instance, round the back, building number etc.');"><?php print get_text("Address About"); ?>:</TD>	<!-- 9/10/13 -->
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<INPUT CLASS='text_biggest text_left' id='wiz_about' NAME="frm_address_about" SIZE="52" TYPE="text" VALUE="" MAXLENGTH="512" />
					</TD>
				</TR>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_city"];?>')"><?php print get_text("City");?>:</TD>
					<TD ALIGN='center'>
<?php
						if($gmaps) {
?>
							<BUTTON type="button" onClick="Javascript:loc_lkup(document.add);return false;"><img src="./markers/glasses.png" alt="Lookup location." /></BUTTON>&nbsp;&nbsp;
<?php
							}
?>
					</TD>
					<TD class='td_data text_biggest text_left'>
					<INPUT ID="my_txt" CLASS='text_biggest' onFocus = "createAutoComplete();$('city_reset').visibility='visible';" NAME="wiz_frm_city" autocomplete="off" tabindex=30 SIZE="32" TYPE="text" VALUE="<?php print $city; ?>" MAXLENGTH="32" onChange = " $('city_reset').visibility='visible'; this.value=capWords(this.value)">
					<span id="suggest" onmousedown="$('suggest').style.display='none'; $('city_reset').style.visibility='visible';" style="visibility:hidden;border:#000000 1px solid;width:150px;right:400px;" /></span>
					<IMG ID = 'city_reset' SRC="./markers/reset.png" STYLE = "margin-left:20px; visibility:hidden;" onClick = "this.style.visibility='hidden'; document.add.frm_city.value=''; document.add.frm_city.focus(); obj_sugg = null;" />
<?php
						if ($gmaps) {		// 12/1/2012
?>
							<BUTTON type="button" onClick="Javascript:do_nearby(document.add);return false;">Nearby?</BUTTON> <!-- 11/22/2012 -->
<?php
							}
?>
					</TD>
				</TR>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_state'];?>');"><?php print get_text("St"); ?>:</TD>
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<INPUT CLASS='text_biggest text_left' ID='wiz_st' NAME="frm_state" SIZE="<?php print $st_size;?>" TYPE="text" VALUE="<?php print $st;?>" MAXLENGTH="<?php print $st_size;?>" />
					</TD>
				</TR>
				<TR>
					<TD COLSPAN=3>
						<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 300px; font-size: 1.5em; font-weight: bold; border: 1px outset #707070;'></DIV>
					</TD>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('To address - Not plotted on map, for information only');"><?php print get_text("To address");?>:</TD>	<!-- 9/10/13 -->
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<INPUT CLASS='text_biggest text_left' id='wiz_toaddress' NAME="frm_to_address" SIZE="52" TYPE="text" VALUE=""  MAXLENGTH="1024">
					</TD>
				</TR>
			</TABLE>
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage2"); doModal("stage3");'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	<DIV id='stage3' class="modal-content" style='display: none;'>	<!-- Fourth Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			<TABLE>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('Type or class of Incident');"><?php print get_text("Incident Type");?>:
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<SELECT CLASS='text_biggest text_left' tabindex=60 onChange="window.stageval1 = this.selectedIndex;">
							<OPTION VALUE=0 SELECTED>TBD</OPTION>
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
							$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							$the_grp = strval(rand());			//  force initial optgroup value
							$i = 0;
							while ($temp_row = stripslashes_deep(mysql_fetch_array($temp_result))) {
								if ($the_grp != $temp_row['group']) {
									print ($i == 0)? "": "</OPTGROUP>\n";
									$the_grp = $temp_row['group'];
									print "<OPTGROUP LABEL='{$temp_row['group']}'>\n";
									}
								$color = $temp_row['color'];
								$bgcolor = "white";
/* 								switch($color) {
									case "blue":
									case "black":
									case "green":
									case "lightgreen":
										$bgcolor = "white";
										break;
									case "":
										$bgcolor = "white";
										$color = "black";
										break;								
									default:
										$bgcolor = "black";
										break;
									}*/
								print "\t<OPTION VALUE=' {$temp_row['id']}' CLASS='{$temp_row['group']}' style='color: {$color}; background-color: {$bgcolor};' title='{$temp_row['description']}'> {$temp_row['type']} </OPTION>\n";
								if (!(empty($temp_row['protocol']))) {
									$temp = preg_replace("/[\n\r]/"," ",$temp_row['protocol']);
									$temp = addslashes($temp);
									print "\n<SCRIPT>\n\t window.protocols[{$temp_row['id']}] = \"{$temp}\";\n</SCRIPT>\n";		// 7/16/09, 5/6/10
									}
								$i++;
								}		// end while()
							print "\n</OPTGROUP>\n";
?>
						</SELECT>
					</TD>
				</TR>
				<TR>
					<TD CLASS='td_label text_biggest text_left' onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_prio"];?>');"><?php print get_text("Priority");?>:</TD>
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<SELECT CLASS='text_biggest text_left' NAME="frm_severity" tabindex=70>
							<OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
							<OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
							<OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
						</SELECT>
					</TD>
				</TR>
				<TR>	<!--  3/15/11 -->
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_proto"];?>');"><?php print get_text("Protocol");?></A>:</SPAN></TD>
					<TD></TD>
					<TD ID='proto_cell'></TD>
				</TR>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_911"];?>');"><?php print get_text("911 Contacted"); ?></A>:&nbsp;</TD>
					<TD>&nbsp;</TD>
					<TD class='td_data text_biggest text_left'>
						<INPUT ID='wzd_911' CLASS='text_biggest text_left' id='911' NAME="frm_nine_one_one" tabindex=100 SIZE="56" TYPE="text" VALUE="" MAXLENGTH="96" >&nbsp;
						<BUTTON type="button" onClick="javascript:var now = new Date(); $('wzd_911').value=now.getDate()+'/' + (now.getMonth()+1) + '/' + now.getFullYear() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();">Now</BUTTON>
					</TD>
				</TR>
			</TABLE>
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage3"); doModal("stage4");'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	<DIV id='stage4' class="modal-content" style='display: none;'>	<!-- Fifth Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			<TABLE>
				<TR>
					<TD CLASS="td_label text_biggest text_left" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_synop"];?>');"><?php print get_text("Synopsis");?>: </TD>
					<TD></TD>
					<TD class='td_data text_biggest text_left'>
						<TEXTAREA CLASS='text_biggest text_left' id='description' NAME="frm_description" COLS="40" ROWS="10" WRAP="virtual"></TEXTAREA>
					</TD>
				</TR>
			</TABLE>
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage4"); doModal("stage5");'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	<DIV id='stage5' class="modal-content" style='display: none;'>	<!-- Sixth Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			Stage 5
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage5"); doModal("stage6");'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	<DIV id='stage6' class="modal-content" style='display: none;'>	<!-- Seventh Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			Stage 6
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage6"); doModal("stage7");'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	<DIV id='stage7' class="modal-content" style='display: none;'>	<!-- Eighth Stage -->
		<DIV class="modal-header">
			<SPAN class='heading text_biggest' style='width: 100%; display: block;'>New Incident</SPAN>
		</DIV>
		<DIV class="modal-body">
			Stage 7
		</DIV>
		<DIV class="modal-footer">
			<CENTER><SPAN id='nextBtn' CLASS='plain text' style='float: none; width: 80px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='closeModal("stage7"); modalEnd()'>Next</SPAN></CENTER><BR />
		</DIV>
	</DIV>
	</FORM>
</DIV>