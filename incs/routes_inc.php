<?php
error_reporting(E_ALL);

/*
7/8/10 extracted from routes.php
8/4/10 Add check for tickets/ units / facilities entered in no maps mode and added default question mark icon for those items.
8/13/10 apply setUIToDefault() map control;
11/23/10 - mi vs km per locale
11/24/10 parens added to sql to resolve ambiguity
11/18/10 Revised function do_list to resolve issue of single dispatch from units list showing all units. 
12/18/10 Added filter by capabilities.
2/5/11 calls assigned added as list order element
3/15/11 correction for embedded apostrophe, function get_assigned_td()added, locale switch added
5/4/11 Additions for multi region working.
5/4/11 white-space style added, accommodate session/get ticket id container re color change
8/1/11 Added function call for do_landb.
*/

function get_assigned_td($unit_id, $on_click = "") {		// returns td string - 3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
		WHERE `responder_id` = '{$unit_id}' AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";	//	5/4/11
	
	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ( mysql_num_rows($result_as) == 0) {unset($result_as); return "<TD></TD>";}
	else {		
		$row_assign = stripslashes_deep(mysql_fetch_assoc($result_as)) ;
		unset($result_as);
		$tip = str_replace ( "'", "`",    ("{$row_assign['contact']}/{$row_assign['street']}/{$row_assign['city']}/{$row_assign['phone']}/{$row_assign['scope']}   "));
	
		switch($row_assign['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 							$severityclass='severity_normal'; break;
			}
	
		switch (mysql_affected_rows()) {		// 8/30/10
			case 0:
				$the_disp_stat="";
				break;			
			case 1:
				$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
				break;
			default:							// multiples
			    $the_disp_stat = "<SPAN CLASS='disp_stat'>&nbsp;" . mysql_affected_rows() . "&nbsp;</SPAN>&nbsp;";
			    break;
			}						// end switch()
		$ass_td = "<TD ALIGN='left' onMouseover=\\\"Tip('{$tip}')\\\" onmouseout=\\\"UnTip()\\\" onClick = '{$on_click}' CLASS='$severityclass'  STYLE = 'white-space:nowrap;'>{$the_disp_stat}" . shorten($row_assign['scope'], 24) . "</TD>";
		return $ass_td;
		}		// end else
	}		// end function get_assigned_td()

	function do_list($unit_id ="", $capabilities ="", $searchtype) {		// 12/18/10
		global $unav_id_str, $row_ticket, $dispatches_disp, $dispatches_act, $from_top, $eol, $sidebar_width, $sortby_distance ;
		
		$conversion = get_dist_factor();			// KM vs mi - 11/23/10
		switch($row_ticket['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			case $GLOBALS['SEVERITY_NORMAL'] : 	$severityclass='severity_normal'; break;
			default: 							dump( basename(__FILE__) . "/" . __LINE__); break;
			}
	
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,
			UNIX_TIMESTAMP(booked_date) AS booked_date,
			UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` 
			FROM `$GLOBALS[mysql_prefix]ticket`  
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
			WHERE `$GLOBALS[mysql_prefix]ticket`.`id`=" . get_ticket_id () . " LIMIT 1";			// 7/24/09 10/16/08 Incident location 09/25/09 Pre Booking
	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_ticket = stripslashes_deep(mysql_fetch_array($result));
		$facility = $row_ticket['facility'];
		$rec_fac = $row_ticket['rec_facility'];
			if(($row_ticket['lat']==0.999999) && ($row_ticket['lng']==0.999999)) {	// check for tickets created in no-maps mode 8/4/10
				$lat = get_variable('def_lat');
				$lng = get_variable('def_lng');
			} else {
				$lat = $row_ticket['lat'];
				$lng = $row_ticket['lng'];
			} // end check for tickets created in no-maps mode
		
	//	print "var thelat = " . $lat . ";\nvar thelng = " . $lng . ";\n";		// set js-accessible location data
		unset ($result);
	
		if ($rec_fac > 0) {
			$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`= $rec_fac ";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing
			$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_rec_fac = stripslashes_deep(mysql_fetch_array($result_rfc));
			$rf_lat = $row_rec_fac['lat'];
			$rf_lng = $row_rec_fac['lng'];
			$rf_name = $row_rec_fac['name'];		
			
	//		print "var thereclat = " . $rf_lat . ";\nvar thereclng = " . $rf_lng . ";\n";		// set js-accessible location data for receiving facility
		} else {
	//		print "var thereclat;\nvar thereclng;\n";		// set js-accessible location data for receiving facility
		}
	
?>
	<SCRIPT>
		var color=0;
		var last_from;
		var last_to;
		var rec_fac;
		var current_id;			// 10/25/08
		var output_direcs = "";	//10/6/09
		var have_direcs = 0;	//10/6/09
		var tick_name = "<?php print $row_ticket['scope'];?>";	// 3/15/11
	
		if (GBrowserIsCompatible()) {
			var colors = new Array ('odd', 'even');
			
			var Direcs = null;			// global
			var Now;
			var mystart;
			var myend;
		    function setDirections(fromAddress, toAddress, recfacAddress, locale, unit_id) {	//10/6/09

				if (document.routes_Form.frm_allow_dirs.value==='false') {return false;}		// 11/21/09
				
				$("mail_button").style.display = "none";			//10/6/09
				$("loading").style.display = "inline-block";		// 10/28/09
	
				$("directions_ok_no").style.display = "none";
				$("loading_2").style.display = "inline-block";
				
			    last_from = fromAddress;
			    last_to = toAddress;
				rec_fac = recfacAddress;
				f_unit = unit_id;	//10/6/09
				G_START_ICON.image = "./our_icons/sm_white.png";
				G_START_ICON.iconSize = new GSize(12,20); 
				G_END_ICON.image = "./our_icons/sm_white.png";
				G_END_ICON.iconSize = new GSize(12,20);         	
	
				Now = new Date();      				// Grab the current date.
				mystart = Now.getTime(); 		// Initialize variable Start
		
				if (rec_fac != "") {	//10/6/09
				    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress + " to: " + recfacAddress, { "locale": locale, preserveViewport : true  });
						}
					else{
				    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale, preserveViewport : true  });
						}

					GEvent.addListener(Direcs, "addoverlay", GEvent.callback(Direcs, cb2())); 		// 11/21/09
			    	}		// end function set Directions()
	
			function cb2() {                               // callback function 10/6/09
				var output_direcs = "";
				for ( var i = 0; i < gdir.getNumRoutes(); i++) {        // Traverse all routes - not really needed here, but ...
					var groute = gdir.getRoute(i);
					var distanceTravelled = 0;             // if you want to start summing these
	 
					for ( var j = 0; j < groute.getNumSteps(); j++) {                // Traverse the steps this route
						var gstep = groute.getStep(j);
						var directions_text =  gstep.getDescriptionHtml();
						var directions_dist = gstep.getDistance().html;
						output_direcs = output_direcs + directions_text + " " + directions_dist + ". " + "\n";
						}
					}
				output_direcs = output_direcs.replace("<div class=\"google_note\">", "\n -");	//10/6/09
				output_direcs = output_direcs.replace("Destination", "\n***Destination");	//10/6/09
				output_direcs = output_direcs.replace("&nbsp:", " ");	//10/6/09
				document.email_form.frm_direcs.value = output_direcs;	//10/6/09
				document.email_form.frm_u_id.value = f_unit;	//10/6/09
				document.email_form.frm_scope.value = tick_name;	//10/29/09

				have_direcs = 1;	//10/6/09
				$("mail_button").style.display = "inline-block";	//10/6/09
				$("loading").style.display = "none";		// 10/28/09	
				$("loading_2").style.display = "none";
				$("directions_ok_no").style.display = "inline-block";			
				}                // end function cb2()
	
			function mail_direcs(f) {	//10/6/09
				f.target = 'Mail Form'
				newwindow_mail=window.open('',f.target,'titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300');
				if (isNull(newwindow_mail)) {
					alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
					return;
					}
				newwindow_mail.focus();
				f.submit();
				return false;
				}
		
			function do_sidebar(sidebar, color, id, unit_id) {						// No map
				var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09
				marker = null;
				gmarkers[id] = null;										// marker to array for side bar click function
		
				side_bar_html += "<TR ID = '_tr" + id  + "' CLASS='" + colors[(id+1)%2] +"' VALIGN='bottom' onClick = myclick(" + id + "," + unit_id +");><TD>";
	
				side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;' /></TD>";
				var letter = ""+ id;										// start with 1 - 1/5/09 - 1/29/09
	
	//			var the_class = (direcs[id])?  "emph" : "td_label";
				var the_class = (lats[id])?  "emph" : "td_label";
				side_bar_html += "<TD CLASS='" + the_class + "' ALIGN='right'>" + letter + " "+ sidebar +"</TD></TR>\n";
				return null;
				}				// end function create Marker()
	
	
			function createMarker(point,sidebar,tabs, color, id, unit_id) {		// Creates marker and sets up click event infowindow
				do_sidebar(sidebar, color, id, unit_id)
				var icon = new GIcon(listIcon);
				var uid = unit_id;
				var letter = ""+ id;
											// start with 1 - 1/5/09 - 1/29/09
				var icon_url = "./our_icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;				// 1/5/09
		
				icon.image = icon_url;		// ./our_icons/gen_icon.php?blank=4&text=zz"
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
					detailmap.setCenter(point, 17);  					// larger # = closer
					detailmap.addOverlay(marker);
					});
			
				gmarkers[id] = marker;							// marker to array for side bar click function
				infoTabs[id] = tabs;							// tabs to array
				bounds.extend(point);							// extend the bounding box		
		
				return marker;
				}				// end function create Marker()
				
			function createdummyMarker(point,sidebar,tabs, color, id, unit_id) {		// Creates marker and sets up click event infowindow
				do_sidebar(sidebar, color, id, unit_id)
				var icon = new GIcon(listIcon);
				var uid = unit_id;
				var letter = ""+ id;
											// start with 1 - 1/5/09 - 1/29/09
				var icon_url = "./our_icons/question1.png";				// 1/5/09
				icon.image = icon_url;
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
					detailmap.setCenter(point, 17);  					// larger # = closer
					detailmap.addOverlay(marker);
					});
			
				gmarkers[id] = marker;							// marker to array for side bar click function
				infoTabs[id] = tabs;							// tabs to array
				bounds.extend(point);							// extend the bounding box		
		
				return marker;
				}				// end function create Marker()				
		
			function myclick(id, unit_id) {								// responds to side bar click
				var norecfac = "";
				if (document.getElementById(current_id)) {
					document.getElementById(current_id).style.visibility = "hidden";			// hide last check if defined
					}
				current_id= "R"+id;
				document.getElementById(current_id).style.visibility = "visible";			// show newest
				if (lats[id]) {																// position data?
					$('mail_dir_but').style.visibility = "visible";			// 11/12/09	
<?php 				if(($lat==0.999999) && ($lng==0.999999)) { // test of tickets entered in no-maps mode 8/4/10
?>					
						var thelat = <?php print get_variable('def_lat');?>; var thelng = <?php print  get_variable('def_lng');?>;		// coords of click point
<?php 				} else { ?>
						var thelat = <?php print $lat;?>; var thelng = <?php print $lng;?>;		// coords of click point
<?php
					} // end of test of tickets entered in no-maps mode 8/4/10 
					
					if ($row_ticket['rec_facility'] > 0) {
?>			
						var thereclat = <?php print $rf_lat;?>; var thereclng = <?php print $rf_lng;?>;									//adds in receiving facility
						if (direcs[id]) {
							setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, thereclat + " " + thereclng, "en_US", unit_id);	// get directions
							}
<?php
						} 
					else {
?>			
						if (direcs[id]) {
							setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, norecfac, "en_US", unit_id);					// get directions
							}
<?php
						}
