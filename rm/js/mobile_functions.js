function do_sub(the_status) {				// form submitted
	var params = "frm_id="+the_assigns_id;
	params += "&frm_tick="+tick_id;
	params += "&frm_unit="+responder_id;
	params += "&frm_vals="+ the_status;
	sendRequest ('./ajax/update_assigns.php',handleResult, params);			// does the work
	get_ticket(tick_id) 	
	}			// end function do_sub()

function start_miles() {
	var value = prompt("Enter your start miles", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_mileage.php?type=start_miles&value=' + value + '&assigns_id=' + the_assigns_id + '&version=' + randomnumber;
	sendRequest (url, st_miles_cb, "");
	function st_miles_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('mileage_start_but').innerHTML = "Start Miles<BR />" + value; 
			$('mileage_start_but').style.backgroundColor = "#66FF00"; 
			$('mileage_start_but').style.color = "#707070";			
			}
		}
	}
	
function end_miles() {
	var value = prompt("Enter your end miles", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_mileage.php?type=end_miles&value=' + value + '&assigns_id=' + the_assigns_id + '&version=' + randomnumber;
	sendRequest (url, end_miles_cb, "");
	function end_miles_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('mileage_end_but').innerHTML = "End Miles<BR />" + value; 
			$('mileage_end_but').style.backgroundColor = "#66FF00"; 
			$('mileage_end_but').style.color = "#707070";
			}
		}	
	}
	
function os_miles() {
	var value = prompt("Enter your on scene miles", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_mileage.php?type=on_scene_miles&value=' + value + '&assigns_id=' + the_assigns_id + '&version=' + randomnumber;
	sendRequest (url, os_miles_cb, "");
	function os_miles_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('mileage_os_but').innerHTML = "On Scene Miles<BR />" + value; 
			$('mileage_os_but').style.backgroundColor = "#66FF00"; 
			$('mileage_os_but').style.color = "#707070";			
			}	
		}	
	}
	
function notes() {
	user_id = <?php print $the_user;?>;
	var value = prompt("Enter call notes", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_notes.php?notes=' + value + '&user_id=' + user_id + '&assigns_id=' + the_assigns_id + '&ticket_id=' + tick_id + '&version=' + randomnumber;
	sendRequest (url, notes_cb, "");
	function notes_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('notes_but').style.backgroundColor = "#66FF00"; 
			$('notes_but').style.color = "#707070";			
			}	
		}	
	get_ticket(tick_id);
	}

function get_messages() {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/messagelist.php?version=' + randomnumber;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
		the_messages=req.responseText;
		$('message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('message_list').innerHTML = the_messages;},1000);
		}
	}	

function get_alerts() {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/alertlist.php?version=' + randomnumber;
	sendRequest (url, alerts_cb, "");
	function alerts_cb(req) {
		the_alerts=req.responseText;
		$('alert_list').innerHTML = "Loading Alerts............";
		setTimeout(function() {$('alert_list').innerHTML = the_alerts;},1000);
		}
	}	

function get_tickets(user_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/ticket_list.php?user_id=' + user_id + '&version=' + randomnumber;
	sendRequest (url, tickets_cb, "");
	function tickets_cb(req) {
		var the_tickets=req.responseText;
		$('ticket_list').innerHTML = "Loading Ticket List............";
		setTimeout(function() {$('ticket_list').innerHTML = the_tickets;},1000);
		}
	}	
	
function get_ticket_markers(user_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/ticket_markers.php?user_id=' + user_id + '&version=' + randomnumber;
	sendRequest (url, ticketMarkers_cb, "");
	function ticketMarkers_cb(req) {
		var the_tickets=JSON.decode(req.responseText);
		for(var key in the_tickets) {
			var tkt_id = the_tickets[key][0];
			var tkt_scope = the_tickets[key][1];
			var tkt_lat = the_tickets[key][2];
			var tkt_lng = the_tickets[key][3];
			var tkt_desc = the_tickets[key][4];
			var tkt_opened = the_tickets[key][5];
			var info = "<TABLE style='width: 100%;'><TR><TDstyle='font-weight: bold;'>" + tkt_scope + "</TD></TR>";
			info += "<TR><TD>" + tkt_desc + "</TD></TR>";
			info += "<TR><TD>" + tkt_opened + "</TD></TR></TABLE>";
			var icon = "black.png";
			createMarker(tkt_lat, tkt_lng, info, icon, tkt_scope);
			}
		}
	}

