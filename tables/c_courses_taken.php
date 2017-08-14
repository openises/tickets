<?php
/*
12/10/11 - initial release
12/19/11 'group' => group_name
*/
?>
		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>"/>
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> 

		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'courses_taken' - Add New Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Course:</TD>		<TD><SELECT NAME='frm_courses_id'>
		<OPTION VALUE='0' selected>Select one</OPTION>
<?php
	$query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]courses` ORDER BY `group_name` ASC, `course` ASC";    			
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ($the_grp != $row['group_name']) {
			echo (intval($i) == 0)? "": "\t</OPTGROUP>\n";
			$the_grp = $row['group_name'];
			echo "\t\t<OPTGROUP LABEL='$the_grp'>\n";
			}	
		echo "\t\t<OPTION VALUE='{$row['id']}'>{$row['course']}</OPTION>\n";
		$i++;		
		}				// end while()
?>
		</OPTGROUP></SELECT></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">User:</TD>		<TD><SELECT NAME='frm_user_id'>
		<OPTION VALUE='0' selected>Select one</OPTION>
<?php
	$query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]user` WHERE ((`name_l` IS NOT NULL) AND (LENGTH(`name_l`) > 0)) ORDER BY `name_l` ASC, `name_f` ASC";    			
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		echo "\t\t<OPTION VALUE='{$row['id']}'>{$row['name_l']}, {$row['name_f']} {$row['name_mi']} ({$row['user']})</OPTION>\n";
		}				// end while()
	
	$date_now = date ("Y-m-d", now());		// today as default
?>
		</SELECT></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Date:</TD>
		<TD><INPUT  ID="ID3" CLASS="dirty" MAXLENGTH="10" SIZE="10" type="text" NAME="frm_date" VALUE="<?php echo $date_now;?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Info:</TD>
		<TD><INPUT  ID="ID4" CLASS="dirty" MAXLENGTH="64" SIZE="64" type="text" NAME="frm_info" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
			<INPUT ID="fd5" type="hidden" NAME="frm__by" VALUE="1" />
		<INPUT ID="fd6" type="hidden" NAME="frm__from" VALUE="127.0.0.1" />
		<INPUT ID="fd7" type="hidden" NAME="frm__on" VALUE="2011-12-10 22:20:58" />
		<TR>
			<TD COLSPAN="99" ALIGN="center">
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r';document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.c.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnCheckInput(document.c, this );"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</TD>
		</TR>
		</FORM>
		</TD></TR></TABLE>

<?php