?>
					}
				else {
					$('directions').innerHTML = "";							// no position data, no directions
					$('mail_dir_but').style.visibility = "hidden";			// 11/12/09	 -	
					}
	
				$("directions").innerHTML= "";								// prior directions no longer apply - 11/21/09
				if (gdir) {	gdir.clear();}

				}					// end function my click(id)
	
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
		
			var starting = false;
	
			function sv_win(theLat, theLng) {				// 8/17/09
				if(starting) {return;}						// dbl-click proof
				starting = true;					
	//			alert(622);
				var url = "street_view.php?thelat=" + theLat + "&thelng=" + theLng;
				newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
				if (!(newwindow_sl)) {
					alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
					return;
					}
				newwindow_sl.focus();
				starting = false;
				}		// end function sv win()
	
			
			function handleErrors(){		//G_GEO_UNKNOWN_DIRECTIONS 	
				if (gdir.getStatus().code == G_GEO_UNKNOWN_DIRECTIONS ) {
					alert("501: directions unavailable\n\nClick map point for directions.");
					}
				else if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
					alert("440: No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
				else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
					alert("442: A map request could not be processed, reason unknown.\n Error code: " + gdir.getStatus().code);
				else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
					alert("444: Technical error.\n Error code: " + gdir.getStatus().code);
				else if (gdir.getStatus().code == G_GEO_BAD_KEY)
					alert("448: The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
				else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
					alert("450: A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
				else alert("451: An unknown error occurred.");
				}		// end function handleErrors()
	
			function onGDirectionsLoad(){ 
	//			var temp = gdir.getSummaryHtml();
				}		// function onGDirectionsLoad()
	
			function guest () {
				alert ("Demonstration only.  Guests may not commit dispatch!");
				}
				
			function validate(){		// frm_id_str
				msgstr="";
				for (var i =1;i<unit_sets.length;i++) {				// 3/30
					if (unit_sets[i]) {
						msgstr+=unit_names[i]+"\n";
						document.routes_Form.frm_id_str.value += unit_ids[i] + "|";
						}
					}
				if (msgstr.length==0) {
					var more = (nr_units>1)? "s": ""
					alert ("Please select unit" + more + ", or cancel");
					return false;
					}
				else {
					var quick = <?php print (intval(get_variable("quick")==1))? "true;\n" : "false;\n";?>
				
					if ((quick) || (confirm ("Please confirm unit dispatch\n\n" + msgstr))) {		// 11/23/09
	
						document.routes_Form.frm_id_str.value = document.routes_Form.frm_id_str.value.substring(0, document.routes_Form.frm_id_str.value.length - 1);	// drop trailing separator
						document.routes_Form.frm_name_str.value = msgstr;	// for re-use
						document.routes_Form.submit();
	//					document.getElementById("outer").style.display = "none";		4/26/10
						document.getElementById("bottom").style.display = "block";					
						}
					else {
						document.routes_Form.frm_id_str.value="";	
						return false;
						}
					}
	
				}		// end function validate()
		
			function exists(myarray,myid) {
				var str_key = " " + myid;		// force associative
				return ((typeof myarray[str_key])!="undefined");		// exists if not undefined
				}		// end function exists()
				
			var icons=[];						// note globals
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$icons = $GLOBALS['icons'];
		
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// map type to blank icon id
			$blank = $icons[$row['icon']];
			print "\ticons[" . $row['id'] . "] = " . $row['icon'] . ";\n";	// 
			}
		unset($result);
?>
			var map;
			var center;
			var zoom;
			
		    var gdir;				// directions
		    var geocoder = null;
		    var addressMarker;
			$("mail_button").style.display = "none";		// 10/28/09
			$("loading").style.display = "none";		// 10/28/09		
			
			var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders' STYLE = 'WIDTH: <?php print $sidebar_width;?>px;'>";
	
			var gmarkers = [];
			var infoTabs = [];
			var lats = [];
			var lngs = [];
			var unit_names = [];		// names 
			var unit_sets = [];			// settings
			var unit_ids = [];			// id's
			var unit_assigns =  [];		// unit id's assigned this incident
			var direcs =  [];			// if true, do directions - 7/13/09
	
			var which;			// marker last selected
			var i = 0;			// sidebar/icon index
			map = new GMap2(document.getElementById("map_canvas"));		// create the map
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
//			map.addControl(new GSmallMapControl());						// 9/23/08
			map.setUIToDefault();										// 8/13/10
			map.addControl(new GMapTypeControl());
			var bounds = new GLatLngBounds();						// create empty bounding box			
			
			do_landb();				// 8/1/11 - show scribbles				
<?php if (intval(get_variable('terrain')) == 1) { ?>
				map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	
	
			gdir = new GDirections(map, document.getElementById("directions"));
			
			GEvent.addListener(gdir, "load", onGDirectionsLoad);
			try {GEvent.addListener(gdir, "error", handleErrors);}
			catch (e) {}
		
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
																		// set Incident position
			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 675
			bounds.extend(point);										// Incident into BB
		
			GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom

				setDirections(last_from, last_to, "en_US") ;

				});
			var accept_click = false;					// 10/15/08
			GEvent.addListener(map, "click", function(marker, point) {		// point.lat()
				var the_start = point.lat().toString() + "," + point.lng().toString();
				var the_end = thelat.toString() + "," + thelng.toString();	
				setDirections(the_start, the_end, "en_US");
				});				// end GEvent.addListener()
	
			var nr_units = 	0;
			var email= false;
			
<?php
	
			function get_cd_str($in_row) {			// unit row in, 
			global $unit_id;
	//																			// first, already on this run?		
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE  `ticket_id` = " . get_ticket_id () . "
					 AND (`responder_id`={$in_row['unit_id']}) 
					 AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) LIMIT 1;";	// 6/25/10
				snap(__LINE__, $query);
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if(mysql_affected_rows()==1) 			{return " CHECKED DISABLED ";}	
	
				if (($unit_id != "") && ((mysql_affected_rows()!=1) || ((mysql_affected_rows()==1) && (intval($in_row['multi'])==1))))		{print "checked";return " CHECKED ";}				// 12/18/10 - Checkbox checked here individual unit seleted.
				if (intval($in_row['dispatch'])==2) 	{return " DISABLED ";}				// 2nd, disallowed  - 5/30/10
				if (intval($in_row['multi'])==1) 		{return "";}						// 3rd, allowed
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
					WHERE `responder_id`={$in_row['unit_id']} 
					AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
					LIMIT 1;";		// 6/25/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if(mysql_affected_rows()==1) 			{return " DISABLED ";}		// 3/30/10
				else							 		{return "";}
				}			// function get cd_str($in_row)
				
	
			$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
													// build js array of responders to this ticket - possibly none
			$query = "SELECT `ticket_id`, `responder_id` 
				FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . get_ticket_id ();
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
			
			while ($assigns_row = stripslashes_deep(mysql_fetch_array($result))) {
				print "\t\tunit_assigns[' '+ " . $assigns_row['responder_id']. "]= true;\n";	// note string forced
				}
			print "\n";
