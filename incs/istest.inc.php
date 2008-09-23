<?php
error_reporting(E_ALL);
//$istest = TRUE;
$istest = FALSE;
//session_start();
//if ($istest) {  
//	show_variables();
//	}

	//==================================================================
	// A "Best Practice" query
	// Using mysql_real_escape_string() around each variable prevents SQL 
	// Injection. This example demonstrates the "best practice" method for 
	// querying a database, independent of the Magic Quotes setting. 
	//
	// Quote variable to make safe
	function quote_smart($value) {
		if (get_magic_quotes_gpc()) {		// Stripslashes
			$value = stripslashes($value);
			}
		if (!is_numeric($value)) {			// Quote if not a number or a numeric string
			$value = "'" . mysql_real_escape_string($value) . "'";
			}
		return $value;
		}
	// Usage example:
	//
	// Connect
	// $link = mysql_connect('mysql_host', 'mysql_user', 'mysql_password')
	//	OR die(mysql_error());
	//
	// Make a safe query
	// $sql = sprintf("SELECT * FROM users WHERE user=%s AND password=%s",
	//			  quote_smart($_POST['username']),
	//			  quote_smart($_POST['password']));
	//
	//mysql_query($sql);

	function quote_smart_deep($value) {		// recursive array-capable version of the above
	    $value = is_array($value) ? array_map('quote_smart_deep', $value) : quote_smart($value);
	    return $value;
		}

  //=====================================================================

  // display $_GET, $_POST, $_COOKIE, and $my_session variables for debugging
  function show_variables() {
	 print '<div id="debug" style="padding:10px;z-index:99;background-color:#fff;color:#000;">';
	 print '<h3 style="text-decoration: underline">DEBUG DUMP</h3>';
	 print '<b>Set "debug = false" before delivery!</b><pre>';

	 print '<b>GET variables:</b><br />';
	 foreach($_GET as $key => $val) {
		 print "$key: ";
		 if(is_array($_GET[$key]))
			 print_r($val);
		 else
			 print $val;
		 print '<br />';
	 	}
  
	 print '<br /><b>POST variables:</b><br />';
	 foreach($_POST as $key => $val) {
		 print "$key: ";
		 if(is_array($_POST[$key]))
			 print_r($val);
		 else
			 print $val;
		 print '<br />';
		 }

	 print '<br /><b>COOKIE variables:</b><br />';
	 foreach($_COOKIE as $key => $val) {
		 print "$key: ";
		 if(is_array($_COOKIE[$key]))
			 print_r($val);
		 else
			 print $val;
		 print '<br />';
	 	}

	 print '<br /><b>SESSION variables</b>:<br />';
	 foreach($my_session as $key => $val) {
		 print "$key: ";
		 if(is_array($my_session[$key]))
			 print_r($val);
		 else
			 print $val;
		 print '<br />';
	 	}

	 print '</pre></div>';
  	}


?>
