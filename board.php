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
$COLS_DESCR = 32;		// incident description -  32 characters as default
$COLS_ADDR = 32;		// address -  32 characters as default

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
12/13/09 applied filter to 'open assigns' list
3/18/10 added log 'dispatch' entry
4/4/10 status alignment fixed
4/27/10 unit_name correction
4/29/10 added 'Cancel' button 
5/7/10 query name changes
6/21/10 - user select board sort order added
Sequence numbering: SELECT a.id, @num := @num + 1 seqno from ticket a, (SELECT @num := 0) d;
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/10/10 address column names disambiguated, path correction
8/29/10 use dispatch status tags per 'disp_stat' setting
9/29/10 use revised mysql2timestamp($m), per JB, do_diff moved to FIP
11/16/10 assign 'as of' correction
12/9/10 in_strike corrections, table width corrections for missing unit data
2/8/11 Revised query line 712 to correct multiple showings of same responder in add assignmenet from callboard .
2/8/11 added multi to line 712 sql
3/15/11 Revisions to support user editable color schemes and day/night mode
4/28/11 handle replaces unit name
5/9/11 add test for existence of dform element
6/10/11 changes for regional capability
4/24/12 Revised SQL station to correct incorrect GROUP BY clause.
6/20/12 applied get_text() to "Units", don't reset 'dispatch' time on reset
10/19/12 mysql setting set, form end moved outside table row
10/20/12 button label correction, generate log entry for each changed assigns event
1/8/2013 function get_disp_cell() added, reload() set true
1/10/2013 function my_gregoriantojd added, gregoriantojd being absent from some config's
*/

@session_start();
require_once('./incs/functions.inc.php');		//7/28/10
$query = "SET @@global.sql_mode= '';";			//10/19/12
$result = mysql_query($query) ;

//dump($_SESSION);
//dump($_REQUEST);

$from_top = 0;		// position of floating div, pixels from  top of frame

if($istest) {
	dump($_POST);
	}

