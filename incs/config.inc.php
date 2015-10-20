<?php
error_reporting(E_ALL);		// 3/5/12

/*
6/9/08  revised to add 'super' priv's level
7/16/08 revised default military time
8/8/08  added server identification
8/26/08 added server times
9/13/08 added lat_lng setting
9/13/08 added wp_key
9/13/08 added GSearch key
8/10/08 revised level text per globals
10/8/08 user edit revised per permission levels
10/17/08 added '__sleep' setting
1/26/09 removed gsearch key
1/27/09 added default area code
1/28/09 copied settings fm install
2/3/09  revised per session lack of time-delta adjustment
2/24/09 added 'terrain' setting
3/11/09 added 'quick' hint
3/17/09 changed aprs to 'auto_poll'
8/26/08 added NIST time - turned off
4/5/09  added log record count, add'l settings values
7/12/09 added smtp account hint
7/24/09 Added gtrack_url setting including help text.
8/3/09 Added locale setting including help text.
8/5/09 Added Function key settings
10/20/09 Replaced eregi with preg_replace to work with php 5.30 and greater.
11/01/09 Added setting for reverse geocoding on or off when setting location of incident - default off.
1/23/10 revised per table 'session' removal
3/21/10 pie chart settings hint added
8/13/10	hints added for recent settings
8/27/10 hint added
8/29/10 dispatch status tags hnt added
9/3/10 added unit to user display
12/4/10 cloud handling added
1/10/11 Added setting for group or dispatch
1/22/11 allow UC in email addr's
3/15/11 Help for CSS color settings
3/18/11 Added aprs.fi key help.
6/10/11 Added revisable Title string
11/7/11	Added Statistics users to count in System Summary
10/23/12 Added code for messaging and added extra missing settings to reset function
5/8/2013 date display corrected
5/21/2013 ICS button title/mouseover, others added.
3/25/2015 - added 'os_watch' hint
 */
$colors = array ('odd', 'even');

/* run the OPTIMIZE sql query on all tables */
function optimize_db(){
	$result = mysql_query("OPTIMIZE TABLE $GLOBALS[mysql_prefix]ticket, $GLOBALS[mysql_prefix]action, $GLOBALS[mysql_prefix]user, $GLOBALS[mysql_prefix]settings, $GLOBALS[mysql_prefix]notify") or do_error('functions.inc.php::optimize_db()', 'mysql_query(optimize) failed', mysql_error(), __FILE__, __LINE__);
	}
