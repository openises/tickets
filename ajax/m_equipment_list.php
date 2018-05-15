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

function equip_list($member) {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	// initiate arrays
	$equip_row = array();

	// search rules
	
	$query = "SELECT *, `et`.`id` AS `etid`, 
		`a`.`id` AS `id`, 	
		`a`.`member_id` AS `mid`,
		`a`.`completed` AS `issued`,
		`et`.`equipment_name` AS `name`,	
		`et`.`description` AS `description`, 
		`et`.`spec` AS `spec`,
		`et`.`condition` AS `condition` 			
		FROM `$GLOBALS[mysql_prefix]allocations` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]equipment_types` `et` ON ( `a`.`skill_id` = et.id ) 	
		WHERE `a`.`member_id` = {$member} AND `a`.`skill_type` = '3' 
		ORDER BY `a`.`completed`";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$equip_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$equip_row[$i][0] = $row['id'];
			$equip_row[$i][1] = htmlentities($row['name'], ENT_QUOTES);
			$equip_row[$i][2] = htmlentities($row['description'], ENT_QUOTES);
			$equip_row[$i][3] = htmlentities($row['condition'], ENT_QUOTES);
			$equip_row[$i][4] = htmlentities($row['issued'], ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $equip_row;
	}
$output_arr = equip_list($member);

print json_encode($output_arr);
exit();
?>