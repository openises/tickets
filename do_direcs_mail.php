<?php
/*
9/17/09	initial release - Email directions to Facilty to responding unit.
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		//

@session_start();
session_write_close();
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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
#.plain 	{ background-color: #FFFFFF;}
</STYLE>
<?php

if (empty($_POST['frm_u_id']) && empty($_GET['unit_id'])) {
	print "<CENTER>You must select a Unit first</BR></BR>";
	print "<A href='javascript: self.close ()'>Close</A></CENTER>";
	exit();
	}

if(!array_key_exists('direcs', $_GET)) {
	$direcs = ($_POST['frm_direcs'] == "") ? $_POST['frm_text'] : $_POST['frm_direcs'];
	$tick_name = (isset($_POST['frm_scope'])) ? $_POST['frm_scope'] : "";	//10/29/09
	$the_sms = ((array_key_exists('frm_smsgaddrs', $_POST)) && ($_POST['frm_smsgaddrs'] != "") && ((array_key_exists('frm_use_smsg', $_POST)) && $_POST['frm_use_smsg'] == 1)) ? $_POST['frm_smsgaddrs'] : "";
	$the_tick = ((isset($_POST['frm_tick_id'])) && ($_POST['frm_tick_id'] != "")) ? $_POST['frm_tick_id'] : 0;	
	$unit_id = ((isset($_POST['frm_u_id'])) && ($_POST['frm_u_id'] != "")) ? $_POST['frm_u_id'] : 0;
	$the_addrs = ((isset($_POST['frm_addr'])) && ($_POST['frm_addr'] != "")) ? $_POST['frm_addr'] : "";		
	$smsg_id = "";
	$mail_subject = $_POST['frm_mail_subject'];
	$direcs = str_replace("&nbsp;",' ',$direcs); 
	$direcs = str_replace("</tr>",'NEWLINE',$direcs);
	$direcs = str_replace("</td>",' ',$direcs);
	$direcs = strip_tags($direcs);
	$direcs = str_replace("NEWLINE",'&#13;&#10;',$direcs);
	//$direcs = stripslashes($direcs);
	//$direcs = html_entity_decode($direcs);
	unset($_POST['frm_direcs']);
	unset($_POST['frm_u_id']);
	unset($_POST['frm_tick_id']);
	unset($_POST['frm_smsgaddrs']);
	unset($_POST['frm_mail_subject']);
	unset($_POST['frm_scope']);	//10/29/09
	$display_form = (array_key_exists('showform', $_POST)) ? true : false;
}
if (empty($_POST) || $display_form) {	
	if(array_key_exists('unit_id', $_GET)) {
		$unit_id = $_GET['unit_id'];
		}
	if(array_key_exists('direcs', $_GET)) {
		$direcs = $_GET['direcs'];
		$direcs = str_replace("&nbsp;",' ',$direcs); 
		$direcs = str_replace("</tr>",'NEWLINE',$direcs);
		$direcs = str_replace("</td>",' ',$direcs);
		$direcs = strip_tags($direcs);
		$direcs = str_replace("NEWLINE",'&#13;&#10;',$direcs);
		}
	if(array_key_exists('subject', $_GET)) {
		$mail_subject = $_GET['subject'];
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = $unit_id";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$contact_email = $row['contact_via'];
		$contact_name = $row['contact_name'];
		$smsg_id = $row['smsg_id'];
		}
?>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
 	var direcs = "";
	
	function strip_tags(input, allowed) {
		allowed = (((allowed || '') + '')
		.toLowerCase()
		.match(/<[a-z][a-z0-9]*>/g) || [])
		.join('');
		var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
		commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
		return input.replace(commentsAndPhpTags, '')
		.replace(tags, function($0, $1) {
		return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
		});
		}
	
	function populateData() {
		direcs = window.opener.direcs;
		direcs = direcs.replace(/<\/h2>/g,'\r\n'); 
		direcs = direcs.replace(/<\/h3>/g,'\r\n'); 
		direcs = direcs.replace(/<tr class="">/g,"#");
		direcs = direcs.replace(/<\/tr>/g,'\r\n'); 
		direcs = direcs.replace(/<\/td>/g,' '); 
		direcs = unescape(direcs); 
		direcs = strip_tags(direcs);
		document.mail_form.frm_text.value = direcs;
		}
		
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

	<BODY onLoad = "populateData();"><CENTER>		<!-- 1/12/09 -->
	<H3>Mail to Directions <?php print get_text('Unit');?></H3>
	<P>
		<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
		<TABLE BORDER = 0>
			<TR CLASS= 'even'>
				<TD CLASS='td_label text text_right'>To:</TD>
				<TD CLASS='td_data text'>
					<INPUT NAME='frm_name' SIZE=32 VALUE = '<?php print $contact_name;?>'>
				</TD>
				</TR>

			<TR CLASS= 'odd'>
				<TD ALIGN='right'>Addr:</TD>
				<TD CLASS='td_data text'>
					<INPUT NAME='frm_addr' SIZE=32 VALUE = '<?php print $contact_email;?>'>
				</TD>
			</TR>
<?php
			if((get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {
?>
				<TR CLASS='even'>
					<TD CLASS='td_label text text_right'><?php get_provider_name(get_msg_variable('smsg_provider'));?> Addrs: </TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE='text' NAME='frm_smsgaddrs' size='60' VALUE='<?php print $smsg_id;?>' />
					</TD>
				</TR>	
				<TR CLASS='even'>
					<TD CLASS='td_label text text_right'>Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: </TD>
					<TD CLASS='td_data text'>
<?php
						if(($smsg_id != "" && $contact_email == "") || ($smsg_id != "" && get_msg_variable('default_sms') == "1")) {
							$checked = "CHECKED";
							} else {
							$checked = "";
							}
?>
						<INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE="0" <?php print $checked;?> />
					</TD>
				</TR>			
<?php
				}
?>
			<TR CLASS='even'>
				<TD CLASS='td_label text text_right'>Subject: </TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60 VALUE = '<?php print $mail_subject;?> - <?php print $tick_name;?>'>
				</TD>
			</TR>
			<TR CLASS='odd'>
				<TD CLASS='td_label text text_right'>Message:</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA>
				</TD>
			</TR>
			<TR CLASS='even'>
				<TD ALIGN='center' COLSPAN=3>
					<SPAN id='send_but' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate();"><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>
					<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.mail_form.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</TD>
			</TR>
		</TABLE>
<?php
		if((get_variable('use_messaging') != 2) && (get_variable('use_messaging') != 3)) {
?>
			<INPUT TYPE="hidden" NAME = 'frm_smsgaddrs' VALUE=""/> <!-- 10/23/12 -->
			<INPUT TYPE='hidden' NAME = 'frm_use_smsg' VALUE = "0"> <!-- 10/23/12 -->
<?php
			}	
?>
		<INPUT TYPE="hidden" NAME="frm_direcs" VALUE="">
		<INPUT TYPE="hidden" NAME="frm_u_id" VALUE='<?php print $unit_id;?>'>
		<INPUT TYPE="hidden" NAME="frm_tick_id" VALUE='<?php print $the_tick;?>'>		
		<INPUT TYPE="hidden" NAME="frm_mail_subject" VALUE="">
		<INPUT TYPE="hidden" NAME="frm_scope" VALUE="">		<!-- 10/29/09 -->
		</FORM>
		</CENTER>
<?php
		}		// end if (empty($_POST)) {

	else {
		$theCount = do_send ($the_addrs, $the_sms, $mail_subject, $direcs, $the_tick, $unit_id);	// ($to_str, $subject_str, $text_str )
?>
	<BODY>
	<CENTER>
	<BR />
	<BR />
	<BR />
	<H3><?php print $theCount;?> Mail sent</H3>
	<BR />
	<BR />
	<BR />
	<SPAN id='fin_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
	</CENTER>
<?php

	}		// end else
?> 
</BODY>
<SCRIPT>
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
</HTML>
