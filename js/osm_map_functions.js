function isIE() { 
	if((navigator.appName == 'Microsoft Internet Explorer') || ((navigator.appName == 'Netscape') && (new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})").exec(navigator.userAgent) != null))) {
		return true;
		} else {
		return false;
		}
	}

var inczindexno = 30000;
var unitzindexno = 20000;
var faczindexno = 10000;

var status_control = [];
var status_bgcolors = [];
var status_textcolors = [];
var fac_status_control = [];
var theLayer;
var theAssigned = [];
var responderbigHeader = false;
var facilitybigHeader = false;
var incidentbigHeader = false;
var warningsbugHeader = false;

var map_is_fixed = false;
var unit_icons=[];
unit_icons[0] = 0;
unit_icons[4] = 4;

var fac_icons=[];
fac_icons[0] = 1;
fac_icons[1] = 2;
fac_icons[2] = 3;
fac_icons[3] = 4;	
fac_icons[4] = 5;
fac_icons[5] = 6;
fac_icons[6] = 7;
fac_icons[7] = 8;

var wl_icons=[];
wl_icons[0] = 0;
wl_icons[1] = 1;
wl_icons[2] = 2;
wl_icons[3] = 3;
wl_icons[4] = 4;

var popupInfo = "";
var currPopup;
var map;				// make globally visible
var myMarker;
var condMarkers;
var sortby = '`date`';	//	10/23/12
var sort = "DESC";	//	10/23/12
var thescreen = 'ticket';	//	10/23/12
var incs_sortarray = [];
var resps_sortarray = [];
var facs_sortarray = [];
var msgs_sortarray = [];
var theResponders = [];
var theFacilities = [];
var popupsarr = [];
var tempCaption = "";
var tcell1 = 0;
var tcell2 = 0;
var tcell3 = 0;
var tcell4 = 0;
var tcell5 = 0;
var tcell6 = 0;
var tcell7 = 0;
var tcell8 = 0;
var tcell9 = 0;
var mcell1 = 0;
var mcell2 = 0;
var mcell3 = 0;
var mcell4 = 0;
var mcell5 = 0;
var mcell6 = 0;
var mcell7 = 0;
var mcell8 = 0;
var cell1 = 0;
var cell2 = 0;
var cell3 = 0
var cell4 = 0;
var cell5 = 0;
var cell6 = 0;
var cell7 = 0;
var cell8 = 0;
var cell9 = 0;
var fcell1 = 0;
var fcell2 = 0;
var fcell3 = 0;
var fcell4 = 0;
var fcell5 = 0;
var fcell6 = 0;
var acell1 = 0;
var acell2 = 0;
var acell3 = 0;
var acell4 = 0;
var acell5 = 0;
var wcell1 = 0;
var wcell2 = 0;
var wcell3 = 0;
var wcell4 = 0;
var wcell5 = 0;
var inc_last_display = 0;
var resp_last_display = 0;
var fac_last_display = 0;
var file_last_display = 0;
var newindow=false;
var curr_table = 'log';
var the_icon;
var currentPopup;
var marker;
var btn;
var selection;
var markers;
var popups = [];
var layercontrol;
var bounds;
var inorout = "inbox";
var folderchanged = false;
var facFin = false;
var fileFin = false;
var theResponder = 0;
var theTicket = 0;
var theFacility = 0;
var thefiletype = 1;
var divTag = false;
var popup_clicked = false;
var infoPopups = [];
var popups = [];
var frontPopup = 0;
var screensize;

function isFloat(n){
    return n != "" && !isNaN(n) && Math.round(n) != n;
	}

String.prototype.rpad = function(padString, length) {
	var str = this;
	while (str.length < length)
		str = str + padString;
	return str;
	}
	
String.prototype.trunc = String.prototype.trunc ||
      function(n){
          return this.length>n ? this.substr(0,n-1)+'&hellip;' : this;
      };
	
function pad(width, string, padding) { 
	return (width <= string.length) ? string : pad(width, string + padding, padding)
	}
	
function bothpad(width, string, padding) { 
	return (width <= string.length) ? string : pad(width, padding + string + padding, padding)
	}

Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
			}
		}
    return false;
	}

function circleInside(point, center, radius) {
	var dist = point.distanceTo(center);
	var theRet = false;
	if(dist <= radius) {
		theRet = true;
		} else {
		theRet = false;
		}
	return theRet;
	}
	
var check_initialized = false;
var check_interval = null;

var swi=1;
var na=document.getElementsByTagName("blink");

function blink_continue() {
	if (b_interval!=null) {return;}
	b_interval = window.setInterval('blink_loop()', 500); 
	}			// end function blink_continue()

function blink_loop() {
	do_blink();
	}			// end function blink_loop()
	
function bringPopupToFront(id) {
	if(frontPopup == id) {return;}
	var theText = "<DIV style='position: relative;'>";
	theText += "<SPAN id='iw_" + id + "' class='plain' style='display: inline; float: right; position: relative; top: 2px; right: 5px; ' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_resppopup(" + id + ");'>...</SPAN>";
	theText += "<DIV id='" + id + "' style='background-color: red; border-radius: 4px; padding: 4px;' onMouseover='bringPopupToFront(this.id);'>" + rmarkers[id].infopopup + "</DIV></DIV>";
	var popup = L.popup({className: "custom-popup", closeButton: false}).setContent(theText);
	infoPopups[id] = popup;
	rmarkers[id].closePopup();
	rmarkers[id].bindPopup(popup).openPopup();
	frontPopup=id; 
	}

function do_assignment_flags() {
	$('show_asgn').onclick = function() {hide_assignment_flags();};
	$('show_asgn').innerHTML = "Hide Assigned<BR /><IMG ID='show_asgn_img' SRC='./images/hide_assigned.png' BORDER=0>";
	for(key in theAssigned) {
		if(theAssigned[key]) {
			if(rmarkers[key].latlng) {
				var theKey = key;
				var theText = "<DIV style='position: relative;'>";
				theText += "<SPAN id='iw_" + theKey + "' class='plain' style='position: relative; top: 2px; right: 5px; display: inline; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_resppopup(" + theKey + ");'>...</SPAN>";
				theText += "<DIV id='" + theKey + "' style='background-color: red; border-radius: 4px; padding: 4px;' onMouseover='bringPopupToFront(this.id);'>" + rmarkers[theKey].infopopup + "</DIV></DIV>";
				var popup = L.popup({className: "custom-popup", closeButton: false}).setContent(theText);
				infoPopups[theKey] = popup;
				if(rmarkers[key] && rmarkers[key].latlng) {rmarkers[key].bindPopup(popup).openPopup(); frontPopup=key;}
				}
			}
		}
	}
	
function do_fs_assignment_flags() {
	$('fs_show_asgn').onclick = function() {hide_fs_assignment_flags();};
	$('fs_show_asgn').innerHTML = "Hide Assigned <IMG ID='fs_show_asgn_img' SRC='./images/hide_assigned_small.png' BORDER=0>";
	for(key in theAssigned) {
		if(theAssigned[key]) {
			if(rmarkers[key].latlng) {
				var theKey = key;
				var theText = "<DIV style='position: relative;'>";
				theText += "<SPAN id='iw_" + theKey + "' class='plain' style='position: relative; top: 2px; right: 5px; display: inline; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_fs_resppopup(" + theKey + ");'>...</SPAN>";
				theText += "<DIV id='" + theKey + "' style='background-color: red; border-radius: 4px; padding: 4px;'  onMouseover='bringPopupToFront(this.id);'>" + rmarkers[theKey].infopopup + "</DIV></DIV>";
				var popup = L.popup({className: "custom-popup", closeButton: false}).setContent(theText);
				infoPopups[theKey] = popup;
				if(rmarkers[key] && rmarkers[key].latlng) {rmarkers[key].bindPopup(popup).openPopup(); frontPopup=key;}
				}
			}
		}
	}
	
function do_indv_assignment_flag(id) {
	var theText = "<DIV style='position: relative;'>";
	theText += "<SPAN id='iw_" + id + "' class='plain' style='position: relative; top: 2px; right: 5px; display: inline; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_resppopup(" + id + ");'>...</SPAN>";
	theText += "<DIV id='" + id + "' style='background-color: red; border-radius: 4px; padding: 4px;'  onMouseover='bringPopupToFront(this.id);'>" + rmarkers[id].infopopup + "</DIV></DIV>";
	var popup = L.popup({className: "custom-popup", closeButton: false}).setContent(theText);
	infoPopups[id] = popup;
	if(rmarkers[id] && rmarkers[id].latlng) {rmarkers[id].bindPopup(popup).openPopup();}
	}
	
function isNumeric(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
	}
	
function hide_assignment_flags() {
	$('show_asgn').onclick = function() {do_assignment_flags();};
	$('show_asgn').innerHTML = "Show Assigned<BR /><IMG ID='show_asgn_img' SRC='./images/assigned.png' BORDER=0>";
	for(var key in theAssigned) {
		if(isNumeric(key)) {
			if(rmarkers[key] && rmarkers[key].latlng) {rmarkers[key].closePopup();}
			}
		}
	}
	
function hide_fs_assignment_flags() {
	$('fs_show_asgn').onclick = function() {do_fs_assignment_flags();};
	$('fs_show_asgn').innerHTML = "Show Assigned <IMG ID='fs_show_asgn_img' SRC='./images/assigned_small.png' BORDER=0>";
	for(var key in theAssigned) {
		if(isNumeric(key)) {
			if(rmarkers[key] && rmarkers[key].latlng) {rmarkers[key].closePopup();}
			}
		}
	}

function do_blink() {
	if (swi == 1) {
		sho="visible";
		swi=0;
		} else {
		sho="hidden";
		swi=1;
		}
	for(i=0;i<na.length;i++) {
		na[i].style.visibility=sho;
		}
	blink_continue();
	}

function change_status_sel(the_control, the_val, theIcon, the_unit) {
	var oldval = false;
	var newval = the_val;
	var existing = false;
	var thelength = false;
	if(document.getElementById(the_control)) {
		thelength = document.getElementById(the_control).options.length;
		existing = document.getElementById(the_control).selectedIndex;
		if(document.getElementById(the_control).options[existing].value) {		
			oldval = document.getElementById(the_control).options[existing].value;
			}
		for(var f = 0; f < thelength; f++) {
			if((document.getElementById(the_control).options[f].value == newval) && (f != existing)) {
				document.getElementById(the_control).options[f].selected = true;
				document.getElementById(the_control).style.backgroundColor = window.status_bgcolors[newval];
				document.getElementById(the_control).style.color = window.status_textcolors[newval];
				$("rsupd_" + the_unit).style.color = "#FFFFFF";
				$("rsupd_" + the_unit).style.backgroundColor = "#000000";
				do_sel_update (the_unit, the_val, theIcon);
				}
			}
		}
	}
	
function logged_in() {								// returns boolean
	var temp = parent.frames["upper"].$("whom").innerHTML==NOT_STR;
	return !temp;
	}

function fence_get() {								// set cycle
	if (check_interval!=null) {return;}			// ????
	check_interval = window.setInterval('check_fence_loop()', 60000);		// 4/7/10 
	}			// end function mu get()

function fence_init() {								// get initial values from server -  4/7/10
	if (check_initialized) { return; }
	check_initialized = true;
		ring_fence();
		exclude();				
		fence_get();				// start loop
	}				// end function mu_init()		
	
function check_fence_loop() {								// monitor for changes - 4/10/10, 6/10/11	
		ring_fence();
		exclude();			
	}			// end function do_loop()			

function blink_text(id, bgcol, bgcol2, maincol, seccol) {	//	6/10/11
	if(!document.getElementById(id)) {
		alert("A unit in your group is\noutside a ring fence\nhowever you aren't currently\nviewing the group it is allocated to");
	} else {	
		function BlinkIt () {
			if(document.getElementById (id)) {
				var blink = document.getElementById (id);
				var flag = id + "_flag";	
				color = (color == maincol) ? seccol : maincol;
				back = (back == bgcol) ? bgcol2 : bgcol;
				blink.style.background = back;
				blink.style.color = color;
				document.getElementById(id).title = "Outside Ringfence";
				if($(flag)) {	
					$(flag).innerHTML = "RF";
					}							
				}
			}
		window.setInterval (BlinkIt, 1000);
		var color = maincol;
		var back = bgcol;				
		}
	}
	
function unblink_text(id) {	//	6/10/11
	if(!document.getElementById(id)) {
	} else {	
	if(document.getElementById (id)) {
		var unblink = document.getElementById (id);
		unblink.style.background = "";
		unblink.style.color = "";			
			}
		}
	}

function blink_text2(id, bgcol, bgcol2, maincol, seccol) {	//	6/10/11
	if(!document.getElementById(id)) {
		alert("A unit in your group is\ninside an exclusion zone\nhowever you aren't currently\nviewing the group it is allocated to");
	} else {	
		function BlinkIt () {
			if(document.getElementById (id)) {
				var blink = document.getElementById (id);
				var flag = id + "_flag";
				color = (color == maincol) ? seccol : maincol;
				back = (back == bgcol) ? bgcol2 : bgcol;
				blink.style.background = back;
				blink.style.color = color;
				document.getElementById(id).title = "Inside Exclusion Zone";
				if($(flag)) {	
					$(flag).innerHTML = "EZ";
					}				
				}
			}
		window.setInterval (BlinkIt, 1000);
		var color = maincol;
		var back = bgcol;				
		}
	}			
	
function unblink_text2(id) {	//	6/10/11
	if(!document.getElementById(id)) {
	} else {	
	if(document.getElementById (id)) {
		var unblink = document.getElementById (id);
		unblink.style.background = "";
		unblink.style.color = "";			
			}
		}
	}	
	
//	Tickets show / hide by Priority functions

function set_initial_pri_disp() {
	$('normal').checked = true;
	$('medium').checked = true;
	$('high').checked = true;
	$('all').checked = true;
	$('none').checked = false;
}

function hideGroup(color, category) {			// 8/7/09 Revised function to correct incorrect display, revised 12/03/10 completely revised
	var priority = color;
	var priority_name="";
	if(priority == 0) {
		priority_name="normal";
		}
	if(priority == 1) {
		priority_name="medium";
		}
	if(priority == 2) {
		priority_name="high";
		}
	if(priority == 4) {
		priority_name="all";
		}
	if(priority == 5) {
		priority_name="none";
		}

	if(priority == 0) {
		for (var i = 1; i < tmarkers.length; i++) {
			if (tmarkers[i]) {
				if ((tmarkers[i].id == priority) && (tmarkers[i].category == category)) {
					tmarkers[i].addTo(map);	
					}
				if ((tmarkers[i].id != priority) && (tmarkers[i].category == category)) {
					map.removeLayer(tmarkers[i]);		
					}

				}		// end if (tmarkers[i])
			} 	// end for ()
		$('normal').checked = true;
		$('medium').checked = false;
		$('high').checked = false;
		$('all').checked = false;
		$('none').checked = false;
		$('pri_all').style.display = '';
		$('pri_none').style.display = '';
		}	//	end if priority == 1
	if(priority == 1) {
		for (var i = 1; i < tmarkers.length; i++) {
			if (tmarkers[i]) {
				if ((tmarkers[i].id == priority) && (tmarkers[i].category == category)) {
					tmarkers[i].addTo(map);		
					}
				if ((tmarkers[i].id != priority) && (tmarkers[i].category == category)) {
					map.removeLayer(tmarkers[i]);		
					}

				}		// end if (tmarkers[i])
			} 	// end for ()
		$('normal').checked = false;
		$('medium').checked = true;
		$('high').checked = false;
		$('all').checked = false;
		$('none').checked = false;
		$('pri_all').style.display = '';
		$('pri_none').style.display = '';
		}	//	end if priority == 2
	if(priority == 2) {
		for (var i = 1; i < tmarkers.length; i++) {
			if (tmarkers[i]) {
				if ((tmarkers[i].id == priority) && (tmarkers[i].category == category)) {
					tmarkers[i].addTo(map);		
					}
				if ((tmarkers[i].id != priority) && (tmarkers[i].category == category)) {
					map.removeLayer(tmarkers[i]);
					}

				}		// end if (tmarkers[i])
			} 	// end for ()
		$('normal').checked = false;
		$('medium').checked = false;
		$('high').checked = true;
		$('all').checked = false;
		$('none').checked = false;
		$('pri_all').style.display = '';
		$('pri_none').style.display = '';
		}	//	end if priority == 3
	if(priority == 4) {		//	show All
		for (var i = 1; i < tmarkers.length; i++) {
			if (tmarkers[i]) {
				if (tmarkers[i].category == category) {
					tmarkers[i].addTo(map);		
					}
				}		// end if (tmarkers[i])
			} 	// end for ()
		$('normal').checked = true;
		$('medium').checked = true;
		$('high').checked = true;
		$('all').checked = true;
		$('none').checked = false;
		$('pri_all').style.display = 'none';
		$('pri_none').style.display = '';
		}	//	end if priority == 4
	if(priority == 5) {		// hide all
		for (var i = 1; i < tmarkers.length; i++) {
			if (tmarkers[i]) {
				if (tmarkers[i].category == category) {
					map.removeLayer(tmarkers[i]);	
					}
				}		// end if (tmarkers[i])
			} 	// end for ()
		$('normal').checked = false;
		$('medium').checked = false;
		$('high').checked = false;
		$('all').checked = false;
		$('none').checked = true;
		$('pri_all').style.display = '';
		$('pri_none').style.display = 'none';
		}	//	end if priority == 5
	}			// end function hideGroup(color, category)

function set_pri_chkbox(control) {
	var pri_control = control;
	if($(pri_control).checked == true) {
		$(pri_control).checked = true;
		} else {
		$(pri_control).checked = false;
		}
	}

//	End of Tickets show / hide by Priority functions

// 	Units show / hide functions				
	
function set_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
	if(!$('RESP_ALL')) {return;}
	var resptbl = document.getElementById('respondertable');
	if(resptbl) {
	var headerRow = resptbl.rows[0];
	var tableRow = resptbl.rows[1];
		if((!window.cell1) || (window.cell1 == 0)) {window.cell1 = tableRow.cells[0].offsetWidth;}
		if((!window.cell2) || (window.cell2 == 0)) {window.cell2 = tableRow.cells[1].offsetWidth;}
		if((!window.cell3) || (window.cell3 == 0)) {window.cell3 = tableRow.cells[2].offsetWidth;}
		if((!window.cell4) || (window.cell4 == 0)) {window.cell4 = tableRow.cells[3].offsetWidth;}
		if((!window.cell5) || (window.cell5 == 0)) {window.cell5 = tableRow.cells[4].offsetWidth;}
		if((!window.cell6) || (window.cell6 == 0)) {window.cell6 = tableRow.cells[5].offsetWidth;}
		if((!window.cell7) || (window.cell7 == 0)) {window.cell7 = tableRow.cells[6].offsetWidth;}
		if($('screenname') == "responders") {
			if((!window.cell8) || (window.cell8 == 0)) {window.cell8 = tableRow.cells[7].offsetWidth;}
			if((!window.cell9) || (window.cell9 == 0)) {window.cell9 = tableRow.cells[8].offsetWidth;}
			}
		}
	if(hidden != 0) {
		$('RESP_ALL').style.display = 'inline';
		$('RESP_ALL_BUTTON').style.display = 'inline';
		$('RESP_ALL').checked = false;	
		} else {
		$('RESP_ALL').style.display = 'none';
		$('RESP_ALL_BUTTON').style.display = 'none';
		$('RESP_ALL').checked = false;
		}
	if((shown != 0) && (number_of_units != 0)) {
		$('RESP_NONE').style.display = 'inline';
		$('RESP_NONE_BUTTON').style.display = 'inline';
		$('RESP_NONE').checked = false;	
		} else {
		$('RESP_NONE').style.display = 'none';
		$('RESP_NONE_BUTTON').style.display = 'none';
		$('RESP_NONE').checked = false;
		}
	for (var i = 0; i < curr_cats.length; i++) {
		var catname = curr_cats[i];
		if(cat_sess_stat[i]=="s") {
			for (var j = 1; j < rmarkers.length; j++) {
				if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == catname)) {
					rmarkers[j].addTo(map);		
					var catid = catname + j;
					if($(catid)) {
						$(catid).style.display = "";
						}
					}
				}
			if($(catname)) {$(catname).checked = true;}
			} else {
			for (var j = 1; j < rmarkers.length; j++) {
				if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == catname)) {
					map.removeLayer(rmarkers[j]);
					var catid = catname + j;
					if($(catid)) {
						$(catid).style.display = "none";
						}
					}
				}
			if($(catname)) {$(catname).checked = false;}
			}				
		}
	if(typeof(resptbl) !== 'undefined') {
		if($('screenname').innerHTML == "responders") {responderlist2_setwidths();} else if($('screenname').innerHTML == "situation") {responderlist_setwidths();}
		}
	}

function do_view_cats() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
	$('go_can').style.display = 'inline';
	$('can_button').style.display = 'inline';
	$('go_button').style.display = 'inline';
	}

function cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
	$('go_can').style.display = 'none';
	$('can_button').style.display = 'none';
	$('go_button').style.display = 'none';
	$('RESP_ALL').checked = false;
	$('RESP_NONE').checked = false;
	set_categories();
	}

function set_chkbox(control) {
	var units_control = control;
  	if($(units_control).checked == true) {
		$(units_control).checked = true;
		} else {
		$(units_control).checked = false;
		}
	do_view_cats();
	}
	
function set_buttons(theType) {
	if(theType == "category") {
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			if ($(category).checked == true) {
				if($('RESP_NONE').checked == true) {
					$('RESP_NONE').checked = false;
					}
				}
			return true;
			}
		} else if (theType == "all") {
		if($('RESP_ALL').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];				
				$(category).checked = true;
				}
			return true;
			}
		} else if (theType == "none") {
		if($('RESP_NONE').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];				
				$(category).checked = false;
				}
			return true;
			}
		}
	}

function do_go_button() {							// 12/03/10	Show Hide categories
	if ($('RESP_ALL').checked == true) {
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=" + sess_id;
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gb_handleResult, params);
			$(category).checked = true;				
			for (var j = 1; j < rmarkers.length; j++) {
				var catid = category + j;
				if($(catid)) {
					$(catid).style.display = "";
					}
				if((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {rmarkers[j].addTo(map);}
				}
			}
		$('RESP_ALL').checked = false;
		$('RESP_ALL').style.display = 'none';
		$('RESP_ALL_BUTTON').style.display = 'none';				
		$('RESP_NONE').style.display = 'inline';
		$('RESP_NONE_BUTTON').style.display = 'inline';				
		$('go_button').style.display = 'none';
		$('can_button').style.display = 'none';				
		} else if ($('RESP_NONE').checked == true) {
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=" + sess_id;
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gb_handleResult, params);	
			$(category).checked = false;
			for (var j = 1; j < rmarkers.length; j++) {
				var catid = category + j;
				if($(catid)) {
					$(catid).style.display = "none";
					}
				if((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {map.removeLayer(rmarkers[j]);}
				}
			}
		$('RESP_NONE').checked = false;
		$('RESP_ALL').style.display = 'inline';
		$('RESP_ALL_BUTTON').style.display = 'inline';				
		$('RESP_NONE').style.display = 'none';
		$('RESP_NONE_BUTTON').style.display = 'none';					
		$('go_button').style.display = 'none';
		$('can_button').style.display = 'none';
		} else {
		var x = 0;
		var y = 0;
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			if ($(category).checked == true) {
				x++;
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=" + sess_id;
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(category).checked = true;			
				for (var j = 1; j < rmarkers.length; j++) {
					var catid = category + j;
					if($(catid)) {
						$(catid).style.display = "";
						}
					if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {rmarkers[j].addTo(map);}
					}
				}
			}
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];				
			if ($(category).checked == false) {
				y++;
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=" + sess_id;
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(category).checked = false;
				var y=0;
				for (var j = i; j < rmarkers.length; j++) {
					var catid = category + j;
					if($(catid)) {
						$(catid).style.display = "none";
						}
					if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {
						map.removeLayer(rmarkers[j]);
						}
					}
				}	
			}
		}
		$('go_button').style.display = 'none';
		$('can_button').style.display = 'none';
		if((x > 0) && (x < curr_cats.length)) {
			$('RESP_ALL').style.display = 'inline';
			$('RESP_ALL_BUTTON').style.display = 'inline';
			$('RESP_NONE').style.display = '';
			$('RESP_NONE_BUTTON').style.display = 'inline';
			}
		if(x == 0) {
			$('RESP_ALL').style.display = 'inline';
			$('RESP_ALL_BUTTON').style.display = 'inline';
			$('RESP_NONE').style.display = 'none';
			$('RESP_NONE_BUTTON').style.display = 'none';
			}
		if(x == curr_cats.length) {
			$('RESP_ALL').style.display = 'none';
			$('RESP_ALL_BUTTON').style.display = 'none';
			$('RESP_NONE').style.display = 'inline';
			$('RESP_NONE_BUTTON').style.display = 'inline';
			}
	var resptbl = document.getElementById('respondertable');
	if(resptbl) {
		if($('screenname').innerHTML == "responders") {responderlist2_setwidths();} else {responderlist_setwidths();}
		}
	}	// end function do_go_button()

function gb_handleResult(req) {							// 12/03/10	The persist callback function
	}

// Facilities show / hide functions		

function set_fac_categories() {
	var factbl = document.getElementById('facilitiestable');
	if(factbl) {
		var headerRow = factbl.rows[0];
		var tableRow = factbl.rows[1];
		if((!window.fcell1) || (window.fcell1 == 0)) {window.fcell1 = tableRow.cells[0].offsetWidth;}
		if((!window.fcell2) || (window.fcell2 == 0)) {window.fcell2 = tableRow.cells[1].offsetWidth;}
		if((!window.fcell3) || (window.fcell3 == 0)) {window.fcell3 = tableRow.cells[2].offsetWidth;}
		if((!window.fcell4) || (window.fcell4 == 0)) {window.fcell4 = tableRow.cells[3].offsetWidth;}
		if((!window.fcell5) || (window.fcell5 == 0)) {window.fcell5 = tableRow.cells[4].offsetWidth;}
		if((!window.fcell6) || (window.fcell6 == 0)) {window.fcell6 = tableRow.cells[5].offsetWidth;}
		}
	if($('screenname').innerHTML == 'facilities') {return; }
	if(fac_hidden!=0) {
		$('fac_ALL').style.display = '';
		$('fac_ALL_BUTTON').style.display = '';
		$('fac_ALL').checked = false;	
		} else {			
		$('fac_ALL').style.display = 'none';
		$('fac_ALL_BUTTON').style.display = 'none';
		$('fac_ALL').checked = false;
		}
	if(fac_shown!=0) {
		$('fac_NONE').style.display = '';
		$('fac_NONE_BUTTON').style.display = '';
		$('fac_NONE').checked = false;
		} else {
		$('fac_NONE').style.display = 'none';
		$('fac_NONE_BUTTON').style.display = 'none';
		$('fac_NONE').checked = false;
		}
	for (var i = 0; i < fac_curr_cats.length; i++) {
		var fac_catname = fac_curr_cats[i];
		if(fac_cat_sess_stat[i]=="s") {
			for (var j = 0; j < fmarkers.length; j++) {
				if((fmarkers[j]) && (fmarkers[j].category == fac_catname)) {
					fmarkers[j].addTo(map);		
					var fac_catid = fac_catname + j;
					if($(fac_catid)) {
						$(fac_catid).style.display = "";
						}
					}
				}
			$(fac_catname).checked = true;
			} else {
			for (var j = 0; j < fmarkers.length; j++) {
				if((fmarkers[j]) && (fmarkers[j].category == fac_catname)) {
					map.removeLayer(fmarkers[j]);		
					var fac_catid = fac_catname + j;
					if($(fac_catid)) {
						$(fac_catid).style.display = "none";
						}
					}
				}
			$(fac_catname).checked = false;
			}				
		}
	if(factbl) {
		facilitylist_setwidths();
		}
	}

function do_view_fac_cats() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
	$('fac_go_can').style.display = 'inline';
	$('fac_can_button').style.display = 'inline';
	$('fac_go_button').style.display = 'inline';
	}

function fac_cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
	$('fac_go_can').style.display = 'none';
	$('fac_can_button').style.display = 'none';
	$('fac_go_button').style.display = 'none';
	$('fac_ALL').checked = false;
	$('fac_NONE').checked = false;
	set_fac_categories();
	}

function set_fac_chkbox(control) {
	var fac_control = control;
	if($(fac_control).checked == true) {
		$(fac_control).checked = true;
		} else {
		$(fac_control).checked = false;
		}
	do_view_fac_cats();
	}
	
function set_fac_buttons(theType) {
	if(theType == "category") {
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var category = fac_curr_cats[i];
			if ($(category).checked == true) {
				if($('fac_NONE').checked == true) {
					$('fac_NONE').checked = false;
					}
				}
			return true;
			}
		} else if (theType == "all") {
		if($('fac_ALL').checked == true) {
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var category = fac_curr_cats[i];				
				$(category).checked = true;
				}
			return true;
			}
		} else if (theType == "none") {
		if($('fac_NONE').checked == true) {
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var category = fac_curr_cats[i];				
				$(category).checked = false;
				}
			return true;
			}
		}
	}

function do_go_facilities_button() {							// 12/03/10	Show Hide categories
	var factbl = document.getElementById('facilitiestable');
	if ($('fac_ALL').checked == true) {
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_category = fac_curr_cats[i];
			var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=" + sess_id;
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gb_handleResult, params);
			$(fac_category).checked = true;		
			for (var j = 0; j < fmarkers.length; j++) {
				var fac_catid = fac_category + j;
				if($(fac_catid)) {
					$(fac_catid).style.display = "";
					}
				if ((fmarkers[j]) && (fmarkers[j].category) && (fmarkers[j].category == fac_category)) {			
					fmarkers[j].addTo(map);		
					}
				}
			}
			$('fac_ALL').checked = false;
			$('fac_ALL').style.display = 'none';
			$('fac_ALL_BUTTON').style.display = 'none';				
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';				
			$('fac_go_button').style.display = 'none';
			$('fac_can_button').style.display = 'none';

		} else if ($('fac_NONE').checked == true) {
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_category = fac_curr_cats[i];
			var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=" + sess_id;
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gb_handleResult, params);	
			$(fac_category).checked = false;				
			for (var j = 0; j < fmarkers.length; j++) {
				var fac_catid = fac_category + j;
				if($(fac_catid)) {
					$(fac_catid).style.display = "none";
				}
				if ((fmarkers[j]) && (fmarkers[j].category) && (fmarkers[j].category == fac_category)) {			
					map.removeLayer(fmarkers[j]);		
					}
				}
			}
			$('fac_NONE').checked = false;
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';				
			$('fac_NONE').style.display = 'none';
			$('fac_NONE_BUTTON').style.display = 'none';					
			$('fac_go_button').style.display = 'none';
			$('fac_can_button').style.display = 'none';
		} else {
		var x = 0;
		var y = 0;
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_category = fac_curr_cats[i];
			if ($(fac_category).checked == true) {
				x++;
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=" + sess_id;
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(fac_category).checked = true;			
				for (var j = 0; j < fmarkers.length; j++) {
					var fac_catid = fac_category + j;
					if($(fac_catid)) {
						$(fac_catid).style.display = "";
						}
					if ((fmarkers[j]) && (fmarkers[j].category) && (fmarkers[j].category == fac_category)) {			
						fmarkers[j].addTo(map);		
						}
					}
				}
			}
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_category = fac_curr_cats[i];				
			if ($(fac_category).checked == false) {
				y++;
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=" + sess_id;
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(fac_category).checked = false;
				var y=0;
				for (var j = 0; j < fmarkers.length; j++) {
					var fac_catid = fac_category + j;

					if($(fac_catid)) {
						$(fac_catid).style.display = "none";
						}
					if ((fmarkers[j]) && (fmarkers[j].category) && (fmarkers[j].category == fac_category)) {			
						map.removeLayer(fmarkers[j]);		
						}
					}
				}	
			}
		}
	$('fac_go_button').style.display = 'none';
	$('fac_can_button').style.display = 'none';
	if((x > 0) && (x < fac_curr_cats.length)) {
		$('fac_ALL').style.display = '';
		$('fac_ALL_BUTTON').style.display = '';
		$('fac_NONE').style.display = '';
		$('fac_NONE_BUTTON').style.display = '';
		}
	if(x == 0) {
		$('fac_ALL').style.display = '';
		$('fac_ALL_BUTTON').style.display = '';
		$('fac_NONE').style.display = 'none';
		$('fac_NONE_BUTTON').style.display = 'none';
		}
	if(x == fac_curr_cats.length) {
		$('fac_ALL').style.display = 'none';
		$('fac_ALL_BUTTON').style.display = 'none';
		$('fac_NONE').style.display = '';
		$('fac_NONE_BUTTON').style.display = '';
		}
	if(factbl) {
		facilitylist_setwidths();
		}
	}	// end function do_go_button()

function gfb_handleResult(req) {							// 12/03/10	The persist callback function
	}

// end of facilities show / hide functions

// show hide polygons
function do_view_bnd() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
	$('bnd_go_can').style.display = 'inline';
	$('bnd_can_button').style.display = 'inline';
	$('bnd_go_button').style.display = 'inline';
	}

function bnd_cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
	$('bnd_go_can').style.display = 'none';
	$('bnd_can_button').style.display = 'none';
	$('bnd_go_button').style.display = 'none';
	$('BND_ALL').checked = false;
	$('BND_NONE').checked = false;
	}

function set_bnd_chkbox(control) {
	var bnd_control = control;
	if($(bnd_control).checked == true) {
		$(bnd_control).checked = false;
		} else {
		$(bnd_control).checked = true;
		}
	do_view_bnd();
	}

function do_go_bnd_button() {							// 12/03/10	Show Hide categories
	var bnd_curr = bound_names;
	if ($('BND_ALL').checked == true) {
		for (var key in boundary) {
			var bnds = bnd_curr[key];
			var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=s&sess_id=" + sess_id;
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gbb_handleResult, params);
			$(bnds).checked = true;	
			if(bound_names[key]) {				
				boundary[key].addTo(map);		
				}
			$('BND_ALL').checked = false;
			$('BND_ALL').style.display = 'none';
			$('BND_ALL_BUTTON').style.display = 'none';				
			$('BND_NONE').style.display = '';
			$('BND_NONE_BUTTON').style.display = '';				
			$('bnd_go_button').style.display = 'none';
			$('bnd_can_button').style.display = 'none';
			}
	} else if ($('BND_NONE').checked == true) {
		for (var key in boundary) {
			var bnds = bnd_curr[key];
			var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=h&sess_id=" + sess_id;
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gbb_handleResult, params);
			$(bnds).checked = false;
			if(bound_names[key]) {
				map.removeLayer(boundary[key]);		
				}
			$('BND_NONE').checked = false;
			$('BND_ALL').style.display = '';
			$('BND_ALL_BUTTON').style.display = '';				
			$('BND_NONE').style.display = 'none';
			$('BND_NONE_BUTTON').style.display = 'none';					
			$('bnd_go_button').style.display = 'none';
			$('bnd_can_button').style.display = 'none';
			}
	} else {
		var x = 0;
		var y = 0;
		for (var key in boundary) {
			var bnds = bnd_curr[key];
			if ($(bnds) && ($(bnds).checked == true)) {
				x++;
				var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=s&sess_id=" + sess_id;
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gbb_handleResult, params);
				$(bnds).checked = true;		
				if(bound_names[key]) {			
					boundary[key].addTo(map);		
					}
				}
			}
		for (var key in boundary) {
			var bnds = bnd_curr[key];
			if ($(bnds) && ($(bnds).checked == false)) {
				y++;
				var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=h&sess_id=" + sess_id;
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gbb_handleResult, params);
				$(bnds).checked = false;
				if(bound_names[key]) {
					map.removeLayer(boundary[key]);	
					}
				}
			}	
		}
	$('bnd_go_button').style.display = 'none';
	$('bnd_can_button').style.display = 'none';
	if((x > 0) && (x < bnd_curr.length)) {
		$('BND_ALL').style.display = '';
		$('BND_ALL_BUTTON').style.display = '';
		$('BND_NONE').style.display = '';
		$('BND_NONE_BUTTON').style.display = '';
	}
	if(x == 0) {
		$('BND_ALL').style.display = '';
		$('BND_ALL_BUTTON').style.display = '';
		$('BND_NONE').style.display = 'none';
		$('BND_NONE_BUTTON').style.display = 'none';
	}
	if(x == bnd_curr.length-1) {
		$('BND_ALL').style.display = 'none';
		$('BND_ALL_BUTTON').style.display = 'none';
		$('BND_NONE').style.display = '';
		$('BND_NONE_BUTTON').style.display = '';
	}

}	// end function do_go_bnd_button()

