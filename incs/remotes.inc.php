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
6/10/11 - Internal Tickets Tracker added (<!-- do_t_tracker -->)
7/6/11 -  do_ogts() added
9/25/11 - do_ogts() revised to accommodate 3-element 'ogts_info' setting
11/15/11 - fixes to GLat(), LocateA(), do_gtrack() - correct $result => $temp_result
2/22/12 - applied corrections to sane(), incl revised threshold logic to avoid safe_strtotime()
3/24/12 - OGTS fixes to accommodate UK  addrresses.
4/2/12 - accommodate absence of OGTS address data
4/18/12 - APRS SQL  and data type corrections applied
4/20/12 fix to accommodate empty json element, per KB email - snap(__FUNCTION__, __LINE__);
4/29/12 addl ogts and aprs error detection and logging
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

5/30/2017 - added Traccar and javAPRSSrvr server capabilities - see below
Traccar - Traccar server must use MySQL database and not the default database, and must be accessible from server Tickets is on.
          Client should use callsign in Device Identifier if a Ham. If not, it is recommended to use telephone number without any '-'
          Presently uses server, database, user, and password for xastir configuration.
javAPRSSrvr - javAPRSSrvr must be accessible from the server Tickets is on.
              javAPRSSrvr must use dbgate client of javAPRSSrvr
              Presently uses server, database, user, and password for xastir configuration.
*/

$thirty_days = 30*24*60*60;                            // seconds - 7/4/2013
$sixty_days = 60*24*60*60;
$onetwenty_days = 120*24*60*60;
$threesixty_days = 360*24*60*60;

$sanetimes = array();
$sanetimes[0] = $thirty_days;
$sanetimes[1] = $sixty_days;
$sanetimes[2] = $onetwenty_days;
$sanetimes[3] = $threesixty_days;

$timeinuse = $sanetimes[3];    //    Change for testing to allow greater time lapse for old data.

function sane($in_lat, $in_lng, $in_time ) {        // applies sanity check to input values - returns boolean - 6/24/2013
    global $sanetimes, $timeinuse;
    if ( ( ! ( is_float ( $in_lat ) ) ) ||
         ( ! ( is_float ( $in_lng ) ) ) ||
         ( ! ( is_int ( $in_time ) ) ) )                                 return false;    // 2/22/12
    if ( abs ( $in_lat> 90.0 ) )                                         return false;
    if ( ( abs ( $in_lat == 0.0 ) ) || ( abs ( $in_lng == 0.0 ) ) )     return false;
    if ( abs ( $in_lng ) > 180.0 )                                         return false;
    if ( ( now ( ) - $in_time ) > $timeinuse )                             return false;    // 7/4/2013
    return                                                                 true;
    }                // end function sane()

