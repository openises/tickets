<?php
$do_blink = TRUE;
$ld_ticker = "";
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$isGuest = (is_guest()) ? 1 : 0;
$sess_id = $_SESSION['id'];
$good_internet = ($_SESSION['good_internet']) ? $_SESSION['good_internet'] : 0;
$use_ticker = 0;
$def_lat = get_variable('def_lat');
$def_lng = get_variable('def_lng');
if(file_exists("modules.inc.php")) {
	require_once('modules.inc.php');
	$use_ticker = (($_SESSION['good_internet']) && (module_active("Ticker")==1) && (!($not_sit))) ? 1 : 0;
	}
$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
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
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$showmaps = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) ? 1 : 0;
$api_key = get_variable('gmaps_api_key');
$key_str = (strlen($api_key) == 39) ? "key={$api_key}&" : false;
$gmaps_ok = ($key_str) ? 1 : 0;
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$fac_curr_cats = get_fac_category_butts();
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$guest = (is_guest()) ? 1 : 0;
$customSit_setting = get_variable('custom_situation');
$customSit_arr = explode ("/", $customSit_setting);			// Recent Events, Statistics
$showEvents = intval($customSit_arr[0]);
$showStats = intval($customSit_arr[1]);
$mapzooms = array();
$dir = (is_dir('./_osm/tiles')) ? './_osm/tiles' : '../_osm/tiles';
$mapdir = scandir($dir);
foreach($mapdir as $val) {
	if($val <> "." && $val <> "..") {
		if(is_dir('../_osm/tiles/' . $val)) {
			$mapzooms[] = intval($val);
			}
		}
	}
if(count($mapzooms) > 0 && get_variable('local_maps') == "1") {$localZoomMin = min($mapzooms); $localZoomMax = max($mapzooms);} else {$localZoomMin = 0; $localZoomMax = 20;}
// print $localZoomMin . ", " . $localZoomMax . ", " . $localZoomMin . "<BR />";
$setZoom = (get_variable('local_maps') == "1") ? $localZoomMin : get_variable('def_zoom');
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}
$def_srt_arr_respsit = array('icon','handle','mail','incidents','status','m','asof');
$def_srt_arr_resp = array('icon','name','handle','mail','incidents','status','sa','m','asof');
$def_sort = (get_variable('responder_list_sort') != "") ? get_variable('responder_list_sort') : "1,1";
$temp = explode(",", $def_sort);
$def_sort_respsit = $temp[0] -1;
$def_sort_resp = $temp[1] -1;

