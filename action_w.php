<?php
/*
8/16/08	lots of changes; lock icon for date entry control, date validation, 'fetch_assoc' vs 'fetch_array', 'delete' process, 'LIMIT 1' added
8/24/08 removed LIMIT from INSERT sql
10/7/08	set  WRAP="virtual"
10/19/08 set end tags
10/22/08 added priorities as notify selection criteria
1/21/09 added show butts - re button menu
1/27/09 options list style revision - per variable unit types
2/12/09 changed order per AF request
3/18/10 log corrections made
3/20/10 units order and textarea width changes, replaced multi-select with checkboxes
7/20/10 color added to types, status added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/8/10  corrected div height calculation, scroll => auto
8/15/10 dupe prvention added
8/27/10 missing fmp call
3/15/11 changed default.css to stylesheet.php
1/22/11 Added refresh of window opener when Finished adding action.
4/9/2014 addslashes included for string apostrophe handling
12/13/2014 corrections applied to 4/9/2014 changes
*/
error_reporting(E_ALL);

session_start();
session_write_close();
if (!array_key_exists ("user_id", $_SESSION)) {exit();}		//3/6/2015 - if logged out then kill this window

require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);		// 8/27/10

$istest = FALSE;
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

$get_action = (empty($_GET['action']))? "form" : $_GET['action'];		// 10/21/08
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Action Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="8/24/08" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
	.but_container{width: 99%; border: 1px solid black; text-align: center; position: fixed; top: 5px; left: 1px; z-index: 999; padding: 5px; background-color: rgb(0%,0%,0%); background-color: rgba(0%, 0%, 0%, 0.5);}
	.midline {vertical-align: middle; display: inline-block; width: 100px;}
</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};

var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;
var listHeight;
var listwidth;
var colwidth;
var colheight;

function set_size() {
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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	listHeight = viewportheight * .25;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .5;
	listwidth = colwidth;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
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
	}

function ck_window() {		//  onLoad = "ck_window()"
	if (window.opener == null) { alert ("<?php print __LINE__;?>")}
	}		// end function ck_window()

if(document.all && !document.getElementById) {		// accomodate IE
	document.getElementById = function(id) {
		return document.all[id];
		}
	}

try {
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
	}

function $() {									// 2/11/09
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

function do_asof(theForm, theBool) {							// 8/10/08
//		alert(56);
//		alert(theForm.name);
	theForm.frm_year_asof.disabled = theBool;
	theForm.frm_month_asof.disabled = theBool;
	theForm.frm_day_asof.disabled = theBool;
	theForm.frm_hour_asof.disabled = theBool;
	theForm.frm_minute_asof.disabled = theBool;
	}

function do_unlock(theForm) {									// 8/10/08
	document.getElementById("lock").style.visibility = "hidden";
	do_asof(theForm, false)
	}

function do_lock(theForm) {										// 8/10/08
	do_asof(theForm, true)
	document.getElementById("lock").style.visibility = "visible";
	}

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};

function chknum(str) {
	var nums = str.trim().replace(/\D/g, "" );							// strip all non-digits
	return (nums == str.trim());
	}

function chkval(val, lo, hi) {
	return  (chknum(val) && !((val> hi) || (val < lo)));}

function datechk_r(theForm) {		// as-of vs now
	var start = new Date();
	start.setFullYear(theForm.frm_year_asof.value, theForm.frm_month_asof.value-1, theForm.frm_day_asof.value);
	start.setHours(theForm.frm_hour_asof.value, theForm.frm_minute_asof.value, 0,0);

	var end = new Date();
	return (start.valueOf() <= end.valueOf());
	}

