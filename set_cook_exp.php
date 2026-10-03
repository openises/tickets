<?php
/*
1/11/10 initial release - updates user cookie expiration
*/
error_reporting(E_ALL);

require_once './incs/functions.inc.php';
require_login_or_401();
set_sess_exp();
print "";
?>
