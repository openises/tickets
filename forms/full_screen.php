<?php

error_reporting(E_ALL);
$units_side_bar_height = .6;
$do_blink = TRUE;
$ld_ticker = "";
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");

require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";
$columns_arr = explode(',', get_msg_variable('columns'));
$not_sit = (array_key_exists('id', ($_GET)))?  $_GET['id'] : NULL;

if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
$use_ticker = (($_SESSION['good_internet']) && (module_active("Ticker")==1) && (!($not_sit))) ? 1 : 0;
/*

*/

	// set auto-refresh if any mobile units														
$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		#regions_control { font-family: verdana, arial, helvetica, sans-serif; font-size: 5px; background-color: #FEFEFE; font-weight: bold;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 10px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {text-align: left; margin-left: 0px; overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 1.5em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 1.2em; border: 1px solid #999; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { left: -1px; top: 0; position: absolute; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #000000; color: #FFFFFF; font-weight: bold; overflow: hidden; }
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
		#controlsbar {position: fixed; top: 0px; left: 0px; height: 150px; background-color: #000000; z-index: 9999;}
		.leaflet-control-layers-expanded { padding: 6px 10px 6px 6px; color: #333; background-color: #FFFFFF;}
		.leaflet-control-layers-expanded .leaflet-control-layers-list {height: auto; display: block; position: relative; margin-bottom: 20px;}
		.leaflet-bottom {bottom: 150px;	}
	</STYLE>
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<?php 

if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
if ($_SESSION['internet']) {
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
?>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
	<script src="./js/Google.js"></script>
	<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
	<script type="text/javascript" src="./js/L.Graticule.js"></script>
	<script type="text/javascript" src="./js/leaflet-providers.js"></script>
	<script type="text/javascript" src="./js/usng.js"></script>
	<script type="text/javascript" src="./js/osgb.js"></script>
	<script type="text/javascript" src="./js/geotools2.js"></script>
<?php } ?>

<SCRIPT>
window.onresize=function(){set_size()};
var showTicker = <?php print $use_ticker;?>;
var controlDiv;
var theLayer;
var quick = false;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var inc_last_display = 0;
var inc_period_changed = 0;
var i_interval = null;
var r_interval = null;
var f_interval = null;
var b_interval = null;
var fs_interval = null;
var latest_ticket = 0;
var latest_responder = 0;
var latest_facility = 0;
var do_update = true;
var do_resp_update = true;
var do_fac_update = true;
var tickets_updated = [];
var responders_updated = [];
var facilities_updated = [];
var inc_period = 0;
var last_disp = 0;
var cell1 = "0px";
var cell2 = "0px";
var cell3 = "0px";
var cell4 = "0px";
var cell5 = "0px";
var cell6 = "0px";
var cell7 = "0px";
var mapCenter;
var mapZoom;
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
var captions = ["Current situation", "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year", "Scheduled"];
var heading = captions[inc_period];
heading += " - ";
heading += "<?php print get_variable('map_caption');?>";
		
/* Initial period selection - current tickets, 
	options available 0 (current tickets), 
	1 - Closed today
	2 - Closed Yesterday+
	3 - Closed this week
	4 - Closed last week
	5 - Closed last week+
	6 - Closed this month
	7 - Closed last month
	8 - Closed this year
	9 - Closed last year
*/
var colors = new Array ('odd', 'even');
var fscolors = new Array ('fs_odd', 'fs_even');

function set_period(period) {
	window.inc_period = period;
	$('theHeading').innerHTML = window.captions[window.inc_period];
	}

function submit_period() {
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	inc_period_changed = 1;
	load_fs_incidentlist();
	}

function destroy_unitmarkers() {
	for (var i = 1; i < rmarkers.length; i++) {
		map.removeLayer(rmarkers[i]);
		}
	rmarkers.length = 0;
	}
	
function destroy_incmarkers() {
	for (var i = 1; i < tmarkers.length; i++) { 
		map.removeLayer(tmarkers[i]);
		}
	tmarkers.length = 0;
	}
	
function destroy_facmarkers() {
	for (var i = 1; i < fmarkers.length; i++) { 
		map.removeLayer(fmarkers[i]);
		}
	fmarkers.length = 0;
	}

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
	mapWidth = viewportwidth;
	mapHeight = viewportheight - 10;
	outerwidth = viewportwidth;
	outerheight = viewportheight - 10;
	listHeight = viewportheight * .25;
	colwidth = outerwidth * .42;
	colheight = outerheight * .7;
	listHeight = viewportheight * .5;
	listwidth = colwidth * .95
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = mapHeight + "px";
	$('listheading').style.width = listwidth + "px";
	$('ticketlist').style.maxHeight = listHeight + "px";
	$('ticketlist').style.width = listwidth + "px";
	$('ticketheader').style.width = listwidth + "px";
	$('assignmentslist').style.maxHeight = listHeight + "px";	
	$('assignmentslist').style.width = listwidth + "px";
	$('assignments_list').style.maxHeight = listHeight + "px";
	$('assignments_list').style.width = listwidth + "px";
	$('assignmentsheader').style.width = listwidth + "px";
	$("hidelists").style.position = "absolute";	
	$("hidelists").style.top = "0px";
	$("hidelists").style.left = colwidth + "px";
	if(isTicker == 1) {
		$('controls_bar').style.position = "fixed";
		$("controls_bar").style.bottom = "40px";
		}
	load_exclusions();
	load_ringfences();
	load_catchments();
	load_basemarkup();
	load_groupbounds();	
	load_fs_incidentlist();
	load_fs_responders();
	load_fs_facilities();
	full_scr_ass();
	load_regions();
	set_initial_pri_disp();
	load_poly_controls();
	mapCenter = map.getCenter();
	mapZoom = map.getZoom();
//	document.getElementById('layerControls').appendChild( );
	setTimeout(function() {$('leftcol').style.display = "none"; $('showlists').style.display='inline-block';},10000);
	}

