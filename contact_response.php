<?php 
error_reporting(E_ALL);		// 10/1/08

/*
12/24/14 safe contact form
*/
@session_start();	
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10
extract($_GET);
$theName = base64_decode($nx);
$theEmail = base64_decode($ex);
$theSec = base64_decode($sx);
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]access_requests` WHERE `name` = '" . $theName . "' AND `email` = '" . $theEmail . "' AND `sec_code` = '" . $theSec . "' LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
$rows = mysql_num_rows($result);
if($rows > 0) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$success = true;
	$theID = $row['id'];
	$query1	= "DELETE FROM `$GLOBALS[mysql_prefix]access_requests` WHERE `id` = " . $theID;
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$theText = "Thank you, your request has been submitted<BR />";
	} else {
	$success = false;
	$theText = "There was a problem with the data submitted please try to request access again<BR />";
	}
if($success) {
	$textStr = "Access Request received from " . $theName . "\r\n\r\nEMAIL: " . $theEmail . "\r\nPHONE: " . $row['phone'] . "\r\nREASON FOR REQUEST: " . $row['reason'] . "\r\n";
	$contact_add = get_contact_addr();
	do_send($contact_add, "", "Tickets Access Request", $textStr, 0, 0, 0, NULL);
	}
?> 
<!DOCTYPE html>
<html>
<HEAD>
<TITLE>Tickets - Contact and request access form</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="8/24/08" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE type="text/css">	
INPUT {FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none;}
SELECT {FONT-WEIGHT: normal; FONT-SIZE: 100%; COLOR: #000000; FONT-STYLE: normal; height: 20px; cursor: pointer;}
OPTION {FONT-WEIGHT: normal; FONT-SIZE: 100%; COLOR: #000000; FONT-STYLE: normal; height: 20px; cursor: pointer;}
FIELDSET {margin: 0 0 20px; padding: 10px; border: 3px inset #FFFFFF; border-radius: 20px 20px;}
LABEL {width: 40%; display: inline-block; vertical-align: top; font-weight: bold; padding: 5px; text-align: left;}
LEGEND {font-weight: bold; font-size: 14px; padding: 5px; background: #0000FF; border: 3px inset #FFFFFF; color: #FFFFFF; border-radius: 20px 20px; }
TEXTAREA {clear: both;	font-size: 1em;}
</STYLE>
<script src="./js/misc_function.js" type="application/x-javascript"></script>	
</HEAD>
<BODY> 
	<DIV id='outer'>
		<DIV ID='titlebar'>
			<TABLE ALIGN='left'>
				<TR VALIGN='top'>
					<TD ROWSPAN=4><IMG SRC="<?php print get_variable('logo');?>" BORDER=0 /></TD>
					<TD>
<?php

						$temp = get_variable('_version');
						$version_ary = explode ( "-", $temp, 2);
						if(get_variable('title_string')=="") {
							$title_string = "<FONT SIZE='3'>ickets " . trim($version_ary[0]) . " on <B>" . get_variable('host') . "</B></FONT>";
							} else {
							$title_string = "<FONT SIZE='3'><B>ickets - " .get_variable('title_string') . "</B></FONT>";
							}
						print $title_string;
?>
					</TD>
				</TR>
			</TABLE>
		</DIV><BR />
		<DIV id='title' class='header' style='position: absolute; top: 80px; left: 0px; width: 100%; text-align: center;'>Contact us to request Login Details</DIV><BR /><BR /><BR />
		<DIV id='contact_form' STYLE='position: relative; top: 20px;'><BR /><BR /><BR />
			<DIV id='theForm'  style='position: relative; left: 25%; top: 20px; width: 50%; text-align: center; font-size: 12px; margin: 10px; border: 3px outset #646464;'><BR /><BR />
				<?php print $theText;?> 
			</DIV>
		</DIV>
	</DIV>
</BODY>
</HTML>