function show_top() {				// generates the document introduction
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - Call Board Module</TITLE>
		<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8"/>
		<META HTTP-EQUIV="Expires" 				CONTENT="0"/>
		<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE"/>
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
		<META HTTP-EQUIV="Script-date" 			CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"> <!-- 3/15/11 -->
	<STYLE>
		span.even 	{ background-color: #DEE3E7;}
		.odd 	{ background-color: #EFEFEF;}
		.plain 	{ background-color: #FFFFFF;}
		input.btn {  color:#050;  font: bold 84% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border:1px solid;  border-color: #696 #363 #363 #696;  } 

		#BGCOLOR {BACKGROUND-COLOR: #EFEFEF;}
		.emph { background-color: #99b2cc;FONT-SIZE: 12px; COLOR: #ffffff; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		.emphb { background-color: #99b2cc;FONT-SIZE: 12px; COLOR: #ffffff; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
		tr.bigeven 	{ background-color: #DEE3E7;line-height: 200% }
		td {cursor: pointer; cursor: hand;} 
		/* Apply mousedown effect only to NON IE browsers */
		html>body .hovermenu ul li a:active{ border-style: inset;}
		
		checkbox {border-width: 0px;}
		span.ok{font-weight: light; color: gray;}

		span.over {font-weight: light; color: red;}

		#bar 		{ width: auto; height: auto; background:transparent; z-index: 100; } 
		* html #bar { /*\*/position: absolute; top: expression((4 + (ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop)) + 'px'); right: expression((30 + (ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft)) + 'px');/**/ }
		#foo > #bar { position: fixed; top: 4px; right: 30px; }

		td.my_plain	{background-color: white; white-space:nowrap;}
		tr td		{white-space:nowrap;}
		</STYLE>
<SCRIPT>
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
				alert ("926: failed");
				return false;
				}																						 
			}		// end function sync Ajax(strURL)


		function do_refresh() {						// 7/10/10
<?php
	if (get_variable('call_board')==2) 	{			// window vs. frame behavior
		print "\t\t do_frm_refresh();\n";			// re-size cb frame 
		}
?>	
		document.can_Form.submit();					// reload frame or window
		}		// end function do_refresh()

function do_frm_refresh() {				// frame refresh - call board option 2
	var temp = parent.document.getElementById('the_frames').getAttribute('rows');		// e.g., '63, 126,*'
	var rows = temp.split(",", 4)
	var height_in_pix = get_lines().trim();
	rows[1] = height_in_pix;
	temp = rows.join(",");
	parent.document.getElementById('the_frames').setAttribute('rows', temp);		// set revised cb frame height
	document.nav_form.func.value='board';
	document.nav_form.submit();														// refresh it
	}
	
function get_lines(){							// returns pixel count
	lines = syncAjax("lines_a.php");			// note synch call - 8/10/10
	return lines;	
	}				// end function get_lines()
	
	
	function tween(in_val, min_val, max_val) {							// min and max inclusive
		if ((in_val >= min_val) && (in_val<= max_val)) return in_val;
		else {
//			alert(190);
			if (in_val >= max_val) return max_val;
			if (in_val <= min_val) return min_val;
			alert ("err 192");
			}
		}
	
	function reSizeScr_add(lines) {				// 196			-- 5/23/09
		var the_height = ((lines * 25)+280);
		window.resizeTo(800, tween(the_height, 200, (window.screen.height - 200)));		// 10/31/09 - derived via trial/error (more of the latter, mostly)
		}		// end function re SizeScr()


	function reSizeScr(lines) {				// 196			-- 5/23/09
//		var the_height = ((lines * 25)+80);				// 4/27/10
		var the_height = ((lines * 30)+120);				// 4/27/10
		window.resizeTo((0.98)* screen.width, tween(the_height, 200, (window.screen.height - 200)));		// 10/31/09 - derived via trial/error (more of the latter, mostly)
//		alert(tween(the_height, 200, (window.screen.height - 200)));
		}		// end function re SizeScr()

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
		newwindow_add = window.open("<?php print $url; ?>", "Email",  "titlebar, resizable=1, scrollbars=yes, height=480,width=800,status=no,toolbar=no,menubar=no,location=no, left=50,top=150,screenX=100,screenY=300");
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
		$temp = mysql2timestamp($in_date);		// 9/29/10		
		return (good_date_time($in_date)) ?  date(get_variable("date_format"), $temp): "";		// 
		}
	
	function my_to_date_sh($in_date) {			// short date_time string
		$temp = mysql2timestamp($in_date);		// 9/29/10
		return (good_date_time($in_date)) ?  date("H:i", $temp): "";		// 
		}

	function my_gregoriantojd ( $da, $mo, $yr) {		// 1/10/2013
		return strtotime ("{$da} {$mo} {$yr}");
		}
		
	$jd_today = my_gregoriantojd (date ("M"), date ("j"), date ("Y"));			// julian today - see get_disp_cell() - 1/7/2013

	function get_disp_cell($row_element, $form_element, $theClass ) {		// returns td cell with disp times or checkbox - 1/8/2013
		$can_update = (array_key_exists ('level', $_SESSION) )? ( is_administrator() || is_user()): FALSE;			// 1/8/2013
		global $jd_today; 
		if (is_date($row_element)) {
			$ttip_str = " onmouseover=\"Tip(' " . my_to_date($row_element) . "')\" onmouseout=\"UnTip()\" ";		
			$then = strtotime($row_element);
			$jd_then = my_gregoriantojd (date ("M", $then), date ("j", $then), date ("Y", $then));
			$this_class = ($jd_then == $jd_today )? $theClass: "my_plain";
			return "\n\t<TD CLASS='{$this_class}' {$ttip_str}>" . my_to_date_sh($row_element) . "</TD>\n";	// identify as not-today
			}
		else {
			$is_dis = ($can_update)? "" : "DISABLED";		// limit to admins, operators 
			return "\n\t<TD CLASS='{$theClass}'><INPUT TYPE='checkbox' NAME='{$form_element}' {$is_dis} onClick = 'checkbox_clicked()' ></TD>\n";			
			}
		}		// end function get_disp_cell()

	sleep(1);		// wait for possible logout to complete	
	@session_start();

	if(empty($_SESSION))    {		// expired?
	show_top() ;
?>

</HEAD>
<BODY> <!-- <?php print __LINE__; ?> -->
<?php
require_once('./incs/links.inc.php');
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors

?>
<CENTER><BR><SPAN ID='zzstart' onClick = "Javascript: self.location.href = '<?php print basename(__FILE__); ?>';"><H3>Call board loading ...</H3></span>
</BODY><!-- <?php echo __LINE__;?> -->
</HTML>

<?php
		}				// end if (!mysql_affected_rows())
	else {
	
		set_sess_exp();				// update session time		
		extract($_POST);
//		$func = (!(array_key_exists('func', $_POST)))? "board" : $_POST['func'];		// array_key_exists ( mixed key, array search )
		$func = (!(array_key_exists('func', $_REQUEST)))? "board" : $_REQUEST['func'];		// array_key_exists ( mixed key, array search )

		show_top();
		$guest = is_guest();		// 10/31/09
		$user = is_user();			// 5/11/10

?>	
	<SCRIPT>
	var myuser = "<?php print $_SESSION['user']?$_SESSION['user']: "not";?>";
	var mylevel = "<?php print isset($_SESSION['level'])?get_level_text($_SESSION['level']): "na";?>";
	var myscript = "<?php print isset($_SESSION['user'])? LessExtension(basename( __FILE__)): "login";?>";
	
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
			print ($guest||($user))? "'view';" : "'edit';";		// 5/11/10
?>	
			document.nav_form.submit();
			}

		function do_mail_all_win() {			// 6/16/09
			if(starting) {return;}					
			starting=true;	
		
			newwindow_um=window.open("do_unit_mail.php?name=0", "Email",  "titlebar, resizable=1, scrollbars, height=640,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
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
	
				the_form.res_times.checked = false;				// 6/20/12
//				the_form.frm_dispatched.disabled = false;
//				the_form.frm_dispatched.checked = false;
//				the_form.frm_dispatched.disabled = dis;
				
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
				}		// end function our delete()

			function our_wrapup() {
				setTimeout('$(\'del_id\').style.display=\'none\';', 2000);			// show for 2 seconds

//				window.location.reload();
				document.can_Form.submit();			//  screen refresh/re-size 
				}

			var resp = "";														// 5/28/09
			while ((resp.toLowerCase() !="r") && (resp !="d")) {				// 6/19/09
				resp = prompt("Enter 'r' to Reset dispatch times\nEnter 'd' to Delete this dispatch, or press Cancel.\n", "");
				if (isNull(resp)) {the_form.res_times.checked = false; return;}		// 5/6/10
				else {
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
				}	
			}  	// end function do_assgn_reset()

	</SCRIPT>
	
<?php
	$guest = is_guest();			// 10/31/09
	$user = is_user();			// 10/31/09

	switch ($func) {
	
		case 'add': 					//  ==== { ==== first build JS array of existing assigns for dupe prevention

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 6/10/11
			$result = mysql_query($query);	// 4/18/11
			$al_groups = array();
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/18/11
				$al_groups[] = $row['group'];
				}	
			if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
				$where2 = "";
				} else {			
				if(isset($_SESSION['viewed_groups'])) {		//	6/10/11
					$curr_viewed= explode(",",$_SESSION['viewed_groups']);
					}

				if(!isset($curr_viewed)) {			//	6/10/11
					$x=0;	
					$where2 = "AND (";
					foreach($al_groups as $grp) {
						$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
						$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
						$where2 .= $where3;
						$x++;
						}
					} else {
					$x=0;	
					$where2 = "AND (";	
					foreach($curr_viewed as $grp) {
						$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
						$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
						$where2 .= $where3;
						$x++;
						}
					}
				}

			$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
					FROM `$GLOBALS[mysql_prefix]ticket` 
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`				
					WHERE `status` = {$GLOBALS['STATUS_OPEN']} {$where2} 
					GROUP BY `tick_id` ORDER BY `severity` DESC, `problemstart` ASC "; // highest severity, oldest open
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			if (mysql_affected_rows()==1) {			// if a single, do it
				$row = mysql_fetch_assoc($result);
?>
<SCRIPT>
function do_post() {
	document.temp_Form.frm_ticket_id.value=<?php print $row['tick_id'];?>;
	document.temp_Form.submit();
	}
setTimeout('do_post()', 1000);	
</SCRIPT>
<?php
				}				// end if (mysql_affected_rows()==1)
			else {				// build <SELECT> list
				$lines = mysql_affected_rows();
?>			
 </HEAD>
<BODY onLoad = "reSizeScr_add(<?php print $lines;?>)"><!-- <?php echo __LINE__ ;?> --><BR /><BR />
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="add_nav_form" ACTION = "<?php print basename(__FILE__); ?>" METHOD = "post">
			<TR CLASS="even"><TH colspan=4 ALIGN="center">Select Incident for Dispatch</TH></TR>
			<TR CLASS="odd" VALIGN="baseline">
				<TD CLASS="td_label" ALIGN="right">Incident:&nbsp;</TD>
				<TD ALIGN='left' COLSPAN=3>
					<SELECT NAME="frm_ticket_sel" onChange = "if (!(this.value=='')) {this.form.frm_ticket_id.value=this.value;this.form.submit()}";>
					<OPTION VALUE= '' SELECTED>Select</OPTION>
<?php
					$inc_ctr = mysql_affected_rows();
					while ($row = mysql_fetch_array($result))  {
						$addr = substr($row['street'] . " " . $row['city'] . " " . $row['state'], 0, 24);
						$descr = substr($row['scope'] , 0, 24) . " - " . $addr ;
						print "\t\t\t<OPTION value='{$row['tick_id']}'> {$descr}</OPTION>\n";		
//						$the_ticket_id = $row['id'];
						}
?>
					</SELECT>	
				</TD></TR>
<?php									// 4/29/10
	$the_onclick = (get_variable('call_board')==1)? "history.back()": "window.close()";
?>	
			<TR><TD COLSPAN = 4 ALIGN = 'center'><BR /><BR /><INPUT TYPE = 'button' value = 'Cancel' onClick = '<?php print $the_onclick; ?>' /></TD></TR>
				</TABLE>
<?php			
				}				// end if/else (mysql_affected_rows()==1)
?>
	<INPUT TYPE='hidden' NAME='frm_ticket_id' VALUE='' /> 
	<INPUT TYPE='hidden' NAME='func' VALUE='add_b' />
	</FORM>

 
<?php	
			break;				// end case 'add' ==== } ===
			
		case 'add_b': 					//  ==== { ==== 
			extract ($_POST);
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 6/10/11
			$result = mysql_query($query);	// 4/18/11
			$al_groups = array();
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/18/11
				$al_groups[] = $row['group'];
				}	
			if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
				$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 2";
				} else {			
				if(isset($_SESSION['viewed_groups'])) {		//	6/10/11
					$curr_viewed= explode(",",$_SESSION['viewed_groups']);
					}

				if(!isset($curr_viewed)) {			//	6/10/11
					$x=0;	
					$where2 = "WHERE (";
					foreach($al_groups as $grp) {
						$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
						$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
						$where2 .= $where3;
						$x++;
						}
					} else {
					$x=0;	
					$where2 = "WHERE (";	
					foreach($curr_viewed as $grp) {
						$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
						$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
						$where2 .= $where3;
						$x++;
						}
					}
				$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 2";	//	6/10/11				
				}

			$assigns = array();				// map unit id to ticket id
			function get_cd_str ($unit_row, $ticket_id) {
				global $assigns;
//				dump($assigns);
				if ((array_key_exists($unit_row['id'], $assigns))
					&& ($assigns[$unit_row['id']] == $ticket_id )) 	{ return " CHECKED DISABLED ";}	// this unit, this ticket
				if ($unit_row['multi'] == 1) 						{ return "";}		// multiple assign allowed
				if (array_key_exists($unit_row['id'], $assigns)) 	{ return " DISABLED ";}
				else 												{ return "";}		
				}				// end function get_cd_str ()
					
			print "\n<SCRIPT>\n";
			print "\t\tassigns = new Array();\n";
			
			$query = "SELECT *,`as_of` AS `as_of` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ORDER BY `as_of` DESC";	// 12/13/09
	
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row = stripslashes_deep(mysql_fetch_array($result))) {
				print "\t\tassigns['" .$row['ticket_id'] .":" . $row['responder_id'] . "']=true;\n";	// build assoc array of ticket:unit pairs
				}
?>
		<SCRIPT>	
		function validate_ad(theForm) {
			var errmsg="";
			if (theForm.frm_unit_id_str.value == "")	{errmsg+= "\tSelect one or more units\n";}
			if (theForm.frm_comments.value == "")		{errmsg+= "\tComments required\n";}
			if (!(theForm.frm_miles_strt.value.trim()) =="") {							// 11/4/09
				if (!(parseInt(theForm.frm_miles_strt.value.trim()) == theForm.frm_miles_strt.value.trim())) 
														{errmsg+= "\tStart mileage error\n";}
				}
			if (!(theForm.frm_miles_onsc.value.trim()) =="") {							// 11/4/09
				if (!(parseInt(theForm.frm_miles_onsc.value.trim()) == theForm.frm_miles_onsc.value.trim())) 
														{errmsg+= "\tOn scene mileage error\n";}
				}
			if (!(theForm.frm_miles_end.value.trim()) =="") {
				if (!(parseInt(theForm.frm_miles_end.value.trim()) == theForm.frm_miles_end.value.trim())) 
														{errmsg+= "\tEnd mileage error\n";}
				}
			if (!(theForm.frm_miles_tot.value.trim()) =="") {	//	10/23/12
				if (!(parseInt(theForm.frm_miles_tot.value.trim()) == theForm.frm_miles_tot.value.trim())) 
														{errmsg+= "\tTotal mileage error\n";}
				}					
			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
				theForm.submit();
				}
			}				// end function vali date(theForm)
	
		</SCRIPT>
		<SCRIPT SRC='./js/misc_function.js' type='text/javascript'></SCRIPT> 		
		</HEAD>
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]responder`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`			
					{$where2} GROUP BY `$GLOBALS[mysql_prefix]responder`.`id`";		// 2/12/09   
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$lines = mysql_affected_rows();

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `$GLOBALS[mysql_prefix]ticket`.`id` = {$frm_ticket_id}
					LIMIT 1"; 		// see case $func = 'add_b'
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$row = mysql_fetch_array($result);
			$latitude = $row['lat'];
			$longitude = $row['lng'];
?>
		<BODY onLoad = "reSizeScr_add(<?php print $lines;?>)"><!-- <?php echo __LINE__; ?> --><LEFT> 
				<A NAME='page_top'>

			<DIV ID='buttons' style="position:fixed; top:10px; right:2px; height: 150px; width: 150px; overflow-y: auto; overflow-x: auto;">
			<TABLE>
			<TR><TD ALIGN='left'><A HREF="#page_top">to top</A></TD></TR>
			<TR><TD ALIGN='left'><A HREF="#page_bottom">to bottom</A></TD></TR>
			<TR><TD>&nbsp;</TD></TR>
<?php
			if (get_variable('call_board')==1) {
				print "\t<TR><TD ALIGN='left'><INPUT TYPE='button' VALUE='Cancel'  CLASS = 'btn' onClick='do_refresh();' STYLE = 'left: 0;' />\n";
				}
			else {
				print "\t<TR><TD ALIGN='left'><INPUT TYPE='button' VALUE='Cancel'  CLASS = 'btn' onClick='window.close();'  STYLE = 'left: 0;' />\n";
				}
?>
				&nbsp;<INPUT TYPE="button" VALUE="Reset" onclick="Javascript: document.add_Form.frm_unit_id_str.value = ''; document.add_Form.reset();"  CLASS = 'btn'  STYLE = 'left: 0;' /></TD></TR>
				<TR><TD ALIGN='left'><INPUT TYPE="button" VALUE="           Next           " name="sub_but" onClick="validate_ad(document.add_Form)" CLASS = 'btn'  STYLE = 'left: 0;' ></TD></TR>
				</TABLE>

			</DIV> <!-- 3/30/10 -->

			<TABLE BORDER=0 STYLE = "border-collapse:collapse; margin-left:32px" CELLSPACING=0  CELLPADDING=0 >
			<FORM NAME="add_Form"  ACTION = "<?php print basename(__FILE__); ?>" METHOD = "post">
			<TR CLASS="even"><TH colspan=4 ALIGN="center">Assign <?php print get_text("Units");?> to Incident: <?php print $row['scope'];?></TH></TR>
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`
						WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";				
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = mysql_fetch_assoc($result))  {
				$assigns[$row['responder_id']] = $row['ticket_id'];
				}

			$capt = get_text("Units") . ":";
			$dist_capt = "&nbsp;mi SLD";				// 4/27/10
			$query = "SELECT *, `$GLOBALS[mysql_prefix]responder`.`id` AS `unit_id`,
				`$GLOBALS[mysql_prefix]responder`.`name` AS `unit_name`,
				(((acos(sin(({$latitude}*pi()/180)) * sin((`$GLOBALS[mysql_prefix]responder`.`lat`*pi()/180))+cos(({$latitude}*pi()/180)) * cos((`$GLOBALS[mysql_prefix]responder`.`lat`*pi()/180)) * cos((({$longitude} - `$GLOBALS[mysql_prefix]responder`.`lng`)*pi()/180))))*180/pi())*60*1.1515) AS `distance`
				FROM `$GLOBALS[mysql_prefix]responder` 
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `$GLOBALS[mysql_prefix]responder`.`type` = t.id )					
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]responder`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`					
				{$where2} 
				GROUP BY `$GLOBALS[mysql_prefix]responder`.`id` 
				ORDER BY `distance` ASC, `$GLOBALS[mysql_prefix]responder`.`name` ASC";		// 2/12/09 , 6/10/11  

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$i = 0;
			while ($row = mysql_fetch_assoc($result))  {
				$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 4/26/10
				$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];	
			
				$distance = ($row['distance']> 5000.0)? "?" : round($row['distance'],1);

				$cd_str = get_cd_str ($row, $_POST['frm_ticket_id']);

				$em_addr = is_email($row['contact_via'])? $row['contact_via']."," : "";				//  4/28/11
				print "<TR CLASS='{$evenodd[($i+1)%2]}' VALIGN='baseline'>\n\t\t<TD ALIGN='right' CLASS='td_label'>{$capt} </TD>";
				print "<TD ALIGN='left'><INPUT TYPE = 'checkbox' NAME = '_resp{$row['unit_name']}' VALUE= '{$row['unit_id']}' onClick = 'this.form.frm_unit_id_str.value += \"{$row['unit_id']}\"+ \",\"; this.form.frm_contact_str.value += \"{$em_addr}\"' {$cd_str} />";
				print "<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>{$row['handle']}</SPAN></TD>";
//				print "<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>{$row['unit_name']}</SPAN></TD>";
				print "<TD ALIGN='left'>{$distance} {$dist_capt}</TD>";
				print "<TD ALIGN='left'>" . get_status_sel($row['unit_id'], $row['un_status_id'], "u") . "</TD>";
				print "</TR>\n";
				$capt = "";
				$dist_capt = "";
				$i++;
				}
