	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename;?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
	<INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->
	<INPUT TYPE="hidden" NAME="frm_set_severity"	VALUE="<?php print $row['set_severity'] ;?>"/>&nbsp;&nbsp;&nbsp;&nbsp;

	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Incident types' - Update/Delete Entry</FONT></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Id:</TD><TD><INPUT MAXLENGTH=4 SIZE=4 TYPE= "text" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" onChange = "this.value=JSfnTrim(this.value)" disabled/> <SPAN class='warn' >numeric</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Type:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_type" VALUE="<?php print $row['type'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='warn' >text</SPAN></TD></TR>

	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Description:</TD>
		<TD><INPUT MAXLENGTH="60" SIZE="60" type="text" NAME="frm_description" VALUE="<?php print $row['description'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Protocol:</TD>
		<TD><TEXTAREA NAME="frm_protocol" COLS="90" ROWS = "1"><?php print $row['protocol'] ;?></TEXTAREA> <SPAN class='opt' >text</SPAN></TD></TR>

<?php
	$temp = array("", "", "");
	$temp[$row['set_severity']] = " checked ";
?>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Set severity:</TD>
		<TD>
			<SPAN STYLE = "margin-left:20px;"><?php print get_text('Normal'); ?>&raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '0' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[0];?>/></SPAN>
			<SPAN STYLE = "margin-left:20px;"><?php print get_text('Medium'); ?> &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '1' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[1];?>/></SPAN>
			<SPAN STYLE = "margin-left:20px;"><?php print get_text('High'); ?> &raquo; 	 <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '2' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[2];?>/></SPAN>
		</TD></TR>

<?php						// 4/4/2015
	switch($row['watch']) {
		case "0":	$checked1_value = "CHECKED"; 	$checked2_value = ""; 			break;
		default: 	$checked1_value = ""; 			$checked2_value = "CHECKED";
		}
?>
	<TR VALIGN="baseline" CLASS="even">
		<TD CLASS="td_label" ALIGN="right">Watch:</TD>
		<TD VALIGN='baseline'><B>
			<SPAN STYLE = 'margin-left:20px;'>No &raquo; <INPUT TYPE='radio' NAME="frm_watch" VALUE= "0" <?php print $checked1_value;?>/>
			<SPAN STYLE = 'margin-left:20px;'>Yes &raquo; <INPUT TYPE='radio' NAME="frm_watch" VALUE= "1" <?php print $checked2_value;?>/>
			</TD></TR>

	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Group:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_group" VALUE="<?php print $row['group'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Sort:</TD><TD><INPUT MAXLENGTH=11 SIZE=11 TYPE= "text" NAME="frm_sort" VALUE="<?php print $row['sort'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Radius:</TD><TD><INPUT MAXLENGTH=4 SIZE=4 TYPE= "text" NAME="frm_radius" VALUE="<?php print $row['radius'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>

	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Color:</TD>
		<TD><INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_color" VALUE="<?php print $row['color'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Opacity:</TD><TD><INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_opacity" VALUE="<?php print $row['opacity'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
<?php
	$mg_select = "<SELECT NAME='frm_notify_mailgroup'>";
	$mg_select .= "<OPTION VALUE=0 SELECTED>Select Mail List</OPTION>";
	$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";
	$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
		$sel = ($row['notify_mailgroup'] == $row_mg['id']) ? "SELECTED" : "";
		$mg_select .= "\t<OPTION {$sel} VALUE='{$row_mg['id']}'>{$row_mg['name']} </OPTION>\n";
		}
	$mg_select .= "</SELECT>";
?>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Notify Mailgroup:</TD><TD><?php print $mg_select;?></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Notify Email:</TD><TD><INPUT MAXLENGTH=256 SIZE=60 TYPE= "text" NAME="frm_notify_email" VALUE="<?php print $row['notify_email'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Notify When:</TD><TD><INPUT MAXLENGTH=1 SIZE=1 TYPE= "number" min="1" max="3" NAME="frm_notify_when" VALUE="<?php print $row['notify_when'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >1,2 or 3 for All, Open or Close</SPAN></TD></TR>
		<TR>
			<TD COLSPAN="99" ALIGN="center">
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.u.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnCheckInput(document.u, this );"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN id='del_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="if (confirm('Please confirm DELETE action')) {this.form.func.value='d'; this.form.submit();}"><SPAN STYLE='float: left;'><?php print get_text("Delete");?></SPAN><IMG STYLE='float: right;' SRC='./images/delete.png' BORDER=0></SPAN>
			</TD>
		</TR>
	<TR><TD COLSPAN="99" ALIGN="center">
	</FORM>
	</TD></TR></TABLE>
<?php
//	4/4/2015 - added 'watch' attribute handling
