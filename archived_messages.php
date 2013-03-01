<?php
/*

*/
error_reporting(E_ALL);

@session_start();
require_once('./incs/functions.inc.php');
//include('./incs/html2text.php');	
$the_tickets = array();
$columns_arr = explode(',', get_msg_variable('columns'));

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_tickets[] = $row['id'];
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Messages</TITLE>
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT>
var thelevel = "<?php print $_SESSION['level'];?>";
</SCRIPT>
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT>
var columns = "<?php print get_msg_variable('columns');?>";

var screen = 'msg_win';
var theScreen;
var the_ids = new Array();
var i=0;
var sortby = '`date`';
var sort = "DESC";
var filterby = '';
var groupby = '';
var thefilter = "";
var the_cal = "";
var filter = "";
var ticket_id = "";
var the_selected_ticket = "";
var the_ticket = "";
var the_columns = new Array(<?php print get_msg_variable('columns');?>);
var responder_id = "";
var thelevel;

function get_archive_msgs() {
	get_arch_messagelist('','',sortby, 'DESC','', 'msg_win');
	}
			
thelevel = "<?php print can_delete_msg();?>";
</SCRIPT>
</HEAD>

<BODY onLoad="get_archive_msgs();">
<?php

$the_control = "<SELECT NAME='frm_the_ticks' onChange='select_ticket(this.options[this.selectedIndex].value, filter);'>";
$the_control .= "<OPTION VALUE=0 SELECTED>All</OPTION>";
foreach($the_tickets AS $val) {
	$the_control .= "<OPTION VALUE=" . $val . ">Ticket " . $val . "</OPTION>";
	}
$the_control .= "</SELECT>";
?>
<DIV style='margin: 2%; background-color: #CECECE; height: 100%;'>
	<DIV style='width: 99%; height: 100%;'>
		<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'>
			<SPAN style='vertical-align: middle; text-align: center; font-size: 22px; color: #FFFFFF;'>Archived Messages</SPAN>
			<A HREF='config.php' id='back_but' class='plain' style='float: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);'>Back</A><BR />
			<SPAN style='font-size: 10px;'>Click Column Heading to sort</SPAN><BR />
		</DIV>
		<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'>
			<FORM NAME='the_filter'>			
				<SPAN style='vertical-align: middle; text-align: center;'><B>FILTER: &nbsp;&nbsp;</B><INPUT TYPE='text' NAME='frm_filter' size='60' MAXLENGTH='128' VALUE=''>
					<SPAN id = 'filter_box' class='plain' style='float: none; vertical-align: middle;' onMouseover = 'do_hover(this);' onMouseout='do_plain(this);' onClick='do_filter(the_ticket,"");'>&nbsp;&nbsp;&#9654;&nbsp;&nbsp;GO</SPAN>
					<SPAN id = 'the_clear' class='plain' style='float: none; display: none; vertical-align: middle;' onMouseover = 'do_hover(this);' onMouseout='do_plain(this);' onClick='clear_filter(the_ticket,"");'>&nbsp;&nbsp;X&nbsp;&nbsp;Clear</SPAN>
				</SPAN>
			</FORM>
		</DIV>
		<TABLE cellspacing='0' cellpadding='0' style='width: 99%; background-color: #CECECE; position: relative;'>
			<TR style='padding-top: 3px; padding-bottom: 3px; background-color: #CECECE; color: #FFFFFF; width: 100%;'>
<?php
			$print = "";
			$print .= (in_array('1', $columns_arr)) ? "<TD id='ticket' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`ticket_id`',filter)\">Tkt</TD>" : "";					
			$print .= (in_array('2', $columns_arr)) ? "<TD id='type' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`msg_type`',filter)\">Typ</TD>" : "";				
			$print .= (in_array('3', $columns_arr)) ? "<TD id='from' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`fromname`',filter)\">From</TD>" : "";				
			$print .= (in_array('4', $columns_arr)) ? "<TD id='recipients' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`recipients`',filter)\">To</TD>" : "";
			$print .= (in_array('5', $columns_arr)) ? "<TD id='subject' class='cols_h' NOWRAP style='width: 20%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`subject`',filter)\">Subject</TD>" : "";					
			$print .= (in_array('6', $columns_arr)) ? "<TD id='message' class='cols_h' NOWRAP style='width: 45%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`message`',filter)\">Message</TD>" : "";
			$print .= (in_array('7', $columns_arr)) ? "<TD id='date' class='cols_h' style='width: 7%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`date`',filter)\">Date</TD>" : "";
			$print .= (in_array('8', $columns_arr)) ? "<TD id='owner' class='cols_h' NOWRAP style='width:5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`_by`',filter)\">Owner</TD>" : "";
			print $print;
?>			
			</TR>
		</TABLE>
		<DIV ID = 'message_list' style='position: relative; background-color: #CECECE; overflow-y: scroll; overflow-x: hidden; height: 70%; border: 2px outset #FEFEFE; width: 100%;'></DIV>
	</DIV>
</DIV>
</BODY>
</HTML>