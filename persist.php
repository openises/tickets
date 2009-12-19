<?php
/*
2/14/09 initial release of session persistence processor - sets flag n (POST['f_n']) to given value (POST['v_n'])
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');

//if($istest) {
//	dump ($_GET);
//	dump ($_POST);
//	}

$f_n = 		$_POST['f_n'];
$v_n = 		$_POST['v_n']; 
$sess_id = 	$_POST['sess_id'];				// sess_id

$query = "UPDATE `$GLOBALS[mysql_prefix]session` SET `$f_n` ='$v_n' WHERE `sess_id`='$sess_id' LIMIT 1";
//snap(__LINE__, $query);
$result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
print"";
?>