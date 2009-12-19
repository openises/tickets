<?php
error_reporting(E_ALL);

// ========== Beginning of user-reviseable values ==========================================================================
//
// The following three arrays are threshold values for the List function, and control its highlighting.
// Each array is a comma-separated list of minute values, for, respectively:
//
//    Dispatched, Responding, On_scene, Facility en-route, Facility arrive, Clear, Problemend
//
// Elapsed times exceeding these values will appear in the list highlighted in red  Note that different
// values apply to incidents as a function of the three incident severities.
//
// These may be revised to suit your operation's needs.  Please do so with care - no edits are applied!
//
// =====  Dispatched, Responding, On_scene, Facility en-route, Facility arrive, Clear, Problemend  ======

$thresh_n = array(3, 20, 30, 40, 50, 60, 120);	// threshold times in minutes - normal incidents
$thresh_m = array(2, 5, 15, 15, 15, 15, 60);	// threshold times in minutes - medium-severity incidents
$thresh_h = array(1, 5, 30, 40, 50, 60, 30);	// threshold times in minutes - high-severity incidents

// ========================================================================================================
//
// Call board layout values - group percentages followed by individual columnn widths - 11/27/09
//
// ========================================================================================================


$TBL_INC_PERC = 50;		// incident group - four columns  -  50 percent as default
$TBL_UNIT_PERC = 35;	// unit group, includes checkboxes  -  35 percent as default
$TBL_CALL_PERC = 10;	// call group - three columns  -  10 percent as default
						// total shd be ~ 100
//						column width in characters - use zero to suppress display

$COLS_INCID = 18;		// incident name -  18 characters as default
$COLS_OPENED = 0;		// date/time opened -  0 characters as default
$COLS_DESCR = 100;		// incident description -  100 characters as default
$COLS_ADDR = 100;		// address -  100 characters as default

$COLS_UNIT = 15;			// unit name

$COLS_ASOF = 9;			// call as-of date/time -  9 characters as default
$COLS_USER = 3;			// last update by user xxx -  3 characters as default
$COLS_COMMENTS = 8;		// run comments -  8 characters as default

// ======== End of user-reviseable values ======================================================================

/*
	alert(<?php print __LINE__;?>);
5/23/08	fix to status_val field name
6/4/08	Deletion logic revised to remove  timed-based inactive and add explicit deletions
6/26/08	added $doTick to assign view/edit ticket functions by priv level	
8/24/08 added htmlentities function to TITLE strings
9/17/08 disallow guest edit to unit status
9/27/08 removed dead code relating to $unit_scr
9/28/08	converted TD hide/show to SPAN, to improve col alignment
10/9/08	show unit status dropdown only one time
11/7/08 incident strikethrough corrections
11/8/08 added checkboxes; correction to unit status update
1/12/09 center added for call frame, accomodate frame operation, do status update in-frame, dollar function added
1/15/09 update assigns with unit_id, added ajax functions, added script-specific CONSTANTs
1/16/08 removed 'delta' in favor of INTERVAL-based SQL, added mailit() for new dispatches
1/17/09 incident strike-through corrections
1/29/09 fixed sql stmt, TBD default comment added
2/12/09 fix select list order
2/18/09 added persistence to 'hide cleared'
2/19/09 'do all clicked buttons' added
2/20/09 show/hide changed to radio button
2/28/09 fixes to CB update
3/1/09 restrict guest updates
3/9/09 bypass email if no addr, set 'dispatched' time
3/25/09 'mailed', 'fetch_assoc' for performance, 'mailed' removed
4/11/09 apply guest checkbox restrictions
4/26/09 changes to list layout per AF, addslashes replaces htmlentities
5/11/09 added $theClass to unit name display, 'available' SELECTED if exists
5/17/09 moved buttons to floating div, with user location adjustment
5/20/09	simplified Reset button handling, hide/show cleared dispatches
5/23/09	fix to table alignment, edit 'select status' handling, bold units, relocate and visible 'times reset'
5/24/09 width fix per AH email
5/28/09 dispatch deletion added, per AF request.
5/25/09 change to sort order
6/6/09 	page refresh link added
6/16/09	show_top() added, mail win added
6/19/09 d/r case-independence added
7/27/09	synchronous AJAX call to avoid collisions
9/12/09	table cleanup, ticket descr added
10/6/09 Changed comments form field to textarea and comments field in table to text from varchar
10/6/09 Added Unit to Facility enroute and arrived status, added links button
10/20/09 strip newlines
10/29/09 $_REQUEST to $_POST (original reason unknown)
10/31/09 window height syntax, list window open corrections, is_guest() -> $guest
11/2/09 correction to insert param count
11/4/09 mileage added to add form
11/6/09 removed quote - source ???, removed old id manipulation in function our_reset()
11/27/09 revised default column widths - per AF; removed redundant form
*/
require_once('./incs/functions.inc.php'); 

$from_top = 0;		// position of 'floating' div, pixels from  top of frame
//$guest = is_guest();		// 10/31/09

if($istest) {
	dump($_POST);
	}

