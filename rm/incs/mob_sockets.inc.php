<?php
/*
*/
$temp1  = get_variable('socketserver_url');
$temp2 = get_variable('socketserver_port');
$host = ($temp1 == "") ? "{$_SERVER['SERVER_NAME']}" : $temp1;
$port = ($temp2 == "") ? "1337" : $temp2;
$isLocal = ($host == "127.0.0.1") ? 1 : 0;
$guest = (is_guest()) ? 1 : 0;
?>
	<script>
	function get_user_id() {
		var user_id = parseInt("<?php print $_SESSION['user_id'];?>");		
		return user_id;
		}				// end function get_user_id()
		

	var hostURL = "<?php print $host;?>";
	var	hostPORT = "<?php print $port;?>";
	var	isLocal = <?php print $isLocal;?>;
	var checkConn = false;
	var socket;
	var checkConn = false
	var host = "ws://" + hostURL + ":" + hostPORT;
	var broadcast_interval = null;
	var checkconn_interval = null;
	var users = <?php echo json_encode($users_arr);?>;
	var guest = <?php print $guest;?>;
	
	function start_connection() {
		if (window.checkconn_interval!=null || guest) {return;}		//	Interval already set
		window.checkconn_interval = window.setInterval('theConnection()', 2000);
		}
	
	function sleep(milliseconds) {
		var start = new Date().getTime();
		for (var i = 0; i < 1e7; i++) {
			if ((new Date().getTime() - start) > milliseconds){
				break;
				}
			}
		}
		
	function theConnection() {
		if(window.checkConn == true && window.socket) {	//	stop duplicate connections
			window.checkconn_interval = null;	//	stop timer if connection already established
			return;
			} else {
			if(!guest) {window.socket = new WebSocket(window.host);}
			}
		
		window.socket.onopen = function(){
			if(!guest) {
				var n = new Date();
				var hours = n.getHours();
				var mins = n.getMinutes();
				var teststring = "Broadcast OK " + hours + ":" + mins;
				$('broadcastWrapper').style.display = 'inline-block';
				$('timeText').innerHTML = teststring;
				$('usercount').innerHTML = "1 user(s)";
				if(responder_id != 0) {$('help_but').style.display = "block";}
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
					usercount(payload);
					break;
				 default:
					msgtype_1(payload, unit_id);
				} 
			}				// end incoming			
		}
	
	if(responder_id == 0) {$('help_but').style.display = "none";};
	
	function usercount(message) {
		$('usercount').innerHTML = message + " user(s)";
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
		if (typeof(do_audible) == "function") {do_audible();}					// if in top
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
