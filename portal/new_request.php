<?php
/*
9/10/13 - New file, New request form for Portal user
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
$logged_in = $logged_out = false;
if (empty($_SESSION)) {
	$logged_out = true;
	header("Location: ../index.php");
	} else {
	$logged_in = true;
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
	
function get_city($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = $row['addr_city'];
		} else {
		$the_ret = get_variable('def_city');
		}
	return $the_ret;
	}
	

$now = time() - (intval(get_variable('delta_mins')*60));
if ($_SESSION['internet']) {				// 8/22/10
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
	} else {
	$api_key = "";
	$key_str = "";	
	}
	
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Service User Portal</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="./css/stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<style type="text/css">
body {overflow:hidden}
</style>
<SCRIPT SRC="../js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="../js/domready.js"></script>
<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>libraries=geometry,weather&sensor=false"></SCRIPT>
<SCRIPT>
var randomnumber;
var the_string;
var theClass = "background-color: #CECECE";
var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
var request_lat;
var request_lng;
var the_color;
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
var theLat;
var theLng;
var showall = "yes";
var ct = 1;
var countmail = 0;
var the_link = "";

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
	
String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};
		
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
	
function new_line() {
	var theNumber = ct.toString();
	var defSt = "<?php print get_variable('def_st');?>";
	var defCity = "<?php print get_city($_SESSION['user_id']);?>";
	var div1 = document.createElement('div');
	div1.id = "extra_address" + ct;
	var the_text = "<DIV style='font-size: 1em;'>";
	the_text +=	"<TABLE style='width: 100%;'>";
	the_text += "<TR class='odd'>";	
	the_text += '<TD class="inside_td_label" COLSPAN=99><SPAN class="inside_td_label" style="float: left; display: inline; vertical-align: middle;">Additional Address Number ' + theNumber + '</SPAN><SPAN id="a_line' + ct + '" class="plain" style="display: inline; vertical-align: middle; float: right;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="delIt(\'extra_address' + ct + '\')">Delete</SPAN></TD>';
	the_text +=	"</TR>";
	the_text +=	"<TR class='even'>";	
	the_text +=	"<TD class='inside_td_label' style='text-align: left;' TITLE='<?php print get_text('Patient');?> name'><?php print get_text('Patient');?></TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_patient_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=''></TD>";
	the_text += "</TR>";
	the_text += "<TR class='odd'>";
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='<?php print get_text('Patient');?> ID'><?php print get_text('Patient');?> ID</TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_patient_id_extra[]' TYPE='TEXT' SIZE='12' MAXLENGTH='12' VALUE=''></TD>";
	the_text += "</TR>";
	the_text +=	"<TR class='even'>";	
	the_text +=	"<TD class='inside_td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?></TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_street_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=''></TD>";
	the_text += "</TR>";
	the_text += "<TR class='odd'>";	
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='City'><?php print get_text('City');?></TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_city_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE='" + defCity + "'></TD>";
	the_text += "</TR>";
	the_text += "<TR class='even'>";	
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?></TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_postcode_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=''></TD>";
	the_text += "</TR>";		
	the_text += "<TR class='odd'>";
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?></TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_state_extra[]' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE='" + defSt + "'></TD>";
	the_text += "</TR>";
	the_text +=	"<TR class='spacer'>";
	the_text +=	"<TD class='spacer' COLSPAN=99></TD>";
	the_text +=	"</TR>";
	the_text +=	"</TABLE>";
	the_text +=	"</DIV>";
	div1.innerHTML = the_text;
	document.getElementById('formline').appendChild(div1);
	ct++;
	}

function delIt(eleId) {	// function to delete the newly added set of elements
	d = document;
	var ele = d.getElementById(eleId);
	var parentEle = d.getElementById('formline');
	parentEle.removeChild(ele);
	ct--;
	}
	
function sub_request() {
	var theForm = document.forms['add'];
	var theAddAddress = "";
	var theDescVal = "\r\n" + theForm.frm_description.value + "\r\n";
	var theField = document.getElementsByName("frm_to_street_extra[]");
	var theField2 = document.getElementsByName("frm_to_city_extra[]");
	var theField3 = document.getElementsByName("frm_to_state_extra[]");
	var theField4 = document.getElementsByName("frm_patient_extra[]");
	var theField5 = document.getElementsByName("frm_patient_id_extra[]");
	if(theField.length != 0) {
		theAddAddress += "Additional Addresses and Passengers:\r\n";
		for (var i = 0; i < theField.length; i++ ){
			theAddAddress += theField4[i].value + ", " + theField5[i].value + "\r\n";
			theAddAddress += theField[i].value + ", " + theField2[i].value + ", " + theField3[i].value + "\r\n";
			theAddAddress += "-----------------------\r\n";			
			}
		theAddAddress += "\r\n";
		}
	var err_msg = "";
	var appEmail = theForm.frm_app_email.value;
	var street = theForm.frm_street.value;
	var city = theForm.frm_city.value;
	var postcode = theForm.frm_postcode.value;
	var state = theForm.frm_state.value;
	var theApprover = theForm.frm_approver.value;
	var theAppContact = theForm.frm_app_contact.value;
	var theCompany = theForm.frm_company.value;
	var theManager = theForm.frm_contact.value;
	var theManagerPhone = theForm.frm_contactno.value;	
	var thePickup = theForm.frm_pickup.value;
	var theArrival = theForm.frm_arrival.value;	
	var theAuthDet = "";
 	if(theApprover != "") {
		theAuthDet += "Approver: ";	
		theAuthDet += theApprover;
		theAuthDet += "\r\n";
		}
	if(theAppContact != "") {
		theAuthDet += "Approver Contact Phone: ";	
		theAuthDet += theAppContact;
		theAuthDet += "\r\n";
		}
	if(theCompany != "") {
		theAuthDet += "Company: ";	
		theAuthDet += theCompany;
		theAuthDet += "\r\n";
		}
	if(theManager != "") {
		theAuthDet += "Manager: ";	
		theAuthDet += theManager;
		theAuthDet += "\r\n";
		}
	if(theManagerPhone != "") {
		theAuthDet += "Contact: ";	
		theAuthDet += theManagerPhone;
		theAuthDet += "\r\n";
		}
	var theTimeDet = "";
	if(thePickup != "") {
		theTimeDet += "Pickup Time: ";		
		theTimeDet += thePickup;
		theTimeDet += "\r\n";
		}
	if(theArrival != "") {
		theTimeDet += "Arrival Time: ";	
		theTimeDet += theArrival;
		theTimeDet += "\r\n";
		}
	if(theAuthDet != "") {
		theAuthDet = "Approver Details\r\n" + theAuthDet + "\r\n";
		}
	if(theTimeDet != "") {
		theTimeDet = "Journey Time Details\r\n" + theTimeDet + "\r\n";
		}
	if(theForm.frm_patient_id.value != "") {
		var thePatientID = "<?php print get_text('Patient');?> ID: " + theForm.frm_patient_id.value + "\r\n";
		} else {
		var thePatientID = "";	
		}
	var thePatient = theForm.frm_patient.value;
	var thePhone = (theForm.frm_phone.value != "") ? theForm.frm_phone.value : "Not Given";
	var patDetails = "Passenger Name: " + thePatient + "\r\nPassenger Contact: " + thePhone + "\r\n\r\n";
	var theDescription = patDetails + thePatientID + theTimeDet + theAddAddress + theDescVal + theAuthDet;
	var requestDate = theForm.frm_year_request_date.value + "-" + theForm.frm_month_request_date.value + "-" + theForm.frm_day_request_date.value;
	var ToAddress = encodeURI(theForm.frm_to_street.value + ", " + theForm.frm_to_city.value + ", " + theForm.frm_to_state.value);
	var dest_address_array = ToAddress.split(",");
	if(dest_address_array[0] == "") {
		ToAddress = "";
		}
	var theUserName = "<?php print addslashes(get_user_name($_SESSION['user_id']));?>";
	var thePickup = theForm.frm_pickup.value;
	var theArrival = theForm.frm_arrival.value;
	var origFac = theForm.frm_orig_fac.value;
	var recFac = theForm.frm_rec_fac.value;	
	var theScope = theForm.frm_patient.value + " " + requestDate;
	var theComments = "";
/*	if(theApprover == "") { err_msg += "\tYour name as Approver required\n"; }
	if(appEmail == "") { err_msg += "\tYour email address is required for updates\n"; }
	if(theAppContact == "") { err_msg += "\tYour contact phone number required\n"; }	*/
