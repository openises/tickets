<?php 

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$mode = (array_key_exists('mode', $_REQUEST)) ? $_REQUEST['mode'] : 0;
$the_messages = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_messages[] = $row['id'];
	}

$the_contacts = array();
$i = 1;
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_contacts[$i][0] = $row['name'];
	$the_contacts[$i][1] = $row['organization'];	
	$the_contacts[$i][2] = $row['phone'];
	$the_contacts[$i][3] = $row['mobile'];	
	$the_contacts[$i][4] = $row['email'];
	$i++;
	}
	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	if($row['contact_via'] != "") {
		$the_contacts[$i][0] = $row['name'];
		$the_contacts[$i][1] = "responder";	
		$the_contacts[$i][2] = $row['phone'];
		$the_contacts[$i][3] = $row['mobile'];	
		$the_contacts[$i][4] = $row['contact_via'];
		$i++;
		}
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	if($row['email'] != "") {
		$the_contacts[$i][0] = $row['name_f'] . " " . $row['name_l'];
		$the_contacts[$i][1] = "user";	
		$the_contacts[$i][2] = $row['phone_p'];
		$the_contacts[$i][3] = $row['phone_m'];	
		$the_contacts[$i][4] = $row['email'];
		$i++;
		}
	}

$the_users = array();	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_users[] = $row['id'];
	}	

$count_users = count($the_users);

$the_addressbook = "<SELECT NAME='frm_addressbook' onChange='pop_address(this.options[this.selectedIndex].value);'>";
$the_addressbook .= "<OPTION VALUE='0' SELECTED>Select Address from Contacts</OPTION>";
$z=1;
foreach($the_contacts as $val) {
	$the_addressbook .= "<OPTION VALUE=" . $the_contacts[$z][4] . ">" . $the_contacts[$z][0] . "  "  . $the_contacts[$z][4] . "</OPTION>";
	$z++;
	}
$the_addressbook .= "</SELECT>";

function the_ticket($theRow, $theWidth=500, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_label text text_center' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD CLASS='td_label text text_left'>" . get_text("Addr") . ":</TD><TD CLASS='td_data text text_left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD CLASS='td_label text text_left'>" . get_text("City") . ":</TD><TD CLASS='td_data text text_left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD CLASS='td_label text text_left'>" . get_text("Priority") . ":</TD> <TD CLASS='" . $severityclass . " text text_left'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='even'  VALIGN='top'><TD CLASS='td_label text text_left'>" . get_text("Synopsis") . ":</TD><TD CLASS='td_data text text_left'>" . replace_quotes(highlight($search, nl2br($theRow['tick_descr']))) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='odd' ><TD CLASS='td_label text text_left'>" . get_text("Protocol") . ":</TD> <TD CLASS='{$severityclass} text text_left'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD CLASS='td_label text text_left'>" . get_text("911 Contacted") . ":</TD>	<TD CLASS='td_data text text_left'>" . highlight($search, nl2br($theRow['nine_one_one'])) . "</TD></TR>\n";	//	6/26/10
	$print .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>" . get_text("Reported by") . ":</TD><TD CLASS='td_data text text_left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD CLASS='td_label text text_left'>" . get_text("Phone") . ":</TD><TD CLASS='td_data text text_left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$end_date = (is_null($theRow['problemend'])) ? $theRow['problemend']: date("Y-m-d H:i:00", (time() - (intval(get_variable('delta_mins'))*60)));	// 11/29/2012
	$elapsed =  my_date_diff($theRow['problemstart'], $end_date);
	$elapsed_str = get_elapsed_time ($theRow);			
	$print .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>" . get_text("Status") . ":</TD><TD CLASS='td_data text text_left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$print .= "<TR CLASS='even'><TD CLASS='td_label text text_left'>" . get_text("Written") . ":</TD><TD CLASS='td_data text text_left'>" . format_date_2(strtotime($theRow['date'])) . $by_str;
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date_2(strtotime($theRow['updated'])) . "</TD></TR>\n";
	$print .=  empty($theRow['booked_date']) ? "" : "<TR CLASS='odd'><TD CLASS='td_label text text_left'>Scheduled date:</TD><TD CLASS='td_data text text_left'>" . format_date_2(strtotime($theRow['booked_date'])) . "</TD></TR>\n";	// 10/6/09
	$print .= "<TR CLASS='even' ><TD CLASS='td_label text text_left' COLSPAN='2'>&nbsp;</TD></TR>\n";			// separator
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD CLASS='td_label text text_left'>{$incident} at Facility:</TD>		<TD CLASS='td_data text text_left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD CLASS='td_label text text_left'>Receiving Facility:</TD>		<TD CLASS='td_data text text_left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09
	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD CLASS='td_label text text_left'>{$disposition}:</TD>	<TD CLASS='td_data text text_left'>" . replace_quotes(highlight($search, nl2br($theRow['comments']))) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD CLASS='td_label text text_left'>" . get_text("Run Start") . ":</TD><TD CLASS='td_data text text_left'>" . format_date_2(strtotime($theRow['problemstart']));
	$end_str = (good_date_time($theRow['problemend']))? format_date_2(strtotime($theRow['problemend'])) : "";
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;{$end_str}&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD CLASS='td_label text text_left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>" . get_text("Position") . "</U>: </TD>
		<TD CLASS='td_data text text_left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08

	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow[0]);				// log
	$print .="</TD></TR>";
	$print .= "<TR STYLE = 'display:none;'><TD CLASS='td_data text text_left' colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
	$print .= "<TR><TD COLSPAN=99>";
	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09 - new_show_assigns($id_in)
	$print .= "</TD></TR><TR><TD COLSPAN=99>";
	$print .= show_actions($theRow[0], "date", FALSE, FALSE, 1);
	$print .= "</TD></TR>";	
	$print .= "</TABLE>\n";	
	return $print;
	}		// end function do ticket(

