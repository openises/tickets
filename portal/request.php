<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once '../incs/functions.inc.php';
do_login(basename(__FILE__));
$requester = get_owner($_SESSION['user_id']);
$id = (isset($_GET['id'])) ? $_GET['id'] : $_REQUEST['id'];

$can_edit = (is_service_user()) ? TRUE : FALSE;

$query = "SELECT *, UNIX_TIMESTAMP(`request_date`) AS `request_date` FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . $id . " LIMIT 1";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
$row = stripslashes_deep(mysql_fetch_assoc($result));

$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$rec_fac_menu = "<SELECT NAME='frm_rec_fac'>";
$rec_fac_menu .= "<OPTION VALUE=0 selected>Receiving Facility</OPTION>";
while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
		$sel = ($row_fc['id'] == $row['rec_facility']) ? "SELECTED" : "";
		$rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . " " . $sel . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
		}
$rec_fac_menu .= "<SELECT>";

$query_fc2 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc2 = mysql_query($query_fc2) or do_error($query_fc2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$orig_fac_menu = "<SELECT NAME='frm_orig_fac'>";
$orig_fac_menu .= "<OPTION VALUE=0 selected>Receiving Facility</OPTION>";
while ($row_fc2 = mysql_fetch_array($result_fc2, MYSQL_ASSOC)) {
		$sel = ($row_fc2['id'] == $row['orig_facility']) ? "SELECTED" : "";
		$orig_fac_menu .= "<OPTION VALUE=" . $row_fc2['id'] . " " . $sel . ">" . shorten($row_fc2['name'], 30) . "</OPTION>";
		}
$orig_fac_menu .= "<SELECT>";

$status_array = array('Open', 'Accepted', 'Resourced', 'Complete');
$status_sel = "<SELECT NAME='frm_status'>";
foreach($status_array AS $val) {
	$sel = ($val == $row['status']) ? "SELECTED": "";
	$status_sel .= "<OPTION VALUE='" . $val . "' " . $sel . ">" . $val . "</OPTION>";
	}
$status_sel .= "</SELECT>";

function get_contact_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[] = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		$the_ret[] = ($row['email'] != "") ? $row['email'] : "Unknown";
		$the_ret[] = ($row['email_s'] != "") ? $row['email_s'] : "Unknown";		
		$the_ret[] = ($row['phone_p'] != "") ? $row['phone_p'] : "Unknown";			
		$the_ret[] = ($row['phone_s'] != "") ? $row['phone_s'] : "Unknown";		
		}
	return $the_ret;
	}

function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		}
	return $the_ret;
	}

