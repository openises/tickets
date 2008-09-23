<?php
/*
10/8/07 added domain detection for GMaps API key association
1/8/08 added settings email_reply_to' and call_board;
3/20/08 added settings map height and width;
6/7/08  added kml files setting
6/9/08  revised default 'admin' account level to 'super'
6/12/08 revised version number only
6/27/08 revised version number only
6/28/08 revised version number only
7/16/08 revised default military time
9/13/08 added error_reporting, lat/lng setting entry
9/13/08 added table `unit_types`
9/13/08 added white pages key
9/13/08 GSearch API key
9/15/08 added tables 'login'
9/16/08 corr's to undefined's
9/17/08 dropped field 'hash', rearranged field 'user' in table 'user'
9/17/08 added table 'photos'
9/18/08 added table `cities`
9/22/08 version # only
*/
error_reporting(E_ALL);			// 9/13/08

$version = "2.8.b beta";		// see usage below 9/22/08

function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

switch(strtoupper($_SERVER["HTTP_HOST"])) {
	case '127.0.0.1': {$api_key = "ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BRi_j0U6kJrkFvY4-OX2XYmEAa76BSxM3tBbKeopztUxxRu-Em4ds4HHg";
	break;}

	case 'LOCALHOST': {$api_key = "ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BT2yXp_ZAY8_ufC3CFXhHIE1NvwkxRGkBZARk7Vp6dHzzw2qCN6kP4pTQ";
	break;}

	default: $api_key = "";
	}				// end switch

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
</HEAD><BODY>
<FONT CLASS="header">Installing <?php print $version; ?> </FONT><BR /><BR />
<SCRIPT>
	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_db_host.value == "")			{errmsg+= "\tMySQL HOST name is required\n";}
		if (theForm.frm_db_dbname.value == "")			{errmsg+= "\tMySQL DATABASE name is required\n";}
		if (theForm.frm_api_key.value.length != 86)		{errmsg+= "\tGMaps API key is required - 86 chars\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			return true;
			}
		}				// end function validate(theForm)

</SCRIPT>
<?php

