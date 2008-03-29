<?php 
// Released Jul 23, 2006
// Enumerated types added Jul 20
// Dynarch JS Calendar functions added
// improvements to datatype 'time' handling
//
//	Made available under the terms of GNU General Public License (GPL) http://www.gnu.org/copyleft/gpl.html
//
//$tablename = "dispatches"; 
$tablename = "ticket"; 
$gmap=TRUE;
require_once('functions.inc.php'); 

$key_str			= "_id";			// FOREIGN KEY (parent_id) REFERENCES parent(id) relationship terminal string identifier 

/* cosmetic stuff from here - MAY  be changed */

$irving_title		= "Tables maintenence";
$rowsPerPage		= 20;				// determines number of rows displayed per page in listing
$showblobastext		= TRUE;				// change to FALSE if blobs are not to be displayed
$date_out_format	= 'Y-m-d H:i';		// well, date format - per php date syntax
//$date_out_format	= 'n/j/y H:i';		// ex: 5/25/06
$date_in_format		= 0;					// yyyy-mm-dd, per mMySQL standard
$links_col			= 0;				// in the listing display, this column sees the View/Edit/Delete function links
$text_type_max		= 90;				// text input fields exceeding this size limit will be treated as <textarea>
$text_list_max		= 32;				// text input fields exceeding this size limit will be treated as <textarea>
$fill_from_last		= FALSE;			// if set to TRUE, new recrods are populated from last created
$doUTM				= FALSE;			// if set, coord displays UTM
$istest 			= TRUE;				// if set to TRUE, displays form variables for trouble-shooting atope each loaded page

/* maps irv_settings for use IF you are implementing maps */

$maps 				= TRUE;
$api_key			= "ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BQOqXXamPs-BOuxLXsFgzG1vgHGdBTx978MQ0RymVQmZOPJN5XuAFdftw";	// AS local opensara

$def_state			= "10";				// Florida
$def_county			= "58";				// Sarasota
$def_lat			= NULL;				// default center lattitude - if present, overrides county centroid 
$def_lon			= NULL;				// guess!
$radius				= 10;				// radius of circle on default center (miles)
$do_hints			= FALSE;			// if true, print data hints at input fields

if (($mysql_db=="")||($mysql_user=="")) {print "<br><br><br><br>" ; die(" - - - - - - - - - -  - - - - - - - - - - Please set values for both \$mysql_db and \$mysql_user in settings.inc.php! - - - - - - - - - - ");}

$FK_id = strtolower($key_str);				// set for case independence
$id_lg = strlen($FK_id);					// lgth of foreign key id string

if (!empty($_GET)) extract($_GET);
    else if (!empty($HTTP_POST_VARS)) extract($HTTP_POST_VARS);

if (!array_key_exists('func', $_POST)) {
	$func = "r";					// Select table, of C R U D or Select
	$sortby="";						// controls sort direction
	}
$evenodd = array ("even", "odd");	// for table row colors
$hints = array("int"=>"numeric", "blob"=>"blob", "string"=>"text", "datetime"=>"date/time", "time"=>"time", "timestamp"=>"date/time", "date"=>"date", "real"=>"float'g pt.");
$primaries = array();				// table names
$secondaries = array();				// table names
$arDate_formats = array(array ("-",0, 1, 2), array ("/", 2, 0, 1));

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
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
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

