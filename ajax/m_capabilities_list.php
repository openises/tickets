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

function capab_list($member) {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	// initiate arrays
	$capab_row = array();

	// search rules
	$query = "SELECT *, `ct`.`id` AS `ctid`, 
		`a`.`id` AS `id`, 	
		`a`.`member_id` AS `mid`,
		`a`.`_on` AS `updated`,
		`ct`.`name` AS `name`,	
		`ct`.`description` AS `description` 
		FROM `$GLOBALS[mysql_prefix]allocations` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ct` ON ( `a`.`skill_id` = ct.id ) 	
		WHERE `a`.`member_id` = {$member} AND `a`.`skill_type` = '2' 
		ORDER BY `a`.`completed`";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$capab_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$capab_row[$i][0] = $row['id'];
			$capab_row[$i][1] = htmlentities($row['name'], ENT_QUOTES);
			$capab_row[$i][2] = htmlentities($row['description'], ENT_QUOTES);
			$capab_row[$i][3] = htmlentities($row['updated'], ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $capab_row;
	}
$output_arr = capab_list($member);

print json_encode($output_arr);
exit();
?>