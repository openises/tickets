<?php
/*
7/25/09	initial release
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Test Instamapper</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
</HEAD>
<BODY>

<?php

if (!(empty($_POST))) {

		function test_instam($key) {		// returns array, or FALSE

			// snap(basename(__FILE__) . __LINE__, $key_val);
			// http://www.instamapper.com/api?action=getPositions&key=4899336036773934943

			$url = "http://www.instamapper.com/api?action=getPositions&key={$key}";
		
			$data="";
			if (function_exists("curl_init")) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec ($ch);
				curl_close ($ch);
				}
			else {				// not CURL
				if ($fp = @fopen($url, "r")) {
					while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
					fclose($fp);
					}		
				else {
//					print "-error 1";		// @fopen fails
					return FALSE;
					}
				}
					
			/*
			InstaMapper API v1.00
			1263013328977,bold,1236239763,34.07413,-118.34940,25.0,0.0,335
			1088203381874,CABOLD,1236255869,34.07701,-118.35262,27.0,0.4,72
			*/
//			dump($data);			
			$ary_data = explode ("\n", $data);
			return $ary_data;
		}		// end function
	
	$ary = test_instam($_POST['dev_key']) ;
	if (!($ary)) {
?>

<FORM NAME= 'frm_instam' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<TABLE ALIGN='center'>
<TR CLASS  = 'even'><TH COLSPAN=2>Instamapper Test Fails for key: <?php print $_POST['dev_key'];?></TH></TR>

<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
	<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
</TD></TR></TABLE>

<?php
		}				// end if (fails)
	else {
?>
<FORM NAME= 'frm_instam' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<TABLE ALIGN='center'>
<TR CLASS  = 'even'><TH COLSPAN=2>Instamapper Test Succeeds for key: <?php print $_POST['dev_key'];?></TH></TR>
<TR><TD>&nbsp;</TD></TR>
<?php
for ($i = 1; $i<(count($ary) - 2); $i++) {
	$tmp_ary = explode (",", $ary[$i]);
?>
<TR CLASS='odd'><TD>Device license:</TD><TD><?php print $tmp_ary[0];?></TD></TR>
<TR CLASS='even'><TD>Name</TD><TD><?php print $tmp_ary[1];?></TD></TR>
<TR CLASS='odd'><TD>Time</TD><TD><?php print $tmp_ary[2];?></TD></TR>
<TR CLASS='even'><TD>lat</TD><TD><?php print $tmp_ary[3];?></TD></TR>
<TR CLASS='odd'><TD>Lng:</TD><TD><?php print $tmp_ary[4];?></TD></TR>
<TR CLASS='even'><TD>Course:</TD><TD><?php print $tmp_ary[5];?></TD></TR>
<TR CLASS='odd'><TD>Speed:</TD><TD><?php print $tmp_ary[6];?></TD></TR>
<TR CLASS='even'><TD>Alt:</TD><TD><?php print $tmp_ary[7];?></TD></TR>
<TR ><TD COLSPAN = 2 ALIGN='center'><HR SIZE=1 COLOR='blue'WIDTH='75%'></TD</TR>

<?php
	}
?>

<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
	<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
</TD></TR></TABLE>

<?php	
	
		}		// end else {}
	
	}			// end if (!(empty($_POST))) 
else {
?>
<TABLE ALIGN = 'center' cellpadding = 4 BORDER = 0>
<TR CLASS  = 'even'><TH COLSPAN=2>Instamapper Test</TH></TR>
<FORM NAME= 'frm_instam' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
</TD></TR>
<TR CLASS  = 'odd'><TD>
License key:
</TD><TD>
	<INPUT NAME = 'dev_key' TYPE = 'text' SIZE = '30' VALUE=''>	<BR /><BR />
</TD></TR>
<TR CLASS  = 'even'><TD COLSPAN=2 ALIGN='center'>
	<INPUT TYPE='button' VALUE = 'Test' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
</TD></TR></TABLE>
<?php
	}		// end else {}
?>	

</BODY>
</HTML>
