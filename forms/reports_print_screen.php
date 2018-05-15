<?php
error_reporting(E_ALL);									// 10/1/08
@session_start();
session_write_close();
do_login(basename(__FILE__));
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$currDate = date('m,d,Y');
$locale = get_variable('locale');	// 08/03/09
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
<SCRIPT>
window.onresize=function(){set_size();}
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var which='<?php print $report;?>';					// global - which report default
var what = '<?php print $what;?>';
var func = '<?php print $func;?>';
var currDate = "<?php print $currDate;?>";
var ticksel = '<?php print $tick_sel;?>';
var respsel = '<?php print $resp_sel;?>';
var startdate = '<?php print $startdate;?>';
var enddate = '<?php print $enddate;?>';;
var viewportwidth;
var viewportheight;
var outerwidth;
var currDate = "<?php print $currDate;?>";

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
	outerwidth = viewportwidth * .96;
	$('outer').style.width = outerwidth + "px";
	$('leftcol').style.width = outerwidth + "px";
	$('button_bar').style.width = outerwidth + "px";	
	$('report_header').style.width = outerwidth + "px";
	$('report').style.width = outerwidth + "px";
	$('report2').style.width = outerwidth + "px";
	$('report3').style.width = outerwidth + "px";
	set_tablewidths();
	}
	
function getHeaderHeight(element) {
	return element.clientHeight;
	}
	
function goGetit(func, what) {
	var randomnumber=Math.floor(Math.random()*99999999);
	$('report_header').style.display = "none";
	$('report').style.display = "none";
	$('report2').style.display = "none";
	$('report3').style.display = "none";
	$('report_header').innerHTML = "";
	$('report').innerHTML = "";
	$('report2').innerHTML = "";
	$('report3').innerHTML = "";
	if(func == "ugr") {
		var url = "./ajax/reports.php?report=" + which + "&func=" + what + "&date=" + currDate + "&tick_sel=" + ticksel + "&resp_sel=" + respsel + "&startdate=" + startdate + "&enddate=" + enddate + "&width=100%&do_print=1&version=" + randomnumber;
		} else {
		var url = "./ajax/reports.php?report=" + which + "&func=dr&date=" + what + "&tick_sel=" + ticksel + "&resp_sel=" + respsel + "&startdate=" + startdate + "&enddate=" + enddate + "&width=100%&do_print=1&version=" + randomnumber;
		}
	sendRequest (url,reports_cb, "");
	function reports_cb(req) {
		var theResponse = JSON.decode(req.responseText);
		$('report_header').innerHTML = theResponse[0];
		$('report').innerHTML = theResponse[1];
		setTimeout(function() {
			if(theResponse[0]) {
				$('report_header').innerHTML = "<IMG style='display: block; vertical-align: middle; float: left;' src=\"<?php print get_variable('report_graphic');?>\"/>";
				$('report_header').innerHTML += theResponse[0] + "<BR />";
				$('report_header').innerHTML += "<SPAN id='r_contact' CLASS='tablehead text' style='display: inline-block; vertical-align: middle; margin-right: 10px; float: right;'><?php print get_variable('report_contact');?></SPAN>";
				}
			if(theResponse[1]) {$('report').innerHTML = theResponse[1];}
			if(theResponse[2]) {$('report2').innerHTML = theResponse[2];}
			if(theResponse[3]) {$('report3').innerHTML = theResponse[3];}
			if($('report_header')) {$('report_header').style.width = outerwidth + "px";}
			if($('reportstable')) {$('reportstable').style.width = outerwidth + "px";}
			if($('left')) {$('left').style.width = outerwidth + "px";}
			if(theResponse[0]) {$('report_header').style.display = "block";}
			if(theResponse[1]) {$('report').style.display = "block";}
			if(theResponse[2]) {$('report2').style.display = "block";}
			if(theResponse[3]) {$('report3').style.display = "block";}
			set_tablewidths();
			}, 100)
		}
	}
	
