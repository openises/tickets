<?php
require_once('./incs/functions.inc.php');
//extract($_GET);
$call = "N1OES-9";
$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` , `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" . $call . "' ORDER BY `packet_date`";	//	6/16/08 
$times = array();				// each entry defines the $ith hour of the time range
$alts = array();				// and the max altitude within that hour
$start_hr = '';

for ($i=0; $i< mysql_affected_rows(); $i++) {
	$times[$i] = NULL;		
	$alts[$i] =  NULL;		
	}
	
$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$sidebar_line = "<TABLE border=0>\n";
if (mysql_affected_rows()> 1 ) {
		while ($row_tr = stripslashes_deep(mysql_fetch_array($result_tr))) {
			if (empty($start_hr)) {
				$start_hr = floor(strtotime($row_tr['packet_date'])/(60*60));		// relative hour
				}
			$n = floor(strtotime($row_tr['packet_date'])/(60*60)) - $start_hr ;		// compute array index
			$times[$n] = TRUE;
			if (isnull($alts[$n]) ||$row_tr['altitude']>$alts[$n]) { $alts[$n]=$row_tr['altitude'] ;}
			$i++;
			}		// end while ($row_tr...)
		dump($alts);
		}
	else {
		print __LINE__ ;
	}
?>