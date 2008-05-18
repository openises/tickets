<?php 
error_reporting(E_ALL);
require_once('functions.inc.php');
do_login(basename(__FILE__));

if ($istest) {
	dump ($_GET);
	dump ($_POST);
	}
extract($_GET);
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT>
		function ck_frames() {
			if(self.location.href==parent.location.href) {
				self.location.href = 'index.php';
				}
			}		// end function ck_frames()

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && document.getElementById) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}
		
	function to_routes(id) {
//		alert (id);
		document.routes_Form.ticket_id.value=id;
		document.routes_Form.submit();
		}
	
	function whatBrows() {					//Displays the generic browser type
		window.alert("Browser is : " + type);
		}
	
	function ShowLayer(id, action){												// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("document.getElementById('" + id + "').style.display='" + action + "'");
		}
	
	function hideit (elid) {
		ShowLayer(elid, "none");
		}
	
	function showit (elid) {
		ShowLayer(elid, "block");
		}

	function validate_cen(theForm) {			// Map center  validation	
		var errmsg="";
		if (theForm.frm_lat.value=="")			{errmsg+="\tMap center is required.\n";}
		if (theForm.frm_map_caption.value=="")	{errmsg+="\tMap caption is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.frm_lat.disabled = false;
			theForm.frm_lng.disabled = false;
			theForm.frm_zoom.disabled = false;
			return true;
			}
		}				// end function validate(theForm)

	function validate_res(theForm) {			// Responder form contents validation	
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				if(confirm("Please confirm removing this Unit")) 	{return true;}
				else 													{return false;}
				}
			}
		var errmsg="";
		var got_type = false;
		for (i=0; i<theForm.frm_type.length; i++){
			if (theForm.frm_type[i].checked) {	got_type = true;	}
			}
		if (theForm.frm_name.value=="")				{errmsg+="\tUnit NAME is required.\n";}
		if (theForm.frm_descr.value=="")			{errmsg+="\tUnit DESCRIPTION is required.\n";}
		if (theForm.frm_un_status_id.value==0)		{errmsg+="\tUnit STATUS is required.\n";}
		if (!got_type)								{errmsg+="\tUnit TYPE is required.\n";}
		if (!theForm.frm_mobile.checked) {		// fixed
			theForm.frm_lat.disabled=false;
			if (theForm.frm_lat.value == "") 		{errmsg+= "\tMAP LOCATION is required\n";}
			theForm.frm_lat.disabled=true;
			}
		else {										// mobile
			if (theForm.frm_callsign.value=="")		{errmsg+="\tCALLSIGN is required.\n";}
			else {									// not empty
				for (i=0; i< calls.length;i++) {	// duplicate?
					if (calls[i] == theForm.frm_callsign.value) {
						errmsg+="\tDuplicate CALLSIGN - not permitted.\n";
						break;
						}			
					}		// end for (...)
				}			// end else {}
			}				// end mobile
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.frm_lat.disabled = false;
			theForm.frm_lng.disabled = false;
			return true;
			}
		}				// end function validate(theForm)

	function validate_user(theForm) {			// Responder form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				if(confirm("Please confirm removing this Unit")) 	{return true;}
				else 														{return false;}
				}
			}
		var errmsg="";
		var got_level = false;
		for (i=0; i<theForm.frm_level.length; i++){
			if (theForm.frm_level[i].checked) {	got_level = true;	}
			}
		if (theForm.frm_user.value=="")				{errmsg+="\tUserID is required.\n";}
		if (theForm.frm_passwd.value=="")			{errmsg+="\tPASSWORD is required.\n";}
		if (theForm.frm_passwd_confirm.value=="")	{errmsg+="\tCONFIRM PASSWORD is required.\n";}
		if (theForm.frm_passwd.value!=theForm.frm_passwd_confirm.value) {errmsg+="\tPASSWORD and CONFIRM PASSWORD fail to match.\n";}
		if (!got_level)								{errmsg+="\tUser LEVEL is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			return true;
			}
		}				// end function validate(theForm)

	function validate_set(theForm) {			// limited form contents validation  
		var errmsg="";
		if (theForm.gmaps_api_key.value.length!=86)			{errmsg+= "\tInvalid GMaps API key\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			return true;
			}
		}				// end function validate(theForm)

	function add_res () {		// turns on add responder form
		showit('res_add_form'); 
		hideit('tbl_responders');
		hideIcons();			// hides responder icons
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
		
	function hideIcons() {
		map.clearOverlays();
		}				// end function hideicons() 

	function do_lat (lat) {
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_lng.value=lng.toFixed(6);
		document.forms[0].frm_lng.disabled=true;
		}
	
	function collect(){				// constructs a string of id's for deletion
		var str = sep = "";
		for (i=0; i< document.del_Form.elements.length; i++) {
			if (document.del_Form.elements[i].type == 'checkbox' && (document.del_Form.elements[i].checked==true)) {
				str += (sep + document.del_Form.elements[i].name.substring(1));		// drop T
				sep = ",";
				}
			}
		document.del_Form.idstr.value=str;	
		}
		
	function all_ticks(bool_val) {									// set checkbox = true/false
		for (i=0; i< document.del_Form.elements.length; i++) {
			if (document.del_Form.elements[i].type == 'checkbox') {
				document.del_Form.elements[i].checked = bool_val;		
				}
			}			// end for (...)
		}				// end function all_ticks()
		
	</SCRIPT>
	

<?php
function list_responders($addon = '', $start) {
global $my_session;
?>
<SCRIPT>

var color=0;
	var colors = new Array ('odd', 'even');

	function hideGroup(color) {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].id == color) {
					gmarkers[i].show();
					}
				else {
					gmarkers[i].hide();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "visible";
		}			// end function

	function showAll() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "hidden";

		}			// end function

	function createMarker(point,tabs, color, id) {				// Creates marker and sets up click event infowindow
		points = true;											// at least one
		var icon = new GIcon(listIcon);
		icon.image = icons[color] + ((id % 100)+1) + ".png";	// e.g.,marker9.png, 100 icons limit
		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide - unused

		GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();
			marker.openInfoWindowTabsHtml(infoTabs[id]);
			var dMapDiv = document.getElementById("detailmap");
			var detailmap = new GMap2(dMapDiv);
			detailmap.addControl(new GSmallMapControl());
			detailmap.setCenter(point, 13);  					// larger # = closer
			detailmap.addOverlay(marker);
			});

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		bounds.extend(point);									// extend the bounding box
		return marker;
		}				// end function create Marker()
		
	function do_sidebar (sidebar, id) {
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (id+1) + ". "+ sidebar +"</TD></TR>\n";
		}

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (line_no+1) + ". "+ sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		GEvent.trigger(gmarkers[id], "click");
		}

	function doGrid() {
		map.addOverlay(new LatLonGraticule());
		}

	function do_lat (lat) {
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_lng.value=lng.toFixed(6);
		document.forms[0].frm_lng.disabled=true;
		}

	var icons=[];						// note globals
	icons[1] = "./markers/YellowIcons/marker";		// e.g.,marker9.png
	icons[2] = "./markers/RedIcons/marker";			// BlueIcons/GreenIcons/YellowIcons/RedIcons/WhiteIcons
	icons[3] = "./markers/BlueIcons/marker";		// see GLOBALS re ordering
	icons[4] = "./markers/GreenIcons/marker";
	icons[5] = "./markers/WhiteIcons/marker";

	var map;
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
	side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Units</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for details - or to dispatch</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Description</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var points = false;								// none

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>

	var bounds = new GLatLngBounds();						// create  bounding box
	map.addControl(new GOverviewMapControl());
	map.enableScrollWheelZoom(); 	

	var listIcon = new GIcon();
	listIcon.image = "./markers/yellow.png";	// yellow.png - 16 X 28
	listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new GSize(20, 34);
	listIcon.shadowSize = new GSize(37, 34);
	listIcon.iconAnchor = new GPoint(8, 28);
	listIcon.infoWindowAnchor = new GPoint(9, 2);
	listIcon.infoShadowAnchor = new GPoint(18, 25);

	var newIcon = new GIcon();
	newIcon.image = "./markers/white.png";	// yellow.png - 20 X 34
	newIcon.shadow = "./markers/shadow.png";
	newIcon.iconSize = new GSize(20, 34);
	newIcon.shadowSize = new GSize(37, 34);
	newIcon.iconAnchor = new GPoint(8, 28);
	newIcon.infoWindowAnchor = new GPoint(9, 2);
	newIcon.infoShadowAnchor = new GPoint(18, 25);

	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

	GEvent.addListener(map, "click", function(marker, point) {
//		if (marker) {
//			document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=false;
//			document.forms[0].frm_lat.value=document.forms[0].frm_lng.value="";
//			document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=true;
//			}
//		if (point) {				// new - ADD
//			myZoom = map.getZoom();
//			map.clearOverlays();
//			do_lat (point.lat())							// display
//			do_lng (point.lng())
//			map.setCenter(point, myZoom);		// panTo(center)
//			map.panTo(point);				// panTo(center)
//			if (document.forms[0].frm_zoom) {				// get zoom?
//				document.forms[0].frm_zoom.disabled = false;
//				document.forms[0].frm_zoom.value = myZoom;
//				document.forms[0].frm_zoom.disabled = true;
//				}
//			marker = new GMarker(point, {icon: newIcon, draggable:true});
//			map.addOverlay(marker);
//
//			}
		});				// end GEvent.addListener() "click"

