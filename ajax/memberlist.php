<?php 
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
// $the_session = $_GET['session'];
// if(!(secure_page($the_session))) {
	// exit();
	// } else {

$page = (isset($_GET['page'])) ? $_GET['page'] : 1; 
$the_list_lengths = explode(',', get_variable('list_length'));
$def_list_leng = $the_list_lengths[0];
$where="";
 
// get how many rows we want to have into the grid - rowNum parameter in the grid 
$limit = (isset($_GET['rows'])) ? $_GET['rows'] : $def_list_leng; 
 
// get index row - i.e. user click to sort. At first time sortname parameter -
// after that the index from colModel 
$sidx = (isset($_GET['sidx'])) ? $_GET['sidx'] : 1; 
 
// sorting order - at first time sortorder 
$sord = (isset($_GET['sord'])) ? $_GET['sord'] : 'ASC';

// User level 
$lev = (isset($_GET['lev'])) ? $_GET['lev'] : $_SESSION['level'] ;  

// Team 
$team = (isset($_GET['team'])) ? $_GET['team'] : 0;  

// User 
$user = (isset($_GET['user'])) ? $_GET['user'] : $SESSION['user_id'];  

$query = "SELECT * from `$GLOBALS[mysql_prefix]user` WHERE `id` = '{$user}'";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_rows = mysql_num_rows($result);
if($num_rows !=0) {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$member = $row['member'];
	} else {
	$member = NULL;
	}
// if we not pass at first time index use the first column for the index or what you want
if(!$sidx) $sidx =1; 

if(($lev == 0) || ($lev == 1) || ($lev == 2)) {
	$where = "";
	} elseif (($team !=0 ) && ($lev == 3)) {
	$where = "WHERE `te`.`id` = $team ";
	} elseif (($lev == 4) && ($member != NULL)) {
	$where = "WHERE `m`.`id` = $member ";
	} else {
	exit();
	}

// calculate the number of rows for the query. We need this for paging the result 
$result = mysql_query("SELECT COUNT(*) AS count FROM `$GLOBALS[mysql_prefix]member`"); 
$row = mysql_fetch_array($result,MYSQL_ASSOC); 
$count = $row['count']; 
 
// calculate the total pages for the query 
if( $count > 0 && $limit > 0) { 
              $total_pages = ceil($count/$limit); 
} else { 
              $total_pages = 0; 
} 
 
// if for some reasons the requested page is greater than the total 
// set the requested page to total page 
if ($page > $total_pages) $page=$total_pages;
 
// calculate the starting position of the rows 
$start = $limit*$page - $limit;
 
// if for some reasons start position is negative set it to 0 
// typical case is that the user type 0 for the requested page 
if($start <0) $start = 0; 

// the actual query for the grid data 
$query = "SELECT *, `m`.`_on` AS `updated`,
	`t`.`id` AS `type_id`, 
	`s`.`id` AS `status_id`, 
	`m`.`id` AS `member_id`, 
	`m`.`field6` AS `middle_name`, 
	`m`.`field1` AS `surname`, 
	`m`.`field2` AS `firstname`, 
	`m`.`field9` AS `street`, 
	`m`.`field10` AS `city`, 	
	`m`.`field11` AS `postcode`, 
	`m`.`field21` AS `member_status_id`, 
	`m`.`field7` AS `membertype`, 
	`m`.`field12` AS `lat`, 
	`m`.`field13` AS `lon`, 
	`m`.`field25` AS `contact`, 	
	`m`.`field17` AS `joindate`, 
	`m`.`field16` AS `duedate`, 
	`m`.`field18` AS `dob`, 	
	`m`.`field19` AS `crb`, 
	`m`.`field4` AS `teamno`,
	`s`.`description` AS `stat_descr`, 
	`s`.`status_val` AS `status_name`, 
	`t`.`name` AS `type_name`, 	
	`t`.`background` AS `type_background`, 
	`t`.`color` AS `type_text`, 
	`s`.`background` AS `status_background`, 
	`s`.`color` AS `status_text`, 
	`m`.`field14` AS `medical` 
	FROM `$GLOBALS[mysql_prefix]member` `m` 
	LEFT JOIN `$GLOBALS[mysql_prefix]member_types` `t` ON ( `m`.`field7` = t.id )	
	LEFT JOIN `$GLOBALS[mysql_prefix]member_status` `s` ON ( `m`.`field21` = s.id ) 	
	LEFT JOIN `$GLOBALS[mysql_prefix]team` `te` ON ( `m`.`field3` = te.id ) 
	LEFT JOIN `$GLOBALS[mysql_prefix]user` `us` ON ( `m`.`id` = us.member ) 	
	$where ORDER BY $sidx $sord LIMIT $start , $limit";

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);