//	foreach ($_POST as $VarName=>$VarValue) {echo "POST:$VarName => $VarValue, <BR />";};
//	foreach ($_GET as $VarName=>$VarValue) 	{echo "GET:$VarName => $VarValue, <BR />";};
//	echo "<BR/>";

	function table_exists($name,$drop_tables) {			//check if mysql table exists, if it's a re-install
		$query 		= "SELECT COUNT(*) FROM $name";
       	$result 	= mysql_query($query);
		$num_rows 	= @mysql_num_rows($result);

		if($num_rows) {
			if($drop_tables) {
				mysql_query("DROP TABLE $name");
				print "<LI> Dropped table '<B>$name</B>'<BR />";
				}
			else {
				print "<FONT CLASS=\"warn\">Table '$name' already exists, use Re-install option instead. Click back in your browser.</FONT></BODY></HTML>";
				exit();
				}
			}
		}

	function prefix ($tbl) {		/* returns concatenated string */
		global $db_prefix;
		return  $db_prefix . $tbl;
		}

	/* insert new values into settings table */
	function do_insert_settings($name,$value) {
		$tablename = prefix("settings");
		$query = "INSERT INTO `$tablename` (name,value) VALUES('$name','$value')";
		$result = mysql_query($query) or die("DO_INSERT_SETTINGS($name,$value) failed, execution halted");
		}
	//create tables
	function create_tables($db_prefix,$drop_tables=1) {
		//check if tables exists and if drop_tables is 1
		table_exists($db_prefix."action",$drop_tables);
		table_exists($db_prefix."assigns",$drop_tables);
		table_exists($db_prefix."chat_messages",$drop_tables);
		table_exists($db_prefix."chat_rooms",$drop_tables);
		table_exists($db_prefix."cities",$drop_tables);		// 9/17/08
		table_exists($db_prefix."clones",$drop_tables);
		table_exists($db_prefix."contacts",$drop_tables);
		table_exists($db_prefix."in_types",$drop_tables);
		table_exists($db_prefix."log",$drop_tables);
		table_exists($db_prefix."logins",$drop_tables);		// 9/17/08
		table_exists($db_prefix."notify",$drop_tables);
		table_exists($db_prefix."patient",$drop_tables);	
		table_exists($db_prefix."photos",$drop_tables);		// 9/17/08
		table_exists($db_prefix."responder",$drop_tables);
		table_exists($db_prefix."settings",$drop_tables);
		table_exists($db_prefix."ticket",$drop_tables);
		table_exists($db_prefix."tracks",$drop_tables);
		table_exists($db_prefix."unit_types",$drop_tables);
		table_exists($db_prefix."un_status",$drop_tables);
		table_exists($db_prefix."user",$drop_tables);
		table_exists($db_prefix."session",$drop_tables);

		$tables = "";
		
		$table_name = prefix("action");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `ticket_id` int(8) NOT NULL default '0',
		  `date` datetime default NULL,
		  `description` text NOT NULL,
		  `user` int(8) default NULL,
		  `action_type` int(8) default NULL,
		  `responder` text,
		  `updated` datetime default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
	// --
	// -- Table structure for table `assigns`
	// --

		$table_name = prefix("assigns");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `as_of` datetime default NULL,
		  `status_id` int(4) default '1',
		  `ticket_id` int(4) default NULL,
		  `responder_id` int(4) default NULL,
		  `comments` varchar(64) default NULL,
		  `user_id` int(4) NOT NULL,
		  `dispatched` datetime default NULL,
		  `responding` datetime default NULL,
		  `clear` datetime default NULL,
		  `in-quarters` datetime default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `chat_messages`
	// --

		$table_name = prefix("chat_messages");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(10) unsigned NOT NULL auto_increment,
		  `message` varchar(255) NOT NULL default '0',
		  `when` datetime default NULL,
		  `chat_room_id` int(7) NOT NULL default '0',
		  `user_id` int(7) NOT NULL default '1',
		  `from` varchar(16) NOT NULL COMMENT 'ip addr',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `chat_rooms`
	// --

		$table_name = prefix("chat_rooms");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(7) NOT NULL auto_increment,
		  `room` varchar(16) NOT NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";


	// -- 
	// -- Table structure for table `cities`
	// -- 
	
		$table_name = prefix("cities");
		$query = "CREATE TABLE `$table_name` (
		`id` int(11) NOT NULL auto_increment,
		`city_zip` int(5) unsigned zerofill NOT NULL,
		`city_name` varchar(50) NOT NULL,
		`city_state` char(2) NOT NULL,
		`city_lat` double NOT NULL,
		`city_lng` double NOT NULL,
		`city_county` varchar(50) NOT NULL,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM;";
	
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";


	// --
	// -- Table structure for table `contacts`
	// --

		$table_name = prefix("contacts");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(7) NOT NULL auto_increment,
		  `name` varchar(48) NOT NULL,
		  `organization` varchar(48) default NULL,
		  `phone` varchar(24) default NULL,
		  `mobile` varchar(24) default NULL,
		  `email` varchar(48) NOT NULL,
		  `other` varchar(24) default NULL,
		  `as-of` datetime NOT NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `in_types`
	// --

		$table_name = prefix("in_types");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(4) NOT NULL auto_increment,
		  `type` varchar(20) NOT NULL,
		  `description` varchar(120) default NULL,
		  `group` varchar(20) default NULL,
		  `sort` INT NOT NULL DEFAULT '0',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM COMMENT='Incident types';";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

		$query = "INSERT INTO `$table_name` (`id`, `type`, `description`, `group`, `sort`) VALUES
			(NULL, 'examp1', 'Example one', 'grp 1', '1'),
			(NULL, 'examp2', 'Example two', 'grp 1', '2');";
		mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);

	// --
	// -- Table structure for table `log` - revised 6/7/08
	// --

		$table_name = prefix("log");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `who` tinyint(7) default NULL,
		  `from` varchar(20) default NULL,
		  `when` datetime default NULL,
		  `code` tinyint(7) NOT NULL default '0',
		  `ticket_id` int(7) default NULL,
		  `responder_id` int(7) default NULL,
		  `info` varchar( 40 ) NULL DEFAULT NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=InnoDB COMMENT='Log of station actions';";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";


	// -- 
	// -- Table structure for table `logins`	// 9/15/08
	// -- 
		$table_name = prefix("logins");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `ip` varchar(15) NOT NULL,
		  `salt` varchar(36) NOT NULL,
		  `intime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=armscii8 COMMENT='login authentication';";
		
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `notify`
	// --

		$table_name = prefix("notify");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `ticket_id` int(8) NOT NULL default '0',
		  `user` int(8) NOT NULL default '0',
		  `execute_path` tinytext,
		  `on_action` tinyint(1) default '0',
		  `on_ticket` tinyint(1) default '0',
		  `on_patient` tinyint(1) default '0',
		  `email_address` tinytext,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `patient`
	// --

		$table_name = prefix("patient");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `ticket_id` int(8) NOT NULL default '0',
		  `name` varchar(32) default NULL,
		  `date` datetime default NULL,
		  `description` text NOT NULL,
		  `user` int(8) default NULL,
		  `action_type` int(8) default NULL,
		  `updated` datetime default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";



// -- 
// -- Table structure for table `photos`
// -- 
		$table_name = prefix("photos");

		$query = "CREATE TABLE `$table_name` (
		`id` bigint(8) NOT NULL auto_increment,
		`description` varchar(256) NOT NULL,
		`ticket_id` int(7) NOT NULL COMMENT 'associated ticket id',
		`taken_by` varchar(48) default NULL,
		`taken_on` varchar(24) default NULL,
		`by` int(7) NOT NULL COMMENT 'user id',
		`on` datetime NOT NULL,
		`from` varchar(16) NOT NULL COMMENT 'ip address',
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `responder`
	// --
		$table_name = prefix("responder");

		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `name` text,
		  `mobile` tinyint(2) default '0',
		  `description` text NOT NULL,
		  `capab` varchar(255) default NULL COMMENT 'Capability',
		  `un_status_id` int(4) NOT NULL default '0',
		  `other` varchar(96) default NULL,
		  `callsign` varchar(24) default NULL,
		  `contact_name` varchar(64) default NULL,
		  `contact_via` varchar(64) default NULL,
		  `lat` double default NULL,
		  `lng` double default NULL,
		  `type` tinyint(1) default NULL,
		  `updated` datetime default NULL,
		  `user_id` int(4) default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `settings`
	// --

		$table_name = prefix("settings");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `name` tinytext,
		  `value` tinytext,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `ticket`
	// --

		$table_name = prefix("ticket");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `in_types_id` int(4) NOT NULL,
		  `contact` varchar(48) NOT NULL default '',
		  `street` varchar(48) default NULL,
		  `city` varchar(32) default NULL,
		  `state` char(2) default NULL,
		  `phone` varchar(16) default NULL,
		  `lat` double default NULL,
		  `lng` double default NULL,
		  `date` datetime default NULL,
		  `problemstart` datetime default NULL,
		  `problemend` datetime default NULL,
		  `scope` text NOT NULL,
		  `affected` text,
		  `description` text NOT NULL,
		  `comments` text,
		  `status` tinyint(1) NOT NULL default '0',
		  `owner` tinyint(4) NOT NULL default '0',
		  `severity` int(2) NOT NULL default '0',
		  `updated` datetime default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `tracks`
	// --

		$table_name = prefix("tracks");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(7) NOT NULL auto_increment,
		  `packet_id` varchar(48) default NULL,
		  `source` varchar(96) default NULL,
		  `latitude` double default NULL,
		  `longitude` double default NULL,
		  `speed` int(8) default NULL,
		  `course` int(8) default NULL,
		  `altitude` int(8) default NULL,
		  `symbol_table` varchar(96) default NULL,
		  `symbol_code` varchar(96) default NULL,
		  `status` varchar(96) default NULL,
		  `closest_city` varchar(200) default NULL,
		  `mapserver_url_street` varchar(200) default NULL,
		  `mapserver_url_regional` varchar(200) default NULL,
		  `packet_date` datetime default NULL,
		  `updated` datetime NOT NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `packet_id` (`packet_id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

