<?php
function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

/* foreach example 3: key and value */

$a = array( "one" => 1, "two" => 2, "three" => 3, "seventeen" => 17	);
$b = array( "aaa" => 1, "bbb" => 2, "ccc" => 3, "dddd" => 17	);

$c = array ($a,$a );
//dump ($c);

foreach ($c as $outerkey => $outervalue) {
	if (is_array($outervalue)) {
		foreach ($outervalue as $innerkey => $innervalue) {
			print "innerkey: .  $innerkey  \n";
			print "innervalue: . $innervalue  \n";	
			}
		}
	else {
		print "outerkey: . $outerkey \n";
		}
//	dump (is_array($key));
//	dump (is_array($value));
	}

?>