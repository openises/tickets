<?php
/*
6/6/08 revised to accommodate deleted incident and unit records, these identified by a # at its index value
8/7/08 added ACTION & PATIENT delete types
8/9/08 calculate graphics width
8/15/08	mysql_fetch_array to mysql_fetch_assoc - performance
8/15/08	handle dropped tickets
10/1/08	added error reporting
11/21/08 removed istest.inc.php include
1/21/09 corrected log info handling
1/21/09 added show butts - re button menu
1/31/09 dispatch function added
2/2/09 accommodate trashed unit status values
2/6/09 added dispatch statistics
2/8/09 added selected unit/incident
2/24/09 added dollar function
3/23/09 fixes per freitas email
4/11/09 responder sort by name
8/3/09 Added switch function to change date format dependant on locale setting. Fixed initial display to default to unit report
8/10/09 deleted locale = '2'
10/31/09 corrected  dispatch log no-data display
3/18/10 added incident log report
3/23/10 added optgroups to select lists
3/24/10 trim() added
3/25/10 log codes inc file added
4/4/10 heading alignments
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/27/10 added 'after-action' report
10/4/10 added incident management report
11/29/10 locale == 2 handling added
12/3/10 get_text added for captions
12/30/10 sql correction applied
1/14/10 'error' code handling added to Station report
1/16/11 get_text 'Units' added
3/15/11 added reference to stylesheet.php for revisable day night colors.
3/23/11 fix for date variable not defined when reports submitted with $_POST set
4/1/11 Fixed array of incident status types to include scheduled.
4/12/11 Revised sql to reduce un-needed fields
4/14/11 added shorten() width factors as a function of user monitor width
4/24/11  problemstart data added to dispatch report
4/5/11 get_new_colors() added
6/4/11  added facility actions LOG_CALL_U2FENR, etc. to the test
6/8/11 do_dispreport complete re-write, using problemstart as base for elapsed times
7/22/11 - correction per MC email.
7/24/11 corrections to qualifier per MC email.
11/4/11 - AS corrections to Unit log per AJ email; handle final unprinted log entry
5/29/12 - AS corrections to avoid using mysql unixtimestamp and SQL to reduce data load returned
10/24/12 - rewrite dispatch report to use assigns, vs. log data
12/1/2012 - re-do re unix SQL time replacement
1/7/2013 - date correction, use setting disp_stat for column headings
2/4/2013 - Change to after action report to add associated messages to ticket detail.
5/31/2013 - strtotime() applied for date arithnetic/conversion
1/2/2015 - shortened scope string, per JB site
*/
error_reporting(E_ALL);									// 10/1/08
$asof = "3/24/10";

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10
do_login(basename(__FILE__));
require_once('./incs/log_codes.inc.php'); 				// 3/25/10
$img_width  = round(.7*$_SESSION['scr_width']/3);		//8/9/08

if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

//$ionload =  ((isset($_POST) && isset($_POST['frm_group']) && $_POST['frm_group']=='i'))? " inc_onload();": "";

extract($_GET);
extract($_POST);

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

$locale = get_variable('locale');	// 08/03/09

$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");

$theWidth = (!empty($_POST) && $_POST['frm_full_w']==1) ? "96%" : "50%";
$fullWidth = (!empty($_POST) && $_POST['frm_full_w']==1) ? 1 : 0;
$fullWidthChecked = (!empty($_POST) && $_POST['frm_full_w']==1) ? "CHECKED" : "";
$currDate = date('m,d,Y');

$evenodd = array ("even", "odd");	// CLASS names for alternating tbl row colors
// ================ report-specific variables ===============================================
// IM
	$tick_array = array();
	$deltas = array();
	$counts = array();
	$severities = array ();
	$units_str = $today = $today_ref = "";

