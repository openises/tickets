		<FORM NAME="v" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Conditions' - View Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Type name:</TD>	<TD><?php print $row['title'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>	<TD><?php print $row['description'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Icon:</TD>			<TD><IMG ID='ID3' SRC="<?php print './rm/roadinfo_icons/' . $row['icon'];?>"></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">By:</TD>			<TD><?php print get_owner($row['_by']);?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">From:</TD>			<TD><?php print $row['_from'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">On:</TD>			<TD><?php print $row['_on'];?></TD></TR>

<?php