//	thePhone = theAppContact;
	if(thePatient == "") { err_msg += "\tName of Person required\n"; }
/*	if(thePhone == "") { err_msg += "\tContact number of Person required\n"; }
	if(theCompany == "") { err_msg += "\tName of Company required\n"; }
	if(theManager == "") { err_msg += "\tName of Company Manager required\n"; }	*/
	if(street == "") { err_msg += "\t<?php print get_text('Street Address');?> required\n"; }
	if(city == "") { err_msg += "\t<?php print get_text('City');?> is required\n"; }
	if(state == "") { err_msg += "\t<?php print get_text('State');?> is required, for UK State is UK\n"; }
	if(theForm.frm_description.value == "") { err_msg += "\t<?php print get_text('Description');?> is required\n"; }
	if(requestDate == "") { err_msg += "\tRequest date required\n"; }
	if((thePickup == "") && (theArrival == "")) { err_msg += "\tEither a Pickup or Arrival Time is required\n"; }
	if(err_msg != "") {
		alert ("Please correct the following and re-submit:\n\n" + err_msg);
		return;
		} else {
		$('the_form').style.display="none";
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Inserting Request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var geocoder = new google.maps.Geocoder();
		var myAddress = theForm.frm_street.value.trim() + ", " +theForm.frm_city.value.trim() + ", " +theForm.frm_postcode.value.trim() + ", " +theForm.frm_state.value.trim();
		geocoder.geocode( { 'address': myAddress}, function(results, status) {		
		if (status == google.maps.GeocoderStatus.OK) {
			theForm.frm_lat.value = results[0].geometry.location.lat();
			theForm.frm_lng.value = results[0].geometry.location.lng();
			theLat = theForm.frm_lat.value;
			theLng = theForm.frm_lng.value
			var params = "frm_street=" + street;
			params += "&frm_app_email=" + appEmail
			params += "&frm_city=" + city;
			params += "&frm_postcode=" + postcode;			
			params += "&frm_state=" + state;
			params += "&frm_lat=" + theLat;
			params += "&frm_lng=" + theLng;
			params += "&frm_description=" + theDescription;
			params += "&frm_request_date=" + requestDate;
			params += "&frm_phone=" + thePhone;
			params += "&frm_toaddress=" + ToAddress;
			params += "&frm_pickup=" + thePickup;
			params += "&frm_arrival=" + theArrival;			
			params += "&frm_username=" + theApprover;
			params += "&frm_patient=" + thePatient;
			params += "&frm_orig_fac=" + origFac;
			params += "&frm_rec_fac=" + recFac;
			params += "&frm_scope=" + theScope;
			params += "&frm_comments=" + theComments;
			var paramsEncoded = encodeURI(params);
			var url = './ajax/insert_request.php?'+paramsEncoded;
			sendRequest (url,local_handleResult, "");			// does the work via POST
			} else { 
			alert("Geocode lookup failed: " + status);
			$('the_form').style.display="block";
			$('waiting').style.display='none';
			alert("Couldn't insert your request at this time due to an error, please try again.");
			return
			}
		});				// end geocoder.geocode()
		}
	}

