<?php
/*
1/10/11 addded batch send in order to hide other addees
*/

//
// function is called with a single message consisting of a subject and text and an array of addresses - 
//      whether cell or landline.  Whether to send with blind or visible addee's is determined here.
//		Transport is smtp or native php mail - depending on smtp setting
// 		Exanple smtp setting ; "outgoing.verizon.net/587/none/ashoreN/********/ashoreN@verizon.net";

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
		
		if ((count($my_from_ary)>0) && (strtoupper(trim(@$my_from_ary[1]=="B")))){	  										// 1/10/11 - hide other addee's?
			$numSent = $mailer->batchSend($message);			// yes - batchSend hides them
			}
		else {
			$numSent = $mailer->send($message);					// no - conventional send
			}
//		snap( __LINE__, $numSent);
	mail("shoreas@gmail.com", "php mail", "as");

	return $numSent;
	} 				// end function real_smtp

?>