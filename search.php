<?php

/*
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
*/
error_reporting(E_ALL);

session_start();
//require_once($_SESSION['fip']);				// 7/28/10
require_once('./incs/functions.inc.php');		// 9/29/10
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);					// 9/29/10
if ($istest) {
	dump ($_POST);
	dump ($_GET);
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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT>

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


function $() {									// 2/11/09
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

<BODY onLoad = "ck_frames()">
<?php 
	$post_frm_query = (array_key_exists('frm_query', $_POST)) ? strip_tags($_POST['frm_query']) : FALSE ;		// 1/6/2013

	if ($post_frm_query) {


//		$query_str = quote_smart(trim($_POST['frm_query']));		// 7/20/10

		print "<BR /><SPAN STYLE = 'margin-left:80px;'><FONT CLASS='header'>Search results for '$_POST[frm_query]'</FONT></SPAN><BR /><BR />\n";
		$_POST['frm_query'] = ereg_replace(' ', '|', $_POST['frm_query']);
		$query_str = quote_smart(trim(ereg_replace(' ', '|', $_POST['frm_query'])));
		if($_POST['frm_search_in'])	{								//what field are we searching?
			$search_fields = "{$_POST['frm_search_in']} REGEXP '$_POST[frm_query]'";	//
			}
		else {							//list fields and form the query to search all of them
			$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket`");
			$search_fields = "";
			$ok_types = array("string", "blob");
			for ($i = 0; $i < mysql_num_fields($result); $i++) {
				if (in_array (mysql_field_type($result, $i), $ok_types )) {
    				$search_fields .= mysql_field_name($result, $i) ." REGEXP {$query_str} OR ";
    				}
    			}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);		// drop trailing OR
			}
		if (get_variable('restrict_user_tickets') && !(is_administrator()))		//is user restricted to his/her own tickets?
			$restrict_ticket = "AND owner='{$_SESSION['user_id']}'";
		else{
			$restrict_ticket = "";
			}
		$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";		// 9/19/08

// ___________________________________  NEW STUFF __________________	9/30/10	
		$id_stack= array();
		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]ticket`  WHERE `status` <> {$GLOBALS['STATUS_RESERVED']} AND `status` LIKE " . quote_smart($_POST['frm_querytype']) . " AND " . $search_fields . " " . $restrict_ticket . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;		// 9/19/08
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);

		$tick_hits = mysql_affected_rows();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($id_stack, $row['id']);
			}
		$query = "SELECT `ticket_id` FROM `$GLOBALS[mysql_prefix]patient` 
			WHERE `description` REGEXP " . quote_smart($_POST['frm_query']) . " OR `name` REGEXP " . quote_smart($_POST['frm_query']) ;		
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
		$per_hits = mysql_affected_rows();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($id_stack, $row['ticket_id']);
			}

		$query = "SELECT `ticket_id` FROM `$GLOBALS[mysql_prefix]action` 
			WHERE `description` REGEXP " . quote_smart($_POST['frm_query']);		// 9/19/08
		$result = mysql_query($query) or do_error('','', mysql_error(),basename( __FILE__), __LINE__);
		$act_hits = mysql_affected_rows();
		
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			array_push($id_stack, $row['ticket_id']);
			}

		if (empty($id_stack )){
			print "<SPAN STYLE = 'margin-left:80px'><B>No matches found</B></SPAN><BR /><BR />";
			}		
		else {
			$id_stack = array_unique($id_stack);		// at least one
	
			$in_str = $sep = "";
			for ($i=0; $i< count($id_stack); $i++) {
				$in_str .= "{$sep}'{$id_stack[$i]}'";
				$sep = ", ";
				}			
	
			$query = "SELECT `id`, UNIX_TIMESTAMP(`problemstart`) AS `problemstart`, UNIX_TIMESTAMP(`updated`) AS `updated`, `scope`, `status`, `severity`,
				CONCAT_WS(' ',`street`,`city`,`state`) AS `addr`
				FROM `$GLOBALS[mysql_prefix]ticket` 
				WHERE `status` <> {$GLOBALS['STATUS_RESERVED']} 
				AND `id` IN ({$in_str})
				AND `status` LIKE " . quote_smart($_POST['frm_querytype']) . "
				ORDER BY `severity` DESC, `problemstart` ASC";
	//		dump ($query);
	
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			
	// ___________________________________  END NEW STUFF __________________		
//			dump(mysql_num_rows($result));
			
			if(mysql_num_rows($result) == 1) {
				// display ticket in whole if just one returned
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				add_header($row['id']);
				show_ticket($row['id'], FALSE, $_POST['frm_query']);				// include search term for highlighting
				exit();
				}
			elseif (mysql_num_rows($result) == 0) {
				print "<SPAN STYLE = 'margin-left:80px'><B>No matches found</B></SPAN><BR /><BR />";
			
				}
			else {		//  more than one, list them
				print "<SPAN STYLE = 'margin-left:80px'><B>Matches</B>: tickets {$tick_hits}, actions {$act_hits}, persons {$per_hits}</SPAN><BR /><BR />";
			
				print "<TABLE BORDER='0'><TR CLASS='even'>
					<TD CLASS='td_header'><SPAN STYLE = 'margin-left:2px;'>Ticket</SPAN></TD>
					<TD CLASS='td_header'><SPAN STYLE = 'margin-left:20px;'>Opened</SPAN></TD>
					<TD CLASS='td_header'><SPAN STYLE = 'margin-left:20px;'>Description</SPAN></TD>
					<TD CLASS='td_header'><SPAN STYLE = 'margin-left:20px;'>Location</SPAN></TD></TR>";
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
	
					print "<TR CLASS='{$evenodd[$counter%2]}' onClick = \"Javascript: self.location.href = 'main.php?id={$row['id']}';\">
						<TD CLASS='$severityclass'>#{$row['id']}</TD>
						<TD CLASS='$severityclass'><SPAN STYLE = 'margin-left:10px;'>" . format_date($row['problemstart'])."</SPAN></TD>
						<TD CLASS='$severityclass'><SPAN STYLE = 'margin-left:10px;'>{$strike}" . shorten(highlight($_POST['frm_query'], $row['scope']), 120) . "{$strikend}</SPAN></TD>
						<TD CLASS='$severityclass'><SPAN STYLE = 'margin-left:10px;'>{$strike}" . shorten(highlight($_POST['frm_query'], $row['addr']), 120) . "{$strikend}</SPAN></TD>
						</TR>\n";				// 2/25/09
					$counter++;
					}			
				print '</TABLE><BR /><BR />';
				}			// end if/else
			}			// end if/else (empty($id_stack ))
		}				// end if ($_POST['frm_query'])
	else {
		print "<SPAN STYLE = 'margin-left:86px'><FONT CLASS='header'>Search</FONT></SPAN>";
		}