function get_ticket(ticket_id) {
	tick_id = ticket_id;
	randomnumber=Math.floor(Math.random()*99999999);
	user_id = <?php print $the_user;?>;
	url ='./ajax/ticket_detail.php?ticket_id=' + ticket_id + '&user_id=' + user_id + '&version=' + randomnumber;
	sendRequest (url, ticket_cb, "");
	function ticket_cb(req) {
		var the_ticket=JSON.decode(req.responseText);
		the_assigns_id = the_ticket[0];
		$('ticket_list').style.display = "none";
		$('ticket_detail').style.display = "block";				
		$('ticket_detail_wrapper').style.display = "block";	
		var the_text_alert = "Dispatching Assigns ID " + the_assigns_id;
		$('disp_but').innerHTML = "Dispatched<BR />" + the_ticket[1]; 
		if(the_ticket[1] != "") { $('disp_but').setAttribute( "onClick", "" ); $('disp_but').setAttribute( "onMouseover", "" );	$('disp_but').setAttribute( "onMouseout", "" );}
		$('resp_but').innerHTML = "Responding<BR />" + the_ticket[2]; 
		if(the_ticket[2] != "") { $('resp_but').setAttribute( "onClick", "" ); $('resp_but').setAttribute( "onMouseover", "" );	$('resp_but').setAttribute( "onMouseout", "" );}
		$('os_but').innerHTML = "On Scene<BR />" + the_ticket[3]; 
		if(the_ticket[3] != "") { $('os_but').setAttribute( "onClick", "" ); $('os_but').setAttribute( "onMouseover", "" ); $('os_but').setAttribute( "onMouseout", "" );}
		$('fenr_but').innerHTML = "Fac enroute<BR />" + the_ticket[4]; 
		if(the_ticket[4] != "") { $('fenr_but').setAttribute( "onClick", "" ); $('fenr_but').setAttribute( "onMouseover", "" ); $('fenr_but').setAttribute( "onMouseout", "" );}
		$('farr_but').innerHTML = "Fac Arrived<BR />" + the_ticket[5]; 
		if(the_ticket[5] != "") { $('farr_but').setAttribute( "onClick", "" ); $('farr_but').setAttribute( "onMouseover", "" ); $('farr_but').setAttribute( "onMouseout", "" );}
		$('clear_but').innerHTML = "Clear<BR />" + the_ticket[6]; 
		if(the_ticket[6] != "") { $('clear_but').setAttribute( "onClick", "" ); $('clear_but').setAttribute( "onMouseover", "" ); $('clear_but').setAttribute( "onMouseout", "" );}
		if((the_ticket[7] != "") && (the_ticket[7] != 0)) { $('mileage_start_but').innerHTML = "Start Miles<BR />" + the_ticket[7];} 
		if((the_ticket[7] != "") && (the_ticket[7] != 0)) { $('mileage_start_but').setAttribute( "onClick", "" ); $('mileage_start_but').setAttribute( "onMouseover", "" ); $('mileage_start_but').setAttribute( "onMouseout", "" );}
		if((the_ticket[8] != "") && (the_ticket[8] != 0)) { $('mileage_end_but').innerHTML = "End Miles<BR />" + the_ticket[8]; }
		if((the_ticket[8] != "") && (the_ticket[8] != 0)) { $('mileage_end_but').setAttribute( "onClick", "" ); $('mileage_end_but').setAttribute( "onMouseover", "" ); $('mileage_end_but').setAttribute( "onMouseout", "" );}
		if((the_ticket[9] != "") && (the_ticket[9] != 0)) {$('mileage_os_but').innerHTML = "On Scene Miles<BR />" + the_ticket[9]; }
		if((the_ticket[9] != "") && (the_ticket[9] != 0)) { $('mileage_os_but').setAttribute( "onClick", "" ); $('mileage_os_but').setAttribute( "onMouseover", "" ); $('mileage_os_but').setAttribute( "onMouseout", "" );}	
		if(the_ticket[1] != "") { $('disp_but').style.backgroundColor = "#66FF00"; $('disp_but').style.color = "#707070"; }
		if(the_ticket[2] != "") { $('resp_but').style.backgroundColor = "#66FF00"; $('resp_but').style.color = "#707070"; }
		if(the_ticket[3] != "") { $('os_but').style.backgroundColor = "#66FF00"; $('os_but').style.color = "#707070"; }
		if(the_ticket[4] != "") { $('fenr_but').style.backgroundColor = "#66FF00"; $('fenr_but').style.color = "#707070"; }
		if(the_ticket[5] != "") { $('farr_but').style.backgroundColor = "#66FF00"; $('farr_but').style.color = "#707070"; }
		if(the_ticket[6] != "") { $('clear_but').style.backgroundColor = "#66FF00"; $('clear_but').style.color = "#707070"; }	
		if((the_ticket[7] != "") && (the_ticket[7] != 0)) { $('mileage_start_but').style.backgroundColor = "#66FF00"; $('mileage_start_but').style.color = "#707070"; }	
		if((the_ticket[8] != "") && (the_ticket[8] != 0)) { $('mileage_end_but').style.backgroundColor = "#66FF00"; $('mileage_end_but').style.color = "#707070"; }	
		if((the_ticket[9] != "") && (the_ticket[9] != 0)) { $('mileage_os_but').style.backgroundColor = "#66FF00"; $('mileage_os_but').style.color = "#707070"; }	
		if(the_ticket[12] != "") { $('notes_but').style.backgroundColor = "#66FF00"; $('notes_but').style.color = "#707070"; }				
		$('resp_but').setAttribute( "onClick", "javascript:do_sub('frm_responding');" );
		$('os_but').setAttribute( "onClick", "javascript:do_sub('frm_on_scene');" );
		if(the_ticket[10] == 0) {
			$('fenr_but').style.display = "none";
			$('farr_but').style.display = "none";
			} else {
			$('fenr_but').setAttribute( "onClick", "javascript:do_sub('frm_u2fenr');" );
			$('farr_but').setAttribute( "onClick", "javascript:do_sub('frm_u2farr');" );
			}
		$('clear_but').setAttribute( "onClick", "javascript:do_sub('frm_clear');" );		
		$('ticket_detail').innerHTML = "Loading Ticket Details............";
		setTimeout(function() {$('ticket_detail').innerHTML = the_ticket[11];},1000);
		}
	}	
	
