var sortby = '`date`';
var sort = "DESC";
var filterby = '';
var groupby = '';
var thefilter = "";
var the_cal = "";
var filter = "";
var ticket_id = "";
var the_selected_ticket = "";
var the_ticket = "";
var responder_id = "";
var datewidth = "8%";
var msgs_interval = 0;
var the_filter = "";
var theTicket;
var theResponder;
var theFilter;
var theSort;
var theOrder = "DESC";
var theScreen;
var thescreen;
var theStatus;
var msgs_interval = "";
var sentmsgs_interval = "";
var the_messages = "";
var folder = "";

// Browser Window Size and Position
// copyright Stephen Chapman, 3rd Jan 2005, 8th Dec 2005
// you may copy these functions but please keep the copyright notice as well
function pageWidth() {
	return window.innerWidth != null? window.innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body != null ? document.body.clientWidth : null;
	} 
function pageHeight() {
	return  window.innerHeight != null? window.innerHeight : document.documentElement && document.documentElement.clientHeight ?  document.documentElement.clientHeight : document.body != null? document.body.clientHeight : null;
	} 
function posLeft() {
	return typeof window.pageXOffset != 'undefined' ? window.pageXOffset :document.documentElement && document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ? document.body.scrollLeft : 0;
	} 
function posTop() {
	return typeof window.pageYOffset != 'undefined' ?  window.pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ? document.body.scrollTop : 0;
	} 
function posRight() {
	return posLeft()+pageWidth();} function posBottom() {return posTop()+pageHeight();
	}
                    
Array.prototype.inArray = function (value) {
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] == value) {
			return true;
			}
		}
	return false;
	};
					
function sort_switcher(thescreen, ticket_id, responder_id, sort_by, filter) {
	if(sort_by == '`ticket_id`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('type')) {$('type').innerHTML = "Type";}			
		if(theSort == '`ticket_id`') {
			theSort = '`ticket_id`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'ticket_id') {
			theSort = 'ticket_id';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`ticket_id`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('ticket').innerHTML = "Tkt &#9660";			
			} else if(theOrder == "DESC") {
			$('ticket').innerHTML = "Tkt &#9650";				
			} else {
			$('ticket').innerHTML = "Tkt &#9660";			
			}	
	} else if(sort_by == '`msg_type`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}		
		if(theSort == '`msg_type`') {
			theSort = '`msg_type`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'msg_type') {
			theSort = 'msg_type';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`msg_type`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('type').innerHTML = "Typ &#9660";
			} else if(theOrder == "DESC") {
			$('type').innerHTML = "Typ &#9650";			
			} else {
			$('type').innerHTML = "Typ &#9660";			
			}	
	} else if(sort_by == '`fromname`') {
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`fromname`') {
			theSort = '`fromname`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'fromname') {
			theSort = 'fromname';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`fromname`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('fromname').innerHTML = "F &#9660";			
			} else if(theOrder == "DESC") {
			$('fromname').innerHTML = "F &#9650";				
			} else {
			$('fromname').innerHTML = "F &#9660";			
			}	
	} else if(sort_by == '`recipients`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`recipients`') {
			theSort = '`recipients`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}
			} else if(theSort == 'recipients') {
			theSort = 'recipients';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`recipients`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('recipients').innerHTML = "To &#9660";			
			} else if(theOrder == "DESC") {
			$('recipients').innerHTML = "To &#9650";				
			} else {
			$('recipients').innerHTML = "To &#9660";			
			}				
	} else if(sort_by == '`subject`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('message')) {$('message').innerHTML = "Message";}			
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`subject`') {
			theSort = '`subject`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'subject') {
			theSort = 'subject';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`subject`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('subject').innerHTML = "Subject &#9660";			
			} else if(theOrder == "DESC") {
			$('subject').innerHTML = "Subject &#9650";				
			} else {
			$('subject').innerHTML = "Subject &#9660";			
			}	
	} else if(sort_by == '`message`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}			
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`message`') {
			theSort = '`message`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'message') {
			theSort = 'message';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`message`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('message').innerHTML = "Message &#9660";			
			} else if(theOrder == "DESC") {
			$('message').innerHTML = "Message &#9650";				
			} else {
			$('message').innerHTML = "Message &#9660";			
			}			
	} else if(sort_by == '`date`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`date`') {
			theSort = '`date`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'date') {
			theSort = 'date';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`date`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('date').innerHTML = "Date &#9660";			
			} else if(theOrder == "DESC") {
			$('date').innerHTML = "Date &#9650";				
			} else {
			$('date').innerHTML = "Date &#9660";			
			}
	} else if(sort_by == '`_by`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`_by`') {
			theSort = '`_by`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}
			} else if(theSort == '_by') {
			theSort = '_by';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`_by`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('owner').innerHTML = "Owner &#9660";			
			} else if(theOrder == "DESC") {
			$('owner').innerHTML = "Owner &#9650";				
			} else {
			$('owner').innerHTML = "Owner &#9660";			
			}	
	} else {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '') {
			theSort = '`date`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('date').innerHTML = "Date &#9660";			
			} else if(theOrder == "DESC") {
			$('date').innerHTML = "Date &#9650";				
			} else {
			$('date').innerHTML = "Date &#9660";			
			}	
		}
	if(folder == "inbox") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);
				} else if((theTicket == "") && (theResponder =="")) {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}
			} else {
			if(theTicket != "") {		
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}		
			}
		} else if(folder == "wastebasket") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);
				} else if((theTicket == "") && (theResponder =="")) {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}
			} else {
			if(theTicket != "") {		
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}		
			}		
		} else if(folder == "sent") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}
			} else {
			if(theTicket != "") {		
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);			
				} else if(theResponder != "") {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}		
			}		
		} else if(folder == "archive") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else if(theResponder != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else {
				}
			} else {
			if(theTicket != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);			
				} else if(theResponder != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else {
				}		
			}
		}
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