function init_fsmap(theType, lat, lng, icon, theZoom, locale, useOSMAP, control_position) {
	if(locale == 1 && useOSMAP == 1) {	//	UK Use Ordnance Survey as Basemap
		var openspace_api = "<?php print get_variable('openspace_api');?>";
		openspaceLayer = L.tileLayer.OSOpenSpace(openspace_api, {debug: true});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		var currentSessionLayer = "<?php print $_SESSION['layer_inuse'];?>";
		var baseLayerNamesArr = ["Ordnance Survey"];	
		var baseLayerVarArr = [openspaceLayer];
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = baseLayerVarArr[a];

		map = new L.Map('map_canvas', {
			crs: L.OSOpenSpace.getCRS(),
			continuousWorld: true,
			worldCopyJump: false,
			minZoom: 0,
			maxZoom: L.OSOpenSpace.RESOLUTIONS.length - 1,
			zoomControl: false,
			layers: [openspaceLayer],
			});

		if(window.geo_provider == 1) {
			geocoder = L.Control.Geocoder.google(window.GoogleKey), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});
			} else if(window.geo_provider == 2) {
			geocoder = L.Control.Geocoder.bing(window.BingKey), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});				
			} else {
			geocoder = L.Control.Geocoder.nominatim(), 
			control = L.Control.geocoder({
				showResultIcons: false,
				collapsed: true,
				expand: 'click',
				position: 'topleft',
				placeholder: 'Search...',
				errorMessage: 'Nothing found.',
				geocoder: geocoder
				});
			}
		if(!isIE()) {
			control.addTo(map);
			}		
	
		var baseLayers = {
			"Ordnance Survey": openspaceLayer,
		};
		
		var overlays = {
			"Grid": grid,
		};
		if(control_position == "tl") {
			ctrlPos = 'topleft';
			} else if(control_position == "tr") {
			ctrlPos = 'topright';
			} else if(control_position == "bl") {
			ctrlPos = 'bottomleft';
			} else if(control_position == "br") {
			ctrlPos = 'bottomright';
			} else {
			ctrlPos = 'none';
			}
		if(ctrlPos != "none") {
			layercontrol = L.control.layers(baseLayers, overlays, {position: ctrlPos}).addTo(map);
			map.addLayer(roadalerts);
			layercontrol.addOverlay(roadalerts, "Road Conditions");
			L.control.scale().addTo(map);
			L.control.zoom({position: ctrlPos}).addTo(map);
			}
		if(theType ==2) {
			createcrossMarker(lat, lng);
			}
		if(theType ==3) {
			createstdMarker(lat, lng);
			}
		map.setView([lat, lng], 13);
		bounds = map.getBounds();	
		bounds = map.getBounds();	
		zoom = map.getZoom();
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		} else {
		var latLng;
		var in_local_bool = <?php print get_variable('local_maps');?>;
		var osmUrl = (in_local_bool=="1")? "./_osm/tiles/{z}/{x}/{y}.png":	"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		var	cmAttr = '';
		var cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade';
		var OSM   = L.tileLayer(osmUrl, {attribution: cmAttr});
		var ggl = new L.Google('ROAD');
		var ggl1 = new L.Google('TERRAIN');
		var ggl2 = new L.Google('SATELLITE');
		var ggl3 = new L.Google('HYBRID');
		var clouds = L.OWM.clouds({showLegend: false, opacity: 0.3});
		var cloudscls = L.OWM.cloudsClassic({showLegend: false, opacity: 0.3});
		var precipitation = L.OWM.precipitation({showLegend: false, opacity: 0.3});
		var precipitationcls = L.OWM.precipitationClassic({showLegend: false, opacity: 0.3});
		var rain = L.OWM.rain({showLegend: false, opacity: 0.3});
		var raincls = L.OWM.rainClassic({showLegend: false, opacity: 0.3});
		var snow = L.OWM.snow({showLegend: false, opacity: 0.3});
		var pressure = L.OWM.pressure({showLegend: false, opacity: 0.3});
		var pressurecntr = L.OWM.pressureContour({showLegend: false, opacity: 0.8});
		var temp = L.OWM.temperature({showLegend: false, opacity: 0.3});
		var wind = L.OWM.wind({showLegend: false, opacity: 0.3});
		var dark = L.tileLayer.provider('Thunderforest.TransportDark');
		var aerial = L.tileLayer.provider('MapQuestOpen.Aerial');
		var nexrad = L.tileLayer.wms("http://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi", {
			layers: 'nexrad-n0r-900913',
			format: 'image/png',
			transparent: true,
			attribution: "",
		});
		var shade = L.tileLayer.wms("http://ims.cr.usgs.gov:80/servlet19/com.esri.wms.Esrimap/USGS_EDC_Elev_NED_3", {
			layers: "HR-NED.IMAGE", 
			format: 'image/png',
			attribution: "",
		});
		var usgstopo = L.tileLayer('http://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {
			maxZoom: 20,
			attribution: '',
		});
		var grid = L.graticule({ interval: .5 })
		roadalerts = new L.LayerGroup();
		
		var currentSessionLayer = "<?php print $_SESSION['layer_inuse'];?>";
		var baseLayerNamesArr = ["Open_Streetmaps","Google","Google_Terrain","Google_Satellite","Google_Hybrid","USGS_Topo","Dark","Aerial"];	
		var baseLayerVarArr = [OSM,ggl,ggl1,ggl2,ggl3,usgstopo,dark,aerial];
		var a = baseLayerNamesArr.indexOf(currentSessionLayer);
		theLayer = baseLayerVarArr[a];
		
		if(window.geo_provider == 1) {
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: 20,
				zoom: theZoom,
				layers: [theLayer],
				zoomControl: false,
				}
				)};
				geocoder = L.Control.Geocoder.google(window.GoogleKey), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}
			} else if(window.geo_provider == 2){
			if(!map) { map = L.map('map_canvas',
				{
				maxZoom: 20,
				zoom: theZoom,
				layers: [theLayer],
				zoomControl: false,
				}
				)};
				geocoder = L.Control.Geocoder.bing(window.BingKey), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}			
			} else {
			if(!map) {
				map = L.map('map_canvas',{
					maxZoom: 20,
					zoom: theZoom,
					layers: [theLayer],
					zoomControl: false,
					}
					)};
				geocoder = L.Control.Geocoder.nominatim(), 
				control = L.Control.geocoder({
					showResultIcons: false,
					collapsed: true,
					expand: 'click',
					position: 'topleft',
					placeholder: 'Search...',
					errorMessage: 'Nothing found.',
					geocoder: geocoder
					});
				if(!isIE()) {
					control.addTo(map);
					}
			}

		var baseLayers = {
			"Open Streetmaps": OSM,
			"Google": ggl,
			"Google Terrain": ggl1,
			"Google Satellite": ggl2,
			"Google Hybrid": ggl3,
			"USGS Topo": usgstopo,
			"Dark": dark,
			"Aerial": aerial,		
		};
		
		var overlays = {
			"Clouds": cloudscls,
			"Precipitation": precipitationcls,
			"Rain": raincls,
			"Pressure": pressurecntr,
			"Temperature": temp,
			"Wind": wind,
			"Snow": snow,
			"Radar": nexrad,
			"Grid": grid,
		};

		layercontrol = L.control.layers(baseLayers, overlays, {position: control_position, collapsed: false});
		layercontrol._map = map;
		controlDiv = layercontrol.onAdd(map);

		map.addLayer(roadalerts);
		layercontrol.addOverlay(roadalerts, "Road Conditions");
		L.control.scale().addTo(map);
		L.control.zoom({position: control_position}).addTo(map);
		if(theType ==2) {
			createcrossMarker(lat, lng);
			}
		if(theType ==3) {
			createstdMarker(lat, lng);
			}
		map.setView([lat, lng], 13);
		bounds = map.getBounds();	
		zoom = map.getZoom();
		map.on('baselayerchange', function (eventLayer) {
			var layerName = eventLayer.name;
			var layerName = layerName.replace(" ", "_");
			var params = "f_n=layer_inuse&v_n=" +URLEncode(layerName)+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, layer_handleResult, params);	
			});
		}
	return map;
	}
	
