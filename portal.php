<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once './incs/functions.inc.php';
do_login(basename(__FILE__));
$requester = get_owner($_SESSION['user_id']);

function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		}
	return $the_ret;
	}
	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Service User Portal</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<?php
$api_key = get_variable('gmaps_api_key');
?>	
<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT>
var randomnumber;
var the_string;
var theClass = "background-color: #CECECE";
var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
var request_lat;
var request_lng;
var the_color;
var fac_street = [];
var fac_city = [];
var fac_state = [];

function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) top.location.href = document.location.href;
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

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}
	
function sendRequest(url,callback,postData) {
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
	req.setRequestHeader('User-Agent','XMLHTTP/1.0');
	if (postData)
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	req.onreadystatechange = function () {
		if (req.readyState != 4) return;
		if (req.status != 200 && req.status != 304) {
			return;
			}
		callback(req);
		}
	if (req.readyState == 4) return;
	req.send(postData);
	}

var XMLHttpFactories = [
	function () {return new XMLHttpRequest()	},
	function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
	function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
	function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
	];

function createXMLHTTPObject() {
	var xmlhttp = false;
	for (var i=0;i<XMLHttpFactories.length;i++) {
		try {
			xmlhttp = XMLHttpFactories[i]();
			}
		catch (e) {
			continue;
			}
		break;
		}
	return xmlhttp;
	}

function syncAjax(strURL) {
	if (window.XMLHttpRequest) {						 
		AJAX=new XMLHttpRequest();						 
		} 
	else {																 
		AJAX=new ActiveXObject("Microsoft.XMLHTTP");
		}
	if (AJAX) {
		AJAX.open("GET", strURL, false);														 
		AJAX.send(null);
		return AJAX.responseText;																				 
		} 
	else {
		alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
		return false;
		}																						 
	}
	
function requests_get() {
	msgs_interval = window.setInterval('do_requests_loop()', 60000);
	}	
	
function do_requests_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_requests.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, requests_cb2, "");
	}

function requests_cb2(req) {
	var the_requests=JSON.decode(req.responseText);
	if(the_requests[0][0] == "No Current Requests") {
		width = "width: 6%; ";
		} else {
		width = "";
		}
	theClass = "background-color: #CECECE";
	the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
	the_string += "<TR class='heading' style='font-weight: bold; font-size: 1em; color: #FFFFFF; text-align: left;'>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>ID</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Patient</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Phone</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Contact</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Scope</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Description</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Comments</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Status</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Requested</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Tentative</TD>";	
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Accepted</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Declined</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Resourced</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Completed</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Closed</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Updated</TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>By</TD>";			
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Mileage</TD>";		
	the_string += "</TR>";			
	for(var key in the_requests) {
		if(the_requests[key][0] == "No Current Requests") {
			$('export_but').style.display = "none";			
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD COLSPAN=99 width='100%' style='font-weight: bold; font-size: 16px; text-align: center;'>No Current Requests</TD></TR>";
			} else {
			$('export_but').style.display = "inline-block";				
			var the_request_id = the_requests[key][0];
			the_string += "<TR title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px; text-align: left;'>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][13] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][15] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][16] + "</TD>";	
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][19] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][20] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][21] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][22] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][23] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][24] + "</TD>";	
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
			the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][27] + "</TD>";
			the_string += "</TR>";
			if(the_requests[key][16] == "Accepted"){
				the_color = 3;
				} else if (the_requests[key][16] == "Declined"){
				the_color = 2;
				} else {
				the_color = 4;
				}
			if((the_requests[key][29] != .999999) && (the_requests[key][30] != .999999) && (the_color != 3)) {
				request_lat = the_requests[key][29];
				request_lng = the_requests[key][20];					
				createMarker(request_lat, request_lng, the_color)
				}		
			}
		}
		the_string += "</TABLE>";
		$('all_requests').innerHTML = the_string;
	}

