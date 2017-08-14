<?php
?>
<html>
<body>
<script type="application/x-javascript"><!--
function do_Post(the_table) {
	document.tables.tablename.value=the_table;
	document.tables.submit();
	}
setTimeout('Redirect()',1000);
function Redirect()
{
  do_Post('roadinfo');
}
// --></script>
<FORM NAME='tables' METHOD = 'post' ACTION='tables.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='r'>
<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
</FORM>
</body>
</html>