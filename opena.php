<?php
/*
9/15/08	 initial release
10/1/08	 added error reporting call
10/1/08	 relocated variable extract
3/15/11 changed stylesheet.php to stylesheet.php
6/10/2013 revised to extract json object attrib's
*/
error_reporting(E_ALL);				//10/1/08
require_once('./incs/functions.inc.php');		//7/28/10

$call = (empty($_GET))? "": $_GET['frm_call'];				// 10/1/08
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Test APRS</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
td { background-color: inherit; FONT-WEIGHT: normal; FONT-SIZE: x-small; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Courier new, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; VERTICAL-ALIGN: top;  }

</STYLE>
<SCRIPT>
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function do_focus() {
<?php
$the_key = trim(get_variable('aprs_fi_key'));
if (empty($the_key)) {print "\n\tdocument.aprs_form.frm_key.focus();\n";}
	else 			 {print "\n\tdocument.aprs_form.frm_call.focus();\n";}
?>
	}		// end function do_focus()
</SCRIPT>
	
</HEAD>
<BODY onLoad = "do_focus();">
<CENTER><BR /><BR />
<H3>APRS CALLSIGN TEST - <?php print $call;?></H3>
(data via aprs.fi)<BR /><BR />
<?php
if (!empty($_GET)) {
	$call_str = $_GET['frm_call'];
	$key_str =  $_GET['frm_key'];
	$the_url = "http://api.aprs.fi/api/get?name={$call_str}&what=loc&apikey={$key_str}&format=json";
//		Call is VE6GG  API Key is 30429.mLY7wgrnfT2R6D
	$data=get_remote($the_url, TRUE);				// returns JSON-decoded values, ($url, $json=TRUE) {				// 11/26/10	, 4/23/11
	$temp = $data->result;
	if (strtoupper($temp) != "OK"){
		print "<BR /><H3>Test fails!  \"{$data->{'description'}}\"</H3><BR /><BR />";
		}
	else {
		print "<BR /><H3>Test with aprs.fi succeeds!</H3>";
		$temp = ($data->found );			// match count
		if($temp==0) {
			print "<BR /><H3>No data for '{$_GET['frm_call']}'</H3><BR />";		
			}
		else {			
			$entry = (object) $data->entries[0];
			$lat = 			(property_exists($entry, 'lat')) ? 		$entry->lat			: "na"; 
			$lng = 			(property_exists($entry, 'lng')) ? 		$entry->lng			: "na"; 
			$updated = 		(property_exists($entry, 'time')) ? 	$entry->time		: "na"; 
			$course = 		(property_exists($entry, 'course')) ?	$entry->course		: "na"; 
			$mph = 			(property_exists($entry, 'speed')) ?	$entry->speed		: "na"; 
			$alt = 			(property_exists($entry, 'altitude')) ? $entry->altitude	: "na"; 
			$callsign_in = 	(property_exists($entry, 'name')) ? 	$entry->name		: "na"; 			
			$p_d_timestamp = (property_exists($entry, 'lasttime'))? mysql_format_date($entry->lasttime) : "na";		// to timestamp	

			print "Lat: {$lat}, Long: {$lng}, Time: {$p_d_timestamp}, Course: {$course}, Speed: {$mph}<BR><BR><BR><BR>" ; 
			}
		}			// end if/else OK
	}	// end if (!empty($_GET))
	
$the_key = trim(get_variable('aprs_fi_key'));
?>
<FORM NAME = "aprs_form" METHOD="get" ACTION="<?php print basename(__FILE__); ?>">
<B>aprs.fi key:&nbsp;&nbsp;</B><INPUT TYPE="text" NAME="frm_key" SIZE="30" VALUE="<?php print trim($the_key);?>" /><BR /><BR /><BR />
<B>Callsign:&nbsp;&nbsp;</B> <INPUT TYPE="text" NAME="frm_call" SIZE="16" VALUE="" /><BR /><BR /><BR />
<INPUT TYPE="button" VALUE = "Finished" onClick = "self.close()">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
<INPUT TYPE="submit" VALUE='Do test'> 
</BODY></HTML>
