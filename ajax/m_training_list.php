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

function tra_list($member) {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	// initiate arrays
	$tra_row = array();

	// search rules
	$query = "SELECT *, `tp`.`id` AS `tpid`, 
		`a`.`id` AS `id`, 	
		`a`.`member_id` AS `mid`,
		`tp`.`package_name` AS `package_name`,	
		`tp`.`description` AS `description`,
		`a`.`completed` AS `completed`,	
		`a`.`refresh_due` AS `refresh_due`		
		FROM `$GLOBALS[mysql_prefix]allocations` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` `tp` ON ( `a`.`skill_id` = tp.id ) 	
		WHERE `a`.`member_id` = {$member} AND `a`.`skill_type` = '1' 
		ORDER BY `package_name";	

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$tra_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$completed = $row['completed'];
			$completed = do_datestring(strtotime($completed));
			$refresh = $row['refresh_due'];
			$refresh = do_datestring(strtotime($refresh));
			$tra_row[$i][0] = $row['id'];
			$tra_row[$i][1] = htmlentities($row['package_name'], ENT_QUOTES);
			$tra_row[$i][2] = htmlentities($row['description'], ENT_QUOTES);
			$tra_row[$i][3] = htmlentities($completed, ENT_QUOTES);
			$tra_row[$i][4] = htmlentities($refresh, ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $tra_row;
	}
$output_arr = tra_list($member);
print json_encode($output_arr);
exit();
?>