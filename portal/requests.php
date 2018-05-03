<?php
/*
9/10/13 - requests.php - lists pending / open requests for Tickets user
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
session_start();						// 
session_write_close();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once '../incs/functions.inc.php';
do_login(basename(__FILE__));
$requester = get_owner($_SESSION['user_id']);

$fromConfig = (array_key_exists('from_config', $_GET) && $_GET['from_config'] == 1) ? 1 : 0;

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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<style type="text/css">
.summ_td_label {text-align: left; border: 1px outset #FFFFFF; font-weight: bold; background-color: #707070; color: #FFFFFF; width: 15%;}
.summ_td_data {border: 1px outset #FFFFFF; background-color: #CECECE; color: #000000; width: 8%;}
table.fixedheadscrolling { cellspacing: 0; border-collapse: collapse; }
table.fixedheadscrolling td {overflow: hidden; }
div.theContainer {border: 1px solid #999;}
div.theArea {overflow: auto; overflow-x: hidden;}
table.scrollable thead tr {position: absolute; left: -1px; top: 0px; }
table.fixedheadscrolling th {text-align: left; border-left: 1px solid #999;}
</style>
<SCRIPT TYPE="application/x-javascript" SRC="../js/jss.js"></SCRIPT>
<SCRIPT SRC="../js/misc_function.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};
var randomnumber;
var the_string;
var theClass = "background-color: #CECECE";
var the_onclick;
var showall = "no";
var viewportwidth;
var viewportheight;
var listwidth;
var listheight;

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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	listwidth = outerwidth * .90;
	listheight = outerheight * .70;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('the_list').style.width = listwidth + "px";
	$('the_list').style.height = listheight + "px";
	$('all_requests').style.width = listwidth + "px";
	$('all_requests').style.maxHeight = listheight + "px";
	get_requests();
	}

function ck_frames() {		// onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	}		// end function ck_frames()

function getHeaderHeight(element) {
	return element.clientHeight;
	}
	
function setTableCells(theTable, tableWidth) {
	var table = document.getElementById(theTable);
	if(table) {
		var headerRow = table.rows[0];
		var tableRow = table.rows[1];
		if(tableRow) {
			for (var i = 0; i < tableRow.cells.length; i++) {
				headerRow.cells[i].style.width = tableRow.cells[i].clientWidth +1 + "px";
				}
			} else {
			var numCols = headerRow.cells.length;
			var cellwidth = tableWidth / numCols;
			for (var i = 0; i < headerRow.cells.length; i++) {
				headerRow.cells[i].style.width = cellwidth + "px";
				}				
			}
		if(getHeaderHeight(headerRow) >= 10) {
			var theRow = table.insertRow(1);
			theRow.style.height = "20px";
			for (var i = 0; i < headerRow.cells.length; i++) {
				var theCell = theRow.insertCell(i);
				theCell.innerHTML = " ";
				}
			}
		}	
	}
	
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

function get_requests() {
	window.msgs_interval = null;
	$('all_requests').innerHTML = "<CENTER><IMG src='../images/owmloading.gif'></CENTER>";
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/list_requests_admin.php?showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, requests_cb, "");
	function requests_cb(req) {
		var the_requests=JSON.decode(req.responseText);
		the_string = "<TABLE id='requeststable' class='fixedheadscrolling scrollable' style='width: 100%;'>";
		the_string += "<thead>";
		the_string += "<TR class='plain_listheader text' style='width: " + window.listwidth + "px;'>";
		the_string += "<TH class='plain_listheader text' style='width: 40px; border-right: 1px solid #FFFFFF;'><?php print get_text('ID');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Patient');?></TH>";
		the_string += "<TH class='plain_listheader text' style='bold; border-right: 1px solid #FFFFFF;'><?php print get_text('Phone');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Contact');?></TH>";
		the_string += "<TH class='plain_listheader text' style='width: 15%; border-right: 1px solid #FFFFFF;'><?php print get_text('Scope');?></TH>";
		the_string += "<TH class='plain_listheader text' style='width: 10%; border-right: 1px solid #FFFFFF;'><?php print get_text('Description');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Status');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Requested');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Updated');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('By');?></TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>...</TH>";				
		the_string += "</TR>";
		the_string += "</thead>";
		the_string += "<tbody>";		
		theClass = "background-color: #CECECE";
		for(var key in the_requests) {
			if(the_requests[key][0] == "No Current Requests") {
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD CLASS='text_biggest text_bold text_center' COLSPAN=99 width='100%'>No Current Requests</TD></TR>";
				} else {
				var the_request_id = the_requests[key][0];
				if((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
					the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
					} else {
					the_onclick = "";
					}
				var theTitle = the_requests[key][13];
				var theField = the_requests[key][13];
				if(theField.length > 48) {
					theField = theField.substring(0,48)+"...";
					}
				the_string += "<TR title='" + theTitle + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px; width: 100%;'>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='width: 15%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + theField + "</TD>";
				the_string += "<TD CLASS='plain_list text' title='" + the_requests[key][14] + "' style='width: 10%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap: sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14].substring(0,24)+"..." + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";	
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
				the_string += "<TD CLASS='plain_list text'style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
				if(the_requests[key][35] != 0) {
					the_string += "<TD CLASS='plain_list text'><SPAN id='ed_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"location.href='../edit.php?id=" + the_requests[key][35] + "';\">Open Ticket</SPAN></TD>";
					} else {
					the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;'>&nbsp;</TD>";
					}
				the_string += "</TR>";
				}
			}
		the_string += "</tbody></TABLE>";
		setTimeout(function() {
			$('all_requests').innerHTML = the_string;
			setTableCells("requeststable", window.listwidth);
			requests_get();
			},1500);
		}
	}		
	
function requests_get() {
	msgs_interval = window.setInterval('do_requests_loop()', 30000);
	}	
	
function do_requests_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/list_requests_admin.php?showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, requests_cb2, "");
	}

function requests_cb2(req) {
	var the_requests=JSON.decode(req.responseText);
	if(the_requests[0] == "No Current Requests") {
		var columnWidth = (window.innerWidth * .93) / 10;
		width = "width: " + columnWidth + "px; ";
		} else {
		width = "";
		}
	the_string = "<TABLE id='requeststable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
	the_string += "<thead>";
	the_string += "<TR class='plain_listheader text' style='width: 100%;'>";
	the_string += "<TH class='plain_listheader text' style='width: 40px; border-right: 1px solid #FFFFFF;'><?php print get_text('ID');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Patient');?></TH>";
	the_string += "<TH class='plain_listheader text' style='bold; border-right: 1px solid #FFFFFF;'><?php print get_text('Phone');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Contact');?></TH>";
	the_string += "<TH class='plain_listheader text' style='width: 15%; border-right: 1px solid #FFFFFF;'><?php print get_text('Scope');?></TH>";
	the_string += "<TH class='plain_listheader text' style='width: 10%; border-right: 1px solid #FFFFFF;'><?php print get_text('Description');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Status');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Requested');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('Updated');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'><?php print get_text('By');?></TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>...</TH>";				
	the_string += "</TR>";
	the_string += "</thead>";
	the_string += "<tbody>";		
	theClass = "background-color: #CECECE";
	for(var key in the_requests) {
		if(the_requests[key][0] == "No Current Requests") {
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD CLASS='text_biggest text_bold text_center COLSPAN=99 width='100%'>No Current Requests</TD></TR>";
			} else {
			var the_request_id = the_requests[key][0];
			if((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
				the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
				} else {
				the_onclick = "";
				}
			var theTitle = the_requests[key][13];
			var theField = the_requests[key][13];
			if(theField.length > 48) {
				theField = theField.substring(0,48)+"...";
				}
			the_string += "<TR title='" + theTitle + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px; width: 100%;'>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='width: 15%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + theField + "</TD>";
			the_string += "<TD CLASS='plain_list text' title='" + the_requests[key][14] + "' style='width: 10%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap: sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14].substring(0,24)+"..." + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";	
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
			the_string += "<TD CLASS='plain_list text'style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
			if(the_requests[key][35] != 0) {
				the_string += "<TD CLASS='plain_list text'><SPAN id='ed_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"location.href='../edit.php?id=" + the_requests[key][35] + "';\">Open Ticket</SPAN></TD>";
				} else {
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;'>&nbsp;</TD>";
				}
			the_string += "</TR>";
			}
		}
	the_string += "</TABLE>";
	setTimeout(function() {
		$('all_requests').innerHTML = the_string;
		setTableCells("requeststable", window.listwidth);
		},1500);
	}
	
function summary_get() {
	summary_interval = window.setInterval('do_summary_loop()', 10000);
	}	
	
function do_summary_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/requests_wallboard.php?version=" + randomnumber;
	sendRequest (url, summary_cb2, "");
	}

function get_summary() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/requests_wallboard.php?version=" + randomnumber;
	sendRequest (url, summary_cb, "");
	function summary_cb(req) {
		var the_summary=JSON.decode(req.responseText);
		var theColor = "style='background-color: #CECECE; color: #000000;'";
		if(the_summary[0] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var numOpen = "<TD class='summ_td_label text'>Requests Open (not accepted): </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[0] + "</TD>";
		if(the_summary[1] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }			
		var numAcc = "<TD class='summ_td_label text'>Requests Accepted (not resourced): </TD><TD class='summ_td_data text'>" + the_summary[1] + "</TD>";
		var numComp = "<TD class='summ_td_label text'>Requests Completed: </TD><TD class='summ_td_data text'>" + the_summary[3] + "</TD>";
		if(the_summary[7] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var totTent = "<TD class='summ_td_label text'>Requests Tentative: </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[7] + "</TD>";
		var totCan = "<TD class='summ_td_label text'>Requests Cancelled: </TD><TD class='summ_td_data text'>" + the_summary[8] + "</TD>";
		var totDec = "<TD class='summ_td_label text'>Requests Declined: </TD><TD class='summ_td_data text'>" + the_summary[9] + "</TD>";
		var summaryText = "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
		summaryText += "<TR>" + numOpen + numAcc + numComp + "</TR>";
		summaryText += "<TR>" + totTent + totCan + totDec + "</TR>";
		summaryText += "</TABLE>";
		$('theSummary').innerHTML = summaryText;
		summary_get();			
		}
	}
	
function summary_cb2(req) {
	var the_summary=JSON.decode(req.responseText);
	var theColor = "style='background-color: #CECECE; color: #000000;'";
	if(the_summary[0] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var numOpen = "<TD class='summ_td_label text'>Requests Open (not accepted): </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[0] + "</TD>";
	if(the_summary[1] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }			
	var numAcc = "<TD class='summ_td_label text'>Requests Accepted (not resourced): </TD><TD class='summ_td_data text'>" + the_summary[1] + "</TD>";
	var numComp = "<TD class='summ_td_label text'>Requests Completed: </TD><TD class='summ_td_data text'>" + the_summary[3] + "</TD>";
	if(the_summary[7] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var totTent = "<TD class='summ_td_label text'>Requests Tentative: </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[7] + "</TD>";
	var totCan = "<TD class='summ_td_label text'>Requests Cancelled: </TD><TD class='summ_td_data text'>" + the_summary[8] + "</TD>";
	var totDec = "<TD class='summ_td_label text'>Requests Declined: </TD><TD class='summ_td_data text'>" + the_summary[9] + "</TD>";
	var summaryText = "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
	summaryText += "<TR>" + numOpen + numAcc + numComp + "</TR>";
	summaryText += "<TR>" + totTent + totCan + totDec + "</TR>";
	summaryText += "</TABLE>";
	$('theSummary').innerHTML = summaryText;
	}

function hide_closed() {
	showall = "no";
	$('hideBut').style.display = "none";
	$('showBut').style.display = "inline-block";
	get_requests();
	}

function show_closed() {
	showall = "yes";
	$('showBut').style.display = "none";
	$('hideBut').style.display = "inline-block";
	get_requests();
	}

function do_logout() {
	document.gout_form.submit();			// send logout 
	}		
	
function stop_timers() {
	window.clearInterval(msgs_interval);
	window.clearInterval(summary_interval);
	}
</SCRIPT>
</HEAD>
<BODY onLoad="ck_frames(); get_requests(); get_summary();" onUnload = "stop_timers();";>
	<DIV ID='outer'>
		<DIV id='the_banner' class='heading text text_center' style='position: fixed; left: 2%; top: 2%; width:92%; border: 2px outset #CECECE; padding: 10px; height: 50px;'>Requests
			<SPAN id='showBut' class='plain text' onMouseOver='do_hover();' onMouseOut='do_plain();' onClick='show_closed();'>Show All</SPAN>
			<SPAN id='hideBut' class='plain text' style='display: none;' onMouseOver='do_hover();' onMouseOut='do_plain();' onClick='hide_closed();'>Hide Closed</SPAN>	
			<SPAN ID='export_but' CLASS='plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.location.href='csv_export.php'"><?php print get_text('Export Requests to CSV');?></SPAN>
			<SPAN id='theSummary' style='height: 20px; width: 60%; float: right;'></SPAN>
		</DIV>
		<DIV class="theContainer" id='the_list' style='position: relative; top: 100px; left: 5%;'>
			<DIV class="theArea" id='all_requests'><CENTER><IMG src='../images/owmloading.gif'></CENTER></DIV>
<?php
			if($fromConfig == 1) {
?>
				<CENTER>
				<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Finished</SPAN>
				</CENTER>
<?php
				}
?>			
		</DIV>
	</DIV>
	<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>
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
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	listwidth = outerwidth * .90;
	listheight = outerheight * .70;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('the_list').style.width = listwidth + "px";
	$('the_list').style.height = listheight + "px";
	$('all_requests').style.width = listwidth + "px";
	$('all_requests').style.maxHeight = listheight + "px";
	</SCRIPT>
</BODY>
</HTML>
