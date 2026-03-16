<?php
error_reporting(E_ALL);
$iw_width= "800px";			// map infowindow with
$side_bar_height = 1.0;		// height of units sidebar as decimal fraction - default is 0.9 (90%)
$zoom_tight = FALSE;		// replace with a decimal number to over-ride the standard default zoom setting
/*
*/

require_once('./incs/functions.inc.php');
@session_start();
session_write_close();
$sess_id = session_id();
do_login(basename(__FILE__));
// Auth-gate: personnel module requires at least user-level access (not guest/unit/stats/service)
if (is_guest() || !isset($_SESSION['level']) || $_SESSION['level'] > $GLOBALS['LEVEL_USER']) {
    header('Location: main.php');
    exit();
}
//require_once('./incs/all_forms_js_variables.inc.php');
$key_field_size = 30;
$email_text = "";
// Phase 2 security cleanup: removed extract — all variables already accessed explicitly
$scr_width = $_SESSION['scr_width'];
$scr_height = $_SESSION['scr_height'];
$left_col_width = $scr_width * 0.58;
$right_col_width = $scr_width * 0.33; 

$u_types = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}member_types` ORDER BY `id`";		// types in use
$result = db_query($query);
while ($row = stripslashes_deep($result->fetch_assoc())) {
	$u_types [$row['id']] = array ($row['name']);		// name, index
	}
unset($result);

$st_types = array();
$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}member_status` ORDER BY `id`";		// types in use
$result = db_query($query);
while ($row = stripslashes_deep($result->fetch_assoc())) {
	$st_types [$row['id']] = array ($row['status_val']);		// name, index
	}
unset($result);

$user= $_SESSION['user_id'];
$level = $_SESSION['level'];
$team_manager = 0;

$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets CAD Membership Database</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="./js/Control.Geocoder.css" />
<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>

<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<script type="application/x-javascript" src="./js/proj4js.js"></script>
<script type="application/x-javascript" src="./js/proj4-compressed.js"></script>
<script type="application/x-javascript" src="./js/leaflet/leaflet.js"></script>
<script type="application/x-javascript" src="./js/proj4leaflet.js"></script>
<script type="application/x-javascript" src="./js/leaflet/KML.js"></script>
<script type="application/x-javascript" src="./js/leaflet/gpx.js"></script>  
<script type="application/x-javascript" src="./js/osopenspace.js"></script>
<script type="application/x-javascript" src="./js/leaflet-openweathermap.js"></script>
<script type="application/x-javascript" src="./js/esri-leaflet.js"></script>
<script type="application/x-javascript" src="./js/Control.Geocoder.js"></script>
<script type="application/x-javascript" src="./js/usng.js"></script>
<script type="application/x-javascript" src="./js/osgb.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (safe_strlen($api_key) == 39)?  "key={$api_key}&" : false;
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
<SCRIPT TYPE="application/x-javascript" SRC="./js/member.js"></SCRIPT>
<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<script type="application/x-javascript" src="./js/geotools2.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var colors = new Array ('odd', 'even');
var viewportwidth, viewportheight, mapWidth, mapHeight, outerwidth, outerheight, colwidth, leftcolwidth, rightcolwidth, colheight, listHeight, listwidth, leftlistwidth, rightlistwidth, inner_listwidth, celwidth, res_celwidth, fac_celwidth;

var memb_field = 'teamno';
var memb_direct = 'ASC';
function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	set_fontsizes(viewportwidth, "fullscreen");
	mapWidth = viewportwidth * .30;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	leftcolwidth = viewportwidth * .55;
	rightcolwidth = viewportwidth * .30;
	colheight = outerheight * .95;
	listHeight = viewportheight * .8;
	listwidth = colwidth * .99;
	leftlistwidth = leftcolwidth * .99;
	rightlistwidth = rightcolwidth * .99;
	inner_listwidth = leftlistwidth *.98;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
	if($('list')) {$('list').style.width = leftcolwidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
	if($('memberssheading')) {$('memberssheading').style.width = leftcolwidth + "px";}
	if($('memberlist')) {$('memberlist').style.width = leftcolwidth + "px";}
	if($('the_list')) {$('the_list').style.width = leftcolwidth + "px";}
	load_memberlist(memb_field, memb_direct);
	pop_summary(); 
	}

try {
	parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
//		parent.frames["upper"].check_bin();		
	}
catch(e) {
	}

parent.upper.show_butts();												// 11/2/08

function go_there(where) {
	document.go_Form.action = where;
	document.go_Form.submit();
	}				// end function go there ()
	
function go_edit(memberid) {
	document.toedit_Form.action = "member.php?func=member&edit=true&id=" + memberid;
	document.toedit_Form.submit();
	}				// end function go there ()	

function whatBrows() {									//Displays the generic browser type
	window.alert("Browser is : " + type);
	}

function hideit (elid) {
	ShowLayer(elid, "none");
	}

function showit (elid) {
	ShowLayer(elid, "block");
	}
	
function chknum(val) { 
	return ((val.trim().replace(/\D/g, "")==val.trim()) && (val.trim().length>0));
	}

function chkval(val, lo, hi) { 
	return  (chknum(val) && !((val> hi) || (val < lo)));
	}

function datechk_s(theForm) {		// pblm start vs now
	var start = new Date();
	start.setFullYear(theForm.frm_year_completed.value, theForm.frm_month_completed.value-1, theForm.frm_day_completed.value);
	start.setHours(theForm.frm_hour_completed.value, theForm.frm_minute_completed.value, 0,0);
	var now = new Date();
	return (start.valueOf() <= now.valueOf());	
	}
	
function datechk_e(theForm) {		// pblm end vs now
	var end = new Date();
	end.setFullYear(theForm.frm_year_refresh.value, theForm.frm_month_refresh.value-1, theForm.frm_day_refresh.value);
	end.setHours(theForm.frm_hour_refresh.value, theForm.frm_minute_refresh.value, 0,0);
	var now = new Date();
	return (end.valueOf() <= now.valueOf());	
	}
	
function datechk_r(theForm) {		// pblm start vs end
	var start = new Date();
	start.setFullYear(theForm.frm_year_completed.value, theForm.frm_month_completed.value-1, theForm.frm_day_completed.value);
	start.setHours(theForm.frm_hour_completed.value, theForm.frm_minute_completed.value, 0,0);
	var end = new Date();
	end.setFullYear(theForm.frm_year_refresh.value, theForm.frm_month_refresh.value-1, theForm.frm_day_refresh.value);
	end.setHours(theForm.frm_hour_refresh.value,theForm.frm_minute_refresh.value, 0,0);
	return (start.valueOf() <= end.valueOf());	
	}		

function validate(theForm) {						// Responder form contents validation	8/11/09
	if (theForm.frm_remove) {
		if (theForm.frm_remove.checked) {
			var str = "Please confirm removing '" + theForm.frm_surname.value + "'";
			if(confirm(str)) 	{
				theForm.submit();					// 8/11/09
				return true;}
			else 				{return false;}
			}
		}
	var errmsg="";
	if (theForm.frm_surname.value.trim()=="")													{errmsg+="Surname is required.\n";}
	if (theForm.frm_firstname.value.trim()=="")													{errmsg+="First Name is required.\n";}
	if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)					{errmsg+="Member TYPE is required.\n";}	// 1/1/09
	if (theForm.frm_mem_stat_id.options[theForm.frm_mem_stat_id.selectedIndex].value==0)	{errmsg+="Member STATUS is required.\n";}
	if (theForm.frm_medical.value.trim()=="")													{errmsg+="Member Medical details is required.\n";}
	
	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		theForm.submit();

		}
	}				// end function va lidate(theForm)

function validate_skills(theForm) {						// Responder form contents validation	8/11/09
	var errmsg="";
	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {
		theForm.submit();
		}
	}				// end function va lidate(theForm)		

function add_res () {		// turns on add member form
	showit('res_add_form');
	hideit('tbl_members');
	}

function collect(){				// constructs a string of id's for deletion
	var str = sep = "";
	for (i=0; i< document.del_Form.elements.length; i++) {
		if (document.del_Form.elements[i].type == 'checkbox' && (document.del_Form.elements[i].checked==true)) {
			str += (sep + document.del_Form.elements[i].name.substring(1));		// drop T
			sep = ",";
			}
		}
	document.del_Form.idstr.value=str;
	}
	
function do_Post(the_table) {
	document.tables.tablename.value=the_table;
	document.tables.submit();
	}
	
function go_there (where) {
	document.go_Form.action = where;
	document.go_Form.submit();
	}

