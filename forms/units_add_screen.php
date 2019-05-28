<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
print do_calls();		// call signs to JS array for validation
?>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var fac_lat = [];
var fac_lng = [];
<?php
$query = "SELECT `id`, `lat`, `lng` FROM `$GLOBALS[mysql_prefix]facilities`";
$result = mysql_query($query);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	print "\tfac_lat[" . $row['id'] . "] = " . $row['lat'] . " ;\n";
	print "\tfac_lng[" . $row['id'] . "] = " . $row['lng'] . " ;\n";
	}
?>

var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});
			
var colors = new Array ('odd', 'even');
var fields = ["name",
			"about",
			"location",
			"description",
			"phone",
			"capability",
			"contact_name",
			"contact_email",
			"cellphone",
			"filename"];
var medfields = ["city",
				"handle",
				"ringfence",
				"exclusion",
				"unittype",
				"unitstatus",
				"track",
				"atfacility",
				"smsgid",
				"grid",
				"callsign",
				"file"];
var smallfields = ["show_lat", "show_lng"];

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
	mapWidth = viewportwidth * .40;
	mapHeight = mapWidth * .9;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = listwidth + "px";
	$('rightcol').style.width = listwidth + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('map_legend').style.width = mapWidth + "px";
	$('icon_legend').style.width = mapWidth + "px";
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}
	load_exclusions();
	load_ringfences();
	load_basemarkup();
	load_groupbounds();
	map.invalidateSize();
	}

var icons=[];
icons[0] = 											 4;	// units white
icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black

var unit_icons=[];
unit_icons[0] = 0;
unit_icons[4] = 4;

var fac_icons=[];
fac_icons[0] = 1;
fac_icons[1] = 2;
fac_icons[2] = 3;
fac_icons[3] = 4;	
fac_icons[4] = 5;
fac_icons[5] = 6;
fac_icons[6] = 7;
fac_icons[7] = 8;

var max_zoom = <?php print get_variable('def_zoom');?>;

var map;				// make globally visible
var myMarker;
var sortby = '`date`';	//	10/23/12
var sort = "DESC";	//	10/23/12
var columns = "<?php print get_msg_variable('columns');?>";	//	10/23/12
var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	10/23/12
var thescreen = 'ticket';	//	10/23/12
var thelevel = '<?php print $the_level;?>';
var rmarkers = new Array();			//	Responder Markers array
var cmarkers = new Array();			//	conditions markers array
var the_icon;
var currentPopup;
var marker;
var markers;
var zoom = <?php print get_variable('def_zoom');?>;
var locale = <?php print get_variable('locale');?>;
var my_Local = <?php print get_variable('local_maps');?>;
var lon = <?php print get_variable('def_lng');?>;
var lat = <?php print get_variable('def_lat');?>;

function to_routes(id) {
	document.routes_Form.ticket_id.value=id;
	document.routes_Form.submit();
	}

function to_fac_routes(id) {
	document.fac_routes_Form.fac_id.value=id;
	document.fac_routes_Form.submit();
	}

