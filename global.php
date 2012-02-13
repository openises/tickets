<?php
/*
*/
error_reporting(E_ALL);	
$a = 1;
function x () {
	$b = 2;

	function y() {
		global $a;
		global $b;
		echo $a;	
		echo $b;	
		}	//  end y
	}		// end x
	
	

?>
