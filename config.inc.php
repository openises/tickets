<?php
/* config.inc.php contains config specific functions */
$colors = array ('odd', 'even');

/* run the OPTIMIZE sql query on all tables */
function optimize_db(){
	$result = mysql_query("OPTIMIZE TABLE $GLOBALS[mysql_prefix]ticket, $GLOBALS[mysql_prefix]action, $GLOBALS[mysql_prefix]user, $GLOBALS[mysql_prefix]settings, $GLOBALS[mysql_prefix]notify") or do_error('functions.inc.php::optimize_db()', 'mysql_query(optimize) failed', mysql_error(), __FILE__, __LINE__);
	}
/* reset database to defaults */
function reset_db($user=0,$ticket=0,$settings=0,$purge=0){
	/* if($purge) {
		print '<LI> Purging closed tickets...NOT IMPLEMENTED';
		//$result = mysql_query("DELETE FROM action") or do_error("functions.php.inc::reset_db($user,$ticket,$settings)", 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		//$result = mysql_query("DELETE FROM ticket WHERE status = '$GLOBALS[STATUS_CLOSED]'") or do_error("functions.php.inc::reset_db($user,$ticket,$settings)",'mysql query failed', mysql_error(), __FILE__, __LINE__);
		//SELECT action.id FROM action,ticket WHERE action.ticket_id=ticket.id and ticket.status=2;
		} */
	if($ticket)	{
		print '<LI> Deleting tickets...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]ticket") or do_error("functions.php.inc::reset_db($user,$ticket,$settings)",'mysql query failed', mysql_error(), __FILE__, __LINE__);
	 	print '<LI> Deleting actions...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]action") or do_error("functions.php.inc::reset_db($user,$ticket,$settings)", 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	 	print '<LI> Deleting notifies...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]notify") or do_error("functions.php.inc::reset_db($user,$ticket,$settings)", 'mysql query failed', mysql_error(), __FILE__, __LINE__);
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
		do_insert_settings('_version','2.4 beta');
		do_insert_settings('host','www.yourdomain.com');
		do_insert_settings('framesize','50');
		do_insert_settings('frameborder','1');
		do_insert_settings('allow_notify','0');
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('abbreviate_description','65');
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('validate_email','1');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('date_format','Y-M-d H:i');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('military_time','0');
		do_insert_settings('gmaps_api_key','0');		// frm_api_key
		do_insert_settings('def_lat','39.1');			// approx center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_zoom','3');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('def_st','');
		do_insert_settings('def_city','');
		do_insert_settings('delta_mins','0');
		do_insert_settings('_aprs_time','0');
		do_insert_settings('aprs_poll','0');			// new 10/15/07
		do_insert_settings('UTM','0');
		do_insert_settings('aprs_poll','0');
		do_insert_settings('link_capt','');
		do_insert_settings('link_url','');
		}

	print '<LI> Database reset done<BR /><BR />';
	}

