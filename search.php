<?php

/*
SELECT DISTINCT year(`problemstart`) AS `the_year` FROM `$GLOBALS[mysql_prefix]ticket` WHERE (year(`problemstart`) != 0) ORDER BY `the_year` DESC
8/28/08 mysql_fetch_array to  mysql_fetch_assoc
9/19/08 add injection protection to query parameters
1/21/09 added show butts - re button menu
2/24/09 added dollar function
7/20/10 gmaps call removed, quote_smart added for injection prevention
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/30/10 major re-do based on using $id_stack as list of qualifying ticket id's
3/15/11 changed stylesheet.php to stylesheet.php
4/5/11 get_new_colors() added
1/6/2013 XSS check corrected
9/25/2014 JC-specific functions added, GMaps references removed.
1/1/2015 - added year as search argument
4/12/2015 -  added actions and patients to ticket display
*/
error_reporting(E_ALL);

session_start();
session_write_close();
require_once('./incs/functions.inc.php');		// 9/29/10
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);					// 9/29/10
//if ($istest) {
//	dump ($_POST);
//	dump ($_GET);
//	}

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

$evenodd = array ("even", "odd");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Search Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth;
var viewportheight;
var searchlistwidth;
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
	searchlistwidth = viewportwidth * .9;
	searchtable_setwidths();
	set_fontsizes(viewportwidth, "fullscreen");
	}

function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		}
	}

try {
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
	}

function get_new_colors() {								// 4/5/11
	window.location.href = '<?php print basename(__FILE__);?>';
	}

function isViewable(element){
	return (element.clientHeight > 0);
	}
	
