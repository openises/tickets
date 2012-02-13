<?php 
error_reporting(E_ALL);		// 10/1/08

$cycle = 5000;			// user reviseable delay between polls, in milliseconds
$list_length = 99;		// chat list length maximum

/*
12/26/09 rather complete re-write, to include invitations, list length limiting, who's logged on
1/23/10 PHP sessions replaces custom session handler
5/29/10 revised chat read for asynch ajax
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
5/4/11 get_new_colors() added
*/
@session_start();	
require_once('./incs/functions.inc.php');		//7/28/10
do_login(basename(__FILE__));
extract ($_GET);

$hours = (intval(get_variable('chat_time'))>0)? intval(get_variable('chat_time')) : 4;	// force to default

$old = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($hours*60*60)); // n hours ago

$query  = "DELETE FROM `$GLOBALS[mysql_prefix]chat_messages` WHERE `when`< '" . $old . "'";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				// 11/15/11
$sig_script = "<SCRIPT>
		function set_signal(inval) {
			var temp_ary = inval.split('|', 2);		// inserted separator
			document.chat_form.frm_message.value+=temp_ary[1] + ' ';
			}		// end function set_signal()
		</SCRIPT>
		";

$signals_list = $sig_script ."<SELECT NAME='signals' onFocus = 'clear_to()'; onBlur = 'set_to()'; onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
$signals_list .= "<OPTION VALUE='0' SELECTED>Select signal/code</OPTION>";

$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY 'text' ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
while ($row = stripslashes_deep(mysql_fetch_array($result))) {
//	$signals_list .= "\t<OPTION VALUE='{$row['code']}'>{$row['text']} ({$row['code']})</OPTION>\n";
	$signals_list .=  "\t<OPTION VALUE='{$row['code']}'>{$row['code']}|{$row['text']}</OPTION>\n";		// pipe separator

	}				
$signals_list .= "</SELECT>\n";
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Chat Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="8/24/08" />
	<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css" />	<!-- 3/15/11 -->
