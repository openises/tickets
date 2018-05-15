<?php
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
//$the_session = $_GET['session'];
// if(!(secure_page($the_session))) {
	// exit();
	// } else {
	
$thestring = "";
$today = time();
$query = "SELECT *, `refresh_due` AS refresh_due FROM `$GLOBALS[mysql_prefix]allocations`
					LEFT JOIN `$GLOBALS[mysql_prefix]member` ON `$GLOBALS[mysql_prefix]allocations`.`member_id`=`$GLOBALS[mysql_prefix]member`.`id`
					LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` ON `$GLOBALS[mysql_prefix]allocations`.`skill_id`=`$GLOBALS[mysql_prefix]training_packages`.`id`					
					WHERE `skill_type` = '1' AND (`refresh_due` BETWEEN (NOW()) AND (NOW() + INTERVAL 6 MONTH))";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$thestring .= "<TABLE>";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
	$thestring .= "<TR>";
	$thestring .= "<TD>" . $row['field1'] . " " . $row['field2'] . "</TD>";
	$thestring .= "<TD>" . $row['package_name'] . "</TD>";
	$numDays = abs($today - strtotime($row['refresh_due']))/60/60/24;
	if($numDays >= 500) {
		$theFlag="style='font-weight: bold; background-color: red; color: #000000;'";
		}
	$thestring .= "<TD " . $theFlag . ">" . date('d/m/Y', strtotime($row['refresh_due'])) . "</TD>";
	$thestring .= "</TR>";
	}

$thestring .= "</TABLE>";

print json_encode($thestring);
//}