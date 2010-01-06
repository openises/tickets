<?php
/*
3/8/09 initial release - simply a way to connect an XHR call to the server-side function
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');

do_send ($_REQUEST['to_str'], $_REQUEST['subject_str'], $_REQUEST['text_str'] ) ;

/*
snap(basename(__FILE__), $_REQUEST['to_str']);
snap(basename(__FILE__), $_REQUEST['subject_str']);
snap(basename(__FILE__), $_REQUEST['text_str']);
*/
print "";
?>
