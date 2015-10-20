<?php

error_reporting(E_ALL);
$facs_side_bar_height = .5;		// max height of facilities sidebar as decimal fraction of screen height - default is 0.6 (60%)
$zoom_tight = FALSE;				// replace with a decimal number to over-ride the standard default zoom setting
$iw_width= "300px";					// map infowindow with
/*
9/10/2013 New File - for locations where there are known problems.
*/

@session_start();	
session_write_close();
require_once($_SESSION['fip']);
do_login(basename(__FILE__));

$key_field_size = 30;
$st_size = (get_variable("locale") ==0)?  2: 4;		

extract($_GET);
extract($_POST);
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}


function loc_format_date($date){
	if (get_variable('locale')==1)	{return date("j/n/y H:i",$date);}
	else 							{return date(get_variable("date_format"),$date);}	// return date(get_variable("date_format"),strtotime($date));
	}				// end function fac format date
function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}

$usng = get_text('USNG');
$osgb = get_text('OSGB');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Warn Locations Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<?php
$api_key = trim(get_variable('gmaps_api_key'));
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
?>
	<SCRIPT SRC='./js/misc_function.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="./js/domready.js"		TYPE="text/javascript" ></script>
	<SCRIPT>

	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();

	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function get_new_colors() {
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	function to_str(instr) {
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);								// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
		}


	function do_usng_conv(theForm){						// usng to LL array
		tolatlng = new Array();
		USNGtoLL(theForm.frm_ngs.value, tolatlng);
		theForm.frm_lat.value = point.lat(); theForm.frm_lng.value = point.lng();
		do_lat (point.lat());
		do_lng (point.lng());
		do_ngs(theForm);
		}				// end function
		
	function do_unlock_pos(theForm) {
		theForm.frm_ngs.disabled=false;
		$("lock_p").style.visibility = "hidden";
		$("usng_link").style.textDecoration = "underline";
		}

	function do_coords(inlat, inlng) {
		if(inlat.toString().length==0) return;
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				// lat to degr, dec min's
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

	function do_lat_fmt(inlat) {
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

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && $) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && $) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}

	var starting = false;

	function whatBrows() {									//Displays the generic browser type
		window.alert("Browser is : " + type);
		}

	function ShowLayer(id, action){							// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("$('" + id + "').style.display='" + action + "'");
		}

	function hideit (elid) {
		ShowLayer(elid, "none");
		}

	function showit (elid) {
		ShowLayer(elid, "block");
		}

	function validate(theForm) {						// form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{
					theForm.submit();
					return true;}
				else 				{return false;}
				}
			}

		var errmsg="";
		if (theForm.frm_name.value.trim()=="")											{errmsg+="Location NAME is required.\n";}
		if (theForm.frm_descr.value.trim()=="")											{errmsg+="Location DESCRIPTION is required.\n";}
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {														// good to go!
//			top.upper.calls_start();
			theForm.submit();
//			return true;
			}
		}				// end function va lidate(theForm)

	function add_res () {		// turns on add responder form
		showit('loc_add_form');
		hideit('tbl_locations');
		}

