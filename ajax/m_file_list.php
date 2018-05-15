<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
extract ($_GET);
$internet = ((array_key_exists('internet', $_SESSION)) && ($_SESSION['internet'] == true)) ? true: false;
$istest = FALSE;
$output_arr = array();
$num_rows = 0;

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 
 

function file_list($member) {
	global $istest, $internet, $num_rows;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	// initiate arrays
	$file_row = array();

	// search rules
	
	$query = "SELECT *, `f`.`id` AS `file_id` FROM `$GLOBALS[mysql_prefix]mdb_files` `f` 
		WHERE `f`.`member_id` = {$member} 
		ORDER BY `f`.`name`";	
	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$file_row[0][0] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$filesize = formatBytes($row['filesize'], 2);
			$file_row[$i][0] = $row['file_id'];
			$file_row[$i][1] = htmlentities($row['shortname'], ENT_QUOTES);
			$file_row[$i][2] = htmlentities($row['description'], ENT_QUOTES);
			$file_row[$i][3] = htmlentities($filesize, ENT_QUOTES);
			$file_row[$i][4] = htmlentities($row['_on'], ENT_QUOTES);
			$i++;
			}				// end tickets while ($row = ...)
		}
	return $file_row;
	}
$output_arr = file_list($member);

print json_encode($output_arr);
exit();
?>