$def_srt_arr_facsit = array('id','name','mail','status','updated');
$def_srt_arr_fac = array('id','name','mail','status','updated');
$def_sort_fac = (get_variable('facility_list_sort') != "") ? get_variable('facility_list_sort') : "1,1";
$temp = explode(",", $def_sort_fac);
$def_sort_facsit = $temp[0] -1;
$def_sort_fac = $temp[1] -1;
$sitresp_sort = (array_key_exists('sitresp_sort', $_SESSION)) ? $_SESSION['sitresp_sort'] : $def_srt_arr_respsit[$def_sort_respsit];
$sitresp_direc = (array_key_exists('sitresp_direct', $_SESSION)) ? $_SESSION['sitresp_direct'] : "ASC";
$resp_sort = (array_key_exists('respresp_sort', $_SESSION)) ? $_SESSION['respresp_sort'] : $def_srt_arr_resp[$def_sort_resp];
$resp_direc = (array_key_exists('respresp_direct', $_SESSION)) ? $_SESSION['respresp_direct'] : "ASC";
$sitfac_sort = (array_key_exists('fac_sort', $_SESSION)) ? $_SESSION['fac_sort'] : $def_srt_arr_facsit[$def_sort_facsit];
$sitfac_direc = (array_key_exists('fac_direct', $_SESSION)) ? $_SESSION['fac_direct'] : "ASC";
$fac_sort = (array_key_exists('fac_sort', $_SESSION)) ? $_SESSION['fac_sort'] : $def_srt_arr_fac[$def_sort_fac];
$fac_direc = (array_key_exists('fac_direct', $_SESSION)) ? $_SESSION['fac_direct'] : "ASC";
$listheader_height = get_variable("listheader_height");
?>
<SCRIPT>
var listheader_height = "<?php print $listheader_height;?>";
var sit_resp_def_sort = '<?php print $sitresp_sort;?>';
var sit_resp_def_sort_index = "r" + <?php print $def_sort_respsit + 1;?>;
var resp_def_sort = '<?php print $resp_sort;?>';
var resp_def_sort_index = "rr" + <?php print $def_sort_resp + 1;?>;
var sit_fac_def_sort = '<?php print $sitfac_sort;?>';
var sit_fac_def_sort_index = "r" + <?php print $def_sort_facsit + 1;?>;
var fac_def_sort = '<?php print $fac_sort;?>';
var fac_def_sort_index = "f" + <?php print $def_sort_fac + 1;?>;
var changed_resp_sort = false;
var resp_direct = '<?php print $sitresp_direc;?>';
var resp_field = window.sit_resp_def_sort;
var resp_id = window.sit_resp_def_sort_index;
var resp_direct2 = '<?php print $resp_direc;?>';
var resp_field2 = window.resp_def_sort;
var resp_id2 = window.resp_def_sort_index;
var fac_direct = '<?php print $fac_direc;?>';
var fac_field = window.sit_fac_def_sort;
var fac_id = window.sit_fac_def_sort_index;
var fac_field2 = window.fac_def_sort;
var fac_id2 = window.fac_def_sort_index;
var maps = '<?php print $_SESSION['maps_sh'];?>' 
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var showTicker = <?php print $use_ticker;?>;
var showEvents = <?php print $showEvents;?>;
var showStats = <?php print $showStats;?>;
var doDebug = (parseInt(<?php print get_variable('debug');?> == 1)) ? true: false;
var guest = <?php print $isGuest;?>;
var sess_id = "<?php print $sess_id;?>";
var good_gmapsapi = <?php print $gmaps_ok;?>;
var internet = <?php print $showmaps;?>;
var good_internet = <?php print $good_internet;?>;
var geo_provider = <?php print get_variable('geocoding_provider');?>;
var BingKey = "<?php print get_variable('bing_api_key');?>";
var GoogleKey = "<?php print get_variable('gmaps_api_key');?>";
var openspace_api = "<?php print get_variable('openspace_api');?>";
var currentSessionLayer = "<?php print $_SESSION['layer_inuse'];?>";
var icons=[];
icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>] = 1;	// blue
icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>] = 2;	// yellow
icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>] =  3;	// red
var dzf = parseInt("<?php print get_variable('def_zoom_fixed');?>");
var max_zoom = <?php print get_variable('def_zoom');?>;
var columns = "<?php print get_msg_variable('columns');?>";	//	10/23/12
var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	10/23/12
var thelevel = '<?php print $the_level;?>';
var locale = <?php print get_variable('locale');?>;
var my_Local = <?php print get_variable('local_maps');?>;
var def_lng = <?php print get_variable('def_lng');?>;
var def_lat = <?php print get_variable('def_lat');?>;
var def_zoom = <?php print get_variable('def_zoom');?>;
var zoom = <?php print get_variable('def_zoom');?>;
var states_arr = <?php echo json_encode($states); ?>;
var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php
var curr_cats = <?php echo json_encode($curr_cats); ?>;
var cat_sess_stat = <?php echo json_encode($cat_sess_stat); ?>;
var hidden = <?php print json_encode($hidden); ?>;
var shown = <?php print json_encode($shown); ?>;
var number_of_units = <?php print get_no_units(); ?>;
var fac_curr_cats = <?php echo json_encode($fac_curr_cats); ?>;
var fac_cat_sess_stat = <?php echo json_encode(get_fac_session_status()); ?>;
var fac_hidden = <?php print find_fac_hidden(); ?>;
var fac_shown = <?php print find_fac_showing(); ?>;
var bnd_curr = <?php echo json_encode(get_bnd_session()); ?>;
var bnd_names_curr = <?php echo json_encode(get_bnd_session_names()); ?>;
var bnd_hidden = <?php print find_bnd_hidden(); ?>;
var bnd_shown = <?php print find_bnd_showing(); ?>;
var setZoom = <?php print $setZoom;?>;
var theZoom = <?php print $localZoomMin;?>;
var max_zoom = <?php print $localZoomMax;?>;
var mapWidth = <?php print get_variable('map_width');?>;
var mapHeight = <?php print get_variable('map_height');?>;
var theAPI = '<?php print get_variable('cloudmade_api');?>';
var bounds;

var textID = "<?php print get_text('Icon');?>";
var textScope = "<?php print get_text('Scope');?>";
var textAddress = "<?php print get_text('Address');?>";
var textType = "<?php print get_text('Type');?>";
var textA = "<?php print get_text('A');?>";
var textP = "<?php print get_text('P');?>";
var textU = "<?php print get_text('U');?>";
var textUpdated = "<?php print get_text('Updated');?>";

