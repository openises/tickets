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
$isGuest = (is_guest()) ? 1 : 0;
$sess_id = $_SESSION['id'];
$requester = get_owner($_SESSION['user_id']);
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$showmaps = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) ? 1 : 0;
$api_key = get_variable('gmaps_api_key');
$key_str = (strlen($api_key) == 39) ? "key={$api_key}&" : false;
$gmaps_ok = ($key_str) ? 1 : 0;
/*$mandatoryFields = array(
					"Approver",
					"Your Email",
					"Your Contact",
					"Request Date",
					"Company Name",
					"Company Manager",
					"Company Manager Phone",
					"Patient",
					"Patient Phone",
					"Patient ID",
					"Pickup or Arrival Time",
					"Start Street",
					"Start City",
					"Start Postcode",
					"Start State",
					"Destination Street",
					"Destination City",
					"Destination Postcode",
					"Destination State",
					"Return Journey",
					"Return Pickup Time",
					"Return Start street",
					"Return Start City",
					"Return Start Postcode",
					"Return Start State",
					"Return Destination Street",
					"Return Destination City",
					"Return Destination Postcode",
					"Return Destination State",
					"Description"
					);*/
					
$mandatoryFields = array(true,true,true,true,true,true,true,true,true,false,true,true,true,false,true,true,true,false,true,true,true,true,true,false,true,true,true,false,true,true);

function isMandatory($id) {
	global $mandatoryFields;
	if($mandatoryFields[$id]) {
		return "<FONT COLOR='RED' SIZE='-1'>*</FONT>";
		} else {
		return "";
		}
	}

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
	
function generate_time_dropdown($fieldname, $hour=00, $minute=00, $disabled = FALSE) {			// 'extra allows 'disabled'
	$dis_str = ($disabled)? " disabled" : "" ;
	$output = "<SELECT name='frm_hour_$fieldname' $dis_str onChange='setPickupDropoffHour(\"" . $fieldname . "\", this.options[selectedIndex].value);'>";
	for($i = 0; $i < 25; $i++){
		if($i < 10) {$di = "0" . $i;} else {$di = $i;}
		$output .= "<OPTION VALUE='$i'";
		$output .= ($hour == $di) ? " SELECTED>$di</OPTION>" : ">$di</OPTION>";
		}
	$output .= "</SELECT>";
	$output .= "&nbsp;<SELECT name='frm_minute_$fieldname' $dis_str onChange='setPickupDropoffMinute(\"" . $fieldname . "\", this.options[selectedIndex].value);'>";
	for($i = 0; $i < 60; $i++){
		if(count(strval($i)) < 2) {$di = "0" . $i;} else {$di = $i;}
		if($i < 10) {$di = "0" . $i;} else {$di = $i;}
		$output .= "<OPTION VALUE='$i'";
		$output .= ($minute == $di) ? " SELECTED>$di</OPTION>" : ">$di</OPTION>";
		}
	$output .= "</SELECT>";
	$output .= "<INPUT TYPE='hidden' NAME='frm_" . $fieldname . "' VALUE = '' />";
	return $output;
	}		// end function generate_time_dropdown(
	

$now = time() - (intval(get_variable('delta_mins')*60));
$api_key = trim(get_variable('gmaps_api_key'));
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Service User Portal</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="./css/stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<style type="text/css">
body {overflow:hidden}
</style>
<?php
require_once('../incs/all_forms_js_variables.inc.php');
?>
<SCRIPT TYPE="application/x-javascript" SRC="../js/jss.js"></SCRIPT>
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
<script type="application/x-javascript" src="../js/usng.js"></script>
<script type="application/x-javascript" src="../js/osgb.js"></script>
<?php
if($key_str) {
	if($https) {
?>
		<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
		<script src="./js/Google.js"></script>
<?php
		} else {
?>
		<script src="https://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
		<script src="./js/Google.js"></script>
<?php				
		}
	}
