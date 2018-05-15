<?php
/*
*/
$temp1  = get_variable('socketserver_url');
$temp2 = get_variable('socketserver_port');
$host = (array_key_exists("SERVER_NAME", $_SERVER)) ? "{$_SERVER['SERVER_NAME']}" : $temp1;


$isLocal = ($host == "127.0.0.1") ? 1 : 0;
$guest = (is_guest()) ? 1 : 0;
@session_start();
session_write_close();
$user_id = (array_key_exists('user_id', $_SESSION)) ? $_SESSION['user_id'] : 0;
$ishttps = (array_key_exists('HTTPS', $_SERVER)) ? 1 : 0;
if($ishttps) {
	$port = ($temp2 == "") ? "1348" : $temp2;	
	} else {
	$port = ($temp2 == "") ? "1337" : $temp2;
	}

$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users2 = stripslashes_deep(mysql_fetch_assoc($result_users2))) 	{
	$user_names[$row_users2['id']] = $row_users2['user'];
	}
?>
	<script>
	function get_user_id() {
		var user_id = parseInt("<?php print $_SESSION['user_id'];?>");		
		return user_id;
		}				// end function get_user_id()
	
	var https = <?php print $ishttps;?>;
	var protocol = (https) ? "wss" : "ws";
	var hostURL = "<?php print $host;?>";
	var	hostPORT = "<?php print $port;?>";
	var	isLocal = <?php print $isLocal;?>;
	var checkConn = false;
	var socket;
	var users = <?php echo json_encode($users_arr);?>;
	var usernames = <?php echo json_encode($user_names);?>;
	var host = protocol + "://" + hostURL + ":" + hostPORT;
	var broadcast_interval = null;
	var checkconn_interval = null;
	var guest = <?php print $guest;?>;
	
	function start_connection() {
		if (window.checkconn_interval!=null || guest) {return;}		//	Interval already set
		window.checkconn_interval = window.setInterval('theConnection()', 2000);
		}
		
	function Socket_startup() {
		if (window.checkconn_interval!=null || guest) {return;}		//	Interval already set
		window.checkconn_interval = window.setInterval('theConnection()', 2000);
		}			// end function Socket_startup()
	
	function sleep(milliseconds) {
		var start = new Date().getTime();
		for (var i = 0; i < 1e7; i++) {
			if ((new Date().getTime() - start) > milliseconds){
				break;
				}
			}
		}
		
	function do_heartbeat() {	//	Heartbeat to drive connected users information
		var userid = get_user_id();
		if(userid == 0) {return;}
		broadcast("I am " + userid, 599);
		broadcast("System asks how many users connected", 96);
		}			// end function do_heartbeat()
		
	function addZero(inVal) {
		if (inVal < 10) {
			inVal = "0" + inVal;
			}
		return inVal;
		}
		
	function broadcast_heartbeat() {	//	Timer for do_heartbeat()
		if (window.broadcast_interval!=null) {return;}
		window.broadcast_interval = window.setInterval('do_heartbeat()', 10000);
		}			// end function broadcast_heartbeat()
		
	function get_current_datetime() {
		var n = new Date();
		var hours = n.getHours();
		var mins = n.getMinutes();
		var seconds = n.getSeconds();
		return hours + ":" + mins + ":" + seconds;
		}
		
	function theConnection() {
		if(window.checkConn == true && window.socket) {	//	stop duplicate connections
			window.checkconn_interval = null;	//	stop timer if connection already established
			return;
			} else {
			if(!guest) {window.socket = new WebSocket(window.host);}
			}

		window.socket.onopen = function(){
			window.checkConn = true;
			if(!guest) {
				var n = new Date();
				var hours = n.getHours();
				var mins = n.getMinutes();
				var teststring = "Broadcast OK " + hours + ":" + mins;
				do_heartbeat();
				broadcast_heartbeat();
				$('broadcastWrapper').style.display = 'block';
				$('timeText').innerHTML = teststring;
				$('usercount').innerHTML = "1 user(s)";
				if(responder_id != 0) {$('help_but').style.display = "inline-block";}
				}
			}
		
		window.socket.onclose = function(){
			$('timeText').innerHTML = "";
			if($('help_but')) {$('help_but').style.display = "none";}
			}
			
		window.socket.onerror = function(error){
			writeto_log(5099, 0, 0, "Websocket connection error", 0, 0, 0);
			}

		window.socket.onmessage = function(event) {					// on incoming
			var ourArr = event.data.split("/");
			var the_message = ourArr[1];
			var temp = get_user_id();
			var msgType = (ourArr[2]) ? parseInt(ourArr[2]) : 1;
			var unit_id = users[ourArr[0]];
			var payload = ourArr[1];					// no, drop user_id segment before showing it
			switch(msgType) {
				 case 1:
					if (the_message && (ourArr[0] != temp))  {
						msgtype_1(payload, unit_id);
						}
					break;
				 case 99:
					msgtype_99(payload, unit_id);
					break;
				 case 199:
					msgtype_199(payload, unit_id);
					break;
				 case 21:
					msgtype_21(payload, unit_id);
					break;
				 case 22:
					msgtype_22(payload, unit_id);
					break;
				 case 23:
					msgtype_23(payload, unit_id);
					break;
				 case 24:
					msgtype_24(payload, unit_id);
					break;
				 case 25:
					msgtype_25(payload, unit_id);
					break;
				 case 26:
					msgtype_26(payload, unit_id);
					break;
				 case 27:
					msgtype_27(payload, unit_id);
					break;
				 case 28:
					msgtype_28(payload, unit_id);
					break;
				 case 29:
					msgtype_29(payload, unit_id);
					break;
				 case 40:
					break;
				 case 97:
					var theUsers = parseInt(payload);
					window.hasUsercount = theUsers;
					usercount(theUsers);
					break;
				 case 98:
					var connectedString = "";
					var part1 = ourArr[0];
					var part2 = payload;
					var part3 = ourArr[2];
					var connectedUsers = payload;
					var usersArr = payload.split(",");
					for(var i = 0; i < usersArr.length; i++) {
						connectedString += usernames[usersArr[i]] + "\r\n";
						}
					break;
				case 599:
					break;
				 default:
					msgtype_1(payload, unit_id);
				} 
			}				// end incoming			
		}
	
	if(responder_id == 0) {$('help_but').style.display = "none";};
	
	function usercount(message) {
		if($('usercount')) {$('usercount').innerHTML = " " + message + " user(s)";}
		}
		
	function msgtype_1(message, unit_id) {
		$('has_line').style.display = "inline-block";
		$('has_text').innerHTML = "<p>" + message + "</p>";
		writeto_log(5000, 0, 0, message, 0, 0, 0); 			
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_21(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_22(message, unit_id) {
		do_audio();		// invoke audio function in top
		}

	function msgtype_23(message, unit_id) {
		do_audio();		// invoke audio function in top
		}

	function msgtype_24(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_25(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_26(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_27(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_28(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_29(message, unit_id) {
		do_audio();		// invoke audio function in top
		}
		
	function msgtype_99(message, unit_id) {
		var theUser = get_user_id();
		var theResponder = users[theUser];
		if(unit_id != 0 && unit_id != theResponder) {
			do_respalert(unit_id);
			do_audio();		// invoke audio function in top	
			if(message) {
				writeto_log(5001, 0, 0, message, 0, 0, 0);
				}
			} else if(unit_id != 0 && unit_id == theResponder) {
			alert("Help request sent");
			} else {
			// Do Nothing
			}
		}
		
	function msgtype_199(message, unit_id) {
		do_audio();		// invoke audio function in top
		alert(message);
		}

	function broadcast(theMessage, theType) {
<?php
		$do_broadcast = get_variable('broadcast');
		if (intval ($do_broadcast) == 1) {							// possibly disabled
?>
			var type = (theType) ? theType : 1;
	    	var temp = get_user_id();
			var outStr = temp + "/" + theMessage + "/" + theType;
	    	socket.send(outStr);
<?php
			}		// end ($do_broadcast) == 1
?>		
	    }		// end function broadcast

	function do_audio()	{
		if (typeof(do_audible) == "function") {do_audible('Broadcast');}					// if in top
		}		// end function do_audio()

	function do_respalert(id) {
		var mapWidth = <?php print get_variable('map_width');?>+32;
		var mapHeight = <?php print get_variable('map_height');?>+200;
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
		var title = "Responder Assitance Request";
		var url = "unit_popup.php?id="+id;;
		newwindow=window.open(url, id, spec);
		if (isNull(newwindow)) {
			alert ("Responder alert screen requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}
	</script>
