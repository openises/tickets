<?php
//	{						-- dummy
require_once('istest.inc.php');
require_once('mysql.inc.php');
require_once("phpcoord.php");				// UTM converter	
/* constants - do NOT change */
$GLOBALS['STATUS_CLOSED'] 			= 1;
$GLOBALS['STATUS_OPEN']   			= 2;
$GLOBALS['NOTIFY_ACTION'] 			= 'Added Action';
$GLOBALS['NOTIFY_TICKET'] 			= 'Ticket Update';
$GLOBALS['ACTION_DESCRIPTION']		= 1;
$GLOBALS['ACTION_OPEN'] 			= 2;
$GLOBALS['ACTION_CLOSE'] 			= 3;
$GLOBALS['PATIENT_OPEN'] 			= 4;
$GLOBALS['PATIENT_CLOSE'] 			= 5;

//$GLOBALS['ACTION_OWNER'] 			= 4;
//$GLOBALS['ACTION_PROBLEMSTART'] 	= 5;
//$GLOBALS['ACTION_PROBLEMEND'] 	= 6;
//$GLOBALS['ACTION_AFFECTED'] 		= 7;
//$GLOBALS['ACTION_SCOPE'] 			= 8;
//$GLOBALS['ACTION_SEVERITY']		= 9;

$GLOBALS['ACTION_COMMENT']			= 10;
$GLOBALS['SEVERITY_NORMAL'] 		= 0;
$GLOBALS['SEVERITY_MEDIUM'] 		= 1;
$GLOBALS['SEVERITY_HIGH'] 			= 2;
$GLOBALS['LEVEL_ADMINISTRATOR'] 	= 1;
$GLOBALS['LEVEL_USER'] 				= 2;
$GLOBALS['LEVEL_GUEST'] 			= 3;

$GLOBALS['TYPE_MEDS']				= 1; // added by AS
$GLOBALS['TYPE_FIRE'] 				= 2;
$GLOBALS['TYPE_COPS'] 				= 3;
$GLOBALS['TYPE_OTHR'] 				= 4;

$evenodd = array ("even", "odd");	// class names for alternating table row colors

/* connect to mysql database */
mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_passwd']) or do_error('functions.inc.php::mysql_open()', 'mysql_connect() failed', mysql_error(),basename( __FILE__),__LINE__);
mysql_select_db($GLOBALS['mysql_db']) or do_error('functions.inc.php::mysql_select_db()', 'mysql_select_db() failed', mysql_error(), basename( __FILE__), __LINE__);
/* check for mysql tables, if non-existent, point to install.php */
/*		bypass 11/5/07
$failed = 0;
if (!mysql_table_exists("$GLOBALS[mysql_prefix]ticket")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]ticket' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]action")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]action' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]patient")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]patient' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]notify")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]notify' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]settings")) 	{ print "MySQL table '$GLOBALS[mysql_prefix]settings' is missing<BR />"; $failed = 1; 	}
if (!mysql_table_exists("$GLOBALS[mysql_prefix]user")) 		{ print "MySQL table '$GLOBALS[mysql_prefix]user' is missing<BR />"; $failed = 1; 	}
if ($failed) {
	print "Some or several tables missing in database, please run <a href=\"install.php\">install.php</a> if you haven't or check your database.";
	exit();
	}
*/	
function mysql_table_exists($table) {/* check if mysql table exists */
	$query = "SELECT COUNT(*) FROM $table";
	$result = mysql_query($query);
	$num_rows = @mysql_num_rows($result);
	if($num_rows)
		return TRUE;
	else
		return FALSE;
	}

function get_issue_date($id){
	$result = mysql_query("SELECT date FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id'");
	$row = mysql_fetch_array($result);
	print $row[date];
	}

function check_for_rows($query) {		/* check sql query for returning rows, courtesy of Micah Snyder */
	if($sql = mysql_query($query)) {
		if(mysql_num_rows($sql) !== 0)
			return mysql_num_rows($sql);
		else
			return false;
		}
	else
		return false;
	}

//	} { -- dummy

function list_tickets($sort_by_field='',$sort_value='') {	// list tickets ===================================================
	$get_status = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['status'])))) ) ? "" : $_GET['status'] ;
	$get_sortby = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['sortby'])))) ) ? "" : $_GET['sortby'] ;
	$get_offset = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['offset'])))) ) ? "" : $_GET['offset'] ;

	$closed = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_CLOSED']))? "Closed" : "";
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

?>
<TABLE BORDER=0>
	<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'>Current <?php print $closed; ?> Run Tickets</FONT></TD></TR>
	<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>
<!--	<TR><TD WIDTH = 360 VALIGN='TOP' ><DIV ID='side_bar'></DIV></TD> -->
	<TR><TD VALIGN='TOP' ><DIV ID='side_bar'></DIV></TD>			
		<TD></TD>			
		<TD CLASS='td_label'><DIV ID='map' STYLE='WIDTH: 512PX; HEIGHT: 450PX'></DIV>	
		<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />
		Units: <A HREF="#" onClick = "hideGroup(0)">	<IMG SRC = './markers/sm_yellow.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Incident Priority:&nbsp;&nbsp;&nbsp;&nbsp;
		Typical: <A HREF="#" onClick = "hideGroup(1)">	<IMG SRC = './markers/sm_blue.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		High: <A HREF="#" onClick = "hideGroup(2)">		<IMG SRC = './markers/sm_green.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		Highest: <A HREF="#" onClick = "hideGroup(3)">	<IMG SRC = './markers/sm_red.png' BORDER=0></A>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN ID="allIcons" STYLE="visibility: hidden">Show all: <A HREF="#" onClick = "showAll()"><IMG SRC = './markers/sm_white.png' BORDER=0></A></CENTER><BR /></TD>
		
	</TR>
	<TR><TD COLSPAN='99'> </TD></TR>
	<TR><TD CLASS='td_label' COLSPAN=3 ALIGN='center'>
		&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:shoreas@Gmail.com?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		</TD></TR></TABLE>
	<FORM NAME='view_form' METHOD='get' ACTION='config.php'>
	<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
	<INPUT TYPE='hidden' NAME='view' VALUE='true'>
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

<SCRIPT>