function set_bnds() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show, revised 3/15/11.
	for (var key in bnd_curr) {
		var bnds = bnd_curr[key];
		var bnd_nm = bnd_names_curr[key];
		if(bnds == "s") {
			if(bnd_nm) {$(bnd_nm).checked = true;}
			if(window.boundary[key]) {window.boundary[key].addTo(map);}
			} else {
			if(window.boundary[key]) {map.removeLayer(boundary[key]);}				
			if(bnd_nm) {$(bnd_nm).checked = false;}
			}				
		if(bnd_hidden!=0) {
			if($('BND_ALL')) { $('BND_ALL').style.display = '';}
			if($('BND_ALL_BUTTON')) { $('BND_ALL_BUTTON').style.display = '';}
			if($('BND_ALL')) { $('BND_ALL').checked = false;}	
			} else {			
			if($('BND_ALL')) { $('BND_ALL').style.display = 'none';}
			if($('BND_ALL_BUTTON')) { $('BND_ALL_BUTTON').style.display = 'none';}
			if($('BND_ALL')) { $('BND_ALL').checked = false;}
			}
		if(bnd_shown!=0) {
			if($('BND_NONE')) { $('BND_NONE').style.display = '';}
			if($('BND_NONE_BUTTON')) { $('BND_NONE_BUTTON').style.display = '';}
			if($('BND_NONE')) { $('BND_NONE').checked = false;}
			} else {
			if($('BND_NONE')) { $('BND_NONE').style.display = 'none';}
			if($('BND_NONE_BUTTON')) { $('BND_NONE_BUTTON').style.display = 'none';}
			if($('BND_NONE')) { $('BND_NONE').checked = false;}
			}
		}
	}
	
function gbb_handleResult(req) {							// 12/03/10	The persist callback function
	}

// end of functions for showing and hiding boundaries
var show_cont;
var hide_cont;	
var divarea;	

function hideDiv(div_area, hide_cont, show_cont) {	//	3/15/11
	if (div_area == "buttons_sh") {
		var controlarea = "hide_controls";
		}
	if (div_area == "resp_list_sh") {
		var controlarea = "resp_list";
		}
	if (div_area == "facs_list_sh") {
		var controlarea = "facs_list";
		}
	if (div_area == "incs_list_sh") {
		var controlarea = "incs_list";
		}
	if (div_area == "region_boxes") {
		var controlarea = "region_boxes";
		}
	if (div_area == "ticketlist") {
		var controlarea = "ticketlist";
		}
	if (div_area == "responderlist") {
		var controlarea = "responderlist";
		}
	if (div_area == "facilitylist") {
		var controlarea = "facilitylist";
		}
	if (div_area == "loglist") {
		var controlarea = "loglist";
		}
	var divarea = div_area 
	var hide_cont = hide_cont 
	var show_cont = show_cont 
	if($(divarea)) {
		$(divarea).style.display = 'none';
		$(hide_cont).style.display = 'none';
		$(show_cont).style.display = '';
		}
	if(typeof(controlarea) !== 'undefined') {
		var params = "f_n=" +controlarea+ "&v_n=h&sess_id=" + sess_id;
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);
		}		
	} 

function showDiv(div_area, hide_cont, show_cont) {	//	3/15/11
	if (div_area == "buttons_sh") {
		var controlarea = "hide_controls";
		}
	if (div_area == "resp_list_sh") {
		var controlarea = "resp_list";
		}
	if (div_area == "facs_list_sh") {
		var controlarea = "facs_list";
		}
	if (div_area == "incs_list_sh") {
		var controlarea = "incs_list";
		}
	if (div_area == "region_boxes") {
		var controlarea = "region_boxes";
		}
	if (div_area == "ticketlist") {
		var controlarea = "ticketlist";
		}
	if (div_area == "responderlist") {
		var controlarea = "responderlist";
		}
	if (div_area == "facilitylist") {
		var controlarea = "facilitylist";
		}
	if (div_area == "loglist") {
		var controlarea = "loglist";
		}		
	var divarea = div_area
	var hide_cont = hide_cont 
	var show_cont = show_cont 
	if($(divarea)) {
		$(divarea).style.display = '';
		$(hide_cont).style.display = '';
		$(show_cont).style.display = 'none';
		}
	if(typeof(controlarea) !== 'undefined') {
		var params = "f_n=" +controlarea+ "&v_n=s&sess_id=" + sess_id;
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);
		}		
	} 

function show_All() {						// 8/7/09 Revised function to correct incorrect display, 12/03/10, revised to remove units show and hide from this function
	for (var i = 0; i < gmarkers.length; i++) {
		if (gmarkers[i]) {
			if (gmarkers[i].category == "Incident") {
			gmarkers[i].addTo(map)		
			}
			}
		} 	// end for ()
	$("show_all_icon").style.display = "none";
	$("incidents").style.display = "inline-block";
	}			// end function

function show_btns_closed() {						// 4/30/10
	$('btn_go').style.display = 'inline';
	$('btn_can').style.display = 'inline';
	}
function hide_btns_closed() {
	$('btn_go').style.display = 'none';
	$('btn_can').style.display = 'none';
	if($('frm_interval')) {
		$('frm_interval').options[0].selected = true;
		}
	if($('period_select')) {
		$('period_select').options[0].selected = true;
		}
	}

function show_btns_scheduled() {						// 4/30/10
	$('btn_scheduled').style.display = 'inline';
	$('btn_can').style.display = 'inline';
	}
function hide_btns_scheduled() {
	$('btn_scheduled').style.display = 'none';
	$('btn_can').style.display = 'none';
	}
	
function do_print_ticket (id) {
	var url = "print_screen.php?ticket_id="+ id;
	var printWindow = window.open(url, 'printWindow', 'resizable=1, scrollbars, height=800, width=1000, left=100, top=100, screenX=100, screenY=100');
	close_context();
	printWindow.focus();
	}
	
function do_add_note (id) {
	var url = "add_note.php?ticket_id="+ id;
	var noteWindow = window.open(url, 'noteWindow', 'resizable=1, scrollbars, height=240, width=600, left=100, top=100, screenX=100, screenY=100');
	close_context();
	noteWindow.focus();
	}
	
function do_add_action (id) {
	var url = "action_w.php?ticket_id="+ id;
	var actionWindow = window.open(url, 'actWindow', 'resizable=1, scrollbars, height=800, width=800, left=100, top=100, screenX=100, screenY=100');
	close_context();
	actionWindow.focus();
	}
	
function do_edit_action (id) {
	var url = "action_w.php?id="+ id + "&action=edit";
	var actionWindow = window.open(url, 'actWindow', 'resizable=1, scrollbars, height=800, width=800, left=100, top=100, screenX=100, screenY=100');
	close_context();
	actionWindow.focus();
	}
	
function do_add_patient (id) {
	var url = "patient_w.php?ticket_id="+ id;
	var patientWindow = window.open(url, 'patWindow', 'resizable=1, scrollbars, height=240, width=600, left=100, top=100, screenX=100, screenY=100');
	close_context();
	patientWindow.focus();
	}

function do_aprs_window() {				// 6/25/08
	var url = "http://www.openaprs.net/?center=" + def_lat + "," + def_lng;
	var spec ="titlebar, resizable=1, scrollbars, height=640,width=640,status=0,toolbar=0,menubar=0,location=0, left=50,top=250,screenX=50,screenY=250";
	newwindow=window.open(url, 'openaprs',  spec);
	if (isNull(newwindow)) {
		alert ("APRS display requires popups to be enabled. Please adjust your browser options.");
		return;
		}
	newwindow.focus();
	}				// end function

function do_track(callsign) {
	if (parent.frames["upper"].logged_in()) {
		try  {open_iw.close()} catch (e) {;}
		var width = 1000;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "track_u.php?source="+callsign;
		newwindow=window.open(url, callsign,  spec);
		if (isNull(newwindow)) {
			alert ("Track display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}
	}				// end function
	
function do_tracks() {
	if (parent.frames["upper"].logged_in()) {
		try  {open_iw.close()} catch (e) {;}
		var width = 1000;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=100,screenX=100,screenY=100";
		var url = "tracks.php";
		newwindow=window.open(url, 'Unit Tracks',  spec);
		if (isNull(newwindow)) {
			alert ("Track display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}	
	}

function checkAll() {	//	9/10/13
	var theField = document.res_add_Form.elements["frm_group[]"];
	for (i = 0; i < theField.length; i++) {
		theField[i].checked = true ;
		}
	}

function uncheckAll() {	//	9/10/13
	var theField = document.res_add_Form.elements["frm_group[]"];
	for (i = 0; i < theField.length; i++) {
		theField[i].checked = false ;
		}
	}
	
function to_str(instr) {			// 0-based conversion - 2/13/09
	function ord( string ) {
		return (string+'').charCodeAt(0);
		}

	function chr( ascii ) {
		return String.fromCharCode(ascii);
		}
	function to_char(val) {
		return(chr(ord("A")+val));
		}

	var lop = (instr % 26);													// low-order portion, a number
	var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
	return hop+to_char(lop);
	}
	
function loc_lkup(my_form) {
	if(!$('map_canvas')) {return; }
	var theLat = my_form.frm_lat.value;
	var theLng = my_form.frm_lng.value;	
	var theCity = my_form.frm_city.value.trim();
	var theStreet = my_form.frm_street.value.trim();
	if (theCity == "" || my_form.frm_state.value.trim() == "") {
		alert ("City and State are required for location lookup.");
		return false;
		}
	if(theStreet != "" && theCity != "") {
		var myAddress = theStreet + ", " + theCity + " " + my_form.frm_state.value.trim();
		} else if(theStreet == "" && theCity != "") {
		var myAddress = theCity + " " + my_form.frm_state.value.trim();			
		}
	control.options.geocoder.geocode(myAddress, function(results) {
		if(!results[0]) {
			if(allow_nogeo == "1") {
				dummy_pt_to_map(my_form);
				} else {
				if(!confirm("Could not find location from address. Do you want to use default location?")) {
					alert("Please try inputting the address in a different way");
					return false;
					} else {
					dummy_pt_to_map(my_form);
					return;
					}
				}
			}
		var r = results[0]['center'];
		theLat = r.lat;
		theLng = r.lng;
		pt_to_map (my_form, theLat, theLng);
		if(my_form == document.add) { find_warnings(theLat, theLng);}
		});
	}				// end function loc_lkup()
	
function pt_to_map (my_form, lat, lng) {
	if(!$('map_canvas')) {return; }
	if(marker) {map.removeLayer(marker);}
	if(myMarker) {map.removeLayer(myMarker);}
	var theLat = parseFloat(lat).toFixed(6);
	var theLng = parseFloat(lng).toFixed(6);
	my_form.frm_lat.value=theLat;	
	my_form.frm_lng.value=theLng;		
	my_form.show_lat.value=do_lat_fmt(theLat);
	my_form.show_lng.value=do_lng_fmt(theLng);	
	if(locale == 0) { my_form.frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
	if(locale == 1) { my_form.frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
	if(locale == 2) { my_form.frm_ngs.value=LLtoUTM(theLat, theLng, 5); }
	if(document.wiz_add) {
		document.wiz_add.wiz_show_lat.value=do_lat_fmt(theLat);
		document.wiz_add.wiz_show_lng.value=do_lng_fmt(theLng);	
		if(locale == 0) { document.wiz_add.wiz_frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
		if(locale == 1) { document.wiz_add.wiz_frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
		if(locale == 2) { document.wiz_add.wiz_frm_ngs.value=LLtoUTM(theLat, theLng, 5); }		
		}
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
	marker = L.marker([theLat, theLng], {icon: icon});
	marker.addTo(map);
	map.setView([theLat, theLng], 16);
	}				// end function pt_to_map ()
	
function dummy_pt_to_map(my_form) {
	if(!$('map_canvas')) {return; }
	if(marker) {map.removeLayer(marker);}
	if(myMarker) {map.removeLayer(myMarker);}
	var theLat = def_lat;
	var theLng = def_lng;
	my_form.frm_lat.value=theLat;	
	my_form.frm_lng.value=theLng;		
	my_form.show_lat.value=do_lat_fmt(theLat);
	my_form.show_lng.value=do_lng_fmt(theLng);	
	if(locale == 0) { my_form.frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
	if(locale == 1) { my_form.frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
	if(locale == 2) { my_form.frm_ngs.value=LLtoUTM(theLat, theLng, 5); }
	if(document.wiz_add) {
		document.wiz_add.wiz_show_lat.value=do_lat_fmt(theLat);
		document.wiz_add.wiz_show_lng.value=do_lng_fmt(theLng);	
		if(locale == 0) { document.wiz_add.wiz_frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
		if(locale == 1) { document.wiz_add.wiz_frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
		if(locale == 2) { document.wiz_add.wiz_frm_ngs.value=LLtoUTM(theLat, theLng, 5); }		
		}
	var iconurl = "./our_icons/question1.png";
	icon = new baseIcon({iconUrl: iconurl});	
	marker = L.marker([def_lat, def_lng], {icon: icon});
	marker.addTo(map);
	map.setView([def_lat, def_lng], 16);	
	}

function newGetAddress(latlng, currform) {
	var popup = L.popup();
	control.options.geocoder.reverse(latlng, 20, function(results) {
		if(!results) {alert("Try again"); return;}
		if(window.geo_provider == 0){
			var r1 = results[0]; 
			var r = r1['properties']['address'];
			if(r.neighbourhood && r.neighbourhood != "") {
				r.city = r.neighbourhood;
				} else if(r.suburb && r.suburb != "") {
				r.city = r.suburb;
				} else if(r.town && r.town != "") {
				r.city = r.town;
				}
			} else if(window.geo_provider == 1) {
			var r = results[0].properties.address;
			if(!r.city) {
				if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
					theCity = r.locality;
					} else {
					theCity = "";
					}
				}
			} else if(window.geo_provider == 2) {
			var r = results[0]; 
			if(!r.city) {
				if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
					theCity = r.locality;
					} else {
					theCity = "";
					}
				}
			}
		var lat = parseFloat(latlng.lat.toFixed(6));
		var lng = parseFloat(latlng.lng.toFixed(6));
		var theCity = r.city;
		if(!r.state) {
			if(r.county) {
				var state = r.county;
				} else {
				var state = "";
				}
			} else {
			var state = r.state;
			}
		if(!theCity) {
			var theCity = "";
			}
		var ausStates = ['New South Wales','Queensland','NSW','QLD','Northern Territory','Western Australia','South Australia','Victoria','Tasmania'];	//	Australian State full names in array
		var ausStatesAbb = ['NSW','QLD','NSW','QLD','NT','WA','SA','Vic','Tas'];	//	Australian State abbreviations in array
		var auskey = ausStates.indexOf(state);	//	Checks if current reported state is an Australian one.
		if(auskey != -1) {state = ausStatesAbb[auskey];}	//	if State is Australian, converts full name to abbreviation.
		if (r) {
			switch(currform) {
				case "a":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.res_add_Form.frm_street.value = theStreet1 + theStreet2;
					document.res_add_Form.frm_city.value = theCity;
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					document.res_add_Form.frm_state.value = state;
					document.res_add_Form.frm_lat.value = lat; 
					document.res_add_Form.frm_lng.value = lng; 
					document.res_add_Form.show_lat.value = lat; 
					document.res_add_Form.show_lng.value = lng;
					if(locale == 0) { document.res_add_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(locale == 1) { document.res_add_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(locale == 2) { document.res_add_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.res_add_Form.frm_street.focus();	
					break;

				case "e":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.res_edit_Form.frm_street.value = theStreet1 + theStreet2;
					document.res_edit_Form.frm_city.value = theCity;
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					document.res_edit_Form.frm_state.value = state;
					document.res_edit_Form.frm_lat.value = lat; 
					document.res_edit_Form.frm_lng.value = lng; 
					document.res_edit_Form.show_lat.value = lat; 
					document.res_edit_Form.show_lng.value = lng;
					if(locale == 0) { document.res_edit_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(locale == 1) { document.res_edit_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(locale == 2) { document.res_edit_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.res_edit_Form.frm_street.focus();					
					break;
					
				case "wa":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.loc_add_Form.frm_street.value = theStreet1 + theStreet2;
					document.loc_add_Form.frm_city.value = theCity;
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					document.loc_add_Form.frm_state.value = state;
					document.loc_add_Form.frm_lat.value = lat; 
					document.loc_add_Form.frm_lng.value = lng; 
					document.loc_add_Form.show_lat.value = lat; 
					document.loc_add_Form.show_lng.value = lng;
					if(locale == 0) { document.loc_add_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(locale == 1) { document.loc_add_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(locale == 2) { document.loc_add_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.loc_add_Form.frm_street.focus();	
					break;

				case "we":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.loc_edit_Form.frm_street.value = theStreet1 + theStreet2;
					document.loc_edit_Form.frm_city.value = theCity;
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					document.loc_edit_Form.frm_state.value = state;
					document.loc_edit_Form.frm_lat.value = lat; 
					document.loc_edit_Form.frm_lng.value = lng; 
					document.loc_edit_Form.show_lat.value = lat; 
					document.loc_edit_Form.show_lng.value = lng;
					if(locale == 0) { document.loc_edit_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(locale == 1) { document.loc_edit_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(locale == 2) { document.loc_edit_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.loc_edit_Form.frm_street.focus();					
					break;
					
				case "c":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street + ", " : "";
					var address3 = (theCity != "") ? theCity + ", " : "";
					var address4 = (state != "") ? state : "";					
					document.c.frm_address.value = address1 + address2 + address3 + address4;
					document.c.frm_lat.value = lat; 
					document.c.frm_lng.value = lng; 
					document.c.frm_address.focus();
					break;
					
				case "u":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street + ", " : "";
					var address3 = (theCity != "") ? theCity + ", " : "";
					var address4 = (state != "") ? state : "";					
					document.u.frm_address.value = address1 + address2 + address3 + address4;
					document.u.frm_lat.value = lat; 
					document.u.frm_lng.value = lng; 
					document.u.frm_address.focus();
					break;
					
				case "ni":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street : "";
					document.add.frm_street.value = address1 + address2;
					document.add.frm_city.value = theCity;
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					if(r.properState) { state = r.properState;}
					document.add.frm_state.value = state;
					document.add.frm_lat.value = lat; 
					document.add.frm_lng.value = lng; 
					document.add.show_lat.value = lat; 
					document.add.show_lng.value = lng; 
					document.add.frm_street.focus();
					if(locale == 0) { document.add.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(locale == 1) { document.add.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(locale == 2) { document.add.frm_ngs.value=LLtoUTM(lat, lng, 5); }
					break;
					
				case "ei":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street : "";
					document.edit.frm_street.value = address1 + address2;
					document.edit.frm_city.value = theCity;
					if(locale == 0) {
						state = (state != "" && state.length > 2) ? states_arr[state] : state;
						}
					if(locale == 1) {state = "UK";}
					document.edit.frm_state.value = state;
					document.edit.frm_lat.value = lat; 
					document.edit.frm_lng.value = lng; 
					document.edit.show_lat.value = lat; 
					document.edit.show_lng.value = lng; 
					document.edit.frm_street.focus();
					if(locale == 0) { document.edit.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(locale == 1) { document.edit.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(locale == 2) { document.edit.frm_ngs.value=LLtoUTM(lat, lng, 5); }							
					break;
					
				default:
					alert ("596: error");
				}		// end switch()
			var theContent1 = (number != "") ? number + " ": "";
			var theContent2 = (street != "") ? street + ", ": "";
			var theContent3 = (theCity != "") ? theCity + ", ": "";
			var theContent4 = (state != "") ? state + ", ": "";
			var theContent = theContent1 + theContent2 + theContent3 + theContent4;
			popup
				.setLatLng(latlng)
				.setContent(theContent)
				.openOn(map);
			}
		});
	}
	
function getTheAddress(latlng) {
	control.options.geocoder.reverse(latlng, 20, function(results) {
		var r = results[0];
		var lat = parseFloat(latlng.lat.toFixed(6));
		var lng = parseFloat(latlng.lng.toFixed(6));
		var theCity = "";
		if(!r.city) {
			if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
				theCity = r.locality;
				} else {
				theCity = "";
				}
			} else {
			theCity = r.city;
			}
		if(!r.state) {
			if(r.county) {
				var state = r.county;
				} else {
				var state = "";
				}
			} else {
			var state = r.state;
			}
		if (r) {
			var street = (r.road) ? r.road : "";
			var number = (r.house_number) ? r.house_number : "";
			var address1 = (number != "") ? number + " " : "";
			var address2 = (street != "") ? street : "";
			document.add.frm_street.value = address1 + address2;
			document.add.frm_city.value = theCity;
			var theState = (state != "") ? states_arr[state] : "";
			document.add.frm_state.value = theState;
			document.add.frm_lat.value = lat; 
			document.add.frm_lng.value = lng; 
			document.add.show_lat.value = lat; 
			document.add.show_lng.value = lng; 
			document.add.frm_street.focus();
			if(locale == 0) { document.add.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
			if(locale == 1) { document.add.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
			if(locale == 2) { document.add.frm_ngs.value=LLtoUTM(lat, lng, 5); }
			}
		});
	}

function do_usng_conv(theForm){						// usng to LL array			- 12/4/08
	tolatlng = new Array();
	USNGtoLL(theForm.frm_ngs.value, tolatlng);
	var point = new L.LatLng(tolatlng[0].toFixed(2) ,tolatlng[1].toFixed(2));
	map.setCenter(point, def_zoom);
	var iconurl = "./markers/crosshair.png";		
	var icon = new baseIcon({iconUrl: iconurl});	
	var marker = L.marker(point, {icon: icon});
	theForm.frm_lat.value = point.lat(); theForm.frm_lng.value = point.lng();
	do_lat (point.lat());
	do_lng (point.lng());
	do_ngs(theForm);
	domap();			// show it
	}				// end function

function do_unlock_pos(theForm) {				// 12/20/08
	theForm.frm_ngs.disabled=false;
	$("lock_p").style.visibility = "hidden";
	if($("usng_link")) {$("usng_link").style.textDecoration = "underline";}
	if($("osgb_link")) {$("osgb_link").style.textDecoration = "underline";}	
	if($("utm_link")) {$("utm_link").style.textDecoration = "underline";}			
	}

function do_coords(inlat, inlng) {
	inlat = inlat.toString();
	inlng = inlng.toString();
	if(inlat.toString().length==0) return;
	var str = "Position Data\n\n\n" + inlat + ", " + inlng + "\n";
	str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
	str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
	return str;
	}

function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
	var d = new Number(inval);
	d  = (inval>0)?  Math.floor(d):Math.round(d);
	var mi = (inval-d)*60;
	var m = Math.floor(mi)				// min's
	var si = (mi-m)*60;
	var s = si.toFixed(1);
	return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
	}

function lat2ddm(inlat) {				// lat to degr, dec min's  9/7/08
	var x = new Number(inlat);
	var y  = (inlat>0)?  Math.floor(x):Math.round(x);
	var z = ((Math.abs(x-y)*60).toFixed(1));
	var nors = (inlat>0.0)? " N":" S";
	return Math.abs(y) + '\260 ' + z +"'" + nors;
	}

function lng2ddm(inlng) {				// lng to degr, dec min's
	var x = new Number(inlng);
	var y  = (inlng>0)?  Math.floor(x):Math.round(x);
	var z = ((Math.abs(x-y)*60).toFixed(1));
	var eorw = (inlng>0.0)? " E":" W";
	return Math.abs(y) + '\260 ' + z +"'" + eorw;
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
		alert ("invalid LL format selector");
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
		alert ("invalid LL format selector");
		}
	}	

function test(location) {
	alert(location);
	}

function createcrossMarker(lat, lon) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconurl = "./markers/crosshair.png";		
		icon = new basecrossIcon({iconUrl: iconurl});	
		marker = L.marker([lat, lon], {icon: icon});
		marker.addTo(map);
		}
	}
	
function createstdMarker(lat, lon) {
	if(marker) { map.removeLayer(marker); }
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconurl = "./our_icons/yellow.png";
		icon = new baseIcon({iconUrl: iconurl});	
		marker = L.marker([lat, lon], {icon: icon});
		marker.addTo(map);
		}
	}
	
function createmmMarker(lat, lon) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconurl = "./our_icons/yellow.png";
		icon = new baseIcon({iconUrl: iconurl});	
		marker = L.marker([lat, lon], {icon: icon});
		marker.addTo(minimap);
		}
	}

function createMarker(lat, lon, info, color, stat, theid, sym, category, region, tip) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconStr = sym;
		var iconurl = "./our_icons/gen_icon.php?blank=" + escape(window.icons[color]) + "&text=" + iconStr;	
		icon = new baseIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip, zIndexOffset: window.inczindexno, riseOnHover: true, riseOffset: 30000});
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			if($('screenname').innerHTML == "fullscreen") {
				get_fs_tickpopup(theid);
				} else if($('screenname').innerHTML == "popup") {
				get_fs_tickpopup(theid);	
				} else {
				get_tickpopup(theid);
				}
			});	
		marker.id = color;
		marker.category = category;
		marker.region = region;		
		marker.stat = stat;
		tmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		tmarkers[theid].latlng = point;
		if(my_Local == "1" && (theBounds instanceof Array)) {
			var southWest = L.latLng(theBounds[3], theBounds[0]);
			var northEast = L.latLng(theBounds[1], theBounds[2]);
			var maxBounds = L.latLngBounds(southWest, northEast);
			if(maxBounds.contains(point)) {
				bounds.extend(point);
				}
			} else {
			bounds.extend(point);				
			}
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "fullscreen") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			}
		window.inczindexno++;
		return marker;
		} else {
		return false;
		}
	}

function createUnitMarker(lat, lon, info, color, stat, theid, sym, category, region, tip, type) {
	if((isFloat(lat)) && (isFloat(lon))) {
		if(!sym) { sym = "UNK"; }
		var origin = ((sym.length)>3)? (sym.length)-3: 0;
		var iconStr = sym.substring(origin);
		if(($('screenname')) && ($('screenname').innerHTML == 'responders')){
			var theIconColor = parseInt(type);
			} else if(($('screenname')) && ($('screenname').innerHTML == 'situation')) {
			var theIconColor = escape(window.unit_icons[color]);
			} else {
			var theIconColor = parseInt(type);
			}
		var iconurl = "./our_icons/gen_icon.php?blank=" + theIconColor + "&text=" + iconStr;
		icon = new baseIcon({iconUrl: iconurl});
		var marker = L.marker([lat, lon], {icon: icon, title: tip, zIndexOffset: window.unitzindexno, riseOnHover: true, riseOffset: 30000});
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			if(infoPopups[theid]) {
				do_indv_assignment_flag(theid);
				}
			});
		marker.on('click', function(e) {
			if($('screenname').innerHTML == "fullscreen") {
				get_fs_resppopup(theid);
				} else {
				get_resppopup(theid);
				}
			});
		marker.on('mouseover', function(e) {
			marker.setZIndexOffset("20000");
			});
		marker.id = color;
		marker.category = category;
		marker.region = region;		
		marker.stat = stat;
		rmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		rmarkers[theid].latlng = point;
		rmarkers[theid].infopopup = info;
		if(my_Local == "1" && (theBounds instanceof Array)) {
			var southWest = L.latLng(theBounds[3], theBounds[0]);
			var northEast = L.latLng(theBounds[1], theBounds[2]);
			var maxBounds = L.latLngBounds(southWest, northEast);
			if(maxBounds.contains(point)) {
				bounds.extend(point);
				}
			} else {
			bounds.extend(point);				
			}
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "fullscreen") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			}
		window.unitzindexno++;
		return marker;
		} else {
		return false;
		}
	}
	
function createFacilityMarker(lat, lon, info, color, stat, theid, sym, category, region, tip) {
	if((isFloat(lat)) && (isFloat(lon))) {
		if(!sym) { sym = "UNK"; }
		var origin = ((sym.length)>3)? (sym.length)-3: 0;
		var iconStr = sym.substring(origin);
		var iconurl = "./our_icons/gen_fac_icon.php?blank=" + color + "&text=" + iconStr;
		icon = new baseFacIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip, zIndexOffset: window.faczindexno, riseOnHover: true, riseOffset: 30000});
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			if($('screenname') && $('screenname').innerHTML == "fullscreen") {
				get_fs_facspopup(theid);
				} else {
				get_facspopup(theid);
				}
			});	
		marker.id = color;
		marker.category = category;
		marker.region = region;	
		marker.stat = stat;
		fmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		fmarkers[theid].latlng = point;		
		if(my_Local == "1" && (theBounds instanceof Array)) {
			var southWest = L.latLng(theBounds[3], theBounds[0]);
			var northEast = L.latLng(theBounds[1], theBounds[2]);
			var maxBounds = L.latLngBounds(southWest, northEast);
			if(maxBounds.contains(point)) {
				bounds.extend(point);
				}
			} else {
			bounds.extend(point);				
			}
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "fullscreen") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			} else {
			map.fitBounds(bounds);				
			}
		window.faczindexno++;
		return marker;
		} else {
		return false;
		}
	}
	
function createWlocationMarker(lat, lon, info, color, stat, theid, sym, category, region, tip) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconStr = sym;
		var iconurl = "./our_icons/gen_fac_icon.php?blank=" + escape(window.wl_icons[color]) + "&text=" + iconStr;
		icon = new baseFacIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip}).bindPopup(info).openPopup();
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.id = color;
		marker.category = category;
		marker.region = region;	
		marker.stat = stat;
		wlmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		wlmarkers[theid].latlng = point;			
		if(my_Local == "1" && (theBounds instanceof Array)) {
			var southWest = L.latLng(theBounds[3], theBounds[0]);
			var northEast = L.latLng(theBounds[1], theBounds[2]);
			var maxBounds = L.latLngBounds(southWest, northEast);
			if(maxBounds.contains(point)) {
				bounds.extend(point);
				}
			} else {
			bounds.extend(point);				
			}
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "fullscreen") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			}
		return marker;
		} else {
		return false;
		}
	}
	
function createWlocationMarkerSit(lat, lon, info, color, stat, theid, sym, category, region, tip) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconStr = sym;
		var iconurl = "./our_icons/info.png";
		icon = new baseFacIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon}).bindPopup(info).openPopup();
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.id = color;
		marker.category = category;
		marker.region = region;	
		marker.stat = stat;
		wlmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		wlmarkers[theid].latlng = point;			
		if(my_Local == "1" && (theBounds instanceof Array)) {
			var southWest = L.latLng(theBounds[3], theBounds[0]);
			var northEast = L.latLng(theBounds[1], theBounds[2]);
			var maxBounds = L.latLngBounds(southWest, northEast);
			if(maxBounds.contains(point)) {
				bounds.extend(point);
				}
			} else {
			bounds.extend(point);				
			}
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "fullscreen") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			}
		return marker;
		} else {
		return false;
		}
	}
	
function createConditionMarker(lat, lon, theid, info, category, image_file) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var icon = new baseSqIcon({iconUrl: image_file});	
		var cmarker = L.marker([lat, lon], {icon: icon}).bindPopup(info).openPopup();
		cmarker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
			
		cmarkers[theid] = cmarker;
		var point = new L.LatLng(lat, lon);
		cmarkers[theid].latlng = point;					
		cmarker.addTo(roadalerts);
		return cmarker;
		} else {
		return false;
		}
	}

function test(location) {
	alert(location);
	}

function createdummyMarker(lat, lon, info, icon, title){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		var marker = L.marker([lat, lon], {icon: image_file}).addTo(map);
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			if($('screenname').innerHTML == "fullscreen") {
				get_fs_tickpopup(theid);
				} else if($('screenname').innerHTML == "popup") {
				} else {
				get_tickpopup(theid);
				}
			});
		tmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		tmarkers[theid].latlng = point;
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyUnitMarker(lat, lon, info, icon, title, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		icon = new baseIcon({iconUrl: image_file});	
		var marker = L.marker([lat, lon], {icon: icon}).addTo(map);
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			if($('screenname').innerHTML == "fullscreen") {
				get_fs_resppopup(theid);
				} else {
				get_resppopup(theid);
				}
			});
		rmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		rmarkers[theid].latlng = point;
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyIncMarker(lat, lon, info, icon, title, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		icon = new baseIcon({iconUrl: image_file});	
		var marker = L.marker([lat, lon], {icon: icon}).addTo(map);
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			if($('screenname').innerHTML == "fullscreen") {
				get_fs_tickpopup(theid);
				} else if($('screenname').innerHTML == "popup") {
				} else {
				get_tickpopup(theid);
				}
			});
		tmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		tmarkers[theid].latlng = point;
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyFacMarker(lat, lon, info, icon, title, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		icon = new baseIcon({iconUrl: image_file});	
		var marker = L.marker([lat, lon], {icon: icon}).addTo(map);
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			if($('screenname') && $('screenname').innerHTML == "fullscreen") {
				get_fs_facspopup(theid);
				} else {
				get_facspopup(theid);
				}
			});
		fmarkers[theid] = marker;
		var point = new L.LatLng(lat, lon);
		fmarkers[theid].latlng = point;
		return marker;
		} else {
		return false;
		}
	}

function destroy_unitmarkers() {
	for(var key in rmarkers) {
		if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
		}
	}
	
function sendInfo(theText) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var theURL = base64_encode(theText);
	var url = './ajax/do_error.php?the_error=' + theURL + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,errCB, "");
	function errCB(req) {
		var theResponse = JSON.decode(req.responseText);
		theResult = theResponse[0];
		}
	}

function ajaxSafe(s) {
	return s.replace(/&(?!\w+([;\s]|$))/g, "&amp;")
	.replace(/</g, "&lt;").replace(/>/g, "&gt;");
	}

function log_debug(theText) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var theString = ajaxSafe(theText);
	var url = './ajax/write_debuglog.php?debugtxt=' + theString + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,errCB, "");
	function errCB(req) {
		var theResponse = JSON.decode(req.responseText);
		theResult = theResponse[0];
		if(theResult == 99) {
			alert("Can't create debug file - check file permissions on Tickets Directory");
			} else if(theResult == 0){
			alert("Can't write to debug file - check file permissions on Tickets Directory");	
			}
		}
	}

function get_tickpopup(id) {
	if(typeof(tmarkers) !== 'undefined') {
		for(var key in tmarkers) {
			if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
				} else {
				tmarkers[key].closePopup();
				}
			}
		}
	if(typeof(rmarkers) !== 'undefined') {
		for(var key in rmarkers) {
			if (rmarkers[key] && typeof(rmarkers[key]._popup)=='undefined') {
				} else {
				if(!theAssigned[key]) {
					rmarkers[key].closePopup();
					}
				}
			}
		}
	if(typeof(fmarkers) !== 'undefined') {
		for(var key in fmarkers) {
			if (fmarkers[key] && typeof(fmarkers[key]._popup)=='undefined') {
				} else {
				fmarkers[key].closePopup();
				}
			}
		}
	if(typeof(wlmarkers) !== 'undefined') {
		for(var key in wlmarkers) {
			if (wlmarkers[key] && typeof(wlmarkers[key]._popup)=='undefined') {
				} else {
				wlmarkers[key].closePopup();
				}
			}
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/inc_popup.php?id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(tmarkers[id].getLatLng());
			tmarkers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}
		
function get_resppopup(id) {
	if(typeof(rmarkers) !== 'undefined') {
		for(var key in rmarkers) {
			if (rmarkers[key] && typeof(rmarkers[key]._popup)=='undefined') {
				} else {
				if(!theAssigned[key]) {
					rmarkers[key].closePopup();
					}
				}
			if(theAssigned[id]) {
				rmarkers[id].closePopup();
				}
			}
		}
	if(typeof(tmarkers) !== 'undefined') {
		for(var key in tmarkers) {
			if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
				} else {
				tmarkers[key].closePopup();
				}
			}
		}
	if(typeof(fmarkers) !== 'undefined') {
		for(var key in fmarkers) {
			if (fmarkers[key] && typeof(fmarkers[key]._popup)=='undefined') {
				} else {
				fmarkers[key].closePopup();
				}
			}
		}
	if(typeof(wlmarkers) !== 'undefined') {
		for(var key in wlmarkers) {
			if (wlmarkers[key] && typeof(wlmarkers[key]._popup)=='undefined') {
				} else {
				wlmarkers[key].closePopup();
				}
			}
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/resp_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(rmarkers[id].getLatLng());
			rmarkers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}
		
