<?php

//	CONSTANT settings specific to this script
define("TIME_LIMIT", 8);				// time in hours for closed assigns to be visible
// define("REPORT_COMPLETE", TRUE);		// Do (or if FALSE, don't) report update completion
// end of file-specific constants

/*
5/23/08	fix to status_val field name
6/4/08	Deletion logic revised to remove  timed-based inactive and add explicit deletions
6/26/08	added $doTick to assign view/edit ticket functions by priv level	
8/24/08 added htmlentities function to TITLE strings
9/17/08 disallow guest edit to unit status
9/27/08 removed dead code relating to $unit_scr
9/28/08	converted TD hide/show to SPAN, to improve col alignment
10/9/08	show unit status dropdown only one time
11/7/08 incident strikethrough corrections
11/8/08 added checkboxes; correction to unit status update
1/12/09 center added for call frame, accomodate frame operation, do status update in-frame, dollar function added
1/15/09 update assigns with unit_id, added ajax functions, added script-specific CONSTANTs
1/16/08 removed 'delta' in favor of INTERVAL-based SQL, added mailit() for new dispatches
1/17/09 incident strike-through corrections
1/29/09 fixed sql stmt, TBD default comment added
2/12/09 fix select list order
2/18/09 added persistence to 'hide cleared'
2/19/09 'do all clicked buttons' added
2/20/09 show/hide changed to radio button
2/28/09 fixes to CB update
3/1/09 restrict guest updates
3/9/09 bypass email if no addr, set 'dispatched' time
3/25/09 'mailed', 'fetch_assoc' for performance, 'mailed' removed
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php'); 

if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
sleep(1);		// wait for possible logout to complete	
$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds
$sess_key = get_sess_key();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

if (!mysql_affected_rows()==1) {			//logged-in?				1/13/09
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Assigns Wait for login</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
</HEAD>
<BODY>
<CENTER><BR><SPAN ID='start' onClick = "Javascript: self.location.href = 'assigns.php';"><H3>Call board waiting for login</H3></span>
</BODY>
</HTML>

<?php
		}				// end if (!mysql_affected_rows())
	else {
	
	upd_lastin();				// update session time
		
	extract($_GET);
	extract($_POST);
	$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
	$func = (empty($_POST))? "list" : $_POST['func'];
	
?> 
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - Assignments Module</TITLE>
		<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8"/>
		<META HTTP-EQUIV="Expires" 				CONTENT="0"/>
		<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
		<META HTTP-EQUIV="Script-date" 			CONTENT="8/24/08">
		<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT>
	//alert (window.opener.parent.frames["upper"].document.getElementById("whom").innerHTML);
	//if ((!window.opener) || (window.opener.parent.frames["upper"].document.getElementById("whom").innerHTML == "not"))
	//		{self.location.href = 'index.php';}				// must run only as window, with user logged in
	var myuser = "<?php print isset($my_session)?$my_session['user_name']: "not";?>";
	var mylevel = "<?php print isset($my_session)?get_level_text($my_session['level']): "na";?>";
	var myscript = "<?php print isset($my_session)? LessExtension(basename( __FILE__)): "login";?>";
	
	if (!(window.opener==null)){					// 1/12/09
		try {
			window.opener.parent.frames["upper"].$("whom").innerHTML = 	myuser;
			window.opener.parent.frames["upper"].$("level").innerHTML =	mylevel;
			window.opener.parent.frames["upper"].$("script").innerHTML = myscript;
			}
		catch(e) {
			}
		}			
	function $() {									// 1/12/09
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
	
	/* function $() Sample Usage:
	var obj1 = document.getElementById('element1');
	var obj2 = document.getElementById('element2');
	function alertElements() {
	  var i;
	  var elements = $('a','b','c',obj1,obj2,'d','e');
	  for ( i=0;i
	*/  
		
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	function sendRequest(url,callback,postData) {		// ajax function set - 1/15/09
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
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
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
	
		function editA(id) {							// edit assigns
			document.nav_form.frm_id.value=id;
<?php
			print "\t\tdocument.nav_form.func.value=";	// guest priv's = 'read-only'
			print is_guest()? "'view';" : "'edit';";
?>	
			document.nav_form.submit();
			}
	
		function viewT(id) {			// view ticket
			document.T_nav_form.id.value=id;
			document.T_nav_form.action='main.php';
			document.T_nav_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function editT(id) {			// edit ticket
			document.T_nav_form.id.value=id;
			document.T_nav_form.action='edit.php';
			document.T_nav_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function viewU(id) {			// view unit
			document.U_nav_form.id.value=id;
			document.U_nav_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function editU(id) {			// edit unit
			document.U_edit_form.id.value=id;
			document.U_edit_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
	</SCRIPT>
	
<?php 								// id, as_of, status_id, ticket_id, unit_id, comment, user_id
	switch ($func) {					// 300 - 730 =======================================================
	
		case 'add': 					//  === { === first build JS array of existing assigns for dupe prevention
		print "\n<SCRIPT>\n";
		print "\t\tassigns = new Array();\n";
		
		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of FROM `$GLOBALS[mysql_prefix]assigns` ORDER BY `as_of` DESC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row = stripslashes_deep(mysql_fetch_array($result))) {
			print "\t\tassigns['" .$row['ticket_id'] .":" . $row['responder_id'] . "']=true;\n";	// build assoc array of ticket:unit pairs
			}
?>		
		function validate_ad(theForm) {
			var errmsg="";
			if (theForm.frm_ticket_id.value == "")	{errmsg+= "\tSelect Incident\n";}
			if (theForm.frm_unit_id.value == "")	{errmsg+= "\tSelect Unit\n";}
			if (theForm.frm_status_id.value == "")	{errmsg+= "\tSelect Status\n";}
			if (theForm.frm_comments.value == "")	{errmsg+= "\tComments required\n";}
			if (assigns[theForm.frm_ticket_id.value + ":" +theForm.frm_unit_id.value]) {
										errmsg+= "\tDuplicates existing assignment\n";}
			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
				theForm.submit();
				}
			}				// end function vali date(theForm)
	
		function reSizeScr() {
			window.resizeTo(800,300);		
			}
	
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "reSizeScr()"><CENTER>		<!-- 1/12/09 -->
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="add_Form" onSubmit="return validate_ad(document.add_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
			<TR CLASS="even"><th colspan=2 ALIGN="center">Assign Unit to Incident</th></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Incident:</TD>
				<TD><SELECT NAME="frm_ticket_id">
					<OPTION VALUE= '' selected>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = " . $GLOBALS['STATUS_OPEN']. " ORDER BY `scope`"; 
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
					while ($row = mysql_fetch_array($result))  {
						print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['scope'] . "</OPTION>\n";		
						}
?>
					</SELECT>	
				</TD></TR>
			<TR CLASS="even" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Unit:</TD>
				<TD><SELECT name="frm_unit_id" onChange = "document.add_Form.frm_log_it.value='1'" >
					<OPTION value= '' selected>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name` ASC";		// 2/12/09   
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
					while ($row = mysql_fetch_array($result))  {
						print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";		
						}
?>
			</SELECT></TD></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">&nbsp;&nbsp;Unit Status:</TD>
				<TD><SELECT name="frm_status_id"  onChange = "document.add_Form.frm_log_it.value='1'"> 
					<OPTION VALUE= '' SELECTED>Select</OPTION>
	
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$the_grp = strval(rand());			//  force initial OPTGROUP value
			$i = 0;
			while ($row = stripslashes_deep(mysql_fetch_array($result_st))) {
				if ($the_grp != $row['group']) {
					print ($i == 0)? "": "\t</OPTGROUP>\n";
					$the_grp = $row['group'];
					print "\t<OPTGROUP LABEL='$the_grp'>\n";
					}
				print "\t<OPTION VALUE=' {$row['id']}'  CLASS='{$row['group']}' title='{$row['description']}'> {$row['status_val']} </OPTION>\n";		// 6/23/08
	//			print "\t<OPTION VALUE=" . $row['id'] . ">" . $row['status_val'] . "</OPTION>\n";
				$i++;
				}		// end while()
			print "\n</OPTGROUP>\n";
			unset($result_st);
?>
					</SELECT>	
				</TD></TR>
			<TR CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Comments:</TD>
				<TD><INPUT MAXLENGTH="64" SIZE="64" NAME="frm_comments" VALUE="TBD" TYPE="text" onFocus="Javascript:if (this.value=='TBD') {this.value='';};"></TD></TR> <!-- 1/29/09 -->
			
			<TR CLASS="odd" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<BR>
				<INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="button" VALUE="Reset" onclick="Javascript: this.form.reset();">&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="submit" VALUE="           Submit           " name="sub_but" >  
				</TD></TR>
			 </tbody></table>
			<INPUT TYPE='hidden' NAME='frm_by_id'	VALUE= "<?php print $my_session['user_id'];?>">
			<INPUT TYPE='hidden' NAME='func' 		VALUE= 'add_db'>
			<INPUT TYPE='hidden' NAME='frm_log_it' 	VALUE=''/>
			</FORM>
<?php	
			break;				// end case 'add' === } ===
			

				//	id, as_of, status_id, ticket_id, unit_id, comment, user_id
		case 'add_db' : 		// === {  ==========================================================================================
			 	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 		// 3/9/09
				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `dispatched`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`)
								VALUES (%s,%s,%s,%s,%s,%s,%s)",
									quote_smart($now),
									quote_smart($now),
									quote_smart($frm_status_id),
									quote_smart($frm_ticket_id),
									quote_smart($frm_unit_id),
									quote_smart($frm_comments),
									quote_smart($frm_by_id));
	
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
									// apply status update to unit status
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($frm_status_id) . " WHERE `id` = " .quote_smart($frm_unit_id)  ." LIMIT 1";	// 11/8/08
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	
				do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_unit_id, $frm_status_id);
				
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " .quote_smart($frm_unit_id)  ." LIMIT 1";	// 1/29/09

				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_array($result));
				$to_str = $row['contact_via'];
				
				if (is_email($to_str)) {		// 3/9/09
	
					$text = "";
					$the_msg =(is_email($to_str))? mail_it ($to_str, $text, $frm_ticket_id, $text_sel=3, TRUE): "";
					$temp = (explode("\n", $text));
					$lines = count($temp);
	
					$the_unit = $row['name'];
					unset($row);
				
?>
		<SCRIPT>
		function handleResult(req) {				// the called-back function
<?php print	($istest)? "\n\t\talert(360)\n" : "" ?>
			}

		function send_it() {
			var url = "do_send.php";		// ($to_str, $subject_str, $text_str )

			var the_to = escape("<?php print $to_str; ?>");
			var the_subj = escape("Subject");
			var the_msg = escape(document.add_cont_form.frm_text.value);		// the variables

			var postData = "to_str=" + the_to +"&subject_str=" + the_subj + "&text_str=" + the_msg; // the post string
			sendRequest(url,handleResult,postData) ;
			}		// end function send_it()			

		function dummy() {		
			}
		
		</SCRIPT>
		</HEAD>
	<BODY><CENTER>		<!-- 1/12/09 -->
		<CENTER><BR><BR><H3>New Dispatch: <?php print $the_unit; ?></H3>
		<I>edit message to suit</I><BR><BR>
		<FORM NAME='add_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<TEXTAREA NAME="frm_text" COLS=60 ROWS=<?php print $lines+2; ?>><?php print mail_it ($to_str, "New", $frm_ticket_id, 3, TRUE);?></TEXTAREA> 
		<P>		
		<INPUT TYPE='button' VALUE='Send message' onClick = "send_it(); setTimeout('dummy()',1000); document.add_cont_form.submit()">&nbsp;&nbsp;
		<INPUT TYPE='button' VALUE='Do NOT send' onClick = "document.add_cont_form.submit()">
		<INPUT TYPE='hidden' NAME='func' VALUE='list'>
		</FORM></BODY></HTML>
<?php	
			break;				// end case 'add_db' 
			}		// end case 'add_db'  === } ===
		
		case 'list' :			// 394 - 880  { ========================================================
		
	// 		$unit_scr = "http://" . $_SERVER["SERVER_ADDR"] . ":". $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	//		$temparr = explode ("/", $unit_scr);
	//		$temparr[count($temparr)-1] = "units.php";
	//		$unit_scr=implode ("/", $temparr);
	$guest = is_guest();
																				
	if ((array_key_exists("chg_hide", $_POST)) && ($_POST['chg_hide']==1)) {			// change persistence value - 2/18/09
		$temp = $_POST['hide_cl'];
		$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `f2` ='$temp' WHERE `sess_id`='$sess_key' LIMIT 1";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
		$my_session = get_mysession();			// refresh session array
		}