function show_stats(){/* show database/user stats */

	//get variables from db
	$user_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]user WHERE level=$GLOBALS[LEVEL_USER]"));
	$admin_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]user WHERE level=$GLOBALS[LEVEL_ADMINISTRATOR]"));
	$guest_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]user WHERE level=$GLOBALS[LEVEL_GUEST]"));
	$ticket_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]ticket"));
	$ticket_open_in_db 	= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE status='$GLOBALS[STATUS_OPEN]'"));
	$meds_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE type=$GLOBALS[TYPE_MEDS]"));
	$fire_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE type=$GLOBALS[TYPE_FIRE]"));
	$cops_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE type=$GLOBALS[TYPE_COPS]"));
	$othrs_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE type=$GLOBALS[TYPE_OTHR]"));

	$pluralG = ($guest_in_db==1)? "": "s";
	$pluralU = ($user_in_db==1)? "": "s";
	$pluralA = ($admin_in_db==1)? "": "s";
	
	$pluralM = ($meds_in_db==1)? "": "s";
	$pluralF = ($fire_in_db==1)? "": "s";
	$pluralC = ($cops_in_db==1)? "": "s";
	$pluralO = ($othrs_in_db==1)? "": "s";
	
	print "<TABLE BORDER='0'><TR CLASS='even'><TD CLASS='td_label'COLSPAN=2 ALIGN='center'>Summary</TD></TR><TR>";		// print phpversion();
	print "<TR CLASS='odd'><TD CLASS='td_label'>PHP Version:</TD><TD ALIGN='right'><B>" . phpversion() . "</B></TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Database:</TD><TD ALIGN='right'><B>$GLOBALS[mysql_db]</B> on <B>$GLOBALS[mysql_host]</B> running mysql <B>".mysql_get_server_info()."</B></TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Tickets in database:&nbsp;&nbsp;</TD><TD ALIGN='right'>$ticket_open_in_db open, ".($ticket_in_db - $ticket_open_in_db)." closed, $ticket_in_db total</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Units in database:</TD><TD ALIGN='right'>$meds_in_db Med'l unit$pluralM, $fire_in_db Fire unit$pluralF, $cops_in_db Police unit$pluralC, $othrs_in_db Other$pluralO, ".($meds_in_db+$fire_in_db+ $cops_in_db +$othrs_in_db)." total</TD></TR>";
	
	print "<TR CLASS='odd'><TD CLASS='td_label'>Users in database:</TD><TD ALIGN='right'>$user_in_db user$pluralU, $admin_in_db administrator$pluralA, $guest_in_db guest$pluralG, ".($user_in_db+$admin_in_db+$guest_in_db)." total</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Current User:</TD><TD ALIGN='right'>";
	print $_SESSION['user_name'];

	switch($_SESSION['level']) {
		case $GLOBALS['LEVEL_ADMINISTRATOR']: 	print ' (administrator)'; 	break;
		case $GLOBALS['LEVEL_USER']: 			print ' (user)'; 			break;
		case $GLOBALS['LEVEL_GUEST']: 			print ' (guest)'; 			break;
		}

	print "</TD></TR><TR CLASS='odd'><TD CLASS=\"td_label\">Sorting:</TD><TD ALIGN=\"right\">";	//
	$_SESSION['ticket_per_page'] == 0 ? print "unlimited" : print $_SESSION['ticket_per_page'];
	print " tickets/page, order by '<B>".str_replace('DESC','descending',$_SESSION['sortorder'])."</B>'</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Visting from:</TD><TD ALIGN='right'>" . $_SERVER['REMOTE_ADDR'] . ", " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Browser:</TD><TD ALIGN='right'>";
	print $_SERVER["HTTP_USER_AGENT"];
	print  "</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Monitor resolution: </TD><TD ALIGN='right'>" . $_SESSION['scr_width'] . " x " . $_SESSION['scr_height'] . "</TD></TR>";
	print "</TABLE>";		//
	}

function list_users(){/* list users */
	global $colors;
	$result = mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]user") or do_error('list_users()::mysql_query()', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if (!check_for_rows("SELECT id FROM $GLOBALS[mysql_prefix]user")) { print '<B>[no users found]</B><BR />'; return; 	}
	print "<TABLE BORDER='0'>";
	print "<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><B>Users - click to edit</B></TD></TR>";
	print "<TR CLASS='odd'><TD><B>ID&nbsp;&nbsp;&nbsp;</B></TD><TD><B>User&nbsp;&nbsp;&nbsp;</B></TD><TD><B>Call&nbsp;&nbsp;&nbsp;</B></TD><TD><B>Description&nbsp;&nbsp;&nbsp;</B></TD><TD><B>Level&nbsp;&nbsp;&nbsp;</B></TD></TR>";
	$i=1;
	while($row = stripslashes_deep(mysql_fetch_array($result))) {
		print "<TR CLASS='" . $colors[$i%2] . "'><TD><A HREF=\"config.php?func=user&id=" . $row['id'] . "\">#" . $row['id'] . "</A></TD><TD>" . $row['user'] . "</TD><TD>" . $row['callsign'] . "</TD><TD>" . $row['info'] . "</TD><TD>";

		switch($row['level'])	{
			case $GLOBALS['LEVEL_ADMINISTRATOR']:	print "administrator";	break;
			case $GLOBALS['LEVEL_USER']:			print "user";			break;
			case $GLOBALS['LEVEL_GUEST']:			print "guest";			break;
			}

		print "</TD></TR>\n";
		$i++;
		}
	print '</TABLE><BR />';
	}

