<?php

/*
12/15/15	New File - Websocket server using core PHP stream sockets.
*/
error_reporting(0);
require_once('../incs/functions.inc.php');
$temp1  = (get_variable('socketserver_url') != "") ? get_variable('socketserver_url') : "localhost";
$temp2 = get_variable('socketserver_port');
$serveraddress = (array_key_exists("SERVER_NAME", $_SERVER)) ? "{$_SERVER['SERVER_NAME']}" : $temp1;
$serverport = ($temp2 == "") ? "1337" : $temp2;
$serverstring = "tcp://" . $serveraddress . ":" . $serverport;

// Core Server functions

//	check if port is open or free
function check_port($port) {
	global $serveraddress;
    $conn = @fsockopen($serveraddress, $port, $errno, $errstr, 2);
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

function checkAdmin($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if($num_rows) {
		if($num_rows == 1) {
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$level = $row['level'];
			}
		if($level == $GLOBALS['LEVEL_ADMINISTRATOR'] || $level == $GLOBALS['LEVEL_SUPER']) {
			return true;
			} else {
			return false;
			}
		} else {
		return false;
		}
	}
	
function do_nouser_shutdown() {
	echo "Shutting down server as there are no clients anyore\r\n";
	global $server;	
	sleep(5);
	fclose($server);
	exit;	
	}

//	Clean shutdown
function do_shutdown($data) {
	global $server, $client_socks;
	$elements = explode("/", $data);
	$theMessage = "Closing down websocket server. Please refresh your screen to restart the websocket server";
	$sendstring = $elements[0] . "/" . $theMessage . "/199";
	echo "Closing Server\r\n";
	foreach($client_socks as $theClient) {
		$writeReturn = fwrite($theClient, encode($sendstring));
		}
	sleep(5);
	fclose($server);
	exit;
	}
	
function do_restart($data) {
	global $server, $client_socks, $serveraddress, $serverport, $serverstring;
	$elements = explode("/", $data);
	$theMessage = "Restarting websocket server";
	$sendstring = $elements[0] . "/" . $theMessage . "/" . "199";
	echo "Restarting server\r\n";
	foreach($client_socks as $theClient) {
		$writeReturn = fwrite($theClient, encode($sendstring));
		}
	$server = stream_socket_server("{$serverstring}", $errno, $errstr);
	if ($server === false) {
		echo "Cannot start server";
		die("$errstr ($errno)\n");
		} else {
		echo "Socket Server started and listening on " . $serverstring . "\r\n"; 
		}
	}

//	Websocket Handshake
function handshake($connect) {
    $info = array();

    $line = fgets($connect);
    $header = explode(' ', $line);
	if((!array_key_exists(1, $header)) || (!array_key_exists(0, $header))) {
		return false;
		}
    $info['method'] = $header[0];
    $info['uri'] = $header[1];

    while ($line = rtrim(fgets($connect))) {
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $info[$matches[1]] = $matches[2];
        } else {
            break;
        }
    }

    $address = explode(':', stream_socket_get_name($connect, true));
    $info['ip'] = $address[0];
    $info['port'] = $address[1];
	dump($info);
    if (empty($info['Sec-WebSocket-Key'])) {
        return false;
		}
/*  if(!$info['origin'] == "http://localhost") {
		echo "Intrusion attempt<BR />";
		return false;
		} */

    $SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept:$SecWebSocketAccept\r\n\r\n";
    fwrite($connect, $upgrade);

    return $info;
	}