?>
	<SCRIPT>
	
		function reSizeScr() {
			var lines = document.can_Form.lines.value;
			window.resizeTo(900,((lines * 23)+240));		// derived via trial/error (more of the latter, mostly)
			}
		  
		function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
															// NOT correspond with what browsers actually do...
			var SAFECHARS = "0123456789" +					// Numeric
							"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
							"abcdefghijklmnopqrstuvwxyz" +	// guess
							"-_.!*'()";					// RFC2396 Mark characters
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
	//			alert ("332 " + AJAX.responseText);
				return AJAX.responseText;																				 
				} 
			else {
				alert ("457: failed");
				return false;
				}																						 
			}		// end function sync Ajax(strURL)
		

		var starting = false;						// 2/15/09
	
		function do_mail_win(addrs, ticket_id) {	// 3/27/09
			if(starting) {return;}					// dbl-click catcher
//			alert("503 " +addrs);
			starting=true;	
			window.blur();							// blur current window
			var url = "mail_edit.php?ticket_id=" + ticket_id + "&addrs=" + addrs + "&text=";	// no text
			var win_name = "mail_edit";
			newwindow_mail=window.open(url, win_name,  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100, top=300,screenX=100,screenY=300");
			if (isNull(win_name)) {
				alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
				return;
				}

			newwindow_mail.focus();
			starting = false;
			}		// end function do mail_win()		

		var button_live = false;
		function show_but(id) {
			if (button_live) {
				alert ("Please complete button action.");
				return false;
				}
			else {
				var theid = "TD"+id;
				elem = $(theid);
				elem.style.display = "block";
				button_live = true;
				return false;
				}
			}		// end function show_but(id)
	
		function hide_but(id) {
			var theid = "TD"+id;
			if(!$(theid)) {return false;}		// 9/17/08
			elem = $(theid);
			elem.style.display = "none";
			button_live = false;
			return false;
			}
	
		var last_form_no;
		function to_server(the_Form) {							// write unit status data via ajax xfer
			var querystr = "?frm_ticket_id=" + URLEncode(the_Form.frm_ticket_id.value.trim());
			querystr += "&frm_responder_id=" + URLEncode(the_Form.frm_responder_id.value.trim());
			querystr += "&frm_status_id=" + URLEncode(the_Form.frm_status_id.value.trim());
		
			var url = "as_up_un_status.php" + querystr;			// 
			var payload = syncAjax(url);						// 
			if (payload.substring(0,1)=="-") {	
				alert ("513: msg failed ");
				return false;
				}
			else {
	 			var bull_str = "<B>&bull;</B> ";
				var form_no = the_Form.name.substring(1);
				hide_but(form_no);								// hide the buttons
	
				if (last_form_no) {
					var elem = "myDate" + last_form_no;
					var temp = $(elem).innerHTML;
					$(elem).innerHTML = temp.substr(9);		// drop the bullet
					}
				var elem = "myDate" + form_no;
				$(elem).innerHTML = bull_str + payload;
				last_form_no = form_no;
				}				// end if/else (payload.substring(... )
			}		// end function to_server()
	
		
	function do_res(the_form_no) {
		document.forms[the_form_no].reset();
		}
		
	</SCRIPT>	
	</HEAD>
	<BODY onLoad = "reSizeScr()";><!-- 516 -->
	<CENTER>
<?php
		function get_un_stat_sel($s_id, $b_id) {					// returns select list as string
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
 			$dis = (is_guest())? " DISABLED": "";								// 9/17/08
			$the_grp = strval(rand());			//  force initial OPTGROUP value
			$i = 0;
			$outstr = "\n\t\t<SELECT name='frm_status_id'  onFocus = 'show_but($b_id)' $dis >\n";
			while ($row = stripslashes_deep(mysql_fetch_array($result_st))) {
				if ($the_grp != $row['group']) {
					$outstr .= ($i == 0)? "": "\t</OPTGROUP>\n";
					$the_grp = $row['group'];
					$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>\n";
					}
				$sel = ($row['id']==$s_id)? " SELECTED": "";
				$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel .">" . $row['status_val'] . "</OPTION>\n";
				$i++;
				}		// end while()
			$outstr .= "\t\t</OPTGROUP>\n\t\t</SELECT>\n";
			return $outstr;
			unset($result_st);
			}

		$priorities = array("","severity_medium","severity_high" );

		print "<TABLE BORDER=1 ALIGN='center' WIDTH='100%'  cellspacing = 1 CELLPADDING = 2 ID='call_board' STYLE='display:block'>";
		print "<TR CLASS='even'><TD COLSPAN=15 ALIGN = 'center'><B>Call Board</B>&nbsp;&nbsp;&nbsp;&nbsp;<FONT SIZE='-3'><I> (mouseover/click for details)</I></FONT></TD></TR>\n";

		$status_vals_ar = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_array($result))) {
			$sep = (empty($row['description']))? "": ":";
			$status_vals_ar[$row['id']] = $row['status_val'] . $sep . $row['description'] ;
			}
		$temp = TIME_LIMIT;				// note CONSTANT use 1/16/08

		switch ($my_session['f2']) {		// persistence flags 2/18/09
			case "":						// default, show
			case " ":						// 
			case "s":						
//				$hide_sql = " OR `clear`> (NOW() - INTERVAL $temp HOUR) ";
				$cwi = get_variable('closed_interval');					// closed window interval in hours - 3/3/09
				$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));

				$hide_sql = " OR `clear`>= '$time_back' ";

				$butn_txt = "Hide ";
				$butn_val = "h";
			    break;
			case "h":						// hide
				$hide_sql = "";
				$butn_txt = "Show ";
				$butn_val = "s";
			    break;
			default:
			    echo "error" . __LINE__ . "\n";
			}
			
		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , `$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`, `t`.`status` AS `thestatus`,
			`r`.`id` AS `theunitid`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
				WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'    $hide_sql 
			ORDER BY `theticket` ASC";																		// 1/16/08