<?php

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$types = array();	$types[$GLOBALS['TYPE_EMS']] = "EMS";	$types[$GLOBALS['TYPE_FIRE']] = "Fire";
						$types[$GLOBALS['TYPE_COPS']] = "Police";	$types[$GLOBALS['TYPE_MUTU']] = "Mutual";	$types[$GLOBALS['TYPE_OTHR']] = "Other";

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);
	
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//	dump (mysql_affected_rows());
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  while() for RESPONDER ==========
	
		$toedit = (is_guest())? "" : "<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
		if ($row['mobile']==0) {							// for fixed units
			$mode = ($row['lat']==0)? 4 :  0;				// toss invalid lat's
?>
		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// mobile position

<?php
			}			// end fixed
		else {			// is mobile, do infowin
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if (mysql_affected_rows()>0) {		// got track stuff. do tab 2 and 3
				$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));
				$mode = ($rowtr['speed'] == 0)? 1: 2 ;
				if ($rowtr['speed'] >= 50) { $mode = 3;}
?>
				var point = new GLatLng(<?php print $rowtr['latitude'];?>, <?php print $rowtr['longitude'];?>);	// mobile position
<?php
				}				// end got tracks 
			else {				// no track data, do sidebar only
				$mode = 4;			
				}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end mobile
