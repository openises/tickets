<?php
extract($_GET);
set_time_limit(0);
require_once('functions.inc.php');

function get_provider_name($val) {
	switch($val) {
		case 0:
		print "Not Implemented";
		break;	
		case 1:
		print "SMS Responder";
		break;
		case 2:
		print "Txt Local";
		break;		
		default:
		print "Unknown";
		}
	}

function update_msg_setting ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]msg_settings` WHERE `name`= '$which' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]msg_settings` SET `value`= '$what' WHERE `name` = '$which'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	unset ($result);
	return TRUE;
	}				// end function update_setting ()
	
function update_delivered($who, $what) {
	$deliveredto = array();
	$thetemp = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `message_id` = '" . $what . "' AND `msg_type` = '3' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$thetemp = ($row['delivered'] != NULL) ? explode("," , $row['delivered']) : NULL;
		foreach($thetemp as $val) {
			$deliveredto[] = intval($val);
			}
		if(!(in_array($who, $deliveredto, true)))	{
			if(count($deliveredto) == 0) {
				$the_string = $who;
				} else {
				$the_string = $row['delivered'] . "," . $who;
				}
			$query_new = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `delivered` = '" . $the_string . "' WHERE `message_id` = '$what'";
			$result_new = mysql_query($query_new) or do_error($query_new, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
			if($result_new) {
				unset($thetemp);
				unset($deliveredto);
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `message_id` = '" . $what . "' AND `msg_type` = '3' LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$thetemp = ($row['delivered'] != NULL) ? explode("," , $row['delivered']) : NULL;
				foreach($thetemp as $val) {
					$deliveredto[] = intval($val);
					}				
				}
			}
		$the_d_count = ($deliveredto != NULL) ? count($deliveredto) : 0;
		$sentto = ($row['recipients'] != NULL) ? explode("," , $row['recipients']) : NULL ;
		$the_s_count = ($sentto != NULL) ? count($sentto) : 0;	
		if($the_d_count == 0) {
			$del_stat = 0;
			} elseif(($the_d_count > 0) && ($the_d_count != $the_s_count)) {
			$del_stat = 1;
			} elseif(($the_d_count > 0) && ($the_d_count == $the_s_count)) {
			$del_stat = 2;
			} else {
			$del_stat = 99;
			}
			
		$delivered = $row['delivered'];
		if(strpos($delivered, ",")) {
			$the_sep = ",";
			} else {
			$the_sep = "";
			}

		$the_string = $the_sep . $sentto;
		$query2 = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `delivery_status` = " . $del_stat . " WHERE `message_id`='$what'";
		$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		return true;
		} else {
		return false;
		}
	}
	
function format_smsdate($time) {
	$times12=array(1,2,3,4,5,6,7,8,9,10,11,12);
	$times24=array(13,14,15,16,17,18,19,20,21,22,23,00);
	$the_date = explode(" ", $time);
	$datepart = $the_date[0];
	$timepart = $the_date[1];
	$meridiem = (isset($the_date[2])) ? $the_date[2] : "";
	$thetime = explode(":", $timepart);
	$thehour = $thetime[0];
	$the_marker = intval($thehour) - 1;
	if($meridiem == "PM") {
		$hourpart = $times24[$the_marker];
		} else {
		$hourpart = $thehour ;
		}
	if(($meridiem == "AM") && ($thehour == "12")) {
		$hourpart =  "00";
		}
	$the_timestring = $hourpart . ":" . $thetime[1] . ":" . $thetime[2];
	$date_arr = explode("/", $datepart);
	$day=$date_arr[1];
	$month = $date_arr[0];
	$year = $date_arr[2];
	$datestring = $year . "-" . $month . "-" . $day . " " . $the_timestring;
	return $datestring;
	}

function check_validxml($page) {
	libxml_use_internal_errors(true);
	if($xml = simplexml_load_file($page)) {
		return true;
		} else {
		return false;
		}
	}
	
