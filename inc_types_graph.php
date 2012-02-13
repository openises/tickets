<?php
/*
3/21/10 user-spec for pie diameter added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/


@session_start();
require_once($_SESSION['fip']);		//7/28/10
extract($_GET);

$severities = array();
$temp = explode ("/", get_variable('pie_charts'));
$type_diam = (count($temp)> 0 )? $temp[1] : "450";		// 3/21/10

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types`  ORDER BY `group` ASC,`sort` ASC, `type` ASC";		// array of incident types text
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
while($row = stripslashes_deep(mysql_fetch_array($result))) {
	$inc_types_text[$row['id']] = $row['type'];
	}				// end while($row...)

//$where = " WHERE `when` > '2007-12-01 11:50:59' AND `when` < '2009-03-15 11:50:59' ";
$where = " WHERE `when` > '" . $p1 . "' AND `when` < '" . $p2 . "' ";
//																	//	now get log entries and associated incident type, per current value
$query = "SELECT *, UNIX_TIMESTAMP(`when`) AS `when`, t.id AS `tick_id`,t.scope AS `tick_name`, t.severity AS `tick_severity` FROM `$GLOBALS[mysql_prefix]log`
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON (log.ticket_id = t.id)
	". $where . " AND `code` = '" . $GLOBALS['LOG_INCIDENT_OPEN'] ."'
	ORDER BY `when` ASC		
	";

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
$inc_types = array();
while($row = stripslashes_deep(mysql_fetch_array($result), MYSQL_ASSOC)){			// build assoc arrays of types and counts
	if (array_key_exists($row['in_types_id'], $inc_types)) {
		$inc_types[$row['in_types_id']]++;
		}
	else {
		$inc_types[$row['in_types_id']] = 1;
		}
	}		// end while($row =...)

include('baaChart.php');
$width = isset($img_width)? $img_width: $type_diam;	// 3/21/10
$mygraph = new baaChart($width);
//$mygraph->setTitle($from,$to);
$mygraph->setTitle('Incidents by Type','');
//dump ($inc_types);
foreach($inc_types as $key => $val) {
	if ((strlen($key)>0)) {
		$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE,$val, $inc_types_text[$key]);
		}
    }		// end foreach()
$mygraph->setBgColor(0,0,0,1);  //transparent background
$mygraph->setChartBgColor(0,0,0,1);  //as background
$mygraph->drawGraph();

/*
include('baaChart.php');
	$mygraph = new baaChart(1000);
	$mygraph->setTitle('Regional Sales','Jan - Jun 2002');
	$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE,"25,30,35,40,30,35","Hello");
	$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE,"65,70,80,90,75,48","Goodbye");
	$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE,"12,18,25,20,22,30","West");
	$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE,"50,60,75,80,60,75","East");
	$mygraph->addDataSeries('P',PIE_CHART_PCENT + PIE_LEGEND_VALUE,"30,45,50,55,52,60","Europe");
	$mygraph->drawGraph();
*/	
?>	