function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
			}
		}
	if(tabid == "tab1") {
		if($('tab1')) {CngClass('tab1', 'tabinuse');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content1')) {$("content1").style.display = "block";}
		} else if(tabid == "tab2") {
		if($('tab2')) {CngClass('tab2', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content2')) {$("content2").style.display = "block";}
		init_minimap(3, lat,lng, "", 13, <?php print get_variable('locale');?>, 1);
		minimap.setView([lat,lng], 13);
		}
	}

function pop_summary() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/db_summary.php?session=<?php print MD5($sess_id);?>&version=' + randomnumber;
	sendRequest (url, pop_cb, "");			
	function pop_cb(req) {
		var the_ret_arr=JSON.decode(req.responseText);
			$('f3').innerHTML = the_ret_arr[0];
			$('f1').innerHTML = the_ret_arr[1];
			$('f2').innerHTML = the_ret_arr[2];
			$('f4').innerHTML = the_ret_arr[3];				
		}
	}

function do_Post(the_table) {
	document.tables.tablename.value=the_table;
	document.tables.submit();
	}

function go_there (where) {
	document.go_Form.action = where;
	document.go_Form.submit();
	}

function linkFromSumm(table, index) {
	document.toTables.tablename.value = table;
	document.toTables.id.value = index;	
	document.toTables.submit();		
	}
</SCRIPT>
<FORM NAME='tables' METHOD = 'post' ACTION='tables.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='r'>
<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
</FORM>

<?php
	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? 		$_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? 			$_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? 			$_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? 				$_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? 				$_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? 			$_GET['view']: "";
	$_getextra = 		(array_key_exists ('extra',$_GET ))? 			$_GET['extra']: "";
	$_get_training = 	(array_key_exists ('training',$_GET))? 			$_GET['training']:  "";
	$_get_event = 		(array_key_exists ('event',$_GET))? 			$_GET['event']:  "";
	$_get_capability = 	(array_key_exists ('capability',$_GET))? 		$_GET['capability']:  "";
	$_get_clothing = 	(array_key_exists ('clothing',$_GET))? 			$_GET['clothing']:  "";
	$_get_equipment = 	(array_key_exists ('equipment',$_GET))? 		$_GET['equipment']:  "";
	$_get_vehicle = 	(array_key_exists ('vehicle',$_GET))? 			$_GET['vehicle']:  "";	
	$_get_files = 		(array_key_exists ('files',$_GET))? 			$_GET['files']:  "";		
	$_get_e_training = 	(array_key_exists ('e_training',$_GET))? 		$_GET['e_training']:  "";
	$_get_e_event = 	(array_key_exists ('e_event',$_GET))? 			$_GET['e_event']:  "";	
	$_get_e_capability =(array_key_exists ('e_capability',$_GET))? 		$_GET['e_capability']:  "";	
	$_get_e_clothing = 	(array_key_exists ('e_clothing',$_GET))? 		$_GET['e_clothing']:  "";	
	$_get_e_equipment = (array_key_exists ('e_equipment',$_GET))? 		$_GET['e_equipment']:  "";
	$_get_e_vehicle = 	(array_key_exists ('e_vehicle',$_GET))? 		$_GET['e_vehicle']:  "";	
	$_get_e_files = 	(array_key_exists ('e_files',$_GET))? 			$_GET['e_files']:  "";	
	$_get_addtpack = 	(array_key_exists ('goaddtpack',$_GET))? 		$_GET['goaddtpack']:  "";
	$_get_addevent = 	(array_key_exists ('goaddevent',$_GET))? 		$_GET['goaddevent']:  "";	
	$_get_addcapab = 	(array_key_exists ('goaddcapab',$_GET))? 		$_GET['goaddcapab']:  "";	
	$_get_addcloth = 	(array_key_exists ('goaddcloth',$_GET))? 		$_GET['goaddcloth']:  "";	
	$_get_addequip = 	(array_key_exists ('goaddequip',$_GET))? 		$_GET['goaddequip']:  "";	
	$_get_addveh = 		(array_key_exists ('goaddveh',$_GET))? 			$_GET['goaddveh']:  "";	
	$_get_addfile = 	(array_key_exists ('goaddfile',$_GET))? 		$_GET['goaddfile']:  "";		
	$_get_edittpack = 	(array_key_exists ('goedittpack',$_GET))? 		$_GET['goedittpack']:  "";
	$_get_editevent = 	(array_key_exists ('goeditevent',$_GET))? 		$_GET['goeditevent']:  "";	
	$_get_editcapab = 	(array_key_exists ('goeditcapab',$_GET))? 		$_GET['goeditcapab']:  "";	
	$_get_editcloth = 	(array_key_exists ('goeditcloth',$_GET))? 		$_GET['goeditcloth']:  "";	
	$_get_editequip = 	(array_key_exists ('goeditequip',$_GET))? 		$_GET['goeditequip']:  "";	
	$_get_editveh = 	(array_key_exists ('goeditveh',$_GET))? 		$_GET['goeditveh']:  "";		
	$_get_editfile = 	(array_key_exists ('goeditfile',$_GET))? 		$_GET['goeditfile']:  "";
	
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Member - checkbox - 8/12/09
		$frm_field3 = (array_key_exists('frm_field3', $_POST)) ? $_POST['frm_field3'] : 0;
		$frm_field7 = isset($_POST['frm_field7']) ? intval($_POST['frm_field7']) : 0;		// 3/14/26 - null-safe for bigint column
		$frm_field12 = (isset($_POST['frm_field12']) && $_POST['frm_field12'] !== '') ? floatval($_POST['frm_field12']) : NULL;	// 3/14/26 - lat (double)
		$frm_field13 = (isset($_POST['frm_field13']) && $_POST['frm_field13'] !== '') ? floatval($_POST['frm_field13']) : NULL;	// 3/14/26 - lng (double)
		$frm_field21 = isset($_POST['frm_field21']) ? intval($_POST['frm_field21']) : 0;	// 3/14/26 - null-safe for int column
		// 3/14/26 - Enum fields (Yes/No) default to schema defaults when empty (MySQL strict mode)
		$enum_defaults = array(8 => 'Yes', 15 => 'No', 19 => 'No', 46 => 'No', 47 => 'No', 48 => 'No', 49 => 'No', 50 => 'No', 51 => 'No', 52 => 'No', 53 => 'No', 54 => 'No', 55 => 'No');
		foreach ($enum_defaults as $_ef => $_ed) {
			$vname = 'frm_field' . $_ef;
			$$vname = (isset($_POST[$vname]) && in_array($_POST[$vname], array('Yes', 'No'))) ? $_POST[$vname] : $_ed;
		}
		$frm_field18 = "$_POST[frm_year_frm_field18]-$_POST[frm_month_frm_field18]-$_POST[frm_day_frm_field18] 00:00:00";
		$frm_field17 = "$_POST[frm_year_frm_field17]-$_POST[frm_month_frm_field17]-$_POST[frm_day_frm_field17] 00:00:00";
		$frm_field16 = "$_POST[frm_year_frm_field16]-$_POST[frm_month_frm_field16]-$_POST[frm_day_frm_field16] 00:00:00";
		$frm_field56 = (isset($_POST['frm_year_frm_field56'])) ? "$_POST[frm_year_frm_field56]-$_POST[frm_month_frm_field56]-$_POST[frm_day_frm_field56] 00:00:00" : NULL;
		$frm_field57 = (isset($_POST['frm_year_frm_field57'])) ? "$_POST[frm_year_frm_field57]-$_POST[frm_month_frm_field57]-$_POST[frm_day_frm_field57] 00:00:00" : NULL;
		$frm_field58 = (isset($_POST['frm_year_frm_field58'])) ? "$_POST[frm_year_frm_field58]-$_POST[frm_month_frm_field58]-$_POST[frm_day_frm_field58] 00:00:00" : NULL;
		$frm_field59 = (isset($_POST['frm_year_frm_field59'])) ? "$_POST[frm_year_frm_field59]-$_POST[frm_month_frm_field59]-$_POST[frm_day_frm_field59] 00:00:00" : NULL;
		$frm_field60 = (isset($_POST['frm_year_frm_field60'])) ? "$_POST[frm_year_frm_field60]-$_POST[frm_month_frm_field60]-$_POST[frm_day_frm_field60] 00:00:00" : NULL;
		$frm_field61 = (isset($_POST['frm_year_frm_field61'])) ? "$_POST[frm_year_frm_field61]-$_POST[frm_month_frm_field61]-$_POST[frm_day_frm_field61] 00:00:00" : NULL;
		$frm_field62 = (isset($_POST['frm_year_frm_field62'])) ? "$_POST[frm_year_frm_field62]-$_POST[frm_month_frm_field62]-$_POST[frm_day_frm_field62] 00:00:00" : NULL;
		$frm_field63 = (isset($_POST['frm_year_frm_field63'])) ? "$_POST[frm_year_frm_field63]-$_POST[frm_month_frm_field63]-$_POST[frm_day_frm_field63] 00:00:00" : NULL;
		$frm_field64 = (isset($_POST['frm_year_frm_field64'])) ? "$_POST[frm_year_frm_field64]-$_POST[frm_month_frm_field64]-$_POST[frm_day_frm_field64] 00:00:00" : NULL;
		$frm_field65 = (isset($_POST['frm_year_frm_field65'])) ? "$_POST[frm_year_frm_field65]-$_POST[frm_month_frm_field65]-$_POST[frm_day_frm_field65] 00:00:00" : NULL;		
		$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;
		$from = $_SERVER['REMOTE_ADDR'];			
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		$old_capabilities = array();
		$old_training = array();
		$old_events = array();
		$old_equipment = array();
		$old_vehicles = array();
		$old_clothing = array();
		$old_files = array();
		
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}allocations` WHERE `member_id` = ?";
		$result = db_query($query, [intval($_POST['frm_id'])]);
		while ($row = stripslashes_deep($result->fetch_assoc())) {
			if($row['skill_type'] ==1) {
				$old_training[] = $row['skill_id'];
				}
			if($row['skill_type'] ==2) {
				$old_capabilities[] = $row['skill_id'];
				}
			if($row['skill_type'] ==3) {
				$old_equipment[] = $row['skill_id'];
				}
			if($row['skill_type'] ==4) {
				$old_vehicles[] = $row['skill_id'];
				}
			if($row['skill_type'] ==5) {
				$old_clothing[] = $row['skill_id'];
				}
			if($row['skill_type'] ==6) {
				$old_events[] = $row['skill_id'];
				}						
			}

		$old_t = "";
		$old_ev = "";
		$old_c = "";
		$old_e = "";
		$old_v = "";
		$old_cl = "";
		
		foreach($old_training as $val) {
			$old_t .= $val . ",";
			}
			
		foreach($old_events as $val) {
			$old_ev .= $val . ",";
			}
	
		foreach($old_capabilities as $val) {
			$old_c .= $val . ",";
			}

		foreach($old_equipment as $val) {
			$old_e .= $val . ",";
			}

		foreach($old_vehicles as $val) {
			$old_v .= $val . ",";
			}

		foreach($old_clothing as $val) {
			$old_cl .= $val . ",";
			}		

		$old_t = substr($old_t, 0, -1);
		$old_ev = substr($old_ev, 0, -1);
		$old_c = substr($old_c, 0, -1);
		$old_e = substr($old_e, 0, -1);
		$old_v = substr($old_v, 0, -1);
		$old_cl = substr($old_cl, 0, -1);
		
		if(isset($_POST['frm_exist_pic'])) {
			$filename = "./mdb_pictures_waste/" . $_POST['frm_id'] . "/id.jpg";
			} else {
			$filename = "";
			}
		
		$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}waste_basket_m`
				(`field1`,
				`field2`,
				`field3`,
				`field4`,
				`field5`,
				`field6`,
				`field7`,
				`field8`,
				`field9`,
				`field10`,
				`field11`,
				`field12`,
				`field13`,
				`field14`,
				`field15`,
				`field16`,
				`field17`,
				`field18`,
				`field19`,
				`field20`,
				`field21`,
				`field22`,
				`field23`,
				`field24`,
				`field25`,
				`field26`,
				`field27`,
				`field28`,
				`field29`,
				`field30`,
				`field31`,
				`field32`,
				`field33`,
				`field34`,
				`field35`,
				`field36`,
				`field37`,
				`field38`,
				`field39`,
				`field40`,
				`field41`,
				`field42`,
				`field43`,
				`field44`,
				`field45`,
				`field46`,
				`field47`,
				`field48`,
				`field49`,
				`field50`,
				`field51`,
				`field52`,
				`field53`,
				`field54`,
				`field55`,
				`field56`,
				`field57`,
				`field58`,
				`field59`,
				`field60`,
				`field61`,
				`field62`,
				`field63`,
				`field64`,
				`field65`,
				`training`,
				`capabilities`,
				`equipment`,
				`vehicles`,
				`clothing`,
				`_by`,
				`_on`,
				`_from`,
				`old_id` )
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$result = db_query($query, [
				trim($_POST['frm_field1'] ?? ''),
				trim($_POST['frm_field2'] ?? ''),
				trim($frm_field3),
				trim($_POST['frm_field4'] ?? ''),
				trim($filename ?? ''),
				trim($_POST['frm_field6'] ?? ''),
				$frm_field7,
				$frm_field8,
				trim($_POST['frm_field9'] ?? ''),
				trim($_POST['frm_field10'] ?? ''),
				trim($_POST['frm_field11'] ?? ''),
				$frm_field12,
				$frm_field13,
				trim($_POST['frm_field14'] ?? ''),
				$frm_field15,
				trim($frm_field16 ?? ''),
				trim($frm_field17 ?? ''),
				trim($frm_field18 ?? ''),
				$frm_field19,
				trim($_POST['frm_field20'] ?? ''),
				$frm_field21,
				trim($_POST['frm_field22'] ?? ''),
				trim($_POST['frm_field23'] ?? ''),
				trim($_POST['frm_field24'] ?? ''),
				trim($_POST['frm_field25'] ?? ''),
				trim($_POST['frm_field26'] ?? ''),
				trim($_POST['frm_field27'] ?? ''),
				trim($_POST['frm_field28'] ?? ''),
				trim($_POST['frm_field29'] ?? ''),
				trim($_POST['frm_field30'] ?? ''),
				trim($_POST['frm_field31'] ?? ''),
				trim($_POST['frm_field32'] ?? ''),
				trim($_POST['frm_field33'] ?? ''),
				trim($_POST['frm_field34'] ?? ''),
				trim($_POST['frm_field35'] ?? ''),
				trim($_POST['frm_field36'] ?? ''),
				trim($_POST['frm_field37'] ?? ''),
				trim($_POST['frm_field38'] ?? ''),
				trim($_POST['frm_field39'] ?? ''),
				trim($_POST['frm_field40'] ?? ''),
				trim($_POST['frm_field41'] ?? ''),
				trim($_POST['frm_field42'] ?? ''),
				trim($_POST['frm_field43'] ?? ''),
				trim($_POST['frm_field44'] ?? ''),
				trim($_POST['frm_field45'] ?? ''),
				$frm_field46,
				$frm_field47,
				$frm_field48,
				$frm_field49,
				$frm_field50,
				$frm_field51,
				$frm_field52,
				$frm_field53,
				$frm_field54,
				$frm_field55,
				$frm_field56,
				$frm_field57,
				$frm_field58,
				$frm_field59,
				$frm_field60,
				$frm_field61,
				$frm_field62,
				$frm_field63,
				$frm_field64,
				$frm_field65,
				trim($old_t),
				trim($old_c),
				trim($old_e),
				trim($old_v),
				trim($old_cl),
				trim($who),
				trim($now),
				trim($from),
				trim($_POST['frm_id'])
			]);	
		
		$query = "DELETE FROM {$GLOBALS['mysql_prefix']}member WHERE `id`=?";
		$result = db_query($query, [intval($_POST['frm_id'])]);

		$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `member_id`=?";
		$result = db_query($query, [intval($_POST['frm_id'])]);
		
		$files_directory = "./mdb_files/" . $_POST['frm_id'];
		$files_wastebasket = "./mdb_file_waste/" . $_POST['frm_id'];
		$pictures_directory = "./mdb_pictures/" . $_POST['frm_id'];
		$pictures_wastebasket = "./mdb_pictures_waste/" . $_POST['frm_id'];
		
		if(file_exists($files_directory)) {
			rename ($files_directory, $files_wastebasket);
			}

		if(file_exists($pictures_directory)) {
			rename ($pictures_directory, $pictures_wastebasket);
			}

		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mdb_files` WHERE `member_id` = ?";
		$result = db_query($query, [intval($_POST['frm_id'])]);
		while ($row = stripslashes_deep($result->fetch_assoc())) {
			$oldname = explode("/",$row['name']);
			$filename = "./file_waste/" . $_POST['frm_id'] . "/" . $oldname[3];
			$query2 = "INSERT INTO `{$GLOBALS['mysql_prefix']}waste_basket_f`
					(`member_id`,
					`name`,
					`shortname`,
					`description`,
					`_on`)
				VALUES (?,?,?,?,?)";
			$result2 = db_query($query2, [
					trim($row['member_id']),
					trim($filename),
					trim($row['shortname']),
					trim($row['description']),
					trim($now)
				]);		
			}
			
		$query = "DELETE FROM {$GLOBALS['mysql_prefix']}mdb_files WHERE `member_id`=?";
		$result = db_query($query, [intval($_POST['frm_id'])]);
		
		$email_text = "Member " . $_POST['frm_field2'] . " " . $_POST['frm_field1'] . " Has been Deleted by user " . get_owner($who) . " on " . $now;
		$addrs = mdb_notify_user();
		if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
			$addr_arr = implode("|", array_unique($addrs));			
			do_send($addr_arr, "Member Data Changed", $email_text);
			}
?>
<SCRIPT>
		parent.frames["upper"].check_bin();	
</SCRIPT>
<?php		
		$caption = "<B>Member <I>" . htmlspecialchars($_POST['frm_field2']) . " " . htmlspecialchars($_POST['frm_field1']) . "</I> has been deleted from database.</B>";
		} else {
		if ($_getgoedit == 'true') {
			$errmsg = "";
			// 3/14/26 - Improved file upload handling with proper error checking
			if (isset($_FILES['frm_field5']) && $_FILES['frm_field5']['error'] === UPLOAD_ERR_OK) {
				$upload_directory = "./mdb_pictures/" . $_POST['frm_id'] . "/";
				if (!(file_exists($upload_directory))) {
					mkdir ($upload_directory, 0777);
					}
				chmod($upload_directory, 0777);
				$file = $upload_directory . "id.jpg";
				if (move_uploaded_file($_FILES['frm_field5']['tmp_name'], $file)) {	// If file uploaded OK
					if (filesize($file) < 149000) {		// 3/14/26 - was safe_strlen(filesize()) which always passed
						$filename = $file;
						$errmsg = "";
						} else {
						$filename = NULL;
						$errmsg = "Attached file is too large! (max 145KB)";
						}
				} else {
					$filename = isset($_POST['frm_exist_id_pic']) ? $_POST['frm_exist_id_pic'] : '';
					$errmsg = "File upload failed — could not save file.";
				}
			} elseif (isset($_FILES['frm_field5']) && $_FILES['frm_field5']['error'] !== UPLOAD_ERR_NO_FILE) {
				// File was selected but upload had an error (e.g. too large for PHP limits)
				$filename = isset($_POST['frm_exist_id_pic']) ? $_POST['frm_exist_id_pic'] : '';
				$errmsg = "File upload error (code " . $_FILES['frm_field5']['error'] . "). Check PHP upload_max_filesize.";
			} else {
				$filename = isset($_POST['frm_exist_id_pic']) ? $_POST['frm_exist_id_pic'] : '';
			}
			
			$image = $filename;
			$frm_field3 = (array_key_exists('frm_field3', $_POST)) ? $_POST['frm_field3'] : 0;
			$frm_field7 = isset($_POST['frm_field7']) ? intval($_POST['frm_field7']) : 0;		// 3/14/26 - null-safe for bigint column
			$frm_field12 = (isset($_POST['frm_field12']) && $_POST['frm_field12'] !== '') ? floatval($_POST['frm_field12']) : NULL;	// 3/14/26 - lat (double)
			$frm_field13 = (isset($_POST['frm_field13']) && $_POST['frm_field13'] !== '') ? floatval($_POST['frm_field13']) : NULL;	// 3/14/26 - lng (double)
			$frm_field21 = isset($_POST['frm_field21']) ? intval($_POST['frm_field21']) : 0;	// 3/14/26 - null-safe for int column
			// 3/14/26 - Enum fields (Yes/No) default to schema defaults when empty (MySQL strict mode)
			$enum_defaults = array(8 => 'Yes', 15 => 'No', 19 => 'No', 46 => 'No', 47 => 'No', 48 => 'No', 49 => 'No', 50 => 'No', 51 => 'No', 52 => 'No', 53 => 'No', 54 => 'No', 55 => 'No');
			foreach ($enum_defaults as $_ef => $_ed) {
				$vname = 'frm_field' . $_ef;
				$$vname = (isset($_POST[$vname]) && in_array($_POST[$vname], array('Yes', 'No'))) ? $_POST[$vname] : $_ed;
			}
			$frm_field18 = "$_POST[frm_year_frm_field18]-$_POST[frm_month_frm_field18]-$_POST[frm_day_frm_field18] 00:00:00";
			$frm_field17 = "$_POST[frm_year_frm_field17]-$_POST[frm_month_frm_field17]-$_POST[frm_day_frm_field17] 00:00:00";
			$frm_field16 = "$_POST[frm_year_frm_field16]-$_POST[frm_month_frm_field16]-$_POST[frm_day_frm_field16] 00:00:00";
			$frm_field56 = (isset($_POST['frm_year_frm_field56'])) ? "$_POST[frm_year_frm_field56]-$_POST[frm_month_frm_field56]-$_POST[frm_day_frm_field56] 00:00:00": NULL;
			$frm_field57 = (isset($_POST['frm_year_frm_field57'])) ? "$_POST[frm_year_frm_field57]-$_POST[frm_month_frm_field57]-$_POST[frm_day_frm_field57] 00:00:00": NULL;
			$frm_field58 = (isset($_POST['frm_year_frm_field58'])) ? "$_POST[frm_year_frm_field58]-$_POST[frm_month_frm_field58]-$_POST[frm_day_frm_field58] 00:00:00": NULL;
			$frm_field59 = (isset($_POST['frm_year_frm_field59'])) ? "$_POST[frm_year_frm_field59]-$_POST[frm_month_frm_field59]-$_POST[frm_day_frm_field59] 00:00:00": NULL;
			$frm_field60 = (isset($_POST['frm_year_frm_field60'])) ? "$_POST[frm_year_frm_field60]-$_POST[frm_month_frm_field60]-$_POST[frm_day_frm_field60] 00:00:00": NULL;
			$frm_field61 = (isset($_POST['frm_year_frm_field61'])) ? "$_POST[frm_year_frm_field61]-$_POST[frm_month_frm_field61]-$_POST[frm_day_frm_field61] 00:00:00": NULL;
			$frm_field62 = (isset($_POST['frm_year_frm_field62'])) ? "$_POST[frm_year_frm_field62]-$_POST[frm_month_frm_field62]-$_POST[frm_day_frm_field62] 00:00:00": NULL;
			$frm_field63 = (isset($_POST['frm_year_frm_field63'])) ? "$_POST[frm_year_frm_field63]-$_POST[frm_month_frm_field63]-$_POST[frm_day_frm_field63] 00:00:00": NULL;
			$frm_field64 = (isset($_POST['frm_year_frm_field64'])) ? "$_POST[frm_year_frm_field64]-$_POST[frm_month_frm_field64]-$_POST[frm_day_frm_field64] 00:00:00": NULL;
			$frm_field65 = (isset($_POST['frm_year_frm_field65'])) ? "$_POST[frm_year_frm_field65]-$_POST[frm_month_frm_field65]-$_POST[frm_day_frm_field65] 00:00:00": NULL;
			$frm_field6 = $_POST['frm_field6'];
			$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;		// 11/14/10
			$from = $_SERVER['REMOTE_ADDR'];
			$query = "UPDATE `{$GLOBALS['mysql_prefix']}member` SET
				`field1`=?, `field2`=?, `field3`=?, `field4`=?, `field5`=?,
				`field6`=?, `field7`=?, `field8`=?, `field9`=?, `field10`=?,
				`field11`=?, `field12`=?, `field13`=?, `field14`=?, `field15`=?,
				`field16`=?, `field17`=?, `field18`=?, `field19`=?, `field20`=?,
				`field21`=?, `field22`=?, `field23`=?, `field24`=?, `field25`=?,
				`field26`=?, `field27`=?, `field28`=?, `field29`=?, `field30`=?,
				`field31`=?, `field32`=?, `field33`=?, `field34`=?, `field35`=?,
				`field36`=?, `field37`=?, `field38`=?, `field39`=?, `field40`=?,
				`field41`=?, `field42`=?, `field43`=?, `field44`=?, `field45`=?,
				`field46`=?, `field47`=?, `field48`=?, `field49`=?, `field50`=?,
				`field51`=?, `field52`=?, `field53`=?, `field54`=?, `field55`=?,
				`field56`=?, `field57`=?, `field58`=?, `field59`=?, `field60`=?,
				`field61`=?, `field62`=?, `field63`=?, `field64`=?, `field65`=?,
				`_by`=?, `_on`=?, `_from`=?
				WHERE `id`=?";

			$result = db_query($query, [
				trim($_POST['frm_field1'] ?? ''),
				trim($_POST['frm_field2'] ?? ''),
				trim($frm_field3),
				trim($_POST['frm_field4'] ?? ''),
				trim($filename ?? ''),
				trim($frm_field6 ?? ''),
				$frm_field7,
				$frm_field8,
				trim($_POST['frm_field9'] ?? ''),
				trim($_POST['frm_field10'] ?? ''),
				trim($_POST['frm_field11'] ?? ''),
				$frm_field12,
				$frm_field13,
				trim($_POST['frm_field14'] ?? ''),
				$frm_field15,
				trim($frm_field16 ?? ''),
				trim($frm_field17 ?? ''),
				trim($frm_field18 ?? ''),
				$frm_field19,
				trim($_POST['frm_field20'] ?? ''),
				$frm_field21,
				trim($_POST['frm_field22'] ?? ''),
				trim($_POST['frm_field23'] ?? ''),
				trim($_POST['frm_field24'] ?? ''),
				trim($_POST['frm_field25'] ?? ''),
				trim($_POST['frm_field26'] ?? ''),
				trim($_POST['frm_field27'] ?? ''),
				trim($_POST['frm_field28'] ?? ''),
				trim($_POST['frm_field29'] ?? ''),
				trim($_POST['frm_field30'] ?? ''),
				trim($_POST['frm_field31'] ?? ''),
				trim($_POST['frm_field32'] ?? ''),
				trim($_POST['frm_field33'] ?? ''),
				trim($_POST['frm_field34'] ?? ''),
				trim($_POST['frm_field35'] ?? ''),
				trim($_POST['frm_field36'] ?? ''),
				trim($_POST['frm_field37'] ?? ''),
				trim($_POST['frm_field38'] ?? ''),
				trim($_POST['frm_field39'] ?? ''),
				trim($_POST['frm_field40'] ?? ''),
				trim($_POST['frm_field41'] ?? ''),
				trim($_POST['frm_field42'] ?? ''),
				trim($_POST['frm_field43'] ?? ''),
				trim($_POST['frm_field44'] ?? ''),
				trim($_POST['frm_field45'] ?? ''),
				$frm_field46,
				$frm_field47,
				$frm_field48,
				$frm_field49,
				$frm_field50,
				$frm_field51,
				$frm_field52,
				$frm_field53,
				$frm_field54,
				$frm_field55,
				$frm_field56,
				$frm_field57,
				$frm_field58,
				$frm_field59,
				$frm_field60,
				$frm_field61,
				$frm_field62,
				$frm_field63,
				$frm_field64,
				$frm_field65,
				trim($who),
				trim($now),
				trim($from),
				intval($_POST['frm_id'])
			]);
			do_log($GLOBALS['LOG_MEMBER_CHANGE'], intval($_POST['frm_id']), 0, $_POST['frm_field2'] . " " . $_POST['frm_field1']);

			$email_text = "Member " . $_POST['frm_field2'] . " " . $_POST['frm_field1'] . " Has been Changed by user " . get_owner($who) . " on " . $now . "\n\n";
			
			if(($image != NULL) || ($image != "")) {
				$email_text .= "Image added " . $image . "\n";
				}				
			
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));
				do_send($addr_arr, "Member Data Changed", $email_text);
				}
			$caption = "<B><FONT SIZE = '4px' COLOR = 'blue'>&nbsp;&nbsp;&nbsp;" . htmlspecialchars($_POST['frm_field2']) . " " . htmlspecialchars($_POST['frm_field1']) . " data has been updated.</FONT></B>&nbsp;&nbsp;" . $errmsg;
			}
		}				// end else {}

		if ($_getgoadd == 'true') {
			$errmsg = "";
			// 3/14/26 - was $_FILES['frm_image'] which never matched form field name 'frm_field5'
			if (isset($_FILES['frm_field5']) && $_FILES['frm_field5']['error'] === UPLOAD_ERR_OK) {
				$upload_directory = "./mdb_pictures/" . $_POST['frm_id'] . "/";
				if (!(file_exists($upload_directory))) {
					mkdir ($upload_directory, 0777);
					}
				chmod($upload_directory, 0777);
				$file = $upload_directory . "id.jpg";
				if (move_uploaded_file($_FILES['frm_field5']['tmp_name'], $file)) {	// If file uploaded OK
					if (filesize($file) < 149000) {		// 3/14/26 - was safe_strlen(filesize())
						$filename = $file;
						$errmsg = "";
						} else {
						$filename = NULL;
						$errmsg = "Attached file is too large! (max 145KB)";
						}
				} else {
					$filename = "";
					$errmsg = "File upload failed — could not save file.";
				}
			} elseif (isset($_FILES['frm_field5']) && $_FILES['frm_field5']['error'] !== UPLOAD_ERR_NO_FILE) {
				$filename = "";
				$errmsg = "File upload error (code " . $_FILES['frm_field5']['error'] . "). Check PHP upload_max_filesize.";
			} else {
				$filename = "";
			}
			
			$attachment = $filename;
			$frm_field3 = (array_key_exists('frm_field3', $_POST)) ? $_POST['frm_field3'] : 0;
			$frm_field7 = isset($_POST['frm_field7']) ? intval($_POST['frm_field7']) : 0;		// 3/14/26 - null-safe for bigint column
			$frm_field12 = (isset($_POST['frm_field12']) && $_POST['frm_field12'] !== '') ? floatval($_POST['frm_field12']) : NULL;	// 3/14/26 - lat (double)
			$frm_field13 = (isset($_POST['frm_field13']) && $_POST['frm_field13'] !== '') ? floatval($_POST['frm_field13']) : NULL;	// 3/14/26 - lng (double)
			$frm_field21 = isset($_POST['frm_field21']) ? intval($_POST['frm_field21']) : 0;	// 3/14/26 - null-safe for int column
			// 3/14/26 - Enum fields (Yes/No) default to schema defaults when empty (MySQL strict mode)
			$enum_defaults = array(8 => 'Yes', 15 => 'No', 19 => 'No', 46 => 'No', 47 => 'No', 48 => 'No', 49 => 'No', 50 => 'No', 51 => 'No', 52 => 'No', 53 => 'No', 54 => 'No', 55 => 'No');
			foreach ($enum_defaults as $_ef => $_ed) {
				$vname = 'frm_field' . $_ef;
				$$vname = (isset($_POST[$vname]) && in_array($_POST[$vname], array('Yes', 'No'))) ? $_POST[$vname] : $_ed;
			}
			$frm_field18 = "$_POST[frm_year_frm_field18]-$_POST[frm_month_frm_field18]-$_POST[frm_day_frm_field18] 00:00:00";
			$frm_field17 = "$_POST[frm_year_frm_field17]-$_POST[frm_month_frm_field17]-$_POST[frm_day_frm_field17] 00:00:00";
			$frm_field16 = "$_POST[frm_year_frm_field16]-$_POST[frm_month_frm_field16]-$_POST[frm_day_frm_field16] 00:00:00";
			$frm_field56 = (isset($_POST['frm_year_frm_field56'])) ? "$_POST[frm_year_frm_field56]-$_POST[frm_month_frm_field56]-$_POST[frm_day_frm_field56] 00:00:00": NULL;
			$frm_field57 = (isset($_POST['frm_year_frm_field57'])) ? "$_POST[frm_year_frm_field57]-$_POST[frm_month_frm_field57]-$_POST[frm_day_frm_field57] 00:00:00": NULL;
			$frm_field58 = (isset($_POST['frm_year_frm_field58'])) ? "$_POST[frm_year_frm_field58]-$_POST[frm_month_frm_field58]-$_POST[frm_day_frm_field58] 00:00:00": NULL;
			$frm_field59 = (isset($_POST['frm_year_frm_field59'])) ? "$_POST[frm_year_frm_field59]-$_POST[frm_month_frm_field59]-$_POST[frm_day_frm_field59] 00:00:00": NULL;
			$frm_field60 = (isset($_POST['frm_year_frm_field60'])) ? "$_POST[frm_year_frm_field60]-$_POST[frm_month_frm_field60]-$_POST[frm_day_frm_field60] 00:00:00": NULL;
			$frm_field61 = (isset($_POST['frm_year_frm_field61'])) ? "$_POST[frm_year_frm_field61]-$_POST[frm_month_frm_field61]-$_POST[frm_day_frm_field61] 00:00:00": NULL;
			$frm_field62 = (isset($_POST['frm_year_frm_field62'])) ? "$_POST[frm_year_frm_field62]-$_POST[frm_month_frm_field62]-$_POST[frm_day_frm_field62] 00:00:00": NULL;
			$frm_field63 = (isset($_POST['frm_year_frm_field63'])) ? "$_POST[frm_year_frm_field63]-$_POST[frm_month_frm_field63]-$_POST[frm_day_frm_field63] 00:00:00": NULL;
			$frm_field64 = (isset($_POST['frm_year_frm_field64'])) ? "$_POST[frm_year_frm_field64]-$_POST[frm_month_frm_field64]-$_POST[frm_day_frm_field64] 00:00:00": NULL;
			$frm_field65 = (isset($_POST['frm_year_frm_field65'])) ? "$_POST[frm_year_frm_field65]-$_POST[frm_month_frm_field65]-$_POST[frm_day_frm_field65] 00:00:00": NULL;
			$frm_field6 = $_POST['frm_field6'];
			$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;		// 11/14/10
			$from = $_SERVER['REMOTE_ADDR'];			
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 1/27/09
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}member`
					(`field1`, `field2`, `field3`, `field4`, `field5`,
					`field6`, `field7`, `field8`, `field9`, `field10`,
					`field11`, `field12`, `field13`, `field14`, `field15`,
					`field16`, `field17`, `field18`, `field19`, `field20`,
					`field21`, `field22`, `field23`, `field24`, `field25`,
					`field26`, `field27`, `field28`, `field29`, `field30`,
					`field31`, `field32`, `field33`, `field34`, `field35`,
					`field36`, `field37`, `field38`, `field39`, `field40`,
					`field41`, `field42`, `field43`, `field44`, `field45`,
					`field46`, `field47`, `field48`, `field49`, `field50`,
					`field51`, `field52`, `field53`, `field54`, `field55`,
					`field56`, `field57`, `field58`, `field59`, `field60`,
					`field61`, `field62`, `field63`, `field64`, `field65`,
					`_by`, `_on`, `_from` )
				VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_field1'] ?? ''),
					trim($_POST['frm_field2'] ?? ''),
					trim($frm_field3),
					trim($_POST['frm_field4'] ?? ''),
					trim($filename ?? ''),
					trim($frm_field6 ?? ''),
					$frm_field7,
					$frm_field8,
					trim($_POST['frm_field9'] ?? ''),
					trim($_POST['frm_field10'] ?? ''),
					trim($_POST['frm_field11'] ?? ''),
					$frm_field12,
					$frm_field13,
					trim($_POST['frm_field14'] ?? ''),
					$frm_field15,
					trim($frm_field16 ?? ''),
					trim($frm_field17 ?? ''),
					trim($frm_field18 ?? ''),
					$frm_field19,
					trim($_POST['frm_field20'] ?? ''),
					$frm_field21,
					trim($_POST['frm_field22'] ?? ''),
					trim($_POST['frm_field23'] ?? ''),
					trim($_POST['frm_field24'] ?? ''),
					trim($_POST['frm_field25'] ?? ''),
					trim($_POST['frm_field26'] ?? ''),
					trim($_POST['frm_field27'] ?? ''),
					trim($_POST['frm_field28'] ?? ''),
					trim($_POST['frm_field29'] ?? ''),
					trim($_POST['frm_field30'] ?? ''),
					trim($_POST['frm_field31'] ?? ''),
					trim($_POST['frm_field32'] ?? ''),
					trim($_POST['frm_field33'] ?? ''),
					trim($_POST['frm_field34'] ?? ''),
					trim($_POST['frm_field35'] ?? ''),
					trim($_POST['frm_field36'] ?? ''),
					trim($_POST['frm_field37'] ?? ''),
					trim($_POST['frm_field38'] ?? ''),
					trim($_POST['frm_field39'] ?? ''),
					trim($_POST['frm_field40'] ?? ''),
					trim($_POST['frm_field41'] ?? ''),
					trim($_POST['frm_field42'] ?? ''),
					trim($_POST['frm_field43'] ?? ''),
					trim($_POST['frm_field44'] ?? ''),
					trim($_POST['frm_field45'] ?? ''),
					$frm_field46,
					$frm_field47,
					$frm_field48,
					$frm_field49,
					$frm_field50,
					$frm_field51,
					$frm_field52,
					$frm_field53,
					$frm_field54,
					$frm_field55,
					$frm_field56,
					$frm_field57,
					$frm_field58,
					$frm_field59,
					$frm_field60,
					$frm_field61,
					$frm_field62,
					$frm_field63,
					$frm_field64,
					$frm_field65,
					trim($who),
					trim($now),
					trim($from)
				]);
			$new_id = db()->insert_id;	
			do_log($GLOBALS['LOG_MEMBER_ADD'], $new_id, 0, $_POST['frm_field2'] . " " . $_POST['frm_field1']);			
			
			// 3/14/26 - was $_FILES['frm_image'] which never matched form field name 'frm_field5'
			if (isset($_FILES['frm_field5']) && $_FILES['frm_field5']['error'] === UPLOAD_ERR_OK) {
				$upload_directory = "./mdb_pictures/" . $new_id . "/";
				if (!(file_exists($upload_directory))) {
					mkdir ($upload_directory, 0777);
					}
				chmod($upload_directory, 0777);
				$file = $upload_directory . "id.jpg";
				if (move_uploaded_file($_FILES['frm_field5']['tmp_name'], $file)) {	// If file uploaded OK
					if (filesize($file) < 149000) {		// 3/14/26 - was safe_strlen(filesize())
						$filename = $file;
						$errmsg = "";
						} else {
						$filename = NULL;
						$errmsg = "Attached file is too large! (max 145KB)";
						}
					} else {
					$filename = NULL;
					}
				} else {
				$filename = NULL;
				}

			$image = $filename;

			$query = "UPDATE `{$GLOBALS['mysql_prefix']}member` SET
				`field5`=?
				WHERE `id`=?";
			$result = db_query($query, [trim($filename ?? ''), $new_id]);	// 3/14/26 - null-safe trim

			$caption = "<B>Member  <i>" . htmlspecialchars($_POST['frm_field2']) . " " . htmlspecialchars($_POST['frm_field1']) . "</i> data has been added.</B>";

			$email_text = "Member " . $_POST['frm_field2'] . " " . $_POST['frm_field1'] . " Has been added by user " . get_owner($who) . " on " . $now . "\n\n";

			if(($image != NULL) || ($image != "")) {
				$email_text .= "Image added " . $image . "\n";
				}			
			
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));
				do_send($addr_arr, "Member Data Changed", $email_text);
				}				
			}							// end if ($_getgoadd == 'true')

