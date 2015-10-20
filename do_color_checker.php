<?php
/*
3/15/11	initial release, color check file for checking colors configured for css day and night.
*/

error_reporting(E_ALL);		//	

//@session_start();
require_once('./incs/functions.inc.php');
if (!(empty($_GET))) {
	$func = $_GET['func'];
	$mode = $_GET['mode'];
	$doc_bg = "#" . $_GET['bgc'];
	$doc_txt = "#" . $_GET['txt'];	
	$sev_norm = "#0000FF";
	$sev_med = "#008000";
	$sev_high = "#C00000";
	$even = "#" . $_GET['rl'];
	$odd = "#" . $_GET['rd'];
	$plain = "#" . $_GET['plain'];	
	$links = "#" . $_GET['links'];
	$headings = "#" . $_GET['header'];	
	$other = "#" . $_GET['otxt'];	
	$hdgb = "#" . $_GET['hdgb'];	
	$hdgt = "#" . $_GET['hdgt'];	
	$spacer = "#" . $_GET['spacer'];	
	$inpb = "#" . $_GET['inpb'];	
	$inpt = "#" . $_GET['inpt'];		
	$legend = "#" . $_GET['legend'];		
	$smb = "#" . $_GET['smb'];		
	$smt = "#" . $_GET['smt'];	
	$titlebar = "#" . $_GET['titlebar'];
	} else {
	Print "Incorrectly called";
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>CSS Color Checker</TITLE>
<META NAME="Description" CONTENT="Tickets CSS Color Checker">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09">
<STYLE type="text/css">
BODY { background-color: <?php print $doc_gb;?>;	margin:0; font-weight: normal; font-size: 12px; color: <?php print $doc_txt;?>; 
	font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
TABLE {	border-collapse: collapse;}
INPUT { background-color: <?php print $inpb;?>; font-weight: normal; font-size: 12px; color: <?php print $inpt;?>; 
	font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
TEXTAREA { background-color: <?php print $inpb;?>; font-weight: normal; font-size: 12px; color: <?php print $inpt;?>; 
	font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
SELECT { background-color: <?php print $smb;?>;font-weight: normal; font-size: 12px; color: <?php print $smt;?>; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: underline;}
A {	font-weight: bold; font-size: 12px; color: <?php print $links;?>; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
li.mylink { font-weight: bold; font-size: 24px; color: <?php print $links;?>; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.emph { background-color: #99b2cc; font-size: 12px; color: #ffffff; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
.severity_high { font-weight: bold; font-size: 10px; color: <?php print $sev_high;?>; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.severity_medium { font-weight: bold; font-size: 10px; color: <?php print $sev_med;?>; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.severity_normal { font-weight: bold; font-size: 10px; color: <?php print $sev_norm;?>; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.found  { background-color: #000000; color: #ffffff;}
.scheduled { white-space:nowrap; background-color: #0000FF; color: #FFFFFF; font-size: 12px; font-weight: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.td_label { background-color: <?php print $doc_gb;?>; font-weight: bold; font-size: 12px; color: #000000; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.td_mand { font-weight: bold; font-size: 12px; color: #CC0000; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
.td_data { white-space:nowrap; background-color: <?php print $doc_gb;?>; font-size: 12px; color: #000000; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.emph { background-color: #99b2cc; font-size: 12px; color: #ffffff; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}
.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  	padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #EFEFEF;font-weight: bolder;}
.header { font-weight: bold; font-size: 12pt; color: <?php print $hdgt;?>; background-color: <?php print $hdgb;?>; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}	
#td_header { font-weight: bold; font-size: 15px; color: #000000; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;}	
tr.even { background-color: <?php print $even;?>;}
tr.odd { background-color: <?php print $odd;?>;}
tr.plain { background-color: <?php print $plain;?>;}
tr.spacer { background-color: <?php print $spacer;?>;}
tr.heading { background-color: <?php print $hdgb;?>; color: <?php print $hdgt;?>;}
.button { background-color: #CECECE; color: #000000; font: normal 12px Arial, Helvetica, sans-serif; color:#000000; border-width: 4px; border-style: outset; border-color: #505050;
  	padding:2px; vertical-align:middle;}
.button:hover { background-color: #EFEFEF; color: #000000; font: normal 12px Arial, Helvetica, sans-serif; color:#000000; border-width: 4px; border-style: inset; border-color: #505050;
  	padding:2px; vertical-align:middle;}	
.cat_button { color: <?php print $other;?>; float:left; padding:2px; vertical-align:middle;}
.pri_button { color: <?php print $other;?>; float:left; padding:2px; vertical-align:middle;}
.conf_next_button { font-size: 10px; color: green; float:left; padding:2px;	vertical-align:middle;}
.conf_can_button { font-size: 10px; color: red; float:left; padding:2px; vertical-align:middle;}
.other_text { color: <?php print $other;?>; font-size: 12pt; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
.legend { color: <?php print $legend;?>; font-size: 10pt; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
.titlebar_text { color: <?php print $titlebar;?>; } 

</STYLE>	
</HEAD>
<?php

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}

unset($result);

$icons = $GLOBALS['icons'];				// 1/1/09
$sm_icons = $GLOBALS['sm_icons'];

function get_icon_legend (){			// returns legend string - 1/1/09
	global $u_types, $sm_icons, $icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $u_types[$row['type']];
		$print .= "\t\t<DIV style='text-align: center; vertical-align: middle; float: left;'>" .$temp[0] . " &raquo; <IMG SRC = './our_icons/" . $icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0>&nbsp;&nbsp;&nbsp;</DIV>\n";
		}
	return $print;
	}			// end function get_icon_legend ()

switch($func) {
	case "main":
		?>
		<BODY style='background: <?php print $doc_bg;?>;'>
		<CENTER>
		<TABLE STYLE="width: 90%; border-bottom: #CECECE;">
		<TR><TD CLASS="titlebar_text">Tickets 2.12 B Beta on www.yourdomain.com</TD><TD><SPAN CLASS="plain">Logout</SPAN></TD></TR>
		<TR><TD COLSPAN=2 ><SPAN CLASS="plain">Situation</SPAN>
				<SPAN CLASS="plain">New</SPAN>
				<SPAN CLASS="plain">Units</SPAN>
				<SPAN CLASS="plain">Facs</SPAN>
				<SPAN CLASS="plain">Search</SPAN>
				<SPAN CLASS="plain">Reports</SPAN>
				<SPAN CLASS="plain">Confug</SPAN>
				<SPAN CLASS="plain">SOPs</SPAN>
				<SPAN CLASS="plain">Chat</SPAN>
				<SPAN CLASS="plain">Help</SPAN>
				<SPAN CLASS="plain">Log</SPAN>
				<SPAN CLASS="plain">Full scr</SPAN>
				<SPAN CLASS="plain">Links</SPAN>
				<SPAN CLASS="plain">Board</SPAN>
				<SPAN CLASS="plain">Mobile</SPAN></TD></TR>
		</TABLE>
		<BR />
		<TABLE STYLE="width: 90%">
		<TR CLASS="header"><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'>Current Situation - Tickets User (This is the header text color)</FONT></TD></TR>
		<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'><FONT STYLE="color: #505050">This is the Spacer bar</FONT></TD></TR>
		<TR VALIGN="top">
		<TD STYLE="width: 50%">
		<TABLE STYLE="width: 100%">
		<TR CLASS="even"><TD WIDTH='50%' CLASS="severity_high">Severity high with even row color</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD</TR>
		<TR CLASS="odd"><TD WIDTH='50%' CLASS="severity_high">Severity high with odd row color</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD</TR>
		<TR CLASS="even"><TD WIDTH='50%' CLASS="severity_medium">Severity medium with even row color</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD</TR>
		<TR CLASS="odd"><TD WIDTH='50%' CLASS="severity_medium">Severity medium with odd row color</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD</TR>
		<TR CLASS="even"><TD WIDTH='50%' CLASS="severity_normal">Severity normal with even row color</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD</TR>
		<TR CLASS="odd"><TD WIDTH='50%' CLASS="severity_normal">Severity normal with odd row color</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD</TR>
		<TR><TD COLSPAN="2">&nbsp;</TD></TR>
		<TR CLASS="plain"><TD WIDTH='50%'>Plain row data</TD><TD WIDTH='50%' CLASS="td_data">Normal Table data</TD></TR>
		<TR><TD COLSPAN="2">&nbsp;</TD></TR>
		<TR><TD COLSPAN="2" CLASS="legend"><CENTER>Legend Text: <?php print get_icon_legend();?><CENTER></TD></TR>
		</TABLE>
		<CENTER><A HREF="#">This is a Link</A></CENTER>
		<TD CLASS="td_label" STYLE="width: 50%; vertical-align: middle;">
		<TABLE>
		<TR><TD><IMG SRC="map.png" ALT="This is the Map"></TD></TR>
		<TR><TD><CENTER><A HREF="#">Grid</A>&nbsp;&nbsp;<A HREF="#">Traffic</A></CENTER></TD></TR>
		</TABLE>
		</TD></TR>
		</TABLE>
		<BR /><BR /><A HREF="do_color_checker.php?mode=<?php print $mode;?>&func=forms&bgc=<?php print str_replace("#","",$doc_bg);?>&txt=<?php print str_replace("#","",$doc_txt);?>&rl=<?php print str_replace("#","",$even);?>&rd=<?php print str_replace("#","",$odd);?>&plain=<?php print str_replace("#","",$plain);?>&hdgb=<?php print str_replace("#","",$hdgb);?>&hdgt=<?php print str_replace("#","",$hdgt);?>&spacer=<?php print str_replace("#","",$spacer);?>&links=<?php print str_replace("#","",$links);?>&header=<?php print str_replace("#","",$headings);?>&inpb=<?php print str_replace("#","",$inpb);?>&inpt=<?php print str_replace("#","",$inpt);?>&otxt=<?php print str_replace("#","",$other);?>&smb=<?php print str_replace("#","",$smb);?>&smt=<?php print str_replace("#","",$smt);?>&legend=<?php print str_replace("#","",$legend);?>&titlebar=<?php print str_replace("#","",$titlebar);?>" CLASS="button"/>Form Colors</A><BR /><BR />
		<A HREF="#" CLASS="button" OnClick="window.close();"/>Close Window</A><BR /><BR />
		</CENTER></BODY></HTML>	
	<?php
	break;

	case "forms":
?>
		<BODY style='background: <?php print $doc_bg;?>;'>
		<CENTER>
		<TABLE STYLE="width: 90%; border-bottom: #CECECE;">
		<TR><TD CLASS="titlebar_text">Tickets 2.12 B Beta on www.yourdomain.com</TD><TD><SPAN CLASS="plain">Logout</SPAN></TD></TR>
		<TR><TD COLSPAN=2 >
		<SPAN CLASS="plain">Situation</SPAN>
		<SPAN CLASS="plain">New</SPAN>
		<SPAN CLASS="plain">Units</SPAN>
		<SPAN CLASS="plain">Facs</SPAN>
		<SPAN CLASS="plain">Search</SPAN>
		<SPAN CLASS="plain">Reports</SPAN>
		<SPAN CLASS="plain">Confug</SPAN>
		<SPAN CLASS="plain">SOPs</SPAN>
		<SPAN CLASS="plain">Chat</SPAN>
		<SPAN CLASS="plain">Help</SPAN>
		<SPAN CLASS="plain">Log</SPAN>
		<SPAN CLASS="plain">Full scr</SPAN>
		<SPAN CLASS="plain">Links</SPAN>
		<SPAN CLASS="plain">Board</SPAN>
		<SPAN CLASS="plain">Mobile</SPAN></TD></TR>
		</TABLE>	
		<BR />
		<TABLE STYLE="width: 90%">
		<FORM>
		<TR CLASS="even"><TD WIDTH='50%' CLASS="td_label">Input Box</TD><TD WIDTH='50%' CLASS="td_data"><INPUT TYPE=text NAME=inputbox SIZE=20 MAXLENGTH=20 VALUE="Some Text Here"></TD></TR>
		<TR CLASS="odd"><TD WIDTH='50%' CLASS="td_label">Textarea</TD><TD WIDTH='50%' CLASS="td_data"><TEXTAREA cols="30" rows="5" name="myname">This is a Textarea.</TEXTAREA></TD></TR>
		<TR CLASS="even"><TD WIDTH='50%' CLASS="td_label"><TD WIDTH='50%' CLASS="td_data">
		<SELECT NAME="SELECT">
		<OPTION VALUE=0 SELECTED>SELECT AN ITEM
		<OPTION VALUE=1>INCIDENT
		<OPTION VALUE=2>RESPONDER
		<OPTION VALUE=3>FACILITY
		</SELECT>
		</TD></TR></FORM></TABLE>
		<BR /><BR /><A HREF="#" CLASS="button" OnClick="window.close();"/>Close Window</A><BR /><BR />
		</CENTER></BODY></HTML>
<?php
	break;
	
	default:
	print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
}	
?>	