//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$i = 1;	
		if (mysql_affected_rows()>0) {
			$doUnit = (is_guest())? "viewU" : "editU";
			$doTick = (is_guest())? "viewT" : "editT";				// 06/26/08
			$now = time() - (get_variable('delta_mins')*60);
			$items = mysql_affected_rows();
			$header = "<TR CLASS='even'>";
			
			$header .= "<TD COLSPAN=2 ALIGN='center' CLASS='emph'>Incident</TD>";		// 9/27/08
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD COLSPAN=3 ALIGN='center' CLASS='emph'>Unit</TD>";			// 3/27/09
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD COLSPAN=8 ALIGN='center' CLASS='emph'>Dispatch</TD>";
			$header .= "</TR>\n";

			$header .= "<TR CLASS='odd'>";
			$header .= "<TD ALIGN='center' CLASS='emph'>Name</TD><TD ALIGN='center'>Addr</TD>";
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD ALIGN='center' CLASS='emph'>Name</TD><TD ALIGN='center'>&nbsp;<IMG SRC='mail.png'></TD><TD COLSPAN=2  ALIGN='left' >&nbsp;&nbsp;&nbsp;Status</TD>";		// 9/27/08
//			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD ALIGN='center' CLASS='emph'>As of</TD><TD ALIGN='center'>By</TD><TD ALIGN='center'>Comment</TD><TD TITLE= 'Dispatched' ALIGN='center'>D</TD>
				<TD TITLE= 'Responding' ALIGN='center'>R</TD><TD TITLE= 'On scene' ALIGN='center'>O</TD><TD ALIGN='center'>Clear</TD><TD></TD>";		// 1/12/09
			$header .= "</TR>\n";

			$dis = $guest? " DISABLED ": "";				// 3/1/09

			$unit_ids = array();
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// 3/25/09
					if ($i == 1) {print $header;}
					$theClass = ($row['severity']=='')? "":$priorities[$row['severity']];
					print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>\n";
					print "<FORM NAME='F$i' METHOD='get' ACTION='' $dis >\n";

// 	 INCIDENTS	2 cols
					$in_strike = 	($row['thestatus']== $GLOBALS['STATUS_CLOSED'])? "<STRIKE>": "";					// 11/7/08
					$in_strikend = 	($row['thestatus']== $GLOBALS['STATUS_CLOSED'])? "</STRIKE>": "";

					print "\t<TD onClick = $doTick('" . $row['ticket_id'] . "') CLASS='$theClass' TITLE= '" . $row['ticket_id'] .":" . htmlentities ($row['theticket'], ENT_QUOTES) . "' ALIGN='left'>" . $in_strike . shorten($row['theticket'], 16) . $in_strikend . "</TD>\n";		// call 8/24/08
					$address = (empty($row['street']))? "" : $row['street'] . ", ";
					$address .= $row['city'];
					print "\t<TD onClick = $doTick('" . $row['ticket_id'] . "') CLASS='$theClass' TITLE='". htmlentities($address, ENT_QUOTES) ."' ALIGN='left'>" .  $in_strike . shorten($address, 16) .  $in_strikend .	"</TD>\n";		// address 8/24/08, 1/17/09
// 
					print "\t<TD></TD>\n";				// 9/28/08

//  UNITS			3 col's
					if (is_date($row['clear'])) {							// 6/26/08
						$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
						}
					else {
						$strike = $strikend = "";
						}			
 
					if (!($row['responder_id']==0)) {
						print "\t<TD onClick = $doUnit('" . $row['responder_id'] . "') TITLE = '" . htmlentities ($row['theunit'], ENT_QUOTES) . "' ALIGN='left'>" .  shorten($row['theunit'], 14) . "</TD>\n";							// unit 8/24/08, 1/17/09
						print "<TD  CLASS='mylink' onmouseover =\"$('c{$i}').style.visibility='visible';\" onmouseout = \"$('c{$i}').style.visibility='hidden'; \" ALIGN='center'>
							<SPAN id=\"c{$i}\" style=\"visibility: hidden\">
							&nbsp;<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit ". htmlentities ($row['theunit'], ENT_QUOTES) . 
							"' onclick = \"do_mail_win(F{$i}.frm_contact_via.value, {$row['ticket_id']}); \"> 
							</SPAN></TD>";
						if (!in_array ($row['responder_id'], $unit_ids)) {				// 10/9/08
							$unit_st_val = (array_key_exists($row['un_status_id'], $status_vals_ar))? $status_vals_ar[$row["un_status_id"]]: "";

							print "\t<TD TITLE= '$unit_st_val'>" .  get_un_stat_sel($row['un_status_id'], $i) . "</TD>\n";						// status
//							print "\t<TD ID=TD$i STYLE='display:none'>\n\t<SPAN ID='tbd' STYLE='display:none'><INPUT TYPE='button' VALUE='Go' style = 'height: 1.5em' onClick=\"to_server(F$i);\">\n";
							
						print "\t<TD>\n\t<SPAN ID=TD$i STYLE='display:none'><INPUT TYPE='button' VALUE='Go' style = 'height: 1.5em' onClick=\"to_server(F$i); window.opener.parent.frames['main'].location.reload();\">\n"; 		// 9/28/08
							print "\t<INPUT TYPE='button' VALUE='Cancel'  style = 'height: 1.5em;' onClick=\"document.F$i.reset();hide_but($i)\"></SPAN></TD>\n";
							array_push($unit_ids, $row['responder_id']);
							}
						else {
							print "<TD COLSPAN=2></TD>";
							}
						}
					else {
						print "\t<TD COLSPAN=3  CLASS='$theClass' onClick = editA(" . $row['assign_id'] . ") ID='myDate$i' ALIGN='left'><B>&nbsp;&nbsp;&nbsp;&nbsp;NA</b></TD>\n";	
						}
// 
//					print "\t<TD></TD>\n";				// 9/28/08
//   ASSIGNS	8 cols	- 1/12/09
					print "\t<TD CLASS='$theClass' onClick = editA(" . $row['assign_id'] . "); ID='myDate$i' ALIGN='right' TITLE='" . date("n/j `y H:i", $row['as_of']) ." '>" .  $strike . date("H:i", $row['as_of'])  .  $strikend . "</TD>\n";						// as of 
					print "\t<TD CLASS='$theClass' onClick = editA(" . $row['assign_id'] . "); TITLE = '" . $row['theuser'] . "'>" .  $strike . shorten ($row['theuser'], 8) .  $strikend . "</TD>\n";						// user  
					print "\t<TD CLASS='$theClass' onClick = editA(" . $row['assign_id'] . "); TITLE='" . $row['assign_id'] . ": " . shorten ($row['assign_comments'], 72) . "'>" . $strike .  shorten ($row['assign_comments'], 14) . $strikend .  "</TD>\n";				// comment

					$is_cd = (is_date($row['dispatched']))? " CHECKED DISABLED": " onClick = \"$('B$i').style.display = ''; $('btn_do_all').style.display='';\"";
					print "\t<TD CLASS='$theClass' TITLE= 'Dispatched'><INPUT TYPE='checkbox' NAME='frm_dispatched' $is_cd ></TD>\n"; 
					
					$is_cd = (is_date($row['responding']))? " CHECKED DISABLED": " onClick = \"$('B$i').style.display = ''; $('btn_do_all').style.display='';\"";
					print "\t<TD CLASS='$theClass' TITLE= 'Responding'><INPUT TYPE='checkbox' NAME='frm_responding' $is_cd ></TD>\n"; 
					
					$is_cd = (is_date($row['on_scene']))? " CHECKED DISABLED": " onClick = \"$('B$i').style.display = ''; $('btn_do_all').style.display='';\"";
					print "\t<TD CLASS='$theClass' TITLE= 'On scene'><INPUT TYPE='checkbox' NAME='frm_on_scene' $is_cd ></TD>\n"; // note names!

					print "\t<TD CLASS='$theClass' TITLE= 'Clear'>";
					if (is_date($row['clear'])) {
//						dump($row['clear']);
						print "<NOBR>". ezDate($row['clear']) . "</NOBR></TD>\n";		//
						}
					else {
						print "<INPUT TYPE='checkbox' NAME='frm_clear' onClick = \"$('B$i').style.display = ''; $('btn_do_all').style.display='';\"></TD>\n";
						}
					
					print "\t<TD ID='B$i' STYLE='display:none'>";
//					print "<INPUT TYPE='button' VALUE='Go'  onClick = \"do_sub($i-1, 'B$i');\"><INPUT TYPE='reset' VALUE='Cancel' onClick = '$(\"B$i\").style.display = \"none\"; do_res( $i-1);'></TD>\n";
					print "<INPUT TYPE='reset' VALUE='Reset' onClick = '$(\"B$i\").style.display = \"none\"; do_res( $i-1);'></TD>\n";
//	
					print "\t<INPUT TYPE='hidden' NAME='frm_contact_via' VALUE='" . $row['contact_via'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_responder_id' VALUE='" . $row['responder_id'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_ticket_id' VALUE='" . $row['ticket_id'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_assign_id' VALUE='" . $row['assign_id'] . "'>\n";		// 1/12/09
//					print "\t<INPUT TYPE='hidden' NAME='frm_mailed' VALUE='" . $row['mailed'] . "'>\n";				// 3/25/09
					print "</FORM>\n</TR>\n";
					$i++;			 
				}		// end while($row ...)
				$lines = $i;
			}		// end if (mysql_affected_rows()>0) 
		$lines = $i;

		if ($i>1) {
			print "<TR CLASS='" . $evenodd[($i+1)%2] . "'><TD COLSPAN=99 ALIGN='center'>";
			print "<FONT SIZE='-2'><I>Incident severity:&nbsp;&nbsp;&nbsp;&nbsp;<span CLASS='text_black'><FONT SIZE='-2'>Normal</span>&nbsp;&nbsp;&nbsp;&nbsp; <span CLASS='severity_medium'><FONT SIZE='-2'>Medium</span>&nbsp;&nbsp;&nbsp;&nbsp; <span CLASS='severity_high'><FONT SIZE='-2'>High</span></I></FONT>\n";
			print "</TD></TR>\n";				
			}				
		else {
			print "<TR><TH COLSPAN=99>&nbsp;</TH></TR>\n";
			print "<TR><TH COLSPAN=99><BR />No Current Call Assignments<BR /></TH></TR>\n";
			}
		print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>&nbsp;<TD COLSPAN=99 ALIGN='center'>";
		if (!is_guest()) {																		// 9/17/08
			print "<INPUT TYPE='button' VALUE = 'Add' onClick = \"document.nav_form.func.value='add'; document.nav_form.submit()\">";
			print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
			}
		$dis = $guest? " DISABLED ": "";
		print "<INPUT TYPE='button' VALUE = 'Close' onClick = 'self.close()'/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n"; // 2/18/09
		print "<INPUT TYPE='button' $dis VALUE='Apply all' ID = 'btn_do_all' onClick=\"do_all();
			$('done_id').style.visibility='visible';  
			setTimeout('$(\'done_id\').style.visibility=\'hidden\';', 2000);\"
			style='display: none;'/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		print "<SPAN ID = 'done_id' CLASS = 'emph' STYLE='visibility:hidden'><B>&nbsp;Done!&nbsp;</B></SPAN>";
		switch ($my_session['f2']) {
			case "":
			case " ":
			case "s":
				$checkeds = " CHECKED ";
				$checkedh = "";
			    break;
			case "h":
				$checkeds = "";
				$checkedh = " CHECKED ";
				break;
			default:
			    echo "error" . __LINE__ . "<BR />";;
				}
		
		print "<BR />Cleared:&nbsp;&nbsp;&nbsp;&nbsp;Show&raquo;
			<INPUT NAME='frm_sorh' TYPE='radio' value='s' $checkeds onChange = 'document.nav_form.chg_hide.value=1;do_hors(\"s\")'/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hide&raquo;
			<INPUT NAME='frm_sorh' TYPE='radio' value='h' $checkedh onChange = 'document.nav_form.chg_hide.value=1;do_hors(\"h\")'/>";
		print "</TD></TR>\n";
		print "</TABLE>";		
?>
<SCRIPT>

	function do_hors (str) {		// set hide/show cleared via reload - 2/20/09
		document.nav_form.hide_cl.value=str;	
		document.nav_form.chg_hide.value=1;	
		document.nav_form.func.value='list';
		document.nav_form.submit();				
		}

		var announce;					// set = false for group update $('btn_do_all').style.visibility

		function handleResult(req) {			// the called-back function
			if (announce) {alert('Update complete (no e-mail sent)');}
			}			// end function handleResult(
	
	function do_sub(the_form, the_button) {				// form submitted	1/20/09, 2/28/09
		$(the_button).style.display = "none";			// hide the button

		var vals = sep = "";
		for (j=0; j<document.forms[the_form].elements.length; j++) {

			if (document.forms[the_form].elements[j].type == "checkbox") {
				if ((!(document.forms[the_form].elements[j].disabled) && (document.forms[the_form].elements[j].checked))) {
					document.forms[the_form].elements[j].disabled = true;
					vals+=sep + document.forms[the_form].elements[j].name;					
					sep = "%";				// safe char as separator
					}
				}

			}			// end for (j ... )
		var params = "frm_id="+ document.forms[the_form].frm_assign_id.value;				// 1/20/09
		params += "&frm_tick="+document.forms[the_form].frm_ticket_id.value;
		params += "&frm_unit="+document.forms[the_form].frm_responder_id.value;
		params += "&frm_vals="+ vals;
		sendRequest ('assigns_t.php',handleResult, params);			// does the work
		}			// end function do_sub()

	var ary_addrs = [];								// key = incident id, value = pipe-sep'd emails			
	var the_ticket_id = "";

	function do_this_form(the_index) {				// call ajax function for each clicked button 
		the_val = parseInt(the_index)+1;
		the_button = "B"+the_val;
		do_sub(the_index, the_button);				// 
		if (!(document.forms[the_index].frm_contact_via.value == "")) {
			the_ticket_id = document.forms[the_index].frm_ticket_id.value;
			if (the_ticket_id in ary_addrs) {
				ary_addrs[the_ticket_id] += "|" + document.forms[the_index].frm_contact_via.value; 		// append
				}
			else {
				ary_addrs[the_ticket_id] = document.forms[the_index].frm_contact_via.value;				// else push
				}
			}
		}				// end function do_this_form()

	function do_all() {										// 2/19/09
		for (i=0; i< document.forms.length; i++) {			// look at each form
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_dispatched.disabled ) && (document.forms[i].frm_dispatched.checked)) {do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_responding.disabled ) && (document.forms[i].frm_responding.checked)) {do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_on_scene.disabled ) && (document.forms[i].frm_on_scene.checked)) {do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_clear) && (!document.forms[i].frm_clear.disabled ) && (document.forms[i].frm_clear.checked)) {do_this_form(i);}
			}			
		}		// end function do all()