//	Websocket encoding to send string to all connected clients
function encode($payload, $type = 'text', $masked = false) {
    $frameHead = array();
    $payloadLength = strlen($payload);

    switch ($type) {
        case 'text':
            // first byte indicates FIN, Text-Frame (10000001):
            $frameHead[0] = 129;
            break;

        case 'close':
            // first byte indicates FIN, Close Frame(10001000):
            $frameHead[0] = 136;
            break;

        case 'ping':
            // first byte indicates FIN, Ping frame (10001001):
            $frameHead[0] = 137;
            break;

        case 'pong':
            // first byte indicates FIN, Pong frame (10001010):
            $frameHead[0] = 138;
            break;
    }

    // set mask and payload length (using 1, 3 or 9 bytes)
    if ($payloadLength > 65535) {
        $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
        $frameHead[1] = ($masked === true) ? 255 : 127;
        for ($i = 0; $i < 8; $i++) {
            $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
        }
        // most significant bit MUST be 0
        if ($frameHead[2] > 127) {
            return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
        }
    } elseif ($payloadLength > 125) {
        $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
        $frameHead[1] = ($masked === true) ? 254 : 126;
        $frameHead[2] = bindec($payloadLengthBin[0]);
        $frameHead[3] = bindec($payloadLengthBin[1]);
    } else {
        $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
    }

    // convert frame-head to string:
    foreach (array_keys($frameHead) as $i) {
        $frameHead[$i] = chr($frameHead[$i]);
    }
    if ($masked === true) {
        // generate a random mask:
        $mask = array();
        for ($i = 0; $i < 4; $i++) {
            $mask[$i] = chr(rand(0, 255));
        }

        $frameHead = array_merge($frameHead, $mask);
    }
    $frame = implode('', $frameHead);

    // append payload to frame:
    for ($i = 0; $i < $payloadLength; $i++) {
        $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
    }

    return $frame;
	}