// ===================================================================================

			$query = "SELECT *, UNIX_TIMESTAMP(problemstart) AS problemstart, UNIX_TIMESTAMP(problemend) AS problemend 
				FROM `$GLOBALS[mysql_prefix]ticket` 
				WHERE `id`= " . get_ticket_id () . " LIMIT 1;";	// 4/5/10
			$result_pos = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if(mysql_affected_rows()==1) {
				$row_position = stripslashes_deep(mysql_fetch_array($result_pos));
				$latitude = $row_position['lat'];
				$longitude = $row_position['lng'];
				$problemstart = $row_position['problemstart'];
				$problemend = $row_position['problemend'];
				unset($result_pos);
				}
			else {
	//			dump ($query);
				}
	
			$where = (empty($unit_id))? "" : " AND `r`.`id` = $unit_id ";		// revised 5/23/08 per AD7PE 
//			$where2 = (empty($capabilities))? "" : " AND (";	// 12/18/10
			if(!empty($unit_id)) { 
				$where2="";
			} else {
				$where2 = (empty($capabilities))? "" : " AND (";	// 12/18/10
				$searchitems = (empty($capabilities))? "" : explode(" ", $capabilities);
				if($searchitems) {
					for($j = 0; $j < count($searchitems); $j++){
						if  ($j+1 != count($searchitems)) {
							$where2 .= "`r`.`capab` LIKE '%{$searchitems[$j]}%' $searchtype";
						} else {
							$where2 .= "`r`.`capab` LIKE '%{$searchitems[$j]}%')";
						}
					}
				}
			}

			
			switch (intval(trim(get_variable('locale')))) {			// nm conversion, 3/15/11
				case 0:
					$nm_to_what = 1.1515;				// mi
					$capt = "mi";
					break;
				case 1:
					$nm_to_what = 1.1515*1.609344;		// UK - km
					$capt = "km";
					break;
				case 2:
					$nm_to_what = 1.1515*1.609344;		// ROW - km
					$capt = "km";
					break;
				default:
					$nm_to_what = 1.1515*1.609344 ;		// ERROR?
					$capt = "km";
					break;
					}			
			$have_position = (!(($latitude==0.999999) && ($longitude==0.999999)));
			$by_distance = (($sortby_distance)&& ($have_position))? "`distance` ASC, ": "";			// 6/19/10 - user-set variable, 2/5/11 calls assigned added as order element
							// 5/30/10, 11/24/10