//										common to all modes
		$the_bull = ($mode == 0)? "" : "<FONT COLOR=" . $bulls[$mode] ."><B>&bull;</B></FONT>";
			
		$sidebar_line = "<TD>" . shorten($row['name'], 30) . "</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 16) . "</TD>"; 
		$temp = $row['un_status_id'];

		$sidebar_line .= "<TD CLASS='td_data'> " . shorten($status_vals[$temp], 10) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . format_sb_date($row['updated']) . "</TD>";

		print "\tvar do_map = true;\n";		// default

		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$temp] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>Details:&nbsp;&nbsp;&nbsp;&nbsp;" . $toedit . "<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "</TABLE>";

		switch ($mode) {
			case 0:				// not mobile
?>			
				do_sidebar ("<?php print $sidebar_line; ?>", i);
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
			    break;
			case 1:				// stopped
			case 2:				// moving
			case 3:				// fast
?>			
				do_sidebar ("<?php print $sidebar_line; ?>", i);
<?php			
				$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $rowtr['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $rowtr['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $rowtr['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($rowtr['packet_date']) . "</TD></TR>";
				$tab_2 .= "</TABLE>";
?>

				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("<?php print $rowtr['source']; ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php			
			    break;
			case 4:				// mobile - no track
?>
				do_sidebar_nm ("<?php print $sidebar_line; ?>", i, <?php print $row['id'];?>);	// special sidebar link - adds id for view
				var do_map = false;
<?php			
			    break;
			default:
			    echo "mode error: $mode";
			    break;
			}		// end switch
?>
			if (do_map) {
				var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i);	// (point,tabs, color, id)
				map.addOverlay(marker);
				}
			i++;				// zero-based
<?php

		}				// end  ==========  while() for RESPONDER ==========
?>
	if (!points) {		// any?
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds)-1;
		map.setCenter(center,zoom);
		}
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=6>&nbsp;</TD></TR>";
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
</SCRIPT>
<?php
	}				// end function list_responders() ===========================================================
	