function startup() {
	$('date').innerHTML = "Date &#9660";
	}
	
function get_arch_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen, archive) {
	if((sortby == '`ticket_id`') || (sortby == 'ticket_id')) {
		theSort = 'ticket_id';
		} else if((sortby == '`msg_type`') || (sortby == 'msg_type')) { 
		theSort = 'msg_type';
		} else if((sortby == '`fromname`') || (sortby == 'fromname')) {
		theSort = 'fromname';
		} else if((sortby == '`recipients`') || (sortby == 'recipients')) { 
		theSort = 'recipients';
		} else if((sortby == '`subject`') || (sortby == 'subject')) {
		theSort = 'subject';
		} else if((sortby == '`message`') || (sortby == 'message')) { 
		theSort = 'message';
		} else if((sortby == '`date`') || (sortby == 'date')) {
		theSort = 'date';
		} else if((sortby == '`_by`') || (sortby == '_by')) { 
		theSort = '_by';
		} else {
		theSort = 'date';
		}
	theTicket = ticket_id;
	theResponder = responder_id;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_arch_msgs.php?filename='+archive+'&sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, arch_mess_cb, "");
	function arch_mess_cb(req) {
		the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: normal; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_row = parseInt(the_messages[key][12]) + 1;
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR title='" + the_messages[key][11] + "' style='" + theClass + "; border-bottom: 2px solid #000000;'>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>&nbsp;</TD>";
					the_string += "</TR>";
					if(theClass == "background-color: #CECECE") {
						theClass = "background-color: #DEDEDE";
						} else {
						theClass = "background-color: #CECECE";	
						}
					}
				}
			}
			the_string += "</TABLE>";
			$('message_list').innerHTML = "Loading Messages............";
			setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
		}
	}		
	
function get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	window.clearInterval(msgs_interval);
	window.clearInterval(sentmsgs_interval);
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	folder = "inbox";
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
		the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: bold; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_text = "";
					switch(the_messages[key][12]) {
						case "0":
 							the_text = "Undelivered";
							the_del_flag = "color: red;";
   							break;
 						case "1":
   							the_text = "Partially Delivered";
							the_del_flag = "color: blue;";
   							break;
 						case "2":
 							the_text = "Delivered";
							the_del_flag = "color: green;";
   							break;
						case "3":
							the_text = "Not Applicable";
							the_del_flag = "color: black;";
							break;
 						default:
 							the_text = "Error";
 						}