function get_facspopup(id) {
	if(typeof(fmarkers) !== 'undefined') {
		for(var key in fmarkers) {
			if (fmarkers[key] && typeof(fmarkers[key]._popup)=='undefined') {
				} else {
				fmarkers[key].closePopup();
				}
			}
		}
	if(typeof(rmarkers) !== 'undefined') {
		for(var key in rmarkers) {
			if (rmarkers[key] && typeof(rmarkers[key]._popup)=='undefined') {
				} else {
				if(!theAssigned[key]) {
					rmarkers[key].closePopup();
					}
				}
			}
		}
	if(typeof(tmarkers) !== 'undefined') {
		for(var key in tmarkers) {
			if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
				} else {
				tmarkers[key].closePopup();
				}
			}
		}
	if(typeof(wlmarkers) !== 'undefined') {
		for(var key in wlmarkers) {
			if (wlmarkers[key] && typeof(wlmarkers[key]._popup)=='undefined') {
				} else {
				wlmarkers[key].closePopup();
				}
			}
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/facs_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(fmarkers[id].getLatLng());
			fmarkers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}
		
function get_fs_tickpopup(id) {
	for(var key in tmarkers) {
		if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
			} else {
			tmarkers[key].closePopup();
			}
		}
	if(typeof(rmarkers) !== 'undefined') {
		for(var key in rmarkers) {
			if (rmarkers[key] && typeof(rmarkers[key]._popup)=='undefined') {
				} else {
				if(!theAssigned[key]) {
					rmarkers[key].closePopup();
					}
				}
			}
		}
	if(typeof(tmarkers) !== 'undefined') {
		for(var key in tmarkers) {
			if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
				} else {
				tmarkers[key].closePopup();
				}
			}
		}
	if(typeof(wlmarkers) !== 'undefined') {
		for(var key in wlmarkers) {
			if (wlmarkers[key] && typeof(wlmarkers[key]._popup)=='undefined') {
				} else {
				wlmarkers[key].closePopup();
				}
			}
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/fs_inc_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(tmarkers[id].getLatLng());
			tmarkers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}
		
function get_fs_resppopup(id) {
	for(var key in tmarkers) {
		if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
			} else {
			tmarkers[key].closePopup();
			}
		}
	for(var key in rmarkers) {
		if (rmarkers[key] && typeof(rmarkers[key]._popup)=='undefined') {
			} else {
			rmarkers[key].closePopup();
			}
		}
	if(typeof(tmarkers) !== 'undefined') {
		for(var key in tmarkers) {
			if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
				} else {
				tmarkers[key].closePopup();
				}
			}
		}
	if(typeof(wlmarkers) !== 'undefined') {
		for(var key in wlmarkers) {
			if (wlmarkers[key] && typeof(wlmarkers[key]._popup)=='undefined') {
				} else {
				wlmarkers[key].closePopup();
				}
			}
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/fs_resp_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(rmarkers[id].getLatLng());
			rmarkers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}
		
function get_fs_facspopup(id) {
	for(var key in tmarkers) {
		if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
			} else {
			tmarkers[key].closePopup();
			}
		}
	for(var key in rmarkers) {
		if (rmarkers[key] && typeof(rmarkers[key]._popup)=='undefined') {
			} else {
			rmarkers[key].closePopup();
			}
		}
	if(typeof(tmarkers) !== 'undefined') {
		for(var key in tmarkers) {
			if (tmarkers[key] && typeof(tmarkers[key]._popup)=='undefined') {
				} else {
				tmarkers[key].closePopup();
				}
			}
		}
	if(typeof(wlmarkers) !== 'undefined') {
		for(var key in wlmarkers) {
			if (wlmarkers[key] && typeof(wlmarkers[key]._popup)=='undefined') {
				} else {
				wlmarkers[key].closePopup();
				}
			}
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/fs_facs_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(fmarkers[id].getLatLng());
			fmarkers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}
	
function mytclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	if((quick) || (!tmarkers[id]) || (internet == 0)) {
		document.tick_form.id.value=id;
		document.tick_form.action='edit.php';
		document.tick_form.submit();
		} else {
		if($('screenname').innerHTML == "fullscreen") {
			get_fs_tickpopup(id);
			} else {
			get_tickpopup(id);
			}
		}
	return false;
	}
	
function myrclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	if((quick) || (!rmarkers[id]) || (internet == 0)) {
		document.resp_form.id.value=id;
		document.resp_form.func.value='responder';
		document.resp_form.edit.value='false';
		document.resp_form.view.value='true';
		document.resp_form.action='units.php';
		document.resp_form.submit();
		} else if(guest) {
		document.resp_form.id.value=id;
		document.resp_form.func.value='responder';
		document.resp_form.view.value='true';
		document.resp_form.edit.value='false';
		document.resp_form.action='units.php';
		document.resp_form.submit();			
		} else {
		if($('screenname').innerHTML == "fullscreen") {
			get_fs_resppopup(id);
			} else {
			get_resppopup(id);
			}
		}
	return false;
	}
	
function myfclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	if((quick) || (typeof fmarkers == 'undefined') || (!fmarkers[id]) || (internet == 0)) {
		document.fac_form.id.value=id;
		document.fac_form.func.value='responder';
		if(typeof document.fac_form.view != 'undefined') {
			document.fac_form.edit.value='false';
			document.fac_form.view.value='true';
			} else {
			document.fac_form.edit.value='true';
			}
		document.fac_form.action='facilities.php';
		document.fac_form.submit();
		} else if(guest) {
		document.fac_form.id.value=id;
		document.fac_form.func.value='responder';
		document.fac_form.view.value='true';
		document.fac_form.edit.value='false';
		document.fac_form.action='units.php';
		document.fac_form.submit();			
		} else {
		if($('screenname').innerHTML == "fullscreen") {
			get_fs_facspopup(id);
			} else {
			get_facspopup(id);
			}
		}
	return false;
	}
	
function mywlclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	if((quick) || (!wlmarkers[id]) || (internet == 0)) {
		document.wl_form.id.value=id;
		document.wl_form.submit();
		} else {
		map.panTo(wlmarkers[id].getLatLng());
		wlmarkers[id].openPopup();
		}
	return false;
	}
	
function myrssclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	map.panTo(rss_markers[id].getLatLng());
	rss_markers[id].openPopup();
	return false;
	}
	
function init_map(theType, lat, lng, icon, initzoom, locale, useOSMAP, control_position) {
	var protocol = (https == "1") ? "https://" : "http://";
	if(locale == 1 && useOSMAP == 1) {	//	UK Use Ordnance Survey as Basemap
		openspaceLayer = L.tileLayer.OSOpenSpace(openspace_api, {debug: true});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		var baseLayerNamesArr = ["Ordnance Survey"];	
		var baseLayerVarArr = [openspaceLayer];
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = baseLayerVarArr[a];
		map = new L.Map('map_canvas', {
			crs: L.OSOpenSpace.getCRS(),
			continuousWorld: true,
			worldCopyJump: false,
			minZoom: 0,
			maxZoom: L.OSOpenSpace.RESOLUTIONS.length - 1,
			zoomControl: false,
			layers: [openspaceLayer],
			});

		if(window.geo_provider == 1) {
			geocoder = L.Control.Geocoder.google(window.GoogleKey), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});
			} else if(window.geo_provider == 2) {
			geocoder = L.Control.Geocoder.bing(window.BingKey), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});				
			} else {
			geocoder = L.Control.Geocoder.nominatim(), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});
			}
		if(!isIE()) {
			control.addTo(map);
			}

		var baseLayers;
		var overlays = {
			"Grid": grid,
		};
		if(control_position == "tl") {
			ctrlPos = 'topleft';
			} else if(control_position == "tr") {
			ctrlPos = 'topright';
			} else if(control_position == "bl") {
			ctrlPos = 'bottomleft';
			} else if(control_position == "br") {
			ctrlPos = 'bottomright';
			} else {
			ctrlPos = 'none';
			}
		if(ctrlPos != "none") {
			layercontrol = L.control.layers(baseLayers, overlays, {position: ctrlPos}).addTo(map);
			map.addLayer(roadalerts);
			layercontrol.addOverlay(roadalerts, "Road Conditions");
			L.control.scale().addTo(map);
			L.control.zoom({position: ctrlPos}).addTo(map);
			}
		if(theType ==2) {
			createcrossMarker(lat, lng);
			}
		if(theType ==3) {
			createstdMarker(lat, lng);
			}
		map.setView([lat, lng], initzoom);
		bounds = map.getBounds();	
		zoom = map.getZoom();
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=" + sess_id;
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		} else {
		var latLng;
		var osmUrl = (my_Local=="1")? "./_osm/tiles/{z}/{x}/{y}.png": protocol + "{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		var	cmAttr = '';
		var cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade';
		var OSM   = L.tileLayer(osmUrl, {attribution: cmAttr});
		if(good_gmapsapi == 1) {
			var ggl = new L.Google('ROAD');
			var ggl1 = new L.Google('TERRAIN');
			var ggl2 = new L.Google('SATELLITE');
			var ggl3 = new L.Google('HYBRID');
			}
		var clouds = L.OWM.clouds({appId: owm_api,showLegend: false, opacity: 0.3});
		var cloudscls = L.OWM.cloudsClassic({appId: owm_api,showLegend: false, opacity: 0.3});
		var precipitation = L.OWM.precipitation({appId: owm_api,showLegend: false, opacity: 0.3});
		var precipitationcls = L.OWM.precipitationClassic({appId: owm_api,showLegend: false, opacity: 0.3});
		var rain = L.OWM.rain({appId: owm_api,showLegend: false, opacity: 0.3});
		var raincls = L.OWM.rainClassic({appId: owm_api,showLegend: false, opacity: 0.3});
		var snow = L.OWM.snow({appId: owm_api,showLegend: false, opacity: 0.3});
		var pressure = L.OWM.pressure({appId: owm_api,showLegend: false, opacity: 0.3});
		var pressurecntr = L.OWM.pressureContour({appId: owm_api,showLegend: false, opacity: 0.8});
		var temp = L.OWM.temperature({appId: owm_api,showLegend: false, opacity: 0.3});
		var wind = L.OWM.wind({appId: owm_api,showLegend: false, opacity: 0.3});
		var dark = L.tileLayer.provider('Thunderforest.TransportDark');
		if(owm_api != "") {
			var city = L.OWM.current({interval: 15, lang: 'en', minZoom: 8, appId: owm_api});
			} else {
			var city = L.OWM.current({interval: 15, lang: 'en', minZoom: 8});
			}
		var nexrad = L.tileLayer.wms(protocol + "mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi", {
			layers: 'nexrad-n0r-900913',
			format: 'image/png',
			transparent: true,
			attribution: "",
		});
		var shade = L.tileLayer.wms(protocol + "ims.cr.usgs.gov:80/servlet19/com.esri.wms.Esrimap/USGS_EDC_Elev_NED_3", {
			layers: "HR-NED.IMAGE", 
			format: 'image/png',
			attribution: "",
		});
		var usgstopo = L.tileLayer(protocol + 'basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {
			maxZoom: 20,
			attribution: '',
		});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		if(good_gmapsapi == 0 && (currentSessionLayer == "Google" || currentSessionLayer == "Google_Terrain" || currentSessionLayer == "Google_Satellite" || currentSessionLayer == "Google_Hybrid")) {
			currentSessionLayer = "Open_Streetmaps";
			alert("please change your default map layer, Google is not available,\n Tickets has set the map to Open Street Maps.\n After changing you will need to log out and log in again.");
			}
		if(good_gmapsapi == 1) {
			var baseLayerNamesArr = ["Open_Streetmaps","Google","Google_Terrain","Google_Satellite","Google_Hybrid","USGS_Topo","Dark"];	
			var baseLayerVarArr = [OSM,ggl,ggl1,ggl2,ggl3,usgstopo,dark];
			} else {
			var baseLayerNamesArr = ["Open_Streetmaps","USGS_Topo","Dark"];	
			var baseLayerVarArr = [OSM,usgstopo,dark];
			}
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = (my_Local != "1") ? baseLayerVarArr[a]: OSM;	// Load OSM if using local maps
		if(window.geo_provider == 1) {
			if(window.GoogleKey.length > 0 && window.GoogleKey.length < 39) {
				alert("Google set as Geo-coding provider but invalid Google Maps API Key");
				}
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: max_zoom,
				minZoom: theZoom,
				zoom: initzoom,
				layers: [theLayer],
				zoomControl: false,
				closePopupOnClick: false,
				}
				)};
				geocoder = L.Control.Geocoder.google(window.GoogleKey), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}
			} else if(window.geo_provider == 2){
			if(window.BingKey.length != 64) {
				alert("Bing set as Geo-coding provider but invalid Bing Maps API Key");
				}
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: max_zoom,
				minZoom: theZoom,
				zoom: initzoom,
				layers: [theLayer],
				zoomControl: false,
				closePopupOnClick: false,
				}
				)};
				geocoder = L.Control.Geocoder.bing(window.BingKey), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}			
			} else {
			if(!map) {map = L.map('map_canvas',
				{
				maxZoom: max_zoom,
				minZoom: theZoom,
				zoom: initzoom,
				layers: [theLayer],
				zoomControl: false,
				closePopupOnClick: false,
				}
				)};
				geocoder = L.Control.Geocoder.nominatim(), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}
			}

		if(my_Local == "0") {
			if(good_gmapsapi == 1) {
				var baseLayers = {
					"Open Streetmaps": OSM,
					"Google": ggl,
					"Google Terrain": ggl1,
					"Google Satellite": ggl2,
					"Google Hybrid": ggl3,
					"USGS Topo": usgstopo,
					"Dark": dark,
					};
				} else {
				var baseLayers = {
					"Open Streetmaps": OSM,
					"USGS Topo": usgstopo,
					"Dark": dark,
					};
				}
			
			var overlays = {
				"Clouds": cloudscls,
				"Precipitation": precipitationcls,
				"Rain": raincls,
				"Pressure": pressurecntr,
				"Temperature": temp,
				"Wind": wind,
				"Snow": snow,
				"Radar": nexrad,
				"Grid": grid,
				"City Weather": city,
			};
			map.setView([lat, lng], initzoom);
			bounds = map.getBounds();	
			zoom = map.getZoom();
			} else {	//	remove all but OSM if using local maps
			var baseLayers = {
				"Open Streetmaps": OSM,
			};
			if(my_Local == "1" && (theBounds instanceof Array)) {
				var southWest = L.latLng(theBounds[3], theBounds[0]);
				var northEast = L.latLng(theBounds[1], theBounds[2]);
				var maxBounds = L.latLngBounds(southWest, northEast);
				map.setMaxBounds(maxBounds);
				}
			var overlays = {};
			map.setView([lat, lng], initzoom);
			bounds = map.getBounds();	
			zoom = map.getZoom();		
			}
			
			if(control_position == "tl") {
				ctrlPos = 'topleft';
				} else if(control_position == "tr") {
				ctrlPos = 'topright';
				} else if(control_position == "bl") {
				ctrlPos = 'bottomleft';
				} else if(control_position == "br") {
				ctrlPos = 'bottomright';
				} else {
				ctrlPos = 'none';
				}
			
		if(ctrlPos != "none") {
			layercontrol = L.control.layers(baseLayers, overlays, {position: ctrlPos}).addTo(map);
			map.addLayer(roadalerts);
			layercontrol.addOverlay(roadalerts, "Road Conditions");
			L.control.scale().addTo(map);
			L.control.zoom({position: ctrlPos}).addTo(map);
			}
		if(theType ==2) {
			createcrossMarker(lat, lng);
			}
		if(theType ==3) {
			createstdMarker(lat, lng);
			}
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=" + sess_id;
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		}
	return map;
	}
	
function layer_handleResult(req) {
//	alert(req.responseText);
	}
	
function init_fsmap(theType, lat, lng, icon, initzoom, locale, useOSMAP, control_position) {
	var protocol = (https == "1") ? "https://" : "http://";
	if(locale == 1 && useOSMAP == 1) {	//	UK Use Ordnance Survey as Basemap
		openspaceLayer = L.tileLayer.OSOpenSpace(openspace_api, {debug: true});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		var baseLayerNamesArr = ["Ordnance Survey"];	
		var baseLayerVarArr = [openspaceLayer];
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = baseLayerVarArr[a];
		map = new L.Map('map_canvas', {
			crs: L.OSOpenSpace.getCRS(),
			continuousWorld: true,
			worldCopyJump: false,
			minZoom: 0,
			maxZoom: L.OSOpenSpace.RESOLUTIONS.length - 1,
			zoomControl: false,
			layers: [openspaceLayer],
			});

		if(window.geo_provider == 1) {
			geocoder = L.Control.Geocoder.google(window.GoogleKey), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});
			} else if(window.geo_provider == 2) {
			geocoder = L.Control.Geocoder.bing(window.BingKey), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});				
			} else {
			geocoder = L.Control.Geocoder.nominatim(), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});
			}
		if(!isIE()) {
			control.addTo(map);
			}
		var baseLayers;
		var overlays = {
			"Grid": grid,
		};
		if(control_position == "tl") {
			ctrlPos = 'topleft';
			} else if(control_position == "tr") {
			ctrlPos = 'topright';
			} else if(control_position == "bl") {
			ctrlPos = 'bottomleft';
			} else if(control_position == "br") {
			ctrlPos = 'bottomright';
			} else {
			ctrlPos = 'none';
			}
		if(ctrlPos != "none") {
			layercontrol = L.control.layers(baseLayers, overlays, {position: ctrlPos}).addTo(map);
			map.addLayer(roadalerts);
			layercontrol.addOverlay(roadalerts, "Road Conditions");
			L.control.scale().addTo(map);
			L.control.zoom({position: ctrlPos}).addTo(map);
			}
		if(theType ==2) {
			createcrossMarker(lat, lng);
			}
		if(theType ==3) {
			createstdMarker(lat, lng);
			}
		map.setView([lat, lng], theZoom);
		bounds = map.getBounds();	
		zoom = map.getZoom();
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=" + sess_id;
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		} else {
		var latLng;
		var osmUrl = (my_Local=="1")? "./_osm/tiles/{z}/{x}/{y}.png": protocol + "{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		var	cmAttr = '';
		var OSM = L.tileLayer(osmUrl, {attribution: cmAttr});
		if(good_gmapsapi == 1) {
			var ggl = new L.Google('ROAD');
			var ggl1 = new L.Google('TERRAIN');
			var ggl2 = new L.Google('SATELLITE');
			var ggl3 = new L.Google('HYBRID');
			}
		var clouds = L.OWM.clouds({appId: owm_api,showLegend: false, opacity: 0.3});
		var cloudscls = L.OWM.cloudsClassic({appId: owm_api,showLegend: false, opacity: 0.3});
		var precipitation = L.OWM.precipitation({appId: owm_api,showLegend: false, opacity: 0.3});
		var precipitationcls = L.OWM.precipitationClassic({appId: owm_api,showLegend: false, opacity: 0.3});
		var rain = L.OWM.rain({appId: owm_api,showLegend: false, opacity: 0.3});
		var raincls = L.OWM.rainClassic({appId: owm_api,showLegend: false, opacity: 0.3});
		var snow = L.OWM.snow({appId: owm_api,showLegend: false, opacity: 0.3});
		var pressure = L.OWM.pressure({appId: owm_api,showLegend: false, opacity: 0.3});
		var pressurecntr = L.OWM.pressureContour({appId: owm_api,showLegend: false, opacity: 0.8});
		var temp = L.OWM.temperature({appId: owm_api,showLegend: false, opacity: 0.3});
		var wind = L.OWM.wind({appId: owm_api,showLegend: false, opacity: 0.3});
		var dark = L.tileLayer.provider('Thunderforest.TransportDark');
		if(owm_api != "") {
			var city = L.OWM.current({interval: 15, lang: 'en', minZoom: 8, appId: owm_api});
			} else {
			var city = L.OWM.current({interval: 15, lang: 'en', minZoom: 8});
			}
		var nexrad = L.tileLayer.wms(protocol + "mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi", {
			layers: 'nexrad-n0r-900913',
			format: 'image/png',
			transparent: true,
			attribution: "",
		});
		var shade = L.tileLayer.wms(protocol + "ims.cr.usgs.gov:80/servlet19/com.esri.wms.Esrimap/USGS_EDC_Elev_NED_3", {
			layers: "HR-NED.IMAGE", 
			format: 'image/png',
			attribution: "",
		});
		var usgstopo = L.tileLayer(protocol + 'basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {
			maxZoom: 20,
			attribution: '',
		});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		if(good_gmapsapi == 0 && (currentSessionLayer == "Google" || currentSessionLayer == "Google_Terrain" || currentSessionLayer == "Google_Satellite" || currentSessionLayer == "Google_Hybrid")) {
			currentSessionLayer = "Open_Streetmaps";
			alert("please change your default map layer, Google is not available,\n Tickets has set the map to Open Street Maps.\n After changing you will need to log out and log in again.");
			}
		if(good_gmapsapi == 1) {
			var baseLayerNamesArr = ["Open_Streetmaps","Google","Google_Terrain","Google_Satellite","Google_Hybrid","USGS_Topo","Dark"];	
			var baseLayerVarArr = [OSM,ggl,ggl1,ggl2,ggl3,usgstopo,dark];
			} else {
			var baseLayerNamesArr = ["Open_Streetmaps","USGS_Topo","Dark"];	
			var baseLayerVarArr = [OSM,usgstopo,dark];
			}
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = (my_Local != "1") ? baseLayerVarArr[a]: OSM;	// Load OSM if using local maps
		if(window.geo_provider == 1) {
			if(window.GoogleKey.length > 0 && window.GoogleKey.length < 39) {
				alert("Google set as Geo-coding provider but invalid Google Maps API Key");
				}
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: max_zoom,
				minZoom: theZoom,
				zoom: initzoom,
				layers: [theLayer],
				zoomControl: false,
				}
				)};
				geocoder = L.Control.Geocoder.google(window.GoogleKey), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}
			} else if(window.geo_provider == 2){
			if(window.BingKey.length != 64) {
				alert("Bing set as Geo-coding provider but invalid Bing Maps API Key");
				}
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: max_zoom,
				minZoom: theZoom,
				zoom: initzoom,
				layers: [theLayer],
				zoomControl: false,
				}
				)};
				geocoder = L.Control.Geocoder.bing(window.BingKey), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}			
			} else {
			if(!map) {map = L.map('map_canvas',
				{
				maxZoom: max_zoom,
				minZoom: theZoom,
				zoom: initzoom,
				layers: [theLayer],
				zoomControl: false,
				}
				)};
				geocoder = L.Control.Geocoder.nominatim(), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}
			}

		if(my_Local == "0") {	//	remove all but OSM if using local maps
			if(good_gmapsapi == 1) {
				var baseLayers = {
					"Open Streetmaps": OSM,
					"Google": ggl,
					"Google Terrain": ggl1,
					"Google Satellite": ggl2,
					"Google Hybrid": ggl3,
					"USGS Topo": usgstopo,
					"Dark": dark,
					};
				} else {
				var baseLayers = {
					"Open Streetmaps": OSM,
					"USGS Topo": usgstopo,
					"Dark": dark,
					};
				}
			
			var overlays = {
				"Clouds": cloudscls,
				"Precipitation": precipitationcls,
				"Rain": raincls,
				"Pressure": pressurecntr,
				"Temperature": temp,
				"Wind": wind,
				"Snow": snow,
				"Radar": nexrad,
				"Grid": grid,
				"City Weather": city,
			};

			} else {
			var baseLayers = {
				"Open Streetmaps": OSM,
			};
			
			var overlays = {};				
			}
			if(control_position == "tl") {
				ctrlPos = 'topleft';
				} else if(control_position == "tr") {
				ctrlPos = 'topright';
				} else if(control_position == "bl") {
				ctrlPos = 'bottomleft';
				} else if(control_position == "br") {
				ctrlPos = 'bottomright';
				} else {
				ctrlPos = 'none';
				}

		if(ctrlPos != "none") {
			layercontrol = L.control.layers(baseLayers, overlays, {position: ctrlPos}).addTo(map);
			map.addLayer(roadalerts);
			layercontrol.addOverlay(roadalerts, "Road Conditions");
			L.control.scale().addTo(map);
			L.control.zoom({position: ctrlPos}).addTo(map);
			}
		if(theType ==2) {
			createcrossMarker(lat, lng);
			}
		if(theType ==3) {
			createstdMarker(lat, lng);
			}
		map.setView([lat, lng], setZoom);
		bounds = map.getBounds();	
		zoom = map.getZoom();
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=" + sess_id;
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		}
	return map;
	}

function init_minimap(theType, lat, lng, icon, theZoom, locale, useOSMAP) {
	var protocol = (https == "1") ? "https://" : "http://";
	var latLng;
	var my_Path = "http://127.0.0.1/_osm/tiles/";
	var osmUrl = (my_Local=="1")? "../_osm/tiles/{z}/{x}/{y}.png": protocol + "{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
	var	cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade';
	var OSM   = L.tileLayer(osmUrl, {attribution: cmAttr});
	if(minimap) { minimap.remove(); }
	minimap = L.map('minimap',
		{
		maxZoom: 18,
		zoom: theZoom,
		layers: [OSM],
		zoomControl: false,
		attributionControl: false,
		});
	createmmMarker(lat, lng);
	return minimap;
	}
	
function checkArray(form, arrayName)	{	//	5/3/11
	var retval = new Array();
	for(var i=0; i < form.elements.length; i++) {
		var el = form.elements[i];
		if(el.type == "checkbox" && el.name == arrayName && el.checked) {
			retval.push(el.value);
			}
		}
	return retval;
	}
	
function checkForm(form)	{
	var errmsg="";
	var itemsChecked = checkArray(form, "frm_group[]");
	if(itemsChecked.length != 0) {
		var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=" + sess_id;
		var url = "persist3.php";	//	3/15/11	
		sendRequest (url, fvg_handleResult, params);				
		} else {
		errmsg+= "\tYou cannot Hide all the regions\n";
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		}
	loadData();
	}

function fvg_handleResult(req) {	// 6/10/11	The persist callback function for viewed groups.
	show_regsmsg("Viewed Regions have changed");
	window.resp_last_display = 0;
	window.inc_last_display = 0;
	window.do_inc_update = true;	
	window.do_resp_update = true;
	window.do_fac_update = true;
	do_log_refresh = true;
	responders_updated = [];
	facilities_updated = [];
	window.incFin = false;
	window.respFin = false;
	window.facFin = false;
	window.logFin = false;
	window.statSel = false;
	window.facstatSel = false;
	loadData();
	update_regions_text();
	}
	
function form_validate(theForm) {	//	5/3/11
	checkForm(theForm);
	}				// end function validate(theForm)
	
function show_regsmsg(msg) {
	if($('regs_conf_span')) {
		$('regs_conf_span').innerHTML = msg;			
		setTimeout("$('regs_conf_span').innerHTML =''", 3000);	// show for 3 seconds
		}
	}
	
function getWidth(ele) {
	return ele.getBoundingClientRect().width;
	}
	
function getHeight(ele) {
	return ele.getBoundingClientRect().height;
	}
	
function getHeaderHeight(element) {
	return element.clientHeight;
	}
	
function set_inc_headers(id, header_text, the_bull) {
	if(id == "t1") {
		window.t1_text = header_text + the_bull;
		window.t2_text = textScope;
		window.t3_text = textAddress;
		window.t4_text = textType;
		window.t5_text = textA;
		window.t6_text = textP;
		window.t7_text = textU;
		window.t8_text = textUpdated;
		} else if(id == "t2") {
		window.t2_text = header_text + the_bull;
		window.t1_text = textID;
		window.t3_text = textAddress;
		window.t4_text = textType;
		window.t5_text = textA;
		window.t6_text = textP;
		window.t7_text = textU;
		window.t8_text = textUpdated;
		} else if(id == "t3") {
		window.t3_text = header_text + the_bull;
		window.t1_text = textID;
		window.t2_text = textScope;
		window.t4_text = textType;
		window.t5_text = textA;
		window.t6_text = textP;
		window.t7_text = textU;
		window.t8_text = textUpdated;
		} else if(id == "t4") {
		window.t4_text = header_text + the_bull;
		window.t1_text = textID;
		window.t2_text = textScope;
		window.t3_text = textAddress;
		window.t5_text = textA;
		window.t6_text = textP;
		window.t7_text = textU;
		window.t8_text = textUpdated;
		} else if(id == "t5") {
		window.t5_text = header_text + the_bull;
		window.t1_text = textID;
		window.t2_text = textScope;
		window.t3_text = textAddress;
		window.t4_text = textType;
		window.t6_text = textP;
		window.t7_text = textU;
		window.t8_text = textUpdated;
		} else if(id == "t6") {
		window.t6_text = header_text + the_bull;
		window.t1_text = textID;
		window.t2_text = textScope;
		window.t3_text = textAddress;
		window.t4_text = textType;
		window.t5_text = textA;
		window.t7_text = textU;
		window.t8_text = textUpdated;
		} else if(id == "t7") {
		window.t7_text = header_text + the_bull;
		window.t1_text = textID;
		window.t2_text = textScope;
		window.t3_text = textAddress;
		window.t4_text = textType;
		window.t5_text = textA;
		window.t6_text = textP;
		window.t8_text = textUpdated;
		} else if(id == "t8") {
		window.t8_text = header_text + the_bull;
		window.t1_text = textID;
		window.t2_text = textScope;
		window.t3_text = textAddress;
		window.t4_text = textType;
		window.t5_text = textA;
		window.t6_text = textP;
		window.t7_text = textU;
		}
	}
	
function do_inc_sort(id, field, header_text) {
	if($('spinner_i')) {
		$('spinner_i').style.display = "block";
		$('spinner_i').innerHTML = "<CENTER><IMG src='./images/animated_spinner.gif'></CENTER>";
		}
	window.changed_inc_sort = true;
	if(inc_field == field) {
		if(window.inc_direct == "ASC") {
			window.inc_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.inc_header = header_text;
			set_inc_headers(id, header_text, the_bull);
			} else if(window.inc_direct == "DESC") { 
			window.inc_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.inc_header = header_text; 
			set_inc_headers(id, header_text, the_bull);
			}
		} else {
		$(inc_id).innerHTML = inc_header;
		window.inc_field = field;
		window.inc_direct = "ASC";
		window.inc_id = id;
		window.inc_header = header_text;
		var the_bull = "&#9650";
		set_inc_headers(id, header_text, the_bull);
		}
	load_incidentlist(window.inc_field, window.inc_direct);
	return true;
	}
	
var cursorX = 0;
var cursorY = 0;

function getPos(e, id){
	window.cursorX=e.clientX;
	window.cursorY=e.clientY;
	return true;
	}
	
function close_context() {
//	document.body.removeChild(divTag);
	if(divTag) {divTag.style.display = "none";}
	document.oncontextmenu = function() {return true; }
	}

function getPosition(element) {
	var offsets = $(element).getBoundingClientRect();
	var top = offsets.top;
	var left = offsets.left;
	var theRet = [];
	theRet[0] = top;
	theRet[1] = left;
	return theRet;
	}
	
function localContext(id) {
	if(divTag) {divTag.style.display = "none";}
	document.oncontextmenu = function() {return false; }
	createDiv(id);
	}

function createDiv(id) {
	var clickedElem = "inc_" + id;
	var coords = getPosition(clickedElem);
	divTag = document.createElement("div");
	divTag.id = "div1";
	divTag.style.position = "absolute";
	divTag.style.width = "130px";
	divTag.style.height = "210px";
    divTag.style.backgroundColor = "rgb(0%, 0%, 0%)";
    divTag.style.backgroundColor = "rgba(0%, 0%, 0%, 0.3)";
	divTag.style.borderLeft = "1px solid #707070";
	divTag.style.borderTop = "1px solid #707070";
	divTag.innerHTML = "<DIV style='height: auto; width: 130px; display: block;'>";
	divTag.innerHTML += "<DIV style='height: 25px; display: block;'><img src='images/close.png' style='float: right;' onClick='close_context();' alt='close' height='23px' width='23px'></DIV>";
	divTag.innerHTML += "<A id='mail_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' HREF='#' onClick = 'do_mail_all_win(" + id + ");' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Contact Units</A>";
	divTag.innerHTML += "<A id='disp_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' HREF='routes.php?ticket_id=" + id + "' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Dispatch</A><BR />";
	divTag.innerHTML += "<SPAN id='note_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' onClick = 'do_add_note(" + id + ");' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Add note</SPAN><BR />";
	divTag.innerHTML += "<SPAN id='action_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' onClick = 'do_add_action(" + id + ");' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Add action</SPAN><BR />";
	divTag.innerHTML += "<SPAN id='patient_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' onClick = 'do_add_patient(" + id + ");' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Add patient</SPAN><BR />";
	divTag.innerHTML += "<SPAN id='print_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' onClick = 'do_print_ticket(" + id + ");' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Print</SPAN><BR />";
	divTag.innerHTML += "<SPAN id='close_" + id + "' CLASS='plain text' style='height: auto; width: 100px; display: block;' onClick = 'do_close_tick(" + id + ");' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>Close</SPAN><BR /><BR /></DIV><BR />";
	divTag.style.left = cursorX + "px";
	divTag.style.top = coords[0] + "px";
	document.body.appendChild(divTag);
    }