if (GBrowserIsCompatible()) {
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

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (line_no) + ". "+ sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
//		alert (151);
//		alert (v_id);
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
//		alert (157);
//		alert (id);
		GEvent.trigger(gmarkers[id], "click");
		}

	function do_sidebar (instr, id) {								// constructs sidebar row
		side_bar_html += "<TR CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='td_label'>" + (id) + ". "+ instr +"</TD></TR>\n";
		}		// end function do_sidebar ()

	function createMarker(point, tabs, color, id) {					// Creates marker and sets up click event infowindow
		points = true;
		var icon = new GIcon(baseIcon);
		icon.image = icons[color] + ((id % 100)) + ".png";			// e.g.,marker9.png, 100 icons limit
//		alert(color + " " + icon.image);
		var marker = new GMarker(point, icon);	
		marker.id = color;				// for hide/unhide
		
		GEvent.addListener(marker, "click", function() {			// here for both side bar and icon click
//			alert(178);
			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();			
			marker.openInfoWindowTabsHtml(infoTabs[id]);
//			alert(183);
			var dMapDiv = document.getElementById("detailmap");
			var detailmap = new GMap2(dMapDiv);
			detailmap.addControl(new GSmallMapControl());
			detailmap.setCenter(point, 12);  						// larger # = closer
			detailmap.addOverlay(marker);
			});

		gmarkers[id] = marker;							// marker to array for side_bar click function
		infoTabs[id] = tabs;							// tabs to array
		
		bounds.extend(point);										// extend the bounding box
		
		return marker;
		}				// end function create Marker()
	function doGrid() {
		map.closeInfoWindow();
		map.addOverlay(new LatLonGraticule());
		}	
		
	var icons=[];						// note globals
	icons[0] = 											   "./markers/YellowIcons/marker";	// tickets icons - e.g.,marker9.png
	icons[<?php print $GLOBALS['SEVERITY_NORMAL']; ?>+1] = "./markers/BlueIcons/marker";	
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM']; ?>+1] = "./markers/GreenIcons/marker";
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =   "./markers/RedIcons/marker";		//	BlueIcons/GreenIcons/YellowIcons/RedIcons

	var map;
	var center;
	var zoom;
	var points = false;
	
	var side_bar_html = "<TABLE border=0 CLASS='sidebar'>";
	side_bar_html += "<tr class='even'><td colspan=5 align='center'>Click for information</td></tr>";
	side_bar_html += "<tr class='odd'><td></td><td align='center'><B>Incident</B></td><td>P</td><td>A</td><td align='center'>As of</td></tr>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>
	
	var bounds = new GLatLngBounds();						// create  bounding box
	map.addControl(new GOverviewMapControl());

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);
	map.enableScrollWheelZoom(); 	
	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
//		alert (center);
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])		
		});	
				 
<?php
	$get_status = (!empty ($get_status))? $get_status : $GLOBALS['STATUS_OPEN'];			 						 // default to show all open tickets
//	$order_by =  ((!empty ($_GET)) && ($_GET['sortby'] == ''))? $_SESSION['sortorder']: $order_by = $_GET['sortby']; // use default sort order?
	$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
//	dump ($_SESSION['sortorder']);
																			//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($_SESSION['ticket_per_page'] && (check_for_rows("SELECT id FROM $GLOBALS[mysql_prefix]ticket") > $_SESSION['ticket_per_page']))	{
		if ($_GET['offset']) {
			$limit = "LIMIT $_GET[offset],$_SESSION[ticket_per_page]";
			}
		else {
			$limit = "LIMIT 0,$_SESSION[ticket_per_page]";
			}
		}
	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$where = ($get_status==2)? " WHERE `status`='2' OR (`status`='1'  AND `problemend` > (NOW() - INTERVAL 24 HOUR)) ": " WHERE `status`='1' ";

	if ($sort_by_field && $sort_value) {					//sort by field?
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]ticket WHERE $sort_by_field='$sort_value' $restrict_ticket ORDER BY $order_by";
		print __LINE__ . "<BR />";
		}
	else {
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]ticket $where $restrict_ticket ORDER BY $order_by $limit";
		}
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

							// major while ... starts here
							
	while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
		if ($row['description'] == '') $row['description'] = '[no description]';
		if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
			if (strlen($row['description']) > get_variable('abbreviate_description')) {
				$row['description'] = substr($row['description'],0,get_variable('abbreviate_description')).'...';
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
			default: 							$severityclass=''; break;
			}
		
		$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
		
		$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>$street</TD></TR>";
		$utm = get_variable('UTM');
		if ($utm==1) {
			$coords =  $row['lat'] . "," . $row['lng'];
			$tab_1 .= "<TR CLASS='even'><TD>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>";
			}
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>";
		$tab_1 .= 	"&nbsp;&nbsp;<A HREF='main.php?id=" . $row['id'] . "'><U>Show details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
		if (!(is_guest() && get_variable('guest_add_ticket')==0)) {			
			$tab_1 .= 	"<A HREF='patient.php?ticket_id=" . $row['id']."'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tab_1 .= 	"<A HREF='action.php?ticket_id=" . $row['id'] . "'><U>Add Action</U></A>&nbsp;&nbsp;";
			}
		$tab_1 .= 	"</TD></TR><TABLE>";
		

		$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
		$tab_2 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 120) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
		$tab_2 .= "<TR CLASS='odd'><TD>Comments:</TD><TD>" . shorten($row['comments'], 120) . "</TD></TR>";
		$tab_2 .= "<TR><TD>&nbsp;</TD></TR>";
		$tab_2 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'>";
		$tab_2 .= 	"&nbsp;&nbsp;<A HREF='main.php?id=" . $row['id'] . "'><U>Show details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
		if (!(is_guest() && get_variable('guest_add_ticket')==0)) {			
			$tab_2 .= 	"<A HREF='patient.php?ticket_id=" . $row['id'] . "'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tab_2 .= 	"<A HREF='action.php?ticket_id=" . $row['id'] . "'><U>Add Action</U></A>&nbsp;&nbsp;";
			}
		$tab_2 .= 	"</TD></TR><TABLE>";
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `ticket_id` = " . $row['id'];
		$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$A = mysql_affected_rows();
		
		$query= "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE `ticket_id` = " . $row['id'];
		$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$P = mysql_affected_rows ();

		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
			
		$sidebar_line = "<TD CLASS='$severityclass'>$strike" . shorten($row['scope'], 30) . " $strikend</TD><TD CLASS='td_data'> " . $P;
		$sidebar_line .= " </TD><TD CLASS='td_data'> " . $A . " </TD><TD CLASS='td_data'> " . format_sb_date($row['updated']) . "</TD>";
?>
		var myinfoTabs = [
			new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
			new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $tab_2);?>"),
			new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
			];

		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket	
		bounds.extend(point);																// point into BB
		i++;																				// step the index
	
		var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, i);	// (point,tabs, color, id) 
		do_sidebar ("<?php print $sidebar_line;?>", i)		
		map.addOverlay(marker);
<?php

		}				// end tickets while ($row = ...) start responders while ($row = ...)
?>
		side_bar_html += (i>0)? "": "<TR CLASS='odd'><TD COLSPAN='5' ALIGN='center'><BR /><B>No <?php print $closed; ?> tickets!</B><BR /><BR /></TD></TR>";
// ==============================================================================================================
		points = false;			
		i++

<?php
	$types = array();	$types[$GLOBALS['TYPE_MEDS']] = "Medical";	$types[$GLOBALS['TYPE_FIRE']] = "Fire";
						$types[$GLOBALS['TYPE_COPS']] = "Police";	$types[$GLOBALS['TYPE_OTHR']] = "Other";

	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	print (mysql_affected_rows()==0)? "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center' COLSPAN=99><B>No Units!</B></TD></TR>\"\n" : "\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='center' COLSPAN=2><B>Unit</B></TD><TD>M</TD><TD></TD></TR>\"\n" ;
	
	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");			// major while ... for RESPONDER data starts here
							
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$toedit = (is_guest())? "" : "<A HREF='config.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
		$mobile = ($row['mobile']==1);