function do_incLog() {
	var randomnumber=Math.floor(Math.random()*99999999);
	$('report').innerHTML = "";
	$('report2').innerHTML = "";
	$('report3').innerHTML = "";
	var url = "./ajax/reports.php?report=l&tick_sel=" + ticksel + "&do_print=1&version=" + randomnumber;
	sendRequest (url,reports_cb, "");
	function reports_cb(req) {
		var theResponse = JSON.decode(req.responseText);
		$('report_header').innerHTML = "<IMG style='display: block; vertical-align: middle; margin-left: auto; float: left;' src=\"<?php print get_variable('report_graphic');?>\"/>";
		$('report_header').innerHTML += theResponse[0] + "<BR />";
		$('report_header').innerHTML += "<SPAN id='r_contact' CLASS='tablehead text text_right' style='display: inline-block; vertical-align: middle; margin-left: auto; float: right;'><?php print get_variable('report_contact');?></SPAN>";
		$('report2').innerHTML = theResponse[1];
		}
	}
	
function set_tablewidths() {
	if($('reportstable')) {
		var theTable = document.getElementById('reportstable');
		$(theTable).style.width = outerwidth + "px";
		} else if($('left')) {
		var theTable = document.getElementById('left');	
		} else {
		return;
		}
	if(theTable) {
		var headerRow = theTable.rows[0];
		var tableRow = theTable.rows[1];
		if(tableRow) {
			for (var i = 0; i < tableRow.cells.length; i++) {
				if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
				}
			}
		if(getHeaderHeight(headerRow) >= 20) {
			var theRow = theTable.insertRow(1);
			theRow.style.height = "20px";
			for (var i = 0; i < tableRow.cells.length; i++) {
				var theCell = theRow.insertCell(i);
				theCell.innerHTML = " ";
				}
			}				
		}
	}
</SCRIPT>
</HEAD>
<?php
if($report == "l") {
	$loadstring = "do_incLog();";
	} else {
	$loadstring = "goGetit('" . $func . "', '" . $what . "');";	
	}
?>
<BODY onLoad = "<?php print $loadstring;?> location.href = '#top';">
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT> <!-- 10/2/10 -->
<A NAME="top" />
<DIV ID='to_bottom' style="position:fixed; top:20px; left:10px; height: 12px; width: 10px; z-index: 9999;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 /></DIV>
<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
	<DIV id='leftcol' style='position: absolute; left: 5px; top: 10px; z-index: 3; width: 98%;'>
		<DIV id='button_bar' class='but_container'>
			<SPAN id='print_but' class='plain' style='float: left; vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'>Print</SPAN>
			<SPAN id='close_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
		</DIV>
		<DIV id='report_table_head' class='heading text_large' style='position: relative; top: 60px; height: auto; padding-top: 5px; padding-bottom: 5px;'>
			<DIV id='report_header' style='text-align: left; height: 50px;'></DIV>
		</DIV><BR /><BR />
		<DIV id='report' style='position: relative; top: 30px; text-align: center; width: 96%;'></DIV><BR /><BR />
		<DIV id='report2' style='position: relative; top: 20px; text-align: center; width: 96%;'></DIV><BR /><BR />
		<DIV id='report3' style='position: relative; top: 20px; text-align: center; width: 96%;'></DIV><BR /><BR /><BR /><BR />
	</DIV>
</DIV>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></DIV>
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
outerwidth = viewportwidth * .96;
$('outer').style.width = outerwidth + "px";
$('leftcol').style.width = outerwidth + "px";
$('button_bar').style.width = outerwidth + "px";	
$('report_header').style.width = outerwidth + "px";
$('report').style.width = outerwidth + "px";
$('report2').style.width = outerwidth + "px";
$('report3').style.width = outerwidth + "px";
$('report_table_head').style.width = outerwidth + "px";
</SCRIPT>
</BODY>
</HTML>
<?php
exit();