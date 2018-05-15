<?php
/*
5/21/2013 initial release - useage: inside the page <head> "require_once('./incs/socket2me.inc.php');"
5/27/2013 removed user_id prepend
6/3/2013 revised js source per AH email
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

require_once('functions.inc.php');
$temp1  = get_variable('socketserver_url');
$temp2 = get_variable('socketserver_port');
$host = (array_key_exists("SERVER_NAME", $_SERVER)) ? "{$_SERVER['SERVER_NAME']}" : $temp1;
$port = ($temp2 == "") ? "1337" : $temp2;
$isLocal = ($host == "127.0.0.1") ? 1 : 0;
@session_start();
$user_id = (array_key_exists('user_id', $_SESSION)) ? $_SESSION['user_id'] : 0;
$the_whom = (array_key_exists('user', $_SESSION))? $_SESSION['user']: NOT_STR;
$guest = (is_guest()) ? 1 : 0;
$users_arr = array();
$user_names = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) 	{
	$users_arr[$row_users['id']] = $row_users['responder_id'];
	}
	
$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users2 = stripslashes_deep(mysql_fetch_assoc($result_users2))) 	{
	$user_names[$row_users2['id']] = $row_users2['user'];
	}

?>
	<script>

<?php
	if ((intval( get_variable ('broadcast')==1)) &&  (intval(get_variable ('internet')==1))) { 		//
?>
		function has_check(inStr) {
			if (inStr.trim().length == 0) {
				alert("Value required - try again."); return;
				} else {
				var msg =  $("whom").innerHTML + " sent: " + inStr.trim(); // identify sender
				broadcast(msg, 1); 				// send it
				setTimeout(function(){
					document.has_form.has_text.value = "              Sent!";		// note spaces
					setTimeout(function(){
						document.has_form.has_text.value = "";
						// back to normal
						}, 3000);
					}, 1000);
				}		// end else{}
			}		// end function has_check()	
<?php
		}
?>

	function get_user_id() {									
		var user_id = $('user_id').innerHTML;
		return user_id;
		}				// end function get_user_id()
		
	function sleep(milliseconds) {
		var start = new Date().getTime();
		for (var i = 0; i < 1e7; i++) {
			if ((new Date().getTime() - start) > milliseconds){
				break;
				}
			}
		}
		

	var hostURL = "<?php print $host;?>";
	var	hostPORT = "<?php print $port;?>";
	var	isLocal = <?php print $isLocal;?>;
	var socket = false;
	var sk_interval = null;
	var users = <?php echo json_encode($users_arr);?>;
	var usernames = <?php echo json_encode($user_names);?>;
	var checkConn = false
	var host = "ws://" + hostURL + ":" + hostPORT;
	var broadcast_interval = null;
	var checkconn_interval = null;
	var guest = <?php print $guest;?>;
	
	function showhide_hasusers() {
		var el = $('has_messages');
		var xpos = (el.offsetLeft - el.scrollLeft + el.clientLeft) + 10;
		var ypos = (el.offsetTop - el.scrollTop + el.clientTop);
		var theWidth = el.clientWidth * .8;
		var theHeight = el.offsetHeight * .8;
		$('has_users').style.position = "absolute";
		$('has_users').style.left = xpos+'px';
		$('has_users').style.top = ypos+'px';
		$('has_users').style.width = theWidth+'px';
		$('has_users').style.height = theHeight+'px';		
		if($('has_users').style.display == 'none') {
			$('has_users').style.display = 'block';
			} else {
			$('has_users').style.display = 'none';
			}
		}
	
	function Socket_startup() {
		if (window.checkconn_interval!=null || guest) {return;}		//	Interval already set
		window.checkconn_interval = window.setInterval('theConnection()', 2000);
		}			// end function Socket_startup()
	
	function do_heartbeat() {	//	Heartbeat to drive connected users information
		var userid = get_user_id();
		if(userid == 0) {return;}
		broadcast("I am " + userid, 599);
		broadcast("System asks how many users connected", 96);
		}			// end function do_heartbeat()
		
	function addZero(i) {
		if (i < 10) {
			i = "0" + i;
			}
		return i;
		}

	function get_current_datetime() {
		var n = new Date();
		var hours = addZero(n.getHours());
		var mins = addZero(n.getMinutes());
		var seconds = addZero(n.getSeconds());
		return hours + ":" + mins + ":" + seconds;
		}
		
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
		
	function theConnection() {
		if(window.checkConn == true && window.socket) {	//	stop duplicate connections
			window.checkconn_interval = null;	//	stop timer if connection already established
			return;
			} else {
			if(!guest) {window.socket = new WebSocket(window.host);}
			}

		window.socket.onopen = function(){
			if(!guest) {
				window.checkConn = true;
				var n = new Date();
				var hours = n.getHours();
				var mins = n.getMinutes();
				var theTime = get_current_datetime();
				var teststring = "Broadcast OK " + theTime;
				broadcast_heartbeat();
				$('has_flag').innerHTML = teststring;
				$('has_send').style.display = "inline-block";
				$('has_cancel').style.display = "inline-block";
				$('has_text').style.display = "inline-block";
				}
			}
		
		window.socket.onclose = function(){
			window.checkConn = false;
			$('has_send').style.display = "none";
			$('has_cancel').style.display = "none";
			$('has_text').style.display = "none";
			}
			
		window.socket.onerror = function(error){
//			writeto_log(5099, 0, 0, "Websocket error Line 61 socket2me", 0, 0, 0);
			}
			
		window.socket.onmessage = function(event) {					// on incoming
			var ourArr = event.data.split("/");
			var the_message = ourArr[1];
			var temp = get_user_id();
			var msgType = (ourArr[2]) ? parseInt(ourArr[2]) : 1;
			var unit_id = parseInt(users[ourArr[0]]);
			var payload = ourArr[1];					// no, drop user_id segment before showing it
			switch(msgType) {
				 case 1:
					msgtype_1(payload, unit_id);
					break;
				 case 99:
					msgtype_99(payload, unit_id);
					break;
				 case 199:
					msgtype_199(payload, unit_id);
					break;
				 case 299:
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
					showOK();
					break;
				 case 40:
					var theUsers = parseInt(payload);
					usercount(theUsers);
					break;
				 case 94:
					break;
				 case 97:
					var theUsers = parseInt(payload);
					window.hasUsercount = theUsers;
					usercount(theUsers);
					break;
				 case 98:
					var connectedString = "<B>Connected Users</B><BR /><BR />";
					var part1 = ourArr[0];
					var part2 = payload;
					var part3 = ourArr[2];
					var connectedUsers = payload;
					var usersArr = payload.split(",");
					for(var i = 0; i < usersArr.length; i++) {
						connectedString += usernames[usersArr[i]] + "<BR />";
						}
					$('has_users').innerHTML = connectedString;
					break;
				 case 599:
					break;
				 default:
					msgtype_1(payload, unit_id);
				} 
			}				// end incoming
		}
		
	function usercount(message) {
		$('usercount').innerHTML = message + " user(s)";
		}
		
	function msgtype_1(message, unit_id) {
		var currenttext = $('has_messages').innerHTML;
		var theTime = get_current_datetime();
		$('has_messages').innerHTML = message + " - received: " + theTime + "<BR />" + currenttext;
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
		
	function msgtype_40(message, unit_id) {
		alert(message);
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
			if(theMessage == "close server") {
				alert("Closing Down Websocket Server");
				}
			if(theMessage == "restart server") {
				alert("Restarting Websocket Server");
				}				
			var type = (theType) ? theType : 1;
			if(theType == 1) {writeto_log(5000, 0, 0, theMessage, 0, 0, 0);}
	    	var temp = get_user_id();
			var outStr = temp + "/" + theMessage + "/" + theType;
			if(window.socket) {
				window.socket.send(outStr);
				}
<?php
			}		// end ($do_broadcast) == 1
?>		
	    }		// end function broadcast

	function do_audio()	{
		if (typeof(do_audible) == "function") {
			do_audible('incident');
			}
		}
		
	function do_respalert(id) {
		try  {map.closeInfoWindow()} catch(err){;}
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
