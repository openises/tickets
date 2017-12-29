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
$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT *, 
			`r`.`updated` AS `r_updated`,
			`r`.`id` AS `unit_id`,
			`r`.`name` AS `unit_name`,
			`s`.`status_val` AS `un_status_val`,
			`s`.`bg_color` AS `st_background`,
			`s`.`text_color` AS `st_textcolor`,
			`t`.`name` AS `typename`
			FROM `$GLOBALS[mysql_prefix]responder` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON `s`.id=`r`.`un_status_id`
			LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON `t`.id=`r`.`type`	
			WHERE `r`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));

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
			<SPAN id='print_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'><SPAN STYLE='float: left;'><?php print get_text("Print");?></SPAN><IMG STYLE='float: right;' SRC='./images/print_small.png' BORDER=0></SPAN>
			<SPAN id='close_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
<?php
			if (!(is_guest())) {
?>
				<SPAN id='edit_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.opener.parent.frames["main"].location="?func=responder&edit=true&id&id=<?php print $_GET['id'];?>";'><SPAN STYLE='float: left;'><?php print get_text("Edit");?></SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN>
<?php
				}
?>
		</DIV>
		<DIV id='leftcol' style='position: absolute; left: 2%; top: 70px; z-index: 3; text-align: center;'>
<?php
			if (!(empty($row))) {
				print do_unit($row, $the_width, FALSE, FALSE);
				} else {
				print "<CENTER><H3>No data for Unit # {$id} </H3>";
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
