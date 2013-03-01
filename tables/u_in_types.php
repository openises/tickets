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
			<SPAN STYLE = "margin-left:20px;">Normal &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '0' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[0];?>/></SPAN>
			<SPAN STYLE = "margin-left:20px;">Medium &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '1' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[1];?>/></SPAN>
			<SPAN STYLE = "margin-left:20px;">High &raquo; 	 <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '2' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[2];?>/></SPAN>		
		</TD></TR>

	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Group:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_group" VALUE="<?php print $row['group'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Sort:</TD><TD><INPUT MAXLENGTH=11 SIZE=11 TYPE= "text" NAME="frm_sort" VALUE="<?php print $row['sort'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Radius:</TD><TD><INPUT MAXLENGTH=4 SIZE=4 TYPE= "text" NAME="frm_radius" VALUE="<?php print $row['radius'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>

	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Color:</TD>
		<TD><INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_color" VALUE="<?php print $row['color'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Opacity:</TD><TD><INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_opacity" VALUE="<?php print $row['opacity'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
		<TR><TD COLSPAN="99" ALIGN="center">
	<BR />
	<INPUT TYPE="button" 	VALUE="Cancel" onClick = "Javascript: document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;

	<INPUT TYPE="reset" 	VALUE="Reset"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this )"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="del_but" VALUE="Delete this entry" onclick="if (confirm('Please confirm DELETE action')) {this.form.func.value='d'; this.form.submit();}"/></TD></TR>
	</FORM>
	</TD></TR></TABLE>
<?php	