?>
<script type="application/x-javascript" src="../js/osm_map_functions.js"></script>
<script type="application/x-javascript" src="../js/L.Graticule.js"></script>
<script type="application/x-javascript" src="../js/leaflet-providers.js"></script>
<script type="application/x-javascript" src="../js/geotools2.js"></script>
<SCRIPT>
var thelevel = '<?php print $the_level;?>';
var locale = <?php print get_variable('locale');?>;
var my_Local = <?php print get_variable('local_maps');?>;
var def_lon = <?php print get_variable('def_lng');?>;
var def_lat = <?php print get_variable('def_lat');?>;
var def_zoom = <?php print get_variable('def_zoom');?>;
var zoom = <?php print get_variable('def_zoom');?>;
var guest = <?php print $isGuest;?>;
var sess_id = "<?php print $sess_id;?>";
var good_gmapsapi = <?php print $gmaps_ok;?>;
var currentSessionLayer = "<?php print $_SESSION['layer_inuse'];?>";
var icons=[];
icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>] = 1;	// blue
icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>] = 2;	// yellow
icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>] =  3;	// red
var setZoom = 1;
var theZoom = 1;
var max_zoom = 1;
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
var theRetLat;
var theRetLng;
var theRetLat;
var theRetLng;
var showall = "yes";
var ct = 1;
var countmail = 0;
var the_link = "";
var isReturn = 0;
var gotLatLng = false;
var gotRetLatLng = false;
var mandatoryFields = <?php echo json_encode($mandatoryFields); ?>;
var viewportwidth;
var viewportheight;
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
	set_fontsizes(viewportwidth, "popup");
	}

function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) top.location.href = document.location.href;
	}		// end function out_frames()
	
function setPickupDropoffHour(formfield, theHour) {
	var theForm = document.forms['add'];
	theHour = (theHour.length < 2) ? "0" + theHour : theHour;
	if(formfield == "pickup") {
		theForm.frm_pickup.value = theHour;
		} else if(formfield == "arrival") {
		theForm.frm_arrival.value = theHour;
		} else if(formfield == "ret_time") {
		theForm.frm_ret_time.value = theHour;
		}
	}
	
function setPickupDropoffMinute(formfield, theMinute, theForm) {
	var theForm = document.forms['add'];
	theMinute = (theMinute.length < 2) ? "0" + theMinute : theMinute;
	if(formfield == "pickup") {
		theForm.frm_pickup.value += ":" + theMinute;
		} else if(formfield == "arrival") {
		theForm.frm_arrival.value += ":" + theMinute;
		} else if(formfield == "ret_time") {
		theForm.frm_ret_time.value += ":" + theMinute;
		}
	}