// we should set the appropriate header information. Do not forget this.
header("Content-type: text/xml;charset=utf-8");
 
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .= "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";
 
// be sure to put text data in CDATA
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	if((can_edit()) || (is_manager($row['member_id'])) || (is_curr_member($row['member_id']))) {
		$statusmenu = get_status_sel($row['member_id'], $row['member_status_id'], "m");
		} else {
		$statusmenu = "<SPAN style='background: " . $row['status_background'] . "; color: " . $row['status_text'] . ";'>" . $row['status_name'] . "</SPAN>";
		}
	if((can_edit()) || (is_manager($row['member_id']))) {
		$typemenu = get_type_sel($row['member_id'], $row['membertype'], "m");
		} else {
		$typemenu = "<SPAN style='background: " . $row['type_background'] . "; color: " . $row['type_text'] . ";'>" . $row['type_name'] . "</SPAN>";
		}		
	$type_col = ($row['type_id'] == 2) ? "red" : "green";
	$type_back = ($row['type_id'] == 2) ? "yellow" : "#CECECE";	
    $s .= "<row id='". $row['member_id']."'>";            
    $s .= "<cell>". $row['member_id']."</cell>";
    $s .= "<cell><![CDATA[". $row['firstname']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['middle_name']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['surname']."]]></cell>";	
    $s .= "<cell><![CDATA[". $row['teamno']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['street']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['city']."]]></cell>";	
    $s .= "<cell><![CDATA[". $row['postcode']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['lat']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['lon']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['contact']."]]></cell>";	
    $s .= "<cell><![CDATA[". $row['membertype']."]]></cell>";	
    $s .= "<cell><![CDATA[". $row['type_name']."]]></cell>";
    $s .= "<cell><![CDATA[". $typemenu."]]></cell>";
    $s .= "<cell><![CDATA[". $row['status_name']."]]></cell>";
    $s .= "<cell><![CDATA[". $statusmenu."]]></cell>";
    $s .= ((strtotime($row['joindate']) == NULL) || (date("Y", strtotime($row['joindate'])) == '1970') || (date("Y", strtotime($row['joindate'])) == '0000')) ? "<cell><![CDATA[TBA]]></cell>" : "<cell><![CDATA[". date("d-m-Y", strtotime($row['joindate']))."]]></cell>";
    $s .= ((strtotime($row['duedate']) == NULL) || (date("Y", strtotime($row['duedate'])) == '1970') || (date("Y", strtotime($row['duedate'])) == '0000')) ? "<cell><![CDATA[TBA]]></cell>" : "<cell><![CDATA[". date("d-m-Y", strtotime($row['duedate']))."]]></cell>";
    $s .= ((strtotime($row['updated']) == NULL) || (date("Y", strtotime($row['updated'])) == '1970') || (date("Y", strtotime($row['updated'])) == '0000')) ? "<cell><![CDATA[TBA]]></cell>" : "<cell><![CDATA[". date("d-m-Y", strtotime($row['updated']))."]]></cell>";
    $s .= "</row>";
}
$s .= "</rows>"; 
 
echo $s;
//}