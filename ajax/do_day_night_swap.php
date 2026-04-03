<?php
/*
5/4/11 initial release
*/
error_reporting(E_ALL);
session_start();
$_SESSION['day_night'] = ($_SESSION['day_night']=="Day")? "Night" : "Day";    // swap
session_write_close();
echo htmlspecialchars($_SESSION['day_night'], ENT_QUOTES, 'UTF-8');    // XSS-safe output (inline — no functions.inc.php dependency)
?>
