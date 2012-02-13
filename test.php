<?php
/*
*/
	function dump ($variable) {
		echo "\n<PRE>";
		var_dump ($variable) ;
		echo "</PRE>\n";
		}

error_reporting(E_ALL);	

if (empty($_GET)) {
	$temp = session_start();
	$_SESSION['xx'] = $temp;		 
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = dirname($_SERVER['PHP_SELF']);
	$extra = basename(__FILE__) . "?aa={$temp}";				// 8/29/10
	header("Location: http://{$host}/{$uri}/{$extra}");	
	}
else {
	$temp = session_start();
	dump($_SESSION);
	}
?>
