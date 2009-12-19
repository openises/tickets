<?php
/*
6/9/08  added  'Closed Calls' button
7/27/08 handle deleted status values
8/02/08 provide link to dispatch function
8/3/08  add assign data to unit IW's
8/6/08  added function do_tracks
8/15/08 mysql_fetch_array to mysql_fetch_assoc - performance
8/22/08 added usng position
8/24/08 revised sort order to include severity
8/25/08 added responders TITLE display
8/25/08 revised map control type to small - for TB
9/8/08  lat/lng to CG format
9/12/08 added USNG PHP functions
9/14/08 added js trim()
10/9/08 added check for div defined - IE JS pblm
10/14/08 changed reference to usng.js
10/15/08 changed 'Comments' to 'Disposition'
10/15/08 corrections re LL2NGS
10/16/08 added traffic functions
10/17/08 added hide_Units()
10/21/08 added edit link in infowindow
10/21/08 added  rand into link, istest as global
11/1/08 added prefix
11/06/08 sql error
11/6/08 missing table close tags corrected, timer for mini-map
11/29/08 added streetview
12/24/08 added GOverviewMapControl()
1/6/09  revised unit types for variable types
1/9/09  use icons subdir
1/10/09 dollar function added
1/17/09 caption changed to 'situation'
1/21/09 - drop aprs field fm unit types
1/23/09 tracks correction
1/25/09 do/don't show serial no.
1/27/09 revised sort order
1/29/09 revised icons array index
2/2/09 order sorts 'status=completed' last, unit status fix for non-existent keys.
2/11/09 added streetview function, removed redundant dollar function
2/12/09, 2/14/09 added persistence to show/hide units
2/13/09 added to_str() for no. units > 25
2/21/09 dropped infowindow from map
2/24/09 handle no-position units
3/2/09 corrected table caption
3/3/09 underline units sans position
3/16/09	get current aprs, instam updates
3/23/09 null added as possible value
3/23/09 is_float() replaces settype(), the latter not detecting 0, fix quotes
3/25/09 added time validation for remote sources, my_is_float()
4/2/09 correction for sidebar letters, added default zoom handling, closed ticket display interval
5/4/09 my_is_float() repl is_float
7/9/09 popups, per AH, COLOR='blue' correction
7/16/09	protocol display
7/27/09	'id' ambiguity resolved
7/29/09 Added Gtrack, Locatea and Google Latitude tracking sources, revised mobile speed icon display
7/29/09 Modified code to get tracking data, updated time and speed to fix errors. variable for updated and speed is now set before query result is unset. 
8/1/09 Added Facilities display
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
8/3/09 Added code to get locale variable and change USNG/OSGB/UTM dependant on variable in tabs and sidebar.
8/3/09 Revised function popup_ticket to remove spurious listener.
8/7/09 Revised show/hide units and show hide incident markers
8/11/09 Revised code for incident popup to use function my_is_float to capture out units with no location
8/11/09 Added code to show responding units on incident details screen.
8/12/09 Revised MYSQL queries where there is an ambiguity between field names (description) in Ticket and In_types tables to correct ticket display
8/12/09	toUTM() parameters corrected
8/13/09	shorten() disposition, etc. 
8/19/09 drawCircle() added
9/29/09 Added Handling for Special Tickets
10/8/09 Index in list and on marker changed to part of name after / for both units and facilities
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs for both units and facilities
10/21/09 Added hide/show for unavailable units in Situation map.
10/21/09 Added check for any closed or special incidents on the database before showing the buttons in the situation screen.
10/27/09 Added check for special incidents being due and bring to current situation screen if due and mark with * in list.
10/27/09 Added Booked date to Info Window tab 1 for ticket.
10/28/09 Added receiving facility to Info Window tab 1 for ticket
10/30/09 Added dispatch times and miles to ticket print, fixed action/patient print
10/30/09 Removed period after index in sidebar
11/06/09 Changed "Special" Incidents to "Scheduled" Incidents.
11/10/09 fixes to facilities display by AS
11/11/09 top/bottom anchors added
11/20/09 sort order handle, name
*/

//	{ -- dummy


function list_tickets($sort_by_field='',$sort_value='') {	// list tickets ===================================================
	global $my_session, $istest;
//	$dzf = get_variable('def_zoom_fixed');			// 4/2/09
	$cwi = get_variable('closed_interval');			// closed window interval in hours

//	snap(basename(__FILE__), __LINE__);

	$get_status = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['status'])))) ) ? "" : $_GET['status'] ;
	$get_sortby = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['sortby'])))) ) ? "" : $_GET['sortby'] ;
	$get_offset = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['offset'])))) ) ? "" : $_GET['offset'] ;

	if (!isset($_GET['status'])) {
		$open = "Open";
	} else {
	$open = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_OPEN']))? "Open" : "";
	}

	$closed = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_CLOSED']))? "Closed" : "";
	$scheduled = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_SCHEDULED']))? "Scheduled" : "";			// 9/29/09

	if ((empty($closed)) && (empty($scheduled))) {		// 9/29/09
		$heading = "Current Situation";
		}

	if (!empty($closed)) {					// 9/29/09
		$heading = "Closed Incidents";
		}

	if (!empty($scheduled)) {					// 9/29/09
		$heading = "Scheduled Incidents";
		}

//	$heading = $closed ? "Closed Incidents" : "Current Situation";		// 3/2/09

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

?>
<TABLE BORDER=0>
	<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'><?php print $heading; ?> </FONT></TD></TR>	<!-- 1/17/09 -->
	<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>
	<TR><TD VALIGN='TOP' width='400px' ><DIV ID='side_bar'></DIV></TD>
		<TD></TD>
		<TD CLASS='td_label'>
			<DIV ID='map' STYLE='WIDTH: <?php print get_variable('map_width');?>PX; HEIGHT: <?php print get_variable('map_height');?>PX'></DIV>

		<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR />
			<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doTraffic()'><U>Traffic</U></A><BR /><BR />

		Units:<IMG SRC = './icons/sm_white.png' BORDER=0><IMG SRC = './icons/sm_black.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 10/21/09 -->

		<SPAN ID="show_it" STYLE="display: none" onClick = "do_show_Units();"><U>Show</U></SPAN>
		<SPAN ID="hide_it" STYLE="display: ''" onClick = "do_hide_Units();"><U>Hide</U></SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<SPAN ID="hide_unavail" STYLE="display: ''" onClick = "hide_unit_stat_unavail();"><U>Hide unavailable</U></SPAN>	<!-- 10/21/09 -->
		<SPAN ID="show_unavail" STYLE="display: ''" onClick = "show_unit_stat_unavail();"><U>Show unavailable</U></SPAN>	<!-- 10/21/09 -->
		<BR /><BR />
		
		Facilities:<IMG SRC = './icons/sm_shield_green.png' BORDER=0><IMG SRC = './icons/sm_square_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 10/21/09 -->

		<SPAN ID="hide_fac" STYLE="display: ''" onClick = "hide_Facilities();"><U>Hide</U></SPAN>
		<SPAN ID="show_fac" STYLE="display: none" onClick = "show_Facilities();"><U>Show</U></SPAN>

		<BR /><BR />

		<SPAN ID="incidents" STYLE="display: inline-block">
		Incident Priority:<IMG SRC = './icons/sm_blue.png' BORDER=0><IMG SRC = './icons/sm_green.png' BORDER=0><IMG SRC = './icons/sm_red.png' BORDER=0>&nbsp;&nbsp;	<!-- 10/21/09 -->
		<A HREF="#" onClick = "hideGroup(1)">Typical: 	<IMG SRC = './icons/sm_blue.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp; <!-- 1/9/09 -->
		<A HREF="#" onClick = "hideGroup(2)">	High: 	<IMG SRC = './icons/sm_green.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		<A HREF="#" onClick = "hideGroup(3)">Highest: 	<IMG SRC = './icons/sm_red.png' BORDER=0></A>
		</SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN ID="show_all_icon" STYLE="display: none"><A HREF="#" onClick = "show_All()">Show all: <IMG SRC = './markers/sm_white.png' BORDER=0></A></SPAN>
		</NOBR></CENTER><BR />

<BR />
		</TD>

		</CENTER><BR /></TD>
	</TR>

	<TR><TD COLSPAN='99'> </TD></TR>
	<TR><TD CLASS='td_label' COLSPAN=3 ALIGN='center'>
		&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:shoreas@Gmail.com?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		</TD></TR></TABLE>
	<FORM NAME='unit_form' METHOD='get' ACTION='units.php'>
	<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
	<INPUT TYPE='hidden' NAME='view' VALUE=''>
	<INPUT TYPE='hidden' NAME='edit' VALUE=''>
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

	<FORM NAME='tick_form' METHOD='get' ACTION='edit.php'>				<!-- 11/27/09 -->
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

	<FORM NAME='facy_form' METHOD='get' ACTION='facilities.php'>		<!-- 11/27/09 -->
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
	</FORM>

<SCRIPT>
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

