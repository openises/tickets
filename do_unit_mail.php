<?php
/*
6/13/09	initial release
6/28/09	handle no assigns and empty scope
*/

error_reporting(E_ALL);		//
	
require_once('./incs/functions.inc.php');
//dump($_POST);

if (empty($_POST)) {
	$query = "SELECT DISTINCT `ticket_id` , scope, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		ORDER BY `t`.`scope` ASC" ;

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_affected_rows()==0) {			// 6/28/09
		$step = 2;
		}
	else{
		$step = 1;
		}
	}
else {
	$step = $_POST['frm_step'];
	}

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
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
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
		var the_height = ((lines * 23)+160);			// values derived via trial/error (more of the latter, mostly)
		if (the_height <260) {the_height = 260;}
		window.resizeTo(the_width,the_height);	
		}
	
</SCRIPT>
</HEAD>
<?php

	switch($step) {
		case 1:
?>		
<BODY onLoad = "reSizeScr(1)"><CENTER>		<!-- 1/12/09 -->
<CENTER><H3>Mail to Units</H3>
<P>

	
<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>	<!-- '2' = select units, '3' = send to selected units -->
<?php
	$query = "SELECT DISTINCT `ticket_id` , scope, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		ORDER BY `t`.`scope` ASC" ;

//	dump($query);
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if(mysql_affected_rows()>0) {
		print "<EM>Units assigned to Incident</EM>: <SELECT NAME='frm_sel_inc'>\n\t<OPTION VALUE=0 SELECTED>All incidents</OPTION>\n";
		while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
			if(!(empty($row['scope']))) {				// 6/28/09
				print "\t<OPTION VALUE='{$row['incident']}'>{$row['scope']}</OPTION>\n";
				}
			}
		}		// end if(mysql_affected_rows()>0)
?>
	</SELECT></FORM></P>
	<BR /><BR />
	<INPUT TYPE='button' VALUE='Next' onClick = "do_step_1()">&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.close();'>
	</CENTER>
	
<?php
			break;

		case 2:
			if ((!array_key_exists ( 'frm_sel_inc', $_POST)) || ($_POST['frm_sel_inc']==0)) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `contact_via` <> '' AND `contact_via` IS NOT NULL ORDER BY `name` ASC";
				}
			else {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
					LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
					WHERE `ticket_id` = {$_POST['frm_sel_inc']} AND `contact_via` <> '' AND `contact_via` IS NOT NULL ORDER BY `name` ASC";
				}
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$lines = mysql_affected_rows() +8;

?>
			<SCRIPT>
			
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
		<BODY onLoad = "reSizeScr(<?php print $lines;?>)"><CENTER>		<!-- 1/12/09 -->
			<CENTER><H3>Mail to Units</H3>
<?php
		if(mysql_affected_rows()>0) {
?>
			<SPAN ID='clr_spn' STYLE = 'display:block' onClick = 'do_clear()'>&raquo; <U>Un-check all</U></SPAN>
			<SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check()'>&raquo; <U>Check all</U></SPAN>
<?php
		}
?>
			<P>
			
			<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	<!-- '3' = select units, '3' = send to selected units -->
			<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->

<?php			
				if(mysql_affected_rows()>0) {
					$i=1;
					print "<TABLE ALIGN = 'center' BORDER=0 WIDTH=500>\n";
					while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
						print "\t<TR CLASS= '". $evenodd[($i+1)%2] . "'><TD><INPUT TYPE='checkbox' NAME='cb" .$i. "' VALUE='" . $row['contact_via'] . "' CHECKED>&nbsp;&nbsp;{$row['name']}&nbsp;&nbsp;&nbsp;&nbsp;(<I>{$row['contact_via']}</I>) </TD></TR>\n";				
						$i++;
						}		// end while()
?>
			
			<TR CLASS='<?php print $evenodd[($i+1)%2]; ?>'><TD>Subject: <INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
			<TR CLASS='<?php print $evenodd[($i)%2]; ?>'><TD>Message: <TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA></TD></TR>
			<TR CLASS='<?php print $evenodd[($i+1)%2]; ?>'><TD ALIGN='center'><BR /><BR />
				<INPUT TYPE='button' 	VALUE='Next' onClick = "do_step_2()">&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />
				</TD></TR>

<?php
				print "</TABLE></FORM>";
				
				}		// end if(mysql_affected_rows()>0)
			else {
				print "<H3>No addresses available!</H3>\n";
				print "<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />";
				}
		
			break;

		case 3:
			do_send ($_POST['frm_add_str'], $_POST['frm_subj'], $_POST['frm_text'] );	// ($to_str, $subject_str, $text_str )
?>
<BODY onLoad = "reSizeScr(2)"><CENTER>		<!-- 1/12/09 -->
<CENTER><BR /><BR /><BR /><H3>Mail sent</H3>
<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php
			break;

		default:
		    echo "error error error ";
		}

	

?>
</BODY>
</HTML>
