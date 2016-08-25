<?php

/*
1/3/10 complete re-write to support button light-up for multi-user operation
1/11/10 added do_set_sess_exp()
4/1/10 JSON replaces eval
4/5/10 do_time, cb width calc, cb script rename, syncAjax() {
4/7/10 $cycle added, 'mu_init' to 'get_latest.php', unit position change now tracked
4/10/10 replaced JSON return with tab-sep'd string
4/11/10 removed poll value references
4/15/10 fullscreen=no
5/12/10 show/hide Board button
6/12/10 browser id, audible alarms added for new ticket, chat invite
7/3/10 changed Card to SOP's
7/21/10 hide cb frame on logout
7/27/10 Unit login handling added
7/28/10 window focus added, logout moved to top row
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/8/10 implment version no. as hyphen-separated string
8/16/10 convert synch ajax to asynch
8/20/10 'term' => 'mobile'
8/21/10 light up active module/button
8/24/10 emd card handling cleanup
8/25/10 server variables handling cleaned up
8/27/10 chat error detection
10/28/10 additions to support modules
1/7/11  JSON re-introduced with length validation and parseInt()
3/15/11 added reference to stylesheet.php for revisable day night colors.
5/4/11 day/night color changes added
5/10/11 log window width increased
6/28/11 try/catch added to accommodate main's new auto-refresh
2/10/12	added logout() call to error detection 3 places
2/25/12 action and patient data to button light-up
2/27/12 div's added for latest ticket, assigns, action and patient
10/23/12 Added code for messaging
5/13/2013 added ics-213 button conditional on setting value
5/24/2013 - websockets code added
5/29/2013 - revised message handling/notification, do_logout() calls commented out in try/catch error handling
5/30/2013 - set 5-second poll cycle.
6/3/2013 - made HAS button appearance conditional on setting
7/2/2013 include setting internet in HAS include
7/16/13 Revisions to strings for top bars which fail on intial load after install and stop buttons from showing.
10/25/13 Revised get_filelist and associated timer.
1/3/14 Added Road Condition Alert markers and live moving unit markers
1/30/14 Revised new message handling and added unread messages flag
3/23/2015 - corrected script-name to 'os_watch' 2 places
3/30/2015 - added OSW initialization
4/2/2015 - added data existence check
9/16/2015 - revise OSW operation
*/

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');		// 7/28/10
require_once('./incs/browser.inc.php');			// 6/12/10
@session_start();
session_write_close();
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}
//$temp = intval(get_variable('auto_poll'));
//$poll_cycle_time = ($temp > 0)? ($temp * 1000) : 15000;	// 5/30/2013
$poll_cycle_time = 5000;	// five seconds to ms - 8/20/10

$browser = trim(checkBrowser(FALSE));						// 6/12/10
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE><?php print ucwords (LessExtension(basename(__FILE__)));?> </TITLE>
<META NAME="Author" CONTENT="" />
<META NAME="Keywords" CONTENT="" />
<META NAME="Description" CONTENT="" />
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">

<STYLE type="text/css">
	table			{border-collapse:collapse;}
	table, td, th	{border:0px solid black;}
	.message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	input		{background-color:transparent;}		/* Benefit IE radio buttons */
</STYLE>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>	<!-- 1/6/11 JSON call-->
<SCRIPT SRC='./js/md5.js'></SCRIPT>				<!-- 11/30/08 -->

<SCRIPT>
	var current_butt_id = "main";
	var internet = false;
	var is_messaging = 0;

