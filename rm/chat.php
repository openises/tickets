<?php 
/*
9/10/13 New file for Mobile page - variation on existing Tickets chat page
*/
error_reporting(E_ALL);
@session_start();	
require_once('../incs/functions.inc.php');		//7/28/10
//do_login(basename(__FILE__));
extract ($_GET);

$hours = (intval(get_variable('chat_time'))>0)? intval(get_variable('chat_time')) : 4;	// force to default

$old = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($hours*60*60)); // n hours ago

$query  = "DELETE FROM `$GLOBALS[mysql_prefix]chat_messages` WHERE `when`< '" . $old . "'";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				// 11/15/11
$sig_script = "<SCRIPT>
		function set_signal(inval) {
			var temp_ary = inval.split('|', 2);		// inserted separator
			document.chat_form.frm_message.value+=temp_ary[1] + ' ';
			}		// end function set_signal()
		</SCRIPT>
		";

$signals_list = $sig_script ."<SELECT style='width: 200px;' NAME='signals' onFocus = 'clear_to();' onBlur = 'set_to();' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
$signals_list .= "<OPTION VALUE='0' SELECTED>Select signal/code</OPTION>";

$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY 'text' ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
while ($row = stripslashes_deep(mysql_fetch_array($result))) {
	$signals_list .=  "\t<OPTION VALUE='{$row['code']}'>{$row['code']}|{$row['text']}</OPTION>\n";		// pipe separator

	}				
$signals_list .= "</SELECT>\n";
?> 
	<DIV>
	<FONT CLASS="header">Chat</FONT> <I>(logged-in: <span id='whos_chatting'></span>)</I><BR /><BR />
	</DIV>
	<DIV style='overflow-y: scroll; height: 40%; width: 100%; background-color: #FFFFFF;'>
		<TABLE ID="person" border="0" width='100%' STYLE = 'margin-left:10px;'></TABLE>
	</DIV>
	<BR /><BR />
	<CENTER>
	<DIV STYLE = 'background-color: #707070; text-align: left; position: relative; bottom: 0px; margin: 5px; padding: 5px; color: #FFFFFF; border: 2px outset #DEDEDE; vertical-align: top;'>

		<FORM METHOD="post" NAME='chat_form' onSubmit="return false;">
			<NOBR>
			<DIV style='width: 20%; display: inline-block; vertical-align: top;'>
				<B>Message</B>
			</DIV>
			<DIV style='width: 80%; display: inline-block;'>		
				<INPUT TYPE="text" NAME="frm_message" MAXLENGTH="255" SIZE="25" value = "" onChange = "clear_to()"; onBlur = 'set_to()'; ><BR />
				<INPUT TYPE="button" VALUE = "Send" onClick="wr_chat_msg(document.forms['chat_form']);set_to()"  style='margin-left:20px;' >
				<INPUT TYPE="Reset" VALUE = "Reset" style='margin-left:20px;'  onClick="this.form.reset(); document.chat_form.frm_message.value='';" />
			</DIV><BR />
			<DIV class='spacer'></div>
			<DIV style='width: 20%; display: inline-block;'>
				<B>Signals</B>
			</DIV>
			<DIV style='width: 80%; display: inline-block;'>
				<?php print  $signals_list; ?><br />

				<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0'>
				<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $_SESSION['user_id'];?>'>
				<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>'>
			</DIV><BR />
			<DIV class='spacer'></div>	
<?php
			if(isset($_SESSION['user_id'])) {
?>
				<DIV ID = 'botton_row' style='width: 20%; display: inline-block;'>					
					<B>Invite</B>
				</DIV>
				<DIV style='width: 80%; display: inline-block;'>					
					<SELECT NAME='chat_invite' onFocus = "pause_messages(); $('can_butt').style.display='inline';" onChange = "$('send_butt').style.display='inline';;"> 
						<OPTION VALUE="" SELECTED>Select</OPTION>	
						<OPTION VALUE=0>All</OPTION>	

<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` != {$_SESSION['user_id']} ";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
							print "\t\t<OPTION VALUE={$row['id']}>{$row['user']}</OPTION>\n";	
							}
						print "\t</SELECT>\n";
?>
					<INPUT ID = 'send_butt' TYPE='button' VALUE = 'Send invite' style='margin-left:10px; display:none' onClick = "do_send_inv(document.chat_form.chat_invite.value);">
					<SPAN ID= 'sent_msg' STYLE = 'margin-left:60px; display:none;'><B>Invitation Sent!</B></span>
					<INPUT ID = 'can_butt' TYPE='button' VALUE = 'Cancel' style='margin-left:10px; display:none' onClick = "do_can();">
					<BR /><SPAN ID= 'help' STYLE = 'margin-left:150px; color: red; background-color: #FFFFFF;'><B></B></SPAN>
				</DIV>
<?php
				}
?>
		</FORM>
		<FORM METHOD="post" NAME='chat_form_2' onSubmit="return false;">
			<INPUT TYPE="hidden" NAME = "frm_message" VALUE=' has left this chat.'>
			<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0'>
			<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $_SESSION['user_id'];?>'>
			<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>'>
		</FORM>
		<A NAME="bottom"></A>
	</DIV>
	</CENTER>
