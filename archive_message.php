<?php 
/*
archive_message.php - view and handle an archive message - loads message using ./ajax/arch_msg.php
10/23/12 - new file
*/
@session_start();
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');

$the_contacts = array();
$i = 1;
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_contacts[$i][0] = $row['name'];
	$the_contacts[$i][1] = $row['organization'];	
	$the_contacts[$i][2] = $row['phone'];
	$the_contacts[$i][3] = $row['mobile'];	
	$the_contacts[$i][4] = $row['email'];
	$i++;
	}

$the_users = array();	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_users[] = $row['id'];
	}	

$count_users = count($the_users);

$the_addressbook = "<SELECT NAME='frm_addressbook' onChange='pop_address(this.options[this.selectedIndex].value);'>";
$the_addressbook .= "<OPTION VALUE='0' SELECTED>Select Address from Contacts</OPTION>";
$z=1;
foreach($the_contacts as $val) {
	$the_addressbook .= "<OPTION VALUE=" . $the_contacts[$z][4] . ">" . $the_contacts[$z][0] . "  "  . $the_contacts[$z][4] . "</OPTION>";
	$z++;
	}
$the_addressbook .= "</SELECT>";
if((empty($_POST)) && (!empty($_GET))) {
	$filename = strip_tags($_GET['filename']);	
	$the_row = strip_tags($_GET['rownum']);
	}
$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Message</TITLE>
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT>
var sep = "";
function reply_button() {
	$("print_but").style.display="none";
	$("next_but").style.display="none";
	$("prev_but").style.display="none";	
	$("reply").style.display="block"; 
	$("view").style.display="none";
	$("forward").style.display="none";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";
	$("can_but").style.display="inline-block";		
	$("send_but").style.display="inline-block";		
	$("send_but").onclick=function() {send_button('reply_frm')};
	}
	
function forward_button() {
	$("print_but").style.display="none";
	$("next_but").style.display="none";
	$("prev_but").style.display="none";	
	$("reply").style.display="none"; 
	$("view").style.display="none";
	$("forward").style.display="block";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";
	$("can_but").style.display="inline-block";		
	$("send_but").style.display="inline-block";	
	$("send_but").onclick=function() {send_button('forward_frm')};	
	}
	
function cancel_button() {
	$("reply").style.display="none"; 
	$("view").style.display="block";
	$("forward").style.display="none";
	$("forward_but").style.display="inline-block";
	$("reply_but").style.display="inline-block";	
	$("can_but").style.display="none";		
	$("send_but").style.display="none";			
	}

function send_button(theForm) {
	$("print_but").style.display="none";
	$("next_but").style.display="none";
	$("prev_but").style.display="none";	
	$("reply").style.display="none"; 
	$("view").style.display="none";
	$("forward").style.display="none";
	$("forward_but").style.display="none";
	$("reply_but").style.display="none";	
	$("can_but").style.display="none";		
	$("send_but").style.display="none";	
	$("close_but").style.display="none";		
	$("the_sending").style.display="block";		
	document.forms[theForm].submit();
//	cancel_button();
	refresh_opener("opener");
	}

function pop_address(id) {
	if(document.reply_frm.frm_addrs) {
		if(document.reply_frm.frm_addrs.value == "") {
			sep = "";
			} else {
			sep = "|";
			}
		document.reply_frm.frm_addrs.value = document.reply_frm.frm_addrs.value + sep + id;
		}
	if(document.forward_frm.frm_addrs) {
		if(document.forward_frm.frm_addrs.value == "") {
			sep = "";
			} else {
			sep = "|";
			}	
		document.forward_frm.frm_addrs.value = document.forward_frm.frm_addrs.value + sep + id;
		}		
	}

function go_to(id, screen) {
	var thescreen = screen;
	document.go_frm.id.value = id;
	document.go_frm.screen.value = thescreen;	
	document.go_frm.submit();
	}