function get_respname($theid) {	//	Gets responder ID from SMS Gateway ID
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = '" . $theid . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_name = $row['name'];
		} else {
		$the_name="No Name";
		}
	return $the_name;
	}

function get_tickname($theid) {	//	Gets responder ID from SMS Gateway ID
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = '" . $theid . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_name = $row['scope'];
		} else {
		$the_name="No Name";
		}
	return $the_name;
	}	

?>
<!DOCTYPE html> 
<HTML>
<HEAD>
<TITLE>Message</TITLE>
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/jss.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT>
var sep = "";
var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;

window.onresize=function(){set_size()};

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
	}

function reply_button() {
	$("print_but").style.display="none";
	if($('next_but')) {$("next_but").style.display="none";}
	if($('prev_but')) {$("prev_but").style.display="none";}
	$("reply").style.display="block"; 
	$("view").style.display="none";
	$("forward").style.display="none";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";
	$("can_but").style.display="inline-block";		
	$("send_but").style.display="inline-block";	
	$("disp_but").style.display="none";	
	$("send_but").onclick=function() {send_button('reply_frm')};
	}
	
function forward_button() {
	$("print_but").style.display="none";
	if($('next_but')) {$("next_but").style.display="none";}
	if($('prev_but')) {$("prev_but").style.display="none";}
	$("reply").style.display="none"; 
	$("view").style.display="none";
	$("forward").style.display="block";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";
	$("can_but").style.display="inline-block";		
	$("send_but").style.display="inline-block";	
	if($('disp_but')) {$("disp_but").style.display="none";}
	$("send_but").onclick=function() {send_button('forward_frm')};	
	}
	
function cancel_button() {
	$("print_but").style.display="inline-block";
	if($('next_but')) {$("next_but").style.display="inline-block";}
	if($('prev_but')) {$("prev_but").style.display="inline-block";}
	$("reply").style.display="none"; 
	$("view").style.display="block";
	$("forward").style.display="none";
	$("forward_but").style.display="inline-block";
	$("reply_but").style.display="inline-block";	
	$("can_but").style.display="none";		
	$("send_but").style.display="none";
	if($('disp_but')) {$("disp_but").style.display="inline-block";}
	}

function send_button(theForm) {
	$("print_but").style.display="none";
	if($('next_but')) {$("next_but").style.display="none";}
	if($('prev_but')) {$("prev_but").style.display="none";}
	$("reply").style.display="none"; 
	$("view").style.display="none";
	$("forward").style.display="none";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";	
	$("can_but").style.display="none";		
	$("send_but").style.display="none";	
	$("close_but").style.display="none";
	if($('disp_but')) {$("disp_but").style.display="none";}
	$("the_sending").style.display="block";		
	document.forms[theForm].submit();
	}

