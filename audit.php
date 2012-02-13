<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets Table Audit</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<SCRIPT>
</SCRIPT>
</HEAD>
<BODY>
<?php
error_reporting(E_ALL);				// 9/13/08
require_once('./incs/functions.inc.php');

function audit ($basic, $extra, $support, $field ) {
	$our_array = array();
	$query = "SELECT * FROM `{$support}`";	
	$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);// 
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$our_array[$row['id']]=TRUE;
		}

	$query = "SELECT * FROM `{$basic}` ";	// 8/11/08
	$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);// in_types_id
	$header = FALSE;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		if (!(array_key_exists($row[$field], $our_array))) {
			if (!($header)){
				print "<br /><h3>{$extra} $GLOBALS[mysql_prefix]{$basic}</h3>";
				$header = TRUE;
				}			
			print "Table '{$support}' missing index {$row[$field]}.  Called for in '{$basic}' record id: {$row['id']} <br />\n ";
			}		// end while()

		}
	print ($header)? "": "<BR /><h3>Table {$basic}{$extra} OK for '{$field}' </h3><BR />";
	}		// end function

//	audit ($basic, "open, $support, $field, $caption ) 
	audit ("$GLOBALS[mysql_prefix]ticket", "Open", "$GLOBALS[mysql_prefix]in_types", "in_types_id");
	audit ("$GLOBALS[mysql_prefix]responder", "", "$GLOBALS[mysql_prefix]unit_types", "type");
	audit ("$GLOBALS[mysql_prefix]responder", "", "$GLOBALS[mysql_prefix]un_status", "un_status_id");

	audit ("$GLOBALS[mysql_prefix]facilities", "", "$GLOBALS[mysql_prefix]fac_types", "type");
	audit ("$GLOBALS[mysql_prefix]facilities", "", "$GLOBALS[mysql_prefix]fac_status", "status_id");
?>