//		dump ($mobile);
		if (!$mobile) {
			$mode = ($row['lat']==0)? 4 :  0;				// valid?
?>
		var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// mobile position

<?php
			}
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
		$the_bull = ($mode == 0)? "" : "<B><FONT COLOR=" . $bulls[$mode] .">&bull;</FONT></B>";
			
		$sidebar_line = "<TD COLSPAN=2>" . shorten($row['name'], 30) . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . $the_bull . "</TD>";
		$sidebar_line .= "<TD CLASS='td_data'> " . format_sb_date($row['updated']) . "</TD>";
?>

		var do_map = true;		// default
		
<?php
		$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $row['status'] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>Details:&nbsp;&nbsp;&nbsp;&nbsp;" . $toedit . "<A HREF='config.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
		$tab_1 .= "<TABLE>";

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
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $rowtr['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD>		<TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD>	<TD>" . $rowtr['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD>		<TD>" . $rowtr['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD>		<TD>" . format_date($rowtr['packet_date']) . "</TD></TR>";
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
//				alert(<?php print $row['type'];?>);
//				var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i);	// (point,tabs, color, id)
				var marker = createMarker(point, myinfoTabs,0, i);	// (point,tabs, color, id)	// yellow for responders
				map.addOverlay(marker);
				}
			i++;				// zero-based
<?php

		}				// end major while ($row = ...) for each responder
?>
	if (!points) {		// any?
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds);
		map.setCenter(center,zoom);
		}
<?php
	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=5 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'>&bull;</FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'>&bull;</FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'>&bull;</FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'>&bull;</FONT></TD></TR>";
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div

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
	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}
	
//	if (get_variable('restrict_user_tickets') && !(is_administrator()))
//		$restrict_user = "AND owner='$_SESSION[user_id]'";
	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
//	$result = mysql_query("SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]ticket WHERE ID='$id' $restrict_user") or do_error('show_ticket()::mysql_query()', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$result = mysql_query("SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]ticket WHERE ID='$id' $restrict_ticket") or do_error('show_ticket()::mysql_query()', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">No such ticket or user access to ticket is denied</FONT>";
		exit();
		}
	
	$row = stripslashes_deep(mysql_fetch_array($result));
	if ($print == 'true') {
		print "<TABLE BORDER='0' CLASS='print_TD' width='800px'>";		
		print "<TR><TD CLASS='print_TD'><B>Incident</B>:</TD>	<TD CLASS='print_TD'>" . $row['scope'].	"</TD></TR>\n"; 
		print "<TR><TD CLASS='print_TD'><B>ID</B>:</TD>			<TD CLASS='print_TD'>" . $row['id'].	"</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Written</B>:</TD>	<TD CLASS='print_TD'>" . format_date($row['date']) . "</TD></TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Updated</B>:</TD>	<TD CLASS='print_TD'>" . format_date($row['updated']) . "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Reported by</B>:</TD><TD CLASS='print_TD'>" . $row['contact'].	"</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Phone</B>:</TD>		<TD CLASS='print_TD'>" . format_phone($row['phone']) ."</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Status:</B></TD>		<TD CLASS='print_TD'>" . get_status($row['status'])."</TD></TR>\n";
		print "<TR><TD CLASS='print_TD' COLSPAN='2'></TD></TR>\n";

		print "<TR><TD CLASS='print_TD'><B>Address</B>:</TD>	<TD CLASS='print_TD'>" . $row['street']. "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>City</B>:</TD>		<TD CLASS='print_TD'>" . $row['city']. "&nbsp;&nbsp;&nbsp;&nbsp;<B>St</B>:" . $row['state'] . "</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Priority:</B></TD>	<TD CLASS='print_TD'>" . get_severity($row['severity']).	"</TD></TR>\n";
		print "<TR><TD CLASS='print_TD'><B>Description:</B></TD><TD CLASS='print_TD'>" . nl2br ($row['description']). "</TD></TR>";
		print "<TR><TD CLASS='print_TD'><B>Comments:</B></TD>	<TD CLASS='print_TD'>" . nl2br ($row['comments']). "</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Owner:</B></TD>		<TD CLASS='print_TD'>" . get_owner($row['owner']). "</TD></TR>\n"; 
		print "<TR><TD CLASS='print_TD'><B>Issued:</B></TD>		<TD CLASS='print_TD'>" . format_date($row['date']). "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Run Start:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemstart']). "</TD></TR>";
		print "<TR><TD CLASS='print_TD'><B>Run End:</B></TD>	<TD CLASS='print_TD'>" . format_date($row['problemend']).	"</TD></TR>";
/*		print "<TR><TD CLASS='print_TD'><B>Affected:</B></TD>	<TD CLASS='print_TD'>" . $row['affected']. "</TD></TR>\n"; */
		print "<TR><TD CLASS='print_TD'><B>Map</B>:</TD>		<TD CLASS='print_TD'>&nbsp;&nbsp;<B>Lat</B>: " . $row['lat']. "&nbsp;&nbsp;&nbsp;&nbsp; <B>Lon</B>: " . $row['lng'] . "</TD></TR>\n"; 

		print show_actions($row['id'], "date", FALSE, FALSE);		// lists actions and patient data, print
		
		print "</BODY></HTML>";
		return;
		}		// end if ($print == 'true')
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left" WIDTH="<?php print $_SESSION['scr_width']-32; ?>">
	<TR VALIGN="top"><TD CLASS="print_TD" ALIGN="left">
<?php

	print do_ticket($row, "500px", $search = FALSE) ;
	
	print "<TD ALIGN='left'>";
//	print "<TABLE ID='theMap'><TR CLASS='odd' ><TD  ALIGN='center'><DIV ID='map' STYLE='WIDTH:" . ($_SESSION['scr_width']-32)/2 . "px; HEIGHT: 450PX'></DIV><BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A></TD></TR></TABLE>\n";
	print "<TABLE ID='theMap'><TR CLASS='odd' ><TD  ALIGN='center'><DIV ID='map' STYLE='WIDTH:512px; HEIGHT: 450PX'></DIV><BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A></TD></TR></TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];	

	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "</TD></TR></TABLE>\n";
?>
	<SCRIPT SRC="graticule.js" type="text/javascript"></SCRIPT>
	<SCRIPT>

	function doGrid() {
		map.addOverlay(new LatLonGraticule());
		}
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
	var icons=[];						// note globals
	icons[<?php print $GLOBALS['SEVERITY_NORMAL']; ?>] = "./markers/BlueIcons/blank.png";	
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM']; ?>] = "./markers/GreenIcons/blank.png";
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =   "./markers/RedIcons/blank.png";
	
	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";
	
	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());
	
	map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>),14);
	var icon = new GIcon(baseIcon);
	icon.image = icons[<?php print $row['severity'];?>];		
	var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom(); 	

