<?php

error_reporting(E_ALL);
/*
8/20/09 created facilities.php from units.php
10/6/09 Added links button
10/8/09 Index in list and on marker changed to part of name after /
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs
10/29/09 Removed period after index in sidebar
11/11/09 Fixed sidebar display when not using map location
11/11/09 Made map location mandatory for form input, added 'top' anchor.
11/27/09 Changed edit 'Cancel' action
3/24/10 removed 'top' function calls
7/5/10 Added Location fields and phone number fields as for Incident. Geocoding of address and reverse geocoding of map click implemented.
7/7/10 mysql_fetch_array -> mysql_fetch_assoc
7/7/10 removed refresh, add mail button, list_xxx function name changed
7/22/10 NULL handling revised, miscjs, google reverse geocode parse added
7/27/10 unit-level limitation applied
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
7/28/10 Added default icon for tickets entered in no-maps operation
8/13/10 map.setUIToDefault();
8/25/10 light top-frame button
11/29/10 locale 2 handling added
12/6/10 internet test relocated
2/17/11 Changed wrong log events from log_unit_status to LOG_FACILITY_ADD or LOG_FACILITY_CHANGE as appropriate
3/15/11 Added reference to stylesheet.php for revisable day night colors.
3/19/11 changed index length to 6 chars
4/27/11 icon logic added, top/bottom nav added
5/4/11 get_new_colors() added
7/1/11 permissions corrected
8/1/11 state length increased to 4 chars
6/10/11 Added Groups and Boundaries
6/18/12 'points' boolean to 'got_points'
9/5/12 GMaps V3 key handling added
1/4/2013 V3 polylines and polygon, setMap conversions made
5/30/13 Implement catch for when there are no allocated regions for current user. 
6/4/2013 beds information added for all operations
8/28/13 Added mailgroup capability - email to mailgroup when facility set as originating or receiving facility. Also about status field.
*/

@session_start();	
session_write_close();
/* if (!($_SESSION['internet'])) {				// 12/6/10
	header("Location: facilities_nm.php");
	} */

require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));

$key_field_size = 30;
$st_size = (get_variable("locale") ==0)?  2: 4;	

$FacID = (isset($_GET['id'])) ? $_GET['id'] : 0;	

// Phase 2 security cleanup: replaced extract with explicit variable assignments
$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : 0);
$mapmode = $_GET['mapmode'] ?? $_POST['mapmode'] ?? null;
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

function fac_format_date($date){							/* 1/20/2013 */ 
	if (get_variable('locale')==1)	{return date("j/n/y H:i",$date);}					// 08/27/10 - Revised to show UK format for locale = 1	
	else 							{return date(get_variable("date_format"),$date);}	// return date(get_variable("date_format"),strtotime($date));
	}				// end function fac format date
function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}

$usng = get_text('USNG');
$osgb = get_text('OSGB');

$f_types = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}fac_types` ORDER BY `id`";		// types in use
$result = db_query($query);
while ($row = stripslashes_deep($result->fetch_assoc())) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);

$icons = $GLOBALS['fac_icons'];
$sm_icons = $GLOBALS['sm_fac_icons'];	//	3/15/11

$f_types = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}fac_types` ORDER BY `id`";		// types in use
$result = db_query($query);
while ($row = stripslashes_deep($result->fetch_assoc())) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);

function get_icon_legend (){			// returns legend string
	global $f_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `{$GLOBALS['mysql_prefix']}facilities` ORDER BY `type`";
	$result = db_query($query);
	$print = "";											// output string
	while ($row = stripslashes_deep($result->fetch_assoc())) {
		$temp = $f_types[$row['type']];
		$print .= "\t\t<SPAN class='legend' style='height: 3em; text-align: center; vertical-align: middle; float: none;'> ". $temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0 PADDING='10'>&nbsp;&nbsp;&nbsp;</SPAN>";
		}
	return $print;
	}			// end function get_icon_legend ()	
	
