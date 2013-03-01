<?php
/*
8/7/08	initial release - replaces a Google search
1/18/09 POST replace GET
1/26/09 added functions.inc, get_variable for wp key 
10/1/09	revised return string to include match count as initial entry
3/13/10 constituents table handling added
4/30/10 accommodate add'l phone fields
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/6/10  Added test for internet available
9/2/10 corrected test for internet available
9/30/10 fix per JB email
*/

@session_start();
require_once('incs/functions.inc.php');		//7/28/10

$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]caller_id` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `call_str` varchar(256) NOT NULL,
		  `lookup_vals` varchar(1024) NOT NULL,
		  `status` int(2) NOT NULL,
		  `_by` int(7) NOT NULL DEFAULT '0',
		  `_from` varchar(16) DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

$query	= "DELETE FROM `$GLOBALS[mysql_prefix]caller_id` WHERE `_on` < (NOW() - INTERVAL 7 DAY)";		 // remove if older than one week
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

		function get_wp_data ($instr, $source_id, $the_phone) {
			global $apartment, $misc;
			$wp_key = "729c1a751fd3d2428cfe2a7b43442c64";				// 
			$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=" . urlencode($instr) . ";api_key={$wp_key};outputtype=JSON";

			$resp_data = "";
			if (function_exists("curl_init")) {		// got CURL?
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$resp_data = curl_exec ($ch);
				curl_close ($ch);
				}
			else {									// no CURL
				if ($fp = @fopen($url, "r")) {
					while (!feof($fp) && (strlen($resp_data)<9000)) $resp_data .= fgets($fp, 128);
					fclose($fp);
					}		
				else {
					return FALSE;
					}
				}
			$jsonresp = json_decode ($resp_data, true); 		// to array

			if(@$jsonresp["errors"]) {
//				dump(__LINE__);
				$error_str = "0;;{$the_phone};;;;;;;;0;";
				return $error_str;}						// ! empty => errors
			
			if(array_key_exists ( "business", $jsonresp['listings'][0])) 
				{$name = $jsonresp['listings'][0]["business"]["businessname"];}
			else {
				$name = (array_key_exists ( "people", $jsonresp['listings'][0]))  ?			
					"{$jsonresp['listings'][0]['people'][0]['lastname']},
					 {$jsonresp['listings'][0]['people'][0]['firstname']}
					 {$jsonresp['listings'][0]['people'][0]['middlename']}":
					 "";
				}

			$outStr = "0;";																	// priors
			$outStr .= "{$name};";															// name
			$outStr .= extr_digits($the_phone) . ";";										// phone
																							// street
			$outStr .= (array_key_exists ( "fullstreet", $jsonresp['listings'][0]["address"])) ?
				$jsonresp['listings'][0]["address"]["fullstreet"] . ";":
				";";

			$outStr .= $jsonresp['listings'][0]["address"]["city"]	. ";";					// city 
			$outStr .= $jsonresp['listings'][0]["address"]["state"] . ";";					// state 	
			$outStr .= ";";								// zip - unused 
			$outStr .= $jsonresp['listings'][0]["geodata"]["latitude"]	. ";"; 
			$outStr .= $jsonresp['listings'][0]["geodata"]["longitude"]	. ";"; 
			$outStr .=  ";"; 			// misc placeholder
			$outStr .=$source_id . ";"; 			//
			return 	$outStr;						// end function do_the_row()

			}				// end function get wp_data ()

	function cid_lookup($phone)  {
		$aptStr = " Apt:";															
		function do_the_row($inRow, $source_id, $the_phone ) {		// for ticket or constituents data
			global $apartment, $misc;
			$outStr = $inRow['contact']	. ";";			// name
			$outStr .= extr_digits($the_phone) . ";";			// phone
			$outStr .= $inRow['street'] . (stripos($inRow['street'], " Apt:"))? "" : $apartment;		// street and apartment - 3/13/10
			
			$outStr .= $inRow['street']	. $apartment . ";";			// street and apartment - 3/13/10
			$outStr .= $inRow['city']	. ";";			// city 
			$outStr .= $inRow['state']	. ";";			// state 	
			$outStr .= ";";								// frm_zip - unused 
			$outStr .=$inRow['lat']		. ";"; 
			$outStr .=$inRow['lng']		. ";"; 
			$outStr .=$misc			. ";"; 			// possibly empty - 3/13/10
			$outStr .=$source_id . ";"; 			//
			return 	$outStr;						// end function do the_row()
			}
	
																// collect constituent data this phone no.
	
	$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$phone}'
		OR `phone_2`= '{$phone}' OR `phone_3`= '{$phone}' OR `phone_4`= '{$phone}'	LIMIT 1";
	
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$cons_row = (mysql_num_rows($result)==1)	? stripslashes_deep(mysql_fetch_array($result)): NULL;
	$apartment = 	(is_null($cons_row))		? "" : $aptStr . $cons_row['apartment']; 
	$misc = 		(is_null($cons_row))		? "" : $cons_row['miscellaneous'];
	$source = 0;	// none
	

	$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `phone`= '{$phone}' ORDER BY `updated` DESC";			// 9/29/09
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$ret = mysql_num_rows($result) . ";";						// hits - common to each return
	if (mysql_num_rows($result)> 0) {							// build return string from newest incident data
		$row = stripslashes_deep(mysql_fetch_array($result));
		$source_id = 1;										// incidents
		$ret .= do_the_row($row, $source_id, $phone);
		}
	
	 elseif (!(is_null($cons_row))) {						// 3/13/10
	 	$source_id = 2;										// constituents
		$ret .= do_the_row($cons_row, $source_id, $phone);						// otherwise use constituents data
		}
	
	else {													// no priors or constituents - do WP
		$source_id = 3;										// wp
		$ret = get_wp_data ($phone, $source_id, $phone);
		}					// end no priors
	
	$ret .= ";" . $source;	// add data source
	return $ret;			// semicolon-separated string
	}			// end function cid lookup() 

function extr_digits ($in_str) {
	return ereg_replace("[^0-9]", "", $in_str);
	}


//$cid_str = (empty($_GET))? "0000000000": $_GET['phone'];		// bad 
$cid_str = (empty($_GET))? "2125867000": $_GET['phone'];		// Hilton
//$cid_str = (empty($_GET))? "4102242850": $_GET['phone'];		// Giant pharmacy
//$cid_str = (empty($_GET))? "4103533986": $_GET['phone'];		// cell
//$cid_str = (empty($_GET))? "4108498240": $_GET['phone'];
//$cid_str = (empty($_GET))? "4108498721": $_GET['phone'];

$lookup_str =  cid_lookup(extr_digits ($cid_str));		// given a phone no., returns data string

$query = "INSERT INTO `$GLOBALS[mysql_prefix]caller_id` (`call_str`, `lookup_vals`, `status`)  
	VALUES ( " . quote_smart(trim($cid_str)) . ", " .  quote_smart(addslashes(trim($lookup_str))) . ", 0);";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

$retval =  (explode(";", $lookup_str)) ;
$received = format_date_time(mysql_format_date(now()));
$sources = array("NA", "prior incidents", "Constituents data", "White pages");
$extra = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}{$extra}/";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Caller ID Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	
	</HEAD>
	<BODY>

<TABLE ALIGN="center" cellpadding = 2 cellspacing = 2 BORDER=0 STYLE = "margin-top:40px" >
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TH COLSPAN=2 ALIGN = 'center'>Caller ID information Saved</TH>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Name</TD>
	<TD><?php echo $retval[1];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>	<!--  $new_string = ereg_replace("[^0-9]", "", $string);  -->
	<TD>Phone no.</TD>
	<TD><?php echo format_phone (ereg_replace("[^0-9]", "", $retval[2]));?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Address</TD>
	<TD><?php echo $retval[3];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>City</TD>
	<TD><?php echo $retval[4];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>State</TD>
	<TD><?php echo $retval[5];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD></TD>
	<TD><?php echo $retval[6];?></TD>			<!-- wp returns zip -->
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Latitude</TD>
	<TD><?php echo $retval[7];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>Longitude</TD>
	<TD><?php echo $retval[8];?></TD>
	</TR>
<!--
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>tbd</TD>
	<TD><?php echo $retval[9];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>tbd</TD>
	<TD><?php echo $retval[10];?></TD>
	</TR>
-->	
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Call received</TD>
	<TD><?php echo $received;?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>Information source</TD>
	<TD><?php echo $sources[$retval[10]];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Prior calls this number</TD>
	<TD><?php echo $retval[0];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD colspan = 2 align= 'center'><BR />
	<A HREF= "<?php echo $url;?>"><U>to Tickets</U><BR /><BR /></A>
	</TD>
	</TR>
</TABLE>


</BODY></HTML>
