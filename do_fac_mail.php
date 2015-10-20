<?php
/*
8/17/09	initial release - mail to facility
7/16/10 added check for no addressees
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		//

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
$evenodd = array ("even", "odd");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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

//dump($_POST);

if (empty($_POST)) {

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
	
	function reSizeScr(lines){
		var the_width = 720;
		var the_height = ((lines * 21)+400);				// values derived via trial/error (more of the latter, mostly)
		window.resizeTo(the_width,the_height);	
		}

	function validate() {
		var addr_err = true;
			for (i=0; i< document.mail_form.length; i++) {
			 	if ((document.mail_form.elements[i].name.substring(0, 2) == 'cb') && (document.mail_form.elements[i].checked)) {
			 		addr_err = false;
		 			}
		 		}
	
		var errmsg="";
		if (addr_err) 									  {errmsg+="One or more addresses required\n";}
		if (document.mail_form.frm_subj.value.trim()=="") {errmsg+="Message subject is required\n";}
		if (document.mail_form.frm_text.value.trim()=="") {errmsg+="Message text is required\n";}
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
	
<?php

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ";		// (array_key_exists('first', $search_array)) 
	$query .= (array_key_exists('fac_id', $_GET))? " WHERE `id` = " . quote_smart(trim($_GET['fac_id'])) . " LIMIT 1": "";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//	dump($query);
?>
	<BODY onLoad = "reSizeScr(<?php print mysql_affected_rows();?>)"><CENTER>		<!-- 1/12/09 -->

	<CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail Facilities </H3>
<?PHP
	if (mysql_affected_rows()>0) {
		print "<FORM NAME='mail_form' METHOD='post' ACTION='" . basename(__FILE__) . "'>\n";
		print "<TABLE BORDER = 0 ALIGN='center'>\n";
		$i = 0;
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			if (is_email($row['contact_email'])) {
				print "<TR CLASS = '{$evenodd[($i%2)]}'><TD><INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$row['contact_email']}' CHECKED></TD>
					<TD>{$row['name']}</TD><TD>{$row['contact_name']}</TD><TD>{$row['contact_email']}</TD><TD></TD></TR>\n";
				$i++;
				}
			if (is_email($row['security_email'])) {
				print "<TR CLASS = '{$evenodd[($i%2)]}'><TD><INPUT TYPE='checkbox' NAME='cb" .$i. "' VALUE='" . $row['security_email'] . "' CHECKED></TD>
					<TD>{$row['name']}</TD><TD>{$row['security_contact']}</TD><TD>{$row['security_email']}</TD><TD></TD></TR>\n";
				$i++;
				}	// end if (is_email)
			}		// end while()
		}				// end if (mysql_affected_rows()>0) 

	if ($i > 0 ) {							// 7/16/10
				
?>
		<TR><TD COLSPAN=5>&nbsp;</TD></TR>	
		<TR CLASS='even'><TD ALIGN='right'>Subject: </TD><TD COLSPAN=4><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
		<TR CLASS='odd'><TD ALIGN='right'>Message:</TD><TD COLSPAN=4> <TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA></TD></TR>
		<TR CLASS='even'><TD></TD><TD ALIGN='left' COLSPAN=3><BR /><BR />
			<INPUT TYPE='button' 	VALUE='Send' onClick = "validate()"  STYLE =  'margin-left: 100px'>
			<INPUT TYPE='reset' 	VALUE='Reset' STYLE =  'margin-left: 20px'>
			<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'STYLE =  'margin-left: 20px'><BR /><BR />
			</TD></TR>
			</TABLE></FORM>
<?php
		}		// end if ($i > 0 )
	else {
?>
		<BR /><H3>No facility addresses available</H3><BR /><BR />
		<INPUT TYPE='button'  VALUE = 'Close' onClick='window.close();' />
<?php
	
		}		// end if/else end if ($i > 0 )
		
		}		// end if (empty($_POST)) {

	else {
		$addr_str = $sep = "";
		foreach ($_POST as $VarName=>$VarValue) {
			if (substr($VarName, 0, 2) == 'cb') {
				$addr_str .= $sep . $VarValue;
				$sep = "|";
				}				// end if
			}		// end foreach
		do_send ($addr_str, $_POST['frm_subj'], $_POST['frm_text'], 0, 0);	// ($to_str, $subject_str, $text_str ) - | separator
?>
	<BODY>
	<CENTER><BR /><BR /><BR /><H3>Mail sent</H3>
	<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php

	}		// end else
?>
</BODY>
</HTML>
