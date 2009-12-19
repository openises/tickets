<?php
/*
2/18/09 initial release
2/28/09 added email addr validation
7/12/09	general cleanup, added subject handling
*/
$pre_pend = "";					// 7/12/09, prepend to message subject line, user values here
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
//dump($_GET);
//dump($_POST);
if (empty($_POST)) {
	$query = "SELECT `id`, `scope` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$_GET['ticket_id']} LIMIT 1";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = mysql_fetch_assoc($result);
	unset($result);
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Message Edit</TITLE>
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT>

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};

function reSizeScr(lines) {
	window.resizeTo(640,((lines * 18)+250));		// derived via trial/error (more of the latter, mostly)
	}

function addrcheck(str) {
	var at="@"
	var dot="."
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
	if (str.indexOf(at)==-1)													{return false;}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr) 	{return false;}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr)	{return false;}
	if (str.indexOf(at,(lat+1))!=-1)											{return false;}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot)		{return false;}
	if (str.indexOf(dot,(lat+2))==-1)											{return false;}
	if (str.indexOf(" ")!=-1)													{return false;}
	return true					
	}
var temp;
var lines;

function do_val(theForm) {										// 2/28/09
	if (theForm.frm_addrs.value.trim() == "") {
		alert("Addressee required");
		return false;
		}
		
	temp = theForm.frm_addrs.value.trim().split("|");			// explode to array
	var emerr = false;
	for (i=0; i<temp.length; i++) {								// check each addr
		if (!(addrcheck(temp[i].trim()))) {
			emerr = true;
			}
		}
	if (emerr) {
		alert("Valid addressee email required");
		return false;
		}
		
	if (theForm.frm_text.value.trim() == "") {
		alert("Message text required");
		return false;
		}
	theForm.submit();
	}
	

<?php
	if (empty($_POST)) {
		$text = mail_it ($_GET['addrs'], $_GET['text'], $_GET['ticket_id'], 3, TRUE) ;		// returns msg text **ONLY**
		dump($text);
		$lines = count(explode("\n", $text));		// 7/12/09
?>

</SCRIPT>
</HEAD>

<BODY onLoad = "reSizeScr(<?php print $lines;?>)";><CENTER>
<H3>Edit message to suit</H3>
<FORM NAME="mail_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
<TABLE ALIGN='center' BORDER=0>
<TR CLASS='even'><TD COLSPAN=2>
		<TEXTAREA NAME="frm_text" COLS=60 ROWS=<?php print $lines; ?>><?php print $text ;?></TEXTAREA> <!-- allow four add'l lines -->
	</TD></TR>
<TR><TD CLASS='odd'>Subject:</TD><TD><INPUT TYPE='text' NAME= 'frm_subj' size = 60 MAXLENGTH=60 VALUE='<?php print $row['scope'];?>' /></TD></TR>
<TR CLASS='even'><TD>Addressed to: </TD><TD><INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE='<?php print $_GET['addrs'];?>'>
	</TD></TR>
<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><EM> note '</EM><B>|</B><EM>' separator</EM></TD></TR>
<TR CLASS='even'><TD COLSPAN=2 ALIGN = 'center'>
<INPUT TYPE="button" VALUE="OK - mail this" onClick = "do_val(document.mail_frm);" />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE="button" VALUE="Reset" onClick = "document.mail_frm.reset(); /">&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE="button" VALUE="Dont send" onClick = "javascript: if(confirm('Confirm do not send?')) {window.close()};" />
	</TD></TR></TABLE>
</FORM>

<?php
	}		// end if (empty($_POST))

else {
	snap(basename(__FILE__), __LINE__ );
	do_send ($_POST['frm_addrs'], $_POST['frm_subj'],  $_POST['frm_text'] );		// ($to_str, $subject_str, $text_str ) 7/13/09
?>
</SCRIPT>
</HEAD>

<BODY onLoad = "setTimeout('window.close()',1000);"><CENTER>
<BR /><BR /><H3>Emailing dispatch notifications</H3><P>
<?php
	}				// end else
?>
</CENTER></BODY>
</HTML>