function get_facilityname($value) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";		 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['name'];
	}

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Service User Request</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<SCRIPT SRC="../js/misc_function.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT>
	var randomnumber;
	var the_string;
	var theClass = "background-color: #CECECE";

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
		
	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}

	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];

	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function syncAjax(strURL) {
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);
			return AJAX.responseText;																				 
			} 
		else {
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
			return false;
			}																						 
		}
		
	function do_edit() {
		$('view').style.display = 'none';
		$('edit').style.display = 'inline';
		}
		
	function accept(id) {
		randomnumber=Math.floor(Math.random()*99999999);
		var url ="./ajax/insert_ticket.php?id=" + id + "&version=" + randomnumber;
		sendRequest (url, requests_cb, "");
		function requests_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 0) {
				alert("Could not insert new Ticket, please try again");
				} else {
				$('view').style.display = 'none';
				$('edit').style.display = 'none';			
				$('result').style.display = 'inline-block';
				var the_link = "A New Ticket has been inserted. click the link below to view<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='the_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.parent.frames[\"main\"].location=\"../edit.php?id=" + the_response[0] + "\"; window.close();'>Go to Ticket</SPAN>";			
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				}
			}
		}

	function status_update(the_id, the_val) {									// write unit status data via ajax xfer
		var querystr = "the_id=" + the_id;
		querystr += "&status=" + the_val;
		var url = "up_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<?php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			$('view').style.display = 'none';
			$('edit').style.display = 'none';			
			$('result').style.display = 'inline-block';
			var the_link = "Status has been updated<BR /><BR /><BR /><BR />";		
			the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
			$('done').innerHTML = the_link;
			}
		}		// end function status_update()
		
	function tentative(id) {
		randomnumber=Math.floor(Math.random()*99999999);
		var url ="./ajax/insert_ticket_tentative.php?id=" + id + "&version=" + randomnumber;
		sendRequest (url, requests_cb, "");
		function requests_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 0) {
				alert("Could not insert new Ticket, please try again");
				} else {
				$('view').style.display = 'none';
				$('edit').style.display = 'none';			
				$('result').style.display = 'inline-block';
				var the_link = "A New Ticket has been inserted. click the link below to view<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='the_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.parent.frames[\"main\"].location=\"../edit.php?id=" + the_response[0] + "\"; window.close();'>Go to Ticket</SPAN>";			
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				}
			}
		}
	
	function decline(id) {
		randomnumber=Math.floor(Math.random()*99999999);
		var url ="./ajax/decline.php?id=" + id + "&version=" + randomnumber;
		sendRequest (url, requests_cb, "");
		function requests_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 200) {
				alert("Error, please try again");
				} else {
				$('view').style.display = 'none';
				$('edit').style.display = 'none';			
				$('result').style.display = 'inline-block';
				var the_link = "The request has been declined<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				}
			}
		}

	function startup() {
		$('edit').style.display = 'none';
		$('result').style.display = 'none';
		$('view').style.display = 'inline';	
		}

	</SCRIPT>
	</HEAD>
	<!-- <BODY onLoad = "ck_frames();"> -->

<?php
	$rec_facility = ($row['rec_facility'] != 0) ? get_facilityname($row['rec_facility']) : "Not Set";
	$orig_facility = ($row['orig_facility'] != 0) ? get_facilityname($row['orig_facility']) : "Not Set";	
	$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
	$now = time() - (intval(get_variable('delta_mins')*60));
	$the_details = get_contact_details($row['requester']);	
	$contact_email_p = $the_details[1];
	$contact_email_s = $the_details[2];			
	$contact_phone_p = $the_details[3];
	$contact_phone_s = $the_details[4];		

if(!empty($_POST)) {
	$meridiem_request_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_request_date'])))) ) ? "" : $_POST['frm_meridiem_request_date'] ;
	$request_date = "$_POST[frm_year_request_date]-$_POST[frm_month_request_date]-$_POST[frm_day_request_date] $_POST[frm_hour_request_date]:$_POST[frm_minute_request_date]:00$meridiem_request_date";	
	$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET 
		`street` = " . quote_smart(trim($_POST['frm_street'])) . ",
		`city` = " . quote_smart(trim($_POST['frm_city'])) . ",
		`state` = " . quote_smart(trim($_POST['frm_state'])) . ",
		`the_name` = " . quote_smart(trim($_POST['frm_patient'])) . ",
		`phone` = " . quote_smart(trim($_POST['frm_phone'])) . ",
		`orig_facility` = " . quote_smart(trim($_POST['frm_orig_fac'])) . ",		
		`rec_facility` = " . quote_smart(trim($_POST['frm_rec_fac'])) . ",
		`scope` = " . quote_smart(trim($_POST['frm_scope'])) . ",
		`description` = " . quote_smart(trim($_POST['frm_description'])) . ",		
		`comments` = " . quote_smart(trim($_POST['frm_comments'])) . ",	
		`request_date` = " . quote_smart(trim($request_date)) . ",	
		`status` = " . quote_smart(trim($_POST['frm_status'])) . ",	
		`description` = " . quote_smart(trim($_POST['frm_description'])) . "
		WHERE `id` = " . $_POST['id'];
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
print "Done";
?>
	<BODY>
<?php	
	} else {	
	
?>
	<BODY onLoad="startup(); location.href = '#top';">

	<DIV id='view' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='banner' class='heading' style='font-size: 20px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>Tickets Service User Request</DIV><BR /><BR />
		<DIV id='leftcol' style='position: fixed; left: 2%; top: 8%; width: 96%; height: 90%;'>
			<DIV id='left_scroller' style='position: relative; top: 0px; left: 0px; height: 80%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
				<TABLE style='width: 100%;'>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Requested By</TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($row['requester']);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'>Request Date and Time</TD><TD class='td_data' style='text-align: left;'><?php print format_date($row['request_date']);?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Status');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['status'];?></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Patient');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['the_name'];?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['street'];?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['city'];?></TD>
					</TR>			
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['state'];?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['phone'];?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Email');?></TD><TD class='td_data' style='text-align: left;'><?php print $contact_email_p;?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_facility;?></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_facility;?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['scope'];?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['description'];?></TD>
					</TR>		
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Comments');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['comments'];?></TD>
					</TR>			
				</TABLE>
			</DIV><BR /><BR />
