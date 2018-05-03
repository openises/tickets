<?php
error_reporting(E_ALL);

session_start();						// 
session_write_close();
require_once('../incs/functions.inc.php');
$user = ($_SESSION['level'] == "7") ? $_SESSION['user_id']: 0; 
session_write_close();

function get_thefacilityname($value) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";		 
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = ($row['name'] != "") ? $row['name']: "Unknown";
		}
	return $the_ret;	
	}
	
function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $row['name_f'] . " " . $row['name_l'] : $row['user'];
		}
	return $the_ret;
	}
 
function exportMysqlToCsv($user,$filename = 'requests.csv'){
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '"';
    $csv_escaped = "\\";
	
	$where = ((isset($user)) && ($user != 0)) ? "WHERE `requester` = " . $user: "";

	$order = "ORDER BY `request_date`";
	$order2 = "ASC";
	$query = "SELECT 
			`r`.`street` AS `street`,
			`r`.`city` AS `city`,
			`r`.`state` AS `state`,
			`r`.`the_name` AS `customer`,
			`r`.`phone` AS `phone`,
			`r`.`to_address` AS `to_address`,
			`r`.`pickup` AS `pickup`,
			`r`.`arrival` AS `arrival`,
			`r`.`rec_facility` AS `rec_facility`,
			`r`.`scope` AS `title`,
			`r`.`description` AS `description`,
			`r`.`status` AS `status`,
			`r`.`id` AS `request_id`,
			`r`.`requester` AS `requester`,
			`r`.`contact` AS `contact`,
			`a`.`id` AS `assigns_id`,
			`a`.`start_miles` AS `start_miles`,
			`a`.`end_miles` AS `end_miles`,
			`a`.`miles` AS `miles`,
			`r`.`request_date` AS `request_date`,
			`r`.`accepted_date` AS `accepted_date`,
			`r`.`declined_date` AS `declined_date`,		
			`r`.`resourced_date` AS `resourced_date`,
			`r`.`completed_date` AS `completed_date`,	
			`r`.`closed` AS `closed_date`,
			`r`.`cancelled` AS `cancelled_date`,
			`r`.`_on` AS `_on`,
			`a`.`dispatched` AS `dispatched`,
			`a`.`clear` AS `clear`		
			FROM `$GLOBALS[mysql_prefix]requests` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id` 			
			{$where} GROUP BY `r`.`id` {$order} {$order2}";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
    $fields_cnt = mysql_num_fields($result);
	
	$output = array();
	$z=0;
    while ($row = mysql_fetch_array($result)){
		$miles = (($row['miles'] != NULL) && ($row['miles'] != 0) && (($row['start_miles'] == NULL) || ($row['start_miles'] == 0)) && (($row['end_miles'] == NULL) || ($row['end_miles'] == 0))) ? $row['miles'] : 0;
		$miles = ((($row['miles'] == NULL) || ($row['miles'] == 0)) && (($row['start_miles'] != NULL) && ($row['start_miles'] != 0)) && (($row['end_miles'] != NULL) && ($row['end_miles'] != 0))) ? $row['end_miles'] - $row['start_miles'] : $miles;
		$output[$z][] = get_user_name($row['requester']);
		$output[$z][] = $row['contact'];
		$output[$z][] = $row['customer'];
		$output[$z][] = $row['street'];		
		$output[$z][] = $row['city'];
		$output[$z][] = $row['state'];		
		$output[$z][] = $row['phone'];
		$theToAddress = explode(',',$row['to_address']);
		if($theToAddress[0] == "") {
			$output[$z][] = "";
			} else {
			$output[$z][] = $row['to_address'];
			}
		$output[$z][] = ($row['pickup'] != 0) ? $row['pickup']: "";	
		$output[$z][] = ($row['arrival'] != 0) ? $row['arrival']: "";	
		$output[$z][] = ($row['rec_facility'] != 0) ? get_thefacilityname($row['rec_facility']): "Not Set";	
		$output[$z][] = $row['title'];
		$output[$z][] = $row['description'];	
		$output[$z][] = $row['status'];	
		$output[$z][] = ($row['request_date'] != NULL) ? format_date_2(strtotime($row['request_date'])): "";		
		$output[$z][] = ($row['accepted_date'] != NULL) ? format_date_2(strtotime($row['accepted_date'])): "";	
		$output[$z][] = ($row['declined_date'] != NULL) ? format_date_2(strtotime($row['declined_date'])): "";	
		$output[$z][] = ($row['resourced_date'] != NULL) ? format_date_2(strtotime($row['resourced_date'])): "";	
		$output[$z][] = ($row['completed_date'] != NULL) ? format_date_2(strtotime($row['completed_date'])): "";
		$output[$z][] = ($row['closed_date'] != NULL) ? format_date_2(strtotime($row['closed_date'])): "";
		$output[$z][] = ($row['cancelled_date'] != NULL) ? format_date_2(strtotime($row['cancelled_date'])): "";
		$output[$z][] = $miles;
		$z++;
		}
	$fields_cnt = count($output[0]);
	$rows_cnt = count($output);
	
	$headers = array('Requester','Approver','Customer','Street','City','State','Phone','To Address','Pickup Time','Arrival Time','Receiving Facility','Title','Description','Status','Request Date','Accepted Date','Declined Date','Resourced Date','Completed Date','Closed Date','Cancelled Date','Mileage');
	
	$headers_cnt = count($headers);
 
    $schema_insert = '';
 
    for ($i = 0; $i < $headers_cnt; $i++) {
		$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, stripslashes($headers[$i])) . $csv_enclosed;
		if($i < $headers_cnt - 1) {
			$schema_insert .= $csv_separator;
			}
		} // end for
 
    $out = $schema_insert;
    $out .= $csv_terminated;
    // Format the data
	for ($k = 0; $k < $rows_cnt; $k++) {
        $schema_insert = '';
        for ($j = 0; $j < $fields_cnt; $j++)
        {
            if (($output[$k][$j] == '0') || ($output[$k][$j] != ''))
            {
 
                if ($csv_enclosed == '')
                {
                    $schema_insert .= $output[$k][$j];
                } else
                {
                    $schema_insert .= $csv_enclosed . 
					str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $output[$k][$j]) . $csv_enclosed;
                }
            } 
            if ($j < $fields_cnt - 1)
            {
                $schema_insert .= $csv_separator;
            }
        } // end for
 
        $out .= $schema_insert;
        $out .= $csv_terminated;
		

		
    } // end for
	
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Length: " . strlen($out));
    // Output to browser with appropriate mime type, you choose ;)
	header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=$filename");
    echo $out;
    exit;
	} 

exportMysqlToCsv($user);
 
?> 
