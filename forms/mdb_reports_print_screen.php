<?php
/*
12/22/11	New file, main page
*/
$sess_id = session_id();
$reporttype = (array_key_exists('reportType', $_GET)) ? $_GET['reportType'] : 1;
$member = (array_key_exists('member', $_GET)) ? $_GET['member'] : 0;
$team = (array_key_exists('team', $_GET)) ? $_GET['team'] : 0;
$reportTitle = (array_key_exists('reportTitle', $_GET)) ? $_GET['reportTitle'] : "";
if($reportTitle == "") {
	$reporttypes = array('Error', 'Training Report', 'Equipment Report', 'Vehicle Report', 'Clothing Report', 'Full Report', 'Training Due Report', 'Events Report', 'Contact List');
	$selectedReport = $reporttypes[$reporttype];
	} else {
	$selectedReport = $reportTitle;		
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tickets - MDB Reports Print Screen</title>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<script src="./js/misc_function.js" type="text/javascript"></script>
<script type="text/javascript">
window.onresize=function(){set_size();}
var viewportwidth, viewportheight, outerwidth, colwidth;

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
	outerwidth = viewportwidth * .98;
	colwidth = outerwidth * .90;
	if($('outer')) {$('outer').style.width = colwidth + "px";}
	if($('report_title')) {$('report_title').style.width = colwidth + "px";}
	if($('spacer')) {$('spacer').style.width = colwidth + "px";}
	if($('report_contents')) {$('report_contents').style.width = colwidth + "px";}
	set_fontsizes(viewportwidth, "popup");
	}

function get_report(id, theType, the_team) {
	$('report_contents').style.display = "block";
	$('report_contents').innerHTML = "<CENTER><IMG src='./images/animated_spinner.gif'></CENTER>"
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = "./ajax/mdb_reports.php?member=" + id + "&report=" + theType + "&team=" + the_team + "&session=<?php print MD5($sess_id);?>&version=" + randomnumber;
	sendRequest (url, pop_report_cb, "");			
	function pop_report_cb(req) {
		$('report_contents').style.display = "block";		
		$('report_contents').innerHTML = req.responseText;
		}
	}
</script>
 
</head>
<BODY onLoad = "get_report(<?php print $member;?>, <?php print $reporttype;?>, <?php print $team;?>);">
	<DIV id='outer' stle='position: absolute; top: 30px;'>
		<DIV id='column' style='position: relative; left: 5%;'>
			<DIV id='topbar' style='background-color: #CECECE; border: 2px outset #707070; width: 100%; position: fixed; top: 0px; left: 0px; z-index: 9999;'>
				<SPAN class='plain text' id='close_but' style='margin-right: 10px; float: right; width: 100px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='window.close();'><?php print get_text('Close');?> <IMG style='vertical-align: middle; float: right;' src="./images/close.png"/></SPAN>
				<SPAN class='plain text' id='print_but' style='margin-right: 10px; float: right; width: 100px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='window.print();'><?php print get_text('Print');?> <IMG style='vertical-align: middle; float: right;' src="./images/print_small.png"/></SPAN>
			</DIV>
			<DIV id='report_title' class='tablehead text_large text_center text_bold' style='position: relative; top: 50px; display: block;'><?php print $selectedReport;?></DIV>
			<DIV id='spacer' class='spacer' style='position: relative; top: 100px; height: 10px; display: block;'>&nbsp;</DIV>
			<DIV id='report_contents' style='position: relative; top: 50px; width: 100%; display: block; text-align: left; display: none;'></DIV>
		</DIV>
	</DIV>
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
outerwidth = viewportwidth * .98;
colwidth = outerwidth * .90;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('column')) {$('column').style.width = colwidth + "px";}
if($('report_title')) {$('report_title').style.width = colwidth + "px";}
if($('spacer')) {$('spacer').style.width = colwidth + "px";}
if($('report_contents')) {$('report_contents').style.width = colwidth + "px";}
set_fontsizes(viewportwidth, "popup");
</SCRIPT>
</HTML>