function validate(theForm) {						// Responder form contents validation
	if (theForm.frm_remove) {
		if (theForm.frm_remove.checked) {
			var str = "Please confirm removing '" + theForm.frm_name.value + "'";
			if(confirm(str)) 	{
				theForm.submit();
				return true;}
			else 				{return false;}
			}
		}
	theForm.frm_mobile.value = (theForm.frm_mob_disp.checked)? 1:0;
	theForm.frm_multi.value =  (theForm.frm_multi_disp.checked)? 1:0;

	theForm.frm_direcs.value = (theForm.frm_direcs_disp.checked)? 1:0;
	var errmsg="";

	if (theForm.frm_name.value.trim()=="" && !theForm.frm_memname)							{errmsg+="Unit NAME is required.\n";}
	if (theForm.frm_handle.value.trim()=="")												{errmsg+="Unit HANDLE is required.\n";}
	if (theForm.frm_icon_str.value.trim()=="")												{errmsg+="Unit ICON is required.\n";}

	if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)					{errmsg+="Unit TYPE selection is required.\n";}
	if (any_track(theForm)){
		if (theForm.frm_callsign.value.trim()==""){
			if(theForm.frm_track_disp.selectedIndex == 8) {
				} else {
				errmsg+="License information is required with Tracking.\n";
				}
			}
		} else {
		if (!(theForm.frm_callsign.value.trim()==""))										{errmsg+="License information used ONLY with Tracking.\n";}
		}


	if (theForm.frm_un_status_id.options[theForm.frm_un_status_id.selectedIndex].value==0)	{errmsg+="Unit STATUS selection is required.\n";}
	
	if (theForm.frm_descr.value.trim()=="")													{errmsg+="Unit DESCRIPTION is required with Tracking.\n";}
	if(allow_nogeo == "0") {
		if (theForm.frm_lat.value.trim().length == 0) 	{
			errmsg+="Map location is required for non-mobile units.\n";
			}
		} else {
		if(theForm.frm_lat.value.trim().length == 0) {
			theForm.frm_lat.value = def_lat;
			theForm.frm_lng.value = def_lng;
			}
		}			
	
	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		} else {
		theForm.submit();
		}
	}				// end function validate(theForm)
	
function any_track(theForm) {
	return (theForm.frm_track_disp.selectedIndex > 0);
	}

var track_captions = ["", "Callsign", "Device key", "Userid ", "Userid ", "Badge", "Device", "Userid", "Automatic"];	//	9/6/13
function do_tracking(theForm, theVal) {							// 7/10/09, 7/24/09 added specific code to switch off unselected,
	theForm.frm_aprs.value=theForm.frm_instam.value=theForm.frm_locatea.value=theForm.frm_gtrack.value=theForm.frm_glat.value=theForm.frm_ogts.value=theForm.frm_t_tracker.value=theForm.frm_mob_tracker.value=theForm.frm_xastir_tracker.value=theForm.frm_followmee_tracker.value=theForm.frm_traccar.value=theForm.frm_javaprssrvr.value=0;		//	 9/6/13
	switch(parseInt(theVal)) {
		case <?php print $GLOBALS['TRACK_NONE'];?>:		 	break;
		case <?php print $GLOBALS['TRACK_APRS'];?>:		 	theForm.frm_aprs.value=1;	 break;
		case <?php print $GLOBALS['TRACK_INSTAM'];?>:	 	theForm.frm_instam.value=1;	 break;
		case <?php print $GLOBALS['TRACK_LOCATEA'];?>:	 	theForm.frm_locatea.value=1; break;
		case <?php print $GLOBALS['TRACK_GTRACK'];?>:	 	theForm.frm_gtrack.value=1;  break;
		case <?php print $GLOBALS['TRACK_GLAT'];?>:		 	theForm.frm_glat.value=1;	 break;
		case <?php print $GLOBALS['TRACK_T_TRACKER'];?>:	theForm.frm_t_tracker.value=1;	break;
		case <?php print $GLOBALS['TRACK_OGTS'];?>:		 	theForm.frm_ogts.value=1;	 break;
		case <?php print $GLOBALS['TRACK_MOBILE'];?>:	 	theForm.frm_mob_tracker.value=1;	 break;	//	9/6/13
		case <?php print $GLOBALS['TRACK_XASTIR'];?>:	 	theForm.frm_xastir_tracker.value=1;	 break;	//	1/30/14
		case <?php print $GLOBALS['TRACK_FOLLOWMEE'];?>:	theForm.frm_followmee_tracker.value=1;	 break;	//	1/30/14
		case <?php print $GLOBALS['TRACK_TRACCAR'];?>:	 	theForm.frm_traccar.value=1;	 break;	//	6/29/17
		case <?php print $GLOBALS['TRACK_JAVAPRSSRVR'];?>:	theForm.frm_javaprssrvr.value=1;	 break;	//	6/29/17
		default:  alert("error <?php print __LINE__;?>");
		}		// end switch()
	}				// end function do tracking()		

function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}
	