<SCRIPT type="text/javascript">
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
	function Ck_Range(realin) {					// western and northern hemispheres only
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
				
					default :alert (types[myform.elements[i].name] + " ??");
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
</SCRIPT>


<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">

<BODY>
<?php $the_table = (strlen($tablename)>0)? $tablename : "tbd"; ?>
<CENTER><BR /><H3>Table: <SPAN STYLE="background: white">&nbsp;<?php print $the_table; ?>&nbsp;</SPAN> <BR /></H3></CENTER>
<FORM NAME="detail" METHOD="post" 	ACTION="<?php print $_SERVER['PHP_SELF'] ?>">
<INPUT TYPE="hidden" NAME="tablename" 	VALUE=""/>
<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
<INPUT TYPE="hidden" NAME="id"  		VALUE=""/>
<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
<INPUT TYPE="hidden" NAME="func"  		VALUE=""/>  <!-- retrieve details -->
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

switch ($func) {
	case "c":																	// Create record -- add Enums	enum('a','b','c')
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
		
		if ($arrayattr[$i][5]!= "auto_increment") {
			$lineno++;
			$mand_opt =($arrayattr[$i][2]!= "YES")? "warn" : "opt";						// identifies mandatory vs. optional input
			$max = ereg_replace("[^0-9]", "", $arrayattr[$i][1]);						// max input lgth per attrib's array
			print "<TR VALIGN=\"baseline\" CLASS=\"" .$evenodd [$lineno % 2]  . "\">";
			print "<TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace( "_", " ", ucfirst(mysql_field_name($result, $i))) . ":</TD>";
			switch (mysql_field_type($result, $i)) {
				case "datetime":
				case "timestamp":
				case "date":
				case "timestamp":
					fnDoCal($i);				// generates JS Calendar stuff
					$max = 16;
					$value = date($date_out_format);
					print "<TD><INPUT MAXLENGTH=$max ID=\"fd$i\" SIZE=$max type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$value\" onChange = \"this.value=JSfnTrim(this.value)\"/>";
					fnCalButt ($i);
					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					print "</TD></TR>\n\t"; 
					break;
					
				case "time":
					$value = date ("H:i");
					$max = 5;
					print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$value\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					print "</TD></TR>\n\t"; 
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
								$temp = (isset($temp_row[2]))? " - " . substr(trim($temp_row[2]), 0, 6) : ""; 
								print "\t\t<OPTION VALUE='" . trim($temp_row[0]) . "'>" . trim($temp_row[1]) . $temp . "</OPTION>\n";	
								}
							print "\t\t</SELECT>";
							print ($do_hints)? "<SPAN class='$mand_opt' >(" . mysql_affected_rows() . ")</SPAN>": "";
							print "</TD></TR>\n\t";
							unset ($temp_result);
							$gotit = TRUE;
							}											// end if (mysql_table_exists($thetable)) ...
						}										// end maybe dropdown
					if (!$gotit) {
						print "<TD><INPUT ID=\"ID$i\" MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
						print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
						print "</TD></TR>\n\t"; 
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
							print "$temparray[$j]<INPUT TYPE='radio' NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE= \"$temparray[$j]\" $checked/>&nbsp;&nbsp;&nbsp;&nbsp;";
							}				// end for ($j = 0;
						print "</TD></TR>\n\t"; 	
						}				// end if ("enum")
					else	{					
						if (($max> $text_type_max) || (mysql_field_type($result, $i)=="blob")){
							print "\n\t\t<TD><TEXTAREA ID=\"ID$i\" CLASS=\"$class\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" COLS=\"90\" ROWS = \"3\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" >$last_data</TEXTAREA> ";
							}
						else {
							print "\n\t\t<TD><INPUT  ID=\"ID$i\" CLASS=\"$class\" MAXLENGTH=\"$max\" SIZE=\"$max\" type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"> ";
							}
						print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
						print "</TD></TR>\n\t"; 
	 					}				// end else
					break;
		
				case "real":
					$max = 12;
					print "<TD><INPUT ID=\"ID$i\" MAXLENGTH=$max SIZE=$max TYPE=text NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$last_data\" onFocus=\"JSfnChangeClass(this.id, 'dirty');\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					print "</TD></TR>\n\t"; 
					break;
		
			
				default:
					print __line__ . mysql_field_type($result, $i)  . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
				}					// end switch 
			}		// end if ... != "auto_increment") 
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
	</td></tr></table>
