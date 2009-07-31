<?php
/*
6/9/08 revised to add 'super' priv's level
7/16/08 revised default military time
8/8/08	added server identification
8/26/08 added server times
9/13/08 added lat_lng setting
9/13/08 added wp_key
9/13/08 added GSearch key
8/10/08 revised level text per globals
10/8/08	user edit revised per permission levels
10/17/08 added '__sleep' setting
1/26/09 removed gsearch key
1/27/09 added default area code
1/28/09 copied settings fm install
2/3/09 	revised per session lack of time-delta adjustment
2/24/09 added 'terrain' setting
3/11/09 added 'quick' hint
3/17/09 changed aprs to 'auto_poll'
8/26/08 added NIST time - turned off
4/5/09 added log record count, add'l settings values
7/12/09	added smtp account hint
*/
$colors = array ('odd', 'even');

/* run the OPTIMIZE sql query on all tables */
function optimize_db(){
	$result = mysql_query("OPTIMIZE TABLE $GLOBALS[mysql_prefix]ticket, $GLOBALS[mysql_prefix]action, $GLOBALS[mysql_prefix]user, $GLOBALS[mysql_prefix]settings, $GLOBALS[mysql_prefix]notify") or do_error('functions.inc.php::optimize_db()', 'mysql_query(optimize) failed', mysql_error(), __FILE__, __LINE__);
	}
/* reset database to defaults */
function reset_db($user=0,$ticket=0,$settings=0,$purge=0){
	global $my_session;
	if($ticket)	{
	 	print '<LI> Deleting actions...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]action") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting assigns...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]assigns") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting chat_messages...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]chat_messages") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting log...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]log") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting notifies...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]notify") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting patient...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]patient") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting responder...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]responder") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		print '<LI> Deleting tickets...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]ticket") or do_error("",'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting tracks...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]tracks") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		}

	if($user)	{
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]notify") or do_error('reset_db()::mysql_query(delete notifies)', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		print '<LI> Deleting users and notifies...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]user") or do_error('reset_db()::mysql_query(delete users)', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$query = "INSERT INTO $GLOBALS[mysql_prefix]user (user,info,level,passwd) VALUES('admin','Administrator',$GLOBALS[LEVEL_ADMINISTRATOR],PASSWORD('admin'))";
		$result = mysql_query($query) or do_error(query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		print '<LI> Admin account created with password \'admin\'';
		}
	if($settings) {		//reset all default settings
		print '<LI> Deleting settings...';

		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]settings") or do_error('reset_db()::mysql_query(delete settings)', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		do_insert_settings('_aprs_time','0');
		do_insert_settings('_version',$version);
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','65');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','1');
		do_insert_settings('auto_poll','0');			// new 10/15/07, 3/17/09
		do_insert_settings('def_area_code','');			// new 1/27/09
		do_insert_settings('call_board','1');			// new 1/10/08
		do_insert_settings('chat_time','4');			// new 1/16/08
		do_insert_settings('date_format','n/j/y H:i');
		do_insert_settings('def_city','');
		do_insert_settings('def_lat','39.1');			// approx center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_st','');
		do_insert_settings('def_zoom','3');
		do_insert_settings('delta_mins','0');
		do_insert_settings('email_reply_to','');		// new 1/10/08
		do_insert_settings('frameborder','1');
		do_insert_settings('framesize','50');
		do_insert_settings('gmaps_api_key',$_POST['frm_api_key']);		//
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('host','www.yourdomain.com');	
		do_insert_settings('kml_files','1');		//	 'new 6/7/08
		do_insert_settings('lat_lng','0');			// 9/13/08
		do_insert_settings('link_capt','');
		do_insert_settings('link_url','');
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('map_height','512');
		do_insert_settings('map_width','512');
		do_insert_settings('military_time','1');				// 7/16/08
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('terrain','1');						// 2/24/09
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');
		do_insert_settings('validate_email','1');
		do_insert_settings('wp_key','729c1a751fd3d2428cfe2a7b43442c64');		// 9/13/08 
		do_insert_settings('auto_route','1');					// 1/17/09
		do_insert_settings('serial_no_ap','1');					// 1/17/09
		}	//


	print '<LI> Database reset done<BR /><BR />';
	}

