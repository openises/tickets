<?php
error_reporting(E_ALL);		// E_ALL

session_start();
session_write_close();	
require_once('incs/functions.inc.php');	
do_login(basename(__FILE__));
require_once('./forms/mobile_screen.php');
?>
<DIV ID='to_bottom' style="position:fixed; bottom:50px; left:150px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" BORDER=0 /></div>

</BODY>

</HTML>