function map($mode) {				// RESPONDER ADD AND EDIT
	global $row;
			if (($mode=="a") || (empty($row['lat']))) 	{$lat = get_variable('def_lat'); $lng = get_variable('def_lng'); $gotpt=FALSE;}
			else 										{$lat = $row['lat']; $lng = $row['lng']; $gotpt=TRUE;}
?>

<SCRIPT>
//
		function writeConsole(content) {
			top.consoleRef=window.open('','myconsole',
				'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=0' +',resizable=1')
		 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
				+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></HTML>'
				)				// end top.consoleRef.document.writeln()
		 	top.consoleRef.document.close();
			}				// end function writeConsole(content)
		
		function map_reset() {
			map.clearOverlays();
			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
			map.setCenter(point, <?php print get_variable('def_zoom');?>);
			map.addOverlay(new GMarker(point, myIcon));		
			}
		function map_cen_reset() {				// reset map center icon
			map.clearOverlays();
			}
		
		var map = new GMap2(document.getElementById('map'));
								// note globals
		var myZoom;
		var marker;
		
		var myIcon = new GIcon();
		myIcon.image = "./markers/yellow.png";	
		myIcon.shadow = "./markers/sm_shadow.png";
		myIcon.iconSize = new GSize(16, 28);
		myIcon.shadowSize = new GSize(22, 20);
		myIcon.iconAnchor = new GPoint(8, 28);
		myIcon.infoWindowAnchor = new GPoint(5, 1);
		
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
		map.addControl(new GOverviewMapControl());
		var tab1contents;				// info window contents - first/only tab
										// default point - possible dummy
		map.setCenter(new GLatLng(<?php print $lat; ?>, <?php print $lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom
		map.enableScrollWheelZoom(); 	

<?php
		if ($gotpt) 	{		// got a location?
?>		
	 		var point = new GLatLng(<?php print $row['lat'] . ", " . $row['lng']; ?>);
			var marker = new GMarker(point, {icon: myIcon, draggable:true});
//			map.addOverlay(marker);
	 		map.addOverlay(new GMarker(point, myIcon));
			GEvent.addListener(marker, "dragend", function() {
//				alert (780);
				var point = marker.getPoint();
				map.panTo(point);
				});		// end GEvent.addListener "dragend"
<?php
			}
		if (!((isset ($mode)) && ($mode=="v"))) {	// disallow if view mode
?>

		GEvent.addListener(map, "click", function(marker, point) {
			if (marker) {
				map.removeOverlay(marker);
				document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=false;
				document.forms[0].frm_lat.value=document.forms[0].frm_lng.value="";
				document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=true;
				}
			if (point) {
				myZoom = map.getZoom();
				map.clearOverlays();
				do_lat (point.lat())							// display
				do_lng (point.lng())
//				map.setCenter(point, myZoom);		// panTo(center)
				map.panTo(point);				// panTo(center)
				if (document.forms[0].frm_zoom) {				// get zoom?
					document.forms[0].frm_zoom.disabled = false;
					document.forms[0].frm_zoom.value = myZoom;
					document.forms[0].frm_zoom.disabled = true;					
					}	
				marker = new GMarker(point, {icon: myIcon, draggable:true});
				map.addOverlay(marker);
				
//				map.openInfoWindowHtml(point,tab1contents);
				}
			});				// end GEvent.addListener() "click"
<?php
			}				// end if ($mode=="v")
?>			
			
	</SCRIPT>
<?php
	}		// end function map()

	function finished ($caption) {
//		print "</HEAD><BODY onLoad = 'document.fin_form.submit();'>";
		print "</HEAD><BODY>";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
//		print "<INPUT TYPE='submit'  VALUE='Continue'>";
		print "</FORM></BODY></HTML>";	
		}

	function do_calls($id = 0) {
		$print = "\n<SCRIPT>\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]responder` where `id` != $id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_array($result))) {
			if (!empty($row['callsign'])) {
				$print .="\t\tcalls.push('" .$row['callsign'] . "');\n";
				}
			}				// end while();
		$print .= "</SCRIPT>\n";
		return $print;
		}		// end function do calls($id = 0)

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Responder	
		$query = "DELETE FROM $GLOBALS[mysql_prefix]responder WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Unit <i>" . stripslashes_deep($_POST['frm_name']) . "</i> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$frm_mobile = ((array_key_exists ('frm_mobile',$_POST )) && ($_POST['frm_mobile']=='on'))? 1 : 0 ;		
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`un_status_id`= " . quote_smart(trim($_POST['frm_un_status_id'])) . ",
				`callsign`= " . 	quote_smart(trim($_POST['frm_callsign'])) . ",
				`mobile`= " . 		$frm_mobile . ",
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_via`= " . 	quote_smart(trim($_POST['frm_contact_via'])) . ",
				`lat`= " . 			quote_smart(trim($_POST['frm_lat'])) . ",
				`lng`= " . 			quote_smart(trim($_POST['frm_lng'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($my_session['user_id'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . " 
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";		// 	
	
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_UNIT_STATUS'], "", $_POST['frm_id'], $_POST['frm_un_status_id']);}
			
			$caption = "<B>Unit <i>" . stripslashes_deep($_POST['frm_name']) . "</i> has been updated.</B><BR /><BR />";
			}
		}				// end else {}
		
	if ($_getgoadd == 'true') {
		$frm_mobile = ((array_key_exists ('frm_mobile',$_POST )) && ($_POST['frm_mobile']=='on'))? 1 : 0 ;		
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]responder` (
			`name`, `description`, `capab`, `un_status_id`, `callsign`, `mobile`, `contact_name`, `contact_via`, `lat`, `lng`, `type`, `user_id`, `updated` ) 
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_un_status_id'])) . "," .
				quote_smart(trim($_POST['frm_callsign'])) . "," .
				$frm_mobile . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_via'])) . "," .
				quote_smart(trim($_POST['frm_lat'])) . "," .
				quote_smart(trim($_POST['frm_lng'])) . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($my_session['user_id'])) . "," .
				quote_smart(trim($now)) . ");";


		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//		do_log($GLOBALS['LOG_UNIT_STATUS'], mysql_insert_id(), $_POST['frm_un_status_id']);
		do_log($GLOBALS['LOG_UNIT_STATUS'], "", mysql_insert_id(), $_POST['frm_un_status_id']);
		
		$caption = "<B>Unit <i>" . stripslashes_deep($_POST['frm_name']) . "</i> has been added.</B><BR /><BR />";
		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')
	
	if ($_getadd == 'true') {
		print do_calls();		// call signs to JS array for validation
?>		
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD>
		<BODY  onLoad = "ck_frames()" onunload="GUnload()">
		<FONT CLASS="header">Add Unit</FONT><BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<FORM NAME= "res_add_Form" METHOD="POST" onSubmit="return validate_res(document.res_add_Form);" ACTION="units.php?func=responder&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Capability: </TD>	<TD><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD>
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_EMS'];?>" NAME="frm_type"> EMS<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE'];?>" NAME="frm_type"> Fire<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS'];?>" NAME="frm_type"> Police<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MUTU'];?>" NAME="frm_type"> Mutual Assist<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR'];?>" NAME="frm_type"> Other<BR />
			</TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>
			<TD><SELECT NAME="frm_un_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
				<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "\t</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		print "\t<OPTION VALUE=' {$row_st['id']}'  CLASS='{$row_st['group']}' title='{$row_st['description']}'> {$row_st['status_val']} </OPTION>\n";