function searchtable_setwidths() {
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
	searchlistwidth = viewportwidth * .9;
	$('searchlist').style.width = searchlistwidth + "px";
	$('the_searchlist').style.width = searchlistwidth + "px";
	$('searchtable').style.width = searchlistwidth + "px";
	var viewableRow = 1;
	var searchtbl = document.getElementById('searchtable');
	var headerRow = searchtbl.rows[0];
	for (i = 1; i < searchtbl.rows.length; i++) {
		if(!isViewable(searchtbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	var tableRow = searchtbl.rows[viewableRow];
	if(tableRow &&i != searchtbl.rows.length) {
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {
				var thewidth = tableRow.cells[i].clientWidth +2;
				headerRow.cells[i].style.width = thewidth + "px";
				}
			}
		} else {
		var cellwidthBase = window.listwidth / 6;
		for (var i = 0; i < headerRow.cells.length; i++) {
			headerRow.cells[i].style.width = cellwidthbase + "px";
			}
		}
	}

function validate(theForm) {
	function TrimString(sInString) {
		sInString = sInString.replace( /^\s+/g, "" );// strip leading
		return sInString.replace( /\s+$/g, "" );// strip trailing
		}
	theForm.frm_query.value = TrimString(document.queryForm.frm_query.value);
	return true;
	}				// end function validate(theForm)
</SCRIPT>
</HEAD>
<?php
$do_str = ( ( array_key_exists('search_type', $_POST) ) && ( $_POST['search_type'] == 'pa') ) ? "do_pa ()" : "" ; 	// special case
?>
<BODY onLoad = "ck_frames(); <?php echo $do_str; ?>">
<?php
	$post_frm_query = ( ( array_key_exists('search_type', $_POST) ) && ( $_POST['search_type'] == 'db') ) ? strip_tags($_POST['frm_query']) : FALSE ;		// 1/6/2013
	if($post_frm_query) {
		$year_text = ($_POST['frm_year'] == 0) ? " for all years" : " in year " . $_POST['frm_year'];
		} else {
		$year_text = "";
		}

	if ($post_frm_query) {
?>
		<BR />
		<BR />
		<BR />
		<BR />
		<SPAN STYLE = 'margin-left:80px;'><FONT CLASS='header'>Search results for '<?php print $_POST['frm_query'];?>' <?php print $year_text;?></FONT></SPAN>
		<BR />
		<BR />
<?php
		$_POST['frm_query'] = str_replace(' ', '|', $_POST['frm_query']);
		$query_str = quote_smart(trim(str_replace(' ', '|', $_POST['frm_query'])));
		if($_POST['frm_search_in'])	{								//what field are we searching?
			$search_fields = "CAST({$_POST['frm_search_in']} AS CHAR) REGEXP '$_POST[frm_query]'";	//
			} else {							//list fields and form the query to search all of them
			$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket`");
			$search_fields = "";
			$ok_types = array("string", "blob", "VAR_STRING", "CHAR", "LONG", "LONGLONG", "BLOB");
			for ($i = 0; $i < mysql_num_fields($result); $i++) {
				if (in_array (mysql_field_type($result, $i), $ok_types )) {
    				$search_fields .= "CAST(`" . mysql_field_name($result, $i) ."` AS CHAR) REGEXP {$query_str} OR ";
    				}
    			}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);		// drop trailing OR
			}
		if (get_variable('restrict_user_tickets') && !(is_administrator()))	{	//is user restricted to his/her own tickets?
			$restrict_ticket = "AND owner='{$_SESSION['user_id']}'";
			} else {
			$restrict_ticket = "";
			}
		$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";		// 9/19/08

		$id_stack= array();

		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` <> {$GLOBALS['STATUS_RESERVED']} AND `status` LIKE " . quote_smart($_POST['frm_querytype']) . " AND " . $search_fields . " " . $restrict_ticket . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;		// 9/19/08
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);

		$tick_hits = mysql_affected_rows();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($id_stack, $row['id']);
			}
		$query = "SELECT `ticket_id` FROM `$GLOBALS[mysql_prefix]patient`
			WHERE CAST(`description` AS CHAR) REGEXP " . quote_smart($_POST['frm_query']) . " OR CAST(`name` AS CHAR) REGEXP " . quote_smart($_POST['frm_query']) ;
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
		$per_hits = mysql_affected_rows();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($id_stack, $row['ticket_id']);
			}

		$query = "SELECT `ticket_id` FROM `$GLOBALS[mysql_prefix]action`
			WHERE CAST(`description` AS CHAR) REGEXP " . quote_smart($_POST['frm_query']);		// 9/19/08
		$result = mysql_query($query) or do_error('','', mysql_error(),basename( __FILE__), __LINE__);
		$act_hits = mysql_affected_rows();

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($id_stack, $row['ticket_id']);
			}

		if (empty($id_stack )){
			print "<SPAN STYLE = 'margin-left:80px'><B>No matches found</B></SPAN><BR /><BR />";
			} else {
			$id_stack = array_unique($id_stack);		// at least one
			$in_str = $sep = "";
			for ($i=0; $i< count($id_stack); $i++) {
				if (isset($id_stack[$i])) {				// 4/12/2015
					$in_str .= "{$sep}'{$id_stack[$i]}'";
					$sep = ", ";
					}
				}

		$acts_ary = $pats_ary = array();				// 4/12/2015
		
		$where = ($_POST['frm_year'] != 0) ? "WHERE year(`date`) = " . quote_smart($_POST["frm_year"]) : "";

		$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]action` {$where} GROUP BY `ticket_id`";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
			$acts_ary[$row['ticket_id']] = $row['the_count'];
			}

		$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]patient` {$where} GROUP BY `ticket_id`";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
			$pats_ary[$row['ticket_id']] = $row['the_count'];
			}
		
		$where2 = ($where == "") ? "WHERE `status` <> {$GLOBALS['STATUS_RESERVED']}" : "AND `status` <> {$GLOBALS['STATUS_RESERVED']}";

