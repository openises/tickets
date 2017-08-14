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
$jc_911 = mysql_table_exists("$GLOBALS[mysql_prefix]jc_911");

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
	if ($jc_911) {
?>
<script>
		function do_db () {
			$("db").style.display = "inline";
			$("pa").style.display = "none";
			$("fa").style.display = "none";
			$("po").style.display = "none";
			document.queryForm.frm_query.focus();
			}

		function do_pa () {
			$("db").style.display = "none";
			$("pa").style.display = "inline";
			$("fa").style.display = "none";
			$("po").style.display = "none";
			}

		function do_fa () {
			$("db").style.display = "none";
			$("pa").style.display = "none";
			$("fa").style.display = "inline";
			$("po").style.display = "none";
			document.getElementById("addr_list").innerHTML = "";
			document.fa_form.frm_street.value = "";
			document.fa_form.frm_street.focus();
			}

		function do_po () {				// not implemented
			$("db").style.display = "inline";
			$("pa").style.display = "inline";
			$("fa").style.display = "inline";
			$("po").style.display = "none";
			}

</script>
		<span style = "margin-left:40px;"><b>Search &raquo;</b>
		<button style = "margin-left:20px;margin-top:10px" onclick = "do_db()">Incidents</button>
		<button style = "margin-left:20px;margin-top:10px" onclick = "do_pa()">Partial address</button>
		<button style = "margin-left:20px;margin-top:10px" onclick = "do_fa()">Full address</button>
		<button style = "display:none; margin-left:20px;margin-top:10px" onclick = "do_po()">Position</button>
		</span>
		<div id   = 'db' style = 'display:inline;'>
<?php
		}		// end 	if ($jc_911)

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
<?php
	if ($jc_911) {
?>

<script>

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {

				alert("error@ 330" );
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}

	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];

	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try { xmlhttp = XMLHttpFactories[i](); }
			catch (e) { continue; }
			break;
			}
		return xmlhttp;
		}

	char_lim = 4;

	function do_selected_addr( theId ) {						// client-side js span 'onclick' function
		var myWindow = window.open("show_jc_911.php?id=" +  theId, "myWindow", "top=100, left=250, width=250, height=640");
		myWindow.focus();
		}

	function get_fa_list(inStr) {	     				// 8/10/2014
			function fa_callback(req) {			// private callback function
				document.getElementById("addr_list").innerHTML = req.responseText;
				}
		if (inStr.length < char_lim) { return;}			// revisit length check
		else {
			var params = "q=" + escape(inStr);			// post keyboarded string
//						(url,  callback,  postData)
			sendRequest ( './ajax/jc_addr_lookup_srch.php', fa_callback, params);	// url, return handler, data sent
			}
	 	}				// end function get_fa_list()
// ______________________________________________________________

</script>

<div id = "pa" style = "display:none; margin-left:60px; margin-top:20px;">
<form name = "pa_form" method = "post" action = "<?php echo basename(__FILE__) ;?>">
<input type = hidden name = "search_type" value = "pa" />
<h3 style = 'margin-left: 40px;'>Partial address:</h3>
<span style = 'margin-left: 40px;'><i>Enter known information</i></span><br />

<input name = "frm_first" type = "text" value = "" placeholder = "First name" style = "margin-left:40px;"/>
<input name = "frm_last" type = "text" value = "" placeholder = "Last name" />
<input name = "frm_addr" type = "text" value = "" placeholder = "Street or road" />

<?php
	echo "<SELECT NAME = 'frm_community'><option value = ''>Select Community</OPTION>n";
	$query = "SELECT DISTINCT `community` FROM `$GLOBALS[mysql_prefix]jc_911` ORDER BY `community` ASC";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename(__FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		echo "<option value = '{$row['community']}'>{$row['community']}</option>\n";
		}				// end while ()
	echo "</SELECT>\n";