function browser($instr) {
	if ( strpos($instr, 'Gecko') ) {
		if ( strpos($instr, 'Netscape') )	{
			$browser = 'Netscape (Gecko/Netscape)';
			}
		else if ( strpos($instr, 'Firefox') )	 {
			$browser = 'Mozilla Firefox (Gecko/Firefox)';
			}
		else {
			$browser = 'Mozilla (Gecko/Mozilla)';
			}
		}
	else if ( strpos($instr, 'MSIE') ) {
		if ( strpos($instr, 'Opera') )	{
			$browser = 'Opera (MSIE/Opera/Compatible)';
			}
		else {
			$browser = 'Internet Explorer (MSIE/Compatible)';
			}
		}
	else {
		if ( strpos($instr, 'Opera') )	{
			$browser = 'Opera (MSIE/Opera/Compatible)';
			}
		else {
			$browser = 'Others browsers';
			}
		}
	return $browser;
	}		// end function

function logged_on() {
	global $colors;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` ORDER BY `user_name`";	// 6/15/08 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "<TABLE BORDER=1><TR CLASS='even'><TH COLSPAN=99>Logged on</TD></TR>\n";
	$i=0;
	while($row = stripslashes_deep(mysql_fetch_array($result))) {
		$print .= "<TR CLASS='" . $colors[$i%2] . "'>";
		$print .= "<TD><B>" . $row['user_name'] . "</B></TD>";
		$print .= "<TD>" . browser($row['browser']) . "</TD>";
		$the_time = $row['last_in'] - (get_variable('delta_mins')*60);				// 2/3/09
		$print .= "<TD>" . format_date("". $the_time) . "</TD>";	 				// adjust with server time offset
		$print .= "<TR>\n";	
		}
	$print .= "</TABLE>\n";
	return $print;
	}			// end function logged_on()

function show_stats(){			/* 6/9/08 show database/user stats */
	global $my_session;
	
	function ntp_time() {
	// ntp time servers to contact
	// we try them one at a time if the previous failed (failover)
	// if all fail then wait till tomorrow
	//	$time_servers = array("time.nist.gov",
	//	$time_servers = array("nist1.datum.com",
	//							"time-a.timefreq.bldrdoc.gov",
	//							"utcnist.colorado.edu");
	//
		$time_server = "nist1.datum.com";							// I'm in California and the clock will be set to -0800 UTC [8 hours] for PST
		$fp = fsockopen($time_server, 37, $errno, $errstr, 30);		// you will need to change this value for your region (seconds)
		if (!$fp) {
			return FALSE;
			} 
		else {
			$data = NULL;
			while (!feof($fp)) {
				$data .= fgets($fp, 128);
				}
			fclose($fp);
	
			if (strlen($data) != 4) {								// we have a response...is it valid? (4 char string -> 32 bits)
				echo "NTP Server {$time_server	} returned an invalid response.\n";
				return FALSE;
				}
			else {
				$NTPtime = ord($data{0	})*pow(256, 3) + ord($data{1	})*pow(256, 2) + ord($data{2	})*256 + ord($data{3	});
				$TimeFrom1990 = $NTPtime - 2840140800;			// convert the seconds to the present date & time
				$TimeNow = $TimeFrom1990 + 631152000;			// 2840140800 = Thu, 1 Jan 2060 00:00:00 UTC
				return 	$TimeNow;
				}
			}
		}		// end function ntp_time() 
	
	
	
	
	//get variables from db
	$memb_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_MEMBER]"));		// 3/3/09
	$oper_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_USER]"));
	$admin_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_ADMINISTRATOR]"));
	$guest_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_GUEST]"));
	$super_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_SUPER]"));
	$ticket_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket`"));
	$ticket_open_in_db 	= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE status='$GLOBALS[STATUS_OPEN]'"));
	$ticket_rsvd_in_db 	= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE status='$GLOBALS[STATUS_RESERVED]'"));


	$pluralM =  ($memb_in_db==1)? "": "s";
	$pluralG = ($guest_in_db==1)? "": "s";
	$pluralOp = ($oper_in_db==1)? "": "s";
	$pluralA = ($admin_in_db==1)? "": "s";
	$pluralS = ($super_in_db==1)? "": "s";
	
	$rsvd_str = ($ticket_rsvd_in_db==0)? "": $ticket_rsvd_in_db . " reserved, ";
	print "<TABLE BORDER='0'><TR CLASS='even'><TD CLASS='td_label'COLSPAN=2 ALIGN='center'>System Summary</TD></TR><TR>";	


	print "<TR CLASS='even'><TD CLASS='td_label'>Tickets Version:</TD><TD ALIGN='left'><B>" . get_variable('_version') . "</B></TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>PHP Version:</TD><TD ALIGN='left'>" . phpversion() . " under " .$_SERVER['SERVER_SOFTWARE'] . "</TD></TR>";		// 8/8/08
	print "<TR CLASS='even'><TD CLASS='td_label'>Database:</TD><TD ALIGN='left'>$GLOBALS[mysql_db] on $GLOBALS[mysql_host] running mysql ".mysql_get_server_info()."</TD></TR>";

	$fmt = "m/d/Y H:i:s";
	$now =  date($fmt,time());											// 8/26/08
	$adj =  date($fmt, (time() - (get_variable('delta_mins')*60)));
//	$nist = date($fmt, ntp_time());
	$nist = "NA";

	print "<TR CLASS='odd'><TD CLASS='td_label'>Server time:</TD>
		<TD ALIGN='left'>" . $now . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>Adjusted:</B> $adj  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>NIST:</B> $nist</TD></TR>";

	print "<TR CLASS='even'><TD CLASS='td_label'>Tickets in database:&nbsp;&nbsp;</TD><TD ALIGN='left'>$rsvd_str $ticket_open_in_db open, ".($ticket_in_db - $ticket_open_in_db - $ticket_rsvd_in_db)." closed, $ticket_in_db total</TD></TR>";

	$type_color=array();												// 1/28/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_color[$row['id']]=  $row['name'];
		}
	unset($result);

	$query = "SELECT `type`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]responder` GROUP BY `type`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$total = 0;
	$out_str = "";
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$total += $row['the_count'];
		$plural = ($row['the_count']!= 1)? "s": "";
		$out_str .= $row['the_count'] ." " . $type_color[$row['type']] . $plural . ", " ;
		}
	$show_str = $out_str . "total " . $total;
	unset($result);	

	print "<TR CLASS='odd'><TD CLASS='td_label'>Units in database:</TD><TD ALIGN='left'>" . $show_str . "</TD></TR>";
	
	print "<TR CLASS='even'><TD CLASS='td_label'>Users in database:</TD><TD ALIGN='left'>$super_in_db Super$pluralS, $admin_in_db Administrator$pluralA, $oper_in_db Operator$pluralOp, $guest_in_db Guest$pluralG, $memb_in_db Member$pluralM, ".($super_in_db+$oper_in_db+$admin_in_db+$guest_in_db+$memb_in_db)." total</TD></TR>";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]log`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$nr_logs = mysql_affected_rows();
	unset($result);	

	print "<TR CLASS='odd'><TD CLASS='td_label'>Log records in database:&nbsp;&nbsp;</TD><TD ALIGN='left'>{$nr_logs}</TD></TR>";		// 4/5/09
		
	print "<TR CLASS='even'><TD CLASS='td_label'>Current User:</TD><TD ALIGN='left'>";
	print $my_session['user_name'] . ", " .	get_level_text ($my_session['level']);

//	print "</TD></TR><TR CLASS='even'><TD CLASS=\"td_label\">Sorting:</TD><TD ALIGN=\"left\">";	//
	$my_session['ticket_per_page'] == 0 ? print ", unlimited " : print $my_session['ticket_per_page'];
	print " tickets/page, order by '".str_replace('DESC','descending', $my_session['sortorder'])."'</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Visting from:</TD><TD ALIGN='left'>" . $_SERVER['REMOTE_ADDR'] . ", " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Browser:</TD><TD ALIGN='left'>";
	print $_SERVER["HTTP_USER_AGENT"];
	print  "</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Monitor resolution: </TD><TD ALIGN='left'>" . $my_session['scr_width'] . " x " . $my_session['scr_height'] . "</TD></TR>";
	print "</TABLE>";		//
	}

function list_users(){/* list users */
	global $my_session, $colors;
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user`") or do_error('list_users()::mysql_query()', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if (!check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]user`")) { print '<B>[no users found]</B><BR />'; return; 	}
	print "<TABLE BORDER='0'>";
	$caption = (is_guest() || is_user())? "": "- click to edit";
	print "<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><B>Users" . $caption . " </B></TD></TR>";
	print "<TR CLASS='odd'><TD><B>ID&nbsp;&nbsp;&nbsp;</B></TD><TD><B>User&nbsp;&nbsp;&nbsp;</B></TD><TD><B>Call&nbsp;&nbsp;&nbsp;</B></TD><TD><B>Description&nbsp;&nbsp;&nbsp;</B></TD><TD><B>Level&nbsp;&nbsp;&nbsp;</B></TD></TR>";
	$i=1;
	while($row = stripslashes_deep(mysql_fetch_array($result))) {				// 10/8/08
		if (is_guest() || is_user()) {
			print "<TR CLASS='" . $colors[$i%2] . "'><TD>" . $row['id'] . "</TD><TD>" . $row['user'] . "</TD><TD>" . $row['callsign'] . "</TD><TD>" . $row['info'] . "</TD><TD>";
			}
		else {
			print "<TR CLASS='" . $colors[$i%2] . "'><TD><A HREF=\"config.php?func=user&id=" . $row['id'] . "\">#" . $row['id'] . "</A></TD><TD>" . $row['user'] . "</TD><TD>" . $row['callsign'] . "</TD><TD>" . $row['info'] . "</TD><TD>";
			}

		switch($row['level'])	{
			case $GLOBALS['LEVEL_SUPER']:			print get_level_text($GLOBALS['LEVEL_SUPER']);			break;		// 6/9/08, 8/10/08
			case $GLOBALS['LEVEL_ADMINISTRATOR']:	print get_level_text($GLOBALS['LEVEL_ADMINISTRATOR']);	break;
			case $GLOBALS['LEVEL_USER']:			print get_level_text($GLOBALS['LEVEL_USER']);			break;
			case $GLOBALS['LEVEL_GUEST']:			print get_level_text($GLOBALS['LEVEL_GUEST']);			break;
			case $GLOBALS['LEVEL_MEMBER']:			print get_level_text($GLOBALS['LEVEL_MEMBER']);			break;
			}

		print "</TD></TR>\n";
		$i++;
		}
	print '</TABLE><BR />';
	}

