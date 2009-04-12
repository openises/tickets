<?php
/*
9/17/08 notify user if no contact addresses
10/6/08	extracted mail function to functions.inc.php as mail_it()
10/6/08	detect zero addressees
10/7/08	set WRAP="virtual"
10/7/08	ajax-ify for inter-message delay
10/17/08 changed addr string to pipe-delim'd
3/22/09 added mobile as email addr
*/
require_once('./incs/functions.inc.php');
if($istest) {
	print "GET";
	dump ($_GET);
	print "POST";
	dump ($_POST);
	}
extract ($_GET);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Mail Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<?php

if (empty ($_POST)) {
		$ticket_id = $_GET['ticket_id'];
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
		$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
//		if (!$reply_to = get_variable("email_reply_to"))  {
//			$reply_to = "<INPUT TYPE='text' NAME='frm_reply_to' SIZE=36 VALUE=''>";
//			}
		$text = mail_it ("", "", $ticket_id, 2, TRUE) ;		// returns msg text **ONLY**
		$temp = explode("\n", $text);
		$nr_lines = intval(count($temp) + 2);
?>
	<SCRIPT src="./js/multiSelect.js"></SCRIPT>

	<SCRIPT>
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function OKaddr(theStr) {
		var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return filter.test(theStr);
		}

	var line = 1;
	var max=19;
	function do_more () {
		if (line==max) {return;}
		else {
			elem="F"+line;
			document.getElementById(elem).style.display = '';		// show it
			line++;
			}
		}		// end function do_more ()
		
	function do_val(theForm) {
//		for (i=0;i<document.forms[0].elements.length; i++) {
//			alert("72 " + document.forms[0].elements[i].name + " " +document.forms[0].elements[i].type);
//			}

		var j = 0;
		var sep="";
		for (var i=0; i<theForm.elements.length;i++) {
			if ((theForm.elements[i].type == "checkbox") && (theForm.elements[i].checked)) {
				theForm.frm_to_str.value += (sep + theForm.elements[i].value);
				sep="|";
				j++;
				}
			}
//		alert("84 " + theForm.frm_to_str.value);
		var errmsg="";
		if (j==0) 				{errmsg+= "\tAt least one address is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
//			theForm.frm_text.value = escape(theForm.frm_text.value);
			theForm.submit();
			}
		}				// end function val idate()

	</SCRIPT>
	</HEAD>
	<BODY>
	<DIV ID='first' STYLE='display:block'>
	<FORM METHOD="post" ACTION="<?php print basename( __FILE__); ?>" NAME="mail_Form" >
	<INPUT TYPE='hidden' NAME = 'frm_ticket_id' VALUE='<?php print $ticket_id; ?>'>
	<TABLE>
	<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><BR /><B>Edit Message<BR /><BR /></TD></TR>
	<TR CLASS='odd'><TD>Ticket:</TD><TD><B><?php print shorten($t_row['scope'], 48); ?></B></TD></TR>
	<TR CLASS='even'><TD>Message:</TD>		<TD><TEXTAREA ROWS = <?php print $nr_lines; ?> COLS=60 NAME='frm_text' WRAP="virtual"><?php print $text; ?></TEXTAREA></TD></TR>
<?php														//			generate dropdown menu of contacts

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` ORDER BY `name` ASC";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		if (mysql_affected_rows()>0) {				// 9/17/08
			$got_addr = TRUE;
			$height = (mysql_affected_rows() + 1) * 16;
			print "<TR CLASS='odd'><TD>To:</TD>";
			print "<TD><SELECT NAME='frm_to[]' style='width: 250px; height: " . $height ."px;' multiple >\n";
	    	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
	    		if ((!((trim($row['email']))) == "") && (is_email(trim($row['email'])))) {
					print "\t<OPTION VALUE='" . $row['email'] . "'>" . $row['name'] . "/" .$row['organization'] . " <I>(email)</I></OPTION>\n";
					}
	    		if ((!((trim($row['mobile']))) == "") && (is_email(trim($row['mobile'])))) {
					print "\t<OPTION VALUE='" . $row['mobile'] . "'>" . $row['name'] . "/" .$row['organization'] . " <I>(mobile)</I></OPTION>\n";
					}
	    		if ((!((trim($row['other']))) == "") && (is_email(trim($row['other'])))) {
					print "\t<OPTION VALUE='" . $row['other'] . "'>" . $row['name'] . "/" .$row['organization'] . " <I>(other)</I></OPTION>\n";
					}
				}
			print "\t</SELECT>\n</TD></TR>";
			}				// end (mysql_affected_rows()>0)
		else {
			print "<TR CLASS='even'><TD COLSPAN=2 align='CENTER'><B>No addresses.<BR /> Populate 'Contacts' table via Configuration link.</TD></TR>";
			$got_addr = FALSE;
			}
		
?>
		<INPUT TYPE = "hidden" NAME="frm_to_str" VALUE="">
		<INPUT TYPE = "hidden" NAME="frm_subj" VALUE= "<?php print shorten($t_row['scope'], 48); ?>">
		</FORM>
	<FORM NAME='dummy' METHOD='get'>
	<TR CLASS='even'><TD COLSPAN=2 ALIGN="center"><BR />
<?php if ($got_addr) { ?>

		<INPUT TYPE="button" VALUE="Reset" onClick = "document.mail_Form.reset();">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" VALUE="Send" onClick="do_val(document.mail_Form);">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php	} ?>

		<INPUT TYPE="button" VALUE="Cancel"  onClick="self.close();" >
		</FORM>
		</TD></TR>
	</TABLE>
	</DIV>
	
	<DIV ID='second' STYLE='display:none'>
	<CENTER>
	<H3>Sending mail ...</H3><BR /><BR /><BR />
	<IMG SRC="./markers/spinner.gif" BORDER=0>
	</DIV>
	
	</BODY></HTML>
<?php
	}		// end if (empty ($_POST))
else {
	print __LINE__;
//	snap(basename(__FILE__) . __LINE__, $_POST['frm_to_str'] );
//	snap(basename(__FILE__) . __LINE__, $_POST['frm_subj'] );
//	snap(basename(__FILE__) . __LINE__, $_POST['frm_text']);

	do_send ($_POST['frm_to_str'], $_POST['frm_subj'], $_POST['frm_text']) ;		// ($to, $subject, $text) ;
?>
<SCRIPT>
/*
function sendRequest(url,callback,postData) {		// 10/15/08
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
	
	//	Useage:
	//
	//	sendRequest('file.txt',handleResult, argument);
	//
	// Now the file file.txt is fetched, and when that's done the function handleResult() is called.
	// This function receives the XMLHttpRequest object as an argument, which I traditionally call
	// req (though, of course, you can use any variable name you like). Typically, this function reads out
	// the responseXML or responseText and does something with it.
	//
	
	function handleResult(req) {				// the called-back function
//		alert("191 " + req.responseText);
//		var writeroot = [some element];
//		writeroot.innerHTML = req.responseText;
		}
//	var params = "lorem=abcdef&name=binny";
//	sendRequest('_sleep.php',handleResult, params);
	
	function domail() {
		var theAddresses = "<?php print implode("|", $_POST['frm_to']);?>";		// 10/17/08
		var theText="<?php print $_POST['frm_text'];?>";
//		alert ("202 " + theText);
		var theId = "<?php print $_POST['frm_ticket_id'];?>";
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId) + "&text_sel=1" ;		// ($to_str, $text, $ticket_id)   10/15/08
//			 mail_it ($to_str, $text, $ticket_id, $text_sel=1;, $txt_only = FALSE)

		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
//		alert ("208 " + params);
*/	
		}

</SCRIPT>

	</HEAD>
	<BODY>
	<CENTER><BR /><BR /><BR /><BR /><BR /><h3>Sent!</h3><BR /><BR />
	<FORM NAME='can_Form' METHOD="get" ACTION = "<?php print basename( __FILE__); ?>" >
	<INPUT TYPE="button" VALUE = "Close" onClick = "self.close()"></CENTER>
	</FORM>		
	</BODY></HTML>
<?php
	}		// end else ...
	
?>
