<?php
error_reporting(E_ALL);
$interval = 48;				// booked date limit - hide if date is > n hours ahead of 'now'
$blink_duration = 5;		// blink for n (5, here) minutes after ticket was written
$button_height = 50;		// height in pixels
$button_width = 160;		// width in pixels
$button_spacing = 4;		// spacing in pixels
$map_size = .75;			// map size multiplier - as a percent of full size
$butts_width = 0;

$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
/*
7/13/10 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/20/10 handle non-unit access
8/27/10 button alignment, can_edit() added
8/28/10 handle facility events
8/29/10 added disp_status to units line
8/30/10 option size added
9/3/10 added user call selection via $mode
10/8/10 added self-refresh to update 'Other current calls ', DEFINES 
11/10/10 'other calls' centering revised
3/15/11 added reference to stylesheet.php for revisable day night colors
3/19/11 frame logic added per main.php
4/5/11 get_new_colors() added
5/19/11 f_arr_btn button label corrected, unit handle replaces name in list, refresh to current assign selection
5/23/11 Actions/Persons buttons now available to operator/user.
6/19/11 corrections to incident selection check
10/18/11 Added Regions stuff
2/14/12 significant re-write of $assigns_stack logic
2/17/12 corrected $assigns_stack logic. 
2/19/12 div's added for latest ticket, assign id's
2/27/12 blink logic added
*/

session_start();	
require_once('incs/functions.inc.php');	
do_login(basename(__FILE__));
define("UNIT", 0);
define("MINE", 1);
define("ALL", 2);

$istest = FALSE;
if ($istest) {
 dump(__LINE__);

//	if (count($_GET)>0) {
		print "GET<BR/>\n";
		dump ($_GET);
//		}
//	if (count($_POST)>0) {
		print "POST<BR/>\n";
		dump ($_POST);
//		}
	}
	
$internet = $_SESSION['internet'];	
require_once('incs/functions_major_nm.inc.php');				// 7/28/10
$patient = get_text("Patient");									// 12/1/10

// 0=>unit, 1=>my calls, 2=> all calls - 9/3/10 
																// set/initialize $mode 
if (array_key_exists('frm_mode', $_GET)) {$mode =  $_GET['frm_mode'];}
else {						// unset
	if (is_unit())  {
		$mode = UNIT;
		}
	else {

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
		$mode = (intval ($user_row['responder_id'])>0)? MINE: ALL;		// $mode => 'all' if no unit associated this user - 10/3/10
		}
	}		// end if/else initialize $mode

function get_butts($ticket_id, $unit_id) {
	global $patient;
	$win_height =  get_variable('map_height') + 120;
	$win_width = get_variable('map_width') + 10;
	if ($_SESSION['internet']) {
		print "<INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Map' onClick  = \"var popWindow = window.open('map_popup.php?id={$ticket_id}', 'PopWindow', 'resizable=1, scrollbars, height={$win_height}, width={$win_width}, left=250,top=50,screenX=250,screenY=50'); popWindow.focus();\" />\n"; // 7/3/10
		}
	if (can_edit()) {		// 5/23/11
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'New' onClick = \"var newWindow = window.open('add.php?mode=1', 'addWindow', 'resizable=1, scrollbars, height=640, width=800, left=100,top=100,screenX=100,screenY=100'); newWindow.focus();\" />\n"; // 8/9/10
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Edit' onClick = \"var newWindow = window.open('edit_nm.php?mode=1&id={$ticket_id}', 'editWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100'); newWindow.focus();\" />\n"; // 2/1/10

		if (!is_closed($ticket_id)) {		// 10/5/09
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Close' onClick = \"var mailWindow = window.open('close_in.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=480, width=700, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\" />\n";  // 8/20/09
			}
		} 		// end if ($can_edit())
	if (is_administrator() || is_super() || is_unit()){
		if (!is_closed($ticket_id)) {
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Action' onClick  = \"var actWindow = window.open('action_w.php?mode=1&ticket_id={$ticket_id}', 'ActWindow', 'resizable=1, scrollbars, height=480, width=900, left=250,top=50,screenX=250,screenY=50'); ActWindow.focus();\" />\n"; // 7/3/10
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = '{$patient}' onClick  = \"var patWindow = window.open('patient_w.php?mode=1&ticket_id={$ticket_id}', 'patWindow', 'resizable=1, scrollbars, height=480,width=720, left=250,top=50,screenX=250,screenY=50'); patWindow.focus();\" />\n"; // 7/3/10
			}
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Notify' onClick  = \"var notWindow = window.open('config.php?mode=1&func=notify&id={$ticket_id}', 'NotWindow', 'resizable=1, scrollbars, height=400, width=600, left=250,top=50,screenX=250,screenY=50'); notWindow.focus();\" />\n"; // 7/3/10
		}
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Note' onClick = \"var noteWindow = window.open('add_note.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); noteWindow.focus();\" />\n"; // 10/8/08
//	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Print' onClick='main.php?print=true&id=$ticket_id;'>\n ";
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'E-mail' onClick = \"var mailWindow = window.open('mail.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\" />\n"; // 2/1/10
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Dispatch' onClick = \"var dispWindow = window.open('routes_nm.php?frm_mode=1&ticket_id={$ticket_id}', 'dispWindow', 'resizable=1, scrollbars, height=480, width=" . round (0.8 * ($_SESSION['scr_width'])) . ", left=100,top=100,screenX=100,screenY=100'); dispWindow.focus();\" />\n"; // 2/1/10
	}				// end function get butts()

