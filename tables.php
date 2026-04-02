<?php
/**
 * Generic database table editor — CRUD interface for auxiliary data tables.
 *
 * Provides listing, viewing, adding, editing, and deleting records in any
 * database table that has a corresponding definition file in the tables/
 * directory. Handles foreign key relationships (with configurable display
 * overrides), enumerated types, date/time formatting, file uploads, and
 * referential integrity checks before deletion.
 *
 * @package TicketsCAD
 * @since   v3.0
 */
// Made available under the terms of GNU General Public License (GPL) http://www.gnu.org/copyleft/gpl.html
/*
Released Jul 23, 2006
Enumerated types added Jul 20
Dynarch JS Calendar functions added
improvements to datatype 'time' handling
9/5/08 corrections to max length
9/18/08 changes to $_POST handling, frame jump prevention and login check
12/20/08 named fields as hiddens
12/26/08 set tables directory as repository for custom processors
12/29/08 icon revised to img filename - superceded 1/4/09
1/5/09 aprs added to unit_types schema
1/29/09 corrected if(...)
7/7/09	revised textarea limit criterion, added Script-date meta
8/20/09	handle prefixes correctly
10/6/09 Added Facilities icons handling
10/10/09 quotes corrected
10/13/09 referential integrity checks added
10/20/09 disallow edit/delete unit status = 'available'
11/2/09 correction to 10/20/09 entry
11/8/09 ereg_replace() deprecated and replaced
2/8/10 PHP parseInt added, plus c_un_status.php, u_un_status.php
2/25/10 'disallow' made inactive
6/21/10 NULL vs empty for real types when empty, fnQuote_Smart to case 'pu', length and maxlength corrections
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/5/10 fix to session_start();
9/12/10 's' => 'l'
9/15/10 added to test for dd list type, td onClick call added
9/16/10 get_comments() added
9/19/10 do_onload() added
10/26/10 _by => _userid - to pick up table 'user', correction to option list build
10/31/10 revised sql in get_comments(), handle un-set search_str, added check/un-check all
11/9/10 function is_in_use() added
11/20/10 corrected 'push' sgl-quote handling
12/15/10 accommodate comments problem
3/15/11 changed default.css to stylesheet.php
3/18/11 revised to correct error if $_POST['srch_str'] does not exist
6/10/11 Added Regions
12/12/11 - special case table user added
1/6/2013 - security measures added
*/
$gmap=TRUE;

session_start();
session_write_close();
if (empty($_SESSION)) {				// 1/6/2013
	header("Location: index.php");
	}

require_once('./incs/functions.inc.php');
$query = "SET @@session.sql_mode= '';";		// 6/25/10
$result = db_query($query);

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
$istest=FALSE;
if ($istest) {
	dump($_POST);
	}
do_login(basename(__FILE__));	// 9/18/08

// 3/14/26 - CSRF validation for POST requests that include a token
// Forms served by this page have tokens auto-injected via JS; cross-page navigation POSTs don't
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
	if (!csrf_verify($_POST['csrf_token'])) {
		print '<br><br><center><b>Security Error:</b> Your session has expired or the form token is invalid. Please <a href="' . e(basename(__FILE__)) . '">go back</a> and try again.</center>';
		exit();
	}
}

// 3/14/26 - Output buffering to auto-inject CSRF tokens into all forms
// Appends script at end of output (browsers execute it even after </HTML>)
ob_start(function($html) {
	$token = csrf_token();
	$script = "\n" . '<script>(function(){var t=\'' . addslashes($token) . '\';document.querySelectorAll(\'form\').forEach(function(f){if(!f.querySelector(\'input[name="csrf_token"]\')){var i=document.createElement(\'input\');i.type=\'hidden\';i.name=\'csrf_token\';i.value=t;f.appendChild(i);}});})();</script>';
	return $html . $script;
});

if(is_administrator() || (get_variable('oper_can_edit') == "1")) {

$key_str			= "_id";			// FOREIGN KEY (parent_id) REFERENCES parent(id) relationship terminal string identifier

/* cosmetic stuff from here - MAY  be changed */

$irving_title		= "Tables maintenence";
$rowsPerPage		= 20;				// determines number of rows displayed per page in listing
$showblobastext		= TRUE;				// change to FALSE if blobs are not to be displayed
$date_out_format	= 'Y-m-d H:i';		// well, date format - per php date syntax
//$date_out_format	= 'n/j/y H:i';		// ex: 5/25/06
$date_in_format		= 0;					// yyyy-mm-dd, per MySQL standard
$links_col			= 0;				// in the listing display, this column sees the View/Edit/Delete function links
$text_type_max		= 128;				// text input fields exceeding this size limit will be treated as <textarea> 7/7/09
$text_list_max		= 32;				// text input fields exceeding this size limit will be treated as <textarea>
$fill_from_last		= FALSE;			// if set to TRUE, new recrods are populated from last created
$doUTM				= FALSE;			// if set, coord displays UTM
$istest 			= FALSE;			// TRUE displays form variables for trouble-shooting atop each loaded page

/* maps irv_settings for use IF you are implementing maps */

$maps 				= TRUE;
$api_key			= getenv('TICKETS_MAPS_API_KEY') ?: ""; // NOSONAR - user-configurable Google Maps API key, set via environment variable or replace this value

$def_state			= "10";				// Florida
$def_county			= "58";				// Sarasota
$def_lat			= NULL;				// default center lattitude - if present, overrides county centroid
$def_lon			= NULL;				// guess!
$radius				= 10;				// radius of circle on default center (miles)
$do_hints			= TRUE;				// if true, print data hints at input fields
if (($mysql_db=="")||($mysql_user=="")) {print "<br><br><br><br>" ; die(" - - - - - - - - - -  - - - - - - - - - - Please set values for both \$mysql_db and \$mysql_user in settings.inc.php! - - - - - - - - - - ");}

$FK_id = strtolower($key_str);			// set for case independence
$id_lg = safe_strlen($FK_id);				// lgth of foreign key id string
$custom	= FALSE;						// custom processor in use

// 3/14/26 - FK overrides for columns that don't follow the _id naming convention
// display_expr: SQL expression for display value; display_alias: alias for the expression
$fk_overrides = [
	'owner' => ['table' => 'member', 'display_expr' => "CONCAT(`id`, ' - ', `field2`, ' ', `field1`)", 'display_alias' => 'display_name'],
];
$can_edit = ((is_super()) || (is_administrator()) || (get_variable('oper_can_edit') == "1"));										// 3/19/11

// Replaced extract — explicit variable assignments (Phase 2 cleanup)
$func      = $_POST['func']      ?? 'l';         // CRUD function: l=list, v=view, a=add, e=edit, d=delete, s=select
$tablename = isset($_POST['tablename']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['tablename']) : '';  // sanitize table name — alphanumeric + underscores only
$indexname = isset($_POST['indexname']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['indexname']) : '';   // sanitize index name
$id        = $_POST['id']        ?? '';           // record ID for view/edit/delete
$page      = isset($_POST['page'])    ? intval($_POST['page'])    : 0;  // pagination: current page number
$numrows   = isset($_POST['numrows']) ? intval($_POST['numrows']) : 0;  // pagination: total row count
$srch_str  = $_POST['srch_str']  ?? '';           // search filter string (pipe-delimited)
$sortby    = isset($_POST['sortby']) && !empty($_POST['sortby']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['sortby']) : 'id';   // sanitize sort column
$sortdir   = isset($_POST['sortdir']) ? intval($_POST['sortdir']) : 0;  // sort direction (0=ASC, 1=DESC)
//$sortby = (!(isset($index)) || empty($index))?			 "id" : $index;
function get_comments($the_table) {  				// returns array key=> name, value=> comment - 10/31/10
	$_array = array();								// 12/15/10
	$query = "SHOW FULL COLUMNS FROM `{$GLOBALS['mysql_prefix']}{$the_table}`;";
	$result = db_query($query);			// use $result for meta-information reference
	if (!($result)) {return $_array;}		// 12/15/10
	while($row = stripslashes_deep($result->fetch_assoc())) {		//
		$_array[$row['Field']] = $row['Comment'];
		}
	return $_array;
	}				// end function

function is_in_use($index_val) {	// 11/9/10 - return boolean based on whether the identified entry is in use
	global $tablename, $mysql_prefix;
	$index_val = intval($index_val);
	switch ($tablename) {
		case "unit_types":
			$the_table = $mysql_prefix . "responder";
			$query ="SELECT * FROM `$the_table` WHERE `type` = ? LIMIT 1";						// get in_row count only
			$res_test = db_query($query, [$index_val]);
			$in_use = ($res_test->num_rows>0);
		    break;
		case "un_status":
			$the_table = $mysql_prefix . "responder";
			$query ="SELECT * FROM `$the_table` WHERE `un_status_id` = ? LIMIT 1";						// get in_row count only
			$res_test = db_query($query, [$index_val]);
			$in_use = (($res_test->num_rows>0) || (intval ($index_val)==1));	// 11/2/09
		    break;
		case "fac_status":
			$the_table = $mysql_prefix . "facilities";
			$query ="SELECT * FROM `$the_table` WHERE `status_id` = ? LIMIT 1";						// get in_row count only
			$res_test = db_query($query, [$index_val]);
			$in_use = ($res_test->num_rows>0);
		    break;
		case "fac_types":
			$the_table = $mysql_prefix . "facilities";
			$query ="SELECT * FROM `$the_table` WHERE `type` = ? LIMIT 1";						// get in_row count only
			$res_test = db_query($query, [$index_val]);
			$in_use = ($res_test->num_rows>0);
		    break;
		case "in_types":						//
			$the_table = $mysql_prefix . "ticket";
			$query ="SELECT * FROM `$the_table` WHERE `in_types_id` = ? LIMIT 1";						// get in_row count only
			$res_test = db_query($query, [$index_val]);
			$in_use = ($res_test->num_rows>0);
		    break;
		case "region":						// 6/10/11
			$the_table = $mysql_prefix . "allocates";
			$query ="SELECT * FROM `$the_table` WHERE `group` = ? LIMIT 1";						// get in_row count only
			$res_test = db_query($query, [$index_val]);
			$in_use = ($res_test->num_rows>0);
		    break;
		default:
			$in_use = FALSE;
		}				// end switch
//	dump($in_use);
	return $in_use;
	}				// end function  is_in_use()

$evenodd = array ("even", "odd");	// for table row colors
$hints = array("int"=>"numeric", "blob"=>"blob", "string"=>"text", "datetime"=>"date/time", "time"=>"time", "timestamp"=>"date/time", "date"=>"date", "real"=>"float'g pt.");
$primaries = array();				// table names
$secondaries = array();				// table names
$arDate_formats = array(array ("-",0, 1, 2), array ("/", 2, 0, 1));
$disallow = FALSE;										// 2/25/10
if ((isset($tablename)) && (empty($indexname))) {
	$query ="SELECT * FROM `$mysql_prefix$tablename` LIMIT 1";
	$result = db_query($query);
	$num_fields = $result->field_count;
	for($i = 0; $i < $num_fields; $i++) {
		$finfo = $result->fetch_field_direct($i);
		if($finfo->flags & MYSQLI_PRI_KEY_FLAG) {
			$indexname = $finfo->name;
			unset ($result);
			break;
			}
		}
	unset($result);
	}

function fnQuote_Smart($value) {    // Stripslashes
//    if (@ get_magic_quotes_gpc()) {
//        $value = stripslashes($value);
//    	}
    if (!is_numeric($value)) {    // Quote if not integer
        $value = "'" . db()->real_escape_string($value) . "'";
	    }
    return $value;
	}

function get_digs($str_in) {		// returns extracted digits
	$allowed = "/[^\d]/";
	return preg_replace($allowed,"",$str_in);
	}

function myerror($script,$line,$custom_err='', $query = '')	{	/* raise an error event */
	print "<BR><FONT CLASS=\"warn\">Error in '<B>$script</B>', line '<B>$line</B>'</FONT><BR>";
	if ($custom_err != '') 	print "Additional info: '<B>$custom_err</B>'<BR>";
	if ($query != '') 	print "Query: '<B>$query</B>'<BR>";
	print '<BR>Please contact the <A HREF="help.php?q=credits">author</A> with these details.<BR>';
	die('<B>Execution stopped.</B></FONT>');
	}
function get_file($mystr) {								// returns extracted script name
	if (!$temp=(strrchr ($mystr, "/"))) {$temp=(strrchr ( $mystr, "\\"));} 		// win32 or *nix?
	return $temp;
	}

function mysql_field_type_compat($result, $i) {
	$field = mysqli_fetch_field_direct($result, $i);
	$type_map = [
		MYSQLI_TYPE_TINY => 'int', MYSQLI_TYPE_SHORT => 'int', MYSQLI_TYPE_LONG => 'int',
		MYSQLI_TYPE_INT24 => 'int', MYSQLI_TYPE_LONGLONG => 'int',
		MYSQLI_TYPE_FLOAT => 'real', MYSQLI_TYPE_DOUBLE => 'real', MYSQLI_TYPE_DECIMAL => 'real',
		MYSQLI_TYPE_NEWDECIMAL => 'real',
		MYSQLI_TYPE_VAR_STRING => 'string', MYSQLI_TYPE_STRING => 'string',
		MYSQLI_TYPE_BLOB => 'blob', MYSQLI_TYPE_TINY_BLOB => 'blob',
		MYSQLI_TYPE_MEDIUM_BLOB => 'blob', MYSQLI_TYPE_LONG_BLOB => 'blob',
		MYSQLI_TYPE_DATE => 'date', MYSQLI_TYPE_DATETIME => 'datetime',
		MYSQLI_TYPE_TIMESTAMP => 'timestamp', MYSQLI_TYPE_TIME => 'time',
		MYSQLI_TYPE_YEAR => 'year', MYSQLI_TYPE_ENUM => 'string', MYSQLI_TYPE_SET => 'string',
	];
	return $type_map[$field->type] ?? 'unknown';
}

function fnDatabaseExists($dbName) {					//Verifies existence of a MySQL database
	global $mysql_host, $mysql_user, $mysql_passwd;
	$bRetVal = FALSE;
	if ($oConn = @mysqli_connect($mysql_host, $mysql_user, $mysql_passwd)) {
		$result = mysqli_query($oConn, 'SHOW DATABASES');
		while ($row = $result->fetch_array(MYSQLI_NUM)) {
			if ($row[0] == $dbName)
				$bRetVal = TRUE;
		}
		mysqli_free_result($result);
	}
	return ($bRetVal);
}

function fnSubTableExists($TableName) {					// returns name of substitution table, or FALSE
	global $id_lg, $primaries, $secondaries ;
	$thename = substr( $TableName, 0, safe_strlen($TableName)-$id_lg);				// high-order portion possible base name?
	if ((in_array($thename, $primaries)) || (in_array ($thename, $secondaries))) {
		return $thename;
		}
	else 																		{
		return FALSE;}
	}

function fnDoCal($id) {
	global $calstuff;
	$calstuff .= "Calendar.setup({";
	$calstuff .= "inputField: \"fd$id\", ";
	$calstuff .= "ifFormat: \"%Y-%M-%e %I:%M\", ";
	$calstuff .= "button: \"ft$id\", ";
	$calstuff .= "align: \"Tl\", ";
	$calstuff .= "singleClick: true";
	$calstuff .= "});";
	} 					// end  fnDoCal()

function fnCalButt ($id) {									// displays the calendar gif button
	print "<img src='./markers/img.gif' id='ft$id' style='cursor: pointer; border: 1px solid red;' title='Date selector'";
    print " onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
	}

$calstuff="";						// JS calendar string gets built here

$ctrp = $ctrs = 0;
$sql = "SHOW TABLES ";													// populate array of table names
$result = db_query($sql);	// $mysql_db
while ($row = $result->fetch_row()) {
	$sql ="SELECT * FROM `$row[0]` LIMIT 1";
	$result2 = db_query($sql);	// $mysql_db
	$row2 = $result2->fetch_array();
	$gotit = FALSE;
	for ($i = 0; $i < $result2->field_count; $i++) {			// look at each field
		if (strtolower(substr(mysqli_fetch_field_direct($result2, $i)->name, -$id_lg)) == $FK_id) {	// find any foreign key
			$primaries[$ctrp] = $row[0];							// a primary
			$ctrp++;
			$gotit = TRUE;
			break;
			}
		}
	if (!$gotit) {														// not a primary
		$secondaries[$ctrs] = $row[0];
		$ctrs++;
		}
	}
unset ($result);
unset ($result2);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Single - A Generic MySQL Table Processor</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"/>
<META HTTP-EQUIV="Expires" CONTENT="0"/>
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE"/>
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE"/>
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript"/>
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<!--  onFocus="LL_showinfo(1)" onBlur="LL_hideallinfo()" -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="./js/Control.Geocoder.css" />
<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
<STYLE>
/* comment */
	TABLE {font-size: 0.8vw;}
A:hover 					{text-decoration: underline; color: red;}
TH:hover 					{text-decoration: underline; color: red;}
td.mylink:hover 			{background-color: rgb(255, 255, 255); }
.clean						{color:silver;}
.dirty						{color:black;}
INPUT.button 				{background-color: rgb(255, 255, 255); }
input.text:focus, textarea:focus	{background-color: lightyellow; color:black;}
/* input:blur, textarea:blur	{background-color: white; color:black;} */

</STYLE>
<?php
if (($func == "c")||($func == "u")) {			// not required for all functions
//	print "<SCRIPT type=\"application/x-javascript\" src=\"RegExpValidate.js\"></SCRIPT>";
	}
?>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT>
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
<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<script type="application/x-javascript" src="./js/usng.js"></script>
<script type="application/x-javascript" src="./js/osgb.js"></script>
<script type="application/x-javascript" src="./js/geotools2.js"></script>
<SCRIPT>
	var viewportwidth;
	var viewportheight;
	var outerwidth;
	var outerheight;
	var tablewidth;
	window.onresize=function(){set_size();}
</SCRIPT>
</HEAD>
<?php
	require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
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
		tablewidth = viewportwidth * .8;
		if($('listView')) {$('listView').style.width = tablewidth + "px";}
		}

	if(self.location.href==parent.location.href) {				// 1/6/2013
		self.location.href = 'index.php';
		}

	Array.prototype.inArray = function (value) {	// Returns true if argument value exists in array, else false - 12/31/08
		for (i=0; i < this.length; i++) {
			if (this[i] == value) {	return true;}
			}
		return false;
		};
	function JSfnTrim(argvalue) {					// drops leading and trailing spaces and cr's
		var tmpstr = ltrim(argvalue);
		return rtrim(tmpstr);
			function ltrim(argvalue) {
				while (1) {
					if ((argvalue.substring(0, 1) != " ") && (argvalue.substring(0, 1) != "\n"))
						break;
					argvalue = argvalue.substring(1, argvalue.length);
					}
				return argvalue;
				}								// end function ltrim()
			function rtrim(argvalue) {
				while (1) {
					if ((argvalue.substring(argvalue.length - 1, argvalue.length) != " ") && (argvalue.substring(argvalue.length - 1, argvalue.length) != "\n"))
						break;
					argvalue = argvalue.substring(0, argvalue.length - 1);
					}
				return argvalue;
			}									// end rtrim()
		}										// end JSfnTrim()