<?php
	$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;

	$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
	$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
	$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
	$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $street . " </TD></TR>";
	$tab_1 .= "<TABLE>";
?>
	map.openInfoWindowHtml(point, "<?php print $tab_1;?>");		
	
	GEvent.addListener(map, "click", function(marker, point) {
		if (point) {
			map.clearOverlays();
			var thisMarker = new GMarker(point);
			map.addOverlay(thisMarker);
			document.getElementById("newlat").innerHTML = point.lat().toFixed(6);
			document.getElementById("newlng").innerHTML = point.lng().toFixed(6);
			
			var nlat = document.getElementById("newlat").innerHTML ;
			var nlng = document.getElementById("newlng").innerHTML ;
			var olat = document.getElementById("oldlat").innerHTML ;
			var olng = document.getElementById("oldlng").innerHTML ;
		
			var km=distCosineLaw(parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng));
			var dist = ((km * km2feet).toFixed(0)).toString();
			var dist1 = dist/5280;
			var dist2 = (dist>5280)? ((dist/5280).toFixed(2) + " mi") : dist + " ft" ;
			
			document.getElementById("range").innerHTML  = dist2;
			document.getElementById("brng").innerHTML = (brng (parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng)).toFixed(0)) + ' degr';

			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
			map.addOverlay(new GMarker(point, icon));
			var polyline = new GPolyline([
			    new GLatLng(nlat, nlng),
			    new GLatLng(olat, olng)
				], "#FF0000", 2);
			map.addOverlay(polyline);			
			}
			
		} )
	
	</SCRIPT>
<?php
	}				// end function show_ticket() =======================================================
//	} {		-- dummy