//	135
//	function $() {									// 2/12/09
//		var elements = new Array();
//		for (var i = 0; i < arguments.length; i++) {
//			var element = arguments[i];
//			if (typeof element == 'string')
//				element = document.getElementById(element);
//			if (arguments.length == 1)
//				return element;
//			elements.push(element);
//			}
//		return elements;
//		}

	/* fun ction $() Sample Usage:
	var obj1 = document.getElementById('element1');
	var obj2 = document.getElementById('element2');
	function alertElements() {
	  var i;
	  var elements = $('a','b','c',obj1,obj2,'d','e');
	  for ( i=0;i
	  }
	*/

	function to_str(instr) {			// 0-based conversion - 2/13/09
//		alert("143 " + instr);
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

	function sendRequest(url,callback,postData) {								// 2/14/09
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
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
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

<?php
	$quick = (!(is_guest()) && (intval(get_variable('quick')==1)));				// 11/27/09
	print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>

if (GBrowserIsCompatible()) {

//	$("map").style.backgroundImage = "url(./markers/loading.jpg)";
	$("map").style.backgroundImage = "url('http://maps.google.com/staticmap?center=<?php echo get_variable('def_lat');?>,<?php echo get_variable('def_lng');?>&zoom=<?php echo get_variable('def_zoom');?>&size=<?php echo get_variable('map_width');?>x<?php echo get_variable('map_height');?>&key=<?php echo get_variable('gmaps_api_key');?> ')";

	var colors = new Array ('odd', 'even');

	function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09
	
//		drawCircle(53.479874, -2.246704, 10.0, "#000080", 1, 0.75, "#0000FF", .5);

		var d2r = Math.PI/180;
		var r2d = 180/Math.PI;
		var Clat = radius * 0.014483;
		var Clng = Clat/Math.cos(lat * d2r);
		var Cpoints = [];
		for (var i=0; i < 33; i++) {
			var theta = Math.PI * (i/16);
			Cy = lat + (Clat * Math.sin(theta));
			Cx = lng + (Clng * Math.cos(theta));
			var P = new GPoint(Cx,Cy);
			Cpoints.push(P);
			}
		var polygon = new GPolygon(Cpoints, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity);
		map.addOverlay(polygon);
		}

	function hideGroup(color) {							// 8/7/09 Revised function to correct incorrect display
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].id == color) {
					gmarkers[i].show();
					}
				else {
					gmarkers[i].hide();			// 1/11/09
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("show_all_icon").style.display = "inline-block";
		$("incidents").style.display = "inline-block";

		}			// end function


	function show_All() {						// 8/7/09 Revised function to correct incorrect display
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		$("show_all_icon").style.display = "none";
		$("allIcons").style.display = "inline-block";
		$("incidents").style.display = "inline-block";
		}			// end function


	function show_Units() {						// 8/7/09 Revised function to correct incorrect display
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0 - 2/12/09
			if (gmarkers[i]) {
				if ((gmarkers[i].id == 0) || (gmarkers[i].id == 4)) {
					gmarkers[i].show();
					}
				else {
//					gmarkers[i].hide();						// hide incidents - 1/8/09
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("incidents").style.display = "inline-block";
		$("show_all_icon").style.display =	"inline-block";
		$('show_it').style.display='none';
		$('hide_it').style.display='inline';
		}

	function hide_Units () {								// 10/17/08
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (gmarkers[i]) {
				if ((gmarkers[i].id == 0) || (gmarkers[i].id == 4)) {			// 8/7/09 Revised function to correct incorrect display
					gmarkers[i].hide();
					}
				else {
					gmarkers[i].show();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("incidents").style.display = "inline-block";
		$("show_all_icon").style.display =	"inline-block";
		$("show_it").style.display=			"inline";				// 12/02/09
		$("hide_it").style.display=			"none";
		}				// end function hide_units ()
		
	function hide_unit_stat_unavail() {								// 10/21/09
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (gmarkers[i]) {
				if (gmarkers[i].stat == 1) {
					gmarkers[i].hide();
					}
				else {
					gmarkers[i].show();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("incidents").style.display = "inline-block";
		$("show_all_icon").style.display =	"inline-block";
		$("show_unavail").style.display=			"inline";
		$("hide_unavail").style.display=			"none";
		}				// end function hide_unit_stat_unavail ()		
		
	function show_unit_stat_unavail() {								// 10/21/09
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		$("incidents").style.display = "inline-block";
		$("show_all_icon").style.display =	"inline-block";
		$("show_unavail").style.display=			"none";
		$("hide_unavail").style.display=			"inline";
		}				// end function hide_unit_stat_unavail ()			

	function do_hide_Units() {						// 2/14/09
		var params = "f_n=f1&v_n=h&sess_id=<?php print get_sess_key(); ?>";					// flag 1, value h
		var url = "persist.php";
		sendRequest (url, h_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()

	function hide_Facilities() {								// 8/1/09
		for (var i = 0; i < fmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (fmarkers[i]) {
					fmarkers[i].hide();
					}
			} 	// end for ()
		$("hide_fac").style.display = "none";
		$("show_fac").style.display = "inline-block";
		$("fac_table").style.display = "none";
		}				// end function hide_Facilities ()

	function show_Facilities () {								// 8/1/09
		for (var i = 0; i < fmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (fmarkers[i]) {
					fmarkers[i].show();
					}
			} 	// end for ()
		$("hide_fac").style.display = "inline-block";
		$("show_fac").style.display = "none";
		$("fac_table").style.display = "inline-block";
		location.href = "#bottom";				// 11/11/09
		
		}				// end function show_Facilities ()


	function h_handleResult(req) {					// the 'called-back' persist function - hide
		hide_Units();
		}

	var starting = false;

	function do_mail_fac_win(id) {			// Facility email 9/22/09
		if(starting) {return;}					
		starting=true;	
		var url = "do_fac_mail.php?fac_id=" + id;	
		newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_in)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_in.focus();
		starting = false;
		}

	function do_show_Units() {
		var params = "f_n=f1&v_n=s&sess_id=<?php print get_sess_key(); ?>";					// flag 1, value s
		var url = "persist.php";
		sendRequest (url, s_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()

	function s_handleResult(req) {					// the 'called-back' persist function - show
		show_Units();
		}

	function do_sidebar (instr, id, sym, myclass) {								// constructs ticket and unit sidebar row - 1/7/09
		side_bar_html += "<TR CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='" + myclass + "'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 10/30/09 removed period
		}		// end function do sidebar ()

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
		GEvent.trigger(gmarkers[id], "click");
		location.href = "#top";
		}

	function do_sidebar_t_ed (sidebar, line_no, rcd_id, letter) {					// ticket edit 
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_ed_tick(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_data'>" + letter + "</TD>" + sidebar +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		location.href = "#top";
		}

	function do_sidebar_u_iw (instr, id, sym, myclass) {						// constructs unit incident sidebar row - 1/7/09
//		alert("467 " + id);
//		alert("468 " + sym);
		side_bar_html += "<TR CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='" + myclass + "'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 10/30/09 removed period
		}		// end function do sidebar ()

	function myclick_ed_tick(id) {				// Responds to sidebar click - edit ticket data
		document.tick_form.id.value=id;			// 11/27/09
		document.tick_form.submit();
		}

	function do_sidebar_u_ed (sidebar, line_no, rcd_id, letter) {					// unit edit 
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_data'>" + letter + "</TD>" + sidebar +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		location.href = "#top";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.unit_form.id.value=v_id;	// 11/27/09
		if (quick) {
			document.unit_form.edit.value="true";
			}
		else {
			document.unit_form.view.value="true";
			}
		document.unit_form.submit();
		}

	function do_sidebar_fac_ed (fac_instr, fac_id, fac_sym, myclass) {					// constructs facilities sidebar row 9/22/09
		side_bar_html += "<TR CLASS='" + colors[fac_id%2] +"' onClick = fac_click_ed(" + fac_id + ");><TD CLASS='" + myclass + "'><B>" + (fac_sym) + "</B></TD>"+ fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac_iw ()

	function do_sidebar_fac_iw (fac_instr, fac_id, fac_sym, myclass) {					// constructs facilities sidebar row 9/22/09
		side_bar_html += "<TR CLASS='" + colors[fac_id%2] +"' onClick = fac_click_iw(" + fac_id + ");><TD CLASS='" + myclass + "'><B>" + (fac_sym) + "</B></TD>"+ fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac ()

	function fac_click_iw(fac_id) {						// Responds to facilities sidebar click, triggers listener above 9/22/09
		GEvent.trigger(fmarkers[fac_id], "click");
		location.href = "#top";
		}

	function fac_click_ed(id) {							// Responds to facility sidebar click - edit data
		document.facy_form.id.value=id;					// 11/27/09
		document.facy_form.submit();
		}

	function createMarker(point, tabs, color, stat, id, sym) {					// Creates marker and sets up click event infowindow 10/21/09 added stat to hide unavailable units
		points = true;
		var icon = new GIcon(baseIcon);
//		alert("./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + sym);
		var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + sym;				// 1/6/09
		icon.image = icon_url;

		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide
		marker.stat = stat;				// 10/21/09

		GEvent.addListener(marker, "click", function() {					// here for both side bar and icon click
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();
			marker.openInfoWindowTabsHtml(infoTabs[id]);

			setTimeout(function() {											// wait for rendering complete - 11/6/08
				if ($("detailmap")) {				// 10/9/08
					var dMapDiv = $("detailmap");
					var detailmap = new GMap2(dMapDiv);
					detailmap.addControl(new GSmallMapControl());
					detailmap.setCenter(point, 17);  						// larger # = closer
					detailmap.addOverlay(marker);
					}
				else {
//					alert(62);
//					alert($("detailmap"));
					}
				},3000);				// end setTimeout(...)

			});
		gmarkers[id] = marker;							// marker to array for side_bar click function
		infoTabs[id] = tabs;							// tabs to array
		if (!(map_is_fixed)){
			bounds.extend(point);
			}
		return marker;
		}				// end function create Marker()

	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			}
		grid = !grid;
		}			// end function doGrid

    var trafficInfo = new GTrafficOverlay();
    var toggleState = true;

	function doTraffic() {				// 10/16/08
		if (toggleState) {
	        map.removeOverlay(trafficInfo);
	     	}
		else {
	        map.addOverlay(trafficInfo);
	    	}
        toggleState = !toggleState;			// swap
	    }				// end function doTraffic()


	var icons=[];						// note globals
	icons[0] = 											 4;	// units white
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black

	var map;
	var center;
	var zoom;
	var points = false;
<?php

$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";
print (($dzf==1) || ($dzf==3))? "true;\n":"false;\n";

$kml_olays = array();
$dir = "./kml_files";
$dh  = opendir($dir);
$i = 1;
$temp = explode ("/", $_SERVER['REQUEST_URI']);
$temp[count($temp)-1] = "kml_files";				//
$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
while (false !== ($filename = readdir($dh))) {
	if (!is_dir($filename)) {
	    echo "\tvar kml_" . $i . " = new GGeoXml(\"" . $server_str . $filename . "\");\n";
	    $kml_olays[] = "map.addOverlay(kml_". $i . ");";
	    $i++;
	    }
	}
//	dump ($kml_olays);
?>

function do_add_note (id) {				// 8/12/09
	var url = "add_note.php?ticket_id="+ id;
	var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
	noteWindow.focus();
	}
	
function do_track(callsign) {		
	if (parent.frames["upper"].logged_in()) {
//		if(starting) {return;}					// 6/6/08
//		starting=true;
		map.closeInfoWindow();
		var width = <?php print get_variable('map_width');?>+360;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "track_u.php?source="+callsign;

		newwindow=window.open(url, callsign,  spec);
		if (isNull(newwindow)) {
			alert ("Track display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function do track()

function do_popup(id) {					// added 7/9/09
	if (parent.frames["upper"].logged_in()) {
		map.closeInfoWindow();
		var width = <?php print get_variable('map_width');?>+32;
		var spec ="titlebar, resizable=1, scrollbars, height=590,width=" + width + ",status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "incident_popup.php?id="+id;

		newwindow=window.open(url, id, spec);
		if (isNull(newwindow)) {
			alert ("Popup Incident display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function do popup()

	var side_bar_html = "<TABLE border=0 CLASS='sidebar' WIDTH = <?php print max(320, intval($my_session['scr_width']* 0.4));?> >";
	side_bar_html += "<tr class='even'><td colspan=99 align='center'>Click for information</td></tr>";
	side_bar_html += "<tr class='odd'><td></td><td align='center'><B>Incident</B></td><td align='center'><B>Type</B></td><td>P</td><td>A</td><td align='center'>As of</td></tr>";
	var gmarkers = [];
	var fmarkers = [];
	var infoTabs = [];
	var facinfoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index

	$("show_unavail").style.display=			"none";				// 10/21/09
	$("hide_unavail").style.display=			"inline";

	map = new GMap2($("map"));		// create the map
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

	map.addControl(new GSmallMapControl());					// 8/25/08
	map.addControl(new GMapTypeControl());

	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);

	mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());		// 4/4/09

	var bounds = new GLatLngBounds();						// create  bounding box
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.enableScrollWheelZoom();

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);
	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

<?php
	$get_status = (!empty ($get_status))? $get_status : ($GLOBALS['STATUS_OPEN']) ;		 // default to show all open tickets
	$order_by =  (!empty ($get_sortby))? $get_sortby: $my_session['sortorder']; // use default sort order?
																				//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($my_session['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $my_session['ticket_per_page']))	{
		if ($_GET['offset']) {
			$limit = "LIMIT $_GET[offset],$my_session[ticket_per_page]";
			}
		else {
			$limit = "LIMIT 0,$my_session[ticket_per_page]";
			}
		}
	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$my_session[user_id]" : "";
	$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));

	switch($get_status) {				//9/29/09 Added capability for Scheduled Incidents 10/27/09 changed to bring scheduled incidents to front when due.
			case 1: $where = "WHERE `status`='1'"; break;
			case 2: $where = "WHERE `status`='2' OR (`status`='3'  AND `booked_date` <= (NOW() - INTERVAL 6 HOUR)) OR (`status`='1'  AND `problemend` >= '{$time_back}')"; break;
			case 3: $where = "WHERE `status`='3'"; break;
			default: $where = "WHERE `status`='2' OR (`status`='3'  AND `booked_date` <= (NOW() - INTERVAL 6 HOUR))"; break;
			}
	
	if ($sort_by_field && $sort_value) {					//sort by field?
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,
			UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, in_types.type AS `type`, 
			in_types.id AS `t_id` FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.`in_types_id`=in_types.id  
			WHERE $sort_by_field='$sort_value' $restrict_ticket ORDER BY $order_by";
		}
	else {					// 2/2/09, 8/12/09
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,
			UNIX_TIMESTAMP(booked_date) AS booked_date,	UNIX_TIMESTAMP(date) AS date,
			UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.updated) AS updated,
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
			`$GLOBALS[mysql_prefix]in_types`.type AS `type`, `$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, `$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`, `$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name` FROM $GLOBALS[mysql_prefix]ticket 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` 
				ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 
				ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id` 
			$where $restrict_ticket 
			ORDER BY `status` DESC, `severity` DESC, `$GLOBALS[mysql_prefix]ticket`.`id` ASC";		// 2/2/09, 10/28/09
		}
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							// major while ... starts here

	while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
	
		switch($row['status']) {				//10/27/09 to Add star to scheduled incidents on current situation screen
			case 1: $sp = ""; break;
			case 2: $sp = ""; break;
			case 3: $sp = "*"; break;
			default: $sp = ""; break;
			}
	
			print "\t\tvar scheduled = '$sp';\n";
?>
			var sym = (i+1).toString();					// for sidebar
			var sym2= scheduled + (i+1).toString();			// for icon
	
<?php
			$the_id = $row['tick_id'];		// 11/27/09
	
			if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
			if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
				if (strlen($row['tick_descr']) > get_variable('abbreviate_description')) {
					$row['tick_descr'] = substr($row['tick_descr'],0,get_variable('abbreviate_description')).'...';
					}
				}
			if (get_variable('abbreviate_affected')) {
				if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
					$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
					}
				}
			switch($row['severity'])		{		//color tickets by severity
			 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 				$severityclass='severity_normal'; break;
				}
	
			$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
			$todisp = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id={$the_id}'><U>Dispatch</U></A>";	// 8/2/08
	
			if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
				$strike = "<strike>"; $strikend = "</strike>";
				}
			else { $strike = $strikend = "";}
			$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
	
			$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . shorten($row['scope'], 48)  . "$strikend</B></TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Booked Date:</TD><TD>" . format_date($row['booked_date']) . "</TD></TR>";	//10/27/09
			$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>$street</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Receiving Facility:</TD><TD>" . shorten($row['fac_name'], 30)  . "</TD></TR>";	//10/28/09
			$utm = get_variable('UTM');
			if ($utm==1) {
				$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
				$tab_1 .= "<TR CLASS='even'><TD>UTM grid:</TD><TD>" . toUTM($coords) . "</TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><FONT SIZE='-1'>";
			$tab_1 .= 	$todisp . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Details</U></A>";		// 08/8/02
			if (!(is_guest() && get_variable('guest_add_ticket')==0)) {
				$tab_1 .= 	"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='edit.php?id=" . $the_id . $rand . "'><U>Edit</U></A><BR /><BR />";					// 10/21/08
				$tab_1 .= 	"<SPAN onClick = do_popup('" . $the_id  . "');><FONT COLOR='blue'><B><U>Popup</B></U></FONT></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 7/7/09
				$tab_1 .= 	"<SPAN onClick = 'do_add_note (" . $the_id . ");'><FONT COLOR='blue'><B><U>Add note</B></U></FONT></SPAN><BR /><BR />" ;	// 7/7/09
				
				$tab_1 .= 	"<A HREF='patient.php?ticket_id=" . $the_id . $rand ."'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";	// 7/9/09
				$tab_1 .= 	"<A HREF='action.php?ticket_id=" . $the_id . $rand ."'><U>Add Action</U></A>";
				}
			$tab_1 .= 	"</FONT></TD></TR></TABLE>";			// 11/6/08
	
	
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";	// 8/12/09
			$tab_2 .= "<TR CLASS='even'>	<TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['tick_descr']), 48) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
			$tab_2 .= "<TR CLASS='odd'>		<TD>Disposition:</TD><TD>" . shorten($row['comments'], 48) . "</TD></TR>";		// 8/13/09
	
			$locale = get_variable('locale');	// 08/03/09
			switch($locale) { 
				case "0":
				$tab_2 .= "<TR CLASS='even'>	<TD>USNG:</TD><TD>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "1":
				$tab_2 .= "<TR CLASS='even'>	<TD>OSGB:</TD><TD>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "2":
				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
				$tab_2 .= "<TR CLASS='even'>	<TD>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				default:
				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
				}
	
	//		$tab_2 .= "<TR>					<TD>&nbsp;</TD></TR>";
			$tab_2 .= "<TR>					<TD COLSPAN=2>" . show_assigns(0, $the_id) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'>	<TD COLSPAN=2 ALIGN='center'>";
			$tab_2 .= $todisp . "&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
			if (!(is_guest() && get_variable('guest_add_ticket')==0)) {
				$tab_2 .= 	"<A HREF='patient.php?ticket_id=" . $the_id . "'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
				$tab_2 .= 	"<A HREF='action.php?ticket_id=" . $the_id . "'><U>Add Action</U></A>&nbsp;&nbsp;";
				}
			$tab_2 .= 	"</TD></TR></TABLE>";		// 11/6/08
			$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `ticket_id` = " . $the_id;
			$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
			$A = mysql_affected_rows();
	
			$query= "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE `ticket_id` = " . $the_id;
			$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
			$P = mysql_affected_rows ();
	
			$sidebar_line = "<TD CLASS='$severityclass'  TITLE = '" . htmlentities ($row['scope'], ENT_QUOTES) . "'><NOBR>$strike" . $sp . shorten($row['scope'], 20) . " $strikend</NOBR></TD>";	//10/27/09
			$sidebar_line .= "<TD CLASS='$severityclass'  TITLE = '" . htmlentities ($row['type'], ENT_QUOTES) . "'><NOBR>$strike" . shorten($row['type'], 20) . " $strikend</NOBR></TD>";
			$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . $P . " </TD><TD CLASS='td_data'> " . $A . " </NOBR></TD>";
			$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . format_sb_date($row['updated']) . "</NOBR></TD>";
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $tab_2);?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
	
			var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket
			if (!(map_is_fixed)){																// 4/3/09
				bounds.extend(point);
				}
			i++;																				// step the index
			var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, 0, i, sym2);	// (point,tabs, color, id, sym) - 1/6/09, 10/21/09 added 0 for stat display to avoid conflicts with unit marker hide by unavailable status
			map.addOverlay(marker);

			var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
<?php
			if ($quick) {		// 11/27/09
?>
				do_sidebar_t_ed ("<?php print $sidebar_line;?>", i, <?php print $row['tick_id'];?>, sym);	// (sidebar, line_no, rcd_id, letter)
<?php
				}
			else {
?>
				do_sidebar ("<?php print $sidebar_line;?>", i, i, the_class)							// (instr, id, sym, myclass) - 3/3/09
<?php
				}
			if (intval($row['radius']) > 0) {
				$color= (substr($row['color'], 0, 1)=="#")? $row['color']: "#000000";		// black default
?>	
//				drawCircle(				38.479874, 				-78.246704, 						50.0, 					"#000080",						 1, 		0.75,	 "#0000FF", 					.2);
				drawCircle(	<?php print $row['lat']?>, <?php print $row['lng']?>, <?php print $row['radius']?>, "<?php print $color?>", 1, 0.75, "<?php print $color?>", .<?php print $row['opacity']?>);
<?php
				}			// end if (intval($row['radius']) 
//			dump($row);
			}				// end tickets while ($row = ...)
?>
		side_bar_html += (i>0)? "": "<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'><BR /><B>No tickets!</B><BR /><BR /></TD></TR>";

// ==========================================      RESPONDER start    ================================================
		points = false;
		i++;
		var j=0;

// {-------- START NEW --------------------------------------------------------------------------------------
<?php
	$u_types = array();												// 1/1/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
		}
	//dump($u_types);
	unset($result);

	$assigns = array();					// 08/8/3
	$tickets = array();					// ticket id's

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		$status_hide[$temp] = $row_st['hide'];
		}

	unset($result_st);

//	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle`";	//
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle` ASC, `name` ASC";	//11/20/09 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	print (mysql_affected_rows()==0)? "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center' COLSPAN=99><B>No units!</B></TD></TR>\"\n" : "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center'><B>Unit</B></TD><TD ALIGN='center' COLSPAN=2><B>Dispatch</B></TD><TD>M</TD><TD></TD></TR>\"\n" ;

	$aprs = FALSE;
	$instam = FALSE;
	$locatea = FALSE;		//7/23/09
	$gtrack = FALSE;		//7/23/09
	$glat = FALSE;		//7/23/09
	$i=0;				// counter
// =============================================================================
	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
	$utc = gmdate ("U");				// 3/25/09

	while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for RESPONDER ==========
		$got_point = FALSE;

	$name = $row['name'];			//	10/8/09
	$temp = explode("/", $name );
	$index =  (strlen($temp[count($temp) -1])<3)? substr($temp[count($temp) -1] ,0,strlen($temp[count($temp) -1])): substr($temp[count($temp) -1] ,-3 ,strlen($temp[count($temp) -1]));		
	
	print "\t\tvar sym = '$index';\n";				// for sidebar and icon 10/8/09
	
												// 2/13/09
		$todisp = (is_guest())? "": "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;";	// 08/8/02
		$toedit = (is_guest())? "" :"&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;" ;	// 10/8/08
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U>&nbsp;&nbsp;</SPAN>" ;
		$tofac = (is_guest())? "": "<A HREF='units.php?func=responder&view=true&dispfac=true&id=" . $row['id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 08/8/02


		$temp = $row['un_status_id'] ;		// 2/24/09
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
		$hide_status = (array_key_exists($temp, $status_hide))? $status_hide[$temp] : "??";				// 10/21/09
		if ($hide_status == "y") {
			$hide_unit = 1;
			} else {
			$hide_unit = 0;
			}

		if ($row['aprs']==1) {				// get most recent aprs position data
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_aprs = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$aprs_updated = $row_aprs['updated'];
			$aprs_speed = $row_aprs['speed'];
//			if (($row_aprs) && (settype($row_aprs['latitude'], "float"))) {
			if (($row_aprs) && (my_is_float($row_aprs['latitude']))) {
				echo "\t\tvar point = new GLatLng(" . $row_aprs['latitude'] . ", " . $row_aprs['longitude'] ."); // 677\n";
				$got_point = TRUE;

				}
			unset($result_tr);
			}
		else { $row_aprs = FALSE; }
//		dump($row_aprs);

		if ($row['instam']==1) {			// get most recent instamapper data
			$temp = explode ("/", $row['callsign']);			// callsign/account no. 3/22/09

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_instam = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$instam_updated = $row_instam['updated'];
			$instam_speed = $row_instam['speed'];
			if (($row_instam) && (my_is_float($row_instam['latitude']))) {											// 4/29/09
				echo "\t\tvar point = new GLatLng(" . $row_instam['latitude'] . ", " . $row_instam['longitude'] ."); // 724\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_instam = FALSE; }

		if ($row['locatea']==1) {			// get most recent locatea data		// 7/23/09
			$temp = explode ("/", $row['callsign']);			// callsign/account no.

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_locatea = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$locatea_updated = $row_locatea['updated'];
			$locatea_speed = $row_locatea['speed'];
			if (($row_locatea) && (my_is_float($row_locatea['latitude']))) {
				echo "\t\tvar point = new GLatLng(" . $row_locatea['latitude'] . ", " . $row_locatea['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_locatea = FALSE; }

		if ($row['gtrack']==1) {			// get most recent gtrack data		// 7/23/09
			$temp = explode ("/", $row['callsign']);			// callsign/account no.

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_gtrack = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$gtrack_updated = $row_gtrack['updated'];
			$gtrack_speed = $row_gtrack['speed'];
			if (($row_gtrack) && (my_is_float($row_gtrack['latitude']))) {
				echo "\t\tvar point = new GLatLng(" . $row_gtrack['latitude'] . ", " . $row_gtrack['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_gtrack = FALSE; }

		if ($row['glat']==1) {			// get most recent latitude data		// 7/23/09
			$temp = explode ("/", $row['callsign']);			// callsign/account no.

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_glat = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$glat_updated = $row_glat['updated'];
			if (($row_glat) && (my_is_float($row_glat['latitude']))) {
				echo "\t\tvar point = new GLatLng(" . $row_glat['latitude'] . ", " . $row_glat['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_glat = FALSE; }

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");	// 753\n";
			$got_point= TRUE;
			}

//		print __LINE__ . "<BR />";
		$the_bull = "";											// define the bullet
		$update_error = strtotime('now - 6 hours');				// set the time for silent setting
//		echo $update_error;
		if ($row['aprs']==1) {
			if ($row_aprs) {
				$spd = 2;										// default
				if($aprs_speed == 0) {$spd = 1;}			// stopped
				if($aprs_speed >= 50) {$spd = 3;}		// fast
				}
			else {
				$spd = 0;				// no data
				}
			$the_bull = "<FONT COLOR=" . $bulls[$spd] ."><B>AP</B></FONT>";
			}			// end aprs

		if ($row['instam']==1) {
			if ($instam_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>IN</B></FONT>";}
			if ($instam_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>IN</B></FONT>";}
			if ($instam_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>IN</B></FONT>";}
			if ($instam_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>IN</B></FONT>";}
			}

		if ($row['locatea']==1) {
			if ($locatea_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>LO</B></FONT>";}		// 7/23/09
			if ($locatea_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>LO</B></FONT>";}
			if ($locatea_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>LO</B></FONT>";}
			if ($locatea_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>LO</B></FONT>";}
			}

		if ($row['gtrack']==1) {
			if ($gtrack_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>GT</B></FONT>";}		// 7/23/09
			if ($gtrack_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>GT</B></FONT>";}
			if ($gtrack_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>GT</B></FONT>";}
			if ($gtrack_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>GT</B></FONT>";}
			}
		if ($row['glat']==1) {

			$the_bull = "<FONT COLOR = 'green'><B>GL</B></FONT>";		// 7/23/09
			if ($glat_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>GL</B></FONT>";}
			}
						// end bullet stuff
// name

		$name = $row['name'];		//	10/8/09
		$temp = explode("/", $name );
		$display_name = $temp[0];

//		$sidebar_line = "<TD TITLE = '" . htmlentities ($row['name'], ENT_QUOTES) . "'><U>" . shorten($row['name'], 24) ."</U></TD>";
		$sidebar_line = "<TD TITLE = '" . htmlentities ($display_name, ENT_QUOTES) . "'><U>" . shorten($display_name, 24) ."</U></TD>";	//	10/8/09


// assignments 3/16/09

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
			WHERE `responder_id` = '{$row['id']}' AND `clear` IS NULL ";
//		dump($query);

		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
		unset($result_as);

		switch($row_assign['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 				$severityclass='severity_normal'; break;
			}

		$tick_ct = (mysql_affected_rows()>1)? "(" .mysql_affected_rows() . ") ": "";
		$ass_td =  (mysql_affected_rows()>0)? "<TD COLSPAN=2 CLASS='$severityclass' TITLE = '" .$row_assign['scope'] . "' >" .$tick_ct . shorten($row_assign['scope'], 24) . "</TD>": "<TD>na</TD>";

		$sidebar_line .= ($row_assign)? $ass_td : "<TD COLSPAN=2>na</TD>";

// status, mobility
		$sidebar_line .= "<TD CLASS='td_data'> " . $the_bull . "</TD>";

// as of
		$strike = $strike_end = "";
		if ((($row['instam']==1) && $row_instam ) || (($row['aprs']==1) && $row_aprs ) || (($row['locatea']==1) && $row_locatea ) || (($row['gtrack']==1) && $row_gtrack ) || (($row['glat']==1) && $row_glat )) {		// either remote source?
			$the_class = "emph";
			if ($row['aprs']==1) {															// 3/24/09
				$the_time = $aprs_updated;
				$instam = TRUE;				// show footer legend
				}
			if ($row['instam']==1) {															// 3/24/09
				$the_time = $instam_updated;
				$instam = TRUE;				// show footer legend
				}
			if ($row['locatea']==1) {															// 7/23/09
				$the_time = $locatea_updated;
				$locatea = TRUE;				// show footer legend
				}
			if ($row['gtrack']==1) {															// 7/23/09
				$the_time = $gtrack_updated;
				$gtrack = TRUE;				// show footer legend
				}
			if ($row['glat']==1) {																// 7/23/09
				$the_time = $glat_updated;
				$glat = TRUE;				// show footer legend
				}
		} else {
			$the_time = $row['updated'];
			$the_class = "td_data";
		}

		if (abs($utc - $the_time) > $GLOBALS['TOLERANCE']) {								// attempt to identify  non-current values
			$strike = "<STRIKE>";
			$strike_end = "</STRIKE>";
		} else {
		$strike = $strike_end = "";
		}

//	    snap(basename( __FILE__) . __LINE__, $the_class );

		$sidebar_line .= "<TD CLASS='$the_class'> $strike" . format_sb_date($the_time) . "$strike_end</TD>";	// 6/17/08

// tab 1

		if (((my_is_float($row['lat']))) || ($row_aprs) || ($row_instam) || ($row_locatea) || ($row_gtrack) || ($row_glat)) {						// 5/4/09

			$temptype = $u_types[$row['type']];
			$the_type = $temptype[0];																	// 1/1/09

			$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
			if (array_key_exists($row['id'], $assigns)) {
				$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $tofac . $todisp . $totrack . $toedit . "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE>";

// tab 2
		$tabs_done=FALSE;
		if ($row_aprs) {		// three tabs if APRS data
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_aprs['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_aprs['course'] . ", Speed:  " . $row_aprs['speed'] . ", Alt: " . $row_aprs['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $row_aprs['closest_city'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $row_aprs['status'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_aprs['packet_date']) . " $strike_end (UTC)</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;

?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("APRS <?php print addslashes(substr($row_aprs['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			}	// end if ($row_aprs)

		if ($row_instam) {		// three tabs if instam data
//			dump(__LINE__);
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_instam['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_instam['course'] . ", Speed:  " . $row_instam['speed'] . ", Alt: " . $row_instam['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_instam['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Instam <?php print addslashes(substr($row_instam['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_instam)

		if ($row_locatea) {		// three tabs if locatea data		7/23/09
//			dump(__LINE__);
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_locatea['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_locatea['course'] . ", Speed:  " . $row_locatea['speed'] . ", Alt: " . $row_locatea['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_locatea['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("LocateA <?php print addslashes(substr($row_locatea['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_gtrack)

		if ($row_gtrack) {		// three tabs if gtrack data		7/23/09
//			dump(__LINE__);
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_gtrack['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_gtrack['course'] . ", Speed:  " . $row_gtrack['speed'] . ", Alt: " . $row_gtrack['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_gtrack['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Gtrack <?php print addslashes(substr($row_gtrack['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_gtrack)

		if ($row_glat) {		// three tabs if glat data			7/23/09
//			dump(__LINE__);
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . $row_glat['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>As of: </TD><TD> $strike " . format_date($row_glat['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("G Lat <?php print addslashes(substr($row_glat['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_gtrack)

		if (!($tabs_done)) {	// else two tabs
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			}		// end if(!($tabs_done))
		}		// end position data available

//			bottom of if/else position data

	if ((!(my_is_float($row['lat']))) || ($quick)) {		// 11/27/09
?>
		do_sidebar_u_ed ("<?php print $sidebar_line;?>", i, <?php print $row['id'];?>, sym);		// (sidebar, line_no, rcd_id, letter)
<?php
		}
	else {
		$the_color = ($row['mobile']=="1")? 0 : 4;		// icon color black, white		-- 4/18/09
?>
		var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
		do_sidebar ("<?php print $sidebar_line; ?>", i, sym, the_class);		// (instr, id, sym, myclass)

		var marker = createMarker(point, myinfoTabs, <?php print $the_color;?>, <?php print $hide_unit;?>, i, sym);	// 859  - 4/18/09, 10/21/09 added status to allow hiding of unavailable units.
		map.addOverlay(marker);

<?php
		}

	$i++;				// zero-based
	print "\t\ti++;\n"; 	// 3/20/09

	}				// end  ==========  while() for RESPONDER ==========

	$source_legend = (($aprs)||($instam)||($gtrack)||($locatea)||($glat))? "<TD CLASS='emph' ALIGN='center'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09

	print "\n\tside_bar_html+= \"<TR CLASS='\" + colors[i%2] +\"'><TD COLSPAN=5 ALIGN='center'>{$source_legend}</TD></TR>\";\n";
	print "\n\tside_bar_html+= \"<TR CLASS='\" + colors[i%2] +\"'><TD COLSPAN=99 ALIGN='center'>&nbsp;&nbsp;<B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'>&bull;</FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'>&bull;</FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'>&bull;</FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'>&bull;</FONT>&nbsp;&nbsp;</TD></TR>\";\n";

// ====================================Add Facilities to Map 8/1/09================================================
?>
	var icons=[];	
	var g=0;

	var fmarkers = [];

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);
	fac_icon.image = icons[1];

	$("hide_fac").style.display = "none";
	$("show_fac").style.display = "inline-block";

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new GMarker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
	return fac_marker;
	}

<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, `$GLOBALS[mysql_prefix]facilities`.description AS facility_description, `$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name FROM `$GLOBALS[mysql_prefix]facilities` LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

	print (mysql_affected_rows()==0)? "\n\t\tside_bar_html += \"<TD colspan='99'><TABLE ID='fac_table' STYLE='display: none'><TR><TD ALIGN='center' COLSPAN=8><BR><B>Facilities</B></TD></TR><TR CLASS='even'><TD></TD><TD ALIGN='center'><B>No Facilities!</B></TD></TR>\"\n" : "\n\t\tside_bar_html += \"<TD colspan='99'><TABLE ID='fac_table' STYLE='display: none'><TR><TD ALIGN='center' COLSPAN=8><BR><B>Facilities</B></TD></TR><TR CLASS='even'><TD></TD><TD ALIGN='center'><B>Facility</B></TD><TD ALIGN='center'><B>Type</B></TD><TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD><TD ALIGN='center'><B>Status</B></TD><TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD><TD><B>&nbsp;&nbsp;&nbsp;Updated</B></TD></TR>\"\n";
	
	while($row_fac = mysql_fetch_array($result_fac)){
	$fac_id=($row_fac['fac_id']);
	$fac_type=($row_fac['icon']);

	$fac_name = $row_fac['facility_name'];			//	10/8/09
	$fac_temp = explode("/", $fac_name );
//	$fac_index =  (strlen($temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		
	$fac_index =  (strlen($fac_temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		// 11/10/09
	
	print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
	
//	$toroute = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $the_id . "'><U>Dispatch</U></A>";	// 8/2/08
	$toroute = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $fac_id . "'><U>Dispatch</U></A>";	// 11/10/09

	if(is_guest()) {
		$facedit = $toroute = $facmail = "";
		}
	else {
		$facedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&edit=true&id=" . $row_fac['fac_id'] . "'><U>Edit</U></A>" ;
		$facmail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_mail_fac_win('" .$row_fac['fac_id']  . "');><U><B>Email</B></U></SPAN>" ;
		$toroute = "&nbsp;<A HREF='fac_routes.php?fac_id=" . $fac_id . "'><U>Route To Facility</U></A>";	// 8/2/08
		}

	if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {

		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];

		$sidebar_fac_line = "<TD TITLE = '" . addslashes($facility_display_name) . "'><U>" . addslashes(shorten($facility_display_name, 16)) ."</U></TD>";
		$sidebar_fac_line .= "<TD>&nbsp;&nbsp;&nbsp;" . addslashes(shorten($row_fac['fac_type_name'], 8)) ."</TD><TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
		$sidebar_fac_line .= "<TD>" . addslashes($row_fac['status_val']) ."</TD><TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
		$sidebar_fac_line .= "<TD>&nbsp;" . format_sb_date($row_fac['updated']) . "</TD>";

		$fac_tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
		$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
		$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
		$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'> " . format_date($row_fac['updated']) . "</TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toroute . $facedit . $facmail . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&view=true&id=" . $row_fac['fac_id'] . "'><U>View</U></A></TD></TR>";
//		$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toroute . $facedit ."&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&view=true&id=" . $row_fac['fac_id'] . "'><U>View</U></A></TD></TR>";
		$fac_tab_1 .= "</TABLE>";

		$fac_tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
		$fac_tab_2 .= "</TABLE>";
		
		?>
//		var fac_sym = (g + 1).toString();				// 11/12/09
		var myfacinfoTabs = [
			new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
			new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
			];
<?php

			echo "var fac_icon = new GIcon(baseIcon);\n";
			echo "var fac_type = $fac_type;\n";
			echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
			echo "fac_icon.image = fac_icon_url;\n";
			echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
			echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
			echo "map.addOverlay(fac_marker);\n";
			echo "\n";
?>
			if (fac_marker.isHidden()) {
				fac_marker.show();
			} else {
				fac_marker.hide();
			}
<?php
			}	// end if my_is_float
?>
		if(quick) {						// set up for facility edit - 11/27/09
			do_sidebar_fac_ed ("<?php print $sidebar_fac_line;?>", <?php print $row_fac['fac_id'];?>, fac_sym, fac_icon);		
			}
		else {					// set up for facility infowindow
			do_sidebar_fac_iw ("<?php print $sidebar_fac_line;?>", g, fac_sym, fac_icon);
			}
		g++;
<?php
	}	// end while

?>
	side_bar_html += "</TD></TABLE>\n";
<?php
//}
// =====================================End of functions to show facilities========================================================================

	for ($i = 0; $i<count($kml_olays); $i++) {				// emit kml overlay calls
		echo "\t\t" . $kml_olays[$i] . "\n";
		}
?>
	if (!(map_is_fixed)){
		if (!points) {		// any?
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);
			map.setCenter(center,zoom);
			}			// end if/else (!points)
	}				// end if (!(map_is_fixed))

<?php


//	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `problemend` IS NOT NULL ";		// 10/21/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 1 ";		// 10/21/09

		$result_ct = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_closed = mysql_num_rows($result_ct); 
		unset($result_ct);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 3 ";		// 10/21/09
		$result_scheduled = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_scheduled = mysql_num_rows($result_scheduled); 
		unset($result_scheduled);

//dump($num_closed);
//dump($num_scheduled);
//	$num_closed = 0;
//	$num_scheduled = 0;

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}

	if(empty($open)) {									// 6/9/08  added button
		print "\n\tvar current_button = \"<INPUT TYPE='button' VALUE='Current situation' onClick = 'document.to_all.submit()'>\"\n";
		print "\n\tside_bar_html+= \"<TR><TD COLSPAN=99 ALIGN='center'><BR>\" + current_button + \"</TD></TR>\";\n";
		}
	if((empty($closed)) && ($num_closed > 0)) {									// 6/9/08  added button, 10/21/09 added check for closed incidents on the database
		print "\n\tvar closed_button = \"<INPUT TYPE='button' VALUE='Closed Incidents' onClick = 'document.to_closed.submit()'>\"\n";
		print "\n\tside_bar_html+= \"<TR><TD COLSPAN=99 ALIGN='center'><BR>\" + closed_button + \"</TD></TR>\";\n";
		}
	if((empty($scheduled)) && ($num_scheduled > 0)) {								// 9/29/09  added button for scheduled incidents, 10/21/09 added check for scheduled incidents on the database
		print "\n\tvar scheduled_button = \"<INPUT TYPE='button' VALUE='Scheduled Incidents' onClick = 'document.to_scheduled.submit()'>\"\n";
		print "\n\tside_bar_html+= \"<TR><TD COLSPAN=99 ALIGN='center'><BR>\" + scheduled_button + \"</TD></TR>\";\n";
		}

?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div

<?php
	switch ($my_session['f1']) {		// persistence flags 2/14/09
		case NULL:						// default 3/23/09
		case " ":						//
		case "s":
			print "\tshow_Units();\n";
		    break;
		case "h":
			print "\thide_Units();\n";
		    break;
		default:
		    echo "error" . __LINE__ . "\n";
		}
?>


// =============================================================================================================
	}		// end if (GBrowserIsCompatible())
else {
	alert("Sorry, browser compatibility problem. Contact your tech support group.");
	}
</SCRIPT>

<?php
	}				// end function list_tickets() ===========================================================


//	} { -- dummy

function show_ticket($id,$print='false', $search = FALSE) {								/* show specified ticket */
	global $my_session, $istest;

	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}


	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$my_session[user_id]" : "";

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(booked_date) AS booked_date,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`, `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
		`rf`.`name` AS `rec_fac_name`, `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`, 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON `$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.facility 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf ON `rf`.id = `$GLOBALS[mysql_prefix]ticket`.rec_facility 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09

//dump($query);
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";	// 8/18/09
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_array($result));
//dump($row);

    $locale = get_variable('locale');    // 10/29/09
    switch($locale) {
        case "0":
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
        break;

        case "1":
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);    // 8/23/08, 10/15/08, 8/3/09
        break;
   
        case "2":
        $coords =  $row['lat'] . "," . $row['lng'];                                    // 8/12/09
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);    // 8/23/08, 10/15/08, 8/3/09
        break;

        default:
        print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
        }


	if ($print == 'true') {

		print "<TABLE BORDER='0' CLASS='print_TD' width='800px'>";
		print "<TR><TD CLASS='print_TD'><B>Incident</B>:</TD>	<TD CLASS='print_TD'>" . $row['scope'].	"&nbsp;&nbsp;<I>(#" . $row['tick_id'] . ")</I></TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Priority:</B></TD>	<TD CLASS='print_TD'>" . get_severity($row['severity']);
		print  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>Nature:</B>&nbsp;&nbsp;{$row['type']}</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Protocol:</B>:</TD>	<TD CLASS='print_TD'>{$row['protocol']}</TD></TD></TR>\n";		// 7/16/09
		print "<TR><TD CLASS='print_TD'><B>Written</B>:</TD>	<TD CLASS='print_TD'>" . format_date($row['date']) . "</TD></TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Updated</B>:</TD>	<TD CLASS='print_TD'>" . format_date($row['updated']) . "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Reported by</B>:</TD><TD CLASS='print_TD'>" . $row['contact'].	"</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Phone</B>:</TD>		<TD CLASS='print_TD'>" . format_phone($row['phone']) ."</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Status:</B></TD>		<TD CLASS='print_TD'>" . get_status($row['status'])."</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Scheduled date:</B></TD>		<TD CLASS='print_TD'>" . format_date($row['booked_date']) . "</TD></TR>\n";	// 10/6/09
		print "<TR><TD CLASS='print_TD' COLSPAN='2'></TD></TR>\n";

		print "<TR><TD CLASS='print_TD'><B>Address</B>:</TD>	<TD CLASS='print_TD'>" . $row['street']. "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>City</B>:</TD>		<TD CLASS='print_TD'>" . $row['city']. "&nbsp;&nbsp;&nbsp;&nbsp;<B>St</B>: " . $row['state'] . "</TD></TR>\n";
		print "<TR VALIGN='top'><TD CLASS='print_TD'><B>Description:</B></TD>	<TD>" .  nl2br($row['tick_descr']) . "</TD></TR>\n";	//	8/12/09
		print "<TR><TD CLASS='print_TD'><B>Facility:</B></TD>		<TD CLASS='print_TD'>" . $row['fac_name']."</TD></TR>\n";	// 8/1/09
		print "<TR><TD CLASS='print_TD'><B>Receiving Facility:</B></TD>		<TD CLASS='print_TD'>" . $row['rec_fac_name']."</TD></TR>\n";	// 10/6/09
		print "<TR><TD CLASS='print_TD'><B>Disposition:</B></TD><TD CLASS='print_TD'>" . nl2br ($row['comments']). "</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Owner:</B></TD>		<TD CLASS='print_TD'>" . get_owner($row['owner']). "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Issued:</B></TD>		<TD CLASS='print_TD'>" . format_date($row['date']). "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Run Start:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemstart']). "</TD></TR>";
		print "<TR><TD CLASS='print_TD'><B>Run End:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemend']).	"</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Affected:</B></TD>	<TD CLASS='print_TD'>" . $row['affected']. "</TD></TR>\n"; */

		print "<TR><TD CLASS='print_TD'><B>Position:</B></TD>	<TD CLASS='print_TD'>" . get_lat($row['lat']) . ", " .  get_lng($row['lng']) . "&nbsp;&nbsp;&nbsp;&nbsp;" . $grid_type . "</TD></TR>\n"; 		// 9/13/08
		print "</TABLE>\n";

		print show_actions($row['tick_id'], "date", FALSE, FALSE);		// lists actions and patient data, print - 10/30/09

// =============== 10/30/09 

		function my_to_date($in_date) {			// date_time format to user's spec
			$temp = mktime(substr($in_date,11,2),substr($in_date,14,2),substr($in_date,17,2),substr($in_date,5,2),substr($in_date,8,2),substr($in_date,0,4));
			return (good_date_time($in_date)) ?  date(get_variable("date_format"), $temp): "";		// 
			}
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `facility_id` IS NOT NULL LIMIT 1";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$facilities = mysql_affected_rows()>0;		// set boolean in order to avoid waste space

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `start_miles` IS NOT NULL  LIMIT 1";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$miles = mysql_affected_rows()>0;		// set boolean in order to avoid waste space
		unset($result_temp);

		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
			`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
			`t`.`description` AS `thetickdescr`, `t`.`status` AS `thestatus`, `r`.`id` AS `theunitid`, `r`.`name` AS `theunit` ,
			`f`.`name` AS `thefacility`, `g`.`name` AS `the_rec_facility`, `$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
			FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`$GLOBALS[mysql_prefix]assigns`.`facility_id` = `f`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `g` ON (`$GLOBALS[mysql_prefix]assigns`.`rec_facility_id` = `g`.`id`)
			WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = $id
			ORDER BY `theunit` ASC ";																// 5/25/09, 1/16/08

//		dump($query );
	
		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_affected_rows()>0) {
			print "<P><TABLE  CLASS='print_TD' BORDER = 1 CELLPADDING = 2 STYLE = 'border-collapse: collapse;'>\n";
			print "<TR><TH>Unit</TH><TH>D</TH><TH>R</TH><TH>E</TH>";
			print ($facilities)? "<TH>FE</TH><TH>FA</TH>": "";
			print "<TH>C</TH>";
			print ($miles)? "<TH>M/S</TH><TH>M/E</TH>": "";
			print "</TR>";
			
			while ( $asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result))){
				print "<TR>";			
				print "<TD>" . shorten($asgn_row['theunit'], 24) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['dispatched']) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['responding']) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['on_scene']) . "</TD>";
				print ($facilities)? "<TD>" . my_to_date($asgn_row['u2fenr']) . "</TD>": "";
				print ($facilities)? "<TD>" . my_to_date($asgn_row['u2farr']) . "</TD>": "";
				print "<TD>" . my_to_date($asgn_row['clear']) . "</TD>";
				print ($miles)? "<TD>" . my_to_date($asgn_row['start_miles']) . "</TD>": "";
				print ($miles)? "<TD>" . my_to_date($asgn_row['end_miles']) . "</TD>": "";
				print "</TR>\n";				
				}		// end while () $asgn_row = ...
			print "</TABLE>\n";
			}				// end if (mysql_affected_rows()>0 
		
// ==============

		print "\n</BODY>\n</HTML>";
		return;
		}		// end if ($print == 'true')
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">
	<TR VALIGN="top"><TD CLASS="print_TD" ALIGN="left">
<?php
	print do_ticket($row, max(320, intval($my_session['scr_width']* 0.4)), $search) ;				// 2/25/09
	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		<BR /><SPAN ID='grid_id' onClick='doGrid()'><U>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<SPAN ID='do_sv' onClick = 'sv_win(document.sv_form)' ><u>Street view</U></SPAN>
		</TD></TR>";	// 11/29/08

	print "<FORM NAME='sv_form' METHOD='post' ACTION=''><INPUT TYPE='hidden' NAME='frm_lat' VALUE=" .$row['lat'] . ">";		// 2/11/09
	print "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=" .$row['lng'] . "></FORM>";

	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n
		<TR><TD ALIGN='center'><BR /><FONT SIZE='-1'>Click map point for distance information.</FONT></TD></TR>\n";
	print "</TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];

	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "</TD></TR>\n";
//	print "<TR><TD ALIGN='left'>";
//	print show_log ($id);				// log as a table
//	print "</TD></TR></TABLE>\n";
	print "</TABLE>\n";


?>
<!--	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT> 10/14/08 -->
	<SCRIPT>
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

//	function do_sv(lat, lng) {				// open streetview window - 11/29/08
//		newwindow_sv=window.open("streetview.php?lat=" + lat + "&lng=" + lng, "streetview",  "titlebar, resizable=1, scrollbars, height=480,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
//		if (isNull(newwindow_sv)) {
//			alert ("StreetView operation requires popups to be enabled. Please adjust your browser options.");
//			return;
//			}
//		newwindow_sv.focus();
//		}		// end function do_sv()

	var starting = false;

	function sv_win(theForm) {				// 2/11/09
		if(starting) {return;}				// dbl-click proof
		starting = true;

		var thelat = theForm.frm_lat.value;
		var thelng = theForm.frm_lng.value;
		var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
		newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_sl)) {
			alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_sl.focus();
		starting = false;
		}		// end function sv win()

	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			grid = false;
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			grid = true;
			}
		}

	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW

		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();

		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;
	var thisMarker = false;

	var map;
	var icons=[];						// note globals	- 1/29/09
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./icons/blue.png";		// normal
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./icons/green.png";	// green
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./icons/red.png";		// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./icons/white.png";	// white - not in use

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
//	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
//	baseIcon.infoShadowAnchor = new GPoint(18, 25);

	map = new GMap2($("map"));		// create the map
<?php
$maptype = get_variable('maptype');	// 08/02/09

	switch($maptype) { 
		case "1":
		break;

		case "2":
?>
		map.setMapType(G_SATELLITE_MAP);
<?php
		break;
	
		case "3":
?>
		map.setMapType(G_PHYSICAL_MAP);
<?php
		break;
	
		case "4":
?>
		map.setMapType(G_HYBRID_MAP);
<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());				// 12/24/08
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>
	map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>),14);
	var icon = new GIcon(baseIcon);
	icon.image = icons[<?php print $row['severity'];?>];
	var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1147
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom();

// ====================================Add Responding Units to Map 8/1/09================================================

	var icons=[];	
	icons[1] = "./icons/white.png";		// normal
	icons[2] = "./icons/black.png";	// green

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var unit_icon = new GIcon(baseIcon);
	unit_icon.image = icons[1];

function createMarker(unit_point, number) {		// Show this markers index in the info window when clicked
	var unit_marker = new GMarker(unit_point, unit_icon);	
	var html = number;
	GEvent.addListener(unit_marker, "click", function() {unit_marker.openInfoWindowHtml(html);});
	return unit_marker;
	}


<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
	$responder_id=($row['responder_id']);
	if ($row['clear'] == NULL) {

		$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
		$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row_unit = mysql_fetch_array($result_unit)){
		$unit_id=($row_unit['id']);
		$mobile=($row_unit['mobile']);
		if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {

		if ($mobile == 1) {
			echo "var unit_icon = new GIcon(baseIcon);\n";
			echo "var unit_icon_url = \"./icons/gen_icon.php?blank=0&text=RU\";\n";
			echo "unit_icon.image = unit_icon_url;\n";
			echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
			echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
			echo "map.addOverlay(unit_marker);\n";
			echo "\n";
		} else {
			echo "var unit_icon = new GIcon(baseIcon);\n";
			echo "var unit_icon_url = \"./icons/gen_icon.php?blank=4&text=RU\";\n";
			echo "unit_icon.image = unit_icon_url;\n";
			echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
			echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
			echo "map.addOverlay(unit_marker);\n";
			echo "\n";
		}	// end inner if
		}	// end middle if
		}	// end outer if
		}	// end inner while
	}	//	end outer while

// =====================================End of functions to show responding units========================================================================
// ====================================Add Facilities to Map 8/1/09================================================
?>

	var icons=[];	
	var g=0;

	var fmarkers = [];

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);
	fac_icon.image = icons[1];

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new GMarker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
	return fac_marker;
}


<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, `$GLOBALS[mysql_prefix]facilities`.description AS facility_description, `$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name FROM `$GLOBALS[mysql_prefix]facilities` LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	while($row_fac = mysql_fetch_array($result_fac)){

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){

	$fac_name = $row_fac['facility_name'];			//	10/8/09
	$fac_temp = explode("/", $fac_name );
	$fac_index =  (strlen($fac_temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		
	
	print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09

	$fac_id=($row_fac['id']);
	$fac_type=($row_fac['icon']);

	$f_disp_name = $row_fac['facility_name'];		//	10/8/09
	$f_disp_temp = explode("/", $f_disp_name );
	$facility_display_name = $f_disp_temp[0];

	if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {

		$fac_tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
		$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
		$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
		$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
		$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date($row_fac['updated']) . "</TD></TR>";
		$fac_tab_1 .= "</TABLE>";

		$fac_tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
		$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
		$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
		$fac_tab_2 .= "</TABLE>";
		
?>
//		var fac_sym = (g+1).toString();
		var myfacinfoTabs = [
			new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
			new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
			];
<?php

			echo "var fac_icon = new GIcon(baseIcon);\n";
			echo "var fac_type = $fac_type;\n";
			echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
			echo "fac_icon.image = fac_icon_url;\n";
			echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
			echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
			echo "map.addOverlay(fac_marker);\n";
			echo "\n";
		}	// end if my_is_float

?>
		g++;
<?php
	}	// end while

}
// =====================================End of functions to show facilities========================================================================

//	$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;  2/21/09

//	$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
//	$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
//	$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $street . " </TD></TR>";
//	$tab_1 .= "</TABLE>";		// 11/6/08

	do_kml();			// kml functions

?>
//	map.openInfoWindowHtml(point, "<?php // print $tab_1;?>");

	GEvent.addListener(map, "click", function(marker, point) {
		if (point) {
			var baseIcon = new GIcon();
			baseIcon.iconSize=new GSize(32,32);
			baseIcon.iconAnchor=new GPoint(16,16);
			var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);		// 10/13/08

			map.clearOverlays();
			var thisMarker = new GMarker(point, cross);
			map.addOverlay(thisMarker);
			$("newlat").innerHTML = point.lat().toFixed(6);
			$("newlng").innerHTML = point.lng().toFixed(6);

			var nlat = $("newlat").innerHTML ;
			var nlng = $("newlng").innerHTML ;
			var olat = $("oldlat").innerHTML ;
			var olng = $("oldlng").innerHTML ;

			var km=distCosineLaw(parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng));
			var dist = ((km * km2feet).toFixed(0)).toString();
			var dist1 = dist/5280;
			var dist2 = (dist>5280)? ((dist/5280).toFixed(2) + " mi") : dist + " ft" ;

			$("range").innerHTML	= dist2;
			$("brng").innerHTML	= (brng (parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng)).toFixed(0)) + ' degr';
			$("newusng").innerHTML= LLtoUSNG(nlat, nlng, 5);
			$("pointl1").style.display = "block";
			$("pointl2").style.display = "block";

			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1196
			map.addOverlay(new GMarker(point, icon));
			var polyline = new GPolyline([
			    new GLatLng(nlat, nlng),
			    new GLatLng(olat, olng)
				], "#FF0000", 2);
			map.addOverlay(polyline);
			}
		} )

	function lat2ddm(inlat) {				// 9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}

	function lng2ddm(inlng) {
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}


	function do_coords(inlat, inlng) {  //9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
		alert(str);
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

	</SCRIPT>
<?php

	}				// end function show_ticket() =======================================================
//	} {		-- dummy

function do_ticket($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table

	global $my_session;
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0'ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>Incident: <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Priority:</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nature:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Protocol:</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	
	$print .= "<TR CLASS='even'><TD ALIGN='left'>Written:</TD>		<TD ALIGN='left'>" . format_date($theRow['date']) . "</TD></TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Updated:</TD>		<TD ALIGN='left'>" . format_date($theRow['updated']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD ALIGN='left'>Reported by:</TD>	<TD ALIGN='left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Phone:</TD>			<TD ALIGN='left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD ALIGN='left'>Status:</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09

	$print .= "<TR CLASS='even' ><TD ALIGN='left' COLSPAN='2'>&nbsp;	<TD ALIGN='left'></TR>\n";			// separator
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Address:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>City:</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['city']);
	$print .=	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;" . highlight($search, $theRow['state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Incident at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09

	$print .= "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>Description:</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['tick_descr'])) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>Disposition:</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>Run Start:</TD>					<TD ALIGN='left'>" . format_date($theRow['problemstart']);
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($theRow['problemend']) . "</TD></TR>\n";

	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD ALIGN='left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>Position</U>: </TD>
		<TD ALIGN='left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08

	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow[0]);				// log
	$print .="</TD></TR>";

	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
	$print .= "</TABLE>\n";

	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09
	$print .= show_actions($theRow[0], "date", FALSE, FALSE);

	return $print;
	}		// end function do_ticket(


//	} -- dummy

function popup_ticket($id,$print='false', $search = FALSE) {								/* 7/9/09 - show specified ticket */
	global $my_session, $istest;

	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$my_session[user_id]" : "";

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id' $restrict_ticket";	// 8/12/09

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">No such ticket or user access to ticket is denied</FONT>";
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">
<?php

	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		</TD></TR>";	// 11/29/08

	print "<FORM NAME='sv_form' METHOD='post' ACTION=''><INPUT TYPE='hidden' NAME='frm_lat' VALUE=" .$row['lat'] . ">";		// 2/11/09
	print "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=" .$row['lng'] . "></FORM>";

	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n";
	print "</TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];
	print "</TABLE>\n";


?>
<!--	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT> 10/14/08 -->
	<SCRIPT>
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			grid = false;
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			grid = true;
			}
		}

	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW

		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();

		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;
	var thisMarker = false;

	var map;
	var icons=[];						// note globals	- 1/29/09
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./icons/blue.png";		// normal
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./icons/green.png";	// green
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./icons/red.png";		// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./icons/white.png";	// white - not in use

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	map = new GMap2($("map"));		// create the map
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
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());				// 12/24/08
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>
	map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>),11);
	var icon = new GIcon(baseIcon);
	icon.image = icons[<?php print $row['severity'];?>];
	var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1147
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom();

// ====================================Add Active Responding Units to Map =========================================================================
	var icons=[];						// note globals	- 1/29/09
	icons[1] = "./icons/white.png";		// normal
	icons[2] = "./icons/black.png";	// green

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var unit_icon = new GIcon(baseIcon);
	unit_icon.image = icons[1];

function createMarker(unit_point, number) {
	var unit_marker = new GMarker(unit_point, unit_icon);
	// Show this markers index in the info window when it is clicked
	var html = number;
	GEvent.addListener(unit_marker, "click", function() {unit_marker.openInfoWindowHtml(html);});
	return unit_marker;
}


<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
		$responder_id=($row['responder_id']);
		if ($row['clear'] == NULL) {
	
			$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id=($row_unit['id']);
				$mobile=($row_unit['mobile']);
				if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {
			
					if ($mobile == 1) {
						echo "var unit_icon = new GIcon(baseIcon);\n";
						echo "var unit_icon_url = \"./icons/gen_icon.php?blank=0&text=RU\";\n";						// 4/18/09
						echo "unit_icon.image = unit_icon_url;\n";
						echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
						echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
						echo "map.addOverlay(unit_marker);\n";
						echo "\n";
					} else {
						echo "var unit_icon = new GIcon(baseIcon);\n";
						echo "var unit_icon_url = \"./icons/gen_icon.php?blank=4&text=RU\";\n";						// 4/18/09
						echo "unit_icon.image = unit_icon_url;\n";
						echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
						echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
						echo "map.addOverlay(unit_marker);\n";
						echo "\n";
						}	// end if/else ($mobile)
					}	// end ((my_is_float()) - responding units
				}	// end outer if
			}	// end inner while
		}	//	end outer while

// =====================================End of functions to show responding units========================================================================
// ====================================Add Facilities to Map 8/1/09================================================
?>
	var icons=[];	
	var g=0;

	var fmarkers = [];

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);
	fac_icon.image = icons[1];

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new GMarker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
	return fac_marker;
}