function layer_handleResult(req) {
//	alert(req.responseText);
	}
	
var thelevel = '<?php print $the_level;?>';

function get_new_colors() {
	window.location.href = '<?php print basename(__FILE__);?>';
	}
	
function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
			}
		}
	if(tabid == "tab1") {
		if($('tab1')) {CngClass('tab1', 'tabinuse');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content1')) {$("content1").style.display = "block";}
		} else if(tabid == "tab2") {
		if($('tab2')) {CngClass('tab2', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content2')) {$("content2").style.display = "block";}
		} else {
		if($('tab3')) {CngClass('tab3', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "block";}
		init_minimap(3, lat,lng, "", 13, <?php print get_variable('locale');?>, 1);
		minimap.setView([lat,lng], 13);
		}
	}

</SCRIPT>

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='text/javascript'></SCRIPT>
		<SCRIPT SRC="./js/graticule_V3.js" 	TYPE="text/javascript"></SCRIPT>
<?php
	}
	if($_SESSION['good_internet']) {
		$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;
		if((module_active("Ticker")==1) && (!($sit_scr))) {
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='text/javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='text/javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";
			}
		}

	$al_groups = $_SESSION['user_groups'];

	if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	
		$regions_string = "Viewing Regions:&nbsp;&nbsp; " . $al_names;
		} else {
		$regions_string = "";
		}

?>	
<STYLE TYPE="text/css">
.box { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:1000; width: 180px; }
.bar { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
/* 3/26/2013
.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
*/
.bar_header { height: 30px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
.content { padding: 1em; }
</STYLE>
</HEAD>
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	
	if((!(is_guest())) && ($_SESSION['good_internet']) && (!($get_id))) {
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}	
	
	$gunload = "clearInterval(i_interval); clearInterval(r_interval); clearInterval(f_interval); clearInterval(b_interval);";
	$from_right = 20;
	$from_top = 10;
	$temp = intval(trim(get_variable('situ_refr')));
	$refresh =  ($temp < 15)? 15000: $temp * 1000;
	$set_to = (intval(trim(get_variable('situ_refr')))>0)? "setTimeout('location.reload(true);', {$refresh});": "";
	$the_api_key = trim(get_variable('gmaps_api_key'));	
	$set_map = "";	// 1/16/2013
	$set_regions_control = ((!($get_id)) && ((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))) ? "set_regions_control();" : "";
	$get_messages = ($get_id) ? "get_mainmessages(" . $get_id . " ,'',sortby, sort, '', 'ticket');" : "";
?>
<BODY onLoad = "set_size(); <?php print $ld_ticker;?> location.href = '#top'; <?php print $do_mu_init;?>" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>fullscreen</DIV>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>

<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
	<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
		<SPAN ID='theHeading' CLASS='header' STYLE='background-color: inherit;'></SPAN>
		<SPAN ID='theRegions' CLASS='heading' STYLE='background-color: #707070;' onMouseover='Tip("<?php print $regions_string;?>", WIDTH, 300);' onmouseout='UnTip();'>Viewing Regions (mouse over to view)</SPAN>
		<SPAN ID='sev_counts' CLASS='sev_counts'></SPAN>
	</DIV>
	<DIV id='left_sidebar' style='position: fixed; top: 30px; left: 0px; height: 500px; font-size: 1.2em; z-index: 9999; background-color: #FFFFFF;'>
		<SPAN id='showlists' class='plain' style='position: absolute; top: 250px; left: 0px; width: 35px; display: none; cursor: pointer; padding: 2px; background-color: #FEFEFE; text-align: right;' 
		onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' 
		onClick="$('leftcol').style.display = 'inline-block'; $('showlists').style.display = 'none'; $('hidelists').style.display = 'inline-block';">
		<IMG src='./images/fs_show_lists_button.jpg'></SPAN>
		<DIV id='leftcol' style='position: absolute; top: 5px; left: 5px; width: 250px; border: 1px outset #707070; background-color:rgba(0, 0, 0, 0.2); text-align: center; z-index: 3;'>
			<BR /><BR />
			<DIV id='listheading' class='heading' style='text-align: center; font-size: 24px;'>Incidents and Assignments</DIV><BR /><BR />
			<DIV id='ticketheader' class='header' style='text-align: center;'>Incidents</DIV>
			<DIV class="scrollableContainer" id='ticketlist' style='border: 1px outset #707070;'>
				<DIV class="scrollingArea" id='the_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
			<BR /><BR />
			<DIV id='assignmentsheader' class='header' style='text-align: center;'>Unit Assignments</DIV>
			<DIV class="scrollableContainer" id='assignmentslist' style='border: 1px outset #707070;'>
				<DIV class="scrollingArea" id='assignments_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
		</DIV>
		<SPAN id='hidelists' class='plain' style='position: absolute; top: 0px; left: 500px; float: right; z-index: 9999; width: 26px; cursor: pointer; display: none;' 
		onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' 
		onClick="$('leftcol').style.display = 'none'; $('showlists').style.display='inline-block'; $('hidelists').style.display='none';">
		<IMG src='./images/fs_hide_lists_button.jpg'></SPAN>
	</DIV>
	<DIV id='map_canvas' style='border: 1px outset #707070; z-index: 1;'></DIV>	
</DIV>
<DIV id='controls_bar' style='position: fixed; bottom: 0px; left: 0px; width: 100%; border: 1px outset #707070; background-color:rgba(0, 0, 0, 0.25); text-align: center; z-index: 3;'>
	<SPAN id='close' class='plain' style='float: left;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
	<CENTER>
	<TABLE style='text-align: center; width: 200px;'>
		<TR>
			<TD>
				<FORM NAME = 'frm_interval_sel' STYLE = 'display:inline' >
					<SELECT NAME = 'frm_interval' onChange = 'show_btns_closed(); set_period(this.value);'>
						<OPTION VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>
						<OPTION VALUE='0'><?php print get_text("Current situation"); ?></OPTION>
						<OPTION VALUE='1'><?php print $incidents;?> closed today</OPTION>
						<OPTION VALUE='2'><?php print $incidents;?> closed yesterday+</OPTION>
						<OPTION VALUE='3'><?php print $incidents;?> closed this week</OPTION>
						<OPTION VALUE='4'><?php print $incidents;?> closed last week</OPTION>
						<OPTION VALUE='5'><?php print $incidents;?> closed last week+</OPTION>
						<OPTION VALUE='6'><?php print $incidents;?> closed this month</OPTION>
						<OPTION VALUE='7'><?php print $incidents;?> closed last month</OPTION>
						<OPTION VALUE='8'><?php print $incidents;?> closed this year</OPTION>
						<OPTION VALUE='9'><?php print $incidents;?> closed last year</OPTION>
					</SELECT>
				</FORM>
			</TD>
			<TD>
				<SPAN ID = 'btn_go' class = 'plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='submit_period(); hide_btns_closed();' STYLE = 'color: green; display: none;'><U>Next</U></SPAN>
			</TD>
			<TD>
				<SPAN ID = 'btn_can' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='hide_btns_closed(); hide_btns_scheduled();' STYLE = 'color: red; display: none'><U>Cancel</U></SPAN>
			</TD>
		</TR>
	</TABLE>
	</CENTER>

</DIV>	
<SCRIPT>
		var controlsHTML = "<TABLE id='controlstable' ALIGN='center'>";
		controlsHTML +=	"<TR CLASS='odd'><TD>";
		controlsHTML +=	"<TABLE WIDTH='100%'><TR class='heading_2' WIDTH='100%'><TH ALIGN='center'>Incidents</TH></TR><TR><TD>";
		controlsHTML +=	"<DIV class='pri_button' onClick=\"set_pri_chkbox('normal'); hideGroup(1, 'Incident');\"><IMG SRC = './our_icons/sm_blue.png' STYLE = 'vertical-align: middle'BORDER=0>&nbsp;&nbsp;Normal: <input type=checkbox id='normal'  onClick=\"set_pri_chkbox('normal')\"/>&nbsp;&nbsp;</DIV>";
		controlsHTML +=	"<DIV class='pri_button' onClick=\"set_pri_chkbox('medium'); hideGroup(2, 'Incident');\"><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;Medium: <input type=checkbox id='medium'  onClick=\"set_pri_chkbox('medium')\"/>&nbsp;&nbsp;</DIV>";
		controlsHTML +=	"<DIV class='pri_button' onClick=\"set_pri_chkbox('high'); hideGroup(3, 'Incident');\"><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;High: <input type=checkbox id='high'  onClick=\"set_pri_chkbox('high')\"/>&nbsp;&nbsp;</DIV>";
		controlsHTML +=	"<DIV class='pri_button' ID = 'pri_all' class='pri_button' STYLE = 'display: none; width: 70px;' onClick=\"set_pri_chkbox('all'); hideGroup(4, 'Incident');\"><IMG SRC = './our_icons/sm_blue.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;All <input type=checkbox id='all'  STYLE = 'display:none;' onClick=\"set_pri_chkbox('all')\"/>&nbsp;&nbsp;</DIV>";
		controlsHTML +=	"<DIV class='pri_button' ID = 'pri_none' class='pri_button' STYLE = 'width: 60px;' onClick=\"set_pri_chkbox('none'); hideGroup(5, 'Incident');\"><IMG SRC = './our_icons/sm_white.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;None <input type=checkbox id='none' STYLE = 'display:none;' onClick=\"set_pri_chkbox('none')\"/>&nbsp;&nbsp;</DIV>";
		controlsHTML +=	"</TD></TR></TABLE></TD></TR><TR CLASS='odd'><TD><DIV ID = 'boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
		controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'fac_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
		controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
		controlsHTML += "</TABLE></CENTER></TD></TR></TABLE>";
</SCRIPT>
<DIV style='position: fixed; top: 100px; right: 0px; z-index: 9999; width: auto;'>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, TRUE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>
</DIV>
<SCRIPT>

//	setup map-----------------------------------//
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var isTicker = <?php print module_active("Ticker");?>;
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
var theMapHeight = viewportheight-10;
$('map_canvas').style.width = viewportwidth + "px";
$('map_canvas').style.height = theMapHeight + "px";
$('controls_bar').style.height = "50px";
var map;
var minimap;
var sortby = '`date`';
var sort = "DESC";
var columns = "<?php print get_msg_variable('columns');?>";
var the_columns = new Array(<?php print get_msg_variable('columns');?>);
var thelevel = '<?php print $the_level;?>';
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	conditions markers array
var rss_markers = [];		//	RSS markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var latLng;
var in_local_bool = "0";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_fsmap(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", <?php print get_variable('def_zoom');?>, theLocale, useOSMAP, "br");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], <?php print get_variable('def_zoom');?>);
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;
$('controls').innerHTML = controlsHTML;
$('theHeading').innerHTML = heading;
<?php
do_kml();
?>
</SCRIPT>
<?php
	$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;		//	10/23/12	
	if(($_SESSION['good_internet']) && (module_active("Ticker")==1) && (!($sit_scr))) {			//	10/23/12
		require_once('./modules/Ticker/incs/ticker.inc.php');
		$the_markers = buildmarkers();
		foreach($the_markers AS $value) {
?>
<SCRIPT>
			var theLat = <?php print $value[3];?>;
			var theLng = <?php print $value[4];?>;
			var theA = "<?php print $value[6];?>";
			var the_point = new L.LatLng(theLat, theLng);		//	10/23/12
			var the_header = "Traffic Alert";		//	10/23/12
			var the_text = "<?php print $value[1];?>";		//	10/23/12
			var the_id = "<?php print $value[0];?>";		//	10/23/12
			var the_category = "<?php print $value[5];?>";		//	10/23/12
			var the_link = '<A CLASS="link" HREF="' + theA + '" TARGET="_blank" TITLE="' + the_text + '">' + the_text + '</A>';
			var the_descrip = "<DIV style='border: 1px outset #707070; background-color: yellow;'>";
			the_descrip += "<DIV style='font-size: 14px; color: #FFFFFF; background-color: #707070; font-weight: bold;'>" + the_header + "</DIV><BR />";		//	10/23/12
			the_descrip += "<DIV style='font-size: 14px; color: #000000; font-weight: bold;'>" + the_category + "</DIV><BR />";		//	10/23/12
			the_descrip += "<DIV><SPAN>" + the_link + "</SPAN></DIV><BR />";		
			the_descrip += "<DIV style='font-size: 12px; color: blue; font-weight: normal;'>";		//	10/23/12
			the_descrip += "<?php print $value[2];?>";		//	10/23/12
			the_descrip += "</DIV></DIV>";		//	10/23/12
			var rss_marker = create_feedMarker(the_point, the_text, the_descrip, the_id, the_id);		//	10/23/12
			rss_marker.addTo(map);			
</SCRIPT>
<?php
		}
	}
?>
<FORM NAME='to_listtype' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE='' />
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
</FORM>
<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
<INPUT TYPE='hidden' NAME='func' VALUE='1' />
</FORM>
<FORM NAME='to_map' METHOD='get' ACTION = 'config.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='api_key' />
</FORM>

<br /><br />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>
<A NAME="bottom" />
</BODY>
<?php
if (array_key_exists('print', ($_GET))) {
?>
<script>
$("down").style.display = $("up").style.display = "none";
</script>
<?php
	}
?>
</HTML>
