<?php
/*
2/9/10 - initial release
5/25/10 - Changed display of hide value to what exists in database
3/13/2015 - added 'watch' attribute handling
*/
?>
<SCRIPT>
	function set_bg_vals (the_form) {
		the_form.frm_bg_color.value = the_form.dmy_status_id.options[document.u.dmy_status_id.selectedIndex].style.backgroundColor;
		the_form.frm_text_color.value = the_form.dmy_status_id.options[document.u.dmy_status_id.selectedIndex].style.color;
		}

	function do_reset(the_form) {
		the_form.frm_bg_color.value=the_form.def_bg_color.value; 		// reset form values
		the_form.frm_text_color.value=the_form.def_text_color.value;
//		alert();
		the_form.dmy_status_id.style.backgroundColor=the_form.def_bg_color.value; 	// reset <select> display
		the_form.dmy_status_id.style.color=the_form.def_text_color.value;

		the_form.reset();
		}		// end function do_reset()


</SCRIPT>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename;?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print $row['id'] ;?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->

	<INPUT TYPE="hidden" NAME="frm_bg_color"  	VALUE="<?php print $row['bg_color'] ;?>" />
	<INPUT TYPE="hidden" NAME="frm_text_color"	VALUE="<?php print $row['text_color'] ;?>" />

	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'Unit Status' - Update/Delete Entry</FONT></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">ID:</TD>
		<TD><INPUT MAXLENGTH=bigint(4) SIZE=bigint(4) TYPE= "text" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" onChange = "this.value=JSfnTrim(this.value)" disabled/> <SPAN class='warn' >numeric</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Status val:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_status_val" VALUE="<?php print $row['status_val'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
<?php

	$check_0 = $check_1 = $check_2 = "";
	switch ($row['dispatch']) {
		case 0: $check_0 = " CHECKED "; 	break;
		case 1: $check_1 =  " CHECKED "; 	break;
		case 2: $check_2 =  " CHECKED "; 	break;
		default: dump(__LINE__); 			dump($row['dispatch']);
		}
?>
	<TR VALIGN="bottom" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Can dispatch:</TD>
		<TD><SPAN STYLE = 'margin-left:20px'><B>Yes &raquo;<INPUT TYPE='radio' NAME="frm_dispatch" VALUE= 0  <?php print $check_0;?> /></SPAN>
			<SPAN STYLE = 'margin-left:20px;'>No - not enforced  &raquo;<INPUT TYPE='radio' NAME="frm_dispatch" VALUE= "1" <?php print $check_1;?>/></SPAN>
			<SPAN STYLE = 'margin-left:20px'>No - enforced &raquo;<INPUT TYPE='radio' NAME="frm_dispatch" VALUE= "2" <?php print $check_2;?>/></SPAN>
			</TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Description:</TD>
		<TD><INPUT MAXLENGTH="60" SIZE="60" type="text" NAME="frm_description" VALUE="<?php print $row['description'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>

<?php
	switch($row['hide']) {
		case "n":	$checked1_value = "CHECKED"; 	$checked2_value = ""; 			break;
		case "y":	$checked1_value = ""; 			$checked2_value = "CHECKED"; 	break;
		default: 	$checked1_value = "CHECKED"; 	$checked2_value = "";
		}
?>
	<TR VALIGN="baseline" CLASS="odd">
		<TD CLASS="td_label" ALIGN="right">Hide:</TD>
		<TD VALIGN='baseline'><B>
			<SPAN STYLE = 'margin-left:20px;'>No &raquo; <INPUT TYPE='radio' NAME="frm_hide" VALUE= "n" <?php print $checked1_value;?>/>
			<SPAN STYLE = 'margin-left:20px;'>Yes &raquo; <INPUT TYPE='radio' NAME="frm_hide" VALUE= "y" <?php print $checked2_value;?>/>
			</TD></TR>
<?php
	switch($row['excl_from_reset']) {
		case "n":	$checked1_value = "CHECKED"; 	$checked2_value = ""; 			break;
		case "y":	$checked1_value = ""; 			$checked2_value = "CHECKED"; 	break;
		default: 	$checked1_value = "CHECKED"; 	$checked2_value = "";
		}
?>
	<TR VALIGN="baseline" CLASS="odd">
		<TD CLASS="td_label" ALIGN="right">Exclude from Status Reset:</TD>
		<TD VALIGN='baseline'><B>
			<SPAN STYLE = 'margin-left:20px;'>No &raquo; <INPUT TYPE='radio' NAME="frm_excl_from_reset" VALUE= "n" <?php print $checked1_value;?>/>
			<SPAN STYLE = 'margin-left:20px;'>Yes &raquo; <INPUT TYPE='radio' NAME="frm_excl_from_reset" VALUE= "y" <?php print $checked2_value;?>/>
			</TD></TR>

<?php						// 3/13/2015
	switch($row['watch']) {
		case "0":	$checked1_value = "CHECKED"; 	$checked2_value = ""; 			break;
		default: 	$checked1_value = ""; 			$checked2_value = "CHECKED";
		}