// ============================= Regions Stuff	

// Allows Tickets to be dispatched to any responders in the same region as the current user.						
							
			// $query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 4/18/11
			// $result = mysql_query($query);	// 5/4/11
			// $al_groups = array();
			// $al_names = "";	
			// while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 5/4/11
				// $al_groups[] = $row['group'];
				// $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 5/4/11
				// $result2 = mysql_query($query2);	// 5/4/11
				// while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 5/4/11		
						// $al_names .= $row2['group_name'] . ", ";
					// }
				// }

			// if(isset($_SESSION['viewed_groups'])) {
				// $al_groups= explode(",",$_SESSION['viewed_groups']);
				// }
				
			// if(!isset($_SESSION['viewed_groups'])) {	//	5/4/11
			// $x=0;	
			// $where3 = "AND (";
			// foreach($al_groups as $grp) {
				// $where4 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				// $where3 .= "`a`.`group` = '{$grp}'";
				// $where3 .= $where4;
				// $x++;
				// }
			// } else {
			// $x=0;	
			// $where3 = "AND (";	
			// foreach($al_groups as $grp) {
				// $where4 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				// $where3 .= "`a`.`group` = '{$grp}'";
				// $where3 .= $where4;
				// $x++;
				// }
			// }
			// $where3 .= " AND `a`.`type` = 2";