/* reset database to defaults */
function reset_db($user=0,$ticket=0,$responders=0,$facilities=0,$settings=0,$messages=0,$purge=0){
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
		print '<LI> Deleting tickets...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]ticket") or do_error("",'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]allocates WHERE `type` = 1") or do_error("",'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		}

	if($responders) {
	 	print '<LI> Deleting responder...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]responder") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]allocates WHERE `type` = 2") or do_error("",'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	 	print '<LI> Deleting tracks...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]tracks") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]tracks_hh") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		}

	if($facilities) {
	 	print '<LI> Deleting facilities...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]facilities") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]allocates WHERE `type` = 3") or do_error("",'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		}

	if($messages) {
	 	print '<LI> Deleting messages...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]messages") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]messages_bin") or do_error("", 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		}

	if($user)	{
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]notify") or do_error('reset_db()::mysql_query(delete notifies)', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		print '<LI> Deleting users and notifies...';
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]user") or do_error('reset_db()::mysql_query(delete users)', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]allocates WHERE `type` = 4") or do_error("",'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		//	add admin user
		$query = "INSERT INTO $GLOBALS[mysql_prefix]user (user,info,level,passwd) VALUES('admin','Administrator',$GLOBALS[LEVEL_ADMINISTRATOR],PASSWORD('admin'))";
		$result = mysql_query($query) or do_error(query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();	//	get user id for new admin user
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	//	get current time for insert
		//	add allocation for new admin user to region / group 1
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group`,`type`,`al_as_of`,`al_status`,`resource_id`,`sys_comments`,`user_id`) VALUES (1,4,'$now',0,$new_id,'Allocated to Group after reset operation',$new_id)";
		$result = mysql_query($query) or do_error(query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		print '<LI> Admin account created with password \'admin\'';
		}
	if($settings) {		//reset all default settings
		print '<LI> Deleting settings...';

		$result = mysql_query("DELETE FROM $GLOBALS[mysql_prefix]settings") or do_error('reset_db()::mysql_query(delete settings)', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		do_insert_settings('_aprs_time','0');
		do_insert_settings('_sleep','5');		//	10/23/12
		do_insert_settings('_version',$version);
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','65');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','1');
		do_insert_settings('auto_poll','0');			// new 10/15/07, 3/17/09
		do_insert_settings('auto_route','1');					// 1/17/09
		do_insert_settings('_aprs_time','0');
		do_insert_settings('def_area_code','');			// new 1/27/09
		do_insert_settings('call_board','1');			// new 1/10/08
		do_insert_settings('chat_time','4');			// new 1/16/08
		do_insert_settings('closed_interval','');		//	10/23/12
		do_insert_settings('date_format','n/j/y H:i');
		do_insert_settings('def_area_code','');		//	10/23/12
		do_insert_settings('def_city','');
		do_insert_settings('def_lat','39.1');			// approx center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_st','');
		do_insert_settings('def_zoom','3');
		do_insert_settings('def_zoom_fixed','3');		//	10/23/12
		do_insert_settings('delta_mins','0');
		do_insert_settings('email_reply_to','');		// new 1/10/08
		do_insert_settings('frameborder','1');
		do_insert_settings('framesize','50');
		do_insert_settings('gmaps_api_key',$_POST['frm_api_key']);		//
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('host','www.yourdomain.com');
		do_insert_settings('instam_key','');		//	10/23/12
		do_insert_settings('kml_files','1');		//	 'new 6/7/08
		do_insert_settings('lat_lng','0');			// 9/13/08
		do_insert_settings('link_capt','');
		do_insert_settings('link_url','');
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('map_height','512');
		do_insert_settings('map_width','512');
		do_insert_settings('military_time','1');				// 7/16/08
		do_insert_settings('msg_text_1','');		//	10/23/12
		do_insert_settings('msg_text_2','');		//	10/23/12
		do_insert_settings('msg_text_3','');		//	10/23/12
		do_insert_settings('quick','0');		//	10/23/12
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('serial_no_ap','1');					// 1/17/09
		do_insert_settings('situ_refr','');		//	10/23/12
		do_insert_settings('terrain','1');						// 2/24/09
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');
		do_insert_settings('validate_email','1');
		do_insert_settings('wp_key','729c1a751fd3d2428cfe2a7b43442c64');		// 9/13/08
		do_insert_settings('internet','1');		//	10/23/12
		do_insert_settings('smtp_acct','');		//	10/23/12
		do_insert_settings('email_from','');		//	10/23/12
		do_insert_settings('gtrack_url','');					// 7/24/09
		do_insert_settings('maptype','1');					// 7/24/09
		do_insert_settings('locale','0');						// 8/3/09
		do_insert_settings('func_key1','http://openises.sourceforge.net/,Open ISES');		// 8/5/09
		do_insert_settings('func_key2','');					// 8/5/09
		do_insert_settings('func_key3','');					// 8/5/09
		do_insert_settings('reverse_geo','0');				// 11/01/09
		do_insert_settings('logo','t.png');		//	10/23/12
		do_insert_settings('pie_charts','300/450/300');		//	10/23/12
		do_insert_settings('sound_wav','aooga.wav');		//	10/23/12
		do_insert_settings('sound_mp3','phonesring.mp3');		//	10/23/12
		do_insert_settings('disp_stat','D/R/O/FE/FA/Clear');		//	10/23/12
		do_insert_settings('oper_can_edit','0');		//	10/23/12
		do_insert_settings('group_or_dispatch','0');				// 12/16/10
		do_insert_settings('_inc_num','');				//	10/23/12
		do_insert_settings('_cloud','0');				//	10/23/12
		do_insert_settings('aprs_fi_key','');				//	10/23/12
		do_insert_settings('title_string','');				//	10/23/12
		do_insert_settings('ogts_info','');				//	10/23/12
		do_insert_settings('regions_control','0');				//	10/23/12
		do_insert_settings('map_in_portal','1');				//	10/23/12
		do_insert_settings('use_messaging','0');				//	10/23/12
		do_insert_settings ('ics_top','0');						// 5/21/2013 apply ICS button to top.php if == 1
		do_insert_settings ('auto_refresh','1/1/1');			// 5/21/2013 auto-refresh for sitscr, fullscr, mobile
		}	//


	print '<LI> Database reset done<BR /><BR />';
	}

function show_stats(){			/* 6/9/08 show database/user stats */

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
	$super_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_SUPER] AND `passwd` <> '55606758fdb765ed015f0612112a6ca7'"));	//	11/07/11
	$stats_in_db 		= mysql_num_rows(mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE level=$GLOBALS[LEVEL_STATS]"));
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


	print "<TR CLASS='odd'><TD CLASS='td_label'>Tickets Version:</TD><TD ALIGN='left'><B>" . get_variable('_version') . "</B></TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Server OS:</TD><TD ALIGN='left'>" . php_uname() . "</TD></TR>";
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
	$show_str = $out_str . $total . " total";
	unset($result);

	print "<TR CLASS='odd'><TD CLASS='td_label'>Units in database:</TD><TD ALIGN='left'>" . $show_str . "</TD></TR>";

	print "<TR CLASS='even'><TD CLASS='td_label'>Users in database:</TD><TD ALIGN='left'>$super_in_db Super$pluralS, $admin_in_db Administrator$pluralA, $oper_in_db Operator$pluralOp, $guest_in_db Guest$pluralG, $memb_in_db Member$pluralM, $stats_in_db Statistics ".($super_in_db+$oper_in_db+$admin_in_db+$guest_in_db+$memb_in_db+$stats_in_db)." total</TD></TR>";	//	11/07/11

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]log`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$nr_logs = mysql_affected_rows();
	unset($result);

	print "<TR CLASS='odd'><TD CLASS='td_label'>Log records in database:&nbsp;&nbsp;</TD><TD ALIGN='left'>{$nr_logs}</TD></TR>";		// 4/5/09

	print "<TR CLASS='even'><TD CLASS='td_label'>Current User:</TD><TD ALIGN='left'>";
	print $_SESSION['user'] . ", " .	get_level_text ($_SESSION['level']);

//	print "</TD></TR><TR CLASS='even'><TD CLASS=\"td_label\">Sorting:</TD><TD ALIGN=\"left\">";	//
	$_SESSION['ticket_per_page'] == 0 ? print ", unlimited " : print $_SESSION['ticket_per_page'];
	print " tickets/page, order by '".str_replace('DESC','descending', $_SESSION['sortorder'])."'</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Visting from:</TD><TD ALIGN='left'>" . $_SERVER['REMOTE_ADDR'] . ", " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Browser:</TD><TD ALIGN='left'>";
	print $_SERVER["HTTP_USER_AGENT"];
	print  "</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Monitor resolution: </TD><TD ALIGN='left'>" . $_SESSION['scr_width'] . " x " . $_SESSION['scr_height'] . "</TD></TR>";
	print "</TABLE>";		//
	}

function list_users(){		/* list users */
	global $colors;						// 9/3/10
//	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]user`") or do_error('list_users()::mysql_query()', 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$query = "SELECT *,
		`u`.`id` AS `userid`,
		`r`.`name` AS `unitname`,
		`r`.`id` AS `unitid`
		FROM `$GLOBALS[mysql_prefix]user` `u`
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`u`.`responder_id` = `r`.`id`)
		WHERE `passwd` <> '55606758fdb765ed015f0612112a6ca7'
		ORDER BY `u`.`user` ASC ";																// 5/25/09, 1/16/08
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	if (mysql_affected_rows()==0) 	 { print '<B>[no users found]</B><BR />'; return; 	}

//	if (!check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]user`")==0) { print '<B>[no users found]</B><BR />'; return; 	}
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		// 1/23/10

	print "<TABLE BORDER='0' CELLPADDING=2>";
	$caption = (has_admin())?" - click to edit":  ""; 	//
	print "<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><B>Users" . $caption . " </B></TD></TR>";
	print "<TR CLASS='odd'><TD><B>ID</B></TD>
		<TD><B>&nbsp;User</B></TD>
		<TD><B>&nbsp;Online</B></TD>
		<TD><B>&nbsp;Level</B></TD>
		<TD><B>&nbsp;Unit</B></TD>
		<TD><B>&nbsp;Description</B></TD>
		<TD><B>&nbsp;Log in</B></TD>
		<TD><B>&nbsp;From</B></TD>
		<TD><B>&nbsp;Browser</B></TD>
		</TR>";
	$i=1;
	while($row = stripslashes_deep(mysql_fetch_array($result))) {				// 10/8/08
		$onclick = (has_admin())? " onClick = \"self.location.href = 'config.php?func=user&id={$row['userid']}' \"": "";

		$level = get_level_text($row['level']);
//		$login = format_date_time($row['login']);
		$login = format_sb_date_2($row['login']);		// 5/8/2013
		$online = ($row['expires'] > $now)? "<IMG SRC = './markers/checked.png' BORDER=0>" : "";
		print "<TR CLASS='{$colors[$i%2]}' {$onclick}>
				<TD>{$row['userid']}</TD>
				<TD>&nbsp;{$row['user']}</TD>
				<TD ALIGN = 'center'>{$online}</TD>
				<TD>{$level}</TD>
				<TD>{$row['unitname']}</TD>
				<TD>{$row['info']}</TD>
				<TD>{$login}</TD>
				<TD>{$row['_from']}</TD>
				<TD>{$row['browser']}</TD>
				</TR>\n";
		$i++;
		}
	print '</TABLE><BR />';
	}		// end function list_users()

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

	if(!preg_match( "/^" .			// replaced eregi() with preg_replace() 10/20/09, 1/22/11
            "[a-zA-Z0-9]+([_\\.-][a-zA-Z0-9]+)*" .    //user
            "@" .
            "([a-zA-Z0-9]+([\.-][a-zA-Z0-9]+)*)+" .   //domain
            "\\.[a-zA-Z]{2,}" .                    	//sld, tld
            "$/", $email, $regs)
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
		case "access_requests": 		return "Allow new users to request access from login screen - swithes on request access button"; break;
		case "allow_custom_tags": 		return "Enable/disable use of custom tags for rowbreak, italics etc."; break;
		case "allow_notify": 			return "Allow/deny notification of ticket updates"; break;
		case "auto_poll":				return "APRS/Instamapper will be polled every n minutes.  Use 0 for no poll"; break;
		case "auto_route": 				return "Do/don&#39;t (1/0) use routing for new tickets"; break;												// 9/13/08
		case "call_board":				return "Call Board - 0, 1, 2 - for none, floating window, fixed frame"; break;
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
		case "ics_date": 				return "Date format for ICS forms. Format dates according to php function date() variables"; break;
		case "kml_files":  				return "Do/don&#39;t (1/0) display KML files"; break;
		case "lat_lng":					return "Lat/lng display: (0) for DDD.ddddd, (1) for DDD MMM SS.ss, (2) for DDD MM.mm"; break;		// 9/13/08
		case "link_capt":				return "Caption to be used for external link button"; break;
		case "link_url":				return "URL of external page link"; break;
		case "login_banner": 			return "Message to be shown at login screen"; break;
		case "map_caption":				return "Map caption - cosmetic"; break;
		case "map_height":				return "Map height - pixels"; break;
		case "map_width":				return "Map width - pixels"; break;
		case "military_time": 			return "Enter dates as military time (no am/pm)"; break;
		case "openspace_api": 			return "UK use only, API key for Openspace use to show UK Ordnance Survey Maps"; break;
		case "quick":					return "Do/don&#39;t (1/0) bypass user notification steps for quicker operation"; break;			// 3/11/09
		case "restrict_units": 			return "Restrict units from seing other areas of Tickets, only mobile screen."; break;
		case "restrict_user_add": 		return "Restrict user to only post tickets as himself"; break;
		case "restrict_user_tickets": 	return "Restrict to showing only tickets to current user"; break;
		case "serial_no_ap": 			return "Don&#39;t (0), Do prepend (1), or Append(2) ticket ID# to incident name"; break;												// 9/13/08
		case "situ_refr":				return "Situation map auto refresh - in seconds"; break;											// 3/11/09
		case "smtp_acct":				return "Ex: outgoing.verizon.net/587/ashore4/*&^$#@/ashore4@verizon.net"; break;					// 7/12/09
		case "terrain": 				return "Do/don&#39;t (1/0) include terrain map view option"; break;
		case "ticket_per_page": 		return "Number of tickets per page to show"; break;
		case "ticket_table_width": 		return "Width of table when showing ticket"; break;
		case "UTM":						return "Shows UTM values in addition to Lat/Long"; break;
		case "validate_email": 			return "Do/don&#39;t (1/0) use simple email validation check for notifies"; break;
		case "wp_key": 					return "White pages lookup key - obtain your own for high volume use"; break;												// 9/13/08
		case "closed_interval": 		return "Closed tickets and cleared dispatches are visible for this many hours"; break;												// 9/13/08
		case "def_zoom_fixed": 			return "Dynamic or fixed map/zoom; 0 dynamic, 1 fixed situ, 2 fixed units, 3 both"; break;												// 9/13/08
		case "instam_key": 				return "Instamapper &#39;Master API key&#39;"; break;												// 9/13/08
		case "msg_text_1": 				return "Default message string for incident new/edit notifies; see instructions"; break;		// 4/5/09										// 9/13/08
		case "msg_text_2": 				return "Default message string for incident mini-menu email; see instructions"; break;												// 9/13/08
		case "msg_text_3": 				return "Default message string for for dispatch notifies; see instructions"; break;												// 9/13/08
		case "ogts_info": 				return "Open GTS server info"; break;												// 9/13/08
		case "gtrack_url": 				return "URL for Gtrack server in format http://www.yourserver.com"; break;	//06/24/09
		case "maptype": 				return "Default Map display type - 1 for Standard, 2 for Satellite, 3 for Terrain Map, 4 for Hybrid"; break;	//08/02/09
		case "locale": 					return "Locale for USNG/UTM/OSG setting plus date format - 0=US, 1=UK, 2=ROW "; break;	//08/03/09
		case "func_key1": 				return "User Defined Function key 1 - Insert URL or File- URL to include http:// followed by Text to display on button. Separate values with comma."; break;	//08/05/09
		case "func_key2": 				return "User Defined Function key 2 - Insert URL or File- URL to include http:// followed by Text to display on button. Separate values with comma."; break;	//08/05/09
		case "func_key3": 				return "User Defined Function key 3 - Insert URL or File- URL to include http:// followed by Text to display on button. Separate values with comma."; break;	//08/05/09
		case "reverse_geo": 			return "Use Reverse Geocoding when setting location for an incident. 1 for yes, 0 for no. Default is 0"; break;	//11/01/09
		case "logo": 					return "Enter filename of your site logo file here"; break;	//8/13/10
		case "regions_control": 		return "Regions select / view control floating over map (0) or docked to top bar (1)"; break;												// 9/13/08
		case "pie_charts": 				return "Severity/Incident types/Location pie chart diameters, in pixels"; break;	// 3/21/10
		case "internet": 				return "Internet/network connection available: 1 (default) for Yes, 2 for No, 3 for maybe - will check network dynamically"; break;	// 8/13/10
		case "sound_mp3": 				return "Enter filename of your site mp3 alert tone - Default is phonesring.mp3"; break;	// 8/13/10
		case "sound_wav": 				return "Enter filename of your site WAV alert tone - Default is aooga.wav"; break;	// 8/13/10
		case "oper_can_edit": 			return "Operator is disallowed (0) or allowed to (1) edit incident data"; break;	// 8/27/10
		case "disp_stat": 				return "Dispatch status tags, slash-separated; for &#39;dispatched&#39;, responding&#39;, &#39;on-scene&#39;, &#39;facility-enroute&#39;, &#39;facility arrived&#39;, &#39;clear&#39; IN THAT ORDER! (D/R/O/FE/FA/Clear)"; break;	// 8/29/10
		case "group_or_dispatch": 		return "Show hide categories for units on the situation screen are based on show/hide setting in un_status table (0 - default) or on status groups in un_status table (1)"; break;	// 8/29/10
		case "aprs_fi_key": 			return "To use aprs location data you will need to sign up for an aprs.fi user account/key (free).  Obtain from http://aprs.fi"; break;	// 3/19/11
		case "title_string": 			return "If text is entered here it replaces the default title in the top bar."; break;	// 6/10/11
		case "use_messaging": 			return "Setting determines whether to use Tickets 2-way Messaging interface. Setting 0 (Default) does not use messaging, 1 to use Email, 2 to use SMS Gateway and 3 to use Email and SMS Gateway"; break;	// 6/10/11
		case "map_in_portal": 			return "Setting determines whether to show map on portal page or not - 1 (default) shows the map"; break;	// 6/10/11
		case "ics_top": 				return "Do/don&#39;t (1/0) show ICS button in top button row.  (Default is 0, for \"No\".)";	 break;	// 5/21/2013
		case "auto_refresh": 			return "Do/don&#39;t (1/0) Automatic refresh for Sit scr, Full scr, Mobile; slash-separated, with 1 = Yes.  (Default is 1/1/1.)";	 break;	// 5/21/2013
		case "broadcast": 				return "Do/don&#39;t (1/0) use &#39;broadcast to other users&#39; - aka HAS, for Hello-All-Stations  (Default is 0, for \"No\")";	 break;	// 5/21/2013
		case "hide_booked": 			return "Booked/scheduled runs don&#39;t appear on the situation screen until they are this-many hours from 'now'.  (Default is 48 hours.)";	 break;	// 5/21/2013
		case "use_responder_mobile": 	return "Use Responder Mobile (rm) page - provides for auto redirect to mobile page for smartphone devices";	 break;	// 9/10/13
		case "responder_mobile_tracking": 	return "Use inbuilt tracking from Responder Mobile (rm) page. 0 is switched off, a positive whole number is the number of minutes between updates.";	 break;	// 9/10/13
		case "local_maps": 				return "Use local maps (OSM) for Responder Mobile (rm) page. Requires download of map tiles from config page";	 break;	// 9/10/13
		case "cloudmade_api": 			return "Cloudmade API code. Used to provide night mode on Responder Mobile (rm) page.";	 break;	// 9/10/13
		case "responder_mobile_forcelogin": return "Booked/scheduled runs don&#39;t appear on the situation screen until they are this-many hours from 'now'.  (Default is 48 hours.)";	 break;	// 9/10/13
		case "use_disp_autostat": 		return "Use Automatic Status updates for Responder status based on changes in dispatch status - Needs setup through config page.";	 break;	// 9/10/13
		case "portal_contact_email": 	return "Contact Us email address that appears on the Portal Page";	 break;	// 9/10/13
		case "portal_contact_phone": 	return "Contact Us phone number that appears on the Portal Page.";	 break;	// 9/10/13
		case "notify_facilities": 		return "Do Notifies to specified address / address list when Receiving Facility or Incident at Facility set.";	 break;	// 9/10/13
		case "notify_in_types": 		return "Do Notifies to specified address for a particular incident type.";	 break;	// 9/10/13
		case "warn_proximity": 			return "For Location Warnings - proximity of warnings selected for current location";	 break;	// 9/10/13
		case "warn_proximity_units": 	return "For Location Warnings, measurment units - M = Miles, K =  Kilometres";	 break;	// 9/10/13
		case "use_osmap": 				return "Use UK Ordnance survey maps. Only works if locale is 1 and Openspace API set. Shows link in infowindow for OS Map popup.";	 break;
		case "xastir_db": 				return "If using private Xastir server for APRS tracking, the database name that the APRS data is written to.";	 break;
		case "xastir_dbpass": 			return "For Xastir Database, the password for access";	 break;
		case "xastir_dbuser": 			return "For Xastir Database the MySQL user id.";	 break;
		case "xastir_server": 			return "The address of the Xastir Database, localhost by default.";	 break;
		case "os_watch": 				return "Example: 5/15/60, meaning units on-scene at priority calls are reported every 5 minutes, on-scene at normal calls every 15, and &#39;Others&#39; every 60 minutes.  See documentation re &#39;Others&#39;.";	 break;		// 4/14/2015
		case "add_uselocation": 		return "When adding a new incident from the Mobile page, use users current position to auto populate incident location";	 break;
		case "bing_api_key": 			return "API key for use with Bing Geolocation service";	 break;
		case "geocoding_provider": 		return "Geocoding provider - 0 (default) for OSM Nominatim, 1 for Google, 2 for Bing";	 break;
		case "addr_source": 			return "0 - don&#39;t bother, 1 - use existing incident street addresses, 2 - use constituents.";	break;
		case "default_map_layer": 		return "0 (Default) - Open Streetmap, 1 - Google Road, 2 - Google Terrain, 3 - Google Satellite, 4 - Google Hybrid, 5 - USGS Topographic, 6 - Dark Map, 7 - Aerial Map.";	break;
		default: 						return "No help for '$setting'"; break;	//	 ics_top
		}
	}