</SCRIPT>
<?php
		break;				// end case 'list' ==== } =======
	
	case 'view' :			// read-only ====== {  ====================================================================
?>
		<SCRIPT>
		function reSizeScr() {
			window.resizeTo(800,300);		
			}
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "reSizeScr()"><CENTER>		<!-- 1/12/09 -->
<?php	
														// if (!empty($row['clear'])) ??????
			$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, UNIX_TIMESTAMP(`dispatched`) AS `dispatched`, UNIX_TIMESTAMP(`responding`) AS `responding`, UNIX_TIMESTAMP(`on_scene`) AS `on_scene`, UNIX_TIMESTAMP(`clear`) AS `clear`,  `assigns`.`id` AS `assign_id` , `assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
				`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
				WHERE `assigns`.`id` = $frm_id LIMIT 1";
	
			$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result));
	
?>
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
			<TR CLASS="even"><TD colspan=2 ALIGN="center">Call Assignment (#<?php print $asgn_row['id']; ?>)</TD></TR>
			<TR CLASS="odd" VALIGN="baseline" onClick = "viewT('<?php print $asgn_row['ticket_id'];?>')">
				<TD CLASS="td_label" ALIGN="right">&raquo; <U>Incident</U>:</TD><TD>
<?php
			print $asgn_row['scope'] . "</TD></TR>\n";		
	
			if (!$asgn_row['responder_id']=="0"){
				$unit_name = $asgn_row['name'];
				$unit_link = " onClick = \"viewU('" . $asgn_row['responder_id'] . "')\";";
				$highlight = "&raquo;";
				}
			else {
				$highlight = "";
				$unit_name = "<FONT COLOR='red'><B>UNASSIGNED</B></FONT>";
				$unit_link = "";
				}
			print "<TR CLASS='even' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD><TD>" . format_date($asgn_row['as_of']) .
				"&nbsp;&nbsp;&nbsp;&nbsp;By " . $asgn_row['user'] . "</TD></TR>\n";		
			print "<TR CLASS='odd' VALIGN='baseline' " . $unit_link . ">";
			print "<TD CLASS='td_label' ALIGN='right'> " . $highlight . "<U>Unit</U>:</TD><TD>" . $unit_name ."</TD></TR>\n";
	
			print "<TR CLASS='even' VALIGN='baseline'>\n";
			print "<TD CLASS='td_label' ALIGN='right'>&nbsp;&nbsp;Unit Status:</TD><TD>";
			if ($asgn_row['responder_id']!="0"){
				print $asgn_row['status_val'];
				}		// end if (!$asgn_row['responder_id']=="0")
			else {
				print "NA";
				}
?>
			</TD></TR>
			
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">Dispatched:</TD>	<TD><?php print (format_date($asgn_row['dispatched'])) ;?></TD></TR>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Responding:</TD>	<TD><?php print (format_date($asgn_row['responding'])) ;?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">On scene:</TD>		<TD><?php print (format_date($asgn_row['on_scene'])) ;?></TD></TR>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Clear:</TD>		<TD><?php print (format_date($asgn_row['clear'])) ;?></TD></TR>
			
			<TR CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Comments:</TD>
				<TD><?php print $asgn_row['assign_comments']; ?></TD></TR>
			
			<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<br>
				<INPUT TYPE="BUTTON" VALUE="Cancel"  onClick="history.back();" style="height: 1.5em;">&nbsp;&nbsp;&nbsp;&nbsp;	
<?php
			if(!is_guest()){
				print "<INPUT TYPE='BUTTON' VALUE='Edit' onClick='document.nav_form.func.value=\"edit\";document.nav_form.submit();' style='height: 1.5em;'>\n";
				}
?>			
				</TD></TR>
			 </tbody></table>
			<INPUT TYPE='hidden' NAME='func' value= ''>
			<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
			</FORM>
<?php	
			break;			// end case 'view' == } ==
		
		case 'edit':		// ====  {  ==================================================================================
?>
	<SCRIPT>
		var incident_st = unit_st = assign_st = true;		// changes to false on activation
	
		function do_del(the_Form) {
			if (confirm("Delete this dispatch record?")) {the_Form.submit();}
			}
			
		function do_reset(the_Form) {
	//		incident_st = unit_st = assign_st = true;
			the_Form.func.value='edit';
			the_Form.frm_id.value='<?php print $frm_id;?>';		
			the_Form.submit();
			}		// end function do_reset()
	
		function validate_ed(theForm) {
			var errmsg="";
			if (theForm.frm_unit_id) {						// defined?
				if (theForm.frm_unit_id.value == 0)			{errmsg+= "\tSelect Unit\n";}
				}
			if (theForm.frm_unit_status_id) {
				if (theForm.frm_unit_status_id.value == 0)	{errmsg+= "\tSelect Unit Status\n";}
				}
			if (theForm.frm_comments.value == "")			{errmsg+= "\tComments required\n";}
	
			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
				theForm.frm_inc_status_id.disabled = incident_st;
				theForm.frm_unit_status_id.disabled = unit_st;
	//			theForm..disabled = assign_st_id.disabled = assign_st;
			
				theForm.submit();
				}
			}				// end function validate_ed(theForm)
			
	
		function confirmation() {
			var answer = confirm("This dispatch run completed?")
			if (answer){
				document.edit_Form.frm_complete.value=1; 
				document.edit_Form.submit();
				}
			}		// end function confirmation()
		function reSizeScr() {
			window.resizeTo(800,300);		
			}
		function enable(instr) {
			var element= instr
			$(element).style.visibility = "visible";
	//		var i = document.forms[0].length;
			for (i=0; i<document.forms[0].length;i++){
					var start = document.forms[0].elements[i].name.length - instr.length
					if (instr == document.forms[0].elements[i].name.substring(start,99)) {
	//					alert (document.forms[0].elements[i].name.substring(start,99));
						document.forms[0].elements[i].disabled = false;
						}
				}
			}
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "reSizeScr()"><CENTER>		<!-- 1/12/09 -->
<?php	
			$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , `$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
				`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
				WHERE `$GLOBALS[mysql_prefix]assigns`.`id` = $frm_id LIMIT 1";
	
			$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			$asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result));
			$clear = (is_date($asgn_row['clear']))? "<FONT COLOR='red'><B>Cleared</B></FONT>": "";
			$disabled = "";
	
