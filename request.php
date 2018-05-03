<?php
/*
9/10/13 - request.php - file for view and edit of portal user request
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once '../incs/functions.inc.php';
do_login(basename(__FILE__));
	
$api_key = trim(get_variable('gmaps_api_key'));
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Service User Request</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<SCRIPT TYPE="application/x-javascript" SRC="../js/misc_function.js"></SCRIPT>	
	<SCRIPT TYPE="application/x-javascript" SRC="../js/domready.js"></script>
	<script src="../js/leaflet/leaflet.js"></script>
	<script src="../js/proj4js.js"></script>
	<script src="../js/proj4-compressed.js"></script>
	<script src="../js/proj4leaflet.js"></script>
	<script src="../js/leaflet/KML.js"></script>
	<script src="../js/leaflet/gpx.js"></script>  
	<script src="../js/osopenspace.js"></script>
	<script src="../js/leaflet-openweathermap.js"></script>
	<script src="../js/esri-leaflet.js"></script>
	<script src="../js/Control.Geocoder.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
<?php
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
?>
	<script type="application/x-javascript" src="../js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="../js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="../js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="../js/geotools2.js"></script>
<?php
	require_once('./incs/all_forms_js_variables.inc.php');
?>
	<SCRIPT>
	var the_link = "";
	var countmail = 0;
	var randomnumber;
	var the_string;
	var theClass = "background-color: #CECECE";
	var fac_lat = [];
	var fac_lng = [];
	var fac_street = [];
	var fac_city = [];
	var fac_state = [];
	var rec_fac_lat = [];
	var rec_fac_lng = [];
	var rec_fac_street = [];
	var rec_fac_city = [];
	var rec_fac_state = [];

	function addressLookup(address) {
		var ret_arr = [];
		control.options.geocoder.geocode(address, function(results) {
			if(!results[0]) {
				alert("Error geocoding the address");
				return;
				}
			var r = results[0]['center'];
			ret_arr[0] = r.lat;
			ret_arr[1] = r.lng;
			});
		return ret_arr;
		}				// end function loc_lkup()
		
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
		
	function do_edit() {
		$('view').style.display = 'none';
		$('edit').style.display = 'inline';
		}

	function validate(theForm) {
		var err_msg = "";
		var street = theForm.frm_street.value;
		var city = theForm.frm_city.value;
		var state = theForm.frm_state.value;
		var theDescription = theForm.frm_description.value
		var requestDate = theForm.frm_year_request_date.value + "-" + theForm.frm_month_request_date.value + "-" + theForm.frm_day_request_date.value + " 00:00:00";
		var thePhone = (theForm.frm_phone.value != "") ? theForm.frm_phone.value : "none";
		var ToAddress = theForm.frm_toaddress.value;
		var ThePickup = theForm.frm_pickup.value;
		var TheArrival = theForm.frm_arrival.value;
		var dest_address_array = ToAddress.split(",");
		if(dest_address_array[0] == "") {
			ToAddress = "";
			}
		var thePatient = theForm.frm_patient.value;
		var origFac = theForm.frm_orig_fac.value;
		var recFac = theForm.frm_rec_fac.value;	
		var theScope = theForm.frm_scope.value;
		if(thePatient == "") { err_msg += "\tName of person required\n"; }
		if(theScope == "") { err_msg += "\tRequest title required\n"; }
		if(street == "") { err_msg += "\tStreet address required\n"; }
		if(city == "") { err_msg += "\tCity is required\n"; }
		if(state == "") { err_msg += "\tState required, for UK State is UK\n"; }
		if(theDescription == "") { err_msg += "\tDescription of job required\n"; }
		if(requestDate == "") { err_msg += "\tRequest date required\n"; }
		if(err_msg != "") {
			alert ("Please correct the following and re-submit:\n\n" + err_msg);
			return;
			} else {
			var myAddress = theForm.frm_street.value.trim() + ", " + theForm.frm_city.value.trim() + ", " + theForm.frm_state.value.trim();
			var address1 = addressLookup(myAddress);
			if (address1) {
				window.theLat = address1[0];
				window.theLng = address1[1];
				}
			theForm.submit();
			}
		}
		
	function do_cancel(req_id) {
		countmail = 0;
		randomnumber=Math.floor(Math.random()*99999999);
		$('view').style.display="none";
		$('edit').style.display = 'none';	
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Cancelling request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var url ="./ajax/cancel_request.php?id=" + req_id + "&version=" + randomnumber;
		sendRequest (url, requests_cb, "");
		function requests_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 999) {
				$('waiting').style.display='none';			
				$('result').style.display = 'inline-block';
				the_link = "Request could not be cancelled, please try again.<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				} else {
				var to_str1 = the_response[1];
				var smsg_to_str1 = the_response[2];
				var subject_str1 = the_response[3];
				var text_str1 = the_response[4];
				var to_str2 = the_response[5];
				var smsg_to_str2 = the_response[6];
				var subject_str2 = the_response[7];
				var text_str2 = the_response[8];
				var to_str3 = the_response[9];
				var smsg_to_str3 = the_response[10];
				var subject_str3 = the_response[11];
				var text_str3 = the_response[12];	
				var randomnumber = Math.floor(Math.random()*99999999);	
				if((to_str1 == "") && (smsg_to_str1 == "") && (text_str1 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str1 + "&smsg_to_str=" + smsg_to_str1 + "&subject_str=" + subject_str1 + "&text_str=" + encodeURI(text_str1) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str2 == "") && (smsg_to_str2 == "") && (text_str2 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str2 + "&smsg_to_str=" + smsg_to_str2 + "&subject_str=" + subject_str2 + "&text_str=" + encodeURI(text_str2) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str3 == "") && (smsg_to_str3 == "") && (text_str3 != "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str3 + "&smsg_to_str=" + smsg_to_str3 + "&subject_str=" + subject_str3 + "&text_str=" + encodeURI(text_str3) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				the_link = "<SPAN>The Request has been cancelled and the controllers have been informed. You will receive an email confirmation.</SPAN><BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
//				the_link += "<BR /><BR />" + countmail + " messages have been sent";
				window.opener.get_requests(window.opener.req_field, window.opener.req_direct);	
				}
			}
		}	
		
	function accept(id) {
		countmail = 0;
		randomnumber=Math.floor(Math.random()*99999999);
		$('view').style.display = 'none';
		$('edit').style.display = 'none';	
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Accepting request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var url ="./ajax/insert_ticket.php?id=" + id + "&version=" + randomnumber;
		sendRequest (url, requests_cb, "");
		function requests_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 0) {
				$('waiting').style.display='none';		
				$('result').style.display = 'inline-block';
				the_link = "Could not insert new Ticket, please try again<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				} else {
				var to_str1 = the_response[1];
				var smsg_to_str1 = the_response[2];
				var subject_str1 = the_response[3];
				var text_str1 = the_response[4];
				var to_str2 = the_response[5];
				var smsg_to_str2 = the_response[6];
				var subject_str2 = the_response[7];
				var text_str2 = the_response[8];
				var to_str3 = the_response[9];
				var smsg_to_str3 = the_response[10];
				var subject_str3 = the_response[11];
				var text_str3 = the_response[12];	
				var randomnumber = Math.floor(Math.random()*99999999);	
				if((to_str1 == "") && (smsg_to_str1 == "") && (text_str1 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str1 + "&smsg_to_str=" + smsg_to_str1 + "&subject_str=" + subject_str1 + "&text_str=" + encodeURI(text_str1) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str2 == "") && (smsg_to_str2 == "") && (text_str2 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str2 + "&smsg_to_str=" + smsg_to_str2 + "&subject_str=" + subject_str2 + "&text_str=" + encodeURI(text_str2) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str3 == "") && (smsg_to_str3 == "") && (text_str3 != "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str3 + "&smsg_to_str=" + smsg_to_str3 + "&subject_str=" + subject_str3 + "&text_str=" + encodeURI(text_str3) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				the_link = "<SPAN>A New Ticket has been inserted. click the link below to view</SPAN><BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='the_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.parent.frames[\"main\"].location=\"../edit.php?id=" + the_response[0] + "\"; window.close();'>Go to Ticket</SPAN>";			
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
//				the_link += "<BR /><BR />" + countmail + " messages have been sent";
				window.opener.get_requests(window.opener.req_field, window.opener.req_direct);
				}
			}
		}

	function status_update(the_id, the_val) {									// write unit status data via ajax xfer
		$('view').style.display="none";
		$('edit').style.display = 'none';
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Updating Status<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var querystr = "the_id=" + the_id;
		querystr += "&status=" + the_val;
		var url = "up_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			$('view').style.display="inline_block";
			$('waiting').style.display='none';
			$('waiting').innerHTML = "";
			alert ("Could not update status");
			return false;
			}
		else {
			$('waiting').style.display='none';				
			$('result').style.display = 'inline-block';
			the_link = "<SPAN>Status has been updated</SPAN><BR /><BR /><BR /><BR />";		
			the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
			$('done').innerHTML = the_link;
			window.opener.get_requests(window.opener.req_field, window.opener.req_direct);
			}
		}		// end function status_update()
		
	function tentative(id) {
		countmail = 0;
		randomnumber=Math.floor(Math.random()*99999999);
		$('view').style.display="none";
		$('edit').style.display = 'none';
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Tentatively accepting request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var url ="./ajax/insert_ticket_tentative.php?id=" + id + "&version=" + randomnumber;
		sendRequest (url, requests_cb, "");
		function requests_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 0) {
				$('waiting').style.display='none';					
				$('result').style.display = 'inline-block';
				the_link = "Could not insert new Ticket, please try again<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				} else {
				var to_str1 = the_response[1];
				var smsg_to_str1 = the_response[2];
				var subject_str1 = the_response[3];
				var text_str1 = the_response[4];
				var to_str2 = the_response[5];
				var smsg_to_str2 = the_response[6];
				var subject_str2 = the_response[7];
				var text_str2 = the_response[8];
				var to_str3 = the_response[9];
				var smsg_to_str3 = the_response[10];
				var subject_str3 = the_response[11];
				var text_str3 = the_response[12];	
				var randomnumber = Math.floor(Math.random()*99999999);	
				if((to_str1 == "") && (smsg_to_str1 == "") && (text_str1 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str1 + "&smsg_to_str=" + smsg_to_str1 + "&subject_str=" + subject_str1 + "&text_str=" + encodeURI(text_str1) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str2 == "") && (smsg_to_str2 == "") && (text_str2 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str2 + "&smsg_to_str=" + smsg_to_str2 + "&subject_str=" + subject_str2 + "&text_str=" + encodeURI(text_str2) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str3 == "") && (smsg_to_str3 == "") && (text_str3 != "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str3 + "&smsg_to_str=" + smsg_to_str3 + "&subject_str=" + subject_str3 + "&text_str=" + encodeURI(text_str3) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				the_link = "<SPAN>A New Ticket has been inserted. click the link below to view</SPAN><BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='the_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.parent.frames[\"main\"].location=\"../edit.php?id=" + the_response[0] + "\"; window.close();'>Go to Ticket</SPAN>";			
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
//				the_link += "<BR /><BR />" + countmail + " messages have been sent";
				window.opener.get_requests(window.opener.req_field, window.opener.req_direct);
				}
			}
		}
	
	function decline(id) {
		countmail = 0;
		var theReason = prompt("Please enter a reason you are declining this request", "Type reason here");
		randomnumber=Math.floor(Math.random()*99999999);
		$('view').style.display="none";
		$('edit').style.display = 'none';	
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Declining request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var url ="./ajax/decline.php?id=" + id + "&reason=" + theReason + "&version=" + randomnumber;
		sendRequest (url, decline_cb, "");
		function decline_cb(req) {
			var the_response=JSON.decode(req.responseText);
			if(the_response[0] == 200) {
				$('waiting').style.display='none';				
				$('result').style.display = 'inline-block';
				the_link = "There was an error, please try again<BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
				$('done').innerHTML = the_link;
				} else {
				var to_str1 = the_response[1];
				var smsg_to_str1 = the_response[2];
				var subject_str1 = the_response[3];
				var text_str1 = the_response[4];
				var to_str2 = the_response[5];
				var smsg_to_str2 = the_response[6];
				var subject_str2 = the_response[7];
				var text_str2 = the_response[8];
				var to_str3 = the_response[9];
				var smsg_to_str3 = the_response[10];
				var subject_str3 = the_response[11];
				var text_str3 = the_response[12];	
				var randomnumber = Math.floor(Math.random()*99999999);	
				if((to_str1 == "") && (smsg_to_str1 == "") && (text_str1 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str1 + "&smsg_to_str=" + smsg_to_str1 + "&subject_str=" + subject_str1 + "&text_str=" + encodeURI(text_str1) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str2 == "") && (smsg_to_str2 == "") && (text_str2 == "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str2 + "&smsg_to_str=" + smsg_to_str2 + "&subject_str=" + subject_str2 + "&text_str=" + encodeURI(text_str2) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				if((to_str3 == "") && (smsg_to_str3 == "") && (text_str3 != "")) {
					} else {
					var url ="../do_send_mail.php?to_str=" + to_str3 + "&smsg_to_str=" + smsg_to_str3 + "&subject_str=" + subject_str3 + "&text_str=" + encodeURI(text_str3) + "&version=" + randomnumber;
					sendRequest (url,mail_handleResult, "");
					}
				the_link = "<SPAN>The request has been declined</SPAN><BR /><BR /><BR /><BR />";		
				the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
//				the_link += "<BR /><BR />" + countmail + " messages have been sent";
				window.opener.get_requests(window.opener.req_field, window.opener.req_direct);
				}
			}
		}

	function do_userUpdatedMail(theto,thesmsg,subj,text) {
		countmail = 0;
		var to_str = theto;
		var smsg_to_str = thesmsg;
		var subject_str = subj;
		var text_str = text;
		var randomnumber = Math.floor(Math.random()*99999999);	
		if((to_str == "") && (smsg_to_str == "") && (text_str == "")) {
			} else {
			var url ="../do_send_mail.php?to_str=" + to_str + "&smsg_to_str=" + smsg_to_str + "&subject_str=" + subject_str + "&text_str=" + encodeURI(text_str) + "&version=" + randomnumber;
			sendRequest (url,mail_handleResult, "");
			}
		}

	function mail_handleResult(req) {
		var the_response=JSON.decode(req.responseText);
		if(the_response && parseInt(the_response[0]) > 0) {
			countmail++;
			}
		if($('waiting')) {$('waiting').style.display='none';}
		if($('finish_but')) { $('finish_but').style.display = "inline";}
		$('result').style.display = 'inline-block';
		$('done').innerHTML = the_link;
		}

	function startup() {
		$('edit').style.display = 'none';
		$('result').style.display = 'none';
		$('view').style.display = 'inline';	
		}

	function do_lat (lat) {
		document.edit_frm.frm_lat.value=lat;			// 9/9/08
		}
	function do_lng (lng) {
		document.edit_frm.frm_lng.value=lng;
		}

	function do_fac_to_loc(text, index){			// 9/22/09
		var theFaclat = fac_lat[index];
		var theFaclng = fac_lng[index];
		var theFacstreet = fac_street[index];
		var theFaccity = fac_city[index];
		var theFacstate = fac_state[index];
		do_lat(theFaclat);
		do_lng(theFaclng);
		document.edit_frm.frm_street.value = theFacstreet
		document.edit_frm.frm_city.value = theFaccity;
		document.edit_frm.frm_state.value = theFacstate;	
		}					// end function do_fac_to_loc
		
	function do_rec_fac_to_loc(text, index){			// 9/22/09
		var recFaclat = rec_fac_lat[index];
		var recFaclng = rec_fac_lng[index];
		var recFacstreet = rec_fac_street[index];
		var recFaccity = rec_fac_city[index];
		var recFacstate = rec_fac_state[index];
		do_lat(recFaclat);
		do_lng(recFaclng);
		document.edit_frm.frm_toaddress = recFacstreet + ", " + recFaccity + ", " + recFacstate;
		}					// end function do_fac_to_loc		

	</SCRIPT>
	</HEAD>
<?php
$requester = get_owner($_SESSION['user_id']);

function get_contact_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row2 = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[] = (($row2['name_f'] != "") && ($row2['name_l'] != "")) ? $row2['name_f'] . " " . $row2['name_l'] : $row2['user'];
		$the_ret[] = ($row2['email'] != "") ? $row2['email'] : "Not Set";
		}
	return $the_ret;
	}
	
function get_requester_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row3 = stripslashes_deep(mysql_fetch_assoc($result));
		if($row3['email'] == "") {
			if($row3['email_s'] == "") {
				$the_ret[0] = "";
				} else {
				$the_ret[0] = $row3['email_s'];
				}
			} else {
				$the_ret[0] = $row3['email'];
			}
		} else {
		$the_ret[0] = "";
		}
	return $the_ret;
	}

function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row4 = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row4['name_f'] != "") && ($row4['name_l'] != "")) ? $the_ret[] = $row4['name_f'] . " " . $row4['name_l'] : $the_ret[] = $row4['user'];
		}
	return $the_ret;
	}

function get_thefacilityname($value) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";		 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row5 = stripslashes_deep(mysql_fetch_assoc($result));
		return $row5['name'];
		} else {
		return "";
		}
	}
	
function get_facilitydetails($value) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";		 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row6 = stripslashes_deep(mysql_fetch_assoc($result));
		$return = array();
		$return['street'] = $row6['street'];
		$return['city'] = $row6['city'];
		$return['state'] = $row6['state'];
		} else {
		$return['street'] = "";
		$return['city'] = "";
		$return['state'] = "";		
		}
	return $return;
	}
	
if((!empty($_POST)) && (empty($_GET))) {
	$theDetails = get_requester_details($_SESSION['user_id']);
	$userName = $_POST['frm_username'];
	$userEmail = $theDetails[0];
	$appEmail = ($_POST['frm_app_email'] != "") ? $_POST['frm_app_email'] : NULL;
	$the_email = (($appEmail != NULL) && (is_email($appEmail))) ? $appEmail : $theDetails[0];
	$meridiem_request_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_request_date'])))) ) ? "" : $_POST['frm_meridiem_request_date'] ;
	$request_date = "$_POST[frm_year_request_date]-$_POST[frm_month_request_date]-$_POST[frm_day_request_date] 00:00:00$meridiem_request_date";	
	$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET 
		`email` = " . quote_smart(trim($appEmail)) . ",
		`street` = " . quote_smart(trim($_POST['frm_street'])) . ",
		`city` = " . quote_smart(trim($_POST['frm_city'])) . ",
		`postcode` = " . quote_smart(trim($_POST['frm_postcode'])) . ",
		`state` = " . quote_smart(trim($_POST['frm_state'])) . ",
		`the_name` = " . quote_smart(trim($_POST['frm_patient'])) . ",
		`phone` = " . quote_smart(trim($_POST['frm_phone'])) . ",
		`to_address` = " . quote_smart(trim($_POST['frm_toaddress'])) . ",
		`pickup` = " . quote_smart(trim($_POST['frm_pickup'])) . ",
		`arrival` = " . quote_smart(trim($_POST['frm_arrival'])) . ",
		`orig_facility` = " . quote_smart(trim($_POST['frm_orig_fac'])) . ",		
		`rec_facility` = " . quote_smart(trim($_POST['frm_rec_fac'])) . ",
		`scope` = " . quote_smart(trim($_POST['frm_scope'])) . ",
		`description` = " . quote_smart(trim($_POST['frm_description'])) . ",		
		`request_date` = " . quote_smart(trim($request_date)) . ",	
		`status` = " . quote_smart(trim($_POST['frm_status'])) . "
		WHERE `id` = " . $_POST['id'];
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
	$the_ticket = (($_POST['frm_ticket_id'] != NULL) AND ($_POST['frm_ticket_id'] != "0") AND ($_POST['frm_ticket_id'] != "")) ? strip_tags($_POST['frm_ticket_id']) : "0";
	if($the_ticket != "0") {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . intval($the_ticket) . " LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		if($result) {
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$the_scope = $_POST['frm_scope'];
			$new_scope = "EDITED " . $the_scope;
			$output1 = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET ";
			$output2 = "";
			$output2 .= "`scope` = " . quote_smart(trim($new_scope)) . ",";
			$output2 .= ($_POST['frm_street'] == $row['street']) ? "": "`street` = " . quote_smart(trim($_POST['frm_street'])) . ",";
			$output2 .= ($_POST['frm_city'] == $row['city']) ? "": "`city` = " . quote_smart(trim($_POST['frm_city'])) . ",";
			$output2 .= ($_POST['frm_state'] == $row['state']) ? "": "`state` = " . quote_smart(trim($_POST['frm_state'])) . ",";
			$output2 .= ($_POST['frm_patient'] == $row['contact']) ? "": "`contact` = " . quote_smart(trim($_POST['frm_patient'])) . ",";
			$output2 .= ($_POST['frm_phone'] == $row['phone']) ? "": "`phone` = " . quote_smart(trim($_POST['frm_phone'])) . ",";
			$output2 .= ($_POST['frm_toaddress'] == $row['to_address']) ? "": "`to_address` = " . quote_smart(trim($_POST['frm_toaddress'])) . ",";
			$output2 .= ($_POST['frm_orig_fac'] == $row['facility']) ? "": "`facility` = " . quote_smart(trim($_POST['frm_orig_fac'])) . ",";	
			$output2 .= ($_POST['frm_rec_fac'] == $row['rec_facility']) ? "": "`rec_facility` = " . quote_smart(trim($_POST['frm_rec_fac'])) . ",";	
			$output2 .= ($_POST['frm_description'] == $row['description']) ? "": "`description` = " . quote_smart(trim($_POST['frm_description'])) . ",";	
			$output3 = " WHERE `id` = " . $the_ticket;
			if($output2 != "") {
				$output2 = substr($output2, 0, -1);
				$output = $output1 . $output2 . $output3;
				$result = mysql_query($output) or do_error($output, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				}
			}
		}
	do_log($GLOBALS['LOG_EDIT_REQUEST'], $_POST['id']);	
	$the_summary = "Request from " . get_user_name($_POST['requester']) . "has been edited\\r";
	$the_summary = get_text('Scope') . ": " . $_POST['frm_scope'] . "\\r";	
	$the_summary .= get_text('Patient') . " name: " . $_POST['frm_patient'] . "\\r";
	$the_summary .= get_text('Street') . ": " . $_POST['frm_street'] . " ";	
	$the_summary .= get_text('City') . ": " . $_POST['frm_city'] . " ";	
	$the_summary .= get_text('Postcode') . ": " . $_POST['frm_postcode'] . " ";	
	$the_summary .= get_text('State') . ": " . $_POST['frm_state'] . "\\r";	
	$the_summary .= get_text('Contact Phone') . ": " . $_POST['frm_phone'] . "\\r";
	$the_summary .= get_text('To Address') . ": " . $_POST['frm_toaddress'] . "\\r";
	$the_summary .= get_text('Pickup Time') . ": " . $_POST['frm_pickup'] . "\\r";
	$the_summary .= get_text('Arrival Time') . ": " . $_POST['frm_arrival'] . "\\r";
	$orig_Fac = (intval($_POST['frm_orig_fac']) != 0) ? get_thefacilityname(intval($_POST['frm_orig_fac'])) : "";
	$rec_Fac =  (intval($_POST['frm_rec_fac']) != 0) ? get_thefacilityname(intval($_POST['frm_rec_fac'])) : "";
	$the_summary .= ((is_array($orig_Fac)) && ($orig_Fac[0] != "")) ? "Originating Facility " . $orig_Fac[0] . "\\rAddress: " . $orig_Fac[1] . "\\rPhone " . $orig_Fac[2] . "\\r" : "";
	$the_summary .= ((is_array($rec_Fac)) && ($rec_Fac[0] != "")) ? "Receiving Facility " . $rec_Fac[0] . "\\rAddress: " . $rec_Fac[1] . "\\rPhone " . $rec_Fac[2] . "\\r" : "";
	$searchArray = array("\r\n", "\n", "\r");
	$theDescription = str_replace($searchArray, "\\r", $_POST['frm_description']);
	$the_summary .= get_text('Description') . "\\r" . $theDescription . "\\r";	
	$the_summary .= get_text('Request Date') . ": " . format_date_2(strtotime($request_date)) . "\\r";		
	$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
	$to_str1 = "";
	$smsg_to_str1 = "";
	$subject_str1 = "";
	$text_str1 = "";	
	$to_str2 = "";
	$smsg_to_str2 = "";
	$subject_str2 = "";
	$text_str2 = "";	
	$to_str3 = "";
	$smsg_to_str3 = "";
	$subject_str3 = "";
	$text_str3 = "";	
	$theEmailCount = 0;	
	if ($addrs) {				// any addresses?
		$to_str1 = implode("|", $addrs);
		$smsg_to_str1 = "";
		$subject_str1 = get_text('Service User') . " Request has been edited\\r";
		$text_str1 = "A request " . get_text('Service User') . " has been edited by " . $userName . " Dated " . $now . "Please log on to Tickets and check\\r"; 
		$text_str1 .= "Request Summary\\r" . $the_summary;
		$text_str1 = addslashes($text_str1);
		$theEmailCount++;
//		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);
		}				// end if/else ($addrs)	
	if ($the_email != "") {				// any addresses?
		$to_str2 = $the_email;
		$smsg_to_str2 = "";
		$subject_str2 = "Your request " . $_POST['frm_scope'] . " has been changed\\r";
		$text_str2 = "Your Request " . $_POST['frm_scope'] . " has been changed\\r"; 
		$text_str2 .= "Request Summary\\r" . $the_summary;
		$text_str2 = addslashes($text_str2);
		$theEmailCount++;
//		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
		}				// end if/else ($the_email)	
	if ($userEmail != "") {				// any addresses?
		$to_str3 = $userEmail;
		$smsg_to_str3 = "";
		$subject_str3 = "Your request " . $scope . " has been registered\\r";
		$text_str3 = "Your Request " . $scope . " has been registered\\r"; 
		$text_str3 .= "Request Summary\\r" . $the_summary;
		$text_str3 = addslashes($text_str3);
		$theEmailCount++;
//		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
		}				// end if/else ($userEmail)	
?>
	<BODY onLoad='do_userUpdatedMail("<?php print $to_str1;?>", "<?php print $smsg_to_str1;?>", "<?php print $subject_str1;?>", "<?php print $text_str1;?>"); do_userUpdatedMail("<?php print $to_str2;?>", "<?php print $smsg_to_str2;?>", "<?php print $subject_str2;?>", "<?php print $text_str2;?>"); do_userUpdatedMail("<?php print $to_str3;?>", "<?php print $smsg_to_str3;?>", "<?php print $subject_str3;?>", "<?php print $text_str3;?>");'>
		<CENTER>
		<DIV id='confirmation'>
			<BR /><BR /><BR />
			<DIV style="background-color: green; color: black; font-size: 1.5em;">Request Updated</DIV>
			<BR /><BR />
			<?php print $theEmailCount;?> email(s) have been sent<BR /><BR />
			<SPAN id='finish_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.opener.get_requests(window.opener.req_field, window.opener.req_direct); window.close();">Finish</SPAN>		
		</DIV>
		</CENTER>
<?php
	} elseif((!empty($_GET)) && (empty($_POST))) {
	$id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
	$only_view = ((isset($_GET['func'])) && ($_GET['func'] == "view")) ? TRUE : FALSE;
	$can_edit = ((is_service_user()) && (!$only_view)) ? TRUE : FALSE;
	$query = "SELECT *, 
			`r`.`id` AS `request_id`,
			`r`.`pickup` AS `pickup`,
			`r`.`arrival` AS `arrival`,
			`r`.`ticket_id` AS `ticket_id`,
			`r`.`email` AS `email`,
			`r`.`orig_facility` AS `orig_facility`,
			`r`.`rec_facility` AS `rec_facility`,			
			`a`.`ticket_id` AS `a_ticket_id`,
			`a`.`id` AS `assigns_id`,
			`a`.`start_miles` AS `start_miles`,
			`a`.`end_miles` AS `end_miles`,
			`a`.`comments` AS `assigns_comments`,
			`request_date` AS `request_date`,
			`tentative_date` AS `tentative_date`,		
			`accepted_date` AS `accepted_date`,
			`declined_date` AS `declined_date`,		
			`resourced_date` AS `resourced_date`,
			`completed_date` AS `completed_date`,	
			`closed` AS `closed`,
			`_on` AS `_on`,
			`a`.`dispatched` AS `dispatched`,
			`a`.`clear` AS `clear`		
			FROM `$GLOBALS[mysql_prefix]requests` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id` 			
			WHERE `r`.`id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$tentative_date = $row['tentative_date'];			
	$accepted_date = $row['accepted_date'];	
	$declined_date = $row['declined_date'];	
	$resourced_date = (($row['dispatched'] != "") || ($row['dispatched'] != NULL)) ? $row['dispatched'] : $row['resourced_date'];
	if(($row['dispatched'] != "") && ($row['dispatched'] != NULL) && ($row['resourced_date'] == NULL)) {
		$query2 = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `resourced_date` = '" . mysql_format_date($row['dispatched']) . " WHERE `id` = " . $id;
		$result2 = mysql_query($query2) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
		}
	$completed_date = (($row['clear'] != "") || ($row['clear'] != NULL)) ? $row['clear'] : $row['completed_date'];
	if(($row['clear'] != "") && ($row['clear'] != NULL) && ($row['completed_date'] == NULL)) {
		$query3 = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `completed_date` = '" . mysql_format_date($row['clear']) . " WHERE `id` = " . $id;
		$result3 = mysql_query($query3) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		
		}		
	$closed_date = $row['closed'];			
	$updated_by = get_owner($row['_by']);
	$orig_fac_details = get_facilitydetails($row['orig_facility']);
	$rec_fac_details = get_facilitydetails($row['rec_facility']);
	$status_array = array('Open', 'Tentative', 'Accepted', 'Resourced', 'Complete');
	$status_sel = "<SELECT NAME='frm_status'>";
	foreach($status_array AS $val) {
		$sel = ($val == $row['status']) ? "SELECTED": "";
		$status_sel .= "<OPTION VALUE='" . $val . "' " . $sel . ">" . $val . "</OPTION>";
		}
	$status_sel .= "</SELECT>";

	$rec_facility = ($row['rec_facility'] != 0) ? get_thefacilityname($row['rec_facility']) : "Not Set";
	$orig_facility = ($row['orig_facility'] != 0) ? get_thefacilityname($row['orig_facility']) : "Not Set";	
	$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
	$now = time() - (intval(get_variable('delta_mins')*60));
	$the_details = get_contact_details($row['requester']);	#
	$contact_email_p = $the_details[1];
?>
<SCRIPT>
<?php	
	$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
	$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$rec_fac_menu = "<SELECT NAME='frm_rec_fac' onChange='do_rec_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>";
	$rec_fac_menu .= "<OPTION VALUE=0 selected>Receiving Facility</OPTION>";
	while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
			$sel = ($row_fc['id'] == $row['rec_facility']) ? "SELECTED" : "";
			$rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . " " . $sel . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
			$rf_street = ($row_fc['street'] != "") ? $row_fc['street'] : "Empty";
			$rf_city = ($row_fc['city'] != "") ? $row_fc['city'] : "Empty";
			$rf_state = ($row_fc['state'] != "") ? $row_fc['state'] : "Empty";
			print "\trec_fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;\n";
			print "\trec_fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;\n";	
			print "\trec_fac_street[" . $row_fc['id'] . "] = '" . $rf_street . "' ;\n";	
			print "\trec_fac_city[" . $row_fc['id'] . "] = '" . $rf_city . "' ;\n";
			print "\trec_fac_state[" . $row_fc['id'] . "] = '" . $rf_state . "' ;\n";	
			}
	$rec_fac_menu .= "<SELECT>";

	$query_fc2 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
	$result_fc2 = mysql_query($query_fc2) or do_error($query_fc2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$orig_fac_menu = "<SELECT NAME='frm_orig_fac' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>";
	$orig_fac_menu .= "<OPTION VALUE=0 selected>Originating Facility</OPTION>";
	while ($row_fc2 = mysql_fetch_array($result_fc2, MYSQL_ASSOC)) {
			$sel = ($row_fc2['id'] == $row['orig_facility']) ? "SELECTED" : "";
			$orig_fac_menu .= "<OPTION VALUE=" . $row_fc2['id'] . " " . $sel . ">" . shorten($row_fc2['name'], 30) . "</OPTION>";
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
	<BODY onLoad="startup(); location.href = '#top';">

	<DIV id='view' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='banner' class='heading' style='font-size: 20px; position: relative; top: 5%; width: 100%; border: 1px outset #000000;'>Tickets Service User Request</DIV><BR /><BR />
		<DIV id='leftcol' style='position: fixed; left: 2%; top: 8%; width: 96%; height: 90%;'>
			<DIV id='left_scroller' style='position: relative; top: 0px; left: 0px; height: 90%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
				<TABLE style='width: 100%;'>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Requested By</TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($row['requester']);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'>Request Date and Time</TD><TD class='td_data' style='text-align: left;'><?php print format_dateonly(strtotime($row['request_date']));?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Status');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['status'];?></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Patient');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['the_name'];?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['street'];?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['city'];?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Postcode');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['postcode'];?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['state'];?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Destination Address');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['to_address'];?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Pickup Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['pickup'];?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Arrival Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['arrival'];?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['phone'];?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Email');?></TD><TD class='td_data' style='text-align: left;'><?php print $contact_email_p;?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Originating Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_facility;?></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_facility;?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['scope'];?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><?php print nl2br($row['description']);?></TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>	
					<TR class='heading'>	
						<TD COLSPAN='2' class='heading' style='text-align: left;'>Status Times and Dates</TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Tentative Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $tentative_date;?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Accepted Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $accepted_date;?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Declined Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $declined_date;?></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Resourced Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $resourced_date;?></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Completed Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $completed_date;?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Closed Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $closed_date;?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Updated by');?></TD><TD class='td_data' style='text-align: left;'><?php print $updated_by;?></TD>
					</TR>	
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>	
				</TABLE>
			</DIV><BR /><BR />
<?php
	if($can_edit) {
?>
			<SPAN id='edit_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_edit();">Edit</SPAN>
<?php
	}
	if(($can_edit) && ($row['cancelled'] == "")) {	//	12/3/13
?>
			<SPAN id='req_can_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_cancel(<?php print $row['request_id'];?>);">Cancel Request</SPAN>			
<?php
	}
	if((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Declined'))) {
?>
			<SPAN id='tent_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "tentative(<?php print $id;?>);">Tentatively Accept and open Ticket</SPAN>
<?php
	}
	if((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Declined'))) {
?>
			<SPAN id='accept_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "accept(<?php print $id;?>);">Accept and open Ticket</SPAN>
<?php
	}
	if((!is_service_user()) && ($row['status'] == 'Tentative')) {
?>
			<SPAN id='accept_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "status_update(<?php print $id;?>, 'Accepted');">Accept</SPAN>
<?php
	}
	if((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Tentative'))) {
?>	
			<SPAN id='decline_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "decline(<?php print $id;?>);">Decline</SPAN>			
<?php
	}
?>
			<SPAN id='close_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.opener.get_requests(window.opener.req_field, window.opener.req_direct); window.close();">Close</SPAN><BR /><BR />		
		</DIV>
	</DIV>
	<DIV id='edit' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='edit_banner' class='heading' style='font-size: 20px; position: relative; top: 5%; width: 100%; border: 1px outset #000000;'>Edit Tickets Service User Request</DIV><BR /><BR />
		<DIV id='edit_leftcol' style='position: fixed; left: 2%; top: 8%; width: 96%; height: 90%;'>
			<DIV id='edit_left_scroller' style='position: relative; top: 0px; left: 0px; height: 90%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
				<FORM NAME='edit_frm' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
				<TABLE style='width: 100%;'>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Requested By</TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($row['requester']);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'>Request Date and Time</TD><TD class='td_data' style='text-align: left;'><?php print generate_dateonly_dropdown('request_date',strtotime($row['request_date']),FALSE);?></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Status');?></TD><TD class='td_data' style='text-align: left;'><?php print $status_sel;?></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Patient');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['the_name'];?>"></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_street' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['street'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_city' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['city'];?>"></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Postcode');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_postcode' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['postcode'];?>"></TD>
					</TR>						
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print $row['state'];?>"></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Destination Address');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_toaddress' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['to_address'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Pickup Time');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_pickup' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['pickup'];?>"></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Arrival Time');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_arrival' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['arrival'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_phone' TYPE='TEXT' SIZE='16' MAXLENGTH='16' VALUE="<?php print $row['phone'];?>"></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Originating Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_fac_menu;?></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_fac_menu;?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_scope' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['scope'];?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_description" COLS="45" ROWS="10" WRAP="virtual"><?php print $row['description'];?></TEXTAREA></TD>
					</TR>		
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>	
				</TABLE>
				<INPUT NAME='requester' TYPE='hidden' SIZE='24' VALUE="<?php print $_SESSION['user_id'];?>">
				<INPUT NAME='id' TYPE='hidden' SIZE='24' VALUE="<?php print $id;?>">
				<INPUT NAME='frm_lat' TYPE='hidden' SIZE='10' VALUE="<?php print $row['lat'];?>">
				<INPUT NAME='frm_lng' TYPE='hidden' SIZE='10' VALUE="<?php print $row['lng'];?>">
				<INPUT NAME='frm_ticket_id' TYPE='hidden' SIZE='8' VALUE="<?php print $row['ticket_id'];?>">
				<INPUT NAME='frm_app_email' TYPE='hidden' SIZE='128' VALUE="<?php print $row['email'];?>">
				<INPUT NAME='frm_username' TYPE='hidden' SIZE='128' VALUE="<?php print get_user_name($row['requester']);?>">
			</DIV><BR /><BR />
			<SPAN id='sub_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "validate(document.edit_frm);">Update</SPAN>
			<SPAN id='close_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.opener.get_requests(window.opener.req_field, window.opener.req_direct); window.close();">Cancel</SPAN><BR /><BR />	
			</FORM>		
		</DIV>
	</DIV>		
	<DIV id='waiting' style='display: none; text-align: center;'></DIV>
	<DIV id='result' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='done'></DIV>
	</DIV>
	<DIV id='map_canvas' style='display: none;'></DIV>
	<SCRIPT>
	var map;				// make globally visible
	var mapWidth = <?php print get_variable('map_width');?>;
	var mapHeight = <?php print get_variable('map_height');?>;;
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	var initZoom = <?php print get_variable('def_zoom');?>;
	init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	</SCRIPT>
<?php
	} else {
?>
	<BODY>
	<DIV id='confirmation2'>
		Called Incorrectly
	</DIV>
<?php
	}
?>
</BODY>
</HTML>