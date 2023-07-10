<?php
ini_set('memory_limit', '5120M');
set_time_limit ( 0 );
error_reporting(E_ALL);
require_once('./incs/mysql.inc.php');
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
$dir = getcwd() . "/backups";
if( !extension_loaded('mysql') ){
	require_once('./incs/mysql2i.class.php');
	}

$css = (!empty($_POST) && array_key_exists('css', $_POST)) ? $_POST['css'] : array();
$css_count = count($css);

if(empty($_POST)) {
	$connect = mysql_connect($mysql_host, $mysql_user, $mysql_passwd);
	$db_selected = mysql_select_db($mysql_db);
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]css_day`";
	$result = mysql_query($query);
	if($result) {
		while ($row = mysql_fetch_assoc($result)){
			if($row['name'] == "page_background") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "normal_text") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "form_input_background") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "form_input_text") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "row_light") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "row_dark") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "select_menu_background") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "select_menu_text") {$css[$row['name']] = "#" . $row['value'];}
			if($row['name'] == "links") {$css[$row['name']] = "#" . $row['value'];}
			}
		$mysqlclosed = mysql_close();
		} else {
		$css['page_background'] = "#CECECE";
		$css['normal_text'] = "#000000";
		$css['form_input_background'] = "#FFFFFF";
		$css['form_input_text'] = "#000000";
		$css['row_light'] = "#DEE3E7";
		$css['row_dark'] = "#EFEFEF";
		$css['select_menu_background'] = "#FFFFFF";
		$css['select_menu_text'] = "#000000";
		$css['links'] = "#000099";
		}
	extract($css);
	} else {
	$page_background = (array_key_exists('page_background', $_POST)) ? $_POST['page_background'] : "#CECECE";
	$normal_text = (array_key_exists('normal_text', $_POST)) ? $_POST['normal_text'] : "#000000";
	$form_input_background = (array_key_exists('form_input_background', $_POST)) ? $_POST['form_input_background'] : "#FFFFFF";
	$form_input_text = (array_key_exists('form_input_text', $_POST)) ? $_POST['form_input_text'] : "#000000";
	$row_light = (array_key_exists('row_light', $_POST)) ? $_POST['row_light'] : "#DEE3E7";
	$row_dark = (array_key_exists('row_dark', $_POST)) ? $_POST['row_dark'] : "#EFEFEF";
	$select_menu_background = (array_key_exists('select_menu_background', $_POST)) ? $_POST['select_menu_background'] : "#FFFFFF";
	$select_menu_text = (array_key_exists('select_menu_text', $_POST)) ? $_POST['select_menu_text'] : "#000000";
	$links = (array_key_exists('links', $_POST)) ? $_POST['links'] : "#000099";
	}

$directory_separator = DIRECTORY_SEPARATOR;
$curr_dir = getcwd();
$backupdir = $curr_dir . DIRECTORY_SEPARATOR . "backups";
if (!(file_exists($backupdir))) {		
	if(!(mkdir($backupdir))) {
		print "Backup Directory doesn't exist and cannot be created. Please contact the developer<BR />"; 
		exit();
		}
	}
	
function get_css($element, $day_night){
	global $css;
	return (array_key_exists($element, $css))? "#" . $css[$element] : FALSE ;
	}
	
function dump($variable) {
	echo "\n<PRE>\n";				// pretty it a bit - 2/23/2013
	var_dump($variable) ;
	echo "</PRE>\n";
	}

$variables = array();
function get_variable($which){
	global $variables, $mysql_host, $mysql_user, $mysql_passwd, $mysql_db;
	$connect = mysql_connect($mysql_host, $mysql_user, $mysql_passwd);
	$db_selected = mysql_select_db($mysql_db);
	if (empty($variables)) {
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]settings`");
		if($result) {
			while ($row = mysql_fetch_assoc($result)) {
				$name = $row['name']; $value=$row['value'] ;
				$variables[$name] = $value;
				}
			} else {
			return FALSE;
			}
		}
	$mysqlclosed = mysql_close();
	return (array_key_exists($which, $variables))? $variables[$which] : FALSE ;
	}
	
