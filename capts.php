<?php
ob_start();							// 8/28/10

$cols = 6;	// no. of columns in the list presentation

/*
8/21/10 initial release
1/28/11 addslashes applied vice 'escape'
3/15/11 changed stylesheet.php to stylesheet.php
3/19/11 added edit allow test
5/26/11 added SQL inject prevention 
6/26/11 rewrote restore function to copy 'capt' to 'repl' for all records
5/1/12 input size change
*/
error_reporting(E_ALL);				

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10
//do_login(basename(__FILE__));

if ($istest) {
	if (!empty($_GET)) {
		print "GET<BR/>\n";
		dump ($_GET);
		}
	if (!empty($_POST)) {
		print "POST<BR/>\n";
		dump ($_POST);
		}
	}

$func = (empty($_POST))? "l":$_POST['func'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Captions processor</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css" />
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
	function do_edit(in_id) {
<?php								// 3/19/11
		if ( (is_administrator()) || (is_super()) ) {
?>
		document.to_edit_form.frm_id.value=in_id;
		document.to_edit_form.submit();	
<?php
	}
?>
		}
</SCRIPT>
</HEAD>
<BODY>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

	<FORM NAME = 'to_edit_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE ='hidden' NAME = 'frm_id' VALUE='' />
		<INPUT TYPE ='hidden' NAME = 'func' VALUE='e' />
		</FORM>
	<FORM NAME = 'can_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE ='hidden' NAME = 'func' VALUE='l' />
	</FORM>
	<FORM NAME = 'to_Cfg_form' METHOD = 'post' ACTION = 'config.php'>
	</FORM>
	<FORM NAME = 'do_restore_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE ='hidden' NAME = 'func' VALUE='r' />
		</FORM>
<?php 
	switch ($func) {
		case "u" :			// update
			$the_repl = quote_smart(trim($_POST['frm_repl'])) ;
			$query = "UPDATE `$GLOBALS[mysql_prefix]captions` SET `repl` = {$the_repl} WHERE `id` = " . quote_smart($_POST['frm_id']) . " LIMIT 1;";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

			$outstr = urlencode("Update applied!");
			header("Location:capts.php?caption={$outstr}");
			break;

		case "l" :	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` ORDER BY `capt` ASC ";		
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$rows =  mysql_affected_rows(); 			// Could be a mysql_num_rows() as well
			$j = 1;
			$perCol = (integer)(ceil($rows/$cols)); 			// How many items per col
			$colors = array ('odd', 'even');
	
			$i=0;
																		// outer table
			echo "<TABLE ID='outer' ALIGN='center' CELLPADDING = 4 >";
			$notice = (array_key_exists('caption', $_GET))? $_GET['caption']: "";
			echo "<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'><B><I>{$notice}</I></B></TD></TR>\n";
			echo "<TR CLASS='even'><TD COLSPAN=99 ALIGN='center'><H3>Click <u>caption</u> to edit</H3></TD></TR>\n";
			echo "<TR style='vertical-align: top;'><TD style='vertical-align: top;'>";
			$out_str = "<TABLE ALIGN='center' VALIGN='top' border=0>\n";
		
			$out_str .=  "<TR CLASS='odd'><TD><B>&nbsp;&nbsp;Caption</B></TD><TD><B>&nbsp;&nbsp;Replacement</B></TD></TR>\n";
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$i++;
				$capt_val = shorten($row['capt'], 16);
				$repl_val = shorten($row['repl'], 16);												// 1/28/11
				$out_str .=  "<TR CLASS = '{$colors[$i%2]}' onClick = 'do_edit({$row['id']});'>
					<TD CLASS='td_label text' onMouseover=\"Tip('" . addslashes($row['capt']) . "');\" onmouseout=\"UnTip();\">{$capt_val}</TD>
					<TD CLASS='td_data text' onMouseover=\"Tip('" . addslashes($row['repl']) . "');\" onmouseout=\"UnTip();\">{$repl_val}</TD>
					</TR>\n";
				if ($i == $perCol){
					$i=0;
					$out_str .=  "</TABLE>\n";
					echo $out_str;
					echo "</TD><TD style='vertical-align: top;'>";		// outer table
					$out_str = "\n<TABLE BORDER=0 ALIGN='center' VALIGN='top'>";
					$out_str .=  "<TR CLASS='odd'><TD><B>&nbsp;&nbsp;Caption</B></TD><TD><B>&nbsp;&nbsp;Replacement</B></TD></TR>\n";
					}
				$j++;
				}		// end while()
			$out_str .=  "</TABLE>";
			echo $out_str;
			echo "</TD></TR>";
			if (is_super() || is_administrator()) {										// 3/19/11
			
?>
			<TR CLASS='odd'>
				<TD COLSPAN=99 ALIGN='center'>
					<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.to_Cfg_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
					<SPAN id='restore_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id); Tip("Restore default captions");' onMouseout='do_plain(this.id); UnTip();' onClick="if(confirm('Click OK to restore all original captions')){document.do_restore_form.submit();}"><SPAN STYLE='float: left;'><?php print get_text("Restore");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				</TD>
			</TR>			
			</TABLE><!-- outer table -->
<?php
				}
			break;

		case "e" :			// edit
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `id` = " . quote_smart($_POST['frm_id']) . " LIMIT 1";		
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row =  stripslashes_deep(mysql_fetch_array($result));
?>
			<FORM NAME = 'editForm' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
			<TABLE ALIGN='center' STYLE = 'margin-top:60px'>
				<TR CLASS='even' VALIGN = 'bottom'><TH COLSPAN=2>Enter caption change</TH></TR>
				<TR CLASS='odd' VALIGN = 'bottom'><TD COLSPAN=2>&nbsp;</TD></TR>
				
				<TR CLASS='odd' VALIGN='baseline'>
					<TD><?php print $row['capt'];?>:&nbsp;</TD>
					<TD><INPUT TYPE = "text" NAME = "frm_repl" VALUE="<?php print $row['repl'];?>" size = 64></TD>	<!-- 8/30/10 , 5/1/12-->
					</TR>
				<TR CLASS='odd'>
					<TD COLSPAN='2'>&nbsp;</TD>
				</TR>
				<TR CLASS='even' VALIGN='baseline'>
					<TD COLSPAN='2'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD COLSPAN=2 ALIGN='center'>
						<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.can_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
						<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.editForm.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN id='sub_but' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.editForm.submit();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
					</TD>
				</TR>
			</TABLE>
			<INPUT TYPE ='hidden' NAME = 'frm_id' VALUE='<?php print $_POST['frm_id'];?>' />
			<INPUT TYPE ='hidden' NAME = 'func' VALUE='u' />
			</FORM>
<?php
			break;

		case "r" :			// restore defaults
			$the_table = "$GLOBALS[mysql_prefix]captions";
 			$query = "UPDATE `{$the_table}` SET `repl` = `capt`;";			// 6/26/11

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			$outstr = urlencode( "Entries restored to original values");
			header("Location:capts.php?caption={$outstr}");
			break;

		default :
			echo "ERROR - ERROR - ERROR" . __LINE__; 
		}	// end switch 
	
?>
</BODY>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
	
set_fontsizes(viewportwidth, "fullscreen");
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
listHeight = viewportheight * .25;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .5;
listwidth = colwidth;
</SCRIPT>
</HTML>
