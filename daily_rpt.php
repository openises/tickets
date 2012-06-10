<?php
/*
10/2/10 initial release
12/1/10 get_text disposition added
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Daily Tickets Report</TITLE>
		<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8"/>
		<META HTTP-EQUIV="Expires" 				CONTENT="0"/>
		<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
		<META HTTP-EQUIV="Script-date" 			CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<STYLE>
		tr.even { background-color: #DEE3E7;}
		tr.odd 	{ background-color: #EFEFEF;}
		th 		{ FONT-WEIGHT: bold; FONT-SIZE: 11px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.imp	{ FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		td		{ white-space:nowrap; FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
	</STYLE>
	<BODY>
	<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>

<?php
	function do_print($row_in) {
		global $today, $today_ref, $line_ctr, $units_str, $severities;
			if (empty($today)) {
				$today_ref = date("z", $row_in['problemstart']);
				$today = substr( format_date($row_in['problemstart']), 0, 5);
				}
			else {
				if (!($today_ref == (date("z", $row_in['problemstart'])))) {				// date change?
					$today_ref = date("z", $row_in['problemstart']);
					$today = substr( format_date($row_in['problemstart']), 0, 5);
					}
				}			

		$def_city = get_variable('def_city');
		$def_st = get_variable('def_st');
		
		print "<TR CLASS= ''>\n";
		print "<TD>{$today}</TD>\n";							//		Date - 

		$problemstart = format_date($row_in['problemstart']);
		$problemstart_sh = short_ts($problemstart);
		print "<TD onMouseover=\"Tip('{$problemstart}');\" onmouseout='UnTip();'>{$problemstart_sh}</TD>\n";						//		start
		
		$problemend = format_date($row_in['problemend']);
		$problemend_sh = short_ts($problemend);
		print "<TD onMouseover=\"Tip('{$problemend}');\" onmouseout='UnTip();'>{$problemend_sh}</TD>\n";						//		end

		$elapsed = my_date_diff($row_in['problemstart'], $row_in['problemend']);
		print "<TD>{$elapsed}</TD>\n";							//		Ending time

		print "<TD ALIGN='center'>{$severities[$row_in['severity']]}</TD>\n";

		$scope = $row_in['tick_scope'];
		$scope_sh = shorten($row_in['tick_scope'], 20);
		print "<TD onMouseover=\"Tip('{$scope}');\" onmouseout='UnTip();'>{$scope_sh}</TD>\n";					//		Call type

		$comment = $row_in['comments'];
		$short_comment = shorten ( $row_in['comments'] , 50);
		print "<TD onMouseover=\"Tip('{$comment}');\" onMouseout='UnTip();'>{$short_comment}</TD>\n";			//		Comments/Disposition

		$facility = $row_in['facy_name'];
		$facility_sh = shorten($row_in['facy_name'], 16);
		print "<TD onMouseover=\"Tip('{$facility}');\" onmouseout='UnTip();'>{$facility_sh}</TD>\n";			//		Facility

		$city = ($row_in['tick_city']==$def_city)? 	"": ", {$row_in['tick_city']}" ;
		$st = ($row_in['tick_state']==$def_st)? 	"": ", {$row_in['tick_state']}";
		$addr = "{$row_in['tick_street']}{$city}{$st}";
		$addr_sh = shorten($row_in['tick_street'] . $city . $st, 20);

		print "<TD onMouseover=\"Tip('{$addr}');\" onMouseout='UnTip();'>{$addr_sh}</TD>\n";					//		Street addr
		print "<TD>{$units_str}</TD>\n";						//		Units responding
		print "</TR>\n\n";
		$line_ctr++;
		}		// end function do print()

	function do_stats($in_row) {
		global $deltas, $counts;
		$deltas[$in_row['severity']]+= ($in_row['problemend'] - $in_row['problemstart']);	
		$deltas[3] 					+= ($in_row['problemend'] - $in_row['problemstart']);
		$counts[$in_row['severity']]++;
		$counts[3]++;	
		}		// end function do stats()
		
	$tick_array = array(0);
	$deltas = array(0, 0, 0, 0);		// normal, medium, high, total
	$counts = array(0, 0, 0, 0);		// 
	$severities = array ("", "M", "H");	// severity symbols

	$query = "SELECT *, UNIX_TIMESTAMP(problemstart) AS `problemstart`,
		UNIX_TIMESTAMP(problemend) AS `problemend`,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
		`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
		`t`.`id` AS `tick_id`,
		`t`.`description` AS `tick_descr`,
		`t`.`status` AS `tick_status`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,			
		`r`.`id` AS `unit_id`,
		`r`.`name` AS `unit_name` ,
		`r`.`type` AS `unit_type` ,
		`f`.`name` AS `facy_name` ,
		`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`assigns`.`facility_id` = `f`.`id`)
		ORDER BY `problemstart` ASC";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	
	$lines = mysql_affected_rows();
	print "\n<SCRIPT>\n\tvar lines = {$lines};\n</SCRIPT>\n";		// hand to JS - 5/23/09
	print "<TABLE BORDER=0 ALIGN='center' cellspacing = 1 CELLPADDING = 4  ID='call_board' STYLE='display:block'>";
	print "<TR>
			<TD><B>Date</B></TD>
			<TD><B>Opened</B></TD>
			<TD><B>Closed</B></TD>
			<TD><B>Elapsed</B></TD>
			<TD><B>Severity</B></TD>
			<TD><B>Call type</B></TD>
			<TD><B>Comments/<?php print $disposition;?></B></TD>
			<TD><B>Facility</B></TD>
			<TD><B>Address</B></TD>
			<TD><B>Unit responding</B></TD>
			</TR>";

	if ($lines == 0) {												// empty?			
//		print "<TR><TH ><BR /><BR /><BR />No Current Incidents<BR /></TH><TH></TH></TR>\n";
		}
	else {
		$units_str = "";
		$i=0;		
		$today = $today_ref = "";
		$buffer = "";
		$sep = ", ";

		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {					// major while ()
			array_push ($tick_array, $row['tick_id']);									// stack them up

			if (empty($buffer)) {											// first time
				$buffer = $row;
				$units_str = $row['unit_name'];
				}
			else {		// not first time
				if ($row['tick_id'] == $buffer['tick_id']) {
					$units_str .= $sep . $row['unit_name'] ;		// no change, collect unit names
//					$buffer = $row;
					}
				else {
					do_print($buffer);		// print from buffer
					do_stats($buffer);
					$buffer = $row;
					$units_str = $row['unit_name'];
					}
				}		// end if/else
			}		// end while(
		do_print($buffer);		// print from buffer
		do_stats($buffer);
		}		// end else{}
		
	$tick_array2 = array_unique ($tick_array );		// delete dupes
	$tick_array3 = array_values ($tick_array2 );	// compress result
	$sep = $tick_str = "";
	for ($i=0; $i< count($tick_array3); $i++ ) {
		$tick_str .= $sep . $tick_array3[$i];
		$sep = ",";	
		}
//	dump($tick_str);
	$query = "SELECT *, 
		UNIX_TIMESTAMP(problemstart) AS `problemstart`,
		UNIX_TIMESTAMP(problemend) AS `problemend`,
		`u`.`user` AS `theuser`,
		NULL AS `unit_name`,
		`t`.`scope` AS `tick_scope`,
		`t`.`id` AS `tick_id`,
		`t`.`description` AS `tick_descr`,
		`t`.`status` AS `tick_status`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,			
		`f`.`name` AS `facy_name` 
		FROM `$GLOBALS[mysql_prefix]ticket`			 `t`
		LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`t`.`_by` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`t`.`facility` = `f`.`id`)
		WHERE `t`.`id` NOT IN ({$tick_str})
		AND `t`.`status` <> '{$GLOBALS['STATUS_RESERVED']}'
		ORDER BY `problemstart` ASC";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	print "<TR><TD COLSPAN=99 ALIGN='center'><B>Not dispatched</B></TD></TR>";
	$units_str = "";
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// final while () 
		$deltas[$row['severity']]	+= ($row['problemend'] - $row['problemstart']);	// stats
		$deltas[3] 					+= ($row['problemend'] - $row['problemstart']);
		$counts[$row['severity']]++;
		$counts[3]++;	
		do_print($row);
		}
	
	print "</TABLE>";
	print "<BR /><BR /><SPAN STYLE='margin-left:100px'>Mean incident close times by severity:&nbsp;&nbsp;&nbsp;";
	for ($i = 0; $i<3; $i++) {					// each severity level
		$mean = round($deltas[$i] / $counts[$i]);
		print "<B>" . ucfirst(get_severity($i)) ."</B> ({$counts[$i]}): ". my_date_diff(0, $mean) . ",&nbsp;&nbsp;&nbsp;&nbsp;";
		}
	$mean = round($deltas[3] / $counts[3]);		// overall
	print "<B>Overall</B>  ({$counts[3]}): ". my_date_diff(0, $mean);
	print "</SPAN><BR /><BR /><BR />";
?>
