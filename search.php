<?php 
/*
8/28/08 mysql_fetch_array to  mysql_fetch_assoc
9/19/08 add injection protection to query parameters
1/21/09 added show butts - re button menu
2/24/09 added dollar function
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
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
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
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
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
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
<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
</HEAD>

<BODY onLoad = "ck_frames()">
<?php 
	$post_frm_query = (array_key_exists('frm_query', ($_POST))) ? $_POST['frm_query']  : "" ;

	if ($post_frm_query) {
		print "<FONT CLASS='header'>Search results for '$_POST[frm_query]'</FONT><BR /><BR />\n";
		$_POST['frm_query'] = ereg_replace(' ', '|', $_POST['frm_query']);
		if($_POST['frm_search_in'])		//what field are we searching?
			$search_fields = "$_POST[frm_search_in] REGEXP '$_POST[frm_query]'";
		else {
			//list fields and form the query to search all of them
			$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket`");
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++)
    			$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);
			}
		
		if (get_variable('restrict_user_tickets') && !(is_administrator()))		//is user restricted to his/her own tickets?
			$restrict_ticket = "AND owner='$my_session[user_id]'";
		else{
			$restrict_ticket = "";
			}
		
		//tickets
		
		$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";		// 9/19/08

		$query = "SELECT *,UNIX_TIMESTAMP(`problemstart`) AS `problemstart`,UNIX_TIMESTAMP(`problemend`) AS `problemend`,UNIX_TIMESTAMP(`date`) AS `date` ,UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` LIKE " . quote_smart($_POST['frm_querytype']) . " AND " . $search_fields . " " . $restrict_ticket . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;		// 9/19/08
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
//		dump ($query);
		if(mysql_num_rows($result) == 1) {
			// display ticket in whole if just one returned
			$row = stripslashes_deep(mysql_fetch_assoc($result));
//			add_header($_GET['id']);
			add_header($row['id']);
			show_ticket($row['id'], FALSE, $_POST['frm_query']);				// include search term for highlighting

			exit();
			}
		else if (mysql_num_rows($result)) {		// 
			$ticket_found = $counter = 1;
			print "<TABLE BORDER='0'><TR CLASS='even'><TD CLASS='td_header'>Ticket</TD><TD CLASS='td_header'>Date</TD><TD CLASS='td_header'>Description</TD><TD CLASS='td_header'>Status</TD></TR>";
			while($row = stripslashes_deep(mysql_fetch_assoc($result))){				// 8/28/08
				print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD><A HREF='main.php?id={$row['id']}'>#{$row['id']}</A>&nbsp;&nbsp;</TD><TD>".format_date($row['updated'])."&nbsp;&nbsp;&nbsp;</TD><TD><A HREF='main.php?id={$row['id']}'>" . highlight($_POST['frm_query'], $row['scope']) . "</A></TD><TD>" . get_status($row['status'])  . "</TD></TR>\n";				// 2/25/09
				$counter++;
				}
			
			print '</TABLE><BR /><BR />';
			}
		else
			print 'No matching tickets found.  <BR /><BR />';
														//patient data
		$query = "SELECT *,UNIX_TIMESTAMP(date) AS `date` FROM `$GLOBALS[mysql_prefix]patient` WHERE `description` REGEXP " . quote_smart($_POST['frm_query']) . " OR `name` REGEXP " . quote_smart($_POST['frm_query']) ;		// 9/19/08
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
		if(mysql_num_rows($result) && !$ticket_found) {
			// display ticket in whole if just one returned
			add_header($_GET['id']);

			$row = stripslashes_deep(mysql_fetch_assoc($result));
			show_ticket($row[ticket_id],FALSE,$_POST['frm_query']);
			exit();
			}
		else if (mysql_num_rows($result) == 1) 	{
			$counter = 0;
			print '<TABLE BORDER="0"><TR><TD CLASS="td_header">Ticket</TD><TD CLASS="td_header">Date</TD><TD CLASS="td_header">Patient</TD></TR>';
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {				// 8/28/08
				print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD VALIGN='top'><A HREF='main.php?id=$row[ticket_id]'>#$row[ticket_id]</A>&nbsp;&nbsp;</TD><TD NOWRAP VALIGN='top'>".format_date($row[updated])."&nbsp;&nbsp;&nbsp;</FONT></TD><TD><A HREF='main.php?id=$row[ticket_id]'>" . highlight($_POST['frm_query'], $row[description]) . "</A></TD></TR>\n";
				$counter++;
				}
			print '</TABLE>';
			}
		else {
			print 'No matching patient data found.  ';
			}
														//actions
		$query = "SELECT *,UNIX_TIMESTAMP(date) AS `date` FROM `$GLOBALS[mysql_prefix]action` WHERE `description` REGEXP " . quote_smart($_POST['frm_query']);		// 9/19/08
		$result = mysql_query($query) or do_error('','', mysql_error(),basename( __FILE__), __LINE__);
		if(mysql_num_rows($result) && !$ticket_found) {
			// display ticket in whole if just one returned
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			add_header($_GET['id']);
			show_ticket($row[ticket_id], FALSE, $_POST['frm_query']);
			exit();
			}
		else if (mysql_num_rows($result) == 1) 	{
			print '<TABLE BORDER="0"><TR><TD CLASS="td_header">Ticket</TD><TD CLASS="td_header">Date</TD><TD CLASS="td_header">Action</TD></TR>';
			$counter = 0;
			while($row = stripslashes_deep(mysql_fetch_assoc($result))) {				// 8/28/08
				print "<TR CLASS='" . $evenodd[$counter%2] . "' ><TD VALIGN='top'><A HREF='main.php?id=$row[ticket_id]'>#$row[ticket_id]</A>&nbsp;&nbsp;</TD><TD NOWRAP VALIGN='top'>".format_date($row[updated])."&nbsp;&nbsp;&nbsp;</FONT></TD><TD><A HREF='main.php?id=$row[ticket_id]'>" . highlight($_POST['frm_query'], $row[description]) . "</A></TD></TR>\n";
				$counter++;
				}
			print '</TABLE>';
			}
		else {
			print 'No matching actions found.  ';
			}
			
		}				// end if ($_POST['frm_query'])
	else {
		print "<FONT CLASS='header'>Search</FONT>";
		}
?>
<BR /><BR />
<FORM METHOD="post" NAME="queryForm" ACTION="search.php" onSubmit="return validate(document.queryForm)">
<TABLE CELLPADDING="2" BORDER="0">
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
<TR CLASS = "even"><TD></TD><TD ALIGN = "center"><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back()" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit"></TD></TR>
</TABLE></FORM>
</BODY></HTML>