// Replacement code - only allows Tickets to be dispatched to responders in the same region
			
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 1 AND `resource_id` = " . get_ticket_id () . " ORDER BY `id` ASC;";	// 4/18/11
			$result = mysql_query($query);	// 5/4/11
			$al_groups = array();
			$al_names = "";	
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 5/4/11
				$al_groups[] = $row['group'];
				$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 5/4/11
				$result2 = mysql_query($query2);	// 5/4/11
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 5/4/11		
						$al_names .= $row2['group_name'] . ", ";
					}
				}

			$x=0;	
			$where3 = "AND (";	
			foreach($al_groups as $grp) {
				$where4 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where3 .= "`a`.`group` = '{$grp}'";
				$where3 .= $where4;
				$x++;
				}
			
			$where3 .= " AND `a`.`type` = 2";			
			
// ================================ end of regions stuff			
							
			$query = "(SELECT *, UNIX_TIMESTAMP(`updated`) AS `updated`, `r`.`name` AS `unit_name`, `t`.`name` AS `type_name`, `r`.`type` AS `type`,
				`r`.`id` AS `unit_id`, `r`.`capab` AS `capab`,
				`s`.`status_val` AS `unitstatus`, `contact_via`, 
				(((acos(sin(({$latitude}*pi()/180)) * sin((`r`.`lat`*pi()/180))+cos(({$latitude}*pi()/180)) * cos((`r`.`lat`*pi()/180)) * cos((({$longitude} - `r`.`lng`)*pi()/180))))*180/pi())*60*{$nm_to_what}) AS `distance`,
				(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
					WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`  
					AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )
					AS `calls_assigned`			
				
				FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`r`.`un_status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON (`r`.`type` = `t`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`r`.`id` = `a`.`resource_id`)					
				 WHERE  `dispatch` = 0 $where $where2 $where3 GROUP BY unit_id )
			UNION DISTINCT
				(SELECT *, UNIX_TIMESTAMP(`updated`) AS `updated`, `r`.`name` AS `unit_name`, `t`.`name` AS `type_name`, `r`.`type` AS `type`,
				`r`.`id` AS `unit_id`, `r`.`capab` AS `capab`,
				`s`.`status_val` AS `unitstatus`, `contact_via`, 
				9999 AS `distance`,
				(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
					WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`  
					AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
					AS `calls_assigned`			
				
				FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`r`.`un_status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON (`r`.`type` = `t`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`r`.`id` = `a`.`resource_id`)					
				 WHERE  `dispatch` > 0 $where $where2 $where3 GROUP BY unit_id )
			 ORDER BY `dispatch` ASC, `calls_assigned` ASC, {$by_distance} `handle` ASC, `unit_name` ASC, `unit_id` ASC 		 ";		//	5/4/11	
//	 		dump($query);
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if(mysql_affected_rows()>0) {
			$end_date = (intval($problemend)> 1)? $problemend:  (time() - (get_variable('delta_mins')*60));
			$elapsed = my_date_diff($problemstart, $end_date);		// 5/13/10

//	==========================================================================================		
			$search_arg = ((array_key_exists('capabilities', ($_GET))))? "<TR class='even' STYLE = 'white-space:nowrap;'><TD COLSPAN='99' ALIGN='center'>" . get_text("Units") . " capabilities match: '" . $_GET['capabilities']. "'</TD></TR>": "";
?>
			side_bar_html += "<TR class='even'>	<TD CLASS='<?php print $severityclass; ?>' COLSPAN=99 ALIGN='center'><B>Routes to Incident: <I><?php print shorten($row_ticket['scope'], 20) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(" . $elapsed; ?>)</I></B></TD></TR>\n";
			side_bar_html += "<?php print $search_arg;?>";
			side_bar_html += "<TR class='odd'>	<TD COLSPAN=99 ALIGN='center'>Click line, icon or map for route</TD></TR>\n";
			side_bar_html += "<TR class='even' STYLE = 'white-space:nowrap;'><TD COLSPAN=3></TD><TD ALIGN='left'><?php print get_text("Units");?></TD><TD ALIGN='right'>SLD&nbsp;(<?php print $capt;?>)</TD><TD ALIGN='center'>Call</TD><TD ALIGN='left'>Status</TD><TD>M</TD><TD ALIGN='left'>As of</TD></TR>\n";
	
<?php
// major while ... for RESPONDER data starts here
				$i = $k = 1;				// sidebar/icon index
				while ($unit_row = stripslashes_deep(mysql_fetch_assoc($result))) {				// 7/13/09
	
					$has_coords = ((my_is_float($unit_row['lat'])) && (my_is_float($unit_row['lng'])));				// 2/25/09, 7/7/09
					$has_rem_source = ((intval ($unit_row['aprs'])==1)||(intval ($unit_row['instam'])==1)||(intval ($unit_row['locatea'])==1)||(intval ($unit_row['gtrack'])==1)||(intval ($unit_row['glat'])==1));		// 11/15/09
	
					if(is_email($unit_row['contact_via'])) {
						print "\t\t\t email= true;\n";				
						}
?>
					nr_units++;
					var i = <?php print $i;?>;						// top of loop
					
					unit_names[i] = "<?php print addslashes($unit_row['unit_name']);?>";	// unit name 8/25/08, 4/27/09
					unit_preselected = "<?php print $unit_id;?>";
					if (unit_preselected != "") {
						unit_sets[i] = true;								// pre-set checkbox settings
						show_butts(to_visible);		//	sets dispatch button visible if there is a pre-selected unit - for dispatch from unit functionality.	5/4/11
						} else {
						unit_sets[i] = false;
						}
					unit_ids[i] = <?php print $unit_row['unit_id'];?>;
	 				direcs[i] = <?php print (intval($unit_row['direcs'])==1)? "true": "false";?>;			// do directions - 7/13/09
<?php
					if ($has_coords) {
						$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
						$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . shorten($unit_row['unit_name'], 48) . "</TD></TR>";
						$tab_1 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $unit_row['description']), 32) . "</TD></TR>";
						$tab_1 .= "<TR CLASS='odd'><TD>Status:</TD><TD>" . $unit_row['unitstatus'] . " </TD></TR>";
						$tab_1 .= "<TR CLASS='even'><TD>Contact:</TD><TD>" . $unit_row['contact_name']. " Via: " . $unit_row['contact_via'] . "</TD></TR>";
						$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($unit_row['updated']) . "</TD></TR>";
						$tab_1 .= "</TABLE>";
						}