//					if(the_messages[key][2] == "IS") { the_del_flag = "color: green;" }
					var the_delstat = "Delivery Status: " + the_text + " ---- ";
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR title='" + the_delstat + the_messages[key][11] + "' style='" + theClass + "; border-bottom: 2px solid #000000;'>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
					if(thelevel == '1') {
						the_string += "&nbsp;&nbsp;<SPAN style='color: red;' onclick='del_message(" + the_message_id + ", \"inbox\")'>&nbsp;&nbsp;X</SPAN></TD>";
						} else {
						the_string += "&nbsp;&nbsp;</TD>";
						}
					the_string += "</TR>";
					if(theClass == "background-color: #CECECE") {
						theClass = "background-color: #DEDEDE";
						} else {
						theClass = "background-color: #CECECE";	
						}
					}
				}
			}
			the_string += "</TABLE>";
			$('message_list').innerHTML = "Loading Messages............";
			setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
			main_messagelist_get(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);			
		}
	}		
	
function main_messagelist_get(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	msgs_interval = window.setInterval('do_main_msgs_loop(\''+theTicket+'\',\''+theResponder+'\',\''+theSort+'\',\''+theOrder+'\',\''+theFilter+'\',\''+theScreen+'\')', 60000);
	}	
	
function do_main_msgs_loop(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	folder = "inbox";
	if(thescreen == "ticket") {
		datewidth = "10%";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
		var url = './ajax/list_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, main_msg_cb, "");
	}

function main_msg_cb(req) {
	var the_string = "";	
	the_messages=JSON.decode(req.responseText);
	var theClass = "background-color: #CECECE";
	for(var key in the_messages) {
		if(the_messages[key][0] == "No Messages") {
			the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
			} else {
			var the_message_id = the_messages[key][0]	
			var the_record_id = the_messages[key][10]				
			if(the_record_id) {	
				if(the_messages[key][9] == 0) {
					theStatus = "font-weight: bold; font-style: normal;";
					} else {
					theStatus = "font-weight: normal; font-style: normal;";
					}		
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
				if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
				if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
				if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
				if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
				if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
				if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
				if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
				the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
				if(thelevel == '1') {
					the_string += "&nbsp;&nbsp;<SPAN style='color: red;' onclick='del_message(" + the_message_id + ", \"inbox\")'>&nbsp;&nbsp;X</SPAN></TD>";
					} else {
					the_string += "&nbsp;&nbsp;</TD>";
					}
				the_string += "</TR>";
				if(theClass == "background-color: #CECECE") {
					theClass = "background-color: #DEDEDE";
					} else {
					theClass = "background-color: #CECECE";	
					}
				}
			}
		}
		the_string += "</TABLE>";
		$('message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
	}
	
function expand(id) {
	var the_msg = "M" + id;
	var the_control = "C" + id;
	if($(the_msg).style.height == 'auto') {
		$(the_msg).style.height = '14px';
		$(the_control).innerHTML = "&#9660";
		} else {
		$(the_msg).style.height = 'auto';
		$(the_control).innerHTML = "&#9650";		
		}
	}
	
function do_filter(folder) {
	filter = document.the_filter.frm_filter.value;
	theFilter = filter;
	if(filter == "") {
		} else {
		if(folder == "inbox") {
			get_main_messagelist(theTicket,theResponder,theSort, theOrder,theFilter,theScreen);
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "sent") {
			get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "wastebasket") {
			get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "archive") {
			get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);				
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "all") {
			get_all_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);				
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";				
			} else {
			get_main_messagelist(theTicket,theResponder,theSort, theOrder,theFilter,theScreen);
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			}
		}
	}
	
