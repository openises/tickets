<?php
$to      = 'shoreas@gmail.com';
$subject = 'test test test  ';
$message = 'hello';
$headers = "From: shoreas@gmail.com";
$headers .= "\r\nBcc: ashore3@verizon.net";
$headers .= "\r\nBcc: technology@kolshalomannapolis.org";
$headers .= "\r\nBcc: dee@saefern.org\r\n\r\n";
$headers .= "\r\nX-Mailer: PHP/" . phpversion();

mail($to, $subject, $message, $headers);
print "sent - sent - sent - sent - sent - sent - sent - sent ";
?>
