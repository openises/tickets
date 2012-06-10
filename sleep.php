<?php
	var_dump(time());
	var_dump(microtime(true));

//returns false and generates a warning
//var_dump(time_sleep_until(time()-1));
//while (TRUE) {
// may only work on faster computers, will sleep up to 0.2 seconds
	var_dump(time_sleep_until(microtime(true)+10.2));
//	}

?>
