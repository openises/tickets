<?php
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
if(array_key_exists('q', $_GET) && ($_GET['q'] != $_SESSION['id'])) {
	exit();
	}

$ret_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
$result = mysql_query($query);
$units_ct = mysql_num_rows($result);

$ret_arr = array();
$curr_cats = get_category_butts();
$cat_sess_stat = get_session_status($curr_cats);
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();

//	Categories for Unit status

$categories = array();
$categories = $curr_cats;

$cats_buttons = "<form action='#'><TABLE WIDTH='100%'><TR class='heading_2'><TH ALIGN='center'>" . get_text("Units") . "</TH></TR><TR class='odd'><TD COLSPAN=99 CLASS='td_label' >";

if($units_ct > 0) {
	foreach($categories as $key => $value) {
		$cats_buttons .= "<DIV class='cat_button text'>" . $value . ": <input type=checkbox id='" . $value . "' onChange='set_buttons(\"category\"); set_chkbox(\"" . $value . "\");'/>&nbsp;&nbsp;</DIV>";
		}
	$cats_buttons .= "</TD></TR><TR CLASS='odd'><TD COLSPAN=99 CLASS='td_label'>";
	$all="RESP_ALL";
	$none="RESP_NONE";
	$cats_buttons .= "<DIV ID = 'RESP_ALL_BUTTON' class='cat_button text'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='" . $all . "' onChange='set_buttons(\"all\"); set_chkbox(\"" . $all . "\");'/></FONT></DIV>";
	$cats_buttons .= "<DIV ID = 'RESP_NONE_BUTTON' class='cat_button text'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='" . $none . "' onChange='set_buttons(\"none\"); set_chkbox(\"" . $none . "\");'/></FONT></DIV>";
	$cats_buttons .= "<DIV ID = 'go_can' style='float:right; padding:2px;'><SPAN ID = 'go_button' onClick='do_go_button();' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: green;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);'>Next</SPAN>";
	$cats_buttons .= "<SPAN ID = 'can_button'  onClick='cancel_buttons();' class='plain' style='width: 50px; float: none; display: none; font-size: .8em; color: red;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);''>Can</SPAN></DIV>";
	$cats_buttons .= "</TD></TR></TABLE></form>";
	} else {
	foreach($categories as $key => $value) {
		$cats_buttons .= "<DIV class='cat_button text' STYLE='display: none;'>" . $value . ": <input type=checkbox id='" . $value . "' onChange='set_buttons(\"category\"); set_chkbox(\"" . $value . "\");'/>&nbsp;&nbsp;</DIV>";
		}
	$all="RESP_ALL";
	$none="RESP_NONE";
	$cats_buttons .= "<DIV class='cat_button text' style='color: red;'>None Defined ! </DIV>";
	$cats_buttons .= "<DIV ID = 'RESP_ALL_BUTTON' class='cat_button text' STYLE='display: none;'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='" . $all . "' onChange='set_buttons(\"all\"); set_chkbox(\"" . $all . "\");'/></FONT></DIV>";
	$cats_buttons .= "<DIV ID = 'RESP_NONE_BUTTON' class='cat_button text' STYLE='display: none;'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='" . $none . "' onChange='set_buttons(\"none\"); set_chkbox(\"" . $none . "\");'/></FONT></DIV>";
	$cats_buttons .= "</form></TD></TR></TABLE></DIV>";
	}


$ret_arr[0] = $cats_buttons;
//dump($ret_arr);
print json_encode($ret_arr);
exit();
?>