function clear_filter(folder) {
	if(folder == "inbox") {
		get_main_messagelist(theTicket,theResponder,theSort, theOrder,'', theScreen);		
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "sent") {
		get_sent_messagelist(theTicket, theResponder, theSort, theOrder, '', theScreen);
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "wastebasket") {
		get_wastelist(theTicket, theResponder, theSort, theOrder, '', theScreen);
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "archive") {
		get_arch_messagelist(theTicket, theResponder, theSort, theOrder, '', theScreen, archive);
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "all") {
		get_all_messagelist(theTicket, theResponder, theSort, theOrder, '', theScreen);
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";		
		} else {	
		get_main_messagelist(theTicket,theResponder,theSort, theOrder,'', theScreen);		
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		}		
	}
	
function select_ticket(ticket_id, filter) {
	theTicket = ticket_id;
	get_main_messagelist(ticket_id, responder_id,sortby,'DESC', filter, thescreen);
	the_ticket = ticket_id;
	$('filter_box').onclick = do_filter(the_ticket);
	$('the_clear').onclick = clear_filter(the_ticket);	
	}
	
function read_status(status, id, thescreen,ticket_id,responder_id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/msg_status.php?status=" + status + "&id=" + id + "&version=" + randomnumber;
	sendRequest (url, msgstat_cb, "");
	function msgstat_cb(req) {
		var theresp=JSON.decode(req.responseText);
		if(theresp[0] == 100) {
			get_inbox();
			} else {
			}
		}
	}

function refresh_opener(the_screen, thefolder) {
	if(the_screen == "ticket") {
		if(thefolder== "inbox") {
			window.opener.get_mainmessages();
			} else if(thefolder== "sent") {
			window.opener.get_sentmessages();	
			} else if(thefolder== "archive") {
			window.opener.get_archive(archive, thebutton);
			} else {
			}
		} else if (the_screen == "messages") {
		get_mainmessages();
		} else {
		get_mainmessages();
		}
	}

function refresh_waste(the_screen) {
	if(the_screen = "ticket") {
		window.opener.get_wastebin();
		} else if (the_screen = "messages") {
		get_wastebin();
		} else {
		get_wastebin();
		}
	}

function del_message(id, folder) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/del_message.php?id=" + id + "&version=" + randomnumber;	
	if (confirm("Are you sure you want to delete this message?")) { 	
		sendRequest (url, msgdel_cb, "");
		}
	function msgdel_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			if(folder == 'inbox') {
				get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
				} else if(folder == 'sent') {
				get_sent_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);
				} else {
				get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
				}
			} else {
			alert("Error deleting the message, please try again.");
			}
		}
	}

function del_all_messages() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/del_messages.php?version=" + randomnumber;	
	if (confirm("Are you sure you want to delete all the messages?")) { 	
		sendRequest (url, msgsdel_cb, "");
		}
	function msgsdel_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
			} else {
			alert("Error deleting messages, please try again.");
			}
		}
	}
	
function empty_waste() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/empty_wastebasket.php?version=" + randomnumber;	
	if (confirm("Are you sure you want to empty the wastebin?")) { 	
		sendRequest (url, emp_waste_cb, "");
		}
	function emp_waste_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			alert("Wastebasket Emptied");
			get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
			} else {
			alert("Error emptying the wastebasket, please try again.");			
			}
		}
	}
	
function restore_msg(id, folder) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/restore_message.php?id=" + id + "&version=" + randomnumber;	
	if (confirm("Are you sure you want to restore this message?")) { 	
		sendRequest (url, emp_waste_cb, "");
		}
	function emp_waste_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
			} else {
			alert("Error restoring the message, please try again.");			
			}
		}
	}
	