function load_incidentlist(sort, dir) {
	window.counter++;
	window.incFin = false;
	window.incLoading = true;
	if(sort != window.inc_field) {
		window.inc_field = sort;
		}
	if(dir != window.inc_direct) {
		window.inc_direct = dir;
		}
	if($('the_list').innerHTML == "") {
		$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = sess_id;
	var url = './ajax/sit_incidents.php?sort='+window.inc_field+'&dir='+ window.inc_direct+'&func='+window.inc_period+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,incidentlist_cb, "");
	function incidentlist_cb(req) {
		var inc_arr = JSON.decode(req.responseText);
		if(!inc_arr) {
			if(doDebug) {
				log_debug(req.responseText); 
				sendInfo(req.responseText);
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......Error loading Incident list.........</marquee>";
			$('the_list').innerHTML = outputtext;
			window.incFin = true;
			pageLoaded();
			return;
			}	
		if(window.inc_period_changed == 1) {
			if($('map_canvas')) {	
				for(var key in tmarkers) {
					if(tmarkers[key]) {map.removeLayer(tmarkers[key]); tmarkers[key] = false;}
					}
				}
			$('the_list').innerHTML = "";
			window.inc_period_changed = 0;
			window.do_inc_refresh = true;
			}
		if((inc_arr[0]) && (inc_arr[0][0] == 0)) {
			window.inc_last_display = 0;
			if($('map_canvas')) {	
				for(var key in tmarkers) {
					if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
					}
				}
			outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Incidents, please select another time period or add a new incident.........</marquee>";
			window.incFin = true;
			pageLoaded();
			$('the_list').innerHTML = outputtext;
			var the_sev_str = "<font color='blue'>Normal " + inc_arr[0][22] + "</FONT>, ";
			the_sev_str += "<font color='green'>Medium " + inc_arr[0][23] + "</FONT>, ";
			the_sev_str += "<font color='red'>High " + inc_arr[0][24] + "</FONT>";			
			$('sev_counts').innerHTML = the_sev_str;
			return false;
			}
		if(window.changed_inc_sort == true) {
			for(var key in tmarkers) {
				if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
				}
			}
		var i = 1;
		var blinkstart = "";
		var blinkend = "";
		var category = "Incident";
		var outputtext = "<TABLE id='incidenttable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR style='width: " + window.listwidth + "px; background-color: #EFEFEF;'>";
		outputtext += "<TH id='t1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + iconTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">" + window.t1_text + "</TH>";
		outputtext += "<TH id='t2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + incTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'scope', '" + textScope + "')\">" + window.t2_text + "</TH>";
		outputtext += "<TH id='t3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + locTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'street', '" + textAddress + "')\">" + window.t3_text + "</TH>";
		outputtext += "<TH id='t4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + typeTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'type', '" + textType + "')\">" + window.t4_text + "</TH>";
		outputtext += "<TH id='t5' class='plain_listheader text' style='width: 1em;' onMouseOver=\"do_hover_listheader(this.id); Tip('" + actTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'a', '" + textA + "')\">" + window.t5_text + "</TH>";
		outputtext += "<TH id='t6' class='plain_listheader text' style='width: 1em;' onMouseOver=\"do_hover_listheader(this.id); Tip('" + numTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'p', '" + textP + "')\">" + window.t6_text + "</TH>";
		outputtext += "<TH id='t7' class='plain_listheader text' style='width: 1em;' onMouseOver=\"do_hover_listheader(this.id); Tip('" + assTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'u', '" + textU + "')\">" + window.t7_text + "</TH>";
		outputtext += "<TH id='t8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + updatedTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'updated', '" + textUpdated + "')\">" + window.t8_text + "</TH>";
		outputtext += "<TH id='r9'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		for(var key in inc_arr) {
			if(key != 0) {
				var inc_id = inc_arr[key][20];
				var infowindowtext = "";
				if(tmarkers[inc_id]) {
					if(window.changed_inc_sort == false) {
						var curPos = tmarkers[inc_id].getLatLng();
						if((curPos.lat != inc_arr[key][2]) || (curPos.lng != inc_arr[key][3])) {
							theLatLng = new L.LatLng(inc_arr[key][2], inc_arr[key][3]);
							tmarkers[inc_id].setLatLng(theLatLng);
							}
						} else {
						// Changed sort, don't refresh markers
						}
					} else {
					if($('map_canvas')) {	
						if((isFloat(inc_arr[key][2])) && (isFloat(inc_arr[key][3]))) {
							var marker = createMarker(inc_arr[key][2], inc_arr[key][3], infowindowtext, inc_arr[key][5], inc_arr[key][4], inc_id, i, category, 0, inc_arr[key][11]);
							marker.addTo(map);
							} else {
							var marker = createdummyIncMarker(def_lat, def_lng, infowindowtext, "", inc_arr[key][0], i);
							marker.addTo(map);
							}
						}
					}
				if(inc_arr[key][19] == 1) {
					blinkstart = "<blink>";
					blinkend = "</blink>";
					} else {
					blinkstart = "";
					blinkend = "";
					}
				if(inc_arr[key][6] == 1) {
					var strike = "text-decoration: line-through;";
					} else {
					var strike = "";
					}
				if(inc_arr[key][21] == 0) {
					var datestring = "<SPAN>" + inc_arr[key][10] + "</SPAN>";
					} else if(inc_arr[key][21] != 0 && inc_arr[key][6] == 2) {
					var datestring = "<SPAN style='background-color: cyan; color: #000000;'>" + inc_arr[key][10] + "</SPAN>";						
					} else {
					var datestring = "<SPAN style='background-color: blue; color: #FFFFFF;'>" + inc_arr[key][21] + "</SPAN>";
					}
				outputtext += "<TR ID='inc_" + inc_id + "' CLASS='" + colors[i%2] +"' style='cursor: context-menu; width: " + window.listwidth + "px; " + strike + "' oncontextmenu=\"getPos(event); localContext(" + inc_id + ");\" onMouseover=\"Tip('" + inc_arr[key][11] + "', WIDTH, 200, SHADOW, true)\" onMouseout='UnTip();' onClick='mytclick(" + inc_id + ");'>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + ";'>" + pad(4, i, "\u00a0") + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + ";'>" + html_entity_decode(inc_arr[key][0], 'ENT_QUOTES') + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + ";'>" + html_entity_decode(inc_arr[key][1], 'ENT_QUOTES') + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + ";'>" + inc_arr[key][4] + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + "; width: 1em;'>" + pad(6, inc_arr[key][17], "\u00a0") + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + "; width: 1em;'>" + pad(6, inc_arr[key][16], "\u00a0") + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + "; width: 1em;'>" + blinkstart + pad(6, inc_arr[key][18], "\u00a0") + blinkend + "</TD>";
				outputtext += "<TD class='plain_list text_bolder' style='cursor: context-menu; color: " + inc_arr[key][14] + ";'>" + datestring + "</TD>";
				outputtext += "<TD>" + pad(3, " ", "\u00a0") + "</TD>";
				outputtext += "</TR>";
				if(window.tickets_updated[key]) {
					if(window.tickets_updated[key] != inc_arr[key][10]) {
						window.do_update = true;
						} else {
						window.do_update = false;
						}
					} else {
					window.tickets_updated[key] = inc_arr[key][10];
					window.do_update = true;
					}
				window.latest_ticket = key;
				i++;
				}
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		var the_sev_str = "<font color='blue'>Normal " + inc_arr[0][22] + "</FONT>, ";
		the_sev_str += "<font color='green'>Medium " + inc_arr[0][23] + "</FONT>, ";
		the_sev_str += "<font color='red'>High " + inc_arr[0][24] + "</FONT>";			
		$('sev_counts').innerHTML = the_sev_str;
		setTimeout(function() {
			if(window.inc_last_display == 0) {
				$('the_list').innerHTML = outputtext;
				} else {
				if((window.inc_last_display != window.latest_ticket) || (window.changed_inc_sort == true) || (window.do_inc_refresh == true)) {
					$('the_list').innerHTML = outputtext;
					window.na=document.getElementsByTagName("blink");
					if(window.changed_inc_sort == true) {
						if($('spinner_i')) {
							$('spinner_i').innerHTML = "";
							$('spinner_i').style.display = "none";
							}
						}	
					}
				}
			var inctbl = document.getElementById('incidenttable');
			if(inctbl) {
				var headerRow = inctbl.rows[0];
				var tableRow = inctbl.rows[1];
				if(tableRow) {
					for (var i = 0; i < tableRow.cells.length; i++) {
						if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
						}
					} else {
					var cellwidthBase = window.listwidth / 32;
					tcell1 = cellwidthBase * 4;
					tcell2 = cellwidthBase * 5.75;
					tcell3 = cellwidthBase * 7;
					tcell4 = cellwidthBase * 5;
					tcell5 = cellwidthBase * 2;
					tcell6 = cellwidthBase * 2;
					tcell7 = cellwidthBase * 2;
					tcell8 = cellwidthBase * 5;
					tcell9 = cellwidthBase * 0.25;
					headerRow.cells[0].style.width = tcell1 + "px";
					headerRow.cells[1].style.width = tcell2 + "px";
					headerRow.cells[2].style.width = tcell3 + "px";
					headerRow.cells[3].style.width = tcell4 + "px";
					headerRow.cells[4].style.width = tcell5 + "px";
					headerRow.cells[5].style.width = tcell6 + "px";
					headerRow.cells[6].style.width = tcell7 + "px";
					headerRow.cells[7].style.width = tcell8 + "px";
					headerRow.cells[8].style.width = tcell9 + "px";
					}
				if((window.inc_last_display != window.latest_ticket) || (window.changed_inc_sort == true) || (window.do_inc_refresh == true)) {
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = inctbl.insertRow(1);
						theRow.style.height = "20px";
						for (var i = 0; i < tableRow.cells.length; i++) {
							var theCell = theRow.insertCell(i);
							theCell.innerHTML = " ";
							}
						}
					}
				}
			do_blink();
			window.incFin = true;
			window.inc_last_display = window.latest_ticket;
			pageLoaded();
			window.do_inc_refresh = false;
			},500);
		window.incLoading = false;
		}				// end function incidentlist_cb()
	}				// end function load_incidentlist()
	
function incidentlist_setwidths() {
	var viewableRow = 1;
	var inctbl = document.getElementById('respondertable');
	var headerRow = inctbl.rows[0];
	for (i = 1; i < inctbl.rows.length; i++) {
		if(!isViewable(inctbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = inctbl.rows[viewableRow];
	if(tableRow && i != inctbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
			}
		} else {
		var cellwidthBase = window.listwidth / 32;
		tcell1 = cellwidthBase * 4;
		tcell2 = cellwidthBase * 5.75;
		tcell3 = cellwidthBase * 7;
		tcell4 = cellwidthBase * 5;
		tcell5 = cellwidthBase * 2;
		tcell6 = cellwidthBase * 2;
		tcell7 = cellwidthBase * 2;
		tcell8 = cellwidthBase * 5;
		tcell9 = cellwidthBase * 0.25;
		headerRow.cells[0].style.width = tcell1 + "px";
		headerRow.cells[1].style.width = tcell2 + "px";
		headerRow.cells[2].style.width = tcell3 + "px";
		headerRow.cells[3].style.width = tcell4 + "px";
		headerRow.cells[4].style.width = tcell5 + "px";
		headerRow.cells[5].style.width = tcell6 + "px";
		headerRow.cells[6].style.width = tcell7 + "px";
		headerRow.cells[7].style.width = tcell8 + "px";
		headerRow.cells[8].style.width = tcell9 + "px";				
		}
	if(getHeaderHeight(headerRow) >= listheader_height) {
		var theRow = inctbl.insertRow(1);
		theRow.style.height = "20px";
		for (var i = 0; i < tableRow.cells.length; i++) {
			var theCell = theRow.insertCell(i);
			theCell.innerHTML = " ";
			}
		}
	}
	
function incidentlist_get() {								// set cycle
	if (i_interval!=null) {return;}
	if(window.incFin == true || window.respLoading == true || window.facLoading == true || window.logLoading == true) {return;}
	i_interval = window.setInterval('incidentlist_loop()', 60000);
	}			// end function incidentlist_get()

function incidentlist_loop() {
	load_incidentlist(window.inc_field, window.inc_direct);
	}			// end function incidentlist_loop()
	
function do_inc_sort_fw(id, field, header_text) {
	if($('spinner_i')) {
		$('spinner_i').style.display = "block";
		$('spinner_i').innerHTML = "<CENTER><IMG src='./images/animated_spinner.gif'></CENTER>";
		}
	window.changed_inc_sort = true;
	if(inc_field == field) {
		if(window.inc_direct == "ASC") {
			window.inc_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.inc_header = header_text;
			set_inc_headers(id, header_text, the_bull);
			} else if(window.inc_direct == "DESC") { 
			window.inc_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.inc_header = header_text; 
			set_inc_headers(id, header_text, the_bull);
			}
		} else {
		$(inc_id).innerHTML = inc_header;
		window.inc_field = field;
		window.inc_direct = "ASC";
		window.inc_id = id;
		window.inc_header = header_text;
		var the_bull = "&#9650";
		set_inc_headers(id, header_text, the_bull);
		}
	load_full_incidentlist(window.inc_field, window.inc_direct);
	return true;
	}
	
function load_full_incidentlist(sort, dir) {
	window.counter++;
	window.incFin = false;
	if(sort != window.inc_field) {
		window.inc_field = sort;
		}
	if(dir != window.inc_direct) {
		window.inc_direct = dir;
		}
	if($('the_list').innerHTML == "") {
		$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = sess_id;
	var url = './ajax/sit_incidents.php?sort='+window.inc_field+'&dir='+ window.inc_direct+'&func='+window.inc_period+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,incidentlist_cb, "");
	function incidentlist_cb(req) {
		var inc_arr = JSON.decode(req.responseText);
		if(!inc_arr) {
			if(doDebug) {
				log_debug(req.responseText); 
				sendInfo(req.responseText);
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......Error loading Incident list.........</marquee>";
			$('the_list').innerHTML = outputtext;
			return;
			}	
		if(window.inc_period_changed == 1) {
			$('the_list').innerHTML = "";
			window.inc_period_changed = 0;
			}
		if((inc_arr[0]) && (inc_arr[0][0] == 0)) {
			window.inc_last_display = 0;
			outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Incidents, please select another time period or add a new incident.........</marquee>";
			$('the_list').innerHTML = outputtext;
			var the_sev_str = "<font color='blue'>Normal " + inc_arr[0][22] + "</FONT>, ";
			the_sev_str += "<font color='green'>Medium " + inc_arr[0][23] + "</FONT>, ";
			the_sev_str += "<font color='red'>High " + inc_arr[0][24] + "</FONT>";			
			$('sev_counts').innerHTML = the_sev_str;
			return false;
			}
		var i = 1;
		var blinkstart = "";
		var blinkend = "";
		var category = "Incident";
		var outputtext = "<TABLE id='incidenttable' class='fixedheadscrolling scrollable' style='width: " + window.outerwidth + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR style='width: " + window.outerwidth + "px; background-color: #EFEFEF;'>";
		outputtext += "<TH id='t2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + incTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'scope', '" + textScope + "')\">" + window.t2_text + "</TH>";
		outputtext += "<TH id='t3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + locTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'street', '" + textAddress + "')\">" + window.t3_text + "</TH>";
		outputtext += "<TH id='t4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + typeTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'type', '" + textType + "')\">" + window.t4_text + "</TH>";
		outputtext += "<TH id='t5' class='plain_listheader text' style='width: 1em;' onMouseOver=\"do_hover_listheader(this.id); Tip('" + actTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'a', '" + textA + "')\">" + window.t5_text + "</TH>";
		outputtext += "<TH id='t6' class='plain_listheader text' style='width: 1em;' onMouseOver=\"do_hover_listheader(this.id); Tip('" + numTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'p', '" + textP + "')\">" + window.t6_text + "</TH>";
		outputtext += "<TH id='t7' class='plain_listheader text' style='width: 1em;' onMouseOver=\"do_hover_listheader(this.id); Tip('" + assTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'u', '" + textU + "')\">" + window.t7_text + "</TH>";
		outputtext += "<TH id='t8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + updatedTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort_fw(this.id, 'updated', '" + textUpdated + "')\">" + window.t8_text + "</TH>";
		outputtext += "<TH id='r9' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r10' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r11' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r12' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r13' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r14' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r15' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";
		outputtext += "<TH id='r16' class='plain_listheader text'>" + pad(3, " ", "\u00a0") + "</TH>";		
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		var tabindex = 1;
		for(var key in inc_arr) {
			if(key != 0) {
				var inc_id = inc_arr[key][20];
				var infowindowtext = "";
				if(inc_arr[key][19] == 1) {
					blinkstart = "<blink>";
					blinkend = "</blink>";
					} else {
					blinkstart = "";
					blinkend = "";
					}
				if(inc_arr[key][6] == 1) {
					var strike = "text-decoration: line-through;";
					} else {
					var strike = "";
					}
				if(inc_arr[key][21] == 0) {
					var datestring = "<SPAN>" + inc_arr[key][10] + "</SPAN>";
					} else if(inc_arr[key][21] != 0 && inc_arr[key][6] == 2) {
					var datestring = "<SPAN style='background-color: cyan; color: #000000;'>" + inc_arr[key][10] + "</SPAN>";						
					} else {
					var datestring = "<SPAN style='background-color: blue; color: #FFFFFF;'>" + inc_arr[key][21] + "</SPAN>";
					}
				outputtext += "<TR ID='inc_" + inc_id + "' CLASS='" + colors[i%2] +"' style='cursor: pointer; width: " + window.outerwidth + "px; " + strike + "' onMouseover=\"Tip('" + inc_arr[key][0] + " - " + inc_arr[key][1] + "');\" onMouseout='UnTip();'>";
				outputtext += "<TD roll='button' aria-label='Incident Name for " + inc_id + " " + htmlentities(inc_arr[key][0], 'ENT_QUOTES') + "' tabindex=" + tabindex + " class='plain_list text text_bolder' style='cursor: pointer; color: " + inc_arr[key][14] + ";'>" + htmlentities(inc_arr[key][0], 'ENT_QUOTES') + "</TD>";
				tabindex++;
				outputtext += "<TD roll='button' aria-label='Address for Incident " + inc_id + " " + inc_arr[key][1] + "' tabindex=" + tabindex + " class='plain_list text text_bolder' style='cursor: pointer; color: " + inc_arr[key][14] + ";'>" + inc_arr[key][1] + "</TD>";
				tabindex++;
				outputtext += "<TD roll='button' aria-label='Incident type for Incident " + inc_id + " " + inc_arr[key][4] + "' tabindex=" + tabindex + " class='plain_list text text_bolder' style='cursor: pointer; color: " + inc_arr[key][14] + ";'>" + inc_arr[key][4] + "</TD>";
				tabindex++;
				outputtext += "<TD roll='button' aria-label='Number of Actions for Incident " + inc_id + " " + inc_arr[key][17] + "' tabindex=" + tabindex + " class='plain_list text text_bolder text_center' style='cursor: pointer; color: " + inc_arr[key][14] + "; width: 1em;'>" + bothpad(6, inc_arr[key][17], "\u00a0") + "</TD>";
				tabindex++;
				outputtext += "<TD roll='button' aria-label='Number of Patients for Incident " + inc_id + " " + inc_arr[key][16] + "' tabindex=" + tabindex + " class='plain_list text text_bolder text_center' style='cursor: pointer; color: " + inc_arr[key][14] + "; width: 1em;'>" + bothpad(6, inc_arr[key][16], "\u00a0") + "</TD>";
				tabindex++;
				outputtext += "<TD roll='button' aria-label='Number of Units assigned on Incident " + inc_id + " " + inc_arr[key][18] + "' tabindex=" + tabindex + " class='plain_list text text_bolder text_center' style='cursor: pointer; color: " + inc_arr[key][14] + "; width: 1em;'>" + blinkstart + bothpad(6, inc_arr[key][18], "\u00a0") + blinkend + "</TD>";
				tabindex++;
				outputtext += "<TD roll='button' aria-label='Incident " + inc_id + " Updated " + inc_arr[key][25] + "' tabindex=" + tabindex + " class='plain_list text text_bolder' style='cursor: pointer; color: " + inc_arr[key][14] + ";'>" + datestring + "</TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='View Incident " + inc_id + "' tabindex=" + tabindex + " id='view" + inc_id + "' class='plain text' style='height: auto; width: 85%; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);'  onClick='get_ticket(" + inc_id + ");'><SPAN style='float: left;'>" + viewbuttonText + "</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/list_small.png' BORDER=0></SPAN></TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='Add Patient to Incident " + inc_id + "' tabindex=" + tabindex + " id='pat" + inc_id + "' class='plain text' style='height: auto; width: 85%; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);'onClick=\"get_auxForm(" + inc_id + ", 'Add Patient', 'patient');\"><SPAN style='float: left;'>Add " + patientbuttonText + "</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/patient_small.png' BORDER=0></SPAN></TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='Add Action to Incident " + inc_id + "' tabindex=" + tabindex + " id='act" + inc_id + "' class='plain text' style='height: auto; width: 85%; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" + inc_id + ", 'Add Action', 'action');\"><SPAN style='float: left;'>Add " + actionbuttonText + "</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/action_small.png' BORDER=0></SPAN></TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='Add Note to Incident " + inc_id + "' tabindex=" + tabindex + " id='note" + inc_id + "' class='plain text' style='height: auto; width: 85%; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" + inc_id + ", 'Add Note', 'note');\"><SPAN style='float: left;'>Add " + notebuttonText + "</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN></TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='Dispatch Incident " + inc_id + "' tabindex=" + tabindex + " id='disp_" + inc_id + "' CLASS='plain text' style='height: auto; width: 85%; display: block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"load_dispatch(" + inc_id + ", window.disp_field, window.disp_direct, window.searchitem);\"><SPAN style='float: left;'>" + dispatchbuttonText + "</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/dispatch_small.png' BORDER=0></SPAN></TD>";			
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='Print Incident " + inc_id + "' tabindex=" + tabindex + " id='prt" + inc_id + "' class='plain text' style='height: auto; width: 85%; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"do_print_ticket(" + inc_id + ");\"><SPAN style='float: left;'>" + printbuttonText + "</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/print_small.png' BORDER=0></SPAN></TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true'><SPAN roll='button' aria-label='Contact Units Assigned to Incident " + inc_id + "' tabindex=" + tabindex + " id='contact" + inc_id + "' class='plain text' style='height: auto; width: 85%; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" + inc_id + ", 'Contact Units', 'contact_all');\"><SPAN style='float: left;'>" + contactbuttonText + " Units</SPAN>&nbsp;&nbsp;<IMG style='vertical-align: middle; float: right;' SRC='./images/mail_small.png' BORDER=0></SPAN></TD>";
				tabindex++;
				outputtext += "<TD aria-hidden='true' class='plain_list text_large text_bolder' style='cursor: pointer; color: " + inc_arr[key][14] + ";'>" + pad(3, " ", "\u00a0") + "</TD>";
				tabindex++;
				outputtext += "</TR>";
				if(window.tickets_updated[key]) {
					if(window.tickets_updated[key] != inc_arr[key][10]) {
						window.do_update = true;
						} else {
						window.do_update = false;
						}
					} else {
					window.tickets_updated[key] = inc_arr[key][10];
					window.do_update = true;
					}
				window.latest_ticket = key;
				i++;
				}
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		var the_sev_str = "<font color='blue'>Normal " + inc_arr[0][22] + "</FONT>, ";
		the_sev_str += "<font color='green'>Medium " + inc_arr[0][23] + "</FONT>, ";
		the_sev_str += "<font color='red'>High " + inc_arr[0][24] + "</FONT>";			
		$('sev_counts').innerHTML = the_sev_str;
		setTimeout(function() {
			if(window.inc_last_display == 0) {
				$('the_list').innerHTML = outputtext;
				} else {
				if((window.inc_last_display != window.latest_ticket) || (window.changed_inc_sort == true) || (window.do_inc_refresh == true)) {
					$('the_list').innerHTML = outputtext;
					window.na=document.getElementsByTagName("blink");
					if(window.changed_inc_sort == true) {
						if($('spinner_i')) {
							$('spinner_i').innerHTML = "";
							$('spinner_i').style.display = "none";
							}
						}	
					}
				}
			},500);
		setTimeout(function() {
			var inctbl = document.getElementById('incidenttable');
			var headerRow = inctbl.rows[0];
			var tableRow = inctbl.rows[1];
			if(tableRow) {
				for (var i = 0; i < tableRow.cells.length; i++) {
					if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth +1 + "px";}
					}
				} else {
				var cellwidthBase = window.outerwidth / 56;
				var cellwidth = cellwidthBase * 4;
				for (var i = 0; i < headerRow.cells.length; i++) {
					if(headerRow.cells[i]) {headerRow.cells[i].style.width = cellwidth + "px";}
					}
				}
			window.inc_last_display = window.latest_ticket;
			window.do_inc_refresh = false;
			full_incidentlist_get();
			},1500);
		}				// end function incidentlist_cb()
	}				// end function load_incidentlist()
	
	
	
function full_incidentlist_setwidths() {
	var viewableRow = 1;
	var inctbl = document.getElementById('respondertable');
	var headerRow = inctbl.rows[0];
	for (i = 1; i < inctbl.rows.length; i++) {
		if(!isViewable(inctbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = inctbl.rows[viewableRow];
	if(tableRow && i != inctbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
			}
		} else {
		var cellwidthBase = window.outerwidth / 56;
		var cellwidth = cellwidthBase * 4;
		for (var i = 0; i < headerRow.cells.length; i++) {
			if(headerRow.cells[i]) {headerRow.cells[i].style.width = cellwidth + "px";}
			}		
		}
	}
	
function full_incidentlist_get() {								// set cycle
	if (i_interval!=null) {return;}
	i_interval = window.setInterval('full_incidentlist_loop()', 20000);
	}			// end function full_incidentlist_get()

function full_incidentlist_loop() {
	load_full_incidentlist(window.inc_field, window.inc_direct);
	}			// end function incidentlist_loop()
	
function load_full_incidentlist_incbuttons() {
	window.counter++;
	window.incFin = false;
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = sess_id;
	var url = './ajax/fullsit_incidents.php?version='+randomnumber+'&func='+window.inc_period+'&q='+sessID;
	sendRequest (url,incidentlist_cb, "");
	function incidentlist_cb(req) {
		var inc_arr = JSON.decode(req.responseText);
		if(!inc_arr) {
			if(doDebug) {
				log_debug(req.responseText); 
				sendInfo(req.responseText);
				}
			return;
			}	
		if(window.inc_period_changed == 1) {
			}
		if((inc_arr[0]) && (inc_arr[0][0] == 0)) {
			window.inc_last_display = 0;
			$('incbuttons').innerHTML = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Incidents, please select another time period or add a new incident.........</marquee>";
			return false;
			}
		var i = 1;
		$('incbuttons').innerHTML = "";
		for(var key in inc_arr) {
			if(key != 0) {
				var inc_id = inc_arr[key][20];
				if(inc_arr[key][21] == 0) {
					var datestring = "<SPAN class='text text_white text_bold'>" + inc_arr[key][10] + "</SPAN>";
					} else if(inc_arr[key][21] != 0 && inc_arr[key][6] == 2) {
					var datestring = "<SPAN class='text text_bold' style='background-color: cyan; color: #000000;'>" + inc_arr[key][10] + "</SPAN>";						
					} else {
					var datestring = "<SPAN class='text text_bold' style='background-color: blue; color: #FFFFFF;'>" + inc_arr[key][21] + "</SPAN>";
					}
				var addonstring = "<SPAN class='text text_white text_bold'>A: " + inc_arr[key][7] + "&nbsp;&nbsp;&nbsp;P: " + inc_arr[key][8] + "&nbsp;&nbsp;&nbsp;U: " + inc_arr[key][9] + "</SPAN>";
				var buttonelement = document.createElement('span');
				buttonelement.id = "but_inc_" + inc_id;
				buttonelement.roll = "button";
				buttonelement.setAttribute('aria-label', "Incident " + inc_id);
				buttonelement.setAttribute('tabindex', inc_id);
				buttonelement.className = 'plain_centerbuttons text';
				buttonelement.style.width = '18%';
				buttonelement.style.display = 'block';
				buttonelement.style.textAlign = 'center';
				buttonelement.style.verticalAlign = 'middle';
				buttonelement.innerHTML = "<CENTER><DIV style='height: 90%; width: 90%; vertical-align: middle; background-color: " + inc_arr[key][14] + ";'><span class='text text_bold text_white'>" + htmlentities(inc_arr[key][0], 'ENT_QUOTES') + "</SPAN><BR />" + datestring + "<BR />" + addonstring + "</DIV></CENTER>";
				buttonelement.onmouseover = function() {do_hover_centerbuttons(this.id);}
				buttonelement.onmouseout = function() {do_plain_centerbuttons(this.id);}
				buttonelement.onclick = function(e) {get_auxForm(this.id, 'Incident Options', 'incbutton_opts',e.clientX,e.clientY);}
				$('incbuttons').appendChild(buttonelement);
				if(inc_arr[key][19] == 1) {
					blink_element(buttonelement.id);
					} else {
					unblink_element(buttonelement.id);
					}
				window.latest_ticket = key;
				i++;
				}
			}
		setTimeout(function() {
			window.inc_last_display = window.latest_ticket;
			window.do_inc_refresh = false;
			},500);
		}				// end function incidentlist_cb()
	}				// end function load_incidentlist()

function ringfence_alert(respid) {
	var the_responders = JSON.decode(window.theResponderHandles);
	try {
		if(newwindow_incfs && newwindow_incfs.$('alerts').style.display == "none") {
			newwindow_incfs.$('alerts').style.display="block";
			}
		var theID = "rf_resp_" + respid;
		if($(newwindow_incfs.$(theID))) {
			return;
			}
		var handle = the_responders[respid];
		var alertsdiv = newwindow_incfs.$('alerts');
		var alertspan = document.createElement('span');
		alertspan.id = theID;
		alertspan.className = "plain_centerbuttons text";
		alertspan.style.float = "left";
		alertspan.style.width = "100px";
		alertspan.style.margin = "10px";
		alertspan.onmouseover = function() {newwindow_incfs.do_hover_centerbuttons(this.id);}
		alertspan.onmouseout = function() {newwindow_incfs.do_plain_centerbuttons(this.id);}
		alertspan.onclick = function() {alert("Ringfence alert responder " + theID);}
		alertspan.innerHTML = "Ringfence Alert , Responder - <SPAN class='text_large' style='color: red; background-color: yellow; font-weight: bold;'>" + handle + "</SPAN>";
		alertsdiv.appendChild(alertspan);
		}
	catch(err) {
		// Do nothing
		}
	}
	
function exclusion_alert(respid) {
	var the_responders = JSON.decode(window.theResponderHandles);
	try {
		if(newwindow_incfs && newwindow_incfs.$('alerts').style.display == "none") {
			newwindow_incfs.$('alerts').style.display="block";
			}
		var theID = "ex_resp_" + respid;
		if($(newwindow_incfs.$(theID))) {
			return;
			}
		var handle = the_responders[respid];
		var alertsdiv = newwindow_incfs.$('alerts');
		var alertspan = document.createElement('span');
		alertspan.id = theID;
		alertspan.className = "plain_centerbuttons text";
		alertspan.style.float = "left";
		alertspan.style.width = "100px";
		alertspan.style.margin = "10px";
		alertspan.onmouseover = function() {newwindow_incfs.do_hover_centerbuttons(this.id);}
		alertspan.onmouseout = function() {newwindow_incfs.do_plain_centerbuttons(this.id);}
		alertspan.onclick = function() {alert("Exclusion Zone alert responder " + theID);}	
		alertspan.innerHTML = "Exclusion Zone Alert , Responder - <SPAN class='text_large' style='color: red; background-color: yellow; font-weight: bold;'>" + handle + "</SPAN>";
		alertsdiv.appendChild(alertspan);
		}
	catch(err) {
		// Do nothing
		}
	}

function isInteger(s) {
	return (s.toString().search(/^-?[0-9]+$/) == 0);
	}

function do_destroy() {
	for(var key in rmarkers) {
		if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
		}
	}

var changed_resp_sort = false;

function set_resp_headers(id, header_text, the_bull) {
	if(id == "r1") {
		window.r1_text = header_text + the_bull;
		window.r2_text = textHandle;
		window.r3_text = textName;
		window.r4_text = textMail;
		window.r5_text = textIncs;
		window.r6_text = textStatus;
		window.r7_text = textM;
		window.r8_text = textAsof;
		} else if(id == "r2") {
		window.r2_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r3_text = textName;
		window.r4_text = textMail;
		window.r5_text = textIncs;
		window.r6_text = textStatus;
		window.r7_text = textM;
		window.r8_text = textAsof;
		} else if(id == "r3") {
		window.r3_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r2_text = textHandle;
		window.r4_text = textMail;
		window.r5_text = textIncs;
		window.r6_text = textStatus;
		window.r7_text = textM;
		window.r8_text = textAsof;
		} else if(id == "r4") {
		window.r4_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r2_text = textHandle;
		window.r3_text = textName;		
		window.r5_text = textIncs;
		window.r6_text = textStatus;
		window.r7_text = textM;
		window.r8_text = textAsof;
		} else if(id == "r5") {
		window.r5_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r2_text = textHandle;
		window.r3_text = textName;
		window.r4_text = textMail;
		window.r6_text = textStatus;
		window.r7_text = textM;
		window.r8_text = textAsof;
		} else if(id == "r6") {
		window.r6_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r2_text = textHandle;
		window.r3_text = textName;
		window.r4_text = textMail;
		window.r5_text = textIncs;
		window.r7_text = textM;
		window.r8_text = textAsof;
		} else if(id == "r7") {
		window.r7_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r2_text = textHandle;
		window.r3_text = textName;
		window.r4_text = textMail;
		window.r5_text = textIncs;
		window.r8_text = textAsof;
		} else if(id == "r8") {
		window.r8_text = header_text + the_bull;
		window.r1_text = textIcon;
		window.r2_text = textHandle;
		window.r3_text = textName;
		window.r4_text = textMail;
		window.r5_text = textIncs;
		window.r6_text = textStatus;
		window.r7_text = textM;		
		}
	}
	
function do_resp_sort(id, field, header_text) {
	if($('spinner_r')) {
		$('spinner_r').style.display = "block";
		$('spinner_r').innerHTML = "<CENTER><IMG src='./images/animated_spinner.gif'></CENTER>";
		}
	var params = "f_n=sitresp_sort&v_n=" + field + "&sess_id=" + sess_id;
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, gb_handleResult, params);
	window.changed_resp_sort = true;
	if(window.resp_field == field) {
		if(window.resp_direct == "ASC") {
			window.resp_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.resp_header = header_text;
			window.resp_field = field;
			set_resp_headers(id, header_text, the_bull);
			} else if(window.resp_direct == "DESC") { 
			window.resp_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.resp_header = header_text; 
			window.resp_field = field;
			set_resp_headers(id, header_text, the_bull);
			}
		} else {
		$(resp_id).innerHTML = resp_header;
		window.resp_field = field;
		window.resp_direct = "ASC";
		window.resp_id = id;
		window.resp_header = header_text;
		var the_bull = "&#9650";
		set_resp_headers(id, header_text, the_bull);
		}
	var params = "f_n=sitresp_direct&v_n=" + window.resp_direct + "&sess_id=" + sess_id;
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, gb_handleResult, params);
	load_responderlist(window.resp_field, window.resp_direct);
	return true;
	}

