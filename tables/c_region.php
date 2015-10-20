		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="frm_owner" 	VALUE="<?php print $_SESSION['user_id']; ?>" />		

	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Region' - Add New Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Region or Group name:</TD>
		<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="64" SIZE="48" type="text" NAME="frm_group_name" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label ALIGN="right">Group Category:</TD>
				<TD CLASS="td_data"><SELECT NAME="frm_category" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region_type` ORDER BY `id` ASC";		// 12/18/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_cat = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_cat['id']}'>{$row_cat['name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>		
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>
		<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="60" SIZE="48" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Integer</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Area Code:</TD>
		<TD><INPUT ID="ID3" CLASS="dirty" MAXLENGTH="4" SIZE="4" type="text" NAME="frm_def_area_code" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Integer</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def City:</TD>
		<TD><INPUT ID="ID4" CLASS="dirty" MAXLENGTH="20" SIZE="20" type="text" NAME="frm_def_city" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Latitude:</TD>
		<TD><INPUT ID="ID5" CLASS="dirty" MAXLENGTH="10" SIZE="10" type="text" NAME="frm_def_lat" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Double</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Longitude:</TD>
		<TD><INPUT ID="ID6" CLASS="dirty" MAXLENGTH="10" SIZE="10" type="text" NAME="frm_def_lng" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Double</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def State:</TD>
		<TD><INPUT ID="ID7" CLASS="dirty" MAXLENGTH="20" SIZE="20" type="text" NAME="frm_def_st" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Zoom:</TD>
		<TD><INPUT ID="ID8" CLASS="dirty" MAXLENGTH="2" SIZE="2" type="text" NAME="frm_def_zoom" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Integer</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Boundary:</TD>
				<TD CLASS="td_data"><SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_r` = 1 ORDER BY `line_name` ASC";		// 12/18/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>		
		<tr><td colspan=99 align='center'>
		</td></tr>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"				VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this );"/> 
		</TD></TR>
		</FORM>
		</TD></TR></TABLE>

<?php