function get_requests() {
	var width = "";	
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_requests.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, requests_cb, "");
	function requests_cb(req) {
		var the_requests=JSON.decode(req.responseText);
		if(the_requests[0][0] == "No Current Requests") {
			width = "width: 6%; ";
			} else {
			width = "";
			}
		theClass = "background-color: #CECECE";
		the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
		the_string += "<TR class='heading' style='font-weight: bold; font-size: 1em; color: #FFFFFF; text-align: left;'>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>ID</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Patient</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Phone</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Contact</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Scope</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Description</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Comments</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Status</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Requested</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Tentative</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Accepted</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Declined</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Resourced</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Completed</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Closed</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Updated</TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>By</TD>";			
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 1em; color: #FFFFFF;'>Mileage</TD>";		
		the_string += "</TR>";			
		for(var key in the_requests) {
			if(the_requests[key][0] == "No Current Requests") {
				$('export_but').style.display = "none";				
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD COLSPAN=99 width='100%' style='font-weight: bold; font-size: 16px; text-align: center;'>No Current Requests</TD></TR>";
				} else {
				$('export_but').style.display = "inline-block";						
				var the_request_id = the_requests[key][0];	
				the_string += "<TR title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px; text-align: left;'>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][13] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][15] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][16] + "</TD>";	
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][19] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][20] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][21] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][22] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][23] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][24] + "</TD>";	
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
				the_string += "<TD style='white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('./portal/request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][27] + "</TD>";
				the_string += "</TR>";
				if(the_requests[key][16] == "Accepted"){
					the_color = 3;
					} else if (the_requests[key][16] == "Declined"){
					the_color = 2;
					} else {
					the_color = 4;
					}
				if((the_requests[key][29] != .999999) && (the_requests[key][30] != .999999) && (the_color != 3)) {
					request_lat = the_requests[key][29];
					request_lng = the_requests[key][30];		
					createMarker(request_lat, request_lng, the_color)
					}	
				}
			}
			the_string += "</TABLE>";
			$('all_requests').innerHTML = the_string;
			requests_get();
		}
	}		

function markers_get() {
	msgs_interval = window.setInterval('do_markers_loop()', 60000);
	}	
	
function do_markers_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_ticketsandresponders.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, markers_cb2, "");
	}

function markers_cb2(req) {
	var the_markers=JSON.decode(req.responseText);
	for (var key in the_markers) {
		var the_lat = the_markers[key].lat;
		var the_lng = the_markers[key].lng;		
		createMarker(the_lat, the_lng, 2);
		for(var elements in the_markers[key].responders) {
			var r_lat = the_markers[key].responders[elements].lat;
			var r_lng = the_markers[key].responders[elements].lng;		
			createMarker(r_lat, r_lng, 1);			
			}
		} 	
	}
	
function get_the_markers() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_ticketsandresponders.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, markers_cb, "");
	function markers_cb(req) {
		var the_markers=JSON.decode(req.responseText);
		for (var key in the_markers) {
			var the_lat = the_markers[key].lat;
			var the_lng = the_markers[key].lng;	
			createMarker(the_lat, the_lng, 2);
			for(var elements in the_markers[key].responders) {
				var r_lat = the_markers[key].responders[elements].lat;
				var r_lng = the_markers[key].responders[elements].lng;		
				createMarker(r_lat, r_lng, 1);			
				}
			} 		
		}
	markers_get();
	}	
	
function do_coords(inlat, inlng) { 										 //9/14/08
	if((inlat.length==0)||(inlng.length==0)) {return;}
	var str = inlat + ", " + inlng + "\n";
	str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
	str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
	}

function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
	var d = new Number(Math.abs(inval));
	d  = Math.floor(d);
	var mi = (Math.abs(inval)-d)*60;	// fraction * 60
	var m = Math.floor(mi)				// min's as fraction
	var si = (mi-m)*60;					// to sec's
	var s = si.toFixed(1);
	return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
	}

function lat2ddm(inlat) {				//  lat to degr, dec.min's - 9/9/089/7/08
	var x = new Number(Math.abs(inlat));
	var degs  = Math.floor(x);				// degrees
	var mins = ((Math.abs(x-degs)*60).toFixed(1));
	var nors = (inlat>0.0)? " N":" S";
	return degs + '\260'  + mins +"'" + nors;
	}