<?php
	switch ($tablename) {
		case "unit_types":
?>

	var sm_icons = new Array();
	var icons = new Array();
	var type_names = new Array();
	var icons_dir = "./our_icons/";

<?php
	$icons = $GLOBALS['icons'];
	for ($i=0; $i<count($icons); $i++) {										// onto JS array
		print "\ticons.push(\"{$icons[$i]}\");\n";								// 11/20/10
		}

	$sm_icons = $GLOBALS['sm_icons'];
	for ($i=0; $i<count($sm_icons); $i++) {
		print "\tsm_icons.push(\"{$sm_icons[$i]}\");\n";								// 11/20/10
		}
	if (($func =="c") || ($func =="r")) {										// build array of existing names
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}unit_types`";
		$result = db_query($query);
		while ($row_un = stripslashes_deep($result->fetch_assoc())) {
			print "\n\ttype_names.push(\"{$row_un['name']}\");\n";		// onto JS array - 11/20/10
			}				// end while ()
		}				// end if()
?>
	function validate_u_t(theForm) {			// unit type entry validation - c and u
		var errmsg="";
		if (theForm.frm_name.value == "")				{errmsg+= "\tType name is required\n";}
		if (theForm.frm_description.value == "")		{errmsg+= "\tType description is required\n" ;}
		if (theForm.frm_icon.value == "")				{errmsg+= "\tIcon selection is required\n" ;}
<?php
	if ($func =="c")  {										//check existence
?>
		if (type_names.inArray(theForm.frm_name.value))	{errmsg+= "\tDuplicated Type name\n";}
<?php
		}			// end if ($func =="c")
?>
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function validate_u_t(theForm)
														// 12/29/08 'which' is now a string, e.g, 'red.png'
	function icon_to_form(the_index) {						// 12/31/08
		var the_img = $('ID3');
		the_img.src = icons_dir+sm_icons[the_index];		// display small icon
		document.forms[1].frm_icon.value=the_index;			// icon index to form variable
		$('ID3').style.visibility = "visible";				// initially hidden for 'create'
		return;
		}

	function gen_img_str(the_index) {						// returns image string for nth icon
		var the_sm_image = icons_dir + sm_icons[the_index];
//		alert(the_sm_image);
		var the_title = icons[the_index].substr (0, icons[the_index].length-4).toUpperCase();	// extract color name
		return "<IMG SRC='" + the_sm_image + "' onClick  = 'icon_to_form(" + the_index + ")' TITLE='" + the_title +"' />";
		}
<?php
		if ($row) {
?>
			var which = "<?php print $row['icon'];?>";	// full size icon
			icon_to_form(which);						// must be update or view
<?php
			}				// end if (isset($row))

	    break;			// end case "unit_types"
		case "fac_types":
?>

	var sm_icons = new Array();
	var icons = new Array();
	var type_names = new Array();
	var icons_dir = "./our_icons/";

<?php
// Adds capabilities for Facilities Icons 10/6/09-----------------------------------------------

	$icons = $GLOBALS['fac_icons'];
	for ($i=0; $i<count($icons); $i++) {										// onto JS array
		print "\ticons.push(\"{$icons[$i]}\");\n";								// 11/20/10
		}

	$sm_icons = $GLOBALS['fac_icons'];
	for ($i=0; $i<count($sm_icons); $i++) {
		print "\tsm_icons.push(\"{$sm_icons[$i]}\");\n";						// 11/20/10
		}
	if (($func =="c") || ($func =="r")) {										// build array of existing names
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}fac_types`";
		$result = db_query($query);
		while ($row_fac = stripslashes_deep($result->fetch_assoc())) {
			print "\n\ttype_names.push(\"{$row_fac['name']}\");\n";		// onto JS array - 11/20/10
			}				// end while ()
		}				// end if()
