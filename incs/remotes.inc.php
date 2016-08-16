<?php
error_reporting(E_ALL);

/*
8/21/10 - initial release
8/24/10 - glat hyphen logic removed
11/21/10 - update times only on location change
4/8/11 - relocated JSON error suppression
4/9/11 - corrects double json decode
4/23/11 - no JSON decode for instamapper, include NULL in UPDATE test
4/24/11 - aprs error suppress added
4/25/11 - glat - check position or time change, sane() added
6/10/11 - Internal Tickets Tracker added (do_t_tracker)
7/6/11 -  do_ogts() added
9/25/11 - do_ogts() revised to accommodate 3-element 'ogts_info' setting
11/15/11 - fixes to GLat(), LocateA(), do_gtrack() - correct $result => $temp_result
2/22/12 - applied corrections to sane(), incl revised threshold logic to avoid strtotime()
3/24/12 - OGTS fixes to accommodate UK  addrresses.
4/2/12 - accommodate absence of OGTS address data
4/18/12 - APRS SQL  and data type corrections applied
4/20/12 fix to accommodate empty json element, per KB email - snap(__FUNCTION__, __LINE__);
4/29/12 add'l ogts and aprs error detection and logging
6/21/2013 glat track writing conditioned on unit movement
6/24/2013 removed date range check from sane()
7/2/2013 revisions to APRS, Glat to apply server time on responder position update
7/4/2013 date.time test added to sane()
7/9/2013 - applied now_ts() as update time to all device handlers
1/30/2014 - Added function do_xastir
2/14/2014 - redid instam IAW revised API, no master key usage
5/12/2014 - revised to handle movement detection
3/3/2015 - revised aprs movement detection, like_ify() lookup
6/20/2015 - major do_aprs() refactoring
*/

$thirty_days = 30*24*60*60;							// seconds - 7/4/2013
$sixty_days = 60*24*60*60;
$onetwenty_days = 120*24*60*60;
$threesixty_days = 360*24*60*60;

$sanetimes = array();
$sanetimes[0] = $thirty_days;
$sanetimes[1] = $sixty_days;
$sanetimes[2] = $onetwenty_days;
$sanetimes[3] = $threesixty_days;

$timeinuse = $sanetimes[3];	//	Change for testing to allow greater time lapse for old data.

function sane($in_lat, $in_lng, $in_time ) {		// applies sanity check to input values - returns boolean - 6/24/2013
	global $sanetimes, $timeinuse;
	if ( ( ! ( is_float ( $in_lat ) ) ) ||
		 ( ! ( is_float ( $in_lng ) ) ) ||
		 ( ! ( is_int ( $in_time ) ) ) ) 								return FALSE;	// 2/22/12
	if ( abs ( $in_lat> 90.0 ) ) 										return FALSE;
	if ( ( abs ( $in_lat == 0.0 ) ) || ( abs ( $in_lng == 0.0 ) ) ) 	return FALSE;
	if ( abs ( $in_lng ) > 180.0 ) 										return FALSE;
	if ( ( now ( ) - $in_time ) > $timeinuse ) 							return FALSE;	// 7/4/2013
	return 																TRUE;
	}				// end function sane()

