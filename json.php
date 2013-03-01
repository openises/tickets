
<?php
/*
*/
error_reporting(E_ALL);	
	function dump ($variable) {
		echo "\n<PRE>";
		var_dump ($variable) ;
		echo "</PRE>\n";
		}
$x = '["0","1713","50","2009-09-22 13:40:20","2011-06-16 18:09:56","703e98024ddb2c1c250e9051109892ae"]';
dump (json_decode($x)) ;
?>


