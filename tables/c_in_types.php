<!--
4/4/2015 - added 'watch' attribute handling
-->

		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>"/>
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="frm_set_severity"	VALUE="0"/>&nbsp;&nbsp;&nbsp;&nbsp;

		<TABLE BORDER="0" ALIGN="center">

		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'in_types' - Add New Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Type:</TD>
			<TD><INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="20" SIZE="20" type="text" NAME="frm_type" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>
			<TD><INPUT  ID="ID2" CLASS="dirty" MAXLENGTH="60" SIZE="60" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>

		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Protocol:</TD>
			<TD><TEXTAREA ID="ID3" CLASS="dirty" NAME="frm_protocol" COLS="90" ROWS = "2" onFocus="JSfnChangeClass(this.id, 'dirty');" ></TEXTAREA> <SPAN class='opt' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Severity:</TD>
			<TD>
				<SPAN STYLE = "margin-left:20px;"><?php print get_text('Normal'); ?> &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '0' onClick = "this.form.frm_set_severity.value=this.value;" CHECKED/></SPAN>
				<SPAN STYLE = "margin-left:20px;"><?php print get_text('Medium'); ?> &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '1' onClick = "this.form.frm_set_severity.value=this.value;"/></SPAN>
				<SPAN STYLE = "margin-left:20px;"><?php print get_text('High'); ?> &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '2' onClick = "this.form.frm_set_severity.value=this.value;"/></SPAN>
			</TD></TR>
<!-- 4/4/2015  -->
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Watch:</TD>	<!-- 3/13/2015  -->
			<TD VALIGN='baseline'>
				<SPAN STYLE = 'margin-left:20px; width:150px;'><B>No &raquo;<INPUT TYPE='radio' NAME="frm_watch" VALUE= "0"  CHECKED /></SPAN>
				<SPAN STYLE = 'margin-left:20px; width:150px;'>Yes &raquo;<INPUT TYPE='radio' NAME="frm_watch" VALUE= "1" /></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Group:</TD>
			<TD><INPUT  ID="ID5" CLASS="dirty" MAXLENGTH="20" SIZE="20" type="text" NAME="frm_group" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='opt' >text</SPAN></TD></TR>

		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Sort:</TD><TD><INPUT ID="ID6" MAXLENGTH=11 SIZE=11 TYPE= "text" NAME="frm_sort" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Radius:</TD><TD><INPUT ID="ID7" MAXLENGTH=4 SIZE=4 TYPE= "text" NAME="frm_radius" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Color:</TD>
			<TD><INPUT ID="ID8" CLASS="dirty" MAXLENGTH="8" SIZE="8" type="text" NAME="frm_color" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='opt' >text</SPAN></TD></TR>

		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Opacity:</TD><TD><INPUT ID="ID9" MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_opacity" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
<?php
		$mg_select = "<SELECT ID='ID10' NAME='frm_notify_mailgroup'>";
		$mg_select .= "<OPTION VALUE=0 SELECTED>Select Mail List</OPTION>";
		$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup`";		// 12/18/10
		$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
			$mg_select .= "<OPTION VALUE=" . $row_mg['id'] . ">" . $row_mg['name'] . "</OPTION>";
			}
		$mg_select .= "</SELECT>";
?>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Notify Mailgroup:</TD><TD><?php print $mg_select;?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Notify Email:</TD><TD><INPUT ID="ID11" MAXLENGTH=256 SIZE=60 TYPE="text" NAME="frm_notify_email" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Notify When:</TD><TD><INPUT ID="ID12" MAXLENGTH=1 SIZE=1 TYPE= "number" min="1" max="3" NAME="frm_notify_when" VALUE=1 onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >1,2 or 3 for All, Open or Close</SPAN></TD></TR>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="reset"		VALUE="Reset"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/>

		</TD></TR>
		</FORM>

		</TD></TR></TABLE>

<?php
