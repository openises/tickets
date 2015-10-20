<?php 
/*
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		// 10/1/08

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10

function do_my_instam($key) {				// 3/17/09
	// http://www.instamapper.com/api?action=getPositions&key=4899336036773934943
	
	
//	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1 AND `instam`= 1 AND `callsign` <> ''";  // work each call/license
//	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	
//	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$data="";
//		$key_val = $row['callsign'];
		$url = "http://www.instamapper.com/api?action=getPositions&key={$key}";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec ($ch);
			curl_close ($ch);
			print __LINE__ ;
			}
		else {				// not CURL
			print __LINE__ ;
			if ($fp = @fopen($url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error 1";		// @fopen fails
				}
			}
				
	dump($data);

	/*
				$the_time = (gmdate("U") - date("Z") - (get_variable('delta_mins')*60));
				$the_format = get_variable('date_format');
				print "date('Z') = " . date("Z"). "<br />\n";
				print "time1=" . date($the_format, time()). "<br />\n";		
				print "time2=" . date($the_format, time() - date("Z")). "<br />\n";		
				print "time3=" . date($the_format, $the_time). "<br />\n";		
				print "time4=" . date($the_format, $the_time). "<br />\n";		
				print "now=" . mysql_format_date($the_time) . "<br />\n";		


	InstaMapper API v1.00
	1263013328977,bold,1236239763,34.07413,-118.34940,25.0,0.0,335
	1088203381874,CABOLD,1236255869,34.07701,-118.35262,27.0,0.4,72
	*/
	
	//	if (((strpos ($data, "Invalid")>0)) || ((strpos ($data, "Missing")>0))) {
	//		print "-error 0";							// wp return bad
	//		}
	//	else {}											// wp return good
	
	$ary_data = explode ("\n", $data);
	dump($ary_data);
	if (count($ary_data) > 1) {
		for ($i=0; $i<count($ary_data); $i++) {
			$str_pos = explode (",", $ary_data[$i]);
			if (count($str_pos)==8) {
/*
   1. Device key
   2. Device label
   3. Position timestamp in UTC (number of seconds since January 1, 1970)
   4. Latitude
   5. Longitude
   6. Altitude in meters
   7. Speed in meters / second
   8. Heading in degrees 
*/   
/*
		$the_time = date("U", gmdate("U")) + date("Z");
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

*/


				$the_time = ($str_pos[2] - date("Z") - (get_variable('delta_mins')*60));
				$the_format = get_variable('date_format');
				print "date('Z') = " . date("Z"). "<br />\n";
				print "time1=" . date($the_format, $str_pos[2]). "<br />\n";		
				print "time2=" . date($the_format, $str_pos[2] + date("Z")). "<br />\n";		
				print "time3=" . date($the_format, $the_time). "<br />\n";		
				print "time4=" . date($the_format, $the_time). "<br />\n";		
				print "now=" . mysql_format_date($the_time) . "<br />\n";		
// --------------------------------------------------------------------
/*
//		$query = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]_snap_data` (`source`,`stuff`)  
//			VALUES(%s,%s)",
//				quote_smart_deep(trim($source)),
//				quote_smart_deep(trim($stuff)));
*/
//---------------------------------------------------------------------
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (`source`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
							VALUES (%s,%s,%s,%s,%s,%s,%s,%s)",
								quote_smart($str_pos[1]),
								quote_smart($str_pos[3]),
								quote_smart($str_pos[4]),
								quote_smart($str_pos[7]),
								quote_smart($str_pos[6]),
								quote_smart($str_pos[5]),
								quote_smart(mysql_format_date($str_pos[2])),
								quote_smart($str_pos[6])) ;
//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
/*
INSERT INTO `tracks_hh` (`source`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`),
							VALUES ('AFbold',34.07655,-118.35032,304,0.1,52.0,'2009-03-19 07:51:10',0.1)


*/

//---------------------------------------------------------------------------------
//				print "dvcid=" . $str_pos[0] . "<br />\n";		
//				print "dvkey=" . $str_pos[1] . "<br />\n";
//				print "time=" . date($the_format, $the_time). "<br />\n";		
//				print "lat=" . $str_pos[3]. "<br />\n";		
//				print "lng=" .$str_pos[4]. "<br />\n";		
//				print "Alt=" .$str_pos[5]. "<br />\n";		
//				print "Spd=" .$str_pos[6]. "<br />\n";		
//				print "Hdg=" .$str_pos[7]. "<br />\n";		
//				print "<br />\n";	
				$the_time = ($str_pos[2] - date("Z") - (get_variable('delta_mins')*60));
				$now = mysql_format_date($the_time);		// map UTC to local time equiv
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
					`lat`=		" . quote_smart(trim($str_pos[3])) . ",
					`lng`=		" . quote_smart(trim($str_pos[4])) . ",
					`updated` = " .	quote_smart(trim($now)) . "
					WHERE `callsign`= " . quote_smart(trim($str_pos[1])) . " LIMIT 1";				// 3/17/09, 8/26/08  --
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
				dump($query);
				unset($result);
					
					
				}		// end if (count())


			}		// end for ()
		}		// end if (count())
	
//		}		// end while
	}		// end function do_instam()


$instam_key = get_variable("instam_key");
if (!(empty($instam_key ))) {
	do_my_instam($instam_key);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD>
	<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<TITLE>Tickets <?php print get_variable('_version');?></TITLE>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
</FRAMESET>
</HTML>