?>
				</TD></TR>

			<TR CLASS="<?php print $evenodd[($i+1)%2];?>">
				<TD CLASS="td_label" ALIGN="right">Comments:&nbsp;</TD>
				
				<TD ALIGN='left' COLSPAN=3><TEXTAREA NAME="frm_comments" COLS="60" ROWS="3" onFocus="Javascript:if (this.value=='TBD') {this.value='';}">TBD</TEXTAREA></TD></TR> <!-- 10/05/09 -->

			<TR CLASS='<?php print $evenodd[($i)%2];?>'><TD CLASS="td_label" ALIGN="right">Mileage:</TD> <!--11/4/09-->
				<TD colspan=4 ALIGN='center'>
					<SPAN CLASS="td_label"> Start:</SPAN> <INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_strt" VALUE="" TYPE="text" />
					<SPAN STYLE = "WIDTH: 60PX; DISPLAY: inline-block"></SPAN>
					<SPAN CLASS="td_label"> On scene:</SPAN> <INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_onsc" VALUE="" TYPE="text" />
					<SPAN STYLE = "WIDTH: 60PX; DISPLAY: inline-block"></SPAN>
					<SPAN CLASS="td_label">End:</SPAN> <INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_end" VALUE="" TYPE="text" />
					<SPAN STYLE = "WIDTH: 60PX; DISPLAY: inline-block"></SPAN>
					<SPAN CLASS="td_label">Total:</SPAN> <INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_tot" VALUE="" TYPE="text" />
				</TD>
			</TR>	<!-- 10/23/12 -->
			 </TABLE>
					 
			<INPUT TYPE='hidden' NAME='func' 			VALUE= 'add_db' />
			<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE="<?php print $frm_ticket_id;?>" />
			<INPUT TYPE='hidden' NAME='frm_unit_id_str'	VALUE= "" /> 	<!-- comma sep'd string of unit id's - 4/23/10 -->
			<INPUT TYPE='hidden' NAME='frm_contact_str'	VALUE= "" /> 	<!-- comma sep'd string of contact via's - 4/23/10 -->
			<INPUT TYPE='hidden' NAME='frm_by_id'		VALUE= "<?php print $_SESSION['user_id'];?>" />
			<INPUT TYPE='hidden' NAME='frm_log_it' 		VALUE='' />
			</FORM>
			<BR /><BR />
				
				
				<A NAME='page_bottom'>
<?php	
			break;				// end case 'add_b' ==== } ===
			
		case 'add_db' : 		// ==== { ====	id, as_of, status_id, ticket_id, frm_unit_id_str, comment, user_id

//			snap(basename(__FILE__), __LINE__);
		
			function handle_mail($to_str, $ticket_id) {				// 6/16/09 
				global $istest;
				
				$text = "";
				$the_msg = mail_it ($to_str, "", $text, $ticket_id, 3, TRUE);		// get default msg text
				$temp = (explode("\n", $text));
				$msg_lines = count($temp);
											
?>
<SCRIPT>
					function handleResult(req) {				// the called-back function
<?php
		if ($istest) {print "\n\t alert(648);\n";}
?>		
						}		// end function handle Result()	

					function send_it(addr, msg) {				// 12/13/09
					
						function isValidEmail(str) {
							return (str.lastIndexOf(".") > 2) && (str.indexOf("@") > 0) && (str.lastIndexOf(".") > (str.indexOf("@")+1)) && (str.indexOf("@") == str.lastIndexOf("@"));
							} 
						
						sep=outstr=errstr="";
						temp = addr.split(',');						// comma sep's
						for (i=0;i<temp.length;i++) {				// build string of valid addresses
							if ((temp[i].trim().length>0) && (!(isValidEmail(temp[i].trim()))))
								{errstr +="\t" + temp[i].trim()+"\n";} 
							else {
								if (temp[i].trim().length>0) {		// OK and not empty?
									outstr +=sep + temp[i].trim();
									sep = "|";						// note pipe separator
									}
								}
							}		// end for ()
						if (errstr.length>0)	{					// errors?
							alert("Invalid addresses:\n" +errstr );
							return false;
							}
						if (outstr.length==0)	{					// empty?
							alert("Valid addresses required\n");
							return false;
							}
						
						var url = "do_send.php";		// ($to_str, $subject_str, $text_str )
						
						var the_to = addr;
						var the_subj = escape("New Dispatch");
						var the_msg = escape(msg);		// the variables
						
						var postData = "to_str=" + the_to +"&subject_str=" + the_subj + "&text_str=" + the_msg; // the post string
						sendRequest(url,dummy,postData) ;
						return true;
						
						}		// end function send it()
					
					function dummy() {		
						window.close();
						}

					function ender() {
						$('sending').style.display = 'none';
<?php
			print "\n\t\t\t\t\t"; 
			print (get_variable('call_board')==1)? "document.add_cont_form.submit();\n" : "window.close();\n";
?>			
						}		// end function ender()
							
		
					function do_send_it () {
						if (send_it(document.add_mail_form.frm_to.value, document.add_mail_form.frm_text.value )) {
							$('sending').style.display = 'inline';
							setTimeout("ender();",3000); 
							}
						else {
							return false;		// error notice was alerted
							}
						} 				// end function do send_it ()
						
			
</SCRIPT>
</HEAD>
<BODY><!-- <?php echo __LINE__; ?> -->

			<TABLE ALIGN='center' BORDER=4><TR VALIGN='top'>
			<TR><TH COLSPAN=3 ALIGN='center'>Dispatch record(s) written<BR /><BR /></TH></TR>
			<TR><TD ALIGN='right'>
					<B>Notification:</B><BR/><BR/>
					<I>edit message to suit</I>&nbsp;&nbsp;
				</TD>
				<TD>&nbsp;</TD>
				<TD ALIGN='left'>
					<FORM NAME='add_mail_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">	<!-- 11/27/09 -->
<?php
			$msg_str = "Dispatching" . mail_it ($to_str, "", "New", $ticket_id, 3, TRUE); 
?>			
					<TEXTAREA NAME="frm_text" COLS=60 ROWS=<?php print $msg_lines+8; ?>><?php print $msg_str;?></TEXTAREA>
					
					</TD></TR>
			<TR VALIGN='top'><TD ALIGN='right'><B>Email to:</B><BR /><I> (use comma separator)</I></TD><TD></TD>
				<TD><INPUT TYPE='text' name='frm_to' SIZE=96 VALUE='<?php print $to_str ;?>'/> <BR /><BR />
					</TD>
					</TR>
				<TR VALIGN = 'bottom'>
				<TD COLSPAN=2></TD>
				<TD ALIGN='left' COLSPAN=2>
					<INPUT TYPE='button' VALUE='    Reset    ' onClick = "document.add_mail_form.reset();"  CLASS = 'btn'>&nbsp;&nbsp;&nbsp;
					<INPUT TYPE='button' VALUE='Send message' onClick = "do_send_it ();"  CLASS = 'btn'>&nbsp;&nbsp;&nbsp;
					<INPUT TYPE='button' VALUE='Do NOT send' onClick = "window.close();"  CLASS = 'btn'> 	<!-- 6/16/09 - force refresh -->
					<INPUT TYPE='hidden' NAME='func' VALUE='list'>&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN ID = 'sending' CLASS = 'header' STYLE = 'display: none'>Sending!<SPAN>
					</FORM>
				</TD>
				</TR></TABLE>

<?php  			
				}				// end function handle mail()
//			dump($_POST);
			$unit_ids = explode(",", $_POST['frm_unit_id_str']);		//  4/23/10
			for ($i=0; $i< count($unit_ids)-1; $i++) {
						
				$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 		// 11/2/09, 3/9/09, 10/6/09 added start end and total miles

				$temp = trim($frm_miles_strt);				// 11/4/09
				$start_mi = (empty($temp))? 0: $temp ;
				$temp = trim($frm_miles_onsc);				// 12/9/10
				$onsc_mi = (empty($temp))? 0: $temp ;
				$temp = trim($frm_miles_end);
				$end_mi = (empty($temp))? 0: $temp ;
				$temp = trim($frm_miles_tot);
				$tot_mi = (empty($temp))? 0: $temp ;		//	10/23/12			
																// 12/9/10
				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `dispatched`, `ticket_id`, `responder_id`, `comments`, `start_miles`, `on_scene_miles`, `end_miles`, `miles`, `user_id`)
								VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
									quote_smart($now),
									quote_smart($now),
									quote_smart($frm_ticket_id),
									quote_smart($unit_ids[$i]),
									quote_smart($frm_comments),
									quote_smart($start_mi),
									quote_smart($onsc_mi),
									quote_smart($end_mi),
									quote_smart($tot_mi),									
									quote_smart($frm_by_id));
		
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
									// apply status update to unit status
				do_log($GLOBALS['LOG_CALL_DISP'], $frm_ticket_id, $unit_ids[$i], 0);	// 3/18/10
				}					// end for ($i=0; $i< count($unit_ids)-1; $i++)
				
			$to_str = $_POST['frm_contact_str'];
			
			handle_mail($to_str, $frm_ticket_id);