function show_top() {				// generates the document introduction
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - Assignments Module</TITLE>
		<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8"/>
		<META HTTP-EQUIV="Expires" 				CONTENT="0"/>
		<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
		<META HTTP-EQUIV="Script-date" 			CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
		<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
		<STYLE>
		span.even 	{ background-color: #DEE3E7;}
		.odd 	{ background-color: #EFEFEF;}
		.plain 	{ background-color: #FFFFFF;}
		TD { background-color: inherit; FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; VERTICAL-ALIGN: top;  }
		input.btn {  color:#050;  font: bold 84% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border:1px solid;  border-color: #696 #363 #363 #696;  } 

		BODY { BACKGROUND-COLOR: #EFEFEF; FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		TABLE {border-collapse: collapse; }
		#BGCOLOR {BACKGROUND-COLOR: #EFEFEF;}
		INPUT { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		SELECT { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: underline }
		A { FONT-WEIGHT: bold; FONT-SIZE: 10px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}
		li.mylink { FONT-WEIGHT: bold; FONT-SIZE: 24px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}
		TD { background-color: inherit; FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; VERTICAL-ALIGN: top;  }
		.print_TD { BACKGROUND-COLOR: #FFFFFF; FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.td_label { background-color: inherit;FONT-WEIGHT: bold; FONT-SIZE: 10px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.td_mand { FONT-WEIGHT: bold; FONT-SIZE: 10px; COLOR: #CC0000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.td_data { white-space:nowrap; background-color: inherit;FONT-SIZE: 10px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.emph { background-color: #99b2cc;FONT-SIZE: 10px; COLOR: #ffffff; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.nodir { background-color: #99b2cc;FONT-SIZE: 10px; COLOR: #ffffff; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		#td_header { FONT-WEIGHT: bold; FONT-SIZE: 15px; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.td_link { FONT-WEIGHT: bold; FONT-SIZE: 15px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; }
		.header { FONT-WEIGHT: bold; FONT-SIZE: 12pt; COLOR: #000000; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none } 
		.text { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.warn { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #CC0000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.severity_high { FONT-WEIGHT: bold; FONT-SIZE: 10px; COLOR: #C00000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.severity_medium { FONT-WEIGHT: bold; FONT-SIZE: 10px; COLOR: #008000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_green { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #009000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_orange { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #EBA500; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_blue { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #0000E0; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_red { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #C00000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_black { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_small { FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_medium { FONT-WEIGHT: normal; FONT-SIZE: 16px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.text_big { FONT-WEIGHT: normal; FONT-SIZE: 18px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.found  { BACKGROUND-COLOR: #000000; COLOR: #ffffff;}
		#detailmap, #mapDiv { font: normal 10px verdana; }
		#detailmap 	{width: 300px; height: 120px; border:1px solid gray; }
		#infowin 	{width:	300px; overflow:auto; } 
		tr.even 	{ background-color: #DEE3E7;}
		tr.odd 	{ background-color: #EFEFEF;}
		tr.plain 	{ background-color: #FFFFFF;}
		td {cursor: pointer; cursor: hand;} 
		.hovermenu ul{font: bold 13px arial;padding-left: 0;margin-left: 0;height: 20px;}
		.hovermenu ul li{list-style: none;display: inline;}
		.hovermenu ul li a{padding: 2px 0.5em;text-decoration: none;float: left;color: black;background-color: #FFF2BF;border: 2px solid #FFF2BF;}
		.hovermenu ul li a:hover{background-color: #FFE271;border-style: outset;}
		/* Apply mousedown effect only to NON IE browsers */
		html>body .hovermenu ul li a:active{ border-style: inset;}
		
		checkbox {border-width: 0px;}
		span.ok{font-weight: light; color: gray;}

		span.over {font-weight: light; color: red;}

		#bar 		{ width: auto; height: auto; background:transparent; z-index: 100; } 
		* html #bar { /*\*/position: absolute; top: expression((4 + (ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop)) + 'px'); right: expression((40 + (ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft)) + 'px');/**/ }
		#foo > #bar { position: fixed; top: 4px; right: 40px; }
		
		
				
		</STYLE>
<SCRIPT>

	function tween(in_val, min_val, max_val) {							// min and max inclusive
		if ((in_val >= min_val) && (in_val<= max_val)) return in_val;
		else {
			if (in_val >= max_val) return max_val;
			if (in_val <= min_val) return min_val;
			alert ("err 188");
			}
		}
	
	function reSizeScr(lines) {				// 140			-- 5/23/09
			var the_height = ((lines * 23)+120);
			window.resizeTo((0.98)* screen.width, tween(the_height, 260, window.screen.height - 200));		// 10/31/09 - derived via trial/error (more of the latter, mostly)
		}		// end function reSizeScr()

function do_add_btn() {							// 11/4/09
<?php
		if (intval(get_variable('call_board'))==1) {
?>		
			document.nav_form.func.value='add'; 
			document.nav_form.submit();				// 11/6/09
<?php
		}
	else {
		$url = basename(__FILE__) . "?func=add";
?>	
		newwindow_add = window.open("<?php print $url; ?>", "Email",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
		if (isNull(newwindow_add)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_add.focus();
<?php
			}		// end if/else
?>			
	 }

</SCRIPT>
				
<?php
	}		// end function show_top()

	function my_to_date($in_date) {			// date_time format to user's spec
		$temp = mktime(substr($in_date,11,2),substr($in_date,14,2),substr($in_date,17,2),substr($in_date,5,2),substr($in_date,8,2),substr($in_date,0,4));
		return (good_date_time($in_date)) ?  date(get_variable("date_format"), $temp): "";		// 
		}
	
	function my_to_date_sh($in_date) {			// short date_time string
		$temp = mktime(substr($in_date,11,2),substr($in_date,14,2),substr($in_date,17,2),substr($in_date,5,2),substr($in_date,8,2),substr($in_date,0,4));
		return (good_date_time($in_date)) ?  date("H:i", $temp): "";		// 
		}

sleep(1);		// wait for possible logout to complete	
$the_time_limit = $GLOBALS['SESSION_TIME_LIMIT'] * 60;		// seconds
$sess_key = get_sess_key();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]session` WHERE `sess_id` = '" . $sess_key . "' AND `last_in` > '" . (time()-$the_time_limit) . "' LIMIT 1";
$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

if (!mysql_affected_rows()==1) {			//logged-in?				1/13/09
	show_top() ;
?>

</HEAD>
<BODY>
<?php
require_once('./incs/links.inc.php');
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
//$guest = is_guest();		// 10/31/09

?>
<CENTER><BR><SPAN ID='start' onClick = "Javascript: self.location.href = '<?php print basename(__FILE__); ?>';"><H3>Call board waiting for login</H3></span>
</BODY>
</HTML>

<?php
		}				// end if (!mysql_affected_rows())
	else {
	
		upd_lastin();				// update session time
		
		extract($_POST);
//		$func = (!(array_key_exists('func', $_POST)))? "board" : $_POST['func'];		// array_key_exists ( mixed key, array search )
		$func = (!(array_key_exists('func', $_REQUEST)))? "board" : $_REQUEST['func'];		// array_key_exists ( mixed key, array search )

		show_top();
		$guest = is_guest();		// 10/31/09

?>	
	<SCRIPT>
	//alert (window.opener.parent.frames["upper"].document.getElementById("whom").innerHTML);
	//if ((!window.opener) || (window.opener.parent.frames["upper"].document.getElementById("whom").innerHTML == "not"))
	//		{self.location.href = 'index.php';}				// must run only as window, with user logged in
	var myuser = "<?php print isset($my_session)?$my_session['user_name']: "not";?>";
	var mylevel = "<?php print isset($my_session)?get_level_text($my_session['level']): "na";?>";
	var myscript = "<?php print isset($my_session)? LessExtension(basename( __FILE__)): "login";?>";
	
	if (!(window.opener==null)){					// 1/12/09
		try {
			window.opener.parent.frames["upper"].$("whom").innerHTML = 	myuser;
			window.opener.parent.frames["upper"].$("level").innerHTML =	mylevel;
			window.opener.parent.frames["upper"].$("script").innerHTML = myscript;
			}
		catch(e) {
			}
		}			
	function $() {									// 1/12/09
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
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	function sendRequest(url,callback,postData) {		// ajax function set - 1/15/09
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
//		req.open(method,url,true);
		req.open(method,url,false);		// synchronous, 7/27/09
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + ' " . __LINE__ . "');\n";}
//	snap(__LINE__, __FILE__);
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
	
		function editA(id) {							// edit assigns
			document.nav_form.frm_id.value=id;
<?php
			print "\t\tdocument.nav_form.func.value=";	// guest priv's = 'read-only'
			print ($guest)? "'view';" : "'edit';";
?>	
			document.nav_form.submit();
			}

		function do_mail_all_win() {			// 6/16/09
			if(starting) {return;}					
			starting=true;	
		
			newwindow_um=window.open("do_unit_mail.php", "Email",  "titlebar, resizable=1, scrollbars, height=640,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
			if (isNull(newwindow_um)) {
				alert ("This requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_um.focus();
			starting = false;
			}
	
	
		function viewT(id) {			// view ticket
			document.T_nav_form.id.value=id;
			document.T_nav_form.action='main.php';
			document.T_nav_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function editT(id) {			// edit ticket
			document.T_nav_form.id.value=id;
			document.T_nav_form.action='edit.php';
			document.T_nav_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function viewU(id) {			// view unit
			document.U_nav_form.id.value=id;
			document.U_nav_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function editU(id) {			// edit unit
			document.U_edit_form.id.value=id;
			document.U_edit_form.submit();
			if (!(window.opener==null)){window.opener.focus();}		
			}
	
		function do_assgn_reset(id, the_form) {						// 4/26/09
	
			function our_reset(id, the_form) {									// reset dispatch checks 
				var dis = <?php print ($guest)? "true": "false"; ?>;			// disallow guest actions
	
				the_form.res_times.checked = false;
				the_form.frm_dispatched.disabled = false;
				the_form.frm_dispatched.checked = false;
				the_form.frm_dispatched.disabled = dis;
				
				the_form.frm_responding.disabled = false;
				the_form.frm_responding.checked = false;
				the_form.frm_responding.disabled = dis;
				
				the_form.frm_on_scene.disabled = false;
				the_form.frm_on_scene.checked = false;
				the_form.frm_on_scene.disabled = dis;	

				the_form.frm_u2fenr.disabled = false;		//10/6/09 Unit to Facility
				the_form.frm_u2fenr.checked = false;
				the_form.frm_u2fenr.disabled = dis;	

				the_form.frm_u2farr.disabled = false;		//10/6/09 Unit to Facility
				the_form.frm_u2farr.checked = false;
				the_form.frm_u2farr.disabled = dis;				
	
				the_form.frm_clear.disabled = false;
				the_form.frm_clear.checked = false;
				the_form.frm_clear.disabled = dis;
				
//				btn_element = the_form.name+"cb";			// 11/6/09
//				$(btn_element).style.display='inline';
//				txt_element = the_form.name+"ct";
//				$(txt_element).style.display='none';
	
				var url = "assign_res.php";
				var postData = "frm_id=" + id;				// the post string
				sendRequest(url,handleResult,postData) ;
	
				}		// end function our_reset()
	
	
			function our_delete(id, the_form) {				// delete this dispatch record
				$('del_id').style.display='block';
				var url = "assign_del.php";
				var postData = "frm_id=" + id;				// the post string
				sendRequest(url,our_wrapup,postData) ;
				setTimeout('$(\'del_id\').style.display=\'none\';document.can_Form.submit();', 2000);			// show for 2 seconds
				}		// end function our_delete()

			function our_wrapup() {
				setTimeout('$(\'del_id\').style.display=\'none\';', 2000);			// show for 2 seconds

//				window.location.reload();
				document.can_Form.submit();			//  screen refresh/re-size 
				}

			var resp = "";														// 5/28/09
			while ((resp.toLowerCase() !="r") && (resp !="d")) {				// 6/19/09
				resp = prompt("Enter 'r' to Reset dispatch times\nEnter 'd' to Delete this dispatch, or press Cancel.\n", "");

				switch(resp.toLowerCase()){			// process the input
				

					case "r":
						our_reset(id, the_form);
						break;

					case "d":
						our_delete(id, the_form)
						break;

					default:			// user cancelled
						the_form.res_times.checked = false;
						return;
					}	// end switch(resp)
				}		// end while ( ... )

			the_form.res_times.checked = false;
				
			}  	// end function do_assgn_reset()

	</SCRIPT>
	
<?php
	$guest = is_guest();			// 10/31/09

	switch ($func) {
	
		case 'add': 					//  ==== { ==== first build JS array of existing assigns for dupe prevention
		print "\n<SCRIPT>\n";
		print "\t\tassigns = new Array();\n";
		
		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of FROM `$GLOBALS[mysql_prefix]assigns` ORDER BY `as_of` DESC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row = stripslashes_deep(mysql_fetch_array($result))) {
			print "\t\tassigns['" .$row['ticket_id'] .":" . $row['responder_id'] . "']=true;\n";	// build assoc array of ticket:unit pairs
			}
?>		
		function validate_ad(theForm) {
			var errmsg="";
			if (theForm.frm_ticket_id.value == "")	{errmsg+= "\tSelect Incident\n";}
			if (theForm.frm_unit_id.value == "")	{errmsg+= "\tSelect Unit\n";}
			if (theForm.frm_status_id.value == "")	{errmsg+= "\tSelect Status\n";}
			if (theForm.frm_comments.value == "")	{errmsg+= "\tComments required\n";}
			if (!(theForm.frm_miles_strt.value.trim()) =="") {							// 11/4/09
				if (!(parseInt(theForm.frm_miles_strt.value.trim()) == theForm.frm_miles_strt.value.trim())) 
													{errmsg+= "\tStart mileage error\n";}
				}
			if (!(theForm.frm_miles_end.value.trim()) =="") {
				if (!(parseInt(theForm.frm_miles_end.value.trim()) == theForm.frm_miles_end.value.trim())) 
													{errmsg+= "\tEnd mileage error\n";}
				}									
			if (assigns[theForm.frm_ticket_id.value + ":" +theForm.frm_unit_id.value]) {
										errmsg+= "\tDuplicates existing assignment\n";}

			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
				theForm.submit();
				}
			}				// end function vali date(theForm)
	
		function reSizeScr() {
<?php
			if (get_variable('call_board')==1) {print "window.resizeTo(1100,320);\n";}
?>				
			}
	
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "reSizeScr()"><CENTER>		<!-- add 1/12/09 -->
			<BR /><BR />
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="add_Form"  ACTION = "<?php print basename(__FILE__); ?>" METHOD = "post">
			<TR CLASS="even"><TH colspan=2 ALIGN="center">Assign Unit to Incident</TH></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Incident:&nbsp;</TD>
				<TD ALIGN='left'><SELECT NAME="frm_ticket_id">
					<OPTION VALUE= '' selected>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = " . $GLOBALS['STATUS_OPEN']. " ORDER BY `scope`"; 
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
					while ($row = mysql_fetch_array($result))  {
						print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['scope'] . "</OPTION>\n";		
						}
?>
					</SELECT>	
				</TD></TR>
			<TR CLASS="even" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Unit:&nbsp;</TD>
				<TD ALIGN='left'><SELECT name="frm_unit_id" onChange = "document.add_Form.frm_log_it.value='1'" >
					<OPTION value= '' selected>Select</OPTION>
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name` ASC";		// 2/12/09   
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while ($row = mysql_fetch_array($result))  {
				print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";		
				}
?>
			</SELECT></TD></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">&nbsp;&nbsp;Unit Status:&nbsp;</TD>
				<TD ALIGN='left'><SELECT name="frm_status_id"  onChange = "document.add_Form.frm_log_it.value='1'"> 
	
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$the_grp = strval(rand());			//  force initial OPTGROUP value
			$i = 0;								// 5/11/09
			$got_one = FALSE;

			while ($row = stripslashes_deep(mysql_fetch_array($result_st))) {
				if ($the_grp != $row['group']) {
					print ($i == 0)? "": "\t\t\t\t\t</OPTGROUP>\n";
					$the_grp = $row['group'];
					print "\t\t\t\t\t<OPTGROUP LABEL='$the_grp'>\n";
					}
				$sel = "";															// 5/11/09
				if (strtolower (substr($row['status_val'], 0, 5)) == "avail"){
					$sel = " SELECTED ";
					$got_one = TRUE;
					}
					
				print "\t\t\t\t\t<OPTION VALUE=' {$row['id']}'  CLASS='{$row['group']}' title='{$row['description']}' $sel> {$row['status_val']} </OPTION>\n";		// 6/23/08
	//			print "\t<OPTION VALUE=" . $row['id'] . ">" . $row['status_val'] . "</OPTION>\n";
				$i++;
				}		// end while()
			print "\t\t\t\t\t</OPTGROUP>\n";
			unset($result_st);
			if (!($got_one)){
				print "\t<OPTION VALUE=0 SELECTED >Select</OPTION>\n";
				}
?>
					</SELECT>	
				</TD></TR>
			<TR CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Comments:&nbsp;</TD>
				
				<TD ALIGN='left'><TEXTAREA NAME="frm_comments" COLS="60" ROWS="2" onFocus="Javascript:if (this.value=='TBD') {this.value='';}">TBD</TEXTAREA></TD></TR> <!-- 10/05/09 -->

			<TR CLASS='odd'><TD CLASS="td_label" ALIGN="right">Mileage:</TD> <!--11/4/09-->
				<TD colspan=3 ALIGN='center'>
					<SPAN CLASS="td_label"> Start:</SPAN> <INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_strt" VALUE="" TYPE="text" />
					<SPAN STYLE = "WIDTH: 60PX; DISPLAY: inline-block"></SPAN>
					<SPAN CLASS="td_label">End:</SPAN>
				<INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_end" VALUE="" TYPE="text" /></TD></TR>
			 </TABLE>
					 
			<INPUT TYPE='hidden' NAME='frm_by_id'	VALUE= "<?php print $my_session['user_id'];?>" />
			<INPUT TYPE='hidden' NAME='func' 		VALUE= 'add_db' />
			<INPUT TYPE='hidden' NAME='frm_log_it' 	VALUE='' />
			</FORM>
				
<?php
	if (get_variable('call_board')==1) {
		print "\t<INPUT TYPE='button' VALUE='Cancel'  CLASS = 'btn' onClick='reSizeScr();history.back();' />&nbsp;&nbsp;\n";
		}
	else {
		print "\t<INPUT TYPE='button' VALUE='Cancel'  CLASS = 'btn' onClick='window.close();' />&nbsp;&nbsp;\n";
		}
?>
				<INPUT TYPE="button" VALUE="Reset" onclick="Javascript: document.add_Form.reset();"  CLASS = 'btn' />&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="           Submit           " name="sub_but" onClick="validate_ad(document.add_Form)" CLASS = 'btn' >  
<?php	
			break;				// end case 'add' ==== } ===
			

							//	id, as_of, status_id, ticket_id, unit_id, comment, user_id
		case 'add_db' : 		// ==== { ====
		
			function handle_mail($to_str, $unit_id, $unit_name, $ticket_id) {				// 6/16/09 
//				snap(basename(__FILE__), __LINE__);
				
				$text = "";
				$the_msg = mail_it ($to_str, $text, $ticket_id, 3, TRUE);		// get default msg text
				$temp = (explode("\n", $text));
				$lines = count($temp);
											
?>
<SCRIPT>
					function handleResult(req) {				// the called-back function
<?php
		if ($istest) {print "\n\t alert('error at ' . __LINE__ . ');\n";}
?>		
			
					function send_it() {
						var url = "do_send.php";		// ($to_str, $subject_str, $text_str )
			
						var the_to = escape("<?php print $to_str; ?>");
						var the_subj = escape("New Dispatch");
						var the_msg = escape(document.add_cont_form.frm_text.value);		// the variables
			
						var postData = "to_str=" + the_to +"&subject_str=" + the_subj + "&text_str=" + the_msg; // the post string
//						sendRequest(url,handleResult,postData) ;
						sendRequest(url,dummy,postData) ;
						}		// end function send it()			
			
					function dummy() {		
						window.close();
						}
			
			
</SCRIPT>
</HEAD>
<BODY>		<!-- add_db 1/12/09 -->

					<TABLE ALIGN='center' BORDER=0><TR VALIGN='top'>
					<TD ALIGN='right'>
						<B>Notification to: <?php print $unit_name; ?></B><BR/><BR/>
						<I>edit message to suit</I>
					</TD>
					<TD>&nbsp;</TD>
					<TD ALIGN='center'>
						<FORM NAME='add_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">	<!-- 11/27/09 -->
						<TEXTAREA NAME="frm_text" COLS=60 ROWS=<?php print $lines+2; ?>><?php print mail_it ($to_str, "New", $ticket_id, 3, TRUE);?></TEXTAREA> 
					</TD>
					<TD>&nbsp;</TD>
					<TD ALIGN='left'>
						<INPUT TYPE='button' VALUE='Send message' onClick = "send_it(); setTimeout('dummy()',1000); document.can_Form.submit()"  CLASS = 'btn'><BR /><BR />
						<INPUT TYPE='button' VALUE='Do NOT send' onClick = "window.close();"  CLASS = 'btn'> 	<!-- 6/16/09 - force refresh -->
						<INPUT TYPE='hidden' NAME='func' VALUE='list'>
						</FORM>
					</TD>
					</TR></TABLE>
<?php  			
				}				// end function handle mail()
//			dump($_POST);
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = $frm_ticket_id AND `responder_id` = $frm_unit_id LIMIT 1";				
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()==0){		// prevent duplicates
						
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 		// 11/2/09, 3/9/09, 10/6/09 added start end and total miles

				$temp = trim($frm_miles_strt);				// 11/4/09
				$start_mi = (empty($temp))? 0: $temp ;
				$temp = trim($frm_miles_end);
				$end_mi = (empty($temp))? 0: $temp ;

				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `dispatched`, `status_id`, `ticket_id`, `responder_id`, `comments`, `start_miles`, `end_miles`, `user_id`)
								VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
									quote_smart($now),
									quote_smart($now),
									quote_smart($frm_status_id),
									quote_smart($frm_ticket_id),
									quote_smart($frm_unit_id),
									quote_smart($frm_comments),
									quote_smart($start_mi),
									quote_smart($end_mi),
									quote_smart($frm_by_id));
		
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
									// apply status update to unit status
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($frm_status_id) . " WHERE `id` = " .quote_smart($frm_unit_id)  ." LIMIT 1";	// 11/8/08
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		
				do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_unit_id, $frm_status_id);
				}					// end if (mysql_affected_rows()==0)
				
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " .quote_smart($frm_unit_id)  ." LIMIT 1";	// 1/29/09

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_array($result));
//			snap(basename(__FILE__), __LINE__);
			$to_str = $row['contact_via'];
			
			if (is_email($to_str)) {		// 3/9/09
				handle_mail($to_str, $frm_unit_id, $row['name'], $frm_ticket_id);
				}

//			$host  = $_SERVER['HTTP_HOST'];						// 6/26/09
//			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
//			$extra = basename(__FILE__);
//			header("Location: http://$host$uri/$extra");		// finished - reload
//			exit;				
//
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Add Complete</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<SCRIPT>
</SCRIPT>
</HEAD>
	<BODY>
		<BR><BR><CENTER><H3>Dispatch record written - email?</H3><BR><BR>
</BODY></HTML>
<?php
			break;				// end case 'add_db' ==== } =====
				
	case 'board' :			// ===== { =====

		function cb_shorten($instring, $limit) {
//			return (strlen($instring) > $limit)? substr($instring, 0, $limit-4) . "..." : $instring ;
			return (strlen($instring) > $limit)? substr($instring, 0, $limit): $instring;	// &#133
			}
																				
	if ((array_key_exists("chg_hide", $_POST)) && ($_POST['chg_hide']==1)) {			// change persistence value - 2/18/09
		$temp = $_POST['hide_cl'];
		$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `f2` ='$temp' WHERE `sess_id`='$sess_key' LIMIT 1";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
		$my_session = get_mysession();			// refresh session array
		}
?>
	<SCRIPT>
	
		function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
															// NOT correspond with what browsers actually do...
			var SAFECHARS = "0123456789" +					// Numeric
							"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
							"abcdefghijklmnopqrstuvwxyz" +	// guess
							"-_.!*'()";					// RFC2396 Mark characters
			var HEX = "0123456789ABCDEF";
		
			var encoded = "";
			for (var i = 0; i < plaintext.length; i++ ) {
				var ch = plaintext.charAt(i);
			    if (ch == " ") {
				    encoded += "+";				// x-www-urlencoded, rather than %20
				} else if (SAFECHARS.indexOf(ch) != -1) {
				    encoded += ch;
				} else {
				    var charCode = ch.charCodeAt(0);
					if (charCode > 255) {
					    alert( "Unicode Character '"
		                        + ch
		                        + "' cannot be encoded using standard URL encoding.\n" +
						          "(URL encoding only supports 8-bit characters.)\n" +
								  "A space (+) will be substituted." );
						encoded += "+";
					} else {
						encoded += "%";
						encoded += HEX.charAt((charCode >> 4) & 0xF);
						encoded += HEX.charAt(charCode & 0xF);
						}
					}
				} 			// end for(...)
			return encoded;
			};			// end function
		
		function URLDecode(encoded ){   					// Replace + with ' '
		   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
		   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
		   var i = 0;
		   while (i < encoded.length) {
		       var ch = encoded.charAt(i);
			   if (ch == "+") {
			       plaintext += " ";
				   i++;
			   } else if (ch == "%") {
					if (i < (encoded.length-2)
							&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
							&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
						plaintext += unescape( encoded.substr(i,3) );
						i += 3;
					} else {
						alert( '-- invalid escape combination near ...' + encoded.substr(i) );
						plaintext += "%[ERROR]";
						i++;
					}
				} else {
					plaintext += ch;
					i++;
					}
			} 				// end  while (...)
			return plaintext;
			};				// end function URLDecode()
		
		function syncAjax(strURL) {							// synchronous ajax function
			if (window.XMLHttpRequest) {						 
				AJAX=new XMLHttpRequest();						 
				} 
			else {																 
				AJAX=new ActiveXObject("Microsoft.XMLHTTP");
				}
			if (AJAX) {
				AJAX.open("GET", strURL, false);														 
				AJAX.send(null);							// form name
				return AJAX.responseText;																				 
				} 
			else {
				alert ("856: failed");
				return false;
				}																						 
			}		// end function sync Ajax(strURL)

		var button_live = false;
		function show_but(id) {
			if (button_live) {
				alert ("Please complete button action.");
				return false;
				}
			else {
				var theid = "TD"+id;
				elem = $(theid);
				elem.style.display = "block";
				button_live = true;
				return false;
				}
			}		// end function show_but(id)
	
		function hide_but(id) {
			var theid = "TD"+id;
			if(!$(theid)) {return false;}		// 9/17/08
			elem = $(theid);
			elem.style.display = "none";
			button_live = false;
			return false;
			}
	
		var last_form_no;
		function to_server(the_Form) {							// write unit status data via ajax xfer
			var querystr = "?frm_ticket_id=" + URLEncode(the_Form.frm_ticket_id.value.trim());
			querystr += "&frm_responder_id=" + URLEncode(the_Form.frm_responder_id.value.trim());
			querystr += "&frm_status_id=" + URLEncode(the_Form.frm_status_id.value.trim());
		
			var url = "as_up_un_status.php" + querystr;			// 
			var payload = syncAjax(url);						// 
			if (payload.substring(0,1)=="-") {	
				alert ("895: msg failed ");
				return false;
				}
			else {
	 			var bull_str = "<B>&bull;</B> ";
				var form_no = the_Form.name.substring(1);
				hide_but(form_no);								// hide the buttons
	
				if (last_form_no) {
					var elem = "myDate" + last_form_no;
					var temp = $(elem).innerHTML;
					$(elem).innerHTML = temp.substr(9);		// drop the bullet
					}
				var elem = "myDate" + form_no;
				$(elem).innerHTML = bull_str + payload;
				last_form_no = form_no;
				}				// end if/else (payload.substring(... )
			}		// end function to_server()
	
		
	function do_res() {									//  reset all forms
		for (i = 0; i< document.forms.length; i++) {
			if (document.forms[i].name.substr(0,1) == "F") {	
				document.forms[i].reset();
				}
			}
		}
		
	</SCRIPT>	
	</HEAD>
<?php
	$lines=1;
	$onload_str = (get_variable('call_board')==1)? "onLoad = 'reSizeScr({$lines})'": "";
?>
	<BODY <?php print $onload_str;?> ><!-- 947 -->
	<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
	
	<CENTER>
<?php
		function get_un_stat_sel($s_id, $b_id) {					// returns select list as string
			global $guest;
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
 			$dis = ($guest)? " DISABLED": "";								// 9/17/08
			$the_grp = strval(rand());			//  force initial OPTGROUP value
			$i = 0;
			$outstr = "\n\t\t<SELECT name='frm_status_id'  onFocus = 'show_but($b_id)' $dis >\n";
			while ($row = stripslashes_deep(mysql_fetch_array($result_st))) {
				if ($the_grp != $row['group']) {
					$outstr .= ($i == 0)? "": "\t</OPTGROUP>\n";
					$the_grp = $row['group'];
					$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>\n";
					}
				$sel = ($row['id']==$s_id)? " SELECTED": "";
				$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel .">" . $row['status_val'] . "</OPTION>\n";
				$i++;
				}		// end while()
			$outstr .= "\t\t</OPTGROUP>\n\t\t</SELECT>\n";
			return $outstr;
			unset($result_st);
			}

		$priorities = array("","severity_medium","severity_high" );
		$status_vals_ar = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE 1";
		$result_s = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_array($result_s))) {
			$sep = (empty($row['description']))? "": ":";
			$status_vals_ar[$row['id']] = $row['status_val'] . $sep . $row['description'] ;
			}
?>

	<DIV STYLE="position:fixed; width:120px; height:auto; top:0px; right: 0px; background-color:#EFEFEF;">
	
	<FORM NAME="list_win_form" onSubmit ="javascript: var cb_window=window.open('<?php print basename(__FILE__);?>?func=list', 'Callboard_List', win_spec); cb_window.focus();" METHOD="post" ACTION="<?php print basename(__FILE__);?>">
	<INPUT TYPE = "hidden" NAME="func" VALUE="list">
	</FORM>
	<SCRIPT>
		var is_guest = <?php print ($guest)? "true;\n": "false;\n"; ?>

		var win_spec = "titlebar, resizable=1, scrollbars, height=200,width=1000,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
		function open_list_win(){
//			document.list_win_form.submit();				// 10/31/09
			var cb_window=window.open('<?php print basename(__FILE__);?>?func=list', 'Callboard_List', win_spec);
			cb_window.focus();
			}		// end function
	
		function open_add_win(){
			var cb_window=window.open('<?php print basename(__FILE__);?>?func=add&close=y', 'Callboard_Add',  'titlebar, resizable=1, scrollbars, height=200,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50'); cb_window.focus();
				}		// end function
	
		function open_edit_win(the_id){
			var the_url = "<?php print basename(__FILE__);?>?func=edit&id="+ the_id;
			var cb_window=window.open(the_url, 'Callboard_Edit',  'titlebar, resizable=1, scrollbars, height=200,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50'); cb_window.focus();
				}		// end function
		function do_refresh() {
<?php
	if (get_variable('call_board')==1) 	{print "\tlocation.reload(true);\n";}
	else								{print "\tdocument.dummy_form.submit();\n";}
?>


			}		// end function
	
		function apply_all_clicked (){
			do_all();
			$('apply_btn').style.display='none'; 					// hide 'Apply all'
			$('can_btn').style.display='none'; 						// hide 'Cancel'
			$('add_btn').style.display='inline'; 					// show 'Add'. 'All Units Mail'

			$('mail_btn').style.display='inline';
			$('list_btn').style.display='inline';
			$('close_btn').style.display='inline';

			$('done_id').style.display='inline';  					// show 'Done!' for 2 seconds
			setTimeout('$(\'done_id\').style.display=\'none\';', 2000);
			}
			
		function checkbox_clicked(){								//  hide/show on any cb click 10/21/09
			$('add_btn').style.display='none';
			$('mail_btn').style.display='none';
			$('list_btn').style.display='none';
			$('close_btn').style.display='none';
			if ($('refr_btn')) {$('refr_btn').style.display='none';}
			$('apply_btn').style.display='inline';
			$('can_btn').style.display='inline';
			}
		
		function cancel_clicked(){									//  hide/show on Cancel click 10/21/09
			do_res();
			$('add_btn').style.display='inline';
			$('mail_btn').style.display='inline';
			$('list_btn').style.display='inline';
			$('close_btn').style.display='inline';
			if ($('refr_btn')) {$('refr_btn').style.display='inline';}
			$('apply_btn').style.display='none';
			$('can_btn').style.display='none';
			}

		var starting = false;						// 2/15/09
	
		function do_mail_win(addrs, ticket_id) {	// 3/27/09
			if(starting) {return;}					// dbl-click catcher
			starting=true;	
			window.blur();							// blur current window
			var url = "mail_edit.php?ticket_id=" + ticket_id + "&addrs=" + addrs + "&text=";	// no text
			var win_name = "mail_edit";
			newwindow_mail=window.open(url, win_name,  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100, top=300,screenX=100,screenY=300");
			if (isNull(win_name)) {
				alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
				return;
				}

			newwindow_mail.focus();
			starting = false;
			}		// end function do mail_win()		

		
	</SCRIPT>
	<FORM NAME='dummy_form' ACTION='index.php' TARGET='_top'></FORM>
	<P ALIGN='LEFT'>
	<DIV ID="foo"><DIV ID="bar">
		<TABLE BORDER=0 STYLE = "border-collapse:collapse;" CELLSPACING=1>
		<TR><TD COLSPAN=2><SPAN CLASS = "emph" ID = "done_id" STYLE="display:none"><B>&nbsp;Done!&nbsp;</B></SPAN></TD></TR>
		<TR><TD COLSPAN=2><SPAN CLASS = "emph" ID = "del_id"  STYLE="display:none"><B>&nbsp;Deleted!&nbsp;</B></SPAN></TD></TR>
		
		<?php if (!($guest)) { ; ?>		
		
		<TR><TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Apply all" ID = "apply_btn" onClick = "apply_all_clicked ();" STYLE="display:none" />
			</TD>
			<TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Cancel"    ID = "can_btn"   onClick = "cancel_clicked();"   STYLE="display:none" /> 
			</TD></TR>
		<TR><TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Add"       ID = "add_btn"   onClick = "do_add_btn()" STYLE="display:inline" />
			</TD>
			<TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Mail  "      ID = "mail_btn"  onClick = "do_mail_all_win();"  STYLE="display:inline" />
			</TD></TR>
		
		<?php } ?>	
		
		<TR><TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "List  "      ID = "list_btn"  onClick = "open_list_win();"    STYLE="display:inline" />		
			</TD>
			<TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Close"     ID = "close_btn" onClick = "self.close()"        STYLE="display:inline" />
			</TD></TR>
		<TR><TD ALIGN='left' COLSPAN=2><INPUT TYPE="button" CLASS="btn" VALUE = "Refresh"   ID = "refr_btn"  onClick = "do_refresh()" STYLE="display:inline" />
			</TD></TR>
		
		<?php
			$btn_text = ($my_session['f2'] == "h")? "Show": "Hide";
			$frm_val = ($my_session['f2'] == "h")? "s": "h";
		?>
		
		<TR><TD ALIGN='left' COLSPAN=2>Cleared: <SPAN onClick = "do_hors('<?php print $frm_val ;?>')"><U><?php print $btn_text ;?></U></SPAN>
		</TD></TR>
		</TABLE>
	</DIV></DIV>
	
  
</DIV>

<?php	
		switch ($my_session['f2']) {		// persistence flags 2/18/09
			case "":						// default, show
			case " ":						// 
			case "s":						

				$temp =  get_variable('closed_interval');
				if (empty($temp))	$cwi = 24;
				else				$cwi = $temp;

				$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));

				$hide_sql = " OR `clear`>= '$time_back' ";

				$butn_txt = "Hide ";
				$butn_val = "h";
			    break;
			case "h":						// hide
				$hide_sql = "";
				$butn_txt = "Show ";
				$butn_val = "s";
			    break;
			default:
			    echo "error" . __LINE__ . "\n";
			}
		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , `$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`, `t`.`description` AS `thetickdescr`, `t`.`status` AS `thestatus`,
			`r`.`id` AS `theunitid`, `r`.`name` AS `theunit` , `$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
			FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
				WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'    $hide_sql 
			ORDER BY `severity` DESC, `theticket` ASC, `theunit` ASC ";																// 5/25/09, 1/16/08

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

		$lines = mysql_affected_rows();
		print "\n<SCRIPT>\n\tvar lines = {$lines};\n</SCRIPT>\n";		// hand to JS - 5/23/09
		if ($lines == 0) {												// empty?
			
			print "<TABLE BORDER=0 ALIGN='left' WIDTH = '90%' cellspacing = 1 CELLPADDING = 1  ID='call_board' STYLE='display:block'>";
			print "<TR CLASS='even'><TD  ALIGN = 'center' WIDTH='80%'><B>Call Board</B>&nbsp;&nbsp;&nbsp;&nbsp;<FONT SIZE='-3'><I> (mouseover/click for details)</I></FONT></TD><TD WIDTH=150px></TD></TR>\n";
			print "<TR><TH ><BR /><BR /><BR />No Current Dispatches<BR /></TH><TH></TH></TR>\n";
			print "</TABLE>";
			}
		else {															// not empty

			$i = 1;	
	
			print "<TABLE BORDER=0 ALIGN='left' WIDTH='88%'  cellspacing = 1 CELLPADDING = 1 ID='call_board' STYLE='display:block'>\n";	// 5/24/09
 			print "<TR CLASS='even'><TD COLSPAN=18 ALIGN = 'center'><B>Call Board</B><FONT SIZE='-3'><I> &nbsp;&nbsp;&nbsp;&nbsp;(mouseover/click for details)</I></FONT></TD><TD WIDTH=150px></TD></TR>\n";	// 5/24/09
		
			$doUnit = ($guest)? "viewU" : "editU";
			$doTick = ($guest)? "viewT" : "editT";				// 06/26/08
			$now = time() - (get_variable('delta_mins')*60);
			$items = mysql_affected_rows();
			$header = "<TR CLASS='even'>";
			
			$header .= "<TD COLSPAN=4 ALIGN='center' CLASS='emph' WIDTH='{$TBL_INC_PERC}%'>Incident</TD>";		// 9/27/08
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD COLSPAN=9 ALIGN='center' CLASS='emph'WIDTH='{$TBL_UNIT_PERC}%'>Unit</TD>";			// 3/27/09
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD COLSPAN=4 ALIGN='center' CLASS='emph'WIDTH='{$TBL_CALL_PERC}%'>Dispatch</TD>";
			$header .= "</TR>\n";

			$header .= "<TR CLASS='odd'>";												// 4/26/09, 10/6/09 (unit to facility status)
			$header .= "<TD ALIGN='left' CLASS='emph'> " . cb_shorten("Name", $COLS_INCID) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Open", $COLS_OPENED) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Synopsis", $COLS_DESCR) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Addr", $COLS_ADDR) . "</TD>
						<TD ALIGN='center'>&nbsp;</TD>
						<TD ALIGN='left' CLASS='emph'> " . cb_shorten("Name", $COLS_UNIT) . "</TD>
						<TD ALIGN='center' TITLE='E-mail'><IMG SRC='mail.png'></TD>
						<TD ALIGN='center' TITLE= 'Dispatched'>D</TD>
						<TD ALIGN='center' TITLE= 'Responding'>R</TD>
						<TD ALIGN='center' TITLE= 'On scene'>O</TD>
						<TD ALIGN='center' TITLE= 'Fac en-route'>FE</TD>
						<TD ALIGN='center' TITLE= 'Fac arr'>FA</TD>
						<TD ALIGN='left'   TITLE= 'Clear'>Clear</TD>
						
						<TD COLSPAN=2  ALIGN='left' >&nbsp;&nbsp;&nbsp;Status</TD>
						<TD ALIGN='left' CLASS='emph'> " . cb_shorten("As of", $COLS_ASOF) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("By", $COLS_USER) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Comment", $COLS_COMMENTS) . " </TD>
						<TD ALIGN='center' TITLE='Reset unit dispatch times or Delete dispatch' width='5%'>&nbsp;R/D </TD>	<!-- 5/28/09 -->
						<TD>&nbsp;</TD>";		// 1/12/09
			$header .= "</TR>\n";

			$dis = $guest? " DISABLED ": "";				// 3/1/09

			$unit_ids = array();
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// 3/25/09
			
					if ($i == 1) {print $header;}
					$theClass = ($row['severity']=='')? "":$priorities[$row['severity']];
					print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>\n";
					print "<FORM NAME='F$i' METHOD='get' ACTION='' $dis >\n";

// 	 INCIDENTS	4 cols + sep	- 9/12/09
					if (!(empty($row['scope']))) {
						$in_strike = 	($row['thestatus']== $GLOBALS['STATUS_CLOSED'])? "<STRIKE>": "";					// 11/7/08
						$in_strikend = 	($row['thestatus']== $GLOBALS['STATUS_CLOSED'])? "</STRIKE>": "";

						$the_name = addslashes ($row['theticket']);															// 9/12/09
						$the_short_name = cb_shorten($row['theticket'], $COLS_INCID);
						print "\t<TD onClick = $doTick('{$row['ticket_id']}') CLASS='{$theClass}' 
							 onmouseover=\"Tip('[#{$row['ticket_id']}] {$the_name}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike} {$the_short_name}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
						print "<TD onmouseover=\"Tip('Opened: " . my_to_date($row['problemstart']) . "')\"  onmouseout=\"UnTip()\"  >" . cb_shorten(my_to_date_sh($row['problemstart']), $COLS_OPENED) . "</TD>\n";

	
						$the_descr = addslashes ($row['thetickdescr']);
						$the_short_one = cb_shorten($row['thetickdescr'], $COLS_DESCR);
						print "\t<TD onClick = $doTick('{$row['ticket_id']}') CLASS='{$theClass}' ALIGN='left' 
							onmouseover=\"Tip('$the_descr')\" onmouseout=\"UnTip()\">{$in_strike} {$the_short_one}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
				
						$address = (empty($row['street']))? "" : $row['street'] . ", ";
						$address = addslashes($address . $row['city']. " ". $row['state']);
						$short_addr = cb_shorten($address, $COLS_ADDR);
						print "\t<TD onClick = {$doTick}('{$row['ticket_id']}') CLASS='{$theClass}' 
							onmouseover=\"Tip('{$address}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike}{$short_addr}{$in_strikend}</TD>\n";		// address 8/24/08, 1/17/09
						}
					else {
						print "<TD COLSPAN=4>[#{$row['ticket_id']}]</TD>";				// id only if absent
						}
					print "\t<TD></TD>\n";				// 9/28/08, 4/26/09

//  UNITS			3 col's	- 9/12/09

					if (is_date($row['clear'])) {							// 6/26/08
						$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
						}
					else {
						$strike = $strikend = "";
						}			
 

					if (!($row['responder_id']==0)) {																	// 5/11/09

						$unit_name = empty($row['theunitid']) ? "[#{$row['responder_id']}]" : addslashes($row['theunit']) ;			// id only if absent
						$short_name = cb_shorten($unit_name, $COLS_UNIT);
						print "\t<TD CLASS='$theClass' onClick = {$doUnit}('{$row['responder_id']}') 
							 onmouseover=\"Tip('[#{$row['theunitid']}] {$unit_name}')\" ALIGN='left' onmouseout=\"UnTip()\"><B>{$short_name}</B></TD>\n";							// unit 8/24/08, 1/17/09
						
						print "\t<TD  CLASS='mylink' onmouseover =\"$('c{$i}').style.visibility='visible';\" onmouseout = \"$('c{$i}').style.visibility='hidden'; \" ALIGN='center'>
							\n\t<SPAN id=\"c{$i}\" style=\"visibility: hidden\">
							&nbsp;<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit {$unit_name}'. 
							 onclick = \"do_mail_win(F{$i}.frm_contact_via.value, {$row['ticket_id']}); \"> 
							</SPAN></TD>\n";		// 4/26/09
	
//		dispatched 
						$ttip_str = "";
						$show_date = (is_date($row['dispatched']))?   my_to_date($row['dispatched']) : "";					
						if ($guest) {				// 4/11/09
							$is_cd = (is_date($row['dispatched']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$ttip_str = (is_date($row['dispatched']))? " onmouseover=\"Tip('Disp: {$show_date}')\"  onmouseout=\"UnTip()\" ": "";		
						
							$is_cd = (is_date($row['dispatched']))? " CHECKED DISABLED": "  onClick = \"checkbox_clicked()\"";		// 5/20/09
							}
						print "\t<TD CLASS='$theClass' {$ttip_str} ><INPUT TYPE='checkbox' NAME='frm_dispatched' $is_cd > </TD>\n"; 
//		responding
						$ttip_str = "";
						$show_date = (is_date($row['responding']))?   my_to_date($row['responding']) : "";
						if ($guest) {				// 4/11/09
							$is_cd = (is_date($row['responding']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$ttip_str = (is_date($row['responding']))? " onmouseover=\"Tip('Resp: {$show_date}')\"  onmouseout=\"UnTip()\" ": "";		
						
							$is_cd = (is_date($row['responding']))? " CHECKED DISABLED": "  onClick = \"checkbox_clicked()\"";		// 5/20/09
							}
						print "\t<TD CLASS='$theClass' {$ttip_str} ><INPUT TYPE='checkbox' NAME='frm_responding' $is_cd > </TD>\n"; 
						
//		on_scene
						$ttip_str = "";
						$show_date = (is_date($row['on_scene']))?   my_to_date($row['on_scene']) : "";
						if ($guest) {				// 4/11/09
							$is_cd = (is_date($row['on_scene']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$ttip_str = (is_date($row['on_scene']))? " onmouseover=\"Tip('On_scene: {$show_date}')\"  onmouseout=\"UnTip()\" ": "";		
						
							$is_cd = (is_date($row['on_scene']))? " CHECKED DISABLED": "  onClick = \"checkbox_clicked()\"";		// 5/20/09
							}
						print "\t<TD CLASS='$theClass' {$ttip_str} ><INPUT TYPE='checkbox' NAME='frm_on_scene' $is_cd > </TD>\n"; 					
						
//		u2fenr
						$ttip_str = "";
						$show_date = (is_date($row['u2fenr']))?   my_to_date($row['u2fenr']) : "";
						if ($guest) {	//10/6/09
							$is_cd = (is_date($row['u2fenr']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$is_cd = (is_date($row['u2fenr']))? " CHECKED DISABLED":  " onClick = \"checkbox_clicked()\"";	
							}
						$ttip_str = (is_date($row['u2fenr']))? " onmouseover=\"Tip('Fac\'y enroute: {$show_date}')\"  onmouseout=\"UnTip()\" ": "";		

						print "\t<TD CLASS='$theClass' {$ttip_str}><INPUT TYPE='checkbox' NAME='frm_u2fenr' $is_cd > </TD>\n"; // note names!

						if ($guest) {	//10/6/09
							$is_cd = (is_date($row['u2farr']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$is_cd = (is_date($row['u2farr']))? " CHECKED DISABLED":  " onClick = \"checkbox_clicked()\"";	
							}
//		u2farr
						$ttip_str = "";
						$show_date = (is_date($row['u2farr']))?   my_to_date($row['u2farr']) : "";
						if ($guest) {				// 4/11/09
							$is_cd = (is_date($row['u2farr']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$ttip_str = (is_date($row['u2farr']))? " onmouseover=\"Tip('Fac\'y arrive: {$show_date}')\"  onmouseout=\"UnTip()\" ": "";		
						
							$is_cd = (is_date($row['u2farr']))? " CHECKED DISABLED": "  onClick = \"checkbox_clicked()\"";		// 5/20/09
							}
						print "\t<TD CLASS='$theClass' {$ttip_str} ><INPUT TYPE='checkbox' NAME='frm_u2farr' $is_cd > </TD>\n"; 

//		clear
						$ttip_str = "";
//						$the_date_str = (is_date($row['clear']))? ezDate($row['clear']): "";
						$show_date = (is_date($row['clear']))?   my_to_date($row['clear']) : "";

						if ($guest) {				// 4/11/09
							$is_cd = (is_date($row['clear']))? " CHECKED DISABLED": " DISABLED ";
							}
						else {
							$ttip_str = (is_date($row['clear']))? " onmouseover=\"Tip('Clear: {$show_date}')\"  onmouseout=\"UnTip()\" ": "";		
						
							$is_cd = (is_date($row['clear']))? " CHECKED DISABLED": "  onClick = \"checkbox_clicked()\"";		// 5/20/09
							}
						print "\t<TD CLASS='$theClass' {$ttip_str} ><INPUT TYPE='checkbox' NAME='frm_clear' $is_cd > </TD>\n"; 
						

						if (!in_array ($row['responder_id'], $unit_ids)) {				// 10/9/08
							$unit_st_val = (array_key_exists($row['un_status_id'], $status_vals_ar))? $status_vals_ar[$row["un_status_id"]]: "";
	
							print "\t<TD  onmouseover=\"Tip('{$unit_st_val}')\" TITLE= '$unit_st_val' onmouseout=\"UnTip()\">" .  get_un_stat_sel($row['un_status_id'], $i) . "</TD>\n";						// status
							
							print "\t<TD>\n\t<SPAN ID=TD$i STYLE='display:none'><INPUT TYPE='button' VALUE='Go'  CLASS = 'btn' onClick=\"to_server(F$i); window.opener.parent.frames['main'].location.reload();\">\n"; 		// 9/28/08
							print "\t<INPUT TYPE='button' VALUE='Cancel'   CLASS = 'btn' onClick=\"document.F$i.reset();hide_but($i)\"></SPAN></TD>\n";
							array_push($unit_ids, $row['responder_id']);
							}
						else {
							print "<TD COLSPAN=2></TD>";
							}
						}			// end 'got a responder'
					else {	
						print "\t<TD COLSPAN=3  CLASS='$theClass' onClick = editA(" . $row['assign_id'] . ") ID='myDate$i' ALIGN='left'><B>&nbsp;&nbsp;&nbsp;&nbsp;NA</b></TD>\n";	
						}		// end 'no responder'

					$d1 = $row['assign_as_of'];
					$d2 = mktime(substr($d1,11,2),substr($d1,14,2),substr($d1,17,2),substr($d1,5,2),substr($d1,8,2),substr($d1,0,4));

					$temp = "[#{$row['assign_id']}] " . date(get_variable("date_format"), $d2);
					
					print  "\t<TD onmouseover=\"Tip('{$temp} ')\" onmouseout=\"UnTip()\" CLASS='$theClass' 
						onClick = editA(" . $row['assign_id'] . "); ID='myDate$i' ALIGN='left' TITLE='" . 
						date("n/j y H:i", $row['as_of']) ." '>" .  $strike . 
						cb_shorten (date("H:i", $row['as_of']), $COLS_ASOF) .  $strikend . "</TD>\n";			// as of 

					print "\t<TD onmouseover=\"Tip('{$row['theuser']}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ");'>" .  $strike . cb_shorten ($row['theuser'], $COLS_USER) .  $strikend . "</TD>\n";															// user  

					$comment = addslashes (remove_nls($row['assign_comments']));

					print "\t<TD onmouseover=\"Tip('{$comment}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ")'; >" . $strike .  cb_shorten ($comment, $COLS_COMMENTS) . $strikend . "</TD>\n";	// comment

					
					print "\t<TD TITLE = 'Click to RESET D R O FE FA C times' CLASS='mylink' ALIGN='center'>
						<INPUT TYPE='radio' NAME = 'res_times' {$dis} onClick = \"do_assgn_reset({$row['assign_id']}, this.form)\" /></TD>\n";

					print "\t<INPUT TYPE='hidden' NAME='frm_the_unit' VALUE='" . addslashes($row['theunit']) . "'>\n";  
					print "\t<INPUT TYPE='hidden' NAME='frm_contact_via' VALUE='" . $row['contact_via'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_responder_id' VALUE='" . $row['responder_id'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_ticket_id' VALUE='" . $row['ticket_id'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_assign_id' VALUE='" . $row['assign_id'] . "'>\n";		// 1/12/09 
//					print "\t<INPUT TYPE='hidden' NAME='frm_mailed' VALUE='" . $row['mailed'] . "'>\n";				// 3/25/09
					print "</FORM>\n</TR>\n";
					$i++;			 
				}		// end while($row ...)
				$lines = $i;
//				snap(basename(__FILE__), __LINE__);
			}		// end if (mysql_affected_rows()>0) 

?>


<SCRIPT>

	function do_hors (str) {		// set hide/show cleared via reload - 2/20/09
		document.nav_form.hide_cl.value=str;	
		document.nav_form.chg_hide.value=1;	
		document.nav_form.func.value='board';
		document.nav_form.submit();				
		}

		var announce;					// set = false for group update $('apply_btn').style.visibility

	function handleResult(req) {			// the called-back function
		if (announce) {alert('Update complete (no e-mail sent)');}
		}			// end function handle Result(
	
	function do_sub(the_form) {				// form submitted	1/20/09, 2/28/09, 5/20/09
		var vals = sep = "";
		for (j=0; j<document.forms[the_form].elements.length; j++) {

			if (document.forms[the_form].elements[j].type == "checkbox") {
				if ((!(document.forms[the_form].elements[j].disabled) && (document.forms[the_form].elements[j].checked))) {
					document.forms[the_form].elements[j].disabled = true;
					vals+=sep + document.forms[the_form].elements[j].name;					
					sep = "%";				// safe char as separator
					}
				}

			}			// end for (j ... )
		var params = "frm_id="+ document.forms[the_form].frm_assign_id.value;				// 1/20/09
		params += "&frm_tick="+document.forms[the_form].frm_ticket_id.value;
		params += "&frm_unit="+document.forms[the_form].frm_responder_id.value;
		params += "&frm_vals="+ vals;
		sendRequest ('assigns_t.php',handleResult, params);			// does the work
		}			// end function do_sub()

	var ary_addrs = [];								// key = incident id, value = pipe-sep'd emails			
	var the_ticket_id = "";

	function do_this_form(the_index) {				// call ajax function for each clicked button
		the_val = parseInt(the_index)+1;
//		var t=setTimeout("var dummy = false",10000);
		do_sub(the_index);				// 
		if (!(document.forms[the_index].frm_contact_via.value == "")) {
			the_ticket_id = document.forms[the_index].frm_ticket_id.value;
			if (the_ticket_id in ary_addrs) {
				ary_addrs[the_ticket_id] += "|" + document.forms[the_index].frm_contact_via.value; 		// append
				}
			else {
				ary_addrs[the_ticket_id] = document.forms[the_index].frm_contact_via.value;				// else push
				}
			}
		}				// end function do_this_form()

	function do_all() {										// 2/19/09
		var do_refresh = false;								// 6/16/09
		for (i=0; i< document.forms.length; i++) {			// look at each form
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_dispatched.disabled ) && (document.forms[i].frm_dispatched.checked)) {do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_responding.disabled ) && (document.forms[i].frm_responding.checked)) {do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_on_scene.disabled ) && (document.forms[i].frm_on_scene.checked))   {do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_u2fenr.disabled ) && (document.forms[i].frm_u2fenr.checked))   {do_this_form(i);}	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_u2farr.disabled ) && (document.forms[i].frm_u2farr.checked))   {do_this_form(i);}	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_clear.disabled ) && (document.forms[i].frm_clear.checked))      {do_this_form(i); do_refresh = true;}		// 6/16/09

//			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_clear) && (!document.forms[i].frm_clear.disabled ) && (document.forms[i].frm_clear.checked)) {do_this_form(i); do_refresh = true;}		// 6/16/09
			}
		if (do_refresh) {document.can_Form.submit();}		//  at least one checked item - do screen refresh  6/16/09
		}		// end function do all()

	function clr_all_btn(){
		var a_check = false;

		for (i=0; i< document.forms.length; i++) {			// look at each form
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_dispatched.disabled ) && (document.forms[i].frm_dispatched.checked)) 		{a_check = true; }
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_responding.disabled ) && (document.forms[i].frm_responding.checked)) 		{a_check = true; }
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_on_scene.disabled ) && (document.forms[i].frm_on_scene.checked)) 			{a_check = true; }
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_u2fenr.disabled ) && (document.forms[i].frm_u2fenr.checked)) 			{a_check = true; }	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F") && (!document.forms[i].frm_u2farr.disabled ) && (document.forms[i].frm_u2farr.checked)) 			{a_check = true; }	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_clear) && (!document.forms[i].frm_clear.disabled ) && (document.forms[i].frm_clear.checked)) {a_check = true;  }
			}				// end for ( ... )
		if (!a_check){
			$('apply_btn').style.visibility='hidden'; 
			}
		}		// end function clr_all_btn()

</SCRIPT>
<?php
		break;				// end case 'board' ==== } =====
	
	case 'view' :			// read-only ====== {  =====
		if (get_variable('call_board')==1) {
?>
<SCRIPT>
	reSizeScr(16)
</SCRIPT>
<?php
			}
?>
		</HEAD>
		<BODY><CENTER>		<!-- 1268 - 1/12/09 -->
<?php	
														// if (!empty($row['clear'])) ??????
			$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, UNIX_TIMESTAMP(`dispatched`) AS `dispatched`, UNIX_TIMESTAMP(`responding`) AS `responding`, UNIX_TIMESTAMP(`on_scene`) AS `on_scene`, UNIX_TIMESTAMP(`u2fenr`) AS `u2fenr`, UNIX_TIMESTAMP(`u2farr`) AS `u2farr`, UNIX_TIMESTAMP(`clear`) AS `clear`,  `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , `$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
				`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
				WHERE `$GLOBALS[mysql_prefix]assigns`.`id` = $frm_id LIMIT 1";
	
			$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result));
	
?>
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
			<TR CLASS="even"><TD colspan=2 ALIGN="center">Call Assignment (#<?php print $asgn_row['id']; ?>)</TD></TR>
			<TR CLASS="odd" VALIGN="baseline" onClick = "viewT('<?php print $asgn_row['ticket_id'];?>')">
				<TD CLASS="td_label" ALIGN="right">&raquo; <U>Incident</U>:</TD><TD>
<?php
			print $asgn_row['scope'] . "</TD></TR>\n";		
	
			if (!$asgn_row['responder_id']=="0"){
				$unit_name = $asgn_row['name'];
				$unit_link = " onClick = \"viewU('" . $asgn_row['responder_id'] . "')\";";
				$highlight = " &raquo;";
				}
			else {
				$highlight = "";
				$unit_name = "<FONT COLOR='red'><B>UNASSIGNED</B></FONT>";
				$unit_link = "";
				}
			print "<TR CLASS='even' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD><TD>" . format_date($asgn_row['as_of']) .
				"&nbsp;&nbsp;&nbsp;&nbsp;By " . $asgn_row['user'] . "</TD></TR>\n";		
			print "<TR CLASS='odd' VALIGN='baseline' " . $unit_link . ">";
			print "<TD CLASS='td_label' ALIGN='right'> " . $highlight . "<U>Unit</U>:</TD><TD>" . $unit_name ."</TD></TR>\n";
	
			print "<TR CLASS='even' VALIGN='baseline'>\n";
			print "<TD CLASS='td_label' ALIGN='right'>&nbsp;&nbsp;Unit Status:</TD><TD>";
			if ($asgn_row['responder_id']!="0"){
				print $asgn_row['status_val'];
				}		// end if (!$asgn_row['responder_id']=="0")
			else {
				print "NA";
				}
?>
			</TD></TR>
			<!-- 1441 -->
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">Dispatched:</TD>	<TD><?php print (format_date($asgn_row['dispatched'])) ;?></TD></TR>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Responding:</TD>	<TD><?php print (format_date($asgn_row['responding'])) ;?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">On scene:</TD>		<TD><?php print (format_date($asgn_row['on_scene'])) ;?></TD></TR>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Fac en-route:</TD>	<TD><?php print (format_date($asgn_row['u2fenr'])) ;?></TD></TR> <!--10/6/09-->
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">Fac arr:</TD>		<TD><?php print (format_date($asgn_row['u2farr'])) ;?></TD></TR> <!--10/6/09-->
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Clear:</TD>		<TD><?php print (format_date($asgn_row['clear'])) ;?></TD></TR>
			
			<TR CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Comments:</TD>
				<TD ALIGN='left'><?php print $asgn_row['assign_comments']; ?></TD></TR> <!-- 10/06/09 -->
			
			<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<br>
				<INPUT TYPE="BUTTON" VALUE="Cancel"  onClick="history.back();"  CLASS = 'btn' />&nbsp;&nbsp;&nbsp;&nbsp;	
<?php
			if(!($guest)){
				print "<INPUT TYPE='BUTTON' VALUE='Edit' onClick='document.nav_form.func.value=\"edit\";document.nav_form.submit();'  CLASS = 'btn'>\n";
				}
?>			
				</TD></TR>
			 </tbody></table>
			<INPUT TYPE='hidden' NAME='func' value= ''>
			<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
			</FORM>
<?php	
			break;			// end case 'view' == } ==
		
		case 'edit':		// ====  {  ==================================================================================
?>
	<SCRIPT>
		var incident_st = unit_st = assign_st = true;		// changes to false on activation

		function do_del(the_Form) {
			if (confirm("Delete this dispatch record?")) {the_Form.submit();}
			}
			
		function do_reset(the_Form) {
	//		incident_st = unit_st = assign_st = true;
			the_Form.func.value='edit';
			the_Form.frm_id.value='<?php print $frm_id;?>';		
			the_Form.submit();
			}		// end function do_reset()
	
		function validate_ed(theForm) {
			var errmsg="";
			if (theForm.frm_unit_id) {						// defined?
				if (theForm.frm_unit_id.value == 0)			{errmsg+= "\tSelect Unit\n";}
				}
			if (theForm.frm_unit_status_id) {
				if (theForm.frm_unit_status_id.value == 0)	{errmsg+= "\tSelect Unit Status\n";}
				}
			if (theForm.frm_comments.value == "")			{errmsg+= "\tComments required\n";}
	
			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
				theForm.frm_inc_status_id.disabled = incident_st;
				theForm.frm_unit_status_id.disabled = unit_st;
	//			theForm..disabled = assign_st_id.disabled = assign_st;
			
				theForm.submit();
				}
			}				// end function validate_ed(theForm)
	
		function confirmation() {
			var answer = confirm("This dispatch run completed?")
			if (answer){
				document.edit_Form.frm_complete.value=1; 
				document.edit_Form.submit();
				}
			}		// end function confirmation()
<?php
		if (get_variable('call_board')==1) {print "reSizeScr(18);\n";}
?>				
		function enable(instr) {
			var element= instr
			$(element).style.visibility = "visible";
	//		var i = document.forms[0].length;
			for (i=0; i<document.forms[0].length;i++){
					var start = document.forms[0].elements[i].name.length - instr.length
					if (instr == document.forms[0].elements[i].name.substring(start,99)) {
	//					alert (document.forms[0].elements[i].name.substring(start,99));
						document.forms[0].elements[i].disabled = false;
						}
				}
			}
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "reSizeScr(16)"><CENTER>		<!-- 1654 - 1/12/09 -->
			<DIV ID = 'edit_btns' STYLE="display:block; position:fixed; width:120px; height:auto; top:<?php print $from_top + 20;?>px; right: 150px; background-color:transparent; text-align:left;">	<!-- 5/17/09 -->				
				<INPUT TYPE="button" VALUE="Cancel" onClick="history.back();" CLASS = 'btn' />	
				<INPUT TYPE="button" VALUE="Reset" onclick="document.edit_Form.reset();"  CLASS = 'btn' />	
				<INPUT TYPE="button" VALUE="           Submit           " name="sub_but" onClick="document.edit_Form.submit();"  CLASS = 'btn' >  
				</LEFT>
			</DIV>
		
<?php	
			$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , `$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
				`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
				WHERE `$GLOBALS[mysql_prefix]assigns`.`id` = $frm_id LIMIT 1";
	
			$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			$asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result));
			$clear = (is_date($asgn_row['clear']))? "<FONT COLOR='red'><B>Cleared</B></FONT>": "";
			$disabled = "";
	
?>
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
			<TR CLASS="odd"><TD CLASS="td_label" colspan=99 ALIGN="center">Edit this Call Assignment (#<?php print $asgn_row['assign_id'] ?>) 
		<?php print $clear; ?></TD></TR>
			<TR><TD>&nbsp;</TD></TR>
	
			<TR CLASS="even" VALIGN="bottom">
				<TD CLASS="td_label" ALIGN="right">Incident:</TD>
				<TD TITLE = "<?php print $asgn_row['scope']; ?>"><?php print shorten($asgn_row['scope'], 32); ?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php	
					$selO = ($asgn_row['status']==$GLOBALS['STATUS_OPEN'])?   " SELECTED" :"";
					$selC = ($asgn_row['status']==$GLOBALS['STATUS_CLOSED'])? " SELECTED" :"" ;
?>			
					</TD><TD CLASS="td_label"> Status:&nbsp;</TD><TD><SELECT NAME='frm_inc_status_id' onChange="Javascript: incident_st = false;">
					<OPTION VALUE= <?php print $GLOBALS['STATUS_OPEN'] .  $selO; ?> >Open</OPTION>
					<OPTION VALUE= <?php print $GLOBALS['STATUS_CLOSED'] .  $selC; ?> >Closed</OPTION>
					</SELECT>
	
				</TD></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Unit:</TD>
<?php
				if ($asgn_row['responder_id']==0) {
?>			
					<TD><SELECT name="frm_unit_id" onChange = "document.edit_Form.frm_log_it.value='1'" >
						<OPTION value= '0' selected>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ";	//  
					$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
					while ($row = mysql_fetch_array($result))  {
						print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";		
						}
					print "</SELECT>\n";
					$do_unit = FALSE;
					}
				else {
?>
					<TD TITLE = "<?php print $asgn_row['name']; ?>"><?php print shorten($asgn_row['name'], 32);?>&nbsp;&nbsp;&nbsp;&nbsp;</TD>
<?php
					$do_unit = TRUE;
					}
?>			
				<TD CLASS="td_label">Unit Status:</TD><TD>
				<SELECT name="frm_unit_status_id"  onChange = "Javascript: unit_st=false; document.edit_Form.frm_log_it.value='1'" <?php print $disabled;?> > 
<?php																// UNIT STATUS
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$the_grp = strval(rand());			//  force initial optgroup value
				$i = 0; 
				$got_one = FALSE;
				while ($row2 = stripslashes_deep(mysql_fetch_array($result))) {
					if ($the_grp != $row2['group']) {
						print ($i == 0)? "": "\t\t\t</OPTGROUP>\n";
						$the_grp = $row2['group'];
						print "\t\t\t<OPTGROUP LABEL='$the_grp'>\n";
						}
					$sel = "";															// 5/11/09
					if (strtolower (substr($row2['status_val'], 0, 5)) == "avail"){
						$sel = " SELECTED ";
						$got_one = TRUE;
						}
						
					print "\t\t\t<OPTION VALUE=" . $row2['id'] . " $sel >" . $row2['status_val'] . "</OPTION>\n";
					$i++;
					}		// end while()
				print "\t\t\t</OPTGROUP>\n";
				
				$the_option = (!($got_one))? "\t<OPTION VALUE=0 SELECTED>Select</OPTION>\n": "\n";				// 5/23/09
				print $the_option;

				print "</SELECT>\n";
				unset($result);
?>
				</TD></TR>
			<TR CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Comments:</TD>
				<TD colspan=3><TEXTAREA NAME="frm_comments" COLS="45" ROWS="5" $disabled><?php print $asgn_row['assign_comments']; ?></TEXTAREA></TD></TR> <!-- 10/06/09 -->
				<!-- <TD colspan=3><INPUT MAXLENGTH="64" SIZE="64" NAME="frm_comments" VALUE="<?php print $asgn_row['assign_comments']; ?>" TYPE="text" <?php print $disabled;?>></TD></TR>-->

			<TR CLASS=''><TD CLASS="td_label" ALIGN="right">Mileage:</TD> <!--10/6/09-->
				<TD colspan=3 ALIGN='center'>
					<SPAN CLASS="td_label"> Start:</SPAN> <INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_strt" VALUE="<?php print $asgn_row['start_miles']; ?>" TYPE="text" <?php print $disabled;?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN CLASS="td_label">End:</SPAN>
				<INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_end" VALUE="<?php print $asgn_row['end_miles']; ?>" TYPE="text" <?php print $disabled;?>></TD></TR>
<?php
		 	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 		// mysql format
	
	//	 	dump($asgn_row['dispatched']);
			if (is_date($asgn_row['dispatched'])) {
				$the_date = $asgn_row['dispatched'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['dispatched']))? " CHECKED ": "";
			print "\n<TR CLASS='even'><TD CLASS='td_label' ALIGN='right'>Dispatched:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_db' TYPE='radio' onClick =  \"enable('dispatched')\" $chekd ><SPAN ID = 'dispatched' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("dispatched",totime($the_date), $the_dis);	// ($date_suffix,$default_date=0, $disabled=FALSE)
			print "</SPAN></TD></TR>\n";
	
	//	 	dump($asgn_row['responding']);
			if (is_date($asgn_row['responding'])) {
				$the_date = $asgn_row['responding'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['responding']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['responding']))? $asgn_row['responding']	: $now ;
			print "\n<TR CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Responding:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_rb' TYPE='radio' onClick =  \"enable('responding')\" $chekd><SPAN ID = 'responding' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("responding",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";
				
	//	 	dump($asgn_row['on_scene']);
			if (is_date($asgn_row['on_scene'])) {
				$the_date = $asgn_row['on_scene'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['on_scene']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['on_scene']))? $asgn_row['on_scene']	: $now ;
			print "\n<TR CLASS='even'><TD CLASS='td_label' ALIGN='right'>On scene:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_ob' TYPE='radio' onClick =  \"enable('on_scene')\" $chekd><SPAN ID = 'on_scene' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("on_scene",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";

	//	 	dump($asgn_row['u2fenr']);
			if (is_date($asgn_row['u2fenr'])) {
				$the_date = $asgn_row['u2fenr'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['u2fenr']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['u2fenr']))? $asgn_row['u2fenr']	: $now ;
			print "\n<TR CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Fac en-route:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_fe' TYPE='radio' onClick =  \"enable('u2fenr')\" $chekd><SPAN ID = 'u2fenr' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("u2fenr",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";

	//	 	dump($asgn_row['u2farr']);
			if (is_date($asgn_row['u2farr'])) {
				$the_date = $asgn_row['u2farr'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['u2farr']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['u2farr']))? $asgn_row['u2farr']	: $now ;
			print "\n<TR CLASS='even'><TD CLASS='td_label' ALIGN='right'>Fac arr:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_fa' TYPE='radio' onClick =  \"enable('u2farr')\" $chekd><SPAN ID = 'u2farr' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("u2farr",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";
	
	//	 	dump($asgn_row['clear']);
			if (is_date($asgn_row['clear'])) {
				$the_date = $asgn_row['clear'];
				$the_vis = "visible";
				$the_dis = FALSE;
				}
			else {
				$the_date = $now;
				$the_vis = "hidden";
				$the_dis = TRUE;
				}
			$chekd = (is_date($asgn_row['clear']))? " CHECKED ": "";
			$the_date = (is_date($asgn_row['clear']))? $asgn_row['clear']	: $now ;
			print "\n<TR CLASS='odd'><TD CLASS='td_label' ALIGN='right'>Clear:</TD>";
			print "<TD COLSPAN=3><INPUT NAME='frm_cb' TYPE='radio' onClick =  \"document.edit_Form.frm_complete.value=1; enable('clear')\" $chekd ><SPAN ID = 'clear' STYLE = 'visibility:" . $the_vis ."'>";
			generate_date_dropdown("clear",totime($the_date), $the_dis);
			print "</SPAN></TD></TR>\n";
				
?>
			<TR CLASS='even' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD>
				<TD colspan=2><?php print format_date($asgn_row['as_of']);?>&nbsp;&nbsp;&nbsp;&nbsp;By: <?php print $asgn_row['user'];?></TD>
				</TR>		
	
<!--		<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<br>
				<INPUT TYPE="BUTTON" VALUE="Cancel"  onClick="history.back();"  CLASS = 'btn'>
<?php
				if (!$disabled) {
?>			
				&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="BUTTON" VALUE="Reset"  onclick="Javascript: do_reset(document.edit_Form)"  CLASS = 'btn'/>&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="BUTTON" VALUE=" Submit " name="sub_but" onClick = "validate_ed(document.edit_Form)"  CLASS = 'btn' />
				</TD></TR>
-->				
				<TR CLASS='odd'><TD>&nbsp;</TD></TR>
				<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'>
<?php
				if(!(is_date($asgn_row['clear']))){				// 6/4/08	// 6/26/08
?>		
	<!--			<INPUT TYPE="BUTTON" VALUE="Run Complete" onClick="confirmation()"  CLASS = 'btn'/> -->
<?php
					}
				else {
?>		
					<INPUT TYPE="BUTTON" VALUE="Delete" onClick="do_del(document.del_Form);"  CLASS = 'btn'/>
<?php
					}
				}
?>			
				</TD></TR>
			 </tbody></table>
			<INPUT TYPE='hidden' NAME='frm_by_id' value= "<?php print $my_session['user_id'];?>"/>
			<INPUT TYPE='hidden' NAME='func' value= 'edit_db'/>
			<INPUT TYPE='hidden' NAME='frm_complete' value= ''/> 
			<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'/>
<?php
			if ($do_unit) {
				print "\t\t<INPUT TYPE='hidden' NAME='frm_unit_id' value= '" .  $asgn_row['responder_id'] . "'/>\n";
				}
?>		
			<INPUT TYPE='hidden' NAME='frm_ticket_id' value= '<?php print $asgn_row['ticket_id'];?>'/>
			<INPUT TYPE='hidden' NAME='frm_log_it' value=''/>
			<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
			</FORM>
			<FORM NAME="del_Form" ACTION = "<?php print basename(__FILE__); ?>" METHOD = "post">
			<INPUT TYPE='hidden' NAME='func' value= 'delete_db'/>
			<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'/>
			</FORM>
			
<?php
			break;			// end 	case 'edit': == } == 
			
		case 'edit_db':		// ==== {  ================================================ 
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			
			if (isset($frm_inc_status_id)) {
				$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `status`= " . quote_smart($frm_inc_status_id) . ", `updated` = " . quote_smart($now) . " WHERE `id` = " . quote_smart($frm_ticket_id) ." LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				do_log($GLOBALS['LOG_INCIDENT_CHANGE'], $frm_ticket_id);
				}
				
			if (isset($frm_unit_status_id)) {
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($frm_unit_status_id) . ", `updated` = " . quote_smart($now) . " WHERE `id` = " . quote_smart($frm_unit_id) ." LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				do_log($GLOBALS['LOG_UNIT_CHANGE'], $frm_unit_id);	
				}
	
			if (!(empty($frm_complete))) 	{			// is run completed?  6/4/08	// 6/26/08		
				do_log($GLOBALS['LOG_UNIT_COMPLETE'], $frm_ticket_id, $frm_unit_id);		// set clear times
				$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) . ", `clear`= " . quote_smart($now) . " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				}
			
			$frm_dispatched =	(array_key_exists('frm_db', $_POST))? 	quote_smart($_POST['frm_year_dispatched'] . "-" . $_POST['frm_month_dispatched'] . "-" . $_POST['frm_day_dispatched']." " . $_POST['frm_hour_dispatched'] . ":". $_POST['frm_minute_dispatched'] .":00") : "";
			$frm_responding = 	(array_key_exists('frm_rb', $_POST))? 	quote_smart($_POST['frm_year_responding'] . "-" . $_POST['frm_month_responding'] . "-" . $_POST['frm_day_responding']." " . $_POST['frm_hour_responding'] . ":". $_POST['frm_minute_responding'] .":00") : "";
			$frm_on_scene = 	(array_key_exists('frm_os', $_POST))?  	quote_smart($_POST['frm_year_on_scene'] . "-" .   $_POST['frm_month_on_scene'] . "-" .   $_POST['frm_day_on_scene']." " .   $_POST['frm_hour_on_scene'] . ":".   $_POST['frm_minute_on_scene'] .":00") : "";
			$frm_u2fenr = 	(array_key_exists('frm_fe', $_POST))?  	quote_smart($_POST['frm_year_u2fenr'] . "-" .   $_POST['frm_month_u2fenr'] . "-" .   $_POST['frm_day_u2fenr']." " .   $_POST['frm_hour_u2fenr'] . ":".   $_POST['frm_minute_u2fenr'] .":00") : "";	//10/6/09
			$frm_u2farr = 	(array_key_exists('frm_fa', $_POST))?  	quote_smart($_POST['frm_year_u2farr'] . "-" .   $_POST['frm_month_u2farr'] . "-" .   $_POST['frm_day_u2farr']." " .   $_POST['frm_hour_u2farr'] . ":".   $_POST['frm_minute_u2farr'] .":00") : "";	//10/6/09
			$frm_clear = 		(array_key_exists('frm_cb', $_POST))?  	quote_smart($_POST['frm_year_clear'] . "-" . 	  $_POST['frm_month_clear'] . "-" 	.    $_POST['frm_day_clear']." " .      $_POST['frm_hour_clear'] . ":".      $_POST['frm_minute_clear'] .":00") : "";
			
			$date_part = (empty($frm_dispatched))? 	"": ", `dispatched`= " . 	$frm_dispatched ;
			$date_part .= (empty($frm_responding))? "": ", `responding`= " . 	$frm_responding;
			$date_part .= (empty($frm_on_scene))? 	"": ", `on_scene`= " 	. 	$frm_on_scene;
			$date_part .= (empty($frm_u2fenr))? 	"": ", `u2fenr`= " 	. 	$frm_u2fenr;
			$date_part .= (empty($frm_u2farr))? 	"": ", `u2farr`= " 	. 	$frm_u2farr;
			$date_part .= (empty($frm_clear))? 		"": ", `clear`= " . 		$frm_clear;

			$unit_sql = (isset($frm_unit_id))?	" `responder_id`=" .quote_smart($frm_unit_id) . ", " :"";			// 1/15/09

			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET " .$unit_sql. " `as_of`= " . quote_smart($now) . ", `comments`= " . quote_smart($_POST['frm_comments']) . ", `start_miles`= " . quote_smart($_POST['frm_miles_strt']) . ", `end_miles`= " . quote_smart($_POST['frm_miles_end']) ;	//10/6/09
			$query .= $date_part;
			$query .=  " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
//dump($query);
			$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);
	
			$message = "Update Applied";
?>
			</HEAD>
	<BODY>
		<BR><CENTER><H3><?php print $message; ?></H3><BR>
		<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()"  CLASS = 'btn'/>
		<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
		<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
		</FORM></BODY></HTML> <!-- 1844 -->
<?php	
			break;				// end 	case 'edit_db' == } ==
			
	case 'delete_db':		// =====  {  =====================  6/4/08	
		
			$query  = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";	
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	
			$message = "Assign record deleted";
?>
			</HEAD>
	<BODY><CENTER>		<!-- 1751 - 1/12/09 -->
		<BR><BR><CENTER><H3><?php print $message; ?></H3><BR><BR><BR>
		<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()" CLASS = 'btn'/>
		<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
		</FORM></BODY></HTML> <!-- 1406 -->
<?php	
			break;			// end case 'delete_db': === } ===
	
	case 'list' :			// 1770 - 1990  { ========================================================
		$guest = is_guest();		// 10/31/09
		
		function get_deltas ($in_row) {					// returns array of strings or FALSE
			global $thresh_n, $thresh_m, $thresh_h;
			$deltas = array("","","","","","","" );		// length 7
//			$thresh = array(10, 20, 30, 40, 50, 60, 1);		// minutes
			switch ($in_row['severity']) {
				case $GLOBALS['SEVERITY_NORMAL']:
					$thresh = $thresh_n ;
				    break;
				case $GLOBALS['SEVERITY_MEDIUM']:
					$thresh = $thresh_m;
				    break;
				case $GLOBALS['SEVERITY_HIGH']:
					$thresh = $thresh_h;					
				    break;
				default:
					$thresh = $thresh_n;
				}
		
			if (!(is_date($in_row["problemstart"]))) return $deltas;
			
			else {
			
										// dispatched	
				if ((!(is_date($in_row["dispatched"]))) || ($in_row["dispatched"]< $in_row["problemstart"])) {$deltas[0] = "";}	
				else {
					$diff =  do_diff(0, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[0])? "ok": "over";
//					dump(($diff/60)); dump($thresh[0]);
					$deltas[0] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
										// responding
				if ((!(is_date($in_row["responding"]))) || ($in_row["responding"]< $in_row["problemstart"])) {$deltas[1] = "";}
				else {
					$diff =  do_diff(1, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[1])? "ok": "over";
					$deltas[1] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
									// on_scene
				if ((!(is_date($in_row["on_scene"]))) || ($in_row["on_scene"]< $in_row["problemstart"])) {$deltas[2] = "";}
				else {
					$diff =  do_diff(2, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[2])? "ok": "over";
					$deltas[2] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
									// u2fenr
				if ((!(is_date($in_row["u2fenr"]))) || ($in_row["u2fenr"]< $in_row["problemstart"])) {$deltas[3] = "";}
				else {
					$diff =  do_diff(3, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[3])? "ok": "over";
					$deltas[3] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
								// u2farr
				if ((!(is_date($in_row["u2farr"]))) || ($in_row["u2farr"]< $in_row["problemstart"])) {$deltas[4] = "";}	
				else {
					$diff =  do_diff(4, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[4])? "ok": "over";
					$deltas[4] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
								// clear	
				if ((!(is_date($in_row["clear"]))) || ($in_row["clear"]< $in_row["problemstart"])) {$deltas[5] = "";}
				else {
					$diff =  do_diff(5, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[5])? "ok": "over";
					$deltas[5] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
									// problemend	
				if ((!(is_date($in_row["problemend"]))) || ($in_row["problemend"]< $in_row["problemstart"])) {$deltas[6] = "";}	
				else {
					$diff =  do_diff(6, $in_row);									// returns seconds
					$class = (($diff/60)<=$thresh[6])? "ok": "over";
					$deltas[6] = "<SPAN CLASS='{$class}'>(" . show_diff($diff) . ")</SPAN>";
					}
				return $deltas;
				} 			// end if/else
			}		// end function
		
		/*
		$row["problemstart"])
		$row["dispatched"])
		$row["responding"])
		$row["on_scene"])
		$row["u2fenr"])
		$row["u2farr"])
		$row["clear"])
		$row["problemend"])
		*/
			
		function show_diff($secs) {				// seconds in, returns formatted conversion
			$days = floor ( $secs/86400 ); //calculate the days
			$diff = $secs - ($days*86400); // subtract the days
			
			$hours = floor ( $diff/3600 ); // calculate the hours
			$diff = $diff - ($hours*3600); // subtract the hours
			
			$mins = floor ( $diff/60 ); // calculate the minutes
			$mins_show = ($mins<10)? "0" . $mins : $mins ;

			$outstr ="";
			if ($days>0) {$outstr .= $days. ":";}
			if ($hours>0) {$outstr .= $hours;}			
			$outstr .=":" . $mins_show;
			return $outstr;
			}				// end function

		function do_diff($indx, $row){		// returns diff in seconds
			switch ($indx) {
				case 0:
					$temp = mktime(substr($row['dispatched'],11,2),substr($row['dispatched'],14,2),0,substr($row['dispatched'],5,2),substr($row['dispatched'],8,2),substr($row['dispatched'],0,4));
				    break;
				case 1:
					$temp = mktime(substr($row['responding'],11,2),substr($row['responding'],14,2),0,substr($row['responding'],5,2),substr($row['responding'],8,2),substr($row['responding'],0,4));
				    break;
				case 2:
					$temp = mktime(substr($row['on_scene'],11,2),substr($row['on_scene'],14,2),0,substr($row['on_scene'],5,2),substr($row['on_scene'],8,2),substr($row['on_scene'],0,4));		
				    break;
				case 3:
					$temp = mktime(substr($row['u2fenr'],11,2),substr($row['u2fenr'],14,2),0,substr($row['u2fenr'],5,2),substr($row['u2fenr'],8,2),substr($row['u2fenr'],0,4));		
				    break;
				case 4:
					$temp = mktime(substr($row['u2farr'],11,2),substr($row['u2farr'],14,2),0,substr($row['u2farr'],5,2),substr($row['u2farr'],8,2),substr($row['u2farr'],0,4));		
				    break;
				case 5:
					$temp = mktime(substr($row['clear'],11,2),substr($row['clear'],14,2),0,substr($row['clear'],5,2),substr($row['clear'],8,2),substr($row['clear'],0,4));		
				    break;
				case 6:
					$temp = mktime(substr($row['problemend'],11,2),substr($row['problemend'],14,2),0,substr($row['problemend'],5,2),substr($row['problemend'],8,2),substr($row['problemend'],0,4));		
				    break;
				default:
					dump($indx);				// error  error  error  error  error 
				}
			return $temp - mktime(substr($row['problemstart'],11,2),substr($row['problemstart'],14,2),0,substr($row['problemstart'],5,2),substr($row['problemstart'],8,2),substr($row['problemstart'],0,4));		
			}
																
	if ((array_key_exists("chg_hide", $_POST)) && ($_POST['chg_hide']==1)) {			// change persistence value - 2/18/09
		$temp = $_POST['hide_cl'];
		$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `f2` ='$temp' WHERE `sess_id`='$sess_key' LIMIT 1";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
		$my_session = get_mysession();			// refresh session array
		}
	$priorities = array("","severity_medium","severity_high" );

	$where = "WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";

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
		ORDER BY `severity` DESC, `theticket` ASC, `theunit` ASC ";																// 5/25/09, 1/16/08

//	dump($query);
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

	$lines = mysql_affected_rows();
?>
	  
	</HEAD>
	<BODY  onLoad = 'reSizeScr(<?php print $lines;?> )'><!-- 2162 -->
	<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
	<CENTER>
<?php
		if ($lines == 0) {												// empty?
			
			print "<BR /><BR /><EM>No Dispatches</EM><BR />";
			}
		else {															// not empty
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `facility_id` IS NOT NULL";
			$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$facilities = mysql_affected_rows()>0;		// set boolean in order to avoid waste space

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `start_miles` IS NOT NULL";
			$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$miles = mysql_affected_rows()>0;		// set boolean in order to avoid waste space
			
			unset($result_temp);

			$i = 1;	
	
			print "<TABLE BORDER=0 ALIGN='left'  cellspacing = 1 CELLPADDING = 1 ID='call_board' STYLE='display:block'>\n";	// 5/24/09
 			print "<TR CLASS='even'><TD COLSPAN=99 ALIGN = 'center'><B>Call Board</B> - {$lines } calls <FONT SIZE='-3'><I> &nbsp;&nbsp;&nbsp;&nbsp;(mouseover for details)</I></FONT></TD></TR>\n";	// 5/24/09
		
			$doUnit = ($guest)? "viewU" : "editU";
			$doTick = ($guest)? "viewT" : "editT";				// 06/26/08
			$now = time() - (get_variable('delta_mins')*60);
			$items = mysql_affected_rows();
			$header = "<TR CLASS='even'>";
			
			$header .= "<TD COLSPAN=3 ALIGN='center' CLASS='emph' >Incident</TD>";		// 9/27/08
			$header .= "<TD>&nbsp;</TD>";
			
			$header .= ($facilities)? "<TD COLSPAN=2 ALIGN = 'center' CLASS='emph'>Facility</TD><TD>&nbsp;</TD>" : "";
			$header .= "<TD COLSPAN=1 ALIGN='center' CLASS='emph'>Unit</TD>";			// 3/27/09
			$header .= "<TD>&nbsp;</TD>";

			$header .= "<TD COLSPAN=99 ALIGN='center' CLASS='emph'>Dispatch</TD>";
			$header .= "</TR>\n";

			$header .= "<TR CLASS='odd'>";												// 4/26/09, 10/6/09 (unit to facility status)
			$header .= "<TD ALIGN='center' CLASS='emph'>Name</TD>
						<TD ALIGN='center'>Synopsis</TD>
						<TD ALIGN='center'>Addr</TD>
						<TD ALIGN='center'>&nbsp;</TD>";

			$header .= ($facilities)? "<TD ALIGN = 'center'>From</TD><TD ALIGN = 'center'>To</TD><TD>&nbsp;</TD>" : "";						
						
			$header .= "<TD ALIGN='center' CLASS='emph'>Name</TD><TD>&nbsp;</TD>";
			
			$header .= "<TD ALIGN='center' TITLE= 'Opened'>Opened</TD>
						<TD ALIGN='center' TITLE= 'Dispatched'>D</TD>
						<TD ALIGN='center' TITLE= 'Responding'>R</TD>
						<TD ALIGN='center' TITLE= 'On scene'  >O</TD>";
						
			if ($facilities) {						
				$header .= "<TD ALIGN='center' TITLE= 'Fac en-route'>FE</TD>
							<TD ALIGN='center' TITLE= 'Fac arr'>FA</TD>";
							}
			$header .= "<TD ALIGN='center' TITLE= 'Dispatch cleared'>Clear</TD>						
						<TD ALIGN='center' TITLE= 'Incident closed'>End</TD>						
						<TD ALIGN='center'>By</TD>
						<TD ALIGN='center'>&nbsp;Comment </TD>";
			$header .=($miles)? "<TD ALIGN='center' COLSPAN=3>Mileage</TD>":"";
						
			$header .= "</TR>\n";

			$dis = $guest? " DISABLED ": "";				// 3/1/09

			$unit_ids = array();
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// 3/25/09
			
				if ($i == 1) {print $header;}
				$theClass = ($row['severity']=='')? "":$priorities[$row['severity']];
				print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>\n";
//				print "<FORM NAME='F$i' METHOD='get' ACTION='' $dis >\n";

// 	 INCIDENTS	3 cols + sep	- 9/12/09
					if (!(empty($row['scope']))) {
						$in_strike = 	($row['thestatus']== $GLOBALS['STATUS_CLOSED'])? "<STRIKE>": "";					// 11/7/08
						$in_strikend = 	($row['thestatus']== $GLOBALS['STATUS_CLOSED'])? "</STRIKE>": "";

						$the_name = addslashes (remove_nls($row['theticket']));															// 10/20/09
						$the_short_name = shorten ($the_name, 10);
						print "\t<TD ALIGN='left' onClick = \"ignore('{$row['ticket_id']}')\" CLASS='{$theClass}' 
							 onmouseover=\"Tip('{$row['ticket_id']}:{$the_name}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike} {$the_short_name}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
	
						$the_descr = addslashes (remove_nls($row['thetickdescr']));
						$the_short_one = shorten ($the_descr, 10);
						print "\t<TD ALIGN='left' onClick = \"ignore('{$row['ticket_id']}')\" CLASS='{$theClass}' ALIGN='left' 
							onmouseover=\"Tip('$the_descr')\" onmouseout=\"UnTip()\">{$in_strike} {$the_short_one}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
				
						$address = (empty($row['street']))? "" : $row['street'] . ", ";
						$address = addslashes($address . $row['city']. " ". $row['state']);
						$short_addr = shorten($address, 10);
						print "\t<TD ALIGN='left' onClick = \"ignore('{$row['ticket_id']}')\" CLASS='{$theClass}' 
							onmouseover=\"Tip('{$address}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike}{$short_addr}{$in_strikend}</TD>\n";		// address 8/24/08, 1/17/09

						}
					else {
						print "<TD ALIGN='left' COLSPAN=3>[#{$row['ticket_id']}]</TD>";				// id only if absent
						}
					print "\t<TD></TD>\n";				// 9/28/08, 4/26/09

//	Facilities - 2 cols + sep
						if ($facilities) {						
	
							$the_name = addslashes ($row['thefacility']);															// 9/12/09
							$the_short_name = shorten($row['thefacility'], 10);
	
							$the_rec_name = addslashes ($row['the_rec_facility']);															// 9/12/09
							$the_rec_short_name = shorten($row['the_rec_facility'], 10);
							
							print "\t<TD ALIGN='left' onClick = \"ignore('{$row['facility_id']}')\" CLASS='{$theClass}' 
								 onmouseover=\"Tip('{$row['facility_id']}:{$the_name}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike} {$the_short_name}{$in_strikend}&nbsp;</TD>\n";		// call 8/24/08, 4/26/09
							
							print "\t<TD ALIGN='left' onClick = \"ignore('{$row['facility_id']}')\" CLASS='{$theClass}' 
								 onmouseover=\"Tip('{$row['facility_id']}:{$the_rec_name}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike} {$the_rec_short_name}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
								 
							print "<TD>&nbsp;</TD>";
							}


//  UNITS			1 col + sep	- 9/12/09

					if (is_date($row['clear'])) {							// 6/26/08
						$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
						}
					else {
						$strike = $strikend = "";
						}			
 

					if (!($row['responder_id']==0)) {	
						$unit_name = empty($row['theunitid']) ? "[#{$row['responder_id']}]" : addslashes($row['theunit']) ;			// id only if absent
						$short_name = shorten($unit_name, 10);
						print "\t<TD ALIGN='left' CLASS='$theClass' onClick = \"ignore('{$row['responder_id']}')\" 
							 onmouseover=\"Tip('{$unit_name}')\" ALIGN='left' onmouseout=\"UnTip()\"><B>{$short_name}</B></TD>\n";							// unit 8/24/08, 1/17/09
						}			// end 'got a responder'
					else {	
						print "\t<TD ALIGN='left' CLASS='$theClass' onClick = editA(" . $row['assign_id'] . ") ID='myDate$i' ALIGN='left'><B>&nbsp;&nbsp;&nbsp;&nbsp;NA</b></TD>\n";	
						}		// end 'no responder'

					print "<TD></TD>";
// 	 DISPATCHES	13? cols 	- 9/12/09
					$deltas = get_deltas ($row);				// 11/1/09
//					dump ($deltas);

					print "<TD ALIGN='left' onmouseover=\"Tip('Opened: " . my_to_date($row['problemstart']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['problemstart']) . "</TD>\n";
					print "<TD ALIGN='left' onmouseover=\"Tip('Dispatched: " . my_to_date($row['dispatched']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['dispatched']) . "{$deltas[0]}</TD>\n";
					print "<TD ALIGN='left' onmouseover=\"Tip('Responding: " . my_to_date($row['responding']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['responding']) . "{$deltas[1]}</TD>\n";
					print "<TD ALIGN='left' onmouseover=\"Tip('On-scene: " . my_to_date($row['on_scene']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['on_scene']) . "{$deltas[2]}</TD>\n";
					if ($facilities) {
						print "<TD ALIGN='left' onmouseover=\"Tip('En-route to facility: " . my_to_date($row['u2fenr']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['u2fenr']) . "{$deltas[3]}</TD>\n";
						print "<TD ALIGN='left' onmouseover=\"Tip('At facility: " . my_to_date($row['u2farr']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['u2farr']) . "{$deltas[4]}</TD>\n";
						}
					print "<TD ALIGN='left' onmouseover=\"Tip('Cleared: " . my_to_date($row['clear']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['clear']) . "{$deltas[5]}</TD>\n";
					print "<TD ALIGN='left' onmouseover=\"Tip('Closed: " . my_to_date($row['problemend']) . "')\"  onmouseout=\"UnTip()\"  >" . my_to_date_sh($row['problemend']) . "{$deltas[6]} </TD>\n";

					$is_disabled = ($guest)? " DISABLED ": "";

					$temp = "???";
					
					print "\t<TD ALIGN='left' onmouseover=\"Tip('{$row['theuser']}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ");'>" .  $strike . shorten ($row['theuser'], 8) .  $strikend . "</TD>\n";															// user  

					$the_comment = addslashes (remove_nls($row['assign_comments']));		// 10/20/09
					$the_short_one = shorten ($the_comment, 10);
					
					print "\t<TD ALIGN='left' onmouseover=\"Tip('{$the_comment}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ");' >" . $strike .  $the_short_one . $strikend . "</TD>\n";	// comment
					
					if ($miles) {
						print "\t<TD ALIGN='left' CLASS='$theClass' >" . $row['start_miles'] . "&nbsp;</TD>\n";	
						print "\t<TD ALIGN='left' CLASS='$theClass' >" . $row['end_miles'] . "&nbsp;</TD>\n";
						$dist = ((my_is_int($row['start_miles'])) && (my_is_int($row['end_miles'])))? ($row['end_miles'] -  $row['start_miles']) : "";
						print "\t<TD ALIGN='left' CLASS='$theClass' >{$dist}</TD>\n";
						}

					print "\n</TR>\n";
					
					$i++;			 
				}		// end while($row ...)
				
				$lines = $i;
//				snap(basename(__FILE__), __LINE__);
				print "<TR><TD COLSPAN=99 ALIGN='center'><BR /><B>End</B><BR /></TD></TR>";
			}		// end if (mysql_affected_rows()>0) 
		print "</TABLE>";
		print "<DIV STYLE='position:fixed; width:120px; height:auto; top:0px; right: 0px; background-color:#EFEFEF;'>";

		print "<BR /><BR /><BR /><INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close()'  CLASS = 'btn'>";
		print "</DIV>";
?>
		<FORM NAME='finform' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE = 'hidden' NAME='func' VALUE='board'>
		</FORM>

<?php

		break;				// end case 'list' ==== } =======


		default:				// =======================================================================================
			print $func . "	 error: " . __LINE__;
		}				// end switch ($func)
	
	$temp = $_POST;

	$hide_cl = (array_key_exists ('hide_cl', $_POST))? $_POST['hide_cl'] :"0";
?>
	
	<FORM NAME='nav_form' METHOD='post' ACTION = "<?php print basename(__FILE__); ?>">
	<INPUT TYPE='hidden' NAME='frm_id' VALUE=''/>
	<INPUT TYPE='hidden' NAME='func' VALUE=''/>
	<INPUT TYPE='hidden' NAME='lines' value=''/>
	<INPUT TYPE='hidden' NAME='hide_cl' value='<?php print $hide_cl; ?>'/>		<!-- 1/20/09 -->
	<INPUT TYPE='hidden' NAME='chg_hide' value='0'/>							<!-- 2/18/09 -->
	</FORM>
	
	<FORM NAME='T_nav_form' METHOD='get' TARGET = 'main' ACTION = "main.php">
	<INPUT TYPE='hidden' NAME='id' VALUE=''/>
	</FORM>
	
	<FORM NAME='U_nav_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
	<INPUT TYPE='hidden' 	NAME='id' VALUE=''/>
	<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'/>
	<INPUT TYPE='hidden' 	NAME='view' VALUE='true'/>
	</FORM>
	
	<FORM NAME='U_edit_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
	<INPUT TYPE='hidden' 	NAME='id' VALUE=''/>
	<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'/>
	<INPUT TYPE='hidden' 	NAME='edit' VALUE='true'/>
	</FORM>

<?php
	$where = (get_variable('call_board')==2)? "index.php" :  basename(__FILE__);
	$lines = isset($lines)? $lines: 1;
?>
	<FORM NAME='can_Form' METHOD="post" TARGET = '_top' ACTION = "<?php print $where; ?>"/>
	<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
	<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines;?>'/>
	</FORM>

	</BODY></HTML><!-- <?php print $lines;?> --><!-- <?php print __LINE__;?> -->
<?php
	}		// end else ...		1/13/09

?>	
