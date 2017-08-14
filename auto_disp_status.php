<?php 
/* Change log - auto_status.php
9/10/13 - Config file to configure automatic status updates when dispatch status changes
*/

error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');	

// Declare arrays for all resource ids
$status_ids = array();
$disp_status = array();
$current = array();
// end of array declaration

// get status ids
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status`;";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$i = $row['id'];
		$status_ids[$i][0] = $row['id'];
		$status_ids[$i][1] = $row['status_val'];
		$status_ids[$i][2] = $row['group'];
		}
		
// end of status ids

// Dispatch Status Values
	$disp_status[1] = "Dispatched";
	$disp_status[2] = "Responding";
	$disp_status[3] = "On Scene";
	$disp_status[4] = "Facility en-route";
	$disp_status[5] = "Facility arrived";
	$disp_status[6] = "Clear";
	
// end of dispatch status values

// get current settings
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_disp_status` ORDER BY `id` ASC;";
	$result = mysql_query($query);
	$z=1;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$current[$z][0] = $row['id'];
		$current[$z][1] = $row['name'];
		$current[$z][2] = intval($row['status_val']);
		$z++;
		}
// end of Current Settings

if(!empty($_POST)) {
	$the_data = array();
	$i=1;
	foreach($_POST['frm_status'] as $val) {
		$the_data[$i][] = $i;
		$the_data[$i][] = $val;
		$the_data[$i][] = $disp_status[$i];
		$i++;
		}
	foreach($the_data as $val) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_disp_status` WHERE `id` = '" . $val[0] . "'";	
		$result = mysql_query($query);	
		if(mysql_num_rows($result) > 0) {	//	Entry exists for the status value			
			$query = "UPDATE `$GLOBALS[mysql_prefix]auto_disp_status` SET `name` = '" . $val[2] . "', `status_val` = " . $val[1] . " WHERE `id`=" . $val[0];		
			$result = mysql_query($query);	
			} else {	//	entry doesn't exist for the status value - insert a new one	
			$query  = "INSERT INTO `$GLOBALS[mysql_prefix]auto_disp_status` (`id`, `name`, `status_val`) VALUES ('" . $val[0] . "', '" . $val[2] . "', '" . $val[1] . "')"; 
			$result = mysql_query($query);	
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
			.table_cell 	{ width: 20%; font-size: 14px; border: 1px solid #000000; word-wrap: break-word;}			
			.header			{ display: table-cell; color: #000000; width: 5%;}
			.page_heading	{ font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF;}	
			.page_heading_text { font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF; width: 50%; display: inline;}
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
		<DIV class='heading' style='width: 100%; position: absolute; text-align: center;'>AUTOMATIC STATUS UPDATES WITH DISPATCH STATUS CHANGES</DIV>
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
				$i=1;
				foreach($disp_status as $the_id) {
?>
					<TR class='<?php print $class;?>'>
						<TD class='td_label'><?php print $the_id;?></TD>
						<TD class='td_data'>
							<select name="frm_status[<?php print $i;?>]" size="1">
								<option value="0" selected="selected">Select Status Value</option>						
<?php
								foreach($status_ids AS $val) {
									$sel = ($val[0] == $current[$i][2]) ? " SELECTED" : "";
?>

									<option value="<?php print $val[0];?>"<?php print $sel;?>><?php print $val[2] . "->" . $val[1];?></option>
<?php
									}
?>
							</select>
						</TD>
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
			<DIV style='width: 100%; word-wrap: break-word;'>
			This page is to set the Auto Dispatch Status values and their associated status values.<BR /><BR />
			On the left side sre the Dispatch status values and on the right are select controls with the status values currently configured on the Tickets CAD system.
			These can be changed through Config / Units Status.<BR /><BR />
			To congigure the auto status values, for each Dispatch status value that you want a specific unit status to be set, select the unit status next to that dispatch status and then click submit.<BR />
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