<?php

@session_start();
session_write_close();
require_once(isset($_SESSION['fip']) ? $_SESSION['fip'] : './incs/functions.inc.php');        //7/28/10
require_login_or_401();
do_aprs();
?>
