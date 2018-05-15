<?php
error_reporting(E_ALL);
require_once('incs/functions.inc.php');
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
do_login(basename(__FILE__));	// session_start()
@session_start();
session_write_close();

$mode = (array_key_exists('mode', $_GET)) ? $_GET['mode'] : 'start';
$mdbTables = array('allocations','capability_types','clothing_types','defined_fields','equipment_types','fieldsets','files','log','member','member_status','member_types','team','training_packages','vehicles','vehicle_types','events','event_types','waste_basket_f','waste_basket_m');
$tickets_mdbTables = array('allocations','capability_types','clothing_types','defined_fields','equipment_types','fieldsets','mdb_files','log','member','member_status','member_types','team','training_packages','vehicles','vehicle_types','events','event_types','waste_basket_f','waste_basket_m');
$tableCount = count($mdbTables);
$ticketsTableCount = count($tickets_mdbTables);
$existingDataCount = 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Units Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
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
	outerWidth = viewportwidth *.98;
	outerHeigth = viewportheight *.85;
	colWidth = outerWidth * .8;
	colHeight = outerHeight *.6;
	set_fontsizes(viewportwidth, "fullscreen");
	if($('outer')) {$('outer').style.width = outerWidth + "px";}
	if($('outer')) {$('outer').style.height = outerHeight + "px";}
	if($('leftcol')) {$('leftcol').style.width = colWidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = colHeight + "px";}
	setWidths();
	}	

function ck_frames() {
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		} else {
		parent.upper.show_butts();
		}
	}		// end function ck_frames()
