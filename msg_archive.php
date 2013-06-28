<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once './incs/functions.inc.php';
require './incs/exportcsv.inc.php';

$thefiles = array();
if ($handle = opendir('./message_archives')) {
    while (false !== ($entry = readdir($handle))) {
		if(($entry != ".") && ($entry != "..")) {
			$thefiles[] =  $entry;
			}
		}
    closedir($handle);
	}
	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` ORDER by `date` ASC LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_num_rows($result) != 0) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$oldest_date = $row['date'];
	} else {
	$oldest_date = 0;
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` ORDER by `date` DESC LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_num_rows($result) != 0) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$newest_date = $row['date'];
	} else {
	$newest_date = 0;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Message Archive</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT>
function $() {									// 1/21/09
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
		
function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		parent.upper.do_day_night("<?php print $_SESSION['day_night'];?>")
		}
	}		// end function ck_frames()
	
function go_there (where, the_id) {		//
	document.go.action = where;
	document.go.submit();
	}				// end function go there ()	
	
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}

function submit_archive() {
	if(document.forms['archive_form'].del_messages.checked == true) {
		if (confirm("Are you sure you want to remove the messages after archiving them?")) { 
		document.forms['archive_form'].submit();
			}
		} else {
		document.forms['archive_form'].submit();
		}
	}	
</SCRIPT>
</HEAD>
<BODY onLoad = "ck_frames();">
<FORM NAME="go" action="#" TARGET = "main"></FORM>
<?php
if(empty($_POST)) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$num_messages = mysql_num_rows($result);
?>
	<DIV id='outer' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='banner' class='heading' style='font-size: 28px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>MESSAGE ARCHIVING</DIV><BR /><BR />
		<DIV id='leftcol' style='position: relative; left: 2%; top: 5%; width: 45%; float: left; border: 1px outset #000000;'>
<?php 
		if($num_messages != 0) {
?>
			<FORM NAME='archive_form' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
			<TABLE style='width: 100%;'>
				<TR class='heading'>
					<TH class='heading' COLSPAN=99 style='font-size: 18px;'>ARCHIVE MESSAGES (<?php print $num_messages;?> messages stored)</TH>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
				</TR>			
				<TR class='odd'>	
					<TD class='td_label' style='text-align: left;'>&nbsp;&nbsp;Start Date</TD><TD class='td_data'><?php print generate_dateonly_dropdown('start',strtotime($oldest_date),FALSE);?></TD>
				</TR>
				<TR class='even'>	
					<TD class='td_label' style='text-align: left;'>&nbsp;&nbsp;End Date</TD><TD class='td_data'><?php print generate_dateonly_dropdown('end',strtotime($newest_date),FALSE);?></TD>
				</TR>
				<TR class='odd'>	
					<TD class='td_label' COLSPAN=99>DELETE MESSAGES<input type="checkbox" name="del_messages" value="yes"></TD>
				</TR>			
				<TR class='spacer'>
					<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
				</TR>
				<TR class='even'>
					<TD COLSPAN=99></TD>
				</TR>
			</TABLE>
			<INPUT NAME='table' TYPE='hidden' SIZE='24' VALUE='messages'>
			</FORM><BR /><BR />
			<SPAN id='sub_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "submit_archive();">Submit</SPAN><BR /><BR />	
<?php
			} else {
?>
			<SPAN CLASS='header' id='no_msgs_flag'>There are no messages currently stored</SPAN>
<?php
			}
?>
		</DIV>
		<DIV id='rightcol'  style='position: relative; right: 2%; width: 35%; float: right; border: 1px outset #000000;'>
			<FORM NAME='manage_archives' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
			<TABLE style='width: 100%; padding-left: 10px;'>
				<TR class='heading'>
					<TH class='heading' COLSPAN=99 style='font-size: 18px;'>MANAGE ARCHIVES (<?php print count($thefiles);?> files)</TH>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
				</TR>			
				<TR class='heading'>
					<TD class='header' style='font-size: 12px; text-align: left;'>DELETE</TD>
					<TD class='header' style='font-size: 12px; text-align: left;'>FILENAME</TD>				
				</TR>	
