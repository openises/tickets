<?php

error_reporting(E_ALL);
$facs_side_bar_height = .5;		// max height of facilities sidebar as decimal fraction of screen height - default is 0.6 (60%)
$zoom_tight = FALSE;				// replace with a decimal number to over-ride the standard default zoom setting
$iw_width= "300px";					// map infowindow with
/*
9/10/2013 New File - for locations where there are known problems.
*/

@session_start();	
session_write_close();
if (!($_SESSION['internet'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'warn_locations_nm.php';
	header("Location: http://$host$uri/$extra");								// to top of calling script		
	exit();
	}

require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));
$key_field_size = 30;
$st_size = (get_variable("locale") ==0)?  2: 4;		

extract($_GET);
extract($_POST);
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}


function loc_format_date($date){
	if (get_variable('locale')==1)	{return date("j/n/y H:i",$date);}					// 08/27/10 - Revised to show UK format for locale = 1	
	else 							{return date(get_variable("date_format"),$date);}	// return date(get_variable("date_format"),strtotime($date));
	}				// end function fac format date
function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}

$usng = get_text('USNG');
$osgb = get_text('OSGB');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Location Warning Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 2em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { left: -1px; top: 0; position: absolute; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
		.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
		div.tabBox {}
		div.tabArea { font-size: 80%; font-weight: bold; padding: 0px 0px 3px 0px; }
		span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; -moz-border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
				padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
		span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; -moz-border-radius: .75em .75em 0em 0em;
				border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
		span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
		div.content { font-size: 80%; background-color: #F0F0F0; border: 2px outset #707070; -moz-border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
				position: relative;	z-index: 101; cursor: normal; height: 250px;}
		div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
	</STYLE>
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT><!-- 10/23/12-->

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
	<script type="text/javascript" src="./js/usng.js"></script>
	<script type="text/javascript" src="./js/osgb.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
?>
			<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
			<script type="text/javascript" src="./js/Google.js"></script>
<?php 
			}
		}
?>
	<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
	<script type="text/javascript" src="./js/L.Graticule.js"></script>
	<script type="text/javascript" src="./js/leaflet-providers.js"></script>
	<script type="text/javascript" src="./js/geotools2.js"></script>
	<script>
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

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
	var check_initialized = false;
	var check_interval = null;	

	function get_new_colors() {
		window.location.href = '<?php print basename(__FILE__);?>';
		}
		
	var grid_bool = false;		
	function toglGrid() {						// toggle
		grid_bool = !grid_bool;
		if (grid_bool)	{ grid = new Graticule(map); }
		else 			{ grid.setMap(null); }
		}		// end function toglGrid()
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
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

</SCRIPT>
<?php
	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Location - checkbox
		$query = "DELETE FROM $GLOBALS[mysql_prefix]warnings WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Location <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "NULL" : quote_smart(trim($_POST['frm_lat'])) ;
			$the_lng = empty($_POST['frm_lng'])? "NULL" : quote_smart(trim($_POST['frm_lng'])) ;
			$the_type = intval($_POST['frm_loc_type']);
			
			$loc_id = $_POST['frm_id'];
			$by = $_SESSION['user_id'];					// 6/4/2013
			$from = $_SERVER['REMOTE_ADDR'];			
			$query = "UPDATE `$GLOBALS[mysql_prefix]warnings` SET
				`title`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) . ",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`loc_type`= " . 	$the_type . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",				
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`_by`= " . 			quote_smart(trim($by)) . ",
				`_on`= " . 			quote_smart(trim($now)) . ",
				`_from`= " . 		quote_smart(trim($from)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_WARNLOCATION_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}	//2/17/11
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$by = $_SESSION['user_id'];		//	4/14/11
		$frm_lat = (empty($_POST['frm_lat']))? 'NULL': quote_smart(trim($_POST['frm_lat']));
		$frm_lng = (empty($_POST['frm_lng']))? 'NULL': quote_smart(trim($_POST['frm_lng']));
		$the_type = empty($_POST['frm_loc_type'])? 4 : $_POST['frm_loc_type'] ;
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
		$by = $_SESSION['user_id'];					// 6/4/2013
		$from = $_SERVER['REMOTE_ADDR'];			
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]warnings` (
			`title`, 
			`street`, 
			`city`, 
			`state`, 
			`lat`, 
			`lng`, 
			`loc_type`,
			`description`, 
			`_by`, 
			`_on`, 
			`_from` )
			VALUES (" .
			quote_smart(trim($_POST['frm_name'])) . "," .
			quote_smart(trim($_POST['frm_street'])) . "," .
			quote_smart(trim($_POST['frm_city'])) . "," .
			quote_smart(trim($_POST['frm_state'])) . "," .
			$frm_lat . "," .
			$frm_lng . "," .			
			$the_type . "," .					
			quote_smart(trim($_POST['frm_descr'])) . "," .
			quote_smart(trim($by)) . "," .
			quote_smart(trim($now)) . "," .
			quote_smart(trim($from)) . ");";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

		do_log($GLOBALS['LOG_WARNLOCATION_ADD'], 0, mysql_insert_id(), 0);	//	2/17/11

		$caption = "<B>Location  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
	if ($_getadd == 'true') {
		if (!($_SESSION['internet'])) {
			print "Not usable in No-Maps mode<BR />";
			exit();
			} else {
			require_once('./incs/links.inc.php');
			require_once('./forms/wl_add_screen.php');
			}
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
	if ($_getedit == 'true') {
		if (!($_SESSION['internet'])) {
			print "Not usable in No-Maps mode<BR />";
			exit();
			} else {
			require_once('./incs/links.inc.php');
			require_once('./forms/wl_edit_screen.php');
			}
		exit();
		}		// end if ($_GET['edit'])
// view =================================================================================================================

	if ($_getview == 'true') {
		if (!($_SESSION['internet'])) {
			print "Not usable in No-Maps mode<BR />";
			exit();
			} else {
			require_once('./incs/links.inc.php');
			require_once('./forms/wl_view_screen.php');
			}
		exit();
		}		// end if ($_GET['view'])
// ============================================= initial display =======================
	if (!isset($mapmode)) {$mapmode="a";}
	if (!($_SESSION['internet'])) {
		print "Not usable in No-Maps mode<BR />";
		exit();
		} else {
		require_once('./incs/links.inc.php');
		require_once('./forms/wl_screen.php');
		}
	exit();
?>