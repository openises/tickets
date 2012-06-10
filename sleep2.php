<?php

while (TRUE) {
// may only work on faster computers, will sleep up to 0.2 seconds
	$next = microtime()+60.0;
	time_sleep_until($next);
	echo date("H:i", time());
	}

?>
