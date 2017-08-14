<?php
/*
6/13/09	initial release
6/28/09	handle no assigns and empty scope
3/21/10 div, table re-arrange, add color-coding by status,  legend
4/28/10 open tickets only, order by severity
5/25/10 size changes applied
7/1/10 restrict to active assigns this incident
7/2/10 accomodate facility email  
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
11/15/10 signals added
11/19/10 window width set as fixed, MySQL LOCATE() employed for email addr test
1/14/10 status legend, email count added, get_text units, incident
2/8/11 dumps removed, for production
3/15/11 changed stylesheet.php to stylesheet.php
10/23/12 Added code for messaging (SMS Gateway)
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
set_time_limit(0);
@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');
//dump($_REQUEST);
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
	if(my_is_float($lat1) && my_is_float($lon1) && my_is_float($lat2) && my_is_float($lon2)) {
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344); 
			} else if ($unit == "N") {
			return ($miles * 0.8684);
			} else {
			return $miles;
			}
		} else {
		return 0;
		}
	}

function subval_sort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}

$tik_id = 0;
$smsg_provider = return_provider_name(get_msg_variable('smsg_provider'));
$smsg_providers = array('SMS Responder','SMS Broadcast','MOTOTRBO Text Message','Txt Local');
$using_smsg = ((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) ? true : false;

if ((!(empty($_GET))) && (array_key_exists('name', $_GET))) {	//	10/23/12
	$step = (((integer) $_GET['name'])==0)? 2 : 0 ;
	} elseif((!(empty($_GET))) && (array_key_exists('the_ticket', $_GET)) && $_GET['the_ticket'] == "doselect")  {	//	10/23/12
	$step = 1;
	} elseif((!(empty($_GET))) && (array_key_exists('the_ticket', $_GET)) && $_GET['the_ticket'] != "undefined" && $_GET['the_ticket'] != "doselect")  {	//	10/23/12
	$tik_id = $_GET['the_ticket'];
	$step = (((integer) $_GET['the_ticket'])==0)? 0 : 2 ;
	} else {
//	dump(__LINE__);
	if (empty($_POST)) {
		$query = "SELECT DISTINCT `ticket_id` , scope, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			WHERE `status` = {$GLOBALS['STATUS_OPEN']} OR  `status` = {$GLOBALS['STATUS_OPEN']}
			ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;				// 4/28/10
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$no_open_tickets = mysql_affected_rows();
		if($no_open_tickets==0) {			// 6/28/09
			$step = 2;
			} else {
			$step = 1;
			}
		} else {
		$step = $_POST['frm_step'];
		}
	}
//dump(__LINE__);
//dump($step);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print LessExtension(basename(__FILE__));?> </TITLE>
<META NAME="Description" CONTENT="Email to units and facilities">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
BODY {FONT-SIZE: 1vw;}
INPUT {FONT-SIZE: 1vw;}
SELECT {FONT-SIZE: 1vw;}
OPTION {FONT-SIZE: 1vw;}
TABLE {FONT-SIZE: 1vw;}
TEXTAREA {FONT-SIZE: 1vw;}
.td_label {FONT-SIZE: 1vw;}
.plain {FONT-SIZE: 1vw;}
.hover {FONT-SIZE: 1vw;}
.container {display: table; border: 1px outset #FFFFFF; padding: 10px;}
.row { white-space: nowrap; display: table-row;}
.cell {display: table-cell; padding-left: 10px; padding-right: 10px;}
</STYLE>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}
	
	function do_step_1() {
		document.mail_form.submit();
		}

	function do_step_2() {
		if (document.mail_form.frm_text.value.trim()=="") {
			alert ("Message text is required");
			return false;
			}
		var sep = "";
		var sep2 = "";	//	10/23/12
		var z;	//	10/23/12
		for (i=0;i<document.mail_form.elements.length; i++) {	//	10/23/12
			if((document.mail_form.elements[i].type =='checkbox') && (document.mail_form.elements[i].checked)){		// frm_add_str
				var the_val_arr = document.mail_form.elements[i].value.split(":"); 
				var the_e_add = the_val_arr[0];
				var the_r_id = the_val_arr[1];
				var the_smsg_id = the_val_arr[2];
				var x=1;
				if((document.mail_form.use_smsg[1]) && (document.mail_form.use_smsg[1].checked)) {
					if((the_smsg_id != "NONE") && (the_smsg_id != "")) {
						document.mail_form.frm_smsg_ids.value += sep2 + the_smsg_id;	
						document.mail_form.frm_resp_ids.value += sep + the_r_id;							
						} else {
						document.mail_form.frm_resp_ids.value += sep + the_r_id;					
						document.mail_form.frm_add_str.value += sep + the_e_add;
						}
					} else {
					document.mail_form.frm_resp_ids.value += sep + the_r_id;					
					document.mail_form.frm_add_str.value += sep + the_e_add;
					}
				sep = "|";
				sep2 = ",";				
				}
			}
		if ((document.mail_form.frm_add_str.value.trim()=="") && (document.mail_form.frm_smsg_ids.value.trim()=="")) {	//	10/23/12
			alert ("Addressees required");
			return false;
			}
		document.mail_form.submit();	
		}

	function reSizeScr(lines){							// 5/25/10 
		var the_width = 1200;							// 11/19/10
		var the_height = ((lines * 10)+200);			// values derived via trial/error (more of the latter, mostly)
		if (the_height <400) {the_height = 400;}
		window.resizeTo(the_width,the_height);	
		}
	var set_text = true;

	function set_signal(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		if (set_text) {
			var sep = (document.mail_form.frm_text.value=="")? "" : " ";
			document.mail_form.frm_text.value+=sep + temp_ary[1] + ' ';
			document.mail_form.frm_text.focus();
			}
		else {
			var sep = (document.mail_form.frm_subj.value=="")? "" : " ";
			document.mail_form.frm_subj.value+= sep + temp_ary[1] + ' ';
			document.mail_form.frm_subj.focus();
			}
		}		// end function set_signal()
		
	function set_message(id) {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		var theMessages = <?php echo json_encode($std_messages);?>;
		var message = theMessages[parseInt(id)]['message'];
		var tick_id = <?php print $tik_id;?>;
		var url = './ajax/get_replacetext.php?tick=' + tick_id + '&version=' + randomnumber + '&text=' + encodeURIComponent(message);
		sendRequest (url,replacetext_cb, "");			
		function replacetext_cb(req) {
			var the_text=JSON.decode(req.responseText);
			if (the_text[0] == "") {
				var replacement_text = message;
				} else {
				var replacement_text = the_text[0];					
				}
			document.mail_form.frm_text.value += replacement_text;					
			}			// end function replacetext_cb()	
		}		// end function set_message(message)

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
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

	function syncAjax(strURL) {							// synchronous ajax function - 4/5/10
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
			return AJAX.responseText;																				 
			} 
		else {
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
			return false;
			}																						 
		}		// end function sync Ajax()

</SCRIPT>
</HEAD>
<?php
	switch($step) {
			case 0:
				$where = (((integer) $_GET['name'])==0)? 
					" ORDER BY `name` ASC " : 
					" WHERE `id` = {$_GET['name']} LIMIT 1";		 // if id supplied
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` {$where};";		// 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$smsg_ids = (isset($row['smsg_id'])) ? $row['smsg_id'] : "";			
?>
			<BODY scroll='auto' onLoad = "reSizeScr(1); document.mail_form.frm_subj.focus();"><CENTER>		<!-- 1/12/09 -->
			<TABLE ALIGN='center' BORDER = 0>
				<TR CLASS='odd'><TH COLSPAN=2>Mail to: <?php print $row['name']; ?></TH></TR> <!-- 7/2/10 -->
				
				<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
				<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	
<?php
				if($row['contact_via'] != "") { 
?>
					<TR VALIGN = 'TOP' CLASS='even'><TD ALIGN='right'  CLASS="td_label">To: </TD>
						<TD><INPUT TYPE='text' NAME='frm_add_str' VALUE='<?php print $row['contact_via'];?>' SIZE = 36></TD>
					</TR>	

<?php 
					} else { 
					print "<INPUT TYPE='hidden' NAME='frm_add_str' value''>"; 
					} 
?>
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD ALIGN='right' CLASS="td_label">Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 55></TD></TR>	
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label">Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=45 ROWS=4 wrap="hard"></TEXTAREA><BR /><SPAN CLASS='warn'><?php print get_text("messaging help"); ?></SPAN></TD></TR>
				<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
					<TD ALIGN='right' CLASS="td_label">Signal: </TD><TD>

						<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
							<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
							$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
							$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {
								print "\t<OPTION VALUE='{$row2['code']}'>{$row2['code']}|{$row2['text']}</OPTION>\n";		// pipe separator
								}
?>
						</SELECT>
						<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>
						<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'>&nbsp;&nbsp;</SPAN>
					</TD>
				</TR>
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label">Standard Message: </TD><TD>

						<SELECT NAME='std_msgs' onChange = 'set_message(this.options[this.selectedIndex].value);'>	<!--  11/17/10 -->
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
						print get_standard_messages_sel();
?>
						</SELECT>
						<BR />
					</TD>
				</TR>
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='center' COLSPAN=2><BR /><BR />
						<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_step_2();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
						<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
					</TD>
				</TR>
				<TR><TD>&nbsp;</TD></TR>	
<?php
				if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) { // 10/23/12
?>				
					<TR>
						<TD ALIGN='left' COLSPAN=2>
<?php 
							if($row['contact_via'] != "") {
?>			
								<input type="radio" name="use_smsg" VALUE="0"
<?php
									if($row['contact_via'] != "" && get_msg_variable('default_sms') == "0") {
										print "checked";
										}	
?>
									> Use Email or Twitter<br>
<?php 
								} 
?>
							<input type="radio" name="use_smsg" VALUE="1"
<?php
							if($row['contact_via'] == "" || get_msg_variable('default_sms') == "1") {
								print "checked";
								}	
?>
								> Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>
								?<br>	<!-- 10/23/12 -->
						</TD>
					</TR>
<?php
					} else {
?>
				<INPUT TYPE='hidden' NAME="use_smsg" VALUE='0'>
<?php
					}
?>
				</TABLE>
				<INPUT type='hidden' NAME='frm_resp_ids' VALUE=''>
				<INPUT type='hidden' NAME='frm_smsg_ids' VALUE='<?php print $smsg_ids;?>'>				
				</FORM>
	
<?php
								
				break;

		case 1:
			$query = "SELECT DISTINCT `ticket_id` , `scope`, `severity`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				WHERE `t`.`status` = {$GLOBALS['STATUS_OPEN']} OR `t`.`status` = {$GLOBALS['STATUS_SCHEDULED']}	
				ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;				// 4/28/10
		
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$no_tickets = mysql_affected_rows();
			if($no_tickets==1) {
				$row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC) ;
//				dump($row);
?>
				<BODY scroll='auto' onLoad = "document.mail_form_single.submit();">	<!-- 1/12/09 -->
					<FORM NAME='mail_form_single' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
						<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>	<!-- '2' = select units, '3' = send to selected units -->
						<INPUT TYPE='hidden' NAME='frm_sel_inc' VALUE='<?php print $row['ticket_id'];?>'>	
					</FORM>
				</BODY>
				</HTML>			
<?php			
				}
			
?>		
			<BODY scroll='auto' onLoad = "reSizeScr(1); document.mail_form.frm_subj.focus();">
				<CENTER>		<!-- 1/12/09 -->
				<H3>Mail to <?php print get_text("Units"); ?></H3>
				<P>
				<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
					<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>	<!-- '2' = select units, '3' = send to selected units -->
<?php
			$bg_colors_arr = array ("transparent", "lime", "red");		// for severity
			if($no_tickets >= 2) {
				print "<EM>". get_text("Units"). " assigned to ". get_text("Incident") . "</EM>: 
					<SELECT NAME='frm_sel_inc' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;'>\n\t
					<OPTION VALUE=0 SELECTED>All incidents </OPTION>\n";
				while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
					$bg_color = $bg_colors_arr[$row['severity']];
					if(!(empty($row['scope']))) {				// 6/28/09
						print "\t<OPTION VALUE='{$row['incident']}' STYLE='background-color:{$bg_color}; color:black;' >{$row['scope']} </OPTION>\n";
						}
					}
				}		// end if($no_tickets >= 2)
?>
					</SELECT>
				</FORM>
				</P>
				<BR />
				<BR />
				<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_step_1();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</CENTER>
<?php
			break;

		case 2:													// 9/19/10
//			dump(__LINE__);
			$tik_id = ((isset($_GET['the_ticket'])) && ($_GET['the_ticket'] != 0)) ? $_GET['the_ticket'] : 0;	//	10/23/12
			$t_query = "SELECT `t`.`lat` AS `t_lat`, `t`.`lng` AS `t_lng`	FROM `$GLOBALS[mysql_prefix]ticket` `t`	WHERE `id` = {$tik_id} LIMIT 1";
			$t_result = mysql_query($t_query) or do_error($t_query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$t_row = stripslashes_deep(mysql_fetch_assoc($t_result), MYSQL_ASSOC);			
			$assigned_resp = array();			
			$default_msg = "Ticket ID *" . $tik_id . "*";	//	10/23/12
			if ((!array_key_exists ( 'frm_sel_inc', $_POST)) || ($_POST['frm_sel_inc']==0)) {
				$default_msg = "Ticket ID *" . $tik_id . "*";	//	10/23/12
				$query_ass = "SELECT *, 
					`r`.`id` AS `responder_id`
					FROM `$GLOBALS[mysql_prefix]assigns` `a`
					LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	 `s` ON (`r`.`un_status_id` = `s`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)
					WHERE `ticket_id` = {$tik_id} 
					AND (LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')) 
					AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
					ORDER BY `r`.`id` ASC ";	//	10/23/12			
				$result_ass = mysql_query($query_ass) or do_error($query_ass, 'mysql query failed', mysql_error(), __FILE__, __LINE__);				
				while($row_ass = stripslashes_deep(mysql_fetch_assoc($result_ass), MYSQL_ASSOC)){
					$assigned_resp[] = $row_ass['responder_id'];
					}
			
				$query = "SELECT *,	`r`.`id` AS `responder_id`,
					`r`.`lat` AS `r_lat`,
					`r`.`lng` AS `r_lng`				
					FROM `$GLOBALS[mysql_prefix]responder` `r`
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	`s` ON (`r`.`un_status_id` = `s`.`id`)
					WHERE LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> '')
					ORDER BY  `name` ASC ";	//	10/23/12
//			dump(__LINE__);
//			dump($step);
				}
			else {												// 7/1/10 - 9/19/10
				$query = "SELECT *, `r`.`id` AS `responder_id`,
					`t`.`lat` AS `t_lat`,
					`t`.`lng` AS `t_lng`,
					`r`.`lat` AS `r_lat`,
					`r`.`lng` AS `r_lng`
					FROM `$GLOBALS[mysql_prefix]assigns` `a`
					LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	 `s` ON (`r`.`un_status_id` = `s`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)
					WHERE `ticket_id` = {$_POST['frm_sel_inc']} AND (LOCATE('@', `contact_via`) > 1 || (`smsg_id` IS NOT NULL AND `smsg_id` <> ''))
					AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
					ORDER BY `name` ASC ";	//	10/23/12
//			dump(__LINE__);
				$tik_id = $_POST['frm_sel_inc'];
				$default_msg = "Ticket ID *" . $tik_id . "*";	//	10/23/12
				}
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$lines = mysql_affected_rows() +8;
			$no_rows = mysql_affected_rows();
?>
			<SCRIPT>
			
			function do_clear(){
				for (i=0;i<document.mail_form.elements.length; i++) {
					if(document.mail_form.elements[i].type =='checkbox'){
						document.mail_form.elements[i].checked = false;
						}
					}		// end for ()
				$('clr_spn').style.display = "none";
				$('chk_spn').style.display = "inline-block";
				}		// end function do_clear

			function do_check(){
				for (i=0;i<document.mail_form.elements.length; i++) {
					if(document.mail_form.elements[i].type =='checkbox'){
						document.mail_form.elements[i].checked = true;
						}
					}		// end for ()
				$('clr_spn').style.display = "inline-block";
				$('chk_spn').style.display = "none";
				}		// end function do_clear

			</SCRIPT>
		<BODY scroll='auto' onLoad = "reSizeScr(<?php print $lines;?>); document.mail_form.frm_subj.focus();"><CENTER>		<!-- 1/12/09  -->
		<TABLE ALIGN = 'center' border=0>
			<TR>
				<TD COLSPAN=99 ALIGN='center'>
					<SPAN CLASS='text_biggest bold'>Mail to <?php print get_text("Units"); ?></SPAN><BR /><SPAN class='text' style='display: inline;'>Check checkbox to Select Unit</SPAN>
				</TD>
			</TR>
<?php
		if($no_rows>0) {
?>
			<TR>
				<TD COLSPAN=99 ALIGN='center'>
					<SPAN id='clr_spn' CLASS='plain text' style='width: 100px; display: none; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_clear();"><SPAN STYLE='float: left;'><?php print get_text("Uncheck All");?></SPAN><IMG STYLE='float: right;' SRC='./images/unselect_all_small.png' BORDER=0></SPAN>
					<SPAN id='chk_spn' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_check();"><SPAN STYLE='float: left;'><?php print get_text("Check All");?></SPAN><IMG STYLE='float: right;' SRC='./images/select_all_small.png' BORDER=0></SPAN>
				</TD>
			</TR>
			<TR>
				<TD COLSPAN=99>&nbsp;</TD>
			</TR>
<?php
		}
?>
			<P>
			
			<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	<!-- '3' = select units, '3' = send to selected units -->
			<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->

<?php			
				if($no_rows>0) {
					$the_arr = array();
					$n=1;
					while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){	//	create an array from the result row
						$name_arr = explode("/", $row['name']);
						$thename = $name_arr[0];
						$the_arr[$n]['smsg_id'] = $row['smsg_id'];
						$the_arr[$n]['name'] = $row['name'];
						$the_arr[$n]['handle'] = $row['handle'];	
						$the_arr[$n]['responder_id'] = $row['responder_id'];
						$the_arr[$n]['contact_via'] = $row['contact_via'];
						$the_arr[$n]['bg_color'] = $row['bg_color'];		
						$the_arr[$n]['text_color'] = $row['text_color'];
						$the_arr[$n]['cellphone'] = ($row['cellphone'] != "" && $row['cellphone'] != null) ? $row['cellphone'] : "";
						$the_arr[$n]['distance'] = (isset($t_row['t_lat'])) ? distance($row['r_lat'], $row['r_lng'], $t_row['t_lat'], $t_row['t_lng'], "N") : 0;	//	populate array entry with distance from responder to ticket
						$the_arr[$n]['status'] = $row['status_val'];
						$n++;
						}
					if((isset($_GET['the_ticket'])) && ($_GET['the_ticket'] != 0)) {
						$the_arr = subval_sort($the_arr,'distance'); 	//	sort array by distance ascending but only if the mail form is called from a Ticket
						}

					$i=1;
					print "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_units_legend() . "</TD></TR>";
					print "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>";
					print "<TR><TD COLSPAN = 3 ALIGN='left' style='padding-left: 10px; padding-right: 10px;'>" . get_unit_status_legend() . "</TD></TR>";
					print "<TR><TD COLSPAN = 3 ALIGN='center'>&nbsp;</TD></TR>";
					print "<TR><TD>\n";
					print "<TABLE ALIGN = 'center' BORDER=0><TR><TD>\n";
					print "<DIV class='container' style='display: block; width: auto; min-height: 200PX; max-height: 600px; overflow-y: scroll; overflow-x: none;'>";	//	10/23/12
					foreach($the_arr as $val) {
						if(!empty($assigned_resp)) {
							$checked = in_array($val['responder_id'],$assigned_resp) ? "checked" : "";
							} else {
							$checked = "";
							}
						$smsg = $cell = "";
						$e_add = (($val['contact_via'] == NULL) || ($val['contact_via'] == "")) ? "<SPAN TITLE='No email address stored' class='cell' style='color: LightGrey;'>(E) NONE</SPAN>" : "<SPAN TITLE='{$val['contact_via']}' class='cell'>(E) " . shorten($val['contact_via'], 30) . "</SPAN>" ;
						if($using_smsg && $smsg_provider == "SMS Responder") {
							$smsg = (($val['smsg_id'] == NULL) || ($val['smsg_id'] == "")) ? "<SPAN TITLE='SMS Gateway ID stored' class='cell' style='color: LightGrey; display:'>(SMSG) NONE</SPAN>" : "<SPAN TITLE='{$val['smsg_id']}' class='cell'>(SMSG) " . shorten($val['smsg_id'], 10) . "</SPAN>" ;
							}
						if($using_smsg && $smsg_provider == "Txt Local") {				
							$cell = (($val['cellphone'] == NULL) || ($val['cellphone'] == "")) ? "<SPAN TITLE='No Cellphone number stored' class='cell' style='color: LightGrey;'>(CELL) NONE</SPAN>" : "<SPAN TITLE='{$val['cellphone']}' class='cell'>(CELL) " . shorten($val['cellphone'], 12) . "</SPAN>" ;
							}
						$dist = (round($val['distance'],1) != 0) ? "Dist: " . round($val['distance'],2) : "";         
						print "\t<SPAN class='row' STYLE='background-color:{$val['bg_color']}; color:{$val['text_color']};'>
							<INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$val['contact_via']}:{$val['responder_id']}:{$val['smsg_id']}:' {$checked}>
							<SPAN class='cell'>{$dist}</SPAN>
							<SPAN class='cell'>{$val['handle']}</SPAN>
							<SPAN TITLE='{$val['status']}' class='cell'>{$val['name']}</SPAN>
							{$e_add} 
							{$smsg}
							{$cell}
							</SPAN>\n";	//	10/23/12
						$i++;
						}		// end while()
?>
					<BR />
					</DIV>
				</TD>
			</TR>
			</TABLE>
			</TD><TD style='vertical-align: top;'>
			<TABLE BORDER=0>
			<TR VALIGN='top' CLASS='even'><TD CLASS="td_label" ALIGN='right'>Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 55 VALUE='<?php print $default_msg;?>'></TD></TR>	<!-- 10/23/12 -->
			<TR VALIGN='top' CLASS='odd'><TD CLASS="td_label" ALIGN='right'>Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=45 ROWS=4 wrap="soft"></TEXTAREA><BR /><SPAN CLASS='warn'><?php print get_text("messaging help"); ?></SPAN></TD></TR>

			<TR VALIGN = 'TOP' CLASS='even'>
				<TD ALIGN='right' CLASS="td_label">Signal: </TD><TD>

					<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
					<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
//					dump(__LINE__);
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
					while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
						print "\t<OPTION VALUE='{$row['code']}'>{$row['code']}|{$row['text']}</OPTION>\n";

						}
?>
					</SELECT>
					<BR />
					<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>
					<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'></SPAN>
				</TD>
			</TR>
			<TR VALIGN = 'TOP' CLASS='even'>
				<TD ALIGN='right' CLASS="td_label">Standard Message: </TD><TD>	<!-- 10/23/12 -->

					<SELECT NAME='std_msgs' onChange = 'set_message(this.options[this.selectedIndex].value);'>	<!--  11/17/10 -->
					<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
					print get_standard_messages_sel();
?>
					</SELECT>
					<BR />
				</TD>
			</TR>
			<TR VALIGN='top' CLASS='odd'>
				<TD ALIGN='center' COLSPAN=2><BR /><BR />
					<SPAN ID='next_but' CLASS='plain text' STYLE='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_step_2();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
					<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.mail_form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</TD>
			</TR>
<?php	//	10/23/12
				if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
?>					
					<TR><TD>&nbsp;</TD></TR>				
					<TR>
						<TD ALIGN='left' COLSPAN=2>
							<input type="radio" name="use_smsg" VALUE="0" checked> Use Email<br>
							<input type="radio" name="use_smsg" VALUE="1"> Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?<br>						
						</TD>
					</TR>
<?php
					} else {
?>
				<INPUT TYPE='hidden' NAME="use_smsg" VALUE='0'>
<?php
					}
?>
				<INPUT type='hidden' NAME='frm_resp_ids' VALUE=''>
				<INPUT type='hidden' NAME='frm_smsg_ids' VALUE=''>		
				<INPUT type='hidden' NAME='frm_ticket_id' VALUE='<?php print $tik_id;?>'>					
<?php
				print "</TABLE></TD></TR></TABLE></FORM><BR />";
				
				} else {		// end if(mysql_affected_rows()>0)		
?>
				<H3>No addresses available!</H3>
					<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
<?php
				}
		
			break;

		case 3:	//	10/23/12
			$smsg_ids = ((isset($_POST['use_smsg'])) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";
			$address_str = $_POST['frm_add_str'];
			$resp_ids = ((isset($_POST['frm_resp_ods'])) && ($_POST['frm_resp_ids'] != "") && ($_POST['frm_resp_ids'] != 0)) ? $_POST['frm_resp_ids'] : 0;
			$count = 0;
			$tik_id = ((isset($_POST['frm_ticket_id'])) && ($_POST['frm_ticket_id'] != 0)) ? $_POST['frm_ticket_id'] : 0;
			$count = do_send ($address_str, $smsg_ids, $_POST['frm_subj'], $_POST['frm_text'], $tik_id, $_POST['frm_resp_ids']);	// ($to_str, $to_smsr, $subject_str, $text_str, $ticket_id, $responder_id )
//			snap(__LINE__, $count);
?>
<BODY scroll='auto' onLoad = "reSizeScr(2)"><CENTER>		<!-- 1/14/10 -->
<CENTER><BR /><BR /><BR /><H3><?php print "Messages sent: {$count}";?></H3>
<BR /><BR />
<SPAN id='closebut' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>


<?php
			break;

		default:
		    echo __LINE__ . " error error error ";
		}

?>
</BODY>
</HTML>
