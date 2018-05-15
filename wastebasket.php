<?php
/*
*/
error_reporting(E_ALL);

require_once('./incs/functions.inc.php'); 
@session_start();

do_login(basename(__FILE__));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets Personnel Database - Wastebasket</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<meta http-equiv=”X-UA-Compatible” content=”IE=EmulateIE7" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<STYLE type="text/css">
	.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
	.hover_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }				  
	.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #EFEFEF;font-weight: bolder; cursor: pointer; }	
	.plain_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder; cursor: pointer;}					  
	.hover_lo 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 1px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
	.plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;}
	.data 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: yellow;font-weight: bolder;}		
	.message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	#outer { border-radius: 20px 20px;}
	#leftcol { border-radius: 20px 20px;}	
	#map { border: 2px outset #707070; border-radius: 20px 20px;}	
	.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
  	</STYLE>
	<link rel="stylesheet" type="text/css" media="screen" href="css/custom-theme/jquery-ui-1.8.16.custom.css" />
<!--	<link rel="stylesheet" type="text/css" media="screen" href="css/custom.css" />	-->
	<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />	
	<LINK REL=StyleSheet HREF="default.css?version=<?php print time();?>" TYPE="text/css">
	<script src="./js/jquery-1.5.2.min.js" type="text/javascript"></script>
	<script src="./js/i18n/grid.locale-en.js" type="text/javascript"></script>
	<script src="./js/jquery.jqGrid.min.js" type="text/javascript"></script>
	<script src="./js/misc_function.js" type="text/javascript"></script>
	<script src="./js/OpenLayers.js"></script>
	<script type="text/javascript" src="./js/proj4js.js"></script>
	<script type="text/javascript" src="./js/jquery.textarea-expander.js"></script>
	<script type="text/javascript">
	// Here we set a globally the altRows option
	jQuery.extend(jQuery.jgrid.defaults, { altRows:true });
	</script>
	<script>
         jQuery.noConflict();
	</script>
	<script type="text/javascript">
	function $() {															// 12/20/08
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
		
	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}
		
	function do_hover_centered (the_id) {
		CngClass(the_id, 'hover_centered');
		return true;
		}
		
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
		
	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}
		
	function do_plain_centered (the_id) {				// 8/21/10
		CngClass(the_id, 'plain_centered');
		return true;
		}
		
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
		return true;
		}		
	
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	function list_data() {
		var url = "./ajax/waste_list.php";
		var the_text = "";
		sendRequest (url ,list_cb, "");			
		function list_cb(req) {
			var the_ret_arr=JSON.decode(req.responseText);
			var bgc = 0;			
			var bg = "";
			for (var i = 0; i < the_ret_arr.length; i++) {
				var entry = the_ret_arr[i];
				if(bgc == 0) {
					bg = "#FFFFFF";
					} else {
					bg = "#CECECE";
					}
				the_text += "<DIV style='background: " + bg + "; border: 1px outset #707070; vertical-align: middle;'>"
				for (var j = 0; j < entry.length; j++) {
					var id = entry[0];
					var elem = entry[j];
					the_text += "<DIV style='width: 19%; text-align: left; display: inline-block; vertical-align: middle;'>&nbsp;" + elem + "</DIV>";
					}					
				the_text += "<DIV style='width: 19%; text-align: center; display: inline-block; cursor: pointer; vertical-align: middle; padding-top: 6px; padding-bottom: 6px;'><SPAN id='restore" + id + "' class='plain_centered' onMouseOver='do_hover_centered(this.id);' onMouseOut='do_plain_centered(this.id);'  onClick='restore_data(" + id + ");'>Restore</SPAN>";
				the_text += "<SPAN id='delete" + id + "' class='plain_centered' onMouseOver='do_hover_centered(this.id);' onMouseOut='do_plain_centered(this.id);'  onClick='delete_wb_ind(" + id + ");'>Delete</SPAN></DIV>";
				the_text += "</DIV>";
				if(bgc == 0) {
					bgc = 1;
					} else {
					bgc = 0;
					}
				}
			if(the_ret_arr.length == 0) {
				the_text = "<BR /><CENTER>Wastebasket is empty<BR /></CENTER>";
				$('delete_all').style.display = 'none';
				}
			$('list').innerHTML = the_text;
			}				// end function list_cb()
		}				// end function list_data()
		
	function restore_data(id) {
		var url = "./ajax/wb_restore.php?id=" + id;
		sendRequest (url ,rest_cb, "");			
		function rest_cb(req) {
			var the_ret_arr=JSON.decode(req.responseText);
			if(the_ret_arr == 100) {
				alert("Data Restored");				
				}
			if(the_ret_arr == 99) {
				alert("Data Restore failed");
				}
				list_data();				
			}				// end function rest_cb()
		}				// end function restore_data()
		
	function delete_wb() {
		var url = "./ajax/wb_delete.php";
		sendRequest (url ,del_cb, "");			
		function del_cb(req) {
			var the_ret_arr=JSON.decode(req.responseText);
			if(the_ret_arr == 100) {
				alert("Wastebasket Emptied");				
				}
			if(the_ret_arr == 99) {
				alert("Wastebasket couldn't be emptied. Please try again later.");
				}
			list_data();
			}				// end function del_cb()
		}				// end function delete_wb()

	function delete_wb_ind(id) {
		var url = "./ajax/wb_delete.php?id=" + id;
		sendRequest (url ,del_cb, "");			
		function del_cb(req) {
			var the_ret_arr=JSON.decode(req.responseText);
			if(the_ret_arr == 100) {
				alert("Wastebasket Emptied");				
				}
			if(the_ret_arr == 99) {
				alert("Wastebasket couldn't be emptied. Please try again later.");
				}
			list_data();
			}				// end function del_cb()
		}				// end function delete_wb()

	function go_there (where) {
		document.go_Form.action = where;
		document.go_Form.submit();
		}
	</script>

	<BODY onLoad="list_data();">	
	<A NAME='top'>		<!-- 11/11/09 -->
	<DIV ID='outer' style='position: absolute; left: 1%; width: 98%;'>		
		<DIV id='topbar' style='position: fixed; top: 0px; left: 0px; margin-left: 10%; font-size: 14px; z-index: 999; width: 80%; background-color: #DEDEDE; border: 2px outset #CECECE;'>
			<DIV class='tablehead' style='width: 100%; float: right; text-align: center;'><b>Tickets Membership Database - Restore from Wastebasket</b></DIV>	
		</DIV>	

		<DIV ID='leftcol' style='width: 60%; position: absolute; left: 20%; top: 50px; background: #FEF7D6;'>
			<DIV style='position: relative; top: 15px; width: 97%; padding-top: 15px; padding-bottom: 15px;'>
				<DIV style='vertical-align: middle; border: 1px outset #CECECE;'>
					<DIV id='header' style='color: #FFFFFF; background: #707070; font-size: 18px; font-weight: bold; vertical-align: middle; border: 1px outset #CECECE;'>
						<DIV style='width: 19%; text-align: left; display: inline-block;'>ID</DIV> 
						<DIV style='width: 19%; text-align: left; display: inline-block;'>Surname</DIV> 
						<DIV style='width: 19%; text-align: left; display: inline-block;'>Firstname</DIV>
						<DIV style='width: 19%; text-align: left; display: inline-block;'>Callsign</DIV> 
						<DIV style='width: 19%; text-align: center; display: inline-block;'>Action</DIV> 
					</DIV>
				<DIV>
				<DIV id='list' style='vertical-align: middle; border: 1px outset #CECECE;'></DIV><BR /><BR />
				<DIV style='text-align: center; vertical-align: middle; padding: 10px;'>
					<SPAN id='delete_all' class='plain_centered' onMouseOver="do_hover_centered(this.id);" onMouseOut="do_plain_centered(this.id);" onClick='delete_wb();' style='vertical-align: middle;'>Empty Wastebasket</SPAN>
					<SPAN id='back_to_main' class='plain_centered' onMouseOver="do_hover_centered(this.id);" onMouseOut="do_plain_centered(this.id);" onClick="go_there('member.php');" style='vertical-align: middle;'>Return</SPAN>
				</DIV>
			</DIV>
		</DIV>
	</DIV>
	<FORM NAME='go_Form' METHOD="post" ACTION = ""></FORM>
	</BODY>
	</HTML>