function get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	window.clearInterval(msgs_interval);
	window.clearInterval(sentmsgs_interval);
	var datewidth = "8%";
	if(screen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((ticket_id != "") && (ticket_id != 0)) { 
		the_selected_ticket = "&ticket_id=" + ticket_id;
		} else {
		the_selected_ticket = "";
		}
	if((responder_id != "") && (responder_id != 0)) { 
		the_selected_responder = "&responder_id=" + responder_id;
		} else {
		the_selected_responder = "";
		}		
	if(filter != "") {
		thefilter = "&filter=" + filter;
		} else {
		thefilter = "";
		}	
	var url ='./ajax/list_waste_messages.php?sort='+sortby+'&columns='+columns+'&way='+sort+thefilter+the_selected_ticket+the_selected_responder+"&screen=" + thescreen + "&version=" + randomnumber;
	sendRequest (url, waste_mess_cb, "");
	function waste_mess_cb(req) {
		var the_string = "";
		the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		var theStatus = "font-weight: normal";
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				if($('empty_waste')) { $('empty_waste').style.display = "none";}
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: normal; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>&nbsp;&nbsp;<SPAN style='color: red;' onclick='restore_msg(" + the_message_id + ", 'wastebasket')'>&nbsp;&nbsp;R</SPAN></TD>";			
					the_string += "</TR>";
					if(theClass == "background-color: #CECECE") {
						theClass = "background-color: #DEDEDE";
						} else {
						theClass = "background-color: #CECECE";	
						}
					if($('empty_waste')) { $('empty_waste').style.display = "inline-block";}
					}
				}
			}
		the_string += "</TABLE>";
		$('message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
		}
	}	

function get_sent_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	$('message_list').innerHTML = "";
	var the_sentstring = "";	
	window.clearInterval(msgs_interval);
	window.clearInterval(sentmsgs_interval);
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_sent_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, sent_mess_cb, "");
	function sent_mess_cb(req) {
		the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_sentstring += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_sentstring += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: bold; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_text = "";
					switch(the_messages[key][12]) {
						case "0":
 							the_text = "Undelivered";
							the_del_flag = "color: red;";
   							break;
 						case "1":
   							the_text = "Partially Delivered";
							the_del_flag = "color: blue;";
   							break;
 						case "2":
 							the_text = "Delivered";
							the_del_flag = "color: green;";
   							break;
						case "3":
							the_text = "Not Applicable";
							the_del_flag = "color: black;";
							break;
 						default:
 							the_text = "Error";
 						}
					var the_delstat = "Delivery Status: " + the_text + " ---- ";
					the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_sentstring += "<TR title='" + the_delstat + the_messages[key][11] + "' style='" + theClass + "; border-bottom: 2px solid #000000;'>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_sentstring += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_sentstring += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_sentstring += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_sentstring += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_sentstring += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
					if(thelevel == '1') {
						the_sentstring += "&nbsp;&nbsp;<SPAN style='color: red;' onclick='del_message(" + the_message_id + ", \"sent\")'>&nbsp;&nbsp;X</SPAN></TD>";
						} else {
						the_sentstring += "&nbsp;&nbsp;</TD>";
						}
					the_sentstring += "</TR>";
					if(theClass == "background-color: #CECECE") {
						theClass = "background-color: #DEDEDE";
						} else {
						theClass = "background-color: #CECECE";	
						}
					}
				}
			}
			the_sentstring += "</TABLE>";
			$('message_list').innerHTML = "";
			$('message_list').innerHTML = "Loading Messages............";
			setTimeout(function() {$('message_list').innerHTML = the_sentstring ;},1000);
			sent_messagelist_get(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);			
		}
	}		
	
function sent_messagelist_get(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	sentmsgs_interval = window.setInterval('do_sent_msgs_loop(\''+theTicket+'\',\''+theResponder+'\',\''+theSort+'\',\''+theOrder+'\',\''+theFilter+'\',\''+theScreen+'\')', 60000);
	}	
	
