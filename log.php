<?php

//	CONSTANT settings specific to this script
// end file-specific constants

/*
1/18/09 initial version
2/30/09 delete functions added
3/12/10 session started
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
4/19/11 obtain log codes via a 'require'
4/5/11 get_new_colors() added
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}
	
function get_status_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['status_val'];
		} else {
		return " ";
		}
	}

//dump($_POST);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets Log Processing</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="Tickets Log Entry"">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" type="application/x-javascript"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth;
var viewportheight;
var lcell1 = 0;
var lcell2 = 0;
var lcell3 = 0;
var lcell4 = 0;
var lcell5 = 0;

function validate_del() {
	if (document.del_form.frm_days_val.value==0) { 
		alert("check days value");
		return false;
		}
	else {
		return true;
		}
	}			// end function

function get_new_colors() {								// 4/5/11
	window.location.href = '<?php print basename(__FILE__);?>';
	}

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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	set_tablewidths();
	}

function set_tablewidths() {
	var theTable = document.getElementById('logtable');
	if(theTable) {
		var headerRow = theTable.rows[0];
		var tableRow = theTable.rows[1];
		if(tableRow) {
			if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 2 + "px";}
			if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 2 + "px";}
			if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 2 + "px";}
			if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 2 + "px";}
			if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 2 + "px";}
			if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 2 + "px";}
			if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 2 + "px";}
			} else {
			var cellwidthBase = window.listwidth / 35;
			cell1 = cellwidthBase * 5;
			cell2 = cellwidthBase * 5;
			cell3 = cellwidthBase * 5;
			cell4 = cellwidthBase * 5;
			cell5 = cellwidthBase * 5;
			cell6 = cellwidthBase * 5;
			cell7 = cellwidthBase * 5;
			headerRow.cells[0].style.width = lcell1 + "px";
			headerRow.cells[1].style.width = lcell2 + "px";
			headerRow.cells[2].style.width = lcell3 + "px";
			headerRow.cells[3].style.width = lcell4 + "px";						
			headerRow.cells[4].style.width = lcell5 + "px";
			headerRow.cells[5].style.width = lcell4 + "px";						
			headerRow.cells[6].style.width = lcell5 + "px";				
			}
		}
	}

</SCRIPT>
</HEAD>
<BODY>
<?php
if (empty($_POST)) {
	if (is_guest()) {
?>
		<CENTER><BR /><BR /><BR /><BR /><BR /><H3>Guests not allowed Log access. </CENTER><BR /><BR />

		<SPAN ID='can_button' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
<?php
		exit();
		}
?>
	<DIV ID='outer'>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='heading text_biggest' style='float: left;'>Station Log</SPAN>
			<SPAN ID='can_button' CLASS='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
			<SPAN ID='sub_button' CLASS='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.log_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			<SPAN ID='reset_button' CLASS='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.log_form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
			<SPAN ID='review_button' CLASS='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.log_form.func.value='view';document.log_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Review");?></SPAN><IMG STYLE='float: right;' SRC='./images/list_small.png' BORDER=0></SPAN>
<?php 
			if (is_super()) {
?>
				<SPAN ID='del_button' CLASS='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.log_form.func.value='del';document.log_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Deletion");?></SPAN><IMG STYLE='float: right;' SRC='./images/delete.png' BORDER=0></SPAN>
<?php 	
				} 
?>
		</DIV>
		<FORM NAME="log_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>">
		<TABLE STYLE='margin-left: 50px; position: relative; top: 80px;'>
			<TR CLASS = 'odd'>
				<TD CLASS='td_label text'>Unit</td>
				<TD CLASS='td_data text'>
					<SELECT CLASS='text' NAME="frm_responder">
						<OPTION CLASS='text' VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle` ASC";           // 12/18/10
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_responder = stripslashes_deep(mysql_fetch_assoc($result))) {
								print "\t<OPTION CLASS='text' VALUE='{$row_responder['id']}'>{$row_responder['handle']} ({$row_responder['name']}) </OPTION>\n";
								}
?>
					</SELECT>
				</TD>
			</TR>
			<TR CLASS = 'odd'>
				<TD CLASS='td_label text'>Log entry:</TD>
				<TD CLASS='td_data text'>
					<TEXTAREA NAME="frm_comment" COLS="45" ROWS="5" WRAP="virtual"></TEXTAREA>
				</TD>
			</TR>
			</TR>
			<TR>
				<TD COLSPAN=99>&nbsp;</TD>
			</TR>
		</TABLE>
		<INPUT TYPE='hidden' NAME='func' VALUE='add'>
		</FORM>
	</DIV>
<?php 
	} else {										// not empty
	function my_show_log () {				// returns  string
		global $evenodd ;					// class names for alternating table row colors
		require_once('./incs/log_codes.inc.php');				// returns $types array - 4/19/11
		
		$query = "
			SELECT *,  `u`.`user` AS `thename`,
			`$GLOBALS[mysql_prefix]log`.`info` AS `theinfo`,
			`r`.`name` AS `resp_name`,
			`t`.`scope` AS `tick_scope`			
			FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON `$GLOBALS[mysql_prefix]log`.`who` = `u`.`id`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON `$GLOBALS[mysql_prefix]log`.`responder_id` = `r`.`id`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON `$GLOBALS[mysql_prefix]log`.`ticket_id` = `t`.`id`
			ORDER BY `$GLOBALS[mysql_prefix]log`.`when` DESC LIMIT 5000;";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$i = 1;
?>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='heading text_biggest' style='float: left;'>Station Log</SPAN>
			<SPAN id='entry_button' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.dummy_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Log Entry");?></SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN>
			<SPAN id='fin_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
		</DIV>
		<A NAME='page_top'></A>
		<DIV class="scrollableContainer" style='border: 1px outset #707070; position: relative; top: 80px; left: 20px; width: 95%;'>
			<DIV class="scrollingArea">				
		
<?php
		$print = "<TABLE id='logtable' class='fixedheadscrolling scrollable' STYLE='width: 100%;'>";
		$do_hdr = TRUE; 
		$day_part="";
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			if ($do_hdr) {
				$print .= "<thead><TR style='width: 100%;'>";
				$print .= "<TH class='plain_listheader text text_left'>When</TH>";
				$print .= "<TH class='plain_listheader text text_left'>Code</TH>";
				$print .= "<TH class='plain_listheader text text_left'>By</TH>";
				$print .= "<TH class='plain_listheader text text_left'>Ticket</TH>";
				$print .= "<TH class='plain_listheader text text_left'>Unit</TH>";
				$print .= "<TH class='plain_listheader text text_left'>Info</TH>";
				$print .= "<TH class='plain_listheader text text_left'>From</TH>";
				$print .= "</TR>";
				$print .= "</thead>";
				$print .= "<tbody>";
				$do_hdr = FALSE;
				}
			switch ($row['code']):
				case $GLOBALS['LOG_SIGN_IN']:
				case $GLOBALS['LOG_SIGN_OUT']:
				case $GLOBALS['LOG_COMMENT']:
				case $GLOBALS['LOG_INCIDENT_OPEN']:
				case $GLOBALS['LOG_INCIDENT_CLOSE']:
				case $GLOBALS['LOG_INCIDENT_CHANGE']:
				case $GLOBALS['LOG_UNIT_CHANGE']:
				case $GLOBALS['LOG_UNIT_COMMENT']:
					$i++;
					$print .= "<TR CLASS='{$evenodd[$i%2]}' style='width: 100%;'>";
					$temp = preg_split('/ /',  $row['when']);				// date and time
					if ($temp[0]==$day_part) {
						$the_date = $temp[1];
						} else {
						$the_date = "<U>{$temp[0]}</U> {$temp[1]}";
						$day_part = $temp[0];
						}					
					$print .= 
						"<TD CLASS='plain_list text text_left'>&nbsp;". $the_date . "&nbsp;</TD>".
						"<TD CLASS='plain_list text text_left'>". $types[$row['code']] . "</TD>".
						"<TD CLASS='plain_list text text_left'>". $row['thename'] . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($row['tick_scope'], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($row['resp_name'], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($row['theinfo'], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>&nbsp;". $row['from'] . "</TD>".
						"</TR>\n";
					    break;
						
				case $GLOBALS['LOG_UNIT_STATUS']:
					$statusval = get_status_name($row['theinfo']);
					$i++;
					$print .= "<TR CLASS='{$evenodd[$i%2]}' style='width: 100%;'>";
					$temp = preg_split('/ /',  $row['when']);				// date and time
					if ($temp[0]==$day_part) {
						$the_date = $temp[1];
						} else {
						$the_date = "<U>{$temp[0]}</U> {$temp[1]}";
						$day_part = $temp[0];
						}					
					$print .= 
						"<TD CLASS='plain_list text text_left'>&nbsp;". $the_date . "&nbsp;</TD>".
						"<TD CLASS='plain_list text text_left'>". $types[$row['code']] . "</TD>".
						"<TD CLASS='plain_list text text_left'>". $row['thename'] . "</TD>".
						"<TD CLASS='plain_list text text_left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($row['resp_name'], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($statusval, 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>&nbsp;". $row['from'] . "</TD>".
						"</TR>\n";
					    break;
				case $GLOBALS['LOG_CALL_DISP']:
				case $GLOBALS['LOG_CALL_RESP']:
				case $GLOBALS['LOG_CALL_ONSCN']:
				case $GLOBALS['LOG_CALL_CLR']:
					$dispcodes = array();
					$dispcodes[30] = "Dispatch";
					$dispcodes[31] = "Responding";
					$dispcodes[32] = "On Scene";
					$dispcodes[33] = "Clear";
					$i++;
					$print .= "<TR CLASS='{$evenodd[$i%2]}' style='width: 100%;'>";
					$temp = preg_split('/ /',  $row['when']);				// date and time
					if ($temp[0]==$day_part) {
						$the_date = $temp[1];
						} else {
						$the_date = "<U>{$temp[0]}</U> {$temp[1]}";
						$day_part = $temp[0];
						}					
					$print .= 
						"<TD CLASS='plain_list text text_left'>&nbsp;". $the_date . "&nbsp;</TD>".
						"<TD CLASS='plain_list text text_left'>". $types[$row['code']] . "</TD>".
						"<TD CLASS='plain_list text text_left'>". $row['thename'] . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($row['tick_scope'], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($row['resp_name'], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>" . str_pad($dispcodes[$row['code']], 20, " ", STR_PAD_RIGHT) . "</TD>".
						"<TD CLASS='plain_list text text_left'>&nbsp;". $row['from'] . "</TD>".
						"</TR>\n";
					    break;
		
					endswitch;
			
			}
		$print .= "<TR><TD CLASS='plain_list text' COLSPAN=99 ALIGN='center'><BR /><B>End of Station Log Report</B><BR /><BR /></TD></TR>\n";
		$print .= "</tbody></TABLE></DIV></DIV><BR /><BR />";
		return $print;
		}		// end function my_show_log ()

?>
	<DIV ID='outer'>
<?php
		switch ($_POST['func']) {
			case "add":
				do_log($GLOBALS['LOG_COMMENT'], $ticket_id=0, $_POST['frm_responder'], strip_tags(trim($_POST['frm_comment'])));
				print "<script>window.close();</script>";			
				break;
			case "view":
				print my_show_log ();
				print "<SCRIPT>set_tablewidths();</SCRIPT>";
				print "<BR CLEAR='left'><BR>";
				break;
			case "del":		// 2/30/09
?>
			<CENTER>
			<FORM NAME="del_form" METHOD="post" ACTION = "<?php print basename(__FILE__); ?>">
			<INPUT TYPE="hidden" NAME="func" VALUE="del_db" />

			Delete log entries older than:
				one day&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "1" onClick = "document.del_form.frm_days_val.value='this.value';" />&nbsp;&nbsp;&nbsp;&nbsp;
				one week&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "7"  onClick = "document.del_form.frm_days_val.value='this.value';" />&nbsp;&nbsp;&nbsp;&nbsp;
				two weeks&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "14" onClick = "document.del_form.frm_days_val.value='this.value';"  />&nbsp;&nbsp;&nbsp;&nbsp;
				one month&raquo;<INPUT TYPE="radio" NAME="frm_del" VALUE = "30" onClick = "document.del_form.frm_days_val.value='this.value';"  /><BR /><BR /><BR />
				<INPUT TYPE='button' VALUE='OK - do it' onClick = "if ((validate_del()) && (confirm('Confirm deletion - CANNOT BE UNDONE!'))) {document.del_form.submit();}" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Cancel' onClick = "document.can_form.submit();" />
				<INPUT TYPE='hidden' NAME='frm_days_val' VALUE=0>
				</FORM>

			<FORM NAME="can_form" METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>
<?php
			break;
			case "del_db": 		// 2/30/09
				$the_date = mysql_format_date(time() - (get_variable('delta_mins')*60));
				$query = "DELETE from `$GLOBALS[mysql_prefix]log` WHERE `when` < ('{$the_date}' - INTERVAL {$_POST['frm_del']} DAY)";
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	// 
				print "<BR /> <BR /> " . mysql_affected_rows() . " Log entries deleted<BR /> <BR /> <BR /> ";
			break;
	
			default:
				echo "ERROR - ERROR";		
			}
?>
	</DIV>
<?php
		} 
?>
<FORM NAME="dummy_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>"></FORM>
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
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
</SCRIPT>
</BODY>
</HTML>


