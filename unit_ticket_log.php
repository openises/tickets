<?php
/*
9/10/13 New File - for writing unit or ticket specific log entry
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);
if(!isset($_POST)) {
	if((isset($_GET)) && (($_GET['responder'] == 0) || ($_GET['responder'] == ""))) {
		$responder = 0;
		} elseif(!isset($_GET)) {
		$responder = 0;
		} else {
		$responder = $_GET['responder'];
		}

	if((isset($_GET)) && (($_GET['ticket'] == 0) || ($_GET['ticket'] == ""))) {
		$ticket = 0;
		} elseif(!isset($_GET)) {
		$ticket = 0;
		} else {
		$ticket = $_GET['ticket'];
		}	
	}

//dump($_POST);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets Log Processing</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="Tickets Log Entry"">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
.box { background-color: transparent; border: 0px solid #000000; color: #000000; padding: 0px; position: absolute; z-index:1000; }
.bar { background-color: #DEE3E7; color: #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; }
.content { padding: 1em; }
</STYLE>
<SCRIPT SRC="./js/misc_function.js" type="text/javascript"></SCRIPT>

<SCRIPT>
function validate_del() {
	if (document.del_form.frm_days_val.value==0) { 
		alert("check days value");
		return false;
		}
	else {
		return true;
		}
	}			// end function

function get_new_colors() {								// 4/5/11
	window.location.href = '<?php print basename(__FILE__);?>';
	}

function $() {															// 12/20/08
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
	
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}
	
function do_plain (the_id) {				// 8/21/10
	CngClass(the_id, 'plain');
	return true;
	}

</SCRIPT>
</HEAD>
<BODY>
<?php
if (empty($_POST)) {
	if (is_guest()) {
?>
		<CENTER><BR /><BR /><BR /><BR /><BR /><H3>Guests not allowed Log access. </CENTER><BR /><BR />

		<INPUT TYPE='button' value='Cancel' onClick = 'window.exit();'>
<?php
		} 
?>


	<FORM NAME="log_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>">
	<TABLE>
<?php
	if(($responder != 0) && ($ticket == 0)) {
		$theTag = get_text('Unit');
		} elseif(($ticket != 0) && ($responder == 0)) {
		$theTag = get_text('Incident');	
		} else {
		$theTag = "";
		}
?>
	<TR CLASS = 'even' ><TH COLSPAN=2><?php print $theTag;?> Log</TH></TR>
	<TR CLASS = 'odd'><TD>Log entry:</TD><TD><TEXTAREA NAME="frm_comment" COLS="70" ROWS="10" WRAP="virtual"></TEXTAREA></TD></TR>
	<TR CLASS = 'even'><TD COLSPAN=2 ALIGN='center'>
	<INPUT TYPE = 'button' VALUE='Submit' onClick="document.log_form.submit()" />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE = 'button' VALUE='Reset' onClick="document.log_form.reset()" />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE = 'button' VALUE='Cancel' onClick="window.close()" />
	</TD></TR>
	</TABLE>
	<INPUT TYPE='hidden' NAME='func' VALUE='add'>
	<INPUT TYPE='hidden' NAME='responder' VALUE=<?php print $responder;?>>
	<INPUT TYPE='hidden' NAME='ticket' VALUE=<?php print $ticket;?>>
	</FORM>
<?php 
	} else {										// not empty
	extract($_POST);
	do_log($GLOBALS['LOG_COMMENT'], $ticket, $responder, strip_tags(trim($_POST['frm_comment'])));
?>
	<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR />Log entry inserted
	<BR /><BR /><BR /><SPAN id='close_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close()" />Close</SPAN>
	</DIV>
<?php
	} 
	
?>
</BODY>
</HTML>
