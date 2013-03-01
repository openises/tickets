<?php
/*
6/10/11 new release
*/
error_reporting(E_ALL);

@session_start();
require_once('./incs/functions.inc.php');		//7/28/10

function ringf() {
	$coords = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		print $row['id'];
		print " ";
		print $row['ring_fence'];
		print "<br />";
		if (($row['ring_fence'] != NULL) && ($row['ring_fence'] > 0))	{
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]lines` WHERE `id` = {$row['ring_fence']}";
			$result2 = mysql_query($query2)or do_error($query2, mysql_error(), basename(__FILE__), __LINE__);
			$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
			extract ($row2);			
			$points = explode (";", $line_data);
			for ($i = 0; $i < count($points); $i++) {
				$coords[] = explode (",", $points[$i]);	
				}
			}
		}
	dump($coords);
}
// @session_start(); 		// 1/23/10
// $_SESSION[$f_n] = $v_n;
ringf();

?>