function lng2ddm(inlng) {				//  lng to degr, dec.min's - 9/9/089/7/08
	var x = new Number(Math.abs(inlng));
	var degs  = Math.floor(x);				// degrees
	var mins = ((Math.abs(x-degs)*60).toFixed(1));
	var eorw = (inlng>0.0)? " E":" W";
	return degs + '\260' + mins +"'" + eorw;
	}

function do_lat_fmt(inlat) {				// 9/9/08
	switch(lat_lng_frmt) {
		case 0:
			return inlat;
			break;
		case 1:
			return ll2dms(inlat);
			break;
		case 2:
			return lat2ddm(inlat);
			break;
		default:
			alert ( "error <?php print __LINE__;?>");
		}	
	}

function do_lng_fmt(inlng) {
	switch(lat_lng_frmt) {
		case 0:
			return inlng;
			break;
		case 1:
			return ll2dms(inlng);
			break;
		case 2:
			return lng2ddm(inlng);
			break;
		default:
			alert ("error <?php print __LINE__;?>");
		}	
	}
	
function do_lat (lat) {
	document.add.frm_lat.value=lat;			// 9/9/08
	}
function do_lng (lng) {
	document.add.frm_lng.value=lng;
	}

function do_grids(theForm) {								// 12/13/10
<?php															// 1/24/11
		$locale = intval(trim(get_variable("locale"))); 
		switch($locale) { 
			case "0":
				echo "\n\t\t do_usng(theForm);\n";
				break;
		
			case "1":
				echo "\n\t\t do_osgb(theForm);\n";


				break;
			default:																	// 8/10/09
				echo "\n\t\t do_utm(theForm);\n";
			}		// end switch
?>
	}
	
function do_fac_to_loc(text, index){			// 9/22/09
	var curr_lat = fac_lat[index];
	var curr_lng = fac_lng[index];
	var curr_street = fac_street[index];
	var curr_city = fac_city[index];
	var curr_state = fac_state[index];
	do_lat(curr_lat);
	do_lng(curr_lng);
	pt_to_map(document.forms['add'], curr_lat, curr_lng);			// show it
	document.add.fac_street.value = curr_street;
	document.add.fac_city.value = curr_city;
	document.add.fac_state.value = curr_state;	
	}					// end function do_fac_to_loc
	
function do_usng(theForm) {								// 8/23/08, 12/5/10
	theForm.frm_grid.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);	// US NG
	}

function do_utm (theForm) {
	var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
	var utm_out = ll_in.toUTMRef().toString();
	temp_ary = utm_out.split(" ");
	theForm.frm_grid.value = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
	}

function do_osgb (theForm) {
	theForm.frm_grid.value = LLtoOSGB(theForm.frm_lat.value, theForm.frm_lng.value);
	}
	
function pt_to_map (my_form, lat, lng) {				// 1/19/09
	map.clearOverlays();								// 4/27/10
	var loc = <?php print get_variable('locale');?>;
	my_form.frm_lat.value=lat;	
	my_form.frm_lng.value=lng;		
	map.setCenter(new GLatLng(my_form.frm_lat.value, my_form.frm_lng.value), <?php print get_variable('def_zoom');?>);
	var marker = new GMarker(map.getCenter());		// marker to map center
	var myIcon = new GIcon();
	myIcon.image = "./markers/sm_red.png";
	map.addOverlay(marker, myIcon);
	}				// end function pt_to_map ()
	
function loc_lkup(my_form) {		   // added 1/19/09 -- getLocations(address,  callback -- not currently used )
	if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
		alert ("City and State are required for location lookup.");
		return false;
		}
	var geocoder = new GClientGeocoder();
//				"1521 1st Ave, Seattle, WA"		
	var address = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();
	
	if (geocoder) {
		geocoder.getLatLng(
			address,
			function(point) {
				if (!point) {
					alert(address + " not found");
					} 
				else {
					pt_to_map (my_form, point.lat(), point.lng())
					}
				}
			);
		}
	}				// end function addrlkup()


var map;						// note globals
var geocoder = null;
var thePoint;
var baseIcon;
var cross;
var fac_lat = [];
var fac_lng = [];
var icons=[];	
icons[1] = "./portal/markers/sm_white.png";		// white
icons[2] = "./portal/markers/sm_red.png";	// red
icons[3] = "./portal/markers/sm_blue.png";	// blue
icons[4] = "./portal/markers/sm_yellow.png";	// yellow