function get_current() {		// 3/16/09, 6/10/11, 7/25/09  2/14/2014
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

	$aprs = $instam = $locatea = $gtrack = $glat = $ogts = $t_tracker = $mob_tracker = $xastir_tracker = $followmee_tracker = FALSE;		// 6/10/11, 7/6/11, 1/30/14
	$ts_threshold = strtotime('now - 24 hour');				// discard inputs older than this - 4/25/11

	$query = "SELECT `id`, `aprs`, `instam`, `locatea`, `gtrack`, `glat`, `ogts`, `t_tracker`, `mob_tracker`, `xastir_tracker`, `followmee_tracker` FROM `$GLOBALS[mysql_prefix]responder` WHERE ((`aprs` = 1) OR (`instam` = 1) OR (`locatea` = 1) OR (`gtrack` = 1) OR (`glat` = 1) OR (`ogts` = 1) OR (`t_tracker` = 1) OR (`followmee_tracker` = 1))";
	$result = mysql_query($query) or do_error($query, ' mysql error=', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ($row['aprs'] == 1) 	{ $aprs = TRUE;}
		if ($row['instam'] == 1) { $instam = TRUE;}
		if ($row['locatea'] == 1) { $locatea = TRUE;}		//7/29/09
		if ($row['gtrack'] == 1) { $gtrack = TRUE;}		//7/29/09
		if ($row['glat'] == 1) { $glat = TRUE;}			//7/29/09
		if ($row['ogts'] == 1) { $ogts = TRUE;}					// 7/6/11
		if ($row['t_tracker'] == 1) { $t_tracker = TRUE;}		// 6/10/11
		if ($row['mob_tracker'] == 1) { $mob_tracker = TRUE;}		// 9/6/11
		if ($row['xastir_tracker'] == 1) { $xastir_tracker = TRUE;}		// 1/30/14
		if ($row['followmee_tracker'] == 1) { $followmee_tracker = TRUE;}		// 1/30/14
		}		// end while ()
	unset($result);
	if ($aprs) 		{do_aprs();}
	if ($instam) 	{do_instam();}					// 2/14/2014
	if ($locatea) 	{do_locatea();}					//7/29/09
	if ($gtrack) 	{do_gtrack();}					//7/29/09
	if ($glat) 		{do_glat();}					//7/29/09
	if ($ogts) 		{do_ogts();}					// 7/6/11
	if ($t_tracker) {do_t_tracker();}				// 6/10/11
	if ($mob_tracker) {do_mob_tracker();}				// 6/10/11
	if ($xastir_tracker) {do_xastir();}				// 6/10/11
	if ($followmee_tracker) {do_followmee();}				// 6/10/11
	return array("aprs" => $aprs, "instam" => $instam, "locatea" => $locatea, "gtrack" => $gtrack, "glat" => $glat, "ogts" => $ogts, "t_tracker" => $t_tracker, "mob_tracker" => $mob_tracker, "xastir_tracker" => $xastir_tracker, "followmee_tracker" => $followmee_tracker);		//7/29/09, 7/6/11, 6/10/11
	}		// end get_current()

function get_instam_device($key) {				// 2/14/2014
	$the_url = "http://www.insta-mapper.com/api/api_single.php?device_id={$key}";
	$data = get_remote($the_url, FALSE);		// no JSON decode - 4/23/11
	$arr = json_decode( $data );
	if ( is_array( $arr ) ) {
		$temp = get_object_vars($arr[0]);
		extract ( $temp );
		if ( ( isset ( $lat) ) && ( isset ( $lng ) ) ) {
			$where_clause = "WHERE (`instam` = 1 AND `callsign` = '{$key}')";
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `lat` = '{$lat}', `lng` = '{$lng}' {$where_clause}";
			mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11
			if ( mysql_affected_rows() > 0 ) {						// if movement
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '" . now_ts() . "'{$where_clause}";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11
				$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source`= '" . quote_smart($key) . "' AND `updated` < (NOW() - INTERVAL 7 DAY)"; 	// remove prior track this device  3/20/09
				$result = mysql_query($query);
				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh`(`source`,`utc_stamp`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
									VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
										quote_smart($device_id),
										0,
										quote_smart($lat),
										quote_smart($lng),
										quote_smart($heading),
										round($speed),
										quote_smart($altitude),
										quote_smart(now_ts()),
										$GLOBALS['TRACK_INSTAM']) ;
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
				unset($result);
				} 			// end if movement
			}			// end if isset (lat, lng)
		}			// end if ( is_array( $arr ) )
	}		// end function get_instam_device()

function do_instam() {						// 2/14/2014
	$query = "SELECT `callsign` FROM `$GLOBALS[mysql_prefix]responder` WHERE `instam` = 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		get_instam_device (trim($row['callsign']));		// pull data each device
		}
	}		// end function do_instam()