function reload_session(){/* reload session variables from db after profile update */
	global $my_session;

	$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `user`='$my_session[user_name]'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$my_session 	= mysql_fetch_array($result);
/*	
	$my_session['level'] 				= $row['level'];
	$my_session['reporting']	 		= $row['reporting'];
	$my_session['ticket_per_page'] 	= $row['ticket_per_page'];
	$my_session['sortorder']			= $row['sortorder'].($row['sort_desc'] ? "DESC" : "");
*/
	}

function do_insert_settings($name,$value){/* insert new values into settings table */
	$query =  sprintf("INSERT INTO `$GLOBALS[mysql_prefix]settings` (`name`,`value`) VALUES(%s,%s)",
								quote_smart(trim($name)),
								quote_smart(trim($value)));
	$result = mysql_query($query) or do_error($query, '', mysql_error(), __FILE__, __LINE__);
	}

function validate_email($email){ 	//really validate? - code courtesy of Jerrett Taylor 
	if (!get_variable('validate_email')){
		$return['status'] = true;  $return['msg'] = $email;
		return $return;
		}
	$return = array();

//	if (!eregi("^[0-9a-z_]([-_.]?[0-9a-z])*@[0-9a-z][-.0-9a-z]*\\.[a-z]{2,4	}[.]?$",$email, $check)) --

	if(!eregi( "^" .
            "[a-z0-9]+([_\\.-][a-z0-9]+)*" .    //user
            "@" .
            "([a-z0-9]+([\.-][a-z0-9]+)*)+" .   //domain
            "\\.[a-z]{2,}" .                    //sld, tld
            "$", $email, $regs)
   			) {

		$return['status'] = false;
		$return['msg'] = 'invalid e-mail address';
		return $return;
		}

//	$host = substr(strstr($check[0], '@'), 1);
//	if (!checkdnsrr($host.'.',"MX")) {
//		$return['status'] = false;
//		$return['msg'] = "invalid host ($host)";
//		return $return;
//		}

	$return['status'] = true; $return['msg'] = $email;
	return $return;
	}