//		print "\t<OPTION VALUE=" . $row_st['id'] . ">" . $row_st['status_val'] . "</OPTION>\n";
		$i++;
		}		// end while()
	print "\n</OPTGROUP>\n";
	unset($result_st);
?>
		</SELECT></TD></TR>
		<TR CLASS = "odd" VALIGN='bottom'><TD CLASS="td_label">Callsign:</TD>		<TD><INPUT SIZE="24" MAXLENGTH="24" TYPE="text" NAME="frm_callsign" VALUE="" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS="td_label">Mobile:</SPAN>&nbsp;&nbsp;<INPUT TYPE="checkbox" NAME="frm_mobile"></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Map:<TD><INPUT TYPE="text" NAME="frm_lat" VALUE="" disabled />&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="" disabled /></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>

		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center'>
		<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
			EMS: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Mutual: 	<IMG SRC = './markers/sm_white.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
		</TD></TR></TABLE><!-- end outer -->

<?php
		map("a") ;				// call GMap js ADD mode
?>
		<FORM NAME='can_Form' METHOD="get" ACTION = "units.php">
		<INPUT TYPE='hidden' NAME = 'func' VALUE='responder'/>
		</FORM>
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['add'])

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_array($result);		
		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$checked = (!empty($row['mobile']))? " checked" : "" ;
		print do_calls($id);								// generate JS calls array
