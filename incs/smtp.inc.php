<?php
/*
1/10/11 addded batch send in order to hide other addees
*/

//
// function is called with a single message consisting of a subject and text and an array of addresses - 
//      whether cell or landline.  Whether to send with blind or visible addee's is determined here.
//		Transport is smtp or native php mail - depending on smtp setting
//		Example smtp settings; 	smtp.gmail.com/465/ssl/shoreas@gmail.com/&^%$#@
//								outgoing.verizon.net/587//ashore3/&^%$#@ - works at KA and local 
//														*** note empty security position! ***


function do_swift_mail ($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str) { // 7/5/10 - per Kurt Jack
	require_once 'lib/swift_required.php';
		if (!(empty($my_smtp_ary))) {									// SMTP?
			$transport = Swift_SmtpTransport::newInstance($my_smtp_ary[0] , $my_smtp_ary[1] , $my_smtp_ary[2])
				  ->setUsername($my_smtp_ary[3])
				  ->setPassword($my_smtp_ary[4])
				  ;				
				}
		else {															// php mail
			$transport = Swift_MailTransport::newInstance();			// Create the php mail Transport
			}
		
		$mailer = Swift_Mailer::newInstance($transport);					// Create the Mailer using your created Transport

		$message = Swift_Message::newInstance($my_subject_str)	
		  ->setFrom($my_from_ary[0])
		  ->setTo($my_to_ary)
		  ->addReplyTo(trim($my_replyto_str))
		  ->setBody($my_message_str)
		  ;	
		
//		if ((count($my_from_ary)>0) && (strtoupper(trim(@$my_from_ary[1]=="B")))){	  										// 1/10/11 - hide other addee's?
		if ((count($my_from_ary)>1) && (strtoupper(substr (trim(@$my_from_ary[1]), 0, 1 )=="B"))) {	  						// 1/10/11 - hide other addee's?
			$numSent = $mailer->batchSend($message);			// yes - batchSend hides them
			}
		else {
			$numSent = $mailer->send($message);					// no - conventional send
			}

	return $numSent;
	} 				// end function real_smtp


function do_native_mail ($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str) {
	$bccc = ((count($my_from_ary)>1) && (strtoupper(substr (trim($my_from_ary[1]), 0, 1 )=="B"))) ? "Bcc: " : "Cc: ";
	
	$headers="";
	for ($i=0; $i<count($my_to_ary); $i++) {
		$headers.= ($bccc . trim($my_to_ary[$i]) . PHP_EOL);
		}
	if (is_email($my_replyto_str)) {
		$headers .= "From: " . trim($my_from_ary[0]) . PHP_EOL .
		    "Reply-To: " . $my_replyto_str . PHP_EOL .
		    "X-Mailer: PHP/" . phpversion();
		
	    }
	$temp = mail ( trim($my_from_ary[0]),  $my_subject_str,  $my_message_str ,  $headers);	
	
	return ($temp)? (string) count($my_to_ary) : "0" ;
	} 				// end function do_native_mail

?>