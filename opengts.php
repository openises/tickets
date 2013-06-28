<?php
/*
7/4/11	initial release
4/20/12 fix to accommodate empty json element, per KB email
*/
error_reporting(E_ALL);

require_once('incs/functions.inc.php');		//7/28/10
if (empty($_POST)) {$_POST['_func']= "form";}

extract ($_POST);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Test OpenGTS</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
</HEAD>
<BODY>

<?php
function customError($errno, $errstr)
  {
  echo "Error:</b> [$errno] $errstr<br /><br />";
  echo "<b>Data format error - Ending Script</b><br /><br /><br /><br />";
  die();
  } 
set_error_handler("customError"); 
		function test_opengts( $_url, $_account, $_pw ) {		// returns array, or FALSE
//			target	http://track.kmbnet.net:8080/events/data.json?a=sysadmin&p=12test34&g=all&limit=1

			$url = "http://{$_url}/events/data.jsonx?a={$_account}&p={$_pw}&g=all&limit=1";

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
					return "- connect error";
					}
				}
			if (strpos ( $data, "Invalid")) return "- Account/Password error";
			
			if (!($data)) return "- connect error " . __LINE__;
//			dump($url);
//			dump($data);
//			$data = utf8_decode($data);
//			dump(mb_check_encoding ( $data, 'UTF-8'));
			$jsonresp = json_decode ($data, true); 		// 
//			dump($jsonresp);
			$result = json_last_error();
			if (($result != JSON_ERROR_NONE) || (!(is_array($jsonresp)))) {return " - data error " . __LINE__;}
			if (strpos ( $data, "Invalid device")) return "- device error";
//			dump(gettype($jsonresp));
			
			foreach ($jsonresp["DeviceList"] as $device) {
				if (!(empty($device['EventData'][0]))) {				// 4/20/12
//					dump($device["EventData"][0]["Timestamp"]);
					echo "<TABLE ALIGN='center'BORDER = 1 STYLE = 'margin-top:20px;'>\n";
					foreach ($device["EventData"][0] as $key => $value) {
					    echo "<TR><TD>{$key}</TD><TD>{$value}</TD></TR>\n";
						}	// 			// end inner foreach ()
					echo "<TABLE >";
	
					echo "<br />\n";
					}		// end if (!(empty(...)))
				}				// end outer foreach ()
			return "" ;		// good data
		}		// end function test opengts()

//if (!(empty($_POST))) {
switch ($_func) {
	case("test") :
	$temp = test_opengts( trim($frm_url), trim($frm_account), trim($frm_pw)) ;
	if ($temp) {
?>

<FORM NAME= 'frm_og' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<input type = hidden name = '_func' value = 'test'>
<TABLE ALIGN='center' STYLE = 'margin-top:40px;'>
<TR CLASS  = 'even'>
	<TH COLSPAN=2><?php echo "OpenGTS Test Fails for<br /><br /> 
	URL: '{$_POST['frm_url']}',  
	Account:'{$_POST['frm_account']}',  
	PW:'{$_POST['frm_pw']}'
	<br /><br />{$temp}
	";?>	
	</TH></TR>

<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
	<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
</TD></TR></TABLE>
</FORM>

<?php
		}				// end if (fails)
	else {
?>
<FORM NAME= 'frm_og' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
<input type = hidden name = '_func' value = 'form'>
<TABLE ALIGN='center' STYLE = 'margin-top:40px;'>
<TR CLASS  = 'even'><TD COLSPAN=2 ALIGN='center'>
	<B><?php echo "OpenGTS Test succeeds for <br /><br />URL: '{$_POST['frm_url']}',  Account: '{$_POST['frm_account']}', PW:'{$_POST['frm_pw']}'";?></B>
	</TD></TR>
<TR><TD>&nbsp;</TD></TR>
<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
	<INPUT TYPE='button' VALUE = 'Save' onClick = 'document.frm_save.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
</TD></TR></TABLE>
</FORM>
<FORM NAME= 'frm_save' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
	<INPUT TYPE='hidden' NAME = '_func'		 	VALUE = 'save' />
	<INPUT TYPE='hidden' NAME = 'frm_url' 		VALUE = '<?php echo trim($frm_url);?>' />
	<INPUT TYPE='hidden' NAME = 'frm_account' 	VALUE = '<?php echo trim($frm_account);?>' />
	<INPUT TYPE='hidden' NAME = 'frm_pw' 		VALUE = '<?php echo trim($frm_pw);?>' />
	</FORM>

<?php		
		}		// end if/else {fails}
	
	break;
case("form") :

	$init_vals = explode("/", get_variable('ogts_info'));
	$init_url =		trim($init_vals[0]);
	$init_acct = 	(count($init_vals) > 1)? trim($init_vals[1]) : "" ;
	$init_pw =  	(count($init_vals) > 2)? trim($init_vals[2]) : "" ;

?>
<TABLE ALIGN = 'center' cellpadding = 4 BORDER = 0 STYLE = 'margin-top:40px;'>
<TR CLASS  = 'even'><TD COLSPAN=2 align = 'center'><B>OpenGTS Test - enter/revise test values</B></TD></TR>
<FORM NAME= 'frm_og' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
	</TD></TR>
<TR CLASS  = 'odd'><TD>&nbsp;</TD></TR>	
<TR CLASS  = 'odd'>
	<TD>Server URL:</TD>
	<TD><INPUT NAME = 'frm_url' TYPE = 'text' SIZE = '60' VALUE='<?php echo $init_url;?>'></TD>	
	</TR>
<TR CLASS = 'even'>
	<TD>Account:</TD>
	<TD><INPUT NAME = 'frm_account' TYPE = 'text' SIZE = '20' VALUE='<?php echo $init_acct;?>'></TD>	
	</TR>
<TR CLASS  = 'odd'>
	<TD>Password:</TD>
	<TD><INPUT NAME = 'frm_pw' TYPE = 'text' SIZE = '20' VALUE='<?php echo $init_pw;?>'></TD>	
	</TR>
<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR />
	<INPUT TYPE='button' VALUE = 'Run test' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
	<INPUT TYPE='hidden' NAME = '_func' VALUE = 'test' />
	</FORM>
	
</TD></TR></TABLE>
<?php
	break;
case("save") :
	$setting_val = "{$frm_url}/{$frm_account}/{$frm_pw}";
	$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$setting_val' WHERE `name` = 'ogts_info'";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
?>
<CENTER><BR /><BR />
	<H2>OpenGTS test values saved as settings</H2><BR><BR>
	<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close();' />
	</CENTER>	

<?php
	break;
default :
	echo "ERROR " . __LINE__ . __LINE__ . __LINE__ . __LINE__ . __LINE__;

}		// END switch ($_POST['_func'])

?>	

</BODY>
</HTML>