?>
					new_element = document.createElement("input");								// please don't ask!
					new_element.setAttribute("type", 	"checkbox");
					new_element.setAttribute("name", 	"unit_<?php print $unit_row['unit_id'];?>");
					new_element.setAttribute("id", 		"element_id");
					new_element.setAttribute("style", 	"visibility:hidden");
					document.forms['routes_Form'].appendChild(new_element);
					var multi = <?php print (intval($unit_row['multi'])==1)? "true;\n" : "false;\n";?>	// 5/22/09
<?php
					$dispatched_to = (array_key_exists($unit_row['unit_id'], $dispatches_disp))?  $dispatches_disp[$unit_row['unit_id']]: "";
					if ($has_coords ) {
						if(($unit_row['lat']==0.999999) && ($unit_row['lng']==0.999999)) { // check units created in no-maps mode 8/4/10
?>	
						lats[i] = <?php print get_variable('def_lat');?>; 		// 774-1 now compute distance - in km
						lngs[i] = <?php print get_variable('def_lng');?>;
<?php 					} else { ?>
						lats[i] = <?php print $unit_row['lat'];?>; 		// 774-2 now compute distance - in km
						lngs[i] = <?php print $unit_row['lng'];?>;
<?php 					} // end check units created in no-maps mode 8/4/10  

						if(($row_ticket['lat']==0.999999) && ($row_ticket['lng']==0.999999)) { // check tickets created in no-maps mode 8/4/10
							$ticket_lat=get_variable('def_lat');
							$ticket_lng=get_variable('def_lng');
							} else {
							$ticket_lat=$row_ticket['lat'];
							$ticket_lng=$row_ticket['lng'];							
							}
?>							
<?php					
						}
	
					if (($has_coords) && ($has_rem_source) && (!(empty($unit_row['callsign'])))) {				// 11/15/09
						$thespeed = "";
						$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
							WHERE `source`= '$unit_row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";
	
						$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						if (mysql_affected_rows()>0) {		// got a track?
						
							$track_row = stripslashes_deep(mysql_fetch_array($result_tr));			// most recent track report
				
							$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
							$tab_2 .= "<TR><TH CLASS='even' COLSPAN=2>" . $track_row['source'] . "</TH></TR>";
							$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $track_row['course'] . ", Speed:  " . $track_row['speed'] . ", Alt: " . $track_row['altitude'] . "</TD></TR>";
							$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $track_row['closest_city'] . "</TD></TR>";
							$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $track_row['status'] . "</TD></TR>";
							$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($track_row['packet_date']) . "</TD></TR>";
							$tab_2 .= "</TABLE>";
?>
							var myinfoTabs = [
								new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['unit_name'], 8));?>", "<?php print $tab_1;?>"),
								new GInfoWindowTab("<?php print $track_row['source']; ?>", "<?php print $tab_2;?>"),
								new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
								];
