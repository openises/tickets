<?php
$link = mysql_connect('localhost', 'mysql_user', 'mysql_password');

// this connect uses the default port number, 3306;\
// if the Mysql install uses some other port number, say 3307, then the syntax is:
// 'localhost:3307'

if (!$link) {
    die('Could not connect: ' . mysql_error());
	}
echo 'Connected successfully';
mysql_close($link);
?> 