<?php
require_once '../incs/functions.inc.php';
require_login_or_401();
$theTerm = sanitize_string($_GET['q']);
$theAnswer = get_text($theTerm);
print json_encode($theAnswer);
exit();
?>