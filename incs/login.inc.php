<?php
/*
8/21/10 initial creation
8/27/10 dropped nm variant of fip, cleared out end-of-script junk
8/29/10 corrected 'unit' login to mobile.php
8/31/10 set_filenames($internet) if in 'maybe' mode
11/6/10 remove redundant <body... >
11/15/10 meta description added
11/18/10 defer="defer" added for IE	
12/2/10 list_type added - AH
3/15/11 Revised show hide session variables.
2/16/11 Add fac_flag_2 session variable to persist facilities listing sort order
3/15/11	Changes for show and hide and css colors
3/19/11 get_unit() added for unit login, $_SESSION['user_unit_id']
5/10/11 logo changed
7/3/11 key check corrected
3/1/12 Changed level['MEMBER'] to level['UNIT']
6/1/12 Hide Guest loging notice if guest account doesn't exist.
*/

function do_logout($return=FALSE){						/* logout - destroy session data */
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
	@session_destroy();						// 2/18/08
	
	if ($return) return;
	
	do_login('main.php', TRUE);				// wait for login
	}
// ==========================================================================
	function check_conn () {				// returns TRUE/FALSE
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

	function set_filenames($internet) {
		$normal = (($internet == 1) || (($internet == 3) && (check_conn ())));		// check_conn()  returns TRUE/FALSE = 8/31/10			
	
		$_SESSION['internet'] = $normal;                        
//		$_SESSION['fip'] =($normal)? "./incs/functions.inc.php":	"./incs/functions_nm.inc.php";                        
		$_SESSION['fip'] ="./incs/functions.inc.php";                        // 8/27/10
		$_SESSION['fmp'] = ($normal)? "./incs/functions_major.inc.php": "./incs/functions_major_nm.inc.php";                              
		$_SESSION['addfile'] = ($normal)? "add.php": "add.php";											
		$_SESSION['editfile'] = ($normal)? "edit.php":	"edit.php";										  
		$_SESSION['unitsfile'] = ($normal)? "units.php": "units_nm.php";								     
		$_SESSION['facilitiesfile'] = ($normal)?	"facilities.php": "facilities_nm.php";		                    
		$_SESSION['routesfile'] = ($normal)?	"routes.php": "routes_nm.php";						        
		$_SESSION['facroutesfile'] = ($normal)? "fac_routes.php": "fac_routes_nm.php";
		}

// ==========================================================================

function is_expired($id) {		// returns boolean
	global $now ;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$id} LIMIT 1;";	
	$result = mysql_query($query);
	$row = @stripslashes_deep(mysql_fetch_assoc($result));
	return ((is_resource($result)) && (mysql_affected_rows()==1) && ($row['expires'] > $now));
	}

