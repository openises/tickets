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
// 			alert(map.getZoom());

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
			if(($row_ticket['lat']==$GLOBALS['NM_LAT_VAL']) && ($row_ticket['lng']==$GLOBALS['NM_LAT_VAL'])) {	// check for tickets created in no-maps mode 8/4/10
				$lat = get_variable('def_lat');
				$lng = get_variable('def_lng');
			} else {
				$lat = $row_ticket['lat'];
				$lng = $row_ticket['lng'];
			} // end check for tickets created in no-maps mode
		
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
	
		var colors = new Array ('odd', 'even');
		var direcs = "";
		var Direcs = null;			// global
		var Now;
		var mystart;
		var myend;

		var theDirections;
		var textDirections;
		var current_unit;
		
		function setDirections(fromLat, fromLng, toLat, toLng, recLat, recLng, locale, unit_id, lineCalled) {
			$('mail_dir_but').style.visibility = "hiddent";			// 11/12/09	
			$("mail_button").style.display = "none";	//10/6/09
			if(window.theDirections) { window.theDirections.removeFrom(map);}
			window.current_unit = unit_id;
			window.theDirections = L.Routing.control({
				waypoints: [
					L.latLng(fromLat,fromLng),
					L.latLng(toLat,toLng)
				],
				  lineOptions: {
					styles: [
					  // Shadow
					  {color: 'black', opacity: 0.8, weight: 11},
					  // Outline
					  {color: 'green', opacity: 0.8, weight: 8},
					  // Center
					  {color: 'orange', opacity: 1, weight: 4}
					],
				}
			});
			window.theDirections.addTo(map);
			setTimeout(function() {
			direcs = $('directions').innerHTML;
			document.email_form.frm_direcs.value = textDirections;
			document.email_form.frm_u_id.value = current_unit;	
			$('mail_dir_but').style.visibility = "visible";			// 11/12/09	
			$("mail_button").style.display = "inline-block";	//10/6/09
			},3000);
			}

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
			var the_class = (lats[id])?  "emph" : "td_label";
			side_bar_html += "<TD CLASS='" + the_class + "' ALIGN='right'>" + letter + " "+ sidebar +"</TD></TR>\n";
			return null;
			}				// end function do_sidebar()

		function myclick(id, unit_id) {								// responds to side bar click
			var norecfac = "";
			if (document.getElementById(current_id)) {
				document.getElementById(current_id).style.visibility = "hidden";			// hide last check if defined
				}
			current_id= "R"+id;
			document.getElementById(current_id).style.visibility = "visible";			// show newest
			if (lats[id]) {																// position data?
<?php 				if(($lat==$GLOBALS['NM_LAT_VAL']) && ($lng==$GLOBALS['NM_LAT_VAL'])) { // test of tickets entered in no-maps mode 8/4/10
?>					
					var thelat = <?php print get_variable('def_lat');?>; var thelng = <?php print  get_variable('def_lng');?>;		// 286 - coords of click point
<?php 				} else { ?>
					var thelat = <?php print $lat;?>; 
					var thelng = <?php print $lng;?>;		// 288 - coords of click point
<?php
				} // end of test of tickets entered in no-maps mode 8/4/10 
				
				if ($row_ticket['rec_facility'] > 0) {
?>			
					var thereclat = <?php print $rf_lat;?>; 
					var thereclng = <?php print $rf_lng;?>;									//adds in receiving facility
					setDirections(lats[id], lngs[id], thelat, thelng, thereclat, thereclng, "en_US", unit_id, "Line 201");	// 296 - get directions
<?php
					} 
				else {
?>
					setDirections(lats[id], lngs[id], thelat, thelng, "", "", "en_US", unit_id, "Line 206");					// 303 - get directions
<?php
					}
?>
				}
			else {
				$('directions').innerHTML = "";							// no position data, no directions
				$('mail_dir_but').style.visibility = "hidden";			// 11/12/09	 -	
				}

			$("directions").innerHTML = "";								// prior directions no longer apply - 11/21/09
			}					// end function my click(id)	

		var starting = false;

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
			var bounds;
		    var geocoder = null;
		    var addressMarker;
			var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
				iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
				}
				});

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



			var nr_units = 	0;
			var email= false;
			
<?php	
			function get_cd_str($in_row) {			// unit row in, 
				global $unit_id;
//																			// first, already on this run?		
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE  `ticket_id` = " . get_ticket_id () . "
					 AND (`responder_id`={$in_row['unit_id']}) 
					 AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) LIMIT 1;";	// 6/25/10
//				snap(__LINE__, $query);
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
			$have_position = (!(($latitude==$GLOBALS['NM_LAT_VAL']) && ($longitude==$GLOBALS['NM_LAT_VAL'])));

