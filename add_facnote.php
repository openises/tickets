<?php
/*
8/10/09	initial release
1/27/10 corrections applied to update field
3/16/10 ceck for empty note
7/12/10 <br. -> '\n'
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
12/1/10 added get_text(disposition)
3/15/11 changed stylesheet.php to stylesheet.php
1/7/2013 added user ident to inserted string, strip_tags as XSS prevention
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10


if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Add Facility Note to Existing Incident</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;
var colwidth;
var colheight;

String.prototype.trim = function () {				// 3/16/10
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};
function validate () {
	document.frm_facnote.submit();	
	}
</SCRIPT>
</HEAD>
<?php
if (empty($_POST)) { 
	$origin = "";
	$destination = "";
	$type = "";
	$eta = "";
	$notes = "";
	$ticket_id = $_GET['ticket_id'];
	$existing = 0;

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facnotes` WHERE `ticket_id` = " . $ticket_id . " LIMIT 1";		
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ticket_id = $row['ticket_id'];
		$origin = $row['origin'];
		$destination = $row['destination'];
		$type = $row['type'];
		$eta = $row['ETA'];
		$notes = $row['notes'];
		$existing = 1;
		}
?>
	<BODY onLoad = "document.frm_facnote.frm_origin.focus();">
		<DIV ID='outer'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?>
				<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.frm_facnote.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</DIV>
			<DIV ID='inner' STYLE="position: relative; top: 70px;">
				<CENTER>
				<SPAN CLASS='text text_large text_bold text_center' style='width: 100%; display: block;'>Enter note text</SPAN><BR />
				<FORM NAME='frm_facnote' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
				<TABLE>
					<TR>
						<TD class='td_label'>Origin:</TD>
						<TD class='td_data'>
							<INPUT NAME="frm_origin" tabindex=1 SIZE="48" MAXLENGTH="64" TYPE="text" VALUE="<?php print $origin;?>" />
						</TD>
					</TR>
					<TR>
						<TD class='td_label'>Destination: </TD>
						<TD class='td_data'>
							<INPUT NAME="frm_destination" tabindex=2 SIZE="48" MAXLENGTH="64" TYPE="text" VALUE="<?php print $destination;?>" />
						</TD>
					</TR>
					<TR>
						<TD class='td_label'>Type: </TD>
						<TD class='td_data'>
							<SELECT NAME="frm_type" onChange = "this.value=JSfnTrim(this.value)">
<?php
								if($type != 0) {
?>
									<OPTION VALUE=0>Select</OPTION>
<?php
									} else {
?>
									<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
									}
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_case_cat` ORDER BY `category` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
									$sel = ($type == $row['id']) ? "SELECTED" : "";
									print "\t<OPTION VALUE='{$row['id']}' {$sel}>{$row['category']}</OPTION>\n";
									}
?>
							</SELECT>		
						</TD>
					</TR>
					<TR>
						<TD class='td_label'>ETA: </TD>
						<TD class='td_data'><INPUT NAME="frm_eta" tabindex=3 SIZE="16" MAXLENGTH="16" TYPE="text" VALUE="<?php print $eta;?>" /></TD>
					</TR>
					<TR>
						<TD class='td_label'>Note: </TD>
						<TD class='td_data'><TEXTAREA NAME='frm_notes' tabindex=4 COLS=60 ROWS = 3><?php print $notes;?></TEXTAREA></TD>
					</TR>
				</TABLE>
				<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='<?php print $_GET['ticket_id']; ?>' />
				<INPUT TYPE = 'hidden' NAME = 'frm_existing' VALUE='<?php print $existing;?>' />
				</FORM>
			</DIV>
		</DIV>
<?php
	} else {
	if(intval($_POST['frm_existing']) == 1) {
		$query = "DELETE FROM $GLOBALS[mysql_prefix]facnotes WHERE `ticket_id`=" . $_POST['frm_ticket_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		}
		
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 6/4/2013
	$query  = "INSERT INTO `$GLOBALS[mysql_prefix]facnotes` (
			`ticket_id`, `origin`, `destination`, `type`, `ETA`, `notes`, `_by`, `_on`, `_from`
			) VALUES (" .
			quote_smart(trim($_POST['frm_ticket_id'])) . "," .
			quote_smart(trim($_POST['frm_origin'])) . "," .
			quote_smart(trim($_POST['frm_destination'])) . "," .
			quote_smart(trim($_POST['frm_type'])) . "," .
			quote_smart(trim($_POST['frm_eta'])) . "," .
			quote_smart(trim($_POST['frm_notes'])) . "," .				
			quote_smart(trim($_SESSION['user_id'])) . "," .
			quote_smart(trim($now)) . "," .
			quote_smart(trim($_SERVER['REMOTE_ADDR'])) . ");";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if ($quick) {
?>
		<BODY onLoad = "opener.location.reload(true); opener.parent.frames['upper'].show_msg ('Note added!'); window.close();">
<?php
		} else {
?>
		<BODY onLoad = "opener.location.reload(true);">
		<CENTER>
		<BR />
		<BR />
		<H3>Note added</H3>
		<BR />
		<BR />
		<SPAN id='fin_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
		</CENTER>
<?php
		unset($result);
		}		// end if/else (quick)
	}		// end if/else (empty())
?>
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
set_fontsizes(viewportwidth, "popup");	
outerwidth = viewportwidth * .95;
outerheight = viewportheight * .45;
colwidth = outerwidth;
colheight = outerheight;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('inner')) {$('inner').style.width = colwidth + "px";}
if($('inner')) {$('inner').style.height = colheight + "px";}
</SCRIPT>
</BODY>
</HTML>