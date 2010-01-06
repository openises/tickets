<?php
/*
8/10/09	initial release
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
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
<TITLE>Add Note to Existing Incident</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">

<SCRIPT>
</SCRIPT>
</HEAD>
<?php
if (empty($_POST)) { 
?>
<BODY onLoad = "document.frm_note.frm_text.focus();">
<CENTER>
<H4>Enter note text</H4>
<FORM NAME='frm_note' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
<TEXTAREA NAME='frm_text' COLS=60 ROWS = 3></TEXTAREA>
<BR />
<B>Apply to</B>&nbsp;:&nbsp;&nbsp;
Summary &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='0' CHECKED />&nbsp;&nbsp;&nbsp;&nbsp;
Disposition &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='1' /><BR /><BR />
<INPUT TYPE = 'button' VALUE = 'Cancel' onClick = 'window.close()' />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE = 'button' VALUE = 'Reset' onClick = 'this.form.reset()' />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE = 'button' VALUE = 'Next' onClick = 'this.form.submit()' />
<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='<?php print $_GET['ticket_id']; ?>' />
</FORM>
<?php
		}		// end if (empty($_POST))
	else {
		$field_name = array('description', 'comments');
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$_POST['frm_ticket_id']} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$synop = $row['description'];
		$disp = $row['comments'];
		$now = (time() - (get_variable('delta_mins')*60)); 
		$format = get_variable('date_format');
		$the_date = date($format, $now);
		$the_text = $synop . " [" . $the_date . "]" . trim($_POST['frm_text']);
	
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `{$field_name[$_POST['frm_add_to']]}`= " . quote_smart($the_text) . " WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//		dump($query);

	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
	if ($quick) {
?>
	<BODY onLoad = "opener.parent.frames['upper'].show_msg ('Note added!'); window.close();">
	</BODY></HTML>
	
<?php
	}				// end if ($quick)
else {
?>
<BODY>http://127.0.0.1/tickets_next/edit.php?id=403#
<BR /><BR />
<H3>Note added to Call '<?php print $row['scope'];?>'</H3><BR /><BR />
<INPUT TYPE = 'button' VALUE = 'Finished' onClick = 'window.close()'>
</BODY>
</HTML>
<?php
		unset($result);
		}		// end if/else
	}		// end if/else (empty())
?>