function get_backups() {
	global $dir;
	$backups = array();
	$theRet = "";
	$theRet .= "<SELECT NAME='db_schema'>";
	$theRet .= "\t<OPTION VALUE='0'>Select One</OPTION>";
	if ($handle = opendir($dir)) {
		while (false !== ($filename = readdir($handle))) {
			if ($filename != "." && $filename != "..") {
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				if($ext == "sql" || $ext == "SQL") {
					$theRet .= "\t<OPTION VALUE='" . $filename . "'>" . $filename . "</OPTION>";
					}
				}
			}
		}
	$theRet .= "</SELECT>";
	return $theRet;
	}
	
function get_tables() {
	$tableList = array();
	$res = mysqli_query($this->conn,"SHOW TABLES");
	while($cRow = mysqli_fetch_array($res)) {
		$tableList[] = $cRow[0];
		}
	return $tableList;
	}
	
function remove_comments(&$output) {
	$lines = explode("\n", $output);
	$output = "";
	$linecount = count($lines);
	$in_comment = false;
	for($i = 0; $i < $linecount; $i++) {
		if(preg_match("/^\/\*/", preg_quote($lines[$i]))) {
			$in_comment = true;
			}
		if(!$in_comment) {
			$output .= $lines[$i] . "\n";
			}
		if(preg_match("/\*\/$/", preg_quote($lines[$i]))) {
			$in_comment = false;
			}
		}
	unset($lines);
	return $output;
	}

function remove_remarks($sql) {
	$lines = explode("\n", $sql);
	$sql = "";
	$linecount = count($lines);
	$output = "";
	for ($i = 0; $i < $linecount; $i++)	{
		if(($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) {
			if (isset($lines[$i][0]) && $lines[$i][0] != "#") {
				$output .= $lines[$i] . "\n";
				} else {
				$output .= "\n";
				}
			$lines[$i] = "";
			}
		}
	return $output;
	}

function split_sql_file($sql, $delimiter) {
	$tokens = explode($delimiter, $sql);
	$sql = "";
	$output = array();
	$matches = array();
	$token_count = count($tokens);
	for ($i = 0; $i < $token_count; $i++) {
		if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
			$unescaped_quotes = $total_quotes - $escaped_quotes;
			if (($unescaped_quotes % 2) == 0) {
				$output[] = $tokens[$i];
				$tokens[$i] = "";
				} else {
				$temp = $tokens[$i] . $delimiter;
				$tokens[$i] = "";
				$complete_stmt = false;
				for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
					$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
					$unescaped_quotes = $total_quotes - $escaped_quotes;
					if (($unescaped_quotes % 2) == 1) {
						$output[] = $temp . $tokens[$j];
						$tokens[$j] = "";
						$temp = "";
						$complete_stmt = true;
						$i = $j;
						} else {
						$temp .= $tokens[$j] . $delimiter;
						$tokens[$j] = "";
						}
					}
				}
			}
		}
	return $output;
	}
	
function dump_db($host,$user,$passwd,$db,$prefix) {
	require_once('./incs/MySQLDump.class.php');
	$delta = (get_variable('delta_mins')) ? get_variable('delta_mins') : 0;
	$the_now = time() - ($delta * 60);
	$locale = (get_variable('locale')) ? get_variable('locale') : 0;
	if(intval($locale == 0)) {
		$now = date('mdy', $the_now);
		} else {
		$now = date('dmy', $the_now);			
		}
	$hourmin = date('Hi', $the_now);
	$backup = new MySQLDump(); //create new instance of MySQLDump
	$the_db = $prefix . $db;
	$backup->connect($host,$user,$passwd,$db);		// connect
	if (!$backup->connected) { die('Error: '.$backup->mysql_error); } 		// MySQL parameters from mysql.inc.php
	$backup->list_tables(); 												// list all tables
	$broj = count($backup->tables); 										// count all tables, $backup->tables 
																			//   will be array of table names
	$the_db_dump ="\n\n-- start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start \n";
	$the_db_dump .="\n-- Dumping tables for database: $db\n"; //write "intro" ;)
	
	for ($i=0;$i<$broj;$i++) {						//dump all tables:
		$table_name = $backup->tables[$i]; 			//get table name
		if(($prefix == "")  || (strrpos($table_name, $prefix) === 0)) {
			$backup->dump_table($table_name); 			//dump it to output (buffer)
			$the_db_dump .=htmlspecialchars($backup->output); 	//write output
			}
		}

	$the_db_dump .="\n\n-- end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end \n";
	$file = './backups/' . $now . '_' . $hourmin . '_tickets_backup.sql';
	$fh = fopen($file, 'w');
	if(!fwrite($fh, $the_db_dump)) {	
		$thereturn = "<BR /><B><FONT COLOR='red'>DB Backup failed</FONT></B>";
		fclose($fh);
		} else {
		$thereturn = '<BR /><B>Tickets Database backup complete</B>';
		}
	return $thereturn;
	}