function collect(){				// constructs a string of id's for deletion
	var str = sep = "";
	for (i=0; i< document.del_Form.elements.length; i++) {
		if (document.del_Form.elements[i].type == 'checkbox' && (document.del_Form.elements[i].checked==true)) {
			str += (sep + document.del_Form.elements[i].name.substring(1));
			sep = ",";
			}
		}
	document.del_Form.idstr.value=str;
	}

function all_ticks(bool_val) {
	for (i=0; i< document.del_Form.elements.length; i++) {
		if (document.del_Form.elements[i].type == 'checkbox') {
			document.del_Form.elements[i].checked = bool_val;
			}
		}			// end for (...)
	}				// end function all ticks()

function do_lat (lat) {							// 9/14/08
	document.res_edit_Form.frm_lat.value=lat.toFixed(6);			// 9/9/08
	document.res_edit_Form.show_lat.disabled=false;
	document.res_edit_Form.show_lat.value=do_lat_fmt(document.forms[0].frm_lat.value);
	document.res_edit_Form.show_lat.disabled=true;
	}
function do_lng (lng) {
	document.res_edit_Form.frm_lng.value=lng.toFixed(6);			// 9/9/08
	document.res_edit_Form.show_lng.disabled=false;
	document.res_edit_Form.show_lng.value=do_lng_fmt(document.forms[0].frm_lng.value);
	document.res_edit_Form.show_lng.disabled=true;
	}
	
function do_fac_to_loc(index){			// 9/22/09
	var curr_lat = fac_lat[index];
	var curr_lng = fac_lng[index];
	do_lat(curr_lat);
	do_lng(curr_lng);
	if(marker) {map.removeLayer(marker);}
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});
	var LatLng = new L.LatLng(curr_lat, curr_lng);
	marker = new L.marker(LatLng, {icon:icon, draggable:'false'});
	marker.addTo(map);
	map.setView(LatLng, initZoom);
	}					// end function do_fac_to_loc	

</SCRIPT>
</HEAD>
<BODY onLoad='set_size();'>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'>
			<FORM NAME= "res_add_Form" ENCTYPE="multipart/form-data" METHOD="POST" ACTION="<?php print $_SESSION['unitsfile'];?>?func=responder&goadd=true">
			<TABLE BORDER="0" ID='addform'>
			<TR CLASS='even'>
				<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
			</TR>
			<TR CLASS='even'>
				<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
					<SPAN CLASS='text_green text_biggest'>Add Unit</SPAN>
					<BR />
					<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
					<BR />
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>
<?php
			if($useMdb == "0") {
?>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Roster User">Roster User</A>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<?php print get_roster();?>
						<DIV id='user_details' style='width: 300px; vertical-align: top; display: none; font-size: 1.3em; word-wrap: normal;'></DIV>
					</TD>
				</TR>
<?php
				} else {
?>
				<INPUT TYPE="hidden" NAME="frm_roster_id" VALUE="0" />
<?php
				}

			if($useMdb == "1" && $useMdbContact == "1") {
?>
				<TR ID = 'members_row' CLASS = "odd">
					<TD CLASS="td_label text top">
						<A CLASS="td_label text" HREF="#" TITLE="Members on Unit">Members Assigned to Unit</A>:<BR /><SPAN CLASS='text_white'>Red shows members already assigned to other units.</SPAN>
					</TD>
					<TD>&nbsp;</TD>				
					<TD COLSPAN=2 CLASS='td_data_wrap text'>
						<?php print get_responder_members(NULL);?>
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>
<?php				
				}
?>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Unit Name - enter, well, the name!">Name</A>:<font color='red' size='-1'>*</font>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='name' MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="" />
				</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Handle - local rules, could be callsign or badge number, generally for radio comms use">Handle</A>:&nbsp;<font color='red' size='-1'>*</font>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='handle' MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="" />
					<SPAN STYLE = 'margin-left:30px'  CLASS="td_label text"> Icon: </SPAN>&nbsp;<FONT COLOR='red' size='-1'>*</FONT>&nbsp;<INPUT TYPE = "text" NAME = "frm_icon_str" SIZE = 3 MAXLENGTH=3 VALUE="" />
				</TD>
			</TR>