?>
	function validate_f_t(theForm) {			// unit type entry validation - c and u
		var errmsg="";
		if (theForm.frm_name.value == "")				{errmsg+= "\tType name is required\n";}
		if (theForm.frm_description.value == "")		{errmsg+= "\tType description is required\n" ;}
		if (theForm.frm_icon.value == "")				{errmsg+= "\tIcon selection is required\n" ;}
<?php
	if ($func =="c")  {										//check existence
?>
		if (type_names.inArray(theForm.frm_name.value))	{errmsg+= "\tDuplicated Type name\n";}
<?php
		}			// end if ($func =="c")
?>
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function validate_f_t(theForm)

	function icon_to_form(the_index) {
		var the_img = $('ID3');
		the_img.src = icons_dir+sm_icons[the_index];		// display small icon
		document.forms[1].frm_icon.value=the_index;			// icon index to form variable
		$('ID3').style.visibility = "visible";				// initially hidden for 'create'
		return;
		}

	function gen_img_str(the_index) {						// returns image string for nth icon
		var the_sm_image = icons_dir + sm_icons[the_index];
//		alert(the_sm_image);
		var the_title = icons[the_index].substr (0, icons[the_index].length-4).toUpperCase();	// extract color name
		return "<IMG SRC='" + the_sm_image + "' onClick  = 'icon_to_form(" + the_index + ")' TITLE='" + the_title +"' />";
		}


<?php
		if ($row) {
?>
			var which = "<?php print $row['icon'];?>";	// full size icon
			icon_to_form(which);						// must be update or view
<?php
			}				// end if (isset($row))

	    break;			// end case "fac_types"

	    }				// end switch ($tablename)

// End of code for capabilities for Facilities Icons 10/6/09-----------------------------------------------
?>

function $() {									// 12/20/08
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
		}
	return elements;
	}

function getElement(aID){
	return (document.getElementById) ? document.getElementById(aID) : document.all[aID];
	}

JSfnBrowserSniffer();
function JSfnBrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && document.getElementById) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}


	function JSfnShowLayer(id, action){												// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.visibility='" + action + "'");  	// id is the name of the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".visibility='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("document.getElementById('" + id + "').style.visibility='" + action + "'");
		}

	function JSfnHideit (spanid) {
		JSfnShowLayer(spanid, "hidden");
		}

	function JSfnShowit (spanid) {
		JSfnShowLayer(spanid, "visible");
		}

	function JSfnChangeClass(id, newClass) {	// ex: onBlur="JSfnChangeClass(this.id, 'dirty');"
		identity=document.getElementById(id);
		identity.className=newClass;
		}

	function JSfnDisallow (thefunc, theid) {		// 10/20/09
		alert("Disallowed");
		return false;
		}

	function JSfnToFunc (thefunc, theid) {
		if (thefunc == "d" ) {
			if (!confirm ("Please confirm item deletion?\n\n" )) {
				return;
				}
			}
		document.detail.func.value=thefunc;
		document.detail.id.value=theid;
		document.detail.tablename.value="<?php print $tablename; ?>";
		document.detail.submit();
		}

	function JSfnToNav(pageno) {				// paging function
		document.r.page.value=pageno;
		document.r.func.value="r";
		document.r.submit();
		}				// end function JSfnToNav()

	var currsort = '<?php print $sortby;?>';

	function JSfnToSort(thevalue) {				// column name
		if (thevalue == currsort) 	{
			document.r.sortdir.value = parseInt(document.r.sortdir.value);
			document.r.sortdir.value++;
			document.r.sortdir.value = (document.r.sortdir.value) % 2; // toggle direction
			} else {
			document.r.sortdir.value = 0;
			}
		document.r.sortby.value=thevalue;
		document.r.page.value="1";				// start at page 1
		document.r.submit();
		}				// end function JSfnToSort()