?>
<button style = "" onclick = "this.form.submit();">Next</button>
<!--  <button style = "">Reset</button> -->
</form>
<div id = "pa_list">
<?php

	$post_frm_pa_query = ( ( array_key_exists('search_type', $_POST) ) && ( $_POST['search_type'] == 'pa') ) ;		// 1/6/2013

	if ($post_frm_pa_query) {
	// _____________________________________________________________________________________


	$js_func = "do_selected_addr";						// client-side js span 'onclick' function
	$tablename = "jc_911";

	function get_resident_phr() {		// returns get_resident query phrase - avoiding conditionals
		$sw_val = (empty ($_POST['frm_first']) ) ?  0 : 1 ;
		$sw_val += (empty ($_POST['frm_last']) ) ?  0 : 2 ;
		switch ($sw_val) {
			case 0:				// neither
				return "";
				break;

			case 1:				// first only
				return " (CAST(`resident`AS CHAR) REGEXP '{$_POST['frm_first']}') AND";
				break;

			case 2:				// last only
				return " (CAST(`resident`AS CHAR) REGEXP '{$_POST['frm_last']}') AND"; ;
				break;

			case 3:				// both
				return "( (CAST(`resident`AS CHAR) REGEXP '{$_POST['frm_first']}') OR (`resident` REGEXP '{$_POST['frm_last']}') ) AND";
				break;

			}		// end switch
		}		// end function get_rd_name_phr()

	$rd_name_lgth = 0;
	function get_rd_name_phr() {		// returns rd_name query phrase
		global $rd_name_lgth;
		$rd_name_lgth = strlen ( $_POST['frm_addr']);
		return (empty($_POST['frm_addr'] ) ) ?  ""  : " ( CAST(`rd_name`AS CHAR) REGEXP '{$_POST['frm_addr']}' OR CAST(`old_rd_name`AS CHAR) REGEXP '{$_POST['frm_addr']}' ) AND" ;
		}

	function get_community_phr() {		// returns community query phrase
		return (empty($_POST['frm_community'] ) ) ?  ""  : " (CAST(`community`AS CHAR) REGEXP '{$_POST['frm_community']}' ) AND" ;
		}

	$query = "SELECT `id` AS `payload` ,
			CONCAT_WS( ' ', `resident` , `house_num` , UPPER(`rd_name`) , UPPER(`community`) )	AS `address` ,
			CONCAT_WS( ' ', `old_num` , UPPER(`old_rd_name`) ) 									AS `address_old`
		FROM `$GLOBALS[mysql_prefix]{$tablename}` WHERE ";					// now build the WHERE clause

	$rd_name_phr = get_rd_name_phr();
	$resident_phr = get_resident_phr();
	$community_phr = get_community_phr();
	$query .= "{$rd_name_phr}";
	$query .= "{$resident_phr}";
	$query .= "{$community_phr}";
	$query = substr($query, 0, (strlen($query) - 4));  		// drop terminal ' AND'
	$query .= " ORDER BY `community` ASC, `rd_name` ASC, `house_num` ASC";

	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename(__FILE__), __LINE__);
	if (mysql_num_rows($result)> 0) {
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
			extract ($row);
			echo "<span style = 'margin-left: 80px;'><input type = radio name = 'addr_rb' onclick = '{$js_func}(\"{$payload}\")'>{$address} / {$address_old}</span><br />\n";	// call client function
			}		// end while ($row ...)
		}		// end if ()
	else {
		echo "<h2 style = 'margin-left:360px;'>No matches!</h2><br />";
		}
	}		// end if ($post_frm_pa_query)

// _____________________________________________________________________________________

?>
</div> <!-- / pa_list -->
</div> <!-- / pa -->

<div id = "fa" style = "display:none; margin-left:60px; margin-top:20px;">

<form name = "fa_form" method = "post" action = "">
<input type = hidden name = "search_type" value = "fa" />
<h3 style = 'margin-left: 40px;'>Full address:</h3>
<INPUT  style = "margin-left:40px;" NAME="frm_street" onkeyup="get_fa_list(this.value);"
	SIZE="72" TYPE="text" VALUE="" MAXLENGTH="96" autocomplete="off" />

<DIV ID="addr_list" style = "margin-left:40px; display:inline;"></DIV>	<!-- search results displayed here -->

<!-- <button style = "" onclick = "chk_fa(this.form;)">Next</button>  -->
</form>
</div> <!-- end "fa" -->

<div id = "po" style = "display:none; margin-left:60px; margin-top:20px;">
<form name = "po_form" method = "post" action = "">
<input type = hidden name = "search_type" value = "po" />
<h3 style = 'margin-left: 40px;'>Position:</h3>
<input name = "frm_lat" type = "text" value = "" placeholder = "Latitude" style= "margin-left:40px;"/>
<input name = "frm_lng" type = "text" value = "" placeholder = "Longitude" />
<button style = "" onclick = "chk_po(this.form;)">Next</button>
</form>

	<div id="map" style="width: 600px; height: 400px"></div>
	<script src="./js/leaflet/leaflet.js"></script>
	<script>
		var map = L.map('map').setView([51.505, -0.09], 13);
		L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
				'Imagery © <a href="http://mapbox.com">Mapbox</a>',
			id: 'examples.map-i875mjb7'
		}).addTo(map);
		var popup = L.popup();
		function onMapClick(e) {
			popup
				.setLatLng(e.latlng)
				.setContent("You clicked the map at " + e.latlng.toString())
				.openOn(map);
		}
		map.on('click', onMapClick);
	</script>
</body>
</html>

</div> <!-- end "po" -->
<?php
	}		// end if (jc_911)
?>
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
