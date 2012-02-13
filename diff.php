<?php
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10

function yy_date_diff($d1, $d2){		// end, start timestamp integers in, returns string - 5/13/10
	dump(__LINE__);
	dump($d1);
	dump($d2);
	if ($d1 < $d2){						// check higher timestamp and switch if neccessary
		$temp = $d2;
		$d2 = $d1;
		$d1 = $temp;
		}
	else {
		$temp = $d1; //temp can be used for day count if required
		}

	if (!(intval($d2)>0)) {$d2 = time();}

	dump(__LINE__);
	dump($d1);
	dump($d2);
	$d1 = date_parse(date("Y-m-d H:i:s",$d1));
	$d2 = date_parse(date("Y-m-d H:i:s",$d2));
	if ($d1['second'] >= $d2['second']){	//seconds
		$diff['second'] = $d1['second'] - $d2['second'];
		}
	else {
		$d1['minute']--;
		$diff['second'] = 60-$d2['second']+$d1['second'];
		}
	if ($d1['minute'] >= $d2['minute']){	//minutes
		$diff['minute'] = $d1['minute'] - $d2['minute'];
		}
	else {
		$d1['hour']--;
		$diff['minute'] = 60-$d2['minute']+$d1['minute'];
		}
	if ($d1['hour'] >= $d2['hour']){	//hours
		$diff['hour'] = $d1['hour'] - $d2['hour'];
		}
	else {
		$d1['day']--;
		$diff['hour'] = 24-$d2['hour']+$d1['hour'];
		}
	if ($d1['day'] >= $d2['day']){	//days
		$diff['day'] = $d1['day'] - $d2['day'];
		}
	else {
		$d1['month']--;
		$diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
		}
	if ($d1['month'] >= $d2['month']){	//months
		$diff['month'] = $d1['month'] - $d2['month'];
		}
	else {
		$d1['year']--;
		$diff['month'] = 12-$d2['month']+$d1['month'];
		}
	$diff['year'] = $d1['year'] - $d2['year'];	//years

	$out_str = ""; 
	$plural = ($diff['year'] == 1)? "": "s";								// needless elegance
	$out_str .= empty($diff['year'])? "" : "{$diff['year']} yr{$plural}, ";

	$plural = ($diff['month'] == 1)? "": "s";
	$out_str .= empty($diff['month'])? "" : "{$diff['month']} mo{$plural}, ";

	$plural = ($diff['day'] == 1)? "": "s";
	$out_str .= empty($diff['day'])? "" : "{$diff['day']} day{$plural}, ";

	$plural = ($diff['hour'] == 1)? "": "s";
	$out_str .= empty($diff['hour'])? "" : "{$diff['hour']} hr{$plural}, ";

	$plural = ($diff['minute'] == 1)? "": "s";
	$out_str .= empty($diff['minute'])? "" : "{$diff['minute']} min{$plural}";
	dump(__LINE__);

	return  $out_str;
	}
$start_date = 1273610940;
$end_date = time();
//$date_diff_array = my_date_diff($born_date, time());
//$date_diff_array = my_date_diff($born_date, $born_date);
//print_r($date_diff_array);
//print "{$date_diff_array['year']} years, {$date_diff_array['month']} months, {$date_diff_array['day']} days, {$date_diff_array['hour']} hours, {$date_diff_array['minute']} minutes";

print yy_date_diff($end_date, 0);
print yy_date_diff($end_date, time());
?>