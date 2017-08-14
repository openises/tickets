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
$disposition = get_text("Disposition");				// 12/1/10
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Add Note to Existing Incident</TITLE>
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
	if(document.frm_note.frm_text.value.trim().length==0) {
		alert("Enter text - or Cancel"); 
		return false;
		} 
	else {
		document.frm_note.submit();	
		}
	}
</SCRIPT>
</HEAD>
<?php
if (empty($_POST)) {
	$heading = "Add Note";
?>
	<BODY onLoad = "document.frm_note.frm_text.focus();">
		<DIV ID='outer'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?>
				<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.frm_note.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</DIV>
			<DIV ID='inner' STYLE="position: relative; top: 70px;">
				<CENTER>
				<SPAN CLASS='text text_large text_bold text_center' style='width: 100%; display: block;'>Enter note text</SPAN><BR />
				<FORM NAME='frm_note' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
				<TEXTAREA NAME='frm_text' COLS=60 ROWS = 3></TEXTAREA>
				<BR />
				<BR />
<SCRIPT>
				function set_signal(inval) {
					var temp_ary = inval.split("|", 2);		// inserted separator
					document.frm_note.frm_text.value+=" " + temp_ary[1] + ' ';		
					document.frm_note.frm_text.focus();		
					}		// end function set_signal()
</SCRIPT>

				Signal &raquo; 
				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
					<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
					while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
						print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
						}
?>
				</SELECT>
				<B>Apply to</B>&nbsp;:&nbsp;&nbsp;
				Description &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='0' CHECKED />&nbsp;&nbsp;&nbsp;&nbsp;
				<?php print $disposition;?> &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='1' />
				<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='<?php print $_GET['ticket_id']; ?>' />
				</FORM>				
			</DIV>
		</DIV>


<?php
	} else {
	$field_name = array('description', 'comments');
	$frm_ticket_id=(int)$_POST['frm_ticket_id'];	//	4/4/14
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$frm_ticket_id} LIMIT 1";	//	4/4/14
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$now = (time() - (get_variable('delta_mins')*60)); 
	$format = get_variable('date_format');
	$the_date = date($format, $now);
	$the_in_str = ($_POST['frm_add_to']=="0")? $row['description'] : $row['comments'] ;
	@session_start();
	session_write_close();		
	$the_text = "{$the_in_str} [{$_SESSION['user']}:{$the_date}]" . strip_tags(trim($_POST['frm_text'])) . "\n";		// 1/7/2013

	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `{$field_name[$_POST['frm_add_to']]}`= " . quote_smart($the_text) . " WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if ($quick) {
?>
		<BODY onLoad = "opener.location.reload(true); opener.parent.frames['upper'].show_msg ('Note added!'); window.close();">
<?php
		} else {				// end if ($quick)
?>
		<BODY onLoad = "opener.location.reload(true);">
		<CENTER>
		<BR />
		<BR />
		<H3>Note added to Incident '<?php print $row['scope'];?>'</H3>
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