//								1/1/2015
			$query = "SELECT `id`, UNIX_TIMESTAMP(`problemstart`) AS `problemstart`, UNIX_TIMESTAMP(`updated`) AS `updated`, `scope`, `status`, `severity`,
				CONCAT_WS(' ',`street`,`city`,`state`) AS `addr`
				FROM `$GLOBALS[mysql_prefix]ticket`
				{$where} {$where2}
				AND `id` IN ({$in_str})
				AND `status` LIKE " . quote_smart($_POST['frm_querytype']) . "
				ORDER BY `severity` DESC, `problemstart` ASC";

			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			if(mysql_num_rows($result) == 1) {	//	revised to redirect to main.php rather than show ticket in search.php	4/29/13
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', FALSE);
				header('Pragma: no-cache');

				$host  = $_SERVER['HTTP_HOST'];
				$url = "main.php?id=" . $row['id'];
				redir($url);
				exit();

				} elseif (mysql_num_rows($result) == 0) {
				print "<SPAN CLASS='text' STYLE = 'margin-left:80px'><B>No matches found</B></SPAN><BR /><BR />";
				} else {		//  more than one, list them
				print "<SPAN CLASS='text' STYLE = 'margin-left: 80px'><B>Matches</B>: tickets {$tick_hits}, actions {$act_hits}, persons {$per_hits}</SPAN><BR /><BR />";
				print "<DIV class='scrollableContainer' id='searchlist' style='position: relative; left: 2%;'>";
				print "<DIV class='scrollingArea' id='the_searchlist' style='width: 100%;'>";
				print "<TABLE BORDER='0' id='searchtable' class='fixedheadscrolling scrollable' style='width: 100%;'><thead>";
				print "<TR style='width: 99%;'>";
				print "<TH CLASS='plain_listheader text text_left'>Ticket</TH>";
				print "<TH CLASS='plain_listheader text text_left'>Opened</TH>";
				print "<TH CLASS='plain_listheader text text_left'>Description</TH>";
				print "<TH CLASS='plain_listheader text text_left'>Location</TH>";
				print "<TH CLASS='plain_listheader text text_left'>Actions</TH>";
				print "<TH CLASS='plain_listheader text text_left'>Patients</TH>";
				print "</TR></thead><tbody>";
				$counter = 0;

				while($row = stripslashes_deep(mysql_fetch_assoc($result))){				// 8/28/08
					if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
						$strike = "<strike>"; $strikend = "</strike>";
						}
					else { $strike = $strikend = "";}
					switch($row['severity'])		{		//color tickets by severity
					 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
						case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
						default: 				$severityclass='severity_normal'; break;
						}

					$acts = (array_key_exists($row['id'], $acts_ary)) ? strval($acts_ary[$row['id']]) : "0";
					$pats = (array_key_exists($row['id'], $pats_ary)) ? strval($pats_ary[$row['id']]) : "0";
					print "<TR CLASS='{$evenodd[$counter%2]}' style='width: 100%;' onClick = \"Javascript: self.location.href = 'main.php?id={$row['id']}';\">";
					print "<TD CLASS='plain_list $severityclass text text_left'>#{$row['id']}</TD>";
					print "<TD CLASS='plain_list $severityclass text text_left'>" . format_date($row['problemstart'])."</TD>";
					print "<TD CLASS='plain_list $severityclass text text_left'>{$strike}" . shorten(highlight($_POST['frm_query'], $row['scope']), 120) . "{$strikend}</TD>";
					print "<TD CLASS='plain_list $severityclass text text_left'>{$strike}" . shorten(highlight($_POST['frm_query'], $row['addr']), 120) . "{$strikend}</TD>";
					print "<TD CLASS='plain_list $severityclass text text_left'>" . $acts . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
					print "<TD CLASS='plain_list $severityclass text text_left'>" . $pats . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
					print "</TR>";
					$counter++;
					}
				print '</tbody></TABLE></DIV></DIV>';
				print '<SCRIPT>searchtable_setwidths();</SCRIPT>';
				}			// end if/else
			}			// end if/else (empty($id_stack ))
		}				// end if ($_POST['frm_query'])
	else {
//		print "<SPAN STYLE = 'margin-left:86px'><FONT CLASS='header'>Search</FONT></SPAN>";
		}

?>
<BR /><BR />