// *********************************************************************

	function capWords(str){ 
		var words = str.split(" "); 
		for (var i=0 ; i < words.length ; i++){ 
			var testwd = words[i]; 
			var firLet = testwd.substr(0,1); 
			var rest = testwd.substr(1, testwd.length -1) 
			words[i] = firLet.toUpperCase() + rest 
	  	 	} 
		return( words.join(" ")); 
		} 

	function do_lat (lat) {
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].show_lat.disabled=false;
		document.forms[0].show_lat.value=do_lat_fmt(document.forms[0].frm_lat.value);
		document.forms[0].show_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);
		document.forms[0].show_lng.disabled=false;
		document.forms[0].show_lng.value=do_lng_fmt(document.forms[0].frm_lng.value);
		document.forms[0].show_lng.disabled=true;
		}

	function do_ngs() {											// LL to USNG
		var loc = <?php print get_variable('locale');?>;
		document.forms[0].frm_ngs.disabled=false;
		if(loc == 0) {
			document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
			}
		if(loc == 1) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}
		if(loc == 2) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}			
		document.forms[0].frm_ngs.disabled=true;
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
		}				// end function all ticks()

	function do_add_reset(the_form) {
		the_form.reset();
		do_ngs();
		}

	function to_top() {
		location.href = '#top';
		}
		
	function to_bottom() {
		location.href = '#bottom';
		}
		
	function add_hash(in_str) { // prepend # if absent
		return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
		}		

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}		
</SCRIPT>
<?php
function list_locations($addon = '', $start) {
	global $iw_width, $f_types, $tolerance;

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]warnings`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$locations = mysql_affected_rows()>0 ?  mysql_affected_rows(): "<I>none</I>";
	unset($result);

?>

<SCRIPT >
	var color=0;
	var colors = new Array ('odd', 'even');

	function hideDiv(div_area, hide_cont, show_cont) {
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "locs_list_sh") {
			var controlarea = "locs_list";
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

	function showDiv(div_area, hide_cont, show_cont) {
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "locs_list_sh") {
			var controlarea = "locs_list";
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
		
	function gb_handleResult(req) {							// 12/03/10	The persist callback function
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

	function do_sidebar (sidebar, id, the_class, loc_id) {
		var loc_id = loc_id;
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"'>";
		side_bar_html += "<TD CLASS='" + the_class + "' onClick = myclick(" + id + "); >" + loc_id + sidebar +"</TD></TR>\n";		// 3/15/11
		}

	function do_sidebar_nm (sidebar, line_no, id, loc_id) {	
		var loc_id = loc_id;	
		var letter = to_str(line_no);	
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"'>";
		side_bar_html += "<TD onClick = myclick_nm(" + id + "); >" + loc_id + sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		location.href = '#top';	
		}

	function do_lat (lat) {
		document.forms[0].frm_lat.value=lat.toFixed(6);
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);
		}

	function do_ngs() {											// LL to USNG
		var loc = <?php print get_variable('locale');?>;
		document.forms[0].frm_ngs.disabled=false;
		if(loc == 0) {
			document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
			}
		if(loc == 1) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}
		if(loc == 2) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}			
		document.forms[0].frm_ngs.disabled=true;
		}

<?php
$dzf = get_variable('def_zoom_fixed');
print (((my_is_int($dzf)) && ($dzf==2)) || ((my_is_int($dzf)) && ($dzf==3)))? "true;\n":"false;\n";

?>
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_locations' WIDTH='100%'>";
	side_bar_html += "<TR class='even'>	<TD WIDTH='5%'><B>ID</B></TD><TD WIDTH='30%' ALIGN='left'><B>Name</B></TD>";
	side_bar_html += "<TD WIDTH='40%' ALIGN='left'><B><?php print get_text("Street"); ?></B></TD><TD WIDTH='25%' ALIGN='left'><B><?php print get_text("As of"); ?></B></TD></TR>";
	var i = <?php print $start; ?>;					// sidebar/icon index

<?php
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` `f` ORDER BY `title` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_locations = mysql_affected_rows();
	$i=1;				// counter
// =============================================================================
	$utc = gmdate ("U");
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// ==========  major while() for Location ==========
		$the_bg_color = 	$GLOBALS['LOC_TYPES_BG'];
		$the_text_color = 	$GLOBALS['LOC_TYPES_TEXT'];
		$the_on_click = (my_is_float($row['lat']))? " onClick = myclick({$i}); " : " onClick = myclick_nm({$row['id']}); ";	//	3/15/11
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";

		if(is_guest()) {
			$toedit = $tomail = $toroute = "";
			}
		else {
			$toedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['warnlocationsfile']}?func=location&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>" ;
			}		

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			if(((float) $row['lat']==$GLOBALS['NM_LAT_VAL']) && ((float)$row['lng']==$GLOBALS['NM_LAT_VAL'])) {
			} else {
			}
			$got_point= TRUE;
			}

		$update_error = strtotime('now - 6 hours');							// set the time for silent setting