<?php
			if(get_num_groups()) {
				if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>		
			<TR CLASS='odd' VALIGN="top">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>:
					<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
					<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<DIV id='checkButts' style='display: none;'>
						<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
						<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>	
					</DIV>
<?php
					$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	4/18/11
					print get_user_group_butts(($_SESSION['user_id']));	//	4/18/11		
?>	
				</TD>
			</TR>
<?php				
				} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
?>		
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>: 
					<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
					<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<DIV id='checkButts' style='display: none;'>
						<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
						<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>	
					</DIV>
<?php
					$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
					print get_user_group_butts(($_SESSION['user_id']));	//	4/18/11		
?>	
				</TD>
			</TR>
<?php
			} else {
?>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
				<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>: 
					<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
					<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<DIV id='checkButts' style='display: none;'>
						<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
						<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>		
					</DIV>
<?php
					$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
					print get_user_group_butts_readonly($_SESSION['user_id']);
?>	
				</TD>
			</TR>
<?php			
			}
		} else {
?>
			<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">
<?php
		}
		if(is_administrator()) {
?>
			<TR CLASS='even' VALIGN="top">
				<TD CLASS='td_label text'>
					<A CLASS="td_label text" HREF="#"  TITLE="Sets Boundaries for Ring Fences and exclusion zones"><?php print get_text("Boundaries");?></A>:
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2>
					<TABLE STYLE='width: 100%;'>
						<TR>
							<TD CLASS='td_label text text_right'>
								<A CLASS="td_label text" HREF="#"  TITLE="Sets boundary used to ring-fence the area this unit is allowed in"><?php print get_text("Ringfence");?></A>:&nbsp;
							</TD>
							<TD CLASS='td_data text text_left'>
								<SELECT id='ringfence' NAME="frm_ringfence" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_rf` = 1 ORDER BY `line_name` ASC";
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
										print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>
						<TR>
							<TD CLASS='td_label text text_right'>
								<A CLASS="td_label text" HREF="#" TITLE="Sets exclusion zone for this unit"><?php print get_text("Exclusion Zone");?></A>:&nbsp
							</TD>
							<TD CLASS='td_data text text_left'>
								<SELECT id='exclusion' NAME="frm_excl_zone" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_ex` = 1 ORDER BY `line_name` ASC";
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
										print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						
						</TR>
					</TABLE>
				</TD>
			</TR>			
<?php
			}
?>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>			
			<TR CLASS = "even" VALIGN='middle'>
				<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Unit Type - Select from pulldown menu">Type</A>: <font color='red' size='-1'>*</font></TD>
				<TD>&nbsp;</TD>
				<TD CLASS='td_data text'>
					<SELECT id='unittype' NAME='frm_type'><OPTION VALUE=0>Select one</OPTION>
<?php
						foreach ($u_types as $key => $value) {
							$temp = $value;
							print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
							}
?>
					</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</TD>
				<TD CLASS='td_data text'>
					<A CLASS="td_label text" HREF="#" TITLE="Unit is mobile unit?">Mobile</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" /><BR />
					<A CLASS="td_label text" HREF="#" TITLE="Unit can be dispatched to multiple incidents?">Multiple</A>  &raquo;<INPUT TYPE="checkbox" NAME="frm_multi_disp" /><BR />
					<A CLASS="td_label text" HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked />
				</TD>
			</TR>
			<TR CLASS = "odd" VALIGN='top'  TITLE = 'Select one'>
				<TD CLASS="td_label text" >
					<A CLASS="td_label text" HREF="#" TITLE="Tracking Type - select from the pulldown menu - you must also fill in the callsign or tracking id which is used by the tracking provider to identify the unit - each unit should have a unique id.">Tracking</A>:&nbsp;
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<SELECT id='track' NAME='frm_track_disp' onChange = "do_tracking(this.form, this.options[this.selectedIndex].value);">
						<OPTION VALUE='0' SELECTED>None</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_APRS'];?>'>APRS</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_INSTAM'];?>'>Instamapper</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_LOCATEA'];?>'>LocateA</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_GTRACK'];?>'>Gtrack</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_GLAT'];?>'>Google Lat</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_T_TRACKER'];?>'>Tickets Tracker</OPTION>					
						<OPTION VALUE='<?php print $GLOBALS['TRACK_OGTS'];?>'>OpenGTS</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_MOBILE'];?>'>Mobile Tracking</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_XASTIR'];?>'>Xastir</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_FOLLOWMEE'];?>'>FollowMee</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_TRACCAR'];?>'>Traccar</OPTION>
						<OPTION VALUE='<?php print $GLOBALS['TRACK_JAVAPRSSRVR'];?>'>Javaprssrvr</OPTION>
					</SELECT>&nbsp;&nbsp;
