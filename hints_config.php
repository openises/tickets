<?php
/*
3/15/11 added css color tables configuration capability
*/
	if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09 
	error_reporting (E_ALL  ^ E_DEPRECATED);
	session_start();
	session_write_close();
	require_once('./incs/functions.inc.php');
	do_login(basename(__FILE__));	// session_start()

	if(isset($_GET['func'])) {
		$func = $_GET['func'];
	} else {
		$func = "group";
	}
	
	require_once('./incs/config.inc.php');
	require_once('./incs/usng.inc.php');				// 9/16/08
	
 	$query	= "SELECT `user` FROM `$GLOBALS[mysql_prefix]user` WHERE `id` <> '{$_SESSION['user_id']}'";		// 12/2/08
 	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$users = "";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$users .= trim($row['user']) . "\t";			
		}			
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Hints Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<STYLE>
	LI { margin-left: 20px;}
	.spl { FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}

#bar 		{ width: auto; height: auto; background:transparent; z-index: 100; } 
* html #bar { /*\*/position: absolute; top: expression((60 + (ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop)) + 'px'); right: expression((320 + (ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft)) + 'px');/**/ }
#foo > #bar { position: fixed; top: 60px; right: 320px; }

	</STYLE>
	<SCRIPT SRC='./js/md5.js'></SCRIPT>				<!-- 11/30/08 -->
	<SCRIPT SRC='./js/jscoord.js'></SCRIPT>		<!-- coordinate conversion 12/4/10 -->	
	<SCRIPT SRC="./js/jscolor/jscolor.js"></SCRIPT>				<!-- 01/24/11 -->

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
	
	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
<?php
	// if (intval(get_variable('call_board')) == 0) {						// hide the button - 4/10/10
		// print "\t parent.frames['upper'].document.getElementById('call').style.display = 'none';";
		// }
?>		
	function ck_frames() {
<?php
	// if ($mode==1) {											// 9/8/10
		// print "return;\n";
		// }
//	else {
?>	
//		if(self.location.href==parent.location.href) {
//			self.location.href = 'index.php';
//			}
//		else {
//			parent.upper.show_butts();										// 1/21/09
//			}
<?php
//		}
?>		
		}		// end function ck_frames()
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	String.prototype.trim = function () {				// 10/19/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
     
	function in_array (ary, val) {						// 12/2/08
		for (var i = 0; i<ary.length; i++) {
			if(ary[i] == val) {
				return true;
				}
			}
		return false;
		}				// end function in array

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && document.getElementById) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}
	
	function whatBrows() {					//Displays the generic browser type
		window.alert("Browser is : " + type);
		}
	
	function ShowLayer(id, action){												// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("document.getElementById('" + id + "').style.display='" + action + "'");
		}
	
	function hideit (elid) {
		ShowLayer(elid, "none");
		}
	
	function showit (elid) {
		ShowLayer(elid, "block");
		}

	function validate_set(theForm) {			// limited form contents validation  
		var errmsg="";
		if (theForm.gmaps_api_key.value.length!=86)			{errmsg+= "\tInvalid GMaps API key\n";}
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			return true;
			}
		}				// end function validate set(theForm)
		
<?php
print "// file as of " . date("l, dS F, Y @ h:ia", filemtime(basename(__FILE__))) . "\n";
print "//" . date("n/j/y", filemtime(basename(__FILE__))) . "\n";
?>
	</SCRIPT>
	