<FORM METHOD="post" NAME="queryForm" ACTION="<?php echo basename(__FILE__); ?>" onSubmit="return validate(document.queryForm)">
<input type = hidden name = "search_type" value = "db" />
<TABLE CELLPADDING="2" BORDER="0" STYLE = 'margin-left:80px;'>
	<TR CLASS = "even">
		<TD VALIGN="top" CLASS="td_label text">Search for: &nbsp;</TD>
		<TD CLASS='td_data text'><INPUT TYPE="text" SIZE="40" MAXLENGTH="255" VALUE="<?php print $post_frm_query;?>" NAME="frm_query"></TD>
	</TR>
	<TR CLASS = "odd">
		<TD VALIGN="top" CLASS="td_label text">In: &nbsp;</TD>
		<TD CLASS='td_data text'>
			<SELECT NAME="frm_search_in">
				<OPTION VALUE="" checked>All fields</OPTION>
				<OPTION VALUE="contact">Reported by</OPTION>
				<OPTION VALUE="street">Address</OPTION>
				<OPTION VALUE="city">City</OPTION>
				<OPTION VALUE="state">State</OPTION>
				<OPTION VALUE="description">Description</OPTION>
				<OPTION VALUE="comments">Comments</OPTION>
				<OPTION VALUE="owner">Owner</OPTION>
				<OPTION VALUE="date">Issue Date</OPTION>
				<OPTION VALUE="problemstart">Problem Starts</OPTION>
				<OPTION VALUE="problemend">Problem Ends</OPTION>
			</SELECT>
		</TD>
	</TR>
<?php						// 1/1/2015
	$query ="SELECT DISTINCT year(`problemstart`) AS `the_year` FROM `$GLOBALS[mysql_prefix]ticket` WHERE (year(`problemstart`) != 0) ORDER BY `the_year` DESC";
	$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
	
	$thisYear = date("Y");
?>
	<TR CLASS = "even">
		<TD VALIGN="top" CLASS="td_label text">Year:</TD>
		<TD CLASS='td_data text'>
			<SELECT NAME="frm_year">
				<OPTION VALUE=0 SELECTED>All</OPTION>
<?php
				if(mysql_num_rows($result) > 0) {
					while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
						echo "\t<OPTION VALUE=\"{$row['the_year']}\">{$row['the_year']}</OPTION>\n";
						}
					} else {
					echo "\t<OPTION VALUE=\"{$thisYear}\">{$thisYear}</OPTION>\n";
					}
?>
			</SELECT>
		</TD>
	</TR>
	<TR CLASS = "odd">
		<TD VALIGN="top" CLASS="td_label text">Order By: &nbsp;</TD>
		<TD CLASS='td_data text'>
			<SELECT NAME="frm_ordertype">
				<OPTION VALUE="date">Issue Date</OPTION>
				<OPTION VALUE="problemstart">Problem Starts</OPTION>
				<OPTION VALUE="problemend">Problem Ends</OPTION>
				<OPTION VALUE="affected">Affected</OPTION>
				<OPTION VALUE="scope">Incident</OPTION>
				<OPTION VALUE="owner">Owner</OPTION>
			</SELECT>
			&nbsp;Descending: <INPUT TYPE="checkbox" NAME="frm_order_desc" VALUE="DESC" CHECKED>
		</TD>
	</TR>
	<TR CLASS = "even">
		<TD VALIGN="top" CLASS="td_label">Status: &nbsp;</TD>
		<TD>
			<INPUT TYPE="radio" NAME="frm_querytype" VALUE="%" CHECKED> All<BR />
			<INPUT TYPE="radio" NAME="frm_querytype" VALUE="<?php print $STATUS_OPEN;?>"> Open<BR />
			<INPUT TYPE="radio" NAME="frm_querytype" VALUE="<?php print $STATUS_CLOSED;?>"> Closed<BR />
		</TD>
	</TR>
	<TR CLASS = "even">
		<TD COLSPAN=2 ALIGN = "center">
			<SPAN ID='can_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='history.back();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
			<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.queryForm.reset(); init();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
			<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.queryForm.submit();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
		</TD>
	</TR>
</TABLE>
</FORM>

</div>
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
searchlistwidth = viewportwidth * .9;
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</BODY></HTML>