// add allocations
		
		if ($_get_addtpack  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			$expires = $_POST['frm_expires'];
			$frm_completed = "$_POST[frm_year_completed]-$_POST[frm_month_completed]-$_POST[frm_day_completed]";
			$frm_refresh = ($expires == "Yes") ? "$_POST[frm_year_refresh]-$_POST[frm_month_refresh]-$_POST[frm_day_refresh]" : "";
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `frequency`, `completed`, `refresh_due`, `_on` )
				VALUES (?,?,?,?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_id']),
					1,
					trim($_POST['frm_skill']),
					trim($expires),
					trim($frm_completed),
					trim($frm_refresh),
					trim($now)
				]);
			
			$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nTraining Package - " . get_its_name($_POST['frm_skill'], 'package_name', 'training_packages') . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}

			$caption = "<B>Member Training Details Updated.</B>";

//			finished ($caption);		// wrap it up
		}							// end if ($_get_addtpack == 'true')

		if ($_get_addevent  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
					
			$start = "$_POST[frm_year_start]-$_POST[frm_month_start]-$_POST[frm_day_start]";
			$end = "$_POST[frm_year_end]-$_POST[frm_month_end]-$_POST[frm_day_end]";
			
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `start`, `end`, `_on` )
				VALUES (?,?,?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_id']),
					6,
					trim($_POST['frm_skill']),
					trim($start),
					trim($end),
					trim($now)
				]);
			
			$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nEvent - " . get_its_name($_POST['frm_skill'], 'event_name', 'events') . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}

			$caption = "<B>Member Event Attendance Details Updated.</B>";

