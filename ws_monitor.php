<?php
/*
05/11/16	New file - Websocket Messaging Monitor
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);

require_once('./incs/functions.inc.php');
$temp1  = get_variable('socketserver_url');
$temp2 = get_variable('socketserver_port');
$host = ($temp1 == "") ? "{$_SERVER['SERVER_NAME']}" : $temp1;
$port = ($temp2 == "") ? "1337" : $temp2;
$isLocal = ($host == "127.0.0.1") ? 1 : 0;
@session_start();
$user_id = (array_key_exists('user_id', $_SESSION)) ? $_SESSION['user_id'] : "";
$users_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) 	{
	$users_arr[$row_users['id']] = $row_users['responder_id'];
	}
	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `login` >= (NOW() - INTERVAL 6 HOUR)";
$result_users = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) 	{
	$from_arr1[$row_users['_from']] = $row_users['user'];
	}
	
$from_arr = array_unique($from_arr1);

?>
<!DOCTYPE html>
<html>
<head>
<title>Tickets Websocket Monitor</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
     <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<LINK REL=StyleSheet HREF="./css/stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>	<!-- 1/6/11 JSON call-->
	<script>
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
	var sk_inteval = null;
	var users = <?php echo json_encode($users_arr);?>;
	var users_IP = <?php echo json_encode($from_arr);?>;
	var checkConn = false
	var host = "ws://" + hostURL + ":" + hostPORT;
	var checkconn_interval = null;
	
	function Socket_startup() {
		if (window.checkconn_interval!=null) {return;}		//	Interval already set
		window.checkconn_interval = window.setInterval('theConnection()', 2000);
		}			// end function Socket_startup()
	
	function addZero(inVal) {
		if (inVal < 10) {
			inVal = "0" + inVal;
			}
		return inVal;
		}
		
	function theConnection() {
		if(window.checkConn == true || window.socket) {	//	stop duplicate connections
			window.checkconn_interval = null;	//	stop timer if connection already established
			return;
			} else {
			window.socket = new WebSocket(window.host);
			}
			
		window.socket.onopen = function(){
			window.checkConn = true;
			}
		
		window.socket.onclose = function(){
			window.checkConn = false;
			}
			
		window.socket.onmessage = function(event) {					// on incoming
 			var n = new Date();
			var theHours = addZero(n.getHours());
			var theMins = addZero(n.getMinutes());
			var theSeconds = addZero(n.getSeconds());
			var dateStamp = theHours + ":" + theMins + ":" + theSeconds;
			var ourArr = event.data.split("/");
			var the_message = ourArr[1];
			var msgType = (ourArr[2]) ? parseInt(ourArr[2]) : 1;
			var unit_id = parseInt(users[ourArr[0]]);
			var payload = ourArr[1];					// no, drop user_id segment before showing it
			switch(msgType) {
				case 1:
					var logText = "Broadcast Message: " + payload + " at " + dateStamp + "<BR />";
					$('messageLog').innerHTML += logText;
					$('messageLog').scrollTop = $('messageLog').scrollHeight;
					break;
				case 94:
					var logText = "Users connected at " + dateStamp + "<BR />";
					var theConnected = payload.split(" ");
					for (n = 0; n < theConnected.length; n++) {
						if(theConnected[n] != "") {
							var theIP = theConnected[n].split(":");
							var theUsername = (typeof users_IP[theIP[0]] != 'undefined') ? users_IP[theIP[0]] : "Unk";
							logText += "IP Address: " + theIP[0] + " connected, User: " + theUsername + ", using port: " + theIP[1] + "<BR />";
							}
						}
					logText += "<BR />";
					$('summary').innerHTML = logText;
					$('summary').scrollTop = $('summary').scrollHeight;
					break;
				case 97:
					var logText = "Usercount Heartbeat: " + payload + " at " + dateStamp + "<BR />";
					$('messageLog').innerHTML += logText;
					$('messageLog').scrollTop = $('messageLog').scrollHeight;
					break;
				case 299:
					var logText = "System Message: " + payload + " at " + dateStamp + "<BR />";
					$('messageLog').innerHTML += logText;
					$('messageLog').scrollTop = $('messageLog').scrollHeight;
					break;
				default:
					var logText = "System Message: " + payload + " at " + dateStamp + "<BR />";
					$('messageLog').innerHTML += logText;
					break;				
				} 
			}				// end incoming
		}
		
	</script>
</HEAD>
<BODY onLoad='Socket_startup();' style='background-color: #000000; color: #FFFFFF;'>
	<DIV id='summary' style='background-color: #707070; color: #FFFFFF; height: 100px; width: 100%; overflow-y: auto;'></DIV><BR />
	<DIV id='messageLog' style='background-color: #000000; color: #FFFFFF; height: 250px; width: 100%; overflow-y: auto;'></DIV>				
</BODY>
</HTML>