?>		
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD>
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<FONT CLASS="header">Edit Unit Data</FONT>&nbsp;&nbsp;(#<?php print $id; ?> )<BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='editform'>
		<FORM METHOD="POST" NAME= "res_edit_Form" onSubmit="return validate_res(document.res_edit_Form);" ACTION="units.php?func=responder&goedit=true">
		<TR CLASS = "even"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Capability: </TD>										<TD><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD>
<?php
		$type_checks = array ("", "", "", "", "", "");	// all empty
		$type_checks[$row['type']] = " checked";		// set the nth entry

?>		
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_EMS']; ?>" NAME="frm_type" <?php print $type_checks[1];?>> EMS<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE']; ?>" NAME="frm_type" <?php print $type_checks[2];?>> Fire<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS']; ?>" NAME="frm_type" <?php print $type_checks[3];?>> Police<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MUTU']; ?>" NAME="frm_type" <?php print $type_checks[4];?>> Mutual<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR']; ?>" NAME="frm_type" <?php print $type_checks[5];?>> Other<BR />
		</TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>
			<TD><SELECT NAME="frm_un_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "<OPTGROUP LABEL='$the_grp'>\n";
			}
		$sel = ($row['un_status_id']== $row_st['id'])? " SELECTED" : "";
		print "\t<OPTION VALUE=" . $row_st['id'] . $sel .">" . $row_st['status_val']. "<OPTION>\n";
		$i++;
		}
	unset($result_st);