function validate(theForm) {
	var errmsg="";
	if (theForm.frm_description.value == "")		{errmsg+= "\tDescription is required\n";}
	do_unlock(theForm) ;
	if (!chkval(theForm.frm_year_asof.value, <?php print date('Y')-1 . ", " . date('Y'); ?>)) 	{errmsg+= "\tAs-of date error - Year\n";}
	if (!chkval(theForm.frm_month_asof.value, 1,12)) 		{errmsg+= "\tAs-of date error - Month\n";}
	if (!chkval(theForm.frm_day_asof.value, 1,31)) 			{errmsg+= "\tAs-of date error - Day\n";}
	if (!chkval(theForm.frm_hour_asof.value, 0,23)) 		{errmsg+= "\tAs-of time error - Hours\n";}
	if (!chkval(theForm.frm_minute_asof.value, 0,59)) 		{errmsg+= "\tAs-of time error - Minutes\n";}
	if (!datechk_r(theForm))								{errmsg+= "\tAs-of date/time error - future?\n" ;}

	if (errmsg!="") {
		do_lock(theForm);
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		theForm.submit();
		}
	}				// end function validate(theForm)

function set_signal(inval) {
	var lh_sep = (document.add_frm.frm_description.value.trim().length>0)? " " : "";
	var temp_ary = inval.split("|", 2);		// inserted separator
	document.add_frm.frm_description.value+= lh_sep + temp_ary[1] + ' ';
	document.add_frm.frm_description.focus();
	}		// end function set_signal()

</SCRIPT>
</HEAD>
<?php
print "<BODY onLoad = 'ck_window(); init();'>\n";

$do_yr_asof = false;		// js year housekeeping

$optstyles = array ();		// see css

$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`";				// 1/27/09
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$optstyles[$row['name']] = $row['name'];
	}
unset($result);

if ($get_action == 'add') {
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

	if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$_GET[ticket_id]'")) {
		print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
		} elseif ($_POST['frm_description'] == '') {
		print '<FONT CLASS="warn">Please enter Description.</FONT><BR />';
		} else {
		$responder = $sep = "";
		foreach ($_POST as $VarName=>$VarValue) {			// 3/20/10
			$temp = explode("_", $VarName);
			if (substr($VarName, 0, 7)=="frm_cb_") {
				$responder .= $sep . $VarValue;		// space separator for multiple responders
				$sep = " ";
				}
			}
		$_POST['frm_description'] = addslashes(strip_html($_POST['frm_description'])); //fix formatting, custom tags etc.

		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST['frm_meridiem_asof'] : "" ;

		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
																			// 8/15/10
		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]action`
			(`description`,`ticket_id`,`date`,`user`,`action_type`, `updated`, `responder`) VALUES (
				" . quote_smart($_POST['frm_description']) . ",
				'{$_GET['ticket_id']}',
				'{$now}',
				{$_SESSION['user_id']},
				{$GLOBALS['ACTION_COMMENT']},
				'{$frm_asof}',
				'{$responder}')";		// 8/24/08
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename(__FILE__), __LINE__);

		$ticket_id = mysql_insert_id();								// just inserted action id
		do_log($GLOBALS['LOG_ACTION_ADD'], $_GET['ticket_id'], 0,  mysql_insert_id());		// 3/18/10
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE `id`='" . $_GET['ticket_id'] . "' LIMIT 1";
		$result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);

		print "<br /><CENTER><FONT CLASS='header text_large'>Action record has been added</FONT><BR /><BR />";
		print "<BR /><BR /><SPAN ID='fin_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='opener.location.reload(true); opener.parent.frames[\"upper\"].show_msg(\"Action added!\"); window.close();'><SPAN STYLE='float: left;'>" . get_text('Finished') . "</SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN><BR /><BR /><BR /></CENTER>";
//		print "</BODY>";				// 10/19/08
		$id = $_GET['ticket_id'];
		$addrs = notify_user($id,$GLOBALS['NOTIFY_ACTION_CHG']);		// returns array or FALSE

		if ($addrs) {
			$theTo = implode("|", array_unique($addrs));
			$theText = "TICKET - ACTION: ";
			mail_it ($theTo, "", $theText, $id, 1 );
			}				// end if/else ($addrs)

		print "</HTML>";				// 10/19/08
		}		// end else ...