function check_server($url) {
	if (function_exists("curl_init")) {
		if($url == NULL) return false;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		$pos = strpos($data, "API PIN");
		if($pos) {
			return true;
		} else {
			if(check_validxml($data)) {	
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch); 
				if($httpcode>=200 && $httpcode<300){
					return true;
				} else {
					return false;
				}
			} else {
				curl_close($ch);
				return false;
			}
		}
	} else {
		$url = @parse_url($url); 
		if (!$url) return false; 	
		$url = array_map('trim', $url); 
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port']; 
		 
		$path = (isset($url['path'])) ? $url['path'] : '/'; 
		$path .= (isset($url['query'])) ? "?$url[query]" : ''; 
		 
		if (isset($url['host']) && $url['host'] != gethostbyname($url['host'])) { 
			$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30); 
		 
			if (!$fp) return false; //socket not opened

			fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n"); //socket opened
			$headers = fread($fp, 4096); 
			fclose($fp); 
		 
			if(preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers)){//matching header
				return true; 
				} else {
				return false;
				}
			} else {
				do_log($GLOBALS['LOG_SMSGATEWAY_CONNECT'], 0, 0, "Cannot connect to SMS Gateway server: ");				
				return false;
			}
		}
	}

function get_reader_name($id){								/* get owner name from id */
	$result	= mysql_query("SELECT user FROM `$GLOBALS[mysql_prefix]user` WHERE `id`='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "None" : $row['user'];
	}

function can_delete_msg(){
	if($_SESSION['level'] ==  $GLOBALS['LEVEL_SUPER']) {
		$ret = 1;
		} else {
		$ret = 0;
		}
	return $ret;
	}
	
function GetBetween($content,$start,$end){	//	Function to check for presence of text between two delimiters
  	$r = explode($start, $content);
    	if (isset($r[1])){
        	$r = explode($end, $r[1]);
        	return $r[0];
		}
    	return '';
	}
	
function auto_status($message, $responder, $datestring) {
	$time = strtotime($datestring);
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$start_tag = get_msg_variable('start_tag');
	$end_tag = get_msg_variable('end_tag');
	$string = strtoupper(GetBetween($message, "$start_tag", "$end_tag"));
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]auto_status` WHERE `text` = '" . $string . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) >= 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_val = intval($row['status_val']);
		$query_time = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `smsg_id`='" . $responder . "'";
		$result_time = mysql_query($query_time) or do_error($query_time, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_time = stripslashes_deep(mysql_fetch_assoc($result_time));
		if(strtotime($row_time['updated']) < $time) {
			$query2 = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`=" . $the_val . ", `user_id`='999999', `updated`= '" . $datestring . "'WHERE `smsg_id`='" . $responder . "'";
			$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
			if($result2) {
				$the_ret = $the_val;
				} else {
				$the_ret = 0;
				}
			} else {
			$the_ret = 0;
			}
		} else {
		$the_ret = 0;
		}
	return $the_ret;
	}

function ReplaceImap($txt) {	//	function to clean up body text
	$carimap = array("=C3=A9", "=C3=A8", "=C3=AA", "=C3=AB", "=C3=A7", "=C3=A0", "=20", "=C3=80", "=C3=89");
	$carhtml = array("é", "è", "ê", "ë", "ç", "à", "&nbsp;", "À", "É");
	$txt = str_replace($carimap, $carhtml, $txt);
	return $txt;
	}

// Xpertmailer version
function get_emails($url, $user, $password, $port, $ssl="", $timeout=10 ) {	//	Called from AJAX file to get emails in background - AJAX file called by top.php
	$counter = 0;
	$ret = array();
	$the_list = white_list();	
	$ticket_id = 0;
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
				dump($the_message);
				if(in_array($from, $the_list)) {
					$date = date_parse($the_message[$z]['date']);				
					$datepart = $date['year'] . "-" . $date['month'] . "-" . $date['day'];
					$timepart = $date['hour'] . ":" . $date['minute'] . ":" . $date['second'];
					$datestring = $datepart . " " . $timepart;	
					$the_count = store_email(2, "Tickets", "email", "{$the_message[$z]['subject']}", "{$the_message[$z]['text']}", $ticket_id, 0, $datestring, $the_message[$z]['from'], $fromname);
					if($the_count == 1) {
						$counter++;
						}
					}			
				}
			}
		// optional, you can delete this message from server
		//	POP3::pDele($c, $i);
		} else {
		$i = 0;
		}
	$ret[0] = $i;
	$ret[1] = $counter;
	return $ret;
	}	
	
