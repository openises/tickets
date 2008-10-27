<?php
/*
9/15/08	 initial release
10/1/08	 added error reporting call
10/1/08	 relocated variable extract
*/
error_reporting(E_ALL);				//10/1/08
$call = (empty($_GET))? "": $_GET['call'];				// 10/1/08

function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}
$evenodd = array ("even", "odd" );	// CLASS names for alternating table row colors
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Test APRS</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<STYLE>
td { background-color: inherit; FONT-WEIGHT: normal; FONT-SIZE: x-small; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Courier new, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; VERTICAL-ALIGN: top;  }

</STYLE>
<BODY onLoad = "document.frm_call.call.focus();">
<CENTER>
<H3>APRS CALLSIGN TEST - <?php print $call;?></H3>
(data via aprsworld.net)<BR /><BR />
<?php
if (!empty($_GET)) {
	$call = $_GET['call'];
//	$call = "K8RDK-8";
	
	$url = "http://db.aprsworld.net/datamart/csv.php?call=". trim($call);
	
	$raw="";		
	if ($fp = @fopen($url, r)) {		
		while (!feof($fp)) $raw .= fgets($fp, 128);	
			fclose($fp);					
			}
	$raw = str_replace("\r",'',$raw);							// strip cr's
	$raw = str_replace ( '"', '', $raw);
	$data = explode (",",  $raw , 50 );							// break each line	str_replace ( mixed search, mixed replace, mixed subject [, int &count] )
	if (count($data)< 28){
		print "<BR /><H3>Fails!</H3><BR /><BR />";
		}
	else {
		print "<TABLE ALIGN='center'>";
		print "<TR><TH>Succeeds<BR /></TH></TR>";

		for ($i=0;$i<count($data); $i++) {
			print "<TR CLASS='" . $evenodd[$i%2] . "'><TD ALIGN='left'>" . $data[$i] . "</TD></TR>";
			}
		print "</TABLE><BR /><BR /><BR />";
		}
//	dump($data);	
	}		// end if (isset($_GET))
?>
<FORM NAME = "frm_call" METHOD="get" ACTION="<?php print basename(__FILE__); ?>">
Enter Callsign: <INPUT TYPE="text" NAME="call" LENGTH="10" /><BR /><BR /><BR />
<INPUT TYPE="submit" VALUE='Do test'> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
<INPUT TYPE="button" VALUE = "Finished" onClick = "self.close()">
</BODY></HTML>