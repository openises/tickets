<?php 
/* Change log - cleanse_regions.php
11/03/11	New File - accessed from Config to cleanse the region allocations where there are issues caused by duplicate entries	
*/

error_reporting(E_ALL);

@session_start();
require_once('./incs/functions.inc.php');	

if(isset($_GET['func']) && ($_GET['func']=='clean')) {
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
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
		<TITLE>Tickets <?php print $disp_version;?></TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
	</SCRIPT>
	</HEAD>
<?php
	// Declare arrays for all resource ids
	$region_ids = array();
	$ticket_ids = array();
	$user_ids = array();
	$unit_ids = array();
	$facility_ids = array();
	// end of array declaration

	// get region ids.
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$region_ids[] = $row['id'];
		}
	// end of region ids

	// get ticket ids.
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$ticket_ids[] = $row['id'];
		}
	// end of ticket ids
		
	// get user ids	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$user_ids[] = $row['id'];
		}
	// end of user ids

	// get responder / unit ids
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$unit_ids[] = $row['id'];
		}	
	// end of responder ids		

	// get facility ids
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$facility_ids[] = $row['id'];
		}
	// end of facility ids

	$text_output = "";
	// cleanse entries for Users
		$counter1 = 0;
		foreach ($region_ids as $value) {
			foreach ($user_ids as $value2) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 4;";
				$result = mysql_query($query);
				$num_entries = mysql_num_rows($result);
				if ($num_entries > 1) {
					$counter1++;
					for ($i = 1; $i < $num_entries; $i++) {
						$query_d  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 4 LIMIT 1;";
						$result_d = mysql_query($query_d);				
					}
				}
			}
		}
	// end of User cleanse

	// cleanse entries for Tickets
		$counter2 = 0;
		foreach ($region_ids as $value) {
			foreach ($ticket_ids as $value2) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 1;";
				$result = mysql_query($query);
				$num_entries = mysql_num_rows($result);
				if ($num_entries > 1) {
					$counter2++;				
					for ($i = 1; $i < $num_entries; $i++) {
						$query_d  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 1 LIMIT 1;";
						$result_d = mysql_query($query_d);				
					}
				}
			}
		}
	// end of Ticket cleanse

	// cleanse entries for Responders
		$counter3 = 0;
		foreach ($region_ids as $value) {
			foreach ($unit_ids as $value2) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 2;";
				$result = mysql_query($query);
				$num_entries = mysql_num_rows($result);
				if ($num_entries > 1) {
					$counter3++;					
					for ($i = 1; $i < $num_entries; $i++) {
						$query_d  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 2 LIMIT 1;";
						$result_d = mysql_query($query_d);				
					}
				}
			}
		}
	// end of Responder cleanse	

	// cleanse entries for Facilities
		$counter4 = 0;
		foreach ($region_ids as $value) {
			foreach ($facility_ids as $value2) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 3;";
				$result = mysql_query($query);
				$num_entries = mysql_num_rows($result);
				if ($num_entries > 1) {
					$counter4++;
					for ($i = 1; $i < $num_entries; $i++) {
						$query_d  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 3 LIMIT 1;";
						$result_d = mysql_query($query_d);				
					}
				}
			}
		}
		
	// end of Facility cleanse	

	$text_output .= $counter1 >= 1 ? "User Allocations Cleansed<BR />" : "User Allocation Cleansing not required<BR />";
	$text_output .= $counter2 >= 1 ? "Ticket Allocations Cleansed<BR />" : "Ticket Allocation Cleansing not required<BR />";
	$text_output .= $counter3 >= 1 ? "Responder Allocations Cleansed<BR />" : "Responder Allocation Cleansing not required<BR />";
	$text_output .= $counter4 >= 1 ? "Facility Allocations Cleansed<BR />" : "Facility Allocation Cleansing not required<BR />";	
?>
	<BODY onLoad = 'ck_frames()'>
	<?php print $text_output;?>
	<DIV style='font-size: 14px; position: fixed; top: 150px; left: 100px;'>
	Region table Cleansed<br /><br />
	<A style='font-size: 14px;' href="config.php">Return to Config</A>		
	</DIV>
	</BODY>
	</HTML>
