<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */
extract ($_GET);
$internet = ((array_key_exists('internet', $_SESSION)) && ($_SESSION['internet'] == true)) ? true: false;
$istest = FALSE;
$output_arr = array();
$num_rows = 0;

function veh_list($member) {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	// initiate arrays
	$veh_row = array();

	// search rules

	$query = "SELECT *, `v`.`id` AS `vid`, 
		`a`.`id` AS `id`, 	
		`a`.`member_id` AS `mid`,
		`v`.`make` AS `make`,
		`v`.`model` AS `model`,
		`v`.`year` AS `year`,
		`v`.`color` AS `color`,	
		`v`.`regno` AS `regno`,		
		`t`.`name` AS `type_name` 
		FROM `$GLOBALS[mysql_prefix]allocations` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]vehicles` `v` ON ( `a`.`skill_id` = v.id ) 
		LEFT JOIN `$GLOBALS[mysql_prefix]vehicle_types` `t` ON ( `v`.`type` = `t`.`id` )		
		WHERE `a`.`member_id` = {$member} AND `a`.`skill_type` = '4' 
		ORDER BY `v`.`regno`";	

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$veh_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$veh_row[$i][0] = $row['id'];
			$veh_row[$i][1] = htmlentities($row['make'], ENT_QUOTES);
			$veh_row[$i][2] = htmlentities($row['model'], ENT_QUOTES);
			$veh_row[$i][3] = htmlentities($row['year'], ENT_QUOTES);
			$veh_row[$i][4] = htmlentities($row['color'], ENT_QUOTES);
			$veh_row[$i][5] = htmlentities($row['regno'], ENT_QUOTES);
			$veh_row[$i][6] = htmlentities($row['type_name'], ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $veh_row;
	}
$output_arr = veh_list($member);

print json_encode($output_arr);
exit();
?>