?>
<BR /><BR />
<FORM METHOD="post" NAME="queryForm" ACTION="search.php" onSubmit="return validate(document.queryForm)">
<TABLE CELLPADDING="2" BORDER="0" STYLE = 'margin-left:80px;'>
<TR CLASS = "even"><TD VALIGN="top" CLASS="td_label">Query: &nbsp;</TD><TD><INPUT TYPE="text" SIZE="40" MAXLENGTH="255" VALUE="<?php print $post_frm_query;?>" NAME="frm_query"></TD></TR>
<TR CLASS = "odd"><TD VALIGN="top" CLASS="td_label">Search in: &nbsp;</TD><TD>
<SELECT NAME="frm_search_in">
<OPTION VALUE="" checked>All</OPTION>
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
</SELECT></TD></TR>
<TR CLASS = "even"><TD VALIGN="top" CLASS="td_label">Order By: &nbsp;</TD><TD>
<SELECT NAME="frm_ordertype">
<OPTION VALUE="date">Issue Date</OPTION>
<OPTION VALUE="problemstart">Problem Starts</OPTION>
<OPTION VALUE="problemend">Problem Ends</OPTION>
<OPTION VALUE="affected">Affected</OPTION>
<OPTION VALUE="scope">Incident</OPTION>
<OPTION VALUE="owner">Owner</OPTION>
</SELECT>&nbsp;Descending: <INPUT TYPE="checkbox" NAME="frm_order_desc" VALUE="DESC" CHECKED></TD></TR>
<TR CLASS = "odd"><TD VALIGN="top" CLASS="td_label">Status: &nbsp;</TD><TD>
<INPUT TYPE="radio" NAME="frm_querytype" VALUE="%" CHECKED> All<BR />
<INPUT TYPE="radio" NAME="frm_querytype" VALUE="<?php print $STATUS_OPEN;?>"> Open<BR />
<INPUT TYPE="radio" NAME="frm_querytype" VALUE="<?php print $STATUS_CLOSED;?>"> Closed<BR />
</TD></TR>
<TR CLASS = "even"><TD></TD><TD ALIGN = "left"><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back()" / ><INPUT TYPE="reset" VALUE="Reset" STYLE ="margin-left:20px" /><INPUT TYPE="submit" VALUE="Next"  STYLE ="margin-left:20px" /></TD></TR>
</TABLE></FORM>
</BODY></HTML>
