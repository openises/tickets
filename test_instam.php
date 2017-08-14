<?php
/*
7/25/09	initial release
1/21/10 captions changed to conform to IM usage
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');

/*
log_error(basename(__FILE__), __LINE__);			// 2/12/2014 - debug function_exists('imap_open')

get_current() ;

log_error(basename(__FILE__), __LINE__);			// 2/12/2014 - debug function_exists('imap_open')
*/
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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
</HEAD>
<BODY onload = "if (document.frm_instam) {document.frm_instam.dev_key.focus();}">

<?php

if (!(empty($_POST))) {
		function test_instam($key) {		// returns array, or FALSE
			$start_at = time();			

			// snap(basename(__FILE__) . __LINE__, $key_val);

			$the_url = "http://www.insta-mapper.com/api/api_single.php?device_id={$key}";
		
			$data="";
			if (function_exists("curl_init")) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $the_url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec ($ch);
				curl_close ($ch);
				}
			else {				// not CURL
				if ($fp = @fopen($the_url, "r")) {
					while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
					fclose($fp);
					}		
				else {
//					print "-error 1";		// @fopen fails
					return FALSE;
					}
				}
					
			$elapsed = time() - $start_at;

			if (is_null (json_decode($data ) ) ) {
				echo "<br /><center><h3>Instamapper server says:</h3></center><br />";
				echo "<br /><center><h3><i>{$data}</i></h3></center><br />";
				return false;
				}			
			else {
				$data = get_remote($the_url, FALSE);		// no JSON decode - 4/23/11
				$arr = @json_decode( $data );
//				dump(gettype($arr));
				
				$temp = @get_object_vars($arr[0]);	

				echo "<center>\n<table style = 'margin-top: 6px;'>";
				echo "<tr><td colspan=2 align='center'><h3>Results</h3></td></tr>";
				foreach ($arr[0] as $key => $value) {
				    echo "<tr><td>{$key}</td><td>{$value}</td></tr>\n";
					}
				echo "<tr><td colspan=2 align='center'><h3>Time: {$elapsed} seconds</h3></td></tr>";
				echo "</table>";
				
				$ary_data = explode ("\n", $data);
				return $ary_data;
				}
		}		// end function
	
	$ary = test_instam(trim($_POST['dev_key'])) ;
	if (!($ary)) {
?>

<FORM NAME= 'frm_instam' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<TABLE ALIGN='center'>
<TR CLASS  = 'even'><TH COLSPAN=2>Instamapper Test Fails for key: <?php print $_POST['dev_key'];?></TH></TR>

<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
	<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
</TD></TR></TABLE>

<?php
		}				// end if (fails)
	else {
?>
<FORM NAME= 'frm_instam' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<TABLE ALIGN='center' style = "margin-top:40px;">
<TR CLASS  = 'even'><TH COLSPAN=2>Instamapper test succeeds for key: <?php print $_POST['dev_key'];?></TH></TR>
<TR><TD>&nbsp;</TD></TR>
<?php
for ($i = 1; $i<(count($ary) - 2); $i++) {
	$tmp_ary = explode (",", $ary[$i]);
?>
<TR CLASS='odd'><TD>Device key:</TD><TD><?php print $tmp_ary[0];?></TD></TR>
<TR CLASS='even'><TD>Name</TD><TD><?php print $tmp_ary[1];?></TD></TR>
<TR CLASS='odd'><TD>Time</TD><TD><?php print format_date($tmp_ary[2]);?></TD></TR>
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
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
</TD></TR></TABLE>

<?php	
	
		}		// end else {}
	
	}			// end if (!(empty($_POST))) 
else {
?>
<TABLE id = 'in_table' ALIGN = 'center' cellpadding = 4 BORDER = 0>
<TR CLASS  = 'even'><TH COLSPAN=2>Instamapper Test</TH></TR>
<FORM NAME= 'frm_instam' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
</TD></TR>
<TR CLASS  = 'odd'><TD>API key:</TD>
<TD>
	<INPUT NAME = 'dev_key' TYPE = 'text' SIZE = '30' VALUE=''>	<BR /><BR />
</TD></TR>
<TR CLASS  = 'even'><TD COLSPAN=2 ALIGN='center'>
	<INPUT TYPE='button' VALUE = 'Test' onClick = 'document.getElementById("spinner").style.display = "";document.getElementById("in_table").style.display = "none"; this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
</TD></TR></TABLE>
<center>
<img id = "spinner" src = "./images/animated_spinner.gif" style = "margin-top:100px; display:none">
<?php
	}		// end else {}
?>	

</BODY>
</HTML>