<SCRIPT>				
					var track_info = "APRS:   callsign\nInstamapper:   Device key\nLocateA:   Userid\nGtrack:   Userid\nLatitude:   Badge\nOpenGTS:   Device\nMobile Tracking: automatic\nXastir:    Callsign\nFollowme:    Device Key\nTraccar:    Callsign\nJavaprssrvr:    Callsign\n";
</SCRIPT>
					<INPUT TYPE = 'button' onClick = alert(track_info) value="?"> 
						&nbsp;&raquo;&nbsp;<INPUT id='callsign' SIZE='<?php print $key_field_size;?>' MAXLENGTH='<?php print $key_field_size;?>' TYPE='text' NAME='frm_callsign' VALUE="">&nbsp;
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>	
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Unit Status - Select from pulldown menu">Status</A>:&nbsp;<font color='red' size='-1'>*</font>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<SELECT id='unitstatus' NAME="frm_un_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
						<OPTION VALUE='0' SELECTED>Select one</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
						$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$the_grp = strval(rand());			//  force initial optgroup value
						$i = 0;
						while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
							if ($the_grp != $row_st['group']) {
								print ($i == 0)? "": "\t</OPTGROUP>\n";
								$the_grp = $row_st['group'];
								print "\t<OPTGROUP LABEL='$the_grp'>\n";
								}
							print "\t<OPTION VALUE=' {$row_st['id']}'  title='{$row_st['description']}'><SPAN STYLE='background-color:{$row_st['bg_color']}; color:{$row_st['text_color']};'> {$row_st['status_val']} </SPAN></OPTION>\n";
							$i++;
							}		// end while()
						print "\n</OPTGROUP>\n";
						unset($result_st);
?>
					</SELECT>
				</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="About unit status - information about particular status values for this unit">About Status</A>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='about' SIZE="61" TYPE="text" NAME="frm_status_about" VALUE="" MAXLENGTH="512">
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>	
			<TR CLASS='odd'>
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='location' SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61">
				</TD>
			</TR>
			<TR CLASS='even'>
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required">City</A>:&nbsp;&nbsp;&nbsp;&nbsp;
					<button type="button" onClick="Javascript:loc_lkup(document.res_add_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button>
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='city' SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label text" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;
					<INPUT id='state' SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="<?php print $st_size;?>">
				</TD>
			</TR>
<?php
			$query_fac	= "SELECT `f`.`id` AS `fac_id`, `lat`, `lng`, `type`, `handle` FROM `$GLOBALS[mysql_prefix]facilities` `f`
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
				ORDER BY `handle`";
			$result_fac	= mysql_query($query_fac) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (mysql_num_rows($result_fac) > 0) {
?>
				<TR CLASS = "odd" VALIGN='middle'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Unit is located at the selected facility as a home base">Locate at Facility:&nbsp;</A>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<FONT SIZE='-2'>
						<SELECT id='atfacility' NAME='frm_facility_sel' onChange="do_fac_to_loc(this.options[selectedIndex].value.trim());">
							<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
							while ($row_fac = stripslashes_deep(mysql_fetch_assoc($result_fac))) {
								echo "\t\t<OPTION VALUE = {$row_fac['fac_id']} CLASS = ''>{$row_fac['handle']}</OPTION>\n";
								}
?>
						</SELECT>
					</TD>
				</TR>		
<?php		
			}			// end if ()