//		-- 
//		-- Table structure for table `unit_types`		8/13/08
//		-- 
		
		$table_name = prefix("unit_types");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` int(11) NOT NULL auto_increment,
		  `name` varchar(16) NOT NULL,
		  `description` varchar(48) NOT NULL,
		  `color` varchar(7) NOT NULL,
		  `updated` datetime NOT NULL,
		  `from` varchar(16) NOT NULL COMMENT 'ip',
		  `user_id` int(11) NOT NULL COMMENT 'by',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Allows for variable unit types' AUTO_INCREMENT=5 ;";
		
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

//		-- 
//		--  data for table `unit_types`
//		-- 
		
		$query = "INSERT INTO `$table_name` (`id`, `name`, `description`, `color`, `updated`, `from`, `user_id`) VALUES 
			(1, 'EMS', 'Emergency medicine services', 'ffff00', '0000-00-00 00:00:00', '', 1),
			(2, 'Fire', 'Fire Services', 'ff0000', '0000-00-00 00:00:00', '', 1),
			(3, 'Police', 'Police services', '0000ff', '0000-00-00 00:00:00', '', 1),
			(4, 'Mutual', 'Mutual assistance', 'ffffff', '0000-00-00 00:00:00', '', 1);
			";
		
		mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);
		
//		--
// 		-- Table structure for table `un_status`
// 		--

		$table_name = prefix("un_status");
		$query = 	"CREATE TABLE `$table_name` (
	    `id` bigint(4) NOT NULL auto_increment,
	    `status_val` varchar(20) NOT NULL,
	    `description` varchar(60) default NULL,
	    `group` varchar(20) default NULL,
	    `sort` int(11) NOT NULL default '0',
	    PRIMARY KEY  (`id`),
	    UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

		$query = "INSERT INTO `$table_name` ( `id` , `status_val` , `description` , `group` , `sort` ) VALUES
			(NULL , 'examp1', 'Example one', 'first', '1'),
			(NULL , 'examp2', 'Example two', 'second', '2');
			";
		mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);

	// --
	// -- Table structure for table `user` - revisions 9/17/08
	// --
		$table_name = prefix("user");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `user` text COMMENT 'userid',
		  `passwd` tinytext COMMENT 'md5 hash',
		  `info` text NOT NULL,
		  `level` tinyint(1) default NULL,
		  `email` text,
		  `ticket_per_page` tinyint(1) default NULL,
		  `sort_desc` tinyint(1) default '0',
		  `sortorder` tinytext,
		  `reporting` tinyint(1) default '1',
		  `callsign` varchar(12) default NULL COMMENT 'added 9/23/07',
		  `clone_id` int(11) NOT NULL default '0' COMMENT 'db clone to use',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `clones`
	// --
		$table_name = prefix("clones");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` int(4) NOT NULL auto_increment,
		  `name` varchar(16) default NULL,
		  `prefix` varchar(8) default NULL,
		  `date` datetime default NULL COMMENT 'last used',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

	// --
	// -- Table structure for table `session`
	// --
		$table_name = prefix("session");
		$query = 	"CREATE TABLE `$table_name` (
		  `id` bigint(4) NOT NULL auto_increment,
		  `sess_id` varchar(40) NULL,
		  `user_name` varchar(40) NULL,
		  `user_id` int(4) NULL,
		  `level` int(2) NULL,
		  `ticket_per_page` varchar(16) NULL,
		  `sortorder` varchar(16) NULL,
		  `scr_width` varchar(16) NULL,
		  `scr_height` varchar(16) NULL,
		  `browser` varchar(100) NULL,
		  `last_in` bigint  NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

		print "<LI> Created tables " . $tables . "<BR />";
		}


	function create_user() {	// create default admin user (note: priv's level 'super') and guest // 6/9/08 
		$tablename = prefix("user");
		print "<P>";
		mysql_query("INSERT INTO `$tablename` (user,passwd,info,level,ticket_per_page,sort_desc,sortorder,reporting) VALUES('admin',PASSWORD('admin'),'Administrator',0,0,1,'date',0)") or die("INSERT INTO user failed, execution halted at line " . __LINE__);
		print "<LI> Created user '<B>admin</B>'";
		mysql_query("INSERT INTO `$tablename` (user,passwd,info,level,ticket_per_page,sort_desc,sortorder,reporting) VALUES('guest',PASSWORD('guest'),'Guest',3,0,1,'date',0)") or die("INSERT INTO user failed, execution halted at line " . __LINE__);
		print "<LI> Created user '<B>guest</B>'";
		print "</P>";
		}

	//insert settings
	function insert_settings() {
		global $version, $api_key;

		do_insert_settings('_aprs_time','0');
		do_insert_settings('_version',$version);
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','65');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','0');
		do_insert_settings('aprs_poll','0');			// new 10/15/07
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
		do_insert_settings('gsearch_api_key','');		// 9/13/08 
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
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');
		do_insert_settings('validate_email','1');
		do_insert_settings('wp_key','729c1a751fd3d2428cfe2a7b43442c64');		// 9/13/08 
		
		print "<LI> Inserted default settings";
		}

	//output mysql settings to mysql.inc.php
	function write_conf($host,$db,$user,$password,$prefix) {
		if (!$fp = fopen('./incs/mysql.inc.php', 'a'))
        	print '<LI> <FONT CLASS="warn">Cannot open mysql.inc.php for writing</FONT>';
		else {
			ftruncate($fp,0);
			fwrite($fp, "<?php\n");
//			fwrite($fp, "	/* generated by " . basename( __FILE__). " " date('r') . "*/\n");
			fwrite($fp, "	/* generated by {basename( __FILE__) date('r')} */\n");
			fwrite($fp, '	$mysql_host 	= '."'$host';\n");
			fwrite($fp, '	$mysql_db 		= '."'$db';\n");
			fwrite($fp, '	$mysql_user 	= '."'$user';\n");
			fwrite($fp, '	$mysql_passwd 	= '."'$password';\n");
			fwrite($fp, '	$mysql_prefix 	= '."'$prefix';\n");
			fwrite($fp, '?>');
			}

		fclose($fp);
		print '<LI> Wrote configuration to \'<B>./incs/mysql.inc.php</B>\'';
		}

	//upgrade db from 0.65 to 0.7
	function upgrade_065_07($prefix) {
		print '<LI> Upgrading structure <B>0.65->0.7...</B><BR />';
		mysql_query("ALTER TABLE $prefix"."ticket ADD severity int(2) NOT NULL default '0'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #1 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD level tinyint(1) default NULL") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #2 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD ticket_per_page tinyint(1) default '0'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #3 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD sort_desc tinyint(1) default '0'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #4 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD sortorder tinytext") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #5 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."user ADD reporting tinyint(1) default '1'") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #6 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."action ADD user int(8) default NULL") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #7 failed</FONT>");
		mysql_query("ALTER TABLE $prefix"."action ADD action_type int(8) default NULL") or die("<FONT CLASS=\"warn\">Could not upgrade 0.65->0.7, query #8 failed</FONT>");

		print '<LI> Replacing permissions and actions...</B>';
		mysql_query("UPDATE $prefix"."user SET level='1' WHERE admin='1'") or die("<FONT CLASS=\"warn\">Could not replace user permissions (admin)</FONT>");
		mysql_query("UPDATE $prefix"."user SET level='2' WHERE admin='0'") or die("<FONT CLASS=\"warn\">Could not replace user permissions (user)</FONT>");
		mysql_query("UPDATE $prefix"."action SET action_type='10', user='0'") or die("<FONT CLASS=\"warn\">Could not fix action data</FONT>");
		mysql_query("ALTER TABLE $prefix"."user DROP admin") or die("<FONT CLASS=\"warn\">Could not drop user field 'admin'</FONT>");

		print '<LI> Replacing settings...</B>';
		mysql_query("DELETE FROM $prefix"."settings") or die("<FONT CLASS=\"warn\">Could not <remove old settings</FONT>");
		insert_settings();
		}