// name

		$display_name = $name = shorten(htmlentities($row['title'], ENT_QUOTES), 20);	
		$display_street = $street = shorten(htmlentities($row['street'], ENT_QUOTES), 40);			

		$sidebar_line = "&nbsp;&nbsp;<TD WIDTH='30%' TITLE = '{$row['title']}' {$the_on_click}><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>" . addslashes($name) ."</SPAN></U></TD>";	//	6/10/11
		$sidebar_line .= "<TD WIDTH='40%' TITLE = '" . addslashes($street) . "' {$the_on_click}><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'><NOBR>" . addslashes($street) ."</NOBR></SPAN></U></TD>";

// as of
		$strike = $strike_end = "";
		$the_time = $row['_on'];
		$the_class = "";
		$sidebar_line .= "<TD WIDTH='25%' CLASS='$the_class'> $strike <NOBR>" . new_format_sb_date($the_time) . "</NOBR> $strike_end</TD>";
		$name = $row['title'];	// 11/11/09		
		$temp = explode("/", $name );
		$index = substr($temp[count($temp) -1], -6, strlen($temp[count($temp) -1]));
?>
		var loc_id = "<?php print $index;?>";
		do_sidebar_nm("<?php print $sidebar_line;?>" , i, <?php print $row['id'];?>, loc_id);
<?php
	$i++;				// zero-based
	}				// end  ==========  while() for Location ==========