function do_ticket($theRow, $theWidth, $search=FALSE) {						// returns table
	$print = "<TABLE BORDER='0'ID='left' width='" . $theWidth . "'>\n";		// 
	
	$print .= "<TR CLASS='even'><TD CLASS='td_data' COLSPAN=2 ALIGN='center'><B>Incident <I>" . $theRow['scope'] . "</I></B></TD></TR>\n"; 
	$print .= "<TR CLASS='odd' ><TD>ID:</TD>			<TD VALIGN='top'>" . $theRow['id'] . "</TD></TD></TR>\n";
	$print .= "<TR CLASS='even'><TD>Written:</TD>		<TD>" . format_date($theRow['date']) . "</TD></TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD>Updated:</TD>		<TD>" . format_date($theRow['updated']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD>Reported by:</TD>	<TD>" . $theRow['contact'] . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD>Phone:</TD>			<TD>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'><TD>Status:</TD>		<TD>" . get_status($theRow['status']) . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD COLSPAN='2'>&nbsp;	<TD></TR>\n";			// separator
	$print .= "<TR CLASS='even' ><TD>Address:</TD>		<TD>" . highlight($search, $theRow['street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD>City:</TD>			<TD>" . highlight($search, $theRow['city']);
	$print .=	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;" . highlight($search, $theRow['state']) . "</TD></TR>\n";

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass=''; break;
		}
	
	$print .= "<TR CLASS='even' ><TD>Priority:</TD>					<TD CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd'  VALIGN='top'><TD>Description:</TD>	<TD>" . highlight($search, nl2br($theRow['description'])) . "</TD></TR>\n";
	$print .= "<TR CLASS='even'  VALIGN='top'><TD>Comments:</TD>	<TD>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";

	$print .= "<TR CLASS='odd' ><TD>Run Start:</TD>					<TD>" . format_date($theRow['problemstart']);
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($theRow['problemend']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD>Map:</TD>						<TD>&nbsp;&nbsp;Lat:&nbsp;&nbsp;<SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;Lon:&nbsp;&nbsp; <SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>\n";
	$utm = get_variable('UTM');
	
	if ($utm==1) {
		$coords =  $theRow['lat'] . "," . $theRow['lng'];
		$print .= "<TR CLASS='even'  VALIGN='top'><TD>UTM:</TD>		<TD>" . toUTM($coords) . "<TD></TD></TR>\n";
		}
	//				Northing 4508427,	Easting 380578, Zone 17T

	$print .= "<TR ID='point' CLASS='even' STYLE = 'display:none;'><TD>Point:</TD><TD>&nbsp;&nbsp;Lat:&nbsp;&nbsp; <SPAN ID='newlat'></SPAN>
		&nbsp;&nbsp;Lon:&nbsp;&nbsp; <SPAN ID='newlng'></SPAN></TD></TR>\n";

	$print .= "<TR ID='point' CLASS='odd' STYLE = 'visibility:visible;'><TD>Point:</TD><TD ALIGN='center'>&nbsp;&nbsp;Range:&nbsp;&nbsp; <SPAN ID='range'>na</SPAN>
		&nbsp;&nbsp;Brng:&nbsp;&nbsp; <SPAN ID='brng'>na</SPAN></TD></TR>\n";
	$print .= "<TR><TD COLSPAN=2 ALIGN='center'><BR />Click map point for distance information.</TD></TR>";

	$print .= "</TABLE>\n";
	return $print;
	}
	
//	} {		-- dummy

function show_actions ($the_id, $theSort="date", $links, $display) {			/* list actions and patient data belonging to ticket */
//	global $evenodd;
	if ($display) {
		$evenodd = array ("even", "odd");		// class names for display table row colors
		}
	else {
		$evenodd = array ("plain", "plain");	// print
		}
	$query = "SELECT `id`, `name` FROM $GLOBALS[mysql_prefix]responder";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$responderlist = array();
	$responderlist[0] = "NA";	
	while ($act_row = stripslashes_deep(mysql_fetch_array($result))){
		$responderlist[$act_row['id']] = $act_row['name'];
		}
	$print = "<TABLE BORDER='0' ID='patients' width='800px'>";
																	/* list patients */
	$query = "SELECT *,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]patient WHERE ticket_id='$the_id' ORDER BY date";
	$result = mysql_query($query) or do_error('show_ticket(list patient)::mysql_query()', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = "Patient: &nbsp;&nbsp;";
	$counter=0;
	while ($act_row = stripslashes_deep(mysql_fetch_array($result))){
		$print .= "<TR CLASS='" . $evenodd[$counter%2] . "' WIDTH='100%'><TD VALIGN='top' NOWRAP CLASS='td_label'>" . $caption . "</TD>";
		$print .= "<TD NOWRAP>" . $act_row['name'] . "</TD><TD NOWRAP>". format_date($act_row['updated']) . "</TD>";
		$print .= "<TD NOWRAP> by <B>".get_owner($act_row['user'])."</B>";
		
		$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'] ? "*" : "-")."</TD><TD>" . nl2br($act_row['description']) . "</TD>";
		if ($links) {
			$print .= "<TD>&nbsp;[<A HREF='patient.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
				<A HREF='patient.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</TD></TR>\n";	
				}
		$caption = "";				// once only
		$counter++;
		}
																	/* list actions */
	$query = "SELECT *,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$the_id' ORDER BY date";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {
		return "";
		}
	else {
		$caption = "Actions: &nbsp;&nbsp;";
		$counter=0;
		while ($act_row = stripslashes_deep(mysql_fetch_array($result))){
			$print .= "<TR CLASS='" . $evenodd[$counter%2] . "' WIDTH='100%'><TD VALIGN='top' NOWRAP CLASS='td_label'>$caption</TD>";
			$responders = explode (" ", trim($act_row['responder']));	// space-separated list to array
			$sep = $respstring = "";
			for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
				if (array_key_exists($responders[$i], $responderlist)) {
					$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
					$sep = "<BR />";
					}
				}
			
			$print .= "<TD NOWRAP>" . $respstring . "</TD><TD NOWRAP>".format_date($act_row['updated']) ."</TD>";
			$print .= "<TD NOWRAP>by <B>".get_owner($act_row['user'])."</B> ";
			$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'])? '*' : '-';
			$print .= "</TD><TD WIDTH='100%'>" . nl2br($act_row['description']) . "</TD>";
			if ($links) {
				$print .= "<TD><NOBR>&nbsp;[<A HREF='action.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
					<A HREF='action.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</NOBR></TD></TR>\n";	
				}
			$caption = "";
			$counter++;
			}				// end if/else (...)
		$print .= "</TABLE>\n";
		return $print;
		}
	}			// end function show_actions

// } -- dummy

function set_ticket_status($status,$id){				/* alter ticket status */
	$query = "UPDATE $GLOBALS[mysql_prefix]ticket SET status='$status' WHERE ID='$id'LIMIT 1";
	$result = mysql_query($query) or do_error("set_ticket_status(s:$status, id:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function format_date($date){							/* format date to defined type */ 
	if (good_date($date)) {	
		return date(get_variable("date_format"),$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)
	
function good_date($date) {
	return (is_string ($date) && strlen($date)==10);
	}

function format_sb_date($date){							/* format sidebar date */ 
	if (is_string ($date) && strlen($date)==10) {	
		return date("M-d H:i",$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)

function get_status($status){							/* return status text from code */
	switch($status)	{
		case 1: return 'Closed';
			break;
		case 2: return 'Open';
			break;
		default: return 'Status error';
		}
	}

function get_owner($id){								/* get owner name from id */
//	dump ($id);
	$result	= mysql_query("SELECT user FROM $GLOBALS[mysql_prefix]user WHERE id='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_array($result));
	return (mysql_affected_rows()==0 )? "unk?" : $row['user'];
//	return $row['user'];
	}

function get_severity($severity){			/* return severity string from value */
	switch($severity) {
		case $GLOBALS['SEVERITY_NORMAL']: 	return "normal"; break;
		case $GLOBALS['SEVERITY_MEDIUM']: 	return "medium"; break;
		case $GLOBALS['SEVERITY_HIGH']: 	return "high"; break;
		default: 							return "Severity error"; break;
		}
	}

function get_responder($id){			/* return responder-type string from value */
	$result	= mysql_query("SELECT `name` FROM $GLOBALS[mysql_prefix]responder WHERE id='$id' LIMIT 1") or do_error("get_responder(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$temprow	= stripslashes_deep(mysql_fetch_array($result));
	return $temprow['name'];
	}

function strip_html($html_string) {						/* strip HTML tags/special characters and fix custom ones to prevent bad HTML, CrossSiteScripting etc */
	$html_string =strip_tags(htmlspecialchars($html_string));	//strip all "real" html and convert special characters first
	
	if (!get_variable('allow_custom_tags')){
		//$html_string = str_replace('\[|\]', '', $html_string);
		//$html_string = str_replace('[b]', '', $html_string);
		//$html_string = str_replace('[/b]', '', $html_string);
		//$html_string = str_replace('[i]', '', $html_string);
		//$html_string = str_replace('[/i]', '', $html_string);
		return $html_string;
		}
	
	$html_string = str_replace('[b]', '<b>', $html_string);	//fix bolds
	$html_string = str_replace('[/b]', '</b>', $html_string);
	
	$html_string = str_replace('[i]', '<i>',$html_string);	//fix italics
	$html_string = str_replace('[/i]', '</i>', $html_string);
	
	return $html_string;
	}

function do_mail($ticket_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
	$message  = "PHP Ticket on ".get_variable('host')."\n";
	$message .= "This message has been sent to you because you are subscribed to be notified of updates to this ticket.\n\n";
	$message .= "Notify Action: $action\n";
	$message .= "Ticket ID: " . $t_row['id'] . "\n";
	$message .= "Ticket Name: " . $t_row['scope'] . "\n";
//	$message .= "Ticket Owner: ".get_owner($t_row['owner'])."\n";
	$message .= "Ticket Status: ".get_status($t_row['status'])."\n";
//	$message .= "Ticket Affected: $t_row['affected']\n";
	$message .= "Ticket Run Start: " . $t_row['problemstart'] . "\n";
	$message .= "Ticket Run End: " . $t_row['problemend'] . "\n";
	$message .= "Ticket Description: ".wordwrap($t_row['description'])."\n";
	$message .= "Ticket Comments: ".wordwrap($t_row['comments'])."\n";
	
	//add patient record to message
	if(check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE ticket_id='$ticket_id' ORDER BY DATE")){
		$message .= "\nPatient:\n";
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE ticket_id='$ticket_id'";
		$ticket_result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]action)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while($t_row = stripslashes_deep(mysql_fetch_array($ticket_result)))
			$message .= $t_row['name'] . ", " . $t_row['updated']  . "- ". wordwrap($t_row['description'])."\n";
			}
	//add actions to message
	if(check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$ticket_id' ORDER BY DATE")){
		$message .= "\nActions:\n";
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$ticket_id'";
		$ticket_result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]action)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while($t_row = stripslashes_deep(mysql_fetch_array($ticket_result)))
			$message .= $t_row['updated'] . " - ".wordwrap($t_row['description'])."\n";
			}
	
	$message .= "\nThis is an automated message, please do not reply.";
	mail($row['email_address'],'Ticket Notification', $message);
	}		// end function do_mail()

function notify_user($ticket_id,$action){	/* notify user check, $action is the action that triggered the notify, edit, close etc */
	if (get_variable('allow_notify') != '1') return;	//should we notify?
	
	$query = "SELECT * FROM $GLOBALS[mysql_prefix]notify WHERE ticket_id='$ticket_id'";	//lookup notifies in "notify" table
	$result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]notify)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_array($result))){		//is it the right action?
		if (($action == $GLOBALS['NOTIFY_ACTION'] AND $row['on_action']) OR ($action == $GLOBALS['NOTIFY_TICKET'] AND $row['on_ticket'])){

			if (strlen($row['email_address'])){			// notify by email?
				$ticket_result = mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE id='$ticket_id'") or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]ticket)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
				$message  = "PHP Ticket on ".get_variable('host')."\n";
				$message .= "This message has been sent to you because you are subscribed to be notified of updates to this ticket.\n\n";
				$message .= "Notify Action: $action\n";
				$message .= "Ticket ID: " . $t_row['id'] . "\n";
				$message .= "Ticket Name: " . $t_row['scope'] . "\n";
//				$message .= "Ticket Owner: ".get_owner($t_row['owner'])."\n";
				$message .= "Ticket Status: ".get_status($t_row['status'])."\n";
//				$message .= "Ticket Affected: $t_row['affected']\n";
				$message .= "Ticket Run Start: " . $t_row['problemstart'] . "\n";
				$message .= "Ticket Run End: " . $t_row['problemend'] . "\n";
				$message .= "Ticket Description: ".wordwrap($t_row['description'])."\n";
				$message .= "Ticket Comments: ".wordwrap($t_row['comments'])."\n";
			
				//add patient record to message
				if(check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE ticket_id='$ticket_id' ORDER BY DATE")){
					$message .= "\nPatient:\n";
					$query = "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE ticket_id='$ticket_id'";
					$ticket_result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]action)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					while($t_row = stripslashes_deep(mysql_fetch_array($ticket_result)))
						$message .= $t_row['name'] . ", " . $t_row['updated']  . "- ". wordwrap($t_row['description'])."\n";
						}
				//add actions to message
				if(check_for_rows("SELECT * FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$ticket_id' ORDER BY DATE")){
					$message .= "\nActions:\n";
					$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE ticket_id='$ticket_id'";
					$ticket_result = mysql_query($query) or do_error("notify_user(i:$ticket_id,$action)::mysql_query(SELECT FROM $GLOBALS[mysql_prefix]action)", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					while($t_row = stripslashes_deep(mysql_fetch_array($ticket_result)))
						$message .= $t_row['updated'] . " - ".wordwrap($t_row['description'])."\n";
						}
			
				$message .= "\nThis is an automated message, please do not reply.";
				mail($row['email_address'],'Ticket Notification', $message);
				}
	
			//notify by running program
			if (strlen($row['execute_path'])){	/* not done yet */
				}
			}
		else {			/* no matching action */
			return;
			}
		}
	}

$variables = array();
function get_variable($which){								/* get variable from db settings table  */
	global $variables;
	if (empty($variables)) {
		$result = mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]settings") or do_error("get_variable(n:$name)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_array($result))){
			$name = $row['name']; $value=$row['value'] ;
			$variables[$name] = $value;
			}
		}
	return $variables[$which];
	}
	