<?php if(file_exists("./incs/modules.inc.php")) { ?>
	var ticker_active = <?php print module_active("Ticker");?>;
<?php } else { ?>
	var ticker_active = 0;
<?php } ?>

	var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
		
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

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	function do_time() {		//4/5/10
		var today=new Date();
		today.setDate(today.getSeconds()+7.5);		// half-adjust
		var hours = today.getHours();
		var h=(hours < 10)?  "0" + hours : hours ;
		var mins = today.getMinutes();
		var m=(mins < 10)?  "0" + mins : mins ;
		$('time_of_day').innerHTML=h+":"+m;
		}

	var the_time = setInterval("do_time()", 15000);
	var is_initialized = false;
	var nmis_initialized = false;	//	10/23/12
	var mu_interval = null;
	var nm_interval = null;			//	10/23/12
	var msgs_interval = null;		//	10/23/12
	var emsgs_interval = null;		//	10/23/12
	var pos_interval = null;
	var lit=new Array();
	var lit_r = new Array();
	var lit_o = new Array();
	var unread_messages = 0;
	var hasUsercount = 0;
	var chat_id = 0;				// new chat invite - 8/25/10
	var ticket_id = 0;				// new ticket
	var unit_id;					// 'moved' unit
	var updated;					// 'moved' unit date/time
	var dispatch;					// latest dispatch status change - date-time
	var new_msg = 0;				// New messages, 10/23/12
	var the_unit = 0;
	var the_status = 0;
	var the_time = 0;

	var d = new Date();				// millisecs since 1970/01/01
	var chk_osw_at = d.getTime(); 	// when to check OSW
	var ws_server_started = false;
	var mu = false;
	
	function start_server() {
		var randomnumber=Math.floor(Math.random()*99999999);
		var url ="./socketserver/server.php?version=" + randomnumber;
		var obj; 
		obj = new XMLHttpRequest();
		obj.onreadystatechange = function() {
			if(typeof parent.frames["main"].Socket_startup == 'function') {
				setTimeout(function(){parent.frames["main"].Socket_startup(); }, 5000);
				}
			if(typeof Socket_startup == 'function') {
				setTimeout(function(){Socket_startup(); }, 5000);
				}
			}
		obj.open("POST", url, true);
		obj.send(null);
		}
	
	function end_server() {
		var randomnumber=Math.floor(Math.random()*99999999);
		var url = './socketserver/deletefile.php';
		sendRequest (url,server2_cb, "");
		function server2_cb(req) {
			}
		}
		
	window.addEventListener("unload", end_server(), false); 
	
	function do_msgs_loop() {		//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		if (window.XMLHttpRequest) {
			xmlHttp = new XMLHttpRequest();
			xmlHttp.open("GET", "./ajax/get_messages.php?version=" + randomnumber, true);
			xmlHttp.onreadystatechange = handleRequestStateChange2;
			xmlHttp.send(null);
			}
		}			// end function do_msgs_loop()

	function handleRequestStateChange2() {	//	10/23/12, 1/30/14
		var the_resp;
		var the_val;
		if (xmlHttp.readyState == 4) {
			if (xmlHttp.status == 200) {
				var response = JSON.decode(xmlHttp.responseText);
				if(response) {
					if(response[0]) {
						for(var key in response[0]) {
							the_resp = key;
							the_val = response[0][key];
							un_stat_chg(the_resp, the_val);
							}
						}
					if(response[1]) {
						var the_mess = response[1][0];
						var the_stored = response[1][1];
						if(the_stored != 0) {
							show_msg("There are " + the_stored + " new messages");
							msg_signal_r();								// light the msg button
							} else {
							msg_signal_r_off();								// unlight the msg button
							}
						}
					}
				}
			}
		}

	function do_loop() {								// monitor for changes - 4/10/10, 6/10/11
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('get_latest_id.php?version=' + randomnumber,get_latest_id_cb, "");
		}			// end function do_loop()

	function do_latest_msgs_loop() {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('./ajax/list_message_totals.php?version=' + randomnumber,get_latest_messages_cb, "");
		}
	function un_stat_chg(unit_id, the_stat_id) {	//	10/23/12
		var the_stat_control = "frm_status_id_u_" + unit_id;
		if(typeof parent.frames["main"].change_status_sel == 'function') {
			parent.frames["main"].change_status_sel(the_stat_control, the_stat_id);
			}
		}

	var arr_lgth_good = 15;								// size of a valid returned array - 3/23/2015
	var gd = new Date();								// 3/23/2015
	var g_time_now = gd.getTime();						// set global vals
	g_priority_run_at = g_normal_run_at = g_routine_run_at = g_time_now ;					// next routine  when-to-run ( 60 mins nominal default)

	function get_latest_id_cb(req) {					// get_latest_id callback() - 8/16/10
		try {
			var the_id_arr=JSON.decode(req.responseText);	// 1/7/11
			}
		catch (e) {
//			alert(req.responseText);
//			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
//			do_logout();				// 2/10/12
			return;
			}

		try {
			var the_arr_lgth = the_id_arr.length;		// sanity check
			}
		catch (e) {
//			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
//			do_logout();				// 2/10/12
			return;
			}
//		alert("237 " + the_id_arr[12]);
//		alert("238 " + the_id_arr.length);
		if (the_arr_lgth != arr_lgth_good)  {
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
//			do_logout();				// 2/10/12
			}
		var temp = parseInt(the_id_arr[0]);				// new chat invite?
		if (temp != chat_id) {
			chat_id = temp;
			chat_signal();								// light the chat button
			}
		$("div_ticket_id").innerHTML = the_id_arr[1].trim();	// 2/19/12
		var temp =  parseInt(the_id_arr[1]);			// ticket?
		if (temp != ticket_id) {
			ticket_id = temp;
			tick_signal();								// light the ticket button
			if(typeof parent.frames["main"].load_incidentlist == 'function') {
				parent.frames["main"].load_incidentlist();
				}
			}
		var temp =  parseInt(the_id_arr[2]);			// unit?
		var temp1 =  the_id_arr[3].trim();				// unit timestamp?
		if ((temp != unit_id) || (temp1 != updated)) {	//	10/23/12
			unit_id = temp;
			updated =  temp1;							// timestamp this unit
			$('unit_id').innerHTML = unit_id;			// unit id
			unit_signal();								// light the unit button
			if(typeof parent.frames["main"].load_responderlist == 'function') {
				parent.frames["main"].load_responderlist();
				}
			if(typeof parent.frames["main"].load_responderlist2 == 'function') {
				parent.frames["main"].load_responderlist2();
				}
			}

		$("div_assign_id").innerHTML = the_id_arr[4].trim();			// 2/19/12
//		alert("201 " + the_id_arr[4].trim());
		if (the_id_arr[4].trim() != dispatch)  {		// 1/21/11
			dispatch = the_id_arr[4].trim();
			unit_signal();								// sit scr to blue
			}

		if (the_id_arr[5].trim() != $("div_action_id").innerHTML)  {		// 2/25/12
			misc_signal();													// situation button blue if ...
			$("div_action_id").innerHTML = the_id_arr[5].trim();
			}

		if (the_id_arr[6].trim() != $("div_patient_id").innerHTML)  {		// 2/25/12
			misc_signal();													// situation button blue if ...
			$("div_patient_id").innerHTML = the_id_arr[6].trim();
			}
		if (the_id_arr[7] != $("div_requests_id").innerHTML) {	//		9/10/13
			if(the_id_arr[7] != "0") {
				$("div_requests_id").innerHTML = the_id_arr[7];
				$("reqs").style.display = "inline-block";
				$("reqs").innerHTML = "Open Requests = " + the_id_arr[7];
				} else if (the_id_arr[8] != "0") {
				$("div_requests_id").innerHTML = the_id_arr[7];
				$("reqs").style.display = "inline-block";
				$("reqs").innerHTML = "Requests";
				} else {
				$("div_requests_id").innerHTML = the_id_arr[7];
				$("reqs").style.display = "none";
				$("reqs").innerHTML = "";
				}
			}

		var temp2 =  parseInt(the_id_arr[9]);			// unit?	9/10/13
		var temp3 =  parseInt(the_id_arr[10]);			// status?	9/10/13
		var temp4 =  the_id_arr[11].trim();				// unit timestamp?	9/10/13
		if ((temp2 != the_unit) || (temp3 != the_status) || (temp4 != the_time)) {	//		9/10/13
			the_unit = temp2;	//		9/10/13
			the_status = temp3;	//		9/10/13
			the_time =  temp4;	// timestamp this unit, 	9/10/13
			un_stat_chg(the_unit, the_status);	//		9/10/13
			}
// 									9/16/2015
		var d = new Date();
		var rightNow = d.getTime(); 									// millisecs since 1970/01/01
		if ( rightNow > chk_osw_at ) {
			chk_osw_at = rightNow + ( 60000 );							// set next check at one minute from rightNow
			if ( the_id_arr[12] == 1 ) {								// run on-scene watch?
				var rand = Math.floor(Math.random() * 10000);			// cache buster
				var window_addr = "os_watch.php?rand=" + rand;
				newwindow_co=window.open( window_addr, "On-Scene Watch",  "titlebar, location=0, resizable=1, scrollbars, height=240,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
				setTimeout(function() { newwindow_co.focus(); }, 1);
				}
			}			// end ( rightNow > chk_osw_at )

		}			// end function get_latest_id_cb()

	function get_latest_messages_cb(req) {					// get_latest_messages callback(), 10/23/12, 1/30/14
		var the_msg_arr=JSON.decode(req.responseText);
		var the_number = parseInt(the_msg_arr[0][0]);
		unread_messages = the_number;
		if(unread_messages != 0) {
			$("msg").innerHTML = "Msgs (" + unread_messages + ")";
			msg_signal_o();
			} else {
			$("msg").innerHTML = "Msgs";
			msg_signal_o_off();
			}
		new_msgs_get();
		}			// end function get_latest_messages_cb()
	function toHex(x) {
		hex="0123456789ABCDEF";almostAscii=' !"#$%&'+"'"+'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ['+'\\'+']^_`abcdefghijklmnopqrstuvwxyz{|}';r="";
		for(i=0;i<x.length;i++){
			let=x.charAt(i);pos=almostAscii.indexOf(let)+32;
			h16=Math.floor(pos/16);h1=pos%16;r+=hex.charAt(h16)+hex.charAt(h1);
			};
		return r;
		};

	function mu_get() {								// set cycle
		if (mu_interval!=null) {return;}			// ????
		mu_interval = window.setInterval('do_loop()', <?php print $poll_cycle_time;?>);		// 4/7/10
		}			// end function mu get()
	function new_msgs_get() {								// set cycle, 10/23/12
		if (nm_interval!=null) {return;}			// ????
		nm_interval = window.setInterval('do_latest_msgs_loop()', 30000);
		}			// end function mu get()

	function messages_get() {								// set cycle, 10/23/12
		if (msgs_interval!=null) {return;}			// ????
		msgs_interval = window.setInterval('do_msgs_loop()', 30000);
		}			// end function mu get()
		
	function mu_init() {								// get initial values from server -  4/7/10
		if(mu) {return;}
		mu = true;
		var theBroadcast =  <?php print get_variable('broadcast');?>;
		if(parseInt(theBroadcast) == 1) {
			start_server();
			}
		var randomnumber=Math.floor(Math.random()*99999999);
		if (is_initialized) { return; }
		is_initialized = true;
		sendRequest ('get_latest_id.php?version=' + randomnumber,init_cb, "");
			function init_cb(req) {
				var the_id_arr=JSON.decode(req.responseText);				// 1/7/11
				if (the_id_arr.length != arr_lgth_good)  {						// 2/25/12, 10/23/12
					alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
					}
				else {
					chat_id =  parseInt(the_id_arr[0]);
					ticket_id = parseInt(the_id_arr[1]);
					unit_id =  parseInt(the_id_arr[2]);
					updated =  the_id_arr[3].trim();					// timestamp this unit
					dispatch = the_id_arr[4].trim();					// 1/21/11
					if($("div_ticket_id")) {$("div_ticket_id").innerHTML = the_id_arr[1].trim();}	// 2/19/12
					if($("div_assign_id")) {$("div_assign_id").innerHTML = the_id_arr[4].trim();}	// 2/19/12
					if($("div_action_id")) {$("div_action_id").innerHTML = the_id_arr[5].trim();}	// 2/25/12
					if($("div_patient_id")) {$("div_patient_id").innerHTML = the_id_arr[6].trim();}	// 2/25/12
					if(the_id_arr[7] != "0") {	//		9/10/13
						if($("div_requests_id")) {$("div_requests_id").innerHTML = the_id_arr[7];}
						if($("reqs")) {$("reqs").style.display = "inline-block";}
						if($("reqs")) {$("reqs").innerHTML = "Open Requests = " + the_id_arr[7];}
						} else if (the_id_arr[8] != "0") {
						if($("div_requests_id")) {$("div_requests_id").innerHTML = the_id_arr[7];}
						if($("reqs")) {$("reqs").style.display = "inline-block";}
						if($("reqs")) {$("reqs").innerHTML = "Requests";}
						} else {
						if($("div_requests_id")) {$("div_requests_id").innerHTML = the_id_arr[7];}
						if($("reqs")) {$("reqs").style.display = "none";}
						if($("reqs")) {$("reqs").innerHTML = "";}
						}
					}
				mu_get();				// start loop
				var is_messaging = parseInt("<?php print get_variable('use_messaging');?>");
				if((is_messaging == 1) || (is_messaging == 2) || (is_messaging == 3)) {
					get_msgs();
					nm_init();
					}
				}				// end function init_cb()
		}				// end function mu_init()

	function nm_init() {								// get initial values from server -  10/23/12, 1/30/14
		var randomnumber=Math.floor(Math.random()*99999999);
		if (nmis_initialized) { return; }
		nmis_initialized = true;
		sendRequest ('./ajax/list_message_totals.php?version=' + randomnumber,msg_cb, "");
			function msg_cb(req) {
				var the_msg_arr=JSON.decode(req.responseText);
				var the_number = parseInt(the_msg_arr[0][0]);
				unread_messages = the_number;
				if(unread_messages != 0) {
					if($("msg")) {$("msg").innerHTML = "Msgs (" + unread_messages + ")";}
					msg_signal_o();
					} else {
					if($("msg")) {$("msg").innerHTML = "Msgs";}
					msg_signal_o_off();
					}
				new_msgs_get();
				}			// end function msg_cb()
		}				// end function nm_init()
// for messages
	function get_msgs() {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			xmlHttp = new XMLHttpRequest();
			xmlHttp.open("GET", "./ajax/get_messages.php?version=" + randomnumber, true);
			xmlHttp.onreadystatechange = handleRequestStateChange;
			xmlHttp.send(null);
			}
		}
	function handleRequestStateChange() {	//	10/23/12, 1/30/14
		var the_resp;
		var the_val;
		if (xmlHttp.readyState == 4) {
			if (xmlHttp.status == 200) {
				var response = JSON.decode(xmlHttp.responseText);
				for(var key in response[0]) {
					the_resp = key;
					the_val = response[0][key];
					un_stat_chg(the_resp, the_val);
					}
				if(response[1]) {
					var the_mess = response[1][0];
					var the_stored = response[1][1];
					if(the_stored != 0) {
						show_msg("There are " + the_stored + " new messages");
						msg_signal_r();								// light the msg button
						} else {
						msg_signal_r_off();								// unlight the msg button
						}
					}
				}
			}
		messages_get();
		}