function get_css_day_help($setting){/* get help for color settings	3/15/11 */
	switch($setting) {
		case "page_background":				return "Main Page Background color."; break;
		case "normal_text": 				return "Normal text color."; break;
		case "row_dark": 					return "Dark background color of list entries."; break;
		case "row_light": 					return "Dark background color of list entries."; break;
		case "row_plain": 					return "Plain Row Background color"; break;
		case "select_menu_background": 		return "Background color for pulldown (select) menus."; break;
		case "select_menu_foreground": 		return "Text color for pulldown (select) menus."; break;
		case "form_input_text":				return "Form field text color."; break;
		case "form_input_box_background": 	return "Form field background color."; break;
		case "legend":						return "Text color for unit and facility legends."; break;
		case "links":						return "Text color for links."; break;
		case "other_text": 					return "All other text elements color."; break;
		case "list_header_text": 			return "Text color for list headings."; break;
		default: 							return "No help for '$setting'"; break;	//
		}
	}

function get_css_night_help($setting){/* get help for color settings	3/15/11 */
	switch($setting) {
		case "page_background":				return "Main Page Background color."; break;
		case "normal_text": 				return "Normal text color."; break;
		case "row_dark": 					return "Dark background color of list entries."; break;
		case "row_light": 					return "Dark background color of list entries."; break;
		case "row_plain": 					return "Plain Row Background color"; break;
		case "select_menu_background": 		return "Background color for pulldown (select) menus."; break;
		case "select_menu_foreground": 		return "Text color for pulldown (select) menus."; break;
		case "form_input_text":				return "Form field text color."; break;
		case "form_input_box_background": 	return "Form field background color."; break;
		case "legend":						return "Text color for unit and facility legends."; break;
		case "links":						return "Text color for links."; break;
		case "other_text": 					return "All other text elements color."; break;
		case "list_header_text": 			return "Text color for list headings."; break;
		default: 							return "No help for '$setting'"; break;	//		default: 						return "No help for '$setting'"; break;	//
		}
	}