function store_email($msg_type, $recipients, $messageid, $subject, $message, $ticket_id = 0, $resp_id = 0, $time, $from_address, $fromname) {	//	stores incoming and outgoing emails in messages table
	$counter = 0;
	$message = addslashes($message);
	$subject = addslashes($subject);
	$resp_id =(($resp_id == "") || ($resp_id == 0) || ($resp_id == "")) ? '0' : $resp_id;
	$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 1;		// 11/14/10
	$from = $_SERVER['REMOTE_ADDR'];
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$message = mysql_real_escape_string($message);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = '2' AND `message_id` = '{$messageid}' AND `subject` = '{$subject}' AND `message` = '{$message}'";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]messages` (msg_type, message_id, ticket_id, resp_id, recipients, from_address, fromname, subject, message, date, _by, _from, _on) VALUES({$msg_type},'{$messageid}',{$ticket_id},{$resp_id},'{$recipients}','{$from_address}','{$fromname}','{$subject}','" . $message . "','{$now}',{$who},'{$from}','{$now}')";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
		if($result) {
			$counter = 1;
			}
		}
	return $counter;
	}

function white_list() {	//	function to check sender is in allowed list - allowed list determines which incoming emails will be stored and viewable.
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]known_sources`";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
		$the_ret[] = $row['email'];
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
		if($row['contact_via'] != "") {
			$the_ret[] = $row['contact_via'];
			}
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts`";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
		if($row['email'] != "") {
			$the_ret[] = $row['email'];
			}
		}
	return $the_ret;
	}

function get_resp_id($resp_handle) {	//	Gets responder ID from SMS Gateway ID
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `smsg_id` = '" . $resp_handle . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_id = $row['id'];
		} else {
		$the_id=NULL;
		}
	return $the_id;
	}
	
function get_resp_id2($theEmail) {	//	Gets responder ID from email
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `contact_via` = '" . $theEmail . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_id = $row['id'];
		} else {
		$the_id=NULL;
		}
	return $the_id;
	}
	
function get_resp_name($resp_handle) {	//	Gets responder ID from SMS Gateway ID
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `smsg_id` = '" . $resp_handle . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_name = $row['name'];
		} else {
		$the_name="No Name";
		}
	return $the_name;
	}	

function send_message($server,$orgcode,$apipin,$message,$reciptype,$recipients,$importance,$replyto,$mode) {	//	Sends message to SMS Gateway
//	print $server . "," . $orgcode . "," . $apipin . "," . $message . "," . $reciptype . "," . $recipients . "," . $importance . "," . $replyto . "," . $mode . "<BR />";
	$smsg_server_inuse = array();
	$smsg_server_inuse[0] = ($server == 1) ? get_msg_variable('smsg_og_serv1') : get_msg_variable('smsg_og_serv2');
	if($smsg_server_inuse[0] == get_msg_variable('smsg_og_serv1')) {
		$smsg_server_inuse[1] = get_msg_variable('smsg_server');
		} else {
		$smsg_server_inuse[1] = get_msg_variable('smsg_server2');
		}
	$url = $smsg_server_inuse[0];
	$reply_to = ($smsg_server_inuse[0] == get_msg_variable('smsg_og_serv1')) ? get_msg_variable('smsg_replyto') : get_msg_variable('smsg_replyto_2');
	
	if (function_exists("curl_init")) {	
		$fields = array(
					'orgcode'=>urlencode($orgcode),
					'apipin'=>urlencode($apipin),
					'message'=>urlencode($message),
					'reciptype'=>urlencode($reciptype),
					'recipients'=>urlencode($recipients),
					'importance'=>urlencode($importance),
					'replyto'=>urlencode($reply_to),
					'mode'=>urlencode($mode)						
					);

		//url-ify the data for the POST
		$fields_string="";
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&'; 
			}
		rtrim($fields_string,'&');

		//open connection
		$ch = curl_init();
		$timeout = 10;	

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);	
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);	
		
		//execute post
		$result = curl_exec($ch);
		//close connection
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		
		if ($curl_errno > 0) {
			print $curl_error . "<BR />";
			print $curl_errno . "<BR />";
			$result = "999";
			}
	} else {	//	No cURL
		$optional_headers = NULL;
		$data = array(
					'orgcode'=>urlencode($orgcode),
					'apipin'=>urlencode($apipin),
					'message'=>urlencode($message),
					'reciptype'=>urlencode($reciptype),
					'recipients'=>urlencode($recipients),
					'importance'=>urlencode($importance),
					'replyto'=>urlencode($replyto),
					'mode'=>urlencode($mode)						
					);
		$params = array('http' => array(  
					'method' => 'POST',  
					'content' => $data  
					));  
		if ($optional_headers !== null) {  
			$params['http']['header'] = $optional_headers;  
			}  
		$ctx = stream_context_create($params);  
		$fp = @fopen($url, 'rb', false, $ctx);  
		if (!$fp) { 
			$result = "999";
			}  
		$response = @stream_get_contents($fp);  
		if ($response === false) {  
			$result = "999";
			}  
		}
	return $result;	
	}
	