// for responder positions
	function do_positions() {	//	1/3/14
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			respxmlHttp = new XMLHttpRequest();
			respxmlHttp.open("GET", "./ajax/responder_data.php?version=" + randomnumber, true);
			respxmlHttp.onreadystatechange = readPositions;
			respxmlHttp.send(null);
			}
		}
	function readPositions() {	//	1/3/14
		if (respxmlHttp.readyState == 4) {
			if (respxmlHttp.status == 200) {
				var resp_positions = JSON.decode(respxmlHttp.responseText);
				for(var key in resp_positions) {
					var the_resp_id = resp_positions[key][0];
					var the_resp_lat = parseFloat(resp_positions[key][4]);
					var the_resp_lng = parseFloat(resp_positions[key][5]);
					if(typeof parent.frames["main"].set_marker_position == 'function') {
						parent.frames["main"].set_marker_position(the_resp_id, the_resp_lat, the_resp_lng);
						}
					}
				}
			}
		positions_get();
		}
	function positions_get() {			// set cycle, 1/3/14
		if (pos_interval!=null) {return;}			// ????
		pos_interval = window.setInterval('do_positions_loop()', 30000);
		}			// end function mu get()
	function do_positions_loop() {	//	1/3/14
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			respxmlHttp = new XMLHttpRequest();
			respxmlHttp.open("GET", "./ajax/responder_data.php?version=" + randomnumber, true);
			respxmlHttp.onreadystatechange = readPositions2;
			respxmlHttp.send(null);
			}
		}
	function readPositions2() {	//	1/3/14
		if (respxmlHttp.readyState == 4) {
			if (respxmlHttp.status == 200) {
				var resp_positions = JSON.decode(respxmlHttp.responseText);
				for(var key in resp_positions) {
					var the_resp_id = resp_positions[key][0];
					var the_resp_lat = parseFloat(resp_positions[key][4]);
					var the_resp_lng = parseFloat(resp_positions[key][5]);
					if(typeof parent.frames["main"].set_marker_position == 'function') {
						parent.frames["main"].set_marker_position(the_resp_id, the_resp_lat, the_resp_lng);
						}
					}
				}
			}
		}
	function do_set_sess_exp() {			// set session expiration  - 1/11/10
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('set_cook_exp.php?version=' + randomnumber,set_cook_exp_handleResult, "");
		}
	function set_cook_exp_handleResult() {
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

	function syncAjax(strURL) {							// synchronous ajax function - 4/5/10
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
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
			return false;
			}
		}		// end function sync Ajax()

	function do_audible(element) {	// 6/12/10
		a = typeof a !== 'undefined' ? a : 'incident';
		try 		{
		document.getElementById(element + '_alert').currentTime=0;
		document.getElementById(element + '_alert').play();}
		catch (e) 	{console.log(e);}		// ignore
		}				// end function do_audible()

	function get_line_count() {							// 4/5/10
		var url = "do_get_line_ct.php";
		var payload = syncAjax(url);						// does the work
		return payload;
		}		// end function get line_count()

	function chat_signal() {									// light the button
		CngClass("chat", "signal_r");
		lit["chat"] = true;
		do_audible('chat');				// 6/12/10
		}
	function unit_signal() {										// light the units button and - if not already lit red - the situation button
		do_audible('unit');
		if (lit["main"]) {return; }									// already lit - possibly red
		CngClass("main", "signal_b");
		lit["main"] = true;
		}
	function msg_signal() {										// light the msg button, 10/23/12, 1/30/14
		if (lit["msg"]) {return; }									// already lit - possibly red
		CngClass("msg", "signal_b");
		lit["msg"] = true;
		}
	function msg_signal_r() {										// light the msg button, 10/23/12, 1/30/14
		if (lit_r["msg"]) {return; }									// already lit - possibly red
		CngClass("msg", "signal_r");
		lit_r["msg"] = true;
		do_audible();				// 1/20/14
		}

	function msg_signal_r_off() {										// light the msg button, 10/23/12, 1/30/14
		if (!lit_r["msg"]) {return; }									// not lit ignore
		if(unread_messages != 0) {
			CngClass("msg", "signal_o");
			lit_o["msg"] = true;
			} else {
			if(lit["msg"]) {
				CngClass("msg", "signal_b");
				lit_o["msg"] = false;
				} else {
				CngClass("msg", "plain");
				lit_o["msg"] = false;
				}
			}
		lit_r["msg"] = false;
		lit_o["msg"] = true;
		}
	function msg_signal_o() {										// light the msg button, 10/23/12, 1/30/14
		if (lit_o["msg"]) {return; }									// already lit - possibly red
		if (lit_r["msg"]) {return; }
		CngClass("msg", "signal_o");
		lit_o["msg"] = true;
		}

	function msg_signal_o_off() {										// light the msg button, 10/23/12, 1/30/14
		if (!lit_o["msg"]) {return; }									// not lit ignore
		if (lit_r["msg"]) {
			CngClass("msg", "signal_r");
			} else {
			if(lit["msg"]) {
				CngClass("msg", "signal_b");
				lit_o["msg"] = false;
				} else {
				CngClass("msg", "plain");
				lit_o["msg"] = false;
				}
			}
		lit_o["msg"] = false;
		}
	function tick_signal() {										// red light the button
		CngClass("main", "signal_r");
		lit["main"] = true;
		do_audible('incident');				// 6/12/10
		}
																	// 2/25/12
	function misc_signal() {										// blue light to situation button if not already lit
		if (lit["main"]) {return; }									// already lit - possibly red
		CngClass("main", "signal_b");
		lit["main"] = true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function do_hover (the_id) {
		if (the_id == current_butt_id) {return true;}				// 8/21/10
		if (lit[the_id]) {return true;}
		CngClass(the_id, 'hover');
		return true;
		}
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
	function do_plain (the_id) {				// 8/21/10
		if (the_id == current_butt_id) {return true;}
		if (lit[the_id] ) {return true;}
		CngClass(the_id, 'plain');
		return true;
		}
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
		return true;
		}
	function do_signal (the_id) {		// lights the light
		lit[the_id] = true;
		CngClass(the_id, 'signal');
		return true;
		}
	function do_off_signal (the_id) {
		CngClass(the_id, 'plain')
		return true;
		}

	function light_butt(btn_id) {				// 8/24/10 -
		CngClass(btn_id, 'signal_w')			// highlight this button
		if(!(current_butt_id == btn_id)) {
			do_off_signal (current_butt_id);	// clear any prior one if different
			}
		current_butt_id = btn_id;				//
		}				// end function light_butt()

	function go_there (where, the_id) {		//
		CngClass(the_id, 'signal_w')			// highlight this button
		if(!(current_butt_id == the_id)) {
			do_off_signal (current_butt_id);	// clear any prior one if different
			}
		current_butt_id = the_id;				// 8/21/10
		lit[the_id] = false;
		document.go.action = where;
		document.go.submit();
		}				// end function go there ()

	function go_there_win(where, the_id) {
		CngClass(the_id, 'signal_w')                    // highlight this button
                if(!(current_butt_id == the_id)) {
                        do_off_signal (current_butt_id);        // clear any prior one if different
                        }
                current_butt_id = the_id;                               // 8/21/10
                lit[the_id] = false;
		newwindow_c=window.open(where, "New Ticket",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		}

	function show_msg (msg) {
		$('msg_span').innerHTML = msg;
		setTimeout("$('msg_span').innerHTML =''", 3000);	// show for 3 seconds
		}
	function logged_in() {								// returns boolean
		var temp = $("whom").innerHTML==NOT_STR;
		return !temp;
		}

	function do_logout() {						// 10/27/08
		$("user_id").innerHTML  = 0;
		$('time_of_day').innerHTML="";

		clearInterval(mu_interval);
		mu_interval = null;
		clearInterval(nm_interval);	//	10/23/12
		nm_interval = null;	//	10/23/12
		clearInterval(msgs_interval);	//	10/23/12
		msgs_interval = null;	//	10/23/12
		clearInterval(emsgs_interval);	//	10/23/12
		emsgs_interval = null;	//	10/23/12
		$('whom').innerHTML=NOT_STR;
		is_initialized = false;
		nmis_initialized = false;	//	10/23/12
		if(ticker_active == 1) {
			clearInterval(ticker_interval);
			var ticker_interval = null;
			ticker_is_initialized = false;
		}

		try {						// close() any open windows
			newwindow_c.close();
			}
		catch(e) {
			}
		try {
			newwindow_sl.close();
			}
		catch(e) {
			}
		try {
			newwindow_cb.close();
			}
		catch(e) {
			}
		try {
			newwindow_fs.close();
			}
		catch(e) {
			}
		try {
			newwindow_em.close();
			}
		catch(e) {
			}

		newwindow_sl = newwindow_cb = newwindow_c = newwindow_fs = newwindow_em = null;

		hide_butts();		// hide buttons

<?php if (get_variable('call_board') == 2) { ?>

		parent.document.getElementById('the_frames').setAttribute('rows', '<?php print (get_variable('framesize') + 25);?>, 0, *'); // 7/21/10

<?php } ?>

		$('gout').style.display = 'none';		// hide the logout button
		document.gout_form.submit();			// send logout
		}
	function hide_butts() {						// 10/27/08, 3/15/11
		setTimeout(" $('buttons').style.display = 'none';" , 500);
		$("daynight").style.display = "none";				// 5/2/11
		$("main_body").style.backgroundColor  = "<?php print get_css('page_background', 'Day');?>";
		$("main_body").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("tagline").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("user_id").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("unit_id").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("script").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("time_of_day").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("whom").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("level").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("perms_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("modules_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("time_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		try {
			$('manual').style.display = 'none';		// hide the manual link	- possibly absent
			}
		catch(e) {
			}
		}

	function show_butts() {						// 10/27/08
		$("buttons").style.display = "inline";
		$("daynight").style.display = "inline";
		$("has_form_row").style.display = "none";		// 5/26/2013
		$("has_message_row").style.display = "none";
		}
//	============== module window openers ===========================================

	function open_FWindow(theFilename) {										// 9/10/13
		var url = theFilename;
		var ofWindow = window.open(url, 'ViewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { ofWindow.focus(); }, 1);
		}

	var newwindow_sl = null;
	var starting;

	function do_sta_log() {				// 1/19/09
		light_butt('log') ;
		if ((newwindow_sl) && (!(newwindow_sl.closed))) {newwindow_sl.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}						// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			newwindow_sl=window.open("log.php", "sta_log",  "titlebar, location=0, resizable=1, scrollbars, height=240,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
			if (isNull(newwindow_sl)) {
				alert ("Station log operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_sl.focus();
			starting = false;
			}
		}		// end function do sta_log()
	var newwindow_msg = null;
	function do_mess() {				// 10/23/12
		light_butt('msg') ;
		if ((newwindow_msg) && (!(newwindow_msg.closed))) {newwindow_msg.focus(); return;}		// 10/23/12
		if (logged_in()) {
			if(starting) {return;}
			starting=true;
			do_set_sess_exp();		// session expiration update
			newwindow_msg=window.open("messages.php", "messages",  "titlebar, location=0, resizable=1, scrollbars=no, height=600,width=950,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300");
			if (isNull(newwindow_msg)) {
				alert ("Viewing messages requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_msg.focus();
			starting = false;
			}
		}		// end function do sta_log()
	var newwindow_cb = null;
	function do_callBoard() {
		light_butt('call');
		if ((newwindow_cb) && (!(newwindow_cb.closed))) {newwindow_cb.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}						// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			var the_height = 60 + (16 * get_line_count());
			var the_width = (2.0 * Math.floor((Math.floor(.90 * screen.width) / 2.0)));

			newwindow_cb=window.open("board.php", "callBoard",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+", width="+the_width+", status=0,toolbar=0,menubar=0,location=0, left=20,top=300,screenX=20,screenY=300");

			if (isNull(newwindow_cb)) {
				alert ("Call Board operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_cb.focus();
			starting = false;
			}
		}		// end function do callBoard()
	var newwindow_c = null;

	function chat_win_close() {				// called from chat.pgp
		newwindow_c = null;
		}
	function do_chat() {
		light_butt('chat') ;
		if ((newwindow_c) && (!(newwindow_c.closed))) {newwindow_c.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}					// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			try {
				newwindow_c.focus();
				}
			catch(e) {
				}

			newwindow_c=window.open("chat.php", "chatBoard",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
			if (isNull(newwindow_c)) {
				alert ("Chat operation requires popups to be enabled. Please adjust your browser options - or else turn off the Chat option setting.");
				return;
				}
			newwindow_c.focus();
			starting = false;
			CngClass("chat", "plain");

			}
		}
		
	var newwindow_fs = null;
	function do_full_scr() {                            //9/7/09
		light_butt('full');
		if ((newwindow_fs) && (!(newwindow_fs.closed))) {newwindow_fs.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}                        // 4/15/10 fullscreen=no
			do_set_sess_exp();		// session expiration update
			if(window.focus() && newwindow_fs) {newwindow_fs.focus()}    // if already exists
			starting=true;
			params  = 'width='+screen.width;
			params += ', height='+screen.height;
			params += ', top=0, left=0, scrollbars = 1';
			params += ', resizable=1';
			newwindow_fs=window.open("full_scr.php", "full_scr", params);
			if (isNull(newwindow_fs)) {
				alert ("This operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_fs.focus();
			starting = false;
			}
		}        // end function do full_scr()
		
	var newwindow_wsm = null;
	function do_wsm_scr() {                            //9/7/09
		if ((newwindow_wsm) && (!(newwindow_wsm.closed))) {newwindow_wsm.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}                        // 4/15/10 fullscreen=no
			do_set_sess_exp();		// session expiration update
			if(window.focus() && newwindow_wsm) {newwindow_wsm.focus()}    // if already exists
			starting=true;
			params  = 'width=500';
			params += ', height=400';
			params += ', top=100, left=100, scrollbars = 0';
			params += ', resizable=1';
			newwindow_wsm=window.open("ws_monitor.php", "Websocket_Monitor", params);
			if (isNull(newwindow_wsm)) {
				alert ("This operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_wsm.focus();
			starting = false;
			}
		}        // end function do full_scr()

	function do_emd_card(filename) {
		light_butt('card') ;
		try {
			newwindow_em=window.open(filename, "emdCard",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
			}
		catch (e) {
			}
		try {
			newwindow_em.focus();;
			}
		catch (e) {
			}
		if (isNull(newwindow_em)) {
			alert ("SOP Doc's operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		starting = false;
		}

<?php
$start_up_str = 	(array_key_exists('user', $_SESSION))? "": " mu_init();";
$the_userid = 		(array_key_exists('user_id', $_SESSION))? $_SESSION['user_id'] : "na"; 	//	7/16/13
$the_whom = 		(array_key_exists('user', $_SESSION))? $_SESSION['user']: NOT_STR;
$the_level = 		(array_key_exists('level', $_SESSION))? get_level_text($_SESSION['level']):"na";

$day_night = (array_key_exists('day_night', $_SESSION)) ? $_SESSION['day_night'] : 'Day';
print "\n\t var the_whom = '{$the_whom}'\n";
print "\t var the_level ='{$the_level}'\n";

function get_daynight() {
	$day_night = ((array_key_exists('day_night', $_SESSION)) && ($_SESSION['day_night'])) ? $_SESSION['day_night'] : 'Day';
	return $day_night;
	}
?>
	function do_day_night(which){
		for (i=0;i<document.day_night_form.elements.length;i++) {
			if ((document.day_night_form.elements[i].type=='radio') && (document.day_night_form.elements[i].name=='frm_daynight')) {
				if (document.day_night_form.elements[i].value == which) {
					document.day_night_form.elements[i].checked = true;
					document.day_night_form.elements[i].disabled = true;
					}
				else {
					document.day_night_form.elements[i].checked = false;
					document.day_night_form.elements[i].disabled = false;
					}
				}				// end if (type=='radio')
			}
		}		// end function do_day_night()

	function guest_hide_buttons(level) {
		if((level == "Guest") || (level == 1)) {
			window.guest = 1;
			if($("msg")) {$("msg").style.display  = "none";}
			if($("reps")) {$("reps").style.display  = "none";}
			if($("conf")) {$("conf").style.display  = "none";}
			if($("card")) {$("card").style.display  = "none";}
			if($("chat")) {$("chat").style.display  = "none";}
			if($("log")) {$("log").style.display  = "none";}
			if($("rc")) {$("rc").style.display  = "none";}
			if($("links")) {$("links").style.display  = "none";}
			if($("call")) {$("call").style.display  = "none";}
			if($("term")) {$("term").style.display  = "none";}
			if($("reqs")) {$("reqs").style.display  = "none";}
			if($("ics")) {$("ics").style.display  = "none";}
			if($("has_button")) {$("has_button").style.display  = "none";}
			}
		}
		
	function unit_hide_buttons() {
		alert("Hide Buttons for Restricted Units");
		if(the_level == "Unit") {
			if($("buttons")) {$("buttons").style.display  = "none";}
			}
		}	

	function top_init() {					// initialize display
		CngClass('main', 'signal_w');		// light up 'sit' button - 8/21/10
		$("whom").innerHTML  =	the_whom;
		$("level").innerHTML =	the_level;
		do_time();
<?php												// 5/4/11
		if (empty($_SESSION)) {						// pending login
			$day_checked = $night_checked = "";
			$day_disabled = $night_disabled= "DISABLED";
			}
		else {				// logged-in
			if ($start_up_str == 'Day') {	//	7/16/13	Revised to fix error on initial startup
				$day_checked = "CHECKED";			// allow only 'night'
				$day_disabled = "DISABLED";
				$night_checked = "";
				$night_disabled = "";
				}
			else {
				$day_checked = "";					//  allow only 'day'
				$day_disabled = "";
				$night_checked = "CHECKED";
				$night_disabled = "DISABLED";
				}
?>
		var current_user_id = "<?php print $the_userid;?>";
			guest_hide_buttons();
			show_butts();	// navigation buttons
			$("gout").style.display  = "inline";								// logout button
			$("user_id").innerHTML  = "<?php print $the_userid;?>";		//	7/16/13
			$("whom").innerHTML  = "<?php print $the_whom;?>";			// user name, 7/1613
			$("level").innerHTML = "<?php print $the_level;?>";		//	7/16/13
			mu_init();			// start polling
<?php
			}				// end if/else (empty($_SESSION))
?>
		}		// end function top_init()

	function do_log (instr) {
		$('log_div').innerHTML += instr + "<br />";
		}

	function get_new_colors() {										// 5/4/11 - a simple refresh
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function set_day_night(which) {			// 5/2/11
		sendRequest ('./ajax/do_day_night_swap.php', day_night_callback, "");
			function day_night_callback(req) {
				var the_ret_val = req.responseText;
				try {
					parent.frames["main"].get_new_colors();			// reloads main frame
					}
				catch (e) {
					}
				window.clearInterval(mu_interval);
				window.clearInterval(nm_interval);	//	10/23/12
				window.clearInterval(msgs_interval);	//	10/23/12
				window.clearInterval(emsgs_interval);	//	10/23/12
				get_new_colors();								// reloads top
				}									// end function day_night_callback()
		}

	function do_manual(filename){							// launches Tickets manual page -  5/27/11
		try {
			newwindow_em=window.open(filename, "Manual",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=20,top=20,screenX=20,screenY=20");
			}
		catch (e) {
			}
		try {
			newwindow_em.focus();;
			}
		catch (e) {
			}
		}		// end do_manual()

		function can_has () {							// cancel HAS function - return to normal display
			$("has_form_row").style.display = "none";
			show_butts();								// show buttons
			}
		function end_message_show() {
			setTimeout(function(){
				$("has_message_row").style.display = $("has_form_row").style.display = "none";
				$("has_form_row").style.display = "none";
				show_butts();								// show buttons
				}, 1000);			// end setTimeout()
			}					// end function

<?php				// 7/2/2013
		if ((intval( get_variable ('broadcast')==1)) &&  (intval(get_variable ('internet')==1))) { 		//
?>
			function do_broadcast() {
				if(hasUsercount > 1) {
					$("has_form_row").style.display = "inline-block";
					$("has_message_row").style.display = "none";
					document.has_form.has_text.focus()
					} else {
					hide_butts();
					$("has_form_row").style.display = "none";
					$("has_message_text").innerHTML = "There are no other users connected, messages will not be sent";
					CngClass("has_message_text", "heading");
					$("has_message_row").style.display = "block";	// include button		
					}
				}
			function has_check(inStr) {
				if (inStr.trim().length == 0) { alert("Value required - try again."); return;}
				else {
					var msg =  $("whom").innerHTML + " sends: " + inStr.trim(); // identify sender
					broadcast(msg, 1); 				// send it
					setTimeout(function(){
						CngClass("has_text", "heading");
						document.has_form.has_text.value = "              Sent!";		// note spaces
						setTimeout(function(){
							document.has_form.has_text.value = "";
							$("has_form_row").style.display = "none";		// hide the form row
							CngClass("has_text", "");
							show_butts();								// back to normal
							}, 3000);
						}, 1000);
					}		// end else{}
				}		// end function has_check()

			function hide_has_message_row() {
				$("msg_span").style.display = "none";
				show_butts();								// show buttons
				}

			function show_has_message(in_message) {
				hide_butts();											// make room
				$("has_message_text").innerHTML = in_message;			// the message text
				CngClass("has_message_text", "heading");
				$("has_message_row").style.display = "block";	// include button
				}
<?php
			}			// end if (broadcast && internet )
?>
	</SCRIPT>
</HEAD>
<BODY ID="main_body" onLoad = "top_init();">	<!-- 3/15/11, 10/23/12 -->
<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV> <!-- 2/25/12 -->
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV ID = "div_requests_id" STYLE="display:none;"></DIV>	<!-- 10/23/12 -->

	<TABLE ALIGN='left'>
		<TR VALIGN='top' style='height: 30px;'>
			<TD ROWSPAN=4><IMG SRC="<?php print get_variable('logo');?>" BORDER=0 /></TD>
			<TD>
<?php

	$temp = get_variable('_version');				// 8/8/10
	$version_ary = explode ( "-", $temp, 2);
	if(get_variable('title_string')=="") {
		$title_string = "<FONT SIZE='3'>ickets " . trim($version_ary[0]) . " on <B>" . get_variable('host') . "</B></FONT>";
		} else {
		$title_string = "<FONT SIZE='3'><B>" .get_variable('title_string') . "</B></FONT>";
		}
?>
				<SPAN ID="tagline" CLASS="titlebar_text"><?php print $title_string; ?></SPAN>	<!-- 3/15/11 -->
				<SPAN ID="logged_in_txt" STYLE = 'margin-left: 8px;' CLASS="titlebar_text"><?php print get_text("Logged in"); ?>:</SPAN>	<!-- 3/15/11 -->
				<SPAN ID="whom" CLASS="titlebar_text"><?php print NOT_STR ; ?></SPAN>
				<SPAN ID="perms_txt" CLASS="titlebar_text">:<SPAN ID="level" CLASS="titlebar_text">na</SPAN>&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
<?php
	$temp = get_variable('auto_poll');

	$dir = "./emd_cards";
	if (file_exists ($dir)) {
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if ((strlen($filename)>2) && (get_ext($filename)=="pdf"))  {
			    $card_file = $filename;						// at least one pdf, use first encountered
			    break;
			    }
			}
		$card_addr=(!empty($card_file))? $dir . "/" . $filename  : "";
		}
?>
				<SPAN ID='user_id' STYLE="display:none" CLASS="titlebar_text">0</SPAN><!-- default value - 5/29/10, 3/15/11 -->
				<SPAN ID='unit_id' STYLE="display:none" CLASS="titlebar_text"></SPAN><!-- unit that has just moved - 4/7/10, 3/15/11 -->
				<SPAN ID='modules_txt' CLASS="titlebar_text"><?php print get_text("Module"); ?>: </SPAN><SPAN ID="script" CLASS="titlebar_text">login</FONT></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
				<SPAN ID='daynight' CLASS="titlebar_text"  STYLE = 'display:none'>
					<FORM NAME = 'day_night_form' STYLE = 'display: inline-block'>
											<!-- set in  above -->
					<INPUT TYPE="radio" NAME="frm_daynight" VALUE="Day" <?php print "{$day_disabled} {$day_checked}" ;?> 		onclick = ' set_day_night(this.value);'>Day&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE="radio" NAME="frm_daynight" value="Night" <?php print "{$night_disabled}  {$night_checked}" ;?> onclick = 'set_day_night(this.value);' >Night&nbsp;&nbsp;&nbsp;&nbsp;
					</FORM>
				</SPAN>
				<SPAN ID='time_txt' CLASS="titlebar_text"><?php print get_text("Time"); ?>: </SPAN><b><SPAN ID="time_of_day" CLASS="titlebar_text"></SPAN></b></FONT>&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
<?php				// 5/26/11
	$dir = "./manual";
	if (file_exists ($dir)) {
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if ((strlen($filename)>2) && (get_ext($filename)=="pdf"))  {
			    $manual_file = $filename;						// at least one pdf, use first encountered
			    break;
			    }
			}
		$manual_addr=(!empty($manual_file))? $dir . "/" . $filename  : "";
		}
	if (!(empty($manual_addr))) {
?>

				<SPAN ID='manual' CLASS="titlebar_text" onClick = "do_manual('<?php echo $manual_addr;?>');" STYLE="display:none;"  ><U>Manual</U></SPAN>
<?php
			}
?>
				<SPAN ID = 'gout' CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_logout()" STYLE="display:none; float: none;"><?php print get_text("Logout"); ?></SPAN> <!-- 7/28/10 -->
<?php
		if ($_SERVER['HTTP_HOST'] == "127.0.0.1") { print "&nbsp;&nbsp;&nbsp;&nbsp;DB:&nbsp;{$mysql_db}&nbsp;&nbsp;&nbsp;&nbsp;";}
?>

				<SPAN ID='msg_span' CLASS = 'message'></SPAN>
				<DIV id='broadcastWrapper' class='plain' TITLE='Click to Open Websocket Server Monitor' style='display: none; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_wsm_scr();'>
					<SPAN ID = 'usercount' CLASS="titlebar_text" style='float: right; font-weight: bold; padding-right: 20px;'></SPAN>
					<SPAN ID = 'timeText' CLASS="titlebar_text" style='float: right; font-weight: bold; padding-right: 20px;'></SPAN>
				</DIV>
				<br />
			</TD>
		</TR>
		<TR>
			<TD ID = 'buttons' STYLE = "display:none">
			<SPAN ID = 'main'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick ="go_there('main.php', this.id);"><?php print get_text("Situation"); ?></SPAN>
<!--		<SPAN ID = 'mi'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick ="go_there('maj_inc.php', this.id);">Maj Incs</SPAN> -->
			<SPAN ID = 'add'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('add.php', this.id);"><?php print get_text("New"); ?></SPAN>
			<SPAN ID = 'resp'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('units.php', this.id);"><?php print get_text("Units"); ?></SPAN>
			<SPAN ID = 'facy'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('facilities.php', this.id);"><?php print get_text("Fac's"); ?></SPAN>
<?php
		if((!is_guest()) && ((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3))) {		//	10/23/12
?>
			<SPAN ID = 'msg'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_mess();"><?php print get_text("Msgs"); ?></SPAN>
<?php
			}
?>
			<SPAN ID = 'srch'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('search.php', this.id);"><?php print get_text("Search"); ?></SPAN>
<?php
		if (!(is_guest())) {
?>
			<SPAN ID = 'reps'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('reports.php', this.id);"><?php print get_text("Reports"); ?></SPAN>
			<SPAN ID = 'conf'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('config.php', this.id);"><?php print get_text("Config"); ?></SPAN>
<?php
			}
		if (!(is_guest()) && !(empty($card_addr))) {
?>
			<SPAN ID = 'card'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting = false; do_emd_card('<?php print $card_addr; ?>')"><?php print get_text("SOP's"); ?></SPAN>	<!-- 7/3/10 -->
<?php
			}
		if((!(is_guest())) && (!(intval(get_variable('chat_time')==0)))) {
?>
			<SPAN ID = 'chat'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_chat();"><?php print get_text("Chat"); ?></SPAN>
<?php
			}
?>
			<SPAN ID = 'help'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('help.php', this.id);"><?php print get_text("Help"); ?></SPAN>
<?php
		if (!(is_guest())) {
?>
			<SPAN ID = 'log'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "do_sta_log()"><?php print get_text("Log"); ?></SPAN>
			<SPAN ID = 'rc'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('rc_redirect.php', this.id)">Road Cond</SPAN>
<?php
			}
?>
			<SPAN ID = 'full'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_full_scr()"><?php print get_text("Full scr"); ?></SPAN>
<?php
		if (!(is_guest())) {
			$call_disp_attr = (get_variable('call_board')==1)?  "inline" : "none";
?>
			<SPAN ID = 'links'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "light_butt('links'); parent.main.$('links').style.display='inline';"><?php print get_text("Links"); ?></SPAN>
			<SPAN ID = 'call'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false;do_callBoard()" STYLE = 'display:<?php print $call_disp_attr; ?>'><?php print get_text("Board"); ?></SPAN> <!-- 5/12/10 -->
<?php
			}
?>
<!-- ================== -->
<?php
		if (!(is_guest())) {
?>
			<SPAN ID = 'term' CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('mobile.php', this.id);"><?php print get_text("Mobile"); ?></SPAN>	<!-- 7/27/10 -->
<!-- ================== -->
			<SPAN ID = 'reqs'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('./portal/requests.php', this.id);">Requests</SPAN>	<!-- 10/23/12 -->
<?php
			}
		if ((!(is_guest())) && (intval(get_variable('ics_top')==1))) { 		// 5/21/2013
?>

<!-- ================== -->			<!-- 5/13/2013 -->
			<SPAN ID = 'ics'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false;window.open('ics213.php', 'ics213')"><?php print get_text("ICS-FORMS"); ?></SPAN> <!-- 5/13/2013 -->
<?php
			}		// end if (ics_top)

		if ((!(is_guest())) && (intval ( get_variable ('broadcast')==1 )) &&  (intval ( get_variable ('internet')==1 )) ) { 		// 6/3/2013 -7/2/2013
?>
			<SPAN ID = 'has_button' CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "do_broadcast();"><?php echo get_text("HAS"); ?></SPAN> <!-- 5/24/2013 -->
<?php
	}			// end if (broadcast && internet )
?>
			</TD>
		</TR>
		<TR ID = 'has_form_row' STYLE = "display:none;">
			<TD ALIGN=CENTER>
				<SPAN ID = "has_span" >
				<FORM NAME = 'has_form' METHOD = post ACTION = "javascript: void(0)">
				<INPUT TYPE = 'text' NAME = 'has_text' ID = 'has_text' CLASS = '' size=90 value = "" STYLE = "margin-left:6px;" placeholder="enter your broadcast message" />
				<BUTTON VALUE="Send" onclick = "has_check ( this.form.has_text.value.trim() )" STYLE = "margin-left:16px;">Send</BUTTON>
				<BUTTON VALUE="Cancel" onclick = "can_has ();" STYLE = "margin-left:24px;">Cancel</BUTTON>
				</FORM>
				</SPAN>
			</TD>
		</TR>

		<TR ID = 'has_message_row' STYLE = "display: none;">
			<TD ALIGN=CENTER>
				<SPAN ID = "msg_span" STYLE = "margin-left:50px; " >
					<SPAN ID = "has_message_text"></SPAN>
					<BUTTON VALUE="OK" onclick = "end_message_show();"  STYLE = "margin-left:20px">OK</BUTTON>
				</SPAN>
			</TD>
		</TR>

<!-- ================== -->
	</TABLE>
	<TABLE ALIGN='center'>
	<FORM NAME="go" action="#" TARGET = "main"></FORM>
	<FORM NAME="gout_form" action="main.php" TARGET = "main">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>

	<P>
		<DIV ID = "log_div"></DIV>
<!-- <button onclick = 'alert(getElementById("user_id"))'>Test</button> -->
<?php
	$the_wav_file = get_variable('sound_wav');		// browser-specific cabilities as of 6/12/10
	$the_mp3_file = get_variable('sound_mp3');

	$temp = explode (" ", $browser);
	switch (trim($temp[0])) {
	    case "firefox" :
			print (empty($the_wav_file))? "\n": "\t\t<audio id=\"incident\" src=\"./sounds/{$the_wav_file}\" preload></audio>\n";
			break;
	    case "chrome" :
	    case "safari" :
			print (empty($the_mp3_file))? "\n":  "\t\t<audio id=\"incident_alert\" src=\"./sounds/{$the_mp3_file}\" preload></audio>\n";
			print (empty($the_mp3_file))? "\n":  "\t\t<audio id=\"chat_alert\" src=\"./sounds/chat-alert.mp3\" preload></audio>\n";
			print (empty($the_mp3_file))? "\n":  "\t\t<audio id=\"message_alert\" src=\"./sounds/chat-alert.mp3\" preload></audio>\n";
			print (empty($the_mp3_file))? "\n":  "\t\t<audio id=\"unit_alert\" src=\"./sounds/unit-alert.mp3\" preload></audio>\n";
			break;
	    default:
		}	// end switch
?>
<!--  example frame manipulation
<button onClick = "alert(parent.document.getElementById('the_frames').getAttribute('rows'));">Get</button>
<button onClick = "parent.document.getElementById('the_frames').setAttribute('rows', '600, 100, *');">Set</button>
-->
<DIV ID='test' style="position: fixed; top: 20px; left: 20px; height: 20px; width: 100px;" onclick = "location.href = '#bottom';">
	<h3></h3></DIV>
<!-- <button onclick = "show_has_message('asasasasas ERERERERER ')">Test</button> -->
<?php							// 7/2/2013
	if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {
		require_once('./incs/socket2me.inc.php');		// 5/24/2013
		}
?>
</BODY>
</HTML>
