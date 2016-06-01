<?php
error_reporting(E_ALL);

$side_bar_height = .5;
$iw_width= "300px";
/*
12/9/14/11New File.
*/

@session_start();
session_write_close();
require_once($_SESSION['fip']);

$tablename = "{$GLOBALS['mysql_prefix']}mmarkup";

$query = "CREATE TABLE IF NOT EXISTS `{$tablename}` (
	  `id` bigint(4) NOT NULL AUTO_INCREMENT,
	  `line_name` varchar(32) NOT NULL,
	  `line_status` int(2) NOT NULL DEFAULT '0' COMMENT '0 => show, 1 => hide',
	  `line_type` int(2) NOT NULL DEFAULT '0' COMMENT 'poly, circle, ellipse',
	  `line_data` varchar(4096) NOT NULL,
	  `use_with_bm` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with base map',
	  `use_with_r` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with regions',		  
	  `use_with_f` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with facilities',
	  `use_with_u_ex` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units - exclusion zone',
	  `use_with_u_rf` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units - ringfence',
	  `line_color` varchar(8) DEFAULT NULL,
	  `line_opacity` float DEFAULT NULL,
	  `line_width` int(2) DEFAULT NULL,
	  `fill_color` varchar(8) DEFAULT NULL,
	  `fill_opacity` float DEFAULT NULL,
	  `filled` int(1) DEFAULT '0',
	  `_by` int(7) NOT NULL DEFAULT '0',
	  `_from` varchar(16) DEFAULT NULL,
	  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `ID` (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Lines and borders'" ;
$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);


do_login(basename(__FILE__));