?>
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
			<TR CLASS="odd"><TD CLASS="td_label" colspan=99 ALIGN="center">Edit this Call Assignment (#<?php print $asgn_row['assign_id'] ?>) 
		<?php print $clear; ?></TD></TR>
			<TR><TD>&nbsp;</TD></TR>
	
			<TR CLASS="even" VALIGN="bottom">
				<TD CLASS="td_label" ALIGN="right">Incident:</TD>
				<TD TITLE = "<?php print $asgn_row['scope']; ?>"><?php print shorten($asgn_row['scope'], 32); ?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php	
					$selO = ($asgn_row['status']==$GLOBALS['STATUS_OPEN'])?   " SELECTED" :"";
					$selC = ($asgn_row['status']==$GLOBALS['STATUS_CLOSED'])? " SELECTED" :"" ;
?>			
					</TD><TD CLASS="td_label"> Status:&nbsp;</TD><TD><SELECT NAME='frm_inc_status_id' onChange="Javascript: incident_st = false;">
					<OPTION VALUE= <?php print $GLOBALS['STATUS_OPEN'] .  $selO; ?> >Open</OPTION>
					<OPTION VALUE= <?php print $GLOBALS['STATUS_CLOSED'] .  $selC; ?> >Closed</OPTION>
					</SELECT>
	
				</TD></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Unit:</TD>
<?php
				if ($asgn_row['responder_id']==0) {
?>			
					<TD><SELECT name="frm_unit_id" onChange = "document.edit_Form.frm_log_it.value='1'" >
						<OPTION value= '0' selected>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ";	//  
					$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
					while ($row = mysql_fetch_array($result))  {
						print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";		
						}
					print "</SELECT>\n";
					$do_unit = FALSE;
					}
				else {
?>
					<TD TITLE = "<?php print $asgn_row['name']; ?>"><?php print shorten($asgn_row['name'], 32);?>&nbsp;&nbsp;&nbsp;&nbsp;</TD>
<?php
					$do_unit = TRUE;
					}
?>			
				<TD CLASS="td_label">Unit Status:</TD><TD>
				<SELECT name="frm_unit_status_id"  onChange = "Javascript: unit_st=false; document.edit_Form.frm_log_it.value='1'" <?php print $disabled;?> > 
<?php																// UNIT STATUS
				if (intval($asgn_row['responder_id'])==0) {
					print "\t<OPTION VALUE=0 SELECTED>Select</OPTION>\n";
					}
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$the_grp = strval(rand());			//  force initial optgroup value
				$i = 0;
				while ($row2 = stripslashes_deep(mysql_fetch_array($result))) {
					if ($the_grp != $row2['group']) {
						print ($i == 0)? "": "\t</OPTGROUP>\n";
						$the_grp = $row2['group'];
						print "\t<OPTGROUP LABEL='$the_grp'>\n";
						}
					print "\t<OPTION VALUE=" . $row2['id'] . ">" . $row2['status_val'] . "</OPTION>\n";
					$i++;
					}		// end while()
				print "\t</OPTGROUP>\n</SELECT>\n";
				unset($result);
?>
				</TD></TR>
			<TR CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Comments:</TD>
				<TD colspan=3><INPUT MAXLENGTH="64" SIZE="64" NAME="frm_comments" VALUE="<?php print $asgn_row['assign_comments']; ?>" TYPE="text" <?php print $disabled;?>></TD></TR>
<?php
		 	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 		// mysql format
	
	//	 	dump($asgn_row['dispatched']);
			if (is_date($asgn_row['dispatched'])) {
				$the_date = $asgn_row['dispatched'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['dispatched']))? " CHECKED ": "";
			print "\n<TR CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Dispatched:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_db' TYPE='radio' onClick =  \"enable('dispatched')\" $chekd ><SPAN ID = 'dispatched' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("dispatched",totime($the_date), $the_dis);	// ($date_suffix,$default_date=0, $disabled=FALSE)
			print "</SPAN></TD></TR>\n";
	
	//	 	dump($asgn_row['responding']);
			if (is_date($asgn_row['responding'])) {
				$the_date = $asgn_row['responding'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['responding']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['responding']))? $asgn_row['responding']	: $now ;
			print "\n<TR CLASS='even'><TD CLASS='td_label' ALIGN='right'>Responding:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_rb' TYPE='radio' onClick =  \"enable('responding')\" $chekd><SPAN ID = 'responding' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("responding",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";
				
	//	 	dump($asgn_row['on_scene']);
			if (is_date($asgn_row['on_scene'])) {
				$the_date = $asgn_row['on_scene'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['on_scene']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['on_scene']))? $asgn_row['on_scene']	: $now ;
			print "\n<TR CLASS='odd'><TD CLASS='td_label' ALIGN='right'>On scene:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_ob' TYPE='radio' onClick =  \"enable('on_scene')\" $chekd><SPAN ID = 'on_scene' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("on_scene",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";
	
	//	 	dump($asgn_row['clear']);
			if (is_date($asgn_row['clear'])) {
				$the_date = $asgn_row['clear'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['clear']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['clear']))? $asgn_row['clear']	: $now ;
			print "\n<TR CLASS='even'><TD CLASS='td_label' ALIGN='right'>Clear:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_cb' TYPE='radio' onClick =  \"document.edit_Form.frm_complete.value=1; enable('clear')\" $chekd ><SPAN ID = 'clear' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("clear",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";
				
?>
			<TR CLASS='odd' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD>
				<TD colspan=2><?php print format_date($asgn_row['as_of']);?>&nbsp;&nbsp;&nbsp;&nbsp;By: <?php print $asgn_row['user'];?></TD>
				</TR>		
	
			<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<br>
				<INPUT TYPE="BUTTON" VALUE="Cancel"  onClick="history.back();" style="height: 1.5em;">
<?php
				if (!$disabled) {
?>			
				&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="BUTTON" VALUE="Reset"  onclick="Javascript: do_reset(document.edit_Form)" style="height: 1.5em;"/>&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="BUTTON" VALUE=" Submit " name="sub_but" onClick = "validate_ed(document.edit_Form)" style="width: 12em;height: 1.5em;" />
				</TD></TR>
				<TR CLASS='odd'><TD>&nbsp;</TD></TR>
				<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'>
<?php
	//			if(!(is_date($clear))){				// 6/4/08	// 6/26/08
				if(!(is_date($asgn_row['clear']))){				// 6/4/08	// 6/26/08
?>		
	<!--			<INPUT TYPE="BUTTON" VALUE="Run Complete" onClick="confirmation()" style="height: 1.5em;"/> -->
<?php
					}
				else {
?>		
					<INPUT TYPE="BUTTON" VALUE="Delete" onClick="do_del(document.del_Form);" style="height: 1.5em;"/>
<?php
					}
				}
?>			
				</TD></TR>
			 </tbody></table>
			<INPUT TYPE='hidden' NAME='frm_by_id' value= "<?php print $my_session['user_id'];?>"/>
			<INPUT TYPE='hidden' NAME='func' value= 'edit_db'/>
			<INPUT TYPE='hidden' NAME='frm_complete' value= ''/> 
			<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'/>
<?php
			if ($do_unit) {
				print "\t\t<INPUT TYPE='hidden' NAME='frm_unit_id' value= '" .  $asgn_row['responder_id'] . "'/>\n";
				}
?>		
			<INPUT TYPE='hidden' NAME='frm_ticket_id' value= '<?php print $asgn_row['ticket_id'];?>'/>
			<INPUT TYPE='hidden' NAME='frm_log_it' value=''/>
			<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
			</FORM>
			<FORM NAME="del_Form" ACTION = "<?php print basename(__FILE__); ?>" METHOD = "post">
			<INPUT TYPE='hidden' NAME='func' value= 'delete_db'/>
			<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'/>
			</FORM>
			
<?php
			break;			// end 	case 'edit': == } == 
			
		case 'edit_db':		// ==== {  ================================================ 
/*
array(10) {
  ["frm_unit_id"]=>
  string(1) "2"
  ["frm_unit_status_id"]=>
  string(1) "1"
  ["frm_comments"]=>
  string(3) "New"
  ["frm_by_id"]=>
  string(1) "1"
  ["func"]=>
  string(7) "edit_db"
  ["frm_complete"]=>
  string(0) ""
  ["frm_id"]=>
  string(1) "5"
  ["frm_ticket_id"]=>
  string(1) "1"
  ["frm_log_it"]=>
  string(1) "1"
  ["lines"]=>
  string(1) "5"
}
*/
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			
			if (isset($frm_inc_status_id)) {
				$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `status`= " . quote_smart($frm_inc_status_id) . ", `updated` = " . quote_smart($now) . " WHERE `id` = " . quote_smart($frm_ticket_id) ." LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				do_log($GLOBALS['LOG_INCIDENT_CHANGE'], $frm_ticket_id);
				}
				
			if (isset($frm_unit_status_id)) {
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($frm_unit_status_id) . ", `updated` = " . quote_smart($now) . " WHERE `id` = " . quote_smart($frm_unit_id) ." LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				do_log($GLOBALS['LOG_UNIT_CHANGE'], $frm_unit_id);	
				}
	
			if (!(empty($frm_complete))) 	{			// is run completed?  6/4/08	// 6/26/08		
				do_log($GLOBALS['LOG_UNIT_COMPLETE'], $frm_ticket_id, $frm_unit_id);		// set clear times
				$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) . ", `clear`= " . quote_smart($now) . " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				}
			
			$frm_dispatched =	(array_key_exists('frm_db', $_POST))? 	quote_smart($_POST['frm_year_dispatched'] . "-" . $_POST['frm_month_dispatched'] . "-" . $_POST['frm_day_dispatched']." " . $_POST['frm_hour_dispatched'] . ":". $_POST['frm_minute_dispatched'] .":00") : "";
			$frm_responding = 	(array_key_exists('frm_rb', $_POST))? 	quote_smart($_POST['frm_year_responding'] . "-" . $_POST['frm_month_responding'] . "-" . $_POST['frm_day_responding']." " . $_POST['frm_hour_responding'] . ":". $_POST['frm_minute_responding'] .":00") : "";
			$frm_on_scene = 	(array_key_exists('frm_os', $_POST))?  	quote_smart($_POST['frm_year_on_scene'] . "-" .   $_POST['frm_month_on_scene'] . "-" .   $_POST['frm_day_on_scene']." " .   $_POST['frm_hour_on_scene'] . ":".   $_POST['frm_minute_on_scene'] .":00") : "";
			$frm_clear = 		(array_key_exists('frm_cb', $_POST))?  	quote_smart($_POST['frm_year_clear'] . "-" . 	  $_POST['frm_month_clear'] . "-" 	.    $_POST['frm_day_clear']." " .      $_POST['frm_hour_clear'] . ":".      $_POST['frm_minute_clear'] .":00") : "";
			
			$date_part = (empty($frm_dispatched))? 	"": ", `dispatched`= " . 	$frm_dispatched ;
			$date_part .= (empty($frm_responding))? "": ", `responding`= " . 	$frm_responding;
			$date_part .= (empty($frm_on_scene))? 	"": ", `on_scene`= " 	. 	$frm_on_scene;
			$date_part .= (empty($frm_clear))? 		"": ", `clear`= " . 		$frm_clear;

			$unit_sql = (isset($frm_unit_id))?	" `responder_id`=" .quote_smart($frm_unit_id) . ", " :"";			// 1/15/09

			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET " .$unit_sql. " `as_of`= " . quote_smart($now) . ", `comments`= " . quote_smart($_POST['frm_comments']) ;
			$query .= $date_part;
			$query .=  " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
			$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);
	
			$message = "Update Applied";