?>
	<TR VALIGN="baseline" CLASS="even">
		<TD CLASS="td_label" ALIGN="right">Watch:</TD>
		<TD VALIGN='baseline'><B>
			<SPAN STYLE = 'margin-left:20px;'>No &raquo; <INPUT TYPE='radio' NAME="frm_watch" VALUE= "0" <?php print $checked1_value;?>/>
			<SPAN STYLE = 'margin-left:20px;'>Yes &raquo; <INPUT TYPE='radio' NAME="frm_watch" VALUE= "1" <?php print $checked2_value;?>/>
			</TD></TR>

	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Group:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_group" VALUE="<?php print $row['group'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Sort:</TD><TD><INPUT MAXLENGTH=int(11) SIZE=int(11) TYPE= "text" NAME="frm_sort" VALUE="<?php print $row['sort'] ;?>" onChange = "this.value=JSfnTrim(this.value)"/> <SPAN class='opt' >numeric</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Background color:</TD>
			<TD>
				<SELECT name='dmy_status_id' STYLE='background-color:<?php print $row['bg_color']; ?>; color:<?php print $row['text_color']; ?>;' ONCHANGE =  "set_bg_vals (this.form);this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;">
<?php $sel = (strtolower($row['bg_color']) == strtolower("transparent"))? "SELECTED" : ""; ?>
				<OPTION VALUE=0 STYLE='background-color:transparent; 	color:black;'  <?php print $sel; ?> >None</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("maroon"))? "SELECTED" : ""; ?>
				<OPTION VALUE=1 STYLE='background-color:maroon; 		color:white;'  <?php print $sel; ?> >Maroon</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("red"))? "SELECTED" : ""; ?>
				<OPTION VALUE=2 STYLE='background-color:red; 			color:white;'  <?php print $sel; ?> >Red</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("orange"))? "SELECTED" : ""; ?>
				<OPTION VALUE=3 STYLE='background-color:orange; 		color:black;'  <?php print $sel; ?> >Orange</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("tan"))? "SELECTED" : ""; ?>
				<OPTION VALUE=4 STYLE='background-color:tan; 			color:black;'  <?php print $sel; ?> >Tan</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("yellow"))? "SELECTED" : ""; ?>
				<OPTION VALUE=5 STYLE='background-color:yellow; 		color:black;'  <?php print $sel; ?> >Yellow</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("olive"))? "SELECTED" : ""; ?>
				<OPTION VALUE=6 STYLE='background-color:olive; 			color:white;'  <?php print $sel; ?> >Olive</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("purple"))? "SELECTED" : ""; ?>
				<OPTION VALUE=7 STYLE='background-color:purple; 		color:white;'  <?php print $sel; ?> >Purple</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("fuchsia"))? "SELECTED" : ""; ?>
				<OPTION VALUE=8 STYLE='background-color:fuchsia; 		color:white;'  <?php print $sel; ?> >Fuchsia</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("HotPink"))? "SELECTED" : ""; ?>
				<OPTION VALUE=9 STYLE='background-color:HotPink; 		color:black;'  <?php print $sel; ?> >HotPink</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("pink"))? "SELECTED" : ""; ?>
				<OPTION VALUE=10 STYLE='background-color:pink; 			color:black;'  <?php print $sel; ?> >Pink</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("green"))? "SELECTED" : ""; ?>
				<OPTION VALUE=11 STYLE='background-color:green; 		color:white;'  <?php print $sel; ?> >Green</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("PaleGreen"))? "SELECTED" : ""; ?>
				<OPTION VALUE=12 STYLE='background-color:PaleGreen; 	color:black;'  <?php print $sel; ?> >PaleGreen</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("lime"))? "SELECTED" : ""; ?>
				<OPTION VALUE=13 STYLE='background-color:lime; 			color:black;'  <?php print $sel; ?> >Lime</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("aqua"))? "SELECTED" : ""; ?>
				<OPTION VALUE=14 STYLE='background-color:aqua; 			color:black;'  <?php print $sel; ?> >Aqua</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("black"))? "SELECTED" : ""; ?>
				<OPTION VALUE=15 STYLE='background-color:black; 		color:white;'  <?php print $sel; ?> >Black</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("silver"))? "SELECTED" : ""; ?>
				<OPTION VALUE=16 STYLE='background-color:silver; 		color:black;'  <?php print $sel; ?> >Silver</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("SlateGray"))? "SELECTED" : ""; ?>
				<OPTION VALUE=17 STYLE='background-color:SlateGray; 	color:white;'  <?php print $sel; ?> >SlateGray</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("navy"))? "SELECTED" : ""; ?>
				<OPTION VALUE=18 STYLE='background-color:navy; 			color:white;'  <?php print $sel; ?> >Navy</OPTION>
<?php $sel = (strtolower($row['bg_color']) == strtolower("blue"))? "SELECTED" : ""; ?>
				<OPTION VALUE=19 STYLE='background-color:blue; 			color:white;'  <?php print $sel; ?> >Blue</OPTION>
				</SELECT>
			</TD></TR>
		<TR>
			<TD COLSPAN="99" ALIGN="center">
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_reset(document.u); "><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnCheckInput(document.u, this );"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN id='del_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="if (confirm('Please confirm DELETE action')) {this.form.func.value='d'; this.form.submit();}"><SPAN STYLE='float: left;'><?php print get_text("Delete");?></SPAN><IMG STYLE='float: right;' SRC='./images/delete.png' BORDER=0></SPAN>
			</TD>
		</TR>
	<INPUT TYPE="hidden" NAME="def_bg_color"  	VALUE="<?php print $row['bg_color'];?>" /> <!-- default values see reset button -->
	<INPUT TYPE="hidden" NAME="def_text_color"	VALUE="<?php print $row['text_color'];?>" />

	</FORM>
	</TD></TR></TABLE>
<?php
