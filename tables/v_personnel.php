<SCRIPT>
function validate_form(myform) {	// reject empty form elements
	myform.frm_date_of_birth.value = myform.frm_year_date_of_birth.value + "-" + myform.frm_month_date_of_birth.value + "-" + myform.frm_day_date_of_birth.value + " 00:00:00";
	myform.submit();		
	}				// end function 
</SCRIPT>
		<FORM NAME="v" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pu" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="frm__by" 	VALUE="<?php print $_SESSION['user_id']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__on" 	VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>" />
		<INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Personnel' - View Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Surname:</TD>
			<TD><?php print $row['surname'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Fornames:</TD>
			<TD><?php print $row['forenames'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Address:</TD>
			<TD><?php print $row['address'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">State:</TD>
			<TD><?php print $row['state'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Date of Birth:</TD>
			<TD><?php print format_date_2(strtotime($row['date_of_birth']));?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Gender:</TD>
			<TD><?php print $row['gender'];?></SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Identifier:</TD>
			<TD><?php print $row['person_identifier'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Email:</TD>
			<TD><?php print $row['email'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Home phone:</TD>
			<TD><?php print $row['homephone'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Work phone:</TD>
			<TD><?php print $row['workphone'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Cellphone:</TD>
			<TD><?php print $row['cellphone'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Next of Kin Name:</TD>
			<TD><?php print $row['next_of_kin_name'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Next of Kin Address:</TD>
			<TD><?php print $row['next_of_kin_address'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Next of Kin Home phone:</TD>
			<TD><?php print $row['next_of_kin_homephone'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Next of Kin Work phone:</TD>
			<TD><?php print $row['next_of_kin_workphone'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Next of Kin Cellphone:</TD>
			<TD><?php print $row['next_of_kin_cellphone'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">AR Callsign:</TD>
			<TD><?php print $row['amateur_radio_callsign'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Notes:</TD>
			<TD><?php print $row['person_notes'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Capabilities:</TD>
			<TD><?php print $row['person_capabilities'];?></TD></TR>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />


<?php
