<?php
require_once __DIR__ . '/functions.inc.php';
require_login_or_401();
/*
1/11/10 initial release - updates user cookie expiration
*/
error_reporting(E_ALL);

require_once($fip);        //7/28/10
set_sess_exp();
print "";
?>
