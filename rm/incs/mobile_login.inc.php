<?php
/*
*/
function do_mobile_logout($return=FALSE){						/* logout - destroy session data */
	global $hide_dispatched, $hide_status_groups;
	@session_start();
 	$_SESSION['expires'] = 0;							
	if (array_key_exists ('user_id', $_SESSION)) {			// 7/27/10 - 8/10/10
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_RESERVED']} AND `_by` = {$_SESSION['user_id']};";	//8/10/10
		$result = mysql_query($query);
		}	
	$sid = session_id();
												// 1/8/10
	$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET 
		`sid` = NULL, 
		`expires` = NULL 
		WHERE `$GLOBALS[mysql_prefix]user`.`sid` = '{$sid}' LIMIT 1 ;";	 // 8/10/10
	$result = mysql_query($query);				// toss any error

	$the_id = array_key_exists ('user_id', $_SESSION)? $_SESSION['user_id'] : 0;	// possibly already logged out
	do_log($GLOBALS['LOG_SIGN_OUT'], 0, 0, $the_id);								// log this logout	

	if (isset($_COOKIE[session_name()])) { setcookie(session_name(), '', time()-42000, '/'); }		// 8/25/10
	unset ($sid);
	$_SESSION = array();
	@session_destroy();
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	header('Pragma: no-cache');
	if((get_variable('def_lat') == 0) && (get_variable('def_lng') == 0)) {
		$nocenter = true;
		}

	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'index.php';
	header("Location: http://$host$uri/$extra");
	if ($return) return;	
	exit;		
	}
	
function check_conn_mob () {				// returns TRUE/FALSE
	$url = "http://maps.google.com/";
	$response="";
	$parts=parse_url($url);
	if(!$parts) return false; /* the URL was seriously wrong */
	
	if (function_exists("curl_init")) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);				// 8/11/10
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
	
		if($parts['scheme']=='https'){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
	
		$response = curl_exec($ch);
		curl_close($ch);
		if(preg_match('/HTTP\/1\.\d+\s+(\d+)/', $response, $matches)){
			$code=intval($matches[1]);
		} else {
			$code=0;
		}
	
		if(($code>=200) && ($code<400)) {
			return TRUE;
		} else {
			return FALSE;
		}		
	} else {				// not CURL
		if ($fp = @fopen($url, "r")) {
			while (!feof($fp) && (strlen($response)<9000)) $response .= fgets($fp, 128);
			fclose($fp);
			return TRUE;
			}		
		else {
			return FALSE;
			}
		}
		
	}	// end function check_conn ()
	
function set_filenames_mob($internet) {
	$internet_good = (($internet == 1) || (($internet == 3) && (check_conn()))) ? true: false;		// check_conn()  returns TRUE/FALSE = 8/31/10
	if($internet_good) {	//	10/29/13
		$normal = true;
		} else {
		$normal = false;
		}
	$_SESSION['internet'] = $normal;   
	$_SESSION['good_internet'] = $internet_good;
	$_SESSION['fip'] ="./incs/functions.inc.php";                        // 8/27/10
	$_SESSION['fmp'] = ($normal)? "./incs/functions_major.inc.php": "./incs/functions_major_nm.inc.php";                              
	$_SESSION['addfile'] = ($normal)? "add.php": "add.php";											
	$_SESSION['editfile'] = ($normal)? "edit.php":	"edit.php";										  
	$_SESSION['unitsfile'] = ($normal)? "units.php": "units_nm.php";								     
	$_SESSION['facilitiesfile'] = ($normal)?	"facilities.php": "facilities_nm.php";		                    
	$_SESSION['routesfile'] = ($normal)?	"routes.php": "routes_nm.php";						        
	$_SESSION['facroutesfile'] = ($normal)? "fac_routes.php": "fac_routes_nm.php";
	$_SESSION['warnlocationsfile'] = ($normal)?	"warn_locations.php": "warn_locations_nm.php";			//	8/9/13
	}

