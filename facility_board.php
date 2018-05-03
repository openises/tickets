<?php
/*
9/10/13 - Major re-write to previous versions
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
$logged_in = $logged_out = false;
if (empty($_SESSION)) {
	$logged_out = true;
	header("Location: index.php");
	} else {
	$logged_in = true;
	}
require_once './incs/functions.inc.php';
do_login(basename(__FILE__));

$requester = get_owner($_SESSION['user_id']);


function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $row['user'];
		}
	return $the_ret;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - <?php print get_text('Facility');?> <?php print get_text('Portal');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<STYLE>
	table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0;}
	table.cruises td {overflow: hidden; }
	div.scrollableContainer { position: relative; padding-top: 2em;}
	div.scrollingArea { max-height: 90%; overflow: auto; overflow-x: hidden; }
	table.scrollable thead tr { position: absolute; left: -1px; top: 0px; border: 1px solid #606060;}
	table.cruises th { text-align: center; overflow: hidden; font-size: 1.2em; cursor: default;}
	.text-labels {font-size: 2em; font-weight: 700;}
	.plain_listheader 	{color:#000000; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
	.listRow 	{color:#000000; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; text-decoration: none; background-color: #DEE3E7; font-weight: bolder; cursor: pointer; height: 100px;}
	.listEntry 	{text-align: left; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; color: inherit; border: 1px solid #606060; text-decoration: none; background-color: inherit; font-weight: bolder; cursor: pointer; font-size: 1.2em;}
	.noentries_listRow 	{color:#FFFFFF; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; border: 1px solid #606060; text-decoration: none; background-color: green; font-weight: bolder; height: 50px; cursor: default;}
	.noentries 	{text-align: center; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; color:#FFFFFF; border: 1px solid #606060; text-decoration: none; background-color: green; font-weight: bolder; font-size: 1.2em; cursor: default;}
	.btn_chkd 		{ height: 50px; color:#050; font: bold 16px 'trebuchet ms',helvetica,sans-serif; background-color:#EFEFEF; border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center;} 
	.btn_not_chkd 	{ height: 50px; color:#050; font: bold 16px 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7; border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center;} 
	.btn_hover	 	{ height: 50px; color:#050; font: bold 16px 'trebuchet ms',helvetica,sans-serif; background-color:#DEDEDE; border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center;} 
</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/leaflet/leaflet-routing-machine.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script type="application/x-javascript" src="./js/leaflet/KML.js"></script>
<script type="application/x-javascript" src="./js/leaflet/gpx.js"></script>  
<script type="application/x-javascript" src="./js/osopenspace.js"></script>
<script type="application/x-javascript" src="./js/leaflet-openweathermap.js"></script>
<script type="application/x-javascript" src="./js/esri-leaflet.js"></script>
<script type="application/x-javascript" src="./js/Control.Geocoder.js"></script>
<?php
if ($_SESSION['internet']) {
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
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<script type="application/x-javascript" src="./js/usng.js"></script>
<script type="application/x-javascript" src="./js/osgb.js"></script>
<script type="application/x-javascript" src="./js/geotools2.js"></script>
<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>

<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var doDebug = true;
var changed_showhide = true;
var changed_mkrshowhide = true;
var randomnumber;
var viewportwidth;
var viewportheight;
var the_string;
var theClass = "background-color: #CECECE";
var reqFin = false;
var outerWidth = 0;
var outerHeight = 0;
var listWidth = 0;
var listHeight = 0;
var cellwidth = 0;
var colors = new Array ('odd', 'even');
var listtimer = null;
var textDirections;
var showall = "no";

function do_hover_listheader (the_id) {
	CngClass(the_id, 'hover_listheader');
	return true;
	}

function do_plain_listheader (the_id) {				// 8/21/10
	CngClass(the_id, 'plain_listheader');
	return true;
	}

window.onresize=function(){set_size()};

function getHeaderHeight(element) {
	return element.clientHeight;
	}

function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	outerWidth = viewportwidth * .98;
	outerHeight = viewportheight * .80;
	listWidth = outerWidth * .99;	
	listHeight = outerHeight * .99;
	cellwidth = listWidth / 9;
	$('outer').style.width = outerWidth + "px";
	$('outer').style.height = outerHeight + "px";
	loadIt();
	}

function loadIt() {								// set cycle
	get_requests();
	}
	
function mainLoop() {
	if (listtimer!=null) {return;}
	listtimer = window.setInterval('get_requests2()', 30000);	
	}

function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) {
		top.location.href = document.location.href;
		location.href = '#top'; 
		setTimeout(function() {
			set_size();
			},1000);
		} else {
		location.href = '#top';
		set_size();
		}
	}		// end function out_frames()
	

function $() {									// 1/21/09
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
		
function go_there (where, the_id) {		//
	document.go.action = where;
	document.go.submit();
	}				// end function go there ()	
	
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function do_btn_hover (the_id) {
	CngClass(the_id, 'btn_hover');
	return true;
	}

function do_btn_plain (the_id) {
	CngClass(the_id, 'btn_not_chkd');
	return true;
	}
	
function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}
	
function setDirections(fromLat, fromLng, toLat, toLng, theDiv) {
	if(fromLat == "" || fromLng == "" || toLat == "" || toLng == "") {return;}
	window.theDirections = L.Routing.control({
		waypoints: [
			L.latLng(fromLat,fromLng),
			L.latLng(toLat,toLng)
		]});
	window.theDirections.on('routingerror', function(o) { console.log(o); });
	setTimeout(function() {
		theETA = Math.round(window.totTime / 60) + " Minutes<BR /><BR /><SPAN style='color: red; width: 80%; display: inline-block;'>Approximate based on tracking data</SPAN>";
		$(theDiv).innerHTML = theETA;
		},1000);
	}
	
function logged_in() {								// returns boolean
	var temp = <?php print $logged_in;?>;
	return temp;
	}	
	
function isNull(val) {								// checks var stuff = null;
	return val === null;
	}

dbfns = new Array ();					//  field names per assigns_t.php expectations
dbfns['c'] = 'frm_clear';
dbfns['a'] = 'frm_u2farr';

function set_assign(which, theAssign, theTicket, theUnit, btn) {
	if (!(parseInt(theAssign)) > 0) {return;}
	var currTxt = $(btn).innerHTML;
	var params = "frm_id=" + theAssign;
	params += "&frm_tick=" + theTicket;
	params += "&frm_unit=" + theUnit;
	params += "&frm_vals=" + dbfns[which];
	sendRequest ('assigns_t.php',handleResult, params);			// does the work
	var curr_time = do_time();
	replaceButtonText(btn, currTxt + " @ " + curr_time)
	CngClass(btn, 'btn_chkd');
	}		// end function set_assign()
	
function set_button(btn,theTime) {
	var currTxt = $(btn).innerHTML;
	if(the_time != "") {
		replaceButtonText(btn, currTxt + " @ " + theTime)
		CngClass(btn, 'btn_chkd');
		}
	}

function handleResult(req) {			// the called-back function
	}			// end function handle Result()

function replaceButtonText(buttonId, text) {
	if (document.getElementById) {
		var button=document.getElementById(buttonId);
		if (button) {
			if (button.childNodes[0]) {
				button.childNodes[0].nodeValue=text;
				}
			else if (button.value) {
				button.value=text;
				}
			else {					//if (button.innerHTML) 
				button.innerHTML=text;
				}
			}
		}
	}		// end function replaceButtonText()
	
var newwindow = null;
var starting;
function do_window(id) {				// 1/19/09
	if ((newwindow) && (!(newwindow.closed))) {newwindow.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		var url = "./add_facnote.php?ticket_id=" + id;
		newwindow=window.open(url, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
		if (isNull(newwindow)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		starting = false;
		}
	}		// end function do_window()
	
var catwindow = null;
function do_catwindow() {				// 1/19/09
	if ((catwindow) && (!(catwindow.closed))) {catwindow.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		var url = "./faccategories.php";
		catwindow=window.open(url, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
		if (isNull(catwindow)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		catwindow.focus();
		starting = false;
		}
	}		// end function do_window()
	
function do_showall() {
	if(window.showall == "no") {
		window.showall = "yes";
		$('showall_but').innerHTML = "Hide Cleared";
		} else {
		window.showall = "no";
		$('showall_but').innerHTML = "Show Cleared";		
		}
	loadIt();
	}

function get_requests() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./ajax/facboard_incidents.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber+"&q="+sessID+"&showall="+window.showall;
	sendRequest (url,requestlist_cb, "");
	function requestlist_cb(req) {
//		var theIncs = new Array();
		var i = 1;
 		var the_requests = JSON.decode(req.responseText);
		var theCount;
		var outputtext = "<TABLE id='requeststable' class='cruises scrollable' style='width: " + window.listWidth + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR class='plain_listheader' style='width: " + window.listWidth + "px;'>";
		outputtext += "<TH class='plain_listheader'>Incident Type</TH>";
		outputtext += "<TH class='plain_listheader'>Origin</TH>";
		outputtext += "<TH class='plain_listheader'>Destination</TH>";
		outputtext += "<TH class='plain_listheader'>Type</TH>";
		outputtext += "<TH class='plain_listheader'>Num Patients</TH>";
		outputtext += "<TH class='plain_listheader'>Notes</TH>";
		outputtext += "<TH class='plain_listheader'>Patient Name</TH>";
		outputtext += "<TH class='plain_listheader'>ETA</TH>";
		outputtext += "<TH class='plain_listheader'>Status</TH>";
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		if(the_requests[0][0] == 0) {
			outputtext += "<TR class='noentries_listRow' style='width: " + window.listwidth + "px;'><TD class='noentries' COLSPAN=99>Nothing Current</TD></TR>";
			theCount = 0;
			} else {
			theCount = the_requests.length;
			for (var key = 0; key < the_requests.length; key++) {
				if(the_requests[key][13] == 0) {
				outputtext += "<TR class='listRow' style='background-color: yellow; width: " + window.listWidth + "px;'>";
				} else {
				outputtext += "<TR class='listRow " + colors[i%2] + "' style='width: " + window.listWidth + "px;'>";
				}
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][2] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][5] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][6] + "</TD>";
				outputtext += "<TD class='listEntry' style='color: " + the_requests[key][11] + "; background-color: " + the_requests[key][12] + ";' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][7] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][3] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][10] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][8] + "</TD>";
				outputtext += "<TD ID='eta_" + the_requests[key][4] + "' class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][9] + "</TD>";
				var btnID1 = "arrbtn" + the_requests[key][13];
				var btnID2 = "clrbtn" + the_requests[key][13];
				if(the_requests[key][15] != "") {
					var txt1 = "Arrived @ " + the_requests[key][15];
					var class1 = "btn_chkd";
					} else {
					var txt1 = "Arrive";
					var class1 = "btn_not_chkd";
					var mouseon1 = "onMouseover='do_btn_hover(this.id);'";
					var mouseoff1 = "onMouseout='do_btn_plain(this.id);'";
					}
				if(the_requests[key][16] != "") {
					var txt2 = "Clear @ " + the_requests[key][15];
					var class2 = "btn_chkd"
					} else {
					var txt2 = "Clear";
					var class2 = "btn_not_chkd";
					var mouseon2 = "onMouseover='do_btn_hover(this.id);'";
					var mouseoff2 = "onMouseout='do_btn_plain(this.id);'";
					}
				outputtext += "<TD class='listEntry'><SPAN id='" + btnID1 + "' class='" + class1 + "' style='width: 100%; display: inline-block;' " + mouseon1 + " " + mouseoff1 + " onClick=\"set_assign('a'," + the_requests[key][13] + "," + the_requests[key][4] + "," + the_requests[key][14] + ",'" + btnID1 + "');\">" + txt1 + "</SPAN><SPAN id='" + btnID2 + "' class='" + class2 + "' style='width: 100%; display: inline-block;' " + mouseon2 + " " + mouseoff2 + " onClick=\"set_assign('c'," + the_requests[key][13] + "," + the_requests[key][4] + "," + the_requests[key][14] + ",'" + btnID2 + "');\">" + txt2 + "</SPAN></TD>";
				outputtext += "</TR>";
				setDirections(the_requests[key][17], the_requests[key][18], the_requests[key][19], the_requests[key][20], "eta_" +  the_requests[key][4]);
				i++;
				}
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		$('all_requests').innerHTML = outputtext;
		var theWidth = cellwidth + "px";
		var reqtbl = document.getElementById('requeststable');
		if(theCount == 0) {
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				for (var j = 0; j < headerRow.cells.length; j++) {
					headerRow.cells[j].style.width = theWidth;
					}
				}
			} else {
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				for (var j = 0; j < headerRow.cells.length; j++) {
					headerRow.cells[j].style.width = theWidth;
					}
				for (var k = 0; k < tableRow.cells.length; k++) {
					tableRow.cells[k].style.width = theWidth;
					}
				if(getHeaderHeight(headerRow) >= 25) {
					var theRow = reqtbl.insertRow(1);
					theRow.style.height = "30px";
					var no1 = theRow.insertCell(0);
					var no2 = theRow.insertCell(1);
					var no3 = theRow.insertCell(2);
					var no4 = theRow.insertCell(3);
					var no5 = theRow.insertCell(4);
					var no6 = theRow.insertCell(5);
					var no7 = theRow.insertCell(6);
					var no8 = theRow.insertCell(7);
					var no9 = theRow.insertCell(8);
					no1.innerHTML = " ";
					no2.innerHTML = " ";
					no3.innerHTML = " ";
					no4.innerHTML = " ";
					no5.innerHTML = " ";
					no6.innerHTML = " ";
					no7.innerHTML = " ";
					no8.innerHTML = " ";
					no9.innerHTML = " ";
					}
				}
			}			//	end if theCount == 0
		mainLoop();
		}				// end function facilitylist_cb()
	}				// end function load_facilitylist()
	
function get_requests2() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./ajax/facboard_incidents.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber+"&q="+sessID+"&showall="+window.showall;
	sendRequest (url,requestlist_cb2, "");
	function requestlist_cb2(req) {
		var i = 1;
 		var the_requests = JSON.decode(req.responseText);
		var theCount;
		var outputtext = "<TABLE id='requeststable' class='cruises scrollable' style='width: " + window.listWidth + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR class='plain_listheader' style='width: " + window.listWidth + "px;'>";
		outputtext += "<TH class='plain_listheader'>Incident Type</TH>";
		outputtext += "<TH class='plain_listheader'>Origin</TH>";
		outputtext += "<TH class='plain_listheader'>Destination</TH>";
		outputtext += "<TH class='plain_listheader'>Med Type</TH>";
		outputtext += "<TH class='plain_listheader'>Num Patients</TH>";
		outputtext += "<TH class='plain_listheader'>Notes</TH>";
		outputtext += "<TH class='plain_listheader'>Patient Name</TH>";
		outputtext += "<TH class='plain_listheader'>ETA</TH>";
		outputtext += "<TH class='plain_listheader'>Status</TH>";
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		if(the_requests[0][0] == 0) {
			outputtext += "<TR class='noentries_listRow' style='width: " + window.listwidth + "px;'><TD class='noentries' COLSPAN=99>Nothing Current</TD></TR>";
			theCount = 0;
			} else {
			theCount = the_requests.length;
			for (var key = 0; key < the_requests.length; key++) {
				outputtext += "<TR class='listRow " + colors[i%2] + "' style='width: " + window.listWidth + "px;'>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][2] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][5] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][6] + "</TD>";
				outputtext += "<TD class='listEntry' style='color: " + the_requests[key][11] + "; background-color: " + the_requests[key][12] + ";' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][7] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][3] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][10] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][8] + "</TD>";
				outputtext += "<TD ID='eta_" + the_requests[key][4] + "' class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][9] + "</TD>";
				var btnID1 = "arrbtn" + the_requests[key][13];
				var btnID2 = "clrbtn" + the_requests[key][13];
				if(the_requests[key][15] != "") {
					var txt1 = "Arrived @ " + the_requests[key][15];
					var class1 = "btn_chkd";
					} else {
					var txt1 = "Arrive";
					var class1 = "btn_not_chkd";
					var mouseon1 = "onMouseover='do_btn_hover(this.id);'";
					var mouseoff1 = "onMouseout='do_btn_plain(this.id);'";
					}
				if(the_requests[key][16] != "") {
					var txt2 = "Clear @ " + the_requests[key][15];
					var class2 = "btn_chkd"
					} else {
					var txt2 = "Clear";
					var class2 = "btn_not_chkd";
					var mouseon2 = "onMouseover='do_btn_hover(this.id);'";
					var mouseoff2 = "onMouseout='do_btn_plain(this.id);'";
					}
				outputtext += "<TD class='listEntry'><SPAN id='" + btnID1 + "' class='" + class1 + "' style='width: 100%; display: inline-block;' " + mouseon1 + " " + mouseoff1 + " onClick=\"set_assign('a'," + the_requests[key][13] + "," + the_requests[key][4] + "," + the_requests[key][14] + ",'" + btnID1 + "');\">" + txt1 + "</SPAN><SPAN id='" + btnID2 + "' class='" + class2 + "' style='width: 100%; display: inline-block;' " + mouseon2 + " " + mouseoff2 + " onClick=\"set_assign('c'," + the_requests[key][13] + "," + the_requests[key][4] + "," + the_requests[key][14] + ",'" + btnID2 + "');\">" + txt2 + "</SPAN></TD>";
				outputtext += "</TR>";
				setDirections(the_requests[key][17], the_requests[key][18], the_requests[key][19], the_requests[key][20], "eta_" +  the_requests[key][4]);
				i++;
				}
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		$('all_requests').innerHTML = outputtext;
		var theWidth = cellwidth + "px";
		var reqtbl = document.getElementById('requeststable');
		if(theCount == 0) {
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				for (var j = 0; j < headerRow.cells.length; j++) {
					headerRow.cells[j].style.width = theWidth;
					}
				}
			} else {
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				for (var j = 0; j < headerRow.cells.length; j++) {
					headerRow.cells[j].style.width = theWidth;
					}
				for (var k = 0; k < tableRow.cells.length; k++) {
					tableRow.cells[k].style.width = theWidth;
					}
				if(getHeaderHeight(headerRow) >= 25) {
					var theRow = reqtbl.insertRow(1);
					theRow.style.height = "30px";
					var no1 = theRow.insertCell(0);
					var no2 = theRow.insertCell(1);
					var no3 = theRow.insertCell(2);
					var no4 = theRow.insertCell(3);
					var no5 = theRow.insertCell(4);
					var no6 = theRow.insertCell(5);
					var no7 = theRow.insertCell(6);
					var no8 = theRow.insertCell(7);
					var no9 = theRow.insertCell(8);
					no1.innerHTML = " ";
					no2.innerHTML = " ";
					no3.innerHTML = " ";
					no4.innerHTML = " ";
					no5.innerHTML = " ";
					no6.innerHTML = " ";
					no7.innerHTML = " ";
					no8.innerHTML = " ";
					no9.innerHTML = " ";
					}
				}
			}
		}
	}

function do_logout() {
	document.gout_form.submit();
	}	
		
function do_unload() {
	}
</SCRIPT>
</HEAD>
<?php


if((!isset($_SESSION)) && (empty($_POST))) {
	print "Not Logged in";
} elseif((isset($_SESSION)) && (empty($_POST))) {
	$now = time() - (intval(get_variable('delta_mins')*60));
?>

	<BODY style='overflow: hidden;' onLoad="out_frames();" onUnload='do_unload();'>

		<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
		<DIV id='outer' style='position: absolute; top: 0px; left: 1%; text-align: center;'>
			<DIV id='banner' style='padding-left: 30%; text-align: left; background-color: #707070; vertical-align: middle; cursor: default;'><SPAN class='heading' style='font-size: 3em; vertical-align: middle; cursor: default;'>Tickets <?php print get_text('Facility');?> <?php print get_text('Portal');?></SPAN>
				<SPAN ID='gout' CLASS='plain' style='float: right; font-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_logout()"><?php print get_text('Logout');?></SPAN>
				<SPAN ID='cats_but' CLASS='plain' style='float: right; ont-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_catwindow()"><?php print get_text('Categories');?></SPAN>
				<SPAN ID='showall_but' CLASS='plain' style='float: right; font-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_showall()">Show <?php print get_text('Cleared');?></SPAN>
			</DIV>
			<DIV id='requests_list' style='display: block; cursor: default;'>
				<DIV id='list_header' class='header' style='font-size: 20px; vertical-align: middle; height: 22px;'>Current Jobs</DIV><BR />
				<DIV class="scrollableContainer" id='the_bottom'>
					<DIV class="scrollingArea" id='all_requests'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
				</DIV>
			</DIV>
		</DIV>
		<DIV id='directions' style='display: none;'></DIV>
		<DIV id='map_canvas' style='display: none;'></DIV>
	<SCRIPT>
		var map;				// make globally visible
		var minimap;
		var latLng;
		var in_local_bool = "0";
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "br");
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 13);
	</SCRIPT>
	<FORM METHOD='POST' NAME="gout_form" action="index.php">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>
	<FORM NAME="go" action="#" TARGET = "main"></FORM>	
	</BODY>
<?php
	}
?> 

</HTML>