function get_msg_settings_help($setting){/* get help for color settings	3/15/11 */
	switch($setting) {
		case "email_server":				return "POP3 server address such as pop.gmail.com. Do not include the http://"; break;
		case "email_port": 					return "Email server port - normally 110. For gmail use port 995"; break;
		case "email_protocol": 				return "Leave as default POP3"; break;
		case "email_addon": 				return "Leave as default 'notls'"; break;
		case "email_del": 					return "Delete (1) or not delete (0) server emails after download"; break;
		case "email_folder": 				return "Leave as default INBOX"; break;
		case "email_userid": 				return "Your login ID for the email server. Either just 'userid' or sometimes 'userid@domain.com'. This is email provider dependant"; break;
		case "email_password": 				return "Your email server password"; break;
		case "email_svr_simple":			return "Email server simple or normal authentication - i.e. does this use SSL and a different port. Most public servers do not use simple authentication. 1 for simple 0 for normal."; break;
		case "no_whitelist": 				return "Use (0) or not use (1) the whitelist functionality which stops storing of emails from unknown senders"; break;
		case "smsg_provider": 				return "Shows the current SMS Gateway Provider (only provides name translation in the various screens). Only SMS Responder is currently implemented, please leave with this setting. "; break;
		case "smsg_server":					return "Incoming API page for SMS Gateway provider (the receiving page). Include the http://."; break;
		case "smsg_server2":				return "If SMS Gateway provider has a backup server this is the address for the receiving page. Include the http://"; break;
		case "smsg_og_serv1": 				return "Outgoing Primary server API sending page for the SMS Gateway provider. Include the http://"; break;
		case "smsg_og_serv2": 				return "Outgoing Secondary server API sendig page for SMS Gateway provider. Include the http://"; break;
		case "smsg_force_sec": 				return "Force Tickets to use the backup SMS Gateway server - for use when the primary is still working but maybe slow or otherwise unreliable. If Primary is completely down then Tickets will automate switching to secondary."; break;
		case "smsg_orgcode": 				return "SMS Gateway provider primary authorisation - your account name"; break;
		case "smsg_apipin": 				return "SMS Gateway provider password / pin for the account name / org code"; break;
		case "smsg_mode": 					return "Leave as default - 'SENDXML'"; break;
		case "smsg_replyto": 				return "The reply to number for the SMS Gateway provider (primary server) that ensures that replies to SMS Messages end up on the server and thus into Tickets"; break;
		case "smsg_replyto": 				return "The reply to number for the SMS Gateway provider (secondary server) that ensures that replies to SMS Messages end up on the server and thus into Tickets"; break;
		case "smsg_server_inuse": 			return "Shows what SMS Gateway server is currently in use, primary (1) or secondary (2). Can be changed however this is also automatically changed by Tickets based on a check of server connection"; break;
		case "columns": 					return "For message lists the specific columns to show - enables reduction in detail where screen real estate is limited."; break;
		case "use_autostat": 				return "Whether to use automatic status updates for responders who send reply messages with specific text in the message. Delimiters for the special text are set in start and end tag."; break;
		case "start_tag": 					return "Start delimiter. Text in incoming SMS messages between the start tag and end tag will be used to drive specific responder status changes."; break;
		case "end_tag": 					return "The end tag for the special text in SMS incoming messages that delimits the end of the special text. Text between the start tag and this will be used for auto status updates."; break;
		default: 							return "No help for '$setting'"; break;	//		default: 						return "No help for '$setting'"; break;	//
		}
	}

//		case 'kml files':  				return 'Dont/Do display KML files - 0/1'; break;
//def_zoom_fixed

?>