function get_themessage(therow, thefile) {
	$('progress').style.display = 'inline'; 
	var randomnumber=Math.floor(Math.random()*99999999);
	var type_flag;
	var flag_format = "";
	var the_string = "";
	var url ='./ajax/view_arch_msg.php?filename=' + thefile +'&rownum=' + therow + '&version=' + randomnumber;
	sendRequest (url, mess_cb, "");
	function mess_cb(req) {
		var the_message=JSON.decode(req.responseText);
		var the_msg_id = the_message[0];
		var the_tkt_id = the_message[1];
		var the_type = the_message[2];
		if(the_type == "OS") {
			type_flag = "Outgoing SMS";
			flag_bg = "orange";
			flag_fg = "white";
			$('reply_but').style.display = 'none';
			} else if(the_type == "IS") {
			type_flag = "Incoming SMS";
			flag_bg = "white";
			flag_fg = "orange";			
			} else if(the_type == "IE") {
			type_flag = "Incoming Email";
			flag_bg = "white";
			flag_fg = "blue";			
			} else if(the_type == "OE") {
			type_flag = "Outgoing Email";
			flag_bg = "blue";
			flag_fg = "white";			
			} else {
			type_flag = "Unknown Message Type";
			}
		$('the_type').style.color = flag_fg;
		$('the_type').style.backgroundColor = flag_bg;		
		$('the_type2').style.color = flag_fg;
		$('the_type2').style.backgroundColor = flag_bg;
		$('the_type3').style.color = flag_fg;
		$('the_type3').style.backgroundColor = flag_bg;		
		var the_from_name = the_message[3];
		$('the_recipients').innerHTML = the_message[4];
		$('the_subject').innerHTML = the_message[5];
		$('the_message').innerHTML = the_message[6];
		$('the_date').innerHTML = the_message[7];
		$('the_type').innerHTML = type_flag;		
		$('the_type2').innerHTML = type_flag;		
		$('the_type3').innerHTML = type_flag;				
		var the_owner = the_message[8];
		var the_id = the_message[9];
		$('the_readby').innerHTML = the_message[10];
		$('the_from_add').innerHTML = the_message[11];
		document.forms['reply_frm'].frm_subject.value = the_message[5];
		document.forms['reply_frm'].frm_message.value = the_message[6];		
		document.forms['reply_frm'].frm_addrs.value = the_message[11];	
		document.forms['reply_frm'].frm_theothers.value = the_message[12];	
		document.forms['reply_frm'].frm_ticket_id.value = the_tkt_id;		
		document.forms['reply_frm'].frm_resp_id.value = the_message[13];			
		if(the_type == "IS") {
			document.reply_frm.frm_use_smsg.checked = true;
			document.forward_frm.frm_use_smsg.checked = true;			
			}
		document.forms['forward_frm'].frm_subject.value = the_message[5];
		document.forms['forward_frm'].frm_message.value = the_message[6];		
		document.forms['forward_frm'].frm_theothers.value = the_message[12];
		document.forms['forward_frm'].frm_ticket_id.value = the_tkt_id;	
		document.forms['forward_frm'].frm_resp_id.value = the_message[13];
		var numrows = parseInt(the_message[14]);
		if(parseInt(therow) <= numrows) {
			var nextrow = parseInt(therow) + 1;
			the_string = "<SPAN class='plain' id='next_but' onMouseover='do_hover(this);' onMouseout='do_plain(this);'  style='float: right; color: #000000; display: inline-block; vertical-align: middle;' onClick='get_themessage(\"" + nextrow + "\", \"" + thefile + "\");'>Next</SPAN>";
			$('thenext').innerHTML = the_string;
			} else {
			$('thenext').innerHTML = "";
			}
		if(parseInt(therow) >= 2) {
			var prevrow = parseInt(therow) - 1;
			the_string = "<SPAN class='plain' id='prev_but' onMouseover='do_hover(this);' onMouseout='do_plain(this);'  style='float: right; color: #000000; display: inline-block; vertical-align: middle;' onClick='get_themessage(\"" + prevrow + "\", \"" + thefile + "\");'>Prev</SPAN>";
			$('theprev').innerHTML = the_string;
			} else {
			$('theprev').innerHTML = "";	
			}
		}
	$('progress').style.display = 'none'; 
	}		


</SCRIPT>
</HEAD>

