<?php
/*
get_messages.php - AJAX file gets email and SMS Gateway messages in background - called from top.php
10/23/12 - new file
*/
require_once('./incs/functions.inc.php');
require_once './lib/xpm/POP3.php';
require_once './lib/xpm/MIME.php';
error_reporting(E_ALL);				// 9/13/08
set_time_limit(0);

function get_theemails($url, $user, $password, $port, $ssl="", $timeout=30 ) {
	$no_whitelist = 1;
	$del_emails = 0;
	$counter = 0;
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));	
	$simple = get_msg_variable('email_svr_simple');
	if($simple == 1) {
		$c = POP3::connect($url, $user, $password);		//	no
		} else {
		$c = POP3::connect($url, $user, $password, $port, $ssl, $timeout);
		}
	// STAT
	if(!$c) {
		print "Can't connect to email server<BR />";		
		} else {
		print 'Successfully connected !<BR />';
		}
	$s = POP3::pStat($c);
	// $i - total number of messages, $b - total bytes
	list($i, $b) = each($s);
	$x = intval($i);
	if ($x >= 1) { // if we have messages
		print "You have " . $x . " new messages<BR /><BR /><BR />";
		print "<TABLE>";
		$the_message = array();
		$the_message2 = array();
		$style = "style='background-color: #707070; color: #FFFFFF;'";
		for($z = 1; $z <= $x; $z++) {
			print "<TR " . $style . ">";
			$the_message[$z]['id'] = $z;
			// RETR
			$r = POP3::pRetr($c, $z); // <- get the last mail (newest)
			$m = MIME::split_message($r);
			$split = MIME::split_mail($r, $headers, $body);	
			if($headers && $body) {
				foreach($headers AS $val) {
					if($val['name'] == "From") { 
						$pos = strpos($val['value'], "<");
						if(($pos >= 0) && ($val['value'] != "")) {
							$the_message[$z]['from'] = GetBetween($val['value'],'<','>'); 
							$the_message[$z]['fromname'] = $the_message[$z]['from'];
							} else {
							if(is_email($val['value'])) {
								$the_message[$z]['from'] = $val['value'];
								$the_message[$z]['fromname'] = $the_message[$z]['from'];
								}
							}
						}
					if($val['name'] == "To") {
						$pos = strpos($val['value'], "<");					
						if(($pos >= 0) && ($val['value'] != "")) {
							$the_message[$z]['to'] = GetBetween($val['value'],'<','>');
							} else {
							if(is_email($val['value'])) {
								$the_message[$z]['to'] = $val['value'];
								}							
							}
						}
					if($val['name'] == "X-Originating-Email") { 
						$pos = strpos($val['value'], "[");
						if(($pos >= 0) && ($val['value'] != "")) {					
							$the_message2['from'] = GetBetween($val['value'],'[',']'); 
							$the_message2['fromname'] = $the_message2['from'];							
							} else {
							if(is_email($val['value'])) {
								$the_message2['from'] = $val['value'];
								$the_message2['fromname'] = $the_message[$z]['from'];
								}
							}
						}
					if($val['name'] == "Subject") { $the_message[$z]['subject'] = $val['value']; } 
					if($val['name'] == "Date") { $the_message[$z]['date'] = $val['value']; } 
					if((!array_key_exists('from', $the_message[$z])) && (array_key_exists('from', $the_message2) && $the_message2['from'] != "")) {
						$the_message[$z]['from'] = $the_message2['from'];
						} elseif((!array_key_exists('from', $the_message[$z])) && (!array_key_exists('from', $the_message2))) {
						$the_message[$z]['from'] = "No Address";
						} else {
						$the_message[$z]['from'] = $the_message[$z]['from'];
						}
					if((!array_key_exists('fromname', $the_message[$z])) && (array_key_exists('fromname', $the_message2) && $the_message2['fromname'] != "")) {
						$the_message[$z]['fromname'] = $the_message2['fromname'];
						} elseif((!array_key_exists('fromname', $the_message[$z])) && (!array_key_exists('fromname', $the_message2))) {
						$the_message[$z]['fromname'] = "No Name";
						} else {
						$the_message[$z]['fromname'] = $the_message[$z]['fromname'];
						}						
					if((!array_key_exists('fromname', $the_message[$z])) && (array_key_exists('fromname', $the_message2) && $the_message2['fromname'] != "")) {
						$the_message[$z]['fromname'] = $the_message2['fromname'];
						}						
					}
				$the_message[$z]['to'] = ((array_key_exists('to', $the_message[$z])) && ($the_message[$z]['to'] != "")) ? $the_message[$z]['to'] : "Tickets";				
				$the_message[$z]['subject'] = ((array_key_exists('subject', $the_message[$z])) && ($the_message[$z]['subject'] != "")) ? $the_message[$z]['subject'] : "Email";
				$the_message[$z]['text'] = clean_hdr_fm_text(addslashes(htmlentities($body[0]['content'])));
				$the_message[$z]['text'] = ((array_key_exists('text', $the_message[$z])) && ($the_message[$z]['text'] != "")) ? $the_message[$z]['text'] : "No Text";	
				$from_address = $the_message[$z]['from'];
				$from_name = (($the_message[$z]['fromname'] == "No Name") && ($from_address != "")) ? $from_address : $the_message[$z]['fromname'];	
				$to = $the_message[$z]['to'];	
				$subject = $the_message[$z]['subject'];
				$text = $the_message[$z]['text'];	
				if((array_key_exists('date', $the_message[$z])) && ($the_message[$z]['date'] != "")) {
					$date = date_parse($the_message[$z]['date']);				
					$datepart = $date['year'] . "-" . $date['month'] . "-" . $date['day'];
					$timepart = $date['hour'] . ":" . $date['minute'] . ":" . $date['second'];
					$datestring = $datepart . " " . $timepart;	
					} else {
					$datestring = $now;
					}
				$message_arr = array();
				$message_arr[0] = "Incoming email";
				$message_arr[1] = $subject;
				$message_arr[2] = $text;
				$message_arr[3] = $datestring;
				$the_email = store_theemail($message_arr);
				print "<TD style='border: 1px outset #707070;'>" . $the_email[0] . "</TD>";
				print "<TD style='border: 1px outset #707070;'>" . $the_email[1] . "</TD>";
				print "<TD style='border: 1px outset #707070;'>" . $the_email[2] . "</TD>";
				print "<TD style='border: 1px outset #707070;'>" . $the_email[3] . "</TD>";
				print "<TD style='border: 1px outset #707070;'>" . $the_email[4] . "</TD>";
				}
			print "</TR>";
			if($style = "style='background-color: #707070; color: #FFFFFF;'") {
				$style = "style='background-color: #CECECE; color: #000000;'";
				} else {
				$style = "style='background-color: #707070; color: #FFFFFF;'";
				}
			}
		print "</TABLE>";
		}
	POP3::pQuit($c);
	POP3::disconnect($c);	
	}