function get_responses($server,$orgcode,$apipin,$messageid,$mode) {	//	Polls SMS Gateway for responses - called function do_smsg_retrieve(..
	$smsg_server_inuse = array();
	$smsg_server_inuse[0] = ($server == 1) ? get_msg_variable('smsg_og_serv1') : get_msg_variable('smsg_og_serv2');
	if($smsg_server_inuse[0] == get_msg_variable('smsg_og_serv1')) {
		$smsg_server_inuse[1] = get_msg_variable('smsg_server');
		} else {
		$smsg_server_inuse[1] = get_msg_variable('smsg_server2');
		}
	$url = $smsg_server_inuse[1];
	if (function_exists("curl_init")) {	
		$fields = array(
					'orgcode'=>urlencode($orgcode),
					'apipin'=>urlencode($apipin),
					'msgid'=>urlencode($messageid),
					'checkmode'=>urlencode($mode)						
					);
		//url-ify the data for the POST
		$fields_string="";
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&'; 
			}
		rtrim($fields_string,'&');

		//open connection
		$ch = curl_init();
		$timeout = 10;	

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);	
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);	
		
		//execute post
		$result = curl_exec($ch);

		//close connection
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno) {
			$result = "999";
			}
	} else {	//	No cURL
		$optional_headers = NULL;
		$data = array(
					'orgcode'=>urlencode($orgcode),
					'apipin'=>urlencode($apipin),
					'msgid'=>urlencode($messageid),
					'checkmode'=>urlencode($mode)						
					);
		$params = array('http' => array(  
					'method' => 'POST',  
					'content' => $data  
					));  
		if ($optional_headers !== null) {  
			$params['http']['header'] = $optional_headers;  
			}  
		$ctx = stream_context_create($params);  
		$fp = @fopen($url, 'rb', false, $ctx);  
		if (!$fp) { 			
			$result = "999";
			}
		$response = @stream_get_contents($fp);  
		if ($response === false) {  
			$result = "999";
			}  
		}
	return $result;	
	}
	