//			snap(basename(__FILE__), __LINE__);

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";		// 2/12/09   
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$lines = mysql_affected_rows();
			unset($result);

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
//	alert(<?php echo __LINE__; ?>);
	window.opener.do_refresh();
	window.opener.reSizeScr($lines);
</SCRIPT>
</HEAD>
<BODY><!-- <?php echo __LINE__;?> -->
	<BR><BR><CENTER>

	<BR><BR>
		<FORM NAME='add_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
<?php
//	snap(basename(__FILE__), __LINE__);
	$on_click = (get_variable('call_board')==1)? "document.add_cont_form.submit();": "window.close();";
?>
		<INPUT TYPE='hidden' NAME='func' VALUE='board' />
		</FORM>
	</BODY></HTML><!-- <?php echo __LINE__;?> -->

<?php
//			snap(basename(__FILE__), __LINE__);
			break;				// end case 'add_db' ==== } =====
				
	case 'board' :			// ===== { =====
		function cb_shorten($instring, $limit) {
//			return (strlen($instring) > $limit)? substr($instring, 0, $limit-4) . "..." : $instring ;
			return (strlen($instring) > $limit)? substr($instring, 0, $limit): $instring;	// &#133
			}
																				
	if ((array_key_exists("chg_hide", $_POST)) && ($_POST['chg_hide']==1)) {			// change persistence value - 2/18/09

		$temp = $_POST['hide_cl'];
		@session_start();
		$_SESSION['show_hide_fac'] = 	$temp;		// show/hide closed assigns
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
				alert ("964: msg failed ");
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
//				$(elem).innerHTML = bull_str + payload;
				$(elem).innerHTML = payload;
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";		// 2/12/09   
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$lines = mysql_affected_rows();
	unset($result);

	$onload_str = (get_variable('call_board')==1)? "onLoad = '\t\treSizeScr({$lines})'": "";
//	$onload_str = "";
?>
	<BODY <?php print $onload_str;?> ><!-- <?php echo __LINE__; ?> -->
	<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
	
	<CENTER>
<?php
		function get_un_stat_sel($s_id, $b_id) {					// returns select list as string 
			global $guest;
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`, `$GLOBALS[mysql_prefix]un_status` 
				WHERE `$GLOBALS[mysql_prefix]un_status`.`id` = $s_id 
				AND `$GLOBALS[mysql_prefix]un_status`.`id` = `$GLOBALS[mysql_prefix]responder`.`un_status_id` LIMIT 1" ;	
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_st)) : FALSE;
			$init_bg_color = ($row)? $row['bg_color'] : "transparent";
			$init_txt_color = ($row)? $row['text_color']: "black";		

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
 			$dis = ($guest)? " DISABLED": "";								// 9/17/08
			$the_grp = strval(rand());			//  force initial OPTGROUP value
			$i = 0;
			$outstr = "\n\t\t<SELECT name='frm_status_id'  onFocus = 'show_but($b_id)' $dis STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;'>\n";
			while ($row = stripslashes_deep(mysql_fetch_array($result_st))) {
				if ($the_grp != $row['group']) {
					$outstr .= ($i == 0)? "": "\t</OPTGROUP>\n";
					$the_grp = $row['group'];
					$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>\n";
					}
				$sel = ($row['id']==$s_id)? " SELECTED": "";
				$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'>" . $row['status_val'] . "</OPTION>\n";
				$i++;
				}		// end while()
			$outstr .= "\t\t</OPTGROUP>\n\t\t</SELECT>\n";
//			dump ($outstr);

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

	<DIV STYLE="position:fixed; width:120px; height:auto; top:0px; right: 0px; background-color: inherit;">		<!-- 3/15/11 -->
	
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
		
		function apply_all_clicked (){
			do_all();
			$('apply_btn').style.display='none'; 					// hide 'Apply all'
			$('can_btn').style.display='none'; 						// hide 'Cancel'
			$('add_btn').style.display='inline'; 					// show 'Add'. 'All Units Mail'

			$('mail_btn').style.display='inline';
			$('list_btn').style.display='inline';
			$('close_btn').style.display='inline';
			$('refr_btn').style.display='inline';

			$('done_id').style.display='inline';  					// show 'Done!' for 2 seconds
			setTimeout('$(\'done_id\').style.display=\'none\';', 2000);
			window.location.reload(true);							// 1/8/2013
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
	<P ALIGN='LEFT'>
	<DIV ID="foo"'><DIV ID="bar">
		<TABLE BORDER=0 STYLE = "border-collapse:collapse;" CELLSPACING=1>
		<TR><TD COLSPAN=2><SPAN CLASS = "emph" ID = "done_id" STYLE="display:none"><B>&nbsp;Done!&nbsp;</B></SPAN></TD></TR>
		<TR><TD COLSPAN=2><SPAN CLASS = "emph" ID = "del_id"  STYLE="display:none"><B>&nbsp;Deleted!&nbsp;</B></SPAN></TD></TR>
		
<?php 
	if (!($guest)) 		{ 
		$disp = (is_unit())? "none": "inline";				// 7/27/10
?>			
		<TR><TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Apply all" ID = "apply_btn" onClick = "apply_all_clicked ();" STYLE="display:none" />
			</TD>
			<TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Cancel"    ID = "can_btn"   onClick = "cancel_clicked();"   STYLE="display:none" /> 
			</TD></TR>
		<TR><TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Add"       ID = "add_btn"   onClick = "do_add_btn()" STYLE="display: <?php print $disp;?>" />
			</TD>
			<TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Mail  "      ID = "mail_btn"  onClick = "do_mail_all_win();"  STYLE="display:inline" />
			</TD></TR>
		
<?php 
		}		// end  if (!($guest)
	$temp = get_variable('call_board');										// 7/10/10
	$refr_str = ($temp == 2 )? "do_frm_refresh()": "do_refresh()";		// window vs frame refresh

	$btn_text = ($_SESSION['show_hide_fac'] == "h")? "Show": "Hide";
	$frm_val =  ($_SESSION['show_hide_fac'] == "h")? "s": "h";
?>	
		
		<TR><TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "List  "      ID = "list_btn"  onClick = "open_list_win();"    STYLE="display:inline" />		
			</TD>
			<TD ALIGN='left'><INPUT TYPE="button" CLASS="btn" VALUE = "Close"     ID = "close_btn" onClick = "self.close()"        STYLE="display:inline" />
			</TD></TR>
		<TR><TD ALIGN='left' COLSPAN=2><INPUT TYPE="button" CLASS="btn" VALUE = "Refresh"   ID = "refr_btn"  onClick = "<?php print $refr_str; ?>" STYLE="display:inline" />
			</TD>
			</TR>
		<TR><TD ALIGN='left' COLSPAN=2>Cleared: <SPAN onClick = "do_hors('<?php print $frm_val ;?>')"><U><?php print $btn_text ;?></U></SPAN>

		</TD></TR>
		</TABLE>
	</DIV></DIV>
	
  
</DIV>

<?php	
		switch ($_SESSION['show_hide_fac']) {		// persistence flags 2/18/09
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
			}														// 5/7/10 name changes
																	// user-selected sort - 6/21/10, 8/9/10 ambiguous addr names resolved
//		$order_by = (array_key_exists('sort', $_POST))? "`unit_name` ASC, `handle` ASC " : "`severity` DESC, `tick_scope` ASC, `unit_name` ASC ";			
		$order_by = (array_key_exists('sort', $_POST))? "`handle` ASC " : "`severity` DESC, `tick_scope` ASC, `unit_name` ASC ";			
																	// 8/10/10
																	
// ============================= Regions Stuff	sets which tickets the user can see.						
							
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	//	6/10/11
			$result = mysql_query($query);	// 6/10/11
			$al_groups = array();
			$al_names = "";	
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 6/10/11
				$al_groups[] = $row['group'];
				$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 6/10/11
				$result2 = mysql_query($query2);	// 6/10/11
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 6/10/11		
						$al_names .= $row2['group_name'] . ", ";
					}
				}

			if(is_super()) {	//	6/10/11
				$al_names .= "Superadmin Level";
			}				

			if(isset($_SESSION['viewed_groups'])) {	//	5/4/11
				$curr_viewed= explode(",",$_SESSION['viewed_groups']);
				} else {
				$curr_viewed = $al_groups;
				}	
			
			if(!isset($_SESSION['viewed_groups'])) {	//	6/10/11
				if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
					$where = "WHERE `a`.`type` = 1";
					} else {				
					$x=0;	
					$where = "WHERE ((";
					foreach($al_groups as $grp) {
						$where2 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
						$where .= "`a`.`group` = {$grp}";
						$where .= $where2;
						$x++;
						}
					$where .= " AND `a`.`type` = 1) ";
					}	//	end if count($al_groups ==0)
				} else {
				if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
					$where = "WHERE `a`.`type` = 1";
					} else {	
					$x=0;	
					$where = "WHERE ((";		//	6/10/11
					foreach($curr_viewed as $grp) {
						$where2 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
						$where .= "`a`.`group` = {$grp}";
						$where .= $where2;
						$x++;
						}
					$where .= " AND `a`.`type` = 1) ";							
					}	//	end if count($curr_viewed ==0)
				}	//	End if !isset $_SESSION['viewed_groups']
			
// ================================ end of regions stuff																				
																	
		$query = "SELECT *, `as_of` AS `as_of`,
			`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
			`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
			`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
			`t`.`description` AS `tick_descr`,
			`t`.`status` AS `tick_status`,
			`t`.`street` AS `tick_street`,
			`t`.`city` AS `tick_city`,
			`t`.`state` AS `tick_state`,			
			`r`.`id` AS `unit_id`,
			`r`.`name` AS `unit_name` ,
			`r`.`type` AS `unit_type` ,
			`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
			FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `a`.`resource_id`)			
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
			{$where} AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') $hide_sql 
			GROUP BY `$GLOBALS[mysql_prefix]assigns`.`id`
 			ORDER BY {$order_by }";		//	4/24/12
// 			ORDER BY `severity` DESC, `tick_scope` ASC, `unit_name` ASC ";		// 5/25/09, 1/16/08, 4/24/12

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

		$lines = mysql_affected_rows();
		
		if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
			$curr_viewed= explode(",",$_SESSION['viewed_groups']);
			} else {
			$curr_viewed = $al_groups;
			}

		$curr_names="";	//	6/10/11
		$z=0;	//	6/10/11
		foreach($curr_viewed as $grp_id) {	//	6/10/11
			$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
			$curr_names .= get_groupname($grp_id);
			$curr_names .= $counter;
			$z++;
			}
			
		$regs_string = "<SPAN style='padding: 2px; color: #000000; background: #00FFFF; font-weight: bold; font-size: 12px;'>Showing " . get_text("Regions") . ":&nbsp;&nbsp;" . $curr_names . "</SPAN>";	//	6/10/11
		
		print "\n<SCRIPT>\n\tvar lines = {$lines};\n</SCRIPT>\n";		// hand to JS - 5/23/09
		if ($lines == 0) {												// empty?
			
			print "<TABLE BORDER=0 ALIGN='left' WIDTH = '90%' cellspacing = 1 CELLPADDING = 1  ID='call_board' STYLE='display:block'>";
			print "<TR CLASS='even'><TD ALIGN = 'center' WIDTH='80%'><B>Call Board</B>&nbsp;&nbsp;" . $regs_string . "<FONT SIZE='-3'><I> (mouseover/click for details)</I></FONT></TD><TD WIDTH=150px></TD></TR>\n";	//	6/10/11
			print "<TR><TH ><BR /><BR /><BR />No Current Dispatches<BR /></TH><TH></TH></TR>\n";
			print "</TABLE>";
			}
		else {															// not empty

			$i = 1;	
	
			print "<TABLE BORDER=0 ALIGN='left' WIDTH='88%'  cellspacing = 1 CELLPADDING = 1 ID='call_board' STYLE='display:block'>\n";	// 5/24/09
 			print "<TR CLASS='even'><TD COLSPAN=18 ALIGN = 'center'><B>Call Board</B><FONT SIZE='-3'>&nbsp;&nbsp;&nbsp;&nbsp;" . $regs_string . "&nbsp;&nbsp;&nbsp;&nbsp;<I>(mouseover/click for details)</I></FONT></TD><TD WIDTH=150px></TD></TR>\n";	// 5/24/09
			 
			$doUnit = (($guest)||($user))? "viewU" : "editU";		// 5/11/10
			$doTick = ($guest)? "viewT" : "editT";				// 06/26/08
			$now = time() - (get_variable('delta_mins')*60);
			$items = mysql_affected_rows();
			$tags_arr = explode("/", get_variable('disp_stat'));		// 8/29/10 

			$header = "<TR CLASS='even'>";
			
			$header .= "<TD COLSPAN=4 ALIGN='center' CLASS='emphb' WIDTH='{$TBL_INC_PERC}%' onClick = 'document.can_Form.submit();' TITLE = 'Click to sort by Incident'><U>Incident</U></TD>";		// 9/27/08
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD COLSPAN=9 ALIGN='center' CLASS='emphb 'WIDTH='{$TBL_UNIT_PERC}%' onClick = 'document.sort_Form.submit();'  TITLE = 'Click to sort by Unit'><U>" . get_text("Units") . "</U></TD>";			// 3/27/09
			$header .= "<TD>&nbsp;</TD>";
			$header .= "<TD COLSPAN=4 ALIGN='center' CLASS='emphb' WIDTH='{$TBL_CALL_PERC}%'>Dispatch</TD>";
			$header .= "</TR>\n";

			$header .= "<TR CLASS='odd'>";												// 4/26/09, 10/6/09 (unit to facility status)
			$header .= "<TD ALIGN='left' CLASS='emph'> " . cb_shorten("Name", $COLS_INCID) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Open", $COLS_OPENED) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Synopsis", $COLS_DESCR) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Addr", $COLS_ADDR) . "</TD>
						<TD ALIGN='center'>&nbsp;</TD>
						<TD ALIGN='left' CLASS='emph'> " . cb_shorten("Name", $COLS_UNIT) . "</TD>
						<TD ALIGN='center' TITLE='E-mail'><IMG SRC='mail.png'></TD>
						<TD ALIGN='center' TITLE= 'Dispatched'>{$tags_arr[0]}</TD>
						<TD ALIGN='center' TITLE= 'Responding'>{$tags_arr[1]}</TD>
						<TD ALIGN='center' TITLE= 'On scene'>{$tags_arr[2]}</TD>
						<TD ALIGN='center' TITLE= 'Fac en-route'>{$tags_arr[3]}</TD>
						<TD ALIGN='center' TITLE= 'Fac arr'>{$tags_arr[4]}</TD>
						<TD ALIGN='left'   TITLE= 'Clear'>{$tags_arr[5]}</TD>
						
						<TD COLSPAN=2  ALIGN='left' >&nbsp;&nbsp;&nbsp;Status</TD>
						<TD ALIGN='left' CLASS='emph'> " . cb_shorten("As of", $COLS_ASOF) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("By", $COLS_USER) . "</TD>
						<TD ALIGN='left'>" . cb_shorten("Comment", $COLS_COMMENTS) . " </TD>
						<TD ALIGN='center' TITLE='Reset unit dispatch times or Delete dispatch' width='5%'>&nbsp;R/D </TD>";		// 5/28/09, 1/12/09, 2/10/11
			$header .= "</TR>\n";

			$dis = $guest? " DISABLED ": "";				// 3/1/09

			$unit_ids = array();
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// major while () - 3/25/09
//	============================= Regions stuff
				$query_un = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `resource_id` = '$row[unit_id]' ORDER BY `id` ASC;";	// 6/10/11
				$result_un = mysql_query($query_un);	// 6/10/11
				$un_groups = array();
				while ($row_un = stripslashes_deep(mysql_fetch_assoc($result_un))) 	{	// 6/10/11
					$un_groups[] = $row_un['group'];
					}
	
//				dump($row);

				if(count($al_groups) == 0) {
					$inviewed = 1;
					} else {
					$inviewed = 0;	//	6/10/11
					foreach($un_groups as $un_val) {
						if(in_array($un_val, $al_groups)) {
							$inviewed++;
							}
						}
					}
					
//	============================= end of Regions stuff					
			
					if ($i == 1) {print $header;}
					$theClass = ($row['severity']=='')? "":$priorities[$row['severity']];
					print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>\n";
					print "<FORM NAME='F$i' METHOD='get' ACTION='' $dis >\n";

				if ($inviewed > 0) {	//	Tests to see whether assigned unit is in one of the users groups 6/10/11
// 	 INCIDENTS	4 cols + sep	- 9/12/09, 12/9/10
					$in_strike = 	((!(empty($row['scope']))) && ($row['tick_status']== $GLOBALS['STATUS_CLOSED']))? "<STRIKE>": "";					// 11/7/08
					$in_strikend = 	((!(empty($row['scope']))) && ($row['tick_status']== $GLOBALS['STATUS_CLOSED']))? "</STRIKE>": "";
					if (!(empty($row['scope']))) {

						$the_name = addslashes ($row['tick_scope']);															// 9/12/09
						$the_short_name = cb_shorten($row['tick_scope'], $COLS_INCID);
						print "\t<TD onClick = $doTick('{$row['ticket_id']}') CLASS='{$theClass}' 
							 onmouseover=\"Tip('[#{$row['ticket_id']}] {$the_name}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike} {$the_short_name}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
						print "<TD onmouseover=\"Tip('Opened: " . my_to_date($row['problemstart']) . "')\"  onmouseout=\"UnTip()\"  >" . cb_shorten(my_to_date_sh($row['problemstart']), $COLS_OPENED) . "</TD>\n";

	
						$the_descr = addslashes ($row['tick_descr']);
						$the_short_one = cb_shorten($row['tick_descr'], $COLS_DESCR);
						print "\t<TD onClick = $doTick('{$row['ticket_id']}') CLASS='{$theClass}' ALIGN='left' 
							onmouseover=\"Tip('$the_descr')\" onmouseout=\"UnTip()\">{$in_strike} {$the_short_one}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
				
						$address = (empty($row['tick_street']))? "" : $row['tick_street'] . ", ";		// 8/10/10
						$address = addslashes($address . $row['tick_city']. " ". $row['tick_state']);
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
					if (!($row['unit_id'] == 0)) {																	// 5/11/09
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`	WHERE `id`= '{$row['unit_type']}' LIMIT 1";
						$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
						$row_type = (mysql_affected_rows() > 0) ? stripslashes_deep(mysql_fetch_assoc($result_type)) : "";
						$the_bg_color = empty($row_type)?	"transparent" : $GLOBALS['UNIT_TYPES_BG'][$row_type['icon']];		// 3/15/10
						$the_text_color = empty($row_type)? "black" :		$GLOBALS['UNIT_TYPES_TEXT'][$row_type['icon']];		// 
						unset ($row_type);
						$unit_name = empty($row['unit_id']) ? "[#{$row['unit_id']}]" : ($row['unit_name']) ;			// id only if absent
						$short_name = cb_shorten($row['handle'], $COLS_UNIT);
						print "\t<TD CLASS='$theClass' onClick = {$doUnit}('{$row['unit_id']}') 
							 onmouseover=\"Tip('[#{$row['unit_id']}] {$unit_name}')\" ALIGN='left' onmouseout=\"UnTip()\">
							 <SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'><B>{$short_name}</B></SPAN></TD>\n";							// unit 8/24/08, 1/17/09
						
						print "\t<TD  CLASS='mylink' onmouseover =\"$('c{$i}').style.visibility='visible';\" onmouseout = \"$('c{$i}').style.visibility='hidden'; \" ALIGN='center'>
							\n\t<SPAN id=\"c{$i}\" style=\"visibility: hidden\">
							&nbsp;<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit {$unit_name}'. 
							 onclick = \"do_mail_win(F{$i}.frm_contact_via.value, {$row['ticket_id']}); \"> 
							</SPAN></TD>\n";		// 4/26/09
	
						echo get_disp_cell($row['dispatched'], 	"frm_dispatched", $theClass );		// 1/8/2013
						echo get_disp_cell($row['responding'], 	"frm_responding", $theClass );
						echo get_disp_cell($row['on_scene'], 	"frm_on_scene", $theClass );
						echo get_disp_cell($row['u2fenr'], 		"frm_u2fenr", $theClass );
						echo get_disp_cell($row['u2farr'], 		"frm_u2farr", $theClass );
						echo get_disp_cell($row['clear'], 		"frm_clear", $theClass );
						
						if (!in_array ($row['unit_id'], $unit_ids)) {				// status array not yet shown?
							$unit_st_val = (array_key_exists($row['un_status_id'], $status_vals_ar))? $status_vals_ar[$row["un_status_id"]]: "";
							if (empty($row['unit_id'])) {				// 3/15/10
								print "\t<TD ALIGN='left'><SPAN STYLE='margin-left: 10px'>na</SPAN></TD>\n";
								}
							else {	
								print "\t<TD ALIGN='left' onmouseover=\"Tip('{$unit_st_val}')\" TITLE= '$unit_st_val' onmouseout=\"UnTip()\">" .  get_un_stat_sel($row['un_status_id'], $i) . "</TD>\n";						// 4/4/10 status
								}
							
							print "\t<TD>\n\t<SPAN ID='TD{$i}' STYLE='display:none'><INPUT TYPE='button' VALUE='Go'  CLASS = 'btn' onClick=\"to_server(F$i); do_refresh();\">\n"; 		// 9/28/08
							print "\t<INPUT TYPE='button' VALUE='Cancel'   CLASS = 'btn' onClick=\"document.F$i.reset();hide_but($i)\"></SPAN></TD>\n";
							array_push($unit_ids, $row['unit_id']);
							}
						else {
							print "<TD COLSPAN=2></TD>";
							}
						}			// end 'got a responder'
					else {	
						print "\t<TD COLSPAN=10 CLASS='$theClass' onClick = editA(" . $row['assign_id'] . ") ID='myDate$i' ALIGN='left'><B>&nbsp;&nbsp;&nbsp;&nbsp;NA</b></TD>\n";	
						}		// end 'no responder'


					$d1 = $row['assign_as_of'];
					$d2 = mysql2timestamp($d1);		// 9/29/10

					$temp = "[#{$row['assign_id']}] " . date(get_variable("date_format"), $d2);
					
					print  "\t<TD onmouseover=\"Tip('{$temp} ')\" onmouseout=\"UnTip()\" CLASS='$theClass' 
						onClick = editA(" . $row['assign_id'] . "); ID='myDate$i' ALIGN='left' TITLE='" . 
						format_date_2($row['as_of']) ." '>" .  $strike . 
						format_sb_date_2($row['assign_as_of']) .  $strikend . "</TD>\n";			// as of - 11/16/10

					print "\t<TD onmouseover=\"Tip('{$row['theuser']}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ");'>" .  $strike . cb_shorten ($row['theuser'], $COLS_USER) .  $strikend . "</TD>\n";															// user  

					$comment = addslashes (remove_nls($row['assign_comments']));

					print "\t<TD onmouseover=\"Tip('{$comment}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ")'; >" . $strike .  cb_shorten ($comment, $COLS_COMMENTS) . $strikend . "</TD>\n";	// comment

					
					print "\t<TD TITLE = 'Click to RESET R O FE FA C times' CLASS='mylink' ALIGN='center'>
						<INPUT TYPE='radio' NAME = 'res_times' {$dis} onClick = \"do_assgn_reset({$row['assign_id']}, this.form)\" /></TD>\n";

					print "\t<INPUT TYPE='hidden' NAME='frm_the_unit' VALUE='" . addslashes($row['unit_name']) . "'>\n";  
					print "\t<INPUT TYPE='hidden' NAME='frm_contact_via' VALUE='" . $row['contact_via'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_responder_id' VALUE='" . $row['unit_id'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_ticket_id' VALUE='" . $row['ticket_id'] . "'>\n";
					print "\t<INPUT TYPE='hidden' NAME='frm_assign_id' VALUE='" . $row['assign_id'] . "'>\n";		// 1/12/09 
//					print "\t<INPUT TYPE='hidden' NAME='frm_mailed' VALUE='" . $row['mailed'] . "'>\n";				// 3/25/09
					print "\n</TR>\n";
				} // end if $inviewed	6/10/11
					print "\n\t</FORM>\n";				// 10/19/12
				
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
		for (i=0; i< document.forms.length; i++) {			// look at each form - 1/8/2013
		
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_dispatched)	&& (!document.forms[i].frm_dispatched.disabled )	&& (document.forms[i].frm_dispatched.checked))	{do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_responding)	&& (!document.forms[i].frm_responding.disabled )	&& (document.forms[i].frm_responding.checked))	{do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_on_scene)	&& (!document.forms[i].frm_on_scene.disabled )		&& (document.forms[i].frm_on_scene.checked))	{do_this_form(i);}
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_u2fenr)		&& (!document.forms[i].frm_u2fenr.disabled )		&& (document.forms[i].frm_u2fenr.checked))		{do_this_form(i);}	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_u2farr)		&& (!document.forms[i].frm_u2farr.disabled )		&& (document.forms[i].frm_u2farr.checked))		{do_this_form(i);}	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_clear)		&& (!document.forms[i].frm_clear.disabled )			&& (document.forms[i].frm_clear.checked))		{do_this_form(i); do_refresh = true;}		// 6/16/09