function adj_time($time_stamp) {
	$temp = mysql2timestamp($time_stamp);					// MySQL to integer form
	return date ("H:i", $temp);
	}

// $api_key = get_variable('gmaps_api_key');		// 
	$vers = rand(1,99999);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Mobile Terminal Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="-1" />	<!-- 3/15/11 -->
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print $vers;?>" TYPE="text/css" />	<!-- 3/15/11 -->
	<STYLE>
		input.btn_chkd 		{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#EFEFEF;  border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center; } 
		input.btn_not_chkd 	{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 
		input.btn_smaller 	{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width-40;?>px; height: <?php print $button_height-6;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 
		input:hover 		{ background-color: white; border-width: 4px; border-STYLE: outset;}
		div.sel 			{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 

		select.sit 			{ font: 11px Verdana, Geneva, Arial, Helvetica, sans-serif; background-color: white; color: #102132; border: none;}
		A 					{ FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}
		.disp_stat 			{ FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		option				{ FONT-SIZE: 16px;}
		input				{background-color:transparent;}		/* Benefit IE radio buttons */

		.calls 				{font:15px arial,sans-serif;}
	</STYLE>	
	<SCRIPT TYPE="text/javascript" src="./js/misc_function.js"></SCRIPT>
	<script language="JavaScript">
	<!--
	function showhideFrame(btn) {
		xx = window.top.document.getElementsByTagName("frameset")[0];
		if (xx.rows == "75,*")
			{xx.rows = "0,*";
			var params = "f_n=show_hide_upper&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
			var url = "persist2.php";	//	3/15/11
			sendRequest (url, handleResult, params);
			btn.value = "Show Menu";
		} else {
			xx.rows = "75,*";
			var params = "f_n=show_hide_upper&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
			var url = "persist2.php";
			sendRequest (url, handleResult, params);
			btn.value = "Hide Menu";			
		}
	}
	
	function checkUpper() {
		xx = window.top.document.getElementsByTagName("frameset")[0];	
		var upperVis = "<?php print $_SESSION['show_hide_upper'];?>";
		if (upperVis == "h") {
			xx = window.top.document.getElementsByTagName("frameset")[0];
			xx.rows = "0,*";
			$('b1').value = "Show Menu";
			} else {
			xx.rows = "75,*";
			$('b1').value = "Hide Menu";
		}
	}	
	-->
	</script>	
	<SCRIPT>
<?php
if ( get_variable('call_board') == 2) {		// 3/19/11
	$cb_per_line = 22;						// adjust as needed
	$cb_fixed_part = 60;
	$cb_min = 96;
	$cb_max = 300;
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";	// active calls
	$result = @mysql_query($query);
	$lines = mysql_affected_rows();
	unset($result);
	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;		// vs min
	$height = ($height>$cb_max)? $cb_max: $height;		// vs max
?>
frame_rows = parent.document.getElementById('the_frames').getAttribute('rows');	// get current configuration
var rows = frame_rows.split(",", 4);
rows[1] = <?php print $height ;?>;						// new cb frame height, re-use top
frame_rows = rows.join(",");
parent.document.getElementById('the_frames').setAttribute('rows', frame_rows);
parent.calls.location.href = 'board.php';							// 7/21/10

<?php
	}		// end if ( get_variable('call_board') == 2) 
?>	
//	- 3/19/11	
	function $() {									// 1/21/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}
	
		parent.frames["upper"].$('user_id').innerHTML = "<?php print $_SESSION["user_id"]; ?>";		// 

<?php
	if (array_key_exists('log_in', $_GET)) {						// 12/26/09 unit login? - array_key_exists('hello', $a)
?>
		parent.frames["upper"].mu_init ();										// start polling
		if (parent.frames.length == 3) {										// 1/20/09, 4/10/09
			parent.calls.location.href = 'board.php';							// 1/11/09
			}
<?php
		}		// end unit login

$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$temp = get_unit();															// 3/19/11
$term_str = ($temp )? $temp : "Mobile" ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
?>
	try {
		parent.frames["upper"].$("gout").style.display  = "inline";									// logout button
		parent.frames["upper"].$("user_id").innerHTML  = "<?php print $_SESSION['user_id'];?>";	
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";			// user name
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename(__FILE__));?>";				// module name
		parent.frames["upper"].$("main_body").style.backgroundColor  = "<?php print get_css('page_background', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("main_body").style.color  = "<?php print get_css('normal_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("tagline").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("user_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("unit_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("script").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("time_of_day").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("whom").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("level").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("perms_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("modules_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("time_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11

		parent.frames["upper"].$("term").innerHTML  = "<?php print $term_str;?>";				// responder or 'Mobile' name - 3/19/11

		}
	catch(e) {
		}
	var frame_rows;			// frame
	parent.upper.show_butts();										// 1/21/09
	parent.upper.light_butt('term');								// light it up
	parent.frames["upper"].document.getElementById("gout").style.display  = "inline";


	function get_new_colors() {								// 4/5/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

<?php																	// 4/10/10
	
	if ((intval(get_variable('call_board')) == 2)&& (is_unit())) {						// hide the frame
?>
	frame_rows = parent.document.getElementById('the_frames').getAttribute('rows');
	var rows = frame_rows.split(",", 4);
	rows[1] = 0;
	temp = rows.join(",");
	parent.document.getElementById('the_frames').setAttribute('rows', temp);		// set revised cb frame height
	
<?php
		}		// end if ((intval(get_variable('call_board')) == 2)&& (is_unit()))
?>
	function ck_frames() {		//  onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
<?php																	// 4/10/10
	if (intval(get_variable('call_board')) == 0) {						// hide the 'board' button
		print "\t parent.frames['upper'].$('call').style.display = 'none';";
		}
?>		

function replaceButtonText(buttonId, text) {
	if (document.getElementById) {
		var button=document.getElementById(buttonId);
		if (button) {
			if (button.childNodes[0]) {
				button.childNodes[0].nodeValue=text;
				}
			else if (button.value) {
				button.value=text;
				}
			else {					//if (button.innerHTML) 
				button.innerHTML=text;
				}
			}
		}
	}		// end function replaceButtonText()

	function show_btns_closed() {						// 4/30/10
		$('btn_go').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_closed() {
		$('btn_go').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.frm_interval_sel.frm_interval.selectedIndex=0;
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

	var announce = true;
	function handleResult(req) {			// the called-back function
		if (announce) {alert('<?php echo __LINE__; ?>');}
		}			// end function handle Result(

	
	var announce = true;
	function handleResult(req) {			// the called-back function
		}			// end function handle Result()

	function toss() {				// ignores button click
		return;
		}

	var watch_val;										// interval var - for clearInterval() - 2/19/12

	function start_watch() {							// get initial values from top
		parent.frames['upper'].mu_init();				// start the polling
		$("div_ticket_id").innerHTML = parent.frames["upper"].$("div_ticket_id").innerHTML;		// copy for monitoring
		$("div_assign_id").innerHTML = parent.frames["upper"].$("div_assign_id").innerHTML;
		$("div_action_id").innerHTML = parent.frames["upper"].$("div_action_id").innerHTML;	
		$("div_patient_id").innerHTML = parent.frames["upper"].$("div_patient_id").innerHTML;
		
		watch_val = window.setInterval("do_watch()",5000);		// 4/7/10 - 5 seconds
		}				// end function start watch()

	function end_watch(){
		window.clearInterval(watch_val);
		window.location.reload();
		}				// end function end_watch()

	function do_watch() {								// monitor for changes - 4/10/10, 6/10/11
		if (							// any change?
			($("div_ticket_id").innerHTML != parent.frames["upper"].$("div_ticket_id").innerHTML) ||
			($("div_assign_id").innerHTML != parent.frames["upper"].$("div_assign_id").innerHTML) ||
			($("div_action_id").innerHTML != parent.frames["upper"].$("div_action_id").innerHTML) ||
			($("div_patient_id").innerHTML != parent.frames["upper"].$("div_patient_id").innerHTML)			
			)
				{			  // a change
				end_watch();
				window.location.reload();				
			}
		}			// end function do_watch()		

</SCRIPT>
<?php			// 7/14/09
$buster = strval(rand());			//  cache buster
?>
	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
</HEAD>

<?php																// 0=>unit, 1=>my calls, 2=> all calls - 9/3/10 
if ((($mode==0) || ($mode==1))) {									// pull $the_unit, $the_unit_name, this user
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` 
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `u`.`responder_id` = `r`.`id` )
		WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$user_row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_unit = $user_row['responder_id'];
	$the_unit_name = (empty($user_row['name']))? "NA": $user_row['name'];	// 'NA' if no responder this user
	}
else {
	 $the_unit_name = "NA";
	}

$restrict = ((($mode==UNIT) ) || ($mode==MINE))? " (`responder_id` = {$the_unit}) AND ": "";		// 8/20/10, 9/3/10 
																					// 5/19/11 -  all open assigns
$query = "SELECT *,  `t`.`id` AS `tick_id`,
			`t`.`street` AS `tick_street`,
			`t`.`city` AS `tick_city`,
			`t`.`status` AS `tick_status`,
			`t`.`updated` AS `tick_updated`,
			`r`.`name` AS `unit_name`,
			`r`.`handle` AS `unit_handle`,				
			`a`.`id` AS `assign_id`				
		FROM  `$GLOBALS[mysql_prefix]ticket` `t`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a`  		ON (`a`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`a`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `u`	ON (`r`.`type` = `u`.`id` )	
		WHERE {$restrict}
			((`t`.`status` = {$GLOBALS['STATUS_OPEN']})
			OR ((`t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} AND `t`.`booked_date` < (NOW() + INTERVAL {$interval} HOUR)))
			)
		ORDER BY `t`.`status` DESC, `t`.`severity` DESC, `t`.`problemstart` ASC";

// dump($query);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
if (mysql_affected_rows()==0) {
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

	$for_str = $the_unit_name;

	$caption = ($mode==MINE)? "All calls": $the_unit_name;
	$frm_mode = ($mode==MINE)? ALL: MINE;
/*
<BODY onLoad="checkUpper(); start_watch();" onUnload = "end_watch();"> 
<BODY onLoad="checkUpper();" > <!-- 2/19/12 -->
*/
?>
<BODY onLoad="checkUpper(); start_watch();" onUnload = "end_watch();"> <!-- <?php echo __LINE__;?> -->
	<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>	
	<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>

<BR /><BR /><BR /><BR />
<CENTER>
<input id="b1" type="button" value="Hide Top Menu" onclick="showhideFrame(this)"><BR /><BR /> 
<H2><?php print $for_str;?>: no current calls  as of <?php print substr($now, 11,5);?></H2>
<?php
	if (can_edit()) {
?>
			<FORM NAME = 'switch_form' METHOD = 'get' ACTION = '<?php print basename(__FILE__);?>'>
			<INPUT TYPE='hidden' NAME = 'frm_mode' VALUE = '2' />	
			<INPUT ID='chng_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='All calls' onClick = 'document.switch_form.submit();' />
			</FORM>
<?php
		}
?>
</CENTER>
<?php
	}		// end if (mysql_affected_rows()==0)
	
	
else {						// set up $assigns_stack, $selected_indx - 2/14/12, 2/17/12
 
	$i = $selected_indx = 0;
	$assigns_stack = array();
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		array_push($assigns_stack, $in_row);									// stack it up		
		if (empty($_GET['assign_id']) && empty($_GET['ticket_id'])) {
			if (empty($_GET) && ($i==0))	{$selected_indx = $i;}
			}
		else {
			if 	((empty($assigns_stack[$i]['assign_id'])) && 
				($assigns_stack[$i]['tick_id'] == $_GET['ticket_id'])) 
					{$selected_indx = $i;}
 			elseif (
 				(!empty($_GET['assign_id'])) && 
 				($assigns_stack[$i]['assign_id'] == $_GET['assign_id'])) 
 					{$selected_indx = $i;}
			}
		$i++;
		}		// end while(...)
 
	$assign_id = 	$assigns_stack[$selected_indx]['assign_id'];				// if any
	$ticket_id =  	$assigns_stack[$selected_indx]['tick_id'];					// 2/20/12
	$unit_id =  	$assigns_stack[$selected_indx]['responder_id'];				// if any
?>
<SCRIPT>
// =======================
	dbfns = new Array ();					//  field names per assigns_t.php expectations
	dbfns['d'] = 'frm_dispatched';
	dbfns['r'] = 'frm_responding';
	dbfns['s'] = 'frm_on_scene';
	dbfns['c'] = 'frm_clear';
	dbfns['e'] = 'frm_u2fenr';
	dbfns['a'] = 'frm_u2farr';
	
	btn_ids = new Array ();					//  
	btn_ids['d'] = 'disp_btn';
	btn_ids['r'] = 'resp_btn';
	btn_ids['s'] = 'onsc_btn';
	btn_ids['c'] = 'clear_btn';
	btn_ids['e'] = 'f_enr_btn';
	btn_ids['a'] = 'f_arr_btn';
	
	btn_labels = new Array ();				//  
	btn_labels['d'] = '<?php print get_text("Disp"); ?> @ ';
	btn_labels['r'] = '<?php print get_text("Resp"); ?> @ ';
	btn_labels['s'] = '<?php print get_text("Onsc"); ?> @ ';
	btn_labels['c'] = '<?php print get_text("Clear"); ?> @';
	btn_labels['e'] = 'Fac enr @';
	btn_labels['a'] = 'Fac arr @';
	
	btn_labels_full = new Array ();				//  
	btn_labels_full['d'] = '<?php print get_text("Dispatched"); ?> @ ';
	btn_labels_full['r'] = '<?php print get_text("Responding"); ?> @ ';
	btn_labels_full['s'] = '<?php print get_text("On-scene"); ?> @ ';
	btn_labels_full['c'] = '<?php print get_text("Clear"); ?> @';
	btn_labels_full['e'] = "Fac'y Enr @";
	btn_labels_full['a'] = "Fac'y Arr @";

<?php
	if (!(intval($assign_id) > 0)) {				// dispatch times to be set?
?>
	function set_assign(which) { return; }	// no 	
	function set_rec_fac(which) { return; }	// no 	
<?php
		}
	else  {
?>		
	function set_assign(which) {						// values; d r s c a e
		var params = "frm_id=" +<?php print $assign_id;?>;				// 1/20/09
		params += "&frm_tick=" +<?php print $ticket_id;?>;
		params += "&frm_unit=" +<?php print $unit_id;?>;
		params += "&frm_vals=" + dbfns[which];
		sendRequest ('assigns_t.php',handleResult, params);			// does the work
		var curr_time = do_time();
		replaceButtonText(btn_ids[which], btn_labels[which] + curr_time)
		CngClass(btn_ids[which], 'btn_chkd');				// CngClass(obj, the_class)
		parent.frames['upper'].show_msg (btn_labels_full[which] + curr_time);
<?php
	if (array_key_exists('assign_id', ($_GET))) {			// 5/19/11
?>	
		document.to_refresh.assign_id.value = <?php print $_GET['assign_id']; ?>;
		document.to_refresh.ticket_id.value = <?php print $_GET['ticket_id']; ?>;
<?php
	}

if (get_variable('call_board')==2			) {	
	print "\n\t parent.top.calls.do_refresh();\n";
	}
else {
	print "\n\t document.to_refresh.submit();\n";		// 10/8/10
	}

?>
		}		// end function set_assign()
	function set_rec_fac(which) {	//	10/18/11 function to update receiving facility
		var params = "rec_fac=" +which;
		params += "&unit=" +<?php print $unit_id;?>;
		params += "&tick_id=" +<?php print $ticket_id;?>;
		params += "&frm_id=" +<?php print $assign_id;?>;		
		sendRequest ('rec_fac_t.php',handleResult, params);			// does the work	
		parent.frames['upper'].show_msg ("Receiving Facility Updated");
<?php
		if (array_key_exists('assign_id', ($_GET))) {
?>	
			document.to_refresh.assign_id.value = <?php print $_GET['assign_id']; ?>;
			document.to_refresh.ticket_id.value = <?php print $_GET['ticket_id']; ?>;
<?php
		}

		if (get_variable('call_board')==2			) {	
			print "\n\t parent.top.calls.do_refresh();\n";
			}
		else {
			print "\n\t document.to_refresh.submit();\n";
			}
?>		
	}	//	end function set_rec_fac

<?php
		}		// end if/else (!(intval($assign_id) > 0))
?>			
	function do_blink() {																// 2/27/12
		for(i=0; i<document.getElementsByTagName("blink").length; i++){					// each element
			s=document.getElementsByTagName("blink")[i];
			s.style.visibility=(s.style.visibility=='visible')?'hidden':'visible';		// swap visibility
			}
		blink_count--;								// limit blink duration
		if (blink_count==0) {end_blink();}
		}		// end function do_blink()

	var blink_var = false;
	var blink_count;									// duration of blink

	function start_blink () {
		var temp = document.getElementsByTagName("blink").length;
		if (document.getElementsByTagName("blink").length > 0){			// don't bother if non set
			blink_var = setInterval('do_blink()',500);					// on/off cycle is once per second
			blink_count = 60;											// = 60 seconds
			}
		}
	function end_blink() {
		for(i=0; i<document.getElementsByTagName("blink").length; i++){		//  force visibility each element
			s=document.getElementsByTagName("blink")[i];
			s.style.visibility='visible';	
			}	
		if (blink_var) {clearInterval(blink_var);}
		}
	
</SCRIPT>
<?php
$unload_str = ($_SESSION['internet'])? "GUnload(); end_watch();"  : "end_watch();";
?>
<BODY onLoad="checkUpper(); start_watch(); start_blink();" onUnload = "end_watch(); end_blink();">  <!-- <?php echo __LINE__;?> -->
	<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
		
	<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>	<!-- 2/27/12 -->
	<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
	<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
	<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>

	<DIV ID='to_bottom' style="position:fixed; top:10px; left:150px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 /></div>
	
	<A NAME="top" /> <!-- 11/11/09 -->
<?php
//		$unit_str = (isset($user_row))? " for {$user_row['name']}": "(" . get_units_legend() . ")";
		if (($mode == UNIT) || ($mode == MINE)){
			$my_unit = empty($user_row['name'])? "(NA)": $user_row['name'];
			$unit_str = " for {$my_unit}";
			}
		else {
			$unit_str = "";
			}		
		$margin = 20;				// 11/10/10

?>
<TABLE ID='outermost' BORDER=0 CLASS='normal'>	<!-- 3/15/11 -->
<TR>
	<TD ROWSPAN=2 ID = 'left col'>
		<TABLE BORDER=0><TR><TD>
<?php
			get_butts($ticket_id, $unit_id);
?>
		</TD></TR></TABLE>
	</TD>
	<TD ID = 'ctr top' ALIGN='center'>
		<TABLE BORDER=0 >
		<TR><TD ALIGN='center'><input id="b1" type="button" value="Hide Menu" onclick="showhideFrame(this)"></TD></TR>
		<TR CLASS='spacer'><TD class='spacer'>&nbsp;</TD></TR>
		<TR><TD ALIGN='left'>	<!-- 3/15/11 -->	
<?php
			$id_array = array();
			
			$time_now = mysql_format_date(now());			// collect ticket id's into $id_array 
			
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `updated` > ('{$time_now}' - INTERVAL 5 MINUTE);";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
				array_push($id_array, $in_row['ticket_id']);
				}
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `updated` > ('{$time_now}' - INTERVAL 5 MINUTE);";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
				array_push($id_array, $in_row['ticket_id']);
				}			

			$colors = array("even", "odd");
			echo "<TABLE BORDER=0 CLASS='calls'>\n";		// 
			echo "<TR CLASS = 'even'><TH COLSPAN=7 ALIGN='center'>Current calls {$unit_str}</TH></TR>";		// 
			$the_ticket_id = (array_key_exists('ticket_id', $_GET))? $_GET['ticket_id'] : 0 ;				// possibly empty on initial etry

			for ($i = 0; $i<count($assigns_stack); $i++) {
				echo "<TR VALIGN='top' CLASS= '{$colors[($i + 1) % 2]}'>\n";
				if (($i==0) && ($the_ticket_id==0)) {$the_ticket_id = $assigns_stack[0]['ticket_id'];}		// first entry into mobile
																// column 2
				$the_url = basename(__FILE__) . "?assign_id={$assigns_stack[$i]['assign_id']}&ticket_id={$assigns_stack[$i]['tick_id']}&frm_mode={$mode}\"";

//				if ((now() -  mysql2timestamp($assigns_stack[$i]['tick_updated'])) < $blink_duration*60) {
				if (((now() -  mysql2timestamp($assigns_stack[$i]['tick_updated'])) < $blink_duration*60) ||
					(in_array( $assigns_stack[$i]['tick_id'], $id_array))) {

					$blinkst = "<blink>";
					$blinkend ="</blink>";
					}
				else {$blinkst = $blinkend = "";
					}		
			
//				$checked = ($i == $selected_indx) ? "CHECKED": "";											// this one?
				if ($i == $selected_indx) {
					$checked = "CHECKED";
					$the_ticket_id = $assigns_stack[$i]['tick_id'];
					}
				else {$checked = "";}
				echo "\t<TD><INPUT TYPE = 'radio' NAME = 'others' VALUE='{$i}' {$checked} onClick = 'location.href=\"{$the_url}' /></TD>\n";
// --
				switch($assigns_stack[$i]['severity'])		{					//set cell color by severity
				 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; 	break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; 	break;
					default: 							$severityclass='severity_normal'; 	break;
					}
				$the_icon = intval($assigns_stack[$i]['icon']);					// 6/19/11
				$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$the_icon];		// 8/29/10
				$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$the_icon];
				$unit_handle = addslashes($assigns_stack[$i]['handle']);
				$the_disp_stat = get_disp_status ($assigns_stack[$i]);			// 8/29/10
				echo "\t<TD>&nbsp;<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>{$unit_handle}</SPAN></TD>\n";		// column 2 - handle 
				echo "\t<TD>&nbsp;{$the_disp_stat}</TD>\n";		// column 3-  disp status
				$the_ticket = shorten("{$assigns_stack[$i]['scope']}", 24); 					
				echo "\t<TD CLASS='{$severityclass}' >&nbsp;{$blinkst}{$the_ticket}{$blinkend}</TD>\n";						// column 5 - ticket
				$the_addr = shorten("{$assigns_stack[$i]['tick_street']}, {$assigns_stack[$i]['tick_city']}", 24); 					
				echo "\t<TD CLASS='{$severityclass}' >&nbsp;{$the_addr}</TD>\n";							// column 6 - address
				if($assigns_stack[$i]['tick_status'] == $GLOBALS['STATUS_SCHEDULED']) {
					$the_date = $assigns_stack[$i]['booked_date'];					
					$booked_symb = "<IMG SRC = 'markers/clock.png'/> &nbsp;";
					}
				else {
					$the_date =$assigns_stack[$i]['problemstart'];					
					$booked_symb = "";
					}
				echo "<TD CLASS='{$severityclass}' >" .  format_date_time($the_date) . "</TD>\n";			// column 4 - date
			echo "\t<TD>&nbsp;{$booked_symb}</TD>\n";						// column 7 - booked symb
// --
				echo "</TR>\n";
				}			// end for ($i ...)
			echo "</TABLE>\n";
?>
		</TD></TR></TABLE>

	</TD>
	<TD ROWSPAN=2 ID = 'right col'>
<?php
	
			print "<BR CLEAR = 'left' /><P ALIGN='left'>";
		
			$time_disp =  $assigns_stack[$selected_indx]['dispatched'];
			$time_resp =  $assigns_stack[$selected_indx]['responding'];
			$time_onsc =  $assigns_stack[$selected_indx]['on_scene'];
			$time_clear = $assigns_stack[$selected_indx]['clear'];
			$time_fenr =  $assigns_stack[$selected_indx]['u2fenr'];
			$time_farr =  $assigns_stack[$selected_indx]['u2farr'];
		
	$sb_width = max(320, intval($_SESSION['scr_width']* 0.4));				// 8/27/10
	$map_width = ($_SESSION['internet'])? get_variable('map_width'): 0;
	$position =  $sb_width + $map_width + $butts_width +10;

//	$selected_indx possibly NULL
	$display_val = ($assigns_stack[$selected_indx]["assign_id"]>0)?  "block" : "none";
?>
		<TABLE BORDER=0><TR><TD ID='buttons' style=" height: auto; width: 200px; overflow-y: scroll; overflow-x: hidden;">
<?php	if (is_date($time_disp)) { 
?>
		<INPUT ID='disp_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='Disp @ <?php print adj_time($time_disp) ;?>' onClick = 'toss();'  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			}	
			else  { 
?>
		<INPUT ID='disp_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='Dispatched' onClick = "set_assign('d');"   STYLE = 'display:<?php echo $display_val;?>;' />
<?php			} 
			if (is_date($time_resp)) { 
?>
		<INPUT ID='resp_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='Resp @ <?php print adj_time($time_resp) ;?>' onClick = 'toss();'  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			}	
			else  { 
?>
		<INPUT ID='resp_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='Responding' onClick = "set_assign('r');"  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			} 
			if (is_date($time_onsc)) { 
?>
		<INPUT ID='onsc_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='On-scene @ <?php print adj_time($time_onsc);?>' onClick = 'toss();'  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			}	
			else  { 
?>
		<INPUT ID='onsc_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='On-scene' onClick = "set_assign('s');"  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			} 
			if ($assigns_stack[$selected_indx]['rec_facility_id']>0) {		//	10/18/11 changed to just check if receiving facility is set - Incident at facility is not valid for this function.
				if (is_date($time_fenr)) { 
?>
			<INPUT ID='f_enr_btn' TYPE= 'button' CLASS='btn_chkd' VALUE="Fac'y enr @ <?php print adj_time($time_fenr);?>" onClick = 'toss();'  STYLE = 'display:<?php echo $display_val;?>;' />
<?php				}	
				else  { 
?>
			<INPUT ID='f_enr_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE="Fac'y enroute" onClick = "set_assign('e');"  STYLE = 'display:<?php echo $display_val;?>;' />
<?php				} 	
				if (is_date($time_farr)) { 		// 5/19/11
?>
			<INPUT ID='f_arr_btn' TYPE= 'button' CLASS='btn_chkd' VALUE="Fac'y arr @ <?php print adj_time($time_farr);?>" onClick = 'toss();'  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			}	
				else  { 
?>
			<INPUT ID='f_arr_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE="Fac'y arrive" onClick = "set_assign('a');"  STYLE = 'display:<?php echo $display_val;?>;' />
<?php				} 	
				}		//  end if (facility ... )
				
			if (is_date($time_clear)) { 
?>
		<INPUT ID='clear_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='Clear @ <?php print adj_time($time_clear);?>' onClick = 'toss();'  STYLE = 'display:<?php echo $display_val;?>;' />
<?php			}	
				else  { 
?>
		<INPUT ID='clear_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='Clear' onClick = "set_assign('c');"   STYLE = 'display:<?php echo $display_val;?>;' />	
<?php
					}		// end if (is_date($time_clear))

	if ((is_unit()) || ((has_admin())&&(intval($unit_id)>0))) {				// do/do-not allow status change - 2/7/12
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `u` 
			WHERE `u`.`id` = {$unit_id} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$temp_row = mysql_fetch_assoc($result);    
?>
		<DIV CLASS='sel' style='width: 152px; display:<?php echo $display_val;?>;'><?php print get_text("Status"); ?>:<BR /><?php print get_status_sel($unit_id, $temp_row['un_status_id'], "u", 10);?></DIV>
		<DIV CLASS='sel' style='width: 152px; display:<?php echo $display_val;?>;'><?php print get_text("Receiving Facility"); ?>:<BR /><?php print get_recfac_sel($unit_id, $ticket_id, $assign_id);?></DIV>	<!-- 10/18/11 -->	
<?php
		}

	if ($mode == ALL) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON (`$GLOBALS[mysql_prefix]responder`.`id` = `$GLOBALS[mysql_prefix]user`.`responder_id`)
			WHERE `$GLOBALS[mysql_prefix]user`.`id` = {$_SESSION['user_id']}
			LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
		if (intval($user_row['responder_id'])>0) {
?>		
			<FORM NAME = 'switch_form' METHOD = 'get' ACTION = '<?php print basename(__FILE__);?>'>
			<INPUT TYPE='hidden' NAME = 'frm_mode' VALUE = '1' />	
			<INPUT ID='chng_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE="<?php print $user_row['name'];?>" onClick = 'document.switch_form.submit();' />
			</FORM>
<?php			
	 		}
	 	}				// end  if ($mode == ALL)
	else {		// do all button?
		if (can_edit()) {
?>
			<FORM NAME = 'switch_form' METHOD = 'get' ACTION = '<?php print basename(__FILE__);?>'>
			<INPUT TYPE='hidden' NAME = 'frm_mode' VALUE = '2' />	
			<INPUT ID='chng_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='All calls' onClick = 'document.switch_form.submit();' />
			</FORM>
<?php 
				}		 	// end if (can_edit()
		 	}
?>
		</TD></TR></TABLE>

	</TD>
	</TR>
<TR>
	<TD ID = 'ctr 2nd'>
		<TABLE BORDER=0><TR><TD STYLE='WIDTH:<?php print $butts_width;?>PX'></TD><TD>
<?php
			if (intval($the_ticket_id) > 0) {				// possibly none		-- 2/15/12
				show_ticket($the_ticket_id, NULL, NULL, FALSE );					// hide map -2/7/12
				}
?>
		</TD></TR></TABLE>

	</TD>
</TR>
</TABLE>

<?php
		include("./incs/links.inc.php");
		$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
		$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
		$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
		$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;
	
?>
<A NAME="bottom" /> <!-- 11/11/09 -->
<?php
	}		// end if/else (mysql_affected_rows()==0)

//$caption = ($mode==1)? "All calls": $the_unit_name;	
//$mode = ($mode==1)? ALL: MINE;	
?>
	<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
	<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>' />
	<INPUT TYPE='hidden' NAME='func' VALUE='' />
	</FORM>
	<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
	<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
	</FORM>
	<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
	<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
	</FORM>

	<FORM NAME='to_refresh' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 10/8/10 -->
	<INPUT TYPE='hidden' NAME='frm_mode' VALUE='<?php print $mode;?>' />
	<INPUT TYPE='hidden' NAME='assign_id' VALUE='' />		<!-- 5/19/11 -->
	<INPUT TYPE='hidden' NAME='ticket_id' VALUE='' />		<!-- 2/22/12 -->
	</FORM>
<!--
<p>
<a href="javascript:decreaseFontSize();">-</a> 
<a href="javascript:increaseFontSize();">+</a>
</p>
-->
<DIV ID='to_bottom' style="position:fixed; bottom:50px; left:150px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" BORDER=0 /></div>
</BODY>

</HTML>