function xml2array($contents, $get_attributes=1, $priority = 'tag') { 	//	function to parse incoming XML from SMS Gateway into a PHP array
    if(!$contents) return array(); 

    if(!function_exists('xml_parser_create')) { 
        //print "'xml_parser_create()' function not found!"; 
        return array(); 
    } 

    //Get the XML parser of PHP - PHP must have this module for the parser to work 
    $parser = xml_parser_create(''); 
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");  
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
    xml_parse_into_struct($parser, trim($contents), $xml_values); 
    xml_parser_free($parser); 

    if(!$xml_values) return;//Hmm... 

    //Initializations 
    $xml_array = array(); 
    $parents = array(); 
    $opened_tags = array(); 
    $arr = array(); 

    $current = &$xml_array; //Refference 

    //Go through the tags. 
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array 
    foreach($xml_values as $data) { 
        unset($attributes,$value);//Remove existing values, or there will be trouble 

        //This command will extract these variables into the foreach scope 
        // tag(string), type(string), level(int), attributes(array). 
        extract($data);//We could use the array by itself, but this cooler. 

        $result = array(); 
        $attributes_data = array(); 
         
        if(isset($value)) { 
            if($priority == 'tag') $result = $value; 
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode 
        } 

        //Set the attributes too. 
        if(isset($attributes) and $get_attributes) { 
            foreach($attributes as $attr => $val) { 
                if($priority == 'tag') $attributes_data[$attr] = $val; 
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
            } 
        } 

        //See tag status and do the needed. 
        if($type == "open") {//The starting of the tag '<tag>' 
            $parent[$level-1] = &$current; 
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
                $current[$tag] = $result; 
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 

                $current = &$current[$tag]; 

            } else { //There was another element with the same tag name 

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                    $repeated_tag_index[$tag.'_'.$level]++; 
                } else {//This section will make the value an array if multiple tags with the same name appear together 
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array 
                    $repeated_tag_index[$tag.'_'.$level] = 2; 
                     
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                        $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                        unset($current[$tag.'_attr']); 
                    } 

                } 
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
                $current = &$current[$tag][$last_item_index]; 
            } 

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
            //See if the key is already taken. 
            if(!isset($current[$tag])) { //New Key 
                $current[$tag] = $result; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data; 

            } else { //If taken, put all things inside a list(array) 
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 

                    // ...push the new element into that array. 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                     
                    if($priority == 'tag' and $get_attributes and $attributes_data) { 
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; 

                } else { //If it is not an array... 
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value 
                    $repeated_tag_index[$tag.'_'.$level] = 1; 
                    if($priority == 'tag' and $get_attributes) { 
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                             
                            $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                            unset($current[$tag.'_attr']); 
                        } 
                         
                        if($attributes_data) { 
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                        } 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken 
                } 
            } 

        } elseif($type == 'close') { //End of tag '</tag>' 
            $current = &$parent[$level-1]; 
        } 
    } 
    return($xml_array); 
	}  
	
function check_xml_response() {
	$server = (get_msg_variable('smsg_force_sec') == 0) ? (get_msg_variable('smsg_server_inuse')) : 2; 	//	Gets current server in use - primary or backup
	$data = send_message($server,'','','','','','','','');	//	Calls function that does the sending
	if(!$data) {
		if(get_msg_variable('smsg_server_inuse') == 1) {	//	If current server in use is set as 1, change to 2.
			update_msg_setting ('smsg_server_inuse', 2);	//	Changes Server to backup if no response or incorrect response from SMS Gateway Server
			} else {
			update_msg_setting ('smsg_server_inuse', 2);	//	Changes Server to backup if no response or incorrect response from SMS Gateway Server
			}
		$server = get_msg_variable('smsg_server_inuse');
		$data = send_message($server,'','','','','','','','');	//	Calls function that does the sending
		if(!$data) {		
			print "Problem with the SMS Gateway<BR />";
			do_log($GLOBALS['LOG_SMSGATEWAY_SEND'], 0, 0, "Cannot send SMS Message to SMS Gateway: ");			
			return false;
			} else {
			$ret_arr = array();
			$ret_arr = xml2array($data);
			if((isset($ret_arr['SMSRESPONDER']['ERROR'])) && ($ret_arr['SMSRESPONDER']['ERROR'] == 'Unable to find this Org Code with this API PIN')) {
				return true;
				} else {
				return false;
				}			
			}			
		} else {
		$ret_arr = array();
		$ret_arr = xml2array($data);
		if((isset($ret_arr['SMSRESPONDER']['ERROR'])) && ($ret_arr['SMSRESPONDER']['ERROR'] == 'Unable to find this Org Code with this API PIN')) {
			return true;
			} else {
			return false;
			}			
		}	
	}