function load_responderlist(sort, dir) {
	var resp_assigns = JSON.decode(window.theAssigns);
	window.respFin = false;
	window.respLoading = true;
	if(sort != window.resp_field) {
		window.resp_field = sort;
		}
	if(dir != window.resp_direct) {
		window.resp_direct = dir;
		}
	if($('the_rlist').innerHTML == "") {
		$('the_rlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/sit_responders.php?sort='+window.resp_field+'&dir='+ window.resp_direct+'&screen=sit&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,responderlist_cb, "");		
	function responderlist_cb(req) {
		var i = 1;
		var responder_number = 0;
		var resp_arr = JSON.decode(req.responseText);
		if(!resp_arr) {
			if(doDebug) {
				log_debug(req.responseText); 
				sendInfo(req.responseText);
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......Error loading Responder list.........</marquee>";
			$('the_rlist').innerHTML = outputtext;
			window.respFin = true;
			pageLoaded();
			return;
			}
		if((resp_arr[0]) && (resp_arr[0][0] == 0)) {
			for(var key in rmarkers) {
				if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Units to view.........</marquee>";
			$('the_rlist').innerHTML = outputtext;
			window.latest_responder = 0;
			window.respFin = true;
			pageLoaded();
			} else {
			var outputtext = "<TABLE id='respondertable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.leftlistwidth + "px; background-color: #EFEFEF;'>";
			outputtext += "<TH id='r1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + iconTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'icon', '" + textIcon + "')\">" + window.r1_text + "</TH>";
			outputtext += "<TH id='r2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + handleTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'handle', '" + textHandle + "')\">" + window.r2_text + "</TH>";
			outputtext += "<TH id='r3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + nameTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'name', '" + textName + "')\">" + window.r3_text + "</TH>";
			outputtext += "<TH id='r4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + emailTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'mail', '" + textMail + "')\">" + window.r4_text + "</TH>";
			outputtext += "<TH id='r5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + incsTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'incidents', '" + textIncs + "')\">" + window.r5_text + "</TH>";
			outputtext += "<TH id='r6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + statusTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'status', '" + textStatus + "')\">" + window.r6_text + "</TH>";
			outputtext += "<TH id='r7' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + trackingTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'm', '" + textM + "')\">" + window.r7_text + "</TH>";
			outputtext += "<TH id='r8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + respUpdTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'asof', '" + textAsof + "')\">" + window.r8_text + "</TH>";
			outputtext += "<TH id='r9'>" + pad(5, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in resp_arr) {
				if(key != 0) {
					if(resp_arr[key][2]) {
						var unit_id = resp_arr[key][2];
						var unit_no = resp_arr[key][17];
						var theIndx = i-1;
						window.theResponders[theIndx] = unit_no;
						if(resp_arr[key][11] != "") {
							var theMailBut = pad(10, "<DIV style='text-align: center;'><IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit " + resp_arr[key][1] + "' onclick = 'do_mail_win(\"" + unit_no + "\");'></DIV>", "\u00a0");
							} else {
							var theMailBut = pad(10, "", "\u00a0");
							}
						if(resp_arr[key][26] != "") {
							 var theTip = " onMouseover=\"Tip('" + htmlentities(resp_arr[key][26], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
							 } else {
							 var theTip = "";
							 }
						var bg_color = resp_arr[key][7];
						var fg_color = resp_arr[key][8];
						outputtext += "<TR id='" + resp_arr[key][20] + unit_no +"' CLASS='" + colors[i%2] +"' style='width: " + window.listwidth + "px;'>";
						outputtext += "<TD id='ricon_" + unit_no + "' class='plain_list text' style=\"background-color: " + bg_color + "; color: " + fg_color + ";\" onClick='myrclick(" + unit_no + ");'>" + unit_id + "</TD>";
						var nameTip = "onMouseover=\"Tip('" + htmlentities(resp_arr[key][0], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
						outputtext += "<TD id='rhand_" + unit_no + "' class='plain_list text_bolder' " + nameTip + " onClick='myrclick(" + unit_no + ");'>" + pad(7, htmlentities(resp_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</TD>";
						outputtext += "<TD id='rname_" + unit_no + "' class='plain_list text_bolder' " + nameTip + " onClick='myrclick(" + unit_no + ");'>" + pad(10, htmlentities(resp_arr[key][12], 'ENT_QUOTES'), "\u00a0") + "</TD>";
						outputtext += "<TD id='rmail_" + unit_no + "' class='plain_list text_bolder'>" + theMailBut + "</TD>";
						outputtext += "<TD id='rincs_" + unit_no + "' class='plain_list text_bolder' onClick='myrclick(" + unit_no + ");'>" + pad(30, get_assigns(unit_no), "\u00a0") + "</TD>";
						if(resp_arr[key][30] == 0) {
							outputtext += "<TD id='rs_" + unit_no + "' class='plain_list text' " + theTip + ">" + get_status_sel(unit_no, resp_arr[key][15], resp_arr[key][1]) + "</TD>";
							} else {
							outputtext += "<TD id='rs_" + unit_no + "' class='plain_list text' " + theTip + ">" + pad(20, resp_arr[key][23], "\u00a0") + "</TD>";
							}
						outputtext += "<TD id='rmob_" + unit_no + "' class='plain_list text' onClick='myrclick(" + unit_no + ");'>" +  pad(5, resp_arr[key][13], "\u00a0") + "</TD>";
						outputtext += "<TD id='rsupd_" + unit_no + "' class='plain_list text' onClick='myrclick(" + unit_no + ");'><SPAN id = '" + resp_arr[key][27] + "'>" + resp_arr[key][16] + "</SPAN></TD>";
						outputtext += "<TD class='plain_list text'>" + pad(5, " ", "\u00a0") + "</TD>";
						outputtext += "</TR>";
						
						if(resp_assigns[unit_no] && resp_assigns[unit_no].length != 0) {
							theAssigned[unit_no] = true; 
							infowindowtext = "<B>" + resp_arr[key][2] + "</B><BR />" + get_assigns_flag(unit_no);
							} else {
							infowindowtext = " ";
							}
						if(window.responders_updated[resp_arr[key][17]]) {
							if(window.responders_updated[resp_arr[key][17]] != resp_arr[key][16]) {
								window.do_resp_update = true;
								} else {
								window.do_resp_update = false;
								}
							} else {
							window.responders_updated[resp_arr[key][17]] = resp_arr[key][16];
							window.do_resp_update = true;
							}
											
						if($('map_canvas')) {						
							if(rmarkers[unit_no]) {
								if(window.changed_resp_sort == false) {
									var curPos = rmarkers[unit_no].getLatLng();
									if((curPos.lat != resp_arr[key][3]) || (curPos.lng != resp_arr[key][4])) {
										theLatLng = new L.LatLng(resp_arr[key][3], resp_arr[key][4]);
										rmarkers[unit_no].setLatLng(theLatLng);
										}
									}
								} else {
								if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
									var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_no, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
									marker.addTo(map);
									} else {
									var marker = createdummyUnitMarker(def_lat, def_lng, infowindowtext, "", resp_arr[key][0], unit_no);
									marker.addTo(map);
									}
								}
							}
						responder_number = unit_no;
						}
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				if(window.numAssigns != 0) {
					if($('show_asgn')) {
						$('show_asgn').style.color = "#000000";
						$('show_asgn_img').style.opacity = "1";
						$('show_asgn').onclick = function() {do_assignment_flags();};
						$('show_asgn').onmouseover = function() {do_hover_centerbuttons(this.id); Tip("Click to show flags for assigned units or hide flags");};
						$('show_asgn').onmouseout = function() {do_plain_centerbuttons(this.id); UnTip();};
						$('show_asgn').style.cursor = "pointer";
						}
					if($('fs_show_asgn')) {
						$('fs_show_asgn').style.color = "#000000";
						$('fs_show_asgn_img').style.opacity = "1";
						$('fs_show_asgn').onclick = function() {do_fs_assignment_flags();};
						$('fs_show_asgn').onmouseover = function() {do_hover(this.id); Tip("Click to show flags for assigned units or hide flags");};
						$('fs_show_asgn').onmouseout = function() {do_plain(this.id); UnTip();};
						$('fs_show_asgn').style.cursor = "pointer";
						}					
					} else {
					if($('show_asgn')) {
						$('show_asgn').style.color = "#CFCFCF";
						$('show_asgn_img').style.opacity = "0.3";
						$('show_asgn').onclick = null;
						$('show_asgn').onmouseover = null;
						$('show_asgn').onmouseout = null;
						$('show_asgn').style.cursor = "default";				
						}
					if($('fs_show_asgn')) {
						$('fs_show_asgn').style.color = "#CFCFCF";
						$('fs_show_asgn_img').style.opacity = "0.3";
						$('fs_show_asgn').onclick = null;
						$('fs_show_asgn').onmouseover = null;
						$('fs_show_asgn').onmouseout = null;
						$('fs_show_asgn').style.cursor = "default";				
						}
					}
				if(window.resp_last_display == 0) {		//	first load
					$('the_rlist').innerHTML = outputtext;
					if($('boxes')) {set_categories();}
					} else {
					if((responder_number != window.latest_responder) || (window.do_resp_update == true) || (window.changed_resp_sort == true) || (window.do_resp_refresh == true)) {	//	data changed
						$('the_rlist').innerHTML = "";
						$('the_rlist').innerHTML = outputtext;
						if($('boxes')) {set_categories();}
						window.respFin = window.facFin = window.incFin = window.logFin = window.facstatSel = true;
						window.statSel = false;
						if(window.changed_resp_sort == true) {
							if($('spinner_r')) {
								$('spinner_r').innerHTML = "";
								$('spinner_r').style.display = "none";
								}
							}		
						}
					}
				for(var key in resp_arr) {
					if(parseFloat(resp_arr[key][3]) && parseFloat(resp_arr[key][4])) {
						if(parseInt(resp_arr[key][28]) != 0) {check_excl(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						if(parseInt(resp_arr[key][29]) != 0) {check_ringfence(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						}
					}
				var resptbl = document.getElementById('respondertable');
				if(resptbl) {
					var headerRow = resptbl.rows[0];
					var tableRow = resptbl.rows[1];
					if(tableRow) {
						for (var i = 0; i < tableRow.cells.length; i++) {
							if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
							}
						} else {
						var cellwidthBase = window.listwidth / 8;
						for (var i = 0; i < tableRow.cells.length; i++) {		
							headerRow.cells[0].style.width = cellwidthBase + "px";
							}
						}
					if((window.resp_last_display == 0) || (responder_number != window.latest_responder) || (window.do_resp_update == true) || (window.changed_resp_sort == true) || (window.do_resp_refresh == true)) {
						if(getHeaderHeight(headerRow) >= listheader_height) {
							var theRow = resptbl.insertRow(1);
							theRow.style.height = "20px";
							for (var i = 0; i < tableRow.cells.length; i++) {
								var theCell = theRow.insertCell(i);
								theCell.innerHTML = " ";
								}
							}
						}
					}
				window.resp_last_display = resp_arr[0][23];
				window.respFin = true;
				window.statSel = true;
				window.latest_responder = responder_number;
				pageLoaded();
				window.do_resp_refresh = false;
				window.changed_resp_sort = false;
				},3000);
			}
		window.respLoading = false;
		}				// end function responderlist_cb()
	}				// end function load_responderlist()

function isViewable(element){
	return (element.clientHeight > 0);
	}
	
function responderlist_setwidths() {
	var viewableRow = 1;
	var resptbl = document.getElementById('respondertable');
	if(!resptbl) {return;}
	var headerRow = resptbl.rows[0];
	for (i = 1; i < resptbl.rows.length; i++) {
		if(!isViewable(resptbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = resptbl.rows[viewableRow];
	if(tableRow &&i != resptbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
			}
		} else {
		var cellwidthBase = window.listwidth / 8;
		for (var i = 0; i < tableRow.cells.length; i++) {		
			headerRow.cells[0].style.width = cellwidthBase + "px";
			}
		}
	if(getHeaderHeight(headerRow) >= listheader_height) {
		var theRow = resptbl.insertRow(1);
		theRow.style.height = "20px";
		for (var i = 0; i < tableRow.cells.length; i++) {
			var theCell = theRow.insertCell(i);
			theCell.innerHTML = " ";
			}
		}
	}
	
function responderlist_get() {
	if (r_interval!=null) {return;}
	if(window.incLoading == true || window.respFin == true || window.facLoading == true || window.logLoading == true) {return;}
	r_interval = window.setInterval('responderlist_loop()', 120000); 
	}			// end function mu get()

function responderlist_loop() {
	load_responderlist(resp_field, resp_direct);
	}			// end function do_loop()
	
//	Responderlist for Units screen

function set_resp_headers2(id, header_text, the_bull) {
	if(id == "r1") {
		window.rr1_text = header_text + the_bull;
		window.rr2_text = textName;
		window.rr3_text = textMail;
		window.rr4_text = textIncs;
		window.rr5_text = textStatus;
		window.rr6_text = textStatusAbout;
		window.rr7_text = textM;
		window.rr8_text = textAsof;
		} else if(id == "r2") {
		window.rr2_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr3_text = textMail;
		window.rr4_text = textIncs;
		window.rr5_text = textStatus;
		window.rr6_text = textStatusAbout;
		window.rr7_text = textM;
		window.rr8_text = textAsof;
		} else if(id == "r3") {
		window.rr3_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr2_text = textName;
		window.rr4_text = textIncs;
		window.rr5_text = textStatus;
		window.rr6_text = textStatusAbout;
		window.rr7_text = textM;
		window.rr8_text = textAsof;
		} else if(id == "r4") {
		window.rr4_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr2_text = textName;
		window.rr3_text = textMail;
		window.rr5_text = textStatus;
		window.rr6_text = textStatusAbout;
		window.rr7_text = textM;
		window.rr8_text = textAsof;
		} else if(id == "r5") {
		window.rr5_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr2_text = textName;
		window.rr3_text = textMail;
		window.rr4_text = textIncs;
		window.rr6_text = textStatusAbout;
		window.rr7_text = textM;
		window.rr8_text = textAsof;
		} else if(id == "r6") {
		window.rr6_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr2_text = textName;
		window.rr3_text = textMail;
		window.rr4_text = textIncs;
		window.rr5_text = textStatus;
		window.rr7_text = textM;
		window.rr8_text = textAsof;
		} else if(id == "r7") {
		window.rr7_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr2_text = textName;
		window.rr3_text = textMail;
		window.rr4_text = textIncs;
		window.rr5_text = textStatus;
		window.rr6_text = textStatusAbout;
		window.rr8_text = textAsof;
		} else if(id == "r8") {
		window.rr8_text = header_text + the_bull;
		window.rr1_text = textIcon;
		window.rr2_text = textName;
		window.rr3_text = textMail;
		window.rr4_text = textIncs;
		window.rr5_text = textStatus;
		window.rr6_text = textStatusAbout;
		window.rr7_text = textM;
		}
	}
	
function do_resp_sort2(id, field, header_text) {
	if($('spinner_r')) {
		$('spinner_r').style.display = "block";
		$('spinner_r').innerHTML = "<CENTER><IMG src='./images/animated_spinner.gif'></CENTER>";
		}
	var params = "f_n=respresp_sort&v_n=" + field + "&sess_id=" + sess_id;
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, gb_handleResult, params);
	window.changed_resp_sort = true;
	if(window.resp_field == field) {
		if(window.resp_direct == "ASC") {
			window.resp_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.resp_header = header_text;
			window.resp_field = field;
			set_resp_headers2(id, header_text, the_bull);
			} else if(window.resp_direct == "DESC") { 
			window.resp_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.resp_header = header_text; 
			window.resp_field = field;
			set_resp_headers2(id, header_text, the_bull);
			}
		} else {
		$(resp_id).innerHTML = resp_header;
		window.resp_field = field;
		window.resp_direct = "ASC";
		window.resp_id = id;
		window.resp_header = header_text;
		var the_bull = "&#9650";

		set_resp_headers2(id, header_text, the_bull);
		}
	var params = "f_n=respresp_direct&v_n=" + window.resp_direct + "&sess_id=" + sess_id;
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, gb_handleResult, params);
	load_responderlist2(window.resp_field, window.resp_direct);
	return true;
	}

function load_responderlist2(sort, dir) {
	var resp_assigns = JSON.decode(window.theAssigns);
	window.statSel = false;
	window.respFin = false;
	if(sort != window.resp_field) {
		window.resp_field = sort;
		}
	if(dir != window.resp_direct) {
		window.resp_direct = dir;
		}
	if($('the_rlist').innerHTML == "") {
		$('the_rlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/sit_responders.php?sort='+window.resp_field+'&dir='+ window.resp_direct+'&screen=responder&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,responderlist2_cb, "");		
	function responderlist2_cb(req) {
		var i = 1;
		var responder_number = 0;	
		var resp_arr = JSON.decode(req.responseText);
		if(!resp_arr) {
			if(doDebug) {
				log_debug(req.responseText); 
				sendInfo(req.responseText);
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......Error loading Responder list.........</marquee>";
			$('the_rlist').innerHTML = outputtext;
			window.respFin = true;
			window.statSel = false;
			pageLoaded();
			return;
			}
		if((resp_arr[0]) && (resp_arr[0][0] == 0)) {
			for(var key in rmarkers) {
				if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Units to view.........</marquee>";
			$('the_rlist').innerHTML = outputtext;
			if($('boxes')) {$('boxes').innerHTML = resp_arr[0][19];}
			window.latest_responder = 0;
			window.respFin = true;
			window.statSel = false;
			pageLoaded();
			} else {
			var outputtext = "<TABLE id='respondertable' class='fixedheadscrolling scrollable' style='width: " + window.leftlistwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.leftlistwidth + "px; background-color: #EFEFEF;'>";
			outputtext += "<TH id='r1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + iconTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'icon', '" + textIcon + "')\">" + window.rr1_text + "</TH>";
			outputtext += "<TH id='r2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + nameTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'name', '" + textName + "')\">" + window.rr2_text + "</TH>";
			outputtext += "<TH id='r3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + emailTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'mail', '" + textMail + "')\">" + window.rr3_text + "</TH>";
			outputtext += "<TH id='r4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + incsTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'incidents', '" + textIncs + "')\">" + window.rr4_text + "</TH>";
			outputtext += "<TH id='r5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + statusTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'status', '" + textStatus + "')\">" + window.rr5_text + "</TH>";
			outputtext += "<TH id='r6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + statusAboutTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'sa', '" + textStatusAbout + "')\">" + window.rr6_text + "</TH>";
			outputtext += "<TH id='r7' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + trackingTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'm', '" + textM + "')\">" + window.rr7_text + "</TH>";
			outputtext += "<TH id='r8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + respUpdTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'asof', '" + textAsof + "')\">" + window.rr8_text + "</TH>";
			outputtext += "<TH id='r9'>" + pad(2, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in resp_arr) {
				if(key != 0) {
					if((i == 1) && ((window.dzf == 0) || (window.dzf == 1))) {
 						var thePoint = L.latLng(resp_arr[key][3],resp_arr[key][4]);
						window.bounds = L.latLngBounds(thePoint);
						}
					if(resp_arr[key][2]) {
						var unit_id = resp_arr[key][2];
						var unit_no = resp_arr[key][17];
						var theIndx = i-1;
						window.theResponders[theIndx] = unit_no;
						if(resp_arr[key][11] != "") {
							var theMailBut = pad(8, "<DIV style='text-align: center;'><IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit " + resp_arr[key][1] + "' onclick = 'do_mail_win(\"" + unit_no + "\");'></DIV>", "\u00a0");
							} else {
							var theMailBut = pad(8, "", "\u00a0");
							}
						if(resp_arr[key][26] != "") {
							 var theTip = " onMouseover=\"Tip('" + htmlentities(resp_arr[key][26], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
							 } else {
							 var theTip = "";
							 }
						var bg_color = resp_arr[key][7];
						var fg_color = resp_arr[key][8];
						outputtext += "<TR id='" + resp_arr[key][20] + unit_no +"' CLASS='plain_list text " + colors[i%2] +"' style='width: " + window.leftlistwidth + "px;'>";
						outputtext += "<TD id='ricon_" + unit_no + "' class='plain_list text_bolder' style='background-color: " + bg_color + "; color: " + fg_color + ";' onClick='myrclick(" + unit_no + ");'>" + resp_arr[key][2] + "</TD>";
						if(use_mdb == "1" && use_mdb_contact == "1") {
							var nameTip = "onMouseover=\"Tip('" + htmlentities(resp_arr[key][1], 'ENT_QUOTES') + " - " + htmlentities(resp_arr[key][0], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
							outputtext += "<TD id='rname_" + unit_no + "' class='plain_list text' " + nameTip + " onClick='myrclick(" + unit_no + ");'>" + htmlentities(resp_arr[key][12], 'ENT_QUOTES') + "</TD>";
							} else {
							outputtext += "<TD id='rname_" + unit_no + "' class='plain_list text' onClick='myrclick(" + unit_no + ");'>" + htmlentities(resp_arr[key][0], 'ENT_QUOTES') + "</TD>";
							}
						outputtext += "<TD id='rmail_" + unit_no + "' class='plain_list text_bolder' style='text-align: center;'>" + theMailBut + "</TD>";
						outputtext += "<TD id='rincs_" + unit_no + "' class='plain_list text_bolder' onClick='myrclick(" + unit_no + ");'>" + get_assigns(unit_no) + "</TD>";
						outputtext += "<TD id='rs_" + unit_no + "' class='plain_list text' " + theTip + ">" + get_status_sel(unit_no, resp_arr[key][15], resp_arr[key][1]) + "</TD>";
						var status_about = pad(22, html_entity_decode(resp_arr[key][26], 'ENT_QUOTES'), "\u00a0")
						outputtext += "<TD id='rsab_" + unit_no + "' class='plain_list text' " + theTip + " onClick='myrclick(" + unit_no + ");'>" + status_about.trunc(20) + "</TD>";
						outputtext += "<TD id='rmob_" + unit_no + "' class='plain_list text' onClick='myrclick(" + unit_no + ");'>" +  pad(6, resp_arr[key][13], "\u00a0") + "</TD>";
						var theFlag = resp_arr[key][27];
						outputtext += "<TD id='rsupd_" + unit_no + "' class='plain_list text' onClick='myrclick(" + unit_no + ");'><SPAN id = '" + theFlag + "' style='white-space: nowrap;'>" + pad(2, resp_arr[key][16], "\u00a0") + "</SPAN></TD>";
						outputtext += "<TD class='plain_list text'>" + pad(12, " ", "\u00a0") + "</TD>";
						outputtext += "</TR>";
						if(resp_assigns[unit_no] && resp_assigns[unit_no].length != 0) {
							theAssigned[unit_no] = true; 
							infowindowtext = "<B>" + resp_arr[key][2] + "</B><BR />" + get_assigns_flag(unit_no);
							} else {
							infowindowtext = " ";
							}
						if(window.responders_updated[resp_arr[key][17]]) {
							if(window.responders_updated[resp_arr[key][17]] != resp_arr[key][16]) {
								window.do_resp_update = true;
								} else {
								window.do_resp_update = false;
								}
							} else {
							window.responders_updated[resp_arr[key][17]] = resp_arr[key][16];
							window.do_resp_update = true;
							}
						if($('map_canvas')) {
							if(rmarkers[unit_no]) {
								if(window.changed_resp_sort == false) {
									var curPos = rmarkers[unit_no].getLatLng();
									if((curPos.lat != resp_arr[key][3]) || (curPos.lng != resp_arr[key][4])) {
										theLatLng = new L.LatLng(resp_arr[key][3], resp_arr[key][4]);
										rmarkers[unit_no].setLatLng(theLatLng);
										}
									}
								} else {
								if($('map_canvas')) {
									if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
										var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_no, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
										marker.addTo(map);
										} else {
										var marker = createdummyUnitMarker(def_lat, def_lng, infowindowtext, "", resp_arr[key][0], unit_no);
										marker.addTo(map);
										}
									}
								}
							}
						responder_number = unit_no;
						}
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				if(window.numAssigns != 0) {
					if($('show_asgn')) {
						$('show_asgn').style.color = "#000000";
						$('show_asgn_img').style.opacity = "1";
						$('show_asgn').onclick = function() {do_assignment_flags();};
						$('show_asgn').onmouseover = function() {do_hover_centerbuttons(this.id); Tip("Click to show flags for assigned units or hide flags");};
						$('show_asgn').onmouseout = function() {do_plain_centerbuttons(this.id); UnTip();};
						$('show_asgn').style.cursor = "pointer";
						}
					if($('fs_show_asgn')) {
						$('fs_show_asgn').style.color = "#000000";
						$('fs_show_asgn_img').style.opacity = "1";
						$('fs_show_asgn').onclick = function() {do_fs_assignment_flags();};
						$('fs_show_asgn').onmouseover = function() {do_hover(this.id); Tip("Click to show flags for assigned units or hide flags");};
						$('fs_show_asgn').onmouseout = function() {do_plain(this.id); UnTip();};
						$('fs_show_asgn').style.cursor = "pointer";
						}					
					} else {
					if($('show_asgn')) {
						$('show_asgn').style.color = "#CFCFCF";
						$('show_asgn_img').style.opacity = "0.3";
						$('show_asgn').onclick = null;
						$('show_asgn').onmouseover = null;
						$('show_asgn').onmouseout = null;
						$('show_asgn').style.cursor = "default";				
						}
					if($('fs_show_asgn')) {
						$('fs_show_asgn').style.color = "#CFCFCF";
						$('fs_show_asgn_img').style.opacity = "0.3";
						$('fs_show_asgn').onclick = null;
						$('fs_show_asgn').onmouseover = null;
						$('fs_show_asgn').onmouseout = null;
						$('fs_show_asgn').style.cursor = "default";				
						}
					}
				if(window.resp_last_display == 0) {		//	first display
					$('the_rlist').innerHTML = outputtext;
					if($('boxes')) {$('boxes').innerHTML = resp_arr[0][21];}
					if($('boxes')) {set_categories();}
					} else {
					if((responder_number != window.latest_responder) || (window.do_resp_update == true) || (window.changed_resp_sort == true) || (window.do_resp_refresh == true)) {	//	Data changed
						$('the_rlist').innerHTML = outputtext;
						if($('boxes')) {$('boxes').innerHTML = resp_arr[0][21];}
						if($('boxes')) {set_categories();}
						window.respFin = window.facFin = window.incFin = window.logFin = window.facstatSel = true;
						window.statSel = false;
						if(window.changed_resp_sort == true) {
							if($('spinner_r')) {
								$('spinner_r').innerHTML = "";
								$('spinner_r').style.display = "none";
								}
							}	
						}
					}
				for(var key in resp_arr) {
					if(parseFloat(resp_arr[key][3]) && parseFloat(resp_arr[key][4])) {
						if(parseInt(resp_arr[key][28]) != 0) {check_excl(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						if(parseInt(resp_arr[key][29]) != 0) {check_ringfence(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						}
					}
				var resptbl = document.getElementById('respondertable');
				if(resptbl) {
					var headerRow = resptbl.rows[0];
					var tableRow = resptbl.rows[1];
					if(tableRow) {
						for (var i = 0; i < tableRow.cells.length; i++) {
							if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
							}
						} else {
						var cellwidthBase = window.listwidth / 28;
						cell1 = cellwidthBase * 2;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 5;
						cell5 = cellwidthBase * 5;
						cell6 = cellwidthBase * 3;
						cell7 = cellwidthBase * 4;
						cell8 = cellwidthBase * 1;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";						
						headerRow.cells[4].style.width = cell5 + "px";							
						headerRow.cells[5].style.width = cell6 + "px";						
						headerRow.cells[6].style.width = cell7 + "px";		
						headerRow.cells[7].style.width = cell8 + "px";		
						}
					if((window.resp_last_display == 0) || (responder_number != window.latest_responder) || (window.do_resp_update == true) || (window.changed_resp_sort == true) || (window.do_resp_refresh == true)) {
						if(getHeaderHeight(headerRow) >= listheader_height) {
							var theRow = resptbl.insertRow(1);
							theRow.style.height = "20px";
							for (var i = 0; i < tableRow.cells.length; i++) {
								var theCell = theRow.insertCell(i);
								theCell.innerHTML = " ";
								}
							}
						}
					}
				window.resp_last_display = resp_arr[0][23];
				window.respFin = true;
				window.statSel = true;
				window.latest_responder = responder_number;
				pageLoaded();
				window.do_resp_refresh = false;
				window.changed_resp_sort = false;
				responderlist2_get();
				},500);
			}
		}				// end function responderlist_cb()
	}				// end function load_responderlist()

function responderlist2_setwidths() {
	var viewableRow = 1;
	var resptbl = document.getElementById('respondertable');
	var headerRow = resptbl.rows[0];
	for (i = 1; i < resptbl.rows.length; i++) {
		if(!isViewable(resptbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = resptbl.rows[viewableRow];
	if(tableRow &&i != resptbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
			}
		} else {
		var cellwidthBase = window.listwidth / 28;
		cell1 = cellwidthBase * 2;
		cell2 = cellwidthBase * 4;
		cell3 = cellwidthBase * 4;
		cell4 = cellwidthBase * 5;
		cell5 = cellwidthBase * 5;
		cell6 = cellwidthBase * 3;
		cell7 = cellwidthBase * 4;
		cell8 = cellwidthBase * 1;
		headerRow.cells[0].style.width = cell1 + "px";
		headerRow.cells[1].style.width = cell2 + "px";
		headerRow.cells[2].style.width = cell3 + "px";
		headerRow.cells[3].style.width = cell4 + "px";
		headerRow.cells[4].style.width = cell5 + "px";
		headerRow.cells[5].style.width = cell6 + "px";
		headerRow.cells[6].style.width = cell7 + "px";
		headerRow.cells[7].style.width = cell8 + "px";
		}
	if(getHeaderHeight(headerRow) >= listheader_height) {
		var theRow = resptbl.insertRow(1);
		theRow.style.height = "10px";
		for (var i = 0; i < tableRow.cells.length; i++) {
			var theCell = theRow.insertCell(i);
			theCell.innerHTML = " ";
			}
		}
	}
	
function responderlist2_get() {
	if (r_interval != null) {return;}
	r_interval = window.setInterval('responderlist2_loop()', 6000); 
	}			// end function mu get()

function responderlist2_loop() {
	load_responderlist2(resp_field, resp_direct);
	}			// end function do_loop()

// end


var changed_fac_sort = false;

function set_fac_headers(id, header_text, the_bull) {
	if(id == "f1") {
		window.f1_text = header_text + the_bull;
		window.f2_text = textFacName;
		window.f3_text = textFacMail;		
		window.f4_text = textFacStatus;
		window.f5_text = textFacUpdated;
		} else if(id == "f2") {
		window.f2_text = header_text + the_bull;
		window.f1_text = textFacIcon;
		window.f3_text = textFacMail;		
		window.f4_text = textFacStatus;
		window.f5_text = textFacUpdated;
		} else if(id == "f3") {
		window.f3_text = header_text + the_bull;
		window.f1_text = textFacIcon;
		window.f2_text = textFacName;
		window.f4_text = textFacStatus;
		window.f5_text = textFacUpdated;
		} else if(id == "f4") {
		window.f4_text = header_text + the_bull;
		window.f1_text = textFacIcon;
		window.f2_text = textFacName;
		window.f3_text = textFacMail;	
		window.f5_text = textFacUpdated;
		} else if(id == "f5") {
		window.f5_text = header_text + the_bull;
		window.f1_text = textFacIcon;
		window.f2_text = textFacName;
		window.f3_text = textFacMail;	
		window.f4_text = textFacStatus;
		}
	}
	
function do_fac_sort(id, field, header_text) {
	if($('spinner_f')) {
		$('spinner_f').style.display = "block";
		$('spinner_f').innerHTML = "<CENTER><IMG src='./images/animated_spinner.gif'></CENTER>";
		}
	var params = "f_n=fac_sort&v_n=" + field + "&sess_id=" + sess_id;
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, gb_handleResult, params);
	window.changed_fac_sort = true;
	if(window.fac_field == field) {
		if(window.fac_direct == "ASC") {
			window.fac_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.fac_header = header_text;
			window.fac_field = field;
			set_fac_headers(id, header_text, the_bull);
			} else if(window.fac_direct == "DESC") { 
			window.fac_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.fac_header = header_text; 
			window.fac_field = field;
			set_fac_headers(id, header_text, the_bull);
			}
		} else {
		$(fac_id).innerHTML = fac_header;
		window.fac_field = field;
		window.fac_direct = "ASC";
		window.fac_id = id;
		window.fac_header = header_text;
		var the_bull = "&#9650";
		set_fac_headers(id, header_text, the_bull);
		}
	var params = "f_n=fac_direct&v_n=" + window.fac_direct + "&sess_id=" + sess_id;
	var url = "persist2.php";	//	3/15/11
	sendRequest (url, gb_handleResult, params);
	load_facilitylist(fac_field, fac_direct);
	return true;
	}
	
function load_facilitylist(sort, dir) {
	window.facstatSel = false;
	window.facFin = false;
	window.facLoading = true;
	if(sort != window.fac_field) {
		window.fac_field = sort;
		}
	if(dir != window.fac_direct) {
		window.fac_direct = dir;
		}
	if($('the_flist').innerHTML == "") {
		$('the_flist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/sit_facilities.php?sort='+window.fac_field+'&dir='+ window.fac_direct+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,facilitylist_cb, "");
	function facilitylist_cb(req) {
		var i = 1;
		var facility_number = 0;
		var fac_arr = JSON.decode(req.responseText);
		if(!fac_arr) {
			if(doDebug) {
				log_debug(req.responseText); 
				sendInfo(req.responseText);
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......Error loading Facilities list.........</marquee>";
			$('the_flist').innerHTML = outputtext;
			window.facFin = true;
			window.facstatSel = false;
			pageLoaded();
			return;
			}
		if((fac_arr[0]) && (fac_arr[0][0] == 0)) {
			for(var key in fmarkers) {
				if(fmarkers[key]) {map.removeLayer(fmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Facilities to view.........</marquee>";
			$('the_flist').innerHTML = outputtext;
			if($('fac_boxes')) {$('fac_boxes').innerHTML = fac_arr[0][12];}
			window.latest_facility = 0;
			window.facFin = true;
			window.facstatSel = false;
			pageLoaded();
			} else {
			var outputtext = "<TABLE id='facilitiestable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.leftlistwidth + "px; background-color: #EFEFEF;'>";
			outputtext += "<TH id='f1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + facIconTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'id', '" + textFacIcon + "')\">" + window.f1_text + "</TH>";
			outputtext += "<TH id='f2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + facNameTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'name', '" + textFacName + "')\">" + window.f2_text + "</TH>";
			outputtext += "<TH id='f3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + facEmailTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'mail', '" + textFacMail + "')\">" + window.f3_text + "</TH>";
			outputtext += "<TH id='f4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + facStatusTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'status', '" + textFacStatus + "')\">" + window.f4_text + "</TH>";
			outputtext += "<TH id='f5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + facUpdTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'updated', '" + textFacUpdated + "')\">" + window.f5_text + "</TH>";
			outputtext += "<TH id='f6'>" + pad(3, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in fac_arr) {
				if((key > 0) && (fac_arr[key][2]) && (fac_arr[key][2] != "")) {
					var fac_id = fac_arr[key][10];
					var theIndx = i-1;
					window.theFacilities[theIndx] = fac_id;
					var bg_color = fac_arr[key][5];
					var fg_color = fac_arr[key][6];
					if(fac_arr[key][7] != "") {
						var theMailBut = pad(8, "<DIV style='text-align: center;'><IMG SRC='mail.png' BORDER=0 TITLE = 'click to email facility " + fac_arr[key][0] + "' onclick = 'do_fac_mail_win(\"" + fac_id + "\", \"" + fac_arr[key][7] + "\");'></DIV>", "\u00a0");
						} else {
						var theMailBut = pad(8, "", "\u00a0");
						}
					if(fac_arr[key][16] != "") {
						 var theTip = " onMouseover=\"Tip('" + htmlentities(fac_arr[key][16], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
						 } else {
						 var theTip = "";
						 }
					outputtext += "<TR id='" + fac_arr[key][15] + fac_id +"' TITLE='" + fac_arr[key][16] + "' CLASS='plain_list text " + colors[i%2] + "' style='width: " + window.leftlistwidth + "px;'>";
					outputtext += "<TD class='plain_list text' style=\"background-color: " + bg_color + "; color: " + fg_color + ";\" onClick='myfclick(" + fac_id + ");'>" + fac_arr[key][2] + "</TD>";
					outputtext += "<TD class='plain_list text' style=\"text-align: left;\" onClick='myfclick(" + fac_id + ");'>" + htmlentities(fac_arr[key][0], 'ENT_QUOTES') + "</TD>";
					outputtext += "<TD class='plain_list text'>" + theMailBut + "</TD>";
					outputtext += "<TD id='fs_" + fac_id + "' class='plain_list text' " + theTip + ">" + get_fac_status_sel(fac_id, fac_arr[key][8], fac_arr[key][2]) + "</TD>";
					outputtext += "<TD id='fsupd_" + fac_id + "' class='plain_list text' onClick='myfclick(" + fac_id + ");'>" + fac_arr[key][9] + "</TD>";
					outputtext += "<TD class='plain_list text'>" + pad(3, " ", "\u00a0") + "</TD>";
					outputtext += "</TR>";
					if(window.facilities_updated[fac_id]) {
						if(window.facilities_updated[fac_id] != fac_arr[key][9]) {
							window.do_fac_update = true;
							} else {
							window.do_fac_update = false;
							}
						} else {
						window.facilities_updated[fac_id] = fac_arr[key][9];
						window.do_fac_update = true;
						}
						
					if($('map_canvas')) {						
						if(fmarkers[fac_id]) {
							if(window.changed_resp_sort == false) {
								// not changed sort order but don't refresh markers
								} else {
								// Changed sort order only, don't refresh markers
								}
							} else {
							// new map, no marker
							if(!fac_arr[key][2]) {
								var icon_str = "UNK";
								} else {
								var icon_str = fac_arr[key][2].toString();
								}
							infowindowtext = "";
							if((isFloat(fac_arr[key][3])) && (isFloat(fac_arr[key][4]))) {
								var marker = createFacilityMarker(fac_arr[key][3], fac_arr[key][4], infowindowtext, fac_arr[key][11], 0, fac_id, icon_str,  fac_arr[key][15], 0, "This is a Facility"); // 7/28/10, 3/15/11, 12/23/13
								marker.addTo(map);
								} else {
								var marker = createdummyFacMarker(def_lat, def_lng, infowindowtext, "", fac_arr[key][0], fac_id);
								marker.addTo(map);
								}
							}
						}					
					facility_number = fac_id;
					i++;					
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				if(window.fac_last_display == 0) {
					$('the_flist').innerHTML = outputtext;
					if($('fac_boxes')) {$('fac_boxes').innerHTML = fac_arr[0][12];}
					set_fac_categories();
					} else {
					if((facility_number != window.latest_facility) || (window.do_fac_update == true) || (window.changed_fac_sort == true) || (window.do_fac_refresh == true)) {
						$('the_flist').innerHTML = outputtext;
						if($('fac_boxes')) {$('fac_boxes').innerHTML = fac_arr[0][12];}
						set_fac_categories();
						window.respFin = window.facFin = window.incFin = window.logFin = window.statSel = true;
						window.facstatSel = false;
						if(window.changed_fac_sort == true) {
							if($('spinner_f')) {
								$('spinner_f').innerHTML = "";
								$('spinner_f').style.display = "none";
								}
							}
						}
					}
				var factbl = document.getElementById('facilitiestable');
				if(factbl) {
					var headerRow = factbl.rows[0];
					var tableRow = factbl.rows[1];
					if(tableRow) {
						for (var i = 0; i < tableRow.cells.length; i++) {
							if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
							}
						} else {
						var cellwidthBase = window.listwidth / 20;
						fcell1 = cellwidthBase * 2;
						fcell2 = cellwidthBase * 6.55;
						fcell3 = cellwidthBase * 3;
						fcell4 = cellwidthBase * 5;
						fcell5 = cellwidthBase * 3;
						fcell6 = cellwidthBase * .45;
						headerRow.cells[0].style.width = fcell1 + "px";
						headerRow.cells[1].style.width = fcell2 + "px";
						headerRow.cells[2].style.width = fcell3 + "px";
						headerRow.cells[3].style.width = fcell4 + "px";						
						headerRow.cells[4].style.width = fcell5 + "px";
						headerRow.cells[5].style.width = fcell6 + "px";	
						}
					if((window.fac_last_display == 0) || (facility_number != window.latest_facility) || (window.do_fac_update == true) || (window.changed_fac_sort == true) || (window.do_fac_refresh == true)) {
						if(getHeaderHeight(headerRow) >= listheader_height) {
							var theRow = factbl.insertRow(1);
							theRow.style.height = "20px";
							for (var i = 0; i < tableRow.cells.length; i++) {
								var theCell = theRow.insertCell(i);
								theCell.innerHTML = " ";
								}
							}
						}
					}
				window.facFin = true;
				window.fac_last_display = fac_arr[key][10];
				window.facstatSel = true;
				window.latest_facility = facility_number;
				pageLoaded();
				},500);
			}
		window.facLoading = false;
		}				// end function facilitylist_cb()
	}				// end function load_facilitylist()	

function facilitylist_setwidths() {
	var viewableRow = 1;
	var factbl = document.getElementById('facilitiestable');
	var headerRow = factbl.rows[0];
	for (i = 1; i < factbl.rows.length; i++) {
		if(!isViewable(factbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = factbl.rows[viewableRow];
	if(tableRow && i != factbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
			}
		} else {
		var cellwidthBase = window.listwidth / 20;
		fcell1 = cellwidthBase * 2;
		fcell2 = cellwidthBase * 6.55;
		fcell3 = cellwidthBase * 3;
		fcell4 = cellwidthBase * 5;
		fcell5 = cellwidthBase * 3;
		fcell6 = cellwidthBase * .45;
		headerRow.cells[0].style.width = fcell1 + "px";
		headerRow.cells[1].style.width = fcell2 + "px";
		headerRow.cells[2].style.width = fcell3 + "px";
		headerRow.cells[3].style.width = fcell4 + "px";						
		headerRow.cells[4].style.width = fcell5 + "px";	
		headerRow.cells[5].style.width = fcell6 + "px";	
		}
	if(getHeaderHeight(headerRow) >= listheader_height) {
		var theRow = factbl.insertRow(1);
		theRow.style.height = "20px";
		for (var i = 0; i < tableRow.cells.length; i++) {
			var theCell = theRow.insertCell(i);
			theCell.innerHTML = " ";
			}
		}
	}
	
function facilitylist_get() {
	if (f_interval!=null) {return;}
	if(window.facFin == true || window.respLoading == true || window.incLoading == true || window.logLoading == true) {return;}
	f_interval = window.setInterval('facilitylist_loop()', 240000);
	}			// end function mu get()

function facilitylist_loop() {
	load_facilitylist(fac_field, fac_direct);
	}			// end function do_loop()
	
var changed_wl_sort = false;
var wl_direct = "ASC";
var wl_field = "id";
var wl_id = "r1";

function set_warnloc_headers(id, header_text, the_bull) {
	if(id == "w1") {
		window.w1_text = header_text + the_bull;
		window.w2_text = textWlTitle;
		window.w3_text = textWlType;
		window.w4_text = textWlAddress;
		window.w5_text = textWlUpdated;
		} else if(id == "w2") {
		window.w2_text = header_text + the_bull;
		window.w1_text = textWlID;
		window.w3_text = textWlType;
		window.w4_text = textWlAddress;
		window.w5_text = textWlUpdated;
		} else if(id == "w3") {
		window.w3_text = header_text + the_bull;
		window.w1_text = textWlID;
		window.w2_text = textWlTitle;
		window.w4_text = textWlAddress;
		window.w5_text = textWlUpdated;
		} else if(id == "w4") {
		window.w4_text = header_text + the_bull;
		window.w1_text = textWlID;
		window.w2_text = textWlTitle;
		window.w3_text = textWlType;
		window.w5_text = textWlUpdated;
		} else if(id == "w5") {
		window.w4_text = header_text + the_bull;
		window.w1_text = textWlID;
		window.w2_text = textWlTitle;
		window.w3_text = textWlType;
		window.w4_text = textWlAddress;
		}
	}
	
function do_warnloc_sort(id, field, header_text) {
	window.changed_wl_sort = true;
	window.wl_last_display = 0;
	if(window.wl_field == field) {
		if(window.wl_direct == "ASC") {
			window.wl_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.wl_header = header_text;
			window.wl_field = field;
			set_warnloc_headers(id, header_text, the_bull);
			} else if(window.wl_direct == "DESC") { 
			window.wl_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.wl_header = header_text; 
			window.wl_field = field;
			set_warnloc_headers(id, header_text, the_bull);
			}
		} else {
		$(wl_id).innerHTML = wl_header;
		window.wl_field = field;
		window.wl_direct = "ASC";
		window.wl_id = id;
		window.wl_header = header_text;
		var the_bull = "&#9650";
		set_warnloc_headers(id, header_text, the_bull);
		}
	load_warnloclist(field, fac_direct);
	return true;
	}
	
function load_warnloclist(sort, dir) {
	if(sort != window.wl_field) {
		window.wl_field = sort;
		}
	if(dir != window.wl_direct) {
		window.wl_direct = dir;
		}
	if($('the_wllist').innerHTML == "") {
		$('the_wllist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/list_warnlocations.php?sort='+window.wl_field+'&dir='+ window.wl_direct+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,warnloclist_cb, "");
	function warnloclist_cb(req) {
		var location_number = 0;
		var loc_arr = JSON.decode(req.responseText);
		if(loc_arr[0][0] == 0) {
			for(var key in wlmarkers) {
				if(map) {
					if(wlmarkers[key]) {map.removeLayer(wlmarkers[key]);}
					}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Warn Locations to view.........</marquee>";
			$('the_wllist').innerHTML = outputtext;
			window.latest_wlocation = 0;
			} else {
			for(var key in wlmarkers) {
				if(map) {
					if(wlmarkers[key]) {map.removeLayer(wlmarkers[key]);}
					}
				}
			var outputtext = "<TABLE id='locationstable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px; background-color: #EFEFEF;'>";
			outputtext += "<TH id='f1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + wlIDTip + "');\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_warnloc_sort(this.id, 'id', '" + textWlID + "')\">" + window.w1_text + "</TH>";
			outputtext += "<TH id='f2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + wlTitleTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_warnloc_sort(this.id, 'title', '" + textWlTitle + "')\">" + window.w2_text + "</TH>";
			outputtext += "<TH id='f3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + wlTypeTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_warnloc_sort(this.id, 'type', '" + textWlType + "')\">" + window.w3_text + "</TH>";
			outputtext += "<TH id='f4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + wlAddressTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_warnloc_sort(this.id, 'address', '" + textWlAddress + "')\">" + window.w4_text + "</TH>";
			outputtext += "<TH id='f5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + wlUpdatedTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_warnloc_sort(this.id, 'updated', '" + textWlUpdated + "')\">" + window.w5_text + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			var i=0;
			for(var key in loc_arr) {
				if((key > 0) && (loc_arr[key][8]) && (loc_arr[key][8] != "")) {
					var loc_id = loc_arr[key][8];
					outputtext += "<TR id='" + i +"' CLASS='" + colors[i%2] + "' style='width: " + window.listwidth + "px;'>";
					outputtext += "<TD class='plain_list text' onClick='mywlclick(" + loc_id + ");'>" + i + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='mywlclick(" + loc_id + ");'>" + loc_arr[key][1] + "</TD>";
					outputtext += "<TD class='plain_list text' style='background-color: " + loc_arr[key][5] + "; color: " + loc_arr[key][6] + ";' onClick='mywlclick(" + loc_id + ");'>" + loc_arr[key][11] + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='mywlclick(" + loc_id + ");'>" + loc_arr[key][4] + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='mywlclick(" + loc_id + ");'>" + loc_arr[key][7] + "</TD>";
					outputtext += "</TR>";
					if(window.locations_updated[loc_id]) {
						if(window.locations_updated[loc_id] != loc_arr[key][9]) {
							window.do_wl_update = true;
							} else {
							window.do_wl_update = false;
							}
						} else {
						window.locations_updated[loc_id] = loc_arr[key][9];
						window.do_wl_update = true;
						}
					infowindowtext = loc_arr[key][9];
					if(map) {
						if((isFloat(loc_arr[key][2])) && (isFloat(loc_arr[key][3]))) {
							var marker = createWlocationMarker(loc_arr[key][2], loc_arr[key][3], infowindowtext, loc_arr[key][10], 0, loc_id, i,  0, 0, loc_arr[key][1]);
							marker.addTo(map);
							location_number = loc_id;
							}
						}
					}
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {	
				if(window.latest_wlocation == 0) {
					$('the_wllist').innerHTML = outputtext;
					window.latest_wlocation = location_number;
					} else {
					if((location_number != window.latest_facility) || (window.do_wl_update == true) || (window.changed_wl_sort == true)) {
						$('the_wllist').innerHTML = outputtext;
						window.latest_wlocation = location_number;
						}
					}
				var loctbl = document.getElementById('locationstable');
				if(loctbl) {
					var headerRow = loctbl.rows[0];
					var tableRow = loctbl.rows[1];
					if(tableRow) {
						for (var i = 0; i < tableRow.cells.length; i++) {
							if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
							}
						if(getHeaderHeight(headerRow) >= listheader_height) {
							var theRow = loctbl.insertRow(1);
							theRow.style.height = "20px";
							for (var i = 0; i < tableRow.cells.length; i++) {
								var theCell = theRow.insertCell(i);
								theCell.innerHTML = " ";
								}
							}
						} else {
						var cellwidthBase = window.listwidth / 20;
						wcell1 = cellwidthBase;
						wcell2 = cellwidthBase * 7;
						wcell3 = cellwidthBase * 7;
						wcell4 = cellwidthBase;
						wcell5 = cellwidthBase;
						headerRow.cells[0].style.width = wcell1 + "px";
						headerRow.cells[1].style.width = wcell2 + "px";
						headerRow.cells[2].style.width = wcell3 + "px";
						headerRow.cells[3].style.width = wcell4 + "px";
						headerRow.cells[4].style.width = wcell5 + "px";
						}
					}
				},500);
			}
		}				// end function warnloclist_cb()
	warnloclist_get();
	}				// end function load_warnloclist()	

function warnloclist_setwidths() {
	var viewableRow = 1;
	var loctbl = document.getElementById('locationstable');
	var headerRow = loctbl.rows[0];
	for (i = 1; i < loctbl.rows.length; i++) {
		if(!isViewable(loctbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = loctbl.rows[viewableRow];
	if(tableRow && i != loctbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
			}
		} else {
		tableRow.cells[0].style.width = window.wcell1 + "px";
		tableRow.cells[1].style.width = window.wcell2 + "px";
		tableRow.cells[2].style.width = window.wcell3 + "px";
		tableRow.cells[3].style.width = window.wcell4 + "px";
		headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
		headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
		headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
		headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
		}
	if(getHeaderHeight(headerRow) >= listheader_height) {
		var theRow = loctbl.insertRow(1);
		theRow.style.height = "20px";
		for (var i = 0; i < tableRow.cells.length; i++) {
			var theCell = theRow.insertCell(i);
			theCell.innerHTML = " ";
			}
		}
	}
	
function warnloclist_get() {
	if (wl_interval!=null) {return;}
	wl_interval = window.setInterval('warnloc_loop()', 600000);
	}			// end function mu get()

function warnloc_loop() {
	load_warnloclist(fac_field, fac_direct);
	}			// end function do_loop()
	
function load_warnlocations(screen) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/list_warnlocations.php?sort='+window.wl_field+'&dir='+ window.wl_direct+'&version='+randomnumber+'&q='+sess_id+'&screen='+screen;
	sendRequest (url,warnloclist_cb, "");
	function warnloclist_cb(req) {
		var location_number = 0;
		var loc_arr = JSON.decode(req.responseText);
		var i=0;
		for(var key in loc_arr) {
			infowindowtext = loc_arr[key][9];
			var loc_id = loc_arr[key][8];
			if((isFloat(loc_arr[key][2])) && (isFloat(loc_arr[key][3]))) {
				var marker = createWlocationMarkerSit(loc_arr[key][2], loc_arr[key][3], infowindowtext, 1, 0, i, "!" + i,  0, 0, "This is a Warn Location");
				marker.addTo(map);
				location_number = loc_id;
				}
			i++;
			}
		}				// end function warnloclist_cb()

	}				// end function load_warnloclist()	
	
function load_fs_incidentlist() {
	if($('the_list').innerHTML == "") {
		$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);	
	var url = './ajax/full_screen_incidents.php?func='+inc_period+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,incidentlist_cb, "");		
	function incidentlist_cb(req) {
		var inc_arr = JSON.decode(req.responseText);
		if(window.inc_period_changed == 1) {
			for(var key in tmarkers) {
				if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
				}
			$('the_list').innerHTML = "";
			window.inc_period_changed = 0;
			}
		if((inc_arr[0]) && (inc_arr[0][0] == 0)) {
			window.inc_last_display = 0;
			for(var key in tmarkers) {
				if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
				}
			outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Incidents, please select another time period or add a new incident.........</marquee>";
			$('the_list').innerHTML = outputtext;
			var the_sev_str = "<font color='blue'>Normal " + inc_arr[0][22] + "</FONT>, ";
			the_sev_str += "<font color='green'>Medium " + inc_arr[0][23] + "</FONT>, ";
			the_sev_str += "<font color='red'>High " + inc_arr[0][24] + "</FONT>";			
			$('sev_counts').innerHTML = the_sev_str;
			} else {
			if(window.changed_inc_sort == true) {
				for(var key in tmarkers) {
					if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
					}
				}	
			var i = 1;
			var blinkstart = "";
			var blinkend = "";
			var ticket_number = 0;
			var category = "Incident";
			var outputtext = "<TABLE id='incidenttable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='t1' class='plain_listheader_fs text text_bold'>&nbsp;" + textID + "</TH>";
			outputtext += "<TH id='t2' class='plain_listheader_fs text text_bold'>" + textScope + "</TH>";
			outputtext += "<TH id='t3' class='plain_listheader_fs text text_bold'>" + textAddress + "</TH>";
			outputtext += "<TH id='t4' class='plain_listheader_fs text text_bold'>" + textType + "</TH>";
			outputtext += "<TH id='t5' class='plain_listheader_fs text text_bold'>" + textP + "</TH>";
			outputtext += "<TH id='t6' class='plain_listheader_fs text text_bold'>" + textA + "</TH>";
			outputtext += "<TH id='t7' class='plain_listheader_fs text text_bold'>" + textU + "</TH>";
			outputtext += "<TH id='t8' class='plain_listheader_fs text text_bold'>" + textUpdated + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in inc_arr) {
				if(key != 0) {
					var inc_id = inc_arr[key][20];
					var infowindowtext = inc_arr[key][21];
					if(inc_arr[key][19] == 1) {
						blinkstart = "<blink>";
						blinkend = "</blink>";
						}
					outputtext += "<TR CLASS='" + fscolors[i%2] +"' style='width: " + window.listwidth + "px;' onMouseover=\"Tip('" + inc_arr[key][0] + " - " + inc_arr[key][1] + "')\" onMouseout='UnTip();' onClick='mytclick(" + inc_id + ");'>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>&nbsp;" + key + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][0] + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][1] + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][4] + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][17] + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][16] + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + blinkstart + inc_arr[key][18] + blinkend + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][10] + "</TD>";
					outputtext += "</TR>";
					if(window.tickets_updated[key]) {
						if(window.tickets_updated[key] != inc_arr[key][11]) {
							window.do_update = true;
							} else {
							window.do_update = false;
							}
						} else {
						window.tickets_updated[key] = inc_arr[key][11];
						window.do_update = true;
						}
					ticket_number = key;
					if(tmarkers[i]) {
						var curPos = tmarkers[i].getLatLng();
						if((curPos.lat != inc_arr[key][2]) || (curPos.lng != inc_arr[key][3])) {
							theLatLng = new L.LatLng(inc_arr[key][2], inc_arr[key][3]);
							tmarkers[i].setLatLng(theLatLng);
							}
						} else {
						if((isFloat(inc_arr[key][2])) && (isFloat(inc_arr[key][3]))) {
							var marker = createMarker(inc_arr[key][2], inc_arr[key][3], infowindowtext, inc_arr[key][5], inc_arr[key][4], inc_id, i, category, 0, inc_arr[key][11]);		// 3/19/11
							marker.addTo(map);
							}
						}
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			var the_sev_str = "<font color='blue'>Normal " + inc_arr[0][22] + "</FONT>, ";
			the_sev_str += "<font color='green'>Medium " + inc_arr[0][23] + "</FONT>, ";
			the_sev_str += "<font color='red'>High " + inc_arr[0][24] + "</FONT>";			
			$('sev_counts').innerHTML = the_sev_str;
			setTimeout(function() {
				if(window.inc_last_display == 0) {
					$('the_list').innerHTML = outputtext;
					} else {
					if((ticket_number != window.latest_ticket) || (window.do_update == true) || (window.changed_inc_sort == true)) {
						$('the_list').innerHTML = outputtext;
						window.na=document.getElementsByTagName("blink");
						window.latest_ticket = ticket_number;
						window.inc_last_display = 1;
						}
					}
				var inctbl = document.getElementById('incidenttable');
				if(inctbl) {
					var headerRow = inctbl.rows[0];
					var tableRow = inctbl.rows[1];
					if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
					if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
					if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
					if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
					if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
					if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
					if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
					if(tableRow.cells[7] && headerRow.cells[7]) {headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";}
					} else {
					$('t1').style.width = cell1 + "px";
					$('t2').style.width = cell2 + "px";
					$('t3').style.width = cell3 + "px";
					$('t4').style.width = cell4 + "px";
					$('t5').style.width = cell5 + "px";
					$('t6').style.width = cell6 + "px";
					$('t7').style.width = cell7 + "px";
					}				
				},500);
			}
		}				// end function incidentlist_cb()
	fs_incidentlist_get();
	}				// end function load_incidentlist()
	
function fs_incidentlist_get() {								// set cycle
	if (i_interval!=null) {return;}
	i_interval = window.setInterval('fs_incidentlist_loop()', 30000);
	}			// end function mu get()

function fs_incidentlist_loop() {
	load_fs_incidentlist();
	}			// end function do_loop()	

function load_fs_responders() {
	var resp_assigns = JSON.decode(window.theAssigns);
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/full_screen_responders.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,responderlist_cb, "");		
	function responderlist_cb(req) {
		var i = 1;
		var responder_number = 0;	
		var resp_arr = JSON.decode(req.responseText);
		if(resp_arr[0][22] == 0) {
			for(var key in rmarkers) {
				if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
				}
			$('boxes').innerHTML = resp_arr[0][19];
			window.latest_responder = 0;
			} else {
			for(var key in resp_arr) {
				if(key != 0) {
					if(resp_arr[key][17]) {
						var unit_id = resp_arr[key][17];
						if(resp_assigns[unit_id] && resp_assigns[unit_id].length != 0) {
							theAssigned[unit_id] = true; 
							infowindowtext = "<B>" + resp_arr[key][2] + "</B><BR />" + get_assigns_flag(unit_id);
							} else {
							infowindowtext = " ";
							}
						if(window.responders_updated[unit_id]) {
							if(window.responders_updated[unit_id] != resp_arr[key][16]) {
								window.do_resp_update = true;
								} else {
								window.do_resp_update = false;
								}
							} else {
							window.responders_updated[unit_id] = resp_arr[key][16];
							window.do_resp_update = true;
							}
						if(rmarkers[unit_id]) {
							var curPos = rmarkers[unit_id].getLatLng();
							if((curPos.lat != resp_arr[key][3]) || (curPos.lng != resp_arr[key][4])) {
								theLatLng = new L.LatLng(resp_arr[key][3], resp_arr[key][4]);
								rmarkers[unit_id].setLatLng(theLatLng);
								}
							} else {
							if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
								var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_id, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
								marker.addTo(map);
								}
							}							
						responder_number = unit_id;
						}
					}
				i++;
				}
			setTimeout(function() {
				if(resp_arr[0][24] != 0) {
					if($('show_asgn')) {
						$('show_asgn').style.color = "#000000";
						$('show_asgn_img').style.opacity = "1";
						$('show_asgn').onclick = function() {do_assignment_flags();};
						$('show_asgn').onmouseover = function() {do_hover_centerbuttons(this.id); Tip("Click to show flags for assigned units or hide flags");};
						$('show_asgn').onmouseout = function() {do_plain_centerbuttons(this.id); UnTip();};
						$('show_asgn').style.cursor = "pointer";
						}
					if($('fs_show_asgn')) {
						$('fs_show_asgn').style.color = "#000000";
						$('fs_show_asgn_img').style.opacity = "1";
						$('fs_show_asgn').onclick = function() {do_fs_assignment_flags();};
						$('fs_show_asgn').onmouseover = function() {do_hover(this.id); Tip("Click to show flags for assigned units or hide flags");};
						$('fs_show_asgn').onmouseout = function() {do_plain(this.id); UnTip();};
						$('fs_show_asgn').style.cursor = "pointer";
						}					
					} else {
					if($('show_asgn')) {
						$('show_asgn').style.color = "#CFCFCF";
						$('show_asgn_img').style.opacity = "0.3";
						$('show_asgn').onclick = null;
						$('show_asgn').onmouseover = null;
						$('show_asgn').onmouseout = null;
						$('show_asgn').style.cursor = "default";				
						}
					if($('fs_show_asgn')) {
						$('fs_show_asgn').style.color = "#CFCFCF";
						$('fs_show_asgn_img').style.opacity = "0.3";
						$('fs_show_asgn').onclick = null;
						$('fs_show_asgn').onmouseover = null;
						$('fs_show_asgn').onmouseout = null;
						$('fs_show_asgn').style.cursor = "default";				
						}
					}
				if(window.resp_last_display == 0) {
					$('boxes').innerHTML = resp_arr[0][21];
					window.latest_responder = responder_number;
					if($('boxes')) {set_categories();}
					} else {
					if((ticket_number != window.latest_ticket) || (window.do_update == true) || (window.changed_resp_sort == true)) {
						$('boxes').innerHTML = resp_arr[0][21];
						window.latest_responder = responder_number;
						if($('boxes')) {set_categories();}
						}
					}
				},500);			
			}
		}				// end function responderlist_cb()
	fs_responders_get();
	}				// end function load_responderlist()

function fs_responders_get() {
	if (r_interval!=null) {return;}
	r_interval = window.setInterval('fs_responders_loop()', 60000); 
	}			// end function mu get()

function fs_responders_loop() {
	load_fs_responders();
	}			// end function do_loop()

function load_fs_facilities() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/full_screen_facilities.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,facilitylist_cb, "");
	function facilitylist_cb(req) {
		var i = 1;
		var facility_number = 0;
		var fac_arr = JSON.decode(req.responseText);
		if(fac_arr[0][13] == 0) {
			for(var key in fmarkers) {
				if(fmarkers[key]) {map.removeLayer(fmarkers[key]);}
				}
			$('fac_boxes').innerHTML = fac_arr[0][19];
			window.latest_facility = 0;
			} else {
			for(var key in fac_arr) {
				if((key > 0) && (fac_arr[key][2]) && (fac_arr[key][2] != "")) {
					var fac_id = fac_arr[key][10];
					if(window.facilities_updated[fac_id]) {
						if(window.facilities_updated[fac_id] != fac_arr[key][9]) {
							window.do_fac_update = true;
							} else {
							window.do_fac_update = false;
							}
						} else {
						window.facilities_updated[fac_id] = fac_arr[key][9];
						window.do_fac_update = true;
						}
					if(fmarkers[fac_id]) {
						} else {
						if(!fac_arr[key][2]) {
							var icon_str = "UNK";
							} else {
							var icon_str = fac_arr[key][2].toString();
							}
						infowindowtext = fac_arr[key][14];
						if((isFloat(fac_arr[key][3])) && (isFloat(fac_arr[key][4]))) {
							var marker = createFacilityMarker(fac_arr[key][3], fac_arr[key][4], infowindowtext, fac_arr[key][11], 0, fac_id, icon_str,  fac_arr[key][15], 0, "This is a Facility"); // 7/28/10, 3/15/11, 12/23/13
							marker.addTo(map);
							}
						}
					facility_number = fac_id;
					}
				i++;
				}
			setTimeout(function() {	
				if(window.fac_last_display == 0) {
					$('fac_boxes').innerHTML = fac_arr[0][12];
					set_fac_categories();
					window.latest_facility = facility_number;
					} else {
					if((facility_number != window.latest_facility) || (window.do_fac_update == true) || (window.changed_fac_sort == true)) {
						$('fac_boxes').innerHTML = fac_arr[0][12];
						window.latest_facility = facility_number;
						set_fac_categories();
						}
					}
				},500);
			}
		}				// end function facilitylist_cb()
	fs_facilities_get();
	}				// end function load_facilitylist()	

function fs_facilities_get() {
	if (r_interval!=null) {return;}
	r_interval = window.setInterval('fs_facilities_loop()', 120000); 
	}			// end function mu get()

function fs_facilities_loop() {
	load_fs_facilities();
	}			// end function do_loop()

function add_hash(in_str) { // prepend # if absent
	return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
	}

function draw_poly(linename, category, color, opacity, width, filled, fillcolor, fillopacity, linedata, theType, theID) {
	if(filled == 0) { filled = false; } else { filled = true;}
	if(!linedata) {return;}
	if(!(boundary[theID])) {
		var path = new Array();
		var thelineData = linedata.split(';');
		for (var i = 0; i < thelineData.length; i++) { 
			var theCoords = thelineData[i].split(',');
			var theLatLng = new L.LatLng(theCoords[0], theCoords[1]);
			path[i] = theLatLng;
			}
		var polygon = L.polygon([path],{
		clickable: false,
		color: color,
		weight: width,
		opacity: opacity,
		fill: filled,
		fillColor: fillcolor,
		fillOpacity: fillopacity,
		stroke: true
		});
		if(!boundary[theID]) {
			polygon.addTo(map);
			boundary[theID] = polygon;
			if(linename && !bound_names[theID]) {
				bound_names[theID] = linename;
				}		
			}
		}
	return polygon;
	}
	
function draw_polyline(linename, color, opacity, width, linedata, theID) {
	if(!linedata) {return;}
	var path = new Array();
	var thelineData = linedata.split(';');
	for (i = 0; i < thelineData.length; i++) { 
		var theCoords = thelineData[i].split(',');
		var theLatLng = new L.LatLng(theCoords[0], theCoords[1]);
		path[i] = theLatLng;
		}
	polyline = L.polyline(path,{
	clickable: false,
	color: color,
	weight: width,
	opacity: opacity,
	stroke: true
	});
	if(!boundary[theID]) {
		polyline.addTo(map);
		boundary[theID] = polyline;
		if(linename && !bound_names[theID]) {
			bound_names[theID] = linename;
			}		
		}
	return polyline;
	}
	
function drawCircle(linename, linedata, strokeColor, strokeWidth, strokeOpacity, filled, fillColor, fillOpacity, theType, theID) {
	if(filled == 0) { filled = false; } else { filled = true;}
	var theData = linedata.split(';');
	var thelineData = theData[0].split(',');
	var theRadius = theData[1];
	var radius = theRadius*1000
	if((!(bound_names[theID])) && (!(boundary[theID]))){
		var draw_circle = L.circle([thelineData[0], thelineData[1]], radius, {
			clickable: false,
			color: strokeColor,
			fill: filled,
			fillColor: fillColor,
			fillOpacity: fillOpacity
			});
		if(!boundary[theID]) {
			draw_circle.addTo(map);
			draw_circle.bindPopup(linename);
			boundary[theID] = draw_circle;
			if(linename && !bound_names[theID]) {
				bound_names[theID] = linename;
				}		
			}
		}
	}

function drawBanner(linename, linedata, width, color, category, theID) {        // Create the banner - 6/5/2013
	var theData = linedata.split(';');
	var thelineData = theData[0].split(',');
	var lat = thelineData[0];
	var lng = thelineData[1];
	var theBanner = theData[1];
	var point = new L.LatLng(lat, lng);
	var font_size = width;
	var the_color = (typeof color == 'undefined')? "000000" : color ;	// default to black
	var html = "<DIV style=\"background: transparent; font-size: " + font_size + "px; color: " + the_color + ";\">" + theBanner + "</DIV>";
	var myTextLabel = L.marker(point, {
		icon: L.divIcon({
			html: html
		}),
		draggable: false
	});
	myTextLabel.addTo(map);
	if(!boundary[theID]) {
		myTextLabel.addTo(map);
		boundary[theID] = myTextLabel;
		if(linename && !bound_names[theID]) {
			bound_names[theID] = linename;
			}		
		}
	}				// end function draw Banner()
	
function load_catchments() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_mmarkup.php?func=c&version=' + randomnumber;
	sendRequest (url,catchments_cb, "");
	function catchments_cb(req) {
		var cat_arr = JSON.decode(req.responseText);
		for(var key in cat_arr[0]) {
			var theID = cat_arr[0][key]['id'];
			var theLinename = cat_arr[0][key]['name'];
			var theIdent = cat_arr[0][key]['ident'];
			var theCategory = cat_arr[0][key]['cat'];
			var theData = cat_arr[0][key]['data'];
			var theColor = cat_arr[0][key]['color'];
			var theOpacity = cat_arr[0][key]['opacity'];
			var theWidth = cat_arr[0][key]['width'];
			var theFilled = cat_arr[0][key]['filled'];
			var theFillcolor = cat_arr[0][key]['fill_color'];
			var theFillopacity = cat_arr[0][key]['fill_opacity'];
			var theType = cat_arr[0][key]['type'];
			if(theType == "p") {
				var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "catchment", theID);
				} else if(theType == "c") {
				var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "catchment", theID);
				}
			}
		}				// end function catchments_cb()
	}				// end function load_catchments()
	
function load_basemarkup() {
	var randomnumber=Math.floor(Math.random()*99999999);		
	var url = './ajax/get_mmarkup.php?func=b&version=' + randomnumber;
	sendRequest (url,basemarkup_cb, "");		
	function basemarkup_cb(req) {
		var base_arr = JSON.decode(req.responseText);
		for(var key in base_arr[0]) {
			var theID = base_arr[0][key]['id'];
			var theLinename = base_arr[0][key]['name'];
			var theIdent = base_arr[0][key]['ident'];
			var theCategory = base_arr[0][key]['cat'];
			var theData = base_arr[0][key]['data'];
			var theColor = base_arr[0][key]['color'];
			var theOpacity = base_arr[0][key]['opacity'];
			var theWidth = base_arr[0][key]['width'];
			var theFilled = base_arr[0][key]['filled'];
			var theFillcolor = base_arr[0][key]['fill_color'];
			var theFillopacity = base_arr[0][key]['fill_opacity'];
			var theType = base_arr[0][key]['type'];
			if(theType == "p") {
				var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "basemarkup", theID);
				} else if(theType == "l") {
				var polyline = draw_polyline(theLinename, theColor, theOpacity, theWidth, theData, theID);
				} else if(theType == "c") {
				var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "basemarkup", theID);
				} else if(theType == "b") {
				var banner = drawBanner(theLinename, theData, theWidth, theColor, "basemarkup", theID);				
				}
			}
		}				// end function basemarkup_cb()
	}				// end function load_basemarkup()
	
function load_groupbounds() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_mmarkup.php?func=g&version=' + randomnumber;
	sendRequest (url,groupbound_cb, "");		
	function groupbound_cb(req) {
		var gpb_arr = JSON.decode(req.responseText);
		for(var key in gpb_arr[0]) {
			var theID = gpb_arr[0][key]['id'];
			var theLinename = gpb_arr[0][key]['name'];
			var theIdent = gpb_arr[0][key]['ident'];
			var theCategory = gpb_arr[0][key]['cat'];
			var theData = gpb_arr[0][key]['data'];
			var theColor = gpb_arr[0][key]['color'];
			var theOpacity = gpb_arr[0][key]['opacity'];
			var theWidth = gpb_arr[0][key]['width'];
			var theFilled = gpb_arr[0][key]['filled'];
			var theFillcolor = gpb_arr[0][key]['fill_color'];
			var theFillopacity = gpb_arr[0][key]['fill_opacity'];
			var theType = gpb_arr[0][key]['type'];
			if(theType == "p") {
				var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "groupbound", theID);
				} else if(theType == "c") {
				var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "groupbound", theID);
				}
			}
		}				// end function groupbound_cb()
	}				// end function load_groupbounds()
	
function load_exclusions() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_mmarkup.php?func=e&version=' + randomnumber;
	sendRequest (url,exclusions_cb, "");		
	function exclusions_cb(req) {
		var exc_arr = JSON.decode(req.responseText);
		for(var key in exc_arr[0]) {
			var theID = exc_arr[0][key]['id'];
			var theLinename = exc_arr[0][key]['name'];
			var theIdent = exc_arr[0][key]['ident'];
			var theCategory = exc_arr[0][key]['cat'];
			var theData = exc_arr[0][key]['data'];
			var theColor = exc_arr[0][key]['color'];
			var theOpacity = exc_arr[0][key]['opacity'];
			var theWidth = exc_arr[0][key]['width'];
			var theFilled = exc_arr[0][key]['filled'];
			var theFillcolor = exc_arr[0][key]['fill_color'];
			var theFillopacity = exc_arr[0][key]['fill_opacity'];
			var theType = exc_arr[0][key]['type'];
			if(theType == "p") {
				var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "exclusion", theID);
				} else if(theType == "c") {
				var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "exclusion", theID);
				}
			}
		}				// end function exclusions_cb()
	}				// end function load_exclusions()
	
function load_ringfences() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_mmarkup.php?func=r&version=' + randomnumber;
	sendRequest (url,ringfences_cb, "");		
	function ringfences_cb(req) {
		var rf_arr = JSON.decode(req.responseText);
		for(var key in rf_arr[0]) {
			var theID = rf_arr[0][key]['id'];
			var theLinename = rf_arr[0][key]['name'];
			var theIdent = rf_arr[0][key]['ident'];
			var theCategory = rf_arr[0][key]['cat'];
			var theData = rf_arr[0][key]['data'];
			var theColor = rf_arr[0][key]['color'];
			var theOpacity = rf_arr[0][key]['opacity'];
			var theWidth = rf_arr[0][key]['width'];
			var theFilled = rf_arr[0][key]['filled'];
			var theFillcolor = rf_arr[0][key]['fill_color'];
			var theFillopacity = rf_arr[0][key]['fill_opacity'];
			var theType = rf_arr[0][key]['type'];
			if(theType == "p") {
				var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "ringfence", theID);
				} else if(theType == "c") {
				var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "ringfence", theID);
				}
			}
		}				// end function ringfences_cb()
	}				// end function load_ringfences()	
	
function load_poly_controls() {
	var outputtext = "<DIV style='font-size: 1.1em;'>";
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_poly_buttons.php?version=' + randomnumber;
	sendRequest (url,polys_cb, "");		
	function polys_cb(req) {
		var pol_arr = JSON.decode(req.responseText);
		outputtext = pol_arr[0];
		$('poly_boxes').innerHTML = outputtext;
		setTimeout(function() {set_bnds();},2000);
		}				// end function polys_cb()
	}				// end function load_poly_controls()
	
function load_regions() {
	var outputtext = "<DIV style='font-size: 1.1em;'>";
	var randomnumber=Math.floor(Math.random()*99999999);		
	sendRequest ('./ajax/get_regions_control.php?version=' + randomnumber,regions_cb, "");		
	function regions_cb(req) {
		var reg_arr = JSON.decode(req.responseText);
		outputtext = reg_arr[0];
		if($('regions_control')) {$('regions_control').innerHTML = outputtext;}
		if($('theRegions')) {
			$('theRegions').onmouseover = function() {
				Tip(reg_arr[1], WIDTH, 300);
				};
			$('theRegions').onmouseout = function() {
				UnTip();
				};
			$('theRegions').style.cursor = "pointer";
			}
		}				// end function regions_cb()
	}				// end function load_regions()
	
function update_regions_text() {
	var outputtext = "<DIV style='font-size: 1.1em;'>";
	var randomnumber=Math.floor(Math.random()*99999999);		
	sendRequest ('./ajax/get_regions_control.php?version=' + randomnumber,regions_cb2, "");		
	function regions_cb2(req) {
		var reg_arr = JSON.decode(req.responseText);
		if($('theRegions')) {
			$('theRegions').onmouseover = function() {
				Tip(reg_arr[1], WIDTH, 300);
				};
			}
		}				// end function regions_cb2()
	}				// end function update_regions_text()

function check_excl(resp_id, lat, lng, flag) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/check_exclzone.php?resp_id=' + resp_id + '&lat=' + lat + '&lng=' + lng + '&version=' + randomnumber;
	sendRequest (url,fencecheckcb, "");		
	function fencecheckcb(req) {
		var ez_arr = JSON.decode(req.responseText);
		var theResponse = parseInt(ez_arr[0]);
		if(theResponse == 1) {
			blink_text2(flag, '#00FF00', '#FFFF00', '#FFFF00', '#FF0000');
			exclusion_alert(resp_id);
			}
		}				// end function fencecheckcb()
	}				// end function check_excl()

function check_ringfence(resp_id, lat, lng, flag) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/check_ringfence.php?resp_id=' + resp_id + '&lat=' + lat + '&lng=' + lng + '&version=' + randomnumber;
	sendRequest (url,fencecheckcb, "");		
	function fencecheckcb(req) {
		var rf_arr = JSON.decode(req.responseText);
		var theResponse =  parseInt(rf_arr[0]);
		if(theResponse == 1) {
			blink_text(flag, '#FF0000', '#FFFF00', '#FFFF00', '#FF0000');
			ringfence_alert(resp_id);
			}
		}				// end function fencecheckcb()
	}				// end function check_ringfence()

function sleep(milliseconds) {
	var start = new Date().getTime();
	for (var i = 0; i < 1e7; i++) {
		if ((new Date().getTime() - start) > milliseconds){
			break;
			}
		}
	}
	
function load_status_control() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_status_controls.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,sc_cb, "");		
	function sc_cb(req) {
		var sc_arr = JSON.decode(req.responseText);
		for(var key in sc_arr) {
			window.status_control[key] = sc_arr[key];
			}
		}				// end function sc_cb()
	if($('screenname').innerHTML == "responders") { 
		setTimeout(function() {
			load_status_bgcolors();
			load_status_textcolors();
			load_responderlist2(window.resp_field, window.resp_direct);
			},1500);
		} else {
		setTimeout(function() {
			load_status_bgcolors();
			load_status_textcolors();
			load_responderlist(window.resp_field, window.resp_direct);
			},1000);
		}
	}				// end function load_status_control()
	
function IsNumeric(input) {
	return (input - 0) == input && input.length > 0;
	}
	
function load_status_bgcolors() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_status_bgcolors.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,sc_bgcol_cb, "");		
	function sc_bgcol_cb(req) {
		var scbgcol_arr = JSON.decode(req.responseText);
		for(var key in scbgcol_arr) {
			if(IsNumeric(key)){window.status_bgcolors[key] = scbgcol_arr[key];}
			}
		}				// end function sc_bgcol_cb()
	}				// end function load_status_bgcolors()
	
function load_status_textcolors() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_status_textcolors.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,sc_textcol_cb, "");		
	function sc_textcol_cb(req) {
		var sctextcol_arr = JSON.decode(req.responseText);
		for(var key in sctextcol_arr) {
			if(IsNumeric(key)){window.status_textcolors[key] = sctextcol_arr[key];}
			}
		}				// end function sc_textcol_cb()
	}				// end function load_status_textcolors()
	
function get_mi_totals() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/list_mi_total.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,mi_total_cb, "");		
	function mi_total_cb(req) {
		var response_arr = JSON.decode(req.responseText);
		if(response_arr[0] != 0) {
			$('maj_incs').style.display = "inline-block";
			if(response_arr[0] > 1) {
				$('maj_incs').innerHTML = response_arr[0] + " Major Incidents.";
				} else {
				$('maj_incs').innerHTML = response_arr[0] + " Major Incident.";				
				}
			} else {
			$('maj_incs').style.display = "none";	
			}
		}				// end function mi_total_cb()	
	}
	
