<?php
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
function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		parent.upper.do_day_night("<?php print $_SESSION['day_night'];?>")
		}
	}		// end function ck_frames()
</HEAD>
<BODY onLoad = "ck_frames();">
<?php
if(empty($_POST)) {
?>
<DIV id='outer' style='position: absolute; left: 5%; top: 5%; width: 90%; text-align: center;'>
	<DIV id='leftcol' style='position: relative: left: 2%; top: 5%; width: 40%; float: left; border: 3px outset #CECECE;'>
		<FORM NAME='archive_form' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
		<TABLE style='width: 100%;'>
			<TR class='heading'>
				<TH class='heading' COLSPAN=99 style='font-size: 20px;'>ARCHIVE MESSAGES</TH>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
			</TR>			
			<TR class='odd'>	
				<TD class='td_label'>Start Date</TD><TD class='td_data'><?php print generate_date_dropdown('start',0,FALSE);?></TD>
			</TR>
			<TR class='even'>	
				<TD class='td_label'>End Date</TD><TD class='td_data'><?php print generate_date_dropdown('end',0,FALSE);?></TD>
			</TR>
			<TR class='odd'>	
				<TD class='td_label' COLSPAN=99>DELETE MESSAGES<input type="checkbox" name="del_messages" value="yes"></TD>
			</TR>			
			<TR class='spacer'>
				<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
			</TR>
			<TR class='even'>
				<TD COLSPAN=99><INPUT TYPE='submit' name="submit" value="submit" type="submit"></TD>
			</TR>
		</TABLE>
		<INPUT NAME='table' TYPE='hidden' SIZE='24' VALUE='messages'>
		</FORM>
	</DIV>
	<DIV id='rightcol'  style='position: relative: left: 2%; width: 50%; float: right; border: 3px outset #CECECE;'>
		<FORM NAME='manage_archives' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
		<TABLE style='width: 100%;'>
			<TR class='heading'>
				<TH class='heading' COLSPAN=99 style='font-size: 20px;'>MANAGE ARCHIVES</TH>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
			</TR>			
			<TR class='heading'>
				<TD class='header'>DELETE</TD>
				<TD class='header'>FILENAME</TD>				
			</TR>	
<?php
				$class='odd';
				foreach($thefiles AS $val) {
					$thefile = "./message_archives/" . $val;
					
?>
					<TR class='<?php print $class;?>'>
						<TD class='td_label'><INPUT TYPE='checkbox' name='files[]' value=<?php print $val;?>></TD>
						<TD class='td_data'><A HREF='<?php print $thefile;?>'><?php print $val;?></TD>
					</TR>
<?php
					$class = ($class == 'even') ? 'odd': 'even';
					}
?>
			<TR class='spacer'>
				<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
			</TR>
			<TR class='even'>
				<TD COLSPAN=99><INPUT TYPE='submit' name="submit" value="submit" type="submit"></TD>
			</TR>
		</TABLE>
		</FORM>
	</DIV>
</DIV>
<?php
} else {
dump($_POST);
	if(isset($_POST['frm_year_start'])) {
		$start = $_POST['frm_year_start'] . "-" . $_POST['frm_month_start'] . "-" . $_POST['frm_day_start'] . " " . $_POST['frm_hour_start'] . ":" . $_POST['frm_minute_start'] . ":00";
		$end = $_POST['frm_year_end'] . "-" . $_POST['frm_month_end'] . "-" . $_POST['frm_day_end'] . " " . $_POST['frm_hour_end'] . ":" . $_POST['frm_minute_end'] . ":00";	
		$starttag = "$_POST[frm_year_start]$_POST[frm_month_start]$_POST[frm_day_start]";
		$endtag = "$_POST[frm_year_end]$_POST[frm_month_end]$_POST[frm_day_end]";	
		$filetag = $starttag . "_" . $endtag;
		$filename = "./message_archives/msg_archive_" . $filetag . ".csv";
		$table = $_POST['table'];
		$del = ((isset($_POST['del_messages'])) && ($_POST['del_messages'] == "yes")) ? TRUE : FALSE;
		exportMysqlToCsv($table, $filename, $start, $end, $del);
	} else {
		print "managing existing files<BR />";
	}
}
?> 
</BODY>
</HTML>
