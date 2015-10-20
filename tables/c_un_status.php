<?php
/*
2/9/10 - initial release
5/25/10 - Changed default value of radio buttons for hide value to "n"
3/13/2015 - added 'watch' attribute handling
*/
?>
<SCRIPT>
	function set_bg_vals (the_form) {
		the_form.frm_bg_color.value = the_form.dmy_status_id.options[document.c.dmy_status_id.selectedIndex].style.backgroundColor;
		the_form.frm_text_color.value = the_form.dmy_status_id.options[document.c.dmy_status_id.selectedIndex].style.color;
		}
</SCRIPT>
		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>">
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="un_status"/>
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>

		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Unit Status' - Add New Entry</FONT></TD></TR>
		<TR><TD COLSPAN=4>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Status:</TD>
			<TD><INPUT  ID="ID1"  MAXLENGTH="20" SIZE="20" type="text" NAME="frm_status_val" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"></TD>
			</TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Can dispatch:</TD>
			<TD VALIGN='baseline'><SPAN STYLE = 'margin-left:20px'><B>Yes &raquo;<INPUT TYPE='radio' NAME="frm_dispatch" VALUE= 0  CHECKED /></SPAN>
			<SPAN STYLE = 'margin-left:20px'>No - not enforced &raquo;<INPUT TYPE='radio' NAME="frm_dispatch" VALUE= "1" /></SPAN>
			<SPAN STYLE = 'margin-left:20px'>No - enforced &raquo;<INPUT TYPE='radio' NAME="frm_dispatch" VALUE= "2" /></SPAN>
			</TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Description:</TD>
			<TD><INPUT  ID="ID2"  MAXLENGTH="60" SIZE="60" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Hide:</TD>
					<TD VALIGN='baseline'><SPAN STYLE = 'margin-left:20px'><B>No &raquo;<INPUT TYPE='radio' NAME="frm_hide" VALUE= "n"  CHECKED /></SPAN>
						<SPAN STYLE = 'margin-left:20px'>Yes &raquo;<INPUT TYPE='radio' NAME="frm_hide" VALUE= "y" /></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Watch:</TD>	<!-- 3/13/2015  -->
					<TD VALIGN='baseline'><SPAN STYLE = 'margin-left:20px'><B>No &raquo;<INPUT TYPE='radio' NAME="frm_watch" VALUE= "0"  CHECKED /></SPAN>
						<SPAN STYLE = 'margin-left:20px'>Yes &raquo;<INPUT TYPE='radio' NAME="frm_watch" VALUE= "1" /></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Group:</TD>
			<TD><INPUT  ID="ID4"  MAXLENGTH="20" SIZE="20" type="text" NAME="frm_group" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Sort:</TD><TD><INPUT ID="ID5" MAXLENGTH=11 SIZE=11 TYPE= "text" NAME="frm_sort" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/> </TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Background color:</TD>
			<TD>
				<SELECT name='dmy_status_id' STYLE='background-color:transparent; color:black;' ONCHANGE =  "set_bg_vals (this.form);this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;">
				<OPTION VALUE=0 STYLE='background-color:transparent; 	color:black;' SELECTED>None</OPTION>
				<OPTION VALUE=1 STYLE='background-color:maroon; 		color:white;' >Maroon</OPTION>
				<OPTION VALUE=2 STYLE='background-color:red; 			color:white;' >Red</OPTION>
				<OPTION VALUE=3 STYLE='background-color:orange; 		color:black;' >Orange</OPTION>
				<OPTION VALUE=4 STYLE='background-color:tan; 			color:black;' >Tan</OPTION>
				<OPTION VALUE=5 STYLE='background-color:yellow; 		color:black;' >Yellow</OPTION>
				<OPTION VALUE=6 STYLE='background-color:olive; 			color:white;' >Olive</OPTION>
				<OPTION VALUE=7 STYLE='background-color:purple; 		color:white;' >Purple</OPTION>
				<OPTION VALUE=8 STYLE='background-color:fuchsia; 		color:white;' >Fuchsia</OPTION>
				<OPTION VALUE=9 STYLE='background-color:HotPink; 		color:black;' >HotPink</OPTION>
				<OPTION VALUE=10 STYLE='background-color:pink; 			color:black;' >Pink</OPTION>
				<OPTION VALUE=12 STYLE='background-color:green; 		color:white;' >Green</OPTION>
				<OPTION VALUE=13 STYLE='background-color:PaleGreen; 	color:black;' >PaleGreen</OPTION>
				<OPTION VALUE=14 STYLE='background-color:lime; 			color:black;' >Lime</OPTION>
				<OPTION VALUE=15 STYLE='background-color:aqua; 			color:black;' >Aqua</OPTION>
				<OPTION VALUE=16 STYLE='background-color:black; 		color:white;' >Black</OPTION>
				<OPTION VALUE=17 STYLE='background-color:silver; 		color:black;' >Silver</OPTION>
				<OPTION VALUE=18 STYLE='background-color:SlateGray; 	color:white;' >SlateGray</OPTION>
				<OPTION VALUE=19 STYLE='background-color:navy; 			color:white;' >Navy</OPTION>
				<OPTION VALUE=20 STYLE='background-color:blue; 			color:white;' >Blue</OPTION>
				</SELECT>
			</TD></TR>
			<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="reset"		VALUE="Reset"  onClick = "this.form.frm_bg_color.value=this.form.def_bg_color.value; this.form.frm_text_color.value=this.form.def_text_color.value; this.form.reset()"; />&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/>
		<INPUT TYPE="hidden" NAME="frm_bg_color"  	VALUE="transparent" />
		<INPUT TYPE="hidden" NAME="frm_text_color"	VALUE="black" />
		<INPUT TYPE="hidden" NAME="def_bg_color"  	VALUE="transparent" /> <!-- default values see reset button -->
		<INPUT TYPE="hidden" NAME="def_text_color"	VALUE="black" />

		</TD></TR>
		</FORM>
		</TD></TR>
		</TABLE>

<?php
