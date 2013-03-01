<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once '../incs/functions.inc.php';
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
<HEAD><TITLE>Tickets - Service User Requests</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="../js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT>
var randomnumber;
var the_string;
var theClass = "background-color: #CECECE";
var the_onclick;

function ck_frames() {		// onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	}		// end function ck_frames()
	
function do_sel_update (the_id, the_val) {							// 12/17/09
	status_update(the_id, the_val);
	}
	
function status_update(the_id, the_val) {									// write unit status data via ajax xfer
	var querystr = "the_id=" + the_id;
	querystr += "&status=" + the_val
	;

	var url = "up_status.php?" + querystr;			// 
	var payload = syncAjax(url);						// 
	if (payload.substring(0,1)=="-") {	
		alert ("<?php print __LINE__;?>: msg failed ");
		return false;
		}
	else {
	get_requests();
		return true;
		}
	}		// end function status_update()
		
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

function get_requests() {
	the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed; color: #FFFFFF;'>";
	the_string += "<TR class='heading' style='font-weight: bold; font-size: 12px;'>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>ID</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Patient</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Phone</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Contact</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Scope</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Description</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Comments</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Status</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Requested</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Tentative</TD>";	
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Accepted</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Declined</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Resourced</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Completed</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Closed</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>Updated</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px;'>By</TD>";			
	the_string += "</TR>";			
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/list_requests.php?version=" + randomnumber;
	sendRequest (url, requests_cb, "");
	function requests_cb(req) {
		var the_requests=JSON.decode(req.responseText);
		theClass = "background-color: #CECECE";
		for(var key in the_requests) {
			if(the_requests[key][0] == "No Current Requests") {
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD COLSPAN=99 width='100%' style='font-weight: bold; font-size: 16px; text-align: center;'>No Current Requests</TD></TR>";
				} else {
				var the_request_id = the_requests[key][0];
				if((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
					the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
					} else {
					the_onclick = "";
					}
				the_string += "<TR title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px;'>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][13] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][15] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";	
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][19] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][20] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][21] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][22] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][23] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][24] + "</TD>";	
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
				the_string += "</TR>";
				}
			}
			the_string += "</TABLE>";
			$('all_requests').innerHTML = the_string;
			requests_get();			
		}
	}		
	
function requests_get() {
	msgs_interval = window.setInterval('do_requests_loop()', 10000);
	}	
	
function do_requests_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/list_requests.php?version=" + randomnumber;
	sendRequest (url, requests_cb2, "");
	}

function requests_cb2(req) {
	the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
	the_string += "<TR class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>ID</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Patient</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Phone</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Contact</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Scope</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Description</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Comments</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Status</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Requested</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Tentative</TD>";	
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Accepted</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Declined</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Resourced</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Completed</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Closed</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>Updated</TD>";
	the_string += "<TD class='heading' style='font-weight: bold; font-size: 12px; color: #FFFFFF;'>By</TD>";					
	the_string += "</TR>";		
	var the_requests=JSON.decode(req.responseText);
	theClass = "background-color: #CECECE";
	for(var key in the_requests) {
		if(the_requests[key][0] == "No Current Requests") {
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD COLSPAN=99 width='100%' style='font-weight: bold; font-size: 16px; text-align: center;'>No Current Requests</TD></TR>";
			} else {
			var the_request_id = the_requests[key][0];
			if((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
				the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
				} else {
				the_onclick = "";
				}			
			the_string += "<TR title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px;'>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][13] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][15] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";	
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][19] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][20] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][21] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][22] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][23] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][24] + "</TD>";	
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
			the_string += "</TR>";
			}
		}
		the_string += "</TABLE>";
		$('all_requests').innerHTML = the_string;
	}

function do_logout() {
	// clearInterval(mu_interval);
	// mu_interval = null;
	// is_initialized = false;
	document.gout_form.submit();			// send logout 
	}		
	
</SCRIPT>
</HEAD>
<!-- <BODY onLoad = "ck_frames();"> -->
<BODY onLoad="ck_frames(); location.href = '#top'; get_requests();">
	<DIV id='the_banner' class='heading' style='position: fixed; left: 2%; top: 2%; width:90%; border: 2px outset #CECECE; padding: 10px; text-align: center; font-size: 28px;'>Requests</DIV>
	<DIV id='the_list' style='position: fixed; left: 2%; top: 10%; width: 90%; height: 80%; max-height: 90%; border: 2px outset #CECECE; padding: 10px;'>
		<DIV ID='all_requests' style='width: 98%; overflow-y: auto;'></DIV>
	</DIV>	
</BODY>
</HTML>
