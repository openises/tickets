<?php
/*
6/30/09	initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
12/18/10 set signal added
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		//
	

@session_start();
require_once('incs/functions.inc.php');		//7/28/10
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
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
#.plain 	{ background-color: #FFFFFF;}
</STYLE>
<?php

//dump($_POST);

if (empty($_POST)) {


	$colors = array();
	$colors[$GLOBALS['LEVEL_SUPER'] ] = 			"#FFFFFF";		// white
	$colors[$GLOBALS['LEVEL_ADMINISTRATOR'] ] = 	"#C0C0C0";		// gray
	$colors[$GLOBALS['LEVEL_USER'] 	] =				"#FFFF00";		// yellow
	$colors[$GLOBALS['LEVEL_GUEST'] ] = 			"#CCFF00";		// mint
	$colors[$GLOBALS['LEVEL_MEMBER'] ] = 			"#FFCC00";		// orange
	$colors[$GLOBALS['LEVEL_UNIT'] 	] = 			"#00CCCC";		// lt. blue	

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `email` IS NOT NULL
		ORDER BY `level` ASC,`user` ASC" ;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$rows = array();
	while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
		if (is_email($row['email'])) {
			$rows[] = $row;
			}
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
	
	function do_step_1() {
		document.mail_form.submit();
		}

	function do_step_2() {
		if (document.mail_form.frm_text.value.trim()=="") {
			alert ("Message text is required");
			document.mail_form.frm_text.focus();
			return false;
			}
		var sep = "";
		for (i=0;i<document.mail_form.elements.length; i++) {
			if((document.mail_form.elements[i].type =='checkbox') && (document.mail_form.elements[i].checked)){		// frm_add_str
				document.mail_form.frm_add_str.value += sep + document.mail_form.elements[i].value;
				sep = "|";
				}
			}
		if (document.mail_form.frm_add_str.value.trim()=="") {
			alert ("Addressees required");
			return false;
			}
		document.mail_form.submit();	
		}

	function reSizeScr(lines){
		var the_width = 600;
		var the_height = ((lines * 23)+380);			// values derived via trial/error (more of the latter, mostly)
		window.resizeTo(the_width,the_height);	
		}
	
	function do_clear(){
		for (i=0;i<document.mail_form.elements.length; i++) {
			if(document.mail_form.elements[i].type =='checkbox'){
				document.mail_form.elements[i].checked = false;
				}
			}		// end for ()
		$('clr_spn').style.display = "none";
		$('chk_spn').style.display = "block";
		}		// end function do_clear

	function do_check(){
		for (i=0;i<document.mail_form.elements.length; i++) {
			if(document.mail_form.elements[i].type =='checkbox'){
				document.mail_form.elements[i].checked = true;
				}
			}		// end for ()
		$('clr_spn').style.display = "block";
		$('chk_spn').style.display = "none";
		}		// end function do_clear

	</SCRIPT>
	</HEAD>

<?php		
	if(count($rows)>0) {
?>
	<BODY onLoad = "reSizeScr(<?php print count($rows);?>)"><CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail to Users</H3>
<?php
	if(count($rows)>2) {
?>
		<SPAN ID='clr_spn' STYLE = 'display:block' onClick = 'do_clear()'>&raquo; <U>Un-check all</U></SPAN>
		<SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check()'>&raquo; <U>Check all</U></SPAN>
<?php
		}
?>		
	<P>
		<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
<?php		
		print "<TABLE ALIGN = 'center' BORDER=0 WIDTH=500>\n";
		for ($i=0; $i < count($rows); $i++) {
			$row = stripslashes_deep($rows[$i]);
			print "\t<TR CLASS= '{$evenodd[($i)%2]}'>
				<TD ALIGN='right'><INPUT TYPE='checkbox' CHECKED NAME='cb{($i+1)}'VALUE='{$row['email']}'> </TD>
				<TD><SPAN style = \"background-color:{$colors[$row['level']]}\"> &nbsp;{$row['user']}&nbsp;</SPAN>
					(<I>{$row['email']}</I>) </TD>
				<TD ALIGN='left'>{$row['name_f']} {$row['name_mi']} {$row['name_l']}</TD>
				</TR>\n";
			}		// end for()

?>	
		<TR CLASS='<?php print $evenodd[($i)%2]; ?>'><TD>Subject: </TD><TD COLSPAN=2><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
<SCRIPT>
	function set_signal(inval) {				// 12/18/10
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.mail_form.frm_text.value+=" " + temp_ary[1] + ' ';		
		document.mail_form.frm_text.focus();		
		}		// end function set_signal()
</SCRIPT>
		<TR CLASS='<?php print $evenodd[($i+1)%2]; ?>'><TD>Message:</TD><TD COLSPAN=2> <TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA></TD></TR>

		<TR CLASS='<?php print $evenodd[($i+1)%2]; ?>'>		<!-- 11/15/10 -->
			<TD></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>


		<TR CLASS='<?php print $evenodd[($i)%2]; ?>'><TD ALIGN='center' COLSPAN=3><BR /><BR />
			<INPUT TYPE='button' 	VALUE='Send' onClick = "do_step_2()">&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />
			</TD></TR>
			</TABLE></FORM>

		Levels: 
		<SPAN style = 'background-color:<?php print $colors[$GLOBALS['LEVEL_SUPER']];?>'>Super</SPAN>&nbsp;&nbsp;
		<SPAN style = 'background-color:<?php print $colors[$GLOBALS['LEVEL_ADMINISTRATOR']];?>'>Admin</SPAN>&nbsp;&nbsp;
		<SPAN style = 'background-color:<?php print $colors[$GLOBALS['LEVEL_USER']];?>'>Operator</SPAN>&nbsp;&nbsp;
		<SPAN style = 'background-color:<?php print $colors[$GLOBALS['LEVEL_GUEST']];?>'>Guest</SPAN>&nbsp;&nbsp;
		<SPAN style = 'background-color:<?php print $colors[$GLOBALS['LEVEL_MEMBER']];?>'>Member</SPAN>&nbsp;&nbsp;
		<SPAN style = 'background-color:<?php print $colors[$GLOBALS['LEVEL_UNIT']];?>'>Unit</SPAN>
		  
<?php
			}		// end if(mysql_affected_rows()>0)
		else {
?>
	<BODY onLoad = "reSizeScr(2)"><CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail to Users</H3>
	<BR /><BR />
	<H3>No addresses available!</H3><BR /><BR />
	<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.close();'><BR /><BR />

<?php
			}
		}		// end if (empty($_POST)) {

	else {

			do_send ($_POST['frm_add_str'], $_POST['frm_subj'], $_POST['frm_text'] );	// ($to_str, $subject_str, $text_str )
?>
	<BODY onLoad = "reSizeScr(2)"><CENTER>		<!-- 1/12/09 -->
	<CENTER><BR /><BR /><BR /><H3>Mail sent</H3>
	<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php

	}		// end else
?> </BODY>
</HTML>
