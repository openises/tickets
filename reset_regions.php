<?php 
error_reporting(E_ALL);

@session_start();
require_once('./incs/functions.inc.php');	

if(isset($_GET['func']) && ($_GET['func']=='reset')) {
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<HEAD>
		<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<META HTTP-EQUIV="Expires" CONTENT="0" />
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
		<TITLE>Tickets <?php print $disp_version;?></TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()	
	</SCRIPT>
	</HEAD>
<?php
	$query = "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]allocates`";
	$result = mysql_query($query);		

	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]allocates` (
		`id` bigint(8) NOT NULL auto_increment,
		`group` int(4) NOT NULL default '1',
		`type` tinyint(1) NOT NULL default '1',  
		`al_as_of` datetime default NULL,
		`al_status` int(4) default NULL,  
		`resource_id` int(4) default NULL,
		`sys_comments` varchar(64) default NULL,
		`user_id` int(4) NOT NULL default  '0',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	$result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
	$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`;";
	$result_insert = mysql_query($query_insert);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
		$id = $row['id'];
		$tick_stat = $row['status'];
		$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
				(1 , 1, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
		$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		}

	$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`;";
	$result_insert = mysql_query($query_insert);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
		$id = $row['id'];	// 4/13/11
		$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
				(1 , 2, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
		$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		}			

	$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";
	$result_insert = mysql_query($query_insert);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
		$id = $row['id'];	// 4/13/11
		$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
				(1 , 3, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";	// 4/13/11
		$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		}
				
	$query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]user`;";
	$result_insert = mysql_query($query_insert);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) 	{
		$id = $row['id'];
		$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
				(1 , 4, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";
		$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		}		
?>
	<BODY onLoad = 'ck_frames()'>
	<DIV style='font-size: 14px; position: fixed; top: 250px; left: 100px;'>
	All Resources reset to Region 1<br /><br />
	Return to <A style='font-size: 14px;' href="config.php">CONFIG</A>	
	</DIV>
	</BODY>
	</HTML>
<?php
	} else {
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<HEAD>
	<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<TITLE>Tickets <?php print $disp_version;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT>
		function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()	
	</SCRIPT>	
	</HEAD>
	<BODY onLoad = 'ck_frames()'>
	<DIV style='font-size: 14px; position: fixed; top: 250px; left: 100px;'>
	Are you sure you want to reset all resources on the system back to Region 1<br />
	<br />
	If you are SURE, click <A style='font-size: 14px;' href="reset_regions.php?func=reset">RESET REGIONS</A>
	<br />
	<br />
	<A style='font-size: 14px;' href="config.php">CANCEL</A>		
	</DIV>
	</BODY>
	</HTML>
<?php
	}