<?php
							$thespeed = ($track_row['speed'] == 0)?"<FONT COLOR='red'><B>&bull;</B></FONT>"  : "<FONT COLOR='green'><B>&bull;</B></FONT>" ;
							if ($track_row['speed'] >= 50) { $thespeed = "<FONT COLOR='WHITE'><B>&bull;</B></FONT>";}
?>
							var point = new GLatLng(<?php print $track_row['latitude'];?>, <?php print $track_row['longitude'];?>);	// 783 - mobile position
							bounds.extend(point);															// point into BB

<?php
							}			// end if (mysql_affected_rows()>0;) for track data
						else {				// no track data
						
							$k--;			// not a clickable unit for dispatch
?>
							var myinfoTabs = [
								new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['unit_name'], 12));?>", "<?php print $tab_1;?>"),
								new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
								];
<?php						
							}				// end  no track data
											// 8/7/09
						}		// end if (has rem_source ... )
				
					else {				// no rem_source
						if ($has_coords) {					//  2/25/09
?>
							var myinfoTabs = [
								new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['unit_name'], 12));?>", "<?php print $tab_1;?>"),
								new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
								];
<?php
								if(($unit_row['lat']==0.999999) && ($unit_row['lng']==0.999999)) { // check units created in no-maps mode 8/4/10
?>	
									lats[i] = <?php print get_variable('def_lat');?>; 		// 819-1 now compute distance - in km
									lngs[i] = <?php print get_variable('def_lng');?>;
<?php 								} else { ?>
									lats[i] = <?php print $unit_row['lat'];?>; 		// 819-2 now compute distance - in km
									lngs[i] = <?php print $unit_row['lng'];?>;
<?php 								} // end check units created in no-maps mode 8/4/10

									if(($row_ticket['lat']==0.999999) && ($row_ticket['lng']==0.999999)) { // check tickets created in no-maps mode 8/4/10
										$ticket_lat=get_variable('def_lat');
										$ticket_lng=get_variable('def_lng');
										} else {
										$ticket_lat=$row_ticket['lat'];
										$ticket_lng=$row_ticket['lng'];							
										}							
							}		// end if ($has_coords)
	
						$thespeed = "";
						}									// END IF/ELSE (rem_source)
				    $the_disp_str = "";			
					if ($unit_row['dispatch']==2) {
						print "\tsidebar_line = '<TD ALIGN=center><INPUT TYPE=checkbox disabled STYLE = \"visibility: hidden;\"></TD>'";
						}
					else {
						switch ($unit_row['calls_assigned']) {									// 8/29/10
							case 0:
							    break;
							case 1:
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
									WHERE (`responder_id` = {$unit_row['unit_id']}
									AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
									limit 1";		
								$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
								$row_as = stripslashes_deep(mysql_fetch_assoc($result_as));
								$the_disp_str = "<SPAN CLASS='disp_stat'>&nbsp;" . get_disp_status ($row_as) . "&nbsp;</SPAN>&nbsp;";
							    break;
						
							default:						// display count
							    $the_disp_str = "<SPAN CLASS='disp_stat'>&nbsp;{$unit_row['calls_assigned']}&nbsp;</SPAN>&nbsp;";
							    break;
							}			// end switch ()
?>				
					sidebar_line = "<TD ALIGN='center'><INPUT TYPE='checkbox' <?php print get_cd_str($unit_row); ?> NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='show_butts(to_visible); unit_sets[<?php print $i; ?>]=this.checked;' /></TD>";
<?php
						}
?>				
	
					sidebar_line += "<TD TITLE = \"<?php print addslashes($unit_row['unit_name']);?>\">";
<?php
					$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$unit_row['icon']];		// 2/1/10
					$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$unit_row['icon']];
					$strike = ($unit_row['dispatch']==0)? "": "color:red;text-decoration:line-through;" ;

					$the_style = "<SPAN STYLE='{$strike}background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>";
					$str_dist = ($have_position)? number_format(round($unit_row['distance'], 1), 1): "" ;		// 3/5/11
//					dump(__LINE__);
?>
					sidebar_line += "<NOBR><?php print $the_style . shorten($unit_row['unit_name'], 20);?></SPAN></NOBR></TD>";
					sidebar_line += "<TD ALIGN='right'><?php print $str_dist;?></TD>"; // 8/25/08, 4/27/09

					sidebar_line += "<?php print get_assigned_td($unit_row['unit_id']); ?>";		// 3/15/11
<?php				$the_style = "<SPAN STYLE='{$strike}background-color:{$unit_row['bg_color']}; color:{$unit_row['text_color']};'>"; ?>
					sidebar_line += "<TD TITLE = \"<?php print $unit_row['unitstatus'];?>\" CLASS='td_data'><?php print $the_style . shorten($unit_row['unitstatus'], 12);?></SPAN></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print substr(format_sb_date($unit_row['updated']), 4);?></TD>";