$day_night = "Day";
?>

<!DOCTYPE html>
<html>
<HEAD>
<TITLE>Tickets - Database Backup Loader</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<STYLE>
/* Core Elements */
BODY 	{ background-color: <?php print $page_background;?>; margin:0; font-weight: normal; font-style: normal; 
		color: <?php print $normal_text;?>; font-family: Arial, Verdana, Geneva, "Trebuchet MS", Tahoma, Helvetica, sans-serif; text-decoration: none;}
TABLE 	{border-collapse: collapse;}
INPUT 	{background-color: <?php print $form_input_background;?>; font-weight: normal; color: <?php print $form_input_text;?>;}
INPUT:focus {background-color: yellow;}
TEXTAREA {background-color: <?php print $form_input_background;?>; font-weight: normal; color: <?php print $form_input_text;?>;}
TEXTAREA:focus {background-color: yellow;}
FIELDSET { margin: 0 0 20px; padding: 0.9em; border: 3px inset #FFFFFF; border-radius: 20px 20px; background-color: <?php print $row_light;?>;}
LABEL { width: 40%; display: inline-block; vertical-align: top; font-weight: bold; padding: 2px; text-align: left; text-decoration: underline;}
LEGEND { font-weight: bold; padding: 5px; background: #0000FF; border: 3px inset #FFFFFF; color: #FFFFFF; border-radius: 20px 20px;}
SELECT 	{background-color: <?php print $select_menu_background;?>; font-weight: normal;; 
		color: <?php print $select_menu_text;?>; text-decoration: underline;}
OPTION 	{font-weight: normal;}
A 		{font-weight: bold; color: <?php print $links;?>;}
.even {background-color: <?php print $row_light;?>;}
.odd {background-color: <?php print $row_dark;?>;}

/* buttons and links */
.plain, .hover, .plain_inactive {margin-left: 4px; color:#000000; padding: 4px 0.5em; text-decoration: none; float: left; font-weight: bold; cursor: pointer; border-radius:.5em;}
.plain {border: 1px outset #FFFFFF; background-color: #EFEFEF;}
.hover {border: 1px inset #FFFFFF; background-color: #DEE3E7;}

/* Text Colors */
.text_green {color: #009000;}
.text_orange {color: #EBA500;}
.text_blue {color: #0000E0;}
.text_red {color: #C00000;}	
.text_black {color: #000000;}
.text_white {color: #FFFFFF;}

/* Text Sizes */
.text_verysmall {font-size: .6em;}
.text_small {font-size: .7em;}
.text {font-size: 1em;}
.text_medium {font-size: .8em;}
.text_large {font-size: 1.1em;}
.text_big {font-size: 1.3em;}
.text_biggest {font-size: 1.5em;}
.text_massive {font-size: 3em;}

/* Text Weight */
.text_light {font-weight: lighter;}
.text_normal {font-weight: normal;}
.text_bold {font-weight: bold;}
.text_bolder {font-weight: bolder;}
.text_boldest {font-weight: 900;}

/* Text Decoration */
.italic {text-decoration: italic;}
.underline {text-decoration: underline;}

/* Text Wrap */
.nowrap {white-space:nowrap;}

/* Text Alignment */
.text_center {text-align: center;}
.text_valign_middle {vertical-align: middle;}
.text_valign_base {vertical-align: baseline;}

/* Text Alignment */
.middle {vertical-align: middle;}
.top {vertical-align: text-top;}
.bottom {vertical-align: text-bottom;}
.text_left {text-align: left;}
.text_right {text-align: right;}
.text_center {text-align: center;}

/* Scrolling Lists */
table.fixedheadscrolling { cellspacing: 0; border-collapse: collapse; }
table.fixedheadscrolling td {overflow: hidden; }
div.scrollableContainer {position: relative; top: 0px; border: 1px solid #999;}
div.scrollableContainer2 {position: relative; top: 0px; border: 1px solid #999;}
div.scrollingArea {max-height: 240px; overflow: auto; overflow-x: hidden;}
div.scrollingArea2 {max-height: 600px; overflow: auto; overflow-x: hidden;}
table.scrollable thead tr {position: absolute; left: -1px; top: 0px; }
table.fixedheadscrolling th {text-align: left; border-left: 1px solid #999;}

</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT type="text/javascript">
window.onresize=function(){set_size()};	
var viewportwidth, viewportheight, outerWidth, outerHeight, colWidth, colHeight;

function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	set_fontsizes(viewportwidth, "fullscreen");
	}
	
function confirm_delete() {
	if(confirm("Do you really want to delete this backup?")) {
		document.current_backups_form.submit();
		} else {
		return false;
		}
	}

</SCRIPT>
</HEAD>
<?php
if(empty($_GET)) {
?>
	<BODY>
		<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
			<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
				<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_biggest' STYLE='background-color: inherit;'>Tickets Database Backups</SPAN>
				</DIV>
				<BR />
				<BR />
				<FORM METHOD="POST" NAME= "current_backups_form" ACTION="db_loader.php?mode=del_bu">
				<FIELDSET>
					<LEGEND class='text_large text_bold'>Tickets Database Backups</LEGEND>
					<DIV style='position: relative;'>
<?php

						$i=1;
						if ($handle = opendir($dir)) {
							while (false !== ($filename = readdir($handle))) {
								if ($filename != "." && $filename != "..") {
									$ext = pathinfo($filename, PATHINFO_EXTENSION);
									if($ext == "sql" || $ext == "SQL") {

										print "<label for='backup[]'>" . $filename . "</label><input type='checkbox' name='backup[]' value='" . $filename . "'>";
										print "<BR />";
										}
									}
								$i++;
								}
							}
?>
					</DIV>		
				</FIELDSET>
				<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
				<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
				<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
				<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
				<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
				<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
				<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
				<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
				<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
				<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
				<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
				<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
				<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
				<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
				</FORM>
				<CENTER>
				<SPAN id='del_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='confirm_delete();'>Delete Selected<IMG id='del_img' style='float: right;' SRC='./images/delete.png' /></SPAN>
				<SPAN id='backup_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.do_backup.submit();'>Backup Database<IMG id='del_img' style='float: right;' SRC='./images/save.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.start_restore.submit();'>Restore a Backup<IMG id='can_img' style='float: right;' SRC='./images/restore_small.png' /></SPAN>
				</CENTER>
			</DIV>
		</DIV>
		<FORM METHOD="POST" NAME= "start_restore" ACTION="db_loader.php?mode=start">
		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
		</FORM>
		<FORM METHOD="POST" NAME= "do_backup" ACTION="db_loader.php?mode=do_backup">
		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
		</FORM>
<?php
	} elseif(array_key_exists('mode', $_GET) && $_GET['mode'] == "do_backup") {
?>
		<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
			<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%; height: 90%;'>
				<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_biggest' STYLE='background-color: inherit;'>Backing-up Tickets Database</SPAN>
				</DIV>
				<BR />
				<FIELDSET id='backupstatuswrapper' style='display: none;'>
					<LEGEND class='text_large text_bold'>Backing Up existing DB</LEGEND>
					<DIV id='backupstatus' style='width: 100%; height: 30px; overflow-y: auto;'>
					</DIV>
					<BR />
				</FIELDSET>
				<CENTER>
				<SPAN id='fin_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.fin_form.submit();'>Finish<IMG id='can_img' style='float: right;' SRC='./images/finished_small.png' /></SPAN>
				</CENTER>
			</DIV>
		</DIV>
		<FORM NAME='fin_form' METHOD="post" ACTION = "db_loader.php">
		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
		</FORM>	
<?php
		$output = dump_db($_POST['ticketshost'],$_POST['ticketsuser'],$_POST['ticketspassword'],$_POST['ticketsdb'],$_POST['ticketsprefix']);
?>
<SCRIPT>
		$('backupstatuswrapper').style.display = "block";
		$('backupstatus').innerHTML += "<?php print $output;?>";
</SCRIPT>
<?php
	} elseif(array_key_exists('mode', $_GET) && $_GET['mode'] == "del_bu") {
?>
	<BODY>
		<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
			<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
				<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_biggest' STYLE='background-color: inherit;'>Tickets Database Backups</SPAN>
				</DIV>
				<BR />
				<BR />
				<FIELDSET>
					<LEGEND class='text_large text_bold'>Tickets Database Backups</LEGEND>
					<DIV style='position: relative;'>
<?php
						$filestore = getcwd() . "/backups/";
						foreach($_POST['backup'] as $val) {
							$file = $filestore . $val;
							if(unlink($file)) {
								print $file . " has been deleted<BR />";
								} else {
								print $file . " could not be been deleted<BR />";
								}
							
							}
?>
					</DIV>		
				</FIELDSET>		
				<CENTER>
				<SPAN id='cont_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.continue_form.submit();'>Continue<IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
				</CENTER>
			</DIV>
		</DIV>
		<FORM METHOD="POST" NAME= "continue_form" ACTION="db_loader.php">
		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
		</FORM>
<?php
	} elseif(array_key_exists('mode', $_GET) && $_GET['mode'] == "start") {
?>
	<BODY>
		<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
			<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
				<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_biggest' STYLE='background-color: inherit;'>Restore Tickets DB Backup</SPAN>
				</DIV>
				<BR />
				<BR />
				<FORM METHOD="POST" NAME= "restore_form" ACTION="db_loader.php?mode=check">
				<FIELDSET>
					<LEGEND class='text_large text_bold'>Tickets Database Connection Information</LEGEND>
					<DIV style='position: relative;'>
						<LABEL for="ticketshost">Tickets Database Host</LABEL>
						<INPUT name='ticketshost' type='text' VALUE='<?php print $mysql_host;?>' />
						<BR />
						<LABEL for="ticketsdb">Tickets Database Name</LABEL>
						<INPUT name='ticketsdb' type='text' VALUE='<?php print $mysql_db;?>' />
						<BR />
						<LABEL for="ticketsuser">Tickets Database User</LABEL>
						<INPUT name='ticketsuser' type='text' VALUE='<?php print $mysql_user;?>' />
						<BR />
						<LABEL for="ticketspassword">Tickets Database User Password</LABEL>
						<INPUT name='ticketspassword' type='password' VALUE='<?php print $mysql_passwd;?>' />
						<BR />
						<LABEL for="ticketsprefix">Tickets Database Prefix</LABEL>
						<INPUT name='ticketsprefix' type='text' VALUE='<?php print $mysql_prefix;?>' />
						<BR />
						<LABEL for="db_schema">Backup File</LABEL>
						<?php print get_backups();?>;
						<BR />
					</DIV>		
				</FIELDSET>
				<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
				<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
				<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
				<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
				<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
				<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
				<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
				<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
				<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
				</FORM>
				<CENTER>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.restore_form.submit();'>Submit<IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
				<SPAN id='can_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_form.submit();'>Cancel<IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
				</CENTER>
			</DIV>
		</DIV>
		<FORM NAME='can_form' METHOD="post" ACTION = "db_loader.php">
		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
		</FORM>	
<?php
	} elseif(array_key_exists('mode', $_GET) && $_GET['mode'] == "check") {
?>
	<BODY>
		<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
			<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
				<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_biggest' STYLE='background-color: inherit;'>Check existing Data</SPAN>
				</DIV>
				<FORM METHOD="POST" NAME= "go_form" ACTION="db_loader.php?mode=go">
				<FIELDSET>
					<LEGEND class='text_large text_bold'>Tickets Database</LEGEND>
					<DIV id='status' style='width: 80%; height: 95%; overflow-y: auto;'>
					</DIV>
					<BR />
					<BR />
				</FIELDSET>
				<div style="text-align: center;">
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 150px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.go_form.submit();'>Submit<IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
				</div>
				<div style="text-align: center;">
				<SPAN id='restart_but' CLASS='plain text' style='float: none; width: 150px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.restart_form.submit();'>Submit<IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
				</div>
			</DIV>
		</DIV>

		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $_POST['ticketshost'];?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $_POST['ticketsdb'];?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $_POST['ticketsuser'];?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $_POST['ticketspassword'];?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $_POST['ticketsprefix'];?>' />			
		<INPUT name='db_schema' type='hidden' VALUE='<?php print $_POST['db_schema'];?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
		</FORM>
		<FORM NAME='restart_form' METHOD="post" ACTION = "db_loader.php">
		<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
		<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
		<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
		<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
		<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
		<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
		<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
		<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
		<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
		<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
		<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
		<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
		<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
		<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />	
		</FORM>		
<?php
	$connect = mysql_connect($_POST['ticketshost'], $_POST['ticketsuser'], $_POST['ticketspassword']);
	$db_selected = mysql_select_db('information_schema');
	if($db_selected) {
		$tables = array();
		$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $_POST['ticketsdb'] . "';";
		$result = mysql_query($query);
		while($row = mysql_fetch_assoc($result)) {
			$tables[] = $row['TABLE_NAME'];
			}
		$tableCount = count($tables);
		$existingDataCount = 0;
		$db_selected = mysql_select_db($_POST['ticketsdb']);
		for($i = 0; $i < $tableCount; $i++) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $tables[$i] . "`";
			$result = mysql_query($query);
			if($result && mysql_num_rows($result) > 0) {
				$existingDataCount++;
				}
			}
		if($existingDataCount > 0) {
			$output = "Data already exists in Tickets Database, are you sure you wish to continue?<BR />";
?>
<SCRIPT>
			$('status').innerHTML += "<?php print $output;?><BR />";
			$('status').innerHTML += "<label for='do_backup'>Backup Database First?</label>";
			$('status').innerHTML += "<input type='checkbox' name='do_backup' value=1 CHECKED>";
			$('sub_but').innerHTML = "Continue <IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' />";
			$('sub_but').style.display = "block";
</SCRIPT>
<?php
			} else {
			$output = "Database empty, ready to go?";
?>
<SCRIPT>
			$('status').innerHTML += "<?php print $output;?><BR />";
			$('sub_but').innerHTML = "Go fo it <IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' />";
			$('sub_but').style.display = "block";
</SCRIPT>
<?php			
			}
		$mysqlclosed = mysql_close();
		} else {
		$output = "Error connecting to database, please check your connection details and try again.<BR />";
?>
<SCRIPT>
		$('status').innerHTML += "<?php print $output;?><BR />";
		$('restart_but').style.display = "block";
</SCRIPT>
<?php
		}
	} elseif(array_key_exists('mode', $_GET) && $_GET['mode'] == "go") {
?>
	<BODY>
		<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
			<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%; height: 90%;'>
				<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_biggest' STYLE='background-color: inherit;'>Restoring Database Backup</SPAN>
				</DIV>
				<BR />
				<FIELDSET id='backupstatuswrapper' style='display: none;'>
					<LEGEND class='text_large text_bold'>Backing Up existing DB</LEGEND>
					<DIV id='backupstatus' style='width: 100%; height: 30px; overflow-y: auto;'>
					</DIV>
					<BR />
				</FIELDSET>
				<FIELDSET id='deletestatuswrapper' style='display: none;'>
					<LEGEND class='text_large text_bold'>Removing existing tables</LEGEND>
					<DIV id='deletestatus' style='width: 100%; height: 100px; overflow-y: auto;'>
					</DIV>
					<BR />
				</FIELDSET>
				<CENTER>
				<SPAN id='progress_img' style='display: none; z-index: 9999;'><IMG id='can_img' SRC='./images/owmloading.gif' /></SPAN><BR />
				</CENTER>
				<FIELDSET id='statuswrapper' style='display: none;'>
					<LEGEND class='text_large text_bold'>Restoring Backup</LEGEND>
					<DIV id='status' style='width: 100%; height: 200px; overflow-y: auto;'>
					</DIV>
					<BR />
					<BR />
				</FIELDSET>
				<CENTER>
				<SPAN id='fin_but' CLASS='plain text' style='float: none; width: 150px; display: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.fin_form.submit();'>Finish<IMG id='can_img' style='float: right;' SRC='./images/finished_small.png' /></SPAN>
				</CENTER>
			</DIV>
		</DIV>
	<FORM NAME='fin_form' METHOD="post" ACTION = "index.php">
	<INPUT name='ticketshost' type='hidden' VALUE='<?php print $mysql_host;?>' />
	<INPUT name='ticketsdb' type='hidden' VALUE='<?php print $mysql_db;?>' />
	<INPUT name='ticketsuser' type='hidden' VALUE='<?php print $mysql_user;?>' />
	<INPUT name='ticketspassword' type='hidden' VALUE='<?php print $mysql_passwd;?>' />
	<INPUT name='ticketsprefix' type='hidden' VALUE='<?php print $mysql_prefix;?>' />
	<INPUT name='page_background' type='hidden' VALUE='<?php print $page_background;?>' />
	<INPUT name='normal_text' type='hidden' VALUE='<?php print $normal_text;?>' />
	<INPUT name='form_input_background' type='hidden' VALUE='<?php print $form_input_background;?>' />
	<INPUT name='form_input_text' type='hidden' VALUE='<?php print $form_input_text;?>' />
	<INPUT name='row_light' type='hidden' VALUE='<?php print $row_light;?>' />
	<INPUT name='row_dark' type='hidden' VALUE='<?php print $row_dark;?>' />
	<INPUT name='select_menu_background' type='hidden' VALUE='<?php print $select_menu_background;?>' />
	<INPUT name='select_menu_text' type='hidden' VALUE='<?php print $select_menu_text;?>' />
	<INPUT name='links' type='hidden' VALUE='<?php print $links;?>' />
	</FORM>
<?php

	$connect = mysql_connect($_POST['ticketshost'], $_POST['ticketsuser'], $_POST['ticketspassword']);
	$db_selected = mysql_select_db('information_schema');
	if($db_selected) {	//	Connected OK to information Schema
		$tables = array();
		$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $_POST['ticketsdb'] . "';";
		$result = mysql_query($query);
		while($row = mysql_fetch_assoc($result)) {
			$tables[] = $row['TABLE_NAME'];
			}
		mysql_close($connect);	
		$tableCount = count($tables);
		if($tableCount > 0) {	//	Existing data
?>
<SCRIPT>
			if (typeof window.innerWidth != 'undefined') {
				viewportwidth = window.innerWidth,
				viewportheight = window.innerHeight
				} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
				viewportwidth = document.documentElement.clientWidth,
				viewportheight = document.documentElement.clientHeight
				} else {
				viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
				viewportheight = document.getElementsByTagName('body')[0].clientHeight
				}
			set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
<?php
			if(array_key_exists('do_backup', $_POST) && $_POST['do_backup'] == "1") { 
				$output = dump_db($_POST['ticketshost'],$_POST['ticketsuser'],$_POST['ticketspassword'],$_POST['ticketsdb'],$_POST['ticketsprefix']);
?>
<SCRIPT>
				$('backupstatuswrapper').style.display = "block";
				$('backupstatus').innerHTML += "<?php print $output;?>";
</SCRIPT>
<?php
				}
?>
<SCRIPT>
			$('deletestatuswrapper').style.display = "block";
			$('progress_img').style.display = "block";
</SCRIPT>
<?php
			$existingDataCount = 0;
			$connect = mysql_connect($_POST['ticketshost'], $_POST['ticketsuser'], $_POST['ticketspassword']);
			$db_selected = mysql_select_db($_POST['ticketsdb']);
			if($db_selected) {
				for($i = 0; $i < $tableCount; $i++) {
					$query = "DROP TABLE `$GLOBALS[mysql_prefix]" . $tables[$i] . "`";
					$result = mysql_query($query);
					if($result) {
						$output = $_POST['ticketsdb'] . " Database " . $tables[$i] . " Table dropped successfully<BR />";
?>
<SCRIPT>
						$('deletestatus').innerHTML += "<?php print $output;?>";
						$('deletestatus').scrollTop = $('deletestatus').scrollHeight;
</SCRIPT>
<?php
						}
					}
				mysql_close($connect);	
?>
<SCRIPT>
				$('deletestatus').innerHTML += "<BR /><b>Previous Data erased completely</B><BR />";
				$('deletestatus').scrollTop = $('deletestatus').scrollHeight;
				$('statuswrapper').style.display = "block";
				$('status').innerHTML += "<B>Restoring previous database backup</B><BR /><BR />";
</SCRIPT>
<?php
				$host = $_POST['ticketshost'];
				$user = $_POST['ticketsuser'];
				$pass = $_POST['ticketspassword'];
				$db = $_POST['ticketsdb'];
				$dbms_schema = $dir . "/" . $_POST['db_schema'];
				$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema)) or die('problem ');
				$sql_query = remove_remarks($sql_query);
				$sql_query = split_sql_file($sql_query, ';');
				$connect = mysql_connect($host,$user,$pass);
				$db_selected = mysql_select_db($db);
				if($db_selected) {
					$count = count($sql_query);
					for($i = 0; $i < $count; $i++) {
						$query = $sql_query[$i];
						$result = mysql_query($query);
						if($result) {
?>
<SCRIPT>
							$('status').innerHTML += "Completed Line " + <?php print $i;?> + "<BR />";
							$('status').scrollTop = $('status').scrollHeight;
</SCRIPT>
<?php
							}
						}
					mysql_close($connect);		
?>
<SCRIPT>
					$('progress_img').style.display = "none";
					$('fin_but').style.display = "block";
</SCRIPT>
<?php
					} else {
?>
<SCRIPT>
					$('progress_img').style.display = "none";
					$('statuswrapper').style.display = "block";
					$('status').innerHTML += "Problem connecting to database to restore Database<BR /><BR />";
					$('fin_but').style.display = "block";
</SCRIPT>
<?php						
					}
				} else {	//	Problem connecting to database to delete existing data
?>
<SCRIPT>
				$('progress_img').style.display = "none";
				$('statuswrapper').style.display = "block";
				$('status').innerHTML += "Problem connecting to database to delete existing data<BR /><BR />";
				$('fin_but').style.display = "block";
</SCRIPT>
<?php				
				}
			} else {	//	No existing Data, just restore Database
?>
<SCRIPT>
			$('progress_img').style.display = "block";
			$('status').innerHTML += "Restoring database backup<BR /><BR />";
</SCRIPT>
<?php
			$host = $_POST['ticketshost'];
			$user = $_POST['ticketsuser'];
			$pass = $_POST['ticketspassword'];
			$db = $_POST['ticketsdb'];
			$dbms_schema = $dir . "/" . $_POST['db_schema'];
			$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema)) or die('problem ');
			$sql_query = remove_remarks($sql_query);
			$sql_query = split_sql_file($sql_query, ';');
			$connect = mysql_connect($host,$user,$pass);
			$db_selected = mysql_select_db($db);
			$count = count($sql_query);
			for($i = 0; $i < $count; $i++) {
				$query = $sql_query[$i];
				$result = mysql_query($query);
				if($result) {
?>
<SCRIPT>
					$('status').innerHTML += "Completed Line " + <?php print $i;?> + "<BR />";
					$('status').scrollTop = $('status').scrollHeight;
</SCRIPT>
<?php
					}
				}
?>
<SCRIPT>
				$('status').innerHTML += "<BR /><B>Database restore successfully</B><BR />";
				$('status').scrollTop = $('status').scrollHeight;
</SCRIPT>
<?php
			mysql_close($connect);		
?>
<SCRIPT>
			$('progress_img').style.display = "none";
			$('fin_but').style.display = "block";
</SCRIPT>
<?php		
			}
		}
	}
?>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</BODY>
</HTML>