function get_setting_help($setting){/* get help for settings */
	switch($setting) {
		case "_aprs_time":				return "Not user-settable; used for APRS time between polls"; break;
		case "_version": 				return "Tickets version number"; break;
		case "abbreviate_affected": 	return "Abbreviates \"affected\" string at this length when listing tickets, 0 to turn off"; break;
		case "abbreviate_description": 	return "Abbreviates descriptions at this length when listing tickets, 0 to turn off"; break;
		case "allow_custom_tags": 		return "Enable/disable use of custom tags for rowbreak, italics etc."; break;
		case "allow_notify": 			return "Allow/deny notification of ticket updates"; break;
		case "auto_poll":				return "APRS/Instamapper will be polled every n minutes.  Use 0 for no poll"; break;
		case "auto_route": 				return "Do/don&#39;t (1/0) use routing for new tickets"; break;												// 9/13/08
		case "call_board":				return "Call Board - 0, 1, n - for none, window, fixed frame size"; break;
		case "chat_time":				return "Keep n hours of Chat"; break;
		case "date_format": 			return "Format dates according to php function date() variables"; break;	
		case "def_area_code":			return "Default telephone area code"; break;
		case "def_city":				return "Default city name"; break;
		case "def_lat":					return "Map center default lattitude"; break;
		case "def_lng":					return "Map center default longitude"; break;
		case "def_st":					return "Default two-letter state"; break;
		case "def_zoom":				return "Map default zoom"; break;
		case "delta_mins":				return "Minutes delta - for server/users time synchronization"; break;
		case "email_reply_to":			return "The default reply-to address for emailing incident information"; break;
		case "email_from":				return "Outgoing email will use this value as the FROM value. VALID ADDRESS MANDATORY!"; break;
		case "frameborder": 			return "Size of frameborder"; break;
		case "framesize": 				return "Size of the top frame in pixels"; break;
		case "gmaps_api_key":			return "Google maps API key - see HELP/README re how to obtain"; break;	
		case "guest_add_ticket": 		return "Allow guest users to add tickets - NOT RECOMMENDED"; break;
		case "host": 					return "Hostname where Tickets is run"; break;
		case "kml_files":  				return "Do/don&#39;t (1/0) display KML files"; break;
		case "lat_lng":					return "Lat/lng display: (0) for DDD.ddddd, (1) for DDD MMM SS.ss, (2) for DDD MM.mm"; break;		// 9/13/08
		case "link_capt":				return "Caption to be used for external link button"; break;
		case "link_url":				return "URL of external page link"; break;
		case "login_banner": 			return "Message to be shown at login screen"; break;
		case "map_caption":				return "Map caption - cosmetic"; break;
		case "map_height":				return "Map height - pixels"; break;
		case "map_width":				return "Map width - pixels"; break;
		case "military_time": 			return "Enter dates as military time (no am/pm)"; break;
		case "quick":					return "Do/don&#39;t (1/0) bypass user notification steps for quicker operation"; break;			// 3/11/09
		case "restrict_user_add": 		return "Restrict user to only post tickets as himself"; break;
		case "restrict_user_tickets": 	return "Restrict to showing only tickets to current user"; break;
		case "serial_no_ap": 			return "Don&#39;t (0), Do prepend (1), or Append(2) ticket ID# to incident name"; break;												// 9/13/08
		case "situ_refr":				return "Situation map auto refresh - in seconds"; break;											// 3/11/09
		case "smtp_acct":				return "Ex: outgoing.verizon.net/587/ashore3/*&^$#@/ashore3@verizon.net"; break;					// 7/12/09
		case "terrain": 				return "Do/don&#39;t (1/0) include terrain map view option"; break;
		case "ticket_per_page": 		return "Number of tickets per page to show"; break;
		case "ticket_table_width": 		return "Width of table when showing ticket"; break;
		case "UTM":						return "Shows UTM values in addition to Lat/Long"; break;
		case "validate_email": 			return "Do/don&#39;t (1/0) use simple email validation check for notifies"; break;
		case "wp_key": 					return "White pages lookup key - obtain your own for high volume use"; break;												// 9/13/08
		case "closed_interval": 		return "Closed tickets and cleared dispatches are visible for this many hours"; break;												// 9/13/08
		case "def_zoom_fixed": 			return "Dynamic or fixed map/zoom; 0 dynamic, 1 fixed situ, 2 fixed units, 3 both"; break;												// 9/13/08
		case "instam_key": 				return "Instamapper master account key"; break;												// 9/13/08
		case "msg_text_1": 				return "Default message string for incident new/edit notifies; see instructions"; break;		// 4/5/09										// 9/13/08
		case "msg_text_2": 				return "Default message string for incident mini-menu email; see instructions"; break;												// 9/13/08
		case "msg_text_3": 				return "Default message string for for dispatch notifies; see instructions"; break;												// 9/13/08

		default: 						return "No help for '$setting'"; break;	//
		}
	}
//		case 'kml files':  				return 'Dont/Do display KML files - 0/1'; break;
//def_zoom_fixed

?>