function do_sent_msgs_loop(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	folder = "sent";
	if(thescreen == "ticket") {
		datewidth = "10%";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
		var url = './ajax/list_sent_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, sent_msg_cb2, "");
	}

function sent_msg_cb2(req) {
	$('message_list').innerHTML = "";
	var the_sentstring = "";	
	the_messages=JSON.decode(req.responseText);
	var theClass = "background-color: #CECECE";
	for(var key in the_messages) {
		if(the_messages[key][0] == "No Messages") {
			the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
			the_sentstring += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_sentstring += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
			} else {
			var the_message_id = the_messages[key][0]	
			var the_record_id = the_messages[key][10]				
			if(the_record_id) {	
				if(the_messages[key][9] == 0) {
					theStatus = "font-weight: bold; font-style: normal;";
					} else {
					theStatus = "font-weight: normal; font-style: normal;";
					}		
				the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
				the_sentstring += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
				if(the_columns.inArray('2')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
				if(the_columns.inArray('3')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
				if(the_columns.inArray('4')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
				if(the_columns.inArray('5')) {the_sentstring += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
				if(the_columns.inArray('6')) {the_sentstring += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
				if(the_columns.inArray('7')) {the_sentstring += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
				if(the_columns.inArray('8')) {the_sentstring += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
				the_sentstring += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
				if(thelevel == '1') {
					the_sentstring += "&nbsp;&nbsp;<SPAN style='color: red;' onclick='del_message(" + the_message_id + ", \"inbox\")'>&nbsp;&nbsp;X</SPAN></TD>";
					} else {
					the_sentstring += "&nbsp;&nbsp;</TD>";
					}
				the_sentstring += "</TR>";
				if(theClass == "background-color: #CECECE") {
					theClass = "background-color: #DEDEDE";
					} else {
					theClass = "background-color: #CECECE";	
					}
				}
			}
		}
		the_sentstring += "</TABLE>";
		$('message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
	}
	
function get_all_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	folder="all"
	window.clearInterval(msgs_interval);
	window.clearInterval(sentmsgs_interval);
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_all_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, all_mess_cb, "");
	function all_mess_cb(req) {
		the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: bold; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_text = "";
					switch(the_messages[key][12]) {
						case "0":
 							the_text = "Undelivered";
							the_del_flag = "color: red;";
   							break;
 						case "1":
   							the_text = "Partially Delivered";
							the_del_flag = "color: blue;";
   							break;
 						case "2":
 							the_text = "Delivered";
							the_del_flag = "color: green;";
   							break;
						case "3":
							the_text = "Not Applicable";
							the_del_flag = "color: black;";
							break;
 						default:
 							the_text = "Error";
 						}
					var the_delstat = "Delivery Status: " + the_text + " ---- ";
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR title='" + the_delstat + the_messages[key][11] + "' style='" + theClass + "; border-bottom: 2px solid #000000;'>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "</TR>";
					if(theClass == "background-color: #CECECE") {
						theClass = "background-color: #DEDEDE";
						} else {
						theClass = "background-color: #CECECE";	
						}
					}
				}
			}
			the_string += "</TABLE>";
			$('message_list').innerHTML = "Loading Messages............";
			setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
			all_messagelist_get(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);			
		}
	}		
	
function all_messagelist_get(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	msgs_interval = window.setInterval('do_all_msgs_loop(\''+theTicket+'\',\''+theResponder+'\',\''+theSort+'\',\''+theOrder+'\',\''+theFilter+'\',\''+theScreen+'\')', 60000);
	}	
	
function do_all_msgs_loop(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	folder = "all";
	if(thescreen == "ticket") {
		datewidth = "10%";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
		var url = './ajax/list_all_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, all_msg_cb, "");
	}

function all_msg_cb(req) {
	folder="all";
	var the_string = "";	
	the_messages=JSON.decode(req.responseText);
	var theClass = "background-color: #CECECE";
	for(var key in the_messages) {
		if(the_messages[key][0] == "No Messages") {
			the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
			} else {
			var the_message_id = the_messages[key][0]	
			var the_record_id = the_messages[key][10]				
			if(the_record_id) {	
				if(the_messages[key][9] == 0) {
					theStatus = "font-weight: bold; font-style: normal;";
					} else {
					theStatus = "font-weight: normal; font-style: normal;";
					}		
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
				if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
				if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
				if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
				if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
				if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + the_messages[key][6] + "</DIV></TD>";}
				if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
				if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
				the_string += "</TR>";
				if(theClass == "background-color: #CECECE") {
					theClass = "background-color: #DEDEDE";
					} else {
					theClass = "background-color: #CECECE";	
					}
				}
			}
		}
		the_string += "</TABLE>";
		$('message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
	}