<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, `$GLOBALS[mysql_prefix]facilities`.description AS facility_description, `$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name FROM `$GLOBALS[mysql_prefix]facilities` LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	while($row_fac = mysql_fetch_array($result_fac)){

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){
	
		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index =  (strlen($temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		
		
		print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
	
		$fac_id=($row_fac['id']);
		$fac_type=($row_fac['icon']);
	
		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];
	
		if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {

			$fac_tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date($row_fac['updated']) . "</TD></TR>";
			$fac_tab_1 .= "</TABLE>";

			$fac_tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
			$fac_tab_2 .= "</TABLE>";
			
			?>
//			var fac_sym = (g+1).toString();
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
			<?php

			echo "var fac_icon = new GIcon(baseIcon);\n";
			echo "var fac_type = $fac_type;\n";
			echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
			echo "fac_icon.image = fac_icon_url;\n";
			echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
			echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
			echo "map.addOverlay(fac_marker);\n";
			echo "\n";
		}	// end if my_is_float - facilities

?>
		g++;
<?php
	}	// end while

}
// =====================================End of functions to show facilities========================================================================
	do_kml();			// kml functions

?>
	function lat2ddm(inlat) {				// 9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}

	function lng2ddm(inlng) {
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}


	function do_coords(inlat, inlng) {  //9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
		alert(str);
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

	</SCRIPT>
<?php
	}				// end function popup_ticket() =======================================================
