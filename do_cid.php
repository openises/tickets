<?php
/*
11/5/10 initial release
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
$the_number = "1234567890";
$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$phone}'
	OR `phone_2`= '{$phone}' OR `phone_3`= '{$phone}' OR `phone_4`= '{$phone}'	ORDER BY `updated` ASC";

$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$street = $apartment = $city = $st = "";
while ($row = stripslashes_deep(mysql_fetch_array($result))) {			// use most recent data
	if (!(empty(trim($row['street']))))			{$street =		trim($row['city']);}		// 11/5/10
	if (!(empty(trim($row['apartment']))))		{$apartment =	trim($row['apartment']);}	
	if (!(empty(trim($row['city']))))			{$city =		trim($row['city']);}
	if (!(empty(trim($row['state']))))			{$st =			trim($row['state']);}
	}

if (empty($city)) 	{$city = get_variable('def_city');}	
if (empty($st)) 	{$st = get_variable('def_st');}		

$street = quote_smart($street);
$apartment = quote_smart($apartment);
$city = quote_smart($city);
$st = quote_smart($st);

$query = "DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_RESERVED']} AND `_by` = {tbd};";	//8/10/10
$result = mysql_query($query);

$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
		`id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
		`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` , 
		`severity` , `updated`, `booked_date`, `_by` 
	) VALUES (
		NULL , 0, 0, NULL , '{$street} {$apartment}', {$st} , {$the_number} , NULL , NULL , NULL , 
		NULL , NULL , '', NULL , '', NULL , '{$GLOBALS['STATUS_RESERVED']}', '0', '0', NULL, NULL, $by
	)";
	
$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
@session_start();
session_write_close();
if (!(empty($_SESSION)) {

	}
?>