// ____________________________________________________
	exit();

	} else if ($get_action == 'delete') {
		if (array_key_exists('confirm', ($_GET))) {
			$mode=$_POST['mode'];
			do_log($GLOBALS['LOG_ACTION_DELETE'], $_GET['ticket_id'], 0, $_GET['id']);		// 8/7/08
//			($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09

			$result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") or do_error('','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
			print '<CENTER><FONT CLASS="header text_large">Action deleted</FONT><BR /><BR />';
			if($mode == 1) {
?>
				<SPAN ID='fin_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='opener.location.reload(true); window.close();'><SPAN STYLE='float: left;'><?php print get_text('Finished');?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0 /></SPAN></CENTER>
<?php
				} else {
				add_header($_GET['ticket_id']);
				show_ticket($_GET['ticket_id']);
				}
			} else {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			print "<CENTER>";
			print "<FONT CLASS='header text_large'>Really delete action record '" . shorten($row['description'], 24) . "' ? </FONT><BR /><BR />";
			print "<FORM NAME='delfrm' METHOD='post' ACTION='action_w.php?action=delete&id=$_GET[id]&ticket_id=" . $_GET['ticket_id'] . "&confirm=1'>";
?>
			<SPAN ID='sub_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.delfrm.submit();'><SPAN STYLE='float: left;'><?php print get_text('Yes');?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0 /></SPAN>
<?php
			if($mode == 1) {
?>
				<SPAN ID='can_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text('Cancel');?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0 /></SPAN>
<?php
				} else {
?>
				<SPAN ID='can_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='history.back();'><SPAN STYLE='float: left;'><?php print get_text('Cancel');?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0 /></SPAN>
<?php
				}
			print "<INPUT type='hidden' NAME='mode' VALUE=" . $mode . "/>";
			print "</CENTER></FORM>";
			}
		} else if ($get_action == 'update') {		//update action and show ticket
		$responder = $sep = "";
		foreach ($_POST as $VarName=>$VarValue) {			// 3/20/10
			$temp = explode("_", $VarName);
			if (substr($VarName, 0, 7)=="frm_cb_") {
				$responder .= $sep . $VarValue;		// space separator for multiple responders
				$sep = " ";
				}
			}
		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
		$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]action` SET `description`='$_POST[frm_description]', `responder` = '$responder', `updated` = '$frm_asof' WHERE `id`='$_GET[id]' LIMIT 1") or do_error('action_w.php::update action','mysql_query',mysql_error(),basename( __FILE__), __LINE__);
		$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` =	'$frm_asof' WHERE id='$_GET[ticket_id]' LIMIT 1") 	or do_error('action_w.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") 			or do_error('action_w.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		print "<br /><CENTER><FONT CLASS='header text_large'>Action record has been updated</FONT><BR /><BR />";
		print "<BR /><BR /><SPAN ID='fin_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='opener.location.reload(true); opener.parent.frames[\"upper\"].show_msg(\"Action added!\"); window.close();'><SPAN STYLE='float: left;'>" . get_text('Finished') . "</SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN><BR /><BR /><BR /></CENTER>";
//		print "</BODY>";				// 10/19/08
		$id = $_GET['ticket_id'];
		$addrs = notify_user($id,$GLOBALS['NOTIFY_ACTION_CHG']);		// returns array or FALSE

		if ($addrs) {
			$theTo = implode("|", array_unique($addrs));
			$theText = "TICKET - ACTION: ";
			mail_it ($theTo, "", $theText, $id, 1 );
			}				// end if/else ($addrs)

		print "</HTML>";				// 10/19/08
		exit();
		} else if ($get_action == 'edit') {		//get and show action to update
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		$responders = explode(" ", $row['responder']);				// to array
		$do_yr_asof = true;
		$ticket_id = $row['ticket_id'];
		$heading = "Edit Action";
?>
		<DIV ID='outer'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?></SPAN>
				<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.ed_frm.reset(); init();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='return validate(document.ed_frm);'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</DIV>
			<FORM METHOD="post" NAME='ed_frm' ACTION="action_w.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $ticket_id;?>&action=update">
			<TABLE BORDER="0" STYLE='margin-left: 100px; position: relative; top: 50px;'>
				<TR CLASS='even'>
					<TD CLASS='td_label text'>Description: <font color='red' size='-1'>*</font></TD>
					<TD CLASS='td_data text'>
						<TEXTAREA ROWS="3" COLS="60" NAME="frm_description"><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS='even'>		<!-- 11/15/10 -->
					<TD CLASS="td_label text"></TD>
					<TD>Signal &raquo;
						<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
							<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
							$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
								$short = shorten ($row_sig['text'], 40);
								print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$short}</OPTION>\n";		// pipe separator
								}
