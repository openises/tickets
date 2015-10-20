<?php
/*
any comments go here
*/
error_reporting(E_ALL);	
function dump($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "</PRE>\n";
	}

dump ($_POST);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tom's test</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<SCRIPT>
</SCRIPT>
</HEAD>
<BODY>
<?php if (empty($_POST)) { ?>
<h3>First pass</h3>
<?php 
	}
else {	?>
<h1>Second pass</h1>
<?php } ?>

<form name="my_form"  method = post action = '<?php print basename(__FILE__); ?>'> <br />
<?php $the_value = (empty($_POST))? "" : $_POST['my_field_1']; ?>
field 1 <input type = text name = 'my_field_1'  value = "<?php print $the_value; ?>" size = 6 /> <br />

<?php $the_value = (empty($_POST))? "" : $_POST['my_field_2']; ?>
field 2 <input type = text name = 'my_field_2'  value = "<?php print $the_value; ?>"  size = 8 /> <br />

<?php $the_value = (empty($_POST))? "" : $_POST['my_field_3']; ?>
field 3 <input type = text name = 'my_field_3'  value = "<?php print $the_value; ?>"  size = 12 /> <br />

<input type = hidden name = 'my_field_4'  value =  'any old stuff here' />
<input type = submit>
<input type = reset>
</form>

</BODY>
</HTML>