function pausejs(millis) {
	var date = new Date();
	var curDate = null;
	do { curDate = new Date(); }
	while(curDate-date < millis);
	}
	
function local_handleResult(req) {			// the called-back function
	countmail = 0;
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
		pausejs(2000);
		if((to_str2 == "") && (smsg_to_str2 == "") && (text_str2 == "")) {
			} else {
			var url ="../do_send_mail.php?to_str=" + to_str2 + "&smsg_to_str=" + smsg_to_str2 + "&subject_str=" + subject_str2 + "&text_str=" + encodeURI(text_str2) + "&version=" + randomnumber;
			sendRequest (url,mail_handleResult, "");
			}
		pausejs(2000);
		if((to_str3 == "") && (smsg_to_str3 == "") && (text_str3 != "")) {
			} else {
			var url ="../do_send_mail.php?to_str=" + to_str3 + "&smsg_to_str=" + smsg_to_str3 + "&subject_str=" + subject_str3 + "&text_str=" + encodeURI(text_str3) + "&version=" + randomnumber;
			}
		the_link = "<SPAN>Your request has been inserted successfully</SPAN><BR /><BR /><BR /><BR />";		
		the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>";
//		the_link += "<BR /><BR />" + countmail + " messages have been sent";
		if($('waiting')) {$('waiting').style.display='none';}
		$('result').style.display = 'inline-block';
		$('done').innerHTML = the_link;
		window.opener.get_requests();
		}
	}			// end function local handleResult	