$mmarkup_id = (isset($_GET['id'])) ? $_GET['id'] : 0;	
extract($_GET);
extract($_POST);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Map Markup Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 2em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { left: -1px; top: 0; position: absolute; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
		.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
		div.tabBox {}
		div.tabArea { font-size: 80%; font-weight: bold; padding: 0px 0px 3px 0px; }
		span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; -moz-border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
				padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
		span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; -moz-border-radius: .75em .75em 0em 0em;
				border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
		span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
		div.content { font-size: 80%; background-color: #F0F0F0; border: 2px outset #707070; -moz-border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
				position: relative;	z-index: 101; cursor: normal; height: 250px;}
		div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
		#Test {position: absolute; visibility: hidden; height: auto; width: auto; white-space: nowrap;}
		.textLabelclass{ width: auto;}
	</STYLE>
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></SCRIPT>
	<SCRIPT src="./js/usng.js"></SCRIPT>
	<SCRIPT src="./js/proj4js.js"></SCRIPT>
	<SCRIPT src="./js/proj4-compressed.js"></SCRIPT>
	<SCRIPT src="./js/leaflet/leaflet.js"></SCRIPT>
	<SCRIPT src="./js/proj4leaflet.js"></SCRIPT>
	<SCRIPT src="./js/leaflet/KML.js"></SCRIPT>
	<script src="./js/leaflet/gpx.js"></script>  
	<SCRIPT src="./js/leaflet-openweathermap.js"></SCRIPT>
	<SCRIPT src="./js/esri-leaflet.js"></SCRIPT>
	<SCRIPT src="./js/OSOpenspace.js"></SCRIPT>
	<script src="./js/Control.Geocoder.js"></script>
	<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
	<script src="./js/Google.js"></script>
	<SCRIPT type="text/javascript" src="./js/osm_map_functions.js.php"></SCRIPT>
	<SCRIPT type="text/javascript" src="./js/L.Graticule.js"></SCRIPT>
	<SCRIPT SRC="./js/jscolor/jscolor.js"  type="text/javascript"></SCRIPT>
	<script type="text/javascript" src="./js/leaflet-providers.js"></script>
	<SCRIPT>
	var map, label;
	var layercontrol;
	var markers = [];
	var points = [];
	var boundary = [];
	var bound_names = [];
	var strokeColor;
	var	strokeWidth;
	var	strokeOpacity;
	var	fillColor;
	var	fillOpacity;
	
	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();
		
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && $) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && $) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}
		
	function mymclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
		document.doit_form.id.value=id;
		document.doit_form.func.value='view';
		document.doit_form.view.value='true';
		document.doit_form.submit();
		}
		
	function do_add() {					// Responds to sidebar click, then triggers listener above -  note [i]
		document.doit_form.id.value="";
		document.doit_form.func.value='add';
		document.doit_form.add.value='true';
		document.doit_form.submit();
		}
		
	function do_checked(theForm) {								// 126
		$('fill_tr').style.display = '';
		theForm.frm_filled.value = 1;
		theForm.frm_filled_n.checked = false;
		theForm.frm_filled_y.checked = true;
		}
		
	function do_un_checked(theForm) {
		$('fill_tr').style.display = "none";					// hide input row
		theForm.frm_filled.value = 0;
		theForm.frm_filled_n.checked = true;
		theForm.frm_filled_y.checked = false;
		}
		
	var circle_OK = false;	

	function chk_circle(theForm) {
		var err_msg = "";
		if (!(count == 1)) 									{err_msg += "Click map for circle center\n";}
		if (!(is_ok_radius (theForm.circ_radius.value))) 	{err_msg += "Valid circle radius is required\n";};

		if (!(err_msg == "")) {
			alert ("Please correct the following:\n\n" + err_msg);
			return;
			}
		else {
			circle_OK = true;

			var lat = parseFloat(points[0].lat().toFixed(6));
			var lng = parseFloat(points[0].lng().toFixed(6));
			var radius = parseFloat(theForm.circ_radius.value);
			strokeColor = theForm.frm_line_color.value;
			strokeWidth = parseInt(theForm.frm_line_width.value);
			strokeOpacity = parseFloat(theForm.frm_line_opacity.value);
			fillColor = theForm.frm_fill_color.value;
			fillOpacity = (theForm.frm_filled.value = 0)? 0: parseFloat(theForm.frm_fill_opacity.value);
			drawCircle(lat, lng, radius, add_hash(strokeColor), strokeWidth, strokeOpacity, add_hash(fillColor), fillOpacity);	// 210

			}
		}

	function is_ok_radius (instr) {
		if(instr.trim() == "") 								{return false;}
		if(instr.trim() == "0.0") 							{return false;}
		instr_ary = instr.split(".");
		if ((instr_ary.length)>2)							{return false;}
		if (instr_ary[0].NaN) 								{return false;}
		if (((instr_ary.length)==2) && (instr_ary[1].NaN))	{return false;}
		return true;
		}
		
	var mk1_text = "ID";
	var mk2_text = "Category";
	var mk3_text = "Name";
	var mk4_text = "Type";
	var mk5_text = "Updated";
	changed_markup_sort = false;
	var markup_direct = 'DESC';
	var markup_field = 'id';
	var markup_id = "mk1";
	var markup_header = "ID";
	var markup_last_display = 0;

	function set_markup_headers(id, header_text, the_bull) {
		if(id == "mk1") {
			window.mk1_text = header_text + the_bull;
			window.mk2_text = "Category";
			window.mk3_text = "Name";
			window.mk4_text = "Type";
			window.mk5_text = "Updated";
			} else if(id == "mk2") {
			window.mk2_text = header_text + the_bull;
			window.mk1_text = "ID";
			window.mk3_text = "Name";
			window.mk4_text = "Type";
			window.mk5_text = "Updated";
			} else if(id == "mk3") {
			window.mk3_text = header_text + the_bull;
			window.mk1_text = "ID";
			window.mk2_text = "Category";
			window.mk4_text = "Type";
			window.mk5_text = "Updated";
			} else if(id == "mk4") {
			window.mk4_text = header_text + the_bull;
			window.mk1_text = "ID";
			window.mk2_text = "Category";
			window.mk3_text = "Name";
			window.mk5_text = "Updated";
			} else if(id == "mk5") {
			window.mk5_text = header_text + the_bull;
			window.mk1_text = "ID";
			window.mk2_text = "Category";
			window.mk3_text = "Name";
			window.mk4_text = "Type";
			}
		}
		
	function do_markup_sort(id, field, header_text) {
		window.changed_mkup_sort = true;
		window.markup_last_display == 0;
		if(markup_field == field) {
			if(window.markup_direct == "ASC") {
				window.markup_direct = "DESC"; 
				var the_bull = "&#9660"; 
				window.markup_header = header_text;
				set_markup_headers(id, header_text, the_bull);
				} else if(window.inc_direct == "DESC") { 
				window.markup_direct = "ASC"; 
				var the_bull = "&#9650"; 
				window.markup_header = header_text; 
				set_markup_headers(id, header_text, the_bull);
				}
			} else {
			$(markup_id).innerHTML = markup_header;
			window.markup_field = field;
			window.markup_direct = "ASC";
			window.markup_id = id;
			window.markup_header = header_text;
			var the_bull = "&#9650";
			set_markup_headers(id, header_text, the_bull);
			}
		load_markup(field, markup_direct);
		return true;
		}

	function load_markup(sort, dir) {
		if(sort != window.markup_field) {
			window.markup_field = sort;
			}
		if(dir != window.markup_direct) {
			window.markup_direct = dir;
			}
		if($('the_mmarkuplist').innerHTML == "") {
			$('the_mmarkuplist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
			}
		var randomnumber=Math.floor(Math.random()*99999999);	
		var url = './ajax/list_mmarkup.php?sort='+window.markup_field+'&dir='+ window.markup_direct+'&version='+randomnumber;
		sendRequest (url,mmarkup_cb, "");		
		function mmarkup_cb(req) {
			var markup_arr = JSON.decode(req.responseText);
			if(markup_arr[0] == 0) {
				window.markup_last_display = 0;
				outputtext = "<marquee direction='left' style='font-size: 2em; font-weight: bold;'>......No Map Markup currently configured.........</marquee>";
				$('the_mmarkuplist').innerHTML = outputtext;
				return false;
				}
			var markup_number = 0;
			var outputtext = "<TABLE id='markuptable' class='cruises scrollable' style='width: " + window.listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listwidth + "px;'>";
			outputtext += "<TH id='mk1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_markup_sort(this.id, 'id', 'ID')\">" + window.mk1_text + "</TH>";
			outputtext += "<TH id='mk2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_markup_sort(this.id, 'cat', 'Category')\">" + window.mk2_text + "</TH>";
			outputtext += "<TH id='mk3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_markup_sort(this.id, 'name', 'Name')\">" + window.mk3_text + "</TH>";
			outputtext += "<TH id='mk4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_markup_sort(this.id, 'type', 'Type')\">" + window.mk4_text + "</TH>";
			outputtext += "<TH id='mk5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_markup_sort(this.id, 'updated', 'Updated')\">" + window.mk5_text + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var i = 0; i < markup_arr.length; i++) {
				var theID = markup_arr[i]['id'];
				var theLinename = markup_arr[i]['name'];
				var theIdent = markup_arr[i]['ident'];
				var theCategory = markup_arr[i]['cat'];
				var theData = markup_arr[i]['data'];
				var theColor = "#" + markup_arr[i]['color'];
				var theOpacity = markup_arr[i]['opacity'];
				var theWidth = markup_arr[i]['width'];
				var theFilled = markup_arr[i]['filled'];
				var theFillcolor = "#" + markup_arr[i]['fill_color'];
				var theFillopacity = markup_arr[i]['fill_opacity'];
				var theType = markup_arr[i]['type'];
				switch(theType) {
					case "c":
						var theTypename = "Circle";
						var theTypecolor = "white";
						var theTypebg = "blue";
						break;
					case "b":
						var theTypename = "Banner";
						var theTypecolor = "white";
						var theTypebg = "green";
						break;
					case "p":
						var theTypename = "Polygon";
						var theTypecolor = "black";
						var theTypebg = "orange";
						break;
					case "l":
						var theTypename = "Line";
						var theTypecolor = "black";
						var theTypebg = "yellow";
						break;
					default:
						var theTypename = "Error";
						var theTypecolor = "yellow";
						var theTypebg = "red";
					}
				var updated = markup_arr[i]['updated'];
				if(theType == "p") {
					if(!boundary[theID]) {
						var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "catchment", theID);
						}
					} else if(theType == "c") {
					if(!boundary[theID]) {
						var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "catchment", theID);
						}
					} else if(theType == "l") {
					if(!boundary[theID]) {
						var polyline = draw_polyline(theLinename, theColor, theOpacity, theWidth, theData, theID);;
						}
					} else if(theType == "b") {
					if(!boundary[theID]) {
						var banner = drawBanner(theLinename, theData, theWidth, theColor, "banner", theID);
						}
					}					
				outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: " + window.listwidth + "px;' onMouseover=\"Tip('" + theLinename + " - " + theCategory + "')\" onMouseout='UnTip();' onClick='mymclick(" + theID + ");'>";
				outputtext += "<TD style='font-weight: bold; color: " + theTypecolor + "; background-color: " + theTypebg + ";'>" + theID + "</TD>";
				outputtext += "<TD style='font-weight: bold;'>" + theCategory + "</TD>";
				outputtext += "<TD style='font-weight: bold;'>" + theLinename + "</TD>";
				outputtext += "<TD style='font-weight: bold;'>" + theTypename + "</TD>";
				outputtext += "<TD style='font-weight: bold;'>" + updated + "</TD>";
				outputtext += "</TR>";
				markup_number = theID;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_mmarkuplist').innerHTML = outputtext;
				var mkuptbl = document.getElementById('markuptable');
				if(mkuptbl) {
					var headerRow = mkuptbl.rows[0];
					var tableRow = mkuptbl.rows[1];
					if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].offsetWidth + "px";}
					if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].offsetWidth + "px";}
					if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].offsetWidth + "px";}
					if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].offsetWidth + "px";}
					if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].offsetWidth + "px";}
					}				
				},500);
			}				// end function mmarkup_cb()
		}				// end function load_markup()
	</SCRIPT>