function do_smsg_send($orgcode,$apipin,$subject,$message,$reciptype,$recipients,$importance,$replyto,$mode,$ticket_id) {	//	Collects data for message - called from FIP function do_send(...)
	$now = time() - (intval(intval(get_variable('delta_mins')))*60);
	$ret_arr=array();
	$each_recipient=array();
	if(check_xml_response()) {
		$server = (get_msg_variable('smsg_force_sec') == 0) ? (get_msg_variable('smsg_server_inuse')) : 2; 	//	Gets current server in use - primary or backup
		$data = send_message($server,$orgcode,$apipin,$message,$reciptype,$recipients,$importance,$replyto,$mode);	//	Calls function that does the sending
		$ret_arr=xml2array($data);	
		$count = $ret_arr['SMSRESPONDER']['RECIPIENTCOUNT'];
		$messageid = $ret_arr['SMSRESPONDER']['MESSAGEID'];
		$datestring = date("Y-m-d H:i:s", $now);
		if($count >= 1) {
			store_msg($recipients, $messageid, $subject, $message, 'TICKETS', $ticket_id, $datestring, $datestring, 3);
			}
		} else {
		$count = 0;
		}
	return $count;
	}

function XmlIsWellFormed($xmlContent) {
	libxml_use_internal_errors(true);
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($xmlContent);
	$errors = libxml_get_errors();
	if (empty($errors)){
		return true;
		}
	$error = $errors[ 0 ];
	if ($error->level < 3){
		return true;
		}
	return false;
	}
	
function do_smsg_retrieve($orgcode,$apipin,$mode) {	// retrieves responses from SMS Gateway called from AJAX file which is called from top.php
	$rtn_msg = "";
	$stat_up = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `msg_type` = '3'";	//	Select messages to query for updates - only ones where the OG message has been sent by Tickets
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_response=array();
		$ticket_id = $row['ticket_id'];
		$messageid = $row['message_id'];
		$server = (get_msg_variable('smsg_force_sec') == 0) ? (get_msg_variable('smsg_server_inuse')) : 2; 	//	Gets current server in use - primary or backup	
		$data = get_responses($server,$orgcode,$apipin,$messageid,$mode);	//	Calls function that does the sending
		$the_response=xml2array($data);
		if(check_xml_response()) {
			$response_count = count($the_response['SMSRESPONDER']['RECIPIENT']);
			$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));	
		// Messages and status updates
			$t = 0;
			$recipient = array();
			$ret_arr1 = array();
			foreach($the_response['SMSRESPONDER'] AS $recipient) {
				if(isset($recipient[$t])) {
					$ret_arr1 = $recipient;
					} else {
					$ret_arr1[$t] = $recipient;
					}
				$t++;
				}
			$ret_arr = array();
			foreach($ret_arr1 as $ret_arr) {
				$callsign = $ret_arr['CALLSIGN'];
				$contact = $ret_arr['CONTACT'];			
				$status = $ret_arr['STATUS'];
				$thetime = (!is_array($ret_arr['TIME'])) ? format_smsdate($ret_arr['TIME']) : "";
				switch($status) {	//	Work with message status updates - write logs for failures.
					case "FAILED (NO RESPONSE)":
						do_log($GLOBALS['LOG_SMSGATEWAY_RECEIVE'], 0, 0, "SMS Message Not received by recipients: ");							
						break;	
					case "TRANSIT":
						break;
					case "DELIVERED LATE":
						do_log($GLOBALS['LOG_SMSGATEWAY_RECEIVE'], 0, 0, "SMS Message Delivery delay: ");	
						$who = intval(get_resp_id($callsign));
						$what = $messageid;
						update_delivered($who, $what);							
						break;
					case "DELIVERED":
						$who = intval(get_resp_id($callsign));
						$what = $messageid;
						update_delivered($who, $what);						
						break;						
					default:
						if($status == "") { $status = "NA"; }
	//						$temp = store_msg($callsign, $messageid, "SMS Responder Status Update", $status, 'SMSR', $ticket_id, $thetime, 0, 4);
						}				
					
	//	Replies
				if(isset($ret_arr['REPLY'])) {	//	Check if replies exist
					$the_replies = array();
					if(isset($ret_arr['REPLY'][0]['TEXT'])) {
						$the_replies = $ret_arr['REPLY'];
						} else {
						$the_replies[0] = $ret_arr['REPLY'];
						}
	//				$replies = array();
					foreach($the_replies AS $replies) {
						$replyto = $callsign;
						$message = $replies['TEXT'];
						if($message == "") { $message = "NA"; }
						$datestring = (isset($replies['TIME'])) ? format_smsdate($replies['TIME']) : $now;
						$respname = (get_resp_name($replyto) != "") ? get_resp_name($replyto): "NA";
						$resp_id = intval(get_resp_id($replyto));
						$temp = store_msg($replyto, $messageid, "SMS Reply", $message, $respname, $ticket_id, $datestring, 0, 4);
						if(get_msg_variable('use_autostat') == 1) {	//	 Check if Auto Status Updates is set as on and if so check replies for smart text.
							$the_return = auto_status($message, $replyto, $datestring);
							if($the_return != 0) {
								$stat_up[$resp_id] = $the_return;	//	if auto status is on and funtion auto status returns a required update then write that update to $stat_up array for output.
								}
							}
						}
					}
				}
			if(empty($stat_up)) {
				$stat_up[0] = 99;
				}
			} else {
			$stat_up[0] = 99;	
			}
		}
	return $stat_up;
	}

