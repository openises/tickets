<SCRIPT>
	var prepend = "./markers/";
	var icons = new Array( prepend+"sm_white.png", prepend+"sm_yellow.png", prepend+"sm_red.png", prepend+"sm_blue.png", prepend+"sm_green.png", prepend+"sm_gray.png", prepend+"sm_lt_blue.png", prepend+"sm_orange.png");
</SCRIPT>
		<FORM NAME="c" METHOD="post" ACTION="/tickets_2_9_X/tables_x.php">
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="unit_types"/>
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="frm_by" 		VALUE="<?php print $my_session['user_id']; ?>"/>
		<INPUT TYPE="hidden" NAME="frm_from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>"/>
		<INPUT TYPE="hidden" NAME="frm_on" 		VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>"/>
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'unit_types' - Add New Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Type:</TD>
		<TD><INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="16" SIZE="16" type="text" NAME="frm_name" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>
		<TD><INPUT  ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Icon:</TD>
		<TD><INPUT  ID="ID3" CLASS="dirty" MAXLENGTH="7" SIZE="7" type="text" NAME="frm_icon" VALUE="" DISABLED> <SPAN class='warn' >click icon to select</SPAN></TD></TR>
		<TR CLASS="even"><TD></TD><TD ALIGN='center'>
			<IMG SRC="./markers/sm_white.png" onClick = 	document.c.frm_icon.disabled=false;document.c.frm_icon.value=1;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_yellow.png" onClick = 	document.c.frm_icon.disabled=false;document.c.frm_icon.value=2;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_red.png" onClick = 		document.c.frm_icon.disabled=false;document.c.frm_icon.value=3;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_blue.png" onClick = 		document.c.frm_icon.disabled=false;document.c.frm_icon.value=4;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_green.png" onClick = 	document.c.frm_icon.disabled=false;document.c.frm_icon.value=5;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_gray.png" onClick = 		document.c.frm_icon.disabled=false;document.c.frm_icon.value=6;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_lt_blue.png" onClick = 	document.c.frm_icon.disabled=false;document.c.frm_icon.value=7;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
			<IMG SRC="./markers/sm_orange.png" onClick = 	document.c.frm_icon.disabled=false;document.c.frm_icon.value=8;document.c.frm_icon.disabled=true;>&nbsp;&nbsp;
	

		</TD></TR>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="reset"		VALUE="Reset"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/> 
		<input type = button value="click" onclick ="do_icons(3)">
		</TD></TR>
		</FORM>
		</td></tr></table>
	<!-- ----------Common--------------- -->

<?php
