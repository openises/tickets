<?php
/*
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
function do_my_instam($key_val) {				// 3/17/09
	// http://www.instamapper.com/api?action=getPositions&key=4899336036773934943
		
//		$from_utc = ($row_tr)?  "&from_ts=" . $row_tr['utc_stamp']: "";		// 3/26/09
		$from_utc = "";											// reconsider for tracking
		
		$url = "http://www.instamapper.com/api?action=getPositions&key={$key_val}{$from_utc}";
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
				print "";		// @fopen fails
				}
			}
			
/*

InstaMapper API v1.00
0142649733246,Robert Wing,1290605747,51.12893,-2.73858,0.0,0.0,0
3111130478040,Chris Perks,1290623627,51.23314,-2.99970,13.5,0.0,90
*/
	
	$ary_data = explode ("\n", $data);
	if (count($ary_data) > 1) {							// any data?
		for ($i=1; $i<count($ary_data)-1; $i++) {		// 11/25/10
			$the_position = explode (",", $ary_data[$i]);
			if (count($the_position)==8) {				// 
			
				list($device, $user, $when, $lat, $lng, $course, $speed, $altitude ) = $the_position;								
//						0		1		2	3		4		5		6		7
				$updated = mysql_format_date($when);

				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
					`lat`= '{$lat}' ,
					`lng`= '{$lng}',
					`updated` = '{$updated}',
					`user_id` = 0
					WHERE (`instam` = 1 
					AND  (`lat` != '{$lat}' OR `lng` != '{$lng}'  ) 
					AND  `callsign` = '{$device}')";		// 7/25/09

				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

				if (mysql_affected_rows()> 0 ) {												// if any movement
//					snap(__LINE__, mysql_affected_rows($result));

					$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source`= " . quote_smart(trim($user));		// remove prior track this device  3/20/09
					$result = mysql_query($query);				// 7/28/10
												// 
					$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh`(`source`,`utc_stamp`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
										VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
											quote_smart($user),
											quote_smart($when),
											quote_smart($lat),
											quote_smart($lng),
											quote_smart($altitude),
											round($speed),
											quote_smart($course),
											quote_smart(mysql_format_date($when)),
											quote_smart($speed)) ;
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);					
					unset($result);
					}				// end if (mysql_affected_rows>0)
					
				}		// end if (count())
			}		// end for ()
		}		// end if (count())
	
	}		// end function do_instam()

do_my_instam("7746755003372629669");

?>
