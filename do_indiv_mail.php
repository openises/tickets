<?php
/*
8/17/09	initial release
*/
error_reporting(E_ALL);		//
require_once('./incs/functions.inc.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print LessExtension(basename(__FILE__));?> </TITLE>
<META NAME="Description" CONTENT="Email to units">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<STYLE>
#.plain 	{ background-color: #FFFFFF;}
</STYLE>
<?php

//dump($_POST);

if (empty($_POST)) {		
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . quote_smart(trim($_GET['the_id'])). " LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = mysql_fetch_assoc($result);
?>

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
	

	function validate() {
		var errmsg="";
		if (document.mail_form.frm_addr.value.trim()=="") {errmsg+="Message address is required";}
		if (document.mail_form.frm_subj.value.trim()=="") {errmsg+="Message subject is required";}
		if (document.mail_form.frm_text.value.trim()=="") {errmsg+="Message text is required";}
		if (!(errmsg=="")){
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			document.mail_form.submit();	
			}
		}				// end function validate()

	</SCRIPT>
	</HEAD>

	<BODY><CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail to Unit</H3>
	<P>
		<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
		<TABLE BORDER = 0>
		<TR CLASS= 'even'>
			<TD ALIGN='right'>To:</TD><TD><INPUT NAME='frm_name' SIZE=32 VALUE = '<?php print $row['contact_name'];?>'></TD>
			</TR>

		<TR CLASS= 'odd'>
			<TD ALIGN='right'>Addr:</TD><TD><INPUT NAME='frm_addr' SIZE=32 VALUE = '<?php print $row['contact_via'];?>'></TD>
			</TR>
	
		<TR CLASS='even'><TD ALIGN='right'>Subject: </TD><TD COLSPAN=2><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
		<TR CLASS='odd'><TD ALIGN='right'>Message:</TD><TD COLSPAN=2> <TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA></TD></TR>
		<TR CLASS='even'><TD ALIGN='center' COLSPAN=3><BR /><BR />
			<INPUT TYPE='button' 	VALUE='Send' onClick = "validate()">&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />
			</TD></TR>
			</TABLE></FORM>
<?php
		}		// end if (empty($_POST)) {

	else {

			do_send ($_POST['frm_addr'], $_POST['frm_subj'], $_POST['frm_text'] );	// ($to_str, $subject_str, $text_str )
?>
	<BODY><CENTER>		
	<CENTER><BR /><BR /><BR /><H3>Mail sent</H3>
	<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php

	}		// end else
?> </BODY>
</HTML>
