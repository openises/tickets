<?php
error_reporting(E_ALL);
/*
12/16/09 initial release
*/
require_once('./incs/functions.inc.php');
$the_phone = $_GET['frm_phone'];
$the_width = 600;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
	<HEAD><TITLE>Tickets - Call History</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
   <STYLE TYPE="text/css">
		body 	{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 	{border-collapse: collapse; }
		td		{FONT-WEIGHT: normal; FONT-SIZE: 9px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }

    	</STYLE>

<SCRIPT>
</SCRIPT>
</HEAD>
<BODY>
<?php
//$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE phone = {$the_phone} ORDER BY `problemstart`";		// 
	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(booked_date) AS booked_date,
		UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated, `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`, `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`, `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`, `rf`.`name` AS `rec_fac_name`, `rf`.`lat` AS `rf_lat`, `rf`.`lng` AS `rf_lng`, `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`, `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`phone`='{$the_phone}' ORDER BY `$GLOBALS[mysql_prefix]ticket`.`problemstart` ASC";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

// dump ($query);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$count = (mysql_affected_rows()==0)? "": " <I>(" .mysql_affected_rows() . ")</I>";
?>
<TABLE ALIGN='center' ID = 'outer'>
<TR><TH><BR /><BR />Calls for <?php print format_phone ($the_phone) . $count; ?></TH></TR>
<TR><TD>
<?php
if (mysql_affected_rows()==0) {
?>
</TD></TR>
<TR CLASS='even'><TH ALIGN='center'>None</TD></TR>

<?php
	}		// end if (mysql_affected_rows()==0)
else {

	while ($row_ticket = stripslashes_deep(mysql_fetch_array($result))) {
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		}
	} 
?>
</TH></TR>
<TR CLASS='odd'><TD ALIGN = 'center'><BR />
	<INPUT TYPE='button' VALUE= 'Finished' onClick = 'window.close()'>
</TD></TR></TABLE>
</BODY>
</HTML>