<?php
if(!empty($_POST)) {
	$the_separator = "\n\n------------------Original Message  ------------------\n\n";
	if((isset($_POST['frm_use_smsg'])) && ($_POST['frm_use_smsg'] == 1)) {
		do_send ("", $_POST['frm_addrs'], "Tickets CAD",  $_POST['frm_reply'] . $the_separator . $_POST['frm_message'], $_POST['frm_ticket_id'], $_POST['frm_resp_id'] );		// - ($to_str, $to_smsr, $subject_str, $text_str, %ticket_id, $responder_id ) 
		} else {
		$the_addresses = (!empty($_POST['frm_theothers'])) ? $_POST['frm_addrs'] . "|" . $_POST['frm_theothers'] : $_POST['frm_addrs'];
		do_send ($the_addresses, "", "Tickets CAD",  $_POST['frm_reply'] . $the_separator . $_POST['frm_message'], $_POST['frm_ticket_id'], $_POST['frm_resp_id'] );		// - ($to_str, $to_smsr, $subject_str, $text_str, %ticket_id, $responder_id ) 
		}
?>
	<BODY>
		<CENTER>
		<DIV style='position: absolute; top: 50px; left: 220px; font-size: 20px; font-weight: bold;'><?php print "Message Sent";?></DIV>
		<DIV ID='controls' style='position: relative; top: 150px; left: 5%; display: block; text-align: center; width: 20%;'>
		<SPAN id='close_but' class='plain' style='float: left; display: inline-block;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='window.close();'>CLOSE</SPAN>
		</DIV>	
		</CENTER>	
	</BODY>
	</HTML>
 <?php
	exit();
	}