?>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>	
			<TR CLASS = "odd">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Phone Number">Phone</A>:&nbsp;
				</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='phone' SIZE="12" MAXLENGTH="48" TYPE="text" NAME="frm_phone" VALUE="" />
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99></TD>
			</TR>	
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Unit Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
				</TD>	
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<TEXTAREA CLASS='text' id='description' NAME="frm_descr" COLS=56 ROWS=2></TEXTAREA>
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Unit Capability - training, equipment on board etc">Capability</A>:&nbsp;
				</TD>	
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<TEXTAREA CLASS='text' id='capability' NAME="frm_capab" COLS=56 ROWS=2></TEXTAREA>
				</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Unit Contact name">Contact Name</A>:&nbsp;
				</TD>	
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='contact_name' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" />
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Contact via - for email to unit this must be a valid email address or email to SMS address. For Twitter, input the Screen Name preceded by a '@'.">Contact Via</A>:&nbsp;
				</TD>	
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='contact_email' SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_contact_via" VALUE="" />
				</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="Cellphone number - input as country code then number without first 0">Cellphone</A>:&nbsp;
				</TD>	
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='cellphone' SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_cell" VALUE="" />
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">
					<A CLASS="td_label text" HREF="#" TITLE="<?php get_provider_name(get_msg_variable('smsg_provider'));?> ID - This is for <?php get_provider_name(get_msg_variable('smsg_provider'));?> Integration and is the ID used by <?php get_provider_name(get_msg_variable('smsg_provider'));?> to send SMS messages"><?php get_provider_name(get_msg_variable('smsg_provider'));?> ID</A>:&nbsp;
				</TD>	
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='smsgid' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_smsg_id" VALUE="" />
				</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Latitude and Longitude - set from map click">
					<SPAN onClick = 'javascript: do_coords(document.res_add_Form.frm_lat.value ,document.res_add_Form.frm_lng.value)'>
						Lat/Lng</A></SPAN>

				</TD>
				<TD>
					<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_add_Form);' />
				</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='show_lat' TYPE="text" NAME="show_lat" SIZE=11 VALUE="" disabled />
					<INPUT id='show_lng' TYPE="text" NAME="show_lng" SIZE=11 VALUE="" disabled />&nbsp;&nbsp;
<?php
					$locale = get_variable('locale');	// 08/03/09
					switch($locale) {
						case "0":
							$label = "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_add_Form)'>USNG:</SPAN>";
							$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='' disabled />";
							break;
						
						case "1":
							$label = "<SPAN ID = 'osgb_link' style='font-weight: bold;'>OSGB</SPAN>";
							$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='' disabled />";
							break;

						case "2":
							$label = "<SPAN ID = 'utm_link' style='font-weight: bold;'>UTM</SPAN>";
							$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='' disabled />";
							break;
						
						default:
						print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
						}