var t1_text = textID;
var t2_text = textScope;
var t3_text = textAddress;
var t4_text = textType;
var t5_text = textA;
var t6_text = textP;
var t7_text = textU;
var t8_text = textUpdated;
var inc_header = textID;

var iconTip = "<?php print get_tip('Map Icon');?>";
var incTip = "<?php print get_tip('Incident name or scope');?>";
var locTip = "<?php print get_tip('Incident Location');?>";
var typeTip = "<?php print get_tip('Type of Incident');?>";
var numTip = "<?php print get_tip('Number of Patients');?>";
var actTip = "<?php print get_tip('Number of Actions');?>";
var assTip = "<?php print get_tip('Number of Units assigned to this Incident');?>";
var updatedTip = "<?php print get_tip('Incident data last updated');?>";

var textIcon = "<?php print get_text('Icon');?>"; 
var textHandle = "<?php print get_text('Handle');?>"; 
var textMail = "<?php print get_text('Mail');?>"; 
var textIncs = "<?php print get_text('Incidents');?>"; 
var textStatus = "<?php print get_text('Status');?>"; 
var textM = "<?php print get_text('M');?>"; 
var textAsof = "<?php print get_text('As of');?>"; 

var respBull = (resp_direct == "ASC") ? "&#9650" : "&#9660";
var r1_text = (resp_id == "r1") ? textIcon + respBull : textIcon; 
var r2_text = (resp_id == "r2") ? textHandle + respBull : textHandle;
var r3_text = (resp_id == "r3") ? textMail + respBull : textMail;
var r4_text = (resp_id == "r4") ? textIncs + respBull : textIncs;
var r5_text = (resp_id == "r5") ? textStatus + respBull : textStatus;
var r6_text = (resp_id == "r6") ? textM + respBull : textM;
var r7_text = (resp_id == "r7") ? textAsof + respBull : textAsof;
var resp_header = textIcon + respBull;

var iconTip = "<?php print get_tip('Map Icon');?>";
var handleTip = "<?php print get_tip('Responder Handle');?>";
var emailTip = "<?php print get_tip('Email this responder');?>";
var incsTip = "<?php print get_tip('Incident(s) this responder assigned to or number of incidents');?>";
var statusTip = "<?php print get_tip('Responder Status');?>";
var trackingTip = "<?php print get_tip('Responder Tracking Type - GL-Google Latitude, MT-Tickets RM Tracker, TT-Tickets Internal Tracker');?>";
var respUpdTip = "<?php print get_tip('Responder data last updated');?>";

var textName = "<?php print get_text('Name');?>";
var textStatusAbout = "<?php print get_text('Status About');?>";
var textM = "<?php print get_text('M');?>"; 

var respBull = (resp_direct == "ASC") ? "&#9650" : "&#9660";
var rr1_text = (resp_id == "rr1") ? textIcon + respBull : textIcon; 
var rr2_text = (resp_id == "rr2") ? textName + respBull : textName;
var rr3_text = (resp_id == "rr3") ? textMail + respBull : textMail;
var rr4_text = (resp_id == "rr4") ? textIncs + respBull : textIncs;
var rr5_text = (resp_id == "rr5") ? textStatus + respBull : textStatus;
var rr6_text = (resp_id == "rr6") ? textStatusAbout + respBull : textStatusAbout; 
var rr7_text = (resp_id == "rr7") ? textM + respBull : textM;
var rr8_text = (resp_id == "rr8") ? textAsof + respBull : textAsof;
var resp_header = textIcon + respBull;

var nameTip = "<?php print get_tip('Responder Name');?>";
var statusAboutTip = "<?php print get_tip('Responder status about');?>";

var textFacIcon = "<?php print get_text('Icon');?>";
var textFacName = "<?php print get_text('Name');?>";
var textFacMail = "<?php print get_text('Mail');?>";
var textFacStatus = "<?php print get_text('Status');?>";
var textFacUpdated = "<?php print get_text('Updated');?>";

var facBull = (fac_direct == "ASC") ? "&#9650" : "&#9660";
var f1_text = (fac_id == "f1") ? textFacIcon + facBull : textFacIcon;
var f2_text = (fac_id == "f2") ? textFacName + facBull : textFacName;
var f3_text = (fac_id == "f3") ? textFacMail + facBull : textFacMail;
var f4_text = (fac_id == "f4") ? textFacStatus + facBull : textFacStatus;
var f5_text = (fac_id == "f5") ? textFacUpdated + facBull : textFacUpdated;
var fac_header = textFacIcon;

var facIconTip = "<?php print get_tip('Map Icon');?>";
var facNameTip = "<?php print get_tip('Facility Name');?>";
var facEmailTip = "<?php print get_tip('Email this Facility');?>";
var facStatusTip = "<?php print get_tip('Facility Status / Availability');?>";
var facUpdTip = "<?php print get_tip('Facility data last updated');?>";