function mail_handleResult(req) {
	var the_response=JSON.decode(req.responseText);	
	if(parseInt(the_response[0]) > 0) {
		countmail++;
		}
	}

function sendRequest(url,callback,postData) {
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
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
	
function logged_in() {								// returns boolean
	var temp = <?php print $logged_in;?>;
	return temp;
	}	
	
function isNull(val) {								// checks var stuff = null;
	return val === null;
	}
	
function do_lat (lat) {
	document.add.frm_lat.value=lat;			// 9/9/08
	}
function do_lng (lng) {
	document.add.frm_lng.value=lng;
	}

function do_fac_to_loc(text, index){			// 9/22/09
	var theFaclat = fac_lat[index];
	var theFaclng = fac_lng[index];
	var theFacstreet = fac_street[index];
	var theFaccity = fac_city[index];
	var theFacstate = fac_state[index];
	do_lat(theFaclat);
	do_lng(theFaclng);
	document.add.frm_street.value = theFacstreet
	document.add.frm_city.value = theFaccity;
	document.add.frm_state.value = theFacstate;	
	}					// end function do_fac_to_loc
	
function do_rec_fac_to_loc(text, index){			// 9/22/09
	var recFaclat = rec_fac_lat[index];
	var recFaclng = rec_fac_lng[index];
	var recFacstreet = rec_fac_street[index];
	var recFaccity = rec_fac_city[index];
	var recFacstate = rec_fac_state[index];
	do_lat(recFaclat);
	do_lng(recFaclng);
	document.add.frm_to_street.value = recFacstreet
	document.add.frm_to_city.value = recFaccity;
	document.add.frm_to_state.value = recFacstate;	
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
	
function GUnload(){
	return;
	}		

function do_logout() {
	document.gout_form.submit();
	}		
<?php
$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$rec_fac_menu = "<SELECT NAME='frm_rec_fac' onChange='do_rec_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>";
$rec_fac_menu .= "<OPTION VALUE=0 selected>Receiving Facility</OPTION>";
while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
		$rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
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
<BODY onLoad="out_frames(); location.href = '#top';">
	<FORM NAME="go" action="#" TARGET = "main"></FORM>
	<DIV id='outer' style='position: absolute; width: 95%; text-align: center; margin: 10px; height: 690px; overflow: hidden;'>
		<DIV id='the_form' style='height: 100%; overflow: hidden;'>
			<DIV id='the_heading' class='heading' style='font-size: 1.25em; height: 30px;'>ADD A NEW REQUEST
				<SPAN id='sub_but' CLASS ='plain' style='float: none; font-size: 1em;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "sub_request();">Submit</SPAN>
				<SPAN id='can_but' CLASS ='plain' style='float: none; font-size: 1em;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN>		
			</DIV>
			<DIV id='inner' style='z-index: 1; overflow-y: scroll; height: 660px;'>
				<FORM NAME='add' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
				<TABLE style='width: 100%;'>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Requested By</TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($_SESSION['user_id']);?></TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>							
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your name as the service approver'><?php print get_text('Approver');?>:</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_approver' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your contact email, all updates will be provided to this address'><?php print get_text('Your Email');?>:</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_app_email' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your contact phone number'><?php print get_text('Your Contact');?>:</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_app_contact' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='When job is required'>When Required:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD><TD class='td_data' style='text-align: left;'><?php print generate_dateonly_dropdown('request_date',0,FALSE);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your organisation name OR the private Homecare / Carehome agency name for recharging later'><?php print get_text('Company Name');?>: </TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_company' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Homecare / Carehome manager name for recharging later'><?php print get_text('Company Manager');?>: </TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_contact' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Homecare / Carehome manager number for contact if required'><?php print get_text('Contact Manager Phone');?>: </TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_contactno' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Who is the service user - if this is a pickup, who is being picked up'><?php print get_text('Patient');?>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Contact number of person being served'><?php print get_text('Patient');?> <?php print get_text('Phone');?>:</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_phone' TYPE='TEXT' SIZE='16' MAXLENGTH='16' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='<?php print get_text('Patient');?> ID'><?php print get_text('Patient');?> ID</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient_id' TYPE='TEXT' SIZE='12' MAXLENGTH='12' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Pickup time from start address'><?php print get_text('Pickup Time');?>:</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_pickup' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Arrival time at destination'>(OR)&nbsp;&nbsp;<?php print get_text('Arrival Time');?>:</TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_arrival' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Start Address');?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='City'><?php print get_text('City');?>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE="<?php print get_city($_SESSION['user_id']);?>"></TD>
					</TR>		
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_postcode' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
					</TR>	
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Destination');?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='City'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE="<?php print get_variable('def_city');?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_postcode' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>						
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Additional Addresses');?></TD>
					</TR>
					<TR>
						<TD COLSPAN=99 ID='td_wrapper'>
							<DIV id="formline">	
							</DIV>
						</TD>
					</TR>
					<TR class='even' style='height: 30px; vertical-align: middle;'>
						<TD class='td_label' COLSPAN=99>
							<SPAN id='add_newline' class='plain' style='float: none; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='new_line();'>Add Line</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
						</TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>					
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Originating Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_fac_menu;?></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_fac_menu;?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;'>Free Text <?php print get_text('Description');?>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_description" COLS="45" ROWS="10" WRAP="virtual"></TEXTAREA></TD>
					</TR>		
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' COLSPAN=2 style='text-align: center;'><FONT COLOR='RED' SIZE='-1'>*</FONT>&nbsp;&nbsp;&nbsp;<B>Required</B></TD></TD>
					</TR>	
				</TABLE>
				<INPUT NAME='requester' TYPE='hidden' SIZE='24' VALUE="<?php print $_SESSION['user_id'];?>">
				<INPUT NAME='frm_lat' TYPE='hidden' SIZE='10' VALUE="">
				<INPUT NAME='frm_lng' TYPE='hidden' SIZE='10' VALUE="">
				</FORM>
				<FORM METHOD='POST' NAME="gout_form" action="index.php">
				<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
				</FORM>
			</DIV>
		</DIV>
	</DIV>
	<DIV id='waiting' style='display: none; text-align: center;'></DIV>
	<DIV id='result' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
		<DIV id='done'></DIV>
	</DIV>
</BODY>
</HTML>