<?php
					if (($has_coords)) {		//  2/25/09
						if(($unit_row['lat']==0.999999) && ($unit_row['lng']==0.999999)) {	// check for facilities entered in no maps mode 8/4/10
?>	
							var point = new GLatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);	//  840 for each responder 832
							var unit_id = <?php print $unit_row['unit_id'];?>;
							bounds.extend(point);																// point into BB
							var marker = createdummyMarker(point, sidebar_line, myinfoTabs,<?php print $unit_row['type'];?>, i, unit_id);	// (point,sidebar,tabs, color, id)
							if (!(isNull(marker))) {
								map.addOverlay(marker);
								}
<?php 						} else { ?>
							var point = new GLatLng(<?php print $unit_row['lat'];?>, <?php print $unit_row['lng'];?>);	//  840 for each responder 832
							var unit_id = <?php print $unit_row['unit_id'];?>;
							bounds.extend(point);																// point into BB
							var marker = createMarker(point, sidebar_line, myinfoTabs,<?php print $unit_row['type'];?>, i, unit_id);	// (point,sidebar,tabs, color, id)
							if (!(isNull(marker))) {
								map.addOverlay(marker);
								}
<?php
							}	// end check for facilities entered in no maps mode 8/4/10	
						}				// end if ($has_coords) 
					else {
						print "\n\t\t\t\tdo_sidebar(sidebar_line, color, i);\n";
						}		// end if/else ($has_coords)
					$i++;
					$k++;
					}				// end major while ($unit_row = ...)  for each responder
				print "\t\t var start = 1;\n";	// already sorted - 3/24/10		
				}				// end if(mysql_affected_rows()>0)
			else {
				print "\t\t var start = 0;\n";	// already sorted - 3/24/10
				}			
				
	//					responders complete
			if(($row_ticket['lat']==0.999999) && ($row_ticket['lng']==0.999999)) {	// check for facilities entered in no maps mode 8/4/10
	?>
				var point = new GLatLng(<?php print get_variable('def_lat'); ?>, <?php print get_variable('def_lng'); ?>);	// incident
				var baseIcon = new GIcon();
				var inc_icon = new GIcon(baseIcon, "./our_icons/question1.png", null);		// 10/26/08
				var thisMarker = new GMarker(point);
				map.addOverlay(thisMarker);				
<?php } else { ?>			
				var point = new GLatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>);	// incident
				var baseIcon = new GIcon();
				var inc_icon = new GIcon(baseIcon, "./markers/sm_black.png", null);		// 10/26/08
				var thisMarker = new GMarker(point);
				map.addOverlay(thisMarker);			
<?php } ?>			

	
			if (nr_units==0) {
				side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><BR /><H3>No <?php print get_text("Units");?>!</H3></TD></TR>";	
				map.setCenter(new GLatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>), <?php echo get_variable('def_zoom'); ?>);
				}
			else {
				center = bounds.getCenter();
				zoom = map.getBoundsZoomLevel(bounds);		// -1 for further out	
//				var radii = new Array (0, 1, 2, 3, 4, 5,   6,   7,  8,  9, 10, 11, 12, 13, 14, 15, 16, 17) ;
				var radii = new Array (100, 100, 100, 100, 50, 50, 50, 50, 40, 20, 10, 5, 5, 5,  5,  5,  5,  5) ;	// miles
				var the_rad = radii[zoom];
 				drawCircle(<?php print $row_position['lat'];?>,  <?php print $row_position['lng'];?>, the_rad, "#000080", 1, 0.75, "#0000FF", .05);				
				map.setCenter(center,zoom);
				side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>\n";
				side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>\n";
				side_bar_html+= "<TR><TD>&nbsp;</TD></TR>\n";
				}
					
			side_bar_html +="</TABLE>\n";
			document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side bar div
	
			var thelat = <?php print $lat;?>; var thelng = <?php print $lng;?>;
	
			var norecfac = "";	//10/6/09
	
			if (start>0) {
	
				var current_id= "R"+start;			//
				document.getElementById(current_id).style.visibility = "visible";		// show link check image at the selected sidebar el ement
				$("mail_button").style.display = "none";	//10/6/09
				if (lats[start]) {
<?php
					if ($rec_fac > 0) {					
?>				
						var thereclat = <?php print $rf_lat;?>; var thereclng = <?php print $rf_lng;?>;	//adds in receiving facility
						if (direcs[start]) {
							setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, thereclat + " " + thereclng, "en_US", unit_id);	// get directions	10/6/09
							}
<?php
						} 
					else {
?>			
						if (direcs[start]) {
							setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, norecfac, "en_US", unit_id);	// get directions	10/6/09
							}
<?php
						}		// end if/else ($rec_fac > 0)
?>
					}		// end if (lats[start]) 
				}		// end if (start>0)
<?php
// ======================================
?>

				location.href = "#top";				// 11/12/09	
						
			}		// end if (GBrowserIsCompatible())
	
		else {
			alert("Sorry,  browser compatibility problem. Contact your tech support group.");
			}
	
		</SCRIPT>
		
<?php
		}			// end function do_list() ===========================================================
?>