function get_mailgroup_name($id) {	//	8/28/13
	if($id == 0) {
		return "";
		}
	$id = sanitize_int($id);
	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mailgroup` WHERE `id` = ?";
	$result = db_query($query, [$id]);
	$row = stripslashes_deep($result->fetch_assoc());
	$the_ret = $row['name'];
	return $the_ret;
	}
	
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Facilities Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT><!-- 10/23/12-->
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
<?php
	if ($_SESSION['internet'] || $_SESSION['good_internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
			if($https) {
?>
				<script src="https://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php
				} else {
?>
				<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php				
				}
			}
		}
?>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
	var map;		// note global
	var layercontrol;
	
	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();
	parent.upper.light_butt('facy');		// light the button - 8/25/10

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
	var map;								// map object

	function checkAll() {	//	9/10/13
		var theField = document.res_add_Form.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = true ;
			}
		}

	function uncheckAll() {	//	9/10/13
		var theField = document.res_add_Form.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = false ;
			}
		}
		
	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && $) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && $) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}

	var starting = false;

	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;
		document.routes_Form.submit();
		}

</SCRIPT>
<?php
	function finished ($caption) {
		print "</HEAD><BODY><!--" . __LINE__ . " -->";
		require_once('./incs/links.inc.php');	// 10/6/09
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM>\n<A NAME='bottom' />\n</BODY></HTML>";
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT >\n";
		$print .="\t\tvar calls = new Array();\n";
		$id = sanitize_int($id);
		$query	= "SELECT `id`, `callsign` FROM `{$GLOBALS['mysql_prefix']}facilities` where `id` != ?";
		$result	= db_query($query, [$id]);
		while($row = stripslashes_deep($result->fetch_assoc())) {
			if (!empty($row['callsign'])) {
				$print .="\t\tcalls.push('" .$row['callsign'] . "');\n";
				}
			}				// end while();
		$print .= "</SCRIPT>\n";
		return $print;
		}		// end function do calls()

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Facility - checkbox
		$frm_id = sanitize_int($_POST['frm_id']);
		$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}facilities` WHERE `id`= ?";
		$result = db_query($query, [$frm_id]);
		$caption = "<B>Facility <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? NULL : trim($_POST['frm_lat']) ;
			$the_lng = empty($_POST['frm_lng'])? NULL : trim($_POST['frm_lng']) ;
			$frm_opening_hours = base64_encode(serialize($_POST['frm_opening_hours']));
			$curr_groups = $_POST['frm_exist_groups']; 	//	4/14/11
			$groups = isset($_POST['frm_group']) ? ", " . implode(',', $_POST['frm_group']) . "," : $_POST['frm_exist_groups'];	//	3/28/12 - fixes error when accessed from view ticket screen..
			$fac_id = sanitize_int($_POST['frm_id']);
			$fac_stat = sanitize_int($_POST['frm_status_id']);
			$by = $_SESSION['user_id'];					// 6/4/2013
			$query = "UPDATE `{$GLOBALS['mysql_prefix']}facilities` SET
				`name`= ?,
				`street`= ?,
				`city`= ?,
				`state`= ?,
				`handle`= ?,
				`icon_str`= ?,
				`boundary`= ?,
				`description`= ?,
				`beds_a`= ?,
				`beds_o`= ?,
				`beds_info`= ?,
				`capab`= ?,
				`status_id`= ?,
				`status_about`= ?,
				`lat`= ?,
				`lng`= ?,
				`contact_name`= ?,
				`contact_email`= ?,
				`contact_phone`= ?,
				`security_contact`= ?,
				`security_email`= ?,
				`security_phone`= ?,
				`opening_hours`= ?,
				`access_rules`= ?,
				`security_reqs`= ?,
				`pager_p`= ?,
				`pager_s`= ?,
				`type`= ?,
				`user_id`= ?,
				`notify_mailgroup` = ?,
				`notify_email` = ?,
				`notify_when` = ?,
				`updated`= ?
				WHERE `id`= ?;";	//	8/28/13

			$result = db_query($query, [trim($_POST['frm_name']), trim($_POST['frm_street']), trim($_POST['frm_city']), trim($_POST['frm_state']), trim($_POST['frm_handle']), trim($_POST['frm_icon_str']), trim($_POST['frm_boundary']), trim($_POST['frm_descr']), trim($_POST['frm_beds_a']), trim($_POST['frm_beds_o']), trim($_POST['frm_beds_info']), trim($_POST['frm_capab']), $fac_stat, trim($_POST['frm_status_about']), $the_lat, $the_lng, trim($_POST['frm_contact_name']), trim($_POST['frm_contact_email']), trim($_POST['frm_contact_phone']), trim($_POST['frm_security_contact']), trim($_POST['frm_security_email']), trim($_POST['frm_security_phone']), $frm_opening_hours, trim($_POST['frm_access_rules']), trim($_POST['frm_security_reqs']), trim($_POST['frm_pager_p']), trim($_POST['frm_pager_s']), sanitize_int($_POST['frm_type']), $by, sanitize_int($_POST['frm_notify_mailgroup']), trim($_POST['frm_notify_email']), trim($_POST['frm_notify_when']), trim($now), $fac_id]);

			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_FACILITY_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}	//2/17/11
			$list = $_POST['frm_exist_groups']; 	//	4/14/11
			$ex_grps = explode(',', $list); 	//	4/14/11 
			
			if($curr_groups != $groups) { 	//	4/14/11
				foreach($_POST['frm_group'] as $posted_grp) { 	//	4/14/11
					if(!in_array($posted_grp, $ex_grps)) {
						$posted_grp_int = sanitize_int($posted_grp);
						$query  = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
								(?, 3, ?, ?, ?, 'Allocated to Group' , ?)";
						$result = db_query($query, [$posted_grp_int, $now, $fac_stat, $fac_id, $by]);	
						}
					}
				foreach($ex_grps as $existing_grps) { 	//	4/14/11
					if(!in_array($existing_grps, $_POST['frm_group'])) {
						$existing_grps_int = sanitize_int($existing_grps);
						$query  = "DELETE FROM `{$GLOBALS['mysql_prefix']}allocates` WHERE `type` = 3 AND `group` = ? AND `resource_id` = ?";
						$result = db_query($query, [$existing_grps_int, $fac_id]);	
						}
					}
				}				
			
			
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$frm_opening_hours = base64_encode(serialize($_POST['frm_opening_hours']));
		$by = $_SESSION['user_id'];		//	4/14/11
		$frm_lat = (empty($_POST['frm_lat']))? NULL : trim($_POST['frm_lat']);		// 7/22/10
		$frm_lng = (empty($_POST['frm_lng']))? NULL : trim($_POST['frm_lng']);		// 7/15/10
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 6/4/2013
		$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}facilities` (
			`name`, `street`, `city`, `state`, `handle`, `icon_str`, `boundary`, `description`, `beds_a`, `beds_o`, `beds_info`, `capab`, `status_id`, `status_about`, `contact_name`, `contact_email`, `contact_phone`, `security_contact`, `security_email`, `security_phone`, `opening_hours`, `access_rules`, `security_reqs`, `pager_p`, `pager_s`, `lat`, `lng`, `type`, `user_id`, `notify_mailgroup`, `notify_email`, `notify_when`, `updated` )
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";	//	8/28/13

		$result = db_query($query, [trim($_POST['frm_name']), trim($_POST['frm_street']), trim($_POST['frm_city']), trim($_POST['frm_state']), trim($_POST['frm_handle']), trim($_POST['frm_icon_str']), trim($_POST['frm_boundary']), trim($_POST['frm_descr']), trim($_POST['frm_beds_a']), trim($_POST['frm_beds_o']), trim($_POST['frm_beds_info']), trim($_POST['frm_capab']), sanitize_int($_POST['frm_status_id']), trim($_POST['frm_status_about']), trim($_POST['frm_contact_name']), trim($_POST['frm_contact_email']), trim($_POST['frm_contact_phone']), trim($_POST['frm_security_contact']), trim($_POST['frm_security_email']), trim($_POST['frm_security_phone']), $frm_opening_hours, trim($_POST['frm_access_rules']), trim($_POST['frm_security_reqs']), trim($_POST['frm_pager_p']), trim($_POST['frm_pager_s']), $frm_lat, $frm_lng, sanitize_int($_POST['frm_type']), $_SESSION['user_id'], sanitize_int($_POST['frm_notify_mailgroup']), trim($_POST['frm_notify_email']), trim($_POST['frm_notify_when']), trim($now)]);
		$new_id=db()->insert_id;

//	9/10/13 File Upload support
		$print = "";
		if ((isset($_FILES['frm_file'])) && ($_FILES['frm_file']['name'] != "")){
			$nogoodFile = false;	
			$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py"); 
			foreach ($blacklist as $file) { 
				if(preg_match("/$file\$/i", $_FILES['frm_file']['name'])) { 
					$nogoodFile = true;
					}
				}
			if(!$nogoodFile) {
				$exists = false;
				$existing_file = "";
				$upload_directory = "./files/";
				if (!(file_exists($upload_directory))) {				
					mkdir ($upload_directory, 0770);
					}
				chmod($upload_directory, 0770);	
				$filename = rand(1,999999);
				$realfilename = $_FILES["frm_file"]["name"];
				$file = $upload_directory . $filename;
					
//	Does the file already exist in the files table		

				$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}files` WHERE `orig_filename` = ?";
				$result = db_query($query, [$realfilename]);
				if(db()->affected_rows == 0) {	//	file doesn't exist already
					if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
						if (strlen(filesize($file)) < 20000000) {
							$print .= "";
							} else {
							$print .= "Attached file is too large!";
							}
						} else {
						$print .= "Error uploading file";
						}
					} else {
					$row = stripslashes_deep($result->fetch_assoc());			
					$exists = true;
					$existing_file = $row['filename'];	//	get existing file name
					}
					
				$from = $_SERVER['REMOTE_ADDR'];	
				$filename = ($existing_file == "") ? $filename : $existing_file;	//	if existing file, use this file and write new db entry with it.
				$frm_file_title = sanitize_string($_POST['frm_file_title']);
				$frm_filetype = sanitize_string($_FILES['frm_file']['type']);
				$query_insert  = "INSERT INTO `{$GLOBALS['mysql_prefix']}files` (
						`title` , `filename` , `orig_filename`, `ticket_id` , `responder_id` , `facility_id`, `type`, `filetype`, `_by`, `_on`, `_from`
					) VALUES (?, ?, ?, ?, 0, 0, 0, ?, ?, ?, ?)";
				$result_insert	= db_query($query_insert, [$frm_file_title, $filename, $realfilename, $id, $frm_filetype, $by, $now, $from]);
				if($result_insert) {	//	is the database insert successful
					$dbUpdated = true;
					} else {	//	problem with the database insert
					$dbUpdated = false;				
					}
				}
			} else {	// Problem with the file upload
			$fileUploaded = false;
			}	
			
