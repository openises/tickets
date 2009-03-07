<?php
/*
10/5/08 sql tidy-up
*/
error_reporting(E_ALL);
require_once('./functions.inc.php');


//snap (__FILE__, __LINE__);
include ("./jpgraph.php");
include ("./jpgraph_line.php");
include ("./jpgraph_error.php");

function getRiemannDistance($lat_from, $long_from, $lat_to, $long_to, $unit='k'){
	switch ($unit): /*** distance unit ***/
	case 'm': 		/*** miles ***/
	   $unit = 3963;
	   break;
	case 'n': 		/*** nautical miles ***/
	   $unit = 3444;
	   break;
	default:    	/*** kilometers ***/
	   $unit = 6371;
	endswitch;

	$degreeRadius = deg2rad(1); /*** 1 degree = 0.017453292519943 radius ***/
	
	$lat_from  *= $degreeRadius; /*** convert longitude and latitude to radians ***/
	$long_from *= $degreeRadius;
	$lat_to    *= $degreeRadius;
	$long_to   *= $degreeRadius;
	
	$dist = sin($lat_from) * sin($lat_to) + cos($lat_from) * cos($lat_to) * cos($long_from - $long_to); /*** apply the Great Circle Distance Formula ***/
	
	return ($unit * acos($dist)); /*** radius of earth * arc cosine ***/
	}

$theDay="";
function TimeCallback($aVal) {				// Callback formatting function for the X-scale to convert timestamps
	global $theDay;
//	dump($aVal);
	return (intval($aVal));
	}

function is_my_null($inchar) {
	return $inchar=="-";
	}

function get_low($in_date) {		// ex:2008-08-26 21:31:09
	$ar1 = split(" ", $in_date);
	$ar2 = split(":", $ar1[1]);		// time
	$ar2[0] = floor ($ar2[0]/4)*4;
	$ar2[0] = str_pad ($ar2[0], 2,"0" , STR_PAD_LEFT);	// pad ldg 0 if needed
	$ar2[1] = $ar2[2] = "00"; 
	$ar1[1] = implode (":", $ar2);	// restore
	$s1 = implode(" ", $ar1);
	return $s1;
	}	
	
$times = array();				// each entry defines the $ith hour of the time range
$alts = array();				// and the max altitude within that hour
$labels = array();				// graph label for nth hour
$start_hr = '';

$p1 = $_GET['p1'];;				// 
$query = "SELECT MIN(packet_date) AS 'min', MAX(packet_date) AS 'max' FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '$p1'";
$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row_tr = mysql_fetch_assoc($result_tr);
//snap (basename( __FILE__), __LINE__);

$modulus = 60*60;
$low = ((strtotime(get_low($row_tr['min'])))/($modulus));	// relative hour in this time range	
$high = round (strtotime($row_tr['max'])/($modulus))+2;		// note padding

$nr_hrs = intval($high - $low);								// interval in hours
for ($i = 0;$i<$nr_hrs; $i++) {								// an entry each hour
	$mins[$i] = $maxs[$i] = "-";							// default NULL
	array_push($labels, date("n/d H:i" , ($low*$modulus) + ($i*$modulus)));
	}

//$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` , `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" .$p1 . "' ORDER BY `packet_date`";	//	6/16/08 
$query = "SELECT  `source`, `altitude` , `packet_date`  FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" .$p1 . "' ORDER BY `packet_date`";	//	10/5/08 
$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//snap (basename( __FILE__) . __LINE__, mysql_affected_rows());

while ($row_tr = stripslashes_deep(mysql_fetch_assoc($result_tr))) {
	$the_hr = intval(floor(strtotime($row_tr['packet_date'])/$modulus) - $low);
	$mins[$the_hr]= is_my_null($mins[$the_hr]) ?  $row_tr['altitude'] : min($mins[$the_hr],$row_tr['altitude']);
	$maxs[$the_hr]= is_my_null($maxs[$the_hr]) ?  $row_tr['altitude'] : max($maxs[$the_hr],$row_tr['altitude']);
	}				// end while ($row_tr ...)

$errdatay = $maxs;						// 2008-08-26 21:31:09 to 2008-09-01 18:25:16

$ydata = $maxs;

$graph = new Graph(600,200,"auto");    						// Create the graph. These two calls are always required
$graph->SetScale("textlin");
$graph->SetMarginColor('#EFEFEF');	// 

$lineplot=new LinePlot($ydata);// Create the linear plot
$lineplot->SetColor("blue");
$lineplot->value-> Show();
$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');	// set alternating colors

$graph->Add($lineplot);										// Add the plot to the graph

$graph->img->SetMargin(45,20,20,80);						// left, right, top, bottom
$graph->title->Set("Track Altitude - " . $p1);
$graph->yaxis->title->Set("Feet");
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetLabelFormatCallback( 'TimeCallback');

$graph->xaxis->SetTickLabels($labels);
$graph->title->SetFont(FF_VERDANA,FS_BOLD);		// FF_ARIAL
$graph->yaxis->title->SetFont(FF_VERDANA);
$graph->xaxis->scale->ticks->Set(300,60);

$lineplot->SetColor("blue");
$lineplot->SetWeight(2);
$lineplot-> mark->SetType(MARK_UTRIANGLE );
$graph->yaxis->SetColor("red");
$graph->yaxis->SetWeight(2);
$graph->SetShadow();
$graph->xaxis->SetTextLabelInterval(4);
$graph->SetTickDensity(TICKD_DENSE);
$graph->Stroke();									// Display the graph
?>
