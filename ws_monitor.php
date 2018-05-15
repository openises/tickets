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
$users_arr = array();
$user_names = array();
@session_start();
session_write_close();
$user_id = (array_key_exists('user_id', $_SESSION)) ? $_SESSION['user_id'] : 0;

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

$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users2 = stripslashes_deep(mysql_fetch_assoc($result_users2))) 	{
	$user_names[$row_users2['id']] = $row_users2['user'];
	}

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
	var usernames = <?php echo json_encode($user_names);?>;
	var checkConn = false
	var host = "ws://" + hostURL + ":" + hostPORT;
	var checkconn_interval = null;
	
	function get_user_id() {
		if((window.opener) && (window.opener.parent.frames["upper"])) {						// in call board?
			user_id = window.opener.parent.frames["upper"].$("user_id").innerHTML;
			} else if($('user_id')) {	//	In top bar
			user_id = $('user_id').innerHTML;
			} else {
			user_id = (parent.frames["upper"])? parent.frames["upper"].$('user_id').innerHTML : $('user_id').innerHTML;	
			}		// end else
		return user_id;
		}				// end function get_user_id()

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
		
	function do_heartbeat() {	//	Heartbeat to drive connected users information
		var userid = get_user_id();
		if(userid == 0) {return;}
		broadcast("I am " + userid, 599);
		}			// end function do_heartbeat()
		
	function broadcast_heartbeat() {	//	Timer for do_heartbeat()
		if (window.broadcast_interval!=null) {return;}
		window.broadcast_interval = window.setInterval('do_heartbeat()', 10000);
		}			// end function broadcast_heartbeat()
		
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
			broadcast_heartbeat();
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
//					$('summary').innerHTML = logText;
//					$('summary').scrollTop = $('summary').scrollHeight;
					break;
				case 97:
//					var logText = "Usercount Heartbeat: " + payload + " at " + dateStamp + "<BR />";
//					$('messageLog').innerHTML += logText;
//					$('messageLog').scrollTop = $('messageLog').scrollHeight;
					break;
				 case 98:
					var connectedString = "";
					var part1 = ourArr[0];
					var part2 = payload;
					var part3 = ourArr[2];
					var connectedUsers = payload;
					var usersArr = payload.split(",");
					for(var i = 0; i < usersArr.length; i++) {
						connectedString += usernames[usersArr[i]] + "<BR />";
						}
					$('summary').innerHTML = "Connected Users<BR />" + connectedString;
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
		
	function broadcast(theMessage, theType) {
<?php
		$do_broadcast = get_variable('broadcast');
		if (intval ($do_broadcast) == 1) {							// possibly disabled
?>
			var user_id = get_user_id();
			if(theMessage == "close server") {
				alert("Closing Down Websocket Server");
				}
			if(theMessage == "restart server") {
				alert("Restarting Websocket Server");
				}				
			var type = (theType) ? theType : 1;
			if(theType == 1) {writeto_log(5000, 0, 0, theMessage, 0, 0, 0);}
	    	var temp = user_id;
			var outStr = temp + "/" + theMessage + "/" + theType;
			if(window.socket) {
				window.socket.send(outStr);
				}
<?php
			}		// end ($do_broadcast) == 1
?>		
	    }		// end function broadcast

		
	</script>
</HEAD>
<BODY onLoad='Socket_startup();' style='background-color: #000000; color: #FFFFFF;'>
	<DIV id='summary' style='background-color: #707070; color: #FFFFFF; height: 100px; width: 100%; overflow-y: auto;'></DIV><BR />
	<DIV id='messageLog' style='background-color: #000000; color: #FFFFFF; height: 250px; width: 100%; overflow-y: auto;'></DIV>				
</BODY>
</HTML>