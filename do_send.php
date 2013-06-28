<?php
/*
3/8/09 initial release - simply a way to connect an XHR call to the server-side function
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10
//dump($_GET);
//dump($_POST);
//do_send ($to_str, $subject_str, $text_str ) ;

//	var postData = "to_str=" + the_to +"&subject_str=" + the_subj + "&text_str=" + the_msg; // the post string
//snap(basename(__FILE__) . __LINE__, $_POST['to_str'] );
//snap(basename(__FILE__) . __LINE__, $_POST['subject_str'] );
//snap(basename(__FILE__) . __LINE__, $_POST['text_str']);

do_send ($_POST['to_str'], $_POST['subject_str'], $_POST['text_str'], 0, 0 ) ;

//snap(basename(__FILE__) . __LINE__, 0);
print "";
?>