//			finished ($caption);		// wrap it up
		}							// end if ($_get_addevent == 'true')				
		
		if ($_get_addcloth  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `completed`, `_on` )
				VALUES (?,?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_id']),
					5,
					trim($_POST['frm_skill']),
					trim($now),
					trim($now)
				]);
			
			$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nClothing Item - " . get_its_name($_POST['frm_skill'], 'clothing_item', 'clothing_types') . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}
				
			$caption = "<B>Member Clothing Details Updated.</B>";

//			finished ($caption);		// wrap it up
		}							// end if ($_get_addcloth == 'true')			

		if ($_get_addcapab  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_id']),
					2,
					trim($_POST['frm_skill']),
					trim($now)
				]);

			$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nCapability - " . get_its_name($_POST['frm_skill'], 'name', 'capability_types') . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}			
			
			$caption = "<B>Member Capabilities Updated.</B>";

//			finished ($caption);		// wrap it up
		}							// end if ($_get_addcapab == 'true')	

		if ($_get_addequip  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `_on` )
				VALUES (?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_id']),
					3,
					trim($_POST['frm_skill']),
					trim($now)
				]);

			$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nEquipment Item - " . get_its_name($_POST['frm_skill'], 'equipment_name', 'equipment_types') . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}			
			
			$caption = "<B>Member Equipment Details Updated.</B>";			