// ============================= Regions Stuff	
		
			$al_groups = $_SESSION['user_groups'];

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

			$order = (($sortby_distance)&& ($have_position))? "ORDER BY `distance` ASC ": "ORDER BY `dispatch` ASC, `calls_assigned` ASC, `handle` ASC, `unit_name` ASC, `unit_id` ASC";
							
			$query = "(SELECT *, UNIX_TIMESTAMP(`updated`) AS `updated`, `r`.`handle` AS `unit_handle`, `r`.`name` AS `unit_name`, `t`.`name` AS `type_name`, `r`.`type` AS `type`, `r`.`icon_str` AS `icon_str`, `t`.`icon` AS `icon`,
				`r`.`id` AS `unit_id`, `r`.`capab` AS `capab`, `r`.`status_about` AS `status_about`,
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
				(SELECT *, UNIX_TIMESTAMP(`updated`) AS `updated`, `r`.`handle` AS `unit_handle`, `r`.`name` AS `unit_name`, `t`.`name` AS `type_name`, `r`.`type` AS `type`, `r`.`icon_str` AS `icon_str`, `t`.`icon` AS `icon`,
				`r`.`id` AS `unit_id`, `r`.`capab` AS `capab`, `r`.`status_about` AS `status_about`,
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
				{$order}";		//	6/17/13				 
				 
				 
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if(mysql_affected_rows()>0) {
			$end_date = (intval($problemend)> 1)? $problemend:  (time() - (get_variable('delta_mins')*60));
			$elapsed = my_date_diff($problemstart, $end_date);		// 5/13/10

//	==========================================================================================		
			$search_arg = ((array_key_exists('capabilities', ($_GET))))? "<TR class='even' STYLE = 'white-space:nowrap;'><TD COLSPAN='99' ALIGN='center'>" . get_text("Units") . " capabilities match: '" . $_GET['capabilities']. "'</TD></TR>": "";
?>
			var start = 1;
			var current_id= "R"+start;			//
			side_bar_html += "<TR class='even'>	<TD CLASS='<?php print $severityclass; ?>' COLSPAN=99 ALIGN='center'><B>Routes to Incident: <I><?php print shorten($row_ticket['scope'], 20) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(" . $elapsed; ?>)</I></B></TD></TR>\n";
			side_bar_html += "<?php print $search_arg;?>";
			side_bar_html += "<TR class='odd'>	<TD COLSPAN=99 ALIGN='center'>Click line, icon or map for route</TD></TR>\n";
			side_bar_html += "<TR class='even' STYLE = 'white-space:nowrap;'><TD COLSPAN=3></TD><TD ALIGN='left'><?php print get_text("Handles");?></TD><TD ALIGN='left'><?php print get_text("Units");?></TD><TD ALIGN='right'>SLD&nbsp;(<?php print $capt;?>)</TD><TD ALIGN='center'>Call</TD><TD ALIGN='left'>Status</TD><TD>M</TD><TD ALIGN='left'>As of</TD></TR>\n";
			
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
	 				direcs[i] = <?php print (intval($unit_row['direcs'])==1)? "true": "false";?>;			// DO NOT DO DIRECTIONS - 7/13/09
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
						if(($unit_row['lat']==$GLOBALS['NM_LAT_VAL']) && ($unit_row['lng']==$GLOBALS['NM_LAT_VAL'])) { // check units created in no-maps mode 8/4/10
?>	
						lats[i] = <?php print get_variable('def_lat');?>; 		// 774-1 now compute distance - in km
						lngs[i] = <?php print get_variable('def_lng');?>;
<?php 					} else { ?>
						lats[i] = <?php print $unit_row['lat'];?>; 		// 774-2 now compute distance - in km
						lngs[i] = <?php print $unit_row['lng'];?>;
<?php 					} // end check units created in no-maps mode 8/4/10  

						if(($row_ticket['lat']==$GLOBALS['NM_LAT_VAL']) && ($row_ticket['lng']==$GLOBALS['NM_LAT_VAL'])) { // check tickets created in no-maps mode 8/4/10
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
							var myinfoTabs = "<?php print $tab_1;?>";

<?php
							$thespeed = ($track_row['speed'] == 0)?"<FONT COLOR='red'><B>&bull;</B></FONT>"  : "<FONT COLOR='green'><B>&bull;</B></FONT>" ;
							if ($track_row['speed'] >= 50) { $thespeed = "<FONT COLOR='WHITE'><B>&bull;</B></FONT>";}
?>
							var point = new L.LatLng(<?php print $track_row['latitude'];?>, <?php print $track_row['longitude'];?>);
							try{bounds.extend(point);}															// point into BB
							catch(err)	{}
<?php
							}			// end if (mysql_affected_rows()>0;) for track data
						else {				// no track data
						
							$k--;			// not a clickable unit for dispatch
?>
							var myinfoTabs = "<?php print $tab_1;?>";
<?php						
							}				// end  no track data
											// 8/7/09
						}		// end if (has rem_source ... )
				
					else {				// no rem_source
						if ($has_coords) {					//  2/25/09
?>
							var myinfoTabs = "<?php print $tab_1;?>";
<?php
								if(($unit_row['lat']==$GLOBALS['NM_LAT_VAL']) && ($unit_row['lng']==$GLOBALS['NM_LAT_VAL'])) { // check units created in no-maps mode 8/4/10
?>	
									lats[i] = <?php print get_variable('def_lat');?>; 		// 819-1 now compute distance - in km
									lngs[i] = <?php print get_variable('def_lng');?>;
<?php 								} else { ?>
									lats[i] = <?php print $unit_row['lat'];?>; 		// 819-2 now compute distance - in km
									lngs[i] = <?php print $unit_row['lng'];?>;
<?php 								} // end check units created in no-maps mode 8/4/10

									if(($row_ticket['lat']==$GLOBALS['NM_LAT_VAL']) && ($row_ticket['lng']==$GLOBALS['NM_LAT_VAL'])) { // check tickets created in no-maps mode 8/4/10
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
						$can_dispatch = false;
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
						$can_dispatch = true;
?>				
						sidebar_line = "<TD ALIGN='center'><INPUT TYPE='checkbox' <?php print get_cd_str($unit_row); ?>ID='C_" + <?php print $i;?> + "' NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='show_butts(to_visible); unit_sets[<?php print $i; ?>]=this.checked;' /></TD>";
<?php
						}
?>	
<!-- 6/17/13 Added Handle to list fields -->
					sidebar_line += "<TD TITLE = \"<?php print addslashes($unit_row['unit_handle']);?>\">";
<?php
					$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$unit_row['icon']];
					$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$unit_row['icon']];
					$strike = ($can_dispatch) ? "": "color:red;text-decoration:line-through;" ;	//	6/17/13	Added check for Dispatch disallowed to strikethrough check
					$the_style = "<SPAN STYLE='" . $strike . " background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>";
