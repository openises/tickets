<?php
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');
require_once './lib/xpm/POP3.php';
require_once './lib/xpm/MIME.php';
error_reporting(E_ALL);				// 9/13/08
set_time_limit(0);
@session_start();
$the_result = "";
if (empty($_SESSION)) {
	header("Location: index.php");
	}
do_login(basename(__FILE__));

function get_the_emails($url, $user, $password, $port, $ssl="", $timeout=10 ) {	//	Called from AJAX file to get emails in background - AJAX file called by top.php
//	print $url . "," . $user . "," . $password . "," . $port . "," .  $ssl . "," . $timeout . "<BR />"; 
	$simple = get_msg_variable('email_svr_simple');
	if($simple == 1) {
		$c = POP3::connect($url, $user, $password);		//	no
		} else {
		$c = POP3::connect($url, $user, $password, $port, $ssl, $timeout);
		}
	// STAT
	if(!$c) {
		do_log($GLOBALS['LOG_EMAIL_CONNECT'], 0, 0, "Cannot connect to IC Email server: ");		
		}
	$s = POP3::pStat($c);
	// $i - total number of messages, $b - total bytes
	list($i, $b) = each($s);
	$x = intval($i);
	if ($i >= 1) { // if we have messages
		$the_message = array();
		for($z = 1; $z <= $x; $z++) {
			$the_message[$z]['id'] = $z;
			// RETR
			$r = POP3::pRetr($c, $z); // <- get the last mail (newest)
			$m = MIME::split_message($r);
			$split = MIME::split_mail($r, $headers, $body);	
			if($headers && $body) {
				$y = 0;
				foreach($headers AS $val) {
					if($val['name'] == "From") { $the_message[$z]['from'] = GetBetween($val['value'],'<','>'); $thename = explode("<", $val['value']); $fromname = $thename[0]; } 
					if($val['name'] == "To") { $the_message[$z]['to'] = $val['value']; } 
					if($val['name'] == "Subject") { $the_message[$z]['subject'] = $val['value']; } 
					if($val['name'] == "Date") { $the_message[$z]['date'] = $val['value']; } 
					$y++;
					}
				$the_message[$z]['text'] = addslashes(htmlentities($body[0]['content']));
				$from = $the_message[$z]['from'];
				$date = date_parse($the_message[$z]['date']);				
				$datepart = $date['year'] . "-" . $date['month'] . "-" . $date['day'];
				$timepart = $date['hour'] . ":" . $date['minute'] . ":" . $date['second'];
				$datestring = $datepart . " " . $timepart;	
				}
			}
			dump($the_message);
		// optional, you can delete this message from server
		//	POP3::pDele($c, $i);
		}
	}	

// expertmailer version	
if((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 3)) {
	$url = get_msg_variable('email_server');
	$port = intval(get_msg_variable('email_port'));
	$protocol = get_msg_variable('email_protocol');
	$addon = get_msg_variable('email_addon');
	$folder = get_msg_variable('email_folder');
	$user = get_msg_variable('email_userid'); 
	$password = get_msg_variable('email_password');
	$ssl = 'ssl';
	print get_the_emails("$url", "$user", "$password", $port, "$ssl", 100);
	}

print $the_result;	
?>	
