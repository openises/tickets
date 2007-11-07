<?php
$version = "2.4.a beta";		// see usage below

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

	/* insert new values into settings table */
	function do_insert_settings($name,$value) {
		$query = "INSERT INTO $_POST[frm_db_prefix]settings (name,value) VALUES('$name','$value')"; 
		$result = mysql_query($query) or die("do_insert_settings($name,$value) failed, execution halted");
		}

	//create tables
	function create_tables($db_prefix,$drop_tables=0) {
		//check if tables exists and if drop_tables is 1
		table_exists($db_prefix."action",$drop_tables);
		table_exists($db_prefix."patient",$drop_tables);
		table_exists($db_prefix."ticket",$drop_tables);
		table_exists($db_prefix."user",$drop_tables);
		table_exists($db_prefix."notify",$drop_tables);
		table_exists($db_prefix."settings",$drop_tables);
		table_exists($db_prefix."responder",$drop_tables);
		table_exists($db_prefix."log",$drop_tables);
		table_exists($db_prefix."tracks",$drop_tables);
	
		//patient table - added 7/15/07 by AS
		if ($db_prefix) $db_prefix_patient = $db_prefix."patient"; else $db_prefix_patient = "patient";
		$query = 	"CREATE TABLE $db_prefix_patient (
				  	id int(8) NOT NULL auto_increment,
  					ticket_id int(8) NOT NULL default '0',
  					name varchar(32) default NULL,
  					date datetime default NULL,
  					description text NOT NULL,
  					user int(8) default NULL,
  					action_type int(8) default NULL,
  					updated datetime default NULL,
  					PRIMARY KEY  (id),
  					UNIQUE KEY ID (id)
					) TYPE=MyISAM;";		
		mysql_query($query) or die("CREATE TABLE $db_prefix_patient failed, execution halted");
					
		//responder table - added 7/15/07 by AS
		if ($db_prefix) $db_prefix_responder = $db_prefix."responder"; else $db_prefix_responder = "responder";
		$query = 	"CREATE TABLE $db_prefix_responder (
				  `id` int(8) NOT NULL auto_increment,
				  `name` text,
				  `mobile` tinyint(2) default '0',
				  `description` text NOT NULL,
				  `status` varchar(64) default NULL,
				  `callsign` varchar(24) default NULL,
				  `contact_name` varchar(64) default NULL,
				  `contact_via` varchar(64) default NULL,
				  `lat` double default NULL,
				  `lng` double default NULL,
				  `type` tinyint(1) default NULL,
				  `updated` datetime default NULL,
				   	PRIMARY KEY  (id),
  					UNIQUE KEY ID (id)
					) TYPE=MyISAM;";		
		mysql_query($query) or die("CREATE TABLE $db_prefix_responder failed, execution halted");
					
		//action table
		if ($db_prefix) $db_prefix_action = $db_prefix."action"; else $db_prefix_action = "action";
		$query = 	"CREATE TABLE $db_prefix_action (
				  	id int(8) NOT NULL auto_increment,
  					ticket_id int(8) NOT NULL default '0',
  					date datetime default NULL,
  					description text NOT NULL,
  					user int(8) default NULL,
  					action_type int(8) default NULL,
  					responder text,
  					updated datetime default NULL,
  					PRIMARY KEY  (id),
  					UNIQUE KEY ID (id)
					) TYPE=MyISAM;";		
		mysql_query($query) or die("CREATE TABLE $db_prefix_action failed, execution halted");
					
		//notify table
		if ($db_prefix) $db_prefix_notify = $db_prefix."notify"; else $db_prefix_notify = "notify";		
		$query = 	"CREATE TABLE $db_prefix_notify (
  					id int(8) NOT NULL auto_increment,
  					ticket_id int(8) NOT NULL default '0',
  					user int(8) NOT NULL default '0',
  					execute_path tinytext,
  					on_action tinyint(1) default '0',
  					on_ticket tinyint(1) default '0',
  					email_address tinytext,
  					PRIMARY KEY  (id),
  					UNIQUE KEY ID (id)
					) TYPE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE $db_prefix_notify failed, execution halted");
			
		//settings table
		if ($db_prefix) $db_prefix_settings = $db_prefix."settings"; else $db_prefix_settings = "settings";
		$query = 	"CREATE TABLE $db_prefix_settings (
 					id int(8) NOT NULL auto_increment,
  					name tinytext,
  					value tinytext,
  					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE $db_prefix_settings failed, execution halted");
		
		//ticket table
		if ($db_prefix) $db_prefix_ticket = $db_prefix."ticket"; else $db_prefix_ticket = "ticket";	
		$query = 	"CREATE TABLE $db_prefix_ticket (
  					id int(8) NOT NULL auto_increment,
				    contact varchar(48) NOT NULL default '',
				    street varchar(48) default NULL,
				    city varchar(32) default NULL,
				    state char(2) default NULL,
				    phone varchar(16) default NULL,
				    lat double default NULL,
				    lng double default NULL,  					
  					date datetime default NULL,
  					problemstart datetime default NULL,
  					problemend datetime default NULL,
  					scope text NOT NULL,
  					affected text,
  					description text NOT NULL,
  					comments text,
  					status tinyint(1) NOT NULL default '0',
  					owner tinyint(4) NOT NULL default '0',
  					severity int(2) NOT NULL default '0',
  					updated datetime default NULL,
 					PRIMARY KEY  (id),
  					UNIQUE KEY ID (id)
					) TYPE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE $db_prefix_ticket failed, execution halted");
					
					
		//user table
		if ($db_prefix) $db_prefix_user = $db_prefix."user"; else $db_prefix_user = "user";	
		$query = 	"CREATE TABLE $db_prefix_user (
  					id int(8) NOT NULL auto_increment,
  					passwd tinytext,
  					info text NOT NULL,
  					user tinytext,
  					level tinyint(1) default NULL,
  					email tinytext,
  					ticket_per_page tinyint(1) default NULL,
  					sort_desc tinyint(1) default '0',
  					sortorder tinytext,
  					reporting tinyint(1) default '1',
					callsign varchar(12) default NULL COMMENT 'added 9/23/07',
  					PRIMARY KEY  (id),
  					UNIQUE KEY ID (id)
					) TYPE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE $db_prefix_user failed, execution halted");

		//log table - added 9/03/07 by AS
		if ($db_prefix) $db_prefix_log = $db_prefix."log"; else $db_prefix_log = "log";
		$query = 	"CREATE TABLE `$db_prefix_log` (
					`id` tinyint(7) NOT NULL auto_increment,
					`who` tinyint(7) default NULL,
					`when` timestamp NULL default NULL,
					`what` tinyint(4) default NULL,
					`more` text,
					`from` varchar(128) default NULL,
					PRIMARY KEY	(id)
					) TYPE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE $db_prefix_log failed, execution halted");
		
		if ($db_prefix) $db_prefix_tracks = $db_prefix."tracks"; else $db_prefix_tracks = "tracks";	
		
		$query = 	"CREATE TABLE $db_prefix_tracks (
		  `id` smallint(7) NOT NULL auto_increment,
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
		PRIMARY KEY  (id),
		UNIQUE KEY `packet_id` (`packet_id`),
		UNIQUE KEY ID (id)
		) TYPE=MyISAM;";
		mysql_query($query) or die("CREATE TABLE $db_prefix_tracks failed, execution halted");

					
		print "<LI> Created tables '$db_prefix_action', '$db_prefix_patient', '$db_prefix_notify', '$db_prefix_settings', '$db_prefix_user', '$db_prefix_ticket', '$db_prefix_responder', '$db_prefix_log', '$db_prefix_tracks'<BR />";
		}
	
	//create default admin user
	function create_user() {
		mysql_query("INSERT INTO $_POST[frm_db_prefix]user (user,passwd,info,level,ticket_per_page,sort_desc,sortorder,reporting) VALUES('admin',PASSWORD('admin'),'Administrator',1,0,1,'date',0)") or die("INSERT INTO user failed, execution halted");
		print "<LI> Created user '<B>admin</B>'";
		mysql_query("INSERT INTO $_POST[frm_db_prefix]user (user,passwd,info,level,ticket_per_page,sort_desc,sortorder,reporting) VALUES('guest',PASSWORD('guest'),'Guest',3,0,1,'date',0)") or die("INSERT INTO user failed, execution halted");
		print "<LI> Created user '<B>guest</B>'";
		}
	
	//insert settings
	function insert_settings() {
		global $version;
		$thekey = $_POST['frm_api_key'];
		do_insert_settings('_aprs_time','0');	
		do_insert_settings('gmaps_api_key',$thekey);	
		do_insert_settings('_version',$version);		// note; defined above
		do_insert_settings('abbreviate_affected','30');
		do_insert_settings('abbreviate_description','65');
		do_insert_settings('allow_custom_tags','0');
		do_insert_settings('allow_notify','0');
		do_insert_settings('date_format','Y-M-d H:i');
		do_insert_settings('def_city','');
		do_insert_settings('def_lat','39.1');		// center US
		do_insert_settings('def_lng','-90.7');
		do_insert_settings('def_st','');
		do_insert_settings('def_zoom','3');
		do_insert_settings('delta_mins','0');
		do_insert_settings('frameborder','1');
		do_insert_settings('framesize','50');
		do_insert_settings('guest_add_ticket','0');
		do_insert_settings('host','www.yourdomain.com');
		do_insert_settings('link_capt','');		
		do_insert_settings('link_url','');		
		do_insert_settings('login_banner','Welcome to Tickets - an Open Source Dispatch System');
		do_insert_settings('map_caption','Your area');
		do_insert_settings('military_time','1');
		do_insert_settings('restrict_user_add','0');
		do_insert_settings('restrict_user_tickets','0');
		do_insert_settings('ticket_per_page','0');
		do_insert_settings('ticket_table_width','640');
		do_insert_settings('UTM','0');		
		do_insert_settings('aprs_poll','0');		
		do_insert_settings('validate_email','1');
		print "<LI> Inserted default settings";
		}
	
	//output mysql settings to mysql.inc.php
	function write_conf($host,$db,$user,$password,$prefix) {	
		if (!$fp = fopen('mysql.inc.php', 'a'))
        	print '<LI> <FONT CLASS="warn">Cannot open mysql.inc.php for writing</FONT>';
		else {
			ftruncate($fp,0);
			fwrite($fp, "<?php\n");
			fwrite($fp, "	/* generated by install.php */\n");
			fwrite($fp, '	$mysql_host 	= '."'$host';\n");
			fwrite($fp, '	$mysql_db 		= '."'$db';\n");
			fwrite($fp, '	$mysql_user 	= '."'$user';\n");
			fwrite($fp, '	$mysql_passwd 	= '."'$password';\n");
			fwrite($fp, '	$mysql_prefix 	= '."'$prefix';\n");
			fwrite($fp, '?>');
			}
		
		fclose($fp);
		print '<LI> Wrote configuration to \'<B>mysql.inc.php</B>\'';
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

	if($_GET['go']) {				/* connect to mysql database if option isn't writeconf' */

		if ($_POST['frm_option'] != 'writeconf') {
		
			if (!@mysql_connect($_POST['frm_db_host'], $_POST['frm_db_user'], $_POST['frm_db_password'])) {
				$the_pw = (empty($_POST['frm_db_password']))? "<i>none entered</i>"  : $_POST['frm_db_password'] ;
				print "<B>Connection to MySQL failed using the following entered values:</B><BR /><BR />\n";
				print "MySQL Host:<B> " . $_POST['frm_db_host'] . "</B><BR />\n";
				print "MySQL Username:<B> " . $_POST['frm_db_user'] . "</B><BR />\n";
				print "MySQL Password:<B> " . $the_pw . "</B><BR /><BR />\n";
				print "Please obtain correct values for these entries and try again.<BR /><BR />";		
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
				print "<LI> Installation done!";
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
	else if ($_GET['help']) {
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
			<TR CLASS="even"><TD></TD><TD><INPUT TYPE="Submit" VALUE="Install"></TD></TR>
			</TABLE>
			</FORM>
			<?php
			}
		}
/*
10/8/07 - added domain detection for GMaps API key association
*/
?>
</BODY></HTML>