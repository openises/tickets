<?php
/*
12/16/11 initial release
12/19/11 'group' => 'group_name'
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
	<HTML>
	<HEAD>
	<TITLE>Classes Database Initialization</TITLE>
	<META NAME="Description" CONTENT="">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />
	<SCRIPT>
		function depart() {location.href = "index.php";  }
	</SCRIPT>
	</HEAD>
	<BODY>
<?php
if (array_key_exists('initialize', $_POST)) {
	
	$query 	= "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]courses`;";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

	$query 	= " CREATE TABLE `$GLOBALS[mysql_prefix]courses` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `course` varchar(48) NOT NULL,
		  `group_name` varchar(48) DEFAULT NULL,
		  `ident` varchar(48) DEFAULT NULL,
		  `credits` varchar(48) DEFAULT NULL,
		  `duration` varchar(48) DEFAULT NULL,
		  `source` varchar(48) DEFAULT NULL,
		  `sort` int(7) DEFAULT '0',
		  `_by` int(7) DEFAULT '0',
		  `_from` varchar(16) DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			
	$query 	= "DROP TABLE IF EXISTS `$GLOBALS[mysql_prefix]courses_taken`;";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

	$query 	= "CREATE TABLE `$GLOBALS[mysql_prefix]courses_taken` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `courses_id` int(7) NOT NULL,
		  `user_id` int(7) NOT NULL,
		  `date` date NOT NULL,
		  `info` varchar(64) DEFAULT NULL,
		  `_by` int(7) DEFAULT '0',
		  `_from` varchar(16) DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			
?>
	<br /><br /><br /><br /><br /><br /><center>
	<h2>
	Classes database initialization complete!
	</h2>
	<input type = button value = "Finished" onClick = "setTimeout('depart()',750);" />
	
<?php
	}
else {
?>
	<br /><br /><br /><br /><br /><br /><center>
	<h2>
	Press <i>Proceed</i> to initialize Classes Database - cannot be undone!
	</h2>
	<form name = 'init_form' method = post action = '<?php echo basename(__FILE__);?>'>
	<input type = button value = "Proceed" onClick = "if (confirm('Last chance - click OK to proceed')){this.form.submit();}" />
	<input type = hidden name = 'initialize' value = 'initialize' />
	</form>
	<br /><br /><br />
	<input type = button value = "Cancel" onClick = "setTimeout('depart()',750);" />

<?php
	}
?>	
</BODY>
</HTML>

