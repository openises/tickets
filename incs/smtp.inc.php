<?php
/*
1/10/11 addded batch send in order to hide other addees
*/

//
// function is called with a single message consisting of a subject and text and an array of addresses - 
//      whether cell or landline.  Whether to send with blind or visible addee's is determined here.
//		Transport is smtp or native php mail - depending on smtp setting
//		Example smtp settings; 	smtp.gmail.com/465/ssl/shoreas@gmail.com/&^%$#@
//								outgoing.verizon.net/587//ashore3/&^%$#@ - works at KA and local *** note empty security position! ***
//
	if(file_exists('./lib/phpmailer/PHPMailerAutoload.php')) {
		require './lib/phpmailer/PHPMailerAutoload.php';
		}
	if(file_exists('./lib/phpmailer/class.phpmailer.php')) {
		require './lib/phpmailer/class.phpmailer.php';
		}
	if(file_exists('./lib/phpmailer/class.smtp.php')) {
		require './lib/phpmailer/class.smtp.php';
		}
	if(file_exists('../lib/phpmailer/PHPMailerAutoload.php')) {
		require '../lib/phpmailer/PHPMailerAutoload.php';
		}
	if(file_exists('../lib/phpmailer/class.phpmailer.php')) {
		require '../lib/phpmailer/class.phpmailer.php';
		}
	if(file_exists('../lib/phpmailer/class.smtp.php')) {
		require '../lib/phpmailer/class.smtp.php';
		}

function callbackAction($result, $to, $cc, $bcc, $subject, $body) {
    echo "Message subject: \"$subject\"\n";
    foreach ($to as $address) {
        echo "Message to {$address[1]} <{$address[0]}>\n";
		}
    foreach ($cc as $address) {
        echo "Message CC to {$address[1]} <{$address[0]}>\n";
		}
    foreach ($bcc as $toaddress) {
        echo "Message BCC to {$toaddress[1]} <{$toaddress[0]}>\n";
		}
    if ($result) {
        echo "Message sent successfully\n";
		} else {
        echo "Message send failed\n";
		}
	}

function do_smtp_mail($my_smtp_ary, $my_to_ary, $my_subject_str, $my_message_str, $my_from_ary, $my_replyto_str) {
	// smtp array: 0 = server, 1 = port, 2 = security, 3 = user, 4 = password, 5 = email address.
	if (!(empty($my_smtp_ary))) {									// SMTP?
		$server = $my_smtp_ary[0];
		$port =  $my_smtp_ary[1];
		$useracct =  $my_smtp_ary[3];
		$pass =  $my_smtp_ary[4];
		$security =  $my_smtp_ary[2];
		$from = $my_from_ary[0];
		$do_bcc = (array_key_exists(1, $my_from_ary) && $my_from_ary[1] == "B") ? true : false;
		
		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
		try {
			//Server settings
			$mail->SMTPDebug = 0;									// Enable verbose debug output
			$mail->isSMTP();										// Set mailer to use SMTP
			$mail->Host = $server;									// Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               	// Enable SMTP authentication
			$mail->Username = $useracct;							// SMTP username
			$mail->Password = $pass;								// SMTP password
			$mail->SMTPSecure = $security;							// Enable TLS encryption, `ssl` also accepted
			$mail->Port = $port;                                    	// TCP port to connect to

			//Recipients
			$mail->setFrom($from, "Tickets CAD");
			$messageCount = 0;
			if(!$do_bcc) {
				foreach($my_to_ary as $val) {
					$temp = explode("@", $val);
					$toName = $temp[0];
					$toAddress = $val;
					$mail->addAddress($toAddress, $toName);     	// Add a recipient
					$messageCount++;
					}
				} else {
				foreach($my_to_ary as $val) {
					$temp = explode("@", $val);
					$toName = $temp[0];
					$toAddress = $val;
					$mail->addBCC($toAddress, $toName);
					$messageCount++;
					}					
				}
			
			$mail->addReplyTo(trim($my_replyto_str));

			//Content
			$mail->isHTML(true);                                  	// Set email format to HTML
			$mail->Subject = $my_subject_str;
			$mail->Body    = $my_message_str;
			$mail->AltBody = $my_message_str;
			
			// Other
		
			$mail->send();
			return $messageCount;
			} catch (Exception $e) {
			$messageCount = 0;
			}
		}
	} 				// end function do_smtp_mail
	
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