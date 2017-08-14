<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
if (!(array_key_exists('func', $_GET))) {		//	3/15/11
	$func = 0;
} else {
	extract ($_GET);
	}
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;
$sortby = (!(array_key_exists('sort', $_GET))) ? "tick_id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];
$func = (!(array_key_exists('func', $_GET))) ? 0 : $_GET['func'];
$sort_by_field = (!(array_key_exists('sortbyfield', $_GET))) ? "" : $_GET['sortbyfield'];
$sort_value = (!(array_key_exists('sort_value', $_GET))) ? "" : $_GET['sort_value'];
$my_offset = (!(array_key_exists('my_offset', $_GET))) ? 0 : $_GET['my_offset'];
$istest = FALSE;
$iw_width= "270px";					// map infowindow with
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$output_arr = array();
$num_rows = 0;
$by_severity = array(0, 0, 0);
$sev_color = array('blue','green','red');

function subval_sort($a,$subkey, $dd) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
		}
	if($dd == 1) {	
		asort($b);
		} else {
		arsort($b);
		}
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
		}
	return $c;
	}

function incident_list($sort_by_field='',$sort_value='', $sortby="tick_id", $sortdir="ASC", $func=0, $my_offset=0) {
	global $istest, $iw_width, $disposition, $patient, $incident, $num_rows, $internet, $by_severity, $sev_color;
	$time = microtime(true); // Gets microseconds
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	@session_start();		// 
	
	if (isset($_SESSION['list_type'])) {$func = $_SESSION['list_type'];}		// 12/02/10	 persistance for the tickets list

	$cwi = get_variable('closed_interval');			// closed window interval in hours
	//	output row fields - ID, name(scope), location, lat, lng, description, status, actions, patients, assigned, updated, infowindow text, tip string, scheduled flag
	
	// initiate arrays
	$ticket_row = array();
	
	//	User Groups
	
	$al_groups = $_SESSION['user_groups'];
	
	if(array_key_exists('viewed_groups', $_SESSION)) {		//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}
		
//	Count number of actions on Ticket

	$acts_ary = $pats_ary = array();				// 6/2/10
	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]action` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$acts_ary[$row['ticket_id']] = $row['the_count'];
		}

//	Count number of patients on Ticket

	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]patient` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$pats_ary[$row['ticket_id']] = $row['the_count'];
		}	
		
	// search rules

	$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
																				//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($_SESSION['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $_SESSION['ticket_per_page']))	{
		if ($_GET['offset']) {
			$limit = "LIMIT $_GET[offset],$_SESSION[ticket_per_page]";
			}
		else {
			$limit = "LIMIT 0,$_SESSION[ticket_per_page]";
			}
		}
	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$time_back = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60) - ($cwi*3600));
	$sort_by_severity = ($func == 0)? "`severity` DESC ": "";

	if (!(array_key_exists('func', $_GET))) {		//	3/15/11
		$func = 0;
	} else {
		extract ($_GET);
		}
	if ((array_key_exists('func', $_GET)) && ($_GET['func'] == 10)) {		//	3/15/11
		$func = 10;
		}
		
	//	Set regions applicable for user
	
	if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
		$where2 = " AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";
		} else {		
		if(!isset($curr_viewed)) {			//	6/10/11
			$x=0;	
			$where2 = "AND (";
			foreach($al_groups as $grp) {
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			} else {
			$x=0;	
			$where2 = "AND (";	
			foreach($curr_viewed as $grp) {
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			}
		$where2 .= " AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";	
		}

	$interval = get_variable('hide_booked');
	switch($func) {		
		case 0: 
			$where = "WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_OPEN']}' OR 
				(`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND (`$GLOBALS[mysql_prefix]ticket`.`booked_date` <= (NOW() + INTERVAL " . $interval . " HOUR) OR `$GLOBALS[mysql_prefix]ticket`.`booked_date` > NOW())) OR 
				(`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_CLOSED']}' AND `$GLOBALS[mysql_prefix]ticket`.`problemend` >= '{$time_back}'))
				{$where2} 
				AND `$GLOBALS[mysql_prefix]allocates`.`al_status` = 1 OR `$GLOBALS[mysql_prefix]allocates`.`al_status` = 2 OR (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `$GLOBALS[mysql_prefix]ticket`.`booked_date` <= (NOW() + INTERVAL " . $interval . " HOUR) AND `$GLOBALS[mysql_prefix]allocates`.`al_status` = 2) OR (`$GLOBALS[mysql_prefix]allocates`.`al_status` = 0 AND `$GLOBALS[mysql_prefix]allocates`.`al_as_of` >= '{$time_back}')";	//	11/29/10, 4/18/11, 4/18/11
			break;
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
			$the_start = get_start($func);		// mysql timestamp format 
			$the_end = get_end($func);
			$where = " WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_CLOSED']}' AND `$GLOBALS[mysql_prefix]ticket`.`problemend` BETWEEN '{$the_start}' AND '{$the_end}') {$where2} AND `$GLOBALS[mysql_prefix]allocates`.`al_status` = 0";		//	4/18/11, 4/18/11
			break;				
		case 10:
			$where = "WHERE (`$GLOBALS[mysql_prefix]ticket`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `$GLOBALS[mysql_prefix]ticket`.`booked_date` >= (NOW() + INTERVAL " . $interval . " HOUR)) {$where2} ";	//	11/29/10, 4/18/11, 4/18/11
			break;			
		default: print "error - error - error - error " . __LINE__;
		}				// end switch($func)
		
	if ($sort_by_field && $sort_value) {					//sort by field?
		$query = "SELECT *,problemstart AS problemstart,problemend AS problemend,
			`date` AS `date`,updated AS updated, in_types.type AS `type`, in_types.id AS `t_id` 
			FROM `$GLOBALS[mysql_prefix]allocates`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]allocates`.`resource_id`=`$GLOBALS[mysql_prefix]ticket`.`id` 			
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.`in_types_id`=`$GLOBALS[mysql_prefix]in_types`.`id` 
			WHERE `ticket`.`{$sort_by_field}` LIKE '%{$sort_value}%' $restrict_ticket AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 ORDER BY $order_by";
		}
	else {					// 2/2/09, 8/12/09, updated 4/18/11 to support regional operation
		$query = "SELECT *,problemstart AS problemstart,
			`problemend` AS `problemend`,
			`booked_date` AS `booked_date`,	
			`date` AS `date`, 
			`$GLOBALS[mysql_prefix]ticket`.`scope` AS scope, 
			`$GLOBALS[mysql_prefix]ticket`.`street` AS ticket_street, 
			`$GLOBALS[mysql_prefix]ticket`.`state` AS ticket_city, 
			`$GLOBALS[mysql_prefix]ticket`.`city` AS ticket_state,
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
			`$GLOBALS[mysql_prefix]in_types`.`type` AS `type`, 
			`$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, 
			`$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`, 
			`$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
			(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`  
				AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
				AS `units_assigned`			
			FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 
				ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` 
				ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 
				ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id`
			$where $restrict_ticket 
			GROUP BY tick_id ORDER BY `status` DESC, {$sort_by_severity} 
			LIMIT 1000 OFFSET {$my_offset}";		// 2/2/09, 10/28/09, 2/21/10
		}
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$num_rows = mysql_num_rows($result);
//	Major While
	if($num_rows == 0) {
		$ticket_row[0][99] = 0;
		} else {
		$temp  = (string) ( round((microtime(true) - $time), 3));
		$i = 1;
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			$tick_gps = get_allocates(1, $row['tick_id']);	//	6/10/11
			$grp_names = "Groups Assigned: ";	//	6/10/11
			$y=0;	//	6/10/11
			foreach($tick_gps as $value) {	//	6/10/11
				$counter = (count($tick_gps) > ($y+1)) ? ", " : "";
				$grp_names .= get_groupname($value);
				$grp_names .= $counter;
				$y++;
				}
			$by_severity[$row['severity']] ++;
			if (($row['units_assigned']==0) && ($row['status']==$GLOBALS['STATUS_OPEN'])) {					// 4/11/10
				$do_blink = 1;
				} else {
				$do_blink = 0;
				}	
			$tip =  addslashes ( "{$grp_names}/{$row['contact']}/{$row['ticket_street']}/{$row['ticket_city']}/{$row['ticket_state']}/{$row['phone']}/{$row['scope']}");		// tooltip string - 10/28/2012
			$sched_flag = (($row['status'] == $GLOBALS['STATUS_SCHEDULED']) && ($func != 10)) ? "*" : "";		
			$type = shorten($row['type'], 18);
			$severity = $row['severity'];
			$status = $row['status'];
			$the_id = $row['tick_id'];		// 11/27/09
			$radius = $row['radius'];
			$updated = format_sb_date_2($row['updated']);
			$the_scope = replace_quotes(shorten($row['scope'], 40));
			$address_street=replace_quotes(shorten($row['ticket_street'] . " " . $row['ticket_city'], 20));
			$lat = $row['lat'];
			$lng = $row['lng'];
			$num_assigned = $row['units_assigned'];
			$num_actions = array_key_exists ($the_id , $acts_ary)? $acts_ary[$the_id]: 0;		// 6/2/10
			$num_patients = array_key_exists ($the_id , $pats_ary)? $pats_ary[$the_id]: 0;
			if ($status== $GLOBALS['STATUS_CLOSED']) {
				$strike = "<strike>"; $strikend = "</strike>";
				}
			else { $strike = $strikend = "";}
			
			if (intval($row['radius']) > 0) {
				$color= (substr($row['color'], 0, 1)=="#")? $row['color']: "blue";		// black default
				}				// end if (intval($row['radius']) 
			$color = isset($color) ? $color : "blue";
			if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
			if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
				if (strlen($row['tick_descr']) > get_variable('abbreviate_description')) {
					$row['tick_descr'] = substr($row['tick_descr'],0,get_variable('abbreviate_description')).'...';
					}
				}
			if (get_variable('abbreviate_affected')) {
				if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
					$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
					}
				}

			$A = array_key_exists ($the_id , $acts_ary)? $acts_ary[$the_id]: "&nbsp;";		// 6/2/10
			$P = array_key_exists ($the_id , $pats_ary)? $pats_ary[$the_id]: "&nbsp;";
			$pats_count = (isset($pats_ary[$the_id])) ? $pats_ary[$the_id] : "&nbsp;";
			$acts_count = (isset($acts_ary[$the_id])) ? $acts_ary[$the_id] : "&nbsp;";	
			
			$use_quick = (((integer)$func == 0) || ((integer)$func == 10)) ? FALSE : TRUE ;	//	11/29/10
			$locale = get_variable('locale');	// 08/03/09			
			if (my_is_float($row['lat'])) {		// 6/21/10
				$temp_array[0] = $row['lat'];
				$temp_array[1] = $row['lng'];
				$temp_array[2] = addslashes(shorten($row['scope'], 48));
				$temp_array[3] = addslashes(shorten(str_replace($eols, " ", $row['tick_descr']), 256));
				$street = empty($row['ticket_street'])? "" : replace_quotes($row['ticket_street']) . "<BR/>" . replace_quotes($row['ticket_city']) . " " . replace_quotes($row['ticket_state']) ;
				$todisp = (is_guest()|| is_unit())? "": "<A id='disp_" . $the_id . "' CLASS='plain' style='float: none; color: #000000;' HREF='{$_SESSION['routesfile']}?ticket_id={$the_id}' onMouseOver=\"do_hover(this.id);\" onMouseOut=\"do_plain(this.id);\">Dispatch</A>";	// 7/27/10
			
				$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
				$theTabs = "<div class='infowin'><BR />";
				$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
				$theTabs .= '<div class="tabArea">';
				$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
				$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab2\', 2, null, null);">Details</span>';
				$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row['lat'] . ',' . $row['lng'] . ');">Location</span>';
				$theTabs .= '</div>';
				$theTabs .= '<div class="contentwrapper">';
			
				$tab_1 = "<TABLE width='280px' style='height: 260px;'><TR><TD><TABLE width='98%'>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . replace_quotes(shorten($row['scope'], 48))  . "$strikend</B></TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>As of:</TD><TD ALIGN='left'>" . format_date_2(($row['updated'])) . "</TD></TR>";
				if (is_date($row['booked_date'])){
					$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left' >Booked Date:</TD><TD ALIGN='left'>" . format_date_2($row['booked_date']) . "</TD></TR>";	//10/27/09, 3/15/11
					}
				$tab_1 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Reported by:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['contact'], 32)) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Phone:</TD><TD ALIGN='left'>" . format_phone($row['phone']) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Addr:</TD><TD ALIGN='left'>$address_street</TD></TR>";
		
				$elapsed = get_elapsed_time ($row);
				$tab_1 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Status:</TD><TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
				$tab_1 .= (empty($row['fac_name']))? "" : "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Receiving Facility:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['fac_name'], 30))  . "</TD></TR>";	//3/27/10, 3/15/11
				$utm = get_variable('UTM');
				if ($utm==1) {
					$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
					$tab_1 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>UTM grid:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";
					}
				$tab_1 .= "</TABLE></TD></TR>";
				$tab_1 .= "<TR><TD COLSPAN=2 ALIGN='center'>";
				$tab_1 .= "</TABLE>";			// 11/6/08	

				$tab_2 = "<TABLE width='280px' style='height: 280px;' ><TR><TD><TABLE width='98%'>";
				$tab_2 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>Description:</TD><TD ALIGN='left'>" . replace_quotes(shorten(str_replace($eols, " ", $row['tick_descr']), 48)) . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>911 contact:</TD><TD ALIGN='left'>" . shorten($row['nine_one_one'], 48) . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD class='td_label' style='font-size: 80%;' ALIGN='left'>{$disposition}:</TD><TD ALIGN='left'>" . shorten(replace_quotes($row['comments']), 48) . "</TD></TR></TABLE></TD></TR>";		// 8/13/09, 3/15/11
				$tab_2 .= "<TR><TD COLSPAN=2 ALIGN='left'><DIV style='max-height: 200px; overflow-y: scroll;'>" . show_assigns(0, $the_id) . "</DIV></TD></TR>";

				$tab_2 .= "</TABLE>";			// 11/6/08			
				
				$tab_3 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
				$tab_3 .= "<TABLE width='98%'>";

				switch($locale) { 
					case "0":
					$tab_3 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "1":
					$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "2":
					$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
					$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					default:
					print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
					}
				$tab_3 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lat</TD><TD class='td_data' style='font-size: 80%;'>" . $row['lat'] . "</TD></TR>";
				$tab_3 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lng</TD><TD class='td_data' style='font-size: 80%;'>" . $row['lng'] . "</TD></TR>";
				$tab_3 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
				$tab_3 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
				$tab_3 .= "</TABLE></TD</TR></TABLE>";
				}
				
			$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
			$theTabs .= "<div class='content' id='content2' style = 'display: none;'>" . $tab_2 . "</div>";
			$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_3 . "</div>";
			$theTabs .= "</div>";
			$theTabs .= "</div>";
			$theTabs .= "</div>";
			
			$ticket_row[$i][0] = $the_scope;
			$ticket_row[$i][1] = $address_street;
			$ticket_row[$i][2] = $lat;
			$ticket_row[$i][3] = $lng;
			$ticket_row[$i][4] = $type;
			$ticket_row[$i][5] = $severity;
			$ticket_row[$i][6] = $status;
			$ticket_row[$i][7] = $num_actions;
			$ticket_row[$i][8] = $num_patients;
			$ticket_row[$i][9] = $num_assigned;
			$ticket_row[$i][10] = $updated;
			$ticket_row[$i][11] = $tip;
			$ticket_row[$i][12] = $sched_flag;
			$ticket_row[$i][13] = $radius;
			$ticket_row[$i][14] = $sev_color[$severity];
			$ticket_row[$i][15] = $i;
			$ticket_row[$i][16] = $acts_count;
			$ticket_row[$i][17] = $pats_count;
			$ticket_row[$i][18] = intval($row['units_assigned']);
			$ticket_row[$i][19] = $do_blink;
			$ticket_row[$i][20] = $the_id;		
			$ticket_row[$i][21] = $theTabs;	
			$i++;
			}				// end tickets while ($row = ...)
		return $ticket_row;
		}
	}