function get_current() {        // 3/16/09, 6/10/11, 7/25/09  2/14/2014
    $delay = 1;            // minimum time in minutes between  queries - 7/25/09
    $when = get_variable('_aprs_time');                // misnomer acknowledged
    if(time() < $when) {
        return;
        } else {
        $next = time() + $delay*60;
        $query = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`='$next' WHERE `name`='_aprs_time'";
        $result = db_query($query);
        }

    $aprs = $instam = $locatea = $gtrack = $glat = $ogts = $t_tracker = $mob_tracker = $xastir_tracker = $followmee_tracker = $traccar = $javaprssrvr = false;        // 6/10/11, 7/6/11, 1/30/14
    $ts_threshold = safe_strtotime('now - 24 hour');                // discard inputs older than this - 4/25/11

    $query = "SELECT `id`, `aprs`, `instam`, `locatea`, `gtrack`, `glat`, `ogts`, `t_tracker`, `mob_tracker`, `xastir_tracker`, `followmee_tracker`, `traccar`, `javaprssrvr`
        FROM `{$GLOBALS['mysql_prefix']}responder`
        WHERE ((`aprs` = 1) OR (`instam` = 1) OR (`locatea` = 1) OR (`gtrack` = 1) OR (`glat` = 1) OR (`ogts` = 1) OR (`t_tracker` = 1) OR (`xastir_tracker` = 1) OR (`followmee_tracker` = 1) OR (`traccar` = 1) OR (`javaprssrvr` = 1))";
    $result = db_query($query);

    while ($row = stripslashes_deep($result->fetch_assoc())) {
        if ($row['aprs'] == 1)                { $aprs = true;}
        if ($row['instam'] == 1)            { $instam = true;}
        if ($row['locatea'] == 1)            { $locatea = true;}        //7/29/09
        if ($row['gtrack'] == 1)            { $gtrack = true;}        //7/29/09
        if ($row['glat'] == 1)                { $glat = true;}            //7/29/09
        if ($row['ogts'] == 1)                { $ogts = true;}                    // 7/6/11
        if ($row['t_tracker'] == 1)            { $t_tracker = true;}        // 6/10/11
        if ($row['mob_tracker'] == 1)        { $mob_tracker = true;}        // 9/6/11
        if ($row['xastir_tracker'] == 1)    { $xastir_tracker = true;}        // 1/30/14
        if ($row['followmee_tracker'] == 1) { $followmee_tracker = true;}        // 1/30/14
        if ($row['traccar'] == 1)            { $traccar = true;}        // 6/29/17
        if ($row['javaprssrvr'] == 1)        { $javaprssrvr = true;}        // 6/29/17
        }        // end while ()
    unset($result);
    if ($aprs)                    {do_aprs();}
    if ($instam)                {do_instam();}                    // 2/14/2014
    if ($locatea)                {do_locatea();}                    //7/29/09
    if ($gtrack)                {do_gtrack();}                    //7/29/09
    if ($glat)                    {do_glat();}                    //7/29/09
    if ($ogts)                    {do_ogts();}                    // 7/6/11
    if ($t_tracker)                {do_t_tracker();}                // 6/10/11
    if ($mob_tracker)            {do_mob_tracker();}                // 6/10/11
    if ($xastir_tracker)        {do_xastir();}                // 6/10/11
    if ($followmee_tracker)        {do_followmee();}        // 6/10/11
    if ($traccar)                {do_traccar();}                    // 6/10/11
    if ($javaprssrvr)            {do_javaprssrvr();}                // 5/30/17
    return array("aprs" => $aprs, "instam" => $instam, "locatea" => $locatea, "gtrack" => $gtrack, "glat" => $glat, "ogts" => $ogts, "t_tracker" => $t_tracker, "mob_tracker" => $mob_tracker, "xastir_tracker" => $xastir_tracker, "followmee_tracker" => $followmee_tracker, "traccar" => $traccar, "javaprssrvr" => $javaprssrvr);        //7/29/09, 7/6/11, 6/10/11
    }        // end get_current()

function get_instam_device($key) {                // 2/14/2014
    $the_url = "http://www.insta-mapper.com/api/api_single.php?device_id={$key}";
    $data = get_remote($the_url, false);        // no JSON decode - 4/23/11
    $arr = json_decode( $data );
    if ( is_array( $arr ) ) {
        $temp = get_object_vars($arr[0]);
        extract ( $temp );
        if ( ( isset ( $lat) ) && ( isset ( $lng ) ) ) {
            $safe_lat = floatval($lat);
            $safe_lng = floatval($lng);
            $safe_key = db()->real_escape_string($key);
            $where_clause = "WHERE (`instam` = 1 AND `callsign` = '{$safe_key}')";
            $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `lat` = '{$safe_lat}', `lng` = '{$safe_lng}' {$where_clause}";
            db_query($query); //11/15/11
            if ( $GLOBALS['db_handle']->affected_rows > 0 ) {                        // if movement
                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `updated` = '" . now_ts() . "'{$where_clause}";
                $result = db_query($query); //11/15/11
                $query    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks_hh` WHERE `source`= ? AND `updated` < (NOW() - INTERVAL 7 DAY)";     // remove prior track this device  3/20/09
                $result = db_query($query, [trim($key)]);
                $query  = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh`(`source`,`utc_stamp`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
                                    VALUES (?,?,?,?,?,?,?,?,?)";
                $result = db_query($query, [
                                        trim($device_id),
                                        0,
                                        trim($lat),
                                        trim($lng),
                                        trim($heading),
                                        round($speed),
                                        trim($altitude),
                                        trim(now_ts()),
                                        $GLOBALS['TRACK_INSTAM']]);
                unset($result);
                }             // end if movement
            }            // end if isset (lat, lng)
        }            // end if ( is_array( $arr ) )
    }        // end function get_instam_device()

function do_instam() {                        // 2/14/2014
    $query = "SELECT `callsign` FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `instam` = 1";
    $result = db_query($query);
    while ($row = stripslashes_deep($result->fetch_assoc())) {
        get_instam_device (trim($row['callsign']));        // pull data each device
        }
    }        // end function do_instam()

function do_gtrack() {            //7/29/09
    global $ts_threshold;        // 4/25/11

    $gtrack_url = get_variable('gtrack_url');
    $query    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `gtrack`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
    $result = db_query($query);
    while ($row = @$result->fetch_assoc()) {        // for each responder/account
        $tracking_id = ($row['callsign']);
        $db_lat = ($row['lat']);
        $db_lng = ($row['lng']);
        $db_updated = ($row['updated']);
        $update_error = safe_strtotime('now - 1 hour');

        $request_url = $gtrack_url . "/data.php?userid=$tracking_id";        //gtrack_url set by entry in settings table
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
        else {                // not CURL
            if ($fp = @fopen($request_url, "r")) {
                while (!feof($fp) && (safe_strlen($data)<9000)) $data .= fgets($fp, 128);
                fclose($fp);
                }
            else {
                print "-error " . __LINE__;        // @fopen fails
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
            $time = date("H:i:s", safe_strtotime($time));    // format as mySQL time
            $updated = $date . " " . $time;    // create updated datetime
            }
        $mph = $xml->marker['mph'];
        $kph = $xml->marker['kph'];
        $heading = $xml->marker['heading'];

        if (!empty($lat) && !empty($lng)) {        //check not NULL

            if ($db_lat<>$lat && $db_lng<>$lng) {    // check for change in position

                if(($db_updated == $updated) && ($update_error > $updated)) {
                } else {
                $query    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
                $resultd = db_query($query);
                unset($resultd);
                                                                            // 11/21/10, 11/15/11
                // Sanitize external XML data before SQL use
                $safe_gt_lat = floatval($lat);
                $safe_gt_lng = floatval($lng);
                $safe_gt_uid = db()->real_escape_string($user_id);
                $safe_gt_mph = floatval($mph);
                $safe_gt_alt = db()->real_escape_string($alt);
                $safe_gt_updated = db()->real_escape_string($updated);

                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                    `lat` = '{$safe_gt_lat}', `lng` ='{$safe_gt_lng}', updated    = '" . now_ts() . "'
                    WHERE `gtrack` = 1
                    AND  (`lat` != '{$safe_gt_lat}' OR `lng` != '{$safe_gt_lng}' )
                    AND  `callsign` = '{$safe_gt_uid}'";
                $result_temp = db_query($query);

                $query = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks_hh` WHERE source = '{$safe_gt_uid}'";    // remove prior track this device
                $result_temp = db_query($query);                // 7/28/10

                $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('{$safe_gt_uid}', '{$safe_gt_lat}', '{$safe_gt_lng}', round({$safe_gt_mph}), '{$safe_gt_alt}', '{$safe_gt_updated}')";        // 6/24/10
                $result_temp = db_query($query);

                $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('{$safe_gt_uid}', '{$safe_gt_lat}', '{$safe_gt_lng}', '{$safe_gt_mph}', '{$safe_gt_alt}', '{$safe_gt_updated}', '{$safe_gt_updated}')";
                $result_temp = db_query($query);
                }    //end if
            }    //end if
        }    //end if
        }    // end while
    }    // end function do_gtrack()