function disp_button(theForm) {
	$("print_but").style.display="none";
	if($('next_but')) {$("next_but").style.display="none";}
	if($('prev_but')) {$("prev_but").style.display="none";}
	$("reply").style.display="none"; 
	$("view").style.display="none";
	$("forward").style.display="none";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";	
	$("can_but").style.display="none";		
	$("send_but").style.display="none";	
	$("close_but").style.display="none";	
	if($('disp_but')) {$("disp_but").style.display="none";}
	document.forms["disp_frm"].submit();
	}	

function pop_address(id) {
	if(document.reply_frm.frm_addrs) {
		if(document.reply_frm.frm_addrs.value == "") {
			sep = "";
			} else {
			sep = "|";
			}
		document.reply_frm.frm_addrs.value = document.reply_frm.frm_addrs.value + sep + id;
		}
	if(document.forward_frm.frm_addrs) {
		if(document.forward_frm.frm_addrs.value == "") {
			sep = "";
			} else {
			sep = "|";
			}	
		document.forward_frm.frm_addrs.value = document.forward_frm.frm_addrs.value + sep + id;
		}		
	}

function go_to(id, screen) {
	var thescreen = screen;
	document.go_frm.mode.value = 1;
	document.go_frm.id.value = id;
	document.go_frm.screen.value = thescreen;	
	document.go_frm.submit();
	}
	

</SCRIPT>
</HEAD>

