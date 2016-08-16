<?php

/*
12/15/15	New File - Websocket server using core PHP stream sockets.
*/
error_reporting(0);
require_once('./incs/functions.inc.php');

// Core Server functions

//	check if port is open or free
function check_port($port) {
    $conn = @fsockopen("127.0.0.1", $port, $errno, $errstr, 2);
    if ($conn) {
        fclose($conn);
        return "1";
		} else {
		return "0";
		}
	}

//	Check commonly used ports
function server_report() {
    $report = array();
    $svcs = array('21'=>'FTP',
                  '22'=>'SSH',
                  '25'=>'SMTP',
                  '80'=>'HTTP',
                  '110'=>'POP3',
                  '143'=>'IMAP',
				  '1337'=>'TICKETS SOCKET SERVER',
                  '3306'=>'MySQL');
    foreach ($svcs as $port=>$service) {
        $report[$service] = check_port($port);
    }
    return $report;
	}

dump(server_report());
exit();
?>
