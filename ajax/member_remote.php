<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();

$ret = "";
$ret .="<table ID='large' cellspacing='0'><thead><tr><th>Member ID</th><th>Name</th><th>Team ID</th><th>Address</th><th>Postcode</th><th>Join Date</th><th>Membership Due</th><th>Updated</th></tr></thead>";
$query = "SELECT *, `updated` AS `updated`,
	`t`.`id` AS `type_id`, 
	`s`.`id` AS `status_id`, 
	`m`.`id` AS `member_id`, 
	`m`.`fullname` AS `fullname`, 
	`m`.`surname` AS `surname`, 
	`m`.`firstname` AS `firstname`, 
	`m`.`street` AS `street`, 
	`m`.`postcode` AS `postcode`, 
	`m`.`joindate` AS `joindate`, 
	`m`.`duedate` AS `duedate`, 
	`m`.`crb` AS `crb`, `m`.`teamno` AS `teamno`,
	`s`.`description` AS `stat_descr`,  
	`m`.`description` AS `unit_descr` 
	FROM `$GLOBALS[mysql_prefix]member` `m` 
	LEFT JOIN `$GLOBALS[mysql_prefix]member_types` `t` ON ( `m`.`membertype` = t.id )	
	LEFT JOIN `$GLOBALS[mysql_prefix]member_status` `s` ON ( `m`.`mem_status_id` = s.id ) 	
	ORDER BY `m`.`id` ASC ";

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$ret .= "<tbody>";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$ret .= "<tr>";
	$ret .= "<td>" . $row['member_id'] . "</td>";
	$ret .= "<td>" . $row['fullname'] . "</td>";
	$ret .= "<td>" . $row['teamno'] . "</td>";
	$ret .= "<td>" . $row['street'] . "</td>";
	$ret .= "<td>" . $row['postcode'] . "</td>";
	$ret .= ((strtotime($row['joindate']) == NULL) || (date("Y", strtotime($row['joindate'])) == '1970') || (date("Y", strtotime($row['joindate'])) == '0000')) ? "<td>TBA</td>" : "<td>" . date("d-m-Y", strtotime($row['joindate'])) . "</td>";
	$ret .= ((strtotime($row['duedate']) == NULL) || (date("Y", strtotime($row['duedate'])) == '1970') || (date("Y", strtotime($row['duedate'])) == '0000')) ? "<td>TBA</td>" : "<td>" . date("d-m-Y", strtotime($row['duedate'])) . "</td>";	
	$ret .= ((strtotime($row['updated']) == NULL) || (date("Y", strtotime($row['updated'])) == '1970') || (date("Y", strtotime($row['updated'])) == '0000')) ? "<td>TBA</td>" : "<td>" . date("d-m-Y", strtotime($row['updated'])) . "</td>";
	$ret .= "</tr>";
	}
$ret .= "</tbody></table>";
print $ret;