<SCRIPT>

	try {
		window.opener.document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		window.opener.document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		window.opener.document.getElementById("script").innerHTML = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	var me = "<?php print $_SESSION['user'];?>";
    var colors = new Array();
    colors[0] = '#DEE3E7';
    colors[1] = '#EFEFEF';
    colors[2] = '#FFFFFF';
    var the_to = false;				// timeout object
    var first = true;
	window.onBlur = clearTimeout (the_to);
  
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

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
	function get_new_colors() {								// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
														// NOT correspond with what browsers actually do...
		var SAFECHARS = "0123456789" +					// Numeric
						"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// guess
						"abcdefghijklmnopqrstuvwxyz" +	// guess again
						"-_.!~*'()";					// RFC2396 Mark characters
		var HEX = "0123456789ABCDEF";
	
		var encoded = "";
		for (var i = 0; i < plaintext.length; i++ ) {
			var ch = plaintext.charAt(i);
		    if (ch == " ") {
			    encoded += "+";				// x-www-urlencoded, rather than %20
			} else if (SAFECHARS.indexOf(ch) != -1) {
			    encoded += ch;
			} else {
			    var charCode = ch.charCodeAt(0);
				if (charCode > 255) {
				    alert( "Unicode Character '"
	                        + ch
	                        + "' cannot be encoded using standard URL encoding.\n" +
					          "(URL encoding only supports 8-bit characters.)\n" +
							  "A space (+) will be substituted." );
					encoded += "+";
				} else {
					encoded += "%";
					encoded += HEX.charAt((charCode >> 4) & 0xF);
					encoded += HEX.charAt(charCode & 0xF);
					}
				}
			} 			// end for(...)
		return encoded;
		};			// end function
	
	function URLDecode(encoded ){   					// Replace + with ' '
	   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
	   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
	   var i = 0;
	   while (i < encoded.length) {
	       var ch = encoded.charAt(i);
		   if (ch == "+") {
		       plaintext += " ";
			   i++;
		   } else if (ch == "%") {
				if (i < (encoded.length-2)
						&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
						&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
					plaintext += unescape( encoded.substr(i,3) );
					i += 3;
				} else {
					alert( '-- invalid escape combination near ...' + encoded.substr(i) );
					plaintext += "%[ERROR]";
					i++;
				}
			} else {
				plaintext += ch;
				i++;
				}
		} 				// end  while (...)
		return plaintext;
		};				// end function URLDecode()

	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
//			alert ("103 " + AJAX.responseText);
			return AJAX.responseText;																				 
			} 
		else {
			alert ("129: failed")
			return false;
			}																						 
		}		// end function sync Ajax(strURL)
	
	var last_msg_id=0;									// initial value at page load

	function rd_chat_msg() {							// read chat messages via ajax xfer - 5/29/10

		var our_max = (first)? 5 : <?php print $list_length ;?>;		// startup limiter
		var params = "last_id=" + last_msg_id + "&max_ct=" + our_max ;
		first = false;													// standard limiter
//		alert("211 " + params);
		sendRequest ('chat_rd.php',handleResult, params);	// 
		}

	function handleResult(req) {									// the called-back phone lookup function
		var payload = req.responseText;		
		if (payload.substring(0,1)=="-") {
			alert ("chat failed -  <?php print __LINE__;?>");
			return false;
			}
		else {
//			alert("220 " + payload);
			var person = document.getElementById("person");
			var lines = payload.split(0xFF, 99) 											// lines FF-delimited
			for (i=0;i<lines.length; i++) {
				var theLine = lines[i].split("\t", 6);										// tab-delimited
				if (theLine.length>1){
					var tr = person.insertRow(-1);
					var the_color = (theLine[0]==me)? colors[2]: colors[theLine[3] % 2];	// highlight if this user
					tr.style.backgroundColor = the_color;
					tr.insertCell(-1).appendChild(document.createTextNode(theLine[1]));		// time
					tr.insertCell(-1).appendChild(document.createTextNode(theLine[0]));		// user
					tr.insertCell(-1).appendChild(document.createTextNode(theLine[2]));		// message
					
<?php	if ($istest) { print "\ntr.insertCell(-1).appendChild(document.createTextNode(theLine[3]));\n"; }?>				
					
					last_msg_id = (theLine[3]>last_msg_id)? theLine[3]:last_msg_id ;
					location.href = "#bottom";				// make input line visible
					}
				}			// end for (i=... )
			}			// end if/else (payload.substring(... )
		trim_list(<?php print $list_length; ?>);		// delete rows
		
		ctr = $('person').rows.length;		// now clear out local-inserted rows
		for (i=ctr-1; i>=0;i--) {
			while (($('person').rows[i]) && ($('person').rows[i].cells[0].innerHTML == "")) {
				$('person').deleteRow(i);
				}
			}
		}		// end function handleResult()

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
<?php if ($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";} ?>
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
			try { xmlhttp = XMLHttpFactories[i](); }
			catch (e) { continue; }
			break;
			}
		return xmlhttp;
		}

	function wr_invite(target) {							// write chat message via ajax xfer
		var url = "chat_invite.php?frm_to=" + target.trim() + "&frm_user=" + document.chat_form.frm_user.value;		// user id or broadcast
		var payload = syncAjax(url);						// send lookup url
		if (payload.substring(0,1)=="-") {					// stringObject.substring(start,stop)
			alert ("chat failed -  <?php print __LINE__;?>");
			set_to();										// set timeout again
			return false;
			}
		else {
			return;
			}				// end if/else (payload.substring(... )
		}		// end function wr invite msg()
		

	function wr_chat_msg(the_Form) {							// write chat message via ajax xfer
		if (the_Form.frm_message.value.trim()=="") {return;}

		var person = document.getElementById("person");		// into table

		var new_tr = person.insertRow(-1);
		new_tr.style.backgroundColor = colors[2];
		new_tr.insertCell(-1).appendChild(document.createTextNode(""));		// empty time
		new_tr.insertCell(-1).appendChild(document.createTextNode("<?php print $_SESSION['user'];?>"));		// user
		new_tr.insertCell(-1).appendChild(document.createTextNode(the_Form.frm_message.value.trim()));		// message

		clear_to();
		var querystr = "?frm_message=" + URLEncode(the_Form.frm_message.value.trim());
		querystr += "&frm_room=" + URLEncode(the_Form.frm_room.value.trim());
		querystr += "&frm_user=" + URLEncode(the_Form.frm_user.value.trim());
		querystr += "&frm_from=" + URLEncode(the_Form.frm_from.value.trim());

		var url = "chat_wr.php" + querystr;					// phone no. or addr string
		var payload = syncAjax(url);						// send lookup url
		if (payload.substring(0,1)=="-") {					// stringObject.substring(start,stop)
			alert ("wr_chat msg failed -  <?php print __LINE__;?>");
			set_to();										// set timeout again
			return false;
			}
		else {
			set_to();										// set timeout again
			the_Form.frm_message.value="";
//			the_Form.frm_message.focus();
			do_focus ()
			}				// end if/else (payload.substring(... )
		}		// end function wr_chat_ msg()

 	function show_hide(the_id) {						// display then hide given id
		$(the_id).style.display='inline';
		setTimeout("$('sent_msg').style.display='none';", 3000);
		}

	function do_focus () {	
		document.chat_form.frm_message.focus();
		}	

	function do_enter(e) {										// enter key submits form
		var keynum;
		var keychar;
		if(window.event)	{keynum = e.keyCode;	} 			// IE
		else if(e.which)	{keynum = e.which;	}				// Mozilla/Opera
		if (keynum==13) {										// allow enter key
			wr_chat_msg(document.forms[0]) ;					// submit to server-side script
			do_focus ()
			}
		else {
			keychar = String.fromCharCode(keynum);
			return keychar;
				}
		} //	end function do_enter(e)

	function announce() {										//end announcement
		wr_chat_msg(document.chat_form);
		}

	function set_to() {										// set timeout
		if (!the_to) {the_to=setTimeout('getMessages(false)', <?php print $cycle; ?>)}
		}
		
	function clear_to() {
		clearTimeout (the_to);
		the_to = false;
		}
		
	function getMessages(ignore){
//		alert(<?php print __LINE__;?>)
		clear_to();
		rd_chat_msg();
		set_to();												// set timeout again
		do_focus ();
		}

	function do_send_inv(in_val) {
		show_hide('sent_msg');
		wr_invite(in_val);
		$('send_butt').style.display='none';
		do_can ();			// hide some buttons and reset select
		}

	function trim_list(ctr) {			// delete oldest rows from display
		ctr = $('person').rows.length;
		while ($('person').rows.length>ctr){
			var main = $('person');
			main.deleteRow(-1);
			}
		}

	function do_can () {
		$('send_butt').style.display='none';
		$('can_butt').style.display='none';
		document.chat_form.chat_invite.options[0].selected = true;
		}		// end function do_can ()

	</SCRIPT>
