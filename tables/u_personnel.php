<SCRIPT>
function validate_form(myform) {	// reject empty form elements
	myform.frm_date_of_birth.value = myform.frm_year_date_of_birth.value + "-" + myform.frm_month_date_of_birth.value + "-" + myform.frm_day_date_of_birth.value + " 00:00:00";
	myform.frm_year_date_of_birth.disabled=true;
	myform.frm_month_date_of_birth.disabled=true;
	myform.frm_day_date_of_birth.disabled=true;	
	myform.submit();		
	}				// end function 
</SCRIPT>
		<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pu" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="frm_date_of_birth" VALUE="" />
		<INPUT TYPE="hidden" NAME="frm__by" 	VALUE="<?php print $_SESSION['user_id']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__on" 	VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>" />
		<INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Personnel' - Update Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Surname:</TD>
			<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_surname" VALUE="<?php print $row['surname'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Fornames:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_forenames" VALUE="<?php print $row['forenames'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Address:</TD>
			<TD><INPUT ID="ID3" CLASS="dirty" MAXLENGTH="128" SIZE="48" type="text" NAME="frm_address" VALUE="<?php print $row['address'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">State:</TD>
			<TD><INPUT ID="ID4" CLASS="dirty" MAXLENGTH="24" SIZE="4" type="text" NAME="frm_state" VALUE="<?php print $row['state'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Date of Birth:</TD>
			<TD><?php print generate_dateonly_dropdown('date_of_birth',strtotime($row['date_of_birth']),FALSE);?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Gender:</TD>
			<TD><INPUT ID="ID5" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_gender" VALUE="<?php print $row['gender'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Identifier:</TD>
			<TD><INPUT ID="ID6" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_person_identifier" VALUE="<?php print $row['person_identifier'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Email:</TD>
			<TD><INPUT ID="ID7" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_email" VALUE="<?php print $row['email'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Home phone:</TD>
			<TD><INPUT ID="ID8" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_homephone" VALUE="<?php print $row['homephone'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Work phone:</TD>
			<TD><INPUT ID="ID9" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_workphone" VALUE="<?php print $row['workphone'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Cellphone:</TD>
			<TD><INPUT ID="ID10" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_cellphone" VALUE="<?php print $row['cellphone'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Next of Kin Name:</TD>
			<TD><INPUT ID="ID11" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_next_of_kin_name" VALUE="<?php print $row['next_of_kin_name'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Next of Kin Address:</TD>
			<TD><INPUT ID="ID12" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_next_of_kin_address" VALUE="<?php print $row['next_of_kin_address'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Next of Kin Home phone:</TD>
			<TD><INPUT ID="ID13" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_next_of_kin_homephone" VALUE="<?php print $row['next_of_kin_homephone'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Next of Kin Work phone:</TD>
			<TD><INPUT ID="ID14" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_next_of_kin_workphone" VALUE="<?php print $row['next_of_kin_workphone'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Next of Kin Cellphone:</TD>
			<TD><INPUT ID="ID15" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_next_of_kin_cellphone" VALUE="<?php print $row['next_of_kin_cellphone'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">AR Callsign:</TD>
			<TD><INPUT ID="ID16" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_amateur_radio_callsign" VALUE="<?php print $row['amateur_radio_callsign'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Notes:</TD>
			<TD><TEXTAREA ID="ID17" name="frm_person_notes" rows="6" cols="48" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"><?php print $row['person_notes'];?></TEXTAREA> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Capabilities:</TD>
			<TD><TEXTAREA ID="ID18" name="frm_person_capabilities" rows="6" cols="48" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"><?php print $row['person_capabilities'];?></TEXTAREA> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR>
			<TD COLSPAN="99" ALIGN="center">
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r';document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.u.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate_form(document.u, this);"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</TD>
		</TR>
		</FORM>
		</td></tr></table>

<?php