//			finished ($caption);		// wrap it up
		}							// end if ($_getaddtpack == 'true')		

		if ($_get_addveh  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
//			dump($_POST);
			$frequency = (isset($_POST['frm_selector'])) ? $_POST['frm_selector'] : "";
			
			$start = (isset($_POST['frm_start'])) ? $_POST['frm_start'] : "";
			$end = (isset($_POST['frm_end'])) ? $_POST['frm_end'] : "";		
			$days = (isset($_POST['frm_days'])) ? $_POST['frm_days'] : "";	
			
			$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}allocations` (
				`member_id`, `skill_type`, `skill_id`, `frequency`, `start`, `end`, `days`, `_on` )
				VALUES (?,?,?,?,?,?,?,?)";

			$result = db_query($query, [
					trim($_POST['frm_id']),
					4,
					trim($_POST['frm_skill']),
					trim($frequency),
					trim($start),
					trim($end),
					trim($days),
					trim($now)
				]);

			$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nVehicle - " . get_its_name($_POST['frm_skill'], 'regno', 'vehicles') . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}			
			
			$caption = "<B>Member Vehicle Details Updated.</B>";

//			finished ($caption);		// wrap it up
		}							// end if ($_get_addveh == 'true')	
		
		if ($_get_addfile  == 'true') {	
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			$owner = get_member_name($_POST['frm_id']);
			if (isset($_FILES['frm_file'])) {
				$base_uploads = "./mdb_files/";
				chmod ($base_uploads, 0777);
				$file_arr = explode(".", $_FILES['frm_file']['name']);			
				$extension = end($file_arr);
				$upload_directory = "./mdb_files/" . $_POST['frm_id'] . "/";
				if (!(file_exists($upload_directory))) {				
					mkdir ($upload_directory, 0777);
					}
				chmod($upload_directory, 0777);	

				$file = $upload_directory . $_POST['frm_id'] . "_" . rand() . "." . $extension;
				$shortname = $_POST['frm_shortname'];
				if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
					if (safe_strlen(filesize($file)) < 40000000) {
						$filename = $file;
						$query = "INSERT INTO `{$GLOBALS['mysql_prefix']}mdb_files` (
							`member_id`, `name`, `shortname`, `description`, `filesize`, `_on` )
							VALUES (?,?,?,?,?,?)";

						$result = db_query($query, [
								trim($_POST['frm_id']),
								trim($filename),
								trim($shortname),
								trim($_POST['frm_descr']),
								trim($_FILES['frm_file']['size']),
								trim($now)
							]);
						$caption = "<B>File \"" . $shortname . "\" has been added to the member file store.</B>";
						} else {
						$flag = "No update done";
						$errmsg = "Attached file is too large!";
						$caption = "<B>Error - " . $flag . ", " . $errmsg . "</B>";
						}
				} else {
					$flag = "No update done";
					$errmsg = "File Not Uploaded";
					$caption = "<B>Error - " . $flag . ", " . $errmsg . "</B>";
				}
			} else {
				$flag = "No update done";
				$errmsg = "Form Error";
				$caption = "<B>Error - " . $flag . ", " . $errmsg . "</B>";
			}
			
			$text_str = "Member " . $owner . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
			$text_str .= ". \n\nFile - " . $shortname . " has been added.";	
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}			

//			finished ($caption);		// wrap it up
		}							// end if ($_get_addfile == 'true')			

// edit allocations
		
		if ($_get_edittpack  == 'true') {
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `id`=?";
				$result = db_query($query, [intval($_POST['frm_all_id'])]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nTraining Package - " . get_its_name($_POST['frm_skill'], 'package_name', 'training_packages') . " has been removed.";					
				
				$caption = "<B>Member Training has been deleted from database.</B>";
				} else {
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
				$expires = $_POST['frm_expires'];
				$frm_completed = "$_POST[frm_year_completed]-$_POST[frm_month_completed]-$_POST[frm_day_completed]";
				$frm_refresh = ($expires == "Yes") ? "$_POST[frm_year_refresh]-$_POST[frm_month_refresh]-$_POST[frm_day_refresh]" : "";

				$query = "UPDATE `{$GLOBALS['mysql_prefix']}allocations` SET
					`member_id`=?, `skill_id`=?, `frequency`=?, `completed`=?, `refresh_due`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_id']),
					trim($_POST['frm_skill']),
					trim($expires),
					trim($frm_completed),
					trim($frm_refresh),
					trim($now),
					intval($_POST['frm_all_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nTraining Package - " . get_its_name($_POST['frm_skill'], 'package_name', 'training_packages') . " has been changed.";	
				
				$caption = "<B>Member Training Details Updated.</B>";
			}

			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}				
			
//			finished ($caption);		// wrap it up
		}							// end if ($_getedittpack == 'true')
			
		if ($_get_editevent  == 'true') {
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `id`=?";
				$result = db_query($query, [intval($_POST['frm_all_id'])]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nEvent - " . get_its_name($_POST['frm_skill'], 'event_name', 'events') . " has been removed.";					
				
				$caption = "<B>Member Event Attendance has been deleted from database.</B>";
				} else {
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
				$start = "$_POST[frm_year_start]-$_POST[frm_month_start]-$_POST[frm_day_start]";
				$end = "$_POST[frm_year_end]-$_POST[frm_month_end]-$_POST[frm_day_end]";
				$query = "UPDATE `{$GLOBALS['mysql_prefix']}allocations` SET
					`member_id`=?, `skill_id`=?, `start`=?, `end`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_id']),
					trim($_POST['frm_skill']),
					trim($start),
					trim($end),
					trim($now),
					intval($_POST['frm_all_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nEvent - " . get_its_name($_POST['frm_skill'], 'event_name', 'events') . " has been changed.";	
				
				$caption = "<B>Member Event Attendance Details Updated.</B>";
			}

			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}				
			
//			finished ($caption);		// wrap it up
		}							// end if ($_get_editevent == 'true')
		
		if ($_get_editcloth  == 'true') {	
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `id`=?";
				$result = db_query($query, [intval($_POST['frm_all_id'])]);
				
				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nClothing Item - " . get_its_name($_POST['frm_skill'], 'clothing_item', 'clothing_types') . " has been removed.";					
				
				$caption = "<B>Member clothing record has been deleted from database.</B>";
				} else {		
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
				
				$query = "UPDATE `{$GLOBALS['mysql_prefix']}allocations` SET
					`member_id`=?, `skill_id`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_id']),
					trim($_POST['frm_skill']),
					trim($now),
					intval($_POST['frm_all_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nClothing Item - " . get_its_name($_POST['frm_skill'], 'clothing_item', 'clothing_types') . " has been changed.";	
				
				$caption = "<B>Member Clothing Details Updated.</B>";
			}
			
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}				

//			finished ($caption);		// wrap it up
		}							// end if ($_geteditcloth == 'true')			

		if ($_get_editcapab  == 'true') {	
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `id`=?";
				$result = db_query($query, [intval($_POST['frm_all_id'])]);
				
				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nCapability - " . get_its_name($_POST['frm_skill'], 'name', 'capability_types') . " has been removed.";					
				
				$caption = "<B>Member capabilities record has been deleted from database.</B>";
				} else {
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
				
				$query = "UPDATE `{$GLOBALS['mysql_prefix']}allocations` SET
					`member_id`=?, `skill_id`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_id']),
					trim($_POST['frm_skill']),
					trim($now),
					intval($_POST['frm_all_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nCapability - " . get_its_name($_POST['frm_skill'], 'name', 'capability_types') . " has been changed.";	
				
				$caption = "<B>Member Capabilities Updated.</B>";
			}

			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}				
			
//			finished ($caption);		// wrap it up
		}							// end if ($_geteditcapab == 'true')	

		if ($_get_editequip  == 'true') {
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `id`=?";
				$result = db_query($query, [intval($_POST['frm_all_id'])]);
				
				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nEquipment Item - " . get_its_name($_POST['frm_skill'], 'equipment_name', 'equipment_types') . " has been removed.";					
				
				$caption = "<B>Member equipment record has been deleted from database.</B>";
				} else {		
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
				
				$query = "UPDATE `{$GLOBALS['mysql_prefix']}allocations` SET
					`member_id`=?, `skill_id`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_id']),
					trim($_POST['frm_skill']),
					trim($now),
					intval($_POST['frm_all_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nEquipment Item - " . get_its_name($_POST['frm_skill'], 'equipment_name', 'equipment_types') . " has been changed.";		
				
				$caption = "<B>Member Equipment Details Updated.</B>";
			}

			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}					
			
//			finished ($caption);		// wrap it up
		}							// end if ($_geteditequip == 'true')		

		if ($_get_editveh  == 'true') {	
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$query = "DELETE FROM {$GLOBALS['mysql_prefix']}allocations WHERE `id`=?";
				$result = db_query($query, [intval($_POST['frm_all_id'])]);
				
				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nVehicle - " . get_its_name($_POST['frm_skill'], 'regno', 'vehicles') . " has been removed.";					
				
				$caption = "<B>Member Vehicle record has been deleted from database.</B>";
				} else {

				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
				$frequency = (isset($_POST['frm_selector'])) ? $_POST['frm_selector'] : "";
				$start = (isset($_POST['frm_start'])) ? $_POST['frm_start'] : "";
				$end = (isset($_POST['frm_end'])) ? $_POST['frm_end'] : "";		
				$days = (isset($_POST['frm_days'])) ? $_POST['frm_days'] : "";				
				$query = "UPDATE `{$GLOBALS['mysql_prefix']}allocations` SET
					`member_id`=?, `skill_id`=?, `frequency`=?, `start`=?, `end`=?, `days`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_id']),
					trim($_POST['frm_skill']),
					trim($frequency),
					trim($start),
					trim($end),
					trim($days),
					trim($now),
					intval($_POST['frm_all_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nVehicle - " . get_its_name($_POST['frm_skill'], 'regno', 'vehicles') . " has been changed.";
			
				$caption = "<B>Member Vehicle Details Updated.</B>";
			}

			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}					
			
//			finished ($caption);		// wrap it up
		}							// end if ($_getedittveh == 'true')			

		if ($_get_editfile  == 'true') {	
			if ((isset($_POST['frm_all_remove'])) && ($_POST['frm_all_remove'] != "")) {
				$base_dir = getcwd();
				$file = $base_dir . "/" . substr($_POST['frm_file_name'], 2);
				if(unlink($file)) {
					$query = "DELETE FROM {$GLOBALS['mysql_prefix']}mdb_files WHERE `id`=?";
					$result = db_query($query, [intval($_POST['frm_file_id'])]);
					
					$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
					$text_str .= ". \n\nFile - " . $_POST['frm_file'] . " " . $_POST['frm_file_name'] . ") has been removed.";						
					
					$caption = "<B>\"" . $_POST['frm_file'] . "\" has been deleted from the member file store.</B>";
					} else {
					$caption = "<B>\"" . $_POST['frm_file'] . "\" could not be deleted from the member file store at this time.</B>";
					}				
				} else {
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

				$query = "UPDATE `{$GLOBALS['mysql_prefix']}mdb_files` SET
					`description`=?, `_on`=?
					WHERE `id`=?";

				$result = db_query($query, [
					trim($_POST['frm_description']),
					trim($now),
					intval($_POST['frm_id'])
				]);

				$text_str = "Member " . get_member_name($_POST['frm_id']) . " Has been modified by user " . get_owner($_SESSION['user_id']) . " on " . $now . "\n\n";
				$text_str .= ". \n\nFile - " . get_its_name($_POST['frm_skill'], 'shortname', 'files') . " has been changed.";		
				
				$caption = "<B>Member Stored File details have been updated.</B>";
			}
			
			$addrs = mdb_notify_user();
			if(is_array($addrs) && !empty($addrs)) {	// 3/14/26 - was $addrs[0] on FALSE
				$addr_arr = implode("|", array_unique($addrs));			
				do_send($addr_arr, "Member Data Changed", $text_str);
				}					

//			finished ($caption);		// wrap it up
		}							// end if ($_getedittveh == 'true')					
// add Capabilities ===========================================================================================================================

	if ($_get_training == 'true') {
		$id=sanitize_int($_GET['id']);
		$disallow = is_user() ;		
		include('./forms/add_training.php');
?>
<?php		
	exit();
		}
		
	if ($_get_event == 'true') {
		$id=sanitize_int($_GET['id']);
		$disallow = is_user() ;	
		include('./forms/add_event.php');
?>
<?php		
	exit();
		}
		
	if ($_get_capability == 'true') {
		$id=sanitize_int($_GET['id']);
		$disallow = is_user() ;			
		include('./forms/add_capability.php');
?>		
<?php
		exit();
		}
		
	if ($_get_clothing == 'true') {
		$id=sanitize_int($_GET['id']);
		$disallow = is_user() ;		
		include('./forms/add_clothing.php');
?>
<?php
		exit();
		}
	if ($_get_equipment == 'true') {
		$id=sanitize_int($_GET['id']);	
		$disallow = is_user() ;		
		include('./forms/add_equipment.php');
?>		
<?php
		exit();
		}
	if ($_get_vehicle == 'true') {
		$id=sanitize_int($_GET['id']);
		$disallow = is_user() ;			
		include('./forms/add_vehicle.php');
?>		
<?php
		exit();
		}
	if ($_get_files == 'true') {
		$id=sanitize_int($_GET['id']);
		$disallow = is_user() ;		
		include('./forms/add_file.php');
?>		
<?php
		exit();
		}
		
// edit Capabilities etc. ===========================================================================================================================

	if ($_get_e_training == 'true') {
		$id=sanitize_int($_GET['mem_id']);
		$disallow = is_user() ;		
		include('./forms/edit_training.php');
?>
<?php		
	exit();
		}
		
	if ($_get_e_event == 'true') {
		$id=sanitize_int($_GET['mem_id']);
		$disallow = is_user() ;		
		include('./forms/edit_event.php');
?>
<?php		
	exit();
		}
		
	if ($_get_e_capability == 'true') {
		$id=sanitize_int($_GET['mem_id']);
		$disallow = is_user() ;		
		include('./forms/edit_capability.php');
?>		
<?php
		exit();
		}
		
	if ($_get_e_clothing == 'true') {
		$id=sanitize_int($_GET['mem_id']);
		$disallow = is_user() ;	
		include('./forms/edit_clothing.php');
?>
<?php
		exit();
		}
	if ($_get_e_equipment == 'true') {
		$id=sanitize_int($_GET['mem_id']);	
		$disallow = is_user() ;	
		include('./forms/edit_equipment.php');
?>		
<?php
		exit();
		}
	if ($_get_e_vehicle == 'true') {
		$id=sanitize_int($_GET['mem_id']);	
		$disallow = is_user() ;	
		include('./forms/edit_vehicle.php');
?>		
<?php
		exit();
		}		
	if ($_get_e_files == 'true') {
		$id=sanitize_int($_GET['mem_id']);	
		$disallow = is_user() ;	
		include('./forms/edit_file.php');
?>		
<?php
		exit();
		}
		
// add member ===========================================================================================================================

	if ($_getadd == 'true') {
		if(can_edit()) {
		$disallow = is_user() ;			
?>
		</HEAD>
		<BODY onLoad = "ck_frames();"> <!-- <?php print __LINE__;?> -->
<?php
		include('./forms/add_form.php');
?>
		<!-- 1100 -->
		</BODY>
		</HTML>
<?php
		exit();
		} else {
		exit();
		}		// end if ($_GET['add'])
	}

// edit member =================================================================================================================

	if (($_getedit == 'true') || ($_getextra == 'edit')) {
		$id = sanitize_int($_REQUEST['id']);	
		if((can_edit()) || (is_team_manager($id)) || (is_curr_member($id))) {
			$query	= "SELECT *, `field17` AS `joindate`,
				`field18` AS `dob`,
				`field16` AS `duedate`,
				`field12` AS `lat`,
				`field13` AS `lng`,
				`_on` AS `updated`
				FROM `{$GLOBALS['mysql_prefix']}member` WHERE `id`=?";
			$result	= db_query($query, [$id]);
			$row	= $result->fetch_array();
			$row['duedate'] = safe_strtotime($row['duedate']);
			$row['joindate'] = !empty($row['joindate']) ? safe_strtotime($row['joindate']) : false;	// 3/14/26 - null-safe
			$row['dob'] = !empty($row['dob']) ? safe_strtotime($row['dob']) : false;
			$row['updated'] = !empty($row['updated']) ? safe_strtotime($row['updated']) : false;
			$lat = (!empty($row['lat'])) ? $row['lat'] : get_variable('def_lat');	// 3/14/26 - fall back to default when NULL
			$lng = (!empty($row['lng'])) ? $row['lng'] : get_variable('def_lng');
			$type_checks = array ("", "", "", "", "");
			$type_checks[$row['field7']] = " checked";
			$disallow = is_user() ;
			$fullname = $row['field2'] . " " . $row['field1'];
?>
			</HEAD>
			<BODY onLoad = "ck_frames();"> <!-- <?php print __LINE__;?> -->
<?php


			include('./forms/edit_form.php');
?>
			</BODY>
			</HTML>
<?php
			exit();
			} else {
			exit();
			}	// end if ($_GET['edit'])
		}

// view member =================================================================================================================
?>
</HEAD>
<?php
	if (($_getview == 'true') || ($_getextra == 'view')) {
		$id = sanitize_int($_GET['id']);
		if((can_view()) || (is_team_manager($id)) || (is_curr_member($id))) {		

			$query	= "SELECT *, `_on` AS `updated`,
				`field12` AS `lat`,
				`field13` AS `lng`,
				`field18` AS `dob`,
				`field17` AS `joindate`,
				`field16` AS `duedate`
				FROM `{$GLOBALS['mysql_prefix']}member` `m`
				WHERE `m`.`id`=? LIMIT 1";
			$result	= db_query($query, [$id]);
			$row	= stripslashes_deep($result->fetch_assoc());
			$row['duedate'] = safe_strtotime($row['duedate']);
			$row['joindate'] = !empty($row['joindate']) ? safe_strtotime($row['joindate']) : false;	// 3/14/26 - null-safe
			$row['dob'] = !empty($row['dob']) ? safe_strtotime($row['dob']) : false;
			$row['updated'] = !empty($row['updated']) ? safe_strtotime($row['updated']) : false;
			$lat = (!empty($row['lat'])) ? $row['lat'] : get_variable('def_lat');	// 3/14/26 - fall back to default when NULL
			$lng = (!empty($row['lng'])) ? $row['lng'] : get_variable('def_lng');
			if (isset($row['field21'])) {
				$query	= "SELECT * FROM `{$GLOBALS['mysql_prefix']}member_status` WHERE `id`=?";	// status value
				$result_st	= db_query($query, [$row['field21']]);
				$row_st	= $result_st->fetch_assoc();
				unset($result_st);
				}
			$un_st_val = (isset($row_st) && $row_st)? $row_st['status_val'] : "?";				// 3/14/26 - null-safe
			$un_st_bg = (isset($row_st) && $row_st && isset($row_st['bg_color']))? $row_st['bg_color'] : "white";		// 3/14/10, 3/14/26
			$un_st_txt = (isset($row_st) && $row_st && isset($row_st['text_color']))? $row_st['text_color'] : "black";	// 3/14/26
			$type_checks = array ("", "", "", "", "", "");
			if (isset($row['field7']) && $row['field7'] < count($type_checks)) { $type_checks[$row['field7']] = " checked"; }	// 3/14/26 - bounds check

			$fullname = $row['field2'] . " " . $row['field1'];

			print "\t<BODY onLoad = 'ck_frames();'>\n";

			$temp = isset($u_types[$row['field7']]) ? $u_types[$row['field7']] : null;	// 3/14/26 - null-safe when no member types configured
			$the_type = ($temp !== null) ? $temp[0] : "";			// name of type
			$fullname = $row['field2'] . " " . $row['field1'];
			$disallow = true ;

			include('./forms/view_form.php');
?>
							<!-- END UNIT VIEW -->
<?php
?>
			<!-- 1408 -->
			</BODY>
			</HTML>
<?php
			exit();
			} else {
			exit();
			}	// end if ($_GET['view'])
		}

// ============================================= initial display =======================

?>
		</HEAD>
<?php
		if(isset($_POST['caption'])) {
			$caption = $_POST['caption'];
			} else {
			$caption = "";
			}
?>
		<BODY>
		<A NAME='top'>
		<DIV ID='outer' style='position: absolute; left: 1%; width: 100%;'>
			<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
				<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><?php print $caption;?></SPAN><b>Member List</b></SPAN>
			</DIV>
			<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
				<DIV id="memberssheading" class='heading' style='border: 1px outset #707070; padding-top: 3px; padding-bottom: 3px;'>
					<DIV CLASS='tablehead text_biggest' style='text-align: center; display: inline-block; width: 100%;'><?php print get_text('Member');?> List
						<SPAN id='reload_members' class='plain text' style='width: 19px; height: 19px; float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover(this.id); Title="Click to refresh Responder List";' onmouseout='do_plain(this.id);' onClick="load_memberlist(memb_field, memb_direct);"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
					</DIV>
					<SPAN class='text_medium text_center text_italic' style='color: #FFFFFF; width: 100%; display: block;' id='caption'>click on item to view / edit, Click headers to sort</SPAN>
				</DIV>		
				<DIV class="scrollableContainer" id='memberlist' style='width: 100%; border: 1px outset #707070;'>
					<DIV class="scrollingArea" id='the_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
				</DIV>
				<BR />
				<BR />
				<BR />
				<DIV STYLE='text-align: center; border: 1px outset #707070;'>
					<DIV class='tablehead text_biggest' style='text-align: center;'>Membership Database Summary</DIV><BR />
					<DIV class='info_head text_large'>Member Types Configured</DIV>
					<DIV class='info text' id='f1'></DIV>
					<BR />
					<DIV class='info_head text_large'>Member Status Types Configured</DIV>		
					<DIV class='info text' id='f2'></DIV>
					<BR />
					<DIV class='info_head text_large'>Member Summary</DIV>			
					<DIV class='info text' id='f3'></DIV>
					<BR />
					<DIV STYLE='text-align: center; border: 1px outset #707070;'>
						<DIV class='info_head text_large'>Due Date alerts</DIV>			
						<DIV class='info text' id='f4' style='display: block; max-height: 300px; overflow-y: scroll;'></DIV>
					</DIV>
				</DIV>
			</DIV>
			<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
				<DIV style='position: fixed; top: 50px; z-index: 1;'>
					<DIV id='srch_button' class = 'plain_centerbuttons text' style='cursor: default; width: 80px; display: block; float: none;' onmouseover='do_hover_centerbuttons(this.id);' onmouseout='do_plain_centerbuttons(this.id);' onClick = "document.srch_Form.submit();"><?php print get_text('Search');?><BR /><IMG src="./images/search_small.png"/></DIV><BR />
					<DIV id='rpts_button' class = 'plain_centerbuttons text' style='cursor: default; width: 80px; display: block; float: none;' onmouseover='do_hover_centerbuttons(this.id);' onmouseout='do_plain_centerbuttons(this.id);' onClick = "document.rpts_Form.submit();"><?php print get_text('Reports');?><BR /><IMG src="./images/reports.png"/></DIV><BR />
<?php
					if(can_edit()) {
?>
						<DIV id='addmember_button' class = 'plain_centerbuttons text' style='cursor: default; width: 80px; display: block; float: none;' onmouseover='do_hover_centerbuttons(this.id);' onmouseout='do_plain_centerbuttons(this.id);' onClick = "document.add_Form.submit();"><?php print get_text('Add Member');?><BR /><IMG src="./images/add.png"/></DIV><BR />	
						<DIV id='mail_button' class = 'plain_centerbuttons text' style='cursor: default; width: 80px; display: block; float: none;' onmouseover='do_hover_centerbuttons(this.id);' onmouseout='do_plain_centerbuttons(this.id);' onClick = "do_member_mail_win();"><?php print get_text('Email Members');?><BR /><IMG src="./images/email.png"/></DIV><BR />
<?php
						}
?>
				</DIV>
			</DIV>
			<DIV id='right_col' style='position: absolute; right: 2%; top: 60px;'>	
				<DIV id = 'map_canvas' style = 'border: 1px outset #707070;'></DIV>			
			</DIV>
		</DIV>
		<SCRIPT>

		//	setup map-----------------------------------//
		var map;
		var minimap;
		var sortby = '`date`';
		var sort = "DESC";
		var thelevel = '<?php print $the_level;?>';
		var markers = [];			//	Incident markers array
		var latLng;
		// set widths
		var viewportwidth, viewportheight, outerwidth, outerheight, mapWidth, mapHeight, colwidth, rightcolwidth, leftcolwidth;
		if (typeof window.innerWidth != 'undefined') {
			viewportwidth = window.innerWidth,
			viewportheight = window.innerHeight
			} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
			viewportwidth = document.documentElement.clientWidth,
			viewportheight = document.documentElement.clientHeight
			} else {
			viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
			viewportheight = document.getElementsByTagName('body')[0].clientHeight
			}
		set_fontsizes(viewportwidth, "fullscreen");
		mapWidth = viewportwidth * .30;
		mapHeight = viewportheight * .55;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		colwidth = outerwidth * .42;
		leftcolwidth = viewportwidth * .55;
		rightcolwidth = viewportwidth * .30;
		colheight = outerheight * .95;
		listHeight = viewportheight * .8;
		listwidth = colwidth * .99;
		leftlistwidth = leftcolwidth * .99;
		rightlistwidth = rightcolwidth * .99;
		inner_listwidth = leftlistwidth *.98;
		celwidth = listwidth * .20;
		res_celwidth = listwidth * .15;
		fac_celwidth = listwidth * .15;
		if($('outer')) {$('outer').style.width = outerwidth + "px";}
		if($('outer')) {$('outer').style.height = outerheight + "px";}
		if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
		if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
		if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
		if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
		if($('list')) {$('list').style.width = leftcolwidth + "px";}
		if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
		if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
		if($('memberssheading')) {$('memberssheading').style.width = leftcolwidth + "px";}
		if($('memberlist')) {$('memberlist').style.width = leftcolwidth + "px";}
		if($('the_list')) {$('the_list').style.width = leftcolwidth + "px";}
		// end of set widths
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = 0;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
		var bounds = map.getBounds();
		var mapCenter = map.getCenter();	
		var zoom = map.getZoom();
		var got_points = false;
		load_memberlist(memb_field, memb_direct);
		pop_summary(); 
		</SCRIPT>
		<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='member'>
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>
		<FORM NAME='toedit_Form' METHOD='post' ACTION=''>			
		</FORM>
		<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='member'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>
		<FORM NAME='srch_Form' METHOD='get' ACTION='mdb_search.php'>
		</FORM>
		<FORM NAME='rpts_Form' METHOD='get' ACTION='mdb_reports.php'>
		</FORM>
		<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member"></FORM>
		<FORM NAME='tables' METHOD = 'post' ACTION='tables.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='r'>
		<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
		</FORM>
		<FORM NAME='toTables' METHOD='post' ACTION='tables.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='v'>
		<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>	
		<FORM NAME='go_Form' METHOD="post" ACTION = ""></FORM>
		<!-- 1452 -->
		</BODY>				<!-- END MEMBER LIST and ADD -->
<?php

		print "\n</HTML> \n";
		exit();
?>			



