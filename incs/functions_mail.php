<?php
/*
1/9/11 initial extractf from FIP
4/19/11 corrections re smtp array makeup
11/10/11 Changed 911 to get_text('911'), Revised Map to included locale settings.
*/
function mail_it ($to_str, $smsg_to_str, $text, $ticket_id, $text_sel=1, $txt_only = FALSE) {	// 10/6/08, 10/15/08,  2/18/09, 3/7/09, 10/23/12, 11/14/2012, 12/14/2012
	global $istest;
//	if (is_null($text_sel)) {$text_sel = 1;}			//

	switch ($text_sel) {		// 7/7/09
		case NULL:				// 11/15/2012
		case 1:
		   	$match_str = strtoupper(get_variable("msg_text_1"));				// note case
		   	break;
		case 2:
		   	$match_str = strtoupper(get_variable("msg_text_2"));
		   	break;
		case 3:
		   	$match_str = strtoupper(get_variable("msg_text_3"));
		   	break;
		}

	if (empty($match_str)) {$match_str = " " . implode ("", range("A", "V"));}		// empty get all - force non-zero hit
	snap(basename(__FILE__), __LINE__);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`=$ticket_id LIMIT 1";
	snap(__LINE__, $query );
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
	$the_scope = strlen(trim($t_row['scope']))>0? trim($t_row['scope']) : "[#{$ticket_id}]" ;	// possibly empty
	$eol = PHP_EOL;
	$locale = get_variable('locale');	

	$message="";
	$_end = (good_date_time($t_row['problemend']))?  "  End:" . $t_row['problemend'] : "" ;		// 
	
	for ($i = 0;$i< strlen($match_str); $i++) {
		if(!($match_str[$i]==" ")) {
			switch ($match_str[$i]) {
				case "A":
				    break;
				case "B":
					$gt = get_text("Incident");
					$message .= "{$gt}: {$the_scope}{$eol}";
				    break;
				case "C":
					$gt = get_text("Priority");
					$message .= "{$gt}: " . get_severity($t_row['severity']) . $eol;
				    break;
				case "D":
					$gt = get_text("Nature");
					$message .= "{$gt}: " . get_type($t_row['in_types_id']) . $eol;
				    break;
				case "J":
					$gt = get_text("Addr");
					$str = "";
					$str .= (empty($t_row['street']))? 	""  : $t_row['street'] . " " ;
					$str .= (empty($t_row['city']))? 	""  : $t_row['city'] . " " ;
					$str .= (empty($t_row['state']))? 	""  : $t_row['state'];
					$message .= empty($str) ? "" : "{$gt}: " . $str . $eol;
				    break;
				case "K":
					$gt = get_text("Description");
					$message .= (empty($t_row['description']))?  "": "{$gt}: ". wordwrap($t_row['description']).$eol;
				    break;
				case "G":
					$gt = get_text("Reported by");
					$message .= "{$gt}: " . $t_row['contact'] . $eol;
				    break;
				case "H":
					$gt = get_text("Phone");
					$message .= (empty($t_row['phone']))?  "": "{$gt}: " . format_phone ($t_row['phone']) . $eol;
					break;
				case "E":
					$gt = get_text("Written");
					$message .= (empty($t_row['date']))? "":  "{$gt}: " . format_date_2($t_row['date']) . $eol;
				    break;
				case "F":
					snap(__LINE__, $t_row['updated']);
					$gt = get_text("Updated");
					$message .= "{$gt}: " . format_date_2($t_row['updated']) . $eol;
				    break;
				case "I":
					$gt = get_text("Status");
					$message .= "{$gt}: ".get_status($t_row['status']).$eol;
				    break;
				case "L":
					$gt = get_text("Disposition");
					$message .= (empty($t_row['comments']))? "": "{$gt}: ".wordwrap($t_row['comments']).$eol;
				    break;
				case "M":
					snap(__LINE__, $t_row['problemstart']);
					$gt = get_text("Run Start");
					$message .= get_text("{$gt}") . ": " . format_date_2($t_row['problemstart']). $_end .$eol;
				    break;
				case "N":
					$gt = get_text("Position");
					if($locale == 0) {
						$usng = LLtoUSNG($t_row['lat'], $t_row['lng']);
						$message .= "{$gt}: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $usng . "\n";
						}
					if($locale == 1) {
						$osgb = LLtoOSGB($t_row['lat'], $t_row['lng']);
						$message .= "{$gt}: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $osgb . "\n";
						}	
					if($locale == 2) {
						$utm = LLtoUTM($t_row['lat'], $t_row['lng']);
						$message .= "{$gt}: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $utm . "\n";
						}							
				    break;
			
				case "P":															
					$gt = get_text("Patient");
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {
						$message .= "\n{$gt}:\n";
						while($pat_row = stripslashes_deep(mysql_fetch_array($result))){
							$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
							}
						}
					unset ($result);
				    break;
			
				case "O":
					$gt = get_text("Actions");
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$ticket_id'";		// 10/16/08
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_affected_rows()>0) {
						$message .= "\n{$gt}:\n";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						while($act_row = stripslashes_deep(mysql_fetch_array($result))) {
							$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
							}
						}	
					unset ($result);
				    break;
			
				case "Q":
					$gt = get_text("Tickets host");
					$message .= "{$gt}: ".get_variable('host').$eol;
				    break;

				case "R":							// 6/26/10
					$gt = get_text("911 Contacted");
					$message .= (empty($t_row['nine_one_one']))?  "": "{$gt}: " . wordwrap($t_row['nine_one_one']).$eol;	//	11/10/11
				    break;

				case "S":		// 6/20/12 - 12/14/2012
					$gt = get_text("Links");
					$protocol = explode("/", $_SERVER["SERVER_PROTOCOL"]);
					$uri = explode("/", $_SERVER["REQUEST_URI"]);
					unset ($uri[count($uri)-1]);
					$uri = join("/", $uri);					
					//$message .= "{$gt}: {$temp_arr[0]}://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}/main.php?id={$ticket_id}";
					$message .= "{$gt}: {$protocol[0]}//{$_SERVER["SERVER_ADDR"]}:{$_SERVER["SERVER_PORT"]}{$uri}?id={$ticket_id}";
					break;
				case "T":							// 6/20/12
					$gt = get_text("Facility");
					if ((intval($t_row['rec_facility'])>0) || (intval($t_row['facility'])>0)) {
						$the_facility = (intval($t_row['rec_facility'])>0)? intval($t_row['rec_facility']) : intval($t_row['facility']);					
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`={$the_facility} LIMIT 1";	
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
						if (mysql_num_rows ($result)>0) {
							$f_row = stripslashes_deep(mysql_fetch_array($result));
							$message .= "{$gt}: {$f_row['handle']}\n";
							$message .= "{$gt}: {$f_row['beds_info']}\n";
							}
						}
				    break;

				case "U":		// 11/13/2012
					$query_u = "SELECT  `handle` FROM `$GLOBALS[mysql_prefix]assigns` `a`
						LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`a`.`responder_id` = `r`.`id`)
						WHERE `a`.`ticket_id` = $ticket_id AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'
						ORDER BY `handle` ASC ";																// 5/25/09, 1/16/08
					$result_u = mysql_query($query_u) or do_error($query_u, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_num_rows($result_u)>0) {
						$gt = get_text("Units");
						$message .= "\n{$gt} (" . mysql_num_rows($result_u) . "):\n";
						while($u_row = stripslashes_deep(mysql_fetch_assoc($result_u))) {
							$message .= "{$u_row['handle']},";
							}
						$message .= $eol;		// 4/1/2013
						}	
					unset ($result_u);
					break;
					
				case "V":
					if (is_date($t_row['booked_date'])) {
						$gt = get_text("Scheduled For");
						$message .= get_text("{$gt}") . ": " . format_date_2($t_row['booked_date']). $_end .$eol;
						}
				    break;

				default:
//				    $message = "Match string error:" . $match_str[$i]. " " . $match_str . $eol ;
					@session_start();
					$err_str = "mail error: '{$match_str[$i]}' @ " .  __LINE__;		// 6/18/12
					if (!(array_key_exists ( $err_str, $_SESSION ))) {		// limit to once per session
						do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_str);
						$_SESSION[$err_str] = TRUE;		
						}
				}		// end switch ()
			}		// end if(!($match_...))
		}		// end for ($i...)

	$message = str_replace("\n.", "\n..", $message);					// see manual re mail win platform peculiarities

	$subject = (strpos ($match_str, "A" ))? "": "Incident: {$the_scope}";	// 11/14/2012 - 11/14/2012 - don't duplicate

	if ($txt_only) {
		return $subject . "\n" . $message;		// 2/16/09
		}
	else {
		$smsg_to_str = ($smsg_to_str == NULL) ? "" : $smsg_to_str;
		do_send ($to_str, $smsg_to_str, $subject, $message, $ticket_id, 0);	//	10/23/12
		}
	}				// end function mail_it ()
// ________________________________________________________

function do_send ($to_str, $subject_str, $text_str ) {					// 7/7/09
	global $istest;
    require_once('smtp.inc.php');     									// defer load until required - 8/2/10
	$sleep = 4;															// seconds delay between text messages

	$my_smtp_ary = explode ("/",  trim(get_variable('smtp_acct')));   

	if ((count($my_smtp_ary)>1) && (count($my_smtp_ary)!=6)) {
		 do_log($GLOBALS['LOG_ERROR'], 0, 0, "Invalid smtp account information: " . trim(get_variable('smtp_acct')));
		 return ;
		}

	if ((count($my_smtp_ary)==6) && (!(is_email(trim($my_smtp_ary[5]))))) {									// email format test
		 do_log($GLOBALS['LOG_ERROR'], 0, 0, "Invalid smtp account address: " . trim($my_smtp_ary[5]));
		 return ;
		}
	if (!(is_email(trim(get_variable('email_reply_to'))))) {					// email format test
		do_log($GLOBALS['LOG_ERROR'], 0, 0, "Invalid email reply-to: " . trim(get_variable('email_reply_to')));
		return ;		
		}


	function stripLabels($sText){
		$labels = array("Incident:", "Priority:", "Nature:", "Addr:", "Descr:", "Reported by:", "Phone:", "Written:", "Updated:", "Status:", "Disp:", "Run Start:", "Map:", "Patient:", "Actions:", "Tickets host:"); // 5/9/10
		for ($x = 0; $x < count($labels); $x++) {
			$sText = str_replace($labels[$x] , '', $sText);
			}
		return $sText;
		}

	$to_array = array_values(array_unique(explode ("|", ($to_str))));		// input is pipe-delimited string  - 10/17/08
	require_once("cell_addrs.inc.php");										// 10/22/08
	
	$ary_cell_addrs = $ary_ll_addrs = array();
	for ($i = 0; $i< count($to_array); $i++) {								// walk down the input address string/array
		$temp =  explode ( "@", $to_array[$i]);
//		if (in_array(trim(strtolower($temp[1])), $cell_addrs))  {				// cell addr?
		if (!(in_array(trim(strtolower($temp[1])), $cell_addrs)))  {				// cell addr?
			array_push ($ary_cell_addrs, $to_array[$i]);						// yes
			}
		else {																	// no, land line addr
			array_push ($ary_ll_addrs, $to_array[$i]);	
			}
		}				// end for ($i = ...)

	$caption="";
	$my_from_ary = explode("/", trim(get_variable('email_from')));				// note /B option
	$my_replyto_str = trim(get_variable('email_reply_to'));
	$count_cells = $count_ll = 0; 				// counters
	if (count($ary_ll_addrs)>0) {												// got landline addee's?
//								  ($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str)	
		if (count($my_smtp_ary)>1) {
			$count_ll = do_swift_mail ($my_smtp_ary, $ary_ll_addrs, $subject_str, $text_str, $my_from_ary, $my_replyto_str );		
			}
		else {
			$count_ll = do_native_mail ($my_smtp_ary, $ary_ll_addrs, $subject_str, $text_str, $my_from_ary, $my_replyto_str );		
			}		
		}

	if (count($ary_cell_addrs)>0) {		// got cell addee's?
		$lgth = 140;
		$ix = 0;
		$i = 1;
		$cell_text_str = stripLabels($text_str);								// strip labels 5/10/10
		while (substr($cell_text_str, $ix , $lgth )) {							// chunk to $lgth-length strings
			$subject_ex = $subject_str . "/part " . $i . "/";					// 10/21/08
//										 ($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str)	
		if (count($my_smtp_ary)>1) {
			$count_cells = do_swift_mail ($my_smtp_ary, $ary_cell_addrs, $subject_ex, substr ($cell_text_str, $ix , $lgth ), $my_from_ary, $my_replyto_str);	
			}
		else {
			$count_cells = do_native_mail ($my_smtp_ary, $ary_cell_addrs, $subject_ex, substr ($cell_text_str, $ix , $lgth ), $my_from_ary, $my_replyto_str);	
			}
			if($i>1) {sleep ($sleep);}								// 10/17/08
			$ix+=$lgth;
			$i++;
			}				// end while (substr($cell_text_...)
		}									// end if (count($ary_cell_addrs)>0)
	return (string) ($count_ll + $count_cells);
	}					// end function do send ()

function is_email($email){		   //  validate email, code courtesy of Jerrett Taylor - 10/8/08, 7/2/10
	if(!preg_match( "/^" .
	"[a-zA-Z0-9]+([_\\.-][a-zA-Z0-9]+)*" .		//user
	"@" .
	"([a-zA-Z0-9]+([\.-][a-zA-Z0-9]+)*)+" .   	//domain
	"\\.[a-zA-Z]{2,}" .							//sld, tld
	"$/", $email, $regs)) {
			return FALSE;
			}
		else {
			return TRUE;
			}
		}							  // end function is_email()
?>