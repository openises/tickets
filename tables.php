<?php
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
if (empty($_SESSION)) {				// 1/6/2013
	header("Location: index.php");
	}

require_once('./incs/functions.inc.php');
$query = "SET @@global.sql_mode= '';";		// 6/25/10
$result = mysql_query($query) ;

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
$istest=FALSE;
if ($istest) {
	dump($_POST);
	}
do_login(basename(__FILE__));	// 9/18/08
if(is_administrator()) {

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
$istest 			= TRUE;				// TRUE displays form variables for trouble-shooting atop each loaded page

/* maps irv_settings for use IF you are implementing maps */

$maps 				= TRUE;
$api_key			= "ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BQOqXXamPs-BOuxLXsFgzG1vgHGdBTx978MQ0RymVQmZOPJN5XuAFdftw";	// AS local opensara

$def_state			= "10";				// Florida
$def_county			= "58";				// Sarasota
$def_lat			= NULL;				// default center lattitude - if present, overrides county centroid 
$def_lon			= NULL;				// guess!
$radius				= 10;				// radius of circle on default center (miles)
$do_hints			= TRUE;				// if true, print data hints at input fields
if (($mysql_db=="")||($mysql_user=="")) {print "<br><br><br><br>" ; die(" - - - - - - - - - -  - - - - - - - - - - Please set values for both \$mysql_db and \$mysql_user in settings.inc.php! - - - - - - - - - - ");}

$FK_id = strtolower($key_str);			// set for case independence
$id_lg = strlen($FK_id);				// lgth of foreign key id string
$custom	= FALSE;						// custom processor in use
$can_edit = ((is_super()) || (is_administrator()));										// 3/19/11

if (!array_key_exists('func', $_POST)) {
	$func = "l";					// Select table, of C R U D or Select
	$tablename = "";				// set per user selection
	$indexname = "";				// set per schema 
	}

extract($_POST);

$sortby = (!(isset($sortby)) || empty($sortby))?		 "id" : $sortby; 
$sortdir = (!(isset($sortdir)) || empty($sortdir))?		 0 : $sortdir; 
$sortby = (!(isset($index)) || empty($index))?			 "id" : $index; 

function get_comments($the_table) {  				// returns array key=> name, value=> comment - 10/31/10
//	global $mysql_prefix;
	$_array = array();								// 12/15/10
	$query = "SHOW FULL COLUMNS FROM `$GLOBALS[mysql_prefix]{$the_table}`;";
//	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);		
	$result = mysql_query($query) ;			// use $result for meta-information reference
	if (!($result)) {return $_array;}		// 12/15/10
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// 
		$_array[$row['Field']] = $row['Comment'];
		}
	return $_array;
	}				// end function

