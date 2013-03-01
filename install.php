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
10/08/08 version # only
10/11/08 major schema update
10/17/08 added '__sleep' setting
10/19/08 added pager nos. to responder
10/22/08 expanded table notify schema
11/6/08 table user default corrections, sql_mode
1/17/09 changed `in-quarters` to `on_scene`, insert `auto_route` settings
1/17/09 version no. to '2.9 B beta'
1/18/08 added 'in service' unit status
1/25/09 team tables renamed
1/27/09 added default area code setting, unit_types schema, responder schema updates
2/3/09 version no.
2/8/09 table tracks_hh added
2/11/09 session table expanded
2/14/09 session flags to varchar
2/21/09 check file write-able
2/24/09 added 'terrain' setting
3/17/09 chgd aprs_poll to auto_poll
3/22/09 removed redundant def_area_code
4/10/09 responder schema update
1/23/10 - removed table session
8/5/10 version number base - to permit index.php to update schema, internet setting added
8/8/10    accomodate absent mysql.inc.php - as install trigger
10/29/10  'PASSWORD' => 'MD5' to accommodate old MySQL versions
12/18/10   write permissions test corrected
1/10/11 Added default setting for Group or dispatch
5/11/12 Added code for quick start.
1/9/2013 API key is no longer mandatory
*/

error_reporting(E_ALL);				// 2/3/09

$version = "2.20 A base beta";				// see usage below 8/5/10