function get_message(message_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/message_detail.php?message_id=' + message_id + '&version=' + randomnumber;
	sendRequest (url, message_cb, "");
	function message_cb(req) {
		var the_message=JSON.decode(req.responseText);
		var the_return_add = the_message[0];
		var tickets_address = "<?php print get_variable('email_reply_to');?>";
		$('message_list').style.display = "none";
		$('message_detail').style.display = "block";				
		$('message_detail').innerHTML = "Loading Message Details............";
		setTimeout(function() {$('message_detail').innerHTML = the_message[1];},1000);
		setTimeout(function() {if(the_return_add != tickets_address) { $('reply_but').style.display = "inline";}},1000);		
		}
	}	

function do_reply(to_address) {
	var user_email = "<?php print $the_email;?>";
	$('message_detail').style.display = "none";
	$('message_reply').style.display = "block";
	document.reply_form.frm_to.value =	to_address;
	document.reply_form.frm_from.value = user_email;	
	}

function get_alert(alert_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/alert_detail.php?alert_id=' + alert_id + '&version=' + randomnumber;
	sendRequest (url, ticket_cb, "");
	function ticket_cb(req) {
		var the_ticket=req.responseText;
		$('alert_list').style.display = "none";
		$('alert_detail').style.display = "block";				
		$('alert_detail').innerHTML = "Loading Alert Details............";
		setTimeout(function() {$('alert_detail').innerHTML = the_ticket;},1000);
		}
	}	

function close_alert_detail() {
	$('close_alert_detail').style.display = "none";
	$('alert_detail').style.display = "none";
	$('alert_list').style.display = "block";	
	}
	
function close_ticket_detail() {
	$('ticket_detail').style.display = "none";
	$('ticket_detail_wrapper').style.display = "none";	
	$('ticket_list').style.display = "block";	
	}

function close_message_detail() {
	$('message_detail').style.display = "none";
	$('message_list').style.display = "block";	
	}

function slideIt(theDiv, theButton) {
	var slidingDiv = $(theDiv);
	var stopPosition = 0;
	if (parseInt(slidingDiv.style.left) < stopPosition ) {
		slidingDiv.style.left = parseInt(slidingDiv.style.left) + 4 + "px";
//		setTimeout(slideIt(theDiv, theButton), .5);
		setTimeout(function(){slideIt(theDiv, theButton)}, .5);
		
		}
	$(theButton).setAttribute( "onClick", 'javascript: slideIn("' + theDiv + '", this.id);' );	
	$(theButton).innerHTML = "Hide Menu";	
	}

