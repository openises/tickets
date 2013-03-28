<?php
/*

*/
error_reporting(E_ALL);

@session_start();
require_once('./incs/functions.inc.php');
//include('./incs/html2text.php');	
$the_tickets = array();
$columns_arr = explode(',', get_msg_variable('columns'));
$the_level = $_SESSION['level'];
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_tickets[] = $row['id'];
	}

function read_directory($directory) {
	$the_ret = array();
	$dirhandler = opendir($directory);
	$i=0;
	while ($file = readdir($dirhandler)) {
		if ($file != '.' && $file != '..') {
			$i++;
			$the_ret[$i]=$file;                
		}   
	}
    closedir($dirhandler);
	return $the_ret;
	}

$files = array();
$files = read_directory(getcwd().'/message_archives/'); 

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
<STYLE type="text/css">
.signal_w { margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#FFFFFF; border-width: 2px; border-STYLE: inset; border-color: #3366FF;
			  padding: 1px 0.5em;text-decoration: none;float: left;color: white;background-color: #3366FF;font-weight: bolder;}
</STYLE>
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
var thelevel = '<?php print $the_level;?>';
var current_butt_id = "inbox";
var the_list = "";
var archive;
var folder = "inbox";
var the_sentstring = "";
var thebutton;	

function open_tick_window (id) {				// 5/2/10
	var url = "single.php?ticket_id="+ id;
	var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	tickWindow.focus();
	}

function get_mainmessages() {
	get_main_messagelist('','',sortby, 'DESC','', 'msg_win');
	}

function get_sentmessages() {
	get_sent_messagelist('','',sortby, 'DESC','', 'msg_win');
	}
	
function light_butt(btn_id) {				// 8/24/10 -     
	CngClass(btn_id, 'signal_w')			// highlight this button
	if(!(current_butt_id == btn_id)) {
		do_off_signal (current_butt_id);	// clear any prior one if different
		}
	current_butt_id = btn_id;				// 
	}				// end function light_butt()
	
function do_off_signal (the_id) {
	CngClass(the_id, 'plain')
	return true;
	}
	
function do_hover (the_id) {
	if (the_id == current_butt_id) {return true;}				// 8/21/10
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {				// 8/21/10
	if (the_id == current_butt_id) {return true;}
	CngClass(the_id, 'plain');
	return true;
	}
		
function get_archive(thearchive, button) {
	if($('all_read_but')) { $('all_read_but').style.display = "none"; }
	if($('all_unread_but')) { $('all_unread_but').style.display = "none"; }
	if($('empty_waste')) { $('empty_waste').style.display = "none"; }	
	if($('del')) { $('del').innerHTML = "&nbsp;&nbsp;";	}
	folder = "archive";
	clear_filter(folder);	
	thebutton = button;
	light_butt(button);	
	the_list="archive";
	archive = thearchive;
	get_arch_messagelist('','',sortby, 'DESC','', 'msg_win', thearchive);
	}
	
function get_wastebin() {
	if($('all_read_but')) { $('all_read_but').style.display = "none"; }
	if($('all_unread_but')) { $('all_unread_but').style.display = "none"; }
	if($('empty_waste')) { $('empty_waste').style.display = "inline-block"; }
	if($('del')) { $('del').innerHTML = "Res"; }
	folder = "wastebasket";	
	clear_filter(folder);
	light_butt('deleted');
	archive = "";	
	get_wastelist('','',sortby, 'DESC','');
	}
	
function get_inbox() {
	if($('all_read_but')) { $('all_read_but').style.display = "inline-block"; }
	if($('all_unread_but')) { $('all_unread_but').style.display = "inline-block"; }
	if($('empty_waste')) { $('empty_waste').style.display = "none";	}
	if($('del')) { $('del').innerHTML = "Del"; }	
	folder = "inbox";	
	clear_filter(folder);		
	light_butt('inbox');
	archive = "";	
	get_mainmessages();
	}

function get_sent() {
	if($('all_read_but')) { $('all_read_but').style.display = "inline-block"; }
	if($('all_unread_but')) { $('all_unread_but').style.display = "inline-block"; }
	if($('empty_waste')) { $('empty_waste').style.display = "none"; }
	if($('del')) { $('del').innerHTML = "Del";	}
	folder = "sent";	
	clear_filter(folder);		
	light_butt('sent');
	archive = "";	
	get_sentmessages();
	}	
	
thelevel = "<?php print can_delete_msg();?>";
</SCRIPT>
</HEAD>

<BODY onLoad="get_inbox();light_butt('inbox');">
<DIV style='background-color: #CECECE; height: 100%;'>
	<DIV id='folderlist' style='position: absolute; left: 0px; top: 0px; width: 18%; height: 100%;'>
		<SPAN id='folders_header' class='heading' style='margin-left: 2%; width: 96%; float: none; display: inline-block; font-size: 18px; border: 4px outset #FFFFFF;'>MESSAGE FOLDERS</SPAN><BR /><BR />	
		<SPAN id='inbox_header' class='heading' style='margin-left: 2%; width: 97%; float: none; display: inline-block;'>Current Messages</SPAN><BR /><BR />	
		<SPAN id='inbox' class='plain' style='margin-left: 5%; width: 80%; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_inbox();'>Inbox</SPAN><BR /><BR />
		<SPAN id='sent' class='plain' style='margin-left: 5%; width: 80%; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_sent();'>Sent Messages</SPAN><BR /><BR />
		<SPAN id='deleted' class='plain' style='margin-left: 5%; width: 80%; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_wastebin();'>Deleted Items</SPAN><BR /><BR />
		<SPAN id='archive_header' class='heading' style='margin-left: 2%; width: 97%; float: none; display: inline-block;'>Archive</SPAN><BR /><BR />
		<DIV id='archivelist' style='position: relative; left: 0px; top: 0px; width: 95%; height: 75%; overflow-y: scroll;'>
<?php
			foreach($files AS $val) {
				$temp = explode(".", $val);
				$temp2 = $temp[0];
				$temp3 = explode("_", $temp2);
				$start_y = substr($temp3[2],0,4);
				$start_m = substr($temp3[2],4,2);
				$start_d = substr($temp3[2],6,2);
				$end_y = substr($temp3[3],0,4);
				$end_m = substr($temp3[3],4,2);
				$end_d = substr($temp3[3],6,2);	
				$start = $start_d . "-" . $start_m . "-" . $start_y;
				$end = $end_d . "-" . $end_m . "-" . $end_y;
				$filename = $start . " to " . $end;
?>
				<SPAN id='<?php print $filename;?>' class='plain' style='margin-left: 5%; width: 80%; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="get_archive('<?php print $val;?>',this.id);"><?php print $filename;?></SPAN><BR /><BR />	
<?php
				}
?>
		</DIV>
	</DIV>
	<DIV id='view_messages' style='position: absolute; right: 0px; top: 0px; width: 82%; height: 100%; border: 4px outset #FFFFFF;'>
		<DIV id='header1' style='position: relative; width: 100%;'>
			<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'><BR />
				<SPAN id='close_but' class='plain' style='float: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='window.close();'>Close</SPAN>
<?php
				if(is_super()) {
?>
				<SPAN id='all_read_but' class='plain' style='float: none; display: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='read_status("read", 0, "messages");'>Mark All Read</SPAN>	
				<SPAN id='all_unread_but' class='plain' style='float: none; display: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='read_status("unread", 0, "messages");'>Mark All Unread</SPAN>	
				<SPAN id='del_all' class='plain' style='float: none; display: inline-block;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='del_all_messages();'>Delete All Messages</SPAN>	
				<SPAN id='empty_waste' class='plain' style='float: none; display: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='empty_waste()'>Empty Wastebin</SPAN>	
<?php
}
?>
			
			</DIV>
			<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'>
				<SPAN style='vertical-align: middle; text-align: center; font-size: 22px; color: #FFFFFF;'>Messages</SPAN>
				<SPAN style='font-size: 10px;'>Click Column Heading to sort</SPAN><BR />
			</DIV>
			<DIV style='background-color: #707070; color: #FFFFFF; position: relative; text-align: center;'>
				<FORM NAME='the_filter'>			
					<SPAN style='vertical-align: middle; text-align: center;'><B>FILTER: &nbsp;&nbsp;</B><INPUT TYPE='text' NAME='frm_filter' size='60' MAXLENGTH='128' VALUE=''>
						<SPAN id = 'filter_box' class='plain' style='float: none; vertical-align: middle;' onMouseover = 'do_hover(this);' onMouseout='do_plain(this);' onClick='do_filter(folder);'>&nbsp;&nbsp;&#9654;&nbsp;&nbsp;GO</SPAN>
						<SPAN id = 'the_clear' class='plain' style='float: none; display: none; vertical-align: middle;' onMouseover = 'do_hover(this);' onMouseout='do_plain(this);' onClick='clear_filter(folder);'>&nbsp;&nbsp;X&nbsp;&nbsp;Clear</SPAN>
					</SPAN>
				</FORM><BR />
			</DIV>
			<TABLE cellspacing='0' cellpadding='0' style='width: 99%; background-color: #CECECE; position: relative;'>
				<TR id='therow' style='padding-top: 3px; padding-bottom: 3px; background-color: #CECECE; color: #FFFFFF; width: 100%;'>
<?php
				$print = "";
				$print .= (in_array('1', $columns_arr)) ? "<TD id='ticket' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`ticket_id`',filter)\">Tkt</TD>" : "";					
				$print .= (in_array('2', $columns_arr)) ? "<TD id='type' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`msg_type`',filter)\">Typ</TD>" : "";				
				$print .= (in_array('3', $columns_arr)) ? "<TD id='fromname' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`fromname`',filter)\">From</TD>" : "";				
				$print .= (in_array('4', $columns_arr)) ? "<TD id='recipients' class='cols_h' NOWRAP style='width: 5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`recipients`',filter)\">To</TD>" : "";
				$print .= (in_array('5', $columns_arr)) ? "<TD id='subject' class='cols_h' NOWRAP style='width: 15.5%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`subject`',filter)\">Subject</TD>" : "";					
				$print .= (in_array('6', $columns_arr)) ? "<TD id='message' class='cols_h' NOWRAP style='width: 40%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`message`',filter)\">Message</TD>" : "";
				$print .= (in_array('7', $columns_arr)) ? "<TD id='date' class='cols_h' style='width: 8%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`date`',filter)\">Date</TD>" : "";
				$print .= (in_array('8', $columns_arr)) ? "<TD id='owner' class='cols_h' NOWRAP style='width:7%;' onClick=\"sort_switcher('main', the_selected_ticket,'','`_by`',filter)\">Owner</TD>" : "";
				$print .= "<TD id='del' class='cols_h' NOWRAP style='width: 3%; color: red;'>DEL</TD>";
				print $print;
?>			
				</TR>
			</TABLE>
		</DIV>
		<DIV ID = 'message_list' style='position: relative; background-color: #CECECE; overflow-y: scroll; overflow-x: hidden; height: 75%; width: 100%;'></DIV>
	</DIV>
</DIV>
</BODY>
</HTML>