?>
					sidebar_line += "<NOBR><?php print $the_style . shorten($unit_row['unit_handle'], 10);?></SPAN></NOBR></TD>";

<!-- End of Handle -->					
<?php
					$str_dist = ($have_position)? number_format(round($unit_row['distance'], 1), 1): "" ;		// 3/5/11
?>
					sidebar_line += "<TD TITLE = \"<?php print addslashes($unit_row['unit_name']);?>\">";
					sidebar_line += "<NOBR><?php print $the_style . shorten($unit_row['unit_name'], 20);?></SPAN></NOBR></TD>";
					sidebar_line += "<TD ALIGN='right'><?php print $str_dist;?></TD>"; // 8/25/08, 4/27/09
					sidebar_line += "<?php print get_assigned_td($unit_row['unit_id']); ?>";		// 3/15/11
<?php				$the_style = "<SPAN STYLE='{$strike}background-color:{$unit_row['bg_color']}; color:{$unit_row['text_color']};'>"; 
					$statusTemp = ($unit_row['status_about'] != "") ? htmlentities($unit_row['status_about'],ENT_QUOTES) : "No further status information available";
?>
					sidebar_line += "<TD TITLE = \"<?php print $statusTemp;?>\" CLASS='td_data' onMouseover=\"Tip('<?php print $statusTemp;?>');\" onMouseout='UnTip();'><?php print $the_style . shorten($unit_row['unitstatus'], 12);?></SPAN></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print substr(format_sb_date($unit_row['updated']), 4);?></TD>";
					var unit_id = <?php print $unit_row['unit_id'];?>;