function do_login($requested_page, $outinfo = FALSE){			/* do login/session code */

	global $istest;
	session_start();
	if ($istest) {
		if (!empty($_POST)) extract($_POST);
			    else if (!empty($HTTP_POST_VARS)) extract($HTTP_POST_VARS);
		if (!empty($_GET)) extract($_GET);	    
	
		foreach ($_POST as $VarName=>$VarValue) {echo "POST:$VarName => $VarValue, <BR />";};
		foreach ($_GET as $VarName=>$VarValue) 	{echo "GET:$VarName => $VarValue, <BR />";};
		echo "<BR/>";
		}
	if(!session_is_registered('auth')){
/*
		if( stristr($_POST['pass'], "'") ||
			stristr($_POST['pass'], '"') ||
			stristr($_POST['usr'], "'") ||
			stristr($_POST['usr'], '"') ||
			stristr($_POST['usr'], '\\') ||
			stristr($_POST['pass'], '\\') ) 
*/
		if((!empty($_POST))&&(check_for_rows("SELECT user,passwd FROM $GLOBALS[mysql_prefix]user WHERE user='$_POST[frm_user]' AND passwd=PASSWORD('$_POST[frm_passwd]')"))) {
			$auth = True;
			session_register('auth');
			$query 	= "SELECT * FROM $GLOBALS[mysql_prefix]user WHERE user='" . mysql_real_escape_string($_POST['frm_user']) . "'";
			$result = mysql_query($query) or do_error("do_login(get_permissions)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row 	= stripslashes_deep(mysql_fetch_array($result));
			if ($row['sortorder'] == NULL) $row['sortorder'] = "date";
			
			$_SESSION['user_name']     		= $_POST['frm_user'];
			$_SESSION['user_id']     		= $row['id'];
			$_SESSION['level'] 				= $row['level'];
			$_SESSION['reporting']	 		= $row['reporting'];
			$_SESSION['ticket_per_page'] 	= $row['ticket_per_page'];
			$dir = ($row['sort_desc']) ? " DESC " : "";
			$_SESSION['sortorder']			= $row['sortorder'] .$dir;
			$_SESSION['scr_width']			= $_POST['scr_width'];
			$_SESSION['scr_height']			= $_POST['scr_height'];
			$_SESSION['browser']			= $_SERVER['HTTP_USER_AGENT'];	// 
			return;
			}
		else {			
			$log_file = "log.dat";
			$tab = "\t";
			$tzoffset = 5*60*60;
			$localtime=(gmdate("M d h:i a", date('U') - $tzoffset));
			
			if (!file_exists($log_file)) {
			   if ($f = fopen($log_file,"w")) fclose($f);
			   chmod ($log_file, 0666);};
			
			$lf = fopen($log_file,"a");
			$newdata = $tab . $localtime . $tab . $requested_page . $tab .gethostbyaddr($_SERVER['REMOTE_ADDR']) . $tab . $_SERVER['HTTP_USER_AGENT'] . "\n"; //
			$newdata = stripslashes($newdata);
			fwrite($lf,$newdata);
			fclose($lf);		
		
?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<HTML xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - Login Module</TITLE>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
		<META HTTP-EQUIV="Expires" CONTENT="0">
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
		<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
		<SCRIPT>
		function do_onload () {
			if(self.location.href==parent.location.href) {			// prevent frame jump
				self.location.href = 'index.php';
				}; 		
			document.login_form.scr_width.value=screen.availWidth;
			document.login_form.scr_height.value=screen.availHeight;
			}		// end function do_onload () 
<?php
	if ($outinfo) {		// clarify logout/in
?>	
			parent.frames["upper"].document.getElementById("whom").innerHTML  = "not";
			parent.frames["upper"].document.getElementById("level").innerHTML  = "na";
<?php	
		}
?>	
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "do_onload()">
		<CENTER><BR />
		<?php if(get_variable('_version') != '') print "<SPAN style='FONT-WEIGHT: bold; FONT-SIZE: 15px; COLOR: #000000;'>" . get_variable('login_banner')."</SPAN><BR /><BR />"; ?>
		</FONT><FORM METHOD="post" ACTION="<?php print $requested_page;?>" NAME="login_form">
		<TABLE BORDER="0">
		
		<?php if((!empty($_POST)) && ($_POST['frm_user'] != '')) print '<TR CLASS="odd"><TD COLSPAN="2"><FONT CLASS="warn">Login failed. Try again.</FONT></TD></TR>'; ?>
		<TR CLASS='even'><TD ROWSPAN=6 VALIGN='middle' ALIGN='left' bgcolor=#EFEFEF><BR /><BR />&nbsp;&nbsp;<IMG BORDER=0 SRC='open_source_button.png'><BR /><BR />
		&nbsp;&nbsp;<a href="http://www.openaprs.net/"><img src="http://www.openaprs.net/images/tag/openaprs.png" width="88" height="31"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD><TD CLASS="td_label">User:</TD><TD><INPUT TYPE="text" NAME="frm_user" MAXLENGTH="255" SIZE="30"></TD></TR>
		<TR CLASS='odd'><TD CLASS="td_label">Password: &nbsp;&nbsp;</TD><TD><INPUT TYPE="password" NAME="frm_passwd" MAXLENGTH="255" SIZE="30"></TD></TR>
		<TR CLASS='even'><TD></TD><TD><INPUT TYPE="submit" VALUE="Log In"></TD></TR>
		<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><BR />&nbsp;&nbsp;&nbsp;&nbsp;Visitors may login as <B>guest</B> with password <B>guest</B>.&nbsp;&nbsp;&nbsp;&nbsp;</TD></TR>
		<TR CLASS='odd'><TD COLSPAN=2>&nbsp;</TD></TR>
		<TR CLASS='even' HEIGHT = "30px" VALIGN="top"><TD COLSPAN=2 ALIGN='center' CLASS="td_label" ><BR /><A HREF="http://groups.google.com/group/open-source-cad"><U>Join our newsgroup </U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:shoreas@Gmail.com?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A><BR /><BR /></TD>
		<INPUT TYPE='hidden' NAME = 'scr_width' VALUE=''>
		<INPUT TYPE='hidden' NAME = 'scr_height' VALUE=''>
		</FORM></CENTER>
		</HTML>
<?php
			exit();
			}
		}				// end if(!session_is_registered() - return sans action if session OK
		
	}		// end function do_login()

function do_logout(){/* logout - destroy session */
	session_start();
	session_unset();
	session_destroy();
	do_login('main.php', TRUE);
	}

function do_error($err_function,$err,$custom_err='',$file='',$line=''){/* raise an error event */
	print "<FONT CLASS=\"warn\">An error occured in function '<B>$err_function</B>': '<B>$err</B>'<BR />";
	if ($file OR $line) print "Error occured in '$file' at line '$line'<BR />";
	if ($custom_err != '') print "Additional info: '<B>$custom_err</B>'<BR />";
	print '<BR />Check your MySQL connection and if the problem persist, contact the <A HREF="help.php?q=credits">author</A>.<BR />';
	die('<B>Execution stopped.</B></FONT>');
	}

function add_header($ticket_id)		{/* add header with links */
	print "<BR /><NOBR><FONT SIZE='2'>This Ticket: ";	
	if (!is_guest()){
		print "<A HREF='edit.php?id=$ticket_id'>Edit </A> | ";
		print "<A HREF='edit.php?id=$ticket_id&delete=1'>Delete </A> | ";
		if (!is_closed($ticket_id)) {
			print "<A HREF='action.php?ticket_id=$ticket_id'>Add Action</A> | ";
			print "<A HREF='patient.php?ticket_id=$ticket_id'>Add Patient</A> | ";
			}
		print "<A HREF='config.php?func=notify&id=$ticket_id'>Notify</A> | ";
		}
	print "<A HREF='main.php?print=true&id=$ticket_id'>Print </A> | ";
	print "<A HREF='#' onClick = \"window.open('mail.php', 'newWindow', 'resizable, scrollbars, height=300, width=400, left=100,top=100,screenX=100,screenY=100')\">E-mail </A> | ";
	print "<A HREF='routes.php?ticket_id=$ticket_id'>Routes</A></FONT></NOBR><BR />  ";		// new 9/22
// 	myWindow = window.open('mail.php', 'newWindow', 'resizable, scrollbars, height=600, width=900, left=100,top=100,screenX=100,screenY=100');	// IE + NN	
	}

function is_closed($id){/* is ticket closed? */
	return check_for_rows("SELECT id,status FROM $GLOBALS[mysql_prefix]ticket WHERE id='$id' AND status='$GLOBALS[STATUS_CLOSED]'");
	}

function is_administrator(){/* is user admin? */
	if ($_SESSION['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) return 1;
	}

function is_guest(){/* is user guest? */
//	if ($_SESSION['level'] == $GLOBALS['LEVEL_GUEST']) return 1;
	return ($_SESSION['level'] == $GLOBALS['LEVEL_GUEST']);
	}

function is_user(){/* is user admin? */
	if ($_SESSION['level'] == $GLOBALS['LEVEL_USER']) return 1;
	}
																	/* print date and time in dropdown menus */ 
function generate_date_dropdown($date_suffix,$default_date=0, $disabled=FALSE) {			// 'extra allows 'disabled'
	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");					// hours west of GMT
	$deltam = get_variable('delta_mins');											// align server clock minutes
	$local = mktime(date("G"), date("i")-$deltam, date("s"), date("m"), date("d"), date("Y"));

	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		$minute		= date('i',$default_date);
		$meridiem	= date('a',$default_date);
		if (get_variable('military_time')==1) 	$hour = date('H',$default_date);
		else 									$hour = date('h',$default_date);;
		}
	else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		$minute		= date('i', $local);
		$meridiem	= date('a', $local);
		if (get_variable('military_time')==1) 	$hour = date('H', $local);
		else 									$hour = date('h', $local);
		}
	print "<SELECT name='frm_year_$date_suffix' $dis_str>";
	for($i = 2007; $i < 2008; $i++){
		print "<OPTION VALUE='$i'";
		$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
		}
			
	print "</SELECT>";
	print "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
	for($i = 1; $i < 13; $i++){
		print "<OPTION VALUE='$i'";
		$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
		}
		
	print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' $dis_str>";
	for($i = 1; $i < 32; $i++){
		print "<OPTION VALUE=\"$i\"";
		$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
		}
	print "</SELECT>\n&nbsp;&nbsp;";
	
	print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
	
	print "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_hour_$date_suffix' VALUE='$hour' $dis_str>:";
	print "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_minute_$date_suffix' VALUE='$minute' $dis_str>";
//	dump (!get_variable('military_time'));
	$show_ampm = (!get_variable('military_time')==1);
	if ($show_ampm){	//put am/pm optionlist if not military time
		print "\n<SELECT NAME='frm_meridiem_$date_suffix' $dis_str><OPTION value='am'";
		if ($meridiem == 'am') print ' selected';
		print ">am</OPTION><OPTION value='pm'";
		if ($meridiem == 'pm') print ' selected';
		print ">pm</OPTION></SELECT>";
		}
	}		// end function generate_date_dropdown(

function report_action($action_type,$ticket_id,$value1='',$value2=''){/* insert reporting actions */
//	exit(); //not used in 0.7
	if (!get_variable('reporting')) return;
	
	switch($action_type)	{
//		case $GLOBALS[ACTION_AFFECTED]: $description = "Changed affected field: $value1"; break;
//		case $GLOBALS[ACTION_SCOPE]: 	$description = "Changed scope field: $value1"; break;
//		case $GLOBALS[ACTION_SEVERITY]: $description = "Changed severity from $value1 to $value2"; break;
		case $GLOBALS[ACTION_OPEN]: 	$description = "Ticket Opened"; break;
		case $GLOBALS[ACTION_CLOSED]: 	$description = "Ticket Closed"; break;
		case $GLOBALS[PATIENT_OPEN]: 	$description = "Patient Item Opened"; break;
		case $GLOBALS[PATIENT_CLOSED]: 	$description = "Patient Item Closed"; break;
		default: 						$description = "[unknown report value: $action_type]";
		}
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$query = "INSERT INTO action (date,ticket_id,action_type,description,user) VALUES('$now','$ticket_id','$action_type','$description','$_SESSION[user_id]')";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

function shorten($instring, $limit) {
	return (strlen($instring) > $limit)? substr($instring, 0, $limit-4) . "..." : $instring ;	// &#133
	}

function format_phone ($instr) {
	$temp = trim($instr);
	return  (!empty($temp))? "(" . substr ($instr, 0,3) . ") " . substr ($instr,3, 3) . "-" . substr ($instr,6, 4): "";
	}
	
function highlight($term, $string) {		// highlights search term
	$replace = "<SPAN CLASS='found'>" .$term . "</SPAN>";
	if (function_exists('str_ireplace')) {
		return str_ireplace ($term,  $replace, $string); 
		}
	else {
		return str_replace ($term,  $replace, $string); 
		}
	}

function stripslashes_deep($value) {
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);
    return $value;
	}
function trim_deep($value) {	
    $value = is_array($value) ?
                array_map('trim_deep', $value) :
                trim($value);
    return $value;
	}
function mysql_real_escape_string_deep($value) {
    $value = is_array($value) ?
                array_map('mysql_real_escape_string_deep', $value) :
                mysql_real_escape_string($value);
    return $value;
	}
function nl2brr($text) {
    return preg_replace("/\r\n|\n|\r/", "<BR />", $text);
	}

function get_level_text ($level) {
	switch ($level) {
		case $GLOBALS['LEVEL_ADMINISTRATOR'] 	: return "Admin"; break;
		case $GLOBALS['LEVEL_USER'] 			: return "User"; break;
		case $GLOBALS['LEVEL_GUEST'] 			: return "Guest"; break;;
		default 								: return "level error"; break;
		}
	}		//end function
	
function got_gmaps() {								// valid GMaps API key ?
	return (strlen(get_variable('gmaps_api_key'))==86);
	}

function mysql_format_date($indate="") {			// returns MySQL-format date given argument timestamp or default now
	if (empty($indate)) {$indate = time();}
	return date("Y-m-d H:i:s", $indate);
	}

function toUTM($coordsIn) {							// UTM converter - assume comma separator
	$temp = explode(",", $coordsIn);
	if (!count($temp)==2) {
		print __LINE__; 
		dump ($coordsIn);
		}
	$coords = new LatLng(trim($temp[0]), trim($temp[1]));	
	$utm = $coords->toUTMRef();
	$temp = $utm->toString();
	$temp1 = explode (" ", $temp);					// parse by space
	$temp2 = explode (".", $temp1[1]);				// parse by period
	$temp3 = explode (".", $temp1[2]);
	return $temp1[0] . " " . $temp2[0] . " " . $temp3[0];
	}				// end function toUTM ()
	

function output_csv($data, $filename = false){
	$csv = array();
	foreach($data as $row){
		$csv[] = implode(', ', $row);
		}
	$csv = sprintf('%s', implode("\n", $csv));

	if ( !$filename ){
		return $csv;
		}

	// Dumping output straight out to browser.

//	header('Content-Type: application/csv');
//	header('Content-Disposition: attachment; filename=' . $filename);
//	echo $csv;
//	exit;
	}


function do_aprs() {			//	populates the APRS tracks table 
								// major surgery by Randy Hammock, August 07
								// Note:	This function assumes the structure/format of APRS data as of Aug 30,2007.
								//			Contact developer with solid information regarding any change in that format.
								//
	$delay = 1;			// minimum time in minutes between APRS queries

	function mysql2timestamp($m) {
		return mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
		}
	function date_OK ($indate) {	// checks for date/time within 48 hours
		return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60); 
		}

	$when = get_variable('_aprs_time');
	if(time() < $when) {
		return;
	} else {
		$next = time() + $delay*60;
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$next' WHERE `name`='_aprs_time'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `packet_date`< (NOW() - INTERVAL 30 DAY)"; // remove ALL expired track records 
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` = 1";  // work each call sign
//		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$result	= mysql_query($query);				// skip error inserts

		while ($row = @mysql_fetch_array($result)) {	
			$url = "http://db.aprsworld.net/datamart/csv.php?call=". $row['callsign'];	
			$raw="";		
			if ($fp = @fopen($url, r)) {		
				while (!feof($fp)) $raw .= fgets($fp, 128);		
					fclose($fp);					
					}
			$raw = str_replace("\r",'',$raw);								// Strip Carriage Returns
			$data = explode ("\n",  $raw , 50 );							// Break each line
			if (count($data) > 1) {
				$data[1] = str_replace("\",\"", '|', $data[1]); 			// Convert to pipe delimited
				$data[1] = str_replace("\"", '', $data[1]);	  				// Strip remaining quotes
				$fields = explode ("|",  $data[1]);				 			// Break out the fields
				$fields = mysql_real_escape_string_deep($fields);
				if ((count($fields) == 14) && (date_OK ($fields[13])))  {	// APRS data sanity check
					$packet_id = $fields[1] . $fields[13]; 					// source, date - unique

					$query  = "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '$fields[1]' AND  `packet_date`< (NOW() - INTERVAL 24 HOUR)"; // remove expired track records this source
					$temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					$error = FALSE;

					$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`packet_id`,
															`source`,`latitude`,`longitude`,`course`,
															`speed`,`altitude`,`symbol_table`,`symbol_code`,
															`status`,`closest_city`,`mapserver_url_street`,
															`mapserver_url_regional`,`packet_date`,`updated`)
										VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
															NOW() + INTERVAL 1 MINUTE)",
											quote_smart($packet_id),
											quote_smart($fields[1]),
											quote_smart($fields[2]),
											quote_smart($fields[3]),
											quote_smart($fields[4]),
											quote_smart($fields[5]),
											quote_smart($fields[6]),
											quote_smart($fields[7]),
											quote_smart($fields[8]),
											quote_smart($fields[9]),
											quote_smart($fields[10]),
											quote_smart($fields[11]),
											quote_smart($fields[12]),
											quote_smart($fields[13]));

					$result_tr = mysql_query($query) or $error = TRUE ;
					if(!$error) {											// update as_of date/time
						$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
						$query = sprintf("UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '$now' WHERE `responder`.`callsign`= %s LIMIT 1",
							quote_smart($fields[1]));
						$result_tr = mysql_query($query);	
						}
					
					}				// end if (count()== 15)		
				}		// end for ($i...)		
		
			}		// end while ($row =...)

		}		// end else time
	}		// end function do_aprs() 

/*
9/29 quotes line 355 
11/02 corrections to list and show ticket to handle newlines in Description and Comments fields.
11/03 added function do_onload () frame jump prevention
*/
?>