<?php
	} elseif(isset($_GET['func']) && ($_GET['func']=='list')) {
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
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
		<TITLE>Tickets <?php print $disp_version;?></TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
		<STYLE>
			.table_header	{ color: #FFFFFF; text-align: left; height: 20px; border: 1px solid #000000; background: #707070;}	
			.table_hdr_cell { color: #FFFFFF; width: 20%; font-weight: bold; font-size: 16px; border: 1px solid #000000;}
			.table_row		{ color: #000000; text-align: left; height: 15px; border: 1px solid #000000;}	
			.table_cell 	{ width: 20%; font-size: 14px; border: 1px solid #000000;}			
			.header			{ display: table-cell; color: #000000; width: 5%;}
			.page_heading	{ font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF;}	
			.page_heading_text { font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF; width: 50%; dispay: inline;}
			.button_bar 	{ font-size: 1.2em; text-align: center; display: inline; width: 30%; position: fixed; right:30%; top: 0px;}					
			.buttons 		{ border: 2px outset #FFFFFF; padding: 2px; background-color: #EFEFEF; font-weight: bold; display: inline; cursor: pointer;}	
			.flag 			{ border: 2px outset #707070; background: #CECECE; font-size: 20px; font-weight: bold; display: inline; position: fixed; right:30%; top: 5%;}				
		</STYLE>			
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
	
	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}	
	</SCRIPT>
	</HEAD>
<?php
	// Declare arrays for all resource ids
	$region_ids = array();
	$ticket_ids = array();
	$user_ids = array();
	$unit_ids = array();
	$facility_ids = array();
	// end of array declaration

	// get region ids.
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$region_ids[] = $row['id'];
		}
	// end of region ids

	// get ticket ids.
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$ticket_ids[] = $row['id'];
		}
	// end of ticket ids
		
	// get user ids	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$user_ids[] = $row['id'];
		}
	// end of user ids

	// get responder / unit ids
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$unit_ids[] = $row['id'];
		}	
	// end of responder ids		

	// get facility ids
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";
		$result = mysql_query($query);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$facility_ids[] = $row['id'];
		}
	// end of facility ids
	
?>
	<BODY onLoad = 'ck_frames()'>

	<DIV style='font-size: 20px; font-weight: bold; width:70%;'>
	<DIV class='page_heading'>
	Region Table Allocation List<DIV class='button_bar'>
	<A class='buttons' href="cleanse_regions.php">Cleanse / Sanitize</A>	
	<A class='buttons' href="config.php">Cancel / Return to Config</A></DIV></DIV>	
	<DIV id='flag' class='flag'></DIV>
	<DIV style='width:100%;'>
<?php	
	$counter = 0;
	print "<TABLE style='width: 100%; border: 1px;'>";
	print "<TR class='table_header'>";
	print "<TD class='table_hdr_cell'>Region</TD><TD class='table_hdr_cell'>Users</TD><TD class='table_hdr_cell'>Tickets</TD><TD class='table_hdr_cell'>Responders</TD><TD class='table_hdr_cell'>Facilities</TD></TR>";
	// list all allocations
		foreach ($region_ids as $value) {
			print "<TR>";
			print "<TD class='table_cell'>" . $value . "</TD>";
			print "<TD class='table_cell'>";
			if(count($user_ids) > 0) {			
				foreach ($user_ids as $value2) {
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 4;";
					$result = mysql_query($query);
					$num_entries = mysql_num_rows($result);
					if($num_entries == 1) {
						print "User ID: " . $value2 . "<BR />";
						} elseif($num_entries >=2) {
						$counter++;						
						print "<FONT COLOR='red'>User ID: " . $value2 . "&nbsp;&nbsp;&nbsp;" . "Duplicate Entries</FONT>";
						}
					}
				} else {
				print "No Users Allocated to Regions";
				}					
			print "</TD>";
			print "<TD class='table_cell'>";
			if(count($ticket_ids) > 0) {			
				foreach ($ticket_ids as $value2) {
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 1;";
					$result = mysql_query($query);
					$num_entries = mysql_num_rows($result);
					if($num_entries == 1) {
						print "Ticket ID: " . $value2 . "<br />";
						} elseif($num_entries >=2) {
						$counter++;
						print "<FONT COLOR='red'>Ticket ID: " . $value2 . "&nbsp;&nbsp;&nbsp;" . "Duplicate Entries</FONT>";
						}
					}
				} else {					
				print "No Tickets Allocated to Regions";
				}				
			print "</TD>";	
			print "<TD class='table_cell'>";			
			if(count($unit_ids) > 0) {
				foreach ($unit_ids as $value2) {
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 2;";
					$result = mysql_query($query);
					$num_entries = mysql_num_rows($result);
					if($num_entries == 1) {				
						print "Responder ID: " . $value2 . "<br />";
						} elseif($num_entries >=2) {
						$counter++;						
						print "<FONT COLOR='red'>Responder ID: " . $value2 . "&nbsp;&nbsp;&nbsp;" . "Duplicate Entries</FONT>";
						}
					}
				} else {
				print "No Responders Allocated to Regions";
				}				
			print "</TD>";
			print "<TD class='table_cell'>";
			if(count($facility_ids) > 0) {
				foreach ($facility_ids as $value2) {
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `resource_id` = '{$value2}' AND `group` = '{$value}' AND `type` = 3;";
					$result = mysql_query($query);
					$num_entries = mysql_num_rows($result);
					if($num_entries == 1) {		
						print "Facility ID: " . $value2 . "<br />";
						} elseif($num_entries >=2) {
						$counter++;
						print "<FONT COLOR='red'>Facility ID: " . $value2 . "&nbsp;&nbsp;&nbsp;" . "Duplicate Entries</FONT>";
						}
					}
				} else {
				print "No Facilities Allocated to Regions";
				}
			print "</TD></TR>";
		}
	if($counter >= 1) {
		$output_text = "<FONT COLOR='red'>THERE ARE ERRORS</FONT>";
		} else {
		$output_text = "<FONT COLOR='green'>NO ERRORS</FONT>";
		}
	// end of allocations list			
?>
	</DIV>
	</DIV>
	<SCRIPT>
	$('flag').innerHTML = "<?php print $output_text; ?>";
	</SCRIPT>
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
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
	<TITLE>Tickets <?php print $disp_version;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT>
		function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
	</SCRIPT>	
	</HEAD>
	<BODY onLoad = 'ck_frames()'>
	<DIV style='font-size: 14px; position: fixed; top: 150px; left: 100px;'>
	Are you sure you want to cleanse the Region allocations<br />
	<br />
	If you are SURE, click <A style='font-size: 14px;' href="cleanse_regions.php?func=clean">CLEANSE</A>
	<br />
	<br />
	If NOT then click <A style='font-size: 14px;' href="config.php">CANCEL</A>		
	</DIV>
	</BODY>
	</HTML>
<?php
	}
?>
