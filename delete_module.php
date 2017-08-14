<?php
/*
5/17/11 Module Deletion Script
*/

@session_start();

require_once($_SESSION['fip']);
error_reporting(E_ALL);				// 2/3/09
do_login(basename(__FILE__));	// session_start()
$tickets_dir = getcwd();	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
</HEAD><BODY>

<?php

function module_tabs_exist($name) {
	$query 		= "SELECT COUNT(*) FROM `$GLOBALS[mysql_prefix]modules`";
	$result 	= mysql_query($query);
	$num_rows 	= @mysql_num_rows($result);
	if($num_rows) {
		$query_exists	= "SELECT * FROM `$GLOBALS[mysql_prefix]modules` WHERE `mod_name`=\"{$name}\"";
		$result_exists	= mysql_query($query_exists) or do_error($query_exists, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$num_rows = mysql_num_rows($result_exists);
		if($num_rows != 0) {
			return 1;
		} else {
			return 0;
		}	
		} else {
		return 0;
		}
	}
	
function mod_table_exists($tablename) {			//check if mysql table exists, if it's a re-install
	$query 		= "SELECT COUNT(*) FROM $tablename";
	$result 	= mysql_query($query);
	$num_rows 	= @mysql_num_rows($result);
	if($num_rows) {
		return 1;
	} else {
		return 0;
	}
	}
	

if (isset($_POST['module_choice'])) { // Handle the form.
//	$query_exists	= "SELECT * FROM `$GLOBALS[mysql_prefix]modules` WHERE `mod_name`=\"{$name}\"";
//	$result_exists	= mysql_query($query_exists) or do_error($query_exists, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//	$num_rows = mysql_num_rows($result_exists);
?>
	<DIV style='background-color:#CECECE; position: absolute; width: 60%; height: 60%; left: 20%; top: 10%; border:2px inset #FFF2BF; display: block; text-align: center'>
	<TABLE BORDER="0">
	<TR><TH class='heading'>Module Deletion - Confirmation</TH></TR>
	<TR><TD>&nbsp;</TD></TR>
	<TR><TD>&nbsp;</TD></TR>
	<FORM NAME="delete_2" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<TR><TD style='font-size: 14px; font-weight: bold; background-color: #AEAEAE; text-align: center;'>Selected Module: <?php print $_POST['module_choice'];?></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
	<TR><TD>&nbsp;</TD></TR>
	<TR><TD>&nbsp;</TD></TR>	
	<TR><TD CLASS="td_label">Click Submit to confirm module deletion or Cancel to abort</TD></TR>	
	<TR><TD>&nbsp;</TD></TR>
	<TR><TD>&nbsp;</TD></TR>	
	<INPUT TYPE='hidden' NAME='confirmation' VALUE='<?php print $_POST['module_choice'];?>'>
	<INPUT TYPE='hidden' NAME='flag' VALUE='Confirmation Received'>	
	<TR><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel" onClick="window.location.href='config.php'" >&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" /></TD></TR>
	</FORM></TABLE>	
	</DIV>

<?php	
} elseif (isset($_POST['confirmation'])) { // If form not submitted print form.
	$mod_name = $_POST['confirmation'];
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]modules` WHERE `mod_name`= '$mod_name'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$row = mysql_fetch_assoc($result);
	$module_name = $row['mod_name'];
	$table = $row['table'];	

	function rmdir_recurse($path) {  
		$path = rtrim($path, '/').'/';  
		$handle = opendir($path);  
		while(false !== ($file = readdir($handle))) {  
			if($file != '.' and $file != '..' ) {  
				$fullpath = $path.$file;  
				if(is_dir($fullpath)) rmdir_recurse($fullpath); else unlink($fullpath);  
			}  
		}  
		closedir($handle);  
		rmdir($path);
		return TRUE;	
	} 
		
?>
	<DIV style='background-color:#CECECE; position: absolute; width: 60%; height: 60%; left: 20%; top: 10%; border:2px inset #FFF2BF; display: block; text-align: center'>
	<BR /><BR /><BR /><BR /><?php	print $_POST['flag'];?><BR /><BR />
	Deleting Tickets Module........<?php print $_POST['confirmation'];?><BR /><BR />
	Dropping Table........<?php print $table;?>...........	
<?php	
	$query	= "DROP table `$GLOBALS[mysql_prefix]" . $table ."`";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if($result) {
		print "Success<BR />";
		} else {
		print "Failed<BR />";
		}
?>	

	Removing Directory and files /modules/<?php print $_POST['confirmation'];?>..........
<?php
	$directory = $tickets_dir . "/modules/" . $module_name;
	$rem_dir = rmdir_recurse($directory);
	if($rem_dir ==  true) {
		print "Success<BR />";
		} else {
		print "Failed<BR />";
		}		
?>
	Removing Entry from Modules Table..........
<?php
	$query	= "DELETE FROM `$GLOBALS[mysql_prefix]modules` WHERE `mod_name`= '$mod_name'";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	if($result) {
		print "Success<BR />";
		} else {
		print "Failed<BR />";
		}
?>		
	<BR /><BR />Module Removed Successfuly<BR /><BR />
	<A HREF="config.php"><< Return to Configuration Page >></A>
	</DIV>
<?php

} else {

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]modules`";
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$num = mysql_num_rows($result);

	
	$choice = "<SELECT name='module_choice' >";
	$choice .= "<OPTION style='color:#FFFF00; background-color:#CC0000;' selected>Select Module to Delete</OPTION>";
	while ($row = mysql_fetch_assoc($result)) {
		$module_name = $row['mod_name'];
		$table = $row['table'];
		$choice .= "<OPTION VALUE='$module_name'>$module_name</OPTION>";
		}
	$choice .= "</SELECT>";

?>
	<DIV style='background-color:#CECECE; position: absolute; width: 60%; height: 60%; left: 20%; top: 10%; border:2px inset #FFF2BF; display: block'>	
	<center>TICKETS MODULES INSTALLATION.</center>
	<DIV style='background-color:#CECECE; position: absolute; width: 40%; height: 20%; left: 5%; top: 10%; border:2px inset #FFF2BF; display: block'>
	<TABLE BORDER="0">
	<TH COLSPAN="2">Delete a Tickets Module<BR /></TH>
	<FORM NAME="delete_1" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<TR CLASS="even"><TD CLASS="td_label">Module: </TD><TD><?php print $choice;?></TD>
	<TR CLASS="even"><TD COLSPAN="2" ALIGN="center"><input type="submit" name="submit" value="Submit" /></TD></TR>
	</FORM></TABLE>
	</div>
	<DIV style='background-color:#CECECE; position: absolute; width: 50%; height: 80%; right: 5%; top: 10%; border:2px inset #FFF2BF; display: block'>
	HELP PANEL
	</DIV>
	</DIV>
	<?php
	}
?>
