<?php 
/*
3/15/11 changed stylesheet.php to stylesheet.php
*/

@session_start();
require_once($_SESSION['fip']);		//7/28/10
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - External Link Page</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->

<BODY>
<?php
print "<iframe frameborder = 0 src =\"http://" . get_variable('link_url') . "\" width='100%' height='800px' >";
?>
</iframe>
<!-- http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=115428078694996810842.000001133c4a5be90f83c&om=1&ll=41.335576,-82.250519&spn=0.902239,1.724854&z=10 -->
</BODY>
</HTML>
