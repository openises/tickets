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

function cloth_list($member) {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	// initiate arrays
	$cloth_row = array();

	// search rules
	
	$query = "SELECT *, `ct`.`id` AS `ctid`, 
		`a`.`id` AS `id`, 	
		`a`.`member_id` AS `mid`,
		`a`.`completed` AS `issued`,
		`ct`.`clothing_item` AS `name`,	
		`ct`.`description` AS `description`, 
		`ct`.`size` AS `size` 	
		FROM `$GLOBALS[mysql_prefix]allocations` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]clothing_types` `ct` ON ( `a`.`skill_id` = ct.id ) 	
		WHERE `a`.`member_id` = {$member} AND `a`.`skill_type` = '5' 
		ORDER BY `a`.`completed`";		
	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$cloth_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$completed = $row['issued'];
			$completed = do_datestring(strtotime($completed));
			$cloth_row[$i][0] = $row['id'];
			$cloth_row[$i][1] = htmlentities($row['name'], ENT_QUOTES);
			$cloth_row[$i][2] = htmlentities($row['description'], ENT_QUOTES);
			$cloth_row[$i][3] = htmlentities($row['size'], ENT_QUOTES);
			$cloth_row[$i][4] = htmlentities($completed, ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $cloth_row;
	}
$output_arr = cloth_list($member);

print json_encode($output_arr);
exit();
?>