function do_locatea() {                //7/29/09
    global $ts_threshold;                    // 4/25/11

    $query    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `locatea`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
    $result = db_query($query);
    while ($row = @$result->fetch_assoc()) {        // for each responder/account
        $tracking_id = ($row['callsign']);
        $db_lat = ($row['lat']);
        $db_lng = ($row['lng']);
        $db_updated = ($row['updated']);
        $update_error = safe_strtotime('now - 4 hours');

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
        else {                // not CURL
            if ($fp = @fopen($request_url, "r")) {
                while (!feof($fp) && (safe_strlen($data)<9000)) $data .= fgets($fp, 128);
                fclose($fp);
                }
            else {
                print "-error " . __LINE__;        // @fopen fails
                }
            }

        $xml = new SimpleXMLElement($data);
        $user_id = $xml->marker['userid'];
        $lat = $xml->marker['lat'];
        $lng = $xml->marker['lng'];
        $alt = $xml->marker['alt'];
        $date = $xml->marker['local_date'];
        if ($date != "") {
            list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day    8/3/09
            $date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
            $time = $xml->marker['local_time'];
            $time = date("H:i:s", safe_strtotime($time));    // format as mySQL time
            $updated = $date . " " . $time;                // updated datetime, e.g., 2009-09-22 13:40:20
            }

            if ( sane ( floatval ($lat), floatval ($lng), intval ( strtotime ( mysql2timestamp ( $updated ) ) ) ) ) {        // 7/4/2013
            $mph = $xml->marker['mph'];
            $kph = $xml->marker['kph'];
            $heading = $xml->marker['heading'];
            $queryd    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
            $resultd = db_query($queryd);
            unset($resultd);
                                                                        // 4/25/11
            $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                `lat` = '{$lat}', `lng` ='{$lng}', updated    = '" . now_ts() . "'
                WHERE ((`locatea` = 1)
                AND ( callsign = '{$user_id}'))";
                                                            // 11/15/11
            $result_temp = db_query($query);

            $query = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks_hh` WHERE `source` = '$user_id'";        // remove prior track this device
            $result_temp = db_query($query);        // 7/28/10

            $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";        // 6/24/10
            $result_temp = db_query($query);

            $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
            $result_temp = db_query($query);
            }    //end if sane()
        }    // end while
    }    // end function do_locatea()

function do_glat() {            //    7/7/2013
    $query    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `glat`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
    $result = db_query($query);

    while ($row = @$result->fetch_assoc()) {        // for each responder/account
        $callsign = $row['callsign'];
        $the_url = "http://www.google.com/latitude/apps/badge/api?user={$callsign}&type=json";
        $data = get_remote($the_url, false);                    // arrives NOT decoded - 7/7/2013
        $json = json_decode($data, true);                        // to *array*

        if ( isset($json["features"][0]["geometry"]["coordinates"] ) ) {        // major 'if'

            $glat_id =         @$json["features"][0]["properties"]["id"];
            $lat =             @$json["features"][0]["geometry"]["coordinates"][1];
            $lng =             @$json["features"][0]["geometry"]["coordinates"][0];
            $timestamp =     @$json["features"][0]["properties"]["timeStamp"];

            if ( sane ( @floatval($lat), @floatval($lng), @intval($timestamp) ) ) {                    // 7/6/2013

                $where_clause = "WHERE `glat` = 1 AND `callsign` LIKE '%" . $glat_id . "'";    // force common 'WHERE'
                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `lat` = " . $lat . ", `lng` = " . $lng . " " . $where_clause;
                $result_temp = db_query($query); //11/15/11
                if ($result_temp) {                // location change?
                    $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `updated` = '" . now_ts() . "' " . $where_clause;
                    $result_temp = db_query($query); //11/15/11

                    $updated = mysql_format_date($timestamp);                            // to datetime format
                    $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh` (`source`, `latitude`, `longitude`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated')";
                    $result_temp = db_query($query);

                    $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (`source`, `latitude`, `longitude`,`packet_date`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated', '$updated')";
                    $result_temp = db_query($query);
                    }            // end if (movement)
                }            // end if ( sane() )
            else {
//                log_error("Google Latitude data error - {$row['callsign']} @ " . __LINE__ );
                }
            }            // end if ( isset ["geometry"]["coordinates"])
        }            // end while()
    }        // end function do_glat();

function aprs_date_ok ($indate) {    // checks for date/time within 48 hours
    return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60);
    }

