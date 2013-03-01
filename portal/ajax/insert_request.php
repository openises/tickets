<?php
require_once('../../incs/functions.inc.php');
include('../../incs/html2text.php');

dump($_POST);
// $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
// $where = $_SERVER['REMOTE_ADDR'];
// $street = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['fac_street'])) : quote_smart(trim($_POST['frm_street']));
// $city = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['fac_city'])) : quote_smart(trim($_POST['frm_city']));
// $state = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['fac_state'])) : quote_smart(trim($_POST['frm_state']));	
// $description = ((isset($_POST['orig_facility'])) && ($_POST['orig_facility'] != 0)) ? quote_smart(trim($_POST['frm_street'] . "/n " . $_POST['frm_city'] . "/n" . $_POST['frm_state'] . "/n" . $_POST['frm_description'])) : $_POST['frm_description'];
// $meridiem_request_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_request_date'])))) ) ? "" : $_POST['frm_meridiem_request_date'] ;
// $request_date = "$_POST[frm_year_request_date]-$_POST[frm_month_request_date]-$_POST[frm_day_request_date] $_POST[frm_hour_request_date]:$_POST[frm_minute_request_date]:00$meridiem_request_date";	
// $query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
			// `contact`, 
			// `street`, 
			// `city`, 
			// `state`, 
			// `the_name`, 
			// `phone`, 
			// `orig_facility`,
			// `rec_facility`, 
			// `scope`, 
			// `description`, 
			// `comments`, 
			// `lat`,
			// `lng`,
			// `request_date`, 
			// `status`, 
			// `accepted_date`,
			// `declined_date`, 
			// `resourced_date`, 
			// `completed_date`, 
			// `closed`, 
			// `requester`, 
			// `_by`, 
			// `_on`, 
			// `_from` 
			// ) VALUES (
			// " . quote_smart(trim(get_user_name($_SESSION['user_id']))) . ",
			// " . quote_smart(trim($street)) . ",	
			// " . quote_smart(trim($city)) . ",	
			// " . quote_smart(trim($state)) . ",	
			// " . quote_smart(trim($_POST['frm_patient'])) . ",
			// " . quote_smart(trim($_POST['frm_phone'])) . ",		
			// " . quote_smart(trim($_POST['frm_orig_fac'])) . ",					
			// " . quote_smart(trim($_POST['frm_rec_fac'])) . ",	
			// " . quote_smart(trim($_POST['frm_scope'])) . ",	
			// " . quote_smart(trim($description)) . ",					
			// " . quote_smart(trim($_POST['frm_comments'])) . ",		
			// " . $_POST['frm_lat'] . ",		
			// " . $_POST['frm_lng'] . ",				
			// " . quote_smart(trim($request_date)) . ",
			// 'Open',
			// NULL,
			// NULL,
			// NULL,
			// NULL,
			// NULL,
			// " . $_SESSION['user_id'] . ",
			// " . $_SESSION['user_id'] . ",				
			// '" . $now . "',
			// '" . $where . "')";
// $result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);