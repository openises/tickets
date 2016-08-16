<?php
function check_port($port) {
    $conn = @fsockopen("127.0.0.1", $port, $errno, $errstr, 0.2);
    if ($conn) {
        fclose($conn);
        return true;
    }
}

function server_report() {
    $report = array();
    $svcs = array('21'=>'FTP',
                  '22'=>'SSH',
                  '25'=>'SMTP',
                  '80'=>'HTTP',
                  '110'=>'POP3',
                  '143'=>'IMAP',
                  '3306'=>'MySQL');
    foreach ($svcs as $port=>$service) {
        $report[$service] = check_port($port);
    }
    return $report;
}

function check_other($port) {
	$ret = check_port($port);
	return $ret;
	}

$report = server_report();
$other = (check_other($_GET['port']) == 1) ? "Open" : "Closed";
?>
<table>
    <tr>
        <td>Service</td>
        <td>Status</td>
    </tr>
    <tr>
        <td>FTP</td>
        <td><?php echo $report['FTP'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td>SSH</td>
        <td><?php echo $report['SSH'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td>SMTP</td>
        <td><?php echo $report['SMTP'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td>HTTP</td>
        <td><?php echo $report['HTTP'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td>POP3</td>
        <td><?php echo $report['POP3'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td>IMAP</td>
        <td><?php echo $report['IMAP'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td>MySQL</td>
        <td><?php echo $report['MySQL'] ? "Online" : "Offline"; ?></td>
    </tr>
    <tr>
        <td><?php echo $_GET['port'];?></td>
        <td><?php echo $other;?></td>
    </tr>
</table>