<?php
if (($func == "c")||($func == "u")) {			// Create and Update funcs only
?>
	function JSfnMyDate() {							// returns local date/time string per MySQL date format
		var curdate = new Date();
		var year = curdate.getYear();
		if (year < 1000) {year+=1900;}			// resolves different JS's issue
		var month = curdate.getMonth()
		if (month <10) { month = "0"+ month;}
		var mday = curdate.getDate()
		if (mday<10) {mday = "0"+mday ;}
		var hours = curdate.getHours()
		if (hours<10) {hours= "0"+hours;}
		var minutes = curdate.getMinutes();
		if (minutes<10) {minutes= "0"+minutes;}
		return year + "-" + month + "-" + mday + " " + hours + ":" + minutes;
		}

	function ck_blob(strin) {						// check non-empty
		if (((strin.length)==0) || (strin==null)) {
			return false;
			}
		else { return true; }
		}					// end blob()
	function ck_string(strin) {						// check non-empty
		if (((strin.length)==0) || (strin==null)) 	{return false;}
		else 										{return true;}
		}					// end blob()
	function ck_real (strin) {					// use JS function
		if (parseFloat(strin)== strin) 	{return true;}
		else 							{return false;}
		}
	function Ck_Range(realin) {					//
		if ((parseFloat(realin) >= 180.0 ) || (parseFloat(realin) <= -180.0 ) || (parseFloat(realin) == 0.0 )) {
			return false;}
		else {
			return true;}
		}
	function ck_int(strin){						// positive integers only - use JS function
		if ((parseInt(strin, 10) == strin)  && (parseInt(strin, 10) >= 0)) 	{return true;}
		else 																{return false;}
		}
	function ck_timestamp (strin) {				// false if fails ************
		return true;
		}
	function ck_datetime (strin) {				// false if fails ************
		return true;
		}
	function ck_date (strin) {					// false if fails ************
		return true;
		}

	function ck_time (strin) {					// false if fails ************
		var thearray = strin.split(":");
		if ((thearray.length)!=2) 																		{return false;}
		if ((parseInt(thearray[0], 10) != thearray[0]) || (parseInt(thearray[1], 10) != thearray[1]))	{return false;}
		if ((thearray[0]>23)||(thearray[0]>59)) 														{return false;}
		return true;
		}

	function JSfnCheckInput(myform, mybutton) {	// reject empty form elements
		function JSfnAddError() {
			errmsg +=  "'" + displayname +  "' format error\n" ;
			}				// end function

		var errmsg = "";
<?php
		if (isset($tablename)){					// go column-by-column

			print "\n\t\tmands = new Array();\t\t\t// array of mandatory fieldnames\n ";
			print "\t\ttypes = new Array();\t\t\t// array of fieldname types\n ";
			$query ="SHOW COLUMNS FROM `$mysql_prefix$tablename`";			// check value where possible - by mysql_field_type
			$result = db_query($query);		// check presence/absence
			while ($row = $result->fetch_assoc()) {
				$thename = $row['Field'];
				$thetype = $row['Type'];
				print "\t\ttypes['frm_' + '$thename'] = \"$thetype\";\n";
				if (($row['Null']) !=0) {								//
					print "\t\tmands['frm_' + '$thename'] = \"$thetype\";\n";
					}		// end if ...
				}				// end while ...
			print "\n";
			unset($result);

			}			// end if (isset($tablename)) ...
?>
		for (i=0; i<myform.elements.length; i++) {					// check mandatories and values
			if (myform.elements[i].name.substring(0, 4) == "frm_") {
				var displayname = myform.elements[i].name.substring(4, 99);
				}
			if (mands[myform.elements[i].name]) {					// defined in mandatory array?
				if (myform.elements[i].type == 'select-one') { 			// field type = select?
					if (myform.elements[i].options[myform.elements[i].selectedIndex].value==0) 	{
						errmsg += "'" + displayname + "' is rqd\n";
						}
					}							// end field type = select
				else { 															// all other types
					if (myform.elements[i].value=='') 	{errmsg += "'" + displayname + "' is rqd\n"; }
					}															// end else ... (all other types)
				}																// end mandatory
			if ((myform.elements[i].value.length>0) && (types[myform.elements[i].name])) {			// non-empty and defined in types array?
				var theTemp = types[myform.elements[i].name].split("(");
				var fieldtype = theTemp[0];
				switch (fieldtype){						// name to types array
					case "varchar":
					case "char":
						if (!ck_string(myform.elements[i].value))	{JSfnAddError();}
						break;
					case "blob":
					case "BLOB":
					case "mediumblob":
					case "longblob":
						if (!ck_blob(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "string":
					case "VAR_STRING":
					case "STRING":
					case "text":
					case "tinytext":
					case "mediumtext":
					case "longtext":
					case "enum":
						if (!ck_string(myform.elements[i].value))	{JSfnAddError();}
						break;
					case "real":
					case "float":
					case "decimal":
						if (!ck_real(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "tinyint":
					case "smallint":
					case "mediumint":
					case "int":
					case "INTEGER":
					case "bigint":
					case "DOUBLE":
					case "double":
						if (!ck_int(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "timestamp":
					case "TIMESTAMP":
						if (!ck_blob(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "date":
					case "DATE":
					case "datetime":
					case "DATETIME":
						if (!ck_date(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "time":
					case "TIME":
						if (!ck_time(myform.elements[i].value))		{JSfnAddError();}
						break;

					default :alert ("414 " + types[myform.elements[i].name] + " ??");
					}
				}									// end if (types ...)
			}									// end for (i=0 ...
		if (errmsg!= "") {
			mybutton.disabled=false;			// allow clicks
			alert ("Input errors - please correct the following:\n\n" + errmsg)
			}
		else {myform.submit(); }

		}			// end function check input()
<?php
	}			// end if (($func == ...
?>

function do_onload() {
	if		(document.c){do_focus(document.c)}
	else if (document.u){do_focus(document.u)}
	else if (document.s){do_focus(document.s)}
	return;
	}

function do_focus(in_form) {
	for (i=0; i<in_form.elements.length; i++) {
		if (in_form.elements[i].type == 'text') {
			in_form.elements[i].focus();
			return;
			}
		}
	}

</SCRIPT>
<?php
if(array_key_exists('srch_str', $_POST)) {		//	3/18/11
	$search_str = $_POST['srch_str'];
	} else {
	$search_str = "";
	}
?>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->

<BODY onLoad = "do_onload()">	<!-- 9/21/08 -->
<?php $the_table = (safe_strlen($tablename)>0)? $tablename : "tbd"; ?>
<BR />
<CENTER>
	<SPAN CLASS='header text_biggest text_center' style='padding-top: 10px; padding-bottom: 10px; width: 100%; display: block;'>Table:
		<SPAN STYLE="background: white">&nbsp;<?php print $the_table; ?>&nbsp;</SPAN>
	</SPAN>
</CENTER>
<BR />
<FORM NAME="detail" METHOD="post" 	ACTION="<?php print $_SERVER['PHP_SELF'] ?>">
<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>	<!-- 7/11/10 -->
<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
<INPUT TYPE="hidden" NAME="id"  		VALUE=""/>
<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
<INPUT TYPE="hidden" NAME="func"  		VALUE=""/>  <!-- retrieve details -->
<INPUT TYPE="hidden" NAME="srch_str"  	VALUE="<?php print $search_str;?>"/> <!-- 9/12/10 -->

</FORM>
<?php
if (($func == "c")||($func == "u")) {			// not required for all functions
	$query ="DESCRIBE `$mysql_prefix$tablename`";									// collect table field attributes
	$resultattr = db_query($query);
	$arrayattr = array();
	$i = 0;
	while ($rowattr = $resultattr->fetch_array())  {							// write each data row attr
		for($j = 0; $j < count($rowattr)-1; $j++){									// each column
			if((isset($rowattr[$j])) && (!is_null($rowattr[$j]))) {
				$arrayattr[$i][$j] = $rowattr[$j];
				}
			}		// end for($j = ...
		$i++;
		}			// end while ($rowattr = ...
	unset ($resultattr);
	}

switch ($func) {		// ================================== case "c" ======================================================
	case "c":																	// Create record -- add Enums	enum('a','b','c')
	$comments_ar = get_comments($tablename);					// array of name, comment

	$the_custom = "./tables/c_" . $tablename . ".php";				// 12/26/08
	if (file_exists ( $the_custom)){
		require_once($the_custom) ;
		$custom	= TRUE;
		} else {
		if ($fill_from_last) {
			$the_id = $indexname;													// for form pre-filling
			$query = "SELECT * FROM `$mysql_prefix$tablename` WHERE `$the_id` = (SELECT MAX(`$the_id`) FROM `$mysql_prefix$tablename`)";
			$result = db_query($query);
			if ($result->num_rows == 0) 	{$row = NULL ;}
			else							{$row = $result->fetch_array();}
			unset ($result);
			}
		else {$row = NULL;}
?>

		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>">
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ;?>"/>
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->


		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table '<?php print $tablename?>' - Add New Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
	<?php

		$query ="SELECT * FROM `$mysql_prefix$tablename` LIMIT 1";
		$result = db_query($query);
		$num_fields = $result->field_count;	// 3/14/26 - Cache field count before loop (db_query calls inside loop change mysqli_field_count)
		$lineno = 0;
		$thetemp = get_defined_constants(true);
//		dump($thetemp);
		for ($i = 0; $i < $num_fields; $i++) {
			if ((!is_null($row)) && ($fill_from_last)) 	{$last_data = $row[$i]; $class="clean";}
			else 										{$last_data = ""; 		$class="dirty";}

			if (substr(mysqli_fetch_field_direct($result, $i)->name, 0, 1 ) =="_") {				// 12/20/08
				switch (mysqli_fetch_field_direct($result, $i)->name) {
					case "_by":
					case "_userid":													// 10/26/10
						$value = $_SESSION['user_id'];
						print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__by\" VALUE=\"$value\" />\n";
						break;
					case "_from":
						$value = $_SERVER['REMOTE_ADDR'];
						print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__from\" VALUE=\"$value\" />\n";
						break;
					case "_on":
//						$value = date("Y-m-d H:i:00");			// ex: 2008-12-18 01:46:18;
						$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
						print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__on\" VALUE=\"$now\" />\n";
						break;
					}				// end switch ()
				}				// end if (substr())
			else {
				if ($arrayattr[$i][5]!= "auto_increment") {
					$lineno++;
					$mand_opt =($arrayattr[$i][2]!= "YES")? "warn" : "opt";						// identifies mandatory vs. optional input
					$max = get_digs($arrayattr[$i][1]);											// max input lgth per attrib's array - 2/8/10
					print "<TR VALIGN=\"baseline\" CLASS=\"" .$evenodd [$lineno % 2]  . "\">";
					print "<TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace( "_", " ", ucfirst(mysqli_fetch_field_direct($result, $i)->name)) . ":</TD>";
					switch (mysql_field_type_compat($result, $i)) {
						case "DATETIME":
						case "DATE":
						case "TIMESTAMP":
						case "datetime":
						case "date":
						case "timestamp":
							fnDoCal($i);				// generates JS Calendar stuff
							$max = 16;
							$value = date($date_out_format);
							print "<TD><INPUT MAXLENGTH=$max ID=\"fd$i\" SIZE=$max type=\"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$value\" onChange = \"this.value=JSfnTrim(this.value)\"/>";
							fnCalButt ($i);
//							print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
							$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
							print "{$hint_str}</TD></TR>\n\t";
							break;

						case "TIME":
						case "time":
							$value = date ("H:i");
							$max = 5;
							print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$value\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//							print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
							$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
							print "{$hint_str}</TD></TR>\n\t";
							break;

						case "int":
						case "bigint":
						case "INTEGER":
						case "BIGINT":
						case "INT":
						case "LONGLONG":
						case "LONG":
							$gotit = FALSE;
							if (strtolower(substr(mysqli_fetch_field_direct($result, $i)->name, -$id_lg)) == $FK_id) {			// maybe dropdown
								$lgth = safe_strlen(mysqli_fetch_field_direct($result, $i)->name);
								$thetable = substr( mysqli_fetch_field_direct($result, $i)->name,0, $lgth-$id_lg) ;			// extract corresponding table name
								if (mysql_table_exists($thetable)) {											// does non-empty table exist?
									$query ="SELECT * FROM `$mysql_prefix$thetable` LIMIT 1";					// order will be by column 1, name unk
									$temp_result = db_query($query);
									$thecolumn = mysqli_fetch_field_direct($temp_result, 1)->name	;							// column 1 field name

									$query ="SELECT * FROM `$mysql_prefix$thetable` ORDER BY `$thecolumn` ASC";
									$temp_result = db_query($query);
									print "\t\t<TD><SELECT NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "'>\n\t\t<OPTION VALUE='0' selected>Select one</OPTION>\n";
									while ($temp_row = $temp_result->fetch_array())  {							// each row
										print "\t\t<OPTION VALUE='" . trim($temp_row[0]) . "'>" . trim($temp_row[1]) . "</OPTION>\n";
										}
									print "\t\t</SELECT>";
//									print ($do_hints)? "<SPAN class='$mand_opt' >(" . mysql_affected_rows() . ")</SPAN>": "";
									$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
									print "{$hint_str}</TD></TR>\n\t";
									unset ($temp_result);
									$gotit = TRUE;
									}											// end if (mysql_table_exists($thetable)) ...
								}										// end maybe dropdown
							// 3/14/26 - Check FK overrides for columns without _id suffix
							if (!$gotit && isset($fk_overrides[mysqli_fetch_field_direct($result, $i)->name])) {
								$fk = $fk_overrides[mysqli_fetch_field_direct($result, $i)->name];
								$fk_table = $mysql_prefix . $fk['table'];
								$fk_alias = $fk['display_alias'];
								$fk_query = "SELECT `id`, {$fk['display_expr']} AS `{$fk_alias}` FROM `{$fk_table}` ORDER BY `{$fk_alias}` ASC";
								$temp_result = db_query($fk_query);
								print "\t\t<TD><SELECT NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "'>\n";
								print "\t\t<OPTION VALUE='0' selected>None</OPTION>\n";
								if ($temp_result) {
									while ($temp_row = $temp_result->fetch_assoc()) {
										print "\t\t<OPTION VALUE='" . e($temp_row['id']) . "'>" . e($temp_row[$fk_alias]) . "</OPTION>\n";
									}
								}
								$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
								print "\t\t</SELECT>{$hint_str}</TD></TR>\n\t";
								unset($temp_result);
								$gotit = TRUE;
							}
							if (!$gotit) {
								print "<TD><INPUT ID=\"ID$i\" MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//								print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
								$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
								print "{$hint_str}</TD></TR>\n\t";
								}				// end if (!$gotit)
							break;

						case "BLOB":
						case "blob":
						case "VAR_STRING":
						case "STRING":
						case "CHAR":
						case "char":
						case "str":
						case "string":
						case "text":
						case "longtext":
							if (substr($arrayattr[$i][1], 0, 4) == "enum") {				// yes, parse enums
								$temp = substr($arrayattr[$i][1], 4);
								$temparray = explode( ",", $temp);
								print "<TD VALIGN='baseline'>&nbsp;<B>";
								$drops = array("'","(",")");
								$default = (isset($arrayattr[$i][4]))? $arrayattr[$i][4] : "";
								for ($j = 0; $j < count($temparray); $j++) {
									$temparray[$j] = str_replace($drops, "", $temparray[$j]);		// drop sgl quotes, parens
									$checked=($temparray[$j]==$default)? " CHECKED ": "";
									print "$temparray[$j]<INPUT TYPE='radio' NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE= \"$temparray[$j]\" $checked STYLE='vertical-align:middle;'/>&nbsp;&nbsp;&nbsp;&nbsp;";
									}				// end for ($j = 0;
								$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
								print "</B>{$hint_str}</TD></TR>\n\t";
								}				// end if ("enum")
							else	{
								if (($max> $text_type_max) || (mysql_field_type_compat($result, $i)=="blob")){
									print "\n\t\t<TD><TEXTAREA ID='ID{$i}' CLASS='{$class}' NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "' COLS='90' ROWS = '3' onFocus=\"JSfnChangeClass(this.id, 'dirty');\" STYLE='vertical-align:text-top;'>{$last_data}</TEXTAREA> ";
									}
								else {
									print "\n\t\t<TD><INPUT  ID=\"ID$i\" CLASS=\"$class\" MAXLENGTH=\"$max\" SIZE=\"$max\" type=\"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"> ";
									}
//								print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
								$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
								print "{$hint_str}</TD></TR>\n\t";
			 					}				// end else
							break;

						case "real":
						case "DOUBLE":
						case "double":
							$max = 12;
							print "<TD><INPUT ID=\"ID$i\" MAXLENGTH=$max SIZE=$max TYPE=text NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//							print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
							$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
							print "{$hint_str}</TD></TR>\n\t";
							break;


						default:
							print __line__ . mysql_field_type_compat($result, $i)  . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
						}					// end switch
					}		// end if ... != "auto_increment")
				}		// end else ...
			}		// end for ($i = ...
		unset ($result);

	?>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<SPAN id='can_but' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r';document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
		<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="reset(document.c);"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
		<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnCheckInput(document.c, this);"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
		</TD></TR>
		</FORM>
		</TD></TR></TABLE>
	<?php
		}

	break;														// end 'Create record'

	case "r":																			// Retrieve/List =================
	function fnLinkTDm ( $theclass, $theid, $thestring, $the_in_use) {		// returns <td ... /td>
		global $tablename, $mysql_prefix, $disallow, $can_edit;				// 2/25/10, 3/19/11

		$the_js_func = ($disallow)? "JSfnDisallow" : "JSfnToFunc" ;		// 10/20/09

		if ($the_in_use) {																	// 10/13/09
			$on_click = "onclick = \"alert('DELETE disallowed for this item');\"";
			} else {
			$on_click = "onclick = \"{$the_js_func}('d', '" . $theid. "');\"";
			}

		$breakat = 24;
		if (safe_strlen($thestring) > $breakat) {
			$return = " CLASS='" . $theclass . " text text_center' onmouseover =\"document.getElementById('b" . $theid . "').style.visibility='hidden' ; document.getElementById('c" . $theid . "').style.visibility='visible';\" onmouseout = \"document.getElementById('c" . $theid . "').style.visibility='hidden'; document.getElementById('b" . $theid . "').style.visibility='visible' ; \" >\n";
			$return .= substr($thestring, 0, $breakat) . "<SPAN id=\"b" . $theid . "\" style=\"visibility:visible\">" ;
			$return .= substr($thestring, $breakat) . "</SPAN><SPAN id=\"c" . $theid . "\" style=\"visibility: hidden\">\n";
			$return .= ". . . <IMG SRC='markers/view.png' BORDER=0 TITLE = 'click to view this' onclick = \"JSfnToFunc('v', '" . $theid . "');\">";
			$return .= " | ";
			if($can_edit) {										// 3/19/11
				$return .= " <IMG SRC='markers/edit.png' BORDER=0 TITLE = 'click to edit this' onclick = \"{$the_js_func}('u', '" . $theid . "');\">";
				}
			$return .= " | ";
			if ((!($the_in_use)) &&($can_edit)) {																	// 11/2/09
				$return .= "<IMG SRC='markers/del.png' BORDER=0 TITLE = 'click to delete this' $on_click> | ";
				}
			$return .= "</SPAN>\n";
			} else {
			$return = " CLASS='" . $theclass . "' onmouseover =\"document.getElementById('c" . $theid . "').style.visibility='visible';\" onmouseout = \"document.getElementById('c" . $theid . "').style.visibility='hidden'; \" >\n";
			$return .= "<SPAN id=\"c" . $theid . "\" style=\"visibility: hidden\">\n";
			$return .= " <IMG SRC='markers/view.png' BORDER=0 TITLE = 'click to view this' onclick = \"JSfnToFunc('v', '" . $theid . "');\">";
			$return .= " | ";
			if($can_edit) {										// 3/19/11
				$return .= "<IMG SRC='markers/edit.png' BORDER=0 TITLE = 'click to edit this' onclick = \"{$the_js_func}('u', '" . $theid . "');\">";
				}
			$return .= " | ";
			if ((!($the_in_use)) && ($can_edit)) {																	// 11/2/09
				$return .= "<IMG SRC='markers/del.png' BORDER=0 TITLE = 'click to delete this' $on_click> | ";
				}
			$return .= "</SPAN>\n";
			}
		return "<TD ALIGN='center'" . $return . $thestring ."</TD>\n";
		}			// end function fnLinkTDm ()



	$dirs = array (" ASC ", " DESC ");
	$arrowdir = array ("<IMG SRC='./markers/up.png' HEIGHT='20px' style='vertical-align: text-top; float: right;'>", "<IMG SRC='./markers/down.png' HEIGHT='20px' style='vertical-align: text-top; float: right;'>");			// sort direction arrows
	if (!isset($sortby)) {
		$sortby = $indexname; $sortdir = 0;										// default sort by is in $indexname
		}
	if (empty($numrows))	{
		$query ="SELECT * FROM `$mysql_prefix$tablename` ";						// get row count only
		$result = db_query($query);
		$numrows = $result->num_rows;
		unset ($result);
		$pageNum = 1;
		} else {
		$pageNum = ($page > 0) ? $page : 1;
		}
	$offset = ($pageNum - 1) * $rowsPerPage;									// calculate 0-based offset	from 1-based page no.
	$special = "`id`, `name`, `office`, `street`, `city`, `state`, `zip`, `phone`, `status`, `contract`, `email`, `website`, `type`, `chief`, `chief_title`, `emergency_contact`, `emergency_phone`, `contact_via`, `contact_via_use`, `admin_contact`, `admin_phone`, `hours`, `contact_date`";
	$select ="SELECT * FROM `$mysql_prefix$tablename` ";

	$order_by = " ORDER BY `{$sortby}` {$dirs[$sortdir]}";

	$limit = " LIMIT $offset, $rowsPerPage";

	if (empty($srch_str))  {$where = "";}
	else {
		$where = " WHERE (";
		$ary_srch = explode ("|", $srch_str);
		$safe_search_term = db()->real_escape_string($ary_srch[0]);
		$the_or = "";
		for ($i=1; $i< count($ary_srch); $i++) {
			$safe_col = preg_replace('/[^a-zA-Z0-9_]/', '', $ary_srch[$i]);  // sanitize column name
			$where .= "{$the_or} (`{$safe_col}` REGEXP '{$safe_search_term}')";
			$the_or = " OR ";
			}				// end for ($i=...)
		$where .= ")";

		}		// end if/else

	$query = $select . $where . $order_by . $limit ;

	$result = db_query($query);
	$row_count = $result->num_rows;

	print "<TABLE ID='listView' ALIGN=\"center\" CELLPADDING=\"2\">\n";

	if ($result->num_rows == 0) {
		$page="";
		$message = (empty($where))? "Table '" . str_replace( "_", " ", ucfirst($tablename))  . "' is empty!" :
		"No matches in '{$tablename}' search for '{$ary_srch[0]}' ";
		print "<TR VALIGN='top'><TD ALIGN='center' CLASS='header text'><BR /><BR /><BR />{$message}<BR /><BR /><BR /><BR /><BR /></TD></TR>";
		} else {				// we got rows
		$maxPage = ceil($numrows/$rowsPerPage);						// # pages => $maxPage
		$prev = $next = $nav = '';									// initially empty
		$plural = ($row_count==1)? "" : "s";
		$head1 = "<TR CLASS = 'odd' valign='TOP' style='width: 100%;'><TH CLASS='text' COLSPAN=99 ALIGN='center'>{$row_count} record{$plural} "." <FONT SIZE=\"-2\">&nbsp;&nbsp;(mouseover ";
		$head2 = "<TR CLASS = 'plain_listheader text' VALIGN='top' style='width: 100%;'>";
		$cols = $result->field_count;
		$cols = ($cols > 17) ? 17 : $cols;
		$subst = array();											// will hold substitution values for colnames like 'what_id'

		for ($i = 0; $i < $cols; $i++) {							// write table header, etc.
			if ((mysqli_fetch_field_direct($result, $i)->name != $indexname) && (strtolower(substr(mysqli_fetch_field_direct($result, $i)->name, -$id_lg)) == $FK_id)
						&& ($temp = fnSubTableExists(mysqli_fetch_field_direct($result, $i)->name))) {							// prepare to replace with indexed values
				$query = "SELECT * FROM $mysql_prefix$temp";
				$temp_result = db_query($query);
				while ($temp_row = $temp_result->fetch_array())  {											// each row/value => $substitutions array
					if (($temp == 'user') && (array_key_exists('user', $temp_row))) {							// 12/12/11 - special case table user
						$subst[fnSubTableExists(mysqli_fetch_field_direct($result, $i)->name)][$temp_row[0]] = $temp_row['user'];		// assign value to column_name[index]  value
						} else {
						$subst[fnSubTableExists(mysqli_fetch_field_direct($result, $i)->name)][$temp_row[0]] = $temp_row[1];		// assign value to column_name[index]  value
						}
					}						// end while ($temp_row = ...
				unset ($temp_result);
				}
			// 3/14/26 - FK overrides for columns that don't follow the _id convention (e.g. 'owner')
			$col_name = mysqli_fetch_field_direct($result, $i)->name;
			if (isset($fk_overrides[$col_name])) {
				$fko = $fk_overrides[$col_name];
				$fko_query = "SELECT `id`, {$fko['display_expr']} AS `{$fko['display_alias']}` FROM `{$mysql_prefix}{$fko['table']}` ORDER BY `{$fko['display_alias']}` ASC";
				$fko_result = db_query($fko_query);
				while ($fko_row = $fko_result->fetch_array()) {
					$subst[$col_name][$fko_row[0]] = $fko_row[$fko['display_alias']];
				}
				unset($fko_result);
			}

			$thecolumn = mysqli_fetch_field_direct($result, $links_col)->name;		// column name
			$arrow = (mysqli_fetch_field_direct($result, $i)->name == $sortby) ? $arrowdir[$sortdir] : "<IMG SRC='./images/blank.png' HEIGHT='20px' style='vertical-align: text-top; float: right;' />";
			$head2 .= "<TH ID='header_" . $i . "' ALIGN='top' CLASS='plain_listheader text' onMouseover='do_hover_listheader(this.id);' onMouseout='do_plain_listheader(this.id);' onClick =\"JSfnToSort('" . mysqli_fetch_field_direct($result, $i)->name . "')\" >" . str_replace( "_", " ", ucfirst(mysqli_fetch_field_direct($result, $i)->name)) . "<BR />$arrow</TH>\n";
			}
		$head2 .= "</TR>\n";										// end table heading
		print $head1 . "<U>" . str_replace( "_", " ", ucfirst($thecolumn)) . "</U> data for functions)</FONT></TH></TR>\n" . $head2;
		$lineno = 0;
		$srch_term = isset($ary_srch) ? array_shift ($ary_srch): "";

		while ($row = $result->fetch_array())  {			// write each data row - highlight($term, $string)
			$lineno++;
			$on_click = " onClick=JSfnToFunc('v',{$row[$indexname]});";		// 9/15/10

			print "<TR valign=\"top\" CLASS=\"" . $evenodd [$lineno % 2] . "\" style='width: 100%;'>";			// alternate line bg colors
			for($i = 0; $i < $cols; $i++){													// each column

				$in_use = FALSE;				// test for index value in use - 10/13/09
				if ($i==0) {					// index column only
					$in_use = is_in_use($row[$i]);								// 11/9/10
					}

				$lgth = safe_strlen(mysqli_fetch_field_direct($result, $i)->name);								// shortened column name
				if (isset($row[$i])) {														// not empty

					if (mysql_field_type_compat($result, $i)=="time") {
						print "<TD CLASS='mylink text text-center' {$on_click} >" . substr($row[$i],0,5) . "</TD>\n";
						} else {
						if ($i == $links_col) {												// 'name' or 'descr' or default
							print fnLinkTDm ( "mylink" , $row[0] , $row[$i] , $in_use );	// generate JS function link - assume id as column 0
							} else {
							if ((mysqli_fetch_field_direct($result, $i)->name != $indexname) && (strtolower(substr(mysqli_fetch_field_direct($result, $i)->name, -$id_lg)) == $FK_id)) {	// check terminal 3 chars
								$thearray = substr(mysqli_fetch_field_direct($result, $i)->name, 0, $lgth-$id_lg);	// extract table name
								if (isset($subst[$thearray][$row[$i]])) {					// defined?
									$thedata = $subst[$thearray][$row[$i]];					// yes - pull substitution data
									} else {
									$thedata = $row[$i];
									}								// no substitution data
								} elseif (isset($subst[mysqli_fetch_field_direct($result, $i)->name][$row[$i]])) {	// 3/14/26 - FK override substitution
								$thedata = $subst[mysqli_fetch_field_direct($result, $i)->name][$row[$i]];
								} else { 									// not substitution or date
								$thedata = (safe_strlen($row[$i])>$text_list_max)? substr($row[$i], 0,$text_list_max) . "&hellip;" : $row[$i];
								}
							if(($tablename=="unit_types") && (mysqli_fetch_field_direct($result, $i)->name=="icon")) {					// 1/29/09
								$thedata = "<IMG SRC='./our_icons/" . $sm_icons[$row[$i]] . "'>";				// display icon image
								}
							if(($tablename=="fac_types") && (mysqli_fetch_field_direct($result, $i)->name=="icon")) {					// 1/29/09
								$thedata = "<IMG SRC='./our_icons/" . $sm_icons[$row[$i]] . "'>";				// display icon image
								}
							$out_str = ((isset($ary_srch)) && (in_array ( mysqli_fetch_field_direct($result, $i)->name,$ary_srch )))?
								highlight($srch_term, $thedata): $thedata;

							print "<TD CLASS='mylink text'{$on_click} >" . $out_str . "</TD>\n";			// type not "datetime" and name not "descript"
							}		// end else ...
						}	// end not "datetime"
					} else {	// end if (isset() ...
					print "<TD CLASS='mylink text' {$on_click}>" . $i . "</TD>\n";							// empty
					}			//  not set
				}			// end for($i = 1 ...
			unset ($row);
			print "</TR>\n";
			}			// end while ...
		unset ($result);
		unset ($subst);
		print "</TABLE>";

		for($page = 1; $page <= $maxPage; $page++) {  				// set link to each page no.
			$nav .= ($page == $pageNum)?
				"<SPAN id='page_" . $page . "' class='plain text text_red text_boldest' style='background-color: blue; color: white; float: none; display: inline-block;'>" . $page . "</SPAN>" :
				"<SPAN id='page_" . $page . "' class='plain text' style='float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='JSfnToNav(\"" . $page . "\");'>" . $page . "</SPAN>" ;
			}
		if ($pageNum > 1) {											// create prior/next links
			$page = $pageNum - 1;
			$prev = "<SPAN id='prev_but' CLASS='plain text text-center' style='float: none; vertical-align: middle; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='JSfnToNav(" . $page . ");'><IMG style='vertical-align: middle;' SRC='./markers/prev.png' height='16' width= '16' border='0' /></SPAN>";
			}

		if ($pageNum < $maxPage) {									// if not on last
			$page = $pageNum + 1;
			$next = "<SPAN id='next_but' CLASS='plain text text-center' style='float: none; vertical-align: middle; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='JSfnToNav(" . $page . ");'><IMG style='vertical-align: middle;' SRC='./markers/next.png' height='16' width= '16' border='0' /></SPAN>";
			}
		print "<CENTER><DIV id='navbuttons' style='width: 800px; display: block; text-align: center;'>" . $prev . $nav . $next . "</DIV></CENTER>";			// print the navigation links
		}					// end got rows
?>
	<BR />
	<BR />
	<FORM NAME="r" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<TABLE ALIGN="center" style='width: 100%;'>
		<TR>
			<TD ALIGN="center" COLSPAN="99">
				<CENTER>
				<BR />
<?php
				if (($row_count > 0) || (array_key_exists('srch_str', $_POST))) {
?>
					<SPAN ID='srch_but' CLASS='plain text' style='width: auto; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.retform.func.value='s'; document.retform.submit();"><SPAN STYLE='float: left;'>Search <?php print ucfirst($tablename);?></SPAN>&nbsp;&nbsp;<IMG STYLE='float: right;' SRC='./images/search_small.png' BORDER=0></SPAN>
<?php
					}
?>
				<SPAN ID='prop_but' CLASS='plain text' style='width: auto; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.retform.func.value='p'; document.retform.submit();"><SPAN STYLE='float: left;'><?php print ucfirst($tablename);?> Properties</SPAN>&nbsp;&nbsp;<IMG STYLE='float: right;' SRC='./images/exclamation_small.png' BORDER=0></SPAN>
<?php
				if ($can_edit) {
?>
					<SPAN ID='add_entry_but' CLASS='plain text' style='width: auto; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.retform.func.value='c'; document.retform.submit();"><SPAN STYLE='float: left;'>New <?php print str_replace( "_", " ", ucfirst($tablename));?> entry</SPAN>&nbsp;&nbsp;<IMG STYLE='float: right;' SRC='./images/plus_small.png' BORDER=0></SPAN>
<?php
					}
?>
			</TD>
		</TR>
	</TABLE>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename; ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="page" 		VALUE="<?php print $page; ?>"/>
	<INPUT TYPE="hidden" NAME="numrows"	 	VALUE="<?php print $numrows; ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE="<?php print $sortdir; ?>"/>
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r">
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	</FORM>
<?php
	break;											// end Retrieve  ==================================


case "u":	// =======================================  Update 	=======================================
	$comments_ar = get_comments($tablename);					// array of name, comment

	$query = "DESCRIBE `$mysql_prefix$tablename` ";		// 6/21/10
	$result = db_query($query);			// use $result for meta-information reference
	$types = array();
	$i = 0;
	while($row = stripslashes_deep($result->fetch_assoc())) {		// major while () - 3/25/09
		$types[$i] = $row['Type'];
		$i++;
		}

	$post_id = sanitize_int($_POST['id']);
	$query ="SELECT * FROM `$mysql_prefix$tablename` WHERE `" . $indexname . "` = ? LIMIT 1";					// target row
	$result = db_query($query, [$post_id]);			// use $result for meta-information reference
	$row = $result->fetch_array();																		// $row has data
	$lineno = 0;															// for alternating row colors
	$thetemp = get_defined_constants(true);
	$the_custom = "./tables/u_" . $tablename . ".php";				// 12/20/08
	if (file_exists ( $the_custom)){
//		print __LINE__ . "<BR />";
		$custom	= TRUE;
		require_once($the_custom) ;
		} else {
?>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print e($_POST['id']); ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->


	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table '<?php print $tablename?>' - Update/Delete Entry</FONT></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
<?php
	$num_fields = $result->field_count;	// 3/14/26 - Cache field count before loop
	for ($i = 0; $i < $num_fields; $i++) {
		$max = get_digs($types[$i]);											// max input lgth per types array - 6/21/10
		if (substr(mysqli_fetch_field_direct($result, $i)->name, 0, 1 ) =="_") {				// 12/20/08
			switch (mysqli_fetch_field_direct($result, $i)->name) {
				case "_by":
				case "_userid":													// 10/26/10
					$value = $_SESSION['user_id'];
					print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__by\" VALUE=\"$value\" />\n";
					break;
				case "_from":
					$value = $_SERVER['REMOTE_ADDR'];
					print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__from\" VALUE=\"$value\" />\n";
					break;
				case "_on":
					$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		// 11/8/09
					print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__on\" VALUE=\"$now\" />\n";
					break;
				}				// end switch ()
			} else {					// end if (substr())
			$disabled = ($arrayattr[$i][5] == "auto_increment")? " disabled" : "";
			$lineno++;
			$mand_opt =($arrayattr[$i][2]!= "YES")? "warn" : "opt";
			print "<TR VALIGN=\"baseline\" CLASS=\"" .$evenodd [$lineno % 2]  . "\">";
			print "<TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace ( "_", " ", ucfirst(mysqli_fetch_field_direct($result, $i)->name)) . ":</TD>";
			switch (mysql_field_type_compat($result, $i)) {
				case "datetime":
				case "date":
				case "timestamp":
				case "DATETIME":
				case "DATE":
				case "TIMESTAMP":
					$max = 16;
					$value=date($date_out_format);
//					echo __LINE__ . " " . $max . "<BR />";
					print "<TD><INPUT MAXLENGTH=$max SIZE=$max type=\"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/>";
//					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
					$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
					print "{$hint_str}</TD></TR>\n\t";
					break;

				case "time":
				case "TIME":
					$max = 5;
					print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
					$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
					print "{$hint_str}</TD></TR>\n\t";
					break;

				case "int":
				case "bigint":
				case "INTEGER":
				case "BIGINT":
				case "INT":
				case "LONGLONG":
				case "LONG":
					$gotit = FALSE;
					if ((mysqli_fetch_field_direct($result, $i)->name != $indexname) && (strtolower(substr(mysqli_fetch_field_direct($result, $i)->name, -$id_lg)) == $FK_id)) {			// maybe dropdown
						$lgth = safe_strlen(mysqli_fetch_field_direct($result, $i)->name);
						$thetable = substr( mysqli_fetch_field_direct($result, $i)->name,0, $lgth-$id_lg) ;			// extract corresponding table name
						if (mysql_table_exists($thetable)) {											// does table exist?
							$query ="SELECT * FROM `$mysql_prefix$thetable` LIMIT 1";					// order will be by 2nd column
							$temp_result = db_query($query);
							$thecolumn = mysqli_fetch_field_direct($temp_result, 1)->name	;							// field name 2nd column

							$query ="SELECT * FROM `$mysql_prefix$thetable` ORDER BY `$thecolumn` ASC";	// get option values
							$temp_result = db_query($query);
							print "\t\t<TD><SELECT NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "'>\n";
							if ($row[mysqli_fetch_field_direct($result, $i)->name]=='0') {print "\t\t<OPTION VALUE='0' selected>Select</OPTION>\n" ;}				// no selection made
							while ($sel_row = $temp_result->fetch_array())  {								// each row - assume 2nd column has values
								$selected = ($sel_row['id'] == $row[mysqli_fetch_field_direct($result, $i)->name])? " selected" : "";
								print "\t\t<OPTION VALUE='" . $sel_row[0] . "'" . $selected  . " >" . $sel_row[1] . "</OPTION>\n";		// *************
								}
					$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
							print "\t\t</SELECT>";
							print "{$hint_str}</TD></TR>\n\t";
							unset ($temp_result);
							$gotit = TRUE;
							}											// end if (mysql_table_exists($thetable)) ...
						}										// end maybe dropdown
					// 3/14/26 - Check FK overrides for columns without _id suffix
					if (!$gotit && isset($fk_overrides[mysqli_fetch_field_direct($result, $i)->name])) {
						$fk = $fk_overrides[mysqli_fetch_field_direct($result, $i)->name];
						$fk_table = $mysql_prefix . $fk['table'];
						$fk_alias = $fk['display_alias'];
						$fk_query = "SELECT `id`, {$fk['display_expr']} AS `{$fk_alias}` FROM `{$fk_table}` ORDER BY `{$fk_alias}` ASC";
						$temp_result = db_query($fk_query);
						$current_val = $row[mysqli_fetch_field_direct($result, $i)->name];
						print "\t\t<TD><SELECT NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "'>\n";
						$none_selected = (empty($current_val) || $current_val == '0') ? " selected" : "";
						print "\t\t<OPTION VALUE='0'{$none_selected}>None</OPTION>\n";
						if ($temp_result) {
							while ($sel_row = $temp_result->fetch_assoc()) {
								$selected = ($sel_row['id'] == $current_val) ? " selected" : "";
								print "\t\t<OPTION VALUE='" . e($sel_row['id']) . "'{$selected}>" . e($sel_row[$fk_alias]) . "</OPTION>\n";
							}
						}
						$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
						print "\t\t</SELECT>{$hint_str}</TD></TR>\n\t";
						unset($temp_result);
						$gotit = TRUE;
					}
					if (!$gotit) {
//						dump(__LINE__);
//						dump($max);
						print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"$disabled/> ";
//						print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
						$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
						print "{$hint_str}</TD></TR>\n\t";
						}
					break;

					case "BLOB":
					case "blob":
					case "VAR_STRING":
					case "STRING":
					case "CHAR":
					case "char":
					case "str":
					case "string":
					case "text":
					case "longtext":
					if (substr($arrayattr[$i][1], 0, 4) == "enum") {				// yes, parse enums
						$temp = substr($arrayattr[$i][1], 4);
						$temparray = explode( ",", $temp);
						print "<TD VALIGN='baseline'><B>&nbsp;";
						$drops = array("'","(",")");

						for ($j = 0; $j < count($temparray); $j++) {
							$temparray[$j] = str_replace($drops, "", $temparray[$j]);		// drop sgl quotes, parens
							$checked=($row[$i]==$temparray[$j])? " CHECKED": "";
							print "$temparray[$j]<INPUT TYPE='radio' NAME=\"frm_" . mysqli_fetch_field_direct($result, $i)->name . "\" VALUE= \"$temparray[$j]\" $checked/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							}				// end for ($j = 0;
						$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
						print "</B>{$hint_str}</TD></TR>\n\t";
						}				// end if ("enum")
					else {
						if ($max> $text_type_max) {
							print "\n\t\t<TD><TEXTAREA NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "' COLS='90' ROWS = '1' STYLE='vertical-align:text-top;'>{$row[$i]}</TEXTAREA> ";
							}
						else {
//							$max = max($max, safe_strlen($row[$i]));				// 9/5/08
//					echo __LINE__ . " " . $max . "<BR />";

							print "\n\t\t<TD><INPUT MAXLENGTH='{$max}' SIZE='{$max}' type='text' NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "' VALUE='{$row[$i]}' onChange = 'this.value=JSfnTrim(this.value)'$disabled/> ";
							}
//						print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
						$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
						print "{$hint_str}</TD></TR>\n\t";
			 			}
					break;

				case "real":
				case "DOUBLE":
				case "double":
					$max = 12;
					print "<TD><INPUT MAXLENGTH={$max} SIZE={$max} TYPE=text NAME='frm_" . mysqli_fetch_field_direct($result, $i)->name . "' VALUE='{$row[$i]}' onChange = 'this.value=JSfnTrim(this.value)'/> ";
//					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type_compat($result, $i)] . "</SPAN>": "";
					$hint_str = (empty($comments_ar[mysqli_fetch_field_direct($result, $i)->name]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysqli_fetch_field_direct($result, $i)->name]}" ;
					print "{$hint_str}</TD></TR>\n\t";
					break;

				default:
					print __line__ . mysql_field_type_compat($result, $i)  . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
				}					// end switch
			}				// end else
		}		// end for ($i = ...

	unset ($result);
?>
	<TR><TD COLSPAN="99" ALIGN="center"><BR />
	<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='Javascript: document.retform.submit();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
	<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.u.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
	<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='JSfnCheckInput(document.u, this );'><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
	<SPAN ID='del_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="if (confirm('Please confirm DELETE action')) {document.u.func.value='d'; document.u.submit();}"><SPAN STYLE='float: left;'><?php print get_text("Delete");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
	</TD></TR>
	</FORM>
	</TD></TR></TABLE>
<?php
	}				// end else

	break;		// end Update ==========================

	case "pc":													// Process 'Create record' data =================
	$query = "DESCRIBE `$mysql_prefix$tablename` ";				// 6/21/10
	$result = db_query($query);			// use $result for meta-information reference
	$types = array();
	while($row = stripslashes_deep($result->fetch_assoc())) {		// major while () - 3/25/09
		$types[$row['Field']] = $row['Type'];
		}
//	dump($types);

	$temp1 = $temp2 = "";
	foreach ($_POST as $VarName=>$VarValue) {
		if (substr($VarName, 0, 4)=="frm_") {
			$temp1 .= "`" . substr($VarName, 4, 99) . "`,";		// field names - note tic's = 6/21/10
			$temp2 .= (((boolean) strpos( " double float real ",  $types[substr($VarName, 4, 99)])) && (empty($VarValue)))?
					"NULL,":
					fnQuote_Smart(trim($VarValue)) . ",";
			}
		}		// end foreach () ...
																// now drop trailing comma
	$query  = "INSERT INTO $mysql_prefix$tablename (" . substr($temp1, 0, (safe_strlen($temp1) - 1)) . ") VALUES (" . substr($temp2, 0, (safe_strlen($temp2) - 1)) . ")";
	$result = db_query($query);
	unset ($result);
?>
	<CENTER><BR /><BR />
	<FORM NAME="pc" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>">
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r"/>  <!-- retrieve -->
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0>
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->

	</FORM>
<?php
	$query = "SELECT MAX(id) AS id FROM `$mysql_prefix$tablename`" ;
	$result = db_query($query);
	$row = $result->fetch_array();
	$id = $row['id'];
	unset ($result);
//	break;

	case "v":		// View detail	========================
	$id = (array_key_exists('id', $_POST)) ? sanitize_int($_POST['id']) : $id;
	$query ="SELECT * FROM `$mysql_prefix" . $tablename . "` WHERE `" . $indexname . "` = ? LIMIT 1";
	$result = db_query($query, [$id]);
	$row = $result->fetch_array();
	if (!(isset($srch_str))) {$srch_str="";}	// 10/31/10
	$ary_srch = explode ("|", $srch_str);		// 9/13/10
	$srch_term = isset($ary_srch) ? array_shift ($ary_srch): "";

	$lineno = 0;

	$the_custom = "./tables/v_" . $tablename . ".php";				// 12/26/08
	if (file_exists ( $the_custom)){
		require_once($the_custom) ;
		$custom	= TRUE;
		} else {

?>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print $id ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby ;?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/>

<?php
	print "<TABLE BORDER=\"0\" ALIGN=\"center\" >";
	if ($func == "pc") 	{print "<TR CLASS=\"even\" VALIGN=\"top\"><TD COLSPAN=\"2\"  ALIGN=\"CENTER\"><FONT SIZE=\"+1\">New '$tablename' entry accepted.</FONT></TD></TR>";}
	else				{print "<TR CLASS=\"even\" VALIGN=\"top\"><TD COLSPAN=\"2\" ALIGN=\"CENTER\"><FONT SIZE=\"+1\">Table '$tablename' - View Entry</FONT></TD></TR>";}
	print "<TR><TD>&nbsp;</TD></TR>";
	for ($i = 0; $i < $result->field_count; $i++) {
		$lineno++;
		print "\n\t<TR CLASS=" . $evenodd [$lineno % 2] . " VALIGN=\"top\"><TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace( "_", " ", ucfirst(mysqli_fetch_field_direct($result, $i)->name)) . ":</TD><TD>";
		if ((mysqli_fetch_field_direct($result, $i)->name != $indexname)
				&& (strtolower(substr(mysqli_fetch_field_direct($result, $i)->name, -$id_lg)) == $FK_id)
				&& ($temp = fnSubTableExists(mysqli_fetch_field_direct($result, $i)->name))
				&& (intval ($row[$i]) > 0)) {							// prepare to replace with indexed values - 9/15/10


			$query ="SELECT * FROM `$mysql_prefix" . $temp . "` WHERE `" . $indexname . "` = ? LIMIT 1";
			$temp_result = db_query($query, [intval($row[$i])]);
			if ($temp_result->num_rows > 0) 	{										// defined?
				$temp_row = $temp_result->fetch_array();						// yes
				dump($temp_row);
				print (($temp == 'user')&&(array_key_exists('user', $temp_row)))? $temp_row['user']: $temp_row[1];		// 12/12/11 - special case
				} else { 																	// no
				print $row[$i];
				}
			unset ($temp_result);
			unset ($temp_row);
			} elseif (isset($fk_overrides[mysqli_fetch_field_direct($result, $i)->name]) && intval($row[$i]) > 0) {	// 3/14/26 - FK override for view
			$fko = $fk_overrides[mysqli_fetch_field_direct($result, $i)->name];
			$fko_query = "SELECT {$fko['display_expr']} AS `{$fko['display_alias']}` FROM `{$mysql_prefix}{$fko['table']}` WHERE `id` = ? LIMIT 1";
			$fko_result = db_query($fko_query, [intval($row[$i])]);
			if ($fko_result->num_rows > 0) {
				$fko_row = $fko_result->fetch_array();
				print $fko_row[$fko['display_alias']];
			} else {
				print $row[$i];
			}
			unset($fko_result);
			} else {
			$empty = (safe_strlen($row[$i])== 0) ?  " - empty" : $empty = "";

			switch (mysql_field_type_compat($result, $i)) {
				case "datetime":
				case "date":
				case "timestamp":
					print $row[$i];
					break;

				case "time":
					print substr($row[$i],0,5);
					break;

				case "blob":
					print ($showblobastext)? $row[$i] : "Blob". $empty ;
					break;
				default:
					print $row[$i] ;
				}	// end switch
			}
		print "</TD></TR>";
		} 			// end for ($i = ... )
	unset ($result);
	if ($func == "pc") 	{
?>
		<TR>
			<TD COLSPAN="2" ALIGN="CENTER">
			<BR />
				<SPAN ID='another_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.pc.func.value='c';document.pc.submit();"><SPAN STYLE='float: left;'><?php print get_text("Another");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</TD>
		</TR>
<?php
		}
	}			// end else ...
?>

	<TR>
		<TD COLSPAN="2" ALIGN="center">
		<BR />
		<SPAN ID='cont_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='Javascript: document.retform.submit();'><SPAN STYLE='float: left;'><?php print get_text("Continue");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
<?php
		$disallow = is_in_use($row['id']);				// 10/20/09	- 2/25/10 - 11/9/10
		if ((!($disallow) && ($can_edit))) {			// 3/19/11
?>
			<SPAN ID='del_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnToFunc ('d', '<?php print $id ?>');"><SPAN STYLE='float: left;'><?php print get_text("Delete entry");?></SPAN><IMG STYLE='float: right;' SRC='./images/delete.png' BORDER=0></SPAN>
<?php
			}
		if ($can_edit) {							// 3/19/11
?>
			<SPAN ID='edit_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnToFunc('u', '<?php print $id ?>');"><SPAN STYLE='float: left;'><?php print get_text("Edit entry");?></SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN>
<?php
			}
?>
	</TR>
	</FORM>
	</TABLE>
<?php
	break;		// end View ==========================


	case "pu":																	// Process Update 	================
	$query = "DESCRIBE `$mysql_prefix$tablename` ";
	$result = db_query($query);			// use $result for meta-information reference
	$types = array();
	while($row = stripslashes_deep($result->fetch_assoc())) {		// major while () - 3/25/09
		$types[$row['Field']] = $row['Type'];
		}

	$query = "UPDATE $mysql_prefix$tablename SET ";
	foreach ($_POST as $VarName=>$VarValue) {
		if ((substr($VarName, 0, 4)=="frm_") && ($VarName != $indexname)) {
			$safe_col_name = preg_replace('/[^a-zA-Z0-9_]/', '', substr($VarName, 4, 99));  // sanitize column name from POST key
			if (((boolean) strpos( " double float real ",  $types[substr($VarName, 4, 99)])) && (empty($VarValue))){
				$query .= "`" . $safe_col_name . "` = NULL,";
				} else {
				$query .= "`" . $safe_col_name . "` = " . fnQuote_Smart($VarValue) . ",";		// 6/21/10
				}

			}		// field names - note tic's
		}
	$pu_id = sanitize_int($_POST['id']);
	$query = substr($query, 0, (safe_strlen($query) - 1)) . " WHERE `" .$indexname . "` = " . $pu_id . " LIMIT 1";
	$result = db_query($query);
	unset ($result);
//	dump ($query);
?>
	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD ALIGN="CENTER"><FONT SIZE="+1">Update complete</TD></TR>
	<TR><TD>&nbsp;</TD></TR></TABLE>
	<CENTER>
	<FORM NAME="pu" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r"/>  <!-- retrieve -->
	<SPAN id='cont_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.pu.submit();"><SPAN STYLE='float: left;'><?php print get_text("Continue");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->

	</FORM>
<?php
	break;		// end Process Update 	=================

	case "d":																		// Delete ===========================
	$del_id = sanitize_int($_POST['id']);
	$query ="DELETE FROM $mysql_prefix$tablename WHERE `" . $indexname . "` = ? LIMIT 1";
	$result = db_query($query, [$del_id]);
	unset ($result);
?>
	<TABLE BORDER="0" ALIGN="center">
	<TR><TD>&nbsp;</TD></TR>
	<TR CLASS="even" VALIGN="top"><TD ALIGN="CENTER"><FONT SIZE="+1">&nbsp;&nbsp;Item Deleted&nbsp;&nbsp;</TD></TR>
	<TR><TD>&nbsp;</TD></TR></TABLE><CENTER>

	<FORM NAME="d" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r"/>  <!-- retrieve -->
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	<SPAN id='cont_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.d.submit();"><SPAN STYLE='float: left;'><?php print get_text("Continue");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
	</FORM>
<?php
	break;		// end Delete ======================

	case "p":	// Properties  ===========================

?>
	<TABLE BORDER="0" ALIGN="center">
		<TR>
			<TD>&nbsp;</TD>
		</TR>
		<TR CLASS="even" VALIGN="top">
			<TD ALIGN="CENTER" COLSPAN = "2"><FONT SIZE="+1">&nbsp;&nbsp;Field Properties - Table  '<?php print str_replace( "_", " ", ucfirst($tablename)) ; ?>'&nbsp;&nbsp;</TD>
		</TR>
	</TABLE><BR /><BR />
<?php
	$query ="SHOW FULL COLUMNS FROM `$mysql_prefix$tablename`";
	$result = db_query($query);
	print "<table align='CENTER'>";
	print "<TR class='plain_listheader'>";
	print "<TH class='plain_listheader text text_left'>Field</TH>";
	print "<TH class='plain_listheader text text_left'>Type</TH>";
	print "<TH class='plain_listheader text text_left'>Collation</TH>";
	print "<TH class='plain_listheader text text_left'>Null</TH>";
	print "<TH class='plain_listheader text text_left'>Key</TH>";
	print "<TH class='plain_listheader text text_left'>Default</TH>";
	print "<TH class='plain_listheader text text_left'>Extra</TH>";
	print "<TH class='plain_listheader text text_left' COLSPAN=99>Permissions</TH>";
	print "</TR>";
	$lineno = 0;
	while ($row = $result->fetch_array())  {									// write each data row
		$lineno++;
		print "<TR VALIGN='top' CLASS='" . $evenodd [$lineno % 2] . "'>";		// alternate line bg colors
		for($i = 0; $i < count($row); $i++){										// each column
			if((isset($row[$i])) && (!is_null($row[$i]))) {print "<TD class='text text_left' style='padding-right: 20px;'>$row[$i]</TD>";} else {print "<TD class='text text_left' style='padding-right: 20px;'>&nbsp;</TD>";}
			}
		print "</TR>";

		}			// end while ($row =
	print "</table>\n";
	unset ($result);

?>
	<FORM NAME="p" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r"/>
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/>
	<CENTER>
	<BR>
	<SPAN id='cont_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.retform.func.value='r'; document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Continue");?></SPAN>&nbsp;<IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
<?php
		if ($can_edit) {
?>
			<SPAN id='add_but' CLASS='plain text' style='float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.p.func.value='c'; document.p.submit();"><SPAN STYLE='float: left;'>Add new <?php print str_replace( "_", " ", ucfirst($tablename)); ?> entry </SPAN>&nbsp;&nbsp;<IMG STYLE='float: right;' SRC='./images/plus_small.png' BORDER=0></SPAN>
<?php
			}
?>

	</FORM>
	<?php

	break;		// end Properties ======================

	case "s":	// Search table ====================

?>
	<SCRIPT>
		var str = sep = "";

		function validate_s(theForm) {			// search values test
			for (i=0; i< theForm.elements.length; i++) {
				if (theForm.elements[i].type == 'checkbox' && (theForm.elements[i].checked==true)) {
					str += (sep + theForm.elements[i].name);		// leading separator
					sep = "|";
					}
				}

			var errmsg="";
			if (JSfnTrim(theForm.argument.value) == "")	{errmsg+= "\tSearch value is required\n";}
			if (str == "")								{errmsg+= "\tField(s) to search is required\n" ;}

			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
//				alert(theForm.argument.value + sep + str);
				document.l.srch_str.value = JSfnTrim(theForm.argument.value) + sep + str;		// search argument is HO value
				document.l.tablename.value="<?php print $tablename;?>";
				document.l.submit();															// good to go
				}
			}		// end function validate_s()

	</SCRIPT>

<?php
	print "<BR /><BR />";

	$query = "DESCRIBE `$mysql_prefix$tablename`";
	$result = db_query($query);			// use $result for meta-information reference
	$types = array();
	$i=0;

	$name = array();
	while($row = stripslashes_deep($result->fetch_assoc())) {		// major while () - 3/25/09
		$temp = explode("(", $row['Type']);
		if ((trim($temp[0] == "text"))|| (trim($temp[0] == "tinytext"))|| (trim($temp[0] == "varchar"))) {
			$name[] = $row['Field'];
			}
		}				// end while(...)
	sort($name);
	$cols = 1;							// no. of columns in the list presentation
	if (count($name)>100) $cols = 4;
	if (count($name)>20) $cols = 3;
	if (count($name)>10) $cols = 2;

	$perCol = (integer)(ceil(count($name)/$cols)); 					// entries per col

	echo "<TABLE ID='outer' ALIGN='center' CELLSPACING = 0 BORDER=0>";
?>
<TR CLASS='even' COLSPAN=99 >
	<TD CLASS='td_label'>Search for:</TD><TD COLSPAN= <?php print ($cols-1);?>>
		<FORM NAME='s' METHOD = 'post' ACTION = '<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='text' 		NAME = 'argument' SIZE = 32 VALUE=''>
		<INPUT TYPE='hidden' 	NAME = 'fields' VALUE=''> <!-- slash-separated list of names -->
		</TD></TR>
<?php
	echo "<TR CLASS='odd'><TD COLSPAN=99 ALIGN='left'><H3>In:</H3></TD></TR>\n";
	echo "<TR VALIGN='top'><TD COLSPAN=2>";

	$out_str = "<TABLE ALIGN='left' CELLSPACING = 0  border=0 width=100%>\n";

	$i = 0;
	$j = 1;
	for ($n=0; $n<count($name); $n++) {

		$i++;
		$out_str .= "<TR CLASS='{$evenodd[($i+1) % 2]}'><TD CLASS= 'td_label'>
			<INPUT TYPE='checkbox' NAME = '{$name[$n]}' VALUE='{$name[$n]}' STYLE = 'display:inline; margin-left:20px'> {$name[$n]}</TD></TR>\n";
		if ($i == $perCol){		// start new column?
			$i=0;
			$out_str .=  "</TABLE>\n";
			echo $out_str;
			echo "</TD><TD>";		// outer table
			$out_str = "\n<TABLE BORDER=0 ALIGN='center'  CELLSPACING = 0 >";
			}
		$j++;
		}		// end for ($n... )
	$out_str .=  "</TABLE>";
	echo $out_str;
	echo "</TD></TR>";
?>

<SCRIPT>
function do_check(the_bool) {
	for(i=0;i<document.s.elements.length;i++) {
		if (document.s.elements[i].type =='checkbox') {document.s.elements[i].checked=the_bool;}
		}
	}
</SCRIPT>
	<TR CLASS= "<?php print $evenodd[($i+1) % 2];?>">
		<TD COLSPAN=99 ALIGN='center'><BR />
			<SPAN id='check_off' CLASS='plain text' style='width: 100px; display: none; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('check_on').style.display='inline-block'; $('check_off').style.display='none'; 	do_check(false);"><SPAN STYLE='float: left;'><?php print get_text("Uncheck All");?></SPAN><IMG STYLE='float: right;' SRC='./images/unselect_all_small.png' BORDER=0></SPAN>
			<SPAN id='check_on' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('check_on').style.display='none'; 	 $('check_off').style.display='inline-block'; do_check(true);"><SPAN STYLE='float: left;'><?php print get_text("Check All");?></SPAN><IMG STYLE='float: right;' SRC='./images/select_all_small.png' BORDER=0></SPAN>
			<BR />
			<BR />
		</TD>
	</TR>
	<TR CLASS='<?php print $evenodd[($i) % 2];?>'>
		<TD COLSPAN=99 ALIGN='center'><BR />
			<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.s.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
			<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate_s(document.s);'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r'; document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
		</TD>
	</TR>
	</TABLE>
	</TABLE>
	</FORM>
	<?php
	print "</TABLE></TABLE></FORM>\n";
	break;			// end Search ======================

	case "l":	// Select table ====================

	print "<BR /><BR /><BR /><BR /><BR /><BR /><BR />";
//	fnTables();											// 1/6/2013
	break;
	default:

	print __LINE__ . $func . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
	}	// end switch ($func)

//if (($func == "r") || ($func == "p")) {			// limit visibility
//	fnTables();
//	}

function fnTables () {							/// displays tables comprising db $mysql_db
	global $mysql_db, $mysql_prefix, $FK_id, $id_lg, $primaries, $secondaries;
	$ctrp=$ctrs=0;
	$pref_lgth = safe_strlen($mysql_prefix);

	$sql = "SHOW TABLES ";
	$result = db_query($sql);	// $mysql_db
	while ($row = $result->fetch_row()) {
		$sql ="SELECT * FROM `$row[0]` LIMIT 1";
		$result2 = db_query($sql);	// $mysql_db
		$row2 = $result2->fetch_array();
		$gotit = FALSE;
		for ($i = 0; $i < $result2->field_count; $i++) {			// look at each field - substr ( string, start, 999)

			if (strtolower(substr(mysqli_fetch_field_direct($result2, $i)->name, -$id_lg)) == $FK_id) {	// find any implied key
//				$primaries[$ctrp] = $row[0];							// a primary
				$primaries[$ctrp] = substr($row[0], $pref_lgth, 999);							// a primary - 8/20/09
				$ctrp++;
				$gotit = TRUE;
				break;
				}
			}
		if (!$gotit) {				// not a primary
			$secondaries[$ctrs] = $row[0];
			$secondaries[$ctrs] = substr($row[0], $pref_lgth, 999);
			$ctrs++;
			}
		}
	mysqli_free_result($result);
	print "<BR /><BR /><BR /><TABLE ALIGN=\"center\" BORDER=0><TR CLASS=\"even\"><TD ALIGN=\"center\" CLASS=\"td_link\" COLSPAN=\"2\"><FONT SIZE=\"+1\">Available '$mysql_db ' tables</FONT></TD></TR>";

	print "<TR VALIGN=\"top\"><TD><B><nobr>Primary Tables:</nobr></B></TD><TD ALIGN='center'>";
	for($i = 0; $i < $ctrp; $i++) {
		print "<A HREF=\"#\" ONCLICK=\"Javascript: document.l.tablename.value='$primaries[$i]'; document.l.indexname.value='id'; document.l.submit();\"> $primaries[$i] </A>&nbsp;&nbsp;&nbsp;\n";
		}
	print "</TD></TR>\n\n<TR>\n<TD>&nbsp;</TD></TR>\n\n<TR VALIGN=\"top\">\n<TD><A HREF='#'onclick = \"Javascript:JSfnShowit('support')\"> <B>Support:</A>&nbsp;&nbsp;</B></TD>\n<TD ALIGN='center'><SPAN ID='support' STYLE = 'visibility: hidden'>";
	for($i = 0; $i < $ctrs; $i++) {
		print "<A HREF=\"#\" ONCLICK=\"Javascript: document.l.tablename.value='$secondaries[$i]'; document.l.indexname.value='id'; document.l.submit();\"> $secondaries[$i] </A>&nbsp;&nbsp;&nbsp;\n";
		}
	print "<A HREF='#'onclick = \"Javascript:JSfnHideit('support')\"> <B>:Hide</A></SPAN></TD></TR></TABLE>";
	}
?>
<!-- ----------Common--------------- -->
<FORM NAME="l" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>">
<INPUT TYPE = "hidden" NAME="tablename" VALUE=""/>
<INPUT TYPE = "hidden" NAME="indexname" VALUE="id"/>
<INPUT TYPE = "hidden" NAME="sortby"	VALUE="id"/>
<INPUT TYPE = "hidden" NAME="sortdir"	VALUE="0"/>
<INPUT TYPE = "hidden" NAME="func" 		VALUE="r"/>
<INPUT TYPE = "hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->

</FORM>
<CENTER><BR /><BR /><BR />

<FORM NAME="retform" method="post" action="<?php print $_SERVER['PHP_SELF'] ?>">
<INPUT TYPE = "hidden" NAME="tablename" VALUE="<?php print $tablename; ?>"/>
<INPUT TYPE = "hidden" NAME="indexname" VALUE="<?php print $indexname; ?>"/>
<INPUT TYPE = "hidden" NAME="sortby"	VALUE="<?php print $indexname ;?>"/>
<INPUT TYPE = "hidden" NAME="sortdir"	VALUE=0 />
<INPUT TYPE = "hidden" NAME="func" 		VALUE="r"/>
<INPUT TYPE = "hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->

</FORM>
</CENTER>

<?php
if ($calstuff!="") {

	print "<link rel='stylesheet' type='text/css' media='all' href='./js/calendar-win2k-cold-1.css' title='win2k-cold-1' />\n";
	print "<script type='application/x-javascript' src='./js/calendar.js'></script>\n";
	print "<script type='application/x-javascript' src='./js/calendar-en.js'></script>\n";
	print "<script type='application/x-javascript' src='./js/calendar-setup.js\'></script>\n";		// 10/10/09

	print "<SCRIPT TYPE=\"application/x-javascript\">\n";
	print $calstuff;
	print "\n</SCRIPT>\n";
	}
?>
<CENTER>
<FORM NAME = 'finform' METHOD = 'post' ACTION = 'config.php'>
</FORM>
<FORM NAME = 'mainform' METHOD = 'post' ACTION = 'main.php'>
</FORM>
<SPAN ID='fin_but' CLASS='plain text' style='width: 150px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.finform.submit();"><SPAN STYLE='float: left;'>Finished - To Config</SPAN><IMG STYLE='float: right;' SRC='./images/config_small.png' BORDER=0></SPAN>
<SPAN ID='main_but' CLASS='plain text' style='width: 150px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.mainform.submit();"><SPAN STYLE='float: left;'>Finished - To Situation</SPAN><IMG STYLE='float: right;' SRC='./images/t_small.png' BORDER=0></SPAN>
</CENTER>
<SCRIPT>
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
tablewidth = viewportwidth * .8;
if($('listView')) {$('listView').style.width = tablewidth + "px";}
if($('navbuttons')) {$('navbuttons').style.width = viewportwidth * .8 + "px";}
</SCRIPT>
</BODY>
</HTML>
<?php
} else {
Print "Not Authorised";
exit();		//	Exit gracefully with no view of DB if not admin.
}