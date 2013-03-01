<?php
/*
*/
error_reporting(E_ALL);	
	function dump ($variable) {
		echo "\n<PRE>";
		var_dump ($variable) ;
		echo "</PRE>\n";
		}
$a = 'a:3:{s:27:"K3RGB-152010-11-17 20:48:03";b:1;s:25:"WJ3K-72010-11-17 20:18:59";b:1;s:25:"KV3SPA2010-11-20 22:55:18";b:1;}';
$b = "WJ3K-72010-11-20 19:46:23";

dump(unserialize($a));
dump($b);

?>