//	if($_GET['go']) {				/* connect to mysql database if option isn't writeconf' */
	if (array_key_exists('go', $_GET)) {		// 9/16/08


		$db_prefix=$_POST['frm_db_prefix'];

		if ($_POST['frm_option'] != 'writeconf') {
			$query = "@mysql_connect({$_POST['frm_db_host']}, {$_POST['frm_db_user']}, {$_POST['frm_db_password']})";
//			print __LINE__ . " " . $query . "<BR>";

			if (!@mysql_connect($_POST['frm_db_host'], $_POST['frm_db_user'], $_POST['frm_db_password'])) {
				$the_pw = (empty($_POST['frm_db_password']))? "<i>none entered</i>"  : $_POST['frm_db_password'] ;
				print "<B>Connection to MySQL failed using the following entered values:</B><BR /><BR />\n";
				print "MySQL Host:<B> " . $_POST['frm_db_host'] . "</B><BR />\n";
				print "MySQL Username:<B> " . $_POST['frm_db_user'] . "</B><BR />\n";
				print "MySQL Password:<B> " . $the_pw . "</B><BR /><BR />\n";
				print "MySQL Database Name:<B> " . $_POST['frm_db_dbname'] . "</B><BR /><BR />\n";
				print "Please correct these entries and try again.<BR /><BR />";
?>
				<FORM NAME='db_error' METHOD='post' ACTION = 'install.php'>
				<INPUT TYPE='submit' VALUE='Try again'>
				</FORM>
				</BODY>
				</HTML>
<?php
				die();
				}		// end if (!$result)

//			mysql_connect($_POST['frm_db_host'], $_POST['frm_db_user'], $_POST['frm_db_password']) or die("<FONT CLASS=\"warn\">Couldn't connect to database on '$_POST[frm_db_host]', make sure it is running and user has permissions. Click back in your browser.</FONT>");
			mysql_select_db($_POST['frm_db_dbname']) or die("<FONT CLASS=\"warn\">Couldn't select database '$_POST[frm_db_dbname]', make sure it exists and user has permissions. Click back in your browser.</FONT>");
			}

		//run the functions

		switch($_POST['frm_option']) {
			case 'install':{
				create_tables($_POST['frm_db_prefix']);
				create_user();
				insert_settings();
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> Tickets version $version installation complete!";
				break;
				}
			case 'install-drop':{
				create_tables($_POST['frm_db_prefix'],1);
				create_user();
				insert_settings();
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> Re-Installation done!";
				break;
				}
			case 'upgrade-0.65':{
				upgrade_065_07($_POST['frm_db_prefix']);
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> Upgrade <B>0.65->0.7</B> complete!";
				break;
				}
			case 'writeconf':{
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> All done.";
				break;
				}
			default:
				print "<LI> <FONT CLASS=\"warn\">'$_POST[frm_option]' is not a valid option!</FONT>";
			}

		print '<BR /><BR /><FONT CLASS="warn">Your Tickets installation is now complete - the start page is index.php .</FONT>';
		print '<BR /><BR /><FONT CLASS="warn">It is strongly recommended that you move/delete/change rights on install.php after this</FONT>';
		print '<BR /><BR /><A HREF="index.php"><< Start Tickets</A>';
		}