?>
var buttons_html = "";
<?php

	if(!empty($addon)) {
		print "\n\tbuttons_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
	$("buttons").innerHTML = buttons_html;	// append the assembled side_bar_html contents to the side_bar div
	$("num_locations").innerHTML = <?php print $num_locations;?>;

</SCRIPT>
<?php
	}				// end function list_locations() ===========================================================


	function finished ($caption) {
		print "</HEAD><BODY><!--" . __LINE__ . " -->";
		require_once('./incs/links.inc.php');	// 10/6/09
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='location'>";
		print "</FORM>\n<A NAME='bottom' />\n</BODY></HTML>";
		}

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Location - checkbox
		$query = "DELETE FROM $GLOBALS[mysql_prefix]warnings WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Location <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "0.999999" : quote_smart(trim($_POST['frm_lat'])) ;
			$the_lng = empty($_POST['frm_lng'])? "0.999999" : quote_smart(trim($_POST['frm_lng'])) ;			
			$loc_id = $_POST['frm_id'];
			$by = $_SESSION['user_id'];					// 6/4/2013
			$from = $_SERVER['REMOTE_ADDR'];			
			$query = "UPDATE `$GLOBALS[mysql_prefix]warnings` SET
				`title`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) . ",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_state'])) . ",				
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`_by`= " . 			quote_smart(trim($by)) . ",
				`_on`= " . 			quote_smart(trim($now)) . ",
				`_from`= " . 		quote_smart(trim($from)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_WARNLOCATION_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$by = $_SESSION['user_id'];		//	4/14/11
		$frm_lat = (empty($_POST['frm_lat']))? '0.999999': quote_smart(trim($_POST['frm_lat']));
		$frm_lng = (empty($_POST['frm_lng']))? '0.999999': quote_smart(trim($_POST['frm_lng']));
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$by = $_SESSION['user_id'];
		$from = $_SERVER['REMOTE_ADDR'];			
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]warnings` (
			`title`, 
			`street`, 
			`city`, 
			`state`, 
			`lat`, 
			`lng`, 
			`description`, 
			`_by`, 
			`_on`, 
			`_from` )
			VALUES (" .
			quote_smart(trim($_POST['frm_name'])) . "," .
			quote_smart(trim($_POST['frm_street'])) . "," .
			quote_smart(trim($_POST['frm_city'])) . "," .
			quote_smart(trim($_POST['frm_state'])) . "," .
			$frm_lat . "," .
			$frm_lng . "," .				
			quote_smart(trim($_POST['frm_descr'])) . "," .
			quote_smart(trim($by)) . "," .
			quote_smart(trim($now)) . "," .
			quote_smart(trim($from)) . ");";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

		do_log($GLOBALS['LOG_WARNLOCATION_ADD'], 0, mysql_insert_id(), 0);

		$caption = "<B>Location  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
?>
		</HEAD>
		<BODY onLoad = "ck_frames();">		<!-- <?php echo __LINE__; ?> -->
		<A NAME='top'>
<?php
		require_once('./incs/links.inc.php');
?>		
		<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
		<TABLE BORDER=0 ID='outer' WIDTH='80%'><TR><TD WIDTH='100%'>
		<TABLE BORDER="0" ID='addform' WIDTH='98%'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'><?php print get_text("Add Warn Location"); ?></FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>		
		<FORM NAME= "loc_add_form" METHOD="POST" ACTION="<?php print basename(__FILE__);?>?func=location&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location Name - fill in with Name of the Location"><?php print get_text("Name"); ?></A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD>
		</TR>
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>			
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Street Address - type in street address in fields or click location on map "><?php print get_text("Street"); ?></A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='odd'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.loc_add_form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_descr" COLS=60 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS='even'><TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD></TR>
		<TR CLASS = "odd"><TD COLSPAN='2' ALIGN='center'>
			<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();" STYLE = 'margin-left: 50px' >
			<INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onClick = "do_add_reset(this.form);" STYLE = 'margin-left: 20px' />
			<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>"  onClick="validate(document.loc_add_form);"  STYLE = 'margin-left: 20px' /></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		</FORM></TABLE> <!-- end inner left -->
		</TD></TR></TABLE><!-- end outer -->
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- <?php echo __LINE__;?> -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]warnings WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_assoc($result);

		$lat = $row['lat'];
		$lng = $row['lng'];
?>
		</HEAD>
		<BODY onLoad = "ck_frames(); " > 	<!-- <?php echo __LINE__; ?> -->
		<A NAME='top'>
<?php
		require_once('./incs/links.inc.php');
?>		
		<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
		<TABLE BORDER=0 ID='outer' WIDTH='80%'><TR><TD WIDTH='100%'>
		<TABLE BORDER=0 ID='editform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Warn Location '<?php print $row['title'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM METHOD="POST" NAME= "res_edit_Form" ACTION="<?php print  basename(__FILE__);?>?func=location&goedit=true">

		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location Name - fill in with Name of location">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['title'] ;?>" /></TD></TR>
		<TR class='spacer'><TD class='spacer' COLSPAN='2'>&nbsp;</TD></TR>

<?php
		$dis_rmv = " ENABLED";
?>
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Street Address - type in street address in fields or click location on map ">Location</A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='odd'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3><TEXTAREA NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete Location from system">Remove Location</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
		</TD></TR>
		<TR CLASS = "odd">
			<TD ALIGN='center'><BR>
			<TD ALIGN='center'><BR><INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!-- 11/27/09 -->
				<INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>" onClick="validate(document.res_edit_Form);"></TD></TR>
				</TD></TR>

		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		</FORM></TABLE>
		</TD></TR></TABLE>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 2431 -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['edit'])
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

		if ($_getview == 'true') {

			
			$id = $_GET['id'];
			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` WHERE `id`= " . $id . " LIMIT 1";	// 1/19/2013
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= stripslashes_deep(mysql_fetch_assoc($result));
			$lat = $row['lat'];
			$lng = $row['lng'];
			$coords =  $row['lat'] . "," . $row['lng'];		
?>
			</HEAD>
			<BODY onLoad = 'ck_frames();'>
			<A NAME='top'>
<?php
			require_once('./incs/links.inc.php');