</HEAD>
<BODY onLoad = "if (!(window.opener)) {window.close();};announce();getMessages(true); set_to(); do_focus();" onUnload="wr_chat_msg(document.chat_form_2); clearTimeout(the_to);"> 
<TABLE ID="person" border="0" width='60%' STYLE = 'margin-left:100px;'>
</TABLE>
<?php
					// who's logged-in?
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		// 1/23/10
					
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`  WHERE `id` <> {$_SESSION['user_id']} AND `expires` >'{$now}' ORDER BY `user`";	// 1/23/10 

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$who = $sep = "";
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
//		$who .=  $sep . $row['user_name'] . " " . ($row['last_in'] - (get_variable('delta_mins')*60)) . $sep;			// 2/3/09
		$who .=  $sep . $row['user'] ;		// 1/23/10
		$sep = ", ";
		}
	if (mysql_affected_rows()==0) { $who = "no others";};
?>
		<DIV  STYLE = 'margin-left:100px;'>
		<FONT CLASS="header">Chat</FONT> <I>(logged-in: <?php print $who; ?>)</I><BR /><BR />
		<FORM METHOD="post" NAME='chat_form' onSubmit="return false;">
		<NOBR>
		<INPUT TYPE="text" NAME="frm_message" SIZE=80 value = "" onFocus = "clear_to()"; onBlur = 'set_to()'; >

		<INPUT TYPE="button" VALUE = "Send" onClick="wr_chat_msg(document.forms[0]);set_to()"  style='margin-left:20px;' >
		<INPUT TYPE="Reset" VALUE = "Reset" style='margin-left:20px;'  onClick="this.form.reset(); document.chat_form.frm_message.value='';" />
		<BR /><NOBR>
<?php print  $signals_list; ?><br />

		<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0'>
		<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $_SESSION['user_id'];?>'>
		<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>'>

		<SPAN ID = 'botton_row' STYLE='margin-left:120px;'>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<B>Invite </B><SELECT NAME='chat_invite' 
				onChange = "$('send_butt').style.display='inline';$('can_butt').style.display='inline';"> 
		<OPTION VALUE="" SELECTED>Select</OPTION>	
		<OPTION VALUE=0>All</OPTION>	

<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` != {$_SESSION['user_id']} ";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		print "\t\t<OPTION VALUE={$row['id']}>{$row['user']}</OPTION>\n";	
		}
	print "\t</SELECT>\n";
?>
		<INPUT ID = 'send_butt' TYPE='button' VALUE = 'Send invite' style='margin-left:10px; display:none' onClick = "do_send_inv(document.chat_form.chat_invite.value);">
		<SPAN ID= 'sent_msg' STYLE = 'margin-left:60px; display:none;'><B>Invitation Sent!</B></span>
		<INPUT ID = 'can_butt' TYPE='button' VALUE = 'Cancel' style='margin-left:10px; display:none' onClick = "$('send_butt').style.display='none';$('can_butt').style.display='none';document.chat_form.chat_invite.options[0].selected = true;">
		<INPUT TYPE="button" VALUE = "Close"  style='margin-left:60px;'onClick = "this.disabled=true; clear_to(); opener.chat_win_close(); self.close()">
		<NOBR></CENTER>
		</SPAN>
		</FORM>
		<FORM METHOD="post" NAME='chat_form_2' onSubmit="return false;">
		<INPUT TYPE="hidden" NAME = "frm_message" VALUE=' has left this chat.'>
		<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0'>
		<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $_SESSION['user_id'];?>'>
		<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>'>
		</FORM>
		<A NAME="bottom"></A>
		</DIV>
</BODY>
</HTML>