function reload_session(){/* reload session variables from db after profile update */
	$query 	= "SELECT * FROM $GLOBALS[mysql_prefix]user WHERE user='$_SESSION[user_name]'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$row 	= mysql_fetch_array($result);
	$_SESSION['level'] 				= $row['level'];
	$_SESSION['reporting']	 		= $row['reporting'];
	$_SESSION['ticket_per_page'] 	= $row['ticket_per_page'];
	$_SESSION['sortorder']			= $row['sortorder'].($row['sort_desc'] ? "DESC" : "");
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

//	if (!eregi("^[0-9a-z_]([-_.]?[0-9a-z])*@[0-9a-z][-.0-9a-z]*\\.[a-z]{2,4	}[.]?$",$email, $check)) {

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
		case '_version': 				return 'Tickets version number'; break;
		case 'host': 					return 'Hostname where Tickets is run'; break;
		case 'framesize': 				return 'Size of the top frame in pixels'; break;
		case 'frameborder': 			return 'Size of frameborder'; break;
		case 'allow_notify': 			return 'Allow/deny notification of ticket updates'; break;
		case 'login_banner': 			return 'Message to be shown at login screen'; break;
		case 'abbreviate_description': 	return 'Abbreviates descriptions at this length when listing tickets, 0 to turn off'; break;
		case 'validate_email': 			return 'Simple email validation check for notifies'; break;
		case 'abbreviate_affected': 	return 'Abbreviates \'affected\' string at this length when listing tickets, 0 to turn off'; break;
		case 'allow_custom_tags': 		return 'Enable/disable use of custom tags for rowbreak, italics etc.'; break;
		case 'restrict_user_tickets': 	return 'Restrict to showing only tickets to current user'; break;
		case 'restrict_user_add': 		return 'Restrict user to only post tickets as himself'; break;
		case 'reporting': 				return 'Enable/disable automatic ticket reporting (see help for more info)'; break;
		case 'date_format': 			return 'Format dates according to php function date() variables'; break;
		case 'ticket_table_width': 		return 'Width of table when showing ticket'; break;
		case 'ticket_per_page': 		return 'Number of tickets per page to show'; break;
		case 'guest_add_ticket': 		return 'Allow guest users to add tickets - NOT RECOMMENDED'; break;
		case 'military_time': 			return 'Enter dates as military time (no am/pm)'; break;
		case 'def_lat':					return 'Map center default lattitude'; break;
		case 'def_lng':					return 'Map center default longitude'; break;
		case 'def_zoom':				return 'Map default zoom'; break;
		case 'gmaps_api_key':			return 'Google maps API key - see HELP/README re how to obtain'; break;
		case 'map_caption':				return 'Map caption - cosmetic'; break;
		case 'def_st':					return 'Default two-letter state'; break;
		case 'def_city':				return 'Default city name'; break;
		case 'delta_mins':				return 'Minutes delta - for server/users time synchronization'; break;
		case '_aprs_time':				return 'Not user-settable; used for APRS time between polls'; break;
		case 'aprs_poll':				return 'APRS will be polled every n minutes.  Use 0 for no poll'; break;
		case 'UTM':						return 'Shows UTM values in addition to Lat/Long'; break;
		case 'link_capt':				return 'Caption to be used for external link button'; break;
		case 'link_url':				return 'URL of external page link'; break;
		default: 						return "No help for '$setting'"; break;	//
		}
	}
?>