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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT>
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
<FORM NAME='popup' METHOD = 'get' ACTION = 'Incident_popup.php'>
<INPUT TYPE = 'hidden' NAME = 'id' VALUE=''>
<!-- <INPUT TYPE = 'hidden' NAME = 'tick_only' VALUE=1> -->
<FORM>
<TABLE cellpadding = 2 align='center'  STYLE = 'margin-top:32px;'>
<?php
	echo "<TR CLASS = 'even'><TH>{$caption}</TH><TH>" . get_text("Addr") . "</TH><TH>" . get_text("Incident") . "</TH><TH>Opened</TH></TR>";
 	echo "<TR CLASS = 'odd'><TD COLSPAN=4 ALIGN='center'><I>Click line for " . get_text("Incident") . " detail</I></TD></TR>";
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {	
		echo "<TR CLASS= '{$evenodd[($i)%2]}' onclick = 'do_popup({$in_row['id']});'>";
		echo "<TD ALIGN='right'>" . round($in_row["miles"], 1) . "</TD>";
		echo "<TD>" . shorten("{$in_row['street']}  {$in_row['city']}", 48) . "</TD>";
		echo "<TD>{$in_row["scope"]}</TD>";
		echo "<TD>". substr($in_row["problemstart"], 5, 11) ."</TD>";
		echo "</TR>\n";
		$i++;
		}		// end while()
	echo "</TABLE>";	
	}				// end if/else
?>
</FORM>
<BUTTON onclick = 'window.close()' STYLE = 'margin-top:32px;'>Finished</BUTTON>
<script>
//	document.write("<BUTTON onclick = 'javascript:history.go(-1)' STYLE = 'margin-top:32px;'>Back</BUTTON>");
	</script>
</CENTER>
</BODY>
</HTML>
