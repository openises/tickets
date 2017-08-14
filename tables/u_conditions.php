<?php
function read_directory($directory) {
	$the_ret = array();
	$dirhandler = opendir($directory);
	$i=0;
	while ($file = readdir($dirhandler)) {
		if ($file != '.' && $file != '..') {
			$i++;
			$the_ret[$i]=$file;                
		}   
	}
    closedir($dirhandler);
	return $the_ret;
	}

$theIcons = array();
$theDirectory = getcwd().'/rm/roadinfo_icons/';
$theIcons = read_directory($theDirectory); 
?>
		<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pu" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="frm__by" 	VALUE="<?php print $_SESSION['user_id']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__on" 	VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>" />
		<INPUT TYPE="hidden" NAME="frm_icon" 	VALUE="<?php print $row['icon'];?>" />
		<INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Conditions' - Update Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Type name:</TD>
		<TD><INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="16" SIZE="16" type="text" NAME="frm_title" VALUE="<?php print $row['title'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>
		<TD><INPUT  ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_description" VALUE="<?php print $row['description'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Icon:</TD>
		<TD><IMG ID='ID3' SRC="<?php print './rm/roadinfo_icons/' . $row['icon'];?>"></TD></TR>
	<TR CLASS="even"><TD></TD><TD ALIGN='center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<SCRIPT>
	var theIcons = new Array();
<?php
	$z = 0;
	foreach($theIcons AS $val) {
		print "\ttheIcons[" . $z . "] = '" . $val . "'\n";	
		$z++;
		}
?>
	var the_dir = "./rm/roadinfo_icons/";	
	
	function theicon_to_form(the_icon) {						// 12/31/08
		var the_img = $('ID3');
		document.forms[1].frm_icon.value=the_icon;			// icon index to form variable
		$('ID3').src = the_dir + the_icon;		
		$('ID3').style.visibility = "visible";				// initially hidden for 'create'
		return;
		}				
	
	function gen_the_img(the_icon) {						// returns image string for nth icon
		var the_sm_image = the_icon;
		var the_image = the_dir + the_icon;
		var the_title = the_icon;	// extract color name
		return "<IMG SRC='" + the_image + "' onClick  = 'theicon_to_form(\"" + the_sm_image + "\")' TITLE='" + the_title +"' />";
		}

			for (i=0; i<theIcons.length; i++) {						// generate icons display
				document.write(gen_the_img(theIcons[i])+"&nbsp;&nbsp;\n");
				}
</SCRIPT>
			&laquo; <SPAN class='warn'>click to change icon </SPAN> &nbsp;
		</TD></TR>
		<TR>
			<TD COLSPAN="99" ALIGN="center">
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r';document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.u.reset();icon_to_form('<?php print $row['icon'];?>'); "><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnCheckInput(document.u, this );"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</TD>
		</TR>

		</FORM>
		</td></tr></table>

<?php