</SCRIPT>
</HEAD>
<BODY style='overflow: hidden;'>
<DIV id = "outer" style='position: absolute; left: 0px; top: 10px; height: 70%; width: 90%;'>
<?php
switch($mode) {
	case "start":
?>
	<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
		<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Import Tickets MDB Data into Tickets</SPAN>
		</DIV>
		<BR />
		<BR />
		<FORM METHOD="POST" NAME= "db_connect_Form" ACTION="ticketsmdb_import.php?mode=go">
		<FIELDSET>
			<LEGEND class='text_large text_bold'>Tickets MDB Database Connection Information</LEGEND>
			<DIV style='position: relative;'>
				<LABEL for="mdbhost">Tickets MDB Database Host</LABEL>
				<INPUT name='mdbhost' type='text' VALUE='localhost'/>
				<BR />
				<LABEL for="mdbhost">Tickets MDB Database Name</LABEL>
				<INPUT name='mdbdb' type='text' />
				<BR />
				<LABEL for="mdbuser">Tickets MDB Database User</LABEL>
				<INPUT name='mdbuser' type='text' />
				<BR />
				<LABEL for="mdbpassword">Tickets MDB Database User Password</LABEL>
				<INPUT name='mdbpassword' type='password' />
				<BR />
				<LABEL for="mdbprefix">Tickets MDB Database Prefix</LABEL>
				<INPUT name='mdbprefix' type='text' />
				<BR />
			</DIV>				
		</FIELDSET>
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
			</DIV>		
		</FIELDSET>		
		</FORM>
		<CENTER>
		<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 100px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.db_connect_Form.submit();'><?php print get_text('Submit');?><IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
		</CENTER>
	</DIV>
<?php
	break;
	
	case "go":
	$connect = mysql_connect($_POST['ticketshost'], $_POST['ticketsuser'], $_POST['ticketspassword']);
	$db_selected = mysql_select_db($_POST['ticketsdb']);
	for($y = 0; $y < $tableCount; $y++) {
		if($tickets_mdbTables[$y] != "log") {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $tickets_mdbTables[$y] . "`";
			$result = mysql_query($query);
			if(mysql_num_rows($result) > 0) {
				$existingDataCount++;
				}
			}
		}
		
	$mysqlclosed = mysql_close();
	
 	if($existingDataCount > 0) {
?>
		<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
			<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
				<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Import Tickets MDB Data into Tickets</SPAN>
			</DIV>
			<BR />
			<BR />
			<CENTER>
			<SPAN CLASS='warn'>For Tickets Database <?php print $mysql_db;?><BR />Data already exists in Tickets in the MDB Tables</SPAN><BR /><BR /><BR />
			<SPAN id='dodelete_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.dodelete_Form.submit();'>Empty Tables<IMG id='can_img' style='float: right;' SRC='./images/delete.png' /></SPAN>
			<SPAN id='nodelete_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.nodelete_Form.submit();;'>Cancel<IMG id='can_img' style='float: right;' SRC='./images/cancel.png' /></SPAN>
			</CENTER>
		</DIV>
		<FORM NAME='dodelete_Form' METHOD="post" ACTION = "ticketsmdb_import.php?mode=dodelete">
		<INPUT TYPE='hidden' NAME = 'mdbhost' VALUE="<?php print $_POST['mdbhost'];?>"/>
		<INPUT TYPE='hidden' NAME = 'mdbdb' VALUE="<?php print $_POST['mdbdb'];?>"/>
		<INPUT TYPE='hidden' NAME = 'mdbuser' VALUE="<?php print $_POST['mdbuser'];?>"/>
		<INPUT TYPE='hidden' NAME = 'mdbpassword' VALUE="<?php print $_POST['mdbpassword'];?>" />
		<INPUT TYPE='hidden' NAME = 'mdbprefix' VALUE="<?php print $_POST['mdbprefix'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketshost' VALUE="<?php print $_POST['ticketshost'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketsdb' VALUE="<?php print $_POST['ticketsdb'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketsuser' VALUE="<?php print $_POST['ticketsuser'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketspassword' VALUE="<?php print $_POST['ticketspassword'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketsprefix' VALUE="<?php print $_POST['ticketsprefix'];?>" />
		</FORM>
		<FORM NAME='nodelete_Form' METHOD="post" ACTION = "config.php"></FORM>
<?php
		} else {
		$output_text = "";
		for($z = 0; $z < $tableCount; $z++) {
			$tabledatacount = 0;
			$tabledata = array();
			$table_fields = array();
			$connect = mysql_connect($_POST['mdbhost'], $_POST['mdbuser'], $_POST['mdbpassword']);
			$db_selected = mysql_select_db($_POST['mdbdb']);

			$query = "DESCRIBE `$GLOBALS[mysql_prefix]" . $mdbTables[$z] . "`";
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$table_fields[] = $row['Field'];
				}
				
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]" . $mdbTables[$z] . "`";
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$tabledata[] = $row;
				}	
			
			$mysqlclosed = mysql_close();
			$connect = mysql_connect($_POST['ticketshost'], $_POST['ticketsuser'], $_POST['ticketspassword']);
			$db_selected = mysql_select_db($_POST['ticketsdb']);

			$dataCount = count($tabledata);
			$fieldCount = count($table_fields);
			for($m = 0; $m < $dataCount; $m++) {
				if($tickets_mdbTables[$z] != "log") {
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]" . $tickets_mdbTables[$z] . "` (";
					for($i = 0; $i < $fieldCount; $i++) {
						if($i < ($fieldCount-1)) {
							$query .= "`" . $table_fields[$i] . "`, ";
							} else {
							$query .= "`" . $table_fields[$i] . "`";
							}
						}
					$query .= ") VALUES (";
					for($j = 0; $j < $fieldCount; $j++) {
						if($j < ($fieldCount-1)) {
							$query .= quote_smart(trim($tabledata[$m][$table_fields[$j]])) . ",";
							} else {
							$query .= quote_smart(trim($tabledata[$m][$table_fields[$j]]));
							}
						}
					$query .= ");";
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					if($result) {$tabledatacount++;}
					} else {
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]" . $tickets_mdbTables[$z] . "` (";
					for($i = 0; $i < $fieldCount; $i++) {
						if($table_fields[$i] != 'id') {
							if($i < ($fieldCount-1)) {
								$query .= "`" . $table_fields[$i] . "`, ";
								} else {
								$query .= "`" . $table_fields[$i] . "`";
								}
							}
						}
					$query .= ") VALUES (";
					for($j = 0; $j < $fieldCount; $j++) {
						if($table_fields[$j] != 'id') {
							if($j < ($fieldCount-1)) {
								$query .= quote_smart(trim($tabledata[$m][$table_fields[$j]])) . ",";
								} else {
								$query .= quote_smart(trim($tabledata[$m][$table_fields[$j]]));
								}
							}
						}
					$query .= ");";
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					if($result) {$tabledatacount++;}					
					}
				}
				

			$output_text .= "Added " . $tabledatacount . " entries to Table " . $tickets_mdbTables[$z] . "<BR />";
			}

			$query = "SELECT `id`, `field5`, `field1`, `field2` from `$GLOBALS[mysql_prefix]member`";
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				if($row['field5'] != "") {
					$id = $row['id'];
					$temp = $row['field5'];
					$field1 = $row['field1'];
					$field2 = $row['field2'];
					$location = str_replace('pictures', 'mdb_pictures', $temp);
					$query2 = "UPDATE `$GLOBALS[mysql_prefix]member` SET `field5` = '" . $location . "' WHERE `id` = " . $id;
					$result2 = mysql_query($query2);
					if($result2) {$output_text .= "Updated picture location for member " . $field2 . " " . $field1 . "<BR />";}
					}
				}

			$query = "SELECT `id`, `name`, `member_id` from `$GLOBALS[mysql_prefix]mdb_files`";
			$result = mysql_query($query);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$id = $row['id'];
				$temp = $row['name'];
				$location = str_replace('files', 'mdb_files', $temp);
				$membername = get_member_name($row['member_id']);
				$query2 = "UPDATE `$GLOBALS[mysql_prefix]mdb_files` SET `name` = '" . $location . "' WHERE `id` = " . $id;
				$result2 = mysql_query($query2);
				if($result2) {$output_text .= "Updated file location for member " . $membername . "<BR />";}
				}
				
			$mysqlclosed = mysql_close();
			if(!$mysqlclosed) {
				print "Error closing mysql connection<BR />";
				}
?>				
		<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
			<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
				<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Import Tickets MDB Data into Tickets</SPAN>
			</DIV>
			<BR />
			<BR />
			<DIV style='width: 98%; display: block; height: 200px; overflow-y: scroll;'><?php print $output_text;?></DIV><BR /><BR />
			<SPAN CLASS='text text_bold' style='text-align: left;'>Data Import Complete<BR /><BR /><BR />You now need to make sure that settings are updated to "use_mdb"<BR />and then associate responders with members as required<BR /></SPAN><BR /><BR /><BR />
			<CENTER>
			<SPAN id='fin_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.fin_Form.submit();'>Finish<IMG id='can_img' style='float: right;' SRC='./images/finished_small.png' /></SPAN>
			</CENTER>
		</DIV>
		<FORM NAME='fin_Form' METHOD="post" ACTION = "config.php"></FORM>				
<?php
		}
	break;
	
	case "dodelete":
	$output_text = "";
	for($i = 0; $i < $ticketsTableCount; $i++) {
		if($tickets_mdbTables[$i] != "log") {
			$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]" . $tickets_mdbTables[$i] . "`";
			$result = mysql_query($query);
			if($result) {$output_text .= "Emptied data from " . $tickets_mdbTables[$i] . " table<BR />";}
			}
		}
	$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]responder_x_member`";
	$result = mysql_query($query);
	if($result) {$output_text .= "Emptied data from responder_x_member table<BR />";}
	
?>
	<DIV id = 'leftcol' style='position: relative; left: 30px; float: left; width: 60%;'>
		<DIV CLASS='header' style = "height: 40px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Import Tickets MDB Data into Tickets</SPAN>
		</DIV>
		<BR />
		<BR />
		<DIV style='width: 98%; display: block; height: 200px; overflow-y: scroll;'><?php print $output_text;?></DIV><BR /><BR />
		<SPAN CLASS='text text_bold text_left'>Existing Data deleted</SPAN><BR /><BR /><BR />
		<CENTER>
		<SPAN id='dodelete_but' CLASS='plain text' style='float: none; width: 150px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.go_Form.submit();'>Continue<IMG id='can_img' style='float: right;' SRC='./images/submit_small.png' /></SPAN>
		</CENTER>
	</DIV>
		<FORM NAME='go_Form' METHOD="post" ACTION = "ticketsmdb_import.php?mode=go">
		<INPUT TYPE='hidden' NAME = 'mdbhost' VALUE="<?php print $_POST['mdbhost'];?>"/>
		<INPUT TYPE='hidden' NAME = 'mdbdb' VALUE="<?php print $_POST['mdbdb'];?>"/>
		<INPUT TYPE='hidden' NAME = 'mdbuser' VALUE="<?php print $_POST['mdbuser'];?>"/>
		<INPUT TYPE='hidden' NAME = 'mdbpassword' VALUE="<?php print $_POST['mdbpassword'];?>" />
		<INPUT TYPE='hidden' NAME = 'mdbprefix' VALUE="<?php print $_POST['mdbprefix'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketshost' VALUE="<?php print $_POST['ticketshost'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketsdb' VALUE="<?php print $_POST['ticketsdb'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketsuser' VALUE="<?php print $_POST['ticketsuser'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketspassword' VALUE="<?php print $_POST['ticketspassword'];?>" />
		<INPUT TYPE='hidden' NAME = 'ticketsprefix' VALUE="<?php print $_POST['ticketsprefix'];?>" />
		</FORM>
<?php
	break;
	}
?>
</DIV>
</BODY>
<SCRIPT>
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
outerWidth = viewportwidth *.98;
outerHeigth = viewportheight *.70;
colWidth = outerWidth * .80;
colHeight = outerHeight *.65;
set_fontsizes(viewportwidth, "fullscreen");
if($('outer')) {$('outer').style.width = outerWidth + "px";}
if($('outer')) {$('outer').style.height = outerHeight + "px";}
if($('leftcol')) {$('leftcol').style.width = colWidth + "px";}
if($('leftcol')) {$('leftcol').style.height = colHeight + "px";}
setWidths();
</SCRIPT>
</HTML>