<?php
if(!empty($_POST)) {
	if((isset($_POST['frm_disp'])) && ($_POST['frm_disp'] == 1)) {
		$tick_id = $_POST['frm_ticket_id'];
		$resp_id = $_POST['frm_resp_id'];
		$user_id = $_SESSION['user_id'];
		$respname = get_respname($resp_id);
		$tickname = get_tickname($tick_id);
		$facility = (array_key_exists('facility_id', $_POST)) ? $_POST['facility_id'] : 0;
		$rec_facility = (array_key_exists('rec_facility_id', $_POST)) ? $_POST['rec_facility_id'] : 0;	
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 		
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of` , `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`, `facility_id`, `rec_facility_id`) VALUES 
				('$now', 1, $tick_id, $resp_id, 'Dispatched from Messages', $user_id, '$now')";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$the_flag = "Responder " . $respname . " dispatched to " . $tickname;
?>
		<BODY>
			<CENTER>
			<DIV style='position: absolute; top: 50px; font-size: 20px; font-weight: bold;'><?php print $the_flag;?></DIV>
			<DIV ID='controls' style='position: relative; top: 150px; left: 5%; display: block; text-align: center; width: 20%;'>
				<SPAN id='close_but' class='plain text' style='float: left; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>CLOSE</SPAN>
			</DIV>	
			</CENTER>	
		</BODY>
		</HTML>
<?php
		exit();		
		} else {
		$the_separator = "\n\n------------------Original Message  ------------------\n\n";
		if((isset($_POST['frm_use_smsg'])) && ($_POST['frm_use_smsg'] == 1)) {
			$the_messageid = (!isset($_POST['frm_messageid'])) ? NULL : $_POST['frm_messageid'];
			$the_server = (!isset($_POST['frm_server'])) ? NULL: $_POST['frm_server'];
			do_send ("", $_POST['frm_addrs'], "Tickets CAD",  $_POST['frm_reply'] . $the_separator . $_POST['frm_message'], $_POST['frm_ticket_id'], $_POST['frm_resp_id'], $the_messageid, $the_server );		// - ($to_str, $to_smsr, $subject_str, $text_str, %ticket_id, $responder_id ) 
			} else {
			$the_messageid = (!isset($_POST['frm_messageid'])) ? "email" : $_POST['frm_messageid'];
			$the_server = (!isset($_POST['frm_server'])) ? NULL: $_POST['frm_server'];
			$the_addresses = (!empty($_POST['frm_theothers'])) ? $_POST['frm_addrs'] . "|" . $_POST['frm_theothers'] : $_POST['frm_addrs'];
			do_send ($the_addresses, "", "Tickets CAD",  $_POST['frm_reply'] . $the_separator . $_POST['frm_message'], $_POST['frm_ticket_id'], $_POST['frm_resp_id'], $the_messageid, $the_server );		// - ($to_str, $to_smsr, $subject_str, $text_str, %ticket_id, $responder_id ) 
			}
?>
		<BODY>
			<CENTER>
			<DIV style='position: absolute; top: 50px; left: 220px; font-size: 20px; font-weight: bold;'><?php print "Message Sent";?></DIV>
			<DIV ID='controls' style='position: relative; top: 150px; left: 5%; display: block; text-align: center; width: 20%;'>
				<SPAN id='close_but' class='plain text' style='float: left; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>CLOSE</SPAN>
			</DIV>	
			</CENTER>	
		</BODY>
		</HTML>
<?php
		exit();
		}
	}
	
	
$uid = strip_tags($_GET['id']); 

$this_msg = array_search($uid, $the_messages);
$next_msg = (array_key_exists(($this_msg + 1), $the_messages)) ? $the_messages[($this_msg + 1)] : "Last";
$prev_msg = (array_key_exists(($this_msg - 1), $the_messages)) ? $the_messages[($this_msg - 1)] : "First";

$next_but = ($next_msg != "Last") ? "<SPAN class='plain text' id='next_but' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);'  style='float: right; color: #000000; display: inline-block; vertical-align: middle;' onClick=\"go_to(" . $next_msg . ", '" . $screen . "');\"><IMG STYLE='float: right;' SRC='./images/next.png' BORDER=0 /></SPAN>" : "<SPAN class='plain text' id='next_but' style='float: right; display: inline-block; vertical-align: middle;'><IMG STYLE='float: right; opacity: 0.1; filter: alpha(opacity=50);' SRC='./images/next.png' BORDER=0 /></SPAN>";
$prev_but = ($prev_msg != "First") ? "<SPAN class='plain text' id='prev_but' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);'  style='float: right; color: #000000; display: inline-block; vertical-align: middle;' onClick=\"go_to(" . $prev_msg . ", '" . $screen . "');\"><IMG STYLE='float: right;' SRC='./images/previous.png' BORDER=0 /></SPAN>" : "<SPAN class='plain text' id='prev_but' style='float: right; display: inline-block; vertical-align: middle;'><IMG STYLE='float: right; opacity: 0.1; filter: alpha(opacity=50);' SRC='./images/previous.png' BORDER=0 /></SPAN>";

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
if(isset($_GET['wastebasket'])) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages_bin` `m` WHERE `id` = '" . $uid . "'";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	} else {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` `m` WHERE `id` = '" . $uid . "'";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}
$row = stripslashes_deep(mysql_fetch_assoc($result));
$readby = $row['readby'];
$message = $row['message'];
$recipients = $row['recipients'];
$fromAddress = $row['from_address'];
$theothers = "";
$tick_id = $row['ticket_id'];
$responder_id = $row['resp_id'];
$message_id = $row['message_id'];
$server = $row['server_number'];
$the_sep = "";
$the_readers = array();
$the_readers = explode("," , $row['readby']);
$the_readnames = array();
foreach($the_readers as $val) {
	$the_readnames[] = get_reader_name($val);
	}
$the_names = implode(",", $the_readnames);
$the_user = $_SESSION['user_id'];
$count_readers = count($the_readers);
$the_readstatus = ($count_users == $count_readers) ? 2 : 1;
if(($the_readers[0] != "") && (in_array($the_user, $the_readers, true))) {
	//	Do Nothing - user has already read this message
	} else {
	if(($readby == "") || ($readby == NULL)) {
		$the_sep = "";
		} else {
		$the_sep = ",";
		}
	$the_readstatus = ($count_users == $count_readers) ? 2 : 1;
	$the_readby_str = $readby . $the_sep . $_SESSION['user_id'];
	$query2 = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `readby`='$the_readby_str', `read_status` = " . $the_readstatus . " WHERE `id`='$uid'";
	$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);			
	}

if(($row['msg_type'] == 4) || ($row['msg_type'] == 5) || ($row['msg_type'] == 6)) {
	$fromAddress = ($row['from_address'] == "") ? $row['recipients'] : $row['from_address'];
	$theFrom = explode(",", $fromAddress);
	$theOthers = array();	
	foreach($theFrom AS $val) {
		$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `m` WHERE `smsg_id` = '" . $val . "'";
		$result1 = mysql_query($query1) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
			$theOthers[] = $row1['contact_via'];
			}
		}
	$theothers = implode(",", $theOthers);
	$recipients = implode("|", $theFrom);
	$recipients = "Tickets";
	}
		
