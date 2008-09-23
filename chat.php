<?php 
	require_once('./incs/functions.inc.php'); 
	do_login(basename(__FILE__));
	extract ($_GET);
	if (!isset($my_session)) {session_start();}
	extract ($my_session);
//	dump ($my_session);

/*	chat_messages : id message when chat_room_id user_id 	*/

	$hours = (intval(get_variable('chat_time'))>0)? intval(get_variable('chat_time')) : 4;	// force to default
	
	$old = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($hours*60*60)); // n hours ago

	$query  = "DELETE FROM `$GLOBALS[mysql_prefix]chat_messages` WHERE `when`< '" . $old . "'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Chat Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="8/24/08">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT>

try {
	window.opener.document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
	window.opener.document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
	window.opener.document.getElementById("script").innerHTML = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
	}

    var colors = new Array();
    colors[0] = '#DEE3E7';
    colors[1] = '#EFEFEF';
    var the_to = false;				// timeout object
	window.onBlur = clearTimeout (the_to);
  
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
			alert ("57: failed")
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

	var last_msg_id=0;									// initial value at page load

	function rd_chat_msg() {							// read chat messages via ajax xfer
		var querystr = "?last_id=" + last_msg_id;
		var url = "chat_rd.php" + querystr;	
		var payload = syncAjax(url);		
		if (payload.substring(0,1)=="-") {
			alert ("wr_chat msg failed - 120");
			return false;
			}
		else {
			var person = document.getElementById("person");
			var lines = payload.split(0xFF, 99) 											// lines FF-delimited
			for (i=0;i<lines.length; i++) {
				var theLine = lines[i].split("\t", 6);										// tab-delimited
				if (theLine.length>1){
					var tr = person.insertRow(-1);
					tr.style.backgroundColor = colors[theLine[3] % 2];
					tr.insertCell(-1).appendChild(document.createTextNode(theLine[1]));		// time
					tr.insertCell(-1).appendChild(document.createTextNode(theLine[0]));		// user
					tr.insertCell(-1).appendChild(document.createTextNode(theLine[2]));		// message
<?php
			if ($istest) {
					print "\ntr.insertCell(-1).appendChild(document.createTextNode(theLine[3]));\n";
					}
?>					
					last_msg_id = theLine[3];
					}
				}			// end for (i=... )
			}			// end if/else (payload.substring(... )
		}		// end function rd_chat_msg()

	function wr_chat_msg(the_Form) {							// write chat message via ajax xfer
		if (the_Form.frm_message.value.trim()=="") {return;}
		clear_to();
		the_to = false;
		var querystr = "?frm_message=" + URLEncode(the_Form.frm_message.value.trim());
		querystr += "&frm_room=" + URLEncode(the_Form.frm_room.value.trim());
		querystr += "&frm_user=" + URLEncode(the_Form.frm_user.value.trim());
		querystr += "&frm_from=" + URLEncode(the_Form.frm_from.value.trim());

		var url = "chat_wr.php" + querystr;					// phone no. or addr string
		var payload = syncAjax(url);						// send lookup url
		if (payload.substring(0,1)=="-") {					// stringObject.substring(start,stop)
			alert ("wr_chat msg failed - 157");
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

	function do_focus () {		// test for focus() support
//		if (!document.all) { document.chat_form.frm_message.focus();}
//		else {alert("is IE");}
		document.chat_form.frm_message.focus();
		}
	

	function do_enter(e) {										// enter key submits form
		var keynum;
		var keychar;
		if(window.event)	{keynum = e.keyCode;	} 			// IE
		else if(e.which)	{keynum = e.which;	}				// Mozilla/Opera
	
		if (keynum==13) {										// allow enter key
			wr_chat_msg(document.forms[0]) ;					// submit to server-side script
//			document.chat_form.frm_message.focus();
			do_focus ()
			}
		else {
			keychar = String.fromCharCode(keynum);
			return keychar;
				}
		} //	end function do_enter(e)

	function announce() {										//end announcement
//		for (i=0;i<document.forms.length;i++) {
//			alert (document.forms[i].name);
//			}
		
		wr_chat_msg(document.forms[0]);
		}

	function set_to() {											// set timeout
		if (!the_to) {the_to=setTimeout('getMessages()', 5000)}
		}
		
	function clear_to() {
		clearTimeout (the_to);
		the_to = false;
		}
		
	function getMessages(){
		clear_to();
		rd_chat_msg();
		set_to();												// set timeout again
//		document.chat_form.frm_message.focus();
		do_focus ();
		}

</SCRIPT>
</HEAD>
<BODY onLoad = "announce();getMessages(); set_to(); do_focus();" onunload="wr_chat_msg(document.chat_form_2); clearTimeout(the_to);"> 
<TABLE ID="person" border="0" width='80%'>
</TABLE>
		<FONT CLASS="header">Chat</FONT><BR /><BR />
		<FORM METHOD="post" NAME='chat_form' onSubmit="return false;">
		<INPUT TYPE="text" NAME="frm_message" SIZE=80 onkeypress="return do_enter(event)" VALUE=' has joined this chat.'>
		<CENTER><BR />
		<INPUT TYPE="button" VALUE = "Send" onClick="wr_chat_msg(document.forms[0]);" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
		if ($istest) {
			print "<INPUT TYPE=\"button\" VALUE = \"Get\" onClick=\"rd_chat_msg();\" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
			}
?>		
		<INPUT TYPE="button" VALUE = "Close" onClick = "this.disabled=true; self.close()"></CENTER>
		<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0'>
		<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $my_session['user_id'];?>'>
		<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>'>
		</FORM>
		<FORM METHOD="post" NAME='chat_form_2' onSubmit="return false;">
		<INPUT TYPE="hidden" NAME = "frm_message" VALUE=' has left this chat.'>
		<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0'>
		<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $my_session['user_id'];?>'>
		<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>'>
		</FORM>
		<A NAME="bottom"></A>
</BODY>
</HTML>