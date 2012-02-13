<?php
/*
8/21/10 - initial release
8/24/10 - glat hyphen logic removed
11/21/10 - update times only on location change
*/

require_once('./incs/functions.inc.php');

function get_current_test() {		// 3/16/09, 7/25/09

	$delay = 1;			// minimum time in minutes between  queries - 7/25/09
	$when = get_variable('_aprs_time');				// misnomer acknowledged
	if(time() < $when) { 
		return;
		} 
	else {
		$next = time() + $delay*60;
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$next' WHERE `name`='_aprs_time'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}

	$aprs = $instam = $locatea = $gtrack = $glat = FALSE;	// 3/22/09
	
	$query = "SELECT `id`, `aprs`, `instam`, `locatea`, `gtrack`, `glat` FROM `$GLOBALS[mysql_prefix]responder`WHERE ((`aprs` = 1) OR (`instam` = 1) OR (`locatea` = 1) OR (`gtrack` = 1) OR (`glat` = 1))";	
	$result = mysql_query($query) or do_error($query, ' mysql error=', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ($row['aprs'] == 1) 	{ $aprs = TRUE;}
		if ($row['instam'] == 1) { $instam = TRUE;}
		if ($row['locatea'] == 1) { $locatea = TRUE;}		//7/29/09
		if ($row['gtrack'] == 1) { $gtrack = TRUE;}		//7/29/09
		if ($row['glat'] == 1) { $glat = TRUE;}			//7/29/09
		}		// end while ()
	unset($result);
	if ($glat) {
		$glat_func = do_glat_test();
		}
	print $glat_func;
	$result_code = "Get Current Successful";
	return $result_code;
	
	}		// end get_current() 

function do_glat_test() {			//7/29/09
	$i=1;
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `glat`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
//		dump($row);
//		print $i;
		$user = $row['callsign'];
		$db_lat = ($row['lat']);
		$db_lng = ($row['lng']);
		$db_updated = ($row['updated']);
		$update_error = strtotime('now - 1 hour');
		error_reporting(0);	
		$ret_val = array("", "", "", "");
		$the_url = "http://www.google.com/latitude/apps/badge/api?user={$user}&type=json";
		$json = get_remote($the_url);
		foreach ($json as $key => $value) {				// foreach 1
		    $temp = $value;
			foreach ($temp as $key1 => $value1) {			// foreach 2
			    $temp = $value1;
				foreach ($temp as $key2 => $value2) {			// foreach 3
					$temp = $value2;
					foreach ($temp as $key3 => $value3) {			// foreach 4
						switch (strtolower($key3)) {
							case "id":
								$ret_val[0] = $value3;
							    break;
							case "timestamp":
								$ret_val[1] = $value3;
							    break;
							case "coordinates":
								$ret_val[2] = $value3[0];
								$ret_val[3] = $value3[1];
							    break;
							}		// end switch()
						}		// end for each()
			    	}		// end for each()
				}		// end for each()
			}		// end foreach 1
		error_reporting(E_ALL);
	
		if ((empty($ret_val[0])) || ((empty($ret_val[1])))  || (!(my_is_float($ret_val[2] ))) || (!(my_is_float($ret_val[3])))) {
			$result_code = $i . " " . "do_glat() unsuccesful<br />";
			print $result_code;
		} else {							// valid glat data
			$result_code = $i . " " . "do_glat() successful<br />";
			print $result_code;
			}			// end if/else()
			$i++;
		}			// end while()

	}		// end function do_glat();


$test = get_current_test();
if($test) {
	print $test;
	}