?>
					<?php print $label;?>
					<?php print $input;?>
				</TD>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN='4' class='spacer'></TD>
			</TR>
			<TR class='heading text'>
				<TD COLSPAN='4' class='heading text' style='text-align: center;'>File Upload</TD>
			</TR>
			<TR class='even'>
				<TD class='td_label text' style='text-align: left;'>Choose a file to upload:</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='file' NAME="frm_file" TYPE="file" />
				</TD>
			</TR>
			<TR class='odd'>
				<TD class='td_label text' style='text-align: left;'>File Name</TD>
				<TD>&nbsp;</TD>
				<TD COLSPAN=2 CLASS='td_data text'>
					<INPUT id='filename' NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE="">
				</TD>
			</TR>
			<TR class='spacer'>
				<TD COLSPAN='4' class='spacer'></TD>
			</TR>
			<TR>
				<TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD>
			</TR>
			<TR>
				<TD>&nbsp;</TD>
			</TR>
			</TABLE> <!-- end inner left -->
			<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
			<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
			<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
			<INPUT TYPE='hidden' NAME = 'frm_mobile' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_multi' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_aprs' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_instam' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_locatea' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_gtrack' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_glat' VALUE=0 />
			<INPUT TYPE='hidden' NAME = 'frm_t_tracker' VALUE=0 />	  <!-- 5/11/11 -->	
			<INPUT TYPE='hidden' NAME = 'frm_ogts' VALUE=0 />	<!-- 7/6/11 -->
			<INPUT TYPE='hidden' NAME = 'frm_mob_tracker' VALUE=0 />	<!-- 9/6/13 -->
			<INPUT TYPE='hidden' NAME = 'frm_xastir_tracker' VALUE=0 />	<!-- 1/30/14 -->
			<INPUT TYPE='hidden' NAME = 'frm_followmee_tracker' VALUE=0 />	<!-- 1/30/14 -->
			<INPUT TYPE='hidden' NAME = 'frm_traccar' VALUE=0 />	<!-- 6/29/17 -->
			<INPUT TYPE='hidden' NAME = 'frm_javaprssrvr' VALUE=0 />	<!-- 6/29/17 -->
			<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />  <!-- note default -->
			</FORM>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.reset_Form.submit();'><?php print get_text("Reset");?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.res_add_Form);'><?php print get_text("Submit");?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id= 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
			<DIV id= 'map_legend' style = "text-align: center;">
				<B>Map click to set location, drag / click to change location</B>
			</DIV>
			<BR />
			<DIV ID='icon_legend' style='width: <?php print get_variable('map_width');?>px; text-align: center;'>
				<SPAN><?php print get_text("Unit Legend");?></SPAN>
				<BR /><BR />
				<SPAN style='text-align: center;'><?php print get_icon_legend();?></SPAN>
			</DIV>
		</DIV>
	</DIV>
		<FORM NAME='can_Form' METHOD="post" ACTION = "units.php"></FORM>
		<FORM NAME='reset_Form' METHOD='get' ACTION='units.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>

		<!-- 2829 -->
		<A NAME="bottom" /> <!-- 5/3/10 -->
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>

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
		set_fontsizes(viewportwidth, "fullscreen");
		mapWidth = viewportwidth * .40;
		mapHeight = mapWidth * .9;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		colwidth = outerwidth * .42;
		colheight = outerheight * .95;
		listHeight = viewportheight * .7;
		listwidth = colwidth * .95
		inner_listwidth = listwidth *.9;
		fieldwidth = colwidth * .6;
		medfieldwidth = colwidth * .3;		
		smallfieldwidth = colwidth * .15;
		$('outer').style.width = outerwidth + "px";
		$('outer').style.height = outerheight + "px";
		$('leftcol').style.width = listwidth + "px";
		$('rightcol').style.width = listwidth + "px";
		$('map_canvas').style.width = mapWidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
		$('map_legend').style.width = mapWidth + "px";
		$('icon_legend').style.width = mapWidth + "px";
		for (var i = 0; i < fields.length; i++) {
			if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
			} 
		for (var i = 0; i < medfields.length; i++) {
			if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
			}
		for (var i = 0; i < smallfields.length; i++) {
			if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
			}
		load_exclusions();
		load_ringfences();
		load_basemarkup();
		load_groupbounds();		
		var latLng;
		var boundary = [];			//	exclusion zones array
		var bound_names = [];
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
		var bounds = map.getBounds();	
		var zoom = map.getZoom();
		var got_points = false;	// map is empty of points
		var doReverse = <?php print intval(get_variable('reverse_geo'));?>;
		function onMapClick(e) {
			if(doReverse == 0) {return;}
			if(marker) {map.removeLayer(marker); }
			var iconurl = "./our_icons/yellow.png";
			icon = new baseIcon({iconUrl: iconurl});	
			marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
			marker.addTo(map);
			newGetAddress(e.latlng, "a");
			};

		map.on('click', onMapClick);
<?php
		do_kml();
?>
		</SCRIPT>
		</BODY>
		</HTML>