<?php

	if (array_key_exists('func', ($_REQUEST))) {				// 11/11/10
		switch ($func){
	
		case 'hints' :
			if((isset($_GET))&& (isset($_GET['go']))&& ($_GET['go'] == 'true')) {
			dump($_POST);
				print "</HEAD>\n<BODY onLoad = 'ck_frames(); '>\n";		// 1/23/10
//				foreach ($_POST as $VarName=>$VarValue) {
//					$query = "UPDATE `$GLOBALS[mysql_prefix]hints` SET `value`=". quote_smart($VarValue)." WHERE `name`='".$VarName."', `group`=". quote_smart($VarValue)." WHERE `name`='".$VarName."'";
//					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//					print $VarName;
//					}
				print '<FONT CLASS="header">Hints saved</FONT>.</FONT><BR /><BR />';
				}
			else {
				print "</HEAD>\n<BODY onLoad = 'ck_frames();'>\n";		// 9/21/08
				$evenodd = array ("even", "odd");
				if((isset($_POST['hints_group'])) && ($_POST['hints_group'] != "All")) {
					$group = $_POST['hints_group'];
					$where = "WHERE `$GLOBALS[mysql_prefix]hints`.`group` = '{$group}' ORDER BY `tag` ASC";
//					dump($_POST);
				} else {
					$where = "ORDER BY `group` ASC";
//					dump($_POST);					
				}
				
			?>
			<DIV ID='to_bottom' style="position:fixed; top:4px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 /></div>
			<A NAME="top" /> <!-- 11/11/09 -->

			<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]hints` {$where}";	
//				dump($query);
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$i = 1;
				print "\n<FORM NAME='hints_Form' METHOD = 'post' onSubmit='return validate_set(document.hints_Form);' ACTION='hints_config.php?func=hints&go=true'>
					<table border=0 STYLE = 'MARGIN-LEFT:100PX'>\n";
				print "\n<INPUT TYPE='hidden' NAME='func' VALUE='hints_update' />\n";
				print "\n<TR><TH COLSPAN=2>Hover over hints - enter revisions</TH></TR>\n";
				$dis = ((is_administrator()) || (is_super()))? "": " DISABLED ";				// 3/19/11
				while ($row =  stripslashes_deep(mysql_fetch_array($result))) {
					print "<TR CLASS = {$colors[$i%2]} VALIGN='middle'><TD><INPUT SIZE='10' TYPE='text' NAME='{$row['group']}' VALUE='{$row['group']}' MAXLENGTH='24'></TD></TD><TD><BR />" . substr($row['tag'], 1) . "</TD>
						<TD><TEXTAREA COLS = 120 ROWS=1 NAME = '{$row['tag']}' {$dis}>" . trim($row['hint']) . "</TEXTAREA></TD></TR>\n";
					$i++;	
					}
				print "\n\t\t</FORM></TABLE>";
			?>
			<A NAME="bottom" /> <!-- 11/11/09 -->
			<IMG SRC="markers/up.png" BORDER=0  onclick = "location.href = '#top';" STYLE = 'margin-left: 20px'></TD>

				<DIV ID="foo"><DIV ID="bar">		<!-- 9/26/09 -->
					<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'><BR /><BR />
					<INPUT TYPE='button' VALUE='Reset form'  onClick='document.hints_Form.reset();'><BR /><BR />
					<INPUT TYPE='button' VALUE='Apply changes'  onClick='document.hints_Form.submit();'>
				</DIV></DIV>
		
				<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>		
				</BODY>
				<SCRIPT>
					try {
						parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
						parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
						parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
						}
					catch(e) {
						}
				</SCRIPT>
				</HTML>
	<?php
				exit();
				}				// end else
			break;

			default:
				dump ("ERROR " . __LINE__);
			}						// end switch ($func)
		
		}				// end if (array_key_exists('func', ($_REQUEST)))
?>
<STYLE>
ul {  
  font-family: Arial, Helvetica, sans-serif; 
  font-size: 10px; color: #0F143F; 
  list-style-type: none;
}
</STYLE>
		</HEAD>
	<BODY onLoad = 'ck_frames()'> <!-- 11/13/10 -->
<?php if (isset($top_notice)) print "<SPAN STYLE='margin-left: 100px;' CLASS='header' >{$top_notice}</SPAN><BR /><BR />"; ?>
	
	<FORM NAME='hints_select' METHOD = 'post' ACTION='hints_config.php?func=hints'>
	<BR /><BR /><BR />
	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS='heading'><TH COLSPAN=99>Hints Grouping</TH></TR>	
	<TR CLASS="odd"><TD>Select hints group to show</TD><TD>
	<SELECT NAME='hints_group'>	<!--  11/17/10 -->
	<OPTION VALUE="All" SELECTED>All</OPTION>
<?php
	$query = "SELECT DISTINCT `$GLOBALS[mysql_prefix]hints`.`group`	FROM `$GLOBALS[mysql_prefix]hints` ORDER BY `$GLOBALS[mysql_prefix]hints`.`group` ASC";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		print "\t<OPTION VALUE='{$row['group']}'>{$row['group']}</OPTION>\n";
		}	?>
	</SELECT></TD></TR>
	<TR CLASS="spacer></TR>
	<TR CLASS="even"><TD COLSPAN="2"><INPUT TYPE='button' VALUE='Submit'  onClick='document.hints_select.submit();'></TD></TR>
	</TABLE>
	<INPUT TYPE='hidden' NAME='func' VALUE='show_hints'>
	</FORM>
	
	<DIV ID="foo"><DIV ID="bar">		<!-- 9/26/09 -->

	</DIV></DIV>	
</BODY>
</HTML>
