<?php
/*
10/5/2013 complete rewrite; simplification based on using SQL COUNT/GROUP-BY
*/

require_once('./incs/functions.inc.php');		//7/28/10
extract($_GET);

$where = " WHERE `problemstart` > '{$p1}' AND `problemstart` < '{$p2}' ";
$query = "SELECT `city`, COUNT(*) AS `nr`
	FROM `$GLOBALS[mysql_prefix]ticket` `t`
	{$where}
	AND  `t`.`status` != {$GLOBALS['STATUS_RESERVED']}
	GROUP BY `city`
	ORDER BY `nr` DESC
	LIMIT 5";				// limit is a BAACHART issue

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

if(mysql_num_rows($result) > 0) {
	$temp = explode ("/", get_variable('pie_charts'));
	$type_diam = (count($temp)> 0 )? intval($temp[2]) : "450";		// 
	$width = isset($img_width)? $img_width: $type_diam;	// 3/21/10

	include('baaChart.php');
	$mygraph = new baaChart($width);
	$incidents_capt = get_text ("incidents");
	$mygraph->setTitle("{$incidents_capt} by City","");
	
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		$row ['city']  = ( @strlen( @trim ( $row ['city'] ) )> 0 ) ? $row ['city'] : " ? " ;	// possible null/empty
		$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE, $row ['nr'] , $row ['city'] );
		}
	$mygraph->setBgColor(0,0,0,1);  //transparent background
	$mygraph->setChartBgColor(0,0,0,1);  //as background
	$mygraph->drawGraph();
	}
else {		// a WTF situation?
	$err_arg = basename(__FILE__) . "/" . __LINE__;
	do_log ($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// log error message	
	}
?>	