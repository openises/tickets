<?php
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) { 
	define("PROTOCOL", "https://");
	} else { 
	define("PROTOCOL", "http://"); 
	}
define("WEBHOST_URL", PROTOCOL.$_SERVER['HTTP_HOST']);
define("THISDIR_URL", PROTOCOL.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
$url = WEBHOST_URL;
$thisurl = THISDIR_URL;
$image = $thisurl . "/t.png";
$logo = $thisurl . "/logo.png";
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
function output_xml_field($col_name,$value) {
	$value = str_replace('&', '&amp;',	$value);
    $value = str_replace('<', '&lt;',	$value);
    $value = str_replace('>', '&gt;',	$value);
    $value = str_replace('"', '&quot;',	$value);
    return '<'.$col_name.'>'.$value.'</'.$col_name.'>';
	}

$sort_by_severity = "`severity` DESC, ";

$query = "SELECT *,problemstart AS problemstart,
	`problemend` AS `problemend`,
	`booked_date` AS `booked_date`,	
	`date` AS `date`, 
	`$GLOBALS[mysql_prefix]ticket`.`street` AS ticket_street, 
	`$GLOBALS[mysql_prefix]ticket`.`state` AS ticket_city, 
	`$GLOBALS[mysql_prefix]ticket`.`city` AS ticket_state,
	`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,
	`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
	`$GLOBALS[mysql_prefix]in_types`.`type` AS `type`, 
	`$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
	`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, 
	`$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
	`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`, 
	`$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
	`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
	`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
	(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`  
		AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
		AS `units_assigned`			
	FROM `$GLOBALS[mysql_prefix]ticket` 
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
		ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
	LEFT JOIN `$GLOBALS[mysql_prefix]in_types` 
		ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
	LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 
		ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id`
	WHERE `status` = 1
	GROUP BY tick_id ORDER BY `status` DESC, {$sort_by_severity} `$GLOBALS[mysql_prefix]ticket`.`id` ASC
	LIMIT 1000";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);


$i = 1;
header("Content-type: text/xml");
$XML = "<?xml version=\"1.0\"?>";
$XML .= "<rss version=\"2.0\" xmlns:georss=\"http://www.georss.org/georss\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">";
$XML .= "\t<channel>";
$XML .= "\t\t<title>Incidents</title>\n\n";
$XML .= "\t\t<description><![CDATA[Incidents being run by " . get_variable('host') . "]]></description>\n";	
$XML .= "\t\t<link>" . $url . "</link>\n";		
$XML .= "\t\t<pubDate>" . $now . "</pubDate>\n";
$XML .= "\t\t<lastBuildDate>" . $now . "</lastBuildDate>";
$XML .= "\t\t<language>en-us</language>";
$XML .= "\t\t<managingEditor>" . get_variable('email_reply_to') . "</managingEditor>";
$XML .= "\t\t<webMaster>" . get_variable('email_reply_to') . "</webMaster>";
$XML .= "\t\t<image>";
$XML .= "\t\t\t<url>" . $logo . "</url>";
$XML .= "\t\t\t<title><![CDATA[" . get_variable('host') . "]]></title>";
$XML .= "\t\t\t<link>" . $thisdir . "/</link>";
$XML .= "\t\t</image>";

while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$XML .= "\t\t<item>\n";
	$XML .= "\t\t\t<title>" . $row['scope'] . "</title>\n";
	$XML .= "\t\t\t<description><![CDATA[Description: " . $row['tick_descr'] . "<BR />\n";
	$XML .= "Reported: " . format_date_2(strtotime($row['updated'])) . "<BR />\n";
	$XML .= "Units Assigned: " . $row['units_assigned'] . "<BR />\n";
	$XML .= "Location: " . $row['ticket_street'] . "<BR />\n";
	$XML .= "Latitude: " . $row['lat'] . ", Longitude: " . $row['lng'] . "<BR />\n";
	$XML .= "<img src=\"" . $image . "\" height='40'>]]></description>\n";
	$XML .= "\t\t\t<category><![CDATA[" . $row['type'] . "]]></category>\n";
	$XML .= "\t\t\t<link>" . $url . "</link>\n";	
	$XML .= "\t\t<georss:point>" . $row['lat'] . " " . $row['lng'] . "</georss:point>\n";
	$XML .= "\t\t</item>\n";
	$i++;
	}
$XML .= "\t</channel>\n";
$XML .= "</rss>\n";
echo $XML;
?>