function show_return(selector) {
	var theForm = document.forms['add'];
	var selectedOption = selector.value;
	window.isReturn = selectedOption;
	if(selectedOption == 1) {
		$('retA').style.display = ""; $('retA2').style.display = ""; $('retB').style.display = "";$('retB2').style.display = "";
		$('ret1').style.display = ""; $('ret1A').style.display = ""; $('ret1B').style.display = "";
		$('ret2').style.display = ""; $('ret2A').style.display = ""; $('ret2B').style.display = "";
		$('ret3').style.display = ""; $('ret3A').style.display = ""; $('ret3B').style.display = "";
		$('ret4').style.display = ""; $('ret4A').style.display = ""; $('ret4B').style.display = "";
		$('ret5').style.display = ""; $('ret5A').style.display = ""; $('ret5B').style.display = "";
		$('ret6').style.display = ""; $('ret6A').style.display = ""; $('ret6B').style.display = "";
		$('ret7').style.display = ""; $('ret7A').style.display = ""; $('ret7B').style.display = "";
		$('ret8').style.display = ""; $('ret8A').style.display = ""; $('ret8B').style.display = "";
		$('ret9').style.display = ""; $('ret9A').style.display = ""; $('ret9B').style.display = "";
		theForm.frm_ret_street.value = theForm.frm_to_street.value;
		theForm.frm_ret_city.value = theForm.frm_to_city.value;
		theForm.frm_ret_postcode.value = theForm.frm_to_postcode.value;
		theForm.frm_ret_state.value = theForm.frm_to_state.value;
		theForm.frm_retto_street.value = theForm.frm_street.value;
		theForm.frm_retto_city.value = theForm.frm_city.value;
		theForm.frm_retto_postcode.value = theForm.frm_postcode.value;
		theForm.frm_retto_state.value = theForm.frm_state.value;
		} else {
		$('retA').style.display = "none";$('retA2').style.display = "none";$('retB').style.display = "none";$('retB2').style.display = "none";
		$('ret1').style.display = "none";$('ret1A').style.display = "none";$('ret1B').style.display = "none";
		$('ret2').style.display = "none";$('ret2A').style.display = "none";$('ret2B').style.display = "none";
		$('ret3').style.display = "none";$('ret3A').style.display = "none";$('ret3B').style.display = "none";
		$('ret4').style.display = "none";$('ret4A').style.display = "none";$('ret4B').style.display = "none";
		$('ret5').style.display = "none"; $('ret5A').style.display = "none"; $('ret5B').style.display = "none";
		$('ret6').style.display = "none"; $('ret6A').style.display = "none"; $('ret6B').style.display = "none";
		$('ret7').style.display = "none"; $('ret7A').style.display = "none"; $('ret7B').style.display = "none";
		$('ret8').style.display = "none"; $('ret8A').style.display = "none"; $('ret8B').style.display = "none";
		$('ret9').style.display = "none"; $('ret9A').style.display = "none"; $('ret9B').style.display = "none";		
		}
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
	the_text +=	"<TD class='inside_td_label' style='text-align: left;' TITLE='<?php print get_text('Patient');?> name'><?php print get_text('Patient');?>:</TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_patient_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=''></TD>";
	the_text += "</TR>";
	the_text += "<TR class='odd'>";
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='<?php print get_text('Patient');?> ID'><?php print get_text('Patient');?> ID:</TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_patient_id_extra[]' TYPE='TEXT' SIZE='12' MAXLENGTH='12' VALUE=''></TD>";
	the_text += "</TR>";
	the_text +=	"<TR class='even'>";	
	the_text +=	"<TD class='inside_td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?>:</TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_street_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=''></TD>";
	the_text += "</TR>";
	the_text += "<TR class='odd'>";	
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='City'>City:</TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_city_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE='" + defCity + "'></TD>";
	the_text += "</TR>";
	the_text += "<TR class='even'>";	
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?>:</TD>";
	the_text += "<TD class='inside_td_data' style='text-align: left;'><INPUT NAME='frm_to_postcode_extra[]' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=''></TD>";
	the_text += "</TR>";		
	the_text += "<TR class='odd'>";
	the_text += "<TD class='inside_td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?>:</TD>";
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
	
function sub_request() {
	var lat;
	var lng;
	var retlat;
	var retlng;
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
	var retJourney = window.isReturn;
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
		var pickupArr = thePickup.split(":");
		if(!pickupArr[1]) {thePickup = pickupArr[0] + ":00";}
		theTimeDet += "Pickup Time: ";		
		theTimeDet += thePickup;
		theTimeDet += "\r\n";
		}
	if(theArrival != "") {
		var arriveArr = theArrival.split(":");
		if(!arriveArr[1]) {theArrival = arriveArr[0] + ":00";}
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
	var toStreet = theForm.frm_to_street.value;
	var toCity = theForm.frm_to_city.value;
	var toPostcode = theForm.frm_to_postcode.value;
	var toState = theForm.frm_to_state.value;
	if(theForm.frm_to_postcode != "") {
		var ToAddress = encodeURI(theForm.frm_to_street.value + ", " + theForm.frm_to_city.value + ", " + theForm.frm_to_postcode.value + ", " + theForm.frm_to_state.value);
		} else {
		var ToAddress = encodeURI(theForm.frm_to_street.value + ", " + theForm.frm_to_city.value + ", " + theForm.frm_to_state.value);
		}
	var dest_address_array = ToAddress.split(",");
	if(dest_address_array[0] == "") {
		ToAddress = "";
		}
	var theUserName = "<?php print addslashes(get_user_name($_SESSION['user_id']));?>";
	var origFac = theForm.frm_orig_fac.value;
	var recFac = theForm.frm_rec_fac.value;	
	var theScope = theForm.frm_patient.value + " " + requestDate;
	var theComments = "";
	if(mandatoryFields[0] && theApprover == "") { err_msg += "Your name as Approver required\n"; }
	if(mandatoryFields[1] && appEmail == "") { err_msg += "Your email address is required for updates\n"; }
	if(mandatoryFields[2] && theAppContact == "") { err_msg += "Your contact phone number required\n"; }
	if(mandatoryFields[3] && requestDate == "") { err_msg += "Request date required\n"; }	
	if(mandatoryFields[4] && theCompany == "") { err_msg += "Name of Company required\n"; }
	if(mandatoryFields[5] && theManager == "") { err_msg += "Name of Company Manager required\n"; }
	if(mandatoryFields[6] && theManagerPhone == "") { err_msg += "Contact Number for Company Manager required\n"; }
	if(mandatoryFields[7] && thePatient == "") { err_msg += "Name of Person required\n"; }
	if(mandatoryFields[8] && thePhone == "") { err_msg += "Contact number of Person required\n"; }
	if(mandatoryFields[9] && thePatientID == "") { err_msg += "Person ID required\n"; }
	if(mandatoryFields[10] && thePickup == "" && theArrival == "") { err_msg += "Either a Pickup or Arrival Time is required\n"; }
	if(mandatoryFields[11] && street == "") { err_msg += "<?php print get_text('Street Address');?> required\n"; }
	if(mandatoryFields[12] && city == "") { err_msg += "City is required\n"; }
	if(mandatoryFields[13] && postcode == "") { err_msg += "<?php print get_text('Postcode');?> is required\n"; }
	if(mandatoryFields[14] && state == "") { err_msg += "<?php print get_text('State');?> is required, for UK State is UK\n"; }
	if(mandatoryFields[15] && toStreet == "") { err_msg += "Destination <?php print get_text('Street Address');?> required\n"; }
	if(mandatoryFields[16] && toCity == "") { err_msg += "Destination City is required\n"; }
	if(mandatoryFields[17] && toPostcode == "") { err_msg += "Destination <?php print get_text('Postcode');?> is required\n"; }
	if(mandatoryFields[18] && toState == "") { err_msg += "Destination <?php print get_text('State');?> is required, for UK State is UK\n"; }
	if(mandatoryFields[19] && retJourney == 1) {
		if(mandatoryFields[20] && theForm.frm_ret_time.value == "") {err_msg += "Return journey requested but no return pickup time provided\n";}
		if(mandatoryFields[21] && theForm.frm_ret_street.value == "") {err_msg += "Return journey requested but no pickup street address provided\n";}
		if(mandatoryFields[22] && theForm.frm_ret_city.value == "") {err_msg += "Return journey requested but no pickup city provided\n";}
		if(mandatoryFields[23] && theForm.frm_ret_postcode.value == "") {err_msg += "Return journey requested but no pickup postcode provided\n";}
		if(mandatoryFields[24] && theForm.frm_ret_state.value == "") {err_msg += "Return journey requested but no pickup state provided\n";}	
		if(mandatoryFields[25] && theForm.frm_retto_street.value == "") {err_msg += "Return journey requested but no destination street address provided\n";}
		if(mandatoryFields[26] && theForm.frm_retto_city.value == "") {err_msg += "Return journey requested but no destination city provided\n";}
		if(mandatoryFields[27] && theForm.frm_retto_postcode.value == "") {err_msg += "Return journey requested but no destination postcode provided\n";}
		if(mandatoryFields[28] && theForm.frm_retto_state.value == "") {err_msg += "Return journey requested but no destination state provided\n";}			
		}
	if(mandatoryFields[29] && theForm.frm_description.value == "") { err_msg += "<?php print get_text('Description');?> is required\n"; }
	if(err_msg != "") {
		alert ("Please correct the following and re-submit:\n\n" + err_msg);
		return;
		} else {
		$('the_form').style.display="none";
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Inserting Request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		if(postcode != "") {
			var address = street.trim() + ", " +city.trim() + ", " + postcode.trim() + ", " + state.trim();
			} else {
			var address = street.trim() + ", " +city.trim() + ", " + state.trim();				
			}
		control.options.geocoder.geocode(address, function(results) {
			if(!results[0]) {
				window.theLat = lat = "<?php print get_variable('def_lat');?>";
				window.theLng = lng = "<?php print get_variable('def_lng');?>";
				} else {
				var r = results[0]['center'];
				window.theLat = lat = r.lat;
				window.theLng = lng = r.lng;
				}
			var params = "frm_street=" + street;
			params += "&frm_app_email=" + appEmail
			params += "&frm_city=" + city;
			params += "&frm_postcode=" + postcode;			
			params += "&frm_state=" + state;
			params += "&frm_lat=" + lat;
			params += "&frm_lng=" + lng;
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
			});
		}	
	}
	
function sub_retrequest() {
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
	var retJourney = theForm.frm_return_journey.value;
	var street = theForm.frm_ret_street.value;
	var city = theForm.frm_ret_city.value;
	var postcode = theForm.frm_ret_postcode.value;
	var state = theForm.frm_ret_state.value;
	var theApprover = theForm.frm_approver.value;
	var theAppContact = theForm.frm_app_contact.value;
	var theCompany = theForm.frm_company.value;
	var theManager = theForm.frm_contact.value;
	var theManagerPhone = theForm.frm_contactno.value;	
	var thePickup = theForm.frm_ret_time.value;
	var theArrival = "";	
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
		var pickupArr = thePickup.split(":");
		if(!pickupArr[1]) {thePickup = pickupArr[0] + ":00";}
		theTimeDet += "Pickup Time: ";		
		theTimeDet += thePickup;
		theTimeDet += "\r\n";
		}
	if(theArrival != "") {
		var arriveArr = theArrival.split(":");
		if(!arriveArr[1]) {theArrival = arriveArr[0] + ":00";}
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
	var ToAddress = encodeURI(theForm.frm_retto_street.value + ", " + theForm.frm_retto_city.value + ", " + theForm.frm_retto_state.value);
	var dest_address_array = ToAddress.split(",");
	if(dest_address_array[0] == "") {
		ToAddress = "";
		}
	var theUserName = "<?php print addslashes(get_user_name($_SESSION['user_id']));?>";
	var origFac = theForm.frm_orig_fac.value;
	var recFac = theForm.frm_rec_fac.value;	
	var theScope = theForm.frm_patient.value + " " + requestDate + " - Return Journey";
	var theComments = "";
	if(postcode != "") {
		var RetAddress = theForm.frm_ret_street.value.trim() + ", " + theForm.frm_ret_city.value.trim() + ", " + theForm.frm_ret_postcode.value.trim() + ", " + theForm.frm_ret_state.value.trim();
		} else {
		var RetAddress = theForm.frm_ret_street.value.trim() + ", " + theForm.frm_ret_city.value.trim() + ", " + theForm.frm_ret_state.value.trim();
		}
	control.options.geocoder.geocode(RetAddress, function(results) {
		if(!results[0]) {
			theRetLat = "<?php print get_variable('def_lat');?>";
			theRetLng = "<?php print get_variable('def_lng');?>";
			} else {
			var r = results[0]['center'];
			theRetLat = r.lat;
			theRetLng = r.lng;
			}
		$('the_form').style.display="none";
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Inserting Return Request<BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
		var params = "frm_street=" + street;
		params += "&frm_app_email=" + appEmail
		params += "&frm_city=" + city;
		params += "&frm_postcode=" + postcode;			
		params += "&frm_state=" + state;
		params += "&frm_lat=" + theRetLat;
		params += "&frm_lng=" + theRetLng;
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
		sendRequest (url,local_handleResult2, "");			// does the work via POST
		});
	}

function pausejs(millis) {
	var date = new Date();
	var curDate = null;
	do { curDate = new Date(); }
	while(curDate-date < millis);
	}
	
function local_handleResult(req) {			// the called-back function
	var theForm = document.forms['add'];
	var retJourney = theForm.frm_return_journey.value;
	countmail = 0;
	var the_response=JSON.decode(req.responseText);	
	if(the_response[0] == 0) {
		$('waiting').style.display='none';					
		$('result').style.display = 'inline-block';
		the_link = "Could not insert new Ticket, please try again<BR /><BR /><BR /><BR />";		
		the_link += "<SPAN id='finish' class = 'plain text' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.loadIt(); window.close();'>Close</SPAN>";
		$('done').innerHTML = the_link;	
		} else {
		if(retJourney == 0) {
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
			the_link += "<SPAN id='finish' class = 'plain text' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.loadIt(); window.close();'>Close</SPAN>";
			if($('waiting')) {$('waiting').style.display='none';}
			$('result').style.display = 'inline-block';
			$('done').innerHTML = the_link;
			window.opener.loadIt();
			} else {
			pausejs(3000);
			sub_retrequest();
			}
		}
	}			// end function local handleResult
	
function local_handleResult2(req) {			// the called-back function for the return journey
	var theForm = document.forms['add'];
	var retJourney = theForm.frm_return_journey.value;
	countmail = 0;
	var the_response=JSON.decode(req.responseText);	
	if(the_response[0] == 0) {
		$('waiting').style.display='none';					
		$('result').style.display = 'inline-block';
		the_link = "Could not insert new Ticket, please try again<BR /><BR /><BR /><BR />";		
		the_link += "<SPAN id='finish' class = 'plain text' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.loadIt(); window.close();'>Close</SPAN>";
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
		the_link += "<SPAN id='finish' class = 'plain text' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.loadIt(); window.close();'>Close</SPAN>";
		if($('waiting')) {$('waiting').style.display='none';}
		$('result').style.display = 'inline-block';
		$('done').innerHTML = the_link;
		window.opener.loadIt();
		}
	}			// end function local handleResult
	
function mail_handleResult(req) {
	var the_response=JSON.decode(req.responseText);
	if(the_response && parseInt(the_response[0]) > 0) {
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
			<DIV id='the_heading' class='heading' style='font-size: 1.25em; line-height: 40px;'>ADD A NEW REQUEST
				<SPAN id='sub_but' CLASS ='plain text' style='float: none; font-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "sub_request();">Submit</SPAN>
				<SPAN id='can_but' CLASS ='plain text' style='float: none; font-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.opener.loadIt(); window.close();">Cancel</SPAN>		
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
						<TD class='td_label' style='text-align: left;' TITLE='Your name as the service approver'><?php print get_text('Approver');?>:&nbsp;<?php print isMandatory(0);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_approver' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your contact email, all updates will be provided to this address'><?php print get_text('Your Email');?>:&nbsp;<?php print isMandatory(1);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_app_email' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your contact phone number'><?php print get_text('Your Contact');?>:&nbsp;<?php print isMandatory(2);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_app_contact' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='When job is required'>When Required:&nbsp;<?php print isMandatory(3);?></TD><TD class='td_data' style='text-align: left;'><?php print generate_dateonly_dropdown('request_date',0,FALSE);?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Your organisation name OR the private Homecare / Carehome agency name for recharging later'><?php print get_text('Company Name');?>:&nbsp;<?php print isMandatory(4);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_company' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Homecare / Carehome manager name for recharging later'><?php print get_text('Company Manager');?>:&nbsp;<?php print isMandatory(5);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_contact' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Homecare / Carehome manager number for contact if required'><?php print get_text('Contact Manager Phone');?>:&nbsp;<?php print isMandatory(6);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_contactno' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Who is the service user - if this is a pickup, who is being picked up'><?php print get_text('Patient');?>:&nbsp;<?php print isMandatory(7);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Contact number of person being served'><?php print get_text('Patient');?> <?php print get_text('Phone');?>:&nbsp;<?php print isMandatory(8);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_phone' TYPE='TEXT' SIZE='16' MAXLENGTH='16' VALUE=""></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='<?php print get_text('Patient');?> ID'><?php print get_text('Patient');?> ID:&nbsp;<?php print isMandatory(9);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient_id' TYPE='TEXT' SIZE='12' MAXLENGTH='12' VALUE=""></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Pickup time from start address'><?php print get_text('Pickup Time');?>:&nbsp;<?php print isMandatory(10);?></TD><TD class='td_data' style='text-align: left;'><?php print generate_time_dropdown('pickup', 0, 0, FALSE);?></TD>
					</TR>
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Arrival time at destination'>(OR)&nbsp;&nbsp;<?php print get_text('Arrival Time');?>:</TD><TD class='td_data' style='text-align: left;'><?php print generate_time_dropdown('arrival', 0, 0, FALSE);?></TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Start Address');?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?>:&nbsp;<?php print isMandatory(11);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='City'>City:&nbsp;<?php print isMandatory(12);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE="<?php print get_city($_SESSION['user_id']);?>"></TD>
					</TR>		
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?>:&nbsp;<?php print isMandatory(13);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_postcode' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>					
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?>:&nbsp;<?php print isMandatory(14);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
					</TR>	
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Destination');?></TD>
					</TR>
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?>&nbsp;<?php print isMandatory(15);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='City'>City:&nbsp;<?php print isMandatory(16);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE="<?php print get_variable('def_city');?>"></TD>
					</TR>	
					<TR class='even'>	
						<TD class='td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?>:&nbsp;<?php print isMandatory(17);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_postcode' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>						
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?>:&nbsp;<?php print isMandatory(18);?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_to_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
					</TR>
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' style='text-align: left;' TITLE='Select whether return journey required.'><?php print get_text('Return Journey');?>:&nbsp;<?php print isMandatory(19);?></TD>
						<TD class='td_data' style='text-align: left;'>
							<INPUT type='radio' name='frm_return_journey' value = 0 onClick = "show_return(this);" CHECKED>No
							<INPUT type='radio' name='frm_return_journey' value = 1 onClick = "show_return(this);">Yes						
						</TD>
					</TR>
					<TR id='ret9' class='even' style='display: none;'>	
						<TD id='ret9A' class='td_label' style='text-align: left;' TITLE='Pickup Time for return Journey'><?php print get_text('Pickup Time');?>:&nbsp;<?php print isMandatory(20);?></TD>
						<TD id='ret9B' class='td_data' style='text-align: left;'><?php print generate_time_dropdown('ret_time', 0, 0, FALSE);?></TD>
					</TR>
					<TR id='retA' class='spacer' style='display: none;'>
						<TD id='retA2' class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Start Address');?></TD>
					</TR>
					<TR id='ret1' class='even' style='display: none;'>	
						<TD id='ret1A' class='td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?>:&nbsp;<?php print isMandatory(21);?></TD>
						<TD id='ret1B' class='td_data' style='text-align: left;'><INPUT NAME='frm_ret_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR id='ret2' class='odd' style='display: none;'>	
						<TD id='ret2A' class='td_label' style='text-align: left;' TITLE='City'>City:&nbsp;<?php print isMandatory(22);?></TD>
						<TD id='ret2B' class='td_data' style='text-align: left;'><INPUT NAME='frm_ret_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE="<?php print get_city($_SESSION['user_id']);?>"></TD>
					</TR>		
					<TR id='ret3' class='even' style='display: none;'>	
						<TD id='ret3A' class='td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?>:&nbsp;<?php print isMandatory(23);?></TD>
						<TD id='ret3B' class='td_data' style='text-align: left;'><INPUT NAME='frm_ret_postcode' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>					
					<TR id='ret4' class='odd' style='display: none;'>	
						<TD id='ret4A' class='td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?>:&nbsp;<?php print isMandatory(24);?></TD>
						<TD id='ret4B' class='td_data' style='text-align: left;'><INPUT NAME='frm_ret_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
					</TR>
					<TR id='retB' class='spacer' style='display: none;'>
						<TD id='retB2'  class='spacer' COLSPAN=99 style='height: 15px; font-size: 14px;'><?php print get_text('Destination');?></TD>
					</TR>
					<TR id='ret5' class='even' style='display: none;'>	
						<TD id='ret5A' class='td_label' style='text-align: left;' TITLE='Street Address including building number or name'><?php print get_text('Street');?>:&nbsp;<?php print isMandatory(25);?>*</FONT></TD>
						<TD id='ret5B' class='td_data' style='text-align: left;'><INPUT NAME='frm_retto_street' TYPE='TEXT' SIZE='48' MAXLENGTH='128' VALUE=""></TD>
					</TR>	
					<TR id='ret6' class='odd' style='display: none;'>	
						<TD id='ret6A' class='td_label' style='text-align: left;' TITLE='City'>City:&nbsp;<?php print isMandatory(26);?></TD>
						<TD id='ret6B' class='td_data' style='text-align: left;'><INPUT NAME='frm_retto_city' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE="<?php print get_city($_SESSION['user_id']);?>"></TD>
					</TR>		
					<TR id='ret7' class='even' style='display: none;'>	
						<TD id='ret7A' class='td_label' style='text-align: left;' TITLE='Postcode'><?php print get_text('Postcode');?>:&nbsp;<?php print isMandatory(27);?></TD>
						<TD id='ret7B' class='td_data' style='text-align: left;'><INPUT NAME='frm_retto_postcode' TYPE='TEXT' SIZE='48' MAXLENGTH='48' VALUE=""></TD>
					</TR>					
					<TR id='ret8' class='odd' style='display: none;'>	
						<TD id='ret8A' class='td_label' style='text-align: left;' TITLE='State - for UK this is UK'><?php print get_text('State');?>:&nbsp;<?php print isMandatory(28);?></TD>
						<TD id='ret8B' class='td_data' style='text-align: left;'><INPUT NAME='frm_retto_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print get_variable('def_st');?>"></TD>
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
					<TR class='even'>
						<TD class='td_label' style='line-height: 30px;' COLSPAN=99>
							&nbsp;<SPAN id='add_newline' class='plain text' style='float: none; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='new_line();'>Add Line</SPAN>&nbsp;
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
						<TD class='td_label' style='text-align: left;'>Free Text <?php print get_text('Description');?>:&nbsp;<?php print isMandatory(29);?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_description" COLS="45" ROWS="10" WRAP="virtual"></TEXTAREA></TD>
					</TR>		
					<TR class='spacer'>
						<TD class='spacer' COLSPAN=99></TD>
					</TR>	
					<TR class='odd'>	
						<TD class='td_label' COLSPAN=2 style='text-align: center;'><FONT COLOR='RED' SIZE='-1'>*</FONT>&nbsp;&nbsp;&nbsp;<B>Required</B></TD></TD>
					</TR>
				</TABLE><BR /><BR />	
				<INPUT NAME='requester' TYPE='hidden' SIZE='24' VALUE="<?php print $_SESSION['user_id'];?>">
				<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE = "" />
				<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE = "" />
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
	<DIV id='map_canvas' style='display: none;'></DIV>
	<SCRIPT>
	var map;				// make globally visible
	var mapWidth = <?php print get_variable('map_width');?>;
	var mapHeight = <?php print get_variable('map_height');?>;
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
	set_fontsizes(viewportwidth, "popup");
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	var initZoom = <?php print get_variable('def_zoom');?>;
	init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	</SCRIPT>
</BODY>
</HTML>