function load_fac_status_control() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = sess_id;
	var url = './ajax/get_fac_status_controls.php?version='+randomnumber+'&q='+sess_id;
	sendRequest (url,fsc_cb, "");		
	function fsc_cb(req) {
		var fsc_arr = JSON.decode(req.responseText);
		for(var key in fsc_arr) {
			window.fac_status_control[key] = fsc_arr[key];
			}
		}				// end function fsc_cb()
	setTimeout(function() {
		load_facilitylist(window.fac_field, window.fac_direct);
		},1000);
	}				// end function load_fac_status_control()


var changed_file_sort = false;
var file_direct = "ASC";
var file_field = "name";
var file_id = "file1";

function set_file_headers(id, header_text, the_bull) {
	if(id == "file1") {
		window.file1_text = header_text + the_bull;
		window.file2_text = textFiUploaded;
		window.file3_text = textFiDate;
		window.file4_text = textFiLinked;
		} else if(id == "file2") {
		window.file2_text = header_text + the_bull;
		window.file1_text = textFiName;
		window.file3_text = textFiDate;
		window.file4_text = textFiLinked;
		} else if(id == "file3") {
		window.file3_text = header_text + the_bull;
		window.file1_text = textFiName;
		window.file2_text = textFiUploaded;
		window.file4_text = textFiLinked;
		} else if(id == "file4") {
		window.file4_text = header_text + the_bull;
		window.file1_text = textFiName;
		window.file2_text = textFiUploaded;
		window.file3_text = textFiDate;
		}
	}
	
