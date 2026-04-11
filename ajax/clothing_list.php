<?php

error_reporting(E_ALL);
require_once '../incs/functions.inc.php';
@session_start();
$the_session = sanitize_string($_GET['session']);
if(!(secure_page($the_session))) {
    exit();
    } else {

$page = sanitize_int($_GET['page']);

// get how many rows we want to have into the grid - rowNum parameter in the grid
$limit = sanitize_int($_GET['rows']);

// get index row - i.e. user click to sort. At first time sortname parameter -
// after that the index from colModel
$sidx = sanitize_string($_GET['sidx']);

// sorting order - at first time sortorder
$sord = sanitize_string($_GET['sord']);

// if we not pass at first time index use the first column for the index or what you want
if(!$sidx) $sidx =1;

// Whitelist sidx and sord to prevent SQL injection in ORDER BY
$allowed_columns = ['id', 'clothing_item', 'description', 'size', 'name'];
if (!in_array($sidx, $allowed_columns)) $sidx = '1';
$sord = (strtolower($sord) === 'desc') ? 'DESC' : 'ASC';

// calculate the number of rows for the query. We need this for paging the result
$result = db_query("SELECT COUNT(*) AS count FROM `{$GLOBALS['mysql_prefix']}member`");
$row = $result ? $result->fetch_assoc() : null;
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
$query = "SELECT *, `a`.`id` AS `id`,
    `a`.`clothing_item` AS `name`,
    `a`.`description` AS `description`,
    `a`.`size` AS `size`
    FROM `{$GLOBALS['mysql_prefix']}clothing_types` `a`
    ORDER BY $sidx $sord LIMIT ?, ?";

$result = db_query($query, [$start, $limit], 'ii');

// we should set the appropriate header information. Do not forget this.
header("Content-type: text/xml;charset=utf-8");

$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .= "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

// be sure to put text data in CDATA
while($row = $result->fetch_assoc()) {
    $s .= "<row id='". e($row['id'])."'>";
    $s .= "<cell>". e($row['id'])."</cell>";
    $s .= "<cell><![CDATA[". $row['name']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['description']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['size']."]]></cell>";
    $s .= "</row>";
}
$s .= "</rows>";

echo $s;
}