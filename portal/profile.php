<?php
/*
9/10/13 - profile.php - file for view and edit of portal user request
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once '../incs/functions.inc.php';
do_login(basename(__FILE__));

$query = "SELECT id FROM `$GLOBALS[mysql_prefix]user` WHERE id='" . $_SESSION['user_id'] . "'";
if ($_SESSION['user_id'] < 0 OR check_for_rows($query) == 0) {
	print __LINE__ . " Invalid user id '$_SESSION[user_id]'.";
	exit();
	}

$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id`='$_SESSION[user_id]'";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= mysql_fetch_array($result);
$api_key = trim(get_variable('gmaps_api_key'));
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Service User Profile Edit</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<SCRIPT SRC="../js/misc_function.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT SRC='../js/md5.js'></SCRIPT>				<!-- 11/30/08 -->
<?php
	if($key_str) {
?>
		<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>libraries=geometry,weather&sensor=false"></SCRIPT>
<?php
		}
?>
	<SCRIPT>

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
			

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
			
	function go_there (where, the_id) {		//
		document.go.action = where;
		document.go.submit();
		}				// end function go there ()	
		
	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}
		
	function validate_prof(theForm) {			// profile form contents validation
		var errmsg="";
		if ((hex_md5(theForm.frm_old_password.value))!=theForm.frm_hash.value)  {
			errmsg+="\tOld Password incorrect.\n";
			}		
		if (theForm.frm_passwd.value!=theForm.frm_passwd_confirm.value)  {
			errmsg+="\tPASSWORD and CONFIRM fail to match.\n";
			}
		else {				// 8/27/10
			if ((theForm.frm_passwd.value.trim()=="") || (theForm.frm_passwd.value.trim().length<6))  {errmsg+="\tPasswd length 6 or more is required.\n";}
			}

		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.frm_hash.value = hex_md5(theForm.frm_passwd.value.trim().toLowerCase());
			theForm.frm_passwd.value = theForm.frm_passwd_confirm.value="";					// hide them
			theForm.submit();
			}
		}				// end function validate prof(theForm)
	</SCRIPT>
	</HEAD>
<?php
$get_go = (array_key_exists('go', ($_GET)))? $_GET['go']  : "" ;
if((!empty($_POST)) && ($get_go == 'true')) {
	$frm_sort_desc = array_key_exists('frm_sort_desc', ($_POST))? 1: 0 ;	// checkbox handling
	extract($_POST);
	$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `passwd`='" . $frm_hash . "' WHERE `id`=" . $_SESSION['user_id'];
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
?>
	<BODY>
		<CENTER>
		<DIV id='confirmation'>
			<BR /><BR /><BR />
			<DIV>Your password has been updated.</DIV>
			<BR /><BR />
			<SPAN id='finish_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Finish</SPAN>		
		</DIV>
		</CENTER>
	</BODY>
<?php
	} else {
	$query = "SELECT id FROM `$GLOBALS[mysql_prefix]user` WHERE id='" . $_SESSION['user_id'] . "'";
	if ($_SESSION['user_id'] < 0 OR check_for_rows($query) == 0) {
		print "Invalid user ID<BR />";
		exit();
		}

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id`=" . $_SESSION['user_id'];
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$row	= mysql_fetch_array($result);
?>
	<TABLE BORDER="0" STYLE = 'margin-left:40px'>	<!-- 8/27/10 -->
		<TR CLASS='odd'>
			<TD COLSPAN=2 ALIGN='center'><FONT CLASS="header">Change My Password</FONT><BR /><BR /></TD>
		</TR>
		<FORM METHOD="POST" ACTION="profile.php?go=true" autocomplete="off">
			<TR ALIGN='center' CLASS="odd">
				<TD CLASS="td_label">Old Password:</TD>
				<TD><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_old_password" VALUE=''></TD>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
			</TR>
			<TR CLASS="even">
				<TD CLASS="td_label">New Password:</TD>
				<TD><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd" VALUE=''> &nbsp;&nbsp;<B>Confirm: </B><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"  VALUE=''></TD>
			</TR>
			<TR CLASS="odd">
				<TD ALIGN="center" COLSPAN=2><BR />
					<INPUT TYPE="button" VALUE="Cancel"  onClick="window.close();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE="button" VALUE="Submit" onClick = validate_prof(this.form)>
				</TD>
			</TR>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $_SESSION['user_id'];?>">
			<INPUT TYPE='hidden' NAME='frm_hash' VALUE='<?php print $row['passwd'];?>'>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'];?>">
		</FORM>
	</TABLE>
<?php
		}
?>
</BODY>
</HTML>