var baseIcon = new GIcon();
baseIcon.shadow = "./markers/sm_shadow.png";

baseIcon.iconSize = new GSize(20, 34);
baseIcon.iconAnchor = new GPoint(9, 34);
baseIcon.infoWindowAnchor = new GPoint(9, 2);

var unit_icon = new GIcon(baseIcon);
unit_icon.image = icons[1];

function do_marker(lat, lng, zoom) {		// 9/16/08 - 12/6/08
//	map.clearOverlays();
	var center = new GLatLng(lat, lng);
	var myzoom = zoom;
	map.setCenter(center, myzoom);
	thisMarker  = new GMarker(center, {icon: cross});				// 9/16/08
	map.addOverlay(thisMarker);
	}

function createMarker(lat, lng, number) {		// Show this markers index in the info window when clicked
	var point = new GLatLng(lat, lng);
	var the_icon = new GIcon(baseIcon);
	the_icon.image = icons[number];
	var marker = new GMarker(point, the_icon);	
	map.addOverlay(marker);
	}	

function domap() {										// called from phone, addr lookups
	map = new GMap2($('map'));
<?php
	$maptype = get_variable('maptype');

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>
	$("map").style.backgroundImage = "url(./markers/loading.jpg)";
	map.setUIToDefault();										// 8/13/10

	map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
	map.setCenter(new GLatLng(document.add.frm_lat.value, document.add.frm_lng.value), <?php echo get_variable('def_zoom'); ?>);			// larger # => tighter zoom
	map.addControl(new GOverviewMapControl());
	map.enableScrollWheelZoom();
	do_marker(null, null, null)	;		// 12/6/08
	}				// end function do map()
	
function load(the_lat, the_lng, the_zoom) {				// onLoad function - 4/28/09
	if (GBrowserIsCompatible()) {
		map = new GMap2($('map'));
<?php
	$maptype = get_variable('maptype');	// 08/02/09

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>
		map.setUIToDefault();										// 8/13/10
		map.addControl(new GMapTypeControl());
		map.addControl(new GLargeMapControl());			
<?php 		
		print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";
?>
		baseIcon = new GIcon();				// 
		baseIcon.iconSize=new GSize(20,34);
		baseIcon.iconAnchor=new GPoint(16,16);
		cross = new GIcon(baseIcon, "./markers/crosshair.png", null);	
		do_marker(the_lat, the_lng, the_zoom);		// 12/6/08
		}			// end if (GBrowserIsCompatible())
	}			// end function load()

function GUnload(){				// dummy
	return;
	}		

function do_logout() {
	// clearInterval(mu_interval);
	// mu_interval = null;
	// is_initialized = false;
	document.gout_form.submit();			// send logout 
	}		

<?php
$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$rec_fac_menu = "<SELECT NAME='frm_rec_fac'>";
$rec_fac_menu .= "<OPTION VALUE=0 selected>Receiving Facility</OPTION>";
while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
		$rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
		}
$rec_fac_menu .= "<SELECT>";