function store_theemail($email_arr) {
	$messageid = $email_arr[0];
	$subject = addslashes($email_arr[1]);
	$message = mysql_real_escape_string(addslashes($email_arr[2]));
	$time = $email_arr[3];
	$counter = 0;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = '2' AND `message_id` = '{$messageid}' AND `subject` = '{$subject}' AND `message` = '{$message}' AND `date` = '" . $time . "'";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 0) {
		$counter = 1;
		}
	$stored = ($counter == 1) ? "Stored" : "Not Stored";
	$email_arr[4] = $stored;
	$ret_arr[0] = $messageid;
	$ret_arr[1] = $subject;
	$ret_arr[2] = shorten($message, 35);
	$ret_arr[3] = $time;
	$ret_arr[4] = $stored;
	return $ret_arr;
	}	

if((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 3)) {
	$url = get_msg_variable('email_server');
	$port = intval(get_msg_variable('email_port'));
	$protocol = get_msg_variable('email_protocol');
	$addon = get_msg_variable('email_addon');
	$folder = get_msg_variable('email_folder');
	$user = get_msg_variable('email_userid'); 
	$password = get_msg_variable('email_password');
	$ssl = 'ssl';
	print "Email server parameters: " . $url . ", " . $user . ", " . $password . ", " . $port . ", " . $ssl . "<BR /><BR />";
	get_theemails("$url", "$user", "$password", $port, "$ssl", 100);
	}
?>	
