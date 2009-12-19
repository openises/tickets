<?php
/*
3/8/09 initial release - simply a way to connect an XHR call to the server-side function
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
snap(__LINE__, basename(__FILE__));

//dump($_GET);
//dump($_REQUEST);
//do_send ($to_str, $subject_str, $text_str ) ;

//	var postData = "to_str=" + the_to +"&subject_str=" + the_subj + "&text_str=" + the_msg; // the post string
//snap(basename(__FILE__) . __LINE__, $_REQUEST['to_str'] );
//snap(basename(__FILE__) . __LINE__, $_REQUEST['subject_str'] );
//snap(basename(__FILE__) . __LINE__, $_REQUEST['text_str']);

do_send ($_REQUEST['to_str'], $_REQUEST['subject_str'], $_REQUEST['text_str'] ) ;

snap($_REQUEST['to_str'], basename(__FILE__));
print "";
?>