$query_fc2 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc2 = mysql_query($query_fc2) or do_error($query_fc2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$orig_fac_menu = "<SELECT NAME='frm_orig_fac' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>";
$orig_fac_menu .= "<OPTION VALUE=0 selected>Originating Facility</OPTION>";
while ($row_fc2 = mysql_fetch_array($result_fc2, MYSQL_ASSOC)) {
		$orig_fac_menu .= "<OPTION VALUE=" . $row_fc2['id'] . ">" . shorten($row_fc2['name'], 30) . "</OPTION>";
		$street = ($row_fc2['street'] != "") ? $row_fc2['street'] : "Empty";
		$city = ($row_fc2['city'] != "") ? $row_fc2['city'] : "Empty";
		$state = ($row_fc2['state'] != "") ? $row_fc2['state'] : "Empty";
		print "\tfac_lat[" . $row_fc2['id'] . "] = " . $row_fc2['lat'] . " ;\n";
		print "\tfac_lng[" . $row_fc2['id'] . "] = " . $row_fc2['lng'] . " ;\n";	
		print "\tfac_street[" . $row_fc2['id'] . "] = '" . $street . "' ;\n";	
		print "\tfac_city[" . $row_fc2['id'] . "] = '" . $city . "' ;\n";
		print "\tfac_state[" . $row_fc2['id'] . "] = '" . $state . "' ;\n";		
		}
$orig_fac_menu .= "<SELECT>";

?>	
</SCRIPT>
</HEAD>
<!-- <BODY onLoad = "ck_frames();"> -->

<?php


if((!isset($_SESSION)) && (empty($_POST))) {
	print "Not Logged in";
} elseif((isset($_SESSION)) && (empty($_POST))) {
	$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
	$now = time() - (intval(get_variable('delta_mins')*60));
?>
	<BODY onLoad="out_frames(); location.href = '#top'; get_requests(); get_the_markers(); <?php echo $onload_str ;?>" onUnload="GUnload()">
	<FORM NAME="go" action="#" TARGET = "main"></FORM>
	<DIV id='outer' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='banner' class='heading' style='font-size: 28px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>Tickets Service User Portal
			<SPAN ID='gout' CLASS='plain' style='float: right;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_logout()">Logout</SPAN>
			<SPAN ID='upload_but' CLASS='plain' style='float: right;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.open('./portal/import_requests.php','Import Requests','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')">Import Requests from CSV</SPAN>
			<SPAN ID='export_but' CLASS='plain' style='float: right; display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.open('./portal/csv_export.php','Export Requests','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')">Export Requests to CSV</SPAN>
		</DIV><BR /><BR />
		<DIV id='leftcol' style='position: fixed; left: 2%; top: 10%; width: 45%; height: 45%;'>
			<DIV id='the_heading' class='heading' style='font-size: 20px;'>ADD A NEW REQUEST</DIV>		
			<DIV id='left_scroller' style='position: relative; top: 0px; left: 0px; height: 100%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
				<FORM NAME='add' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
				<TABLE style='width: 100%;'>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Requested By</TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($_SESSION['user_id']);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'>Request Date and Time</TD><TD class='td_data' style='text-align: left;'><?php print generate_date_dropdown('request_date',0,FALSE);?></TD>
					</TR>			
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Patient');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Street');?>&nbsp;&nbsp;<BUTTON type="button" onClick="Javascript:loc_lkup(document.add);return false;"><img src="./markers/glasses.png" alt="Lookup location." /></BUTTON>&nbsp;&nbsp;</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>			
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_phone' TYPE='TEXT' SIZE='16' MAXLENGTH='16' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Originating Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_fac_menu;?></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_fac_menu;?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_scope' TYPE='TEXT' SIZE='48' MAXLENGTH='64' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_description" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD>
					</TR>		
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Comments');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD>
					</TR>
					<TR class='odd'>	
						<TD COLSPAN='2' class='td_label' style='text-align: center;'><?php print get_text('Lat');?><INPUT NAME='frm_lat' TYPE='TEXT' SIZE='10' MAXLENGTH='10' VALUE="">&nbsp;&nbsp;<?php print get_text('Lng');?><INPUT NAME='frm_lng' TYPE='TEXT' SIZE='10' MAXLENGTH='10' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD COLSPAN='2' class='heading' style='text-align: left;'>Originating Facility Details</TD>
					</TR>						
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Facility Street');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='fac_street' TYPE='TEXT' SIZE='48' MAXLENGTH='64' VALUE=""></TD>
					</TR>		
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Facility City');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='fac_city' TYPE='TEXT' SIZE='48' MAXLENGTH='64' VALUE=""></TD>
					</TR>		
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Facility State');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='fac_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE=""></TD>
					</TR>						
				</TABLE>
				<INPUT NAME='requester' TYPE='hidden' SIZE='24' VALUE="<?php print $_SESSION['user_id'];?>">
<!--			<INPUT NAME='fac_street' TYPE='hidden' SIZE='48' VALUE="">			
				<INPUT NAME='fac_city' TYPE='hidden' SIZE='48' VALUE="">	
				<INPUT NAME='fac_state' TYPE='hidden' SIZE='48' VALUE="">	-->				
				</FORM>
			</DIV><BR /><BR />
			<SPAN id='sub_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.add.submit();">Submit</SPAN><BR /><BR />
		</DIV>
		<DIV id='the_bottom' style='position: fixed; left: 2%; bottom: 2%; width: 95%; height: 30%; max-height: 30%; border: 2px outset #CECECE; padding: 10px; overflow-y: scroll;'>
			<DIV ID='all_requests' style='width: 100%;'></DIV>
		</DIV>	
<?php
		if(get_variable('map_in_portal') == 1) {
?>
			<DIV id='map_wrapper' style='position: fixed; right: 2%; top: 9%; width: 40%; height: 45%; border: 2px outset #CECECE; padding: 10px; float: right; overflow: auto;'>
				<DIV id='map' style='width: 800px; height: 800px;'>
				</DIV>
			</DIV>
<?php
			}
?>
	<FORM METHOD='POST' NAME="gout_form" action="index.php">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>
	</DIV>
	</BODY>
<?php
} elseif((isset($_SESSION)) && (!empty($_POST))) {
?>
	<BODY>
<?php
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
	$where = $_SERVER['REMOTE_ADDR'];
	$street = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['fac_street'])) : quote_smart(trim($_POST['frm_street']));
	$city = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['fac_city'])) : quote_smart(trim($_POST['frm_city']));
	$state = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['fac_state'])) : quote_smart(trim($_POST['frm_state']));	
	$lat = ($_POST['frm_lat'] != "") ? $_POST['frm_lat'] : '0';
	$lng = ($_POST['frm_lng'] != "") ? $_POST['frm_lng'] : '0';	
	$description = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['frm_street'] . "/n " . $_POST['frm_city'] . "/n" . $_POST['frm_state'] . "/n" . $_POST['frm_description'])) : $_POST['frm_description'];
	$meridiem_request_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_request_date'])))) ) ? "" : $_POST['frm_meridiem_request_date'] ;
	$request_date = "$_POST[frm_year_request_date]-$_POST[frm_month_request_date]-$_POST[frm_day_request_date] $_POST[frm_hour_request_date]:$_POST[frm_minute_request_date]:00$meridiem_request_date";	
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
				`org`
				`contact`, 
				`street`, 
				`city`, 
				`state`, 
				`the_name`, 
				`phone`, 
				`orig_facility`,
				`rec_facility`, 
				`scope`, 
				`description`, 
				`comments`, 
				`lat`,
				`lng`,
				`request_date`, 
				`status`, 
				`accepted_date`,
				`declined_date`, 
				`resourced_date`, 
				`completed_date`, 
				`closed`, 
				`requester`, 
				`_by`, 
				`_on`, 
				`_from` 
				) VALUES (
				" . 0 . ",
				" . get_user_name($_SESSION['user_id']) . ",
				" . addslashes($street) . ",	
				" . addslashes($city) . ",	
				" . addslashes($state) . ",	
				" . addslashes($_POST['frm_patient']) . ",
				" . addslashes($_POST['frm_phone']) . ",		
				" . $_POST['frm_orig_fac'] . ",					
				" . $_POST['frm_rec_fac'] . ",	
				" . addslashes($_POST['frm_scope']) . ",	
				" . addslashes($description) . ",					
				" . addslashes($_POST['frm_comments']) . ",		
				" . $lat . ",		
				" . $lng . ",				
				" . $request_date . ",
				'Open',
				NULL,
				NULL,
				NULL,
				NULL,
				NULL,
				" . $_SESSION['user_id'] . ",
				" . $_SESSION['user_id'] . ",				
				'" . $now . "',
				'" . $where . "')";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
	if ($addrs) {				// any addresses?
		$to_str = implode("|", $addrs);
		$smsg_to_str = "";
		$subject_str = "New " . get_text('Service User') . " Request";
		$text_str = "A new request has been loaded by \n\n" . get_user_name($_SESSION['user_id']) . "\n\nDated " . $now . "\n\nPlease log on to Tickets and check"; 
		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);
		}				// end if/else ($addrs)	
	
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');	
	$extra = 'portal.php';		
	$url = "http://" . $host . $uri . "/" . $extra;
	redir($url);	
?>
	<FORM METHOD='POST' NAME="gout_form" action="index.php">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>
	</BODY>
<?php
} else {
}
?> 

</HTML>