if($row['msg_type'] == 3) {
	$theRecipients = explode(",", $row['recipients']);
	$theOthers = array();	
	foreach($theRecipients AS $val) {
		$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `m` WHERE `smsg_id` = '" . $val . "'";
		$result1 = mysql_query($query1) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
			$theOthers[] = $row1['contact_via'];
			}
		}
	$theothers = implode("|", $theOthers);
	$fromAddress = "Tickets";	
	}
	
if($row['recipients'] == "Tickets") {
	$recipients = "Tickets";
	}

$message = br2nl(html_entity_decode($message));
$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

if ($row['msg_type'] == 1) {
	$type_flag = "Outoging Email";
	$type = 1;
	$color = "background-color: blue; color: white;";
	} elseif ($row['msg_type'] ==2) {
	$type_flag = "Incoming Email";
	$type = 2;
	$color = "background-color: white; color: blue;";			
	} elseif ($row['msg_type'] ==3) {
	$color = "background-color: orange; color: white;";			
	$type_flag = "Outgoing SMS";
	$type = 3;
	} elseif (($row['msg_type'] ==4) || ($row['msg_type'] ==5) || ($row['msg_type'] ==6)) {
	$color = "background-color: white; color: orange;";				
	$type_flag = "Incoming SMS";	
	$type = 4;
	} else {
	$color = "";				
	$type_flag = "?";
	$type = 99;
	}

