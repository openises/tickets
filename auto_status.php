<?php 
/* Change log - auto_status.php
10/15/12	New File - accessed from Config to set auto status values for incoming SMS messages	
*/

error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');	

// Declare arrays for all resource ids
$status_ids = array();
$signals = array();
$current = array();
// end of array declaration

// get status ids
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status`;";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$i = $row['id'];
		$status_ids[$i][] = $row['id'];
		$status_ids[$i][] = $row['status_val'];
		$status_ids[$i][] = $row['group'];
		}

// end of status ids

// get signals
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes`;";
	$result = mysql_query($query);
	$z=0;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$signals[$z][] = $row['code'];
		$signals[$z][] = $row['text'];
		$z++;
		}

// end of Signals

// get current settings
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_status`;";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$current[$row['status_val']][0] = $row['id'];
		$current[$row['status_val']][1] = $row['text'];
		$current[$row['status_val']][2] = $row['status_val'];
		}

// end of Current Settings

if(!empty($_POST)) {
	$the_data = array();
	$i=0;
	foreach($_POST['the_id'] as $val) {
		$the_data[$i][] = $val;
		$the_data[$i][] = $_POST['text_val' . $i];
		$the_data[$i][] = $_POST['the_id'][$i];
		$i++;
		}
	foreach($the_data as $val) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_status` WHERE `status_val` = '" . $val[2] . "'";	
		$result = mysql_query($query);	
		if(mysql_num_rows($result) > 0) {	//	Entry exists for the status value			
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_status` WHERE `status_val` = '" . $val[2] . "' AND `text` = '" . $val[1] . "'";	
			$result = mysql_query($query);			
			if(mysql_num_rows($result) > 0) {	//	Entry exists for the status value and the submitted text - don't insert but check if that entry has the same ID	
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_status` WHERE `id` = '" . $val[0] . "' AND `status_val` = '" . $val[2] . "' AND `text` = '" . $val[1] . "'";	
				$result = mysql_query($query);	
				if(mysql_num_rows($result) == 0) { // entry exists for status and text but different ID - do nothing
					} else { // entry exists for status and text and same id - do nothing
					}
				} else {	// current entry for the status value has different text - update
					$query = "UPDATE `$GLOBALS[mysql_prefix]auto_status` SET `text`= " . 		quote_smart(trim($val[1])) . ", `status_val`= " . quote_smart(trim($val[2])) . " WHERE `status_val`=" . $val[2];			
					$result = mysql_query($query);	
				}
			} else {	//	entry doesn't exist for the status value - insert a new one	
				if($val[1] != "Not Set") {	//	Ignore empty settings
					$query  = "INSERT INTO `$GLOBALS[mysql_prefix]auto_status` (`text` , `status_val`) VALUES ('" . $val[1] . "', '" . $val[2] . "')"; 
					$result = mysql_query($query);	
					}
			}
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
	Settings Saved<br /><br />
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
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}	
	</SCRIPT>
	</HEAD>
	<BODY onLoad='ck_frames();'>

	<DIV id='outer' style='position: absolute; top: 5%; width: 100%; height: 75%; border: 1px solid #FFFFFF;'>
		<DIV class='heading' style='width: 100%; position: absolute; text-align: center;'>AUTOMATIC STATUS UPDATES VIA INCOMING MESSAGING</DIV>
		<DIV id='left_col' style='width: 45%; position: absolute; top: 60px; left: 2%; border: 3px outset #CECECE;'>
			<FORM NAME='auto_stat_edit' METHOD="post" ACTION="<?php print basename(__FILE__);?>">
			<TABLE style='width: 100%;'>
				<TR class='heading'>
					<TH COLSPAN=99>SETTINGS</TH>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TH>
				</TR>				
<?php
				$class='even';
				$i=0;
				foreach($status_ids as $the_id) {
?>
				<TR class='<?php print $class;?>'>
					<TD class='td_label'><FONT COLOR='blue'>(GROUP - : <?php print $status_ids[$the_id[0]][2];?>)</FONT> <?php print $status_ids[$the_id[0]][1];?></TD>
					<TD class='td_data'>
<?php
					$the_text = (array_key_exists($the_id[0], $current)) ? $current[$the_id[0]][1] : "Not Set";
?>						
						<input type="text" name="text_val<?php print $i;?>" value="<?php print $the_text;?>" size="20" />&nbsp;&nbsp;
						<select name="s<?php print $i;?>" size="1" onchange="document.forms['auto_stat_edit'].text_val<?php print $i;?>.value = document.forms['auto_stat_edit'].s<?php print $i;?>.options[this.selectedIndex].value; document.forms['auto_stat_edit'].s<?php print $i;?>.value=''">
							<option value=" " selected="selected">Select or type in box</option>						
<?php
							foreach($signals AS $val) {
?>

								<option value="<?php print $val[0];?>"><?php print $val[0] . "->" . $val[1];?></option>
<?php
								}
?>
						</select>
					</TD>
					<INPUT TYPE='hidden' NAME='the_id[]' VALUE='<?php print $status_ids[$the_id[0]][0];?>'>
				</TR>
<?php
					$class = ($class == 'even') ? 'odd' : 'even';
					$i++;
					}
?>
			</TABLE>
		</DIV>
		<DIV id='right_col' style='width: 40%; height: 500px; position: absolute; top: 60px; right: 2%; border: 3px outset #DEDEDE; background-color: #F0F0F0;'>
			<DIV class='heading' style='width: 100%;'>HELP</DIV>
			<DIV style='width: 100%; word-break: normal;'>
			This page is to set the Auto Status text values and their associated status values.<BR /><BR />
			On the left side sre the status values currently configured on the Tickets CAD system. These can be changed through Config / Units Status. Next to these are a text box 
			where you can type in the text values you want to search for in incoming SMS messages. The Responder sending the message will put this text between the start and end tags
			configured in Messaging settings (default *). Next to the text box is a select control where you can select from pre-configured signals that are configured in Config / Signals.<BR /><BR />
			To congigure the auto status values, for each status value that you want to be automatically set by incoming SMS message, type the text value that you want to trigger this or 
			select from the pre-configured signals and then click Submit.<BR />
			</DIV>
		</DIV>
		<DIV style='width: 100%; text-align: center; position: absolute; bottom: 10%;'>
			<INPUT TYPE='SUBMIT' NAME='SUBMIT' VALUE='Submit'>
		</DIV>
		</FORM>			
	</DIV>


<?php	

	}
?>
</BODY>
</HTML>