if(empty($_POST)) {	
//$opener = $_GET['screen'];
?>
	<BODY onLoad="get_themessage('<?php print $the_row;?>', '<?php print $filename;?>')">
		<CENTER>	
		<DIV id='progress' style=position: fixed; left: 45%; top: 45%; z-index: 999999; display: none;'><img src="./images/progress.gif"></DIV>
		<DIV ID='controls' style='position: absolute; top: 10px; left: 20%; display: block; text-align: center; width: 60%; height: 10px; vertical-align: middle;'>
			<SPAN id='print_but' class='plain' style='float: left; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='window.print();'>Print</SPAN>			
			<SPAN id='reply_but' class='plain' style='float: left; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='reply_button();'>Reply</SPAN>			
			<SPAN id='forward_but' class='plain' style='float: left; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='forward_button();'>Forward</SPAN>			
			<SPAN id='send_but' class='plain' style='float: left; display: none; vertical-align: middle;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='send_button();'>Send</SPAN>		
			<SPAN id='close_but' class='plain' style='float: right; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='window.close();'>CLOSE</SPAN>
			<SPAN id='can_but' class='plain' style='float: right; display: inline-block; display: none;' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='cancel_button();'>Cancel</SPAN>
			<DIV id='thenext'></DIV>
			<DIV id='theprev'></DIV>
		</DIV>	
		</CENTER>		
		<DIV id='outer' style='position: relative; top: 50px; height: 100%; display: block; margin: 2%;'>
			<DIV id='view' style='padding: 1%; margin: 2%; position: absolute; width: 85%; max-height: 90%; border: 2px outset #707070; padding: 10px; overflow-y: auto;'>
					<DIV style='font-size: 24px; color: #000000; text-align: center;'>VIEW</DIV>

					<DIV id='the_type' style='text-align: center; font-size: 16px; padding: 5px;'></DIV><BR /><BR />
					
 					<DIV style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Already Read by:</DIV>           
					<DIV id='the_readby' style='background-color: #FFFFFF; color: #707070; width: 100%; height: 20px; border: 1px inset #707070;'></DIV><BR /><BR /> 
					
					<DIV style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>From:</DIV>           
					<DIV id='the_from_add' style='background-color: #FFFFFF; color: #707070; width: 100%; height: 20px; border: 1px inset #707070;'></DIV><BR /><BR />      
	
					<DIV style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>To:</DIV>           
					<DIV id='the_recipients' style='background-color: #FFFFFF; color: #707070; width: 100%; height: 20px; border: 1px inset #707070;'></DIV><BR /><BR />      
	
					<DIV style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Date:</DIV>           
					<DIV id='the_date' style='background-color: #FFFFFF; color: #707070; width: 100%; height: 20px; border: 1px inset #707070;'></DIV><BR /><BR />      
  
					<DIV style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Subject:</DIV>           
					<DIV id='the_subject' style='background-color: #FFFFFF; color: #707070; width: 100%; height: 20px; border: 1px inset #707070;'></DIV><BR /><BR />      

					<DIV style='background-color: #707070; color: #FFFFFF; width: 100%; font-weight: bold;'>Message:</DIV>   					
					<DIV id='the_message' style='background-color: #FFFFFF; color: #707070; width: 100%; min-height: 100px; overflow-y: auto; border: 1px inset #707070;'></DIV><BR />     
			</DIV>
			<DIV id='reply' style='position: relative; display: none; width: 100%;'>
				<table> 
					<FORM NAME="reply_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
					<tr>
						<th COLSPAN=99>REPLY</th>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td id='the_type2'></td>
					</tr>
					<tr>    
						<td>&nbsp;</td>					
						<td><?php print $the_addressbook;?></td>           
					</tr>   					
					<tr>          
						<td>To:</td>           
						<td><INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE=""></td>      
					</tr>      
					<tr>           
						<td>Date:</td>           
						<td><INPUT TYPE='text' NAME='frm_date' size='60' VALUE="<?php print $now; ?>"></td>      
					</tr>     
					<tr>           
						<td>Subject:</td>           
						<td><INPUT TYPE='text' NAME='frm_subject' size='60' VALUE=""></td>      
					</tr> 	
					<tr>       
						<td>Original Message:</td>   					
						<td><TEXTAREA NAME="frm_message" readonly="readonly" COLS=60 ROWS=5 style='background-color: #F0F0F0 ; color: #707070; overflow-y: auto; overflow-x: hidden;'></TEXTAREA></td>     
					</tr> 					
					<tr>       
						<td>Response:</td>   					
						<td><TEXTAREA NAME="frm_reply" COLS=60 ROWS=15></TEXTAREA></td>     
					</tr> 
					<tr>
						<td>Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: </td>
						<td><INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE=1></td>
					</tr>		
					<INPUT TYPE="hidden" NAME = 'frm_theothers' VALUE=""/>			
					<INPUT TYPE="hidden" NAME = 'frm_ticket_id' VALUE=""/>	
					<INPUT TYPE="hidden" NAME = 'frm_resp_id' VALUE=""/>						
					</FORM>
				</table>
			</DIV>
			<DIV id='forward' style='position: relative; display: none; width: 100%;'>
				<table> 
					<FORM NAME="forward_frm" METHOD="post" ACTION = "<?php print basename( __FILE__); ?>">
					<tr>
						<th COLSPAN=99>FORWARD</th>
					</tr>
					<tr>
						<td>&nbsp;</td>					
						<td id='the_type3'></td>
					</tr>		
					<tr> 
						<td>&nbsp;</td>					
						<td><?php print $the_addressbook;?></td>           
					</tr>      					
					<tr>          
						<td>To:</td>           
						<td><INPUT TYPE='text' NAME='frm_addrs' size='60' VALUE=""></td>      
					</tr>      
					<tr>           
						<td>Date:</td>           
						<td><INPUT TYPE='text' NAME='frm_date' size='60' VALUE="<?php print $now; ?>"></td>      
					</tr>     
					<tr>           
						<td>Subject:</td>           
						<td><INPUT TYPE='text' NAME='frm_subject' size='60' VALUE=""></td>      
					</tr> 	
					<tr>   
						<td>Original Message:</td>          					
						<td><TEXTAREA NAME="frm_message" readonly="readonly" COLS=60 ROWS=5 style='background-color: #F0F0F0; color: #707070; overflow-y: auto; overflow-x: hidden;'></TEXTAREA></td>      
					</tr>
					<tr>   
						<td>Your Message:</td>          					
						<td><TEXTAREA NAME="frm_reply" COLS=60 ROWS=15></TEXTAREA></td>      
					</tr>					
					<tr>
						<td>Use <?php get_provider_name(get_msg_variable('smsg_provider'));?>?: </td>
						<td><INPUT TYPE='checkbox' NAME='frm_use_smsg' VALUE=1></td>
					</tr>		
					<INPUT TYPE="hidden" NAME = 'frm_theothers' VALUE=""/>		
					<INPUT TYPE="hidden" NAME = 'frm_ticket_id' VALUE=""/>
					<INPUT TYPE="hidden" NAME = 'frm_resp_id' VALUE=""/>						
					</FORM>
				</table>
			</DIV>
			<FORM NAME="go_frm" METHOD="get" ACTION = "<?php print basename( __FILE__); ?>">	
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			<INPUT TYPE='hidden' NAME='screen' VALUE=''>			
			</FORM>
		</DIV>

	</BODY>
<?php
}
?>	
<BODY>
<DIV id = 'the_sending' style='position: absolute; top: 50px; left: 220px; font-size: 20px; font-weight: bold; display: none;'><?php print "Please Wait";?><BR /><BR /><CENTER><img src="./images/pleasewait.gif" alt="Please Wait"/></CENTER></DIV>	
</BODY>
</HTML>