<?php
					if (($has_coords)) {		//  2/25/09
						if(($unit_row['lat']==$GLOBALS['NM_LAT_VAL']) && ($unit_row['lng']==$GLOBALS['NM_LAT_VAL'])) {	// check for facilities entered in no maps mode 8/4/10
?>	
							var point = new L.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
							try{bounds.extend(point);}															// point into BB
							catch(err)	{}
							var marker = createdummyUnitMarker(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, myinfoTabs, <?php print $unit_row['icon'];?>, "<?php print $unit_row['unit_name'];?>", i);	// (point,sidebar,tabs, color, id)
							marker.addTo(map);
<?php 						} else { ?>
							var point = new L.LatLng(<?php print $unit_row['lat'];?> , <?php print $unit_row['lng'];?>);
															// point into BB
//										function createMarker(lat, lon, info, color, stat, theid, sym, category, region, tip) {
							var marker = createUnitMarker(<?php print $unit_row['lat'];?> , <?php print $unit_row['lng'];?>, myinfoTabs, <?php print $unit_row['icon'];?>, 0, i, "<?php print $unit_row['icon_str'];?>", "Incident", 0, "", "<?php print $unit_row['icon'];?>");	// (point,sidebar,tabs, color, id)
							marker.addTo(map);
							try{bounds.extend(point);}															// point into BB
							catch(err)	{}
<?php
							}	// end check for no maps mode 8/4/10	
?>
						map.fitBounds(bounds);
<?php
						print "\n\t\t\t\t do_sidebar(sidebar_line, color, i, unit_id);\n";
						}				// end if ($has_coords) 
					else {
						print "\n\t\t\t\tdo_sidebar(sidebar_line, color, i, unit_id);\n";
						}		// end if/else ($has_coords)
					$i++;
					$k++;
					}				// end major while ($unit_row = ...)  for each responder
//				print "\t\t var start = 1;\n";	// already sorted - 3/24/10		
				}				// end if(mysql_affected_rows()>0)
			else {
				print "\t\t var start = 0;\n";	// already sorted - 3/24/10
				}			
				
	//					responders complete
			if(($row_ticket['lat']==$GLOBALS['NM_LAT_VAL']) && ($row_ticket['lng']==$GLOBALS['NM_LAT_VAL'])) {	// check for tickets entered in no maps mode 8/4/10
?>
				var iconurl = "./our_icons/question1.png";		// 10/26/08
				var icon = new baseIcon({iconUrl: iconurl});
				var thisMarker = L.marker([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], {icon: icon}).bindPopup("Hello").openPopup();
				thisMarker.addTo(map);
<?php } else { ?>	
				var tick_id = "<?php print $row_ticket['id'];?>";
				var point = new L.LatLng(<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>);
				var iconurl = "./our_icons/gen_icon.php?blank=0&text=A";		
				var icon = new baseIcon({iconUrl: iconurl});	
				var thisMarker = L.marker([<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>], {icon: icon}).bindPopup("Hello").openPopup();
				thisMarker.addTo(map);
<?php } ?>			

//			alert(998);
			if (nr_units==0) {
				side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><BR /><H3>No <?php print get_text("Units");?>!</H3></TD></TR>";	
				map.setView([<?php echo $row_ticket['lat']; ?>, <?php echo $row_ticket['lng']; ?>], <?php echo get_variable('def_zoom'); ?>);
				}
			else {
			
				center = bounds.getCenter();
				zoom = map.getZoom();
				try{bounds.extend(point);}
				catch(err)	{}				
				$("loading").style.display = "none";		// 10/28/09	

				var radii = new Array (100, 100, 100, 100, 50, 50, 50, 50, 40, 20, 10, 5, 5, 5,  5,  5,  5,  5) ;	// miles
				var the_rad = radii[zoom];

				map.setView(center,zoom);
				side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>\n";
				side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>\n";
				side_bar_html+= "<TR><TD>&nbsp;</TD></TR>\n";
				}
					
			side_bar_html +="</TABLE>\n";
			document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side bar div
	
			var thelat = <?php print $lat;?>; 
			var thelng = <?php print $lng;?>;		// 970
			var norecfac = "";	//10/6/09
	
			if (start>1) {
				var current_id= "R"+start;			//
				var checkbox_id = "C_" + start;
				document.getElementById(current_id).style.visibility = "visible";		// show link check image at the selected sidebar el ement
				if (lats[start]) {
				var theUnit = unit_ids[start];
<?php
					if ($rec_fac > 0) {					
?>				
						var thereclat = <?php print $rf_lat;?>; 
						var thereclng = <?php print $rf_lng;?>;
						setDirections(lats[start], lngs[start], thelat, thelng, thereclat, thereclng, "en_US", theUnit, "Line 748");	// 985 - get directions	10/6/09
<?php
						} else {
?>	
						setDirections(lats[start], lngs[start], thelat, thelng, "", "", "en_US", theUnit, "Line 752");	// 992 - get directions	10/6/09
<?php
						}		// end if/else ($rec_fac > 0)
?>
					}		// end if (lats[start]) 
				}		// end if (start>0)
<?php
// ======================================
?>

				location.href = "#top";				// 11/12/09	
						
		</SCRIPT>
		
<?php
		}			// end function do_list() ===========================================================
?>
