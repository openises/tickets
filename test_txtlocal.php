<?php
/*
test_txtlocal.php - Test retrieve messages for TXTLOCAL SMS Gateway
09/22/16 - new file
*/
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');
error_reporting(E_ALL);				// 9/13/08
set_time_limit(0);
@session_start();
session_write_close();

function multi_array_key_exists($key, $array) {
    if (array_key_exists($key, $array)) {
        return true;
		} else {
        foreach ($array as $nested) {
            if (is_array($nested) && multi_array_key_exists($key, $nested)) {
                return true;
				}
			}
		}
    return false;
	}

$username = "harveyaj11@hotmail.com";
$hash = "c3129bbb6d62e6ecd592aea0f86159144e54da83";
$server = 'http://api.txtlocal.com/get_messages/';
$the_ret = get_responses_txtlocal($server,$username,$hash);
$response = xml2array($the_ret);
if(multi_array_key_exists('code', $response)) {
	print "Error code: " . $response['response']['errors']['error']['code'] . "<BR />";
	print "Error message: " . $response['response']['errors']['error']['message'] . "<BR />";
	} else {
	$messages = $response['response']['messages']['message'];
	$count = 0;
	foreach($messages as $val) {
		$messageid = $val['id'];
		$dn = $val['number'];
		$message = $val['message'];
		$datestring = $val['date'];
		$replyto = get_resp_id('GR08');
		$respname = (get_resp_name($replyto) != "") ? get_resp_name($replyto): "NA";
		$resp_id = intval(get_resp_id($replyto));
		$ticket_id = link_ticket($message);
		$msgType = 4;
		$server = 0;
//		print $replyto . ", " . $messageid . ", SMS Reply, " . $message . ", " . $respname . ", " . $ticket_id . ", 0, " . $msgType . ", " . $server . "<BR />";
		$temp = store_msg($replyto, $messageid, "SMS Reply", $message, $respname, $ticket_id, $datestring, 0, $msgType, $server);
		$temp = intval($temp);
		$count = $count + $temp;
		}
	print "Number of messages stored " . $count . "<BR />";
	}
exit();
?>	