// End of file upload

		$status_id = $_POST['frm_status_id'];	//4/14/11
		foreach ($_POST['frm_group'] as $grp_val) {	// 6/10/11
		if(test_allocates($new_id, $grp_val, 3))	{		
			$grp_val_int = sanitize_int($grp_val);
			$query_a  = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
					(?, 3, ?, ?, ?, 'Allocated to Group' , ?)";
			$result_a = db_query($query_a, [$grp_val_int, $now, $status_id, $new_id, $by]);	
			}
		}
		
		do_log($GLOBALS['LOG_FACILITY_ADD'], 0, db()->insert_id, $_POST['frm_status_id']);	//	2/17/11

		$caption = "<B>Facility  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/facilities_add_screen_NM.php');
			} else {
			require_once('./forms/facilities_add_screen.php');
			}
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/facilities_edit_screen_NM.php');
			} else {
			require_once('./forms/facilities_edit_screen.php');
			}
		}		// end if ($_GET['edit'])
		
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

	if ($_getview == 'true') {
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/facilities_view_screen_NM.php');
			} else {
			require_once('./forms/facilities_view_screen.php');
			}
		}
	
// ============================================= initial display =======================
		if (!isset($mapmode)) {$mapmode="a";}
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/facilities_screen_NM.php');
			} else {
			require_once('./forms/facilities_screen.php');
			}
		exit();
?>