// ================ end report-specific variables ===============================================
if (empty($_POST)) {				// default to today

	switch($locale) {
		case "0":
		$frm_date = date('m,d,Y');
		$full_date_fmt = date('n/j/y G:i');
		break;

		case "1":
		case "2":				// 11/29/10

		$frm_date = date('m,d,Y');
		$full_date_fmt = date('j/n/y G:i');
		break;

//		case "2":								// 8/10/09
//		$frm_date = date('m,d,Y');
//		$full_date_fmt = date('j/n/y G:i');
//		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";

		}				// end switch
	$frm_func = "dr";				// single day report
	$group = "u";
	} else {
	switch($locale) { 	//	3/23/11
		case "0":
		$frm_date = array_key_exists('frm_date', ($_POST))? $_POST['frm_date']: date('m,d,Y');
		$full_date_fmt = date('n/j/y G:i');
		break;

		case "1":
		case "2":				// 11/29/10

		$frm_date = date('m,d,Y');
		$full_date_fmt = date('j/n/y G:i');
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";

		}				// end switch($locale)

	$frm_func = (array_key_exists( 'frm_func', $_POST))? $_POST['frm_func']: "dr";		//	4/21/11
	$group = $_POST['frm_group'];
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Reports Module</TITLE>
	<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" 				CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date($full_date_fmt, filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css">	<!-- 3/15/11 -->
<style type="text/css">
.hovermenu ul{font:bold 13px arial;padding-left:0;margin-left:0;height:20px;border-radius:.5em;}
.hovermenu ul li{list-style:none; display:inline;}
.hovermenu ul li {padding:2px 0.5em; float:left; color:black; background-color:##DEE3E7; border:2px solid #EFEFEF; width:81px;text-align: center;border-radius:.5em;}
.hovermenu ul li:hover{background-color:#DEE3E7; border-style:outset;}
.hovermenu2 ul{font-weight:bold; padding-left:0; margin-left:0; height:20px; border-radius:.5em;}
.hovermenu2 ul li{list-style:none; display:inline;}
.hovermenu2 ul li {padding:2px 0.5em; float:left; color:black; background-color:#DEE3E7; border:2px solid #EFEFEF; width:179px;text-align: center}
.hovermenu2 ul li:hover{background-color:#DEE3E7; border-style:outset; border-radius:.5em;}
.typical	{color:#000000; font-weight: normal;}
.high		{color:#347C17; font-weight: bold;}
.highest	{color:#FF0000; font-weight: bold;}
p.page { page-break-after: always; }
table td + td { border-left:2px solid red; }
div.scrollingArea { max-height: 500px; overflow: auto; overflow-x: hidden;}
</style>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
	window.onresize=function(){set_size()};
<?php
	print "//  {$asof}  \n";
?>
	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	var which='<?php print $group;?>';					// global - which report default
	var what="<?php print $currDate;?>";
	var func="";
	var viewportwidth;
	var viewportheight;
	var outerwidth;
	var outerheight;
	var ticksel = 0;
	var respsel = 0;
	var currDate = "<?php print $currDate;?>";
	var theWidth = "50%";
	var lit=new Array();
	var reportname = "report";
	var organisation = 0;
	var region = 0;
	var specificdates= false;
	var startdate = 0;
	var enddate = 0;
	
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
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		$('outer').style.width = outerwidth + "px";
		set_fontsizes(viewportwidth, "fullscreen");
		}

	function do_full_w (the_form) {							// 4/24/11
		var the_val = (the_form.full.checked)? 1:0;
		theWidth = (the_val == 1) ? "96%" : "50%";
		}

	function viewT(id) {			// view ticket
		return;
		}

	function viewU(id) {			// view unit
		return;
		}
		
	function showButs() {
		$('print_but').style.display = "inline-block";
		$('downloaddoc_but').style.display = "inline-block";
		$('downloadxls_but').style.display = "inline-block";
		}
		
	function hideButs() {
		$('print_but').style.display = "none";
		$('downloaddoc_but').style.display = "none";
		$('downloadxls_but').style.display = "none";		
		}
		
	function useSpecificdates() {
		if(specificdates == false) {
			specificdates = true; 
			litSpecific();
			$('dates_table').style.display = $('dates_table2').style.display = "none";
			$('specdate_sub').style.display ='inline-block';
			} else {
			specificdates = false;
			unlitSpecific();
			$('dates_table').style.display = $('dates_table2').style.display = "block";
			$('specdate_sub').style.display ='none';
			}
		}
		
	function do_unlock_dates(theForm) {
		if(specificdates) {
			theForm.frm_year_start_date.disabled = false;
			theForm.frm_month_start_date.disabled = false;
			theForm.frm_day_start_date.disabled = false;
			theForm.frm_year_end_date.disabled = false;
			theForm.frm_month_end_date.disabled = false;
			theForm.frm_day_end_date.disabled = false;
			} else {
			theForm.frm_year_start_date.disabled = true;
			theForm.frm_month_start_date.disabled = true;
			theForm.frm_day_start_date.disabled = true;
			theForm.frm_year_end_date.disabled = true;
			theForm.frm_month_end_date.disabled = true;
			theForm.frm_day_end_date.disabled = true;
			}
		}
		
	function openPrintscreen() {
		var randomnumber=Math.floor(Math.random()*99999999);
		if(specificdates) {
			startdate = document.sel_form.frm_month_start_date.value + ", " + document.sel_form.frm_day_start_date.value + ", " + document.sel_form.frm_year_start_date.value;
			enddate = document.sel_form.frm_month_end_date.value + ", " + document.sel_form.frm_day_end_date.value + ", " + document.sel_form.frm_year_end_date.value;
			} else {
			startdate = 0;
			enddate = 0;				
			}
		if(which == "l") {
			var url = "./ajax/reports.php?report=l&tick_sel=" + ticksel + "&width=100%&version=" + randomnumber;
			} else if(which != "l" && func == "ugr") {
			var url = "reports_print.php?report=" + which + "&func=ugr&what=" + what + "&date=" + currDate + "&tick_sel=" + ticksel + "&resp_sel=" + respsel + "&width=100%&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
			} else {
			var url = "reports_print.php?report=" + which + "&func=udr&what=" + what + "&date=" + what + "&tick_sel=" + ticksel + "&resp_sel=" + respsel + "&width=100%&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
			}
		var printWindow = window.open(url, 'printWindow', 'resizable=1, scrollbars, height=600, width=1000, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { printWindow.focus(); }, 1);
		}
	
	function downloadReport(mode) {
		var randomnumber=Math.floor(Math.random()*99999999);
		var title = base64_encode(reportname);
		if(specificdates) {
			startdate = document.sel_form.frm_month_start_date.value + ", " + document.sel_form.frm_day_start_date.value + ", " + document.sel_form.frm_year_start_date.value;
			enddate = document.sel_form.frm_month_end_date.value + ", " + document.sel_form.frm_day_end_date.value + ", " + document.sel_form.frm_year_end_date.value;
			} else {
			startdate = 0;
			enddate = 0;				
			}
		if(which == "l") {
			var url = "./ajax/reports.php?report=l&ticksel=" + ticksel + "&width=100%&mode=" + mode + "&title=" + title + "&version=" + randomnumber;
			} else if(which != "l" && func == "ugr") {
			var url = "download_report.php?report=" + which + "&func=ugr&what=" + what + "&date=" + currDate + "&ticksel=" + ticksel + "&respsel=" + respsel + "&width=100%&mode=" + mode + "&title=" + title + "&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
			} else {
			var url = "download_report.php?report=" + which + "&func=udr&what=dr&date=" + what + "&ticksel=" + ticksel + "&respsel=" + respsel + "&width=100%&mode=" + mode + "&title=" + title + "&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
			}
		var printWindow = window.open(url, 'printWindow', 'resizable=1, scrollbars, height=600, width=1000, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { printWindow.focus(); }, 1);		
		}
		
	function setDates() {
		goGetit('ugr', 's', 1);
		}
		
	function goGetit(func, what, usespec) {
		$('report').innerHTML = "";
		$('report_header').innerHTML = "";
		$('report2').innerHTML = "";
		$('report3').innerHTML = "";
		$('reports').style.display='block';
		$('report_wrapper').style.display = "block";
		$('report_selection').style.display='none';
		$('report').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		if(usespec == 1) {
			if(specificdates) {
				startdate = document.sel_form.frm_month_start_date.value + ", " + document.sel_form.frm_day_start_date.value + ", " + document.sel_form.frm_year_start_date.value;
				enddate = document.sel_form.frm_month_end_date.value + ", " + document.sel_form.frm_day_end_date.value + ", " + document.sel_form.frm_year_end_date.value;
				} else {
				startdate = 0;
				enddate = 0;				
				}
			}
		hideButs();
		var randomnumber=Math.floor(Math.random()*99999999);
		if(func == "ugr") {
			var url = "./ajax/reports.php?report=" + which + "&func=" + what + "&date=" + currDate + "&tick_sel=" + ticksel + "&resp_sel=" + respsel + "&width=" + theWidth + "&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
			} else {
			var url = "./ajax/reports.php?report=" + which + "&func=dr&date=" + what + "&tick_sel=" + ticksel + "&resp_sel=" + respsel + "&width=" + theWidth + "&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
			}
		sendRequest (url,reports_cb, "");
		function reports_cb(req) {
			var theResponse = JSON.decode(req.responseText);
			$('report_header').innerHTML = theResponse[0];
			$('report').innerHTML = theResponse[1];
			if(theResponse[2]) {$('report2').innerHTML = theResponse[2]; $('report2').style.display = "block";}
			if(theResponse[3]) {$('report3').innerHTML = theResponse[3]; $('report3').style.display = "block";}
			if(theResponse[6]) {reportname = theResponse[6];}
			$('showhide_but').innerHTML = "Show Menu";
			showButs();
			set_tablewidths();
			}
		}
		
	function do_incLog() {
		if(specificdates) {
			startdate = document.sel_form.frm_month_start_date.value + ", " + document.sel_form.frm_day_start_date.value + ", " + document.sel_form.frm_year_start_date.value;
			enddate = document.sel_form.frm_month_end_date.value + ", " + document.sel_form.frm_day_end_date.value + ", " + document.sel_form.frm_year_end_date.value;
			} else {
			startdate = 0;
			enddate = 0;				
			}
		hideButs();
		var randomnumber=Math.floor(Math.random()*99999999);
		$('report').innerHTML = "";
		$('report2').innerHTML = "";
		$('report3').innerHTML = "";
		var url = "./ajax/reports.php?report=l&tick_sel=" + ticksel + "&width=" + theWidth + "&organisation=" + organisation + "&startdate=" + startdate + "&enddate=" + enddate + "&version=" + randomnumber;
		sendRequest (url,reports_cb, "");
		function reports_cb(req) {
			var theResponse = JSON.decode(req.responseText);
			$('reports').style.display='block';
			$('report_wrapper').style.display = "none";
			$('report3').style.display = "none";
			$('report2').style.display = "block";
			$('report_header').innerHTML = theResponse[0];
			$('report2').innerHTML = theResponse[1];
			if(theResponse[6]) {reportname = theResponse[6];}
			$('report_selection').style.display='none';
			$('showhide_but').innerHTML = "Show Menu";
			showButs();
			}
		}
		
	function getHeaderHeight(element) {
		return element.clientHeight;
		}
		
	function set_tablewidths() {
		var theTable = document.getElementById('reportstable');
		if(theTable) {
			var headerRow = theTable.rows[0];
			for(var r = 1; r < theTable.rows.length; r++) {
				var tableRow = theTable.rows[r];
				if(tableRow.cells.length > 1) {
					break;
					}
				}
			if(tableRow) {
				for (var i = 0; i < tableRow.cells.length; i++) {
					if(tableRow.cells[i].clientWidth > headerRow.cells[i].clientWidth) {
						if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth + "px";}
						} else {
						if(tableRow.cells[i] && headerRow.cells[i]) {tableRow.cells[i].style.width = headerRow.cells[i].clientWidth + "px";}							
						}
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
		
	function toUDRnav(date_in) {					// daily report
		document.udr_form.frm_date.value=date_in;	// set date params
		document.udr_form.frm_group.value=which;
		document.udr_form.frm_resp_sel.value=document.sel_form.frm_unit_id.options[document.sel_form.frm_unit_id.selectedIndex].value;	// 2/8/09
		document.udr_form.frm_tick_sel.value=document.sel_form.frm_ticket_id.options[document.sel_form.frm_ticket_id.selectedIndex].value;
		get_report("udr", document.udr_form.frm_date.value, document.udr_form.frm_group.value, document.udr_form.frm_resp_sel.value, document.udr_form.frm_tick_sel.value);
		}

	function do_ugr(instr) {						// select for generic
		document.ugr_form.frm_func.value=instr;
		document.ugr_form.frm_group.value=which;
		document.ugr_form.frm_resp_sel.value=document.sel_form.frm_unit_id.options[document.sel_form.frm_unit_id.selectedIndex].value;	// 2/8/09
		document.ugr_form.frm_tick_sel.value=document.sel_form.frm_ticket_id.options[document.sel_form.frm_ticket_id.selectedIndex].value;
		get_report(document.ugr_form.frm_func.value, "ugr", document.ugr_form.frm_group.value, document.ugr_form.frm_resp_sel.value, document.ugr_form.frm_tick_sel.value);
		}		// end do_ugr()
		
	function get_report(theFunc, theDate, theReport, theResp, theTicket) {
		if(theFunc == "udr") {
			document.udr_form.submit();
			}
		if(theDate == "ugr") {
			document.ugr_form.submit();
			}
		}

	function ck_frames() {		// ck_frames()
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()

	function open_tick_window (id) {				// 4/14/11
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=720, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}
		
	function showhide() {
		if($('report_selection').style.display=='none') {
			$('report_selection').style.display='inline';
			$('showhide_but').innerHTML = "Hide Menu";
			$('reports').style.display='none';
			} else {
			$('report_selection').style.display='none';
			$('showhide_but').innerHTML = "Show Menu";
			}
		}
		
	function doLit(what) {
		unLit();
		CngClass(what, "signal_b text");
		lit[what] = true;
		}
		
	function litSpecific() {
		CngClass('lock_s', "signal_b text");
		lit['lock_s'] = true;
		}
		
	function unlitSpecific() {
		CngClass('lock_s', "plain text");
		lit['lock_s'] = false;		
		}
	
	function unLit() {
		CngClass('ulog_but', 'plain text');
		CngClass('comms_but', 'plain text');
		CngClass('flog_but', 'plain text');
		CngClass('dlog_but', 'plain text');
		CngClass('slog_but', 'plain text');
		CngClass('ilog_but', 'plain text');
		CngClass('alog_but', 'plain text');
		CngClass('mlog_but', 'plain text');
		CngClass('inc_log_btn', 'plain text');
		CngClass('region_but', 'plain text');
		CngClass('billreport_but', 'plain text');
		lit['ulog_but'] = false;
		lit['comms_but'] = false;
		lit['flog_but'] = false;
		lit['dlog_but'] = false;
		lit['slog_but'] = false;
		lit['ilog_but'] = false;
		lit['alog_but'] = false;
		lit['mlog_but'] = false;
		lit['inc_log_btn'] = false;
		lit['region_but'] = false;
		lit['billreport_but'] = false;	
		}
		
	function do_hover (the_id) {
		if(lit[the_id]) {return;}
		CngClass(the_id, 'hover text');
		return true;
		}

	function do_plain (the_id) {				// 8/21/10
		if(lit[the_id]) {return;}
		CngClass(the_id, 'plain text');
		return true;
		}
		
	</SCRIPT>

	</HEAD>
<BODY onLoad = "ck_frames()">
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT> <!-- 10/2/10 -->

<A NAME="top" />
<DIV ID='to_bottom' style="position:fixed; top:20px; left:10px; height: 12px; width: 10px; z-index: 9999;" onclick = "location.href = '#bottom';">
<IMG SRC="markers/down.png" BORDER=0 /></DIV>
<DIV id = "outer" style='position: absolute; left: 2%; width: 96%;'>
	<DIV id='button_bar' class='but_container' style='width: 100%;'>
		<SPAN id='showhide_but' class='plain text' style='width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='showhide();'>Hide Menu</SPAN>
		<SPAN id='print_but' class='plain text' style='width: 100px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='openPrintscreen();'>Print</SPAN>
		<SPAN id='downloaddoc_but' class='plain text' style='width: 100px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='downloadReport("doc");'>Download .doc</SPAN>
		<SPAN id='downloadxls_but' class='plain text' style='width: 100px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='downloadReport("xls");'>Download .xls</SPAN>
	</DIV>

<?php
	$i=1;
	$checked = array("a" => "", "u" => "", "c" => "", "d" => "", "s" => "", "i" => "", "f" => "", "m" => ""); // 8/3/09 added d option to array to allow default to unit report correctly
	$temp = (empty($_POST))? "u":  $_POST['frm_group']; 		// set selector fm last, default is unit
	$checked [$temp] = " CHECKED ";								// copy fm last

?>
	<BR CLEAR='left' />
	<BR />
	<DIV id='report_selection' style='position: fixed; top: 50px; left: 2%; z-index: 9999; border: 3px outset #707070;'>
		<TABLE CELLSPACING = 2 CELLPADDING = 2  BORDER=0 width='100%'>
		<TR CLASS='odd'><TH COLSPAN=99>Other Reports</TH></TR>
		<TR CLASS='odd'><TD>&nbsp;</TD></TR>
		<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'>
		<FORM NAME='sel_form' METHOD='post' ACTION = ''>
<?php
//			Unit Selector ------------------------------------------
			$unit_types = array();											// 3/23/10, 4/11/09
			$query = "SELECT *FROM `$GLOBALS[mysql_prefix]unit_types`";		// build array of type names
			$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
				$unit_types[$row['id']] = $row['name'];
				}

			print "Select " . get_text("Unit") . ": <SELECT NAME='frm_unit_id' onChange = \"respsel = this.value.trim();\">\n\t<OPTION VALUE=0 SELECTED>All</OPTION>\n";
			$query = "SELECT * , COUNT( `responder_id` ) FROM `$GLOBALS[mysql_prefix]log`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `$GLOBALS[mysql_prefix]log`.responder_id = r.id )
				GROUP BY `responder_id` HAVING COUNT( `responder_id` ) >=1
				ORDER BY `r`.`type`";

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$do_optgroup = $set_type = TRUE;
			$curr_type = "";
			$optgroup_close = "";

			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$the_name = explode ("/", $row['name']);
				if (array_key_exists($row['type'], $unit_types)) {
					if (!($curr_type == $row['type'])) {
						$curr_type = $row['type'];
						$type_label = $unit_types[$row['type']];
						$do_optgroup = TRUE;
						}
					if ($do_optgroup) {
						print "{$optgroup_close}\n<OPTGROUP LABEL='{$type_label}'>";
						$optgroup_close = "</OPTGROUP>";
						$do_optgroup = FALSE;
						}
					if (!(empty($row['name']))) {print "<OPTION VALUE={$row['responder_id']}>{$the_name[0]}</OPTION>\n";}
					}
				else {
					if (!(empty($row['name']))) {print "<OPTION VALUE={$row['responder_id']}>{$the_name[0]}</OPTION>\n";}
					}
				}				// end while ()
			print "</OPTGROUP></SELECT>\n";
		
//			Incident Selector ------------------------------------------

			$query = "SELECT *, COUNT(`scope`) FROM `$GLOBALS[mysql_prefix]ticket` GROUP BY `scope` HAVING COUNT(`scope`)>=1  AND status > 0";  // build assoc array of all tickets
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$tickets[$row['id']] = shorten($row['scope'], 60);		// 1/2/2015
				}

			print "&nbsp;&nbsp;&nbsp;&nbsp;Select {$incident}:
				<SELECT NAME='frm_ticket_id'  onChange = \" $('inc_log_btn').style.display = ''; ticksel = this.value.trim();\">\n\t" ;
			print "<OPTION VALUE=0 SELECTED>All</OPTION>\n";
			$query = "SELECT *, COUNT(`ticket_id`) FROM `$GLOBALS[mysql_prefix]log` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`t`.`id` = `l`.`ticket_id`)
				GROUP BY `ticket_id` HAVING COUNT(`ticket_id`)>=1
				ORDER BY `t`.`status` DESC, `t`.`id` ASC";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$do_optgroup = $set_status = TRUE;
			$curr_status = "";
			$status_vals = array ('err', 'closed', 'open', 'scheduled');	//	4/1/11
			$optgroup_close = "";
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				if (array_key_exists($row['ticket_id'], $tickets)) {
					if (!($curr_status == $row['status'])) {
						$curr_status = $row['status'];
						$stat_label = $status_vals[$row['status']];
						$do_optgroup = TRUE;
						}
					if ($do_optgroup) {
						print "{$optgroup_close}\n<OPTGROUP LABEL='{$stat_label}'>";
						$optgroup_close = "</OPTGROUP>";
						$do_optgroup = FALSE;
						}
					print "<OPTION VALUE='{$row['ticket_id']}'>{$tickets[$row['ticket_id']]}</OPTION>\n";
					}
				}
			print "\n</OPTGROUP></SELECT>\n";
			
//			Organisation Selector ------------------------------------------

			print "<SPAN id='org_ctrl' style='display: none;'>";
			print "&nbsp;&nbsp;&nbsp;&nbsp;Select Organisation";
			print "<SELECT NAME='frm_org_cntl' onChange = 'organisation = this.value.trim();'>";
			print "<OPTION VALUE=0 SELECTED>All</OPTION>\n";
			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]organisations`";		// 12/2/08
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				print "<OPTION value=" . $row['id'] . ">" . $row['name'] . "</OPTION>";			
				}	
			print "</SELECT></SPAN>";
			
//			Regions Selector ------------------------------------------

			print "<SPAN id='reg_ctrl' style='display: none;'>";
			print "&nbsp;&nbsp;&nbsp;&nbsp;Select Region";
			print "<SELECT NAME='frm_reg_cntl' onChange = 'region = this.value.trim();'>";
			print "<OPTION VALUE=0 SELECTED>All</OPTION>\n";
			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]region`";		// 12/2/08
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				print "<OPTION value=" . $row['id'] . ">" . $row['group_name'] . "</OPTION>";			
				}	
			print "</SELECT></SPAN>";
?>
		<INPUT TYPE='hidden' NAME='frm_full_w' VALUE=<?php print $fullWidth;?>>
		</B></TD></TR>
		<TR CLASS='odd'><TD COLSPAN=99>&nbsp;</TD></TR>
		<TR CLASS='odd'>
			<TD COLSPAN=99 ALIGN='center'>
				Start Date&nbsp;&nbsp;&nbsp;<?php print generate_dateonly_dropdown('start_date',0, TRUE);?>
				End Date&nbsp;&nbsp;&nbsp;<?php print generate_dateonly_dropdown('end_date',0, TRUE);?>
				<SPAN id='specdate_sub' CLASS='plain text' style='width: 105px; float: none; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='setDates();'>Submit</SPAN>
				<SPAN id='lock_s' CLASS='plain text' style='width: 105px; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='useSpecificdates(); do_unlock_dates(document.sel_form);'>Use Specific Dates</SPAN>
			</TD>
		</TR>		
		<TR CLASS='odd'><TD COLSPAN=8 CLASS='td_label' ALIGN='center'>&nbsp;</TD></TR>
		<TR CLASS='odd'>
			<TD COLSPAN=8 CLASS='td_label' style='text-align: center;'>
				<SPAN ID='ulog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='u'; doLit('ulog_but');"><?php print get_text("Unit"); ?> Log</SPAN>
				<SPAN ID='comms_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='c'; doLit('comms_but');"><?php print get_text("Comms report"); ?></SPAN>
				<SPAN ID='flog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='f'; doLit('flog_but');"><?php print get_text("Facility"); ?> Log</SPAN>
				<SPAN ID='dlog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='d'; doLit('dlog_but');"><?php print get_text("Dispatch"); ?> Log</SPAN>
				<SPAN ID='slog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='s'; doLit('slog_but');"><?php print get_text("Station"); ?> Log</SPAN>
				<SPAN ID='ilog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='i'; doLit('ilog_but');"><?php print $incident;?> Summary</SPAN>
				<SPAN ID='alog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='a'; doLit('alog_but');">After-action Report</SPAN>
				<SPAN ID='mlog_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='m'; doLit('mlog_but');"><?php print $incident;?> mgmt Report</SPAN>
				<SPAN ID='billreport_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='b'; doLit('billreport_but'); $('org_ctrl').style.display='inline'; $('reg_ctrl').style.display='none';">Organisation Billing Report</SPAN>
				<SPAN ID='region_but' CLASS='plain text' style='margin: 5px; width: 17%;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick ="Javascript: which='r'; doLit('region_but'); $('reg_ctrl').style.display='inline'; $('org_ctrl').style.display='none';">Region Report</SPAN>
				<SPAN ID="inc_log_btn" style='display: none; margin: 5px; width: 17%;' CLASS='plain text' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = "do_incLog(); Javascript: which = 'l'; doLit('inc_log_btn');"><?php print $incident;?> Log</SPAN>
			</TD>
		</TR>
		<TR CLASS='odd'>
		<TD COLSPAN=99 ALIGN='center'>
			<SPAN STYLE='WIDTH:100px; FLOAT:left;'>&nbsp;</SPAN>
			<BR />
			<BR />
			<DIV id='dates_table'>
<?php
				print "<SPAN id='lw' CLASS='plain text' style='width: 105px;'  onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= \"goGetit('ugr', 'lw', 0);\">Last Week</SPAN>";
				for ($j = -13; $j < 1; $j++)  {
					$temp = mktime(0,0,0,date('m'), date('d')+$j, date('Y'));
					print "<SPAN id='date" . $j . "' CLASS='plain text' style='width: 105px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = \"goGetit('udr', '" . date ('m,d,Y', $temp) . "', 0); Javascript: what='" . date ('m,d,Y', $temp) . "'; Javascript: func='udr';\">";

				$locale = get_variable('locale');	// 08/03/09
				switch($locale) {
					case "0":
					print date ("m/d", $temp);
					print "</SPAN>\n";
					break;

					case "1":
					case "2":				// 11/29/10

					print date ("d/m", $temp);
					print "</SPAN>\n";
					break;

					default:
						print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
					}

					if ($j== -7) {
						print "<BR /><BR /><SPAN id='cw' CLASS='plain text' style='width: 105px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= \"goGetit('ugr', 'cw', 0); Javascript: what='cw'; Javascript: func='ugr';\">This Week</SPAN>";
						$i++;
						}
					}				// end for ($j...)
?>
			</DIV>
		</TD>
		</TR>
		</FORM>

		<FORM NAME='udr_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'><!-- daily -->
		<TR CLASS='odd' width='100%'>
		<TD ALIGN='center' colspan=99>
			<DIV id='dates_table2'>
				<DIV STYLE='width: 100%; display: clock; text-align: left;'>
					<SPAN id='lm' CLASS='plain text' style='width: 225px; float: none; display: inline-block; text-align: center;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= "goGetit('ugr', 'lm', 0); Javascript: what='lm'; Javascript: func='ugr';"><?php print date("M `y", mktime(0, 0, 0, date("m")-1, 15,   date("Y")));?></SPAN>
					<SPAN id='cm' CLASS='plain text' style='width: 225px; float: none; display: inline-block; text-align: center;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= "goGetit('ugr', 'cm', 0); Javascript: what='cm'; Javascript: func='ugr';"><?php print date("M `y");?></SPAN>
					<SPAN id='ly' CLASS='plain text' style='width: 225px; float: none; display: inline-block; text-align: center;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= "goGetit('ugr', 'ly', 0); Javascript: what='ly'; Javascript: func='ugr';"><?php print date("Y", mktime(0, 0, 0, 1, 1,  date("Y")-1));?></SPAN>
					<SPAN id='cy' CLASS='plain text' style='width: 225px; float: none; display: inline-block; text-align: center;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick= "goGetit('ugr', 'cy', 0); Javascript: what='cy'; Javascript: func='ugr';"><?php print date("Y", mktime(0, 0, 0, 1, 1,  date("Y")));?></SPAN>
				</DIV>
			</DIV>
		</TD>
		</TR>
		</TABLE>
		<INPUT TYPE='hidden' NAME='frm_func' VALUE='dr'>
		<INPUT TYPE='hidden' NAME='frm_date' VALUE='<?php print date('m,d,Y'); ?>'>
		<INPUT TYPE='hidden' NAME='frm_group' VALUE='<?php print $group;?>'>
		<INPUT TYPE='hidden' NAME='frm_resp_sel' VALUE=''>									<!-- 2/8/09 -->
		<INPUT TYPE='hidden' NAME='frm_tick_sel' VALUE=''>
		<INPUT TYPE='hidden' NAME='frm_full_w' VALUE=<?php print $fullWidth;?>>
		</FORM>
		<FORM NAME='ugr_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'>	<!-- generic, date-driven -->
		<INPUT TYPE='hidden' NAME='frm_func' VALUE='w'>
		<INPUT TYPE='hidden' NAME='frm_date' VALUE='<?php print date('m,d,Y'); ?>'>
		<INPUT TYPE='hidden' NAME='frm_group' VALUE='<?php print $group;?>'>
		<INPUT TYPE='hidden' NAME='frm_resp_sel' VALUE=''>
		<INPUT TYPE='hidden' NAME='frm_tick_sel' VALUE=''>
		<INPUT TYPE='hidden' NAME='frm_full_w' VALUE=<?php print $fullWidth;?>>
		</FORM>

		 <FORM NAME='log_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'>
		 <INPUT TYPE='hidden' NAME='frm_group' VALUE='l'><!-- incident log -->
		 <INPUT TYPE='hidden' NAME='frm_tick_sel' VALUE=''>
		 <INPUT TYPE='hidden' NAME='frm_full_w' VALUE=<?php print $fullWidth;?>>
		 </FORM>

		<FORM NAME='T_nav_form' METHOD='get' TARGET = 'main' ACTION = "main.php">
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>

		<FORM NAME='U_nav_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
		<INPUT TYPE='hidden' 	NAME='id' VALUE=''>
		<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' 	NAME='view' VALUE='true'>
		</FORM>

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>
	</DIV>
	<DIV id='reports'>
		<DIV style='position: relative; top: 30px;' id='report_header'></DIV>
		<DIV id='report_wrapper' class="scrollableContainer" style='position: relative; top: 50px; left: 1.5%; width: 95%; display: none; height: 80%;'>
			<DIV id='report' class="scrollingArea"></DIV>
		</DIV><BR /><BR /><BR />
		<DIV id='report2' style='position: relative; top: 20px; left: 1.5%; text-align: center; width: 95%; display: none;'></DIV><BR /><BR />
		<DIV id='report3' style='position: relative; top: 20px; left: 1.5%; text-align: center; width: 95%; display: none;'></DIV><BR /><BR />
	</DIV>
</DIV>
<DIV ID='to_top' style="position:fixed; bottom:30px; left:10px; height: 12px; width: 10px; z-index: 9999;" onclick = "location.href = '#top';">
<IMG SRC="markers/up.png" BORDER=0 /></DIV>
<A NAME="bottom" />
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
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
$('outer').style.width = outerwidth + "px";
set_fontsizes(viewportwidth, "fullscreen");
doLit("ulog_but");

</SCRIPT>
</BODY></HTML>

<?php  /*
$GLOBALS['LOG_SIGN_IN']				= 1;
$GLOBALS['LOG_SIGN_OUT']			= 2;
$GLOBALS['LOG_COMMENT']				= 3;		// misc comment
$GLOBALS['LOG_INCIDENT_OPEN']		=10;
$GLOBALS['LOG_INCIDENT_CLOSE']		=11;
$GLOBALS['LOG_INCIDENT_CHANGE']		=12;
$GLOBALS['LOG_ACTION_ADD']			=13;
$GLOBALS['LOG_PATIENT_ADD']			=14;
$GLOBALS['LOG_INCIDENT_DELETE']		=15;		// added 6/4/08
$GLOBALS['LOG_UNIT_STATUS']			=20;
$GLOBALS['LOG_UNIT_COMPLETE']		=21;		// 	run complete
$GLOBALS['LOG_UNIT_CHANGE']			=22;

$GLOBALS['LOG_CALL_DISP']			=30;		// 1/20/09
$GLOBALS['LOG_CALL_RESP']			=31;
$GLOBALS['LOG_CALL_ONSCN']			=32;
$GLOBALS['LOG_CALL_CLR']			=33;

*/
?>