//			if ((document.forms[i].name.substring(0,1)=="F") && (document.forms[i].frm_clear) && (!document.forms[i].frm_clear.disabled ) && (document.forms[i].frm_clear.checked)) {do_this_form(i); do_refresh = true;}		// 6/16/09
			}
		if (do_refresh) {document.can_Form.submit();}		//  at least one checked item - do screen refresh  6/16/09
		}		// end function do all()

	function clr_all_btn(){
		var a_check = false;

		for (i=0; i< document.forms.length; i++) {			// look at each form - 1/8/2013
			if ((document.forms[i].name.substring(0,1)=="F")	&& (document.forms[i].frm_dispatched)	&& (!document.forms[i].frm_dispatched.disabled )	&& (document.forms[i].frm_dispatched.checked)) 		{a_check = true; }
			if ((document.forms[i].name.substring(0,1)=="F")	&& (document.forms[i].frm_responding)	&& (!document.forms[i].frm_responding.disabled )	&& (document.forms[i].frm_responding.checked)) 		{a_check = true; }
			if ((document.forms[i].name.substring(0,1)=="F")	&& (document.forms[i].frm_on_scene)		&& (!document.forms[i].frm_on_scene.disabled )		&& (document.forms[i].frm_on_scene.checked)) 		{a_check = true; }
			if ((document.forms[i].name.substring(0,1)=="F")	&& (document.forms[i].frm_u2fenr)		&& (!document.forms[i].frm_u2fenr.disabled )		&& (document.forms[i].frm_u2fenr.checked)) 			{a_check = true; }	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F")	&& (document.forms[i].frm_u2farr)		&& (!document.forms[i].frm_u2farr.disabled )		&& (document.forms[i].frm_u2farr.checked)) 			{a_check = true; }	//10/6/09
			if ((document.forms[i].name.substring(0,1)=="F")	&& (document.forms[i].frm_clear)		&& (!document.forms[i].frm_clear.disabled ) 		&& (document.forms[i].frm_clear.checked)) 			{a_check = true; }
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
		<BODY><CENTER>		<!-- <?php echo __LINE__; ?> -->
<?php	
														// if (!empty($row['clear'])) ??????
			extract($_POST);

			$query = "SELECT *,
			`as_of` AS `as_of`, 
			`dispatched` AS `dispatched`, 
			`responding` AS `responding`, 
			`on_scene` AS `on_scene`, 
			`u2fenr` AS `u2fenr`, 
			`u2farr` AS `u2farr`, 
			`clear` AS `clear`,  
			`problemstart`) AS `problemstart`,  
			`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , 
			`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
			`u`.`user` AS `theuser`,
			`t`.`scope` AS `theticket`,
			`s`.`status_val` AS `thestatus`, 
			`r`.`name` AS `theunit`,
			`r`.`id` AS `resp_id`
			FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
			WHERE `$GLOBALS[mysql_prefix]assigns`.`id` = $frm_id LIMIT 1";
	
			$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$asgn_row = stripslashes_deep(mysql_fetch_assoc($asgn_result));

//			dump($asgn_row);	
?>
			<TABLE BORDER=0 ALIGN='center'>
			<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
			<TR CLASS="even"><TD colspan=2 ALIGN="center">Call Assignment (#<?php print $asgn_row['assign_id']; ?>)</TD></TR>
			<TR CLASS="odd" VALIGN="baseline" onClick = "viewT('<?php print $asgn_row['ticket_id'];?>')">
				<TD CLASS="td_label" ALIGN="right">&raquo; <U>Incident</U>:</TD><TD>
<?php
			print $asgn_row['scope'] . "</TD></TR>\n";		
	
			if (!$asgn_row['resp_id']=="0"){
//				$unit_name = $asgn_row['name'];
				$unit_name = $asgn_row['handle'];						// 4/28/11
				$unit_link = " onClick = \"viewU('" . $asgn_row['resp_id'] . "')\";";
				$highlight = " &raquo;";
				}
			else {
				$highlight = "";
				$unit_name = "<FONT COLOR='red'><B>UNASSIGNED</B></FONT>";
				$unit_link = "";
				}
			print "<TR CLASS='even' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD><TD>" . format_date_2($asgn_row['as_of']) .
				"&nbsp;&nbsp;&nbsp;&nbsp;By " . $asgn_row['user'] . "</TD></TR>\n";		
			print "<TR CLASS='odd' VALIGN='baseline' " . $unit_link . ">";
			print "<TD CLASS='td_label' ALIGN='right'> " . $highlight . "<U>" . get_text("Units") . "</U>:</TD><TD>" . $unit_name ."</TD></TR>\n";
	
			print "<TR CLASS='even' VALIGN='baseline'>\n";
			print "<TD CLASS='td_label' ALIGN='right'>&nbsp;&nbsp;" . get_text("Units") . " Status:</TD><TD>";
			if ($asgn_row['resp_id']!="0"){
				print $asgn_row['status_val'];
				}		// end if (!$asgn_row['resp_id']=="0")
			else {
				print "NA";
				}
?>
			</TD></TR>
			<!-- 1764 -->
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Incident start:</TD>	<TD><?php print (format_date_2($asgn_row['problemstart'])) ;?></TD></TR>
<?php
		$the_str = (!(good_date_time ($asgn_row['dispatched'])))? "": my_date_diff($asgn_row['problemstart'], $asgn_row['dispatched']);
?>
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">Dispatched:</TD>	<TD><?php print format_date_2($asgn_row['dispatched'])  . "&nbsp;&nbsp;" . $the_str;?></TD></TR>
<?php
		$the_str = (!(good_date_time ($asgn_row['responding'])))? "": my_date_diff($asgn_row['problemstart'], $asgn_row['responding']);
?>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Responding:</TD>	<TD><?php print (format_date_2($asgn_row['responding']))   . "&nbsp;&nbsp;" . $the_str;?></TD></TR>
<?php
		$the_str = (!(good_date_time ($asgn_row['on_scene'])))? "": my_date_diff($asgn_row['problemstart'], $asgn_row['on_scene']);
?>
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">On scene:</TD>		<TD><?php print (format_date_2($asgn_row['on_scene']))   . "&nbsp;&nbsp;" . $the_str;?></TD></TR>
<?php
		$the_str = (!(good_date_time ($asgn_row['u2fenr'])))? "": my_date_diff($asgn_row['problemstart'], $asgn_row['u2fenr']);
?>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Fac en-route:</TD>	<TD><?php print (format_date_2($asgn_row['u2fenr']))   . "&nbsp;&nbsp;" . $the_str;?></TD></TR> <!--10/6/09-->
<?php
		$the_str = (!(good_date_time ($asgn_row['u2farr'])))? "": my_date_diff($asgn_row['problemstart'], $asgn_row['u2farr']);
?>
			<TR CLASS = 'odd'><TD CLASS="td_label" ALIGN="right">Fac arr:</TD>		<TD><?php print (format_date_2($asgn_row['u2farr']))  . "&nbsp;&nbsp;" . $the_str;?></TD></TR> <!--10/6/09-->
<?php
		$the_str = (!(good_date_time ($asgn_row['clear'])))? "": my_date_diff($asgn_row['problemstart'], $asgn_row['clear']);
?>
			<TR CLASS = 'even'><TD CLASS="td_label" ALIGN="right">Clear:</TD>		<TD><?php print (format_date_2($asgn_row['clear']))   . "&nbsp;&nbsp;" . $the_str;?></TD></TR>
			<TR CLASS = 'even'>
				<TD CLASS="td_label" ALIGN="right">Mileage:</TD>		
				<TD><?php print "
						start &raquo;{$asgn_row['start_miles']}&nbsp;&nbsp; 
						on-scene &raquo; {$asgn_row['on_scene_miles']}&nbsp;&nbsp; 
						end &raquo; {$asgn_row['end_miles']}&nbsp;&nbsp;
						tot &raquo; {$asgn_row['miles']}";?>
				</TD>
			</TR>
			
			<TR CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Comments:</TD>
				<TD ALIGN='left'><?php print $asgn_row['assign_comments']; ?></TD></TR> <!-- 10/06/09 -->
			
			<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<br>
				<INPUT TYPE="BUTTON" VALUE="Cancel"  onClick="history.back();"  CLASS = 'btn' />&nbsp;&nbsp;&nbsp;&nbsp;	
<?php
			if(!($guest)){
				print "<INPUT TYPE='BUTTON' VALUE='Edit' onClick='document.nav_form.func.value=\"edit\";document.nav_form.frm_id.value= $frm_id;document.nav_form.submit();'  CLASS = 'btn'>\n";
				}
?>			
				</TD></TR>
			 </tbody></table>
			<INPUT TYPE='hidden' NAME='func' value= ''>
			<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
			<INPUT TYPE='hidden' NAME='resp_id' value='<?php print $asgn_row['resp_id']; ?>'>
			<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'/>			
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
				if (theForm.frm_unit_id.value == 0)			{errmsg+= "\tSelect <?php print get_text("Units");?>\n";}
				}
			if (theForm.frm_unit_status_id) {
				if (theForm.frm_unit_status_id.value == 0)	{errmsg+= "\tSelect <?php print get_text("Units");?> Status\n";}
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
//		if (get_variable('call_board')==1) {print "\t\treSizeScr(18);\n";}
?>				
		function enable(instr) {
			var element= instr
			$(element).style.visibility = "visible";
			for (i=0; i<document.forms[0].length;i++){
					var start = document.forms[0].elements[i].name.length - instr.length
					if (instr == document.forms[0].elements[i].name.substring(start,99)) {
						document.forms[0].elements[i].disabled = false;
						}
				}
			}
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "reSizeScr(13)"><CENTER>		<!-- <?php echo __LINE__; ?> -->
			<DIV ID = 'edit_btns' STYLE="display:block; position:fixed; width:120px; height:auto; top:<?php print $from_top + 20;?>px; right: 150px; background-color:transparent; text-align:left;">	<!-- 5/17/09 -->				
				<INPUT TYPE="button" VALUE="Cancel" onClick="history.back();" CLASS = 'btn' />	
				<INPUT TYPE="button" VALUE="Reset" onclick="document.edit_Form.reset();"  CLASS = 'btn' />	
				<INPUT TYPE="button" VALUE="           Next           " name="sub_but" onClick="document.edit_Form.submit();"  CLASS = 'btn' >  
				</LEFT>
			</DIV>
		
<?php
			$query = "SELECT *, `as_of` AS `as_of`, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` , `$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
				`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit`, `r`.`id` AS `resp_id` FROM `$GLOBALS[mysql_prefix]assigns`
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
				<TD CLASS="td_label" ALIGN="right"><?php print get_text("Units");?>:</TD>
<?php
				if ($asgn_row['resp_id']==0) {
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
				<TD CLASS="td_label"><?php print get_text("Units");?> Status:</TD><TD>
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
					<SPAN CLASS="td_label"> Start:</SPAN> 
						<INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_strt" VALUE="<?php print $asgn_row['start_miles']; ?>" TYPE="text" <?php print $disabled;?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN CLASS="td_label"> On scene:</SPAN> 
						<INPUT MAXLENGTH="8" SIZE="8" NAME="frm_on_scene_miles" VALUE="<?php print $asgn_row['on_scene_miles']; ?>" TYPE="text" <?php print $disabled;?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN CLASS="td_label">End:</SPAN>
						<INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_end" VALUE="<?php print $asgn_row['end_miles']; ?>" TYPE="text" <?php print $disabled;?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN CLASS="td_label">Total Miles:</SPAN>	<!-- 10/23/12 -->
						<INPUT MAXLENGTH="8" SIZE="8" NAME="frm_miles_tot" VALUE="<?php print $asgn_row['miles']; ?>" TYPE="text" <?php print $disabled;?>></TD></TR>	<!-- 10/23/12 -->						
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
				<TD colspan=2><?php print format_date_2($asgn_row['as_of']);?>&nbsp;&nbsp;&nbsp;&nbsp;By: <?php print $asgn_row['user'];?></TD>
				</TR>		
	
<!--		<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
				<br>
				<INPUT TYPE="BUTTON" VALUE="Cancel"  onClick="history.back();"  CLASS = 'btn'>
<?php
				if (!$disabled) {
?>			
				&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="BUTTON" VALUE="Reset"  onclick="Javascript: do_reset(document.edit_Form)"  CLASS = 'btn'/>&nbsp;&nbsp;&nbsp;&nbsp;	
				<INPUT TYPE="BUTTON" VALUE=" Next " name="sub_but" onClick = "validate_ed(document.edit_Form)"  CLASS = 'btn' />
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
			<INPUT TYPE='hidden' NAME='frm_by_id' value= "<?php print $_SESSION['user_id'];?>"/>
			<INPUT TYPE='hidden' NAME='func' value= 'edit_db'/>
			<INPUT TYPE='hidden' NAME='frm_complete' value= ''/> 
			<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'/>
<?php
			if ($do_unit) {
				print "\t\t<INPUT TYPE='hidden' NAME='frm_unit_id' value= '" .  $asgn_row['resp_id'] . "'/>\n";
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
//			dump($_POST);
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
				$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) . ", `clear`= " . quote_smart($now) . " WHERE `id` = " . quote_smart($_POST[frm_id]) . " LIMIT 1";
				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				}
			
			$frm_dispatched =	(array_key_exists('frm_db', $_POST))? 	quote_smart($_POST['frm_year_dispatched'] . "-" . $_POST['frm_month_dispatched'] . "-" . $_POST['frm_day_dispatched']." " . $_POST['frm_hour_dispatched'] . ":". $_POST['frm_minute_dispatched'] .":00") : "";
			$frm_responding = 	(array_key_exists('frm_rb', $_POST))? 	quote_smart($_POST['frm_year_responding'] . "-" . $_POST['frm_month_responding'] . "-" . $_POST['frm_day_responding']." " . $_POST['frm_hour_responding'] . ":". $_POST['frm_minute_responding'] .":00") : "";
			$frm_on_scene = 	(array_key_exists('frm_ob', $_POST))?  	quote_smart($_POST['frm_year_on_scene'] . "-" .   $_POST['frm_month_on_scene'] . "-" .   $_POST['frm_day_on_scene']." " .   $_POST['frm_hour_on_scene'] . ":".   $_POST['frm_minute_on_scene'] .":00") : "";	// 10/20/12
			$frm_u2fenr = 		(array_key_exists('frm_fe', $_POST))?  	quote_smart($_POST['frm_year_u2fenr'] . "-" .   $_POST['frm_month_u2fenr'] . "-" .   $_POST['frm_day_u2fenr']." " .   $_POST['frm_hour_u2fenr'] . ":".   $_POST['frm_minute_u2fenr'] .":00") : "";	//10/6/09
			$frm_u2farr = 		(array_key_exists('frm_fa', $_POST))?  	quote_smart($_POST['frm_year_u2farr'] . "-" .   $_POST['frm_month_u2farr'] . "-" .   $_POST['frm_day_u2farr']." " .   $_POST['frm_hour_u2farr'] . ":".   $_POST['frm_minute_u2farr'] .":00") : "";	//10/6/09
			$frm_clear = 		(array_key_exists('frm_cb', $_POST))?  	quote_smart($_POST['frm_year_clear'] . "-" . 	  $_POST['frm_month_clear'] . "-" 	.    $_POST['frm_day_clear']." " .      $_POST['frm_hour_clear'] . ":".      $_POST['frm_minute_clear'] .":00") : "";
			
			$date_part = (empty($frm_dispatched))? 	"": ", `dispatched`= " . 	$frm_dispatched ;
			$date_part .= (empty($frm_responding))? "": ", `responding`= " . 	$frm_responding;
			$date_part .= (empty($frm_on_scene))? 	"": ", `on_scene`= " 	. 	$frm_on_scene;
			$date_part .= (empty($frm_u2fenr))? 	"": ", `u2fenr`= " 	. 		$frm_u2fenr;
			$date_part .= (empty($frm_u2farr))? 	"": ", `u2farr`= " 	. 		$frm_u2farr;
			$date_part .= (empty($frm_clear))? 		"": ", `clear`= " . 		$frm_clear;

			$unit_sql = (isset($frm_unit_id))?	" `responder_id`=" .quote_smart($frm_unit_id) . ", " :"";			// 1/15/09

//			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET {$unit_sql} `as_of`= " . quote_smart($now) . ", `comments`= " . quote_smart($_POST['frm_comments']) . ", `start_miles`= " . quote_smart($_POST['frm_miles_strt']) . ", `end_miles`= " . quote_smart($_POST['frm_miles_end']) ;	//10/6/09
			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
						 {$unit_sql} `as_of`= " . 	quote_smart($now) . ", 
						 `comments`= " . 			quote_smart($_POST['frm_comments']) . ", 
						 `start_miles`= " . 		quote_smart($_POST['frm_miles_strt']) . ", 
						 `on_scene_miles`= " . 		quote_smart($_POST['frm_on_scene_miles']) . ", 
						 `end_miles`= " . 			quote_smart($_POST['frm_miles_end']) . ",
						 `miles`= " . 				quote_smart($_POST['frm_miles_tot']) ;	//10/6/09, 10/23/12						 

			$query .= $date_part;
			$query .=  " WHERE `id` = " . quote_smart($_POST['frm_id']) . " LIMIT 1";		// 5/26/11

			$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);
//						generate log entry for each changed event - 10/20/12
			$as_query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = " . quote_smart($_POST['frm_id']) . " LIMIT 1";
			$as_result	= mysql_query($as_query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			$as_row = stripslashes_deep(mysql_fetch_assoc($as_result));

			if ((array_key_exists('frm_db', $_POST)) && (quote_smart($as_row['dispatched']) <> $frm_dispatched)) 	{do_log($GLOBALS['LOG_CALL_DISP'], 	$frm_ticket_id, $frm_unit_id, $frm_id);}
			if ((array_key_exists('frm_rb', $_POST)) && (quote_smart($as_row['responding']) <> $frm_responding)) 	{do_log($GLOBALS['LOG_CALL_RESP'], 	$frm_ticket_id, $frm_unit_id, $frm_id);}
			if ((array_key_exists('frm_ob', $_POST)) && (quote_smart($as_row['on_scene']) <> $frm_on_scene)) 		{do_log($GLOBALS['LOG_CALL_ONSCN'],	$frm_ticket_id, $frm_unit_id, $frm_id);}
			if ((array_key_exists('frm_cb', $_POST)) && (quote_smart($as_row['clear']) <> $frm_clear)) 				{do_log($GLOBALS['LOG_CALL_CLR'], 	$frm_ticket_id, $frm_unit_id, $frm_id);}
			if ((array_key_exists('frm_fe', $_POST)) && (quote_smart($as_row['u2fenr']) <> $frm_u2fenr)) 			{do_log($GLOBALS['LOG_CALL_U2FENR'],$frm_ticket_id, $frm_unit_id, $frm_id);}
			if ((array_key_exists('frm_fa', $_POST)) && (quote_smart($as_row['u2farr']) <> $frm_u2farr)) 			{do_log($GLOBALS['LOG_CALL_U2FARR'],$frm_ticket_id, $frm_unit_id, $frm_id);}

//			snap (__LINE__, array_key_exists('frm_db', $_POST));
//			snap (__LINE__, quote_smart($as_row['clear']));
//			snap (__LINE__, $frm_clear);
			$message = "Update Applied";
?>
			</HEAD>
	<BODY><!-- <?php echo __LINE__; ?> -->
		<BR><CENTER><H3><?php print $message; ?></H3><BR>
		<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()"  CLASS = 'btn'/>
		<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
		<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'/>
		</FORM></BODY></HTML><!-- <?php echo __LINE__;?> -->
<?php	
			break;				// end 	case 'edit_db' == } ==
			
	case 'delete_db':		// =====  {  =====================  6/4/08	
		
			$query  = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = " . quote_smart($_POST['frm_id']) . " LIMIT 1";	
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	
			$message = "Assign record deleted";
?>
			</HEAD>
	<BODY><CENTER>		<!-- <?php echo __LINE__; ?> -->
		<BR><BR><CENTER><H3><?php print $message; ?></H3><BR><BR><BR>
		<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
		<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()" CLASS = 'btn'/>
		<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
		</FORM></BODY></HTML><!-- <?php echo __LINE__;?> -->
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
		$row["problemstart"]
		$row["dispatched"]
		$row["responding"]
		$row["on_scene"]
		$row["u2fenr"]
		$row["u2farr"]
		$row["clear"]
		$row["problemend"]
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

	if ((array_key_exists("chg_hide", $_POST)) && ($_POST['chg_hide']==1)) {			// change persistence value - 2/18/09
		$temp = $_POST['hide_cl'];
		@session_start();
		$_SESSION['show_hide_fac'] = 	$temp;		// show/hide closed assigns
		}
	$priorities = array("","severity_medium","severity_high" );

	$where = "WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";

	$query = "SELECT *,
		`as_of` AS `as_of`, 
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
		`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`,
		`t`.`scope` AS `theticket`,
		`t`.`description` AS `thetickdescr`,
		`t`.`status` AS `thestatus`, 
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,
		`r`.`id` AS `theunitid`,
		`r`.`name` AS `theunit` ,
		`r`.`handle` AS `thehandle` ,
		`f`.`name` AS `thefacility`, 
		`g`.`name` AS `the_rec_facility`,
		`$GLOBALS[mysql_prefix]assigns`. `as_of` AS `assign_as_of`
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
	<BODY  onLoad = 'reSizeScr(<?php print $lines;?> )'><!-- <?php echo __LINE__; ?> -->
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
		
			$doUnit = (($guest)||($user))? "viewU" : "editU";	// 5/11/10
			$doTick = ($guest)? "viewT" : "editT";				// 06/26/08
			$now = time() - (get_variable('delta_mins')*60);
			$items = mysql_affected_rows();
			$header = "<TR >";
			
			$header .= "<TD COLSPAN=3 ALIGN='center' CLASS='emph' >Incident</TD>";		// 9/27/08
			$header .= "<TD>&nbsp;</TD>";
			
			$header .= ($facilities)? "<TD COLSPAN=2 ALIGN = 'center' CLASS='emph'>Facility</TD><TD>&nbsp;</TD>" : "";
			$header .= "<TD COLSPAN=1 ALIGN='center' CLASS='emph'>" . get_text("Units") . "</TD>";			// 3/27/09
			$header .= "<TD>&nbsp;</TD>";

			$header .= "<TD COLSPAN=99 ALIGN='center' CLASS='emph' >Dispatch</TD>";
			$header .= "</TR>\n";

			$header .= "<TR CLASS='capt_odd'>";												// 4/26/09, 10/6/09 (unit to facility status)
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
					$in_strike = 	((!(empty($row['scope']))) && ($row['thestatus']== $GLOBALS['STATUS_CLOSED']))? "<STRIKE>": "";					// 11/7/08
					$in_strikend = 	((!(empty($row['scope']))) && ($row['thestatus']== $GLOBALS['STATUS_CLOSED']))? "</STRIKE>": "";
					if (!(empty($row['scope']))) {

						$the_name = addslashes (remove_nls($row['theticket']));															// 10/20/09
						$the_short_name = shorten ($the_name, 10);
						print "\t<TD ALIGN='left' onClick = \"ignore('{$row['ticket_id']}')\" CLASS='{$theClass}' 
							 onmouseover=\"Tip('{$row['ticket_id']}:{$the_name}')\" ALIGN='left' onmouseout=\"UnTip()\">{$in_strike} {$the_short_name}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09
	
						$the_descr = addslashes (remove_nls($row['thetickdescr']));
						$the_short_one = shorten ($the_descr, $COLS_DESCR);
						print "\t<TD ALIGN='left' onClick = \"ignore('{$row['ticket_id']}')\" CLASS='{$theClass}' ALIGN='left' 
							onmouseover=\"Tip('$the_descr')\" onmouseout=\"UnTip()\">{$in_strike} {$the_short_one}{$in_strikend}</TD>\n";		// call 8/24/08, 4/26/09

						$address = (empty($row['tick_street']))? "" : $row['tick_street'] . ", ";
						$address = addslashes($address . $row['tick_city']. " ". $row['tick_state']);
						$short_addr = shorten($address, $COLS_ADDR);
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
							$the_short_name = shorten($row['thefacility'], $COLS_UNIT);
	
							$the_rec_name = addslashes ($row['the_rec_facility']);															// 9/12/09
							$the_rec_short_name = shorten($row['the_rec_facility'], $COLS_UNIT);
							
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
 
//					dump($row['theunitid']);
					if (!($row['theunitid']==0)) {	// theunitid
//						$unit_name = empty($row['theunitid']) ? "[#{$row['responder_id']}]" : addslashes($row['theunit']) ;			// id only if absent
						$unit_name = empty($row['theunitid']) ? "[#{$row['responder_id']}]" : addslashes($row['thehandle']) ;			// id only if absent
						$short_name = shorten($unit_name, $COLS_UNIT);
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
					$the_short_one = shorten ($the_comment, $COLS_COMMENTS);
					
					print "\t<TD ALIGN='left' onmouseover=\"Tip('{$the_comment}')\" onmouseout=\"UnTip()\" CLASS='$theClass' onClick = 'editA(" . $row['assign_id'] . ");' >" . $strike .  $the_short_one . $strikend . "</TD>\n";	// comment
					
					if ($miles) {
						print "\t<TD ALIGN='left' CLASS='$theClass' >" . $row['start_miles'] . "&nbsp;</TD>\n";	
						print "\t<TD ALIGN='left' CLASS='$theClass' >" . $row['on_scene_miles'] . "&nbsp;</TD>\n";	
						print "\t<TD ALIGN='left' CLASS='$theClass' >" . $row['end_miles'] . "&nbsp;</TD>\n";
						if($row['miles'] != NULL) {	//	10/12/23
							$dist = $row_miles;
							} elseif(($row['miles'] == NULL) && ((my_is_int($row['start_miles'])) && (my_is_int($row['end_miles'])))) {
							$dist = $row['end_miles'] -  $row['start_miles'];
							} else {
							$dist = "";
							}
						print "\t<TD ALIGN='left' CLASS='$theClass' >{$dist}</TD>\n";
						}

					print "\n</TR>\n";
					
					$i++;			 
				}		// end while($row ...)
				
				$lines = $i;
//				snap(basename(__FILE__), __LINE__);
				print "<TR><TD COLSPAN=99 ALIGN='center'><BR /><B>End</B><BR /></TD></TR>";
			}		// end if (mysql_affected_rows()>0) 
?>
		</TABLE>
		<DIV ID='foo'><DIV ID='bar'>		<!-- 12/13/09 -->
		<INPUT TYPE='button' VALUE = 'Finished' onClick = 'window.close()'  CLASS = 'btn'>
		</DIV></DIV>
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

	<FORM NAME='can_Form' METHOD="post" ACTION = "<?php echo basename(__FILE__);?>"/>
	<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
	<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines;?>'/>
	</FORM>

	<FORM NAME='sort_Form' METHOD="post" ACTION = "<?php echo basename(__FILE__);?>"/>	<!-- 6/21/10 -->
	<INPUT TYPE='hidden' NAME='func' VALUE='board'/>
	<INPUT TYPE='hidden' NAME='sort' VALUE='unit'/>
	<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines;?>'/>
	</FORM>

	<FORM NAME='temp_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>" >
	<INPUT TYPE='hidden' NAME='func' VALUE='add_b'/>
	<INPUT TYPE='hidden' NAME='frm_ticket_id' value=''/>
	</FORM>

	</BODY></HTML><!-- <?php echo __LINE__;?> -->
<?php
	}		// end else ...		1/13/09

?>	