//	Websocket compliant decode of incoming string
function decode($data) {
    $unmaskedPayload = '';
    $decodedData = array();
    // estimate frame type:
	if(strlen($data) == 0) {return;}
    $firstByteBinary = sprintf('%08b', ord($data[0]));
    $secondByteBinary = sprintf('%08b', ord($data[1]));
    $opcode = bindec(substr($firstByteBinary, 4, 4));
    $isMasked = ($secondByteBinary[0] == '1') ? true : false;
    $payloadLength = ord($data[1]) &127;

    // unmasked frame is received:
    if (!$isMasked) {
        return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
		}

    switch ($opcode) {
        // text frame:
        case 1:
            $decodedData['type'] = 'text';
            break;

        case 2:
            $decodedData['type'] = 'binary';
            break;

        // connection close frame:
        case 8:
            $decodedData['type'] = 'close';
            break;

        // ping frame:
        case 9:
            $decodedData['type'] = 'ping';
            break;

        // pong frame:
        case 10:
            $decodedData['type'] = 'pong';
            break;

        default:
            return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
		}

    if ($payloadLength === 126) {
        $mask = substr($data, 4, 4);
        $payloadOffset = 8;
        $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
        $mask = substr($data, 10, 4);
        $payloadOffset = 14;
        $tmp = '';
        for ($i = 0; $i < 8; $i++) {
            $tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
        $dataLength = bindec($tmp) + $payloadOffset;
        unset($tmp);
		} else {
        $mask = substr($data, 2, 4);
        $payloadOffset = 6;
        $dataLength = $payloadLength + $payloadOffset;
		}

    if (strlen($data) < $dataLength) {
        return false;
		}

    if ($isMasked) {
        for ($i = $payloadOffset; $i < $dataLength; $i++) {
            $j = $i - $payloadOffset;
            if (isset($data[$i])) {
                $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
        $decodedData['payload'] = $unmaskedPayload;
		} else {
        $payloadOffset = $payloadOffset - 4;
        $decodedData['payload'] = substr($data, $payloadOffset);
		}
	return $decodedData;
	}

//	Handle an incoming message.
function onMessage($connect, $data) {
	global $client_socks;
	$current_users = count($client_socks);
	$temp = decode($data);
	$sendstring = $temp['payload'];
	$elements = explode("/", $sendstring);
	if(!array_key_exists(1,$elements)) {return;}
	$userid = $elements[0];
	$isAdmin = checkAdmin($userid);
	$textElement = explode(":", $elements[1]);
	if(array_key_exists(1, $textElement)) {
		$theMessage = substr($textElement[1], 1);
		} else {
		$theMessage = $textElement[0];
		}
	switch($elements[2]) {
		case 1:
		$theUser = 
		$messagelog = "0/" . $elements[1] . "/299";
		foreach($client_socks as $theClient) {
			$writeReturn = fwrite($theClient, encode($messagelog));
			}
		break;
		
		default:
		break;
		}

	if($isAdmin) {
		switch($theMessage) {
			case "close server":
				echo "Shutting down server\r\n";
				do_shutdown($sendstring);
				return;
				break;
			case "restart server":
				echo "Restarting server\r\n";
				do_restart($sendstring);
				return;
				break;
			}
		} else {
		switch($theMessage) {
			case "close server":
			case "restart server":
				return;
				break;
			}			
		}
	if($current_users < 2) {
		if(array_key_exists(2, $elements)) {
			switch($elements[2]) {
				case 96:
					$sendstring = "0/" . $current_users . "/97";
					$writeReturn = fwrite($connect, encode($sendstring));
					break;
				case 95:
					break;
				case 40:
					$writeReturn = fwrite($connect, encode($sendstring));
					break;
				case 0:
					$sendstring = "0/There are no other users connected, message will not be sent/1";
					$writeReturn = fwrite($connect, encode($sendstring));
					break;
				default:
					$writeReturn = fwrite($connect, encode($sendstring));
					break;			
				}
			}
		} else {	//	Normal situation, more than 1 user connected
		if(array_key_exists(2, $elements)) {
			switch($elements[2]) {
				case 96:
					foreach($client_socks as $theClient) {
						$sendstring = "0/" . $current_users . "/97";
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;
				case 95:
					$theUsers = "";
					foreach($client_socks as $theClient) {						
						$theUsers .= stream_socket_get_name($theClient, true) . "\r\n";
						}
					foreach($client_socks as $theClient) {							
						$sendstring = "0/" . $theUsers . "/94";
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;
				case 40:
					foreach($client_socks as $theClient) {						
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;
				default:
					foreach($client_socks as $theClient) {	
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;			
				}
			}
		}
	}
// End of Core Server Functions

// startup check - stops multiple instances of server
$report = server_report();
if($report['TICKETS SOCKET SERVER'] == "1") {
	echo 2;
	exit();
	}
	
// Attempt to start server	

$server = stream_socket_server("{$serverstring}", $errno, $errstr);

//	Check if server started.

if ($server === false) {
	echo 99;
	sleep(2);
	echo "\r\nServer Failed to start\r\n";
	} else {
	echo 1;
	sleep(2);
	echo "\r\nServer Started OK\r\n";
	}

// declare array for monitoring sockets
$client_socks = array();

//	Main While loop, continues from when there is a first connection until there are no connections. 
while(true) {
    //prepare readable sockets
    $read_socks = $client_socks;
    $read_socks[] = $server;
		
    //start reading and use a large timeout
    if(!stream_select ( $read_socks, $write, $except, 300000 )) {
		die('something went wrong while selecting');
		}
		
//new client
    if(in_array($server, $read_socks)) {
        $new_client = stream_socket_accept($server);
		$current_users = count($client_socks);
		if(($new_client) && ($info = handshake($new_client))) {
			dump($info);
            //print remote client information, ip and port number
            echo "Connection accepted from " . stream_socket_get_name($new_client, true) . "\n";
            $client_socks[] = $new_client;
            echo "Now there are total ". count($client_socks) . " clients.\n";
			switch($current_users) {
				case 0;
					sleep(20);
					foreach($client_socks as $theClient) {
						$sendstring = "0/1/97";
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;
				default:
					foreach($client_socks as $theClient) {
						$sendstring = "0/" . $current_users . "/97";
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;
				}
			}
        //delete the server socket from the read sockets
        unset($read_socks[ array_search($server, $read_socks) ]);
		}
	
//message from existing client
	foreach($read_socks as $key=>$sock) {
		$data = fread($sock, 8000);
		if(!$data){
			unset($client_socks[ array_search($sock, $client_socks) ]);
			@fclose($sock);
			echo "A client disconnected. Now there are total " . count($client_socks) . " clients.\n";
			sleep(2);
			$theCount = count($client_socks);
			switch($theCount) {
				case 0:
					break;
				default:
					foreach($client_socks as $theClient) {
						$sendstring = "0/" . count($client_socks) . "/97";
						$writeReturn = fwrite($theClient, encode($sendstring));
						}
					break;						
				}
			} else {
			onMessage($sock, $data);
			}
		}
	}
exit();
?>