?>
			</HEAD>
	<BODY>
		<BR><BR><CENTER><H3><?php print $message; ?></H3><BR><BR>
		<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()"/>
		<INPUT TYPE='hidden' NAME='func' VALUE='list'/>
		<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
		</FORM></BODY></HTML>
<?php	
			break;				// end 	case 'edit_db' == } ==
			
		case 'delete_db':		// =====  {  =====================  6/4/08	
		
				$query  = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";	
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	
				$message = "Assign record deleted";
?>
			</HEAD>
	<BODY><CENTER>		<!-- 1/12/09 -->
		<BR><BR><CENTER><H3><?php print $message; ?></H3><BR><BR><BR>
		<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()"/>
		<INPUT TYPE='hidden' NAME='func' VALUE='list'/>
		</FORM></BODY></HTML>
<?php	
			break;			// end case 'delete_db': === } ===
	
		default:				// =======================================================================================
			print $func . "	 error: " . __LINE__;
		}				// end switch ($func)
	
	$temp = $_POST;

	$hide_cl = (array_key_exists ('hide_cl', $_POST))? $_POST['hide_cl'] :"0";
?>
	
	<FORM NAME='nav_form' METHOD='post' ACTION = "<?php print basename(__FILE__); ?>">
	<INPUT TYPE='hidden' NAME='frm_id' VALUE=''/>
	<INPUT TYPE='hidden' NAME='func' VALUE=''/>
	<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
	<INPUT TYPE='hidden' NAME='hide_cl' value='<?php print $hide_cl; ?>'/>		<!-- 1/20/09 -->
	<INPUT TYPE='hidden' NAME='chg_hide' value='0'/>							<!-- 2/18/09 -->
	</FORM>
	
	<FORM NAME='T_nav_form' METHOD='get' TARGET = 'main' ACTION = "main.php">
	<INPUT TYPE='hidden' NAME='id' VALUE=''/>
	</FORM>
	
	<FORM NAME='U_nav_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
	<INPUT TYPE='hidden' 	NAME='id' VALUE=''/>
	<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'/>
	<INPUT TYPE='hidden' 	NAME='view' VALUE='true'/>
	</FORM>
	
	<FORM NAME='U_edit_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
	<INPUT TYPE='hidden' 	NAME='id' VALUE=''/>
	<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'/>
	<INPUT TYPE='hidden' 	NAME='edit' VALUE='true'/>
	</FORM>
	
	<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"/>
	<INPUT TYPE='hidden' NAME='func' VALUE='list'/>
	<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
	</FORM>
	</BODY></HTML>
<?php
	}		// end else ...		1/13/09
?>	
