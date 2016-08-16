	<FORM NAME="v" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
<?php
function get_mailgroup_name($id) {	//	8/28/13
	if($id == 0) {
		return "";
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_ret = $row['name'];
	return $the_ret;
	}
	
function get_oswatch($id) {	//	8/28/13
	$the_ret = ($id==0) ? "No" : "Yes";
	return $the_ret;
	}
?>
<TABLE BORDER="0" ALIGN="center" ><TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Incident types' - View Entry</FONT></TD></TR><TR><TD>&nbsp;</TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">ID:</TD>			<TD><?php print $row['id'];?></TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Type:</TD>		<TD><?php print $row['type'];?></TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Description:</TD>	<TD><?php print $row['description'];?></TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Protocol:</TD>	<TD><?php print $row['protocol'];?></TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Severity:</TD>	<TD><?php print get_severity($row['set_severity']); ?> </TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Watch:</TD>	<TD><?php print get_oswatch($row['watch']); ?> </TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Group:</TD>		<TD><?php print $row['group'];?></TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Sort:</TD>		<TD><?php print $row['sort'];?></TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Radius:</TD>		<TD><?php print $row['radius'];?></TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Color:</TD>		<TD><?php print $row['color'];?></TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Opacity:</TD><TD><?php print $row['opacity'];?></TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Notify Mailgroup:</TD><TD><?php print get_mailgroup_name($row['notify_mailgroup']);?></TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label" ALIGN="right">Notify Email:</TD><TD><?php print $row['notify_email'];?></TD></TR>
	<TR CLASS="even" VALIGN="top"><TD CLASS="td_label" ALIGN="right">Notify When:</TD><TD><?php print $row['notify_when'] ;?></TD></TR>
	<TR><TD COLSPAN="2" ALIGN="center">
	<BR />
<?php
//	7/11/10 corrections to imclude $row values
