		<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pu" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'unit_types' - Update Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Region or Group name:</TD>
		<TD><INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="16" SIZE="16" type="text" NAME="frm_group_name" VALUE="<?php print $row['group_name'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label ALIGN="right">Group Category:</TD>
				<TD CLASS="td_data"><SELECT NAME="frm_category" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region_type` ORDER BY `id` ASC";		// 12/18/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_cat = stripslashes_deep(mysql_fetch_assoc($result))) {
					$sel = ($row['category'] == $row_cat['id']) ? "SELECTED" : "";
					print "\t<OPTION {$sel} VALUE='{$row_cat['id']}'>{$row_cat['name']} </OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>		
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>
		<TD><INPUT  ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_description" VALUE="<?php print $row['description'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Area Code:</TD>
		<TD><INPUT ID="ID3" CLASS="dirty" MAXLENGTH="4" SIZE="4" type="text" NAME="frm_def_area_code" VALUE="<?php print $row['def_area_code'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Integer</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def City:</TD>
		<TD><INPUT ID="ID4" CLASS="dirty" MAXLENGTH="20" SIZE="20" type="text" NAME="frm_def_city" VALUE="<?php print $row['def_city'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Latitude:</TD>
		<TD><INPUT ID="ID5" CLASS="dirty" MAXLENGTH="10" SIZE="10" type="text" NAME="frm_def_lat" VALUE="<?php print $row['def_lat'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Double</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Longitude:</TD>
		<TD><INPUT ID="ID6" CLASS="dirty" MAXLENGTH="10" SIZE="10" type="text" NAME="frm_def_lng" VALUE="<?php print $row['def_lng'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Double</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def State:</TD>
		<TD><INPUT ID="ID7" CLASS="dirty" MAXLENGTH="20" SIZE="20" type="text" NAME="frm_def_st" VALUE="<?php print $row['def_st'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Def Zoom:</TD>
		<TD><INPUT ID="ID8" CLASS="dirty" MAXLENGTH="2" SIZE="2" type="text" NAME="frm_def_zoom" VALUE="<?php print $row['def_zoom'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >Integer</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label ALIGN="right">Boundary:</TD>
				<TD CLASS="td_data"><SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_r` = 1 ORDER BY `line_name` ASC";		// 12/18/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
					$sel = ($row['boundary'] == $row_bound['id']) ? "SELECTED" : "";				
					print "\t<OPTION {$sel} VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>		
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this );"/> 
		
		</TD></TR>
		</FORM>
		</td></tr></table>

<?php