function do_aprs() {                // 7/2/2013 - 6/20/2015 -  populates the APRS tracks table and updates responder position data
    function log_aprs_err($message) {                                // error logger - 4/29/12
        @session_start();
        if (!(array_key_exists ( "aprs_err", $_SESSION ))) {        // limit to once per session
            do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
            $_SESSION['aprs_err'] = true;
            }
        }        // end function

    function is_recent($time_val) {
        return ( $time_val > (now() - (6+2)*60*60) );
        }

    global $ts_threshold;                    // 4/25/11
    global $istest;
    $now_ts = now_ts();
    $the_key = trim(get_variable('aprs_fi_key'));
    if (empty($the_key)) {
        log_aprs_err("APRS.FI key required for aprs operation") ;
        return false;
        }

    $query    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE `updated`< (NOW() - INTERVAL 7 DAY)";
    $resultd = db_query($query);
    unset($resultd);

    $query    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `mobile`= 1 AND `aprs`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
    $result    = db_query($query);
    if ($result->num_rows > 0) {            //
        $call_arr = array();
        while ($row = @$result->fetch_assoc()) {
            $call_arr[] = $row['callsign'];
            }
        $allcall_arr = array_chunk($call_arr, 20);
        foreach($allcall_arr as $temp_arr) {    //    Each group of 20 callsigns
            $call_str = implode(",", $temp_arr);
            $the_url = "https://api.aprs.fi/api/get?name={$call_str}&what=loc&apikey={$the_key}&format=json";
            $data=get_remote($the_url);                // returns JSON-decoded values
            if ((!(is_array($data))) && (!(is_object($data)))) {                // 4/29/12
                log_aprs_err("APRS JSON data format error");
                }
            $temp = $data->result;
            if (strtoupper($temp) == "OK"){
                $time_offset = 6*60*60;                            // 6 hours - determined from aprs.fi site observation
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
                for ( $i=0; $i< ($data->found ) ; $i++) {        // extract fields from each entry
                    $entry = (object) $data->entries[$i];
                    $callsign_in = $entry->name;
                    $callsign_in_rev = like_ify($callsign_in);        // 3/3/2015 - revise callsign for LIKE lookup
                    $lat = $entry->lat;
                    $lng = $entry->lng;
                    $updated =  $entry->time;
                    @($course = $entry->course);                // 4/24/11
                    @($mph = $entry->speed);
                    @($alt = @$entry->altitude);
                    $packet_date = $entry->lasttime;
                    if ( sane ( floatval ($lat), floatval ($lng), intval ($updated) ) && ( is_recent($packet_date) ) ) {
                        $the_time = mysql_format_date($packet_date - $time_offset);        // adjust per aprs.fi observation
                        // Sanitize external APRS data
                        $safe_aprs_lat = floatval($lat);
                        $safe_aprs_lng = floatval($lng);
                        $safe_aprs_cs = db()->real_escape_string($callsign_in_rev);
                        $safe_aprs_callsign = db()->real_escape_string($callsign_in);
                        $safe_aprs_mph = floatval($mph);
                        $safe_aprs_course = floatval($course);
                        $safe_aprs_alt = floatval($alt);
                        $safe_aprs_pd = intval($packet_date);
                        $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder`
                            SET `lat` = '{$safe_aprs_lat}', `lng` = '{$safe_aprs_lng}'
                            WHERE ( (`aprs` = 1) AND (`callsign` LIKE '{$safe_aprs_cs}') )";                // note LIKE argument
                        $result = db_query($query); //11/15/11
                        if ( $GLOBALS['db_handle']->affected_rows > 0 ) {                                    // movement ?
                            $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `updated` = '{$now_ts}'
                                WHERE ( (`aprs` = 1) AND (`callsign` LIKE '{$safe_aprs_cs}') )";                // note LIKE argument
                            $result = db_query($query); //11/15/11
                            $our_hash = $safe_aprs_callsign . (string) (abs($safe_aprs_lat) + abs($safe_aprs_lng)) ;    // a hash - use tbd
                            $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (
                                `packet_id`, `source`, `latitude`, `longitude`, `speed`, `course`, `altitude`, `packet_date`, `updated`)
                                VALUES (
                                '{$our_hash}', '{$safe_aprs_callsign}', '{$safe_aprs_lat}', '{$safe_aprs_lng}', '{$safe_aprs_mph}', '{$safe_aprs_course}', '{$safe_aprs_alt}', FROM_UNIXTIME({$safe_aprs_pd}), '{$now}')";
                            $result = db_query($query);        // 6/17/2015
                            }
                        }            // end if (sane( ... ))
                    }            // end for ($i...)
                }            // end ( JSON data OK)
            }    //    End foreach 20 callsigns
        }            // end ($GLOBALS['db_handle']->affected_rows > 0) - any APRS units?
    }            // end function do_aprs()

function do_ogts() {            // 3/24/12

    function log_ogts_err($message) {                    // error logger
        @session_start();
        if (!(array_key_exists ( "ogts_err", $_SESSION ))) {        // limit to once per session
            do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
            $_SESSION['ogts_err'] = true;
            }
        }        // end function

    error_reporting(E_ALL);
//        target    http://track.kmbnet.net:8080/events/data.json?a=sysadmin&p=12test34&g=all&limit=1;
//                       000000000000000000000                    11111111   22222222
    $ogts_info = explode("/", get_variable('ogts_info'));         // url/account
    if (count($ogts_info) != 3) {
        log_ogts_err("OpenGTS setting 'ogts_info' format error");
        return false;
        }
//        $url = "http://{$ogts_info[0]}/events/data.jsonx?a={$ogts_info[1]}&p={$ogts_info[2]}&d={$ogts_info[3]}&g=all&limit=1";
    $url = "http://{$ogts_info[0]}/events/data.jsonx?a={$ogts_info[1]}&p={$ogts_info[2]}&g=all&limit=1";

    $data="";
    if (function_exists("curl_init")) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec ($ch);
        curl_close ($ch);
        }
    else {                // no CURL
        if ($fp = @fopen($url, "r")) {
            while (!feof($fp) && (safe_strlen($data)<9000)) $data .= fgets($fp, 128);
            fclose($fp);
            }
        else {            // @fopen fails
            log_ogts_err("OpenGTS connection attempt failed");
            return false;
            }
        }        // end no CURL

    $jsonresp = json_decode ($data, true);         // an associative array
    $result = json_last_error();

    if ((!(empty($result))) || (!(is_array($jsonresp)))) {        // 4/29/12
        log_ogts_err("OpenGTS JSON data format error");
        return false;
        }

    foreach ($jsonresp["DeviceList"] as $device) {
        $ogts_id = $device['Device'];

        if (!(empty($device['EventData']))) {                // 4/20/12
            $lat = $device["EventData"][0]['GPSPoint_lat'];
            $lng = $device["EventData"][0]['GPSPoint_lon'];
            $speed = $device["EventData"][0]['Speed'];
            $timestamp = $device["EventData"][0]['Timestamp'];        // integer

            $updated = mysql_format_date($timestamp);                // to datetime format
            $addr_arr = array();

            if  (array_key_exists('Address', $device["EventData"][0])) {            //  may/may-not exist
                $address = $device["EventData"][0]['Address'];
                $addr_arr = explode (",", $address);
                if (trim($addr_arr[count($addr_arr)-1])=="UK") {        // 3/24/12
        //

                    switch (count($addr_arr)) {                            // sanity checks added 4/2/12
                        case 1:
                        case 2:
//                            snap(__FUNCTION__ . __LINE__ , trim($addr_arr[0]));        // ex: M8 motorway
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
                        }        // end switch()
                    }    // end if (UK)
                else {
                    $state_arr =  explode (" ", $addr_arr[2]);                // state zip
                    $street_work_val =     substr (trim($addr_arr[0]),  0 ,  28);
                    $city_work_val =     substr (trim($addr_arr[1]),  0 ,  28);
                    $state_work_val =     substr (trim($state_arr[1]), 0 ,  4);
                    }                // end else

                $street_work_val = safe_addslashes($street_work_val);
                $city_work_val = safe_addslashes($city_work_val);
                $state_work_val = safe_addslashes($state_work_val);
                $addr_sql = (!((count($addr_arr)>1) && (count($addr_arr)<7)))? "" : ", `street` = '{$street_work_val}',  `city` = '{$city_work_val}',  `state` = '{$state_work_val}'";
                }                // end if  (array_key_exists('Address', $device["EventData"][0])
            else {
                $addr_sql = "";
                }
                                                                                // 4/2/12
                $safe_ogts_lat = floatval($lat);
                $safe_ogts_lng = floatval($lng);
                $safe_ogts_id = db()->real_escape_string($ogts_id);
                $safe_ogts_updated = db()->real_escape_string($updated);
                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                    `lat` = '{$safe_ogts_lat}', `lng` ='{$safe_ogts_lng}', `updated` = '" . now_ts() . "'  {$addr_sql}
                    WHERE ((`ogts` = 1)
                    AND  (`callsign` LIKE '%{$safe_ogts_id}')
                    AND (`updated` <> '{$safe_ogts_updated}'))";

                db_query($query);                            // 5/12/2014
                switch (intval($GLOBALS['db_handle']->affected_rows) ) {
                    case -1:
                        do_error($query, 'query failed', '', basename( __FILE__), __LINE__);
                        break;
                    case 0:                    // no change == no movement
                        break;
                    default:                // movement - good to go
                        $query = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks_hh` WHERE `source` LIKE '%{$ogts_id}'";        // remove prior track this device
                        $result = db_query($query);    // 7/28/10

                        $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh` (`source`, `latitude`, `longitude`, `updated`) VALUES ('$ogts_id', '$lat', '$lng', '$updated')";
                        $result = db_query($query);

                        $queryd    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)";     // remove ALL expired track records
                        $resultd = db_query($queryd);

                        $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (`source`, `latitude`, `longitude`,`packet_date`, `updated`) VALUES ('$ogts_id', '$lat', '$lng', '$updated', '$updated')";
                        $result = db_query($query);
                        }            // end switch ()

            }            // end if (!(empty($device['EventData'])))
    }            // end foreach() ... as $device

}        // end function do_ogts();

function do_t_tracker() {        //    6/10/11
    $query    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `t_tracker`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
    $result = db_query($query);
    while ($row = @$result->fetch_assoc()) {
        $tracking_id = ($row['callsign']);
        $db_lat = ($row['lat']);
        $db_lng = ($row['lng']);
        $db_updated = ($row['updated']);
        $update_error = safe_strtotime('now - 4 hours');

        $query2    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}remote_devices` WHERE `user` = '$tracking_id'";    //    read location data from incoming table
        $result2 = db_query($query2);
        while ($row2 = @$result2->fetch_assoc()) {
            $ic_lat = $row2['lat'];
            $ic_lng = $row2['lng'];
            $ic_speed = $row2['speed'];
            $ic_altitude = $row2['altitude'];
            $ic_direction = $row2['direction'];
            $ic_time = $row2['time'];
            if(($db_updated == $ic_time) && ($update_error > $ic_time)) {
                } else {
                $query3    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records
                $result3 = db_query($query3);

                $safe_ic_lat = floatval($ic_lat);
                $safe_ic_lng = floatval($ic_lng);
                $safe_tracking_id = db()->real_escape_string($tracking_id);
                $safe_ic_speed = floatval($ic_speed);
                $safe_ic_altitude = floatval($ic_altitude);
                $safe_ic_time = db()->real_escape_string($ic_time);

                $query4 = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `lat` = '{$safe_ic_lat}', `lng` ='{$safe_ic_lng}', `updated` = '" . now_ts() . "' WHERE `t_tracker` = 1 AND `callsign` = '{$safe_tracking_id}'";
                $result4 = db_query($query4);

                $query5 = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks_hh` WHERE `source` = '{$safe_tracking_id}'";        // remove prior track this device
                $result5 = db_query($query5);

                $query6 = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('{$safe_tracking_id}', '{$safe_ic_lat}', '{$safe_ic_lng}', round({$safe_ic_speed}), {$safe_ic_altitude}, '{$safe_ic_time}')";        // 6/24/10
                $result6 = db_query($query6);

                $query7 = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('{$safe_tracking_id}', '{$safe_ic_lat}', '{$safe_ic_lng}', round({$safe_ic_speed}), {$safe_ic_altitude}, '{$safe_ic_time}', '{$safe_ic_time}')";
                $result7 = db_query($query7);
                $response_code7 = ($result7) ? 700 : 799;
                }    //end if
            }    // end while
        }    // end while
    }    //    end function do_t_tracker

function do_mob_tracker() {    //    9/6/13
    }

function do_xastir() {                // 1/30/14 - track responder locations with Xastir server - uses Xastir mysql DB.
    global $istest;
    function log_xastir_err($message) {                    // error logger
        @session_start();
        if (!(array_key_exists ( "xastir_err", $_SESSION ))) {        // limit to once per session
            do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
            $_SESSION['xastir_err'] = true;
            }
        }        // end function

    $xastir_server = get_variable("xastir_server");
    $xastir_db = get_variable("xastir_db");
    $xastir_user = get_variable("xastir_dbuser");
    $xastir_pass = get_variable("xastir_dbpass");

    $tickets_server = $GLOBALS['mysql_host'];
    $tickets_db = $GLOBALS['mysql_db'];
    $tickets_user = $GLOBALS['mysql_user'];
    $tickets_pass = $GLOBALS['mysql_passwd'];

    if(($xastir_server == "") || ($xastir_db == "") || ($xastir_user == "") || ($xastir_pass == "")) {
        log_xastir_err("Xastir settings not complete, check in settings");
        return false;
        }

    if(!$xastir_connect = mysqli_connect($xastir_server, $xastir_user, $xastir_pass, $xastir_db)) {
        exit();
        }

    if(!$tickets_connect = mysqli_connect($tickets_server, $tickets_user, $tickets_pass, $tickets_db)) {
        exit();
        }

    $query    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE `updated` < (NOW() - INTERVAL 7 DAY)";
    $resultd = db_query($query);
    unset($resultd);

    $query = "SELECT `callsign`, `xastir_tracker`, `mobile` FROM `{$GLOBALS['mysql_prefix']}responder`
        WHERE (    ( `mobile`= 1 )
        AND      (`xastir_tracker`= 1 )
        AND     (`callsign` <> ''))";
    $result1 = db_query($query);
    while ($row1 = $result1->fetch_assoc()) {
        $callsign_in = $row1['callsign'];

        if(!mysqli_select_db($xastir_connect, $xastir_db)) {
            exit();
            }

        $query = "SELECT * FROM `simpleStation` WHERE `station` = '{$row1['callsign']}' ORDER BY `transmit_time` DESC LIMIT 1";    // possibly none
        $result2 = db_query($query);
        $result_ary = array();
        while ($row2 = $result2->fetch_assoc()) {
            $result_ary[] = $row2;
            }
        foreach($result_ary as $theRow) {
            $lat = $theRow['latitude'];
            $lng = $theRow['longitude'];
            $updated =  mysql2timestamp($theRow['transmit_time']);
            $packet_date =  mysql2timestamp($theRow['transmit_time']);
            $p_d_timestamp = mysql_format_date($theRow['transmit_time']);
            if ( sane ( floatval ($lat), floatval ($lng), intval ($updated) ) ) {
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

                if(!mysqli_select_db($tickets_connect, $tickets_db)) {
                    exit();
                    }
                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                    `lat` = '$lat', `lng` = '$lng'
                    WHERE ( (`xastir_tracker` = 1 )
                    AND (`callsign` = '{$callsign_in}' ) )";
                $result = db_query($query);
//                                    any movement?
                if ($GLOBALS['db_handle']->affected_rows > 0) {
                    $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                        `updated` = '" . now_ts() . "'
                        WHERE ( (`xastir_tracker` = 1)
                        AND (`callsign` = '{$callsign_in}'))";
                    $result_temp = db_query($query);
                    $our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;                // a hash - for dupe prevention

                    $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (
                        packet_id, source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES (
                        '{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '0', '0', '0', '{$p_d_timestamp}', '{$now}')";
                    $result = db_query($query);                // ignore duplicate/errors
                    }                // end if ($GLOBALS['db_handle']->affected_rows > 0 )
                }            // end if (sane())
            }            // end foreach $result_ary
        }            // end while $row1
    mysqli_close($xastir_connect);
    mysqli_close($tickets_connect);
    }            // end function do_xastir()

function do_followmee() {
    function log_followmee_err($message) {                                // error logger - 4/29/12
        @session_start();
        if (!(array_key_exists ( "followmee_err", $_SESSION ))) {        // limit to once per session
            do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
            $_SESSION['followmee_err'] = true;
            }
        }        // end function

    function is_recent($time_val) {
        return ( safe_strtotime($time_val) > (now() - (6+2)*60*60) );
        }

    global $ts_threshold;                    // 4/25/11
    global $istest;
    $now_ts = now_ts();
    $the_key = trim(get_variable('followmee_key'));
    $fmusername = trim(get_variable('followmee_username'));
    if (empty($the_key)) {
        log_followmee_err("Follow Mee key required for Follow Mee operation") ;
        return false;
        }
    if (empty($fmusername)) {
        log_followmee_err("Follow Mee username required for Follow Mee operation") ;
        return false;
        }

    $query    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE `updated`< (NOW() - INTERVAL 7 DAY)";
    $resultd = db_query($query);
    unset($resultd);

    $query    = "SELECT * FROM `{$GLOBALS['mysql_prefix']}responder` WHERE `followmee_tracker`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
    $result    = db_query($query);

    if ($result->num_rows > 0) {            //
        $call_str = $sep = "";
        while ($row = @$result->fetch_assoc()) {
            $call_str .= $sep . $row['callsign'];
            $the_url = "https://www.followmee.com/api/tracks.aspx?key={$the_key}&username={$fmusername}&output=json&function=currentfordevice&deviceid={$call_str}";


            $data=get_remote($the_url);                // returns JSON-decoded values
            if ((!(is_array($data))) && (!(is_object($data)))) {                // 4/29/12
                log_followmee_err("FollowMee JSON data format error");
            }
            if (count($data) > 0){
                $time_offset = 6*60*60;                            // 6 hours - determined from aprs.fi site observation
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

                $entry = $data->Data[0];
                $callsign_in = $entry->DeviceID;
                $callsign_in_rev = like_ify($callsign_in);        // 3/3/2015 - revise callsign for LIKE lookup

                $lat = $entry->Latitude;
                $lng = $entry->Longitude;
                $updated =  $entry->Date;
                $kph = $entry->{'Speed(km/h)'};
                $alt = $entry->{'Altitude(m)'};
                $packet_date = $entry->Date;
                if ( sane ( floatval ($lat), floatval ($lng), intval (safe_strtotime($updated)) ) && ( is_recent($packet_date) ) ) {
                    $the_time = mysql_format_date(safe_strtotime($packet_date) - $time_offset);
                    // Sanitize external FollowMee data
                    $safe_fm_lat = floatval($lat);
                    $safe_fm_lng = floatval($lng);
                    $safe_fm_cs = db()->real_escape_string($callsign_in_rev);
                    $safe_fm_callsign = db()->real_escape_string($callsign_in);
                    $safe_fm_kph = floatval($kph);
                    $safe_fm_course = isset($course) ? floatval($course) : 0;
                    $safe_fm_alt = floatval($alt);
                    $safe_fm_pd = db()->real_escape_string($packet_date);
                    $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder`
                        SET `lat` = '{$safe_fm_lat}', `lng` = '{$safe_fm_lng}'
                        WHERE ( (`followmee_tracker` = 1) AND (`callsign` LIKE '{$safe_fm_cs}') )";                // note LIKE argument

                        $result = db_query($query); //11/15/11
                    if ( $GLOBALS['db_handle']->affected_rows > 0 ) {                                    // movement ?

                        $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET `updated` = '{$now_ts}'
                            WHERE ( (`followmee_tracker` = 1) AND (`callsign` LIKE '{$safe_fm_cs}') )";                // note LIKE argument
                            $result = db_query($query); //11/15/11

                        $our_hash = $safe_fm_callsign . (string) (abs($safe_fm_lat) + abs($safe_fm_lng)) ;    // a hash - use tbd

                        $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (
                            `packet_id`, `source`, `latitude`, `longitude`, `speed`, `course`, `altitude`, `packet_date`, `updated`)
                            VALUES (
                                    '{$our_hash}', '{$safe_fm_callsign}', '{$safe_fm_lat}', '{$safe_fm_lng}', '{$safe_fm_kph}', '{$safe_fm_course}', '{$safe_fm_alt}', '{$safe_fm_pd}', '{$now}')";

                        $result = db_query($query);        // 6/17/2015
                        }

                    }            // end if (sane( ... ))
                }            // end ( JSON data OK)
            }
        }            // end ($GLOBALS['db_handle']->affected_rows > 0) - any units?
    }            // end function do_followmee()


function do_traccar() {                // 5/30/17 - track responder locations with Traccar server - uses Traccar mysql DB. (Traccar must be configured to use MySQL and not its default database)
    global $istest;
 // Do we want to create a unique error function for Traccar?
    function log_traccar_err($message) {                    // error logger
        @session_start();
        if (!(array_key_exists ( "traccar_err", $_SESSION ))) {        // limit to once per session
            do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
            $_SESSION['traccar_err'] = true;
            }
        }        // end function

// Need to create configuration lines for Traccar server
    $traccar_server = get_variable("traccar_server");
    $traccar_db = get_variable("traccar_db");
    $traccar_user = get_variable("traccar_dbuser");
    $traccar_pass = get_variable("traccar_dbpass");

    $tickets_server = $GLOBALS['mysql_host'];
    $tickets_db = $GLOBALS['mysql_db'];
    $tickets_user = $GLOBALS['mysql_user'];
    $tickets_pass = $GLOBALS['mysql_passwd'];

    if(($traccar_server == "") || ($traccar_db == "") || ($traccar_user == "")) {
        log_traccar_err("traccar settings not complete, check in settings");
        return false;
        }

    if(!$traccar_connect = mysqli_connect($traccar_server, $traccar_user, $traccar_pass, $traccar_db)) {
        exit();
        }

    if(!$tickets_connect = mysqli_connect($tickets_server, $tickets_user, $tickets_pass, $tickets_db)) {
        exit();
        }
// added to test tracks_length
    $tracks_length = get_variable("tracks_length");

    $query  = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE `updated` < (NOW() - INTERVAL " . $tracks_length . " HOUR)";         // altered for hours in settings instead of days
    $resultd = db_query($query);
    unset($resultd);

    $query = "SELECT `callsign`, `traccar`, `mobile` FROM `{$GLOBALS['mysql_prefix']}responder`
        WHERE (    ( `mobile`= 1 )
        AND      (`traccar`= 1 )
        AND     (`callsign` <> ''))";
    $result1 = db_query($query);
    while ($row1 = $result1->fetch_assoc()) {
        $callsign_in = $row1['callsign'];
        if(!mysqli_select_db($traccar_connect, $traccar_db)) {
            exit();
            }

// Find position id
        $query = 'select uniqueid, positionid from tc_devices where uniqueid = "' . $row1['callsign'] . '" limit 1';
        $result2 = db_query($query);
        $row2 = $result2 ? $result2->fetch_assoc() : null;
        $positionid = $row2['positionid'];
        $positionids[] = $positionid;

// Use position ID to query last position
        $query = 'select latitude, longitude, speed, course, altitude, devicetime from tc_positions where id = "' . $positionid . '"';
        $result3 = db_query($query);
        $result_ary = array();
        while ($row3 = $result3->fetch_assoc()) {
            $result_ary[] = $row3;
            }

        if(!mysqli_select_db($tickets_connect, $tickets_db)) {
            exit();
            }
        foreach($result_ary as $theRow) {
            $lat = $theRow['latitude'];
            $lng = $theRow['longitude'];
            $course = $theRow['course'];
            $speed = $theRow['speed'];
            $altitude = $theRow['altitude'];
            $updated =  mysql2timestamp($theRow['devicetime']);
            $packet_date =  mysql2timestamp($theRow['devicetime']);
            $p_d_timestamp = $theRow['devicetime'];

            if ( sane ( floatval ($lat), floatval ($lng), intval ($updated) ) ) {
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                    `lat` = '$lat', `lng` = '$lng'
                    WHERE ( (`traccar` = 1 )
                    AND (`callsign` = '{$callsign_in}' ) )";
                $result = db_query($query);
    //                                    any movement?
                if ($GLOBALS['db_handle']->affected_rows > 0) {
                    $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                        `updated` = '" . now_ts() . "'
                        WHERE ( (`traccar` = 1)
                        AND (`callsign` = '{$callsign_in}'))";
                    $result_temp = db_query($query);
                    $our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;                // a hash - for dupe prevention

                    $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (
                        packet_id, source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES (
                        '{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '{$speed}', '{$course}', '{$altitude}', '{$p_d_timestamp}', '{$now}')";
                    $result = db_query($query);
                    }                // end if ($GLOBALS['db_handle']->affected_rows > 0 )
                }            // end if (sane())
            }            // end foreach $result_ary
        }            // end while $row1
    mysqli_close($traccar_connect);
    mysqli_close($tickets_connect);
    }            // end function do_traccar()


function do_javaprssrvr() {                // 5/30/17 - track responder locations with javAPRSSrvr server - uses dbgate client of javAPRSSrvr.
    global $istest;
    function log_javaprssrvr_err($message) {                    // error logger
        @session_start();
        if (!(array_key_exists ( "javaprssrvr_err", $_SESSION ))) {        // limit to once per session
            do_log($GLOBALS['LOG_ERROR'], 0, 0, $message);
            $_SESSION['javaprssrvr_err'] = true;
            }
        }        // end function

// Do we want similar configuration lines for javaprssrvr server?
    $javaprssrvr_server = get_variable("javaprssrvr_server");
    $javaprssrvr_db = get_variable("javaprssrvr_db");
    $javaprssrvr_user = get_variable("javaprssrvr_dbuser");
    $javaprssrvr_pass = get_variable("javaprssrvr_dbpass");

    $tickets_server = $GLOBALS['mysql_host'];
    $tickets_db = $GLOBALS['mysql_db'];
    $tickets_user = $GLOBALS['mysql_user'];
    $tickets_pass = $GLOBALS['mysql_passwd'];

    if(($javaprssrvr_server == "") || ($javaprssrvr_db == "") || ($javaprssrvr_user == "") || ($javaprssrvr_pass == "")) {
        log_javaprssrvr_err("Javaprssrvr settings not complete, check in settings");
        return false;
        }

    if(!$javaprssrvr_connect = mysqli_connect($javaprssrvr_server, $javaprssrvr_user, $javaprssrvr_pass, $javaprssrvr_db)) {
        exit();
        }

    if(!$tickets_connect = mysqli_connect($tickets_server, $tickets_user, $tickets_pass, $tickets_db)) {
        exit();
        }

// added to test tracks_length
    $tracks_length = get_variable("tracks_length");

    $query    = "DELETE FROM `{$GLOBALS['mysql_prefix']}tracks` WHERE `updated` < (NOW() - INTERVAL " . $tracks_length . " HOUR)";         // altered for hours in settings instead of days
    $resultd = db_query($query);
    unset($resultd);

    $query = "SELECT `callsign`, `javaprssrvr`, `mobile` FROM `{$GLOBALS['mysql_prefix']}responder`
        WHERE (    ( `mobile`= 1 )
        AND      (`javaprssrvr`= 1 )
        AND     (`callsign` <> ''))";
    $result1 = db_query($query);
    while ($row1 = $result1->fetch_assoc()) {
        $callsign_in = $row1['callsign'];

        if(!mysqli_select_db($javaprssrvr_connect, $javaprssrvr_db)) {
            exit();
            }

// query positions for callsign
        $result_ary = array();
        $query = "SELECT * FROM `APRSPosits` WHERE `CallsignSSID` = '{$row1['callsign']}' ORDER BY `ReportTime` DESC LIMIT 1";    // possibly none
        $result2 = db_query($query);
        while ($row2 = $result2->fetch_assoc()) {
            $result_ary[] = $row2;
            }
        if(!mysqli_select_db($tickets_connect, $tickets_db)) {
            exit();
            }
        foreach($result_ary as $theRow) {
            $lat = $theRow['Latitude'];
            $lng = $theRow['Longitude'];
            $speed = $theRow['Speed'];
            $course = $theRow['Course'];
            $altitude = $theRow['Altitude'];
            $updated =  mysql2timestamp($theRow['ReportTime']);
            $packet_date =  mysql2timestamp($theRow['ReportTime']);
            $p_d_timestamp = $theRow['ReportTime'];
            if ( sane ( floatval ($lat), floatval ($lng), intval ($updated) ) ) {
                $now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

                $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                    `lat` = '$lat', `lng` = '$lng'
                    WHERE ( (`javaprssrvr` = 1 )
                    AND (`callsign` = '{$callsign_in}' ) )";
                $result = db_query($query);
//                                    any movement?
                if ($GLOBALS['db_handle']->affected_rows > 0) {
                    $query = "UPDATE `{$GLOBALS['mysql_prefix']}responder` SET
                        `updated` = '" . now_ts() . "'
                        WHERE ( (`javaprssrvr` = 1)
                        AND (`callsign` = '{$callsign_in}'))";
                    $result_temp = db_query($query);
                    $our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;                // a hash - for dupe prevention

                    $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}tracks` (
                        packet_id, source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES (
                        '{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '{$speed}', '{$course}', '{$altitude}', '{$p_d_timestamp}', '{$now}')";
                    $result = db_query($query);                // ignore duplicate/errors
                    }                // end if ($GLOBALS['db_handle']->affected_rows > 0 )
                }            // end if (sane())
            }            // end foreach $result_ary
        }            // end while $row1
    mysqli_close($javaprssrvr_connect);
    mysqli_close($tickets_connect);
    }            // end function do_javaprssrvr()
?>