function do_file_sort(id, field, header_text) {
	window.changed_file_sort = true;
	window.file_last_display = 0;
	if(window.file_field == field) {
		if(window.file_direct == "ASC") {
			window.file_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.file_header = header_text;
			window.file_field = field;
			set_file_headers(id, header_text, the_bull);
			} else if(window.file_direct == "DESC") { 
			window.file_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.file_header = header_text; 
			window.file_field = field;
			set_file_headers(id, header_text, the_bull);
			}
		} else {
		$(file_id).innerHTML = file_header;
		window.file_field = field;
		window.file_direct = "ASC";
		window.file_id = id;
		window.file_header = header_text;
		var the_bull = "&#9650";
		set_file_headers(id, header_text, the_bull);
		}
	load_files(window.theTicket, window.theResponder, window.theFacility, window.theMI, window.allowedit, window.file_field, window.file_direct, window.thefiletype);
	return true;
	}
	
function load_files(ticket, responder, facility, mi, allowedit, sort, dir, type) {
	window.theTicket = ticket;
	window.theResponder = responder;
	window.theFacility = facility;
	window.theMI = mi;
	window.thefiletype = type;
	window.allowedit = allowedit;
	if(sort != window.file_field) {
		window.file_field = sort;
		}
	if(dir != window.file_direct) {
		window.file_direct = dir;
		}
	if($('file_list').innerHTML == "") {
		$('file_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	if(window.theTicket != 0) { 
		theString = "ticket_id=" + window.theTicket + "&type=" + window.thefiletype + "&";
		} else if(window.theResponder != 0) {
		theString = "responder_id=" + window.theResponder + "&type=" + window.thefiletype + "&";
		} else if(window.theMI != 0) {
		theString = "mi_id=" + window.theMI + "&type=" + window.thefiletype + "&";
		} else if(window.theFacility != 0) {
		theString = "facility_id=" + window.theFacility + "&type=" + window.thefiletype + "&";
		} else if(window.thefiletype != 0) {
		theString = "type=" + window.thefiletype + "&";
		} else {
		theString = "";
		}
	var url = './ajax/list_files.php?sort='+window.file_field+'&dir='+ window.file_direct+'&' + theString + 'version=' + randomnumber;
	sendRequest (url,filelist_cb, "");
	function filelist_cb(req) {
		var i = 1;
		var file_arr = JSON.decode(req.responseText);
		if((file_arr[0]) && (file_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Files.........</marquee>";
			$('file_list').innerHTML = outputtext;
			} else {
			var outputtext = "<FORM NAME='filesForm'><TABLE id='filestable' class='fixedheadscrolling scrollable' style='width: 100%;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH id='fb' class='plain_listheader text'>&nbsp;&nbsp;</TH>";
			outputtext += "<TH id='file1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + fiNameTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'name', '" + textFiName + "')\">" + window.file1_text + "</TH>";
			outputtext += "<TH id='file2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + fiUploadedTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'owner', '" + textFiUploaded + "')\">" + window.file2_text + "</TH>";
			outputtext += "<TH id='file3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + fiDateTip + " ?');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'updated', '" + textFiDate + "')\">" + window.file3_text + "</TH>";
			outputtext += "<TH id='file4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + fiLinkedTip + " ?');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'updated', '" + textFiLinked + "')\">" + window.file4_text + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in file_arr) {
				if(file_arr[key][0]) {
					var file_id = file_arr[key][6];
					var the_title = (file_arr[key][3] != "") ? file_arr[key][3] : "Untitled";
					var theURL = "./ajax/download.php?filename=" + file_arr[key][0] + "&origname=" + file_arr[key][1] + "&type=" + file_arr[key][2];
					outputtext += "<TR CLASS='" + colors[i%2] + "' style='width: 100%;'>";
					outputtext += "<TD class='plain_list_fs text text_normal'><input type='checkbox' name='frm_file[]' value='" + file_id + "'></TD>";					
					outputtext += "<TD class='plain_list_fs text text_normal' style='white-space: nowrap;' onClick='location.href=\"" + theURL + "\"'>" + pad(30, the_title, "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' onClick='location.href=\"" + theURL + "\"'>" + pad(17, file_arr[key][4], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' style='white-space: nowrap;' onClick='location.href=\"" + theURL + "\"'>" + pad(20, file_arr[key][5], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list_fs text text_normal' onClick='location.href=\"" + theURL + "\"'>" + pad(20, file_arr[key][7], "\u00a0") + "</TD>";
					outputtext += "</TR>";
					}
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "<TR><TD COLSPAN=99 style='text-align: center;'>";
			outputtext += "</TD></TR></FORM></TABLE>";
			setTimeout(function() {$('file_list').innerHTML = outputtext;
				var filetbl = document.getElementById('filestable');
				if(filetbl) {
					var headerRow = filetbl.rows[0];
					var tableRow = filetbl.rows[1];
					for (var i = 0; i < tableRow.cells.length; i++) {
						if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = filetbl.insertRow(1);
						theRow.style.height = "20px";
						for (var i = 0; i < tableRow.cells.length; i++) {
							var theCell = theRow.insertCell(i);
							theCell.innerHTML = " ";
							}
						}
					} else {
					var cellwidthBase = window.mapWidth / 20;
					headerRow.cells[0].style.width = (cellwidthBase * 2) + "px";
					headerRow.cells[1].style.width = (cellwidthBase * 6) + "px";
					headerRow.cells[2].style.width = (cellwidthBase * 3) + "px";
					headerRow.cells[3].style.width = (cellwidthBase * 3) + "px";
					headerRow.cells[4].style.width = (cellwidthBase * 4) + "px";
					}			
				},1000);
			}
		}				// end function filelist_cb()
	}				// end function load_files()
	
var log_direct = 'DESC';
var log_field = 'id';
var log_id = "fil1";


function set_log_headers(id, header_text, the_bull) {
	if(id == "fil1") {
		window.fil1_text = header_text + the_bull;
		window.fil2_text = textLogEvent;
		window.fil3_text = textLogWhen;
		window.fil4_text = textLogUnit;
		window.fil5_text = textLogTick;
		window.fil6_text = textLogInfo;
		} else if(id == "fil2") {
		window.fil2_text = header_text + the_bull;
		window.fil1_text = textLogOwner;
		window.fil3_text = textLogWhen;
		window.fil4_text = textLogUnit;
		window.fil5_text = textLogTick;
		window.fil6_text = textLogInfo;
		} else if(id == "fil3") {
		window.fil3_text = header_text + the_bull;
		window.fil2_text = textLogEvent;
		window.fil1_text = textLogOwner;
		window.fil4_text = textLogUnit;
		window.fil5_text = textLogTick;
		window.fil6_text = textLogInfo;
		} else if(id == "fil4") {
		window.fil4_text = header_text + the_bull;
		window.fil2_text = textLogEvent;
		window.fil3_text = textLogWhen;
		window.fil1_text = textLogOwner;
		window.fil5_text = textLogTick;
		window.fil6_text = textLogInfo;
		} else if(id == "fil5") {
		window.fil5_text = header_text + the_bull;
		window.fil2_text = textLogEvent;
		window.fil3_text = textLogWhen;
		window.fil4_text = textLogUnit;
		window.fil1_text = textLogOwner;
		window.fil6_text = textLogInfo;
		} else {
		window.fil6_text = header_text + the_bull;
		window.fil2_text = textLogEvent;
		window.fil3_text = textLogWhen;
		window.fil4_text = textLogUnit;
		window.fil5_text = textLogTick;
		window.fil1_text = textLogOwner;
		}
	}
	
function do_sort(id, field, header_text) {
	if(log_field == field) {
		if(log_direct == "ASC") { 
			window.log_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.log_header = header_text;
			set_log_headers(id, header_text, the_bull);
			} else if(log_direct == "DESC") { 
			window.log_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.log_header = header_text; 
			set_log_headers(id, header_text, the_bull);
			}
		} else {
		$(log_id).innerHTML = log_header;
		window.log_field = field;
		window.log_direct = "ASC";
		window.log_id = id;
		window.log_header = header_text;
		var the_bull = "&#9650";
		set_log_headers(id, header_text, the_bull);
		}
	load_log(field, window.log_direct);
	return true;
	}

function load_log(sort, dir) {
	window.logFin = false;
	window.logLoading = true;
	if(sort != window.log_field) {
		window.log_field = sort;
		}
	if(dir != window.log_direct) {
		window.log_direct = dir;
		}
	if($('the_loglist').innerHTML == "") {
		$('the_loglist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/sit_log.php?sort=' + window.log_field + '&dir=' + window.log_direct + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,loglist_cb, "");
	function loglist_cb(req) {
		var i = 1;
		var log_arr = JSON.decode(req.responseText);
		if(log_arr[0][0] == 0) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold; color: #000000;'>......Log empty.........</marquee>";	
			$('the_loglist').innerHTML = outputtext;
			window.logFin = true;
			pageLoaded();
			return false;
			} else {
			var outputtext = "<TABLE id='logtable' class='fixedheadscrolling scrollable' style='width: " + window.mapWidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.mapWidth + "px; background-color: #EFEFEF;'>";
			outputtext += "<TH id='fil1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + logOwnerTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'id', '" + textLogOwner + "')\">" + window.fil1_text + "</TH>";
			outputtext += "<TH id='fil2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + logEventTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'code', '" + textLogEvent + "')\">" + window.fil2_text + "</TH>";
			outputtext += "<TH id='fil3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + logWhenTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'date', '" + textLogWhen + "')\">" + window.fil3_text + "</TH>";
			outputtext += "<TH id='fil4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + logUnitTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'responder_id', '" + textLogUnit + "')\">" + window.fil4_text + "</TH>";
			outputtext += "<TH id='fil5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + logTickTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'ticket_id', '" + textLogTick + "')\">" + window.fil5_text + "</TH>";
			outputtext += "<TH id='fil6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('" + logInfoTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'info', '" + textLogInfo + "')\">" + window.fil6_text + "</TH>";
			outputtext += "<TH id='fil7'>" + pad(3, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in log_arr) {
				latest_log = log_arr[0][0];
				var logcolor = log_arr[key][13];
				if(log_arr[key][0]) {
					if(log_arr[key][11] != "") {
						var theURL = log_arr[key][11];
						outputtext += "<TR CLASS='" + colors[i%2] + "' style='width: " + window.mapWidth + "px; text-decoration: underline; color: blue' onMouseover=\"Tip('" + log_arr[key][12] + "')\" onmouseout='UnTip();' onClick='location.href=\"" + theURL + "\"'>";
						} else {
						outputtext += "<TR CLASS='" + colors[i%2] + "' style='width: " + window.mapWidth + "px;' onMouseover=\"Tip('" + log_arr[key][12] + "')\" onmouseout='UnTip();'>";
						}
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + log_arr[key][1] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + log_arr[key][4] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + log_arr[key][3] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + log_arr[key][6] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + pad(10, log_arr[key][5], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + pad(10, log_arr[key][10], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='color: " + logcolor + ";'>" + pad(3, " ", "\u00a0") + "</TD>";
					outputtext += "</TR>";
					}
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			if(window.latest_logid != latest_log || do_log_refresh) {
				setTimeout(function() {$('the_loglist').innerHTML = outputtext;
					var logtbl = document.getElementById('logtable');
					if(logtbl) {
						var headerRow = logtbl.rows[0];
						var tableRow = logtbl.rows[1];
						if(tableRow) {
							for (var i = 0; i < tableRow.cells.length; i++) {
								if(tableRow.cells[i] && headerRow.cells[i]) {
									headerRow.cells[i].style.width = tableRow.cells[i].clientWidth + "px";
									}
								}
							if(getHeaderHeight(headerRow) >= listheader_height) {
								var theRow = logtbl.insertRow(1);
								theRow.style.height = "20px";
								for (var i = 0; i < tableRow.cells.length; i++) {
									var theCell = theRow.insertCell(i);
									theCell.innerHTML = " ";
									}
								}
							} else {
							var cellwidthBase = window.mapWidth / 28;
							for (var i = 0; i < headerRow.cells.length; i++) {
								headerRow.cells[i].style.width = (cellwidthBase * 4) + "px";
								}
							}				
						}
					window.logFin = true;
					pageLoaded();
					window.latest_logid = latest_log;
					},500);
				}
			}
		window.logLoading = false;
		}				// end function loglist_cb()
	}				// end function load_log()

function log_get() {								// set cycle
	if (log_interval!=null) {return;}
	if(window.logFin == true || window.respLoading == true || window.facLoading == true || window.incLoading == true) {return;}
	log_interval = window.setInterval('log_loop()', 240000);
	}			// end function log_get()

function log_loop() {
	load_log(window.log_field, window.log_direct);
	}			// end function log_loop()	

/* function load_tickerMarkers() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './modules/Ticker/AJAX/get_ticker_markers.php?version=' + randomnumber;
	sendRequest (url,ticker_cb, "");
	function ticker_cb(req) {
		var ticker_arr = JSON.decode(req.responseText);
		for(var key in ticker_arr) {
			var theLat = ticker_arr[key][3];
			var theLng = ticker_arr[key][4];
			var the_point = new  L.LatLng(theLat, theLng);
			var the_header = "Traffic Alert";
			var the_text = ticker_arr[key][1];
			var the_id =  "rss_" + ticker_arr[key][0];
			var the_index = ticker_arr[key][0];
			var the_category =  ticker_arr[key][5];
			var the_descrip = "<TABLE>";
			the_descrip += "<TR class='header'><TD COLSPAN=2 class = 'header text' style='text-align: center;'>" + the_header + "</TD></TR>";
			the_descrip += "<TR class='even'><TD class='td_label text'>Title</TD><TD class='td_data text'>" + the_text + "</TD></TR>";
			the_descrip += "<TR class='odd'><TD class='td_label text'>Category</TD><TD class='td_data text'>" + the_category + "</TD></TR>";
			the_descrip += "<TR class='even'><TD COLSPAN=2 class='td_label text'>Description</TD></TR>";
			the_descrip += "<TR class='even'><TD COLSPAN=2 class='td_data text'>";
			the_descrip +=  ticker_arr[key][2];
			the_descrip += "</TD></TR></TABLE>";
			if((isFloat(theLat)) && (isFloat(theLng))) {
				var rss_marker = create_feedMarker(the_point, the_text, the_descrip, the_id, the_id);		//	10/23/12
				rss_marker.addTo(map);
				}
			}
		}				// end function ticker_cb()
	}				// end function load_ticker()	 */
	
function get_mainmessages(ticket_id, responder_id, facility_id, mi_id, sortby, sortdir, theBox) {
	get_theMessages(ticket_id, responder_id, facility_id, mi_id, sortby, sortdir, window.inorout);
	}
	
var msg_direct = 'DESC';
var msg_field = 'id';
var msg_id = "m1";
var msg_ticket = 0;
var_msg_responder = 0;
var msg_facility = 0;
var msg_mi = 0;

function set_msg_headers(id, header_text, the_bull) {
	if(id == "m1") {
		window.msg1_text = header_text + the_bull;
		window.msg2_text = textMsgTkt;
		window.msg3_text = textMsgType;
		window.msg4_text = textMsgFrom;
		window.msg5_text = textMsgTo;
		window.msg6_text = textMsgSubj;
		window.msg7_text = textMsgDate;
		window.msg8_text = textMsgOwner;
		} else if(id == "m2") {
		window.msg1_text = textMsgID;
		window.msg2_text = header_text + the_bull;
		window.msg3_text = textMsgType;
		window.msg4_text = textMsgFrom;
		window.msg5_text = textMsgTo;
		window.msg6_text = textMsgSubj;
		window.msg7_text = textMsgDate;
		window.msg8_text = textMsgOwner;
		} else if(id == "m3") {
		window.msg1_text = textMsgID;
		window.msg2_text = textMsgTkt;
		window.msg3_text = header_text + the_bull;
		window.msg4_text = textMsgFrom;
		window.msg5_text = textMsgTo;
		window.msg6_text = textMsgSubj;
		window.msg7_text = textMsgDate;
		window.msg8_text = textMsgOwner;
		} else if(id == "m4") {
		window.msg1_text = textMsgID;
		window.msg2_text = textMsgTkt;
		window.msg3_text = textMsgType;
		window.msg4_text = header_text + the_bull;
		window.msg5_text = textMsgTo;
		window.msg6_text = textMsgSubj;
		window.msg7_text = textMsgDate;
		window.msg8_text = textMsgOwner;
		} else if(id == "m5") {
		window.msg1_text = textMsgID;
		window.msg2_text = textMsgTkt;
		window.msg3_text = textMsgType;
		window.msg4_text = textMsgFrom;
		window.msg5_text = header_text + the_bull;
		window.msg6_text = textMsgSubj;
		window.msg7_text = textMsgDate;
		window.msg8_text = textMsgOwner;
		} else if(id == "m6") {
		window.msg1_text = textMsgID;
		window.msg2_text = textMsgTkt;
		window.msg3_text = textMsgType;
		window.msg4_text = textMsgFrom;
		window.msg5_text = textMsgTo;
		window.msg6_text = header_text + the_bull;
		window.msg7_text = textMsgDate;
		window.msg8_text = textMsgOwner;
		} else if(id == "m7") {
		window.msg1_text = textMsgID;
		window.msg2_text = textMsgTkt;
		window.msg3_text = textMsgType;
		window.msg4_text = textMsgFrom;
		window.msg5_text = textMsgTo;
		window.msg6_text = textMsgSubj;
		window.msg7_text = header_text + the_bull;
		window.msg8_text = textMsgOwner;
		} else if(id == "m8") {
		window.msg1_text = textMsgID;
		window.msg2_text = textMsgTkt;
		window.msg3_text = textMsgType;
		window.msg4_text = textMsgFrom;
		window.msg5_text = textMsgTo;
		window.msg6_text = textMsgSubj;
		window.msg7_text = textMsgDate;
		window.msg8_text = header_text + the_bull;
		}
	}
	
function do_msg_sort(id, field, header_text) {
	if(msg_field == field) {
		if(msg_direct == "ASC") { 
			window.msg_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.msg_header = header_text;
			set_msg_headers(id, header_text, the_bull);
			} else if(msg_direct == "DESC") { 
			window.msg_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.msg_header = header_text; 
			set_msg_headers(id, header_text, the_bull);
			}
		} else {
		$(msg_id).innerHTML = msg_header;
		window.msg_field = field;
		window.msg_direct = "ASC";
		window.msg_id = id;
		window.msg_header = header_text;
		var the_bull = "&#9650";
		set_msg_headers(id, header_text, the_bull);
		}
	get_theMessages(window.msg_ticket, window.msg_responder, window.msg_facility, window.msg_mi, field, window.msg_direct, window.inorout);
	return true;
	}
	
function get_theMessages(ticket_id, responder_id, facility_id, mi_id, sort, dir, inorout) {
	if(sort != window.msgs_field) {
		window.msgs_field = sort;
		}
	if(dir != window.msgs_direct) {
		window.msgs_direct = dir;
		}
	if($('the_msglist').innerHTML == "") {
		$('the_msglist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var theSearchstring = "";
	if(ticket_id != 0) {
		theSearchstring = "?ticket_id=" + ticket_id + "&";
		window.msg_ticket = ticket_id;
		window.msg_responder = 0;
		window.msg_facility = 0;
		window.msg_mi = 0;
		} else if(responder_id != 0) {
		theSearchstring = "?responder_id=" + responder_id + "&";
		window.msg_ticket = 0;
		window.msg_responder = responder_id;
		window.msg_facility = 0;
		window.msg_mi = 0;
		} else if(facility_id != 0) {
		theSearchstring = "?facility_id=" + facility_id + "&";	
		window.msg_ticket = 0;
		window.msg_responder = 0;
		window.msg_facility = facility_id;
		window.msg_mi = 0;
		} else if(mi_id != 0) {
		theSearchstring = "?mi_id=" + mi_id + "&";	
		window.msg_ticket = 0;
		window.msg_responder = 0;
		window.msg_facility = 0;
		window.msg_mi = mi_id;
		} else {
		theSearchstring = "?";
		window.msg_ticket = 0;
		window.msg_responder = 0;
		window.msg_facility = 0;
		window.msg_mi = 0;
		}
	var theSortField = "sort=" + sort;
	var theOrder = "&dir=" + dir;
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/sidebar_list_messages.php'+theSearchstring+theSortField+theOrder+"&version=" + randomnumber + "&inorout=" + window.inorout+'&q='+sess_id;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
		var msgtabindex = 1;
		var theNew = 0;
		var the_messages=JSON.decode(req.responseText);
		if((!the_messages) || (the_messages[0][0] == "No Messages")) {
			if(($('inbox_new')) && ($('sent_new'))) { get_message_totals(); }
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Messages.........</marquee>";	
			setTimeout(function() {$('the_msglist').innerHTML = outputtext;},2000);
			setTimeout(function() {get_theMessages(ticket_id, responder_id, facility_id, sort, dir, window.inorout);},60000);	
			return false;
			}
		var theClass = "even";
		if(typeof window.fs_sit !== 'undefined') {
			var outputtext = "<TABLE id='messagestable' class='fixedheadscrolling scrollable' style='width: 100%;'>";
			} else {
			var outputtext = "<TABLE id='messagestable' class='fixedheadscrolling scrollable' style='width: 700px;'>";
			}
		outputtext += "<thead>";
		outputtext += "<TR style='width: 100%;'>";
		outputtext += "<TH id='m1' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message ID' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgIDTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'message', '" + textMsgID + "')\">" + window.msg1_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m2' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Incident ID' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgTickTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'ticket_id', '" + textMsgTkt + "')\">" + window.msg2_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m3' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message Type' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgTypeTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'msg_type', '" + textMsgType + "')\">" + window.msg3_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m4' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message Sender' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgSenderTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'from', '" + textMsgFrom + "')\">" + window.msg4_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m5' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message Recipient' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgWhoTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'to', '" + textMsgTo + "')\">" + window.msg5_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m6' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message Subject' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgSubjTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'subject', '" + textMsgSubj + "')\">" + window.msg6_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m7' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message Date' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgDateTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'date', '" + textMsgDate + "')\">" + window.msg7_text + "</TH>";
		msgtabindex++;
		outputtext += "<TH id='m8' class='plain_listheader text' tabindex=" + msgtabindex + " roll='button' aria-label='Sort by Message Owner' onMouseOver=\"do_hover_listheader(this.id); Tip('" + msgOwnerTip + "');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'owner', '" + textMsgOwner + "')\">" + window.msg8_text + "</TH>";		
		msgtabindex++;
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";			
		for (var key = 0; key < the_messages.length; key++) { 
			var the_message_id = the_messages[key][0];
			var the_record_id = the_messages[key][10];				
			if(the_messages[key][9] == 0) {
				theStatus = "font-weight: bold; font-style: normal;";
				theNew++;
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
			var theTitle = the_delstat + the_messages[key][11];
			outputtext += "<TR class=\"" + theClass + "\" title=\"" + theTitle + "\" roll='button' aria-label='Message ID " + the_messages[key][10] + "' tabindex=" + msgtabindex + " style='width: 100%;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox&ticket_id=" + ticket_id + "&responder_id=" + responder_id + "&facility_id=" + facility_id + "&mi_id=" + mi_id + "&sort= " + sort + "&dir=" + dir + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">";
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + the_messages[key][10] + "</TD>";	//	Message ID
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + the_messages[key][1] + "</TD>";	//	Ticket ID
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + pad(8, the_messages[key][2], "\u00a0") + "</TD>";	//	Type Padded to 8 characters
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + the_messages[key][3] + "</TD>";	//	From
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + the_messages[key][4] + "</TD>";	//	To
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + the_messages[key][5] + "</TD>";	//	Subject
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + "; " + the_del_flag + ";'>" + the_messages[key][7] + "</TD>";		//	Date
			outputtext += "<TD class='plain_list text text_normal' style='" + theStatus + ";'>" + the_messages[key][8] + "</TD>";	//	Owner
			msgtabindex++;
			outputtext += "</TR>";
			if(theClass == "even") {
				theClass = "odd";
				} else {
				theClass = "even";	
				}
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		setTimeout(function() {
			$('the_msglist').innerHTML = outputtext;
			var msgtbl = document.getElementById('messagestable');
			if(msgtbl) {
				var headerRow = msgtbl.rows[0];
				var tableRow = msgtbl.rows[1];
				if(tableRow) {
					for (var i = 0; i < tableRow.cells.length; i++) {
						if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -2 + "px";}
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = msgtbl.insertRow(1);
						theRow.style.height = "20px";
						for (var i = 0; i < tableRow.cells.length; i++) {
							var theCell = theRow.insertCell(i);
							theCell.innerHTML = " ";
							}
						}
					} else {
					var cellwidthBase = 700 / 32;
					headerRow.cells[0].style.width = (cellwidthBase * 4) + "px";
					headerRow.cells[1].style.width = (cellwidthBase * 5) + "px";
					headerRow.cells[2].style.width = (cellwidthBase * 7) + "px";
					headerRow.cells[3].style.width = (cellwidthBase * 5) + "px";
					headerRow.cells[4].style.width = (cellwidthBase * 2) + "px";
					headerRow.cells[5].style.width = (cellwidthBase * 2) + "px";
					headerRow.cells[6].style.width = (cellwidthBase * 2) + "px";
					headerRow.cells[7].style.width = (cellwidthBase * 5) + "px";
					}
				}
			},500);
		}				// end function main_mess_cb()
	}				// end function get_theMessages()
	
function messagelist_setwidths() {
	var viewableRow = 1;
	var msgtbl = document.getElementById('messagestable');
	var headerRow = msgtbl.rows[0];
	for (i = 1; i < msgtbl.rows.length; i++) {
		if(!isViewable(msgtbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	if(i != msgtbl.rows.length) {
		var headerRow = theTable.rows[0];
		var tableRow = theTable.rows[viewableRow];
		if(tableRow) {
			for (var i = 0; i < tableRow.cells.length; i++) {
				if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -2 + "px";}
				}
			}
		if(getHeaderHeight(headerRow) >= listheader_height) {
			var theRow = theTable.insertRow(1);
			theRow.style.height = "20px";
			for (var i = 0; i < tableRow.cells.length; i++) {
				var theCell = theRow.insertCell(i);
				theCell.innerHTML = " ";
				}
			}
		} else {
		var cellwidthBase = window.listwidth / 32;
		headerRow.cells[0].style.width = (cellwidthBase * 4) + "px";
		headerRow.cells[1].style.width = (cellwidthBase * 5) + "px";
		headerRow.cells[2].style.width = (cellwidthBase * 7) + "px";
		headerRow.cells[3].style.width = (cellwidthBase * 5) + "px";
		headerRow.cells[4].style.width = (cellwidthBase * 2) + "px";
		headerRow.cells[5].style.width = (cellwidthBase * 2) + "px";
		headerRow.cells[6].style.width = (cellwidthBase * 2) + "px";
		headerRow.cells[7].style.width = (cellwidthBase * 5) + "px";
		}
	}

function any_track(theForm) {					// returns boolean  - 3/24/12 
	return (theForm.frm_track_disp.selectedIndex > 0);
	}

function isNullOrEmpty(str) {
	if (null == str || "" == str) {return true;} else { return false;}
	}
	
function do_popup(id) {					// added 7/9/09
	if (parent.frames["upper"].logged_in()) {
		try  {map.closeInfoWindow()} catch(err){;}
		var mapWidth = window.mapWidth+32;
		var mapHeight = window.mapHeight+200;		// 3/12/10
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=100,screenX=100,screenY=100";
		var url = "incident_popup.php?id="+id;
		newwindow=window.open(url, id, spec);
		if (isNull(newwindow)) {
			alert ("Popup Incident display requires popups to be enabled. Please adjust your browser options.");
			return;
			}

		newwindow.focus();
		}
	}				// end function do popup()
	
function do_osmap(lat, lng, id, scope, description, type) {					// added 7/9/09
	if (parent.frames["upper"].logged_in()) {
		try  {map.closeInfoWindow()} catch(err){;}
		var mapWidth = window.mapWidth+32;
		var mapHeight = window.mapHeight+200;		// 3/12/10
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=100,screenX=100,screenY=100";
		var title = "Ordnance Survey Map for Ticket - " + scope;
		var url = "os_map.php?id="+id+"&type="+type;
		newwindow=window.open(url, id, spec);
		if (isNull(newwindow)) {
			alert ("Popup Ordnance Survey Map display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}
	}				// end function do_osmap()
	
window.getRoute = function (response) {
	var point, points = [];
	var theText = "";
	for (var i=0; i<response.route_geometry.length; i++) {
		point = new L.LatLng(response.route_geometry[i][0] , response.route_geometry[i][1]);
		points.push(point);
		}
	for (var x=0; x<response.route_instructions.length; x++) {
		if((x != 0) && (x != response.route_instructions.length -1)) {
			theText += "<IMG SRC='http://tile.cloudmade.com/wml/latest/images/routing/arrows/" + response.route_instructions[x][7] + ".png'></IMG>";
			}
		theText += response.route_instructions[x][0] + " ";
		theText += response.route_instructions[x][4] + "<BR />";
		}	
	route= new L.Polyline(points, {
		weight: 3,
		opacity: 0.5,
		smoothFactor: 1
	}).addTo(map);
	route.bringToFront();
	$('directions').innerHTML = theText;
	}
			
function setDirections(toAddress, recfacAddress) {
	$('menu_but2').style.display = 'none';
	$('ticket_detail').style.display = 'none';		
	$('directions_wrapper').style.display = 'block';	
	$('directions').innerHTML = "Getting Route.....";
	fromAddress = the_lat + " " + the_lng;
	fromMarker = new L.Marker(new L.latLng([12.999070,77.568679])).addTo(map);
	toMarker=new L.Marker(new L.latLng([13.006610,77.578130])).addTo(map);
	if(recfacAddress != "") {
		transit = "," + [toAddress] + ",";
		toAddress = recfacAddress;
		} else {
		transit = ",";
		}
        console.log('http://routes.cloudmade.com/' + theAPI + '/api/0.3/' + the_lat + ',' + the_lng + transit + toAddress + '/car.js?callback=getRoute');
        addScript('http://routes.cloudmade.com/' + theAPI + '/api/0.3/' + the_lat + ',' + the_lng + transit + toAddress + '/car.js?callback=getRoute');
	}
	
function sidebar_buttonactions(id) {
	if(id == "s_rc") {
		if($('regions_control')) {$('regions_control').style.display = 'block';} 
		if($('s_ct')) {$('s_ct').style.display='none';}
		if($('s_rc')) {$('s_rc').style.display='none';}
		if($('s_fl')) {$('s_fl').style.display='none';}
		if($('s_ms')) {$('s_ms').style.display='none';}
		if($('s_mo')) {$('s_mo').style.display='none';}
		if($('h_rc')) {$('h_rc').style.display='inline-block';}
		} else if(id == "h_rc") {
		if($('regions_control')) {$('regions_control').style.display= 'none';}
		if($('h_rc')) {$('h_rc').style.display='none';} 
		if($('s_rc')) {$('s_rc').style.display='inline-block';} 
		if($('s_fl')) {$('s_fl').style.display='inline-block';}
		if($('s_ct')) {$('s_ct').style.display='inline-block';}
		if($('s_ms')) {$('s_ms').style.display='inline-block';}
		if($('s_mo')) {$('s_mo').style.display='inline-block';}
		} else if(id == "s_fl") {
		if($('fileList')) {$('fileList').style.display= 'block';}
		if($('thefileslist')) {$('thefileslist').style.display='block';} 
		if($('s_ct')) {$('s_ct').style.display='none';}
		if($('s_fl')) {$('s_fl').style.display='none';}
		if($('s_rc')) {$('s_rc').style.display='none';}
		if($('s_ms')) {$('s_ms').style.display='none';}
		if($('s_mo')) {$('s_mo').style.display='none';}
		if($('h_fl')) {$('h_fl').style.display='inline-block';} 
		} else if(id == "h_fl") {
		if($('fileList')) {$('fileList').style.display= 'none';}
		if($('thefileslist')) {$('thefileslist').style.display='none';}
		if($('h_fl')) {$('h_fl').style.display='none';}
		if($('s_fl')) {$('s_fl').style.display='inline-block';}
		if($('s_ct')) {$('s_ct').style.display='inline-block';}
		if($('s_rc')) {$('s_rc').style.display='inline-block';}
		if($('s_ms')) {$('s_ms').style.display='inline-block';}
		if($('s_mo')) {$('s_mo').style.display='inline-block';}
		} else if(id == "s_ms") {
		if($('message_list')) {$('message_list').style.display= 'block';}
		if($('messageslist')) {$('messageslist').style.display='block';}
		if($('s_ct')) {$('s_ct').style.display='none';}
		if($('s_ms')) {$('s_ms').style.display='none';}
		if($('s_rc')) {$('s_rc').style.display='none';}
		if($('s_fl')) {$('s_fl').style.display='none';}
		if($('s_mo')) {$('s_mo').style.display='none';}
		if($('h_ms')) {$('h_ms').style.display='inline-block';}
		} else if(id == "h_ms") {
		if($('message_list')) {$('message_list').style.display= 'none';}
		if($('messageslist')) {$('messageslist').style.display='none';}
		if($('h_ct')) {$('h_ct').style.display='none';}
		if($('h_ms')) {$('h_ms').style.display='none';}
		if($('s_ct')) {$('s_ct').style.display='inline-block';}
		if($('s_fl')) {$('s_fl').style.display='inline-block';}
		if($('s_rc')) {$('s_rc').style.display='inline-block';}
		if($('s_ms')) {$('s_ms').style.display='inline-block';}
		if($('s_mo')) {$('s_mo').style.display='inline-block';}
		} else if (id == "s_ct") {
		if($('controls')) {$('controls').style.display= 'block';}
		if($('s_ct')) {$('s_ct').style.display='none';}
		if($('s_rc')) {$('s_rc').style.display='none';}
		if($('s_fl')) {$('s_fl').style.display='none';}
		if($('s_ms')) {$('s_ms').style.display='none';}
		if($('s_mo')) {$('s_mo').style.display='none';}
		if($('h_ct')) {$('h_ct').style.display='inline-block';}	
		} else if(id == "h_ct") {
		if($('controls')) {$('controls').style.display= 'none';}
		if($('s_ct')) {$('s_ct').style.display='inline-block';}
		if($('s_rc')) {$('s_rc').style.display='inline-block';}
		if($('s_fl')) {$('s_fl').style.display='inline-block';}
		if($('s_ms')) {$('s_ms').style.display='inline-block';}
		if($('s_mo')) {$('s_mo').style.display='inline-block';}
		if($('h_ct')) {$('h_ct').style.display='none';}
		} else if (id == "s_mo") {
		if($('more')) {$('more').style.display= 'block';}
		if($('s_ct')) {$('s_ct').style.display='none';}
		if($('s_rc')) {$('s_rc').style.display='none';}
		if($('s_fl')) {$('s_fl').style.display='none';}
		if($('s_ms')) {$('s_ms').style.display='none';}
		if($('s_ct')) {$('h_ct').style.display='none';}	
		if($('s_mo')) {$('s_mo').style.display='none';}
		if($('h_mo')) {$('h_mo').style.display='inline-block';}	
		} else if(id == "h_mo") {
		if($('more')) {$('more').style.display= 'none';}
		if($('s_ct')) {$('s_ct').style.display='inline-block';}
		if($('s_rc')) {$('s_rc').style.display='inline-block';}
		if($('s_fl')) {$('s_fl').style.display='inline-block';}
		if($('s_ms')) {$('s_ms').style.display='inline-block';}
		if($('s_mo')) {$('s_mo').style.display='inline-block';}
		if($('h_mo')) {$('h_mo').style.display='none';}
		}
	}

function inboxorsent(ticket_id, responder_id, facility_id, mi_id, sortby, sort, type) {
	window.inorout = type;
	$('foldername').innerHTML = type;
	window.folderchanged = true;
	get_theMessages(ticket_id, responder_id, facility_id, mi_id, sortby, sort, window.inorout);
	}
	
function full_scr_ass() {
	$('assignments_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/full_scr_assignments.php?version=' + randomnumber;
	sendRequest (url,asslist_cb, "");
	function asslist_cb(req) {
		var i=1;
		var ass_arr = JSON.decode(req.responseText);
		if(ass_arr[0][0] == 0) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Current Assignments.........</marquee>";	
			$('assignments_list').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='assignmentstable' class='fixedheadscrolling scrollable' style='width: 100%;'>";
			outputtext += "<thead>";
			outputtext += "<TR c='" + colors[i%2] + "'  style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='ass1' class='plain_listheader_fs text text_bold'>&nbsp;" + textFSTick + "</TH>";
			outputtext += "<TH id='ass2' class='plain_listheader_fs text text_bold'>" + textFSDesc + "</TH>";
			outputtext += "<TH id='ass3' class='plain_listheader_fs text text_bold'>" + textFSUnit + "</TH>";
			outputtext += "<TH id='ass4' class='plain_listheader_fs text text_bold'>" + textFSDS + "</TH>";
			outputtext += "<TH id='ass5' class='plain_listheader_fs text text_bold'>" + textFSDate + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key = 0; key < ass_arr.length; key++) {
				var the_resp = ass_arr[key][6];
				outputtext += "<TR class='" + colors[i%2] + "' style='width: " + window.listwidth + "px;' onClick='myrclick(" + the_resp + ");'>";
				outputtext += "<TD class='plain_list_fs text text_normal' >&nbsp;" + ass_arr[key][0] + "</TD>";
				outputtext += "<TD class='plain_list_fs text text_normal' >" + ass_arr[key][2] + "</TD>";
				outputtext += "<TD class='plain_list_fs text text_normal' >" + ass_arr[key][4] + "</TD>";
				outputtext += "<TD class='plain_list_fs text text_normal' >" + ass_arr[key][5] + "</TD>";
				outputtext += "<TD class='plain_list_fs text text_normal' >" + ass_arr[key][1] + "</TD>";
				outputtext += "</TR>";
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {$('assignments_list').innerHTML = outputtext;
				var asstbl = document.getElementById('assignmentstable');
				if(asstbl) {
					var headerRow = asstbl.rows[0];
					var tableRow = asstbl.rows[1];
					if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].offsetWidth + "px";}
					if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].offsetWidth + "px";}
					if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].offsetWidth + "px";}
					if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].offsetWidth + "px";}
					if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].offsetWidth + "px";}
					}				
				},1000);
			}
		}				// end function asslist_cb()
	full_scr_ass_get();	
	}				// end function full_scr_ass()
	
