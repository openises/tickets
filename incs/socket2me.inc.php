<?php
/*
5/21/2013 initial release - useage: inside the page <head> "require_once('./incs/socket2me.inc.php');"
5/27/2013 removed user_id prepend
6/3/2013 revised js source per AH email
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

require_once('functions.inc.php');
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
@session_start();
$user_id = (array_key_exists('user_id', $_SESSION)) ? $_SESSION['user_id'] : "";
//snap (basename(__FILE__), $user_id);

?>
	<script src="./js/easyWebSocket.min.js"></script>	<!-- 6/3/2013 -->
	<script>
//	var user_id;				// js global

	function get_user_id() {									
		if ( (window.opener) && (window.opener.parent.frames["upper"] ) ) {						// in call board?
			user_id = window.opener.parent.frames["upper"].$("user_id").innerHTML;
			}
		else {
			user_id = (parent.frames["upper"])?
				parent.frames["upper"].$('user_id').innerHTML:
				$('user_id').innerHTML;	
			}		// end else				
		return user_id;
		}				// end function get_user_id()
	
	    var socket = new EasyWebSocket('ws://<?php echo "{$host}{$uri}"?>/');		// instantiate
	    
	    socket.onmessage = function(event) {					// on incoming
	    	var ourArr = event.data.split("/");
	    	var temp = get_user_id();
	    	if (ourArr[0] != temp ) {							// is this mine?
	    		var payload = ourArr.slice(1);					// no, drop user_id segment before showing it
	    		payload = payload.join ("/");					// array back to string

				if ( (window.opener) && (window.opener.parent.frames["upper"] ) ) 			// in call board?
					{ window.opener.parent.frames["upper"].show_has_message(payload); }	// call the function() there
				else {
					if ( parent.frames["upper"])	{ parent.frames["upper"].show_has_message(payload); }						
					else						{ show_has_message(payload); }
					}		// end else		

				do_audio();										// invoke audio function in top
				}				// end mine?
			}				// end incoming

	    function broadcast(theMessage ) {
<?php
	$do_broadcast = get_variable('broadcast');
	if (intval ($do_broadcast) == 1) {							// possibly disabled
?>
	    	var temp = get_user_id();
			var outStr = temp + "/" + theMessage;
	    	socket.send(outStr);
<?php
		}		// end ($do_broadcast) == 1
?>		
	    	}		// end function broadcast

		function do_audio()	{
			if (typeof(do_audible) == "function") {do_audible();}					// if in top
			else if ( (window.opener) && ( window.opener.parent.frames["upper"] ) )
			  	{ window.opener.parent.frames["upper"].do_audible(); }				// if in lower frame
			else	{ parent.frames["upper"].do_audible();	}						// if in board 
			}		// end function do_audio()
	</script>