function mobile_is_expired($id) {		// returns boolean
	global $now ;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$id} LIMIT 1;";	
	$result = mysql_query($query);
	$row = @stripslashes_deep(mysql_fetch_assoc($result));
	return ((is_resource($result)) && (mysql_affected_rows()==1) && ($row['expires'] > $now));
	}
	
function do_mobile_login($requested_page, $outinfo = FALSE, $hh = FALSE) {			// do login/ses sion code - returns array - 2/12/09, 3/8/09
	global $expiry, $istest;
	$nocenter = FALSE;
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	@session_start();
	$the_sid = (isset($_SESSION['id']))? $_SESSION['id'] : null;
	$internet = get_variable("internet");				// 8/22/10
	$warn = ((!(empty($_SESSION)))  && ($now > $_SESSION['expires']))? "Log-in has expired due to inactivity.  Please log in again." : "";
	if((!(empty($_SESSION)))  && ($now < $_SESSION['expires']))  {		// expired?

		$the_date = mysql_format_date($expiry) ;
		$sess_key = session_id();										// not expired
		$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `expires`= '{$the_date}' WHERE `sid` = '{$sess_key}' LIMIT 1";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$_SESSION['expires'] = $expiry;
		$warn = "";
		if($internet==3) {set_filenames_mob($internet);}			// possible change to filenames based on connect status - 8/31/10
		}				// end if((!(empty($_SESSION)))  && ...)

// not logged in; now either get form data or db check form entries 		
	else { if(array_key_exists('frm_passwd', $_POST)) {		// first, db check
			$temp = $GLOBALS['LEVEL_USER'];
			$tmp = $_POST['encoding'];
			$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` 
				WHERE `user`=" . quote_smart($_POST['frm_user']). " 	 
				AND (`passwd`=PASSWORD(" . quote_smart($_POST['frm_passwd']) . ") 
				OR `passwd`=MD5(" . quote_smart(strtolower($_POST['frm_passwd'])) . " ))  
				LIMIT 1";
			$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			
			if (mysql_affected_rows()==1) {
				$row = stripslashes_deep(mysql_fetch_assoc($result));

				if ($row['sortorder'] == NULL) $row['sortorder'] = "date";
				$dir = ($row['sort_desc']) ? " DESC " : "";
	
				$sid = session_id();							// 1/8/10
				$browser = checkBrowser(FALSE);
				$the_date = mysql_format_date($expiry) ;				
				$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `sid` = '{$sid}', `expires`= '{$the_date}', `login` = '{$now}', `_from`= '{$_SERVER['REMOTE_ADDR']}', `browser` = '{$browser}'  WHERE `id` = {$row['id']} LIMIT 1";
				$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$_SESSION['noautoforward'] = FALSE;	//	1/30/14
				$_SESSION['id'] = 			$sid;
				$_SESSION['expires'] = 		time();
				$_SESSION['user_id'] = 		$row['id'];
				$_SESSION['user'] = 		$row['user'];				
				$_SESSION['level'] = 		$row['level']; 
				$_SESSION['login_at'] = 	$now; 
				$_SESSION['scr_height'] = 	$_POST['scr_height'];		
				$_SESSION['scr_width'] = 	$_POST['scr_width'];		// monitor dimensions this user
				$_SESSION['allow_dirs'] = 	TRUE;						// allow directions
				$_SESSION['show_closed'] = 	TRUE;						// show closed dispatched
				$_SESSION['sortorder'] = "date"; 
				$_SESSION['sort_desc'] = " DESC "; 
				$_SESSION['ticket_per_page'] = 0;
				$_SESSION['show_hide_unit'] =  "s";		// show/hide units
				$_SESSION['show_hide_unav'] = "s";		// show/hide unavailable units
				$_SESSION['show_hide_fac']  = "h";		// show/hide facilities
				$_SESSION['unit_flag_1'] = "";		// unit id where status or position change
				$_SESSION['unit_flag_2'] = "";
				$_SESSION['tick_flag_1'] = "";
				$_SESSION['tick_flag_2'] = "";
				$_SESSION['fac_flag_2'] = 2;			
				$_SESSION['list_type'] = 0;	
				$_SESSION['show_hide_Deployed'] = "s";	// Show all deployed tickets
				$_SESSION['day_night'] = "Day";
				$_SESSION['maps_sh'] = "Show";
				$_SESSION['hide_controls'] = "s";
				$_SESSION['incs_list'] = "s";
				$_SESSION['resp_list'] = "s";
				$_SESSION['facs_list'] = "s";
				$_SESSION['regions_boxes'] = "s";			
				$_SESSION['user_unit_id'] = $row['responder_id'];
				$_SESSION['show_hide_upper'] = "Show Menu";
				
				set_filenames_mob($internet);			// 8/31/10
				do_log($GLOBALS['LOG_SIGN_IN'],0,0,"{$browser}");		// log it - 12/1/2012											
																		
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_RESERVED']} AND `_by` = {$_SESSION['user_id']};";
				$result = mysql_query($query);
				
				$to = "";
				$subject = "Tickets Mobile Page Login";
				$message = "From: " . gethostbyaddr($_SERVER['REMOTE_ADDR']) ."\nBrowser:" . $_SERVER['HTTP_USER_AGENT'];
				$message .= "\nBy: " . $_POST['frm_user'];
				$message .= "\nScreen: " . $_POST['scr_width'] . " x " .$_POST['scr_height'];
				$message .= "\nReferrer: " . $_POST['frm_referer'];
	
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', FALSE);
				header('Pragma: no-cache');
				if((get_variable('def_lat') == 0) && (get_variable('def_lng') == 0)) {
					$nocenter = true;
					}

				$host  = $_SERVER['HTTP_HOST'];
				$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
				$extra = "index.php";
				$url = "http://" . $host . $uri . "/" . $extra;
				echo '<meta http-equiv="refresh" content="', 0, ';URL=', $url, '">';
				exit;				
				}
			}			// end if((!empty($_POST))&&(check_for_rows(...)

//			if no form data or values fail

?>
		<!DOCTYPE xhtml PUBLIC "-//W3C//DTD XHTML 4.01//EN">
		<html>
		<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<title>Tickets Login Screen</title>
		<LINK REL=StyleSheet HREF="../css/stylesheet.php?version=<?php print time();?>" TYPE="text/css">			
		<style type="text/css">
			#outer { width: 100%; height: 100%; }
			*, html { margin:0; padding:0 }
			div#map_canvas { width:100%; height:85%; z-index: 9999999;}
			div#map_outer { width:100%; height:85%; z-index: 9999999;}	
			div#screen_buttons { width:100%; height:10%; position: fixed; bottom: 0%; z_index: 99999999;}	
			div#regions { width:100%; z_index: 99999998;}		
			div#info { width:100%; overflow:hidden; text-align: center; top:0; left:0; }
			.screen { z-index: 5; width:100%; height: auto; background-color: #CECECE;}			
			.screen_but_hover { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 2px; border-STYLE: inset; border-color: #FFFFFF;
						 padding: 4px; text-decoration: none; background-color: #DEE3E7; font-weight: bolder; text-align: center; width: 15%;}
			.screen_but_plain { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color: #000000;  border-width: 2px; border-STYLE: outset; border-color: #FFFFFF;
						 padding: 4px; text-decoration: none; background-color: #EFEFEF; font-weight: bolder; text-align: center; width: 15%; }				  
			.lightBox { filter:alpha(opacity=60); -moz-opacity:0.6; -khtml-opacity: 0.6; opacity: 0.6; background-color:white; padding:2px; }
			.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 2px; border-STYLE: inset; border-color: #FFFFFF;
						  padding: 4px 0.5em;text-decoration: none;float: none; background-color: #DEE3E7;font-weight: bolder; width: 100px; text-align: center;}
			.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color: #000000;  border-width: 2px; border-STYLE: outset; border-color: #FFFFFF;
						  padding: 4px 0.5em;text-decoration: none; float: none; background-color: #EFEFEF;font-weight: bolder; width: 100px; text-align: center;}
			INPUT { font-size: 1em; border: 2px inset #EFEFEF;}
		</style>		
		<SCRIPT SRC = ".././js/md5.js" ></SCRIPT>		
		<SCRIPT SRC = "../..js/sha1.js" ></SCRIPT>			
		<SCRIPT>
		String.prototype.trim = function () {
			return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
			};
			
		function getBrowserWidth(){
			var val="";
		    if (window.innerWidth){
		        var val= window.innerWidth;}
		    else if (document.documentElement && document.documentElement.clientWidth != 0){
		        var val= document.documentElement.clientWidth;    }
		    else if (window.screen.width && window.screen.width != 0){
		        var val= window.screen.width;    }
		    else if (document.body){var val= document.body.clientWidth;}
		        return(isNaN(val))? 1024: val;
			}
		function getBrowserHeight(){
			var val="";
		    if (window.innerHeight){
		        var val= window.innerHeight;}
		    else if (document.documentElement && document.documentElement.clientHeight != 0){
		        var val= document.documentElement.clientHeight;    }
		    else if (window.screen.height && window.screen.height != 0){
		        var val= window.screen.height;    }
		    else if (document.body){var val= document.body.clientHeight;}
		        return(isNaN(val))? 740: val;
			}

		function Set_Cookie( name, value, expires, path, domain, secure ) {
			var today = new Date();	// set time in milliseconds
			today.setTime( today.getTime() );
			if ( expires )	{
				expires = expires * 1000 * 60 ;
				}
			var expires_date = new Date( today.getTime() + (expires) );	
			document.cookie = name + "=" +escape( value ) +
				( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + //expires.toGMTString()
				( ( path ) ? ";path=" + path : "" ) + 
				( ( domain ) ? ";domain=" + domain : "" ) +
				( ( secure ) ? ";secure" : "" );
			}
			// if the expires variable is set, make the correct expires time, the
			// current script below will set it for x number of days, to make it
			// for hours, delete * 24, for minutes, delete * 60 * 24
			// alert('expires ' + expires_date.toGMTString());// this is for testing purposes only
			// alert( 'today ' + today.toGMTString() );// this is for testing purpose only
			
			function Get_Cookie( check_name ) {
				var a_all_cookies = document.cookie.split( ';' ); 	// first we'll split this cookie up into name/value pairs
				var a_temp_cookie = '';							  	// note: document.cookie only returns name=value, not the other components
				var cookie_name = '';
				var cookie_value = '';
				var b_cookie_found = false; // set boolean t/f default f
				var i = '';		
				for ( i = 0; i < a_all_cookies.length; i++ ) {
					a_temp_cookie = a_all_cookies[i].split( '=' );					// plit each name=value pair
					cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');		// and trim left/right whitespace 	
					if ( cookie_name == check_name ){								// if the extracted name matches passed check_name			
						b_cookie_found = true;			
						if ( a_temp_cookie.length > 1 ){	// we need to handle case where cookie has no value but exists (no = sign, that is):				
							cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
							}				
						return cookie_value;// note that in cases where cookie is initialized but no value, null is returned
						break;
						}
					a_temp_cookie = null;
					cookie_name = '';
					}
				if ( !b_cookie_found ) {
					return null;
					}
				}		// end function Get_Cookie(
		
		function do_hh_onload () {				// 2/24/09
			document.login_form.scr_width.value=getBrowserWidth();
			document.login_form.scr_height.value=getBrowserHeight();
			document.login_form.frm_user.focus();
			}		// end function 


		function do_onload () {
			document.login_form.scr_width.value=screen.width;			// 1/23/10
			document.login_form.scr_height.value=screen.height;
			document.login_form.frm_user.focus();

			}		// end function do_onload () 

		function validate(the_form) {
			the_form.frm_user.value = the_form.frm_user.value.trim().toLowerCase();
			the_form.encoding.value = hex_md5(the_form.frm_passwd.value);
			the_form.frm_passwd.value="";				// DO MOT SEND!
			the_form.submit();
			}	
		
		function do_hover (the_id) {
			CngClass(the_id, 'hover');
			return true;
			}

		function do_plain (the_id) {
			CngClass(the_id, 'plain');
			return true;
			}

		function do_sb_hover (the_id) {
			CngClass(the_id, 'screen_but_hover');
			return true;
			}

		function do_sb_plain (the_id) {
			CngClass(the_id, 'screen_but_plain');
			return true;
			}

		function CngClass(obj, the_class){
			$(obj).className=the_class;
			return true;
			}
			
		function $() {
			var elements = new Array();
			for (var i = 0; i < arguments.length; i++) {
				var element = arguments[i];
				if (typeof element == 'string')		element = document.getElementById(element);
				if (arguments.length == 1)			return element;
				elements.push(element);
				}
			return elements;
			}
			
		function do_tickets_main() {
			document.norm_form.submit();
			}
		
		window.setTimeout("document.forms[0].frm_user.focus()", 1000);
		</SCRIPT>
		</HEAD>
<?php
		print ($hh)? "\n\t<BODY onLoad = 'do_hh_onload()'>\n" : "\n\t<BODY onLoad = 'do_onload()'>\n";		// 2/24/09
?>	
		
		<CENTER>
		<div id='outer' class='screen'>		
			<div style='font-weight: bold; font-size: 1.2em; color: #000000; background-color: #FFFFCC;'><?php print get_variable('login_banner');?></FONT></div><BR />
			<FORM METHOD="post" ACTION="<?php print $requested_page;?>" NAME="login_form"  onSubmit="return true;">
			<div>
<?php
				if(array_key_exists('frm_passwd', $_POST)) {$warn = "Login failed. Pls enter correct values and try again.";}
				if(!(empty($warn))) { 
					print "<div><FONT CLASS='warn'>
					{$warn}
					</FONT><BR /><BR /></div>";
					}
				$temp =  isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : "";
				$my_click = ($_SERVER["HTTP_HOST"] == "127.0.0.1")? " onClick = \"document.login_form.frm_user.value='admin';document.login_form.frm_passwd.value='admin';\"" : "" ;
?>
				<div style='display: inline; font-size: 1.5em; font-weight: bold;'><?php print get_text("User"); ?><BR /><INPUT TYPE="text" NAME="frm_user" onChange = "document.login_form.frm_user.value = document.login_form.frm_user.value.trim();" VALUE=""></div><BR />
				<div style='display: inline; font-size: 1.5em; font-weight: bold;'><?php print get_text("Password"); ?><BR /><INPUT TYPE="password" NAME="frm_passwd" onChange = "document.login_form.frm_passwd.value = document.login_form.frm_passwd.value.trim();"  VALUE=""></div><BR /><BR /><BR />
				<INPUT id="sub_but2" TYPE="submit" VALUE="<?php print get_text("Log In"); ?>" class="plain" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" style="width: auto; font-size: 1.3em;"><BR /><BR />
				<INPUT id="tick_norm_but2" TYPE="button" VALUE="<?php print get_text("Tickets Normal Screen"); ?>" class="plain" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" style="width: auto; font-size: 1.3em;" onClick = 'do_tickets_main();' >
			</div>

			<INPUT TYPE='hidden' NAME = 'encoding' VALUE=''>		
			<INPUT TYPE='hidden' NAME = 'scr_width' VALUE=''>
			<INPUT TYPE='hidden' NAME = 'scr_height' VALUE=''>
			<INPUT TYPE='hidden' NAME = 'frm_referer' VALUE="<?php print $temp; ?>">
			</FORM><BR /><BR />
			<a href="http://www.ticketscad.org/"><SPAN CLASS='text_small'>Tickets CAD Project home</SPAN></a><BR /><BR />
			<div><IMG BORDER=0 SRC='../open_source_button.png' <?php print $my_click; ?>>&nbsp;&nbsp;<img src="../php.png" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>	
			<FORM NAME="norm_form" method='post' action="../index.php">
			<INPUT TYPE='hidden' NAME = 'noautoforward' VALUE=1>			
			</FORM>				
		</div>
		</CENTER>
		</HTML>
<?php
			exit();		// no return value
			}
	}		// end function do_login()
/*
$useragent=$_SERVER['HTTP_USER_AGENT'];
if(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
header('Location: http://detectmobilebrowser.com/mobile');
*/
?>