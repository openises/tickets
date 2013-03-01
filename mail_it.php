<?php
/*
10/7/08	initial version of server-side mail
10/15/08 revised to pass addresses as pipe-delim'd string
3/7/09 added text_sel parameter
3/11/09 corrected call to mail_it()
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/

@session_start();
require_once('incs/functions.inc.php');		//7/28/10 - functions_mail.php
// snap(__LINE__);
//  ($to_str, $text, $ticket_id) - 10/15/08
dump($_POST);
$caption =  mail_it ($_POST['frm_to'], $_POST['frm_text'], $_POST['frm_ticket_id'], $_POST['text_sel'] );	//  ($to_str, $text, $ticket_id) - 10/15/08
?>	