<?php
	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? 		$_POST['frm_remove']: "";
	$func = 			(array_key_exists ('func',$_GET )) ? 			$_GET['func']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? 			$_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? 			$_GET['goadd']: "";
	$_getedit = 		($func == "edit")? 								$_GET['edit']:  "";
	$_getadd = 			($func == "add")? 								$_GET['add']:  "";
	$_getview = 		($func == "view")? 								$_GET['view']: "";
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$by = $_SESSION['user_id'];
	$from = $_SERVER['REMOTE_ADDR'];	
	$caption = "";

	if ($_postfrm_remove == 'yes') {					//delete Responder - checkbox - 8/12/09
		$query = "DELETE FROM `{$tablename}` WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Map Markup <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$line_status = (trim($_POST['rb_line_is_vis'])=='on')?  0: 1;
			$line_width = ($_POST['frm_line_type']=="b") ? $_POST['frm_font_size'] : $_POST['frm_line_width'];
			$query = "UPDATE `{$tablename}` SET 
				`line_name` = " . 		quote_smart(trim($_POST['frm_name'])) .",
				`line_ident` = " . 		quote_smart(trim($_POST['frm_ident'])) .",
				`line_cat_id` = " . 	quote_smart(trim($_POST['frm_line_cat_id'])) .",
				`line_status` = 		'{$line_status}',
				`line_type` = " . 		quote_smart(trim($_POST['frm_line_type'])) .",
				`line_data` = " .  		quote_smart(trim($_POST['frm_line_data'])) .",
				`use_with_bm` = " .  	quote_smart(trim($_POST['frm_use_with_bm'])) .",
				`use_with_r` = " .  	quote_smart(trim($_POST['frm_use_with_r'])) .",
				`use_with_f` = " .  	quote_smart(trim($_POST['frm_use_with_f'])) .",
				`use_with_u_ex` = " .  	quote_smart(trim($_POST['frm_use_with_u_ex'])) .",
				`use_with_u_rf` = " .  	quote_smart(trim($_POST['frm_use_with_u_rf'])) .",			
				`line_color` = " .  	quote_smart(trim($_POST['frm_line_color'])) .",
				`line_opacity` = " .  	quote_smart(trim($_POST['frm_line_opacity'])) .",
				`filled` = " .  		quote_smart(trim($_POST['frm_filled'])) .",
				`fill_color` = " .  	quote_smart(trim($_POST['frm_fill_color'])) .",
				`fill_opacity` = " .  	quote_smart(trim($_POST['frm_fill_opacity'])) .",
				`line_width` = " .  	quote_smart(trim($line_width)) .",
				`_by` =   				'{$by}' ,
				`_from` =	 			'{$from}' ,
				`_on` =   				'{$now}'
				WHERE `id` = 			{$_POST['frm_id']}";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$caption = "<B>Map Markup <i> " . stripslashes_deep($_POST['frm_name']) . "</i>' data has been updated </B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$line_width = ($_POST['frm_line_type']=="b") ? $_POST['frm_font_size'] : $_POST['frm_line_width'];
		$filled =		(trim($_POST['frm_line_type']) == "t")?	"NULL" : quote_smart(trim($_POST['frm_filled'])) ; 
		$fill_color =	(trim($_POST['frm_line_type']) == "t")?	"NULL" : quote_smart(trim($_POST['frm_fill_color'])) ; 
		$fill_opacity =	(trim($_POST['frm_line_type']) == "t")?	"NULL" : quote_smart(trim($_POST['frm_fill_opacity'])) ; 
		$query = "INSERT INTO `{$tablename}` (`line_name`, `line_ident`, `line_cat_id`, `line_status`, `line_type`, `line_data`, `use_with_bm`, `use_with_r`, `use_with_f`, `use_with_u_ex`, `use_with_u_rf`, `line_color`, `line_opacity`, `filled`, `fill_color`, `fill_opacity`,`line_width`,
		`_by`, `_from`, `_on`) 
			VALUES (" .
			 quote_smart(trim($_POST['frm_name'])) ."," .
			 quote_smart(trim($_POST['frm_ident'])) ."," .
			 quote_smart(trim($_POST['frm_line_cat_id'])) ."," .
			 quote_smart(trim($_POST['frm_line_status'])) ."," .
			 quote_smart(trim($_POST['frm_line_type'])) ."," .
			 quote_smart(trim($_POST['frm_line_data'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_bm'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_r'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_f'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_u_ex'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_u_rf'])) ."," .			 
			 quote_smart(trim($_POST['frm_line_color'])) ."," .
			 quote_smart(trim($_POST['frm_line_opacity'])) ."," .
			 $filled ."," .
			 $fill_color ."," .
			 $fill_opacity ."," .
			 quote_smart(trim($line_width)) ."," .
			 quote_smart($by) ."," .
			 quote_smart($from) ."," .
			 quote_smart(trim($now)) . ")" ;

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$insert_id = mysql_insert_id();
		$caption = "<B>Map Markup <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been applied </B><BR /><BR />";
		}							// end if ($_getgoadd == 'true')

// add ==================================================================================================================
// add ==================================================================================================================
// add ==================================================================================================================

	if ($func == 'add') {
		if (!($_SESSION['internet'])) {
			print "Not usable in No-Maps mode<BR />";
			exit();
			} else {
			require_once('./incs/links.inc.php');
			require_once('./forms/mmarkup_add_screen.php');
			}
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($func == 'edit') {
		if (!($_SESSION['internet'])) {
			print "Not usable in No-Maps mode<BR />";
			exit();
			} else {
			require_once('./incs/links.inc.php');
			require_once('./forms/mmarkup_edit_screen.php');
			}
		}		// end if ($_GET['edit'])
		
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

	if ($func == 'view') {
		if (!($_SESSION['internet'])) {
			print "Not usable in No-Maps mode<BR />";
			exit();
			} else {
			require_once('./incs/links.inc.php');
			require_once('./forms/mmarkup_view_screen.php');
			}
		}
// Initial display ======================================================================================================
// Initial display ======================================================================================================
// Initial display ======================================================================================================

	if (!isset($mapmode)) {$mapmode="a";}
	if (!($_SESSION['internet'])) {
		print "Not usable in No-Maps mode<BR />";
		exit();
		} else {
		require_once('./incs/links.inc.php');
		require_once('./forms/mmarkup_screen.php');
		}
	exit();
    break;
?>