function is_in_use($index_val) {	// 11/9/10 - return boolean based on whether the identified entry is in use
	global $tablename, $mysql_prefix;
	switch ($tablename) {		
		case "unit_types":	
			$the_table = $mysql_prefix . "responder";
			$query ="SELECT * FROM `$the_table` WHERE `type` = {$index_val} LIMIT 1";						// get in_row count only
			$res_test = mysql_query($query) or myerror(get_file(__FILE__), __LINE__, 'mysql_error', $query);
			$in_use = (mysql_num_rows($res_test)>0);
		    break;
		case "un_status":	
			$the_table = $mysql_prefix . "responder";
			$query ="SELECT * FROM `$the_table` WHERE `un_status_id` = {$index_val} LIMIT 1";						// get in_row count only
			$res_test = mysql_query($query) or myerror(get_file(__FILE__), __LINE__, 'mysql_error', $query);
			$in_use = ((mysql_num_rows($res_test)>0) || (intval ($index_val)==1));	// 11/2/09
		    break;
		case "fac_status":	
			$the_table = $mysql_prefix . "facilities";
			$query ="SELECT * FROM `$the_table` WHERE `status_id` = {$index_val} LIMIT 1";						// get in_row count only
			$res_test = mysql_query($query) or myerror(get_file(__FILE__), __LINE__, 'mysql_error', $query);
			$in_use = (mysql_num_rows($res_test)>0);
		    break;
		case "fac_types":			
			$the_table = $mysql_prefix . "facilities";
			$query ="SELECT * FROM `$the_table` WHERE `type` = {$index_val} LIMIT 1";						// get in_row count only
			$res_test = mysql_query($query) or myerror(get_file(__FILE__), __LINE__, 'mysql_error', $query);
			$in_use = (mysql_num_rows($res_test)>0);
		    break;
		case "in_types":						// 
			$the_table = $mysql_prefix . "ticket";
			$query ="SELECT * FROM `$the_table` WHERE `in_types_id` = {$index_val} LIMIT 1";						// get in_row count only
			$res_test = mysql_query($query) or myerror(get_file(__FILE__), __LINE__, 'mysql_error', $query);
			$in_use = (mysql_num_rows($res_test)>0);
		    break;						    
		case "region":						// 6/10/11
			$the_table = $mysql_prefix . "allocates";
			$query ="SELECT * FROM `$the_table` WHERE `group` = {$index_val} LIMIT 1";						// get in_row count only
			$res_test = mysql_query($query) or myerror(get_file(__FILE__), __LINE__, 'mysql_error', $query);
			$in_use = (mysql_num_rows($res_test)>0);
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
if ((isset($tablename)) && (!isset($indexname))) {
	$query ="SELECT * FROM `$mysql_prefix$tablename` LIMIT 1";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	while ($property = mysql_fetch_field($result)) {		// through each field this table
		if (($property->primary_key) == 1){
			$indexname = $property->name;					// IMPORTANT!
			unset ($result);
			break;
			}
		}
	unset($result);
	}


function fnQuote_Smart($value) {    // Stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    	}
    if (!is_numeric($value)) {    // Quote if not integer
        $value = "'" . mysql_real_escape_string($value) . "'";
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

function fnDatabaseExists($dbName) {					//Verifies existence of a MySQL database
	global $mysql_host, $mysql_user, $mysql_passwd;
	$bRetVal = FALSE;
	if ($oConn = @mysql_connect($mysql_host, $mysql_user, $mysql_passwd )) {
		$result = mysql_list_dbs($oConn);
		while ($row=mysql_fetch_array($result, MYSQL_NUM)) {
			if ($row[0] ==  $dbName)
			$bRetVal = TRUE;
			}
		mysql_free_result($result);
//		mysql_close($oConn);
		}
	return ($bRetVal);
	}

function fnSubTableExists($TableName) {					// returns name of substitution table, or FALSE
	global $id_lg, $primaries, $secondaries ;
	$thename = substr( $TableName, 0, strlen($TableName)-$id_lg);				// high-order portion possible base name?
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
$result = mysql_query($sql) or die ("DB Error: " . $mysql_db . " inaccessible\n");	// $mysql_db  
while ($row = mysql_fetch_row($result)) {
	$sql ="SELECT * FROM `$row[0]` LIMIT 1";
	$result2 = mysql_query($sql) or die ("DB Error: " . $mysql_db . " inaccessible\n");	// $mysql_db  
	$row2 = mysql_fetch_array($result2);
	$gotit = FALSE;
	for ($i = 0; $i < mysql_num_fields($result2); $i++) {			// look at each field
		if (strtolower(substr(mysql_field_name($result2, $i), -$id_lg)) == $FK_id) {	// find any foreign key
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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
</HEAD>
<!--  onFocus="LL_showinfo(1)" onBlur="LL_hideallinfo()" -->
<STYLE>
/* comment */
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
//	print "<SCRIPT type=\"text/javascript\" src=\"RegExpValidate.js\"></SCRIPT>";
	}
?>

<SCRIPT>
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
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row_un = stripslashes_deep(mysql_fetch_assoc($result))) {
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
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row_fac = stripslashes_deep(mysql_fetch_assoc($result))) {
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

	function JSfnToSort(thevalue) {				// column name
		var currsort = '<?php print $sortby; ?>';
		if (thevalue == currsort) 	{
			document.r.sortdir.value = parseInt(document.r.sortdir.value);
			document.r.sortdir.value++;
			document.r.sortdir.value = (document.r.sortdir.value) % 2; // toggle direction
			}
		else {
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
			$query ="SELECT * FROM `$mysql_prefix$tablename` LIMIT 1";			// check value where possible - by mysql_field_type
			$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);		// check presence/absence
			while ($property = mysql_fetch_field($result)) {
				$thename = $property->name;
				$thetype = $property->type; 
				print "\t\ttypes['frm_' + '$thename'] = '$thetype';\n";
				if (($property->not_null) !=0) {								//
					print "\t\tmands['frm_' + '$thename'] = '$thetype';\n";
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
				switch (types[myform.elements[i].name]){						// name to types array
					case "blob":
						if (!ck_blob(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "string":
						if (!ck_string(myform.elements[i].value))	{JSfnAddError();}
						break;
					case "real":
						if (!ck_real(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "int":
						if (!ck_int(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "timestamp":
						if (!ck_blob(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "date":
					case "datetime":
						if (!ck_date(myform.elements[i].value))		{JSfnAddError();}
						break;
					case "time":
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
<?php $the_table = (strlen($tablename)>0)? $tablename : "tbd"; ?>
<CENTER><BR /><H3>Table: <SPAN STYLE="background: white">&nbsp;<?php print $the_table; ?>&nbsp;</SPAN> <BR /></H3></CENTER>
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
	$resultattr = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	$arrayattr = array();
	$i = 0;
	while ($rowattr = mysql_fetch_array($resultattr))  {							// write each data row attr
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
//		print __LINE__ . "<BR />";
		require_once($the_custom) ;
		$custom	= TRUE;
		}
	else {
		if ($fill_from_last) {
			$the_id = $indexname;													// for form pre-filling
			$query = "SELECT * FROM `$mysql_prefix$tablename` WHERE `$the_id` = (SELECT MAX(`$the_id`) FROM `$mysql_prefix$tablename`)";
			$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
			if (mysql_affected_rows()==0) 	{$row = NULL ;}
			else							{$row = mysql_fetch_array($result);}
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
		$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
		$lineno = 0;
	
		for ($i = 0; $i < mysql_num_fields($result); $i++) {
			if ((!is_null($row)) && ($fill_from_last)) 	{$last_data = $row[$i]; $class="clean";}
			else 										{$last_data = ""; 		$class="dirty";}

			if (substr(mysql_field_name($result, $i), 0, 1 ) =="_") {				// 12/20/08
				switch (mysql_field_name($result, $i)) {
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
					print "<TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace( "_", " ", ucfirst(mysql_field_name($result, $i))) . ":</TD>";
					switch (mysql_field_type($result, $i)) {
						case "datetime":
						case "date":
						case "timestamp":
							fnDoCal($i);				// generates JS Calendar stuff
							$max = 16;
							$value = date($date_out_format);
							print "<TD><INPUT MAXLENGTH=$max ID=\"fd$i\" SIZE=$max type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$value\" onChange = \"this.value=JSfnTrim(this.value)\"/>";
							fnCalButt ($i);
//							print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
							$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
							print "{$hint_str}</TD></TR>\n\t"; 
							break;
							
						case "time":
							$value = date ("H:i");
							$max = 5;
							print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$value\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//							print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
							$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
							print "{$hint_str}</TD></TR>\n\t"; 
							break;
				
						case "int":
							$gotit = FALSE;
							if (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id) {			// maybe dropdown
								$lgth = strlen(mysql_field_name($result, $i));
								$thetable = substr( mysql_field_name($result, $i),0, $lgth-$id_lg) ;			// extract corresponding table name
								if (mysql_table_exists($thetable)) {											// does non-empty table exist?
									$query ="SELECT * FROM `$mysql_prefix$thetable` LIMIT 1";					// order will be by column 1, name unk
									$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
									$thecolumn = mysql_field_name($temp_result, 1)	;							// column 1 field name		
									
									$query ="SELECT * FROM `$mysql_prefix$thetable` ORDER BY `$thecolumn` ASC";
									$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
									print "\t\t<TD><SELECT NAME='frm_" . mysql_field_name($result, $i) . "'>\n\t\t<OPTION VALUE='0' selected>Select one</OPTION>\n";
									while ($temp_row = mysql_fetch_array($temp_result))  {							// each row
										print "\t\t<OPTION VALUE='" . trim($temp_row[0]) . "'>" . trim($temp_row[1]) . "</OPTION>\n";	
										}
									print "\t\t</SELECT>";
//									print ($do_hints)? "<SPAN class='$mand_opt' >(" . mysql_affected_rows() . ")</SPAN>": "";
									$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
									print "{$hint_str}</TD></TR>\n\t"; 
									unset ($temp_result);
									$gotit = TRUE;
									}											// end if (mysql_table_exists($thetable)) ...
								}										// end maybe dropdown
							if (!$gotit) {
								print "<TD><INPUT ID=\"ID$i\" MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//								print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
								$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
								print "{$hint_str}</TD></TR>\n\t"; 
								}				// end if (!$gotit)
							break;
				
						case "blob":
						case "string":
							if (substr($arrayattr[$i][1], 0, 4) == "enum") {				// yes, parse enums
								$temp = substr($arrayattr[$i][1], 4);
								$temparray = explode( ",", $temp);
								print "<TD VALIGN='baseline'>&nbsp;<B>";
								$drops = array("'","(",")");
								$default = (isset($arrayattr[$i][4]))? $arrayattr[$i][4] : "";
								for ($j = 0; $j < count($temparray); $j++) {
									$temparray[$j] = str_replace($drops, "", $temparray[$j]);		// drop sgl quotes, parens
									$checked=($temparray[$j]==$default)? " CHECKED ": "";		
									print "$temparray[$j]<INPUT TYPE='radio' NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE= \"$temparray[$j]\" $checked STYLE='vertical-align:middle;'/>&nbsp;&nbsp;&nbsp;&nbsp;";
									}				// end for ($j = 0;
								$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
								print "</B>{$hint_str}</TD></TR>\n\t"; 
								}				// end if ("enum")
							else	{					
								if (($max> $text_type_max) || (mysql_field_type($result, $i)=="blob")){
									print "\n\t\t<TD><TEXTAREA ID='ID{$i}' CLASS='{$class}' NAME='frm_" . mysql_field_name($result, $i) . "' COLS='90' ROWS = '3' onFocus=\"JSfnChangeClass(this.id, 'dirty');\" STYLE='vertical-align:text-top;'>{$last_data}</TEXTAREA> ";
									}
								else {
									print "\n\t\t<TD><INPUT  ID=\"ID$i\" CLASS=\"$class\" MAXLENGTH=\"$max\" SIZE=\"$max\" type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"> ";
									}
//								print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
								$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
								print "{$hint_str}</TD></TR>\n\t"; 
			 					}				// end else
							break;
				
						case "real":
							$max = 12;
							print "<TD><INPUT ID=\"ID$i\" MAXLENGTH=$max SIZE=$max TYPE=text NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//							print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
							$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
							print "{$hint_str}</TD></TR>\n\t"; 
							break;
				
					
						default:
							print __line__ . mysql_field_type($result, $i)  . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
						}					// end switch 
					}		// end if ... != "auto_increment") 
				}		// end else ...
			}		// end for ($i = ...
		unset ($result);
	
	?>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="reset"		VALUE="Reset"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/> 
		
		</TD></TR>
		</FORM>
		</TD></TR></TABLE>
	<?php
		}

	break;														// end 'Create record'
	
	case "r":																			// Retrieve/List =================
	function fnLinkTDm ( $theclass, $theid, $thestring, $the_in_use) {		// returns <td ... /td>
		global $tablename, $mysql_prefix, $disallow, $can_edit;				// 2/25/10, 3/19/11
//		$disallow = (($tablename == $mysql_prefix . "un_status") && ($theid==1));

		$the_js_func = ($disallow)? "JSfnDisallow" : "JSfnToFunc" ;		// 10/20/09

		if ($the_in_use) {																	// 10/13/09
			$on_click = "onclick = \"alert('DELETE disallowed for this item');\"";
			}
		else {
			$on_click = "onclick = \"{$the_js_func}('d', '" . $theid. "');\"";
			}
	
		$breakat = 24;
		if (strlen($thestring) > $breakat) {
			$return = " CLASS='" . $theclass . "' onmouseover =\"document.getElementById('b" . $theid . "').style.visibility='hidden' ; document.getElementById('c" . $theid . "').style.visibility='visible';\" onmouseout = \"document.getElementById('c" . $theid . "').style.visibility='hidden'; document.getElementById('b" . $theid . "').style.visibility='visible' ; \" >\n";
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
			}
		else {
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
		return "<TD ALIGN='right'" . $return . $thestring ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>\n";
		}			// end function fnLinkTDm ()

			

	$dirs = array (" ASC ", " DESC ");
	$arrowdir = array ("<IMG SRC='./markers/up.png'>", "<IMG SRC='./markers/down.png'>");			// sort direction arrows
	if (!isset($sortby)) {
//	if ($sortby == "") {
		$sortby = $indexname; $sortdir = 0;										// default sort by is in $indexname
		}
	if (!isset ($numrows))	{
		$query ="SELECT * FROM `$mysql_prefix$tablename` ";						// get row count only
		$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
		$numrows = mysql_affected_rows();
		unset ($result);
		$pageNum = 1;
		}
	else {$pageNum = $page; }
	$offset = ($pageNum - 1) * $rowsPerPage;									// calculate 0-based offset	from 1-based page no.
	$special = "`id`, `name`, `office`, `street`, `city`, `state`, `zip`, `phone`, `status`, `contract`, `email`, `website`, `type`, `chief`, `chief_title`, `emergency_contact`, `emergency_phone`, `contact_via`, `contact_via_use`, `admin_contact`, `admin_phone`, `hours`, `contact_date`";
	$select ="SELECT * FROM `$mysql_prefix$tablename` ";
	
	$order_by = " ORDER BY `{$sortby}` {$dirs[$sortdir]}";

	$limit = " LIMIT $offset, $rowsPerPage";

	if (empty($srch_str))  {$where = "";}
	else {
		$where = " WHERE (";
		$ary_srch = explode ("|", $srch_str);
		$the_or = "";
		for ($i=1; $i< count($ary_srch); $i++) {
			$where .= "{$the_or} (`{$ary_srch[$i]}` REGEXP '{$ary_srch[0]}')";
			$the_or = " OR ";
			}				// end for ($i=...)
		$where .= ")";
		
		}		// end if/else 
		
	$query = $select . $where . $order_by . $limit ;

	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	$row_count = mysql_affected_rows();

	print "<TABLE ALIGN=\"center\" BORDER=\"0\" CELLPADDING=\"2\">\n";

	if (mysql_affected_rows() == 0) {
		$page="";
		$message = (empty($where))? "Table '" . str_replace( "_", " ", ucfirst($tablename))  . "' is empty!" :
		"No matches in '{$tablename}' search for '{$ary_srch[0]}' ";
		print "<TR VALIGN='top'><TD ALIGN='center' CLASS='header'><BR /><BR /><BR />{$message}<BR /><BR /><BR /><BR /><BR /></TD></TR>";
		}
	else {				// we got rows
		$maxPage = ceil($numrows/$rowsPerPage);						// # pages => $maxPage
		$prev = $next = $nav = '';									// initially empty
		$plural = ($row_count==1)? "" : "s";
		$head1 = "<TR CLASS = 'odd' valign='TOP'><TH COLSPAN=99 ALIGN='center'>{$row_count} record{$plural} "." <FONT SIZE=\"-2\">&nbsp;&nbsp;(mouseover ";
		$head2 = "<TR CLASS = 'even' VALIGN='top'>";
		$cols = mysql_num_fields($result);
		$subst = array();											// will hold substitution values for colnames like 'what_id'

		for ($i = 0; $i < $cols; $i++) {							// write table header, etc.
			if ((mysql_field_name($result, $i) != $indexname) && (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)
						&& ($temp = fnSubTableExists(mysql_field_name($result, $i)))) {							// prepare to replace with indexed values
				$query = "SELECT * FROM $mysql_prefix$temp";	 
//				echo __line__ . $query . "<br>";
				$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
				while ($temp_row = mysql_fetch_array($temp_result))  {											// each row/value => $substitutions array
					if (($temp == 'user') && (array_key_exists('user', $temp_row))) {							// 12/12/11 - special case table user
						$subst[fnSubTableExists(mysql_field_name($result, $i))][$temp_row[0]] = $temp_row['user'];		// assign value to column_name[index]  value
						}
					else {
						$subst[fnSubTableExists(mysql_field_name($result, $i))][$temp_row[0]] = $temp_row[1];		// assign value to column_name[index]  value
						}
					}						// end while ($temp_row = ...
				unset ($temp_result);
				}

			$thecolumn = mysql_field_name($result, $links_col);		// column name
			$arrow = (mysql_field_name($result, $i) == $sortby) ? $arrowdir[$sortdir] : "";
			$theclass=($i==$links_col)? " CLASS='ul'": "";
			$head2 .= "<TH VALIGN='top'$theclass onClick =\"JSfnToSort('" . mysql_field_name($result, $i) . "')\" >" . str_replace( "_", " ", ucfirst(mysql_field_name($result, $i))) . " $arrow</TH>\n";
			}
		$head2 .= "</TR>\n";										// end table heading
		print $head1 . "<U>" . str_replace( "_", " ", ucfirst($thecolumn)) . "</U> data for functions)</FONT></TH></TR>\n" . $head2;
		$lineno = 0;
		$srch_term = isset($ary_srch) ? array_shift ($ary_srch): "";

		while ($row = mysql_fetch_array($result))  {			// write each data row - highlight($term, $string)
			$lineno++;
			$on_click = " onClick=JSfnToFunc('v',{$row[$indexname]});";		// 9/15/10
			
			print "<TR valign=\"top\" CLASS=\"" . $evenodd [$lineno % 2] . "\">";			// alternate line bg colors
			for($i = 0; $i < $cols; $i++){													// each column
						
				$in_use = FALSE;				// test for index value in use - 10/13/09
				if ($i==0) {					// index column only
					$in_use = is_in_use($row[$i]);								// 11/9/10
					}
				
				$lgth = strlen(mysql_field_name($result, $i));								// shortened column name
				if (isset($row[$i])) {														// not empty

					if (mysql_field_type($result, $i)=="time") { 
						print "<TD CLASS='mylink' {$on_click} >" . substr($row[$i],0,5) . "</TD>\n";
						}
					else {
						if ($i == $links_col) {												// 'name' or 'descr' or default
							print fnLinkTDm ( "mylink" , $row[0] , $row[$i] , $in_use );	// generate JS function link - assume id as column 0
							}
						else {
							if ((mysql_field_name($result, $i) != $indexname) && (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)) {	// check terminal 3 chars
								$thearray = substr(mysql_field_name($result, $i), 0, $lgth-$id_lg);	// extract table name
								if (isset($subst[$thearray][$row[$i]])) {					// defined?
									$thedata = $subst[$thearray][$row[$i]];					// yes - pull substitution data	
									}
								else {
									$thedata = $row[$i]; 
									}								// no substitution data		
								}
							else { 									// not substitution or date
								$thedata = (strlen($row[$i])>$text_list_max)? substr($row[$i], 0,$text_list_max) . "&hellip;" : $row[$i];
								}
							if(($tablename=="unit_types") && (mysql_field_name($result, $i)=="icon")) {					// 1/29/09
								$thedata = "<IMG SRC='./our_icons/" . $sm_icons[$row[$i]] . "'>";				// display icon image
								}
							if(($tablename=="fac_types") && (mysql_field_name($result, $i)=="icon")) {					// 1/29/09
								$thedata = "<IMG SRC='./our_icons/" . $sm_icons[$row[$i]] . "'>";				// display icon image
								}

							$out_str = ((isset($ary_srch)) && (in_array ( mysql_field_name($result, $i),$ary_srch )))?
								highlight($srch_term, $thedata): $thedata;
								
							print "<TD CLASS='mylink'{$on_click} >" . $out_str . "</TD>\n";			// type not "datetime" and name not "descript"
							}		// end else ...
						}	// end not "datetime"
					}	// end if (isset() ...
				else {							// not set
					print "<TD CLASS='mylink' {$on_click}>" . $i . "</TD>\n";							// empty
					}			//  not set
				}			// end for($i = 1 ...
			unset ($row);
			print "</TR>\n";
			}			// end while ...
		unset ($result);
		unset ($subst);

		for($page = 1; $page <= $maxPage; $page++) {  				// set link to each page no.
			$nav .= ($page == $pageNum)? " <font color='red'><b>$page</b></font>&nbsp;&nbsp;" : " <a href=\"Javascript: JSfnToNav($page)\">$page</a>&nbsp;&nbsp;" ;
			}

		if ($pageNum > 1) {											// create prior/next links
			$page = $pageNum - 1;
			$prev = "&nbsp;&nbsp;<a href='Javascript: JSfnToNav($page)'><IMG SRC='./markers/prev.png' height='16' width= '16' border='0'></a> ";
			}

		if ($pageNum < $maxPage) {									// if not on last
			$page = $pageNum + 1;
			$next = " <a href='Javascript: JSfnToNav($page)'><IMG SRC='./markers/next.png'  height='16' width= '16' border='0'></a>&nbsp;&nbsp;&nbsp;&nbsp;";	// ************
			}
		print "<TR VALIGN=\"top\"><TD ALIGN=\"center\" COLSPAN=\"99\"><BR />" . $prev . $nav . $next . "</TD></TR>";			// print the navigation links
		}					// end got rows
	print "</TABLE><TABLE ALIGN=\"center\" >";
	print "<TR><TD ALIGN=\"center\" COLSPAN=\"99\">";
?>
	<FORM NAME="r" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename; ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="page" 		VALUE="<?php print $page; ?>"/>
	<INPUT TYPE="hidden" NAME="numrows"	 	VALUE="<?php print $numrows; ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE="<?php print $sortdir; ?>"/>
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r">
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	
	<CENTER><BR>
<?php
//dump (__LINE__);
//dump($row_count);
if (($row_count > 0) || (array_key_exists('srch_str', $_POST))) {
?>
	<INPUT TYPE="button" VALUE="Search <?php print ucfirst($tablename); ?>" 								onClick = "Javascript: document.retform.func.value='s'; document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp; <!-- 9/12/10 -->
<?php
	}		// end if ($row_count > 0)
?>	

	<INPUT TYPE="button" VALUE="<?php print ucfirst($tablename); ?> Properties" 							onClick = "Javascript: document.retform.func.value='p'; document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
<?php				// 3/19/11
		if ($can_edit) {
?>		
	<INPUT TYPE="button" VALUE="Add new <?php print str_replace( "_", " ", ucfirst($tablename)); ?> entry" 	onclick= "this.form.func.value='c'; this.form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php				// 3/19/11
		}
?>	
	
<!--	<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/> 1/28/09 -->
	</FORM>
	</TD></TR></TABLE>
<?php
	break;											// end Retrieve  ==================================


case "u":	// =======================================  Update 	=======================================
	$comments_ar = get_comments($tablename);					// array of name, comment

	$query = "DESCRIBE `$mysql_prefix$tablename` ";		// 6/21/10
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);			// use $result for meta-information reference
	$types = array();
	$i = 0;
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// major while () - 3/25/09
		$types[$i] = $row['Type'];
		$i++;
		}
//	dump($types);
	
	$query ="SELECT * FROM `$mysql_prefix$tablename` WHERE `$indexname` = \"$id\" LIMIT 1";					// target row
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);			// use $result for meta-information reference
	$row = mysql_fetch_array($result);																		// $row has data
	$lineno = 0;															// for alternating row colors

	$the_custom = "./tables/u_" . $tablename . ".php";				// 12/20/08
	if (file_exists ( $the_custom)){
//		print __LINE__ . "<BR />";
		$custom	= TRUE;
		require_once($the_custom) ;
		}
	else {
?>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print $id ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	

	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table '<?php print $tablename?>' - Update/Delete Entry</FONT></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
<?php
	for ($i = 0; $i < mysql_num_fields($result); $i++) {
		$max = get_digs($types[$i]);											// max input lgth per types array - 6/21/10
//		dump(__LINE__);
//		dump($max);
		if (substr(mysql_field_name($result, $i), 0, 1 ) =="_") {				// 12/20/08
			switch (mysql_field_name($result, $i)) {
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
//					$value = date("Y-m-d H:i:00");			// ex: 2008-12-18 01:46:18;
					$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		// 11/8/09
					print "\t\t<INPUT ID=\"fd$i\" type=\"hidden\" NAME=\"frm__on\" VALUE=\"$now\" />\n";
					break;
				}				// end switch ()
			}				// end if (substr())

		else {			
			$disabled = ($arrayattr[$i][5] == "auto_increment")? " disabled" : "";
			$lineno++;
			$mand_opt =($arrayattr[$i][2]!= "YES")? "warn" : "opt";
			print "<TR VALIGN=\"baseline\" CLASS=\"" .$evenodd [$lineno % 2]  . "\">";
			print "<TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace ( "_", " ", ucfirst(mysql_field_name($result, $i))) . ":</TD>";
			
			switch (mysql_field_type($result, $i)) {
				case "datetime":
				case "date":
				case "timestamp":
					$max = 16;
					$value=date($date_out_format);
//					echo __LINE__ . " " . $max . "<BR />";
					print "<TD><INPUT MAXLENGTH=$max SIZE=$max type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/>";
//					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
					print "{$hint_str}</TD></TR>\n\t"; 
					break;
			
				case "time":
					$max = 5;
					print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
//					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
					print "{$hint_str}</TD></TR>\n\t"; 
					break;
		
				case "int":
				case "bigint":
					$gotit = FALSE;
					if ((mysql_field_name($result, $i) != $indexname) && (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)) {			// maybe dropdown
						$lgth = strlen(mysql_field_name($result, $i));
						$thetable = substr( mysql_field_name($result, $i),0, $lgth-$id_lg) ;			// extract corresponding table name
						if (mysql_table_exists($thetable)) {											// does table exist?
							$query ="SELECT * FROM `$mysql_prefix$thetable` LIMIT 1";					// order will be by 2nd column
							$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
							$thecolumn = mysql_field_name($temp_result, 1)	;							// field name 2nd column 
							
							$query ="SELECT * FROM `$mysql_prefix$thetable` ORDER BY `$thecolumn` ASC";	// get option values
							$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
							print "\t\t<TD><SELECT NAME='frm_" . mysql_field_name($result, $i) . "'>\n";
							if ($row[mysql_field_name($result, $i)]=='0') {print "\t\t<OPTION VALUE='0' selected>Select</OPTION>\n" ;}				// no selection made
							while ($sel_row = mysql_fetch_array($temp_result))  {								// each row - assume 2nd column has values
								$selected = ($sel_row['id'] == $row[mysql_field_name($result, $i)])? " selected" : "";
								print "\t\t<OPTION VALUE='" . $sel_row[0] . "'" . $selected  . " >" . $sel_row[1] . "</OPTION>\n";		// *************
								}
					$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
							print "\t\t</SELECT>";
							print "{$hint_str}</TD></TR>\n\t"; 
							unset ($temp_result);
							$gotit = TRUE;
							}											// end if (mysql_table_exists($thetable)) ...
						}										// end maybe dropdown
					if (!$gotit) {
//						dump(__LINE__);
//						dump($max);
						print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"$disabled/> ";
//						print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
						$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
						print "{$hint_str}</TD></TR>\n\t"; 
						}
					break;
			
				case "blob":
				case "string":
					if (substr($arrayattr[$i][1], 0, 4) == "enum") {				// yes, parse enums
						$temp = substr($arrayattr[$i][1], 4);
						$temparray = explode( ",", $temp);
						print "<TD VALIGN='baseline'><B>&nbsp;";
						$drops = array("'","(",")");
						
						for ($j = 0; $j < count($temparray); $j++) {
							$temparray[$j] = str_replace($drops, "", $temparray[$j]);		// drop sgl quotes, parens
							$checked=($row[$i]==$temparray[$j])? " CHECKED": "";		
							print "$temparray[$j]<INPUT TYPE='radio' NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE= \"$temparray[$j]\" $checked/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							}				// end for ($j = 0;
						$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
						print "</B>{$hint_str}</TD></TR>\n\t"; 
						}				// end if ("enum")
					else {							
						if ($max> $text_type_max) {
							print "\n\t\t<TD><TEXTAREA NAME='frm_" . mysql_field_name($result, $i) . "' COLS='90' ROWS = '1' STYLE='vertical-align:text-top;'>{$row[$i]}</TEXTAREA> ";
							}
						else {
//							$max = max($max, strlen($row[$i]));				// 9/5/08
//					echo __LINE__ . " " . $max . "<BR />";

							print "\n\t\t<TD><INPUT MAXLENGTH='{$max}' SIZE='{$max}' type='text' NAME='frm_" . mysql_field_name($result, $i) . "' VALUE='{$row[$i]}' onChange = 'this.value=JSfnTrim(this.value)'$disabled/> ";
							}
//						print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
						$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
						print "{$hint_str}</TD></TR>\n\t"; 
			 			}
					break;
			
				case "real":
					$max = 12;
					print "<TD><INPUT MAXLENGTH={$max} SIZE={$max} TYPE=text NAME='frm_" . mysql_field_name($result, $i) . "' VALUE='{$row[$i]}' onChange = 'this.value=JSfnTrim(this.value)'/> ";
//					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					$hint_str = (empty($comments_ar[mysql_field_name($result, $i)]))? "" : "&nbsp;&laquo;&nbsp;<SPAN CLASS='hint'>{$comments_ar[mysql_field_name($result, $i)]}" ;
					print "{$hint_str}</TD></TR>\n\t"; 
					break;
			
				default:
					print __line__ . mysql_field_type($result, $i)  . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
				}					// end switch 
			}				// end else
		}		// end for ($i = ...

	unset ($result);
?>
	<TR><TD COLSPAN="99" ALIGN="center">
	<BR />
	<INPUT TYPE="button" 	VALUE="Cancel" onClick = "Javascript: document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="reset" 	VALUE="Reset"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this )"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="del_but" VALUE="Delete this entry" onclick="if (confirm('Please confirm DELETE action')) {this.form.func.value='d'; this.form.submit();}"/></TD></TR>
	</FORM>
	</TD></TR></TABLE>
<?php
	}				// end else 

	break;		// end Update ==========================
	
	case "pc":													// Process 'Create record' data =================
	$query = "DESCRIBE `$mysql_prefix$tablename` ";				// 6/21/10
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);			// use $result for meta-information reference
	$types = array();
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// major while () - 3/25/09
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
	$query  = "INSERT INTO $mysql_prefix$tablename (" . substr($temp1, 0, (strlen($temp1) - 1)) . ") VALUES (" . substr($temp2, 0, (strlen($temp2) - 1)) . ")";
//	dump ($query);
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
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
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	$row = mysql_fetch_array($result);
	$id = $row['id'];

	
	unset ($result);

//	break;

	case "v":		// View detail	========================

	$query ="SELECT * FROM `$mysql_prefix$tablename` WHERE `$indexname` = \"$id\" LIMIT 1";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	$row = mysql_fetch_array($result);
	if (!(isset($srch_str))) {$srch_str="";}	// 10/31/10
	$ary_srch = explode ("|", $srch_str);		// 9/13/10
	$srch_term = isset($ary_srch) ? array_shift ($ary_srch): "";

	$lineno = 0;

	$the_custom = "./tables/v_" . $tablename . ".php";				// 12/26/08
	if (file_exists ( $the_custom)){
//		print __LINE__ . "<BR />";
		require_once($the_custom) ;
		$custom	= TRUE;
		}
	else {

?>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print $id ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby ;?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	
<?php
	print "<TABLE BORDER=\"0\" ALIGN=\"center\" >";
	if ($func == "pc") 	{print "<TR CLASS=\"even\" VALIGN=\"top\"><TD COLSPAN=\"2\"  ALIGN=\"CENTER\"><FONT SIZE=\"+1\">New '$tablename' entry accepted.</FONT></TD></TR>";}
	else				{print "<TR CLASS=\"even\" VALIGN=\"top\"><TD COLSPAN=\"2\" ALIGN=\"CENTER\"><FONT SIZE=\"+1\">Table '$tablename' - View Entry</FONT></TD></TR>";}
	print "<TR><TD>&nbsp;</TD></TR>";
	for ($i = 0; $i < mysql_num_fields($result); $i++) {
		$lineno++;
		print "\n\t<TR CLASS=" . $evenodd [$lineno % 2] . " VALIGN=\"top\"><TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace( "_", " ", ucfirst(mysql_field_name($result, $i))) . ":</TD><TD>";
//		if ((mysql_field_name($result, $i) != $indexname) && (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)
//						&& ($temp = fnSubTableExists(mysql_field_name($result, $i)))) {							// prepare to replace with indexed values
		if ((mysql_field_name($result, $i) != $indexname) 
				&& (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)
				&& ($temp = fnSubTableExists(mysql_field_name($result, $i)))
				&& (intval ($row[$i]) > 0)) {							// prepare to replace with indexed values - 9/15/10


			$query ="SELECT * FROM `$mysql_prefix$temp` WHERE `$indexname` = $row[$i] LIMIT 1";				
			$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query); 
			if (mysql_affected_rows()>0) 	{										// defined?
				$temp_row = mysql_fetch_array($temp_result);						// yes
				print (($temp == 'user')&&(array_key_exists('user', $temp_row)))? $temp_row['user']: $temp_row[1];		// 12/12/11 - special case
//				print $temp_row[1];			// value, whatever name
				}
			else { 																	// no
				print $row[$i];
				}
			unset ($temp_result);
			unset ($temp_row);
			}
		else {
			$empty = (strlen($row[$i])== 0) ?  " - empty" : $empty = "";

			switch (mysql_field_type($result, $i)) {
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
		print "<TR><TD COLSPAN=\"2\" ALIGN=\"CENTER\"><BR /><INPUT TYPE=\"button\" VALUE=\"Another\" onclick=\"document.pc.func.value='c';document.pc.submit()\";/></TD></TR>";
		}
	}			// end else ... 
?>

	<TR><TD COLSPAN="2" ALIGN="center">
	<BR />
	<INPUT TYPE="button" VALUE="Continue" onClick = "Javascript: document.retform.submit();"/>
<?php
	$disallow = is_in_use($row['id']);				// 10/20/09	- 2/25/10 - 11/9/10
	if ((!($disallow) && ($can_edit))) {			// 3/19/11
?>	
	<INPUT TYPE="button" STYLE = 'margin-left:10px' NAME="el_but" VALUE="Delete this entry" onclick="JSfnToFunc ('d', '<?php print $id ?>');"/>
<?php
		}
	if ($can_edit) {							// 3/19/11
?>	
	<INPUT TYPE="button" STYLE = 'margin-left:10px' NAME="edl_but" VALUE="Edit this entry" onclick="JSfnToFunc('u', '<?php print $id ?>');"/></TD>
<?php
		}
?>		
	</TR>
	</FORM>
	</TD></TR></TABLE>
<?php
	break;		// end View ==========================


	case "pu":																	// Process Update 	================
	$query = "DESCRIBE `$mysql_prefix$tablename` ";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);			// use $result for meta-information reference
	$types = array();
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// major while () - 3/25/09
		$types[$row['Field']] = $row['Type'];
		}
//	dump($types);

	$query = "UPDATE $mysql_prefix$tablename SET ";
	foreach ($_POST as $VarName=>$VarValue) {
		if ((substr($VarName, 0, 4)=="frm_") && ($VarName != $indexname)) { 
			if (((boolean) strpos( " double float real ",  $types[substr($VarName, 4, 99)])) && (empty($VarValue))){
				$query .= "`" . substr($VarName, 4, 99) . "` = NULL,";
				}
			else {
				$query .= "`" . substr($VarName, 4, 99) . "` = " . fnQuote_Smart($VarValue) . ",";		// 6/21/10
				}

			}		// field names - note tic's
		}
	$query = substr($query, 0, (strlen($query) - 1)) . " WHERE `" .$indexname . "` = $id LIMIT 1";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
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
	<INPUT TYPE="button" VALUE="Continue" onclick="this.form.submit();"/>
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	
	</FORM>
<?php
	break;		// end Process Update 	=================

	case "d":																		// Delete ===========================
	$query ="DELETE FROM $mysql_prefix$tablename WHERE `" . $indexname . "` = $id LIMIT 1";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
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
	
	<INPUT TYPE="button" VALUE="Continue" onclick="this.form.submit();"/>
	</FORM>
<?php
	break;		// end Delete ======================

	case "p":	// Properties  ===========================
	
//	$query = "SHOW FULL FIELDS FROM $tablename";			// build array of field captions
//	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename(__FILE__), __LINE__);
//	$captions = array();
//	while($row = stripslashes_deep(mysql_fetch_array($result))) {
//		$captions [$row['Field']] = $row['Comment'];
//		print "\tjs_captions['" .$row['Field'] . "']='" .$row['Comment'] . "';\n";
//		}
		
?>
	<TABLE BORDER="0" ALIGN="center">
	<TR><TD>&nbsp;</TD></TR>
	<TR CLASS="even" VALIGN="top"><TD ALIGN="CENTER" COLSPAN = "2"><FONT SIZE="+1">&nbsp;&nbsp;Field Properties - Table  '<?php print str_replace( "_", " ", ucfirst($tablename)) ; ?>'&nbsp;&nbsp;</TD></TR></TABLE>
<?php
	$query ="SELECT * FROM `$mysql_prefix$tablename` LIMIT 1";

	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	print "\n<table align=\"CENTER\" BORDER=\"0\">";
	print "<TR VALIGN=\"top\" CLASS = \"even\"><TH>Field name</TH><TH>Field Type</TH><TH>Default value</TH><TH>Max length</TH><TH>Not NULL</TH><TH>Numeric Field</TH><TH>BLOB</TH><TH>Primary Key</TH><TH>Unique Key</TH><TH>Mutliple Key</TH><TH>Unsigned</TH><TH>Zero-filled</TH></TR>";
	$lineno = 0;

	while ($property = mysql_fetch_field($result)) {
		$lineno++;
		print "\n<TR valign=\"top\" CLASS=\"" . $evenodd [$lineno % 2] . "\">";			// alternate line bg colors
		print "<TD><B>" . $property->name . "</B></TD>";
		print "<TD>" . $property->type . "</TD>";
		print "<TD>" . $property->def . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->max_length . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->not_null . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->numeric . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->blob . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->primary_key . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->unique_key . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->multiple_key . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->unsigned . "</TD>";
		print "<TD ALIGN=\"center\">" . $property->zerofill . "</TD>";
			
		print "</TR>\n";
		}
	unset ($result);
	print "<TR><TD COLSPAN=\"99\">&nbsp;</TD></TR></TABLE>";

	$query ="SHOW FULL COLUMNS FROM `$mysql_prefix$tablename`";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	print "\n<table align=\"CENTER\" BORDER=\"0\">";
	print "\n<TR><TH>Field</TH><TH>Type</TH><TH>Null</TH><TH>Key</TH><TH COLSPAN=3>Default/Extra</TH></TR>";
	$lineno = 0;
	while ($row = mysql_fetch_array($result))  {									// write each data row
		$lineno++;
		print "\n<TR VALIGN=\"top\" CLASS=\"" . $evenodd [$lineno % 2] . "\">";		// alternate line bg colors
//		for($i = 0; $i < count($row)-1; $i++){										// each column
		for($i = 0; $i < count($row); $i++){										// each column
			if((isset($row[$i])) && (!is_null($row[$i]))) {print "<TD> $row[$i] </TD>";}
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
	<INPUT TYPE="hidden" NAME="func" 		VALUE="r"/>  <!-- retrieve -->
	<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
	
	<CENTER><BR><INPUT TYPE="button" 	VALUE="Continue" onClick = "Javascript: document.retform.func.value='r'; document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
<?php				// 3/19/11
		if ($can_edit) {
?>	
	<INPUT TYPE="button" VALUE="Add new <?php print str_replace( "_", " ", ucfirst($tablename)); ?> entry" onclick= "this.form.func.value='c'; this.form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);			// use $result for meta-information reference
	$types = array();
	$i=0;

	$name = array();
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// major while () - 3/25/09
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
	<TR CLASS= "<?php print $evenodd[($i+1) % 2];?>"><TD COLSPAN=99 ALIGN='center'><B>
	<SPAN ID="check_on"		STYLE = 'display:inline;text-decoration:underline;'	onclick = "$('check_on').style.display='none'; 	 $('check_off').style.display='inline'; do_check(true)">Check all</SPAN>
	<SPAN ID="check_off"  	STYLE = 'display:none;text-decoration:underline;'	onclick = "$('check_on').style.display='inline'; $('check_off').style.display='none'; 	do_check(false)">Un-check all</SPAN>
	</B><BR /><BR /></TD></TR>
<?php
	print "<TR CLASS='{$evenodd[($i) % 2]}'><TD COLSPAN=99 ALIGN='center'><BR />
			<INPUT TYPE='button' VALUE='Reset' 	onClick = 'this.form.reset()' >
			<INPUT TYPE='button' VALUE='Next' 	onClick = 'validate_s(this.form)' STYLE='margin-left:40PX;'>
			<INPUT TYPE='button' VALUE='Cancel' onClick = \"Javascript: document.retform.func.value='r'; document.retform.submit();\" STYLE='margin-left:40PX;'>	
		</TD></TR>";
		
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
	$pref_lgth = strlen($mysql_prefix);
	
	$sql = "SHOW TABLES ";
	$result = mysql_query($sql) or die ("DB Error: " . $mysql_db . " inaccessible\n");	// $mysql_db  
	while ($row = mysql_fetch_row($result)) {
		$sql ="SELECT * FROM `$row[0]` LIMIT 1";
		$result2 = mysql_query($sql) or die ("DB Error: " . $mysql_db . " inaccessible\n");	// $mysql_db  
		$row2 = mysql_fetch_array($result2);
		$gotit = FALSE;
		for ($i = 0; $i < mysql_num_fields($result2); $i++) {			// look at each field - substr ( string, start, 999)

			if (strtolower(substr(mysql_field_name($result2, $i), -$id_lg)) == $FK_id) {	// find any implied key
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
	mysql_free_result($result);
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
	print "<script type='text/javascript' src='./js/calendar.js'></script>\n";
	print "<script type='text/javascript' src='./js/calendar-en.js'></script>\n";
	print "<script type='text/javascript' src='./js/calendar-setup.js\'></script>\n";		// 10/10/09

	print "<SCRIPT TYPE=\"text/javascript\">\n";
	print $calstuff;
	print "\n</SCRIPT>\n";
	}
?>
<CENTER>
<FORM NAME = 'finform' METHOD = 'post' ACTION = 'config.php'>
<INPUT TYPE='button' VALUE = 'Finished' onClick = 'this.form.submit()'>
</FORM>
</BODY>
</HTML>
<?php
} else {
exit();		//	Exit gracefully with no view of DB if not admin.
}