?>
			<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
			<FONT CLASS="header">Warn Location'<?php print $row['title'] ;?>' Data</FONT> (#<?php print $row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD WIDTH='100%'>
			<TABLE BORDER=0 ID='view_location' STYLE='display: block'>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Name"); ?>: </TD>			<TD><?php print $row['title'];?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label"><?php print get_text("Location"); ?>: </TD><TD><?php print $row['street'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = 'even'><TD CLASS="td_label"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD><TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Description"); ?>: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>	<TD><?php print loc_format_date(strtotime($row['_on'])); ?></TD></TR>
<?php
			$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>
			<TR><TD>&nbsp;</TD></TR>
<?php
			if (is_administrator() || is_super()) {
?>
				<TR CLASS = "even">
					<TD COLSPAN=99 ALIGN='center'>
						<DIV style='text-align: center;'>
							<SPAN id='edit_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick= 'to_edit_Form.submit();'>Edit</SPAN>
							<SPAN id='can_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick= 'document.can_Form.submit();'>Cancel</SPAN>
						</DIV>
					</TD>
				</TR>
<?php
				}		// end if (is_administrator() || is_super())
?>
			</TABLE>
			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "<?php print basename(__FILE__);?>?func=location&edit=true&id=<?php print $id; ?>"></FORM>
							<!-- END Location VIEW -->
			<A NAME="bottom" /> 
			<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])
// ============================================= initial display =======================
		if (!isset($mapmode)) {$mapmode="a";}
?>
		</HEAD>
		<BODY onLoad = "ck_frames();" >
		<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
		<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT>		
		<A NAME='top'>
		<SPAN STYLE = 'margin-left:100px;'><?php print $caption;?></SPAN>
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;z-index: 1;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></DIV>
<?php
		require_once('./incs/links.inc.php');
		$required = 250 + (mysql_affected_rows()*40);
		$facs_side_bar_height = .9;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)		
		$the_height = (integer)  min (round($facs_side_bar_height * $_SESSION['scr_height']), $required );		// set the max	
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
			
		$heading = "Warn Locations - " . get_variable('map_caption');
?>
		<DIV style='z-index: 1;'>		
			<TABLE ID='outer' WIDTH='100%'>
				<TR CLASS='spacer'>
					<TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;
					</TD>
				</TR>
				<TR CLASS='header'>
					<TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'><?php print $heading; ?> </FONT>
					</TD>
				</TR>
				<TR CLASS='spacer'>
					<TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;
					</TD>
				</TR>				<!-- 6/10/11 -->
				<TR>
					<TD WIDTH = '100%'>
						<TABLE ID = 'sidebar' BORDER = 0 WIDTH='98%'>
							<TR class='even'>
								<TD ALIGN='center'><B>Warn Locations (<DIV id="num_locations" style="display: inline;"></DIV>)</B>
								</TD>
							</TR>
							<TR class='odd'>	
								<TD ALIGN='center'>Click line or icon for details
								</TD>
							</TR>			
							<TR>
								<TD>
									<DIV ID='side_bar' style="height: auto;  overflow-y: scroll; overflow-x: hidden;"></DIV>
								</TD>
							</TR>
							<TR class='spacer'>
								<TD class='spacer'>&nbsp;
								</TD>
							</TR>
							<TR class='spacer'>
								<TD class='spacer'>&nbsp;
								</TD>
							</TR>
							<TR>
								<TD ALIGN='center' COLSPAN=99>
									<DIV ID='buttons' style="width: 100%; text-align: center;"></DIV>
								</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>
			</TABLE>
		</DIV>	<!-- end of outer -->
		<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='location'>
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>

		<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='location'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print  basename(__FILE__);?>?func=location"></FORM>
		<!-- 1452 -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
		</BODY>				<!-- END LOCATION LIST and ADD -->
<?php
		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'>";
		if ((!(is_guest())) && (!(is_unit()))) {
			$buttons .="<INPUT TYPE='button' value= 'Add a Location'  onClick ='document.add_Form.submit();'  STYLE = 'margin-left: 60px;'>";
			}
		$buttons .= "</TD></TR>";

		print list_locations($buttons, 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		exit();
		break;
?>