?>
	</SELECT></TD></TR>

		<TR VALIGN = 'baseline' CLASS = "odd" VALIGN='bottom'><TD CLASS="td_label">Callsign:</TD>		<TD><INPUT SIZE="24" MAXLENGTH="24" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'] ;?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<SPAN CLASS="td_label">Mobile:</SPAN>&nbsp;&nbsp;<INPUT TYPE="checkbox" NAME="frm_mobile" <?php print $checked ; ?>></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="<?php print $row['contact_via'] ;?>" /></TD></TR>
	
		<TR CLASS = "odd"><TD CLASS="td_label">Map:<TD><INPUT TYPE="text" NAME="frm_lat" VALUE="<?php print $row['lat'] ;?>" SIZE=12 disabled />&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="<?php print $row['lng'] ;?>" SIZE=12 disabled /></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even"><TD CLASS="td_label">Remove Unit:</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" ></TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		</FORM></TABLE>
		</TD><TD><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset'></DIV></TD></TR></TABLE>
<?php
		print do_calls($id);		// generate JS calls array
		map("e") ;				// call GMap js EDIT mode
?>
		<FORM NAME='can_Form' METHOD="get" ACTION = "units.php">
		<INPUT TYPE='hidden' NAME = 'func' VALUE='responder'/>
		</FORM>
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['edit'])

		if ($_getview == 'true') {
			$id = $_GET['id'];
			$query	= "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder WHERE id=$id";
//			dump ($query);			
			
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//			dump (mysql_affected_rows());
			$row	= stripslashes_deep(mysql_fetch_array($result));
//			dump ($row);			
//			unset($result);
//			dump ($row);			

			if (isset($row['un_status_id'])) {
				$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id`=" . $row['un_status_id'];	// status value
				$result_st	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$row_st	= mysql_fetch_array($result_st);
				unset($result_st);
				}
			$un_st_val = (isset($row['un_status_id']))? $row_st['status_val'] : "?";
			$type_checks = array ("", "", "", "", "", "");
			$type_checks[$row['type']] = " checked";
			$checked = (!empty($row['mobile']))? " checked" : "" ;			
			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM
?>			
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD>
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
			<FONT CLASS="header">Unit Data</FONT><BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER="0" ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="units.php?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label">Name: </TD>			<TD><?php print $row['name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Description: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Capability: </TD>	<TD><?php print $row['capab'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Status:</TD>		<TD><?php print $un_st_val;?> </TD></TR>
			<TR VALIGN = 'baseline' CLASS = "even"><TD CLASS="td_label">Callsign:</TD>		<TD><?php print $row['callsign'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS="td_label">Mobile:</SPAN>&nbsp;&nbsp;<INPUT disabled TYPE="checkbox" NAME="frm_mobile" <?php print $checked ; ?>></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Contact name:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Contact via:</TD>	<TD><?php print $row['contact_via'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Type: </TD><TD>
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_EMS']; ?>" NAME="frm_type" <?php print $type_checks[1];?>> EMS<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE']; ?>" NAME="frm_type" <?php print $type_checks[2];?>> Fire<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS']; ?>" NAME="frm_type" <?php print $type_checks[3];?>> Police<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MUTU']; ?>" NAME="frm_type" <?php print $type_checks[4];?>> Mutual<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR']; ?>" NAME="frm_type" <?php print $type_checks[5];?>> Other<BR />
				</TD></TR>
			<TR CLASS = 'even'><TD CLASS="td_label">As of:</TD>							<TD><?php print format_date($row['updated']); ?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Map:<TD ALIGN='center'><INPUT TYPE="text" NAME="frm_lat" VALUE="<?php print $row['lat'] ;?>" SIZE=12 disabled />&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="<?php print $row['lng'] ;?>" SIZE=12 disabled /></TD></TR>
<?php
		$utm = get_variable('UTM');
		if ($utm==1) {
			$coords =  $row['lat'] . "," . $row['lng'];
			print "<TR CLASS='even'><TD CLASS='td_label'>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>\n";
			}
		$toedit = (is_guest())? "" : "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
?>			
			<TR><TD>&nbsp;</TD></TR>
<?php
		if (!is_guest()) {
?>		
			<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="to Edit" 	onClick= "to_edit_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="to Dispatch" 	onClick= "document.getElementById('incidents').style.display='block'; document.getElementById('view_unit').style.display='none';">
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			</TD></TR>
<?php
			}
?>			
			</FORM></TABLE>
			<BR /><BR /><BR />
			<TABLE BORDER=1 ID = 'incidents' STYLE = 'display:none' >
			<TR CLASS='even'><TH COLSPAN=99> Click incident to assign to <?php print $row['name'] ;?></TH></TR>
			
<?php
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]ticket ORDER BY `id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							// major while ... starts here
		$i=0;
		while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
			switch($row['severity'])		{		//color tickets by severity
			 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 							$severityclass=''; break;
				}
//			dump ($row);

			print "\t<TR CLASS ='" .  $evenodd[($i+1)%2] . "' onClick = 'to_routes(\"" . $row['id'] . "\")'>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['scope']}'>" . shorten($row['scope'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['description']}'>" . shorten($row['description'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['street']} {$row['city']}'>" . shorten($row['street'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['city']}'>" . shorten($row['city'], 10). "</TD>";
			print "\t\t</TR>\n";
			$i++;
			}
?>
			<TR><TD ALIGN="center" COLSPAN=99><BR /><BR />
				<INPUT TYPE="button" VALUE="Cancel" onClick = "document.getElementById('incidents').style.display='none'; document.getElementById('view_unit').style.display='block';">
			</TD></TR>
			</TABLE><BR><BR>
			</TD><TD><DIV ID='map' style="width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset"></DIV></TD></TR></TABLE>
			<FORM NAME="can_Form" METHOD="post" ACTION = "units.php"></FORM>		
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "units.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>		
			<FORM NAME="routes_Form" METHOD="get" ACTION = "routes.php">
			<INPUT TYPE="hidden" NAME="ticket_id" 	VALUE="">
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>		
			</BODY>					<!-- END RESPONDER VIEW -->
<?php
			map("v") ;				// call GMap js EDIT mode
?>
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])

		$do_list_and_map = TRUE;
		
		if($do_list_and_map) {
			if (!isset($mapmode)) {$mapmode="a";}
			print $caption;
?>
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD><!-- 797 -->
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<TABLE ID='outer'><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
				EMS: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Mutual: 	<IMG SRC = './markers/sm_white.png' BORDER=0>&nbsp;&nbsp;
				Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
			</TD></TR></TABLE><!-- end outer -->
			
			<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='view' VALUE='true'>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			</FORM>
			
			<FORM NAME='add_Form' METHOD='get' ACTION='units.php'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='add' VALUE='true'>
			</FORM>
			
			<FORM NAME='can_Form' METHOD="get" ACTION = "units.php?func=responder"></FORM>
			<FORM NAME='tracks_Form' METHOD="get" ACTION = "tracks.php"></FORM>
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print do_calls();		// generate JS calls array
//		$button = (is_guest())? "": "<TR><TD COLSPAN='99' ALIGN='center'><BR /><INPUT TYPE='button' value= 'Add a Unit'  onClick ='document.add_Form.submit();'></TD></TR>";

		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'><BR /><INPUT TYPE = 'button' onClick = 'document.tracks_Form.submit();' VALUE='Unit Tracks'>";
		$buttons .= (is_guest())? "":"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' value= 'Add a Unit'  onClick ='document.add_Form.submit();'>";
		$buttons .= "</TD></TR>";

		print list_responders($buttons, 0);
		print "\n</HTML> \n";
		exit();
		}				// end if($do_list_and_map)
    break;
?>
