<?php 
/* Change log - auto_status.php
10/15/12	New File - accessed from Config to set auto status values for incoming SMS messages	
*/

error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');	

function get_broken_status_name($val) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $val;
	$result = mysql_query($query);	
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_name = $row['group'] . " - " . $row['status_val'];
		} else {
		$the_name = "";
		}
	return $the_name;
	}

function get_noreset_status($val) {
	$theRet = "n";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $val;
	$result = mysql_query($query);	
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		if($row['excl_from_reset'] == "y") {
			$theRet = "y";
			} else {
			$theRet = "n";
			}
		} else {
		$theRet = "n";
		}
	return $theRet;
	}

// get status control
$the_status_sel = "";
$the_status_sel .= "<SELECT name='frm_status'>";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group`, `status_val` ASC";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
	$i = $row['id'];
	$the_status_sel .= "<OPTION VALUE=" . $i . " STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'>" . $row['group'] . " - " . $row['status_val'] . "</OPTION>";
	}
$the_status_sel .= "</SELECT>";

// end of status control

if(!empty($_POST)) {
	$counter = 0;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id`";	
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$exclFromReset = get_noreset_status(intval($row['un_status_id']));
		if($exclFromReset == "n") {
			$debug .= $row['un_status_id'];
			$query2 = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($_POST['frm_status']) . " WHERE `id`= " . $row['id'];
			$result2 = mysql_query($query2);	
			if($result2) {
				$counter++;
				}
			}
		}
	
	$caption = $counter . " responder Status Values set to " . get_status_name($_POST['frm_status']);
	if($counter == 0) {
		$caption = "Could not set Responder Status Values to " . get_status_name($_POST['frm_status']);	
		}
	
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<HEAD>
		<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<META HTTP-EQUIV="Expires" CONTENT="0" />
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
		<TITLE>Tickets</TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
	</SCRIPT>
	</HEAD>
	<BODY onLoad='ck_frames();'>
	<DIV style='font-size: 14px; position: fixed; top: 150px; left: 100px;'>
	<?php print $caption;?><br /><br />
	<A style='font-size: 14px;' href="config.php">Return to Config</A>		
	</DIV>
	</BODY>
	</HTML>
<?php
	} else { 
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<HEAD>
		<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<META HTTP-EQUIV="Expires" CONTENT="0" />
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
		<TITLE>Tickets</TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
		<STYLE>
			.table_header	{ color: #FFFFFF; text-align: left; height: 20px; border: 1px solid #000000; background: #707070;}	
			.table_hdr_cell { color: #FFFFFF; width: 20%; font-weight: bold; font-size: 16px; border: 1px solid #000000;}
			.table_row		{ color: #000000; text-align: left; height: 15px; border: 1px solid #000000;}	
			.table_cell 	{ width: 20%; font-size: 14px; border: 1px solid #000000;}			
			.header			{ display: table-cell; color: #000000; width: 5%;}
			.page_heading	{ font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF;}	
			.page_heading_text { font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF; width: 50%; dispay: inline;}
			.button_bar 	{ font-size: 1.2em; text-align: center; display: inline; width: 30%; position: fixed; right:30%; top: 0px;}					
			.buttons 		{ border: 2px outset #FFFFFF; padding: 2px; background-color: #EFEFEF; font-weight: bold; display: inline; cursor: pointer;}	
			.flag 			{ border: 2px outset #707070; background: #CECECE; font-size: 20px; font-weight: bold; display: inline; position: fixed; right:30%; top: 5%;}				
		</STYLE>			
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
	
	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}	
	</SCRIPT>
	</HEAD>
	<BODY onLoad='ck_frames();'>

	<DIV id='outer' style='position: absolute; top: 5%; width: 100%; height: 75%; border: 1px solid #FFFFFF;'>
		<DIV class='heading' style='width: 100%; position: absolute; text-align: center;'>Reset Responders to a common Status</DIV>
		<DIV id='left_col' style='width: 45%; position: absolute; top: 60px; left: 2%; border: 3px outset #CECECE;'>
			<FORM NAME='frm_def_status' METHOD="post" ACTION="<?php print basename(__FILE__);?>">
			<TABLE style='width: 100%;'>
				<TR class='heading'>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TH>
				</TR>				
				<TR class='odd'>
					<TD class='td_label'>Select Status Value to set Responders to</TD>
					<TD class='td_data'><FONT COLOR='blue'><?php print $the_status_sel;?></TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TH>
				</TR>	
				<TR class='odd'>
					<TD ALIGN='center' COLSPAN=99><INPUT TYPE='SUBMIT' NAME='SUBMIT' VALUE='Submit'></TH>
				</TR>				
			</TABLE>
		</DIV>
		</FORM>			
	</DIV>


<?php	

	}
?>
</BODY>
</HTML>