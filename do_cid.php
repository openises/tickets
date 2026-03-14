<?php
/*
11/5/10 initial release
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
$the_number = "1234567890";
$query  = "SELECT  * FROM `{$GLOBALS['mysql_prefix']}constituents` WHERE `phone`= ?
	OR `phone_2`= ? OR `phone_3`= ? OR `phone_4`= ?	ORDER BY `updated` ASC";

$result = db_query($query, [$phone, $phone, $phone, $phone]);
$street = $apartment = $city = $st = "";
while ($row = stripslashes_deep($result->fetch_array())) {			// use most recent data
	if (!(empty(trim($row['street']))))			{$street =		trim($row['city']);}		// 11/5/10
	if (!(empty(trim($row['apartment']))))		{$apartment =	trim($row['apartment']);}	
	if (!(empty(trim($row['city']))))			{$city =		trim($row['city']);}
	if (!(empty(trim($row['state']))))			{$st =			trim($row['state']);}
	}

if (empty($city)) 	{$city = get_variable('def_city');}	
if (empty($st)) 	{$st = get_variable('def_st');}		

$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `status` = {$GLOBALS['STATUS_RESERVED']} AND `_by` = {tbd};";	//8/10/10
$result = db_query($query);

$query_insert  = "INSERT INTO `{$GLOBALS['mysql_prefix']}ticket` (
		`id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
		`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` ,
		`severity` , `updated`, `booked_date`, `_by`
	) VALUES (
		NULL , 0, 0, NULL , ?, ? , ? , NULL , NULL , NULL ,
		NULL , NULL , '', NULL , '', NULL , ?, '0', '0', NULL, NULL, ?
	)";

$result_insert	= db_query($query_insert, [trim($street) . ' ' . trim($apartment), trim($city), $the_number, $GLOBALS['STATUS_RESERVED'], $by]);
@session_start();
session_write_close();
if (!(empty($_SESSION)) [

	]
?>
