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

$user = $_POST['dev_key'];
$url = $_POST['frm_locatea_url'];


function do_gt($user, $url) {
	
		$request_url = "http://" . $url . "/data.php?userid=$user";		//change to reflect the server address
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($request_url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error 1";		// @fopen fails
				}
			}

		$ret_array = new SimpleXMLElement($data);

	return $ret_array;

}	// end function do_gt()


	
	$ary = do_gt($_POST['dev_key'], $_POST['frm_locatea_url']) ;
//	$api_key = get_variable('gmaps_api_key');		// empty($_GET)

	$user_id = $ary->marker['userid'];
	$lat = $ary->marker['lat'];
	$lng = $ary->marker['lng'];
	$alt = $ary->marker['alt'];
	$date = $ary->marker['local_date'];
	$mph = $ary->marker['mph'];
	$kph = $ary->marker['kph'];
	$heading = $ary->marker['heading'];


	if (!($user_id)) {
?>

<FORM NAME= 'frm_locatea' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<TABLE ALIGN='center'>
<TR CLASS  = 'even'><TH COLSPAN=2>LocateA Test Fails for key: <?php print $_POST['dev_key'];?></TH></TR>

<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
	<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
</TD></TR></TABLE>

<?php
		}				// end if (fails)
	else {
?>
<FORM NAME= 'frm_locatea' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<TABLE ALIGN='center'>
<TR CLASS  = 'even'><TH COLSPAN=2>LocateA Test Succeeds for key: <?php print $_POST['dev_key'];?></TH></TR>
<TR><TD>&nbsp;</TD></TR>

<TR CLASS='odd'><TD>Device license:</TD><TD><?php print $user_id;?></TD></TR>
<TR CLASS='even'><TD>lat</TD><TD><?php print $lat;?></TD></TR>
<TR CLASS='odd'><TD>Lng:</TD><TD><?php print $lng;?></TD></TR>
<TR CLASS='even'><TD>Course:</TD><TD><?php print $heading;?></TD></TR>
<TR CLASS='odd'><TD>MPH:</TD><TD><?php print $mph;?></TD></TR>
<TR CLASS='even'><TD>KPH:</TD><TD><?php print $kph;?></TD></TR>
<TR CLASS='odd'><TD>Alt:</TD><TD><?php print $alt;?></TD></TR>
<TR ><TD COLSPAN = 2 ALIGN='center'><HR SIZE=1 COLOR='blue'WIDTH='75%'></TD</TR>

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
<TR CLASS  = 'even'><TH COLSPAN=2>Locatea Test</TH></TR>
<FORM NAME= 'frm_locatea' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
</TD></TR>
<TR CLASS  = 'odd'><TD>
License key:
</TD><TD>
	<INPUT NAME = 'dev_key' TYPE = 'text' SIZE = '30' VALUE=''>	<BR /><BR />
</TD></TR>
<TR CLASS  = 'even'><TD COLSPAN=2 ALIGN='center'>
	<INPUT TYPE='button' VALUE = 'Test' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
	<INPUT TYPE='hidden' NAME = 'frm_locatea_url' SIZE = '40' value='www.locatea.net'>

</TD></TR></TABLE>
<?php
	}		// end else {}
?>	

</BODY>
</HTML>
