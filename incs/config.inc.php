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
		do_insert_settings('_sleep','5');				// 10/17/08
		do_insert_settings('_version','2.5 beta');
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','65');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','0');
		do_insert_settings('aprs_poll','0');			// new 10/15/07
		do_insert_settings('call_board','0');			// new 1/10/08
		do_insert_settings('chat_time','4');			// new 1/16/08
		do_insert_settings('date_format','Y-M-d H:i');
		do_insert_settings('def_city','');
		do_insert_settings('def_lat','39.1');			// approx center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_st','');
		do_insert_settings('def_zoom','3');
		do_insert_settings('delta_mins','0');
		do_insert_settings('email_reply_to','');		// new 1/10/08
		do_insert_settings('frameborder','1');
		do_insert_settings('framesize','50');
		do_insert_settings('gmaps_api_key','');			// API key
		do_insert_settings('gsearch_api_key','');		// 9/13/08 GSearch API key
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('host','www.yourdomain.com');
		do_insert_settings('kml_files','1');			// new 6/7/08
		do_insert_settings('lat_lng','0');				// 9/13/08 
		do_insert_settings('link_capt','');
		do_insert_settings('link_url','');
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('map_height','512');
		do_insert_settings('map_width','512');
		do_insert_settings('military_time','1');		// 7/16/08
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');
		do_insert_settings('validate_email','1');	
		do_insert_settings('wp_key','729c1a751fd3d2428cfe2a7b43442c64');		// 9/13/08 wp_key
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
		$print .= "<TD>" . format_date($row['last_in']) . "</TD>";	 	// already adjusted for server time offset
		$print .= "<TR>\n";	
		}
	$print .= "</TABLE>\n";
	return $print;
	}			// end function logged_on()