?>
						</SELECT>
					</TD>
				</TR>
				<TR CLASS='even'>		<!-- 11/15/10 -->
					<TD CLASS='td_label text'>As of: </TD>
					<TD CLASS='td_data text'>
						<?php print generate_date_dropdown('asof',0, TRUE);?>&nbsp;&nbsp;&nbsp;&nbsp;
						<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.ed_frm);'>
					</TD>
				</TR>
				<TR CLASS='odd' VALIGN='top'>
<?php
//				generate dropdown menu of responders -- if(in_array($rowtemp[id], $row[responder]))

				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`, `y`.`id` AS `type_id`, `r`.`id` AS `unit_id`, `r`.`name` AS `unit_name`,
					`s`.`description` AS `stat_descr`,  `r`.`description` AS `unit_descr`,
					(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) AS `nr_assigned`
					FROM `$GLOBALS[mysql_prefix]responder` `r`
					LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` ON ( `r`.`type` = y.id )
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id )
					ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC";
				$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
				$max = 24;
				$height =  (mysql_affected_rows()>$max) ? ($max * 30 ) : (mysql_affected_rows() + 1) * 30;
				print "<TR><TD COLSPAN=2>&nbsp;</TD></TR>";
				print "<TR><TD COLSPAN=2 style='text-align: center;'>" . get_units_legend(). "</TD></TR>";
				print "<TR><TD COLSPAN=2>&nbsp;</TD></TR>";
				print "<TR><TD>&nbsp;</TD>";		// 8/8/10
				$checked = (in_array("0", $responders))? "CHECKED" : "";	// NA is special case - 8/8/10
				print "<TD CLASS='odd td_data text'><DIV style='width: 100%; padding: 5px; height:{$height}PX; overflow-y: auto; overflow-x: hidden;' >
					<INPUT TYPE = 'checkbox' VALUE=0 NAME = 'frm_cb_0'>NA<BR />\n";

				while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
					$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 7/20/10
					$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		//
					$ct_str = ($row['nr_assigned']==0) ? ""  : "&nbsp;({$row['nr_assigned']})" ;
					$checked = (in_array($row['unit_id'], $responders))? "CHECKED" : "";
					$the_name = "frm_cb_" . stripslashes ($row['unit_name']);
					print "\t<INPUT TYPE = 'checkbox' VALUE='{$row['unit_id']}' NAME = \"{$the_name}\" $checked />
						<SPAN class='text' STYLE='width: 55%; display: inline-block; background-color: {$the_bg_color}; color: {$the_text_color};'>" .
						stripslashes ($row['unit_name']) . "</SPAN> &nbsp; {$ct_str}";
					print " - <SPAN class='text' STYLE='width: 35%; display: inline-block; background-color:{$row['bg_color']}; color:{$row['text_color']};'>
						{$row['stat_descr']}</SPAN><BR />\n";		// 7/20/10
					}
				unset ($row);
				print "\t</DIV></TD>\n";
?>
			</TABLE><BR />
			</FORM>
		</DIV>