function do_gtrack() {			//7/29/09
	global $ts_threshold;		// 4/25/11

	$gtrack_url = get_variable('gtrack_url');
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `gtrack`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$tracking_id = ($row['callsign']);
		$db_lat = ($row['lat']);
		$db_lng = ($row['lng']);
		$db_updated = ($row['updated']);
		$update_error = strtotime('now - 1 hour');

		$request_url = $gtrack_url . "/data.php?userid=$tracking_id";		//gtrack_url set by entry in settings table
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
				print "-error " . __LINE__;		// @fopen fails
				}
			}

		$xml = new SimpleXMLElement($data);

		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];
		$alt = $xml->marker['alt'];
		$date = $xml->marker['local_date'];
		if ($date != "") {
			list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day 8/3/09
			$date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
			$time = $xml->marker['local_time'];
			$time = date("H:i:s", strtotime($time));	// format as mySQL time
			$updated = $date . " " . $time;	// create updated datetime
			}
		$mph = $xml->marker['mph'];
		$kph = $xml->marker['kph'];
		$heading = $xml->marker['heading'];

		if (!empty($lat) && !empty($lng)) {		//check not NULL

			if ($db_lat<>$lat && $db_lng<>$lng) {	// check for change in position

				if(($db_updated == $updated) && ($update_error > $updated)) {
				} else {
				$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
				$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				unset($resultd);
																			// 11/21/10, 11/15/11
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
					`lat` = '$lat', `lng` ='$lng', updated	= '" . now_ts() . "'
					WHERE `gtrack` = 1
					AND  (`lat` != '{$lat}' OR `lng` != '{$lng}' )
					AND  `callsign` = '$user_id'";
				$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE source = '$user_id'";	// remove prior track this device
				$result_temp = mysql_query($query);				// 7/28/10

				$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";		// 6/24/10
				$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
				$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}	//end if
			}	//end if
		}	//end if
		}	// end while
	}	// end function do_gtrack()

