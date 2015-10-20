<?php
/*
9/10/13 New File - for writing unit or ticket specific log entry
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets Log Processing</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="Tickets Log Entry"">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
.box { background-color: transparent; border: 0px solid #000000; color: #000000; padding: 0px; position: absolute; z-index:1000; }
.bar { background-color: #DEE3E7; color: #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; }
.content { padding: 1em; }
</STYLE>
<SCRIPT SRC="./js/misc_function.js" type="text/javascript"></SCRIPT>

<SCRIPT>
function get_new_colors() {								// 4/5/11
	window.location.href = '<?php print basename(__FILE__);?>';
	}
	
function $() {															// 12/20/08
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
		}
	return elements;
	}
	
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}
	
function do_plain (the_id) {				// 8/21/10
	CngClass(the_id, 'plain');
	return true;
	}
</SCRIPT>
</HEAD>
<BODY>
<?php
if (is_guest()) {
?>
	<CENTER><BR /><BR /><BR /><BR /><BR /><H3>Guests not allowed Log access. </CENTER><BR /><BR />
	<INPUT TYPE='button' value='Cancel' onClick = 'window.exit();'>
<?php
	exit();
	} else {
?>
	<TABLE WIDTH="100%">
<?php
	$query = "
		SELECT *, 
		`when` AS `when`,
		`l`.`id` AS `log_id`,
		`l`.`when` AS `logwhen`,
		`t`.`scope` AS `tickname`,
		`r`.`handle` AS `unitname`,
		`l`.`info` AS `comment`,
		`s`.`status_val` AS `theinfo`,
		`u`.`user` AS `thename` 
		FROM `$GLOBALS[mysql_prefix]log` l
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t 		ON (l.ticket_id = t.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` r 	ON (l.responder_id = r.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s 	ON (l.info = s.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` u 		ON (l.who = u.id)
		WHERE `l`.`id` = {$id} 
		ORDER BY `when` ASC";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$theComment = (!is_numeric($row['comment'])) ? $row['comment'] : "";
?>
	<TR CLASS = 'even' ><TH COLSPAN=2>Log View</TH></TR>
	<TR CLASS = 'odd'><TD>Unit Name:</TD><TD><?php print $row['unitname'];?></TD></TR>
	<TR CLASS = 'even'><TD>Status Val:</TD><TD><?php print $row['theinfo'];?></TD></TR>
	<TR CLASS = 'odd'><TD>By Who:</TD><TD><?php print $row['thename'];?></TD></TR>
	<TR CLASS = 'even'><TD>When:</TD><TD><?php print format_date_2(strtotime($row['logwhen']));?></TD></TR>
	<TR CLASS = 'odd'><TD>Comment:</TD><TD><?php print $theComment;?></TD></TR>
	<TR CLASS = 'even'><TD COLSPAN=2 ALIGN='center'>
	<SPAN class='plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close()" />Close</SPAN>
	</TD></TR>
	</TABLE>
<?php 
	}
?>
</BODY>
</HTML>