<?php
		} else if ($get_action == 'form') {
		$do_yr_asof = true;
		$user_level = is_super() ? 9999 : $_SESSION['user_id'];
		$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
		$group = get_regions_inuse_numbers($user_level);	//	6/10/11

		$al_groups = $_SESSION['user_groups'];

		if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
			$curr_viewed= explode(",",$_SESSION['viewed_groups']);
			} else {
			$curr_viewed = $al_groups;
			}

		$curr_names="";	//	6/10/11
		$z=0;	//	6/10/11
		foreach($curr_viewed as $grp_id) {	//	6/10/11
			$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
			$curr_names .= get_groupname($grp_id);
			$curr_names .= $counter;
			$z++;
			}

		$heading = "Add Action";
?>
		<DIV ID='outer'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?></SPAN>
				<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.add_frm.reset(); init();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='return validate(document.add_frm);'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</DIV>
			<FORM METHOD="post" NAME="add_frm" onSubmit='return validate(this.form);' ACTION="<?php echo basename(__FILE__);?>?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
			<TABLE BORDER="0" STYLE='margin-left: 100px; position: relative; top: 50px;'>
			<TR CLASS='even'>
				<TD CLASS='td_label text'>Description: <font color='red' size='-1'>*</font></TD>
				<TD CLASS='td_data text'>
					<TEXTAREA ROWS="3" COLS="60" NAME="frm_description"></TEXTAREA>
				</TD>
			</TR>

			<TR CLASS='even'>		<!-- 11/15/10 -->
				<TD CLASS="td_label">Signal: </TD>
				<TD CLASS='td_data text'>
					<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
							$short = shorten ($row_sig['text'], 40);
							print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$short}</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
				</TD>
			</TR>
			<TR>
				<TD CLASS='td_label text'>As of: </TD>
				<TD CLASS='td_data text'>
					<?php print generate_date_dropdown('asof',0, TRUE);?>&nbsp;&nbsp;&nbsp;&nbsp;
					<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.add_frm);'>
				</TD>
			</TR>
<?php
//						generate dropdown menu of responders

			if(!isset($curr_viewed)) {
				if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
					$where = "WHERE `a`.`type` = 2";
					} else {
					$x=0;	//	6/10/11
					$where = "WHERE (";	//	6/10/11
					foreach($al_groups as $grp) {	//	6/10/11
						$where2 = (count($al_groups) > ($x+1)) ? " OR " : ")";
						$where .= "`a`.`group` = '{$grp}'";
						$where .= $where2;
						$x++;
						}
					$where .= "AND `a`.`type` = 2";
					}
				} else {
				if(count($curr_viewed == 0)) {
					$where = "WHERE `a`.`type` = 2";
					} else {
					$x=0;	//	6/10/11
					$where = "WHERE (";
					foreach($curr_viewed as $grp) {
						$where2 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
						$where .= "`a`.`group` = '{$grp}'";
						$where .= $where2;
						$x++;
						}
					$where .= "AND `a`.`type` = 2";
					}
				}

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`, `t`.`id` AS `type_id`, `r`.`id` AS `unit_id`, `r`.`name` AS `unit_name`,
				`s`.`description` AS `stat_descr`,  `r`.`description` AS `unit_descr`, `a`.`id` AS `assigns_id`,
				(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns`
					WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )
					AS `nr_assigned`
				FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id )
				$where GROUP BY unit_id ORDER BY `nr_assigned` DESC, `handle` ASC, `r`.`name` ASC";
				
			$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
			$max = 24;

			$height =  (mysql_affected_rows()>$max) ? ($max * 22 ) : (mysql_affected_rows() + 1) * 22;
			print "<TR><TD COLSPAN=2>&nbsp;</TD></TR>";
			print "<TR><TD COLSPAN=2 style='text-align: center;'>" . get_units_legend(). "</TD></TR>";
			print "<TR><TD COLSPAN=2>&nbsp;</TD></TR>";
			print "<TR><TD>&nbsp;</TD>";		// 8/8/10
			print "<TD CLASS='odd td_data text'><DIV style='width: 100%; padding: 5px; height:{$height}PX; overflow-y: auto; overflow-x: hidden;' >
				<INPUT TYPE = 'checkbox' VALUE=0 NAME = 'frm_cb_0'>NA<BR />\n";

			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$type_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 7/20/10
				$type_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		//

				$ct_str = ($row['nr_assigned']==0) ? ""  : "&nbsp;({$row['nr_assigned']})" ;
				$the_name = "frm_cb_" . stripslashes ($row['unit_name']);
				print "\t<INPUT TYPE = 'checkbox' VALUE='{$row['unit_id']}' NAME = \"{$the_name}\" />
					<SPAN class='text' STYLE='width: 55%; display: inline-block; background-color: {$type_bg_color}; color: {$type_text_color};'>" .
					stripslashes ($row['unit_name']) . "</SPAN> &nbsp; {$ct_str}";
				print " - <SPAN class='text' STYLE='width: 35%; display: inline-block; background-color:{$row['bg_color']}; color:{$row['text_color']};'>
					{$row['stat_descr']}</SPAN><BR />\n";		// 7/20/10
				}
			print "</DIV></TD>";
