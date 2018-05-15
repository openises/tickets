<?php
/*
test_txtlocal.php - Test retrieve messages for TXTLOCAL SMS Gateway
09/22/16 - new file
*/
require_once('./incs/functions.inc.php');
error_reporting(E_ALL);
set_time_limit(0);
@session_start();
session_write_close();

define('HTTPD_CONF', '/etc/httpd/httpd.conf');

$lines = file_get_contents(HTTPD_CONF);
if($lines) {print "File Found<BR />";} else {print "File Not Found<BR />";}
$config = array();
dump($lines);
foreach ($lines as $l) {
	print $l . "<BR />";
    preg_match("/^(?P<key>\w+)\s+(?P<value>.*)/", $l, $matches);
    if (isset($matches['key'])) {
        $config[$matches['key']] = $matches['value'];
    }
}

dump($config);

exit();
?>	
