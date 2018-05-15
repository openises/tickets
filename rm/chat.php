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

$signals_list = $sig_script ."<SELECT class='text_medium' style='width: 200px;' NAME='signals' onFocus = 'clear_to();' onBlur = 'set_to();' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>";
$signals_list .= "<OPTION class='text_medium' VALUE='0' SELECTED>Select signal/code</OPTION>";

$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY 'text' ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
while ($row = stripslashes_deep(mysql_fetch_array($result))) {
	$signals_list .=  "\t<OPTION class='text_medium'  VALUE='{$row['code']}'>{$row['code']}|{$row['text']}</OPTION>\n";		// pipe separator

	}				
$signals_list .= "</SELECT>\n";
?>
<DIV style='width: 98%; height: 80%; display: block;'>
	<DIV>
	<FONT CLASS="header">Chat</FONT> <I>(logged-in: <span id='whos_chatting'></span>)</I><BR />
	</DIV>
	<DIV style='overflow-y: scroll; height: 50%; width: 100%; background-color: #FFFFFF;'>
		<TABLE ID="person" border="0" width='100%' STYLE = 'margin-left:10px;'></TABLE>
	</DIV>
	<CENTER>
	<DIV STYLE = 'background-color: #707070; text-align: left; position: relative; top: 0px; margin: 5px; padding: 5px; color: #FFFFFF; border: 1px outset #DEDEDE; vertical-align: top;'>
		<FORM METHOD="post" NAME='chat_form' onSubmit="return false;">
			<DIV style='width: 20%; display: inline-block; vertical-align: top;'>
				<B>Message</B>
			</DIV>
			<DIV style='width: 80%; display: inline-block;'>
				<INPUT TYPE="text_medium" NAME="frm_message" MAXLENGTH="255" style='width: 45%; font-size: 14px;' value = "" onChange = 'clear_to();' onBlur = 'set_to();' />
				<SPAN id='send_msg_but' class='plain text_medium' style='width: 40px; display: inline-block; padding: 2px; margin: 0px;' onMouseover='do_hover_medium(this.id);' onMouseout='do_plain_medium(this.id);'  onClick="wr_chat_msg(document.forms['chat_form']); set_to();"><?php print get_text('Send');?></SPAN>
				<SPAN id='reset_msg_but' class='plain text_medium' style='width: 40px; display: inline-block; padding: 2px; margin: 0px;' onMouseover='do_hover_medium(this.id);' onMouseout='do_plain_medium(this.id);'  onClick="this.form.reset(); document.chat_form.frm_message.value='';"><?php print get_text('Reset');?></SPAN>
			</DIV>
			<DIV class='spacer'></DIV>
			<DIV style='width: 20%; display: inline-block;'>
				<B>Signals</B>
			</DIV>
			<DIV style='width: 80%; display: inline-block;'>
				<?php print  $signals_list; ?>
				<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0' />
				<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $_SESSION['user_id'];?>' />
				<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>' />
				<BR />
			</DIV>
			<DIV class='spacer'></DIV>	
<?php
			if(isset($_SESSION['user_id'])) {
?>
				<DIV style='width: 100%; display: block;'>
					<SPAN ID = 'bottom_row' style='width: 20%; display: inline-block;'><B>Invite</B></SPAN>				
					<SELECT class='text_medium'  NAME='chat_invite' onFocus = "pause_messages(); $('can_butt').style.display='inline-block';" onChange = "$('send_butt').style.display='inline-block';"> 
						<OPTION class='text_medium'  VALUE="" SELECTED>Select</OPTION>	
						<OPTION class='text_medium'  VALUE=0>All</OPTION>	

<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` != {$_SESSION['user_id']} ";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
							print "\t\t\t<OPTION class='text_medium'  VALUE={$row['id']}>{$row['user']}</OPTION>\n";	
							}
						print "\t\t</SELECT>\n";
?>
					<SPAN id='send_butt' class='plain text_medium' style='width: 70px; display: none; padding: 2px; margin: 0px;' onMouseover='do_hover_medium(this.id);' onMouseout='do_plain_medium(this.id);'  onClick="do_send_inv(document.chat_form.chat_invite.value);"><?php print get_text('Send Invite');?></SPAN>
					<SPAN id='can_butt' class='plain text_medium' style='width: 40px; display: none; padding: 2px; margin: 0px;' onMouseover='do_hover_medium(this.id);' onMouseout='do_plain_medium(this.id);'  onClick="do_can();"><?php print get_text('Cancel');?></SPAN>
					<BR /><SPAN ID= 'sent_msg' STYLE = 'margin-left:30px; display:none;'><B>Invitation Sent!</B></span>
					<SPAN ID= 'help' class='text_medium' STYLE = 'margin-left: 2px; color: red; background-color: #FFFFFF;'></SPAN>
				</DIV>
<?php
				}
?>
		</FORM>
	</DIV>
	<FORM METHOD="post" NAME='chat_form_2' onSubmit="return false;">
		<INPUT TYPE="hidden" NAME = "frm_message" VALUE=' has left this chat.' />
		<INPUT TYPE='hidden' NAME = 'frm_room' VALUE='0' />
		<INPUT TYPE='hidden' NAME = 'frm_user' VALUE='<?php print $_SESSION['user_id'];?>' />
		<INPUT TYPE='hidden' NAME = 'frm_from' VALUE='<?php print $_SERVER['REMOTE_ADDR']; ?>' />
	</FORM>
	</CENTER>
</DIV>