function slideIn(theDiv, theButton) {
	var slidingDiv = $(theDiv);
	var stopPosition = -150;
	if (parseInt(slidingDiv.style.left) > stopPosition ) { 
		slidingDiv.style.left = parseInt(slidingDiv.style.left) - 4 + "px";
//		setTimeout(slideIn(theDiv,theButton), .5);
		setTimeout(function(){slideIn(theDiv, theButton)}, .5);		
		}
	$(theButton).setAttribute( "onClick", 'javascript: slideIt("' + theDiv + '", this.id);' );	
	$(theButton).innerHTML = "Show Menu";		
	}
	
function get_advert() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/advert.php?version=" + randomnumber;
	sendRequest (url, advert_cb, "");
	function advert_cb(req) {
		var ad_response=JSON.decode(req.responseText);
		if(ad_response[0] == 100) {
			alert("error");
			} else {
			var the_alt = ad_response[0];
			var the_url = ad_response[1];
			var the_picture = ad_response[2];
			document.getElementById('ad_pic').src = the_picture;	
			document.getElementById('ad_pic').alt = the_alt;				
			document.getElementById('ad_link').href = the_url;
			document.getElementById('ad_link').title = the_url;	
			document.getElementById('ad_text').innerHTML = "Advert Text Goes Here";
			}
		}
	}	
	
function get_conditions() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/infolist2.php?version=" + randomnumber;
	sendRequest (url, info_cb, "");
	function info_cb(req) {
		var cond_response=JSON.decode(req.responseText);
		for(var key in cond_response) {
			if(cond_response[key][0] == 100) {	
				alert("error");
				} else {
				var the_id = cond_response[key][0];
				var the_title = cond_response[key][1];	
				var the_notes = cond_response[key][2];			
				var the_description = cond_response[key][3];
				var the_type = cond_response[key][4];
				var the_user = cond_response[key][5];	
				var theLat = cond_response[key][6];	
				var theLng = cond_response[key][7];	
				var theIcon = cond_response[key][8];
				var theAddress = cond_response[key][9];	
				var theInfo = the_description + "<BR />" + theAddress + "<BR />";
				var title = the_title + "\r\n" + theAddress;
				createMarker(theLat, theLng, theInfo, theIcon, title);				
				}
			}
		get_geo();
		}
	}

function sub_data(title,description,address,lat,lng,type) {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/submit_entry.php?id=0&version=" + randomnumber + "&type=" + type + "&address=" + address + "&title=" + title + "&description=" + description + "&lat=" + lat + "&lng=" + lng;
	sendRequest (url, sub_cb, "");
	function sub_cb(req) {
		var response=JSON.decode(req.responseText);
		if(response[0] == 100) {
			msg = "Data Submitted - Thank You";
			} else {
			msg = "There was an error submitting the data, please try again";
			}
		show_msg(msg);			
		}
	the_location();
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
	];	//	10/23/12

function createXMLHTTPObject() {	//	10/23/12
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

function syncAjax(strURL) {	//	10/23/12
	if (window.XMLHttpRequest) {						 
		AJAX=new XMLHttpRequest();						 
		} 
	else {																 
		AJAX=new ActiveXObject("Microsoft.XMLHTTP");
		}
	if (AJAX) {
		AJAX.open("GET", strURL, false);														 
		AJAX.send(null);							// form name
		return AJAX.responseText;																				 
		} 
	else {
		alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
		return false;
		}																						 
	}		// end function sync Ajax()
	
function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}

function createMarker(lat, lon, info, icon, title){
	L.marker([lat, lon]).addTo(map);
	}
	
function closeIW() {
//	$('adverts').style.display = 'inline-block';
	}
	
function alert_coords() {
	alert(the_lat + ", " + the_lng);
	}

function alert_location() {
	alert(place);
	}	
	
function the_status(status, title, description) {
	if (confirm("Are you sure you want submit this " + title + " report?")) { 
		sub_data(title,description,form_add,the_lat,the_lng,status)
//		initialise();
		get_conditions();		
		slideIn();
		}
	}
	
function refresh_screen() {
	initialise();
	get_conditions();		
	slideIn();
	}

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}

function do_sb_hover (the_id) {
	CngClass(the_id, 'screen_but_hover');
	return true;
	}

function do_sb_plain (the_id) {
	CngClass(the_id, 'screen_but_plain');
	return true;
	}
	

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}
	
function $() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
	
function show_msg (msg) {
	$('msg_span').style.display = "block";
	$('msg_span').innerHTML = msg;			
	setTimeout("$('msg_span').innerHTML =''", 6000);	// show for 3 seconds
	$('msg_span').style.display = "none";	
	}