function do_locatea() {				//7/29/09
	global $ts_threshold;					// 4/25/11

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `locatea`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$tracking_id = ($row['callsign']);
		$db_lat = ($row['lat']);
		$db_lng = ($row['lng']);
		$db_updated = ($row['updated']);
		$update_error = strtotime('now - 4 hours');

		$request_url = "http://www.locatea.net/data.php?userid=$tracking_id";
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
				print "-error " . __LINE__;		// @fopen fails
				}
			}

		$xml = new SimpleXMLElement($data);
		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];
		$alt = $xml->marker['alt'];
		$date = $xml->marker['local_date'];
		if ($date != "") {
			list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day	8/3/09
			$date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
			$time = $xml->marker['local_time'];
			$time = date("H:i:s", strtotime($time));	// format as mySQL time
			$updated = $date . " " . $time;				// updated datetime, e.g., 2009-09-22 13:40:20
			}

			if ( sane ( floatval ($lat), floatval ($lng), intval ( strtotime ( mysql2timestamp ( $updated ) ) ) ) ) {		// 7/4/2013
			$mph = $xml->marker['mph'];
			$kph = $xml->marker['kph'];
			$heading = $xml->marker['heading'];
			$queryd	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
			$resultd = mysql_query($queryd) or do_error($queryd, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			unset($resultd);
																		// 4/25/11
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
				`lat` = '{$lat}', `lng` ='{$lng}', updated	= '" . now_ts() . "'
				WHERE ((`locatea` = 1)
				AND ( callsign = '{$user_id}'))";
															// 11/15/11
			$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` = '$user_id'";		// remove prior track this device
			$result_temp = mysql_query($query);		// 7/28/10

			$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";		// 6/24/10
			$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
			$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}	//end if sane()
		}	// end while
	}	// end function do_locatea()

function do_glat() {			//	7/7/2013
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `glat`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$callsign = $row['callsign'];
		$the_url = "http://www.google.com/latitude/apps/badge/api?user={$callsign}&type=json";
		$data = get_remote($the_url, FALSE);					// arrives NOT decoded - 7/7/2013
		$json = json_decode($data, true);						// to *array*

		if ( isset($json["features"][0]["geometry"]["coordinates"] ) ) {		// major 'if'

			$glat_id = 		@$json["features"][0]["properties"]["id"];
			$lat = 			@$json["features"][0]["geometry"]["coordinates"][1];
			$lng = 			@$json["features"][0]["geometry"]["coordinates"][0];
			$timestamp = 	@$json["features"][0]["properties"]["timeStamp"];

			if ( sane ( @floatval($lat), @floatval($lng), @intval($timestamp) ) ) {					// 7/6/2013

				$where_clause = "WHERE `glat` = 1 AND `callsign` LIKE '%" . $glat_id . "'";	// force common 'WHERE'
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `lat` = " . $lat . ", `lng` = " . $lng . " " . $where_clause;
				$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11
				if ($result_temp) {				// location change?
					$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '" . now_ts() . "' " . $where_clause;
					$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11

					$updated = mysql_format_date($timestamp);							// to datetime format
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (`source`, `latitude`, `longitude`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated')";
					$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

					$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`source`, `latitude`, `longitude`,`packet_date`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated', '$updated')";
					$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					}			// end if (movement)
				}			// end if ( sane() )
			else {
//				log_error("Google Latitude data error - {$row['callsign']} @ " . __LINE__ );
				}
			}			// end if ( isset ["geometry"]["coordinates"])
		}			// end while()
	}		// end function do_glat();

function aprs_date_ok ($indate) {	// checks for date/time within 48 hours
	return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60);
	}

function do_aprs() {				// 7/2/2013 - 6/20/2015 -  populates the APRS tracks table and updates responder position data

	function log_aprs_err($message) {								// error logger - 4/29/12
		@session_start();
		if (!(array_key_exists ( "aprs_err", $_SESSION ))) {		// limit to once per session
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
			$_SESSION['aprs_err'] = TRUE;
			}
		}		// end function

	function is_recent($time_val) {
		return ( $time_val > (now() - (6+2)*60*60) );
		}

	global $ts_threshold;					// 4/25/11
	global $istest;
	$now_ts = now_ts();
	$the_key = trim(get_variable('aprs_fi_key'));
	if (empty($the_key)) {
		log_aprs_err("APRS.FI key required for aprs operation") ;
		return FALSE;
		}

	$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `updated`< (NOW() - INTERVAL 7 DAY)";
	$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	unset($resultd);

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile`= 1 AND `aprs`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

	if (mysql_num_rows($result) > 0) {			//
		$call_str = $sep = "";
		while ($row = @mysql_fetch_assoc($result)) {
			$call_str .= $sep . $row['callsign'];
			$sep = ",";
			}
		$the_url = "http://api.aprs.fi/api/get?name={$call_str}&what=loc&apikey={$the_key}&format=json";

		$data=get_remote($the_url);				// returns JSON-decoded values
		if ((!(is_array($data))) && (!(is_object($data)))) {				// 4/29/12
			log_aprs_err("APRS JSON data format error");
			}
		$temp = $data->result;

		if (strtoupper($temp) == "OK"){
			$time_offset = 6*60*60;							// 6 hours - determined from aprs.fi site observation
			$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

			for ( $i=0; $i< ($data->found ) ; $i++) {		// extract fields from each entry
				$entry = (object) $data->entries[$i];
				$callsign_in = $entry->name;
				$callsign_in_rev = like_ify($callsign_in);		// 3/3/2015 - revise callsign for LIKE lookup

				$lat = $entry->lat;
				$lng = $entry->lng;
				$updated =  $entry->time;
				@($course = $entry->course);				// 4/24/11
				@($mph = $entry->speed);
				@($alt = @$entry->altitude);
				$packet_date = $entry->lasttime;
				if ( sane ( floatval ($lat), floatval ($lng), intval ($updated) ) && ( is_recent($packet_date) ) ) {
					$the_time = mysql_format_date($packet_date - $time_offset);		// adjust per aprs.fi observation

					$query = "UPDATE `$GLOBALS[mysql_prefix]responder`
						SET `lat` = '{$lat}', `lng` = '{$lng}'
						WHERE ( (`aprs` = 1) AND (`callsign` LIKE '{$callsign_in_rev}') )";				// note LIKE argument

					$result = @mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11
					if ( mysql_affected_rows() > 0 ) {									// movement ?

						$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '{$now_ts}'
							WHERE ( (`aprs` = 1) AND (`callsign` LIKE '{$callsign_in_rev}') )";				// note LIKE argument
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11

						$our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;	// a hash - use tbd

						$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
							`packet_id`, `source`, `latitude`, `longitude`, `speed`, `course`, `altitude`, `packet_date`, `updated`)
							VALUES (
							'{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '{$mph}', '{$course}', '{$alt}', FROM_UNIXTIME({$packet_date}), '{$now}')";

						$result = @mysql_query($query);		// 6/17/2015
						}

					}			// end if (sane( ... ))
				}			// end for ($i...)
			}			// end ( JSON data OK)
		}			// end (mysql_affected_rows() > 0) - any APRS units?
	}			// end function do_aprs()

function do_ogts() {			// 3/24/12

	function log_ogts_err($message) {					// error logger
		@session_start();
		if (!(array_key_exists ( "ogts_err", $_SESSION ))) {		// limit to once per session
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
			$_SESSION['ogts_err'] = TRUE;
			}
		}		// end function

	error_reporting(E_ALL);
//		target	http://track.kmbnet.net:8080/events/data.json?a=sysadmin&p=12test34&g=all&limit=1";
//	                   000000000000000000000                    11111111   22222222
	$ogts_info = explode("/", get_variable('ogts_info')); 		// url/account
	if (count($ogts_info) != 3) {
		log_ogts_err("OpenGTS setting 'ogts_info' format error");
		return FALSE;
		}
//		$url = "http://{$ogts_info[0]}/events/data.jsonx?a={$ogts_info[1]}&p={$ogts_info[2]}&d={$ogts_info[3]}&g=all&limit=1";
	$url = "http://{$ogts_info[0]}/events/data.jsonx?a={$ogts_info[1]}&p={$ogts_info[2]}&g=all&limit=1";

	$data="";
	if (function_exists("curl_init")) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec ($ch);
		curl_close ($ch);
		}
	else {				// no CURL
		if ($fp = @fopen($url, "r")) {
			while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
			fclose($fp);
			}
		else {			// @fopen fails
			log_ogts_err("OpenGTS connection attempt failed");
			return FALSE;
			}
		}		// end no CURL

	$jsonresp = json_decode ($data, true); 		// an associative array
	$result = json_last_error();

	if ((!(empty($result))) || (!(is_array($jsonresp)))) {		// 4/29/12
		log_ogts_err("OpenGTS JSON data format error");
		return FALSE;
		}

	foreach ($jsonresp["DeviceList"] as $device) {
		$ogts_id = $device['Device'];

		if (!(empty($device['EventData']))) {				// 4/20/12
			$lat = $device["EventData"][0]['GPSPoint_lat'];
			$lng = $device["EventData"][0]['GPSPoint_lon'];
			$speed = $device["EventData"][0]['Speed'];
			$timestamp = $device["EventData"][0]['Timestamp'];		// integer

			$updated = mysql_format_date($timestamp);				// to datetime format
			$addr_arr = array();

			if  (array_key_exists('Address', $device["EventData"][0])) {			//  may/may-not exist
				$address = $device["EventData"][0]['Address'];
				$addr_arr = explode (",", $address);
				if (trim($addr_arr[count($addr_arr)-1])=="UK") {		// 3/24/12
		//

					switch (count($addr_arr)) {							// sanity checks added 4/2/12
						case 1:
						case 2:
//							snap(__FUNCTION__ . __LINE__ , trim($addr_arr[0]));		// ex: M8 motorway
							break;
						case 3:
							$street_work_val = substr (trim($addr_arr[0]), 0 , 28);
							$city_work_val = substr (trim($addr_arr[1]), 0 , 28);
							$state_work_val = substr (trim($addr_arr[count($addr_arr)-1]),  0 ,  4 );
							break;
						case 4:
							$street_work_val = substr (trim("{$addr_arr[0]} {$addr_arr[1]}")  , 0 , 28);
							$city_work_val = substr (trim($addr_arr[2]),  0 ,  28);
							$state_work_val = substr (trim($addr_arr[count($addr_arr)-1]),  0 ,  4 );
							break;
						case 5:
						case 6:
							$street_work_val = substr (trim("{$addr_arr[0]} {$addr_arr[1]}"), 0, 28);
							$city_work_val = substr (trim("{$addr_arr[2]}  {$addr_arr[3]} ") , 0, 28);
							$state_work_val = substr (trim($addr_arr[count($addr_arr)-1]),  0 ,  4 );
							break;
						default:
						}		// end switch()
					}	// end if (UK)
				else {
					$state_arr =  explode (" ", $addr_arr[2]);				// state zip
					$street_work_val = 	substr (trim($addr_arr[0]),  0 ,  28);
					$city_work_val = 	substr (trim($addr_arr[1]),  0 ,  28);
					$state_work_val = 	substr (trim($state_arr[1]), 0 ,  4);
					}				// end else

				$street_work_val = addslashes($street_work_val);
				$city_work_val = addslashes($city_work_val);
				$state_work_val = addslashes($state_work_val);
				$addr_sql = (!((count($addr_arr)>1) && (count($addr_arr)<7)))? "" : ", `street` = '{$street_work_val}',  `city` = '{$city_work_val}',  `state` = '{$state_work_val}'";
				}				// end if  (array_key_exists('Address', $device["EventData"][0])
			else {
				$addr_sql = "";
				}
																				// 4/2/12
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
					`lat` = '{$lat}', `lng` ='{$lng}', `updated` = '" . now_ts() . "'  {$addr_sql}
					WHERE ((`ogts` = 1)
					AND  (`callsign` LIKE '%{$ogts_id}')
					AND (`updated` <> '{$updated}'))";

				@mysql_query ($query);							// 5/12/2014
				switch (intval(mysql_affected_rows ()) ) {
					case -1:
						do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						break;
					case 0:					// no change == no movement
						break;
					default:				// movement - good to go
						$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` LIKE '%{$ogts_id}'";		// remove prior track this device
						$result = mysql_query($query);	// 7/28/10

						$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (`source`, `latitude`, `longitude`, `updated`) VALUES ('$ogts_id', '$lat', '$lng', '$updated')";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

						$queryd	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; 	// remove ALL expired track records
						$resultd = mysql_query($queryd) or do_error($queryd, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

						$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`source`, `latitude`, `longitude`,`packet_date`, `updated`) VALUES ('$ogts_id', '$lat', '$lng', '$updated', '$updated')";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						}			// end switch ()

			}			// end if (!(empty($device['EventData'])))
	}			// end foreach() ... as $device

}		// end function do_ogts();

function do_t_tracker() {		//	6/10/11
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `t_tracker`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {
		$tracking_id = ($row['callsign']);
		$db_lat = ($row['lat']);
		$db_lng = ($row['lng']);
		$db_updated = ($row['updated']);
		$update_error = strtotime('now - 4 hours');

		$query2	= "SELECT * FROM `$GLOBALS[mysql_prefix]remote_devices` WHERE `user` = '$tracking_id'";	//	read location data from incoming table
		$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		while ($row2 = @mysql_fetch_assoc($result2)) {
			$ic_lat = $row2['lat'];
			$ic_lng = $row2['lng'];
			$ic_speed = $row2['speed'];
			$ic_altitude = $row2['altitude'];
			$ic_direction = $row2['direction'];
			$ic_time = $row2['time'];
			if(($db_updated == $ic_time) && ($update_error > $ic_time)) {
				} else {
				$query3	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
				$result3 = mysql_query($query3) or do_error($query3, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query4 = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `lat` = '$ic_lat', `lng` ='$ic_lng', `updated` = '" . now_ts() . "' WHERE `t_tracker` = 1 AND `callsign` = '$tracking_id'";
				$result4 = mysql_query($query4) or do_error($query4, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query5 = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` = '$tracking_id'";		// remove prior track this device
				$result5 = mysql_query($query5);

				$query6 = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('$tracking_id', '$ic_lat', '$ic_lng', round({$ic_speed}), $ic_altitude, '$ic_time')";		// 6/24/10
				$result6 = mysql_query($query6) or do_error($query6, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query7 = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$tracking_id', '$ic_lat', '$ic_lng', round({$ic_speed}), $ic_altitude, '$ic_time', '$ic_time')";
				$result7 = mysql_query($query7) or do_error($query7, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$response_code7 = ($result7) ? 700 : 799;
				}	//end if
			}	// end while
		}	// end while
	}	//	end function do_t_tracker

function do_mob_tracker() {	//	9/6/13
	}

function do_xastir() {				// 1/30/14 - track responder locations with Xastir server - uses Xastir mysql DB.
	global $istest;
	function log_xastir_err($message) {					// error logger
		@session_start();
		if (!(array_key_exists ( "xastir_err", $_SESSION ))) {		// limit to once per session
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
			$_SESSION['xastir_err'] = TRUE;
			}
		}		// end function

	$xastir_server = get_variable("xastir_server");
	$xastir_db = get_variable("xastir_db");
	$xastir_user = get_variable("xastir_dbuser");
	$xastir_pass = get_variable("xastir_dbpass");

	if(($xastir_server == "") || ($xastir_db == "") || ($xastir_user == "") || ($xastir_pass == "")) {
		log_xastir_err("Xastir settings not complete, check in settings");
		return FALSE;
		}

	$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `updated` < (NOW() - INTERVAL 7 DAY)";
	$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	unset($resultd);

	$query = "SELECT `callsign`, `xastir_tracker`, `mobile` FROM `$GLOBALS[mysql_prefix]responder`
		WHERE (	( `mobile`= 1 )
		AND  	(`xastir_tracker`= 1 )
		AND 	(`callsign` <> ''))";
	$result1 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while ($row1 = mysql_fetch_assoc($result1)) {
		$callsign_in = $row1['callsign'];
		if(!mysql_connect($xastir_server, $xastir_user, $xastir_pass)) {
			exit();
			}
		if(!mysql_select_db($xastir_db)){
			exit();
			}

		$query = "SELECT * FROM `simpleStation` WHERE `station` = '{$row1['callsign']}' ORDER BY `transmit_time` DESC LIMIT 1";	// possibly none
		$result2 = mysql_query($query);
		while ($row2 = mysql_fetch_assoc($result2)) {
			$lat = $row2['latitude'];
			$lng = $row2['longitude'];
			$updated =  mysql2timestamp($row2['transmit_time']);
			$packet_date =  mysql2timestamp($row2['transmit_time']);
			$p_d_timestamp = mysql_format_date($row2['transmit_time']);
			if ( sane ( floatval ($lat), floatval ($lng), intval ($updated) ) ) {
				$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
				if(!mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_passwd'])) {
					exit();
					}
				if(!mysql_select_db($GLOBALS['mysql_db'])) {
					exit();
					}
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
					`lat` = '$lat', `lng` = '$lng'
					WHERE ( (`xastir_tracker` = 1 )
					AND (`callsign` = '{$callsign_in}' ) )";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//									any movement?
				if (mysql_affected_rows() > 0 ) {
					$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
						`updated` = '" . now_ts() . "'
						WHERE ( (`xastir_tracker` = 1)
						AND (`callsign` = '{$callsign_in}'))";
					$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					$our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;				// a hash - for dupe prevention

					$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
						packet_id, source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES (
						'{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '0', '0', '0', '{$p_d_timestamp}', '{$now}')";
					$result = mysql_query($query);				// ignore duplicate/errors
					}				// end if (mysql_affected_rows() > 0 )
				}			// end if (sane())
			}			// end while $row2
		}			// end while $row1
	}			// end function do_xastir()

function do_followmee() {				// 7/2/2013 - 6/20/2015 -  populates the APRS tracks table and updates responder position data
	function log_followmee_err($message) {								// error logger - 4/29/12
		@session_start();
		if (!(array_key_exists ( "followmee_err", $_SESSION ))) {		// limit to once per session
			do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
			$_SESSION['followmee_err'] = TRUE;
			}
		}		// end function

	function is_recent($time_val) {
		return ( strtotime($time_val) > (now() - (6+2)*60*60) );
		}

	global $ts_threshold;					// 4/25/11
	global $istest;
	$now_ts = now_ts();
	$the_key = trim(get_variable('followmee_key'));
	$fmusername = trim(get_variable('followmee_username'));
	if (empty($the_key)) {
		log_followmee_err("Follow Mee key required for aprs operation") ;
		return FALSE;
		}
	if (empty($fmusername)) {
		log_followmee_err("Follow Mee username required for aprs operation") ;
		return FALSE;
		}

	$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `updated`< (NOW() - INTERVAL 7 DAY)";
	$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	unset($resultd);

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `followmee_tracker`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

	if (mysql_num_rows($result) > 0) {			//
		$call_str = $sep = "";
		while ($row = @mysql_fetch_assoc($result)) {
			$call_str .= $sep . $row['callsign'];
			$the_url = "https://www.followmee.com/api/tracks.aspx?key={$the_key}&username={$fmusername}&output=json&function=currentfordevice&deviceid=${call_str}";


			$data=get_remote($the_url);				// returns JSON-decoded values
			if ((!(is_array($data))) && (!(is_object($data)))) {				// 4/29/12
				log_followmee_err("FollowMee JSON data format error");
			}
			if (count($data) > 0){
				$time_offset = 6*60*60;							// 6 hours - determined from aprs.fi site observation
				$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

				$entry = $data->Data[0];
				$callsign_in = $entry->DeviceID;
				$callsign_in_rev = like_ify($callsign_in);		// 3/3/2015 - revise callsign for LIKE lookup

				$lat = $entry->Latitude;
				$lng = $entry->Longitude;
				$updated =  $entry->Date;
				$kph = $entry->{'Speed(km/h)'};
				$alt = $entry->{'Altitude(m)'};
				$packet_date = $entry->Date;
				if ( sane ( floatval ($lat), floatval ($lng), intval (strtotime($updated)) ) && ( is_recent($packet_date) ) ) {
					$the_time = mysql_format_date(strtotime($packet_date) - $time_offset);		// adjust per aprs.fi observation
					$query = "UPDATE `$GLOBALS[mysql_prefix]responder`
						SET `lat` = '{$lat}', `lng` = '{$lng}'
						WHERE ( (`followmee_tracker` = 1) AND (`callsign` LIKE '{$callsign_in_rev}') )";				// note LIKE argument

						$result = @mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11
					if ( mysql_affected_rows() > 0 ) {									// movement ?

						$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '{$now_ts}'
							WHERE ( (`followmee_tracker` = 1) AND (`callsign` LIKE '{$callsign_in_rev}') )";				// note LIKE argument
							$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); //11/15/11

						$our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;	// a hash - use tbd

						$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
							`packet_id`, `source`, `latitude`, `longitude`, `speed`, `course`, `altitude`, `packet_date`, `updated`)
							VALUES (
									'{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '{$kph}', '{$course}', '{$alt}', '{$packet_date}', '{$now}')";

						$result = @mysql_query($query);		// 6/17/2015
						}

					}			// end if (sane( ... ))
				}			// end ( JSON data OK)
			}
		}			// end (mysql_affected_rows() > 0) - any APRS units?
	}			// end function do_aprs()
?>