<?php
	if($can_edit) {
?>
			<SPAN id='edit_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_edit();">Edit</SPAN>	
<?php
	}
	if((!is_service_user()) && ($row['status'] == 'Open')) {
?>
			<SPAN id='tent_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "tentative(<?php print $id;?>);">Tentatively Accept and open Ticket</SPAN>
<?php
	}
	if((!is_service_user()) && ($row['status'] == 'Open')) {
?>
			<SPAN id='accept_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "accept(<?php print $id;?>);">Accept and open Ticket</SPAN>
<?php
	}
	if((!is_service_user()) && ($row['status'] == 'Tentative')) {
?>
			<SPAN id='accept_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "status_update(<?php print $id;?>, 'Accepted');">Accept</SPAN>
<?php
	}
	if((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Tentative'))) {
?>	
			<SPAN id='decline_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "decline(<?php print $id;?>);">Decline</SPAN>			
<?php
	}
?>
			<SPAN id='close_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN><BR /><BR />		
		</DIV>
	</DIV>
	<DIV id='edit' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='edit_banner' class='heading' style='font-size: 20px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>Edit Tickets Service User Request</DIV><BR /><BR />
		<DIV id='edit_leftcol' style='position: fixed; left: 2%; top: 8%; width: 96%; height: 90%;'>
			<DIV id='edit_left_scroller' style='position: relative; top: 0px; left: 0px; height: 90%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
				<FORM NAME='edit_frm' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
				<TABLE style='width: 100%;'>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Requested By</TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($row['requester']);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'>Request Date and Time</TD><TD class='td_data' style='text-align: left;'><?php print generate_date_dropdown('request_date',$row['request_date'],FALSE);?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Status');?></TD><TD class='td_data' style='text-align: left;'><?php print $status_sel;?></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Patient');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['the_name'];?>"></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_street' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['street'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_city' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['city'];?>"></TD>
					</TR>			
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print $row['state'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_phone' TYPE='TEXT' SIZE='16' MAXLENGTH='16' VALUE="<?php print $row['phone'];?>"></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_fac_menu;?></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_fac_menu;?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_scope' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['scope'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_description" COLS="45" ROWS="2" WRAP="virtual"><?php print $row['description'];?></TEXTAREA></TD>
					</TR>		
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Comments');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"><?php print $row['comments'];?></TEXTAREA></TD>
					</TR>				
				</TABLE>
				<INPUT NAME='requester' TYPE='hidden' SIZE='24' VALUE="<?php print $_SESSION['user_id'];?>">
				<INPUT NAME='id' TYPE='hidden' SIZE='24' VALUE="<?php print $id;?>">
			</DIV><BR /><BR />
			<SPAN id='sub_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.forms['edit_frm'].submit();">Update</SPAN>
			<SPAN id='close_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN><BR /><BR />	
			</FORM>		
		</DIV>
	</DIV>
	<DIV id='result' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='done'></DIV>
	</DIV>
	</BODY>
	</HTML>
<?php
}
?>