var textFiName = "<?php print get_text('Filename');?>";
var textFiUploaded = "<?php print get_text('Uploaded');?>";
var textFiDate = "<?php print get_text('Date');?>";
var textFiLinked = "<?php print get_text('Linked with');?>";

var file1_text = "";
var file2_text = "";
var file3_text = "";
var file4_text = "";
var file_header = "";

var fiNameTip =	"<?php print get_tip('The File Name');?>";
var fiUploadedTip =	"<?php print get_tip('Who uploaded this?');?>";
var fiDateTip =	"<?php print get_tip('When was it uploaded?');?>?";
var fiLinkedTip = "<?php print get_tip('File Associated with?');?>?";

var textWlID = "<?php print get_text('ID');?>";
var textWlTitle = "<?php print get_text('Title');?>";
var textWlType = "<?php print get_text('Type');?>";
var textWlAddress = "<?php print get_text('Address');?>";
var textWlUpdated = "<?php print get_text('Updated');?>";

var w1_text = textWlID;
var w2_text = textWlTitle;
var w3_text = textWlType;
var w4_text = textWlAddress;
var w5_text = textWlUpdated;
var wl_header = textWlID;

var wlIDTip = "<?php print get_tip('Location ID');?>";
var wlTitleTip = "<?php print get_tip('Location Name');?>";
var wlTypeTip = "<?php print get_tip('Warning Type');?>";
var wlAddressTip = "<?php print get_tip('Location Address');?>";
var wlUpdatedTip = "<?php print get_tip('Location data last updated');?>";

var textLogOwner = "<?php print get_text('Owner');?>";
var textLogEvent = "<?php print get_text('Event');?>";
var textLogWhen = "<?php print get_text('When');?>";
var textLogUnit = "<?php print get_text('Unit');?>";
var textLogTick = "<?php print get_text('Ticket');?>";
var textLogInfo = "<?php print get_text('Info');?>";

var fil1_text = textLogOwner;
var fil2_text = textLogEvent;
var fil3_text = textLogWhen;
var fil4_text = textLogUnit;
var fil5_text = textLogTick;
var fil6_text = textLogInfo;
var log_header = textLogOwner;

var logOwnerTip = "<?php print get_tip('Who logged this');?>";
var logEventTip = "<?php print get_tip('What type of event was this');?>";
var logWhenTip = "<?php print get_tip('When did this happen');?>";
var logUnitTip = "<?php print get_tip('If this related to a responder, who was it?');?>";
var logTickTip = "<?php print get_tip('If this related to a Ticket, which one?');?>";
var logInfoTip = "<?php print get_tip('Additional information about this log entry');?>";

var textMsgID = "<?php print get_text('Msg');?>";
var textMsgTkt = "<?php print get_text('Tkt');?>";
var textMsgType = "<?php print get_text('Type');?>";
var textMsgFrom = "<?php print get_text('From');?>";
var textMsgTo = "<?php print get_text('To');?>";
var textMsgSubj = "<?php print get_text('Subj');?>";
var textMsgDate = "<?php print get_text('Date');?>";
var textMsgOwner = "<?php print get_text('Owner');?>";

var msg1_text = "";
var msg2_text = "";
var msg3_text = "";
var msg4_text = "";
var msg5_text = "";
var msg6_text = "";
var msg7_text = "";
var msg8_text = "";
var msg_header = textMsgID;

var msgIDTip = "<?php print get_tip('Message ID');?>";
var msgTickTip = "<?php print get_tip('If this is specific to a Ticket, which one');?>";
var msgTypeTip = "<?php print get_tip('Message Type, IE-Incoming email, OE-Outgoing email, IS-Incoming SMS, OS-Outgoing SMS');?>";
var msgSenderTip = "<?php print get_tip('Sender');?>";
var msgWhoTip = "<?php print get_tip('Who was it sent to');?>";
var msgSubjTip = "<?php print get_tip('Message subject');?>";
var msgDateTip = "<?php print get_tip('Message date');?>";
var msgOwnerTip = "<?php print get_tip('Which Tickets user owns this message - specific for outgoing or original sender of message replied to');?>";

var textFSTick = "<?php print get_text('Ticket');?>";
var textFSDesc = "<?php print get_text('Description');?>";
var textFSUnit = "<?php print get_text('Unit');?>";
var textFSDS = "<?php print get_text('DS');?>";
var textFSDate = "<?php print get_text('Date');?>";
// end of variable setup
</SCRIPT>