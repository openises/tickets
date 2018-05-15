<?php
/*
*/
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$sess_id = $_SESSION['id'];
$ret_arr = array();
if(!array_key_exists('q', $_GET) || $_GET['q'] != $_SESSION['id']) {
	$ret_arr[0] = "Error calling form";
	print json_encode($ret_arr);
	exit();
	}
$disposition = get_text("Disposition");
$output = "<BR /><BR />";
$output .= "<SPAN CLASS='text text_large text_bold text_center' style='width: 100%; display: block;'>Enter note text to add to ticket id " . $_GET['ticket_id'] . "</SPAN><BR />";
$output .= "<FORM NAME='frm_note' METHOD='post' ACTION='./ajax/form_post.php?q=". $sess_id . "&function=note'>";
$output .= "<TEXTAREA NAME='frm_text' style='width: 80%;'></TEXTAREA>";
$output .= "<BR /><BR />";
$output .= "Signal &raquo; <SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
$output .= "<OPTION VALUE=0 SELECTED>Select</OPTION>";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
	$output .= "\t<OPTION VALUE='" . $row_sig['code'] . "'>" . $row_sig['code'] . "|" . $row_sig['text'] . "</OPTION>\n";
	}
$output .= "</SELECT>";
$output .= "<B>Apply to</B>&nbsp;:&nbsp;&nbsp;";
$output .= "Description &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='0' CHECKED />&nbsp;&nbsp;&nbsp;&nbsp;";
$output .= $disposition . " &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='1' />";
$output .= "<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='" . $_GET['ticket_id'] . "' />";
$output .= "</FORM><DIV STYLE='width: 100%; text-align: center;'>";
$output .= "<SPAN ID='reset_but' class='plain text' style='display: inline-block; float: none; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.frm_note.reset();'><SPAN STYLE='float: left;'>" . get_text('Reset') . "</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
$output .= "<SPAN ID='sub_but' class='plain text' style='display: inline-block; float: none; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='sendajax(document.frm_note);'><SPAN STYLE='float: left;'>" . get_text('Next') . "</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
$output .= "</DIV></CENTER><BR /><BR />";
$ret_arr[0] = $output;
print json_encode($ret_arr);