?>
			</TABLE><BR />
			</FORM>
		</DIV>
<?php
		}				// end if ($get_action == 'form')

//				 common to all
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
</FORM>
<SCRIPT>set_size();</SCRIPT>
</BODY>
<?php
	if ($do_yr_asof) { 		// for ADD and EDIT only
?>
<SCRIPT LANGUAGE="Javascript">

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
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
listHeight = viewportheight * .25;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .5;
listwidth = colwidth;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";

function init () {
	do_unlock(document.forms[0])
	var now = new Date();
	if (now.getYear()>2000) {
		document.forms[0].frm_year_asof.value= now.getYear() - 2000;
		}
	else {
		if (now.getYear()>100) {
			document.forms[0].frm_year_asof.value=now.getYear() - 100;
			}
		else {
			document.forms[0].frm_year_asof.value=now.getYear();
			}
		}
	document.forms[0].frm_year_asof.value=now.getFullYear();
	document.forms[0].frm_month_asof.value=now.getMonth()+1;
	document.forms[0].frm_day_asof.value=now.getDate();
	document.forms[0].frm_hour_asof.value=now.getHours();
	document.forms[0].frm_minute_asof.value=now.getMinutes() ;
	if (document.forms[0].frm_hour_asof.value<10) {document.forms[0].frm_hour_asof.value = "0" + document.forms[0].frm_hour_asof.value;}
	if (document.forms[0].frm_minute_asof.value<10) {document.forms[0].frm_minute_asof.value = "0" + document.forms[0].frm_minute_asof.value;}
	do_lock(document.forms[0]);
	}
</SCRIPT>
<?php
		}		// end 	if ($do_yr_asof)

?>
</HTML>
<?php
//function new_notify_user($ticket_id,$action) {								// 10/20/08
//	if (get_variable('allow_notify') != '1') return FALSE;					//should we notify?
//
//	$addrs = array();													//
//
//	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE (`ticket_id`='$ticket_id' OR `ticket_id`=0)  AND `on_action` = '1'";	// all notifies for given ticket - or any ticket 10/22/08
//	print __LINE__;
//	dump ($query);
//	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
//		dump ($row['email_address']);
////		if (($action == $GLOBALS['NOTIFY_ACTION'] AND $row['on_action']) OR ($action == $GLOBALS['NOTIFY_TICKET'] AND $row['on_ticket'])){
//			if (is_email($row['email_address'])) {
//				print __LINE__;
//				array_push($addrs, $row['email_address']);
//				}		// save for emailing
////			}
//		}
//	dump($addrs);
//	return (empty($addrs))? FALSE: $addrs;
//	}
//
?>