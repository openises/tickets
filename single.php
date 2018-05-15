<?php
/*
4/13/10 initial release
4/30/10 added test for data existence
6/1/10 added functions_major.inc.php
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/10/10 address data disambiguated
3/15/11 changed stylesheet.php to stylesheet.php
11/30/2012 unix time conversion dropped
*/
error_reporting(E_ALL);	
		// 7/28/10
@session_start();
session_write_close();
require_once($_SESSION['fip']);		// 7/28/10
require_once($_SESSION['fmp']);		// 7/28/10, 8/10/10
	$query = "SELECT *,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		`booked_date` AS `booked_date`,		
		`date` AS `date`,
		`t`.`updated` AS updated,
		`t`.`description` AS `tick_descr`,
		`t`.`lat` AS `lat`,
		`t`.`lng` AS `lng`,
		`t`.`_by` AS `call_taker`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,				 
		`f`.`name` AS `fac_name`,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`street` AS `rec_fac_street`,
		`rf`.`city` AS `rec_fac_city`,
		`rf`.`state` AS `rec_fac_state`,
		`rf`.`lat` AS `rf_lat`,
		`rf`.`lng` AS `rf_lng`,
		`f`.`lat` AS `fac_lat`,
		`f`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket` `t`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`t`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`f`.`id` = `t`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `t`.`rec_facility`) 
		WHERE `t`.`id`={$_GET['ticket_id']} LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Incident Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<BODY>
<?php
	$the_width = '98%';
?>
	<DIV id='outer'>
		<DIV id='button_bar' class='but_container'>
			<SPAN id='print_but' class='plain text' style='float: right; vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'><SPAN STYLE='float: left;'><?php print get_text("Print");?></SPAN><IMG STYLE='float: right;' SRC='./images/print_small.png' BORDER=0></SPAN>
			<SPAN id='close_but' class='plain text' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
<?php
			if (!(is_guest())) {
?>
				<SPAN id='edit_but' class='plain text' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.opener.parent.frames["main"].location="edit.php?id=<?php print $_GET['ticket_id'];?>";'><SPAN STYLE='float: left;'><?php print get_text("Edit");?></SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN>
<?php
				}
?>
		</DIV>
		<DIV id='leftcol' style='position: absolute; left: 2%; top: 70px; z-index: 3; text-align: center;'>
<?php
			if (!(empty($row_ticket))) {								// 4/30/10
				print do_ticket_wm($row_ticket, $the_width, FALSE, FALSE);
				} else {
				print "<CENTER><H3>No data for Ticket # {$_GET['ticket_id']} </H3>";
				}
?>
		</DIV>
	</DIV>
	<BR />
	<BR />
	<CENTER>
</CENTER>
</BODY>
<SCRIPT>
var tableWidth, outerWidth;
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
outerwidth = viewportwidth *.94;
tableWidth = outerwidth *.96;
set_fontsizes(viewportwidth, "popup");
if($('outer')) {$('outer').style.width = outerWidth + "px";}
//if($('button_bar')) {$('button_bar').style.width = outerWidth + "px";}
if($('leftcol')) {$('leftcol').style.width = tableWidth + "px";}
if($('left')) {$('left').style.width = tableWidth + "px";}
</SCRIPT>
</HTML>
