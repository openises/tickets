<?php
/*
9/10/13 - New file - gets local copies of map tiles from OSM
*/
require_once('./incs/functions.inc.php');
@session_start();
session_write_close();
do_login(basename(__FILE__));
error_reporting(E_ALL);	
set_time_limit(0);

$local = getcwd() . "/_osm/tiles/";

function directory_empty($path) {
	if(($files = @scandir($path)) && (count($files) > 2)) {
		return FALSE;
		} else {
		return TRUE;
		}
	}
	
function dirToArray($dir) { 
	$result = array(); 
	$cdir = scandir($dir); 
	foreach ($cdir as $key => $value) { 
		if (!in_array($value,array(".",".."))) { 
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) { 
				$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
				} else { 
				$result[] = $value; 
				} 
			} 
		} 
	return $result; 
	}
	
$tiles = dirToArray('./_osm/tiles');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tickets Map Configuration</title>
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
     <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	
<style type="text/css">
	.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; float: left; }
	.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder; cursor: pointer; float: left; }	
	.plain_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder; float: left;}					  
	.hover_lo 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 1px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
	.plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;}
	.data 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: yellow;font-weight: bolder;}		
	.message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	.thebox { z-index: 99; position: absolute; left: 40%; top: 20%; width: 20%; height: 20%;}
	.waiting { position: relative; text-align: center; vertical-align: middle; border: 4px outset #707070; font-weight: bold; font-size: 20px; background: yellow; color: black;
				  z-index: 99; display: none; width: 100%; height: 100%;}
	.listwrapper { position: absolute; right: 10px; top: 10px; width: 400px; height: 80%;}
	.title2 {position: relative; top: 40px; width: 100%; height: 30px; display: inline-block; color: #000000; font-size: 18px; font-weight: bold; text-align: center;}
	.leftcol {position: absolute; top: 50px; left: 5%; background: #CECECE; border: 1px outset #707070; padding: 30px;}
	.rightcol {position: absolute; top: 50px; right: 10%; background: #CECECE; font-size: 16px; font-weight: bold; border: 1px outset #707070; padding: 30px;}
	.fullwidthcol {position: absolute; top: 50px; left: 10%; background: #CECECE; font-size: 16px; font-weight: bold; border: 1px outset #707070; padding: 30px;}
	.filelistheading {border: 1px solid #707070; font-weight: bold; font-size: 1em; background-color: #707070; color: #FFFFFF; text-align: center;}
	.filelist {border: 1px solid #707070; overflow-y: scroll; display: none; font-weight: normal; font-size: .8em;}
	.mapcanvas {border: 2px outset #CECECE; display: block;}
	.bannertext {position: relative; left: 5%; background: #707070; color: #FFFFFF; font-size: 24px; font-weight: bold; text-align: center;}
</style>
<script src="./js/misc_function.js" type="application/x-javascript"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<SCRIPT>
	window.onresize=function(){set_size()};
	window.onload=function(){set_size()};
	
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
		mapWidth = viewportwidth * .35;
		mapHeight = viewportheight * .50;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		bannerwidth = outerwidth * .85;
		leftcolwidth = outerwidth * .35;
		leftcolheight = outerheight * .75;
		rightcolwidth = outerwidth * .35;
		rightcolheight = outerheight * .75;
		fullwidthcolwidth = outerwidth * .8;
		fullwidthcolheight = outerheight * .75;
		listwrapperwidth = rightcolwidth *.7;
		listwrapperheight = rightcolheight *.9;
		filelistwidth = listwrapperwidth *.9;
		filelistheight = listwrapperheight *.9;
		$('outer').style.width = outerwidth + "px";
		$('outer').style.height = outerheight + "px";
		if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
		if($('leftcol')) {$('leftcol').style.height = leftcolheight + "px";}
		if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
		if($('rightcol')) {$('rightcol').style.height = rightcolheight + "px";}
		if($('fullwidthcol')) {$('fullwidthcol').style.width = fullwidthcolwidth + "px";}
		if($('fullwidthcol')) {$('fullwidthcol').style.height = fullwidthcolheight + "px";}
		if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
		if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
		$('list_wrapper').style.width = listwrapperwidth + "px";
		$('list_wrapper').style.height = listwrapperheight + "px";
		$('file_list_header').style.width = filelistwidth + "px";
		$('file_list').style.width = filelistwidth + "px";
		$('file_list').style.height = filelistheight + "px";

		$('banner').style.width = bannerwidth + "px";
		if($('menubar')) {$('menubar').style.width = mapWidth + "px";}
		if($('title2')) {$('title2').style.width = outerwidth + "px";}
		if(map) {
			map.invalidateSize();
			}
		}


	var theTiles = [];
	var tl_lon = 0.0;
	var tl_lat = 0.0;
	var br_lon = 0.0;
	var br_lat = 0.0;
	
	function deg2rad(angle) {
		return angle * .017453292519943295;
		}
		
	function long2tile(lon,zoom1) { 
		tt = Number(lon);
		return (Math.floor((tt+180)/360*Math.pow(2,zoom1)));
		}

	function lat2tile(lat,zoom2)  { 
		return (Math.floor((1-Math.log(Math.tan(lat*Math.PI/180) + 1/Math.cos(lat*Math.PI/180))/Math.PI)/2 *Math.pow(2,zoom2))); 
		}
		
	function pausecomp(millis) {
		var date = new Date();
		var curDate = null;
		do { curDate = new Date(); } 
		while(curDate-date < millis);
		} 

	function calc_tile_name(zoom, lat, lon) {
		var xtile = long2tile(lon,zoom);
		var ytile = lat2tile(lat,zoom);
		var ret_arr = new Array(2);
		ret_arr[0] = xtile;
		ret_arr[1] = ytile;
		return ret_arr;
		}				// end function calc_tile_name ()
		
	function startIt() {
		$('help4').innerHTML = "<BR /><BR /><BR /><BR />";
		$('list_wrapper').style.display = "block";
		$('file_list_header').style.display='block';
		$('file_list').style.display='block';
		$('the_box').style.display='block';
		$('waiting').style.display='block';
		$('waiting').innerHTML = "Please Wait, Downloading Tiles<BR /><IMG style='vertical-align: middle;' src='./images/progressbar3.gif'/>";
		get_tile_list();	
		}
		
	function get_tile_list() {
		var zoom_top = parseInt(document.map_tiles_form.zoom_top.value);
		var zoom_btm = parseInt(document.map_tiles_form.zoom_bot.value);
		var top_left_lat = document.map_tiles_form.tl_lat.value;
		var top_left_lon = document.map_tiles_form.tl_lon.value;	
		var btm_rt_lat = document.map_tiles_form.br_lat.value;
		var btm_rt_lon = document.map_tiles_form.br_lon.value;
		var limit1 = zoom_btm + 1;
		for (var z = zoom_top; z<limit1;  z++) {
			theTiles[z] = [];
			var temp = calc_tile_name(z, top_left_lat, top_left_lon) ;		// get tile names for each zoom level
			var col_first = temp[0];
			var row_first = temp[1];
			var temp2 = calc_tile_name (z, btm_rt_lat, btm_rt_lon) ;
			col_last = temp2[0];
			row_last = temp2[1];
			var limit2 = col_last + 1;
			var limit3 = row_last + 1;
			for (var col = col_first; col<limit2;  col++) {
				theTiles[z][col] = [];
				for (var row = row_first; row<limit3;  row++) {
					if((z == zoom_btm) && (col == col_last) && (row == row_last)) { lastfile = "yes";} else { lastfile = "no"; }
					theTiles[z][col][row] = [];
					get_tiles_required(z,col,row,lastfile);
					pausecomp(3000);
					}
				}
			}
		}
		
	function get_tiles_required(z,col,row,lastfile) {
		var sessID = "<?php print $_SESSION['id'];?>";
		var url = "./ajax/gettiles.php?dir=" + z + "&subdir=" + col + "&file=" + row + "&lastfile=" + lastfile+'&q='+sessID;
		var payload = syncAjax(url);
		var the_ret_file=JSON.decode(payload);
		var finish_but = "<SPAN id='b6' class = 'plain' style='display: none; z-index: 999; float: none; width: 120px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'document.forms[\"to_config_Form\"].submit();'>Back to Config</SPAN>";
		if((the_ret_file[0] == "Completed") && (the_ret_file[2] == "no")){
			if($('file_list').innerHTML.length > 5000) {
				$('file_list').innerHTML = the_ret_file[1];
				$('file_list').innerHTML += "<BR />";				
				} else {
				$('file_list').innerHTML += the_ret_file[1];
				$('file_list').innerHTML += "<BR />";
				}
			$('file_list').scrollTop = $('file_list').scrollHeight;
			} else if(the_ret_file[2] == "yes") {
			update_localmaps();
			} else {
			alert("Failed");
			}				
		}
		
	function update_localmaps() {
		var url = "./ajax/update_localmaps.php";
		var finish_but = "<SPAN id='b6' class = 'plain' style='display: none; z-index: 999; float: none; width: 120px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'document.forms[\"to_config_Form\"].submit();'>Back to Config</SPAN>";
		var payload = syncAjax(url);
		var the_ret=JSON.decode(payload);
		if(the_ret[0] == 1){
			$('file_list').innerHTML += "Last Tile Downloaded<BR />";
			$('file_list').scrollTop = $('file_list').scrollHeight;
			$('waiting').style.display='block'; 
			$('waiting').innerHTML = "<CENTER>Complete<BR /><BR /> Also changed setting to use local maps<BR /><BR />" + finish_but + "</CENTER>";
			$('b6').style.display='block'; 
			$('b6').style.zindex = 999;
			update_bounds();
			} else {
			$('file_list').innerHTML += "Last Tile Downloaded<BR />";
			$('file_list').scrollTop = $('file_list').scrollHeight;
			$('waiting').style.display='block'; 
			$('waiting').innerHTML = "<CENTER>Complete<BR /><BR />However failed to change setting to use local maps.<BR />Please go to edit settings<BR />and change Local maps to 1<BR /><BR />" + finish_but + "</CENTER>";
			$('b6').style.display='block'; 
			$('b6').style.zindex = 999;
			}
		}
		
	function get_bounds() {
		var theBounds = map.getBounds();
		document.map_tiles_form.tl_lon.value = theBounds.getWest();
		document.map_tiles_form.tl_lat.value = theBounds.getNorth();
		document.map_tiles_form.br_lon.value = theBounds.getEast();
		document.map_tiles_form.br_lat.value = theBounds.getSouth();
		window.tr_lon = theBounds.getEast();
		window.tr_lat = theBounds.getNorth();
		window.bl_lon = theBounds.getWest();
		window.bl_lat = theBounds.getSouth();
		}
		
	function update_bounds() {
		var url = "./ajax/update_localmap_boundary.php?tr_lat=" + window.tr_lat + "&tr_lon=" + window.tr_lon + "&bl_lat=" + window.bl_lat + "&bl_lon=" + window.bl_lon;
		var payload = syncAjax(url);
		var the_ret=JSON.decode(payload);
		if(the_ret[0] == 1){
			}		
		}
		
	function get_zoom_max() {
		document.map_tiles_form.zoom_bot.value = map.getZoom();
		}	

	function get_zoom_min() {
		document.map_tiles_form.zoom_top.value = map.getZoom();
		}

	function del_tiles() {
		$('deltiles').style.display = 'none';
		$('keeptiles').style.display = 'none';
		$('the_box').style.display='block';
		$('waiting').style.display='block';
		$('waiting').innerHTML = "<BR /><BR />Deleting existing tiles - please wait..<BR /><IMG style='vertical-align: middle;' src='./images/progressbar3.gif'/>";
		$('list_wrapper').style.display = "block";
		$('file_list_header').style.display = "block";
		$('file_list').style.display = "block";
		var theTiles = <?php echo json_encode($tiles);?>;
		for(var zoomlev in theTiles) {
			for(var col in theTiles[zoomlev]) {
				for(var theTile in theTiles[zoomlev][col]) {
					var temp = theTiles[zoomlev][col][theTile];
					var thefile = "zoom=" + zoomlev + "&col=" + col + "&tile=" + temp;
					delete_tile(thefile)
					pausecomp(500);
					}
				}
			}
		}
		
	function delete_tile(tilename) {
		var sessID = "<?php print $_SESSION['id'];?>";
		var url = "./ajax/deltile.php?" + tilename + "&q="+sessID;
		var payload = syncAjax(url);
		var the_ret_file=JSON.decode(payload);
		var finish_but = "<SPAN id='b6' class = 'plain' style='display: none; z-index: 999; float: none; width: 120px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'document.forms[\"to_config_Form\"].submit();'>Back to Config</SPAN>";
		if((the_ret_file[0] == "Completed") && (the_ret_file[2] == "continue")) { 
			if($('file_list').innerHTML.length > 5000) {
				$('file_list').innerHTML = the_ret_file[1];
				$('file_list').innerHTML += "<BR />";				
				} else {
				$('file_list').innerHTML += the_ret_file[1];
				$('file_list').innerHTML += "<BR />";
				}
			if(the_ret_file[4] != "") {$('file_list').innerHTML += the_ret_file[4]; $('file_list').innerHTML += "<BR />";}
			$('file_list').scrollTop = $('file_list').scrollHeight;
			} else if(the_ret_file[2] == "alldone") {
			$('file_list').innerHTML += "Last Tile Deleted<BR />";
			$('title2').innerHTML = "Deleted Existing Tiles";
			$('waiting').innerHTML = "<BR />Complete<BR /><BR />";
			$('waiting').innerHTML += "<SPAN id='go_but' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'go_toit();'>Continue</SPAN>";
			} else {
			alert("Failed");
			}
		}

	function go_toit() {
		document.go_Form.submit();
		}
		
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
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
			return AJAX.responseText;																				 
			} 
		else {
			alert ("201: failed")
			return false;
			}																						 
		}		// end function sync Ajax()		
		
</SCRIPT>
</HEAD>
<BODY>
<DIV id='outer' style='position: relative; top: 0px; left: 0px; width: 100%;'>
<?php
if((!directory_empty($local)) && (!isset($_GET['getgo']))) {
?>
	<DIV id='banner' class='bannertext'>Get Local Map Tiles</DIV>
	<DIV id='title2' class='title2'>Tiles already exist</DIV>
	<DIV id='fullwidthcol' class='fullwidthcol' style='position: relative; top: 50px;'>
		<DIV style='width: 50%;'>
		There are already map tiles stored for offline use. You can delete these and store new map tiles or you can keep the
		existing map tiles and any extra needed will be added. Map tiles will not be overwritten but will be preserved.
		<BR /><BR /><BR />
		<CENTER>
		<SPAN id='deltiles' class = 'plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "del_tiles();">Delete Existing Tiles</SPAN>
		<SPAN id='keeptiles' class = 'plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "go_toit();">Keep Existing Tiles</SPAN>
		</CENTER>
		</DIV>
		<DIV id='list_wrapper' class='listwrapper' style='display: none;'>
			<DIV id='file_list_header' class='filelistheading'>Downloaded Tiles</DIV>
			<DIV id='file_list' class='filelist'></DIV>
		</DIV>
	</DIV>
<?php
	} else {
?>
	<DIV id='banner' class='bannertext'>Get Local Map Tiles</DIV>
	<BR />
	<BR />
	<DIV id='leftcol' class='leftcol'>
		<DIV ID='map_canvas' class='mapcanvas'></DIV> 
		<CENTER>
		<DIV id='menubar'>
			<SPAN id='b5' class = 'plain' style='display: none; width: 45%;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.forms['reset_Form'].submit();">Reset</SPAN>
			<SPAN id='b1' class = 'plain' style='display: inline-block; width: 45%;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "get_bounds(); $('b2').style.display='inline-block'; $('b1').style.display='none'; $('help1').style.display='none'; $('help2').style.display='block'; $('b5').style.display='inline-block';">Get Bounds</SPAN>
			<SPAN id='b2' class = 'plain' style='display: none; width: 45%;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "get_zoom_min(); $('b3').style.display='inline-block'; $('b2').style.display='none'; $('help2').style.display='none'; $('help3').style.display='block';">Get Zoom Out</SPAN>
			<SPAN id='b3' class = 'plain' style='display: none; width: 45%;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "get_zoom_max(); $('b4').style.display='inline-block'; $('b3').style.display='none'; $('help3').style.display='none'; $('help4').style.display='block';">Get Zoom In</SPAN>
			<SPAN id='b4' class = 'plain' style='display: none; width: 45%;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "$('b4').style.display='none'; $('b5').style.display='none'; startIt();">Next</SPAN>
		</DIV>
		<DIV style='width: 500px;'>
			<FORM METHOD="POST" NAME= "map_tiles_form" ACTION="get_tiles.php?func=get_tiles">
				<TABLE style='width: 100%;'>
					<TR>
						<TD class='td_label'>Top Left (lon)</TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='tl_lon'></TD>
					</TR>
					<TR>
						<TD class='td_label'>Top Left (lat)</TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='tl_lat'></TD>
					</TR>
					<TR>
						<TD class='td_label'>Bottom Right (lon)</TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='br_lon'></TD>
					</TR>
					<TR>
						<TD class='td_label'>Bottom Right (lat)</TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='br_lat'></TD>
					</TR>
					<TR>
						<TD class='td_label'>Max-Zoom Out</TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='zoom_top'></TD>
					</TR>
					<TR>
						<TD class='td_label'>Max-Zoom In</TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='zoom_bot'></TD>
					</TR>
				</TABLE>
			</FORM>
		</DIV>
		</CENTER>
		<BR /><BR />
	</DIV>
	<DIV id='rightcol' class='rightcol'>
		<DIV id='list_wrapper' class='listwrapper' style='display: none;'>
			<DIV id='file_list_header' class='filelistheading'>Downloaded Tiles</DIV>
			<DIV id='file_list' class='filelist'></DIV>
		</DIV>
		<DIV id='help1' style='display: block;'><CENTER>Help.</CENTER><BR /><BR />
		This page allows you to collect local Open Streetmap tiles so that mapping will work whether on-line or off.<BR /><BR />
		First, on the map zoom and move the map to the area you want local maps for. Once you have done this click the "Get Bounds" button.<BR />
		</DIV>
		<DIV id='help2' style='display: none;'><CENTER>Help.</CENTER><BR /><BR />
		Next zoom in or out to the minimum amount of detail you will need - i.e. the most you would zoom out on the map.<BR /><BR />
		Once you have got this as you want click on the "Get Zoom Out" button<BR />
		</DIV>
		<DIV id='help3' style='display: none;'><CENTER>Help.</CENTER><BR /><BR />
		Next zoom in or out to the maximum amount of detail you will need - i.e. the most you would zoom in on the map.<BR /><BR />
		Once you have got this as you want click on the "Get Zoom In" button<BR />
		</DIV>	
		<DIV id='help4' style='display: none;'><CENTER>Help.</CENTER><BR /><BR />
		Now click the "Next" button and the system will go away and collect the tiles appropriate for the settings you have provided.<BR />
		Please note that this could take a considerable time. Do not navigate away from this page until the system alerts you that<BR />	
		the collection of tiles is complete. Once you have downloaded all the files remember to go into edit settings and check<BR />
		that "local maps" is set to 1<BR />
		</DIV><BR /><BR />
		<SCRIPT>
		var map;
		map = L.map('map_canvas').setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 1);
		L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>[…]',
		maxZoom: 18
		}).addTo(map);
		</SCRIPT>
	</DIV>
<?php
	}
?>
</DIV>
<DIV id='the_box' style='display: none; z-index: 99; position: absolute; left: 40%; top: 20%; width: 20%; height: 20%;'>
	<DIV id='waiting' style=' position: relative; text-align: center; vertical-align: middle; border: 4px outset #707070; font-weight: bold; font-size: 20px; background: yellow; color: black; z-index: 99; display: none; width: 100%; height: 100%;'></DIV>
</DIV>	
<FORM NAME='to_config_Form' METHOD="post" ACTION = "config.php"></FORM>	
<FORM NAME='reset_Form' METHOD="post" ACTION = "get_tiles.php"></FORM>
<FORM NAME='go_Form' METHOD="post" ACTION = "get_tiles.php?getgo=yes"></FORM>
<SCRIPT>

</BODY>
</HTML>