function full_scr_ass_get() {
	if (fs_interval!=null) {return;}
	fs_interval = window.setInterval('full_scr_ass_loop()', 60000);
	}			// end function mu get()

function full_scr_ass_loop() {
	full_scr_ass();
	}			// end function do_loop()
	
function do_conditions() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/alertlist.php?version=' + randomnumber;
	sendRequest (url,cond_cb, "");
	function cond_cb(req) {
		var cond_arr = JSON.decode(req.responseText);
		if(!cond_arr) { return;}
		for(var f = 0; f < cond_arr.length; f++) {
			var the_condID = cond_arr[f][0];
			var the_condTitle = cond_arr[f][1];
			var the_condTypeTitle = cond_arr[f][2];
			var the_condAddress = cond_arr[f][3];
			var the_condDescription = cond_arr[f][4];
			var the_iconurl = "./rm/roadinfo_icons/" + cond_arr[f][5];
			var the_condDate = cond_arr[f][6];
			var the_condLat = cond_arr[f][7];
			var the_condLng = cond_arr[f][8];
			var info = cond_arr[f][9];
			if($('map_canvas')) {
				if((isFloat(the_condLat)) && (isFloat(the_condLng))) {
					var cmarker = createConditionMarker(the_condLat, the_condLng, the_condID, info, "roadinfo", the_iconurl);
					cmarker.addTo(map);
					}
				}
			}
		}				// end function cond_cb()
	}				// end function do_conditions()	
	
function conditions_get() {
	if (c_interval!=null) {return;}			// ????
	c_interval = window.setInterval('do_conditions_loop()', 30000);
	}			// end function conditions_get()	
	
function do_conditions_loop() {
	do_conditions();
	}			// end function do_conditions_loop()
	
function delfiles(myForm){
	for (i=0;i<myForm.elements.length; i++) {
		if(myForm.elements[i].type == 'checkbox'){
			if(myForm.elements[i].checked == true) {;
				theID = myForm.elements[i].value;
				randomnumber=Math.floor(Math.random()*99999999);
				var url ="./ajax/delfile.php?id=" + theID + "&version=" + randomnumber;
				sendRequest (url, del_handleResult, "");	
				}
			}
		}		// end for ()
	load_files(window.theTicket, window.theResponder, window.theFacility, window.theMI, window.allowedit, window.file_field, window.file_direct, window.thefiletype);
	}		// end function delfiles
	
function del_handleResult(req) {
	}

function check_checkboxes(myForm, checkControl, uncheckControl) {
	var boxesChecked = 0;
	for (i=0;i<myForm.elements.length; i++) {
		if(myForm.elements[i].type =='checkbox'){
			if(myForm.elements[i].checked == true) {
				boxesChecked++;
				}
			}
		}		// end for ()
	if(myForm.elements.length == boxesChecked) {
		$(uncheckControl).style.display = "inline-block";
		$(checkControl).style.display = "none";	
		} else if(boxesChecked == 0) {
		$(uncheckControl).style.display = "none";
		$(checkControl).style.display = "inline-block";		
		} else {
		$(uncheckControl).style.display = "inline-block";
		$(checkControl).style.display = "inline-block";	
		}
	}
	
function do_check(myForm, checkControl, uncheckControl){
	for (i=0;i<myForm.elements.length; i++) {
		if(myForm.elements[i].type =='checkbox'){
			myForm.elements[i].checked = true;
			}
		}		// end for ()
	check_checkboxes(myForm, checkControl, uncheckControl);
	}		// end function do_clear
	
function do_clear(myForm, checkControl, uncheckControl){
	for (i=0;i<myForm.elements.length; i++) {
		if(myForm.elements[i].type =='checkbox'){
			myForm.elements[i].checked = false;
			}
		}		// end for ()
	check_checkboxes(myForm, checkControl, uncheckControl);
	}		// end function do_clear
	
function htmlspecialchars_decode(string, quote_style) {
  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString()
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') {
    // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
}

function doTweet(myForm) {
	if (myForm.frm_message.value.trim() == "") {
		alert("You haven't entered a message yet!");
		return;
		}
	$('theFlag').innerHTML = "<marquee direction='left'>Sending Message</marquee>";
	var tUserid = (myForm.frm_userid.value.trim() != "") ? "&userid=" + myForm.frm_userid.value.trim() : "";
	var tScreenname = (myForm.frm_screenname.value.trim() != "") ? "&screenname=" + myForm.frm_screenname.value.trim() : "";
	var tMessage = URLEncode(myForm.frm_message.value.trim());
	var randomnumber=Math.floor(Math.random()*99999999);
	if(tUserid == "" && tScreenname == ""){
		var url = './ajax/twitter_send.php?message=' + tMessage + '&version=' + randomnumber + '&q=' + sess_id;
		} else {
		var url = './ajax/twitter_direct_send.php?message=' + tMessage + tUserid + tScreenname + '&version=' + randomnumber + '&q=' + sessID;		
		}
	sendRequest (url, theCB, "");
	function theCB(req) {
		var theResult = JSON.decode(req.responseText);
		var theOutput = "";
		if(theResult) {
			if(theResult[0] == 1) {
				theOutput += "Tweet Sent";
				} else {
				theOutput += theResult[0];
				}
			} else {
			theOutput += "Tweet Failed";
			}
		setTimeout(function() {
			myForm.frm_message.value = "";
			myForm.frm_screenname.value = "";
			myForm.frm_userid.value = "";
			$('theFlag').innerHTML = theOutput;
			setTimeout(function() {
				$('theFlag').innerHTML = "";
				},5000);
			},5000);
		}		
	}

function tweetInfo(message) {
	if(!confirm("Are you sure you want to Tweet this Information?")) {
		return;
	}
	var extra = prompt("Any extra information?", "");
	if(extra) {
		message = extra + " " + message;
		}
	var tMessage = message;
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/twitter_send.php?message=' + tMessage + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest(url, theCB2, "");
	function theCB2(req) {
		var theResult2 = JSON.decode(req.responseText);
		var theOutput2 = "";
		if(theResult2) {
			if(theResult2[0] == 1) {
				theOutput2 += "Tweet Sent";
				} else {
				theOutput2 += "Tweet Failed";
				}
			} else {
			theOutput2 += "Tweet Failed";
			}
		alert(theOutput2);
		}
	}
	
function get_status_sel(unit_id, status_val, handle) {
	var status_details = JSON.decode(window.responder_sel);
	var def_bg = "#FFFFFF";
	var def_fg = "#000000";
	for(var i in status_details) {
		for(var j in status_details[i]) {
			if(j == status_val) {
				def_bg = status_details[i][j]['bg_color'];
				def_fg = status_details[i][j]['text_color'];
				}
			}
		}
	var outputtext = "<SELECT CLASS='sit text' id='frm_status_id_u_" + unit_id + "' name='frm_status_id' style='background-color: " + def_bg + "; color: " + def_fg + ";' onchange = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update(" + unit_id + ", this.options[this.selectedIndex].value, \"" + handle + "\");'>";
	for(var i in status_details) {
		outputtext += "<OPTGROUP CLASS='text' LABEL='" + i + "'>";
		for(var j in status_details[i]) {
			var sel = (j == status_val) ? "SELECTED" : "";
			outputtext += "<OPTION CLASS='text' VALUE=" + j + " " + sel + " style='background-color:" + status_details[i][j]['bg_color'] + "; color:" + status_details[i][j]['text_color'] + ";' onMouseover = 'style.backgroundColor = this.backgroundColor;'>" + status_details[i][j]['name'] + "</OPTION>";
			}
		outputtext += "</OPTGROUP>";
		}
	outputtext += "</SELECT>";
	return outputtext;
	}
	
function get_fac_status_sel(fac_id, status_val, handle) {
	var status_details = JSON.decode(window.facility_sel);
	var def_bg = "#FFFFFF";
	var def_fg = "#000000";
	for(var i in status_details) {
		for(var j in status_details[i]) {
			if(j == status_val) {
				def_bg = status_details[i][j]['bg_color'];
				def_fg = status_details[i][j]['text_color'];
				}
			}
		}
	var outputtext = "<SELECT CLASS='sit text' id='frm_status_id_f_" + fac_id + "' name='frm_status_id' STYLE='background-color:" + def_bg + "; color:" + def_fg + ";' onchange = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update_fac(" + fac_id + ", this.options[this.selectedIndex].value, \"" + handle + "\");'>";
	for(var i in status_details) {
		outputtext += "<OPTGROUP CLASS='text' LABEL='" + i + "'>";
		for(var j in status_details[i]) {
			var sel = (j == status_val) ? "SELECTED" : "";
			outputtext += "<OPTION CLASS='text' VALUE=" + j + " " + sel + " STYLE='background-color:" + status_details[i][j]['bg_color'] + "; color:" + status_details[i][j]['text_color'] + ";' onMouseover = 'style.backgroundColor = this.backgroundColor;'>" + status_details[i][j]['name'] + "</OPTION>";
			}
		outputtext += "</OPTGROUP>";
		}
	outputtext += "</SELECT>";
	return outputtext;
	}
	
function get_assigns(unit_id) {
	var retval = "";
	var titleval = "";
	var resp_assigns = JSON.decode(window.theAssigns);
	var the_tickets = JSON.decode(window.theTickets);
	var num_ass = (resp_assigns[unit_id]) ? resp_assigns[unit_id].length : 0;
	if(resp_assigns[unit_id]) {
		if(num_ass == 1) {
			var ticket = resp_assigns[unit_id][0];
			var tickScope = the_tickets[ticket];
			retval += "<SPAN style=width: 100%; text-align: center; display: inline-block;' onMouseover='Tip(\"" + tickScope + "\");' onMouseout='UnTip();'>" + tickScope + "</SPAN>";
			return retval;
			} else if(num_ass >= 2) {
			var tempArr = [];
			for (var i = 0; i < resp_assigns[unit_id].length; i++) {
				tempArr.push(resp_assigns[unit_id][i]);
				}
			var tipStr = tempArr.join();
			retval += "<SPAN style=width: 100%; text-align: center; display: inline-block;' onMouseover='Tip(\"" + tipStr + "\");' onMouseout='UnTip();'>" + num_ass + "</SPAN>";
			return retval;
			} else {
			retval += pad(20, " ", "\u00a0");
			return retval;
			}
		} else {
		retval += pad(20, " ", "\u00a0");
		return retval;
		}
	}
	
function get_assigns_flag(unit_id) {
	var resp_assigns = JSON.decode(window.theAssigns);
	var the_tickets = JSON.decode(window.theTickets);
	var num_ass = (resp_assigns[unit_id]) ? resp_assigns[unit_id].length : 0;
	if(resp_assigns[unit_id]) {
		if(num_ass == 1) {
			var theString = "";
			theString += "<SPAN id='incpop_" + resp_assigns[unit_id][0] + "' class='span_link' style='text-decoration: underline; cursor: pointer;' onMouseover='Tip(\"Click for details\");' onMouseout='UnTip();' onClick='get_incidentinfo(\"" + resp_assigns[unit_id][0] + "\");'>" + the_tickets[resp_assigns[unit_id][0]] + "</SPAN>";
			} else if(num_ass >= 2) {
			var theString = "";
			for (var i = 0; i < num_ass; i++) {
				theString += "<SPAN id='incpop_" + resp_assigns[unit_id][i] + "' class='span_link' style='text-decoration: underline; cursor: pointer;' onMouseover='Tip(\"Click for details\");' onMouseout='UnTip();' onClick='get_incidentinfo(\"" + resp_assigns[unit_id][i] + "\");'>" + the_tickets[resp_assigns[unit_id][i]] + "</SPAN><BR />";
				}
			return theString;
			} else {
			return " ";
			}
		}
	}
	
function get_incidentinfo(ticket_id) {
	get_tickpopup(ticket_id);
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
		if(getHeaderHeight(headerRow) >= listheader_height) {
			var theRow = table.insertRow(1);
			theRow.style.height = "20px";
			for (var i = 0; i < headerRow.cells.length; i++) {
				var theCell = theRow.insertCell(i);
				theCell.innerHTML = " ";
				}
			}
		}	
	}
	
function get_requests() {
	window.reqs_interval = null;
	$('all_requests').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/list_requests_admin.php?showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, requests_cb, "");
	function requests_cb(req) {
		var the_requests=JSON.decode(req.responseText);
		the_string = "<TABLE id='requeststable' class='fixedheadscrolling scrollable' style='width: 100%;'>";
		the_string += "<thead>";
		the_string += "<TR class='plain_listheader text' style='width: " + window.listwidth + "px;'>";
		the_string += "<TH class='plain_listheader text' style='width: 40px; border-right: 1px solid #FFFFFF;'>" + textID + "</TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textPatient + "</TH>";
		the_string += "<TH class='plain_listheader text' style='bold; border-right: 1px solid #FFFFFF;'>" + textPhone + "</TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textContact + "</TH>";
		the_string += "<TH class='plain_listheader text' style='width: 15%; border-right: 1px solid #FFFFFF;'>" + textScope + "</TH>";
		the_string += "<TH class='plain_listheader text' style='width: 10%; border-right: 1px solid #FFFFFF;'>" + textDescription + "</TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textStatus + "</TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textRequested + "</TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textUpdated + "</TH>";
		the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textBy + "</TH>";
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
	reqs_interval = window.setInterval('do_requests_loop()', 30000);
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
	the_string += "<TR class='plain_listheader text' style='width: " + window.listwidth + "px;'>";
	the_string += "<TH class='plain_listheader text' style='width: 40px; border-right: 1px solid #FFFFFF;'>" + textID + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textPatient + "</TH>";
	the_string += "<TH class='plain_listheader text' style='bold; border-right: 1px solid #FFFFFF;'>" + textPhone + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textContact + "</TH>";
	the_string += "<TH class='plain_listheader text' style='width: 15%; border-right: 1px solid #FFFFFF;'>" + textScope + "</TH>";
	the_string += "<TH class='plain_listheader text' style='width: 10%; border-right: 1px solid #FFFFFF;'>" + textDescription + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textStatus + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textRequested + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textUpdated + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textBy + "</TH>";
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
	
var disp_direct = 'ASC';
var disp_field = 'distance';
var disp_id = "disp1";
var searchitem = "";

function do_filter_by_capab(ticket_id) {
	load_dispatch(ticket_id, window.disp_field, window.disp_direct, document.searchform.frm_searchstring.value)
	}

function set_disp_headers(id, header_text, the_bull) {
	if(id == "disp1") {
		window.disp1_text = header_text + the_bull;
		window.disp2_text = textDispNames;
		window.disp3_text = textDispDistance;
		window.disp4_text = textDispCalls;
		window.disp5_text = textDispStatus;
		window.disp6_text = textDispMobile;
		window.disp7_text = textDispAsof;
		} else if(id == "disp2") {
		window.disp2_text = header_text + the_bull;
		window.disp1_text = textDispHandle;
		window.disp3_text = textDispDistance;
		window.disp4_text = textDispCalls;
		window.disp5_text = textDispStatus;
		window.disp6_text = textDispMobile;
		window.disp7_text = textDispAsof;
		} else if(id == "disp3") {
		window.disp3_text = header_text + the_bull;
		window.disp2_text = textDispNames;
		window.disp1_text = textDispHandle;
		window.disp4_text = textDispCalls;
		window.disp5_text = textDispStatus;
		window.disp6_text = textDispMobile;
		window.disp7_text = textDispAsof;
		} else if(id == "disp4") {
		window.disp4_text = header_text + the_bull;
		window.disp2_text = textDispNames;
		window.disp3_text = textDispDistance;
		window.disp1_text = textDispHandle;
		window.disp5_text = textDispStatus;
		window.disp6_text = textDispMobile;
		window.disp7_text = textDispAsof;
		} else if(id == "disp5") {
		window.disp5_text = header_text + the_bull;
		window.disp2_text = textDispNames;
		window.disp3_text = textDispDistance;
		window.disp4_text = textDispCalls;
		window.disp1_text = textDispHandle;
		window.disp6_text = textDispMobile;
		window.disp7_text = textDispAsof;
		} else if(id == "disp6") {
		window.disp5_text = header_text + the_bull;
		window.disp2_text = textDispNames;
		window.disp3_text = textDispDistance;
		window.disp4_text = textDispCalls;
		window.disp1_text = textDispHandle;
		window.disp6_text = textDispMobile;
		window.disp7_text = textDispAsof;
		} else {
		window.disp7_text = header_text + the_bull;
		window.disp2_text = textDispNames;
		window.disp3_text = textDispDistance;
		window.disp4_text = textDispCalls;
		window.disp5_text = textDispStatus;
		window.disp6_text = textDispMobile;
		window.disp1_text = textDispHandle;
		}
	}
	
function do_disp_sort(id, field, header_text) {
	if(disp_field == field) {
		if(disp_direct == "ASC") { 
			window.disp_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.disp_header = header_text;
			set_disp_headers(id, header_text, the_bull);
			} else if(disp_direct == "DESC") { 
			window.disp_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.disp_header = header_text; 
			set_disp_headers(id, header_text, the_bull);
			}
		} else {
		$(disp_id).innerHTML = disp_header;
		window.disp_field = field;
		window.disp_direct = "ASC";
		window.disp_id = id;
		window.disp_header = header_text;
		var the_bull = "&#9650";
		set_disp_headers(id, header_text, the_bull);
		}
	load_dispatch(window.currentTicket, window.disp_field, window.disp_direct, window.searchitem);
	return true;
	}
	
function load_dispatch(ticket_id, sort, dir, searchitem) {
	window.currentTicket = ticket_id;
	if(sort != window.disp_field) {
		window.disp_field = sort;
		}
	if(dir != window.disp_direct) {
		window.disp_direct = dir;
		}
	if($('extra')) {
		$('extra').style.display = "block";
		$('extra').style.width = '80%';
		$('extra').style.height = '80%';
		if($('extra_details').innerHTML == "") {
			$('extra_details').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
			}
		}
	var wrappertext = "<DIV id='disp_outer' style='width: " + window.disp_winWidth + "; height: 90%; display: block;'><BR />";
	wrappertext += 			"<DIV id = 'dispmailform' style='position: relative; top: 10px; left: 30%; height: 50%; width: 40%; display: none; z-index: 9999;'></DIV>";		
	wrappertext += 			"<DIV id = 'leftcol' style='position: relative; top: 10px; float: left; width: 50%;'>";
	wrappertext += 				"<DIV id='dispatches' style='display: block; width: 100%;'>";
	wrappertext += 					"<DIV id='dispatchheader' class='header text_center text_bold text_biggest' style='display: block; width: 100%; text-align: middle;'>Incident Dispatch - <SPAN id='theScope'></SPAN></DIV>";
	wrappertext += 					"<DIV class='scrollableContainer' id='dispatchlist' style='display: block; height: 80%; width: 100%;'>";
	wrappertext += 						"<DIV class='scrollingArea' id='the_displist' style='max-height: 90%; ><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>";		
	wrappertext += 					"</DIV>";
	wrappertext += 				"</DIV>";
	wrappertext += "		<SPAN ID='sub_but' class='plain text' style='display: inline-block; float: none; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate_disp(document.dispatch_frm);'><SPAN STYLE='float: left;'>Next</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
	wrappertext += "		<SPAN ID='mail_dir_but' class='plain text' style='display: none; float: none; width: 150px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_direcs_mail_win();'><SPAN STYLE='float: left;'>Mail Directions</SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>";
	wrappertext += "		<SPAN ID='reset_but' class='plain text' style='display: none; float: none; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='show_butts(to_hidden) ; doReset();'><SPAN STYLE='float: left;'>Reset</SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>";
	wrappertext += "		<SPAN ID='can_but' class='plain text' style='display: inline-block; float: none; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"$('extra').style.display='none';\"><SPAN STYLE='float: left;'>Cancel</SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>";
	wrappertext += "		<FORM METHOD='post' NAME='searchform'>";
	wrappertext += "		<SPAN class='td_label text middle' style='display: inline;'>Filter by capability</SPAN><SPAN class='td_data text middle'><INPUT TYPE='text' style='display: inline;' NAME='frm_searchstring' VALUE= '" + searchitem + "' /></SPAN>";
	wrappertext += "		<SPAN ID='search_but' class='plain text' style='display: inline-block; float: none; width: 100px; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_filter_by_capab(" + ticket_id + ");'><SPAN STYLE='float: left;'>Filter</SPAN><IMG STYLE='float: right;' SRC='./images/filter_small.png' BORDER=0></SPAN>";
	wrappertext += "		</FORM>";
	wrappertext += 			"</DIV>";		
	wrappertext += "		<DIV id='rightcol' style='position: relative; top: 10px; float: right; width: 45%;'>";
	wrappertext += "			<DIV id='map_canvas' style = 'border: 1px outset #707070;'></DIV>";
	wrappertext += "			<SPAN id='map_caption' class='text_center bold text_big' style='display: inline-block;'>Map</SPAN><BR /><BR />";
	wrappertext += "			<DIV ID='legend' CLASS='legend' STYLE='text-align: center; vertical-align: middle;'>";
	wrappertext += "				<SPAN CLASS='header text_big'>Units Legend:</SPAN>";
	wrappertext += "			</DIV><BR />";
	wrappertext += "			<DIV ID='directions' CLASS='text' STYLE='text-align: left; vertical-align: text-top;'></DIV>";
	wrappertext += "		</DIV>";
	wrappertext += "	</DIV>";
	wrappertext += "<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='mail_direcs' onsubmit='return mail_direcs(this);'>";
	wrappertext += "<INPUT TYPE='hidden' NAME='frm_u_id' VALUE='' />";
	wrappertext += "<INPUT TYPE='hidden' NAME='frm_direcs' VALUE='' />";
	wrappertext += "<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Incident - ' />"
	wrappertext += "<INPUT TYPE='hidden' NAME='frm_scope' VALUE='' />"
	wrappertext += "<INPUT TYPE='hidden' NAME='frm_tick_id' VALUE='" + ticket_id + "' />";
	wrappertext += "<INPUT TYPE='hidden' NAME='showform' VALUE='1' />"
	wrappertext += "</FORM>";
	$('extra_details').innerHTML = wrappertext;
	$('extra_header').innerHTML = "Incident Dispatch";
	$('extra_header').style.width = window.disp_winWidth + "px";
	$('map_canvas').style.width = window.mapWidth + "px";
	$('map_canvas').style.height = window.mapHeight + "px";
	$('map_caption').style.width = window.mapWidth + "px";
	if(window.map) {window.map = null;}
	init_map(1, def_lat, def_lng, "", 13, theLocale, useOSMAP, "br");
	map.setView([def_lat, def_lng], 13);
	var bounds = map.getBounds();	
	var zoom = map.getZoom();
	var got_points = false;	// map is empty of points
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/routes_form.php?ticket_id=' + ticket_id + '&sortby=' + window.disp_field + '&dir=' + window.disp_direct + '&searchstring=' + searchitem + '&version=' + randomnumber+'&q='+sess_id;
	sendRequest (url,dispatchlist_cb, "");
	function dispatchlist_cb(req) {
		var i = 1;
		var disp_arr = JSON.decode(req.responseText);
		document.email_form.frm_scope.value = disp_arr[3];
		$('theScope').innerHTML = disp_arr[3];
		$('legend').innerHTML = disp_arr[2];
		if(disp_arr[0] == 0) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold; color: #000000;'>......No Responders.........</marquee>";	
			return false;
			} else {
			var disp_resp = disp_arr[1];
			window.theLat = disp_arr[0].lat;
			window.theLng = disp_arr[0].lng
			var facility = disp_arr[0].facility;
			var rec_facility = disp_arr[0].rec_facility;			
			var outputtext = "<FORM METHOD='post' NAME='dispatch_frm' ACTION='./ajax/form_post.php?ticket_id=" + ticket_id + "&q=" + sess_id + "&function=dispatch'>";
			outputtext += "<TABLE id='disptable' class='fixedheadscrolling scrollable' style='width: 98%;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%; background-color: #EFEFEF;'>";
			outputtext += "<TH id='arrow' class='plain_listheader text'>&nbsp;&nbsp;&nbsp;</TH>";
			outputtext += "<TH id='selector' class='plain_listheader text'>Select</TH>";
			outputtext += "<TH id='disp1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'handle', '" + textDispHandle + "');\">" + window.disp1_text + "</TH>";
			outputtext += "<TH id='disp2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'name', '" + textDispNames + "');\">" + window.disp2_text + "</TH>";
			outputtext += "<TH id='disp3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'distance', '" + textDispDistance + "');\">" + window.disp3_text + "</TH>";
			outputtext += "<TH id='disp4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'dispatch_str', '" + textDispCalls + "');\">" + window.disp4_text + "</TH>";
			outputtext += "<TH id='disp5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'unitstatus', '" + textDispStatus + "');\">" + window.disp5_text + "</TH>";
			outputtext += "<TH id='disp6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'mobile', '" + textDispMobile + "');\">" + window.disp6_text + "</TH>";
			outputtext += "<TH id='disp7' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_disp_sort(this.id, 'updated', '" + textDispAsof + "');\">" + window.disp7_text + "</TH>";
			outputtext += "<TH id='disp8'>" + pad(3, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in disp_resp) {
				if(disp_resp[key][0]) {
					var dispcolor = disp_resp[key][4];
					var bgcolor = disp_resp[key][3];
					var unit_id = disp_resp[key][0];
					unit_ids[i] = unit_id;
					window.unit_names[i] = disp_resp[key][1];
					window.unit_handles[i] = disp_resp[key][2];
					if(disp_resp[key][8] == window.nm_coord || disp_resp[key][9] == window.nm_coord) {
						window.lats[i] = def_lat;
						window.lngs[i] = def_lng;
						} else {
						window.lats[i] = disp_resp[key][8];
						window.lngs[i] = disp_resp[key][9];
						}
					outputtext += "<TR ID = 'row_" + i + "' CLASS='" + colors[i%2] + "' style='width: 100%;' onMouseover=\"Tip('Some text here')\" onmouseout='UnTip();' onClick='show_disp_line(" + i + ", this.id, " + unit_id + ");'>";
					outputtext += "<TD class='plain_list text text_left'><IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + i + "\"  STYLE = 'visibility: hidden;' /></TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' ID='C_" + i + "' NAME = 'unit_" + i + "' " + disp_resp[key][7] + " />&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + pad(7, disp_resp[key][2], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + disp_resp[key][1] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + disp_resp[key][10] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + pad(20, disp_resp[key][14], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + disp_resp[key][5] + "</TD>";
					var mobile = (disp_resp[key][11] == "1") ? "Yes" : "No";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + pad(4, mobile, "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + pad(10, disp_resp[key][12], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' style='background-color: " + bgcolor + "; color: " + dispcolor + ";'>" + pad(3, " ", "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					window.nr_units++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='" + ticket_id + "' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= '" + sess_id + "' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= '' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= '' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= '1' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_facility_id' 	VALUE= '" + facility + "' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_rec_facility_id' VALUE= '" + rec_facility + "' />";
			outputtext += "<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= 'New' />";
			outputtext += "</FORM>";
			}
			setTimeout(function() {
				$('the_displist').innerHTML = outputtext;
				},500);
			setTimeout(function() {				
				var disptbl = document.getElementById('disptable');
				if(disptbl) {
					var headerRow = disptbl.rows[0];
					var tableRow = disptbl.rows[1];
					if(tableRow) {
						for (var i = 0; i < tableRow.cells.length; i++) {				
							if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth - 2 + "px";}
							}
						if(getHeaderHeight(headerRow) >= 30) {
							var theRow = disptbl.insertRow(1);
							theRow.style.height = "20px";
							for (var i = 0; i < tableRow.cells.length; i++) {
								var theCell = theRow.insertCell(i);
								theCell.innerHTML = " ";
								}
							}
						} else {
						var cellwidthBase = window.mapWidth / 28;
						for (var i = 0; i < headerRow.cells.length; i++) {
							headerRow.cells[i].style.width = (cellwidthBase * 4) + "px";
							}
						}				
					}
				$('the_displist').style.height = window.disp_listheight;
				},1000);
		}				// end function dispatchlist_cb()
	}				// end function load_dispatch()
	
function get_assignments() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_assignments.php?version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,assignments_cb, "");
	function assignments_cb(req) {
		var assign_arr = JSON.decode(req.responseText);
		if(!assign_arr) { return;}
		for(var f = 0; f < assign_arr.length; f++) {
			var assignsID = assign_arr[f][0];
			var respID = assign_arr[f][1];
			var tickID = assign_arr[f][2];
			var tickScope = assign_arr[f][3];
			}
		allAssigns = assign_arr;
		}				// end function assignments_cb()
	}				// end function get_assignments()
	
function get_unit_assigns(unit_id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_unit_assignments.php?unit=' + unit_id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,assignments_cb, "");
	function assignments_cb(req) {
		var assign_arr = JSON.decode(req.responseText);
		if(!assign_arr) {return;}
		for(var key in assign_arr) {
			if(isInteger(key)) {
				var the_assignsid = "rincs_" + key;
				if($(the_assignsid)) {
					$(the_assignsid).innerHTML = assign_arr[key];
					}
				}
			}
		}				// end function assignments_cb()	
	}
	
function get_unit_categories() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_unit_categories.php?version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,cats_cb, "");
	function cats_cb(req) {
		var cats_arr = JSON.decode(req.responseText);
		if(!cats_arr) { return;}
		if($('boxes')) {$('boxes').innerHTML = cats_arr[0]; set_categories();}
		}				// end function cats_cb()
	}				// end function get_unit_categories()
