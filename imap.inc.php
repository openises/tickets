<?
/*
* IMAP include file, contains all email importing functions
* STILL EXPERIMENTAL - USE WITH CARE! you've been warned :)
*/
// supported protocols
$GLOBALS['IMAP_IMAP'] 		= 1;
$GLOBALS['IMAP_POP3'] 		= 2;
$GLOBALS['IMAP_IMAP_SSL'] 	= 3;
$GLOBALS['IMAP_POP3_SSL'] 	= 4;
/* decode mime format strings */
function imap_decode($text) {
	$elements=imap_mime_header_decode($text);
	for($i=0;$i<count($elements);$i++) 	{
    	return htmlspecialchars($elements[$i]->text);
		}
	}
/* get mime type */
function imap_get_mime_type(&$structure) {
	$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
	if($structure->subtype)
		return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
	return "TEXT/PLAIN";
	}
/* get part of body by mime type */
function imap_get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) {
	if(!$structure)
	$structure = imap_fetchstructure($stream, $msg_number);
	
	if($structure) {
		if($mime_type == imap_get_mime_type($structure)) {
			if(!$part_number)
 	 			$part_number = "1";
			
 			$text = imap_fetchbody($stream, $msg_number, $part_number);
		
			if($structure->encoding == 3)
 				return imap_base64($text);
 			else if($structure->encoding == 4)
 	 			return imap_qprint($text);
 			else
 	 			return $text;
			}
	
		if($structure->type == 1) { /* multipart */
 		
 			while(list($index, $sub_structure) = each($structure->parts))
 	 		{
  			if($part_number)
 				$prefix = $part_number . '.';
			
			$data = imap_get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
  			if($data)
  				return $data;
	  			}
			}
		}
	return false;
	}
/* connect to server and fetch an $mailbox object */
function imap_connect($server,$port,$folder,$username,$password,$type) {
	//determine protocol type and fix the server connect string
	switch($type) {
	
		case $GLOBALS['IMAP_IMAP']: 	$server_path = '{'.$server.':'.$port.'	}'.$folder; 			break;
		case $GLOBALS['IMAP_POP3']: 	$server_path = '{'.$server.':'.$port.'/pop3	}'.$folder; 		break;
		case $GLOBALS['IMAP_IMAP_SSL']:	$server_path = '{'.$server.':'.$port.'/imap/ssl	}'.$folder; 	break;
		case $GLOBALS['IMAP_POP3_SSL']: $server_path = '{'.$server.':'.$port.'/pop3/ssl	}'.$folder; 	break;
		default: 						$server_path = '{'.$server.':'.$port.'	}'.$folder; 			break;
		}
	
	return imap_open($server_path, $username, $password);
	}
/* return number of messages in current mailbox */
function imap_message_count($mailbox) {
	if ($header = imap_check($mailbox)) 
  		return $header->Nmsgs;
	else
		return 0;
	}
/* close server connection gracefully */
function imap_disconnect($mailbox) {
	return imap_close($mailbox);
	}
/* import IMAP messages from mailbox */
function imap_import($mailbox,$delete_msg=0) {
 	$num_messages = imap_message_count($mailbox);
	
	for($i=1; $num_messages >= $i; $i++) {
		$msg 				= imap_header($mailbox,$i);
		$subject 			= mysql_escape_string(imap_decode($msg->subject));
		$from 				= imap_decode($msg->fromaddress);
		$action				= mysql_escape_string(imap_get_part($mailbox, $i, "TEXT/PLAIN"));
		$action_html		= mysql_escape_string(imap_get_part($mailbox, $i, "TEXT/HTML"));
		//$action		= get_part($mbox, $i, "TEXT/HTML");
		
		//insert ticket
		print "<li> Importing email from '$from', subject: '".substr($subject,0,50)."', body contains <B>".strlen($action)."</B> characters<BR />";
		add_ticket($subject,$from,'','NOW()','NOW()',$GLOBALS[STATUS_OPEN],$GLOBALS[SEVERITY_NORMAL],$my_session[user_id]);
		//$query = "INSERT INTO $GLOBALS[mysql_prefix]ticket (affected,scope,owner,description,problemstart,problemend,status,date,severity) VALUES('$from','',$my_session[user_id],'$subject','2002-03-05 18:30:00','2002-03-05 18:30:00',$GLOBALS[STATUS_OPEN],NOW(),$GLOBALS[SEVERITY_NORMAL])";
		//mysql_query($query) or do_error("imap_import($delete_msg)::mysql_query()", 'mysql query failed', mysql_error());
		//insert action (i.e. the body of the message)
		//$action 	= strip_html($action); //fix formatting, custom tags etc.
		$ticket_id 	= mysql_insert_id();
		
		if ($action) { //is $action empty?		
     		$query 		= "INSERT INTO $GLOBALS[mysql_prefix]action (description,ticket_id,date,user,action_type) VALUES('$action','$ticket_id',NOW(),$my_session[user_id],$GLOBALS[ACTION_COMMENT])";
			mysql_query($query) or do_error("imap_import($delete_msg)::mysql_query()", 'mysql query failed', mysql_error());
			}
		
		if ($action_html) {
			$query 		= "INSERT INTO $GLOBALS[mysql_prefix]action (description,ticket_id,date,user,action_type) VALUES('$action_html','$ticket_id',NOW(),$my_session[user_id],$GLOBALS[ACTION_COMMENT])";
			mysql_query($query) or do_error("imap_import($delete_msg)::mysql_query()", 'mysql query failed', mysql_error());
			}	
		
		if ($delete_msg) imap_delete($mailbox,$i);
		}
	
	print "<li> fetched and inserted $num_messages emails into database<BR /><BR />";
	
	//get rid of deleted messages if deletetion is on
	if ($delete_msg) imap_expunge($mailbox);
	}
?>