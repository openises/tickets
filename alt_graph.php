<?php
require_once('./functions.inc.php');
extract($_GET);
$p1 = "N1OES-9";					// KB3FRX N1OES-9
include ("./incs/jpgraph.php");
include ("./incs/jpgraph_line.php");
include ("./incs/jpgraph_error.php");

$mo="";
$da="";

function TimeCallback($aVal) {				// Callback formatting function for the X-scale to convert timestamps
	GLOBAL $start_hr, $mo, $da;
	$mytime = $start_hr + (60*60*$aVal);
	$mo="";
	$da="";
	$hr="";
//	if (date('i',$mytime)!='00') {return"";}
	switch (date('G',$mytime)) {
		case 0:
		case 6:
		case 12:
		case 18:
		    return date('H:i',$mytime);
		    break;
		default:
		    return "";
		}
//	return  (Date('jG', $aVal)=='00')? Date('n/j', $aVal): Date('H:i', $aVal);				// to hour and minutes.
//	return  (Date('jG', $aVal)=='00')? Date('n/j', $aVal): "";				// to hour and minutes.
	}

	$times = array();				// each entry defines the $ith hour of the time range
	for ($i=0; $i< 24*10; $i++) {	// 10 days
		$times[$i]=0;
		}
	$alts = array();				// min and max altitudes within that hour
	for ($i=0; $i< (24*10*2); $i++) {				// low, high
		$alts[$i] =  "";		
		}
	$start_hr = '';

	$query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,`status` , `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '" .$p1 . "' ORDER BY `packet_date` LIMIT 200";	//	6/16/08 
	$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
	while ($row_tr = stripslashes_deep(mysql_fetch_array($result_tr))) {
		if($row_tr['altitude']>0){
			if (empty($start_hr)) {
				$start_hr = floor(strtotime($row_tr['packet_date'])/(60*60));		// relative hour
				}
			$n = floor(strtotime($row_tr['packet_date'])/(60*60)) - $start_hr ;		// compute array index
			if(empty($alts[2*$n])) {
				$alts[(2*$n)+1] = $alts[(2*$n)] = intval($row_tr['altitude']) ;		// populate both min and max
				}
			else {
				$alts[2*$n] 	= intval(min ( $alts[2*$n] , 		$row_tr['altitude'] ));	// low
				$alts[(2*$n)+1] = intval(max ( $alts[(2*$n)+1] , 	$row_tr['altitude'] ));	// high
				}
			$times[$n] = TRUE;
			}
		}
$errdatay = $alts;
//$errdatay = array(11,9,2,4,19,26,13,19,7,12,11,9,2,4,19,26,13,19,7,12,'x','x', 22, 11, "", "", "", "", "", ""  );
//$errdatay = array(34, 643, 'x', 'x', 667, 696, 698, 699, 710, 741, 750, 755, 796, 809, 810, 812, 816, 629, 839, 629, 839, 829, 839, 878);
//dump ($errdatay);		

// Create the graph. These two calls are always required
$graph = new Graph(960,200,"auto");    
$graph->SetScale("textlin");

$graph->img->SetMargin(40,30,20,60);		// L, R, T, B margins
$graph->SetShadow();

// Create the linear plot
$errplot=new ErrorLinePlot($errdatay);
$errplot->SetColor("red");
$errplot->SetWeight(2);
$errplot->SetCenter();
$errplot->line->SetWeight(2);
$errplot->line->SetColor("blue");

// Add the plot to the graph
$graph->Add($errplot);

$graph->title->Set($p1 . " Track Altitude ");
//$graph->xaxis->title->Set("feet");
$graph->yaxis->title->Set("feet");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
//
$graph->xaxis->SetLabelFormatCallback('TimeCallback');	// Setup the callback and adjust the angle of the labels
$graph->xaxis->SetLabelAngle(90);

//

$datax = $gDateLocale->GetShortMonth();
$graph->xaxis->SetTickLabels($datax);
//$graph->xaxis->scale->ticks->Set(300,60);				// Set the labels every 5min (i.e. 300seconds) and minor ticks every minute


// Display the graph
$graph->Stroke();
?> 