function dump($variable) {
	echo "\n<PRE>";					// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

$api_key = "AIzaSyBN2v_821i9ivnaWoNXb0MIV3Dz8RQ3xqc";			// 1/9/2013

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
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
//		if (theForm.frm_api_key.value.length != 86)		{errmsg+= "\tGMaps API key is required - 86 chars\n";} -- 1/9/2013
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

		table_exists($db_prefix."action",$drop_tables);		// 10/11/08	 - 1/25/09
		table_exists($db_prefix."allocates",$drop_tables);		
		table_exists($db_prefix."assigns",$drop_tables);
		table_exists($db_prefix."captions",$drop_tables);		
		table_exists($db_prefix."certs",$drop_tables);
		table_exists($db_prefix."certs_x_user",$drop_tables);
		table_exists($db_prefix."chat_invites",$drop_tables);		
		table_exists($db_prefix."chat_messages",$drop_tables);
		table_exists($db_prefix."chat_rooms",$drop_tables);
		table_exists($db_prefix."cities",$drop_tables);
		table_exists($db_prefix."clones",$drop_tables);
		table_exists($db_prefix."codes",$drop_tables);		
		table_exists($db_prefix."constituents",$drop_tables);		
		table_exists($db_prefix."contacts",$drop_tables);
		table_exists($db_prefix."courses",$drop_tables);
		table_exists($db_prefix."courses_x_user",$drop_tables);
		table_exists($db_prefix."css_day",$drop_tables);
		table_exists($db_prefix."css_night",$drop_tables);		
		table_exists($db_prefix."documents",$drop_tables);
		table_exists($db_prefix."documents_log",$drop_tables);
		table_exists($db_prefix."facilities",$drop_tables);		
		table_exists($db_prefix."fac_status",$drop_tables);	
		table_exists($db_prefix."fac_types",$drop_tables);	
		table_exists($db_prefix."hints",$drop_tables);	
		table_exists($db_prefix."insurance",$drop_tables);		
		table_exists($db_prefix."in_types",$drop_tables);
		table_exists($db_prefix."log",$drop_tables);
		table_exists($db_prefix."logins",$drop_tables);
		table_exists($db_prefix."mmarkup",$drop_tables);	
		table_exists($db_prefix."mmarkup_cats",$drop_tables);	
		table_exists($db_prefix."modules",$drop_tables);		
		table_exists($db_prefix."notify",$drop_tables);
		table_exists($db_prefix."patient",$drop_tables);
		table_exists($db_prefix."photos",$drop_tables);
		table_exists($db_prefix."pin_ctrl",$drop_tables);	
		table_exists($db_prefix."places",$drop_tables);		
		table_exists($db_prefix."region",$drop_tables);		
		table_exists($db_prefix."region_type",$drop_tables);		
		table_exists($db_prefix."remote_devices",$drop_tables);		
		table_exists($db_prefix."responder",$drop_tables);
		table_exists($db_prefix."rss",$drop_tables);		
		table_exists($db_prefix."settings",$drop_tables);
		table_exists($db_prefix."skills",$drop_tables);
		table_exists($db_prefix."skills_x_user",$drop_tables);
		table_exists($db_prefix."stats_settings",$drop_tables);		
		table_exists($db_prefix."teams",$drop_tables);
		table_exists($db_prefix."teams_x_user",$drop_tables);
		table_exists($db_prefix."team_types",$drop_tables);
		table_exists($db_prefix."ticket",$drop_tables);
		table_exists($db_prefix."titles",$drop_tables);
		table_exists($db_prefix."tracks",$drop_tables);
		table_exists($db_prefix."tracks_hh",$drop_tables);
		table_exists($db_prefix."unit_types",$drop_tables);
		table_exists($db_prefix."un_status",$drop_tables);
		table_exists($db_prefix."user",$drop_tables);

		$tables = "";

// -- phpMyAdmin SQL Dump
// -- version 2.9.2
// -- http://www.phpmyadmin.net
// --
// -- Host: localhost
// -- Generation Time: Oct 11, 2008 at 12:35 PM
// -- Server version: 5.0.27
// -- PHP Version: 5.2.1
// --
// -- Database: `tickets_2_8_c`
// --


// -- --------------------------------------------------------

// --
// -- Table structure for table `action`
// --
		$table_name = prefix("action");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `ticket_id` int(8) NOT NULL default '0',
		 `date` datetime default NULL,
		 `description` text NOT NULL,
		 `user` int(8) default NULL,
		 `action_type` int(8) default NULL,
		 `responder` text,
		 `updated` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables = $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `assigns`
// --

		$table_name = prefix("assigns");
		$query = "CREATE TABLE `$table_name` (
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
		 `on_scene` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `certs`
// --

		$table_name = prefix("certs");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `certificate` varchar(48) NOT NULL COMMENT 'certificate description',
		 `source` varchar(48) NOT NULL COMMENT 'issuing agency',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `certs_x_user`
// --

		$table_name = prefix("certs_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `certificate_id` int(3) NOT NULL COMMENT 'certification description',
		 `user_id` int(4) NOT NULL COMMENT 'issued to - user id',
		 `date` date default NULL COMMENT 'date issued',
		 `comment` varchar(48) default NULL COMMENT 'comment re certification issued',
		 `by` int(7) NOT NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `chat_messages`
// --

		$table_name = prefix("chat_messages");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(10) unsigned NOT NULL auto_increment,
		 `message` varchar(255) NOT NULL default '0',
		 `when` datetime default NULL,
		 `chat_room_id` int(7) NOT NULL default '0',
		 `user_id` int(7) NOT NULL default '1',
		 `from` varchar(16) NOT NULL COMMENT 'ip addr',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `chat_rooms`
// --

		$table_name = prefix("chat_rooms");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(7) NOT NULL auto_increment,
		 `room` varchar(16) NOT NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

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
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `clones`
// --

		$table_name = prefix("clones");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(4) NOT NULL auto_increment,
		 `name` varchar(16) default NULL,
		 `prefix` varchar(8) default NULL,
		 `date` datetime default NULL COMMENT 'last used',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `contacts`
// --

		$table_name = prefix("contacts");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(7) NOT NULL auto_increment,
		 `name` varchar(48) NOT NULL,
		 `organization` varchar(48) default NULL,
		 `phone` varchar(24) default NULL,
		 `mobile` varchar(24) default NULL,
		 `email` varchar(48) NOT NULL,
		 `other` varchar(24) default NULL,
		 `as-of` datetime NOT NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `courses`
// --

		$table_name = prefix("courses");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `course` varchar(48) NOT NULL COMMENT 'certificate description',
		 `source` varchar(48) NOT NULL COMMENT 'sponsor agency',
		 `location` varchar(48) NOT NULL COMMENT 'location',
		 `duration` varchar(48) NOT NULL COMMENT 'no. days or hours',
		 `basis` varchar(48) NOT NULL COMMENT 'cost basis',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `courses_x_user`
// --

		$table_name = prefix("courses_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `courses_id` int(4) NOT NULL COMMENT 'certification description',
		 `user_id` int(4) NOT NULL COMMENT 'issued to - user id',
		 `date` date default NULL COMMENT 'date taken',
		 `comment` varchar(48) default NULL COMMENT 'comment re certification issued',
		 `by` int(7) NOT NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `documents`
// --

		$table_name = prefix("documents");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(10) unsigned NOT NULL auto_increment,
		 `name` varchar(64) NOT NULL,
		 `status` enum('locked','unlocked','na') NOT NULL default 'na',
		 `locked_by` int(7) NOT NULL COMMENT 'user index',
		 `locked_on` datetime default NULL,
		 `info` tinytext,
		 `keyword` varchar(64) default NULL,
		 `type` varchar(64) default NULL,
		 `size` int(10) unsigned NOT NULL,
		 `author` int(10) unsigned default NULL,
		 `source` int(10) unsigned default NULL,
		 `maintainer` int(10) unsigned default NULL,
		 `revision` varchar(64) default NULL COMMENT 'revision information',
		 `created` datetime default NULL,
		 `modified` datetime default NULL,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `documents_log`
// --

		$table_name = prefix("documents_log");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(10) unsigned NOT NULL auto_increment,
		 `user_id` int(10) unsigned NOT NULL,
		 `document_id` int(10) unsigned NOT NULL,
		 `revision` int(10) unsigned NOT NULL,
		 `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `in_types`
// --

		$table_name = prefix("in_types");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(4) NOT NULL auto_increment,
		 `type` varchar(20) NOT NULL,
		 `description` varchar(120) default NULL,
		 `group` varchar(20) default NULL,
		 `sort` int(11) NOT NULL default '0',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Incident types' AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

//		--
//		--  data for table `in_types`
//		--

			$query = "INSERT INTO `$table_name` (`id`, `type`, `description`, `group`, `sort`) VALUES
				(NULL, 'examp1', 'Example one', 'grp 1', '1'),
				(NULL, 'examp2', 'Example two', 'grp 2', '2');";
			mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);

// -- --------------------------------------------------------

// --
// -- Table structure for table `log`
// --

		$table_name = prefix("log");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `who` tinyint(7) default NULL,
		 `from` varchar(20) default NULL,
		 `when` datetime default NULL,
		 `code` tinyint(7) NOT NULL default '0',
		 `ticket_id` int(7) default NULL,
		 `responder_id` int(7) default NULL,
		 `info` varchar(40) default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Log of station actions' AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `logins`
// --

		$table_name = prefix("logins");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `ip` varchar(15) NOT NULL,
		 `salt` varchar(36) NOT NULL,
		 `intime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='login authentication' AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------


// --
// -- Table structure for table `notify`				// 10/22/08
// --

		$table_name = prefix("notify");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `ticket_id` int(8) NOT NULL default '0',
		  `user` int(8) NOT NULL default '0',
		  `execute_path` tinytext,
		  `severities` int(1) NOT NULL default '0' COMMENT '0=NA, 1=all, 2=top 2, 3=top only',
		  `on_action` tinyint(1) default '0',
		  `on_ticket` tinyint(1) default '0',
		  `on_patient` tinyint(1) default '0',
		  `email_address` varchar(255) default NULL,
		  `pager` varchar(255) default NULL COMMENT 'pipe-sep''d ',
		  `pager_cb` varchar(96) default NULL COMMENT 'pager call-back no.',
		  `by` int(7) NOT NULL COMMENT 'user',
		  `from` varchar(16) NOT NULL COMMENT 'IP addr',
		  `on` datetime NOT NULL COMMENT 'updated',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `patient`
// --

		$table_name = prefix("patient");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `ticket_id` int(8) NOT NULL default '0',
		 `name` varchar(32) default NULL,
		 `fullname` varchar(64) NULL default NULL,
		 `dob` varchar(32) NULL default NULL,
		 `gender` int(1) NOT NULL default '0',
		 `insurance_id` int(3) NOT NULL default '0' COMMENT 'see table insurance',
		 `facility_contact` varchar(64) NULL,
		 `facility_id` int(3) NOT NULL default '0',		 
		 `date` datetime default NULL,
		 `description` text NOT NULL,
		 `user` int(8) default NULL,
		 `action_type` int(8) default NULL,
		 `updated` datetime default NULL,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

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
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `responder` 1/27/09, 4/10/09
// --

		$table_name = prefix("responder");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(8) NOT NULL auto_increment,
		  `name` text,
		  `mobile` tinyint(2) default '0',
		  `direcs` tinyint(2) NOT NULL default '1' COMMENT '0=>no directions, 1=> yes',
		  `aprs` tinyint(2) NOT NULL default '0',
		  `instam` tinyint(2) NOT NULL default '0' COMMENT 'instamapper',
		  `description` text NOT NULL,
		  `capab` varchar(255) default NULL COMMENT 'Capability',
		  `un_status_id` int(4) NOT NULL default '0',
		  `other` varchar(96) default NULL,
		  `callsign` varchar(24) default NULL,
		  `contact_name` varchar(64) default NULL,
		  `contact_via` varchar(64) default NULL,
		  `pager_p` varchar(64) default NULL,
		  `pager_s` varchar(64) default NULL,
		  `send_no` varchar(64) default NULL,
		  `lat` double default NULL,
		  `lng` double default NULL,
		  `type` tinyint(1) default NULL,
		  `updated` datetime default NULL,
		  `user_id` int(4) default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

// -- --------------------------------------------------------

// --
// -- Table structure for table `settings`
// --

		$table_name = prefix("settings");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `name` tinytext,
		 `value` tinytext,
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `skills`
// --

		$table_name = prefix("skills");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `skill` varchar(48) NOT NULL,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `skills_x_user`
// --

		$table_name = prefix("skills_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `skills_id` int(3) NOT NULL COMMENT 'certification description',
		 `user_id` int(4) NOT NULL COMMENT 'skill held - user id',
		 `level` enum('b','m','h','x','na') NOT NULL default 'na' COMMENT ' beginner, moderate, high, expert, NA',
		 `comment` varchar(48) default NULL COMMENT 'comment re certification issued',
		 `by` int(7) NOT NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `team_types`
// --

		$table_name = prefix("team_types");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `type` varchar(48) NOT NULL COMMENT 'team type',
		 `comment` varchar(48) NOT NULL COMMENT 'issuing agency',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `teams`
// --

		$table_name = prefix("teams");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `team` varchar(48) NOT NULL COMMENT 'team name- major',
		 `sub-group` varchar(48) NOT NULL COMMENT 'team name- major',
		 `ttypes_id` int(7) NOT NULL COMMENT 'team name',
		 `mission` varchar(48) NOT NULL COMMENT 'issuing agency',
		 `leader` int(4) NOT NULL COMMENT 'team leader - user id',
		 `leader_dpty` int(4) NOT NULL COMMENT 'team leader deputy - user id',
		 `formed` date default NULL COMMENT 'date formed',
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `teams_x_user`
// --

		$table_name = prefix("teams_x_user");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `teams_id` int(4) NOT NULL COMMENT 'certification description',
		 `member_id` int(7) NOT NULL COMMENT 'team member - user id',
		 `status` int(2) default NULL COMMENT 'team member status',
		 `date_a` date default NULL COMMENT 'date assigned',
		 `date_e` date default NULL COMMENT 'date assignment ended',
		 `comment` varchar(48) default NULL COMMENT 'comment re assignment',
		 `by` int(7) default NULL COMMENT 'entered by - user index',
		 `from` varchar(16) default NULL COMMENT 'entered from - IP addr',
		 `on` datetime default NULL COMMENT 'date last updated',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `ticket`
// --

		$table_name = prefix("ticket");
		$query = "CREATE TABLE `$table_name` (
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
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `titles`
// --
		$table_name = prefix("titles");
		$query = "CREATE TABLE `$table_name` (
		 `id` int(7) NOT NULL auto_increment,
		 `title` varchar(24) NOT NULL,
		 `by` int(7) NOT NULL COMMENT 'user index',
		 `from` varchar(16) NOT NULL COMMENT 'IP addr',
		 `on` datetime NOT NULL COMMENT 'last update',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------
// --
// -- Table structure for table `tracks`
// --

		$table_name = prefix("tracks");
		$query = "CREATE TABLE `$table_name` (
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
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `packet_id` (`packet_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------
// --
// -- Table structure for table `tracks_hh`		- 2/8/09
// --
		$table_name = prefix("tracks_hh");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(7) NOT NULL auto_increment,
		  `source` varchar(96) default NULL,
		  `latitude` double default NULL,
		  `longitude` double default NULL,
		  `speed` int(8) default NULL,
		  `course` int(8) default NULL,
		  `altitude` int(8) default NULL,
		  `status` varchar(96) default NULL,
		  `updated` datetime NOT NULL,
		  `from` varchar(16) NOT NULL COMMENT 'ip addr',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";
// -- --------------------------------------------------------

// --
// -- Table structure for table `un_status`				- 1/18/08
// --
		$table_name = prefix("un_status");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(4) NOT NULL auto_increment,
		 `status_val` varchar(20) NOT NULL,
		 `description` varchar(60) default NULL,
		 `group` varchar(20) default NULL,
		 `sort` int(11) NOT NULL default '0',
		 PRIMARY KEY (`id`),
		 UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);

//		--
//		--  data for table `un_status`
//		--

		$tables .= $table_name . ", ";
			$query = "INSERT INTO `$table_name` ( `id` , `status_val` , `description` , `group` , `sort` ) VALUES
				(NULL, 'available', 'Available', 'av', 1),
				(NULL, 'unavailable', 'Unavailable', 'unav', 3),
				(NULL, 'in_service', 'In service', 'inserv', 0);
				";
			mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);


// -- --------------------------------------------------------

// --
// -- Table structure for table `unit_types`				// 1/27/09
// --

		$table_name = prefix("unit_types");
			$query = "CREATE TABLE `$table_name` (
			  `id` int(11) NOT NULL auto_increment,
			  `name` varchar(16) NOT NULL,
			  `description` varchar(48) NOT NULL,
			  `icon` int(3) NOT NULL default '0',
			  `_on` datetime NOT NULL,
			  `_from` varchar(16) NOT NULL COMMENT 'ip',
			  `_by` int(7) NOT NULL COMMENT 'by',
			  PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Allows for variable unit types' AUTO_INCREMENT=6 ;";

//		dump ($query);
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);
		$tables .= $table_name . ", ";

//		--
//		--  data for table `unit_types`
//		--

			$query = "INSERT INTO `$table_name` (`id`, `name`, `description`, `icon`, `_on`, `_from`, `_by`) VALUES
				(1, 'example', 'An example unit type', 3, '2009-01-28 14:13:06', '127.0.0.1', 1);
				";

			mysql_query($query) or die("INSERT INTO TABLE failed, execution halted at line ". __LINE__);


// -- --------------------------------------------------------

// --
// -- Table structure for table `user`				 11/6/08
// --
		$DB = $db_prefix . $_POST['frm_db_dbname'];
		$table_name = prefix("user");
		$query = "CREATE TABLE `$table_name` (
		 `id` bigint(8) NOT NULL auto_increment,
		 `user` text NOT NULL COMMENT 'userid',
		 `passwd` tinytext NOT NULL COMMENT 'MySQL hash',
		 `name_l` text default NULL  COMMENT 'last',
		 `name_f` text default NULL  COMMENT 'first',
		 `name_mi` text default NULL  COMMENT 'middle',
		 `dob` date default NULL,
		 `title_id` tinyint(2) default NULL COMMENT 'title',
		 `addr_street` text default NULL,
		 `addr_city` text default NULL,
		 `addr_st` text default NULL,
		 `disp` tinyint(1) default 1 NULL COMMENT 'dispatch access',
		 `files` tinyint(1) default 0 COMMENT 'docs data access',
		 `pers` tinyint(1) default 0 COMMENT 'personnel data access',
		 `teams` tinyint(1) default 0 COMMENT 'teams data access',
		 `status` enum('approved','pending','na') NOT NULL default 'approved',
		 `open_at` enum('d','f','p','t') NOT NULL default 'd' COMMENT 'after logon',
		 `ident` text default NULL COMMENT 'identification',
		 `info` text default NULL COMMENT 'account information',
		 `phone_p` text default NULL COMMENT 'phone primary',
		 `phone_s` text default NULL COMMENT 'phone secondary',
		 `phone_m` text default NULL COMMENT 'phone mobile',
		 `level` tinyint(1) NOT NULL  default 0 COMMENT 'privileges',
		 `email` text  default NULL COMMENT 'email addr - primary',
		 `email_s` text default NULL COMMENT 'email addr - secondary',
		 `ticket_per_page` tinyint(1) default NULL,
		 `sort_desc` tinyint(1) default 0,
		 `sortorder` tinytext default NULL,
		 `reporting` tinyint(1) default 1,
		 `callsign` varchar(12) default NULL COMMENT 'added 9/23/07',
		 `db_prefix` text  default NULL COMMENT 'db clone to use',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		mysql_query($query) or die("CREATE TABLE failed, execution halted at line ". __LINE__);$tables .= $table_name . ", ";

		print "<LI> Created tables " . substr($tables, 0, -2) . "<BR />";
		}


	function create_user() {	// create default super user (note: priv's level 'super') and guest // 6/9/08, 10/29/10
		global $db_prefix;
		$tablename = prefix("user");
		print "<P>";
		mysql_query("INSERT INTO `$tablename` (`user`,`passwd`,`info`,`level`,`ticket_per_page`,`sort_desc`,`sortorder`,`reporting`,`db_prefix`) VALUES('admin',MD5('admin'),'Super-administrator',0,0,1,'date',0, '$db_prefix')") or die("INSERT INTO user failed, execution halted at line " . __LINE__);
		print "<LI> Created user '<B>admin</B>'";
		mysql_query("INSERT INTO `$tablename` (`user`,`passwd`,`info`,`level`,`ticket_per_page`,`sort_desc`,`sortorder`,`reporting`,`db_prefix`) VALUES('guest',MD5('guest'),'Guest',3,0,1,'date',0,'$db_prefix')") or die("INSERT INTO user failed, execution halted at line " . __LINE__);
		print "<LI> Created user '<B>guest</B>'";
		print "</P>";
		}

	//insert settings
	function insert_settings() {
		global $version, $api_key;

		do_insert_settings('_aprs_time','0');
		do_insert_settings('_sleep','5');				// 10/17/08 --
		do_insert_settings('_version',$version);
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','30');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','1');
		do_insert_settings('auto_poll','0');			// new 10/15/07, rev'd 3/17/09
		do_insert_settings('auto_route','1');					// 1/17/09
		do_insert_settings('call_board','1');			// new 1/10/08
		do_insert_settings('chat_time','4');			// new 1/16/08
		do_insert_settings('closed_interval','');
		do_insert_settings('date_format','n/j/y H:i');
		do_insert_settings('def_area_code','');
		do_insert_settings('def_city','');
		do_insert_settings('def_lat','39.1');			// approx center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_st','');
		do_insert_settings('def_zoom','3');
		do_insert_settings('def_zoom_fixed','0');
		do_insert_settings('delta_mins','0');
		do_insert_settings('email_reply_to','');		// new 1/10/08
		do_insert_settings('frameborder','1');
		do_insert_settings('framesize','50');
		do_insert_settings('gmaps_api_key',$_POST['frm_api_key']);		//
		do_insert_settings('group_or_dispatch','0');		//		1/10/11
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('host','www.yourdomain.com');
		do_insert_settings('instam_key','');			// 4/10/09
		do_insert_settings('kml_files','1');		// new 6/7/08
		do_insert_settings('lat_lng','0');			// 9/13/08
		do_insert_settings('link_capt','');
		do_insert_settings('link_url','');
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('map_height','512');
		do_insert_settings('map_width','512');
		do_insert_settings('military_time','1');				// 7/16/08
		do_insert_settings('msg_text_1','');
		do_insert_settings('msg_text_2','');
		do_insert_settings('msg_text_3','');
		do_insert_settings('quick','0');
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('serial_no_ap','1');					// 1/17/09
		do_insert_settings('situ_refr','');
		do_insert_settings('terrain','1');						// 2/24/09
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');
		do_insert_settings('validate_email','1');
		do_insert_settings('wp_key','729c1a751fd3d2428cfe2a7b43442c64');		// 9/13/08
		do_insert_settings ('internet','1');		// 8/5/10

		print "<LI> Inserted default settings";
		}

	//output mysql settings to mysql.inc.php
	function write_conf($host,$db,$user,$password,$prefix) {
		if (!$fp = fopen('./incs/mysql.inc.php', 'a'))
        	print '<LI> <FONT CLASS="warn">Cannot open mysql.inc.php for writing</FONT>';
		else {
			ftruncate($fp,0);
			fwrite($fp, "<?php\n");
			fwrite($fp, "	/* generated by '" . basename( __FILE__) . "' " . date('r') . " */\n");
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

//			$query = "SET GLOBAL sql_mode='STRICT_ALL_TABLES'";					// 11/6/08
//			mysql_query($query) or die("<FONT CLASS=\"warn\">SQL error at line " . __LINE__ . " </FONT>");

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
//			case 'upgrade-0.65':{
//				upgrade_065_07($_POST['frm_db_prefix']);
//				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
//				print "<LI> Upgrade <B>0.65->0.7</B> complete!";
//				break;
//				}
			case 'writeconf':{
				write_conf($_POST['frm_db_host'],$_POST['frm_db_dbname'],$_POST['frm_db_user'],$_POST['frm_db_password'],$_POST['frm_db_prefix']);
				print "<LI> All done.";
				break;
				}
			default:
				print "<LI> <FONT CLASS=\"warn\">'$_POST[frm_option]' is not a valid option!</FONT>";
			}

		print '<BR /><BR /><FONT CLASS="warn">Your Tickets installation is now complete - the start page is \'index.php\' .</FONT>';
		print '<BR /><BR /><FONT CLASS="warn">It is strongly recommended that you move/delete/change rights on install.php after this</FONT>';
		print '<BR /><BR /><A HREF="index.php?first_start=yes"><< Start Tickets >></A>';	//	5/11/12 Changed link for quick start.		
		}
//	else if ($_GET['help']) {		//
	else if (array_key_exists('help', $_GET)) {		// 9/16/08
?>
		<BLOCKQUOTE>
		1.  Fill in the install form with your mysql server settings. The 'table prefix' option enables you to prefix the tables with
		an optional name if you're only using one database or need multiple instances. Thus a prefix of <B>my_</B> would name the
		tables <B>my_action</B>, <B>my_user</B> etc.<BR /><BR />

		2.  The Google Maps API key is obtained from them at http://www.google.com/apis/maps/signup.html and is free.  There, you'll be asked
		for the domain name to which the key applies, and that will be the Tickets server and directory address.  If you're planning multiple
		installations as many keys as you may need are available.  Please note:  That key is an 86-character string, which should be
		copy/pasted from them into the form.  Hint: email that key to yourself, along with the other form entries.<BR /><BR />

		3.  The <B>Re-install</B> option <FONT CLASS="warn">drops all Tickets data</FONT> in the specified database and re-installs them;
		if the tables already exists this option is required. If the tables names are prefixed, you have to specify it in the form.<BR /><BR />
<!--
		The <B>Upgrade</B> option upgrades an existing Tickets database from the specified version to the newest available. If the database
		structure has been modified in any way this script <FONT CLASS="warn">will most probably fail</FONT>. Please make sure to backup your database
		before proceeding with this upgrade. All the settings will be replaced.<BR /><BR />
-->

		4.  The <B>Write Configuration Only</B> option writes the specified mysql settings to the file <B>'mysql.inc.php'</B> in the <B>'incs'</B>
		subdirectory but doesn't alter the database	in any way.<BR /><BR />

		5.  The file <B>'mysql.inc.php'</B> in the <B>'incs'</B> subdirectory <B>must be write-able in any install option</B>.

		<BR /><BR /><A HREF="install.php"><< back to the install script</A></BLOCKQUOTE>
<?php
		}
	else {
		$filename = './incs';							// 12/18/10
		if (!is_writable($filename)) {					// 8/8/10 - 
		    die ("ERROR! Directory '{$filename}' is not writable. 'Write' permissions must be corrected for installation.");
			}
		$filename = './incs/mysql.inc.php';				// 2/21/09

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
			print "<br><br><br><center><h3>At least one of the Tickets subdirectories is missing, and this needs to be corrected.<br /><br />You might check into how the Tickets zip file was unzipped or otherwise installed.<br><br><br><br><A HREF='mailto:info@TicketsCAD.org?subject=Tickets Install Problem'><u>Or click here to contact the developer.</u></A></h3></center>";
			}
		else {
?>
			Complete this form to install Tickets version <?php print $version;?>. Make sure to read through the <A HREF="install.php?help=1"><U>help</U></A> information.<BR /><BR />
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
			<TR CLASS="even"><TD>Google API Key:<BR /></TD><TD><INPUT TYPE="text" SIZE="70" MAXLENGTH="255" NAME="frm_api_key"  VALUE="<?php print $api_key; ?>"><BR>
				&nbsp;&nbsp;&nbsp;&nbsp;Note: this is a temporary key; Obtain your site's API key at https://code.google.com/apis/console/
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