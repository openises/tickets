<?php
/*
11/21/2012 initial release
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');

if (trim(get_variable("locale"))==0)	{$radius = 3959; $caption = "Mi."; }
else 									{$radius = 6371; $caption = "Km"; }

$query = "SELECT *, ( {$radius} * acos(
	cos(radians({$_GET['tick_lat']})) *
	cos(radians(`lat`)) *
	cos(radians(`lng`) - radians({$_GET['tick_lng']})) +
	sin(radians({$_GET['tick_lat']})) *
	sin(radians(`lat`))	) ) 
	AS `miles` 
	FROM `$GLOBALS[mysql_prefix]ticket` 
	WHERE ((`status` = {$GLOBALS['STATUS_CLOSED']}) AND (`lat` <> 0.999999))
	ORDER BY `miles` ASC LIMIT 50";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
$i=0;
$evenodd = array ("even", "odd");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php echo LessExtension(basename(__FILE__));?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT>
	window.onresize=function(){set_size()};
	
	var viewportwidth, viewportheight;
	
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
		}
		
	function do_popup(id) {
		document.popup.id.value = id;
		document.popup.submit();	
		}
</SCRIPT>
</HEAD>
<BODY>
<CENTER>
<?php
	if (mysql_num_rows($result)==0) {	
?>
<BR/><BR/><BR/><BR/><H2>No closed incidents!</H2>
<?php
		}		// end if (mysql_num_rows($result)==0)
	else {
?>
<FORM NAME='popup' METHOD = 'get' ACTION = 'incident_popup.php'>
<INPUT TYPE = 'hidden' NAME = 'id' VALUE=''>
<!-- <INPUT TYPE = 'hidden' NAME = 'tick_only' VALUE=1> -->
<FORM>
<TABLE cellpadding = 2 align='center'  STYLE = 'margin-top:32px;'>
<?php
	echo "<TR CLASS = 'even'><TH CLASS='text text_left'>{$caption}</TH><TH CLASS='text text_left'>" . get_text("Addr") . "</TH><TH CLASS='text text_left'>" . get_text("Incident") . "</TH><TH CLASS='text text_left'>Opened</TH></TR>";
 	echo "<TR CLASS = 'odd'><TD CLASS='text text_center' COLSPAN=4 ALIGN='center'><I>Click line for " . get_text("Incident") . " detail</I></TD></TR>";
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {	
		echo "<TR CLASS= '{$evenodd[($i)%2]}' onclick = 'do_popup({$in_row['id']});'>";
		echo "<TD CLASS='text' ALIGN='right'>" . round($in_row["miles"], 1) . "</TD>";
		echo "<TD CLASS='text'>" . shorten("{$in_row['street']}  {$in_row['city']}", 48) . "</TD>";
		echo "<TD CLASS='text'>{$in_row["scope"]}</TD>";
		echo "<TD CLASS='text'>". substr($in_row["problemstart"], 5, 11) ."</TD>";
		echo "</TR>\n";
		$i++;
		}		// end while()
	echo "</TABLE>";	
	}				// end if/else
?>
</FORM>
<SPAN ID='fin_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>

<script>
//	document.write("<BUTTON onclick = 'javascript:history.go(-1)' STYLE = 'margin-top:32px;'>Back</BUTTON>");
	</script>
</CENTER>
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
set_fontsizes(viewportwidth, "popup");
</SCRIPT>
</HTML>