//	else if ($_GET['help']) {		// 
	else if (array_key_exists('help', $_GET)) {		// 9/16/08
?>
		<BLOCKQUOTE>
		Fill in the install form with your mysql server settings. The 'table prefix' option enables you to prefix the tables with
		an optional name if you're only using one database or need multiple instances. Thus a prefix of <B>my_</B> would name the
		tables <B>my_action</B>, <B>my_user</B> etc.<BR /><BR />

		The Google Maps API key is obtained from them at http://www.google.com/apis/maps/signup.html and is free.  There, you'll be asked
		for the domain name to which the key applies, and that will be the Tickets server and directory address.  If you're planning multiple
		installations as many keys as you may need are available.  Please note:  That key is an 86-character string, which should be
		copy/pasted from them into the form.  Hint: email that key to yourself, along with the other form entries.<BR /><BR />

		The <B>Re-install</B> option <FONT CLASS="warn">drops all Tickets data</FONT> in the specified database and re-installs them;
		if the tables already exists this option is required. If the tables names are prefixed, you have to specify it in the form.<BR /><BR />
<!--
		The <B>Upgrade</B> option upgrades an existing Tickets database from the specified version to the newest available. If the database
		structure has been modified in any way this script <FONT CLASS="warn">will most probably fail</FONT>. Please make sure to backup your database
		before proceeding with this upgrade. All the settings will be replaced.<BR /><BR />
-->

		The <B>Write Configuration Only</B> option writes the specified mysql settings to the file <B>'mysql.inc.php'</B> but doesn't alter the database
		in any way.

		<BR /><BR /><A HREF="install.php"><< back to the install script</A></BLOCKQUOTE>
<?php
		}
	else {

		$dir = "./";
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if (is_dir($filename)) {
			    $files[] = $filename;
			    }
			}

		$dirsOK = TRUE;
		if (!in_array("incs", $files)) 		{$dirsOK=FALSE;}
		if (!in_array("markers", $files)) 	{$dirsOK=FALSE;}

		if (!$dirsOK) {
			print "<br><br><br><center><h3>At least one of the Tickets subdirectories is missing, and this needs to be corrected.<br /><br />You might check into how the Tickets zip file was unzipped or otherwise installed.<br><br><br><br><A HREF='mailto:shoreas@Gmail.com?subject=Tickets Install Problem'><u>Or click here to contact the developer.</u></A></h3></center>";
			}
		else {
?>
			Fill in this entire form to install Tickets. Make sure to read through the <A HREF="install.php?help=1"><U>help</U></A>.<BR /><BR />
			<FORM NAME = 'install_frm' METHOD="post" ACTION="install.php?go=1"  onSubmit='return validate(document.install_frm)' >
			<FIELDSET style="width: 900px;"><LEGEND style="font-weight: bold; color: #000; font-family: verdana; font-size: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;From your MySQL installation&nbsp;&nbsp;&nbsp;&nbsp;</LEGEND>
			<TABLE BORDER="0">
			<TR CLASS="even"><TD width="200px">MySQL Host: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_host" VALUE=""></TD></TR>
			<TR CLASS="odd"><TD>MySQL Username: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_user" VALUE=""></TD></TR>
			<TR CLASS="even"><TD>MySQL Password: </TD><TD><INPUT TYPE="password" SIZE="45" MAXLENGTH="255" NAME="frm_db_password"  VALUE=""></TD></TR>
			</TABLE>
			</FIELDSET>
			<br />
			<FIELDSET style="width: 900px;"><LEGEND style="font-weight: bold; color: #000; font-family: verdana; font-size: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;Tickets Stuff&nbsp;&nbsp;&nbsp;&nbsp;</LEGEND>
			<TABLE BORDER="0">
			<TR CLASS="even"><TD width="200px">MySQL Database: </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_dbname" VALUE=""> your just-created MySQL database</TD></TR>
			<TR CLASS="odd"><TD>MySQL Table Prefix (optional): </TD><TD><INPUT TYPE="text" SIZE="45" MAXLENGTH="255" NAME="frm_db_prefix" VALUE=""> your choice</TD></TR>
			<TR CLASS="even"><TD>GMaps API Key:<BR />(domain: <?php print $_SERVER['HTTP_HOST'];?>)</TD><TD><INPUT TYPE="text" SIZE="110" MAXLENGTH="255" NAME="frm_api_key"  VALUE="<?php print $api_key; ?>"><BR>
				&nbsp;&nbsp;&nbsp;&nbsp;Obtain from Google at <A HREF="http://www.google.com/apis/maps/signup.html">http://www.google.com/apis/maps/signup.html </A>
				</TD></TR>
			<TR CLASS="odd"><TD>Install Option: </TD><TD>
			<INPUT TYPE="radio" VALUE="install" NAME="frm_option" checked> Install Database - new<BR />
			<INPUT TYPE="radio" VALUE="install-drop" NAME="frm_option"> Re-install Database<BR />
	<!--	<INPUT TYPE="radio" VALUE="upgrade-0.65" NAME="frm_option"> Upgrade 0.65 -> 0.7<BR />	-->
			<INPUT TYPE="radio" VALUE="writeconf" NAME="frm_option"> Write Configuration File Only<BR /><BR>
			</TD></TR>
			<TR CLASS="even"><TD></TD><TD><INPUT TYPE="Reset" VALUE="Reset form">&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="Submit" VALUE="Do it"></TD></TR>
			</TABLE>
			</FORM>
			<?php
			}
		}
?>
</BODY></HTML>