if(empty($_POST)) {
	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$tick_query = "SELECT *,
		`problemstart` AS `my_start`,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		`date` AS `date`,
		`booked_date` AS `booked_date`,		
		`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]ticket`.`facility` AS `facility`,
		`$GLOBALS[mysql_prefix]ticket`.`rec_facility` AS `rec_facility`,		
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
		`rf`.`name` AS `rec_fac_name`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`= " . $tick_id . " " . $restrict_ticket;			// 7/16/09, 8/12/09
	
	$tick_result = mysql_query($tick_query) or do_error($tick_query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$facility_id = 0;
	$rec_facility_id = 0;
	if (!mysql_num_rows($tick_result)){	//no tickets? print "error" or "restricted user rights"
		$num_tkts = 0;
		$error_msg = "No Ticket details for this message";
		} else {
		$num_tkts = mysql_num_rows($tick_result);
		$tick_row = stripslashes_deep(mysql_fetch_array($tick_result));
		$facility_id = ($tick_row['facility'] != 0) ? $tick_row['facility'] : 0;
		$rec_facility_id = ($tick_row['rec_facility'] != 0) ? $tick_row['rec_facility'] : 0;
		$error_msg = "";
		}
	//	ticket_id=" + ticket_id + "&responder_id=" + responder_id + "&facility_id=" + facility_id + "&mi_id=" + mi_id + "&sort= " + sort + "&dir=" + dir 


	$opener = strip_tags($_GET['screen']);
	$ticket_id = array_key_exists('ticket_id', $_GET) ? strip_tags($_GET['ticket_id']) : 0;
	$responder_id = array_key_exists('responder_id', $_GET) ? strip_tags($_GET['responder_id']) : 0;
	$mi_id = array_key_exists('mi_id', $_GET) ? strip_tags($_GET['mi_id']) : 0;
	$sort = array_key_exists('sort', $_GET) ? strip_tags($_GET['sort']) : 0;
	$dir = array_key_exists('dir', $_GET) ? strip_tags($_GET['dir']) : 0;
	$folder = (array_key_exists('folder', $_GET)) ? strip_tags($_GET['folder']) : "inbox";
	$the_refresh =  (isset($_GET['wastebasket'])) ? "refresh_waste(\"" . $opener . "\");" : "refresh_opener(\"" . $opener . "\", \"" . $folder . "\", \"" . $ticket_id . "\", \"" . $responder_id . "\", \"" . $facility_id . "\", \"" . $mi_id . "\", \"" . $sort . "\", \"" . $dir . "\");";
?>
	<BODY onLoad='<?php print $the_refresh;?>;'>
		<CENTER>
		<DIV id='outer' style='position: absolute; top: 0px; left: 0px; width: 100%; height: 750px; display: block;'>
			<DIV id='button_bar' class='but_container'>
				<SPAN id='print_but' class='plain text' style='width: 80px; float: left; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'><SPAN STYLE='float: left;'><?php print get_text('Print');?></SPAN><IMG STYLE='float: right;' SRC='./images/print_small.png' BORDER=0 /></SPAN>			
				<SPAN id='reply_but' class='plain text' style='width: 80px; float: left; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='reply_button();'><SPAN STYLE='float: left;'><?php print get_text('Reply');?></SPAN><IMG STYLE='float: right;' SRC='./images/reply_small.png' BORDER=0 /></SPAN>			
				<SPAN id='forward_but' class='plain text' style='width: 80px; float: left; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='forward_button();'><SPAN STYLE='float: left;'><?php print get_text('Fwd.');?></SPAN><IMG STYLE='float: right;' SRC='./images/forward_small.png' BORDER=0 /></SPAN>			
				<SPAN id='spacer1' style='width: 30px'>&nbsp;</SPAN>	
<?php
				if($mode !=1) {
?>
					<SPAN id='disp_but' class='plain text' style='width: 80px; float: left; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='disp_button();'><SPAN STYLE='float: left;'><?php print get_text('Dispatch');?></SPAN><IMG STYLE='float: right;' SRC='./images/dispatch_small.png' BORDER=0 /></SPAN>	
<?php
					}
?>
				<SPAN id='spacer2' style='width: 30px'>&nbsp;</SPAN>			
				<SPAN id='send_but' class='plain text' style='width: 80px; float: left; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='send_button();'><SPAN STYLE='float: left;'><?php print get_text('Send');?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0 /></SPAN>		
				<SPAN id='spacer3' style='width: 30px'>&nbsp;</SPAN>	
				<SPAN id='spacer' style='width: 30px'>&nbsp;</SPAN>	
				<SPAN id='close_but' class='plain text' style='width: 80px; float: right; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text('Close');?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0 /></SPAN>
				<SPAN id='can_but' class='plain text' style='width: 80px; float: right; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='cancel_button();'><SPAN STYLE='float: left;'><?php print get_text('Cancel');?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0 /></SPAN>
			</DIV>
			<DIV style='position: relative; top: 80px; right: 20px;'>
<?php
				if($mode !=1) {
					print $next_but;
					print $prev_but;
					}
?>
			</DIV>	
			<DIV id='view' style='position: relative; top: 60px; padding: 1%; margin: 2%; width: 95%; height: 600px; overflow-y: auto; text-align: left; font-size: 1.4em;'>
				<DIV style='font-size: 3em; color: #000000; text-align: center;'>VIEW</DIV>
<?php
				if($num_tkts > 0) {
?>
					<DIV style='width: 100%; min-height: 30px; text-align: center;'>
						<SPAN CLASS='header text text_large text_center' style='width: 100%; display: inline-block; height: 30px;'>TICKET DETAILS
							<SPAN id='show_tick' class='plain text' style='float: right; margin-right: 10px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('the_tick').style.display = 'inline-block'; $('show_tick').style.display = 'none'; $('hide_tick').style.display = 'inline-block';">Show</SPAN>
							<SPAN id='hide_tick' class='plain text' style='display: none; float: right; margin-right: 10px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('the_tick').style.display = 'none'; $('hide_tick').style.display = 'none'; $('show_tick').style.display = 'inline-block';">Hide</SPAN>						
						</SPAN>
						<DIV id='the_tick' style='width: 100%; display: none; background-color: transparent'>
<?php 
							print the_ticket($tick_row, "100%");

?>
						</DIV>
					</DIV>
					<BR />
<?php
					}
?>
				<DIV style='text-align: center; font-size: 1.4em; padding: 5px; <?php print $color;?>'><?php print $type_flag;?></DIV><BR />
				
				<DIV CLASS='td_label text text_left' style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Already Read by:</DIV>           
				<DIV CLASS='td_data text text_left' style='background-color: #FFFFFF; color: #707070; width: 100%; border: 1px inset #707070;'><?php print $the_names; ?></DIV><BR /> 
				
				<DIV CLASS='td_label text text_left' style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>From:</DIV>           
				<DIV CLASS='td_data text text_left' style='background-color: #FFFFFF; color: #707070; width: 100%; border: 1px inset #707070; word-break: break-all'><?php print $fromAddress; ?></DIV><BR />     

				<DIV CLASS='td_label text text_left' style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>To:</DIV>           
				<DIV CLASS='td_data text text_left' style='background-color: #FFFFFF; color: #707070; width: 100%; border: 1px inset #707070; word-break: break-all;'><?php print $recipients; ?></DIV><BR />    

				<DIV CLASS='td_label text text_left' style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Date:</DIV>           
				<DIV CLASS='td_data text text_left' style='background-color: #FFFFFF; color: #707070; width: 100%; border: 1px inset #707070; word-break: break-all'><?php print $row['date']; ?></DIV><BR />    

				<DIV CLASS='td_label text text_left' style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Subject:</DIV>           
				<DIV CLASS='td_data text text_left' style='background-color: #FFFFFF; color: #707070; width: 100%; border: 1px inset #707070; word-break: break-all'><?php print $row['subject']; ?></DIV><BR />    

				<DIV CLASS='td_label text text_left' style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Message:</DIV>   					
				<DIV CLASS='td_data_wrap text text_left' style='background-color: #FFFFFF; color: #707070; width: 100%; max-height: 200px; border: 1px inset #707070; overflow-y: auto;'><?php print $message; ?></DIV>  

				<FORM NAME="disp_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
					<INPUT TYPE="hidden" NAME = 'frm_ticket_id' VALUE="<?php print $tick_id;?>"/>	
					<INPUT TYPE="hidden" NAME = 'frm_resp_id' VALUE="<?php print $responder_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_disp' VALUE=1/>
					<INPUT TYPE="hidden" NAME = 'frm_messageid' VALUE="<?php print $message_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_server' VALUE="<?php print $server;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_facility_id' VALUE="<?php print $facility_id;?>"/>		
					<INPUT TYPE="hidden" NAME = 'frm_rec_facility_id' VALUE="<?php print $rec_facility_id;?>"/>							
				</FORM>
			</DIV>
			<DIV id='reply' style='display: none; position: relative; top: 60px; padding: 1%; margin: 2%; width: 500px; height: 700px; overflow-y: auto;'>
				<DIV style='font-size: 3em; color: #000000; text-align: center;'>REPLY</DIV>
				<DIV style='text-align: center; font-size: 1.4em; padding: 5px; <?php print $color;?>'><?php print $type_flag;?></DIV><BR /><BR />
				<table style='font-size: 1.4em;'> 
					<FORM NAME="reply_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
					<tr>    
						<td CLASS='td_label text'>&nbsp;</td>					
						<td CLASS='td_data text'><?php print $the_addressbook;?></td>           
					</tr>   					
					<tr>          
						<td CLASS='td_label text'>To:</td>           
						<td CLASS='td_data text'><INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE="<?php print $fromAddress; ?>"></td>      
					</tr>      
					<tr>           
						<td CLASS='td_label text'>Date:</td>           
						<td CLASS='td_data text'><INPUT TYPE='text' NAME='frm_date' size='60' VALUE="<?php print $now; ?>"></td>      
					</tr>     
					<tr>           
						<td CLASS='td_label text'>Subject:</td>           
						<td CLASS='td_data text'><INPUT TYPE='text' NAME='frm_subject' size='60' VALUE="<?php print $row['subject']; ?>"></td>      
					</tr> 	
					<tr>       
						<td CLASS='td_label text'>Original Message:</td>   					
						<td CLASS='td_data text'><TEXTAREA NAME="frm_message" readonly="readonly" COLS=58 ROWS=5 style='background-color: #F0F0F0 ; color: #707070; overflow-y: auto; overflow-x: hidden;'><?php print $message ;?></TEXTAREA></td>     
					</tr> 					
					<tr>       
						<td CLASS='td_label text'>Response:</td>   					
						<td CLASS='td_data text'><TEXTAREA NAME="frm_reply" COLS=58 ROWS=15></TEXTAREA></td>     
					</tr> 
<?php
					if(($type == 3) || ($type == 4) || ($type == 5) || ($type == 6)) {
?>
						<tr>
							<td CLASS='td_label text'>Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: </td>
							<td CLASS='td_data text'><INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE=1></td>
						</tr>		
<?php			
						}
?>
					<INPUT TYPE="hidden" NAME = 'frm_theothers' VALUE="<?php print $theothers;?>"/>			
					<INPUT TYPE="hidden" NAME = 'frm_ticket_id' VALUE="<?php print $tick_id;?>"/>	
					<INPUT TYPE="hidden" NAME = 'frm_resp_id' VALUE="<?php print $responder_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_messageid' VALUE="<?php print $message_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_server' VALUE="<?php print $server;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_facility_id' VALUE="<?php print $facility_id;?>"/>		
					<INPUT TYPE="hidden" NAME = 'frm_rec_facility_id' VALUE="<?php print $rec_facility_id;?>"/>							
					</FORM>
				</table>
			</DIV>
			<DIV id='forward' style='display: none; position: relative; top: 60px; padding: 1%; margin: 2%; width: 500px; height: 700px; overflow-y: auto;'>
				<DIV style='font-size: 3em; color: #000000; text-align: center;'>FORWARD</DIV>
				<DIV style='text-align: center; font-size: 1.4em; padding: 5px; <?php print $color;?>'><?php print $type_flag;?></DIV><BR /><BR />
				<table style='font-size: 1.4em;'> 
					<FORM NAME="forward_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
					<tr> 
						<td CLASS='td_label text'>&nbsp;</td>					
						<td CLASS='td_data text'><?php print $the_addressbook;?></td>           
					</tr>      					
					<tr>          
						<td CLASS='td_label text'>To:</td>           
						<td CLASS='td_data text'><INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE=""></td>      
					</tr>      
					<tr>           
						<td CLASS='td_label text'>Date:</td>           
						<td CLASS='td_data text'><INPUT TYPE='text' NAME='frm_date' size='60' VALUE="<?php print $now; ?>"></td>      
					</tr>     
					<tr>           
						<td CLASS='td_label text'>Subject:</td>           
						<td CLASS='td_data text'><INPUT TYPE='text' NAME='frm_subject' size='60' VALUE="<?php print $row['subject']; ?>"></td>      
					</tr> 	
					<tr>   
						<td CLASS='td_label text'>Original Message:</td>          					
						<td CLASS='td_data text'><TEXTAREA NAME="frm_message" readonly="readonly" COLS=58 ROWS=5 style='background-color: #F0F0F0; color: #707070; overflow-y: auto; overflow-x: hidden;'><?php print $message ;?></TEXTAREA></td>      
					</tr>
					<tr>   
						<td CLASS='td_label text'>Your Message:</td>          					
						<td CLASS='td_data text'><TEXTAREA NAME="frm_reply" COLS=58 ROWS=15></TEXTAREA></td>      
					</tr>					
<?php
					if(($type == 3) || ($type == 4)) {
?>
						<tr>
							<td>Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: </td>
							<td CLASS='td_data text'><INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE=1></td>
						</tr>		
<?php			
						}
?>		
					<INPUT TYPE="hidden" NAME = 'frm_theothers' VALUE="<?php print $theothers;?>"/>		
					<INPUT TYPE="hidden" NAME = 'frm_ticket_id' VALUE="<?php print $tick_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_resp_id' VALUE="<?php print $responder_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_messageid' VALUE="<?php print $message_id;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_server' VALUE="<?php print $server;?>"/>
					<INPUT TYPE="hidden" NAME = 'frm_facility_id' VALUE="<?php print $facility_id;?>"/>		
					<INPUT TYPE="hidden" NAME = 'frm_rec_facility_id' VALUE="<?php print $rec_facility_id;?>"/>							
					</FORM>
				</table>
			</DIV>
			<FORM NAME="go_frm" METHOD="get" ACTION = "<?php print basename( __FILE__); ?>">
			<INPUT TYPE='hidden' NAME='mode' VALUE=1>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			<INPUT TYPE='hidden' NAME='screen' VALUE=''>			
			</FORM>
		</DIV>
	</CENTER>
<SCRIPT LANGUAGE="Javascript">

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
</SCRIPT>
	</BODY>
<?php
}
?>	
<BODY>
<DIV id = 'the_sending' style='position: absolute; top: 50px; left: 220px; font-size: 20px; font-weight: bold; display: none;'><?php print "Please Wait";?><BR /><BR /><CENTER><img src="./images/pleasewait.gif" alt="Please Wait"/></CENTER></DIV>	
</BODY>
</HTML>
