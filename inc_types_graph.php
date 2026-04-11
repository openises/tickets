<?php
/*
10/5/2013 complete rewrite; simplification based on using SQL COUNT/GROUP-BY
*/

require_once './incs/functions.inc.php';        //7/28/10
$p1 = sanitize_string($_GET['p1']);
$p2 = sanitize_string($_GET['p2']);
$img_width = isset($_GET['img_width']) ? sanitize_int($_GET['img_width']) : null;

$where = " WHERE `problemstart` > ? AND `problemstart` < ? ";

$query = "SELECT `type`, COUNT(*) AS `nr`
    FROM `{$GLOBALS['mysql_prefix']}ticket` `t`
    LEFT JOIN `{$GLOBALS['mysql_prefix']}in_types` `y` ON `t`.`in_types_id` = `y`.`id`
    {$where}
    AND  `t`.`status` != {$GLOBALS['STATUS_RESERVED']}
    GROUP BY `type`
    ORDER BY `nr` DESC
    LIMIT 5";                // limit is a BAACHART issue

$result = db_query($query, [$p1, $p2]);

if($result->num_rows > 0) {
    $temp = explode ("/", get_variable('pie_charts'));
    $type_diam = (count($temp)> 0 )? intval($temp[1]) : "450";        // 3/21/10
    $width = isset($img_width)? $img_width: $type_diam;    // 3/21/10

    include 'baaChart.php';
    $mygraph = new baaChart($width);
    $incidents_capt = get_text ("incidents");
    $mygraph->setTitle("{$incidents_capt} by Type","");

    while($row = stripslashes_deep($result->fetch_assoc())) {            //
        $row ['type']  = ( @strlen ( $row ['type'] ) > 0 ) ? $row ['type'] : " ? " ;                        // possible null/empty
        $mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE, $row ['nr'] , $row ['type'] );
        }
    $mygraph->setBgColor(0,0,0,1);  //transparent background
    $mygraph->setChartBgColor(0,0,0,1);  //as background
    $mygraph->drawGraph();
        }
else {        // a WTF situation?
    $err_arg = basename(__FILE__) . "/" . __LINE__;
    do_log ($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);        // logs supplied error message
    }
?>