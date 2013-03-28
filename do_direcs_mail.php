<?php
/*
9/17/09	initial release - Email directions to Facilty to responding unit.
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		//

@session_start();
require_once($_SESSION['fip']);		//7/28/10
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
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
#.plain 	{ background-color: #FFFFFF;}
</STYLE>
<?php

if (empty($_POST['frm_u_id'])) {
	print "<CENTER>You must select a Unit first</BR></BR>";
	print "<A href='javascript: self.close ()'>Close</A></CENTER>";
	exit();
	}

	
$direcs = $_POST['frm_direcs'];
$tick_name = $_POST['frm_scope'];	//10/29/09
$the_sms = ((isset($_POST['frm_smsgaddrs'])) && ($_POST['frm_smsgaddrs'] != "") && ($_POST['frm_use_smsg'] == 1)) ? $_POST['frm_smsgaddrs'] : "";
$the_tick = ((isset($_POST['frm_tick_id'])) && ($_POST['frm_tick_id'] != "")) ? $_POST['frm_tick_id'] : 0;	
$unit_id = ((isset($_POST['frm_u_id'])) && ($_POST['frm_u_id'] != "")) ? $_POST['frm_u_id'] : 0;
$the_addrs = ((isset($_POST['frm_addr'])) && ($_POST['frm_addr'] != "")) ? $_POST['frm_addr'] : "";		
$smsg_id = "";
$mail_subject = $_POST['frm_mail_subject'];
$direcs = str_replace("&nbsp;",' ',$direcs); 
$direcs = strip_tags($direcs);
$direcs = stripslashes($direcs);
//$direcs = html_entity_decode($direcs);
unset($_POST['frm_direcs']);
unset($_POST['frm_u_id']);
unset($_POST['frm_tick_id']);
unset($_POST['frm_smsgaddrs']);
unset($_POST['frm_mail_subject']);
unset($_POST['frm_scope']);	//10/29/09

if (empty($_POST)) {	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = $unit_id";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$contact_email = $row['contact_via'];
		$contact_name = $row['contact_name'];
		$smsg_id = $row['smsg_id'];
	}

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

	function back_to_routes() {
		window.location.href("fac_routes.php");
		}

	</SCRIPT>
	</HEAD>

	<BODY><CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail to Unit</H3>
	<P>
		<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
		<TABLE BORDER = 0>
		<TR CLASS= 'even'>
			<TD ALIGN='right'>To:</TD><TD><INPUT NAME='frm_name' SIZE=32 VALUE = '<?php print $contact_name;?>'></TD>
			</TR>

		<TR CLASS= 'odd'>
			<TD ALIGN='right'>Addr:</TD><TD><INPUT NAME='frm_addr' SIZE=32 VALUE = '<?php print $contact_email;?>'></TD>
		</TR>
<?php
		if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {	//	10/23/12
?>
			<TR CLASS='even'><TD ALIGN='right'><?php get_provider_name(get_msg_variable('smsg_provider'));?> Addrs: </TD>
				<TD><INPUT TYPE='text' NAME='frm_smsgaddrs' size='60' VALUE='<?php print $smsg_id;?>'></TD>
			</TR>	
			<TR CLASS='even'><TD>Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: </TD> <!-- 10/23/12 -->
				<TD><INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE="0"></TD> <!-- 10/23/12 -->
			</TR>			
<?php
			} else {
?>
			<INPUT TYPE="hidden" NAME = 'frm_smsgaddrs' VALUE=""/> <!-- 10/23/12 -->
			<INPUT TYPE='hidden' NAME = 'frm_use_smsg' VALUE = "0"> <!-- 10/23/12 -->
<?php
			}	
?>
		<TR CLASS='even'><TD ALIGN='right'>Subject: </TD><TD COLSPAN=2><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60 VALUE = '<?php print $mail_subject;?> - <?php print $tick_name;?>'></TD></TR>		<!-- 10/29/09 -->
		<TR CLASS='odd'><TD ALIGN='right'>Message:</TD><TD COLSPAN=2> <TEXTAREA NAME='frm_text' COLS=60 ROWS=4><?php print $direcs;?></TEXTAREA></TD></TR>
		<TR CLASS='even'><TD ALIGN='center' COLSPAN=3><BR /><BR />
		<INPUT TYPE="hidden" NAME="frm_direcs" VALUE="">
		<INPUT TYPE="hidden" NAME="frm_u_id" VALUE='<?php print $unit_id;?>'>
		<INPUT TYPE="hidden" NAME="frm_tick_id" VALUE='<?php print $the_tick;?>'>		
		<INPUT TYPE="hidden" NAME="frm_mail_subject" VALUE="">
		<INPUT TYPE="hidden" NAME="frm_scope" VALUE="">		<!-- 10/29/09 -->
			<INPUT TYPE='button' 	VALUE='Send' onClick = "validate()">&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />
			</TD></TR>
			</TABLE></FORM>
<?php
		}		// end if (empty($_POST)) {

	else {
		do_send ($the_addrs, $the_sms, $_POST['frm_subj'], $_POST['frm_text'], $the_tick, $unit_id);	// ($to_str, $subject_str, $text_str )
?>
	<BODY><CENTER>		
	<CENTER><BR /><BR /><BR /><H3>Mail sent</H3>
	<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php

	}		// end else
?> </BODY>
</HTML>