<?php
	break;														// end 'Create record'

	case "r":																			// Retrieve/List =================
	function fnLinkTDm ( $theclass, $theid, $thestring) {		// returns <td ... /td>
		$breakat = 24;
		if (strlen($thestring) > $breakat) {
			$return = " CLASS='" . $theclass . "' onmouseover =\"document.getElementById('b" . $theid . "').style.visibility='hidden' ; document.getElementById('c" . $theid . "').style.visibility='visible';\" onmouseout = \"document.getElementById('c" . $theid . "').style.visibility='hidden'; document.getElementById('b" . $theid . "').style.visibility='visible' ; \" >\n";
			$return .= substr($thestring, 0, $breakat) . "<SPAN id=\"b" . $theid . "\" style=\"visibility:visible\">" ;
			$return .= substr($thestring, $breakat) . "</SPAN><SPAN id=\"c" . $theid . "\" style=\"visibility: hidden\">\n";
			$return .= ". . . <IMG SRC='markers/view.png' BORDER=0 TITLE = 'click to view this' onclick = \"JSfnToFunc('v', '" . $theid . "');\">";
			$return .= " | ";
			$return .= " <IMG SRC='markers/edit.png' BORDER=0 TITLE = 'click to edit this' onclick = \"JSfnToFunc('u', '" . $theid . "');\">";
			$return .= " | ";
			$return .= "<IMG SRC='markers/del.png' BORDER=0 TITLE = 'click to delete this' onclick = \"JSfnToFunc('d', '" . $theid. "');\">";
			$return .= " | </SPAN>\n";
			}
		else {
			$return = " CLASS='" . $theclass . "' onmouseover =\"document.getElementById('c" . $theid . "').style.visibility='visible';\" onmouseout = \"document.getElementById('c" . $theid . "').style.visibility='hidden'; \" >\n";
			$return .= "<SPAN id=\"c" . $theid . "\" style=\"visibility: hidden\">\n";
			$return .= " <IMG SRC='markers/view.png' BORDER=0 TITLE = 'click to view this' onclick = \"JSfnToFunc('v', '" . $theid . "');\">";
			$return .= " | ";
			$return .= "<IMG SRC='markers/edit.png' BORDER=0 TITLE = 'click to edit this' onclick = \"JSfnToFunc('u', '" . $theid . "');\">";
			$return .= " | ";
			$return .= "<IMG SRC='markers/del.png' BORDER=0 TITLE = 'click to delete this' onclick = \"JSfnToFunc('d', '" . $theid. "');\">";
			$return .= " | </SPAN>\n";
			}
		return "<TD " . $return . $thestring ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>\n";
		}			// end function fnLinkTDm ()

			

	$dirs = array (" ASC ", " DESC ");
	$arrowdir = array ("<IMG SRC='./markers/up.png'>", "<IMG SRC='./markers/down.png'>");			// sort direction arrows
	if ($sortby == "") {
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
	$select ="SELECT * FROM `$mysql_prefix$tablename` ORDER BY `$sortby` ". $dirs[$sortdir];
//	$select ="SELECT $special FROM `$mysql_prefix$tablename` ORDER BY `$sortby` ". $dirs[$sortdir];
	$limit = " LIMIT $offset, $rowsPerPage";
	$query = $select . $limit ;
//	echo $query;
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	print "<TABLE ALIGN=\"center\" BORDER=\"0\" CELLPADDING=\"2\">\n";

	if (mysql_affected_rows() == 0) {
		$page="";
		print "<TR><TD ALIGN='center' CLASS='header'><BR /><BR /><BR /><BR />Table '" . str_replace( "_", " ", ucfirst($tablename))  . "' is empty!<BR /><BR /><BR /><BR /></TD></TR>";
		}
	else {				// we got rows
		$maxPage = ceil($numrows/$rowsPerPage);						// # pages => $maxPage
		$prev = $next = $nav = '';									// initially empty
		$head1 = "<TR CLASS = 'odd'><TH COLSPAN=99 ALIGN='center'>" . $numrows ." records "." <FONT SIZE=\"-2\">&nbsp;&nbsp;(mouseover ";
		$head2 = "<TR CLASS = 'even'>";
		$cols = mysql_num_fields($result);
		$subst = array();											// will hold substitution values for colnames like 'what_id'

		for ($i = 0; $i < $cols; $i++) {							// write table header, etc.
			if ((mysql_field_name($result, $i) != $indexname) && (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)
						&& ($temp = fnSubTableExists(mysql_field_name($result, $i)))) {							// prepare to replace with indexed values
				$query = "SELECT * FROM $mysql_prefix$temp";	 
//				echo __line__ . $query . "<br>";
				$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
				while ($temp_row = mysql_fetch_array($temp_result))  {											// each row/value => $substitutions array
					$subst[fnSubTableExists(mysql_field_name($result, $i))][$temp_row[0]] = $temp_row[1];		// assign value to column_name[index]  value
					}						// end while ($temp_row = ...
				unset ($temp_result);
				}

			$thecolumn = mysql_field_name($result, $links_col);		// column name
			$arrow = (mysql_field_name($result, $i) == $sortby) ? $arrowdir[$sortdir] : "";
			$theclass=($i==$links_col)? " CLASS='ul'": "";
			$head2 .= "<TH$theclass onClick =\"JSfnToSort('" . mysql_field_name($result, $i) . "')\" >" . str_replace( "_", " ", ucfirst(mysql_field_name($result, $i))) . " $arrow</TH>\n";
			}
		$head2 .= "</TR>\n";										// end table heading
		print $head1 . "<U>" . str_replace( "_", " ", ucfirst($thecolumn)) . "</U> data for functions)</FONT></TH></TR>\n" . $head2;
		$lineno = 0;
		while ($row = mysql_fetch_array($result))  {										// write each data row
			$lineno++;
			print "<TR valign=\"bottom\" CLASS=\"" . $evenodd [$lineno % 2] . "\">";			// alternate line bg colors
			for($i = 0; $i < $cols; $i++){													// each column
				$lgth = strlen(mysql_field_name($result, $i));								// shortened column name
				if (isset($row[$i])) {														// not empty
					if (mysql_field_type($result, $i)=="datetime") {						// if type is "datetime" do date format
						print "<TD CLASS=\"mylink\" >" . format_date(strtotime($row[$i])) . "</TD>";
						} 																	// end "datetime"
					elseif (mysql_field_type($result, $i)=="time") { 
						print "<TD CLASS=\"mylink\" >" . substr($row[$i],0,5) . "</TD>";
						}
					else {
						if ($i == $links_col) {												// 'name' or 'descr' or default
							print fnLinkTDm ( "mylink" , $row[0] , $row[$i]);				// generate JS function link - assume id as column 0
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
							print "<TD CLASS=\"mylink\" >" . $thedata . "</TD>";			// type not "datetime" and name not "descript"
							}		// end else ...
						}	// end not "datetime"
					}	// end if (isset() ...
				else {							// not set
					print "<TD CLASS=\"mylink\" >" . $i . "</TD>";							// empty
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
	<CENTER><BR><INPUT TYPE="button" 	VALUE=" <?php print ucfirst($tablename); ?> Properties" onClick = "Javascript: document.retform.func.value='p'; document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" VALUE="Add new <?php print str_replace( "_", " ", ucfirst($tablename)); ?> entry" onclick= "this.form.func.value='c'; this.form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</FORM>
	</TD></TR></TABLE>
<?php
	break;											// end Retrieve  ==================================


	case "u":										// Update 	=======================================
?>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print $id ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby; ?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->

	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table '<?php print $tablename?>' - Update/Delete Entry</FONT></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
<?php
	$query ="SELECT * FROM `$mysql_prefix$tablename` WHERE `$indexname` = \"$id\" LIMIT 1";					// target row
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);			// use $result for meta-information reference
	$row = mysql_fetch_array($result);																		// $row has data
	$lineno = 0;															// for alternating row colors
	for ($i = 0; $i < mysql_num_fields($result); $i++) {
		$disabled = ($arrayattr[$i][5] == "auto_increment")? " disabled" : "";
		$lineno++;
		$mand_opt =($arrayattr[$i][2]!= "YES")? "warn" : "opt";
		$max = ereg_replace("[^0-9]", "", $arrayattr[$i][1]);				// max input lgth per attrib's array
		print "<TR VALIGN=\"baseline\" CLASS=\"" .$evenodd [$lineno % 2]  . "\">";
		print "<TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace ( "_", " ", ucfirst(mysql_field_name($result, $i))) . ":</TD>";
		
		switch (mysql_field_type($result, $i)) {
			case "datetime":
			case "timestamp":
			case "date":
			case "timestamp":
				$max = 16;
				$value=date($date_out_format);
//				echo __LINE__ . $value . "<BR />";
				print "<TD><INPUT MAXLENGTH=$max SIZE=$max type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/>";
				print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
				print "</TD></TR>\n\t"; 
				break;
	
			case "time":
				$max = 5;
				print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
				print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
				print "</TD></TR>\n\t"; 
				break;

			case "int":
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
						print "\t\t</SELECT></TD></TR>\n\t";
						unset ($temp_result);
						$gotit = TRUE;
						}											// end if (mysql_table_exists($thetable)) ...
					}										// end maybe dropdown
				if (!$gotit) {
					print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE= \"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"$disabled/> ";
					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					print "</TD></TR>\n\t"; 
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
					print "</TD></TR>\n\t"; 	
					}				// end if ("enum")
				else {							
					if ($max> $text_type_max) {
						print "\n\t\t<TD><TEXTAREA NAME=\"frm_" . mysql_field_name($result, $i) . "\" COLS=\"90\" ROWS = \"1\">$row[$i]</TEXTAREA> ";
						}
					else {
						print "\n\t\t<TD><INPUT MAXLENGTH=\"$max\" SIZE=\"$max\" type=\"text\" NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"$disabled/> ";
						}
					print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
					print "</TD></TR>\n\t"; 
		 			}
				break;
		
			case "real":
				$max = 12;
				print "<TD><INPUT MAXLENGTH=$max SIZE=$max TYPE=text NAME=\"frm_" . mysql_field_name($result, $i) . "\" VALUE=\"$row[$i]\" onChange = \"this.value=JSfnTrim(this.value)\"/> ";
				print ($do_hints)? "<SPAN class='$mand_opt' >" . $hints[mysql_field_type($result, $i)] . "</SPAN>": "";
				print "</TD></TR>\n\t"; 
				break;
		
			default:
				print __line__ . mysql_field_type($result, $i)  . ": ERROR - ERROR - ERROR - ERROR - ERROR" ;
			}					// end switch 
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
	break;		// end Update ==========================
	case "pc":													// Process 'Create record' data =================
	function fnQuote_Smart($value) {    // Stripslashes
	    if (get_magic_quotes_gpc()) {
	        $value = stripslashes($value);
	    	}
	    if (!is_numeric($value)) {    // Quote if not integer
	        $value = "'" . mysql_real_escape_string($value) . "'";
		    }
	    return $value;
		}

	$temp1 = $temp2 = "";
	foreach ($_POST as $VarName=>$VarValue) {
		if (substr($VarName, 0, 4)=="frm_") {
			$temp1 .= "`" . substr($VarName, 4, 99) . "`,";		// field names - note tic's
			$temp2 .= fnQuote_Smart(trim($VarValue)) . ",";		// field values, apostrophe-enclosed and escaped
			}
		}		// end foreach () ...
																// now drop trailing comma
	$query  = "INSERT INTO $mysql_prefix$tablename (" . substr($temp1, 0, (strlen($temp1) - 1)) . ") VALUES (" . substr($temp2, 0, (strlen($temp2) - 1)) . ")";
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
	</FORM>
<?php
	$query = "SELECT MAX(id) AS id FROM `$mysql_prefix$tablename`" ;
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	$row = mysql_fetch_array($result);
	$id = $row['id'];
	
	unset ($result);

//	break;

	case "v":		// View detail	========================
?>
	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>"/>
	<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename ?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="<?php print $indexname; ?>"/>
	<INPUT TYPE="hidden" NAME="id"  		VALUE="<?php print $id ?>"/>
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="<?php print $sortby ;?>"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->
<?php
	print "<TABLE BORDER=\"0\" ALIGN=\"center\" >";
	if ($func == "pc") 	{print "<TR CLASS=\"even\" VALIGN=\"top\"><TD COLSPAN=\"2\"  ALIGN=\"CENTER\"><FONT SIZE=\"+1\">New '$tablename' entry accepted.</FONT></TD></TR>";}
	else				{print "<TR CLASS=\"even\" VALIGN=\"top\"><TD COLSPAN=\"2\" ALIGN=\"CENTER\"><FONT SIZE=\"+1\">Table '$tablename' - View Entry</FONT></TD></TR>";}
	print "<TR><TD>&nbsp;</TD></TR>";
	$query ="SELECT * FROM `$mysql_prefix$tablename` WHERE `$indexname` = \"$id\" LIMIT 1";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	$row = mysql_fetch_array($result);
	$lineno = 0;
	for ($i = 0; $i < mysql_num_fields($result); $i++) {
		$lineno++;
		print "\n\t<TR CLASS=" . $evenodd [$lineno % 2] . " VALIGN=\"top\"><TD CLASS=\"td_label\" ALIGN=\"right\">" . str_replace( "_", " ", ucfirst(mysql_field_name($result, $i))) . ":</TD><TD>";
		if ((mysql_field_name($result, $i) != $indexname) && (strtolower(substr(mysql_field_name($result, $i), -$id_lg)) == $FK_id)
						&& ($temp = fnSubTableExists(mysql_field_name($result, $i)))) {							// prepare to replace with indexed values
			$query ="SELECT * FROM `$mysql_prefix$temp` WHERE `$indexname` = $row[$i] LIMIT 1";				
			$temp_result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query); 
			if (mysql_affected_rows()>0) 	{										// defined?
				$temp_row = mysql_fetch_array($temp_result);						// yes
				print $temp_row[1];			// value, whatever name
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
?>

	<TR><TD COLSPAN="2" ALIGN="center">
	<BR />
	<INPUT TYPE="button" 	VALUE="Continue" onClick = "Javascript: document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="del_but" VALUE="Delete this entry" onclick="JSfnToFunc ('d', '<?php print $id ?>');"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="edl_but" VALUE="Edit this entry" onclick="JSfnToFunc('u', '<?php print $id ?>');"/></TD></TR>
	</FORM>
	</TD></TR></TABLE>
<?php
	break;		// end View ==========================


	case "pu":																	// Process Update 	================
		function fnQuote_Smart($value) {    // Stripslashes
		    if (get_magic_quotes_gpc()) {
		        $value = stripslashes($value);
		    	}

		    if (!is_numeric($value)) {    // Quote if not integer
		        $value = "'" . mysql_real_escape_string($value) . "'";
			    }
		    return $value;
			}

	$query = "UPDATE $mysql_prefix$tablename SET ";
	foreach ($_POST as $VarName=>$VarValue) {
		if ((substr($VarName, 0, 4)=="frm_") && ($VarName != $indexname)) { $query .= "`" . substr($VarName, 4, 99) . "`" . "='" . $VarValue . "',";}		// field names - note tic's
		}
	$query = substr($query, 0, (strlen($query) - 1)) . " WHERE `" .$indexname . "` = $id LIMIT 1";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	unset ($result);
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
	<INPUT TYPE="button" VALUE="Continue" onclick="this.form.submit();"/>
	</FORM>
<?php
	break;		// end Delete ======================

	case "p":	// Properties  ===========================
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

	$query ="DESCRIBE $mysql_prefix`$tablename`";
	$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
	print "\n<table align=\"CENTER\" BORDER=\"0\">";
	print "\n<TR><TH>Field</TH><TH>Type</TH><TH>Null</TH><TH>Key</TH><TH COLSPAN=3>Default/Extra</TH></TR>";
	$lineno = 0;
	while ($row = mysql_fetch_array($result))  {									// write each data row
		$lineno++;
		print "\n<TR VALIGN=\"top\" CLASS=\"" . $evenodd [$lineno % 2] . "\">";		// alternate line bg colors
		for($i = 0; $i < count($row)-1; $i++){										// each column
			if((isset($row[$i])) && (!is_null($row[$i]))) {print "<TD>$row[$i]</TD>";}
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
	<CENTER><BR><INPUT TYPE="button" 	VALUE="Continue" onClick = "Javascript: document.retform.func.value='r'; document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" VALUE="Add new <?php print str_replace( "_", " ", ucfirst($tablename)); ?> entry" onclick= "this.form.func.value='c'; this.form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</FORM>
	<?php

	break;		// end Properties ======================

	case "s":	// Select table ====================

	print "<BR /><BR /><BR /><BR /><BR /><BR /><BR />";
	fnTables();
	break;
	default:
	print __line__ . $func . " ERROR - ERROR - ERROR - ERROR - ERROR" ;
	}	// end switch ($func)

//if (($func == "r") || ($func == "p")) {			// limit visibility
//	fnTables();
//	}
	
function fnTables () {							/// displays tables comprising db $mysql_db
	global $mysql_db, $FK_id, $id_lg, $primaries, $secondaries;
	$ctrp=$ctrs=0;
	
	$sql = "SHOW TABLES ";
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
		if (!$gotit) {// not a primary
			$secondaries[$ctrs] = $row[0];
			$ctrs++;
			}				
		}
	mysql_free_result($result);
	print "<BR /><BR /><BR /><TABLE ALIGN=\"center\" BORDER=0><TR CLASS=\"even\"><TD ALIGN=\"center\" CLASS=\"td_link\" COLSPAN=\"2\"><FONT SIZE=\"+1\">Available '$mysql_db ' tables</FONT></TD></TR>";

	print "<TR VALIGN=\"top\"><TD><B><nobr>Primary Tables:</nobr></B></TD><TD ALIGN='center'>";
	for($i = 0; $i < $ctrp; $i++) {
		print "<A HREF=\"#\" ONCLICK=\"Javascript: document.s.tablename.value='$primaries[$i]'; document.s.indexname.value='99'; document.s.submit();\"> $primaries[$i] </A>&nbsp;&nbsp;&nbsp;\n";
		}
	print "</TD></TR><TR><TD>&nbsp;</TD></TR><TR VALIGN=\"top\"><TD><A HREF='#'onclick = \"Javascript:JSfnShowit('support')\"> <B>Support:</A>&nbsp;&nbsp;</B></TD><TD ALIGN='center'><SPAN ID='support' STYLE = 'visibility: hidden'>";
	for($i = 0; $i < $ctrs; $i++) {
		print "<A HREF=\"#\" ONCLICK=\"Javascript: document.s.tablename.value='$secondaries[$i]'; document.s.indexname.value='99'; document.s.submit();\"> $secondaries[$i] </A>&nbsp;&nbsp;&nbsp;\n";
		}
	print "<A HREF='#'onclick = \"Javascript:JSfnHideit('support')\"> <B>:Hide</A></SPAN></TD></TR></TABLE>";
	}
?>
<!-- ----------Common--------------- -->
<FORM NAME="s" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF'] ?>">
<INPUT TYPE = "hidden" NAME="tablename" VALUE=""/>
<INPUT TYPE = "hidden" NAME="indexname" VALUE="99"/>
<INPUT TYPE = "hidden" NAME="sortby"	VALUE=""/>
<INPUT TYPE = "hidden" NAME="sortdir"	VALUE="0"/>
<INPUT TYPE = "hidden" NAME="func" VALUE="r"/>
</FORM>
<CENTER><BR /><BR /><BR />

<FORM NAME="retform" method="post" action="<?php print $_SERVER['PHP_SELF'] ?>">
<INPUT TYPE = "hidden" NAME="tablename" VALUE="<?php print $tablename; ?>"/>
<INPUT TYPE = "hidden" NAME="indexname" VALUE="<?php print $indexname; ?>"/>
<INPUT TYPE = "hidden" NAME="sortby"	VALUE="<?php print $indexname ;?>"/>
<INPUT TYPE = "hidden" NAME="sortdir"	VALUE=0 />
<INPUT TYPE = "hidden" NAME="func" VALUE="r"/>
</FORM>
</CENTER></BODY>

<?php
if ($calstuff!="") {

	print "<link rel='stylesheet' type='text/css' media='all' href='./js/calendar-win2k-cold-1.css' title='win2k-cold-1' />\n";
	print "<script type='text/javascript' src='./js/calendar.js'></script>\n";
	print "<script type='text/javascript' src='./js/calendar-en.js'></script>\n";
	print "<script type='text/javascript' src='./js/calendar-setup.js\"></script>\n";

	print "<SCRIPT TYPE=\"text/javascript\">\n";
	print $calstuff;
	print "\n</SCRIPT>\n";
	}
?>
</HTML>
