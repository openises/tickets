<?php
require_once('../incs/functions.inc.php');
@session_start();
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$showmaps = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) ? 1 : 0;
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$fac_curr_cats = get_fac_category_butts();
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}
?>
var doDebug = false;
function isIE() { 
	if((navigator.appName == 'Microsoft Internet Explorer') || ((navigator.appName == 'Netscape') && (new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})").exec(navigator.userAgent) != null))) {
		return true;
		} else {
		return false;
		}
	}
	
var internet = <?php print $showmaps;?>;
var geo_provider = <?php print get_variable('geocoding_provider');?>;
var BingKey = "<?php print get_variable('bing_api_key');?>";
var GoogleKey = "<?php print get_variable('gmaps_api_key');?>";
var icons=[];
icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>] = 1;	// blue
icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>] = 2;	// yellow
icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>] =  3;	// red

var inczindexno = 30000;
var unitzindexno = 20000;
var faczindexno = 10000;

var status_control = [];
var status_bgcolors = [];
var status_textcolors = [];
var fac_status_control = [];
var theLayer;

var dzf = parseInt("<?php print get_variable('def_zoom_fixed');?>");
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

var max_zoom = <?php print get_variable('def_zoom');?>;
var popupInfo = "";
var currPopup;
var map;				// make globally visible
var myMarker;
var condMarkers;
var sortby = '`date`';	//	10/23/12
var sort = "DESC";	//	10/23/12
var columns = "<?php print get_msg_variable('columns');?>";	//	10/23/12
var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	10/23/12
var thescreen = 'ticket';	//	10/23/12
var thelevel = '<?php print $the_level;?>';
var incs_sortarray = [];
var resps_sortarray = [];
var facs_sortarray = [];
var msgs_sortarray = [];
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
var locale = <?php print get_variable('locale');?>;
var my_Local = <?php print get_variable('local_maps');?>;
var lon = <?php print get_variable('def_lng');?>;
var lat = <?php print get_variable('def_lat');?>;
var bounds;
var zoom = <?php print get_variable('def_zoom');?>;
var inorout = "inbox";
var folderchanged = false;
var facFin = false;
var fileFin = false;
var theResponder = 0;
var theTicket = 0;
var theFacility = 0;
var thefiletype = 1;
var states_arr = <?php echo json_encode($states); ?>;

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

<?php
if (is_guest()) {													// 8/25/10
?>	
	parent.frames["upper"].$("add").style.display  = 				"none";			// guests disallowed
	try { parent.frames["upper"].$("ics").style.display  =			"none";}	
	catch(e) { }
	try { parent.frames["upper"].$("has_button").style.display  = 	"none";}
	catch(e) { }	
<?php
	}		// end guest - needs other levels!
?>

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

var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php
var check_initialized = false;
var check_interval = null;

var swi=1;
var na=document.getElementsByTagName("blink");

function blink_continue() {
	if (b_interval!=null) {return;}
	b_interval = window.setInterval('blink_loop()', 500); 
	}			// end function mu get()

function blink_loop() {
	do_blink();
	}			// end function do_loop()		

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

function change_status_sel(the_control, the_val) {
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
				parent.frames["upper"].show_msg ('Responder Status Changed');
				}
			}
		}
	}
	
function logged_in() {								// returns boolean
	var temp = parent.frames["upper"].$("whom").innerHTML==NOT_STR;
	return !temp;
	}

function set_regions_control() {
	var reg_control = "<?php print get_variable('regions_control');?>";
	var regions_showing = "<?php print get_num_groups();?>";
	if(regions_showing) {
		if (reg_control == 0) {
			$('top_reg_box').style.display = 'none';
			$('regions_outer').style.display = 'block';
			} else {
			$('top_reg_box').style.display = 'block';
			$('regions_outer').style.display = 'none';			
			}
		}
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
	if(priority == 1) {
		priority_name="normal";
	}
	if(priority == 2) {
		priority_name="medium";
	}
	if(priority == 3) {
		priority_name="high";
	}
	if(priority == 4) {
		priority_name="all";
	}
	if(priority == 5) {
		priority_name="none";
	}

	if(priority == 1) {
		for (var i = 1; i < tmarkers.length; i++) {
			if (tmarkers[i]) {
				if ((tmarkers[i].id == priority) && (tmarkers[i].category == category)) {
					tmarkers[j].addTo(map);	
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
		$('medium').checked = true;
		$('high').checked = false;
		$('all').checked = false;
		$('none').checked = false;
		$('pri_all').style.display = '';
		$('pri_none').style.display = '';
		}	//	end if priority == 2
	if(priority == 3) {
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
		$(pri_control).checked = false;
		} else {
		$(pri_control).checked = true;
		}
	}

//	End of Tickets show / hide by Priority functions

// 	Units show / hide functions				
	
function set_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
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
	var curr_cats = <?php echo json_encode($curr_cats); ?>;
	var cat_sess_stat = <?php echo json_encode($cat_sess_stat); ?>;
	var hidden = <?php print json_encode($hidden); ?>;
	var shown = <?php print json_encode($shown); ?>;
	var number_of_units = <?php print get_no_units(); ?>;
	if(hidden != 0) {
		$('RESP_ALL').style.display = '';
		$('RESP_ALL_BUTTON').style.display = '';
		$('RESP_ALL').checked = false;	
		} else {
		$('RESP_ALL').style.display = 'none';
		$('RESP_ALL_BUTTON').style.display = 'none';
		$('RESP_ALL').checked = false;
		}
	if((shown != 0) && (number_of_units != 0)) {
		$('RESP_NONE').style.display = '';
		$('RESP_NONE_BUTTON').style.display = '';
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
			$(catname).checked = true;
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
			$(catname).checked = false;
			}				
		}
	if(resptbl) {
		if($('screenname').innerHTML == "responders") {responderlist2_setwidths();} else {responderlist_setwidths();}
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
		$(units_control).checked = false;
		} else {
		$(units_control).checked = true;
		}
	do_view_cats();
	}
	
function set_buttons(theType) {
	var curr_cats = <?php echo json_encode($curr_cats); ?>;
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
	var curr_cats = <?php echo json_encode($curr_cats); ?>;
	if ($('RESP_ALL').checked == true) {
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gb_handleResult, params);
			$(category).checked = true;				
			for (var j = 1; j < rmarkers.length; j++) {
				var catid = category + j;
				if($(catid)) {
					$(catid).style.display = "";
				}
				if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {			
					rmarkers[j].addTo(map);		
					}
				}
			}
			$('RESP_ALL').checked = false;
			$('RESP_ALL').style.display = 'none';
			$('RESP_ALL_BUTTON').style.display = 'none';				
			$('RESP_NONE').style.display = '';
			$('RESP_NONE_BUTTON').style.display = '';				
			$('go_button').style.display = 'none';
			$('can_button').style.display = 'none';				

	} else if ($('RESP_NONE').checked == true) {
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, gb_handleResult, params);	
			$(category).checked = false;				
			for (var j = 1; j < rmarkers.length; j++) {
				var catid = category + j;
				if($(catid)) {
					$(catid).style.display = "none";
					}
				if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {
					map.removeLayer(rmarkers[j]);		
					}
				}
			}
			$('RESP_NONE').checked = false;
			$('RESP_ALL').style.display = '';
			$('RESP_ALL_BUTTON').style.display = '';				
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
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gb_handleResult, params);
				$(category).checked = true;			
				for (var j = 1; j < rmarkers.length; j++) {
					var catid = category + j;
					if($(catid)) {
						$(catid).style.display = "";
						}
					if ((rmarkers[j]) && (rmarkers[j].category) && (rmarkers[j].category == category)) {			
						rmarkers[j].addTo(map);		
						}
					}
				}
			}
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];				
			if ($(category).checked == false) {
				y++;
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
			$('RESP_ALL').style.display = '';
			$('RESP_ALL_BUTTON').style.display = '';
			$('RESP_NONE').style.display = '';
			$('RESP_NONE_BUTTON').style.display = '';
		}
		if(x == 0) {
			$('RESP_ALL').style.display = '';
			$('RESP_ALL_BUTTON').style.display = '';
			$('RESP_NONE').style.display = 'none';
			$('RESP_NONE_BUTTON').style.display = 'none';
		}
		if(x == curr_cats.length) {
			$('RESP_ALL').style.display = 'none';
			$('RESP_ALL_BUTTON').style.display = 'none';
			$('RESP_NONE').style.display = '';
			$('RESP_NONE_BUTTON').style.display = '';
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
	var fac_curr_cats = <?php echo json_encode($fac_curr_cats); ?>;
	var fac_cat_sess_stat = <?php echo json_encode(get_fac_session_status()); ?>;
	var fac_hidden = <?php print find_fac_hidden(); ?>;
	var fac_shown = <?php print find_fac_showing(); ?>;
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
		$(fac_control).checked = false;
		} else {
		$(fac_control).checked = true;
		}
	do_view_fac_cats();
	}
	
function set_fac_buttons(theType) {
	var curr_cats = <?php echo json_encode($fac_curr_cats); ?>;
	if(theType == "category") {
		for (var i = 0; i < curr_cats.length; i++) {
			var category = curr_cats[i];
			if ($(category).checked == true) {
				if($('fac_NONE').checked == true) {
					$('fac_NONE').checked = false;
					}
				}
			return true;
			}
		} else if (theType == "all") {
		if($('fac_ALL').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];				
				$(category).checked = true;
				}
			return true;
			}
		} else if (theType == "none") {
		if($('fac_NONE').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];				
				$(category).checked = false;
				}
			return true;
			}
		}
	}