function show_stats(){			/* 6/9/08 show database/user stats */
	global $my_session;
	//get variables from db
	$oper_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_USER]"));
	$admin_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_ADMINISTRATOR]"));
	$guest_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_GUEST]"));
	$super_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_SUPER]"));
	$ticket_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket`"));
	$ticket_open_in_db 	= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE status='$GLOBALS[STATUS_OPEN]'"));
	$ticket_rsvd_in_db 	= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE status='$GLOBALS[STATUS_RESERVED]'"));
	$meds_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE type=$GLOBALS[TYPE_EMS]"));
	$fire_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE type=$GLOBALS[TYPE_FIRE]"));
	$cops_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE type=$GLOBALS[TYPE_COPS]"));
	$mutus_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE type=$GLOBALS[TYPE_MUTU]"));
	$othrs_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE type=$GLOBALS[TYPE_OTHR]"));

	$pluralG = ($guest_in_db==1)? "": "s";
	$pluralOp = ($oper_in_db==1)? "": "s";
	$pluralA = ($admin_in_db==1)? "": "s";
	$pluralS = ($super_in_db==1)? "": "s";
	
	$pluralM = ($meds_in_db==1)? "": "s";
	$pluralF = ($fire_in_db==1)? "": "s";
	$pluralC = ($cops_in_db==1)? "": "s";
	$pluralU = ($mutus_in_db==1)? "": "s";
	$pluralO = ($othrs_in_db==1)? "": "s";
	$rsvd_str = ($ticket_rsvd_in_db==0)? "": $ticket_rsvd_in_db . " reserved, ";
	print "<TABLE BORDER='0'><TR CLASS='even'><TD CLASS='td_label'COLSPAN=2 ALIGN='center'>System Summary</TD></TR><TR>";	

	$now = format_date(strval(date("U")));									// 8/26/08
	$adj = format_date(strval(date("U") - (get_variable('delta_mins')*60)));

	print "<TR CLASS='even'><TD CLASS='td_label'>Tickets Version:</TD><TD ALIGN='left'><B>" . get_variable('_version') . "</B></TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>PHP Version:</TD><TD ALIGN='left'>" . phpversion() . " under " .$_SERVER['SERVER_SOFTWARE'] . "</TD></TR>";		// 8/8/08
	print "<TR CLASS='even'><TD CLASS='td_label'>Database:</TD><TD ALIGN='left'>$GLOBALS[mysql_db] on $GLOBALS[mysql_host] running mysql ".mysql_get_server_info()."</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Server time:</TD><TD ALIGN='left'>" . $now . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>Adjusted:</B> $adj  </TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Tickets in database:&nbsp;&nbsp;</TD><TD ALIGN='left'>$rsvd_str $ticket_open_in_db open, ".($ticket_in_db - $ticket_open_in_db - $ticket_rsvd_in_db)." closed, $ticket_in_db total</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Units in database:</TD><TD ALIGN='left'>$meds_in_db Med'l unit$pluralM, $fire_in_db Fire unit$pluralF, $cops_in_db Police unit$pluralC, $mutus_in_db Mutual$pluralU, $othrs_in_db Other$pluralO, ".($meds_in_db+$fire_in_db+ $cops_in_db + $mutus_in_db +$othrs_in_db)." total</TD></TR>";
	
	print "<TR CLASS='even'><TD CLASS='td_label'>Users in database:</TD><TD ALIGN='left'>$super_in_db Super$pluralS, $admin_in_db Administrator$pluralA, $oper_in_db Operator$pluralOp, $guest_in_db Guest$pluralG, ".($super_in_db+$oper_in_db+$admin_in_db+$guest_in_db)." total</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Current User:</TD><TD ALIGN='left'>";
	print $my_session['user_name'] . ": " .	get_level_text ($my_session['level']);

	print "</TD></TR><TR CLASS='odd'><TD CLASS=\"td_label\">Sorting:</TD><TD ALIGN=\"left\">";	//
	$my_session['ticket_per_page'] == 0 ? print "unlimited" : print $my_session['ticket_per_page'];
	print " tickets/page, order by '".str_replace('DESC','descending',$my_session['sortorder'])."'</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Visting from:</TD><TD ALIGN='left'>" . $_SERVER['REMOTE_ADDR'] . ", " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Browser:</TD><TD ALIGN='left'>";
	print $_SERVER["HTTP_USER_AGENT"];
	print  "</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Monitor resolution: </TD><TD ALIGN='left'>" . $my_session['scr_width'] . " x " . $my_session['scr_height'] . "</TD></TR>";
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

function validate_email($email){ 	//really validate?/* validate email, code courtesy of Jerrett Taylor */
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
		case '_aprs_time':				return 'Not user-settable; used for APRS time between polls'; break;
		case '_version': 				return 'Tickets version number'; break;
		case 'abbreviate_affected': 	return 'Abbreviates \'affected\' string at this length when listing tickets, 0 to turn off'; break;
		case 'abbreviate_description': 	return 'Abbreviates descriptions at this length when listing tickets, 0 to turn off'; break;
		case 'allow_custom_tags': 		return 'Enable/disable use of custom tags for rowbreak, italics etc.'; break;
		case 'allow_notify': 			return 'Allow/deny notification of ticket updates'; break;
		case 'aprs_poll':				return 'APRS will be polled every n minutes.  Use 0 for no poll'; break;
		case 'call_board':				return 'Call Board feature - Use 0 for no Call Board'; break;
		case 'chat_time':				return 'Keep n hours of Chat'; break;
		case 'date_format': 			return 'Format dates according to php function date() variables'; break;
		case 'def_city':				return 'Default city name'; break;
		case 'def_lat':					return 'Map center default lattitude'; break;
		case 'def_lng':					return 'Map center default longitude'; break;
		case 'def_st':					return 'Default two-letter state'; break;
		case 'def_zoom':				return 'Map default zoom'; break;
		case 'delta_mins':				return 'Minutes delta - for server/users time synchronization'; break;
		case 'email_reply_to':			return 'The default reply-to address for emailing incident information'; break;
		case 'frameborder': 			return 'Size of frameborder'; break;
		case 'framesize': 				return 'Size of the top frame in pixels'; break;
		case 'gmaps_api_key':			return 'Google maps API key - see HELP/README re how to obtain'; break;	
		case 'gsearch_api_key':			return 'Google Search API key - see HELP/README re how to obtain'; break;	//9/13/08
		case 'guest_add_ticket': 		return 'Allow guest users to add tickets - NOT RECOMMENDED'; break;
		case 'host': 					return 'Hostname where Tickets is run'; break;
		case 'kml_files':  				return "Dont/Do display KML files - 0/1"; break;
		case 'lat_lng':					return 'Lat/lng display: (0) for DDD.ddddd, (1) for DDD MMM SS.ss, (2) for DDD MM.mm'; break;		// 9/13/08
		case 'link_capt':				return 'Caption to be used for external link button'; break;
		case 'link_url':				return 'URL of external page link'; break;
		case 'login_banner': 			return 'Message to be shown at login screen'; break;
		case 'map_caption':				return 'Map caption - cosmetic'; break;
		case 'map_height':				return 'Map height - pixels'; break;
		case 'map_width':				return 'Map width - pixels'; break;
		case 'military_time': 			return 'Enter dates as military time (no am/pm)'; break;
		case 'restrict_user_add': 		return 'Restrict user to only post tickets as himself'; break;
		case 'restrict_user_tickets': 	return 'Restrict to showing only tickets to current user'; break;
		case 'ticket_per_page': 		return 'Number of tickets per page to show'; break;
		case 'ticket_table_width': 		return 'Width of table when showing ticket'; break;
		case 'UTM':						return 'Shows UTM values in addition to Lat/Long'; break;
		case 'validate_email': 			return 'Simple email validation check for notifies. Enter 1 for yes'; break;
		case 'wp_key': 					return 'Not used in this version'; break;												// 9/13/08
		default: 						return "No help for '$setting'"; break;	//
		}
	}
//		case 'kml files':  				return 'Dont/Do display KML files - 0/1'; break;

?>