function do_login($requested_page, $outinfo = FALSE, $hh = FALSE) {			// do login/ses sion code - returns array - 2/12/09, 3/8/09
	global $hide_dispatched, $hide_status_groups;
	@session_start();
	global $expiry, $istest;
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

	$the_sid = (isset($_SESSION['id']))? $_SESSION['id'] : null;
//																			7/3/11
	$warn = ((array_key_exists ('expires', $_SESSION)) && ($now > $_SESSION['expires']))? "Log-in has expired due to inactivity.  Please log in again." : "";
	
	$internet = get_variable("internet");				// 8/22/10
	$temp = implode(";",  $_SESSION);
	
	if ((array_key_exists ('user_id', $_SESSION)) && (is_expired($_SESSION['user_id']))) {

		$the_date = mysql_format_date($expiry) ;
		$sess_key = session_id();										// not expired
		$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `expires`= '{$the_date}' WHERE `sid` = '{$sess_key}' LIMIT 1";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$_SESSION['expires'] = $expiry;
		$warn = "";
		if($internet==3) {set_filenames($internet);}			// possible change to filenames based on connect status - 8/31/10
		}				// end if((!(empty($_SESSION)))  && ...)

	else { 				// not logged in; now either get form data or db check form entries 	
		if(array_key_exists('frm_passwd', $_POST)) {		// first, db check
																														// 6/25/10
			$categories = array();													// 3/15/11

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` <> 'NULL'";	// 3/15/11
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_disp = mysql_num_rows($result);	//
			if(($num_disp > 0) && ($hide_dispatched == 1)) { $category_butts[0] = "Deployed"; $i=1; } else { $i=0; }

			if($hide_status_groups == 1) {	// 3/15/11
				$query = "SELECT DISTINCT `group` FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
					$categories[$i] = $row['group'];
					$i++;
					}
				unset($result);
			} else {
				$categories[$i] = "Available";
				$i++;
				$categories[$i] = "Not Available";
				}

			$fac_categories = array();
			$i=0;
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `name` ASC";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$fac_categories[$i] = $row['name'];
				$i++;
				}
			unset($result);

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

				$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET 
					`sid` = '{$sid}', 
					`expires`= '{$the_date}', 
					`login` = '{$now}', 
					`_from`= '{$_SERVER['REMOTE_ADDR']}', 
					`browser` = '{$browser}'  
					WHERE `id` = {$row['id']} LIMIT 1";
					
				$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

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
				$_SESSION['sortorder'] = ($row['sortorder']==NULL)? "date" : $row['sortorder']; 
				$_SESSION['sort_desc'] = ($row['sort_desc']==NULL)? " DESC " : $row['sort_desc']; 
				$_SESSION['ticket_per_page'] = 0;
				$_SESSION['show_hide_unit'] =  "s";		// show/hide units
				$_SESSION['show_hide_unav'] = "s";		// show/hide unavailable units - 4/27/10
				$_SESSION['show_hide_fac']  = "h";		// show/hide facilities - 3/8/10
				$_SESSION['unit_flag_1'] = "";		// unit id where status or position change
				$_SESSION['unit_flag_2'] = "";		// usage tbd 4/7/10
				$_SESSION['tick_flag_1'] = "";		// usage tbd 4/7/10
				$_SESSION['tick_flag_2'] = "";		// usage tbd 4/7/10
				$_SESSION['fac_flag_2'] = 2;		// 2/16/11			
				$_SESSION['list_type'] = 0;		// 12/2/10			
				$_SESSION['show_hide_Deployed'] = "s";	// Show all deployed tickets 3/15/11
				$_SESSION['day_night'] = $_POST['frm_daynight'];	// 01/20/11 Set Day or Night Colors
				$_SESSION['hide_controls'] = "s";		// 3/15/11
				$_SESSION['incs_list'] = "s";		// 3/15/11
				$_SESSION['resp_list'] = "s";		// 3/15/11
				$_SESSION['facs_list'] = "s";		// 3/15/11
				$_SESSION['regions_boxes'] = "s";		// 6/10/11				
				$_SESSION['user_unit_id'] = $row['responder_id'];		//3/19/11
				$_SESSION['show_hide_upper'] = "Show Menu";		//6/10/11
				
				foreach($categories as $key => $value) {				// 3/15/11
					$sess_flag = "show_hide_" . $value;
					$_SESSION[$sess_flag] = "s";
					}

				foreach($fac_categories as $key => $value) {				// 3/15/11
					$fac_sess_flag = "show_hide_fac_" . $value;
					$_SESSION[$fac_sess_flag] = "h";
					}
				$temp = implode(";",  $_SESSION);

				set_filenames($internet);			// 8/31/10
	
				do_log($GLOBALS['LOG_SIGN_IN'],0,0,$row['id']);			// log it													
																		// 7/21/10 
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_RESERVED']} AND `_by` = {$_SESSION['user_id']};";
				$result = mysql_query($query);
	
				$to = "";
				$subject = "Tickets Login";
				$message = "From: " . gethostbyaddr($_SERVER['REMOTE_ADDR']) ."\nBrowser:" . $_SERVER['HTTP_USER_AGENT'];
				$message .= "\nBy: " . $_POST['frm_user'];
				$message .= "\nScreen: " . $_POST['scr_width'] . " x " .$_POST['scr_height'];
				$message .= "\nReferrer: " . $_POST['frm_referer'];
		
//				@mail  ($to, $subject, $message);				// 1/11/09
							
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', FALSE);
				header('Pragma: no-cache');
				
	
				$host  = $_SERVER['HTTP_HOST'];
				$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

				$unit_id = get_unit();				// 3/19/11
				$level = $row['level'];
				
				if($level == $GLOBALS['LEVEL_UNIT']) {	//	3/1/12
					$extra = 'mobile.php';
					} else if ($level == $GLOBALS['LEVEL_STATS'] ) {
					$extra = 'stats_scr.php?stats=stats';
					} else {
					$extra = 'main.php?log_in=1';
					}
//				$extra = (($row['level']== $GLOBALS['LEVEL_UNIT']) ||($unit_id))? 'mobile.php' : 'main.php?log_in=1';				// 8/29/10

				header("Location: http://$host$uri/$extra");								// to top of calling script
				exit;				
				
				}			// end if (mysql_affected_rows()==1)
			}			// end if((!empty($_POST))&&(check_for_rows(...)

//		if no form data or values fail
		@session_destroy();				// 4/29/10
		
?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<HTML xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - free open source computer-aided dispatch software (CAD)</TITLE>
		<META HTTP-EQUIV=="Description" CONTENT="free, open source, CAD, dispatch, emergency response, ARES Teams, RACES Teams, amateur radio " />
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
		<META HTTP-EQUIV="Expires" CONTENT="0">
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
		<META HTTP-EQUIV="Script-date" CONTENT="1/23/10">
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
		<STYLE type="text/css">
		input		{background-color:transparent;}		/* Benefit IE radio buttons */
	  	</STYLE>

		<SCRIPT defer="defer">	<!-- 11/18/10 -->
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
			if (this.window.name!="main") {self.close();}			// in a popup
			if(self.location.href==parent.location.href) {			// prevent frame jump
				self.location.href = 'index.php';
				};
			try {		// should always be true
				parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php echo NOT_STR;?>" ;
				parent.frames["upper"].document.getElementById("level").innerHTML  = "<?php echo NA_STR;?>" ;
				parent.frames["upper"].document.getElementById("script").innerHTML  = "login";
				}
			catch(e) {
				}
			document.login_form.scr_width.value=screen.width;			// 1/23/10
			document.login_form.scr_height.value=screen.height;
	//		document.login_form.frm_user.focus();
	//		parent.upper.hide_butts();				// 1/21/09
			}		// end function do onload () 
	
<?php
		if (get_variable('call_board')==2) {		// 7/7/09
?>
			try {											// 8/10/10
				parent.calls.location.href = 'board.php';
				}
			catch (e) {
				}
<?php
	//		print "\tparent.calls.location.href = 'board.php';\n";				// reload to show 'waiting' message 6/19/09
			}
		print "\tparent.upper.location.href = 'top.php';\n";					// reload and initialize top frame 6/19/09
?>
		window.setTimeout("document.forms[0].frm_user.focus()", 1000);
		</SCRIPT>
		</HEAD>
<?php
		print ($hh)? "\n\t<BODY onLoad = 'do_hh_onload()'>\n" : "\n\t<BODY onLoad = 'do_onload()'>\n";		// 2/24/09
?>	
		
<!--	<BODY onLoad = "do_onload()"> 11/6/10 -->
		<CENTER><BR />
<?php
		if(get_variable('_version') != '') print "<SPAN style='FONT-WEIGHT: bold; FONT-SIZE: 15px; COLOR: #000000;'>" . get_variable('login_banner')."</SPAN><BR /><BR />";
?>
		</FONT>
		
		<FORM METHOD="post" ACTION="<?php print $requested_page;?>" NAME="login_form"  onSubmit="return true;">
		<TABLE BORDER=0>
<?php
		if(array_key_exists('frm_passwd', $_POST)) {$warn = "Login failed. Pls enter correct values and try again.";}
		if(!(empty($warn))) { 
			print "<TR CLASS='odd'><TH COLSPAN='99'><FONT CLASS='warn'>
			{$warn}
			</FONT><BR /><BR /></TH></TR>";
			}
		$temp =  isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : "";
		$my_click = ($_SERVER["HTTP_HOST"] == "127.0.0.1")? " onClick = \"document.login_form.frm_user.value='admin';document.login_form.frm_passwd.value='admin';\"" : "" ;
//	print (array_key_exists ('frm_user', $_POST))? 		$_POST['frm_user'] . "/" : "";
//	print (array_key_exists ('frm_passwd', $_POST))? 	$_POST['frm_passwd']: "";

//	6/1/12
		$guest_exists = 0;
		$query_guest 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `user`='guest'";
		$result_guest = mysql_query($query_guest) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_num_rows($result_guest)==1) {
			$guest_exists = 1;
			}
// End of code to check for guest account existence
?>
		<TR CLASS='even'><TD ROWSPAN=6 VALIGN='middle' ALIGN='left' bgcolor=#EFEFEF><BR /><BR />&nbsp;&nbsp;<IMG BORDER=0 SRC='open_source_button.png' <?php print $my_click; ?>><BR /><BR />
		&nbsp;&nbsp;<img src="php.png" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD><TD CLASS="td_label"><?php print get_text("User"); ?>:</TD>
			<TD><INPUT TYPE="text" NAME="frm_user" MAXLENGTH="255" SIZE="30" onChange = "document.login_form.frm_user.value = document.login_form.frm_user.value.trim();" VALUE=""></TD></TR>
		<TR CLASS='odd'><TD CLASS="td_label"><?php print get_text("Password"); ?>: &nbsp;&nbsp;</TD>
			<TD><INPUT TYPE="password" NAME="frm_passwd" MAXLENGTH="255" SIZE="30" onChange = "document.login_form.frm_passwd.value = document.login_form.frm_passwd.value.trim();"  VALUE=""></TD></TR>
		<TR CLASS="even"><TD COLSPAN=2>&nbsp;&nbsp;</TD></TR>
			<TR CLASS='odd'><TD CLASS="td_label">Colors: &nbsp;&nbsp;</TD>
			<TD><INPUT TYPE="radio" NAME="frm_daynight" VALUE="Day" checked>Day&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="radio" NAME="frm_daynight" value="Night">Night</TD></TR>
		<TR CLASS="even"><TD COLSPAN=2>&nbsp;&nbsp;</TD></TR>
		<TR CLASS='even'><TD></TD><TD><INPUT TYPE="submit" VALUE="<?php print get_text("Log In"); ?>"></TD></TR>
<?php
		if($guest_exists == 1) {	//	6/1/12
?>
			<TR CLASS='even'><TD COLSPAN=3 ALIGN='center'><BR />&nbsp;&nbsp;&nbsp;&nbsp;Visitors may login as <B>guest</B> with password <B>guest</B>.&nbsp;&nbsp;&nbsp;&nbsp;</TD></TR>
<?php
		}
?>
		<TR CLASS='even'><TD COLSPAN=3>&nbsp;</TD></TR>
		<TR CLASS='even'><TD COLSPAN=3>&nbsp;</TD></TR>
	 	</TABLE>
		<INPUT TYPE='hidden' NAME = 'scr_width' VALUE=''>
		<INPUT TYPE='hidden' NAME = 'scr_height' VALUE=''>
		<INPUT TYPE='hidden' NAME = 'frm_referer' VALUE="<?php print $temp; ?>">
		</FORM><BR /><BR />
		<a href="http://www.ticketscad.org/"><SPAN CLASS='text_small'>Tickets CAD Project home</SPAN></a>
		</CENTER></HTML>
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