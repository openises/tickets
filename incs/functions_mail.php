<?php
/*
1/9/11 initial extractf from FIP
4/19/11 corrections re smtp array makeup
11/10/11 Changed 911 to get_text('911'), Revised Map to included locale settings.
*/
function mail_it ($to_str, $text, $ticket_id, $text_sel=1, $txt_only = FALSE) {				// 10/6/08, 10/15/08,  2/18/09, 3/7/09
	global $istest;

/*
Subject		A
Inciden		B  Title
Priorit		C  Priorit
Nature		D  Nature
Written		E  Written
Updated		F  As of
Reporte		G  By
Phone: 		H  Phone: 
Status:		I  Status:
Address		J  Location
Descrip		K  Descrip
Disposi		L  Disposi
Start/end	M
Map: " 		N  Map: " 
Actions		O
Patients	P
Host		Q
911 contact	R				// 6/26/10
*/

	switch ($text_sel) {		// 7/7/09
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
	if (empty($match_str)) {$match_str = implode ("", range("A", "R"));}		// empty get all

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
//	dump($t_row);
	$eol = "\n";
	
	$locale = get_variable('locale');

	$message="";
	$_end = (good_date_time($t_row['problemend']))?  "  End:" . $t_row['problemend'] : "" ;		// 
	
	for ($i = 0;$i< strlen($match_str); $i++) {
		if(!($match_str[$i]==" ")) {
			switch ($match_str[$i]) {
				case "A":
				    break;
				case "B":
					$message .= "Incident: " . $t_row['scope'] . $eol;
				    break;
				case "C":
					$message .= "Priority: " . get_severity($t_row['severity']) . $eol;
				    break;
				case "D":
					$message .= "Nature: " . get_type($t_row['in_types_id']) . $eol;
				    break;
				case "J":
					$str = "";
					$str .= (empty($t_row['street']))? 	""  : $t_row['street'] . " " ;
					$str .= (empty($t_row['city']))? 	""  : $t_row['city'] . " " ;
					$str .= (empty($t_row['state']))? 	""  : $t_row['state'];
					$message .= empty($str) ? "" : "Addr: " . $str . $eol;
				    break;
				case "K":
					$message .= (empty($t_row['description']))?  "": "Descr: ". wordwrap($t_row['description']).$eol;
				    break;
				case "G":
					$message .= "Reported by: " . $t_row['contact'] . $eol;
				    break;
				case "H":
					$message .= (empty($t_row['phone']))?  "": "Phone: " . format_phone ($t_row['phone']) . $eol;
					break;
				case "E":
					$message .= (empty($t_row['date']))? "":  "Written: " . format_date_time($t_row['date']) . $eol;
				    break;
				case "F":
					$message .= "Updated: " . format_date_time($t_row['updated']) . $eol;
				    break;
				case "I":
					$message .= "Status: ".get_status($t_row['status']).$eol;
				    break;
				case "L":
					$message .= (empty($t_row['comments']))? "": "Disp: ".wordwrap($t_row['comments']).$eol;
				    break;
				case "M":
					$message .= "Run Start: " . format_date_time($t_row['problemstart']). $_end .$eol;
				    break;
				case "N":
					if($locale == 0) {
						$usng = LLtoUSNG($t_row['lat'], $t_row['lng']);
						$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $usng . "\n";
						}
					if($locale == 1) {
						$osgb = LLtoOSGB($t_row['lat'], $t_row['lng']);
						$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $osgb . "\n";
						}	
					if($locale == 2) {
						$utm = LLtoUTM($t_row['lat'], $t_row['lng']);
						$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $utm . "\n";
						}							
				    break;
			
				case "P":															
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {
						$message .= "\nPatient:\n";
						while($pat_row = stripslashes_deep(mysql_fetch_array($result))){
							$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
							}
						}
					unset ($result);
				    break;
			
				case "O":
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$ticket_id'";		// 10/16/08
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_affected_rows()>0) {
						$message .= "\nActions:\n";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						while($act_row = stripslashes_deep(mysql_fetch_array($result))) {
							$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
							}
						}	
					unset ($result);
				    break;
			
				case "Q":
					$message .= "Tickets host: ".get_variable('host').$eol;
				    break;

				case "R":							// 6/26/10
					$message .= (empty($t_row['nine_one_one']))?  "": get_text('911') . ": ". wordwrap($t_row['nine_one_one']).$eol;	//	11/10/11
				    break;

				default:
				    $message = "Match string error:" . $match_str[$i]. " " . $match_str . $eol ;

				}		// end switch ()
			}		// end if(!($match_...))
		}		// end for ($i...)

	$message = str_replace("\n.", "\n..", $message);					// see manual re mail win platform peculiarities

	$subject = (strpos ($match_str, "A" ))? $subject = $text . $t_row['scope'] . " (#" .$t_row['id'] . ")": "";

	if ($txt_only) {
		return $subject . "\n" . $message;		// 2/16/09
		}
	else {
		do_send ($to_str,  $subject, $message);
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