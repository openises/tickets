<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
require_once('../incs/status_cats.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$iw_width= "300px";					// map infowindow with
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$ret_arr = array();
$sortby = (!(array_key_exists('sort', $_GET))) ? "tick_id" : $_GET['sort'];
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : $_GET['dir'];
$screen = (!(array_key_exists('screen', $_GET))) ? "situation" : $_GET['screen'];

function subval_sort($a, $subkey, $dd) {
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

$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]warnings`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
$locations = mysql_affected_rows()>0 ?  mysql_affected_rows(): "<I>none</I>";
unset($result);

$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` ORDER BY `title` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_locations = mysql_affected_rows();
$i=1;				// counter
// =============================================================================
$utc = gmdate ("U");
if($num_locations == 0) {
	$ret_arr[0][0] = 0;
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// ==========  major while() for Location ==========
		$the_loc_type = $GLOBALS['LOC_TYPES'][$row['loc_type']];
		$theTypeName = $GLOBALS['LOC_TYPES_NAMES'][$row['loc_type']];
		$the_bg_color = 	$GLOBALS['LOC_TYPES_BG'][$the_loc_type];
		$the_text_color = 	$GLOBALS['LOC_TYPES_TEXT'][$the_loc_type];
		$the_on_click = (my_is_float($row['lat']))? " onClick = myclick({$i}); " : " onClick = myclick_nm({$row['id']}); ";
		$got_point = FALSE;

		if(is_guest() || $screen == "fullscreen") {
			$toedit = $tomail = $toview = $toroute = "";
			} else {
			$toedit = "<SPAN id='editbut' CLASS='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='doEdit(" . $row['id'] . ");'>Edit</SPAN>" ;
			$toview = "<SPAN id='viewbut' CLASS='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='doView(" . $row['id'] . ");'>View</SPAN>";
			}	

	// name
		$name = $row['title'];	// 10/8/09		 4/28/11
		$display_name = $name = shorten(htmlentities($row['title'], ENT_QUOTES), 20);	
		$display_street = $street = shorten(htmlentities($row['street'], ENT_QUOTES), 40);			
		
	// address
		$address_street=replace_quotes($row['street']) . " " . replace_quotes($row['city']);
		$street = empty($row['street'])? "" : replace_quotes($row['street']) . " " . replace_quotes($row['city']) . " " . replace_quotes($row['state']) ;
		$street = shorten($street, 60);
	// as of
		$updated = $row['_on'];

	// tab 1

		if (my_is_float($row['lat'])) {
			$theTabs = "<div class='infowin'>";
			$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
			$theTabs .= '<div class="tabArea">';
			$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
			$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row['lat'] . ',' . $row['lng'] . ');">Location</span>';
			$theTabs .= '</div>';
			$theTabs .= '<div class="contentwrapper">';	
			$tab_1 = "<TABLE width='280px' style='width: 280px; height: 97%; background-color: red; border-radius: 0em .5em .5em 0em; padding: .5em;'>";
			$tab_1 .= "<TR><TD><TABLE width='98%'>";	
			$tab_1 .= "<TR CLASS='even'><TD CLASS='td_data text text_center' COLSPAN=2 style='background-color: red; color: white;'><B>Location Warning</B></TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD CLASS='td_data text text_center' COLSPAN=2><B>" . addslashes(shorten($display_name, 48)) . "</B></TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD CLASS='td_label text text_left'>Description:&nbsp;</TD><TD CLASS='td_data text text_left'>" . addslashes(shorten(str_replace($eols, " ", $row['description']), 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD CLASS='td_label text text_left'>As of:&nbsp;</TD><TD CLASS='td_data text text_left'>" . format_date(strtotime($row['_on'])) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even' style='height: 30px;'><TD COLSPAN=99 ALIGN='center'><TABLE style='width: 100%;'>";
			$tab_1 .= "<TR CLASS='even' style='height: 25px;'><TD ALIGN='center'>&nbsp;</TD></TR>";			
			$tab_1 .= "<TR CLASS='even' style='height: 25px;'><TD CLASS='td_data text' ALIGN='center'>" . $toedit . " " . $toview . "</TD></TR>";
			$tab_1 .= "</TABLE></TD></TR>";
			$tab_1 .= "</TABLE></TD></TR></TABLE>";
			$tab_3 = "<TABLE style='width: 280px; height: 97%; background-color: red;'><TR><TD>";
			$tab_3 .= "<TABLE width='100%'>";
			$locale = get_variable('locale');	// 08/03/09
			switch($locale) { 
				case "0":
				$tab_3 .= "<TR CLASS='odd'><TD class='td_label text text_left' ALIGN='left'>USNG:</TD><TD CLASS='td_data text text_left' ALIGN='left'>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "1":
				$tab_3 .= "<TR CLASS='odd'><TD class='td_label text text_left' ALIGN='left'>OSGB:</TD><TD CLASS='td_data text text_left' ALIGN='left'>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "2":
				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
				$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label text text_left' ALIGN='left'>UTM:</TD><TD CLASS='td_data text text_left' ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				default:
				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
				}
			$tab_3 .= "<TR CLASS='even'><TD class='td_label text text_left'>Lat</TD><TD class='td_data text text_left'>" . $row['lat'] . "</TD></TR>";
			$tab_3 .= "<TR CLASS='odd'><TD class='td_label text text_left'>Lng</TD><TD class='td_data text text_left'>" . $row['lng'] . "</TD></TR>";
			$tab_3 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
			$tab_3 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
			$tab_3 .= "</TABLE></TD</TR></TABLE>";			
			$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
			$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_3 . "</div>";
			$theTabs .= "</div>";
			$theTabs .= "</div>";
			$theTabs .= "</div>";
			}
		$ret_arr[$i][0] = $name;
		$ret_arr[$i][1] = $display_name;
		$ret_arr[$i][2] = $row['lat'];
		$ret_arr[$i][3] = $row['lng'];
		$ret_arr[$i][4] = $street;	
		$ret_arr[$i][5] = $the_bg_color;
		$ret_arr[$i][6] = $the_text_color;
		$ret_arr[$i][7] = $updated;
		$ret_arr[$i][8] = $row['id'];
		$ret_arr[$i][9] = $theTabs;
		$ret_arr[$i][10] = $the_loc_type;
		$ret_arr[$i][11] = $theTypeName;
		$i++;				// zero-based
		}				// end  ==========  while() for Location ==========
	}
	
if($sortdir == "ASC") {
	$dd = 1;
	} else {
	$dd = 0;
	}

switch($sortby) {
	case 'id':
		$sortval = 8;
		break;
	case 'title':
		$sortval = 0;
		break;
	case 'type':
		$sortval = 11;
		break;
	case 'street':
		$sortval = 4;
		break;
	case 'asof':
		$sortval = 7;
		break;
	default:
		$sortval = 8;
	}

if((isset($ret_arr[0])) && ($ret_arr[0][0] == 0)) {
	$the_output = $ret_arr;
	} else {
	$the_arr = subval_sort($ret_arr, $sortval, $dd);
	$the_output = array();
	$z=1;
	foreach($the_arr as $val) {
		$the_output[$z] = $val;
		$z++;
		}
	}

$the_output[0][10] = $num_locations;	
print json_encode($the_output);
exit();