$output_arr = incident_list($sort_by_field, $sort_value, $sortby, $sortdir, $func, $my_offset);
if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}

switch($sortby) {
	case 'id':
		$sortval = 20;
		break;
	case 'scope':
		$sortval = 0;
		break;
	case 'street':
		$sortval = 1;
		break;
	case 'type':
		$sortval = 4;
		break;
	case 'a':
		$sortval = 7;
		break;
	case 'p':
		$sortval = 8;
		break;
	case 'u':
		$sortval = 9;
		break;
	case 'updated':
		$sortval = 10;
		break;
	default:
		$sortval = 20;
	}
if($num_rows > 0) {
	if((isset($output_arr[0][99])) && ($output_arr[0][99] == 0)) {
		print json_encode($output_arr);
		} else {
		$the_arr = subval_sort($output_arr, $sortval, $dd);
		$the_output = array();
		$z=1;
		foreach($the_arr as $val) {
			$the_output[$z] = $val;
			$z++;
			}
		$the_output[0][22] = $by_severity[0];		
		$the_output[0][23] = $by_severity[1];		
		$the_output[0][24] = $by_severity[2];
		print json_encode($the_output);
		}
	} else {
	$output_arr[0][0] = 0;
	$output_arr[0][22] = 0;		
	$output_arr[0][23] = 0;		
	$output_arr[0][24] = 0;
	print json_encode($output_arr);
	}
exit();
?>