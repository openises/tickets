<?php
/*
12/22/11	New file, main page
*/

require_once('./incs/functions.inc.php');
@session_start();
$sess_id = session_id();
if (isset($_GET['logout'])) {
	do_logout();
	exit();
	}
else {
	do_login(basename(__FILE__));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tickets Membership Database</title>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<script src="./js/misc_function.js" type="text/javascript"></script>
<script type="text/javascript">
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
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
	$('reportHeader').style.height = $('head_img').clientHeight + "px";
	set_fontsizes(viewportwidth, "fullscreen");
	}
<?php
	if (array_key_exists('log_in', $_GET)) {			// 12/26/09- array_key_exists('internet', $_SESSION)
?>
		parent.frames["upper"].$("gout").style.display  = "inline";								// logout button - 7/3/11
		parent.frames["upper"].$('buttons').style.display = "inline";	
	try {
		}
	catch(e) {
		}	
<?php
}
?>
window.reportType = 1;
window.theTeam = 0;
window.theMember = 0;

function ck_frames() {
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();
		}
	}
	
function do_logout() {
	clearInterval(mu_interval);
	mu_interval = null;
	is_initialized = false;
	document.gout_form.submit();
		}		
		
try {
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename(__FILE__));?>";
	parent.frames["upper"].show_butts();
	}
catch(e) {
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
		$('print_but').style.display = 'inline-block';
		}
	}
	
function do_Post(the_table) {
	document.tables.tablename.value=the_table;
	document.tables.submit();
	}
	
function go_there (where) {
	document.go_Form.action = where;
	document.go_Form.submit();
	}
	
function set_report(id, title) {
	window.reportType = id;
	$('team_selector').style.display = "block";
	$('r_head').innerHTML = title;
	$('report_contents').innerHTML = "";	
	}
	
function do_print_report() {
	var url = "mdb_print_report.php?reportType=" + window.reportType + "&team=" + window.theTeam + "&member=" + window.theMember + "&reportTitle=" + $('r_head').innerHTML;
	var printWindow = window.open(url, 'printWindow', 'resizable=1, scrollbars, height=800, width=1000, left=100, top=100, screenX=100, screenY=100');
	printWindow.focus();
	}
	
function set_team(id, team) {
	if(id == -1) {
		$('r_head').innerHTML += ", All Teams";
		$('member_selector').style.display = "block";
		} else {
		window.theTeam = id;
		$('r_head').innerHTML += ", for Team " + team;
		get_report(id, window.reportType, window.theTeam);
		}
	}

function run_report(id, name) {
	if(id == -1) {
		$('r_head').innerHTML += ", " + name;
		get_report(0, window.reportType, window.theTeam);		
		} else {
		$('r_head').innerHTML += ", Member " + name;
		window.theMember = id;
		get_report(id, window.reportType, window.theTeam);
		}
	}
</script>
 
</head>
<BODY onLoad = "ck_frames;">
<A NAME='top'></A>
<DIV ID = "to_bottom" style='position:fixed; top: 2px; left:5 0px; height: 12px; width: 10px; z-index: 99;' onclick = "location.href = '#bottom';"><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
<DIV ID='outer' style='position: absolute; left: 1%; width: 100%;'>
	<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
		<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>MDB Reports</SPAN>
	</DIV>
	<DIV id='left_col' class='shadow' style='position: relative; left: 5%; top: 10%; width: 85%; min-height: 200px; font-size: 14px; border: 2px outset #FFFFFF; padding: 30px; align: center;'>
		<SPAN id='reset_but' class='plain text' style='width: 100px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="go_there('mdb_reports.php', this.id);"><?php print get_text('Reset');?> <IMG style='vertical-align: middle; float: right;' src="./images/reset.png"/></SPAN>
		<SPAN class='plain text' id='print_but' style='width: 100px; display: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='do_print_report();'><?php print get_text('Print');?> <IMG style='vertical-align: middle; float: right;' src="./images/print_small.png"/></SPAN><BR />
		<SPAN class='plain text' id='back_but' style='width: 100px; float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='document.can_Form.submit();'><?php print get_text('To MDB');?> <IMG style='vertical-align: middle; float: right;' src="./images/back_small.png"/></SPAN><BR />
		<DIV id='report_selector'>
			<SELECT NAME='frm_report' style='float: left;' onChange='set_report(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text);'>
				<OPTION STYLE='font-size: 100%;' VALUE=0 SELECTED>Select a Report Type</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=1>Training Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=2>Equipment Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=3>Vehicle Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=4>Clothing Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=5>Full Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=6>Training Due Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=7>Events Report</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=8>Contact List</OPTION>
			</SELECT>
		</DIV>
		<DIV id='team_selector' style='float: left; display: none;'>
			<SELECT NAME='frm_team' onChange='set_team(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text);'>
				<OPTION STYLE='font-size: 100%;' VALUE=0 SELECTED>Select a team</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=-1>All Teams</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]team`";
				$result = mysql_query($query);	
				while ($row = mysql_fetch_assoc($result)) {
					print "<OPTION STYLE='font-size: 100%;' VALUE=" . $row['id'] . ">" . $row['name'] . "</OPTION>";
					}
?>
			</SELECT>
		</DIV>
		<DIV id='member_selector' style='float: left; display: none;'>
			<SELECT NAME='frm_member' onChange='run_report(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text);'>
				<OPTION STYLE='font-size: 100%;' VALUE=0 SELECTED>Select a person</OPTION>
				<OPTION STYLE='font-size: 100%;' VALUE=-1>All Members</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member`";
				$result = mysql_query($query);	
				while ($row = mysql_fetch_assoc($result)) {
					print "<OPTION STYLE='font-size: 100%;' VALUE=" . $row['id'] . ">" . $row['field2'] . " " . $row['field1'] . "</OPTION>";
					}
?>
			</SELECT>
		</DIV>
		<BR />
		<BR />
		<DIV id='reportHeader' class='tablehead text_biggest text_center' style='height: 50px; padding-top: 5px; padding-bottom: 5px;'>
			<IMG id='head_img' style='display: block; vertical-align: middle; margin-left: auto; float: left;' src="<?php print get_variable('report_graphic');?>"/>
			<SPAN id='r_head'>Report Type</SPAN>
			<BR />
			<SPAN id='r_contact' CLASS='tablehead text text_right' style='display: block; vertical-align: middle; margin-left: auto; float: right;'>Contact: <?php print get_variable('report_contact');?></SPAN>
		</DIV>
		<DIV id='spacer' class='spacer' style='width: 100%;'>&nbsp;</DIV>
		<DIV id='spacer' class='spacer' style='width: 100%;'>&nbsp;</DIV>
		<DIV id='spacer' class='spacer' style='width: 100%;'>&nbsp;</DIV>
		<DIV class='info' id='report_contents' style='text-align: left; display: none;'></DIV>
		<SPAN id='r_footer' CLASS='tablehead text text_center' style='wdth: 100%; display: block; vertical-align: middle; margin-left: auto;'><?php print get_variable('report_footer');?></SPAN>
	</DIV>
</DIV>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:70px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>

<FORM NAME='view_form' METHOD='get' ACTION='member.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='person'>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='edit' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
<FORM NAME='tables' METHOD = 'post' ACTION='tables.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='r'>
<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
</FORM>
<FORM NAME='can_Form' METHOD="post" ACTION = "member.php"></FORM>
<FORM NAME='go_Form' METHOD="post" ACTION = ""></FORM>
</body>
<SCRIPT>
var viewportwidth, viewportheight;
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
$('reportHeader').style.height = $('head_img').clientHeight + "px";
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
