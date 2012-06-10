<?php
	if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09 
	error_reporting (E_ALL  ^ E_DEPRECATED);
//	session_start();	
	require_once('./incs/functions.inc.php');
//	$istest = TRUE;
	if ($istest) {
		foreach ($_POST as $VarName=>$VarValue) 	{echo "POST:$VarName => $VarValue, <BR />";};
		foreach ($_GET as $VarName=>$VarValue) 		{echo "GET:$VarName => $VarValue, <BR />";};
		echo "<BR/>";
		}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css">			<!-- 3/15/11 -->
	<STYLE>
	LI { margin-left: 20px;}
	.spl { FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}

	</STYLE>
	<SCRIPT>
	function $() {									// 7/11/10
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}
	
</SCRIPT>

<?php
	if (array_key_exists('func', ($_GET))) {				// 11/11/10
		extract ($_GET);
		switch ($func) {
	
	case 'dump' :				// see mysql.inc.php	for MySQL parameters
		require_once('./incs/MySQLDump.class.php');
		$backup = new MySQLDump(); //create new instance of MySQLDump
		
		$the_db = $mysql_prefix . $mysql_db;
		$backup->connect($mysql_host,$mysql_user,$mysql_passwd,$the_db);		// connect
		if (!$backup->connected) { die('Error: '.$backup->mysql_error); } 		// MySQL parameters from mysql.inc.php
		$backup->list_tables(); 												// list all tables
		$broj = count($backup->tables); 										// count all tables, $backup->tables 
																				//   will be array of table names
?>
	<SCRIPT>
	function copyit() {						// 11/30/09
		var tempval= document.the_form.the_dump;
		tempval.focus();
		tempval.select();
		therange=tempval.createTextRange();
		therange.execCommand("Copy");
		}
	//  End -->
	</SCRIPT>
	
<?php
		$_echo ="\n\n-- start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start \n";
		$_echo .="\n-- Dumping tables for database: $mysql_db\n"; //write "intro" ;)
		
		for ($i=0;$i<$broj;$i++) {						//dump all tables:
			$table_name = $backup->tables[$i]; 			//get table name
			$backup->dump_table($table_name); 			//dump it to output (buffer)
			$_echo .=htmlspecialchars($backup->output); 	//write output
			}
		$_echo .="\n\n-- end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end \n";
		
		echo "\n<FORM NAME='the_form'><TEXTAREA NAME ='the_dump' COLS=120 ROWS=20>{$_echo}</TEXTAREA>";
		echo "<BR /><BR /><INPUT onclick='copyit()' type='button' value='Click to copy the dump' name='cpy'\>\n</FORM>\n";
	
		break;
	    

			default:
			dump ("ERROR " . __LINE__);
		}						// end switch ($func)
	
	}				// end if (array_key_exists('func', ($_REQUEST)))
?>
		</HEAD>
	<BODY> <CENTER>
		<TABLE BORDER=0><TR>
				<TD><BR /><BR /><BR /><LI><A HREF="<?php print basename(__FILE__);?>?func=dump">Click to <U>dump DB</U> to screen</A></TD>
				</TR>
			</TABLE>
</BODY>
	
</HTML>