function do_go_facilities_button() {							// 12/03/10	Show Hide categories
	var factbl = document.getElementById('facilitiestable');
	var fac_curr_cats = <?php echo json_encode($fac_curr_cats); ?>;
	if ($('fac_ALL').checked == true) {
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_category = fac_curr_cats[i];
			var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
			var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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

	var hidden = <?php print $hidden; ?>;
	var shown = <?php print $shown; ?>;
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
			var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
			var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
				var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
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
				var params = "f_n=show_hide_bnds_" +URLEncode(bnds)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
				var url = "persist2.php";	//	3/15/11
				sendRequest (url, gbb_handleResult, params);
				$(bnds).checked = false;
				if(bound_names[key]) {			
					map.removeLayer(boundary[key]);		
					}
				}
			}	
		}

	var hidden = <?php print find_bnd_hidden(); ?>;
	var shown = <?php print find_bnd_showing(); ?>;
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
	var bnd_curr = <?php echo json_encode(get_bnd_session()); ?>;
	var bnd_names_curr = <?php echo json_encode(get_bnd_session_names()); ?>;
	var bnd_hidden = <?php print find_bnd_hidden(); ?>;
	var bnd_shown = <?php print find_bnd_showing(); ?>;
	for (var key in bnd_curr) {
		var bnds = bnd_curr[key];
		var bnd_nm = bnd_names_curr[key];
		if(bnds == "s") {
			$(bnd_nm).checked = true;
			if(window.boundary[key]) {window.boundary[key].addTo(map);}
			} else {
			map.removeLayer(boundary[key]);				
			$(bnd_nm).checked = false;
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
	var divarea = div_area 
	var hide_cont = hide_cont 
	var show_cont = show_cont 
	if($(divarea)) {
		$(divarea).style.display = 'none';
		$(hide_cont).style.display = 'none';
		$(show_cont).style.display = '';
		} 
	var params = "f_n=" +controlarea+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
	var url = "persist2.php";
	sendRequest (url, gb_handleResult, params);			
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
	var divarea = div_area
	var hide_cont = hide_cont 
	var show_cont = show_cont 
	if($(divarea)) {
		$(divarea).style.display = '';
		$(hide_cont).style.display = '';
		$(show_cont).style.display = 'none';
		}
	var params = "f_n=" +controlarea+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
	var url = "persist2.php";
	sendRequest (url, gb_handleResult, params);					
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
	}
function show_btns_scheduled() {						// 4/30/10
	$('btn_scheduled').style.display = 'inline';
	$('btn_can').style.display = 'inline';
	}
function hide_btns_scheduled() {
	$('btn_scheduled').style.display = 'none';
	$('btn_can').style.display = 'none';
	}
	
function do_add_note (id) {				// 8/12/09
	var url = "add_note.php?ticket_id="+ id;
	var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
	noteWindow.focus();
	}

function do_aprs_window() {				// 6/25/08
	var url = "http://www.openaprs.net/?center=" + <?php print get_variable('def_lat');?> + "," + <?php print get_variable('def_lng');?>;
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
		var width = <?php print get_variable('map_width');?>+600;
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
	
function loc_lkup(my_form) {		   						// 7/5/10
	if(!$('map_canvas')) {return; }
	var theLat = my_form.frm_lat.value;
	var theLng = my_form.frm_lng.value;	
	if(my_form.frm_street.value.trim() != "" && my_form.frm_city.value.trim() == "") {
		var theCity = my_form.frm_street.value.trim();
		var theStreet = "";
		} else {
		var theCity = my_form.frm_city.value.trim();
		var theStreet = my_form.frm_street.value.trim();
		}
	if (theCity == "" || my_form.frm_state.value.trim() == "") {
		alert ("City and State are required for location lookup.");
		return false;
		}
	var myAddress = theStreet + ", " + theCity + " " + my_form.frm_state.value.trim();
	control.options.geocoder.geocode(myAddress, function(results) {
		if(!results[0]) {
			pt_to_map (my_form, theLat, theLng);
			return;
			}
		var r = results[0]['center'];
		theLat = r.lat;
		theLng = r.lng;
		pt_to_map (my_form, theLat, theLng);
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
	var loc = <?php print get_variable('locale');?>;
	if(loc == 0) { my_form.frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
	if(loc == 1) { my_form.frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
	if(loc == 2) { my_form.frm_ngs.value=LLtoUTM(theLat, theLng, 5); }
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
	marker = L.marker([theLat, theLng], {icon: icon});
	marker.addTo(map);
	map.setView([theLat, theLng], 16);
	}				// end function pt_to_map ()

function newGetAddress(latlng, currform) {
	var popup = L.popup();
	var loc = <?php print get_variable('locale');?>;
	control.options.geocoder.reverse(latlng, 20, function(results) {
		if(window.geo_provider == 0){
			var r1 = results[0];
			var r = r1['properties']['address'];
			} else if(window.geo_provider == 1) {
			var r = results[0];
			} else if(window.geo_provider == 2) {
			var r1 = results[0];
			var r = {city: r1.city, house_number: "", road: r1.street, properState: r1.state};
			}
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
			switch(currform) {
				case "a":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.res_add_Form.frm_street.value = theStreet1 + theStreet2;
					document.res_add_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.res_add_Form.frm_state.value = theState;
					document.res_add_Form.frm_lat.value = lat; 
					document.res_add_Form.frm_lng.value = lng; 
					document.res_add_Form.show_lat.value = lat; 
					document.res_add_Form.show_lng.value = lng;
					var loc = <?php print get_variable('locale');?>;
					if(loc == 0) { document.res_add_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(loc == 1) { document.res_add_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(loc == 2) { document.res_add_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.res_add_Form.frm_street.focus();	
					break;

				case "e":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.res_edit_Form.frm_street.value = theStreet1 + theStreet2;
					document.res_edit_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.res_edit_Form.frm_state.value = theState;
					document.res_edit_Form.frm_lat.value = lat; 
					document.res_edit_Form.frm_lng.value = lng; 
					document.res_edit_Form.show_lat.value = lat; 
					document.res_edit_Form.show_lng.value = lng;
					var loc = <?php print get_variable('locale');?>;
					if(loc == 0) { document.res_edit_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(loc == 1) { document.res_edit_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(loc == 2) { document.res_edit_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.res_edit_Form.frm_street.focus();					
					break;
					
				case "wa":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.loc_add_Form.frm_street.value = theStreet1 + theStreet2;
					document.loc_add_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.loc_add_Form.frm_state.value = theState;
					document.loc_add_Form.frm_lat.value = lat; 
					document.loc_add_Form.frm_lng.value = lng; 
					document.loc_add_Form.show_lat.value = lat; 
					document.loc_add_Form.show_lng.value = lng;
					var loc = <?php print get_variable('locale');?>;
					if(loc == 0) { document.loc_add_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(loc == 1) { document.loc_add_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(loc == 2) { document.loc_add_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.loc_add_Form.frm_street.focus();	
					break;

				case "we":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.loc_edit_Form.frm_street.value = theStreet1 + theStreet2;
					document.loc_edit_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.loc_edit_Form.frm_state.value = theState;
					document.loc_edit_Form.frm_lat.value = lat; 
					document.loc_edit_Form.frm_lng.value = lng; 
					document.loc_edit_Form.show_lat.value = lat; 
					document.loc_edit_Form.show_lng.value = lng;
					var loc = <?php print get_variable('locale');?>;
					if(loc == 0) { document.loc_edit_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(loc == 1) { document.loc_edit_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(loc == 2) { document.loc_edit_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.loc_edit_Form.frm_street.focus();					
					break;
					
				case "c":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theState = (state != "") ? states_arr[state] : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street + ", " : "";
					var address3 = (theCity != "") ? theCity + ", " : "";
					var address4 = (r.state != "") ? r.state : "";					
					document.c.frm_address.value = address1 + address2 + address3 + address4;
					document.c.frm_lat.value = lat; 
					document.c.frm_lng.value = lng; 
					document.c.frm_address.focus();
					break;
					
				case "u":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theState = (state != "") ? states_arr[state] : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street + ", " : "";
					var address3 = (theCity != "") ? theCity + ", " : "";
					var address4 = (r.state != "") ? r.state : "";					
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
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.add.frm_state.value = theState;
					document.add.frm_lat.value = lat; 
					document.add.frm_lng.value = lng; 
					document.add.show_lat.value = lat; 
					document.add.show_lng.value = lng; 
					document.add.frm_street.focus();
					var loc = <?php print get_variable('locale');?>;
					if(loc == 0) { document.add.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(loc == 1) { document.add.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(loc == 2) { document.add.frm_ngs.value=LLtoUTM(lat, lng, 5); }
					break;
					
				case "ei":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street : "";
					document.edit.frm_street.value = address1 + address2;
					document.edit.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.edit.frm_state.value = theState;
					document.edit.frm_lat.value = lat; 
					document.edit.frm_lng.value = lng; 
					document.edit.show_lat.value = lat; 
					document.edit.show_lng.value = lng; 
					document.edit.frm_street.focus();
					var loc = <?php print get_variable('locale');?>;
					if(loc == 0) { document.edit.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(loc == 1) { document.edit.frm_osgb.value=LLtoOSGB(lat, lng, 5); }
					if(loc == 2) { document.edit.frm_utm.value=LLtoUTM(lat, lng, 5); }							
					break;
					
				default:
					alert ("596: error");
				}		// end switch()
			var theContent1 = (number != "") ? number + " ": "";
			var theContent2 = (street != "") ? street + ", ": "";
			var theContent3 = (theCity != "") ? theCity + ", ": "";
			var theContent4 = (theState != "") ? theState + ", ": "";
			var theContent = theContent1 + theContent2 + theContent3 + theContent4;
			popup
				.setLatLng(latlng)
				.setContent(theContent)
				.openOn(map);
			}
		});
	}
	
function getTheAddress(latlng) {
	var loc = <?php print get_variable('locale');?>;
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
			var loc = <?php print get_variable('locale');?>;
			if(loc == 0) { document.add.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
			if(loc == 1) { document.add.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
			if(loc == 2) { document.add.frm_ngs.value=LLtoUTM(lat, lng, 5); }
			}
		});
	}

function do_usng_conv(theForm){						// usng to LL array			- 12/4/08
	tolatlng = new Array();
	USNGtoLL(theForm.frm_ngs.value, tolatlng);

	var point = new L.LatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
	map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
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

function do_coords(inlat, inlng) { 										// 9/14/08
	if(inlat.toString().length==0) return;								// 10/15/08
	var str = inlat + ", " + inlng + "\n";
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
		var marker = L.marker([lat, lon], {icon: icon, title: tip, zIndexOffset: window.inczindexno, riseOnHover: true, riseOffset: 30000}).bindPopup(info).openPopup();
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
		marker.id = color;
		marker.category = category;
		marker.region = region;		
		marker.stat = stat;
		tmarkers[theid] = marker;
		tmarkers[theid][lat] = lat;
		tmarkers[theid][lon] = lon;
		var point = new L.LatLng(lat, lon);
		bounds.extend(point);
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
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
			});
		marker.on('click', function(e) {
			if($('screenname').innerHTML == "fullscreen") {
				get_fs_resppopup(theid);
				} else {
				get_resppopup(theid);
				}
			});	
		marker.id = color;
		marker.category = category;
		marker.region = region;		
		marker.stat = stat;
		rmarkers[theid] = marker;
		rmarkers[theid][lat] = lat;
		rmarkers[theid][lon] = lon;
		var point = new L.LatLng(lat, lon);
		bounds.extend(point);
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
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
			if($('screenname').innerHTML == "fullscreen") {
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
		fmarkers[theid][lat] = lat;
		fmarkers[theid][lon] = lon;
		var point = new L.LatLng(lat, lon);
		bounds.extend(point);
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
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
		var iconurl = "./our_icons/gen_fac_icon.php?blank=" + escape(window.fac_icons[color]) + "&text=" + iconStr;
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
		wlmarkers[theid][lat] = lat;
		wlmarkers[theid][lon] = lon;
		var point = new L.LatLng(lat, lon);
		bounds.extend(point);
		if($('screenname')) {
			var theScreen = $('screenname').innerHTML;
			if((theScreen == "situation") && ((dzf == 1) || (dzf == 3))) {
				map_is_fixed = true;
				} else if((theScreen == "responders") && ((dzf == 2) || (dzf == 3))) {
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
		cmarkers[theid][lat] = lat;
		cmarkers[theid][lon] = lon;	
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
		var marker = L.marker([lat, lon], {icon: image_file}).addTo(map)
			.bindPopup(info);
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyUnitMarker(lat, lon, info, icon, title, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		icon = new baseSqIcon({iconUrl: image_file});	
		var marker = L.marker([lat, lon], {icon: icon}).addTo(map).bindPopup(info);
		rmarkers[theid] = marker;
		rmarkers[theid][lat] = lat;
		rmarkers[theid][lon] = lon;
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyIncMarker(lat, lon, info, icon, title, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		icon = new baseSqIcon({iconUrl: image_file});	
		var marker = L.marker([lat, lon], {icon: icon}).addTo(map).bindPopup(info);
		tmarkers[theid] = marker;
		tmarkers[theid][lat] = lat;
		tmarkers[theid][lon] = lon;
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyFacMarker(lat, lon, info, icon, title, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var image_file = "./our_icons/question1.png";
		icon = new baseSqIcon({iconUrl: image_file});	
		var marker = L.marker([lat, lon], {icon: icon}).addTo(map).bindPopup(info);
		fmarkers[theid] = marker;
		fmarkers[theid][lat] = lat;
		fmarkers[theid][lon] = lon;
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
	
function get_tickpopup(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/inc_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
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
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/resp_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
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
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/facs_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
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
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/fs_inc_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
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
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/fs_resp_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
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
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/fs_facs_popup.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
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
		document.resp_form.edit.value='true';
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
	if((quick) || (!fmarkers[id]) || (internet == 0)) {
		document.fac_form.id.value=id;
		document.fac_form.func.value='responder';
		document.fac_form.edit.value='true';
		document.fac_form.action='facilities.php';
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
		document.wl_form.func.value='responder';
		document.wl_form.edit.value='true';
		document.wl_form.action='warn_locations.php';
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
	
function init_map(theType, lat, lng, icon, theZoom, locale, useOSMAP, control_position) {
	if(locale == 1 && useOSMAP == 1) {	//	UK Use Ordnance Survey as Basemap
		var openspace_api = "<?php print get_variable('openspace_api');?>";
		openspaceLayer = L.tileLayer.OSOpenSpace(openspace_api, {debug: true});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		var currentSessionLayer = "<?php print $_SESSION['layer_inuse'];?>";
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
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		} else {
		var latLng;
		var in_local_bool = <?php print get_variable('local_maps');?>;
		var osmUrl = (in_local_bool=="1")? "./_osm/tiles/{z}/{x}/{y}.png":	"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		var	cmAttr = '';
		var cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade';
		var OSM   = L.tileLayer(osmUrl, {attribution: cmAttr});
		var ggl = new L.Google('ROAD');
		var ggl1 = new L.Google('TERRAIN');
		var ggl2 = new L.Google('SATELLITE');
		var ggl3 = new L.Google('HYBRID');
		var clouds = L.OWM.clouds({showLegend: false, opacity: 0.3});
		var cloudscls = L.OWM.cloudsClassic({showLegend: false, opacity: 0.3});
		var precipitation = L.OWM.precipitation({showLegend: false, opacity: 0.3});
		var precipitationcls = L.OWM.precipitationClassic({showLegend: false, opacity: 0.3});
		var rain = L.OWM.rain({showLegend: false, opacity: 0.3});
		var raincls = L.OWM.rainClassic({showLegend: false, opacity: 0.3});
		var snow = L.OWM.snow({showLegend: false, opacity: 0.3});
		var pressure = L.OWM.pressure({showLegend: false, opacity: 0.3});
		var pressurecntr = L.OWM.pressureContour({showLegend: false, opacity: 0.8});
		var temp = L.OWM.temperature({showLegend: false, opacity: 0.3});
		var wind = L.OWM.wind({showLegend: false, opacity: 0.3});
		var dark = L.tileLayer.provider('Thunderforest.TransportDark');
		var aerial = L.tileLayer.provider('MapQuestOpen.Aerial');
		var nexrad = L.tileLayer.wms("http://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi", {
			layers: 'nexrad-n0r-900913',
			format: 'image/png',
			transparent: true,
			attribution: "",
		});
		var shade = L.tileLayer.wms("http://ims.cr.usgs.gov:80/servlet19/com.esri.wms.Esrimap/USGS_EDC_Elev_NED_3", {
			layers: "HR-NED.IMAGE", 
			format: 'image/png',
			attribution: "",
		});
		var usgstopo = L.tileLayer('http://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {
			maxZoom: 20,
			attribution: '',
		});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		var currentSessionLayer = "<?php print $_SESSION['layer_inuse'];?>";
		var baseLayerNamesArr = ["Open_Streetmaps","Google","Google_Terrain","Google_Satellite","Google_Hybrid","USGS_Topo","Dark","Aerial"];	
		var baseLayerVarArr = [OSM,ggl,ggl1,ggl2,ggl3,usgstopo,dark,aerial];
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = baseLayerVarArr[a];
		
		if(window.geo_provider == 1) {
			if(window.GoogleKey.length > 0 && window.GoogleKey.length < 39) {
				alert("Google set as Geo-coding provider but invalid Google Maps API Key");
				}
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: 20,
				zoom: theZoom,
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
				maxZoom: 20,
				zoom: theZoom,
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
			if(!map) {
				map = L.map('map_canvas',{
					maxZoom: 20,
					zoom: theZoom,
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

		var baseLayers = {
			"Open Streetmaps": OSM,
			"Google": ggl,
			"Google Terrain": ggl1,
			"Google Satellite": ggl2,
			"Google Hybrid": ggl3,
			"USGS Topo": usgstopo,
			"Dark": dark,
			"Aerial": aerial,		
		};
		
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
		map.setView([lat, lng], 13);
		bounds = map.getBounds();	
		zoom = map.getZoom();
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		}
	return map;
	}
	
function layer_handleResult(req) {
//	alert(req.responseText);
	}

function init_minimap(theType, lat, lng, icon, theZoom, locale, useOSMAP) {
	var latLng;
	var owm_apid = "e15882cd5d458e3804cf9efb5164db8c";
	var in_local_bool = "0";
	var my_Path = "http://127.0.0.1/_osm/tiles/";
	var osmUrl = (in_local_bool=="1")? "../_osm/tiles/{z}/{x}/{y}.png":	"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
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
		var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
		var url = "persist3.php";	//	3/15/11	
		sendRequest (url, fvg_handleResult, params);				
		} else {
		errmsg+= "\tYou cannot Hide all the regions\n";
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		}
	}

function fvg_handleResult(req) {	// 6/10/11	The persist callback function for viewed groups.
	show_regsmsg("Viewed Regions have changed");
	load_exclusions();
	load_ringfences();
	load_catchments();
	load_basemarkup();
	load_groupbounds();	
	load_incidentlist(inc_field, inc_direct);
	load_responderlist(resp_field, resp_direct);
	load_facilitylist(fac_field, fac_direct);
	do_conditions();
	load_regions();
	set_initial_pri_disp();
	load_poly_controls();
	update_regions_text();
	}
	
function form_validate(theForm) {	//	5/3/11
	checkForm(theForm);
	}				// end function validate(theForm)
	
function show_regsmsg(msg) {	
	$('regs_conf_span').innerHTML = msg;			
	setTimeout("$('msg_span').innerHTML =''", 3000);	// show for 3 seconds
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
	
var t1_text = "<?php print get_text('ID');?>";
var t2_text = "<?php print get_text('Scope');?>";
var t3_text = "<?php print get_text('Address');?>";
var t4_text = "<?php print get_text('Type');?>";
var t5_text = "<?php print get_text('A');?>";
var t6_text = "<?php print get_text('P');?>";
var t7_text = "<?php print get_text('U');?>";
var t8_text = "<?php print get_text('Updated');?>";
changed_inc_sort = false;
var inc_direct = 'DESC';
var inc_field = 'id';
var inc_id = "t1";
var inc_header = "<?php print get_text('ID');?>";

function set_inc_headers(id, header_text, the_bull) {
	if(id == "t1") {
		window.t1_text = header_text + the_bull;
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t7_text = "<?php print get_text('U');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t2") {
		window.t2_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t7_text = "<?php print get_text('U');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t3") {
		window.t3_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t7_text = "<?php print get_text('U');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t4") {
		window.t4_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t7_text = "<?php print get_text('U');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t5") {
		window.t5_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t7_text = "<?php print get_text('U');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t6") {
		window.t6_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t7_text = "<?php print get_text('U');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t7") {
		window.t7_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t8_text = "<?php print get_text('Updated');?>";
		} else if(id == "t8") {
		window.t8_text = header_text + the_bull;
		window.t1_text = "ID";
		window.t2_text = "<?php print get_text('Scope');?>";
		window.t3_text = "<?php print get_text('Address');?>";
		window.t4_text = "<?php print get_text('Type');?>";
		window.t5_text = "<?php print get_text('A');?>";
		window.t6_text = "<?php print get_text('P');?>";
		window.t7_text = "<?php print get_text('U');?>";
		}
	}
	
function do_inc_sort(id, field, header_text) {
	window.changed_inc_sort = true;
	window.inc_last_display == 0;
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
	load_incidentlist(field, inc_direct);
	return true;
	}

function load_incidentlist(sort, dir) {
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/sit_incidents.php?sort='+window.inc_field+'&dir='+ window.inc_direct+'&func='+inc_period+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,incidentlist_cb, "");
	function incidentlist_cb(req) {
		var inc_arr = JSON.decode(req.responseText);
		if(!inc_arr && doDebug) { alert(req.responseText); }
		if(window.inc_period_changed == 1) {
			for(var key in tmarkers) {
				if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
				}
			if($('map_canvas')) {	
				for(var key in tmarkers) {
					if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
					}
				}
			$('the_list').innerHTML = "";
			window.inc_period_changed = 0;
			}
		if((inc_arr[0]) && (inc_arr[0][0] == 0)) {
			window.inc_last_display = 0;
			if($('map_canvas')) {	
				for(var key in tmarkers) {
					if(tmarkers[key]) {map.removeLayer(tmarkers[key]);}
					}
				}
			outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Incidents, please select another time period or add a new incident.........</marquee>";
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
		var ticket_number = 0;
		var category = "Incident";
		var outputtext = "<TABLE id='incidenttable' class='cruises scrollable' style='width: " + window.listwidth + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR style='width: " + window.listwidth + "px;'>";
		outputtext += "<TH id='t1' class='plain_listheader'>" + window.t1_text + "</TH>";
		outputtext += "<TH id='t2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Incident name or scope');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'scope', '<?php print get_text('Scope');?>')\">" + window.t2_text + "</TH>";
		outputtext += "<TH id='t3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Incident Location');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'street', '<?php print get_text('Address');?>')\">" + window.t3_text + "</TH>";
		outputtext += "<TH id='t4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Type of Incident');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'type', '<?php print get_text('Type');?>')\">" + window.t4_text + "</TH>";
		outputtext += "<TH id='t5' class='plain_listheader' style='width: 12px;' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Number of Patients');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'a', '<?php print get_text('A');?>')\">" + window.t5_text + "</TH>";
		outputtext += "<TH id='t6' class='plain_listheader' style='width: 12px;' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Number of Actions');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'p', '<?php print get_text('P');?>')\">" + window.t6_text + "</TH>";
		outputtext += "<TH id='t7' class='plain_listheader' style='width: 12px;' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Number of Units assigned to this Incident');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'u', '<?php print get_text('U');?>')\">" + window.t7_text + "</TH>";
		outputtext += "<TH id='t8' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Incident data last updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_inc_sort(this.id, 'updated', '<?php print get_text('Updated');?>')\">" + window.t8_text + "</TH>";
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
							var deflat = "<?php print get_variable('def_lat');?>";
							var deflng = "<?php print get_variable('def_lng');?>";		
							var marker = createdummyIncMarker(deflat, deflng, infowindowtext, "", inc_arr[key][0], i);
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
					} else {
					var datestring = "<SPAN style='background-color: blue; color: #FFFFFF;'>" + inc_arr[key][21] + "</SPAN>";
					}
				outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: " + window.listwidth + "px; " + strike + "' onMouseover=\"Tip('" + inc_arr[key][0] + " - " + inc_arr[key][1] + "')\" onMouseout='UnTip();' onClick='mytclick(" + inc_id + ");'>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + ";'>" + pad(4, i, "\u00a0") + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + ";'>" + htmlentities(inc_arr[key][0], 'ENT_QUOTES') + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][1] + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][4] + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + "; width: 12px;'>" + pad(6, inc_arr[key][17], "\u00a0") + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + "; width: 12px;'>" + pad(6, inc_arr[key][16], "\u00a0") + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + "; width: 12px;'>" + blinkstart + pad(6, inc_arr[key][18], "\u00a0") + blinkend + "</TD>";
				outputtext += "<TD class='plain_list' style='color: " + inc_arr[key][14] + ";'>" + datestring + "</TD>";
				outputtext += "<TD>" + pad(3, " ", "\u00a0") + "</TD>";
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
				if((window.do_update == true) || (window.changed_inc_sort == true) || (window.do_inc_refresh == true)) {
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
				if(tableRow) {
					headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
					headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
					headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
					headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
					headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";
					headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";
					headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";
					headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";
					headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 4 + "px";
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
				if(getHeaderHeight(headerRow) >= 20) {
					var theRow = inctbl.insertRow(1);
					theRow.style.height = "20px";
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
			do_blink();
			window.incFin = true;
			pageLoaded();
			window.do_inc_refresh = false;
//			incidentlist_get();
			},500);
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
	if(i != inctbl.rows.length) {
		var tableRow = inctbl.rows[viewableRow];
		tableRow.cells[0].style.width = window.tcell1 + "px";
		tableRow.cells[1].style.width = window.tcell2 + "px";
		tableRow.cells[2].style.width = window.tcell3 + "px";
		tableRow.cells[3].style.width = window.tcell4 + "px";
		tableRow.cells[4].style.width = window.tcell5 + "px";
		tableRow.cells[5].style.width = window.tcell6 + "px";
		tableRow.cells[6].style.width = window.tcell7 + "px";
		tableRow.cells[7].style.width = window.tcell8 + "px";
		tableRow.cells[8].style.width = window.tcell9 + "px";
		headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
		headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
		headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
		headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
		headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";
		headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";
		headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";
		headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";
		headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 4 + "px";
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
	if(getHeaderHeight(headerRow) >= 20) {
		var theRow = inctbl.insertRow(1);
		theRow.style.height = "20px";
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
	
function incidentlist_get() {								// set cycle
	if (i_interval!=null) {return;}
	i_interval = window.setInterval('incidentlist_loop()', 60000);
	}			// end function mu get()

function incidentlist_loop() {
	load_incidentlist(inc_field, inc_direct);
	}			// end function do_loop()

function isInteger(s) {
	return (s.toString().search(/^-?[0-9]+$/) == 0);
	}

function do_destroy() {
	for(var key in rmarkers) {
		if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
		}
	}
	
var r1_text = "<?php print get_text('Icon');?>"; 
var r2_text = "<?php print get_text('Handle');?>"; 
var r3_text = "<?php print get_text('Mail');?>"; 
var r4_text = "<?php print get_text('Incidents');?>"; 
var r5_text = "<?php print get_text('Status');?>"; 
var r6_text = "<?php print get_text('M');?>"; 
var r7_text = "<?php print get_text('As of');?>"; 
var changed_resp_sort = false;
var resp_direct = "ASC";
var resp_field = "icon";
var resp_id = "r1";
var resp_header = "<?php print get_text('Icon');?>";

function set_resp_headers(id, header_text, the_bull) {
	if(id == "r1") {
		window.r1_text = header_text + the_bull;
		window.r2_text = "<?php print get_text('Handle');?>";
		window.r3_text = "<?php print get_text('Mail');?>";
		window.r4_text = "<?php print get_text('Incidents');?>";
		window.r5_text = "<?php print get_text('Status');?>";
		window.r6_text = "<?php print get_text('M');?>";
		window.r7_text = "<?php print get_text('As of');?>";
		} else if(id == "r2") {
		window.r2_text = header_text + the_bull;
		window.r1_text = "<?php print get_text('Icon');?>";
		window.r3_text = "<?php print get_text('Mail');?>";
		window.r4_text = "<?php print get_text('Incidents');?>";
		window.r5_text = "<?php print get_text('Status');?>";
		window.r6_text = "<?php print get_text('M');?>";
		window.r7_text = "<?php print get_text('As of');?>";
		} else if(id == "r3") {
		window.r3_text = header_text + the_bull;
		window.r1_text = "<?php print get_text('Icon');?>";
		window.r2_text = "<?php print get_text('Handle');?>";
		window.r4_text = "<?php print get_text('Incidents');?>";
		window.r5_text = "<?php print get_text('Status');?>";
		window.r6_text = "<?php print get_text('M');?>";
		window.r7_text = "<?php print get_text('As of');?>";
		} else if(id == "r4") {
		window.r4_text = header_text + the_bull;
		window.r1_text = "<?php print get_text('Icon');?>";
		window.r2_text = "<?php print get_text('Handle');?>";
		window.r3_text = "<?php print get_text('Mail');?>";
		window.r5_text = "<?php print get_text('Status');?>";
		window.r6_text = "<?php print get_text('M');?>";
		window.r7_text = "<?php print get_text('As of');?>";
		} else if(id == "r5") {
		window.r5_text = header_text + the_bull;
		window.r1_text = "<?php print get_text('Icon');?>";
		window.r2_text = "<?php print get_text('Handle');?>";
		window.r3_text = "<?php print get_text('Mail');?>";
		window.r4_text = "<?php print get_text('Incidents');?>";
		window.r6_text = "<?php print get_text('M');?>";
		window.r7_text = "<?php print get_text('As of');?>";
		} else if(id == "r6") {
		window.r6_text = header_text + the_bull;
		window.r1_text = "<?php print get_text('Icon');?>";
		window.r2_text = "<?php print get_text('Handle');?>";
		window.r3_text = "<?php print get_text('Mail');?>";
		window.r4_text = "<?php print get_text('Incidents');?>";
		window.r5_text = "<?php print get_text('Status');?>";
		window.r7_text = "<?php print get_text('As of');?>";
		} else if(id == "r7") {
		window.r7_text = header_text + the_bull;
		window.r1_text = "<?php print get_text('Icon');?>";
		window.r2_text = "<?php print get_text('Handle');?>";
		window.r3_text = "<?php print get_text('Mail');?>";
		window.r4_text = "<?php print get_text('Incidents');?>";
		window.r5_text = "<?php print get_text('Status');?>";
		window.r6_text = "<?php print get_text('M');?>";
		}
	}
	
function do_resp_sort(id, field, header_text) {
	window.changed_resp_sort = true;
	window.resp_last_display = 0;
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
	load_responderlist(field, resp_direct);
	return true;
	}

function load_responderlist(sort, dir) {
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/sit_responders.php?sort='+window.resp_field+'&dir='+ window.resp_direct+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,responderlist_cb, "");		
	function responderlist_cb(req) {
		var i = 1;
		var responder_number = 0;	
		var resp_arr = JSON.decode(req.responseText);
		if(!resp_arr && doDebug) { alert(req.responseText); }
		if((resp_arr[0]) && (resp_arr[0][0] == 0)) {
			for(var key in rmarkers) {
				if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Units to view.........</marquee>";
			$('the_rlist').innerHTML = outputtext;
			$('boxes').innerHTML = resp_arr[0][19];
			window.latest_responder = 0;
			} else {
			var outputtext = "<TABLE id='respondertable' class='cruises scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='r1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Map Icon');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'icon', '<?php print get_text('Icon');?>')\">" + window.r1_text + "</TH>";
			outputtext += "<TH id='r2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder Handle');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'handle', '<?php print get_text('Handle');?>')\">" + window.r2_text + "</TH>";
			outputtext += "<TH id='r3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Email this responder');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'mail', '<?php print get_text('Mail');?>')\">" + window.r3_text + "</TH>";
			outputtext += "<TH id='r4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Incident(s) this responder assigned to or number of incidents');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'incidents', '<?php print get_text('Incidents');?>')\">" + window.r4_text + "</TH>";
			outputtext += "<TH id='r5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder Status');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'status', '<?php print get_text('Status');?>')\">" + window.r5_text + "</TH>";
			outputtext += "<TH id='r6' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder Tracking Type - GL-Google Latitude, MT-Tickets RM Tracker, TT-Tickets Internal Tracker');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'm', '<?php print get_text('M');?>')\">" + window.r6_text + "</TH>";
			outputtext += "<TH id='r7' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder data last updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort(this.id, 'asof', '<?php print get_text('As of');?>')\">" + window.r7_text + "</TH>";
			outputtext += "<TH id='r8'>" + pad(5, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in resp_arr) {
				if(key != 0) {
					if(resp_arr[key][2]) {
						var unit_id = resp_arr[key][2];
						var unit_no = resp_arr[key][17];
						if(resp_arr[key][11] != "") {
							var theMailBut = pad(6, "<DIV style='text-align: center;'><IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit " + resp_arr[key][1] + "' onclick = 'do_mail_win(\"" + unit_no + "\");'></DIV>", "\u00a0");
							} else {
							var theMailBut = pad(6, "", "\u00a0");
							}
						if(resp_arr[key][26] != "") {
							 var theTip = " onMouseover=\"Tip('" + htmlentities(resp_arr[key][26], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
							 } else {
							 var theTip = "";
							 }
						var bg_color = resp_arr[key][7];
						var fg_color = resp_arr[key][8];
						outputtext += "<TR id='" + resp_arr[key][20] + i +"' CLASS='" + colors[i%2] +"' style='width: " + window.listwidth + "px;'>";
						outputtext += "<TD style=\"background-color: " + bg_color + "; color: " + fg_color + ";\" onClick='myrclick(" + unit_no + ");'>" + unit_id + "</TD>";
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'>" + pad(10, htmlentities(resp_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</TD>";
						outputtext += "<TD>" + theMailBut + "</TD>";
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'>" + pad(20, resp_arr[key][12], "\u00a0") + "</TD>";
						if(!window.status_control[resp_arr[key][17]]) {
							outputtext += "<TD " + theTip + ">" + resp_arr[key][23] + "</TD>";
							} else {
							outputtext += "<TD " + theTip + ">" + window.status_control[resp_arr[key][17]] + "</TD>";
							}
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'>" +  pad(3, resp_arr[key][13], "\u00a0") + "</TD>";
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'><SPAN id = '" + resp_arr[key][27] + "'>" + resp_arr[key][16] + "</SPAN></TD>";
						outputtext += "<TD>" + pad(5, " ", "\u00a0") + "</TD>";
						outputtext += "</TR>";
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
						infowindowtext = "";					
						if($('map_canvas')) {						
							if(rmarkers[unit_no]) {
								if(window.changed_resp_sort == false) {
									var curPos = rmarkers[unit_no].getLatLng();
									if((curPos.lat != resp_arr[key][3]) || (curPos.lng != resp_arr[key][4])) {
										theLatLng = new L.LatLng(resp_arr[key][3], resp_arr[key][3]);
										rmarkers[unit_no].setLatLng(theLatLng);
										}
									} else {
	/* 								do_destroy();
									if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
										var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_no, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
										marker.addTo(map);
										} else {
										var deflat = "<?php print get_variable('def_lat');?>";
										var deflng = "<?php print get_variable('def_lng');?>";		
										var marker = createdummyUnitMarker(deflat, deflng, infowindowtext, "", resp_arr[key][0], unit_no);
										marker.addTo(map);
										} */
									}
								} else {
								if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
									var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_no, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
									marker.addTo(map);
									} else {
									var deflat = "<?php print get_variable('def_lat');?>";
									var deflng = "<?php print get_variable('def_lng');?>";		
									var marker = createdummyUnitMarker(deflat, deflng, infowindowtext, "", resp_arr[key][0], unit_no);
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
				if(window.resp_last_display == 0) {
					$('the_rlist').innerHTML = outputtext;
					$('boxes').innerHTML = resp_arr[0][21];
					window.latest_responder = responder_number;
					set_categories();
					} else {
					if((responder_number != window.latest_responder) || (window.do_resp_update == true) || (window.changed_resp_sort == true) || (window.do_resp_refresh == true)) {
						$('the_rlist').innerHTML = "";
						$('the_rlist').innerHTML = outputtext;
						$('boxes').innerHTML = resp_arr[0][21];
						window.latest_responder = responder_number;
						set_categories();
						}
					}
				for(var key in resp_arr) {
					if(parseFloat(resp_arr[key][3]) && parseFloat(resp_arr[key][3])) {
						if(parseInt(resp_arr[key][28]) != 0) {check_excl(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						if(parseInt(resp_arr[key][29]) != 0) {check_ringfence(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						}
					}
				var resptbl = document.getElementById('respondertable');
				if(resptbl) {
					var headerRow = resptbl.rows[0];
					var tableRow = resptbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
						if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
						if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
						if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
						if(tableRow.cells[7] && headerRow.cells[7]) {headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";}
						} else {
						var cellwidthBase = window.listwidth / 28;
						cell1 = cellwidthBase * 2;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 3;
						cell4 = cellwidthBase * 6;
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
					if(getHeaderHeight(headerRow) >= 20) {
						var theRow = resptbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						var no5 = theRow.insertCell(4);
						var no6 = theRow.insertCell(5);
						var no7 = theRow.insertCell(6);
						var no8 = theRow.insertCell(7);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						no5.innerHTML = " ";
						no6.innerHTML = " ";
						no7.innerHTML = " ";
						no8.innerHTML = " ";
						}
					}
				window.resp_last_display = resp_arr[0][23];
				window.respFin = true;
				pageLoaded();
				window.do_resp_refresh = false;
				responderlist_get();
				},500);
			}
		}				// end function responderlist_cb()
	}				// end function load_responderlist()

function isViewable(element){
	return (element.clientHeight > 0);
	}
	
function responderlist_setwidths() {
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
	if(i != resptbl.rows.length) {
		var tableRow = resptbl.rows[viewableRow];
		tableRow.cells[0].style.width = window.cell1 + "px";
		tableRow.cells[1].style.width = window.cell2 + "px";
		tableRow.cells[2].style.width = window.cell3 + "px";
		tableRow.cells[3].style.width = window.cell4 + "px";
		tableRow.cells[4].style.width = window.cell5 + "px";
		tableRow.cells[5].style.width = window.cell6 + "px";
		tableRow.cells[6].style.width = window.cell7 + "px";
		tableRow.cells[7].style.width = window.cell8 + "px";
		headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
		headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
		headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
		headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
		headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";
		headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";
		headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";
		headerRow.cells[7].style.width = tableRow.cells[6].clientWidth - 4 + "px";
		} else {
		var cellwidthBase = window.listwidth / 28;
		cell1 = cellwidthBase * 2;
		cell2 = cellwidthBase * 4;
		cell3 = cellwidthBase * 3;
		cell4 = cellwidthBase * 6;
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
	if(getHeaderHeight(headerRow) >= 20) {
		var theRow = resptbl.insertRow(1);
		theRow.style.height = "20px";
		var no1 = theRow.insertCell(0);
		var no2 = theRow.insertCell(1);
		var no3 = theRow.insertCell(2);
		var no4 = theRow.insertCell(3);
		var no5 = theRow.insertCell(4);
		var no6 = theRow.insertCell(5);
		var no7 = theRow.insertCell(6);
		var no8 = theRow.insertCell(7);
		no1.innerHTML = " ";
		no2.innerHTML = " ";
		no3.innerHTML = " ";
		no4.innerHTML = " ";
		no5.innerHTML = " ";
		no6.innerHTML = " ";
		no7.innerHTML = " ";
		no8.innerHTML = " ";
		}
	}
	
function responderlist_get() {
	if (r_interval!=null) {return;}
	r_interval = window.setInterval('responderlist_loop()', 60000); 
	}			// end function mu get()

function responderlist_loop() {
	load_responderlist(resp_field, resp_direct);
	}			// end function do_loop()
	
//	Responderlist for Units screen

var rr1_text = "<?php print get_text('Icon');?>";
var rr2_text = "<?php print get_text('Name');?>";
var rr3_text = "<?php print get_text('Mail');?>";
var rr4_text = "<?php print get_text('Incidents');?>";
var rr5_text = "<?php print get_text('Status');?>";
var rr6_text = "<?php print get_text('Status About');?>";
var rr7_text = "<?php print get_text('M');?>";
var rr8_text = "<?php print get_text('As of');?>";

function set_resp_headers2(id, header_text, the_bull) {
	if(id == "r1") {
		window.rr1_text = header_text + the_bull;
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r2") {
		window.rr2_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r3") {
		window.rr3_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r4") {
		window.rr4_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r5") {
		window.rr5_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r6") {
		window.rr6_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r7") {
		window.rr7_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr8_text = "<?php print get_text('As of');?>";
		} else if(id == "r8") {
		window.rr8_text = header_text + the_bull;
		window.rr1_text = "<?php print get_text('Icon');?>";
		window.rr2_text = "<?php print get_text('Name');?>";
		window.rr3_text = "<?php print get_text('Mail');?>";
		window.rr4_text = "<?php print get_text('Incidents');?>";
		window.rr5_text = "<?php print get_text('Status');?>";
		window.rr6_text = "<?php print get_text('Status About');?>";
		window.rr7_text = "<?php print get_text('M');?>";
		}
	}
	
function do_resp_sort2(id, field, header_text) {
	window.changed_resp_sort = true;
	window.resp_last_display = 0;
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
	load_responderlist2(field, resp_direct);
	return true;
	}

function load_responderlist2(sort, dir) {
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/sit_responders.php?sort='+window.resp_field+'&dir='+ window.resp_direct+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,responderlist2_cb, "");		
	function responderlist2_cb(req) {
		var i = 1;
		var responder_number = 0;	
		var resp_arr = JSON.decode(req.responseText);
		if((resp_arr[0]) && (resp_arr[0][0] == 0)) {
			for(var key in rmarkers) {
				if(rmarkers[key]) {map.removeLayer(rmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Units to view.........</marquee>";
			$('the_rlist').innerHTML = outputtext;
			$('boxes').innerHTML = resp_arr[0][19];
			window.latest_responder = 0;
			} else {
			var outputtext = "<TABLE id='respondertable' class='cruises scrollable' style='width: " + window.leftlistwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.leftlistwidth + "px;'>";
			outputtext += "<TH id='r1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Map Icon');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'icon', '<?php print get_text('Icon');?>')\">" + window.rr1_text + "</TH>";
			outputtext += "<TH id='r2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder Name');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'name', '<?php print get_text('Name');?>')\">" + window.rr2_text + "</TH>";
			outputtext += "<TH id='r3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Email this responder');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'mail', '<?php print get_text('Mail');?>')\">" + window.rr3_text + "</TH>";
			outputtext += "<TH id='r4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Incident(s) this responder assigned to or number of incidents');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'incidents', '<?php print get_text('Incidents');?>')\">" + window.rr4_text + "</TH>";
			outputtext += "<TH id='r5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder status');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'status', '<?php print get_text('Status');?>')\">" + window.rr5_text + "</TH>";
			outputtext += "<TH id='r6' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder status about');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'sa', '<?php print get_text('Status About');?>')\">" + window.rr6_text + "</TH>";
			outputtext += "<TH id='r7' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder Tracking Type - GL-Google Latitude, MT-Tickets RM Tracker, TT-Tickets Internal Tracker');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'm', '<?php print get_text('M');?>')\">" + window.rr7_text + "</TH>";
			outputtext += "<TH id='r8' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Responder data last updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_resp_sort2(this.id, 'asof', '<?php print get_text('As of');?>')\">" + window.rr8_text + "</TH>";
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
						outputtext += "<TR id='" + resp_arr[key][20] + i +"' CLASS='" + colors[i%2] +"' style='width: " + window.leftlistwidth + "px;'>";
						outputtext += "<TD style='background-color: " + bg_color + "; color: " + fg_color + ";' onClick='myrclick(" + unit_no + ");'>" + pad(6, unit_id, "\u00a0") + "</TD>";
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'>" + htmlentities(resp_arr[key][0], 'ENT_QUOTES') + "</TD>";
						outputtext += "<TD style='text-align: center;'>" + theMailBut + "</TD>";
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'>" + pad(22, resp_arr[key][12], "\u00a0") + "</TD>";
						if(!window.status_control[resp_arr[key][17]]) {
							outputtext += "<TD " + theTip + ">" + resp_arr[key][23] + "</TD>";
							} else {
							outputtext += "<TD " + theTip + ">" + window.status_control[resp_arr[key][17]] + "</TD>";
							}
						var status_about = pad(22, html_entity_decode(resp_arr[key][26], 'ENT_QUOTES'), "\u00a0")
						outputtext += "<TD " + theTip + " onClick='myrclick(" + unit_no + ");'>" + status_about.trunc(20) + "</TD>";
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'>" +  pad(6, resp_arr[key][13], "\u00a0") + "</TD>";
						var theFlag = resp_arr[key][27];
						outputtext += "<TD onClick='myrclick(" + unit_no + ");'><SPAN id = '" + theFlag + "' style='white-space: nowrap;'>" + pad(2, resp_arr[key][16], "\u00a0") + "</SPAN></TD>";
						outputtext += "<TD>" + pad(12, " ", "\u00a0") + "</TD>";
						outputtext += "</TR>";
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
						infowindowtext = "";
						if($('map_canvas')) {
							if(rmarkers[unit_no]) {
								if(window.changed_resp_sort == false) {
									var curPos = rmarkers[unit_no].getLatLng();
									if((curPos.lat != resp_arr[key][3]) || (curPos.lng != resp_arr[key][4])) {
										theLatLng = new L.LatLng(resp_arr[key][3], resp_arr[key][3]);
										rmarkers[unit_no].setLatLng(theLatLng);
										}
									} else {
	/* 								do_destroy();
									if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
										var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_no, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
										marker.addTo(map);
										} else {
										var deflat = "<?php print get_variable('def_lat');?>";
										var deflng = "<?php print get_variable('def_lng');?>";		
										var marker = createdummyUnitMarker(deflat, deflng, infowindowtext, "", resp_arr[key][0], unit_no);
										marker.addTo(map);
										} */
									}
								} else {
								if($('map_canvas')) {
									if((isFloat(resp_arr[key][3])) && (isFloat(resp_arr[key][4]))) {
										var marker = createUnitMarker(resp_arr[key][3], resp_arr[key][4], infowindowtext, resp_arr[key][18], 0, unit_no, resp_arr[key][2], resp_arr[key][20], 0, resp_arr[key][9], resp_arr[key][25]); // 7/28/10, 3/15/11, 12/23/13
										marker.addTo(map);
										} else {
										var deflat = "<?php print get_variable('def_lat');?>";
										var deflng = "<?php print get_variable('def_lng');?>";		
										var marker = createdummyUnitMarker(deflat, deflng, infowindowtext, "", resp_arr[key][0], unit_no);
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
				if(window.resp_last_display == 0) {
					$('the_rlist').innerHTML = outputtext;
					$('boxes').innerHTML = resp_arr[0][21];
					window.latest_responder = responder_number;
					set_categories();
					} else {
					if((responder_number != window.latest_responder) || (window.do_resp_update == true) || (window.changed_resp_sort == true)) {
						$('the_rlist').innerHTML = "";
						$('the_rlist').innerHTML = outputtext;
						$('boxes').innerHTML = resp_arr[0][21];
						window.latest_responder = responder_number;
						set_categories();
						}
					}
				for(var key in resp_arr) {
					if(parseFloat(resp_arr[key][3]) && parseFloat(resp_arr[key][3])) {
						if(parseInt(resp_arr[key][28]) != 0) {check_excl(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						if(parseInt(resp_arr[key][29]) != 0) {check_ringfence(resp_arr[key][17], resp_arr[key][3], resp_arr[key][4], resp_arr[key][27]);}
						}
					}
				var resptbl = document.getElementById('respondertable');
				if(resptbl) {
					var headerRow = resptbl.rows[0];
					var tableRow = resptbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
						if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
						if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
						if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
						if(tableRow.cells[7] && headerRow.cells[7]) {headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";}
						if(tableRow.cells[8] && headerRow.cells[8]) {headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 4 + "px";}
						} else {
						var cellwidthBase = window.leftlistwidth / 36;
						cell1 = cellwidthBase * 2;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 2;
						cell4 = cellwidthBase * 4;
						cell5 = cellwidthBase * 5;
						cell6 = cellwidthBase * 4;
						cell7 = cellwidthBase * 4;
						cell8 = cellwidthBase * 1;
						cell9 = cellwidthBase * 7;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";						
						headerRow.cells[4].style.width = cell5 + "px";							
						headerRow.cells[5].style.width = cell6 + "px";						
						headerRow.cells[6].style.width = cell7 + "px";
						headerRow.cells[7].style.width = cell8 + "px";		
						headerRow.cells[8].style.width = cell9 + "px";		
						}
					if(getHeaderHeight(headerRow) >= 20) {
						var theRow = resptbl.insertRow(1);
						theRow.style.height = "20px";
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
				window.resp_last_display = resp_arr[0][23];
				window.respFin = true;
				pageLoaded();
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
	if(i != resptbl.rows.length) {
		var tableRow = resptbl.rows[viewableRow];
		tableRow.cells[0].style.width = window.cell1 + "px";
		tableRow.cells[1].style.width = window.cell2 + "px";
		tableRow.cells[2].style.width = window.cell3 + "px";
		tableRow.cells[3].style.width = window.cell4 + "px";
		tableRow.cells[4].style.width = window.cell5 + "px";
		tableRow.cells[5].style.width = window.cell6 + "px";
		tableRow.cells[6].style.width = window.cell7 + "px";
		tableRow.cells[7].style.width = window.cell8 + "px";
		tableRow.cells[8].style.width = window.cell9 + "px";
		headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
		headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
		headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
		headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
		headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";
		headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";
		headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";
		headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";
		headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 2 + "px";
		} else {
		var cellwidthBase = window.leftlistwidth / 36;
		cell1 = cellwidthBase * 2;
		cell2 = cellwidthBase * 4;
		cell3 = cellwidthBase * 2;
		cell4 = cellwidthBase * 4;
		cell5 = cellwidthBase * 5;
		cell6 = cellwidthBase * 4;
		cell7 = cellwidthBase * 4;
		cell8 = cellwidthBase * 1;
		cell9 = cellwidthBase * 7;
		headerRow.cells[0].style.width = cell1 + "px";
		headerRow.cells[1].style.width = cell2 + "px";
		headerRow.cells[2].style.width = cell3 + "px";
		headerRow.cells[3].style.width = cell4 + "px";
		headerRow.cells[4].style.width = cell5 + "px";
		headerRow.cells[5].style.width = cell6 + "px";
		headerRow.cells[6].style.width = cell7 + "px";
		headerRow.cells[7].style.width = cell8 + "px";
		headerRow.cells[8].style.width = cell9 + "px";
		}
	if(getHeaderHeight(headerRow) >= 20) {
		var theRow = resptbl.insertRow(1);
		theRow.style.height = "25px";
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
	
function responderlist2_get() {
	if (r_interval!=null) {return;}
	r_interval = window.setInterval('responderlist2_loop()', 60000); 
	}			// end function mu get()

function responderlist2_loop() {
	load_responderlist2(resp_field, resp_direct);
	}			// end function do_loop()

// end

var f1_text = "<?php print get_text('Icon');?>";
var f2_text = "<?php print get_text('Name');?>";
var f3_text = "<?php print get_text('Mail');?>";
var f4_text = "<?php print get_text('Status');?>";
var f5_text = "<?php print get_text('Updated');?>";
var changed_fac_sort = false;
var fac_direct = "ASC";
var fac_field = "id";
var fac_id = "f1";
var fac_header = "<?php print get_text('Icon');?>";

function set_fac_headers(id, header_text, the_bull) {
	if(id == "f1") {
		window.f1_text = header_text + the_bull;
		window.f2_text = "<?php print get_text('Name');?>";
		window.f3_text = "<?php print get_text('Mail');?>";		
		window.f4_text = "<?php print get_text('Status');?>";
		window.f5_text = "<?php print get_text('Updated');?>";
		} else if(id == "f2") {
		window.f2_text = header_text + the_bull;
		window.f1_text = "<?php print get_text('Icon');?>";
		window.f3_text = "<?php print get_text('Mail');?>";	
		window.f4_text = "<?php print get_text('Status');?>";
		window.f5_text = "<?php print get_text('Updated');?>";
		} else if(id == "f3") {
		window.f3_text = header_text + the_bull;
		window.f1_text = "<?php print get_text('Icon');?>";
		window.f2_text = "<?php print get_text('Name');?>";
		window.f4_text = "<?php print get_text('Status');?>";
		window.f5_text = "<?php print get_text('Updated');?>";
		} else if(id == "f4") {
		window.f4_text = header_text + the_bull;
		window.f1_text = "<?php print get_text('Icon');?>";
		window.f2_text = "<?php print get_text('Name');?>";
		window.f3_text = "<?php print get_text('Mail');?>";
		window.f5_text = "<?php print get_text('Updated');?>";
		} else if(id == "f5") {
		window.f5_text = header_text + the_bull;
		window.f1_text = "<?php print get_text('Icon');?>";
		window.f2_text = "<?php print get_text('Name');?>";
		window.f3_text = "<?php print get_text('Mail');?>";
		window.f4_text = "<?php print get_text('Status');?>";
		}
	}
	
function do_fac_sort(id, field, header_text) {
	window.changed_fac_sort = true;
	window.fac_last_display = 0;
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
	load_facilitylist(field, fac_direct);
	return true;
	}
	
function load_facilitylist(sort, dir) {
	window.facFin = false;
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/sit_facilities.php?sort='+window.fac_field+'&dir='+ window.fac_direct+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,facilitylist_cb, "");
	function facilitylist_cb(req) {
		var i = 1;
		var facility_number = 0;
		var fac_arr = JSON.decode(req.responseText);
		if(!fac_arr && doDebug) { alert(req.responseText); }
		if((fac_arr[0]) && (fac_arr[0][0] == 0)) {
			for(var key in fmarkers) {
				if(fmarkers[key]) {map.removeLayer(fmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Facilities to view.........</marquee>";
			$('the_flist').innerHTML = outputtext;
			$('fac_boxes').innerHTML = fac_arr[0][12];
			window.latest_facility = 0;
			} else {
			var outputtext = "<TABLE id='facilitiestable' class='cruises scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='f1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Map Icon');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'id', '<?php print get_text('Icon');?>')\">" + window.f1_text + "</TH>";
			outputtext += "<TH id='f2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Facility Name');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'name', '<?php print get_text('Name');?>')\">" + window.f2_text + "</TH>";
			outputtext += "<TH id='f3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Email this Facility');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'mail', '<?php print get_text('Mail');?>')\">" + window.f3_text + "</TH>";
			outputtext += "<TH id='f4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Facility Status / Availability');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'status', '<?php print get_text('Status');?>')\">" + window.f4_text + "</TH>";
			outputtext += "<TH id='f5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Facility data last updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'updated', '<?php print get_text('Updated');?>')\">" + window.f5_text + "</TH>";
			outputtext += "<TH id='f6'>" + pad(3, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in fac_arr) {
				if((key > 0) && (fac_arr[key][2]) && (fac_arr[key][2] != "")) {
					var fac_id = fac_arr[key][10];
					var bg_color = fac_arr[key][5];
					var fg_color = fac_arr[key][6];
					if(fac_arr[key][7] != "") {
						var theMailBut = pad(8, "<DIV style='text-align: center;'><IMG SRC='mail.png' BORDER=0 TITLE = 'click to email facility " + fac_arr[key][0] + "' onclick = 'do_mail_win(\"" + fac_id + "\");'></DIV>", "\u00a0");
						} else {
						var theMailBut = pad(8, "", "\u00a0");
						}
					if(fac_arr[key][16] != "") {
						 var theTip = " onMouseover=\"Tip('" + htmlentities(fac_arr[key][16], 'ENT_QUOTES') + "');\" onMouseout='UnTip();'";
						 } else {
						 var theTip = "";
						 }
					outputtext += "<TR id='" + fac_arr[key][15] + i +"' CLASS='" + colors[i%2] + "' style='width: " + window.listwidth + "px;'>";
					outputtext += "<TD style=\"background-color: " + bg_color + "; color: " + fg_color + ";\" onClick='myfclick(" + fac_id + ");'>" + fac_arr[key][2] + "</TD>";
					outputtext += "<TD style=\"text-align: left;\" onClick='myfclick(" + fac_id + ");'>" + htmlentities(fac_arr[key][0], 'ENT_QUOTES') + "</TD>";
					outputtext += "<TD>" + theMailBut + "</TD>";
					if(!window.fac_status_control[fac_arr[key][10]]) {
						outputtext += "<TD " + theTip + ">" + fac_arr[key][17] + "</TD>";
						} else {
						outputtext += "<TD " + theTip + ">" + window.fac_status_control[fac_arr[key][10]] + "</TD>";
						}
					outputtext += "<TD onClick='myfclick(" + fac_id + ");'>" + fac_arr[key][9] + "</TD>";
					outputtext += "<TD>" + pad(3, " ", "\u00a0") + "</TD>";
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
								var deflat = "<?php print get_variable('def_lat');?>";
								var deflng = "<?php print get_variable('def_lng');?>";		
								var marker = createdummyFacMarker(deflat, deflng, infowindowtext, "", fac_arr[key][0], fac_id);
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
					$('fac_boxes').innerHTML = fac_arr[0][12];
					set_fac_categories();
					window.latest_facility = facility_number;
					} else {
					if((facility_number != window.latest_facility) || (window.do_fac_update == true) || (window.changed_fac_sort == true)) {
						$('the_flist').innerHTML = outputtext;
						$('fac_boxes').innerHTML = fac_arr[0][12];
						window.latest_facility = facility_number;
						set_fac_categories();
						}
					}
				var factbl = document.getElementById('facilitiestable');
				if(factbl) {
					var headerRow = factbl.rows[0];
					var tableRow = factbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
						if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
						if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
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
					if(getHeaderHeight(headerRow) >= 20) {
						var theRow = factbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						var no5 = theRow.insertCell(4);
						var no6 = theRow.insertCell(5);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						no5.innerHTML = " ";
						no6.innerHTML = " ";
						}
					}
				window.facFin = true;
				pageLoaded();
//				facilitylist_get();
				},500);
			}
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
	if(i != factbl.rows.length) {
		var tableRow = factbl.rows[viewableRow];
		tableRow.cells[0].style.width = window.fcell1 + "px";
		tableRow.cells[1].style.width = window.fcell2 + "px";
		tableRow.cells[2].style.width = window.fcell3 + "px";
		tableRow.cells[3].style.width = window.fcell4 + "px";
		tableRow.cells[4].style.width = window.fcell5 + "px";
		tableRow.cells[5].style.width = window.fcell6 + "px";
		headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
		headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
		headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
		headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
		headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";
		headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";
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
	if(getHeaderHeight(headerRow) >= 20) {
		var theRow = factbl.insertRow(1);
		theRow.style.height = "20px";
		var no1 = theRow.insertCell(0);
		var no2 = theRow.insertCell(1);
		var no3 = theRow.insertCell(2);
		var no4 = theRow.insertCell(3);
		var no5 = theRow.insertCell(4);
		var no6 = theRow.insertCell(5);
		no1.innerHTML = " ";
		no2.innerHTML = " ";
		no3.innerHTML = " ";
		no4.innerHTML = " ";
		no5.innerHTML = " ";
		no6.innerHTML = " ";
		}
	}
	
function facilitylist_get() {
	if (f_interval!=null) {return;}
	f_interval = window.setInterval('facilitylist_loop()', 600000);
	}			// end function mu get()

function facilitylist_loop() {
	load_facilitylist(fac_field, fac_direct);
	}			// end function do_loop()
	
var w1_text = "<?php print get_text('ID');?>";
var w2_text = "<?php print get_text('Title');?>";
var w3_text = "<?php print get_text('Address');?>";
var w4_text = "<?php print get_text('Updated');?>";
var changed_wl_sort = false;
var wl_direct = "ASC";
var wl_field = "id";
var wl_id = "r1";
var wl_header = "<?php print get_text('ID');?>";

function set_warnloc_headers(id, header_text, the_bull) {
	if(id == "w1") {
		window.w1_text = header_text + the_bull;
		window.w2_text = "<?php print get_text('Title');?>";
		window.w3_text = "<?php print get_text('Address');?>";
		window.w4_text = "<?php print get_text('Updated');?>";
		} else if(id == "w2") {
		window.w2_text = header_text + the_bull;
		window.w1_text = "<?php print get_text('ID');?>";
		window.f3_text = "<?php print get_text('Address');?>";
		window.f4_text = "<?php print get_text('Updated');?>";
		} else if(id == "w3") {
		window.w3_text = header_text + the_bull;
		window.w1_text = "<?php print get_text('ID');?>";
		window.w2_text = "<?php print get_text('Title');?>";
		window.w4_text = "<?php print get_text('Updated');?>";
		} else if(id == "w4") {
		window.w4_text = header_text + the_bull;
		window.w1_text = "<?php print get_text('ID');?>";
		window.w2_text = "<?php print get_text('Title');?>";
		window.w3_text = "<?php print get_text('Address');?>";
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/list_warnlocations.php?sort='+window.wl_field+'&dir='+ window.wl_direct+'&version='+randomnumber+'&q='+sessID;
	sendRequest (url,warnloclist_cb, "");
	function warnloclist_cb(req) {
		var location_number = 0;
		var loc_arr = JSON.decode(req.responseText);
		if(loc_arr[0][0] == 0) {
			for(var key in wlmarkers) {
				if(wlmarkers[key]) {map.removeLayer(wlmarkers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Warn Locations to view.........</marquee>";
			$('the_wllist').innerHTML = outputtext;
			window.latest_wlocation = 0;
			} else {
			for(var key in wlmarkers) {
				if(wlmarkers[key]) {map.removeLayer(wlmarkers[key]);}
				}
			var outputtext = "<TABLE id='locationstable' class='cruises scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='f1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Location ID');?>');\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_fac_sort(this.id, 'id', '<?php print get_text('ID');?>')\">" + window.w1_text + "</TH>";
			outputtext += "<TH id='f2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Location Name');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'title', '<?php print get_text('Title');?>')\">" + window.w2_text + "</TH>";
			outputtext += "<TH id='f3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Location Address');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'address', '<?php print get_text('Address');?>')\">" + window.w3_text + "</TH>";
			outputtext += "<TH id='f4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Location data last updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_fac_sort(this.id, 'updated', '<?php print get_text('Updated');?>')\">" + window.w4_text + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			var i=0;
			for(var key in loc_arr) {
				if((key > 0) && (loc_arr[key][8]) && (loc_arr[key][8] != "")) {
					var loc_id = loc_arr[key][8];
					outputtext += "<TR id='" + i +"' CLASS='" + colors[i%2] + "' style='width: " + window.listwidth + "px;'>";
					outputtext += "<TD onClick='mywlclick(" + i + ");'>" + i + "</TD>";
					outputtext += "<TD style='background-color: " + loc_arr[key][5] + "; color: " + loc_arr[key][6] + ";' onClick='mywlclick(" + i + ");'>" + loc_arr[key][1] + "</TD>";
					outputtext += "<TD onClick='mywlclick(" + i + ");'>" + loc_arr[key][4] + "</TD>";
					outputtext += "<TD onClick='mywlclick(" + i + ");'>" + loc_arr[key][7] + "</TD>";
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
					if((isFloat(loc_arr[key][2])) && (isFloat(loc_arr[key][3]))) {
						var marker = createWlocationMarker(loc_arr[key][2], loc_arr[key][3], infowindowtext, 0, 0, i, i,  0, 0, "This is a Warn Location");
						marker.addTo(map);
						location_number = loc_id;
						}
					}
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {	
				if(window.fac_last_display == 0) {
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
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
						} else {
						var cellwidthBase = window.listwidth / 16;
						wcell1 = cellwidthBase;
						wcell2 = cellwidthBase * 7;
						wcell3 = cellwidthBase * 7;
						wcell4 = cellwidthBase;
						headerRow.cells[0].style.width = wcell1 + "px";
						headerRow.cells[1].style.width = wcell2 + "px";
						headerRow.cells[2].style.width = wcell3 + "px";
						headerRow.cells[3].style.width = wcell4 + "px";
						}
					}
				},500);
			}
		}				// end function warnloclist_cb()
	warnloclist_get();
	}				// end function load_warnloclist()	

function warnloclist_setwidths() {
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
	tableRow.cells[0].style.width = window.wcell1 + "px";
	tableRow.cells[1].style.width = window.wcell2 + "px";
	tableRow.cells[2].style.width = window.wcell3 + "px";
	tableRow.cells[3].style.width = window.wcell4 + "px";
	headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
	headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
	headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
	headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
	}
	
function warnloclist_get() {
	if (wl_interval!=null) {return;}
	wl_interval = window.setInterval('warnloc_loop()', 600000);
	}			// end function mu get()

function warnloc_loop() {
	load_warnloclist(fac_field, fac_direct);
	}			// end function do_loop()
	
function load_fs_incidentlist() {
	if($('the_list').innerHTML == "") {
		$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);	
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/full_screen_incidents.php?func='+inc_period+'&version='+randomnumber+'&q='+sessID;
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
			var outputtext = "<TABLE id='incidenttable' class='cruises scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='t1' class='plain_listheader_fs'><?php print get_text('ID');?></TH>";
			outputtext += "<TH id='t2' class='plain_listheader_fs'><?php print get_text('Scope');?></TH>";
			outputtext += "<TH id='t3' class='plain_listheader_fs'><?php print get_text('Address');?></TH>";
			outputtext += "<TH id='t4' class='plain_listheader_fs'><?php print get_text('Type');?></TH>";
			outputtext += "<TH id='t5' class='plain_listheader_fs'><?php print get_text('P');?></TH>";
			outputtext += "<TH id='t6' class='plain_listheader_fs'><?php print get_text('A');?></TH>";
			outputtext += "<TH id='t7' class='plain_listheader_fs'><?php print get_text('U');?></TH>";
			outputtext += "<TH id='t8' class='plain_listheader_fs'><?php print get_text('As of');?></TH>";
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
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + key + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][0] + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][1] + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][4] + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][17] + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][16] + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + blinkstart + inc_arr[key][18] + blinkend + "</TD>";
					outputtext += "<TD class='fs_td' style='color: " + inc_arr[key][14] + ";'>" + inc_arr[key][10] + "</TD>";
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
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/full_screen_responders.php?version='+randomnumber+'&q='+sessID;
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
						infowindowtext = resp_arr[key][19];
						if(rmarkers[key]) {
							var curPos = rmarkers[key].getLatLng();
							if((curPos.lat != resp_arr[key][3]) || (curPos.lng != resp_arr[key][4])) {
								theLatLng = new L.LatLng(resp_arr[key][3], resp_arr[key][3]);
								rmarkers[key].setLatLng(theLatLng);
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
				if(window.resp_last_display == 0) {
					$('boxes').innerHTML = resp_arr[0][21];
					window.latest_responder = responder_number;
					set_categories();
					} else {
					if((ticket_number != window.latest_ticket) || (window.do_update == true) || (window.changed_resp_sort == true)) {
						$('boxes').innerHTML = resp_arr[0][21];
						window.latest_responder = responder_number;
						set_categories();
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/full_screen_facilities.php?version='+randomnumber+'&q='+sessID;
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
		}).addTo(map);
		boundary[theID] = polygon;
		if(linename) {
			bound_names[theID] = linename;
			}
		}
	return polygon;
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
			}).addTo(map);
		draw_circle.bindPopup(linename);
		boundary[theID] = draw_circle;		
		if(linename) {
			bound_names[theID] = linename;
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
	boundary[theID] = myTextLabel;
	if(linename) {
		bound_names[theID] = linename;
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
				} else if(theType == "c") {
				var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "basemarkup", theID);
				} else if(theType == "t") {
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
			blink_text2(flag, '#00FF00', '#FFFF00', '#FFFF00', '#FF0000')
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
			}
		}				// end function fencecheckcb()
	}				// end function check_ringfence()
	
function load_status_control() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/get_status_controls.php?version='+randomnumber+'&q='+sessID;
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/get_status_bgcolors.php?version='+randomnumber+'&q='+sessID;
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/get_status_textcolors.php?version='+randomnumber+'&q='+sessID;
	sendRequest (url,sc_textcol_cb, "");		
	function sc_textcol_cb(req) {
		var sctextcol_arr = JSON.decode(req.responseText);
		for(var key in sctextcol_arr) {
			if(IsNumeric(key)){window.status_textcolors[key] = sctextcol_arr[key];}
			}
		}				// end function sc_textcol_cb()
	}				// end function load_status_textcolors()
	
function load_fac_status_control() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/get_fac_status_controls.php?version='+randomnumber+'&q='+sessID;
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

var file1_text = "<?php print get_text('Filename');?>";
var file2_text = "<?php print get_text('Uploaded');?>";
var file3_text = "<?php print get_text('Date');?>";
var file4_text = "<?php print get_text('Linked with');?>";
var changed_file_sort = false;
var file_direct = "ASC";
var file_field = "name";
var file_id = "file1";
var file_header = "<?php print get_text('Filename');?>";

function set_file_headers(id, header_text, the_bull) {
	if(id == "file1") {
		window.file1_text = header_text + the_bull;
		window.file2_text = "<?php print get_text('Uploaded');?>";
		window.file3_text = "<?php print get_text('Date');?>";
		window.file4_text = "<?php print get_text('Linked with');?>";
		} else if(id == "file2") {
		window.file2_text = header_text + the_bull;
		window.file1_text = "<?php print get_text('Filename');?>";
		window.file3_text = "<?php print get_text('Date');?>";
		window.file4_text = "<?php print get_text('Linked with');?>";
		} else if(id == "file3") {
		window.file3_text = header_text + the_bull;
		window.file1_text = "<?php print get_text('Filename');?>";
		window.file2_text = "<?php print get_text('Uploaded');?>";
		window.file4_text = "<?php print get_text('Linked with');?>";
		} else if(id == "file4") {
		window.file4_text = header_text + the_bull;
		window.file1_text = "<?php print get_text('Filename');?>";
		window.file2_text = "<?php print get_text('Uploaded');?>";
		window.file3_text = "<?php print get_text('Date');?>";
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
			var outputtext = "<FORM NAME='filesForm'><TABLE id='filestable' class='cruises scrollable' style='width: 100%;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH id='fb' class='plain_listheader'>&nbsp;&nbsp;</TH>";
			outputtext += "<TH id='file1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('The File Name');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'name', '<?php print get_text('Filename');?>')\">" + window.file1_text + "</TH>";
			outputtext += "<TH id='file2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Who uploaded this?');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'owner', '<?php print get_text('Uploaded');?>')\">" + window.file2_text + "</TH>";
			outputtext += "<TH id='file3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('When was it uploaded?');?>?');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'updated', '<?php print get_text('Date');?>')\">" + window.file3_text + "</TH>";
			outputtext += "<TH id='file4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('File Associated with?');?>?');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_file_sort(this.id, 'updated', '<?php print get_text('Linked with');?>')\">" + window.file4_text + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in file_arr) {
				if(file_arr[key][0]) {
					var file_id = file_arr[key][6];
					var the_title = (file_arr[key][3] != "") ? file_arr[key][3] : "Untitled";
					var theURL = "./ajax/download.php?filename=" + file_arr[key][0] + "&origname=" + file_arr[key][1] + "&type=" + file_arr[key][2];
					outputtext += "<TR CLASS='" + colors[i%2] + "' style='width: 100%;'>";
					outputtext += "<TD><input type='checkbox' name='frm_file[]' value='" + file_id + "'></TD>";					
					outputtext += "<TD style='white-space: nowrap;' onClick='location.href=\"" + theURL + "\"'>" + pad(30, the_title, "\u00a0") + "</TD>";
					outputtext += "<TD onClick='location.href=\"" + theURL + "\"'>" + pad(17, file_arr[key][4], "\u00a0") + "</TD>";
					outputtext += "<TD style='white-space: nowrap;' onClick='location.href=\"" + theURL + "\"'>" + pad(20, file_arr[key][5], "\u00a0") + "</TD>";
					outputtext += "<TD onClick='location.href=\"" + theURL + "\"'>" + pad(20, file_arr[key][7], "\u00a0") + "</TD>";
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
					if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
					if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
					if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
					if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
					if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
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
var log_header = "<?php print get_text('Owner');?>";
var fil1_text = "<?php print get_text('Owner');?>";
var fil2_text = "<?php print get_text('Event');?>";
var fil3_text = "<?php print get_text('When');?>";
var fil4_text = "<?php print get_text('Unit');?>";
var fil5_text = "<?php print get_text('Ticket');?>";
var fil6_text = "<?php print get_text('Info');?>";

function set_log_headers(id, header_text, the_bull) {
	if(id == "fil1") {
		window.fil1_text = header_text + the_bull;
		window.fil2_text = "<?php print get_text('Event');?>";
		window.fil3_text = "<?php print get_text('When');?>";
		window.fil4_text = "<?php print get_text('Unit');?>";
		window.fil5_text = "<?php print get_text('Ticket');?>";
		window.fil6_text = "<?php print get_text('Info');?>";
		} else if(id == "fil2") {
		window.fil2_text = header_text + the_bull;
		window.fil1_text = "<?php print get_text('Owner');?>";
		window.fil3_text = "<?php print get_text('When');?>";
		window.fil4_text = "<?php print get_text('Unit');?>";
		window.fil5_text = "<?php print get_text('Ticket');?>";
		window.fil6_text = "<?php print get_text('Info');?>";
		} else if(id == "fil3") {
		window.fil3_text = header_text + the_bull;
		window.fil2_text = "<?php print get_text('Event');?>";
		window.fil1_text = "<?php print get_text('Owner');?>";
		window.fil4_text = "<?php print get_text('Unit');?>";
		window.fil5_text = "<?php print get_text('Ticket');?>";
		window.fil6_text = "<?php print get_text('Info');?>";
		} else if(id == "fil4") {
		window.fil4_text = header_text + the_bull;
		window.fil2_text = "<?php print get_text('Event');?>";
		window.fil3_text = "<?php print get_text('When');?>";
		window.fil1_text = "<?php print get_text('Owner');?>";
		window.fil5_text = "<?php print get_text('Ticket');?>";
		window.fil6_text = "<?php print get_text('Info');?>";
		} else if(id == "fil5") {
		window.fil5_text = header_text + the_bull;
		window.fil2_text = "<?php print get_text('Event');?>";
		window.fil3_text = "<?php print get_text('When');?>";
		window.fil4_text = "<?php print get_text('Unit');?>";
		window.fil1_text = "<?php print get_text('Owner');?>";
		window.fil6_text = "<?php print get_text('Info');?>";
		} else {
		window.fil6_text = header_text + the_bull;
		window.fil2_text = "<?php print get_text('Event');?>";
		window.fil3_text = "<?php print get_text('When');?>";
		window.fil4_text = "Unit";
		window.fil5_text = "Ticket";
		window.fil1_text = "<?php print get_text('Owner');?>";
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
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/sit_log.php?sort=' + window.log_field + '&dir=' + window.log_direct + '&version=' + randomnumber+'&q='+sessID;
	sendRequest (url,loglist_cb, "");
	function loglist_cb(req) {
		var i = 1;
		var log_arr = JSON.decode(req.responseText);
		if(log_arr[0][0] == 0) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold; color: #000000;'>......Log empty.........</marquee>";	
			$('the_loglist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='logtable' class='cruises scrollable' style='width: " + window.mapWidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.mapWidth + "px;'>";
			outputtext += "<TH id='fil1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Who logged this');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'id', '<?php print get_text('Owner');?>')\">" + window.fil1_text + "</TH>";
			outputtext += "<TH id='fil2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('What type of event was this');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'code', '<?php print get_text('Event');?>')\">" + window.fil2_text + "</TH>";
			outputtext += "<TH id='fil3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('When did this happen');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'date', '<?php print get_text('When');?>')\">" + window.fil3_text + "</TH>";
			outputtext += "<TH id='fil4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('If this related to a responder, who was it?');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'responder_id', '<?php print get_text('Unit');?>')\">" + window.fil4_text + "</TH>";
			outputtext += "<TH id='fil5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('If this related to a Ticket, which one?');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'ticket_id', '<?php print get_text('Ticket');?>')\">" + window.fil5_text + "</TH>";
			outputtext += "<TH id='fil6' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Additional information about this log entry');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_sort(this.id, 'info', '<?php print get_text('Info');?>')\">" + window.fil6_text + "</TH>";
			outputtext += "<TH id='fil7'>" + pad(3, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in log_arr) {
				if(log_arr[key][0]) {
					if(log_arr[key][11] != "") {
						var theURL = log_arr[key][11];
						outputtext += "<TR CLASS='" + colors[i%2] + "' style='width: " + window.mapWidth + "px; text-decoration: underline; color: blue;' onMouseover=\"Tip('" + log_arr[key][12] + "')\" onmouseout='UnTip();' onClick='location.href=\"" + theURL + "\"'>";
						} else {
						outputtext += "<TR CLASS='" + colors[i%2] + "' style='width: " + window.mapWidth + "px;' onMouseover=\"Tip('" + log_arr[key][12] + "')\" onmouseout='UnTip();'>";
						}
					outputtext += "<TD>" + log_arr[key][1] + "</TD>";
					outputtext += "<TD>" + log_arr[key][4] + "</TD>";
					outputtext += "<TD>" + log_arr[key][3] + "</TD>";
					outputtext += "<TD>" + log_arr[key][6] + "</TD>";
					outputtext += "<TD>" + log_arr[key][5] + "</TD>";
					outputtext += "<TD>" + log_arr[key][10] + "</TD>";
					outputtext += "<TD>" + pad(3, " ", "\u00a0") + "</TD>";
					outputtext += "</TR>";
					}
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {$('the_loglist').innerHTML = outputtext;
				var logtbl = document.getElementById('logtable');
				if(logtbl) {
					var headerRow = logtbl.rows[0];
					var tableRow = logtbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
						if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
						if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
						if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
						} else {
						var cellwidthBase = window.mapWidth / 24;
						headerRow.cells[0].style.width = (cellwidthBase * 4) + "px";
						headerRow.cells[1].style.width = (cellwidthBase * 4) + "px";
						headerRow.cells[2].style.width = (cellwidthBase * 4) + "px";
						headerRow.cells[3].style.width = (cellwidthBase * 4) + "px";
						headerRow.cells[4].style.width = (cellwidthBase * 3) + "px";
						headerRow.cells[5].style.width = (cellwidthBase * 3) + "px";
						headerRow.cells[6].style.width = (cellwidthBase * 5) + "px";
						}				
					}
				window.logFin = true;
				pageLoaded();
				},500);
			}
		}				// end function loglist_cb()
	}				// end function load_log()			

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
			the_descrip += "<TR class='header'><TD COLSPAN=2 class = 'header' style='text-align: center;'>" + the_header + "</TD></TR>";
			the_descrip += "<TR class='even'><TD class='td_label'>Title</TD><TD>" + the_text + "</TD></TR>";
			the_descrip += "<TR class='odd'><TD class='td_label'>Category</TD><TD>" + the_category + "</TD></TR>";
			the_descrip += "<TR class='even'><TD COLSPAN=2 class='td_label'>Description</TD></TR>";
			the_descrip += "<TR class='even'><TD COLSPAN=2>";
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
var msg_header = "Msg";
var msg_ticket = 0;
var_msg_responder = 0;
var msg_facility = 0;
var msg_mi = 0;
var msg1_text = "<?php print get_text('Msg');?>";
var msg2_text = "<?php print get_text('Tkt');?>";
var msg3_text = "<?php print get_text('Type');?>";
var msg4_text = "<?php print get_text('From');?>";
var msg5_text = "<?php print get_text('To');?>";
var msg6_text = "<?php print get_text('Subj');?>";
var msg7_text = "<?php print get_text('Date');?>";
var msg8_text = "<?php print get_text('Owner');?>";

function set_msg_headers(id, header_text, the_bull) {
	if(id == "m1") {
		window.msg1_text = header_text + the_bull;
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = "<?php print get_text('Date');?>";
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m2") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = header_text + the_bull;
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = "<?php print get_text('Date');?>";
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m3") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = header_text + the_bull;
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = "<?php print get_text('Date');?>";
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m4") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = header_text + the_bull;
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = "<?php print get_text('Date');?>";
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m5") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = header_text + the_bull;
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = "<?php print get_text('Date');?>";
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m6") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = header_text + the_bull;
		window.msg7_text = "<?php print get_text('Date');?>";
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m7") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = header_text + the_bull;
		window.msg8_text = "<?php print get_text('Owner');?>";
		} else if(id == "m8") {
		window.msg1_text = "<?php print get_text('Msg');?>";
		window.msg2_text = "<?php print get_text('Tkt');?>";
		window.msg3_text = "<?php print get_text('Type');?>";
		window.msg4_text = "<?php print get_text('From');?>";
		window.msg5_text = "<?php print get_text('To');?>";
		window.msg6_text = "<?php print get_text('Subj');?>";
		window.msg7_text = "<?php print get_text('Date');?>";
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
	var url ='./ajax/sidebar_list_messages.php'+theSearchstring+theSortField+theOrder+"&version=" + randomnumber + "&inorout=" + window.inorout;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
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
		var outputtext = "<TABLE id='messagestable' class='cruises scrollable' style='width: 700px;'>";
		outputtext += "<thead>";
		outputtext += "<TR style='width: 100%;'>";
		outputtext += "<TH id='m1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Message ID');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'message', '<?php print get_text('Msg');?>')\">" + window.msg1_text + "</TH>";
		outputtext += "<TH id='m2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('If this is specific to a Ticket, which one');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'ticket_id', '<?php print get_text('Tkt');?>')\">" + window.msg2_text + "</TH>";
		outputtext += "<TH id='m3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Message Type, IE-Incoming email, OE-Outgoing email, IS-Incoming SMS, OS-Outgoing SMS');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'msg_type', '<?php print get_text('Type');?>')\">" + window.msg3_text + "</TH>";
		outputtext += "<TH id='m4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Sender');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'from', '<?php print get_text('From');?>')\">" + window.msg4_text + "</TH>";
		outputtext += "<TH id='m5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Who was it sent to');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'to', '<?php print get_text('To');?>')\">" + window.msg5_text + "</TH>";
		outputtext += "<TH id='m6' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Message subject');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'subject', '<?php print get_text('Subj');?>')\">" + window.msg6_text + "</TH>";
		outputtext += "<TH id='m7' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Message date');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'date', '<?php print get_text('Date');?>')\">" + window.msg7_text + "</TH>";
		outputtext += "<TH id='m8' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Which Tickets user owns this message - specific for outgoing or original sender of message replied to');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_msg_sort(this.id, 'owner', '<?php print get_text('Owner');?>')\">" + window.msg8_text + "</TH>";		
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
			outputtext += "<TR class=\"" + theClass + "\" title=\"" + theTitle + "\" style='width: 100%;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">";
			outputtext += "<TD style='" + theStatus + ";'>" + the_messages[key][10] + "</TD>";	//	Message ID
			outputtext += "<TD style='" + theStatus + ";'>" + the_messages[key][1] + "</TD>";	//	Ticket ID
			outputtext += "<TD style='" + theStatus + ";'>" + pad(8, the_messages[key][2], "\u00a0") + "</TD>";	//	Type Padded to 8 characters
			outputtext += "<TD style='" + theStatus + ";'>" + the_messages[key][3] + "</TD>";	//	From
			outputtext += "<TD style='" + theStatus + ";'>" + the_messages[key][4] + "</TD>";	//	To
			outputtext += "<TD style='" + theStatus + ";'>" + the_messages[key][5] + "</TD>";	//	Subject
			outputtext += "<TD style='" + theStatus + "; " + the_del_flag + ";'>" + the_messages[key][7] + "</TD>";		//	Date
			outputtext += "<TD style='" + theStatus + ";'>" + the_messages[key][8] + "</TD>";	//	Owner
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
					headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";
					headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";
					headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";
					headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";
					headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";
					headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";
					headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";
					headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";
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
		var tableRow = msgtbl.rows[viewableRow];
		tableRow.cells[0].style.width = window.mcell1 + "px";
		tableRow.cells[1].style.width = window.mcell2 + "px";
		tableRow.cells[2].style.width = window.mcell3 + "px";
		tableRow.cells[3].style.width = window.mcell4 + "px";
		tableRow.cells[4].style.width = window.mcell5 + "px";
		tableRow.cells[5].style.width = window.mcell6 + "px";
		tableRow.cells[6].style.width = window.mcell7 + "px";
		tableRow.cells[7].style.width = window.mcell8 + "px";
		headerRow.cells[0].style.width = tableRow.cells[0].clientWidth-4+"px";
		headerRow.cells[1].style.width = tableRow.cells[1].clientWidth-4+"px";
		headerRow.cells[2].style.width = tableRow.cells[2].clientWidth-4+"px";
		headerRow.cells[3].style.width = tableRow.cells[3].clientWidth-4+"px";
		headerRow.cells[4].style.width = tableRow.cells[4].clientWidth-4+"px";
		headerRow.cells[5].style.width = tableRow.cells[5].clientWidth-4+"px";
		headerRow.cells[6].style.width = tableRow.cells[6].clientWidth-4+"px";
		headerRow.cells[7].style.width = tableRow.cells[7].clientWidth-4+"px";
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
		var mapWidth = <?php print get_variable('map_width');?>+32;
		var mapHeight = <?php print get_variable('map_height');?>+200;		// 3/12/10
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
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
		var mapWidth = <?php print get_variable('map_width');?>+32;
		var mapHeight = <?php print get_variable('map_height');?>+200;		// 3/12/10
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
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
		var theAPI = '<?php print get_variable('cloudmade_api');?>';
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
			var outputtext = "<TABLE id='assignmentstable' class='cruises scrollable' style='width: 100%;'>";
			outputtext += "<thead>";
			outputtext += "<TR c='" + colors[i%2] + "'  style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='ass1' class='plain_listheader_fs'><?php print get_text('Ticket');?></TH>";
			outputtext += "<TH id='ass2' class='plain_listheader_fs'><?php print get_text('Description');?></TH>";
			outputtext += "<TH id='ass3' class='plain_listheader_fs'><?php print get_text('Unit');?></TH>";
			outputtext += "<TH id='ass4' class='plain_listheader_fs'><?php print get_text('DS');?></TH>";
			outputtext += "<TH id='ass5' class='plain_listheader_fs'><?php print get_text('Date');?></TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key = 0; key < ass_arr.length; key++) {
				var the_resp = ass_arr[key][6];
				outputtext += "<TR class='" + colors[i%2] + "' style='width: " + window.listwidth + "px;' onClick='myrclick(" + the_resp + ");'>";
				outputtext += "<TD class='fs_td' >" + ass_arr[key][0] + "</TD>";
				outputtext += "<TD class='fs_td' >" + ass_arr[key][2] + "</TD>";
				outputtext += "<TD class='fs_td' >" + ass_arr[key][4] + "</TD>";
				outputtext += "<TD class='fs_td' >" + ass_arr[key][5] + "</TD>";
				outputtext += "<TD class='fs_td' >" + ass_arr[key][1] + "</TD>";
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
	conditions_get();	
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
//	$(uncheckControl).style.display = "inline-block";
//	$(checkControl).style.display = "none";
	}		// end function do_clear
	
function do_clear(myForm, checkControl, uncheckControl){
	for (i=0;i<myForm.elements.length; i++) {
		if(myForm.elements[i].type =='checkbox'){
			myForm.elements[i].checked = false;
			}
		}		// end for ()
	check_checkboxes(myForm, checkControl, uncheckControl);
//	$(uncheckControl).style.display = "none";
//	$(checkControl).style.display = "inline-block";
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
	var screenName = (myForm.frm_screenname.value.trim() != "") ? "&screenname=" + myForm.frm_screenname.value.trim() : "";
	var message = URLEncode(myForm.frm_message.value.trim());
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	if(tUserid == "" && screenName == ""){
		var url = './ajax/twitter_send.php?message=' + message + '&version=' + randomnumber + '&q=' + sessID;
		} else {
		var url = './ajax/twitter_direct_send.php?message=' + message + tUserid + screenName + '&version=' + randomnumber + '&q=' + sessID;		
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
				slert(theOutput);
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
	var screenName = prompt("User Name?", "");
	var twitterID = prompt("Twitter ID?", "");
	twitterID = "&userid=" + twitterID.trim();
	screenName = "&screenname=" + screenName.trim();
	var extra = prompt("Any extra information?", "");
	if(extra) {
		message = extra + " " + message;
		}
	message = URLEncode(message);
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	if(twitterID == "" && screenName == ""){
		var url = './ajax/twitter_send.php?message=' + message + '&version=' + randomnumber + '&q=' + sessID;
		} else {
		var url = './ajax/twitter_direct_send.php?message=' + message + twitterID + screenName + '&version=' + randomnumber + '&q=' + sessID;		
		}
	sendRequest(url, theCB2, "");
	function theCB2(req) {
		var theResult2 = JSON.decode(req.responseText);
		var theOutput2 = "";
		if(theResult2) {
			if(theResult2[0] == 1) {
				theOutput2 += "Tweet Sent";
				} else {
				theOutput2 += theResult2[0];
				}
			} else {
			theOutput2 += "Tweet Failed";
			}
		alert(theOutput2);
		}
	}
