<?php
/*
10/7/08	initial version of server-side mail
10/15/08 revised to pass addresses as pipe-delim'd string
*/
require_once('./incs/functions.inc.php');

//	$caption =  mail_it ($_POST['frm_to'], quote_smart($_POST['frm_text']), quote_smart($_POST['frm_ticket_id']) );	//  ($to_array, $text, $ticket_id)  10/6/08

//dump($_POST);

$caption =  mail_it ($_POST['frm_to'], $_POST['frm_text'], $_POST['frm_ticket_id'] );	//  ($to_str, $text, $ticket_id) - 10/15/08
//snap(basename( __FILE__) . __LINE__, $_POST['frm_ticket_id']);
?>	
