<?php
/*
10/5/2013 complete rewrite; simplification based on using SQL COUNT/GROUP-BY
*/

require_once('./incs/functions.inc.php');		//7/28/10
extract($_GET);

$where = " WHERE `problemstart` > '{$p1}' AND `problemstart` < '{$p2}' ";

$query = "SELECT `type`, COUNT(*) AS `nr`
	FROM `$GLOBALS[mysql_prefix]ticket` `t`
	LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON `t`.`in_types_id` = `y`.`id`
	{$where}
	AND  `t`.`status` != {$GLOBALS['STATUS_RESERVED']}
	GROUP BY `type`
	ORDER BY `nr` DESC
	LIMIT 5";				// limit is a BAACHART issue

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

if(mysql_num_rows($result) > 0) {	
	$temp = explode ("/", get_variable('pie_charts'));
	$type_diam = (count($temp)> 0 )? intval($temp[1]) : "450";		// 3/21/10
	$width = isset($img_width)? $img_width: $type_diam;	// 3/21/10

	include('baaChart.php');
	$mygraph = new baaChart($width);
	$incidents_capt = get_text ("incidents");
	$mygraph->setTitle("{$incidents_capt} by Type","");
	
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		$row ['type']  = ( @strlen ( $row ['type'] ) > 0 ) ? $row ['type'] : " ? " ;						// possible null/empty
		$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE, $row ['nr'] , $row ['type'] );
		}
	$mygraph->setBgColor(0,0,0,1);  //transparent background
	$mygraph->setChartBgColor(0,0,0,1);  //as background
	$mygraph->drawGraph();
		}
else {		// a WTF situation?
	$err_arg = basename(__FILE__) . "/" . __LINE__;
	do_log ($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// logs supplied error message	
	}
?>	