<?php
					$class='odd';
					foreach($thefiles AS $val) {
						$thefile = "./message_archives/" . $val;
?>
						<TR class='<?php print $class;?>'>
							<TD class='td_label' style='font-size: 12px; text-align: left;'><INPUT TYPE='checkbox' name='files[]' value=<?php print $val;?>></TD>
							<TD class='td_data' style='font-size: 12px; text-align: left;'><A HREF='<?php print $thefile;?>'><?php print $val;?></TD>
						</TR>
<?php
						$class = ($class == 'even') ? 'odd': 'even';
						}
?>
				<TR class='spacer'>
					<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
				</TR>
			</TABLE>
			</FORM><BR /><BR />
			<SPAN id='sub2_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.forms['manage_archives'].submit();">Submit</SPAN><BR /><BR />		
		</DIV>
	</DIV>
<?php
} else {
	if(isset($_POST['frm_year_start'])) {
		$month_start = (($_POST['frm_month_start'] > 0) && ($_POST['frm_month_start'] == 9)) ? "0" . $_POST['frm_month_start'] : $_POST['frm_month_start'];
		$day_start = (($_POST['frm_day_start'] > 0) && ($_POST['frm_day_start'] == 9)) ? "0" . $_POST['frm_day_start'] : $_POST['frm_day_start'];
		$month_end = (($_POST['frm_month_end'] > 0) && ($_POST['frm_month_end'] == 9)) ? "0" . $_POST['frm_month_end'] : $_POST['frm_month_end'];
		$day_end = (($_POST['frm_day_end'] > 0) && ($_POST['frm_day_end'] == 9)) ? "0" . $_POST['frm_day_end'] : $_POST['frm_day_end'];
		$start = $_POST['frm_year_start'] . "-" . $_POST['frm_month_start'] . "-" . $_POST['frm_day_start'] . " 00:00:00";
		$end = $_POST['frm_year_end'] . "-" . $_POST['frm_month_end'] . "-" . $_POST['frm_day_end'] . " 23:59:00";	
		$starttag = "$_POST[frm_year_start]$month_start$day_start";
		$endtag = "$_POST[frm_year_end]$month_end$day_end";	
		$filetag = $starttag . "_" . $endtag;
		$filename = "./message_archives/msg_archive_" . $filetag . ".csv";
		$table = $_POST['table'];
		$del = ((isset($_POST['del_messages'])) && ($_POST['del_messages'] == "yes")) ? TRUE : FALSE;
		$the_return = exportMysqlToCsv($table, $filename, $start, $end, $del);
		if($the_return == '100') {
			$title = "Message Archiving Complete";
			} else {
			$title =  "Archive Already exists";
			}
		$print = "";
			
	} else {
		$dir = "./message_archives/";
		$print = "";
		foreach($_POST['files'] as $val) {
			$print .=  "Deleted " . $val . "<BR />";
			unlink($dir . $val);
			}
		$title = "Archive Deletion Complete";
	}
?>
	<BR /><BR />
	<DIV id='finished_buttons' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='leftcol' style='position: relative; left: 20%; top: 5%; width: 48%; float: left; border: 1px outset #000000;'><BR /><BR /><?php print $print;?><BR />
		<SPAN  class='heading' style='font-size: 24px;'><?php print $title;?></SPAN><BR /><BR /><BR />
		<CENTER>
		<SPAN ID='archive_back' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "go_there('msg_archive.php', this.id);">Back to Message Archiving</SPAN>
		<SPAN ID='config_back' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "go_there('config.php', this.id);">Back to Config</SPAN></CENTER>
		<BR /><BR />
		</DIV>
	</DIV>	
<?php
}
?> 
</BODY>
</HTML>