function store_msg($recipients, $messageid, $subject, $message, $fromname, $ticket_id, $time, $ogtime, $type) {	//	Stores incoming and outgoing SMS Messages from or to Gateway in Messages table
	$message = addslashes($message);
	$subject = addslashes($subject);
	$stored = 0;
	$the_responders = array();
	if($recipients != "Tickets") {
		$therecip = explode(",", $recipients);
		foreach($therecip as $val) {
			$the_responders[] = get_resp_id($val);
			}
		$the_resp_list = implode(",", $the_responders);
		}
	$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 1;
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));	
	$resp_id = ((isset($recipients)) && ($recipients != "Tickets")) ? $the_resp_list : $recipients;
	// if(($time != "") && ($time != 0)) {
		// $datestring = $time;
		// } else {
		// $datestring = $now;
		// }
	$datestring = $now;
	if(($ogtime != "") && ($ogtime != 0)) {	
		$datestring = $ogtime;
		}
	$from = "127.0.0.0";
	if($type == 4) {
		if(($messageid != "") && ($recipients != "") && ($datestring != "")) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `message_id` = '{$messageid}' AND `ticket_id` = {$ticket_id} AND `from_address` = '{$recipients}' AND `message` = '{$message}'";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
			if(mysql_num_rows($result) == 0) {
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `message_id` = '" . $messageid . "' AND `msg_type` = '3'";
				$result1 = mysql_query($query1) or do_error($query1, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
				if(mysql_num_rows($result1) != 0) {
					while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) 	{
						$query2 = "INSERT INTO `$GLOBALS[mysql_prefix]messages` (msg_type, message_id, ticket_id, resp_id, recipients, subject, message, from_address, fromname, date, `read_status`, _by, _from, _on) VALUES(4,'{$messageid}',{$ticket_id},'{$resp_id}','{$recipients}','{$subject}','{$message}','{$recipients}','{$fromname}','{$datestring}',0,{$who},'{$from}','{$now}')";
						$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
						if($result2) {
							$stored = 1;
							}
						}
					} else {
					$query2 = "INSERT INTO `$GLOBALS[mysql_prefix]messages` (msg_type, message_id, ticket_id, resp_id, recipients, subject, message, from_address, fromname, date, `read_status`, _by, _from, _on) VALUES(3,'{$messageid}',{$ticket_id},'{$resp_id}','{$recipients}','{$subject}','{$message}','{$recipients}','{$fromname}','{$datestring}',0,{$who},'{$from}','{$now}')";
					$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
					if($result2) {
						$stored = 1;
						}
					}
				}
			}
		} elseif($type == 3) {
		$query2 = "INSERT INTO `$GLOBALS[mysql_prefix]messages` (msg_type, message_id, ticket_id, resp_id, recipients, subject, message, from_address, fromname, date, `read_status`, _by, _from, _on) VALUES(3,'{$messageid}',{$ticket_id},'{$resp_id}','{$recipients}','{$subject}','{$message}','{$recipients}','{$fromname}','{$datestring}',0,{$who},'{$from}','{$now}')";
		$result2 = mysql_query($query2) or do_error($query2, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
		if($result2) {
			$stored = 1;
			}
		} else {
		//	Do nothing
		}
	return $stored;
	}

?>	